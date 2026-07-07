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

    public function submitManualSupplierFeePayment(
        int $supplierId,
        float $amount,
        string $bankName,
        string $accountName,
        string $mobileNumber,
        string $transactionRef,
        float $paidAmount,
        string $paidAt,
        string $slipPath = '',
        string $remark = ''
    ): ?int {
        $this->db->dbquery(
            'INSERT INTO payments(
                supplier_id, amount, platform_fee, supplier_amount, escrow_status, type,
                method, bank_name, account_name, mobile_number, transaction_ref,
                paid_amount, paid_at, payment_slip_path, status, remark
             ) VALUES(
                :supplier_id, :amount, :amount, :zero, NULL, :type,
                :method, :bank_name, :account_name, :mobile_number, :transaction_ref,
                :paid_amount, :paid_at, :slip_path, :status, :remark
             )'
        );
        $this->db->dbbind(':supplier_id', $supplierId);
        $this->db->dbbind(':amount', number_format($amount, 2, '.', ''));
        $this->db->dbbind(':zero', '0.00');
        $this->db->dbbind(':type', 'supplier_fee');
        $this->db->dbbind(':method', $bankName);
        $this->db->dbbind(':bank_name', $bankName);
        $this->db->dbbind(':account_name', $accountName);
        $this->db->dbbind(':mobile_number', $mobileNumber);
        $this->db->dbbind(':transaction_ref', $transactionRef);
        $this->db->dbbind(':paid_amount', round($paidAmount, 2));
        $this->db->dbbind(':paid_at', $paidAt ?: null);
        $this->db->dbbind(':slip_path', $slipPath ?: null);
        $this->db->dbbind(':status', 'pending');
        $this->db->dbbind(':remark', $remark !== '' ? $remark : null);

        if (!$this->db->dbexecute()) {
            return null;
        }
        return (int)$this->db->lastinsertid();
    }

    public function hasPendingSupplierFeePayment($supplierId)
    {
        return !empty($this->getLatestSupplierFeePayment($supplierId, 'pending'));
    }

    public function getLatestSupplierFeePayment($supplierId, $status = null)
    {
        $query = 'SELECT id, amount, method, status, bank_name, account_name,
                         mobile_number, paid_amount, paid_at, transaction_ref,
                         payment_slip_path, verified_at, created_at
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
                         payments.bank_name,
                         payments.account_name,
                         payments.mobile_number,
                         payments.paid_amount,
                         payments.paid_at,
                         payments.status,
                         payments.transaction_ref,
                         payments.payment_slip_path,
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

    /**
     * Unified admin payment history for supplier membership fees and customer
     * booking payments.
     */
    public function getAdminPaymentHistory(
        string $status = 'all',
        int $limit = 20,
        int $offset = 0,
        string $dateFrom = '',
        string $dateTo = '',
        string $type = 'all'
    ): array {
        $query = "SELECT p.id,
                         p.booking_id,
                         p.supplier_id,
                         COALESCE(p.paid_amount, p.amount, 0) AS amount,
                         p.platform_fee,
                         p.method,
                         p.bank_name,
                         p.account_name,
                         p.mobile_number,
                         p.status,
                         p.escrow_status,
                         p.transaction_ref,
                         p.payment_slip_path,
                         p.verified_at,
                         p.verified_note,
                         p.created_at,
                         p.type,
                         repl.id AS replacement_id,
                         CASE
                             WHEN b.id IS NOT NULL
                             THEN CONCAT('BK-', DATE_FORMAT(b.created_at, '%Y%m%d'), '-', LPAD(b.id, 3, '0'))
                             ELSE NULL
                         END AS booking_ref,
                         s.shop_name,
                         supplier_user.email AS owner_email,
                         customer.name AS customer_name,
                         customer.email AS customer_email
                  FROM payments p
                  LEFT JOIN suppliers s ON s.supplier_id = p.supplier_id
                  LEFT JOIN users supplier_user ON supplier_user.user_id = s.user_id
                  LEFT JOIN bookings b ON b.id = p.booking_id
                  LEFT JOIN users customer ON customer.user_id = b.user_id
                  LEFT JOIN booking_supplier_replacements repl ON repl.delta_payment_id = p.id
                  WHERE 1 = 1";

        if ($status !== 'all') {
            $query .= ' AND p.status = :status';
        }
        if ($type !== 'all') {
            $query .= ' AND p.type = :type';
        }
        if ($dateFrom !== '') {
            $query .= ' AND COALESCE(p.verified_at, p.created_at) >= :date_from';
        }
        if ($dateTo !== '') {
            $query .= ' AND COALESCE(p.verified_at, p.created_at) < DATE_ADD(:date_to, INTERVAL 1 DAY)';
        }
        $query .= ' ORDER BY COALESCE(p.verified_at, p.created_at) DESC, p.id DESC';
        $query .= ' LIMIT :limit OFFSET :offset';

        $this->db->dbquery($query);
        if ($status !== 'all') {
            $this->db->dbbind(':status', $status);
        }
        if ($type !== 'all') {
            $this->db->dbbind(':type', $type);
        }
        if ($dateFrom !== '') {
            $this->db->dbbind(':date_from', $dateFrom);
        }
        if ($dateTo !== '') {
            $this->db->dbbind(':date_to', $dateTo);
        }
        $this->db->dbbind(':limit', $limit, PDO::PARAM_INT);
        $this->db->dbbind(':offset', $offset, PDO::PARAM_INT);
        return $this->db->getmultidata();
    }

    public function getAdminPaymentHistoryCount(
        string $status = 'all',
        string $dateFrom = '',
        string $dateTo = '',
        string $type = 'all'
    ): int {
        $query = "SELECT COUNT(*) AS total
                  FROM payments p
                  LEFT JOIN suppliers s ON s.supplier_id = p.supplier_id
                  LEFT JOIN users supplier_user ON supplier_user.user_id = s.user_id
                  LEFT JOIN bookings b ON b.id = p.booking_id
                  LEFT JOIN users customer ON customer.user_id = b.user_id
                  WHERE 1 = 1";

        if ($status !== 'all') {
            $query .= ' AND p.status = :status';
        }
        if ($type !== 'all') {
            $query .= ' AND p.type = :type';
        }
        if ($dateFrom !== '') {
            $query .= ' AND COALESCE(p.verified_at, p.created_at) >= :date_from';
        }
        if ($dateTo !== '') {
            $query .= ' AND COALESCE(p.verified_at, p.created_at) < DATE_ADD(:date_to, INTERVAL 1 DAY)';
        }

        $this->db->dbquery($query);
        if ($status !== 'all') {
            $this->db->dbbind(':status', $status);
        }
        if ($type !== 'all') {
            $this->db->dbbind(':type', $type);
        }
        if ($dateFrom !== '') {
            $this->db->dbbind(':date_from', $dateFrom);
        }
        if ($dateTo !== '') {
            $this->db->dbbind(':date_to', $dateTo);
        }
        return (int)($this->db->getsingledata()['total'] ?? 0);
    }

    /**
     * Reviewed customer deposit payments for the verification history tabs.
     * $status: 'verified' (=> success) or 'rejected' (=> failed).
     * Returns payment-centric rows joined to their booking + customer, using
     * the same field aliases the verification view already reads.
     */
    public function getDepositReviewQueue($status = 'verified', int $limit = 15, int $offset = 0)
    {
        $payStatus = $status === 'rejected' ? 'failed' : 'success';

        $this->db->dbquery(
            "SELECT b.id,
                    b.total_amount,
                    b.status AS booking_status,
                    u.name,
                    u.email,
                    u.phone,
                    p.id AS payment_id,
                    p.amount AS payment_amount,
                    p.transaction_ref,
                    p.method,
                    p.bank_name,
                    p.account_name,
                    p.mobile_number,
                    p.paid_amount,
                    p.paid_at,
                    p.payment_slip_path,
                    p.status AS payment_status,
                    p.verified_at,
                    p.verified_note,
                    p.created_at AS payment_created_at,
                    p.type AS payment_type
             FROM payments p
             JOIN bookings b ON b.id = p.booking_id
             LEFT JOIN users u ON u.user_id = b.user_id
             WHERE p.type IN ('deposit', 'remaining') AND p.status = :pstatus
             ORDER BY p.verified_at DESC, p.id DESC
             LIMIT :limit OFFSET :offset"
        );
        $this->db->dbbind(':pstatus', $payStatus);
        $this->db->dbbind(':limit', $limit, PDO::PARAM_INT);
        $this->db->dbbind(':offset', $offset, PDO::PARAM_INT);

        return $this->db->getmultidata();
    }

    public function getDepositReviewQueueCount(string $status = 'verified'): int
    {
        $payStatus = $status === 'rejected' ? 'failed' : 'success';

        $this->db->dbquery(
            "SELECT COUNT(*) AS total
             FROM payments p
             JOIN bookings b ON b.id = p.booking_id
             WHERE p.type IN ('deposit', 'remaining') AND p.status = :pstatus"
        );
        $this->db->dbbind(':pstatus', $payStatus);
        return (int)($this->db->getsingledata()['total'] ?? 0);
    }

    public function getSupplierFeePaymentById($paymentId)
    {
        $this->db->dbquery(
            'SELECT payments.id,
                    payments.supplier_id,
                    payments.amount,
                    payments.method,
                    payments.bank_name,
                    payments.account_name,
                    payments.mobile_number,
                    payments.paid_amount,
                    payments.paid_at,
                    payments.status,
                    payments.transaction_ref,
                    payments.payment_slip_path,
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
