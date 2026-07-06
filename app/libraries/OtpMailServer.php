<?php
class OtpMailServer{
    
    private $mailserver;

    public function __construct() {
        $this->mailserver = new Mailserver();
    }

    public function otpMailServer($toEmail,$otp){
        $subject = "Your OTP Code Verification";
        $body = "Your OTP code is: <b>$otp</b><br>It expires in 1 minutes.";
        $data = [
            "email" => $toEmail,
            "subject" =>$subject,
            "body" => $body
        ];

        return $this->mailserver->sendEmailOtp($data);
    }
}
?>
