<?php

class EmailVerification
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function storeToken($userId, $tokenHash, $expiresAt)
    {
        $this->db->dbquery('UPDATE email_verifications SET used = 1 WHERE user_id = :user_id AND used = 0');
        $this->db->dbbind(':user_id', (int)$userId);
        $this->db->dbexecute();

        $this->db->dbquery(
            'INSERT INTO email_verifications(user_id, token_hash, expires_at)
             VALUES(:user_id, :token_hash, :expires_at)'
        );
        $this->db->dbbind(':user_id', (int)$userId);
        $this->db->dbbind(':token_hash', $tokenHash);
        $this->db->dbbind(':expires_at', $expiresAt);

        return $this->db->dbexecute();
    }

    public function getValidUser($email, $tokenHash)
    {
        $this->db->dbquery(
            'SELECT users.*
             FROM email_verifications
             INNER JOIN users ON users.user_id = email_verifications.user_id
             WHERE users.email = :email
               AND email_verifications.token_hash = :token_hash
               AND email_verifications.expires_at > NOW()
               AND email_verifications.used = 0
             LIMIT 1'
        );
        $this->db->dbbind(':email', $email);
        $this->db->dbbind(':token_hash', $tokenHash);

        return $this->db->getsingledata();
    }

    public function markVerified($userId, $tokenHash)
    {
        $this->db->dbquery('UPDATE users SET email_verified_at = NOW() WHERE user_id = :user_id');
        $this->db->dbbind(':user_id', (int)$userId);

        if (!$this->db->dbexecute()) {
            return false;
        }

        $this->db->dbquery('UPDATE email_verifications SET used = 1 WHERE user_id = :user_id AND token_hash = :token_hash');
        $this->db->dbbind(':user_id', (int)$userId);
        $this->db->dbbind(':token_hash', $tokenHash);

        return $this->db->dbexecute();
    }
}
