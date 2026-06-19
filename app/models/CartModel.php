<?php

class CartModel
{
    private $db;
    private ?bool $cartVenueRoomColumn = null;
    private ?bool $cartPackageParentColumn = null;
    private ?bool $serviceDefaultTimeColumns = null;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * Get the user's active cart, or create one if none exists.
     */
    public function getOrCreateCart(int $userId): int
    {
        $this->db->dbquery("SELECT id FROM carts WHERE user_id = :uid LIMIT 1");
        $this->db->dbbind(':uid', $userId);
        $row = $this->db->getsingledata();

        if ($row && !empty($row['id'])) {
            return (int)$row['id'];
        }

        $this->db->dbquery("INSERT INTO carts (user_id) VALUES (:uid)");
        $this->db->dbbind(':uid', $userId);
        $this->db->dbexecute();
        return (int)$this->db->lastinsertid();
    }

    /**
     * Add an item to the user's cart.
     * Returns the cart_item_id on success, or false if duplicate/error.
     */
    public function addItem(int $userId, array $data)
    {
        $itemType  = $data['item_type'] ?? 'service';
        $itemId    = (int)($data['item_id'] ?? 0);
        $date      = $data['selected_date'] ?? null;
        $price     = $data['price'] ?? null;
        $source    = $data['source'] ?? null;
        $slotId    = !empty($data['slot_id']) ? (int)$data['slot_id'] : null;
        $venueRoomId = !empty($data['venue_room_id']) ? (int)$data['venue_room_id'] : null;
        $startTime = $data['start_time'] ?? null;
        $endTime   = $data['end_time'] ?? null;
        $packageCartItemId = !empty($data['package_cart_item_id']) ? (int)$data['package_cart_item_id'] : null;
        $hasVenueRoomColumn = $this->hasCartVenueRoomColumn();
        $hasPackageParentColumn = $this->hasCartPackageParentColumn();

        if ($itemId <= 0) {
            return false;
        }

        // Check for duplicate: same user, item_type, item_id, date, slot
        $this->db->dbquery(
            "SELECT id FROM cart_items
             WHERE user_id = :uid AND item_type = :itype AND item_id = :iid
               AND (selected_date = :sdate OR (selected_date IS NULL AND :sdate IS NULL))
               AND (slot_id = :sid OR (slot_id IS NULL AND :sid IS NULL))"
               . ($hasVenueRoomColumn ? "
               AND (venue_room_id = :vrid OR (venue_room_id IS NULL AND :vrid IS NULL))" : "") . "
               " . ($hasPackageParentColumn ? "
               AND (package_cart_item_id = :package_cart_item_id OR (package_cart_item_id IS NULL AND :package_cart_item_id IS NULL))" : "") . "
             LIMIT 1"
        );
        $this->db->dbbind(':uid', $userId);
        $this->db->dbbind(':itype', $itemType);
        $this->db->dbbind(':iid', $itemId, PDO::PARAM_INT);
        $this->db->dbbind(':sdate', $date);
        $this->db->dbbind(':sid', $slotId, PDO::PARAM_INT);
        if ($hasVenueRoomColumn) {
            $this->db->dbbind(':vrid', $venueRoomId, $venueRoomId ? PDO::PARAM_INT : PDO::PARAM_NULL);
        }
        if ($hasPackageParentColumn) {
            $this->db->dbbind(
                ':package_cart_item_id',
                $packageCartItemId,
                $packageCartItemId ? PDO::PARAM_INT : PDO::PARAM_NULL
            );
        }
        $existing = $this->db->getsingledata();

        if ($existing && !empty($existing['id'])) {
            return false; // Already in cart
        }

        $cartId = $this->getOrCreateCart($userId);

        $venueRoomColumnSql = $hasVenueRoomColumn ? ', venue_room_id' : '';
        $venueRoomValueSql = $hasVenueRoomColumn ? ', :vrid' : '';
        $packageParentColumnSql = $hasPackageParentColumn ? ', package_cart_item_id' : '';
        $packageParentValueSql = $hasPackageParentColumn ? ', :package_cart_item_id' : '';
        $this->db->dbquery(
            "INSERT INTO cart_items (cart_id, user_id, item_type, item_id, selected_date, price, source, slot_id, start_time, end_time{$venueRoomColumnSql}{$packageParentColumnSql})
             VALUES (:cid, :uid, :itype, :iid, :sdate, :price, :src, :sid, :stime, :etime{$venueRoomValueSql}{$packageParentValueSql})"
        );
        $this->db->dbbind(':cid', $cartId, PDO::PARAM_INT);
        $this->db->dbbind(':uid', $userId, PDO::PARAM_INT);
        $this->db->dbbind(':itype', $itemType);
        $this->db->dbbind(':iid', $itemId, PDO::PARAM_INT);
        $this->db->dbbind(':sdate', $date);
        $this->db->dbbind(':price', $price);
        $this->db->dbbind(':src', $source);
        $this->db->dbbind(':sid', $slotId, PDO::PARAM_INT);
        $this->db->dbbind(':stime', $startTime);
        $this->db->dbbind(':etime', $endTime);
        if ($hasVenueRoomColumn) {
            $this->db->dbbind(':vrid', $venueRoomId, $venueRoomId ? PDO::PARAM_INT : PDO::PARAM_NULL);
        }
        if ($hasPackageParentColumn) {
            $this->db->dbbind(
                ':package_cart_item_id',
                $packageCartItemId,
                $packageCartItemId ? PDO::PARAM_INT : PDO::PARAM_NULL
            );
        }

        if ($this->db->dbexecute()) {
            return (int)$this->db->lastinsertid();
        }
        return false;
    }

    /**
     * Find a package already in the user's cart that includes this service.
     */
    public function findCartPackageIncludingService(int $userId, int $serviceId): array|false
    {
        $this->db->dbquery(
            "SELECT ci.id AS cart_item_id,
                    p.package_id,
                    p.name AS package_name,
                    s.name AS service_name
             FROM cart_items ci
             INNER JOIN packages p ON ci.item_id = p.package_id AND ci.item_type = 'package'
             INNER JOIN package_items pi ON pi.package_id = p.package_id
             INNER JOIN services s ON pi.service_id = s.id
             WHERE ci.user_id = :uid
               AND pi.service_id = :sid
             ORDER BY ci.id DESC
             LIMIT 1"
        );
        $this->db->dbbind(':uid', $userId, PDO::PARAM_INT);
        $this->db->dbbind(':sid', $serviceId, PDO::PARAM_INT);

        return $this->db->getsingledata();
    }

    public function findPackageCartItem(int $userId, int $packageId): array|false
    {
        $this->db->dbquery(
            "SELECT ci.id AS cart_item_id,
                    ci.item_id AS package_id,
                    p.name AS package_name,
                    p.slug AS package_slug
             FROM cart_items ci
             INNER JOIN packages p ON p.package_id = ci.item_id
             WHERE ci.user_id = :uid
               AND ci.item_type = 'package'
               AND ci.item_id = :package_id
             ORDER BY ci.id DESC
             LIMIT 1"
        );
        $this->db->dbbind(':uid', $userId, PDO::PARAM_INT);
        $this->db->dbbind(':package_id', $packageId, PDO::PARAM_INT);

        return $this->db->getsingledata();
    }

    /**
     * Remove a single item from the cart.
     */
    public function removeItem(int $userId, int $cartItemId): bool
    {
        $this->db->dbquery(
            "DELETE FROM cart_items WHERE id = :ciid AND user_id = :uid LIMIT 1"
        );
        $this->db->dbbind(':ciid', $cartItemId, PDO::PARAM_INT);
        $this->db->dbbind(':uid', $userId, PDO::PARAM_INT);
        return $this->db->dbexecute();
    }

    /**
     * Update customization details for one cart item owned by the user.
     */
    public function updateItemCustomization(int $userId, int $cartItemId, array $data): bool
    {
        $this->db->dbquery(
            "UPDATE cart_items
             SET selected_date = :sdate,
                 slot_id = :sid,
                 start_time = :stime,
                 end_time = :etime
             WHERE id = :ciid AND user_id = :uid
             LIMIT 1"
        );
        $this->db->dbbind(':sdate', $data['selected_date'] ?? null);
        $this->db->dbbind(':sid', $data['slot_id'] ?? null, !empty($data['slot_id']) ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $this->db->dbbind(':stime', $data['start_time'] ?? null);
        $this->db->dbbind(':etime', $data['end_time'] ?? null);
        $this->db->dbbind(':ciid', $cartItemId, PDO::PARAM_INT);
        $this->db->dbbind(':uid', $userId, PDO::PARAM_INT);
        return $this->db->dbexecute();
    }

    public function getCartItem(int $userId, int $cartItemId): array|false
    {
        $this->db->dbquery(
            "SELECT ci.*, s.booking_type, s.duration_minutes, s.buffer_minutes, s.max_concurrent
             FROM cart_items ci
             LEFT JOIN services s ON ci.item_id = s.id AND ci.item_type = 'service'
             WHERE ci.id = :ciid AND ci.user_id = :uid
             LIMIT 1"
        );
        $this->db->dbbind(':ciid', $cartItemId, PDO::PARAM_INT);
        $this->db->dbbind(':uid', $userId, PDO::PARAM_INT);
        return $this->db->getsingledata();
    }

    public function getAvailableSlotsForServiceDate(int $serviceId, string $date): array
    {
        $date = $this->normalizeDate($date);
        if (!$date || strtotime($date) < strtotime(date('Y-m-d'))) {
            return [];
        }

        $this->db->dbquery(
            "SELECT booking_type, duration_minutes, buffer_minutes,
                    max_concurrent, max_concurrent_package, max_concurrent_customize, min_lead_days
             FROM services
             WHERE id = :sid AND is_active = 1
             LIMIT 1"
        );
        $this->db->dbbind(':sid', $serviceId, PDO::PARAM_INT);
        $service = $this->db->getsingledata();
        if (!$service) {
            return [];
        }

        $minLeadDays = max(0, (int)($service['min_lead_days'] ?? 0));
        $earliestDate = date('Y-m-d', strtotime('+' . $minLeadDays . ' days'));
        if (strtotime($date) < strtotime($earliestDate)) {
            return [];
        }

        $hours = $this->hoursForServiceDate($serviceId, $date);
        if (!$hours) {
            return [];
        }

        $bookingType = ($service['booking_type'] ?? 'fullday') === 'slot' ? 'slot' : 'fullday';
        if ($bookingType !== 'slot') {
            if (!$this->isFutureSlot($date, $hours['open_time'])) {
                return [];
            }
            return [[
                'slot_id' => null,
                'start_time' => $hours['open_time'],
                'end_time' => $hours['close_time'],
                'display' => $this->formatTimeRange($hours['open_time'], $hours['close_time']),
                'available' => max(1, (int)($service['max_concurrent'] ?? 1)),
                'available_package' => (int)($service['max_concurrent_package'] ?? 0) > 0 ? (int)($service['max_concurrent_package'] ?? 0) : max(1, (int)($service['max_concurrent'] ?? 1)),
                'available_customize' => (int)($service['max_concurrent_customize'] ?? 0) > 0 ? (int)($service['max_concurrent_customize'] ?? 0) : max(1, (int)($service['max_concurrent'] ?? 1)),
            ]];
        }

        $duration = max(15, (int)($service['duration_minutes'] ?? 60));
        $buffer = max(0, (int)($service['buffer_minutes'] ?? 0));
        $maxConcurrent = max(1, (int)($service['max_concurrent'] ?? 1));
        $generatedSlots = $this->buildSlots($date, $hours['open_time'], $hours['close_time'], $duration, $buffer);
        $storedSlots = $this->storedSlotsForDate($serviceId, $date);
        $slots = [];

        foreach ($generatedSlots as $slot) {
            if (!$this->isFutureSlot($date, $slot['start_time'])) {
                continue;
            }

            $stored = $storedSlots[$slot['start_time']] ?? null;
            $capacity = $stored ? (int)$stored['max_concurrent'] : $maxConcurrent;
            $confirmed = $stored ? (int)$stored['confirmed_count'] : 0;
            $status = $stored['status'] ?? 'available';
            $available = max(0, $capacity - $confirmed);

            // Per-pool remaining
            $pkgCap = $stored ? (int)($stored['max_concurrent_package'] ?? 0) : 0;
            $pkgConfirmed = $stored ? (int)($stored['confirmed_package_count'] ?? 0) : 0;
            $customCap = $stored ? (int)($stored['max_concurrent_customize'] ?? 0) : 0;
            $customConfirmed = $stored ? (int)($stored['confirmed_customize_count'] ?? 0) : 0;
            $availPackage = $pkgCap > 0 ? max(0, $pkgCap - $pkgConfirmed) : $available;
            $availCustomize = $customCap > 0 ? max(0, $customCap - $customConfirmed) : $available;

            if ($status !== 'available' || $available <= 0) {
                continue;
            }

            $slots[] = [
                'slot_id' => $stored['id'] ?? null,
                'start_time' => $slot['start_time'],
                'end_time' => $slot['end_time'],
                'display' => $this->formatTimeRange($slot['start_time'], $slot['end_time']),
                'available' => $available,
                'available_package' => $availPackage,
                'available_customize' => $availCustomize,
            ];
        }

        return $slots;
    }

    public function findAvailableSlotForServiceDate(int $serviceId, string $date, string $startTime, string $endTime): array|false
    {
        $startTime = $this->normalizeTime($startTime);
        $endTime = $this->normalizeTime($endTime);

        foreach ($this->getAvailableSlotsForServiceDate($serviceId, $date) as $slot) {
            if ($this->normalizeTime($slot['start_time']) === $startTime && $this->normalizeTime($slot['end_time']) === $endTime) {
                return $slot;
            }
        }

        return false;
    }

    public function getServiceMinLeadDays(int $serviceId): int
    {
        $this->db->dbquery(
            "SELECT min_lead_days
             FROM services
             WHERE id = :sid AND is_active = 1
             LIMIT 1"
        );
        $this->db->dbbind(':sid', $serviceId, PDO::PARAM_INT);
        $service = $this->db->getsingledata();

        return max(0, (int)($service['min_lead_days'] ?? 0));
    }

    public function getMinLeadDaysForSelection(int $serviceId, ?int $venueRoomId = null): int
    {
        if ($venueRoomId && $venueRoomId > 0) {
            $this->db->dbquery(
                "SELECT COALESCE(vr.min_lead_days, s.min_lead_days, 0) AS min_lead_days
                 FROM venue_rooms vr
                 INNER JOIN venues v ON v.id = vr.venue_id
                 INNER JOIN services s ON s.id = v.service_id
                 WHERE s.id = :sid
                   AND vr.id = :vrid
                   AND s.is_active = 1
                 LIMIT 1"
            );
            $this->db->dbbind(':sid', $serviceId, PDO::PARAM_INT);
            $this->db->dbbind(':vrid', $venueRoomId, PDO::PARAM_INT);
            $row = $this->db->getsingledata();
            if ($row) {
                return max(0, (int)($row['min_lead_days'] ?? 0));
            }
        }

        return $this->getServiceMinLeadDays($serviceId);
    }

    public function isDateAllowedByLeadTime(int $serviceId, string $date, ?int $venueRoomId = null): bool
    {
        $date = $this->normalizeDate($date);
        if (!$date) {
            return false;
        }

        $minLeadDays = $this->getMinLeadDaysForSelection($serviceId, $venueRoomId);
        $earliestDate = date('Y-m-d', strtotime('+' . $minLeadDays . ' days'));

        return strtotime($date) >= strtotime($earliestDate);
    }

    private function hoursForServiceDate(int $serviceId, string $date): array|false
    {
        $this->db->dbquery(
            "SELECT type, open_time, close_time
             FROM service_availability
             WHERE service_id = :sid AND date = :sdate
             LIMIT 1"
        );
        $this->db->dbbind(':sid', $serviceId, PDO::PARAM_INT);
        $this->db->dbbind(':sdate', $date);
        $override = $this->db->getsingledata();

        if ($override) {
            if (($override['type'] ?? '') === 'unavailable') {
                return false;
            }
            return [
                'open_time' => $override['open_time'] ?: '09:00:00',
                'close_time' => $override['close_time'] ?: '17:00:00',
            ];
        }

        $this->db->dbquery(
            "SELECT open_time, close_time
             FROM service_schedules
             WHERE service_id = :sid
               AND day_of_week = :dow
               AND is_available = 1
               AND open_time < close_time
             LIMIT 1"
        );
        $this->db->dbbind(':sid', $serviceId, PDO::PARAM_INT);
        $this->db->dbbind(':dow', (int)date('N', strtotime($date)), PDO::PARAM_INT);
        $schedule = $this->db->getsingledata();

        return $schedule ?: false;
    }

    private function storedSlotsForDate(int $serviceId, string $date): array
    {
        $this->db->dbquery(
            "SELECT id, start_time, end_time,
                    confirmed_count, confirmed_package_count, confirmed_customize_count,
                    max_concurrent, max_concurrent_package, max_concurrent_customize,
                    status
             FROM service_time_slots
             WHERE service_id = :sid AND date = :sdate"
        );
        $this->db->dbbind(':sid', $serviceId, PDO::PARAM_INT);
        $this->db->dbbind(':sdate', $date);

        $slots = [];
        foreach ($this->db->getmultidata() as $slot) {
            $slots[$slot['start_time']] = $slot;
        }

        return $slots;
    }

    private function buildSlots(string $date, string $openTime, string $closeTime, int $durationMinutes, int $bufferMinutes): array
    {
        $slots = [];
        $cursor = strtotime($date . ' ' . $openTime);
        $close = strtotime($date . ' ' . $closeTime);
        $step = ($durationMinutes + $bufferMinutes) * 60;
        $duration = $durationMinutes * 60;

        if (!$cursor || !$close || $cursor >= $close || $duration <= 0 || $step <= 0) {
            return [];
        }

        while ($cursor + $duration <= $close) {
            $slots[] = [
                'start_time' => date('H:i:s', $cursor),
                'end_time' => date('H:i:s', $cursor + $duration),
            ];
            $cursor += $step;
        }

        return $slots;
    }

    private function isFutureSlot(string $date, string $startTime): bool
    {
        $slotStart = strtotime($date . ' ' . $startTime);
        return $slotStart !== false && $slotStart > time();
    }

    private function normalizeDate(string $date): ?string
    {
        $timestamp = strtotime($date);
        return $timestamp ? date('Y-m-d', $timestamp) : null;
    }

    private function normalizeTime(string $time): ?string
    {
        $timestamp = strtotime($time);
        return $timestamp ? date('H:i:s', $timestamp) : null;
    }

    private function formatTimeRange(string $startTime, string $endTime): string
    {
        return date('g:i A', strtotime($startTime)) . ' - ' . date('g:i A', strtotime($endTime));
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

    private function hasServiceDefaultTimeColumns(): bool
    {
        if ($this->serviceDefaultTimeColumns !== null) {
            return $this->serviceDefaultTimeColumns;
        }
        $this->db->dbquery(
            'SELECT COUNT(*) AS total
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = "services"
               AND COLUMN_NAME IN ("default_start_time", "default_end_time")'
        );
        $row = $this->db->getsingledata();
        $this->serviceDefaultTimeColumns = (int)($row['total'] ?? 0) >= 2;
        return $this->serviceDefaultTimeColumns;
    }

    /**
     * Build the automatically managed service timeline for a platform package.
     * Service weekly hours take priority, followed by supplier defaults and then
     * the configured category fallback window.
     */
    public function getPackageEventSchedule(int $packageId, string $eventDate): array
    {
        if ($packageId <= 0 || !DateTimeImmutable::createFromFormat('!Y-m-d', $eventDate)) {
            return [];
        }

        $defaultTimeSelect = $this->hasServiceDefaultTimeColumns()
            ? 's.default_start_time, s.default_end_time,'
            : 'NULL AS default_start_time, NULL AS default_end_time,';
        $defaultStartOrder = $this->hasServiceDefaultTimeColumns()
            ? 's.default_start_time'
            : 'NULL';

        $this->db->dbquery(
            "SELECT pi.id AS package_item_id,
                    pi.service_id,
                    s.name AS service_name,
                    s.booking_type,
                    COALESCE(vr.min_lead_days, s.min_lead_days, 0) AS min_lead_days,
                    c.id AS category_id,
                    c.name AS category_name,
                    COALESCE(sup.shop_name, 'Golden Promise') AS supplier_name,
                    ss.open_time AS schedule_start_time,
                    ss.close_time AS schedule_end_time,
                    {$defaultTimeSelect}
                    vr.name AS venue_room_name
             FROM package_items pi
             INNER JOIN services s ON s.id = pi.service_id
             LEFT JOIN categories c ON c.id = COALESCE(pi.category_id, s.category_id)
             LEFT JOIN suppliers sup ON sup.supplier_id = COALESCE(pi.default_supplier_id, s.supplier_id)
             LEFT JOIN service_schedules ss
                    ON ss.service_id = s.id
                   AND ss.day_of_week = DAYOFWEEK(:event_date)
                   AND ss.is_available = 1
             LEFT JOIN venue_rooms vr ON vr.id = pi.venue_room_id
             WHERE pi.package_id = :package_id
               AND pi.service_id IS NOT NULL
               AND pi.deleted_at IS NULL
             ORDER BY COALESCE(ss.open_time, {$defaultStartOrder}, '23:59:59') ASC,
                      c.name ASC,
                      s.name ASC"
        );
        $this->db->dbbind(':event_date', $eventDate);
        $this->db->dbbind(':package_id', $packageId, PDO::PARAM_INT);
        $rows = $this->db->getmultidata();

        foreach ($rows as &$row) {
            $categoryId = (int)($row['category_id'] ?? 0);
            $categoryTimes = defined('CATEGORY_DEFAULT_TIMES')
                ? (CATEGORY_DEFAULT_TIMES[$categoryId] ?? null)
                : null;

            $row['start_time'] = $row['schedule_start_time']
                ?: ($row['default_start_time'] ?: ($categoryTimes['start'] ?? '09:00:00'));
            $row['end_time'] = $row['schedule_end_time']
                ?: ($row['default_end_time'] ?: ($categoryTimes['end'] ?? '17:00:00'));
            $row['event_date'] = $eventDate;
        }
        unset($row);
        usort($rows, static fn(array $a, array $b): int =>
            strcmp((string)($a['start_time'] ?? ''), (string)($b['start_time'] ?? ''))
        );

        return $rows;
    }

    /**
     * Get all cart items for a user, joined with service details.
     */
    /**
     * Get all cart items for a user, joined with service details.
     * Includes venue location for auto-fill functionality.
     */
    public function getCartItems(int $userId): array
    {
        $hasVenueRoomColumn = $this->hasCartVenueRoomColumn();
        $hasDefaultTimeColumns = $this->hasServiceDefaultTimeColumns();
        $resolvedTimeSelect = $hasDefaultTimeColumns
            ? "COALESCE(
                    (SELECT ss.open_time FROM service_schedules ss
                     WHERE ss.service_id = s.id
                     AND ss.day_of_week = DAYOFWEEK(ci.selected_date)
                     AND ss.is_available = 1 LIMIT 1),
                    s.default_start_time
                ) AS resolved_start_time,
                COALESCE(
                    (SELECT ss.close_time FROM service_schedules ss
                     WHERE ss.service_id = s.id
                     AND ss.day_of_week = DAYOFWEEK(ci.selected_date)
                     AND ss.is_available = 1 LIMIT 1),
                    s.default_end_time
                ) AS resolved_end_time,"
            : "NULL AS resolved_start_time,
                NULL AS resolved_end_time,";
        $venueRoomSelect = $hasVenueRoomColumn
            ? 'COALESCE(cart_vr.id, selected_vr.id) AS venue_room_id,
                    COALESCE(cart_vr.name, selected_vr.name) AS venue_room_name,
                    COALESCE(cart_vr.capacity, selected_vr.capacity) AS venue_room_capacity,
                    COALESCE(cart_venue.name, selected_venue.name) AS venue_name,'
            : 'selected_vr.id AS venue_room_id,
                    selected_vr.name AS venue_room_name,
                    selected_vr.capacity AS venue_room_capacity,
                    selected_venue.name AS venue_name,';
        $venueRoomJoin = $hasVenueRoomColumn
            ? 'LEFT JOIN venue_rooms cart_vr ON cart_vr.id = ci.venue_room_id
            LEFT JOIN venues cart_venue ON cart_venue.id = cart_vr.venue_id'
            : '';
        $serviceMinLeadSelect = $hasVenueRoomColumn
            ? 'COALESCE(cart_vr.min_lead_days, selected_vr.min_lead_days, s.min_lead_days, 0)'
            : 'COALESCE(selected_vr.min_lead_days, s.min_lead_days, 0)';
        $minLeadSelect = "CASE
            WHEN ci.item_type = 'package' THEN COALESCE(
                (SELECT MAX(COALESCE(package_room.min_lead_days, package_service.min_lead_days, 0))
                 FROM package_items package_item
                 INNER JOIN services package_service ON package_service.id = package_item.service_id
                 LEFT JOIN venue_rooms package_room ON package_room.id = package_item.venue_room_id
                 WHERE package_item.package_id = ci.item_id
                   AND package_item.deleted_at IS NULL),
                0
            )
            ELSE {$serviceMinLeadSelect}
        END";
        $packageParentSelect = $this->hasCartPackageParentColumn()
            ? "ci.package_cart_item_id,
                    parent_package.package_id AS addon_package_id,
                    parent_package.name AS addon_package_name,"
            : "NULL AS package_cart_item_id,
                    NULL AS addon_package_id,
                    NULL AS addon_package_name,";
        $packageParentJoin = $this->hasCartPackageParentColumn()
            ? "LEFT JOIN cart_items parent_ci
                     ON parent_ci.id = ci.package_cart_item_id
                    AND parent_ci.user_id = ci.user_id
                    AND parent_ci.item_type = 'package'
               LEFT JOIN packages parent_package ON parent_package.package_id = parent_ci.item_id"
            : '';

        $this->db->dbquery(
            "SELECT ci.id AS cart_item_id, 
                    ci.item_type, 
                    ci.item_id, 
                    ci.selected_date,
                    CASE
                        WHEN ci.item_type = 'package' THEN COALESCE(p.base_price * 1.05, ci.price)
                        ELSE ci.price
                    END AS cart_price, 
                    ci.slot_id, 
                    ci.start_time, 
                    ci.end_time,
                    {$packageParentSelect}
                    {$venueRoomSelect}
                    {$resolvedTimeSelect}

                    COALESCE(s.name, p.name, sp.name) AS service_name,
                    COALESCE(s.thumbnail_url, p.image_url, sp.thumbnail_url) AS thumbnail_url,
                    COALESCE(s.price_min, p.base_price * 1.05, sp.total_price) AS price_min,
                    COALESCE(s.price_max, p.base_price * 1.05, sp.total_price) AS price_max,
                    COALESCE(s.booking_type, 'fullday') AS booking_type,
                    {$minLeadSelect} AS min_lead_days,

                    COALESCE(sup.shop_name, sp_sup.shop_name, 'Golden Promise') AS supplier_name,
                    COALESCE(sup.supplier_id, sp_sup.supplier_id) AS supplier_id,
                    
                    COALESCE(cat.name, package_cat.name) AS category_name,
                    COALESCE(cat.id, package_cat.id) AS category_id,
                    
                    p.slug AS package_slug,
                    
                    -- Venue location for booking auto-fill
                    v.location AS service_location,
                    s.id AS service_id
                    
            FROM cart_items ci
            LEFT JOIN services s ON ci.item_id = s.id AND ci.item_type = 'service'
            LEFT JOIN venues v ON v.service_id = s.id
            {$venueRoomJoin}
            LEFT JOIN venue_room_availability selected_vra ON selected_vra.id = ci.slot_id
            LEFT JOIN venue_rooms selected_vr ON selected_vr.id = selected_vra.room_id
            LEFT JOIN venues selected_venue ON selected_venue.id = selected_vr.venue_id
            LEFT JOIN packages p ON ci.item_id = p.package_id AND ci.item_type = 'package'
            {$packageParentJoin}
            LEFT JOIN supplier_packages sp ON ci.item_id = sp.id AND ci.item_type = 'supplier_package'
            LEFT JOIN suppliers sup ON s.supplier_id = sup.supplier_id
            LEFT JOIN suppliers sp_sup ON sp.supplier_id = sp_sup.supplier_id
            LEFT JOIN categories cat ON s.category_id = cat.id
            LEFT JOIN categories package_cat ON package_cat.slug = 'package'
            WHERE ci.user_id = :uid
            ORDER BY ci.id DESC"
        );
        $this->db->dbbind(':uid', $userId, PDO::PARAM_INT);
        return $this->db->getmultidata();
    }

    /**
     * Return the services included in every platform package currently in a
     * customer's cart, keyed by the cart item id.
     */
    public function getCartPackageServices(int $userId): array
    {
        $this->db->dbquery(
            "SELECT ci.id AS cart_item_id,
                    pi.id AS package_item_id,
                    pi.service_id,
                    pi.quantity,
                    s.name AS service_name,
                    s.thumbnail_url,
                    c.name AS category_name,
                    COALESCE(default_supplier.shop_name, service_supplier.shop_name, 'Golden Promise') AS supplier_name,
                    vr.name AS venue_room_name,
                    v.name AS venue_name
             FROM cart_items ci
             INNER JOIN package_items pi
                     ON pi.package_id = ci.item_id
                    AND pi.deleted_at IS NULL
             INNER JOIN services s ON s.id = pi.service_id
             LEFT JOIN categories c ON c.id = COALESCE(pi.category_id, s.category_id)
             LEFT JOIN suppliers default_supplier ON default_supplier.supplier_id = pi.default_supplier_id
             LEFT JOIN suppliers service_supplier ON service_supplier.supplier_id = s.supplier_id
             LEFT JOIN venue_rooms vr ON vr.id = pi.venue_room_id
             LEFT JOIN venues v ON v.id = vr.venue_id
             WHERE ci.user_id = :uid
               AND ci.item_type = 'package'
               AND pi.service_id IS NOT NULL
             ORDER BY ci.id DESC, c.name ASC, s.name ASC"
        );
        $this->db->dbbind(':uid', $userId, PDO::PARAM_INT);

        $grouped = [];
        foreach ($this->db->getmultidata() as $service) {
            $grouped[(int)$service['cart_item_id']][] = $service;
        }

        return $grouped;
    }

    /**
     * Get the total number of items in the user's cart.
     */
    public function getCartCount(int $userId): int
    {
        $this->db->dbquery(
            "SELECT COUNT(*) AS cnt FROM cart_items WHERE user_id = :uid"
        );
        $this->db->dbbind(':uid', $userId, PDO::PARAM_INT);
        $row = $this->db->getsingledata();
        return $row ? (int)$row['cnt'] : 0;
    }

    /**
     * Calculate the total price of all items in the cart.
     */
    public function getCartTotal(int $userId): float
    {
        $this->db->dbquery(
            "SELECT COALESCE(SUM(
                CASE
                    WHEN ci.item_type = 'package' THEN COALESCE(p.base_price * 1.05, ci.price, 0)
                    ELSE COALESCE(ci.price, s.price_min, s.price, sp.total_price, 0)
                END
             ), 0) AS total
             FROM cart_items ci
             LEFT JOIN services s ON ci.item_id = s.id AND ci.item_type = 'service'
             LEFT JOIN packages p ON ci.item_id = p.package_id AND ci.item_type = 'package'
             LEFT JOIN supplier_packages sp ON ci.item_id = sp.id AND ci.item_type = 'supplier_package'
             WHERE ci.user_id = :uid"
        );
        $this->db->dbbind(':uid', $userId, PDO::PARAM_INT);
        $row = $this->db->getsingledata();
        return $row ? (float)$row['total'] : 0;
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

}
