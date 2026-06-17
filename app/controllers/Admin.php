<?php

require_once APPROOT . '/services/UploadService.php';
require_once APPROOT . '/services/PaymentGatewayService.php';
require_once APPROOT . '/controllers/Booking.php';

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
        $this->view('admin/notifications', [
            'notifications' => $this->notificationModel->getAll($this->currentUserId(), 80),
            'unreadCount' => $this->notificationModel->getUnreadCount($this->currentUserId()),
            'message' => $_SESSION['admin_flash'] ?? '',
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

        $this->view('admin/suppliers', [
            'suppliers' => $this->supplierProfileModel->getApplications($status),
            'status' => $status,
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

        $this->supplierProfileModel->updateStatus((int)$supplierId, 'rejected', $this->currentUserId());
        $_SESSION['admin_flash'] = 'Supplier application rejected.';
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

        $this->view('admin/payments', [
            'payments' => $this->paymentModel->getSupplierFeeQueue($status),
            'status' => $status,
            'selectedPaymentId' => isset($_GET['payment']) ? (int)$_GET['payment'] : null,
            'message' => $_SESSION['admin_flash'] ?? '',
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
        $deleted = $packageModel->deletePackageType((int)$packageId);

        $_SESSION['admin_flash'] = $deleted
            ? 'Package type deleted from database.'
            : 'Package type could not be deleted. It may be linked to existing records.';
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

        $serviceTotal = (float)($package['included_total'] ?? 0);
        if ($serviceTotal <= 0) {
            $_SESSION['admin_flash'] = 'Add services before applying a suggested price.';
            redirect('admin/packageDetail/' . (int)$packageId);
        }

        $packageModel->updatePackageType((int)$packageId, ['base_price' => $serviceTotal]);

        $_SESSION['admin_flash'] = 'Package base price updated from included services. Admin/customer price adds 5% agent fee.';
        redirect('admin/packageDetail/' . (int)$packageId);
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
        if (!$packageModel->getPackageById((int)$packageId)) {
            $_SESSION['admin_flash'] = 'Package type not found.';
            redirect('admin/packages');
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
                 ORDER BY b.created_at DESC"
            );
            $records = $db->getmultidata();
        } else {
            // Verified / rejected deposit history (payment-centric).
            $records = $this->paymentModel->getDepositReviewQueue($status);
        }

        foreach ($records as &$record) {
            $record['booking_ref'] = $bookingModel->generateBookingRef((int)$record['id']);
        }
        unset($record);

        $this->view('admin/paymentVerification', [
            'pendingPayments' => $records,
            'activeStatus' => $status,
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

        $this->jsonResponse([
            'success' => true,
            'message' => 'Payment verified successfully. Customer and suppliers have been notified.',
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
