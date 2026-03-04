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
        --glass-bg: rgba(255, 255, 255, 0.03); 
        --glass-border: rgba(255, 255, 255, 0.1); 
        --accent-cyan: #0ea5e9; 
        --neon-green: #10b981;
        --neon-yellow: #f59e0b;
        --neon-red: #ef4444;
    }

    body { background-color: #0c0f17; color: #e2e8f0; }

    /* 1. GLASS CONTAINER STYLING */
    .glass-card { 
        background: var(--glass-bg) !important; 
        backdrop-filter: blur(20px); 
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid var(--glass-border) !important; 
        border-radius: 20px; 
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
    }

    /* 2. FLOOR PLAN GRID */
    .table-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 15px; }
    
    .table-item { 
        background: rgba(255, 255, 255, 0.02); 
        border: 1px solid var(--glass-border); 
        border-radius: 18px; 
        padding: 24px; 
        text-align: center; 
        cursor: pointer; 
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); 
    }
    
    .table-item:hover { transform: translateY(-5px); border-color: var(--accent-cyan); background: rgba(14, 165, 233, 0.05); }
    
    .table-item.occupied { border-left: 5px solid var(--neon-yellow); background: rgba(245, 158, 11, 0.05); }
    .table-item.available { border-left: 5px solid var(--neon-green); }

    /* 3. TABLE STYLING - FIT CONTENT */
    .table { color: #cbd5e1 !important; margin-bottom: 0 !important; }
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
    }

    .order-row { cursor: pointer; transition: 0.2s; }
    .order-row:hover { background: rgba(14, 165, 233, 0.08) !important; }

    /* 4. NAVIGATION PILLS */
    .nav-pills .nav-link { 
        color: #94a3b8; 
        border-radius: 12px; 
        border: 1px solid transparent; 
        transition: 0.3s;
        font-weight: 600;
        padding: 10px 20px;
    }
    .nav-pills .nav-link.active { 
        background: rgba(14, 165, 233, 0.1) !important; 
        color: var(--accent-cyan) !important; 
        border: 1px solid var(--accent-cyan); 
    }

    /* 5. DATATABLE CUSTOMS */
    .dataTables_wrapper .dataTables_filter input {
        background: rgba(255, 255, 255, 0.05) !important;
        border: 1px solid var(--glass-border) !important;
        color: white !important;
        border-radius: 10px;
        padding: 5px 15px;
    }

    /* 6. CYBER-GLASS SWEETALERT2 OVERRIDES */
    .swal2-popup.cyber-popup {
        background: rgba(15, 23, 42, 0.95) !important;
        backdrop-filter: blur(20px) !important;
        border: 1px solid var(--accent-cyan) !important;
        border-radius: 24px !important;
        color: #fff !important;
        box-shadow: 0 0 40px rgba(14, 165, 233, 0.15) !important;
        padding: 2rem !important;
    }

    .swal2-title {
        color: var(--accent-cyan) !important;
        text-transform: uppercase !important;
        letter-spacing: 3px !important;
        font-weight: 900 !important;
        font-size: 1.5rem !important;
        margin-bottom: 1rem !important;
    }

    .swal2-html-container {
        color: rgba(255, 255, 255, 0.7) !important;
        font-size: 1rem !important;
    }

    .swal2-confirm.cyber-confirm {
        background: transparent !important;
        border: 1px solid var(--neon-green) !important;
        color: var(--neon-green) !important;
        box-shadow: inset 0 0 10px rgba(16, 185, 129, 0.1) !important;
        text-transform: uppercase !important;
        font-weight: 800 !important;
        letter-spacing: 1px;
        border-radius: 12px !important;
        padding: 12px 30px !important;
        margin: 10px !important;
        transition: 0.3s !important;
    }

    .swal2-confirm.cyber-confirm:hover {
        background: var(--neon-green) !important;
        color: #000 !important;
        box-shadow: 0 0 25px var(--neon-green) !important;
    }

    .swal2-cancel.cyber-cancel {
        background: transparent !important;
        border: 1px solid var(--neon-red) !important;
        color: var(--neon-red) !important;
        text-transform: uppercase !important;
        font-weight: 800 !important;
        letter-spacing: 1px;
        border-radius: 12px !important;
        padding: 12px 30px !important;
        margin: 10px !important;
        transition: 0.3s !important;
    }

    .swal2-cancel.cyber-cancel:hover {
        background: var(--neon-red) !important;
        color: #fff !important;
        box-shadow: 0 0 25px var(--neon-red) !important;
    }

    /* Custom Icon Color override */
    .swal2-icon.swal2-question { border-color: var(--accent-cyan) !important; color: var(--accent-cyan) !important; }
    .swal2-icon.swal2-warning { border-color: var(--neon-yellow) !important; color: var(--neon-yellow) !important; }

</style>

<div class="container-fluid py-4">
    <?php if($is_waiter) { ?>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 font-weight-bold text-white mb-0">Table Management</h1>
                <p class="text-white-50 small text-uppercase letter-spacing-1">Real-time table status & management</p>
            </div>
            <a href="dashboard.php" class="btn btn-info shadow-sm px-4" style="border-radius: 12px; font-weight: bold;">
                <i class="fas fa-plus-circle mr-2"></i> NEW ORDER
            </a>
        </div>

        <?php if(isset($_GET['mode']) && $_GET['mode'] == 'select_table') { ?>
        <div class="alert glass-card border-success mb-4 animate__animated animate__pulse animate__infinite">
            <h6 class="text-success font-weight-bold mb-1"><i class="fas fa-crosshairs mr-2"></i> TABLE SELECTION REQUIRED</h6>
            <p class="mb-0 small opacity-75">Tap an available green table to deploy the current cart items.</p>
        </div>
        <?php } ?>

        <div class="row">
            <div class="col-lg-7 mb-4">
                <div class="glass-card p-4" id="table_status_area"></div>
            </div>
            <div class="col-lg-5">
                <div class="glass-card p-4 sticky-top" style="top: 100px; min-height: 300px;" id="order_detail_panel">
                    <div class="text-center py-5 opacity-25">
                        <i class="fas fa-microchip fa-4x mb-4 text-info"></i>
                        <h5>SELECT TABLE TO VIEW ORDERS </h5>
                    </div>
                </div>
            </div>
        </div>

    <?php } else { ?>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 font-weight-bold text-white mb-0">Order Oversight</h1>
                <p class="text-white-50 small text-uppercase">View Orders & Sales History</p>
            </div>
            <button type="button" id="master_print_report" class="btn btn-outline-info shadow-sm px-4" style="border-radius: 12px;">
                <i class="fas fa-file-invoice mr-2"></i>DAILY REPORT
            </button>
        </div>

        <ul class="nav nav-pills mb-4">
            <li class="nav-item">
                <a class="nav-link active" data-toggle="pill" href="#pending">
                    <i class="fas fa-satellite-dish mr-2"></i>ACTIVE ORDERS
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="pill" href="#history">
                    <i class="fas fa-archive mr-2"></i>SALES HISTORY
                </a>
            </li>
        </ul>

        <div class="row">
            <div class="col-lg-8">
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="pending">
                        <div class="glass-card overflow-hidden">
                            <table class="table table-borderless">
                                <thead>
                                    <tr>
                                        <th class="pl-4 fit-content">ID #</th>
                                        <th class="fit-content">UNIT</th>
                                        <th>OPERATOR</th>
                                        <th class="fit-content">REVENUE</th>
                                        <th class="text-right pr-4 fit-content">PROTOCOL</th>
                                    </tr>
                                </thead>
                                <tbody id="admin_pending_list"></tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="tab-pane fade" id="history">
                        <div class="glass-card p-4">
                            <div class="table-responsive">
                                <table class="table table-hover w-100" id="history_table">
                                    <thead>
                                        <tr>
                                            <th class="fit-content">ID #</th>
                                            <th class="fit-content">TIMESTAMP</th>
                                            <th class="fit-content text-center">UNIT</th>
                                            <th>CLEARANCE</th>
                                            <th class="fit-content">TOTAL</th>
                                            <th class="text-right pr-4 fit-content">PROTOCOL</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="glass-card p-4 sticky-top" style="top: 100px; min-height: 400px;" id="admin_detail_preview">
                    <div class="text-center py-5 opacity-25">
                        <i class="fas fa-fingerprint fa-4x mb-4 text-info"></i>
                        <h5>SELECT LOG FOR INSPECTION</h5>
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
            title: 'Cancel Mission?',
            text: "Order data will be purged and table cleared.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'PURGE ORDER',
            cancelButtonText: 'CANCEL',
            customClass: {
                popup: 'cyber-popup',
                confirmButton: 'cyber-cancel', // Using red for delete
                cancelButton: 'cyber-confirm'  // Using green for cancel/stay
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
                            Swal.fire({ 
                                icon: 'success', 
                                title: 'Purged', 
                                customClass: { popup: 'cyber-popup' },
                                buttonsStyling: false,
                                confirmButtonClass: 'cyber-confirm'
                            }).then(() => {
                                load_tables();
                                $('#order_detail_panel').html('<div class="text-center py-5 opacity-25"><i class="fas fa-microchip fa-4x mb-4 text-info"></i><h5>SELECT TABLE FOR TELEMETRY</h5></div>');
                            });
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
                    title: 'Confirm Deployment',
                    text: "Assign items to " + table + "?",
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
                                    Swal.fire({ 
                                        icon: 'success', 
                                        title: 'Deployed!', 
                                        customClass: { popup: 'cyber-popup' },
                                        buttonsStyling: false,
                                        confirmButtonClass: 'cyber-confirm'
                                    }).then(() => {
                                        window.location.href = "dashboard.php";
                                    });
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
                { "data": "order_number", "className": "fit-content font-weight-bold" },
                { "data": "order_date", "className": "fit-content" },
                { "data": "order_table", "className": "fit-content text-center" },
                { "data": "order_cashier" },
                { "data": "order_total", "className": "fit-content text-info font-weight-bold" },
                { "data": "action", "className": "fit-content text-right" }
            ],
            "order": [[ 0, "desc" ]],
            "language": { "search": "", "searchPlaceholder": "Filter logs..." }
        });
    }

    $(document).on('click', '.print_receipt', function(e){
        e.stopPropagation();
        window.open("print.php?order_id=" + $(this).data('id'), "_blank");
    });
});
</script>