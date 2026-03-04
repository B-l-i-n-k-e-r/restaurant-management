<?php
// my_orders.php
include('rms.php');
$object = new rms();
if(!$object->is_login()) { header("location:".$object->base_url.""); exit; }
include('header.php');
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    :root { 
        --glass-bg: rgba(255, 255, 255, 0.03); 
        --glass-border: rgba(255, 255, 255, 0.1); 
        --accent-cyan: #0ea5e9; 
        --neon-green: #10b981;
        --neon-yellow: #f59e0b;
        --neon-red: #ef4444;
    }
    
    body { background-color: #0c0f17; color: #e2e8f0; font-family: 'Inter', sans-serif; }

    /* 1. GLASS CARD STYLING */
    .glass-card { 
        background: var(--glass-bg) !important; 
        backdrop-filter: blur(20px); 
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid var(--glass-border) !important; 
        border-radius: 20px; 
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
    }

    /* 2. TABLE STYLING - FIT CONTENT CONSTRAINT */
    .table { color: #cbd5e1 !important; margin-bottom: 0 !important; border-collapse: separate !important; border-spacing: 0; }
    
    .fit-content { width: 1% !important; white-space: nowrap !important; }

    .table thead th {
        background: transparent !important;
        color: var(--accent-cyan) !important;
        text-transform: uppercase;
        font-size: 0.7rem;
        letter-spacing: 2px;
        border-bottom: 2px solid rgba(14, 165, 233, 0.2) !important;
        padding: 1.2rem 1rem !important;
    }

    .table td {
        vertical-align: middle !important;
        padding: 1rem !important;
        border-top: 1px solid rgba(255, 255, 255, 0.05) !important;
        background: transparent !important;
    }

    tr:hover td { background: rgba(14, 165, 233, 0.05) !important; }

    /* 3. DATATABLE OVERRIDES */
    .dataTables_wrapper .dataTables_filter input {
        background: rgba(255, 255, 255, 0.05) !important;
        border: 1px solid var(--glass-border) !important;
        color: white !important;
        border-radius: 10px;
        padding: 8px 15px;
    }

    .page-link { background: var(--glass-bg) !important; border: 1px solid var(--glass-border) !important; color: #94a3b8 !important; border-radius: 8px !important; margin: 0 3px; }
    .page-item.active .page-link { background: var(--accent-cyan) !important; color: #000 !important; border-color: var(--accent-cyan) !important; font-weight: bold; }

    /* 4. MODAL GLASS UPGRADE */
    .modal-content {
        background: rgba(15, 23, 42, 0.95) !important;
        backdrop-filter: blur(25px);
        border: 1px solid var(--accent-cyan) !important;
        border-radius: 24px !important;
        box-shadow: 0 0 50px rgba(14, 165, 233, 0.2);
    }

    /* 5. TABS STYLING */
    .nav-pills .nav-link { 
        color: #94a3b8; 
        border-radius: 12px; 
        padding: 10px 20px; 
        font-weight: bold; 
        text-transform: uppercase; 
        font-size: 0.8rem;
        letter-spacing: 1px;
    }
    .nav-pills .nav-link.active { 
        background: rgba(14, 165, 233, 0.1) !important; 
        color: var(--accent-cyan) !important; 
        border: 1px solid var(--accent-cyan);
    }

    /* DIGITAL RECEIPT PREVIEW (Modal) */
    .digital-receipt-wrapper {
        background: #000;
        color: #fff;
        padding: 30px;
        border-radius: 15px;
        border: 1px dashed var(--glass-border);
    }
</style>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 font-weight-bold text-white mb-0">My Orders</h1>
            <p class="text-white-50 small text-uppercase letter-spacing-1">Live tracking and order history</p>
        </div>
        <div>
            <a href="user_dashboard.php" class="btn btn-outline-info px-4 py-2" style="border-radius: 12px; font-weight: bold;">
                <i class="fas fa-plus mr-2"></i>NEW ORDER
            </a>
        </div>
    </div>

    <ul class="nav nav-pills mb-4">
        <li class="nav-item">
            <a class="nav-link active" data-toggle="pill" href="#active"><i class="fas fa-satellite-dish mr-2"></i> LIVE TRACKING</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="pill" href="#history"><i class="fas fa-history mr-2"></i> ARCHIVE</a>
        </li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="active">
            <div id="customer_active_area" class="row">
                <div class="col-12 text-center py-5">
                    <div class="spinner-border text-info" role="status"></div>
                </div>
            </div>
        </div>
        
        <div class="tab-pane fade" id="history">
            <div class="card glass-card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table w-100" id="customer_history_table">
                            <thead>
                                <tr>
                                    <th class="pl-4 fit-content">ORDER ID</th>
                                    <th>TIMESTAMP</th>
                                    <th class="fit-content">TABLE</th>
                                    <th class="fit-content">TOTAL BILL</th>
                                    <th class="text-right pr-4 fit-content">PROTOCOL</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold text-uppercase" style="color: var(--accent-cyan);">
                    <i class="fas fa-receipt mr-2"></i>Digital Receipt
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" id="receipt_content"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-link text-white-50 mr-auto" data-dismiss="modal">CLOSE</button>
                <button type="button" class="btn btn-warning px-4 print-trigger" style="border-radius: 12px; font-weight: bold;">
                    <i class="fas fa-print mr-2"></i>PRINT
                </button>
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
            success: function(data){ $('#customer_active_area').html(data); }
        });
    }
    load_active_orders();
    setInterval(load_active_orders, 15000); 

    var dataTable = $('#customer_history_table').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": { 
            "url": "order_action.php", 
            "type": "POST", 
            "data": { "action": "fetch_customer_history" } 
        },
        "columns": [
            { "data": "order_number", "className": "pl-4 fit-content font-weight-bold" },
            { "data": "order_date" },
            { "data": "order_table", "className": "fit-content text-center" },
            { "data": "order_total", "className": "fit-content font-weight-bold text-info" },
            { "data": "action", "className": "text-right pr-4 fit-content" }
        ],
        "order": [[ 0, "desc" ]],
        "language": { 
            "search": "", 
            "searchPlaceholder": "Filter archive...",
            "paginate": { "previous": "<", "next": ">" }
        }
    });

    $(document).on('click', '.view_receipt', function(){
        let order_id = $(this).data('id');
        $('#receipt_content').html('<div class="text-center py-5"><div class="spinner-border text-info"></div></div>');
        $('#detailModal').modal('show');

        $.ajax({
            url: "order_action.php",
            method: "POST",
            data: { action: 'get_receipt_html', order_id: order_id },
            success: function(data){ 
                let updatedData = data.replace(/(Waitstaff:)\s*[^<]*/gi, '$1 Self-Service');
                $('#receipt_content').html('<div class="digital-receipt-wrapper">' + updatedData + '</div>');
            }
        });
    });

    $(document).on('click', '.print-trigger', function(){
        window.print();
    });
});
</script>