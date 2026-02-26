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

// Fetch Active Taxes
$object->query = "SELECT * FROM tax_table WHERE tax_status = 'Enable' ORDER BY tax_name ASC";
$tax_result = $object->get_result();

include('header.php');
?>

<style>
    :root {
        --glass-bg: rgba(255, 255, 255, 0.05);
        --glass-border: rgba(255, 255, 255, 0.12);
        --accent-cyan: #00f2fe;
        --accent-blue: #4facfe;
    }

    .glass-card {
        background: var(--glass-bg) !important;
        backdrop-filter: blur(15px);
        -webkit-backdrop-filter: blur(15px);
        border: 1px solid var(--glass-border) !important;
        border-radius: 20px;
        overflow: hidden;
    }

    .table { color: #f0f0f0 !important; border-collapse: separate; border-spacing: 0 10px; }
    .table thead th { 
        border: none !important; 
        color: var(--accent-cyan); 
        text-transform: uppercase; 
        font-size: 0.8rem;
        letter-spacing: 1px;
        padding: 15px;
    }
    .table tbody tr { 
        background: rgba(255, 255, 255, 0.03);
        transition: transform 0.2s ease, background 0.2s ease;
    }
    .table tbody tr:hover { 
        background: rgba(255, 255, 255, 0.08);
        transform: scale(1.005);
    }
    .table td { vertical-align: middle !important; border: none !important; padding: 15px; }

    .product-img-circle {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 12px;
        border: 2px solid var(--glass-border);
        box-shadow: 0 4px 15px rgba(0,0,0,0.3);
    }

    .tax-selection-container {
        background: rgba(0, 0, 0, 0.3);
        padding: 15px;
        border-radius: 15px;
        border: 1px solid var(--glass-border);
        max-height: 180px;
        overflow-y: auto;
    }
    .tax-item {
        display: flex;
        align-items: center;
        padding: 10px;
        margin-bottom: 8px;
        background: rgba(255, 255, 255, 0.03);
        border-radius: 10px;
        cursor: pointer;
        transition: 0.2s;
    }
    .tax-item:hover { background: rgba(255, 255, 255, 0.08); }
    .tax-item input[type="checkbox"] {
        width: 18px;
        height: 18px;
        margin-right: 15px;
        accent-color: var(--accent-cyan);
    }

    .modal-content {
        background: linear-gradient(160deg, #1e1e1e 0%, #121212 100%) !important;
        backdrop-filter: blur(20px);
        border: 1px solid var(--glass-border);
        border-radius: 25px;
        color: white;
    }

    /* FORM STYLING & COMBOBOX FIX */
    .form-control {
        background: rgba(255, 255, 255, 0.05) !important;
        border: 1px solid var(--glass-border) !important;
        border-radius: 12px;
        color: white !important;
        height: 45px;
    }
    
    /* This fixes the visibility of text in the dropdown menu */
    select.form-control option {
        background-color: #1a1a1a !important;
        color: white !important;
    }

    .form-control:focus {
        border-color: var(--accent-cyan) !important;
        box-shadow: 0 0 10px rgba(0, 242, 254, 0.2);
    }

    .custom-file-upload {
        display: block;
        width: 100%;
        padding: 10px;
        text-align: center;
        background: rgba(255,255,255,0.05);
        border: 2px dashed var(--glass-border);
        border-radius: 12px;
        cursor: pointer;
        transition: 0.3s;
    }
    .custom-file-upload:hover { border-color: var(--accent-cyan); background: rgba(255,255,255,0.08); }
</style>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h4 mb-0 text-white font-weight-bold">Inventory & Product Menu</h1>
        <button type="button" id="add_product" class="btn btn-info shadow-sm" style="border-radius: 12px; padding: 10px 24px;">
            <i class="fas fa-plus-circle mr-2"></i> New Product
        </button>
    </div>

    <span id="message"></span>

    <div class="card glass-card shadow-lg">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table" id="product_table" width="100%">
                    <thead>
                        <tr>
                            <th>Preview</th>
                            <th>Product Name</th>
                            <th>Price</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="productModal" class="modal fade">
    <div class="modal-dialog modal-dialog-centered">
        <form method="post" id="product_form" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header border-0 p-4">
                    <h5 class="modal-title font-weight-bold" id="modal_title">Add Product</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body px-4">
                    <span id="form_message"></span>
                    
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="small text-white-50">Product Category</label>
                            <select name="category_name" id="category_name" class="form-control" required>
                                <option value="">Select Category</option>
                                <?php foreach($category_result as $category): ?>
                                    <option value="<?= $category["category_name"] ?>"><?= $category["category_name"] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-8 mb-3">
                            <label class="small text-white-50">Item Name</label>
                            <input type="text" name="product_name" id="product_name" class="form-control" required placeholder="e.g. Chicken Burger" />
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="small text-white-50">Base Price</label>
                            <input type="text" name="product_price" id="product_price" class="form-control" required placeholder="0.00" />
                        </div>

                        <div class="col-md-12 mb-4">
                            <label class="small text-white-50 mb-2 d-block">Applicable Taxes</label>
                            <div class="tax-selection-container">
                                <?php foreach($tax_result as $tax): ?>
                                    <label class="tax-item m-0 mb-2">
                                        <input type="checkbox" name="tax_ids[]" value="<?= $tax["tax_id"] ?>" class="tax_checkbox">
                                        <div class="d-flex justify-content-between w-100 align-items-center">
                                            <span class="font-weight-bold" style="font-size: 0.9rem;"><?= $tax["tax_name"] ?></span>
                                            <span class="badge badge-dark"><?= $tax["tax_percentage"] ?>%</span>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="small text-white-50">Product Image</label>
                            <label for="product_image" class="custom-file-upload">
                                <i class="fas fa-cloud-upload-alt text-info mb-1"></i>
                                <span class="d-block small">Click to upload image</span>
                            </label>
                            <input type="file" name="product_image" id="product_image" class="d-none" accept="image/*" />
                            <div id="uploaded_image" class="mt-3 text-center"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4">
                    <input type="hidden" name="hidden_id" id="hidden_id" />
                    <input type="hidden" name="action" id="action" value="Add" />
                    <button type="submit" name="submit" id="submit_button" class="btn btn-info btn-block py-2 font-weight-bold" style="border-radius: 12px;">Save Product</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include('footer.php'); ?>

<script>
$(document).ready(function(){
    var dataTable = $('#product_table').DataTable({
        "processing" : true,
        "serverSide" : true,
        "order" : [],
        "ajax" : {
            url:"product_action.php",
            type:"POST",
            data:{action:'fetch'}
        },
        "columnDefs":[ { "targets":[0, 5], "orderable":false } ],
        "language": {
            "search": "_INPUT_",
            "searchPlaceholder": "Search menu..."
        }
    });

    $('#add_product').click(function(){
        $('#product_form')[0].reset();
        $('#product_image').val(''); 
        $('#product_form').parsley().reset();
        $('.tax_checkbox').prop('checked', false);
        $('#modal_title').text('Add New Product');
        $('#action').val('Add');
        $('#submit_button').text('Save Product');
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
                    $('#submit_button').attr('disabled', 'disabled').html('<i class="fas fa-spinner fa-spin"></i> Saving...'); 
                },
                success:function(data) {
                    $('#submit_button').attr('disabled', false).text('Save Product');
                    if(data.error != '') {
                        $('#form_message').html('<div class="alert alert-danger">'+data.error+'</div>');
                    } else {
                        $('#productModal').modal('hide');
                        $('#message').html('<div class="alert alert-success">'+data.success+'</div>');
                        dataTable.ajax.reload();
                        setTimeout(function(){ $('#message').html(''); }, 5000);
                    }
                }
            })
        }
    });

    $(document).on('click', '.edit_button', function(){
        var product_id = $(this).data('id');
        $('#product_form')[0].reset();
        $('#product_form').parsley().reset();
        $('#form_message').html('');
        $('.tax_checkbox').prop('checked', false); 
        
        $.ajax({
            url:"product_action.php",
            method:"POST",
            data:{product_id:product_id, action:'fetch_single'},
            dataType:'JSON',
            success:function(data) {
                $('#category_name').val(data.category_name);
                $('#product_name').val(data.product_name);
                $('#product_price').val(data.product_price);
                
                if(data.tax_ids) {
                    var selectedTaxes = data.tax_ids.split(',');
                    selectedTaxes.forEach(function(val) {
                        $("input[name='tax_ids[]'][value='" + val + "']").prop("checked", true);
                    });
                }

                if(data.product_image != '') {
                    $('#uploaded_image').html(`
                        <div class="p-2 border rounded" style="background: rgba(0,0,0,0.2)">
                            <img src="images/${data.product_image}" class="img-thumbnail bg-transparent border-0" width="80" />
                            <button type="button" class="btn btn-link text-danger btn-sm d-block mx-auto mt-2" id="remove_image_button" data-id="${product_id}">Remove Image</button>
                        </div>
                    `);
                }

                $('#modal_title').text('Modify Product');
                $('#action').val('Edit');
                $('#submit_button').text('Update Product');
                $('#hidden_id').val(product_id);
                $('#productModal').modal('show');
            }
        })
    });

    $(document).on('click', '#remove_image_button', function(){
        var product_id = $(this).data('id');
        if(confirm("Delete this product image?")) {
            $.ajax({
                url: "product_action.php",
                method: "POST",
                data: {product_id: product_id, action: 'remove_image'},
                success: function(data) {
                    $('#uploaded_image').html('<div class="small text-info">Image removed</div>');
                    dataTable.ajax.reload();
                }
            });
        }
    });

    $(document).on('click', '.status_button', function(){
        var id = $(this).data('id');
        var status = $(this).data('status');
        var next_status = (status == 'Enable') ? 'Disable' : 'Enable';
        if(confirm("Are you sure you want to "+next_status+" this product?")) {
            $.ajax({
                url:"product_action.php",
                method:"POST",
                data:{id:id, action:'change_status', next_status:next_status},
                success:function(data) {
                    $('#message').html(data);
                    dataTable.ajax.reload();
                }
            })
        }
    });

    $(document).on('click', '.delete_button', function(){
        var id = $(this).data('id');
        if(confirm("This will permanently delete the product. Continue?")) {
            $.ajax({
                url:"product_action.php",
                method:"POST",
                data:{id:id, action:'delete'},
                success:function(data) {
                    $('#message').html(data);
                    dataTable.ajax.reload();
                }
            })
        }
    });
});
</script>