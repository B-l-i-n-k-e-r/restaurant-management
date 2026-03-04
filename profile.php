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
?>

<style>
    :root {
        --glass: rgba(255, 255, 255, 0.03);
        --glass-border: rgba(255, 255, 255, 0.1);
        --neon-gold: #f39c12;
        --neon-blue: #00d2ff;
        --bg-deep: #050505;
    }

    body { background-color: var(--bg-deep); color: #e0e0e0; }

    /* Glassmorphism Card Styling */
    .glass-card {
        background: var(--glass) !important;
        backdrop-filter: blur(20px);
        border: 1px solid var(--glass-border) !important;
        border-radius: 24px;
        box-shadow: 0 15px 35px rgba(0,0,0,0.5);
    }

    /* Form Input Styling */
    .form-control {
        background: rgba(255, 255, 255, 0.05) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        color: white !important;
        border-radius: 12px;
        padding: 12px 15px;
        transition: all 0.3s ease;
    }
    
    .form-control:focus {
        background: rgba(255, 255, 255, 0.08) !important;
        border-color: var(--neon-gold) !important;
        box-shadow: 0 0 15px rgba(243, 156, 18, 0.2);
    }

    label {
        font-weight: 600;
        color: rgba(255, 255, 255, 0.6);
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 0.5rem;
    }

    .profile-avatar-container {
        position: relative;
        display: inline-block;
    }

    .img-thumbnail {
        background-color: var(--glass);
        border: 2px solid var(--glass-border);
        border-radius: 50% !important; /* Circular profile */
        padding: 5px;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .img-thumbnail:hover {
        transform: scale(1.05);
        border-color: var(--neon-gold);
    }

    /* Input Group Styling */
    .input-group-append .btn {
        background: rgba(255, 255, 255, 0.05) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        border-left: none !important;
        color: var(--neon-gold) !important;
        border-radius: 0 12px 12px 0;
    }

    .btn-info {
        background: var(--neon-gold);
        border: none;
        color: #000;
        font-weight: 700;
        border-radius: 12px;
        transition: 0.3s;
    }

    .btn-info:hover {
        background: #e67e22;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(243, 156, 18, 0.4);
    }

    .parsley-errors-list {
        color: #ff4757;
        font-size: 0.8rem;
        list-style: none;
        padding-left: 0;
        margin-top: 5px;
    }
</style>

<div class="container py-5">
    <div class="row mb-4">
        <div class="col-md-10 col-lg-8 mx-auto">
            <h1 class="h2 font-weight-bold mb-0">ACCOUNT <span style="color: var(--neon-gold);">PROFILE</span></h1>
            <p class="text-muted">Manage your personal information and security settings.</p>
        </div>
    </div>

    <form method="post" id="profile_form" enctype="multipart/form-data">
        <div class="row">
            <div class="col-md-10 col-lg-8 mx-auto">
                <div id="message"></div>
                
                <div class="card glass-card mb-4">
                    <div class="card-body p-4 p-md-5">
                        <div class="row">
                            <div class="col-md-4 text-center mb-4 mb-md-0">
                                <div class="profile-avatar-container mb-3">
                                    <div id="uploaded_image"></div>
                                </div>
                                <div class="form-group mt-3">
                                    <input type="file" name="user_image" id="user_image" class="d-none" accept="image/*" />
                                    <button type="button" class="btn btn-outline-light btn-sm px-4 rounded-pill" onclick="document.getElementById('user_image').click();">
                                        <i class="fas fa-camera mr-2"></i> CHANGE PHOTO
                                    </button>
                                    <small class="text-muted d-block mt-2">JPG or PNG (Max 2MB)</small>
                                </div>
                            </div>

                            <div class="col-md-8">
                                <div class="form-group mb-4">
                                    <label>Full Name</label>
                                    <input type="text" name="user_name" id="user_name" class="form-control" required data-parsley-pattern="/^[a-zA-Z0-9 \s]+$/" data-parsley-trigger="keyup" />
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-4">
                                            <label>Contact Number</label>
                                            <input type="text" name="user_contact_no" id="user_contact_no" class="form-control" required data-parsley-type="integer" data-parsley-trigger="keyup" />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-4">
                                            <label>Email Address</label>
                                            <input type="email" name="user_email" id="user_email" class="form-control" required data-parsley-trigger="keyup" />
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group mb-4">
                                    <label>Account Password</label>
                                    <div class="input-group">
                                        <input type="password" name="user_password" id="user_password" class="form-control" required data-parsley-minlength="6" data-parsley-trigger="keyup" />
                                        <div class="input-group-append">
                                            <button class="btn" type="button" id="toggle_password">
                                                <i class="fas fa-eye" id="password_icon"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <small class="text-muted">Password must be at least 6 characters.</small>
                                </div>

                                <div class="mt-5">
                                    <input type="hidden" name="action" value="profile" />
                                    <button type="submit" name="edit_button" id="edit_button" class="btn btn-info btn-block py-3">
                                        <i class="fas fa-save mr-2"></i> UPDATE PROFILE
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php include('footer.php'); ?>

<script>
$(document).ready(function(){

    /* ================= POPULATE FORM ================= */
    <?php foreach($result as $row) { ?>
        $('#user_name').val("<?php echo $row['user_name']; ?>");
        $('#user_contact_no').val("<?php echo $row['user_contact_no']; ?>");
        $('#user_email').val("<?php echo $row['user_email']; ?>");
        $('#user_password').val("<?php echo $row['user_password']; ?>");
        <?php if($row["user_profile"] != '') { ?>
            $('#uploaded_image').html('<img src="<?php echo $row["user_profile"]; ?>" class="img-thumbnail shadow" style="width: 140px; height: 140px;" /><input type="hidden" name="hidden_user_profile" value="<?php echo $row["user_profile"]; ?>" />');
        <?php } else { ?>
            $('#uploaded_image').html('<img src="img/undraw_profile.svg" class="img-thumbnail shadow" width="140" height="140" />');
        <?php } ?>
    <?php } ?>

    /* ================= TOGGLE PASSWORD ================= */
    $('#toggle_password').click(function(){
        let passwordField = $('#user_password');
        let icon = $('#password_icon');
        let type = passwordField.attr('type') === 'password' ? 'text' : 'password';
        passwordField.attr('type', type);
        icon.toggleClass('fa-eye fa-eye-slash');
    });

    /* ================= PREVIEW IMAGE ================= */
    $('#user_image').change(function(e){
        let extension = $(this).val().split('.').pop().toLowerCase();
        if(jQuery.inArray(extension, ['png','jpg','jpeg']) == -1) {
            Swal.fire('Error', 'Invalid Image Format (Use JPG/PNG)', 'error');
            $(this).val('');
            return false;
        }
        // Simple preview if desired, but AJAX update handles it on success below
    });

    /* ================= SUBMIT FORM ================= */
    $('#profile_form').parsley();

    $('#profile_form').on('submit', function(event){
        event.preventDefault();
        if($('#profile_form').parsley().isValid()) {       
            $.ajax({
                url:"user_action.php",
                method:"POST",
                data:new FormData(this),
                dataType:'json',
                contentType:false,
                processData:false,
                beforeSend:function() {
                    $('#edit_button').attr('disabled', 'disabled').html('<i class="fas fa-circle-notch fa-spin mr-2"></i> SAVING...');
                },
                success:function(data) {
                    $('#edit_button').attr('disabled', false).html('<i class="fas fa-save mr-2"></i> UPDATE PROFILE');

                    if(data.success != '') {
                        // Sync with Header UI (Immediate feedback)
                        $('.text-white.small').text($('#user_name').val()); 
                        
                        if(data.user_profile != '') {
                            $('#uploaded_image img').attr('src', data.user_profile);
                            $('.img-profile').attr('src', data.user_profile); 
                            $('input[name="hidden_user_profile"]').val(data.user_profile);
                        }
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'Profile Updated',
                            text: data.success,
                            background: '#1a1a1a',
                            color: '#fff',
                            confirmButtonColor: '#f39c12'
                        });
                    }

                    if(data.error != '') {
                        $('#message').html('<div class="alert alert-danger">'+data.error+'</div>');
                    }
                }
            })
        }
    });
});
</script>