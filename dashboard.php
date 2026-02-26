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

<div class="container-fluid">
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <h1 class="h3 mb-0 text-white">
            <?php 
            if($object->is_waiter_user()) {
                echo (!isset($_GET['cat'])) ? 'Popular Items' : 'Menu Category'; 
            } elseif($object->is_cashier_user()) {
                echo 'Cashier Billing';
            } else {
                echo 'Admin Dashboard';
            }
            ?>
        </h1>
        <span class="text-white-50"><?php echo date('l, d M Y'); ?></span>
    </div>

    <?php if($object->is_waiter_user()) { ?>
    <div class="waiter-grid-container">
        <div class="category-pills mb-4">
            <div class="d-flex flex-wrap gap-2">
                <a href="dashboard.php" class="category-pill <?php echo !isset($_GET['cat']) ? 'active' : ''; ?>">
                    <i class="fas fa-fire me-2"></i> Popular
                </a>
                <?php
                $object->query = "SELECT * FROM product_category_table WHERE category_status = 'Enable' ORDER BY category_name ASC";
                $categories = $object->get_result();
                foreach($categories as $category) {
                    $active = (isset($_GET['cat']) && $_GET['cat'] == $category['category_id']) ? 'active' : '';
                    echo '<a href="dashboard.php?cat='.$category['category_id'].'" class="category-pill '.$active.'">
                            <i class="fas fa-utensils me-2"></i> '.$category['category_name'].'
                          </a>';
                }
                ?>
            </div>
        </div>

        <div class="product-area">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
                <div class="input-group w-100 w-md-50 mb-2 mb-md-0 search-container glass-card px-2">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-transparent border-0 text-white-50"><i class="fas fa-search"></i></span>
                    </div>
                    <input type="text" id="search_item" class="form-control bg-transparent text-white border-0" placeholder="Search delicious items...">
                </div>

                <button type="button" class="btn btn-danger rounded-pill px-4 shadow-lg ml-md-2" id="open_cart">
                    <i class="fas fa-shopping-bag me-2"></i>
                    Cart (<span id="cart_count"><?php echo $object->Get_cart_count(); ?></span>)
                </button>
            </div>

            <div class="product-grid" id="product_list">
                <?php
                if(!isset($_GET['cat'])) {
                    $object->query = "
                        SELECT p.*, COUNT(o.product_name) as total_sales 
                        FROM product_table p 
                        LEFT JOIN order_item_table o ON p.product_name = o.product_name 
                        WHERE p.product_status = 'Enable' 
                        GROUP BY p.product_id 
                        ORDER BY total_sales DESC, p.product_name ASC 
                        LIMIT 12
                    ";
                } else {
                    $category_id = intval($_GET['cat']);
                    $object->query = "SELECT category_name FROM product_category_table WHERE category_id = '$category_id'";
                    $cat_data = $object->get_result();
                    if(!empty($cat_data)) {
                        $c_name = $cat_data[0]['category_name'];
                        $object->query = "SELECT * FROM product_table WHERE category_name = '$c_name' AND product_status = 'Enable' ORDER BY product_name ASC";
                    }
                }
                
                $products = $object->get_result();

                if(!empty($products)) {
                    foreach($products as $index => $product) {
                        $img = (!empty($product['product_image']) && file_exists('images/'.$product['product_image'])) 
                               ? 'images/'.$product['product_image'] 
                               : 'img/no-image.png';
                        ?>
                        <div class="product-card">
                            <div class="product-img-container shadow-sm">
                                <img src="<?php echo $img; ?>" class="product-img" loading="lazy">
                                <div class="product-overlay">
                                    <h6 class="text-white mb-0 font-weight-bold"><?php echo $product['product_name']; ?></h6>
                                    <span class="text-success font-weight-bold"><?php echo $object->cur . ' ' . $product['product_price']; ?></span>
                                </div>
                            </div>
                            <button class="btn btn-success btn-circle-add add_to_cart" data-id="<?php echo $product['product_id']; ?>" data-name="<?php echo $product['product_name']; ?>">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
        </div>
    </div>

    <a href="order.php?mode=select_table" class="btn btn-success btn-lg shadow-lg place-order-btn">
        <i class="fas fa-check-circle mr-2"></i> REVIEW & ORDER
    </a>

    <?php } elseif($object->is_cashier_user()) { ?>
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="text-white mb-0">Billing Control</h2>
            <p class="text-white-50">Process payments and finalize orders</p>
        </div>
        <div class="col-md-4">
            <div class="cashier-stats glass-card p-3 text-center border-warning">
                <small class="text-white-50 text-uppercase">Orders Pending Payment</small>
                <h2 class="text-warning mb-0" id="pendingCount">0</h2>
            </div>
        </div>
    </div>

    <div class="card glass-card shadow border-0">
        <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
            <h5 class="text-white mb-0"><i class="fas fa-list-ul text-primary me-2"></i> Active Bills</h5>
            <button class="btn btn-sm btn-outline-light" onclick="load_cashier_queue()"><i class="fas fa-sync-alt"></i> Refresh</button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover text-white custom-table" id="billingTable">
                    <thead>
                        <tr class="text-white-50">
                            <th>Order ID</th>
                            <th>Table No.</th>
                            <th>Waiter Name</th>
                            <th>Total Amount</th>
                        </tr>
                    </thead>
                    <tbody id="cashier_billing_list"></tbody>
                </table>
            </div>
        </div>
    </div>

    <?php } else { ?>
    <div class="row">
        <?php 
        $cards = [
            ["Today's Revenue", "primary", "chart-line", $object->Get_total_today_sales(), "Real-time"],
            ["Total Orders", "success", "shopping-bag", $object->Get_total_orders(), "All time"],
            ["Active Tables", "warning", "utensils", $object->Get_total_tables(), "In Use"],
            ["Total Revenue", "info", "wallet", $object->Get_total_sales(), "Cumulative"]
        ];
        foreach($cards as $card) {
            echo '
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card glass-card h-100 border-0 shadow dashboard-card">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-'.$card[1].' text-uppercase mb-1">'.$card[0].'</div>
                                <div class="h2 mb-0 font-weight-bold text-white">'.$card[3].'</div>
                                <small class="text-white-50">'.$card[4].'</small>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-'.$card[2].' fa-2x text-white-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>';
        }
        ?>
    </div>
    <div class="card glass-card mb-4 border-0">
        <div class="card-header bg-transparent border-0">
            <h6 class="m-0 font-weight-bold text-white"><i class="fas fa-th-large mr-2"></i> Live Table Status</h6>
        </div>
        <div class="card-body py-2">
            <div id="admin_table_status" class="table-status-compact"></div>
        </div>
    </div>
    <?php } ?>
</div>

<div class="modal fade" id="cartModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content glass-card-modal text-white">
            <div class="modal-header border-bottom border-secondary">
                <h5 class="modal-title font-weight-bold">Current Cart</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body" id="cart_details_area"></div>
            <div class="modal-footer border-0">
                <a href="order.php?mode=select_table" class="btn btn-success rounded-pill px-4">PROCEED TO TABLE</a>
            </div>
        </div>
    </div>
</div>

<div id="addToCartToast" class="toast glass-card text-white border-0 position-fixed bottom-0 right-0 m-3" style="z-index: 9999;" role="alert" data-delay="2000">
    <div class="toast-body"><i class="fas fa-check-circle text-success mr-2"></i> <span id="toastMessage"></span></div>
</div>

<?php include('footer.php'); ?>

<script>
$(document).ready(function(){
    // Search Functionality
    $("#search_item").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $(".product-card").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

    <?php if($object->is_waiter_user()) { ?>
        // Add to Cart Logic
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
                    $('#toastMessage').text(name + ' added!');
                    $('#addToCartToast').toast('show');
                }
            });
        });

        // Open Cart Modal
        $('#open_cart').click(function(){
            $('#cartModal').modal('show');
            load_cart_data();
        });

        // Load Cart Data Function
        function load_cart_data() {
            $.ajax({
                url: "order_action.php",
                method: "POST",
                data: {action: 'fetch_cart'},
                success: function(data){ $('#cart_details_area').html(data); }
            });
        }

        // Delete Item from Cart (Fixed Action Name)
        $(document).on('click', '.remove_cart_item', function(){
            var id = $(this).data('id');
            if(confirm("Remove this item?")) {
                $.ajax({
                    url: "order_action.php",
                    method: "POST",
                    data: {product_id: id, action: 'remove_cart_item'},
                    dataType: 'json',
                    success: function(data){
                        $('#cart_count').text(data.cart_count);
                        load_cart_data(); // Refresh the list
                    }
                });
            }
        });
    <?php } ?>

    <?php if($object->is_cashier_user()) { ?>
        function load_cashier_queue() {
            $.ajax({
                url: "order_action.php",
                method: "POST",
                data: {action: 'fetch_cashier_queue'},
                success: function(data){ 
                    $('#cashier_billing_list').html(data);
                    var rowCount = $('#cashier_billing_list tr').length;
                    $('#pendingCount').text(rowCount);
                }
            });
        }
        load_cashier_queue();
        setInterval(load_cashier_queue, 5000);
    <?php } ?>
});
</script>

<style>
.glass-card { background: rgba(255, 255, 255, 0.05) !important; backdrop-filter: blur(15px); border: 1px solid rgba(255, 255, 255, 0.1) !important; border-radius: 15px; }
.glass-card-modal { background: #1a1a2e !important; border: 1px solid rgba(255,255,255,0.1); }
.category-pill { padding: 10px 20px; background: rgba(255,255,255,0.1); color: #fff; border-radius: 50px; text-decoration: none !important; transition: 0.3s; }
.category-pill.active { background: #e74c3c; box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3); }
.product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 20px; }
.product-card { background: rgba(255,255,255,0.05); border-radius: 15px; overflow: hidden; position: relative; border: 1px solid rgba(255,255,255,0.05); transition: 0.3s; }
.product-card:hover { transform: translateY(-5px); border-color: rgba(255,255,255,0.2); }
.product-img { width: 100%; height: 150px; object-fit: cover; }
.product-overlay { padding: 10px; background: linear-gradient(transparent, rgba(0,0,0,0.8)); position: absolute; bottom: 0; width: 100%; }
.btn-circle-add { position: absolute; top: 10px; right: 10px; border-radius: 50%; width: 35px; height: 35px; padding: 0; border: none; }
.place-order-btn { position: fixed; bottom: 30px; right: 30px; border-radius: 50px; padding: 15px 30px; z-index: 1000; box-shadow: 0 10px 20px rgba(0,0,0,0.4); }
.custom-table thead th { border: none; font-size: 11px; letter-spacing: 1px; color: #aaa; text-transform: uppercase; }
.custom-table tbody tr { border-bottom: 1px solid rgba(255,255,255,0.05); }
</style>