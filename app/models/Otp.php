<?php
class Otp{

    private $db;
    public function __construct() {
        $this->db = new Database();
    }

    public function storeotp($otp_hash,$userid,$expires){
        $this->db->dbquery('DELETE FROM user_otps WHERE user_id = :id');
        $this->db->dbbind(':id' ,$userid);
        $this->db->dbexecute();

        $this->db->dbquery("INSERT INTO user_otps (user_id, otp_hash, expires_at, attempts_left) VALUES (:id, :otp, :exp, :atm)");
        $this->db->dbbind(":id",$userid);
        $this->db->dbbind(":otp",$otp_hash);
        $this->db->dbbind(":exp",$expires);
        $this->db->dbbind(":atm" ,3);
        if( $this->db->dbexecute()){
            return true;
        }else{
            return false;
        }
    }

    public function verifyotp($client_otp,$userid){
        $this->db->dbquery("SELECT otp_hash,expires_at,attempts_left FROM user_otps where user_id = :id");
        $this->db->dbbind(':id',$userid);
        $row = $this->db->getsingledata();
        if (!$row) return false;

        if(new DateTime($row['expires_at']) < new DateTime()){
            expired($userid);
        }

        if ($row['attempts_left'] <= 0) {
            echo json_encode(['atm_status' => false, 'message' => 'No attempts left']);
            exit;
        }

        $otp_hash = $row['otp_hash'];
        if(password_verify($client_otp,$otp_hash)){
            $this->db->dbquery('DELETE FROM user_otps WHERE user_id = :id');
            $this->db->dbbind(':id' ,$userid);
            $this->db->dbexecute();
            return true;
        }else{
            $this->db->dbquery("UPDATE user_otps SET attempts_left = attempts_left - 1 WHERE user_id = :id");
            $this->db->dbbind(":id",$userid);
            $this->db->dbexecute();
            return false;
        }
    }

    public function expired($userid){
        $this->db->dbquery('DELETE FROM user_otps WHERE user_id = :id');
        $this->db->dbbind(':id' ,$userid);
        $this->db->dbexecute();
        echo json_encode(['expire_status' => 'fail', 'message' => 'Expired']);
        exit;
    }
}
?>