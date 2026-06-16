<?php

class Payments extends Controller
{
    private const SUPPLIER_MEMBERSHIP_FEE = 50000.00;

    private $paymentModel;
    private $supplierProfileModel;

    public function __construct()
    {
        $this->paymentModel = $this->model('Payment');
        $this->supplierProfileModel = $this->model('SupplierProfile');
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

            redirect('supplier/dashboard');
        }

        $this->view('payments/supplier_fee', $data);
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
