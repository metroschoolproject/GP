<?php

class BookingModel
{
    private $db;
    private ?bool $cartVenueRoomColumn = null;
    private ?bool $bookingVenueRoomColumn = null;

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
        $this->db->dbquery(
            "INSERT INTO bookings (user_id, cart_id, total_amount, paid_amount, payment_status, status)
             VALUES (:uid, :cid, :total, 0.00, 'unpaid', 'draft')"
        );
        $this->db->dbbind(':uid', $userId, PDO::PARAM_INT);
        $this->db->dbbind(':cid', $cartId, PDO::PARAM_INT);
        $this->db->dbbind(':total', number_format($totalAmount, 2, '.', ''));

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
        $cartVenueRoomValue = $hasCartVenueRoomColumn ? 'ci.venue_room_id' : 'NULL';
        $venueRoomInsertColumn = $hasBookingVenueRoomColumn ? ', venue_room_id' : '';
        $venueRoomSelectColumn = $hasBookingVenueRoomColumn ? ", COALESCE({$cartVenueRoomValue}, selected_vra.room_id)" : '';

        $this->db->dbquery(
            "INSERT INTO booking_items (booking_id, item_type, item_id, booking_date, price, status, slot_id, start_time, end_time, booking_type{$venueRoomInsertColumn})
            SELECT :bid, ci.item_type, ci.item_id,
                    CONCAT(ci.selected_date, ' ', COALESCE(ci.start_time, '00:00:00')),
                    COALESCE(ci.price, s.price_min, s.price, p.base_price, sp.total_price, 0),
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
            "SELECT bs.*, sup.shop_name, sup.thumbnail_url
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
        $sql = "SELECT b.*, u.name AS customer_name,
                       bs.status AS supplier_status, bs.id AS booking_supplier_id,
                       (SELECT COUNT(*) FROM booking_items WHERE booking_id = b.id) AS item_count
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
        $this->db->dbquery(
            "SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN bs.status = 'pending' THEN 1 ELSE 0 END) AS pending_count,
                SUM(CASE WHEN bs.status = 'confirmed' THEN 1 ELSE 0 END) AS confirmed_count,
                SUM(CASE WHEN bs.status = 'completed' THEN 1 ELSE 0 END) AS completed_count,
                SUM(CASE WHEN bs.status IN ('cancelled','rejected') THEN 1 ELSE 0 END) AS cancelled_count,
                COALESCE(SUM(CASE WHEN bs.status IN ('confirmed','completed') THEN b.total_amount ELSE 0 END), 0) AS est_revenue
             FROM booking_suppliers bs
             INNER JOIN bookings b ON bs.booking_id = b.id
             WHERE bs.supplier_id = :sid"
        );
        $this->db->dbbind(':sid', $supplierId, PDO::PARAM_INT);
        return $this->db->getsingledata() ?: [];
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
        $this->db->dbbind(':status', 'pending');

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
}
