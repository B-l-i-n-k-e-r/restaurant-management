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
    /* Dark Theme UI Overrides */
    body {
        background-color: #0d0d0d; 
        color: #fff;
        overflow-x: hidden;
    }

    #content-wrapper {
        background: transparent;
    }

    /* Filter Pills */
    .filter-pills-container {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 25px;
    }

    .btn-filter {
        border-radius: 50px;
        padding: 10px 24px;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.8rem;
        border: 1px solid rgba(255,255,255,0.2);
        background: rgba(255,255,255,0.05);
        color: #fff;
        transition: 0.3s ease;
        cursor: pointer;
    }

    .btn-filter.active {
        background: #ff5e57; 
        border-color: #ff5e57;
        box-shadow: 0 0 15px rgba(255, 94, 87, 0.4);
    }

    /* Kitchen Grid - Column fit content logic */
    .kitchen-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(max-content, 350px));
        gap: 20px;
        width: 100%;
        align-items: start;
    }

    /* Ticket Card - Dark Glassmorphism */
    .ticket-card {
        background: rgba(25, 25, 25, 0.95); 
        backdrop-filter: blur(10px);
        border-radius: 12px;
        border: 1px solid rgba(255,255,255,0.1);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.6);
        transition: transform 0.2s ease;
        width: fit-content;
        min-width: 300px;
    }

    .ticket-header {
        padding: 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: rgba(255, 255, 255, 0.03);
        border-bottom: 1px solid rgba(255,255,255,0.08);
    }

    .order-id { color: #ff5e57; font-weight: 800; font-size: 1.1rem; }
    .table-label { text-transform: uppercase; font-size: 0.9rem; font-weight: 700; color: #fff; }

    .ticket-info {
        padding: 8px 15px;
        display: flex;
        justify-content: space-between;
        font-size: 0.8rem;
        color: #888;
        background: rgba(0,0,0,0.2);
    }

    .time-badge { color: #ff5e57; font-weight: bold; }

    .ticket-body {
        padding: 15px;
        max-height: 400px;
        overflow-y: auto;
    }

    /* Custom Scrollbar */
    .ticket-body::-webkit-scrollbar { width: 5px; }
    .ticket-body::-webkit-scrollbar-thumb { background: #333; border-radius: 10px; }

    .order-item {
        font-size: 1.15rem;
        margin-bottom: 12px;
        display: flex;
        align-items: flex-start;
        border-bottom: 1px solid rgba(255,255,255,0.05);
        padding-bottom: 8px;
        white-space: nowrap;
    }

    .item-qty { 
        background: #ff5e57; 
        color: #fff; 
        min-width: 28px; 
        height: 28px; 
        display: flex; 
        align-items: center; 
        justify-content: center; 
        border-radius: 4px; 
        font-weight: bold; 
        margin-right: 12px;
        font-size: 0.9rem;
    }

    /* Action Button Styling */
    .btn-status {
        width: 100%;
        padding: 18px;
        border: none;
        font-weight: 800;
        text-transform: uppercase;
        font-size: 1rem;
        letter-spacing: 1.5px;
        cursor: pointer;
        transition: 0.3s;
        margin-top: auto;
    }

    .btn-start { background: #2ecc71; color: #fff; }
    .btn-start:hover { background: #27ae60; }
    
    .btn-ready { background: #f1c40f; color: #000; } /* Yellow for Ready status */
    .btn-ready:hover { background: #d4ac0d; }

    .btn-waiting { background: #34495e; color: #fff; cursor: not-allowed; }

    /* Search Bar */
    .search-container {
        position: relative;
        max-width: 500px;
        margin-bottom: 30px;
    }

    .search-input {
        background: rgba(255,255,255,0.07);
        border: 1px solid rgba(255,255,255,0.15);
        border-radius: 10px;
        padding: 14px 15px 14px 45px;
        color: #fff;
        width: 100%;
        font-size: 1rem;
    }

    .search-icon { position: absolute; left: 18px; top: 18px; color: #888; }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap">
        <div>
            <h1 class="h2 mb-1 text-white font-weight-bold">Kitchen Display</h1>
            <p class="text-white-50 mb-0"><?php echo date('l, d M Y'); ?> • <span id="sync_status">Live Updates</span></p>
        </div>
        <div class="text-right pb-1">
            <span class="text-white-50 font-weight-bold small">ACTIVE ORDERS: <span id="active_count_text" class="text-white" style="font-size: 1.2rem;">0</span></span>
        </div>
    </div>

    <div class="filter-pills-container">
        <button class="btn-filter active" data-filter="All">All Active</button>
        <button class="btn-filter" data-filter="In Process">New Orders</button>
        <button class="btn-filter" data-filter="Preparing">Preparing</button>
        <button class="btn-filter" data-filter="Ready">Ready (Waiting for Cashier)</button>
    </div>

    <div class="search-container">
        <i class="fas fa-search search-icon"></i>
        <input type="text" id="search_orders" class="search-input" placeholder="Search Table # or Order ID..." autocomplete="off">
    </div>

    <div class="kitchen-container" id="kitchen_load">
    </div>
</div>

<audio id="bell" preload="auto">
    <source src="https://www.soundjay.com/misc/sounds/bell-ringing-05.mp3" type="audio/mpeg">
</audio>

<?php include('footer.php'); ?>

<script>
$(document).ready(function(){
    let currentOrderCount = 0;
    let activeFilter = 'All';
    let isRequesting = false;

    function fetch_kitchen_orders() {
        if(isRequesting) return;
        isRequesting = true;
        
        let searchText = $('#search_orders').val();
        
        $.ajax({
            url: "order_action.php",
            method: "POST",
            data: { 
                action: 'fetch_kitchen_grid', 
                filter: activeFilter,
                search: searchText
            },
            success: function(data) {
                $('#kitchen_load').html(data);
                let newCount = $('.ticket-card').length;
                $('#active_count_text').text(newCount);

                if(newCount > currentOrderCount && currentOrderCount !== 0) {
                    let audio = document.getElementById('bell');
                    audio.play().catch(e => console.log("Audio waiting for interaction."));
                }
                currentOrderCount = newCount;
                isRequesting = false;
            },
            error: function() {
                isRequesting = false;
                $('#sync_status').text('Connection Lost').css('color', 'red');
            }
        });
    }

    $('.btn-filter').click(function(){
        $('.btn-filter').removeClass('active');
        $(this).addClass('active');
        activeFilter = $(this).data('filter');
        fetch_kitchen_orders();
    });

    let searchTimer;
    $("#search_orders").on("keyup", function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(fetch_kitchen_orders, 300);
    });

    // Modified Status Logic
    $(document).on('click', '.change_status', function(){
        let order_id = $(this).data('id');
        let current_status = $(this).data('current'); // You'll need to ensure your order_action.php sends this
        let next_status = $(this).data('next');
        let btn = $(this);

        // If it's already "Ready", we disable the button for the kitchen
        // and let them know it's waiting for the cashier.
        if(next_status === 'Ready') {
            // Update to "Ready" status
        } else if (next_status === 'Completed') {
            // We want to prevent the kitchen from completing it if that's the cashier's job
            // For now, let's process the transition to 'Ready'
        }
        
        btn.prop('disabled', true).html('<i class="fas fa-circle-notch fa-spin"></i>');
        
        $.ajax({
            url: "order_action.php",
            method: "POST",
            data: { 
                action: 'update_order_status', 
                order_id: order_id, 
                status: next_status 
            },
            success: function(response) {
                if(response.trim() == 'success') {
                    fetch_kitchen_orders();
                } else {
                    alert("Update failed.");
                    fetch_kitchen_orders();
                }
            }
        });
    });

    setInterval(fetch_kitchen_orders, 10000);
    fetch_kitchen_orders();
});
</script>