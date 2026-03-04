<?php
// reset_password.php
include('rms.php');
$object = new rms();

$token = $_GET["token"] ?? '';
$valid_token = false;

if($token != '') {
    // Check if the token exists in the user_table
    $object->query = "SELECT * FROM user_table WHERE user_password_reset_code = '".$token."'";
    
    // Use get_result() and count the array to avoid the 'undefined method' error
    $result = $object->get_result();
    
    if(is_array($result) && count($result) > 0) {
        $valid_token = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Set New Password - Wakanesa</title>
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #0f172a; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .reset-card { background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(15px); border: 1px solid #0ea5e9; border-radius: 20px; width: 100%; max-width: 400px; padding: 30px; color: #fff; }
        .form-control { background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255,255,255,0.2); color: #fff; }
        .form-control:focus { background: rgba(255, 255, 255, 0.1); color: #fff; border-color: #0ea5e9; box-shadow: 0 0 10px rgba(14, 165, 233, 0.5); }
        .btn-reset { background: #0ea5e9; color: #fff; font-weight: 600; border: none; height: 50px; border-radius: 10px; transition: 0.3s; }
        .btn-reset:hover { background: #0284c7; color: #fff; transform: translateY(-2px); }
        .success-icon { color: #22c55e; font-size: 4rem; margin-bottom: 20px; }
    </style>
</head>
<body>

<div class="reset-card" id="card_content">
    <div id="form_section">
        <h3 class="text-center mb-4">Set New Password</h3>
        <div id="message"></div>

        <?php if($valid_token): ?>
            <form method="post" id="reset_password_form">
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="user_password" id="user_password" class="form-control" placeholder="••••••••" required>
                </div>
                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" id="confirm_password" class="form-control" placeholder="••••••••" required>
                </div>
                <input type="hidden" name="token" value="<?php echo $token; ?>">
                <button type="submit" id="submit_button" class="btn btn-reset btn-block">Update Password</button>
            </form>
        <?php else: ?>
            <div class="alert alert-danger text-center">Invalid or expired reset link.</div>
            <div class="text-center"><a href="forgot_password.php" class="text-info">Request a new one</a></div>
        <?php endif; ?>
    </div>
</div>

<script src="vendor/jquery/jquery.min.js"></script>
<script>
$(document).ready(function(){
    $('#reset_password_form').on('submit', function(e){
        e.preventDefault();
        
        if($('#user_password').val() != $('#confirm_password').val()){
            $('#message').html('<div class="alert alert-danger" style="background:rgba(220,53,69,0.2); border-color:#dc3545; color:#fff;">Passwords do not match</div>');
            return false;
        }

        $.ajax({
            url: "reset_password_action.php",
            method: "POST",
            data: $(this).serialize(),
            dataType: "json",
            beforeSend: function(){
                $('#submit_button').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
            },
            success: function(data){
                if(data.error) {
                    $('#message').html('<div class="alert alert-danger" style="background:rgba(220,53,69,0.2); border-color:#dc3545; color:#fff;">'+data.error+'</div>');
                    $('#submit_button').prop('disabled', false).text('Update Password');
                } else {
                    // SUCCESS UI: Replace the form content entirely
                    $('#card_content').html(`
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle success-icon"></i>
                            <h2 class="font-weight-bold mb-3">Password Changed!</h2>
                            <p class="text-white-50 mb-4">Your account security has been updated. You can now use your new password to sign in.</p>
                            <a href="index.php" class="btn btn-reset btn-block d-flex align-items-center justify-content-center">
                                <i class="fas fa-sign-in-alt mr-2"></i> Go to Login
                            </a>
                        </div>
                    `);
                }
            }
        });
    });
});
</script>
</body>
</html>