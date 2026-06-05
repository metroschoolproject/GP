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

        if (
            ($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' &&
            $this->paymentModel->hasPendingSupplierFeePayment((int)$supplier['supplier_id'])
        ) {
            redirect('supplier/dashboard');
        }

        $data = $this->supplierFeeViewData($supplier);
        $data['message'] = $_SESSION['payment_flash'] ?? $data['message'];
        unset($_SESSION['payment_flash']);

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $method = trim($_POST['method'] ?? '');
            $transactionRef = trim($_POST['transaction_ref'] ?? '');

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

            if ($this->isGatewayMethod($method)) {
                $payment = $this->paymentModel->getLatestSupplierFeePayment((int)$supplier['supplier_id'], 'pending');

                if ($payment && ($payment['method'] ?? '') === $method) {
                    redirect('fakekbz/checkout/' . (int)$payment['id']);
                }

                if (!$payment) {
                    $paymentId = $this->paymentModel->createSupplierFeePayment(
                        (int)$supplier['supplier_id'],
                        self::SUPPLIER_MEMBERSHIP_FEE,
                        $method
                    );

                    if (!$paymentId) {
                        $data['message'] = 'We could not start your payment. Please try again.';
                        $this->view('payments/supplier_fee', $data);
                        return;
                    }

                    redirect('fakekbz/checkout/' . $paymentId);
                }

                redirect('supplier/dashboard');
            }

            if ($transactionRef === '') {
                $data['message'] = 'Please enter your bank transfer transaction reference.';
                $this->view('payments/supplier_fee', $data);
                return;
            }

            if (!$this->paymentModel->hasPendingSupplierFeePayment((int)$supplier['supplier_id'])) {
                $paymentId = $this->paymentModel->createSupplierFeePayment(
                    (int)$supplier['supplier_id'],
                    self::SUPPLIER_MEMBERSHIP_FEE,
                    $method,
                    $transactionRef
                );

                if (!$paymentId) {
                    $data['message'] = 'We could not save your payment information. Please try again.';
                    $this->view('payments/supplier_fee', $data);
                    return;
                }

                $this->notificationModel->notifyAdmins(
                    'Supplier payment submitted',
                    ($supplier['shop_name'] ?? 'A supplier') . ' submitted membership payment details.',
                    'payment',
                    'payment',
                    $paymentId
                );
            }

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
                'intro' => 'Submit the membership fee transaction details so admin can verify your payment and unlock the dashboard.',
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
                    'Bank Transfer',
                ],
                'action' => URLROOT . '/payments/supplierFee',
                'backUrl' => URLROOT . '/supplier/dashboard',
                'submitLabel' => 'Submit payment for review',
                'note' => 'Your dashboard stays locked until admin verifies this payment.',
            ],
        ];
    }

    private function isAllowedMethod($method)
    {
        return in_array($method, ['KBZ Pay', 'Bank Transfer'], true);
    }

    private function isGatewayMethod($method)
    {
        return $method === 'KBZ Pay';
    }

    private function isValidGatewayToken($paymentId, $token)
    {
        return hash_equals($this->gatewayToken((int)$paymentId), $token);
    }

    private function gatewayToken($paymentId)
    {
        return hash('sha256', (int)$paymentId . '|' . session_id() . '|' . APPNAME);
    }
}
