<?php

class Fakekbz extends Controller
{
    private $paymentModel;
    private $supplierProfileModel;

    public function __construct()
    {
        $this->paymentModel = $this->model('Payment');
        $this->supplierProfileModel = $this->model('SupplierProfile');
    }

    public function checkout($paymentId = null)
    {
        $paymentId = (int)$paymentId;
        $userId = $_SESSION['session_uid'] ?? $_SESSION['pending_register_user_id'] ?? null;

        if (!$paymentId || !$userId) {
            redirect('payments/supplierFee');
        }

        $payment = $this->paymentModel->getSupplierFeePaymentById($paymentId);
        $supplier = $this->supplierProfileModel->getByUserId((int)$userId);

        if (
            !$payment ||
            !$supplier ||
            (int)($payment['supplier_id'] ?? 0) !== (int)($supplier['supplier_id'] ?? 0) ||
            ($payment['method'] ?? '') !== 'KBZ Pay' ||
            ($payment['status'] ?? '') !== 'pending'
        ) {
            redirect('supplier/dashboard');
        }

        $token = $this->gatewayToken($paymentId);
        $transactionRef = 'KBZ-DEMO-' . date('YmdHis') . '-' . $paymentId;

        $this->view('fakekbz/checkout', [
            'payment' => $payment,
            'supplier' => $supplier,
            'successUrl' => URLROOT . '/payments/kbzSupplierFeeReturn?payment_id=' . $paymentId . '&status=success&ref=' . rawurlencode($transactionRef) . '&token=' . $token,
            'failUrl' => URLROOT . '/payments/kbzSupplierFeeReturn?payment_id=' . $paymentId . '&status=failed&token=' . $token,
        ]);
    }

    private function gatewayToken($paymentId)
    {
        return hash('sha256', (int)$paymentId . '|' . session_id() . '|' . APPNAME);
    }
}
