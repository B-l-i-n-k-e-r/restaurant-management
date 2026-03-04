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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    :root {
        --glass-bg: rgba(255, 255, 255, 0.03);
        --glass-border: rgba(255, 255, 255, 0.1);
        --accent-cyan: #0ea5e9;
        --neon-green: #10b981;
        --neon-red: #ef4444;
        --dropdown-bg: #111827;
    }

    body { background-color: #0c0f17; color: #e2e8f0; }

    /* 1. GLASS CARD & LAYOUT */
    .glass-card {
        background: var(--glass-bg) !important;
        backdrop-filter: blur(20px);
        border: 1px solid var(--glass-border) !important;
        border-radius: 24px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
    }

    /* 2. TABLE - FIT CONTENT CONSTRAINT */
    .table { color: #e2e8f0 !important; border-collapse: separate !important; border-spacing: 0; }
    .fit-content { width: 1% !important; white-space: nowrap !important; }

    .table thead th {
        background: transparent !important;
        color: var(--accent-cyan) !important;
        text-transform: uppercase;
        font-size: 0.7rem;
        letter-spacing: 2px;
        border-bottom: 2px solid rgba(14, 165, 233, 0.3) !important;
        padding: 1.2rem 1rem !important;
    }

    .table td {
        vertical-align: middle !important;
        padding: 1rem !important;
        border-top: 1px solid rgba(255, 255, 255, 0.05) !important;
    }

    /* 3. PRODUCT SPECIFICS */
    .product-img-circle {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 12px;
        border: 1px solid var(--accent-cyan);
        box-shadow: 0 0 10px rgba(14, 165, 233, 0.2);
    }

    .price-text {
        font-family: 'JetBrains Mono', monospace;
        color: var(--neon-green) !important; 
        font-weight: 600;
    }

    /* 4. MODAL & TAX SELECTOR */
    .modal-content {
        background: rgba(15, 23, 42, 0.98) !important;
        backdrop-filter: blur(25px);
        border: 1px solid var(--accent-cyan) !important;
        border-radius: 25px;
        color: white;
    }

    .form-control {
        background: rgba(0, 0, 0, 0.3) !important;
        border: 1px solid var(--glass-border) !important;
        color: white !important;
        border-radius: 12px;
    }

    select.form-control option { background-color: var(--dropdown-bg); color: #fff; }

    .tax-selection-container {
        background: rgba(0, 0, 0, 0.2);
        padding: 15px;
        border-radius: 15px;
        border: 1px solid var(--glass-border);
        max-height: 180px;
        overflow-y: auto;
    }

    .custom-file-upload {
        border: 2px dashed var(--accent-cyan);
        background: rgba(14, 165, 233, 0.05);
        border-radius: 15px;
        padding: 15px;
        transition: 0.3s;
    }
    .custom-file-upload:hover { background: rgba(14, 165, 233, 0.1); border-style: solid; }

    /* 5. COOL SWEETALERT2 OVERRIDES */
    .swal2-popup.cyber-popup {
        background: rgba(15, 23, 42, 0.98) !important;
        backdrop-filter: blur(20px) !important;
        border: 1px solid var(--accent-cyan) !important;
        border-radius: 24px !important;
        color: #fff !important;
    }
    .swal2-title { color: var(--accent-cyan) !important; text-transform: uppercase; }
    
    .swal2-confirm.cyber-confirm { background: transparent !important; border: 1px solid var(--neon-green) !important; color: var(--neon-green) !important; border-radius: 12px !important; padding: 10px 25px !important; font-weight: bold; margin: 5px; }
    .swal2-confirm.cyber-confirm:hover { background: var(--neon-green) !important; color: #000 !important; box-shadow: 0 0 20px var(--neon-green) !important; }
    
    .swal2-cancel.cyber-cancel { background: transparent !important; border: 1px solid var(--neon-red) !important; color: var(--neon-red) !important; border-radius: 12px !important; padding: 10px 25px !important; font-weight: bold; margin: 5px; }
</style>

<div class="container-fluid py-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-white font-weight-bold">Inventory & Product Menu</h1>
            <p class="text-white-50 small text-uppercase letter-spacing-1">Manage your culinary offerings</p>
        </div>
        <button type="button" id="add_product" class="btn btn-info shadow-sm px-4 py-2" style="border-radius: 15px; font-weight: bold; background: var(--accent-cyan); border: none;">
            <i class="fas fa-plus-circle mr-2"></i> NEW PRODUCT
        </button>
    </div>

    <span id="message"></span>

    <div class="card glass-card shadow-lg">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table" id="product_table" width="100%">
                    <thead>
                        <tr>
                            <th class="pl-4">Preview</th>
                            <th>Product Name</th>
                            <th class="fit-content">Price</th>
                            <th class="fit-content">Category</th>
                            <th class="fit-content text-center">Status</th>
                            <th class="fit-content text-right pr-4">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="productModal" class="modal fade" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <form method="post" id="product_form" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header border-0 p-4">
                    <h5 class="modal-title font-weight-bold text-uppercase letter-spacing-2" id="modal_title" style="color: var(--accent-cyan);">Add Product</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body px-4">
                    <span id="form_message"></span>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="small text-white-50 font-weight-bold">CATEGORY</label>
                            <select name="category_name" id="category_name" class="form-control" required>
                                <option value="">Select Category</option>
                                <?php foreach($category_result as $category): ?>
                                    <option value="<?= $category["category_name"] ?>"><?= $category["category_name"] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-8 mb-3">
                            <label class="small text-white-50 font-weight-bold">ITEM NAME</label>
                            <input type="text" name="product_name" id="product_name" class="form-control" required placeholder="Chicken Burger" />
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="small text-white-50 font-weight-bold">PRICE</label>
                            <input type="text" name="product_price" id="product_price" class="form-control" required placeholder="0.00" />
                        </div>
                        <div class="col-md-12 mb-4">
                            <label class="small text-white-50 font-weight-bold mb-2 d-block">TAX PROTOCOLS</label>
                            <div class="tax-selection-container">
                                <?php foreach($tax_result as $tax): ?>
                                    <label class="tax-item m-0 mb-2 d-flex align-items-center" style="cursor:pointer">
                                        <input type="checkbox" name="tax_ids[]" value="<?= $tax["tax_id"] ?>" class="tax_checkbox mr-3">
                                        <div class="d-flex justify-content-between w-100 align-items-center">
                                            <span class="text-white small"><?= $tax["tax_name"] ?></span>
                                            <span class="badge" style="background: rgba(14,165,233,0.2); color: var(--accent-cyan);"><?= $tax["tax_percentage"] ?>%</span>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="small text-white-50 font-weight-bold">VISUAL ASSET</label>
                            <label for="product_image" class="custom-file-upload w-100 text-center cursor-pointer">
                                <i class="fas fa-camera fa-2x mb-2 text-info"></i>
                                <span class="d-block small text-white-50">Upload Product Image</span>
                            </label>
                            <input type="file" name="product_image" id="product_image" class="d-none" accept="image/*" />
                            <div id="uploaded_image" class="mt-3 text-center"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4">
                    <input type="hidden" name="hidden_id" id="hidden_id" />
                    <input type="hidden" name="action" id="action" value="Add" />
                    <button type="submit" id="submit_button" class="btn btn-info btn-block py-3 font-weight-bold" style="border-radius: 15px; background: var(--accent-cyan); border: none;">INITIALIZE PRODUCT</button>
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
        "ajax" : { url:"product_action.php", type:"POST", data:{action:'fetch'} },
        "columnDefs":[ 
            { "targets":[0], "width": "60px", "className": "pl-4" },
            { "targets":[2, 3, 4], "className": "fit-content" },
            { "targets":[5], "orderable":false, "className": "fit-content text-right pr-4" }
        ],
        "language": {
            "search": "", "searchPlaceholder": "Filter inventory...",
            "paginate": { "previous": "<", "next": ">" }
        },
        "drawCallback": function(settings) {
            $('.price-column-sync').each(function() {
                if (!$(this).find('.price-text').length) {
                    var content = $(this).text();
                    $(this).html('<span class="price-text">' + content + '</span>');
                }
            });
        }
    });

    $('#add_product').click(function(){
        $('#product_form')[0].reset();
        $('#product_image').val(''); 
        $('#product_form').parsley().reset();
        $('.tax_checkbox').prop('checked', false);
        $('#modal_title').text('New Product Deployment');
        $('#action').val('Add');
        $('#submit_button').text('INITIALIZE PRODUCT');
        $('#productModal').modal('show');
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
                success:function(data) {
                    if(data.error != '') {
                        $('#form_message').html('<div class="alert alert-danger">'+data.error+'</div>');
                    } else {
                        $('#productModal').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'System Synced',
                            text: data.success,
                            customClass: { popup: 'cyber-popup', confirmButton: 'cyber-confirm' },
                            buttonsStyling: false
                        });
                        dataTable.ajax.reload();
                    }
                }
            })
        }
    });

    $(document).on('click', '.edit_button', function(){
        var product_id = $(this).data('id');
        $.ajax({
            url:"product_action.php",
            method:"POST",
            data:{product_id:product_id, action:'fetch_single'},
            dataType:'JSON',
            success:function(data) {
                $('#category_name').val(data.category_name);
                $('#product_name').val(data.product_name);
                $('#product_price').val(data.product_price);
                $('.tax_checkbox').prop('checked', false);
                if(data.tax_ids) {
                    var taxes = data.tax_ids.split(',');
                    taxes.forEach(t => $(`.tax_checkbox[value='${t}']`).prop('checked', true));
                }
                if(data.product_image != '') {
                    $('#uploaded_image').html(`<img src="images/${data.product_image}" class="product-img-circle" width="100" />`);
                }
                $('#modal_title').text('Modify Product Specs');
                $('#action').val('Edit');
                $('#submit_button').text('UPDATE ASSET');
                $('#hidden_id').val(product_id);
                $('#productModal').modal('show');
            }
        })
    });

    $(document).on('click', '.status_button', function(){
        var id = $(this).data('id');
        var status = $(this).data('status');
        var next_status = (status == 'Enable') ? 'Disable' : 'Enable';
        
        Swal.fire({
            title: 'Protocol Switch',
            text: "Set visibility to " + next_status + "?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'CONFIRM',
            cancelButtonText: 'ABORT',
            customClass: { popup: 'cyber-popup', confirmButton: 'cyber-confirm', cancelButton: 'cyber-cancel' },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url:"product_action.php",
                    method:"POST",
                    data:{id:id, action:'change_status', status:status, next_status:next_status},
                    success:function(data) {
                        dataTable.ajax.reload();
                    }
                })
            }
        });
    });

    $(document).on('click', '.delete_button', function(){
        var id = $(this).data('id');
        Swal.fire({
            title: 'Terminate Asset?',
            text: "Product will be permanently removed from the database.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'PURGE',
            cancelButtonText: 'ABORT',
            customClass: { popup: 'cyber-popup', confirmButton: 'cyber-cancel', cancelButton: 'cyber-confirm' },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url:"product_action.php",
                    method:"POST",
                    data:{id:id, action:'delete'},
                    success:function(data) {
                        Swal.fire({ icon: 'success', title: 'Asset Purged', customClass: { popup: 'cyber-popup' }});
                        dataTable.ajax.reload();
                    }
                })
            }
        });
    });
});
</script>