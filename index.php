<?php 
include('rms.php');
$object = new rms();

if(!$object->Is_set_up_done()) {
    header("location:".$object->base_url."register.php");
    exit;
}

if($object->is_login()) {
    if($object->is_user()) {
        header("location:".$object->base_url."user_dashboard.php");
    } else {
        header("location:".$object->base_url."dashboard.php");
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Wakanesa Restaurant - Member Login</title>

    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="vendor/parsley/parsley.css"/>

    <style>
        :root {
            --sky-blue: #0ea5e9; 
            --sky-glow: rgba(14, 165, 233, 0.3);
            --glass-bg: rgba(255, 255, 255, 0.1);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #0f172a;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px 15px;
            overflow-x: hidden; 
        }

        .bg-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            z-index: -1;
            background: linear-gradient(rgba(15, 23, 42, 0.8), rgba(15, 23, 42, 0.9)), url('img/bg.jpg') no-repeat center center;
            background-size: cover;
            animation: slowPan 30s infinite alternate;
        }

        @keyframes slowPan { from { transform: scale(1); } to { transform: scale(1.1); } }

        .login-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 24px;
            border: 2px solid var(--sky-blue);
            box-shadow: 0 0 25px var(--sky-glow);
            width: 100%;
            max-width: 450px;
            animation: floatIn 0.8s ease-out forwards;
            position: relative;
        }

        @keyframes floatIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-logo { width: 80px; height: auto; margin-bottom: 15px; filter: drop-shadow(0px 5px 15px var(--sky-glow)); }
        h1 { font-family: 'Playfair Display', serif; color: var(--sky-blue) !important; text-shadow: 0 0 10px var(--sky-glow); }
        label { color: #e2e8f0 !important; font-size: 0.85rem; margin-left: 5px; }
        
        .form-control {
            background: rgba(255, 255, 255, 0.05) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            border-radius: 12px;
            color: #fff !important;
            height: 50px;
        }
        
        .form-control:focus {
            background: rgba(255, 255, 255, 0.1) !important;
            border-color: var(--sky-blue) !important;
            box-shadow: 0 0 15px var(--sky-glow);
        }

        /* Show Password Toggle Styling */
        .input-group-append .btn {
            background: rgba(255, 255, 255, 0.05) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            border-left: none !important;
            color: var(--sky-blue) !important;
            border-top-right-radius: 12px;
            border-bottom-right-radius: 12px;
            transition: 0.3s;
        }

        .input-group-append .btn:hover {
            background: rgba(14, 165, 233, 0.1) !important;
        }

        #user_password {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }

        .btn-login {
            background: var(--sky-blue);
            color: #fff;
            border: none;
            border-radius: 12px;
            height: 55px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            box-shadow: 0 4px 15px var(--sky-glow);
            transition: 0.3s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px var(--sky-glow);
            filter: brightness(1.1);
        }

        .footer-links a { color: #94a3b8; transition: 0.3s; }
        .footer-links a:hover { color: var(--sky-blue); text-decoration: none; }
    </style>
</head>

<body>
<div class="bg-overlay"></div>
<div class="login-card">
    <div class="p-4 p-md-5">
        <div class="text-center mb-4">
            <img src="img/logo.png" alt="Logo" class="login-logo">
            <h1 class="h2 font-weight-bold">Wakanesa</h1>
            <p class="text-white-50 small">Sign in to your account</p>
            <div id="error"></div>
        </div>

        <form method="post" id="login_form">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="user_email" id="user_email" class="form-control" required data-parsley-type="email" placeholder="your@email.com">
            </div>
            <div class="form-group mb-2">
                <label>Password</label>
                <div class="input-group">
                    <input type="password" name="user_password" id="user_password" class="form-control" required placeholder="••••••••">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="button" id="toggle_password">
                            <i class="fas fa-eye" id="password_icon"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="text-right mb-4">
                <a class="footer-links" href="forgot_password.php"><small>Forgot Password?</small></a>
            </div>
            <button type="submit" id="login_button" class="btn btn-login btn-block">Secure Login</button>
            <hr class="my-4" style="border-top: 1px solid rgba(255,255,255,0.1);">
            <div class="text-center footer-links">
                <a href="register.php">Not a member? <strong class="text-white">Register</strong></a>
            </div>
        </form>
    </div>
</div>

<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="vendor/parsley/dist/parsley.min.js"></script>

<script>
$(document).ready(function(){
    /* ================= TOGGLE PASSWORD LOGIC ================= */
    $('#toggle_password').on('click', function() {
        const passwordField = $('#user_password');
        const icon = $('#password_icon');
        const type = passwordField.attr('type') === 'password' ? 'text' : 'password';
        
        passwordField.attr('type', type);
        icon.toggleClass('fa-eye fa-eye-slash');
    });

    /* ================= SUBMIT FORM ================= */
    $('#login_form').on('submit', function(e){
        e.preventDefault();
        if($(this).parsley().isValid()){
            $.ajax({
                url: "login_action.php",
                method: "POST",
                data: $(this).serialize(),
                dataType: "json",
                beforeSend: function(){
                    $('#login_button').prop('disabled', true).html('<i class="fas fa-circle-notch fa-spin"></i> Checking...');
                },
                success: function(data){
                    $('#login_button').prop('disabled', false).html('Login');
                    if(data.error != ''){
                        $('#error').html('<div class="alert alert-danger py-2" style="font-size:0.8rem; border-radius:10px; background:rgba(220,53,69,0.2); border:1px solid #dc3545; color:#fff;">'+data.error+'</div>');
                    } else {
                        window.location.href = data.url;
                    }
                }
            });
        }
    });
});
</script>
</body>
</html>