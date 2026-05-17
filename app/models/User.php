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
            return true;
        } else {
            return false;
        }

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
}



?>
