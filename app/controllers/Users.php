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
    private $emailverificationmodel;
    private $emailverificationmail;

    public function __construct()
    {
        $this->usermodel = $this->model('User');
        $this->resettoken = new GenerateResetToken();
        $this->resetpwmodel = $this->model('Resetpw');
        $this->emailverificationmodel = $this->model('EmailVerification');
        $this->emailverificationmail = new EmailVerificationMailServer();

        if (isset($_SESSION['session_uid'])) {
            $this->userid = $_SESSION['session_uid'];
        } else {
            // Handle not logged-in case safely
            $this->userid = null;
        }
        $this->logger = new AuthLogger();
        $this->ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
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
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
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

            // Block registration on the internal (admin/staff) portal
            if (($input['type'] ?? '') === 'internal') {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Registration is not allowed on this portal.'
                ]);
                exit;
            }

            $username = $input['username'] ?? $input['name'] ?? '';
            $password = $input['password'] ?? '';
            $compassword = $input['compassword'] ?? $input['confirm_password'] ?? '';

            $allowedRoles = ['customer', 'supplier'];
            $requestedRole = $input['role'] ?? $input['type'] ?? '';
            $role = in_array($requestedRole, $allowedRoles, true) ? $requestedRole : 'customer';

            $data = [
                'username' => htmlspecialchars(trim($username), ENT_QUOTES, 'UTF-8'),
                'email' => htmlspecialchars(trim($input['email'] ?? ''), ENT_QUOTES, 'UTF-8'),
                'password' => trim($password),
                'compassword' => trim($compassword),
                'role' => $role,
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

            // Enforce minimum password strength (at least "Fair" = score 2)
            $pwScore = 0;
            if (strlen($data['password']) >= 8) $pwScore++;
            if (preg_match('/[A-Z]/', $data['password'])) $pwScore++;
            if (preg_match('/[0-9]/', $data['password'])) $pwScore++;
            if (preg_match('/[^A-Za-z0-9]/', $data['password'])) $pwScore++;
            if ($pwScore < 2) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Password is too weak. Include uppercase letters, numbers, or symbols.'
                ]);
                exit;
            }

            try {
                $this->usermodel->cleanupStaleUnverifiedPublicAccounts(7);
            } catch (Exception $cleanupError) {
                // Registration should still work if a cleanup query cannot run.
            }

            $allowDuplicateRegisterEmails = defined('ALLOW_DUPLICATE_REGISTER_EMAILS') && ALLOW_DUPLICATE_REGISTER_EMAILS;
            if (!$allowDuplicateRegisterEmails && $this->usermodel->registeremailcheck($data['email'])) {
                echo json_encode([
                    'email' => true
                ]);
                exit;
            }

            $pw_sha = hash('sha256', $data['password']);
            $data['password'] = password_hash($pw_sha, PASSWORD_DEFAULT);

            $registeredUserId = $this->usermodel->register($data);

            if ($registeredUserId) {
                $this->usermodel->assignRole($registeredUserId, $role);

                try {
                    $this->logger->log([
                        'user_id' => $registeredUserId,
                        'identifier' => $data['email'],
                        'event_type' => 'register_success',
                        'ip' => $this->ip,
                        'ua' => $this->ua,
                        'details' => 'Register done successful'
                    ]);
                } catch (Exception $logError) {
                    // Do not break register if logging fails
                }

                $_SESSION['pending_register_user_id'] = $registeredUserId;
                $_SESSION['pending_register_email'] = $data['email'];
                $_SESSION['pending_register_name'] = $data['username'];
                $_SESSION['pending_register_role'] = $role;

                $tokenPair = $this->resettoken->generateResetToken();
                $expires = (new DateTime('+24 hours'))->format('Y-m-d H:i:s');
                $tokenStored = $this->emailverificationmodel->storeToken($registeredUserId, $tokenPair['token_hash'], $expires);
                $mailSent = $tokenStored && $this->emailverificationmail->sendVerificationEmail($data['email'], $tokenPair['token']);

                if (!$mailSent) {
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Account created, but verification email could not be sent.'
                    ]);
                    exit;
                }

                echo json_encode([
                    'status' => 'success',
                    'role' => $role,
                    'redirect' => 'users/verificationSent?e=' . urlencode($data['email'])
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
    public function auth()
    {
        // Store return URL from query param so user goes back after login.
        // The frontend passes window.location.pathname (e.g. /GP/customerServices/detail/56).
        // We strip the leading / and the GP/ base path prefix so it becomes
        // customerServices/detail/56 — which URLROOT/redirect will resolve correctly.
        $redirect = trim((string)($_GET['redirect'] ?? ''));
        if ($redirect !== '' && $redirect[0] === '/' && strpos($redirect, 'http') !== 0) {
            // Remove leading slash and optional GP/ (or bare GP) prefix
            $clean = ltrim($redirect, '/');
            if ($clean === 'GP') {
                $clean = '';
            } elseif (strpos($clean, 'GP/') === 0) {
                $clean = substr($clean, 3);
            }
            if ($clean !== '') {
                $_SESSION['post_login_return_url'] = $clean;
            }
        }
        $this->view('users/auth');
    }

    public function verificationSent()
    {
        $this->view('users/verification_sent', [
            'email' => htmlspecialchars(trim($_GET['e'] ?? ''), ENT_QUOTES, 'UTF-8')
        ]);
    }

    /**
     * JSON — Resend verification email.
     * POST body: { "email": "..." }
     */
    public function resendVerification()
    {
        header('Content-Type: application/json');

        $payload = json_decode(file_get_contents('php://input'), true);
        $email = trim($payload['email'] ?? '');

        if (empty($email)) {
            echo json_encode(['ok' => false, 'message' => 'Email address is required.']);
            return;
        }

        $user = $this->usermodel->getuserinfo($email);
        if (!$user) {
            // Don't reveal whether the email exists
            echo json_encode(['ok' => true, 'message' => 'If an account with that email exists, a verification link has been sent.']);
            return;
        }

        if (!empty($user['email_verified_at'])) {
            echo json_encode(['ok' => true, 'message' => 'Your email is already verified. Please sign in.']);
            return;
        }

        try {
            $tokenPair = $this->resettoken->generateResetToken();
            $expires = (new DateTime('+24 hours'))->format('Y-m-d H:i:s');
            $tokenStored = $this->emailverificationmodel->storeToken($user['user_id'], $tokenPair['token_hash'], $expires);
            $mailSent = $tokenStored && $this->emailverificationmail->sendVerificationEmail($email, $tokenPair['token']);

            if ($mailSent) {
                echo json_encode(['ok' => true, 'message' => 'Verification email sent! Check your inbox.']);
            } else {
                echo json_encode(['ok' => false, 'message' => 'Could not send verification email. Please try again later.']);
            }
        } catch (\Exception $e) {
            echo json_encode(['ok' => false, 'message' => 'An error occurred. Please try again later.']);
        }
    }

    public function verifyEmail()
    {
        $email = htmlspecialchars(trim($_GET['e'] ?? ''), ENT_QUOTES, 'UTF-8');
        $token = trim($_GET['token'] ?? '');
        $tokenHash = $token !== '' ? hash('sha256', $token) : '';
        $user = $email !== '' && $tokenHash !== ''
            ? $this->emailverificationmodel->getValidUser($email, $tokenHash)
            : false;

        if (!$user || !$this->emailverificationmodel->markVerified($user['user_id'], $tokenHash)) {
            $this->view('users/email_verified', [
                'verified' => false,
                'redirect' => 'users/auth',
                'message' => 'This verification link is invalid or expired.'
            ]);
            return;
        }

        // Check if this user has the supplier role
        $roles = $this->usermodel->getUserRoles($user['user_id']);
        $isSupplier = in_array('supplier', $roles, true);
        $isAdmin = in_array('admin', $roles, true);

        if ($isSupplier && !$isAdmin) {
            // Supplier — do NOT create a full session (guest mode).
            // Clear any existing remember-me cookie so session isn't restored.
            if (defined('REMEMBER_ME_COOKIE') && !empty($_COOKIE[REMEMBER_ME_COOKIE])) {
                forgetRememberMeCookie();
            }
            // Destroy any existing session and start fresh with minimal data.
            session_regenerate_id(true);
            $_SESSION = [];
            $_SESSION['pending_register_user_id'] = $user['user_id'];
            $_SESSION['pending_register_email'] = $user['email'];
            $_SESSION['pending_register_name'] = $user['name'] ?? '';
            $_SESSION['pending_register_role'] = 'supplier';

            $this->view('users/email_verified', [
                'verified' => true,
                'redirect' => null,
                'isPendingSupplier' => true,
                'message' => 'Your email has been verified! Your supplier application is pending admin approval. You will be able to log in and access your dashboard once approved.'
            ]);
            return;
        }

        // Customer / admin — log in normally
        $_SESSION['session_uid'] = $user['user_id'];
        $_SESSION['session_email'] = $user['email'];
        $_SESSION['session_name'] = $user['name'] ?? '';
        // Cache role (supplier already excluded above, so this is customer or admin)
        $roles = $this->usermodel->getUserRoles($user['user_id']);
        $_SESSION['session_role'] = in_array('admin', $roles, true) ? 'admin' : 'customer';

        $redirect = $this->getPostLoginRedirect($user['user_id']);

        $this->view('users/email_verified', [
            'verified' => true,
            'redirect' => $redirect,
            'message' => 'Your email is verified. Your account is ready.'
        ]);
    }

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
                    $user = $this->usermodel->getuserinfo($data['email']);

                    if ($user && $this->usermodel->passwordLoginNeedsEmailVerification($user)) {
                        echo json_encode([
                            'status' => 'email_unverified',
                            'email' => $data['email']
                        ]);
                        exit;
                    }

                    // Block moderated accounts (suspended / banned / soft-deleted).
                    // 'locked' is a timed lock handled separately below, so it is excluded here.
                    if ($user && (!empty($user['deleted_at']) || in_array(($user['status'] ?? ''), ['suspended', 'banned'], true))) {
                        echo json_encode([
                            'status' => 'account_blocked',
                            'message' => 'Your account has been ' . (!empty($user['deleted_at']) ? 'deactivated' : $user['status']) . '. Please contact support.'
                        ]);
                        exit;
                    }

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
                    $this->logmodel->clearLoginFails($data['email'], $this->ip);
                    $this->userid = $_SESSION['session_uid'] ?? $this->userid;
                    $_SESSION['post_login_redirect'] = $this->getPostLoginRedirect($this->userid);
                    $_SESSION['pending_remember_me'] = !empty($input['remember_me']);
                    $this->logger->log([
                        'user_id' => $this->userid,
                        'identifier' => $input['email'],
                        'event_type' => 'login_information_correct',
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
                        'event_type' => 'login_information_fail', 
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
        $this->logmodel->recordLoginFail($email, $this->ip);
        $loginfail_count = $this->logmodel->detect_loginfail($email);
        $attemptime = 3;
        if ((int)$loginfail_count['loginfails'] >= $attemptime) {
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
                'loginfails' => (int)$loginfail_count['loginfails'],
                'attempt_count' => (int)$loginfail_count['loginfails'],
                'max_attempts' => $attemptime,
                'remaining_attempts' => 0
            ]);
            exit;
        } else {
            $attemptCount = (int)$loginfail_count['loginfails'];
            echo json_encode([
                'loginfailnotyet' => true,
                'pwd' => false,
                'attempt_count' => $attemptCount,
                'max_attempts' => $attemptime,
                'remaining_attempts' => max(0, $attemptime - $attemptCount)
            ]);
            exit;
        }
    }




    public function logout()
    {
        $userid = $_SESSION['session_uid'] ?? null;
        $email = $_SESSION['session_email'] ?? $_SESSION['pending_register_email'] ?? null;

        if ($userid) {
            try {
                $this->usermodel->clearRememberToken($userid);
            } catch (Exception $rememberError) {
                // Continue logout even if remember-token cleanup fails.
            }

            try {
                $this->usermodel->marklogout($userid);
                $this->logmodel->markSystemLogout($userid);
                $this->logger->log([
                    'user_id' => $userid,
                    'identifier' => $email,
                    'event_type' => 'logout',
                    'ip' => $this->ip,
                    'ua' => $this->ua,
                    'details' => 'User logged out'
                ]);
            } catch (Exception $logError) {
                // Logging should not prevent the session from ending.
            }
        }

        $_SESSION = [];

        forgetRememberMeCookie();

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $params['path'],
                'domain' => $params['domain'],
                'secure' => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => $params['samesite'] ?? 'Lax',
            ]);
        }

        session_destroy();

        redirect('users/login');
    }

    public function authlogin(){
        redirect('users/auth_login');
    }

    private function getPostLoginRedirect($userId)
    {
        // If the user was trying to add to cart before login, go to cart
        if (!empty($_SESSION['cart_redirect_after_login'])) {
            $redirect = $_SESSION['cart_redirect_after_login'];
            unset($_SESSION['cart_redirect_after_login']);
            return $redirect;
        }

        // If the user was on a specific page and clicked "Sign in to book", go back
        if (!empty($_SESSION['post_login_return_url'])) {
            $returnUrl = $_SESSION['post_login_return_url'];
            unset($_SESSION['post_login_return_url']);
            return $returnUrl;
        }

        $roles = $this->usermodel->getUserRoles($userId);

        if (in_array('admin', $roles, true)) {
            return 'admin/dashboard';
        }

        if (in_array('supplier', $roles, true)) {
            $supplierProfileModel = $this->model('SupplierProfile');
            $supplier = $supplierProfileModel->getByUserId($userId);

            if ($supplier && strtolower($supplier['status'] ?? '') === 'pending') {
                return 'supplier/pending';
            }

            if ($supplier && in_array(strtolower($supplier['status'] ?? ''), ['approved', 'verified'], true)) {
                return 'supplier/dashboard';
            }

            return 'supplier/onboarding';
        }

        return 'main/home';
    }


    private function getPublicOauthIntent()
    {
        return ($_GET['type'] ?? '') === 'supplier' ? 'supplier' : 'customer';
    }

    private function finishSocialLogin($user)
    {
        $allowedRoles = ['customer', 'supplier'];
        $requestedRole = $_SESSION['oauth_intent'] ?? 'customer';
        $role = in_array($requestedRole, $allowedRoles, true) ? $requestedRole : 'customer';

        $this->usermodel->assignRole($user['user_id'], $role);
        $this->usermodel->markEmailVerified($user['user_id']);

        $_SESSION['session_uid'] = $user['user_id'];
        $_SESSION['session_email'] = $user['email'];
        $_SESSION['session_name'] = $user['name'] ?? '';
        $_SESSION['session_role'] = $role;

        if ($role === 'supplier') {
            $_SESSION['pending_register_user_id'] = $user['user_id'];
            $_SESSION['pending_register_email'] = $user['email'];
            $_SESSION['pending_register_name'] = $user['name'] ?? '';
            $_SESSION['pending_register_role'] = 'supplier';

            redirect($this->getPostLoginRedirect($user['user_id']));
        }

        $_SESSION['login_success_flash'] = true;
        redirect('main/home');
    }


    // Google 
    public function google()
    {
        $client = new Google_Client();
        $client->setClientId(GOOGLE_CLIENT_ID);
        $client->setClientSecret(GOOGLE_CLIENT_SECRET);
        $client->setRedirectUri(GOOGLE_REDIRECT_URI);
        $client->addScope('email');
        $client->addScope('profile');

        $_SESSION['oauth_intent'] = $this->getPublicOauthIntent();
        $_SESSION['oauth_state'] = bin2hex(random_bytes(16));
        $client->setState($_SESSION['oauth_state']);

        redirect($client->createAuthUrl());
    }

    public function googleCallback()
    {
        if (isset($_GET['error'])) {
            exit('Google login error: ' . htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8'));
        }

        if (!isset($_GET['code'])) {
            exit('Google login failed: authorization code missing.');
        }

        if (!isset($_GET['state']) || $_GET['state'] !== ($_SESSION['oauth_state'] ?? '')) {
            exit('Invalid Google login state');
        }

        $client = new Google_Client();
        $client->setClientId(GOOGLE_CLIENT_ID);
        $client->setClientSecret(GOOGLE_CLIENT_SECRET);
        $client->setRedirectUri(GOOGLE_REDIRECT_URI);

        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        if (isset($token['error'])) {
            $message = $token['error_description'] ?? $token['error'];
            exit('Google token error: ' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));
        }

        $client->setAccessToken($token);

        $oauth = new Google_Service_Oauth2($client);
        $googleUser = $oauth->userinfo->get();

        $user = $this->usermodel->findOrCreateGoogleUser([
            'google_id' => $googleUser->id,
            'email' => $googleUser->email,
            'name' => $googleUser->name,
            'avatar' => $googleUser->picture
        ]);

        $this->finishSocialLogin($user);
    }

    private function getJsonFromUrl($url)
    {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            $response = curl_exec($ch);
            $error = curl_error($ch);
            curl_close($ch);

            if ($response === false) {
                throw new Exception($error ?: 'Request failed');
            }
        } else {
            $response = file_get_contents($url);
            if ($response === false) {
                throw new Exception('Request failed');
            }
        }

        $data = json_decode($response, true);
        if (!is_array($data)) {
            throw new Exception('Invalid response from Facebook');
        }

        return $data;
    }

    public function facebook()
    {
        $_SESSION['oauth_intent'] = $this->getPublicOauthIntent();
        $_SESSION['facebook_oauth_state'] = bin2hex(random_bytes(16));

        $params = [
            'client_id' => FACEBOOK_APP_ID,
            'redirect_uri' => FACEBOOK_REDIRECT_URI,
            'state' => $_SESSION['facebook_oauth_state'],
            'scope' => 'email,public_profile',
            'response_type' => 'code'
        ];

        redirect('https://www.facebook.com/v20.0/dialog/oauth?' . http_build_query($params));
    }

    public function facebookCallback()
    {
        if (isset($_GET['error'])) {
            exit('Facebook login error: ' . htmlspecialchars($_GET['error_description'] ?? $_GET['error'], ENT_QUOTES, 'UTF-8'));
        }

        if (!isset($_GET['code'])) {
            exit('Facebook login failed: authorization code missing.');
        }

        if (!isset($_GET['state']) || $_GET['state'] !== ($_SESSION['facebook_oauth_state'] ?? '')) {
            exit('Invalid Facebook login state');
        }

        try {
            $token = $this->getJsonFromUrl('https://graph.facebook.com/v20.0/oauth/access_token?' . http_build_query([
                'client_id' => FACEBOOK_APP_ID,
                'client_secret' => FACEBOOK_APP_SECRET,
                'redirect_uri' => FACEBOOK_REDIRECT_URI,
                'code' => $_GET['code']
            ]));

            if (isset($token['error']) || empty($token['access_token'])) {
                $message = $token['error']['message'] ?? 'Facebook token error';
                exit('Facebook token error: ' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));
            }

            $facebookUser = $this->getJsonFromUrl('https://graph.facebook.com/me?' . http_build_query([
                'fields' => 'id,name,email,picture.type(large)',
                'access_token' => $token['access_token']
            ]));

            if (empty($facebookUser['email'])) {
                exit('Facebook did not return an email address for this account.');
            }

            $user = $this->usermodel->findOrCreateFacebookUser([
                'facebook_id' => $facebookUser['id'],
                'email' => $facebookUser['email'],
                'name' => $facebookUser['name'] ?? $facebookUser['email'],
                'avatar' => $facebookUser['picture']['data']['url'] ?? null
            ]);

            $this->finishSocialLogin($user);
        } catch (Exception $e) {
            exit('Facebook login failed: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
        }
    }

}


?>
