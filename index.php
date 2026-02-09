<?php 
include('rms.php');

$object = new rms();

// Auto-detect base URL
$object->base_url = ((isset($_SERVER['HTTPS']) ? "https" : "http") . "://".$_SERVER['HTTP_HOST']."/");

// Redirect to register if setup not done
if(!$object->Is_set_up_done()) {
    header("location:".$object->base_url."register.php");
    exit;
}

// Redirect logged-in users to dashboard
if($object->is_login()) {
    header("location:".$object->base_url."dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Restaurant Management System - Login</title>

    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:300,400,600,700,800" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="vendor/parsley/parsley.css"/>

    <style>
        body {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), 
                        url('img/bg.jpg') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.18);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.25);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.25);
            animation: fadeSlideIn 0.8s ease forwards;
            opacity: 0;
            transform: translateY(30px);
        }

        @keyframes fadeSlideIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-logo {
            width: 80px;
            margin-bottom: 15px;
            filter: drop-shadow(0px 4px 4px rgba(0,0,0,0.3));
        }

        .form-control {
            background: rgba(255, 255, 255, 0.9) !important;
            border: none;
            border-radius: 8px;
            color: #333 !important;
            height: 45px;
        }

        label { 
            color: #fff !important; 
            font-weight: 600; 
            text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
        }

        .input-group-text {
            background: rgba(255, 255, 255, 0.9);
            border: none;
            border-top-right-radius: 8px !important;
            border-bottom-right-radius: 8px !important;
            cursor: pointer;
            color: #4e73df;
        }

        .btn-login {
            background: #4e73df;
            border: none;
            border-radius: 8px;
            padding: 0.75rem;
            font-weight: 600;
            transition: 0.3s;
            color: white;
        }

        .btn-login:hover {
            background: #2e59d9;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            color: white;
        }

        .forgot-link, .register-link {
            font-size: 0.85rem;
            color: #fff;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
            transition: 0.3s;
        }

        .forgot-link:hover, .register-link:hover {
            color: #d1d3e2;
            text-decoration: none;
        }
    </style>
</head>

<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-xl-5 col-lg-6 col-md-8">

            <div class="card o-hidden border-0 login-card shadow-lg">
                <div class="card-body p-0">
                    <div class="p-4 p-md-5">

                        <div class="text-center mb-4">
                            <img src="img/logo.png" alt="Wakanesa Restaurant" class="login-logo">
                            <h1 class="h3 text-white font-weight-bold" style="text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">Wakanesa Restaurant</h1>
                            <p class="text-white-50">Login to access your dashboard</p>

                            <?php
                            if(isset($_SESSION['success'])) {
                                echo '<div class="alert alert-success">'.$_SESSION['success'].'</div>';
                                unset($_SESSION['success']);
                            }
                            ?>
                            <span id="error"></span>
                        </div>

                        <form method="post" id="login_form">
                            <div class="form-group">
                                <label>Email Address</label>
                                <input type="email" name="user_email" id="user_email"
                                       class="form-control"
                                       required data-parsley-type="email" data-parsley-trigger="keyup"
                                       placeholder="email@example.com">
                            </div>

                            <div class="form-group">
                                <label>Password</label>
                                <div class="input-group">
                                    <input type="password" name="user_password" id="user_password"
                                           class="form-control"
                                           required data-parsley-trigger="keyup"
                                           placeholder="Enter Password">
                                    <div class="input-group-append">
                                        <span class="input-group-text" id="togglePassword">
                                            <i class="fas fa-eye" id="eyeIcon"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="text-right mb-4">
                                <a class="forgot-link" href="forgot_password.php">Forgot Password?</a>
                            </div>

                            <button type="submit" id="login_button"
                                    class="btn btn-login btn-block shadow-sm">
                                <i class="fas fa-sign-in-alt mr-2"></i> Login
                            </button>

                            <hr style="border-top: 1px solid rgba(255,255,255,0.2);">

                            <div class="text-center">
                                <a class="register-link" href="register.php">New here? Create an Account</a>
                            </div>
                        </form>

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="js/sb-admin-2.min.js"></script>
<script src="vendor/parsley/dist/parsley.min.js"></script>

<script>
$(document).ready(function(){
    $('#login_form').parsley();

    // Password Show/Hide Toggle
    $('#togglePassword').on('click', function() {
        const passwordField = $('#user_password');
        const eyeIcon = $('#eyeIcon');
        const type = passwordField.attr('type') === 'password' ? 'text' : 'password';
        passwordField.attr('type', type);
        eyeIcon.toggleClass('fa-eye fa-eye-slash');
    });

    $('#login_form').on('submit', function(e){
        e.preventDefault();

        if($(this).parsley().isValid()){
            $.ajax({
                url: "login_action.php",
                method: "POST",
                data: $(this).serialize(),
                dataType: "json",
                beforeSend: function(){
                    $('#login_button').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Authenticating...');
                    $('#error').html('');
                },
                success: function(data){
                    $('#login_button').prop('disabled', false).html('<i class="fas fa-sign-in-alt mr-2"></i> Login');

                    if(data.error){
                        $('#error').html('<div class="alert alert-danger shadow-sm">'+data.error+'</div>');
                    } else {
                        window.location.href = "<?php echo $object->base_url; ?>dashboard.php";
                    }
                }
            });
        }
    });
});
</script>
</body>
</html>