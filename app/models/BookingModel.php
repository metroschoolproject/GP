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
    private array $replacementColumnCache = [];
    private array $tableExistsCache = [];
    private ?string $replacementSwapError = null;
    private ?string $paymentVerificationError = null;
    private ?array $lastUnavailableService = null;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function beginTransaction(): bool
    {
        return $this->db->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->db->commit();
    }

    public function rollBack(): bool
    {
        return $this->db->rollBack();
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
        $hasDesignColumns = $this->hasBookingDesignColumns();
        $designInsertColumn = $hasDesignColumns ? ', attire_item_id, decoration_style_id, cake_design_id' : '';
        $designSelectColumn = $hasDesignColumns
            ? ', ci.attire_item_id, ci.decoration_style_id, ci.cake_design_id'
            : '';
        $hasBookingRentalColumns = $this->hasBookingRentalColumns();
        $rentalInsertColumn = $hasBookingRentalColumns ? ', rental_type, borrow_date, return_date' : '';
        $rentalSelectColumn = $hasBookingRentalColumns
            ? ", ci.rental_type, ci.borrow_date, CASE WHEN ci.rental_type = 'borrow' AND aro.days IS NOT NULL THEN DATE_ADD(ci.borrow_date, INTERVAL (aro.days - 1) DAY) ELSE NULL END"
            : '';
        $hasSupplierPackages = $this->tableExists('supplier_packages');
        $supplierPackagePrice = $hasSupplierPackages ? 'sp.total_price' : 'NULL';
        $supplierPackageName = $hasSupplierPackages ? 'sp.name' : 'NULL';
        $supplierPackageShop = $hasSupplierPackages ? 'sp_sup.shop_name' : 'NULL';
        $supplierPackageThumbnail = $hasSupplierPackages ? 'sp.thumbnail_url' : 'NULL';
        $supplierPackageJoin = $hasSupplierPackages
            ? "LEFT JOIN supplier_packages sp ON ci.item_id = sp.id AND ci.item_type = 'supplier_package'
               LEFT JOIN suppliers sp_sup ON sp.supplier_id = sp_sup.supplier_id"
            : '';

        $decoJoin = $hasDesignColumns
            ? 'LEFT JOIN decoration_styles ds ON ds.id = ci.decoration_style_id'
            : '';
        $decoPriceExpr = $hasDesignColumns
            ? "CASE WHEN ci.decoration_style_id IS NOT NULL AND ds.id IS NOT NULL THEN COALESCE(ds.customize_price, ds.package_price, ds.price) END"
            : 'NULL';

        $agentFeeMultiplier = 1 + (get_platform_fee_percent() / 100);

        $this->db->dbquery(
            "INSERT INTO booking_items (booking_id, item_type{$sourceInsertColumn}, item_id, booking_date, price,
                    item_name, supplier_name, category_name, thumbnail_url,
                    status, slot_id, start_time, end_time, booking_type{$venueRoomInsertColumn}{$packageParentInsertColumn}{$designInsertColumn}{$rentalInsertColumn})
            SELECT :bid, ci.item_type{$sourceSelectColumn}, ci.item_id,
                    CONCAT(ci.selected_date, ' ', COALESCE(ci.start_time, '00:00:00')),
                    CASE
                        WHEN ci.item_type = 'package' THEN COALESCE(p.base_price, ci.price, 0) * {$agentFeeMultiplier}
                        ELSE COALESCE({$decoPriceExpr}, ci.price, s.price_min, s.price, {$supplierPackagePrice}, 0)
                    END,
                    COALESCE(s.name, p.name, {$supplierPackageName}),
                    COALESCE(sup.shop_name, {$supplierPackageShop}, 'Golden Promise'),
                    cat.name,
                    COALESCE(s.thumbnail_url, p.image_url, {$supplierPackageThumbnail}),
                    'pending',
                    ci.slot_id, ci.start_time, ci.end_time,
                    COALESCE(s.booking_type, 'fullday'){$venueRoomSelectColumn}{$packageParentSelectColumn}{$designSelectColumn}{$rentalSelectColumn}
            FROM cart_items ci
            LEFT JOIN services s ON ci.item_id = s.id AND ci.item_type = 'service'
            LEFT JOIN packages p ON ci.item_id = p.package_id AND ci.item_type = 'package'
            {$supplierPackageJoin}
            {$decoJoin}
            LEFT JOIN suppliers sup ON s.supplier_id = sup.supplier_id
            LEFT JOIN categories cat ON s.category_id = cat.id
            LEFT JOIN attire_rental_options aro ON aro.id = ci.rental_option_id
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

    private $bookingDesignColumns = null;
    private function hasBookingDesignColumns(): bool
    {
        if ($this->bookingDesignColumns !== null) {
            return $this->bookingDesignColumns;
        }

        $this->db->dbquery("SHOW COLUMNS FROM booking_items LIKE 'attire_item_id'");
        $this->bookingDesignColumns = (bool)$this->db->getsingledata();

        return $this->bookingDesignColumns;
    }

    private ?bool $bookingRentalColumns = null;
    private function hasBookingRentalColumns(): bool
    {
        if ($this->bookingRentalColumns !== null) {
            return $this->bookingRentalColumns;
        }

        $this->db->dbquery("SHOW COLUMNS FROM booking_items LIKE 'rental_type'");
        $this->bookingRentalColumns = (bool)$this->db->getsingledata();

        return $this->bookingRentalColumns;
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

    private function replacementHasColumn(string $column): bool
    {
        if (array_key_exists($column, $this->replacementColumnCache)) {
            return $this->replacementColumnCache[$column];
        }

        $this->db->dbquery("SHOW COLUMNS FROM booking_supplier_replacements LIKE :column");
        $this->db->dbbind(':column', $column);
        $this->replacementColumnCache[$column] = (bool)$this->db->getsingledata();

        return $this->replacementColumnCache[$column];
    }

    private function tableExists(string $table): bool
    {
        if (array_key_exists($table, $this->tableExistsCache)) {
            return $this->tableExistsCache[$table];
        }

        $this->db->dbquery(
            'SELECT COUNT(*) AS total
               FROM information_schema.TABLES
              WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = :table'
        );
        $this->db->dbbind(':table', $table);
        $row = $this->db->getsingledata();
        $this->tableExistsCache[$table] = (int)($row['total'] ?? 0) > 0;

        return $this->tableExistsCache[$table];
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
                    (booking_id, booking_item_id, event_date, preferred_time, start_time, end_time,
                    guest_count, location, contact_phone, special_requests, contact_name)
                VALUES (:bid, :biid, :edate, :ptime, :stime, :etime, :guests, :location, :phone, :notes, :cname)"
            );
            $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
            $this->db->dbbind(':biid', $bookingItemId, PDO::PARAM_INT);
            $this->db->dbbind(':edate', $item['event_date'] ?? null);
            $this->db->dbbind(':ptime', !empty($item['preferred_time']) ? $item['preferred_time'] : null);
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
     * Details of the service whose slot could not be reserved in the most
     * recent reservePackageServiceSlots() call, or null if the last call
     * succeeded. Mirrors the $replacementSwapError accessor pattern.
     *
     * @return array{service_id:int,service_name:string,date:string,message:string}|null
     */
    public function getLastUnavailableService(): ?array
    {
        return $this->lastUnavailableService;
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
    public function reservePackageServiceSlots(int $bookingId, string $eventDate, array $packageSchedule): array|false
    {
        $this->lastUnavailableService = null;
        foreach ($packageSchedule as $event) {
            if (($event['booking_type'] ?? '') === 'slot') {
                $svcId = (int)($event['service_id'] ?? 0);

                // Skip services already reserved for this booking (idempotent retry support)
                $this->db->dbquery(
                    "SELECT 1 FROM booking_slot_reservations
                     WHERE booking_id = :bid AND service_id = :sid AND source = 'package'
                     LIMIT 1"
                );
                $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
                $this->db->dbbind(':sid', $svcId, PDO::PARAM_INT);
                if ($this->db->getsingledata()) {
                    continue;
                }
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
                if (!$slotId || !$this->reserveServiceSlot($slotId, 'package')) {
                    $this->lastUnavailableService = [
                        'service_id'   => $svcId,
                        'service_name' => (string)($event['service_name'] ?? 'Package service'),
                        'date'         => $eventDate,
                        'message'      => 'No package slots available for this time',
                    ];
                    return false;
                }
                if (!$this->recordSlotReservation(
                    $bookingId,
                    $slotId,
                    'package',
                    null,
                    $svcId,
                    (int)($event['package_item_id'] ?? 0)
                )) {
                    return false;
                }
            }
        }
        return $packageSchedule;
    }

    public function reserveBookingItemSlots(int $bookingId): bool
    {
        $this->db->dbquery(
            "SELECT id, slot_id, item_id AS service_id
               FROM booking_items
              WHERE booking_id = :bid
                AND item_type = 'service'
                AND slot_id IS NOT NULL"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        foreach ($this->db->getmultidata() as $item) {
            $slotId = (int)$item['slot_id'];
            if (
                !$this->reserveServiceSlot($slotId, 'custom')
                || !$this->recordSlotReservation(
                    $bookingId,
                    $slotId,
                    'custom',
                    (int)$item['id'],
                    (int)$item['service_id']
                )
            ) {
                return false;
            }
        }
        return true;
    }

    private function recordSlotReservation(
        int $bookingId,
        int $slotId,
        string $source,
        ?int $bookingItemId = null,
        ?int $serviceId = null,
        ?int $packageItemId = null
    ): bool {
        $this->db->dbquery(
            "INSERT INTO booking_slot_reservations
                (booking_id, booking_item_id, package_item_id, service_id, slot_id, source, reserved_at)
             VALUES
                (:bid, :biid, :piid, :sid, :slot, :source, NOW())"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        $this->db->dbbind(':biid', $bookingItemId, $bookingItemId ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $this->db->dbbind(':piid', $packageItemId, $packageItemId ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $this->db->dbbind(':sid', $serviceId, $serviceId ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $this->db->dbbind(':slot', $slotId, PDO::PARAM_INT);
        $this->db->dbbind(':source', $source);
        return $this->db->dbexecute();
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

        $hasSupplierPackageItems = $this->tableExists('supplier_package_items');

        $supplierPackageUnion = $hasSupplierPackageItems ? "
                UNION ALL

                -- Supplier-package service lines
                SELECT bi.booking_id, s2.supplier_id, s2.id, s2.category_id, NULL, spi.price
                FROM booking_items bi
                INNER JOIN supplier_package_items spi
                    ON spi.package_id = bi.item_id AND bi.item_type = 'supplier_package'
                INNER JOIN services s2 ON spi.service_id = s2.id
                WHERE bi.booking_id = :supplier_package_items_bid" : '';

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
                {$supplierPackageUnion}
             ) svc_lines
             WHERE svc_lines.supplier_id IS NOT NULL"
        );
        $this->db->dbbind(':package_bid', $bookingId, PDO::PARAM_INT);
        $this->db->dbbind(':service_bid', $bookingId, PDO::PARAM_INT);
        if ($hasSupplierPackageItems) {
            $this->db->dbbind(':supplier_package_items_bid', $bookingId, PDO::PARAM_INT);
        }

        return $this->db->dbexecute();
    }

    /**
     * Confirm pending supplier rows for suppliers who opted into auto-accept.
     * Returns the number of booking_supplier rows that were auto-confirmed.
     */
    public function autoAcceptEnabledSuppliers(int $bookingId): int
    {
        $this->db->dbquery(
            "SELECT DISTINCT bs.supplier_id
             FROM booking_suppliers bs
             INNER JOIN suppliers s ON s.supplier_id = bs.supplier_id
             WHERE bs.booking_id = :bid
               AND bs.status = 'pending'
               AND s.auto_accept_bookings = 1"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        $supplierRows = $this->db->getmultidata() ?: [];

        $this->db->dbquery(
            "UPDATE booking_suppliers bs
             INNER JOIN suppliers s ON s.supplier_id = bs.supplier_id
             SET bs.status = 'confirmed',
                 bs.confirmed_at = COALESCE(bs.confirmed_at, NOW())
             WHERE bs.booking_id = :bid
               AND bs.status = 'pending'
               AND s.auto_accept_bookings = 1"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        if (!$this->db->dbexecute()) {
            return 0;
        }
        $confirmedRows = $this->db->rowcount();

        foreach ($supplierRows as $row) {
            $supplierId = (int)($row['supplier_id'] ?? 0);
            if ($supplierId > 0) {
                $this->updateBookingItemsStatusBySupplier($bookingId, $supplierId, 'accepted');
            }
        }

        return $confirmedRows;
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
            "SELECT b.*, u.name AS customer_name, u.email AS customer_email, u.phone AS customer_phone,
                    u.address AS customer_address, u.avatar AS customer_avatar, u.status AS customer_status,
                    u.created_at AS customer_created_at, u.last_login AS customer_last_login,
                    u.is_online AS customer_is_online
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
        $hasSupplierPackages = $this->tableExists('supplier_packages');
        $supplierPackageName = $hasSupplierPackages ? 'sp.name' : 'NULL';
        $supplierPackageThumbnail = $hasSupplierPackages ? 'sp.thumbnail_url' : 'NULL';
        $supplierPackageShop = $hasSupplierPackages ? 'sp_sup.shop_name' : 'NULL';
        $supplierPackageJoin = $hasSupplierPackages
            ? "LEFT JOIN supplier_packages sp
                     ON bi.item_id = sp.id AND bi.item_type = 'supplier_package'
               LEFT JOIN suppliers sp_sup ON sp.supplier_id = sp_sup.supplier_id"
            : '';
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
                    COALESCE(bi.item_name, s.name, p.name, {$supplierPackageName}) AS service_name,
                    COALESCE(bi.thumbnail_url, s.thumbnail_url, p.image_url, {$supplierPackageThumbnail}) AS thumbnail_url,
                    COALESCE(bi.supplier_name, sup.shop_name, {$supplierPackageShop}, 'Golden Promise') AS supplier_name,
                    sup.supplier_id,
                    COALESCE(bi.category_name, cat.name) AS category_name,
                    {$bookingVenueSelect}
             FROM booking_items bi
             LEFT JOIN services s ON bi.item_id = s.id AND bi.item_type = 'service'
             LEFT JOIN packages p ON bi.item_id = p.package_id AND bi.item_type = 'package'
             {$packageParentJoin}
             {$supplierPackageJoin}
             LEFT JOIN suppliers sup ON s.supplier_id = sup.supplier_id
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
        $hasSupplierPackages = $this->tableExists('supplier_packages');
        $supplierPackageName = $hasSupplierPackages ? 'sp.name' : 'NULL';
        $supplierPackageThumbnail = $hasSupplierPackages ? 'sp.thumbnail_url' : 'NULL';
        $supplierPackageShop = $hasSupplierPackages ? 'sp_sup.shop_name' : 'NULL';
        $supplierPackageId = $hasSupplierPackages ? 'sp_sup.supplier_id' : 'NULL';
        $supplierPackageJoin = $hasSupplierPackages
            ? "LEFT JOIN supplier_packages sp
                     ON bi.item_id = sp.id AND bi.item_type = 'supplier_package'
               LEFT JOIN suppliers sp_sup ON sp.supplier_id = sp_sup.supplier_id"
            : '';
        $supplierPackageFilter = $hasSupplierPackages
            ? 'OR sp.supplier_id = :sid_package'
            : '';
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
                    COALESCE(bi.item_name, s.name, p.name, {$supplierPackageName}) AS service_name,
                    COALESCE(bi.thumbnail_url, s.thumbnail_url, p.image_url, {$supplierPackageThumbnail}) AS thumbnail_url,
                    COALESCE(bi.supplier_name, sup.shop_name, {$supplierPackageShop}, 'Golden Promise') AS supplier_name,
                    COALESCE(sup.supplier_id, {$supplierPackageId}) AS supplier_id,
                    COALESCE(bi.category_name, cat.name) AS category_name,
                    {$bookingVenueSelect}
             FROM booking_items bi
             LEFT JOIN services s ON bi.item_id = s.id AND bi.item_type = 'service'
             LEFT JOIN packages p ON bi.item_id = p.package_id AND bi.item_type = 'package'
             {$packageParentJoin}
             {$supplierPackageJoin}
             LEFT JOIN suppliers sup ON s.supplier_id = sup.supplier_id
             LEFT JOIN categories cat ON s.category_id = cat.id
             {$bookingVenueJoin}
             LEFT JOIN venue_room_availability slot_vra ON slot_vra.id = bi.slot_id
             LEFT JOIN venue_rooms slot_vr ON slot_vr.id = slot_vra.room_id
             LEFT JOIN venues slot_venue ON slot_venue.id = slot_vr.venue_id
             WHERE bi.booking_id = :bid
               AND (
                    s.supplier_id = :sid_service
                    {$supplierPackageFilter}
                    OR (
                        bi.item_type = 'package'
                        AND EXISTS (
                            SELECT 1
                            FROM booking_suppliers bs_supplier
                            WHERE bs_supplier.booking_id = bi.booking_id
                              AND bs_supplier.supplier_id = :sid_default
                              AND bs_supplier.package_item_id IS NOT NULL
                              AND bs_supplier.status NOT IN ('replaced','rejected','cancelled')
                        )
                    )
               )
             ORDER BY bi.id ASC"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        $this->db->dbbind(':sid_service', $supplierId, PDO::PARAM_INT);
        if ($hasSupplierPackages) {
            $this->db->dbbind(':sid_package', $supplierId, PDO::PARAM_INT);
        }
        $this->db->dbbind(':sid_default', $supplierId, PDO::PARAM_INT);
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
     * Returns schedule entries built from actual reserved slots for a booking.
     * Each entry includes service/supplier/category metadata so it can be used
     * directly in the booking detail view without the full slot expansion.
     */
    public function getReservedSlotSchedule(int $bookingId): array
    {
        $this->db->dbquery(
            "SELECT bsr.slot_id,
                    bsr.service_id,
                    bsr.package_item_id,
                    pi.quantity_type,
                    pi.quantity,
                    sts.start_time,
                    sts.end_time,
                    s.name AS service_name,
                    s.booking_type,
                    c.id AS category_id,
                    c.name AS category_name,
                    COALESCE(pi.default_supplier_id, s.supplier_id) AS supplier_id,
                    COALESCE(sup.shop_name, 'Golden Promise') AS supplier_name,
                    vr.name AS venue_room_name,
                    vr.capacity AS venue_room_capacity
             FROM booking_slot_reservations bsr
             INNER JOIN service_time_slots sts ON sts.id = bsr.slot_id
             INNER JOIN services s ON s.id = bsr.service_id
             LEFT JOIN package_items pi ON pi.id = bsr.package_item_id
             LEFT JOIN categories c ON c.id = COALESCE(pi.category_id, s.category_id)
             LEFT JOIN suppliers sup ON sup.supplier_id = COALESCE(pi.default_supplier_id, s.supplier_id)
             LEFT JOIN venue_rooms vr ON vr.id = pi.venue_room_id
             WHERE bsr.booking_id = :bid AND bsr.released_at IS NULL
             ORDER BY sts.start_time ASC, s.name ASC"
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
                    r.id AS replacement_request_id,
                    r.status AS replacement_status,
                    (
                        SELECT originated_r.id
                        FROM booking_supplier_replacements originated_r
                        WHERE originated_r.booking_supplier_id = bs.id
                        ORDER BY originated_r.id DESC
                        LIMIT 1
                    ) AS originated_replacement_request_id,
                    (
                        SELECT sd.file_url
                        FROM supplier_documents sd
                        WHERE sd.supplier_id = sup.supplier_id
                          AND sd.type = 'cover_photo'
                        ORDER BY sd.id DESC
                        LIMIT 1
                    ) AS thumbnail_url,
                    COALESCE(bs.decline_reason, (
                        SELECT repl.decline_reason
                        FROM booking_supplier_replacements repl
                        WHERE repl.booking_supplier_id = bs.id
                          AND repl.decline_reason IS NOT NULL
                        ORDER BY repl.id DESC
                        LIMIT 1
                    )) AS decline_reason
             FROM booking_suppliers bs
             LEFT JOIN suppliers sup ON bs.supplier_id = sup.supplier_id
             LEFT JOIN services svc ON svc.id = bs.service_id
             LEFT JOIN categories cat ON cat.id = bs.category_id
             LEFT JOIN booking_supplier_replacements r
                    ON r.booking_id = bs.booking_id
                   AND r.new_supplier_id = bs.supplier_id
                   AND r.new_service_id = bs.service_id
                   AND r.status IN ('assigned','accepted')
             WHERE bs.booking_id = :bid
             ORDER BY bs.id ASC"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        return $this->db->getmultidata();
    }

    /**
     * Get package items that have no default supplier (managed by platform).
     * These aren't inserted into booking_suppliers during booking creation.
     */
    public function getUnassignedPackageItems(int $bookingId): array
    {
        $this->db->dbquery(
            "SELECT pi.id AS package_item_id,
                    pi.service_id,
                    pi.category_id,
                    svc.name AS service_name,
                    cat.name AS category_name
             FROM booking_items bi
             INNER JOIN package_items pi
                 ON pi.package_id = bi.item_id
                AND bi.item_type = 'package'
                AND pi.deleted_at IS NULL
             LEFT JOIN services svc ON svc.id = pi.service_id
             LEFT JOIN categories cat ON cat.id = pi.category_id
             WHERE bi.booking_id = :bid
               AND pi.default_supplier_id IS NULL
               AND pi.service_id IS NOT NULL"
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
    public function getAllBookings(
        ?string $statusFilter = null,
        ?string $search = null,
        int $limit = 15,
        int $offset = 0,
        string $sort = 'event_asc',
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?string $typeFilter = null
    ): array
    {
        $sql = "SELECT b.*, u.name AS customer_name, u.email AS customer_email, u.phone AS customer_phone,
                       first_event.event_date,
                       first_event.event_start_time
                FROM bookings b
                LEFT JOIN users u ON b.user_id = u.user_id
                LEFT JOIN (
                    SELECT ed.booking_id,
                           MIN(ed.event_date) AS event_date,
                           SUBSTRING_INDEX(
                               GROUP_CONCAT(COALESCE(ed.start_time, '') ORDER BY ed.event_date ASC, ed.start_time ASC SEPARATOR ','),
                               ',',
                               1
                           ) AS event_start_time
                    FROM event_details ed
                    GROUP BY ed.booking_id
                ) first_event ON first_event.booking_id = b.id
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

        if ($dateFrom || $dateTo) {
            $sql .= " AND EXISTS (
                SELECT 1 FROM event_details ed_range
                WHERE ed_range.booking_id = b.id";
            if ($dateFrom) {
                $sql .= " AND ed_range.event_date >= :date_from";
                $params[':date_from'] = $dateFrom;
            }
            if ($dateTo) {
                $sql .= " AND ed_range.event_date <= :date_to";
                $params[':date_to'] = $dateTo;
            }
            $sql .= ")";
        }

        if ($typeFilter && $typeFilter !== 'all') {
            $hasPackage = "EXISTS (SELECT 1 FROM booking_items t1 WHERE t1.booking_id = b.id AND t1.item_type IN ('package','supplier_package'))";
            $hasStandalone = "EXISTS (SELECT 1 FROM booking_items t2 WHERE t2.booking_id = b.id AND t2.item_type = 'service' AND (t2.package_booking_item_id IS NULL OR t2.package_booking_item_id = 0))";
            $hasAddon = "EXISTS (SELECT 1 FROM booking_items t3 WHERE t3.booking_id = b.id AND t3.item_type = 'service' AND t3.package_booking_item_id IS NOT NULL AND t3.package_booking_item_id > 0)";

            if ($typeFilter === 'package') {
                $sql .= " AND {$hasPackage} AND NOT {$hasStandalone}";
            } elseif ($typeFilter === 'package_addons') {
                $sql .= " AND {$hasPackage} AND {$hasAddon}";
            } elseif ($typeFilter === 'supplier_package') {
                $sql .= " AND EXISTS (SELECT 1 FROM booking_items t4 WHERE t4.booking_id = b.id AND t4.item_type = 'supplier_package')
                         AND NOT EXISTS (SELECT 1 FROM booking_items t5 WHERE t5.booking_id = b.id AND t5.item_type = 'package')";
            } elseif ($typeFilter === 'custom') {
                $sql .= " AND NOT {$hasPackage} AND {$hasStandalone}";
            } elseif ($typeFilter === 'mixed') {
                $sql .= " AND {$hasPackage} AND {$hasStandalone}";
            }
        }

        $orderBy = match ($sort) {
            'event_desc' => '(event_date IS NULL) ASC, event_date DESC, event_start_time DESC, b.id DESC',
            'created_desc' => 'b.created_at DESC, b.id DESC',
            'created_asc' => 'b.created_at ASC, b.id ASC',
            'total_desc' => 'b.total_amount DESC, event_date ASC, b.id DESC',
            'total_asc' => 'b.total_amount ASC, event_date ASC, b.id ASC',
            default => '(event_date IS NULL) ASC,
                        (event_date < CURRENT_DATE()) ASC,
                        CASE WHEN event_date >= CURRENT_DATE() THEN event_date END ASC,
                        CASE WHEN event_date >= CURRENT_DATE() THEN event_start_time END ASC,
                        CASE WHEN event_date < CURRENT_DATE() THEN event_date END DESC,
                        CASE WHEN event_date < CURRENT_DATE() THEN event_start_time END DESC,
                        b.id ASC',
        };
        $sql .= " ORDER BY {$orderBy} LIMIT :limit OFFSET :offset";

        $this->db->dbquery($sql);
        foreach ($params as $key => $val) {
            $this->db->dbbind($key, $val);
        }
        $this->db->dbbind(':limit', $limit, PDO::PARAM_INT);
        $this->db->dbbind(':offset', $offset, PDO::PARAM_INT);

        return $this->db->getmultidata();
    }

    public function getAllBookingsCount(
        ?string $statusFilter = null,
        ?string $search = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?string $typeFilter = null
    ): int
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

        if ($dateFrom || $dateTo) {
            $sql .= " AND EXISTS (
                SELECT 1 FROM event_details ed_range
                WHERE ed_range.booking_id = b.id";
            if ($dateFrom) {
                $sql .= " AND ed_range.event_date >= :date_from";
                $params[':date_from'] = $dateFrom;
            }
            if ($dateTo) {
                $sql .= " AND ed_range.event_date <= :date_to";
                $params[':date_to'] = $dateTo;
            }
            $sql .= ")";
        }

        if ($typeFilter && $typeFilter !== 'all') {
            $hasPackage = "EXISTS (SELECT 1 FROM booking_items t1 WHERE t1.booking_id = b.id AND t1.item_type IN ('package','supplier_package'))";
            $hasStandalone = "EXISTS (SELECT 1 FROM booking_items t2 WHERE t2.booking_id = b.id AND t2.item_type = 'service' AND (t2.package_booking_item_id IS NULL OR t2.package_booking_item_id = 0))";
            $hasAddon = "EXISTS (SELECT 1 FROM booking_items t3 WHERE t3.booking_id = b.id AND t3.item_type = 'service' AND t3.package_booking_item_id IS NOT NULL AND t3.package_booking_item_id > 0)";

            if ($typeFilter === 'package') {
                $sql .= " AND {$hasPackage} AND NOT {$hasStandalone}";
            } elseif ($typeFilter === 'package_addons') {
                $sql .= " AND {$hasPackage} AND {$hasAddon}";
            } elseif ($typeFilter === 'supplier_package') {
                $sql .= " AND EXISTS (SELECT 1 FROM booking_items t4 WHERE t4.booking_id = b.id AND t4.item_type = 'supplier_package')
                         AND NOT EXISTS (SELECT 1 FROM booking_items t5 WHERE t5.booking_id = b.id AND t5.item_type = 'package')";
            } elseif ($typeFilter === 'custom') {
                $sql .= " AND NOT {$hasPackage} AND {$hasStandalone}";
            } elseif ($typeFilter === 'mixed') {
                $sql .= " AND {$hasPackage} AND {$hasStandalone}";
            }
        }

        $this->db->dbquery($sql);
        foreach ($params as $key => $val) {
            $this->db->dbbind($key, $val);
        }
        return (int)($this->db->getsingledata()['total'] ?? 0);
    }

    /**
     * Batch-fetch supplier display names for bookings shown in the admin list.
     *
     * @param int[] $bookingIds
     * @return array<int,string> booking_id => comma-separated supplier names
     */
    public function getSupplierNamesForBookings(array $bookingIds): array
    {
        if (empty($bookingIds)) {
            return [];
        }

        $placeholders = implode(',', array_map(static fn($i) => ':id' . $i, array_keys($bookingIds)));

        $this->db->dbquery(
            "SELECT bs.booking_id,
                    GROUP_CONCAT(DISTINCT sup.shop_name SEPARATOR ', ') AS supplier_names
             FROM booking_suppliers bs
             LEFT JOIN suppliers sup ON bs.supplier_id = sup.supplier_id
             WHERE bs.booking_id IN ({$placeholders})
             GROUP BY bs.booking_id"
        );

        foreach ($bookingIds as $i => $bid) {
            $this->db->dbbind(':id' . $i, (int)$bid, PDO::PARAM_INT);
        }

        $rows = $this->db->getmultidata();
        $result = [];
        foreach ($rows as $row) {
            $result[(int)$row['booking_id']] = (string)($row['supplier_names'] ?? '');
        }

        return $result;
    }

    /**
     * Batch-fetch the display type for a list of bookings.
     * Returns an associative array keyed by booking_id with values like
     * 'Package', 'Package + Add-ons', 'Supplier Package', 'Custom Services', 'Mixed'.
     *
     * @param int[] $bookingIds
     * @return array<int,string>  booking_id => type label
     */
    public function getBookingTypes(array $bookingIds): array
    {
        if (empty($bookingIds)) {
            return [];
        }

        $placeholders = implode(',', array_map(static fn($i) => ':id' . $i, array_keys($bookingIds)));

        $this->db->dbquery(
            "SELECT bi.booking_id,
                    MAX(CASE WHEN bi.item_type = 'package' THEN 1 ELSE 0 END) AS has_package,
                    MAX(CASE WHEN bi.item_type = 'supplier_package' THEN 1 ELSE 0 END) AS has_supplier_package,
                    MAX(CASE WHEN bi.item_type = 'service' AND (bi.package_booking_item_id IS NULL OR bi.package_booking_item_id = 0) THEN 1 ELSE 0 END) AS has_standalone_service,
                    MAX(CASE WHEN bi.package_booking_item_id IS NOT NULL AND bi.package_booking_item_id > 0 THEN 1 ELSE 0 END) AS has_addon
             FROM booking_items bi
             WHERE bi.booking_id IN ({$placeholders})
             GROUP BY bi.booking_id"
        );

        foreach ($bookingIds as $i => $bid) {
            $this->db->dbbind(':id' . $i, (int)$bid, PDO::PARAM_INT);
        }

        $rows = $this->db->getmultidata();
        $result = [];

        foreach ($rows as $row) {
            $bid = (int)$row['booking_id'];
            $hasPackage = (int)$row['has_package'] > 0;
            $hasSupplierPackage = (int)$row['has_supplier_package'] > 0;
            $hasStandaloneService = (int)$row['has_standalone_service'] > 0;
            $hasAddon = (int)$row['has_addon'] > 0;

            if ($hasPackage && $hasStandaloneService) {
                $result[$bid] = 'Mixed';
            } elseif ($hasPackage && $hasAddon) {
                $result[$bid] = 'Package + Add-ons';
            } elseif ($hasPackage) {
                $result[$bid] = 'Package';
            } elseif ($hasSupplierPackage) {
                $result[$bid] = 'Supplier Package';
            } elseif ($hasStandaloneService) {
                $result[$bid] = 'Custom Services';
            } else {
                $result[$bid] = 'Booking';
            }
        }

        return $result;
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
     * Get all assignments for a supplier — pending (needs response) and active (confirmed).
     * Joins with replacements table to flag replacement assignments.
     */
    public function getSupplierAssignments(int $supplierId): array
    {
        $this->db->dbquery(
            "SELECT b.id AS booking_id,
                    b.total_amount,
                    b.paid_amount,
                    b.payment_status,
                    b.status AS booking_status,
                    b.supplier_response_deadline,
                    u.name AS customer_name,
                    u.phone AS customer_phone,
                    bs.id AS booking_supplier_id,
                    bs.status AS supplier_status,
                    bs.confirmed_at,
                    bs.created_at AS assigned_at,
                    s.name AS assigned_service_name,
                    cat.name AS category_name,
                    MIN(ed.event_date) AS event_date,
                    (SELECT ed2.location FROM event_details ed2 WHERE ed2.booking_id = b.id LIMIT 1) AS venue,
                    r.id AS replacement_id,
                    r.status AS replacement_status,
                    r.price_delta,
                    r.requires_customer_approval,
                    osup.shop_name AS original_supplier_name,
                    os.name AS original_service_name,
                    bs.decline_reason
             FROM booking_suppliers bs
             INNER JOIN bookings b ON bs.booking_id = b.id
             LEFT JOIN users u ON b.user_id = u.user_id
             LEFT JOIN event_details ed ON ed.booking_id = b.id
             LEFT JOIN services s ON s.id = bs.service_id
             LEFT JOIN categories cat ON cat.id = bs.category_id
             LEFT JOIN booking_supplier_replacements r
                   ON r.booking_id = b.id AND r.new_supplier_id = bs.supplier_id
                   AND r.status = 'assigned'
             LEFT JOIN suppliers osup ON osup.supplier_id = r.old_supplier_id
             LEFT JOIN services os ON os.id = r.old_service_id
             WHERE bs.supplier_id = :sid
               AND bs.status IN ('pending', 'confirmed', 'in_progress', 'decline_requested')
               AND b.status NOT IN ('draft', 'pending_payment', 'cancelled')
             GROUP BY b.id, bs.id
             ORDER BY
               CASE WHEN bs.status = 'pending' THEN 0 ELSE 1 END ASC,
               bs.created_at DESC"
        );
        $this->db->dbbind(':sid', $supplierId, PDO::PARAM_INT);
        return $this->db->getmultidata() ?: [];
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
    public function updateSupplierStatus(int $bookingSupplierId, string $status, array $allowedCurrentStatuses = []): bool
    {
        $setClause = "status = :status";
        if ($status === 'confirmed') {
            $setClause .= ", confirmed_at = NOW()";
        } elseif ($status === 'completed') {
            $setClause .= ", completed_at = NOW()";
        }

        $where = 'id = :id';
        if ($allowedCurrentStatuses) {
            $placeholders = [];
            foreach (array_values($allowedCurrentStatuses) as $index => $allowedStatus) {
                $placeholder = ':current_' . $index;
                $placeholders[] = $placeholder;
            }
            $where .= ' AND status IN (' . implode(', ', $placeholders) . ')';
        }
        $this->db->dbquery("UPDATE booking_suppliers SET {$setClause} WHERE {$where}");
        $this->db->dbbind(':status', $status);
        $this->db->dbbind(':id', $bookingSupplierId, PDO::PARAM_INT);
        foreach (array_values($allowedCurrentStatuses) as $index => $allowedStatus) {
            $this->db->dbbind(':current_' . $index, $allowedStatus);
        }

        $this->db->dbexecute();
        return $this->db->rowcount() === 1;
    }

    /**
     * Supplier requests a decline for a package booking within the cutoff window.
     * Sets status to 'decline_requested' and stores the reason for admin review.
     */
    public function requestSupplierDecline(int $bookingSupplierId, string $reason): bool
    {
        $this->db->dbquery(
            "UPDATE booking_suppliers
                SET status = 'decline_requested', decline_reason = :reason, declined_at = NOW()
              WHERE id = :id AND status IN ('confirmed', 'pending')"
        );
        $this->db->dbbind(':id', $bookingSupplierId, PDO::PARAM_INT);
        $this->db->dbbind(':reason', $reason !== '' ? $reason : null);
        $this->db->dbexecute();
        return $this->db->rowcount() === 1;
    }

    /**
     * Admin approves a decline request — transitions to needs_replacement
     * so the existing replacement flow kicks in.
     */
    public function approveDeclineRequest(int $bookingSupplierId): bool
    {
        $this->db->dbquery(
            "UPDATE booking_suppliers
                SET status = 'needs_replacement'
              WHERE id = :id AND status IN ('decline_requested', 'supplier_cancellation_requested')"
        );
        $this->db->dbbind(':id', $bookingSupplierId, PDO::PARAM_INT);
        $this->db->dbexecute();
        return $this->db->rowcount() === 1;
    }

    /**
     * Admin rejects a decline request — reverts to pending.
     */
    public function rejectDeclineRequest(int $bookingSupplierId, string $restoreStatus = 'pending'): bool
    {
        $restoreStatus = in_array($restoreStatus, ['pending', 'accepted', 'confirmed', 'in_progress'], true)
            ? $restoreStatus
            : 'pending';
        $this->db->dbquery(
            "UPDATE booking_suppliers
                SET status = :status, decline_reason = NULL, declined_at = NULL
              WHERE id = :id AND status IN ('decline_requested', 'supplier_cancellation_requested')"
        );
        $this->db->dbbind(':id', $bookingSupplierId, PDO::PARAM_INT);
        $this->db->dbbind(':status', $restoreStatus);
        $this->db->dbexecute();
        return $this->db->rowcount() === 1;
    }

    public function countSupplierCancellationRequests(int $bookingId): int
    {
        $this->db->dbquery(
            "SELECT COUNT(*) AS cnt
             FROM booking_suppliers
             WHERE booking_id = :bid AND status = 'supplier_cancellation_requested'"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        return (int)(($this->db->getsingledata()['cnt'] ?? 0));
    }

    /**
     * Mark a supplier row as awaiting an admin-chosen replacement (decline on a
     * confirmed package booking). Unlike a plain 'rejected', this keeps the
     * booking alive so the platform can swap in another supplier.
     */
    public function markSupplierNeedsReplacement(int $bookingSupplierId, string $reason = ''): bool
    {
        $this->db->dbquery(
            "UPDATE booking_suppliers
                SET status = 'needs_replacement', declined_at = NOW(),
                    decline_reason = :reason
              WHERE id = :id AND status IN ('confirmed', 'pending')"
        );
        $this->db->dbbind(':reason', $reason !== '' ? $reason : null, $reason !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $this->db->dbbind(':id', $bookingSupplierId, PDO::PARAM_INT);
        $this->db->dbexecute();
        return $this->db->rowcount() === 1;
    }

    /**
     * Find suppliers in 'needs_replacement' status that have no corresponding
     * booking_supplier_replacements row (legacy data from before the fix).
     * Returns full booking_suppliers rows needed by createReplacementRequest().
     */
    public function findSuppliersNeedingReplacementRequests(): array
    {
        $this->db->dbquery(
            "SELECT bs.*
             FROM booking_suppliers bs
             WHERE bs.status = 'needs_replacement'
               AND NOT EXISTS (
                   SELECT 1 FROM booking_supplier_replacements r
                   WHERE r.booking_supplier_id = bs.id
               )"
        );
        return $this->db->getmultidata();
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
        $packageItemColumn = $this->replacementHasColumn('package_item_id')
            ? ', package_item_id'
            : '';
        $packageItemValue = $this->replacementHasColumn('package_item_id')
            ? ', :package_item_id'
            : '';
        $this->db->dbquery(
            "INSERT INTO booking_supplier_replacements
                (booking_id, booking_supplier_id{$packageItemColumn}, category_id,
                 old_supplier_id, old_service_id, old_price,
                 status, decline_reason, created_at)
             VALUES
                (:bid, :bsid{$packageItemValue}, :cat,
                 :old_sup, :old_svc, :old_price,
                 'pending_admin', :reason, NOW())"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        $this->db->dbbind(':bsid', (int)($supplierRow['id'] ?? 0), PDO::PARAM_INT);
        if ($this->replacementHasColumn('package_item_id')) {
            $this->db->dbbind(
                ':package_item_id',
                !empty($supplierRow['package_item_id']) ? (int)$supplierRow['package_item_id'] : null,
                !empty($supplierRow['package_item_id']) ? PDO::PARAM_INT : PDO::PARAM_NULL
            );
        }
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
        // CAST to SIGNED before subtracting — confirmed_* are UNSIGNED, and
        // 0 - 1 underflows (errors under strict SQL mode) before GREATEST applies.
        $poolUpdate = $hasPools ? ", {$poolCol} = GREATEST(CAST({$poolCol} AS SIGNED) - 1, 0)" : '';

        $this->db->dbquery(
            "UPDATE service_time_slots
                SET confirmed_count = GREATEST(CAST(confirmed_count AS SIGNED) - 1, 0){$poolUpdate},
                    status = CASE WHEN GREATEST(CAST(confirmed_count AS SIGNED) - 1, 0) < max_concurrent AND status <> 'blocked'
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
        $packageItemExpression = $this->replacementHasColumn('package_item_id')
            ? 'COALESCE(r.package_item_id, bs.package_item_id)'
            : 'bs.package_item_id';
        $this->db->dbquery(
            "SELECT r.*,
                    sup.shop_name AS old_shop_name,
                    cat.name      AS category_name,
                    osvc.name     AS old_service_name,
                    nsup.shop_name AS new_shop_name,
                    nsvc.name AS new_service_name,
                    p.status AS delta_payment_status,
                    p.payment_slip_path AS delta_payment_slip,
                    p.paid_amount AS delta_paid_amount,
                    p.bank_name AS delta_bank_name,
                    p.account_name AS delta_account_name,
                    p.mobile_number AS delta_mobile_number,
                    p.transaction_ref AS delta_transaction_ref,
                    p.paid_at AS delta_paid_at,
                    pkg.package_id AS package_id,
                    pkg.name       AS package_name,
                    {$packageItemExpression} AS original_package_item_id,
                    (SELECT event_date FROM event_details ed
                      WHERE ed.booking_id = r.booking_id ORDER BY ed.event_date ASC LIMIT 1) AS event_date
               FROM booking_supplier_replacements r
               LEFT JOIN booking_suppliers bs ON bs.id = r.booking_supplier_id
               LEFT JOIN package_items pi ON pi.id = {$packageItemExpression}
               LEFT JOIN packages pkg ON pkg.package_id = pi.package_id
               LEFT JOIN suppliers  sup  ON sup.supplier_id = r.old_supplier_id
               LEFT JOIN suppliers nsup ON nsup.supplier_id = r.new_supplier_id
               LEFT JOIN categories cat  ON cat.id = r.category_id
               LEFT JOIN services   osvc ON osvc.id = r.old_service_id
               LEFT JOIN services   nsvc ON nsvc.id = r.new_service_id
               LEFT JOIN payments      p ON p.id = r.delta_payment_id
              WHERE r.id = :id
              LIMIT 1"
        );
        $this->db->dbbind(':id', $replacementId, PDO::PARAM_INT);
        return $this->db->getsingledata();
    }

    /**
     * Open replacement requests for the admin tracking view.
     */
    public function getPendingReplacements(): array
    {
        $packageItemExpression = $this->replacementHasColumn('package_item_id')
            ? 'COALESCE(r.package_item_id, bs.package_item_id)'
            : 'bs.package_item_id';
        $this->db->dbquery(
            "SELECT r.*,
                    sup.shop_name AS old_shop_name,
                    cat.name      AS category_name,
                    osvc.name     AS old_service_name,
                    nsup.shop_name AS new_shop_name,
                    nsvc.name AS new_service_name,
                    pkg.package_id AS package_id,
                    pkg.name       AS package_name,
                    b.status      AS booking_status,
                    u.name        AS customer_name,
                    p.status      AS delta_payment_status,
                    p.payment_slip_path AS delta_payment_slip,
                    (SELECT event_date FROM event_details ed
                      WHERE ed.booking_id = r.booking_id ORDER BY ed.event_date ASC LIMIT 1) AS event_date
               FROM booking_supplier_replacements r
               LEFT JOIN booking_suppliers bs ON bs.id = r.booking_supplier_id
               LEFT JOIN package_items pi ON pi.id = {$packageItemExpression}
               LEFT JOIN packages pkg ON pkg.package_id = pi.package_id
               LEFT JOIN suppliers  sup  ON sup.supplier_id = r.old_supplier_id
               LEFT JOIN suppliers  nsup ON nsup.supplier_id = r.new_supplier_id
               LEFT JOIN categories cat  ON cat.id = r.category_id
               LEFT JOIN services   osvc ON osvc.id = r.old_service_id
               LEFT JOIN services   nsvc ON nsvc.id = r.new_service_id
               LEFT JOIN bookings   b    ON b.id = r.booking_id
               LEFT JOIN users      u    ON u.user_id = b.user_id
               LEFT JOIN payments   p    ON p.id = r.delta_payment_id
              WHERE r.status IN (
                    'pending_admin','declined_again','rejected_by_customer',
                    'pending_customer','assigned'
              )
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

        // Services already rejected for this replacement (by the customer or a
        // backed-out supplier) must not be offered again on re-pick.
        $rejectedIds = array_values(array_filter(array_map(
            'intval',
            explode(',', (string)($r['rejected_service_ids'] ?? ''))
        )));

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
        // Exclude services already rejected on a previous pick for this request.
        if ($rejectedIds) {
            $placeholders = implode(',', array_map(static fn($i) => ':rej' . $i, array_keys($rejectedIds)));
            $sql .= " AND s.id NOT IN ($placeholders)";
        }
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
        foreach ($rejectedIds as $i => $sid) {
            $this->db->dbbind(':rej' . $i, $sid, PDO::PARAM_INT);
        }
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
            // Three-tier logic: beyond cap needs customer approval + payment;
            // within cap is auto-assigned with platform absorbing the upgrade.
            $row['over_cap'] = $ceiling !== null && $price > $ceiling;
            $row['needs_customer_approval'] = $row['over_cap'];
            $row['within_cap_upgrade'] = $price > $oldPrice && !$row['over_cap'];
        }
        unset($row);
        return $rows;
    }

    public function replacementInvitationsEnabled(): bool
    {
        return $this->tableExists('booking_supplier_replacement_invitations');
    }

    public function getReplacementInvitations(int $replacementId): array
    {
        if (!$this->replacementInvitationsEnabled()) {
            return [];
        }
        $this->db->dbquery(
            "SELECT ri.*, sup.shop_name, svc.name AS service_name, cat.name AS category_name
               FROM booking_supplier_replacement_invitations ri
               INNER JOIN suppliers sup ON sup.supplier_id = ri.supplier_id
               INNER JOIN services svc ON svc.id = ri.service_id
               LEFT JOIN categories cat ON cat.id = svc.category_id
              WHERE ri.replacement_id = :rid
              ORDER BY FIELD(ri.status, 'chosen','accepted','invited','declined','expired','cancelled'),
                       ri.created_at DESC"
        );
        $this->db->dbbind(':rid', $replacementId, PDO::PARAM_INT);
        return $this->db->getmultidata() ?: [];
    }

    public function inviteReplacementSuppliers(int $replacementId, array $serviceIds, int $adminId): array
    {
        if (!$this->replacementInvitationsEnabled()) {
            return ['created' => 0, 'skipped' => 0, 'error' => 'Replacement invitation migration has not been applied.'];
        }
        $replacement = $this->getReplacement($replacementId);
        if (!$replacement || !in_array($replacement['status'] ?? '', ['pending_admin', 'declined_again', 'rejected_by_customer'], true)) {
            return ['created' => 0, 'skipped' => 0, 'error' => 'Replacement is not open for supplier invitations.'];
        }

        $serviceIds = array_values(array_unique(array_filter(array_map('intval', $serviceIds))));
        if (!$serviceIds) {
            return ['created' => 0, 'skipped' => 0, 'error' => 'Choose at least one supplier service.'];
        }

        $candidates = [];
        foreach ($this->findReplacementCandidates($replacementId) as $candidate) {
            $candidates[(int)$candidate['service_id']] = $candidate;
        }

        $created = 0;
        $skipped = 0;
        foreach ($serviceIds as $serviceId) {
            if (!isset($candidates[$serviceId])) {
                $skipped++;
                continue;
            }
            $candidate = $candidates[$serviceId];
            $this->db->dbquery(
                "INSERT INTO booking_supplier_replacement_invitations
                    (replacement_id, booking_id, supplier_id, service_id, price, price_delta,
                     status, invited_by_admin_id, created_at)
                 VALUES
                    (:rid, :bid, :sid, :svc, :price, :delta, 'invited', :admin, NOW())
                 ON DUPLICATE KEY UPDATE
                    supplier_id = VALUES(supplier_id),
                    price = VALUES(price),
                    price_delta = VALUES(price_delta),
                    status = IF(status IN ('declined','expired','cancelled'), 'invited', status),
                    invited_by_admin_id = VALUES(invited_by_admin_id)"
            );
            $this->db->dbbind(':rid', $replacementId, PDO::PARAM_INT);
            $this->db->dbbind(':bid', (int)$replacement['booking_id'], PDO::PARAM_INT);
            $this->db->dbbind(':sid', (int)$candidate['supplier_id'], PDO::PARAM_INT);
            $this->db->dbbind(':svc', $serviceId, PDO::PARAM_INT);
            $this->db->dbbind(':price', (float)($candidate['price'] ?? 0));
            $this->db->dbbind(':delta', (float)($candidate['price_delta'] ?? 0));
            $this->db->dbbind(':admin', $adminId > 0 ? $adminId : null, $adminId > 0 ? PDO::PARAM_INT : PDO::PARAM_NULL);
            $this->db->dbexecute();
            $created++;
        }

        return ['created' => $created, 'skipped' => $skipped, 'error' => null];
    }

    public function getSupplierReplacementInvitations(int $supplierId): array
    {
        if (!$this->replacementInvitationsEnabled()) {
            return [];
        }
        $this->db->dbquery(
            "SELECT ri.*, r.old_supplier_id, r.old_service_id, r.decline_reason,
                    b.status AS booking_status, b.total_amount, b.paid_amount, b.payment_status,
                    b.supplier_response_deadline,
                    u.name AS customer_name,
                    svc.name AS service_name,
                    cat.name AS category_name,
                    old_sup.shop_name AS original_supplier_name,
                    old_svc.name AS original_service_name,
                    (SELECT event_date FROM event_details ed
                      WHERE ed.booking_id = ri.booking_id ORDER BY ed.event_date ASC LIMIT 1) AS event_date,
                    (SELECT location FROM event_details ed2
                      WHERE ed2.booking_id = ri.booking_id ORDER BY ed2.event_date ASC LIMIT 1) AS venue
               FROM booking_supplier_replacement_invitations ri
               INNER JOIN booking_supplier_replacements r ON r.id = ri.replacement_id
               INNER JOIN bookings b ON b.id = ri.booking_id
               LEFT JOIN users u ON u.user_id = b.user_id
               INNER JOIN services svc ON svc.id = ri.service_id
               LEFT JOIN categories cat ON cat.id = svc.category_id
               LEFT JOIN suppliers old_sup ON old_sup.supplier_id = r.old_supplier_id
               LEFT JOIN services old_svc ON old_svc.id = r.old_service_id
              WHERE ri.supplier_id = :sid
                AND ri.status IN ('invited','accepted','chosen')
              ORDER BY ri.created_at DESC"
        );
        $this->db->dbbind(':sid', $supplierId, PDO::PARAM_INT);
        return $this->db->getmultidata() ?: [];
    }

    public function respondReplacementInvitation(int $invitationId, int $supplierId, string $action): array|false
    {
        if (!$this->replacementInvitationsEnabled() || !in_array($action, ['accept', 'decline'], true)) {
            return false;
        }
        $newStatus = $action === 'accept' ? 'accepted' : 'declined';
        $this->db->dbquery(
            "UPDATE booking_supplier_replacement_invitations
                SET status = :status, responded_at = NOW()
              WHERE id = :id AND supplier_id = :sid AND status = 'invited'"
        );
        $this->db->dbbind(':status', $newStatus);
        $this->db->dbbind(':id', $invitationId, PDO::PARAM_INT);
        $this->db->dbbind(':sid', $supplierId, PDO::PARAM_INT);
        $this->db->dbexecute();
        if ($this->db->rowcount() !== 1) {
            return false;
        }
        $this->db->dbquery(
            "SELECT ri.*, svc.name AS service_name, sup.shop_name
               FROM booking_supplier_replacement_invitations ri
               INNER JOIN services svc ON svc.id = ri.service_id
               INNER JOIN suppliers sup ON sup.supplier_id = ri.supplier_id
              WHERE ri.id = :id LIMIT 1"
        );
        $this->db->dbbind(':id', $invitationId, PDO::PARAM_INT);
        return $this->db->getsingledata();
    }

    public function getAcceptedReplacementChoicesForBooking(int $bookingId): array
    {
        if (!$this->replacementInvitationsEnabled()) {
            return [];
        }
        $this->db->dbquery(
            "SELECT ri.*, r.status AS replacement_status, r.old_price, r.old_service_id,
                    old_svc.name AS old_service_name,
                    old_sup.shop_name AS old_shop_name,
                    sup.shop_name, svc.name AS service_name, cat.name AS category_name
               FROM booking_supplier_replacement_invitations ri
               INNER JOIN booking_supplier_replacements r ON r.id = ri.replacement_id
               LEFT JOIN services old_svc ON old_svc.id = r.old_service_id
               LEFT JOIN suppliers old_sup ON old_sup.supplier_id = r.old_supplier_id
               INNER JOIN suppliers sup ON sup.supplier_id = ri.supplier_id
               INNER JOIN services svc ON svc.id = ri.service_id
               LEFT JOIN categories cat ON cat.id = svc.category_id
              WHERE ri.booking_id = :bid
                AND ri.status = 'accepted'
                AND r.status IN ('pending_admin','declined_again','rejected_by_customer')
              ORDER BY ri.price ASC, ri.created_at ASC"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        return $this->db->getmultidata() ?: [];
    }

    public function chooseAcceptedReplacementInvitation(int $invitationId, int $userId): array|false
    {
        if (!$this->replacementInvitationsEnabled()) {
            return false;
        }
        $this->db->dbquery(
            "SELECT ri.*, r.old_price, r.booking_supplier_id, r.status AS replacement_status,
                    b.user_id, sup.shop_name, svc.name AS service_name
               FROM booking_supplier_replacement_invitations ri
               INNER JOIN booking_supplier_replacements r ON r.id = ri.replacement_id
               INNER JOIN bookings b ON b.id = ri.booking_id
               INNER JOIN suppliers sup ON sup.supplier_id = ri.supplier_id
               INNER JOIN services svc ON svc.id = ri.service_id
              WHERE ri.id = :id AND ri.status = 'accepted' LIMIT 1"
        );
        $this->db->dbbind(':id', $invitationId, PDO::PARAM_INT);
        $choice = $this->db->getsingledata();
        if (!$choice || (int)($choice['user_id'] ?? 0) !== $userId) {
            return false;
        }

        $replacementId = (int)$choice['replacement_id'];
        $oldPrice = (float)($choice['old_price'] ?? 0);
        $newPrice = (float)($choice['price'] ?? 0);
        $delta = round($newPrice - $oldPrice, 2);

        $this->db->beginTransaction();
        try {
            $this->db->dbquery(
                "UPDATE booking_supplier_replacements
                    SET new_supplier_id = :sid,
                        new_service_id = :svc,
                        new_price = :price,
                        price_delta = :delta,
                        requires_customer_approval = :requires,
                        proposed_at = NOW()
                  WHERE id = :rid
                    AND status IN ('pending_admin','declined_again','rejected_by_customer')"
            );
            $this->db->dbbind(':sid', (int)$choice['supplier_id'], PDO::PARAM_INT);
            $this->db->dbbind(':svc', (int)$choice['service_id'], PDO::PARAM_INT);
            $this->db->dbbind(':price', $newPrice);
            $this->db->dbbind(':delta', $delta);
            $this->db->dbbind(':requires', $delta > 0 ? 1 : 0, PDO::PARAM_INT);
            $this->db->dbbind(':rid', $replacementId, PDO::PARAM_INT);
            $this->db->dbexecute();
            if ($this->db->rowcount() !== 1) {
                throw new RuntimeException('Replacement is no longer open.');
            }

            $this->db->dbquery(
                "UPDATE booking_supplier_replacement_invitations
                    SET status = 'chosen', chosen_at = NOW()
                  WHERE id = :id AND status = 'accepted'"
            );
            $this->db->dbbind(':id', $invitationId, PDO::PARAM_INT);
            $this->db->dbexecute();
            if ($this->db->rowcount() !== 1) {
                throw new RuntimeException('Replacement choice is no longer available.');
            }

            $this->db->dbquery(
                "UPDATE booking_supplier_replacement_invitations
                    SET status = 'cancelled'
                  WHERE replacement_id = :rid
                    AND id <> :id
                    AND status IN ('invited','accepted')"
            );
            $this->db->dbbind(':rid', $replacementId, PDO::PARAM_INT);
            $this->db->dbbind(':id', $invitationId, PDO::PARAM_INT);
            $this->db->dbexecute();
            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            error_log('chooseAcceptedReplacementInvitation failed: ' . $e->getMessage());
            return false;
        }

        $choice['price_delta'] = $delta;
        return $choice;
    }

    public function markChosenInvitationSwapAccepted(int $replacementId): bool
    {
        if (!$this->replacementInvitationsEnabled()) {
            return false;
        }
        $r = $this->getReplacement($replacementId);
        if (!$r || (int)($r['new_supplier_id'] ?? 0) <= 0 || (int)($r['new_service_id'] ?? 0) <= 0) {
            return false;
        }
        $bookingId = (int)$r['booking_id'];
        $supplierId = (int)$r['new_supplier_id'];
        $serviceId = (int)$r['new_service_id'];

        $this->db->dbquery(
            "UPDATE booking_suppliers
                SET status = 'confirmed', confirmed_at = COALESCE(confirmed_at, NOW())
              WHERE booking_id = :bid
                AND supplier_id = :sid
                AND service_id = :svc
                AND status = 'pending'
              ORDER BY id DESC LIMIT 1"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        $this->db->dbbind(':sid', $supplierId, PDO::PARAM_INT);
        $this->db->dbbind(':svc', $serviceId, PDO::PARAM_INT);
        $this->db->dbexecute();

        $this->updateReplacement($replacementId, [
            'status' => 'accepted',
            'resolved_at' => date('Y-m-d H:i:s'),
        ]);

        if ($this->bookingReplacementsResolved($bookingId)) {
            $this->updateStatus($bookingId, 'confirmed');
        }
        return true;
    }

    /**
     * Update a replacement row's lifecycle fields. $fields is a whitelist map.
     */
    public function updateReplacement(int $replacementId, array $fields): bool
    {
        $allowed = [
            'new_supplier_id','new_service_id','new_price','price_delta',
            'requires_customer_approval','customer_approved_at','customer_opt_out_deadline',
            'proposed_at','delta_payment_id',
            'status','chosen_by_admin_id','assigned_at','resolved_at','rejected_service_ids',
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
        $this->replacementSwapError = null;
        $r = $this->getReplacement($replacementId);
        if (!$r || (int)($r['new_service_id'] ?? 0) <= 0) {
            $this->replacementSwapError = 'The replacement proposal is incomplete or no longer available.';
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
            $this->replacementSwapError = 'The proposed replacement service no longer exists.';
            return false;
        }
        $newSupplierId = (int)$newSvc['supplier_id'];

        $this->db->beginTransaction();
        try {
            $packageItemId = (int)($r['original_package_item_id'] ?? 0);

            // 1. Old supplier row -> replaced. Clear its package-item identity
            // so the active replacement can own the unique booking/package line.
            // Older/live service-cancellation requests can still be in
            // supplier_cancellation_requested when the replacement is picked.
            $this->db->dbquery(
                "UPDATE booking_suppliers
                    SET status = 'replaced', package_item_id = NULL
                  WHERE id = :id
                    AND status IN ('needs_replacement','confirmed','decline_requested','supplier_cancellation_requested')"
            );
            $this->db->dbbind(':id', $oldSupplierRow, PDO::PARAM_INT);
            $this->db->dbexecute();
            if ($this->db->rowcount() !== 1) {
                throw new RuntimeException('Original supplier line is no longer replaceable.');
            }

            if ($packageItemId > 0) {
                $this->db->dbquery(
                    "UPDATE booking_suppliers
                        SET package_item_id = NULL
                      WHERE booking_id = :bid
                        AND package_item_id = :package_item_id
                        AND id <> :old_id
                        AND status IN ('replaced','rejected','cancelled')"
                );
                $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
                $this->db->dbbind(':package_item_id', $packageItemId, PDO::PARAM_INT);
                $this->db->dbbind(':old_id', $oldSupplierRow, PDO::PARAM_INT);
                $this->db->dbexecute();

                $this->db->dbquery(
                    "SELECT id, status
                       FROM booking_suppliers
                      WHERE booking_id = :bid
                        AND package_item_id = :package_item_id
                      LIMIT 1"
                );
                $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
                $this->db->dbbind(':package_item_id', $packageItemId, PDO::PARAM_INT);
                $activePackageOwner = $this->db->getsingledata();
                if ($activePackageOwner) {
                    throw new RuntimeException('This package service already has an active supplier assignment.');
                }
            }

            $replacementStart = '09:00:00';
            $replacementEnd = '10:00:00';

            // 2. Release the exact package slot recorded for the old package item.
            if ($oldServiceId > 0 && $eventDate) {
                $this->db->dbquery(
                    "SELECT bsr.id AS reservation_id, st.id AS slot_id, st.start_time, st.end_time
                       FROM booking_slot_reservations bsr
                       INNER JOIN service_time_slots st ON st.id = bsr.slot_id
                      WHERE bsr.booking_id = :bid
                        AND bsr.source = 'package'
                        AND bsr.service_id = :sid
                        AND bsr.released_at IS NULL
                        AND (:package_item_id = 0 OR bsr.package_item_id = :package_item_id_match)
                      ORDER BY bsr.id ASC
                      LIMIT 1"
                );
                $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
                $this->db->dbbind(':sid', $oldServiceId, PDO::PARAM_INT);
                $this->db->dbbind(':package_item_id', $packageItemId, PDO::PARAM_INT);
                $this->db->dbbind(':package_item_id_match', $packageItemId, PDO::PARAM_INT);
                $oldSlot = $this->db->getsingledata();
                if ($oldSlot) {
                    $replacementStart = (string)$oldSlot['start_time'];
                    $replacementEnd = (string)$oldSlot['end_time'];
                    $this->db->dbquery(
                        "UPDATE booking_slot_reservations
                            SET released_at = NOW()
                          WHERE id = :id AND released_at IS NULL"
                    );
                    $this->db->dbbind(':id', (int)$oldSlot['reservation_id'], PDO::PARAM_INT);
                    $this->db->dbexecute();
                    if ($this->db->rowcount() === 1) {
                        $this->releaseServiceSlot((int)$oldSlot['slot_id'], 'package');
                    }
                }
            }

            // 3. Insert the new supplier row.
            $finalPrice = $applyNewPrice ? $newPrice : $oldPrice;
            $this->db->dbquery(
                "INSERT INTO booking_suppliers
                    (booking_id, supplier_id, service_id, category_id, package_item_id,
                     item_price, status, created_at)
                 VALUES (:bid, :sup, :svc, :cat, :package_item_id,
                         :price, 'pending', NOW())"
            );
            $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
            $this->db->dbbind(':sup', $newSupplierId, PDO::PARAM_INT);
            $this->db->dbbind(':svc', $newServiceId, PDO::PARAM_INT);
            $this->db->dbbind(':cat', $categoryId);
            $this->db->dbbind(
                ':package_item_id',
                $packageItemId > 0 ? $packageItemId : null,
                $packageItemId > 0 ? PDO::PARAM_INT : PDO::PARAM_NULL
            );
            $this->db->dbbind(':price', $finalPrice);
            if (!$this->db->dbexecute()) {
                throw new RuntimeException('Replacement supplier line could not be created.');
            }
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
                    $newServiceId, $eventDate, $replacementStart, $replacementEnd,
                    $conc['max_concurrent'] ?? 1,
                    $conc['max_concurrent_package'] ?? 0,
                    $conc['max_concurrent_customize'] ?? 0
                );
                if (
                    !$slotId
                    || !$this->reserveServiceSlot($slotId, 'package')
                    || !$this->recordSlotReservation(
                        $bookingId,
                        $slotId,
                        'package',
                        null,
                        $newServiceId,
                        $packageItemId > 0 ? $packageItemId : null
                    )
                ) {
                    throw new RuntimeException('Replacement service has no remaining capacity.');
                }
            }

            // 6. The package booking item remains the package itself. Its
            // per-service display comes from the active booking_suppliers row.

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
            $this->replacementSwapError = str_contains($e->getMessage(), 'booking_slot_reservations')
                ? 'The booking slot reservation migration has not been applied.'
                : $e->getMessage();
            error_log('performReplacementSwap failed: ' . $e->getMessage());
            return false;
        }
    }

    public function getReplacementSwapError(): string
    {
        return $this->replacementSwapError ?: 'The replacement swap could not be completed.';
    }

    /**
     * Create a pending manual payment row for a replacement price delta. The
     * customer pays this (slip upload) and admin verification finalizes the swap.
     */
    public function createReplacementDeltaPayment(int $bookingId, float $delta): int
    {
        $feePercent = get_platform_fee_percent();
        $platformFee = round($delta * ($feePercent / 100), 2);
        $supplierAmount = round($delta - $platformFee, 2);

        $this->db->dbquery(
            "INSERT INTO payments (booking_id, amount, platform_fee, supplier_amount, type, status, created_at)
             VALUES (:bid, :amt, :pfee, :samt, 'replacement_delta', 'pending', NOW())"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        $this->db->dbbind(':amt', number_format($delta, 2, '.', ''), PDO::PARAM_STR);
        $this->db->dbbind(':pfee', number_format($platformFee, 2, '.', ''), PDO::PARAM_STR);
        $this->db->dbbind(':samt', number_format($supplierAmount, 2, '.', ''), PDO::PARAM_STR);
        $this->db->dbexecute();
        return (int)$this->db->lastinsertid();
    }

    /**
     * Map a supplier to its owning user id (for targeted notifications).
     */
    public function getSupplierUserId(int $supplierId): int
    {
        if ($supplierId <= 0) {
            return 0;
        }
        $this->db->dbquery("SELECT user_id FROM suppliers WHERE supplier_id = :sid LIMIT 1");
        $this->db->dbbind(':sid', $supplierId, PDO::PARAM_INT);
        $row = $this->db->getsingledata();
        return (int)($row['user_id'] ?? 0);
    }

    /**
     * Append a service id to a replacement's rejected list (deduped) so the
     * candidate finder won't re-offer it on the next pick.
     */
    public function appendRejectedService(int $replacementId, int $serviceId): bool
    {
        if ($serviceId <= 0) {
            return false;
        }
        $r = $this->getReplacement($replacementId);
        if (!$r) {
            return false;
        }
        $ids = array_values(array_filter(array_map('intval', explode(',', (string)($r['rejected_service_ids'] ?? '')))));
        if (!in_array($serviceId, $ids, true)) {
            $ids[] = $serviceId;
        }
        return $this->updateReplacement($replacementId, ['rejected_service_ids' => implode(',', $ids)]);
    }

    /**
     * Reverse a replacement price-delta that was already paid (payment in
     * 'success') when the replacement later falls through. Refunds the payment
     * and subtracts the delta from the booking totals — exactly once.
     * Safe to call when nothing was paid (returns false, no-op).
     */
    public function reverseReplacementDeltaIfPaid(int $replacementId): bool
    {
        $r = $this->getReplacement($replacementId);
        if (!$r) {
            return false;
        }
        $paymentId = (int)($r['delta_payment_id'] ?? 0);
        $delta = round((float)($r['price_delta'] ?? 0), 2);
        if ($paymentId <= 0 || $delta <= 0) {
            return false;
        }

        // Only reverse a payment that is currently 'success' and not yet
        // refunded (idempotent: a second call finds escrow_status already
        // 'refunded' and rowcount is 0).
        $this->db->dbquery(
            "UPDATE payments
                SET escrow_status = 'refunded', verified_at = NOW()
              WHERE id = :pid AND type = 'replacement_delta' AND status = 'success' AND escrow_status != 'refunded'"
        );
        $this->db->dbbind(':pid', $paymentId, PDO::PARAM_INT);
        $this->db->dbexecute();
        if ($this->db->rowcount() !== 1) {
            return false; // nothing to reverse (not paid, or already refunded)
        }

        $this->db->dbquery(
            "UPDATE bookings
                SET total_amount = GREATEST(COALESCE(total_amount,0) - :d, 0),
                    paid_amount  = GREATEST(COALESCE(paid_amount,0) - :d2, 0)
              WHERE id = :bid"
        );
        $this->db->dbbind(':d', $delta);
        $this->db->dbbind(':d2', $delta);
        $this->db->dbbind(':bid', (int)$r['booking_id'], PDO::PARAM_INT);
        $this->db->dbexecute();

        $this->logStatusChange(
            (int)$r['booking_id'], null, 'replacement_delta_refunded', null,
            'Refunded paid price-difference of ' . number_format($delta, 0) . ' after replacement fell through'
        );
        return true;
    }

    public function rejectReplacementByCustomer(int $replacementId, int $userId): array|false
    {
        $replacement = $this->getReplacementForCustomer($replacementId);
        if (
            !$replacement
            || (int)$replacement['user_id'] !== $userId
            || ($replacement['status'] ?? '') !== 'pending_customer'
        ) {
            return false;
        }

        $this->db->beginTransaction();
        try {
            // Remember the rejected service so the admin's re-pick won't re-offer it.
            $this->appendRejectedService($replacementId, (int)($replacement['new_service_id'] ?? 0));

            $paymentId = (int)($replacement['delta_payment_id'] ?? 0);
            if ($paymentId > 0) {
                $this->db->dbquery(
                    "UPDATE payments
                        SET status = 'failed', verified_at = NOW()
                      WHERE id = :pid AND type = 'replacement_delta' AND status = 'pending'"
                );
                $this->db->dbbind(':pid', $paymentId, PDO::PARAM_INT);
                $this->db->dbexecute();
            }

            $this->db->dbquery(
                "UPDATE booking_supplier_replacements
                    SET status = 'rejected_by_customer',
                        new_supplier_id = NULL, new_service_id = NULL,
                        new_price = NULL, price_delta = NULL,
                        requires_customer_approval = 0, delta_payment_id = NULL,
                        customer_approved_at = NULL, proposed_at = NULL
                  WHERE id = :id AND status = 'pending_customer'"
            );
            $this->db->dbbind(':id', $replacementId, PDO::PARAM_INT);
            $this->db->dbexecute();
            if ($this->db->rowcount() !== 1) {
                throw new RuntimeException('Replacement proposal already handled.');
            }
            $this->logStatusChange(
                (int)$replacement['booking_id'],
                null,
                'replacement_rejected_by_customer',
                $userId,
                'Customer rejected the pricier replacement; admin re-pick required'
            );
            $this->db->commit();
            return $replacement;
        } catch (Throwable $e) {
            $this->db->rollBack();
            error_log('Customer replacement rejection failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Customer opts out of an auto-assigned (cheaper/same) replacement within
     * the 48-hour window. Reverses performReplacementSwap atomically.
     */
    public function rejectAutoAssignedReplacement(int $replacementId, int $userId): array|false
    {
        $replacement = $this->getReplacementForCustomer($replacementId);
        if (
            !$replacement
            || (int)$replacement['user_id'] !== $userId
            || ($replacement['status'] ?? '') !== 'assigned'
            || empty($replacement['requires_customer_approval'])
            || empty($replacement['customer_opt_out_deadline'])
            || strtotime((string)$replacement['customer_opt_out_deadline']) < time()
        ) {
            return false;
        }

        $bookingId  = (int)$replacement['booking_id'];
        $newServiceId = (int)($replacement['new_service_id'] ?? 0);
        $oldSupplierRow = (int)($replacement['booking_supplier_id'] ?? 0);

        $this->db->beginTransaction();
        try {
            // 1. Mark the new booking_suppliers row as cancelled.
            $this->db->dbquery(
                "UPDATE booking_suppliers
                    SET status = 'cancelled'
                  WHERE booking_id = :bid
                    AND service_id = :sid
                    AND status IN ('pending', 'confirmed')
                    AND replaced_by_id IS NULL
                  ORDER BY id DESC LIMIT 1"
            );
            $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
            $this->db->dbbind(':sid', $newServiceId, PDO::PARAM_INT);
            $this->db->dbexecute();

            // 2. Restore the old booking_suppliers row to needs_replacement.
            if ($oldSupplierRow > 0) {
                $this->db->dbquery(
                    "UPDATE booking_suppliers
                        SET status = 'needs_replacement', replaced_by_id = NULL
                      WHERE id = :id AND status = 'replaced'"
                );
                $this->db->dbbind(':id', $oldSupplierRow, PDO::PARAM_INT);
                $this->db->dbexecute();
            }

            // 3. Release the new supplier's slot reservation.
            $this->db->dbquery(
                "SELECT bsr.id AS reservation_id, bsr.slot_id
                   FROM booking_slot_reservations bsr
                  WHERE bsr.booking_id = :bid
                    AND bsr.service_id = :sid
                    AND bsr.released_at IS NULL
                  ORDER BY bsr.id DESC LIMIT 1"
            );
            $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
            $this->db->dbbind(':sid', $newServiceId, PDO::PARAM_INT);
            $newSlot = $this->db->getsingledata();
            if ($newSlot) {
                $this->db->dbquery(
                    "UPDATE booking_slot_reservations
                        SET released_at = NOW()
                      WHERE id = :id AND released_at IS NULL"
                );
                $this->db->dbbind(':id', (int)$newSlot['reservation_id'], PDO::PARAM_INT);
                $this->db->dbexecute();
                if ($this->db->rowcount() === 1) {
                    $this->releaseServiceSlot((int)$newSlot['slot_id'], 'package');
                }
            }

            // 4. Re-activate the old supplier's slot reservation.
            $oldServiceId = (int)($replacement['old_service_id'] ?? 0);
            if ($oldServiceId > 0) {
                $this->db->dbquery(
                    "SELECT bsr.id AS reservation_id, bsr.slot_id
                       FROM booking_slot_reservations bsr
                      WHERE bsr.booking_id = :bid
                        AND bsr.service_id = :sid
                        AND bsr.released_at IS NOT NULL
                      ORDER BY bsr.id DESC LIMIT 1"
                );
                $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
                $this->db->dbbind(':sid', $oldServiceId, PDO::PARAM_INT);
                $oldSlot = $this->db->getsingledata();
                if ($oldSlot) {
                    $this->db->dbquery(
                        "UPDATE booking_slot_reservations
                            SET released_at = NULL
                          WHERE id = :id"
                    );
                    $this->db->dbbind(':id', (int)$oldSlot['reservation_id'], PDO::PARAM_INT);
                    $this->db->dbexecute();
                    $this->reserveServiceSlot((int)$oldSlot['slot_id'], 'package');
                }
            }

            // 5. Reset the replacement row.
            $this->appendRejectedService($replacementId, $newServiceId);
            $this->db->dbquery(
                "UPDATE booking_supplier_replacements
                    SET status = 'rejected_by_customer',
                        new_supplier_id = NULL, new_service_id = NULL,
                        new_price = NULL, price_delta = NULL,
                        requires_customer_approval = 0,
                        customer_opt_out_deadline = NULL,
                        assigned_at = NULL
                  WHERE id = :id AND status = 'assigned'"
            );
            $this->db->dbbind(':id', $replacementId, PDO::PARAM_INT);
            $this->db->dbexecute();
            if ($this->db->rowcount() !== 1) {
                throw new RuntimeException('Replacement already handled.');
            }

            $this->logStatusChange(
                $bookingId,
                null,
                'replacement_opted_out',
                $userId,
                'Customer opted out of auto-assigned replacement within 48h window'
            );

            $this->db->commit();
            return $replacement;
        } catch (Throwable $e) {
            $this->db->rollBack();
            error_log('Customer opt-out rejection failed: ' . $e->getMessage());
            return false;
        }
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
                    s.name AS new_service_name,
                    sup_old.shop_name AS old_shop_name,
                    svc_old.name AS old_service_name,
                    p.status AS delta_payment_status,
                    p.payment_slip_path AS delta_payment_slip
               FROM booking_supplier_replacements r
               INNER JOIN bookings b ON b.id = r.booking_id
               LEFT JOIN suppliers sup ON sup.supplier_id = r.new_supplier_id
               LEFT JOIN suppliers sup_old ON sup_old.supplier_id = r.old_supplier_id
               LEFT JOIN services s ON s.id = r.new_service_id
               LEFT JOIN services svc_old ON svc_old.id = r.old_service_id
               LEFT JOIN payments p ON p.id = r.delta_payment_id
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
                   AND status IN (
                       'pending_admin','pending_customer','assigned',
                       'declined_again','rejected_by_customer'
                   )) AS open_reqs"
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
            "SELECT r.*, sup.shop_name AS new_shop_name,
                    p.status AS delta_payment_status,
                    p.payment_slip_path AS delta_payment_slip
               FROM booking_supplier_replacements r
               LEFT JOIN suppliers sup ON sup.supplier_id = r.new_supplier_id
               LEFT JOIN payments p ON p.id = r.delta_payment_id
              WHERE r.booking_id = :bid AND r.status = 'pending_customer'
              ORDER BY r.id DESC LIMIT 1"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        return $this->db->getsingledata();
    }

    public function getOpenReplacementsForBooking(int $bookingId): array
    {
        $this->db->dbquery(
            "SELECT r.*,
                    sup_old.shop_name AS old_shop_name,
                    svc_old.name AS old_service_name,
                    sup_new.shop_name AS new_shop_name,
                    svc_new.name AS new_service_name,
                    p.status AS delta_payment_status,
                    p.payment_slip_path AS delta_payment_slip,
                    p.paid_amount AS delta_paid_amount,
                    p.bank_name AS delta_bank_name,
                    p.transaction_ref AS delta_transaction_ref
             FROM booking_supplier_replacements r
             LEFT JOIN suppliers sup_old ON sup_old.supplier_id = r.old_supplier_id
             LEFT JOIN services svc_old ON svc_old.id = r.old_service_id
             LEFT JOIN suppliers sup_new ON sup_new.supplier_id = r.new_supplier_id
             LEFT JOIN services svc_new ON svc_new.id = r.new_service_id
             LEFT JOIN payments p ON p.id = r.delta_payment_id
             WHERE r.booking_id = :bid
               AND r.status IN ('pending_admin','pending_customer','assigned','declined_again','rejected_by_customer')
             ORDER BY r.id DESC"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        return $this->db->getmultidata() ?: [];
    }

    /**
     * Get all replacement history for a booking (for customer-facing timeline).
     */
    public function getReplacementHistory(int $bookingId): array
    {
        $this->db->dbquery(
            "SELECT r.id, r.status, r.new_service_id, r.new_price, r.price_delta,
                    r.old_supplier_id, r.new_supplier_id,
                    svc_old.name AS old_service_name,
                    sp_old.shop_name AS old_supplier_name,
                    r.old_price,
                    r.decline_reason,
                    r.proposed_at, r.resolved_at, r.created_at,
                    r.customer_approved_at, r.requires_customer_approval,
                    r.customer_opt_out_deadline,
                    sp_old.shop_name AS old_shop_name,
                    sp_new.shop_name AS new_shop_name,
                    svc_new.name AS new_service_name,
                    p.status AS delta_payment_status,
                    p.payment_slip_path AS delta_payment_slip
             FROM booking_supplier_replacements r
             LEFT JOIN suppliers sp_old ON sp_old.supplier_id = r.old_supplier_id
             LEFT JOIN suppliers sp_new ON sp_new.supplier_id = r.new_supplier_id
             LEFT JOIN services svc_new ON svc_new.id = r.new_service_id
             LEFT JOIN services svc_old ON svc_old.id = r.old_service_id
             LEFT JOIN payments p ON p.id = r.delta_payment_id
             WHERE r.booking_id = :bid
             ORDER BY r.id ASC"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        return $this->db->getmultidata();
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
     * A replacement can be answered once any customer-paid price difference
     * attached to that replacement has been verified. Replacements without a
     * price difference are ready immediately after assignment.
     */
    public function isReplacementDeltaVerified(array $replacement): bool
    {
        $paymentId = (int)($replacement['delta_payment_id'] ?? 0);
        if ($paymentId <= 0) {
            return true;
        }

        $this->db->dbquery(
            "SELECT status
               FROM payments
              WHERE id = :id
                AND booking_id = :bid
                AND type = 'replacement_delta'
              LIMIT 1"
        );
        $this->db->dbbind(':id', $paymentId, PDO::PARAM_INT);
        $this->db->dbbind(':bid', (int)($replacement['booking_id'] ?? 0), PDO::PARAM_INT);
        $payment = $this->db->getsingledata();

        return $payment && ($payment['status'] ?? '') === 'success';
    }

    /**
     * Update booking item status by supplier.
     */
    public function updateBookingItemsStatusBySupplier(int $bookingId, int $supplierId, string $status, int $serviceId = 0): bool
    {
        $hasSupplierPackages = $this->tableExists('supplier_packages');
        $hasSupplierPackageItems = $this->tableExists('supplier_package_items');
        $supplierPackageJoin = $hasSupplierPackages
            ? "LEFT JOIN supplier_packages sp ON bi.item_id = sp.id AND bi.item_type = 'supplier_package'"
            : '';
        $supplierPackageCondition = $hasSupplierPackages
            ? 'OR sp.supplier_id = :sid2'
            : '';
        $supplierPackageItemsCondition = $hasSupplierPackageItems
            ? "OR EXISTS (
                        SELECT 1
                        FROM supplier_package_items spi
                        INNER JOIN services spi_service ON spi.service_id = spi_service.id
                        WHERE bi.item_type = 'supplier_package'
                          AND spi.package_id = bi.item_id
                          AND spi_service.supplier_id = :sid4
                    )"
            : '';

        $serviceFilter = $serviceId > 0 ? 'AND bi.item_id = :service_id AND bi.item_type = \'service\'' : '';

        $this->db->dbquery(
            "UPDATE booking_items bi
             LEFT JOIN services s ON bi.item_id = s.id AND bi.item_type = 'service'
             {$supplierPackageJoin}
             SET bi.status = :status
             WHERE bi.booking_id = :bid
               AND (
                    s.supplier_id = :sid
                    {$supplierPackageCondition}
                    OR EXISTS (
                        SELECT 1
                        FROM package_items pi
                        WHERE bi.item_type = 'package'
                          AND pi.package_id = bi.item_id
                          AND pi.default_supplier_id = :sid3
                    )
                    {$supplierPackageItemsCondition}
               )
               {$serviceFilter}"
        );
        $this->db->dbbind(':status', $status);
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        $this->db->dbbind(':sid', $supplierId, PDO::PARAM_INT);
        if ($hasSupplierPackages) {
            $this->db->dbbind(':sid2', $supplierId, PDO::PARAM_INT);
        }
        $this->db->dbbind(':sid3', $supplierId, PDO::PARAM_INT);
        if ($hasSupplierPackageItems) {
            $this->db->dbbind(':sid4', $supplierId, PDO::PARAM_INT);
        }
        if ($serviceId > 0) {
            $this->db->dbbind(':service_id', $serviceId, PDO::PARAM_INT);
        }

        return $this->db->dbexecute();
    }

    /* ─── Vouchers ──────────────────────────────────────────────── */

    /**
     * Generate vouchers for a booking.
     */
    public function generateVouchers(int $bookingId): bool
    {
        $prefixes = [
            'service' => 'VCH-SRV',
            'package' => 'VCH-PKG',
            'supplier_package' => 'VCH-SPK',
        ];
        $hasSupplierPackages = $this->tableExists('supplier_packages');
        $supplierPackageName = $hasSupplierPackages ? 'sp.name' : 'NULL';
        $supplierPackageShop = $hasSupplierPackages ? 'sp_sup.shop_name' : 'NULL';
        $supplierPackageSupplierId = $hasSupplierPackages ? 'sp_sup.supplier_id' : 'NULL';
        $supplierPackageJoin = $hasSupplierPackages
            ? "LEFT JOIN supplier_packages sp ON bi.item_id = sp.id AND bi.item_type = 'supplier_package'
               LEFT JOIN suppliers sp_sup ON sp.supplier_id = sp_sup.supplier_id"
            : '';
        $this->db->dbquery(
            "DELETE bv
               FROM booking_vouchers bv
               INNER JOIN booking_items bi
                       ON bi.booking_id = bv.booking_id
                      AND bi.item_type = 'package'
               LEFT JOIN packages p ON p.package_id = bi.item_id
              WHERE bv.booking_id = :bid
                AND bv.status = 'active'
                AND bv.service_id IS NULL
                AND bv.service_name = COALESCE(bi.item_name, p.name)"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        $this->db->dbexecute();

        $this->db->dbquery(
            "SELECT voucher_lines.*
               FROM (
                    SELECT
                        bi.id AS id,
                        'package' AS item_type,
                        bs.service_id AS item_id,
                        bi.booking_date,
                        COALESCE(bs.item_price, pi.default_price, pi.customize_price, 0) AS price,
                        COALESCE(s.name, 'Package Service') AS service_name,
                        COALESCE(sup.shop_name, 'Golden Promise') AS supplier_name,
                        COALESCE(cat.name, pi_cat.name, 'Service') AS category_name,
                        bs.supplier_id AS supplier_id,
                        ed.location,
                        COALESCE(slot_times.start_time, bi.start_time, ed.start_time) AS start_time,
                        COALESCE(slot_times.end_time, bi.end_time, ed.end_time) AS end_time
                    FROM booking_items bi
                    INNER JOIN booking_suppliers bs
                            ON bs.booking_id = bi.booking_id
                           AND bs.package_item_id IS NOT NULL
                           AND bs.status NOT IN ('rejected', 'cancelled', 'replaced')
                    INNER JOIN package_items pi
                            ON pi.id = bs.package_item_id
                           AND pi.package_id = bi.item_id
                    LEFT JOIN services s ON s.id = bs.service_id
                    LEFT JOIN suppliers sup ON sup.supplier_id = bs.supplier_id
                    LEFT JOIN categories cat ON cat.id = s.category_id
                    LEFT JOIN categories pi_cat ON pi_cat.id = pi.category_id
                    LEFT JOIN (
                        SELECT bsr.booking_id, bsr.package_item_id,
                               MIN(st.start_time) AS start_time,
                               MAX(st.end_time) AS end_time
                          FROM booking_slot_reservations bsr
                          INNER JOIN service_time_slots st ON st.id = bsr.slot_id
                         WHERE bsr.released_at IS NULL
                         GROUP BY bsr.booking_id, bsr.package_item_id
                    ) slot_times
                           ON slot_times.booking_id = bi.booking_id
                          AND slot_times.package_item_id = bs.package_item_id
                    LEFT JOIN event_details ed ON ed.booking_id = bi.booking_id AND ed.id = (
                        SELECT MIN(id) FROM event_details WHERE booking_id = bi.booking_id
                    )
                    WHERE bi.booking_id = :bid_package
                      AND bi.item_type = 'package'

                    UNION ALL

                    SELECT
                        bi.id AS id,
                        bi.item_type,
                        bi.item_id,
                        bi.booking_date,
                        bi.price,
                        COALESCE(bi.item_name, s.name, {$supplierPackageName}) AS service_name,
                        COALESCE(bi.supplier_name, sup.shop_name, {$supplierPackageShop}, 'Golden Promise') AS supplier_name,
                        COALESCE(bi.category_name, cat.name, 'Service') AS category_name,
                        COALESCE(sup.supplier_id, {$supplierPackageSupplierId}) AS supplier_id,
                        ed.location,
                        bi.start_time,
                        bi.end_time
                    FROM booking_items bi
                    LEFT JOIN services s ON bi.item_id = s.id AND bi.item_type = 'service'
                    {$supplierPackageJoin}
                    LEFT JOIN suppliers sup ON s.supplier_id = sup.supplier_id
                    LEFT JOIN categories cat ON s.category_id = cat.id
                    LEFT JOIN event_details ed ON ed.booking_id = bi.booking_id AND ed.id = (
                        SELECT MIN(id) FROM event_details WHERE booking_id = bi.booking_id
                    )
                    WHERE bi.booking_id = :bid_other
                      AND bi.item_type <> 'package'
               ) voucher_lines
              ORDER BY voucher_lines.item_type, voucher_lines.service_name, voucher_lines.id"
        );
        $this->db->dbbind(':bid_package', $bookingId, PDO::PARAM_INT);
        $this->db->dbbind(':bid_other', $bookingId, PDO::PARAM_INT);
        $items = $this->db->getmultidata();
        if (empty($items)) {
            return true;
        }

        $this->db->dbquery(
            "SELECT service_id, service_name, supplier_id, event_date, start_time, end_time, price
               FROM booking_vouchers
              WHERE booking_id = :bid"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        $existingRows = $this->db->getmultidata() ?: [];
        $existing = [];
        $existingStable = [];
        foreach ($existingRows as $row) {
            $key = implode('|', [
                (string)($row['service_name'] ?? ''),
                (string)($row['supplier_id'] ?? ''),
                (string)($row['event_date'] ?? ''),
                (string)($row['start_time'] ?? ''),
                (string)($row['end_time'] ?? ''),
                number_format((float)($row['price'] ?? 0), 2, '.', ''),
            ]);
            $existing[$key] = ($existing[$key] ?? 0) + 1;

            $stableKey = implode('|', [
                (string)($row['service_id'] ?? ''),
                (string)($row['supplier_id'] ?? ''),
                (string)($row['service_name'] ?? ''),
            ]);
            $existingStable[$stableKey] = true;
        }

        $idx = 0;
        foreach ($items as $item) {
            $eventDate = $item['booking_date'] ? date('Y-m-d', strtotime($item['booking_date'])) : null;
            $serviceId = in_array(($item['item_type'] ?? ''), ['service', 'package'], true)
                ? (int)($item['item_id'] ?? 0)
                : null;
            $supplierId = !empty($item['supplier_id']) ? (int)$item['supplier_id'] : null;
            $itemKey = implode('|', [
                (string)($item['service_name'] ?? 'Service'),
                (string)($supplierId ?? ''),
                (string)($eventDate ?? ''),
                (string)($item['start_time'] ?? ''),
                (string)($item['end_time'] ?? ''),
                number_format((float)($item['price'] ?? 0), 2, '.', ''),
            ]);
            if (($existing[$itemKey] ?? 0) > 0) {
                $existing[$itemKey]--;
                continue;
            }
            $stableItemKey = implode('|', [
                (string)($serviceId ?? ''),
                (string)($supplierId ?? ''),
                (string)($item['service_name'] ?? 'Service'),
            ]);
            if (isset($existingStable[$stableItemKey])) {
                continue;
            }

            $prefix = $prefixes[$item['item_type']] ?? 'VCH';
            $voucherNumber = null;
            for ($attempt = 1; $attempt <= 20; $attempt++) {
                $seed = implode('-', [
                    $item['id'] ?? 0,
                    $bookingId,
                    $item['item_type'] ?? '',
                    $serviceId ?? 0,
                    $supplierId ?? 0,
                    ++$idx,
                    $attempt,
                ]);
                $candidate = $prefix . '-' . strtoupper(substr(md5($seed), 0, 8));
                $this->db->dbquery(
                    "SELECT 1 FROM booking_vouchers WHERE voucher_number = :vnum LIMIT 1"
                );
                $this->db->dbbind(':vnum', $candidate);
                if (!$this->db->getsingledata()) {
                    $voucherNumber = $candidate;
                    break;
                }
            }
            if ($voucherNumber === null) {
                return false;
            }

            $this->db->dbquery(
                "INSERT INTO booking_vouchers
                    (booking_id, voucher_number, service_id, supplier_id, service_name, category_name,
                     event_date, start_time, end_time, location, price, status)
                 VALUES (:bid, :vnum, :sid, :supid, :sname, :cname,
                         :edate, :stime, :etime, :loc, :price, 'active')"
            );
            $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
            $this->db->dbbind(':vnum', $voucherNumber);
            $this->db->dbbind(':sid', $serviceId, $serviceId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $this->db->dbbind(':supid', $supplierId, $supplierId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            $this->db->dbbind(':sname', $item['service_name'] ?? 'Service');
            $this->db->dbbind(':cname', $item['category_name'] ?? 'Service');
            $this->db->dbbind(':edate', $eventDate);
            $this->db->dbbind(':stime', $item['start_time'] ?? null);
            $this->db->dbbind(':etime', $item['end_time'] ?? null);
            $this->db->dbbind(':loc', $item['location'] ?? null);
            $this->db->dbbind(':price', $item['price'] ?? 0);

            if (!$this->db->dbexecute()) {
                return false;
            }
            $existingStable[$stableItemKey] = true;
        }

        return true;
    }

    /**
     * Get vouchers for a booking.
     */
    public function getBookingVouchers(int $bookingId): array
    {
        $this->db->dbquery(
            "SELECT bv.*,
                    COALESCE(sup.shop_name, bi.supplier_name, 'Golden Promise') AS supplier_name,
                    b.user_id
               FROM booking_vouchers bv
               INNER JOIN bookings b ON b.id = bv.booking_id
               LEFT JOIN suppliers sup ON sup.supplier_id = bv.supplier_id
               LEFT JOIN (
                    SELECT booking_id, item_name, MAX(supplier_name) AS supplier_name
                      FROM booking_items
                     GROUP BY booking_id, item_name
               ) bi ON bi.booking_id = bv.booking_id
                   AND bi.item_name = bv.service_name
              WHERE bv.booking_id = :bid
              ORDER BY bv.id ASC"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        return $this->db->getmultidata();
    }

    /**
     * Get vouchers for one supplier on a booking.
     */
    public function getSupplierBookingVouchers(int $bookingId, int $supplierId): array
    {
        if ($bookingId <= 0 || $supplierId <= 0) {
            return [];
        }

        $this->db->dbquery(
            "SELECT bv.*,
                    COALESCE(sup.shop_name, 'Your shop') AS supplier_name,
                    b.user_id
               FROM booking_vouchers bv
               INNER JOIN bookings b ON b.id = bv.booking_id
               LEFT JOIN suppliers sup ON sup.supplier_id = bv.supplier_id
              WHERE bv.booking_id = :bid
                AND bv.supplier_id = :sid
              ORDER BY bv.event_date ASC, bv.start_time ASC, bv.id ASC"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        $this->db->dbbind(':sid', $supplierId, PDO::PARAM_INT);
        return $this->db->getmultidata();
    }

    /**
     * Get all vouchers for a user.
     */
    public function getCustomerVouchers(int $userId, ?string $statusFilter = null, int $limit = 12, int $offset = 0): array
    {
        $sql = "SELECT bv.*,
                       b.user_id,
                       COALESCE(sup.shop_name, bi.supplier_name, 'Golden Promise') AS supplier_name,
                       COALESCE(s.thumbnail_url, bi.thumbnail_url) AS thumbnail_url
                FROM booking_vouchers bv
                INNER JOIN bookings b ON bv.booking_id = b.id
                LEFT JOIN services s ON s.id = bv.service_id
                LEFT JOIN suppliers sup ON sup.supplier_id = bv.supplier_id
                LEFT JOIN (
                    SELECT booking_id, item_name,
                           MAX(supplier_name) AS supplier_name,
                           MAX(thumbnail_url) AS thumbnail_url
                      FROM booking_items
                     GROUP BY booking_id, item_name
                ) bi ON bi.booking_id = bv.booking_id
                    AND bi.item_name = bv.service_name
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

    public function getCustomerVoucherStatusCounts(int $userId): array
    {
        $this->db->dbquery(
            "SELECT bv.status, COUNT(*) AS total
               FROM booking_vouchers bv
               INNER JOIN bookings b ON bv.booking_id = b.id
              WHERE b.user_id = :uid
              GROUP BY bv.status"
        );
        $this->db->dbbind(':uid', $userId, PDO::PARAM_INT);
        $rows = $this->db->getmultidata() ?: [];
        $counts = ['all' => 0, 'active' => 0, 'used' => 0, 'expired' => 0];
        foreach ($rows as $row) {
            $status = (string)($row['status'] ?? 'active');
            $total = (int)($row['total'] ?? 0);
            if (isset($counts[$status])) {
                $counts[$status] = $total;
            }
            $counts['all'] += $total;
        }
        return $counts;
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

    public function expirePastVouchers(?int $userId = null): int
    {
        $sql = "UPDATE booking_vouchers bv
                INNER JOIN bookings b ON b.id = bv.booking_id
                   SET bv.status = 'expired'
                 WHERE bv.status = 'active'
                   AND bv.event_date IS NOT NULL
                   AND bv.event_date < CURDATE()";
        if ($userId !== null) {
            $sql .= " AND b.user_id = :uid";
        }

        $this->db->dbquery($sql);
        if ($userId !== null) {
            $this->db->dbbind(':uid', $userId, PDO::PARAM_INT);
        }
        $this->db->dbexecute();
        return $this->db->rowcount();
    }

    public function markVoucherUsedByCode(string $voucherNumber, int $supplierId): bool
    {
        $code = strtoupper(trim($voucherNumber));
        if ($code === '' || $supplierId <= 0) {
            return false;
        }

        $this->db->dbquery(
            "UPDATE booking_vouchers
                SET status = 'used'
              WHERE voucher_number = :code
                AND supplier_id = :sid
                AND status = 'active'
              LIMIT 1"
        );
        $this->db->dbbind(':code', $code);
        $this->db->dbbind(':sid', $supplierId, PDO::PARAM_INT);
        $this->db->dbexecute();
        return $this->db->rowcount() === 1;
    }

    public function getVoucherByNumber(string $voucherNumber): ?array
    {
        $code = strtoupper(trim($voucherNumber));
        if ($code === '') {
            return null;
        }

        $this->db->dbquery(
            "SELECT bv.*,
                    b.user_id,
                    COALESCE(sup.shop_name, 'Golden Promise') AS supplier_name
               FROM booking_vouchers bv
               INNER JOIN bookings b ON b.id = bv.booking_id
               LEFT JOIN suppliers sup ON sup.supplier_id = bv.supplier_id
              WHERE bv.voucher_number = :code
              LIMIT 1"
        );
        $this->db->dbbind(':code', $code);
        $voucher = $this->db->getsingledata();
        return $voucher ?: null;
    }

    public function syncCustomerVouchers(int $userId): void
    {
        $this->db->dbquery(
            "SELECT id
               FROM bookings
              WHERE user_id = :uid
                AND status IN ('confirmed', 'pending_final_payment', 'finalized', 'completed')"
        );
        $this->db->dbbind(':uid', $userId, PDO::PARAM_INT);
        $bookings = $this->db->getmultidata() ?: [];
        foreach ($bookings as $booking) {
            $bookingId = (int)($booking['id'] ?? 0);
            if ($bookingId > 0) {
                $this->generateVouchers($bookingId);
            }
        }
        $this->expirePastVouchers($userId);
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
     * For customize (non-package) bookings, also sets the supplier row to
     * 'cancellation_pending' so the supplier can accept/decline.
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

        if (!$this->db->dbexecute()) {
            return false;
        }

        // For customize bookings, set the supplier row to cancellation_pending
        // so the supplier can review and accept/decline the cancellation.
        if (!$this->isPackageBooking($bookingId)) {
            $this->db->dbquery(
                "UPDATE booking_suppliers
                 SET status = 'cancellation_pending'
                 WHERE booking_id = :bid AND status IN ('confirmed', 'in_progress', 'pending')"
            );
            $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
            $this->db->dbexecute();
        }

        return true;
    }

    /**
     * Supplier requests cancellation of a confirmed booking.
     * Sets booking to cancellation_requested and marks the supplier's rows.
     * Admin will review and process the refund.
     */
    public function supplierRequestCancellation(int $bookingId, int $supplierId, string $reason, int $bookingSupplierId): bool
    {
        // Verify booking is in a cancellable state
        $this->db->dbquery("SELECT status FROM bookings WHERE id = :bid LIMIT 1");
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        $booking = $this->db->getsingledata();
        if (!$booking || !in_array($booking['status'] ?? '', ['confirmed', 'paid', 'finalized'], true)) {
            return false;
        }

        // Verify the targeted service row belongs to this supplier and is active.
        $this->db->dbquery(
            "SELECT bs.id, bs.status, sup.shop_name, svc.name AS service_name, cat.name AS category_name
             FROM booking_suppliers bs
             LEFT JOIN suppliers sup ON sup.supplier_id = bs.supplier_id
             LEFT JOIN services svc ON svc.id = bs.service_id
             LEFT JOIN categories cat ON cat.id = bs.category_id
             WHERE bs.id = :bsid
               AND bs.booking_id = :bid
               AND bs.supplier_id = :sid
               AND bs.status IN ('accepted', 'confirmed', 'in_progress')
             LIMIT 1"
        );
        $this->db->dbbind(':bsid', $bookingSupplierId, PDO::PARAM_INT);
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        $this->db->dbbind(':sid', $supplierId, PDO::PARAM_INT);
        $supplierRow = $this->db->getsingledata();
        if (!$supplierRow) {
            return false;
        }

        // Resolve supplier's user_id for logging
        $this->db->dbquery("SELECT user_id FROM suppliers WHERE supplier_id = :sid LIMIT 1");
        $this->db->dbbind(':sid', $supplierId, PDO::PARAM_INT);
        $supplierUser = $this->db->getsingledata();
        $changedBy = (int)($supplierUser['user_id'] ?? 0) ?: null;

        // Update booking status
        $this->db->dbquery(
            "UPDATE bookings SET status = 'cancellation_requested' WHERE id = :bid LIMIT 1"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        if (!$this->db->dbexecute()) {
            return false;
        }

        // Mark supplier's rows as supplier_cancellation_requested
        $this->db->dbquery(
            "UPDATE booking_suppliers
             SET status = 'supplier_cancellation_requested',
                 decline_reason = :reason,
                 declined_at = NOW()
             WHERE id = :bsid
               AND booking_id = :bid
               AND supplier_id = :sid
               AND status IN ('accepted', 'confirmed', 'in_progress')"
        );
        $this->db->dbbind(':bsid', $bookingSupplierId, PDO::PARAM_INT);
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        $this->db->dbbind(':sid', $supplierId, PDO::PARAM_INT);
        $this->db->dbbind(':reason', $reason, PDO::PARAM_STR);
        $this->db->dbexecute();

        $supplierName = trim((string)($supplierRow['shop_name'] ?? ''));
        $serviceName = trim((string)($supplierRow['service_name'] ?? $supplierRow['category_name'] ?? ''));
        $supplierContext = $supplierName !== '' ? $supplierName : 'Supplier';
        if ($serviceName !== '') {
            $supplierContext .= ' for ' . $serviceName;
        }

        // Log the status change
        $this->logStatusChange(
            $bookingId,
            $booking['status'],
            'cancellation_requested',
            $changedBy,
            $supplierContext . ' requested cancellation. Reason: ' . $reason
        );

        return true;
    }

    /**
     * Supplier responds to a cancellation request (customize bookings only).
     * @return string 'approved' or 'declined', or '' on failure
     */
    public function supplierRespondToCancellation(int $bookingId, int $supplierId, string $action, string $reason = ''): string
    {
        if (!in_array($action, ['approve', 'decline'], true)) {
            return '';
        }

        // Verify booking is in cancellation_requested status
        $this->db->dbquery("SELECT status FROM bookings WHERE id = :bid LIMIT 1");
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        $booking = $this->db->getsingledata();
        if (!$booking || ($booking['status'] ?? '') !== 'cancellation_requested') {
            return '';
        }

        // Find the supplier's row in cancellation_pending
        $this->db->dbquery(
            "SELECT id, status FROM booking_suppliers
             WHERE booking_id = :bid AND supplier_id = :sid AND status = 'cancellation_pending'
             LIMIT 1"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        $this->db->dbbind(':sid', $supplierId, PDO::PARAM_INT);
        $supplierRow = $this->db->getsingledata();

        if (!$supplierRow) {
            return '';
        }

        $supplierRowId = (int)$supplierRow['id'];

        // Resolve supplier's user_id for logging (changed_by references users.user_id)
        $this->db->dbquery("SELECT user_id FROM suppliers WHERE supplier_id = :sid LIMIT 1");
        $this->db->dbbind(':sid', $supplierId, PDO::PARAM_INT);
        $supplierUser = $this->db->getsingledata();
        $changedBy = (int)($supplierUser['user_id'] ?? 0) ?: null;

        if ($action === 'approve') {
            // Supplier approves: mark their row as cancellation_approved
            $this->db->dbquery(
                "UPDATE booking_suppliers SET status = 'cancellation_approved' WHERE id = :id"
            );
            $this->db->dbbind(':id', $supplierRowId, PDO::PARAM_INT);
            $this->db->dbexecute();

            $this->logStatusChange(
                $bookingId,
                'cancellation_requested',
                'supplier_cancellation_approved',
                $changedBy,
                'Supplier approved cancellation request.' . ($reason !== '' ? ' Note: ' . $reason : '')
            );

            return 'approved';
        }

        // Decline: revert booking status and supplier status
        // Get the old_status from the cancellation_requested log entry
        $this->db->dbquery(
            "SELECT old_status FROM booking_status_logs
             WHERE booking_id = :bid AND new_status = 'cancellation_requested'
             ORDER BY id DESC LIMIT 1"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        $log = $this->db->getsingledata();
        $previousStatus = ($log['old_status'] ?? '') ?: 'confirmed';

        // Revert booking status
        $this->db->dbquery(
            "UPDATE bookings SET status = :status WHERE id = :bid LIMIT 1"
        );
        $this->db->dbbind(':status', $previousStatus);
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        $this->db->dbexecute();

        // Revert supplier row to confirmed
        $this->db->dbquery(
            "UPDATE booking_suppliers SET status = 'confirmed' WHERE id = :id"
        );
        $this->db->dbbind(':id', $supplierRowId, PDO::PARAM_INT);
        $this->db->dbexecute();

        $this->logStatusChange(
            $bookingId,
            'cancellation_requested',
            $previousStatus,
            $changedBy,
            'Supplier declined cancellation. Reason: ' . $reason
        );

        return 'declined';
    }

    /**
     * Get the cancellation reason from booking_status_logs.
     */
    public function getCancellationReason(int $bookingId): string
    {
        $this->db->dbquery(
            "SELECT note FROM booking_status_logs
             WHERE booking_id = :bid AND new_status = 'cancellation_requested'
             ORDER BY id DESC LIMIT 1"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        $log = $this->db->getsingledata();
        $note = $log['note'] ?? '';
        // Strip the "Cancellation requested: " prefix
        return preg_replace('/^Cancellation requested:\s*/i', '', $note);
    }

    /**
     * Admin: cancel a booking.
     */
    public function adminCancelBooking(int $bookingId, string $reason, int $adminId): float|false
    {
        $this->db->beginTransaction();
        $refundAmount = 0.0;
        try {
        // Fetch current status and supplier cancellation marker BEFORE
        // cancelling so refund policy can detect who initiated the request.
        $this->db->dbquery(
            "SELECT b.status,
                    EXISTS (
                        SELECT 1
                        FROM booking_suppliers bs
                        WHERE bs.booking_id = b.id
                          AND bs.status = 'supplier_cancellation_requested'
                    ) AS supplier_requested_cancellation
             FROM bookings b
             WHERE b.id = :bid
             LIMIT 1"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        $currentBooking = $this->db->getsingledata() ?: [];
        $currentStatus = (string)($currentBooking['status'] ?? '');
        $supplierRequestedCancellation = !empty($currentBooking['supplier_requested_cancellation']);

        $this->db->dbquery(
            "UPDATE bookings SET status = 'cancelled', approved_by = :admin_id, approved_at = NOW()
             WHERE id = :bid AND status NOT IN ('cancelled','completed')"
        );
        $this->db->dbbind(':admin_id', $adminId, PDO::PARAM_INT);
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);

        if (!$this->db->dbexecute() || $this->db->rowcount() !== 1) {
            throw new RuntimeException('Booking cannot be cancelled from its current state.');
        }

        // Determine who caused the cancellation:
        // - supplier row status supplier_cancellation_requested = supplier initiated
        // - booking status cancellation_requested = customer initiated
        // - anything else = admin initiated directly
        if ($supplierRequestedCancellation) {
            $cancelledBy = 'supplier';
        } elseif ($currentStatus === 'cancellation_requested') {
            $cancelledBy = 'customer';
        } else {
            $cancelledBy = 'admin';
        }

        $refundCalc = $this->calculateRefund($bookingId, $cancelledBy);

        // Fallback: sum all successful payments if calculateRefund fails
        if ($refundCalc === false) {
            $this->db->dbquery(
                "SELECT COALESCE(SUM(COALESCE(paid_amount, amount)), 0) AS total
                 FROM payments
                 WHERE booking_id = :bid AND status = 'success'"
            );
            $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
            $refundAmount = (float) ($this->db->getsingledata()['total'] ?? 0);
            $policyReason = 'Manual refund by admin (policy calculation unavailable)';
        } else {
            $refundAmount = (float)$refundCalc[0];
            $policyReason = (string)$refundCalc[1];
        }

        // Queue refund if amount > 0
        if ($refundAmount > 0) {
            $this->db->dbquery(
                "INSERT INTO refunds (booking_id, amount, reason, policy_reason, status, requested_by, requested_at)
                 VALUES (:bid, :amount, :reason, :policy, 'pending', :admin_id, NOW())"
            );
            $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
            $this->db->dbbind(':amount', $refundAmount, PDO::PARAM_STR);
            $this->db->dbbind(':reason', $reason, PDO::PARAM_STR);
            $this->db->dbbind(':policy', $policyReason, PDO::PARAM_STR);
            $this->db->dbbind(':admin_id', $adminId, PDO::PARAM_INT);
            $this->db->dbexecute();

            $this->logStatusChange(
                $bookingId,
                'cancelled',
                'cancelled',
                $adminId,
                'Refund of ' . number_format($refundAmount, 0) . ' MMK queued (' . $policyReason . ')'
            );
        }

        $this->releaseBookingSlots($bookingId);
        $this->releaseAttireItems($bookingId);

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

        $this->db->commit();
        return $refundAmount;
        } catch (Throwable $e) {
            $this->db->rollBack();
            error_log('Booking cancellation failed: ' . $e->getMessage());
            return false;
        }
    }

    /* ─── Refund lifecycle ────────────────────────────────────── */

    /**
     * Get all refunds in the admin queue (pending or processing).
     */
    public function getRefundQueue(?int $limit = null, int $offset = 0): array
    {
        $limitSql = '';
        if ($limit !== null && $limit > 0) {
            $limitSql = ' LIMIT :limit OFFSET :offset';
        }

        $this->db->dbquery(
            "SELECT r.*,
                    b.status AS booking_status,
                    b.user_id,
                    u.name AS customer_name,
                    u.email AS customer_email
             FROM refunds r
             JOIN bookings b ON b.id = r.booking_id
             JOIN users u ON u.user_id = b.user_id
             WHERE r.status IN ('pending','processing')
             ORDER BY r.requested_at DESC" . $limitSql
        );
        if ($limitSql !== '') {
            $this->db->dbbind(':limit', $limit, PDO::PARAM_INT);
            $this->db->dbbind(':offset', max(0, $offset), PDO::PARAM_INT);
        }
        return $this->db->getmultidata() ?: [];
    }

    public function getRefundQueueCount(): int
    {
        $this->db->dbquery(
            "SELECT COUNT(*) AS total
             FROM refunds
             WHERE status IN ('pending','processing')"
        );
        $row = $this->db->getsingledata() ?: [];
        return (int)($row['total'] ?? 0);
    }

    /**
     * Get a single refund by ID.
     */
    public function getRefundById(int $refundId): array|false
    {
        $this->db->dbquery(
            "SELECT r.*,
                    b.status AS booking_status,
                    b.user_id,
                    u.name AS customer_name,
                    u.email AS customer_email
             FROM refunds r
             JOIN bookings b ON b.id = r.booking_id
             JOIN users u ON u.user_id = b.user_id
             WHERE r.id = :id LIMIT 1"
        );
        $this->db->dbbind(':id', $refundId, PDO::PARAM_INT);
        return $this->db->getsingledata();
    }

    /**
     * Get refund(s) for a specific booking (most recent first).
     */
    public function getBookingRefund(int $bookingId): array|false
    {
        $this->db->dbquery(
            "SELECT * FROM refunds WHERE booking_id = :bid ORDER BY id DESC LIMIT 1"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        return $this->db->getsingledata();
    }

    /**
     * Admin uploads proof of refund transfer (sets status to processing).
     */
    public function processRefund(
        int $refundId,
        int $adminId,
        string $transactionRef,
        string $bankName,
        string $slipPath,
        string $note = ''
    ): bool {
        $this->db->dbquery(
            "UPDATE refunds
             SET status = 'processing',
                 refund_transaction_ref = :txn,
                 refund_bank_name = :bank,
                 refund_slip_path = :slip,
                 processed_by = :admin_id,
                 processed_at = NOW(),
                 note = :note
             WHERE id = :id AND status IN ('pending','processing')"
        );
        $this->db->dbbind(':txn', $transactionRef, PDO::PARAM_STR);
        $this->db->dbbind(':bank', $bankName, PDO::PARAM_STR);
        $this->db->dbbind(':slip', $slipPath, PDO::PARAM_STR);
        $this->db->dbbind(':admin_id', $adminId, PDO::PARAM_INT);
        $this->db->dbbind(':note', $note, PDO::PARAM_STR);
        $this->db->dbbind(':id', $refundId, PDO::PARAM_INT);
        $ok = $this->db->dbexecute() && $this->db->rowcount() === 1;

        if ($ok) {
            $refund = $this->getRefundById($refundId);
            if ($refund) {
                $this->logStatusChange(
                    (int)$refund['booking_id'],
                    'cancelled',
                    'cancelled',
                    $adminId,
                    'Refund processing: proof uploaded via ' . $bankName .
                    ($transactionRef ? ' (ref: ' . $transactionRef . ')' : '')
                );
            }
        }

        return $ok;
    }

    /**
     * Admin marks a refund as completed (money sent to customer).
     */
    public function completeRefund(int $refundId, int $adminId): bool
    {
        $refund = $this->getRefundById($refundId);
        if (!$refund || $refund['status'] !== 'processing') {
            return false;
        }

        $this->db->beginTransaction();
        try {
            // Mark refund completed
            $this->db->dbquery(
                "UPDATE refunds SET status = 'completed', completed_at = NOW()
                 WHERE id = :id AND status = 'processing'"
            );
            $this->db->dbbind(':id', $refundId, PDO::PARAM_INT);
            $this->db->dbexecute();

            // Ensure the payment escrow_status is flipped to refunded
            $this->db->dbquery(
                "UPDATE payments SET escrow_status = 'refunded', refund_id = :rid
                 WHERE booking_id = :bid AND status = 'success' AND escrow_status != 'refunded'"
            );
            $this->db->dbbind(':rid', $refundId, PDO::PARAM_INT);
            $this->db->dbbind(':bid', (int)$refund['booking_id'], PDO::PARAM_INT);
            $this->db->dbexecute();

            // Link refund_id to all successful payments on this booking
            $this->db->dbquery(
                "UPDATE payments SET refund_id = :rid
                 WHERE booking_id = :bid AND status = 'success'"
            );
            $this->db->dbbind(':rid', $refundId, PDO::PARAM_INT);
            $this->db->dbbind(':bid', (int)$refund['booking_id'], PDO::PARAM_INT);
            $this->db->dbexecute();

            // Audit log
            $this->logStatusChange(
                (int)$refund['booking_id'],
                'cancelled',
                'cancelled',
                $adminId,
                'Refund completed: ' . number_format((float)$refund['amount'], 0) . ' MMK'
            );

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollBack();
            error_log('completeRefund failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Admin rejects a refund request.
     */
    public function rejectRefund(int $refundId, int $adminId, string $reason): bool
    {
        $refund = $this->getRefundById($refundId);
        if (!$refund || !in_array($refund['status'], ['pending','processing'], true)) {
            return false;
        }

        $this->db->dbquery(
            "UPDATE refunds SET status = 'rejected', note = :note, processed_by = :admin_id, processed_at = NOW()
             WHERE id = :id AND status IN ('pending','processing')"
        );
        $this->db->dbbind(':note', $reason, PDO::PARAM_STR);
        $this->db->dbbind(':admin_id', $adminId, PDO::PARAM_INT);
        $this->db->dbbind(':id', $refundId, PDO::PARAM_INT);
        $ok = $this->db->dbexecute() && $this->db->rowcount() === 1;

        if ($ok) {
            $this->logStatusChange(
                (int)$refund['booking_id'],
                'cancelled',
                'cancelled',
                $adminId,
                'Refund rejected: ' . $reason
            );
        }

        return $ok;
    }

    /**
     * Aggregate stats for the admin refund queue page.
     */
    public function getRefundStats(): array
    {
        $this->db->dbquery(
            "SELECT
                SUM(status = 'pending')    AS pending_count,
                SUM(status = 'processing') AS processing_count,
                SUM(status = 'completed')  AS completed_count,
                SUM(status = 'rejected')   AS rejected_count,
                COALESCE(SUM(CASE WHEN status IN ('pending','processing') THEN amount ELSE 0 END), 0) AS pending_amount,
                COALESCE(SUM(CASE WHEN status = 'completed' AND DATE(completed_at) = CURDATE() THEN amount ELSE 0 END), 0) AS completed_today
             FROM refunds"
        );
        return $this->db->getsingledata() ?: [];
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
                WHERE bi_count.booking_id = b.id
                  AND (
                      s_count.supplier_id = bs.supplier_id
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
                WHERE bi_total.booking_id = b.id
                  AND (
                      s_total.supplier_id = bs.supplier_id
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

            // Collect supplier IDs before cancelling
            $this->db->dbquery(
                "SELECT DISTINCT supplier_id FROM booking_suppliers WHERE booking_id = :bid AND supplier_id > 0"
            );
            $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
            $supplierRows = $this->db->getmultidata() ?: [];

            // Package bookings with verified payment: auto-confirm non-responsive
            // suppliers instead of cancelling — the customer already paid.
            if ($this->isPackageBooking($bookingId) && $this->isPaymentVerified($bookingId)) {
                $this->autoConfirmAllSuppliers($bookingId);
                $this->updateStatus($bookingId, 'confirmed');
                $this->generateVouchers($bookingId);
                $this->logStatusChange($bookingId, 'pending_supplier_response', 'confirmed', null, 'Auto-confirmed: supplier response deadline passed (payment already verified)');

                // Still track missed responses for supplier accountability
                foreach ($supplierRows as $sRow) {
                    $supplierId = (int)$sRow['supplier_id'];
                    if ($supplierId > 0) {
                        $this->incrementMissedResponseCount($supplierId, $bookingId);
                    }
                }

                $count++;
                continue;
            }

            $this->db->dbquery(
                "UPDATE bookings SET status = 'cancelled' WHERE id = :id LIMIT 1"
            );
            $this->db->dbbind(':id', $bookingId, PDO::PARAM_INT);
            if ($this->db->dbexecute()) {
                $this->logStatusChange($bookingId, 'pending_supplier_response', 'cancelled', null, 'Auto-expired: 24-hour supplier response deadline passed');
                $this->releaseBookingSlots($bookingId);
                $this->releaseAttireItems($bookingId);
                $this->cancelAllSuppliers($bookingId);

                // Track missed responses per supplier and issue warnings
                foreach ($supplierRows as $sRow) {
                    $supplierId = (int)$sRow['supplier_id'];
                    if ($supplierId > 0) {
                        $this->incrementMissedResponseCount($supplierId, $bookingId);
                    }
                }

                $count++;
            }
        }

        return $count;
    }

    /**
     * Increment a supplier's missed-response counter and issue a warning at thresholds.
     */
    private function incrementMissedResponseCount(int $supplierId, int $bookingId): void
    {
        // Check if the missed_response_count column exists
        $this->db->dbquery("SHOW COLUMNS FROM suppliers LIKE 'missed_response_count'");
        if (!$this->db->getsingledata()) {
            return; // Column doesn't exist yet (migration not run)
        }

        // Increment the counter
        $this->db->dbquery(
            "UPDATE suppliers SET missed_response_count = missed_response_count + 1 WHERE supplier_id = :sid LIMIT 1"
        );
        $this->db->dbbind(':sid', $supplierId, PDO::PARAM_INT);
        $this->db->dbexecute();

        // Get the new count
        $this->db->dbquery(
            "SELECT missed_response_count FROM suppliers WHERE supplier_id = :sid LIMIT 1"
        );
        $this->db->dbbind(':sid', $supplierId, PDO::PARAM_INT);
        $result = $this->db->getsingledata();
        $count = (int)($result['missed_response_count'] ?? 0);

        // Issue warning at thresholds: 3 (first warning), 6 (final warning), then every 3
        $shouldWarn = ($count === 3) || ($count === 6) || ($count > 6 && $count % 3 === 0);
        if (!$shouldWarn) {
            return;
        }

        $isNewWarning = ($count === 3);
        $level = $count >= 6 ? 2 : 1;
        $severity = $level === 2 ? 'high' : 'medium';
        $reason = $level === 2
            ? "FINAL WARNING: {$count} bookings auto-cancelled due to non-response. Your account may be restricted."
            : "You have {$count} bookings auto-cancelled due to not responding within 24 hours. Please respond promptly to avoid account restrictions.";

        // Update warning_level and last_warning_at on suppliers table
        $this->db->dbquery(
            "UPDATE suppliers
             SET warning_level = GREATEST(warning_level, :level),
                 last_warning_at = NOW()
             WHERE supplier_id = :sid LIMIT 1"
        );
        $this->db->dbbind(':level', $level, PDO::PARAM_INT);
        $this->db->dbbind(':sid', $supplierId, PDO::PARAM_INT);
        $this->db->dbexecute();

        // Insert into supplier_warnings table
        $this->db->dbquery(
            "INSERT INTO supplier_warnings (supplier_id, issued_by, reason, severity, source, booking_id)
             VALUES (:sid, 0, :reason, :severity, 'system', :bid)"
        );
        $this->db->dbbind(':sid', $supplierId, PDO::PARAM_INT);
        $this->db->dbbind(':reason', $reason);
        $this->db->dbbind(':severity', $severity);
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        $this->db->dbexecute();

        // Notify the supplier
        $this->db->dbquery("SELECT user_id FROM suppliers WHERE supplier_id = :sid LIMIT 1");
        $this->db->dbbind(':sid', $supplierId, PDO::PARAM_INT);
        $userResult = $this->db->getsingledata();
        $userId = (int)($userResult['user_id'] ?? 0);

        if ($userId > 0) {
            $title = $level === 2 ? 'Final Warning: Missed Booking Responses' : 'Warning: Missed Booking Responses';
            $this->db->dbquery(
                "INSERT INTO notifications (user_id, title, message, type, reference_type, reference_id, is_read, created_at)
                 VALUES (:uid, :title, :msg, 'system', 'supplier', :sid, 0, NOW())"
            );
            $this->db->dbbind(':uid', $userId, PDO::PARAM_INT);
            $this->db->dbbind(':title', $title);
            $this->db->dbbind(':msg', $reason);
            $this->db->dbbind(':sid', $supplierId, PDO::PARAM_INT);
            $this->db->dbexecute();
        }
    }

    public function expireAbandonedUnpaidBookings(): int
    {
        $this->db->dbquery(
            "SELECT b.id
               FROM bookings b
              WHERE b.status = 'pending_payment'
                AND b.payment_status = 'unpaid'
                AND b.created_at < (NOW() - INTERVAL 2 HOUR)
                AND NOT EXISTS (
                    SELECT 1 FROM payments p
                     WHERE p.booking_id = b.id
                       AND p.type = 'deposit'
                       AND p.status IN ('pending','success')
                )"
        );
        $rows = $this->db->getmultidata();
        $count = 0;
        foreach ($rows as $row) {
            $bookingId = (int)$row['id'];
            $this->db->dbquery(
                "UPDATE bookings SET status = 'cancelled'
                  WHERE id = :id AND status = 'pending_payment'"
            );
            $this->db->dbbind(':id', $bookingId, PDO::PARAM_INT);
            $this->db->dbexecute();
            if ($this->db->rowcount() === 1) {
                $this->releaseBookingSlots($bookingId);
                $this->releaseAttireItems($bookingId);
                $this->cancelAllSuppliers($bookingId);
                $this->logStatusChange($bookingId, 'pending_payment', 'cancelled', null, 'Auto-expired: deposit not submitted within 2 hours');
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
            "SELECT r.id AS rid, r.booking_id, r.new_supplier_id, r.new_service_id
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

            // Refund any already-paid delta and remember the rejected service
            // before clearing the pick (must run before updateReplacement).
            $rejectedServiceId = (int)($row['new_service_id'] ?? 0);
            $this->reverseReplacementDeltaIfPaid($rid);
            $this->appendRejectedService($rid, $rejectedServiceId);

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
                AND customer_approved_at IS NULL
                AND COALESCE(proposed_at, created_at) < (NOW() - INTERVAL 3 DAY)"
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

        // C) Customer opt-out window expired — clear the deadline so the UI
        //    stops showing the opt-out affordance.
        $this->db->dbquery(
            "UPDATE booking_supplier_replacements
                SET customer_opt_out_deadline = NULL,
                    requires_customer_approval = 0
              WHERE status = 'assigned'
                AND requires_customer_approval = 1
                AND customer_opt_out_deadline IS NOT NULL
                AND customer_opt_out_deadline < NOW()"
        );
        $this->db->dbexecute();

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

        $this->db->dbquery(
            "SELECT b.status,
                    EXISTS (
                        SELECT 1
                        FROM payments p
                        WHERE p.booking_id = b.id
                          AND p.type = 'deposit'
                          AND p.status = 'success'
                    ) AS has_verified_deposit
               FROM bookings b
              WHERE b.id = :id
              LIMIT 1"
        );
        $this->db->dbbind(':id', $bookingId, PDO::PARAM_INT);
        $booking = $this->db->getsingledata();

        return $booking
            && (
                in_array($booking['status'], $allowedStatuses, true)
                || (int)($booking['has_verified_deposit'] ?? 0) === 1
            );
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
        string $paidAt = '',
        float $platformFee = 0.0,
        float $supplierAmount = 0.0
    ): bool {
        $columns = ['booking_id', 'amount', 'type', 'method', 'status', 'transaction_ref', 'escrow_status'];
        $values = [':bid', ':amount', "'deposit'", ':method', "'pending'", ':ref', "'held'"];
        $bindings = [
            ':bid' => [$bookingId, PDO::PARAM_INT],
            ':amount' => [number_format($paidAmount, 2, '.', ''), PDO::PARAM_STR],
            ':method' => [$method, PDO::PARAM_STR],
            ':ref' => [$reference, PDO::PARAM_STR],
        ];

        // Platform fee column
        $columns[] = 'platform_fee';
        $values[] = ':pfee';
        $bindings[':pfee'] = [number_format($platformFee, 2, '.', '')];

        // Supplier amount column
        $columns[] = 'supplier_amount';
        $values[] = ':samt';
        $bindings[':samt'] = [number_format($supplierAmount, 2, '.', '')];

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

        $this->db->beginTransaction();
        try {
            $status = $this->normalizeBookingStatus('payment_submitted');
            $this->db->dbquery(
                "UPDATE bookings
                    SET status = :status
                  WHERE id = :id
                    AND status IN ('draft','pending_payment')
                  LIMIT 1"
            );
            $this->db->dbbind(':status', $status);
            $this->db->dbbind(':id', $bookingId, PDO::PARAM_INT);
            $this->db->dbexecute();
            if ($this->db->rowcount() !== 1) {
                throw new RuntimeException('Booking is no longer awaiting payment.');
            }

            $this->db->dbquery(
                'INSERT INTO payments (' . implode(', ', $columns) . ')
                 VALUES (' . implode(', ', $values) . ')'
            );
            foreach ($bindings as $param => [$value, $type]) {
                $this->db->dbbind($param, $value, $type);
            }
            $this->db->dbexecute();
            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollBack();
            error_log('Payment proof submission failed: ' . $e->getMessage());
            return false;
        }
    }

    public function releaseBookingSlots(int $bookingId): bool
    {
        $this->db->dbquery(
            "SELECT id, slot_id, source
               FROM booking_slot_reservations
              WHERE booking_id = :bid
                AND released_at IS NULL
              ORDER BY id ASC"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        $rows = $this->db->getmultidata();
        foreach ($rows as $row) {
            $slotId = (int)($row['slot_id'] ?? 0);
            if ($slotId <= 0) {
                continue;
            }
            $this->db->dbquery(
                "UPDATE booking_slot_reservations
                    SET released_at = NOW()
                  WHERE id = :id AND released_at IS NULL"
            );
            $this->db->dbbind(':id', (int)$row['id'], PDO::PARAM_INT);
            $this->db->dbexecute();
            if ($this->db->rowcount() === 1) {
                $this->releaseServiceSlot($slotId, (string)($row['source'] ?? 'custom'));
            }
        }
        return true;
    }

    // ─── Attire Rental Reservation ─────────────────────────────────

    /**
     * Reserve an attire item for a date range (block dates).
     * Returns true on success, false if conflict detected.
     */
    public function reserveAttireItem(int $bookingItemId, int $attireItemId, string $rentalType, ?string $borrowDate, ?string $returnDate, ?int $rentalDays, ?int $bufferDays): bool
    {
        if ($rentalType !== 'borrow' || !$borrowDate || !$returnDate) {
            // Buy type — no date blocking needed, just record the booking
            $this->db->dbquery(
                "INSERT INTO attire_rental_bookings (booking_item_id, attire_item_id, rental_type, borrow_date, return_date, rental_days, buffer_until, status)
                 VALUES (:bii, :aii, :rt, NULL, NULL, NULL, NULL, 'reserved')"
            );
            $this->db->dbbind(':bii', $bookingItemId, PDO::PARAM_INT);
            $this->db->dbbind(':aii', $attireItemId, PDO::PARAM_INT);
            $this->db->dbbind(':rt', $rentalType);
            return $this->db->dbexecute();
        }

        $bufferDays = max(0, (int)($bufferDays ?? 1));
        $bufferUntil = date('Y-m-d', strtotime($returnDate . " + " . $bufferDays . " days"));

        // Check for conflicts
        if (!$this->checkAttireConflict($attireItemId, $borrowDate, $bufferUntil)) {
            return false;
        }

        $this->db->dbquery(
            "INSERT INTO attire_rental_bookings (booking_item_id, attire_item_id, rental_type, borrow_date, return_date, rental_days, buffer_until, status)
             VALUES (:bii, :aii, :rt, :bd, :rd, :rda, :bu, 'reserved')"
        );
        $this->db->dbbind(':bii', $bookingItemId, PDO::PARAM_INT);
        $this->db->dbbind(':aii', $attireItemId, PDO::PARAM_INT);
        $this->db->dbbind(':rt', $rentalType);
        $this->db->dbbind(':bd', $borrowDate);
        $this->db->dbbind(':rd', $returnDate);
        $this->db->dbbind(':rda', $rentalDays, PDO::PARAM_INT);
        $this->db->dbbind(':bu', $bufferUntil);

        return $this->db->dbexecute();
    }

    /**
     * Check if an attire item has a conflicting reservation for the given date range.
     * Returns true if available (no conflict), false if conflict exists.
     */
    public function checkAttireConflict(int $attireItemId, string $borrowDate, string $bufferUntil): bool
    {
        $this->db->dbquery(
            "SELECT COUNT(*) AS cnt FROM attire_rental_bookings
             WHERE attire_item_id = :aii
               AND status IN ('reserved', 'picked_up')
               AND borrow_date <= :bu
               AND buffer_until >= :bd"
        );
        $this->db->dbbind(':aii', $attireItemId, PDO::PARAM_INT);
        $this->db->dbbind(':bu', $bufferUntil);
        $this->db->dbbind(':bd', $borrowDate);
        $result = $this->db->getsingledata();

        return ((int)($result['cnt'] ?? 0)) === 0;
    }

    /**
     * Release all attire reservations for a booking (on cancellation).
     */
    public function releaseAttireItems(int $bookingId): bool
    {
        $this->db->dbquery(
            "UPDATE attire_rental_bookings arb
             INNER JOIN booking_items bi ON bi.id = arb.booking_item_id
             SET arb.status = 'cancelled'
             WHERE bi.booking_id = :bid
               AND arb.status = 'reserved'"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        $this->db->dbexecute();
        return true;
    }

    /**
     * Reserve all attire items for a booking's booking_items.
     * Called after insertBookingItems().
     */
    public function reserveBookingAttireItems(int $bookingId): bool
    {
        $this->db->dbquery(
            "SELECT bi.id, bi.attire_item_id, bi.rental_type, bi.borrow_date, bi.return_date,
                    ai.buffer_days, aro.days AS rental_days
             FROM booking_items bi
             JOIN attire_items ai ON ai.id = bi.attire_item_id
             LEFT JOIN attire_rental_options aro ON aro.attire_item_id = bi.attire_item_id AND aro.days = DATEDIFF(bi.return_date, bi.borrow_date) + 1
             WHERE bi.booking_id = :bid
               AND bi.attire_item_id IS NOT NULL
               AND bi.rental_type IS NOT NULL"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        $items = $this->db->getmultidata();

        foreach ($items as $item) {
            $rentalDays = (int)($item['rental_days'] ?? 0);
            if ($rentalDays <= 0 && !empty($item['borrow_date']) && !empty($item['return_date'])) {
                $rentalDays = (int)((strtotime($item['return_date']) - strtotime($item['borrow_date'])) / 86400) + 1;
            }
            $result = $this->reserveAttireItem(
                (int)$item['id'],
                (int)$item['attire_item_id'],
                (string)$item['rental_type'],
                $item['borrow_date'],
                $item['return_date'],
                $rentalDays,
                (int)($item['buffer_days'] ?? 1)
            );
            if (!$result) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get the most recent deposit payment record for a booking.
     */
    public function getDepositPayment(int $bookingId): array|false
    {
        $selects = ['id', 'amount', 'platform_fee', 'supplier_amount', 'method', 'transaction_ref', 'status', 'verified_at', 'created_at'];
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

    public function getPaymentById(int $paymentId): array|false
    {
        $this->db->dbquery("SELECT * FROM payments WHERE id = :id LIMIT 1");
        $this->db->dbbind(':id', $paymentId, PDO::PARAM_INT);
        return $this->db->getsingledata();
    }

    public function markPaymentSuccess(int $paymentId, int $adminId): bool
    {
        $this->db->dbquery(
            "UPDATE payments
                SET status = 'success', verified_by = :admin, verified_at = NOW()
              WHERE id = :id AND status = 'pending'"
        );
        $this->db->dbbind(':admin', $adminId, PDO::PARAM_INT);
        $this->db->dbbind(':id', $paymentId, PDO::PARAM_INT);
        $this->db->dbexecute();
        return $this->db->rowcount() === 1;
    }

    public function getPaymentVerificationError(): string
    {
        return $this->paymentVerificationError ?: 'Failed to verify payment';
    }

    /**
     * Admin verifies payment and moves booking to paid.
     * Notifies all suppliers that booking is ready for their review.
     */
    public function adminVerifyPayment(int $bookingId, int $adminId, string $note = ''): bool
    {
        $this->paymentVerificationError = null;
        $this->db->dbquery(
            "SELECT p.id,
                    COALESCE(p.paid_amount, p.amount, 0) AS submitted_amount,
                    b.total_amount
               FROM payments p
               INNER JOIN bookings b ON b.id = p.booking_id
              WHERE p.booking_id = :bid
                AND p.type = 'deposit'
                AND p.status = 'pending'
              ORDER BY p.id DESC
              LIMIT 1"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        $pendingPayment = $this->db->getsingledata();
        $paymentId = (int)($pendingPayment['id'] ?? 0);
        if ($paymentId <= 0) {
            $this->paymentVerificationError = 'No pending deposit payment found for this booking. It may already be reviewed.';
            return false;
        }
        $totalAmount = (float)($pendingPayment['total_amount'] ?? 0);
        $expectedDeposit = (int)round($totalAmount * (BOOKING_DEPOSIT_PERCENT / 100));
        $expectedPlatformFee = (int)round($totalAmount * (get_platform_fee_percent() / 100));
        $expectedPayment = (float)($expectedDeposit + $expectedPlatformFee);
        $submittedAmount = (float)($pendingPayment['submitted_amount'] ?? 0);
        if ($expectedPayment <= 0) {
            $this->paymentVerificationError = 'Booking total is invalid, so the expected payment cannot be calculated.';
            return false;
        }
        if (abs($submittedAmount - $expectedPayment) > 0.01) {
            $this->paymentVerificationError = 'Payment amount must equal deposit plus platform fee: '
                . number_format($expectedPayment, 0) . ' MMK expected, '
                . number_format($submittedAmount, 0) . ' MMK submitted.';
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
                "UPDATE bookings
                    SET status = :status,
                        payment_status = 'partial',
                        paid_amount = :paid_amount
                  WHERE id = :id
                  LIMIT 1"
            );
            $this->db->dbbind(':status', $status);
            $this->db->dbbind(
                ':paid_amount',
                number_format((float)$pendingPayment['submitted_amount'], 2, '.', '')
            );
            $this->db->dbbind(':id', $bookingId, PDO::PARAM_INT);
            if (!$this->db->dbexecute()) {
                throw new RuntimeException('Booking status could not be updated.');
            }
            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            $this->paymentVerificationError = 'Payment verification failed while updating records.';
            error_log('Deposit verification failed: ' . $e->getMessage());
            return false;
        }

        // Package bookings auto-accept every supplier line by default. Custom
        // bookings reach payment only after supplier acceptance, so verifying
        // the deposit can confirm the booking immediately.
        if ($this->isPackageBooking($bookingId)) {
            if ($this->autoConfirmAllSuppliers($bookingId)) {
                $this->updateStatus($bookingId, 'confirmed');
                $this->generateVouchers($bookingId);
            }
        } elseif ($this->allSuppliersAccepted($bookingId)) {
            $this->updateStatus($bookingId, 'confirmed');
            $this->generateVouchers($bookingId);
        }

        return true;
    }

    /**
     * Verify a remaining balance payment (admin action).
     * Marks the remaining payment as success and finalizes the booking.
     */
    public function adminVerifyRemainingPayment(int $bookingId, int $adminId, string $note = ''): bool
    {
        $this->paymentVerificationError = null;
        $this->db->dbquery(
            "SELECT p.id,
                    COALESCE(p.paid_amount, p.amount, 0) AS submitted_amount,
                    b.total_amount
               FROM payments p
               INNER JOIN bookings b ON b.id = p.booking_id
              WHERE p.booking_id = :bid
                AND p.type = 'remaining'
                AND p.status = 'pending'
              ORDER BY p.id DESC
              LIMIT 1"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        $pendingPayment = $this->db->getsingledata();
        $paymentId = (int)($pendingPayment['id'] ?? 0);
        if ($paymentId <= 0) {
            $this->paymentVerificationError = 'No pending remaining payment found for this booking.';
            return false;
        }

        $totalAmount = (float)($pendingPayment['total_amount'] ?? 0);
        $submittedAmount = (float)($pendingPayment['submitted_amount'] ?? 0);

        // Calculate already-paid from actual successful payments (not from
        // bookings.paid_amount, which may be stale). Exclude platform fees
        // since they are not part of the service total.
        $this->db->dbquery(
            "SELECT COALESCE(SUM(COALESCE(paid_amount, amount)), 0) AS total_paid,
                    COALESCE(SUM(COALESCE(platform_fee, 0)), 0) AS total_fees
               FROM payments
              WHERE booking_id = :bid
                AND status = 'success'
                AND id <> :exclude_id"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        $this->db->dbbind(':exclude_id', $paymentId, PDO::PARAM_INT);
        $paidRow = $this->db->getsingledata();
        $alreadyPaid = (float)($paidRow['total_paid'] ?? 0);
        $totalFees = (float)($paidRow['total_fees'] ?? 0);

        // Subtract platform fees — they are not part of the service price
        $expectedBalance = max(0, $totalAmount - ($alreadyPaid - $totalFees));

        if ($expectedBalance <= 0) {
            $this->paymentVerificationError = 'This booking is already fully paid.';
            return false;
        }

        // Validate: amount must be between minimum and remaining balance
        $minPayment = defined('MIN_REMAINING_PAYMENT') ? (float)MIN_REMAINING_PAYMENT : 1000;
        if ($submittedAmount < $minPayment - 0.01) {
            $this->paymentVerificationError = 'Payment amount must be at least '
                . number_format($minPayment, 0) . ' MMK.';
            return false;
        }
        if ($submittedAmount > $expectedBalance + 0.01) {
            $this->paymentVerificationError = 'Payment amount cannot exceed remaining balance: '
                . number_format($expectedBalance, 0) . ' MMK.';
            return false;
        }

        $this->db->beginTransaction();
        try {
            // Mark remaining payment as success
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
                throw new RuntimeException('Remaining payment was already reviewed.');
            }

            // Calculate new total paid (including this payment).
            // alreadyPaid excludes platform fees; add them back so
            // bookings.paid_amount stays consistent (it includes fees).
            $newTotalPaid = $alreadyPaid + $totalFees + $submittedAmount;
            $isFullyPaid = ($alreadyPaid + $submittedAmount) >= ($totalAmount - 0.01);

            if ($isFullyPaid) {
                // Fully paid — finalize the booking
                $this->db->dbquery(
                    "UPDATE bookings
                        SET status = 'finalized',
                            payment_status = 'paid',
                            paid_amount = total_amount
                      WHERE id = :id LIMIT 1"
                );
                $this->db->dbbind(':id', $bookingId, PDO::PARAM_INT);
                if (!$this->db->dbexecute()) {
                    throw new RuntimeException('Booking status could not be updated.');
                }
            } else {
                // Partial payment — update paid_amount, keep status as confirmed
                $this->db->dbquery(
                    "UPDATE bookings
                        SET paid_amount = :paid,
                            payment_status = 'partial'
                      WHERE id = :id LIMIT 1"
                );
                $this->db->dbbind(':paid', number_format($newTotalPaid, 2, '.', ''));
                $this->db->dbbind(':id', $bookingId, PDO::PARAM_INT);
                if (!$this->db->dbexecute()) {
                    throw new RuntimeException('Booking paid_amount could not be updated.');
                }
            }

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollBack();
            $this->paymentVerificationError = 'Remaining payment verification failed.';
            error_log('Remaining payment verification failed: ' . $e->getMessage());
            return false;
        }
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

        // Deduct platform fees so suppliers only receive the deposit portion.
        // paid_amount may include platform fee (deposit + fee), but payouts
        // should be based on the deposit portion only.
        $this->db->dbquery(
            "SELECT COALESCE(SUM(platform_fee), 0) AS total_fees
               FROM payments
              WHERE booking_id = :bid AND status = 'success'"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        $feeRow = $this->db->getsingledata();
        $totalPlatformFees = (float)($feeRow['total_fees'] ?? 0);
        $paidAmount = max(0, $paidAmount - $totalPlatformFees);

        // Get all suppliers and their amounts for this booking
        $this->db->dbquery(
            "SELECT bs.supplier_id,
                    COALESCE(SUM(bs.item_price), 0) AS supplier_service_amount
               FROM booking_suppliers bs
              WHERE bs.booking_id = :bid
               AND bs.status IN ('confirmed','in_progress','completed')
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
                    "SELECT id FROM payments
                      WHERE booking_id = :existing_bid
                        AND supplier_id = :existing_sid
                        AND type = 'payout'
                      LIMIT 1"
                );
                $this->db->dbbind(':existing_bid', $bookingId, PDO::PARAM_INT);
                $this->db->dbbind(':existing_sid', $supplierId, PDO::PARAM_INT);
                if ($this->db->getsingledata()) {
                    continue;
                }
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
     * Get the platform fee portion that was included in the deposit payment.
     * bookings.paid_amount includes the platform fee, so subtract this to get
     * the true service-payment portion only.
     */
    public function getDepositPlatformFee(int $bookingId): float
    {
        $this->db->dbquery(
            "SELECT COALESCE(SUM(platform_fee), 0) AS total_fees
               FROM payments
              WHERE booking_id = :bid
                AND type = 'deposit'
                AND status = 'success'"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        $row = $this->db->getsingledata();
        return (float)($row['total_fees'] ?? 0);
    }

    /**
     * Get the true remaining balance for a booking.
     * Unlike bookings.paid_amount (which includes platform fees), this returns
     * the actual amount still owed toward the service total.
     */
    public function getTrueRemainingBalance(int $bookingId): float
    {
        $this->db->dbquery(
            "SELECT COALESCE(total_amount, 0) AS total,
                    COALESCE(paid_amount, 0) AS paid
               FROM bookings WHERE id = :id LIMIT 1"
        );
        $this->db->dbbind(':id', $bookingId, PDO::PARAM_INT);
        $booking = $this->db->getsingledata();
        if (!$booking) return 0.0;

        $total = (float)$booking['total'];
        $paid = (float)$booking['paid'];
        $platformFee = $this->getDepositPlatformFee($bookingId);

        return max(0, $total - ($paid - $platformFee));
    }

    /**
     * Check if a booking has a pending (under review) remaining balance payment.
     */
    public function hasPendingRemainingPayment(int $bookingId): bool
    {
        $this->db->dbquery(
            "SELECT 1 FROM payments
              WHERE booking_id = :bid AND type = 'remaining' AND status IN ('pending','processing')
              LIMIT 1"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        return (bool)$this->db->getsingledata();
    }

    /**
     * Check if a booking has any pending payment (deposit or remaining) awaiting verification.
     */
    public function hasPendingPayment(int $bookingId): bool
    {
        $this->db->dbquery(
            "SELECT 1 FROM payments
              WHERE booking_id = :bid AND type IN ('deposit','remaining') AND status IN ('pending','processing')
              LIMIT 1"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        return (bool)$this->db->getsingledata();
    }

    /**
     * Get all remaining balance payments for a booking (successful + pending).
     */
    public function getRemainingPayments(int $bookingId): array
    {
        $this->db->dbquery(
            "SELECT * FROM payments
              WHERE booking_id = :bid AND type = 'remaining'
              ORDER BY created_at ASC"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        return $this->db->getmultidata();
    }

    /**
     * Get the earliest event date for a booking.
     * Returns null when no event date is set.
     */
    public function getFirstEventDate(int $bookingId): ?string
    {
        $this->db->dbquery(
            "SELECT MIN(event_date) AS event_date
             FROM event_details
             WHERE booking_id = :bid AND event_date IS NOT NULL"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        $row = $this->db->getsingledata();
        return $row['event_date'] ?? null;
    }

    /**
     * Submit remaining balance payment slip (manual bank transfer).
     * Creates a 'remaining' type payment record and updates booking status.
     */
    public function submitRemainingPaymentSlip(
        int $bookingId,
        string $slipPath,
        string $reference,
        string $method,
        string $accountName = '',
        string $mobileNumber = '',
        float $paidAmount = 0.0,
        string $paidAt = ''
    ): bool {
        $this->db->beginTransaction();
        try {
            // Create remaining payment record
            $columns = ['booking_id', 'amount', 'type', 'method', 'status', 'transaction_ref', 'escrow_status'];
            $values = [':bid', ':amount', "'remaining'", ':method', "'pending'", ':ref', "'held'"];
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

            $sql = "INSERT INTO payments (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $values) . ")";
            $this->db->dbquery($sql);
            foreach ($bindings as $param => [$value, $type]) {
                $this->db->dbbind($param, $value, $type);
            }

            if (!$this->db->dbexecute()) {
                throw new RuntimeException('Failed to create payment record.');
            }

            // Update booking status to pending_final_payment only after the
            // booking itself is confirmed.
            $this->db->dbquery(
                "UPDATE bookings SET status = 'pending_final_payment'
                 WHERE id = :id
                   AND status = 'confirmed'
                 LIMIT 1"
            );
            $this->db->dbbind(':id', $bookingId, PDO::PARAM_INT);
            $this->db->dbexecute();

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollBack();
            error_log('Remaining payment submission failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Collect the remaining balance for bookings where the event is within 3 days.
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

            $remaining = max(0, $total - $paid);

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
     * Calculate refund amount based on who cancelled and booking age.
     *
     * @param int    $bookingId
     * @param string $cancelledBy  'customer', 'supplier', or 'admin'
     * @return array|false  [amount, policyReason] or false on error
     */
    public function calculateRefund(int $bookingId, string $cancelledBy = 'customer'): array|false
    {
        $this->db->dbquery(
            "SELECT b.total_amount, b.paid_amount, b.created_at
             FROM bookings b
             WHERE b.id = :id LIMIT 1"
        );
        $this->db->dbbind(':id', $bookingId, PDO::PARAM_INT);
        $booking = $this->db->getsingledata();

        if (!$booking) {
            return false;
        }

        $paidAmount = (float)($booking['paid_amount'] ?? 0);

        // Supplier or admin fault: full refund regardless of timing
        if ($cancelledBy === 'supplier') {
            return [$paidAmount, 'Full refund — cancellation initiated by supplier'];
        }
        if ($cancelledBy === 'admin') {
            return [$paidAmount, 'Full refund — cancellation initiated by admin'];
        }

        // Customer-initiated: refundable only within 7 days of booking creation.
        $bookingCreatedAt = !empty($booking['created_at']) ? strtotime((string)$booking['created_at']) : 0;
        if (!$bookingCreatedAt) {
            return [0, 'No refund — booking date unavailable'];
        }

        $bookingAgeSeconds = max(0, time() - $bookingCreatedAt);
        if ($bookingAgeSeconds <= 7 * 86400) {
            return [$paidAmount, 'Full refund — cancelled within 7 days of booking date'];
        }

        return [0, 'No refund — cancelled more than 7 days after booking date'];
    }

    /**
     * Get supplier earnings (unpaid + paid payouts).
     */
    public function getSupplierEarnings(int $supplierId): array
    {
        $this->db->dbquery(
            "SELECT
                COALESCE(SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END), 0) as pending_amount,
                COALESCE(SUM(CASE WHEN status = 'processing' THEN amount ELSE 0 END), 0) as processing_amount,
                COALESCE(SUM(CASE WHEN status = 'success' THEN amount ELSE 0 END), 0) as paid_amount,
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_count,
                COUNT(CASE WHEN status = 'processing' THEN 1 END) as processing_count,
                COUNT(CASE WHEN status = 'success' THEN 1 END) as paid_count
             FROM payments
             WHERE supplier_id = :sid AND type = 'payout'"
        );
        $this->db->dbbind(':sid', $supplierId, PDO::PARAM_INT);
        $result = $this->db->getsingledata();

        return [
            'pending_amount' => (float)($result['pending_amount'] ?? 0),
            'processing_amount' => (float)($result['processing_amount'] ?? 0),
            'paid_amount' => (float)($result['paid_amount'] ?? 0),
            'pending_count' => (int)($result['pending_count'] ?? 0),
            'processing_count' => (int)($result['processing_count'] ?? 0),
            'paid_count' => (int)($result['paid_count'] ?? 0),
            'total_earned' => (float)(
                ($result['pending_amount'] ?? 0)
                + ($result['processing_amount'] ?? 0)
                + ($result['paid_amount'] ?? 0)
            ),
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

    /**
     * Get supplier earnings breakdown by booking with service names.
     * Used on the earnings page to show per-booking detail.
     *
     * Each row represents one payout record joined to its booking and service.
     * Uses GROUP_CONCAT for service names since a payout is per-supplier-per-booking,
     * and a supplier can have multiple services in one booking.
     */
    public function getSupplierEarningsBreakdown(int $supplierId, int $limit = 20, int $offset = 0): array
    {
        // Payout amount is net (platform fees already deducted by settleSupplierPayouts).
        // Derive the supplier's proportional share of platform fees from the
        // original payment records (deposit/remaining/full) for each booking,
        // so historical fee rates are preserved even if the global rate changes.
        $this->db->dbquery(
            "SELECT
                p.id AS payment_id,
                p.booking_id,
                p.amount AS net_amount,
                p.status,
                p.created_at,
                p.verified_at,
                p.verified_note,
                p.payout_batch_id,
                b.created_at AS booking_date,
                b.total_amount AS booking_total,
                GROUP_CONCAT(DISTINCT s.name SEPARATOR ', ') AS service_names,
                GROUP_CONCAT(DISTINCT bs.item_price) AS item_prices,
                SUM(DISTINCT bs.item_price) AS supplier_service_total,
                (
                    SELECT COALESCE(SUM(COALESCE(py.platform_fee, 0)), 0)
                      FROM payments py
                     WHERE py.booking_id = p.booking_id
                       AND py.status = 'success'
                       AND py.type IN ('deposit','remaining','full')
                ) AS booking_total_fees,
                (
                    SELECT r2.amount FROM refunds r2
                     WHERE r2.booking_id = p.booking_id AND r2.status = 'completed'
                     ORDER BY r2.id DESC LIMIT 1
                ) AS refund_amount,
                (
                    SELECT r3.status FROM refunds r3
                     WHERE r3.booking_id = p.booking_id
                     ORDER BY r3.id DESC LIMIT 1
                ) AS refund_status
             FROM payments p
             JOIN bookings b ON b.id = p.booking_id
             JOIN booking_suppliers bs ON bs.booking_id = p.booking_id AND bs.supplier_id = p.supplier_id
             LEFT JOIN services s ON s.id = bs.service_id
             WHERE p.supplier_id = :sid AND p.type = 'payout'
             GROUP BY p.id
             ORDER BY p.created_at DESC
             LIMIT :limit OFFSET :offset"
        );
        $this->db->dbbind(':sid', $supplierId, PDO::PARAM_INT);
        $this->db->dbbind(':limit', $limit, PDO::PARAM_INT);
        $this->db->dbbind(':offset', $offset, PDO::PARAM_INT);

        $rows = $this->db->getmultidata();

        foreach ($rows as &$row) {
            $net = (float)($row['net_amount'] ?? 0);
            $bookingTotal = (float)($row['booking_total'] ?? 0);
            $supplierServiceTotal = (float)($row['supplier_service_total'] ?? 0);
            $bookingTotalFees = (float)($row['booking_total_fees'] ?? 0);

            // Supplier's proportional share of the platform fees
            $proportion = ($bookingTotal > 0) ? ($supplierServiceTotal / $bookingTotal) : 0;
            $fee = $proportion * $bookingTotalFees;
            $gross = $net + $fee;

            $row['platform_fee'] = round($fee, 2);
            $row['gross_amount'] = round($gross, 2);
        }
        unset($row);

        return $rows;
    }

    /**
     * Get supplier's gross earnings from all successful booking payments
     * associated with the supplier. Returns gross amount, platform fees,
     * net earnings, and the count of completed bookings that generated payouts.
     */
    public function getSupplierGrossEarnings(int $supplierId): array
    {
        // Payout records store the NET amount (after platform fees were deducted
        // during settleSupplierPayouts).  platform_fee is NULL on payout rows.
        // Derive each supplier's proportional share of platform fees from the
        // original payment records so historical fee rates are preserved.
        //
        // One row per payout (GROUP BY p.id), then sum in PHP.
        // This mirrors getSupplierEarningsBreakdown's fee logic.
        $this->db->dbquery(
            "SELECT
                p.amount AS net_amount,
                b.total_amount AS booking_total,
                SUM(DISTINCT bs.item_price) AS supplier_service_total,
                (
                    SELECT COALESCE(SUM(COALESCE(py.platform_fee, 0)), 0)
                      FROM payments py
                     WHERE py.booking_id = p.booking_id
                       AND py.status = 'success'
                       AND py.type IN ('deposit','remaining','full')
                ) AS booking_total_fees
             FROM payments p
             JOIN bookings b ON b.id = p.booking_id
             JOIN booking_suppliers bs ON bs.booking_id = p.booking_id AND bs.supplier_id = p.supplier_id
             WHERE p.supplier_id = :sid AND p.type = 'payout' AND p.status = 'success'
             GROUP BY p.id"
        );
        $this->db->dbbind(':sid', $supplierId, PDO::PARAM_INT);
        $rows = $this->db->getmultidata() ?: [];

        $paidNet = 0.0;
        $paidFees = 0.0;

        foreach ($rows as $row) {
            $net = (float)($row['net_amount'] ?? 0);
            $bookingTotal = (float)($row['booking_total'] ?? 0);
            $supplierServiceTotal = (float)($row['supplier_service_total'] ?? 0);
            $bookingTotalFees = (float)($row['booking_total_fees'] ?? 0);

            $proportion = ($bookingTotal > 0) ? ($supplierServiceTotal / $bookingTotal) : 0;
            $fee = $proportion * $bookingTotalFees;

            $paidNet += $net;
            $paidFees += $fee;
        }

        return [
            'gross_earnings' => round($paidNet + $paidFees, 2),
            'platform_fees'  => round($paidFees, 2),
            'net_earnings'   => round($paidNet, 2),
            'completed_booking_count' => count($rows),
        ];
    }

    /**
     * Get all payment transactions related to a supplier's bookings (deposits, remaining, full).
     * This is the supplier's payment history — what customers paid toward their services.
     */
    public function getSupplierPaymentHistory(int $supplierId, int $limit = 20, int $offset = 0, array $filters = []): array
    {
        $where = "p.type IN ('deposit','remaining','full','payout')";
        $params = [[':sid1', $supplierId, PDO::PARAM_INT]];

        if (!empty($filters['status']) && in_array($filters['status'], ['success', 'pending', 'failed'], true)) {
            $where .= ' AND p.status = :status';
            $params[] = [':status', $filters['status'], PDO::PARAM_STR];
        }
        if (!empty($filters['type']) && in_array($filters['type'], ['deposit', 'remaining', 'full', 'payout'], true)) {
            $where .= ' AND p.type = :type';
            $params[] = [':type', $filters['type'], PDO::PARAM_STR];
        }
        if (!empty($filters['escrow']) && in_array($filters['escrow'], ['held', 'released', 'refunded'], true)) {
            $where .= ' AND p.escrow_status = :escrow';
            $params[] = [':escrow', $filters['escrow'], PDO::PARAM_STR];
        }

        $this->db->dbquery(
            "SELECT p.id, p.booking_id, p.amount, p.platform_fee, p.supplier_amount,
                    p.type, p.status, p.escrow_status, p.method, p.created_at, p.paid_at,
                    u.name AS customer_name
             FROM payments p
             JOIN booking_suppliers bs ON bs.booking_id = p.booking_id AND bs.supplier_id = :sid1
             JOIN bookings b ON b.id = p.booking_id
             JOIN users u ON u.user_id = b.user_id
             WHERE {$where}
             ORDER BY p.created_at DESC
             LIMIT :limit OFFSET :offset"
        );
        foreach ($params as $pv) { $this->db->dbbind($pv[0], $pv[1], $pv[2]); }
        $this->db->dbbind(':limit', $limit, PDO::PARAM_INT);
        $this->db->dbbind(':offset', $offset, PDO::PARAM_INT);
        return $this->db->getmultidata();
    }

    public function getSupplierPaymentHistoryCount(int $supplierId, array $filters = []): int
    {
        $where = "p.type IN ('deposit','remaining','full','payout')";
        $params = [[':sid', $supplierId, PDO::PARAM_INT]];

        if (!empty($filters['status']) && in_array($filters['status'], ['success', 'pending', 'failed'], true)) {
            $where .= ' AND p.status = :status';
            $params[] = [':status', $filters['status'], PDO::PARAM_STR];
        }
        if (!empty($filters['type']) && in_array($filters['type'], ['deposit', 'remaining', 'full', 'payout'], true)) {
            $where .= ' AND p.type = :type';
            $params[] = [':type', $filters['type'], PDO::PARAM_STR];
        }
        if (!empty($filters['escrow']) && in_array($filters['escrow'], ['held', 'released', 'refunded'], true)) {
            $where .= ' AND p.escrow_status = :escrow';
            $params[] = [':escrow', $filters['escrow'], PDO::PARAM_STR];
        }

        $this->db->dbquery(
            "SELECT COUNT(DISTINCT p.id)
             FROM payments p
             JOIN booking_suppliers bs ON bs.booking_id = p.booking_id AND bs.supplier_id = :sid
             WHERE {$where}"
        );
        foreach ($params as $pv) { $this->db->dbbind($pv[0], $pv[1], $pv[2]); }
        $row = $this->db->getsingledata();
        return (int)($row['COUNT(DISTINCT p.id)'] ?? 0);
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
