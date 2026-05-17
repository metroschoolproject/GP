<?php
class AttemptFailAlertMailServer{
    
    private $mailserver;

    public function __construct() {
        $this->mailserver = new Mailserver();
    }

    public function LoginFailAlertMailServer($toEmail,$token = null){
        $subject = "Login Fail!!";
        $body = "We noticed 3 unsuccessful sign-in attempts to your account. If this wasn't you, please open the forgot password page and reset your password.";

        if ($token) {
            $resetUrl = URLROOT . "/resetpassword/resetpassword?token=" . urlencode($token) . '&e=' . urlencode($toEmail);
            $body .= "\n\n" . $resetUrl;
        }

        $data = [
            "email" => $toEmail,
            "subject" =>$subject,
            "body" => $body
        ];

        $this->mailserver->sendEmailOtp($data);
    }


    public function OTPFailAlertMailServer($toEmail){
        $subject = "OTP Fail!!";
        $body = "We noticed 3 unsuccessful sign-in attempts to your account. If this wasn't you, reset your password.";
        $data = [
            "email" => $toEmail,
            "subject" =>$subject,
            "body" => $body
        ];

        $this->mailserver->sendEmailOtp($data);
    }
}
?>
