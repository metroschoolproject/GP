<?php
class Otp{

    private $db;
    public function __construct() {
        $this->db = new Database();
    }

    public function storeotp($otp,$userid,$expires){
        $this->db->dbquery("UPDATE otps SET is_used = 1 WHERE user_id = :id AND type = 'login' AND is_used = 0");
        $this->db->dbbind(':id' ,$userid);
        $this->db->dbexecute();

        $this->db->dbquery("INSERT INTO otps (user_id, code, type, expires_at, attempt_count, max_attempts, is_used) VALUES (:id, :otp, 'login', :exp, 0, :atm, 0)");
        $this->db->dbbind(":id",$userid);
        $this->db->dbbind(":otp",$otp);
        $this->db->dbbind(":exp",$expires);
        $this->db->dbbind(":atm" ,3);
        if( $this->db->dbexecute()){
            return true;
        }else{
            return false;
        }
    }

    public function verifyotp($client_otp,$userid){
        $this->db->dbquery("SELECT id, code, expires_at, attempt_count, max_attempts FROM otps WHERE user_id = :id AND type = 'login' AND is_used = 0 ORDER BY created_at DESC LIMIT 1");
        $this->db->dbbind(':id',$userid);
        $row = $this->db->getsingledata();
        if (!$row) return false;

        if(new DateTime($row['expires_at']) < new DateTime()){
            $this->expired($userid);
        }

        if ($row['attempt_count'] >= $row['max_attempts']) {
            echo json_encode(['atm_status' => false, 'message' => 'No attempts left']);
            exit;
        }

        $otp = $row['code'];
        if(hash_equals($otp, $client_otp)){
            $this->db->dbquery('UPDATE otps SET is_used = 1 WHERE id = :otp_id');
            $this->db->dbbind(':otp_id' ,$row['id']);
            $this->db->dbexecute();
            return true;
        }else{
            $this->db->dbquery("UPDATE otps SET attempt_count = attempt_count + 1 WHERE id = :otp_id");
            $this->db->dbbind(":otp_id",$row['id']);
            $this->db->dbexecute();
            return false;
        }
    }

    public function expired($userid){
        $this->db->dbquery("UPDATE otps SET is_used = 1 WHERE user_id = :id AND type = 'login' AND is_used = 0");
        $this->db->dbbind(':id' ,$userid);
        $this->db->dbexecute();
        echo json_encode(['expire_status' => 'fail', 'message' => 'Expired']);
        exit;
    }
}
?>
