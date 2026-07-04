<?php
class Log{

    private $db;
    public function __construct()
    {
        $this->db = new Database();
    }

    public function createSystemLog($data){
        $this->db->dbquery("
           INSERT INTO system_logs (user_id, action, ip_address, user_agent, login_time, last_active, logout_time, created_at)
           VALUES (:user_id, :action, :ip, :ua, NOW(), NOW(), :logout_time, NOW())
        ");
        $this->db->dbbind(':user_id', $data['user_id'] ?? null);
        $this->db->dbbind(':action', $data['action']);
        $this->db->dbbind(':ip', $data['ip_address'] ?? null);
        $this->db->dbbind(':ua', $data['user_agent'] ?? null);
        $this->db->dbbind(':logout_time', $data['logout_time'] ?? null);
        return $this->db->dbexecute();
    }

    public function createAccountLockoutLog($data){
        $this->db->dbquery("
            INSERT INTO account_lockout_logs (user_id, event, reason, attempt_count, unlocked_by, locked_until, ip_address, created_at)
            VALUES (:user_id, :event, :reason, :attempt_count, :unlocked_by, :locked_until, :ip, NOW())
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
                SET attempt_count = CASE
                        WHEN last_attempt > (NOW() - INTERVAL 15 MINUTE) THEN attempt_count + 1
                        ELSE 1
                    END,
                    locked_until = CASE
                        WHEN last_attempt > (NOW() - INTERVAL 15 MINUTE)
                         AND attempt_count + 1 >= max_attempts THEN DATE_ADD(NOW(), INTERVAL 15 MINUTE)
                        ELSE NULL
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

    public function getAdminLedger(array $filters, int $limit = 20, int $offset = 0): array
    {
        [$systemWhere, $systemBindings] = $this->buildLedgerWhere($filters, 'system', 's');
        [$lockWhere, $lockBindings] = $this->buildLedgerWhere($filters, 'lockout', 'l');

        $sql = $this->ledgerUnionSql($systemWhere, $lockWhere)
            . " ORDER BY created_at DESC, source_id DESC LIMIT :ledger_limit OFFSET :ledger_offset";

        $this->db->dbquery($sql);
        $this->bindLedgerValues(array_merge($systemBindings, $lockBindings));
        $this->db->dbbind(':ledger_limit', max(1, $limit), PDO::PARAM_INT);
        $this->db->dbbind(':ledger_offset', max(0, $offset), PDO::PARAM_INT);

        return $this->db->getmultidata();
    }

    public function getAdminLedgerCount(array $filters): int
    {
        [$systemWhere, $systemBindings] = $this->buildLedgerWhere($filters, 'system', 'cs');
        [$lockWhere, $lockBindings] = $this->buildLedgerWhere($filters, 'lockout', 'cl');

        $this->db->dbquery(
            "SELECT COUNT(*) AS total FROM (" .
            $this->ledgerUnionSql($systemWhere, $lockWhere) .
            ") ledger_count"
        );
        $this->bindLedgerValues(array_merge($systemBindings, $lockBindings));
        $row = $this->db->getsingledata();

        return (int)($row['total'] ?? 0);
    }

    public function getAdminLedgerStats(): array
    {
        $this->db->dbquery("
            SELECT
                (SELECT COUNT(*) FROM system_logs) + (SELECT COUNT(*) FROM account_lockout_logs) AS total,
                (SELECT COUNT(*) FROM system_logs WHERE LOWER(action) LIKE '%fail%') AS warnings,
                (SELECT COUNT(*) FROM system_logs WHERE LOWER(action) LIKE '%login%' AND LOWER(action) LIKE '%fail%') AS failed_logins,
                (SELECT COUNT(*) FROM account_lockout_logs WHERE event = 'locked') AS critical
        ");
        $row = $this->db->getsingledata() ?: [];

        return [
            'total' => (int)($row['total'] ?? 0),
            'warnings' => (int)($row['warnings'] ?? 0),
            'failed_logins' => (int)($row['failed_logins'] ?? 0),
            'critical' => (int)($row['critical'] ?? 0),
        ];
    }

    private function ledgerUnionSql(string $systemWhere, string $lockWhere): string
    {
        return "
            SELECT
                'system' AS source_type,
                sl.id AS source_id,
                sl.action,
                CASE
                    WHEN LOWER(sl.action) LIKE '%fail%' THEN 'warning'
                    ELSE 'success'
                END AS severity,
                sl.user_id,
                COALESCE(u.name, 'Unknown user') AS user_name,
                COALESCE(u.email, '—') AS user_email,
                sl.ip_address,
                sl.user_agent,
                sl.created_at,
                NULL AS reason,
                NULL AS attempt_count,
                sl.logout_time,
                NULL AS locked_until
            FROM system_logs sl
            LEFT JOIN users u ON u.user_id = sl.user_id
            WHERE {$systemWhere}

            UNION ALL

            SELECT
                'lockout' AS source_type,
                al.id AS source_id,
                CONCAT('account_', al.event) AS action,
                CASE WHEN al.event = 'locked' THEN 'critical' ELSE 'success' END AS severity,
                al.user_id,
                COALESCE(u.name, 'Unknown user') AS user_name,
                COALESCE(u.email, '—') AS user_email,
                al.ip_address,
                NULL AS user_agent,
                al.created_at,
                al.reason,
                al.attempt_count,
                NULL AS logout_time,
                al.locked_until
            FROM account_lockout_logs al
            LEFT JOIN users u ON u.user_id = al.user_id
            WHERE {$lockWhere}
        ";
    }

    private function buildLedgerWhere(array $filters, string $source, string $prefix): array
    {
        $alias = $source === 'system' ? 'sl' : 'al';
        $conditions = ['1 = 1'];
        $bindings = [];
        $search = trim((string)($filters['search'] ?? ''));
        $event = (string)($filters['event'] ?? 'all');
        $status = (string)($filters['status'] ?? 'all');
        $dateFrom = trim((string)($filters['date_from'] ?? ''));
        $dateTo = trim((string)($filters['date_to'] ?? ''));

        if ($search !== '') {
            $searchValue = '%' . $search . '%';
            $nameParam = ':' . $prefix . '_search_name';
            $emailParam = ':' . $prefix . '_search_email';
            $ipParam = ':' . $prefix . '_search_ip';
            $eventParam = ':' . $prefix . '_search_event';
            $conditions[] = "(u.name LIKE {$nameParam} OR u.email LIKE {$emailParam} OR {$alias}.ip_address LIKE {$ipParam}"
                . ($source === 'system' ? " OR {$alias}.action LIKE {$eventParam}" : " OR {$alias}.event LIKE {$eventParam}")
                . ')';
            $bindings[$nameParam] = $searchValue;
            $bindings[$emailParam] = $searchValue;
            $bindings[$ipParam] = $searchValue;
            $bindings[$eventParam] = $searchValue;
        }

        if ($source === 'system') {
            if ($event === 'login') {
                $conditions[] = "LOWER(sl.action) LIKE '%login%'";
            } elseif ($event === 'otp') {
                $conditions[] = "LOWER(sl.action) LIKE '%otp%'";
            } elseif ($event === 'logout') {
                $conditions[] = "LOWER(sl.action) LIKE '%logout%'";
            } elseif ($event === 'lockout') {
                $conditions[] = '1 = 0';
            }

            if ($status === 'success') {
                $conditions[] = "LOWER(sl.action) NOT LIKE '%fail%'";
            } elseif ($status === 'warning') {
                $conditions[] = "LOWER(sl.action) LIKE '%fail%'";
            } elseif ($status === 'critical') {
                $conditions[] = '1 = 0';
            }
        } else {
            if (in_array($event, ['login', 'otp', 'logout'], true)) {
                $conditions[] = '1 = 0';
            }

            if ($status === 'success') {
                $conditions[] = "al.event = 'unlocked'";
            } elseif ($status === 'warning') {
                $conditions[] = '1 = 0';
            } elseif ($status === 'critical') {
                $conditions[] = "al.event = 'locked'";
            }
        }

        if ($dateFrom !== '') {
            $fromParam = ':' . $prefix . '_from';
            $conditions[] = "{$alias}.created_at >= {$fromParam}";
            $bindings[$fromParam] = $dateFrom . ' 00:00:00';
        }

        if ($dateTo !== '') {
            $toParam = ':' . $prefix . '_to';
            $conditions[] = "{$alias}.created_at <= {$toParam}";
            $bindings[$toParam] = $dateTo . ' 23:59:59';
        }

        return [implode(' AND ', $conditions), $bindings];
    }

    private function bindLedgerValues(array $bindings): void
    {
        foreach ($bindings as $param => $value) {
            $this->db->dbbind($param, $value);
        }
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
