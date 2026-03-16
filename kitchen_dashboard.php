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
        --sky-blue: #0ea5e9;
        --sky-glow: rgba(14, 165, 233, 0.3);
        --deep-navy: #0f172a;
        --glass-bg: rgba(255, 255, 255, 0.03);
        --glass-border: rgba(255, 255, 255, 0.1);
        --accent-green: #22c55e;
        --sidebar-width: 250px;
    }

    body {
        background-color: var(--deep-navy);
        background-image: radial-gradient(circle at top right, #1e293b, #0f172a);
        color: #fff;
        font-family: 'Poppins', sans-serif;
    }

    .main-content {
        margin-left: var(--sidebar-width);
        padding: 40px;
        transition: all 0.3s;
    }

    /* HUD Header */
    .hud-title-section {
        border-left: 4px solid var(--sky-blue);
        padding-left: 20px;
        margin-bottom: 30px;
    }

    #live_clock {
        font-family: 'JetBrains Mono', monospace;
        color: var(--sky-blue);
        text-shadow: 0 0 15px var(--sky-glow);
        font-size: 2rem;
        font-weight: 700;
        letter-spacing: 2px;
    }

    /* Active Ticket Counter */
    .pending-counter {
        text-align: right;
        border-right: 4px solid var(--sky-blue);
        padding-right: 25px;
    }
    
    .counter-value {
        font-size: 4.5rem;
        font-weight: 900;
        line-height: 1;
        color: #fff;
        text-shadow: 0 0 30px var(--sky-glow);
    }

    /* Search HUD */
    .search-container {
        max-width: 600px;
        margin-bottom: 40px;
        position: relative;
    }

    .search-box {
        background: var(--glass-bg);
        border: 1px solid var(--glass-border);
        backdrop-filter: blur(10px);
        border-radius: 15px;
        padding: 18px 25px 18px 55px;
        width: 100%;
        color: #fff;
        font-size: 1.1rem;
        transition: 0.3s;
    }

    .search-box:focus {
        border-color: var(--sky-blue);
        box-shadow: 0 0 20px var(--sky-glow);
        outline: none;
    }

    .search-icon {
        position: absolute;
        left: 20px;
        top: 22px;
        color: var(--sky-blue);
    }

    /* Ticket Grid - CONSTRAINT: Fit content */
    .ticket-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); 
        grid-auto-rows: min-content; 
        gap: 25px;
        align-items: start; 
    }

    .ticket-card {
        background: rgba(15, 23, 42, 0.8);
        backdrop-filter: blur(15px);
        border-radius: 20px;
        border: 1px solid var(--glass-border);
        display: flex;
        flex-direction: column;
        height: fit-content; /* Critical: Fits content */
        position: relative;
        overflow: hidden;
        transition: 0.3s ease;
    }

    .ticket-card:hover {
        transform: translateY(-5px);
        border-color: var(--sky-blue);
        box-shadow: 0 0 20px var(--sky-glow);
    }

    .ticket-card::before {
        content: "";
        position: absolute;
        top: 0; left: 0; width: 100%; height: 4px;
        background: var(--sky-blue);
    }

    .ticket-header {
        padding: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: rgba(255,255,255,0.02);
    }

    .order-id { font-weight: 800; color: var(--sky-blue); font-family: 'JetBrains Mono', monospace; }
    .table-name { font-weight: 700; font-size: 1.2rem; color: #fff; }

    .ticket-meta {
        padding: 10px 20px;
        background: rgba(0,0,0,0.2);
        display: flex;
        justify-content: space-between;
        font-size: 0.85rem;
    }

    .time-ago { color: var(--accent-green); font-weight: 800; }

    .ticket-body { padding: 20px; }

    .order-item {
        font-size: 1.1rem;
        padding: 12px 0;
        border-bottom: 1px solid var(--glass-border);
        display: flex;
        align-items: flex-start;
        gap: 15px;
    }

    .order-item:last-child { border-bottom: none; }

    .item-qty {
        color: white;
        background: var(--sky-blue);
        padding: 2px 10px;
        border-radius: 8px;
        font-weight: 800;
        min-width: 35px;
        text-align: center;
        box-shadow: 0 0 10px var(--sky-glow);
    }

    @media (max-width: 768px) {
        .main-content { margin-left: 0; padding: 20px; }
        .counter-value { font-size: 3rem; }
    }
</style>

<div class="main-content">
    <div class="row align-items-center mb-5">
        <div class="col-md-8">
            <div class="hud-title-section">
                <h6 class="text-sky-blue font-weight-bold mb-1" style="color: var(--sky-blue); letter-spacing: 2px; text-transform: uppercase;">Operations</h6>
                <h1 class="font-weight-bold m-0" style="letter-spacing: -1px;">KITCHEN <span style="color: var(--sky-blue);">TERMINAL</span></h1>
                <div class="d-flex align-items-center mt-2">
                    <div id="live_clock" class="mr-4"><?php echo date('H:i:s'); ?></div>
                    <div class="text-white-50 small font-weight-bold text-uppercase" style="letter-spacing: 1px;">
                        <i class="fas fa-calendar-alt mr-2"></i><?php echo date('l, d F Y'); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="pending-counter">
                <span class="text-white-50 font-weight-bold small text-uppercase" style="letter-spacing: 2px;">Live Tickets</span>
                <div id="active_count" class="counter-value">0</div>
            </div>
        </div>
    </div>

    <div class="search-container">
        <i class="fas fa-search search-icon"></i>
        <input type="text" id="search_ticket" class="search-box" placeholder="Scan Table or Order ID...">
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

    // High-frequency refresh for kitchen environment
    setInterval(load_orders, 5000); 
    load_orders();
});
</script>

<?php include('footer.php'); ?>