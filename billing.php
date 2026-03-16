<?php
// billing.php
include('rms.php');
$object = new rms();

if(!$object->is_login()) {
    header("location:".$object->base_url."");
    exit;
}

if(!$object->is_cashier_user() && !$object->is_master_user()) {
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
        --deep-navy: #0f172a;
    }
    
    body { background-color: var(--deep-navy); color: #e2e8f0; font-family: 'Poppins', sans-serif; }

    /* 1. GLASS CARD STYLING */
    .glass-card { 
        background: rgba(15, 23, 42, 0.7) !important; 
        backdrop-filter: blur(20px); 
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid var(--glass-border) !important; 
        border-radius: 20px; 
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
    }

    /* 2. TABLE STYLING - FIT CONTENT CONSTRAINT */
    .table { color: #cbd5e1 !important; margin-bottom: 0 !important; border-collapse: separate !important; }
    
    /* Constraint: Force columns to fit content tightly */
    .fit-content { width: 1% !important; white-space: nowrap !important; }

    .table thead th {
        background: rgba(255, 255, 255, 0.02) !important;
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
    }

    tr:hover td { background: rgba(14, 165, 233, 0.05) !important; }

    /* 3. DATATABLE CUSTOMIZATION */
    .dataTables_wrapper .dataTables_filter input {
        background: rgba(255, 255, 255, 0.05) !important;
        border: 1px solid var(--glass-border) !important;
        color: white !important;
        border-radius: 12px;
        padding: 8px 15px;
    }

    .page-link { background: var(--glass-bg) !important; border: 1px solid var(--glass-border) !important; color: var(--accent-cyan) !important; border-radius: 8px !important; margin: 0 3px; }
    .page-item.active .page-link { background: var(--accent-cyan) !important; color: #000 !important; border-color: var(--accent-cyan) !important; font-weight: bold; }

    /* 4. MODAL GLASS UPGRADE */
    .modal-content {
        background: rgba(15, 23, 42, 0.95) !important;
        backdrop-filter: blur(25px);
        border: 1px solid var(--accent-cyan) !important;
        border-radius: 24px !important;
        box-shadow: 0 0 50px rgba(14, 165, 233, 0.2);
    }

    /* 5. STATUS BADGES */
    .badge-paid { background: rgba(16, 185, 129, 0.1); color: var(--neon-green); border: 1px solid var(--neon-green); padding: 6px 12px; border-radius: 8px; font-weight: 800; text-transform: uppercase; font-size: 0.65rem; }
    .badge-pending { background: rgba(245, 158, 11, 0.1); color: var(--neon-yellow); border: 1px solid var(--neon-yellow); padding: 6px 12px; border-radius: 8px; font-weight: 800; text-transform: uppercase; font-size: 0.65rem; }

    /* 6. SWEETALERT2 OVERRIDES */
    .swal2-popup.cyber-popup {
        background: #0f172a !important;
        border: 1px solid var(--accent-cyan) !important;
        border-radius: 24px !important;
        color: #fff !important;
    }
    .swal2-confirm.cyber-confirm { background: var(--accent-cyan) !important; color: #000 !important; border-radius: 12px !important; padding: 10px 25px !important; font-weight: 800; }
    .swal2-cancel.cyber-cancel { background: rgba(255,255,255,0.1) !important; color: #fff !important; border-radius: 12px !important; }
</style>

<div class="container-fluid py-4 px-md-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div style="border-left: 4px solid var(--accent-cyan); padding-left: 20px;">
            <h1 class="h3 font-weight-bold text-white mb-0">BILLING <span style="color: var(--accent-cyan);">TERMINAL</span></h1>
            <p class="text-white-50 small text-uppercase mb-0" style="letter-spacing: 2px;">Protocol: Revenue Clearance</p>
        </div>
        <div class="text-right">
            <button type="button" id="print_all_bills" class="btn btn-outline-warning shadow-sm mr-2" style="border-radius: 12px; font-weight: bold; border-width: 2px;">
                <i class="fas fa-file-invoice-dollar mr-2"></i>DAILY REPORT
            </button>
            <span class="badge px-3 py-2" style="border-radius: 10px; background: rgba(14, 165, 233, 0.1); color: var(--accent-cyan); border: 1px solid var(--accent-cyan);">
                <i class="fas fa-user-shield mr-2"></i><?php echo $object->is_master_user() ? 'ADMIN_ACCESS' : 'CASHIER_UNIT'; ?>
            </span>
        </div>
    </div>

    <div id="message"></div>

    <div class="card glass-card">
        <div class="card-body p-0">
            <div class="table-responsive p-3">
                <table class="table" id="billing_table" width="100%">
                    <thead>
                        <tr>
                            <th class="fit-content">UNIT</th>
                            <th class="fit-content">ORDER ID</th>
                            <th class="fit-content">DATE</th>
                            <th class="fit-content">TIME</th>
                            <th>WAITER</th>
                            <?php if($object->is_master_user()) echo '<th>CASHIER</th>'; ?>
                            <th class="fit-content">STATUS</th>
                            <th class="text-right fit-content">PROTOCOL</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="billingModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <form method="post" id="billing_form">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold text-uppercase" id="modal_title" style="color: var(--accent-cyan); letter-spacing: 2px;">
                        <i class="fas fa-receipt mr-2"></i>Transaction Clearance
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body px-4">
                    <div id="billing_detail"></div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="hidden_order_id" id="hidden_order_id" />
                    <input type="hidden" name="action" id="action" value="Edit" />
                    
                    <button type="button" class="btn btn-link text-white-50 mr-auto" data-dismiss="modal">CLOSE</button>
                    
                    <button type="button" id="modal_print_button" class="btn btn-outline-warning px-4" style="border-radius: 12px; font-weight: bold; border-width: 2px;">
                        <i class="fas fa-print mr-2"></i> PRINT RECEIPT
                    </button>

                    <button type="submit" name="submit" id="settle_button" class="btn btn-info px-4 shadow-sm" style="border-radius: 12px; font-weight: bold; background: var(--accent-cyan); border: none; color: #000;">
                        <i class="fas fa-check-circle mr-2"></i> COMPLETE PAYMENT
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include('footer.php'); ?>

<script>
$(document).ready(function(){

    var dataTable = $('#billing_table').DataTable({
        "processing" : true,
        "serverSide" : true,
        "order" : [[1, "desc"]],
        "ajax" : {
            url:"billing_action.php",
            type:"POST",
            data:{action:'fetch'}
        },
        "columnDefs":[
            {
                "targets":[<?php echo ($object->is_master_user()) ? '7' : '6'; ?>],
                "orderable":false,
                "className": "text-right fit-content"
            },
            { "targets": [0, 1, 2, 3, 5], "className": "fit-content" }
        ],
        "language": {
            "search": "",
            "searchPlaceholder": "Search Ledger...",
            "lengthMenu": "_MENU_",
            "paginate": { 
                "previous": "<i class='fas fa-chevron-left'></i>", 
                "next": "<i class='fas fa-chevron-right'></i>" 
            }
        },
        "drawCallback": function() {
            $('.dataTables_paginate > .pagination').addClass('pagination-sm');
        }
    });

    $('#print_all_bills').click(function(){
        window.open("print.php?action=print_all", "_blank");
    });

    function fetch_order_data(order_id) {
        $.ajax({
            url:"billing_action.php",
            method:"POST",
            data:{order_id:order_id, action:'fetch_single'},
            success:function(data) {
                $('#billing_detail').html(data);
            }
        });
    }

    $(document).on('click', '.view_button', function(){
        var order_id = $(this).data('id');
        var status = $(this).closest('tr').find('.badge').text().trim();
        
        $('#hidden_order_id').val(order_id);
        $('#billingModal').modal('show');
        
        $('#settle_button').hide();
        $('#modal_print_button').show(); 

        if(status !== 'Settled') {
            $('#settle_button').show();
        }

        fetch_order_data(order_id);
    });

    $(document).on('click', '#modal_print_button', function(){
        var order_id = $('#hidden_order_id').val();
        if(order_id != "") {
            window.open("print.php?action=print&order_id=" + order_id, "_blank");
        }
    });

    $('#billing_form').on('submit', function(event){
        event.preventDefault();
        $.ajax({
            url:"billing_action.php",
            method:"POST",
            data:$(this).serialize(),
            beforeSend:function() {
                $('#settle_button').attr('disabled', 'disabled').html('<i class="fas fa-spinner fa-spin"></i> SYNCING...');
            },
            success:function(data) {
                $('#settle_button').attr('disabled', false).html('<i class="fas fa-check-circle mr-2"></i> COMPLETE PAYMENT');
                if($.trim(data) != "") {
                    Swal.fire({
                        icon: 'success',
                        title: 'TRANSACTION FINALIZED',
                        text: 'Order settled and revenue logged.',
                        customClass: { popup: 'cyber-popup', confirmButton: 'cyber-confirm' },
                        buttonsStyling: false
                    });
                    $('#billingModal').modal('hide');
                    dataTable.ajax.reload();
                }
            }
        });
    });

    $(document).on('click', '.delete_button', function(){
        var order_id = $(this).data('id');
        Swal.fire({
            title: 'VOID TRANSACTION?',
            text: "This protocol will purge the billing record.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'PURGE RECORD',
            cancelButtonText: 'ABORT',
            customClass: {
                popup: 'cyber-popup',
                confirmButton: 'cyber-cancel',
                cancelButton: 'cyber-confirm'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url:"billing_action.php",
                    method:"POST",
                    data:{order_id:order_id, action:"remove_bill"},
                    success:function(data) {
                        Swal.fire({ icon: 'success', title: 'PROTOCOL COMPLETED: VOIDED', customClass: { popup: 'cyber-popup' }});
                        dataTable.ajax.reload();
                    }
                })
            }
        });
    });
});
</script>