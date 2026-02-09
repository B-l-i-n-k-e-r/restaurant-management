<?php
// billing.php
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
    .glass-card {
        background: rgba(255, 255, 255, 0.05) !important;
        backdrop-filter: blur(15px);
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        border-radius: 15px;
        color: white;
    }

    .table-responsive {
        width: 100% !important;
        overflow-x: scroll !important;
        -webkit-overflow-scrolling: touch;
        border-radius: 10px;
        padding-bottom: 10px;
    }

    .table-responsive::-webkit-scrollbar { height: 10px; }
    .table-responsive::-webkit-scrollbar-track { background: rgba(255, 255, 255, 0.05); border-radius: 10px; }
    .table-responsive::-webkit-scrollbar-thumb {
        background: rgba(23, 162, 184, 0.5);
        border: 2px solid rgba(0, 0, 0, 0.2);
        border-radius: 10px;
    }

    .table { color: white !important; width: 100% !important; white-space: nowrap; }
    .table-bordered { border: 1px solid rgba(255, 255, 255, 0.1) !important; }
    .table-bordered td, .table-bordered th { border: 1px solid rgba(255, 255, 255, 0.1) !important; }
    
    .modal-content {
        background: rgba(30, 30, 30, 0.9) !important;
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: white;
    }

    .dataTables_wrapper .dataTables_info, 
    .dataTables_wrapper .dataTables_length, 
    .dataTables_wrapper .dataTables_filter, 
    .dataTables_wrapper .dataTables_paginate { color: white !important; padding-top: 15px; }

    .page-link { background-color: rgba(255,255,255,0.1) !important; border-color: rgba(255,255,255,0.1) !important; color: white !important; }
</style>

<h1 class="h3 mb-4 text-white">Billing Management</h1>

<span id="message"></span>

<div class="card glass-card shadow mb-4">
    <div class="card-header py-3 bg-transparent border-bottom-0">
        <h6 class="m-0 font-weight-bold text-info">Bill List</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="billing_table" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Table Number</th>
                        <th>Order Number</th>
                        <th>Order Date</th>
                        <th>Order Time</th>
                        <th>Waiter</th>
                        <?php if($object->is_master_user()) echo '<th>Cashier</th>'; ?>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<div id="billingModal" class="modal fade">
    <div class="modal-dialog modal-xl">
        <form method="post" id="billing_form">
            <div class="modal-content shadow-lg">
                <div class="modal-header border-bottom-0">
                    <h4 class="modal-title" id="modal_title">Bill Details</h4>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div id="billing_detail" class="p-1 p-md-3"></div>
                </div>
                <div class="modal-footer border-top-0">
                    <input type="hidden" name="hidden_order_id" id="hidden_order_id" />
                    <input type="hidden" name="action" id="action" value="Edit" />
                    <input type="submit" name="submit" id="submit_button" class="btn btn-success" value="Print Bill" />
                    <button type="button" class="btn btn-outline-light" data-dismiss="modal">Close</button>
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
        "autoWidth": false,
        "ajax" : {
            url:"billing_action.php",
            type:"POST",
            data:{action:'fetch'}
        },
        "columnDefs":[
            {
                "targets": [<?php echo ($object->is_master_user()) ? '7' : '6'; ?>],
                "orderable":false,
            },
        ],
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
        $('#billing_detail').html('<div class="text-center"><div class="spinner-border text-info" role="status"></div></div>');
        fetch_order_data(order_id);
        $('#hidden_order_id').val(order_id);
        $('#billingModal').modal('show');
    });

    // Submitting the bill marks it Completed
    $('#billing_form').on('submit', function(event){
        event.preventDefault();
        $.ajax({
            url:"billing_action.php",
            method:"POST",
            data:$(this).serialize(),
            beforeSend:function()
            {
                $('#submit_button').attr('disabled', 'disabled').val('Processing...');
            },
            success:function(data)
            {
                $('#submit_button').attr('disabled', false).val('Print');
                $('#billingModal').modal('hide');
                dataTable.ajax.reload();
                window.open("print.php?action=print&order_id="+data, "_blank");
            }
        });
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