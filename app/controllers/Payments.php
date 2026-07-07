<?php

class Payments extends Controller
{
    // Membership fee: fallback value; override via platform_settings key supplier_membership_fee
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

    private function getMembershipFee(): float
    {
        return (float)get_platform_setting('supplier_membership_fee', (string)self::SUPPLIER_MEMBERSHIP_FEE);
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

        if ($pendingPayment && ($pendingPayment['status'] ?? '') === 'pending') {
            redirect('supplier/dashboard');
        }

        $data = $this->supplierFeeViewData($supplier);
        $data['message'] = $_SESSION['payment_flash'] ?? $data['message'];
        unset($_SESSION['payment_flash']);

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $bankName     = trim($_POST['bank_name'] ?? '');
            $accountName  = trim($_POST['account_name'] ?? '');
            $transactionRef = trim($_POST['transaction_ref'] ?? '');
            $expectedFee = $this->getMembershipFee();
            $paidAmount   = $expectedFee;
            $paidAt       = trim($_POST['paid_at'] ?? '');
            $mobileNumber = trim($_POST['mobile_number'] ?? '');
            $remark       = trim($_POST['remark'] ?? '');

            if (
                !$this->isAllowedMethod($bankName)
                || $accountName === ''
                || $transactionRef === ''
                || $expectedFee <= 0
                || $paidAt === ''
                || $mobileNumber === ''
            ) {
                $data['message'] = 'Please fill in all required fields.';
                $this->view('payments/supplier_fee', $data);
                return;
            }

            $slipPath = '';
            if (!empty($_FILES['slip_image']['name']) && ($_FILES['slip_image']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
                $slipPath = $this->storePaymentSlip($_FILES['slip_image']);
            }

            $paymentId = $this->paymentModel->submitManualSupplierFeePayment(
                (int)$supplier['supplier_id'],
                $expectedFee,
                $bankName,
                $accountName,
                $mobileNumber,
                $transactionRef,
                $paidAmount,
                $paidAt,
                $slipPath,
                $remark
            );

            if (!$paymentId) {
                $data['message'] = 'Could not save your payment proof. Please try again.';
                $this->view('payments/supplier_fee', $data);
                return;
            }

            $this->notificationModel->notifyAdmins(
                'New Supplier Fee Submitted',
                ($supplier['shop_name'] ?? 'A supplier') . ' has submitted a supplier membership fee payment. Please review.',
                'payment',
                'supplier',
                (int)$supplier['supplier_id']
            );

            $_SESSION['payment_flash'] = 'Your payment proof has been submitted. Admin will verify and unlock your dashboard shortly.';
            redirect('supplier/dashboard');
        }

        $this->view('payments/supplier_fee', $data);
    }

    private function storePaymentSlip(array $file): string
    {
        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];
        $mimeType = mime_content_type($file['tmp_name']);

        if (!in_array($mimeType, $allowed, true) || $file['size'] > 5 * 1024 * 1024) {
            return '';
        }

        $extMap = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'application/pdf' => 'pdf'];
        $ext = $extMap[$mimeType] ?? 'jpg';
        $relDir = 'uploads/payment-slips/' . date('Y/m');
        $absDir = dirname(APPROOT) . '/public/' . $relDir;

        if (!is_dir($absDir)) {
            mkdir($absDir, 0755, true);
        }

        $filename = 'slip-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $ext;

        if (move_uploaded_file($file['tmp_name'], $absDir . '/' . $filename)) {
            return 'public/' . $relDir . '/' . $filename;
        }

        return '';
    }

    private function supplierFeeViewData($supplier)
    {
        $fee = $this->getMembershipFee();
        return [
            'message' => '',
            'paymentContext' => [
                'eyebrow' => 'Supplier membership',
                'title' => 'Complete your partner payment',
                'intro' => 'Transfer ' . number_format($fee, 0) . ' MMK to our account, then fill in the form below with your transfer details. Admin will verify and unlock your dashboard.',
                'amountLabel' => 'Membership fee',
                'amount' => $fee,
                'currency' => 'MMK',
                'summary' => [
                    'Business' => $supplier['shop_name'] ?? 'Supplier account',
                    'Service' => $supplier['service_name'] ?? 'Service information',
                    'Payment type' => 'Supplier fee (one-time)',
                    'Review status' => ucfirst($supplier['status'] ?? 'pending'),
                ],
                'action' => URLROOT . '/payments/supplierFee',
                'backUrl' => URLROOT . '/supplier/dashboard',
            ],
        ];
    }

    private function isAllowedMethod($method)
    {
        return in_array($method, ['KBZ Pay', 'Wave Money', 'AYA Pay', 'Yoma Bank', 'CB Bank', 'Visa / MasterCard'], true);
    }
}
