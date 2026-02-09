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
    }
    
    .form-control:focus {
        background: rgba(255, 255, 255, 0.15) !important;
        border-color: #17a2b8 !important;
        box-shadow: 0 0 0 0.2rem rgba(23, 162, 184, 0.25);
    }

    label {
        font-weight: 600;
        color: rgba(255, 255, 255, 0.8);
    }

    .img-thumbnail {
        background-color: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
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
        color: white;
    }
</style>

<h1 class="h3 mb-4 text-white">Profile</h1>

<form method="post" id="profile_form" enctype="multipart/form-data">
    <div class="row">
        <div class="col-md-8 col-lg-6">
            <span id="message"></span>
            <div class="card glass-card shadow mb-4">
                <div class="card-header py-3 bg-transparent border-bottom-0">
                    <div class="row align-items-center">
                        <div class="col">
                            <h6 class="m-0 font-weight-bold text-info">Edit Profile Details</h6>
                        </div>
                        <div class="col text-right">
                            <input type="hidden" name="action" value="profile" />
                            <button type="submit" name="edit_button" id="edit_button" class="btn btn-info btn-sm shadow">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="user_name" id="user_name" class="form-control" required data-parsley-pattern="/^[a-zA-Z0-9 \s]+$/" data-parsley-maxlength="175" data-parsley-trigger="keyup" />
                    </div>
                    <div class="form-group">
                        <label>Contact Number</label>
                        <input type="text" name="user_contact_no" id="user_contact_no" class="form-control" required data-parsley-maxlength="12" data-parsley-type="integer" data-parsley-trigger="keyup" />
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="text" name="user_email" id="user_email" class="form-control" required data-parsley-maxlength="175" data-parsley-type="email" data-parsley-trigger="keyup" />
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
                    </div>

                    <div class="form-group">
                        <label>Profile Image</label><br />
                        <input type="file" name="user_image" id="user_image" class="mb-2" />
                        <br />
                        <small class="text-info">Only .jpg, .png file allowed</small><br />
                        <div id="uploaded_image" class="mt-3"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

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
            $('#uploaded_image').html('<img src="<?php echo $row["user_profile"]; ?>" class="img-thumbnail shadow" width="120" /><input type="hidden" name="hidden_user_profile" value="<?php echo $row["user_profile"]; ?>" />');
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
                alert("Invalid Image File");
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

                    $('#user_name').val(data.user_name);
                    $('#user_contact_no').val(data.user_contact_no);
                    $('#user_email').val(data.user_email);
                    $('#user_password').val(data.user_password);
                    
                    if($('#user_profile_name').length) $('#user_profile_name').text(data.user_name);
                    
                    if(data.user_profile != '') {
                        $('#uploaded_image').html('<img src="'+data.user_profile+'" class="img-thumbnail shadow" width="120" /><input type="hidden" name="hidden_user_profile" value="'+data.user_profile+'" />');
                        if($('#user_profile_image').length) $('#user_profile_image').attr('src', data.user_profile);
                    }
                        
                    $('#message').html(data.success);
                    setTimeout(function(){
                        $('#message').html('');
                    }, 5000);
                }
            })
        }
    });
});
</script>