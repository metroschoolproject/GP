<?php

require_once APPROOT . '/services/UploadService.php';
require_once APPROOT . '/services/PaymentGatewayService.php';
require_once APPROOT . '/controllers/Booking.php';
require_once APPROOT . '/services/EmailService.php';

class Admin extends Controller
{
    private $notificationModel;
    private $supplierProfileModel;
    private $paymentModel;
    private $serviceManagementModel;
    private $uploadService;
    private $paymentGateway;

    public function __construct()
    {
        $this->notificationModel = $this->model('Notification');
        $this->supplierProfileModel = $this->model('SupplierProfile');
        $this->paymentModel = $this->model('Payment');
        $this->serviceManagementModel = $this->model('SupplierServiceManager');
        $this->uploadService = new UploadService();
        $this->paymentGateway = new PaymentGatewayService();
    }   

    public function dashboard()
    {
        $this->view('admin/dashboard');
    }

    public function logout()
    {
        redirect('users/logout');
    }

    public function overview()
    {
        $this->view('admin/admin_dashboard');
    }

    /**
     * JSON endpoint — returns all dashboard KPIs from real database data.
     * Query params: filter (today|week|month|year), date (YYYY-MM-DD)
     */
    public function overviewData()
    {
        $filter = $_GET['filter'] ?? 'week';
        if (!in_array($filter, ['today', 'week', 'month', 'year'], true)) {
            $filter = 'week';
        }
        $dateParam = trim($_GET['date'] ?? '');
        $targetDate = $dateParam !== '' ? $dateParam : date('Y-m-d');

        $db = new Database();

        // ── Escrow Wallet ─────────────────────────────────────────
        $db->dbquery(
            "SELECT COALESCE(SUM(CASE WHEN status = 'success' THEN COALESCE(paid_amount, amount, 0) ELSE 0 END), 0) AS total,
                    COALESCE(SUM(CASE WHEN status = 'pending' THEN COALESCE(paid_amount, amount, 0) ELSE 0 END), 0) AS pending_release
             FROM payments
             WHERE escrow_status = 'held' AND type = 'deposit'"
        );
        $escrowRow = $db->getsingledata() ?: [];
        $escrowTotal = (float)($escrowRow['total'] ?? 0);
        $escrowPending = (float)($escrowRow['pending_release'] ?? 0);
        $escrowAvailable = $escrowTotal - $escrowPending;

        // ── Total Revenue (successful deposits) ───────────────────
        $db->dbquery(
            "SELECT COALESCE(SUM(COALESCE(paid_amount, amount, 0)), 0) AS total_revenue
             FROM payments
             WHERE type = 'deposit' AND status = 'success'"
        );
        $totalRevenue = (float)($db->getsingledata()['total_revenue'] ?? 0);

        // ── Bookings stats ───────────────────────────────────────
        $db->dbquery(
            "SELECT COUNT(*) AS total,
                    SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) AS confirmed,
                    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled,
                    SUM(CASE WHEN status = 'payment_submitted' THEN 1 ELSE 0 END) AS payment_submitted,
                    COALESCE(AVG(CASE WHEN status IN ('paid','payment_verified','confirmed','completed','pending_final_payment','finalized') THEN total_amount END), 0) AS avg_spend
             FROM bookings
             WHERE status != 'draft'"
        );
        $bookingStats = $db->getsingledata() ?: [];
        $totalBookings = (int)($bookingStats['total'] ?? 0);
        $confirmedBookings = (int)($bookingStats['confirmed'] ?? 0);
        $cancelledBookings = (int)($bookingStats['cancelled'] ?? 0);
        $pendingPaymentSubmissions = (int)($bookingStats['payment_submitted'] ?? 0);
        $avgSpend = round((float)($bookingStats['avg_spend'] ?? 0), 2);

        // ── Today & week booking counts ──────────────────────────
        $db->dbquery(
            "SELECT SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) AS today_count,
                    SUM(CASE WHEN YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1) THEN 1 ELSE 0 END) AS week_count
             FROM bookings
             WHERE status != 'draft'"
        );
        $countsRow = $db->getsingledata() ?: [];
        $todayBookings = (int)($countsRow['today_count'] ?? 0);
        $weekBookings = (int)($countsRow['week_count'] ?? 0);

        // ── Pending booking confirmations (supplier pending) ─────
        $db->dbquery(
            "SELECT COUNT(*) AS pending_confirm
             FROM booking_suppliers bs
             INNER JOIN bookings b ON bs.booking_id = b.id
             WHERE bs.status = 'pending' AND b.status != 'draft'"
        );
        $pendingBookingConfirm = (int)(($db->getsingledata() ?: [])['pending_confirm'] ?? 0);

        // ── Vendor approvals ─────────────────────────────────────
        $db->dbquery(
            "SELECT SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) AS approved,
                    SUM(CASE WHEN status = 'verified' THEN 1 ELSE 0 END) AS verified,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending,
                    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) AS rejected
             FROM suppliers
             WHERE deleted_at IS NULL"
        );
        $vendorStats = $db->getsingledata() ?: [];
        $vendorApproved = (int)($vendorStats['approved'] ?? 0) + (int)($vendorStats['verified'] ?? 0);
        $vendorPending = (int)($vendorStats['pending'] ?? 0);
        $vendorRejected = (int)($vendorStats['rejected'] ?? 0);

        // ── Community (customers & suppliers) ────────────────────
        $db->dbquery(
            "SELECT SUM(CASE WHEN ur.role_id = 1 THEN 1 ELSE 0 END) AS customers,
                    SUM(CASE WHEN ur.role_id = 2 THEN 1 ELSE 0 END) AS suppliers,
                    SUM(CASE WHEN ur.role_id IN (3,4) THEN 1 ELSE 0 END) AS staffs
             FROM user_roles ur
             INNER JOIN users u ON ur.user_id = u.user_id
             WHERE u.status = 'active' AND u.deleted_at IS NULL"
        );
        $communityStats = $db->getsingledata() ?: [];
        $totalCustomers = (int)($communityStats['customers'] ?? 0);
        $totalSuppliers = (int)($communityStats['suppliers'] ?? 0);
        $totalStaffs = (int)($communityStats['staffs'] ?? 0);

        // ── Revenue Trend ────────────────────────────────────────
        $revenueTrend = $this->buildRevenueTrend($db, $filter, $targetDate);

        // ── Supplier Categories ──────────────────────────────────
        $db->dbquery(
            "SELECT c.name AS category_name, COUNT(sc.supplier_id) AS supplier_count
             FROM categories c
             INNER JOIN supplier_categories sc ON c.id = sc.category_id
             GROUP BY c.id, c.name
             ORDER BY supplier_count DESC, c.name ASC"
        );
        $categoryRows = $db->getmultidata();
        $categoryLabels = [];
        $categoryValues = [];
        foreach ($categoryRows as $row) {
            $categoryLabels[] = $row['category_name'];
            $categoryValues[] = (int)$row['supplier_count'];
        }
        if (empty($categoryLabels)) {
            $categoryLabels = ['No categories yet'];
            $categoryValues = [1];
        }

        // ── Top Partners ─────────────────────────────────────────
        $db->dbquery(
            "SELECT s.shop_name, COUNT(bs.id) AS booking_count
             FROM booking_suppliers bs
             INNER JOIN suppliers s ON bs.supplier_id = s.supplier_id
             INNER JOIN bookings b ON bs.booking_id = b.id AND b.status != 'draft'
             WHERE bs.status IN ('confirmed', 'completed')
             GROUP BY bs.supplier_id, s.shop_name
             ORDER BY booking_count DESC, s.shop_name ASC
             LIMIT 3"
        );
        $topSuppliers = $db->getmultidata();
        if (empty($topSuppliers)) {
            $topSuppliers = [['shop_name' => 'No data yet', 'booking_count' => 0]];
        }

        // ── Upcoming Events ──────────────────────────────────────
        $db->dbquery(
            "SELECT b.id, u.name AS customer_name, ed.event_date, ed.start_time, ed.location,
                    b.status,
                    (SELECT s.shop_name FROM booking_suppliers bs_s
                     INNER JOIN suppliers s_s ON bs_s.supplier_id = s_s.supplier_id
                     WHERE bs_s.booking_id = b.id ORDER BY bs_s.id ASC LIMIT 1) AS supplier_name
             FROM bookings b
             INNER JOIN event_details ed ON ed.booking_id = b.id
             LEFT JOIN users u ON b.user_id = u.user_id
             WHERE ed.event_date >= CURDATE()
               AND b.status IN ('paid','payment_verified','confirmed','pending_final_payment','finalized','completed')
             ORDER BY ed.event_date ASC, ed.start_time ASC
             LIMIT 8"
        );
        $upcomingEvents = $db->getmultidata();

        // ── Popular Packages ─────────────────────────────────────
        $db->dbquery(
            "SELECT p.name, p.image_url,
                    COUNT(bi.id) AS booking_count,
                    COALESCE(SUM(bi.price), 0) AS total_revenue,
                    COALESCE(AVG(r.rating), 0) AS avg_rating
             FROM booking_items bi
             INNER JOIN packages p ON bi.item_id = p.package_id AND bi.item_type = 'package'
             INNER JOIN bookings b ON bi.booking_id = b.id AND b.status != 'draft'
             LEFT JOIN reviews r ON r.booking_id = bi.booking_id AND r.supplier_id IS NOT NULL
             WHERE p.deleted_at IS NULL
             GROUP BY p.package_id, p.name, p.image_url
             ORDER BY booking_count DESC, total_revenue DESC
             LIMIT 4"
        );
        $popularPackages = $db->getmultidata();
        if (empty($popularPackages)) {
            // Fallback: show active packages even without bookings
            $db->dbquery(
                "SELECT name, image_url, 0 AS booking_count, 0 AS total_revenue, 0 AS avg_rating
                 FROM packages
                 WHERE is_active = 1 AND deleted_at IS NULL
                 ORDER BY sort_order ASC, created_at DESC
                 LIMIT 4"
            );
            $popularPackages = $db->getmultidata();
        }

        // ── Assemble response ────────────────────────────────────
        $this->jsonResponse([
            'totalBookings' => $totalBookings,
            'totalRevenue' => $totalRevenue,
            'pendingBookings' => max(0, $totalBookings - $confirmedBookings - $cancelledBookings),
            'confirmedBookings' => $confirmedBookings,
            'cancelledBookings' => $cancelledBookings,
            'avgSpend' => $avgSpend,
            'todayBookings' => $todayBookings,
            'weekBookings' => $weekBookings,
            'totalCustomers' => $totalCustomers,
            'totalSuppliers' => $totalSuppliers,
            'totalStaffs' => $totalStaffs,
            'vendorApproved' => $vendorApproved,
            'vendorPending' => $vendorPending,
            'vendorRejected' => $vendorRejected,
            'pendingBookingConfirm' => $pendingBookingConfirm,
            'pendingPayments' => $pendingPaymentSubmissions,
            'pendingVendorApproval' => $vendorPending,
            'topSuppliers' => array_map(function ($s) {
                return [
                    'name' => $s['shop_name'] ?? '—',
                    'bookings' => (int)($s['booking_count'] ?? 0),
                ];
            }, $topSuppliers),
            'upcomingEvents' => array_map(function ($e) {
                $eventDate = $e['event_date'] ?? '';
                $ts = strtotime($eventDate);
                $dateFormatted = $ts ? date('M d', $ts) : '—';
                $timeFormatted = !empty($e['start_time']) ? date('g:i A', strtotime($e['start_time'])) : '';
                $isToday = $eventDate === date('Y-m-d');
                $isTomorrow = $eventDate === date('Y-m-d', strtotime('+1 day'));
                if ($isToday) {
                    $dateLabel = 'Today';
                } elseif ($isTomorrow) {
                    $dateLabel = 'Tomorrow';
                } else {
                    $dateLabel = $dateFormatted;
                }
                $dateTime = trim($dateLabel . ($timeFormatted ? ', ' . $timeFormatted : ''));
                $supplierName = $e['supplier_name'] ?? '—';

                return [
                    'event' => !empty($supplierName) ? $supplierName : 'Wedding',
                    'customer' => $e['customer_name'] ?? '—',
                    'dateTime' => $dateTime ?: '—',
                    'location' => $e['location'] ?: '—',
                    'package' => $supplierName,
                    'status' => $e['status'] ?? 'confirmed',
                ];
            }, $upcomingEvents),
            'revenueLabels' => $revenueTrend['labels'],
            'revenueSales' => $revenueTrend['sales'],
            'peakPeriod' => $revenueTrend['peak'],
            'peakPeriodLabel' => $revenueTrend['peakLabel'],
            'supplierCategories' => [
                'labels' => $categoryLabels,
                'values' => $categoryValues,
            ],
            'popularPackages' => array_map(function ($p) {
                return [
                    'name' => $p['name'] ?? '—',
                    'image' => $p['image_url'] ?: '',
                    'bookings' => (int)($p['booking_count'] ?? 0),
                    'revenue' => round((float)($p['total_revenue'] ?? 0), 2),
                    'rating' => round((float)($p['avg_rating'] ?? 0), 1) ?: 0,
                ];
            }, $popularPackages),
            'escrow' => [
                'total' => $escrowTotal,
                'pendingRelease' => $escrowPending,
                'available' => $escrowAvailable,
            ],
        ]);
    }

    /**
     * Build revenue trend data: labels, sales values, peak label, peak period.
     */
    private function buildRevenueTrend(Database $db, string $filter, string $targetDate): array
    {
        $now = strtotime($targetDate);
        $today = date('Y-m-d', $now);

        switch ($filter) {
            case 'today':
                $labels = [];
                for ($h = 1; $h <= 24; $h++) {
                    $labels[] = sprintf('%dhr', $h);
                }
                $db->dbquery(
                    "SELECT HOUR(created_at) AS period,
                            COALESCE(SUM(total_amount), 0) AS revenue
                     FROM bookings
                     WHERE status NOT IN ('draft', 'cancelled')
                       AND DATE(created_at) = :date
                     GROUP BY HOUR(created_at)
                     ORDER BY period ASC"
                );
                $db->dbbind(':date', $today);
                $rows = $db->getmultidata();
                $sales = $this->padSeries(24, $rows, 'period');
                $peakLabel = 'PEAK HOUR:';
                break;

            case 'week':
                $monday = date('Y-m-d', strtotime('monday this week', $now));
                $labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                $db->dbquery(
                    "SELECT WEEKDAY(created_at) AS period,
                            COALESCE(SUM(total_amount), 0) AS revenue
                     FROM bookings
                     WHERE status NOT IN ('draft', 'cancelled')
                       AND DATE(created_at) >= :monday
                     GROUP BY WEEKDAY(created_at)
                     ORDER BY period ASC"
                );
                $db->dbbind(':monday', $monday);
                $rows = $db->getmultidata();
                $sales = $this->padSeries(7, $rows, 'period');
                $peakLabel = 'PEAK DAY:';
                break;

            case 'month':
                $labels = [];
                $startOfMonth = date('Y-m-01', $now);
                for ($w = 1; $w <= 4; $w++) {
                    $labels[] = 'Week-' . $w;
                }
                $db->dbquery(
                    "SELECT (WEEK(created_at, 1) - WEEK(:month_start, 1)) AS period,
                            COALESCE(SUM(total_amount), 0) AS revenue
                     FROM bookings
                     WHERE status NOT IN ('draft', 'cancelled')
                       AND DATE_FORMAT(created_at, '%Y-%m') = :ym
                     GROUP BY period
                     ORDER BY period ASC"
                );
                $db->dbbind(':month_start', $startOfMonth);
                $db->dbbind(':ym', date('Y-m', $now));
                $rows = $db->getmultidata();
                $sales = $this->padSeries(4, $rows, 'period');
                $peakLabel = 'PEAK WEEK:';
                break;

            case 'year':
                $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                $db->dbquery(
                    "SELECT MONTH(created_at) - 1 AS period,
                            COALESCE(SUM(total_amount), 0) AS revenue
                     FROM bookings
                     WHERE status NOT IN ('draft', 'cancelled')
                       AND YEAR(created_at) = :year
                     GROUP BY MONTH(created_at)
                     ORDER BY period ASC"
                );
                $db->dbbind(':year', date('Y', $now));
                $rows = $db->getmultidata();
                $sales = $this->padSeries(12, $rows, 'period');
                $peakLabel = 'PEAK MONTH:';
                break;

            default:
                $labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                $sales = array_fill(0, 7, 0);
                $peakLabel = 'PEAK DAY:';
        }

        $maxVal = !empty($sales) ? max($sales) : 0;
        $peakIndex = $maxVal > 0 ? array_search($maxVal, $sales) : 0;
        $peakPeriod = $labels[$peakIndex] ?? '—';
        // Round sales for cleaner display
        $sales = array_map(function ($v) { return round($v, 2); }, $sales);

        return [
            'labels' => $labels,
            'sales' => $sales,
            'peak' => $peakPeriod,
            'peakLabel' => $peakLabel,
        ];
    }

    /**
     * Fill a series array of $length slots using DB rows keyed by $periodColumn.
     * Indices beyond the last slot are folded into the final slot.
     */
    private function padSeries(int $length, array $rows, string $periodColumn): array
    {
        $series = array_fill(0, $length, 0);
        foreach ($rows as $row) {
            $idx = (int)($row[$periodColumn] ?? -1);
            if ($idx < 0) {
                continue;
            }
            if ($idx >= $length) {
                $idx = $length - 1;
            }
            $series[$idx] = (float)($row['revenue'] ?? 0);
        }
        return $series;
    }

    public function notificationsJson()
    {
        $this->jsonResponse([
            'unread_count' => $this->notificationModel->getUnreadCount($this->currentUserId()),
            'notifications' => $this->notificationModel->getLatest($this->currentUserId(), 8),
        ]);
    }

    public function markNotificationRead($notificationId = null)
    {
        if (!$notificationId) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Notification id is required.'], 422);
        }

        $this->notificationModel->markRead((int)$notificationId, $this->currentUserId());
        $this->jsonResponse(['status' => 'success']);
    }

    public function notifications()
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $totalCount = $this->notificationModel->getAllCount($this->currentUserId());

        $this->view('admin/notifications', [
            'notifications' => $this->notificationModel->getAll($this->currentUserId(), $perPage, $offset),
            'unreadCount' => $this->notificationModel->getUnreadCount($this->currentUserId()),
            'message' => $_SESSION['admin_flash'] ?? '',
            'currentPage' => $page,
            'totalPages' => max(1, (int)ceil($totalCount / $perPage)),
            'totalCount' => $totalCount,
            'perPage' => $perPage,
        ]);
        unset($_SESSION['admin_flash']);
    }

    public function bookings()
    {
        $bookingController = new Booking();
        return call_user_func_array([$bookingController, 'adminBookings'], func_get_args());
    }

    public function bookingDetail($bookingId = null)
    {
        $bookingController = new Booking();
        return $bookingController->adminBookingDetail((int)$bookingId);
    }

    public function bookingCancel()
    {
        $bookingController = new Booking();
        return call_user_func_array([$bookingController, 'adminCancelBooking'], func_get_args());
    }

    public function markBookingReceived()
    {
        $bookingController = new Booking();
        return call_user_func_array([$bookingController, 'adminMarkBookingReceived'], func_get_args());
    }

    public function notification($notificationId = null)
    {
        if (!$notificationId) {
            redirect('admin/notifications');
        }

        $notification = $this->notificationModel->getById((int)$notificationId, $this->currentUserId());

        if (!$notification) {
            redirect('admin/notifications');
        }

        $this->notificationModel->markRead((int)$notificationId, $this->currentUserId());
        $referenceType = (string)($notification['reference_type'] ?? '');
        $referenceId = (int)($notification['reference_id'] ?? 0);

        if ($referenceType === 'supplier' && $referenceId > 0) {
            redirect('admin/supplier/' . $referenceId);
        }

        if ($referenceType === 'booking' && $referenceId > 0) {
            redirect('admin/bookingDetail/' . $referenceId);
        }

        if ($referenceType === 'payment' && $referenceId > 0) {
            redirect('admin/payments?payment=' . $referenceId);
        }

        if ($referenceType === 'service' && $referenceId > 0) {
            redirect('admin/service/' . $referenceId);
        }

        redirect('admin/dashboard');
    }

    public function service($serviceId = null)
    {
        if (!$serviceId) {
            redirect('admin/notifications');
        }

        $service = $this->serviceManagementModel->getAdminServiceDetail((int)$serviceId);

        if (!$service) {
            $_SESSION['admin_flash'] = 'Service record was not found.';
            redirect('admin/notifications');
        }

        $this->view('admin/service_review', [
            'service' => $service,
            'message' => $_SESSION['admin_flash'] ?? '',
        ]);
        unset($_SESSION['admin_flash']);
    }

    public function approveService($serviceId = null)
    {
        if (!$serviceId || ($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            redirect('admin/notifications');
        }

        $service = $this->serviceManagementModel->getAdminServiceDetail((int)$serviceId);

        if (!$service) {
            $_SESSION['admin_flash'] = 'Service record was not found.';
            redirect('admin/notifications');
        }

        if (($service['status'] ?? 'inactive') === 'active') {
            $_SESSION['admin_flash'] = 'This service is already approved and live for customers.';
            redirect('admin/service/' . (int)$serviceId);
        }

        if (empty($service['readiness']['ready'])) {
            $_SESSION['admin_flash'] = 'Service cannot be approved yet: ' . implode(' ', $service['readiness']['missing'] ?? []);
            redirect('admin/service/' . (int)$serviceId);
        }

        $this->serviceManagementModel->setServiceStatus((int)$service['supplier_id'], (int)$serviceId, true);
        $_SESSION['admin_flash'] = 'Service approved and published to customers.';
        redirect('admin/service/' . (int)$serviceId);
    }

    public function suppliers()
    {
        $this->supplierApplications();
    }

    public function application()
    {
        $this->supplierApplications();
    }

    private function supplierApplications()
    {
        $status = $_GET['status'] ?? 'pending';
        $allowedStatuses = ['pending', 'approved', 'verified', 'rejected', 'banned', 'all'];

        if (!in_array($status, $allowedStatuses, true)) {
            $status = 'pending';
        }

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 15;
        $offset = ($page - 1) * $perPage;

        $suppliers = $this->supplierProfileModel->getApplications($status, $perPage, $offset);
        $totalCount = $this->supplierProfileModel->getApplicationsCount($status);

        $this->view('admin/suppliers', [
            'suppliers' => $suppliers,
            'status' => $status,
            'stats' => $this->supplierProfileModel->getSupplierStats(),
            'topSuppliers' => $status === 'all' ? $this->supplierProfileModel->getTopSuppliers(5) : [],
            'currentPage' => $page,
            'totalPages' => max(1, (int)ceil($totalCount / $perPage)),
            'totalCount' => $totalCount,
            'perPage' => $perPage,
        ]);
    }

    public function supplier($supplierId = null)
    {
        if ($supplierId === 'application') {
            $this->supplierApplications();
            return;
        }

        if (!$supplierId) {
            redirect('admin/suppliers');
        }

        $supplier = $this->supplierProfileModel->getApplicationById((int)$supplierId);

        if (!$supplier) {
            redirect('admin/suppliers');
        }

        $this->view('admin/supplier_review', [
            'supplier' => $supplier,
            'message' => $_SESSION['admin_flash'] ?? '',
            'performance' => $this->supplierProfileModel->getSupplierPerformance((int)$supplierId),
        ]);
        unset($_SESSION['admin_flash']);
    }

    public function approveSupplier($supplierId = null)
    {
        if (!$supplierId || ($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            redirect('admin/suppliers');
        }

        $this->supplierProfileModel->updateStatus((int)$supplierId, 'approved', $this->currentUserId());
        $_SESSION['admin_flash'] = 'Supplier approved. They can now access the locked dashboard and submit membership payment.';
        redirect('admin/supplier/' . (int)$supplierId);
    }

    public function rejectSupplier($supplierId = null)
    {
        if (!$supplierId || ($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            redirect('admin/suppliers');
        }

        $reason = trim($_POST['reason'] ?? '');
        $this->supplierProfileModel->updateStatus((int)$supplierId, 'rejected', $this->currentUserId());
        if ($reason !== '') {
            $this->supplierProfileModel->warnSupplier((int)$supplierId, 0, 'Rejected: ' . $reason, $this->currentUserId());
        }
        $_SESSION['admin_flash'] = 'Supplier application rejected.';
        redirect('admin/supplier/' . (int)$supplierId);
    }

    public function banSupplier($supplierId = null)
    {
        if (!$supplierId || ($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            redirect('admin/suppliers');
        }
        $reason = trim($_POST['reason'] ?? '');
        if ($reason === '') {
            $_SESSION['admin_flash'] = 'A reason is required to ban a supplier.';
            redirect('admin/supplier/' . (int)$supplierId);
        }
        $this->supplierProfileModel->banSupplier((int)$supplierId, $reason, $this->currentUserId());
        $_SESSION['admin_flash'] = 'Supplier has been banned. Reason: ' . $reason;
        redirect('admin/supplier/' . (int)$supplierId);
    }

    public function unbanSupplier($supplierId = null)
    {
        if (!$supplierId || ($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            redirect('admin/suppliers');
        }
        $this->supplierProfileModel->unbanSupplier((int)$supplierId, $this->currentUserId());
        $_SESSION['admin_flash'] = 'Supplier has been unbanned and restored to approved status.';
        redirect('admin/supplier/' . (int)$supplierId);
    }

    public function warnSupplier($supplierId = null)
    {
        if (!$supplierId || ($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            redirect('admin/suppliers');
        }
        $level = min(2, max(1, (int)($_POST['warning_level'] ?? 1)));
        $note = trim($_POST['warn_note'] ?? '');
        if ($note === '') {
            $_SESSION['admin_flash'] = 'A note is required when issuing a warning.';
            redirect('admin/supplier/' . (int)$supplierId);
        }
        $this->supplierProfileModel->warnSupplier((int)$supplierId, $level, $note, $this->currentUserId());
        $_SESSION['admin_flash'] = 'Warning level ' . $level . ' issued to supplier.';
        redirect('admin/supplier/' . (int)$supplierId);
    }

    public function payments()
    {
        $status = $_GET['status'] ?? 'pending';
        $status = $status === 'rejected' ? 'failed' : $status;
        $allowedStatuses = ['pending', 'success', 'failed', 'all'];

        if (!in_array($status, $allowedStatuses, true)) {
            $status = 'pending';
        }

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $this->view('admin/payments', [
            'payments' => $this->paymentModel->getAdminPaymentHistory($status, $perPage, $offset),
            'status' => $status,
            'selectedPaymentId' => isset($_GET['payment']) ? (int)$_GET['payment'] : null,
            'message' => $_SESSION['admin_flash'] ?? '',
            'currentPage' => $page,
            'totalPages' => max(1, (int)ceil($this->paymentModel->getAdminPaymentHistoryCount($status) / $perPage)),
            'totalCount' => $this->paymentModel->getAdminPaymentHistoryCount($status),
            'perPage' => $perPage,
        ]);
        unset($_SESSION['admin_flash']);
    }

    public function approvePayment($paymentId = null)
    {
        if (!$paymentId || ($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            redirect('admin/payments');
        }

        $payment = $this->paymentModel->getSupplierFeePaymentById((int)$paymentId);

        if (!$payment) {
            $_SESSION['admin_flash'] = 'Payment record was not found.';
            redirect('admin/payments');
        }

        if (($payment['status'] ?? '') !== 'pending') {
            $_SESSION['admin_flash'] = 'This payment has already been reviewed.';
            redirect('admin/payments?payment=' . (int)$paymentId);
        }

        $this->paymentModel->updateSupplierFeeStatus((int)$paymentId, 'success', $this->currentUserId());
        $this->supplierProfileModel->updatePaymentReview((int)$payment['supplier_id'], 'paid', 'verified', 1, $this->currentUserId());

        $_SESSION['admin_flash'] = 'Supplier payment approved. The supplier dashboard is now unlocked.';
        redirect('admin/payments?status=success&payment=' . (int)$paymentId);
    }

    public function rejectPayment($paymentId = null)
    {
        if (!$paymentId || ($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            redirect('admin/payments');
        }

        $payment = $this->paymentModel->getSupplierFeePaymentById((int)$paymentId);

        if (!$payment) {
            $_SESSION['admin_flash'] = 'Payment record was not found.';
            redirect('admin/payments');
        }

        if (($payment['status'] ?? '') !== 'pending') {
            $_SESSION['admin_flash'] = 'This payment has already been reviewed.';
            redirect('admin/payments?payment=' . (int)$paymentId);
        }

        $this->paymentModel->updateSupplierFeeStatus((int)$paymentId, 'failed', $this->currentUserId());
        $this->supplierProfileModel->updatePaymentReview((int)$payment['supplier_id'], 'unpaid', null, 0, $this->currentUserId());

        $_SESSION['admin_flash'] = 'Supplier payment rejected. The supplier can submit payment again.';
        redirect('admin/payments?status=failed&payment=' . (int)$paymentId);
    }

    // ═══════════════════════════════════════════════════════════
    //  PACKAGE TYPE MANAGEMENT
    // ═══════════════════════════════════════════════════════════

    public function packages()
    {
        $packageModel = $this->model('PlatformPackage');
        $page = max(1, (int)($_GET['page'] ?? 1));
        $filters = [
            'search' => trim($_GET['search'] ?? ''),
            'status' => trim($_GET['status'] ?? ''),
        ];

        $result = $packageModel->getAllPackageTypesAdmin($filters, $page);

        $this->view('admin/packages/index', [
            'packages' => $result['packages'],
            'total' => $result['total'],
            'page' => $result['page'],
            'totalPages' => $result['total_pages'],
            'filters' => $filters,
            'message' => $_SESSION['admin_flash'] ?? '',
        ]);
        unset($_SESSION['admin_flash']);
    }

    public function packageDetail($packageId = null)
    {
        if (!$packageId) {
            redirect('admin/packages');
        }

        $packageModel = $this->model('PlatformPackage');
        $package = $packageModel->getPackageById((int)$packageId);

        if (!$package) {
            $_SESSION['admin_flash'] = 'Package type not found.';
            redirect('admin/packages');
        }

        $categories = $packageModel->getAllCategories();
        $serviceOptions = $packageModel->getAdminServiceOptions();
        $hallOptionsByService = [];
        foreach ($serviceOptions as $serviceOption) {
            $label = strtolower((string)($serviceOption['category_slug'] ?? '') . ' ' . (string)($serviceOption['category_name'] ?? ''));
            if (strpos($label, 'venue') !== false || strpos($label, 'hall') !== false) {
                $hallOptionsByService[(int)$serviceOption['id']] = $packageModel->getVenueRoomsForService((int)$serviceOption['id']);
            }
        }

        $this->view('admin/packages/detail', [
            'package' => $package,
            'categories' => $categories,
            'serviceOptions' => $serviceOptions,
            'hallOptionsByService' => $hallOptionsByService,
            'message' => $_SESSION['admin_flash'] ?? '',
        ]);
        unset($_SESSION['admin_flash']);
    }

    public function packageCreate()
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $packageModel = $this->model('PlatformPackage');
            $this->view('admin/packages/create', [
                'categories' => $packageModel->getAllCategories(),
                'serviceOptions' => $packageModel->getAdminServiceOptions(),
                'message' => $_SESSION['admin_flash'] ?? '',
            ]);
            unset($_SESSION['admin_flash']);
            return;
        }

        $packageModel = $this->model('PlatformPackage');
        $slug = trim($_POST['slug'] ?? '');
        $name = trim($_POST['name'] ?? '');

        if ($name === '') {
            $_SESSION['admin_flash'] = 'Package name is required.';
            redirect('admin/packageCreate');
        }

        $imageUrl = '';
        if ($this->uploadService->hasUploaded('package_image')) {
            $imageUrl = $this->uploadService->storePackageImage($_FILES['package_image']);
            if ($imageUrl === '') {
                $_SESSION['admin_flash'] = 'Package image must be JPG, PNG, or WebP and no larger than 6MB.';
                redirect('admin/packageCreate');
            }
        }

        $data = [
            'name' => $name,
            'slug' => $slug,
            'description' => trim($_POST['description'] ?? ''),
            'tagline' => trim($_POST['tagline'] ?? ''),
            'base_price' => (float)($_POST['base_price'] ?? 0),
            'image_url' => $imageUrl,
            'is_active' => !empty($_POST['is_active']),
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
            'category_id' => (int)($_POST['category_id'] ?? 0),
        ];

        $packageId = $packageModel->createPackageType($data);

        if (!$packageId) {
            $_SESSION['admin_flash'] = 'Failed to create package type.';
            redirect('admin/packageCreate');
        }

        $serviceIds = $_POST['service_ids'] ?? [];
        $selectedServiceCount = 0;
        $addedServiceCount = 0;
        if (is_array($serviceIds)) {
            foreach ($serviceIds as $serviceId) {
                $serviceId = (int)$serviceId;
                if ($serviceId <= 0) {
                    continue;
                }
                $selectedServiceCount++;
                if ($packageModel->addPackageService($packageId, $serviceId, (int)($_POST['guest_count'] ?? 100))) {
                    $addedServiceCount++;
                }
            }
        }

        $createdPackage = $packageModel->getPackageById($packageId);
        if ($createdPackage) {
            $packageModel->updatePackageType($packageId, [
                'base_price' => (float)($createdPackage['included_total'] ?? 0),
            ]);
        }

        $_SESSION['admin_flash'] = $selectedServiceCount > 0
            ? 'Package type created successfully. Added ' . $addedServiceCount . ' of ' . $selectedServiceCount . ' selected services.'
            : 'Package type created successfully.';
        redirect('admin/packageDetail/' . $packageId);
    }

    public function packageUpdate($packageId = null)
    {
        if (!$packageId || ($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            redirect('admin/packages');
        }

        $packageModel = $this->model('PlatformPackage');
        $package = $packageModel->getPackageById((int)$packageId);

        if (!$package) {
            $_SESSION['admin_flash'] = 'Package type not found.';
            redirect('admin/packages');
        }

        // Guard: only drafts can be edited
        if (($package['status'] ?? '') !== 'draft') {
            $_SESSION['admin_flash'] = 'Cannot edit a published package directly. Use Edit mode to create a draft first.';
            redirect('admin/packageDetail/' . (int)$packageId);
        }

        $data = [];

        if (isset($_POST['name'])) {
            $data['name'] = trim($_POST['name']);
        }
        if (isset($_POST['slug'])) {
            $data['slug'] = trim($_POST['slug']);
        }
        if (isset($_POST['description'])) {
            $data['description'] = trim($_POST['description']);
        }
        if (isset($_POST['tagline'])) {
            $data['tagline'] = trim($_POST['tagline']);
        }
        $data['base_price'] = $this->moneyInput($_POST['base_price'] ?? ($package['included_total'] ?? $package['base_price'] ?? 0));
        if ($this->uploadService->hasUploaded('package_image')) {
            $imageUrl = $this->uploadService->storePackageImage($_FILES['package_image']);
            if ($imageUrl === '') {
                $_SESSION['admin_flash'] = 'Package image must be JPG, PNG, or WebP and no larger than 6MB.';
                redirect('admin/packageDetail/' . (int)$packageId);
            }
            $data['image_url'] = $imageUrl;
        }
        if (isset($_POST['is_active'])) {
            $data['is_active'] = !empty($_POST['is_active']);
        }
        if (isset($_POST['sort_order'])) {
            $data['sort_order'] = (int)$_POST['sort_order'];
        }
        if (isset($_POST['category_id'])) {
            $data['category_id'] = (int)$_POST['category_id'];
        }

        $updated = $packageModel->updatePackageType((int)$packageId, $data);

        $_SESSION['admin_flash'] = $updated
            ? 'Package type updated successfully.'
            : 'Package type could not be updated. Check duplicate slug or invalid values.';
        redirect('admin/packageDetail/' . (int)$packageId);
    }

    public function packageDelete($packageId = null)
    {
        if (!$packageId || ($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            redirect('admin/packages');
        }

        $packageModel = $this->model('PlatformPackage');
        $package = $packageModel->getPackageById((int)$packageId);

        // Deletion of published packages is handled via publish flow (soft-delete on replace)
        if ($package && ($package['status'] ?? '') === 'published') {
            $_SESSION['admin_flash'] = 'Published packages cannot be deleted directly. Use the archive flow.';
            redirect('admin/packageDetail/' . (int)$packageId);
        }

        $deleted = $packageModel->deletePackageType((int)$packageId);

        $_SESSION['admin_flash'] = $deleted
            ? 'Package deleted from database.'
            : 'Package could not be deleted.';
        redirect('admin/packages');
    }

    public function packageApplySuggestedPrice($packageId = null)
    {
        if (!$packageId || ($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            redirect('admin/packages');
        }

        $packageModel = $this->model('PlatformPackage');
        $package = $packageModel->getPackageById((int)$packageId);

        if (!$package) {
            $_SESSION['admin_flash'] = 'Package type not found.';
            redirect('admin/packages');
        }

        // Guard: only drafts can be edited
        if (($package['status'] ?? '') !== 'draft') {
            $_SESSION['admin_flash'] = 'Cannot modify a published package. Use Edit mode first.';
            redirect('admin/packageDetail/' . (int)$packageId);
        }

        $serviceTotal = (float)($package['included_total'] ?? 0);
        if ($serviceTotal <= 0) {
            $_SESSION['admin_flash'] = 'Add services before applying a suggested price.';
            redirect('admin/packageDetail/' . (int)$packageId);
        }

        $packageModel->updatePackageType((int)$packageId, ['base_price' => $serviceTotal]);

        $_SESSION['admin_flash'] = 'Package base price updated from included services. Admin/customer price adds 5% agent fee.';
        redirect('admin/packageDetail/' . (int)$packageId);
    }

    /**
     * Start editing a published package — clones it into a draft.
     */
    public function packageStartEdit($packageId = null)
    {
        if (!$packageId || ($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            redirect('admin/packages');
        }

        $packageModel = $this->model('PlatformPackage');
        $package = $packageModel->getPackageById((int)$packageId);

        if (!$package) {
            $_SESSION['admin_flash'] = 'Package type not found.';
            redirect('admin/packages');
        }

        if (($package['status'] ?? '') !== 'published') {
            $_SESSION['admin_flash'] = 'Only published packages can enter edit mode.';
            redirect('admin/packageDetail/' . (int)$packageId);
        }

        $draftId = $packageModel->clonePackageAsDraft((int)$packageId);
        if (!$draftId) {
            $_SESSION['admin_flash'] = 'Failed to create editing draft.';
            redirect('admin/packageDetail/' . (int)$packageId);
        }

        $_SESSION['admin_flash'] = 'Editing draft created. Make your changes and publish when ready.';
        redirect('admin/packageDetail/' . $draftId);
    }

    /**
     * Publish a draft — replaces the original live package atomically.
     */
    public function packagePublishDraft($draftId = null)
    {
        if (!$draftId || ($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            redirect('admin/packages');
        }

        $packageModel = $this->model('PlatformPackage');
        $draft = $packageModel->getPackageById((int)$draftId);

        if (!$draft || ($draft['status'] ?? '') !== 'draft') {
            $_SESSION['admin_flash'] = 'Draft package not found.';
            redirect('admin/packages');
        }

        $publishedId = $packageModel->publishDraft((int)$draftId);
        if (!$publishedId) {
            $_SESSION['admin_flash'] = 'Failed to publish draft.';
            redirect('admin/packageDetail/' . (int)$draftId);
        }

        $_SESSION['admin_flash'] = 'Package published successfully.';
        redirect('admin/packageDetail/' . $publishedId);
    }

    /**
     * Discard a draft — permanently deletes it.
     */
    public function packageDiscardDraft($draftId = null)
    {
        if (!$draftId || ($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            redirect('admin/packages');
        }

        $packageModel = $this->model('PlatformPackage');
        $package = $packageModel->getPackageById((int)$draftId);

        if (!$package || ($package['status'] ?? '') !== 'draft') {
            $_SESSION['admin_flash'] = 'Draft package not found.';
            redirect('admin/packages');
        }

        $originalId = (int)($package['replaces_package_id'] ?? 0);
        $discarded = $packageModel->discardDraft((int)$draftId);

        $_SESSION['admin_flash'] = $discarded
            ? 'Draft discarded.'
            : 'Failed to discard draft.';
        redirect($originalId > 0 ? 'admin/packageDetail/' . $originalId : 'admin/packages');
    }

    public function packageAddItem($packageId = null)
    {
        if (!$packageId || ($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            redirect('admin/packages');
        }

        $serviceId = (int)($_POST['service_id'] ?? 0);
        if ($serviceId <= 0) {
            $_SESSION['admin_flash'] = 'Choose a service to add.';
            redirect('admin/packageDetail/' . (int)$packageId);
        }

        $packageModel = $this->model('PlatformPackage');
        $package = $packageModel->getPackageById((int)$packageId);
        if (!$package) {
            $_SESSION['admin_flash'] = 'Package type not found.';
            redirect('admin/packages');
        }

        // Guard: only drafts can be mutated
        if (($package['status'] ?? '') !== 'draft') {
            $_SESSION['admin_flash'] = 'Cannot modify a published package. Use Edit mode first.';
            redirect('admin/packageDetail/' . (int)$packageId);
        }

        $guestCount = max(1, (int)($_POST['guest_count'] ?? 100));
        $hallId = (int)($_POST['hall_id'] ?? 0);
        $added = $packageModel->addPackageService((int)$packageId, $serviceId, $guestCount, $hallId > 0 ? $hallId : null);
        if ($added) {
            $this->refreshPackageBasePrice($packageModel, (int)$packageId);
        }

        $_SESSION['admin_flash'] = $added
            ? 'Service added to package and base price updated.'
            : 'That service is already included or it cannot be added.';
        redirect('admin/packageDetail/' . (int)$packageId);
    }

    public function packageUpdateItem($itemId = null)
    {
        if (!$itemId || ($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            redirect('admin/packages');
        }

        $packageModel = $this->model('PlatformPackage');
        $packageId = $packageModel->getPackageIdForItem((int)$itemId);

        // Guard: only drafts can be mutated
        if ($packageId > 0 && !$packageModel->isDraft($packageId)) {
            $_SESSION['admin_flash'] = 'Cannot modify a published package. Use Edit mode first.';
            redirect('admin/packageDetail/' . $packageId);
        }
        $quantity = max(1, (int)($_POST['quantity'] ?? 1));
        $isHallUpdate = array_key_exists('hall_id', $_POST);
        $updated = false;
        if ($isHallUpdate) {
            $updated = $packageModel->updatePackageItemHall((int)$itemId, (int)$_POST['hall_id']);
        } else {
            $updated = $packageModel->updatePackageItemQuantity((int)$itemId, $quantity);
        }
        if ($updated && $packageId > 0) {
            $this->refreshPackageBasePrice($packageModel, $packageId);
        }

        if ($isHallUpdate) {
            $_SESSION['admin_flash'] = $updated
                ? 'Hall assignment updated successfully and base price recalculated.'
                : 'Could not update hall assignment. The room may not belong to this service.';
        } else {
            $_SESSION['admin_flash'] = $updated
                ? 'Package food guest count and base price updated.'
                : 'Only food or catering services can use guest count.';
        }
        redirect($packageId > 0 ? 'admin/packageDetail/' . $packageId : 'admin/packages');
    }

    public function packageRemoveItem($itemId = null)
    {
        if (!$itemId || ($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            redirect('admin/packages');
        }

        $packageModel = $this->model('PlatformPackage');
        $packageId = $packageModel->getPackageIdForItem((int)$itemId);

        // Guard: only drafts can be mutated
        if ($packageId > 0 && !$packageModel->isDraft($packageId)) {
            $_SESSION['admin_flash'] = 'Cannot modify a published package. Use Edit mode first.';
            redirect('admin/packageDetail/' . $packageId);
        }

        $removed = $packageModel->removePackageItem((int)$itemId);
        if ($removed && $packageId > 0) {
            $this->refreshPackageBasePrice($packageModel, $packageId);
        }

        $_SESSION['admin_flash'] = $removed ? 'Service removed and base price updated.' : 'Service could not be removed.';
        redirect($packageId > 0 ? 'admin/packageDetail/' . $packageId : 'admin/packages');
    }

    private function refreshPackageBasePrice($packageModel, $packageId)
    {
        $package = $packageModel->getPackageById((int)$packageId);
        if (!$package) {
            return false;
        }

        return $packageModel->updatePackageType((int)$packageId, [
            'base_price' => (float)($package['included_total'] ?? 0),
        ]);
    }

    private function currentUserId()
    {
        return isset($_SESSION['session_uid']) ? (int)$_SESSION['session_uid'] : null;
    }

    private function jsonResponse($payload, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($payload);
        exit;
    }

    private function moneyInput($value)
    {
        return max(0, (float)str_replace(',', '', (string)$value));
    }

    /* ─── Payment Verification Dashboard ────────────────────────────── */

    /**
     * Display pending payment slips for admin verification.
     */
    public function paymentVerification(): void
    {
        $bookingModel = $this->model('BookingModel');

        $status = $_GET['status'] ?? 'pending';
        if (!in_array($status, ['pending', 'verified', 'rejected'], true)) {
            $status = 'pending';
        }

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 15;
        $offset = ($page - 1) * $perPage;

        if ($status === 'pending') {
            $db = new Database();
            $manualPaymentSelects = [];
            foreach (['bank_name', 'account_name', 'mobile_number', 'paid_amount', 'paid_at', 'payment_slip_path'] as $column) {
                $db->dbquery("SHOW COLUMNS FROM payments LIKE :column");
                $db->dbbind(':column', $column);
                $manualPaymentSelects[] = $db->getsingledata()
                    ? 'p.' . $column
                    : 'NULL AS ' . $column;
            }

            // Count for pending tab
            $db->dbquery(
                "SELECT COUNT(*) AS total
                 FROM bookings b
                 WHERE b.status = 'payment_submitted'"
            );
            $totalCount = (int)($db->getsingledata()['total'] ?? 0);

            // Get bookings with status='payment_submitted'
            $db->dbquery(
                "SELECT b.*, u.name, u.email, u.phone,
                        p.id as payment_id, p.amount as payment_amount, p.transaction_ref, p.method,
                        " . implode(', ', $manualPaymentSelects) . ",
                        p.created_at as payment_created_at,
                        (SELECT COUNT(*) FROM booking_items WHERE booking_id = b.id) as item_count
                 FROM bookings b
                 LEFT JOIN users u ON b.user_id = u.user_id
                 LEFT JOIN payments p ON b.id = p.booking_id AND p.type = 'deposit' AND p.status = 'pending'
                 WHERE b.status = 'payment_submitted'
                 ORDER BY b.created_at DESC
                 LIMIT :limit OFFSET :offset"
            );
            $db->dbbind(':limit', $perPage, PDO::PARAM_INT);
            $db->dbbind(':offset', $offset, PDO::PARAM_INT);
            $records = $db->getmultidata();
        } else {
            $records = $this->paymentModel->getDepositReviewQueue($status, $perPage, $offset);
            $totalCount = $this->paymentModel->getDepositReviewQueueCount($status);
        }

        foreach ($records as &$record) {
            $record['booking_ref'] = $bookingModel->generateBookingRef((int)$record['id']);
        }
        unset($record);

        $this->view('admin/paymentVerification', [
            'pendingPayments' => $records,
            'activeStatus' => $status,
            'currentPage' => $page,
            'totalPages' => max(1, (int)ceil($totalCount / $perPage)),
            'totalCount' => $totalCount,
            'perPage' => $perPage,
        ]);
    }

    /**
     * Verify payment slip and approve booking (AJAX POST).
     */
    public function verifyPaymentPost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Method not allowed'], 405);
        }

        $adminId = $this->currentUserId();
        if (!$adminId) {
            $this->jsonResponse(['error' => 'Unauthorized'], 401);
            return;
        }

        $bookingId = (int)($_POST['booking_id'] ?? 0);
        $note = trim($_POST['note'] ?? '');

        if ($bookingId <= 0) {
            $this->jsonResponse(['error' => 'Invalid booking'], 400);
            return;
        }

        $bookingModel = $this->model('BookingModel');
        $beforeVerification = $bookingModel->getBookingById($bookingId);

        // Verify payment and update booking status
        if (!$bookingModel->adminVerifyPayment($bookingId, $adminId, $note)) {
            $this->jsonResponse(['error' => 'Failed to verify payment'], 500);
            return;
        }

        // Notify customer
        $notificationModel = $this->model('Notification');
        $notificationModel->notifyBookingCustomer(
            $bookingId,
            'Payment Verified',
            'Your payment has been verified! Suppliers are now reviewing your booking.',
            'payment'
        );

        // Notify suppliers
        $notificationModel->notifyBookingSuppliers(
            $bookingId,
            'New Booking — Payment Verified',
            'A new booking with confirmed payment is ready for your review.',
            'booking'
        );

        // Send the verified payment details to the customer, then notify suppliers.
        $booking   = $bookingModel->getBookingById($bookingId);
        $items     = $bookingModel->getBookingItems($bookingId);
        $eventDetails = $bookingModel->getEventDetails($bookingId);
        $customer  = $bookingModel->getCustomerForBooking($bookingId);
        $suppliers = $bookingModel->getSupplierEmailsForBooking($bookingId);
        $verifiedPayment = $bookingModel->getDepositPayment($bookingId) ?: [];
        if ($customer && $booking) {
            $emailService = new EmailService();
            $emailSent = $emailService->sendAdminVerifiedPaymentToCustomer(
                $customer,
                $booking,
                $verifiedPayment,
                $items,
                $eventDetails
            );
            $emailService->sendPaymentVerifiedEvent($customer, $suppliers, [], $booking, $items, false);
        } else {
            $emailSent = false;
        }

        $paymentId = (int)($verifiedPayment['id'] ?? 0);
        $newStatus = (string)($booking['status'] ?? 'paid');
        $bookingModel->logStatusChange(
            $bookingId,
            (string)($beforeVerification['status'] ?? 'payment_submitted'),
            $newStatus,
            $adminId,
            'Deposit verified by admin' . ($note !== '' ? ': ' . $note : '')
        );

        $this->jsonResponse([
            'success' => true,
            'email_sent' => $emailSent,
            'email_to' => $customer['email'] ?? '',
            'payment_id' => $paymentId,
            'booking_status' => $newStatus,
            'payment_status' => (string)($booking['payment_status'] ?? 'partial'),
            'paid_amount' => (float)($booking['paid_amount'] ?? 0),
            'total_amount' => (float)($booking['total_amount'] ?? 0),
            'verified_at' => $verifiedPayment['verified_at'] ?? date('Y-m-d H:i:s'),
            'message' => $emailSent
                ? 'Payment verified and confirmation email sent to the customer.'
                : 'Payment verified, but the confirmation email could not be sent. Check the mail configuration.',
        ]);
    }

    /**
     * Reject payment slip and request resubmission (AJAX POST).
     */
    public function rejectPaymentSlipPost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Method not allowed'], 405);
        }

        $adminId = $this->currentUserId();
        if (!$adminId) {
            $this->jsonResponse(['error' => 'Unauthorized'], 401);
            return;
        }

        $bookingId = (int)($_POST['booking_id'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');

        if ($bookingId <= 0 || $reason === '') {
            $this->jsonResponse(['error' => 'Please provide a reason'], 400);
            return;
        }

        $bookingModel = $this->model('BookingModel');

        // Reset booking to pending_payment
        $this->db->dbquery("UPDATE bookings SET status = 'pending_payment' WHERE id = :id LIMIT 1");
        $this->db->dbbind(':id', $bookingId, PDO::PARAM_INT);

        if (!$this->db->dbexecute()) {
            $this->jsonResponse(['error' => 'Failed to reject payment'], 500);
            return;
        }

        // Mark the pending payment record as failed
        $this->db->dbquery("SHOW COLUMNS FROM payments LIKE 'verified_note'");
        $hasVerifiedNote = (bool)$this->db->getsingledata();
        $setParts = ["status = 'failed'", 'verified_by = :admin', 'verified_at = NOW()'];
        if ($hasVerifiedNote) {
            $setParts[] = 'verified_note = :reason';
        }

        $this->db->dbquery(
            "UPDATE payments SET " . implode(', ', $setParts) . "
             WHERE booking_id = :bid AND type = 'deposit' AND status = 'pending' LIMIT 1"
        );
        $this->db->dbbind(':admin', $adminId, PDO::PARAM_INT);
        if ($hasVerifiedNote) {
            $this->db->dbbind(':reason', $reason, PDO::PARAM_STR);
        }
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        $this->db->dbexecute();

        // Notify customer
        $notificationModel = $this->model('Notification');
        $notificationModel->notifyBookingCustomer(
            $bookingId,
            'Payment Slip Rejected',
            'Your payment slip was rejected. Reason: ' . $reason . '. Please resubmit a valid payment proof.',
            'payment'
        );

        $this->jsonResponse([
            'success' => true,
            'message' => 'Payment rejected and customer has been notified.',
        ]);
    }

    /* ─── Cron Jobs & Scheduled Tasks ──────────────────────────────── */

    /**
     * Collect final payments for bookings 2-3 days before event.
     * Call via: curl https://goldenpromise.com/admin/cronCollectFinalPayments?token=SECRET_CRON_TOKEN
     *
     * Add to crontab:
     * 0 9 * * * curl -s "https://goldenpromise.com/admin/cronCollectFinalPayments?token=..." > /dev/null
     */
    public function cronCollectFinalPayments(): void
    {
        // Security: Verify cron token
        $cronToken = $_GET['token'] ?? '';
        $expectedToken = defined('CRON_TOKEN') ? CRON_TOKEN : '';

        if ($cronToken !== $expectedToken || $expectedToken === '') {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized cron job']);
            exit;
        }

        $bookingModel = $this->model('BookingModel');
        $processed = $bookingModel->collectFinalPaymentDueBookings();

        if ($processed === false) {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to collect payments']);
        } else {
            echo json_encode([
                'success' => true,
                'processed' => $processed,
                'timestamp' => date('Y-m-d H:i:s'),
            ]);
        }

        exit;
    }

    /**
     * Send payment reminders for bookings 3-5 days before event.
     * Call via: curl https://goldenpromise.com/admin/cronPaymentReminders?token=SECRET_CRON_TOKEN
     */
    public function cronPaymentReminders(): void
    {
        $cronToken = $_GET['token'] ?? '';
        $expectedToken = defined('CRON_TOKEN') ? CRON_TOKEN : '';

        if ($cronToken !== $expectedToken || $expectedToken === '') {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized cron job']);
            exit;
        }

        // Find CONFIRMED bookings with event in next 5 days
        $this->db->dbquery(
            "SELECT b.id, b.user_id, b.total_amount, b.paid_amount, u.email, u.name, ed.event_date
             FROM bookings b
             JOIN users u ON b.user_id = u.user_id
             LEFT JOIN event_details ed ON ed.booking_id = b.id
             WHERE b.status = 'confirmed'
               AND NOT EXISTS (SELECT 1 FROM payments WHERE booking_id = b.id AND type = 'remaining' AND status IN ('pending', 'success'))
               AND DATE(ed.event_date) BETWEEN DATE_ADD(CURDATE(), INTERVAL 3 DAY) AND DATE_ADD(CURDATE(), INTERVAL 5 DAY)
             ORDER BY ed.event_date ASC"
        );
        $bookings = $this->db->getmultidata();

        $emailService = new EmailService();
        $sent = 0;

        foreach ($bookings as $booking) {
            $customer = [
                'name' => $booking['name'] ?? '',
                'email' => $booking['email'] ?? '',
            ];
            $dueDate = date('M d, Y', strtotime($booking['event_date'] ?? 'now'));

            if ($emailService->sendFinalPaymentReminder($customer, $booking, $dueDate)) {
                $sent++;
            }
        }

        echo json_encode([
            'success' => true,
            'reminders_sent' => $sent,
            'timestamp' => date('Y-m-d H:i:s'),
        ]);

        exit;
    }

    /**
     * Process supplier payouts via 2C2P.
     * Call via: curl https://goldenpromise.com/admin/cronProcessPayouts?token=SECRET_CRON_TOKEN
     *
     * Payouts are created when supplier fee is paid. This cron processes pending payouts.
     * For now, suppliers need to provide bank details during onboarding.
     * TODO: Add bank_account and bank_code columns to suppliers table.
     */
    public function cronProcessPayouts(): void
    {
        $cronToken = $_GET['token'] ?? '';
        $expectedToken = defined('CRON_TOKEN') ? CRON_TOKEN : '';

        if ($cronToken !== $expectedToken || $expectedToken === '') {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized cron job']);
            exit;
        }

        $this->db->dbquery(
            "SELECT p.*, s.supplier_id, s.bank_account, s.bank_code
             FROM payments p
             JOIN suppliers s ON p.supplier_id = s.supplier_id
             WHERE p.type = 'payout' AND p.status = 'pending'"
        );
        $payouts = $this->db->getmultidata();

        $processed = 0;
        $failed = 0;

        foreach ($payouts as $payout) {
            $bankAccount = $payout['bank_account'] ?? '';
            $bankCode = $payout['bank_code'] ?? 'AYA';

            if (!$bankAccount) {
                $this->db->dbquery(
                    "UPDATE payments SET status = 'failed', verified_at = NOW()
                     WHERE id = :id LIMIT 1"
                );
                $this->db->dbbind(':id', (int)$payout['id']);
                $this->db->dbexecute();
                $failed++;
                continue;
            }

            $result = $this->paymentGateway->createSupplierPayout(
                (int)$payout['supplier_id'],
                (float)$payout['amount'],
                $bankAccount,
                $bankCode
            );

            if ($result['success'] ?? false) {
                $this->db->dbquery(
                    "UPDATE payments SET status = 'processing', verified_at = NOW()
                     WHERE id = :id LIMIT 1"
                );
                $this->db->dbbind(':id', (int)$payout['id']);
                $this->db->dbexecute();
                $processed++;
            } else {
                $this->db->dbquery(
                    "UPDATE payments SET status = 'failed', verified_at = NOW()
                     WHERE id = :id LIMIT 1"
                );
                $this->db->dbbind(':id', (int)$payout['id']);
                $this->db->dbexecute();
                $failed++;
            }
        }

        echo json_encode([
            'success' => true,
            'payouts_processed' => $processed,
            'payouts_failed' => $failed,
            'timestamp' => date('Y-m-d H:i:s'),
        ]);

        exit;
    }

    /**
     * Auto-expire pending custom-service booking requests where the 48-hour supplier response deadline has passed.
     * Call via: curl https://goldenpromise.com/admin/cronExpireBookingRequests?token=SECRET_CRON_TOKEN
     *
     * Add to crontab:
     * 0 * * * * curl -s "https://goldenpromise.com/admin/cronExpireBookingRequests?token=..." > /dev/null
     */
    public function cronExpireBookingRequests(): void
    {
        $cronToken = $_GET['token'] ?? '';
        $expectedToken = defined('CRON_TOKEN') ? CRON_TOKEN : '';

        if ($cronToken !== $expectedToken || $expectedToken === '') {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized cron job']);
            exit;
        }

        $bookingModel = $this->model('BookingModel');
        $notificationModel = $this->model('Notification');
        $expired = $bookingModel->expireOverdueBookingRequests();

        if ($expired > 0) {
            $this->db->dbquery(
                "SELECT b.id
                 FROM bookings b
                 INNER JOIN booking_status_logs bsl ON bsl.booking_id = b.id
                 WHERE b.status = 'cancelled'
                   AND bsl.new_status = 'cancelled'
                   AND bsl.note LIKE '%Auto-expired%'
                   AND bsl.created_at >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)"
            );
            $recentExpired = $this->db->getmultidata();
            foreach ($recentExpired as $row) {
                $notificationModel->notifyBookingCustomer(
                    (int)$row['id'],
                    'Booking Request Expired',
                    'Your booking request expired because no supplier responded within 48 hours. Please try submitting a new request.',
                    'booking'
                );
            }
        }

        echo json_encode([
            'success' => true,
            'expired' => $expired,
            'timestamp' => date('Y-m-d H:i:s'),
        ]);

        exit;
    }

}
