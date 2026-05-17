<?php
class AuthLogger{
    protected $logmodel;

    public function __construct(){
        require_once APPROOT . '/models/Log.php';
        $this->logmodel = new Log();
    }

    public function log($params){
        $eventType = $params['event_type'];
        $userId = $params['user_id'] ?? null;
        $identifier = $params['identifier'] ?? null;
        $ip = $params['ip'] ?? $_SERVER['REMOTE_ADDR'] ?? null;
        $ua = $params['ua'] ?? $_SERVER['HTTP_USER_AGENT'] ?? null;

        $this->logmodel->createSystemLog([
            'user_id' => $userId,
            'action' => $eventType,
            'ip_address' => $ip,
            'user_agent' => $ua
        ]);

        if ($eventType === 'login_fail' && $identifier) {
            $this->logmodel->recordLoginFail($identifier, $ip);
        }

        if ($eventType === 'login_success' && $identifier) {
            $this->logmodel->clearLoginFails($identifier, $ip);
        }
    }
}

?>
