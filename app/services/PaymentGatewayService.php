<?php

/**
 * PaymentGatewayService
 * Abstraction layer for payment gateway integrations (2C2P, MPAY, etc.)
 * Supports both test/sandbox and live modes
 */
class PaymentGatewayService
{
    private $apiKey;
    private $apiSecret;
    private $merchantId;
    private $apiUrl;
    private $isSandbox;

    public function __construct()
    {
        $this->isSandbox = defined('PAYMENT_GATEWAY_SANDBOX') ? PAYMENT_GATEWAY_SANDBOX : true;
        $this->apiKey = defined('PAYMENT_GATEWAY_API_KEY') ? PAYMENT_GATEWAY_API_KEY : '';
        $this->apiSecret = defined('PAYMENT_GATEWAY_SECRET') ? PAYMENT_GATEWAY_SECRET : '';
        $this->merchantId = defined('MERCHANT_ID') ? MERCHANT_ID : '';

        if ($this->isSandbox) {
            $this->apiUrl = 'https://sandbox.2c2p.com/api'; // Example sandbox
        } else {
            $this->apiUrl = 'https://api.2c2p.com/api'; // Example live
        }
    }

    /**
     * Create payment intent for instant methods (MM QR, Card, etc.)
     * Returns array with intent_id, qr_code_url, or error
     */
    public function createPaymentIntent(int $bookingId, float $amount, string $method, string $returnUrl): array
    {
        $payload = [
            'api_key' => $this->apiKey,
            'merchant_id' => $this->merchantId,
            'reference_id' => 'BOOKING_' . $bookingId,
            'amount' => (int)round($amount), // Gateway expects integer amount
            'currency' => 'MMK',
            'method' => $method, // 'credit_card', 'mm_qr', etc.
            'description' => 'Golden Promise Booking #' . $bookingId,
            'return_url' => $returnUrl,
            'cancel_url' => $returnUrl,
            'notification_url' => url('/webhook/paymentGatewayCallback'),
        ];

        $response = $this->httpPost('/payment/intent', $payload);

        if (!$response || !isset($response['success'])) {
            return [
                'success' => false,
                'error' => 'Payment gateway unavailable. Please try again later.',
            ];
        }

        if ($response['success']) {
            return [
                'success' => true,
                'intent_id' => $response['intent_id'] ?? '',
                'qr_code_url' => $response['qr_code'] ?? '',
                'transaction_id' => $response['transaction_id'] ?? '',
            ];
        }

        return [
            'success' => false,
            'error' => $response['message'] ?? 'Payment gateway error',
        ];
    }

    /**
     * Verify transaction with payment gateway.
     * Returns success/failure and transaction details.
     */
    public function verifyTransaction(string $transactionId): array
    {
        $payload = [
            'api_key' => $this->apiKey,
            'transaction_id' => $transactionId,
        ];

        $response = $this->httpPost('/payment/verify', $payload);

        if (!$response) {
            return ['success' => false, 'error' => 'Gateway verification failed'];
        }

        return [
            'success' => $response['success'] ?? false,
            'status' => $response['status'] ?? '',
            'amount' => $response['amount'] ?? 0,
            'method' => $response['method'] ?? '',
            'verified_at' => $response['timestamp'] ?? '',
        ];
    }

    /**
     * Request refund from payment gateway.
     * Used when customer cancels booking within refund window.
     */
    public function requestRefund(string $originalTransactionId, float $refundAmount): array
    {
        $payload = [
            'api_key' => $this->apiKey,
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
    public function createSupplierPayout(int $supplierId, float $amount, string $bankAccount, string $bankCode): array
    {
        $payload = [
            'api_key' => $this->apiKey,
            'supplier_id' => (string)$supplierId,
            'amount' => (int)round($amount),
            'currency' => 'MMK',
            'bank_code' => $bankCode, // 'AYA', 'KBZ', 'AGD', etc.
            'account_number' => $bankAccount,
            'description' => 'Golden Promise - Booking Payout #' . $supplierId,
            'notification_url' => url('/webhook/payoutCallback'),
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
            'api_key' => $this->apiKey,
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
    private function httpPost(string $endpoint, array $data): array|false
    {
        if (!function_exists('curl_init')) {
            return false;
        }

        $ch = curl_init($this->apiUrl . $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiSecret,
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, !$this->isSandbox); // Skip SSL in sandbox

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
}
