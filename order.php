<?php
// order.php
include('rms.php');
$object = new rms();

if(!$object->is_login()) { header("location:".$object->base_url.""); exit; }

$is_waiter = $object->is_waiter_user();
include('header.php');
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    :root { 
        --sky-blue: #0ea5e9; 
        --sky-glow: rgba(14, 165, 233, 0.4);
        --deep-navy: #0f172a;
        --glass: rgba(255, 255, 255, 0.03); 
        --glass-border: rgba(255, 255, 255, 0.1); 
        --accent-green: #22c55e;
        --accent-red: #ef4444;
        --accent-yellow: #f59e0b;
    }

    body { background-color: var(--deep-navy); color: white; font-family: 'Poppins', sans-serif; }

    /* Global Glass Cards */
    .glass-card { 
        background: rgba(15, 23, 42, 0.7) !important; 
        backdrop-filter: blur(15px); 
        -webkit-backdrop-filter: blur(15px);
        border: 1px solid var(--glass-border) !important; 
        border-radius: 20px; 
        box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3);
    }

    /* Table Column Fit Content Constraint */
    .table td, .table th { 
        white-space: nowrap !important; 
        width: 1% !important; 
        vertical-align: middle;
        border-top: 1px solid var(--glass-border) !important;
    }
    .table td.expand, .table th.expand { width: auto !important; white-space: normal !important; }

    .table thead th {
        border: none !important;
        font-size: 0.75rem;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        padding: 18px 15px !important;
    }

    /* Floor Plan Grid */
    .table-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 15px; }
    
    .table-item { 
        background: rgba(255, 255, 255, 0.03); 
        border: 1px solid var(--glass-border); 
        border-radius: 18px; 
        padding: 24px; 
        text-align: center; 
        cursor: pointer; 
        transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); 
    }
    
    .table-item:hover { 
        transform: translateY(-8px); 
        border-color: var(--sky-blue); 
        box-shadow: 0 10px 30px rgba(0,0,0,0.5), 0 0 15px var(--sky-glow); 
    }
    
    .table-item.occupied { border-left: 5px solid var(--accent-yellow) !important; }
    .table-item.available { border-left: 5px solid var(--accent-green) !important; }

    /* Navigation Pills */
    .nav-pills .nav-link { 
        color: #94a3b8; 
        border-radius: 12px; 
        border: 1px solid var(--glass-border); 
        transition: 0.3s;
        font-weight: 600;
        padding: 10px 22px;
        margin-right: 10px;
        background: rgba(255,255,255,0.05);
    }
    .nav-pills .nav-link.active { 
        background: var(--sky-blue) !important; 
        color: white !important; 
        border-color: var(--sky-blue);
        box-shadow: 0 0 20px var(--sky-glow);
    }

    /* DataTable Filter styling */
    .dataTables_filter input {
        background: rgba(255, 255, 255, 0.05) !important;
        border: 1px solid var(--glass-border) !important;
        color: white !important;
        border-radius: 10px;
        padding: 8px 15px;
        outline: none;
    }

    /* Cyber-Glass SweetAlert2 Overrides */
    .swal2-popup.cyber-popup {
        background: #0f172a !important;
        border: 1px solid var(--sky-blue) !important;
        border-radius: 24px !important;
        color: #fff !important;
        box-shadow: 0 0 30px var(--sky-glow) !important;
    }

    .swal2-title { color: var(--sky-blue) !important; text-transform: uppercase !important; letter-spacing: 2px !important; }

    .swal2-confirm.cyber-confirm {
        background: var(--accent-green) !important;
        color: white !important;
        border-radius: 12px !important;
        padding: 12px 30px !important;
        font-weight: 800 !important;
        box-shadow: 0 0 15px rgba(34, 197, 94, 0.4) !important;
    }

    .swal2-cancel.cyber-cancel {
        background: var(--accent-red) !important;
        color: white !important;
        border-radius: 12px !important;
        padding: 12px 30px !important;
        font-weight: 800 !important;
        box-shadow: 0 0 15px rgba(239, 68, 68, 0.4) !important;
    }
</style>

<div class="container-fluid py-4">
    <?php if($is_waiter) { ?>
        <div class="d-flex justify-content-between align-items-end mb-5">
            <div>
                <h6 class="text-sky-blue font-weight-bold mb-1" style="color: var(--sky-blue); letter-spacing: 2px; text-transform: uppercase;">
                    Floor Management
                </h6>
                <h1 class="h2 mb-0 font-weight-bold text-white">
                    <i class="fas fa-th mr-2"></i>Table Selection
                </h1>
            </div>
            <a href="dashboard.php" class="btn glass-card px-4 py-3 text-white" style="border-color: var(--sky-blue) !important;">
                <i class="fas fa-plus-circle mr-2 text-sky-blue"></i>NEW ORDER
            </a>
        </div>

        <?php if(isset($_GET['mode']) && $_GET['mode'] == 'select_table') { ?>
        <div class="glass-card mb-4 border-0 p-3" style="border-left: 5px solid var(--accent-green) !important; background: rgba(34, 197, 94, 0.1) !important;">
            <h6 class="text-white font-weight-bold mb-1"><i class="fas fa-crosshairs mr-2 text-success"></i> DEPLOYMENT MODE ACTIVE</h6>
            <p class="mb-0 small text-white-50">Please select an available (green) table to assign the current order.</p>
        </div>
        <?php } ?>

        <div class="row">
            <div class="col-lg-7 mb-4">
                <div class="glass-card p-4" id="table_status_area"></div>
            </div>
            <div class="col-lg-5">
                <div class="glass-card p-4 sticky-top" style="top: 20px; min-height: 300px;" id="order_detail_panel">
                    <div class="text-center py-5 opacity-25">
                        <i class="fas fa-terminal fa-4x mb-4 text-sky-blue"></i>
                        <h5>AWAITING TELEMETRY</h5>
                        <p class="small">Select a table to inspect active orders</p>
                    </div>
                </div>
            </div>
        </div>

    <?php } else { ?>
        <div class="mb-5 d-flex justify-content-between align-items-end">
            <div>
                <h6 class="text-sky-blue font-weight-bold mb-1" style="color: var(--sky-blue); letter-spacing: 2px; text-transform: uppercase;">
                    Archive & Intelligence
                </h6>
                <h1 class="h2 mb-0 font-weight-bold text-white">
                    <i class="fas fa-database mr-2"></i>Order Oversight
                </h1>
            </div>
            <button type="button" id="master_print_report" class="btn glass-card px-4 py-3 text-white" style="border-color: var(--accent-green) !important;">
                <i class="fas fa-file-invoice mr-2 text-success"></i>DAILY REPORT
            </button>
        </div>

        <ul class="nav nav-pills mb-4">
            <li class="nav-item">
                <a class="nav-link active" data-toggle="pill" href="#pending">
                    <i class="fas fa-bolt mr-2"></i>ACTIVE ORDERS
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="pill" href="#history">
                    <i class="fas fa-history mr-2"></i>SALES HISTORY
                </a>
            </li>
        </ul>

        <div class="row">
            <div class="col-lg-8">
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="pending">
                        <div class="glass-card overflow-hidden">
                            <table class="table mb-0">
                                <thead>
                                    <tr>
                                        <th class="pl-4">ID #</th>
                                        <th>UNIT</th>
                                        <th class="expand">OPERATOR</th>
                                        <th>REVENUE</th>
                                        <th class="text-right pr-4">PROTOCOL</th>
                                    </tr>
                                </thead>
                                <tbody id="admin_pending_list"></tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="tab-pane fade" id="history">
                        <div class="glass-card p-4">
                            <div class="table-responsive">
                                <table class="table w-100" id="history_table">
                                    <thead>
                                        <tr>
                                            <th>ID #</th>
                                            <th>TIMESTAMP</th>
                                            <th class="text-center">UNIT</th>
                                            <th class="expand">CLEARANCE</th>
                                            <th>TOTAL</th>
                                            <th class="text-right pr-4">PROTOCOL</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="glass-card p-4 sticky-top" style="top: 20px; min-height: 400px;" id="admin_detail_preview">
                    <div class="text-center py-5 opacity-25">
                        <i class="fas fa-fingerprint fa-4x mb-4 text-sky-blue"></i>
                        <h5>INSPECTION READY</h5>
                        <p class="small">Select a log entry to view full details</p>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>
</div>

<?php include('footer.php'); ?>

<script>
$(document).ready(function(){
    const isWaiter = <?php echo $is_waiter ? 'true' : 'false'; ?>;
    const selectMode = <?php echo (isset($_GET['mode']) && $_GET['mode'] == 'select_table') ? 'true' : 'false'; ?>;

    function load_preview(order_id, target_selector) {
        $.ajax({
            url: "order_action.php",
            method: "POST",
            data: {action: 'fetch_order_preview', order_id: order_id},
            success: function(data){ 
                $(target_selector).html(data);
                if(isWaiter) {
                    $('.settle_order_btn')
                        .html('<i class="fas fa-times-circle mr-2"></i>CANCEL ORDER')
                        .removeClass('btn-success')
                        .addClass('btn-danger')
                        .attr('id', 'btn_cancel_order')
                        .attr('data-id', order_id)
                        .css('border-radius', '12px');
                } else {
                    $('.settle_order_btn').remove(); 
                }
            }
        });
    }

    $(document).on('click', '#btn_cancel_order', function(){
        let order_id = $(this).data('id');
        Swal.fire({
            title: 'Purge Order?',
            text: "This action will clear the table telemetry.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'PURGE',
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
                    url: "order_action.php",
                    method: "POST",
                    data: {action: 'cancel_order', order_id: order_id},
                    success: function(data){
                        if(data.trim() == 'success') {
                            load_tables();
                            $('#order_detail_panel').html('<div class="text-center py-5 opacity-25"><i class="fas fa-terminal fa-4x mb-4 text-sky-blue"></i><h5>AWAITING TELEMETRY</h5></div>');
                        }
                    }
                });
            }
        });
    });

    $('#master_print_report').click(function(){ window.open("print.php?action=print_all", "_blank"); });

    if(isWaiter) {
        function load_tables() {
            $.ajax({
                url: "order_action.php",
                method: "POST",
                data: {action: 'fetch_modern_tables'},
                success: function(data){ $('#table_status_area').html(data); }
            });
        }
        load_tables();
        setInterval(function(){ if(!selectMode) load_tables(); }, 10000);

        $(document).on('click', '.table-item', function(){
            let table = $(this).data('table_name');
            let order_id = $(this).data('order_id');
            
            if(selectMode) {
                if(order_id != 0) {
                    Swal.fire({ 
                        icon: 'error', 
                        title: 'Unit Occupied', 
                        customClass: { popup: 'cyber-popup' },
                        buttonsStyling: false
                    });
                    return;
                }
                
                Swal.fire({
                    title: 'Deploy to ' + table + '?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'DEPLOY',
                    cancelButtonText: 'CANCEL',
                    customClass: {
                        popup: 'cyber-popup',
                        confirmButton: 'cyber-confirm',
                        cancelButton: 'cyber-cancel'
                    },
                    buttonsStyling: false 
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "order_action.php",
                            method: "POST",
                            data: {action: 'submit_cart_to_table', table_name: table},
                            success: function(res){ 
                                if(res.trim() == 'success') {
                                    window.location.href = "dashboard.php";
                                } 
                            }
                        });
                    }
                });
            } else {
                if(order_id != 0) load_preview(order_id, '#order_detail_panel');
            }
        });
    } else {
        function load_admin_pending() {
            $.ajax({
                url: "order_action.php",
                method: "POST",
                data: {action: 'fetch_admin_pending'},
                success: function(data){ $('#admin_pending_list').html(data); }
            });
        }
        load_admin_pending();
        setInterval(load_admin_pending, 15000);

        $(document).on('click', '.order-row, .view_history_btn', function(e){
            e.preventDefault();
            load_preview($(this).data('id'), '#admin_detail_preview');
        });

        $('#history_table').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": { "url": "order_action.php", "type": "POST", "data": { "action": "fetch_history" } },
            "columns": [
                { "data": "order_number", "className": "font-weight-bold" },
                { "data": "order_date" },
                { "data": "order_table", "className": "text-center" },
                { "data": "order_cashier", "className": "expand" },
                { "data": "order_total", "className": "text-sky-blue font-weight-bold" },
                { "data": "action", "className": "text-right" }
            ],
            "order": [[ 0, "desc" ]],
            "language": { "search": "", "searchPlaceholder": "Search Archives..." }
        });
    }

    $(document).on('click', '.print_receipt', function(e){
        e.stopPropagation();
        window.open("print.php?order_id=" + $(this).data('id'), "_blank");
    });
});
</script>