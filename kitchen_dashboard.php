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
        --card-bg: rgba(20, 22, 26, 0.98);
        --glass-border: rgba(0, 210, 255, 0.2);
        --accent-orange: #ff5e3a;
        --neon-blue: #00d2ff;
        --neon-purple: #9d50bb;
        --text-muted: #888;
    }

    body {
        background: #06070a; 
        background-image: radial-gradient(circle at top right, #1a1a1a, #06070a);
        color: #fff;
        font-family: 'Inter', -apple-system, sans-serif;
        overflow-x: hidden;
    }

    .main-content {
        margin-left: var(--sidebar-width);
        padding: 40px;
        transition: all 0.3s;
    }

    /* Top HUD Section */
    .hud-title-section {
        border-left: 4px solid var(--neon-purple);
        padding-left: 20px;
        margin-bottom: 30px;
    }

    /* Live Clock Styling */
    #live_clock {
        font-family: 'Monaco', 'Courier New', monospace;
        color: var(--neon-blue);
        text-shadow: 0 0 10px rgba(0, 210, 255, 0.5);
        font-size: 1.8rem;
        font-weight: bold;
        letter-spacing: 2px;
    }

    /* Search Bar HUD */
    .search-container {
        max-width: 600px;
        margin-bottom: 40px;
        position: relative;
    }

    .search-box {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid var(--glass-border);
        border-radius: 12px;
        padding: 18px 25px 18px 55px;
        width: 100%;
        color: #fff;
        font-size: 1.1rem;
        outline: none;
        transition: 0.3s;
    }

    .search-box:focus {
        border-color: var(--neon-purple);
        background: rgba(255, 255, 255, 0.07);
        box-shadow: 0 0 20px rgba(157, 80, 187, 0.2);
    }

    .search-icon {
        position: absolute;
        left: 20px;
        top: 22px;
        color: var(--neon-blue);
        font-size: 1.2rem;
    }

    /* Ticket Grid - Content Fit Implementation */
    .ticket-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); 
        grid-auto-rows: min-content; 
        gap: 25px;
        align-items: start; 
    }

    /* Ticket Card Styling */
    .ticket-card {
        background: var(--card-bg);
        border-radius: 16px;
        border: 1px solid rgba(255, 255, 255, 0.08);
        box-shadow: 0 15px 35px rgba(0,0,0,0.6);
        display: flex;
        flex-direction: column;
        height: fit-content; /* Critical: Card height fits content */
        position: relative;
        overflow: hidden;
        transition: transform 0.3s ease;
    }

    .ticket-card:hover {
        transform: translateY(-5px);
        border-color: var(--neon-blue);
    }

    .ticket-card::before {
        content: "";
        position: absolute;
        top: 0; left: 0; width: 100%; height: 3px;
        background: linear-gradient(90deg, var(--neon-blue), var(--neon-purple));
    }

    .ticket-header {
        padding: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: rgba(255,255,255,0.02);
    }

    .order-id { font-weight: 800; color: var(--neon-blue); font-family: monospace; }
    .table-name { font-weight: 700; font-size: 1.2rem; color: #fff; }

    .ticket-meta {
        padding: 10px 20px;
        background: rgba(0,0,0,0.3);
        display: flex;
        justify-content: space-between;
        font-size: 0.8rem;
    }

    .customer-name { color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; font-weight: 600; }
    .time-ago { color: var(--accent-orange); font-weight: 800; }

    .ticket-body {
        padding: 20px;
    }

    .order-item {
        font-size: 1.15rem;
        padding: 10px 0;
        border-bottom: 1px solid rgba(255,255,255,0.03);
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }

    .order-item:last-child { border-bottom: none; }

    .item-qty {
        color: var(--neon-purple);
        background: rgba(157, 80, 187, 0.15);
        padding: 2px 8px;
        border-radius: 6px;
        font-weight: 900;
        min-width: 35px;
        text-align: center;
    }

    .pending-counter {
        text-align: right;
        border-right: 4px solid var(--neon-blue);
        padding-right: 25px;
    }
    
    .counter-value {
        font-size: 4.5rem;
        font-weight: 900;
        line-height: 1;
        color: #fff;
        text-shadow: 0 0 30px rgba(0, 210, 255, 0.3);
    }

    /* Mobile Responsive Fixes */
    @media (max-width: 768px) {
        .main-content { margin-left: 0; padding: 20px; }
        .counter-value { font-size: 3rem; }
        .ticket-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="main-content">
    <div class="row align-items-center mb-5">
        <div class="col-md-8">
            <div class="hud-title-section">
                <h1 class="font-weight-bold m-0" style="letter-spacing: -1px;">KITCHEN <span style="color: var(--neon-blue);">DASHBOARD</span></h1>
                <div class="d-flex align-items-center mt-2">
                    <div id="live_clock" class="mr-3"><?php echo date('H:i:s'); ?></div>
                    <div class="text-white-50 small font-weight-bold text-uppercase" style="letter-spacing: 1px;">
                        <?php echo date('l, d F Y'); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="pending-counter">
                <span class="text-white-50 font-weight-bold small text-uppercase" style="letter-spacing: 2px;">Active Tickets</span>
                <div id="active_count" class="counter-value">0</div>
            </div>
        </div>
    </div>

    <div class="search-container">
        <i class="fas fa-search search-icon"></i>
        <input type="text" id="search_ticket" class="search-box" placeholder="Scan Table Name or Order ID...">
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

    function updateClock() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('en-GB'); 
        $('#live_clock').text(timeString);
    }
    setInterval(updateClock, 1000);

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
                
                let count = $('.ticket-card').length;
                $('#active_count').text(count);

                if(count > lastOrderCount && lastOrderCount !== 0) {
                    document.getElementById('order_bell').play().catch(e => {});
                }
                lastOrderCount = count;
            }
        });
    }

    $('#search_ticket').on('input', function(){
        load_orders();
    });

    // Refresh every 10s
    setInterval(load_orders, 10000);
    load_orders();
});
</script>

<?php include('footer.php'); ?>