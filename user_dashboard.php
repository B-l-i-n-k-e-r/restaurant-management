<?php
// user_dashboard.php
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
        --wakanesa-gold: #f39c12;
        --neon-blue: #00d2ff;
        --glass-bg: rgba(255, 255, 255, 0.03);
        --glass-border: rgba(255, 255, 255, 0.1);
        --neon-glow: 0 0 15px rgba(0, 210, 255, 0.3);
    }

    /* Column fit-content constraint */
    .fit-content { width: 1% !important; white-space: nowrap !important; }

    body {
        background: #06070a;
        background-image: 
            radial-gradient(circle at 0% 0%, rgba(243, 156, 18, 0.05) 0%, transparent 50%),
            radial-gradient(circle at 100% 100%, rgba(0, 210, 255, 0.05) 0%, transparent 50%);
        color: #fff;
        font-family: 'Inter', sans-serif;
    }

    .category-container {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 30px;
    }

    .category-pill { 
        padding: 10px 20px; 
        background: rgba(255, 255, 255, 0.05); 
        color: #fff; 
        border-radius: 12px; 
        text-decoration: none !important; 
        transition: 0.3s all ease; 
        border: 1px solid var(--glass-border);
        font-weight: 600;
        font-size: 0.9rem;
        letter-spacing: 0.5px;
    }

    .category-pill:hover {
        border-color: var(--neon-blue);
        box-shadow: var(--neon-glow);
        transform: translateY(-2px);
        color: #fff;
    }

    .category-pill.active { 
        background: linear-gradient(135deg, var(--wakanesa-gold), #e67e22); 
        border-color: var(--wakanesa-gold);
        box-shadow: 0 4px 15px rgba(243, 156, 18, 0.4); 
        color: #000;
    }

    .search-wrapper {
        background: var(--glass-bg);
        border: 1px solid var(--glass-border);
        border-radius: 15px;
        padding: 5px 15px;
        transition: 0.3s;
    }
    .search-wrapper:focus-within { border-color: var(--neon-blue); box-shadow: var(--neon-glow); }

    .product-grid { 
        display: grid; 
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); 
        gap: 20px; 
        padding-bottom: 120px; 
    }

    .product-card { 
        background: rgba(255,255,255,0.02); 
        border-radius: 20px; 
        overflow: hidden; 
        position: relative; 
        border: 1px solid var(--glass-border); 
        transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); 
        height: 250px; 
    }

    .product-card:hover { 
        transform: translateY(-8px); 
        border-color: var(--neon-blue);
        box-shadow: 0 10px 30px rgba(0,0,0,0.5), var(--neon-glow);
    }
    
    .product-img { width: 100%; height: 100%; object-fit: cover; }
    
    .product-info-overlay { 
        padding: 15px; 
        background: linear-gradient(transparent, rgba(0,0,0,0.9)); 
        position: absolute; 
        bottom: 0; 
        width: 100%; 
    }

    .popular-badge {
        position: absolute;
        top: 10px;
        left: 10px;
        background: rgba(0, 210, 255, 0.2);
        color: var(--neon-blue);
        backdrop-filter: blur(5px);
        padding: 4px 10px;
        border-radius: 8px;
        font-size: 10px;
        font-weight: 800;
        border: 1px solid var(--neon-blue);
        text-transform: uppercase;
        letter-spacing: 1px;
        z-index: 2;
        box-shadow: var(--neon-glow);
    }

    .btn-add-neon { 
        position: absolute; 
        top: 10px; 
        right: 10px; 
        border-radius: 10px;
        width: 38px; 
        height: 38px; 
        border: none; 
        background: var(--wakanesa-gold);
        color: #000;
        font-weight: bold;
        transition: 0.3s;
        z-index: 3;
    }

    .bottom-nav { 
        position: fixed; 
        bottom: 25px; 
        left: 50%;
        transform: translateX(-50%);
        width: 90%;
        max-width: 400px;
        background: linear-gradient(90deg, #111, #222);
        border: 1px solid var(--neon-blue);
        border-radius: 20px;
        padding: 18px;
        z-index: 1000;
        box-shadow: 0 10px 40px rgba(0,0,0,0.8), var(--neon-glow);
        color: var(--neon-blue);
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 2px;
        display: flex;
        justify-content: center;
        align-items: center;
        cursor: pointer;
    }

    .glass-modal { background: #0a0c10 !important; border: 1px solid var(--neon-blue); border-radius: 20px; }
</style>

<div class="container-fluid pt-5 px-4 px-md-5">
    <div class="mb-5">
        <h1 class="font-weight-bold text-white mb-0" style="font-size: 2.5rem; letter-spacing: -1px;">
            WAKANESA <span style="color: var(--neon-blue); text-shadow: var(--neon-glow);">MENU</span>
        </h1>
        <p class="text-white-50 small font-weight-bold">EST. 2026 • PREMIUM DINING EXPERIENCE</p>
    </div>

    <div class="category-container">
        <a href="user_dashboard.php" class="category-pill <?php echo !isset($_GET['cat']) ? 'active' : ''; ?>">
            <i class="fas fa-fire mr-2"></i> ALL SPECIAL
        </a>
        <?php
        $object->query = "SELECT * FROM product_category_table WHERE category_status = 'Enable' ORDER BY category_name ASC";
        $categories = $object->get_result();
        foreach($categories as $category) {
            $active = (isset($_GET['cat']) && $_GET['cat'] == $category['category_id']) ? 'active' : '';
            echo '<a href="user_dashboard.php?cat='.$category['category_id'].'" class="category-pill '.$active.'">
                    '.strtoupper($category['category_name']).'
                  </a>';
        }
        ?>
    </div>

    <div class="product-area">
        <div class="search-wrapper mb-5 d-flex align-items-center">
            <i class="fas fa-search text-white-50 mr-3"></i>
            <input type="text" id="search_item" class="form-control bg-transparent border-0 text-white" placeholder="Search flavors..." style="box-shadow: none;">
        </div>

        <div class="product-grid" id="product_list">
            <?php
            // Fixed query joining on product_name since product_id is missing in order_item_table
            if(!isset($_GET['cat'])) {
                $object->query = "
                    SELECT p.*, COUNT(oi.product_name) as total_orders 
                    FROM product_table p 
                    LEFT JOIN order_item_table oi ON p.product_name = oi.product_name 
                    WHERE p.product_status = 'Enable' 
                    GROUP BY p.product_id 
                    ORDER BY total_orders DESC 
                    LIMIT 5
                ";
                $is_special = true;
            } else {
                $category_id = intval($_GET['cat']);
                $object->query = "SELECT category_name FROM product_category_table WHERE category_id = '$category_id'";
                $cat_data = $object->get_result();
                $cat_name = !empty($cat_data) ? $cat_data[0]['category_name'] : '';
                $object->query = "SELECT * FROM product_table WHERE category_name = '$cat_name' AND product_status = 'Enable' ORDER BY product_name ASC";
                $is_special = false;
            }
            
            $products = $object->get_result();
            if(!empty($products)) {
                foreach($products as $product) {
                    $img = (!empty($product['product_image']) && file_exists('images/'.$product['product_image'])) 
                           ? 'images/'.$product['product_image'] 
                           : 'img/no-image.png';
                    ?>
                    <div class="product-card">
                        <?php if($is_special && isset($product['total_orders']) && $product['total_orders'] > 0): ?>
                            <div class="popular-badge"><i class="fas fa-star mr-1"></i> TOP CHOICE</div>
                        <?php endif; ?>
                        
                        <img src="<?php echo $img; ?>" class="product-img" loading="lazy">
                        <div class="product-info-overlay">
                            <h6 class="text-white mb-0 font-weight-bold small"><?php echo $product['product_name']; ?></h6>
                            <span style="color: var(--wakanesa-gold); font-weight: 800;">
                                <?php echo $object->cur . ' ' . number_format($product['product_price'], 2); ?>
                            </span>
                        </div>
                        <button class="btn-add-neon add_to_cart" data-id="<?php echo $product['product_id']; ?>" data-name="<?php echo $product['product_name']; ?>">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                    <?php
                }
            }
            ?>
        </div>
    </div>

    <div id="checkout_btn" class="bottom-nav">
        <i class="fas fa-shopping-basket mr-3"></i> MY TRAY (<span id="cart_count_btn"><?php echo $object->Get_cart_count(); ?></span>)
    </div>
</div>

<div class="modal fade" id="cartModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content glass-modal text-white">
            <div class="modal-header border-bottom border-secondary">
                <h5 class="modal-title font-weight-bold">REVIEW SELECTIONS</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-0" id="cart_details_area"></div>
            <div class="modal-footer border-0 p-4">
                <button type="button" id="final_submit_order" class="btn btn-block rounded-pill py-3 font-weight-bold" style="background: var(--neon-blue); color: #000; box-shadow: var(--neon-glow);">CONFIRM ORDER</button>
            </div>
        </div>
    </div>
</div>

<div id="addToCartToast" class="toast glass-card text-white border-0 position-fixed bottom-0 right-0 m-4" style="z-index: 9999; border-left: 4px solid var(--neon-blue) !important;" role="alert" data-delay="1500">
    <div class="toast-body"><i class="fas fa-check-circle text-info mr-2"></i> <span id="toastMessage"></span></div>
</div>

<script>
$(document).ready(function(){
    function load_cart_data() {
        $.ajax({
            url: "order_action.php",
            method: "POST",
            data: {action: 'fetch_cart'},
            success: function(data){ $('#cart_details_area').html(data); }
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
                btn.html('<i class="fas fa-check"></i>').css('background', 'var(--neon-blue)');
                setTimeout(() => { btn.html('<i class="fas fa-plus"></i>').css('background', 'var(--wakanesa-gold)'); }, 800);
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
        if(confirm("Confirm this order?")) {
            $.ajax({
                url: "order_action.php",
                method: "POST",
                data: {action: 'submit_customer_order'},
                success: function(data){
                    if(data.trim() == 'success') { window.location.href = 'my_orders.php'; }
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