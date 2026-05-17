<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('Asia/Yangon'); 

class Users extends Controller
{
    protected $usermodel;
    private $logger;
    private $ip;
    private $ua;
    private $userid;
    private $logmodel;
    private $loginalertmail;
    private $resettoken;
    private $resetpwmodel;

    public function __construct()
    {
        $this->usermodel = $this->model('User');
        $this->resettoken = new GenerateResetToken();
        $this->resetpwmodel = $this->model('Resetpw');

        if (isset($_SESSION['session_uid'])) {
            $this->userid = $_SESSION['session_uid'];
        } else {
            // Handle not logged-in case safely
            $this->userid = null;
        }
        $this->logger = new AuthLogger();
        $this->ip = $_SERVER['REMOTE_ADDR'];
        $this->ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

        $this->logmodel = $this->model('Log');

        $this->loginalertmail = new AttemptFailAlertMailServer();

    }
    // 1Aa@23456

private function getPasswordLockResponse($email)
{
    $user = $this->usermodel->getuserinfo($email);
    $now = new DateTime();

    if ($user && !empty($user['locked_until'])) {
        $lockedUntil = new DateTime($user['locked_until']);
        if ($lockedUntil > $now) {
            return [
                'status' => 'lock',
                'lock' => 'your account locked',
                'lockedUntil' => ['date' => $lockedUntil->format('Y-m-d H:i:s')]
            ];
        }

        if (($user['lock_reason'] ?? '') === 'password_attempts') {
            $this->usermodel->clearpasswordlock($email);
            $this->logmodel->createAccountLockoutLog([
                'user_id' => $user['user_id'],
                'event' => 'unlocked',
                'reason' => 'password_attempts',
                'attempt_count' => $user['failed_password_attempts'] ?? 0,
                'ip_address' => $this->ip
            ]);
        }
    }

    $attemptLock = $this->logmodel->getLoginLock($email);
    if (!empty($attemptLock['locked_until'])) {
        return [
            'status' => 'lock',
            'lock' => 'your account locked',
            'lockedUntil' => ['date' => (new DateTime($attemptLock['locked_until']))->format('Y-m-d H:i:s')]
        ];
    }

    return null;
}


public function register()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            // header("Content-Type: application/json; charset=UTF-8");

            // $input = json_decode(file_get_contents("php://input"), true);

            // if (!is_array($input)) {
            //     throw new Exception("Invalid JSON sent from frontend.");
            // }
            $rawInput = file_get_contents("php://input");

            $input = json_decode($rawInput, true);

            /*
            |--------------------------------------------------------------------------
            | Fallback Support
            |--------------------------------------------------------------------------
            | Some localhost/PHP setups fail to decode JSON correctly.
            | This fallback prevents 400 Bad Request.
            |--------------------------------------------------------------------------
            */

            if (!is_array($input)) {
                $input = $_POST;
            }

            /*
            |--------------------------------------------------------------------------
            | Final Validation
            |--------------------------------------------------------------------------
            */

            if (!is_array($input) || empty($input)) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'No input data received',
                    'raw' => $rawInput
                ]);
                exit;
            }

            $username = $input['username'] ?? $input['name'] ?? '';
            $password = $input['password'] ?? '';
            $compassword = $input['compassword'] ?? $input['confirm_password'] ?? '';

            $data = [
                'username' => htmlspecialchars(trim($username), ENT_QUOTES, 'UTF-8'),
                'email' => htmlspecialchars(trim($input['email'] ?? ''), ENT_QUOTES, 'UTF-8'),
                'password' => trim($password),
                'compassword' => trim($compassword),
            ];

            if (
                empty($data['username']) ||
                empty($data['email']) ||
                empty($data['password']) ||
                empty($data['compassword'])
            ) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'All fields are required'
                ]);
                exit;
            }

            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Invalid email format'
                ]);
                exit;
            }

            if ($data['password'] !== $data['compassword']) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Passwords do not match'
                ]);
                exit;
            }

            if ($this->usermodel->registeremailcheck($data['email'])) {
                echo json_encode([
                    'email' => true
                ]);
                exit;
            }

            $pw_sha = hash('sha256', $data['password']);
            $data['password'] = password_hash($pw_sha, PASSWORD_DEFAULT);

            if ($this->usermodel->register($data)) {

                try {
                    $this->logger->log([
                        'user_id' => $this->userid,
                        'identifier' => $data['email'],
                        'event_type' => 'register_success',
                        'ip' => $this->ip,
                        'ua' => $this->ua,
                        'details' => 'Register done successful'
                    ]);
                } catch (Exception $logError) {
                    // Do not break register if logging fails
                }

                echo json_encode([
                    'status' => 'success',
                    'redirect' => 'login'
                ]);
                exit;

            } else {

                try {
                    $this->logger->log([
                        'user_id' => $this->userid,
                        'identifier' => $data['email'],
                        'event_type' => 'register_fail',
                        'ip' => $this->ip,
                        'ua' => $this->ua,
                        'details' => 'Register Fail'
                    ]);
                } catch (Exception $logError) {
                    // Do not break response if logging fails
                }

                echo json_encode([
                    'status' => 'error',
                    'register' => 'fail'
                ]);
                exit;
            }

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
            exit;
        }
    }

    $this->view('users/auth');
}

// Get Email From User to Server
    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $input = json_decode(file_get_contents("php://input"), true);

                if (!$input) {
                    throw new Exception("Invalid JSON sent from frontend.");
                }

                $data = [
                    'email' => htmlspecialchars(trim($input['email'] ?? ''), ENT_QUOTES, 'UTF-8')                    // 'password' => trim($input['password'] ?? ''),
                ];
         
          
                
                // ALREADY WORK!!!!!!

                if($this->usermodel->registeremailcheck($input['email'])){
                    $lockResponse = $this->getPasswordLockResponse($input['email']);
                    if ($lockResponse) {
                        echo json_encode($lockResponse);
                        exit;
                    }
	        

                    // no lock
                    $challenge = $this->usermodel->getchallenge($data['email']);
                    echo json_encode([
                        'status' => 'success',
                        'challenge' => $challenge
                        
                    ]);
                    exit;
          
                }else{
                    echo json_encode([
                        'status' => 'accountnotfound',                               
                    ]);
                    exit;
                }
             

              
             
                
                

            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            }

            return;
        }

        $this->view('users/auth');


    }

    // Verify Challenge Code, If success, Authanticated!
    public function verifyChallenge(){
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            try {
                $input = json_decode(file_get_contents("php://input"), true);

                if (!$input) {
                    throw new Exception("Invalid JSON sent from frontend.");
                }

                $data = [
                    'email' => htmlspecialchars(trim($input['email'] ?? ''), ENT_QUOTES, 'UTF-8'),                    
                    'pw_sha' => htmlspecialchars(trim($input['pw_sha'] ?? '')),
                    'response' => htmlspecialchars(trim($input['res_code'] ?? ''))
                ];

                $lockResponse = $this->getPasswordLockResponse($data['email']);
                if ($lockResponse) {
                    echo json_encode($lockResponse);
                    exit;
                }

                // verify reset token
                $verifyres = $this->usermodel->login($data);
                if($verifyres){
                    $this->usermodel->markloginsuccess($data['email']);
                    $this->userid = $_SESSION['session_uid'] ?? $this->userid;
                    $this->logger->log([
                        'user_id' => $this->userid,
                        'identifier' => $input['email'],
                        'event_type' => 'login_success',
                        'ip' => $this->ip,
                        'ua' => $this->ua,
                        'details' => 'Authenticated via challenge-response'
                    ]);
                    echo json_encode(['status' => $verifyres, 'redirect' => 'otps/otp']);
                    exit;
                }else{
                    $this->logger->log([
                        'user_id' => $this->userid,
                        'identifier' => $input['email'],
                        'event_type' => 'login_fail', 
                        'ip' => $this->ip,
                        'ua' => $this->ua,
                        'details' => 'Invalid credentials or challenge mismatch'
                    ]);



                    //If Login Failed Over 3 Between 15 Minutes , Send Email Alert
                    // ::::::::: when login it has error                 
                    $this->loginfailhandle($input['email']);
                    echo json_encode(['status' => false, 'redirect' => 'none']);
                    exit ;
                }

            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            }

            return;
        }
        $this->view('users/auth');

    }


    public function loginfailhandle($email){
        $this->usermodel->recordpasswordfail($email);
        $loginfail_count = $this->logmodel->detect_loginfail($email);
        $attemptime = 3;
        if ($loginfail_count['loginfails'] >= $attemptime) {
            $lockTime = new DateTime('+15 minutes');
            $this->usermodel->lockaccount($email, $lockTime->format('Y-m-d H:i:s'));
            $user = $this->usermodel->getuserinfo($email);
            if ($user) {
                $this->logmodel->createAccountLockoutLog([
                    'user_id' => $user['user_id'],
                    'event' => 'locked',
                    'reason' => 'password_attempts',
                    'attempt_count' => $loginfail_count['loginfails'],
                    'locked_until' => $lockTime->format('Y-m-d H:i:s'),
                    'ip_address' => $this->ip
                ]);
            }
            $this->loginalertmail->LoginFailAlertMailServer($email);
            echo json_encode([
                'status' => 'lock',
                'loginfailover' => true,
                'lockedUntil' => ['date' => $lockTime->format('Y-m-d H:i:s')],
                'loginfails' => $loginfail_count['loginfails']
            ]);
            exit;
        } else {
            echo json_encode(['loginfailnotyet' => true,"pwd"=> false, $loginfail_count['loginfails']]);
            exit;
        }
    }




    public function logout()
    {
        $userid = $_SESSION['session_uid'] ?? null;
        if ($userid) {
            $this->usermodel->marklogout($userid);
            $this->logmodel->markSystemLogout($userid);
            $this->logger->log([
                'user_id' => $userid,
                'identifier' => $_SESSION['session_email'] ?? null,
                'event_type' => 'logout',
                'ip' => $this->ip,
                'ua' => $this->ua,
                'details' => 'User logged out'
            ]);
        }

        unset($_SESSION['session_uid']);
        unset($_SESSION['session_email']);
        unset($_SESSION['user_id']);
        unset($_SESSION['user_name']);
        unset($_SESSION['user_email']);

        session_destroy();

        redirect('users/login');
    }

    public function authlogin(){
        redirect('users/auth_login');
    }

}



?>
