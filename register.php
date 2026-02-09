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
    <title>Restaurant Management System - Register</title>

    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="vendor/parsley/parsley.css"/>

    <style>
        body {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), 
                        url('img/bg1.jpg') no-repeat center center fixed;
            background-size: cover;
            height: 100vh;
            display: flex;
            align-items: center;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.1) !important;
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            border-radius: 20px;
        }

        .form-control {
            background: rgba(255, 255, 255, 0.15) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            color: white !important;
            border-radius: 10px;
            padding: 1.2rem 1rem;
        }

        /* Group append styling for the eye icon */
        .input-group-text {
            background: rgba(255, 255, 255, 0.2) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            color: white !important;
            border-top-right-radius: 10px !important;
            border-bottom-right-radius: 10px !important;
            cursor: pointer;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.25) !important;
            box-shadow: none;
            border-color: #36b9cc !important;
        }

        .form-control::placeholder { color: rgba(255, 255, 255, 0.6) !important; }

        select.form-control option { background: #333; color: white; }

        label { color: rgba(255, 255, 255, 0.8) !important; font-weight: 500; }
        
        .btn-register {
            background: #36b9cc;
            border: none;
            border-radius: 10px;
            padding: 0.8rem;
            font-weight: bold;
            transition: 0.3s;
            color: white;
        }

        .btn-register:hover {
            background: #2a96a5;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            color: white;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-xl-10 col-lg-10 col-md-10">
                <div class="card glass-card shadow-lg my-5">
                    <div class="card-body p-0">
                        <form method="post" id="register_form">
                            <div class="p-5">
                                <span id="message"></span>
                                <div class="text-center">
                                    <h1 class="h3 text-white font-weight-bold mb-4">
                                        <?php echo $setup_done ? 'Create Account' : 'Set up Master Admin Account'; ?>
                                    </h1>
                                    <p class="text-white-50 mb-4">Please fill in the details below to continue</p>
                                </div>
                                <div class="row">
                                    <?php if(!$setup_done): ?>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Restaurant Name</label>
                                            <input type="text" name="restaurant_name" id="restaurant_name" class="form-control" placeholder="e.g. Tasty Bites" required data-parsley-maxlength="175" data-parsley-trigger="keyup" />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Restaurant Address</label>
                                            <input type="text" name="restaurant_address" id="restaurant_address" class="form-control" placeholder="Street Address" required data-parsley-maxlength="250" data-parsley-trigger="keyup" />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Restaurant Contact No.</label>
                                            <input type="text" name="restaurant_contact_no" id="restaurant_contact_no" class="form-control" placeholder="Contact Number" required data-parsley-type="integer" data-parsley-maxlength="12" data-parsley-trigger="keyup" />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Restaurant Tag Line</label>
                                            <input type="text" name="restaurant_tag_line" id="restaurant_tag_line" class="form-control" placeholder="Tagline" required data-parsley-maxlength="200" data-parsley-trigger="keyup" />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Currency</label>
                                            <?php echo $object->Currency_list(); ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Timezone</label>
                                            <?php echo $object->Timezone_list(); ?>
                                        </div>
                                    </div>
                                    <?php endif; ?>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Email Address</label>
                                            <input type="text" name="user_email" id="user_email" class="form-control" placeholder="email@example.com" required data-parsley-type="email" data-parsley-trigger="keyup" />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Password</label>
                                            <div class="input-group">
                                                <input type="password" name="user_password" id="user_password" class="form-control" placeholder="********" required data-parsley-trigger="keyup" />
                                                <div class="input-group-append">
                                                    <span class="input-group-text" id="togglePassword">
                                                        <i class="fas fa-eye" id="eyeIcon"></i>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12 mt-4" align="center">
                                        <div class="form-group">
                                            <button type="submit" name="register_button" id="register_button" class="btn btn-register btn-block col-md-4">
                                                <i class="fas fa-user-plus mr-2"></i> <?php echo $setup_done ? 'Register' : 'Set Up Account'; ?>
                                            </button>
                                        </div>
                                        <div class="mt-3">
                                            <a href="index.php" class="small text-info font-weight-bold">Back to Login</a>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="setup_done" value="<?php echo $setup_done ? 1 : 0; ?>">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>
    <script type="text/javascript" src="vendor/parsley/dist/parsley.min.js"></script>

    <script>
    $(document).ready(function(){
        $('select').addClass('form-control');
        $('#register_form').parsley();

        // Password Show/Hide Toggle
        $('#togglePassword').on('click', function() {
            const passwordField = $('#user_password');
            const eyeIcon = $('#eyeIcon');
            const type = passwordField.attr('type') === 'password' ? 'text' : 'password';
            passwordField.attr('type', type);
            eyeIcon.toggleClass('fa-eye fa-eye-slash');
        });

        $('#register_form').on('submit', function(event){
            event.preventDefault();
            if($('#register_form').parsley().isValid())
            {       
                $.ajax({
                    url:"register_action.php",
                    method:"POST",
                    data:$(this).serialize(),
                    dataType:'json',
                    beforeSend:function()
                    {
                        $('#register_button').attr('disabled', 'disabled').html('<i class="fas fa-circle-notch fa-spin"></i> Please wait...');
                    },
                    success:function(data)
                    {
                        $('#register_button').attr('disabled', false).html('<?php echo $setup_done ? 'Register' : 'Set Up Account'; ?>');
                        if(data.error != '')
                        {
                            $('#message').html('<div class="alert alert-danger">'+data.error+'</div>');
                        }
                        else
                        {
                            window.location.href="<?php echo $object->base_url; ?>index.php";
                        }
                    }
                })
            }
        });
    });
    </script>
</body>
</html>