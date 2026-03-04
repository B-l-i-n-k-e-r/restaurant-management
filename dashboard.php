<?php
// dashboard.php
include('rms.php');
$object = new rms();

if(!$object->is_login()) {
    header("location:".$object->base_url);
    exit;
}

include('header.php');
?>

<style>
    :root { 
        --sky-blue: #0ea5e9; 
        --sky-glow: rgba(14, 165, 233, 0.4);
        --deep-navy: #0f172a;
        --glass: rgba(255, 255, 255, 0.03); 
        --glass-border: rgba(255, 255, 255, 0.1); 
        --accent-green: #22c55e;
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

    .glass-card-modal { 
        background: #0f172a !important; 
        border: 1px solid var(--sky-blue); 
        border-radius: 24px; 
        box-shadow: 0 0 30px var(--sky-glow);
    }
    
    /* CONSTRAINT: Fit content for table columns */
    .table td, .table th { 
        white-space: nowrap !important; 
        width: 1% !important; 
        vertical-align: middle;
    }
    .table td.expand, .table th.expand { width: auto !important; white-space: normal !important; }

    /* Category Navigation */
    .category-pill { 
        padding: 10px 22px; 
        background: rgba(255,255,255,0.05); 
        color: #94a3b8; 
        border: 1px solid var(--glass-border); 
        border-radius: 12px; 
        text-decoration: none !important; 
        transition: 0.3s ease; 
        font-size: 0.85rem; 
        font-weight: 600;
    }
    .category-pill:hover { background: var(--sky-blue); color: white; border-color: var(--sky-blue); box-shadow: 0 0 15px var(--sky-glow); }
    .category-pill.active { background: var(--sky-blue); color: white; border-color: var(--sky-blue); box-shadow: 0 0 20px var(--sky-glow); }
    
    /* Product Grid Styling */
    .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 20px; }
    .product-card { 
        background: rgba(255, 255, 255, 0.03); 
        border-radius: 20px; 
        overflow: hidden; 
        position: relative; 
        border: 1px solid var(--glass-border); 
        transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); 
    }
    .product-card:hover { transform: translateY(-8px); border-color: var(--sky-blue); box-shadow: 0 10px 30px rgba(0,0,0,0.5), 0 0 15px var(--sky-glow); }
    .product-img { width: 100%; height: 160px; object-fit: cover; transition: 0.5s; }
    .product-card:hover .product-img { transform: scale(1.1); }
    .product-overlay { padding: 15px; background: linear-gradient(transparent, rgba(15, 23, 42, 1)); position: absolute; bottom: 0; width: 100%; }
    
    .btn-circle-add { 
        position: absolute; top: 12px; right: 12px; border-radius: 50%; width: 38px; height: 38px; 
        display: flex; align-items: center; justify-content: center; border: none; 
        background: var(--sky-blue); color: white; box-shadow: 0 0 15px var(--sky-glow); transition: 0.3s;
    }
    .btn-circle-add:hover { transform: rotate(90deg) scale(1.1); background: #fff; color: var(--sky-blue); }

    .place-order-btn { 
        position: fixed; bottom: 40px; right: 40px; border-radius: 50px; padding: 15px 35px; z-index: 1000; 
        background: var(--accent-green); border: none; color: white; font-weight: 800; 
        box-shadow: 0 0 25px rgba(34, 197, 94, 0.5); text-transform: uppercase; letter-spacing: 1px;
    }

    /* Admin & Cashier Table Aesthetic */
    .custom-table { border-collapse: separate; border-spacing: 0 8px; }
    .custom-table thead th { border: none; font-size: 0.75rem; color: #64748b; text-transform: uppercase; letter-spacing: 1.5px; padding-bottom: 15px; }
    .custom-table tbody tr { background: rgba(255, 255, 255, 0.02); transition: 0.3s; }
    .custom-table tbody tr:hover { background: rgba(255, 255, 255, 0.05); }
    .custom-table td { border: none !important; padding: 18px 15px; }
    .custom-table td:first-child { border-radius: 12px 0 0 12px; }
    .custom-table td:last-child { border-radius: 0 12px 12px 0; }
    
    /* Stats Cards */
    .stat-icon-box { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; }
</style>

<div class="container-fluid mt-4 pb-5">
    <div class="mb-5 d-flex justify-content-between align-items-end">
        <div>
            <h6 class="text-sky-blue font-weight-bold mb-1" style="color: var(--sky-blue); letter-spacing: 2px; text-transform: uppercase;">
                System Dashboard
            </h6>
            <h1 class="h2 mb-0 font-weight-bold text-white">
                <?php 
                if($object->is_waiter_user()) {
                    echo (!isset($_GET['cat'])) ? '<i class="fas fa-bolt mr-2 text-warning"></i>Popular Menu' : '<i class="fas fa-utensils mr-2"></i>Select Items'; 
                } elseif($object->is_cashier_user()) {
                    echo '<i class="fas fa-wallet mr-2 text-success"></i>Billing Terminal';
                } else {
                    echo '<i class="fas fa-rocket mr-2 text-info"></i>Operations Control';
                }
                ?>
            </h1>
        </div>
    </div>

    <?php if($object->is_waiter_user()) { ?>
    <div class="row">
        <div class="col-12">
            <div class="category-pills mb-4 d-flex flex-wrap" style="gap: 12px;">
                <a href="dashboard.php" class="category-pill <?php echo !isset($_GET['cat']) ? 'active' : ''; ?>">
                    <i class="fas fa-star mr-2"></i>All Popular
                </a>
                <?php
                $object->query = "SELECT * FROM product_category_table WHERE category_status = 'Enable' ORDER BY category_name ASC";
                $categories = $object->get_result();
                foreach($categories as $category) {
                    $active = (isset($_GET['cat']) && $_GET['cat'] == $category['category_id']) ? 'active' : '';
                    echo '<a href="dashboard.php?cat='.$category['category_id'].'" class="category-pill '.$active.'">'.$category['category_name'].'</a>';
                }
                ?>
            </div>

            <div class="product-area">
                <div class="row mb-5 align-items-center">
                    <div class="col-md-5 mb-3 mb-md-0">
                        <div class="input-group glass-card border-0 px-3 py-1">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-transparent border-0 text-white-50"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="text" id="search_item" class="form-control bg-transparent text-white border-0" placeholder="Search menu items...">
                        </div>
                    </div>
                    <div class="col-md-7 text-md-right">
                        <button type="button" class="btn glass-card px-4 py-3 text-white" id="open_cart" style="border-color: var(--sky-blue) !important;">
                            <i class="fas fa-shopping-bag mr-2 text-sky-blue"></i>Cart
                            <span class="badge badge-pill badge-primary ml-2 py-1 px-2" id="cart_count"><?php echo $object->Get_cart_count(); ?></span>
                        </button>
                    </div>
                </div>

                <div class="product-grid" id="product_list">
                    <?php
                    if(!isset($_GET['cat'])) {
                        $object->query = "SELECT p.* FROM product_table p WHERE p.product_status = 'Enable' LIMIT 12";
                    } else {
                        $category_id = intval($_GET['cat']);
                        $object->query = "SELECT p.* FROM product_table p JOIN product_category_table c ON p.category_name = c.category_name WHERE c.category_id = '$category_id' AND p.product_status = 'Enable'";
                    }
                    $products = $object->get_result();
                    foreach($products as $product) {
                        $img = (!empty($product['product_image']) && file_exists('images/'.$product['product_image'])) ? 'images/'.$product['product_image'] : 'img/no-image.png';
                        ?>
                        <div class="product-card">
                            <img src="<?php echo $img; ?>" class="product-img" loading="lazy">
                            <button class="btn btn-circle-add add_to_cart" data-id="<?php echo $product['product_id']; ?>" data-name="<?php echo $product['product_name']; ?>">
                                <i class="fas fa-plus"></i>
                            </button>
                            <div class="product-overlay">
                                <h6 class="text-white mb-1 font-weight-bold"><?php echo $product['product_name']; ?></h6>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-sky-blue font-weight-bold"><?php echo $object->cur . ' ' . $product['product_price']; ?></span>
                                    <span class="badge badge-dark opacity-50 small" style="font-size: 10px;">IN STOCK</span>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>

    <a href="order.php?mode=select_table" class="btn btn-success place-order-btn">
        <i class="fas fa-chair mr-2"></i> Select Table
    </a>

    <?php } elseif($object->is_cashier_user()) { ?>
    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="glass-card p-5 text-center h-100 d-flex flex-column justify-content-center" style="border-left: 5px solid #f6e05e !important;">
                <div class="mx-auto mb-4 stat-icon-box bg-warning-soft" style="background: rgba(246, 224, 94, 0.1);">
                    <i class="fas fa-receipt fa-2x text-warning"></i>
                </div>
                <h6 class="text-white-50 text-uppercase small font-weight-bold mb-2">Pending Invoices</h6>
                <h1 class="display-3 font-weight-bold text-white mb-0" id="pendingCount">0</h1>
                <p class="mt-3 mb-0 text-warning opacity-75"><i class="fas fa-sync fa-spin mr-2"></i>Live updates active</p>
            </div>
        </div>

        <div class="col-lg-8 mb-4">
            <div class="glass-card shadow-lg">
                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center pt-4 px-4">
                    <h5 class="text-white mb-0 font-weight-bold"><i class="fas fa-stream mr-2 text-sky-blue"></i>Billing Queue</h5>
                    <button class="btn btn-sm btn-outline-light border-0 rounded-pill px-3" onclick="load_cashier_queue()">
                        <i class="fas fa-redo-alt mr-2"></i>Sync
                    </button>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table custom-table mb-0">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th class="text-center">Table</th>
                                    <th class="expand">Staff</th>
                                    <th class="text-right">Total</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody id="cashier_billing_list">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php } else { ?>
    <div class="row">
        <?php 
        $stats = [
            ["Revenue (Today)", "sky-blue", "chart-line", $object->Get_total_today_sales()],
            ["Active Orders", "success", "shopping-bag", $object->Get_total_orders()],
            ["Total Tables", "warning", "chair", $object->Get_total_tables()],
            ["Performance", "info", "rocket", "Peak"]
        ];
        foreach($stats as $s) { ?>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="glass-card h-100 p-3 border-0" style="transition: 0.3s hover;">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: var(--<?php echo $s[1]; ?>); letter-spacing: 1px;"><?php echo $s[0]; ?></div>
                            <div class="h3 mb-0 font-weight-bold text-white"><?php echo $s[3]; ?></div>
                        </div>
                        <div class="stat-icon-box" style="background: rgba(255,255,255,0.05);">
                            <i class="fas fa-<?php echo $s[2]; ?> text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>

    <div class="glass-card mb-4 mt-2">
        <div class="card-header bg-transparent border-0 pt-4 px-4">
            <h5 class="m-0 font-weight-bold text-white">
                <span class="mr-2" style="display:inline-block; width:10px; height:10px; border-radius:50%; background:var(--accent-green); box-shadow:0 0 10px var(--accent-green);"></span>
                Live Dining Status
            </h5>
        </div>
        <div class="card-body p-4">
            <div id="admin_table_status" class="row no-gutters">
                </div>
        </div>
    </div>
    <?php } ?>
</div>

<div class="modal fade" id="cartModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content glass-card-modal text-white">
            <div class="modal-header border-bottom border-secondary px-4 py-4">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-shopping-basket mr-3 text-sky-blue"></i>Order Summary</h5>
                <button type="button" class="close text-white opacity-50" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-4" id="cart_details_area"></div>
            <div class="modal-footer border-0 px-4 pb-4">
                <button type="button" class="btn btn-outline-light rounded-pill px-4" data-dismiss="modal">Add More Items</button>
                <a href="order.php?mode=select_table" class="btn btn-primary rounded-pill px-5 shadow-lg" style="background: var(--sky-blue); border:none;">
                    PROCEED TO TABLE <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="settleModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content glass-card-modal text-white">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="modal-title font-weight-bold text-success">Finalize Settlement</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-5 text-center">
                <p class="text-white-50 small text-uppercase mb-1 font-weight-bold">Amount Outstanding</p>
                <h1 class="display-3 font-weight-bold text-white mb-5" id="display_total" style="text-shadow: 0 0 20px rgba(255,255,255,0.2);">0.00</h1>

                <div class="form-group text-left mb-4">
                    <label class="small text-white-50 ml-1">Payment Method</label>
                    <select id="payment_method" class="form-control glass-card border-secondary text-white" style="height: 55px; background: rgba(0,0,0,0.3) !important;">
                        <option value="Cash">💵 Cash Payment</option>
                        <option value="Card">💳 Credit / Debit Card</option>
                        <option value="M-Pesa">📱 Mobile Money (M-Pesa)</option>
                    </select>
                </div>

                <div id="cash_only_section" class="text-left">
                    <div class="form-group">
                        <label class="small text-white-50 ml-1">Cash Received</label>
                        <input type="number" id="amount_received" class="form-control form-control-lg glass-card text-white border-secondary" placeholder="0.00" style="height: 60px; font-size: 1.5rem; background: rgba(255,255,255,0.05) !important;">
                    </div>
                    <div class="p-3 rounded-xl d-flex justify-content-between align-items-center" style="background: rgba(0,0,0,0.2); border-radius: 15px;">
                        <span class="text-white-50">Change Due:</span>
                        <h3 class="mb-0 text-warning font-weight-bold" id="display_change">0.00</h3>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 p-4">
                <input type="hidden" id="settle_order_id">
                <button type="button" id="confirm_settlement_btn" class="btn btn-block py-3 font-weight-bold" style="background: var(--accent-green); color: white; border-radius: 15px; box-shadow: 0 10px 20px rgba(34, 197, 94, 0.3);">
                    COMPLETE & PRINT RECEIPT
                </button>
            </div>
        </div>
    </div>
</div>

<div id="addToCartToast" class="toast glass-card text-white border-0 position-fixed bottom-0 right-0 m-4" style="z-index: 9999; border-left: 4px solid var(--accent-green) !important;" role="alert" data-delay="1500">
    <div class="toast-body d-flex align-items-center py-3">
        <i class="fas fa-check-circle text-success mr-3 fa-lg"></i> 
        <span id="toastMessage" class="font-weight-bold"></span>
    </div>
</div>

<?php include('footer.php'); ?>

<script>
$(document).ready(function(){
    // Search Logic
    $("#search_item").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $(".product-card").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

    <?php if($object->is_waiter_user()) { ?>
        $(document).on('click', '.add_to_cart', function(){
            var id = $(this).data('id');
            var name = $(this).data('name');
            $.ajax({
                url: "order_action.php",
                method: "POST",
                data: {product_id: id, action: 'add'},
                dataType: 'json',
                success: function(data){
                    $('#cart_count').text(data.cart_count);
                    $('#toastMessage').text(name + ' added');
                    $('#addToCartToast').toast('show');
                }
            });
        });

        $('#open_cart').click(function(){
            $('#cartModal').modal('show');
            load_cart_data();
        });

        function load_cart_data() {
            $.ajax({
                url: "order_action.php",
                method: "POST",
                data: {action: 'fetch_cart'},
                success: function(data){ $('#cart_details_area').html(data); }
            });
        }

        $(document).on('click', '.remove_cart_item', function(){
            var id = $(this).data('id');
            $.ajax({
                url: "order_action.php",
                method: "POST",
                data: {product_id: id, action: 'remove_cart_item'},
                dataType: 'json',
                success: function(data){
                    $('#cart_count').text(data.cart_count);
                    load_cart_data();
                }
            });
        });
    <?php } ?>

    <?php if($object->is_cashier_user()) { ?>
        let currentTotal = 0;

        function load_cashier_queue() {
            $.ajax({
                url: "order_action.php",
                method: "POST",
                data: {action: 'fetch_cashier_queue'},
                success: function(data){ 
                    $('#cashier_billing_list').html(data);
                    var count = $('#cashier_billing_list tr').length;
                    if($('#cashier_billing_list').text().trim().includes("No orders") || count == 0){
                        $('#pendingCount').text(0);
                    } else {
                        $('#pendingCount').text(count);
                    }
                }
            });
        }

        $(document).on('click', '.settle_order_btn', function(){
            let id = $(this).data('id');
            let amountText = $(this).closest('td').find('span.font-weight-bold').text();
            let amount = amountText.replace(/[^0-9.]/g, ''); 
            
            currentTotal = parseFloat(amount);
            $('#settle_order_id').val(id);
            $('#display_total').text(amountText);
            $('#amount_received').val('');
            $('#display_change').text('0.00');
            $('#settleModal').modal('show');
        });

        $('#amount_received').on('keyup change', function(){
            let received = parseFloat($(this).val()) || 0;
            let change = received - currentTotal;
            $('#display_change').text(change >= 0 ? change.toFixed(2) : '0.00');
        });

        $('#payment_method').change(function(){
            if($(this).val() !== 'Cash') {
                $('#cash_only_section').fadeOut(200);
            } else {
                $('#cash_only_section').fadeIn(200);
            }
        });

        $('#confirm_settlement_btn').click(function(){
            let id = $('#settle_order_id').val();
            let method = $('#payment_method').val();
            let received = $('#amount_received').val();

            if(method === 'Cash' && (parseFloat(received) < currentTotal)) {
                alert("Insufficient cash received.");
                return;
            }

            $.ajax({
                url: "order_action.php",
                method: "POST",
                data: {
                    action: 'settle_order',
                    order_id: id,
                    payment_method: method
                },
                success: function(data){
                    if(data.trim() == 'success') {
                        $('#settleModal').modal('hide');
                        load_cashier_queue();
                    }
                }
            });
        });

        load_cashier_queue();
        setInterval(load_cashier_queue, 10000);
    <?php } ?>

    <?php if($object->is_master_user()) { ?>
        function load_admin_monitor() {
            $.ajax({
                url: "order_action.php",
                method: "POST",
                data: {action: 'fetch_modern_tables'},
                success: function(data){ $('#admin_table_status').html(data); }
            });
        }
        load_admin_monitor();
        setInterval(load_admin_monitor, 15000);
    <?php } ?>
});
</script>