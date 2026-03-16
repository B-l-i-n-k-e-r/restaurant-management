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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    :root {
        --sky-blue: #0ea5e9;
        --sky-glow: rgba(14, 165, 233, 0.4);
        --deep-navy: #0f172a;
        --glass-border: rgba(255, 255, 255, 0.1);
        --accent-green: #22c55e;
        --accent-yellow: #f59e0b;
    }

    body {
        background-color: var(--deep-navy);
        color: #fff;
        font-family: 'Poppins', sans-serif;
    }

    /* KDS Header Styling */
    .kds-header-border {
        border-left: 4px solid var(--sky-blue);
        padding-left: 20px;
    }

    .display-title {
        font-weight: 800;
        letter-spacing: 2px;
        text-transform: uppercase;
        color: #fff;
    }

    /* Filter UI */
    .filter-pills-container {
        display: flex;
        gap: 10px;
        margin-bottom: 25px;
        overflow-x: auto;
        padding-bottom: 5px;
    }

    .btn-filter {
        border-radius: 12px;
        padding: 8px 20px;
        font-weight: 600;
        font-size: 0.75rem;
        letter-spacing: 1px;
        border: 1px solid var(--glass-border);
        background: rgba(255, 255, 255, 0.03);
        color: #94a3b8;
        transition: 0.3s all;
        white-space: nowrap;
    }

    .btn-filter.active {
        background: var(--sky-blue);
        border-color: var(--sky-blue);
        color: #fff;
        box-shadow: 0 0 15px var(--sky-glow);
    }

    /* Kitchen Grid & Tickets */
    .kitchen-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        grid-auto-rows: min-content;
        gap: 20px;
    }

    .ticket-card {
        background: rgba(15, 23, 42, 0.7);
        backdrop-filter: blur(15px);
        -webkit-backdrop-filter: blur(15px);
        border-radius: 20px;
        border: 1px solid var(--glass-border);
        overflow: hidden;
        transition: transform 0.3s ease;
        position: relative;
    }

    .ticket-card:hover {
        transform: translateY(-5px);
        border-color: var(--sky-blue);
    }

    .ticket-header {
        padding: 15px 20px;
        background: rgba(255, 255, 255, 0.02);
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid var(--glass-border);
    }

    .order-id { color: var(--sky-blue); font-weight: 700; font-family: 'JetBrains Mono', monospace; }
    .table-label { font-weight: 800; color: #fff; font-size: 1.1rem; }

    .ticket-body { 
        padding: 20px; 
        min-height: 100px;
    }

    /* Action Buttons */
    .btn-status {
        width: 100%;
        padding: 15px;
        border: none;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 2px;
        transition: 0.3s;
        cursor: pointer;
        background: rgba(255, 255, 255, 0.02);
    }

    .btn-start { border-top: 1px solid var(--sky-blue); color: var(--sky-blue); }
    .btn-start:hover { background: var(--sky-blue); color: #fff; }
    
    .btn-ready { border-top: 1px solid var(--accent-green); color: var(--accent-green); }
    .btn-ready:hover { background: var(--accent-green); color: #fff; }

    /* Search Control */
    .search-container input {
        background: rgba(255, 255, 255, 0.03) !important;
        border: 1px solid var(--glass-border) !important;
        border-radius: 15px;
        padding: 12px 20px 12px 45px;
        color: #fff !important;
        width: 100%;
        transition: 0.3s;
    }
    
    .search-container input:focus {
        border-color: var(--sky-blue) !important;
        box-shadow: 0 0 10px var(--sky-glow);
        outline: none;
    }

    .search-icon {
        position: absolute;
        left: 18px;
        top: 15px;
        color: var(--sky-blue);
        z-index: 10;
    }

    /* SweetAlert Override */
    .swal2-popup.cyber-popup {
        background: #0f172a !important;
        border: 1px solid var(--sky-blue) !important;
        border-radius: 24px !important;
        color: #fff !important;
    }
    .swal2-confirm.cyber-confirm {
        background: var(--sky-blue) !important;
        border-radius: 12px !important;
        padding: 10px 25px !important;
    }
    .swal2-cancel.cyber-cancel {
        background: rgba(255,255,255,0.1) !important;
        border-radius: 12px !important;
    }
</style>

<div class="container-fluid py-4 px-md-5">
    <div class="row align-items-center mb-5">
        <div class="col-md-8">
            <div class="kds-header-border">
                <h1 class="display-title h2 mb-0">Kitchen Terminal</h1>
                <p class="text-white-50 mb-0 small">
                    <span id="sync_status" style="color: var(--sky-blue);"><i class="fas fa-circle-notch fa-spin mr-1"></i> LIVE TELEMETRY</span> 
                    • <?php echo date('l, d M'); ?>
                </p>
            </div>
        </div>
        <div class="col-md-4 text-md-right mt-3 mt-md-0">
            <div class="queue-box">
                <div class="small font-weight-bold text-muted text-uppercase" style="letter-spacing: 1px;">Queue Depth</div>
                <div id="active_count_text" style="font-size: 3rem; font-weight: 900; color: #fff; line-height: 1;">0</div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="filter-pills-container">
                <button class="btn-filter active" data-filter="All">ALL TICKETS</button>
                <button class="btn-filter" data-filter="In Process">NEW</button>
                <button class="btn-filter" data-filter="Preparing">PREPARING</button>
                <button class="btn-filter" data-filter="Ready">READY</button>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="search-container position-relative">
                <i class="fas fa-search search-icon"></i>
                <input type="text" id="search_orders" placeholder="Search Table, Order ID or Item...">
            </div>
        </div>
    </div>

    <div class="kitchen-container" id="kitchen_load">
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

    // Handle Status Updates with Cyber-Glass SweetAlert
    $(document).on('click', '.btn-start, .btn-ready', function(){
        let order_id = $(this).data('id');
        let next_status = $(this).hasClass('btn-start') ? 'Preparing' : 'Ready';
        let btn = $(this);

        Swal.fire({
            title: 'Transition Order?',
            text: "Move to " + next_status + " state?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'PROCEED',
            cancelButtonText: 'ABORT',
            customClass: {
                popup: 'cyber-popup',
                confirmButton: 'cyber-confirm',
                cancelButton: 'cyber-cancel'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "order_action.php",
                    method: "POST",
                    data: {
                        action: 'update_order_status',
                        order_id: order_id,
                        status: next_status
                    },
                    beforeSend: function() {
                        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
                    },
                    success: function(data) {
                        fetch_kitchen_orders();
                    }
                });
            }
        });
    });

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