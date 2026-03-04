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
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Set New Password - Wakanesa</title>
    
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">

    <style>
        :root {
            --sky-blue: #0ea5e9; 
            --sky-glow: rgba(14, 165, 233, 0.5);
            --sky-glow-intense: rgba(14, 165, 233, 0.8);
            --deep-navy: #0f172a;
            --glass-core: rgba(15, 23, 42, 0.85);
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
            overflow: hidden;
        }

        /* Animated Background Overlay */
        .bg-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            z-index: -1;
            background: linear-gradient(rgba(15, 23, 42, 0.8), rgba(15, 23, 42, 0.9)), url('img/bg.jpg') no-repeat center center;
            background-size: cover;
            animation: slowPan 30s infinite alternate;
        }

        @keyframes slowPan { from { transform: scale(1); } to { transform: scale(1.1); } }

        /* Intense Border Glow & Content-Fit */
        .reset-card { 
            background: var(--glass-core); 
            backdrop-filter: blur(25px); 
            -webkit-backdrop-filter: blur(25px);
            border: 2px solid var(--sky-blue); 
            border-radius: 24px; 
            width: 100%; 
            max-width: 450px; 
            min-width: 300px;
            padding: 40px; 
            color: #fff; 
            box-shadow: 0 0 20px var(--sky-glow), 0 0 40px rgba(0, 0, 0, 0.8);
            transition: all 0.4s ease;
            position: relative;
            animation: floatIn 0.8s ease-out forwards;
        }

        @keyframes floatIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .reset-card:hover {
            box-shadow: 0 0 30px var(--sky-glow-intense), 0 0 50px rgba(0, 0, 0, 0.9);
        }

        h3 { 
            font-family: 'Playfair Display', serif; 
            font-weight: 700;
            text-shadow: 0 0 15px var(--sky-glow-intense);
        }

        label { color: #e2e8f0 !important; font-size: 0.85rem; margin-left: 5px; opacity: 0.9; }

        .form-control { 
            background: rgba(255, 255, 255, 0.05) !important; 
            border: 1px solid rgba(255,255,255,0.2) !important; 
            border-radius: 12px;
            height: 50px;
            color: #fff !important; 
            transition: 0.3s;
        }

        .form-control:focus { 
            background: rgba(255, 255, 255, 0.1) !important; 
            border-color: var(--sky-blue) !important; 
            box-shadow: 0 0 20px var(--sky-glow); 
        }

        /* Password Toggle Icon Styling */
        .input-group-append .btn-toggle {
            background: rgba(255, 255, 255, 0.05) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            border-left: none !important;
            color: var(--sky-blue) !important;
            border-top-right-radius: 12px;
            border-bottom-right-radius: 12px;
            padding: 0 15px;
            transition: 0.3s;
        }

        .input-group-append .btn-toggle:hover {
            color: #fff !important;
            text-shadow: 0 0 10px var(--sky-blue);
        }

        /* Intense Button Glow */
        .btn-reset { 
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); 
            color: #fff; 
            font-weight: 700; 
            border: none; 
            height: 55px; 
            border-radius: 12px; 
            text-transform: uppercase;
            letter-spacing: 1.5px;
            box-shadow: 0 0 15px var(--sky-glow), 0 5px 15px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease; 
        }

        .btn-reset:hover { 
            transform: translateY(-2px); 
            box-shadow: 0 0 25px var(--sky-glow-intense), 0 8px 25px rgba(0, 0, 0, 0.4);
            filter: brightness(1.2);
            color: #fff;
        }

        .success-icon { 
            color: #22c55e; 
            font-size: 4.5rem; 
            margin-bottom: 20px; 
            filter: drop-shadow(0 0 15px rgba(34, 197, 94, 0.4));
        }

        .alert {
            border-radius: 12px;
            font-size: 0.85rem;
            backdrop-filter: blur(5px);
        }
    </style>
</head>
<body>

<div class="bg-overlay"></div>

<div class="reset-card" id="card_content">
    <div id="form_section">
        <div class="text-center mb-4">
            <i class="fas fa-shield-alt fa-3x mb-3" style="color: var(--sky-blue); filter: drop-shadow(0 0 10px var(--sky-glow));"></i>
            <h3>New Credentials</h3>
            <p class="text-white-50 small">Secure your account with a strong password.</p>
        </div>
        
        <div id="message"></div>

        <?php if($valid_token): ?>
            <form method="post" id="reset_password_form">
                <div class="form-group mb-4">
                    <label>New Password</label>
                    <div class="input-group">
                        <input type="password" name="user_password" id="user_password" class="form-control" placeholder="••••••••" required>
                        <div class="input-group-append">
                            <button class="btn btn-toggle toggle_password" type="button" data-target="#user_password">
                                <i class="fas fa-eye icon_eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="form-group mb-4">
                    <label>Confirm Password</label>
                    <div class="input-group">
                        <input type="password" id="confirm_password" class="form-control" placeholder="••••••••" required>
                        <div class="input-group-append">
                            <button class="btn btn-toggle toggle_password" type="button" data-target="#confirm_password">
                                <i class="fas fa-eye icon_eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="token" value="<?php echo $token; ?>">
                <button type="submit" id="submit_button" class="btn btn-reset btn-block">Update Password</button>
            </form>
        <?php else: ?>
            <div class="alert alert-danger text-center" style="background:rgba(220,53,69,0.2); border-color:#dc3545; color:#fff;">
                Invalid or expired reset link.
            </div>
            <div class="text-center mt-3">
                <a href="forgot_password.php" style="color: var(--sky-blue); text-decoration: none;">Request a new link</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="vendor/jquery/jquery.min.js"></script>
<script>
$(document).ready(function(){
    // Universal Password Toggle logic
    $('.toggle_password').on('click', function() {
        const target = $($(this).data('target'));
        const icon = $(this).find('.icon_eye');
        const type = target.attr('type') === 'password' ? 'text' : 'password';
        target.attr('type', type);
        icon.toggleClass('fa-eye fa-eye-slash');
    });

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
                $('#submit_button').prop('disabled', true).html('<i class="fas fa-circle-notch fa-spin mr-2"></i> Syncing...');
            },
            success: function(data){
                if(data.error) {
                    $('#message').html('<div class="alert alert-danger" style="background:rgba(220,53,69,0.2); border-color:#dc3545; color:#fff;">'+data.error+'</div>');
                    $('#submit_button').prop('disabled', false).text('Update Password');
                } else {
                    $('#card_content').fadeOut(400, function() {
                        $(this).html(`
                            <div class="text-center py-4">
                                <i class="fas fa-check-circle success-icon"></i>
                                <h2 class="font-weight-bold mb-3 text-white">All Set!</h2>
                                <p class="text-white-50 mb-4">Your password has been updated. Access is now restored.</p>
                                <a href="index.php" class="btn btn-reset btn-block d-flex align-items-center justify-content-center">
                                    <i class="fas fa-sign-in-alt mr-2"></i> Return to Login
                                </a>
                            </div>
                        `).fadeIn(600);
                    });
                }
            }
        });
    });
});
</script>
</body>
</html>