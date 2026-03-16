<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Manually point to the files if autoload.php keeps crashing
require 'vendor/phpmailer/phpmailer/src/Exception.php';
require 'vendor/phpmailer/phpmailer/src/PHPMailer.php';
require 'vendor/phpmailer/phpmailer/src/SMTP.php';

include('rms.php');

$object = new rms();

if(isset($_POST["user_email"])) {
    $error = '';
    $success = '';
    $email = $_POST["user_email"];

    // Use the column name from your screenshot: user_email
    $object->query = "SELECT * FROM user_table WHERE user_email = '".$email."'";
    
    // Fetch results to check if user exists
    $result = $object->get_result();
    $total_row = (is_array($result)) ? count($result) : 0;

    if($total_row > 0) {
        $token = bin2hex(random_bytes(50));
        
        // Update using the column from your screenshot: user_password_reset_code
        $object->query = "
        UPDATE user_table 
        SET user_password_reset_code = '".$token."' 
        WHERE user_email = '".$email."'
        ";
        $object->execute();
        
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com'; 
            $mail->SMTPAuth   = true;
            // IMPORTANT: Ensure this is a 16-character App Password from Google
            $mail->Username   = 'vinniemariba2004@gmail.com'; 
            $mail->Password   = 'uksz nfnp blzu gypl'; 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // InfinityFree / Localhost SSL certificate bypass
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            // Recipients
            $mail->setFrom('vinniemariba2004@gmail.com', 'Wakanesa Restaurant');
            $mail->addAddress($email);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request - Wakanesa Restaurant';
            
            $reset_link = "https://wakanesa.infinityfreeapp.com/reset_password.php?token=" . $token;
            
            $mail->Body = "
                <div style='font-family: Arial, sans-serif; padding: 20px; border: 1px solid #eee; border-radius: 10px;'>
                    <h3 style='color: #333;'>Reset Your Password</h3>
                    <p>We received a request to reset the password for your Wakanesa Restaurant account.</p>
                    <p>Click the button below to proceed:</p>
                    <div style='margin: 20px 0;'>
                        <a href='$reset_link' style='background:#0ea5e9; color:white; padding:12px 25px; text-decoration:none; border-radius:5px; display:inline-block; font-weight: bold;'>Reset Password</a>
                    </div>
                    <p style='font-size: 12px; color: #777;'>If you did not request this, please ignore this email. This link will remain active for a limited time.</p>
                </div>
            ";

            $mail->send();
            $success = 'Reset link has been sent to your email address.';
        } catch (Exception $e) {
            // This provides the specific reason why authentication failed
            $error = "Mail could not be sent. Error: SMTP Error: " . $mail->ErrorInfo;
        }
    } else {
        $error = 'Email Address not found in our records.';
    }

    $output = array(
        'error'   => $error,
        'success' => $success
    );

    // Ensure no previous whitespace/warnings break the JSON
    if (ob_get_length()) ob_clean();
    echo json_encode($output);
}
?>