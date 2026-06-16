<?php

class Webhook extends Controller
{
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

    public function paymentGatewayCallback()
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (!$data) {
            http_response_code(400);
            exit('Invalid payload');
        }

        $signature = $_SERVER['HTTP_X_2C2P_SIGNATURE'] ?? '';

        if (!$this->paymentGateway->validateWebhookSignature($input, $signature)) {
            http_response_code(401);
            exit('Unauthorized');
        }

        $paymentId = (int)str_replace('BOOKING_', '', $data['reference_id'] ?? '');
        $status = strtolower($data['status'] ?? 'pending');
        $transactionRef = $data['transaction_id'] ?? '';

        if (!$paymentId) {
            http_response_code(400);
            exit('Invalid reference');
        }

        $payment = $this->paymentModel->getSupplierFeePaymentById($paymentId);

        if (!$payment) {
            http_response_code(404);
            exit('Payment not found');
        }

        $paymentStatus = match($status) {
            'success', 'completed' => 'success',
            'failed' => 'failed',
            'cancelled' => 'failed',
            'pending' => 'pending',
            default => 'pending',
        };

        $this->paymentModel->updateSupplierFeeStatus($paymentId, $paymentStatus);

        if ($paymentStatus === 'success' && (int)($payment['supplier_id'] ?? 0) > 0) {
            $supplier = $this->supplierProfileModel->getById((int)$payment['supplier_id']);
            if ($supplier) {
                $this->supplierProfileModel->updatePaymentReview(
                    (int)$payment['supplier_id'],
                    'paid',
                    'verified',
                    1,
                    null
                );
            }
        }

        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Webhook processed']);
    }

    public function payoutCallback()
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (!$data) {
            http_response_code(400);
            exit('Invalid payload');
        }

        $signature = $_SERVER['HTTP_X_2C2P_SIGNATURE'] ?? '';

        if (!$this->paymentGateway->validateWebhookSignature($input, $signature)) {
            http_response_code(401);
            exit('Unauthorized');
        }

        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Payout webhook processed']);
    }
}
