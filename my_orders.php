<?php
include('rms.php');
$object = new rms();
if(!$object->is_login()) { header("location:".$object->base_url.""); exit; }
include('header.php');
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    :root { 
        --glass: rgba(255, 255, 255, 0.03); 
        --glass-border: rgba(255, 255, 255, 0.1); 
        --neon-gold: #f39c12; 
        --neon-blue: #00d2ff;
        --bg-deep: #050505;
    }
    
    body { background-color: var(--bg-deep); color: #e0e0e0; font-family: 'Inter', sans-serif; }

    /* Glassmorphism Containers */
    .glass-panel { 
        background: var(--glass); 
        backdrop-filter: blur(20px); 
        border: 1px solid var(--glass-border); 
        border-radius: 24px; 
        box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.8);
    }

    /* Modern Tabs */
    .nav-pills .nav-link { 
        color: #666; 
        border-radius: 12px; 
        padding: 12px 24px; 
        transition: 0.4s; 
        border: 1px solid transparent; 
        font-weight: 600;
        letter-spacing: 0.5px;
    }
    .nav-pills .nav-link.active { 
        background: rgba(243, 156, 18, 0.1) !important; 
        color: var(--neon-gold) !important; 
        border-color: var(--neon-gold);
        box-shadow: 0 0 20px rgba(243, 156, 18, 0.15);
    }

    /* Active Order Cards (From Action 11) */
    .order-card { 
        background: rgba(255,255,255,0.02);
        border: 1px solid var(--glass-border);
        border-radius: 20px;
        padding: 20px;
        transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    .order-card:hover { 
        transform: translateY(-8px);
        border-color: var(--neon-blue);
        box-shadow: 0 15px 40px rgba(0, 210, 255, 0.1);
    }

    /* THE COOL DIGITAL RECEIPT */
    .digital-receipt-wrapper {
        background: #0f0f0f;
        color: #fff;
        border: 1px solid #333;
        border-radius: 20px;
        position: relative;
        padding: 40px 30px;
        box-shadow: 0 25px 50px rgba(0,0,0,0.9);
        overflow: hidden;
    }
    /* Glowing Accent line */
    .digital-receipt-wrapper::before {
        content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
        background: linear-gradient(90deg, transparent, var(--neon-gold), transparent);
    }
    /* Scan-line effect */
    .digital-receipt-wrapper::after {
        content: ""; position: absolute; top: 0; left: 0; width: 100%; height: 100%;
        background: linear-gradient(rgba(18, 16, 16, 0) 50%, rgba(0, 0, 0, 0.1) 50%), linear-gradient(90deg, rgba(255, 0, 0, 0.03), rgba(0, 255, 0, 0.01), rgba(0, 0, 255, 0.03));
        z-index: 10; background-size: 100% 2px, 3px 100%; pointer-events: none;
    }

    /* Table Design for Archive */
    .table { border-collapse: separate; border-spacing: 0 10px; }
    .table tbody tr { background: rgba(255,255,255,0.02); transition: 0.3s; }
    .table tbody tr:hover { background: rgba(255,255,255,0.06); transform: scale(1.01); }
    .table td { border: none !important; vertical-align: middle; padding: 15px !important; }
    .table thead th { border: none; text-transform: uppercase; font-size: 0.7rem; color: #444; letter-spacing: 2px; }

    /* Status Pulse for Active Orders */
    .status-indicator { display: flex; align-items: center; gap: 8px; font-size: 0.8rem; color: var(--neon-gold); }
    .pulse-dot {
        width: 8px; height: 8px; background: var(--neon-gold); border-radius: 50%;
        animation: pulse-ring 1.5s cubic-bezier(0.455, 0.03, 0.515, 0.955) infinite;
    }

    @keyframes pulse-ring {
        0% { transform: scale(.33); }
        80%, 100% { opacity: 0; }
    }
</style>

<div class="container py-5">
    <div class="row mb-5 align-items-center">
        <div class="col-md-7">
            <h1 class="display-4 font-weight-bold mb-0" style="letter-spacing: -2px;">
                MY <span style="color: var(--neon-gold); text-shadow: 0 0 15px rgba(243,156,18,0.3);">ORDERS</span>
            </h1>
            <p class="text-muted lead">Real-time status and digital archive of your dining.</p>
        </div>
        <div class="col-md-5 text-md-right">
            <a href="user_dashboard.php" class="btn btn-warning px-5 py-3 rounded-pill font-weight-bold shadow-lg">
                <i class="fas fa-plus-circle mr-2"></i> NEW ORDER
            </a>
        </div>
    </div>

    <ul class="nav nav-pills mb-5 justify-content-center justify-content-md-start">
        <li class="nav-item">
            <a class="nav-link active" data-toggle="pill" href="#active"><i class="fas fa-broadcast-tower mr-2"></i> LIVE TRACKING</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="pill" href="#history"><i class="fas fa-history mr-2"></i> PAST VISITS</a>
        </li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="active">
            <div class="row" id="customer_active_area">
                <div class="col-12 text-center py-5">
                    <div class="spinner-border text-warning" role="status"></div>
                </div>
            </div>
        </div>
        
        <div class="tab-pane fade" id="history">
            <div class="glass-panel p-4">
                <div class="table-responsive">
                    <table class="table text-white w-100" id="customer_history_table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Timestamp</th>
                                <th>Table No.</th>
                                <th>Total Bill</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 bg-transparent">
            <div class="modal-body p-0" id="receipt_content">
                </div>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>

<script>
$(document).ready(function(){

    // 1. Fetch Live Orders (Action 11)
    function load_active_orders() {
        $.ajax({
            url: "order_action.php",
            method: "POST",
            data: { action: 'fetch_customer_active_orders' },
            success: function(data){ 
                $('#customer_active_area').html(data); 
            }
        });
    }
    load_active_orders();
    setInterval(load_active_orders, 15000); 

    // 2. Archive Table (Action 12)
    $('#customer_history_table').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": { 
            "url": "order_action.php", 
            "type": "POST", 
            "data": { "action": "fetch_customer_history" } 
        },
        "columns": [
            { "data": "order_number" },
            { "data": "order_date" },
            { "data": "order_table" },
            { "data": "order_total" },
            { "data": "action" }
        ],
        "order": [[ 0, "desc" ]],
        "language": { 
            "search": "", 
            "searchPlaceholder": "Search archive...",
            "paginate": { "previous": "←", "next": "→" }
        }
    });

    // 3. Receipt Preview (Action 13)
    $(document).on('click', '.view_receipt', function(){
        let order_id = $(this).data('id');
        
        // Modal Loading State
        $('#receipt_content').html('<div class="digital-receipt-wrapper text-center"><div class="spinner-grow text-warning"></div></div>');
        $('#detailModal').modal('show');

        $.ajax({
            url: "order_action.php",
            method: "POST",
            data: { action: 'get_receipt_html', order_id: order_id },
            success: function(data){ 
                // Wrap Action 13 HTML into our "Cool" digital frame
                let modern_frame = `
                    <div class="digital-receipt-wrapper">
                        <div class="d-flex justify-content-between mb-4">
                            <span class="badge badge-warning px-3 py-2">OFFICIAL RECEIPT</span>
                            <span class="text-white-50 small">#ID-${order_id}</span>
                        </div>
                        <div class="receipt-inner-html">
                            ${data}
                        </div>
                        <div class="mt-4 pt-4 border-top border-secondary">
                            <button class="btn btn-warning btn-block py-3 font-weight-bold rounded-lg" onclick="window.print()">
                                <i class="fas fa-print mr-2"></i> PRINT PHYSICAL COPY
                            </button>
                            <button class="btn btn-link btn-block text-muted btn-sm mt-2" data-dismiss="modal">Close Window</button>
                        </div>
                    </div>
                `;
                $('#receipt_content').html(modern_frame);
            }
        });
    });
});
</script>