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
            $this->db->dbquery('SELECT user_id FROM users WHERE user_id = :user_id LIMIT 1');
            $this->db->dbbind(':user_id', (int)$data['user_id']);
            $user = $this->db->getsingledata();

            if ($user) {
                return (int)$user['user_id'];
            }
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
                    suppliers.auto_accept_bookings,
                    suppliers.min_advance_days,
                    suppliers.cancellation_policy,
                    suppliers.bank_account,
                    suppliers.bank_code,
                    suppliers.notification_prefs,
                    suppliers.warning_level,
                    suppliers.missed_response_count,
                    suppliers.created_at,
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
                        SELECT GROUP_CONCAT(supplier_categories.category_id ORDER BY supplier_categories.category_id SEPARATOR \',\')
                        FROM supplier_categories
                        WHERE supplier_categories.supplier_id = suppliers.supplier_id
                    ) AS category_id_csv,
                    (
                        SELECT supplier_documents.file_url
                        FROM supplier_documents
                        WHERE supplier_documents.supplier_id = suppliers.supplier_id
                          AND supplier_documents.type = \'cover_photo\'
                        ORDER BY supplier_documents.id DESC
                        LIMIT 1
                    ) AS cover_photo_url,
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

    public function getById(int $supplierId): array|false
    {
        $this->db->dbquery(
            'SELECT suppliers.supplier_id,
                    suppliers.user_id,
                    suppliers.shop_name,
                    suppliers.description,
                    suppliers.status,
                    suppliers.payment_status,
                    suppliers.is_available,
                    suppliers.bank_code,
                    suppliers.bank_account,
                    users.name AS owner_name,
                    users.email AS owner_email,
                    users.phone AS owner_phone
               FROM suppliers
               LEFT JOIN users ON users.user_id = suppliers.user_id
              WHERE suppliers.supplier_id = :supplier_id
              LIMIT 1'
        );
        $this->db->dbbind(':supplier_id', $supplierId, PDO::PARAM_INT);

        return $this->db->getsingledata();
    }

    public function getCategories()
    {
        $this->db->dbquery('SELECT id, name FROM categories ORDER BY name ASC');

        return $this->db->getmultidata();
    }

    public function getApplications(
        $status = 'pending',
        int $limit = 15,
        int $offset = 0,
        string $search = '',
        int $categoryId = 0,
        string $paymentStatus = 'all'
    )
    {
        $query = 'SELECT suppliers.supplier_id,
                         suppliers.shop_name,
                         suppliers.description,
                         suppliers.status,
                         suppliers.verify_url,
                         suppliers.payment_status,
                         suppliers.warning_level,
                         suppliers.admin_note,
                         suppliers.created_at,
                         users.name AS owner_name,
                         users.email AS owner_email,
                         users.last_login,
                         users.is_online,
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
                         ) AS business_license_url,
                         (
                            SELECT COUNT(*)
                            FROM booking_suppliers
                            WHERE booking_suppliers.supplier_id = suppliers.supplier_id
                              AND booking_suppliers.status IN (\'confirmed\', \'accepted\', \'completed\')
                         ) AS booking_count,
                         (
                            SELECT COALESCE(AVG(reviews.rating), 0)
                            FROM reviews
                            WHERE reviews.supplier_id = suppliers.supplier_id
                         ) AS avg_rating,
                         (
                            SELECT COUNT(*)
                            FROM reviews
                            WHERE reviews.supplier_id = suppliers.supplier_id
                         ) AS review_count
                  FROM suppliers
                  LEFT JOIN users ON users.user_id = suppliers.user_id';

        $conditions = ['suppliers.deleted_at IS NULL'];
        if ($status !== 'all') {
            $conditions[] = 'suppliers.status = :status';
        }
        if ($search !== '') {
            $conditions[] = '(suppliers.shop_name LIKE :search_shop
                              OR users.name LIKE :search_owner
                              OR users.email LIKE :search_email
                              OR EXISTS (
                                  SELECT 1
                                  FROM supplier_categories search_sc
                                  INNER JOIN categories search_c ON search_c.id = search_sc.category_id
                                  WHERE search_sc.supplier_id = suppliers.supplier_id
                                    AND search_c.name LIKE :search_category
                              )
                              OR EXISTS (
                                  SELECT 1
                                  FROM services search_services
                                  WHERE search_services.supplier_id = suppliers.supplier_id
                                    AND search_services.name LIKE :search_service
                              ))';
        }
        if ($categoryId > 0) {
            $conditions[] = 'EXISTS (
                                SELECT 1
                                FROM supplier_categories filter_sc
                                WHERE filter_sc.supplier_id = suppliers.supplier_id
                                  AND filter_sc.category_id = :category_id
                              )';
        }
        if (in_array($paymentStatus, ['paid', 'unpaid'], true)) {
            $conditions[] = 'suppliers.payment_status = :payment_status';
        }
        if ($conditions) {
            $query .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $query .= ' ORDER BY suppliers.created_at DESC, suppliers.supplier_id DESC';
        $query .= ' LIMIT :limit OFFSET :offset';

        $this->db->dbquery($query);

        if ($status !== 'all') {
            $this->db->dbbind(':status', $status);
        }
        if ($search !== '') {
            $searchValue = '%' . $search . '%';
            $this->db->dbbind(':search_shop', $searchValue);
            $this->db->dbbind(':search_owner', $searchValue);
            $this->db->dbbind(':search_email', $searchValue);
            $this->db->dbbind(':search_category', $searchValue);
            $this->db->dbbind(':search_service', $searchValue);
        }
        if ($categoryId > 0) {
            $this->db->dbbind(':category_id', $categoryId, PDO::PARAM_INT);
        }
        if (in_array($paymentStatus, ['paid', 'unpaid'], true)) {
            $this->db->dbbind(':payment_status', $paymentStatus);
        }
        $this->db->dbbind(':limit', $limit, PDO::PARAM_INT);
        $this->db->dbbind(':offset', $offset, PDO::PARAM_INT);

        return $this->db->getmultidata();
    }

    public function getApplicationsCount(
        string $status = 'pending',
        string $search = '',
        int $categoryId = 0,
        string $paymentStatus = 'all'
    ): int
    {
        $query = 'SELECT COUNT(*) AS total
                  FROM suppliers
                  LEFT JOIN users ON users.user_id = suppliers.user_id';
        $conditions = ['suppliers.deleted_at IS NULL'];
        if ($status !== 'all') {
            $conditions[] = 'suppliers.status = :status';
        }
        if ($search !== '') {
            $conditions[] = '(suppliers.shop_name LIKE :search_shop
                              OR users.name LIKE :search_owner
                              OR users.email LIKE :search_email
                              OR EXISTS (
                                  SELECT 1
                                  FROM supplier_categories search_sc
                                  INNER JOIN categories search_c ON search_c.id = search_sc.category_id
                                  WHERE search_sc.supplier_id = suppliers.supplier_id
                                    AND search_c.name LIKE :search_category
                              )
                              OR EXISTS (
                                  SELECT 1
                                  FROM services search_services
                                  WHERE search_services.supplier_id = suppliers.supplier_id
                                    AND search_services.name LIKE :search_service
                              ))';
        }
        if ($categoryId > 0) {
            $conditions[] = 'EXISTS (
                                SELECT 1
                                FROM supplier_categories filter_sc
                                WHERE filter_sc.supplier_id = suppliers.supplier_id
                                  AND filter_sc.category_id = :category_id
                              )';
        }
        if (in_array($paymentStatus, ['paid', 'unpaid'], true)) {
            $conditions[] = 'suppliers.payment_status = :payment_status';
        }
        if ($conditions) {
            $query .= ' WHERE ' . implode(' AND ', $conditions);
        }
        $this->db->dbquery($query);
        if ($status !== 'all') {
            $this->db->dbbind(':status', $status);
        }
        if ($search !== '') {
            $searchValue = '%' . $search . '%';
            $this->db->dbbind(':search_shop', $searchValue);
            $this->db->dbbind(':search_owner', $searchValue);
            $this->db->dbbind(':search_email', $searchValue);
            $this->db->dbbind(':search_category', $searchValue);
            $this->db->dbbind(':search_service', $searchValue);
        }
        if ($categoryId > 0) {
            $this->db->dbbind(':category_id', $categoryId, PDO::PARAM_INT);
        }
        if (in_array($paymentStatus, ['paid', 'unpaid'], true)) {
            $this->db->dbbind(':payment_status', $paymentStatus);
        }
        return (int)($this->db->getsingledata()['total'] ?? 0);
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
                    suppliers.warning_level,
                    suppliers.admin_note,
                    suppliers.created_at,
                    users.name AS owner_name,
                    users.email AS owner_email,
                    users.phone,
                    users.address,
                    users.last_login,
                    users.is_online,
                    users.deleted_at AS user_deleted_at,
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
               AND suppliers.deleted_at IS NULL
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

    public function banSupplier(int $supplierId, string $reason, int $adminId): bool
    {
        $this->db->dbquery(
            "UPDATE suppliers
             SET status = 'banned',
                 admin_note = :note,
                 approved_by = :admin_id
             WHERE supplier_id = :id"
        );
        $this->db->dbbind(':note', 'BANNED: ' . $reason);
        $this->db->dbbind(':admin_id', $adminId);
        $this->db->dbbind(':id', $supplierId);
        return $this->db->dbexecute();
    }

    public function unbanSupplier(int $supplierId, int $adminId): bool
    {
        $this->db->dbquery(
            "UPDATE suppliers
             SET status = 'approved',
                 admin_note = CONCAT(COALESCE(admin_note, ''), '\nUnbanned by admin #', :admin_id, ' at ', NOW()),
                 approved_by = :admin_id2
             WHERE supplier_id = :id AND status = 'banned'"
        );
        $this->db->dbbind(':admin_id', $adminId);
        $this->db->dbbind(':admin_id2', $adminId);
        $this->db->dbbind(':id', $supplierId);
        return $this->db->dbexecute();
    }

    public function warnSupplier(int $supplierId, int $level, string $note, int $adminId): bool
    {
        $this->db->dbquery(
            "UPDATE suppliers
             SET warning_level = :level,
                 admin_note = CONCAT(COALESCE(admin_note, ''), '\nWARN L', :level2, ' (admin #', :admin_id, '): ', :note, ' — ', NOW())
             WHERE supplier_id = :id"
        );
        $this->db->dbbind(':level', $level);
        $this->db->dbbind(':level2', $level);
        $this->db->dbbind(':note', $note);
        $this->db->dbbind(':admin_id', $adminId);
        $this->db->dbbind(':id', $supplierId);
        return $this->db->dbexecute();
    }

    public function getTopSuppliers(int $limit = 5): array
    {
        $this->db->dbquery(
            "SELECT s.supplier_id, s.shop_name, s.status, s.warning_level,
                    COUNT(DISTINCT bs.id) AS completed_bookings,
                    COALESCE(SUM(CASE WHEN bi.status IN ('confirmed','completed','accepted')
                        THEN COALESCE(bi.price, 0) ELSE 0 END), 0) AS revenue_earned,
                    COALESCE(AVG(r.rating), 0) AS avg_rating,
                    COUNT(DISTINCT r.id) AS review_count
             FROM suppliers s
             LEFT JOIN booking_suppliers bs ON bs.supplier_id = s.supplier_id
             LEFT JOIN booking_items bi ON bi.booking_id = bs.booking_id AND bi.status IN ('confirmed','completed','accepted')
             LEFT JOIN reviews r ON r.supplier_id = s.supplier_id
             WHERE s.status IN ('approved', 'verified')
               AND s.deleted_at IS NULL
             GROUP BY s.supplier_id
             ORDER BY revenue_earned DESC, completed_bookings DESC
             LIMIT :limit"
        );
        $this->db->dbbind(':limit', $limit, PDO::PARAM_INT);
        return $this->db->getmultidata();
    }

    public function getSupplierStats(): array
    {
        $this->db->dbquery(
            "SELECT
                SUM(s.status = 'pending') AS pending,
                SUM(s.status = 'approved') AS approved,
                SUM(s.status = 'verified') AS verified,
                SUM(s.status = 'rejected') AS rejected,
                SUM(s.status = 'banned') AS banned,
                COUNT(*) AS total
             FROM suppliers s
             WHERE s.deleted_at IS NULL"
        );
        return $this->db->getsingledata() ?: ['pending' => 0, 'approved' => 0, 'verified' => 0, 'rejected' => 0, 'banned' => 0, 'total' => 0];
    }

    public function getSupplierPerformance(int $supplierId): array
    {
        $this->db->dbquery(
            "SELECT
                COUNT(DISTINCT bs.id) AS total_bookings,
                SUM(CASE WHEN bs.status IN ('confirmed','accepted') THEN 1 ELSE 0 END) AS active_bookings,
                SUM(CASE WHEN bs.status = 'completed' THEN 1 ELSE 0 END) AS completed_bookings,
                SUM(CASE WHEN bs.status = 'cancelled' OR bs.status = 'rejected' THEN 1 ELSE 0 END) AS cancelled_bookings,
                COALESCE(SUM(CASE WHEN bi.status IN ('confirmed','completed','accepted')
                    THEN COALESCE(bi.price, 0) ELSE 0 END), 0) AS revenue_earned,
                COALESCE(AVG(r.rating), 0) AS avg_rating,
                COUNT(DISTINCT r.id) AS review_count
             FROM booking_suppliers bs
             LEFT JOIN booking_items bi ON bi.booking_id = bs.booking_id
             LEFT JOIN reviews r ON r.supplier_id = bs.supplier_id
             WHERE bs.supplier_id = :sid"
        );
        $this->db->dbbind(':sid', $supplierId, PDO::PARAM_INT);
        return $this->db->getsingledata() ?: ['total_bookings' => 0, 'active_bookings' => 0, 'completed_bookings' => 0, 'cancelled_bookings' => 0, 'revenue_earned' => 0, 'avg_rating' => 0, 'review_count' => 0];
    }

    public function getSupplierServices(int $supplierId): array
    {
        $this->db->dbquery(
            'SELECT id, name, is_active, price_min, price_max, category_id,
                    (SELECT name FROM categories WHERE categories.id = services.category_id) AS category_name
             FROM services
             WHERE supplier_id = :sid
             ORDER BY is_active DESC, id DESC'
        );
        $this->db->dbbind(':sid', $supplierId, PDO::PARAM_INT);
        return $this->db->getmultidata() ?: [];
    }

    public function getSupplierRecentBookings(int $supplierId, int $limit = 5): array
    {
        $this->db->dbquery(
            'SELECT bs.id, bs.status, bs.created_at, bs.confirmed_at, bs.completed_at,
                    bs.item_price,
                    b.id AS booking_id,
                    ed.event_date,
                    s.name AS service_name
             FROM booking_suppliers bs
             LEFT JOIN bookings b ON b.id = bs.booking_id
             LEFT JOIN services s ON s.id = bs.service_id
             LEFT JOIN event_details ed ON ed.booking_id = bs.booking_id AND ed.booking_item_id = bs.id
             WHERE bs.supplier_id = :sid
             ORDER BY bs.created_at DESC
             LIMIT :limit'
        );
        $this->db->dbbind(':sid', $supplierId, PDO::PARAM_INT);
        $this->db->dbbind(':limit', $limit, PDO::PARAM_INT);
        return $this->db->getmultidata() ?: [];
    }

    public function getSupplierReviews(int $supplierId, int $limit = 5): array
    {
        $this->db->dbquery(
            'SELECT r.id, r.rating, r.comment, r.created_at,
                    u.name AS customer_name,
                    s.name AS service_name
             FROM reviews r
             LEFT JOIN users u ON u.user_id = r.customer_id
             LEFT JOIN services s ON s.id = r.service_id
             WHERE r.supplier_id = :sid AND r.deleted_at IS NULL
             ORDER BY r.created_at DESC
             LIMIT :limit'
        );
        $this->db->dbbind(':sid', $supplierId, PDO::PARAM_INT);
        $this->db->dbbind(':limit', $limit, PDO::PARAM_INT);
        return $this->db->getmultidata() ?: [];
    }

    public function getSupplierWarnings(int $supplierId): array
    {
        $this->db->dbquery(
            'SELECT sw.id, sw.reason, sw.severity, sw.source, sw.resolved,
                    sw.resolved_at, sw.resolution_note, sw.created_at,
                    u.name AS issued_by_name
             FROM supplier_warnings sw
             LEFT JOIN users u ON u.user_id = sw.issued_by
             WHERE sw.supplier_id = :sid
             ORDER BY sw.created_at DESC'
        );
        $this->db->dbbind(':sid', $supplierId, PDO::PARAM_INT);
        return $this->db->getmultidata() ?: [];
    }

    public function updateAdminNote(int $supplierId, string $note): bool
    {
        $this->db->dbquery('UPDATE suppliers SET admin_note = :note WHERE supplier_id = :sid');
        $this->db->dbbind(':note', $note);
        $this->db->dbbind(':sid', $supplierId, PDO::PARAM_INT);
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
        // Default to 'year' so KPI cards and charts are consistent on initial load
        $defaultRange = 'year';

        return [
            'stats' => $this->getDashboardStats($supplierId, $defaultRange),
            'services' => $this->getDashboardServices($supplierId),
            'upcomingBookings' => $this->getUpcomingSupplierBookings($supplierId),
            'recentReviews' => $this->getRecentSupplierReviews($supplierId),

            'payments' => $this->getDashboardPayments($supplierId),
            'chartData' => $this->getDashboardChartData($supplierId, $defaultRange),
        ];
    }

    /**
     * AJAX endpoint: returns only stats + chartData for a given date range.
     */
    public function getFilteredDashboardData(int $supplierId, string $range): array
    {
        $validRanges = ['year', '6months', 'month', 'all'];
        if (!in_array($range, $validRanges, true)) {
            $range = 'year';
        }

        return [
            'stats' => $this->getDashboardStats($supplierId, $range),
            'chartData' => $this->getDashboardChartData($supplierId, $range),
        ];
    }

    private function getDashboardPayments(int $supplierId): array
    {
        $this->db->dbquery(
            "SELECT b.id AS booking_id,
                    u.name AS customer_name,
                    ed.event_date,
                    p.method,
                    p.escrow_status,
                    b.payment_status,
                    b.total_amount,
                    b.paid_amount
             FROM booking_suppliers bs
             INNER JOIN bookings b ON b.id = bs.booking_id
             INNER JOIN users u ON u.user_id = b.user_id
             INNER JOIN event_details ed ON ed.booking_id = b.id
             LEFT JOIN payments p ON p.booking_id = b.id AND p.type = 'deposit'
             WHERE bs.supplier_id = :sid
             ORDER BY b.created_at DESC
             LIMIT 10"
        );
        $this->db->dbbind(':sid', $supplierId, PDO::PARAM_INT);
        $rows = $this->db->getmultidata();

        // Add booking_ref
        foreach ($rows as &$row) {
            $row['booking_ref'] = $this->generateBookingRefForPayment((int)($row['booking_id'] ?? 0));
        }
        return $rows;
    }

    private function generateBookingRefForPayment(int $bookingId): string
    {
        return 'BK-' . str_pad((string)$bookingId, 3, '0', STR_PAD_LEFT);
    }

    private function getDashboardChartData(int $supplierId, string $range = 'year'): array
    {
        $monthNames = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

        if ($range === 'month') {
            // Daily buckets for the current month
            $daysInMonth = (int)date('t');
            $labels = [];
            for ($d = 1; $d <= $daysInMonth; $d++) { $labels[] = (string)$d; }
            $revenue = array_fill(0, $daysInMonth, 0);
            $bookings = array_fill(0, $daysInMonth, 0);

            $this->db->dbquery(
                "SELECT DAY(COALESCE(p.verified_at, p.created_at)) AS d,
                        COALESCE(SUM(p.amount), 0) AS revenue
                 FROM payments p
                 WHERE p.supplier_id = :sid
                   AND p.type = 'payout'
                   AND p.status IN ('success', 'processing', 'pending')
                   AND YEAR(COALESCE(p.verified_at, p.created_at)) = YEAR(CURDATE())
                   AND MONTH(COALESCE(p.verified_at, p.created_at)) = MONTH(CURDATE())
                 GROUP BY DAY(COALESCE(p.verified_at, p.created_at))"
            );
            $this->db->dbbind(':sid', $supplierId, PDO::PARAM_INT);
            foreach ($this->db->getmultidata() as $row) {
                $idx = (int)($row['d'] ?? 0) - 1;
                if ($idx >= 0 && $idx < $daysInMonth) {
                    $revenue[$idx] = (float)($row['revenue'] ?? 0);
                }
            }

            $this->db->dbquery(
                "SELECT DAY(ed.event_date) AS d,
                        COUNT(DISTINCT bs.booking_id) AS cnt
                 FROM booking_suppliers bs
                 INNER JOIN event_details ed ON ed.booking_id = bs.booking_id
                 WHERE bs.supplier_id = :sid
                   AND YEAR(ed.event_date) = YEAR(CURDATE())
                   AND MONTH(ed.event_date) = MONTH(CURDATE())
                   AND bs.status IN ('confirmed', 'completed', 'in_progress')
                 GROUP BY DAY(ed.event_date)"
            );
            $this->db->dbbind(':sid', $supplierId, PDO::PARAM_INT);
            foreach ($this->db->getmultidata() as $row) {
                $idx = (int)($row['d'] ?? 0) - 1;
                if ($idx >= 0 && $idx < $daysInMonth) {
                    $bookings[$idx] = (int)($row['cnt'] ?? 0);
                }
            }

            return ['labels' => $labels, 'revenue' => $revenue, 'bookings' => $bookings, 'range' => $range];
        }

        if ($range === '6months') {
            // Last 6 months rolling
            $labels = [];
            for ($i = 5; $i >= 0; $i--) {
                $labels[] = date('M', strtotime("-{$i} months"));
            }
            $revenue = array_fill(0, 6, 0);
            $bookings = array_fill(0, 6, 0);

            $this->db->dbquery(
                "SELECT PERIOD_DIFF(DATE_FORMAT(CURDATE(), '%Y%m'), DATE_FORMAT(COALESCE(p.verified_at, p.created_at), '%Y%m')) AS months_ago,
                        COALESCE(SUM(p.amount), 0) AS revenue
                 FROM payments p
                 WHERE p.supplier_id = :sid
                   AND p.type = 'payout'
                   AND p.status IN ('success', 'processing', 'pending')
                   AND COALESCE(p.verified_at, p.created_at) >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                 GROUP BY PERIOD_DIFF(DATE_FORMAT(CURDATE(), '%Y%m'), DATE_FORMAT(COALESCE(p.verified_at, p.created_at), '%Y%m'))"
            );
            $this->db->dbbind(':sid', $supplierId, PDO::PARAM_INT);
            foreach ($this->db->getmultidata() as $row) {
                $ago = (int)($row['months_ago'] ?? 0);
                $idx = 5 - $ago;
                if ($idx >= 0 && $idx < 6) {
                    $revenue[$idx] = (float)($row['revenue'] ?? 0);
                }
            }

            $this->db->dbquery(
                "SELECT PERIOD_DIFF(DATE_FORMAT(CURDATE(), '%Y%m'), DATE_FORMAT(ed.event_date, '%Y%m')) AS months_ago,
                        COUNT(DISTINCT bs.booking_id) AS cnt
                 FROM booking_suppliers bs
                 INNER JOIN event_details ed ON ed.booking_id = bs.booking_id
                 WHERE bs.supplier_id = :sid
                   AND ed.event_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                   AND bs.status IN ('confirmed', 'completed', 'in_progress')
                 GROUP BY PERIOD_DIFF(DATE_FORMAT(CURDATE(), '%Y%m'), DATE_FORMAT(ed.event_date, '%Y%m'))"
            );
            $this->db->dbbind(':sid', $supplierId, PDO::PARAM_INT);
            foreach ($this->db->getmultidata() as $row) {
                $ago = (int)($row['months_ago'] ?? 0);
                $idx = 5 - $ago;
                if ($idx >= 0 && $idx < 6) {
                    $bookings[$idx] = (int)($row['cnt'] ?? 0);
                }
            }

            return ['labels' => $labels, 'revenue' => $revenue, 'bookings' => $bookings, 'range' => $range];
        }

        if ($range === 'all') {
            // All-time aggregated by calendar month
            $revenue = array_fill(0, 12, 0);
            $bookings = array_fill(0, 12, 0);

            $this->db->dbquery(
                "SELECT MONTH(COALESCE(p.verified_at, p.created_at)) AS m,
                        COALESCE(SUM(p.amount), 0) AS revenue
                 FROM payments p
                 WHERE p.supplier_id = :sid
                   AND p.type = 'payout'
                   AND p.status IN ('success', 'processing', 'pending')
                 GROUP BY MONTH(COALESCE(p.verified_at, p.created_at))"
            );
            $this->db->dbbind(':sid', $supplierId, PDO::PARAM_INT);
            foreach ($this->db->getmultidata() as $row) {
                $idx = (int)($row['m'] ?? 0) - 1;
                if ($idx >= 0 && $idx < 12) {
                    $revenue[$idx] = (float)($row['revenue'] ?? 0);
                }
            }

            $this->db->dbquery(
                "SELECT MONTH(ed.event_date) AS m,
                        COUNT(DISTINCT bs.booking_id) AS cnt
                 FROM booking_suppliers bs
                 INNER JOIN event_details ed ON ed.booking_id = bs.booking_id
                 WHERE bs.supplier_id = :sid
                   AND bs.status IN ('confirmed', 'completed', 'in_progress')
                 GROUP BY MONTH(ed.event_date)"
            );
            $this->db->dbbind(':sid', $supplierId, PDO::PARAM_INT);
            foreach ($this->db->getmultidata() as $row) {
                $idx = (int)($row['m'] ?? 0) - 1;
                if ($idx >= 0 && $idx < 12) {
                    $bookings[$idx] = (int)($row['cnt'] ?? 0);
                }
            }

            return ['labels' => $monthNames, 'revenue' => $revenue, 'bookings' => $bookings, 'range' => $range];
        }

        // Default: 'year' — current calendar year by month
        $revenue = array_fill(0, 12, 0);
        $bookings = array_fill(0, 12, 0);

        $this->db->dbquery(
            "SELECT MONTH(COALESCE(p.verified_at, p.created_at)) AS m,
                    COALESCE(SUM(p.amount), 0) AS revenue
             FROM payments p
             WHERE p.supplier_id = :sid
               AND p.type = 'payout'
               AND p.status IN ('success', 'processing', 'pending')
               AND YEAR(COALESCE(p.verified_at, p.created_at)) = YEAR(CURDATE())
             GROUP BY MONTH(COALESCE(p.verified_at, p.created_at))"
        );
        $this->db->dbbind(':sid', $supplierId, PDO::PARAM_INT);
        foreach ($this->db->getmultidata() as $row) {
            $idx = (int)($row['m'] ?? 0) - 1;
            if ($idx >= 0 && $idx < 12) {
                $revenue[$idx] = (float)($row['revenue'] ?? 0);
            }
        }

        $this->db->dbquery(
            "SELECT MONTH(ed.event_date) AS m,
                    COUNT(DISTINCT bs.booking_id) AS cnt
             FROM booking_suppliers bs
             INNER JOIN event_details ed ON ed.booking_id = bs.booking_id
             WHERE bs.supplier_id = :sid
               AND YEAR(ed.event_date) = YEAR(CURDATE())
               AND bs.status IN ('confirmed', 'completed', 'in_progress')
             GROUP BY MONTH(ed.event_date)"
        );
        $this->db->dbbind(':sid', $supplierId, PDO::PARAM_INT);
        foreach ($this->db->getmultidata() as $row) {
            $idx = (int)($row['m'] ?? 0) - 1;
            if ($idx >= 0 && $idx < 12) {
                $bookings[$idx] = (int)($row['cnt'] ?? 0);
            }
        }

        return ['labels' => $monthNames, 'revenue' => $revenue, 'bookings' => $bookings, 'range' => $range];
    }

    private function getDashboardStats($supplierId, string $range = 'all')
    {
        // Services and ratings are not time-bound
        $this->db->dbquery(
            'SELECT
                (SELECT COUNT(*) FROM services WHERE supplier_id = :services_supplier_id) AS total_services,
                (SELECT COUNT(*) FROM services WHERE supplier_id = :active_services_supplier_id AND is_active = 1) AS active_services,
                (SELECT COALESCE(AVG(rating), 0) FROM reviews WHERE supplier_id = :rating_supplier_id) AS average_rating,
                (SELECT COUNT(*) FROM reviews WHERE supplier_id = :review_supplier_id) AS review_count'
        );
        foreach ([':services_supplier_id', ':active_services_supplier_id', ':rating_supplier_id', ':review_supplier_id'] as $param) {
            $this->db->dbbind($param, $supplierId);
        }
        $portfolio = $this->db->getsingledata() ?: [];

        // Build date filter for bookings (by event date)
        // Use 'ed' as alias — each subquery uses the same alias since they're independent scopes
        $bookingDateFilter = '';
        if ($range === 'month') {
            $bookingDateFilter = ' AND YEAR(ed.event_date) = YEAR(CURDATE()) AND MONTH(ed.event_date) = MONTH(CURDATE())';
        } elseif ($range === '6months') {
            $bookingDateFilter = ' AND ed.event_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)';
        } elseif ($range === 'year') {
            $bookingDateFilter = ' AND YEAR(ed.event_date) = YEAR(CURDATE())';
        }
        // 'all' → no date filter

        // Bookings with date filter
        $this->db->dbquery(
            "SELECT
                (SELECT COUNT(DISTINCT bs.booking_id) FROM booking_suppliers bs INNER JOIN event_details ed ON ed.booking_id = bs.booking_id WHERE bs.supplier_id = :sid1 {$bookingDateFilter}) AS total_bookings,
                (SELECT COUNT(DISTINCT bs.booking_id) FROM booking_suppliers bs INNER JOIN event_details ed ON ed.booking_id = bs.booking_id WHERE bs.supplier_id = :sid2 AND bs.status = 'pending' {$bookingDateFilter}) AS pending_bookings,
                (SELECT COUNT(DISTINCT bs.booking_id) FROM booking_suppliers bs INNER JOIN event_details ed ON ed.booking_id = bs.booking_id WHERE bs.supplier_id = :sid3 AND bs.status IN ('confirmed', 'in_progress') {$bookingDateFilter}) AS active_bookings,
                (SELECT COUNT(DISTINCT bs.booking_id) FROM booking_suppliers bs INNER JOIN event_details ed ON ed.booking_id = bs.booking_id WHERE bs.supplier_id = :sid4 AND bs.status = 'completed' {$bookingDateFilter}) AS completed_bookings,
                (SELECT COUNT(DISTINCT bs.booking_id) FROM booking_suppliers bs INNER JOIN event_details ed ON ed.booking_id = bs.booking_id WHERE bs.supplier_id = :sid5 AND bs.status = 'cancelled' {$bookingDateFilter}) AS cancelled_bookings"
        );
        foreach ([':sid1', ':sid2', ':sid3', ':sid4', ':sid5'] as $param) {
            $this->db->dbbind($param, $supplierId);
        }
        $bookings = $this->db->getsingledata() ?: [];

        // Revenue with date filter
        $revenueDateFilter = '';
        if ($range === 'month') {
            $revenueDateFilter = ' AND YEAR(COALESCE(p.verified_at, p.created_at)) = YEAR(CURDATE()) AND MONTH(COALESCE(p.verified_at, p.created_at)) = MONTH(CURDATE())';
        } elseif ($range === '6months') {
            $revenueDateFilter = ' AND COALESCE(p.verified_at, p.created_at) >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)';
        } elseif ($range === 'year') {
            $revenueDateFilter = ' AND YEAR(COALESCE(p.verified_at, p.created_at)) = YEAR(CURDATE())';
        }

        $this->db->dbquery(
            "SELECT
                COALESCE(SUM(CASE WHEN p.status = 'success' THEN p.amount ELSE 0 END), 0) AS paid_revenue,
                COALESCE(SUM(CASE WHEN p.status IN ('pending', 'processing') THEN p.amount ELSE 0 END), 0) AS pending_revenue,
                COALESCE(SUM(p.amount), 0) AS total_revenue
             FROM payments p
             WHERE p.supplier_id = :sid AND p.type = 'payout' {$revenueDateFilter}"
        );
        $this->db->dbbind(':sid', $supplierId, PDO::PARAM_INT);
        $revenue = $this->db->getsingledata() ?: [];

        // Refund metrics for this supplier's bookings
        $refundDateFilter = '';
        if ($range === 'month') {
            $refundDateFilter = ' AND YEAR(r.completed_at) = YEAR(CURDATE()) AND MONTH(r.completed_at) = MONTH(CURDATE())';
        } elseif ($range === '6months') {
            $refundDateFilter = ' AND r.completed_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)';
        } elseif ($range === 'year') {
            $refundDateFilter = ' AND YEAR(r.completed_at) = YEAR(CURDATE())';
        }

        $this->db->dbquery(
            "SELECT COUNT(*) AS refund_count,
                    COALESCE(SUM(r.amount), 0) AS total_refunded
             FROM refunds r
             INNER JOIN booking_suppliers bs ON bs.booking_id = r.booking_id
             WHERE bs.supplier_id = :sid
               AND r.status = 'completed'
               {$refundDateFilter}"
        );
        $this->db->dbbind(':sid', $supplierId, PDO::PARAM_INT);
        $refunds = $this->db->getsingledata() ?: [];

        return [
            'total_services' => (int)($portfolio['total_services'] ?? 0),
            'active_services' => (int)($portfolio['active_services'] ?? 0),
            'total_bookings' => (int)($bookings['total_bookings'] ?? 0),
            'pending_bookings' => (int)($bookings['pending_bookings'] ?? 0),
            'active_bookings' => (int)($bookings['active_bookings'] ?? 0),
            'completed_bookings' => (int)($bookings['completed_bookings'] ?? 0),
            'cancelled_bookings' => (int)($bookings['cancelled_bookings'] ?? 0),
            'total_revenue' => (float)($revenue['total_revenue'] ?? 0),
            'paid_revenue' => (float)($revenue['paid_revenue'] ?? 0),
            'pending_revenue' => (float)($revenue['pending_revenue'] ?? 0),
            'refund_count' => (int)($refunds['refund_count'] ?? 0),
            'total_refunded' => (float)($refunds['total_refunded'] ?? 0),
            'average_rating' => (float)($portfolio['average_rating'] ?? 0),
            'review_count' => (int)($portfolio['review_count'] ?? 0),
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
            'SELECT bs.booking_id,
                    bs.status AS supplier_status,
                    bs.payout_status,
                    bs.item_price,
                    bi.booking_date,
                    bi.item_name,
                    bi.category_name,
                    b.payment_status,
                    b.total_amount,
                    u.name AS customer_name,
                    u.phone AS customer_phone,
                    ed.event_date,
                    ed.guest_count,
                    ed.location,
                    ed.contact_name,
                    ed.contact_phone
             FROM booking_suppliers bs
             INNER JOIN bookings b ON b.id = bs.booking_id
             LEFT JOIN users u ON u.user_id = b.user_id
             LEFT JOIN booking_items bi ON bi.booking_id = bs.booking_id
                 AND (bi.item_id = bs.service_id OR bs.service_id IS NULL)
             LEFT JOIN event_details ed ON ed.booking_id = bs.booking_id
             WHERE bs.supplier_id = :supplier_id
               AND bs.status IN (\'pending\', \'confirmed\', \'in_progress\')
             ORDER BY COALESCE(ed.event_date, bi.booking_date) ASC
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

    /**
     * Update supplier profile fields (not status/payment).
     * $data may contain: name, email, phone, address (→ users table)
     * and: shop_name, description, business_url (→ suppliers table).
     */
    public function updateProfile(int $userId, array $data): bool
    {
        // Update users table
        $userSets = [];
        $userParams = [];
        if (array_key_exists('name', $data)) {
            $userSets[] = 'name = :name';
            $userParams[':name'] = trim((string)$data['name']);
        }
        if (array_key_exists('email', $data)) {
            $userSets[] = 'email = :email';
            $userParams[':email'] = trim((string)$data['email']);
        }
        if (array_key_exists('phone', $data)) {
            $userSets[] = 'phone = :phone';
            $userParams[':phone'] = trim((string)$data['phone']);
        }
        if (array_key_exists('address', $data)) {
            $userSets[] = 'address = :address';
            $userParams[':address'] = trim((string)$data['address']);
        }

        if (!empty($userSets)) {
            $sql = 'UPDATE users SET ' . implode(', ', $userSets) . ' WHERE user_id = :id';
            $this->db->dbquery($sql);
            foreach ($userParams as $key => $val) {
                $this->db->dbbind($key, $val);
            }
            $this->db->dbbind(':id', $userId);
            $this->db->dbexecute();
        }

        // Update suppliers table
        $supplierSets = [];
        $supplierParams = [];
        if (array_key_exists('shop_name', $data)) {
            $supplierSets[] = 'shop_name = :shop_name';
            $supplierParams[':shop_name'] = trim((string)$data['shop_name']);
        }
        if (array_key_exists('description', $data)) {
            $supplierSets[] = 'description = :description';
            $supplierParams[':description'] = trim((string)$data['description']);
        }
        if (array_key_exists('business_url', $data)) {
            $supplierSets[] = 'verify_url = :verify_url';
            $supplierParams[':verify_url'] = trim((string)$data['business_url']);
        }

        if (!empty($supplierSets)) {
            $sql = 'UPDATE suppliers SET ' . implode(', ', $supplierSets) . ' WHERE user_id = :id';
            $this->db->dbquery($sql);
            foreach ($supplierParams as $key => $val) {
                $this->db->dbbind($key, $val);
            }
            $this->db->dbbind(':id', $userId);
            $this->db->dbexecute();
        }

        return true;
    }
}
