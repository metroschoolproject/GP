<?php

class Admin extends Controller
{
    private $notificationModel;
    private $supplierProfileModel;
    private $paymentModel;
    private $serviceManagementModel;

    public function __construct()
    {
        $this->notificationModel = $this->model('Notification');
        $this->supplierProfileModel = $this->model('SupplierProfile');
        $this->paymentModel = $this->model('Payment');
        $this->serviceManagementModel = $this->model('SupplierServiceManager');
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

}   
