<?php

class Payments extends Controller
{
    private const SUPPLIER_MEMBERSHIP_FEE = 50000.00;

    private $paymentModel;
    private $supplierProfileModel;
    private $paymentGateway;

    public function __construct()
    {
        $this->paymentModel = $this->model('Payment');
        $this->supplierProfileModel = $this->model('SupplierProfile');

        require_once APPROOT . '/services/PaymentGatewayService.php';
        $this->paymentGateway = new PaymentGatewayService();
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
            $method = trim($_POST['method'] ?? '');

            if ($method === '' || !$this->isAllowedMethod($method)) {
                $data['message'] = 'Please choose a valid payment method.';
                $this->view('payments/supplier_fee', $data);
                return;
            }

            $paymentId = $this->paymentModel->createSupplierFeePayment(
                (int)$supplier['supplier_id'],
                self::SUPPLIER_MEMBERSHIP_FEE,
                $method,
                null
            );

            if (!$paymentId) {
                $data['message'] = 'We could not create your payment. Please try again.';
                $this->view('payments/supplier_fee', $data);
                return;
            }

            $returnUrl = URLROOT . '/payments/supplierFeeCallback?payment_id=' . $paymentId;
            $gatewayMethod = $this->mapPaymentMethod($method);

            $result = $this->paymentGateway->createPaymentIntent(
                $paymentId,
                self::SUPPLIER_MEMBERSHIP_FEE,
                $gatewayMethod,
                $returnUrl
            );

            if (!($result['success'] ?? false)) {
                $this->paymentModel->updateSupplierFeeStatus($paymentId, 'failed');
                $data['message'] = $result['error'] ?? 'Payment gateway error. Please try again.';
                $this->view('payments/supplier_fee', $data);
                return;
            }

            $_SESSION['payment_' . $paymentId] = [
                'intent_id' => $result['intent_id'] ?? '',
                'transaction_id' => $result['transaction_id'] ?? '',
            ];

            if (!empty($result['payment_url'] ?? false)) {
                redirect($result['payment_url']);
            } elseif (!empty($result['qr_code_url'] ?? false)) {
                $_SESSION['payment_qr_' . $paymentId] = $result['qr_code_url'];
            }

            redirect('supplier/dashboard');
        }

        $this->view('payments/supplier_fee', $data);
    }

    public function supplierFeeCallback()
    {
        $paymentId = (int)($_GET['payment_id'] ?? 0);
        $status = trim($_GET['status'] ?? 'pending');

        if (!$paymentId) {
            redirect('supplier/dashboard');
        }

        $payment = $this->paymentModel->getSupplierFeePaymentById($paymentId);

        if (!$payment) {
            redirect('supplier/dashboard');
        }

        $userId = $_SESSION['session_uid'] ?? $_SESSION['pending_register_user_id'] ?? null;
        $supplier = $userId ? $this->supplierProfileModel->getByUserId((int)$userId) : null;

        if (!$supplier || (int)($payment['supplier_id'] ?? 0) !== (int)($supplier['supplier_id'] ?? 0)) {
            redirect('supplier/dashboard');
        }

        if ($status === 'success') {
            $this->paymentModel->updateSupplierFeeStatus($paymentId, 'success');
            $this->supplierProfileModel->updatePaymentReview(
                (int)$payment['supplier_id'],
                'paid',
                'verified',
                1,
                null
            );
            $_SESSION['payment_flash'] = 'Payment successful! Your dashboard is now unlocked.';
        } else {
            $this->paymentModel->updateSupplierFeeStatus($paymentId, 'failed');
            $_SESSION['payment_flash'] = 'Payment was not completed. Please try again.';
        }

        redirect('supplier/dashboard');
    }

    private function mapPaymentMethod($method)
    {
        return match($method) {
            '2c2p_mmqr' => 'mm_qr',
            '2c2p_card' => 'credit_card',
            default => 'credit_card',
        };
    }

    private function supplierFeeViewData($supplier)
    {
        return [
            'message' => '',
            'paymentContext' => [
                'eyebrow' => 'Supplier membership',
                'title' => 'Complete your partner payment',
                'intro' => 'Choose your payment method to complete the membership fee.',
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
                    '2c2p_mmqr' => 'MM QR',
                    '2c2p_card' => 'Visa Card',
                ],
                'action' => URLROOT . '/payments/supplierFee',
                'backUrl' => URLROOT . '/supplier/dashboard',
                'submitLabel' => 'Continue',
                'note' => 'You will be redirected to complete your payment securely.',
            ],
        ];
    }

    private function isAllowedMethod($method)
    {
        return in_array($method, ['2c2p_mmqr', '2c2p_card'], true);
    }
}
