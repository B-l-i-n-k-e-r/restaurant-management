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
            // Enable this ONLY for troubleshooting; it will show in your JQuery response
            // $mail->SMTPDebug = 2; 

            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com'; 
            $mail->SMTPAuth   = true;
            $mail->Username   = 'vinniemariba2004@gmail.com'; 
            $mail->Password   = 'hrhr wfzi vzrm cddl'; 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // ADD THIS BLOCK FOR INFINITYFREE
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            $mail->setFrom('vinniemariba2004@gmail.com', 'Wakanesa Restaurant'); // Use your Gmail here too
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request - Wakanesa Restaurant';
            
            // Adjust the URL to your exact folder name if it's different
            $reset_link = "https://wakanesa.infinityfreeapp.com/reset_password.php?token=" . $token;
            
            $mail->Body = "
                <div style='font-family: Arial, sans-serif; padding: 20px;'>
                    <h3>Reset Your Password</h3>
                    <p>Click the button below to reset your Wakanesa Restaurant account password:</p>
                    <a href='$reset_link' style='background:#0ea5e9; color:white; padding:10px 20px; text-decoration:none; border-radius:5px; display:inline-block;'>Reset Password</a>
                    <p>If you did not request this, please ignore this email.</p>
                </div>
            ";

            $mail->send();
            $success = 'Reset link has been sent to your email address.';
        } catch (Exception $e) {
            $error = "Mail could not be sent. Error: {$mail->ErrorInfo}";
        }
    } else {
        $error = 'Email Address not found in our records.';
    }

    $output = array(
        'error'   => $error,
        'success' => $success
    );

    echo json_encode($output);
}
?>