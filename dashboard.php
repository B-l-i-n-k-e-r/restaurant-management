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
    :root { --glass: rgba(255, 255, 255, 0.05); --glass-border: rgba(255, 255, 255, 0.1); --accent: #17a2b8; }
    body { background-color: #0c0c0c; color: white; }
    
    .glass-card { background: var(--glass) !important; backdrop-filter: blur(15px); border: 1px solid var(--glass-border) !important; border-radius: 15px; }
    .glass-card-modal { background: #1a1a2e !important; border: 1px solid var(--glass-border); border-radius: 20px; }
    
    /* Constraint: Fit content for table columns */
    .fit-content { width: 1% !important; white-space: nowrap !important; }

    /* Waiter UI Elements */
    .category-pill { padding: 8px 18px; background: rgba(255,255,255,0.05); color: #aaa; border: 1px solid var(--glass-border); border-radius: 50px; text-decoration: none !important; transition: 0.3s; font-size: 0.9rem; }
    .category-pill:hover { background: rgba(255,255,255,0.1); color: white; }
    .category-pill.active { background: #e74c3c; color: white; border-color: #e74c3c; box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3); }
    
    .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 15px; }
    .product-card { background: var(--glass); border-radius: 15px; overflow: hidden; position: relative; border: 1px solid var(--glass-border); transition: 0.3s; height: 100%; }
    .product-card:hover { transform: translateY(-5px); border-color: rgba(255,255,255,0.2); box-shadow: 0 10px 20px rgba(0,0,0,0.3); }
    .product-img { width: 100%; height: 140px; object-fit: cover; }
    .product-overlay { padding: 10px; background: linear-gradient(transparent, rgba(0,0,0,0.9)); position: absolute; bottom: 0; width: 100%; }
    
    .btn-circle-add { position: absolute; top: 10px; right: 10px; border-radius: 50%; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border: none; box-shadow: 0 4px 10px rgba(0,0,0,0.3); }
    .place-order-btn { position: fixed; bottom: 30px; right: 30px; border-radius: 50px; padding: 12px 25px; z-index: 1000; box-shadow: 0 8px 25px rgba(28, 200, 138, 0.4); font-weight: bold; letter-spacing: 0.5px; }

    /* Admin/Cashier Table Styles */
    .custom-table thead th { border: none; font-size: 11px; letter-spacing: 1px; color: #888; text-transform: uppercase; background: rgba(255,255,255,0.02); }
    .custom-table td { border-top: 1px solid rgba(255,255,255,0.05) !important; vertical-align: middle; }
    
    .dashboard-card { transition: 0.3s; }
    .dashboard-card:hover { background: rgba(255,255,255,0.08) !important; }

    /* Settlement Styles */
    .bg-black-soft { background: rgba(0,0,0,0.3); border-radius: 10px; padding: 15px; }
</style>

<div class="container-fluid mt-3">
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0 font-weight-bold">
                <?php 
                if($object->is_waiter_user()) {
                    echo (!isset($_GET['cat'])) ? '<i class="fas fa-fire text-danger mr-2"></i>Popular Items' : '<i class="fas fa-utensils text-info mr-2"></i>Menu Category'; 
                } elseif($object->is_cashier_user()) {
                    echo '<i class="fas fa-cash-register text-success mr-2"></i>Cashier Desk';
                } else {
                    echo '<i class="fas fa-chart-pie text-primary mr-2"></i>Management Hub';
                }
                ?>
            </h1>
        </div>
        <div class="text-right">
            <span class="badge glass-card px-3 py-2 text-white-50 font-weight-normal">
                <i class="far fa-calendar-alt mr-2"></i><?php echo date('l, d M Y'); ?>
            </span>
        </div>
    </div>

    <?php if($object->is_waiter_user()) { ?>
    <div class="row">
        <div class="col-12">
            <div class="category-pills mb-4 d-flex flex-wrap" style="gap: 10px;">
                <a href="dashboard.php" class="category-pill <?php echo !isset($_GET['cat']) ? 'active' : ''; ?>">
                    All Popular
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
                <div class="row mb-4 align-items-center">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <div class="input-group search-container glass-card px-2 py-1">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-transparent border-0 text-white-50"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="text" id="search_item" class="form-control bg-transparent text-white border-0" placeholder="Find food or drinks...">
                        </div>
                    </div>
                    <div class="col-md-6 text-md-right">
                        <button type="button" class="btn btn-info rounded-pill px-4 shadow-sm" id="open_cart">
                            <i class="fas fa-shopping-basket mr-2"></i>View Cart 
                            <span class="badge badge-light ml-2" id="cart_count"><?php echo $object->Get_cart_count(); ?></span>
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
                            <div class="product-overlay">
                                <h6 class="text-white mb-0 small font-weight-bold"><?php echo $product['product_name']; ?></h6>
                                <span class="text-success small"><?php echo $object->cur . ' ' . $product['product_price']; ?></span>
                            </div>
                            <button class="btn btn-success btn-circle-add add_to_cart" data-id="<?php echo $product['product_id']; ?>" data-name="<?php echo $product['product_name']; ?>">
                                <i class="fas fa-plus fa-sm"></i>
                            </button>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>

    <a href="order.php?mode=select_table" class="btn btn-success btn-lg shadow-lg place-order-btn animate__animated animate__bounceIn">
        <i class="fas fa-chevron-right mr-2"></i> CHOOSE TABLE
    </a>

    <?php } elseif($object->is_cashier_user()) { ?>
    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="glass-card p-4 text-center border-warning h-100 d-flex flex-column justify-content-center">
                <i class="fas fa-clock fa-3x text-warning mb-3"></i>
                <h5 class="text-white-50 text-uppercase small font-weight-bold">Awaiting Settlement</h5>
                <h1 class="display-4 font-weight-bold text-white mb-0" id="pendingCount">0</h1>
                <p class="mt-2 mb-0 small text-warning">Orders ready for billing</p>
            </div>
        </div>

        <div class="col-lg-8 mb-4">
            <div class="card glass-card shadow-lg border-0">
                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center pt-4 px-4">
                    <h5 class="text-white mb-0 font-weight-bold">Active Billing Queue</h5>
                    <button class="btn btn-sm btn-outline-info rounded-pill px-3" onclick="load_cashier_queue()">
                        <i class="fas fa-sync-alt mr-2"></i>Refresh
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table custom-table mb-0">
                            <thead>
                                <tr>
                                    <th class="pl-4 fit-content">Order ID</th>
                                    <th class="fit-content text-center">Table</th>
                                    <th>Assigned Waiter</th>
                                    <th class="text-right pr-4 fit-content">Total Amount</th>
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
            ["Revenue (Today)", "primary", "chart-line", $object->Get_total_today_sales()],
            ["Total Orders", "success", "shopping-bag", $object->Get_total_orders()],
            ["Active Tables", "warning", "chair", $object->Get_total_tables()],
            ["Net Growth", "info", "rocket", "12%"]
        ];
        foreach($stats as $s) { ?>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card glass-card dashboard-card h-100">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-xs font-weight-bold text-<?php echo $s[1]; ?> text-uppercase mb-1"><?php echo $s[0]; ?></div>
                            <div class="h3 mb-0 font-weight-bold text-white"><?php echo $s[3]; ?></div>
                        </div>
                        <i class="fas fa-<?php echo $s[2]; ?> fa-2x text-white-50"></i>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>

    <div class="card glass-card mb-4 border-0">
        <div class="card-header bg-transparent border-0 pt-4 px-4">
            <h6 class="m-0 font-weight-bold text-white"><i class="fas fa-signal mr-2 text-success"></i> Live Restaurant Pulse</h6>
        </div>
        <div class="card-body">
            <div id="admin_table_status" class="row no-gutters">
            </div>
        </div>
    </div>
    <?php } ?>
</div>

<div class="modal fade" id="cartModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content glass-card-modal text-white">
            <div class="modal-header border-bottom border-secondary">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-shopping-cart mr-2"></i>Items in Cart</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body" id="cart_details_area"></div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-light rounded-pill" data-dismiss="modal">Add More</button>
                <a href="order.php?mode=select_table" class="btn btn-success rounded-pill px-4">ASSIGN TO TABLE</a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="settleModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content glass-card-modal text-white">
            <div class="modal-header border-bottom border-secondary">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-file-invoice-dollar mr-2 text-success"></i>Payment Details</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-4">
                <div class="text-center mb-4">
                    <span class="text-white-50 small text-uppercase font-weight-bold">Amount to Pay</span>
                    <h1 class="display-4 font-weight-bold text-success mt-1" id="display_total">0.00</h1>
                </div>

                <div class="form-group">
                    <label class="small text-white-50">Payment Method</label>
                    <select id="payment_method" class="form-control bg-dark text-white border-secondary">
                        <option value="Cash">Cash</option>
                        <option value="Card">Credit/Debit Card</option>
                        <option value="M-Pesa">Mobile Money (M-Pesa)</option>
                    </select>
                </div>

                <div id="cash_only_section">
                    <div class="form-group">
                        <label class="small text-white-50">Cash Received</label>
                        <input type="number" id="amount_received" class="form-control form-control-lg bg-white text-dark font-weight-bold" placeholder="0.00">
                    </div>
                    <div class="bg-black-soft d-flex justify-content-between align-items-center">
                        <span class="text-white-50">Change to Return:</span>
                        <h4 class="mb-0 text-warning font-weight-bold" id="display_change">0.00</h4>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <input type="hidden" id="settle_order_id">
                <button type="button" class="btn btn-outline-light rounded-pill px-4" data-dismiss="modal">Cancel</button>
                <button type="button" id="confirm_settlement_btn" class="btn btn-success rounded-pill px-4">COMPLETE ORDER</button>
            </div>
        </div>
    </div>
</div>

<div id="addToCartToast" class="toast glass-card text-white border-0 position-fixed bottom-0 right-0 m-3" style="z-index: 9999;" role="alert" data-delay="1500">
    <div class="toast-body d-flex align-items-center">
        <i class="fas fa-check-circle text-success mr-2 fa-lg"></i> 
        <span id="toastMessage"></span>
    </div>
</div>

<?php include('footer.php'); ?>

<script>
$(document).ready(function(){
    // Universal Search
    $("#search_item").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $(".product-card").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

    <?php if($object->is_waiter_user()) { ?>
        // --- WAITER INTERACTION ---
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
                    $('#toastMessage').text(name + ' added to basket');
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
        // --- CASHIER QUEUE & SETTLEMENT ---
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

        // Open Settlement Modal
        $(document).on('click', '.settle_order_btn', function(){
            let id = $(this).data('id');
            // Extract numerical value from the amount span
            let amountText = $(this).closest('td').find('span.font-weight-bold').text();
            let amount = amountText.replace(/[^0-9.]/g, ''); 
            
            currentTotal = parseFloat(amount);
            $('#settle_order_id').val(id);
            $('#display_total').text(amountText);
            $('#amount_received').val('');
            $('#display_change').text('0.00');
            $('#settleModal').modal('show');
        });

        // Live Change Calculation
        $('#amount_received').on('keyup change', function(){
            let received = parseFloat($(this).val()) || 0;
            let change = received - currentTotal;
            $('#display_change').text(change >= 0 ? change.toFixed(2) : '0.00');
        });

        // Hide cash section if Card/Mobile is selected
        $('#payment_method').change(function(){
            if($(this).val() !== 'Cash') {
                $('#cash_only_section').fadeOut();
            } else {
                $('#cash_only_section').fadeIn();
            }
        });

        // Confirm Payment
        $('#confirm_settlement_btn').click(function(){
            let id = $('#settle_order_id').val();
            let method = $('#payment_method').val();
            let received = $('#amount_received').val();

            if(method === 'Cash' && (parseFloat(received) < currentTotal)) {
                alert("The amount received is less than the total due.");
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
        // --- ADMIN LIVE MONITOR ---
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