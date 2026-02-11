<?php
// product.php
include('rms.php');
$object = new rms();

if(!$object->is_login()) {
    header("location:".$object->base_url."");
}

if(!$object->is_master_user()) {
    header("location:".$object->base_url."dashboard.php");
}

// Fetch Categories for the Dropdown
$object->query = "
SELECT category_name FROM product_category_table 
WHERE category_status = 'Enable' 
ORDER BY category_name ASC
";
$category_result = $object->get_result();

// Fetch Active Taxes for the Dropdown
$object->query = "SELECT * FROM tax_table WHERE tax_status = 'Enable' ORDER BY tax_name ASC";
$tax_result = $object->get_result();

include('header.php');
?>

<style>
/* Glassmorphism Card & Container */
.glass-card { background: rgba(255,255,255,0.05)!important; backdrop-filter: blur(15px); border:1px solid rgba(255,255,255,0.1)!important; border-radius:15px; color:white; }
.table-responsive { width:100%!important; overflow-x:scroll!important; -webkit-overflow-scrolling: touch; border-radius:10px; padding-bottom:15px; }
.table-responsive::-webkit-scrollbar { height:10px; }
.table-responsive::-webkit-scrollbar-track { background: rgba(255,255,255,0.05); border-radius:10px; }
.table-responsive::-webkit-scrollbar-thumb { background: rgba(23,162,184,0.5); border:2px solid rgba(0,0,0,0.2); border-radius:10px; }
.table-responsive::-webkit-scrollbar-thumb:hover { background: rgba(23,162,184,0.8); }
.table { color:white!important; width:100%!important; white-space:nowrap; }
.table-bordered { border:1px solid rgba(255,255,255,0.1)!important; }
.table-bordered td, .table-bordered th { border:1px solid rgba(255,255,255,0.1)!important; }
.modal-content { background: rgba(30,30,30,0.9)!important; backdrop-filter: blur(20px); border:1px solid rgba(255,255,255,0.2); color:white; }
.form-control { background: rgba(255,255,255,0.1)!important; border:1px solid rgba(255,255,255,0.2)!important; color:white!important; }
.form-control option { background:#333; color:white; }
select[multiple] { height:auto!important; min-height:100px; }
.dataTables_wrapper .dataTables_info, .dataTables_wrapper .dataTables_length, .dataTables_wrapper .dataTables_filter, .dataTables_wrapper .dataTables_paginate { color:white!important; padding-top:15px; }
.page-link { background-color: rgba(255,255,255,0.1)!important; border-color: rgba(255,255,255,0.1)!important; color:white!important; }
</style>

<h1 class="h3 mb-4 text-white">Product Management</h1>

<span id="message"></span>

<div class="card glass-card shadow mb-4">
    <div class="card-header py-3 bg-transparent border-bottom-0">
        <div class="row align-items-center">
            <div class="col">
                <h6 class="m-0 font-weight-bold text-info">Product List</h6>
            </div>
            <div class="col text-right">
                <button type="button" name="add_product" id="add_product" class="btn btn-success btn-circle btn-sm shadow">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="product_table" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Product Price</th>
                        <th>Category</th>
                        <th>Image</th> <!-- New column -->
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>

<div id="productModal" class="modal fade">
    <div class="modal-dialog">
        <form method="post" id="product_form" enctype="multipart/form-data">
            <div class="modal-content shadow-lg">
                <div class="modal-header border-bottom-0">
                    <h4 class="modal-title" id="modal_title">Add Product</h4>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <span id="form_message"></span>
                    
                    <div class="form-group">
                        <label>Category</label>
                        <select name="category_name" id="category_name" class="form-control" required>
                            <option value="">Select Category</option>
                            <?php foreach($category_result as $category): ?>
                                <option value="<?= $category["category_name"] ?>"><?= $category["category_name"] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Product Name</label>
                        <input type="text" name="product_name" id="product_name" class="form-control" required data-parsley-pattern="/^[a-zA-Z0-9 \s]+$/" data-parsley-trigger="keyup" />
                    </div>

                    <div class="form-group">
                        <label>Product Price</label>
                        <input type="text" name="product_price" id="product_price" class="form-control" required data-parsley-pattern="^[0-9]+(\.[0-9]{1,2})?$" placeholder="0.00" data-parsley-trigger="keyup" />
                    </div>

                    <div class="form-group">
                        <label>Assign Taxes</label>
                        <select name="tax_ids[]" id="tax_ids" class="form-control" multiple required>
                            <?php foreach($tax_result as $tax): ?>
                                <option value="<?= $tax["tax_id"] ?>"><?= $tax["tax_name"] ?> (<?= $tax["tax_percentage"] ?>%)</option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-info">Hold Ctrl (Win) or Cmd (Mac) for multiple selection.</small>
                    </div>

                    <!-- New image upload -->
                    <div class="form-group">
                        <label>Product Image</label>
                        <input type="file" name="product_image" id="product_image" class="form-control" accept="image/*" />
                        <small class="text-info">Optional. Max size 2MB.</small>
                        <div id="uploaded_image" class="mt-2"></div>
                    </div>

                </div>
                <div class="modal-footer border-top-0">
                    <input type="hidden" name="hidden_id" id="hidden_id" />
                    <input type="hidden" name="action" id="action" value="Add" />
                    <input type="submit" name="submit" id="submit_button" class="btn btn-info" value="Add" />
                    <button type="button" class="btn btn-outline-light" data-dismiss="modal">Close</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function(){

    var dataTable = $('#product_table').DataTable({
        "processing" : true,
        "serverSide" : true,
        "order" : [],
        "autoWidth": false,
        "ajax" : {
            url:"product_action.php",
            type:"POST",
            data:{action:'fetch'}
        },
        "columnDefs":[ { "targets":[5], "orderable":false } ], // last column is action
    });

    $(window).on('resize', function() { dataTable.columns.adjust(); });

    $('#add_product').click(function(){
        $('#product_form')[0].reset();
        $('#product_form').parsley().reset();
        $('#modal_title').text('Add Product');
        $('#action').val('Add');
        $('#submit_button').val('Add');
        $('#productModal').modal('show');
        $('#form_message').html('');
        $('#uploaded_image').html('');
    });

    $('#product_form').on('submit', function(event){
        event.preventDefault();
        if($('#product_form').parsley().isValid()) {
            $.ajax({
                url:"product_action.php",
                method:"POST",
                data: new FormData(this),
                contentType:false,
                processData:false,
                dataType:'json',
                beforeSend:function() { 
                    $('#submit_button').attr('disabled', 'disabled').val('wait...'); 
                },
                success:function(data) {
                    $('#submit_button').attr('disabled', false);
                    if(data.error != '') {
                        $('#form_message').html(data.error);
                        $('#submit_button').val($('#action').val());
                    } else {
                        $('#productModal').modal('hide');
                        $('#message').html(data.success);
                        dataTable.ajax.reload();
                        setTimeout(function(){ $('#message').html(''); }, 5000);
                    }
                }
            })
        }
    });

    $(document).on('click', '.edit_button', function(){
        var product_id = $(this).data('id');
        $('#product_form').parsley().reset();
        $('#form_message').html('');
        $.ajax({
            url:"product_action.php",
            method:"POST",
            data:{product_id:product_id, action:'fetch_single'},
            dataType:'JSON',
            success:function(data) {
                $('#category_name').val(data.category_name);
                $('#product_name').val(data.product_name);
                $('#product_price').val(data.product_price);
                $('#tax_ids').val(data.tax_ids); 
                $('#uploaded_image').html(data.product_image); // show current image
                $('#modal_title').text('Edit Product');
                $('#action').val('Edit');
                $('#submit_button').val('Edit');
                $('#productModal').modal('show');
                $('#hidden_id').val(product_id);
            }
        })
    });

    $(document).on('click', '.status_button', function(){
        var id = $(this).data('id');
        var status = $(this).data('status');
        var next_status = (status == 'Enable') ? 'Disable' : 'Enable';
        if(confirm("Are you sure you want to "+next_status+" it?")) {
            $.ajax({
                url:"product_action.php",
                method:"POST",
                data:{id:id, action:'change_status', status:status, next_status:next_status},
                success:function(data) {
                    $('#message').html(data);
                    dataTable.ajax.reload();
                    setTimeout(function(){ $('#message').html(''); }, 5000);
                }
            })
        }
    });

    $(document).on('click', '.delete_button', function(){
        var id = $(this).data('id');
        if(confirm("Are you sure you want to remove it?")) {
            $.ajax({
                url:"product_action.php",
                method:"POST",
                data:{id:id, action:'delete'},
                success:function(data) {
                    $('#message').html(data);
                    dataTable.ajax.reload();
                    setTimeout(function(){ $('#message').html(''); }, 5000);
                }
            })
        }
    });
});
</script>
