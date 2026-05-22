<?php

class EmailVerificationMailServer
{
    private $mailserver;

    public function __construct()
    {
        $this->mailserver = new Mailserver();
    }

    public function sendVerificationEmail($toEmail, $token)
    {
        $verifyUrl = URLROOT . '/users/verifyEmail?token=' . urlencode($token) . '&e=' . urlencode($toEmail);
        $body = 'Welcome to Golden Promise.<br><br>';
        $body .= 'Verify your email to finish creating your account. This link expires in 24 hours:<br><br>';
        $body .= '<a href="' . htmlspecialchars($verifyUrl, ENT_QUOTES, 'UTF-8') . '">Verify email</a><br><br>';
        $body .= 'If you did not create this account, ignore this email.';

        return $this->mailserver->sendEmailOtp([
            'email' => $toEmail,
            'subject' => 'Verify your Golden Promise email',
            'body' => $body
        ]);
    }
}
