<?php

class SupplierServiceManager
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
        $this->ensureSchema();
    }

    private function ensureSchema()
    {
        $this->addColumnIfMissing('supplier_packages', 'thumbnail_url', 'ALTER TABLE supplier_packages ADD COLUMN thumbnail_url VARCHAR(255) DEFAULT NULL AFTER total_price');
        $this->addColumnIfMissing('supplier_packages', 'is_active', 'ALTER TABLE supplier_packages ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER thumbnail_url');
        $this->addColumnIfMissing('supplier_packages', 'categories_json', 'ALTER TABLE supplier_packages ADD COLUMN categories_json TEXT DEFAULT NULL AFTER is_active');
    }

    private function addColumnIfMissing($table, $column, $alterSql)
    {
        try {
            $this->db->dbquery(
                'SELECT COUNT(*) AS total
                 FROM INFORMATION_SCHEMA.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = :table_name
                   AND COLUMN_NAME = :column_name'
            );
            $this->db->dbbind(':table_name', $table);
            $this->db->dbbind(':column_name', $column);
            $row = $this->db->getsingledata();

            if ((int)($row['total'] ?? 0) === 0) {
                $this->db->dbquery($alterSql);
                $this->db->dbexecute();
            }
        } catch (Throwable $e) {
            return false;
        }

        return true;
    }

    public function getInitialData($supplierId)
    {
        return [
            'services' => $this->getServices($supplierId),
            'packages' => $this->getPackages($supplierId),
            'categories' => $this->getCategories(),
        ];
    }

    public function getCategories()
    {
        $this->db->dbquery('SELECT id, name, slug FROM categories ORDER BY name ASC');
        return $this->db->getmultidata();
    }

    public function findOrCreateCategory($name)
    {
        $name = trim((string)$name);
        $slug = $this->slugify($name);

        if ($name === '') {
            return null;
        }

        $this->db->dbquery('SELECT id, name FROM categories WHERE LOWER(name) = LOWER(:name) OR slug = :slug LIMIT 1');
        $this->db->dbbind(':name', $name);
        $this->db->dbbind(':slug', $slug);
        $category = $this->db->getsingledata();

        if ($category) {
            return (int)$category['id'];
        }

        $this->db->dbquery('INSERT INTO categories(name, slug) VALUES(:name, :slug)');
        $this->db->dbbind(':name', $name);
        $this->db->dbbind(':slug', $slug);
        $this->db->dbexecute();

        return (int)$this->db->lastinsertid();
    }

    public function getServices($supplierId)
    {
        $this->db->dbquery(
            'SELECT services.id,
                    services.name,
                    services.description,
                    services.price,
                    services.thumbnail_url,
                    services.is_active,
                    services.booking_type,
                    services.duration_minutes,
                    services.buffer_minutes,
                    services.pricing_unit,
                    services.max_concurrent,
                    categories.name AS category
             FROM services
             LEFT JOIN categories ON categories.id = services.category_id
             WHERE services.supplier_id = :supplier_id
             ORDER BY services.created_at DESC, services.id DESC'
        );
        $this->db->dbbind(':supplier_id', (int)$supplierId);

        return array_map([$this, 'formatService'], $this->db->getmultidata());
    }

    public function getPackages($supplierId)
    {
        $this->db->dbquery(
            'SELECT id, name, description, total_price, thumbnail_url, is_active, categories_json
             FROM supplier_packages
             WHERE supplier_id = :supplier_id
               AND deleted_at IS NULL
             ORDER BY created_at DESC, id DESC'
        );
        $this->db->dbbind(':supplier_id', (int)$supplierId);

        return array_map([$this, 'formatPackage'], $this->db->getmultidata());
    }

    public function createService($supplierId, $data)
    {
        $categoryId = $this->findOrCreateCategory($data['category'] ?? 'Others');

        $this->db->dbquery(
            'INSERT INTO services(
                supplier_id, category_id, name, description, price, thumbnail_url,
                is_active, booking_type, duration_minutes, pricing_unit, max_concurrent
             ) VALUES(
                :supplier_id, :category_id, :name, :description, :price, :thumbnail_url,
                :is_active, :booking_type, :duration_minutes, :pricing_unit, :max_concurrent
             )'
        );
        $this->bindServiceFields($supplierId, $categoryId, $data);
        $this->db->dbexecute();

        return $this->getServiceById((int)$this->db->lastinsertid(), $supplierId);
    }

    public function updateService($supplierId, $serviceId, $data)
    {
        $service = $this->getServiceById($serviceId, $supplierId);

        if (!$service) {
            return null;
        }

        $categoryId = $this->findOrCreateCategory($data['category'] ?? $service['category'] ?? 'Others');

        $this->db->dbquery(
            'UPDATE services
             SET category_id = :category_id,
                 name = :name,
                 description = :description,
                 price = :price,
                 thumbnail_url = :thumbnail_url,
                 is_active = :is_active,
                 booking_type = :booking_type,
                 duration_minutes = :duration_minutes,
                 pricing_unit = :pricing_unit,
                 max_concurrent = :max_concurrent
             WHERE id = :id
               AND supplier_id = :supplier_id'
        );
        $this->bindServiceFields($supplierId, $categoryId, $data);
        $this->db->dbbind(':id', (int)$serviceId);
        $this->db->dbexecute();

        return $this->getServiceById($serviceId, $supplierId);
    }

    public function deleteService($supplierId, $serviceId)
    {
        $this->db->dbquery('DELETE FROM supplier_package_items WHERE service_id = :service_id');
        $this->db->dbbind(':service_id', (int)$serviceId);
        $this->db->dbexecute();

        $this->db->dbquery('DELETE FROM service_media WHERE service_id = :service_id');
        $this->db->dbbind(':service_id', (int)$serviceId);
        $this->db->dbexecute();

        $this->db->dbquery('DELETE FROM services WHERE id = :id AND supplier_id = :supplier_id');
        $this->db->dbbind(':id', (int)$serviceId);
        $this->db->dbbind(':supplier_id', (int)$supplierId);

        return $this->db->dbexecute();
    }

    public function setServiceStatus($supplierId, $serviceId, $isActive)
    {
        $this->db->dbquery(
            'UPDATE services
             SET is_active = :is_active
             WHERE id = :id
               AND supplier_id = :supplier_id'
        );
        $this->db->dbbind(':is_active', $isActive ? 1 : 0);
        $this->db->dbbind(':id', (int)$serviceId);
        $this->db->dbbind(':supplier_id', (int)$supplierId);
        $this->db->dbexecute();

        return $this->getServiceById($serviceId, $supplierId);
    }

    public function createPackage($supplierId, $data)
    {
        $categories = $this->normalizeCategories($data['categories'] ?? []);

        $this->db->dbquery(
            'INSERT INTO supplier_packages(supplier_id, name, description, total_price, thumbnail_url, is_active, categories_json)
             VALUES(:supplier_id, :name, :description, :total_price, :thumbnail_url, :is_active, :categories_json)'
        );
        $this->bindPackageFields($supplierId, $data, $categories);
        $this->db->dbexecute();

        return $this->getPackageById((int)$this->db->lastinsertid(), $supplierId);
    }

    public function updatePackage($supplierId, $packageId, $data)
    {
        $package = $this->getPackageById($packageId, $supplierId);

        if (!$package) {
            return null;
        }

        $categories = $this->normalizeCategories($data['categories'] ?? $package['categories'] ?? []);

        $this->db->dbquery(
            'UPDATE supplier_packages
             SET name = :name,
                 description = :description,
                 total_price = :total_price,
                 thumbnail_url = :thumbnail_url,
                 is_active = :is_active,
                 categories_json = :categories_json
             WHERE id = :id
               AND supplier_id = :supplier_id
               AND deleted_at IS NULL'
        );
        $this->bindPackageFields($supplierId, $data, $categories);
        $this->db->dbbind(':id', (int)$packageId);
        $this->db->dbexecute();

        return $this->getPackageById($packageId, $supplierId);
    }

    public function deletePackage($supplierId, $packageId)
    {
        $this->db->dbquery('DELETE FROM supplier_package_items WHERE package_id = :package_id');
        $this->db->dbbind(':package_id', (int)$packageId);
        $this->db->dbexecute();

        $this->db->dbquery(
            'UPDATE supplier_packages
             SET deleted_at = NOW()
             WHERE id = :id
               AND supplier_id = :supplier_id'
        );
        $this->db->dbbind(':id', (int)$packageId);
        $this->db->dbbind(':supplier_id', (int)$supplierId);

        return $this->db->dbexecute();
    }

    public function setPackageStatus($supplierId, $packageId, $isActive)
    {
        $this->db->dbquery(
            'UPDATE supplier_packages
             SET is_active = :is_active
             WHERE id = :id
               AND supplier_id = :supplier_id
               AND deleted_at IS NULL'
        );
        $this->db->dbbind(':is_active', $isActive ? 1 : 0);
        $this->db->dbbind(':id', (int)$packageId);
        $this->db->dbbind(':supplier_id', (int)$supplierId);
        $this->db->dbexecute();

        return $this->getPackageById($packageId, $supplierId);
    }

    private function getServiceById($serviceId, $supplierId)
    {
        $this->db->dbquery(
            'SELECT services.id,
                    services.name,
                    services.description,
                    services.price,
                    services.thumbnail_url,
                    services.is_active,
                    services.booking_type,
                    services.duration_minutes,
                    services.buffer_minutes,
                    services.pricing_unit,
                    services.max_concurrent,
                    categories.name AS category
             FROM services
             LEFT JOIN categories ON categories.id = services.category_id
             WHERE services.id = :id
               AND services.supplier_id = :supplier_id
             LIMIT 1'
        );
        $this->db->dbbind(':id', (int)$serviceId);
        $this->db->dbbind(':supplier_id', (int)$supplierId);
        $service = $this->db->getsingledata();

        return $service ? $this->formatService($service) : null;
    }

    public function getServiceDetail($supplierId, $serviceId)
    {
        $service = $this->getServiceById($serviceId, $supplierId);

        if (!$service) {
            return null;
        }

        $service['media'] = $this->getServiceMedia($serviceId, $supplierId);
        $service['availability'] = $this->getAvailability($supplierId, $serviceId);

        return $service;
    }

    public function getAvailability($supplierId, $serviceId)
    {
        $service = $this->getServiceById($serviceId, $supplierId);

        if (!$service) {
            return null;
        }

        $this->db->dbquery(
            'SELECT day_of_week, open_time, close_time, is_available
             FROM service_schedules
             WHERE service_id = :service_id
             ORDER BY day_of_week ASC'
        );
        $this->db->dbbind(':service_id', (int)$serviceId);
        $schedules = $this->db->getmultidata();

        $this->db->dbquery(
            'SELECT id, date, type, open_time, close_time, reason
             FROM service_availability
             WHERE service_id = :service_id
             ORDER BY date DESC
             LIMIT 30'
        );
        $this->db->dbbind(':service_id', (int)$serviceId);
        $overrides = $this->db->getmultidata();

        return [
            'duration_minutes' => (int)($service['duration_minutes'] ?? 60),
            'buffer_minutes' => (int)($service['buffer_minutes'] ?? 0),
            'max_concurrent' => (int)($service['capacity'] ?? 1),
            'weekly' => $schedules,
            'overrides' => $overrides,
        ];
    }

    public function saveWeeklyAvailability($supplierId, $serviceId, $data)
    {
        if (!$this->getServiceById($serviceId, $supplierId)) {
            return null;
        }

        $duration = max(15, min(720, (int)($data['duration_minutes'] ?? 60)));
        $buffer = max(0, min(240, (int)($data['buffer_minutes'] ?? 0)));
        $maxConcurrent = max(1, min(20, (int)($data['max_concurrent'] ?? 1)));
        $weekly = is_array($data['weekly'] ?? null) ? $data['weekly'] : [];

        $this->db->dbquery(
            'UPDATE services
             SET duration_minutes = :duration_minutes,
                 buffer_minutes = :buffer_minutes,
                 max_concurrent = :max_concurrent,
                 booking_type = :booking_type
             WHERE id = :id
               AND supplier_id = :supplier_id'
        );
        $this->db->dbbind(':duration_minutes', $duration);
        $this->db->dbbind(':buffer_minutes', $buffer);
        $this->db->dbbind(':max_concurrent', $maxConcurrent);
        $this->db->dbbind(':booking_type', 'slot');
        $this->db->dbbind(':id', (int)$serviceId);
        $this->db->dbbind(':supplier_id', (int)$supplierId);
        $this->db->dbexecute();

        $this->db->dbquery('DELETE FROM service_schedules WHERE service_id = :service_id');
        $this->db->dbbind(':service_id', (int)$serviceId);
        $this->db->dbexecute();

        foreach ($weekly as $row) {
            $day = (int)($row['day_of_week'] ?? 0);

            if ($day < 1 || $day > 7) {
                continue;
            }

            $isAvailable = !empty($row['is_available']) ? 1 : 0;
            $openTime = $this->normalizeTime($row['open_time'] ?? '09:00');
            $closeTime = $this->normalizeTime($row['close_time'] ?? '17:00');

            if ($openTime >= $closeTime) {
                $isAvailable = 0;
            }

            $this->db->dbquery(
                'INSERT INTO service_schedules(service_id, day_of_week, open_time, close_time, is_available)
                 VALUES(:service_id, :day_of_week, :open_time, :close_time, :is_available)'
            );
            $this->db->dbbind(':service_id', (int)$serviceId);
            $this->db->dbbind(':day_of_week', $day);
            $this->db->dbbind(':open_time', $openTime);
            $this->db->dbbind(':close_time', $closeTime);
            $this->db->dbbind(':is_available', $isAvailable);
            $this->db->dbexecute();
        }

        return $this->getAvailability($supplierId, $serviceId);
    }

    public function saveDateOverride($supplierId, $serviceId, $data)
    {
        if (!$this->getServiceById($serviceId, $supplierId)) {
            return null;
        }

        $date = $this->normalizeDate($data['date'] ?? '');
        $type = in_array($data['type'] ?? '', ['available', 'unavailable', 'custom_hours'], true) ? $data['type'] : 'unavailable';
        $openTime = $type === 'custom_hours' ? $this->normalizeTime($data['open_time'] ?? '09:00') : null;
        $closeTime = $type === 'custom_hours' ? $this->normalizeTime($data['close_time'] ?? '17:00') : null;
        $reason = trim((string)($data['reason'] ?? ''));

        if (!$date) {
            return null;
        }

        $this->db->dbquery('DELETE FROM service_availability WHERE service_id = :service_id AND date = :date');
        $this->db->dbbind(':service_id', (int)$serviceId);
        $this->db->dbbind(':date', $date);
        $this->db->dbexecute();

        $this->db->dbquery(
            'INSERT INTO service_availability(service_id, date, type, open_time, close_time, reason)
             VALUES(:service_id, :date, :type, :open_time, :close_time, :reason)'
        );
        $this->db->dbbind(':service_id', (int)$serviceId);
        $this->db->dbbind(':date', $date);
        $this->db->dbbind(':type', $type);
        $this->db->dbbind(':open_time', $openTime);
        $this->db->dbbind(':close_time', $closeTime);
        $this->db->dbbind(':reason', $reason !== '' ? $reason : null);
        $this->db->dbexecute();

        return $this->getAvailability($supplierId, $serviceId);
    }

    public function deleteDateOverride($supplierId, $serviceId, $overrideId)
    {
        if (!$this->getServiceById($serviceId, $supplierId)) {
            return false;
        }

        $this->db->dbquery('DELETE FROM service_availability WHERE id = :id AND service_id = :service_id');
        $this->db->dbbind(':id', (int)$overrideId);
        $this->db->dbbind(':service_id', (int)$serviceId);

        return $this->db->dbexecute();
    }

    public function previewSlots($supplierId, $serviceId, $date)
    {
        $service = $this->getServiceById($serviceId, $supplierId);
        $date = $this->normalizeDate($date);

        if (!$service || !$date) {
            return ['date' => $date, 'status' => 'closed', 'slots' => []];
        }

        $this->db->dbquery(
            'SELECT type, open_time, close_time, reason
             FROM service_availability
             WHERE service_id = :service_id
               AND date = :date
             LIMIT 1'
        );
        $this->db->dbbind(':service_id', (int)$serviceId);
        $this->db->dbbind(':date', $date);
        $override = $this->db->getsingledata();

        if ($override && $override['type'] === 'unavailable') {
            return ['date' => $date, 'status' => 'closed', 'reason' => $override['reason'] ?? '', 'slots' => []];
        }

        if ($override && $override['type'] === 'custom_hours') {
            $openTime = $override['open_time'];
            $closeTime = $override['close_time'];
        } else {
            $dayOfWeek = (int)date('N', strtotime($date));
            $this->db->dbquery(
                'SELECT open_time, close_time, is_available
                 FROM service_schedules
                 WHERE service_id = :service_id
                   AND day_of_week = :day_of_week
                 LIMIT 1'
            );
            $this->db->dbbind(':service_id', (int)$serviceId);
            $this->db->dbbind(':day_of_week', $dayOfWeek);
            $schedule = $this->db->getsingledata();

            if (!$schedule || empty($schedule['is_available'])) {
                return ['date' => $date, 'status' => 'closed', 'slots' => []];
            }

            $openTime = $schedule['open_time'];
            $closeTime = $schedule['close_time'];
        }

        $duration = max(15, (int)($service['duration_minutes'] ?? 60));
        $buffer = max(0, (int)($service['buffer_minutes'] ?? 0));
        $slots = $this->buildSlots($date, $openTime, $closeTime, $duration, $buffer);

        return ['date' => $date, 'status' => empty($slots) ? 'closed' : 'open', 'slots' => $slots];
    }

    public function getServiceMedia($serviceId, $supplierId)
    {
        if (!$this->getServiceById($serviceId, $supplierId)) {
            return [];
        }

        $this->db->dbquery(
            'SELECT id, file_url, type
             FROM service_media
             WHERE service_id = :service_id
             ORDER BY id DESC'
        );
        $this->db->dbbind(':service_id', (int)$serviceId);

        return $this->db->getmultidata();
    }

    public function addServiceMedia($supplierId, $serviceId, $fileUrl, $type = 'image')
    {
        if (!$this->getServiceById($serviceId, $supplierId)) {
            return null;
        }

        $this->db->dbquery(
            'INSERT INTO service_media(service_id, file_url, type)
             VALUES(:service_id, :file_url, :type)'
        );
        $this->db->dbbind(':service_id', (int)$serviceId);
        $this->db->dbbind(':file_url', $fileUrl);
        $this->db->dbbind(':type', $type === 'video' ? 'video' : 'image');
        $this->db->dbexecute();

        $mediaId = (int)$this->db->lastinsertid();

        $this->db->dbquery('SELECT id, file_url, type FROM service_media WHERE id = :id LIMIT 1');
        $this->db->dbbind(':id', $mediaId);

        return $this->db->getsingledata();
    }

    public function deleteServiceMedia($supplierId, $serviceId, $mediaId)
    {
        if (!$this->getServiceById($serviceId, $supplierId)) {
            return false;
        }

        $this->db->dbquery(
            'DELETE FROM service_media
             WHERE id = :id
               AND service_id = :service_id'
        );
        $this->db->dbbind(':id', (int)$mediaId);
        $this->db->dbbind(':service_id', (int)$serviceId);

        return $this->db->dbexecute();
    }

    private function getPackageById($packageId, $supplierId)
    {
        $this->db->dbquery(
            'SELECT id, name, description, total_price, thumbnail_url, is_active, categories_json
             FROM supplier_packages
             WHERE id = :id
               AND supplier_id = :supplier_id
               AND deleted_at IS NULL
             LIMIT 1'
        );
        $this->db->dbbind(':id', (int)$packageId);
        $this->db->dbbind(':supplier_id', (int)$supplierId);
        $package = $this->db->getsingledata();

        return $package ? $this->formatPackage($package) : null;
    }

    private function bindServiceFields($supplierId, $categoryId, $data)
    {
        $this->db->dbbind(':supplier_id', (int)$supplierId);
        $this->db->dbbind(':category_id', $categoryId ? (int)$categoryId : null);
        $this->db->dbbind(':name', trim((string)($data['name'] ?? '')));
        $this->db->dbbind(':description', trim((string)($data['desc'] ?? $data['description'] ?? '')));
        $this->db->dbbind(':price', number_format((float)($data['price'] ?? 0), 2, '.', ''), PDO::PARAM_STR);
        $this->db->dbbind(':thumbnail_url', $data['img'] ?? $data['thumbnail_url'] ?? null);
        $this->db->dbbind(':is_active', ($data['status'] ?? 'active') === 'inactive' ? 0 : 1);
        $this->db->dbbind(':booking_type', ($data['booking_type'] ?? '') === 'slot' || !empty($data['timeslot']) ? 'slot' : 'fullday');
        $this->db->dbbind(':duration_minutes', !empty($data['duration_minutes']) ? (int)$data['duration_minutes'] : null);
        $this->db->dbbind(':pricing_unit', $data['pricing_unit'] ?? 'per_session');
        $this->db->dbbind(':max_concurrent', max(1, (int)($data['capacity'] ?? $data['max_concurrent'] ?? 1)));
    }

    private function bindPackageFields($supplierId, $data, $categories)
    {
        $this->db->dbbind(':supplier_id', (int)$supplierId);
        $this->db->dbbind(':name', trim((string)($data['name'] ?? '')));
        $this->db->dbbind(':description', trim((string)($data['desc'] ?? $data['description'] ?? '')));
        $this->db->dbbind(':total_price', number_format((float)($data['price'] ?? $data['total_price'] ?? 0), 2, '.', ''), PDO::PARAM_STR);
        $this->db->dbbind(':thumbnail_url', $data['img'] ?? $data['thumbnail_url'] ?? null);
        $this->db->dbbind(':is_active', ($data['status'] ?? 'active') === 'inactive' ? 0 : 1);
        $this->db->dbbind(':categories_json', json_encode($categories));
    }

    private function formatService($service)
    {
        return [
            'id' => (int)$service['id'],
            'name' => $service['name'] ?? '',
            'price' => (float)($service['price'] ?? 0),
            'category' => $service['category'] ?: 'Others',
            'status' => !empty($service['is_active']) ? 'active' : 'inactive',
            'desc' => $service['description'] ?? '',
            'img' => $service['thumbnail_url'] ?? '',
            'capacity' => (int)($service['max_concurrent'] ?? 1),
            'duration_minutes' => (int)($service['duration_minutes'] ?? 60),
            'buffer_minutes' => (int)($service['buffer_minutes'] ?? 0),
            'timeslot' => ($service['booking_type'] ?? '') === 'slot' ? 'Custom slot' : '',
        ];
    }

    private function normalizeDate($date)
    {
        $timestamp = strtotime((string)$date);

        return $timestamp ? date('Y-m-d', $timestamp) : null;
    }

    private function normalizeTime($time)
    {
        $timestamp = strtotime((string)$time);

        return $timestamp ? date('H:i:s', $timestamp) : '09:00:00';
    }

    private function buildSlots($date, $openTime, $closeTime, $durationMinutes, $bufferMinutes)
    {
        $slots = [];
        $cursor = strtotime($date . ' ' . $openTime);
        $close = strtotime($date . ' ' . $closeTime);
        $stepSeconds = ($durationMinutes + $bufferMinutes) * 60;
        $durationSeconds = $durationMinutes * 60;

        while ($cursor && $close && $cursor + $durationSeconds <= $close) {
            $end = $cursor + $durationSeconds;
            $slots[] = [
                'start_time' => date('H:i', $cursor),
                'end_time' => date('H:i', $end),
            ];
            $cursor += $stepSeconds;
        }

        return $slots;
    }

    private function formatPackage($package)
    {
        $categories = json_decode($package['categories_json'] ?? '[]', true);

        return [
            'id' => (int)$package['id'],
            'name' => $package['name'] ?? '',
            'price' => (float)($package['total_price'] ?? 0),
            'categories' => is_array($categories) ? $categories : [],
            'status' => !empty($package['is_active']) ? 'active' : 'inactive',
            'desc' => $package['description'] ?? '',
            'img' => $package['thumbnail_url'] ?? '',
        ];
    }

    private function normalizeCategories($categories)
    {
        if (!is_array($categories)) {
            $categories = [$categories];
        }

        return array_values(array_unique(array_filter(array_map(function ($category) {
            return trim((string)$category);
        }, $categories))));
    }

    private function slugify($value)
    {
        $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $value), '-'));

        return $slug !== '' ? $slug : 'category';
    }
}
