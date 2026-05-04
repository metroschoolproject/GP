<?php
class ResetPasswordMailServer{
    
    private $mailserver;

    public function __construct() {
        $this->mailserver = new Mailserver();
    }

    public function resetPwMailServer($toEmail,$token){
        $resetUrl = URLROOT . "/resetpassword/resetpassword?token=" . urlencode($token) . '&e=' . urlencode($toEmail);
        $subject = "Password reset for Perum";
        $body = "We recieved a request to reset your password. Click the link below to reset it(expires in 60 minutes):\n\n";
        $body .= $resetUrl . "\n\nIf you did not request this, ignore this email.";

        $data = [
            "email" => $toEmail,
            "subject" =>$subject,
            "body" => $body
        ];

        $this->mailserver->sendEmailOtp($data);
    }
}
?>
