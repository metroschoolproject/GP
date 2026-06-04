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

    public function getUnreadCount($userId = null)
    {
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

    public function getLatest($userId = null, $limit = 8)
    {
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
             WHERE roles.name IN ('admin', 'staff')
               AND users.deleted_at IS NULL"
        );

        return array_map(
            static fn($row) => (int)$row['user_id'],
            $this->db->getmultidata()
        );
    }
}
