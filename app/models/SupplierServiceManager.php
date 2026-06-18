<?php

class SupplierServiceManager
{
    private $db;
    private $hasServicePriceRangeColumns = null;
    private $hasServiceDefaultTimeColumns = null;
    private $hasVenueRoomPriceRangeColumns = null;
    private $hasVenueServiceColumn = null;
    private $hasRentalPriceMatrixColumns = null;
    private $rentalPricingColumns = null;
    private $hasDecorationStylePhotoColumn = null;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function getInitialData($supplierId, $options = [])
    {
        $serviceLimit = $this->normalizeLimit($options['service_limit'] ?? $options['limit'] ?? null);
        $serviceOffset = max(0, (int)($options['service_offset'] ?? 0));
        $packageLimit = $this->normalizeLimit($options['package_limit'] ?? $options['limit'] ?? null);
        $packageOffset = max(0, (int)($options['package_offset'] ?? 0));

        return [
            'services' => $this->getServices($supplierId, $serviceLimit, $serviceOffset),
            'packages' => $this->getPackages($supplierId, $packageLimit, $packageOffset),
            'categories' => $this->getCategories(),
            'meta' => [
                'services' => $this->paginationMeta($serviceLimit, $serviceOffset, $this->countServices($supplierId)),
                'packages' => $this->paginationMeta($packageLimit, $packageOffset, $this->countPackages($supplierId)),
            ],
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

    public function getServices($supplierId, $limit = null, $offset = 0)
    {
        $priceRangeFields = $this->servicePriceRangeSelectFields();
        $defaultTimeFields = $this->serviceDefaultTimeSelectFields();
        $venueSelectFields = $this->venueSelectFields();
        $venueJoin = $this->venueJoinClause();
        $query = 'SELECT services.id,
                         services.name,
                         services.description,
                         services.price,
                         ' . $priceRangeFields . '
                         services.thumbnail_url,
                         services.is_active,
                         services.booking_type,
                         services.duration_minutes,
                         services.buffer_minutes,
                         services.pricing_unit,
                         services.max_concurrent,
                         services.min_lead_days,
                         ' . $defaultTimeFields . '
                         ' . $venueSelectFields . '
                         categories.name AS category
                  FROM services
                  LEFT JOIN categories ON categories.id = services.category_id
                  ' . $venueJoin . '
                  WHERE services.supplier_id = :supplier_id
                  ORDER BY services.created_at DESC, services.id DESC';

        if ($limit !== null) {
            $query .= ' LIMIT :limit OFFSET :offset';
        }

        $this->db->dbquery($query);
        $this->db->dbbind(':supplier_id', (int)$supplierId);
        $this->bindPagination($limit, $offset);

        return array_map([$this, 'formatService'], $this->db->getmultidata());
    }

    public function getPackages($supplierId, $limit = null, $offset = 0)
    {
        $query = 'SELECT id, name, description, total_price, thumbnail_url, is_active, categories_json
                  FROM supplier_packages
                  WHERE supplier_id = :supplier_id
                    AND deleted_at IS NULL
                  ORDER BY created_at DESC, id DESC';

        if ($limit !== null) {
            $query .= ' LIMIT :limit OFFSET :offset';
        }

        $this->db->dbquery($query);
        $this->db->dbbind(':supplier_id', (int)$supplierId);
        $this->bindPagination($limit, $offset);

        return array_map([$this, 'formatPackage'], $this->db->getmultidata());
    }

    public function createService($supplierId, $data)
    {
        $data = $this->applyVenueRoomPriceRange($data);
        $data = $this->applyDecorationStylePriceRange($data);
        $data = $this->applyRentalPriceRange($data);
        $categoryId = $this->findOrCreateCategory($data['category'] ?? 'Others');
        $priceRangeColumns = $this->hasServicePriceRangeColumns() ? ', price_min, price_max' : '';
        $priceRangeValues = $this->hasServicePriceRangeColumns() ? ', :price_min, :price_max' : '';
        $defaultTimeColumns = $this->hasServiceDefaultTimeColumns() ? ', default_start_time, default_end_time' : '';
        $defaultTimeValues  = $this->hasServiceDefaultTimeColumns() ? ', :default_start_time, :default_end_time' : '';

        $this->db->dbquery(
            'INSERT INTO services(
                supplier_id, category_id, name, description, price' . $priceRangeColumns . ', thumbnail_url,
                is_active, booking_type, duration_minutes, pricing_unit, max_concurrent, min_lead_days' . $defaultTimeColumns . '
             ) VALUES(
                :supplier_id, :category_id, :name, :description, :price' . $priceRangeValues . ', :thumbnail_url,
                :is_active, :booking_type, :duration_minutes, :pricing_unit, :max_concurrent, :min_lead_days' . $defaultTimeValues . '
             )'
        );
        $this->bindServiceFields($supplierId, $categoryId, $data);
        $this->db->dbexecute();

        $serviceId = (int)$this->db->lastinsertid();
        $this->saveVenueDetails($supplierId, $serviceId, $data);
        $this->saveDecorationStyles($serviceId, $data);
        $this->saveRentalPricing($serviceId, $data);

        return $this->getServiceById($serviceId, $supplierId);
    }

    public function updateService($supplierId, $serviceId, $data)
    {
        $service = $this->getServiceById($serviceId, $supplierId);

        if (!$service) {
            return null;
        }

        if (!array_key_exists('booking_type', $data) && empty($data['timeslot'])) {
            $data['booking_type'] = !empty($service['timeslot']) ? 'slot' : 'fullday';
        }

        if (!array_key_exists('duration_minutes', $data) && !empty($service['duration_minutes'])) {
            $data['duration_minutes'] = (int)$service['duration_minutes'];
        }

        $data = $this->applyVenueRoomPriceRange($data);
        $data = $this->applyDecorationStylePriceRange($data);
        $data = $this->applyRentalPriceRange($data);
        $categoryId = $this->findOrCreateCategory($data['category'] ?? $service['category'] ?? 'Others');
        $priceRangeUpdate = $this->hasServicePriceRangeColumns()
            ? ',
                 price_min = :price_min,
                 price_max = :price_max'
            : '';
        $defaultTimeUpdate = $this->hasServiceDefaultTimeColumns()
            ? ',
                 default_start_time = :default_start_time,
                 default_end_time = :default_end_time'
            : '';

        $this->db->dbquery(
            'UPDATE services
             SET category_id = :category_id,
                 name = :name,
                 description = :description,
                 price = :price' . $priceRangeUpdate . ',
                 thumbnail_url = :thumbnail_url,
                 is_active = :is_active,
                 booking_type = :booking_type,
                 duration_minutes = :duration_minutes,
                 pricing_unit = :pricing_unit,
                 max_concurrent = :max_concurrent,
                 min_lead_days = :min_lead_days' . $defaultTimeUpdate . '
             WHERE id = :id
               AND supplier_id = :supplier_id'
        );
        $this->bindServiceFields($supplierId, $categoryId, $data);
        $this->db->dbbind(':id', (int)$serviceId);
        $this->db->dbexecute();
        $this->saveVenueDetails($supplierId, $serviceId, $data);
        $this->saveDecorationStyles($serviceId, $data);
        $this->saveRentalPricing($serviceId, $data);

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

        $this->db->dbquery('DELETE FROM decoration_styles WHERE service_id = :service_id');
        $this->db->dbbind(':service_id', (int)$serviceId);
        $this->db->dbexecute();

        $this->db->dbquery('DELETE FROM service_rental_pricing WHERE service_id = :service_id');
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

    public function getAdminServiceDetail($serviceId)
    {
        $serviceId = (int)$serviceId;
        $priceRangeFields = $this->servicePriceRangeSelectFields();
        $venueSelectFields = $this->venueSelectFields();
        $venueJoin = $this->venueJoinClause();

        $this->db->dbquery(
            'SELECT services.id,
                    services.supplier_id,
                    services.name,
                    services.description,
                    services.price,
                    ' . $priceRangeFields . '
                    services.thumbnail_url,
                    services.is_active,
                    services.booking_type,
                    services.duration_minutes,
                    services.buffer_minutes,
                    services.pricing_unit,
                    services.max_concurrent,
                    services.min_lead_days,
                    ' . $venueSelectFields . '
                    categories.name AS category,
                    suppliers.shop_name AS supplier_name,
                    users.name AS owner_name,
                    users.email AS owner_email,
                    users.phone AS supplier_phone,
                    suppliers.status AS supplier_status,
                    suppliers.payment_status AS supplier_payment_status
             FROM services
             INNER JOIN suppliers ON suppliers.supplier_id = services.supplier_id
             LEFT JOIN users ON users.user_id = suppliers.user_id
             LEFT JOIN categories ON categories.id = services.category_id
             ' . $venueJoin . '
             WHERE services.id = :id
             LIMIT 1'
        );
        $this->db->dbbind(':id', $serviceId);
        $row = $this->db->getsingledata();

        if (!$row) {
            return null;
        }

        $service = $this->formatService($row);
        $supplierId = (int)($row['supplier_id'] ?? 0);
        $service['supplier_id'] = $supplierId;
        $service['supplier_name'] = $row['supplier_name'] ?? '';
        $service['owner_name'] = $row['owner_name'] ?? '';
        $service['owner_email'] = $row['owner_email'] ?? '';
        $service['supplier_phone'] = $row['supplier_phone'] ?? '';
        $service['supplier_status'] = $row['supplier_status'] ?? '';
        $service['supplier_payment_status'] = $row['supplier_payment_status'] ?? '';
        $service['media'] = $this->getServiceMedia($serviceId, $supplierId);
        $service['availability'] = $this->getAvailability($supplierId, $serviceId);
        $service['readiness'] = $this->servicePublishReadiness($supplierId, $serviceId);

        return $service;
    }

    public function unpublishServiceIfIncomplete($supplierId, $serviceId)
    {
        $readiness = $this->servicePublishReadiness($supplierId, $serviceId);

        if (!$readiness || !empty($readiness['ready']) || ($readiness['service']['status'] ?? 'inactive') !== 'active') {
            return false;
        }

        $this->setServiceStatus($supplierId, $serviceId, false);

        return true;
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
        $priceRangeFields = $this->servicePriceRangeSelectFields();
        $defaultTimeFields = $this->serviceDefaultTimeSelectFields();
        $venueSelectFields = $this->venueSelectFields();
        $venueJoin = $this->venueJoinClause();
        $this->db->dbquery(
            'SELECT services.id,
                    services.name,
                    services.description,
                    services.price,
                    ' . $priceRangeFields . '
                    services.thumbnail_url,
                    services.is_active,
                    services.booking_type,
                    services.duration_minutes,
                    services.buffer_minutes,
                    services.pricing_unit,
                    services.max_concurrent,
                    services.min_lead_days,
                    ' . $defaultTimeFields . '
                    ' . $venueSelectFields . '
                    categories.name AS category
             FROM services
             LEFT JOIN categories ON categories.id = services.category_id
             ' . $venueJoin . '
             WHERE services.id = :id
               AND services.supplier_id = :supplier_id
             LIMIT 1'
        );
        $this->db->dbbind(':id', (int)$serviceId);
        $this->db->dbbind(':supplier_id', (int)$supplierId);
        $service = $this->db->getsingledata();

        return $service ? $this->formatService($service) : null;
    }

    private function countServices($supplierId)
    {
        $this->db->dbquery('SELECT COUNT(*) AS total FROM services WHERE supplier_id = :supplier_id');
        $this->db->dbbind(':supplier_id', (int)$supplierId);
        $row = $this->db->getsingledata();

        return (int)($row['total'] ?? 0);
    }

    private function countPackages($supplierId)
    {
        $this->db->dbquery(
            'SELECT COUNT(*) AS total
             FROM supplier_packages
             WHERE supplier_id = :supplier_id
               AND deleted_at IS NULL'
        );
        $this->db->dbbind(':supplier_id', (int)$supplierId);
        $row = $this->db->getsingledata();

        return (int)($row['total'] ?? 0);
    }

    private function normalizeLimit($limit)
    {
        if ($limit === null || $limit === '') {
            return null;
        }

        return max(0, min(100, (int)$limit));
    }

    private function bindPagination($limit, $offset)
    {
        if ($limit === null) {
            return;
        }

        $this->db->dbbind(':limit', (int)$limit, PDO::PARAM_INT);
        $this->db->dbbind(':offset', max(0, (int)$offset), PDO::PARAM_INT);
    }

    private function paginationMeta($limit, $offset, $total)
    {
        $offset = max(0, (int)$offset);
        $total = max(0, (int)$total);

        return [
            'limit' => $limit,
            'offset' => $offset,
            'total' => $total,
            'has_more' => $limit !== null && ($offset + (int)$limit) < $total,
        ];
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

    public function servicePublishReadiness($supplierId, $serviceId)
    {
        $service = $this->getServiceDetail($supplierId, $serviceId);

        if (!$service) {
            return null;
        }

        $missing = [];
        $name = trim((string)($service['name'] ?? ''));
        $description = trim((string)($service['desc'] ?? $service['description'] ?? ''));
        $price = (float)($service['price_min'] ?? $service['price'] ?? 0);
        $media = is_array($service['media'] ?? null) ? $service['media'] : [];
        $weekly = is_array($service['availability']['weekly'] ?? null) ? $service['availability']['weekly'] : [];
        $category = strtolower((string)($service['category'] ?? ''));
        $isVenue = $category === 'venue';
        $isDecoration = $category === 'decoration';
        $isRental = in_array($category, ['dress', 'accessories'], true);
        $venueRooms = is_array($service['venue_rooms'] ?? null) ? $service['venue_rooms'] : [];
        $decorationStyles = is_array($service['decoration_styles'] ?? null) ? $service['decoration_styles'] : [];
        $rentalPricing = is_array($service['rental_pricing'] ?? null) ? $service['rental_pricing'] : [];

        if ($name === '') {
            $missing[] = 'Add the service name.';
        }

        if ($description === '') {
            $missing[] = 'Add a service description.';
        }

        if ($price <= 0 && !$isDecoration && !$isRental) {
            $missing[] = 'Add a valid package price.';
        }

        if (empty($media)) {
            $missing[] = 'Upload at least one portfolio photo.';
        }

        if ($isVenue) {
            if (empty($venueRooms)) {
                $missing[] = 'Add at least one hall or room.';
            } elseif (!$this->hasValidVenueRooms($venueRooms)) {
                $missing[] = 'Each hall needs a name, capacity, price, start time, and end time.';
            }
        } elseif ($isDecoration) {
            $validStyles = array_filter($decorationStyles, fn($s) => trim((string)($s['name'] ?? '')) !== '' && (float)($s['price'] ?? 0) > 0);
            if (empty($validStyles)) {
                $missing[] = 'Add at least one decoration style with a name and price.';
            }
        } elseif ($isRental) {
            $hasBorrow = ($rentalPricing['borrow_package_price'] ?? $rentalPricing['borrow_price'] ?? 0) > 0
                || ($rentalPricing['borrow_customize_price'] ?? 0) > 0;
            $hasBuy = ($rentalPricing['buy_package_price'] ?? $rentalPricing['buy_price'] ?? 0) > 0
                || ($rentalPricing['buy_customize_price'] ?? 0) > 0;
            if (!$hasBorrow && !$hasBuy) {
                $missing[] = 'Add a borrow price, a buy price, or both.';
            }
        } elseif (!$this->hasOpenWeeklySchedule($weekly)) {
            $missing[] = 'Set at least one weekly available day with valid start and end time.';
        }

        return [
            'ready' => empty($missing),
            'missing' => $missing,
            'service' => $service,
        ];
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
        $minLeadDays = max(0, min(365, (int)($data['min_lead_days'] ?? 0)));
        $weekly = is_array($data['weekly'] ?? null) ? $data['weekly'] : [];

        $this->db->dbquery(
            'UPDATE services
                 SET duration_minutes = :duration_minutes,
                     buffer_minutes = :buffer_minutes,
                     max_concurrent = :max_concurrent,
                     min_lead_days = :min_lead_days,
                     booking_type = :booking_type
             WHERE id = :id
               AND supplier_id = :supplier_id'
        );
        $this->db->dbbind(':duration_minutes', $duration);
        $this->db->dbbind(':buffer_minutes', $buffer);
        $this->db->dbbind(':max_concurrent', $maxConcurrent);
        $this->db->dbbind(':min_lead_days', $minLeadDays);
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
        $openTime = in_array($type, ['available', 'custom_hours'], true) ? $this->normalizeTime($data['open_time'] ?? '09:00') : null;
        $closeTime = in_array($type, ['available', 'custom_hours'], true) ? $this->normalizeTime($data['close_time'] ?? '17:00') : null;
        $reason = trim((string)($data['reason'] ?? ''));

        if (!$date) {
            return null;
        }

        if ($openTime !== null && $closeTime !== null && $openTime >= $closeTime) {
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

    public function getServiceCalendarMonth($supplierId, $serviceId, $month)
    {
        $service = $this->getServiceById($serviceId, $supplierId);

        if (!$service) {
            return null;
        }

        $monthStart = DateTimeImmutable::createFromFormat('!Y-m-d', preg_match('/^\d{4}-\d{2}$/', (string)$month) ? $month . '-01' : date('Y-m-01'));

        if (!$monthStart) {
            $monthStart = new DateTimeImmutable('first day of this month');
        }

        $monthEnd = $monthStart->modify('last day of this month');
        $gridStart = $monthStart->modify('-' . ((int)$monthStart->format('N') - 1) . ' days');
        $gridEnd = $monthEnd->modify('+' . (7 - (int)$monthEnd->format('N')) . ' days');
        $weekly = $this->calendarWeeklySchedule((int)$serviceId);
        $overrides = $this->calendarDateOverrides((int)$serviceId, $gridStart->format('Y-m-d'), $gridEnd->format('Y-m-d'));
        $bookings = $this->calendarBookings((int)$serviceId, (int)$supplierId, $gridStart->format('Y-m-d'), $gridEnd->format('Y-m-d'));
        $days = [];

        for ($day = $gridStart; $day <= $gridEnd; $day = $day->modify('+1 day')) {
            $date = $day->format('Y-m-d');
            $dayOfWeek = (int)$day->format('N');
            $schedule = $weekly[$dayOfWeek] ?? null;
            $override = $overrides[$date] ?? null;
            $dayBookings = $bookings[$date] ?? [];
            $isOpen = $schedule && !empty($schedule['is_available']) && $schedule['open_time'] < $schedule['close_time'];
            $status = $isOpen ? 'open' : 'closed';
            $source = 'weekly';
            $openTime = $isOpen ? $schedule['open_time'] : null;
            $closeTime = $isOpen ? $schedule['close_time'] : null;

            if ($override) {
                $source = 'override';

                if ($override['type'] === 'unavailable') {
                    $status = 'unavailable';
                    $openTime = null;
                    $closeTime = null;
                } elseif ($override['type'] === 'custom_hours') {
                    $status = 'custom_hours';
                    $openTime = $override['open_time'];
                    $closeTime = $override['close_time'];
                } elseif ($override['type'] === 'available') {
                    $status = 'open';
                    $openTime = $override['open_time'] ?: ($openTime ?: '09:00:00');
                    $closeTime = $override['close_time'] ?: ($closeTime ?: '17:00:00');
                }
            }

            if (!empty($dayBookings)) {
                $status = $status === 'unavailable' ? 'unavailable' : 'booked';
            }

            $days[] = [
                'date' => $date,
                'day' => (int)$day->format('j'),
                'weekday' => $day->format('D'),
                'in_month' => $day->format('Y-m') === $monthStart->format('Y-m'),
                'is_today' => $date === date('Y-m-d'),
                'status' => $status,
                'source' => $source,
                'open_time' => $openTime,
                'close_time' => $closeTime,
                'override' => $override,
                'bookings' => $dayBookings,
                'booking_count' => count($dayBookings),
            ];
        }

        return [
            'service' => [
                'id' => (int)$service['id'],
                'name' => $service['name'] ?? '',
                'category' => $service['category'] ?? '',
            ],
            'month' => $monthStart->format('Y-m'),
            'month_label' => $monthStart->format('F Y'),
            'prev_month' => $monthStart->modify('-1 month')->format('Y-m'),
            'next_month' => $monthStart->modify('+1 month')->format('Y-m'),
            'days' => $days,
        ];
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

        if ($override && in_array($override['type'], ['available', 'custom_hours'], true)) {
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
        $slots = array_map(function ($slot) use ($serviceId, $date, $service) {
            return $this->ensureServiceTimeSlot(
                $serviceId,
                $date,
                $slot['start_time'],
                $slot['end_time'],
                (int)($service['capacity'] ?? 1)
            );
        }, $slots);

        return ['date' => $date, 'status' => empty($slots) ? 'closed' : 'open', 'slots' => $slots];
    }

    public function reserveServiceSlot($slotId)
    {
        $this->db->dbquery(
            "UPDATE service_time_slots
             SET confirmed_count = confirmed_count + 1,
                 status = CASE
                    WHEN confirmed_count + 1 >= max_concurrent THEN 'full'
                    ELSE 'available'
                 END
             WHERE id = :id
               AND status = 'available'
               AND confirmed_count < max_concurrent"
        );
        $this->db->dbbind(':id', (int)$slotId);
        $this->db->dbexecute();

        return $this->db->rowcount() > 0;
    }

    public function releaseServiceSlot($slotId)
    {
        $this->db->dbquery(
            "UPDATE service_time_slots
             SET confirmed_count = GREATEST(confirmed_count - 1, 0),
                 status = CASE
                    WHEN GREATEST(confirmed_count - 1, 0) >= max_concurrent THEN 'full'
                    ELSE 'available'
                 END
             WHERE id = :id"
        );
        $this->db->dbbind(':id', (int)$slotId);
        $this->db->dbexecute();

        return $this->db->rowcount() > 0;
    }

    public function reserveBookingItemSlot($bookingItemId)
    {
        $slotId = $this->getBookingItemSlotId($bookingItemId);

        if (!$slotId) {
            return false;
        }

        return $this->reserveServiceSlot($slotId);
    }

    public function releaseBookingItemSlot($bookingItemId)
    {
        $slotId = $this->getBookingItemSlotId($bookingItemId);

        if (!$slotId) {
            return false;
        }

        return $this->releaseServiceSlot($slotId);
    }

    private function getBookingItemSlotId($bookingItemId)
    {
        $this->db->dbquery('SELECT slot_id FROM booking_items WHERE id = :id LIMIT 1');
        $this->db->dbbind(':id', (int)$bookingItemId);
        $item = $this->db->getsingledata();

        return empty($item['slot_id']) ? null : (int)$item['slot_id'];
    }

    public function reserveBookingSlots($bookingId)
    {
        $this->db->dbquery(
            'SELECT slot_id
             FROM booking_items
             WHERE booking_id = :booking_id
               AND slot_id IS NOT NULL
               AND status <> :cancelled_status'
        );
        $this->db->dbbind(':booking_id', (int)$bookingId);
        $this->db->dbbind(':cancelled_status', 'cancelled');
        $items = $this->db->getmultidata();

        foreach ($items as $item) {
            if (!$this->reserveServiceSlot((int)$item['slot_id'])) {
                return false;
            }
        }

        return true;
    }

    public function releaseBookingSlots($bookingId)
    {
        $this->db->dbquery(
            'SELECT slot_id
             FROM booking_items
             WHERE booking_id = :booking_id
               AND slot_id IS NOT NULL'
        );
        $this->db->dbbind(':booking_id', (int)$bookingId);
        $items = $this->db->getmultidata();

        foreach ($items as $item) {
            $this->releaseServiceSlot((int)$item['slot_id']);
        }

        return true;
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
        $priceMin = max(0, (float)($data['package_price'] ?? $data['price_min'] ?? $data['priceMin'] ?? $data['price'] ?? 0));
        $priceMax = max($priceMin, (float)($data['customize_price'] ?? $data['price_max'] ?? $data['priceMax'] ?? $priceMin));

        $this->db->dbbind(':supplier_id', (int)$supplierId);
        $this->db->dbbind(':category_id', $categoryId ? (int)$categoryId : null);
        $this->db->dbbind(':name', trim((string)($data['name'] ?? '')));
        $this->db->dbbind(':description', trim((string)($data['desc'] ?? $data['description'] ?? '')));
        $this->db->dbbind(':price', number_format($priceMin, 2, '.', ''), PDO::PARAM_STR);
        if ($this->hasServicePriceRangeColumns()) {
            $this->db->dbbind(':price_min', number_format($priceMin, 2, '.', ''), PDO::PARAM_STR);
            $this->db->dbbind(':price_max', number_format($priceMax, 2, '.', ''), PDO::PARAM_STR);
        }
        $this->db->dbbind(':thumbnail_url', $data['img'] ?? $data['thumbnail_url'] ?? null);
        $this->db->dbbind(':is_active', ($data['status'] ?? 'active') === 'inactive' ? 0 : 1);
        $this->db->dbbind(':booking_type', ($data['booking_type'] ?? '') === 'slot' || !empty($data['timeslot']) ? 'slot' : 'fullday');
        $this->db->dbbind(':duration_minutes', !empty($data['duration_minutes']) ? (int)$data['duration_minutes'] : null);
        $this->db->dbbind(':pricing_unit', $data['pricing_unit'] ?? 'per_session');
        $this->db->dbbind(':max_concurrent', max(1, min(65535, (int)($data['capacity'] ?? $data['max_concurrent'] ?? 1))));
        $this->db->dbbind(':min_lead_days', max(0, min(365, (int)($data['min_lead_days'] ?? 0))), PDO::PARAM_INT);
        if ($this->hasServiceDefaultTimeColumns()) {
            $startTime = !empty($data['default_start_time']) ? $data['default_start_time'] : null;
            $endTime   = !empty($data['default_end_time'])   ? $data['default_end_time']   : null;
            $this->db->dbbind(':default_start_time', $startTime);
            $this->db->dbbind(':default_end_time',   $endTime);
        }
    }

    private function applyVenueRoomPriceRange($data)
    {
        if (strtolower((string)($data['category'] ?? '')) !== 'venue' || empty($data['rooms']) || !is_array($data['rooms'])) {
            return $data;
        }

        $packagePrices = [];
        $customizePrices = [];
        $maxCapacity = 1;

        foreach ($data['rooms'] as $room) {
            if (!is_array($room)) {
                continue;
            }

            $packagePrice = max(0, (float)($room['package_price'] ?? $room['price_min'] ?? $room['price'] ?? 0));
            $customizePrice = max($packagePrice, (float)($room['customize_price'] ?? $room['price_max'] ?? $packagePrice));
            if ($packagePrice > 0) {
                $packagePrices[] = $packagePrice;
            }
            if ($customizePrice > 0) {
                $customizePrices[] = $customizePrice;
            }
            $maxCapacity = max($maxCapacity, (int)($room['capacity'] ?? 1));
        }

        if ($packagePrices) {
            $data['price'] = min($packagePrices);
            $data['price_min'] = min($packagePrices);
            $data['package_price'] = min($packagePrices);
        }

        if ($customizePrices) {
            $maxPrice = max($customizePrices);
            $data['price_max'] = max((float)($data['price_min'] ?? 0), $maxPrice);
            $data['customize_price'] = $data['price_max'];
        }

        $data['capacity'] = $maxCapacity;

        return $data;
    }

    private function applyRentalPriceRange($data)
    {
        $category = strtolower((string)($data['category'] ?? ''));
        if (!in_array($category, ['dress', 'accessories'], true)) {
            return $data;
        }

        $rental = is_array($data['rental_pricing'] ?? null) ? $data['rental_pricing'] : [];
        $borrowPackage = max(0, (float)($rental['borrow_package_price'] ?? $rental['borrow_price'] ?? 0));
        $borrowCustomize = max($borrowPackage, (float)($rental['borrow_customize_price'] ?? $rental['borrow_price'] ?? $borrowPackage));
        $buyPackage = max(0, (float)($rental['buy_package_price'] ?? $rental['buy_price'] ?? 0));
        $buyCustomize = max($buyPackage, (float)($rental['buy_customize_price'] ?? $rental['buy_price'] ?? $buyPackage));

        $packagePrices = array_values(array_filter([$borrowPackage, $buyPackage], static fn($price) => $price > 0));
        $customizePrices = array_values(array_filter([$borrowCustomize, $buyCustomize], static fn($price) => $price > 0));
        $priceMin = !empty($packagePrices) ? min($packagePrices) : 0;
        $priceMax = !empty($customizePrices) ? max($customizePrices) : $priceMin;

        $data['price'] = $priceMin;
        $data['price_min'] = $priceMin;
        $data['price_max'] = max($priceMin, $priceMax);
        $data['package_price'] = $data['price_min'];
        $data['customize_price'] = $data['price_max'];

        return $data;
    }

    private function applyDecorationStylePriceRange($data)
    {
        if (strtolower((string)($data['category'] ?? '')) !== 'decoration') {
            return $data;
        }

        $styles = is_array($data['decoration_styles'] ?? null) ? $data['decoration_styles'] : [];
        $prices = [];
        foreach ($styles as $style) {
            if (!is_array($style) || trim((string)($style['name'] ?? '')) === '') {
                continue;
            }

            $price = max(0, (float)($style['price'] ?? 0));
            if ($price > 0) {
                $prices[] = $price;
            }
        }

        if (!empty($prices)) {
            $data['price'] = min($prices);
            $data['price_min'] = min($prices);
            $data['price_max'] = max($prices);
            $data['package_price'] = $data['price_min'];
            $data['customize_price'] = $data['price_max'];
        }

        return $data;
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
        $priceMin = (float)($service['price_min'] ?? $service['price'] ?? 0);
        $priceMax = max($priceMin, (float)($service['price_max'] ?? $priceMin));
        $venueName = trim((string)($service['venue_name'] ?? ''));

        $category = strtolower((string)($service['category'] ?? ''));

        return [
            'id' => (int)$service['id'],
            'name' => $service['name'] ?? '',
            'price' => (float)($service['price'] ?? $priceMin),
            'price_min' => $priceMin,
            'price_max' => $priceMax,
            'package_price' => $priceMin,
            'customize_price' => $priceMax,
            'category' => $service['category'] ?: 'Others',
            'status' => !empty($service['is_active']) ? 'active' : 'inactive',
            'desc' => $service['description'] ?? '',
            'img' => $service['thumbnail_url'] ?? '',
            'capacity' => (int)($service['max_concurrent'] ?? 1),
            'duration_minutes' => (int)($service['duration_minutes'] ?? 60),
            'buffer_minutes' => (int)($service['buffer_minutes'] ?? 0),
            'timeslot' => ($service['booking_type'] ?? '') === 'slot' ? 'Custom slot' : '',
            'min_lead_days' => (int)($service['min_lead_days'] ?? 0),
            'venue_id' => isset($service['venue_id']) ? (int)$service['venue_id'] : null,
            'venue' => $venueName,
            'venue_name' => $venueName,
            'venue_location' => $service['venue_location'] ?? '',
            'venue_rooms' => !empty($service['venue_id']) ? $this->getVenueRooms((int)$service['venue_id']) : [],
            'decoration_styles' => $category === 'decoration' ? $this->getDecorationStyles((int)$service['id']) : [],
            'rental_pricing' => in_array($category, ['dress', 'accessories'], true) ? $this->getRentalPricing((int)$service['id']) : null,
        ];
    }

    private function saveVenueDetails($supplierId, $serviceId, $data)
    {
        if (!$this->hasVenueServiceColumn() || strtolower((string)($data['category'] ?? '')) !== 'venue') {
            return;
        }

        $serviceName = trim((string)($data['name'] ?? 'Venue'));
        $venueName = trim((string)($data['venue'] ?? $data['venue_name'] ?? ''));
        $location = trim((string)($data['venue_location'] ?? $data['location'] ?? ''));
        $description = trim((string)($data['desc'] ?? $data['description'] ?? ''));
        $capacity = max(1, (int)($data['capacity'] ?? $data['max_concurrent'] ?? 1));
        $price = max(0, (float)($data['price_min'] ?? $data['price'] ?? 0));
        $replaceRooms = !empty($data['rooms_replace']);
        $rooms = $this->normalizeVenueRooms($data['rooms'] ?? [], $serviceName, $capacity, $price);
        $hasRoomPriceRange = $this->hasVenueRoomPriceRangeColumns();

        if ($venueName === '') {
            $venueName = $serviceName;
        }

        $this->db->dbquery(
            'SELECT id
             FROM venues
             WHERE service_id = :service_id
               AND supplier_id = :supplier_id
             LIMIT 1'
        );
        $this->db->dbbind(':service_id', (int)$serviceId);
        $this->db->dbbind(':supplier_id', (int)$supplierId);
        $venue = $this->db->getsingledata();

        if ($venue) {
            $venueId = (int)$venue['id'];
            $this->db->dbquery(
                'UPDATE venues
                 SET name = :name,
                     location = :location,
                     description = :description
                 WHERE id = :id
                   AND supplier_id = :supplier_id'
            );
            $this->db->dbbind(':id', $venueId);
        } else {
            $this->db->dbquery(
                'INSERT INTO venues(service_id, supplier_id, name, location, description)
                 VALUES(:service_id, :supplier_id, :name, :location, :description)'
            );
            $this->db->dbbind(':service_id', (int)$serviceId);
        }

        $this->db->dbbind(':supplier_id', (int)$supplierId);
        $this->db->dbbind(':name', $venueName);
        $this->db->dbbind(':location', $location !== '' ? $location : null);
        $this->db->dbbind(':description', $description !== '' ? $description : null);
        $this->db->dbexecute();

        if (empty($venueId)) {
            $venueId = (int)$this->db->lastinsertid();
        }

        $submittedRoomIds = [];

        foreach ($rooms as $room) {
            $hasRoomPhoto = $this->hasVenueRoomPhotoColumn();
            if (!empty($room['id'])) {
                $roomId = (int)$room['id'];
                $roomPriceRangeUpdate = $hasRoomPriceRange
                    ? ',
                         price_min = :price_min,
                         price_max = :price_max'
                    : '';
                $roomPhotoUpdate = $hasRoomPhoto && $room['photo_url'] !== null ? ', photo_url = :photo_url' : '';
                $this->db->dbquery(
                    'UPDATE venue_rooms
                     SET name = :name,
                         capacity = :capacity,
                         price = :price' . $roomPriceRangeUpdate . ',
                         min_lead_days = :min_lead_days' . $roomPhotoUpdate . '
                     WHERE id = :id
                       AND venue_id = :venue_id'
                );
                $this->db->dbbind(':id', $roomId);
                $submittedRoomIds[] = $roomId;
            } else {
                $roomPriceRangeColumns = $hasRoomPriceRange ? ', price_min, price_max' : '';
                $roomPriceRangeValues = $hasRoomPriceRange ? ', :price_min, :price_max' : '';
                $roomPhotoColumns = $hasRoomPhoto && $room['photo_url'] !== null ? ', photo_url' : '';
                $roomPhotoValues = $hasRoomPhoto && $room['photo_url'] !== null ? ', :photo_url' : '';
                $this->db->dbquery(
                    'INSERT INTO venue_rooms(venue_id, name, capacity, price' . $roomPriceRangeColumns . ', min_lead_days' . $roomPhotoColumns . ')
                     VALUES(:venue_id, :name, :capacity, :price' . $roomPriceRangeValues . ', :min_lead_days' . $roomPhotoValues . ')'
                );
            }

            $this->db->dbbind(':venue_id', $venueId);
            $this->db->dbbind(':name', $room['name']);
            $this->db->dbbind(':capacity', $room['capacity']);
            $this->db->dbbind(':price', number_format($room['price'], 2, '.', ''), PDO::PARAM_STR);
            if ($hasRoomPriceRange) {
                $this->db->dbbind(':price_min', number_format($room['price_min'], 2, '.', ''), PDO::PARAM_STR);
                $this->db->dbbind(':price_max', number_format($room['price_max'], 2, '.', ''), PDO::PARAM_STR);
            }
            $minLeadDays = !empty($room['min_lead_days']) ? max(0, min(365, (int)$room['min_lead_days'])) : null;
            $this->db->dbbind(':min_lead_days', $minLeadDays, $minLeadDays === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
            if ($hasRoomPhoto && $room['photo_url'] !== null) {
                $this->db->dbbind(':photo_url', $room['photo_url'] ?? null);
            }
            $this->db->dbexecute();

            if (empty($room['id'])) {
                $roomId = (int)$this->db->lastinsertid();
                $submittedRoomIds[] = $roomId;
            }

            $this->saveVenueRoomAvailability(
                $roomId,
                $room['start_time'] ?? '09:00',
                $room['end_time'] ?? '17:00'
            );
        }

        if ($replaceRooms) {
            $this->deleteRemovedVenueRooms($venueId, $submittedRoomIds);
        }
    }

    private function normalizeVenueRooms($rooms, $serviceName, $defaultCapacity, $defaultPrice)
    {
        $normalized = [];
        $rows = is_array($rooms) ? $rooms : [];

        foreach ($rows as $room) {
            if (!is_array($room)) {
                continue;
            }

            $name = trim((string)($room['name'] ?? ''));
            $capacity = max(1, (int)($room['capacity'] ?? $defaultCapacity));
            $priceMin = max(0, (float)($room['package_price'] ?? $room['price_min'] ?? $room['price'] ?? $defaultPrice));
            $priceMax = max($priceMin, (float)($room['customize_price'] ?? $room['price_max'] ?? $priceMin));
            $minLeadDaysRaw = trim((string)($room['min_lead_days'] ?? ''));
            $minLeadDays = $minLeadDaysRaw === '' ? null : max(0, min(365, (int)$minLeadDaysRaw));

            if ($name === '' && $capacity <= 1 && $priceMin <= 0 && $priceMax <= 0) {
                continue;
            }

            $normalized[] = [
                'id' => !empty($room['id']) ? (int)$room['id'] : null,
                'name' => $name !== '' ? $name : $serviceName,
                'capacity' => $capacity,
                'price' => $priceMin,
                'price_min' => $priceMin,
                'price_max' => $priceMax,
                'start_time' => $this->normalizeTime($room['start_time'] ?? '09:00'),
                'end_time' => $this->normalizeTime($room['end_time'] ?? '17:00'),
                'min_lead_days' => $minLeadDays,
                'photo_url' => trim((string)($room['photo_url'] ?? '')) ?: null,
            ];
        }

        return $normalized;
    }

    private function saveVenueRoomAvailability($roomId, $startTime = '09:00', $endTime = '17:00')
    {
        $startTime = $this->normalizeTime($startTime ?: '09:00');
        $endTime = $this->normalizeTime($endTime ?: '17:00');

        if ($startTime === $endTime) {
            $startTime = '09:00:00';
            $endTime = '17:00:00';
        }

        $this->db->dbquery('DELETE FROM venue_room_availability WHERE room_id = :room_id AND date IS NULL');
        $this->db->dbbind(':room_id', (int)$roomId);
        $this->db->dbexecute();

        $this->db->dbquery(
            'INSERT INTO venue_room_availability(room_id, date, start_time, end_time, is_available)
             VALUES(:room_id, NULL, :start_time, :end_time, 1)'
        );
        $this->db->dbbind(':room_id', (int)$roomId);
        $this->db->dbbind(':start_time', $startTime);
        $this->db->dbbind(':end_time', $endTime);
        $this->db->dbexecute();
    }

    private function deleteRemovedVenueRooms($venueId, $keepIds)
    {
        $this->db->dbquery('SELECT id FROM venue_rooms WHERE venue_id = :venue_id');
        $this->db->dbbind(':venue_id', (int)$venueId);
        $existing = $this->db->getmultidata();
        $keepIds = array_map('intval', $keepIds);

        foreach ($existing as $room) {
            $roomId = (int)$room['id'];
            if (in_array($roomId, $keepIds, true)) {
                continue;
            }

            $this->db->dbquery('SELECT COUNT(*) AS total FROM booking_items WHERE venue_room_id = :room_id');
            $this->db->dbbind(':room_id', $roomId);
            $usage = $this->db->getsingledata();

            if ((int)($usage['total'] ?? 0) > 0) {
                continue;
            }

            $this->db->dbquery('DELETE FROM venue_room_availability WHERE room_id = :room_id');
            $this->db->dbbind(':room_id', $roomId);
            $this->db->dbexecute();

            $this->db->dbquery('DELETE FROM venue_rooms WHERE id = :room_id AND venue_id = :venue_id');
            $this->db->dbbind(':room_id', $roomId);
            $this->db->dbbind(':venue_id', (int)$venueId);
            $this->db->dbexecute();
        }
    }

    private function getVenueRooms($venueId)
    {
        $roomPriceRangeSelect = $this->hasVenueRoomPriceRangeColumns()
            ? 'venue_rooms.price_min, venue_rooms.price_max,'
            : 'venue_rooms.price AS price_min, venue_rooms.price AS price_max,';
        $roomPriceRangeGroupBy = $this->hasVenueRoomPriceRangeColumns()
            ? ', venue_rooms.price_min, venue_rooms.price_max'
            : '';
        $roomPhotoSelect = $this->hasVenueRoomPhotoColumn() ? 'venue_rooms.photo_url,' : "'' AS photo_url,";
        $this->db->dbquery(
            'SELECT venue_rooms.id,
                    venue_rooms.name,
                    venue_rooms.capacity,
                    venue_rooms.price,
                    ' . $roomPriceRangeSelect . '
                    venue_rooms.min_lead_days,
                    ' . $roomPhotoSelect . '
                    MIN(CASE WHEN venue_room_availability.is_available = 1 THEN venue_room_availability.date END) AS available_from,
                    MAX(CASE WHEN venue_room_availability.is_available = 1 THEN venue_room_availability.date END) AS available_to,
                    MIN(CASE WHEN venue_room_availability.is_available = 1 THEN venue_room_availability.start_time END) AS start_time,
                    MAX(CASE WHEN venue_room_availability.is_available = 1 THEN venue_room_availability.end_time END) AS end_time
             FROM venue_rooms
             LEFT JOIN venue_room_availability ON venue_room_availability.room_id = venue_rooms.id
             WHERE venue_id = :venue_id
             GROUP BY venue_rooms.id, venue_rooms.name, venue_rooms.capacity, venue_rooms.price' . $roomPriceRangeGroupBy . '
             ORDER BY venue_rooms.id ASC'
        );
        $this->db->dbbind(':venue_id', (int)$venueId);

        $rooms = array_map(function ($room) {
            return [
                'id' => (int)$room['id'],
                'name' => $room['name'] ?? '',
                'capacity' => (int)($room['capacity'] ?? 1),
                'price' => (float)($room['price'] ?? 0),
                'price_min' => (float)($room['price_min'] ?? $room['price'] ?? 0),
                'price_max' => max((float)($room['price_min'] ?? $room['price'] ?? 0), (float)($room['price_max'] ?? $room['price_min'] ?? $room['price'] ?? 0)),
                'min_lead_days' => $room['min_lead_days'] !== null ? (int)$room['min_lead_days'] : null,
                'photo_url' => $room['photo_url'] ?? null,
                'available_from' => $room['available_from'] ?? '',
                'available_to' => $room['available_to'] ?? '',
                'start_time' => $room['start_time'] ?? '09:00:00',
                'end_time' => $room['end_time'] ?? '17:00:00',
                'overrides' => [],
            ];
        }, $this->db->getmultidata());

        $overrides = $this->getVenueRoomOverrides($venueId);
        foreach ($rooms as &$room) {
            $room['overrides'] = $overrides[$room['id']] ?? [];
        }
        unset($room);

        return $rooms;
    }

    private function getVenueRoomOverrides($venueId)
    {
        $this->db->dbquery(
            'SELECT venue_room_availability.id,
                    venue_room_availability.room_id,
                    venue_room_availability.date,
                    venue_room_availability.start_time,
                    venue_room_availability.end_time,
                    venue_room_availability.is_available
             FROM venue_room_availability
             INNER JOIN venue_rooms ON venue_rooms.id = venue_room_availability.room_id
             WHERE venue_rooms.venue_id = :venue_id
               AND venue_room_availability.date IS NOT NULL
               AND venue_room_availability.date >= CURDATE()
             ORDER BY venue_room_availability.date ASC, venue_room_availability.id ASC'
        );
        $this->db->dbbind(':venue_id', (int)$venueId);

        $rows = [];
        foreach ($this->db->getmultidata() as $row) {
            $roomId = (int)($row['room_id'] ?? 0);
            if ($roomId <= 0) {
                continue;
            }
            $rows[$roomId][] = [
                'id' => (int)$row['id'],
                'room_id' => $roomId,
                'date' => $row['date'] ?? '',
                'type' => !empty($row['is_available']) ? 'custom_hours' : 'unavailable',
                'open_time' => $row['start_time'] ?? '09:00:00',
                'close_time' => $row['end_time'] ?? '17:00:00',
                'is_available' => (int)($row['is_available'] ?? 0),
            ];
        }

        return $rows;
    }

    public function saveVenueRoomDateOverride($supplierId, $serviceId, $data)
    {
        $roomId = (int)($data['room_id'] ?? 0);
        $date = $this->normalizeDate($data['date'] ?? '');
        $type = in_array($data['type'] ?? '', ['available', 'unavailable', 'custom_hours'], true) ? $data['type'] : 'unavailable';
        $isAvailable = $type === 'unavailable' ? 0 : 1;
        $openTime = $isAvailable ? $this->normalizeTime($data['open_time'] ?? '09:00') : null;
        $closeTime = $isAvailable ? $this->normalizeTime($data['close_time'] ?? '17:00') : null;

        if ($roomId <= 0 || !$date || !$this->supplierOwnsVenueRoom($supplierId, $serviceId, $roomId)) {
            return null;
        }

        if ($openTime !== null && $closeTime !== null && $openTime >= $closeTime) {
            return null;
        }

        $this->db->dbquery('DELETE FROM venue_room_availability WHERE room_id = :room_id AND date = :date');
        $this->db->dbbind(':room_id', $roomId);
        $this->db->dbbind(':date', $date);
        $this->db->dbexecute();

        $this->db->dbquery(
            'INSERT INTO venue_room_availability(room_id, date, start_time, end_time, is_available)
             VALUES(:room_id, :date, :start_time, :end_time, :is_available)'
        );
        $this->db->dbbind(':room_id', $roomId);
        $this->db->dbbind(':date', $date);
        $this->db->dbbind(':start_time', $openTime);
        $this->db->dbbind(':end_time', $closeTime);
        $this->db->dbbind(':is_available', $isAvailable);
        $this->db->dbexecute();

        return $this->getServiceDetail($supplierId, $serviceId);
    }

    public function deleteVenueRoomDateOverride($supplierId, $serviceId, $overrideId)
    {
        $overrideId = (int)$overrideId;
        if ($overrideId <= 0) {
            return false;
        }

        $this->db->dbquery(
            'DELETE venue_room_availability
             FROM venue_room_availability
             INNER JOIN venue_rooms ON venue_rooms.id = venue_room_availability.room_id
             INNER JOIN venues ON venues.id = venue_rooms.venue_id
             WHERE venue_room_availability.id = :id
               AND venues.service_id = :service_id
               AND venues.supplier_id = :supplier_id
               AND venue_room_availability.date IS NOT NULL'
        );
        $this->db->dbbind(':id', $overrideId);
        $this->db->dbbind(':service_id', (int)$serviceId);
        $this->db->dbbind(':supplier_id', (int)$supplierId);

        return $this->db->dbexecute();
    }

    private function supplierOwnsVenueRoom($supplierId, $serviceId, $roomId)
    {
        $this->db->dbquery(
            'SELECT venue_rooms.id
             FROM venue_rooms
             INNER JOIN venues ON venues.id = venue_rooms.venue_id
             WHERE venue_rooms.id = :room_id
               AND venues.service_id = :service_id
               AND venues.supplier_id = :supplier_id
             LIMIT 1'
        );
        $this->db->dbbind(':room_id', (int)$roomId);
        $this->db->dbbind(':service_id', (int)$serviceId);
        $this->db->dbbind(':supplier_id', (int)$supplierId);

        return (bool)$this->db->getsingledata();
    }

    private function servicePriceRangeSelectFields()
    {
        return $this->hasServicePriceRangeColumns()
            ? 'services.price_min, services.price_max,'
            : 'services.price AS price_min, services.price AS price_max,';
    }

    private function serviceDefaultTimeSelectFields()
    {
        return $this->hasServiceDefaultTimeColumns()
            ? 'services.default_start_time, services.default_end_time,'
            : 'NULL AS default_start_time, NULL AS default_end_time,';
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

    private function hasServiceDefaultTimeColumns(): bool
    {
        if ($this->hasServiceDefaultTimeColumns !== null) {
            return $this->hasServiceDefaultTimeColumns;
        }

        $this->db->dbquery(
            'SELECT COUNT(*) AS total
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = "services"
               AND COLUMN_NAME IN ("default_start_time", "default_end_time")'
        );
        $row = $this->db->getsingledata();
        $this->hasServiceDefaultTimeColumns = (int)($row['total'] ?? 0) >= 2;

        return $this->hasServiceDefaultTimeColumns;
    }

    private function hasVenueRoomPriceRangeColumns()
    {
        if ($this->hasVenueRoomPriceRangeColumns !== null) {
            return $this->hasVenueRoomPriceRangeColumns;
        }

        $this->db->dbquery(
            'SELECT COUNT(*) AS total
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = "venue_rooms"
               AND COLUMN_NAME IN ("price_min", "price_max")'
        );
        $row = $this->db->getsingledata();
        $this->hasVenueRoomPriceRangeColumns = (int)($row['total'] ?? 0) >= 2;

        return $this->hasVenueRoomPriceRangeColumns;
    }

    private function hasRentalPriceMatrixColumns()
    {
        if ($this->hasRentalPriceMatrixColumns !== null) {
            return $this->hasRentalPriceMatrixColumns;
        }

        $this->db->dbquery(
            'SELECT COUNT(*) AS total
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = "service_rental_pricing"
               AND COLUMN_NAME IN (
                    "borrow_package_price",
                    "borrow_customize_price",
                    "buy_package_price",
                    "buy_customize_price"
               )'
        );
        $row = $this->db->getsingledata();
        $this->hasRentalPriceMatrixColumns = (int)($row['total'] ?? 0) >= 4;

        return $this->hasRentalPriceMatrixColumns;
    }

    private function rentalPricingColumns(): array
    {
        if ($this->rentalPricingColumns !== null) {
            return $this->rentalPricingColumns;
        }

        $this->db->dbquery(
            'SELECT COLUMN_NAME
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = "service_rental_pricing"'
        );
        $rows = $this->db->getmultidata();
        $this->rentalPricingColumns = array_map(static fn($row) => (string)($row['COLUMN_NAME'] ?? ''), $rows);

        return $this->rentalPricingColumns;
    }

    private function venueSelectFields()
    {
        if (!$this->hasVenueServiceColumn()) {
            return 'NULL AS venue_id, NULL AS venue_name, NULL AS venue_location,';
        }

        return 'venues.id AS venue_id,
                venues.name AS venue_name,
                venues.location AS venue_location,';
    }

    private function venueJoinClause()
    {
        if (!$this->hasVenueServiceColumn()) {
            return '';
        }

        return 'LEFT JOIN venues ON venues.service_id = services.id';
    }

    private function hasVenueServiceColumn()
    {
        if ($this->hasVenueServiceColumn !== null) {
            return $this->hasVenueServiceColumn;
        }

        $this->db->dbquery(
            'SELECT COUNT(*) AS total
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = "venues"
               AND COLUMN_NAME = "service_id"'
        );
        $row = $this->db->getsingledata();
        $this->hasVenueServiceColumn = (int)($row['total'] ?? 0) > 0;

        return $this->hasVenueServiceColumn;
    }

    private function normalizeDate($date)
    {
        $timestamp = strtotime((string)$date);

        return $timestamp ? date('Y-m-d', $timestamp) : null;
    }

    private function calendarWeeklySchedule($serviceId)
    {
        $this->db->dbquery(
            'SELECT day_of_week, open_time, close_time, is_available
             FROM service_schedules
             WHERE service_id = :service_id'
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

    private function calendarDateOverrides($serviceId, $startDate, $endDate)
    {
        $this->db->dbquery(
            'SELECT id, date, type, open_time, close_time, reason
             FROM service_availability
             WHERE service_id = :service_id
               AND date BETWEEN :start_date AND :end_date'
        );
        $this->db->dbbind(':service_id', (int)$serviceId);
        $this->db->dbbind(':start_date', $startDate);
        $this->db->dbbind(':end_date', $endDate);

        $overrides = [];
        foreach ($this->db->getmultidata() as $row) {
            $overrides[$row['date']] = [
                'id' => (int)$row['id'],
                'date' => $row['date'],
                'type' => $row['type'],
                'open_time' => $row['open_time'],
                'close_time' => $row['close_time'],
                'reason' => $row['reason'] ?? '',
            ];
        }

        return $overrides;
    }

    private function calendarBookings($serviceId, $supplierId, $startDate, $endDate)
    {
        $this->db->dbquery(
            "SELECT booking_items.id,
                    booking_items.booking_id,
                    DATE(booking_items.booking_date) AS booking_day,
                    booking_items.start_time,
                    booking_items.end_time,
                    booking_items.status,
                    booking_suppliers.status AS supplier_status,
                    users.name AS customer_name
             FROM booking_items
             LEFT JOIN bookings ON bookings.id = booking_items.booking_id
             LEFT JOIN users ON users.user_id = bookings.user_id
             LEFT JOIN booking_suppliers
                    ON booking_suppliers.booking_id = booking_items.booking_id
                   AND booking_suppliers.supplier_id = :supplier_id
             WHERE booking_items.item_type = 'service'
               AND booking_items.item_id = :service_id
               AND DATE(booking_items.booking_date) BETWEEN :start_date AND :end_date
               AND COALESCE(booking_items.status, '') <> 'cancelled'"
        );
        $this->db->dbbind(':supplier_id', (int)$supplierId);
        $this->db->dbbind(':service_id', (int)$serviceId);
        $this->db->dbbind(':start_date', $startDate);
        $this->db->dbbind(':end_date', $endDate);

        $bookings = [];
        foreach ($this->db->getmultidata() as $row) {
            $date = $row['booking_day'] ?? '';

            if ($date === '') {
                continue;
            }

            $bookings[$date][] = [
                'id' => (int)$row['id'],
                'booking_id' => (int)$row['booking_id'],
                'customer_name' => $row['customer_name'] ?? 'Customer',
                'start_time' => $row['start_time'],
                'end_time' => $row['end_time'],
                'status' => $row['status'] ?? 'pending',
                'supplier_status' => $row['supplier_status'] ?? 'pending',
            ];
        }

        return $bookings;
    }

    private function hasOpenWeeklySchedule(array $weekly)
    {
        foreach ($weekly as $row) {
            if (empty($row['is_available'])) {
                continue;
            }

            $open = strtotime($this->normalizeTime($row['open_time'] ?? ''));
            $close = strtotime($this->normalizeTime($row['close_time'] ?? ''));

            if ($open && $close && $open < $close) {
                return true;
            }
        }

        return false;
    }

    private function hasValidVenueRooms(array $venueRooms)
    {
        foreach ($venueRooms as $room) {
            $start = (string)($room['start_time'] ?? '');
            $end = (string)($room['end_time'] ?? '');

            if (
                trim((string)($room['name'] ?? '')) !== '' &&
                (int)($room['capacity'] ?? 0) > 0 &&
                (float)($room['price'] ?? 0) > 0 &&
                $start !== '' &&
                $end !== '' &&
                $start !== $end
            ) {
                return true;
            }
        }

        return false;
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

    private function ensureServiceTimeSlot($serviceId, $date, $startTime, $endTime, $maxConcurrent)
    {
        $startTime = strlen($startTime) === 5 ? $startTime . ':00' : $startTime;
        $endTime = strlen($endTime) === 5 ? $endTime . ':00' : $endTime;
        $maxConcurrent = max(1, (int)$maxConcurrent);

        $this->db->dbquery(
            'SELECT id, confirmed_count, max_concurrent, status
             FROM service_time_slots
             WHERE service_id = :service_id
               AND date = :date
               AND start_time = :start_time
               AND end_time = :end_time
             LIMIT 1'
        );
        $this->db->dbbind(':service_id', (int)$serviceId);
        $this->db->dbbind(':date', $date);
        $this->db->dbbind(':start_time', $startTime);
        $this->db->dbbind(':end_time', $endTime);
        $slot = $this->db->getsingledata();

        if ($slot) {
            if ((int)$slot['max_concurrent'] !== $maxConcurrent) {
                $status = (int)$slot['confirmed_count'] >= $maxConcurrent ? 'full' : 'available';
                $this->db->dbquery(
                    'UPDATE service_time_slots
                     SET max_concurrent = :max_concurrent,
                         status = :status
                     WHERE id = :id'
                );
                $this->db->dbbind(':max_concurrent', $maxConcurrent);
                $this->db->dbbind(':status', $status);
                $this->db->dbbind(':id', (int)$slot['id']);
                $this->db->dbexecute();

                $slot['max_concurrent'] = $maxConcurrent;
                $slot['status'] = $status;
            }

            return [
                'id' => (int)$slot['id'],
                'start_time' => substr($startTime, 0, 5),
                'end_time' => substr($endTime, 0, 5),
                'confirmed_count' => (int)$slot['confirmed_count'],
                'max_concurrent' => (int)$slot['max_concurrent'],
                'status' => $slot['status'],
            ];
        }

        $this->db->dbquery(
            'INSERT INTO service_time_slots(service_id, date, start_time, end_time, confirmed_count, max_concurrent, status)
             VALUES(:service_id, :date, :start_time, :end_time, :confirmed_count, :max_concurrent, :status)'
        );
        $this->db->dbbind(':service_id', (int)$serviceId);
        $this->db->dbbind(':date', $date);
        $this->db->dbbind(':start_time', $startTime);
        $this->db->dbbind(':end_time', $endTime);
        $this->db->dbbind(':confirmed_count', 0);
        $this->db->dbbind(':max_concurrent', $maxConcurrent);
        $this->db->dbbind(':status', 'available');
        $this->db->dbexecute();

        return [
            'id' => (int)$this->db->lastinsertid(),
            'start_time' => substr($startTime, 0, 5),
            'end_time' => substr($endTime, 0, 5),
            'confirmed_count' => 0,
            'max_concurrent' => $maxConcurrent,
            'status' => 'available',
        ];
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

    private function hasVenueRoomPhotoColumn(): bool
    {
        static $checked = null;
        if ($checked !== null) {
            return $checked;
        }
        try {
            $this->db->dbquery("SHOW COLUMNS FROM venue_rooms LIKE 'photo_url'");
            $checked = (bool)$this->db->getsingledata();
        } catch (Throwable $e) {
            $checked = false;
        }

        return $checked;
    }

    private function saveDecorationStyles(int $serviceId, array $data): void
    {
        $category = strtolower((string)($data['category'] ?? ''));
        if ($category !== 'decoration') {
            return;
        }

        $styles = is_array($data['decoration_styles'] ?? null) ? $data['decoration_styles'] : [];
        $hasPhoto = $this->hasDecorationStylePhotoColumn();

        $this->db->dbquery('DELETE FROM decoration_styles WHERE service_id = :service_id');
        $this->db->dbbind(':service_id', $serviceId);
        $this->db->dbexecute();

        $sort = 0;
        foreach ($styles as $style) {
            $name = trim((string)($style['name'] ?? ''));
            $price = max(0, (float)($style['price'] ?? 0));
            if ($name === '') {
                continue;
            }
            $photoUrl = trim((string)($style['photo_url'] ?? ''));
            $photoColumn = $hasPhoto ? ', photo_url' : '';
            $photoValue = $hasPhoto ? ', :photo_url' : '';
            $this->db->dbquery(
                'INSERT INTO decoration_styles (service_id, name, price' . $photoColumn . ', sort_order)
                 VALUES (:service_id, :name, :price' . $photoValue . ', :sort_order)'
            );
            $this->db->dbbind(':service_id', $serviceId);
            $this->db->dbbind(':name', $name);
            $this->db->dbbind(':price', number_format($price, 2, '.', ''), PDO::PARAM_STR);
            if ($hasPhoto) {
                $this->db->dbbind(':photo_url', $photoUrl !== '' ? $photoUrl : null);
            }
            $this->db->dbbind(':sort_order', $sort++, PDO::PARAM_INT);
            $this->db->dbexecute();
        }
    }

    private function getDecorationStyles(int $serviceId): array
    {
        $photoSelect = $this->hasDecorationStylePhotoColumn() ? 'photo_url' : "'' AS photo_url";
        $this->db->dbquery(
            'SELECT id, name, price, ' . $photoSelect . '
             FROM decoration_styles
             WHERE service_id = :service_id
             ORDER BY sort_order ASC, id ASC'
        );
        $this->db->dbbind(':service_id', $serviceId);

        return array_map(function ($row) {
            return [
                'id' => (int)$row['id'],
                'name' => $row['name'] ?? '',
                'price' => (float)$row['price'],
                'photo_url' => trim((string)($row['photo_url'] ?? '')),
            ];
        }, $this->db->getmultidata());
    }

    private function hasDecorationStylePhotoColumn(): bool
    {
        if ($this->hasDecorationStylePhotoColumn !== null) {
            return $this->hasDecorationStylePhotoColumn;
        }

        try {
            $this->db->dbquery("SHOW COLUMNS FROM decoration_styles LIKE 'photo_url'");
            $this->hasDecorationStylePhotoColumn = (bool)$this->db->getsingledata();
        } catch (Throwable $e) {
            $this->hasDecorationStylePhotoColumn = false;
        }

        return $this->hasDecorationStylePhotoColumn;
    }

    private function saveRentalPricing(int $serviceId, array $data): void
    {
        $category = strtolower((string)($data['category'] ?? ''));
        if (!in_array($category, ['dress', 'accessories'], true)) {
            return;
        }

        $rental = is_array($data['rental_pricing'] ?? null) ? $data['rental_pricing'] : [];
        $borrowPackagePrice = ($rental['borrow_package_price'] ?? $rental['borrow_price'] ?? 0) > 0 ? (float)($rental['borrow_package_price'] ?? $rental['borrow_price']) : null;
        $borrowCustomizePrice = ($rental['borrow_customize_price'] ?? 0) > 0 ? (float)$rental['borrow_customize_price'] : $borrowPackagePrice;
        $returnDays = $borrowPackagePrice !== null && ($rental['return_days'] ?? 0) > 0 ? (int)$rental['return_days'] : null;
        $buyPackagePrice = ($rental['buy_package_price'] ?? $rental['buy_price'] ?? 0) > 0 ? (float)($rental['buy_package_price'] ?? $rental['buy_price']) : null;
        $buyCustomizePrice = ($rental['buy_customize_price'] ?? 0) > 0 ? (float)$rental['buy_customize_price'] : $buyPackagePrice;
        $borrowCustomizePrice = $borrowCustomizePrice !== null ? max($borrowPackagePrice ?? 0, $borrowCustomizePrice) : null;
        $buyCustomizePrice = $buyCustomizePrice !== null ? max($buyPackagePrice ?? 0, $buyCustomizePrice) : null;

        $this->db->dbquery('SELECT id FROM service_rental_pricing WHERE service_id = :service_id LIMIT 1');
        $this->db->dbbind(':service_id', $serviceId);
        $existing = $this->db->getsingledata();

        $values = [
            'borrow_package_price' => $borrowPackagePrice,
            'borrow_customize_price' => $borrowCustomizePrice,
            'borrow_price' => $borrowPackagePrice,
            'return_days' => $returnDays,
            'buy_package_price' => $buyPackagePrice,
            'buy_customize_price' => $buyCustomizePrice,
            'buy_price' => $buyPackagePrice,
        ];
        $values = array_intersect_key($values, array_flip($this->rentalPricingColumns()));

        if (empty($values)) {
            return;
        }

        if ($existing) {
            $sets = [];
            foreach (array_keys($values) as $column) {
                $sets[] = $column . ' = :' . $column;
            }
            $this->db->dbquery(
                'UPDATE service_rental_pricing
                 SET ' . implode(', ', $sets) . '
                 WHERE service_id = :service_id'
            );
        } else {
            $columns = array_merge(['service_id'], array_keys($values));
            $placeholders = array_map(static fn($column) => ':' . $column, $columns);
            $this->db->dbquery(
                'INSERT INTO service_rental_pricing (' . implode(', ', $columns) . ')
                 VALUES (' . implode(', ', $placeholders) . ')'
            );
        }

        $this->db->dbbind(':service_id', $serviceId);
        foreach ($values as $column => $value) {
            $this->db->dbbind(':' . $column, $value, $value === null ? PDO::PARAM_NULL : null);
        }
        $this->db->dbexecute();
    }

    private function getRentalPricing(int $serviceId): ?array
    {
        $columns = array_values(array_intersect($this->rentalPricingColumns(), [
            'borrow_package_price',
            'borrow_customize_price',
            'borrow_price',
            'return_days',
            'buy_package_price',
            'buy_customize_price',
            'buy_price',
        ]));

        if (empty($columns)) {
            return null;
        }

        $this->db->dbquery(
            'SELECT ' . implode(', ', $columns) . '
             FROM service_rental_pricing
             WHERE service_id = :service_id
             LIMIT 1'
        );
        $this->db->dbbind(':service_id', $serviceId);
        $row = $this->db->getsingledata();

        if (!$row) {
            return null;
        }

        $borrowPackage = $row['borrow_package_price'] ?? $row['borrow_price'] ?? null;
        $borrowCustomize = $row['borrow_customize_price'] ?? $row['borrow_price'] ?? $borrowPackage;
        $buyPackage = $row['buy_package_price'] ?? $row['buy_price'] ?? null;
        $buyCustomize = $row['buy_customize_price'] ?? $row['buy_price'] ?? $buyPackage;

        return [
            'borrow_package_price' => $borrowPackage !== null ? (float)$borrowPackage : null,
            'borrow_customize_price' => $borrowCustomize !== null ? (float)$borrowCustomize : null,
            'borrow_price' => ($row['borrow_price'] ?? $borrowPackage) !== null ? (float)($row['borrow_price'] ?? $borrowPackage) : null,
            'return_days' => ($row['return_days'] ?? null) !== null ? (int)$row['return_days'] : null,
            'buy_package_price' => $buyPackage !== null ? (float)$buyPackage : null,
            'buy_customize_price' => $buyCustomize !== null ? (float)$buyCustomize : null,
            'buy_price' => ($row['buy_price'] ?? $buyPackage) !== null ? (float)($row['buy_price'] ?? $buyPackage) : null,
        ];
    }
}
