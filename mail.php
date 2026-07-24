<?php
// SMTP Configuration for St. Aloysius Lost & Found
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require_once __DIR__ . '/../libs/PHPMailer/Exception.php';
require_once __DIR__ . '/../libs/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../libs/PHPMailer/SMTP.php';

// --- CONFIGURATION START ---
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587); // Use 587 for STARTTLS or 465 for SSL
define('SMTP_USER', 'staloysiuslost@gmail.com'); 
define('SMTP_PASS', 'pnyc cfog brlg cjyc'); 
define('FROM_EMAIL', 'staloysiuslost@gmail.com'); // Match with SMTP_USER for Gmail deliverability
define('FROM_NAME', 'St. Aloysius Lost & Found');
// --- CONFIGURATION END ---

/**
 * Function to send email using PHPMailer
 */
function sendEmail($to, $subject, $message) {
    // Check if configuration is still at default
    if (empty(SMTP_USER) || SMTP_USER === 'your-college-email@gmail.com') {
        // Fallback to basic mail() only if no credentials provided
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: ' . FROM_NAME . ' <' . FROM_EMAIL . '>' . "\r\n";
        return mail($to, $subject, $message, $headers);
    }

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;

        // Recipients
        $mail->setFrom(SMTP_USER, FROM_NAME);
        $mail->addAddress($to);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;
        $mail->AltBody = strip_tags($message);

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
?>

