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

<style>
    /* Theme Variables */
    :root {
        --wakanesa-gold: #f39c12;
        --glass-bg: rgba(255, 255, 255, 0.05);
        --glass-border: rgba(255, 255, 255, 0.1);
    }

    /* Layout Constraints */
    .fit-content { width: 1% !important; white-space: nowrap !important; }

    .glass-card { 
        background: var(--glass-bg) !important; 
        backdrop-filter: blur(15px); 
        border: 1px solid var(--glass-border) !important; 
        border-radius: 15px; 
    }

    .glass-card-modal { 
        background: #1a1a2e !important; 
        border: 1px solid var(--glass-border); 
        border-radius: 20px; 
    }

    .category-pill { 
        padding: 10px 20px; 
        background: var(--glass-bg); 
        color: #fff; 
        border-radius: 50px; 
        text-decoration: none !important; 
        transition: 0.3s; 
        border: 1px solid var(--glass-border);
        white-space: nowrap;
    }

    .category-pill.active { 
        background: var(--wakanesa-gold); 
        border-color: var(--wakanesa-gold);
        box-shadow: 0 4px 15px rgba(243, 156, 18, 0.3); 
        color: #000;
        font-weight: bold;
    }

    /* Optimized Grid for all screens */
    .product-grid { 
        display: grid; 
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); 
        gap: 15px; 
        padding-bottom: 120px; 
    }

    .product-card { 
        background: var(--glass-bg); 
        border-radius: 15px; 
        overflow: hidden; 
        position: relative; 
        border: 1px solid var(--glass-border); 
        transition: 0.3s; 
        height: 220px; 
    }

    .product-card:hover { transform: translateY(-5px); border-color: rgba(255,255,255,0.2); }
    
    .product-img { width: 100%; height: 100%; object-fit: cover; }
    
    .product-overlay { 
        padding: 12px; 
        background: linear-gradient(transparent, rgba(0,0,0,0.9)); 
        position: absolute; 
        bottom: 0; 
        width: 100%; 
    }

    .btn-circle-add { 
        position: absolute; 
        top: 10px; 
        right: 10px; 
        border-radius: 50%; 
        width: 38px; 
        height: 38px; 
        border: none; 
        z-index: 5; 
        box-shadow: 0 4px 10px rgba(0,0,0,0.3);
    }

    .place-order-btn { 
        position: fixed; 
        bottom: 30px; 
        left: 50%;
        transform: translateX(-50%);
        width: 90%;
        max-width: 400px;
        border-radius: 50px; 
        padding: 15px; 
        z-index: 1000; 
        box-shadow: 0 10px 30px rgba(0,0,0,0.5); 
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    /* Cart Table Polish */
    #cart_details_area table { color: white; }
    #cart_details_area .table td, #cart_details_area .table th { border-color: var(--glass-border); }
</style>

<div class="container-fluid pt-3">
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <h1 class="h4 mb-0 text-white font-weight-bold">Wakanesa Menu</h1>
        <span class="badge glass-card py-2 px-3 text-white-50 font-weight-normal"><?php echo date('D, d M'); ?></span>
    </div>

    <div class="category-pills-container mb-4" style="overflow-x: auto; white-space: nowrap; -webkit-overflow-scrolling: touch; padding-bottom: 5px;">
        <div class="d-inline-flex gap-2">
            <a href="user_dashboard.php" class="category-pill <?php echo !isset($_GET['cat']) ? 'active' : ''; ?>">
                <i class="fas fa-star mr-2"></i>All Specials
            </a>
            <?php
            $object->query = "SELECT * FROM product_category_table WHERE category_status = 'Enable' ORDER BY category_name ASC";
            $categories = $object->get_result();
            foreach($categories as $category) {
                $active = (isset($_GET['cat']) && $_GET['cat'] == $category['category_id']) ? 'active' : '';
                echo '<a href="user_dashboard.php?cat='.$category['category_id'].'" class="category-pill '.$active.'">
                        '.$category['category_name'].'
                      </a>';
            }
            ?>
        </div>
    </div>

    <div class="product-area">
        <div class="row mb-4">
            <div class="col-12">
                <div class="input-group search-container glass-card py-1 px-2">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-transparent border-0 text-white-50"><i class="fas fa-search"></i></span>
                    </div>
                    <input type="text" id="search_item" class="form-control bg-transparent text-white border-0" placeholder="Craving something specific?">
                </div>
            </div>
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
                        <img src="<?php echo $img; ?>" class="product-img" loading="lazy">
                        <div class="product-overlay">
                            <h6 class="text-white mb-0 font-weight-bold small"><?php echo $product['product_name']; ?></h6>
                            <span class="text-warning font-weight-bold"><?php echo $object->cur . ' ' . number_format($product['product_price'], 2); ?></span>
                        </div>
                        <button class="btn btn-warning btn-circle-add add_to_cart" data-id="<?php echo $product['product_id']; ?>" data-name="<?php echo $product['product_name']; ?>">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                    <?php
                }
            } else {
                echo '<div class="col-12 text-center text-white-50 py-5"><i class="fas fa-utensils fa-3x mb-3"></i><br>No items in this category yet.</div>';
            }
            ?>
        </div>
    </div>

    <button id="checkout_btn" class="btn btn-warning btn-lg shadow-lg place-order-btn">
        <i class="fas fa-shopping-basket mr-2"></i> VIEW CART (<span id="cart_count_btn"><?php echo $object->Get_cart_count(); ?></span>)
    </button>
</div>

<div class="modal fade" id="cartModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content glass-card-modal text-white">
            <div class="modal-header border-bottom border-secondary">
                <h5 class="modal-title font-weight-bold">My Order Cart</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-0" id="cart_details_area">
                </div>
            <div class="modal-footer border-0">
                <button type="button" id="final_submit_order" class="btn btn-success btn-block rounded-pill py-3 font-weight-bold">CONFIRM & SEND TO KITCHEN</button>
            </div>
        </div>
    </div>
</div>

<div id="addToCartToast" class="toast glass-card text-white border-0 position-fixed bottom-0 right-0 m-3" style="z-index: 9999;" role="alert" data-delay="1500">
    <div class="toast-body"><i class="fas fa-check-circle text-success mr-2"></i> <span id="toastMessage"></span></div>
</div>

<script>
$(document).ready(function(){
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
                $('#cart_count_btn').text(data.cart_count);
                $('#toastMessage').text(name + ' added!');
                $('#addToCartToast').toast('show');
                
                btn.html('<i class="fas fa-check"></i>').addClass('btn-success').removeClass('btn-warning');
                setTimeout(() => {
                    btn.html('<i class="fas fa-plus"></i>').addClass('btn-warning').removeClass('btn-success');
                }, 800);
            }
        });
    });

    $('#checkout_btn').click(function(){
        load_cart_data();
        $('#cartModal').modal('show');
    });

    $(document).on('click', '.remove_cart_item', function(){
        var product_id = $(this).data('id');
        $.ajax({
            url:"order_action.php",
            method:"POST",
            data:{product_id:product_id, action:'remove_cart_item'},
            dataType: 'json',
            success:function(data) {
                $('#cart_count_btn').text(data.cart_count);
                load_cart_data();
            }
        });
    });

    $('#final_submit_order').click(function(){
        if(confirm("Confirm your order? It will be sent straight to our chefs!")) {
            $.ajax({
                url: "order_action.php",
                method: "POST",
                data: {action: 'submit_customer_order'},
                success: function(data){
                    if(data.trim() == 'success') {
                        window.location.href = 'my_orders.php';
                    } else {
                        alert("We couldn't process the order: " + data);
                    }
                }
            });
        }
    });

    $("#search_item").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("#product_list .product-card").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
});
</script>

<?php include('footer.php'); ?>