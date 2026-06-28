<?php

class PlatformPackage
{
    private $db;
    private $packageQuantityColumns = null;
    private $packageCategoryColumn = null;
    private $packageVenueRoomColumn = null;
    private $serviceRentalPricingTable = null;
    private $rentalPriceMatrixColumns = null;

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
            $tokens = preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($tokens as $i => $token) {
                $key = ':search_' . $i;
                $conditions[] = '(p.name LIKE ' . $key . ' OR p.description LIKE ' . $key . ' OR p.tagline LIKE ' . $key . ' OR p.slug LIKE ' . $key . ')';
                $bindings[$key] = '%' . $token . '%';
            }
            $compact = preg_replace('/\s+/', '', $search);
            if ($compact !== '') {
                $conditions[] = '(REPLACE(p.name, " ", "") LIKE :search_compact OR REPLACE(p.tagline, " ", "") LIKE :search_compact)';
                $bindings[':search_compact'] = '%' . $compact . '%';
            }
        }

        $status = $filters['status'] ?? '';
        if ($status === 'active') {
            $conditions[] = "p.is_active = 1 AND p.status = 'published'";
        } elseif ($status === 'inactive') {
            $conditions[] = "p.is_active = 0 AND p.status = 'published'";
        } elseif ($status === 'draft') {
            $conditions[] = "p.status = 'draft'";
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
                       p.max_concurrent,
                       p.image_url,
                       p.is_active,
                       p.status,
                       p.replaces_package_id,
                       p.sort_order,
                       p.created_at,
                       COUNT(pi.service_id) AS item_count,
                       COALESCE(SUM(CASE WHEN pi.service_id IS NOT NULL THEN ' . $this->packageLineTotalSql() . ' ELSE 0 END), 0) AS included_total,
                       EXISTS(SELECT 1 FROM booking_items bi WHERE bi.item_type = \'package\' AND bi.item_id = p.package_id LIMIT 1) AS has_bookings
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
        $categorySelect = $this->hasPackageCategoryColumn()
            ? 'p.category_id,
               pc.name AS category_name,
               pc.slug AS category_slug'
            : '(SELECT pi_cat.category_id FROM package_items pi_cat WHERE pi_cat.package_id = p.package_id AND pi_cat.category_id IS NOT NULL ORDER BY pi_cat.id ASC LIMIT 1) AS category_id,
               (SELECT c_cat.name FROM package_items pi_cat LEFT JOIN categories c_cat ON c_cat.id = pi_cat.category_id WHERE pi_cat.package_id = p.package_id AND pi_cat.category_id IS NOT NULL ORDER BY pi_cat.id ASC LIMIT 1) AS category_name,
               (SELECT c_cat.slug FROM package_items pi_cat LEFT JOIN categories c_cat ON c_cat.id = pi_cat.category_id WHERE pi_cat.package_id = p.package_id AND pi_cat.category_id IS NOT NULL ORDER BY pi_cat.id ASC LIMIT 1) AS category_slug';
        $categoryJoin = $this->hasPackageCategoryColumn() ? 'LEFT JOIN categories pc ON pc.id = p.category_id' : '';

        $this->db->dbquery(
            'SELECT p.*,
                    ' . $categorySelect . ',
                    COUNT(pi.service_id) AS item_count,
                    COALESCE(SUM(CASE WHEN pi.service_id IS NOT NULL THEN ' . $this->packageLineTotalSql() . ' ELSE 0 END), 0) AS included_total
             FROM packages p
             LEFT JOIN package_items pi ON pi.package_id = p.package_id
             LEFT JOIN services svc ON svc.id = pi.service_id
             ' . $categoryJoin . '
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
        $package['included_total'] = array_reduce($package['items'], static function ($total, $item) {
            return $total + (float)($item['default_price'] ?? 0);
        }, 0.0);
        return $this->withCustomerPackagePrice($package);
    }

    /**
     * ── Admin: Package items (fixed services) ──
     */
    public function getPackageItems($packageId)
    {
        $hallSelect = $this->hasPackageVenueRoomColumn()
            ? 'pi.venue_room_id,
               vr.name AS venue_room_name,
               vr.name AS hall_name,
               vr.capacity AS venue_room_capacity,
               vr.capacity AS hall_capacity,
               vr.price AS venue_room_price'
            : 'NULL AS venue_room_id,
               NULL AS venue_room_name,
               NULL AS hall_name,
               NULL AS venue_room_capacity,
               NULL AS hall_capacity,
               NULL AS venue_room_price';
        $hallJoin = $this->hasPackageVenueRoomColumn() ? 'LEFT JOIN venue_rooms vr ON vr.id = pi.venue_room_id' : '';

        // Rental pricing fields (borrow/buy/return_days for attire)
        if (!$this->hasServiceRentalPricingTable()) {
            $rentalSelect = ', NULL AS borrow_package_price,
               NULL AS borrow_customize_price,
               NULL AS borrow_price,
               NULL AS buy_package_price,
               NULL AS buy_customize_price,
               NULL AS buy_price,
               NULL AS return_days';
        } elseif ($this->hasRentalPriceMatrixColumns()) {
            $rentalSelect = ', srp.borrow_package_price,
               srp.borrow_customize_price,
               srp.borrow_price,
               srp.buy_package_price,
               srp.buy_customize_price,
               srp.buy_price,
               srp.return_days';
        } else {
            $rentalSelect = ', srp.borrow_price AS borrow_package_price,
               srp.borrow_price AS borrow_customize_price,
               srp.borrow_price,
               srp.buy_price AS buy_package_price,
               srp.buy_price AS buy_customize_price,
               srp.buy_price,
               srp.return_days';
        }
        $rentalJoin = $this->hasServiceRentalPricingTable()
            ? 'LEFT JOIN service_rental_pricing srp ON srp.service_id = svc.id'
            : '';

        $this->db->dbquery(
            'SELECT pi.id,
                    pi.category_id,
                    c.name AS category_name,
                    c.slug AS category_slug,
                    pi.service_id,
                    pi.default_supplier_id,
                    pi.attire_item_id,
                    ai.name AS attire_item_name,
                    ai.photo_url AS attire_item_photo,
                    pi.decoration_style_id,
                    ds.name AS decoration_style_name,
                    ' . $hallSelect . ',
                    ' . $this->packageUnitPriceSql() . ' AS unit_price,
                    ' . $this->packageLineTotalSql() . ' AS default_price,
                    ' . $this->packageCustomizePriceSql() . ' AS customize_price,
                    ' . $this->packageQuantityTypeSql() . ' AS quantity_type,
                    ' . $this->packageQuantitySql() . ' AS quantity,
                    pi.max_concurrent AS item_max_concurrent,
                    svc.name AS service_name,
                    svc.description AS service_description,
                    svc.thumbnail_url,
                    svc.price,
                    svc.price_min,
                    svc.price_max,
                    sup.shop_name AS default_supplier_name' . $rentalSelect . '
             FROM package_items pi
             LEFT JOIN categories c ON c.id = pi.category_id
             LEFT JOIN services svc ON svc.id = pi.service_id
             LEFT JOIN suppliers sup ON sup.supplier_id = pi.default_supplier_id
             LEFT JOIN attire_items ai ON ai.id = pi.attire_item_id
             LEFT JOIN decoration_styles ds ON ds.id = pi.decoration_style_id
             ' . $hallJoin . '
             ' . $rentalJoin . '
             WHERE pi.package_id = :package_id
               AND pi.service_id IS NOT NULL
             ORDER BY c.name ASC, svc.name ASC'
        );
        $this->db->dbbind(':package_id', (int)$packageId);

        return array_map([$this, 'normalizePackageItemPricing'], $this->db->getmultidata());
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

        $categoryColumn = $this->hasPackageCategoryColumn();
        $categoryFieldSql = $categoryColumn ? ', category_id' : '';
        $categoryValueSql = $categoryColumn ? ', :category_id' : '';

        $this->db->dbquery(
            'INSERT INTO packages (name, slug, description, tagline, base_price, max_concurrent, image_url, is_active, status, sort_order' . $categoryFieldSql . ')
             VALUES (:name, :slug, :description, :tagline, :base_price, :max_concurrent, :image_url, 0, :status, :sort_order' . $categoryValueSql . ')'
        );
        $this->db->dbbind(':name', $name);
        $this->db->dbbind(':slug', $slug);
        $this->db->dbbind(':description', trim((string)($data['description'] ?? '')));
        $this->db->dbbind(':tagline', trim((string)($data['tagline'] ?? '')));
        $this->db->dbbind(':base_price', (float)($data['base_price'] ?? 0));
        $this->db->dbbind(':max_concurrent', max(0, min(65535, (int)($data['max_concurrent'] ?? 0))), PDO::PARAM_INT);
        $this->db->dbbind(':image_url', trim((string)($data['image_url'] ?? '')));
        $this->db->dbbind(':status', 'draft');
        $this->db->dbbind(':sort_order', (int)($data['sort_order'] ?? 0));
        if ($categoryColumn) {
            $categoryId = (int)($data['category_id'] ?? 0);
            $this->db->dbbind(':category_id', $categoryId > 0 ? $categoryId : null, $categoryId > 0 ? PDO::PARAM_INT : PDO::PARAM_NULL);
        }

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

        if (array_key_exists('max_concurrent', $data)) {
            $fields[] = 'max_concurrent = :max_concurrent';
            $bindings[':max_concurrent'] = max(0, min(65535, (int)$data['max_concurrent']));
        }

        if (array_key_exists('is_active', $data)) {
            $fields[] = 'is_active = :is_active';
            $bindings[':is_active'] = !empty($data['is_active']) ? 1 : 0;
        }

        if (array_key_exists('sort_order', $data)) {
            $fields[] = 'sort_order = :sort_order';
            $bindings[':sort_order'] = (int)$data['sort_order'];
        }
        if ($this->hasPackageCategoryColumn() && array_key_exists('category_id', $data)) {
            $fields[] = 'category_id = :category_id';
            $categoryId = (int)$data['category_id'];
            $bindings[':category_id'] = $categoryId > 0 ? $categoryId : null;
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
     * ── Admin: Check if package has any bookings ──
     */
    public function hasPackageBookings(int $packageId): bool
    {
        $this->db->dbquery(
            "SELECT COUNT(*) AS cnt FROM booking_items WHERE item_type = 'package' AND item_id = :package_id"
        );
        $this->db->dbbind(':package_id', $packageId);
        $row = $this->db->getsingledata();
        return ((int)($row['cnt'] ?? 0)) > 0;
    }

    /**
     * ── Admin: Delete package type ──
     * Soft-deletes (sets deleted_at) when bookings reference this package,
     * hard-deletes when no bookings exist.
     */
    public function deletePackageType($packageId)
    {
        try {
            if ($this->hasPackageBookings((int)$packageId)) {
                // Soft-delete: preserve booking history
                $this->db->dbquery('UPDATE packages SET deleted_at = NOW(), is_active = 0 WHERE package_id = :package_id LIMIT 1');
                $this->db->dbbind(':package_id', (int)$packageId);
                return $this->db->dbexecute();
            }

            // Hard-delete: no bookings, safe to remove entirely
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
     * ── Admin: Restore a soft-deleted package ──
     */
    public function restorePackageType(int $packageId): bool
    {
        $this->db->dbquery('UPDATE packages SET deleted_at = NULL, is_active = 1 WHERE package_id = :package_id AND deleted_at IS NOT NULL LIMIT 1');
        $this->db->dbbind(':package_id', $packageId);
        return $this->db->dbexecute();
    }

    /**
     * ── Admin: Clone a published package into a draft for editing ──
     * Returns the new draft package ID, or false on failure.
     */
    public function clonePackageAsDraft(int $packageId): int|false
    {
        $original = $this->getPackageById($packageId);
        if (!$original) {
            return false;
        }

        // Only clone published packages
        if (($original['status'] ?? '') !== 'published') {
            return false;
        }

        $draftSlug = $this->uniqueSlug($original['slug'] . '-draft-' . time());
        $categoryColumn = $this->hasPackageCategoryColumn();

        $fields = 'name, slug, description, tagline, base_price, max_concurrent, image_url, is_active, status, replaces_package_id, sort_order';
        if ($categoryColumn) {
            $fields .= ', category_id';
        }

        $this->db->dbquery(
            "INSERT INTO packages ({$fields})
             SELECT name, :draft_slug, description, tagline, base_price, max_concurrent, image_url, 0, 'draft', :original_id, sort_order"
            . ($categoryColumn ? ', category_id' : '') . "
             FROM packages
             WHERE package_id = :original_package_id
             LIMIT 1"
        );
        $this->db->dbbind(':draft_slug', $draftSlug);
        $this->db->dbbind(':original_id', $packageId, PDO::PARAM_INT);
        $this->db->dbbind(':original_package_id', $packageId, PDO::PARAM_INT);

        if (!$this->db->dbexecute()) {
            return false;
        }

        $draftId = (int)$this->db->lastinsertid();
        if ($draftId <= 0) {
            return false;
        }

        // Clone package items
        $this->clonePackageItems($packageId, $draftId);

        return $draftId;
    }

    /**
     * Copy package_items from source to target package.
     */
    private function clonePackageItems(int $sourcePackageId, int $targetPackageId): void
    {
        $columns = ['category_id', 'service_id', 'default_supplier_id', 'default_price'];
        if ($this->hasPackagePriceColumn()) {
            $columns[] = 'customize_price';
        }
        if ($this->hasPackageQuantityColumns()) {
            $columns[] = 'quantity_type';
            $columns[] = 'quantity';
        }
        if ($this->hasPackageVenueRoomColumn()) {
            $columns[] = 'venue_room_id';
        }
        if ($this->hasPackageItemConcurrentColumn()) {
            $columns[] = 'max_concurrent';
        }

        $this->db->dbquery(
            'INSERT INTO package_items (package_id, ' . implode(', ', $columns) . ')'
            . ' SELECT :target_id, ' . implode(', ', $columns)
            . ' FROM package_items'
            . ' WHERE package_id = :source_id'
        );
        $this->db->dbbind(':target_id', $targetPackageId, PDO::PARAM_INT);
        $this->db->dbbind(':source_id', $sourcePackageId, PDO::PARAM_INT);
        $this->db->dbexecute();
    }

    /**
     * ── Admin: Publish a draft packaged, replacing its original ──
     * Returns the published package ID, or false on failure.
     */
    public function publishDraft(int $draftPackageId): int|false
    {
        $draft = $this->getPackageById($draftPackageId);
        if (!$draft || ($draft['status'] ?? '') !== 'draft') {
            return false;
        }

        $originalId = (int)($draft['replaces_package_id'] ?? 0);

        // Soft-delete the original published package if replacing
        if ($originalId > 0) {
            $this->db->dbquery(
                'UPDATE packages SET deleted_at = NOW() WHERE package_id = :id AND status = :status LIMIT 1'
            );
            $this->db->dbbind(':id', $originalId, PDO::PARAM_INT);
            $this->db->dbbind(':status', 'published');
            $this->db->dbexecute();
        }

        // Clean up the slug — remove draft timestamp suffix
        $cleanSlug = $this->slugify(preg_replace('/-draft-\d+$/', '', $draft['slug'] ?? ''));
        $cleanSlug = $this->uniqueSlug($cleanSlug, $draftPackageId);

        // Publish the draft
        $this->db->dbquery(
            'UPDATE packages
             SET status = :published, is_active = 1, replaces_package_id = NULL, slug = :slug
             WHERE package_id = :id
             LIMIT 1'
        );
        $this->db->dbbind(':published', 'published');
        $this->db->dbbind(':slug', $cleanSlug);
        $this->db->dbbind(':id', $draftPackageId, PDO::PARAM_INT);

        if (!$this->db->dbexecute()) {
            return false;
        }

        return $draftPackageId;
    }

    /**
     * ── Admin: Discard a draft (delete it permanently).
     * Returns true on success, false on failure.
     */
    public function discardDraft(int $draftPackageId): bool
    {
        $draft = $this->getPackageById($draftPackageId);
        if (!$draft || ($draft['status'] ?? '') !== 'draft') {
            return false;
        }

        // Delete items first
        $this->db->dbquery('DELETE FROM package_items WHERE package_id = :id');
        $this->db->dbbind(':id', $draftPackageId, PDO::PARAM_INT);
        $this->db->dbexecute();

        // Delete the draft
        $this->db->dbquery('DELETE FROM packages WHERE package_id = :id AND status = :draft LIMIT 1');
        $this->db->dbbind(':id', $draftPackageId, PDO::PARAM_INT);
        $this->db->dbbind(':draft', 'draft');

        return $this->db->dbexecute();
    }

    /**
     * Check if a package is a draft — guard for edit operations.
     */
    public function isDraft(int $packageId): bool
    {
        $this->db->dbquery('SELECT status FROM packages WHERE package_id = :id LIMIT 1');
        $this->db->dbbind(':id', $packageId, PDO::PARAM_INT);
        $row = $this->db->getsingledata();
        return ($row['status'] ?? '') === 'draft';
    }

    /**
     * Check if a package is published — guard for clone operations.
     */
    public function isPublished(int $packageId): bool
    {
        $this->db->dbquery('SELECT status FROM packages WHERE package_id = :id LIMIT 1');
        $this->db->dbbind(':id', $packageId, PDO::PARAM_INT);
        $row = $this->db->getsingledata();
        return ($row['status'] ?? '') === 'published';
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

    public function addPackageService($packageId, $serviceId, $quantity = null, $hallId = null, $attireItemId = null, $decorationStyleId = null, $itemMaxConcurrent = null)
    {
        $service = $this->getServiceForPackageItem($serviceId);
        if (!$service) {
            return false;
        }

        $serviceCategoryId = (int)($service['category_id'] ?? 0);
        if ($this->hasPackageCategoryColumn() && $serviceCategoryId > 0) {
            $this->db->dbquery('UPDATE packages SET category_id = :category_id WHERE package_id = :package_id AND category_id IS NULL');
            $this->db->dbbind(':category_id', $serviceCategoryId);
            $this->db->dbbind(':package_id', (int)$packageId);
            $this->db->dbexecute();
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

        $room = null;
        $hallId = (int)($hallId ?? 0);
        if ($hallId > 0) {
            $room = $this->getVenueRoomForService((int)$serviceId, $hallId);
            if (!$room) {
                return false;
            }
        }

        $attireItem = null;
        $attireItemId = (int)($attireItemId ?? 0);
        $isAttireService = $this->isAttireCategory(
            $service['category_slug'] ?? '',
            $service['category_name'] ?? ''
        );
        if ($isAttireService && $attireItemId > 0) {
            $attireItem = $this->getAttireItemForService($attireItemId, (int)$serviceId);
            if (!$attireItem) {
                return false;
            }
        } elseif ($isAttireService) {
            return false;
        }

        $decorationStyle = null;
        $decorationStyleId = (int)($decorationStyleId ?? 0);
        if ($decorationStyleId > 0) {
            $decorationStyle = $this->getDecorationStyleById($decorationStyleId);
            if (!$decorationStyle) {
                return false;
            }
        }

        $packagePrice = $this->servicePackagePrice($service);
        $customizePrice = $this->serviceCustomizePrice($service);
        if ($attireItem) {
            $borrowPackagePrice = (float)($attireItem['borrow_package_price'] ?? 0);
            $buyPackagePrice = (float)($attireItem['buy_package_price'] ?? 0);
            $packagePrice = $borrowPackagePrice > 0 ? $borrowPackagePrice : $buyPackagePrice;

            $borrowCustomizePrice = (float)($attireItem['borrow_customize_price'] ?? 0);
            $buyCustomizePrice = (float)($attireItem['buy_customize_price'] ?? 0);
            $customizePrice = $borrowPackagePrice > 0
                ? ($borrowCustomizePrice > 0 ? $borrowCustomizePrice : $packagePrice)
                : ($buyCustomizePrice > 0 ? $buyCustomizePrice : $packagePrice);
        }
        $price = $room ? (float)($room['price'] ?? 0)
            : ($attireItem ? $packagePrice
            : ($decorationStyle ? (float)($decorationStyle['package_price'] ?? $decorationStyle['price'] ?? 0)
            : $packagePrice));
        $isGuestPriced = $this->isGuestPricedCategory($service['category_slug'] ?? '', $service['category_name'] ?? '');
        $itemQuantity = $isGuestPriced ? max(1, (int)($quantity ?: 100)) : 1;
        $quantityType = $isGuestPriced ? 'guests' : 'fixed';
        $quantityColumns = $this->hasPackageQuantityColumns();
        $hallColumn = $this->hasPackageVenueRoomColumn();
        $priceColumn = $this->hasPackagePriceColumn();
        $itemConcurrentColumn = $this->hasPackageItemConcurrentColumn();

        $quantityColumnsSql = $quantityColumns ? ', quantity_type, quantity' : '';
        $quantityValuesSql = $quantityColumns ? ', :quantity_type, :quantity' : '';
        $hallColumnSql = $hallColumn ? ', venue_room_id' : '';
        $hallValueSql = $hallColumn ? ', :venue_room_id' : '';
        $attireColumnSql = ', attire_item_id';
        $attireValueSql = ', :attire_item_id';
        $decoColumnSql = ', decoration_style_id';
        $decoValueSql = ', :decoration_style_id';
        $priceSql = $priceColumn ? ', customize_price' : '';
        $priceVal = $priceColumn ? ', :customize_price' : '';
        $concurrentCol = $itemConcurrentColumn ? ', max_concurrent' : '';
        $concurrentVal = $itemConcurrentColumn ? ', :item_max_concurrent' : '';

        $this->db->dbquery(
            'INSERT INTO package_items (package_id, category_id, service_id, default_supplier_id, default_price' . $priceSql . $quantityColumnsSql . $hallColumnSql . $attireColumnSql . $decoColumnSql . $concurrentCol . ')
             VALUES (:package_id, :category_id, :service_id, :supplier_id, :default_price' . $priceVal . $quantityValuesSql . $hallValueSql . $attireValueSql . $decoValueSql . $concurrentVal . ')'
        );
        $this->db->dbbind(':package_id', (int)$packageId);
        $this->db->dbbind(':category_id', (int)($service['category_id'] ?? 0));
        $this->db->dbbind(':service_id', (int)$serviceId);
        $this->db->dbbind(':supplier_id', (int)($service['supplier_id'] ?? 0));
        $this->db->dbbind(':default_price', $price);
        if ($priceColumn) {
            $this->db->dbbind(':customize_price', $customizePrice > $price ? $customizePrice : null,
                $customizePrice > $price ? PDO::PARAM_STR : PDO::PARAM_NULL);
        }
        if ($quantityColumns) {
            $this->db->dbbind(':quantity_type', $quantityType);
            $this->db->dbbind(':quantity', $itemQuantity);
        }
        if ($hallColumn) {
            $this->db->dbbind(':venue_room_id', $room ? (int)$room['id'] : null, $room ? PDO::PARAM_INT : PDO::PARAM_NULL);
        }
        $this->db->dbbind(':attire_item_id', $attireItemId > 0 ? $attireItemId : null, $attireItemId > 0 ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $this->db->dbbind(':decoration_style_id', $decorationStyleId > 0 ? $decorationStyleId : null, $decorationStyleId > 0 ? PDO::PARAM_INT : PDO::PARAM_NULL);
        if ($itemConcurrentColumn) {
            $itemMaxConcurrent = $itemMaxConcurrent !== null
                ? max(0, min(65535, (int)$itemMaxConcurrent))
                : null;
            $this->db->dbbind(':item_max_concurrent', $itemMaxConcurrent, $itemMaxConcurrent !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
        }

        return $this->db->dbexecute();
    }

    private function getAttireItemForService(int $attireItemId, int $serviceId): ?array
    {
        $this->db->dbquery(
            'SELECT *
             FROM attire_items
             WHERE id = :id
               AND service_id = :service_id
             LIMIT 1'
        );
        $this->db->dbbind(':id', $attireItemId);
        $this->db->dbbind(':service_id', $serviceId);
        return $this->db->getsingledata();
    }

    private function getDecorationStyleById(int $styleId): ?array
    {
        $this->db->dbquery('SELECT * FROM decoration_styles WHERE id = :id LIMIT 1');
        $this->db->dbbind(':id', $styleId);
        return $this->db->getsingledata();
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

    public function updatePackageItemHall($itemId, $hallId)
    {
        if (!$this->hasPackageVenueRoomColumn()) {
            return false;
        }

        $this->db->dbquery(
            'SELECT pi.service_id
             FROM package_items pi
             WHERE pi.id = :id
             LIMIT 1'
        );
        $this->db->dbbind(':id', (int)$itemId);
        $item = $this->db->getsingledata();
        if (!$item) {
            return false;
        }

        $hallId = (int)$hallId;
        $price = null;

        if ($hallId > 0) {
            $room = $this->getVenueRoomForService((int)$item['service_id'], $hallId);
            if (!$room) {
                return false;
            }
            $price = (float)($room['price'] ?? 0);
        } else {
            // No specific hall — fall back to the service's default package price.
            $service = $this->getServiceForPackageItem((int)$item['service_id']);
            $price = $service ? $this->servicePackagePrice($service) : 0;
        }

        $this->db->dbquery(
            'UPDATE package_items
             SET venue_room_id = :hall_id,
                 default_price = :default_price
             WHERE id = :id
             LIMIT 1'
        );
        $this->db->dbbind(':hall_id', $hallId > 0 ? $hallId : null, $hallId > 0 ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $this->db->dbbind(':default_price', $price);
        $this->db->dbbind(':id', (int)$itemId);

        return $this->db->dbexecute();
    }

    public function getAttireItemsForService($serviceId)
    {
        $this->db->dbquery(
            'SELECT id, name, description, photo_url,
                    borrow_package_price, borrow_customize_price,
                    buy_package_price, buy_customize_price,
                    return_days
             FROM attire_items
             WHERE service_id = :service_id
             ORDER BY sort_order ASC, name ASC'
        );
        $this->db->dbbind(':service_id', (int)$serviceId);
        return $this->db->getmultidata();
    }

    public function getDecorationStylesForService($serviceId)
    {
        $this->db->dbquery(
            'SELECT id, name, price, package_price, customize_price, photo_url
             FROM decoration_styles
             WHERE service_id = :service_id
             ORDER BY sort_order ASC, name ASC'
        );
        $this->db->dbbind(':service_id', (int)$serviceId);
        return $this->db->getmultidata();
    }

    public function getVenueRoomsForService($serviceId)
    {
        $this->db->dbquery(
            'SELECT vr.id,
                    vr.name,
                    vr.capacity,
                    vr.price,
                    v.name AS venue_name,
                    v.location AS venue_location,
                    v.description AS venue_description
             FROM venues v
             INNER JOIN venue_rooms vr ON vr.venue_id = v.id
             WHERE v.service_id = :service_id
             ORDER BY vr.name ASC'
        );
        $this->db->dbbind(':service_id', (int)$serviceId);

        return $this->db->getmultidata();
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
    public function getPackageTypes($filters = [])
    {
        $hasPackageCategory = $this->hasPackageCategoryColumn();
        $conditions = ['p.is_active = 1', 'p.deleted_at IS NULL'];
        $bindings = [];
        $orderClause = 'p.sort_order ASC';

        // Search
        $search = trim((string)($filters['search'] ?? ''));
        if ($search !== '') {
            $tokens = preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($tokens as $i => $token) {
                $key = ':search_' . $i;
                $conditions[] = '(p.name LIKE ' . $key . ' OR p.tagline LIKE ' . $key . ' OR p.description LIKE ' . $key . ')';
                $bindings[$key] = '%' . $token . '%';
            }
            $compact = preg_replace('/\s+/', '', $search);
            if ($compact !== '') {
                $conditions[] = '(REPLACE(p.name, " ", "") LIKE :search_compact OR REPLACE(p.tagline, " ", "") LIKE :search_compact)';
                $bindings[':search_compact'] = '%' . $compact . '%';
            }
        }

        // Category filter
        $category = trim((string)($filters['category'] ?? ''));
        if ($category !== '' && $category !== 'all') {
            if ($hasPackageCategory) {
                $conditions[] = '(pc.slug = :cat_slug OR pc.name = :cat_name)';
            } else {
                $conditions[] = 'EXISTS (
                    SELECT 1 FROM package_items pi2
                    LEFT JOIN categories c2 ON c2.id = pi2.category_id
                    WHERE pi2.package_id = p.package_id
                      AND (c2.slug = :cat_slug OR c2.name = :cat_name)
                )';
            }
            $bindings[':cat_slug'] = $category;
            $bindings[':cat_name'] = $category;
        }

        // Sort
        $sort = $filters['sort'] ?? 'featured';
        if ($sort === 'price_low') {
            $orderClause = 'p.base_price ASC, p.sort_order ASC';
        } elseif ($sort === 'price_high') {
            $orderClause = 'p.base_price DESC, p.sort_order ASC';
        } elseif ($sort === 'name_az') {
            $orderClause = 'p.name ASC';
        } elseif ($sort === 'name_za') {
            $orderClause = 'p.name DESC';
        }

        $where = implode(' AND ', $conditions);
        $categoryJoin = $hasPackageCategory ? 'LEFT JOIN categories pc ON pc.id = p.category_id' : '';

        $sql = 'SELECT p.package_id,
                       p.name,
                       p.slug,
                       p.description,
                       p.tagline,
                       p.base_price,
                       p.max_concurrent,
                       p.image_url,
                       p.sort_order,
                       COUNT(pi.service_id) AS item_count,
                       COALESCE(SUM(CASE WHEN pi.service_id IS NOT NULL THEN ' . $this->packageLineTotalSql() . ' ELSE 0 END), 0) AS included_total
                FROM packages p
                LEFT JOIN package_items pi ON pi.package_id = p.package_id
                LEFT JOIN services svc ON svc.id = pi.service_id
                ' . $categoryJoin . '
                WHERE ' . $where . '
                GROUP BY p.package_id
                ORDER BY ' . $orderClause;

        $this->db->dbquery($sql);
        foreach ($bindings as $param => $value) {
            $this->db->dbbind($param, $value);
        }

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
            $pkg = $this->withCustomerPackagePrice($pkg);
        }
        unset($pkg);

        return $packages;
    }

    /**
     * ── Customer: Get distinct categories across all packages ──
     */
    public function getPackageCategories($filters = [])
    {
        $hasPackageCategory = $this->hasPackageCategoryColumn();
        $conditions = ['p.is_active = 1', 'p.deleted_at IS NULL'];
        $bindings = [];

        $search = trim((string)($filters['search'] ?? ''));
        if ($search !== '') {
            $tokens = preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($tokens as $i => $token) {
                $key = ':search_' . $i;
                $conditions[] = '(p.name LIKE ' . $key . ' OR p.tagline LIKE ' . $key . ' OR p.description LIKE ' . $key . ')';
                $bindings[$key] = '%' . $token . '%';
            }
            $compact = preg_replace('/\s+/', '', $search);
            if ($compact !== '') {
                $conditions[] = '(REPLACE(p.name, " ", "") LIKE :search_compact OR REPLACE(p.tagline, " ", "") LIKE :search_compact)';
                $bindings[':search_compact'] = '%' . $compact . '%';
            }
        }

        $where = implode(' AND ', $conditions);

        if ($hasPackageCategory) {
            $sql = 'SELECT c.name, c.slug, COUNT(DISTINCT p.package_id) AS service_count
                    FROM packages p
                    LEFT JOIN categories c ON c.id = p.category_id
                    WHERE ' . $where . '
                      AND c.id IS NOT NULL
                    GROUP BY c.id, c.name, c.slug
                    ORDER BY c.name ASC';
        } else {
            $sql = 'SELECT c.name, c.slug, COUNT(DISTINCT p.package_id) AS service_count
                    FROM packages p
                    JOIN package_items pi ON pi.package_id = p.package_id
                    LEFT JOIN categories c ON c.id = pi.category_id
                    WHERE ' . $where . '
                      AND pi.service_id IS NOT NULL
                      AND c.id IS NOT NULL
                    GROUP BY c.id, c.name, c.slug
                    ORDER BY c.name ASC';
        }

        $this->db->dbquery($sql);
        foreach ($bindings as $param => $value) {
            $this->db->dbbind($param, $value);
        }

        return $this->db->getmultidata();
    }

    /**
     * ── Customer: Get package type by slug ──
     */
    public function getPackageBySlug($slug)
    {
        if (!$this->hasServiceRentalPricingTable()) {
            $rentalSelect = 'NULL AS borrow_package_price,
               NULL AS borrow_customize_price,
               NULL AS borrow_price,
               NULL AS buy_package_price,
               NULL AS buy_customize_price,
               NULL AS buy_price,
               NULL AS return_days,';
        } elseif ($this->hasRentalPriceMatrixColumns()) {
            $rentalSelect = 'srp.borrow_package_price,
               srp.borrow_customize_price,
               srp.borrow_price,
               srp.buy_package_price,
               srp.buy_customize_price,
               srp.buy_price,
               srp.return_days,';
        } else {
            $rentalSelect = 'srp.borrow_price AS borrow_package_price,
               srp.borrow_price AS borrow_customize_price,
               srp.borrow_price,
               srp.buy_price AS buy_package_price,
               srp.buy_price AS buy_customize_price,
               srp.buy_price,
               srp.return_days,';
        }
        $rentalJoin = $this->hasServiceRentalPricingTable()
            ? 'LEFT JOIN service_rental_pricing srp ON srp.service_id = svc.id'
            : '';
        $hallSelect = $this->hasPackageVenueRoomColumn()
            ? 'pi.venue_room_id,
               vr.name AS venue_room_name,
               vr.capacity AS venue_room_capacity,
               vr.price AS venue_room_price'
            : 'NULL AS venue_room_id,
               NULL AS venue_room_name,
               NULL AS venue_room_capacity,
               NULL AS venue_room_price';
        $hallJoin = $this->hasPackageVenueRoomColumn() ? 'LEFT JOIN venue_rooms vr ON vr.id = pi.venue_room_id' : '';

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
                    ' . $hallSelect . ',
                    ' . $this->packageUnitPriceSql() . ' AS unit_price,
                    ' . $this->packageLineTotalSql() . ' AS default_price,
                    ' . $this->packageCustomizePriceSql() . ' AS customize_price,
                    ' . $this->packageQuantityTypeSql() . ' AS quantity_type,
                    ' . $this->packageQuantitySql() . ' AS quantity,
                    pi.max_concurrent AS item_max_concurrent,
                    svc.name AS service_name,
                    svc.description AS service_description,
                    svc.thumbnail_url,
                    svc.price,
                    svc.price_min,
                    svc.price_max,
                    svc.booking_type,
                    svc.duration_minutes,
                    svc.pricing_unit,
                    ' . $rentalSelect . '
                    sup.shop_name AS supplier_name,
                    COALESCE(review_stats.avg_rating, 0) AS avg_rating,
                    COALESCE(review_stats.review_count, 0) AS review_count
             FROM package_items pi
             LEFT JOIN categories c ON c.id = pi.category_id
             LEFT JOIN services svc ON svc.id = pi.service_id
             LEFT JOIN suppliers sup ON sup.supplier_id = pi.default_supplier_id
             ' . $rentalJoin . '
             ' . $hallJoin . '
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
        $items = array_map([$this, 'normalizePackageItemPricing'], $this->db->getmultidata());
        $package['items'] = $items;
        $package['included_total'] = array_reduce($items, static function ($total, $item) {
            return $total + (float)($item['default_price'] ?? 0);
        }, 0.0);
        $package = $this->withCustomerPackagePrice($package);
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
                'borrow_package_price' => $item['borrow_package_price'] ?? null,
                'borrow_customize_price' => $item['borrow_customize_price'] ?? null,
                'borrow_price' => $item['borrow_price'] ?? null,
                'buy_package_price' => $item['buy_package_price'] ?? null,
                'buy_customize_price' => $item['buy_customize_price'] ?? null,
                'buy_price' => $item['buy_price'] ?? null,
                'return_days' => $item['return_days'] ?? null,
            ]) + [
                'category_id' => (int)($item['category_id'] ?? 0),
                'category_name' => $item['category_name'] ?? '',
                'category_slug' => $item['category_slug'] ?? '',
                'package_item_id' => (int)($item['item_id'] ?? 0),
                'package_price' => (float)($item['default_price'] ?? 0),
                'unit_price' => (float)($item['unit_price'] ?? $item['default_price'] ?? 0),
                'quantity_type' => $item['quantity_type'] ?? 'fixed',
                'quantity' => (int)($item['quantity'] ?? 1),
                'venue_room_id' => (int)($item['venue_room_id'] ?? 0),
                'venue_room_name' => $item['venue_room_name'] ?? '',
                'venue_room_capacity' => (int)($item['venue_room_capacity'] ?? 0),
                'venue_room_price' => (float)($item['venue_room_price'] ?? 0),
            ];
        }, $items)));
        $package['categories'] = $items;

        return $package;
    }

    public function getServicePackageContext($packageId, $packageItemId, $serviceId)
    {
        $hallSelect = $this->hasPackageVenueRoomColumn()
            ? 'pi.venue_room_id,
               vr.name AS venue_room_name,
               vr.capacity AS venue_room_capacity,
               vr.price AS venue_room_price,'
            : 'NULL AS venue_room_id,
               NULL AS venue_room_name,
               NULL AS venue_room_capacity,
               NULL AS venue_room_price,';
        $hallJoin = $this->hasPackageVenueRoomColumn() ? 'LEFT JOIN venue_rooms vr ON vr.id = pi.venue_room_id' : '';

        $this->db->dbquery(
            'SELECT p.package_id,
                    p.name AS package_name,
                    p.slug AS package_slug,
                    pi.id AS package_item_id,
                    pi.service_id,
                    ' . $hallSelect . '
                    ' . $this->packageUnitPriceSql() . ' AS unit_price,
                    ' . $this->packageLineTotalSql() . ' AS package_price,
                    ' . $this->packageCustomizePriceSql() . ' AS customize_price,
                    ' . $this->packageQuantityTypeSql() . ' AS quantity_type,
                    ' . $this->packageQuantitySql() . ' AS quantity,
                    pi.max_concurrent AS item_max_concurrent
             FROM package_items pi
             INNER JOIN packages p ON p.package_id = pi.package_id
             LEFT JOIN services svc ON svc.id = pi.service_id
             ' . $hallJoin . '
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
        $context['venue_room_id'] = (int)($context['venue_room_id'] ?? 0);
        $context['venue_room_capacity'] = (int)($context['venue_room_capacity'] ?? 0);
        $context['venue_room_price'] = (float)($context['venue_room_price'] ?? 0);
        $context['unit_price'] = (float)($context['unit_price'] ?? 0);
        $context['package_price'] = (float)($context['package_price'] ?? 0);
        $context['quantity'] = (int)($context['quantity'] ?? 1);

        return $context;
    }

    public function getAddonServices(int $packageId, int $limit = 6): array
    {
        $limit = max(1, min(12, $limit));
        $this->db->dbquery(
            'SELECT services.id,
                    services.name,
                    services.description,
                    services.thumbnail_url AS image,
                    COALESCE(services.price_max, services.price_min, services.price, 0) AS display_price,
                    categories.name AS category_name,
                    categories.slug AS category_slug,
                    suppliers.shop_name AS supplier_name
             FROM services
             INNER JOIN suppliers ON suppliers.supplier_id = services.supplier_id
             LEFT JOIN categories ON categories.id = services.category_id
             WHERE services.is_active = 1
               AND suppliers.deleted_at IS NULL
               AND suppliers.is_available = 1
               AND suppliers.status IN ("approved", "verified")
               AND suppliers.payment_status = "paid"
               AND NOT EXISTS (
                   SELECT 1
                   FROM package_items
                   WHERE package_items.package_id = :package_id
                     AND package_items.service_id = services.id
                     AND package_items.deleted_at IS NULL
               )
             ORDER BY services.created_at DESC, services.id DESC
             LIMIT :limit'
        );
        $this->db->dbbind(':package_id', $packageId, PDO::PARAM_INT);
        $this->db->dbbind(':limit', $limit, PDO::PARAM_INT);

        return $this->db->getmultidata();
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
                    p.max_concurrent,
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

        return array_map([$this, 'withCustomerPackagePrice'], $this->db->getmultidata());
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
            'borrow_package_price' => ($service['borrow_package_price'] ?? null) !== null ? (float)$service['borrow_package_price'] : null,
            'borrow_customize_price' => ($service['borrow_customize_price'] ?? null) !== null ? (float)$service['borrow_customize_price'] : null,
            'borrow_price' => ($service['borrow_price'] ?? null) !== null ? (float)$service['borrow_price'] : null,
            'buy_package_price' => ($service['buy_package_price'] ?? null) !== null ? (float)$service['buy_package_price'] : null,
            'buy_customize_price' => ($service['buy_customize_price'] ?? null) !== null ? (float)$service['buy_customize_price'] : null,
            'buy_price' => ($service['buy_price'] ?? null) !== null ? (float)$service['buy_price'] : null,
            'return_days' => ($service['return_days'] ?? null) !== null ? (int)$service['return_days'] : null,
        ];
    }

    private function withCustomerPackagePrice(array $package): array
    {
        $includedTotal = (float)($package['included_total'] ?? 0);
        $storedBasePrice = (float)($package['base_price'] ?? 0);
        $packageBasePrice = $storedBasePrice > 0 ? $storedBasePrice : $includedTotal;
        $agentFeeRate = get_platform_fee_percent() / 100;
        $agentFee = $packageBasePrice * $agentFeeRate;

        $package['package_base_price'] = $packageBasePrice;
        $package['agent_fee_rate'] = $agentFeeRate;
        $package['agent_fee'] = $agentFee;
        $package['package_price'] = $packageBasePrice + $agentFee;

        return $package;
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

    private function packageCustomizePriceSql($piAlias = 'pi')
    {
        if (!$this->hasPackagePriceColumn()) {
            return '0';
        }
        return 'COALESCE(NULLIF(' . $piAlias . '.customize_price, 0), COALESCE(NULLIF(' . $piAlias . '.default_price, 0), 0))';
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

    private function hasPackageCategoryColumn()
    {
        if ($this->packageCategoryColumn !== null) {
            return $this->packageCategoryColumn;
        }

        $this->packageCategoryColumn = $this->tableHasColumn('packages', 'category_id');
        return $this->packageCategoryColumn;
    }

    private function hasPackageVenueRoomColumn()
    {
        if ($this->packageVenueRoomColumn !== null) {
            return $this->packageVenueRoomColumn;
        }

        $this->packageVenueRoomColumn = $this->tableHasColumn('package_items', 'venue_room_id');
        return $this->packageVenueRoomColumn;
    }

    private function hasServiceRentalPricingTable()
    {
        if ($this->serviceRentalPricingTable !== null) {
            return $this->serviceRentalPricingTable;
        }

        $this->serviceRentalPricingTable = $this->tableExists('service_rental_pricing');
        return $this->serviceRentalPricingTable;
    }

    private function hasRentalPriceMatrixColumns()
    {
        if ($this->rentalPriceMatrixColumns !== null) {
            return $this->rentalPriceMatrixColumns;
        }

        if (!$this->hasServiceRentalPricingTable()) {
            $this->rentalPriceMatrixColumns = false;
            return false;
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
        $this->rentalPriceMatrixColumns = (int)($row['total'] ?? 0) >= 4;

        return $this->rentalPriceMatrixColumns;
    }

    private function tableExists($tableName)
    {
        $this->db->dbquery(
            'SELECT COUNT(*) AS total
             FROM INFORMATION_SCHEMA.TABLES
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = :table_name'
        );
        $this->db->dbbind(':table_name', (string)$tableName);
        $row = $this->db->getsingledata();

        return (int)($row['total'] ?? 0) > 0;
    }

    private function tableHasColumn($tableName, $columnName)
    {
        $this->db->dbquery(
            'SELECT COUNT(*) AS total
             FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = :table_name
               AND COLUMN_NAME = :column_name'
        );
        $this->db->dbbind(':table_name', (string)$tableName);
        $this->db->dbbind(':column_name', (string)$columnName);
        $row = $this->db->getsingledata();

        return (int)($row['total'] ?? 0) > 0;
    }

    private function getVenueRoomForService($serviceId, $roomId)
    {
        $this->db->dbquery(
            'SELECT vr.id,
                    vr.name,
                    vr.capacity,
                    vr.price
             FROM venues v
             INNER JOIN venue_rooms vr ON vr.venue_id = v.id
             WHERE v.service_id = :service_id
               AND vr.id = :room_id
             LIMIT 1'
        );
        $this->db->dbbind(':service_id', (int)$serviceId);
        $this->db->dbbind(':room_id', (int)$roomId);

        return $this->db->getsingledata();
    }

    private function isGuestPricedCategory($slug, $name)
    {
        $label = strtolower(trim((string)$slug . ' ' . (string)$name));
        // Only services whose price scales with attendee count are guest-driven.
        // Attire is priced per selected outfit/item, so it remains fixed.
        return strpos($label, 'food') !== false
            || strpos($label, 'cater') !== false
            || strpos($label, 'decor') !== false
            || strpos($label, 'music') !== false
            || strpos($label, 'photo') !== false
            || strpos($label, 'makeup') !== false
            || strpos($label, 'studio') !== false;
    }

    private function isAttireCategory($slug, $name): bool
    {
        return strpos(strtolower(trim((string)$slug . ' ' . (string)$name)), 'attire') !== false;
    }

    private function normalizePackageItemPricing(array $item): array
    {
        $isGuestPriced = $this->isGuestPricedCategory(
            $item['category_slug'] ?? '',
            $item['category_name'] ?? ''
        );

        // Keep legacy attire rows from displaying or totaling as per-guest items.
        if (!$isGuestPriced && ($item['quantity_type'] ?? '') === 'guests') {
            $item['quantity_type'] = 'fixed';
            $item['quantity'] = 1;
            $item['default_price'] = (float)($item['unit_price'] ?? $item['default_price'] ?? 0);
        }

        return $item;
    }

    private function serviceCustomizePrice($service)
    {
        foreach (['price_max', 'price_min', 'price'] as $field) {
            $price = (float)($service[$field] ?? 0);
            if ($price > 0) {
                return $price;
            }
        }
        return 0.0;
    }

    private function hasPackagePriceColumn()
    {
        static $has = null;
        if ($has !== null) return $has;
        $this->db->dbquery("SHOW COLUMNS FROM package_items LIKE 'customize_price'");
        $has = (bool)$this->db->getsingledata();
        return $has;
    }

    private function hasPackageItemConcurrentColumn()
    {
        static $has = null;
        if ($has !== null) return $has;
        $this->db->dbquery("SHOW COLUMNS FROM package_items LIKE 'max_concurrent'");
        $has = (bool)$this->db->getsingledata();
        return $has;
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

    /**
     * Get IDs of sub-items (venue rooms, attire items, decoration styles)
     * that are locked to active published packages.
     * These items should not be available for customize (standalone) booking.
     */
    public function getLockedItemIds(): array
    {
        $result = [
            'venue_room_ids' => [],
            'attire_item_ids' => [],
            'decoration_style_ids' => [],
            'food_item_ids' => [],
        ];

        $this->db->dbquery(
            "SELECT DISTINCT pi.venue_room_id, pi.attire_item_id, pi.decoration_style_id, pi.cake_design_id
             FROM package_items pi
             INNER JOIN packages p ON p.package_id = pi.package_id
             WHERE pi.deleted_at IS NULL
               AND p.is_active = 1
               AND p.status = 'published'"
        );
        $rows = $this->db->getmultidata();

        foreach ($rows as $row) {
            if (!empty($row['venue_room_id'])) {
                $result['venue_room_ids'][] = (int)$row['venue_room_id'];
            }
            if (!empty($row['attire_item_id'])) {
                $result['attire_item_ids'][] = (int)$row['attire_item_id'];
            }
            if (!empty($row['decoration_style_id'])) {
                $result['decoration_style_ids'][] = (int)$row['decoration_style_id'];
            }
            if (!empty($row['cake_design_id'])) {
                $result['food_item_ids'][] = (int)$row['cake_design_id'];
            }
        }

        $result['venue_room_ids'] = array_values(array_unique($result['venue_room_ids']));
        $result['attire_item_ids'] = array_values(array_unique($result['attire_item_ids']));
        $result['decoration_style_ids'] = array_values(array_unique($result['decoration_style_ids']));
        $result['food_item_ids'] = array_values(array_unique($result['food_item_ids']));

        return $result;
    }
}
