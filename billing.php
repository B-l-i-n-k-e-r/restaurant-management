<?php

//billing.php

include('rms.php');

$object = new rms();

if(!$object->is_login())
{
    header("location:".$object->base_url."");
}

if(!$object->is_cashier_user() && !$object->is_master_user())
{
    header("location:".$object->base_url."dashboard.php");
}

include('header.php');

?>

<style>
    :root { 
        --glass: rgba(255, 255, 255, 0.05); 
        --glass-border: rgba(255, 255, 255, 0.1); 
        --accent: #17a2b8; 
    }
    
    body { background-color: #0c0c0c; color: white; }

    .glass-card { 
        background: var(--glass) !important; 
        backdrop-filter: blur(15px); 
        border: 1px solid var(--glass-border) !important; 
        border-radius: 15px; 
        color: white;
    }

    .table { color: #ddd !important; border: none !important; }
    .table thead th { 
        background: rgba(255, 255, 255, 0.03); 
        text-transform: uppercase; 
        font-size: 0.75rem; 
        letter-spacing: 1px; 
        border: none !important;
        color: #888;
    }
    .table td { border-top: 1px solid rgba(255, 255, 255, 0.05) !important; vertical-align: middle !important; }
    
    .dataTables_wrapper .dataTables_length select, 
    .dataTables_wrapper .dataTables_filter input {
        background: var(--glass);
        border: 1px solid var(--glass-border);
        color: white;
        border-radius: 8px;
        padding: 5px 10px;
    }
    
    .page-item.active .page-link { background-color: var(--accent) !important; border-color: var(--accent) !important; }
    .page-link { background: var(--glass) !important; border: 1px solid var(--glass-border) !important; color: #ccc !important; }

    .modal-content {
        background: #1a1a1a !important;
        border: 1px solid var(--glass-border);
        border-radius: 20px;
        color: white;
    }
    .modal-header { border-bottom: 1px solid var(--glass-border); }
    .modal-footer { border-top: 1px solid var(--glass-border); }
    .close { color: white; text-shadow: none; opacity: 0.8; }
    .close:hover { color: #ff4d4d; opacity: 1; }

    .btn-glass-info { background: rgba(23, 162, 184, 0.1); border: 1px solid #17a2b8; color: #17a2b8; transition: 0.3s; }
    .btn-glass-info:hover { background: #17a2b8; color: white; }
    
    .btn-glass-danger { background: rgba(231, 74, 59, 0.1); border: 1px solid #e74a3b; color: #e74a3b; transition: 0.3s; }
    .btn-glass-danger:hover { background: #e74a3b; color: white; }

    .badge-paid { background: rgba(28, 200, 138, 0.2); color: #1cc88a; border: 1px solid #1cc88a; padding: 5px 10px; }
    .badge-pending { background: rgba(246, 194, 62, 0.2); color: #f6c23e; border: 1px solid #f6c23e; padding: 5px 10px; }
</style>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 font-weight-bold mb-0">Billing Management</h1>
            <p class="text-white-50 small">View, edit, and finalize customer transactions</p>
        </div>
        <div class="text-right">
            <button type="button" id="print_all_bills" class="btn btn-warning shadow-sm mr-2">
                <i class="fas fa-file-pdf mr-2"></i>Print All Bills
            </button>
            <span class="badge badge-outline-info border border-info text-info px-3 py-2">
                <i class="fas fa-user-shield mr-2"></i><?php echo $object->is_master_user() ? 'Admin Mode' : 'Cashier Mode'; ?>
            </span>
        </div>
    </div>

    <span id="message"></span>

    <div class="card glass-card shadow-lg">
        <div class="card-header bg-transparent border-0 pt-4">
            <h6 class="m-0 font-weight-bold" style="color: var(--accent);">
                <i class="fas fa-file-invoice-dollar mr-2"></i>Recent Bill List
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table" id="billing_table" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Table</th>
                            <th>Order #</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Waiter</th>
                            <?php if($object->is_master_user()) echo '<th>Cashier</th>'; ?>
                            <th>Status</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="billingModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <form method="post" id="billing_form">
            <div class="modal-content shadow-2xl">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold" id="modal_title">
                        <i class="fas fa-receipt mr-2 text-info"></i>Bill Details
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body px-4">
                    <div id="billing_detail"></div>
                </div>
                <div class="modal-footer bg-transparent">
                    <input type="hidden" name="hidden_order_id" id="hidden_order_id" />
                    <input type="hidden" name="action" id="action" value="Edit" />
                    
                    <button type="button" class="btn btn-link text-white-50 mr-auto" data-dismiss="modal">Close</button>
                    
                    <button type="submit" name="submit" id="settle_button" class="btn btn-info px-4">
                        <i class="fas fa-check-circle mr-2"></i> Settle Bill
                    </button>

                    <button type="button" id="modal_print_button" class="btn btn-warning px-4">
                        <i class="fas fa-print mr-2"></i> Print Receipt
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include('footer.php'); ?>

<script>
$(document).ready(function(){

    var dataTable = $('#billing_table').DataTable({
        "processing" : true,
        "serverSide" : true,
        "order" : [],
        "ajax" : {
            url:"billing_action.php",
            type:"POST",
            data:{action:'fetch'}
        },
        "columnDefs":[
            {
                "targets":[<?php echo ($object->is_master_user()) ? '7' : '6'; ?>],
                "orderable":false,
                "className": "text-right"
            },
        ],
        "language": {
            "search": "_INPUT_",
            "searchPlaceholder": "Search bills..."
        }
    });

    var is_admin = <?php echo ($object->is_master_user()) ? 'true' : 'false'; ?>;
    var is_cashier = <?php echo ($object->is_cashier_user()) ? 'true' : 'false'; ?>;

    // Handle "Print All Bills" button
    $('#print_all_bills').click(function(){
        window.open("print.php?action=print_all", "_blank");
    });

    function fetch_order_data(order_id)
    {
        $.ajax({
            url:"billing_action.php",
            method:"POST",
            data:{order_id:order_id, action:'fetch_single'},
            success:function(data)
            {
                $('#billing_detail').html(data);
            }
        });
    }

    $(document).on('click', '.view_button', function(){
        var order_id = $(this).data('id');
        var status = $(this).closest('tr').find('.badge').text().trim();
        
        $('#hidden_order_id').val(order_id);
        $('#billingModal').modal('show');
        
        $('#settle_button').hide();
        $('#modal_print_button').hide();

        if(is_admin) {
            if(status === 'Completed') {
                $('#modal_print_button').show();
            }
        } 
        
        if(is_cashier) {
            $('#modal_print_button').show();
            if(status === 'In Process') {
                $('#settle_button').show();
            }
        }

        fetch_order_data(order_id);
    });

    $(document).on('click', '#modal_print_button', function(){
        var order_id = $('#hidden_order_id').val();
        if(order_id != "") {
            window.open("print.php?action=print&order_id=" + order_id, "_blank");
        }
    });

    $('#billing_form').on('submit', function(event){
        event.preventDefault();
        $.ajax({
            url:"billing_action.php",
            method:"POST",
            data:$(this).serialize(),
            beforeSend:function()
            {
                $('#settle_button').attr('disabled', 'disabled').html('<i class="fas fa-spinner fa-spin"></i> Processing...');
            },
            success:function(data)
            {
                $('#settle_button').attr('disabled', false).html('<i class="fas fa-check-circle mr-2"></i> Settle Bill');
                if($.trim(data) != "") {
                    $('#message').html('<div class="alert alert-success">Bill Settled Successfully!</div>');
                    $('#billingModal').modal('hide');
                    dataTable.ajax.reload();
                    setTimeout(function(){ $('#message').html(''); }, 3000);
                }
            }
        });
    });

    $(document).on('click', '.print_button', function(){
        var order_id = $(this).data('id');
        window.open("print.php?action=print&order_id=" + order_id, "_blank");
    });

    $(document).on('click', '.delete_button', function(){
        var order_id = $(this).data('id');
        if(confirm("Are you sure you want to remove this Order?"))
        {
            $.ajax({
                url:"billing_action.php",
                method:"POST",
                data:{order_id:order_id, action:"remove_bill"},
                success:function(data)
                {
                    $('#message').html(data);
                    dataTable.ajax.reload();
                    setTimeout(function(){ $('#message').html(''); }, 5000);
                }
            })
        }
    });
});
</script>