<?php

class User
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function register($data)
    {
        $this->db->dbquery('INSERT INTO users(name,password,email) VALUES (:name,:password,:email)');
        $this->db->dbbind(':name', $data['username']);
        $this->db->dbbind(':password', $data['password']);
        $this->db->dbbind(':email', $data['email']);

        if ($this->db->dbexecute()) {
            return $this->db->lastinsertid();
        } else {
            return false;
        }

    }

    public function assignRole($userId, $roleName)
    {
        $this->db->dbquery('SELECT id FROM roles WHERE name = :name LIMIT 1');
        $this->db->dbbind(':name', $roleName);
        $role = $this->db->getsingledata();

        if (!$role) {
            return false;
        }

        $this->db->dbquery('SELECT id FROM user_roles WHERE user_id = :user_id AND role_id = :role_id LIMIT 1');
        $this->db->dbbind(':user_id', (int)$userId);
        $this->db->dbbind(':role_id', (int)$role['id']);
        $existingRole = $this->db->getsingledata();

        if ($existingRole) {
            return true;
        }

        $this->db->dbquery('INSERT INTO user_roles(user_id, role_id) VALUES (:user_id, :role_id)');
        $this->db->dbbind(':user_id', (int)$userId);
        $this->db->dbbind(':role_id', (int)$role['id']);

        return $this->db->dbexecute();
    }

    public function userHasAnyRole($userId)
    {
        $this->db->dbquery('SELECT id FROM user_roles WHERE user_id = :user_id LIMIT 1');
        $this->db->dbbind(':user_id', (int)$userId);
        $this->db->getsingledata();

        return $this->db->rowcount() > 0;
    }


    public function registeremailcheck($email)
    {
        $this->db->dbquery("SELECT * FROM users WHERE email=:email");
        $this->db->dbbind(':email', $email);
        $this->db->getsingledata();

        if ($this->db->rowcount() > 0) {
            return true;
        } else {
            return false;
        }

    }

    public function cleanupStaleUnverifiedPublicAccounts($days = 7)
    {
        $days = max(1, (int)$days);
        $cutoff = (new DateTimeImmutable('-' . $days . ' days'))->format('Y-m-d H:i:s');

        $stalePublicUserFilter =
            "u.email_verified_at IS NULL
             AND u.created_at < :cutoff
             AND u.google_id IS NULL
             AND u.facebook_id IS NULL
             AND EXISTS (
                 SELECT 1
                 FROM user_roles public_user_roles
                 INNER JOIN roles public_roles ON public_roles.id = public_user_roles.role_id
                 WHERE public_user_roles.user_id = u.user_id
                   AND public_roles.name IN ('customer', 'supplier')
             )
             AND NOT EXISTS (
                 SELECT 1
                 FROM user_roles internal_user_roles
                 INNER JOIN roles internal_roles ON internal_roles.id = internal_user_roles.role_id
                 WHERE internal_user_roles.user_id = u.user_id
                   AND internal_roles.name NOT IN ('customer', 'supplier')
             )";

        // These auth records do not cascade when the abandoned user is deleted.
        $authRecordDeletes = [
            'DELETE system_logs
             FROM system_logs
             INNER JOIN users u ON u.user_id = system_logs.user_id
             WHERE ' . $stalePublicUserFilter,
            'DELETE account_lockout_logs
             FROM account_lockout_logs
             INNER JOIN users u ON u.user_id = account_lockout_logs.user_id
             WHERE ' . $stalePublicUserFilter,
            'DELETE otps
             FROM otps
             INNER JOIN users u ON u.user_id = otps.user_id
             WHERE ' . $stalePublicUserFilter,
        ];

        foreach ($authRecordDeletes as $query) {
            $this->db->dbquery($query);
            $this->db->dbbind(':cutoff', $cutoff);
            $this->db->dbexecute();
        }

        $this->db->dbquery(
            'DELETE u
             FROM users u
             WHERE ' . $stalePublicUserFilter
        );
        $this->db->dbbind(':cutoff', $cutoff);
        $this->db->dbexecute();

        return $this->db->rowcount();
    }

    // Get challange code
    public function getchallenge($email){
        $this->db->dbquery("SELECT password FROM users WHERE email = :email");
        $this->db->dbbind(":email",$email);
        $row = $this->db->getsingledata();
        if(!$row){
            return false;
        }
        $challenge_code = bin2hex(random_bytes(16));
        $_SESSION['challenge'] = $challenge_code;
        return ['challenge' => $challenge_code];
    }


    public function login($data)
    {
        $this->db->dbquery("SELECT user_id,password FROM users WHERE email = :email");
        $this->db->dbbind(":email", $data['email']);
        $row = $this->db->getsingledata();
        if (!$row) return false;

        $pw_sha = $data['pw_sha'];
        $response = $data['response'];
        $stored_hash = $row['password'];   // bcrypt stored hash
        $challenge = $_SESSION['challenge'] ?? '';
        $_SESSION['session_uid'] = $row['user_id'];
        $_SESSION['session_email'] = $data['email'];

        if (password_verify($pw_sha, $stored_hash)) {
            $expected = hash('sha256', $pw_sha . $challenge);
            if (hash_equals($expected, $response)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }

    


    }

    public function getUserRoles($userId)
    {
        $this->db->dbquery(
            'SELECT roles.name
             FROM user_roles
             INNER JOIN roles ON roles.id = user_roles.role_id
             WHERE user_roles.user_id = :user_id'
        );
        $this->db->dbbind(':user_id', (int)$userId);
        $roles = $this->db->getmultidata();

        return array_column($roles, 'name');
    }

    public function markEmailVerified($userId)
    {
        $this->db->dbquery('UPDATE users SET email_verified_at = NOW() WHERE user_id = :user_id');
        $this->db->dbbind(':user_id', (int)$userId);

        return $this->db->dbexecute();
    }

    public function passwordLoginNeedsEmailVerification($user)
    {
        if (!empty($user['email_verified_at'])) {
            return false;
        }

        $roles = $this->getUserRoles($user['user_id']);

        return in_array('customer', $roles, true) || in_array('supplier', $roles, true);
    }


    public function getuserinfo($email)
    {
        $this->db->dbquery('SELECT * FROM users WHERE email = :email');
        $this->db->dbbind(':email', $email);
        return $this->db->getsingledata();
    }

    // Account lock
    public function lockaccount($email, $until) {
        $this->db->dbquery("UPDATE users SET status = 'locked', lock_reason = 'password_attempts', locked_until = :until WHERE email = :email");
        $this->db->dbbind(':until', $until);
        $this->db->dbbind(':email', $email);
        return $this->db->dbexecute();
    }

    public function recordpasswordfail($email) {
        $this->db->dbquery("UPDATE users SET failed_password_attempts = failed_password_attempts + 1, last_failed_at = NOW() WHERE email = :email");
        $this->db->dbbind(':email', $email);
        return $this->db->dbexecute();
    }

    public function markloginsuccess($email) {
        $this->db->dbquery("UPDATE users SET last_login = NOW(), is_online = 1, failed_password_attempts = 0, last_failed_at = NULL, locked_until = NULL, lock_reason = NULL, status = CASE WHEN status = 'locked' THEN 'active' ELSE status END WHERE email = :email");
        $this->db->dbbind(':email', $email);
        return $this->db->dbexecute();
    }

    public function marklogout($userid) {
        $this->db->dbquery("UPDATE users SET is_online = 0 WHERE user_id = :id");
        $this->db->dbbind(':id', $userid);
        return $this->db->dbexecute();
    }

    public function clearpasswordlock($email) {
        $this->db->dbquery("UPDATE users SET status = 'active', lock_reason = NULL, locked_until = NULL, failed_password_attempts = 0 WHERE email = :email AND lock_reason = 'password_attempts'");
        $this->db->dbbind(':email', $email);
        return $this->db->dbexecute();
    }



    public function guest_email($email)
    {
        if (isset($_POST['guest_email_btn'])) {
            $this->db->dbquery('INSERT INTO guests (email) VALUES (:email)');
            $this->db->dbbind(':email', $email);
            if ($this->db->dbexecute()) {
                return true;
            } else {
                return false;
            }
        }
    }


    public function guest_email_check($email)
    {
        $this->db->dbquery("SELECT * FROM guests WHERE email=:email");
        $this->db->dbbind(':email', $email);


        $this->db->getsingledata();

        if ($this->db->rowcount() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function guest_email_update($data)
    {
        $this->db->dbquery('UPDATE guests SET email=:email WHERE id = :id');
        $this->db->dbbind(':email', $data['guest_email']);

        $this->db->dbbind(":id", $data['id']);

        $this->db->dbexecute();

    }

    public function guest_user_info($email)
    {
        $this->db->dbquery("SELECT * FROM guests WHERE email=:email");
        $this->db->dbbind(':email', $email);
        return $this->db->getsingledata();
    }

    // Google Auth

    public function findOrCreateGoogleUser($data)
    {
        $this->db->dbquery("SELECT * FROM users WHERE google_id = :google_id OR email = :email LIMIT 1");
        $this->db->dbbind(':google_id', $data['google_id']);
        $this->db->dbbind(':email', $data['email']);
        $user = $this->db->getsingledata();

        if ($user) {
            $this->db->dbquery("UPDATE users SET google_id = :google_id, avatar = :avatar WHERE user_id = :id");
            $this->db->dbbind(':google_id', $data['google_id']);
            $this->db->dbbind(':avatar', $data['avatar']);
            $this->db->dbbind(':id', $user['user_id']);
            $this->db->dbexecute();

            return $user;
        }

        $randomPassword = password_hash(hash('sha256', bin2hex(random_bytes(32))), PASSWORD_DEFAULT);

        $this->db->dbquery("INSERT INTO users(name, email, password, google_id, avatar) VALUES(:name, :email, :password, :google_id, :avatar)");
        $this->db->dbbind(':name', $data['name']);
        $this->db->dbbind(':email', $data['email']);
        $this->db->dbbind(':password', $randomPassword);
        $this->db->dbbind(':google_id', $data['google_id']);
        $this->db->dbbind(':avatar', $data['avatar']);
        $this->db->dbexecute();

        return $this->getuserinfo($data['email']);
    }

    public function findOrCreateFacebookUser($data)
    {
        $this->db->dbquery("SELECT * FROM users WHERE facebook_id = :facebook_id OR email = :email LIMIT 1");
        $this->db->dbbind(':facebook_id', $data['facebook_id']);
        $this->db->dbbind(':email', $data['email']);
        $user = $this->db->getsingledata();

        if ($user) {
            $this->db->dbquery("UPDATE users SET facebook_id = :facebook_id, avatar = :avatar WHERE user_id = :id");
            $this->db->dbbind(':facebook_id', $data['facebook_id']);
            $this->db->dbbind(':avatar', $data['avatar']);
            $this->db->dbbind(':id', $user['user_id']);
            $this->db->dbexecute();

            return $this->getuserinfo($data['email']);
        }

        $randomPassword = password_hash(hash('sha256', bin2hex(random_bytes(32))), PASSWORD_DEFAULT);

        $this->db->dbquery("INSERT INTO users(name, email, password, facebook_id, avatar) VALUES(:name, :email, :password, :facebook_id, :avatar)");
        $this->db->dbbind(':name', $data['name']);
        $this->db->dbbind(':email', $data['email']);
        $this->db->dbbind(':password', $randomPassword);
        $this->db->dbbind(':facebook_id', $data['facebook_id']);
        $this->db->dbbind(':avatar', $data['avatar']);
        $this->db->dbexecute();

        return $this->getuserinfo($data['email']);
    }
}



?>
