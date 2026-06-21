<?php

require_once APPROOT . '/services/PaymentGatewayService.php';
require_once APPROOT . '/services/PayoutService.php';

class Webhook extends Controller
{
    public function paymentGatewayCallback()
    {
        http_response_code(410);
        echo json_encode(['error' => 'Gateway webhooks are not used in the manual payment flow.']);
    }

    public function payoutCallback()
    {
        header('Content-Type: application/json');

        $rawBody = file_get_contents('php://input') ?: '';
        $body = json_decode($rawBody, true) ?: [];
        $gateway = new PaymentGatewayService();

        if (!$gateway->isConfigured()) {
            http_response_code(503);
            echo json_encode(['error' => 'Payout gateway is not configured.']);
            return;
        }

        if (!empty($body['payload'])) {
            $decoded = $gateway->decodeGatewayPayload((string)$body['payload']);
            if ($decoded === false) {
                http_response_code(401);
                echo json_encode(['error' => 'Invalid payout callback signature.']);
                return;
            }
            $body = $decoded;
        } else {
            $signature = (string)($_SERVER['HTTP_X_SIGNATURE'] ?? '');
            if ($signature === '' || !$gateway->validateWebhookSignature($rawBody, $signature)) {
                http_response_code(401);
                echo json_encode(['error' => 'Invalid payout callback signature.']);
                return;
            }
        }

        $batchId = trim((string)(
            $_GET['batch_id']
            ?? $body['merchant_reference']
            ?? $body['merchantReference']
            ?? ''
        ));
        $providerReference = trim((string)(
            $body['payout_id']
            ?? $body['transactionID']
            ?? $body['reference']
            ?? ''
        ));
        $status = strtolower(trim((string)(
            $body['status']
            ?? $body['payoutStatus']
            ?? $body['respCode']
            ?? ''
        )));
        $successful = in_array($status, ['success', 'paid', 'completed', '0000'], true);
        $failed = in_array($status, ['failed', 'rejected', 'cancelled', 'canceled'], true);

        if ($batchId === '' || (!$successful && !$failed)) {
            http_response_code(422);
            echo json_encode(['error' => 'Incomplete payout callback payload.']);
            return;
        }

        $updated = (new PayoutService($gateway))->completeBatch(
            $batchId,
            $successful,
            $providerReference
        );

        http_response_code($updated ? 200 : 409);
        echo json_encode(['success' => $updated]);
    }
}
