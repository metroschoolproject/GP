<?php

class Payments extends Controller
{
    private const SUPPLIER_MEMBERSHIP_FEE = 50000.00;

    private $paymentModel;
    private $supplierProfileModel;
    private $notificationModel;

    public function __construct()
    {
        $this->paymentModel = $this->model('Payment');
        $this->supplierProfileModel = $this->model('SupplierProfile');
        $this->notificationModel = $this->model('Notification');
    }

    public function supplierFee()
    {
        $userId = $_SESSION['session_uid'] ?? $_SESSION['pending_register_user_id'] ?? null;

        if (!$userId) {
            redirect('users/login');
        }

        $supplier = $this->supplierProfileModel->getByUserId($userId);

        if (!$supplier) {
            redirect('supplier/onboarding');
        }

        if (!in_array(strtolower($supplier['status'] ?? ''), ['approved', 'verified'], true)) {
            redirect('supplier/pending');
        }

        if (
            ($supplier['payment_status'] ?? '') === 'paid' ||
            $this->paymentModel->hasSuccessfulSupplierFeePayment((int)$supplier['supplier_id'])
        ) {
            redirect('supplier/dashboard');
        }

        $pendingPayment = $this->paymentModel->getLatestSupplierFeePayment((int)$supplier['supplier_id'], 'pending');

        $data = $this->supplierFeeViewData($supplier);
        $data['message'] = $_SESSION['payment_flash'] ?? $data['message'];
        unset($_SESSION['payment_flash']);

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' && $pendingPayment) {
            if (($pendingPayment['method'] ?? '') === 'KBZ Pay') {
                $data['paymentContext']['gatewayPayment'] = $this->kbzGatewayViewData($pendingPayment);
                $this->view('payments/supplier_fee', $data);
                return;
            }

            redirect('supplier/dashboard');
        }

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $method = trim($_POST['method'] ?? '');

            if ($method === '') {
                $data['message'] = 'Please choose a payment method.';
                $this->view('payments/supplier_fee', $data);
                return;
            }

            if (!$this->isAllowedMethod($method)) {
                $data['message'] = 'Please choose a valid payment method.';
                $this->view('payments/supplier_fee', $data);
                return;
            }

            if ($pendingPayment) {
                if (($pendingPayment['method'] ?? '') === 'KBZ Pay') {
                    $data['paymentContext']['gatewayPayment'] = $this->kbzGatewayViewData($pendingPayment);
                    $this->view('payments/supplier_fee', $data);
                    return;
                }

                redirect('supplier/dashboard');
            }

            if ($this->isGatewayMethod($method)) {
                $paymentId = $this->paymentModel->createSupplierFeePayment(
                    (int)$supplier['supplier_id'],
                    self::SUPPLIER_MEMBERSHIP_FEE,
                    $method,
                    null
                );

                if (!$paymentId) {
                    $data['message'] = 'We could not create your KBZ Pay payment. Please try again.';
                    $this->view('payments/supplier_fee', $data);
                    return;
                }

                $payment = $this->paymentModel->getSupplierFeePaymentById($paymentId);
                $data['message'] = 'KBZ Pay payment created. Scan the QR code with your phone and use demo PIN 123456.';
                $data['paymentContext']['gatewayPayment'] = $this->kbzGatewayViewData($payment);
                $this->view('payments/supplier_fee', $data);
                return;
            }

            if (!$this->hasUploadedPaymentSlip()) {
                $data['message'] = 'Please upload your payment slip screenshot.';
                $this->view('payments/supplier_fee', $data);
                return;
            }

            if (!$this->isValidPaymentSlip($_FILES['payment_slip'])) {
                $data['message'] = 'Please upload a valid payment slip image under 5MB.';
                $this->view('payments/supplier_fee', $data);
                return;
            }

            $slipUrl = $this->storePaymentSlip($_FILES['payment_slip'], (int)$supplier['supplier_id'], $supplier['shop_name'] ?? 'supplier');

            if (!$slipUrl) {
                $data['message'] = 'We could not upload your payment slip. Please try again.';
                $this->view('payments/supplier_fee', $data);
                return;
            }

            $paymentId = $this->paymentModel->createSupplierFeePayment(
                (int)$supplier['supplier_id'],
                self::SUPPLIER_MEMBERSHIP_FEE,
                $method,
                $slipUrl
            );

            if (!$paymentId) {
                $data['message'] = 'We could not save your payment information. Please try again.';
                $this->view('payments/supplier_fee', $data);
                return;
            }

            $this->notificationModel->notifyAdmins(
                'Supplier payment submitted',
                ($supplier['shop_name'] ?? 'A supplier') . ' uploaded an AYA Bank Transfer payment slip.',
                'payment',
                'payment',
                $paymentId
            );

            redirect('supplier/dashboard');
        }

        $this->view('payments/supplier_fee', $data);
    }

    public function kbzSupplierFeeReturn()
    {
        $paymentId = (int)($_GET['payment_id'] ?? 0);
        $status = trim($_GET['status'] ?? '');
        $transactionRef = trim($_GET['ref'] ?? '');
        $token = trim($_GET['token'] ?? '');

        if (!$paymentId || !$this->isValidGatewayToken($paymentId, $token)) {
            $_SESSION['payment_flash'] = 'We could not verify the KBZ Pay response. Please try again.';
            redirect('payments/supplierFee');
        }

        $payment = $this->paymentModel->getSupplierFeePaymentById($paymentId);
        $userId = $_SESSION['session_uid'] ?? $_SESSION['pending_register_user_id'] ?? null;
        $supplier = $userId ? $this->supplierProfileModel->getByUserId((int)$userId) : null;

        if (
            !$payment ||
            !$supplier ||
            (int)($payment['supplier_id'] ?? 0) !== (int)($supplier['supplier_id'] ?? 0) ||
            ($payment['method'] ?? '') !== 'KBZ Pay'
        ) {
            $_SESSION['payment_flash'] = 'This KBZ Pay payment could not be found.';
            redirect('payments/supplierFee');
        }

        if (($payment['status'] ?? '') !== 'pending') {
            redirect('supplier/dashboard');
        }

        if ($status === 'success' && $transactionRef !== '') {
            $this->paymentModel->updateSupplierFeeGatewaySuccess($paymentId, $transactionRef);
            $this->supplierProfileModel->updatePaymentReview(
                (int)$payment['supplier_id'],
                'paid',
                'verified',
                1,
                null
            );

            redirect('supplier/dashboard');
        }

        $this->paymentModel->updateSupplierFeeStatus($paymentId, 'failed', null);
        $this->supplierProfileModel->updatePaymentReview((int)$payment['supplier_id'], 'unpaid', null, 0, null);
        $_SESSION['payment_flash'] = 'KBZ Pay payment was not completed. You can try again or use Bank Transfer.';
        redirect('payments/supplierFee');
    }

    private function supplierFeeViewData($supplier)
    {
        return [
            'message' => '',
            'paymentContext' => [
                'eyebrow' => 'Supplier membership',
                'title' => 'Complete your partner payment',
                'intro' => 'Choose KBZ Pay for instant demo checkout, or use AYA Bank Transfer for manual slip review.',
                'amountLabel' => 'Membership fee',
                'amount' => self::SUPPLIER_MEMBERSHIP_FEE,
                'currency' => 'MMK',
                'summary' => [
                    'Business' => $supplier['shop_name'] ?? 'Supplier account',
                    'Service' => $supplier['service_name'] ?? 'Service information',
                    'Payment type' => 'Supplier fee',
                    'Review status' => ucfirst($supplier['status'] ?? 'pending'),
                ],
                'methods' => [
                    'KBZ Pay',
                    'AYA Bank Transfer',
                ],
                'action' => URLROOT . '/payments/supplierFee',
                'backUrl' => URLROOT . '/supplier/dashboard',
                'submitLabel' => 'Continue',
                'note' => 'KBZ Pay unlocks automatically after demo PIN success. AYA Bank Transfer stays pending until admin approves the uploaded slip.',
                'bankTransfer' => [
                    'Bank' => 'AYA Bank',
                    'Account name' => APPNAME,
                    'Account number' => 'AYA-001-234-567',
                    'Reference' => 'Supplier fee - ' . ($supplier['shop_name'] ?? 'Supplier'),
                ],
            ],
        ];
    }

    private function isAllowedMethod($method)
    {
        return in_array($method, ['KBZ Pay', 'AYA Bank Transfer'], true);
    }

    private function isGatewayMethod($method)
    {
        return $method === 'KBZ Pay';
    }

    private function hasUploadedPaymentSlip()
    {
        return isset($_FILES['payment_slip']) && ($_FILES['payment_slip']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
    }

    private function isValidPaymentSlip($file)
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return false;
        }

        if (($file['size'] ?? 0) > 5 * 1024 * 1024) {
            return false;
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        return in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp'], true);
    }

    private function storePaymentSlip($file, $supplierId, $businessName)
    {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        $extensions = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];
        $extension = $extensions[$mimeType] ?? null;

        if (!$extension) {
            return false;
        }

        $supplierFolder = $supplierId . '-' . $this->slugify($businessName);
        $relativeDir = 'uploads/payments/supplier-fees/' . $supplierFolder;
        $absoluteDir = dirname(APPROOT) . '/public/' . $relativeDir;

        if (!is_dir($absoluteDir) && !mkdir($absoluteDir, 0755, true)) {
            return false;
        }

        $filename = 'payment-slip-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $extension;
        $absolutePath = $absoluteDir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $absolutePath)) {
            return false;
        }

        return IMG_ROOT . '/' . $relativeDir . '/' . $filename;
    }

    private function slugify($value)
    {
        $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $value), '-'));

        return $slug !== '' ? $slug : 'supplier';
    }

    private function isValidGatewayToken($paymentId, $token)
    {
        return hash_equals($this->gatewayToken((int)$paymentId), $token);
    }

    private function gatewayToken($paymentId)
    {
        return hash('sha256', (int)$paymentId . '|' . APPNAME . '|' . DB_NAME);
    }

    private function kbzGatewayViewData($payment)
    {
        $paymentId = (int)($payment['id'] ?? 0);
        $baseUrl = defined('NETWORK_URLROOT') && NETWORK_URLROOT !== '' ? NETWORK_URLROOT : URLROOT;
        $checkoutUrl = rtrim($baseUrl, '/') . '/fakekbz/checkout/' . $paymentId . '?token=' . $this->gatewayToken($paymentId);

        return [
            'id' => $paymentId,
            'amount' => (float)($payment['amount'] ?? self::SUPPLIER_MEMBERSHIP_FEE),
            'checkoutUrl' => $checkoutUrl,
            'qrUrl' => 'https://api.qrserver.com/v1/create-qr-code/?size=260x260&margin=12&data=' . rawurlencode($checkoutUrl),
        ];
    }
}
