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

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $method = trim($_POST['method'] ?? '');
            $transactionRef = trim($_POST['transaction_ref'] ?? '');

            if ($method === '' || $transactionRef === '') {
                $data['message'] = 'Please choose a payment method and enter your transaction reference.';
                $this->view('payments/supplier_fee', $data);
                return;
            }

            if (!$this->isAllowedMethod($method)) {
                $data['message'] = 'Please choose a valid payment method.';
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
                    'Wave Pay',
                    'AYA Pay',
                    'CB Pay',
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
        return in_array($method, ['KBZ Pay', 'Wave Pay', 'AYA Pay', 'CB Pay', 'Bank Transfer'], true);
    }
}
