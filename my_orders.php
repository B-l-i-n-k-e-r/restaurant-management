<?php
include('rms.php');
$object = new rms();
if(!$object->is_login()) { header("location:".$object->base_url.""); exit; }
include('header.php');
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link href="https://fonts.googleapis.com/css2?family=Courier+Prime:wght@400;700&display=swap" rel="stylesheet">

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

    /* THE COOL DIGITAL PREVIEW (Screen Only) */
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
    .digital-receipt-wrapper::before {
        content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
        background: linear-gradient(90deg, transparent, var(--neon-gold), transparent);
    }

    /* AUTHENTIC THERMAL PRINT ENGINE */
    @media print {
        @page { 
            margin: 0; 
            size: 80mm auto; 
        }
        
        body * { visibility: hidden; background: white !important; }
        
        #receipt_content, #receipt_content * { visibility: visible; }
        
        #receipt_content {
            position: absolute;
            left: 50% !important;
            top: 0;
            transform: translateX(-50%);
            width: 80mm; 
            padding: 4mm;
            color: #000 !important;
            background: #fff !important;
            font-family: 'Courier Prime', monospace !important;
            font-size: 11px;
            line-height: 1.2;
        }

        .btn, .badge, .btn-link, .border-top, .no-print, 
        .digital-receipt-wrapper::before, .digital-receipt-wrapper::after { 
            display: none !important; 
        }

        .digital-receipt-wrapper {
            border: none !important;
            box-shadow: none !important;
            background: white !important;
            padding: 0 !important;
        }

        .receipt-inner-html { width: 100%; }
        .receipt-inner-html table { width: 100%; border-collapse: collapse; }
        .receipt-inner-html td, .receipt-inner-html th { 
            color: #000 !important; 
            border-bottom: 1px dashed #ccc; 
            padding: 2px 0;
        }
        .dashed-line { 
            border-top: 1px dashed #000 !important; 
            margin: 8px 0; 
            width: 100%;
            height: 1px;
        }
        .show-print-only { display: block !important; }
    }

    /* Requirement: Fit columns to content */
    .fit-content { width: 1% !important; white-space: nowrap !important; }
    
    .table tbody tr { background: rgba(255,255,255,0.02); transition: 0.3s; }
    .table td { border: none !important; vertical-align: middle; padding: 15px !important; }
    
    .show-print-only { display: none; }
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
                                <th class="fit-content">Order ID</th>
                                <th>Timestamp</th>
                                <th class="fit-content">Table No.</th>
                                <th class="fit-content">Total Bill</th>
                                <th class="text-right fit-content">Action</th>
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

    $('#customer_history_table').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": { 
            "url": "order_action.php", 
            "type": "POST", 
            "data": { "action": "fetch_customer_history" } 
        },
        "columns": [
            { "data": "order_number", "className": "fit-content font-weight-bold" },
            { "data": "order_date" },
            { "data": "order_table", "className": "fit-content text-center" },
            { "data": "order_total", "className": "fit-content text-warning" },
            { "data": "action", "className": "text-right fit-content" }
        ],
        "order": [[ 0, "desc" ]],
        "language": { 
            "search": "", 
            "searchPlaceholder": "Search archive...",
            "paginate": { "previous": "←", "next": "→" }
        }
    });

    $(document).on('click', '.view_receipt', function(){
        let order_id = $(this).data('id');
        
        $('#receipt_content').html('<div class="digital-receipt-wrapper text-center"><div class="spinner-grow text-warning"></div></div>');
        $('#detailModal').modal('show');

        $.ajax({
            url: "order_action.php",
            method: "POST",
            data: { action: 'get_receipt_html', order_id: order_id },
            success: function(data){ 
                // Regex to swap Waitstaff name for "Self-Service" to match Self-Order intent
                let updatedData = data.replace(/(Waitstaff:)\s*[^<]*/gi, '$1 Self-Service');

                let modern_frame = `
                    <div class="digital-receipt-wrapper">
                        <div class="text-center mb-1">
                            <h5 class="mb-0 font-weight-bold">WAKANESA RESTAURANT</h5>
                            <p class="small mb-0">City Square 00200, Nairobi Kenya</p>
                            <p class="small">Tel: +254 797 369 845</p>
                            <div class="dashed-line"></div>
                        </div>

                        <div class="receipt-inner-html">
                            ${updatedData}
                        </div>

                        <div class="dashed-line"></div>
                        <div class="text-center mt-2 no-print">
                            <button class="btn btn-warning btn-block py-3 font-weight-bold rounded-lg print-trigger">
                                <i class="fas fa-print mr-2"></i> PRINT OFFICIAL RECEIPT
                            </button>
                            <button class="btn btn-link btn-block text-muted btn-sm mt-2" data-dismiss="modal">Close</button>
                        </div>
                        <div class="text-center mt-2 small show-print-only">
                            *** THANK YOU - COME AGAIN ***<br>
                            Resto-Modern Self-Order System
                        </div>
                    </div>
                `;
                $('#receipt_content').html(modern_frame);
            }
        });
    });

    $(document).on('click', '.print-trigger', function(){
        window.print();
    });
});
</script>