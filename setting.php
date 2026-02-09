<?php
// setting.php
include('rms.php');
$object = new rms();

if(!$object->is_login())
{
    header("location:".$object->base_url."");
}

if(!$object->is_master_user())
{
    header("location:".$object->base_url."dashboard.php");
}
else
{
    $object->query = "SELECT * FROM restaurant_table";
    $result = $object->get_result();
}

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

    /* Form Control Glass Styling */
    .form-control, .form-control:focus {
        background: rgba(255, 255, 255, 0.1) !important;
        border: 1px solid rgba(255, 255, 255, 0.2) !important;
        color: white !important;
    }
    
    /* Select Dropdown Styling */
    select.form-control option {
        background-color: #333;
        color: white;
    }

    label {
        font-weight: 600;
        color: rgba(255, 255, 255, 0.8);
    }

    .img-thumbnail {
        background-color: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    input[type="file"] {
        color: white;
    }
</style>

<h1 class="h3 mb-4 text-white">General Settings</h1>

<span id="message"></span>

<form method="post" id="setting_form" enctype="multipart/form-data">
    <div class="card glass-card shadow mb-4">
        <div class="card-header py-3 bg-transparent border-bottom-0">
            <div class="row align-items-center">
                <div class="col">
                    <h6 class="m-0 font-weight-bold text-info">Restaurant Configuration</h6>
                </div>
                <div class="col text-right">
                    <button type="submit" name="edit_button" id="edit_button" class="btn btn-info btn-sm shadow">
                        <i class="fas fa-edit"></i> Update Settings
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Restaurant Name</label>
                        <input type="text" name="restaurant_name" id="restaurant_name" class="form-control" required />
                    </div>
                    <div class="form-group">
                        <label>Restaurant Email</label>
                        <input type="email" name="restaurant_email" id="restaurant_email" class="form-control" required />
                    </div>
                    <div class="form-group">
                        <label>Restaurant Contact No.</label>
                        <input type="text" name="restaurant_contact_no" id="restaurant_contact_no" class="form-control" required />
                    </div>
                    <div class="form-group">
                        <label>Restaurant Address</label>
                        <textarea name="restaurant_address" id="restaurant_address" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Tag Line</label>
                        <input type="text" name="restaurant_tag_line" id="restaurant_tag_line" class="form-control" />
                    </div>
                    <div class="form-group">
                        <label>Currency</label>
                        <?php echo $object->Currency_list(); ?>
                    </div>
                    <div class="form-group">
                        <label>Timezone</label>
                        <?php echo $object->Timezone_list(); ?>
                    </div>
                    <div class="form-group">
                        <label>Select Logo</label><br />
                        <input type="file" name="restaurant_logo" id="restaurant_logo" class="mb-2" />
                        <br />
                        <small class="text-info">Only .jpg, .png file allowed</small><br />
                        <div id="uploaded_logo" class="mt-3"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<?php include('footer.php'); ?>

<script>
$(document).ready(function(){

    <?php
    foreach($result as $row)
    {
    ?>
    $('#restaurant_name').val("<?php echo $row['restaurant_name']; ?>");
    $('#restaurant_email').val("<?php echo $row['restaurant_email']; ?>");
    $('#restaurant_contact_no').val("<?php echo $row['restaurant_contact_no']; ?>");
    $('#restaurant_address').val("<?php echo $row['restaurant_address']; ?>");
    $('#restaurant_currency').val("<?php echo $row['restaurant_currency']; ?>");
    $('#restaurant_timezone').val("<?php echo $row['restaurant_timezone']; ?>");
    $('#restaurant_tag_line').val("<?php echo $row['restaurant_tag_line']; ?>");
    <?php
        if($row["restaurant_logo"] != '')
        {
    ?>
    $('#uploaded_logo').html('<img src="<?php echo $row["restaurant_logo"]; ?>" class="img-thumbnail shadow" width="100" /><input type="hidden" name="hidden_restaurant_logo" value="<?php echo $row["restaurant_logo"]; ?>" />');
    <?php
        }
    }
    ?>

    $('#restaurant_logo').change(function(){
        var extension = $('#restaurant_logo').val().split('.').pop().toLowerCase();
        if(extension != '')
        {
            if(jQuery.inArray(extension, ['png','jpg','jpeg']) == -1)
            {
                alert("Invalid Image File");
                $('#restaurant_logo').val('');
                return false;
            }
        }
    });

    $('#setting_form').parsley();

    $('#setting_form').on('submit', function(event){
        event.preventDefault();
        if($('#setting_form').parsley().isValid())
        {       
            $.ajax({
                url:"setting_action.php",
                method:"POST",
                data:new FormData(this),
                dataType:'json',
                contentType:false,
                processData:false,
                beforeSend:function()
                {
                    $('#edit_button').attr('disabled', 'disabled');
                    $('#edit_button').html('<i class="fas fa-spinner fa-spin"></i> Wait...');
                },
                success:function(data)
                {
                    $('#edit_button').attr('disabled', false);
                    $('#edit_button').html('<i class="fas fa-edit"></i> Update Settings');

                    $('#restaurant_name').val(data.restaurant_name);
                    $('#restaurant_email').val(data.restaurant_email);
                    $('#restaurant_contact_no').val(data.restaurant_contact_no);
                    $('#restaurant_address').val(data.restaurant_address);
                    $('#restaurant_currency').val(data.restaurant_currency);
                    $('#restaurant_timezone').val(data.restaurant_timezone);
                    $('#restaurant_tag_line').val(data.restaurant_tag_line);

                    if(data.restaurant_logo != '')
                    {
                        $('#uploaded_logo').html('<img src="'+data.restaurant_logo+'" class="img-thumbnail shadow" width="100" /><input type="hidden" name="hidden_restaurant_logo" value="'+data.restaurant_logo+'" />');
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