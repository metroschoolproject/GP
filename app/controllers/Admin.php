<?php

require_once APPROOT . '/services/UploadService.php';

class Admin extends Controller
{
    private $notificationModel;
    private $supplierProfileModel;
    private $paymentModel;
    private $serviceManagementModel;
    private $uploadService;

    public function __construct()
    {
        $this->notificationModel = $this->model('Notification');
        $this->supplierProfileModel = $this->model('SupplierProfile');
        $this->paymentModel = $this->model('Payment');
        $this->serviceManagementModel = $this->model('SupplierServiceManager');
        $this->uploadService = new UploadService();
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

        $this->view('admin/packages/detail', [
            'package' => $package,
            'categories' => $categories,
            'serviceOptions' => $packageModel->getAdminServiceOptions(),
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
        ];

        $packageId = $packageModel->createPackageType($data);

        if (!$packageId) {
            $_SESSION['admin_flash'] = 'Failed to create package type.';
            redirect('admin/packageCreate');
        }

        $serviceIds = $_POST['service_ids'] ?? [];
        if (is_array($serviceIds)) {
            foreach ($serviceIds as $serviceId) {
                $packageModel->addPackageService($packageId, (int)$serviceId, (int)($_POST['guest_count'] ?? 100));
            }
        }

        $createdPackage = $packageModel->getPackageById($packageId);
        if ($createdPackage) {
            $packageModel->updatePackageType($packageId, [
                'base_price' => (float)($createdPackage['included_total'] ?? 0),
            ]);
        }

        $_SESSION['admin_flash'] = 'Package type created successfully.';
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
        $added = $packageModel->addPackageService((int)$packageId, $serviceId, $guestCount);
        if ($added) {
            $this->refreshPackageBasePrice($packageModel, (int)$packageId);
        }

        $_SESSION['admin_flash'] = $added ? 'Service added to package and base price updated.' : 'That service is already included or cannot be added.';
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
        $updated = $packageModel->updatePackageItemQuantity((int)$itemId, $quantity);
        if ($updated && $packageId > 0) {
            $this->refreshPackageBasePrice($packageModel, $packageId);
        }

        $_SESSION['admin_flash'] = $updated
            ? 'Package food guest count and base price updated.'
            : 'Only food or catering services can use guest count.';
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

}   
