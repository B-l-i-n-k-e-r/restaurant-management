<?php
// user_dashboard.php
include('rms.php');
$object = new rms();

// Ensure only logged-in users can access
if(!$object->is_login()) {
    header("location:".$object->base_url);
    exit;
}

include('header.php');
?>

<div class="container-fluid">
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <h1 class="h3 mb-0 text-white">Wakanesa Menu</h1>
        <span class="text-white-50"><?php echo date('l, d M Y'); ?></span>
    </div>

    <div class="waiter-grid-container">
        <div class="category-pills mb-4">
            <div class="d-flex flex-wrap gap-2">
                <a href="user_dashboard.php" class="category-pill <?php echo !isset($_GET['cat']) ? 'active' : ''; ?>">
                    <i class="fas fa-fire me-2"></i> All Specialties
                </a>
                <?php
                $object->query = "SELECT * FROM product_category_table WHERE category_status = 'Enable' ORDER BY category_name ASC";
                $categories = $object->get_result();
                foreach($categories as $category) {
                    $active = (isset($_GET['cat']) && $_GET['cat'] == $category['category_id']) ? 'active' : '';
                    echo '<a href="user_dashboard.php?cat='.$category['category_id'].'" class="category-pill '.$active.'">
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

                <button type="button" class="btn btn-warning rounded-pill px-4 shadow-lg ml-md-2" id="open_cart">
                    <i class="fas fa-shopping-basket me-2"></i>
                    Cart (<span id="cart_count"><?php echo $object->Get_cart_count(); ?></span>)
                </button>
            </div>

            <div class="product-grid" id="product_list">
                <?php
                if(!isset($_GET['cat'])) {
                    $object->query = "SELECT * FROM product_table WHERE product_status = 'Enable' ORDER BY product_name ASC";
                } else {
                    $category_id = intval($_GET['cat']);
                    $object->query = "SELECT category_name FROM product_category_table WHERE category_id = '$category_id'";
                    $cat_data = $object->get_result();
                    
                    if(!empty($cat_data)) {
                        $cat_name = $cat_data[0]['category_name'];
                        $object->query = "SELECT * FROM product_table WHERE category_name = '$cat_name' AND product_status = 'Enable' ORDER BY product_name ASC";
                    } else {
                        $object->query = "SELECT * FROM product_table WHERE product_status = 'Enable' ORDER BY product_name ASC";
                    }
                }
                
                $products = $object->get_result();
                if(!empty($products)) {
                    foreach($products as $product) {
                        $img = (!empty($product['product_image']) && file_exists('images/'.$product['product_image'])) 
                               ? 'images/'.$product['product_image'] 
                               : 'img/no-image.png';
                        ?>
                        <div class="product-card">
                            <div class="product-img-container shadow-sm">
                                <img src="<?php echo $img; ?>" class="product-img" loading="lazy">
                                <div class="product-overlay">
                                    <h6 class="text-white mb-0 font-weight-bold"><?php echo $product['product_name']; ?></h6>
                                    <span class="text-warning font-weight-bold"><?php echo $object->cur . ' ' . number_format($product['product_price'], 2); ?></span>
                                </div>
                            </div>
                            <button class="btn btn-warning btn-circle-add add_to_cart" data-id="<?php echo $product['product_id']; ?>" data-name="<?php echo $product['product_name']; ?>">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        <?php
                    }
                } else {
                    echo '<div class="col-12 text-center text-white-50 py-5">No items found in this category.</div>';
                }
                ?>
            </div>
        </div>
    </div>

    <button id="checkout_btn" class="btn btn-success btn-lg shadow-lg place-order-btn">
        <i class="fas fa-utensils mr-2"></i> REVIEW ORDER
    </button>
</div>

<div class="modal fade" id="cartModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content glass-card-modal text-white">
            <div class="modal-header border-bottom border-secondary">
                <h5 class="modal-title font-weight-bold">My Order Cart</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body" id="cart_details_area">
                </div>
            <div class="modal-footer border-0">
                <button type="button" id="final_submit_order" class="btn btn-success btn-block rounded-pill py-3 font-weight-bold">CONFIRM & SEND TO KITCHEN</button>
            </div>
        </div>
    </div>
</div>

<div id="addToCartToast" class="toast glass-card text-white border-0 position-fixed bottom-0 right-0 m-3" style="z-index: 9999;" role="alert" data-delay="2000">
    <div class="toast-body"><i class="fas fa-check-circle text-success mr-2"></i> <span id="toastMessage"></span></div>
</div>

<style>
.glass-card { background: rgba(255, 255, 255, 0.05) !important; backdrop-filter: blur(15px); border: 1px solid rgba(255, 255, 255, 0.1) !important; border-radius: 15px; }
.glass-card-modal { background: #1a1a2e !important; border: 1px solid rgba(255,255,255,0.1); border-radius: 20px; }
.category-pill { padding: 10px 20px; background: rgba(255,255,255,0.05); color: #fff; border-radius: 50px; text-decoration: none !important; transition: 0.3s; margin-right: 5px; display: inline-block;}
.category-pill.active { background: #f39c12; box-shadow: 0 4px 15px rgba(243, 156, 18, 0.3); }
.product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; padding-bottom: 100px; }
.product-card { background: rgba(255,255,255,0.05); border-radius: 15px; overflow: hidden; position: relative; border: 1px solid rgba(255,255,255,0.05); transition: 0.3s; height: 250px; }
.product-card:hover { transform: translateY(-5px); border-color: rgba(255,255,255,0.2); }
.product-img { width: 100%; height: 100%; object-fit: cover; }
.product-overlay { padding: 15px; background: linear-gradient(transparent, rgba(0,0,0,0.9)); position: absolute; bottom: 0; width: 100%; }
.btn-circle-add { position: absolute; top: 10px; right: 10px; border-radius: 50%; width: 40px; height: 40px; border: none; z-index: 5; }
.place-order-btn { position: fixed; bottom: 30px; right: 30px; border-radius: 50px; padding: 15px 40px; z-index: 1000; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
</style>

<script>
$(document).ready(function(){
    // Function to fetch and display cart contents
    function load_cart_data() {
        $.ajax({
            url: "order_action.php",
            method: "POST",
            data: {action: 'fetch_cart'},
            success: function(data){ 
                $('#cart_details_area').html(data); 
            }
        });
    }

    // Add Item to Cart Logic (Aligned with dashboard.php)
    $(document).on('click', '.add_to_cart', function(){
        var id = $(this).data('id');
        var name = $(this).data('name');
        var btn = $(this);
        $.ajax({
            url:"order_action.php",
            method:"POST",
            data:{product_id:id, action:'add'},
            dataType:'json',
            success:function(data){
                // Update Badge Count
                $('#cart_count').text(data.cart_count);
                
                // Show Toast Notification
                $('#toastMessage').text(name + ' added to cart!');
                $('#addToCartToast').toast('show');
                
                // Visual feedback on button
                btn.html('<i class="fas fa-check"></i>').addClass('btn-success').removeClass('btn-warning');
                setTimeout(() => {
                    btn.html('<i class="fas fa-plus"></i>').addClass('btn-warning').removeClass('btn-success');
                }, 1000);
            }
        });
    });

    // Open Cart View
    $('#open_cart, #checkout_btn').click(function(){
        load_cart_data();
        $('#cartModal').modal('show');
    });

    // Remove Item from Cart
    $(document).on('click', '.remove_cart_item', function(){
        var product_id = $(this).data('id');
        $.ajax({
            url:"order_action.php",
            method:"POST",
            data:{product_id:product_id, action:'remove_cart_item'},
            dataType: 'json',
            success:function(data) {
                $('#cart_count').text(data.cart_count);
                load_cart_data(); // Refresh modal content
            }
        });
    });

    // Submit Order
    $('#final_submit_order').click(function(){
        if(confirm("Ready to send this order to the kitchen?")) {
            $.ajax({
                url: "order_action.php",
                method: "POST",
                data: {action: 'submit_customer_order'},
                success: function(data){
                    if(data.trim() == 'success') {
                        window.location.href = 'my_orders.php';
                    } else {
                        alert("Error: " + data);
                    }
                }
            });
        }
    });

    // Search Filter
    $("#search_item").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("#product_list .product-card").filter(function() {
            $(this).toggle($(this).find('h6').text().toLowerCase().indexOf(value) > -1)
        });
    });
});
</script>

<?php include('footer.php'); ?>