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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    :root { 
        --glass-bg: rgba(255, 255, 255, 0.03); 
        --glass-border: rgba(255, 255, 255, 0.1); 
        --accent-cyan: #0ea5e9; 
        --neon-green: #10b981;
        --neon-yellow: #f59e0b;
        --neon-red: #ef4444;
        --dropdown-bg: #111827; 
    }
    
    body { background-color: #0c0f17; color: #e2e8f0; }

    /* 1. GLASS CARD STYLING */
    .glass-card { 
        background: var(--glass-bg) !important; 
        backdrop-filter: blur(20px); 
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid var(--glass-border) !important; 
        border-radius: 20px; 
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
    }

    /* 2. TABLE STYLING - FIT CONTENT */
    .table { color: #cbd5e1 !important; margin-bottom: 0 !important; border-collapse: separate !important; border-spacing: 0; }
    .fit-content { width: 1% !important; white-space: nowrap !important; }

    .table thead th {
        background: transparent !important;
        color: var(--accent-cyan) !important;
        text-transform: uppercase;
        font-size: 0.7rem;
        letter-spacing: 2px;
        border-bottom: 2px solid rgba(14, 165, 233, 0.2) !important;
        padding: 1.2rem 1rem !important;
    }

    .table td {
        vertical-align: middle !important;
        padding: 1rem !important;
        border-top: 1px solid rgba(255, 255, 255, 0.05) !important;
        background: transparent !important;
    }

    tr:hover td { background: rgba(14, 165, 233, 0.05) !important; }

    .user-profile-img {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        object-fit: cover;
        border: 1px solid var(--glass-border);
    }

    /* 3. DATATABLE OVERRIDES */
    .dataTables_wrapper .dataTables_filter input {
        background: rgba(255, 255, 255, 0.05) !important;
        border: 1px solid var(--glass-border) !important;
        color: white !important;
        border-radius: 10px;
        padding: 8px 15px;
    }
    .dataTables_info { color: #64748b !important; font-size: 0.8rem; padding-top: 15px; }

    .page-link { background: var(--glass-bg) !important; border: 1px solid var(--glass-border) !important; color: #94a3b8 !important; border-radius: 8px !important; margin: 0 3px; }
    .page-item.active .page-link { background: var(--accent-cyan) !important; color: #000 !important; border-color: var(--accent-cyan) !important; font-weight: bold; }

    /* 4. MODAL & FORM STYLING */
    .modal-content {
        background: rgba(15, 23, 42, 0.95) !important;
        backdrop-filter: blur(25px);
        border: 1px solid var(--accent-cyan) !important;
        border-radius: 24px !important;
        box-shadow: 0 0 50px rgba(14, 165, 233, 0.2);
        color: #fff;
    }
    
    .form-control {
        background: rgba(0, 0, 0, 0.3) !important;
        border: 1px solid var(--glass-border) !important;
        border-radius: 12px;
        color: white !important;
        height: auto;
        padding: 10px 15px;
    }
    .form-control:focus { border-color: var(--accent-cyan) !important; box-shadow: 0 0 10px rgba(14, 165, 233, 0.2); }
    .form-control:disabled, .form-control[readonly] { background: rgba(255, 255, 255, 0.02) !important; color: var(--accent-cyan) !important; opacity: 0.8; }

    select.form-control {
        cursor: pointer;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%230ea5e9' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 15px center;
    }
    select.form-control option { background-color: var(--dropdown-bg); color: #fff; }

    /* 5. SWEETALERT2 OVERRIDES */
    .swal2-popup.cyber-popup {
        background: rgba(15, 23, 42, 0.95) !important;
        backdrop-filter: blur(20px) !important;
        border: 1px solid var(--accent-cyan) !important;
        border-radius: 24px !important;
        color: #fff !important;
    }
    .swal2-confirm.cyber-confirm { background: transparent !important; border: 1px solid var(--neon-green) !important; color: var(--neon-green) !important; border-radius: 12px !important; padding: 10px 25px !important; font-weight: 800; text-transform: uppercase; margin: 5px; }
    .swal2-cancel.cyber-cancel { background: transparent !important; border: 1px solid var(--neon-red) !important; color: var(--neon-red) !important; border-radius: 12px !important; padding: 10px 25px !important; font-weight: 800; text-transform: uppercase; margin: 5px; }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 font-weight-bold text-white mb-0">User Management</h1>
            <p class="text-white-50 small text-uppercase letter-spacing-1">System clearance levels & personnel logs</p>
        </div>
        <button type="button" id="add_user" class="btn btn-info shadow-sm" style="border-radius: 12px; font-weight: bold; background: var(--accent-cyan); border: none;">
            <i class="fas fa-user-shield mr-2"></i>ADD NEW USER
        </button>
    </div>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="glass-card p-3 d-flex align-items-center justify-content-start flex-wrap" style="gap: 15px;">
                <span class="small text-uppercase font-weight-bold" style="letter-spacing: 1px; color: var(--accent-cyan);">
                    <i class="fas fa-filter mr-2"></i>Filter View:
                </span>
                
                <select id="role_filter" class="form-control" style="width: 220px; background: rgba(255,255,255,0.05) !important;">
                    <option value="">All Identities</option>
                    <option value="Master">Admins</option>
                    <option value="Waiter">Waiters</option>
                    <option value="Cashier">Cashiers</option>
                    <option value="Kitchen">Kitchen Staff</option>
                    <option value="User">Customers</option>
                </select>
            </div>
        </div>
    </div>

    <div id="message"></div>

    <div class="card glass-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table" id="user_table" width="100%">
                    <thead>
                        <tr>
                            <th class="pl-4 fit-content">STAFF</th>
                            <th class="fit-content">USERNAME</th>
                            <th class="fit-content">CONTACT</th>
                            <th>EMAIL ADDRESS</th>
                            <th class="fit-content">CREDENTIALS</th>
                            <th class="fit-content">ROLE</th>
                            <th class="fit-content">JOINED</th>
                            <th class="fit-content">STATUS</th>
                            <th class="text-right pr-4 fit-content">ACTION</th>
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
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold text-uppercase letter-spacing-2" id="modal_title" style="color: var(--accent-cyan);">
                        <i class="fas fa-id-badge mr-2"></i>Access Provisioning
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body px-4">
                    <span id="form_message"></span>
                    <div class="row">
                        <div class="col-md-12 text-center mb-4">
                            <div id="user_uploaded_image"></div>
                            <input type="file" name="user_image" id="user_image" class="d-none" accept="image/*" />
                            <label for="user_image" class="btn btn-sm btn-outline-info mt-2" style="border-radius: 20px; font-size: 0.7rem;">
                                <i class="fas fa-sync-alt mr-1"></i> UPDATE AVATAR
                            </label>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="small text-white-50">Full Name</label>
                            <input type="text" name="user_name" id="user_name" class="form-control" required />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="small text-white-50">Contact</label>
                            <input type="text" name="user_contact_no" id="user_contact_no" class="form-control" required />
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="small text-white-50">Email Address <span class="text-info small">(Role-Locked)</span></label>
                            <input type="email" name="user_email" id="user_email" class="form-control" required />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="small text-white-50">Role</label>
                            <select name="user_type" id="user_type" class="form-control" required>
                                <option value="" disabled selected>Select Role</option>
                                <option value="Master">Admin</option>
                                <option value="Waiter">Waiter</option>
                                <option value="Cashier">Cashier</option>
                                <option value="Kitchen">Kitchen Staff</option>
                                <option value="User">Customer</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="small text-white-50">Password <span id="password_label" class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" name="user_password" id="user_password" class="form-control" />
                                <div class="input-group-append">
                                    <span class="input-group-text bg-dark border-0 text-white-50" id="toggleModalPassword" style="cursor:pointer;">
                                        <i class="fas fa-eye" id="modalEyeIcon"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="hidden_id" id="hidden_id" />
                    <input type="hidden" name="hidden_user_image" id="hidden_user_image" />
                    <input type="hidden" name="action" id="action" value="Add" />
                    <button type="submit" name="submit" id="submit_button" class="btn btn-info btn-block py-2 font-weight-bold" style="border-radius: 12px; background: var(--accent-cyan); border: none;">SAVE USER</button>
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
        "ajax": { 
            url:"user_action.php", 
            type:"POST", 
            data: function(d) {
                d.action = 'fetch';
                d.filter_role = $('#role_filter').val(); // Send filter to backend
            }
        },
        "columnDefs":[
            { "targets":[0, 1, 2, 4, 5, 6, 7], "className": "fit-content" },
            { "targets":[8], "orderable":false, "className": "text-right pr-4 fit-content" }
        ],
        "language": {
            "search": "",
            "searchPlaceholder": "Search logs...",
            "paginate": { "previous": "<", "next": ">" }
        }
    });

    // Refresh table on filter change
    $('#role_filter').on('change', function(){
        dataTable.ajax.reload();
    });

    // Auto-assign default emails based on role selection
    $('#user_type').on('change', function(){
        var role = $(this).val();
        var email = $('#user_email');
        if(role == 'Master') email.val('admin@wakanesa.com').attr('readonly', true);
        else if(role == 'Waiter') email.val('waiter@wakanesa.com').attr('readonly', true);
        else if(role == 'Cashier') email.val('cashier@wakanesa.com').attr('readonly', true);
        else if(role == 'Kitchen') email.val('kitchen@wakanesa.com').attr('readonly', true);
        else { email.val('').attr('readonly', false); }
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
        $('#user_email').attr('readonly', false);
        $('#modal_title').html('<i class="fas fa-user-plus mr-2"></i>Register User');
        $('#action').val('Add');
        $('#submit_button').html('SAVE USER');
        $('#user_uploaded_image').html('<div class="mx-auto rounded-circle d-flex align-items-center justify-content-center" style="width:80px; height:80px; background: rgba(255,255,255,0.05); border:1px dashed var(--glass-border)"><i class="fas fa-user-shield fa-2x text-white-50"></i></div>');
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
                $('#user_email').val(data.user_email).attr('readonly', true);
                $('#user_contact_no').val(data.user_contact_no);
                $('#user_type').val(data.user_type);
                $('#user_uploaded_image').html('<img src="'+data.user_profile+'" class="rounded user-profile-img" width="80" style="height:80px;"/>');
                $('#hidden_user_image').val(data.user_profile);
                $('#modal_title').html('<i class="fas fa-user-edit mr-2"></i>Modify User');
                $('#action').val('Edit');
                $('#submit_button').html('UPDATE RECORD');
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
                    $('#submit_button').attr('disabled', 'disabled').html('<i class="fas fa-spinner fa-spin"></i> PROCESSING...');
                },
                success:function(data){
                    $('#submit_button').attr('disabled', false).html('SAVE USER');
                    if(data.error != ''){
                        $('#form_message').html('<div class="alert alert-danger">'+data.error+'</div>');
                    } else {
                        $('#userModal').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'DATABASE UPDATED',
                            text: data.success,
                            customClass: { popup: 'cyber-popup', confirmButton: 'cyber-confirm' },
                            buttonsStyling: false
                        });
                        dataTable.ajax.reload();
                    }
                }
            });
        }
    });

    $(document).on('click', '.status_button', function(){
        var id = $(this).data('id');
        var status = $(this).data('status');
        Swal.fire({
            title: 'OVERRIDE STATUS?',
            text: "Personnel status will be updated to " + status,
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'PROCEED',
            cancelButtonText: 'ABORT',
            customClass: { popup: 'cyber-popup', confirmButton: 'cyber-confirm', cancelButton: 'cyber-cancel' },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
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
    });

    $(document).on('click', '.delete_button', function(){
        var id = $(this).data('id');
        Swal.fire({
            title: 'TERMINATE ACCESS?',
            text: "Warning: This purge is irreversible.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'PURGE',
            cancelButtonText: 'ABORT',
            customClass: { popup: 'cyber-popup', confirmButton: 'cyber-cancel', cancelButton: 'cyber-confirm' },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url:"user_action.php",
                    method:"POST",
                    data:{id:id, action:'true_delete'},
                    dataType: 'JSON',
                    success:function(data){
                        Swal.fire({ icon: 'success', title: 'IDENTITY PURGED', customClass: { popup: 'cyber-popup' }});
                        dataTable.ajax.reload();
                    }
                });
            }
        });
    });
});
</script>