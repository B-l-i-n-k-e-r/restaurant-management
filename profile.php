<?php
// profile.php
include('rms.php');
$object = new rms();

if(!$object->is_login())
{
    header("location:".$object->base_url."");
    exit;
}

/* ================= FETCH USER DATA SECURELY ================= */
$object->query = "SELECT * FROM user_table WHERE user_id = :user_id";
$object->execute(['user_id' => $_SESSION["user_id"]]);
$result = $object->statement_result();

include('header.php');

// Identify if the user is a staff member
$is_staff = ($_SESSION['user_type'] !== 'User');
?>

<style>
    :root {
        --neon-blue: #00d2ff;
        --deep-cyan: #0080ff;
        --cyber-black: #06070a;
        --glass-panel: rgba(0, 210, 255, 0.03);
        --border-glow: rgba(0, 210, 255, 0.2);
    }

    body {
        background: radial-gradient(circle at top right, #0a111a, var(--cyber-black));
        color: #fff;
        font-family: 'Inter', 'Segoe UI', sans-serif;
    }

    .hud-container {
        position: relative;
        padding: 60px 20px;
    }

    .profile-hud {
        background: var(--glass-panel);
        backdrop-filter: blur(25px);
        -webkit-backdrop-filter: blur(25px);
        border: 1px solid var(--border-glow);
        border-radius: 40px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 0 40px rgba(0, 210, 255, 0.1);
    }

    .profile-hud::before {
        content: "";
        position: absolute;
        top: 0; left: 0;
        width: 100%; height: 3px;
        background: linear-gradient(90deg, transparent, var(--neon-blue), transparent);
    }

    .avatar-wrapper {
        position: relative;
        width: 160px;
        height: 160px;
        margin: 0 auto;
    }

    .avatar-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
        border: 2px solid transparent;
        background: linear-gradient(var(--cyber-black), var(--cyber-black)) padding-box,
                    linear-gradient(135deg, var(--neon-blue), var(--deep-cyan)) border-box;
        box-shadow: 0 0 30px rgba(0, 210, 255, 0.25);
    }

    .cool-input {
        background: rgba(0, 210, 255, 0.02) !important;
        border: none !important;
        border-bottom: 2px solid rgba(0, 210, 255, 0.1) !important;
        border-radius: 0 !important;
        color: #fff !important;
        font-size: 1.1rem;
        padding: 15px 0 !important;
        transition: 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .cool-input:focus {
        background: transparent !important;
        border-bottom: 2px solid var(--neon-blue) !important;
        box-shadow: none !important;
        transform: translateX(5px);
    }

    /* Styling for locked staff email */
    .cool-input[readonly] {
        cursor: not-allowed;
        border-bottom: 2px solid rgba(255, 255, 255, 0.05) !important;
        color: rgba(255, 255, 255, 0.5) !important;
        opacity: 0.6;
    }

    .field-label {
        font-size: 0.7rem;
        letter-spacing: 2.5px;
        text-transform: uppercase;
        color: var(--neon-blue);
        text-shadow: 0 0 10px rgba(0, 210, 255, 0.5);
        font-weight: 800;
        margin-bottom: 5px;
    }

    .btn-update {
        background: var(--neon-blue);
        color: #000;
        font-weight: 900;
        letter-spacing: 2px;
        border: none;
        border-radius: 12px;
        padding: 20px;
        width: 100%;
        text-transform: uppercase;
        position: relative;
        transition: 0.3s;
        box-shadow: 0 0 20px rgba(0, 210, 255, 0.3);
    }

    .btn-update:hover {
        background: #fff;
        transform: scale(1.02);
        box-shadow: 0 0 40px rgba(255, 255, 255, 0.2);
    }

    .data-deco {
        font-family: 'Courier New', monospace;
        font-size: 10px;
        color: var(--neon-blue);
        opacity: 0.5;
        position: absolute;
        top: 25px;
        right: 45px;
        text-align: right;
        animation: pulseText 2s infinite ease-in-out;
    }

    @keyframes pulseText {
        0%, 100% { opacity: 0.3; }
        50% { opacity: 0.7; }
    }
</style>

<div class="hud-container container">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            
            <div class="text-center mb-5">
                <h1 class="display-4 font-weight-bold" style="letter-spacing: -2px;">PROFILE <span style="color: var(--neon-blue); text-shadow: 0 0 20px rgba(0,210,255,0.6);">SETTINGS</span></h1>
                <div class="small text-uppercase mt-2" style="letter-spacing: 5px; color: rgba(0,210,255,0.5);">Edit Personal Details</div>
            </div>

            <form method="post" id="profile_form" enctype="multipart/form-data">
                <div class="profile-hud p-4 p-md-5">
                    
                    <div class="data-deco">
                        SYSTEM_STATUS: ONLINE<br>
                        ENCRYPTION: ACTIVE<br>
                        NODE_ID: <?php echo $_SESSION["user_id"]; ?>
                    </div>
                    
                    <div class="row align-items-center">
                        <div class="col-md-4 text-center mb-5 mb-md-0">
                            <div class="avatar-wrapper mb-4">
                                <div id="uploaded_image"></div>
                            </div>
                            <input type="file" name="user_image" id="user_image" class="d-none" accept="image/*" />
                            <button type="button" class="btn btn-sm text-info font-weight-bold" onclick="document.getElementById('user_image').click();" style="letter-spacing: 1px; background: rgba(0,210,255,0.1); border-radius: 20px; padding: 5px 15px;">
                                <i class="fas fa-camera mr-2"></i> NEW PHOTO
                            </button>
                        </div>

                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-12 mb-4">
                                    <p class="field-label">Full Name</p>
                                    <input type="text" name="user_name" id="user_name" class="form-control cool-input" required />
                                </div>
                                <div class="col-md-6 mb-4">
                                    <p class="field-label">Email Address <?php if($is_staff) echo '<i class="fas fa-lock ml-2" style="font-size:10px; opacity:0.5;"></i>'; ?></p>
                                    <input type="email" name="user_email" id="user_email" class="form-control cool-input" required <?php if($is_staff) echo 'readonly'; ?> />
                                    <?php if($is_staff): ?>
                                        <small class="text-white-50" style="font-size: 10px;">Staff emails are system-managed.</small>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <p class="field-label">Contact</p>
                                    <input type="text" name="user_contact_no" id="user_contact_no" class="form-control cool-input" required />
                                </div>
                                <div class="col-12 mb-5">
                                    <p class="field-label">Password</p>
                                    <div class="input-group">
                                        <input type="password" name="user_password" id="user_password" class="form-control cool-input" placeholder="Leave blank to keep current" />
                                        <div class="input-group-append">
                                            <button class="btn text-info" type="button" id="toggle_password">
                                                <i class="fas fa-eye" id="password_icon"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="text-right">
                                <input type="hidden" name="action" value="profile" />
                                <button type="submit" name="edit_button" id="edit_button" class="btn-update">
                                    Update Changes
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            
            <div id="message" class="mt-4"></div>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>

<script>
$(document).ready(function(){

    /* DATA POPULATION */
    <?php foreach($result as $row) { ?>
        $('#user_name').val("<?php echo $row['user_name']; ?>");
        $('#user_contact_no').val("<?php echo $row['user_contact_no']; ?>");
        $('#user_email').val("<?php echo $row['user_email']; ?>");
        // Clear password field by default for security/logic
        $('#user_password').val(""); 
        <?php if($row["user_profile"] != '') { ?>
            $('#uploaded_image').html('<img src="<?php echo $row["user_profile"]; ?>" /> <input type="hidden" name="hidden_user_profile" value="<?php echo $row["user_profile"]; ?>" />');
        <?php } else { ?>
            $('#uploaded_image').html('<img src="img/undraw_profile.svg" />');
        <?php } ?>
    <?php } ?>

    $('#toggle_password').click(function(){
        let passwordField = $('#user_password');
        let icon = $('#password_icon');
        let type = passwordField.attr('type') === 'password' ? 'text' : 'password';
        passwordField.attr('type', type);
        icon.toggleClass('fa-eye fa-eye-slash');
    });

    /* AJAX SUBMIT */
    $('#profile_form').on('submit', function(event){
        event.preventDefault();
        $.ajax({
            url:"user_action.php",
            method:"POST",
            data:new FormData(this),
            dataType:'json',
            contentType:false,
            processData:false,
            beforeSend:function() {
                $('#edit_button').attr('disabled', 'disabled').text('UPDATING...');
            },
            success:function(data) {
                $('#edit_button').attr('disabled', false).text('Update Changes');
                if(data.success != '') {
                    $('#message').html('<div class="alert alert-info border-0 text-white" style="border-radius:15px; background: rgba(0,210,255,0.2) !important;">'+data.success+'</div>');
                    $('#user_password').val(""); // Reset password field after update
                    
                    if(data.user_profile != '') {
                        $('#uploaded_image img').attr('src', data.user_profile);
                    }
                }
                if(data.error != '') {
                    $('#message').html('<div class="alert alert-danger border-0 text-white" style="border-radius:15px; background: rgba(220,53,69,0.2) !important;">'+data.error+'</div>');
                }
            }
        });
    });
});
</script>