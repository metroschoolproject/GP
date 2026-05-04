<?php
class AuthLogger{
    protected $db;
    public function __construct(){
        $this->db = new Database();
    }

    public function log($params){
        $this->db->dbquery("
           INSERT INTO auth_audit (user_id, identifier, event_type, ip_address, user_agent, details)
           VALUES (:user_id, :identifier, :event_type, :ip, :ua, :details)
        ");
        $this->db->dbbind(':user_id', $params['user_id'] ?? null);
        $this->db->dbbind(':identifier', $params['identifier'] ?? null);
        $this->db->dbbind(':event_type', $params['event_type']);
        $this->db->dbbind(':ip', $params['ip'] ?? $_SERVER['REMOTE_ADDR'] ?? null);
        $this->db->dbbind(':ua', $params['ua'] ?? $_SERVER['HTTP_USER_AGENT'] ?? null);
        $this->db->dbbind(':details', $params['details'] ?? null);
        $this->db->dbexecute();
    }

}

?>