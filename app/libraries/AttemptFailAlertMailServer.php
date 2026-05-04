<?php
class AttemptFailAlertMailServer{
    
    private $mailserver;

    public function __construct() {
        $this->mailserver = new Mailserver();
    }

    public function LoginFailAlertMailServer($toEmail,$token){
        $resetUrl = URLROOT . "/resetpassword/resetpassword?token=" . urlencode($token) . '&e=' . urlencode($toEmail);

        $subject = "Login Fail!!";
        $body = "We noticed 5 unsuccessful sign-in attempts to your account. If this wasn't you, reset your password.:\n\n";
        $body .= $resetUrl;

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
