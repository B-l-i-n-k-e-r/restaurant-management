<?php
include('rms.php');
$object = new rms();

if(!$object->is_login()) {
    header("location:".$object->base_url);
    exit;
}

include('header.php');
?>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-white">
        <?php 
        if($object->is_waiter_user()) {
            echo (!isset($_GET['cat'])) ? 'Popular Items' : 'Menu Category'; 
        } else {
            echo 'Admin Dashboard';
        }
        ?>
    </h1>

    <?php if($object->is_waiter_user()) { ?>
    <div class="waiter-grid-container">
        <div class="product-area">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
                <div class="input-group w-100 w-md-50 mb-2 mb-md-0 search-container">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-transparent border-0 text-white-50"><i class="fas fa-search"></i></span>
                    </div>
                    <input type="text" id="search_item" class="form-control bg-transparent text-white border-0" placeholder="Search Item..." style="box-shadow: none;">
                </div>

                <button type="button" class="btn btn-danger rounded-pill px-4 shadow-lg ml-md-2" id="open_cart">
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
                        GROUP BY p.product_name 
                        ORDER BY total_sales DESC, p.product_name ASC 
                        LIMIT 9
                    ";
                    $products = $object->get_result();
                } else {
                    $category_id = intval($_GET['cat']);
                    $object->query = "SELECT category_name FROM product_category_table WHERE category_id = '$category_id'";
                    $cat_data = $object->get_result();
                    $products = array();
                    if(!empty($cat_data)) {
                        $c_name = $cat_data[0]['category_name'];
                        $object->query = "SELECT * FROM product_table WHERE category_name = '$c_name' AND product_status = 'Enable' ORDER BY product_name ASC";
                        $products = $object->get_result();
                    }
                }

                if(!empty($products)) {
                    foreach($products as $product) {
                        $img = (!empty($product['product_image'])) ? 'images/'.$product['product_image'] : 'images/placeholder.png';
                        $isPopularView = !isset($_GET['cat']);
                        echo '
                        <div class="product-card">
                            <div class="product-img-container shadow-sm">
                                 <img src="'.$img.'" class="product-img" onerror="this.src=\'images/placeholder.png\'">
                                 <div class="product-overlay">
                                    <h6 class="text-white mb-0 font-weight-bold">'.$product['product_name'].'</h6>
                                    <span class="text-success font-weight-bold small">'.$object->cur.' '.$product['product_price'].'</span>
                                 </div>
                                 '.($isPopularView ? '<div class="popular-badge"><i class="fas fa-fire"></i></div>' : '').'
                            </div>
                            <button class="btn btn-success btn-circle-add add_to_cart" data-id="'.$product['product_id'].'">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>';
                    }
                }
                ?>
            </div>
        </div>
    </div>

    <a href="order.php?mode=select_table" class="btn btn-success btn-lg shadow-lg place-order-btn">
        <i class="fas fa-plus-circle mr-2"></i> PLACE ORDER
    </a>

    <div class="modal fade" id="cartModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content glass-card text-white">
                <div class="modal-header border-0">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-shopping-cart mr-2 text-danger"></i> Current Order</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div id="cart_details_area"></div>
                </div>
                <div class="modal-footer border-0">
                    <a href="order.php?mode=select_table" class="btn btn-success btn-block rounded-pill font-weight-bold py-2 shadow">PROCEED TO TABLE SELECTION</a>
                </div>
            </div>
        </div>
    </div>

    <?php } else { ?>
    <div class="row">
        <?php 
        $cards = [
            ["Today Sales", "primary", "chart-line", $object->Get_total_today_sales()],
            ["Yesterday Sales", "success", "history", $object->Get_total_yesterday_sales()],
            ["Active Tables", "warning", "utensils", $object->Get_total_tables()]
        ];

        foreach($cards as $card) {
            echo '
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card glass-card h-100 border-0 shadow">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-'.$card[1].' text-uppercase mb-1">'.$card[0].'</div>
                                <div class="h5 mb-0 font-weight-bold text-white">'.$card[3].'</div>
                            </div>
                            <div class="col-auto"><i class="fas fa-'.$card[2].' fa-2x text-white-50"></i></div>
                        </div>
                    </div>
                </div>
            </div>';
        }
        ?>
    </div>
    <div class="card glass-card mb-4 mt-4">
        <div class="card-header bg-transparent border-0"><h6 class="m-0 font-weight-bold text-white">Live Table Status</h6></div>
        <div class="card-body"><div id="admin_table_status"></div></div>
    </div>
    <?php } ?>
</div>

<?php include('footer.php'); ?>

<script>
$(document).ready(function(){
    <?php if($object->is_waiter_user()) { ?>
        // Function to update cart count from server
        function updateCartCount() {
            $.ajax({
                url: "order_action.php",
                method: "POST",
                data: {action: 'get_cart_count'},
                dataType: 'json',
                success: function(data) {
                    $('#cart_count').text(data.cart_count);
                }
            });
        }

        // Search
        $("#search_item").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            $(".product-card").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });

        // Add to Cart
        $(document).on('click', '.add_to_cart', function(){
            var product_id = $(this).data('id');
            var btn = $(this);
            btn.find('i').addClass('fa-spinner fa-spin').removeClass('fa-plus');
            $.ajax({
                url:"order_action.php",
                method:"POST",
                data:{product_id:product_id, action:'add'},
                dataType:'json',
                success:function(data){
                    btn.find('i').removeClass('fa-spinner fa-spin').addClass('fa-plus');
                    if(data.cart_count !== undefined) $('#cart_count').text(data.cart_count);
                }
            });
        });

        // Open Cart
        $('#open_cart').click(function(){
            $('#cartModal').modal('show');
            $.ajax({
                url: "order_action.php",
                method: "POST",
                data: {action: 'fetch_cart'},
                success: function(data){ 
                    $('#cart_details_area').html(data); 
                }
            });
        });

        // Remove from Cart
        $(document).on('click', '.remove_cart_item', function(){
            var product_id = $(this).data('id');
            $.ajax({
                url: "order_action.php",
                method: "POST",
                data: {product_id: product_id, action: 'remove_cart_item'},
                dataType: 'json',
                success: function(data){
                    $('#cart_count').text(data.cart_count);
                    // Refresh cart modal content
                    $.ajax({
                        url: "order_action.php",
                        method: "POST",
                        data: {action: 'fetch_cart'},
                        success: function(cartData){ 
                            $('#cart_details_area').html(cartData); 
                        }
                    });
                }
            });
        });

        // Check cart count when page loads and when returning from order.php
        updateCartCount();
        
        // Also update cart count when page becomes visible (user returns from order.php)
        $(document).on('visibilitychange', function() {
            if (!document.hidden) {
                updateCartCount();
            }
        });

    <?php } else { ?>
        function load_admin_status() {
            $.ajax({ 
                url:"order_action.php", 
                method:"POST", 
                data:{action:'reset'}, 
                success:function(data){ 
                    $('#admin_table_status').html(data); 
                } 
            });
        }
        load_admin_status();
        setInterval(load_admin_status, 5000);
    <?php } ?>
});
</script>

<style>
.search-container { background: rgba(255,255,255,0.1); backdrop-filter: blur(15px); border-radius: 50px; border: 1px solid rgba(255,255,255,0.1); }
.glass-card { background: rgba(255,255,255,0.05) !important; backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.1) !important; border-radius: 15px; }
.product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 20px; }
.product-card { position: relative; transition: transform 0.2s; }
.product-card:hover { transform: translateY(-5px); }
.product-img-container { position: relative; overflow: hidden; border-radius: 15px; aspect-ratio: 1/1; border: 1px solid rgba(255,255,255,0.1); }
.product-img { width: 100%; height: 100%; object-fit: cover; }
.product-overlay { position: absolute; bottom: 0; width: 100%; padding: 10px; background: linear-gradient(transparent, rgba(0,0,0,0.9)); }
.btn-circle-add { position: absolute; bottom: -5px; right: -5px; width: 38px; height: 38px; border-radius: 50%; z-index: 5; }
.place-order-btn { position:fixed; bottom:30px; right:30px; border-radius:50px; padding:12px 30px; z-index:999; background: #1cc88a; border: none; font-weight: 700; color: white; }
</style>