<?php

class Payment
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function createSupplierFeePayment($supplierId, $amount, $method, $transactionRef = null)
    {
        $this->db->dbquery(
            'INSERT INTO payments(
                supplier_id,
                amount,
                platform_fee,
                supplier_amount,
                escrow_status,
                type,
                method,
                status,
                transaction_ref
             )
             VALUES(
                :supplier_id,
                :amount,
                :platform_fee,
                :supplier_amount,
                :escrow_status,
                :type,
                :method,
                :status,
                :transaction_ref
             )'
        );
        $this->db->dbbind(':supplier_id', (int)$supplierId);
        $this->db->dbbind(':amount', number_format((float)$amount, 2, '.', ''));
        $this->db->dbbind(':platform_fee', number_format((float)$amount, 2, '.', ''));
        $this->db->dbbind(':supplier_amount', '0.00');
        $this->db->dbbind(':escrow_status', null);
        $this->db->dbbind(':type', 'supplier_fee');
        $this->db->dbbind(':method', $method);
        $this->db->dbbind(':status', 'pending');
        $this->db->dbbind(':transaction_ref', $transactionRef !== '' ? $transactionRef : null);

        if (!$this->db->dbexecute()) {
            return false;
        }

        return (int)$this->db->lastinsertid();
    }

    public function hasPendingSupplierFeePayment($supplierId)
    {
        return !empty($this->getLatestSupplierFeePayment($supplierId, 'pending'));
    }

    public function getLatestSupplierFeePayment($supplierId, $status = null)
    {
        $query = 'SELECT id, amount, method, status, transaction_ref, verified_at, created_at
                  FROM payments
                  WHERE supplier_id = :supplier_id
                    AND type = :type';

        if ($status !== null) {
            $query .= ' AND status = :status';
        }

        $query .= ' ORDER BY id DESC LIMIT 1';

        $this->db->dbquery($query);
        $this->db->dbbind(':supplier_id', (int)$supplierId);
        $this->db->dbbind(':type', 'supplier_fee');

        if ($status !== null) {
            $this->db->dbbind(':status', $status);
        }

        return $this->db->getsingledata();
    }

    public function hasSuccessfulSupplierFeePayment($supplierId)
    {
        $this->db->dbquery(
            'SELECT id
             FROM payments
             WHERE supplier_id = :supplier_id
               AND type = :type
               AND status = :status
             LIMIT 1'
        );
        $this->db->dbbind(':supplier_id', (int)$supplierId);
        $this->db->dbbind(':type', 'supplier_fee');
        $this->db->dbbind(':status', 'success');

        return !empty($this->db->getsingledata());
    }

    public function getSupplierFeeQueue($status = 'pending')
    {
        $status = $status === 'rejected' ? 'failed' : $status;

        $query = 'SELECT payments.id,
                         payments.supplier_id,
                         payments.amount,
                         payments.method,
                         payments.status,
                         payments.transaction_ref,
                         payments.verified_at,
                         payments.created_at,
                         suppliers.shop_name,
                         suppliers.payment_status,
                         users.name AS owner_name,
                         users.email AS owner_email
                  FROM payments
                  LEFT JOIN suppliers ON suppliers.supplier_id = payments.supplier_id
                  LEFT JOIN users ON users.user_id = suppliers.user_id
                  WHERE payments.type = :type';

        if ($status !== 'all') {
            $query .= ' AND payments.status = :status';
        }

        $query .= ' ORDER BY payments.created_at DESC, payments.id DESC';

        $this->db->dbquery($query);
        $this->db->dbbind(':type', 'supplier_fee');

        if ($status !== 'all') {
            $this->db->dbbind(':status', $status);
        }

        return $this->db->getmultidata();
    }

    public function getSupplierFeePaymentById($paymentId)
    {
        $this->db->dbquery(
            'SELECT payments.id,
                    payments.supplier_id,
                    payments.amount,
                    payments.method,
                    payments.status,
                    payments.transaction_ref,
                    payments.verified_at,
                    payments.created_at,
                    suppliers.shop_name,
                    suppliers.payment_status,
                    users.name AS owner_name,
                    users.email AS owner_email
             FROM payments
             LEFT JOIN suppliers ON suppliers.supplier_id = payments.supplier_id
             LEFT JOIN users ON users.user_id = suppliers.user_id
             WHERE payments.id = :id
               AND payments.type = :type
             LIMIT 1'
        );
        $this->db->dbbind(':id', (int)$paymentId);
        $this->db->dbbind(':type', 'supplier_fee');

        return $this->db->getsingledata();
    }

    public function updateSupplierFeeStatus($paymentId, $status, $adminId = null)
    {
        $this->db->dbquery(
            'UPDATE payments
             SET status = :status,
                 verified_by = :verified_by,
                 verified_at = NOW()
             WHERE id = :id
               AND type = :type'
        );
        $this->db->dbbind(':status', $status);
        $this->db->dbbind(':verified_by', $adminId ? (int)$adminId : null);
        $this->db->dbbind(':id', (int)$paymentId);
        $this->db->dbbind(':type', 'supplier_fee');

        return $this->db->dbexecute();
    }

    public function updateSupplierFeeGatewaySuccess($paymentId, $transactionRef)
    {
        $this->db->dbquery(
            'UPDATE payments
             SET status = :status,
                 transaction_ref = :transaction_ref,
                 verified_at = NOW()
             WHERE id = :id
               AND type = :type
               AND status = :pending_status'
        );
        $this->db->dbbind(':status', 'success');
        $this->db->dbbind(':transaction_ref', $transactionRef);
        $this->db->dbbind(':id', (int)$paymentId);
        $this->db->dbbind(':type', 'supplier_fee');
        $this->db->dbbind(':pending_status', 'pending');

        return $this->db->dbexecute();
    }
}
