<?php
// forgot_password.php
include('rms.php');
$object = new rms();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Forgot Password - Wakanesa Restaurant</title>
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="vendor/parsley/parsley.css"/>
    <style>
        body {
            background: url("img/bg.jpg") no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.18);
            backdrop-filter: blur(15px);
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.25);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.25);
        }
        .form-control { border-radius: 8px; }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center align-items-center" style="min-height:100vh;">
        <div class="col-md-5">
            <div class="card glass-card p-4">
                <div class="text-center mb-4">
                    <h1 class="h4 text-white">Reset Password</h1>
                    <p class="text-white-50 small">Enter your email. An administrator will be notified to reset your password.</p>
                </div>
                <span id="message"></span>
                <form method="post" id="forgot_form">
                    <div class="form-group">
                        <input type="email" name="user_email" id="user_email" class="form-control" placeholder="Email Address" required data-parsley-type="email" data-parsley-trigger="keyup">
                    </div>
                    <button type="submit" id="reset_button" class="btn btn-primary btn-block shadow">Send Request</button>
                    <div class="text-center mt-3">
                        <a class="small text-white" href="index.php">Back to Login</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/parsley/dist/parsley.min.js"></script>
<script>
$(document).ready(function(){
    $('#forgot_form').parsley();
    $('#forgot_form').on('submit', function(e){
        e.preventDefault();
        if($(this).parsley().isValid()){
            $.ajax({
                url: "forgot_password_action.php",
                method: "POST",
                data: $(this).serialize(),
                dataType: "json",
                beforeSend: function(){
                    $('#reset_button').prop('disabled', true).text('Processing...');
                },
                success: function(data){
                    $('#reset_button').prop('disabled', false).text('Send Request');
                    if(data.error) {
                        $('#message').html('<div class="alert alert-danger">'+data.error+'</div>');
                    } else {
                        $('#message').html('<div class="alert alert-success">'+data.success+'</div>');
                        $('#forgot_form')[0].reset();
                    }
                }
            });
        }
    });
});
</script>
</body>
</html>