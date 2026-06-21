<?php

require_once APPROOT . '/services/PaymentGatewayService.php';

class PayoutService
{
    private Database $db;
    private PaymentGatewayService $gateway;

    public function __construct(?PaymentGatewayService $gateway = null)
    {
        $this->db = new Database();
        $this->gateway = $gateway ?? new PaymentGatewayService();
    }

    public function requestAvailableBalance(
        int $supplierId,
        string $bankAccount,
        string $bankCode,
        float $requestedAmount
    ): array {
        if (!$this->gateway->isConfigured()) {
            return ['success' => false, 'error' => 'Payout gateway credentials are not configured.'];
        }

        $batchId = 'PO-' . date('YmdHis') . '-' . $supplierId . '-' . bin2hex(random_bytes(4));

        try {
            $this->db->beginTransaction();

            $this->db->dbquery(
                "SELECT COALESCE(SUM(amount), 0) AS available_amount, COUNT(*) AS payout_count
                   FROM payments
                  WHERE supplier_id = :sid
                    AND type = 'payout'
                    AND status = 'pending'
                  FOR UPDATE"
            );
            $this->db->dbbind(':sid', $supplierId, PDO::PARAM_INT);
            $available = $this->db->getsingledata() ?: [];
            $availableAmount = round((float)($available['available_amount'] ?? 0), 2);

            if ($availableAmount <= 0) {
                $this->db->rollBack();
                return ['success' => false, 'error' => 'No payout balance is currently available.'];
            }

            if (abs(round($requestedAmount, 2) - $availableAmount) > 0.01) {
                $this->db->rollBack();
                return [
                    'success' => false,
                    'error' => 'The available balance changed. Refresh the page and try again.',
                    'available_amount' => $availableAmount,
                ];
            }

            $this->db->dbquery(
                'UPDATE suppliers
                    SET bank_account = :account, bank_code = :bank
                  WHERE supplier_id = :sid'
            );
            $this->db->dbbind(':account', $bankAccount);
            $this->db->dbbind(':bank', $bankCode);
            $this->db->dbbind(':sid', $supplierId, PDO::PARAM_INT);
            $this->db->dbexecute();

            $this->db->dbquery(
                "UPDATE payments
                    SET status = 'processing',
                        payout_batch_id = :batch,
                        payout_requested_at = NOW()
                  WHERE supplier_id = :sid
                    AND type = 'payout'
                    AND status = 'pending'"
            );
            $this->db->dbbind(':batch', $batchId);
            $this->db->dbbind(':sid', $supplierId, PDO::PARAM_INT);
            $this->db->dbexecute();

            if ($this->db->rowcount() !== (int)($available['payout_count'] ?? 0)) {
                throw new RuntimeException('The payout balance changed while the request was being created.');
            }

            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            error_log('Payout request preparation failed: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Could not prepare the payout request.'];
        }

        $gatewayResult = $this->gateway->createSupplierPayout(
            $supplierId,
            $availableAmount,
            $bankAccount,
            $bankCode,
            $batchId
        );

        if (empty($gatewayResult['success'])) {
            $this->restoreBatchToPending($batchId);
            return [
                'success' => false,
                'error' => $gatewayResult['error'] ?? 'The payout provider rejected the request.',
            ];
        }

        $providerReference = trim((string)(
            $gatewayResult['payout_id']
            ?? $gatewayResult['reference']
            ?? ''
        ));
        $this->db->dbquery(
            'UPDATE payments
                SET payout_provider_ref = :provider_ref
              WHERE payout_batch_id = :batch
                AND status = :status'
        );
        $this->db->dbbind(':provider_ref', $providerReference !== '' ? $providerReference : null);
        $this->db->dbbind(':batch', $batchId);
        $this->db->dbbind(':status', 'processing');
        $this->db->dbexecute();

        return [
            'success' => true,
            'batch_id' => $batchId,
            'provider_reference' => $providerReference,
            'amount' => $availableAmount,
        ];
    }

    public function completeBatch(string $batchId, bool $successful, string $providerReference = ''): bool
    {
        if ($batchId === '') {
            return false;
        }

        $this->db->dbquery(
            "UPDATE payments
                SET status = :status,
                    payout_provider_ref = COALESCE(NULLIF(:provider_ref, ''), payout_provider_ref),
                    verified_at = NOW()
              WHERE payout_batch_id = :batch
                AND type = 'payout'
                AND status = 'processing'"
        );
        $this->db->dbbind(':status', $successful ? 'success' : 'failed');
        $this->db->dbbind(':provider_ref', $providerReference);
        $this->db->dbbind(':batch', $batchId);

        return $this->db->dbexecute();
    }

    private function restoreBatchToPending(string $batchId): void
    {
        $this->db->dbquery(
            "UPDATE payments
                SET status = 'pending',
                    payout_batch_id = NULL,
                    payout_provider_ref = NULL,
                    payout_requested_at = NULL
              WHERE payout_batch_id = :batch
                AND type = 'payout'
                AND status = 'processing'"
        );
        $this->db->dbbind(':batch', $batchId);
        $this->db->dbexecute();
    }
}
