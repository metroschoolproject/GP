<?php

class BookingModel
{
    private $db;
    private ?bool $cartVenueRoomColumn = null;
    private ?bool $bookingVenueRoomColumn = null;
    private ?bool $cartSourceColumn = null;
    private ?bool $bookingSourceColumn = null;
    private ?bool $bookingSupplierDeadlineColumn = null;
    private ?array $bookingStatusValues = null;
    private array $paymentColumnCache = [];

    public function __construct()
    {
        $this->db = new Database();
    }

    /* ─── Booking CRUD ─────────────────────────────────────────────── */

    /**
     * Create a draft booking from cart items.
     * Returns the booking ID on success, or false on failure.
     */
    public function createDraftFromCart(int $userId, int $cartId, float $totalAmount): int|false
    {
        $status = $this->normalizeBookingStatus('draft');

        $this->db->dbquery(
            "INSERT INTO bookings (user_id, cart_id, total_amount, paid_amount, payment_status, status)
             VALUES (:uid, :cid, :total, 0.00, 'unpaid', :status)"
        );
        $this->db->dbbind(':uid', $userId, PDO::PARAM_INT);
        $this->db->dbbind(':cid', $cartId, PDO::PARAM_INT);
        $this->db->dbbind(':total', number_format($totalAmount, 2, '.', ''));
        $this->db->dbbind(':status', $status);

        if (!$this->db->dbexecute()) {
            return false;
        }

        return (int)$this->db->lastinsertid();
    }

    /**
     * Transfer cart items to booking_items.
     */
    public function insertBookingItems(int $bookingId, int $userId, array $itemPrices = []): array|false
    {
        $hasCartVenueRoomColumn = $this->hasCartVenueRoomColumn();
        $hasBookingVenueRoomColumn = $this->hasBookingVenueRoomColumn();
        $hasCartSourceColumn = $this->hasCartSourceColumn();
        $hasBookingSourceColumn = $this->hasBookingSourceColumn();
        $cartVenueRoomValue = $hasCartVenueRoomColumn ? 'ci.venue_room_id' : 'NULL';
        $venueRoomInsertColumn = $hasBookingVenueRoomColumn ? ', venue_room_id' : '';
        $venueRoomSelectColumn = $hasBookingVenueRoomColumn ? ", COALESCE({$cartVenueRoomValue}, selected_vra.room_id)" : '';
        $sourceInsertColumn = $hasBookingSourceColumn ? ', source' : '';
        $sourceSelectColumn = $hasBookingSourceColumn
            ? ', ' . ($hasCartSourceColumn ? "COALESCE(ci.source, 'custom')" : "'custom'")
            : '';

        $this->db->dbquery(
            "INSERT INTO booking_items (booking_id, item_type{$sourceInsertColumn}, item_id, booking_date, price, status, slot_id, start_time, end_time, booking_type{$venueRoomInsertColumn})
            SELECT :bid, ci.item_type{$sourceSelectColumn}, ci.item_id,
                    CONCAT(ci.selected_date, ' ', COALESCE(ci.start_time, '00:00:00')),
                    CASE
                        WHEN ci.item_type = 'package' THEN COALESCE(p.base_price * 1.05, ci.price, 0)
                        ELSE COALESCE(ci.price, s.price_min, s.price, sp.total_price, 0)
                    END,
                    'pending',
                    ci.slot_id, ci.start_time, ci.end_time,
                    COALESCE(s.booking_type, 'fullday'){$venueRoomSelectColumn}
            FROM cart_items ci
            LEFT JOIN services s ON ci.item_id = s.id AND ci.item_type = 'service'
            LEFT JOIN packages p ON ci.item_id = p.package_id AND ci.item_type = 'package'
            LEFT JOIN supplier_packages sp ON ci.item_id = sp.id AND ci.item_type = 'supplier_package'
            LEFT JOIN venue_room_availability selected_vra ON selected_vra.id = ci.slot_id
            WHERE ci.user_id = :uid
            ORDER BY ci.id DESC"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        $this->db->dbbind(':uid', $userId, PDO::PARAM_INT);
        
        if (!$this->db->dbexecute()) {
            return false;
        }
        
        // Fetch and return the inserted item IDs
        $this->db->dbquery("SELECT id FROM booking_items WHERE booking_id = :bid ORDER BY id ASC");
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        $rows = $this->db->getmultidata();
        $ids = array_column($rows, 'id');

        foreach ($ids as $index => $bookingItemId) {
            if (!array_key_exists($index, $itemPrices)) {
                continue;
            }

            $this->db->dbquery(
                'UPDATE booking_items
                 SET price = :price
                 WHERE id = :id AND booking_id = :bid
                 LIMIT 1'
            );
            $this->db->dbbind(':price', number_format((float)$itemPrices[$index], 2, '.', ''));
            $this->db->dbbind(':id', (int)$bookingItemId, PDO::PARAM_INT);
            $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
            if (!$this->db->dbexecute()) {
                return false;
            }
        }

        return $ids;
    }

    private function hasCartVenueRoomColumn(): bool
    {
        if ($this->cartVenueRoomColumn !== null) {
            return $this->cartVenueRoomColumn;
        }

        $this->db->dbquery("SHOW COLUMNS FROM cart_items LIKE 'venue_room_id'");
        $this->cartVenueRoomColumn = (bool)$this->db->getsingledata();

        return $this->cartVenueRoomColumn;
    }

    private function hasBookingVenueRoomColumn(): bool
    {
        if ($this->bookingVenueRoomColumn !== null) {
            return $this->bookingVenueRoomColumn;
        }

        $this->db->dbquery("SHOW COLUMNS FROM booking_items LIKE 'venue_room_id'");
        $this->bookingVenueRoomColumn = (bool)$this->db->getsingledata();

        return $this->bookingVenueRoomColumn;
    }

    private function hasCartSourceColumn(): bool
    {
        if ($this->cartSourceColumn !== null) {
            return $this->cartSourceColumn;
        }

        $this->db->dbquery("SHOW COLUMNS FROM cart_items LIKE 'source'");
        $this->cartSourceColumn = (bool)$this->db->getsingledata();

        return $this->cartSourceColumn;
    }

    private function hasBookingSourceColumn(): bool
    {
        if ($this->bookingSourceColumn !== null) {
            return $this->bookingSourceColumn;
        }

        $this->db->dbquery("SHOW COLUMNS FROM booking_items LIKE 'source'");
        $this->bookingSourceColumn = (bool)$this->db->getsingledata();

        return $this->bookingSourceColumn;
    }

    private function hasBookingSupplierDeadlineColumn(): bool
    {
        if ($this->bookingSupplierDeadlineColumn !== null) {
            return $this->bookingSupplierDeadlineColumn;
        }

        $this->db->dbquery("SHOW COLUMNS FROM bookings LIKE 'supplier_response_deadline'");
        $this->bookingSupplierDeadlineColumn = (bool)$this->db->getsingledata();

        return $this->bookingSupplierDeadlineColumn;
    }

    private function paymentHasColumn(string $column): bool
    {
        if (array_key_exists($column, $this->paymentColumnCache)) {
            return $this->paymentColumnCache[$column];
        }

        $this->db->dbquery("SHOW COLUMNS FROM payments LIKE :column");
        $this->db->dbbind(':column', $column);
        $this->paymentColumnCache[$column] = (bool)$this->db->getsingledata();

        return $this->paymentColumnCache[$column];
    }

    private function bookingStatusValues(): array
    {
        if ($this->bookingStatusValues !== null) {
            return $this->bookingStatusValues;
        }

        $this->db->dbquery("SHOW COLUMNS FROM bookings LIKE 'status'");
        $column = $this->db->getsingledata();
        $type = (string)($column['Type'] ?? '');
        preg_match_all("/'([^']+)'/", $type, $matches);
        $this->bookingStatusValues = $matches[1] ?? [];

        return $this->bookingStatusValues;
    }

    /**
     * Hardcoded baseline of all possible status values.
     * Used as a last resort when the ENUM cannot be read from the database.
     */
    private const KNOWN_BOOKING_STATUSES = [
        'draft',
        'pending_supplier_response',
        'pending_payment',
        'payment_submitted',
        'payment_verified',
        'paid',
        'suppliers_responding',
        'confirmed',
        'pending_final_payment',
        'finalized',
        'completed',
        'cancelled',
    ];

    private function normalizeBookingStatus(string $status): string
    {
        $allowed = $this->bookingStatusValues();

        // If the DB query failed (empty result), fall back to the comprehensive known list.
        // This prevents SQL truncation errors when the ENUM can't be read dynamically.
        if (empty($allowed)) {
            $allowed = self::KNOWN_BOOKING_STATUSES;
        }

        if (in_array($status, $allowed, true)) {
            return $status;
        }

        // Map new/alternative status values to their equivalents in the current ENUM
        $fallbacks = [
            'pending_supplier_response' => 'suppliers_responding',
            'paid' => 'payment_verified',
        ];
        $fallback = $fallbacks[$status] ?? '';

        if ($fallback !== '' && in_array($fallback, $allowed, true)) {
            return $fallback;
        }

        // Ultimate fallback: 'draft' should exist in every ENUM definition
        return 'draft';
    }

    /**
     * Apply the shared event schedule only to items that did not choose a slot/date earlier.
     */
    public function updateUnscheduledBookingItemsSchedule(int $bookingId, string $eventDate, string $startTime = '', string $endTime = ''): bool
    {
        $eventDate = trim($eventDate);
        $startTime = trim($startTime);
        $endTime = trim($endTime);

        $sets = [];
        if ($eventDate !== '') {
            $sets[] = "booking_date = CONCAT(:event_date, ' ', COALESCE(NULLIF(:booking_start_time_for_date, ''), '00:00:00'))";
        }
        $sets[] = 'start_time = NULLIF(:start_time, \'\')';
        $sets[] = 'end_time = NULLIF(:end_time, \'\')';

        $this->db->dbquery(
            "UPDATE booking_items
             SET " . implode(', ', $sets) . "
             WHERE booking_id = :bid
               AND slot_id IS NULL
               AND start_time IS NULL
               AND end_time IS NULL
               AND TIME(booking_date) <> '00:00:00'"
        );

        if ($eventDate !== '') {
            $this->db->dbbind(':event_date', $eventDate);
            $this->db->dbbind(':booking_start_time_for_date', $startTime);
        }
        $this->db->dbbind(':start_time', $startTime);
        $this->db->dbbind(':end_time', $endTime);
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);

        return $this->db->dbexecute();
    }

    /**
     * Insert event_details for a booking (per-item notes & guest counts stored in event_details).
     */
    public function insertEventDetails(int $bookingId, array $itemsData, array $bookingItemIds): bool
    {
        foreach ($itemsData as $index => $item) {
            $bookingItemId = $bookingItemIds[$index] ?? null;
            
            $this->db->dbquery(
                "INSERT INTO event_details
                    (booking_id, booking_item_id, event_date, start_time, end_time, 
                    guest_count, location, contact_phone, special_requests, contact_name)
                VALUES (:bid, :biid, :edate, :stime, :etime, :guests, :location, :phone, :notes, :cname)"
            );
            $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
            $this->db->dbbind(':biid', $bookingItemId, PDO::PARAM_INT);
            $this->db->dbbind(':edate', $item['event_date'] ?? null);
            $this->db->dbbind(':stime', $item['start_time'] ?? null);
            $this->db->dbbind(':etime', $item['end_time'] ?? null);
            $this->db->dbbind(':guests', !empty($item['guest_count']) ? (int)$item['guest_count'] : null, PDO::PARAM_INT);
            $this->db->dbbind(':location', $item['location'] ?? null);
            $this->db->dbbind(':phone', $item['phone'] ?? null);
            $this->db->dbbind(':notes', $item['notes'] ?? null);
            $this->db->dbbind(':cname', $item['contact_name'] ?? null);
            
            if (!$this->db->dbexecute()) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Link supplier(s) to the booking via booking_suppliers.
     */
    public function insertBookingSuppliers(int $bookingId): bool
    {
        $this->db->dbquery(
            "INSERT IGNORE INTO booking_suppliers (booking_id, supplier_id)
             SELECT :bid, suppliers_for_booking.supplier_id
             FROM (
                SELECT s.supplier_id
                FROM booking_items bi
                INNER JOIN services s ON bi.item_id = s.id AND bi.item_type = 'service'
                WHERE bi.booking_id = :service_bid

                UNION

                SELECT pi.default_supplier_id AS supplier_id
                FROM booking_items bi
                INNER JOIN package_items pi ON bi.item_id = pi.package_id AND bi.item_type = 'package'
                WHERE bi.booking_id = :package_bid

                UNION

                SELECT package_service.supplier_id
                FROM booking_items bi
                INNER JOIN package_items pi ON bi.item_id = pi.package_id AND bi.item_type = 'package'
                INNER JOIN services package_service ON pi.service_id = package_service.id
                WHERE bi.booking_id = :package_service_bid

                UNION

                SELECT sp.supplier_id
                FROM booking_items bi
                INNER JOIN supplier_packages sp ON bi.item_id = sp.id AND bi.item_type = 'supplier_package'
                WHERE bi.booking_id = :supplier_package_bid

                UNION

                SELECT s2.supplier_id
                FROM booking_items bi
                INNER JOIN supplier_package_items spi ON bi.item_id = spi.package_id AND bi.item_type = 'supplier_package'
                INNER JOIN services s2 ON spi.service_id = s2.id
                WHERE bi.booking_id = :supplier_package_items_bid
             ) suppliers_for_booking
             WHERE suppliers_for_booking.supplier_id IS NOT NULL
             GROUP BY suppliers_for_booking.supplier_id"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        $this->db->dbbind(':service_bid', $bookingId, PDO::PARAM_INT);
        $this->db->dbbind(':package_bid', $bookingId, PDO::PARAM_INT);
        $this->db->dbbind(':package_service_bid', $bookingId, PDO::PARAM_INT);
        $this->db->dbbind(':supplier_package_bid', $bookingId, PDO::PARAM_INT);
        $this->db->dbbind(':supplier_package_items_bid', $bookingId, PDO::PARAM_INT);

        return $this->db->dbexecute();
    }

    /**
     * Log a booking status change.
     */
    public function logStatusChange(int $bookingId, ?string $oldStatus, string $newStatus, ?int $changedBy = null, ?string $note = null): bool
    {
        $this->db->dbquery(
            "INSERT INTO booking_status_logs (booking_id, old_status, new_status, changed_by, note)
             VALUES (:bid, :old, :new, :changed_by, :note)"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        $this->db->dbbind(':old', $oldStatus);
        $this->db->dbbind(':new', $newStatus);
        $this->db->dbbind(':changed_by', $changedBy, PDO::PARAM_INT);
        $this->db->dbbind(':note', $note);

        return $this->db->dbexecute();
    }

    /**
     * Update booking status.
     */
    public function updateStatus(int $bookingId, string $status, ?string $paymentStatus = null): bool
    {
        $status = $this->normalizeBookingStatus($status);
        $sql = "UPDATE bookings SET status = :status";
        $params = [':status' => $status, ':id' => $bookingId];

        if ($paymentStatus !== null) {
            $sql .= ", payment_status = :payment_status";
            $params[':payment_status'] = $paymentStatus;
        }

        $sql .= " WHERE id = :id";

        $this->db->dbquery($sql);
        foreach ($params as $key => $val) {
            $this->db->dbbind($key, $val);
        }

        return $this->db->dbexecute();
    }

    /**
     * Update booking paid amount.
     */
    public function updatePaidAmount(int $bookingId, float $amount): bool
    {
        $this->db->dbquery(
            "UPDATE bookings SET paid_amount = :amount WHERE id = :id"
        );
        $this->db->dbbind(':amount', number_format($amount, 2, '.', ''));
        $this->db->dbbind(':id', $bookingId, PDO::PARAM_INT);

        return $this->db->dbexecute();
    }

    /* ─── Retrieval ──────────────────────────────────────────────── */

    /**
     * Get a booking by ID with related user info.
     */
    public function getBookingById(int $bookingId): array|false
    {
        $this->db->dbquery(
            "SELECT b.*, u.name AS customer_name, u.email AS customer_email, u.phone AS customer_phone
             FROM bookings b
             LEFT JOIN users u ON b.user_id = u.user_id
             WHERE b.id = :id
             LIMIT 1"
        );
        $this->db->dbbind(':id', $bookingId, PDO::PARAM_INT);
        return $this->db->getsingledata();
    }

    /**
     * Get booking items with service/supplier details.
     */
    public function getBookingItems(int $bookingId): array
    {
        $hasBookingVenueRoomColumn = $this->hasBookingVenueRoomColumn();
        $bookingVenueSelect = $hasBookingVenueRoomColumn
            ? 'COALESCE(bi_vr.id, slot_vr.id) AS venue_room_id,
                    COALESCE(bi_vr.name, slot_vr.name) AS venue_room_name,
                    COALESCE(bi_venue.name, slot_venue.name) AS venue_name'
            : 'slot_vr.id AS venue_room_id,
                    slot_vr.name AS venue_room_name,
                    slot_venue.name AS venue_name';
        $bookingVenueJoin = $hasBookingVenueRoomColumn
            ? 'LEFT JOIN venue_rooms bi_vr ON bi_vr.id = bi.venue_room_id
             LEFT JOIN venues bi_venue ON bi_venue.id = bi_vr.venue_id'
            : '';

        $this->db->dbquery(
            "SELECT bi.*,
                    COALESCE(s.name, p.name, sp.name) AS service_name,
                    COALESCE(s.thumbnail_url, p.image_url, sp.thumbnail_url) AS thumbnail_url,
                    COALESCE(sup.shop_name, sp_sup.shop_name, 'Golden Promise') AS supplier_name,
                    sup.supplier_id,
                    cat.name AS category_name,
                    {$bookingVenueSelect}
             FROM booking_items bi
             LEFT JOIN services s ON bi.item_id = s.id AND bi.item_type = 'service'
             LEFT JOIN packages p ON bi.item_id = p.package_id AND bi.item_type = 'package'
             LEFT JOIN supplier_packages sp ON bi.item_id = sp.id AND bi.item_type = 'supplier_package'
             LEFT JOIN suppliers sup ON s.supplier_id = sup.supplier_id
             LEFT JOIN suppliers sp_sup ON sp.supplier_id = sp_sup.supplier_id
             LEFT JOIN categories cat ON s.category_id = cat.id
             {$bookingVenueJoin}
             LEFT JOIN venue_room_availability slot_vra ON slot_vra.id = bi.slot_id
             LEFT JOIN venue_rooms slot_vr ON slot_vr.id = slot_vra.room_id
             LEFT JOIN venues slot_venue ON slot_venue.id = slot_vr.venue_id
             WHERE bi.booking_id = :bid
             ORDER BY bi.id ASC"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        return $this->db->getmultidata();
    }

    /**
     * Get booking items that belong to one supplier.
     */
    public function getBookingItemsForSupplier(int $bookingId, int $supplierId): array
    {
        $hasBookingVenueRoomColumn = $this->hasBookingVenueRoomColumn();
        $bookingVenueSelect = $hasBookingVenueRoomColumn
            ? 'COALESCE(bi_vr.id, slot_vr.id) AS venue_room_id,
                    COALESCE(bi_vr.name, slot_vr.name) AS venue_room_name,
                    COALESCE(bi_venue.name, slot_venue.name) AS venue_name'
            : 'slot_vr.id AS venue_room_id,
                    slot_vr.name AS venue_room_name,
                    slot_venue.name AS venue_name';
        $bookingVenueJoin = $hasBookingVenueRoomColumn
            ? 'LEFT JOIN venue_rooms bi_vr ON bi_vr.id = bi.venue_room_id
             LEFT JOIN venues bi_venue ON bi_venue.id = bi_vr.venue_id'
            : '';

        $this->db->dbquery(
            "SELECT bi.*,
                    COALESCE(s.name, p.name, sp.name) AS service_name,
                    COALESCE(s.thumbnail_url, p.image_url, sp.thumbnail_url) AS thumbnail_url,
                    COALESCE(sup.shop_name, sp_sup.shop_name, 'Golden Promise') AS supplier_name,
                    COALESCE(sup.supplier_id, sp_sup.supplier_id) AS supplier_id,
                    cat.name AS category_name,
                    {$bookingVenueSelect}
             FROM booking_items bi
             LEFT JOIN services s ON bi.item_id = s.id AND bi.item_type = 'service'
             LEFT JOIN packages p ON bi.item_id = p.package_id AND bi.item_type = 'package'
             LEFT JOIN supplier_packages sp ON bi.item_id = sp.id AND bi.item_type = 'supplier_package'
             LEFT JOIN suppliers sup ON s.supplier_id = sup.supplier_id
             LEFT JOIN suppliers sp_sup ON sp.supplier_id = sp_sup.supplier_id
             LEFT JOIN categories cat ON s.category_id = cat.id
             {$bookingVenueJoin}
             LEFT JOIN venue_room_availability slot_vra ON slot_vra.id = bi.slot_id
             LEFT JOIN venue_rooms slot_vr ON slot_vr.id = slot_vra.room_id
             LEFT JOIN venues slot_venue ON slot_venue.id = slot_vr.venue_id
             WHERE bi.booking_id = :bid
               AND (
                    s.supplier_id = :sid_service
                    OR sp.supplier_id = :sid_package
                    OR (
                        bi.item_type = 'package'
                        AND EXISTS (
                            SELECT 1
                            FROM package_items pi_supplier
                            LEFT JOIN services pi_service ON pi_service.id = pi_supplier.service_id
                            WHERE pi_supplier.package_id = bi.item_id
                              AND (
                                  pi_supplier.default_supplier_id = :sid_default
                                  OR pi_service.supplier_id = :sid_package_service
                              )
                        )
                    )
               )
             ORDER BY bi.id ASC"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        $this->db->dbbind(':sid_service', $supplierId, PDO::PARAM_INT);
        $this->db->dbbind(':sid_package', $supplierId, PDO::PARAM_INT);
        $this->db->dbbind(':sid_default', $supplierId, PDO::PARAM_INT);
        $this->db->dbbind(':sid_package_service', $supplierId, PDO::PARAM_INT);
        return $this->db->getmultidata();
    }

    /**
     * Get event details for a booking.
     */
    public function getEventDetails(int $bookingId): array
    {
        $this->db->dbquery(
            "SELECT * FROM event_details WHERE booking_id = :bid ORDER BY id ASC"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        return $this->db->getmultidata();
    }

    /**
     * Get booking suppliers.
     */
    public function getBookingSuppliers(int $bookingId): array
    {
        $this->db->dbquery(
            "SELECT bs.*,
                    sup.shop_name,
                    (
                        SELECT sd.file_url
                        FROM supplier_documents sd
                        WHERE sd.supplier_id = sup.supplier_id
                          AND sd.type = 'cover_photo'
                        ORDER BY sd.id DESC
                        LIMIT 1
                    ) AS thumbnail_url
             FROM booking_suppliers bs
             LEFT JOIN suppliers sup ON bs.supplier_id = sup.supplier_id
             WHERE bs.booking_id = :bid
             ORDER BY bs.id ASC"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        return $this->db->getmultidata();
    }

    /**
     * Get booking payments.
     */
    public function getBookingPayments(int $bookingId): array
    {
        $this->db->dbquery(
            "SELECT * FROM payments WHERE booking_id = :bid ORDER BY id ASC"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        return $this->db->getmultidata();
    }

    /**
     * Get status logs for a booking.
     */
    public function getStatusLogs(int $bookingId): array
    {
        $this->db->dbquery(
            "SELECT bsl.*, u.name AS changed_by_name
             FROM booking_status_logs bsl
             LEFT JOIN users u ON bsl.changed_by = u.user_id
             WHERE bsl.booking_id = :bid
             ORDER BY bsl.id ASC"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        return $this->db->getmultidata();
    }

    /**
     * Get all bookings for a user (customer).
     */
    public function getCustomerBookings(int $userId, ?string $statusFilter = null): array
    {
        $sql = "SELECT b.*,
                       (SELECT COUNT(*) FROM booking_items WHERE booking_id = b.id) AS item_count
                FROM bookings b
                WHERE b.user_id = :uid";

        if ($statusFilter && $statusFilter !== 'all') {
            $sql .= " AND b.status = :status";
        }

        $sql .= " ORDER BY b.created_at DESC";

        $this->db->dbquery($sql);
        $this->db->dbbind(':uid', $userId, PDO::PARAM_INT);
        if ($statusFilter && $statusFilter !== 'all') {
            $this->db->dbbind(':status', $statusFilter);
        }

        return $this->db->getmultidata();
    }

    /**
     * Get all bookings for a supplier.
     */
    public function getSupplierBookings(int $supplierId, ?string $statusFilter = null): array
    {
        $supplierItemCountSql = $this->supplierBookingItemCountSql();
        $supplierItemTotalSql = $this->supplierBookingItemTotalSql();
        $sql = "SELECT b.*, u.name AS customer_name,
                       bs.status AS supplier_status, bs.id AS booking_supplier_id,
                       {$supplierItemCountSql} AS item_count,
                       {$supplierItemTotalSql} AS supplier_total_amount
                FROM bookings b
                INNER JOIN booking_suppliers bs ON b.id = bs.booking_id
                LEFT JOIN users u ON b.user_id = u.user_id
                WHERE bs.supplier_id = :sid";

        if ($statusFilter && $statusFilter !== 'all') {
            $sql .= " AND bs.status = :status";
        }

        $sql .= " ORDER BY b.created_at DESC";

        $this->db->dbquery($sql);
        $this->db->dbbind(':sid', $supplierId, PDO::PARAM_INT);
        if ($statusFilter && $statusFilter !== 'all') {
            $this->db->dbbind(':status', $statusFilter);
        }

        return $this->db->getmultidata();
    }

    /**
     * Get supplier bookings with pagination.
     */
    public function getSupplierBookingsWithPagination(int $supplierId, ?string $statusFilter = null, int $limit = 20, int $offset = 0): array
    {
        $supplierItemCountSql = $this->supplierBookingItemCountSql();
        $supplierItemTotalSql = $this->supplierBookingItemTotalSql();
        $sql = "SELECT b.*, u.name AS customer_name, u.phone AS customer_phone,
                       bs.status AS supplier_status, bs.id AS booking_supplier_id,
                       {$supplierItemCountSql} AS item_count,
                       {$supplierItemTotalSql} AS supplier_total_amount,
                       (SELECT event_date FROM event_details WHERE booking_id = b.id LIMIT 1) AS event_date
                FROM bookings b
                INNER JOIN booking_suppliers bs ON b.id = bs.booking_id
                LEFT JOIN users u ON b.user_id = u.user_id
                WHERE bs.supplier_id = :sid
                  AND b.status NOT IN ('draft', 'pending_payment', 'payment_submitted')";

        if ($statusFilter && $statusFilter !== 'all') {
            $sql .= " AND bs.status = :status";
        }

        $sql .= " ORDER BY b.created_at DESC LIMIT :limit OFFSET :offset";

        $this->db->dbquery($sql);
        $this->db->dbbind(':sid', $supplierId, PDO::PARAM_INT);
        if ($statusFilter && $statusFilter !== 'all') {
            $this->db->dbbind(':status', $statusFilter);
        }
        $this->db->dbbind(':limit', $limit, PDO::PARAM_INT);
        $this->db->dbbind(':offset', $offset, PDO::PARAM_INT);

        return $this->db->getmultidata();
    }

    /**
     * Get total count of supplier bookings for pagination.
     */
    public function getSupplierBookingsCount(int $supplierId, ?string $statusFilter = null): int
    {
        $sql = "SELECT COUNT(*) as total
                FROM bookings b
                INNER JOIN booking_suppliers bs ON b.id = bs.booking_id
                WHERE bs.supplier_id = :sid
                  AND b.status NOT IN ('draft', 'pending_payment', 'payment_submitted')";

        if ($statusFilter && $statusFilter !== 'all') {
            $sql .= " AND bs.status = :status";
        }

        $this->db->dbquery($sql);
        $this->db->dbbind(':sid', $supplierId, PDO::PARAM_INT);
        if ($statusFilter && $statusFilter !== 'all') {
            $this->db->dbbind(':status', $statusFilter);
        }

        $result = $this->db->getsingledata();
        return (int)($result['total'] ?? 0);
    }

    /**
     * Search supplier bookings by customer name, booking ref, or customer phone.
     */
    public function searchSupplierBookings(int $supplierId, string $searchTerm, ?string $statusFilter = null, int $limit = 20, int $offset = 0): array
    {
        $searchTerm = '%' . trim($searchTerm) . '%';
        $supplierItemCountSql = $this->supplierBookingItemCountSql();
        $supplierItemTotalSql = $this->supplierBookingItemTotalSql();

        $sql = "SELECT b.*, u.name AS customer_name, u.phone AS customer_phone,
                       bs.status AS supplier_status, bs.id AS booking_supplier_id,
                       {$supplierItemCountSql} AS item_count,
                       {$supplierItemTotalSql} AS supplier_total_amount,
                       (SELECT event_date FROM event_details WHERE booking_id = b.id LIMIT 1) AS event_date
                FROM bookings b
                INNER JOIN booking_suppliers bs ON b.id = bs.booking_id
                LEFT JOIN users u ON b.user_id = u.user_id
                WHERE bs.supplier_id = :sid
                AND (u.name LIKE :search OR u.phone LIKE :search2 OR CONCAT('BK', b.id) LIKE :search3)";

        if ($statusFilter && $statusFilter !== 'all') {
            $sql .= " AND bs.status = :status";
        }

        $sql .= " ORDER BY b.created_at DESC LIMIT :limit OFFSET :offset";

        $this->db->dbquery($sql);
        $this->db->dbbind(':sid', $supplierId, PDO::PARAM_INT);
        $this->db->dbbind(':search', $searchTerm);
        $this->db->dbbind(':search2', $searchTerm);
        $this->db->dbbind(':search3', $searchTerm);
        if ($statusFilter && $statusFilter !== 'all') {
            $this->db->dbbind(':status', $statusFilter);
        }
        $this->db->dbbind(':limit', $limit, PDO::PARAM_INT);
        $this->db->dbbind(':offset', $offset, PDO::PARAM_INT);

        return $this->db->getmultidata();
    }

    /**
     * Get count of search results for supplier bookings.
     */
    public function searchSupplierBookingsCount(int $supplierId, string $searchTerm, ?string $statusFilter = null): int
    {
        $searchTerm = '%' . trim($searchTerm) . '%';

        $sql = "SELECT COUNT(*) as total
                FROM bookings b
                INNER JOIN booking_suppliers bs ON b.id = bs.booking_id
                LEFT JOIN users u ON b.user_id = u.user_id
                WHERE bs.supplier_id = :sid
                AND (u.name LIKE :search OR u.phone LIKE :search2 OR CONCAT('BK', b.id) LIKE :search3)";

        if ($statusFilter && $statusFilter !== 'all') {
            $sql .= " AND bs.status = :status";
        }

        $this->db->dbquery($sql);
        $this->db->dbbind(':sid', $supplierId, PDO::PARAM_INT);
        $this->db->dbbind(':search', $searchTerm);
        $this->db->dbbind(':search2', $searchTerm);
        $this->db->dbbind(':search3', $searchTerm);
        if ($statusFilter && $statusFilter !== 'all') {
            $this->db->dbbind(':status', $statusFilter);
        }

        $result = $this->db->getsingledata();
        return (int)($result['total'] ?? 0);
    }

    /**
     * Get all bookings (admin view).
     */
    public function getAllBookings(?string $statusFilter = null, ?string $search = null): array
    {
        $sql = "SELECT b.*, u.name AS customer_name,
                       (SELECT GROUP_CONCAT(DISTINCT sup.shop_name SEPARATOR ', ')
                        FROM booking_suppliers bs2
                        LEFT JOIN suppliers sup ON bs2.supplier_id = sup.supplier_id
                        WHERE bs2.booking_id = b.id) AS supplier_names
                FROM bookings b
                LEFT JOIN users u ON b.user_id = u.user_id
                WHERE 1=1";

        $params = [];

        if ($statusFilter && $statusFilter !== 'all') {
            $sql .= " AND b.status = :status";
            $params[':status'] = $statusFilter;
        }

        if ($search && trim($search) !== '') {
            $sql .= " AND (b.id LIKE :search OR u.name LIKE :search2)";
            $params[':search'] = '%' . $search . '%';
            $params[':search2'] = '%' . $search . '%';
        }

        $sql .= " ORDER BY b.created_at DESC LIMIT 100";

        $this->db->dbquery($sql);
        foreach ($params as $key => $val) {
            $this->db->dbbind($key, $val);
        }

        return $this->db->getmultidata();
    }

    /**
     * Get supplier stats.
     */
    public function getSupplierStats(int $supplierId): array
    {
        $supplierItemTotalSql = $this->supplierBookingItemTotalSql();
        $this->db->dbquery(
            "SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN bs.status = 'pending' THEN 1 ELSE 0 END) AS pending_count,
                SUM(CASE WHEN bs.status = 'confirmed' THEN 1 ELSE 0 END) AS confirmed_count,
                SUM(CASE WHEN bs.status = 'completed' THEN 1 ELSE 0 END) AS completed_count,
                SUM(CASE WHEN bs.status IN ('cancelled','rejected') THEN 1 ELSE 0 END) AS cancelled_count,
                COALESCE(SUM(CASE WHEN bs.status IN ('confirmed','completed') THEN {$supplierItemTotalSql} ELSE 0 END), 0) AS est_revenue
             FROM booking_suppliers bs
             INNER JOIN bookings b ON bs.booking_id = b.id
             WHERE bs.supplier_id = :sid"
        );
        $this->db->dbbind(':sid', $supplierId, PDO::PARAM_INT);
        return $this->db->getsingledata() ?: [];
    }

    /**
     * Get supplier performance metrics (KPIs).
     */
    public function getSupplierPerformanceMetrics(int $supplierId): array
    {
        // Response rate & acceptance rate
        $this->db->dbquery(
            "SELECT
                COUNT(*) AS total_bookings,
                SUM(CASE WHEN bs.status IN ('confirmed', 'rejected') THEN 1 ELSE 0 END) AS responded_count,
                SUM(CASE WHEN bs.status = 'confirmed' THEN 1 ELSE 0 END) AS accepted_count,
                SUM(CASE WHEN bs.status = 'rejected' THEN 1 ELSE 0 END) AS rejected_count,
                COALESCE(AVG(TIMESTAMPDIFF(HOUR, b.created_at, bs.updated_at)), 0) AS avg_response_hours
             FROM booking_suppliers bs
             INNER JOIN bookings b ON bs.booking_id = b.id
             WHERE bs.supplier_id = :sid
             AND bs.status IN ('confirmed', 'rejected', 'pending')"
        );
        $this->db->dbbind(':sid', $supplierId, PDO::PARAM_INT);
        $metrics = $this->db->getsingledata() ?: [];

        $totalBookings = (int)($metrics['total_bookings'] ?? 0);
        $respondedCount = (int)($metrics['responded_count'] ?? 0);
        $acceptedCount = (int)($metrics['accepted_count'] ?? 0);
        $rejectedCount = (int)($metrics['rejected_count'] ?? 0);
        $avgResponseHours = (float)($metrics['avg_response_hours'] ?? 0);

        $responseRate = $totalBookings > 0 ? round(($respondedCount / $totalBookings) * 100, 1) : 0;
        $acceptanceRate = $respondedCount > 0 ? round(($acceptedCount / $respondedCount) * 100, 1) : 0;

        return [
            'total_bookings' => $totalBookings,
            'response_rate' => $responseRate,
            'acceptance_rate' => $acceptanceRate,
            'avg_response_hours' => round($avgResponseHours, 1),
            'accepted_count' => $acceptedCount,
            'rejected_count' => $rejectedCount,
        ];
    }

    /**
     * Get supplier upcoming bookings.
     */
    public function getSupplierUpcomingBookings(int $supplierId, int $limit = 5): array
    {
        $this->db->dbquery(
            "SELECT b.id, b.total_amount, u.name AS customer_name,
                    bs.status AS supplier_status,
                    (SELECT event_date FROM event_details WHERE booking_id = b.id LIMIT 1) AS event_date
             FROM bookings b
             INNER JOIN booking_suppliers bs ON b.id = bs.booking_id
             LEFT JOIN users u ON b.user_id = u.user_id
             WHERE bs.supplier_id = :sid
             AND bs.status = 'confirmed'
             AND b.status != 'completed'
             ORDER BY (SELECT event_date FROM event_details WHERE booking_id = b.id LIMIT 1) ASC
             LIMIT :limit"
        );
        $this->db->dbbind(':sid', $supplierId, PDO::PARAM_INT);
        $this->db->dbbind(':limit', $limit, PDO::PARAM_INT);

        return $this->db->getmultidata();
    }

    /**
     * Get admin stats.
     */
    public function getAdminStats(): array
    {
        $this->db->dbquery(
            "SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) AS draft_count,
                SUM(CASE WHEN status = 'pending_payment' THEN 1 ELSE 0 END) AS pending_payment_count,
                SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) AS paid_count,
                SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) AS confirmed_count,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed_count,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled_count,
                COALESCE(SUM(total_amount), 0) AS total_revenue
             FROM bookings"
        );
        return $this->db->getsingledata() ?: [];
    }

    /* ─── Supplier Actions ──────────────────────────────────────── */

    /**
     * Update booking_supplier status.
     */
    public function updateSupplierStatus(int $bookingSupplierId, string $status): bool
    {
        $setClause = "status = :status";
        if ($status === 'confirmed') {
            $setClause .= ", confirmed_at = NOW()";
        } elseif ($status === 'completed') {
            $setClause .= ", completed_at = NOW()";
        }

        $this->db->dbquery(
            "UPDATE booking_suppliers SET {$setClause} WHERE id = :id"
        );
        $this->db->dbbind(':status', $status);
        $this->db->dbbind(':id', $bookingSupplierId, PDO::PARAM_INT);

        return $this->db->dbexecute();
    }

    /**
     * Update booking item status by supplier.
     */
    public function updateBookingItemsStatusBySupplier(int $bookingId, int $supplierId, string $status): bool
    {
        $this->db->dbquery(
            "UPDATE booking_items bi
             LEFT JOIN services s ON bi.item_id = s.id AND bi.item_type = 'service'
             LEFT JOIN supplier_packages sp ON bi.item_id = sp.id AND bi.item_type = 'supplier_package'
             SET bi.status = :status
             WHERE bi.booking_id = :bid
               AND (
                    s.supplier_id = :sid
                    OR sp.supplier_id = :sid2
                    OR EXISTS (
                        SELECT 1
                        FROM package_items pi
                        WHERE bi.item_type = 'package'
                          AND pi.package_id = bi.item_id
                          AND pi.default_supplier_id = :sid3
                    )
                    OR EXISTS (
                        SELECT 1
                        FROM supplier_package_items spi
                        INNER JOIN services spi_service ON spi.service_id = spi_service.id
                        WHERE bi.item_type = 'supplier_package'
                          AND spi.package_id = bi.item_id
                          AND spi_service.supplier_id = :sid4
                    )
               )"
        );
        $this->db->dbbind(':status', $status);
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        $this->db->dbbind(':sid', $supplierId, PDO::PARAM_INT);
        $this->db->dbbind(':sid2', $supplierId, PDO::PARAM_INT);
        $this->db->dbbind(':sid3', $supplierId, PDO::PARAM_INT);
        $this->db->dbbind(':sid4', $supplierId, PDO::PARAM_INT);

        return $this->db->dbexecute();
    }

    /* ─── Vouchers ──────────────────────────────────────────────── */

    /**
     * Generate vouchers for a booking.
     */
    public function generateVouchers(int $bookingId): bool
    {
        $this->db->dbquery("SELECT COUNT(*) AS cnt FROM booking_vouchers WHERE booking_id = :bid");
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        $existing = $this->db->getsingledata();
        if ((int)($existing['cnt'] ?? 0) > 0) {
            return true;
        }

        $prefixes = [
            'service' => 'VCH-SRV',
            'package' => 'VCH-PKG',
            'supplier_package' => 'VCH-SPK',
        ];

        $this->db->dbquery(
            "SELECT bi.*,
                    COALESCE(s.name, p.name, sp.name) AS service_name,
                    cat.name AS category_name,
                    COALESCE(sup.supplier_id, sp_sup.supplier_id, pi.default_supplier_id) AS supplier_id,
                    ed.location
             FROM booking_items bi
             LEFT JOIN services s ON bi.item_id = s.id AND bi.item_type = 'service'
             LEFT JOIN packages p ON bi.item_id = p.package_id AND bi.item_type = 'package'
             LEFT JOIN supplier_packages sp ON bi.item_id = sp.id AND bi.item_type = 'supplier_package'
             LEFT JOIN suppliers sup ON s.supplier_id = sup.supplier_id
             LEFT JOIN suppliers sp_sup ON sp.supplier_id = sp_sup.supplier_id
             LEFT JOIN package_items pi ON bi.item_id = pi.package_id AND bi.item_type = 'package'
             LEFT JOIN categories cat ON s.category_id = cat.id
             LEFT JOIN event_details ed ON ed.booking_id = bi.booking_id AND ed.id = (
                SELECT MIN(id) FROM event_details WHERE booking_id = bi.booking_id
             )
             WHERE bi.booking_id = :bid"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        $items = $this->db->getmultidata();

        foreach ($items as $item) {
            $prefix = $prefixes[$item['item_type']] ?? 'VCH';
            $voucherNumber = $prefix . '-' . strtoupper(substr(md5($item['id'] . '-' . time()), 0, 8));

            $this->db->dbquery(
                "INSERT INTO booking_vouchers
                    (booking_id, voucher_number, service_id, supplier_id, service_name, category_name,
                     event_date, start_time, end_time, location, price, status)
                 VALUES (:bid, :vnum, :sid, :supid, :sname, :cname,
                         :edate, :stime, :etime, :loc, :price, 'active')"
            );
            $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
            $this->db->dbbind(':vnum', $voucherNumber);
            $serviceId = ($item['item_type'] ?? '') === 'service' ? (int)($item['item_id'] ?? 0) : null;
            $supplierId = !empty($item['supplier_id']) ? (int)$item['supplier_id'] : null;
            $this->db->dbbind(':sid', $serviceId, $serviceId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $this->db->dbbind(':supid', $supplierId, $supplierId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $this->db->dbbind(':sname', $item['service_name'] ?? 'Service');
            $this->db->dbbind(':cname', $item['category_name'] ?? 'Service');
            $this->db->dbbind(':edate', $item['booking_date'] ? date('Y-m-d', strtotime($item['booking_date'])) : null);
            $this->db->dbbind(':stime', $item['start_time'] ?? null);
            $this->db->dbbind(':etime', $item['end_time'] ?? null);
            $this->db->dbbind(':loc', $item['location'] ?? null);
            $this->db->dbbind(':price', $item['price'] ?? 0);

            if (!$this->db->dbexecute()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get vouchers for a booking.
     */
    public function getBookingVouchers(int $bookingId): array
    {
        $this->db->dbquery(
            "SELECT * FROM booking_vouchers WHERE booking_id = :bid ORDER BY id ASC"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        return $this->db->getmultidata();
    }

    /**
     * Get all vouchers for a user.
     */
    public function getCustomerVouchers(int $userId, ?string $statusFilter = null): array
    {
        $sql = "SELECT bv.*, b.user_id
                FROM booking_vouchers bv
                INNER JOIN bookings b ON bv.booking_id = b.id
                WHERE b.user_id = :uid";

        if ($statusFilter && $statusFilter !== 'all') {
            $sql .= " AND bv.status = :status";
        }

        $sql .= " ORDER BY bv.issued_at DESC";

        $this->db->dbquery($sql);
        $this->db->dbbind(':uid', $userId, PDO::PARAM_INT);
        if ($statusFilter && $statusFilter !== 'all') {
            $this->db->dbbind(':status', $statusFilter);
        }

        return $this->db->getmultidata();
    }

    /**
     * Generate a booking reference number.
     */
    public function generateBookingRef(int $bookingId): string
    {
        $this->db->dbquery("SELECT created_at FROM bookings WHERE id = :id LIMIT 1");
        $this->db->dbbind(':id', $bookingId, PDO::PARAM_INT);
        $row = $this->db->getsingledata();

        $date = $row ? date('Ymd', strtotime($row['created_at'])) : date('Ymd');
        return 'BK-' . $date . '-' . str_pad($bookingId, 3, '0', STR_PAD_LEFT);
    }

    /* ─── Payment ───────────────────────────────────────────────── */

    /**
     * Create a payment record for a booking.
     */
    public function createPayment(int $bookingId, float $amount, string $type, string $method, float $platformFee = 0): int|false
    {
        $this->db->dbquery(
            "INSERT INTO payments (booking_id, amount, platform_fee, supplier_amount, type, method, status)
             VALUES (:bid, :amount, :pfee, :samount, :type, :method, 'pending')"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        $this->db->dbbind(':amount', number_format($amount, 2, '.', ''));
        $this->db->dbbind(':pfee', number_format($platformFee, 2, '.', ''));
        $this->db->dbbind(':samount', number_format($amount - $platformFee, 2, '.', ''));
        $this->db->dbbind(':type', $type);
        $this->db->dbbind(':method', $method);

        if (!$this->db->dbexecute()) {
            return false;
        }

        return (int)$this->db->lastinsertid();
    }

    /**
     * Update payment to success.
     */
    public function confirmPayment(int $paymentId, string $transactionRef): bool
    {
        $this->db->dbquery(
            "UPDATE payments SET status = 'success', transaction_ref = :ref, verified_at = NOW()
             WHERE id = :id AND status = 'pending'"
        );
        $this->db->dbbind(':ref', $transactionRef);
        $this->db->dbbind(':id', $paymentId, PDO::PARAM_INT);

        return $this->db->dbexecute();
    }

    public function updatePaymentStatus(int $paymentId, string $status): bool
    {
        $this->db->dbquery(
            "UPDATE payments SET status = :status, verified_at = NOW()
             WHERE id = :id AND status = 'pending'"
        );
        $this->db->dbbind(':status', $status, PDO::PARAM_STR);
        $this->db->dbbind(':id', $paymentId, PDO::PARAM_INT);

        return $this->db->dbexecute();
    }

    /**
     * Clear the user's cart after successful booking.
     */
    public function clearCart(int $userId): bool
    {
        $this->db->dbquery("DELETE FROM cart_items WHERE user_id = :uid");
        $this->db->dbbind(':uid', $userId, PDO::PARAM_INT);
        return $this->db->dbexecute();
    }

    /* ─── Cancellation ─────────────────────────────────────────── */

    /**
     * Submit a cancellation request.
     */
    public function requestCancellation(int $bookingId, string $reason): bool
    {
        // Log the cancellation request
        $this->db->dbquery(
            "INSERT INTO booking_status_logs (booking_id, old_status, new_status, note)
             SELECT status, 'cancellation_requested', :note
             FROM bookings WHERE id = :bid"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        $this->db->dbbind(':note', 'Cancellation requested: ' . $reason);

        return $this->db->dbexecute();
    }

    /**
     * Admin: cancel a booking.
     */
    public function adminCancelBooking(int $bookingId, string $reason, int $adminId, bool $refundDeposit): bool
    {
        $this->db->dbquery(
            "UPDATE bookings SET status = 'cancelled', approved_by = :admin_id, approved_at = NOW()
             WHERE id = :bid"
        );
        $this->db->dbbind(':admin_id', $adminId, PDO::PARAM_INT);
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);

        if (!$this->db->dbexecute()) {
            return false;
        }

        if ($refundDeposit) {
            // Get payments and mark as refunded
            $this->db->dbquery(
                "UPDATE payments SET escrow_status = 'refunded'
                 WHERE booking_id = :bid AND status = 'success'"
            );
            $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
            $this->db->dbexecute();
        }

        // Update booking_suppliers
        $this->db->dbquery(
            "UPDATE booking_suppliers SET status = 'cancelled'
             WHERE booking_id = :bid AND status NOT IN ('completed')"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        $this->db->dbexecute();

        // Update booking_items
        $this->db->dbquery(
            "UPDATE booking_items SET status = 'cancelled'
             WHERE booking_id = :bid AND status != 'completed'"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        $this->db->dbexecute();

        // Log
        $this->logStatusChange($bookingId, null, 'cancelled', $adminId, 'Cancelled by admin: ' . $reason);

        return true;
    }

    private function supplierBookingItemCountSql(): string
    {
        return "(SELECT COUNT(*)
                FROM booking_items bi_count
                LEFT JOIN services s_count ON bi_count.item_id = s_count.id AND bi_count.item_type = 'service'
                LEFT JOIN supplier_packages sp_count ON bi_count.item_id = sp_count.id AND bi_count.item_type = 'supplier_package'
                WHERE bi_count.booking_id = b.id
                  AND (
                      s_count.supplier_id = bs.supplier_id
                      OR sp_count.supplier_id = bs.supplier_id
                      OR (
                          bi_count.item_type = 'package'
                          AND EXISTS (
                              SELECT 1
                              FROM package_items pi_count
                              LEFT JOIN services pi_service_count ON pi_service_count.id = pi_count.service_id
                              WHERE pi_count.package_id = bi_count.item_id
                                AND (
                                    pi_count.default_supplier_id = bs.supplier_id
                                    OR pi_service_count.supplier_id = bs.supplier_id
                                )
                          )
                      )
                  ))";
    }

    private function supplierBookingItemTotalSql(): string
    {
        return "(SELECT COALESCE(SUM(bi_total.price), 0)
                FROM booking_items bi_total
                LEFT JOIN services s_total ON bi_total.item_id = s_total.id AND bi_total.item_type = 'service'
                LEFT JOIN supplier_packages sp_total ON bi_total.item_id = sp_total.id AND bi_total.item_type = 'supplier_package'
                WHERE bi_total.booking_id = b.id
                  AND (
                      s_total.supplier_id = bs.supplier_id
                      OR sp_total.supplier_id = bs.supplier_id
                      OR (
                          bi_total.item_type = 'package'
                          AND EXISTS (
                              SELECT 1
                              FROM package_items pi_total
                              LEFT JOIN services pi_service_total ON pi_service_total.id = pi_total.service_id
                              WHERE pi_total.package_id = bi_total.item_id
                                AND (
                                    pi_total.default_supplier_id = bs.supplier_id
                                    OR pi_service_total.supplier_id = bs.supplier_id
                                )
                          )
                      )
                  ))";
    }

    /* ─── Dual-Flow Booking Helpers ────────────────────────────────── */

    /**
     * Returns true if ALL booking items have source='package' (no custom services).
     * Package bookings auto-confirm on payment; custom bookings require supplier approval first.
     */
    public function isPackageBooking(int $bookingId): bool
    {
        if (!$this->hasBookingSourceColumn()) {
            return false;
        }

        $this->db->dbquery(
            "SELECT COUNT(*) AS total,
                    SUM(CASE WHEN source = 'package' THEN 1 ELSE 0 END) AS package_count
             FROM booking_items WHERE booking_id = :bid"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        $result = $this->db->getsingledata();
        $total = (int)($result['total'] ?? 0);
        $packageCount = (int)($result['package_count'] ?? 0);
        return $total > 0 && $total === $packageCount;
    }

    /**
     * Returns true if ANY booking item has source='custom' (mixed or all-custom).
     * Mixed bookings are treated as custom: supplier must accept before customer pays.
     */
    public function isCustomServiceBooking(int $bookingId): bool
    {
        return !$this->isPackageBooking($bookingId);
    }

    /**
     * Auto-confirm all pending suppliers and their booking items.
     * Used for package bookings after payment — no manual supplier response needed.
     * Caller is responsible for updating the booking's own status.
     */
    public function autoConfirmAllSuppliers(int $bookingId): bool
    {
        $this->db->dbquery(
            "UPDATE booking_suppliers
             SET status = 'confirmed', confirmed_at = COALESCE(confirmed_at, NOW())
             WHERE booking_id = :bid AND status NOT IN ('rejected', 'cancelled')"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        if (!$this->db->dbexecute()) {
            return false;
        }

        $this->db->dbquery(
            "UPDATE booking_items SET status = 'accepted'
             WHERE booking_id = :bid AND status = 'pending'"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        return $this->db->dbexecute();
    }

    /**
     * Returns true if all booking_suppliers rows are 'confirmed'.
     * Used in custom-service flow to decide when to advance to pending_payment.
     */
    public function allSuppliersAccepted(int $bookingId): bool
    {
        $this->db->dbquery(
            "SELECT COUNT(*) AS total,
                    SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) AS confirmed_count
             FROM booking_suppliers WHERE booking_id = :bid"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        $result = $this->db->getsingledata();
        $total = (int)($result['total'] ?? 0);
        $confirmed = (int)($result['confirmed_count'] ?? 0);
        return $total > 0 && $total === $confirmed;
    }

    /**
     * Set the 48-hour window for supplier to respond to a custom booking.
     * @param string $modifier A strtotime-compatible relative string, e.g. '+48 hours'
     */
    public function setSupplierResponseDeadline(int $bookingId, string $modifier): bool
    {
        if (!$this->hasBookingSupplierDeadlineColumn()) {
            return true;
        }

        $deadline = date('Y-m-d H:i:s', strtotime($modifier));
        $this->db->dbquery(
            "UPDATE bookings SET supplier_response_deadline = :deadline WHERE id = :id LIMIT 1"
        );
        $this->db->dbbind(':deadline', $deadline);
        $this->db->dbbind(':id', $bookingId, PDO::PARAM_INT);
        return $this->db->dbexecute();
    }

    /**
     * Cancel all booking_suppliers rows for a booking (used when a supplier declines).
     */
    public function cancelAllSuppliers(int $bookingId): bool
    {
        $this->db->dbquery(
            "UPDATE booking_suppliers SET status = 'cancelled'
             WHERE booking_id = :bid AND status NOT IN ('completed')"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        return $this->db->dbexecute();
    }

    /**
     * Auto-expire booking requests where the 48-hour supplier response window has passed.
     * Returns the number of bookings cancelled, for cron job reporting.
     */
    public function expireOverdueBookingRequests(): int
    {
        if (!$this->hasBookingSupplierDeadlineColumn()) {
            return 0;
        }

        $this->db->dbquery(
            "SELECT id FROM bookings
             WHERE status = 'pending_supplier_response'
               AND supplier_response_deadline IS NOT NULL
               AND supplier_response_deadline < NOW()"
        );
        $expired = $this->db->getmultidata();

        $count = 0;
        foreach ($expired as $row) {
            $bookingId = (int)$row['id'];
            $this->db->dbquery(
                "UPDATE bookings SET status = 'cancelled' WHERE id = :id LIMIT 1"
            );
            $this->db->dbbind(':id', $bookingId, PDO::PARAM_INT);
            if ($this->db->dbexecute()) {
                $this->logStatusChange($bookingId, 'pending_supplier_response', 'cancelled', null, 'Auto-expired: 48-hour supplier response deadline passed');
                $this->cancelAllSuppliers($bookingId);
                $count++;
            }
        }

        return $count;
    }

    /* ─── Payment Verification & Gating ────────────────────────────── */

    /**
     * Check if a booking's payment has been verified.
     * Returns true if payment has been accepted or the booking is later in the flow.
     */
    public function isPaymentVerified(int $bookingId): bool
    {
        $allowedStatuses = ['paid', 'payment_verified', 'suppliers_responding', 'confirmed', 'pending_final_payment', 'finalized', 'completed'];

        $this->db->dbquery("SELECT status FROM bookings WHERE id = :id LIMIT 1");
        $this->db->dbbind(':id', $bookingId, PDO::PARAM_INT);
        $booking = $this->db->getsingledata();

        return $booking && in_array($booking['status'], $allowedStatuses, true);
    }

    /**
     * Returns true if this booking is in the custom-service supplier-first flow
     * (waiting for supplier to accept before payment is collected).
     */
    public function isPendingSupplierResponse(int $bookingId): bool
    {
        $this->db->dbquery("SELECT status FROM bookings WHERE id = :id LIMIT 1");
        $this->db->dbbind(':id', $bookingId, PDO::PARAM_INT);
        $booking = $this->db->getsingledata();
        return $booking && $booking['status'] === 'pending_supplier_response';
    }

    /**
     * Submit manual bank transfer proof for admin verification.
     * Sets booking status to 'payment_submitted' and creates a pending payment record.
     */
    public function submitPaymentSlip(
        int $bookingId,
        string $slipPath,
        string $reference,
        string $method,
        string $accountName = '',
        string $mobileNumber = '',
        float $paidAmount = 0.0,
        string $paidAt = ''
    ): bool {
        $status = $this->normalizeBookingStatus('payment_submitted');
        $this->db->dbquery("UPDATE bookings SET status = :status WHERE id = :id LIMIT 1");
        $this->db->dbbind(':status', $status);
        $this->db->dbbind(':id', $bookingId, PDO::PARAM_INT);

        if (!$this->db->dbexecute()) {
            return false;
        }

        $columns = ['booking_id', 'type', 'method', 'status', 'transaction_ref', 'escrow_status'];
        $values = [':bid', "'deposit'", ':method', "'pending'", ':ref', "'held'"];
        $bindings = [
            ':bid' => [$bookingId, PDO::PARAM_INT],
            ':method' => [$method, PDO::PARAM_STR],
            ':ref' => [$reference, PDO::PARAM_STR],
        ];

        $optionalColumns = [
            'bank_name' => [$method, PDO::PARAM_STR],
            'account_name' => [$accountName !== '' ? $accountName : null, $accountName !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL],
            'mobile_number' => [$mobileNumber !== '' ? $mobileNumber : null, $mobileNumber !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL],
            'paid_amount' => [$paidAmount > 0 ? round($paidAmount, 2) : null, $paidAmount > 0 ? PDO::PARAM_STR : PDO::PARAM_NULL],
            'paid_at' => [$paidAt !== '' ? $paidAt : null, $paidAt !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL],
            'payment_slip_path' => [$slipPath !== '' ? $slipPath : null, $slipPath !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL],
        ];

        foreach ($optionalColumns as $column => [$value, $type]) {
            if (!$this->paymentHasColumn($column)) {
                continue;
            }
            $param = ':' . $column;
            $columns[] = $column;
            $values[] = $param;
            $bindings[$param] = [$value, $type];
        }

        $this->db->dbquery(
            'INSERT INTO payments (' . implode(', ', $columns) . ')
             VALUES (' . implode(', ', $values) . ')'
        );
        foreach ($bindings as $param => [$value, $type]) {
            $this->db->dbbind($param, $value, $type);
        }

        return $this->db->dbexecute();
    }

    /**
     * Get the most recent deposit payment record for a booking.
     */
    public function getDepositPayment(int $bookingId): array|false
    {
        $this->db->dbquery(
            "SELECT bank_name, account_name, mobile_number, paid_amount, paid_at,
                    transaction_ref, payment_slip_path, status, verified_note
             FROM payments
             WHERE booking_id = :bid AND type = 'deposit'
             ORDER BY id DESC LIMIT 1"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        return $this->db->getsingledata();
    }

    /**
     * Admin verifies payment and moves booking to paid.
     * Notifies all suppliers that booking is ready for their review.
     */
    public function adminVerifyPayment(int $bookingId, int $adminId, string $note = ''): bool
    {
        // Update booking status (normalized so it works with old + new ENUMs)
        $status = $this->normalizeBookingStatus('paid');
        $this->db->dbquery(
            "UPDATE bookings SET status = :status, payment_status = 'partial' WHERE id = :id LIMIT 1"
        );
        $this->db->dbbind(':status', $status);
        $this->db->dbbind(':id', $bookingId, PDO::PARAM_INT);

        if (!$this->db->dbexecute()) {
            return false;
        }

        // Update payment record to success
        $this->db->dbquery(
            "UPDATE payments SET status = 'success', verified_by = :admin, verified_at = NOW(), verified_note = :note
             WHERE booking_id = :bid AND type = 'deposit' LIMIT 1"
        );
        $this->db->dbbind(':admin', $adminId, PDO::PARAM_INT);
        $this->db->dbbind(':note', $note, PDO::PARAM_STR);
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);

        if (!$this->db->dbexecute()) {
            return false;
        }

        // Sync paid_amount on the booking using what the customer actually transferred
        $this->db->dbquery(
            "SELECT COALESCE(paid_amount, amount, 0) AS deposit_paid
             FROM payments WHERE booking_id = :bid AND type = 'deposit' AND status = 'success'
             ORDER BY id DESC LIMIT 1"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        $payRow = $this->db->getsingledata();
        $depositPaid = (float)($payRow['deposit_paid'] ?? 0);
        if ($depositPaid > 0) {
            $this->updatePaidAmount($bookingId, $depositPaid);
        }

        // For package bookings: auto-confirm all suppliers and advance to confirmed
        $this->db->dbquery("SELECT booking_type FROM bookings WHERE id = :id LIMIT 1");
        $this->db->dbbind(':id', $bookingId, PDO::PARAM_INT);
        $bk = $this->db->getsingledata();
        if (($bk['booking_type'] ?? '') === 'package') {
            $this->autoConfirmAllSuppliers($bookingId);
            $confirmedStatus = $this->normalizeBookingStatus('confirmed');
            $this->db->dbquery("UPDATE bookings SET status = :status WHERE id = :id LIMIT 1");
            $this->db->dbbind(':status', $confirmedStatus);
            $this->db->dbbind(':id', $bookingId, PDO::PARAM_INT);
            $this->db->dbexecute();
            $this->generateVouchers($bookingId);
        }

        return true;
    }

    /**
     * Confirm instant payment (Visa/Card or MM QR).
     * Sets booking status directly to paid and creates success payment record.
     */
    public function confirmInstantPayment(int $bookingId, string $method, string $transactionId, float $amount = 0): bool
    {
        // Update booking to paid (normalized to work with old + new ENUMs).
        $status = $this->normalizeBookingStatus('paid');
        $this->db->dbquery(
            "UPDATE bookings SET status = :status, payment_status = 'partial' WHERE id = :id LIMIT 1"
        );
        $this->db->dbbind(':status', $status);
        $this->db->dbbind(':id', $bookingId, PDO::PARAM_INT);

        if (!$this->db->dbexecute()) {
            return false;
        }

        // Create successful payment record
        $this->db->dbquery(
            "INSERT INTO payments (booking_id, type, method, status, transaction_ref, escrow_status, verified_at)
             VALUES (:bid, 'deposit', :method, 'success', :txn, 'held', NOW())"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        $this->db->dbbind(':method', $method, PDO::PARAM_STR);
        $this->db->dbbind(':txn', $transactionId, PDO::PARAM_STR);

        return $this->db->dbexecute();
    }

    /**
     * Calculate and settle supplier payouts after booking completion.
     * Creates payout records for each supplier based on proportional amount.
     */
    public function settleSupplierPayouts(int $bookingId): bool
    {
        // Get booking details
        $this->db->dbquery("SELECT total_amount, paid_amount FROM bookings WHERE id = :id LIMIT 1");
        $this->db->dbbind(':id', $bookingId, PDO::PARAM_INT);
        $booking = $this->db->getsingledata();

        if (!$booking || (float)$booking['paid_amount'] === 0.0) {
            return false;
        }

        $totalAmount = (float)$booking['total_amount'];
        $paidAmount = (float)$booking['paid_amount'];

        // Get all suppliers and their amounts for this booking
        $this->db->dbquery(
            "SELECT bs.supplier_id,
                    COALESCE(SUM(bi.price), 0) as supplier_service_amount
             FROM booking_suppliers bs
             LEFT JOIN booking_items bi ON bi.booking_id = :bid
             LEFT JOIN services s ON bi.item_id = s.id AND bi.item_type = 'service'
             LEFT JOIN supplier_packages sp ON bi.item_id = sp.id AND bi.item_type = 'supplier_package'
             WHERE bs.booking_id = :bid
               AND (s.supplier_id = bs.supplier_id OR sp.supplier_id = bs.supplier_id OR bi.item_type = 'package')
             GROUP BY bs.supplier_id"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        $suppliers = $this->db->getmultidata();

        if (!$suppliers) {
            return false;
        }

        // Create payout record for each supplier
        foreach ($suppliers as $supplier) {
            $supplierId = (int)$supplier['supplier_id'];
            $supplierServiceAmount = (float)$supplier['supplier_service_amount'];

            // Calculate proportional payout
            $proportion = ($totalAmount > 0) ? ($supplierServiceAmount / $totalAmount) : 0;
            $payoutAmount = $proportion * $paidAmount;

            if ($payoutAmount > 0) {
                $this->db->dbquery(
                    "INSERT INTO payments (booking_id, supplier_id, type, amount, escrow_status, status)
                     VALUES (:bid, :sid, 'payout', :amount, 'released', 'pending')"
                );
                $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
                $this->db->dbbind(':sid', $supplierId, PDO::PARAM_INT);
                $this->db->dbbind(':amount', number_format($payoutAmount, 2, '.', ''), PDO::PARAM_STR);

                if (!$this->db->dbexecute()) {
                    return false;
                }
            }
        }

        return true;
    }

    /* ─── Final Payment Collection & Refund Logic ─────────────────── */

    /**
     * Collect final 90% payment for bookings where event is within 3 days.
     * Returns count of bookings processed, or false on error.
     * Call this via cron job daily.
     */
    public function collectFinalPaymentDueBookings(): int|false
    {
        // Find all CONFIRMED bookings with event_date between now and 3 days from now
        $this->db->dbquery(
            "SELECT b.id, b.total_amount, b.paid_amount
             FROM bookings b
             WHERE b.status = 'confirmed'
               AND EXISTS (
                   SELECT 1 FROM event_details ed
                   WHERE ed.booking_id = b.id
                     AND DATE(ed.event_date) BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 DAY)
               )
             ORDER BY b.created_at ASC"
        );

        $bookings = $this->db->getmultidata();
        if (!$bookings) {
            return 0;
        }

        $processed = 0;
        foreach ($bookings as $booking) {
            $bookingId = (int)$booking['id'];
            $total = (float)$booking['total_amount'];
            $paid = (float)$booking['paid_amount'];

            // Calculate remaining amount (90% not yet paid)
            $remaining = ($total * 0.90) - ($paid - ($total * 0.10));

            if ($remaining > 0) {
                // Create pending final payment record
                if ($this->createFinalPaymentRequest($bookingId, $remaining)) {
                    // Update booking status to pending_final_payment
                    $this->updateStatus($bookingId, 'pending_final_payment');
                    $this->logStatusChange($bookingId, 'confirmed', 'pending_final_payment', null, 'Cron: Final payment due');
                    $processed++;
                }
            }
        }

        return $processed;
    }

    /**
     * Create final payment request record.
     */
    public function createFinalPaymentRequest(int $bookingId, float $amount): bool
    {
        $this->db->dbquery(
            "INSERT INTO payments (booking_id, type, method, status, amount, escrow_status)
             VALUES (:bid, 'remaining', 'auto_collection', 'pending', :amount, 'held')"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        $this->db->dbbind(':amount', number_format($amount, 2, '.', ''), PDO::PARAM_STR);

        return $this->db->dbexecute();
    }

    /**
     * Confirm final payment collection and finalize booking.
     * Called after successful payment gateway confirmation.
     */
    public function confirmFinalPayment(int $bookingId, string $transactionRef): bool
    {
        // Update payment record
        $this->db->dbquery(
            "UPDATE payments SET status = 'success', transaction_ref = :ref, verified_at = NOW()
             WHERE booking_id = :bid AND type = 'remaining' AND status = 'pending' LIMIT 1"
        );
        $this->db->dbbind(':ref', $transactionRef, PDO::PARAM_STR);
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);

        if (!$this->db->dbexecute()) {
            return false;
        }

        // Update booking to finalized
        $this->db->dbquery(
            "UPDATE bookings SET status = 'finalized', paid_amount = total_amount WHERE id = :id LIMIT 1"
        );
        $this->db->dbbind(':id', $bookingId, PDO::PARAM_INT);

        return $this->db->dbexecute();
    }

    /**
     * Mark booking as completed and settle supplier payouts.
     * Called when all suppliers mark work as complete.
     */
    public function markBookingCompleted(int $bookingId): bool
    {
        // Get booking details
        $this->db->dbquery("SELECT status FROM bookings WHERE id = :id LIMIT 1");
        $this->db->dbbind(':id', $bookingId, PDO::PARAM_INT);
        $booking = $this->db->getsingledata();

        if (!$booking || !in_array($booking['status'], ['finalized', 'in_progress'], true)) {
            return false;
        }

        // Update booking status
        $this->db->dbquery(
            "UPDATE bookings SET status = 'completed' WHERE id = :id LIMIT 1"
        );
        $this->db->dbbind(':id', $bookingId, PDO::PARAM_INT);

        if (!$this->db->dbexecute()) {
            return false;
        }

        // Update booking_suppliers to completed
        $this->db->dbquery(
            "UPDATE booking_suppliers SET status = 'completed', completed_at = NOW()
             WHERE booking_id = :bid AND status IN ('in_progress', 'confirmed')"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);

        if (!$this->db->dbexecute()) {
            return false;
        }

        // Settle supplier payouts
        return $this->settleSupplierPayouts($bookingId);
    }

    /**
     * Calculate refund amount based on cancellation timing and policy.
     * Returns [refund_amount, policy_reason]
     */
    public function calculateRefund(int $bookingId): array|false
    {
        // Get booking and event date
        $this->db->dbquery(
            "SELECT b.total_amount, b.paid_amount, ed.event_date
             FROM bookings b
             LEFT JOIN event_details ed ON ed.booking_id = b.id
             WHERE b.id = :id LIMIT 1"
        );
        $this->db->dbbind(':id', $bookingId, PDO::PARAM_INT);
        $booking = $this->db->getsingledata();

        if (!$booking) {
            return false;
        }

        $paidAmount = (float)($booking['paid_amount'] ?? 0);
        $eventDate = $booking['event_date'] ? strtotime($booking['event_date']) : 0;
        $now = time();

        // Calculate days until event
        $daysUntilEvent = $eventDate ? (int)(($eventDate - $now) / 86400) : 999;

        // Refund Policy
        if ($daysUntilEvent >= 7) {
            // More than 7 days: Full refund
            return [$paidAmount, 'Full refund - cancelled 7+ days before event'];
        } elseif ($daysUntilEvent >= 2) {
            // 2-7 days: 50% refund
            return [$paidAmount * 0.50, '50% refund - cancelled 2-7 days before event'];
        } else {
            // Less than 2 days: No refund (non-refundable)
            return [0, 'No refund - cancelled less than 2 days before event'];
        }
    }

    /**
     * Get supplier earnings (unpaid + paid payouts).
     */
    public function getSupplierEarnings(int $supplierId): array
    {
        $this->db->dbquery(
            "SELECT
                COALESCE(SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END), 0) as pending_amount,
                COALESCE(SUM(CASE WHEN status = 'success' THEN amount ELSE 0 END), 0) as paid_amount,
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_count,
                COUNT(CASE WHEN status = 'success' THEN 1 END) as paid_count
             FROM payments
             WHERE supplier_id = :sid AND type = 'payout'"
        );
        $this->db->dbbind(':sid', $supplierId, PDO::PARAM_INT);
        $result = $this->db->getsingledata();

        return [
            'pending_amount' => (float)($result['pending_amount'] ?? 0),
            'paid_amount' => (float)($result['paid_amount'] ?? 0),
            'pending_count' => (int)($result['pending_count'] ?? 0),
            'paid_count' => (int)($result['paid_count'] ?? 0),
            'total_earned' => (float)(($result['pending_amount'] ?? 0) + ($result['paid_amount'] ?? 0)),
        ];
    }

    /**
     * Get supplier payout history with pagination.
     */
    public function getSupplierPayouts(int $supplierId, int $limit = 20, int $offset = 0): array
    {
        $this->db->dbquery(
            "SELECT p.*, b.created_at as booking_date
             FROM payments p
             LEFT JOIN bookings b ON p.booking_id = b.id
             WHERE p.supplier_id = :sid AND p.type = 'payout'
             ORDER BY p.created_at DESC
             LIMIT :limit OFFSET :offset"
        );
        $this->db->dbbind(':sid', $supplierId, PDO::PARAM_INT);
        $this->db->dbbind(':limit', $limit, PDO::PARAM_INT);
        $this->db->dbbind(':offset', $offset, PDO::PARAM_INT);

        return $this->db->getmultidata();
    }
}
