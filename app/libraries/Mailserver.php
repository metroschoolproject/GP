<?php 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader from the current project.
require dirname(__DIR__, 2) . '/vendor/autoload.php';

class Mailserver extends Controller{
    /**
     * Create the shared Golden Promise mail transport.
     * OTP, account emails, and booking emails all use this configuration.
     */
    public function createMailer(): PHPMailer
    {
        $mail = new PHPMailer(true);
        $mail->SMTPDebug = 0;
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'hsumyatm7308@gmail.com';
        $mail->Password = 'jbbrepwljysvexsn';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->setFrom('hsumyatm7308@gmail.com', 'Golden Promise');

        return $mail;
    }

    // Mail Server
    public function sendEmailOtp($data){
        $mail = $this->createMailer();
        try {
            //Recipients
            $mail->addAddress($data['email']);     //Add a recipient

            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = $data['subject'];
            $mail->Body    = $data['body'];

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
            return false;
        }

    }
}

?>
