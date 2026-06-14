<?php

class PlatformPackage
{
    private $db;
    private $packageQuantityColumns = null;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * ── Admin: All package types (with pagination) ──
     */
    public function getAllPackageTypesAdmin($filters = [], $page = 1, $perPage = 20)
    {
        $conditions = ['p.deleted_at IS NULL'];
        $bindings = [];

        $search = trim((string)($filters['search'] ?? ''));
        if ($search !== '') {
            $conditions[] = '(p.name LIKE :search OR p.description LIKE :search OR p.tagline LIKE :search OR p.slug LIKE :search)';
            $bindings[':search'] = '%' . $search . '%';
        }

        $status = $filters['status'] ?? '';
        if ($status === 'active') {
            $conditions[] = 'p.is_active = 1';
        } elseif ($status === 'inactive') {
            $conditions[] = 'p.is_active = 0';
        }

        $offset = max(0, ((int)$page - 1) * (int)$perPage);
        $limit = max(1, (int)$perPage);

        // Count
        $countSql = 'SELECT COUNT(*) AS total FROM packages p WHERE ' . implode(' AND ', $conditions);
        $this->db->dbquery($countSql);
        foreach ($bindings as $param => $value) {
            $this->db->dbbind($param, $value);
        }
        $total = (int)($this->db->getsingledata()['total'] ?? 0);

        // Fetch
        $sql = 'SELECT p.package_id,
                       p.name,
                       p.slug,
                       p.description,
                       p.tagline,
                       p.base_price,
                       p.image_url,
                       p.is_active,
                       p.sort_order,
                       p.created_at,
                       COUNT(pi.service_id) AS item_count,
                       COALESCE(SUM(CASE WHEN pi.service_id IS NOT NULL THEN ' . $this->packageLineTotalSql() . ' ELSE 0 END), 0) AS included_total
                FROM packages p
             LEFT JOIN package_items pi ON pi.package_id = p.package_id
             LEFT JOIN services svc ON svc.id = pi.service_id
             WHERE ' . implode(' AND ', $conditions) . '
                GROUP BY p.package_id
                ORDER BY p.sort_order ASC, p.name ASC
                LIMIT :limit OFFSET :offset';

        $this->db->dbquery($sql);
        foreach ($bindings as $param => $value) {
            $this->db->dbbind($param, $value);
        }
        $this->db->dbbind(':limit', $limit);
        $this->db->dbbind(':offset', $offset);

        $packages = $this->db->getmultidata();

        return [
            'packages' => $packages,
            'total' => $total,
            'page' => (int)$page,
            'per_page' => $limit,
            'total_pages' => max(1, (int)ceil($total / $limit)),
        ];
    }

    /**
     * ── Admin: Single package type by ID ──
     */
    public function getPackageById($packageId)
    {
        $this->db->dbquery(
            'SELECT p.*,
                    COUNT(pi.service_id) AS item_count,
                    COALESCE(SUM(CASE WHEN pi.service_id IS NOT NULL THEN ' . $this->packageLineTotalSql() . ' ELSE 0 END), 0) AS included_total
             FROM packages p
             LEFT JOIN package_items pi ON pi.package_id = p.package_id
             LEFT JOIN services svc ON svc.id = pi.service_id
             WHERE p.package_id = :package_id
             GROUP BY p.package_id
             LIMIT 1'
        );
        $this->db->dbbind(':package_id', (int)$packageId);

        $package = $this->db->getsingledata();
        if (!$package) {
            return null;
        }

        $package['items'] = $this->getPackageItems($packageId);
        return $package;
    }

    /**
     * ── Admin: Package items (fixed services) ──
     */
    public function getPackageItems($packageId)
    {
        $this->db->dbquery(
            'SELECT pi.id,
                    pi.category_id,
                    c.name AS category_name,
                    c.slug AS category_slug,
                    pi.service_id,
                    pi.default_supplier_id,
                    ' . $this->packageUnitPriceSql() . ' AS unit_price,
                    ' . $this->packageLineTotalSql() . ' AS default_price,
                    ' . $this->packageQuantityTypeSql() . ' AS quantity_type,
                    ' . $this->packageQuantitySql() . ' AS quantity,
                    svc.name AS service_name,
                    svc.description AS service_description,
                    svc.thumbnail_url,
                    svc.price,
                    svc.price_min,
                    svc.price_max,
                    sup.shop_name AS default_supplier_name
             FROM package_items pi
             LEFT JOIN categories c ON c.id = pi.category_id
             LEFT JOIN services svc ON svc.id = pi.service_id
             LEFT JOIN suppliers sup ON sup.supplier_id = pi.default_supplier_id
             WHERE pi.package_id = :package_id
               AND pi.service_id IS NOT NULL
             ORDER BY c.name ASC, svc.name ASC'
        );
        $this->db->dbbind(':package_id', (int)$packageId);

        return $this->db->getmultidata();
    }

    /**
     * ── Admin: Create package type ──
     */
    public function createPackageType($data)
    {
        $name = trim((string)($data['name'] ?? ''));
        if ($name === '') {
            return false;
        }

        $slug = trim((string)($data['slug'] ?? ''));
        if ($slug === '') {
            $slug = $this->slugify($name);
        }
        $slug = $this->uniqueSlug($slug);

        $this->db->dbquery(
            'INSERT INTO packages (name, slug, description, tagline, base_price, image_url, is_active, sort_order)
             VALUES (:name, :slug, :description, :tagline, :base_price, :image_url, :is_active, :sort_order)'
        );
        $this->db->dbbind(':name', $name);
        $this->db->dbbind(':slug', $slug);
        $this->db->dbbind(':description', trim((string)($data['description'] ?? '')));
        $this->db->dbbind(':tagline', trim((string)($data['tagline'] ?? '')));
        $this->db->dbbind(':base_price', (float)($data['base_price'] ?? 0));
        $this->db->dbbind(':image_url', trim((string)($data['image_url'] ?? '')));
        $this->db->dbbind(':is_active', !empty($data['is_active']) ? 1 : 0);
        $this->db->dbbind(':sort_order', (int)($data['sort_order'] ?? 0));

        try {
            if ($this->db->dbexecute()) {
                return (int)$this->db->lastinsertid();
            }
        } catch (PDOException $e) {
            return false;
        }

        return false;
    }

    /**
     * ── Admin: Update package type ──
     */
    public function updatePackageType($packageId, $data)
    {
        $fields = [];
        $bindings = [':package_id' => (int)$packageId];

        if (array_key_exists('slug', $data)) {
            if (trim((string)$data['slug']) === '' && !empty($data['name'])) {
                $data['slug'] = $this->slugify((string)$data['name']);
            }
            $data['slug'] = $this->uniqueSlug((string)$data['slug'], (int)$packageId);
        }

        foreach (['name', 'slug', 'description', 'tagline', 'image_url'] as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = :$field";
                $bindings[":$field"] = trim((string)$data[$field]);
            }
        }

        if (array_key_exists('base_price', $data)) {
            $fields[] = 'base_price = :base_price';
            $bindings[':base_price'] = (float)$data['base_price'];
        }

        if (array_key_exists('is_active', $data)) {
            $fields[] = 'is_active = :is_active';
            $bindings[':is_active'] = !empty($data['is_active']) ? 1 : 0;
        }

        if (array_key_exists('sort_order', $data)) {
            $fields[] = 'sort_order = :sort_order';
            $bindings[':sort_order'] = (int)$data['sort_order'];
        }

        if (empty($fields)) {
            return false;
        }

        $sql = 'UPDATE packages SET ' . implode(', ', $fields) . ' WHERE package_id = :package_id';
        $this->db->dbquery($sql);
        foreach ($bindings as $param => $value) {
            $this->db->dbbind($param, $value);
        }

        try {
            return $this->db->dbexecute();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * ── Admin: Delete package type ──
     */
    public function deletePackageType($packageId)
    {
        try {
            $this->db->dbquery('DELETE FROM package_items WHERE package_id = :package_id');
            $this->db->dbbind(':package_id', (int)$packageId);
            $this->db->dbexecute();

            $this->db->dbquery('DELETE FROM packages WHERE package_id = :package_id LIMIT 1');
            $this->db->dbbind(':package_id', (int)$packageId);

            return $this->db->dbexecute();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * ── Admin: Add category item to package ──
     */
    public function addPackageItem($packageId, $categoryId)
    {
        // Check for duplicate
        $this->db->dbquery(
            'SELECT id FROM package_items
             WHERE package_id = :package_id AND category_id = :category_id
             LIMIT 1'
        );
        $this->db->dbbind(':package_id', (int)$packageId);
        $this->db->dbbind(':category_id', (int)$categoryId);
        if ($this->db->getsingledata()) {
            return false; // already exists
        }

        $this->db->dbquery(
            'INSERT INTO package_items (package_id, category_id) VALUES (:package_id, :category_id)'
        );
        $this->db->dbbind(':package_id', (int)$packageId);
        $this->db->dbbind(':category_id', (int)$categoryId);

        return $this->db->dbexecute();
    }

    public function addPackageService($packageId, $serviceId, $quantity = null)
    {
        $service = $this->getServiceForPackageItem($serviceId);
        if (!$service) {
            return false;
        }

        $this->db->dbquery(
            'SELECT id FROM package_items
             WHERE package_id = :package_id AND service_id = :service_id
             LIMIT 1'
        );
        $this->db->dbbind(':package_id', (int)$packageId);
        $this->db->dbbind(':service_id', (int)$serviceId);
        if ($this->db->getsingledata()) {
            return false;
        }

        $price = $this->servicePackagePrice($service);
        $isGuestPriced = $this->isGuestPricedCategory($service['category_slug'] ?? '', $service['category_name'] ?? '');
        $itemQuantity = $isGuestPriced ? max(1, (int)($quantity ?: 100)) : 1;
        $quantityType = $isGuestPriced ? 'guests' : 'fixed';
        $quantityColumns = $this->hasPackageQuantityColumns();

        $quantityColumnsSql = $quantityColumns ? ', quantity_type, quantity' : '';
        $quantityValuesSql = $quantityColumns ? ', :quantity_type, :quantity' : '';

        $this->db->dbquery(
            'INSERT INTO package_items (package_id, category_id, service_id, default_supplier_id, default_price' . $quantityColumnsSql . ')
             VALUES (:package_id, :category_id, :service_id, :supplier_id, :default_price' . $quantityValuesSql . ')'
        );
        $this->db->dbbind(':package_id', (int)$packageId);
        $this->db->dbbind(':category_id', (int)($service['category_id'] ?? 0));
        $this->db->dbbind(':service_id', (int)$serviceId);
        $this->db->dbbind(':supplier_id', (int)($service['supplier_id'] ?? 0));
        $this->db->dbbind(':default_price', $price);
        if ($quantityColumns) {
            $this->db->dbbind(':quantity_type', $quantityType);
            $this->db->dbbind(':quantity', $itemQuantity);
        }

        return $this->db->dbexecute();
    }

    /**
     * ── Admin: Remove category item from package ──
     */
    public function removePackageItem($itemId)
    {
        $this->db->dbquery('DELETE FROM package_items WHERE id = :id LIMIT 1');
        $this->db->dbbind(':id', (int)$itemId);
        return $this->db->dbexecute();
    }

    public function getPackageIdForItem($itemId)
    {
        $this->db->dbquery('SELECT package_id FROM package_items WHERE id = :id LIMIT 1');
        $this->db->dbbind(':id', (int)$itemId);
        $row = $this->db->getsingledata();

        return $row ? (int)$row['package_id'] : 0;
    }

    public function updatePackageItemQuantity($itemId, $quantity)
    {
        if (!$this->hasPackageQuantityColumns()) {
            return false;
        }

        $item = $this->getPackageItemForQuantityUpdate($itemId);
        if (!$item || !$this->isGuestPricedCategory($item['category_slug'] ?? '', $item['category_name'] ?? '')) {
            return false;
        }

        $this->db->dbquery(
            'UPDATE package_items
             SET quantity_type = "guests", quantity = :quantity
             WHERE id = :id
             LIMIT 1'
        );
        $this->db->dbbind(':quantity', max(1, (int)$quantity));
        $this->db->dbbind(':id', (int)$itemId);

        return $this->db->dbexecute();
    }

    public function getAdminServiceOptions()
    {
        $this->db->dbquery(
            'SELECT services.id,
                    services.name,
                    services.description,
                    services.price,
                    services.price_min,
                    services.price_max,
                    services.thumbnail_url,
                    services.category_id,
                    categories.name AS category_name,
                    categories.slug AS category_slug,
                    suppliers.supplier_id,
                    suppliers.shop_name AS supplier_name
             FROM services
             INNER JOIN suppliers ON suppliers.supplier_id = services.supplier_id
             LEFT JOIN categories ON categories.id = services.category_id
             WHERE services.is_active = 1
               AND suppliers.deleted_at IS NULL
               AND suppliers.status IN ("approved", "verified")
               AND suppliers.payment_status = "paid"
             ORDER BY categories.name ASC, suppliers.shop_name ASC, services.name ASC'
        );

        return array_map(function ($service) {
            $service['display_price'] = $this->servicePackagePrice($service);
            return $service;
        }, $this->db->getmultidata());
    }

    /**
     * ── Customer: Get all active package types ──
     */
    public function getPackageTypes()
    {
        $this->db->dbquery(
            'SELECT p.package_id,
                    p.name,
                    p.slug,
                    p.description,
                    p.tagline,
                    p.base_price,
                    p.image_url,
                    p.sort_order,
                    COUNT(pi.service_id) AS item_count,
                    COALESCE(SUM(CASE WHEN pi.service_id IS NOT NULL THEN ' . $this->packageLineTotalSql() . ' ELSE 0 END), 0) AS included_total
             FROM packages p
             LEFT JOIN package_items pi ON pi.package_id = p.package_id
             LEFT JOIN services svc ON svc.id = pi.service_id
             WHERE p.is_active = 1
               AND p.deleted_at IS NULL
             GROUP BY p.package_id
             ORDER BY p.sort_order ASC'
        );

        $packages = $this->db->getmultidata();
        if (empty($packages)) {
            return [];
        }

        // Enrich each with included service/category info.
        $packageIds = array_column($packages, 'package_id');
        $placeholders = implode(',', array_fill(0, count($packageIds), '?'));
        $this->db->dbquery(
            "SELECT pi.package_id,
                    c.name AS category_name,
                    c.slug AS category_slug,
                    svc.name AS service_name,
                    sup.shop_name AS supplier_name
             FROM package_items pi
             LEFT JOIN categories c ON c.id = pi.category_id
             LEFT JOIN services svc ON svc.id = pi.service_id
             LEFT JOIN suppliers sup ON sup.supplier_id = pi.default_supplier_id
             WHERE pi.package_id IN ($placeholders)
               AND pi.service_id IS NOT NULL
             ORDER BY c.name ASC, svc.name ASC"
        );
        foreach ($packageIds as $i => $id) {
            $this->db->dbbind($i + 1, (int)$id, \PDO::PARAM_INT);
        }
        $allItems = $this->db->getmultidata();

        $itemsByPackage = [];
        foreach ($allItems as $item) {
            $pid = (int)$item['package_id'];
            $itemsByPackage[$pid][] = $item;
        }

        foreach ($packages as &$pkg) {
            $pid = (int)$pkg['package_id'];
            $pkg['categories'] = $itemsByPackage[$pid] ?? [];
            $pkg['service_count'] = (int)($pkg['item_count'] ?? 0);
        }
        unset($pkg);

        return $packages;
    }

    /**
     * ── Customer: Get package type by slug ──
     */
    public function getPackageBySlug($slug)
    {
        $this->db->dbquery(
            'SELECT p.*
             FROM packages p
             WHERE p.slug = :slug
               AND p.is_active = 1
               AND p.deleted_at IS NULL
             LIMIT 1'
        );
        $this->db->dbbind(':slug', $slug);

        $package = $this->db->getsingledata();
        if (!$package) {
            return null;
        }

        // Get included fixed services.
        $this->db->dbquery(
            'SELECT pi.id AS item_id,
                    pi.category_id,
                    c.name AS category_name,
                    c.slug AS category_slug,
                    pi.service_id,
                    pi.default_supplier_id,
                    ' . $this->packageUnitPriceSql() . ' AS unit_price,
                    ' . $this->packageLineTotalSql() . ' AS default_price,
                    ' . $this->packageQuantityTypeSql() . ' AS quantity_type,
                    ' . $this->packageQuantitySql() . ' AS quantity,
                    svc.name AS service_name,
                    svc.description AS service_description,
                    svc.thumbnail_url,
                    svc.price,
                    svc.price_min,
                    svc.price_max,
                    svc.booking_type,
                    svc.duration_minutes,
                    svc.pricing_unit,
                    sup.shop_name AS supplier_name,
                    COALESCE(review_stats.avg_rating, 0) AS avg_rating,
                    COALESCE(review_stats.review_count, 0) AS review_count
             FROM package_items pi
             LEFT JOIN categories c ON c.id = pi.category_id
             LEFT JOIN services svc ON svc.id = pi.service_id
             LEFT JOIN suppliers sup ON sup.supplier_id = pi.default_supplier_id
             LEFT JOIN (
                SELECT service_id, AVG(rating) AS avg_rating, COUNT(*) AS review_count
                FROM reviews
                WHERE service_id IS NOT NULL
                GROUP BY service_id
             ) review_stats ON review_stats.service_id = svc.id
             WHERE pi.package_id = :package_id
               AND pi.service_id IS NOT NULL
             ORDER BY c.name ASC, svc.name ASC'
        );
        $this->db->dbbind(':package_id', (int)$package['package_id']);
        $items = $this->db->getmultidata();
        $package['items'] = $items;
        $package['services'] = array_values(array_filter(array_map(function ($item) {
            if (empty($item['service_id'])) {
                return null;
            }

            return $this->formatService([
                'id' => $item['service_id'],
                'supplier_id' => $item['default_supplier_id'],
                'name' => $item['service_name'],
                'description' => $item['service_description'],
                'price' => $item['price'],
                'price_min' => $item['price_min'],
                'price_max' => $item['price_max'],
                'thumbnail_url' => $item['thumbnail_url'],
                'supplier_name' => $item['supplier_name'],
                'avg_rating' => $item['avg_rating'],
                'review_count' => $item['review_count'],
                'booking_type' => $item['booking_type'],
                'duration_minutes' => $item['duration_minutes'],
                'pricing_unit' => $item['pricing_unit'],
            ]) + [
                'category_id' => (int)($item['category_id'] ?? 0),
                'category_name' => $item['category_name'] ?? '',
                'category_slug' => $item['category_slug'] ?? '',
                'package_item_id' => (int)($item['item_id'] ?? 0),
                'package_price' => (float)($item['default_price'] ?? 0),
                'unit_price' => (float)($item['unit_price'] ?? $item['default_price'] ?? 0),
                'quantity_type' => $item['quantity_type'] ?? 'fixed',
                'quantity' => (int)($item['quantity'] ?? 1),
            ];
        }, $items)));
        $package['categories'] = $items;

        return $package;
    }

    public function getServicePackageContext($packageId, $packageItemId, $serviceId)
    {
        $this->db->dbquery(
            'SELECT p.package_id,
                    p.name AS package_name,
                    p.slug AS package_slug,
                    pi.id AS package_item_id,
                    pi.service_id,
                    ' . $this->packageUnitPriceSql() . ' AS unit_price,
                    ' . $this->packageLineTotalSql() . ' AS package_price,
                    ' . $this->packageQuantityTypeSql() . ' AS quantity_type,
                    ' . $this->packageQuantitySql() . ' AS quantity
             FROM package_items pi
             INNER JOIN packages p ON p.package_id = pi.package_id
             LEFT JOIN services svc ON svc.id = pi.service_id
             WHERE pi.package_id = :package_id
               AND pi.id = :package_item_id
               AND pi.service_id = :service_id
               AND p.is_active = 1
               AND p.deleted_at IS NULL
             LIMIT 1'
        );
        $this->db->dbbind(':package_id', (int)$packageId);
        $this->db->dbbind(':package_item_id', (int)$packageItemId);
        $this->db->dbbind(':service_id', (int)$serviceId);

        $context = $this->db->getsingledata();
        if (!$context) {
            return null;
        }

        $context['package_id'] = (int)$context['package_id'];
        $context['package_item_id'] = (int)$context['package_item_id'];
        $context['service_id'] = (int)$context['service_id'];
        $context['unit_price'] = (float)($context['unit_price'] ?? 0);
        $context['package_price'] = (float)($context['package_price'] ?? 0);
        $context['quantity'] = (int)($context['quantity'] ?? 1);

        return $context;
    }

    /**
     * ── Customer: Get available services for a category within a package ──
     */
    public function getServicesForCategory($categoryId, $excludePackageId = null)
    {
        $conditions = [
            'services.is_active = 1',
            'suppliers.deleted_at IS NULL',
            'suppliers.is_available = 1',
            'suppliers.status IN ("approved", "verified")',
            'suppliers.payment_status = "paid"',
        ];
        $bindings = [':category_id' => (int)$categoryId];

        if ($excludePackageId) {
            $conditions[] = 'services.id NOT IN (SELECT service_id FROM package_items WHERE package_id = :exclude_pkg_id AND service_id IS NOT NULL)';
            $bindings[':exclude_pkg_id'] = (int)$excludePackageId;
        }

        $sql = 'SELECT services.id,
                       services.name,
                       services.description,
                       services.price,
                       services.price_min,
                       services.price_max,
                       services.thumbnail_url,
                       services.booking_type,
                       services.duration_minutes,
                       services.pricing_unit,
                       suppliers.shop_name AS supplier_name,
                       suppliers.supplier_id,
                       COALESCE(review_stats.avg_rating, 0) AS avg_rating,
                       COALESCE(review_stats.review_count, 0) AS review_count
                FROM services
                INNER JOIN suppliers ON suppliers.supplier_id = services.supplier_id
                LEFT JOIN (
                    SELECT service_id, AVG(rating) AS avg_rating, COUNT(*) AS review_count
                    FROM reviews
                    WHERE service_id IS NOT NULL
                    GROUP BY service_id
                ) review_stats ON review_stats.service_id = services.id
                WHERE services.category_id = :category_id
                  AND ' . implode(' AND ', $conditions) . '
                ORDER BY review_stats.avg_rating DESC, services.created_at DESC
                LIMIT 20';

        $this->db->dbquery($sql);
        foreach ($bindings as $param => $value) {
            $this->db->dbbind($param, $value);
        }

        return array_map([$this, 'formatService'], $this->db->getmultidata());
    }

    /**
     * ── Homepage: Featured packages (top 3 by sort_order) ──
     */
    public function getFeaturedPackages($limit = 3)
    {
        $this->db->dbquery(
            'SELECT p.package_id,
                    p.name,
                    p.slug,
                    p.description,
                    p.tagline,
                    p.base_price,
                    p.image_url,
                    COUNT(pi.service_id) AS item_count,
                    COALESCE(SUM(CASE WHEN pi.service_id IS NOT NULL THEN ' . $this->packageLineTotalSql() . ' ELSE 0 END), 0) AS included_total
             FROM packages p
             LEFT JOIN package_items pi ON pi.package_id = p.package_id
             LEFT JOIN services svc ON svc.id = pi.service_id
             WHERE p.is_active = 1
               AND p.deleted_at IS NULL
             GROUP BY p.package_id
             ORDER BY p.sort_order ASC
             LIMIT :limit'
        );
        $this->db->dbbind(':limit', (int)$limit);

        return $this->db->getmultidata();
    }

    /**
     * ── All active categories (for admin item add dropdown) ──
     */
    public function getAllCategories()
    {
        $this->db->dbquery('SELECT id, name, slug FROM categories ORDER BY name ASC');
        return $this->db->getmultidata();
    }

    // ── helpers ──

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
            'supplier_name' => $service['supplier_name'] ?? '',
            'rating' => round((float)($service['avg_rating'] ?? 0), 1),
            'review_count' => (int)($service['review_count'] ?? 0),
            'booking_type' => $service['booking_type'] ?? 'fullday',
            'duration_minutes' => (int)($service['duration_minutes'] ?? 0),
            'pricing_unit' => $service['pricing_unit'] ?? 'per_session',
        ];
    }

    private function getServiceForPackageItem($serviceId)
    {
        $this->db->dbquery(
            'SELECT services.id,
                    services.name,
                    services.price,
                    services.price_min,
                    services.price_max,
                    services.category_id,
                    services.supplier_id,
                    categories.name AS category_name,
                    categories.slug AS category_slug
             FROM services
             INNER JOIN suppliers ON suppliers.supplier_id = services.supplier_id
             LEFT JOIN categories ON categories.id = services.category_id
             WHERE services.id = :service_id
               AND services.is_active = 1
               AND suppliers.deleted_at IS NULL
               AND suppliers.status IN ("approved", "verified")
               AND suppliers.payment_status = "paid"
             LIMIT 1'
        );
        $this->db->dbbind(':service_id', (int)$serviceId);

        return $this->db->getsingledata();
    }

    private function getPackageItemForQuantityUpdate($itemId)
    {
        $this->db->dbquery(
            'SELECT pi.id,
                    c.name AS category_name,
                    c.slug AS category_slug
             FROM package_items pi
             LEFT JOIN categories c ON c.id = pi.category_id
             WHERE pi.id = :id
             LIMIT 1'
        );
        $this->db->dbbind(':id', (int)$itemId);

        return $this->db->getsingledata();
    }

    private function servicePackagePrice($service)
    {
        foreach (['price_min', 'price', 'price_max'] as $field) {
            $price = (float)($service[$field] ?? 0);
            if ($price > 0) {
                return $price;
            }
        }

        return 0.0;
    }

    private function packageUnitPriceSql($svcAlias = 'svc', $piAlias = 'pi')
    {
        return "COALESCE(NULLIF({$piAlias}.default_price, 0), NULLIF({$svcAlias}.price_min, 0), NULLIF({$svcAlias}.price, 0), NULLIF({$svcAlias}.price_max, 0), 0)";
    }

    private function packageQuantitySql($piAlias = 'pi')
    {
        if (!$this->hasPackageQuantityColumns()) {
            return '1';
        }

        return "GREATEST(1, COALESCE(NULLIF({$piAlias}.quantity, 0), 1))";
    }

    private function packageQuantityTypeSql($piAlias = 'pi')
    {
        if (!$this->hasPackageQuantityColumns()) {
            return "'fixed'";
        }

        return "COALESCE(NULLIF({$piAlias}.quantity_type, ''), 'fixed')";
    }

    private function packageLineTotalSql($svcAlias = 'svc', $piAlias = 'pi')
    {
        return '(' . $this->packageUnitPriceSql($svcAlias, $piAlias) . ' * ' . $this->packageQuantitySql($piAlias) . ')';
    }

    private function hasPackageQuantityColumns()
    {
        if ($this->packageQuantityColumns !== null) {
            return $this->packageQuantityColumns;
        }

        $this->db->dbquery(
            'SELECT COUNT(*) AS total
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = "package_items"
               AND COLUMN_NAME IN ("quantity_type", "quantity")'
        );
        $row = $this->db->getsingledata();
        $this->packageQuantityColumns = (int)($row['total'] ?? 0) === 2;

        return $this->packageQuantityColumns;
    }

    private function isGuestPricedCategory($slug, $name)
    {
        $label = strtolower(trim((string)$slug . ' ' . (string)$name));
        return strpos($label, 'food') !== false || strpos($label, 'cater') !== false;
    }

    private function slugify($text)
    {
        $text = preg_replace('/[^\p{L}\p{N}\s-]/u', '', $text);
        $text = preg_replace('/[\s_]+/', '-', $text);
        $text = preg_replace('/-+/', '-', $text);
        $text = trim($text, '-');
        $text = strtolower($text);

        return $text !== '' ? $text : 'package';
    }

    private function uniqueSlug($slug, $ignorePackageId = 0)
    {
        $baseSlug = $this->slugify($slug);
        $candidate = $baseSlug;
        $suffix = 2;

        while ($this->packageSlugExists($candidate, $ignorePackageId)) {
            $candidate = $baseSlug . '-' . $suffix;
            $suffix++;
        }

        return $candidate;
    }

    private function packageSlugExists($slug, $ignorePackageId = 0)
    {
        $sql = 'SELECT package_id
                FROM packages
                WHERE slug = :slug';

        if ((int)$ignorePackageId > 0) {
            $sql .= ' AND package_id <> :package_id';
        }

        $sql .= ' LIMIT 1';

        $this->db->dbquery($sql);
        $this->db->dbbind(':slug', $slug);
        if ((int)$ignorePackageId > 0) {
            $this->db->dbbind(':package_id', (int)$ignorePackageId);
        }

        return (bool)$this->db->getsingledata();
    }
}
