<?php
class Log{

    private $db;
    public function __construct()
    {
        $this->db = new Database();
    }

    public function detect_loginfail($email){
        $this->db->dbquery("SELECT COUNT(*) AS loginfails FROM auth_audit WHERE identifier = :email AND event_type = 'login_fail' AND created_at > (NOW() - INTERVAL 15 MINUTE)");
        $this->db->dbbind(':email', $email);
        $row = $this->db->getsingledata(); 

        return $row;
    }

    public function detect_otpfail($email){
        $this->db->dbquery("SELECT COUNT(*) AS otpfails FROM auth_audit WHERE identifier = :email AND event_type = 'verifyOTP_fail' AND created_at > (NOW() - INTERVAL 10 MINUTE)");
        $this->db->dbbind(':email', $email);
        $row = $this->db->getsingledata(); 

        return $row;
    }

}


?>