<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('Asia/Yangon'); 

class Resetpassword extends Controller{ 

    protected $usermodel;
    protected $resetpwmodel;
    protected $userid;
    private $resetpwmailserver;
    private $ip;
    private $ua;
    private $logger;
    private $toEmail;
    private $resettoken;

    public function __construct()
    {
        $this->usermodel = $this->model('User');
        $this->resettoken = new GenerateResetToken();
        $this->resetpwmodel = $this->model('Resetpw');
        $this->resetpwmailserver = new ResetPasswordMailServer();
        if (isset($_SESSION['session_uid'])) {
            $this->userid = $_SESSION['session_uid'];
        } else {
            $this->userid = null;
        }

        $this->logger = new AuthLogger();
        $this->ip = $_SERVER['REMOTE_ADDR'];
        $this->ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

    }

    public function singleresettoken(){
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            try{
                $input = json_decode(file_get_contents("php://input"), true);
                $email = $input['email'];
                if($this->usermodel->registeremailcheck($email)){
                    $tokenPair = $this->resettoken->generateResetToken();
                    $expires = (new DateTime("+1 hour"))->format('Y-m-d H:i:s');

          
                    $storetoken = $this->resetpwmodel->storeresetpwhash($this->userid,$tokenPair['token_hash'],$expires);

                    if($storetoken){
                        $this->resetpwmailserver->resetPwMailServer($email,$tokenPair['token']);
                        echo json_encode(['tokenPair' => $tokenPair]);
                        $this->logger->log([
                            'user_id' => $this->userid,
                            'identifier' => $email,
                            'event_type' => 'ResetToken_success',
                            'ip' => $this->ip,
                            'ua' => $this->ua,
                            'details' => 'Reset Token link sent to email successfully.'
                        ]);
                        exit;

                    }else{

                        echo json_encode(['tokenPair' => "noo"]);
                        $this->logger->log([
                            'user_id' => $this->userid,
                            'identifier' => $email,
                            'event_type' => 'ResetToken_fail',
                            'ip' => $this->ip,
                            'ua' => $this->ua,
                            'details' => 'Fail to sent Reset Token Link.'
                        ]);
                        exit;

                    }
                }else{
                    echo json_encode(['e_registered' => false]);
                    exit;
                }
                exit;
            }catch(Exception $e){
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
                exit;
            }
        }

        $this->view('resetpassword/forgetpw');
    }




    public function resetpassword() {
        $token = $_GET['token'] ?? '';
        $email = $_GET['e'] ?? '';

        if (!$token || !$email) {
            die(json_encode(['status'=>'error','message'=>'Invalid reset link']));
        }

        $this->view('resetpassword/resetpw', [
            'token' => $token,
            'email' => $email
        ]);
    }

// Set New Password
public function setnewpassword() {

    if($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $input = json_decode(file_get_contents("php://input"), true);

            $token = $input['token'] ?? null;
            $email = $input['email'] ?? $_SESSION['session_email'];
            $newPassword = $input['password'] ?? null;

            if (!$token || !$email || !$newPassword) {
                echo json_encode(['status' => 'error', 'message' => 'Missing data']);
                exit;
            }

            // // Verify token
            $token256 = hash('sha256', $token);
            $user = $this->resetpwmodel->getUserByEmailAndToken($email,$token256);
            if (!$user) {
                echo json_encode(['status' => 'inspired', 'message' => 'Invalid or expired token',$email, $token256,'user'=>$user]);
                exit;
            }

            // // Hash new password
            $pw_sha = hash('sha256', $newPassword);
            $hashedPassword = password_hash($pw_sha, PASSWORD_DEFAULT);
            // Update password
            if ($this->resetpwmodel->updatePassword($email, $hashedPassword)) {
                $this->resetpwmodel->deletePasswordResetToken($token);
                echo json_encode(['pw_status' => true, 'message' => 'spend just now']);
                $this->logger->log([
                    'user_id' => $this->userid,
                    'identifier' => $email,
                    'event_type' => 'ResetPw_success',
                    'ip' => $this->ip,
                    'ua' => $this->ua,
                    'details' => 'Forget password was reset successfully.'
                ]);   
                exit;



            } else {
                echo json_encode(['status' => false]);
                $this->logger->log([
                    'user_id' => $this->userid,
                    'identifier' => $email,
                    'event_type' => 'ResetPw_fail',
                    'ip' => $this->ip,
                    'ua' => $this->ua,
                    'details' => 'Fail to rest forget password.'
                ]); 
                exit;
            }


                
     

        } catch(Exception $e) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            exit;
        }
    }
}


}
?>