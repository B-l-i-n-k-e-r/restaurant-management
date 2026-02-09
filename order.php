<?php
// order.php
include('rms.php');
$object = new rms();

if(!$object->is_login()) { header("location:".$object->base_url.""); exit; }

$is_waiter = $object->is_waiter_user();
$is_master = $object->is_master_user();

include('header.php');
?>

<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.05) !important;
        backdrop-filter: blur(15px);
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        border-radius: 15px;
        color: white;
    }
    .card-header {
        background: transparent !important;
        color: #17a2b8 !important; 
        font-weight: bold;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
    }
    .table { color: white !important; }
    .modal-content {
        background: rgba(30, 30, 30, 0.8) !important;
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: white;
    }
    .form-control {
        background: rgba(255, 255, 255, 0.1) !important;
        border: 1px solid rgba(255, 255, 255, 0.2) !important;
        color: white !important;
    }
    .form-control option { background: #333; color: white; }
    .table_button { margin-bottom: 10px; margin-right: 5px; border-radius: 10px; transition: all 0.3s; }
    #order_status .table-bordered td, #order_status .table-bordered th { border: 1px solid rgba(255, 255, 255, 0.1) !important; }
    .nav-tabs .nav-link { color: #aaa; border: none; }
    .nav-tabs .nav-link.active { background: rgba(255,255,255,0.1) !important; color: #17a2b8 !important; border-bottom: 2px solid #17a2b8; }
</style>

<h1 class="h3 mb-4 text-white">Order Management</h1>

<ul class="nav nav-tabs mb-4 border-0" id="orderTab" role="tablist">
  <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#live">Live Orders</a></li>
  <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#history">Order History & Reports</a></li>
</ul>

<div class="tab-content">
    <div class="tab-pane fade show active" id="live">
        <div class="row">
            <div class="col col-sm-4">
                <div class="card glass-card shadow mb-4">
                    <div class="card-header py-3">Table Status</div>
                    <div class="card-body" id="table_status"></div>
                </div>
            </div>
            <div class="col col-sm-8">
                <div class="card glass-card shadow mb-4">
                    <div class="card-header py-3">Current Order Details</div>
                    <div class="card-body">
                        <div class="table-responsive" id="order_status">
                            <p class="text-center text-muted">Select a table to view order details</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="history">
        <div class="card glass-card shadow">
            <div class="card-body">
                <table class="table table-bordered" id="history_table" width="100%">
                    <thead><tr><th>Order No.</th><th>Table</th><th>Waiter</th><th>Total</th><th>Date</th><th>Action</th></tr></thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>

<div id="orderModal" class="modal fade">
    <div class="modal-dialog">
        <form method="post" id="order_form">
            <div class="modal-content">
                <div class="modal-header border-bottom-0"><h4 class="modal-title">Add Item</h4><button type="button" class="close text-white" data-dismiss="modal">&times;</button></div>
                <div class="modal-body">
                    <div class="form-group"><label>Category</label>
                        <select name="category_name" id="category_name" class="form-control" required><option value="">Select Category</option>
                        <?php
                        $object->query = "SELECT category_name FROM product_category_table WHERE category_status = 'Enable' ORDER BY category_name ASC";
                        foreach($object->get_result() as $cat) { echo '<option value="'.$cat["category_name"].'">'.$cat["category_name"].'</option>'; }
                        ?>
                        </select>
                    </div>
                    <div class="form-group"><label>Product</label><select name="product_name" id="product_name" class="form-control" required><option value="">Select Product</option></select></div>
                    <div class="form-group"><label>Qty</label><select name="product_quantity" id="product_quantity" class="form-control" required><?php for($i=1;$i<25;$i++){echo '<option value="'.$i.'">'.$i.'</option>';} ?></select></div>
                </div>
                <div class="modal-footer border-top-0">
                    <input type="hidden" name="hidden_table_id" id="hidden_table_id" /><input type="hidden" name="hidden_order_id" id="hidden_order_id" /><input type="hidden" name="hidden_product_rate" id="hidden_product_rate" /><input type="hidden" name="hidden_table_name" id="hidden_table_name" /><input type="hidden" name="action" value="Add" />
                    <input type="submit" class="btn btn-info" value="Add" />
                </div>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function(){
    var isWaiter = <?php echo $is_waiter ? 'true' : 'false'; ?>;

    reset_table_status();
    setInterval(reset_table_status, 10000);

    function reset_table_status() {
        $.ajax({ url:"order_action.php", method:"POST", data:{action:'reset'}, success:function(data){ $('#table_status').html(data); } });
    }

    function fetch_order_data(order_id) {
        $.ajax({ url:"order_action.php", method:"POST", data:{action:'fetch_order', order_id:order_id}, success:function(data) { $('#order_status').html(data); } });
    }

    $('#history_table').DataTable({ "processing": true, "serverSide": true, "ajax": { url: "order_action.php", type: "POST", data: { action: 'fetch_history' } } });

    $(document).on('click', '.table_button', function(){        
        var order_id = $(this).data('order_id');
        var table_name = $(this).data('table_name');

        if(!isWaiter && order_id == 0) {
            $('#order_status').html('<div class="alert alert-warning text-center">No active order on ' + table_name + '</div>');
            return;
        }

        $('#hidden_table_id').val($(this).data('index'));
        $('#hidden_table_name').val(table_name);
        $('#hidden_order_id').val(order_id);

        if(order_id == 0) { $('#orderModal').modal('show'); } else { fetch_order_data(order_id); }
    });

    $(document).on('change', '#category_name', function(){
        $.ajax({ url:"order_action.php", method:"POST", data:{action:'load_product', category_name:$(this).val()}, success:function(data){ $('#product_name').html(data); } });
    });

    $(document).on('change', '#product_name', function(){ $('#hidden_product_rate').val($('#product_name').find(':selected').data('price')); });

    $('#order_form').on('submit', function(e){
        e.preventDefault();
        $.ajax({ url:"order_action.php", method:"POST", data:$(this).serialize(), success:function(data) { $('#orderModal').modal('hide'); fetch_order_data(data); reset_table_status(); } }); 
    });

    $(document).on('click', '.remove_item', function(){
        if(confirm("Remove item?")) {
            var el = $(this);
            $.ajax({ url:"order_action.php", method:"POST", data:{item_id:el.data('item_id'), order_id:el.data('order_id'), action:'remove_item'}, success:function(data) {
                if(data == '0') { $('#order_status').html('<p class="text-center text-muted">Select a table</p>'); reset_table_status(); } else { fetch_order_data(el.data('order_id')); }
            }});
        }
    });

    $(document).on('click', '.print_button', function(){ window.open("print_order.php?id="+$(this).data('id'), "_blank"); });
});
</script>