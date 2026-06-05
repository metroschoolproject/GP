<?php

class SupplierProfile
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    private function getUserId($data)
    {
        if (!empty($data['user_id'])) {
            return (int)$data['user_id'];
        }

        $this->db->dbquery('SELECT user_id FROM users WHERE email = :email LIMIT 1');
        $this->db->dbbind(':email', $data['email']);
        $user = $this->db->getsingledata();

        return $user ? (int)$user['user_id'] : null;
    }

    public function getByUserId($userId)
    {
        $this->db->dbquery(
            'SELECT suppliers.supplier_id,
                    suppliers.user_id,
                    suppliers.shop_name,
                    suppliers.description,
                    suppliers.status,
                    suppliers.agreement_accepted,
                    suppliers.agreement_accepted_at,
                    suppliers.agreement_version,
                    suppliers.payment_status,
                    suppliers.is_available,
                    suppliers.verify_url AS business_url,
                    users.name AS owner_name,
                    users.email AS owner_email,
                    users.phone AS owner_phone,
                    users.address AS owner_address,
                    (
                        SELECT GROUP_CONCAT(categories.name ORDER BY categories.name SEPARATOR \', \')
                        FROM supplier_categories
                        LEFT JOIN categories ON categories.id = supplier_categories.category_id
                        WHERE supplier_categories.supplier_id = suppliers.supplier_id
                    ) AS category_names,
                    (
                        SELECT supplier_documents.file_url
                        FROM supplier_documents
                        WHERE supplier_documents.supplier_id = suppliers.supplier_id
                          AND supplier_documents.type = \'business_license\'
                        ORDER BY supplier_documents.id DESC
                        LIMIT 1
                    ) AS business_license_url,
                    (
                        SELECT services.name
                        FROM services
                        WHERE services.supplier_id = suppliers.supplier_id
                        ORDER BY services.id ASC
                        LIMIT 1
                    ) AS service_name
             FROM suppliers
             LEFT JOIN users ON users.user_id = suppliers.user_id
             WHERE suppliers.user_id = :user_id
             LIMIT 1'
        );
        $this->db->dbbind(':user_id', (int)$userId);

        return $this->db->getsingledata();
    }

    public function getCategories()
    {
        $this->db->dbquery('SELECT id, name FROM categories ORDER BY name ASC');

        return $this->db->getmultidata();
    }

    public function getApplications($status = 'pending')
    {
        $query = 'SELECT suppliers.supplier_id,
                         suppliers.shop_name,
                         suppliers.description,
                         suppliers.status,
                         suppliers.verify_url,
                         suppliers.payment_status,
                         suppliers.created_at,
                         users.name AS owner_name,
                         users.email AS owner_email,
                         (
                            SELECT GROUP_CONCAT(categories.name ORDER BY categories.name SEPARATOR \', \')
                            FROM supplier_categories
                            LEFT JOIN categories ON categories.id = supplier_categories.category_id
                            WHERE supplier_categories.supplier_id = suppliers.supplier_id
                         ) AS category_names,
                         (
                            SELECT services.name
                            FROM services
                            WHERE services.supplier_id = suppliers.supplier_id
                            ORDER BY services.id ASC
                            LIMIT 1
                         ) AS service_name,
                         (
                            SELECT supplier_documents.file_url
                            FROM supplier_documents
                            WHERE supplier_documents.supplier_id = suppliers.supplier_id
                              AND supplier_documents.type = \'business_license\'
                            ORDER BY supplier_documents.id DESC
                            LIMIT 1
                         ) AS business_license_url
                  FROM suppliers
                  LEFT JOIN users ON users.user_id = suppliers.user_id';

        if ($status !== 'all') {
            $query .= ' WHERE suppliers.status = :status';
        }

        $query .= ' ORDER BY suppliers.created_at DESC, suppliers.supplier_id DESC';

        $this->db->dbquery($query);

        if ($status !== 'all') {
            $this->db->dbbind(':status', $status);
        }

        return $this->db->getmultidata();
    }

    public function getApplicationById($supplierId)
    {
        $this->db->dbquery(
            'SELECT suppliers.supplier_id,
                    suppliers.user_id,
                    suppliers.shop_name,
                    suppliers.description,
                    suppliers.status,
                    suppliers.verify_url,
                    suppliers.agreement_accepted,
                    suppliers.agreement_accepted_at,
                    suppliers.agreement_version,
                    suppliers.payment_status,
                    suppliers.created_at,
                    users.name AS owner_name,
                    users.email AS owner_email,
                    users.phone,
                    users.address,
                    (
                        SELECT GROUP_CONCAT(categories.name ORDER BY categories.name SEPARATOR \', \')
                        FROM supplier_categories
                        LEFT JOIN categories ON categories.id = supplier_categories.category_id
                        WHERE supplier_categories.supplier_id = suppliers.supplier_id
                    ) AS category_names,
                    (
                        SELECT supplier_documents.file_url
                        FROM supplier_documents
                        WHERE supplier_documents.supplier_id = suppliers.supplier_id
                          AND supplier_documents.type = \'cover_photo\'
                        ORDER BY supplier_documents.id DESC
                        LIMIT 1
                    ) AS cover_url,
                    (
                        SELECT supplier_documents.file_url
                        FROM supplier_documents
                        WHERE supplier_documents.supplier_id = suppliers.supplier_id
                          AND supplier_documents.type = \'business_license\'
                        ORDER BY supplier_documents.id DESC
                        LIMIT 1
                    ) AS business_license_url
             FROM suppliers
             LEFT JOIN users ON users.user_id = suppliers.user_id
             WHERE suppliers.supplier_id = :supplier_id
             LIMIT 1'
        );
        $this->db->dbbind(':supplier_id', (int)$supplierId);

        return $this->db->getsingledata();
    }

    public function updateStatus($supplierId, $status, $adminId = null)
    {
        $setByColumn = $status === 'approved' ? 'approved_by' : 'verified_by';

        $this->db->dbquery(
            "UPDATE suppliers
             SET status = :status,
                 {$setByColumn} = :admin_id
             WHERE supplier_id = :supplier_id"
        );
        $this->db->dbbind(':status', $status);
        $this->db->dbbind(':admin_id', $adminId ? (int)$adminId : null);
        $this->db->dbbind(':supplier_id', (int)$supplierId);

        return $this->db->dbexecute();
    }

    public function updatePaymentReview($supplierId, $paymentStatus, $supplierStatus = null, $isAvailable = null, $adminId = null)
    {
        $query = 'UPDATE suppliers
                  SET payment_status = :payment_status';

        if ($supplierStatus !== null) {
            $query .= ', status = :supplier_status,
                         verified_by = :admin_id';
        }

        if ($isAvailable !== null) {
            $query .= ', is_available = :is_available';
        }

        $query .= ' WHERE supplier_id = :supplier_id';

        $this->db->dbquery($query);
        $this->db->dbbind(':payment_status', $paymentStatus);

        if ($supplierStatus !== null) {
            $this->db->dbbind(':supplier_status', $supplierStatus);
            $this->db->dbbind(':admin_id', $adminId ? (int)$adminId : null);
        }

        if ($isAvailable !== null) {
            $this->db->dbbind(':is_available', (int)$isAvailable);
        }

        $this->db->dbbind(':supplier_id', (int)$supplierId);

        return $this->db->dbexecute();
    }

    public function getDashboardData($supplierId)
    {
        $supplierId = (int)$supplierId;

        return [
            'stats' => $this->getDashboardStats($supplierId),
            'services' => $this->getDashboardServices($supplierId),
            'upcomingBookings' => $this->getUpcomingSupplierBookings($supplierId),
            'recentReviews' => $this->getRecentSupplierReviews($supplierId),
            'wallet' => $this->getSupplierWallet($supplierId),
        ];
    }

    private function getDashboardStats($supplierId)
    {
        $this->db->dbquery(
            'SELECT
                (SELECT COUNT(*) FROM services WHERE supplier_id = :services_supplier_id) AS total_services,
                (SELECT COUNT(*) FROM services WHERE supplier_id = :active_services_supplier_id AND is_active = 1) AS active_services,
                (SELECT COUNT(*) FROM booking_suppliers WHERE supplier_id = :bookings_supplier_id) AS total_bookings,
                (SELECT COUNT(*) FROM booking_suppliers WHERE supplier_id = :pending_bookings_supplier_id AND status = \'pending\') AS pending_bookings,
                (SELECT COUNT(*) FROM booking_suppliers WHERE supplier_id = :active_bookings_supplier_id AND status IN (\'confirmed\', \'in_progress\')) AS active_bookings,
                (SELECT COUNT(*) FROM booking_suppliers WHERE supplier_id = :completed_bookings_supplier_id AND status = \'completed\') AS completed_bookings,
                (SELECT COALESCE(SUM(booking_items.price), 0)
                 FROM booking_suppliers
                 LEFT JOIN booking_items ON booking_items.booking_id = booking_suppliers.booking_id
                 WHERE booking_suppliers.supplier_id = :revenue_supplier_id
                   AND booking_suppliers.status IN (\'confirmed\', \'in_progress\', \'completed\')) AS total_revenue,
                (SELECT COALESCE(AVG(rating), 0) FROM reviews WHERE supplier_id = :rating_supplier_id) AS average_rating,
                (SELECT COUNT(*) FROM reviews WHERE supplier_id = :review_supplier_id) AS review_count'
        );
        foreach ([
            ':services_supplier_id',
            ':active_services_supplier_id',
            ':bookings_supplier_id',
            ':pending_bookings_supplier_id',
            ':active_bookings_supplier_id',
            ':completed_bookings_supplier_id',
            ':revenue_supplier_id',
            ':rating_supplier_id',
            ':review_supplier_id',
        ] as $param) {
            $this->db->dbbind($param, $supplierId);
        }

        $stats = $this->db->getsingledata() ?: [];

        return [
            'total_services' => (int)($stats['total_services'] ?? 0),
            'active_services' => (int)($stats['active_services'] ?? 0),
            'total_bookings' => (int)($stats['total_bookings'] ?? 0),
            'pending_bookings' => (int)($stats['pending_bookings'] ?? 0),
            'active_bookings' => (int)($stats['active_bookings'] ?? 0),
            'completed_bookings' => (int)($stats['completed_bookings'] ?? 0),
            'total_revenue' => (float)($stats['total_revenue'] ?? 0),
            'average_rating' => (float)($stats['average_rating'] ?? 0),
            'review_count' => (int)($stats['review_count'] ?? 0),
        ];
    }

    private function getDashboardServices($supplierId)
    {
        $this->db->dbquery(
            'SELECT id, name, price, thumbnail_url, is_active, booking_type, pricing_unit, created_at
             FROM services
             WHERE supplier_id = :supplier_id
             ORDER BY created_at DESC, id DESC
             LIMIT 5'
        );
        $this->db->dbbind(':supplier_id', (int)$supplierId);

        return $this->db->getmultidata();
    }

    private function getUpcomingSupplierBookings($supplierId)
    {
        $this->db->dbquery(
            'SELECT booking_suppliers.booking_id,
                    booking_suppliers.status AS supplier_status,
                    booking_suppliers.payout_status,
                    booking_items.booking_date,
                    booking_items.price,
                    bookings.payment_status,
                    users.name AS customer_name
             FROM booking_suppliers
             LEFT JOIN bookings ON bookings.id = booking_suppliers.booking_id
             LEFT JOIN users ON users.user_id = bookings.user_id
             LEFT JOIN booking_items ON booking_items.booking_id = booking_suppliers.booking_id
             WHERE booking_suppliers.supplier_id = :supplier_id
             ORDER BY booking_items.booking_date ASC, booking_suppliers.created_at DESC
             LIMIT 6'
        );
        $this->db->dbbind(':supplier_id', (int)$supplierId);

        return $this->db->getmultidata();
    }

    private function getRecentSupplierReviews($supplierId)
    {
        $this->db->dbquery(
            'SELECT rating, comment, created_at
             FROM reviews
             WHERE supplier_id = :supplier_id
             ORDER BY created_at DESC, id DESC
             LIMIT 4'
        );
        $this->db->dbbind(':supplier_id', (int)$supplierId);

        return $this->db->getmultidata();
    }

    private function getSupplierWallet($supplierId)
    {
        $this->db->dbquery(
            'SELECT id, balance
             FROM wallets
             WHERE supplier_id = :supplier_id
             LIMIT 1'
        );
        $this->db->dbbind(':supplier_id', (int)$supplierId);

        return $this->db->getsingledata() ?: ['balance' => 0];
    }

    private function getSupplierByUserId($userId)
    {
        $this->db->dbquery('SELECT supplier_id FROM suppliers WHERE user_id = :user_id LIMIT 1');
        $this->db->dbbind(':user_id', $userId);

        return $this->db->getsingledata();
    }

    public function updateServiceThumbnail($serviceId, $thumbnailUrl)
    {
        $this->db->dbquery('UPDATE services SET thumbnail_url = :thumbnail_url WHERE id = :id');
        $this->db->dbbind(':id', (int)$serviceId);
        $this->db->dbbind(':thumbnail_url', $thumbnailUrl);

        return $this->db->dbexecute();
    }

    public function addServiceMedia($serviceId, $fileUrl, $type = 'image')
    {
        $this->db->dbquery(
            'INSERT INTO service_media(service_id, file_url, type)
             VALUES(:service_id, :file_url, :type)'
        );
        $this->db->dbbind(':service_id', (int)$serviceId);
        $this->db->dbbind(':file_url', $fileUrl);
        $this->db->dbbind(':type', $type);

        return $this->db->dbexecute();
    }

    private function categoryExists($categoryId)
    {
        if (empty($categoryId)) {
            return false;
        }

        $this->db->dbquery('SELECT id FROM categories WHERE id = :id LIMIT 1');
        $this->db->dbbind(':id', (int)$categoryId);
        $category = $this->db->getsingledata();

        return !empty($category);
    }

    public function isValidCategory($categoryId)
    {
        return $this->categoryExists($categoryId);
    }

    private function normalizeCategoryIds($categoryIds)
    {
        if (!is_array($categoryIds)) {
            $categoryIds = [$categoryIds];
        }

        return array_values(array_unique(array_filter(array_map('intval', $categoryIds), function ($categoryId) {
            return $categoryId > 0;
        })));
    }

    public function areValidCategories($categoryIds)
    {
        $categoryIds = $this->normalizeCategoryIds($categoryIds);

        if (empty($categoryIds)) {
            return false;
        }

        $placeholders = [];
        foreach ($categoryIds as $index => $categoryId) {
            $placeholders[] = ':category_' . $index;
        }

        $this->db->dbquery('SELECT COUNT(*) AS total FROM categories WHERE id IN (' . implode(',', $placeholders) . ')');
        foreach ($categoryIds as $index => $categoryId) {
            $this->db->dbbind(':category_' . $index, $categoryId);
        }
        $result = $this->db->getsingledata();

        return (int)($result['total'] ?? 0) === count($categoryIds);
    }

    public function saveSupplierCategories($supplierId, $categoryIds, $source = 'manual')
    {
        $categoryIds = $this->normalizeCategoryIds($categoryIds);

        if (!$supplierId || empty($categoryIds) || !$this->areValidCategories($categoryIds)) {
            return false;
        }

        $this->db->dbquery('DELETE FROM supplier_categories WHERE supplier_id = :supplier_id');
        $this->db->dbbind(':supplier_id', (int)$supplierId);

        if (!$this->db->dbexecute()) {
            return false;
        }

        foreach ($categoryIds as $categoryId) {
            $this->db->dbquery(
                'INSERT INTO supplier_categories(supplier_id, category_id, source)
                 VALUES(:supplier_id, :category_id, :source)'
            );
            $this->db->dbbind(':supplier_id', (int)$supplierId);
            $this->db->dbbind(':category_id', (int)$categoryId);
            $this->db->dbbind(':source', $source);

            if (!$this->db->dbexecute()) {
                return false;
            }
        }

        return true;
    }

    public function saveSupplierDocument($supplierId, $fileUrl, $type)
    {
        $this->db->dbquery(
            'SELECT id
             FROM supplier_documents
             WHERE supplier_id = :supplier_id
               AND type = :type
             ORDER BY id DESC
             LIMIT 1'
        );
        $this->db->dbbind(':supplier_id', (int)$supplierId);
        $this->db->dbbind(':type', $type);
        $document = $this->db->getsingledata();

        if ($document) {
            $this->db->dbquery(
                'UPDATE supplier_documents
                 SET file_url = :file_url
                 WHERE id = :id'
            );
            $this->db->dbbind(':id', (int)$document['id']);
            $this->db->dbbind(':file_url', $fileUrl);

            return $this->db->dbexecute();
        }

        $this->db->dbquery(
            'INSERT INTO supplier_documents(supplier_id, file_url, type)
             VALUES(:supplier_id, :file_url, :type)'
        );
        $this->db->dbbind(':supplier_id', (int)$supplierId);
        $this->db->dbbind(':file_url', $fileUrl);
        $this->db->dbbind(':type', $type);

        return $this->db->dbexecute();
    }

    public function save($data)
    {
        $userId = $this->getUserId($data);

        if (!$userId || !$this->areValidCategories($data['category_ids'] ?? [])) {
            return false;
        }

        $this->db->dbquery('UPDATE users SET phone = :phone, address = :address WHERE user_id = :user_id');
        $this->db->dbbind(':phone', $data['phone']);
        $this->db->dbbind(':address', $data['business_address']);
        $this->db->dbbind(':user_id', $userId);
        $this->db->dbexecute();

        $supplier = $this->getSupplierByUserId($userId);

        if ($supplier) {
            $supplierId = (int)$supplier['supplier_id'];
	            $this->db->dbquery(
                'UPDATE suppliers
                 SET shop_name = :shop_name,
                     description = :description,
                     verify_url = :verify_url,
                     status = :status,
	                     agreement_accepted = :agreement_accepted,
                     agreement_accepted_at = :agreement_accepted_at,
                     agreement_version = :agreement_version,
                     payment_status = :payment_status,
                     is_available = :is_available
                 WHERE supplier_id = :supplier_id'
            );
            $this->db->dbbind(':supplier_id', $supplierId);
        } else {
            $this->db->dbquery(
                'INSERT INTO suppliers(
                            user_id,
                            shop_name,
                            description,
                            verify_url,
                            status,
	                    agreement_accepted,
                    agreement_accepted_at,
                    agreement_version,
                    payment_status,
                    is_available
                 )
                 VALUES(
                            :user_id,
                            :shop_name,
                            :description,
                            :verify_url,
                            :status,
	                    :agreement_accepted,
                    :agreement_accepted_at,
                    :agreement_version,
                    :payment_status,
                    :is_available
                 )'
            );
            $this->db->dbbind(':user_id', $userId);
        }

        $this->db->dbbind(':shop_name', $data['business_name']);
        $this->db->dbbind(':description', $data['business_description']);
        $this->db->dbbind(':verify_url', $data['business_url']);
        $this->db->dbbind(':status', 'pending');
        $this->db->dbbind(':agreement_accepted', !empty($data['agreement_accepted']) ? 1 : 0);
        $this->db->dbbind(':agreement_accepted_at', $data['agreement_accepted_at']);
        $this->db->dbbind(':agreement_version', $data['agreement_version']);
        $this->db->dbbind(':payment_status', 'unpaid');
        $this->db->dbbind(':is_available', 0);

        if (!$this->db->dbexecute()) {
            return false;
        }

        if (empty($supplierId)) {
            $supplierId = (int)$this->db->lastinsertid();
        }

        if (!$this->saveSupplierCategories($supplierId, $data['category_ids'] ?? [], $data['category_source'] ?? 'manual')) {
            return false;
        }

        return [
            'supplier_id' => $supplierId,
        ];
    }
}
