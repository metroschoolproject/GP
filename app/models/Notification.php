<?php

class Notification
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function notifyAdmins($title, $message, $type, $referenceType, $referenceId)
    {
        $adminIds = $this->getAdminUserIds();

        if (empty($adminIds)) {
            return $this->create(null, $title, $message, $type, $referenceType, $referenceId);
        }

        foreach ($adminIds as $adminId) {
            if (!$this->create((int)$adminId, $title, $message, $type, $referenceType, $referenceId)) {
                return false;
            }
        }

        return true;
    }

    public function notifyUser($userId, $title, $message, $type, $referenceType, $referenceId)
    {
        if (!$userId) {
            return false;
        }

        return $this->create((int)$userId, $title, $message, $type, $referenceType, $referenceId);
    }

    /**
     * Check if a user has enabled notifications for a given type.
     * Checks supplier's notification_prefs first, then falls back to user's.
     * Returns true if enabled or if no preference is set (default on).
     */
    private function shouldNotify(int $userId, string $type): bool
    {
        // Supplier preference map
        $supplierPrefMap = [
            'booking'            => 'new_booking',
            'payment'            => 'payment_received',
            'payment_verified'   => 'payment_received',
            'review'             => 'new_review',
            'publish_approved'   => 'publish_approved',
            'publish_request'    => 'publish_approved',
        ];

        // Customer preference map
        $customerPrefMap = [
            'booking_confirmed'  => 'booking_updates',
            'booking_completed'  => 'booking_updates',
            'booking_cancelled'  => 'booking_updates',
            'payment_verified'   => 'payment_updates',
            'replacement'        => 'replacement_updates',
        ];

        // Try supplier prefs first
        $prefKey = $supplierPrefMap[$type] ?? null;
        if ($prefKey) {
            $this->db->dbquery("SELECT notification_prefs FROM suppliers WHERE user_id = :uid LIMIT 1");
            $this->db->dbbind(':uid', $userId);
            $row = $this->db->getsingledata();
            if ($row && !empty($row['notification_prefs'])) {
                $prefs = json_decode($row['notification_prefs'], true);
                if (is_array($prefs) && isset($prefs[$prefKey]) && !$prefs[$prefKey]) {
                    return false;
                }
            }
            return true; // No prefs or enabled
        }

        // Try customer prefs
        $prefKey = $customerPrefMap[$type] ?? null;
        if ($prefKey) {
            $this->db->dbquery("SELECT notification_prefs FROM users WHERE user_id = :uid LIMIT 1");
            $this->db->dbbind(':uid', $userId);
            $row = $this->db->getsingledata();
            if ($row && !empty($row['notification_prefs'])) {
                $prefs = json_decode($row['notification_prefs'], true);
                if (is_array($prefs) && isset($prefs[$prefKey]) && !$prefs[$prefKey]) {
                    return false;
                }
            }
            return true;
        }

        // Unknown type — always send
        return true;
    }

    public function getUnreadCount($userId = null, bool $respectPopupPreferences = false)
    {
        if ($respectPopupPreferences && $userId) {
            $query = 'SELECT type FROM notifications WHERE is_read = 0 AND (user_id = :user_id OR user_id IS NULL)';
            $this->db->dbquery($query);
            $this->db->dbbind(':user_id', (int)$userId);
            $rows = $this->db->getmultidata() ?: [];
            $total = 0;
            foreach ($rows as $row) {
                if ($this->shouldNotify((int)$userId, (string)($row['type'] ?? ''))) {
                    $total++;
                }
            }
            return $total;
        }

        $query = 'SELECT COUNT(*) AS total FROM notifications WHERE is_read = 0';

        if ($userId) {
            $query .= ' AND (user_id = :user_id OR user_id IS NULL)';
        }

        $this->db->dbquery($query);

        if ($userId) {
            $this->db->dbbind(':user_id', (int)$userId);
        }

        $row = $this->db->getsingledata();

        return (int)($row['total'] ?? 0);
    }

    public function getLatest($userId = null, $limit = 8, bool $respectPopupPreferences = false)
    {
        if ($respectPopupPreferences && $userId) {
            return $this->getLatestForPopup((int)$userId, $limit);
        }

        $query = 'SELECT id, title, message, type, reference_type, reference_id, is_read, created_at
                  FROM notifications';

        if ($userId) {
            $query .= ' WHERE user_id = :user_id OR user_id IS NULL';
        }

        $query .= ' ORDER BY id DESC LIMIT :limit';

        $this->db->dbquery($query);

        if ($userId) {
            $this->db->dbbind(':user_id', (int)$userId);
        }

        $this->db->dbbind(':limit', max(1, min(20, (int)$limit)), PDO::PARAM_INT);

        return $this->db->getmultidata();
    }

    private function getLatestForPopup(int $userId, int $limit): array
    {
        $targetLimit = max(1, min(20, (int)$limit));
        $offset = 0;
        $batchSize = max($targetLimit * 3, 12);
        $visible = [];

        while (count($visible) < $targetLimit) {
            $this->db->dbquery(
                'SELECT id, title, message, type, reference_type, reference_id, is_read, created_at
                   FROM notifications
                  WHERE user_id = :user_id OR user_id IS NULL
                  ORDER BY id DESC
                  LIMIT :limit OFFSET :offset'
            );
            $this->db->dbbind(':user_id', $userId, PDO::PARAM_INT);
            $this->db->dbbind(':limit', $batchSize, PDO::PARAM_INT);
            $this->db->dbbind(':offset', $offset, PDO::PARAM_INT);
            $rows = $this->db->getmultidata() ?: [];

            if (empty($rows)) {
                break;
            }

            foreach ($rows as $row) {
                if ($this->shouldNotify($userId, (string)($row['type'] ?? ''))) {
                    $visible[] = $row;
                    if (count($visible) >= $targetLimit) {
                        break;
                    }
                }
            }

            if (count($rows) < $batchSize) {
                break;
            }
            $offset += $batchSize;
        }

        return $visible;
    }

    public function getAll($userId = null, $limit = 50, $offset = 0)
    {
        $query = 'SELECT id, title, message, type, reference_type, reference_id, is_read, created_at
                  FROM notifications';

        if ($userId) {
            $query .= ' WHERE user_id = :user_id OR user_id IS NULL';
        }

        $query .= ' ORDER BY id DESC LIMIT :limit OFFSET :offset';

        $this->db->dbquery($query);

        if ($userId) {
            $this->db->dbbind(':user_id', (int)$userId);
        }

        $this->db->dbbind(':limit', max(1, min(100, (int)$limit)), PDO::PARAM_INT);
        $this->db->dbbind(':offset', max(0, (int)$offset), PDO::PARAM_INT);

        return $this->db->getmultidata();
    }

    public function getAllCount($userId = null): int
    {
        $query = 'SELECT COUNT(*) AS total FROM notifications';

        if ($userId) {
            $query .= ' WHERE user_id = :user_id OR user_id IS NULL';
        }

        $this->db->dbquery($query);

        if ($userId) {
            $this->db->dbbind(':user_id', (int)$userId);
        }

        return (int)($this->db->getsingledata()['total'] ?? 0);
    }

    public function getAdminInbox($userId, array $filters, int $limit = 20, int $offset = 0): array
    {
        [$where, $bindings] = $this->buildInboxWhere($userId, $filters, 'inbox');
        $this->db->dbquery(
            "SELECT id, title, message, type, reference_type, reference_id, is_read, created_at
             FROM notifications
             WHERE {$where}
             ORDER BY created_at DESC, id DESC
             LIMIT :limit OFFSET :offset"
        );
        $this->bindInboxValues($bindings);
        $this->db->dbbind(':limit', max(1, min(100, $limit)), PDO::PARAM_INT);
        $this->db->dbbind(':offset', max(0, $offset), PDO::PARAM_INT);

        return $this->db->getmultidata();
    }

    public function getAdminInboxCount($userId, array $filters): int
    {
        [$where, $bindings] = $this->buildInboxWhere($userId, $filters, 'count');
        $this->db->dbquery("SELECT COUNT(*) AS total FROM notifications WHERE {$where}");
        $this->bindInboxValues($bindings);

        return (int)($this->db->getsingledata()['total'] ?? 0);
    }

    public function getAdminInboxStats($userId): array
    {
        $where = $userId ? '(user_id = :stats_user_id OR user_id IS NULL)' : '1 = 1';
        $this->db->dbquery(
            "SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) AS unread,
                SUM(CASE WHEN type = 'booking' THEN 1 ELSE 0 END) AS booking,
                SUM(CASE WHEN type = 'payment' THEN 1 ELSE 0 END) AS payment,
                SUM(CASE WHEN type = 'approval' THEN 1 ELSE 0 END) AS approval,
                SUM(CASE WHEN type = 'system' THEN 1 ELSE 0 END) AS system
             FROM notifications
             WHERE {$where}"
        );
        if ($userId) {
            $this->db->dbbind(':stats_user_id', (int)$userId, PDO::PARAM_INT);
        }
        $row = $this->db->getsingledata() ?: [];

        return [
            'total' => (int)($row['total'] ?? 0),
            'unread' => (int)($row['unread'] ?? 0),
            'booking' => (int)($row['booking'] ?? 0),
            'payment' => (int)($row['payment'] ?? 0),
            'approval' => (int)($row['approval'] ?? 0),
            'system' => (int)($row['system'] ?? 0),
        ];
    }

    public function markAllRead($userId = null): bool
    {
        $query = 'UPDATE notifications SET is_read = 1 WHERE is_read = 0';
        if ($userId) {
            $query .= ' AND (user_id = :mark_all_user_id OR user_id IS NULL)';
        }

        $this->db->dbquery($query);
        if ($userId) {
            $this->db->dbbind(':mark_all_user_id', (int)$userId, PDO::PARAM_INT);
        }

        return $this->db->dbexecute();
    }

    private function buildInboxWhere($userId, array $filters, string $prefix): array
    {
        $conditions = [];
        $bindings = [];
        $type = (string)($filters['type'] ?? 'all');
        $state = (string)($filters['state'] ?? 'all');
        $search = trim((string)($filters['search'] ?? ''));

        if ($userId) {
            $userParam = ':' . $prefix . '_user_id';
            $conditions[] = "(user_id = {$userParam} OR user_id IS NULL)";
            $bindings[$userParam] = (int)$userId;
        } else {
            $conditions[] = '1 = 1';
        }

        if (in_array($type, ['booking', 'payment', 'approval', 'system'], true)) {
            $typeParam = ':' . $prefix . '_type';
            $conditions[] = "type = {$typeParam}";
            $bindings[$typeParam] = $type;
        }

        if ($state === 'unread') {
            $conditions[] = 'is_read = 0';
        }

        if ($search !== '') {
            $titleParam = ':' . $prefix . '_title';
            $messageParam = ':' . $prefix . '_message';
            $referenceParam = ':' . $prefix . '_reference';
            $searchValue = '%' . $search . '%';
            $conditions[] = "(title LIKE {$titleParam} OR message LIKE {$messageParam} OR reference_type LIKE {$referenceParam})";
            $bindings[$titleParam] = $searchValue;
            $bindings[$messageParam] = $searchValue;
            $bindings[$referenceParam] = $searchValue;
        }

        return [implode(' AND ', $conditions), $bindings];
    }

    private function bindInboxValues(array $bindings): void
    {
        foreach ($bindings as $param => $value) {
            $this->db->dbbind($param, $value);
        }
    }

    public function getById($notificationId, $userId = null)
    {
        $query = 'SELECT id, title, message, type, reference_type, reference_id, is_read, created_at
                  FROM notifications
                  WHERE id = :id';

        if ($userId) {
            $query .= ' AND (user_id = :user_id OR user_id IS NULL)';
        }

        $query .= ' LIMIT 1';

        $this->db->dbquery($query);
        $this->db->dbbind(':id', (int)$notificationId);

        if ($userId) {
            $this->db->dbbind(':user_id', (int)$userId);
        }

        return $this->db->getsingledata();
    }

    public function getLatestForReference($type, $referenceType, $referenceId)
    {
        $this->db->dbquery(
            'SELECT id, title, message, type, reference_type, reference_id, is_read, created_at
             FROM notifications
             WHERE type = :type
               AND reference_type = :reference_type
               AND reference_id = :reference_id
             ORDER BY id DESC
             LIMIT 1'
        );
        $this->db->dbbind(':type', $type);
        $this->db->dbbind(':reference_type', $referenceType);
        $this->db->dbbind(':reference_id', (int)$referenceId);

        return $this->db->getsingledata();
    }

    public function markRead($notificationId, $userId = null)
    {
        $query = 'UPDATE notifications SET is_read = 1 WHERE id = :id';

        if ($userId) {
            $query .= ' AND (user_id = :user_id OR user_id IS NULL)';
        }

        $this->db->dbquery($query);
        $this->db->dbbind(':id', (int)$notificationId);

        if ($userId) {
            $this->db->dbbind(':user_id', (int)$userId);
        }

        return $this->db->dbexecute();
    }

    private function create($userId, $title, $message, $type, $referenceType, $referenceId)
    {
        $this->db->dbquery(
            'INSERT INTO notifications(user_id, title, message, type, reference_type, reference_id, is_read)
             VALUES(:user_id, :title, :message, :type, :reference_type, :reference_id, :is_read)'
        );
        $this->db->dbbind(':user_id', $userId);
        $this->db->dbbind(':title', $title);
        $this->db->dbbind(':message', $message);
        $this->db->dbbind(':type', $type);
        $this->db->dbbind(':reference_type', $referenceType);
        $this->db->dbbind(':reference_id', (int)$referenceId);
        $this->db->dbbind(':is_read', 0);

        return $this->db->dbexecute();
    }

    private function getAdminUserIds()
    {
        $this->db->dbquery(
            "SELECT DISTINCT users.user_id
             FROM users
             INNER JOIN user_roles ON user_roles.user_id = users.user_id
             INNER JOIN roles ON roles.id = user_roles.role_id
             WHERE roles.name = 'admin'
               AND users.deleted_at IS NULL"
        );

        return array_map(
            static fn($row) => (int)$row['user_id'],
            $this->db->getmultidata()
        );
    }

    /**
     * Get supplier user IDs linked to a booking.
     */
    public function getSupplierUserIdsForBooking(int $bookingId): array
    {
        $this->db->dbquery(
            "SELECT DISTINCT s.user_id
             FROM booking_suppliers bs
             INNER JOIN suppliers s ON bs.supplier_id = s.supplier_id
             WHERE bs.booking_id = :bid
               AND s.user_id IS NOT NULL"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);

        return array_map(
            static fn($row) => (int)$row['user_id'],
            $this->db->getmultidata()
        );
    }

    /**
     * Notify all suppliers linked to a booking.
     */
    public function notifyBookingSuppliers(int $bookingId, string $title, string $message, string $type): bool
    {
        $userIds = $this->getSupplierUserIdsForBooking($bookingId);

        if (empty($userIds)) {
            return false;
        }

        foreach ($userIds as $userId) {
            if (!$this->create($userId, $title, $message, $type, 'booking', $bookingId)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the customer user ID for a booking.
     */
    public function getCustomerUserIdForBooking(int $bookingId): ?int
    {
        $this->db->dbquery(
            "SELECT user_id FROM bookings WHERE id = :bid LIMIT 1"
        );
        $this->db->dbbind(':bid', $bookingId, PDO::PARAM_INT);
        $row = $this->db->getsingledata();

        return $row ? (int)$row['user_id'] : null;
    }

    /**
     * Notify the customer who owns a booking.
     */
    public function notifyBookingCustomer(int $bookingId, string $title, string $message, string $type): bool
    {
        $userId = $this->getCustomerUserIdForBooking($bookingId);

        if (!$userId) {
            return false;
        }

        return $this->create($userId, $title, $message, $type, 'booking', $bookingId);
    }
}
