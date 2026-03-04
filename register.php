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
            padding: 20px 10px;
            overflow-x: hidden;
        }

        .bg-video-wrap {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            z-index: -1;
            background: linear-gradient(rgba(15, 23, 42, 0.8), rgba(15, 23, 42, 0.8)), url('img/bg1.jpg') no-repeat center center;
            background-size: cover;
            animation: slowZoom 20s infinite alternate;
        }

        @keyframes slowZoom { from { transform: scale(1); } to { transform: scale(1.1); } }

        /* INTENSE CONTAINER GLOW */
        .glass-container {
            background: var(--glass-core);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border: 2px solid var(--sky-blue);
            border-radius: 30px;
            overflow: hidden;
            /* Layered glow and depth shadow */
            box-shadow: 0 0 20px var(--sky-glow), 0 30px 60px rgba(0, 0, 0, 0.7);
            width: 100%;
            max-width: 1000px; 
            animation: fadeIn 0.8s ease-out;
            position: relative;
            transition: box-shadow 0.4s ease;
        }

        .glass-container:hover {
            box-shadow: 0 0 30px var(--sky-glow-intense), 0 30px 70px rgba(0, 0, 0, 0.8);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .promo-sidebar {
            background: rgba(14, 165, 233, 0.03);
            color: white;
            padding: 50px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            border-right: 1px solid rgba(14, 165, 233, 0.2);
        }

        .promo-sidebar h2 {
            font-family: 'Playfair Display', serif;
            color: #fff;
            font-size: 2.4rem;
            margin-bottom: 20px;
            text-shadow: 0 0 15px var(--sky-glow-intense);
        }

        .perk-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 25px;
        }

        .perk-icon {
            background: var(--sky-glow);
            color: #fff;
            width: 45px; height: 45px;
            display: flex; align-items: center; justify-content: center;
            border-radius: 12px;
            margin-right: 15px;
            flex-shrink: 0;
            border: 1px solid var(--sky-blue);
            box-shadow: 0 0 10px var(--sky-glow);
        }

        label { color: #e2e8f0 !important; font-size: 0.8rem; margin-left: 5px; opacity: 0.9; }

        .form-control, .custom-select {
            background: rgba(255, 255, 255, 0.05) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            border-radius: 14px;
            height: 50px;
            color: #fff !important;
            transition: 0.3s ease;
        }

        .form-control:focus, .custom-select:focus {
            background: rgba(255, 255, 255, 0.1) !important;
            border-color: var(--sky-blue) !important;
            box-shadow: 0 0 15px var(--sky-glow);
        }

        /* INTENSE BUTTON GLOW */
        .btn-customer {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            color: white;
            border-radius: 14px;
            height: 55px;
            font-weight: 700;
            border: none;
            letter-spacing: 1.5px;
            box-shadow: 0 0 15px var(--sky-glow), 0 5px 15px rgba(0, 0, 0, 0.3);
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .btn-customer:hover {
            transform: scale(1.02) translateY(-2px);
            box-shadow: 0 0 25px var(--sky-glow-intense), 0 10px 25px rgba(0, 0, 0, 0.4);
            filter: brightness(1.2);
            color: #fff;
        }

        .input-group-text {
            background: rgba(255, 255, 255, 0.05) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            border-left: none !important;
            color: var(--sky-blue) !important;
            border-radius: 0 14px 14px 0 !important;
        }

        .back-link { color: #94a3b8; transition: 0.3s; }
        .back-link:hover { color: var(--sky-blue); text-decoration: none; text-shadow: 0 0 8px var(--sky-glow); }

        hr { border-top: 1px solid rgba(255,255,255,0.15); }

        @media (max-width: 991px) {
            .promo-sidebar { display: none !important; }
            .glass-container { max-width: 550px; }
        }
    </style>
</head>

<body>
    <div class="bg-video-wrap"></div>
    
    <div class="container-fluid d-flex justify-content-center py-4">
        <div class="glass-container">
            <div class="row no-gutters">
                <div class="col-lg-5 promo-sidebar d-none d-lg-flex">
                    <h2>Welcome to Wakanesa.</h2>
                    <p class="text-white-50 mb-5">Join our exclusive circle for a premium dining experience.</p>
                    
                    <div class="perk-item">
                        <div class="perk-icon"><i class="fas fa-star"></i></div>
                        <div>
                            <h6 class="mb-0 text-white font-weight-bold">Easy Access</h6>
                            <p class="small text-white-50">Early bird bookings and special seating priority.</p>
                        </div>
                    </div>
                    <div class="perk-item">
                        <div class="perk-icon"><i class="fas fa-bell"></i></div>
                        <div>
                            <h6 class="mb-0 text-white font-weight-bold">Instant Updates</h6>
                            <p class="small text-white-50">Real-time order tracking and personalized alerts.</p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7 p-4 p-md-5">
                    <div class="mb-4">
                        <h3 class="text-white font-weight-bold" style="text-shadow: 0 0 10px var(--sky-glow);">
                            <?php echo $setup_done ? 'Create Account' : 'System Setup'; ?>
                        </h3>
                        <p class="text-white-50 small">Enter your details to register.</p>
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
                                    <label>Restaurant Phone</label>
                                    <input type="text" name="restaurant_contact_no" class="form-control" placeholder="+123..." required />
                                </div>
                                <div class="col-12 mb-3">
                                    <label>Restaurant Address</label>
                                    <textarea name="restaurant_address" class="form-control" rows="2" placeholder="Street, City..." required></textarea>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Currency</label>
                                    <select name="restaurant_currency" class="form-control custom-select" required>
                                        <option value="">Select</option>
                                        <option value="$">USD ($)</option>
                                        <option value="UGX">UGX (Shs)</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Timezone</label>
                                    <select name="restaurant_timezone" class="form-control custom-select" required>
                                        <option value="Africa/Nairobi">Africa/Nairobi</option>
                                        <option value="UTC">UTC</option>
                                    </select>
                                </div>
                                <div class="col-12 mb-3"><hr></div>
                            <?php endif; ?>

                            <div class="col-md-6 mb-3">
                                <label>Full Name</label>
                                <input type="text" name="user_name" class="form-control" placeholder="Name" required />
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
                                        <span class="input-group-text" id="togglePassword" style="cursor: pointer;">
                                            <i class="fas fa-eye" id="eyeIcon"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <button type="submit" id="register_button" class="btn btn-customer btn-block">CREATE ACCOUNT</button>
                                <div class="text-center mt-4">
                                    <a href="index.php" class="small back-link">Already a member? <strong class="text-white">Login</strong></a>
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
        $('#togglePassword').on('click', function() {
            const field = $('#user_password');
            const icon = $('#eyeIcon');
            const type = field.attr('type') === 'password' ? 'text' : 'password';
            field.attr('type', type);
            icon.toggleClass('fa-eye fa-eye-slash');
        });

        $('#register_form').on('submit', function(e){
            e.preventDefault();
            if($(this).parsley().isValid()) {
                $.ajax({
                    url:"register_action.php",
                    method:"POST",
                    data:$(this).serialize(),
                    dataType:'json',
                    beforeSend:function() {
                        $('#register_button').attr('disabled', 'disabled').html('<i class="fas fa-circle-notch fa-spin mr-2"></i> PROCESSING...');
                    },
                    success:function(data) {
                        $('#register_button').attr('disabled', false).html('CREATE ACCOUNT');
                        if(data.error != '') {
                            $('#message').html('<div class="alert mt-3" style="background: rgba(251,113,133,0.15); border: 1px solid #fb7185; color: #fff; border-radius: 12px; font-size:0.85rem;">'+data.error+'</div>');
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