<?php
// kitchen_orders.php
include('rms.php');
$object = new rms();

if(!$object->is_login()) {
    header("location:".$object->base_url."");
    exit;
}

if($_SESSION['user_type'] != 'Kitchen' && $_SESSION['user_type'] != 'Master') {
    header("location:".$object->base_url."dashboard.php");
    exit;
}

include('header.php');
?>

<style>
    :root {
        --neon-blue: #00d2ff;
        --neon-purple: #9d50bb;
        --cyber-black: #050608;
        --glass-bg: rgba(255, 255, 255, 0.02);
        --neon-gradient: linear-gradient(135deg, var(--neon-blue), var(--neon-purple));
    }

    body {
        background: radial-gradient(circle at top right, #0d1117, var(--cyber-black));
        color: #fff;
        font-family: 'Inter', -apple-system, sans-serif;
        overflow-x: hidden;
    }

    #content-wrapper { background: transparent; }

    /* HUD Title Section */
    .kds-header-border {
        border-left: 5px solid;
        border-image: linear-gradient(to bottom, var(--neon-blue), var(--neon-purple)) 1;
        padding-left: 20px;
    }

    .display-5 {
        background: var(--neon-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-weight: 900 !important;
        letter-spacing: -1px;
    }

    /* Filter Pills - Glowing Style */
    .filter-pills-container {
        display: flex;
        gap: 12px;
        margin-bottom: 30px;
    }

    .btn-filter {
        border-radius: 50px;
        padding: 10px 22px;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.7rem;
        letter-spacing: 1px;
        border: 1px solid rgba(0, 210, 255, 0.2);
        background: rgba(0, 210, 255, 0.05);
        color: var(--neon-blue);
        transition: 0.3s all ease;
    }

    .btn-filter.active {
        background: var(--neon-gradient);
        border-color: transparent;
        color: #fff;
        box-shadow: 0 0 20px rgba(157, 80, 187, 0.4);
    }

    /* Grid Layout: Masonry content-fit */
    .kitchen-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(330px, 1fr));
        grid-auto-rows: min-content;
        gap: 25px;
        align-items: start;
    }

    /* Ticket Card - Cyber Glass */
    .ticket-card {
        background: rgba(13, 14, 18, 0.9);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        border: 1px solid rgba(255, 255, 255, 0.08);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        height: fit-content; 
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.6);
        position: relative;
    }

    .ticket-card::before {
        content: "";
        position: absolute;
        top: 0; left: 0; width: 100%; height: 3px;
        background: var(--neon-gradient);
    }

    .ticket-header {
        padding: 20px;
        background: rgba(255, 255, 255, 0.02);
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }

    .order-id { color: var(--neon-blue); font-weight: 800; font-family: monospace; font-size: 1.2rem; }
    .table-label { font-weight: 800; color: #fff; text-shadow: 0 0 10px rgba(255,255,255,0.2); }

    .ticket-info {
        padding: 10px 20px;
        background: rgba(0, 0, 0, 0.3);
        display: flex;
        justify-content: space-between;
        font-size: 0.8rem;
        color: #888;
    }

    .time-badge { color: var(--neon-purple); font-weight: 800; }

    .ticket-body {
        padding: 20px;
    }

    .order-item {
        font-size: 1.15rem;
        margin-bottom: 12px;
        display: flex;
        align-items: flex-start;
        padding-bottom: 10px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.03);
    }

    .item-qty { 
        background: rgba(157, 80, 187, 0.2); 
        color: var(--neon-purple); 
        min-width: 30px; 
        height: 30px; 
        display: flex; 
        align-items: center; 
        justify-content: center; 
        border-radius: 8px; 
        font-weight: 900; 
        margin-right: 15px;
    }

    /* HUD Counter Circle */
    .queue-box {
        text-align: right;
        border-right: 4px solid var(--neon-purple);
        padding-right: 20px;
    }

    /* Buttons */
    .btn-status {
        width: 100%;
        padding: 18px;
        border: none;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 2px;
        transition: 0.3s;
        cursor: pointer;
        background: rgba(255,255,255,0.03);
        color: #fff;
    }

    .btn-start { border-top: 1px solid var(--neon-blue); color: var(--neon-blue); }
    .btn-start:hover { background: var(--neon-blue); color: #000; box-shadow: 0 0 20px rgba(0, 210, 255, 0.4); }
    
    .btn-ready { border-top: 1px solid var(--neon-purple); color: var(--neon-purple); }
    .btn-ready:hover { background: var(--neon-purple); color: #fff; box-shadow: 0 0 20px rgba(157, 80, 187, 0.4); }

    /* Search */
    .search-input {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(0, 210, 255, 0.2);
        border-radius: 15px;
        padding: 16px 20px 16px 50px;
        color: #fff;
        width: 100%;
        transition: 0.3s;
    }

    .search-input:focus {
        border-color: var(--neon-purple);
        background: rgba(255, 255, 255, 0.06);
        outline: none;
        box-shadow: 0 0 15px rgba(157, 80, 187, 0.2);
    }
</style>

<div class="container-fluid py-5 px-md-5">
    <div class="row align-items-center mb-5">
        <div class="col-md-8">
            <div class="kds-header-border">
                <h1 class="display-5 mb-0">KITCHEN DISPLAY</h1>
                <p class="text-white-50 mb-0">
                    <span id="sync_status" style="color: var(--neon-blue); font-weight: bold;">LIVE UPDATES</span> 
                    • <?php echo date('l, d M'); ?>
                </p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="queue-box">
                <div class="small font-weight-bold text-muted text-uppercase">Active Orders</div>
                <div id="active_count_text" style="font-size: 3.5rem; font-weight: 900; color: #fff; line-height: 1;">0</div>
            </div>
        </div>
    </div>

    <div class="filter-pills-container">
        <button class="btn-filter active" data-filter="All">All ACTIVE</button>
        <button class="btn-filter" data-filter="In Process">NEW ORDERS</button>
        <button class="btn-filter" data-filter="Preparing">PREPARING</button>
        <button class="btn-filter" data-filter="Ready">READY</button>
    </div>

    <div class="search-container">
        <i class="fas fa-search search-icon" style="position: absolute; left: 20px; top: 20px; color: var(--neon-blue);"></i>
        <input type="text" id="search_orders" class="search-input" placeholder="Filter by Table or Order ID...">
    </div>

    <div class="kitchen-container mt-4" id="kitchen_load">
        </div>
</div>

<audio id="bell" preload="auto">
    <source src="https://assets.mixkit.co/active_storage/sfx/2358/2358-preview.mp3" type="audio/mpeg">
</audio>

<script>
$(document).ready(function(){
    let currentOrderCount = 0;
    let activeFilter = 'All';

    function fetch_kitchen_orders() {
        let searchText = $('#search_orders').val();
        $.ajax({
            url: "order_action.php",
            method: "POST",
            data: { 
                action: 'fetch_kitchen_grid', 
                filter: activeFilter,
                search: searchText,
                view: 'full_display'
            },
            success: function(data) {
                $('#kitchen_load').html(data);
                let newCount = $('.ticket-card').length;
                $('#active_count_text').text(newCount);

                if(newCount > currentOrderCount && currentOrderCount !== 0) {
                    document.getElementById('bell').play().catch(e => {});
                }
                currentOrderCount = newCount;
            }
        });
    }

    $('.btn-filter').click(function(){
        $('.btn-filter').removeClass('active');
        $(this).addClass('active');
        activeFilter = $(this).data('filter');
        fetch_kitchen_orders();
    });

    $('#search_orders').on('input', function() {
        fetch_kitchen_orders();
    });

    setInterval(fetch_kitchen_orders, 8000);
    fetch_kitchen_orders();
});
</script>

<?php include('footer.php'); ?>