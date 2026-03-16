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
        --sky-blue: #0ea5e9; 
        --sky-glow: rgba(14, 165, 233, 0.4);
        --deep-navy: #0f172a;
        --glass: rgba(255, 255, 255, 0.03); 
        --glass-border: rgba(255, 255, 255, 0.1); 
        --accent-green: #22c55e;
    }
    
    body { 
        background-color: var(--deep-navy); 
        color: white; 
        font-family: 'Poppins', sans-serif; 
    }
    
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
    .category-pills { display: flex; flex-wrap: wrap; gap: 12px; }
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
        height: 250px;
    }
    .product-card:hover { transform: translateY(-8px); border-color: var(--sky-blue); box-shadow: 0 10px 30px rgba(0,0,0,0.5), 0 0 15px var(--sky-glow); }
    .product-img { width: 100%; height: 100%; object-fit: cover; transition: 0.5s; }
    .product-card:hover .product-img { transform: scale(1.1); }
    .product-overlay { padding: 15px; background: linear-gradient(transparent, rgba(15, 23, 42, 1)); position: absolute; bottom: 0; width: 100%; }
    
    .btn-circle-add { 
        position: absolute; top: 12px; right: 12px; border-radius: 50%; width: 38px; height: 38px; 
        display: flex; align-items: center; justify-content: center; border: none; 
        background: var(--sky-blue); color: white; box-shadow: 0 0 15px var(--sky-glow); transition: 0.3s;
        z-index: 5;
    }
    .btn-circle-add:hover { transform: rotate(90deg) scale(1.1); background: #fff; color: var(--sky-blue); }

    .popular-badge {
        position: absolute; top: 12px; left: 12px; background: rgba(14, 165, 233, 0.2);
        color: var(--sky-blue); backdrop-filter: blur(5px); padding: 4px 10px; border-radius: 8px;
        font-size: 10px; font-weight: 800; border: 1px solid var(--sky-blue);
        text-transform: uppercase; letter-spacing: 1px; z-index: 2; box-shadow: 0 0 10px var(--sky-glow);
    }

    .tray-nav-btn { 
        position: fixed; bottom: 40px; right: 40px; border-radius: 50px; padding: 15px 35px; z-index: 1000; 
        background: var(--accent-green); border: none; color: white; font-weight: 800; 
        box-shadow: 0 0 25px rgba(34, 197, 94, 0.5); text-transform: uppercase; letter-spacing: 1px;
        cursor: pointer; transition: 0.3s;
    }
    .tray-nav-btn:hover { transform: scale(1.05); box-shadow: 0 0 35px rgba(34, 197, 94, 0.7); }
</style>

<div class="container-fluid mt-4 pb-5">
    <div class="mb-5 d-flex justify-content-between align-items-end">
        <div>
            <h6 class="text-sky-blue font-weight-bold mb-1" style="color: var(--sky-blue); letter-spacing: 2px; text-transform: uppercase;">
                Premium Dining
            </h6>
            <h1 class="h2 mb-0 font-weight-bold text-white">
                <i class="fas fa-utensils mr-2 text-sky-blue"></i>Wakanesa Menu
            </h1>
        </div>
    </div>

    <div class="category-pills mb-4">
        <a href="user_dashboard.php" class="category-pill <?php echo !isset($_GET['cat']) ? 'active' : ''; ?>">
            <i class="fas fa-fire mr-2"></i>All Specials
        </a>
        <?php
        $object->query = "SELECT * FROM product_category_table WHERE category_status = 'Enable' ORDER BY category_name ASC";
        $categories = $object->get_result();
        foreach($categories as $category) {
            $active = (isset($_GET['cat']) && $_GET['cat'] == $category['category_id']) ? 'active' : '';
            echo '<a href="user_dashboard.php?cat='.$category['category_id'].'" class="category-pill '.$active.'">'.strtoupper($category['category_name']).'</a>';
        }
        ?>
    </div>

    <div class="product-area">
        <div class="row mb-5 align-items-center">
            <div class="col-md-5">
                <div class="input-group glass-card border-0 px-3 py-1">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-transparent border-0 text-white-50"><i class="fas fa-search"></i></span>
                    </div>
                    <input type="text" id="search_item" class="form-control bg-transparent text-white border-0" placeholder="Search menu items...">
                </div>
            </div>
        </div>

        <div class="product-grid" id="product_list">
            <?php
            if(!isset($_GET['cat'])) {
                $object->query = "
                    SELECT p.*, COUNT(oi.product_name) as total_orders 
                    FROM product_table p 
                    LEFT JOIN order_item_table oi ON p.product_name = oi.product_name 
                    WHERE p.product_status = 'Enable' 
                    GROUP BY p.product_id 
                    ORDER BY total_orders DESC 
                    LIMIT 12
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
            foreach($products as $product) {
                $img = (!empty($product['product_image']) && file_exists('images/'.$product['product_image'])) ? 'images/'.$product['product_image'] : 'img/no-image.png';
                ?>
                <div class="product-card">
                    <?php if($is_special && isset($product['total_orders']) && $product['total_orders'] > 0): ?>
                        <div class="popular-badge"><i class="fas fa-star mr-1"></i> Top Choice</div>
                    <?php endif; ?>

                    <img src="<?php echo $img; ?>" class="product-img" loading="lazy">
                    <button class="btn-circle-add add_to_cart" data-id="<?php echo $product['product_id']; ?>" data-name="<?php echo $product['product_name']; ?>">
                        <i class="fas fa-plus"></i>
                    </button>
                    <div class="product-overlay">
                        <h6 class="text-white mb-1 font-weight-bold"><?php echo $product['product_name']; ?></h6>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-sky-blue font-weight-bold"><?php echo $object->cur . ' ' . number_format($product['product_price'], 2); ?></span>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>

    <div id="checkout_btn" class="tray-nav-btn">
        <i class="fas fa-shopping-basket mr-2"></i> My Tray (<span id="cart_count_btn"><?php echo $object->Get_cart_count(); ?></span>)
    </div>
</div>

<div class="modal fade" id="cartModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content glass-card-modal text-white">
            <div class="modal-header border-bottom border-secondary px-4 py-4">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-shopping-basket mr-3 text-sky-blue"></i>Review Selections</h5>
                <button type="button" class="close text-white opacity-50" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-4" id="cart_details_area"></div>
            <div class="modal-footer border-0 px-4 pb-4">
                <button type="button" class="btn btn-outline-light rounded-pill px-4" data-dismiss="modal">Add More</button>
                <button type="button" id="final_submit_order" class="btn btn-primary rounded-pill px-5 shadow-lg" style="background: var(--sky-blue); border:none;">
                    CONFIRM ORDER <i class="fas fa-check ml-2"></i>
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
                $('#toastMessage').text(name + ' added');
                $('#addToCartToast').toast('show');
                btn.html('<i class="fas fa-check"></i>').css('background', 'var(--accent-green)');
                setTimeout(() => { btn.html('<i class="fas fa-plus"></i>').css('background', 'var(--sky-blue)'); }, 800);
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
        $(".product-card").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
});
</script>