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
        return [
            'services' => $this->getServices($filters),
            'categories' => $this->getCategories(),
            'featured' => $this->getFeaturedServices(),
        ];
    }

    public function getCategories()
    {
        $this->db->dbquery(
            'SELECT categories.id, categories.name, categories.slug, COUNT(services.id) AS service_count
             FROM categories
             INNER JOIN services ON services.category_id = categories.id
             INNER JOIN suppliers ON suppliers.supplier_id = services.supplier_id
             WHERE services.is_active = 1
               AND suppliers.deleted_at IS NULL
               AND suppliers.is_available = 1
               AND suppliers.status IN ("approved", "verified")
               AND suppliers.payment_status = "paid"
               AND ' . $this->publishedServiceReadyCondition() . '
             GROUP BY categories.id, categories.name, categories.slug
             ORDER BY categories.name ASC'
        );

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

        $sort = $filters['sort'] ?? 'featured';
        $orderBy = 'review_stats.avg_rating DESC, services.created_at DESC, services.id DESC';
        if ($sort === 'price_low') {
            $orderBy = 'services.price ASC, services.created_at DESC';
        } elseif ($sort === 'price_high') {
            $orderBy = 'services.price DESC, services.created_at DESC';
        } elseif ($sort === 'newest') {
            $orderBy = 'services.created_at DESC, services.id DESC';
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

    public function getServiceDetail($serviceId)
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
        $formatted['venue_rooms'] = strtolower((string)$formatted['category']) === 'venue' ? $this->getVenueRooms($serviceId) : [];
        $formatted['availability'] = $this->getServiceAvailability($serviceId, $formatted);
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

    private function getVenueRooms($serviceId)
    {
        $this->db->dbquery(
            'SELECT venue_rooms.id,
                    venue_rooms.name,
                    venue_rooms.capacity,
                    venue_rooms.price,
                    MIN(CASE WHEN venue_room_availability.is_available = 1 THEN venue_room_availability.start_time END) AS start_time,
                    MAX(CASE WHEN venue_room_availability.is_available = 1 THEN venue_room_availability.end_time END) AS end_time,
                    venues.name AS venue_name,
                    venues.location AS venue_location
             FROM venues
             INNER JOIN venue_rooms ON venue_rooms.venue_id = venues.id
             LEFT JOIN venue_room_availability ON venue_room_availability.room_id = venue_rooms.id
             WHERE venues.service_id = :service_id
             GROUP BY venue_rooms.id, venue_rooms.name, venue_rooms.capacity, venue_rooms.price, venues.name, venues.location
             ORDER BY venue_rooms.id ASC'
        );
        $this->db->dbbind(':service_id', (int)$serviceId);

        return array_map(function ($room) {
            return [
                'id' => (int)$room['id'],
                'name' => $room['name'] ?? '',
                'capacity' => (int)($room['capacity'] ?? 1),
                'price' => (float)($room['price'] ?? 0),
                'start_time' => $room['start_time'] ?? '09:00:00',
                'end_time' => $room['end_time'] ?? '17:00:00',
                'venue_name' => $room['venue_name'] ?? '',
                'venue_location' => $room['venue_location'] ?? '',
            ];
        }, $this->db->getmultidata());
    }

    private function getServiceAvailability($serviceId, $service)
    {
        $weekly = $this->getWeeklySchedule($serviceId);
        $overrides = $this->getDateOverrides($serviceId);
        $upcoming = $this->getUpcomingAvailability($serviceId, $service, $weekly, $overrides);

        return [
            'weekly' => array_values($weekly),
            'overrides' => array_values($overrides),
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

    private function getUpcomingAvailability($serviceId, $service, $weekly, $overrides)
    {
        $days = [];
        $date = new DateTimeImmutable('today');
        $duration = max(15, (int)($service['duration_minutes'] ?? 60));
        $buffer = max(0, (int)($service['buffer_minutes'] ?? 0));
        $bookingType = (($service['booking_type'] ?? 'fullday') === 'slot' || $buffer > 0) ? 'slot' : 'fullday';

        for ($i = 0; $i < 28 && count($days) < 8; $i++) {
            $day = $date->modify('+' . $i . ' days');
            $dateValue = $day->format('Y-m-d');
            $hours = $this->hoursForDate($dateValue, $weekly, $overrides);

            if (!$hours['is_available']) {
                continue;
            }

            $slots = $bookingType === 'slot'
                ? $this->availableSlotsForDate($serviceId, $dateValue, $hours['open_time'], $hours['close_time'], $duration, $buffer, (int)($service['max_concurrent'] ?? 1))
                : [[
                    'start_time' => $hours['open_time'],
                    'end_time' => $hours['close_time'],
                    'label' => $this->formatTimeRange($hours['open_time'], $hours['close_time']),
                    'remaining' => max(1, (int)($service['max_concurrent'] ?? 1)),
                ]];

            if (empty($slots)) {
                continue;
            }

            $days[] = [
                'date' => $dateValue,
                'day_label' => $day->format('D, M j'),
                'status' => $hours['source'] === 'override' ? 'Custom hours' : 'Available',
                'reason' => $hours['reason'] ?? '',
                'slots' => array_slice($slots, 0, 6),
            ];
        }

        return $days;
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
