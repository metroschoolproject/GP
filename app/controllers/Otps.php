<?php
ini_set('display_errors', 1);
date_default_timezone_set('Asia/Yangon'); 

class Otps extends Controller{ 
    private $usermodel;
    private $otpmodel;
    private $otpmailserver;
    private $userid;
    private $ip;
    private $ua;
    private $logger;
    private $toEmail;

    private $logmodel;
    private $otpalertmail;
    public function __construct()
    {
        $this->usermodel = $this->model('User');
        $this->otpmodel = $this->model('Otp');
        $this->otpmailserver = new OtpMailServer();
        if (isset($_SESSION['session_uid'])) {
            $this->userid = $_SESSION['session_uid'];
        } else {
            // Handle not logged-in case safely
            $this->userid = null;
        }
        $this->toEmail = $_SESSION['session_email'];

        $this->logger = new AuthLogger();
        $this->ip = $_SERVER['REMOTE_ADDR'];
        $this->ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

        $this->logmodel = $this->model('Log');
        $this->otpalertmail = new AttemptFailAlertMailServer();
    }

    public function otp(){
        if($_SERVER['REQUEST_METHOD'] !== 'POST'){
            $this->view('otps/otp');
            return;
        }

        header('Content-Type: application/json');

        $otp = $this->generateOtpCode(6);
        $expires = (new DateTime("+1 minutes"))->format('Y-m-d H:i:s');
        $storeotp = $this->otpmodel->storeotp($otp,$this->userid,$expires);

        if($storeotp){
            $this->otpmailserver->otpMailServer($this->toEmail,$otp);
            $this->logger->log([
                'user_id' => $this->userid,
                'identifier' => $this->toEmail,
                'event_type' => 'sendingOTP_success',
                'ip' => $this->ip,
                'ua' => $this->ua,
                'details' => 'OTP sent to email successfully.'
            ]);
            echo json_encode(['status' => true]);
            exit;
        }else{
            $this->logger->log([
                'user_id' => $this->userid,
                'identifier' => $this->toEmail,
                'event_type' => 'sendingOTP_fail',
                'ip' => $this->ip,
                'ua' => $this->ua,
                'details' => 'Fail to send OTP'
            ]);
            echo json_encode(['status' => false]);
            exit;
        }
    }

    public function generateOtpCode(int $digits = 6):string {
        $min = (int) pow(10,$digits-1);
        $max = (int) (pow(10,$digits) - 1);
        return (string) random_int($min,$max);
    }

    // Verify OTP.. IF success, User Authanticate
    public function otpVerify(){
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            try {
                $input = json_decode(file_get_contents("php://input"), true);
                $client_otp = $input['otp'];
                $verifyotp = $this->otpmodel->verifyotp($client_otp,$this->userid);
                if($verifyotp){
                    if (!empty($_SESSION['pending_remember_me'])) {
                        issueRememberMeCookie($this->usermodel, $this->userid);
                    }
                    unset($_SESSION['pending_remember_me']);

                    $this->logger->log([
                        'user_id' => $this->userid,
                        'identifier' => $this->toEmail,
                        'event_type' => 'verifyOTP_success',
                        'ip' => $this->ip,
                        'ua' => $this->ua,
                        'details' => 'OTP verifies successfully'
                    ]);
                    echo json_encode([
                        'otp_try_status' => true,
                        'redirect' => $_SESSION['post_login_redirect'] ?? 'main/home'
                    ]);
                    exit;
                }else{
                    $this->logger->log([
                        'user_id' => $this->userid,
                        'identifier' => $this->toEmail,
                        'event_type' => 'verifyOTP_fail',
                        'ip' => $this->ip,
                        'ua' => $this->ua,
                        'details' => 'Fail to verify OTP' 
                    ]);

                    $this->otpfailhandle();
                    echo json_encode(['otp_try_status' => false]);
                    exit;
                }
            }catch(Exception $e){
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            }
        }
    }

    public function otpfailhandle(){
        $otpfail_count = $this->logmodel->detect_otpfail($this->toEmail);
        if($otpfail_count['otpfails'] >= 3){
            $this->otpalertmail->OTPFailAlertMailServer($this->toEmail);

            // lock account until 15 minutes
            $lockTime = new DateTime('+15 minutes');
            $this->usermodel->lockaccount($this->toEmail, $lockTime->format('Y-m-d H:i:s'));
            $this->logmodel->createAccountLockoutLog([
                'user_id' => $this->userid,
                'event' => 'locked',
                'reason' => 'otp_attempts',
                'attempt_count' => $otpfail_count['otpfails'],
                'locked_until' => $lockTime->format('Y-m-d H:i:s'),
                'ip_address' => $this->ip
            ]);
            $this->logger->log([
                'user_id' => $this->userid,
                'identifier' => $this->toEmail,
                'event_type' => 'lock_account',
                'ip' => $this->ip,
                'ua' => $this->ua,
                'details' => 'Lock Account 15 minutes because user try to attempt over 3 times. it will unlock after 15 minutes' 
            ]);
            echo json_encode(['otp_fail' => true]);
            exit;
        }
    }



}

?>
