<?php
include('rms.php');
$object = new rms();

if(!$object->is_login()) { header("location:".$object->base_url.""); exit; }

$is_waiter = $object->is_waiter_user();
include('header.php');
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    :root { --glass: rgba(255, 255, 255, 0.05); --glass-border: rgba(255, 255, 255, 0.1); --accent: #17a2b8; }
    body { background-color: #0c0c0c; color: white; }
    .glass-card { background: var(--glass) !important; backdrop-filter: blur(15px); border: 1px solid var(--glass-border) !important; border-radius: 15px; }
    
    .table-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 15px; }
    .table-item { background: var(--glass); border: 1px solid var(--glass-border); border-radius: 12px; padding: 20px; text-align: center; cursor: pointer; transition: 0.3s; position: relative; }
    .table-item.occupied { border-left: 4px solid #f6c23e; background: rgba(246, 194, 62, 0.05); }
    .table-item.available { border-left: 4px solid #1cc88a; }
    .status-dot { position: absolute; top: 10px; right: 10px; height: 8px; width: 8px; border-radius: 50%; }

    .order-row { cursor: pointer; border-bottom: 1px solid rgba(255,255,255,0.05); transition: 0.3s; }
    .order-row:hover { background: rgba(255,255,255,0.03); }
    .nav-pills .nav-link { color: #aaa; margin-right: 10px; border-radius: 10px; border: 1px solid transparent; }
    .nav-pills .nav-link.active { background: rgba(23, 162, 184, 0.2) !important; color: #17a2b8 !important; border: 1px solid var(--accent); }
    .item-scroll { max-height: 400px; overflow-y: auto; }

    /* History Table Adjustments */
    #history_table_wrapper .dataTables_length select, 
    #history_table_wrapper .dataTables_filter input {
        background: var(--glass); border: 1px solid var(--glass-border); color: white; border-radius: 5px;
    }
    .page-link { background: var(--glass) !important; border: 1px solid var(--glass-border) !important; color: #17a2b8 !important; }
</style>

<div class="container-fluid">
    <?php if($is_waiter) { ?>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 font-weight-bold">Floor Plan</h1>
            <a href="dashboard.php" class="btn btn-outline-light"><i class="fas fa-th mr-2"></i> Menu</a>
        </div>

        <?php if(isset($_GET['mode']) && $_GET['mode'] == 'select_table') { ?>
        <div class="alert glass-card border-success mb-4">
            <h5 class="text-success"><i class="fas fa-check-circle mr-2"></i> Assign Cart to Table</h5>
            <p class="mb-0">Select an available table to complete the order.</p>
        </div>
        <?php } ?>

        <div class="row">
            <div class="col-lg-5 mb-4">
                <div class="glass-card p-3" id="table_status_area"></div>
            </div>
            <div class="col-lg-7">
                <div class="glass-card p-4" id="order_detail_panel">
                    <div class="text-center py-5 opacity-50">
                        <i class="fas fa-utensils fa-3x mb-3"></i>
                        <h4>Select a table to view details</h4>
                    </div>
                </div>
            </div>
        </div>

    <?php } else { ?>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 font-weight-bold mb-0">Order Oversight</h1>
                <p class="text-white-50 small">Monitor and manage all restaurant transactions</p>
            </div>
            <div class="text-right">
                <button type="button" id="master_print_report" class="btn btn-warning shadow-sm">
                    <i class="fas fa-file-invoice mr-2"></i>Print Sales Report
                </button>
            </div>
        </div>

        <ul class="nav nav-pills mb-4">
            <li class="nav-item"><a class="nav-link active" data-toggle="pill" href="#pending"><i class="fas fa-clock mr-2"></i>Active Orders</a></li>
            <li class="nav-item"><a class="nav-link" data-toggle="pill" href="#history"><i class="fas fa-history mr-2"></i>Order History</a></li>
        </ul>

        <div class="row">
            <div class="col-lg-8">
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="pending">
                        <div class="glass-card overflow-hidden">
                            <table class="table table-borderless text-white mb-0">
                                <thead class="small text-white-50 text-uppercase" style="background: rgba(255,255,255,0.02);">
                                    <tr><th class="pl-4">Order #</th><th>Table</th><th>Waiter</th><th>Total</th><th class="text-right pr-4">Action</th></tr>
                                </thead>
                                <tbody id="admin_pending_list"></tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="tab-pane fade" id="history">
                        <div class="glass-card p-4">
                            <div class="table-responsive">
                                <table class="table table-hover text-white w-100" id="history_table">
                                    <thead>
                                        <tr class="text-white-50 small">
                                            <th>Order #</th>
                                            <th>Date</th>
                                            <th>Table</th>
                                            <th>Cashier</th>
                                            <th>Net Total</th>
                                            <th class="text-right">Action</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="glass-card p-4 sticky-top" style="top: 20px;" id="admin_detail_preview">
                    <div class="text-center py-5 opacity-50">
                        <i class="fas fa-search fa-3x mb-3 text-info"></i>
                        <h5>Select an order to preview details</h5>
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
                    $('.settle_order_btn').html('<i class="fas fa-times-circle mr-2"></i> Cancel Order').removeClass('btn-success').addClass('btn-danger');
                } else {
                    $('.settle_order_btn').remove(); 
                }
            }
        });
    }

    $('#master_print_report').click(function(){
        window.open("print.php?action=print_all", "_blank");
    });

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
                    Swal.fire('Table Occupied', 'Please select an available table.', 'error');
                    return;
                }
                Swal.fire({
                    title: 'Confirm Table',
                    text: "Assign current order to " + table + "?",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Place Order'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "order_action.php",
                            method: "POST",
                            data: {action: 'submit_cart_to_table', table_name: table},
                            success: function(res){ 
                                if(res.trim() == 'success') {
                                    Swal.fire({ icon: 'success', title: 'Success!', timer: 1500, showConfirmButton: false }).then(() => {
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
            "ajax": { 
                "url": "order_action.php", 
                "type": "POST", 
                "data": { "action": "fetch_history" } 
            },
            "columns": [
                { "data": "order_number" },
                { "data": "order_date" },
                { "data": "order_table" },
                { "data": "order_cashier" },
                { "data": "order_total" },
                { "data": "action" }
            ],
            "order": [[ 0, "desc" ]],
            "language": {
                "search": "_INPUT_",
                "searchPlaceholder": "Search history..."
            }
        });
    }

    $(document).on('click', '.print_receipt', function(e){
        e.stopPropagation();
        window.open("print.php?order_id=" + $(this).data('id'), "_blank");
    });
});
</script>