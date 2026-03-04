<?php
// tax.php
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
        --neon-yellow: #f59e0b;
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

    /* 2. TABLE STYLING - FIT CONTENT CONSTRAINT */
    .table { color: #cbd5e1 !important; margin-bottom: 0 !important; border-collapse: separate !important; border-spacing: 0; }
    .fit-content { width: 1% !important; white-space: nowrap !important; }

    .table thead th {
        background: transparent !important;
        color: var(--accent-cyan) !important;
        text-transform: uppercase;
        font-size: 0.75rem;
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

    /* 3. MODAL & FORM CONTROL */
    .modal-content {
        background: rgba(15, 23, 42, 0.95) !important;
        backdrop-filter: blur(25px);
        border: 1px solid var(--accent-cyan) !important;
        border-radius: 24px !important;
        box-shadow: 0 0 50px rgba(14, 165, 233, 0.2);
        color: #fff;
    }

    .form-control {
        background: rgba(0, 0, 0, 0.2) !important;
        border: 1px solid var(--glass-border) !important;
        border-radius: 12px;
        color: white !important;
        padding: 12px;
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
    .swal2-html-container { color: #cbd5e1 !important; }
    
    .swal2-confirm.cyber-confirm { background: transparent !important; border: 1px solid var(--neon-green) !important; color: var(--neon-green) !important; border-radius: 12px !important; padding: 10px 25px !important; font-weight: bold; margin: 5px; }
    .swal2-confirm.cyber-confirm:hover { background: var(--neon-green) !important; color: #000 !important; box-shadow: 0 0 20px var(--neon-green) !important; }
    
    .swal2-cancel.cyber-cancel { background: transparent !important; border: 1px solid var(--neon-red) !important; color: var(--neon-red) !important; border-radius: 12px !important; padding: 10px 25px !important; font-weight: bold; margin: 5px; }
    .swal2-cancel.cyber-cancel:hover { background: var(--neon-red) !important; color: #fff !important; box-shadow: 0 0 20px var(--neon-red) !important; }

    /* Action Buttons */
    .btn-circle { width: 38px; height: 38px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; transition: 0.3s; border: none; }
    .btn-success-cyber { background: rgba(16, 185, 129, 0.1); color: var(--neon-green); border: 1px solid var(--neon-green); }
    .btn-success-cyber:hover { background: var(--neon-green); color: #000; box-shadow: 0 0 15px var(--neon-green); }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 font-weight-bold text-white mb-0">Tax Configuration</h1>
            <p class="text-white-50 small text-uppercase letter-spacing-1">Manage global taxation parameters</p>
        </div>
        <button type="button" id="add_tax" class="btn-circle btn-success-cyber shadow-sm">
            <i class="fas fa-plus"></i>
        </button>
    </div>

    <div id="message"></div>

    <div class="card glass-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table" id="tax_table" width="100%">
                    <thead>
                        <tr>
                            <th class="pl-4">Tax Name</th>
                            <th class="fit-content">Tax Percentage (%)</th>
                            <th class="fit-content text-center">Status</th>
                            <th class="fit-content text-right pr-4">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="taxModal" class="modal fade" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <form method="post" id="tax_form">
            <div class="modal-content">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title font-weight-bold text-uppercase letter-spacing-2" id="modal_title" style="color: var(--accent-cyan);">Add Tax</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body px-4">
                    <span id="form_message"></span>
                    <div class="form-group mb-4">
                        <label class="small text-white-50">Tax Designation</label>
                        <input type="text" name="tax_name" id="tax_name" class="form-control" required data-parsley-pattern="/^[a-zA-Z0-9 \s]+$/" data-parsley-trigger="keyup" placeholder="e.g. VAT" />
                    </div>
                    <div class="form-group mb-2">
                        <label class="small text-white-50">Percentage Value (%)</label>
                        <input type="text" name="tax_percentage" id="tax_percentage" class="form-control" required data-parsley-pattern="^[0-9]{1,2}\.[0-9]{2}$" data-parsley-trigger="keyup" placeholder="0.00" />
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <input type="hidden" name="hidden_id" id="hidden_id" />
                    <input type="hidden" name="action" id="action" value="Add" />
                    <button type="submit" name="submit" id="submit_button" class="btn btn-info px-4 py-2 font-weight-bold" style="border-radius: 10px; background: var(--accent-cyan); border: none;">SAVE SETTINGS</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include('footer.php'); ?>

<script>
$(document).ready(function(){

    var dataTable = $('#tax_table').DataTable({
        "processing" : true,
        "serverSide" : true,
        "order" : [],
        "ajax" : { url:"tax_action.php", type:"POST", data:{action:'fetch'} },
        "columnDefs":[
            { "targets":[1, 2], "className": "fit-content text-center" },
            { "targets":[3], "orderable":false, "className": "fit-content text-right pr-4" }
        ],
        "language": {
            "search": "",
            "searchPlaceholder": "Filter settings...",
            "paginate": { "previous": "<", "next": ">" }
        }
    });

    $('#add_tax').click(function(){
        $('#tax_form')[0].reset();
        $('#tax_form').parsley().reset();
        $('#modal_title').text('Initialize New Tax');
        $('#action').val('Add');
        $('#submit_button').text('ADD PARAMETER');
        $('#taxModal').modal('show');
    });

    $('#tax_form').on('submit', function(event){
        event.preventDefault();
        if($('#tax_form').parsley().isValid()) {     
            $.ajax({
                url:"tax_action.php",
                method:"POST",
                data:$(this).serialize(),
                dataType:'json',
                success:function(data) {
                    if(data.error != '') {
                        $('#form_message').html('<div class="alert alert-danger">'+data.error+'</div>');
                    } else {
                        $('#taxModal').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Protocol Updated',
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
        var tax_id = $(this).data('id');
        $.ajax({
            url:"tax_action.php",
            method:"POST",
            data:{tax_id:tax_id, action:'fetch_single'},
            dataType:'JSON',
            success:function(data) {
                $('#tax_name').val(data.tax_name);
                $('#tax_percentage').val(data.tax_percentage);
                $('#modal_title').text('Modify Tax Settings');
                $('#action').val('Edit');
                $('#submit_button').text('UPDATE SETTINGS');
                $('#hidden_id').val(tax_id);
                $('#taxModal').modal('show');
            }
        })
    });

    $(document).on('click', '.status_button', function(){
        var id = $(this).data('id');
        var status = $(this).data('status');
        var next_status = (status == 'Enable') ? 'Disable' : 'Enable';
        
        Swal.fire({
            title: 'PROTOCOL CHANGE',
            text: "Switch status to " + next_status + "?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'CONFIRM',
            cancelButtonText: 'ABORT',
            customClass: { popup: 'cyber-popup', confirmButton: 'cyber-confirm', cancelButton: 'cyber-cancel' },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url:"tax_action.php",
                    method:"POST",
                    data:{id:id, action:'change_status', status:status, next_status:next_status},
                    success:function(data) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Status Synchronized',
                            customClass: { popup: 'cyber-popup', confirmButton: 'cyber-confirm' },
                            buttonsStyling: false
                        });
                        dataTable.ajax.reload();
                    }
                })
            }
        });
    });

    $(document).on('click', '.delete_button', function(){
        var id = $(this).data('id');
        
        Swal.fire({
            title: 'TERMINATE RECORD?',
            text: "This data will be permanently purged from the system.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'PURGE',
            cancelButtonText: 'ABORT',
            customClass: { popup: 'cyber-popup', confirmButton: 'cyber-cancel', cancelButton: 'cyber-confirm' },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url:"tax_action.php",
                    method:"POST",
                    data:{id:id, action:'delete'},
                    success:function(data) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Record Terminated',
                            customClass: { popup: 'cyber-popup', confirmButton: 'cyber-confirm' },
                            buttonsStyling: false
                        });
                        dataTable.ajax.reload();
                    }
                })
            }
        });
    });
});
</script>