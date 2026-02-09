<?php
// user.php
include('rms.php');
$object = new rms();

if(!$object->is_login()) {
    header("location:".$object->base_url."");
    exit;
}
if(!$object->is_master_user()) {
    header("location:".$object->base_url."dashboard.php");
    exit;
}
include('header.php');
?>

<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.05) !important;
        backdrop-filter: blur(15px);
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        border-radius: 15px;
        color: white;
    }
    .table { color: white !important; }
    .table-bordered { border: 1px solid rgba(255, 255, 255, 0.1) !important; }
    .table-bordered td, .table-bordered th { border: 1px solid rgba(255, 255, 255, 0.1) !important; }
    .modal-content {
        background: rgba(30, 30, 30, 0.9) !important;
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: white;
    }
    .form-control {
        background: rgba(255, 255, 255, 0.1) !important;
        border: 1px solid rgba(255, 255, 255, 0.2) !important;
        color: white !important;
    }
    .form-control option { background: #333; color: white; }
    .dataTables_info, .dataTables_length, .dataTables_filter, .dataTables_paginate { color: white !important; }
    .page-link {
        background-color: rgba(255,255,255,0.1) !important;
        border-color: rgba(255,255,255,0.1) !important;
        color: white !important;
    }
    input[type="file"] { color: white; }
    .view_password { cursor: pointer; text-decoration: none !important; }
    
    .input-group-text-custom {
        background: rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: white;
        cursor: pointer;
    }
</style>

<h1 class="h3 mb-4 text-white">User Management</h1>
<span id="message"></span>

<div class="card glass-card shadow mb-4">
    <div class="card-header py-3 bg-transparent border-bottom-0">
        <div class="row">
            <div class="col">
                <h6 class="m-0 font-weight-bold text-info">User List</h6>
            </div>
            <div class="col text-right">
                <button type="button" id="add_user" class="btn btn-success btn-circle btn-sm shadow">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="user_table" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Profile Photo</th>
                        <th>User Name</th>
                        <th>Contact No.</th>
                        <th>Email</th>
                        <th>Password</th>
                        <th>Type</th>
                        <th>Created On</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<div id="userModal" class="modal fade" data-backdrop="static">
    <div class="modal-dialog">
        <form method="post" id="user_form" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header border-bottom-0">
                    <h4 class="modal-title" id="modal_title">Add User</h4>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <span id="form_message"></span>
                    <div class="form-group">
                        <label>User Name <span class="text-danger">*</span></label>
                        <input type="text" name="user_name" id="user_name" class="form-control" required data-parsley-trigger="keyup" />
                    </div>
                    <div class="form-group">
                        <label>User Contact No. <span class="text-danger">*</span></label>
                        <input type="text" name="user_contact_no" id="user_contact_no" class="form-control" required data-parsley-type="integer" data-parsley-trigger="keyup" />
                    </div>
                    <div class="form-group">
                        <label>User Email <span class="text-danger">*</span></label>
                        <input type="email" name="user_email" id="user_email" class="form-control" required data-parsley-type="email" data-parsley-trigger="keyup" />
                    </div>
                    <div class="form-group">
                        <label>User Password <span id="password_label" class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" name="user_password" id="user_password" class="form-control" data-parsley-trigger="keyup" />
                            <div class="input-group-append">
                                <span class="input-group-text input-group-text-custom" id="toggleModalPassword">
                                    <i class="fas fa-eye" id="modalEyeIcon"></i>
                                </span>
                            </div>
                        </div>
                        <small class="text-white-50" id="password_help"></small>
                    </div>
                    <div class="form-group">
                        <label>User Type <span class="text-danger">*</span></label>
                        <select name="user_type" id="user_type" class="form-control" required>
                            <option value="">Select Type</option>
                            <option value="Master">Master (Admin)</option>
                            <option value="Waiter">Waiter</option>
                            <option value="Cashier">Cashier</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>User Profile</label><br />
                        <input type="file" name="user_image" id="user_image" class="mb-2" accept="image/*" />
                        <div id="user_uploaded_image"></div>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <input type="hidden" name="hidden_id" id="hidden_id" />
                    <input type="hidden" name="hidden_user_image" id="hidden_user_image" />
                    <input type="hidden" name="action" id="action" value="Add" />
                    <button type="submit" name="submit" id="submit_button" class="btn btn-info">Add</button>
                    <button type="button" class="btn btn-outline-light" data-dismiss="modal">Close</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include('footer.php'); ?>

<script>
$(document).ready(function(){
    var dataTable = $('#user_table').DataTable({
        "processing": true,
        "serverSide": true,
        "order": [],
        "ajax": { url:"user_action.php", type:"POST", data:{action:'fetch'} },
        "columnDefs":[{ "targets":[0,4,8], "orderable":false }]
    });

    $('#user_form').parsley();

    $('#toggleModalPassword').on('click', function() {
        const passInput = $('#user_password');
        const eyeIcon = $('#modalEyeIcon');
        const type = passInput.attr('type') === 'password' ? 'text' : 'password';
        passInput.attr('type', type);
        eyeIcon.toggleClass('fa-eye fa-eye-slash');
    });

    $(document).on('click', '.view_password', function(){
        var password = $(this).data('password');
        var targetId = $(this).data('id');
        var span = $('#pass_' + targetId);
        if(span.text() == '********'){
            span.text(password);
            $(this).html('<i class="fas fa-eye-slash text-secondary"></i>');
        } else {
            span.text('********');
            $(this).html('<i class="fas fa-eye text-info"></i>');
        }
    });

    $('#add_user').click(function(){
        $('#user_form')[0].reset();
        $('#user_form').parsley().reset();
        $('#modal_title').text('Add User');
        $('#action').val('Add');
        $('#submit_button').html('Add');
        $('#user_uploaded_image').html('');
        $('#password_label').show();
        $('#password_help').text('');
        $('#user_password').attr('required', true);
        $('#form_message').html('');
        $('#userModal').modal('show');
    });

    $(document).on('click', '.edit_button', function(){
        var user_id = $(this).data('id');
        $('#user_form').parsley().reset();
        $('#form_message').html('');
        $.ajax({
            url:"user_action.php",
            method:"POST",
            data:{user_id:user_id, action:'fetch_single'},
            dataType:'JSON',
            success:function(data){
                $('#user_name').val(data.user_name);
                $('#user_email').val(data.user_email);
                $('#user_contact_no').val(data.user_contact_no);
                $('#user_type').val(data.user_type);
                $('#user_uploaded_image').html('<img src="'+data.user_profile+'" class="img-thumbnail" width="75" />');
                $('#hidden_user_image').val(data.user_profile);
                $('#modal_title').text('Edit User');
                $('#action').val('Edit');
                $('#submit_button').html('Edit');
                $('#hidden_id').val(user_id);
                
                // For Edit: Password is not required
                $('#password_label').hide();
                $('#password_help').text('Leave blank to keep current password');
                $('#user_password').attr('required', false).val('');
                
                $('#userModal').modal('show');
            }
        });
    });

    $('#user_form').on('submit', function(event){
        event.preventDefault();
        if($('#user_form').parsley().isValid()){
            $.ajax({
                url:"user_action.php",
                method:"POST",
                data:new FormData(this),
                dataType:'json',
                contentType:false,
                processData:false,
                beforeSend:function(){
                    $('#submit_button').attr('disabled', 'disabled').html('<i class="fas fa-circle-notch fa-spin"></i> Wait...');
                },
                success:function(data){
                    $('#submit_button').attr('disabled', false).html($('#action').val());
                    if(data.error != ''){
                        $('#form_message').html('<div class="alert alert-danger">'+data.error+'</div>');
                    } else {
                        $('#userModal').modal('hide');
                        $('#message').html('<div class="alert alert-success">'+data.success+'</div>');
                        dataTable.ajax.reload();
                        setTimeout(()=>{$('#message').html('');}, 5000);
                    }
                }
            });
        }
    });

    $(document).on('click', '.status_button', function(){
        var id = $(this).data('id');
        var status = $(this).data('status');
        if(confirm("Are you sure you want to change status to "+status+"?")){
            $.ajax({
                url:"user_action.php",
                method:"POST",
                data:{id:id, action:'change_status', next_status:status},
                dataType: 'JSON',
                success:function(data){
                    $('#message').html('<div class="alert alert-info">'+data.success+'</div>');
                    dataTable.ajax.reload();
                }
            });
        }
    });

    $(document).on('click', '.delete_button', function(){
        var id = $(this).data('id');
        if(confirm("WARNING: This will permanently delete this user. Are you sure?")){
            $.ajax({
                url:"user_action.php",
                method:"POST",
                data:{id:id, action:'true_delete'},
                dataType: 'JSON',
                success:function(data){
                    if(data.error){
                        $('#message').html('<div class="alert alert-danger">'+data.error+'</div>');
                    } else {
                        $('#message').html('<div class="alert alert-danger">'+data.success+'</div>');
                        dataTable.ajax.reload();
                    }
                }
            });
        }
    });
});
</script>