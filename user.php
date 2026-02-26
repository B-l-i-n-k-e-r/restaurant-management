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
    :root {
        --glass-bg: rgba(255, 255, 255, 0.05);
        --glass-border: rgba(255, 255, 255, 0.12);
        --accent-color: #00d2ff;
    }

    /* Glassmorphism Card Effect */
    .glass-card {
        background: var(--glass-bg) !important;
        backdrop-filter: blur(15px);
        -webkit-backdrop-filter: blur(15px);
        border: 1px solid var(--glass-border) !important;
        border-radius: 20px;
        overflow: hidden;
    }

    /* Table Styling */
    .table { color: #e0e0e0 !important; border-collapse: separate; border-spacing: 0 8px; }
    .table thead th { 
        background: rgba(255, 255, 255, 0.03);
        border: none !important; 
        text-transform: uppercase; 
        font-size: 0.75rem; 
        letter-spacing: 1px;
        color: var(--accent-color);
        padding: 15px;
    }
    .table tbody tr { 
        background: rgba(255, 255, 255, 0.02);
        transition: all 0.3s ease;
    }
    .table tbody tr:hover { 
        background: rgba(255, 255, 255, 0.07);
        transform: translateY(-2px);
    }
    .table td { vertical-align: middle !important; border: none !important; padding: 15px; }
    
    /* Profile Circle */
    .user-profile-img {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid var(--accent-color);
        box-shadow: 0 0 10px rgba(0, 210, 255, 0.2);
    }

    /* Form & Modal Styling */
    .modal-content {
        background: linear-gradient(145deg, rgba(30, 30, 30, 0.95), rgba(15, 15, 15, 0.95)) !important;
        backdrop-filter: blur(25px);
        border: 1px solid var(--glass-border);
        border-radius: 25px;
        color: white;
    }
    .form-control {
        background: rgba(0, 0, 0, 0.2) !important;
        border: 1px solid var(--glass-border) !important;
        border-radius: 10px;
        color: white !important;
        padding: 12px;
    }
    .form-control:focus {
        border-color: var(--accent-color) !important;
        box-shadow: 0 0 8px rgba(0, 210, 255, 0.3);
    }

    /* DataTables Pagination & Search */
    .dataTables_wrapper .dataTables_filter input {
        background: var(--glass-bg);
        border: 1px solid var(--glass-border);
        color: white;
        border-radius: 20px;
        padding: 5px 15px;
        outline: none;
    }
    .dataTables_info, .dataTables_length, .dataTables_filter { color: rgba(255,255,255,0.6) !important; margin-bottom: 15px; }
    .page-link {
        background: var(--glass-bg) !important;
        border: 1px solid var(--glass-border) !important;
        color: white !important;
        margin: 0 3px;
        border-radius: 5px;
    }
    .page-item.active .page-link { background: var(--accent-color) !important; border-color: var(--accent-color) !important; }

    /* Action Buttons */
    .btn-action { width: 32px; height: 32px; padding: 0; line-height: 32px; border-radius: 8px; margin: 0 2px; }
    
    /* Animation for Requests */
    .row-request-active { border-left: 4px solid #ffc107 !important; background: rgba(255, 193, 7, 0.05) !important; }
</style>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h4 mb-0 text-white font-weight-bold">Staff & User Control</h1>
        <button type="button" id="add_user" class="btn btn-info shadow-sm" style="border-radius: 10px; padding: 8px 20px;">
            <i class="fas fa-user-plus fa-sm mr-2"></i> Add New User
        </button>
    </div>

    <span id="message"></span>

    <div class="card glass-card shadow-lg">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table" id="user_table" width="100%">
                    <thead>
                        <tr>
                            <th>Staff</th>
                            <th>Username</th>
                            <th>Contact</th>
                            <th>Email</th>
                            <th>Credentials</th>
                            <th>Role</th>
                            <th>Joined</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="userModal" class="modal fade" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <form method="post" id="user_form" enctype="multipart/form-data">
            <div class="modal-content shadow-2xl">
                <div class="modal-header border-0 px-4 pt-4">
                    <h5 class="modal-title font-weight-bold" id="modal_title text-info">Add Staff Member</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body px-4">
                    <span id="form_message"></span>
                    <div class="row">
                        <div class="col-md-12 text-center mb-3">
                            <div id="user_uploaded_image"></div>
                            <input type="file" name="user_image" id="user_image" class="d-none" accept="image/*" />
                            <label for="user_image" class="btn btn-sm btn-outline-info mt-2" style="cursor: pointer;">
                                <i class="fas fa-camera mr-1"></i> Upload Photo
                            </label>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="small text-white-50">Full Name</label>
                            <input type="text" name="user_name" id="user_name" class="form-control" required />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="small text-white-50">Contact No.</label>
                            <input type="text" name="user_contact_no" id="user_contact_no" class="form-control" required />
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="small text-white-50">Email Address</label>
                            <input type="email" name="user_email" id="user_email" class="form-control" required />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="small text-white-50">Role</label>
                            <select name="user_type" id="user_type" class="form-control" required>
                                <option value="">Select Type</option>
                                <option value="Master">Master (Admin)</option>
                                <option value="Waiter">Waiter</option>
                                <option value="Cashier">Cashier</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="small text-white-50">Password <span id="password_label" class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" name="user_password" id="user_password" class="form-control" />
                                <div class="input-group-append">
                                    <span class="input-group-text bg-dark border-0 text-white" id="toggleModalPassword" style="cursor:pointer;">
                                        <i class="fas fa-eye" id="modalEyeIcon"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <input type="hidden" name="hidden_id" id="hidden_id" />
                    <input type="hidden" name="hidden_user_image" id="hidden_user_image" />
                    <input type="hidden" name="action" id="action" value="Add" />
                    <button type="submit" name="submit" id="submit_button" class="btn btn-info btn-block py-2 font-weight-bold" style="border-radius: 12px;">Save Staff Details</button>
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
        "columnDefs":[{ "targets":[0,4,8], "orderable":false }],
        "language": {
            "search": "_INPUT_",
            "searchPlaceholder": "Search staff..."
        },
        "createdRow": function(row, data, dataIndex) {
            if (data[7].indexOf('Requested') > -1) {
                $(row).addClass('row-request-active');
            }
        }
    });

    $('#user_form').parsley();

    // Toggle Password in Modal
    $('#toggleModalPassword').on('click', function() {
        const passInput = $('#user_password');
        const eyeIcon = $('#modalEyeIcon');
        const type = passInput.attr('type') === 'password' ? 'text' : 'password';
        passInput.attr('type', type);
        eyeIcon.toggleClass('fa-eye fa-eye-slash');
    });

    // Toggle Password in Table Row
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
        $('#modal_title').text('Add Staff Member');
        $('#action').val('Add');
        $('#submit_button').html('Save Staff Details');
        $('#user_uploaded_image').html('<div class="mx-auto bg-dark rounded-circle" style="width:100px; height:100px; line-height:100px; border:2px dashed #444"><i class="fas fa-user fa-3x text-secondary mt-4"></i></div>');
        $('#password_label').show();
        $('#user_password').attr('required', true);
        $('#userModal').modal('show');
    });

    $(document).on('click', '.edit_button', function(){
        var user_id = $(this).data('id');
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
                $('#user_uploaded_image').html('<img src="'+data.user_profile+'" class="rounded-circle img-thumbnail" width="100" style="height:100px; object-fit:cover;"/>');
                $('#hidden_user_image').val(data.user_profile);
                $('#modal_title').text('Edit Staff Details');
                $('#action').val('Edit');
                $('#submit_button').html('Update Details');
                $('#hidden_id').val(user_id);
                $('#password_label').hide();
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
                    $('#submit_button').attr('disabled', 'disabled').html('<i class="fas fa-spinner fa-spin"></i> Processing...');
                },
                success:function(data){
                    $('#submit_button').attr('disabled', false).html('Save Staff Details');
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
        if(confirm("Change staff status to "+status+"?")){
            $.ajax({
                url:"user_action.php",
                method:"POST",
                data:{id:id, action:'change_status', next_status:status},
                dataType: 'JSON',
                success:function(data){
                    dataTable.ajax.reload();
                }
            });
        }
    });

    $(document).on('click', '.delete_button', function(){
        var id = $(this).data('id');
        if(confirm("Permanently remove this user? This cannot be undone.")){
            $.ajax({
                url:"user_action.php",
                method:"POST",
                data:{id:id, action:'true_delete'},
                dataType: 'JSON',
                success:function(data){
                    dataTable.ajax.reload();
                }
            });
        }
    });
});
</script>