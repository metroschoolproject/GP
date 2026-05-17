<?php
class Log{

    private $db;
    public function __construct()
    {
        $this->db = new Database();
    }

    public function createSystemLog($data){
        $this->db->dbquery("
           INSERT INTO system_logs (user_id, action, ip_address, user_agent)
           VALUES (:user_id, :action, :ip, :ua)
        ");
        $this->db->dbbind(':user_id', $data['user_id'] ?? null);
        $this->db->dbbind(':action', $data['action']);
        $this->db->dbbind(':ip', $data['ip_address'] ?? null);
        $this->db->dbbind(':ua', $data['user_agent'] ?? null);
        return $this->db->dbexecute();
    }

    public function createAccountLockoutLog($data){
        $this->db->dbquery("
            INSERT INTO account_lockout_logs (user_id, event, reason, attempt_count, unlocked_by, locked_until, ip_address)
            VALUES (:user_id, :event, :reason, :attempt_count, :unlocked_by, :locked_until, :ip)
        ");
        $this->db->dbbind(':user_id', $data['user_id']);
        $this->db->dbbind(':event', $data['event']);
        $this->db->dbbind(':reason', $data['reason'] ?? null);
        $this->db->dbbind(':attempt_count', $data['attempt_count'] ?? 0);
        $this->db->dbbind(':unlocked_by', $data['unlocked_by'] ?? null);
        $this->db->dbbind(':locked_until', $data['locked_until'] ?? null);
        $this->db->dbbind(':ip', $data['ip_address'] ?? null);
        return $this->db->dbexecute();
    }

    public function recordLoginFail($email, $ip) {
        $this->db->dbquery("
            SELECT id, attempt_count, max_attempts
            FROM login_attempts
            WHERE email = :email AND ip_address = :ip
            LIMIT 1
        ");
        $this->db->dbbind(':email', $email);
        $this->db->dbbind(':ip', $ip);
        $row = $this->db->getsingledata();

        if ($row) {
            $this->db->dbquery("
                UPDATE login_attempts
                SET attempt_count = attempt_count + 1,
                    locked_until = CASE
                        WHEN attempt_count + 1 >= max_attempts THEN DATE_ADD(NOW(), INTERVAL 15 MINUTE)
                        ELSE locked_until
                    END,
                    last_attempt = NOW()
                WHERE id = :id
            ");
            $this->db->dbbind(':id', $row['id']);
            return $this->db->dbexecute();
        }

        $this->db->dbquery("
            INSERT INTO login_attempts (email, ip_address, attempt_count, max_attempts)
            VALUES (:email, :ip, 1, 3)
        ");
        $this->db->dbbind(':email', $email);
        $this->db->dbbind(':ip', $ip);
        return $this->db->dbexecute();
    }

    public function clearLoginFails($email, $ip) {
        $this->db->dbquery("
            DELETE FROM login_attempts
            WHERE email = :email AND ip_address = :ip
        ");
        $this->db->dbbind(':email', $email);
        $this->db->dbbind(':ip', $ip);
        return $this->db->dbexecute();
    }

    public function markSystemLogout($userid) {
        $this->db->dbquery("
            UPDATE system_logs
            SET logout_time = NOW()
            WHERE user_id = :user_id
              AND action = 'login_success'
              AND logout_time IS NULL
            ORDER BY id DESC
            LIMIT 1
        ");
        $this->db->dbbind(':user_id', $userid);
        return $this->db->dbexecute();
    }

    public function detect_loginfail($email){
        $this->db->dbquery("SELECT COALESCE(SUM(attempt_count), 0) AS loginfails FROM login_attempts WHERE email = :email AND last_attempt > (NOW() - INTERVAL 15 MINUTE)");
        $this->db->dbbind(':email', $email);
        $row = $this->db->getsingledata(); 

        return $row;
    }

    public function getLoginLock($email){
        $this->db->dbquery("
            SELECT MAX(locked_until) AS locked_until, COALESCE(SUM(attempt_count), 0) AS loginfails
            FROM login_attempts
            WHERE email = :email
              AND locked_until IS NOT NULL
              AND locked_until > NOW()
        ");
        $this->db->dbbind(':email', $email);
        return $this->db->getsingledata();
    }

    public function detect_otpfail($email){
        $this->db->dbquery("
            SELECT COUNT(*) AS otpfails
            FROM system_logs sl
            JOIN users u ON sl.user_id = u.user_id
            WHERE u.email = :email
              AND sl.action = 'verifyOTP_fail'
              AND sl.created_at > (NOW() - INTERVAL 10 MINUTE)
        ");
        $this->db->dbbind(':email', $email);
        $row = $this->db->getsingledata(); 

        return $row;
    }

}


?>
