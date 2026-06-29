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
    private $tableExistsCache = [];

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
        $search = trim((string)($options['search'] ?? ''));

        return [
            'services' => $this->getServices($supplierId, $serviceLimit, $serviceOffset, $search),
            'packages' => $this->getPackages($supplierId, $packageLimit, $packageOffset, $search),
            'categories' => $this->getCategories(),
            'meta' => [
                'services' => $this->paginationMeta($serviceLimit, $serviceOffset, $this->countServices($supplierId, $search)),
                'packages' => $this->paginationMeta($packageLimit, $packageOffset, $this->countPackages($supplierId, $search)),
                'supplier_packages_available' => $this->supplierPackageTablesAvailable(),
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

    public function getServices($supplierId, $limit = null, $offset = 0, string $search = '')
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
                         services.publish_status,
                         services.booking_type,
                         services.duration_minutes,
                         services.buffer_minutes,
                         services.pricing_unit,
                         services.max_concurrent,
                         services.max_concurrent_package,
                         services.max_concurrent_customize,
                         services.min_lead_days,
                         ' . $defaultTimeFields . '
                         ' . $venueSelectFields . '
                         categories.name AS category,
                         categories.slug AS category_slug
                  FROM services
                  LEFT JOIN categories ON categories.id = services.category_id
                  ' . $venueJoin . '
                  WHERE services.supplier_id = :supplier_id';
        if ($search !== '') {
            $query .= ' AND (services.name LIKE :search OR services.description LIKE :search2 OR categories.name LIKE :search3)';
        }
        $query .= ' ORDER BY services.created_at DESC, services.id DESC';

        if ($limit !== null) {
            $query .= ' LIMIT :limit OFFSET :offset';
        }

        $this->db->dbquery($query);
        $this->db->dbbind(':supplier_id', (int)$supplierId);
        if ($search !== '') {
            $like = '%' . $search . '%';
            $this->db->dbbind(':search', $like, PDO::PARAM_STR);
            $this->db->dbbind(':search2', $like, PDO::PARAM_STR);
            $this->db->dbbind(':search3', $like, PDO::PARAM_STR);
        }
        $this->bindPagination($limit, $offset);

        return array_map([$this, 'formatService'], $this->db->getmultidata());
    }

    public function getPackages($supplierId, $limit = null, $offset = 0, string $search = '')
    {
        if (!$this->tableExists('supplier_packages')) {
            return [];
        }

        $query = 'SELECT id, name, description, total_price, thumbnail_url, is_active, categories_json
                  FROM supplier_packages
                  WHERE supplier_id = :supplier_id
                    AND deleted_at IS NULL';
        if ($search !== '') {
            $query .= ' AND (name LIKE :search OR description LIKE :search2)';
        }
        $query .= ' ORDER BY created_at DESC, id DESC';

        if ($limit !== null) {
            $query .= ' LIMIT :limit OFFSET :offset';
        }

        $this->db->dbquery($query);
        $this->db->dbbind(':supplier_id', (int)$supplierId);
        if ($search !== '') {
            $like = '%' . $search . '%';
            $this->db->dbbind(':search', $like, PDO::PARAM_STR);
            $this->db->dbbind(':search2', $like, PDO::PARAM_STR);
        }
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
                is_active, booking_type, duration_minutes, pricing_unit, max_concurrent, max_concurrent_package, max_concurrent_customize, min_lead_days' . $defaultTimeColumns . '
             ) VALUES(
                :supplier_id, :category_id, :name, :description, :price' . $priceRangeValues . ', :thumbnail_url,
                :is_active, :booking_type, :duration_minutes, :pricing_unit, :max_concurrent, :max_concurrent_package, :max_concurrent_customize, :min_lead_days' . $defaultTimeValues . '
             )'
        );
        $this->bindServiceFields($supplierId, $categoryId, $data);
        $this->db->dbexecute();

        $serviceId = (int)$this->db->lastinsertid();
        $this->saveVenueDetails($supplierId, $serviceId, $data);
        $this->saveDecorationStyles($serviceId, $data);
        $this->saveFoodItems($serviceId, $data);
        $this->saveRentalPricing($serviceId, $data);
        $this->saveAttireItems($serviceId, $data);

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
                 max_concurrent_package = :max_concurrent_package,
                 max_concurrent_customize = :max_concurrent_customize,
                 min_lead_days = :min_lead_days' . $defaultTimeUpdate . '
             WHERE id = :id
               AND supplier_id = :supplier_id'
        );
        $this->bindServiceFields($supplierId, $categoryId, $data);
        $this->db->dbbind(':id', (int)$serviceId);
        $this->db->dbexecute();
        $this->saveVenueDetails($supplierId, $serviceId, $data);
        $this->saveDecorationStyles($serviceId, $data);
        $this->saveFoodItems($serviceId, $data);
        $this->saveRentalPricing($serviceId, $data);
        $this->saveAttireItems($serviceId, $data);

        return $this->getServiceById($serviceId, $supplierId);
    }

    public function deleteService($supplierId, $serviceId)
    {
        $supplierId = (int)$supplierId;
        $serviceId = (int)$serviceId;

        $this->db->dbquery(
            'SELECT id
             FROM services
             WHERE id = :id
               AND supplier_id = :supplier_id
             LIMIT 1'
        );
        $this->db->dbbind(':id', $serviceId);
        $this->db->dbbind(':supplier_id', $supplierId);
        if (!$this->db->getsingledata()) {
            return false;
        }

        $this->db->dbquery(
            "SELECT
                (SELECT COUNT(*) FROM booking_items
                 WHERE item_type = 'service' AND item_id = :booking_service_id)
              + (SELECT COUNT(*) FROM booking_vouchers
                 WHERE service_id = :voucher_service_id)
              + (SELECT COUNT(*) FROM reviews
                 WHERE service_id = :review_service_id) AS usage_count"
        );
        $this->db->dbbind(':booking_service_id', $serviceId);
        $this->db->dbbind(':voucher_service_id', $serviceId);
        $this->db->dbbind(':review_service_id', $serviceId);
        $usage = $this->db->getsingledata();

        if ((int)($usage['usage_count'] ?? 0) > 0) {
            throw new DomainException('This service has booking or review history and cannot be deleted. Set it to inactive instead.');
        }

        $this->db->beginTransaction();

        try {
            $childTables = [
                'package_items',
                'supplier_package_items',
                'service_media',
                'decoration_styles',
                'service_rental_pricing',
                'attire_items',
            ];

            foreach ($childTables as $table) {
                if (!$this->tableExists($table)) {
                    continue;
                }
                $this->db->dbquery("DELETE FROM {$table} WHERE service_id = :service_id");
                $this->db->dbbind(':service_id', $serviceId);
                $this->db->dbexecute();
            }

            $this->db->dbquery('DELETE FROM services WHERE id = :id AND supplier_id = :supplier_id');
            $this->db->dbbind(':id', $serviceId);
            $this->db->dbbind(':supplier_id', $supplierId);
            $this->db->dbexecute();
            $deleted = $this->db->rowcount() > 0;

            if (!$deleted) {
                throw new RuntimeException('The service disappeared before it could be deleted.');
            }

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function setServiceStatus($supplierId, $serviceId, $isActive, $publishStatus = null)
    {
        $sets = 'is_active = :is_active';
        if ($publishStatus !== null) {
            $sets .= ', publish_status = :publish_status';
        }
        $this->db->dbquery(
            'UPDATE services
             SET ' . $sets . '
             WHERE id = :id
               AND supplier_id = :supplier_id'
        );
        $this->db->dbbind(':is_active', $isActive ? 1 : 0);
        if ($publishStatus !== null) {
            $this->db->dbbind(':publish_status', $publishStatus);
        }
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
                    services.max_concurrent_package,
                    services.max_concurrent_customize,
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
        $this->requireSupplierPackageTables();
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
        $this->requireSupplierPackageTables();
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
        if (!$this->tableExists('supplier_packages')) {
            return false;
        }
        if ($this->tableExists('supplier_package_items')) {
            $this->db->dbquery('DELETE FROM supplier_package_items WHERE package_id = :package_id');
            $this->db->dbbind(':package_id', (int)$packageId);
            $this->db->dbexecute();
        }

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
        if (!$this->tableExists('supplier_packages')) {
            return null;
        }

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
                    services.max_concurrent_package,
                    services.max_concurrent_customize,
                    services.min_lead_days,
                    services.category_id,
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

    private function countServices($supplierId, string $search = '')
    {
        $query = 'SELECT COUNT(*) AS total FROM services WHERE supplier_id = :supplier_id';
        if ($search !== '') {
            $query .= ' AND (name LIKE :search OR description LIKE :search2)';
        }
        $this->db->dbquery($query);
        $this->db->dbbind(':supplier_id', (int)$supplierId);
        if ($search !== '') {
            $like = '%' . $search . '%';
            $this->db->dbbind(':search', $like, PDO::PARAM_STR);
            $this->db->dbbind(':search2', $like, PDO::PARAM_STR);
        }
        $row = $this->db->getsingledata();
        return (int)($row['total'] ?? 0);
    }

    private function countPackages($supplierId, string $search = '')
    {
        if (!$this->tableExists('supplier_packages')) {
            return 0;
        }

        $query = 'SELECT COUNT(*) AS total FROM supplier_packages WHERE supplier_id = :supplier_id AND deleted_at IS NULL';
        if ($search !== '') {
            $query .= ' AND (name LIKE :search OR description LIKE :search2)';
        }
        $this->db->dbquery($query);
        $this->db->dbbind(':supplier_id', (int)$supplierId);
        if ($search !== '') {
            $like = '%' . $search . '%';
            $this->db->dbbind(':search', $like, PDO::PARAM_STR);
            $this->db->dbbind(':search2', $like, PDO::PARAM_STR);
        }
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
        $isCake = $category === 'cake';
        $isCatering = $category === 'food_drinks';
        $isFood = $isCake || $isCatering;
        $isRental = in_array($category, ['attire'], true);
        $venueRooms = is_array($service['venue_rooms'] ?? null) ? $service['venue_rooms'] : [];
        $decorationStyles = is_array($service['decoration_styles'] ?? null) ? $service['decoration_styles'] : [];
        $attireItems = is_array($service['attire_items'] ?? null) ? $service['attire_items'] : [];
        $rentalPricing = is_array($service['rental_pricing'] ?? null) ? $service['rental_pricing'] : [];

        if ($name === '') {
            $missing[] = 'Add the service name.';
        }

        if ($description === '') {
            $missing[] = 'Add a service description.';
        }

        if ($price <= 0 && !$isDecoration && !$isFood && !$isRental) {
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
            if (!$this->hasOpenWeeklySchedule($weekly)) {
                $missing[] = 'Save your availability schedule with at least one available day.';
            }
        } elseif ($isRental) {
            // Check attire items first; fall back to service-level rental pricing
            if (!empty($attireItems)) {
                $hasValidItem = false;
                foreach ($attireItems as $ai) {
                    $aiBuy = ($ai['buy_package_price'] ?? $ai['buy_price'] ?? 0) > 0
                        || ($ai['buy_customize_price'] ?? 0) > 0;
                    $hasRentalOptions = !empty($ai['rental_options']);
                    if ($aiBuy || $hasRentalOptions) {
                        $hasValidItem = true;
                        break;
                    }
                }
                if (!$hasValidItem) {
                    $missing[] = 'At least one attire item needs rental options (duration + price) or a buy price.';
                }
            } else {
                $hasBorrow = ($rentalPricing['borrow_package_price'] ?? $rentalPricing['borrow_price'] ?? 0) > 0
                    || ($rentalPricing['borrow_customize_price'] ?? 0) > 0;
                $hasBuy = ($rentalPricing['buy_package_price'] ?? $rentalPricing['buy_price'] ?? 0) > 0
                    || ($rentalPricing['buy_customize_price'] ?? 0) > 0;
                if (!$hasBorrow && !$hasBuy) {
                    $missing[] = 'Add a borrow price, a buy price, or both.';
                }
            }
            if (!$this->hasOpenWeeklySchedule($weekly)) {
                $missing[] = 'Save your availability schedule with at least one available day.';
            }
        } elseif ($isCake) {
            $foodItems = is_array($service['food_items'] ?? null) ? $service['food_items'] : [];
            $validFood = array_filter($foodItems, fn($f) => trim((string)($f['name'] ?? '')) !== '' && (float)($f['package_price'] ?? $f['price'] ?? 0) > 0);
            if (empty($validFood)) {
                $missing[] = 'Add at least one cake item with a name and price.';
            }
            if (!$this->hasOpenWeeklySchedule($weekly)) {
                $missing[] = 'Save your availability schedule with at least one available day.';
            }
        } elseif ($isCatering) {
            $foodItems = is_array($service['food_items'] ?? null) ? $service['food_items'] : [];
            $validFood = array_filter($foodItems, fn($f) => trim((string)($f['name'] ?? '')) !== '' && (float)($f['package_price'] ?? $f['price'] ?? 0) > 0);
            if (empty($validFood)) {
                $missing[] = 'Add at least one menu item with a name and per-person price.';
            }
            if (!$this->hasOpenWeeklySchedule($weekly)) {
                $missing[] = 'Save your availability schedule with at least one available day.';
            }
        } elseif (!$this->hasOpenWeeklySchedule($weekly)) {
            $missing[] = 'Save your availability schedule with at least one available day.';
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
            'max_concurrent_package' => (int)($service['max_concurrent_package'] ?? 0),
            'max_concurrent_customize' => (int)($service['max_concurrent_customize'] ?? 0),
            'weekly' => $schedules,
            'overrides' => $overrides,
        ];
    }

    public function saveWeeklyAvailability($supplierId, $serviceId, $data)
    {
        $service = $this->getServiceById($serviceId, $supplierId);
        if (!$service) {
            return null;
        }

        $duration = max(15, min(720, (int)($data['duration_minutes'] ?? 60)));
        $buffer = max(0, min(240, (int)($data['buffer_minutes'] ?? 0)));
        $maxConcurrent = max(1, min(20, (int)($data['max_concurrent'] ?? 1)));
        $minLeadDays = max(0, min(365, (int)($data['min_lead_days'] ?? 0)));
        $weekly = is_array($data['weekly'] ?? null) ? $data['weekly'] : [];

        $maxConcurrentPackage = max(0, min(20, (int)($data['max_concurrent_package'] ?? 0)));
        $maxConcurrentCustomize = max(0, min(20, (int)($data['max_concurrent_customize'] ?? 0)));

        // Determine booking type based on category (only Venue uses slot booking)
        $categoryId = (int)($service['category_id'] ?? 0);
        $slotCategories = defined('SLOT_BOOKING_CATEGORIES') ? SLOT_BOOKING_CATEGORIES : [6];
        $bookingType = in_array($categoryId, $slotCategories, true) ? 'slot' : 'fullday';

        $this->db->dbquery(
            'UPDATE services
                 SET duration_minutes = :duration_minutes,
                     buffer_minutes = :buffer_minutes,
                     max_concurrent = :max_concurrent,
                     max_concurrent_package = :max_concurrent_package,
                     max_concurrent_customize = :max_concurrent_customize,
                     min_lead_days = :min_lead_days,
                     booking_type = :booking_type
             WHERE id = :id
               AND supplier_id = :supplier_id'
        );
        $this->db->dbbind(':duration_minutes', $duration);
        $this->db->dbbind(':buffer_minutes', $buffer);
        $this->db->dbbind(':max_concurrent', $maxConcurrent);
        $this->db->dbbind(':max_concurrent_package', $maxConcurrentPackage);
        $this->db->dbbind(':max_concurrent_customize', $maxConcurrentCustomize);
        $this->db->dbbind(':min_lead_days', $minLeadDays);
        $this->db->dbbind(':booking_type', $bookingType);
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

        $category = strtolower((string)($service['category'] ?? ''));
        $isVenue = $category === 'venue';
        $venueRooms = [];
        $roomOverrides = [];
        $roomBookings = [];
        $serviceMinLeadDays = max(0, (int)($service['min_lead_days'] ?? 0));

        if ($isVenue && !empty($service['venue_id'])) {
            $venueRooms = $this->getVenueRooms((int)$service['venue_id']);
            $roomOverrides = $this->calendarRoomOverrides((int)$service['venue_id'], $gridStart->format('Y-m-d'), $gridEnd->format('Y-m-d'));
            $roomBookings = $this->calendarRoomBookings((int)$serviceId, $gridStart->format('Y-m-d'), $gridEnd->format('Y-m-d'));
        }

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

            $dayEntry = [
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

            if ($isVenue && !empty($venueRooms)) {
                $serviceClosedForDay = $status === 'unavailable';
                $dayRoomOverrides = $roomOverrides[$date] ?? [];
                $dayRoomBookings = $roomBookings[$date] ?? [];
                $dayRooms = [];

                foreach ($venueRooms as $room) {
                    $roomId = (int)$room['id'];
                    $roomMinLead = $room['min_lead_days'] !== null
                        ? max(0, (int)$room['min_lead_days'])
                        : $serviceMinLeadDays;
                    $earliestDate = date('Y-m-d', strtotime('+' . $roomMinLead . ' days'));
                    $leadBlocked = strtotime($date) < strtotime($earliestDate);
                    $roomOverride = $dayRoomOverrides[$roomId] ?? null;
                    $roomBookingCount = $dayRoomBookings[$roomId] ?? 0;

                    if ($serviceClosedForDay) {
                        $roomStatus = 'unavailable';
                        $roomSource = 'service_override';
                        $roomStart = null;
                        $roomEnd = null;
                    } elseif ($roomOverride !== null) {
                        $roomSource = 'override';
                        if ((int)$roomOverride['is_available'] === 0) {
                            $roomStatus = 'unavailable';
                            $roomStart = null;
                            $roomEnd = null;
                        } else {
                            $roomStatus = $roomBookingCount > 0 ? 'booked' : 'open';
                            $roomStart = $roomOverride['start_time'] ?? $room['start_time'];
                            $roomEnd = $roomOverride['end_time'] ?? $room['end_time'];
                        }
                    } else {
                        $roomSource = 'default';
                        $roomStatus = $roomBookingCount > 0 ? 'booked' : 'open';
                        $roomStart = $room['start_time'];
                        $roomEnd = $room['end_time'];
                    }

                    if ($leadBlocked && $roomStatus !== 'unavailable') {
                        $roomStatus = 'lead_blocked';
                    }

                    $dayRooms[] = [
                        'room_id' => $roomId,
                        'status' => $roomStatus,
                        'source' => $roomSource,
                        'start_time' => $roomStart,
                        'end_time' => $roomEnd,
                        'booking_count' => $roomBookingCount,
                        'min_lead_days' => $roomMinLead,
                        'override_id' => $roomOverride !== null ? (int)($roomOverride['id'] ?? 0) : null,
                    ];
                }

                $dayEntry['rooms'] = $dayRooms;
            }

            $days[] = $dayEntry;
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
            'venue_rooms' => !empty($venueRooms) ? array_map(function ($room) {
                return [
                    'id' => (int)$room['id'],
                    'name' => $room['name'] ?? '',
                    'capacity' => (int)($room['capacity'] ?? 1),
                    'min_lead_days' => $room['min_lead_days'] !== null ? (int)$room['min_lead_days'] : null,
                ];
            }, $venueRooms) : null,
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

    /**
     * Read-only: fetch remaining capacity for all services on a given date.
     * Does NOT create or modify time_slot rows — only reads existing data.
     */
    public function fetchAllServicesCapacity(int $supplierId, string $date): array
    {
        $date = $this->normalizeDate($date);
        if (!$date) {
            return ['date' => '', 'services' => []];
        }

        $services = $this->getServices($supplierId);
        if (empty($services)) {
            return ['date' => $date, 'services' => []];
        }

        $dayOfWeek = (int)date('N', strtotime($date));
        $serviceIds = array_map(fn($s) => (int)$s['id'], $services);
        $inPlaceholders = implode(',', array_map(fn($i) => ':sid_' . $i, array_keys($serviceIds)));

        // Fetch all time slots for this date in one query
        $this->db->dbquery(
            "SELECT service_id, start_time, end_time,
                    confirmed_count, max_concurrent,
                    confirmed_package_count, confirmed_customize_count,
                    max_concurrent_package, max_concurrent_customize, status
             FROM service_time_slots
             WHERE date = :date AND service_id IN ({$inPlaceholders})"
        );
        $this->db->dbbind(':date', $date);
        foreach ($serviceIds as $i => $sid) {
            $this->db->dbbind(':sid_' . $i, $sid);
        }
        $slotsByService = [];
        foreach ($this->db->getmultidata() as $row) {
            $slotsByService[(int)$row['service_id']][] = $row;
        }

        // Fetch overrides for this date
        $this->db->dbquery(
            "SELECT service_id, type FROM service_availability WHERE date = :date AND service_id IN ({$inPlaceholders})"
        );
        $this->db->dbbind(':date', $date);
        foreach ($serviceIds as $i => $sid) {
            $this->db->dbbind(':sid_' . $i, $sid);
        }
        $overrides = [];
        foreach ($this->db->getmultidata() as $row) {
            $overrides[(int)$row['service_id']] = $row['type'];
        }

        // Fetch weekly schedule for this day-of-week
        $this->db->dbquery(
            "SELECT service_id, is_available FROM service_schedules WHERE day_of_week = :dow AND service_id IN ({$inPlaceholders})"
        );
        $this->db->dbbind(':dow', $dayOfWeek);
        foreach ($serviceIds as $i => $sid) {
            $this->db->dbbind(':sid_' . $i, $sid);
        }
        $scheduleMap = [];
        foreach ($this->db->getmultidata() as $row) {
            $scheduleMap[(int)$row['service_id']] = !empty($row['is_available']);
        }

        $result = [];
        foreach ($services as $service) {
            $sid = (int)$service['id'];
            $overrideType = $overrides[$sid] ?? null;
            $hasSlots = !empty($slotsByService[$sid]);

            // Skip explicitly unavailable services with no existing slots
            if ($overrideType === 'unavailable' && !$hasSlots) {
                continue;
            }

            // Skip closed days (no override + weekly schedule says closed) with no slots
            if (!$overrideType && empty($scheduleMap[$sid]) && !$hasSlots) {
                continue;
            }

            if ($hasSlots) {
                $slots = [];
                $totalMax = 0;
                $totalConfirmed = 0;
                foreach ($slotsByService[$sid] as $slot) {
                    $mc = (int)$slot['max_concurrent'];
                    $cc = (int)$slot['confirmed_count'];
                    $totalMax += $mc;
                    $totalConfirmed += $cc;
                    $left = max(0, $mc - $cc);
                    $slots[] = [
                        'start_time' => substr($slot['start_time'], 0, 5),
                        'end_time' => substr($slot['end_time'], 0, 5),
                        'max_concurrent' => $mc,
                        'confirmed_count' => $cc,
                        'remaining' => $left,
                        'status' => $slot['status'],
                    ];
                }
                $result[] = [
                    'id' => $sid,
                    'name' => $service['name'],
                    'category' => $service['category'] ?? 'Service',
                    'img' => $service['img'] ?? '',
                    'status' => $service['status'],
                    'booking_type' => $service['timeslot'] ? 'slot' : 'fullday',
                    'total_capacity' => $totalMax,
                    'total_confirmed' => $totalConfirmed,
                    'total_remaining' => max(0, $totalMax - $totalConfirmed),
                    'slots' => $slots,
                ];
            } else {
                // No time slots generated yet — use service-level default
                $mc = (int)($service['capacity'] ?? 1);
                $result[] = [
                    'id' => $sid,
                    'name' => $service['name'],
                    'category' => $service['category'] ?? 'Service',
                    'img' => $service['img'] ?? '',
                    'status' => $service['status'],
                    'booking_type' => $service['timeslot'] ? 'slot' : 'fullday',
                    'total_capacity' => $mc,
                    'total_confirmed' => 0,
                    'total_remaining' => $mc,
                    'slots' => [],
                ];
            }
        }

        return ['date' => $date, 'services' => array_values($result)];
    }

    /**
     * Reserve a slot for a booking.
     * @param string $source 'package' or 'custom'
     */
    public function reserveServiceSlot($slotId, string $source = 'custom')
    {
        $poolColumn = $source === 'package' ? 'confirmed_package_count' : 'confirmed_customize_count';
        $poolCondition = $source === 'package'
            ? 'AND (st.max_concurrent_package = 0 OR st.confirmed_package_count < st.max_concurrent_package)'
            : 'AND (st.max_concurrent_customize = 0 OR st.confirmed_customize_count < st.max_concurrent_customize)';

        $this->db->dbquery(
            "UPDATE service_time_slots
             SET confirmed_count = confirmed_count + 1,
                 {$poolColumn} = {$poolColumn} + 1,
                 status = CASE
                    WHEN confirmed_count + 1 >= max_concurrent THEN 'full'
                    ELSE 'available'
                 END
             WHERE id = (
                SELECT id FROM (
                    SELECT st.id
                    FROM service_time_slots st
                    WHERE st.id = :id
                      AND st.status = 'available'
                      AND st.confirmed_count < st.max_concurrent
                      {$poolCondition}
                    LIMIT 1
                ) AS target
             )"
        );
        $this->db->dbbind(':id', (int)$slotId);
        $this->db->dbexecute();

        return $this->db->rowcount() > 0;
    }

    /**
     * Release a reserved slot.
     * @param string $source 'package' or 'custom'
     */
    public function releaseServiceSlot($slotId, string $source = 'custom')
    {
        $poolColumn = $source === 'package' ? 'confirmed_package_count' : 'confirmed_customize_count';

        // CAST to SIGNED before subtracting — confirmed_* are UNSIGNED, so 0 - 1
        // underflows and errors under strict SQL mode before GREATEST applies.
        $this->db->dbquery(
            "UPDATE service_time_slots
             SET confirmed_count = GREATEST(CAST(confirmed_count AS SIGNED) - 1, 0),
                 {$poolColumn} = GREATEST(CAST({$poolColumn} AS SIGNED) - 1, 0),
                 status = CASE
                    WHEN GREATEST(CAST(confirmed_count AS SIGNED) - 1, 0) >= max_concurrent THEN 'full'
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
        $source = $this->getBookingItemSource($bookingItemId);
        $slotId = $this->getBookingItemSlotId($bookingItemId);

        if (!$slotId) {
            return false;
        }

        return $this->reserveServiceSlot($slotId, $source);
    }

    public function releaseBookingItemSlot($bookingItemId)
    {
        $source = $this->getBookingItemSource($bookingItemId);
        $slotId = $this->getBookingItemSlotId($bookingItemId);

        if (!$slotId) {
            return false;
        }

        return $this->releaseServiceSlot($slotId, $source);
    }

    /**
     * Determine the booking source ('package' or 'custom') for a booking item.
     */
    private function getBookingItemSource($bookingItemId): string
    {
        $this->db->dbquery('SELECT source FROM booking_items WHERE id = :id LIMIT 1');
        $this->db->dbbind(':id', (int)$bookingItemId);
        $item = $this->db->getsingledata();

        return ($item['source'] ?? 'custom') === 'package' ? 'package' : 'custom';
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
        if (!$this->tableExists('supplier_packages')) {
            return null;
        }

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

    private function requireSupplierPackageTables(): void
    {
        if (!$this->supplierPackageTablesAvailable()) {
            throw new RuntimeException(
                'Supplier package management is unavailable because its database migration has not been applied.'
            );
        }
    }

    private function supplierPackageTablesAvailable(): bool
    {
        return $this->tableExists('supplier_packages')
            && $this->tableExists('supplier_package_items');
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
        // Only allow slot-based booking for categories in SLOT_BOOKING_CATEGORIES
        $slotCategories = defined('SLOT_BOOKING_CATEGORIES') ? SLOT_BOOKING_CATEGORIES : [6];
        $isSlotRequest = ($data['booking_type'] ?? '') === 'slot' || !empty($data['timeslot']);
        $bookingType = ($isSlotRequest && in_array($categoryId, $slotCategories, true)) ? 'slot' : 'fullday';
        $this->db->dbbind(':booking_type', $bookingType);
        $this->db->dbbind(':duration_minutes', !empty($data['duration_minutes']) ? (int)$data['duration_minutes'] : null);
        $this->db->dbbind(':pricing_unit', $data['pricing_unit'] ?? 'per_session');
        $this->db->dbbind(':max_concurrent', max(1, min(65535, (int)($data['capacity'] ?? $data['max_concurrent'] ?? 1))));
        $this->db->dbbind(':max_concurrent_package', max(0, min(65535, (int)($data['max_concurrent_package'] ?? 0))));
        $this->db->dbbind(':max_concurrent_customize', max(0, min(65535, (int)($data['max_concurrent_customize'] ?? 0))));
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
        if (!in_array($category, ['attire'], true)) {
            return $data;
        }

        $attireItems = is_array($data['attire_items'] ?? null) ? $data['attire_items'] : [];
        if (!empty($attireItems)) {
            $packagePrices = [];
            $customizePrices = [];
            foreach ($attireItems as $item) {
                if (!is_array($item)) {
                    continue;
                }
                $borrowPackage = max(0, (float)($item['borrow_package_price'] ?? 0));
                $borrowCustomize = max($borrowPackage, (float)($item['borrow_customize_price'] ?? $borrowPackage));
                $buyPackage = max(0, (float)($item['buy_package_price'] ?? 0));
                $buyCustomize = max($buyPackage, (float)($item['buy_customize_price'] ?? $buyPackage));

                if ($borrowPackage > 0) $packagePrices[] = $borrowPackage;
                if ($buyPackage > 0) $packagePrices[] = $buyPackage;
                if ($borrowCustomize > 0) $customizePrices[] = $borrowCustomize;
                if ($buyCustomize > 0) $customizePrices[] = $buyCustomize;
            }

            if (!empty($packagePrices)) {
                $priceMin = min($packagePrices);
                $priceMax = !empty($customizePrices) ? max($customizePrices) : $priceMin;
                $data['price'] = $priceMin;
                $data['price_min'] = $priceMin;
                $data['price_max'] = max($priceMin, $priceMax);
                $data['package_price'] = $data['price_min'];
                $data['customize_price'] = $data['price_max'];
                return $data;
            }
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
        $venueName = html_entity_decode(trim((string)($service['venue_name'] ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $category = strtolower((string)($service['category'] ?? ''));

        return [
            'id' => (int)$service['id'],
            'name' => html_entity_decode($service['name'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            'price' => (float)($service['price'] ?? $priceMin),
            'price_min' => $priceMin,
            'price_max' => $priceMax,
            'package_price' => $priceMin,
            'customize_price' => $priceMax,
            'category' => $service['category'] ?: 'Others',
            'category_slug' => $service['category_slug'] ?? '',
            'status' => !empty($service['is_active']) ? 'active' : 'inactive',
            'publish_status' => $service['publish_status'] ?? 'draft',
            'desc' => html_entity_decode($service['description'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            'img' => $service['thumbnail_url'] ?? '',
            'capacity' => (int)($service['max_concurrent'] ?? 1),
            'max_concurrent_package' => (int)($service['max_concurrent_package'] ?? 0),
            'max_concurrent_customize' => (int)($service['max_concurrent_customize'] ?? 0),
            'duration_minutes' => (int)($service['duration_minutes'] ?? 60),
            'buffer_minutes' => (int)($service['buffer_minutes'] ?? 0),
            'timeslot' => ($service['booking_type'] ?? '') === 'slot' ? 'Custom slot' : '',
            'min_lead_days' => (int)($service['min_lead_days'] ?? 0),
            'venue_id' => isset($service['venue_id']) ? (int)$service['venue_id'] : null,
            'venue' => $venueName,
            'venue_name' => $venueName,
            'venue_location' => html_entity_decode($service['venue_location'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            'venue_rooms' => !empty($service['venue_id']) ? $this->getVenueRooms((int)$service['venue_id']) : [],
            'attire_items' => $category === 'attire' ? $this->getAttireItems((int)$service['id']) : [],
            'decoration_styles' => $category === 'decoration' ? $this->getDecorationStyles((int)$service['id']) : [],
            'food_items' => in_array($category, ['cake', 'food_drinks'], true) ? $this->getFoodItems((int)$service['id'], $category) : [],
            'rental_pricing' => in_array($category, ['attire'], true) ? $this->getRentalPricing((int)$service['id']) : null,
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
                    booking_items.venue_room_id,
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
                'venue_room_id' => !empty($row['venue_room_id']) ? (int)$row['venue_room_id'] : null,
            ];
        }

        return $bookings;
    }

    private function calendarRoomOverrides($venueId, $startDate, $endDate)
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
               AND venue_room_availability.date BETWEEN :start_date AND :end_date'
        );
        $this->db->dbbind(':venue_id', (int)$venueId);
        $this->db->dbbind(':start_date', $startDate);
        $this->db->dbbind(':end_date', $endDate);

        $overrides = [];
        foreach ($this->db->getmultidata() as $row) {
            $roomId = (int)$row['room_id'];
            $date = $row['date'] ?? '';
            if ($date === '') {
                continue;
            }
            $overrides[$date][$roomId] = [
                'id' => (int)$row['id'],
                'room_id' => $roomId,
                'date' => $date,
                'start_time' => $row['start_time'],
                'end_time' => $row['end_time'],
                'is_available' => (int)($row['is_available'] ?? 0),
            ];
        }

        return $overrides;
    }

    private function calendarRoomBookings($serviceId, $startDate, $endDate)
    {
        $this->db->dbquery(
            "SELECT booking_items.venue_room_id,
                    DATE(booking_items.booking_date) AS booking_day,
                    COUNT(*) AS booking_count
             FROM booking_items
             WHERE booking_items.item_type = 'service'
               AND booking_items.item_id = :service_id
               AND booking_items.venue_room_id IS NOT NULL
               AND DATE(booking_items.booking_date) BETWEEN :start_date AND :end_date
               AND COALESCE(booking_items.status, '') <> 'cancelled'
             GROUP BY booking_items.venue_room_id, booking_day"
        );
        $this->db->dbbind(':service_id', (int)$serviceId);
        $this->db->dbbind(':start_date', $startDate);
        $this->db->dbbind(':end_date', $endDate);

        $result = [];
        foreach ($this->db->getmultidata() as $row) {
            $roomId = (int)$row['venue_room_id'];
            $date = $row['booking_day'] ?? '';
            if ($date === '' || $roomId <= 0) {
                continue;
            }
            $result[$date][$roomId] = (int)$row['booking_count'];
        }

        return $result;
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

    private function ensureServiceTimeSlot($serviceId, $date, $startTime, $endTime, $maxConcurrent, $maxConcurrentPackage = 0, $maxConcurrentCustomize = 0)
    {
        $startTime = strlen($startTime) === 5 ? $startTime . ':00' : $startTime;
        $endTime = strlen($endTime) === 5 ? $endTime . ':00' : $endTime;
        $maxConcurrent = max(1, (int)$maxConcurrent);
        $maxConcurrentPackage = max(0, (int)$maxConcurrentPackage);
        $maxConcurrentCustomize = max(0, (int)$maxConcurrentCustomize);

        $this->db->dbquery(
            'SELECT id, confirmed_count, confirmed_package_count, confirmed_customize_count,
                    max_concurrent, max_concurrent_package, max_concurrent_customize, status
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
            $needsUpdate = (int)$slot['max_concurrent'] !== $maxConcurrent
                || ($this->hasSlotPoolColumns($slot) && (
                    (int)($slot['max_concurrent_package'] ?? 0) !== $maxConcurrentPackage
                    || (int)($slot['max_concurrent_customize'] ?? 0) !== $maxConcurrentCustomize
                ));

            if ($needsUpdate) {
                $status = (int)$slot['confirmed_count'] >= $maxConcurrent ? 'full' : 'available';
                $poolSets = $this->hasSlotPoolColumns($slot)
                    ? ', max_concurrent_package = :max_concurrent_package, max_concurrent_customize = :max_concurrent_customize'
                    : '';
                $this->db->dbquery(
                    'UPDATE service_time_slots
                     SET max_concurrent = :max_concurrent,
                         status = :status' . $poolSets . '
                     WHERE id = :id'
                );
                $this->db->dbbind(':max_concurrent', $maxConcurrent);
                $this->db->dbbind(':status', $status);
                if ($this->hasSlotPoolColumns($slot)) {
                    $this->db->dbbind(':max_concurrent_package', $maxConcurrentPackage);
                    $this->db->dbbind(':max_concurrent_customize', $maxConcurrentCustomize);
                }
                $this->db->dbbind(':id', (int)$slot['id']);
                $this->db->dbexecute();

                $slot['max_concurrent'] = $maxConcurrent;
                $slot['status'] = $status;
                if ($this->hasSlotPoolColumns($slot)) {
                    $slot['max_concurrent_package'] = $maxConcurrentPackage;
                    $slot['max_concurrent_customize'] = $maxConcurrentCustomize;
                }
            }

            return [
                'id' => (int)$slot['id'],
                'start_time' => substr($startTime, 0, 5),
                'end_time' => substr($endTime, 0, 5),
                'confirmed_count' => (int)$slot['confirmed_count'],
                'confirmed_package_count' => (int)($slot['confirmed_package_count'] ?? 0),
                'confirmed_customize_count' => (int)($slot['confirmed_customize_count'] ?? 0),
                'max_concurrent' => (int)$slot['max_concurrent'],
                'max_concurrent_package' => (int)($slot['max_concurrent_package'] ?? 0),
                'max_concurrent_customize' => (int)($slot['max_concurrent_customize'] ?? 0),
                'status' => $slot['status'],
            ];
        }

        $poolInsertColumns = $this->hasSlotPoolColumns()
            ? ', confirmed_package_count, confirmed_customize_count, max_concurrent_package, max_concurrent_customize'
            : '';
        $poolInsertValues = $this->hasSlotPoolColumns()
            ? ', 0, 0, :max_concurrent_package, :max_concurrent_customize'
            : '';
        $this->db->dbquery(
            'INSERT INTO service_time_slots(service_id, date, start_time, end_time, confirmed_count, max_concurrent, status' . $poolInsertColumns . ')
             VALUES(:service_id, :date, :start_time, :end_time, :confirmed_count, :max_concurrent, :status' . $poolInsertValues . ')'
        );
        $this->db->dbbind(':service_id', (int)$serviceId);
        $this->db->dbbind(':date', $date);
        $this->db->dbbind(':start_time', $startTime);
        $this->db->dbbind(':end_time', $endTime);
        $this->db->dbbind(':confirmed_count', 0);
        $this->db->dbbind(':max_concurrent', $maxConcurrent);
        $this->db->dbbind(':status', 'available');
        if ($this->hasSlotPoolColumns()) {
            $this->db->dbbind(':max_concurrent_package', $maxConcurrentPackage);
            $this->db->dbbind(':max_concurrent_customize', $maxConcurrentCustomize);
        }
        $this->db->dbexecute();

        return [
            'id' => (int)$this->db->lastinsertid(),
            'start_time' => substr($startTime, 0, 5),
            'end_time' => substr($endTime, 0, 5),
            'confirmed_count' => 0,
            'confirmed_package_count' => 0,
            'confirmed_customize_count' => 0,
            'max_concurrent' => $maxConcurrent,
            'max_concurrent_package' => $maxConcurrentPackage,
            'max_concurrent_customize' => $maxConcurrentCustomize,
            'status' => 'available',
        ];
    }

    /**
     * Check whether service_time_slots has the dual concurrency pool columns.
     */
    private function hasSlotPoolColumns($slotRow = null): bool
    {
        static $has = null;
        if ($has !== null) {
            return $has;
        }
        // If we got a row, inspect it directly.
        if (is_array($slotRow)) {
            $has = array_key_exists('max_concurrent_package', $slotRow);
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
            $name = html_entity_decode(trim((string)($style['name'] ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $price = max(0, (float)($style['price'] ?? 0));
            if ($name === '') {
                continue;
            }
            $packagePrice = max(0, (float)($style['package_price'] ?? $style['price'] ?? 0));
            $customizePrice = max($packagePrice, (float)($style['customize_price'] ?? $style['price'] ?? $packagePrice));
            $photoUrl = trim((string)($style['photo_url'] ?? ''));
            $hasDualPrice = $this->hasDecorationStyleDualPriceColumn();
            $photoColumn = $hasPhoto ? ', photo_url' : '';
            $photoValue = $hasPhoto ? ', :photo_url' : '';
            $dualCol = $hasDualPrice ? ', package_price, customize_price' : '';
            $dualVal = $hasDualPrice ? ', :package_price, :customize_price' : '';
            $this->db->dbquery(
                'INSERT INTO decoration_styles (service_id, name, price' . $dualCol . $photoColumn . ', sort_order)
                 VALUES (:service_id, :name, :price' . $dualVal . $photoValue . ', :sort_order)'
            );
            $this->db->dbbind(':service_id', $serviceId);
            $this->db->dbbind(':name', $name);
            $this->db->dbbind(':price', number_format($price, 2, '.', ''), PDO::PARAM_STR);
            if ($hasDualPrice) {
                $this->db->dbbind(':package_price', number_format($packagePrice, 2, '.', ''), PDO::PARAM_STR);
                $this->db->dbbind(':customize_price', number_format($customizePrice, 2, '.', ''), PDO::PARAM_STR);
            }
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
        $dualSelect = $this->hasDecorationStyleDualPriceColumn()
            ? 'package_price, customize_price'
            : "NULL AS package_price, NULL AS customize_price";
        $this->db->dbquery(
            'SELECT id, name, price, ' . $dualSelect . ', ' . $photoSelect . '
             FROM decoration_styles
             WHERE service_id = :service_id
             ORDER BY sort_order ASC, id ASC'
        );
        $this->db->dbbind(':service_id', $serviceId);

        return array_map(function ($row) {
            return [
                'id' => (int)$row['id'],
                'name' => html_entity_decode($row['name'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                'price' => (float)$row['price'],
                'package_price' => (float)($row['package_price'] ?? $row['price'] ?? 0),
                'customize_price' => (float)($row['customize_price'] ?? $row['price'] ?? 0),
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

    private $hasDecorationStyleDualPriceColumn = null;
    private function hasDecorationStyleDualPriceColumn(): bool
    {
        if ($this->hasDecorationStyleDualPriceColumn !== null) {
            return $this->hasDecorationStyleDualPriceColumn;
        }
        try {
            $this->db->dbquery("SHOW COLUMNS FROM decoration_styles LIKE 'package_price'");
            $this->hasDecorationStyleDualPriceColumn = (bool)$this->db->getsingledata();
        } catch (Throwable $e) {
            $this->hasDecorationStyleDualPriceColumn = false;
        }
        return $this->hasDecorationStyleDualPriceColumn;
    }

    // ── Food Items ──────────────────────────────────────────────

    private function getFoodItems(int $serviceId, string $category = ''): array
    {
        $foodType = $this->categoryToFoodType($category);
        try {
            if ($foodType !== '') {
                $this->db->dbquery(
                    'SELECT id, name, description, price, package_price, customize_price, photo_url, pricing_model
                     FROM food_items
                     WHERE service_id = :service_id AND food_type = :food_type
                     ORDER BY sort_order ASC, id ASC'
                );
                $this->db->dbbind(':service_id', $serviceId);
                $this->db->dbbind(':food_type', $foodType);
            } else {
                $this->db->dbquery(
                    'SELECT id, name, description, price, package_price, customize_price, photo_url, pricing_model
                     FROM food_items
                     WHERE service_id = :service_id
                     ORDER BY sort_order ASC, id ASC'
                );
                $this->db->dbbind(':service_id', $serviceId);
            }
        } catch (Throwable $e) {
            return [];
        }

        return array_map(function ($row) {
            return [
                'id' => (int)$row['id'],
                'name' => html_entity_decode($row['name'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                'description' => html_entity_decode($row['description'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                'price' => (float)($row['price'] ?? 0),
                'package_price' => (float)($row['package_price'] ?? $row['price'] ?? 0),
                'customize_price' => (float)($row['customize_price'] ?? $row['price'] ?? 0),
                'photo_url' => trim((string)($row['photo_url'] ?? '')),
                'pricing_model' => $row['pricing_model'] ?? 'flat',
            ];
        }, $this->db->getmultidata());
    }

    private function categoryToFoodType(string $category): string
    {
        return match ($category) {
            'cake' => 'cake',
            'food_drinks' => 'catering',
            'food' => 'catering', // legacy fallback
            default => '',
        };
    }

    private function saveFoodItems(int $serviceId, array $data): void
    {
        $category = strtolower((string)($data['category'] ?? ''));
        $foodType = $this->categoryToFoodType($category);
        if ($foodType === '') {
            return;
        }

        $items = is_array($data['food_items'] ?? null) ? $data['food_items'] : [];

        try {
            $this->db->dbquery('DELETE FROM food_items WHERE service_id = :service_id AND food_type = :food_type');
            $this->db->dbbind(':service_id', $serviceId);
            $this->db->dbbind(':food_type', $foodType);
            $this->db->dbexecute();
        } catch (Throwable $e) {
            return;
        }

        $sort = 0;
        foreach ($items as $item) {
            $name = html_entity_decode(trim((string)($item['name'] ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if ($name === '') {
                continue;
            }
            $description = trim((string)($item['description'] ?? ''));
            $price = max(0, (float)($item['price'] ?? 0));
            $packagePrice = max(0, (float)($item['package_price'] ?? $item['price'] ?? 0));
            $customizePrice = max($packagePrice, (float)($item['customize_price'] ?? $item['price'] ?? $packagePrice));
            $photoUrl = trim((string)($item['photo_url'] ?? ''));
            // Force pricing model based on category: cake=flat, catering=per_person
            $pricingModel = $foodType === 'cake' ? 'flat' : 'per_person';

            $this->db->dbquery(
                'INSERT INTO food_items (service_id, name, description, price, package_price, customize_price, photo_url, pricing_model, food_type, sort_order)
                 VALUES (:service_id, :name, :description, :price, :package_price, :customize_price, :photo_url, :pricing_model, :food_type, :sort_order)'
            );
            $this->db->dbbind(':service_id', $serviceId);
            $this->db->dbbind(':name', $name);
            $this->db->dbbind(':description', $description !== '' ? $description : null);
            $this->db->dbbind(':price', number_format($price, 2, '.', ''), PDO::PARAM_STR);
            $this->db->dbbind(':package_price', number_format($packagePrice, 2, '.', ''), PDO::PARAM_STR);
            $this->db->dbbind(':customize_price', number_format($customizePrice, 2, '.', ''), PDO::PARAM_STR);
            $this->db->dbbind(':photo_url', $photoUrl !== '' ? $photoUrl : null);
            $this->db->dbbind(':pricing_model', $pricingModel);
            $this->db->dbbind(':food_type', $foodType);
            $this->db->dbbind(':sort_order', $sort++, PDO::PARAM_INT);
            $this->db->dbexecute();
        }
    }

    private function saveRentalPricing(int $serviceId, array $data): void
    {
        $category = strtolower((string)($data['category'] ?? ''));
        if (!in_array($category, ['attire'], true)) {
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

    // ─── Attire Items ──────────────────────────────────────────────

    private function getAttireItems(int $serviceId): array
    {
        $this->db->dbquery(
            'SELECT * FROM attire_items
             WHERE service_id = :service_id
             ORDER BY sort_order ASC, id ASC'
        );
        $this->db->dbbind(':service_id', $serviceId);
        $items = $this->db->getmultidata();

        // Attach rental options to each item
        foreach ($items as &$item) {
            $item['rental_options'] = $this->getAttireRentalOptions((int)$item['id']);
        }
        unset($item);

        return $items;
    }

    /**
     * Fetch rental duration options for an attire item.
     */
    public function getAttireRentalOptions(int $attireItemId): array
    {
        $this->db->dbquery(
            'SELECT * FROM attire_rental_options
             WHERE attire_item_id = :attire_item_id
             ORDER BY sort_order ASC, days ASC'
        );
        $this->db->dbbind(':attire_item_id', $attireItemId);
        return $this->db->getmultidata();
    }

    /**
     * Save rental duration options for an attire item (delete-and-reinsert).
     */
    private function saveAttireRentalOptions(int $attireItemId, array $options): void
    {
        $this->db->dbquery('DELETE FROM attire_rental_options WHERE attire_item_id = :attire_item_id');
        $this->db->dbbind(':attire_item_id', $attireItemId);
        $this->db->dbexecute();

        $sortOrder = 0;
        foreach ($options as $opt) {
            if (!is_array($opt)) {
                continue;
            }
            $days = (int)($opt['days'] ?? 0);
            $price = (float)($opt['price'] ?? 0);
            if ($days <= 0 || $price <= 0) {
                continue;
            }

            $customizePrice = !empty($opt['customize_price']) && (float)$opt['customize_price'] > 0
                ? (float)$opt['customize_price'] : null;

            $this->db->dbquery(
                'INSERT INTO attire_rental_options (attire_item_id, days, price, customize_price, sort_order)
                 VALUES (:attire_item_id, :days, :price, :customize_price, :sort_order)'
            );
            $this->db->dbbind(':attire_item_id', $attireItemId, PDO::PARAM_INT);
            $this->db->dbbind(':days', $days, PDO::PARAM_INT);
            $this->db->dbbind(':price', $price);
            $this->db->dbbind(':customize_price', $customizePrice,
                $customizePrice === null ? PDO::PARAM_NULL : null);
            $this->db->dbbind(':sort_order', $sortOrder, PDO::PARAM_INT);
            $this->db->dbexecute();
            $sortOrder++;
        }
    }

    private function saveAttireItems(int $serviceId, array $data): void
    {
        $category = strtolower((string)($data['category'] ?? ''));
        if ($category !== 'attire') {
            return;
        }

        $replaceItems = !empty($data['attire_items_replace']);
        $items = $data['attire_items'] ?? [];

        if ($replaceItems) {
            $this->db->dbquery('DELETE FROM attire_items WHERE service_id = :service_id');
            $this->db->dbbind(':service_id', $serviceId);
            $this->db->dbexecute();
        }

        $sortOrder = 0;
        foreach ($items as $item) {
            if (!is_array($item) || empty(trim((string)($item['name'] ?? '')))) {
                continue;
            }

            $itemId = (int)($item['id'] ?? 0);

            if ($itemId > 0 && !$replaceItems) {
                $this->db->dbquery(
                    'UPDATE attire_items
                     SET name = :name,
                         description = :description,
                         photo_url = :photo_url,
                         borrow_package_price = :borrow_package_price,
                         borrow_customize_price = :borrow_customize_price,
                         buy_package_price = :buy_package_price,
                         buy_customize_price = :buy_customize_price,
                         return_days = :return_days,
                         buffer_days = :buffer_days,
                         sort_order = :sort_order
                     WHERE id = :id AND service_id = :service_id
                     LIMIT 1'
                );
                $this->db->dbbind(':id', $itemId);
            } else {
                $this->db->dbquery(
                    'INSERT INTO attire_items (service_id, name, description, photo_url,
                     borrow_package_price, borrow_customize_price,
                     buy_package_price, buy_customize_price,
                     return_days, buffer_days, sort_order)
                     VALUES (:service_id, :name, :description, :photo_url,
                     :borrow_package_price, :borrow_customize_price,
                     :buy_package_price, :buy_customize_price,
                     :return_days, :buffer_days, :sort_order)'
                );
            }

            $this->db->dbbind(':service_id', $serviceId);
            $this->db->dbbind(':name', trim((string)($item['name'] ?? '')));
            $this->db->dbbind(':description', trim((string)($item['description'] ?? '')));
            $this->db->dbbind(':photo_url', !empty($item['photo_url']) ? $item['photo_url'] : null,
                !empty($item['photo_url']) ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $this->dbbindPrice(':borrow_package_price', $item['borrow_package_price'] ?? null);
            $this->dbbindPrice(':borrow_customize_price', $item['borrow_customize_price'] ?? null);
            $this->dbbindPrice(':buy_package_price', $item['buy_package_price'] ?? null);
            $this->dbbindPrice(':buy_customize_price', $item['buy_customize_price'] ?? null);
            $this->db->dbbind(':return_days', !empty($item['return_days']) ? (int)$item['return_days'] : null,
                !empty($item['return_days']) ? PDO::PARAM_INT : PDO::PARAM_NULL);
            $bufferDays = isset($item['buffer_days']) ? max(0, (int)$item['buffer_days']) : 1;
            $this->db->dbbind(':buffer_days', $bufferDays, PDO::PARAM_INT);
            $this->db->dbbind(':sort_order', $sortOrder, PDO::PARAM_INT);
            $this->db->dbexecute();

            // Resolve the item ID for rental options save
            $savedItemId = $itemId > 0 && !$replaceItems ? $itemId : (int)$this->db->lastinsertid();

            // Save rental options if provided
            $rentalOptions = $item['rental_options'] ?? [];
            if (!empty($rentalOptions) && $savedItemId > 0) {
                $this->saveAttireRentalOptions($savedItemId, $rentalOptions);
            }

            $sortOrder++;
        }

        // Delete removed items (IDs present in current DB but not in submitted array)
        if (!$replaceItems) {
            $submittedIds = array_filter(array_map(fn($item) => (int)($item['id'] ?? 0), $items), fn($id) => $id > 0);
            if (!empty($submittedIds)) {
                $placeholders = implode(',', array_fill(0, count($submittedIds), '?'));
                $this->db->dbquery(
                    "DELETE FROM attire_items
                     WHERE service_id = :service_id AND id NOT IN ({$placeholders})"
                );
                $this->db->dbbind(':service_id', $serviceId);
                foreach ($submittedIds as $i => $id) {
                    $this->db->dbbind(':delete_id_' . $i, $id, PDO::PARAM_INT);
                }
                // Use manual execute with array for simplicity
            } else {
                // If no IDs submitted (all new), nothing to delete
            }
        }
    }

    private function dbbindPrice(string $param, $value): void
    {
        $val = $value !== null && $value !== '' && (float)$value > 0 ? (float)$value : null;
        $this->db->dbbind($param, $val, $val === null ? PDO::PARAM_NULL : null);
    }
}
