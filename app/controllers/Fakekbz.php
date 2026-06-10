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
        $token = trim($_GET['token'] ?? '');

        if (!$paymentId || !$this->isValidGatewayToken($paymentId, $token)) {
            redirect('payments/supplierFee');
        }

        $payment = $this->paymentModel->getSupplierFeePaymentById($paymentId);
        $supplier = $payment ? $this->supplierProfileModel->getApplicationById((int)$payment['supplier_id']) : null;

        if (
            !$payment ||
            !$supplier ||
            ($payment['method'] ?? '') !== 'KBZ Pay'
        ) {
            redirect('supplier/dashboard');
        }

        $completedStatus = trim($_GET['done'] ?? '');
        if (($payment['status'] ?? '') !== 'pending' && $completedStatus === '') {
            $completedStatus = ($payment['status'] ?? '') === 'success' ? 'success' : 'failed';
        }

        $this->view('fakekbz/checkout', [
            'payment' => $payment,
            'supplier' => $supplier,
            'completedStatus' => $completedStatus,
            'successUrl' => $this->currentBaseUrl() . '/fakekbz/complete/' . $paymentId . '?status=success&token=' . $token,
            'failUrl' => $this->currentBaseUrl() . '/fakekbz/complete/' . $paymentId . '?status=failed&token=' . $token,
            'returnUrl' => $this->currentBaseUrl() . '/supplier/dashboard',
        ]);
    }

    public function complete($paymentId = null)
    {
        $paymentId = (int)$paymentId;
        $token = trim($_GET['token'] ?? '');
        $status = trim($_GET['status'] ?? '');

        if (!$paymentId || !$this->isValidGatewayToken($paymentId, $token)) {
            redirect('payments/supplierFee');
        }

        $payment = $this->paymentModel->getSupplierFeePaymentById($paymentId);

        if (!$payment || ($payment['method'] ?? '') !== 'KBZ Pay') {
            redirect('supplier/dashboard');
        }

        if (($payment['status'] ?? '') === 'pending') {
            if ($status === 'success') {
                $transactionRef = 'KBZ-DEMO-' . date('YmdHis') . '-' . $paymentId;
                $this->paymentModel->updateSupplierFeeGatewaySuccess($paymentId, $transactionRef);
                $this->supplierProfileModel->updatePaymentReview((int)$payment['supplier_id'], 'paid', 'verified', 1, null);
            } else {
                $this->paymentModel->updateSupplierFeeStatus($paymentId, 'failed', null);
                $this->supplierProfileModel->updatePaymentReview((int)$payment['supplier_id'], 'unpaid', null, 0, null);
            }
        }

        header('Location: ' . $this->currentBaseUrl() . '/fakekbz/checkout/' . $paymentId . '?token=' . $token . '&done=' . ($status === 'success' ? 'success' : 'failed'));
        exit;
    }

    private function gatewayToken($paymentId)
    {
        return hash('sha256', (int)$paymentId . '|' . APPNAME . '|' . DB_NAME);
    }

    private function isValidGatewayToken($paymentId, $token)
    {
        return hash_equals($this->gatewayToken((int)$paymentId), $token);
    }

    private function currentBaseUrl()
    {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? parse_url(URLROOT, PHP_URL_HOST);
        $path = rtrim(parse_url(URLROOT, PHP_URL_PATH) ?: '', '/');

        return $scheme . '://' . $host . $path;
    }
}
