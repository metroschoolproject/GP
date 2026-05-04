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


    public function register()
    {


        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                header("Content-Type: application/json; charset=UTF-8");
                $input = json_decode(file_get_contents("php://input"), true);

                if (!$input) {
                    throw new Exception("Invalid JSON sent from frontend.");
                }

                $data = [
                    'username' => htmlspecialchars(trim($input['username'] ?? ''), ENT_QUOTES, 'UTF-8'),
                    'email' => htmlspecialchars(trim($input['email'] ?? ''), ENT_QUOTES, 'UTF-8'),
                    'password' => trim($input['password'] ?? ''),
                    'compassword' => trim($input['compassword'] ?? ''),
                ];


                $pw_sha = hash('sha256',$data['password']);
                $data['password'] = password_hash($pw_sha, PASSWORD_DEFAULT);

                // Call model
                if (!$this->usermodel->registeremailcheck($data['email'])) {
                    if ($this->usermodel->register($data)) {

                        // if i add this code, it shows bad request 

                        $this->logger->log([
                            
                            'identifier' => $input['email'],
                            'event_type' => 'register_success',
                            'ip' => $this->ip,
                            'ua' => $this->ua,
                            'details' => 'Register done successful'
                        ]);
                        echo json_encode(['status' => 'success', 'redirect' => 'login','data' => $data]);
                        
                        exit;

                    } else {
                        $this->logger->log([
                            'user_id' => $this->userid,
                            'identifier' => $input['email'],
                            'event_type' => 'register_fail',
                            'ip' => $this->ip,
                            'ua' => $this->ua,
                            'details' => 'Register Fail'
                        ]);
                        echo json_encode(['status' => 'success', 'register' => 'fail']);
                        
                        exit;
                    }
                } else {
                    echo json_encode([
                        'email' => true,
                        'data' => $data
                    ]);
                    exit;
                }

            } catch (Exception $e) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            }

            return;
        }

        $this->view('users/register');

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

                $user = $this->usermodel->getuserinfo($input['email']);
                $now = new DateTime();

                if($this->usermodel->registeremailcheck($input['email'])){

                    if ($user) {
                        if (!empty($user['locked_until'])) {
                            $lockedUntil = new DateTime($user['locked_until']);
                            if ($lockedUntil > $now) {
                                // lock
                                echo json_encode([
                                    'status' => 'lock',
                                    'lock' => "your account locked",
                                    "lockedUntil" => new DateTime($user['locked_until']),
                                ]);

                                exit;
                            }
                        
                        }
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

        $this->view('users/login');


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

                // verify reset token
                $verifyres = $this->usermodel->login($data);
                if($verifyres){
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
        $this->view('users/login');

    }


    public function loginfailhandle($email){
        $loginfail_count = $this->logmodel->detect_loginfail($email);
        $tokenPair = $this->resettoken->generateResetToken();        
        $expires = (new DateTime("+1 hour"))->format('Y-m-d H:i:s');

        $storetoken = $this->resetpwmodel->storeresetpwhash($this->userid,$tokenPair['token_hash'],$expires);
        $attemptime = 5;
        if ($loginfail_count['loginfails'] >= $attemptime && $storetoken) {
            
            $this->loginalertmail->LoginFailAlertMailServer($email,$tokenPair['token']);
            echo json_encode(['loginfailover' => true,$loginfail_count['loginfails']]);
            exit;
        } else {
            echo json_encode(['loginfailnotyet' => true,"pwd"=> false, $loginfail_count['loginfails']]);
            exit;
        }
    }




    public function logout()
    {
        unset($_SESSION['user_id']);
        unset($_SESSION['user_name']);
        unset($_SESSION['user_email']);

        session_destroy();

        redirect('users/login');
    }


}



?>