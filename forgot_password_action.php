<?php
// forgot_password_action.php
include('rms.php');
$object = new rms();

if(isset($_POST["user_email"])) {
    $error = '';
    $success = '';

    // 1. Check if email exists
    $object->query = "SELECT * FROM user_table WHERE user_email = :user_email";
    $object->execute([':user_email' => $_POST["user_email"]]);

    if($object->row_count() > 0) {
        // 2. Notify Admin
        // Option A: Add a 'password_reset_request' column to user_table (1 = requested)
        // You'll need to run: ALTER TABLE user_table ADD reset_request INT DEFAULT 0
        $object->query = "UPDATE user_table SET reset_request = 1 WHERE user_email = :user_email";
        $object->execute([':user_email' => $_POST["user_email"]]);

        $success = 'Request sent! Please contact the admin to receive your new password.';
    } else {
        $error = 'Email address not found.';
    }

    $output = array('error' => $error, 'success' => $success);
    echo json_encode($output);
}
?>