<?php

/**
 * CustomerModel — admin-side customer (role_id = 1) management.
 *
 * All reads are scoped to customers. Default lists exclude soft-deleted
 * accounts (deleted_at IS NOT NULL) unless the 'deleted' status is requested.
 */
class CustomerModel
{
    private $db;

    /** Role id for customers (see roles table). */
    private const CUSTOMER_ROLE_ID = 1;

    /** Booking statuses that count as "active" for the moderation warning. */
    private const ACTIVE_BOOKING_STATUSES = "'pending_payment','payment_submitted','payment_verified','paid','suppliers_responding','confirmed','pending_final_payment','finalized'";

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * Build the shared WHERE clause for customer lists.
     * Appends bound params via the provided callback after dbquery() is set.
     */
    private function statusCondition(string $status): string
    {
        switch ($status) {
            case 'active':
                return " AND u.status = 'active' AND u.deleted_at IS NULL";
            case 'suspended':
                return " AND u.status = 'suspended' AND u.deleted_at IS NULL";
            case 'banned':
                return " AND u.status = 'banned' AND u.deleted_at IS NULL";
            case 'deleted':
                return " AND u.deleted_at IS NOT NULL";
            case 'all':
            default:
                return " AND u.deleted_at IS NULL";
        }
    }

    /**
     * Paginated customer list with booking counts.
     */
    public function getCustomers(string $status, string $search, int $limit, int $offset): array
    {
        $sql = "SELECT u.user_id, u.name, u.email, u.phone, u.status, u.deleted_at,
                       u.created_at, u.last_login,
                       (SELECT COUNT(*) FROM bookings b WHERE b.user_id = u.user_id) AS bookings_count
                FROM users u
                INNER JOIN user_roles ur ON ur.user_id = u.user_id AND ur.role_id = :role_id
                WHERE 1 = 1";
        $sql .= $this->statusCondition($status);

        if ($search !== '') {
            $sql .= " AND (u.name LIKE :search OR u.email LIKE :search OR u.phone LIKE :search)";
        }
        $sql .= " ORDER BY u.created_at DESC LIMIT :limit OFFSET :offset";

        $this->db->dbquery($sql);
        $this->db->dbbind(':role_id', self::CUSTOMER_ROLE_ID, PDO::PARAM_INT);
        if ($search !== '') {
            $this->db->dbbind(':search', '%' . $search . '%');
        }
        $this->db->dbbind(':limit', $limit, PDO::PARAM_INT);
        $this->db->dbbind(':offset', $offset, PDO::PARAM_INT);

        return $this->db->getmultidata() ?: [];
    }

    public function getCustomersCount(string $status, string $search): int
    {
        $sql = "SELECT COUNT(*) AS total
                FROM users u
                INNER JOIN user_roles ur ON ur.user_id = u.user_id AND ur.role_id = :role_id
                WHERE 1 = 1";
        $sql .= $this->statusCondition($status);

        if ($search !== '') {
            $sql .= " AND (u.name LIKE :search OR u.email LIKE :search OR u.phone LIKE :search)";
        }

        $this->db->dbquery($sql);
        $this->db->dbbind(':role_id', self::CUSTOMER_ROLE_ID, PDO::PARAM_INT);
        if ($search !== '') {
            $this->db->dbbind(':search', '%' . $search . '%');
        }

        return (int)($this->db->getsingledata()['total'] ?? 0);
    }

    /**
     * Summary counts for the stat cards.
     */
    public function getCustomerStats(): array
    {
        $sql = "SELECT
                    SUM(CASE WHEN u.deleted_at IS NULL THEN 1 ELSE 0 END) AS total,
                    SUM(CASE WHEN u.status = 'active' AND u.deleted_at IS NULL THEN 1 ELSE 0 END) AS active,
                    SUM(CASE WHEN u.status IN ('suspended','banned') AND u.deleted_at IS NULL THEN 1 ELSE 0 END) AS suspended_banned,
                    SUM(CASE WHEN u.deleted_at IS NULL AND u.created_at >= DATE_FORMAT(NOW(), '%Y-%m-01') THEN 1 ELSE 0 END) AS new_this_month
                FROM users u
                INNER JOIN user_roles ur ON ur.user_id = u.user_id AND ur.role_id = :role_id";

        $this->db->dbquery($sql);
        $this->db->dbbind(':role_id', self::CUSTOMER_ROLE_ID, PDO::PARAM_INT);
        $row = $this->db->getsingledata() ?: [];

        return [
            'total' => (int)($row['total'] ?? 0),
            'active' => (int)($row['active'] ?? 0),
            'suspended_banned' => (int)($row['suspended_banned'] ?? 0),
            'new_this_month' => (int)($row['new_this_month'] ?? 0),
        ];
    }

    /**
     * Single customer profile with aggregate booking count and total spend.
     * Returns null when the id is not a customer.
     */
    public function getCustomerById(int $id): ?array
    {
        $sql = "SELECT u.user_id, u.name, u.email, u.phone, u.address, u.avatar,
                       u.status, u.deleted_at, u.created_at, u.last_login, u.is_online,
                       u.email_verified_at,
                       (SELECT COUNT(*) FROM bookings b WHERE b.user_id = u.user_id) AS bookings_count,
                       (SELECT COALESCE(SUM(COALESCE(p.paid_amount, p.amount)), 0)
                        FROM payments p
                        INNER JOIN bookings b2 ON b2.id = p.booking_id
                        WHERE b2.user_id = u.user_id AND p.status = 'success') AS total_spent
                FROM users u
                INNER JOIN user_roles ur ON ur.user_id = u.user_id AND ur.role_id = :role_id
                WHERE u.user_id = :id
                LIMIT 1";

        $this->db->dbquery($sql);
        $this->db->dbbind(':role_id', self::CUSTOMER_ROLE_ID, PDO::PARAM_INT);
        $this->db->dbbind(':id', $id, PDO::PARAM_INT);
        $row = $this->db->getsingledata();

        return $row ?: null;
    }

    /**
     * A customer's bookings with earliest event date and total amount.
     */
    public function getCustomerBookings(int $id): array
    {
        $sql = "SELECT b.id, b.status, b.total_amount, b.paid_amount, b.payment_status,
                       b.created_at,
                       CONCAT('BK-', DATE_FORMAT(b.created_at, '%Y%m%d'), '-', LPAD(b.id, 3, '0')) AS booking_ref,
                       (SELECT MIN(ed.event_date) FROM event_details ed WHERE ed.booking_id = b.id) AS event_date
                FROM bookings b
                WHERE b.user_id = :id
                ORDER BY b.created_at DESC";

        $this->db->dbquery($sql);
        $this->db->dbbind(':id', $id, PDO::PARAM_INT);

        return $this->db->getmultidata() ?: [];
    }

    /**
     * Count of active (confirmed/upcoming) bookings — for the ban/delete warning.
     */
    public function getActiveBookingCount(int $id): int
    {
        $sql = "SELECT COUNT(*) AS total
                FROM bookings b
                WHERE b.user_id = :id
                  AND b.status IN (" . self::ACTIVE_BOOKING_STATUSES . ")";

        $this->db->dbquery($sql);
        $this->db->dbbind(':id', $id, PDO::PARAM_INT);

        return (int)($this->db->getsingledata()['total'] ?? 0);
    }

    /**
     * Moderation history for a customer, newest first, with admin name.
     */
    public function getModerationHistory(int $id): array
    {
        $sql = "SELECT csl.old_status, csl.new_status, csl.action, csl.reason,
                       csl.created_at, admin.name AS admin_name
                FROM customer_status_logs csl
                LEFT JOIN users admin ON admin.user_id = csl.changed_by
                WHERE csl.user_id = :id
                ORDER BY csl.created_at DESC, csl.id DESC";

        $this->db->dbquery($sql);
        $this->db->dbbind(':id', $id, PDO::PARAM_INT);

        return $this->db->getmultidata() ?: [];
    }

    /**
     * Change a customer's account status and log the transition.
     */
    public function setStatus(int $id, string $newStatus, string $action, ?string $reason, int $adminId): bool
    {
        $current = $this->getCustomerById($id);
        if (!$current) {
            return false;
        }
        $oldStatus = (string)($current['status'] ?? '');

        $this->db->dbquery("UPDATE users SET status = :status WHERE user_id = :id");
        $this->db->dbbind(':status', $newStatus);
        $this->db->dbbind(':id', $id, PDO::PARAM_INT);
        if (!$this->db->dbexecute()) {
            return false;
        }

        $this->logChange($id, $oldStatus, $newStatus, $action, $reason, $adminId);
        return true;
    }

    /**
     * Update editable contact fields. Email and password are not editable here.
     */
    public function updateContact(int $id, array $data, int $adminId): bool
    {
        $this->db->dbquery(
            "UPDATE users SET name = :name, phone = :phone, address = :address
             WHERE user_id = :id"
        );
        $this->db->dbbind(':name', $data['name'] ?? null);
        $this->db->dbbind(':phone', $data['phone'] ?? null);
        $this->db->dbbind(':address', $data['address'] ?? null);
        $this->db->dbbind(':id', $id, PDO::PARAM_INT);
        if (!$this->db->dbexecute()) {
            return false;
        }

        $current = $this->getCustomerById($id);
        $status = (string)($current['status'] ?? '');
        $this->logChange($id, $status, $status, 'edit_contact', 'Contact details updated by admin.', $adminId);
        return true;
    }

    /**
     * Soft-delete: set deleted_at, force status to banned, block login. Logged.
     */
    public function softDelete(int $id, ?string $reason, int $adminId): bool
    {
        $current = $this->getCustomerById($id);
        if (!$current) {
            return false;
        }
        $oldStatus = (string)($current['status'] ?? '');

        $this->db->dbquery(
            "UPDATE users SET deleted_at = NOW(), status = 'banned', remember_token = NULL
             WHERE user_id = :id"
        );
        $this->db->dbbind(':id', $id, PDO::PARAM_INT);
        if (!$this->db->dbexecute()) {
            return false;
        }

        $this->logChange($id, $oldStatus, 'banned', 'soft_delete', $reason, $adminId);
        return true;
    }

    private function logChange(int $id, ?string $oldStatus, string $newStatus, string $action, ?string $reason, int $adminId): void
    {
        $this->db->dbquery(
            "INSERT INTO customer_status_logs (user_id, old_status, new_status, action, reason, changed_by)
             VALUES (:uid, :old, :new, :action, :reason, :admin)"
        );
        $this->db->dbbind(':uid', $id, PDO::PARAM_INT);
        $this->db->dbbind(':old', $oldStatus !== '' ? $oldStatus : null);
        $this->db->dbbind(':new', $newStatus);
        $this->db->dbbind(':action', $action);
        $this->db->dbbind(':reason', $reason !== '' ? $reason : null);
        $this->db->dbbind(':admin', $adminId, PDO::PARAM_INT);
        $this->db->dbexecute();
    }
}
