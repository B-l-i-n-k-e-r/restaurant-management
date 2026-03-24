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
        --sky-blue: #0ea5e9; 
        --sky-glow: rgba(14, 165, 233, 0.4);
        --deep-navy: #0f172a;
        --glass-bg: rgba(255, 255, 255, 0.03); 
        --glass-border: rgba(255, 255, 255, 0.1); 
        --accent-green: #22c55e;
        --accent-red: #ef4444;
    }
    
    body { background-color: var(--deep-navy); color: #e2e8f0; font-family: 'Poppins', sans-serif; }

    /* TABLE LAYOUT FIXES */
    .table td, .table th { 
        white-space: nowrap !important; 
        width: 1% !important; 
        vertical-align: middle;
        padding: 1.2rem 1rem !important;
        border-top: 1px solid var(--glass-border) !important;
    }
    /* Let the action column be flexible but contained */
    .table td:last-child, .table th:last-child {
        width: auto !important;
        text-align: right;
    }

    .glass-card { 
        background: rgba(15, 23, 42, 0.7) !important; 
        backdrop-filter: blur(20px); 
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid var(--glass-border) !important; 
        border-radius: 24px; 
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
        overflow: hidden;
    }

    /* LIVE STATUS CARD FIXES (Addressing the Image provided) */
    .order-footer-wrapper {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        margin-top: 1rem;
        flex-wrap: wrap; /* Prevents breaking out of card on small screens */
    }

    .order-action-btns {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .pay_now_btn, .view_receipt {
        padding: 6px 12px !important;
        border-radius: 10px !important;
        font-weight: 700 !important;
        text-transform: uppercase;
        font-size: 0.75rem !important;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .glass-card-modal { 
        background: #0f172a !important; 
        border: 1px solid var(--sky-blue) !important; 
        border-radius: 24px !important; 
        box-shadow: 0 0 30px var(--sky-glow) !important;
    }

    .table { color: #cbd5e1 !important; margin-bottom: 0 !important; }
    
    .table thead th {
        background: rgba(14, 165, 233, 0.05) !important;
        color: var(--sky-blue) !important;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 2px;
        border-bottom: 2px solid var(--sky-blue) !important;
    }

    tr:hover td { background: rgba(14, 165, 233, 0.05) !important; }

    /* Tab Styling */
    .nav-pills .nav-link { 
        color: #94a3b8; 
        border-radius: 12px; 
        padding: 12px 25px; 
        font-weight: 600; 
        text-transform: uppercase; 
        font-size: 0.8rem;
        letter-spacing: 1px;
        transition: 0.3s;
    }
    .nav-pills .nav-link.active { 
        background: var(--sky-blue) !important; 
        color: white !important; 
        box-shadow: 0 0 20px var(--sky-glow);
    }

    /* Modal Styling */
    .modal-content {
        background: #0f172a !important;
        border: 1px solid var(--sky-blue) !important;
        border-radius: 28px !important;
        box-shadow: 0 0 50px var(--sky-glow);
    }

    @media print {
        body * { visibility: hidden !important; }
        #receipt_content, #receipt_content * { visibility: visible !important; }
        #receipt_content { position: absolute; left: 0; top: 0; width: 100%; background: white !important; color: black !important; }
    }
</style>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h1 class="h2 font-weight-bold text-white mb-1">Order Tracking</h1>
            <p class="text-white-50 small text-uppercase" style="letter-spacing: 2px;">Real-time Status • <?php echo date('Y'); ?></p>
        </div>
        <a href="user_dashboard.php" class="btn btn-primary px-4 shadow-lg" style="background: var(--sky-blue); border: none; border-radius: 15px; font-weight: bold;">
            <i class="fas fa-plus mr-2"></i>ADD ITEMS
        </a>
    </div>

    <ul class="nav nav-pills mb-4 glass-card p-2 d-inline-flex" style="background: rgba(255,255,255,0.02) !important;">
        <li class="nav-item">
            <a class="nav-link active" data-toggle="pill" href="#active"><i class="fas fa-radar mr-2"></i>LIVE STATUS</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="pill" href="#history"><i class="fas fa-archive mr-2"></i>HISTORY</a>
        </li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="active">
            <div id="customer_active_area" class="row">
                <div class="col-12 text-center py-5">
                    <div class="spinner-grow text-sky-blue" role="status"></div>
                </div>
            </div>
        </div>
        
        <div class="tab-pane fade" id="history">
            <div class="card glass-card">
                <div class="table-responsive">
                    <table class="table w-100" id="customer_history_table">
                        <thead>
                            <tr>
                                <th class="pl-4">ORDER ID</th>
                                <th>DATE/TIME</th>
                                <th>TABLE</th>
                                <th>BILL AMOUNT</th>
                                <th>METHOD</th>
                                <th class="text-right pr-4">ACTION</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="settleModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content glass-card-modal text-white">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="modal-title font-weight-bold text-success">Finalize Settlement</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-5 text-center">
                <p class="text-white-50 small text-uppercase mb-1 font-weight-bold">Amount Outstanding</p>
                <h1 class="display-3 font-weight-bold text-white mb-5" id="display_total" style="text-shadow: 0 0 20px rgba(255,255,255,0.2);">0.00</h1>

                <div class="form-group text-left mb-4">
                    <label class="small text-white-50 ml-1">Payment Method</label>
                    <select id="payment_method" class="form-control glass-card border-secondary text-white" style="height: 55px; background: rgba(0,0,0,0.3) !important;">
                        <option value="Cash">💵 Cash Payment</option>
                        <option value="Card">💳 Credit / Debit Card</option>
                        <option value="M-Pesa">📱 Mobile Money (M-Pesa)</option>
                    </select>
                </div>

                <div id="cash_only_section" class="text-left">
                    <div class="form-group">
                        <label class="small text-white-50 ml-1">Cash Received</label>
                        <input type="number" id="amount_received" class="form-control form-control-lg glass-card text-white border-secondary" placeholder="0.00" style="height: 60px; font-size: 1.5rem; background: rgba(255,255,255,0.05) !important;">
                    </div>
                    <div class="p-3 rounded-xl d-flex justify-content-between align-items-center" style="background: rgba(0,0,0,0.2); border-radius: 15px;">
                        <span class="text-white-50">Change Due:</span>
                        <h3 class="mb-0 text-warning font-weight-bold" id="display_change">0.00</h3>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 p-4">
                <input type="hidden" id="settle_order_id">
                <button type="button" id="confirm_settlement_btn" class="btn btn-block py-3 font-weight-bold" style="background: var(--accent-green); color: white; border-radius: 15px; box-shadow: 0 10px 20px rgba(34, 197, 94, 0.3);">
                    COMPLETE & FINALIZE
                </button>
            </div>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>

<script>
$(document).ready(function(){
    let currentTotal = 0;

    function load_active_orders() {
        $.ajax({
            url: "order_action.php",
            method: "POST",
            data: { action: 'fetch_customer_active_orders' },
            success: function(data){ $('#customer_active_area').html(data); }
        });
    }
    load_active_orders();
    setInterval(load_active_orders, 10000); 

    var dataTable = $('#customer_history_table').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": { 
            "url": "order_action.php", 
            "type": "POST", 
            "data": { "action": "fetch_customer_history" } 
        },
        "columns": [
            { "data": "order_number", "className": "pl-4 font-weight-bold text-sky-blue" },
            { "data": "order_date" },
            { "data": "order_table", "className": "text-center" },
            { "data": "order_total", "className": "font-weight-bold" },
            { "data": "payment_method", "className": "text-white-50" },
            { "data": "action", "className": "text-right pr-4" }
        ],
        "order": [[ 0, "desc" ]],
        "language": { 
            "search": "", 
            "searchPlaceholder": "Search History...",
            "paginate": { "previous": "«", "next": "»" }
        }
    });

    $(document).on('click', '.view_receipt', function(){
        let order_id = $(this).data('id');
        window.location.href = 'print.php?action=print_receipt&order_id=' + order_id;
    });

    $(document).on('click', '.pay_now_btn', function(){
        let id = $(this).data('id');
        let amountText = $(this).data('bill'); 
        let amountNumeric = amountText.replace(/[^0-9.]/g, ''); 
        
        currentTotal = parseFloat(amountNumeric);
        $('#settle_order_id').val(id);
        $('#display_total').text(amountText);
        $('#amount_received').val('');
        $('#display_change').text('0.00');
        $('#payment_method').val('Cash');
        $('#cash_only_section').show();
        $('#settleModal').modal('show');
    });

    $('#amount_received').on('input', function(){
        let received = parseFloat($(this).val()) || 0;
        let change = received - currentTotal;
        $('#display_change').text(change >= 0 ? change.toFixed(2) : '0.00');
    });

    $('#payment_method').change(function(){
        if($(this).val() !== 'Cash') {
            $('#cash_only_section').fadeOut(200);
        } else {
            $('#cash_only_section').fadeIn(200);
        }
    });

    $('#confirm_settlement_btn').on('click', function(){
        let id = $('#settle_order_id').val();
        let method = $('#payment_method').val();
        let received = $('#amount_received').val();

        if(method === 'Cash' && (parseFloat(received) < currentTotal)) {
            Swal.fire({ icon: 'warning', title: 'Insufficient Cash', text: 'Amount received must cover the bill.', background: '#0f172a', color: '#fff'});
            return;
        }

        $.ajax({
            url: "order_action.php",
            method: "POST",
            data: {
                action: 'process_customer_payment',
                order_id: id,
                amount_paid: received,
                payment_method: method
            },
            success: function(data){
                try {
                    let response = JSON.parse(data);
                    if(response.status === 'success') {
                        $('#settleModal').modal('hide');
                        Swal.fire({ 
                            icon: 'success', 
                            title: 'Payment Confirmed', 
                            text: 'Order Finalized Successfully', 
                            background: '#0f172a', 
                            color: '#fff'
                        }).then(() => {
                            window.location.href = 'print.php?action=print_receipt&order_id=' + id;
                        });
                        load_active_orders();
                        if ($.fn.DataTable.isDataTable('#customer_history_table')) {
                            dataTable.ajax.reload();
                        }
                    } else {
                        Swal.fire({ icon: 'error', title: 'Payment Failed', text: response.message, background: '#0f172a', color: '#fff'});
                    }
                } catch(e) {
                    location.reload();
                }
            }
        });
    });
});
</script>