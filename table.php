<?php
// table.php
include('rms.php');
$object = new rms();

if(!$object->is_login()) {
    header("location:".$object->base_url."");
}

if(!$object->is_master_user()) {
    header("location:".$object->base_url."dashboard.php");
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
        --neon-red: #ef4444;
        --dropdown-bg: #111827;
    }

    body { background-color: #0c0f17; color: #e2e8f0; }

    /* 1. LAYOUT & GLASS CARD */
    .glass-card {
        background: var(--glass-bg) !important;
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid var(--glass-border) !important;
        border-radius: 24px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
    }

    /* 2. TABLE - FIT CONTENT CONSTRAINT */
    .table { color: #e2e8f0 !important; margin-bottom: 0 !important; border-collapse: separate !important; border-spacing: 0; }
    
    .fit-content { width: 1% !important; white-space: nowrap !important; }

    .table thead th {
        background: transparent !important;
        color: var(--accent-cyan) !important;
        text-transform: uppercase;
        font-size: 0.7rem;
        letter-spacing: 2px;
        border-bottom: 2px solid rgba(14, 165, 233, 0.3) !important;
        padding: 1.2rem 1rem !important;
    }

    .table td {
        vertical-align: middle !important;
        padding: 1.1rem 1rem !important;
        border-top: 1px solid rgba(255, 255, 255, 0.05) !important;
    }

    /* 3. MODAL & COMBOBOX STYLE */
    .modal-content {
        background: rgba(15, 23, 42, 0.95) !important;
        backdrop-filter: blur(25px);
        border: 1px solid var(--accent-cyan) !important;
        border-radius: 25px;
        color: white;
        box-shadow: 0 0 40px rgba(14, 165, 233, 0.15);
    }

    .form-control {
        background: rgba(0, 0, 0, 0.3) !important;
        border: 1px solid var(--glass-border) !important;
        color: white !important;
        border-radius: 12px;
        padding: 12px;
    }
    
    .form-control:focus { border-color: var(--accent-cyan) !important; box-shadow: 0 0 10px rgba(14, 165, 233, 0.2); }

    /* Custom Dropdown Styling */
    select.form-control option {
        background-color: var(--dropdown-bg);
        color: #fff;
    }

    /* 4. COOL SWEETALERT2 OVERRIDES */
    .swal2-popup.cyber-popup {
        background: rgba(15, 23, 42, 0.98) !important;
        backdrop-filter: blur(20px) !important;
        border: 1px solid var(--accent-cyan) !important;
        border-radius: 24px !important;
        color: #fff !important;
    }
    .swal2-title { color: var(--accent-cyan) !important; text-transform: uppercase; letter-spacing: 1px; }
    
    .swal2-confirm.cyber-confirm { background: transparent !important; border: 1px solid var(--neon-green) !important; color: var(--neon-green) !important; border-radius: 12px !important; padding: 10px 25px !important; font-weight: bold; margin: 5px; }
    .swal2-confirm.cyber-confirm:hover { background: var(--neon-green) !important; color: #000 !important; box-shadow: 0 0 20px var(--neon-green) !important; }
    
    .swal2-cancel.cyber-cancel { background: transparent !important; border: 1px solid var(--neon-red) !important; color: var(--neon-red) !important; border-radius: 12px !important; padding: 10px 25px !important; font-weight: bold; margin: 5px; }

    /* Action Icons */
    .btn-action { width: 35px; height: 35px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; transition: 0.3s; border: none; }
</style>

<div class="container-fluid py-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-white font-weight-bold">Table Management</h1>
            <p class="text-white-50 small text-uppercase letter-spacing-1">Organize and assign floor assets</p>
        </div>
        <button type="button" id="add_table" class="btn btn-info shadow-sm px-4 py-2" style="border-radius: 15px; font-weight: bold; background: var(--accent-cyan); border: none;">
            <i class="fas fa-plus-circle mr-2"></i> NEW TABLE
        </button>
    </div>

    <span id="message"></span>

    <div class="card glass-card shadow-lg">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table" id="table_data" width="100%">
                    <thead>
                        <tr>
                            <th class="pl-4">Table Name</th>
                            <th class="fit-content">Capacity</th>
                            <th class="fit-content">Assigned Waiter</th>
                            <th class="fit-content text-center">Status</th>
                            <th class="fit-content text-right pr-4">Protocol</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="tableModal" class="modal fade" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <form method="post" id="table_form">
            <div class="modal-content">
                <div class="modal-header border-0 p-4">
                    <h5 class="modal-title font-weight-bold text-uppercase letter-spacing-2" id="modal_title" style="color: var(--accent-cyan);">Add New Table</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body px-4">
                    <span id="form_message"></span>
                    <div class="form-group mb-3">
                        <label class="small text-white-50 font-weight-bold">TABLE DESIGNATION</label>
                        <input type="text" name="table_name" id="table_name" class="form-control" required data-parsley-pattern="/^[a-zA-Z0-9 \s]+$/" data-parsley-trigger="keyup" placeholder="e.g. Table 01" />
                    </div>
                    <div class="form-group mb-3">
                        <label class="small text-white-50 font-weight-bold">MAX CAPACITY</label>
                        <select name="table_capacity" id="table_capacity" class="form-control" required>
                            <option value="">Select Capacity</option>
                            <?php for($i = 1; $i <= 20; $i++) { echo '<option value="'.$i.'">'.$i.' Person'.($i > 1 ? 's' : '').'</option>'; } ?>
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="small text-white-50 font-weight-bold">ASSIGN PERSONNEL</label>
                        <select name="waiter_id" id="waiter_id" class="form-control" required>
                            <option value="">Select Waiter</option>
                            <?php
                            $object->query = "SELECT * FROM user_table WHERE user_type = 'Waiter' AND user_status = 'Enable'";
                            $waiter_result = $object->get_result();
                            foreach($waiter_result as $waiter) {
                                echo '<option value="'.$waiter["user_id"].'">'.$waiter["user_name"].'</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4">
                    <input type="hidden" name="hidden_id" id="hidden_id" />
                    <input type="hidden" name="action" id="action" value="Add" />
                    <button type="submit" name="submit" id="submit_button" class="btn btn-info btn-block py-3 font-weight-bold" style="border-radius: 15px; background: var(--accent-cyan); border: none;">INITIALIZE TABLE</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include('footer.php'); ?>

<script>
$(document).ready(function(){
    var dataTable = $('#table_data').DataTable({
        "processing" : true,
        "serverSide" : true,
        "order" : [],
        "ajax" : { url:"table_action.php", type:"POST", data:{action:'fetch'} },
        "columnDefs":[ 
            { "targets":[1, 2, 3], "className": "fit-content" },
            { "targets":[4], "orderable":false, "className": "fit-content text-right pr-4" }
        ],
        "language": {
            "search": "",
            "searchPlaceholder": "Search assets...",
            "paginate": { "previous": "<", "next": ">" }
        }
    });

    $('#add_table').click(function(){
        $('#table_form')[0].reset();
        $('#table_form').parsley().reset();
        $('#modal_title').text('New Table Deployment');
        $('#action').val('Add');
        $('#submit_button').text('ADD TABLE');
        $('#tableModal').modal('show');
    });

    $('#table_form').on('submit', function(event){
        event.preventDefault();
        if($('#table_form').parsley().isValid()) {     
            $.ajax({
                url:"table_action.php",
                method:"POST",
                data:$(this).serialize(),
                dataType:'json',
                success:function(data) {
                    if(data.error != '') {
                        $('#form_message').html('<div class="alert alert-danger">'+data.error+'</div>');
                    } else {
                        $('#tableModal').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Deployment Successful',
                            text: data.success,
                            customClass: { popup: 'cyber-popup', confirmButton: 'cyber-confirm' },
                            buttonsStyling: false
                        });
                        dataTable.ajax.reload();
                    }
                }
            })
        }
    });

    $(document).on('click', '.edit_button', function(){
        var table_id = $(this).data('id');
        $.ajax({
            url:"table_action.php",
            method:"POST",
            data:{table_id:table_id, action:'fetch_single'},
            dataType:'JSON',
            success:function(data) {
                $('#table_name').val(data.table_name);
                $('#table_capacity').val(data.table_capacity);
                $('#waiter_id').val(data.waiter_id); 
                $('#modal_title').text('Modify Table Specs');
                $('#action').val('Edit');
                $('#submit_button').text('UPDATE TABLE');
                $('#hidden_id').val(table_id);
                $('#tableModal').modal('show');
            }
        })
    });

    $(document).on('click', '.status_button', function(){
        var id = $(this).data('id');
        var status = $(this).data('status');
        var next_status = (status == 'Enable') ? 'Disable' : 'Enable';
        
        Swal.fire({
            title: 'Protocol Override?',
            text: "Switch table status to " + next_status,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'PROCEED',
            cancelButtonText: 'ABORT',
            customClass: { popup: 'cyber-popup', confirmButton: 'cyber-confirm', cancelButton: 'cyber-cancel' },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url:"table_action.php",
                    method:"POST",
                    data:{id:id, action:'change_status', status:status, next_status:next_status},
                    success:function(data) {
                        dataTable.ajax.reload();
                    }
                })
            }
        });
    });

    $(document).on('click', '.delete_button', function(){
        var id = $(this).data('id');
        Swal.fire({
            title: 'Decommission Table?',
            text: "Warning: This will purge the table from the floor map.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'PURGE',
            cancelButtonText: 'ABORT',
            customClass: { popup: 'cyber-popup', confirmButton: 'cyber-cancel', cancelButton: 'cyber-confirm' },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url:"table_action.php",
                    method:"POST",
                    data:{id:id, action:'delete'},
                    success:function(data) {
                        Swal.fire({ icon: 'success', title: 'Asset Purged', customClass: { popup: 'cyber-popup' }});
                        dataTable.ajax.reload();
                    }
                })
            }
        });
    });
});
</script>