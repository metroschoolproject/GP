<?php

class BookingModel
{
    private $db;
    private ?bool $cartVenueRoomColumn = null;
    private ?bool $bookingVenueRoomColumn = null;
    private ?bool $cartSourceColumn = null;
    private ?bool $bookingSourceColumn = null;
    private ?bool $cartPackageParentColumn = null;
    private ?bool $bookingPackageParentColumn = null;
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
        $hasCartPackageParentColumn = $this->hasCartPackageParentColumn();
        $hasBookingPackageParentColumn = $this->hasBookingPackageParentColumn();
        $cartVenueRoomValue = $hasCartVenueRoomColumn ? 'ci.venue_room_id' : 'NULL';
        $venueRoomInsertColumn = $hasBookingVenueRoomColumn ? ', venue_room_id' : '';
        $venueRoomSelectColumn = $hasBookingVenueRoomColumn ? ", COALESCE({$cartVenueRoomValue}, selected_vra.room_id)" : '';
        $sourceInsertColumn = $hasBookingSourceColumn ? ', source' : '';
        $sourceSelectColumn = $hasBookingSourceColumn
            ? ', ' . ($hasCartSourceColumn ? "COALESCE(ci.source, 'custom')" : "'custom'")
            : '';
        $packageParentInsertColumn = $hasBookingPackageParentColumn ? ', package_booking_item_id' : '';
        $packageParentSelectColumn = $hasBookingPackageParentColumn ? ', NULL' : '';

        $this->db->dbquery(
            "INSERT INTO booking_items (booking_id, item_type{$sourceInsertColumn}, item_id, booking_date, price,
                    item_name, supplier_name, category_name, thumbnail_url,
                    status, slot_id, start_time, end_time, booking_type{$venueRoomInsertColumn}{$packageParentInsertColumn})
            SELECT :bid, ci.item_type{$sourceSelectColumn}, ci.item_id,
                    CONCAT(ci.selected_date, ' ', COALESCE(ci.start_time, '00:00:00')),
                    CASE
                        WHEN ci.item_type = 'package' THEN COALESCE(p.base_price * 1.05, ci.price, 0)
                        ELSE COALESCE(ci.price, s.price_min, s.price, sp.total_price, 0)
                    END,
                    COALESCE(s.name, p.name, sp.name),
                    COALESCE(sup.shop_name, sp_sup.shop_name, 'Golden Promise'),
                    cat.name,
                    COALESCE(s.thumbnail_url, p.image_url, sp.thumbnail_url),
                    'pending',
                    ci.slot_id, ci.start_time, ci.end_time,
                    COALESCE(s.booking_type, 'fullday'){$venueRoomSelectColumn}{$packageParentSelectColumn}
            FROM cart_items ci
            LEFT JOIN services s ON ci.item_id = s.id AND ci.item_type = 'service'
            LEFT JOIN packages p ON ci.item_id = p.package_id AND ci.item_type = 'package'
            LEFT JOIN supplier_packages sp ON ci.item_id = sp.id AND ci.item_type = 'supplier_package'
            LEFT JOIN suppliers sup ON s.supplier_id = sup.supplier_id
            LEFT JOIN suppliers sp_sup ON sp.supplier_id = sp_sup.supplier_id
            LEFT JOIN categories cat ON s.category_id = cat.id
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

        if ($hasCartPackageParentColumn && $hasBookingPackageParentColumn && !empty($ids)) {
            $this->db->dbquery(
                "SELECT id, package_cart_item_id
                 FROM cart_items
                 WHERE user_id = :uid
                 ORDER BY id DESC"
            );
            $this->db->dbbind(':uid', $userId, PDO::PARAM_INT);
            $cartRows = $this->db->getmultidata();

            $bookingIdByCartId = [];
            foreach ($cartRows as $index => $cartRow) {
                if (isset($ids[$index])) {
                    $bookingIdByCartId[(int)$cartRow['id']] = (int)$ids[$index];
                }
            }

            foreach ($cartRows as $index => $cartRow) {
                $parentCartItemId = (int)($cartRow['package_cart_item_id'] ?? 0);
                if ($parentCartItemId <= 0 || empty($ids[$index]) || empty($bookingIdByCartId[$parentCartItemId])) {
                    continue;
                }

                $this->db->dbquery(
                    "UPDATE booking_items
                     SET package_booking_item_id = :parent_id
                     WHERE id = :item_id AND booking_id = :booking_id
                     LIMIT 1"
                );
                $this->db->dbbind(':parent_id', $bookingIdByCartId[$parentCartItemId], PDO::PARAM_INT);
                $this->db->dbbind(':item_id', (int)$ids[$index], PDO::PARAM_INT);
                $this->db->dbbind(':booking_id', $bookingId, PDO::PARAM_INT);
                if (!$this->db->dbexecute()) {
                    return false;
                }
            }
        }

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

    private function hasCartPackageParentColumn(): bool
    {
        if ($this->cartPackageParentColumn !== null) {
            return $this->cartPackageParentColumn;
        }

        $this->db->dbquery("SHOW COLUMNS FROM cart_items LIKE 'package_cart_item_id'");
        $this->cartPackageParentColumn = (bool)$this->db->getsingledata();
        return $this->cartPackageParentColumn;
    }

    private function hasBookingPackageParentColumn(): bool
    {
        if ($this->bookingPackageParentColumn !== null) {
            return $this->bookingPackageParentColumn;
        }

        $this->db->dbquery("SHOW COLUMNS FROM booking_items LIKE 'package_booking_item_id'");
        $this->bookingPackageParentColumn = (bool)$this->db->getsingledata();
        return $this->bookingPackageParentColumn;
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
     * Reserve per-service time slots for every service inside a package.
     * Creates service_time_slot entries for slot-type services and increments
     * their confirmed_count + confirmed_package_count so they're blocked from double booking.
     *
     * Each slot inherits the service's max_concurrent and pool limits.
     *
     * Returns the array of resolved per-service schedule rows
     * (enriched with event_date, start_time, end_time, booking_type,
     *  supplier_name, category_name etc.) suitable for display.
     *
     * @return array<int,array>  indexed by service_id
     */
    public function reservePackageServiceSlots(string $eventDate, array $packageSchedule): array
    {
        foreach ($packageSchedule as $event) {
            if (($event['booking_type'] ?? '') === 'slot') {
                $svcId = (int)($event['service_id'] ?? 0);
                $svcConcurrent = $this->getServiceConcurrent($svcId);

                // Per-package override: a package_item.max_concurrent > 0 caps how
                // many bookings of this service through this package can share the
                // wedding date. 0 (or unset) falls back to the service's package cap.
                $itemMaxConcurrent = (int)($event['item_max_concurrent'] ?? 0);
                $packageCap = $itemMaxConcurrent > 0
                    ? $itemMaxConcurrent
                    : (int)($svcConcurrent['max_concurrent_package'] ?? 0);

                $slotId = $this->findOrCreateServiceSlot(
                    $svcId,
                    $eventDate,
                    (string)($event['start_time'] ?? '09:00:00'),
                    (string)($event['end_time'] ?? '10:00:00'),
                    $svcConcurrent['max_concurrent'] ?? 1,
                    $packageCap,
                    $svcConcurrent['max_concurrent_customize'] ?? 0
                );
                if ($slotId) {
                    $this->reserveServiceSlot($slotId, 'package');
                }
            }
        }
        return $packageSchedule;
    }

    /**
     * Load service concurrency settings.
     * @return array{max_concurrent:int, max_concurrent_package:int, max_concurrent_customize:int}
     */
    private function getServiceConcurrent(int $serviceId): array
    {
        $defaults = ['max_concurrent' => 1, 'max_concurrent_package' => 0, 'max_concurrent_customize' => 0];
        if ($serviceId <= 0) {
            return $defaults;
        }
        $this->db->dbquery(
            'SELECT max_concurrent, max_concurrent_package, max_concurrent_customize
             FROM services WHERE id = :id LIMIT 1'
        );
        $this->db->dbbind(':id', $serviceId, PDO::PARAM_INT);
        $row = $this->db->getsingledata();
        if (!$row) {
            return $defaults;
        }
        return [
            'max_concurrent' => (int)($row['max_concurrent'] ?? 1),
            'max_concurrent_package' => (int)($row['max_concurrent_package'] ?? 0),
            'max_concurrent_customize' => (int)($row['max_concurrent_customize'] ?? 0),
        ];
    }

    /**
     * Find an existing slot or insert a new one for a service on a given date/time.
     * Returns the slot ID or null on failure.
     */
    private function findOrCreateServiceSlot(
        int $serviceId, string $date, string $startTime, string $endTime,
        int $maxConcurrent, int $maxConcurrentPackage = 0, int $maxConcurrentCustomize = 0
    ): ?int
    {
        $this->db->dbquery(
            "SELECT id FROM service_time_slots
             WHERE service_id = :sid AND date = :date AND start_time = :stime AND end_time = :etime
             LIMIT 1"
        );
        $this->db->dbbind(':sid', $serviceId, PDO::PARAM_INT);
        $this->db->dbbind(':date', $date);
        $this->db->dbbind(':stime', $startTime);
        $this->db->dbbind(':etime', $endTime);
        $existing = $this->db->getsingledata();
        if ($existing) {
            // Slot may have been created earlier (e.g. supplier preview) with a 0
            // package cap. Apply the package's per-item cap if it sets a tighter
            // limit, so the override still governs. Never loosen an existing cap to 0.
            if ($this->hasSlotPoolColumns() && $maxConcurrentPackage > 0) {
                $this->db->dbquery(
                    "UPDATE service_time_slots
                     SET max_concurrent_package = :maxcp
                     WHERE id = :id
                       AND (max_concurrent_package = 0 OR max_concurrent_package > :maxcp2)"
                );
                $this->db->dbbind(':maxcp', $maxConcurrentPackage, PDO::PARAM_INT);
                $this->db->dbbind(':maxcp2', $maxConcurrentPackage, PDO::PARAM_INT);
                $this->db->dbbind(':id', (int)$existing['id'], PDO::PARAM_INT);
                $this->db->dbexecute();
            }
            return (int)$existing['id'];
        }

        // Use dynamic column detection for pool columns
        $hasPools = $this->hasSlotPoolColumns();
        $poolCols = $hasPools ? ', confirmed_package_count, confirmed_customize_count, max_concurrent_package, max_concurrent_customize' : '';
        $poolVals = $hasPools ? ', 0, 0, :maxcp, :maxcc' : '';

        $this->db->dbquery(
            "INSERT INTO service_time_slots (service_id, date, start_time, end_time, confirmed_count, max_concurrent, status{$poolCols})
             VALUES (:sid, :date, :stime, :etime, 0, :maxc, 'available'{$poolVals})"
        );
        $this->db->dbbind(':sid', $serviceId, PDO::PARAM_INT);
        $this->db->dbbind(':date', $date);
        $this->db->dbbind(':stime', $startTime);
        $this->db->dbbind(':etime', $endTime);
        $this->db->dbbind(':maxc', $maxConcurrent, PDO::PARAM_INT);
        if ($hasPools) {
            $this->db->dbbind(':maxcp', $maxConcurrentPackage, PDO::PARAM_INT);
            $this->db->dbbind(':maxcc', $maxConcurrentCustomize, PDO::PARAM_INT);
        }

        return $this->db->dbexecute() ? (int)$this->db->lastInsertId() : null;
    }

    /**
     * Increment the proper confirmed_count on a service_time_slot
     * (total + pool-specific).
     */
    private function reserveServiceSlot(int $slotId, string $source = 'custom'): bool
    {
        $poolCol = $source === 'package' ? 'confirmed_package_count' : 'confirmed_customize_count';
        $poolCheck = $source === 'package'
            ? 'AND (st.max_concurrent_package = 0 OR st.confirmed_package_count < st.max_concurrent_package)'
            : 'AND (st.max_concurrent_customize = 0 OR st.confirmed_customize_count < st.max_concurrent_customize)';

        $hasPools = $this->hasSlotPoolColumns();
        $poolUpdate = $hasPools ? ", {$poolCol} = {$poolCol} + 1" : '';

        $this->db->dbquery(
            "UPDATE service_time_slots
             SET confirmed_count = confirmed_count + 1{$poolUpdate},
                 status = CASE WHEN confirmed_count + 1 >= max_concurrent THEN 'full' ELSE 'available' END
             WHERE id = (
                SELECT id FROM (
                    SELECT st.id
                    FROM service_time_slots st
                    WHERE st.id = :id
                      AND st.status = 'available'
                      AND st.confirmed_count < st.max_concurrent"
                      . ($hasPools ? " {$poolCheck}" : '') . "
                    LIMIT 1
                ) AS target
             )"
        );
        $this->db->dbbind(':id', $slotId, PDO::PARAM_INT);
        $this->db->dbexecute();
        return $this->db->rowcount() > 0;
    }

    /**
     * Check if service_time_slots has pool concurrency columns.
     */
    private function hasSlotPoolColumns(): bool
    {
        static $has = null;
        if ($has !== null) {
            return $has;
        }
        try {
            $this->db->dbquery("SHOW COLUMNS FROM service_time_slots LIKE 'max_concurrent_package'");
            $has = (bool)$this->db->getsingledata();
        } catch (Throwable $e) {
            $has = false;
        }
        return $has;
    }

    /**
     * Link supplier(s) to the booking via booking_suppliers.
     */
    public function insertBookingSuppliers(int $bookingId): bool
    {
        // One row per SERVICE LINE (not per supplier): a package expands to one
        // row per package_item, so a supplier with several services in the
        // package gets one row each — enabling per-service decline/replacement.
        // The uniq_booking_pkg_item (booking_id, package_item_id) key keeps
        // package lines idempotent; direct/supplier-package lines (NULL
        // package_item_id) are distinct per service within a single insert.
        $this->db->dbquery(
            "INSERT IGNORE INTO booking_suppliers
                (booking_id, supplier_id, service_id, category_id, package_item_id, item_price)
             SELECT svc_lines.booking_id, svc_lines.supplier_id, svc_lines.service_id,
                    svc_lines.category_id, svc_lines.package_item_id, svc_lines.item_price
             FROM (
                -- Package service lines (one per package_item)
                SELECT bi.booking_id AS booking_id,
                       pi.default_supplier_id AS supplier_id,
                       pi.service_id AS service_id,
                       pi.category_id AS category_id,
                       pi.id AS package_item_id,
                       COALESCE(pi.default_price, pi.customize_price) AS item_price
                FROM booking_items bi
                INNER JOIN package_items pi
                    ON pi.package_id = bi.item_id AND bi.item_type = 'package'
                   AND pi.deleted_at IS NULL
                WHERE bi.booking_id = :package_bid

                UNION ALL

                -- Direct service lines
                SELECT bi.booking_id, s.supplier_id, s.id, s.category_id, NULL, bi.price
                FROM booking_items bi
                INNER JOIN services s ON bi.item_id = s.id AND bi.item_type = 'service'
                WHERE bi.booking_id = :service_bid

                UNION ALL

                -- Supplier-package service lines
                SELECT bi.booking_id, s2.supplier_id, s2.id, s2.category_id, NULL, NULL
                FROM booking_items bi
                INNER JOIN supplier_package_items spi
                    ON spi.package_id = bi.item_id AND bi.item_type = 'supplier_package'
                INNER JOIN services s2 ON spi.service_id = s2.id
                WHERE bi.booking_id = :supplier_package_items_bid
             ) svc_lines
             WHERE svc_lines.supplier_id IS NOT NULL"
        );
        $this->db->dbbind(':package_bid', $bookingId, PDO::PARAM_INT);
        $this->db->dbbind(':service_bid', $bookingId, PDO::PARAM_INT);
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
        $packageParentSelect = $this->hasBookingPackageParentColumn()
            ? "bi.package_booking_item_id,
                    parent_package.package_id AS addon_package_id,
                    COALESCE(parent_bi.item_name, parent_package.name) AS addon_package_name,"
            : "NULL AS package_booking_item_id,
                    NULL AS addon_package_id,
                    NULL AS addon_package_name,";
        $packageParentJoin = $this->hasBookingPackageParentColumn()
            ? "LEFT JOIN booking_items parent_bi
                     ON parent_bi.id = bi.package_booking_item_id
                    AND parent_bi.booking_id = bi.booking_id
                    AND parent_bi.item_type = 'package'
               LEFT JOIN packages parent_package ON parent_package.package_id = parent_bi.item_id"
            : '';

        $this->db->dbquery(
            "SELECT bi.*,
                    {$packageParentSelect}
                    COALESCE(bi.item_name, s.name, p.name, sp.name) AS service_name,
                    COALESCE(bi.thumbnail_url, s.thumbnail_url, p.image_url, sp.thumbnail_url) AS thumbnail_url,
                    COALESCE(bi.supplier_name, sup.shop_name, sp_sup.shop_name, 'Golden Promise') AS supplier_name,
                    sup.supplier_id,
                    COALESCE(bi.category_name, cat.name) AS category_name,
                    {$bookingVenueSelect}
             FROM booking_items bi
             LEFT JOIN services s ON bi.item_id = s.id AND bi.item_type = 'service'
             LEFT JOIN packages p ON bi.item_id = p.package_id AND bi.item_type = 'package'
             {$packageParentJoin}
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
        $packageParentSelect = $this->hasBookingPackageParentColumn()
            ? "bi.package_booking_item_id,
                    COALESCE(parent_bi.item_name, parent_package.name) AS addon_package_name,"
            : "NULL AS package_booking_item_id,
                    NULL AS addon_package_name,";
        $packageParentJoin = $this->hasBookingPackageParentColumn()
            ? "LEFT JOIN booking_items parent_bi
                     ON parent_bi.id = bi.package_booking_item_id
                    AND parent_bi.booking_id = bi.booking_id
                    AND parent_bi.item_type = 'package'
               LEFT JOIN packages parent_package ON parent_package.package_id = parent_bi.item_id"
            : '';

        $this->db->dbquery(
            "SELECT bi.*,
                    {$packageParentSelect}
                    COALESCE(bi.item_name, s.name, p.name, sp.name) AS service_name,
                    COALESCE(bi.thumbnail_url, s.thumbnail_url, p.image_url, sp.thumbnail_url) AS thumbnail_url,
                    COALESCE(bi.supplier_name, sup.shop_name, sp_sup.shop_name, 'Golden Promise') AS supplier_name,
                    COALESCE(sup.supplier_id, sp_sup.supplier_id) AS supplier_id,
                    COALESCE(bi.category_name, cat.name) AS category_name,
                    {$bookingVenueSelect}
             FROM booking_items bi
             LEFT JOIN services s ON bi.item_id = s.id AND bi.item_type = 'service'
             LEFT JOIN packages p ON bi.item_id = p.package_id AND bi.item_type = 'package'
             {$packageParentJoin}
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
     * Whole days from today until the booking's earliest event date.
     * Returns null when no event date is set. Negative if the date has passed.
     */
    public function daysUntilFirstEvent(int $bookingId): ?int
    {
        $this->db->dbquery(
            "SELECT MIN(event_date) AS event_date
             FROM event_details
             WHERE booking_id = :bid AND event_date IS NOT NULL"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        $row = $this->db->getsingledata();
        $eventDate = $row['event_date'] ?? null;
        if (empty($eventDate)) {
            return null;
        }

        $today = new DateTimeImmutable('today');
        $target = DateTimeImmutable::createFromFormat('!Y-m-d', substr((string)$eventDate, 0, 10));
        if (!$target) {
            return null;
        }

        return (int)$today->diff($target)->format('%r%a');
    }

    /**
     * Get booking suppliers.
     */
    public function getBookingSuppliers(int $bookingId): array
    {
        $this->db->dbquery(
            "SELECT bs.*,
                    sup.shop_name,
                    svc.name AS service_name,
                    cat.name AS category_name,
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
             LEFT JOIN services svc ON svc.id = bs.service_id
             LEFT JOIN categories cat ON cat.id = bs.category_id
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
    public function getCustomerBookings(int $userId, ?string $statusFilter = null, int $limit = 12, int $offset = 0): array
    {
        $sql = "SELECT b.*,
                       (SELECT COUNT(*) FROM booking_items WHERE booking_id = b.id) AS item_count
                FROM bookings b
                WHERE b.user_id = :uid";

        if ($statusFilter && $statusFilter !== 'all') {
            $sql .= " AND b.status = :status";
        }

        $sql .= " ORDER BY b.created_at DESC LIMIT :limit OFFSET :offset";

        $this->db->dbquery($sql);
        $this->db->dbbind(':uid', $userId, PDO::PARAM_INT);
        if ($statusFilter && $statusFilter !== 'all') {
            $this->db->dbbind(':status', $statusFilter);
        }
        $this->db->dbbind(':limit', $limit, PDO::PARAM_INT);
        $this->db->dbbind(':offset', $offset, PDO::PARAM_INT);

        return $this->db->getmultidata();
    }

    public function getCustomerBookingsCount(int $userId, ?string $statusFilter = null): int
    {
        $sql = "SELECT COUNT(*) AS total FROM bookings b WHERE b.user_id = :uid";

        if ($statusFilter && $statusFilter !== 'all') {
            $sql .= " AND b.status = :status";
        }

        $this->db->dbquery($sql);
        $this->db->dbbind(':uid', $userId, PDO::PARAM_INT);
        if ($statusFilter && $statusFilter !== 'all') {
            $this->db->dbbind(':status', $statusFilter);
        }
        return (int)($this->db->getsingledata()['total'] ?? 0);
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
        // A supplier can now have several service rows per booking. Collapse to
        // one row per booking and surface the most actionable status (a pending
        // replacement outranks a plain confirm).
        $statusExpr = $this->supplierAggregateStatusSql();
        $sql = "SELECT b.*, u.name AS customer_name, u.phone AS customer_phone,
                       {$statusExpr} AS supplier_status,
                       MIN(bs.id) AS booking_supplier_id,
                       {$supplierItemCountSql} AS item_count,
                       {$supplierItemTotalSql} AS supplier_total_amount,
                       (SELECT event_date FROM event_details WHERE booking_id = b.id LIMIT 1) AS event_date
                FROM bookings b
                INNER JOIN booking_suppliers bs ON b.id = bs.booking_id
                LEFT JOIN users u ON b.user_id = u.user_id
                WHERE bs.supplier_id = :sid
                  AND b.status NOT IN ('draft', 'pending_payment', 'payment_submitted')";

        if ($statusFilter && $statusFilter !== 'all') {
            $sql .= " AND EXISTS (SELECT 1 FROM booking_suppliers bsf
                                  WHERE bsf.booking_id = b.id AND bsf.supplier_id = :sid_f
                                    AND bsf.status = :status)";
        }

        $sql .= " GROUP BY b.id ORDER BY b.created_at DESC LIMIT :limit OFFSET :offset";

        $this->db->dbquery($sql);
        $this->db->dbbind(':sid', $supplierId, PDO::PARAM_INT);
        $this->db->dbbind(':sid_status', $supplierId, PDO::PARAM_INT);
        if ($statusFilter && $statusFilter !== 'all') {
            $this->db->dbbind(':sid_f', $supplierId, PDO::PARAM_INT);
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
        $sql = "SELECT COUNT(DISTINCT b.id) as total
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

        $statusExpr = $this->supplierAggregateStatusSql();
        $sql = "SELECT b.*, u.name AS customer_name, u.phone AS customer_phone,
                       {$statusExpr} AS supplier_status,
                       MIN(bs.id) AS booking_supplier_id,
                       {$supplierItemCountSql} AS item_count,
                       {$supplierItemTotalSql} AS supplier_total_amount,
                       (SELECT event_date FROM event_details WHERE booking_id = b.id LIMIT 1) AS event_date
                FROM bookings b
                INNER JOIN booking_suppliers bs ON b.id = bs.booking_id
                LEFT JOIN users u ON b.user_id = u.user_id
                WHERE bs.supplier_id = :sid
                AND (u.name LIKE :search OR u.phone LIKE :search2 OR CONCAT('BK', b.id) LIKE :search3)";

        if ($statusFilter && $statusFilter !== 'all') {
            $sql .= " AND EXISTS (SELECT 1 FROM booking_suppliers bsf
                                  WHERE bsf.booking_id = b.id AND bsf.supplier_id = :sid_f
                                    AND bsf.status = :status)";
        }

        $sql .= " GROUP BY b.id ORDER BY b.created_at DESC LIMIT :limit OFFSET :offset";

        $this->db->dbquery($sql);
        $this->db->dbbind(':sid', $supplierId, PDO::PARAM_INT);
        $this->db->dbbind(':sid_status', $supplierId, PDO::PARAM_INT);
        $this->db->dbbind(':search', $searchTerm);
        $this->db->dbbind(':search2', $searchTerm);
        $this->db->dbbind(':search3', $searchTerm);
        if ($statusFilter && $statusFilter !== 'all') {
            $this->db->dbbind(':sid_f', $supplierId, PDO::PARAM_INT);
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

        $sql = "SELECT COUNT(DISTINCT b.id) as total
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
    public function getAllBookings(?string $statusFilter = null, ?string $search = null, int $limit = 15, int $offset = 0): array
    {
        $sql = "SELECT b.*, u.name AS customer_name, u.email AS customer_email, u.phone AS customer_phone,
                       (SELECT event_date FROM event_details ed WHERE ed.booking_id = b.id ORDER BY ed.event_date ASC LIMIT 1) AS event_date,
                       (SELECT GROUP_CONCAT(DISTINCT sup.shop_name SEPARATOR ', ')
                        FROM booking_suppliers bs2
                        LEFT JOIN suppliers sup ON bs2.supplier_id = sup.supplier_id
                        WHERE bs2.booking_id = b.id) AS supplier_names
                FROM bookings b
                LEFT JOIN users u ON b.user_id = u.user_id
                WHERE 1=1";

        $params = [];

        if ($statusFilter === 'pending_payment') {
            $sql .= " AND b.status IN ('pending_payment', 'payment_submitted')";
        } elseif ($statusFilter && $statusFilter !== 'all') {
            $sql .= " AND b.status = :status";
            $params[':status'] = $statusFilter;
        }

        if ($search && trim($search) !== '') {
            $sql .= " AND (
                b.id LIKE :search
                OR u.name LIKE :search2
                OR u.email LIKE :search3
                OR u.phone LIKE :search4
                OR EXISTS (
                    SELECT 1
                    FROM booking_suppliers bs3
                    LEFT JOIN suppliers sup3 ON bs3.supplier_id = sup3.supplier_id
                    WHERE bs3.booking_id = b.id AND sup3.shop_name LIKE :search5
                )
            )";
            $params[':search'] = '%' . $search . '%';
            $params[':search2'] = '%' . $search . '%';
            $params[':search3'] = '%' . $search . '%';
            $params[':search4'] = '%' . $search . '%';
            $params[':search5'] = '%' . $search . '%';
        }

        $sql .= " ORDER BY b.created_at DESC LIMIT :limit OFFSET :offset";

        $this->db->dbquery($sql);
        foreach ($params as $key => $val) {
            $this->db->dbbind($key, $val);
        }
        $this->db->dbbind(':limit', $limit, PDO::PARAM_INT);
        $this->db->dbbind(':offset', $offset, PDO::PARAM_INT);

        return $this->db->getmultidata();
    }

    public function getAllBookingsCount(?string $statusFilter = null, ?string $search = null): int
    {
        $sql = "SELECT COUNT(*) AS total FROM bookings b
                LEFT JOIN users u ON b.user_id = u.user_id
                WHERE 1=1";

        $params = [];

        if ($statusFilter === 'pending_payment') {
            $sql .= " AND b.status IN ('pending_payment', 'payment_submitted')";
        } elseif ($statusFilter && $statusFilter !== 'all') {
            $sql .= " AND b.status = :status";
            $params[':status'] = $statusFilter;
        }

        if ($search && trim($search) !== '') {
            $sql .= " AND (
                b.id LIKE :search
                OR u.name LIKE :search2
                OR u.email LIKE :search3
                OR u.phone LIKE :search4
                OR EXISTS (
                    SELECT 1
                    FROM booking_suppliers bs3
                    LEFT JOIN suppliers sup3 ON bs3.supplier_id = sup3.supplier_id
                    WHERE bs3.booking_id = b.id AND sup3.shop_name LIKE :search5
                )
            )";
            $params[':search'] = '%' . $search . '%';
            $params[':search2'] = '%' . $search . '%';
            $params[':search3'] = '%' . $search . '%';
            $params[':search4'] = '%' . $search . '%';
            $params[':search5'] = '%' . $search . '%';
        }

        $this->db->dbquery($sql);
        foreach ($params as $key => $val) {
            $this->db->dbbind($key, $val);
        }
        return (int)($this->db->getsingledata()['total'] ?? 0);
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
                SUM(CASE WHEN status = 'payment_submitted' THEN 1 ELSE 0 END) AS payment_submitted_count,
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
     * Mark a supplier row as awaiting an admin-chosen replacement (decline on a
     * confirmed package booking). Unlike a plain 'rejected', this keeps the
     * booking alive so the platform can swap in another supplier.
     */
    public function markSupplierNeedsReplacement(int $bookingSupplierId): bool
    {
        $this->db->dbquery(
            "UPDATE booking_suppliers
                SET status = 'needs_replacement', declined_at = NOW()
              WHERE id = :id"
        );
        $this->db->dbbind(':id', $bookingSupplierId, PDO::PARAM_INT);

        return $this->db->dbexecute();
    }

    /**
     * Open a replacement request for a declined supplier on a package booking.
     * Captures what the declined supplier covered (category/service/price) so
     * the admin candidate search can match it. Returns the new replacement id.
     *
     * @param array $supplierRow A booking_suppliers row (bs.*) for the decliner.
     */
    public function createReplacementRequest(int $bookingId, array $supplierRow, ?string $reason = null): int
    {
        $this->db->dbquery(
            "INSERT INTO booking_supplier_replacements
                (booking_id, booking_supplier_id, category_id,
                 old_supplier_id, old_service_id, old_price,
                 status, decline_reason, created_at)
             VALUES
                (:bid, :bsid, :cat,
                 :old_sup, :old_svc, :old_price,
                 'pending_admin', :reason, NOW())"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        $this->db->dbbind(':bsid', (int)($supplierRow['id'] ?? 0), PDO::PARAM_INT);
        $this->db->dbbind(':cat', $supplierRow['category_id'] ?? null);
        $this->db->dbbind(':old_sup', (int)($supplierRow['supplier_id'] ?? 0), PDO::PARAM_INT);
        $this->db->dbbind(':old_svc', $supplierRow['service_id'] ?? null);
        $this->db->dbbind(':old_price', $supplierRow['item_price'] ?? null);
        $this->db->dbbind(':reason', $reason !== '' ? $reason : null);
        $this->db->dbexecute();

        return (int)$this->db->lastinsertid();
    }

    /* ════════════════════════════════════════════════════════════
     *  SUPPLIER REPLACEMENT — candidate search, assign, swap
     *  (see .claude/plans/admin-supplier-replacement-on-decline.md)
     * ════════════════════════════════════════════════════════════ */

    /**
     * Decrement the confirmed counts on a service_time_slot when a reservation
     * is released (mirror of reserveServiceSlot). Re-opens the slot if it drops
     * below capacity.
     */
    private function releaseServiceSlot(int $slotId, string $source = 'custom'): bool
    {
        if ($slotId <= 0) {
            return false;
        }
        $hasPools = $this->hasSlotPoolColumns();
        $poolCol = $source === 'package' ? 'confirmed_package_count' : 'confirmed_customize_count';
        $poolUpdate = $hasPools ? ", {$poolCol} = GREATEST({$poolCol} - 1, 0)" : '';

        $this->db->dbquery(
            "UPDATE service_time_slots
                SET confirmed_count = GREATEST(confirmed_count - 1, 0){$poolUpdate},
                    status = CASE WHEN GREATEST(confirmed_count - 1, 0) < max_concurrent AND status <> 'blocked'
                                  THEN 'available' ELSE status END
              WHERE id = :id"
        );
        $this->db->dbbind(':id', $slotId, PDO::PARAM_INT);
        $this->db->dbexecute();
        return $this->db->rowcount() > 0;
    }

    /**
     * Fetch a single replacement request joined with the booking's wedding date
     * and the original (declined) supplier name.
     */
    public function getReplacement(int $replacementId): array|false
    {
        $this->db->dbquery(
            "SELECT r.*,
                    sup.shop_name AS old_shop_name,
                    cat.name      AS category_name,
                    osvc.name     AS old_service_name,
                    (SELECT event_date FROM event_details ed
                      WHERE ed.booking_id = r.booking_id ORDER BY ed.event_date ASC LIMIT 1) AS event_date
               FROM booking_supplier_replacements r
               LEFT JOIN suppliers  sup  ON sup.supplier_id = r.old_supplier_id
               LEFT JOIN categories cat  ON cat.id = r.category_id
               LEFT JOIN services   osvc ON osvc.id = r.old_service_id
              WHERE r.id = :id
              LIMIT 1"
        );
        $this->db->dbbind(':id', $replacementId, PDO::PARAM_INT);
        return $this->db->getsingledata();
    }

    /**
     * Replacement requests awaiting an admin decision (queue view).
     */
    public function getPendingReplacements(): array
    {
        $this->db->dbquery(
            "SELECT r.*,
                    sup.shop_name AS old_shop_name,
                    cat.name      AS category_name,
                    osvc.name     AS old_service_name,
                    b.status      AS booking_status,
                    u.name        AS customer_name,
                    (SELECT event_date FROM event_details ed
                      WHERE ed.booking_id = r.booking_id ORDER BY ed.event_date ASC LIMIT 1) AS event_date
               FROM booking_supplier_replacements r
               LEFT JOIN suppliers  sup  ON sup.supplier_id = r.old_supplier_id
               LEFT JOIN categories cat  ON cat.id = r.category_id
               LEFT JOIN services   osvc ON osvc.id = r.old_service_id
               LEFT JOIN bookings   b    ON b.id = r.booking_id
               LEFT JOIN users      u    ON u.user_id = b.user_id
              WHERE r.status IN ('pending_admin','declined_again','rejected_by_customer')
              ORDER BY r.created_at ASC"
        );
        return $this->db->getmultidata();
    }

    /**
     * Find candidate replacement services: same category, available on the
     * wedding date, valid/paid supplier, not the declined supplier, and priced
     * at or below the +MAX_REPLACEMENT_UPCHARGE_PCT ceiling. Each candidate is
     * tagged with whether it needs customer approval (price > original).
     */
    public function findReplacementCandidates(int $replacementId): array
    {
        $r = $this->getReplacement($replacementId);
        if (!$r) {
            return [];
        }
        $categoryId = (int)($r['category_id'] ?? 0);
        $oldSupplier = (int)($r['old_supplier_id'] ?? 0);
        $oldPrice = (float)($r['old_price'] ?? 0);
        $eventDate = $r['event_date'] ?? null;

        $cap = defined('MAX_REPLACEMENT_UPCHARGE_PCT') ? (float)MAX_REPLACEMENT_UPCHARGE_PCT : 25.0;
        $ceiling = $oldPrice > 0 ? $oldPrice * (1 + $cap / 100) : null;

        $dayOfWeek = ($eventDate && preg_match('/^\d{4}-\d{2}-\d{2}/', $eventDate))
            ? (int)date('N', strtotime($eventDate)) : null;

        $sql = "SELECT s.id AS service_id, s.name AS service_name, s.price,
                       s.supplier_id, sup.shop_name,
                       s.max_concurrent_package
                  FROM services s
                  INNER JOIN suppliers sup ON sup.supplier_id = s.supplier_id
                 WHERE s.is_active = 1
                   AND s.category_id = :cat
                   AND s.supplier_id <> :old_sup
                   AND sup.deleted_at IS NULL
                   AND sup.is_available = 1
                   AND sup.status IN ('approved','verified')
                   AND sup.payment_status = 'paid'";
        // Price ceiling is NOT enforced — admins may pick an over-budget
        // replacement; those are flagged below and routed through the customer
        // approval (propose + pay delta) flow.
        // Schedule must allow this weekday (venue exempt, like the catalog).
        if ($dayOfWeek !== null) {
            $sql .= " AND EXISTS (
                        SELECT 1 FROM service_schedules ss
                         WHERE ss.service_id = s.id
                           AND ss.day_of_week = :dow
                           AND ss.is_available = 1
                           AND ss.open_time < ss.close_time
                      )";
        }
        // Exclude services whose package pool is already full on that date.
        if ($eventDate) {
            $sql .= " AND NOT EXISTS (
                        SELECT 1 FROM service_time_slots st
                         WHERE st.service_id = s.id
                           AND st.date = :edate
                           AND st.max_concurrent_package > 0
                           AND st.confirmed_package_count >= st.max_concurrent_package
                      )";
        }
        $sql .= " ORDER BY s.price ASC, s.id ASC LIMIT 50";

        $this->db->dbquery($sql);
        $this->db->dbbind(':cat', $categoryId, PDO::PARAM_INT);
        $this->db->dbbind(':old_sup', $oldSupplier, PDO::PARAM_INT);
        if ($dayOfWeek !== null) {
            $this->db->dbbind(':dow', $dayOfWeek, PDO::PARAM_INT);
        }
        if ($eventDate) {
            $this->db->dbbind(':edate', $eventDate);
        }
        $rows = $this->db->getmultidata();

        foreach ($rows as &$row) {
            $price = (float)($row['price'] ?? 0);
            $row['price_delta'] = round($price - $oldPrice, 2);
            $row['needs_customer_approval'] = $price > $oldPrice;
            // Flag candidates beyond the soft upcharge cap so the UI can warn,
            // but they remain selectable.
            $row['over_cap'] = $ceiling !== null && $price > $ceiling;
        }
        unset($row);
        return $rows;
    }

    /**
     * Update a replacement row's lifecycle fields. $fields is a whitelist map.
     */
    public function updateReplacement(int $replacementId, array $fields): bool
    {
        $allowed = [
            'new_supplier_id','new_service_id','new_price','price_delta',
            'requires_customer_approval','customer_approved_at','delta_payment_id',
            'status','chosen_by_admin_id','assigned_at','resolved_at',
        ];
        $sets = [];
        foreach ($fields as $k => $v) {
            if (in_array($k, $allowed, true)) {
                $sets[] = "`{$k}` = :{$k}";
            }
        }
        if (!$sets) {
            return false;
        }
        $this->db->dbquery(
            "UPDATE booking_supplier_replacements SET " . implode(', ', $sets) . " WHERE id = :id"
        );
        foreach ($fields as $k => $v) {
            if (in_array($k, $allowed, true)) {
                $this->db->dbbind(':' . $k, $v);
            }
        }
        $this->db->dbbind(':id', $replacementId, PDO::PARAM_INT);
        return $this->db->dbexecute();
    }

    /**
     * Atomic swap: replace the declined supplier with the chosen one. Releases
     * the old supplier's slot, reserves the new one, rewrites booking_suppliers
     * and the booking_item display snapshot. Returns true on success.
     *
     * Customer-facing item price only changes when $applyNewPrice is true
     * (i.e. the customer paid the delta for a pricier pick). For same/cheaper
     * picks the platform absorbs the difference and price stays put.
     */
    public function performReplacementSwap(int $replacementId, bool $applyNewPrice = false): bool
    {
        $r = $this->getReplacement($replacementId);
        if (!$r || (int)($r['new_service_id'] ?? 0) <= 0) {
            return false;
        }
        $bookingId      = (int)$r['booking_id'];
        $oldSupplierRow = (int)$r['booking_supplier_id'];
        $oldServiceId   = (int)($r['old_service_id'] ?? 0);
        $newServiceId   = (int)$r['new_service_id'];
        $newPrice       = (float)($r['new_price'] ?? 0);
        $oldPrice       = (float)($r['old_price'] ?? 0);
        $categoryId     = $r['category_id'] ?? null;
        $eventDate      = $r['event_date'] ?? null;

        // Look up new supplier + display info before opening the transaction.
        $this->db->dbquery(
            "SELECT s.supplier_id, s.name AS service_name, sup.shop_name,
                    (SELECT sd.file_url FROM supplier_documents sd
                      WHERE sd.supplier_id = sup.supplier_id AND sd.type = 'cover_photo'
                      ORDER BY sd.id DESC LIMIT 1) AS thumbnail_url
               FROM services s
               INNER JOIN suppliers sup ON sup.supplier_id = s.supplier_id
              WHERE s.id = :sid LIMIT 1"
        );
        $this->db->dbbind(':sid', $newServiceId, PDO::PARAM_INT);
        $newSvc = $this->db->getsingledata();
        if (!$newSvc) {
            return false;
        }
        $newSupplierId = (int)$newSvc['supplier_id'];

        $this->db->beginTransaction();
        try {
            // 1. Old supplier row -> replaced.
            $this->db->dbquery(
                "UPDATE booking_suppliers SET status = 'replaced' WHERE id = :id"
            );
            $this->db->dbbind(':id', $oldSupplierRow, PDO::PARAM_INT);
            $this->db->dbexecute();

            // 2. Release old supplier's package slot for the date (best-effort).
            if ($oldServiceId > 0 && $eventDate) {
                $this->db->dbquery(
                    "SELECT id FROM service_time_slots
                      WHERE service_id = :sid AND date = :d ORDER BY id ASC LIMIT 1"
                );
                $this->db->dbbind(':sid', $oldServiceId, PDO::PARAM_INT);
                $this->db->dbbind(':d', $eventDate);
                $oldSlot = $this->db->getsingledata();
                if ($oldSlot) {
                    $this->releaseServiceSlot((int)$oldSlot['id'], 'package');
                }
            }

            // 3. Insert the new supplier row.
            $finalPrice = $applyNewPrice ? $newPrice : $oldPrice;
            $this->db->dbquery(
                "INSERT INTO booking_suppliers
                    (booking_id, supplier_id, service_id, category_id, item_price, status, created_at)
                 VALUES (:bid, :sup, :svc, :cat, :price, 'pending', NOW())"
            );
            $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
            $this->db->dbbind(':sup', $newSupplierId, PDO::PARAM_INT);
            $this->db->dbbind(':svc', $newServiceId, PDO::PARAM_INT);
            $this->db->dbbind(':cat', $categoryId);
            $this->db->dbbind(':price', $finalPrice);
            $this->db->dbexecute();
            $newRowId = (int)$this->db->lastinsertid();

            // 4. Point the old row at its replacement.
            $this->db->dbquery(
                "UPDATE booking_suppliers SET replaced_by_id = :new WHERE id = :old"
            );
            $this->db->dbbind(':new', $newRowId, PDO::PARAM_INT);
            $this->db->dbbind(':old', $oldSupplierRow, PDO::PARAM_INT);
            $this->db->dbexecute();

            // 5. Reserve the new supplier's package slot for the date.
            if ($eventDate) {
                $conc = $this->getServiceConcurrent($newServiceId);
                $slotId = $this->findOrCreateServiceSlot(
                    $newServiceId, $eventDate, '09:00:00', '10:00:00',
                    $conc['max_concurrent'] ?? 1,
                    $conc['max_concurrent_package'] ?? 0,
                    $conc['max_concurrent_customize'] ?? 0
                );
                if ($slotId) {
                    $this->reserveServiceSlot($slotId, 'package');
                }
            }

            // 6. Refresh the booking_item display snapshot (price only if paid).
            $priceSet = $applyNewPrice ? ", price = :price" : "";
            $this->db->dbquery(
                "UPDATE booking_items
                    SET supplier_name = :sname, item_name = :iname, thumbnail_url = :thumb{$priceSet}
                  WHERE booking_id = :bid AND item_type = 'package'"
            );
            $this->db->dbbind(':sname', $newSvc['shop_name'] ?? '');
            $this->db->dbbind(':iname', $newSvc['service_name'] ?? '');
            $this->db->dbbind(':thumb', $newSvc['thumbnail_url'] ?? null);
            if ($applyNewPrice) {
                $this->db->dbbind(':price', $finalPrice);
            }
            $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
            $this->db->dbexecute();

            // 7. If the customer paid a delta, bump the booking totals.
            if ($applyNewPrice && $newPrice > $oldPrice) {
                $delta = $newPrice - $oldPrice;
                $this->db->dbquery(
                    "UPDATE bookings
                        SET total_amount = COALESCE(total_amount,0) + :d,
                            paid_amount  = COALESCE(paid_amount,0) + :d2
                      WHERE id = :bid"
                );
                $this->db->dbbind(':d', $delta);
                $this->db->dbbind(':d2', $delta);
                $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
                $this->db->dbexecute();
            }

            // 8. Replacement row -> assigned.
            $this->db->dbquery(
                "UPDATE booking_supplier_replacements
                    SET status = 'assigned', assigned_at = NOW()
                  WHERE id = :id"
            );
            $this->db->dbbind(':id', $replacementId, PDO::PARAM_INT);
            $this->db->dbexecute();

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollBack();
            error_log('performReplacementSwap failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Create a pending manual payment row for a replacement price delta. The
     * customer pays this (slip upload) and admin verification finalizes the swap.
     */
    public function createReplacementDeltaPayment(int $bookingId, float $delta): int
    {
        $this->db->dbquery(
            "INSERT INTO payments (booking_id, amount, type, status, created_at)
             VALUES (:bid, :amt, 'replacement_delta', 'pending', NOW())"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        $this->db->dbbind(':amt', $delta);
        $this->db->dbexecute();
        return (int)$this->db->lastinsertid();
    }

    /**
     * Attach the customer's bank-transfer proof to a pending replacement-delta
     * payment. Stays 'pending' until an admin verifies it (which finalizes the
     * swap). Returns true on success.
     */
    public function recordReplacementDeltaSlip(
        int $paymentId, string $slipPath, string $bankName, string $accountName,
        string $mobileNumber, string $transactionRef, float $paidAmount
    ): bool {
        $this->db->dbquery(
            "UPDATE payments
                SET method = :method, bank_name = :bank, account_name = :acct,
                    mobile_number = :mobile, transaction_ref = :ref,
                    paid_amount = :paid, payment_slip_path = :slip, paid_at = NOW()
              WHERE id = :pid AND type = 'replacement_delta' AND status = 'pending'"
        );
        $this->db->dbbind(':method', $bankName);
        $this->db->dbbind(':bank', $bankName);
        $this->db->dbbind(':acct', $accountName);
        $this->db->dbbind(':mobile', $mobileNumber);
        $this->db->dbbind(':ref', $transactionRef);
        $this->db->dbbind(':paid', $paidAmount);
        $this->db->dbbind(':slip', $slipPath !== '' ? $slipPath : null);
        $this->db->dbbind(':pid', $paymentId, PDO::PARAM_INT);
        $this->db->dbexecute();
        return $this->db->rowcount() > 0;
    }

    /**
     * Fetch a replacement together with the booking owner's user id — used to
     * authorize the customer-facing delta-payment page.
     */
    public function getReplacementForCustomer(int $replacementId): array|false
    {
        $this->db->dbquery(
            "SELECT r.*, b.user_id, b.total_amount,
                    sup.shop_name AS new_shop_name,
                    s.name AS new_service_name
               FROM booking_supplier_replacements r
               INNER JOIN bookings b ON b.id = r.booking_id
               LEFT JOIN suppliers sup ON sup.supplier_id = r.new_supplier_id
               LEFT JOIN services  s   ON s.id = r.new_service_id
              WHERE r.id = :id LIMIT 1"
        );
        $this->db->dbbind(':id', $replacementId, PDO::PARAM_INT);
        return $this->db->getsingledata();
    }

    /**
     * True once no open replacement work remains on the booking (no
     * needs_replacement supplier rows and no unresolved replacement requests).
     */
    public function bookingReplacementsResolved(int $bookingId): bool
    {
        $this->db->dbquery(
            "SELECT
               (SELECT COUNT(*) FROM booking_suppliers
                 WHERE booking_id = :bid AND status = 'needs_replacement') AS pending_rows,
               (SELECT COUNT(*) FROM booking_supplier_replacements
                 WHERE booking_id = :bid2
                   AND status IN ('pending_admin','pending_customer','declined_again','rejected_by_customer')) AS open_reqs"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        $this->db->dbbind(':bid2', $bookingId, PDO::PARAM_INT);
        $row = $this->db->getsingledata();
        return (int)($row['pending_rows'] ?? 0) === 0 && (int)($row['open_reqs'] ?? 0) === 0;
    }

    /**
     * The replacement on a booking that is waiting for the customer to approve +
     * pay the delta (drives the "Pay difference" banner on booking detail).
     */
    public function getPendingCustomerReplacement(int $bookingId): array|false
    {
        $this->db->dbquery(
            "SELECT r.*, sup.shop_name AS new_shop_name
               FROM booking_supplier_replacements r
               LEFT JOIN suppliers sup ON sup.supplier_id = r.new_supplier_id
              WHERE r.booking_id = :bid AND r.status = 'pending_customer'
              ORDER BY r.id DESC LIMIT 1"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        return $this->db->getsingledata();
    }

    /**
     * Find an open (assigned) replacement row for a given new supplier on a
     * booking — used when that supplier accepts/declines via supplierRespond.
     */
    public function getActiveReplacementForSupplier(int $bookingId, int $newSupplierId): array|false
    {
        $this->db->dbquery(
            "SELECT * FROM booking_supplier_replacements
              WHERE booking_id = :bid AND new_supplier_id = :sup AND status = 'assigned'
              ORDER BY id DESC LIMIT 1"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        $this->db->dbbind(':sup', $newSupplierId, PDO::PARAM_INT);
        return $this->db->getsingledata();
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
             LEFT JOIN (SELECT package_id, MIN(default_supplier_id) AS default_supplier_id FROM package_items GROUP BY package_id) pi ON bi.item_id = pi.package_id AND bi.item_type = 'package'
             LEFT JOIN categories cat ON s.category_id = cat.id
             LEFT JOIN event_details ed ON ed.booking_id = bi.booking_id AND ed.id = (
                SELECT MIN(id) FROM event_details WHERE booking_id = bi.booking_id
             )
             WHERE bi.booking_id = :bid"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        $items = $this->db->getmultidata();

        $idx = 0;
        foreach ($items as $item) {
            $prefix = $prefixes[$item['item_type']] ?? 'VCH';
            $voucherNumber = $prefix . '-' . strtoupper(substr(md5($item['id'] . '-' . $bookingId . '-' . (++$idx)), 0, 8));

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
    public function getCustomerVouchers(int $userId, ?string $statusFilter = null, int $limit = 12, int $offset = 0): array
    {
        $sql = "SELECT bv.*, b.user_id
                FROM booking_vouchers bv
                INNER JOIN bookings b ON bv.booking_id = b.id
                WHERE b.user_id = :uid";

        if ($statusFilter && $statusFilter !== 'all') {
            $sql .= " AND bv.status = :status";
        }

        $sql .= " ORDER BY bv.issued_at DESC LIMIT :limit OFFSET :offset";

        $this->db->dbquery($sql);
        $this->db->dbbind(':uid', $userId, PDO::PARAM_INT);
        if ($statusFilter && $statusFilter !== 'all') {
            $this->db->dbbind(':status', $statusFilter);
        }
        $this->db->dbbind(':limit', $limit, PDO::PARAM_INT);
        $this->db->dbbind(':offset', $offset, PDO::PARAM_INT);

        return $this->db->getmultidata();
    }

    public function getCustomerVouchersCount(int $userId, ?string $statusFilter = null): int
    {
        $sql = "SELECT COUNT(*) AS total
                FROM booking_vouchers bv
                INNER JOIN bookings b ON bv.booking_id = b.id
                WHERE b.user_id = :uid";

        if ($statusFilter && $statusFilter !== 'all') {
            $sql .= " AND bv.status = :status";
        }

        $this->db->dbquery($sql);
        $this->db->dbbind(':uid', $userId, PDO::PARAM_INT);
        if ($statusFilter && $statusFilter !== 'all') {
            $this->db->dbbind(':status', $statusFilter);
        }
        return (int)($this->db->getsingledata()['total'] ?? 0);
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
        $this->db->dbquery(
            "INSERT INTO booking_status_logs (booking_id, old_status, new_status, note)
             SELECT id, status, 'cancellation_requested', :note
             FROM bookings WHERE id = :bid"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        $this->db->dbbind(':note', 'Cancellation requested: ' . $reason);

        if (!$this->db->dbexecute()) {
            return false;
        }

        $this->db->dbquery(
            "UPDATE bookings SET status = 'cancellation_requested' WHERE id = :bid LIMIT 1"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);

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
            // Capture how much is being marked refunded (for the audit note)
            $this->db->dbquery(
                "SELECT COALESCE(SUM(COALESCE(paid_amount, amount)), 0) AS total
                 FROM payments
                 WHERE booking_id = :bid AND status = 'success'"
            );
            $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
            $refundedAmount = (float) ($this->db->getsingledata()['total'] ?? 0);

            // Manual refund: admin processes the money outside the system,
            // this only flips the bookkeeping flag.
            $this->db->dbquery(
                "UPDATE payments SET escrow_status = 'refunded'
                 WHERE booking_id = :bid AND status = 'success'"
            );
            $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
            $this->db->dbexecute();

            // Audit row so the manual refund is traceable (who/when/how much)
            $this->logStatusChange(
                $bookingId,
                'cancelled',
                'cancelled',
                $adminId,
                'Deposit marked as refunded by admin (manual): ' . number_format($refundedAmount, 0) . ' MMK'
            );
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

    /**
     * Most-actionable status across a supplier's service rows on one booking.
     * Requires a bound :sid_status parameter. A pending replacement outranks a
     * plain confirm so the dashboard chip surfaces what needs attention.
     */
    private function supplierAggregateStatusSql(): string
    {
        return "(SELECT bs_st.status FROM booking_suppliers bs_st
                 WHERE bs_st.booking_id = b.id AND bs_st.supplier_id = :sid_status
                 ORDER BY FIELD(bs_st.status,
                    'needs_replacement','pending','confirmed','in_progress','completed','rejected','replaced','cancelled') ASC
                 LIMIT 1)";
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

    /**
     * Expire stale supplier-replacement work (run alongside
     * expireOverdueBookingRequests):
     *   A) A replacement supplier (newly assigned, still 'pending') who blew the
     *      48h response deadline -> auto-decline + re-queue for admin re-pick.
     *   B) A pricier proposal sitting in 'pending_customer' for >3 days with no
     *      payment -> revert to 'pending_admin' so the admin can pick cheaper.
     * Returns the number of replacements re-queued.
     *
     * @return array{requeued:int, booking_ids:int[]}
     */
    public function expireOverdueReplacements(): array
    {
        $requeued = 0;
        $bookingIds = [];

        // A) Assigned replacement supplier missed the deadline.
        $this->db->dbquery(
            "SELECT r.id AS rid, r.booking_id, r.new_supplier_id
               FROM booking_supplier_replacements r
               INNER JOIN bookings b ON b.id = r.booking_id
               INNER JOIN booking_suppliers bs
                       ON bs.booking_id = r.booking_id
                      AND bs.supplier_id = r.new_supplier_id
                      AND bs.status = 'pending'
              WHERE r.status = 'assigned'
                AND b.supplier_response_deadline IS NOT NULL
                AND b.supplier_response_deadline < NOW()"
        );
        $overdue = $this->db->getmultidata();
        foreach ($overdue as $row) {
            $rid = (int)$row['rid'];
            $bookingId = (int)$row['booking_id'];
            $newSupplier = (int)$row['new_supplier_id'];

            // Reject the unresponsive new supplier row.
            $this->db->dbquery(
                "UPDATE booking_suppliers SET status = 'rejected'
                  WHERE booking_id = :bid AND supplier_id = :sup AND status = 'pending'"
            );
            $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
            $this->db->dbbind(':sup', $newSupplier, PDO::PARAM_INT);
            $this->db->dbexecute();

            // Re-open the request for admin.
            $this->updateReplacement($rid, [
                'status' => 'declined_again',
                'new_supplier_id' => null,
                'new_service_id' => null,
                'new_price' => null,
                'price_delta' => null,
            ]);
            $this->logStatusChange($bookingId, null, 'replacement_expired', null, 'Replacement supplier did not respond in 48h; re-queued');
            $requeued++;
            $bookingIds[] = $bookingId;
        }

        // B) Customer never approved a pricier proposal within 3 days.
        $this->db->dbquery(
            "SELECT id AS rid, booking_id, delta_payment_id
               FROM booking_supplier_replacements
              WHERE status = 'pending_customer'
                AND created_at < (NOW() - INTERVAL 3 DAY)"
        );
        $staleProposals = $this->db->getmultidata();
        foreach ($staleProposals as $row) {
            $rid = (int)$row['rid'];
            $bookingId = (int)$row['booking_id'];
            $paymentId = (int)($row['delta_payment_id'] ?? 0);

            if ($paymentId > 0) {
                $this->db->dbquery("UPDATE payments SET status = 'failed' WHERE id = :pid AND status = 'pending'");
                $this->db->dbbind(':pid', $paymentId, PDO::PARAM_INT);
                $this->db->dbexecute();
            }
            $this->updateReplacement($rid, [
                'status' => 'pending_admin',
                'new_supplier_id' => null,
                'new_service_id' => null,
                'new_price' => null,
                'price_delta' => null,
                'requires_customer_approval' => 0,
                'delta_payment_id' => null,
            ]);
            $this->logStatusChange($bookingId, null, 'replacement_proposal_expired', null, 'Customer did not approve pricier replacement in 3 days; re-queued');
            $requeued++;
            $bookingIds[] = $bookingId;
        }

        return ['requeued' => $requeued, 'booking_ids' => array_values(array_unique($bookingIds))];
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

        $columns = ['booking_id', 'amount', 'type', 'method', 'status', 'transaction_ref', 'escrow_status'];
        $values = [':bid', ':amount', "'deposit'", ':method', "'pending'", ':ref', "'held'"];
        $bindings = [
            ':bid' => [$bookingId, PDO::PARAM_INT],
            ':amount' => [number_format($paidAmount, 2, '.', ''), PDO::PARAM_STR],
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
        $selects = ['id', 'amount', 'method', 'transaction_ref', 'status', 'verified_at', 'created_at'];
        foreach (['bank_name', 'account_name', 'mobile_number', 'paid_amount', 'paid_at', 'payment_slip_path', 'verified_note'] as $column) {
            $selects[] = $this->paymentHasColumn($column) ? $column : 'NULL AS ' . $column;
        }

        $this->db->dbquery(
            "SELECT " . implode(', ', $selects) . "
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
        $this->db->dbquery(
            "SELECT id
             FROM payments
             WHERE booking_id = :bid
               AND type = 'deposit'
               AND status = 'pending'
             ORDER BY id DESC
             LIMIT 1"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        $pendingPayment = $this->db->getsingledata();
        $paymentId = (int)($pendingPayment['id'] ?? 0);
        if ($paymentId <= 0) {
            return false;
        }

        $this->db->beginTransaction();
        try {
            // Update only the newest proof that is actually awaiting review.
            $setParts = ["status = 'success'", 'verified_by = :admin', 'verified_at = NOW()'];
            if ($this->paymentHasColumn('verified_note')) {
                $setParts[] = 'verified_note = :note';
            }

            $this->db->dbquery(
                "UPDATE payments SET " . implode(', ', $setParts) . "
                 WHERE id = :payment_id AND status = 'pending' LIMIT 1"
            );
            $this->db->dbbind(':admin', $adminId, PDO::PARAM_INT);
            if ($this->paymentHasColumn('verified_note')) {
                $this->db->dbbind(':note', $note, PDO::PARAM_STR);
            }
            $this->db->dbbind(':payment_id', $paymentId, PDO::PARAM_INT);
            if (!$this->db->dbexecute() || $this->db->rowcount() !== 1) {
                throw new RuntimeException('Pending deposit was already reviewed.');
            }

            // Update booking status (normalized so it works with old + new ENUMs).
            $status = $this->normalizeBookingStatus('paid');
            $this->db->dbquery(
                "UPDATE bookings SET status = :status, payment_status = 'partial' WHERE id = :id LIMIT 1"
            );
            $this->db->dbbind(':status', $status);
            $this->db->dbbind(':id', $bookingId, PDO::PARAM_INT);
            if (!$this->db->dbexecute()) {
                throw new RuntimeException('Booking status could not be updated.');
            }
            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            error_log('Deposit verification failed: ' . $e->getMessage());
            return false;
        }

        // Sync paid_amount on the booking using what the customer actually transferred
        $paidAmountExpr = $this->paymentHasColumn('paid_amount')
            ? 'COALESCE(paid_amount, amount, 0)'
            : 'COALESCE(amount, 0)';
        $this->db->dbquery(
            "SELECT {$paidAmountExpr} AS deposit_paid
             FROM payments WHERE booking_id = :bid AND type = 'deposit' AND status = 'success'
             ORDER BY id DESC LIMIT 1"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        $payRow = $this->db->getsingledata();
        $depositPaid = (float)($payRow['deposit_paid'] ?? 0);
        if ($depositPaid > 0) {
            $this->updatePaidAmount($bookingId, $depositPaid);
        }

        // For package bookings: auto-confirm all suppliers and advance to confirmed.
        // Booking type is derived from booking_items; not every schema has a
        // booking_type column on the bookings table.
        if ($this->isPackageBooking($bookingId)) {
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

    public function getCustomerForBooking(int $bookingId): array
    {
        $this->db->dbquery(
            "SELECT u.user_id, u.name, u.email
             FROM bookings b
             JOIN users u ON b.user_id = u.user_id
             WHERE b.id = :bid
             LIMIT 1"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        return $this->db->getsingledata() ?: [];
    }

    public function getSupplierEmailsForBooking(int $bookingId): array
    {
        $this->db->dbquery(
            "SELECT DISTINCT u.name, u.email, sup.shop_name
             FROM booking_suppliers bs
             JOIN suppliers sup ON bs.supplier_id = sup.supplier_id
             JOIN users u ON sup.user_id = u.user_id
             WHERE bs.booking_id = :bid
             AND bs.status NOT IN ('rejected', 'cancelled')"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        return $this->db->getmultidata() ?: [];
    }

    /**
     * Get email addresses of all admin/staff users for notifications.
     */
    public function getAdminEmails(): array
    {
        $this->db->dbquery(
            "SELECT u.name, u.email
             FROM users u
             JOIN user_roles ur ON u.user_id = ur.user_id
             WHERE ur.role_id IN (3, 4)
             AND u.status = 'active'
             GROUP BY u.user_id"
        );
        return $this->db->getmultidata() ?: [];
    }
}
