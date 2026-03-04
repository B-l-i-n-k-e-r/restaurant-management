<?php
// setting.php
include('rms.php');
$object = new rms();

if(!$object->is_login())
{
    header("location:".$object->base_url."");
    exit;
}

if(!$object->is_master_user())
{
    header("location:".$object->base_url."dashboard.php");
    exit;
}
else
{
    $object->query = "SELECT * FROM restaurant_table";
    $result = $object->get_result();
}

include('header.php');
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
    }

    .hud-container {
        padding: 40px 20px;
    }

    .settings-hud {
        background: var(--glass-panel);
        backdrop-filter: blur(25px);
        -webkit-backdrop-filter: blur(25px);
        border: 1px solid var(--border-glow);
        border-radius: 30px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 0 40px rgba(0, 210, 255, 0.1);
    }

    .settings-hud::before {
        content: "";
        position: absolute;
        top: 0; left: 0;
        width: 100%; height: 3px;
        background: linear-gradient(90deg, transparent, var(--neon-blue), transparent);
    }

    /* FIX: AUTO HEIGHT & PADDING FOR COMBOBOX */
    .cool-input {
        background: rgba(0, 210, 255, 0.05) !important;
        border: none !important;
        border-bottom: 2px solid rgba(0, 210, 255, 0.1) !important;
        border-radius: 4px !important;
        color: #fff !important;
        padding: 10px 12px !important; /* Fixed padding for better vertical alignment */
        height: auto !important; /* Forces height to fit content */
        min-height: 45px; /* Ensures consistent height across types */
        transition: 0.4s;
    }

    select.cool-input {
        cursor: pointer;
        appearance: none; /* Modern reset */
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%2300d2ff' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: calc(100% - 15px) center;
    }

    select.cool-input option {
        background-color: #0a111a;
        color: #fff;
    }

    .cool-input:focus {
        background: rgba(0, 210, 255, 0.1) !important;
        border-bottom: 2px solid var(--neon-blue) !important;
        box-shadow: 0 5px 15px rgba(0, 210, 255, 0.1) !important;
        outline: none;
    }

    .field-label {
        font-size: 0.7rem;
        letter-spacing: 2px;
        text-transform: uppercase;
        color: var(--neon-blue);
        text-shadow: 0 0 10px rgba(0, 210, 255, 0.5);
        font-weight: 800;
        margin-top: 15px;
        margin-bottom: 5px;
    }

    .btn-cyber {
        background: var(--neon-blue);
        color: #000;
        font-weight: 900;
        letter-spacing: 2px;
        border: none;
        border-radius: 12px;
        padding: 15px 30px;
        text-transform: uppercase;
        transition: 0.3s;
        box-shadow: 0 0 20px rgba(0, 210, 255, 0.3);
    }

    .btn-cyber:hover {
        background: #fff;
        transform: scale(1.02);
    }

    .logo-vault {
        border: 2px dashed var(--border-glow);
        border-radius: 20px;
        padding: 20px;
        background: rgba(0, 210, 255, 0.01);
        min-height: 160px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }

    .img-thumbnail {
        background: transparent;
        border: 2px solid var(--neon-blue);
        box-shadow: 0 0 15px rgba(0, 210, 255, 0.3);
        max-height: 100px;
        max-width: 100%;
        object-fit: contain;
    }

    .char-counter {
        font-size: 0.65rem;
        float: right;
        color: rgba(255,255,255,0.4);
    }
</style>

<div class="hud-container container">
    <div class="mb-5 border-left pl-3" style="border-color: var(--neon-blue) !important;">
        <h1 class="h2 font-weight-bold">GENERAL <span style="color: var(--neon-blue);">SETTINGS</span></h1>
        <div class="small text-uppercase" style="letter-spacing: 4px; color: rgba(0,210,255,0.5);">Wakanesa System Configuration</div>
    </div>

    <span id="message"></span>

    <form method="post" id="setting_form" enctype="multipart/form-data">
        <div class="settings-hud p-4 p-md-5">
            <div class="row">
                <div class="col-lg-7 pr-lg-5">
                    <h5 class="text-white-50 mb-4"><i class="fas fa-fingerprint mr-2 text-info"></i> Restaurant Configuration</h5>
                    
                    <div class="form-group">
                        <p class="field-label">Restaurant Name</p>
                        <input type="text" name="restaurant_name" id="restaurant_name" class="form-control cool-input" required />
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <p class="field-label">Restaurant Email</p>
                                <input type="email" name="restaurant_email" id="restaurant_email" class="form-control cool-input" required />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <p class="field-label">Restaurant Contact No.</p>
                                <input type="text" name="restaurant_contact_no" id="restaurant_contact_no" class="form-control cool-input" required />
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <p class="field-label">Restaurant Address</p>
                        <textarea name="restaurant_address" id="restaurant_address" class="form-control cool-input" rows="3" required></textarea>
                    </div>

                    <div class="form-group">
                        <span class="char-counter" id="tag_counter">0 / 50</span>
                        <p class="field-label">Tag Line</p>
                        <input type="text" name="restaurant_tag_line" id="restaurant_tag_line" maxlength="50" class="form-control cool-input" />
                    </div>
                </div>

                <div class="col-lg-5">
                    <h5 class="text-white-50 mb-4"><i class="fas fa-globe mr-2 text-info"></i> GLOBAL SYNC</h5>
                    
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <p class="field-label">Currency</p>
                                <?php echo $object->Currency_list(); ?>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <p class="field-label">Timezone</p>
                                <?php echo $object->Timezone_list(); ?>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <p class="field-label">Business Logo</p>
                        <div class="logo-vault text-center">
                            <div id="uploaded_logo" class="mb-3 w-100">
                                <i class="fas fa-cloud-upload-alt fa-3x text-white-50"></i>
                            </div>
                            <input type="file" name="restaurant_logo" id="restaurant_logo" class="d-none" accept="image/*" />
                            <button type="button" class="btn btn-outline-info btn-sm btn-block" onclick="document.getElementById('restaurant_logo').click();">
                                <i class="fas fa-microchip mr-2"></i> UPLOAD NEW LOGO
                            </button>
                        </div>
                    </div>

                    <div class="mt-5">
                        <button type="submit" name="edit_button" id="edit_button" class="btn-cyber btn-block">
                            Authorize Update
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php include('footer.php'); ?>

<script>
$(document).ready(function(){
    // Ensure comboboxes use the cool-input style
    $('select').addClass('form-control cool-input');

    /* Tag Line Character Counter */
    $('#restaurant_tag_line').on('input', function() {
        var len = $(this).val().length;
        $('#tag_counter').text(len + ' / 50');
    });

    /* Initial Data Load */
    <?php foreach($result as $row) { ?>
        $('#restaurant_name').val("<?php echo addslashes($row['restaurant_name']); ?>");
        $('#restaurant_email').val("<?php echo $row['restaurant_email']; ?>");
        $('#restaurant_contact_no').val("<?php echo $row['restaurant_contact_no']; ?>");
        $('#restaurant_address').val("<?php echo addslashes($row['restaurant_address']); ?>");
        $('#restaurant_currency').val("<?php echo $row['restaurant_currency']; ?>");
        $('#restaurant_timezone').val("<?php echo $row['restaurant_timezone']; ?>");
        $('#restaurant_tag_line').val("<?php echo addslashes($row['restaurant_tag_line']); ?>").trigger('input');
        
        <?php if($row["restaurant_logo"] != '') { ?>
            $('#uploaded_logo').html('<img src="<?php echo $row["restaurant_logo"]; ?>?t=<?php echo time(); ?>" class="img-thumbnail" /><input type="hidden" name="hidden_restaurant_logo" value="<?php echo $row["restaurant_logo"]; ?>" />');
        <?php } ?>
    <?php } ?>

    /* Live Preview on Selection */
    $('#restaurant_logo').change(function(){
        var file = this.files[0];
        var ext = $(this).val().split('.').pop().toLowerCase();
        if(ext != '' && $.inArray(ext, ['png','jpg','jpeg']) == -1) {
            alert("Format Error: Only JPG, JPEG, or PNG allowed.");
            $(this).val('');
        } else {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#uploaded_logo').html('<img src="'+e.target.result+'" class="img-thumbnail" />');
            }
            reader.readAsDataURL(file);
        }
    });

    /* AJAX Submission */
    $('#setting_form').on('submit', function(event){
        event.preventDefault();
        $.ajax({
            url:"setting_action.php",
            method:"POST",
            data:new FormData(this),
            dataType:'json',
            contentType:false,
            processData:false,
            beforeSend:function() {
                $('#edit_button').attr('disabled', 'disabled').html('<i class="fas fa-sync fa-spin"></i> AUTHORIZING...');
            },
            success:function(data) {
                $('#edit_button').attr('disabled', false).html('Authorize Update');
                if(data.success != '') {
                    $('#message').html('<div class="alert alert-info border-0" style="background:rgba(0,210,255,0.2); color:#fff; border-radius:15px;">'+data.success+'</div>');
                    
                    // Force update logo image from response
                    if(data.restaurant_logo && data.restaurant_logo != '') {
                        $('#uploaded_logo').html('<img src="'+data.restaurant_logo+'?t='+new Date().getTime()+'" class="img-thumbnail" /><input type="hidden" name="hidden_restaurant_logo" value="'+data.restaurant_logo+'" />');
                    }
                    
                    setTimeout(function(){ $('#message').html(''); }, 5000);
                }
            },
            error: function(xhr) {
                console.error("System Fault: " + xhr.responseText);
                $('#edit_button').attr('disabled', false).html('Authorize Update');
            }
        });
    });
});
</script>