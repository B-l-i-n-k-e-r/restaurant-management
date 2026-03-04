<?php
// kitchen_dashboard.php
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
        --sidebar-width: 250px;
        --card-bg: rgba(26, 26, 26, 0.95);
        --glass-border: rgba(255, 255, 255, 0.1);
        --accent-orange: #ff5e3a;
        --text-muted: #888;
    }

    body {
        background: #000 url('your-background-image.jpg') no-repeat center center fixed;
        background-size: cover;
        color: #fff;
        font-family: 'Segoe UI', Roboto, sans-serif;
    }

    .main-content {
        margin-left: var(--sidebar-width);
        padding: 40px;
    }

    /* Search Bar from Screenshot */
    .search-container {
        max-width: 500px;
        margin-bottom: 40px;
    }

    .search-box {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid var(--glass-border);
        border-radius: 12px;
        padding: 15px 25px;
        width: 100%;
        color: #fff;
        font-size: 1.1rem;
        outline: none;
    }

    /* Ticket Grid - Content Fit */
    .ticket-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(380px, 1fr)); 
        grid-auto-rows: min-content; 
        gap: 25px;
    }

    /* Matching your Screenshot UI */
    .ticket-card {
        background: var(--card-bg);
        border-radius: 15px;
        overflow: hidden;
        border: 1px solid var(--glass-border);
        display: flex;
        flex-direction: column;
        height: fit-content; /* Ensure columns fit content regardless of size */
    }

    .ticket-header {
        padding: 15px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid rgba(255,255,255,0.05);
    }

    .order-id { font-weight: 600; font-size: 1rem; color: #ddd; }
    .table-name { font-weight: 500; font-size: 1rem; color: #fff; }

    .ticket-meta {
        padding: 10px 20px;
        display: flex;
        justify-content: space-between;
        font-size: 0.85rem;
        text-transform: uppercase;
    }

    .customer-name { color: var(--text-muted); letter-spacing: 1px; }
    .time-ago { color: var(--accent-orange); font-weight: 700; }

    .ticket-body {
        padding: 20px;
        min-height: 120px;
        border-top: 1px solid rgba(255,255,255,0.05);
    }

    .order-item {
        font-size: 1.2rem;
        margin-bottom: 8px;
        color: #eee;
        display: flex;
        gap: 10px;
    }

    .item-qty {
        color: var(--accent-orange);
        font-weight: bold;
    }

    .pending-counter {
        float: right;
        text-align: right;
    }
    
    .counter-value {
        font-size: 3.5rem;
        font-weight: 800;
        line-height: 1;
    }
</style>

<div class="main-content">
    <div class="row mb-2">
        <div class="col-8">
            <h1 class="font-weight-bold">Kitchen Dashboard</h1>
            <h5 class="text-white-50"><?php echo date('l, d M Y'); ?></h5>
        </div>
        <div class="col-4 text-right">
            <div class="pending-counter">
                <span class="text-white-50 font-weight-bold small">PENDING ORDERS:</span>
                <div id="active_count" class="counter-value">0</div>
            </div>
        </div>
    </div>

    <div class="search-container">
        <input type="text" id="search_ticket" class="search-box" placeholder="Search Table # or Order ID...">
    </div>

    <div class="ticket-grid" id="kitchen_order_display">
        </div>
</div>

<audio id="order_bell" preload="auto">
    <source src="https://assets.mixkit.co/active_storage/sfx/2358/2358-preview.mp3" type="audio/mpeg">
</audio>

<script>
$(document).ready(function(){
    let lastOrderCount = 0;

    /**
     * Primary load function. 
     * Uses 'origin: dashboard' to trigger the button-hiding logic in order_action.php
     */
    function load_orders() {
        let search = $('#search_ticket').val();
        $.ajax({
            url: "order_action.php",
            method: "POST",
            data: { 
                action: 'fetch_kitchen_grid', 
                search: search,
                filter: 'All',
                origin: 'dashboard' 
            },
            success: function(data) {
                $('#kitchen_order_display').html(data);
                
                // Calculate current visible tickets for the counter
                let count = $('.ticket-card').length;
                $('#active_count').text(count);

                // Alert sound for new orders
                if(count > lastOrderCount && lastOrderCount !== 0) {
                    document.getElementById('order_bell').play().catch(e => {
                        console.log("Audio play blocked: ", e);
                    });
                }
                lastOrderCount = count;
            }
        });
    }

    // Refresh on search input
    $('#search_ticket').on('input', function(){
        load_orders();
    });

    // Auto-refresh every 10 seconds
    setInterval(load_orders, 10000);

    // Initial load
    load_orders();
});
</script>