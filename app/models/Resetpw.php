<?php
class Resetpw{
    private $db;
    public function __construct() {
        $this->db = new Database();

    }

    public function storeresetpwhash($userid,$tokenHash,$expires){
        $this->db->dbquery('INSERT INTO password_resets(user_id,token_hash,expires_at) VALUES (:id,:token_hash,:exp)');
        $this->db->dbbind(':id' ,$userid);
        $this->db->dbbind(':token_hash',$tokenHash);
        $this->db->dbbind(':exp',$expires);
        if($this->db->dbexecute()){
            return true;
        }else{
            return false;
        }
      
    }

    public function getUserByEmailAndToken($email, $token_hash) {
        $this->db->dbquery("SELECT * FROM password_resets pr
                            JOIN users u ON pr.user_id = u.id
                            WHERE u.email = :email AND pr.token_hash = :token AND pr.expires_at > NOW() AND pr.used = 0");
        $this->db->dbbind(':email', $email);
        $this->db->dbbind(':token', $token_hash);
        if( $this->db->getsingledata()){
            return true;
        }else{
            return false;
        }
    }

    public function updatePassword($email, $hashedPassword) {
        $updatedAt = (new DateTime())->format('Y-m-d H:i:s');

        $this->db->dbquery("UPDATE users SET password = :pw, updated_at = :up WHERE email = :email");
        $this->db->dbbind(':pw', $hashedPassword);
        $this->db->dbbind(':up', $updatedAt);
        $this->db->dbbind(':email', $email);
        if($this->db->dbexecute()){
            return true;
        }else{
            return false;
        }
    }

    public function deletePasswordResetToken($token) {
        $this->db->dbquery("UPDATE password_resets SET used = 1 WHERE token_hash = :token");
        $this->db->dbbind(':token', hash('sha256', $token));
        if( $this->db->dbexecute()){
            return true;
        }else{
            return false;
        }
    }

}
?>