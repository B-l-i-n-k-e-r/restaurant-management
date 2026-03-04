<?php
// reset_password_action.php
include('rms.php');
$object = new rms();

if(isset($_POST["user_password"])) {
    $error = '';
    $success = '';
    $token = $_POST["token"];
    $new_password = $_POST["user_password"];

    // Update the password and NULL the token to expire the link immediately
    $object->query = "
        UPDATE user_table 
        SET user_password = '".$new_password."', 
            user_password_reset_code = NULL 
        WHERE user_password_reset_code = '".$token."'
    ";
    
    if($object->execute()) {
        // We send a simple success flag; the UI change is handled in reset_password.php
        $success = 'Password Updated';
    } else {
        $error = 'Unable to update password. Please try again.';
    }

    echo json_encode(['error' => $error, 'success' => $success]);
}
?>