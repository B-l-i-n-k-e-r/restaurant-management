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
    /* Glassmorphism Card Styling */
    .glass-card {
        background: rgba(255, 255, 255, 0.05) !important;
        backdrop-filter: blur(15px);
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        border-radius: 15px;
        color: white;
    }

    /* Form Input Styling */
    .form-control {
        background: rgba(255, 255, 255, 0.1) !important;
        border: 1px solid rgba(255, 255, 255, 0.2) !important;
        color: white !important;
        border-radius: 8px;
    }
    
    .form-control:focus {
        background: rgba(255, 255, 255, 0.15) !important;
        border-color: #17a2b8 !important;
        box-shadow: 0 0 0 0.2rem rgba(23, 162, 184, 0.25);
    }

    label {
        font-weight: 600;
        color: rgba(255, 255, 255, 0.8);
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
    }

    .img-thumbnail {
        background-color: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 10px;
    }

    /* Input Group / Show Password Styling */
    .input-group-append .btn {
        background: rgba(255, 255, 255, 0.1) !important;
        border: 1px solid rgba(255, 255, 255, 0.2) !important;
        border-left: none !important;
        color: #17a2b8 !important;
        transition: 0.3s;
    }

    .input-group-append .btn:hover {
        background: rgba(255, 255, 255, 0.2) !important;
    }

    .input-group > .form-control:not(:last-child) {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }

    input[type="file"] {
        color: rgba(255, 255, 255, 0.7);
        font-size: 0.9rem;
    }
</style>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-white">Profile Settings</h1>

    <form method="post" id="profile_form" enctype="multipart/form-data">
        <div class="row">
            <div class="col-md-10 col-lg-8 mx-auto">
                <span id="message"></span>
                <div class="card glass-card shadow-lg mb-4">
                    <div class="card-header py-3 bg-transparent border-bottom-0">
                        <div class="row align-items-center">
                            <div class="col">
                                <h6 class="m-0 font-weight-bold text-info"><i class="fas fa-user-edit mr-2"></i>Edit Personal Details</h6>
                            </div>
                            <div class="col text-right">
                                <input type="hidden" name="action" value="profile" />
                                <button type="submit" name="edit_button" id="edit_button" class="btn btn-info px-4 shadow-sm">
                                    <i class="fas fa-save mr-1"></i> Save Changes
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body px-4">
                        <div class="row">
                            <div class="col-md-4 text-center border-right border-secondary">
                                <div id="uploaded_image" class="mb-3">
                                    </div>
                                <div class="form-group">
                                    <label>Update Photo</label>
                                    <input type="file" name="user_image" id="user_image" class="form-control-file d-none" />
                                    <button type="button" class="btn btn-outline-light btn-sm btn-block" onclick="document.getElementById('user_image').click();">
                                        <i class="fas fa-upload mr-1"></i> Choose File
                                    </button>
                                    <small class="text-muted d-block mt-2">Allowed: JPG, PNG</small>
                                </div>
                            </div>

                            <div class="col-md-8">
                                <div class="form-group">
                                    <label>Full Name</label>
                                    <input type="text" name="user_name" id="user_name" class="form-control" required data-parsley-pattern="/^[a-zA-Z0-9 \s]+$/" data-parsley-maxlength="175" data-parsley-trigger="keyup" />
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Contact Number</label>
                                            <input type="text" name="user_contact_no" id="user_contact_no" class="form-control" required data-parsley-maxlength="12" data-parsley-type="integer" data-parsley-trigger="keyup" />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Email Address</label>
                                            <input type="text" name="user_email" id="user_email" class="form-control" required data-parsley-maxlength="175" data-parsley-type="email" data-parsley-trigger="keyup" />
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label>Password</label>
                                    <div class="input-group">
                                        <input type="password" name="user_password" id="user_password" class="form-control" required data-parsley-maxlength="16" data-parsley-trigger="keyup" />
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-info" type="button" id="toggle_password">
                                                <i class="fas fa-eye" id="password_icon"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <small class="text-info">Keep it secure.</small>
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
            $('#uploaded_image').html('<img src="<?php echo $row["user_profile"]; ?>" class="img-thumbnail shadow mb-2" style="width: 150px; height: 150px; object-fit: cover;" /><input type="hidden" name="hidden_user_profile" value="<?php echo $row["user_profile"]; ?>" />');
        <?php } else { ?>
            $('#uploaded_image').html('<img src="img/undraw_profile.svg" class="img-thumbnail shadow mb-2" width="150" />');
        <?php } ?>
    <?php } ?>

    /* ================= TOGGLE PASSWORD LOGIC ================= */
    $('#toggle_password').click(function(){
        var passwordField = $('#user_password');
        var icon = $('#password_icon');
        
        if(passwordField.attr('type') === 'password') {
            passwordField.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            passwordField.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    /* ================= IMAGE VALIDATION ================= */
    $('#user_image').change(function(){
        var extension = $('#user_image').val().split('.').pop().toLowerCase();
        if(extension != '') {
            if(jQuery.inArray(extension, ['png','jpg','jpeg']) == -1) {
                alert("Invalid Image File. Please use JPG or PNG.");
                $('#user_image').val('');
                return false;
            }
        }
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
                    $('#edit_button').attr('disabled', 'disabled');
                    $('#edit_button').html('<i class="fas fa-spinner fa-spin"></i> Wait...');
                },
                success:function(data) {
                    $('#edit_button').attr('disabled', false);
                    $('#edit_button').html('<i class="fas fa-save"></i> Save Changes');

                    if(data.success != '') {
                        // Update current page inputs
                        $('#user_name').val(data.user_name);
                        $('#user_contact_no').val(data.user_contact_no);
                        $('#user_email').val(data.user_email);
                        $('#user_password').val(data.user_password);
                        
                        // Sync with Header Profile (Immediate UI feedback)
                        $('.text-white.small').text(data.user_name); // Updates Topbar Name
                        
                        if(data.user_profile != '') {
                            $('#uploaded_image img').attr('src', data.user_profile);
                            $('.img-profile').attr('src', data.user_profile); // Updates Topbar Image
                            $('input[name="hidden_user_profile"]').val(data.user_profile);
                        }
                            
                        $('#message').html('<div class="alert alert-success">'+data.success+'</div>');
                        setTimeout(function(){
                            $('#message').html('');
                        }, 5000);
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