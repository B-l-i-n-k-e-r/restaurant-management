<?php
include('rms.php');
$object = new rms();

// Check if setup is done
$setup_done = $object->Is_set_up_done();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Join Wakanesa - Create Account</title>

    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="vendor/parsley/parsley.css"/>

    <style>
        :root {
            --sky-blue: #0ea5e9;
            --sky-glow: rgba(14, 165, 233, 0.3);
            --brand-dark: rgba(15, 23, 42, 0.9);
            --glass-white: rgba(255, 255, 255, 0.1);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #0f172a;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px 10px; /* Responsive padding */
            overflow-x: hidden;
        }

        /* Animated Background */
        .bg-video-wrap {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            z-index: -1;
            background: linear-gradient(rgba(15, 23, 42, 0.8), rgba(15, 23, 42, 0.8)), url('img/bg1.jpg') no-repeat center center;
            background-size: cover;
            animation: slowZoom 20s infinite alternate;
        }

        @keyframes slowZoom {
            from { transform: scale(1); }
            to { transform: scale(1.1); }
        }

        /* Main Container - Adjusted for better scaling */
        .glass-container {
            background: var(--glass-white);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 2px solid var(--sky-blue);
            border-radius: 30px;
            overflow: hidden;
            box-shadow: 0 0 30px var(--sky-glow);
            width: 100%;
            max-width: 1000px; 
            margin: auto;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Left Sidebar - Hidden on smaller tablets and mobile */
        .promo-sidebar {
            background: var(--brand-dark);
            color: white;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            border-right: 1px solid rgba(14, 165, 233, 0.2);
        }

        .promo-sidebar h2 {
            font-family: 'Playfair Display', serif;
            color: var(--sky-blue);
            font-size: 2.2rem;
            margin-bottom: 20px;
        }

        .perk-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 20px;
        }

        .perk-icon {
            background: var(--sky-glow);
            color: var(--sky-blue);
            padding: 10px;
            border-radius: 12px;
            margin-right: 15px;
            flex-shrink: 0;
        }

        /* Form Styling */
        label {
            color: #e2e8f0 !important;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.05) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            border-radius: 12px;
            height: 48px;
            color: #fff !important;
            transition: 0.3s;
            font-size: 16px; /* Best for mobile input */
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.1) !important;
            border-color: var(--sky-blue) !important;
            box-shadow: 0 0 15px var(--sky-glow);
        }

        .btn-customer {
            background: var(--sky-blue);
            color: white;
            border-radius: 12px;
            height: 55px;
            font-weight: 700;
            border: none;
            transition: all 0.3s;
            box-shadow: 0 4px 15px var(--sky-glow);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-customer:hover {
            background: #38bdf8;
            transform: translateY(-2px);
            color: white;
        }

        .input-group-text {
            background: rgba(255, 255, 255, 0.05) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            border-left: none !important;
            color: var(--sky-blue) !important;
            border-radius: 0 12px 12px 0 !important;
        }

        .back-link {
            color: #94a3b8;
            text-decoration: none !important;
            transition: 0.3s;
        }

        .back-link:hover { color: var(--sky-blue); }

        /* RESPONSIVE BREAKPOINTS */
        @media (max-width: 991px) {
            .promo-sidebar { display: none !important; } /* Hide sidebar for tablets */
            .glass-container { max-width: 600px; }
        }

        @media (max-width: 576px) {
            body { align-items: flex-start; } /* Allow natural scroll from top */
            .glass-container { border-radius: 20px; }
            .p-md-5 { padding: 1.5rem !important; }
            h3 { font-size: 1.5rem; }
        }
    </style>
</head>

<body>
    <div class="bg-video-wrap"></div>
    
    <div class="container-fluid d-flex justify-content-center">
        <div class="glass-container">
            <div class="row no-gutters">
                <div class="col-lg-5 promo-sidebar d-none d-lg-flex">
                    <h2>Welcome to the Table.</h2>
                    <p class="text-white-50 mb-4">Start your journey with Wakanesa today.</p>
                    
                    <div class="perk-item">
                        <div class="perk-icon"><i class="fas fa-star"></i></div>
                        <div>
                            <h6 class="mb-0 text-white">VIP Access</h6>
                            <p class="small text-white-50">Early bird bookings and special seating.</p>
                        </div>
                    </div>
                    <div class="perk-item">
                        <div class="perk-icon"><i class="fas fa-bell"></i></div>
                        <div>
                            <h6 class="mb-0 text-white">Instant Updates</h6>
                            <p class="small text-white-50">Real-time order tracking and alerts.</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7 p-4 p-md-5">
                    <div class="mb-4 text-center text-lg-left">
                        <h3 class="text-white font-weight-bold">
                            <?php echo $setup_done ? 'Create Account' : 'System Setup'; ?>
                        </h3>
                        <p class="text-white-50 small">Fill in the details below to get started.</p>
                    </div>

                    <span id="message"></span>

                    <form method="post" id="register_form">
                        <div class="row">
                            <?php if(!$setup_done): ?>
                                <div class="col-md-6 mb-3">
                                    <label>Restaurant Name</label>
                                    <input type="text" name="restaurant_name" class="form-control" placeholder="Wakanesa Main" required />
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Contact No.</label>
                                    <input type="text" name="restaurant_contact_no" class="form-control" placeholder="+123..." required />
                                </div>
                            <?php endif; ?>

                            <div class="col-md-6 mb-3">
                                <label>Full Name</label>
                                <input type="text" name="user_name" class="form-control" placeholder="John Doe" required />
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Mobile No.</label>
                                <input type="text" name="user_contact_no" class="form-control" placeholder="07..." required />
                            </div>
                            <div class="col-12 mb-3">
                                <label>Email Address</label>
                                <input type="email" name="user_email" class="form-control" placeholder="email@example.com" required />
                            </div>
                            <div class="col-12 mb-4">
                                <label>Password</label>
                                <div class="input-group">
                                    <input type="password" name="user_password" id="user_password" class="form-control" placeholder="••••••••" required />
                                    <div class="input-group-append">
                                        <span class="input-group-text" id="togglePassword">
                                            <i class="fas fa-eye" id="eyeIcon"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <button type="submit" id="register_button" class="btn btn-customer btn-block">
                                    CREATE ACCOUNT
                                </button>
                                <div class="text-center mt-4">
                                    <a href="index.php" class="small back-link">Already a member? <strong>Login</strong></a>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="setup_done" value="<?php echo $setup_done ? 1 : 0; ?>">
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/parsley/dist/parsley.min.js"></script>

    <script>
    $(document).ready(function(){
        $('#register_form').parsley();

        $('#togglePassword').on('click', function() {
            const field = $('#user_password');
            const icon = $('#eyeIcon');
            const type = field.attr('type') === 'password' ? 'text' : 'password';
            field.attr('type', type);
            icon.toggleClass('fa-eye fa-eye-slash');
        });

        $('#register_form').on('submit', function(event){
            event.preventDefault();
            if($(this).parsley().isValid()) {
                $.ajax({
                    url:"register_action.php",
                    method:"POST",
                    data:$(this).serialize(),
                    dataType:'json',
                    beforeSend:function() {
                        $('#register_button').attr('disabled', 'disabled').html('<i class="fas fa-circle-notch fa-spin"></i> PREPARING...');
                    },
                    success:function(data) {
                        $('#register_button').attr('disabled', false).html('CREATE ACCOUNT');
                        if(data.error != '') {
                            $('#message').html('<div class="alert alert-danger border-0 small mt-3">'+data.error+'</div>');
                        } else {
                            window.location.href="index.php";
                        }
                    }
                });
            }
        });
    });
    </script>
</body>
</html>