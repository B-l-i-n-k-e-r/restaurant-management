<?php
// forgot_password.php
include('rms.php');
$object = new rms();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Forgot Password - Wakanesa Restaurant</title>

    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="vendor/parsley/parsley.css"/>

    <style>
        :root {
            --sky-blue: #0ea5e9; 
            /* Increased opacity for a stronger glow effect */
            --sky-glow: rgba(14, 165, 233, 0.5);
            --sky-glow-intense: rgba(14, 165, 233, 0.8);
            --deep-navy: #0f172a;
            --glass-core: rgba(15, 23, 42, 0.8);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--deep-navy);
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

        /* INCREASED BORDER GLOW */
        .login-card {
            background: var(--glass-core);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border-radius: 24px;
            border: 2px solid var(--sky-blue);
            /* Double layered shadow for intense outer glow */
            box-shadow: 0 0 20px var(--sky-glow), 0 0 40px rgba(0, 0, 0, 0.8);
            width: 100%;
            max-width: 450px; 
            min-width: 300px;
            animation: floatIn 0.8s ease-out forwards;
            position: relative;
            transition: box-shadow 0.4s ease;
        }

        .login-card:hover {
            box-shadow: 0 0 30px var(--sky-glow-intense), 0 0 50px rgba(0, 0, 0, 0.9);
        }

        @keyframes floatIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-logo { width: 70px; height: auto; margin-bottom: 15px; filter: drop-shadow(0px 0px 10px var(--sky-blue)); }
        
        h1 { 
            font-family: 'Playfair Display', serif; 
            color: #fff !important; 
            text-shadow: 0 0 15px var(--sky-glow-intense);
            font-size: 1.75rem;
        }
        
        label { color: #e2e8f0 !important; font-size: 0.85rem; margin-left: 5px; opacity: 0.9; }
        
        .form-control {
            background: rgba(255, 255, 255, 0.05) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            border-radius: 12px;
            color: #fff !important;
            height: 50px;
            transition: 0.3s;
        }
        
        .form-control:focus {
            background: rgba(255, 255, 255, 0.1) !important;
            border-color: var(--sky-blue) !important;
            box-shadow: 0 0 20px var(--sky-glow);
        }

        /* INCREASED BUTTON GLOW */
        .btn-reset {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            color: #fff;
            border: none;
            border-radius: 12px;
            height: 55px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            /* Heavy glow effect */
            box-shadow: 0 0 15px var(--sky-glow), 0 5px 15px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
        }

        .btn-reset:hover {
            transform: translateY(-2px);
            /* Pulsing glow on hover */
            box-shadow: 0 0 25px var(--sky-glow-intense), 0 8px 25px rgba(0, 0, 0, 0.4);
            filter: brightness(1.2);
            color: #fff;
        }

        .btn-reset:active { transform: translateY(0); }

        .btn-reset:disabled {
            background: #334155;
            box-shadow: none;
            opacity: 0.5;
        }

        .footer-links a { color: #94a3b8; transition: 0.3s; text-shadow: 0 0 5px rgba(0,0,0,0.5); }
        .footer-links a:hover { color: var(--sky-blue); text-decoration: none; text-shadow: 0 0 10px var(--sky-glow); }
        
        .alert {
            border-radius: 12px;
            font-size: 0.85rem;
            margin-top: 1rem;
            border: 1px solid transparent;
            backdrop-filter: blur(5px);
        }
    </style>
</head>

<body>
<div class="bg-overlay"></div>
<div class="login-card">
    <div class="p-4 p-md-5">
        <div class="text-center mb-4">
            <img src="img/logo.png" alt="Logo" class="login-logo">
            <h1 class="font-weight-bold">Reset Password</h1>
            <p class="text-white-50 small mt-2">Enter your email and an administrator will be notified to reset your access.</p>
            <div id="message"></div>
        </div>

        <form method="post" id="forgot_form">
            <div class="form-group mb-4">
                <label>Email Address</label>
                <input type="email" name="user_email" id="user_email" class="form-control" placeholder="your@email.com" required data-parsley-type="email" data-parsley-trigger="keyup">
            </div>
            
            <button type="submit" id="reset_button" class="btn btn-reset btn-block">Send Reset Request</button>
            
            <hr class="my-4" style="border-top: 1px solid rgba(255,255,255,0.15);">
            
            <div class="text-center footer-links">
                <a href="index.php"><i class="fas fa-arrow-left mr-2"></i>Back to <strong class="text-white">Login</strong></a>
            </div>
        </form>
    </div>
</div>

<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="vendor/parsley/dist/parsley.min.js"></script>

<script>
$(document).ready(function(){
    $('#forgot_form').on('submit', function(e){
        e.preventDefault();
        if($(this).parsley().isValid()){
            $.ajax({
                url: "forgot_password_action.php",
                method: "POST",
                data: $(this).serialize(),
                dataType: "json",
                beforeSend: function(){
                    $('#reset_button').prop('disabled', true).html('<i class="fas fa-circle-notch fa-spin mr-2"></i> Processing...');
                },
                success: function(data){
                    $('#reset_button').prop('disabled', false).html('Send Reset Request');
                    if(data.error) {
                        $('#message').html('<div class="alert" style="background:rgba(220,53,69,0.2); border:1px solid #dc3545; color:#fff;">'+data.error+'</div>');
                    } else {
                        $('#message').html('<div class="alert" style="background:rgba(40,167,69,0.2); border:1px solid #28a745; color:#fff;">'+data.success+'</div>');
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