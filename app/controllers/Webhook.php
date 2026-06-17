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
        $payload = trim((string)($data['payload'] ?? $_POST['payload'] ?? ''));

        if ($payload === '') {
            http_response_code(400);
            exit('Invalid payload');
        }

        $gatewayResponse = $this->paymentGateway->decodeGatewayPayload($payload);
        if (!$gatewayResponse) {
            http_response_code(401);
            exit('Unauthorized');
        }

        $paymentId = (int)($_GET['payment_id'] ?? 0);
        $transactionRef = $gatewayResponse['tranRef']
            ?? $gatewayResponse['transactionID']
            ?? $gatewayResponse['approvalCode']
            ?? ($gatewayResponse['invoiceNo'] ?? '');

        if (!$paymentId) {
            http_response_code(400);
            exit('Invalid reference');
        }

        $payment = $this->paymentModel->getSupplierFeePaymentById($paymentId);

        if (!$payment) {
            http_response_code(404);
            exit('Payment not found');
        }

        $paymentStatus = ($gatewayResponse['respCode'] ?? '') === '0000' ? 'success' : 'failed';

        if ($paymentStatus === 'success') {
            $this->paymentModel->updateSupplierFeeGatewaySuccess($paymentId, (string)$transactionRef);
        } else {
            $this->paymentModel->updateSupplierFeeStatus($paymentId, 'failed');
        }

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
