<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * PaymentGatewayService
 * Abstraction layer for payment gateway integrations (2C2P, MPAY, etc.)
 * Supports both test/sandbox and live modes
 */
class PaymentGatewayService
{
    private $apiSecret;
    private $merchantId;
    private $baseUrl;
    private $isSandbox;

    public function __construct()
    {
        $this->isSandbox = defined('PAYMENT_GATEWAY_SANDBOX') ? PAYMENT_GATEWAY_SANDBOX : true;
        $this->apiSecret = defined('PAYMENT_GATEWAY_SECRET') ? PAYMENT_GATEWAY_SECRET : '';
        $this->merchantId = defined('MERCHANT_ID') ? MERCHANT_ID : '';
        $this->baseUrl = $this->isSandbox
            ? 'https://sandbox-pgw.2c2p.com'
            : 'https://pgw.2c2p.com';
    }

    public function isConfigured(): bool
    {
        return $this->merchantId !== '' && $this->apiSecret !== '';
    }

    /**
     * Create a 2C2P hosted payment page token.
     * Returns webPaymentUrl for redirect checkout when successful.
     */
    public function createPaymentIntent(int $paymentId, float $amount, string $method, string $returnUrl, ?string $backendReturnUrl = null): array
    {
        if ($this->merchantId === '' || $this->apiSecret === '') {
            return [
                'success' => false,
                'error' => '2C2P sandbox credentials are missing. Check MERCHANT_ID and PAYMENT_GATEWAY_SECRET.',
            ];
        }

        $invoiceNo = $this->buildInvoiceNo($paymentId);
        $currency = defined('PAYMENT_GATEWAY_CURRENCY') ? PAYMENT_GATEWAY_CURRENCY : 'MMK';
        $paymentChannels = $this->paymentChannelsForMethod($method);

        $payload = [
            'merchantID' => $this->merchantId,
            'invoiceNo' => $invoiceNo,
            'description' => 'Golden Promise Payment #' . $paymentId,
            'amount' => round($amount, 2),
            'currencyCode' => $currency,
            'frontendReturnUrl' => $returnUrl,
            'backendReturnUrl' => $backendReturnUrl ?: URLROOT . '/webhook/paymentGatewayCallback?payment_id=' . $paymentId,
            'nonceStr' => bin2hex(random_bytes(16)),
        ];

        if (!empty($paymentChannels)) {
            $payload['paymentChannel'] = $paymentChannels;
        }

        $response = $this->postJwt('/payment/4.3/paymentToken', $payload);

        if (!$response) {
            return [
                'success' => false,
                'error' => 'Payment gateway unavailable. Please try again later.',
            ];
        }

        if (($response['respCode'] ?? '') === '0000' && !empty($response['webPaymentUrl'])) {
            return [
                'success' => true,
                'invoice_no' => $invoiceNo,
                'payment_token' => $response['paymentToken'] ?? '',
                'payment_url' => $response['webPaymentUrl'],
                'response' => $response,
            ];
        }

        $respCode = (string)($response['respCode'] ?? '');
        if ($respCode === '9007') {
            return [
                'success' => false,
                'error' => 'Payment gateway rejected the setup request. Please check the 2C2P merchant ID, secret key, enabled currency, and payment channel in config.php.',
                'response' => $response,
            ];
        }

        return [
            'success' => false,
            'error' => $response['respDesc'] ?? 'Payment gateway error',
            'response' => $response,
        ];
    }

    public function decodeGatewayPayload(string $jwt): array|false
    {
        try {
            return (array)JWT::decode($jwt, new Key($this->apiSecret, 'HS256'));
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Verify transaction with payment gateway.
     * Returns success/failure and transaction details.
     */
    public function verifyTransaction(string $transactionId): array
    {
        $payload = [
            'merchantID' => $this->merchantId,
            'transactionID' => $transactionId,
        ];

        $response = $this->postJwt('/payment/4.3/paymentInquiry', $payload);

        if (!$response) {
            return ['success' => false, 'error' => 'Gateway verification failed'];
        }

        $responseCode = (string)($response['respCode'] ?? '');
        $status = (string)($response['status'] ?? $response['paymentStatus'] ?? $response['transactionStatus'] ?? '');
        $success = ($response['success'] ?? false)
            || $responseCode === '0000'
            || in_array(strtolower($status), ['success', 'paid', 'completed'], true);

        return [
            'success' => $success,
            'status' => $status !== '' ? $status : $responseCode,
            'amount' => $response['amount'] ?? $response['paidAmount'] ?? 0,
            'method' => $response['method'] ?? $response['paymentChannel'] ?? '',
            'verified_at' => $response['timestamp'] ?? '',
        ];
    }

    public function methodMatches(string $expectedMethod, mixed $gatewayMethod): bool
    {
        $expected = strtolower(trim($expectedMethod));
        $actual = strtolower(trim(is_array($gatewayMethod) ? implode(' ', $gatewayMethod) : (string)$gatewayMethod));
        if ($actual === '') {
            return false;
        }

        $aliases = [
            'mm qr' => ['mm qr', 'mmqr', 'qr', 'promptpay'],
            'visa' => ['visa', 'card', 'credit card'],
            'mastercard' => ['mastercard', 'master card', 'card', 'credit card'],
        ];
        foreach ($aliases[$expected] ?? [$expected] as $alias) {
            if ($alias !== '' && str_contains($actual, $alias)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Request refund from payment gateway.
     * Used when customer cancels booking within refund window.
     */
    public function requestRefund(string $originalTransactionId, float $refundAmount): array
    {
        $payload = [
            'original_transaction_id' => $originalTransactionId,
            'refund_amount' => (int)round($refundAmount),
            'reason' => 'Booking cancellation',
        ];

        $response = $this->httpPost('/refund', $payload);

        if (!$response) {
            return ['success' => false, 'error' => 'Refund request failed'];
        }

        return [
            'success' => $response['success'] ?? false,
            'refund_id' => $response['refund_id'] ?? '',
            'status' => $response['status'] ?? 'pending',
            'message' => $response['message'] ?? '',
        ];
    }

    /**
     * Create payout to supplier bank account.
     * Gateway handles settlement/disbursement.
     */
    public function createSupplierPayout(
        int $supplierId,
        float $amount,
        string $bankAccount,
        string $bankCode,
        string $batchId = ''
    ): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'error' => 'Payout gateway credentials are not configured.'];
        }

        $payload = [
            'supplier_id' => (string)$supplierId,
            'amount' => (int)round($amount),
            'currency' => 'MMK',
            'bank_code' => $bankCode, // 'AYA', 'KBZ', 'AGD', etc.
            'account_number' => $bankAccount,
            'description' => 'Golden Promise - Booking Payout #' . $supplierId,
            'merchant_reference' => $batchId,
            'notification_url' => URLROOT . '/webhook/payoutCallback?batch_id=' . rawurlencode($batchId),
        ];

        $response = $this->httpPost('/payout/create', $payload);

        if (!$response) {
            return ['success' => false, 'error' => 'Payout creation failed'];
        }

        return [
            'success' => $response['success'] ?? false,
            'payout_id' => $response['payout_id'] ?? '',
            'status' => $response['status'] ?? 'pending',
            'reference' => $response['reference'] ?? '',
        ];
    }

    /**
     * Get transaction history for reconciliation.
     */
    public function getTransactionHistory(int $limit = 100): array
    {
        $payload = [
            'merchantID' => $this->merchantId,
            'limit' => $limit,
        ];

        $response = $this->httpPost('/transactions/list', $payload);

        if (!$response) {
            return [];
        }

        return $response['transactions'] ?? [];
    }

    /**
     * Make HTTP POST request to gateway API.
     */
    private function postJwt(string $endpoint, array $data): array|false
    {
        $jwt = JWT::encode($data, $this->apiSecret, 'HS256');
        $response = $this->httpPost($endpoint, ['payload' => $jwt]);

        if (!$response) {
            return false;
        }

        if (empty($response['payload'])) {
            return $response;
        }

        return $this->decodeGatewayPayload((string)$response['payload']);
    }

    private function httpPost(string $endpoint, array $data): array|false
    {
        if (!function_exists('curl_init')) {
            return false;
        }

        $ch = curl_init($this->baseUrl . $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            return json_decode($response, true) ?: false;
        }

        return false;
    }

    /**
     * Validate webhook signature (security: prevent spoofing).
     */
    public function validateWebhookSignature(string $payload, string $signature): bool
    {
        $expectedSignature = hash_hmac('sha256', $payload, $this->apiSecret, true);
        $expectedSignature = base64_encode($expectedSignature);

        return hash_equals($signature, $expectedSignature);
    }

    /**
     * Format amount for gateway (handle currency/decimal differences).
     */
    public function formatAmount(float $amount): int
    {
        // MMK typically doesn't use decimals, convert to integer
        return (int)round($amount);
    }

    private function buildInvoiceNo(int $paymentId): string
    {
        return 'GP' . str_pad((string)$paymentId, 10, '0', STR_PAD_LEFT);
    }

    private function paymentChannelsForMethod(string $method): array
    {
        if ($method === 'mm_qr') {
            $channel = defined('PAYMENT_GATEWAY_MMQR_CHANNEL') ? trim((string)PAYMENT_GATEWAY_MMQR_CHANNEL) : '';
            return $channel !== '' ? [$channel] : [];
        }

        $channel = defined('PAYMENT_GATEWAY_CARD_CHANNEL') ? trim((string)PAYMENT_GATEWAY_CARD_CHANNEL) : 'CC';
        return $channel !== '' ? [$channel] : [];
    }
}
