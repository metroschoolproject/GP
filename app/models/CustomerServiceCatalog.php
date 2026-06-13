<?php

class CustomerServiceCatalog
{
    private $db;
    private $hasServicePriceRangeColumns = null;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function getServicePageData($filters = [])
    {
        $services = $this->getServices($filters);
        $hasFilters = trim((string)($filters['search'] ?? '')) !== ''
            || !in_array(($filters['category'] ?? 'all'), ['', 'all'], true)
            || trim((string)($filters['date'] ?? '')) !== ''
            || trim((string)($filters['price_min'] ?? '')) !== ''
            || trim((string)($filters['price_max'] ?? '')) !== '';

        return [
            'services' => $services,
            'categories' => $this->getCategories($filters),
            'featured' => $hasFilters ? array_slice($services, 0, 3) : $this->getFeaturedServices(),
        ];
    }

    public function getCategories($filters = [])
    {
        $conditions = [
            'services.is_active = 1',
            'suppliers.deleted_at IS NULL',
            'suppliers.is_available = 1',
            'suppliers.status IN ("approved", "verified")',
            'suppliers.payment_status = "paid"',
            $this->publishedServiceReadyCondition(),
        ];
        $bindings = [];

        $date = trim((string)($filters['date'] ?? ''));
        if ($date !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $dayOfWeek = (int)date('N', strtotime($date));
            if ($dayOfWeek >= 1 && $dayOfWeek <= 7) {
                $conditions[] = '(
                    LOWER(COALESCE(categories.name, "")) = "venue"
                    OR EXISTS (
                        SELECT 1
                        FROM service_schedules
                        WHERE service_schedules.service_id = services.id
                          AND service_schedules.day_of_week = :cat_wedding_day_of_week
                          AND service_schedules.is_available = 1
                          AND service_schedules.open_time < service_schedules.close_time
                        LIMIT 1
                    )
                )';
                $bindings[':cat_wedding_day_of_week'] = $dayOfWeek;
            }
        }

        $sql = 'SELECT categories.id, categories.name, categories.slug, COUNT(services.id) AS service_count
                FROM categories
                INNER JOIN services ON services.category_id = categories.id
                INNER JOIN suppliers ON suppliers.supplier_id = services.supplier_id
                WHERE ' . implode(' AND ', $conditions) . '
                GROUP BY categories.id, categories.name, categories.slug
                ORDER BY categories.name ASC';

        $this->db->dbquery($sql);
        foreach ($bindings as $param => $value) {
            $this->db->dbbind($param, $value);
        }

        return $this->db->getmultidata();
    }

    public function getFeaturedServices($limit = 3)
    {
        $priceRangeFields = $this->servicePriceRangeSelectFields();
        $this->db->dbquery(
            'SELECT services.id,
                    services.name,
                    services.description,
                    services.price,
                    ' . $priceRangeFields . '
                    services.thumbnail_url,
                    services.booking_type,
                    services.duration_minutes,
                    services.pricing_unit,
                    categories.name AS category,
                    suppliers.shop_name AS supplier_name,
                    suppliers.description AS supplier_description,
                    COALESCE(review_stats.avg_rating, 0) AS avg_rating,
                    COALESCE(review_stats.review_count, 0) AS review_count
             FROM services
             INNER JOIN suppliers ON suppliers.supplier_id = services.supplier_id
             LEFT JOIN categories ON categories.id = services.category_id
             LEFT JOIN (
                SELECT service_id, AVG(rating) AS avg_rating, COUNT(*) AS review_count
                FROM reviews
                WHERE service_id IS NOT NULL
                GROUP BY service_id
             ) review_stats ON review_stats.service_id = services.id
             WHERE services.is_active = 1
               AND suppliers.deleted_at IS NULL
               AND suppliers.is_available = 1
               AND suppliers.status IN ("approved", "verified")
               AND suppliers.payment_status = "paid"
               AND ' . $this->publishedServiceReadyCondition() . '
             ORDER BY review_stats.avg_rating DESC, services.created_at DESC, services.id DESC
             LIMIT :limit'
        );
        $this->db->dbbind(':limit', (int)$limit);

        return array_map([$this, 'formatService'], $this->db->getmultidata());
    }

    public function getServices($filters = [])
    {
        $priceRangeFields = $this->servicePriceRangeSelectFields();
        $conditions = [
            'services.is_active = 1',
            'suppliers.deleted_at IS NULL',
            'suppliers.is_available = 1',
            'suppliers.status IN ("approved", "verified")',
            'suppliers.payment_status = "paid"',
            $this->publishedServiceReadyCondition(),
        ];
        $bindings = [];

        $search = trim((string)($filters['search'] ?? ''));
        if ($search !== '') {
            $conditions[] = '(services.name LIKE :search OR services.description LIKE :search OR suppliers.shop_name LIKE :search OR categories.name LIKE :search)';
            $bindings[':search'] = '%' . $search . '%';
        }

        $category = trim((string)($filters['category'] ?? ''));
        if ($category !== '' && $category !== 'all') {
            $conditions[] = '(categories.slug = :category OR categories.name = :category)';
            $bindings[':category'] = $category;
        }

        $excludeId = (int)($filters['exclude_id'] ?? 0);
        if ($excludeId > 0) {
            $conditions[] = 'services.id <> :exclude_id';
            $bindings[':exclude_id'] = $excludeId;
        }

        $date = trim((string)($filters['date'] ?? ''));
        if ($date !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $dayOfWeek = (int)date('N', strtotime($date));
            if ($dayOfWeek >= 1 && $dayOfWeek <= 7) {
                $conditions[] = '(
                    LOWER(COALESCE(categories.name, "")) = "venue"
                    OR EXISTS (
                        SELECT 1
                        FROM service_schedules
                        WHERE service_schedules.service_id = services.id
                          AND service_schedules.day_of_week = :wedding_day_of_week
                          AND service_schedules.is_available = 1
                          AND service_schedules.open_time < service_schedules.close_time
                        LIMIT 1
                    )
                )';
                $bindings[':wedding_day_of_week'] = $dayOfWeek;
            }
        }

        $priceMin = $this->normalizePriceFilter($filters['price_min'] ?? '');
        $priceMax = $this->normalizePriceFilter($filters['price_max'] ?? '');
        if ($priceMin !== null || $priceMax !== null) {
            $servicePriceMin = $this->servicePriceMinExpression();
            $servicePriceMax = $this->servicePriceMaxExpression();

            if ($priceMin !== null) {
                $conditions[] = $servicePriceMax . ' >= :price_min';
                $bindings[':price_min'] = number_format($priceMin, 2, '.', '');
            }

            if ($priceMax !== null) {
                $conditions[] = $servicePriceMin . ' <= :price_max';
                $bindings[':price_max'] = number_format($priceMax, 2, '.', '');
            }
        }

        $sort = $filters['sort'] ?? 'featured';
        $orderBy = 'review_stats.avg_rating DESC, services.created_at DESC, services.id DESC';
        if ($sort === 'price_low') {
            $orderBy = $this->servicePriceMinExpression() . ' ASC, services.created_at DESC';
        } elseif ($sort === 'price_high') {
            $orderBy = $this->servicePriceMaxExpression() . ' DESC, services.created_at DESC';
        } elseif ($sort === 'newest') {
            $orderBy = 'services.created_at DESC, services.id DESC';
        } elseif ($sort === 'rating') {
            $orderBy = 'review_stats.avg_rating DESC, review_stats.review_count DESC, services.created_at DESC';
        }

        $limit = max(1, min(60, (int)($filters['limit'] ?? 60)));

        $sql = 'SELECT services.id,
                       services.name,
                       services.description,
                       services.price,
                       ' . $priceRangeFields . '
                       services.thumbnail_url,
                       services.booking_type,
                       services.duration_minutes,
                       services.pricing_unit,
                       categories.name AS category,
                       categories.slug AS category_slug,
                       suppliers.supplier_id,
                       suppliers.shop_name AS supplier_name,
                       suppliers.description AS supplier_description,
                       COALESCE(review_stats.avg_rating, 0) AS avg_rating,
                       COALESCE(review_stats.review_count, 0) AS review_count
                FROM services
                INNER JOIN suppliers ON suppliers.supplier_id = services.supplier_id
                LEFT JOIN categories ON categories.id = services.category_id
                LEFT JOIN (
                    SELECT service_id, AVG(rating) AS avg_rating, COUNT(*) AS review_count
                    FROM reviews
                    WHERE service_id IS NOT NULL
                    GROUP BY service_id
                ) review_stats ON review_stats.service_id = services.id
                WHERE ' . implode(' AND ', $conditions) . '
                ORDER BY ' . $orderBy . '
                LIMIT :limit';

        $this->db->dbquery($sql);
        foreach ($bindings as $param => $value) {
            $this->db->dbbind($param, $value);
        }
        $this->db->dbbind(':limit', $limit);

        return array_map([$this, 'formatService'], $this->db->getmultidata());
    }

    public function getServiceDetail($serviceId, $selectedDate = '')
    {
        $priceRangeFields = $this->servicePriceRangeSelectFields();
        $this->db->dbquery(
            'SELECT services.id,
                    services.name,
                    services.description,
                    services.price,
                    ' . $priceRangeFields . '
                    services.thumbnail_url,
                    services.booking_type,
                    services.duration_minutes,
                    services.buffer_minutes,
                    services.max_concurrent,
                    services.pricing_unit,
                    categories.name AS category,
                    categories.slug AS category_slug,
                    suppliers.supplier_id,
                    suppliers.shop_name AS supplier_name,
                    suppliers.description AS supplier_description,
                    suppliers.verify_url AS supplier_url,
                    COALESCE(review_stats.avg_rating, 0) AS avg_rating,
                    COALESCE(review_stats.review_count, 0) AS review_count
             FROM services
             INNER JOIN suppliers ON suppliers.supplier_id = services.supplier_id
             LEFT JOIN categories ON categories.id = services.category_id
             LEFT JOIN (
                SELECT service_id, AVG(rating) AS avg_rating, COUNT(*) AS review_count
                FROM reviews
                WHERE service_id IS NOT NULL
                GROUP BY service_id
             ) review_stats ON review_stats.service_id = services.id
             WHERE services.id = :service_id
               AND services.is_active = 1
               AND suppliers.deleted_at IS NULL
               AND suppliers.is_available = 1
               AND suppliers.status IN ("approved", "verified")
               AND suppliers.payment_status = "paid"
               AND ' . $this->publishedServiceReadyCondition() . '
             LIMIT 1'
        );
        $this->db->dbbind(':service_id', (int)$serviceId);
        $service = $this->db->getsingledata();

        if (!$service) {
            return null;
        }

        $formatted = $this->formatService($service);
        $formatted['buffer_minutes'] = (int)($service['buffer_minutes'] ?? 0);
        $formatted['max_concurrent'] = (int)($service['max_concurrent'] ?? 1);
        $formatted['supplier_url'] = $service['supplier_url'] ?? '';
        $formatted['media'] = $this->getServiceMedia($serviceId, $formatted['image']);
        $selectedDate = $this->normalizeDate($selectedDate);
        $formatted['selected_date'] = $selectedDate ?: '';
        $formatted['venue_rooms'] = strtolower((string)$formatted['category']) === 'venue' ? $this->getVenueRooms($serviceId, $selectedDate) : [];
        $formatted['availability'] = $this->getServiceAvailability($serviceId, $formatted, $selectedDate);
        $formatted['reviews'] = $this->getServiceReviews($serviceId);
        $formatted['related'] = $this->getRelatedServices($serviceId, $formatted['category_slug']);

        return $formatted;
    }

    private function getServiceMedia($serviceId, $thumbnailUrl = '')
    {
        $this->db->dbquery(
            'SELECT file_url, type
             FROM service_media
             WHERE service_id = :service_id
             ORDER BY id ASC'
        );
        $this->db->dbbind(':service_id', (int)$serviceId);
        $media = $this->db->getmultidata();

        if ($thumbnailUrl !== '') {
            array_unshift($media, ['file_url' => $thumbnailUrl, 'type' => 'image']);
        }

        $seen = [];
        return array_values(array_filter($media, function ($item) use (&$seen) {
            $url = trim((string)($item['file_url'] ?? ''));
            if ($url === '' || isset($seen[$url])) {
                return false;
            }
            $seen[$url] = true;
            return true;
        }));
    }

    private function publishedServiceReadyCondition()
    {
        return 'TRIM(COALESCE(services.name, "")) <> ""
            AND TRIM(COALESCE(services.description, "")) <> ""
            AND services.price > 0
            AND (
                TRIM(COALESCE(services.thumbnail_url, "")) <> ""
                OR EXISTS (
                    SELECT 1
                    FROM service_media
                    WHERE service_media.service_id = services.id
                    LIMIT 1
                )
            )
            AND (
                LOWER(COALESCE(categories.name, "")) = "venue"
                OR EXISTS (
                    SELECT 1
                    FROM service_schedules
                    WHERE service_schedules.service_id = services.id
                      AND service_schedules.is_available = 1
                      AND service_schedules.open_time < service_schedules.close_time
                    LIMIT 1
                )
            )
            AND (
                LOWER(COALESCE(categories.name, "")) <> "venue"
                OR EXISTS (
                    SELECT 1
                    FROM venues
                    INNER JOIN venue_rooms ON venue_rooms.venue_id = venues.id
                    WHERE venues.service_id = services.id
                    LIMIT 1
                )
            )';
    }

    private function getVenueRooms($serviceId, $selectedDate = '')
    {
        $selectedDate = $this->normalizeDate($selectedDate);
        $serviceClosed = false;
        if ($selectedDate) {
            $this->db->dbquery(
                'SELECT type
                 FROM service_availability
                 WHERE service_id = :service_id
                   AND date = :selected_date
                 LIMIT 1'
            );
            $this->db->dbbind(':service_id', (int)$serviceId);
            $this->db->dbbind(':selected_date', $selectedDate);
            $serviceOverride = $this->db->getsingledata();
            $serviceClosed = ($serviceOverride['type'] ?? '') === 'unavailable';
        }

        $bookingCountSelect = $selectedDate
            ? ', (
                    SELECT COUNT(*)
                      FROM booking_items
                      WHERE booking_items.venue_room_id = venue_rooms.id
                      AND DATE(booking_items.booking_date) = :selected_date_booking
                      AND COALESCE(booking_items.status, "") <> "cancelled"
                ) AS booking_count'
            : ', 0 AS booking_count';
        $roomDateJoin = $selectedDate
            ? 'LEFT JOIN venue_room_availability AS selected_room_availability
                    ON selected_room_availability.room_id = venue_rooms.id
                   AND selected_room_availability.date = :selected_date_room'
            : 'LEFT JOIN venue_room_availability AS selected_room_availability
                    ON selected_room_availability.room_id = venue_rooms.id
                   AND selected_room_availability.date IS NULL
                   AND 1 = 0';

        $this->db->dbquery(
            'SELECT venue_rooms.id,
                    venue_rooms.name,
                    venue_rooms.capacity,
                    venue_rooms.price,
                    COALESCE(selected_room_availability.start_time, default_room_availability.start_time) AS start_time,
                    COALESCE(selected_room_availability.end_time, default_room_availability.end_time) AS end_time,
                    selected_room_availability.id AS selected_availability_id,
                    selected_room_availability.is_available AS selected_is_available,
                    default_room_availability.is_available AS default_is_available,
                    venues.name AS venue_name,
                    venues.location AS venue_location
                    ' . $bookingCountSelect . '
             FROM venues
             INNER JOIN venue_rooms ON venue_rooms.venue_id = venues.id
             LEFT JOIN venue_room_availability AS default_room_availability
                    ON default_room_availability.room_id = venue_rooms.id
                   AND default_room_availability.date IS NULL
             ' . $roomDateJoin . '
             WHERE venues.service_id = :service_id
             GROUP BY venue_rooms.id,
                      venue_rooms.name,
                      venue_rooms.capacity,
                      venue_rooms.price,
                      selected_room_availability.start_time,
                      selected_room_availability.end_time,
                      selected_room_availability.id,
                      selected_room_availability.is_available,
                      default_room_availability.start_time,
                      default_room_availability.end_time,
                      default_room_availability.is_available,
                      venues.name,
                      venues.location
             ORDER BY venue_rooms.id ASC'
        );
        $this->db->dbbind(':service_id', (int)$serviceId);
        if ($selectedDate) {
            $this->db->dbbind(':selected_date_booking', $selectedDate);
            $this->db->dbbind(':selected_date_room', $selectedDate);
        }

        return array_map(function ($room) use ($selectedDate, $serviceClosed) {
            $bookingCount = (int)($room['booking_count'] ?? 0);
            $hasSelectedRoomRule = $selectedDate && !empty($room['selected_availability_id']);
            $roomOpen = $hasSelectedRoomRule
                ? !empty($room['selected_is_available'])
                : !empty($room['default_is_available']);
            $availableOnDate = $selectedDate
                ? (!$serviceClosed && $roomOpen && $bookingCount === 0)
                : false;

            return [
                'id' => (int)$room['id'],
                'name' => $room['name'] ?? '',
                'capacity' => (int)($room['capacity'] ?? 1),
                'price' => (float)($room['price'] ?? 0),
                'start_time' => $room['start_time'] ?? '09:00:00',
                'end_time' => $room['end_time'] ?? '17:00:00',
                'venue_name' => $room['venue_name'] ?? '',
                'venue_location' => $room['venue_location'] ?? '',
                'booking_count' => $bookingCount,
                'is_available_on_date' => $availableOnDate,
                'room_closed_on_date' => $selectedDate && !$roomOpen,
                'service_closed_on_date' => $serviceClosed,
            ];
        }, $this->db->getmultidata());
    }

    private function getServiceAvailability($serviceId, $service, $selectedDate = '')
    {
        $weekly = $this->getWeeklySchedule($serviceId);
        $overrides = $this->getDateOverrides($serviceId);
        $selectedDate = $this->normalizeDate($selectedDate);
        $selected = $selectedDate ? $this->availabilityForDate($serviceId, $service, $weekly, $overrides, $selectedDate) : null;
        $upcoming = $this->getUpcomingAvailability($serviceId, $service, $weekly, $overrides, $selectedDate);

        return [
            'weekly' => array_values($weekly),
            'overrides' => array_values($overrides),
            'selected' => $selected,
            'upcoming' => $upcoming,
        ];
    }

    private function getWeeklySchedule($serviceId)
    {
        $this->db->dbquery(
            'SELECT day_of_week, open_time, close_time, is_available
             FROM service_schedules
             WHERE service_id = :service_id
             ORDER BY day_of_week ASC'
        );
        $this->db->dbbind(':service_id', (int)$serviceId);

        $weekly = [];
        foreach ($this->db->getmultidata() as $row) {
            $weekly[(int)$row['day_of_week']] = [
                'day_of_week' => (int)$row['day_of_week'],
                'open_time' => $row['open_time'],
                'close_time' => $row['close_time'],
                'is_available' => (int)$row['is_available'],
            ];
        }

        return $weekly;
    }

    private function getDateOverrides($serviceId)
    {
        $this->db->dbquery(
            'SELECT id, date, type, open_time, close_time, reason
             FROM service_availability
             WHERE service_id = :service_id
               AND date >= CURDATE()
             ORDER BY date ASC
             LIMIT 20'
        );
        $this->db->dbbind(':service_id', (int)$serviceId);

        $overrides = [];
        foreach ($this->db->getmultidata() as $row) {
            $overrides[$row['date']] = $row;
        }

        return $overrides;
    }

    private function getUpcomingAvailability($serviceId, $service, $weekly, $overrides, $selectedDate = '')
    {
        $days = [];
        $seen = [];
        $today = new DateTimeImmutable('today');
        $selectedDate = $this->normalizeDate($selectedDate);

        if ($selectedDate) {
            $anchor = DateTimeImmutable::createFromFormat('!Y-m-d', $selectedDate);
            if ($anchor) {
                for ($i = 0; $i < 7; $i++) {
                    $day = $anchor->modify('+' . $i . ' days');
                    if ($day < $today) {
                        continue;
                    }

                    $dateValue = $day->format('Y-m-d');
                    if (isset($seen[$dateValue])) {
                        continue;
                    }

                    $availability = $this->availabilityForDate($serviceId, $service, $weekly, $overrides, $dateValue);
                    if (!$availability || empty($availability['slots'])) {
                        continue;
                    }

                    $days[] = $availability;
                    $seen[$dateValue] = true;
                }
            }

            return $days;
        }

        for ($i = 0; $i < 28 && count($days) < 8; $i++) {
            $day = $today->modify('+' . $i . ' days');
            $dateValue = $day->format('Y-m-d');

            $availability = $this->availabilityForDate($serviceId, $service, $weekly, $overrides, $dateValue);
            if (!$availability || empty($availability['slots'])) {
                continue;
            }

            $days[] = $availability;
            $seen[$dateValue] = true;
        }

        return $days;
    }

    private function availabilityForDate($serviceId, $service, $weekly, $overrides, $dateValue)
    {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $dateValue);
        if (!$date) {
            return null;
        }

        $hours = $this->hoursForDate($dateValue, $weekly, $overrides);
        $slots = [];
        $status = 'Unavailable';

        if (!empty($hours['is_available'])) {
            $duration = max(15, (int)($service['duration_minutes'] ?? 60));
            $buffer = max(0, (int)($service['buffer_minutes'] ?? 0));
            $bookingType = ($service['booking_type'] ?? 'fullday') === 'slot' ? 'slot' : 'fullday';
            $slots = $bookingType === 'slot'
                ? $this->availableSlotsForDate($serviceId, $dateValue, $hours['open_time'], $hours['close_time'], $duration, $buffer, (int)($service['max_concurrent'] ?? 1))
                : [[
                    'start_time' => $hours['open_time'],
                    'end_time' => $hours['close_time'],
                    'label' => $this->formatTimeRange($hours['open_time'], $hours['close_time']),
                    'remaining' => max(1, (int)($service['max_concurrent'] ?? 1)),
                ]];
            $status = $hours['source'] === 'override' ? 'Custom hours' : 'Available';
        }

        if (empty($slots) && !empty($hours['is_available'])) {
            $status = 'Booked';
        }

        return [
            'date' => $dateValue,
            'day_label' => $date->format('D, M j'),
            'status' => $status,
            'reason' => $hours['reason'] ?? '',
            'is_selected_date' => ($service['selected_date'] ?? '') === $dateValue,
            'booking_type' => $bookingType ?? ($service['booking_type'] ?? 'fullday'),
            'slots' => array_slice($slots, 0, 6),
        ];
    }

    private function hoursForDate($date, $weekly, $overrides)
    {
        if (isset($overrides[$date])) {
            $override = $overrides[$date];

            if ($override['type'] === 'unavailable') {
                return ['is_available' => false, 'reason' => $override['reason'] ?? 'Unavailable', 'source' => 'override'];
            }

            if (in_array($override['type'], ['available', 'custom_hours'], true)) {
                return [
                    'is_available' => true,
                    'open_time' => $override['open_time'] ?: '09:00:00',
                    'close_time' => $override['close_time'] ?: '17:00:00',
                    'reason' => $override['reason'] ?? '',
                    'source' => 'override',
                ];
            }
        }

        $dayOfWeek = (int)date('N', strtotime($date));
        $schedule = $weekly[$dayOfWeek] ?? null;

        if (!$schedule || empty($schedule['is_available'])) {
            return ['is_available' => false, 'source' => 'weekly'];
        }

        return [
            'is_available' => true,
            'open_time' => $schedule['open_time'],
            'close_time' => $schedule['close_time'],
            'source' => 'weekly',
        ];
    }

    private function availableSlotsForDate($serviceId, $date, $openTime, $closeTime, $durationMinutes, $bufferMinutes, $maxConcurrent)
    {
        $slots = $this->buildSlots($date, $openTime, $closeTime, $durationMinutes, $bufferMinutes);
        $stored = $this->storedSlotsForDate($serviceId, $date);

        return array_values(array_filter(array_map(function ($slot) use ($stored, $maxConcurrent) {
            $storedSlot = $stored[$slot['start_time']] ?? null;
            $capacity = $storedSlot ? (int)$storedSlot['max_concurrent'] : $maxConcurrent;
            $confirmed = $storedSlot ? (int)$storedSlot['confirmed_count'] : 0;
            $status = $storedSlot['status'] ?? 'available';
            $remaining = max(0, $capacity - $confirmed);

            if ($status !== 'available' || $remaining <= 0) {
                return null;
            }

            return [
                'start_time' => $slot['start_time'],
                'end_time' => $slot['end_time'],
                'label' => $this->formatTimeRange($slot['start_time'], $slot['end_time']),
                'remaining' => $remaining,
            ];
        }, $slots)));
    }

    private function storedSlotsForDate($serviceId, $date)
    {
        $this->db->dbquery(
            'SELECT start_time, end_time, confirmed_count, max_concurrent, status
             FROM service_time_slots
             WHERE service_id = :service_id
               AND date = :date'
        );
        $this->db->dbbind(':service_id', (int)$serviceId);
        $this->db->dbbind(':date', $date);

        $slots = [];
        foreach ($this->db->getmultidata() as $slot) {
            $slots[$slot['start_time']] = $slot;
        }

        return $slots;
    }

    private function getServiceReviews($serviceId, $limit = 4)
    {
        $this->db->dbquery(
            'SELECT rating, comment, created_at
             FROM reviews
             WHERE service_id = :service_id
             ORDER BY created_at DESC
             LIMIT :limit'
        );
        $this->db->dbbind(':service_id', (int)$serviceId);
        $this->db->dbbind(':limit', (int)$limit);

        return $this->db->getmultidata();
    }

    private function getRelatedServices($serviceId, $categorySlug, $limit = 3)
    {
        if (!$categorySlug) {
            return [];
        }

        return $this->getServices([
            'category' => $categorySlug,
            'exclude_id' => (int)$serviceId,
            'limit' => (int)$limit,
        ]);
    }

    private function buildSlots($date, $openTime, $closeTime, $durationMinutes, $bufferMinutes)
    {
        $slots = [];
        $start = strtotime($date . ' ' . $openTime);
        $end = strtotime($date . ' ' . $closeTime);
        $step = ($durationMinutes + $bufferMinutes) * 60;
        $duration = $durationMinutes * 60;

        if (!$start || !$end || $start >= $end || $duration <= 0 || $step <= 0) {
            return [];
        }

        while ($start + $duration <= $end) {
            $slots[] = [
                'start_time' => date('H:i:s', $start),
                'end_time' => date('H:i:s', $start + $duration),
            ];
            $start += $step;
        }

        return $slots;
    }

    private function formatTimeRange($startTime, $endTime)
    {
        return date('g:i A', strtotime($startTime)) . ' - ' . date('g:i A', strtotime($endTime));
    }

    private function normalizeDate($date)
    {
        $date = trim((string)$date);
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return '';
        }

        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        return $parsed && $parsed->format('Y-m-d') === $date ? $date : '';
    }

    private function formatService($service)
    {
        $priceMin = (float)($service['price_min'] ?? $service['price'] ?? 0);
        $priceMax = max($priceMin, (float)($service['price_max'] ?? $priceMin));

        return [
            'id' => (int)$service['id'],
            'supplier_id' => (int)($service['supplier_id'] ?? 0),
            'name' => $service['name'] ?? '',
            'description' => $service['description'] ?? '',
            'price' => (float)($service['price'] ?? $priceMin),
            'price_min' => $priceMin,
            'price_max' => $priceMax,
            'image' => $service['thumbnail_url'] ?? '',
            'category' => $service['category'] ?: 'Wedding Service',
            'category_slug' => $service['category_slug'] ?? '',
            'supplier_name' => $service['supplier_name'] ?? 'Golden Promise supplier',
            'supplier_description' => $service['supplier_description'] ?? '',
            'rating' => round((float)($service['avg_rating'] ?? 0), 1),
            'review_count' => (int)($service['review_count'] ?? 0),
            'booking_type' => $service['booking_type'] ?? 'fullday',
            'duration_minutes' => (int)($service['duration_minutes'] ?? 0),
            'pricing_unit' => $service['pricing_unit'] ?? 'per_session',
        ];
    }

    private function servicePriceRangeSelectFields()
    {
        return $this->hasServicePriceRangeColumns()
            ? 'services.price_min, services.price_max,'
            : 'services.price AS price_min, services.price AS price_max,';
    }

    private function servicePriceMinExpression()
    {
        return $this->hasServicePriceRangeColumns()
            ? 'COALESCE(services.price_min, services.price)'
            : 'services.price';
    }

    private function servicePriceMaxExpression()
    {
        return $this->hasServicePriceRangeColumns()
            ? 'COALESCE(services.price_max, services.price_min, services.price)'
            : 'services.price';
    }

    private function normalizePriceFilter($price)
    {
        $price = trim((string)$price);
        if ($price === '' || !is_numeric($price)) {
            return null;
        }

        return max(0, (float)$price);
    }

    private function hasServicePriceRangeColumns()
    {
        if ($this->hasServicePriceRangeColumns !== null) {
            return $this->hasServicePriceRangeColumns;
        }

        $this->db->dbquery(
            'SELECT COUNT(*) AS total
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = "services"
               AND COLUMN_NAME IN ("price_min", "price_max")'
        );
        $row = $this->db->getsingledata();
        $this->hasServicePriceRangeColumns = (int)($row['total'] ?? 0) >= 2;

        return $this->hasServicePriceRangeColumns;
    }
}
