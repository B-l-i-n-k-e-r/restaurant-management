<?php
// category.php
include('rms.php');
$object = new rms();

if(!$object->is_login()) {
    header("location:".$object->base_url."");
}

if(!$object->is_master_user()) {
    header("location:".$object->base_url."dashboard.php");
}

include('header.php');
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    :root {
        --glass-bg: rgba(255, 255, 255, 0.05);
        --glass-border: rgba(255, 255, 255, 0.1);
        --neon-blue: #0ea5e9;
    }

    /* Glassmorphism Card & Container */
    .glass-card {
        background: var(--glass-bg) !important;
        backdrop-filter: blur(15px);
        -webkit-backdrop-filter: blur(15px);
        border: 1px solid var(--glass-border) !important;
        border-radius: 20px;
        color: white;
        transition: transform 0.3s ease;
    }

    /* Table Responsiveness & Scrollbar */
    .table-responsive {
        width: 100% !important;
        overflow-x: auto !important;
        border-radius: 12px;
    }

    /* Table Styling - FIT CONTENT CONSTRAINT */
    .table { 
        color: white !important; 
        width: 100% !important; 
        margin-bottom: 0 !important;
    }
    
    .table td, .table th {
        white-space: nowrap !important;
        width: 1% !important; /* Forces columns to shrink to content */
        vertical-align: middle;
        border-color: rgba(255, 255, 255, 0.08) !important;
        padding: 1rem 1.5rem !important;
    }
    
    .table thead th {
        background: rgba(255, 255, 255, 0.03);
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 1px;
        font-weight: 600;
        color: var(--neon-blue);
        border-top: none !important;
    }

    /* Modal Glass Styling */
    .modal-content {
        background: rgba(15, 23, 42, 0.95) !important;
        backdrop-filter: blur(25px);
        border: 1px solid rgba(14, 165, 233, 0.3);
        color: white;
        border-radius: 20px;
    }
    .form-control {
        background: rgba(255, 255, 255, 0.05) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        color: white !important;
        border-radius: 10px;
    }

    /* ORIGINAL ACTION BUTTON STYLE */
    .btn-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: 0.3s;
    }
    .btn-circle:hover { transform: scale(1.1) rotate(90deg); }

    /* SweetAlert2 Cyber Theme */
    .swal2-popup.cyber-popup {
        background: rgba(15, 23, 42, 0.98) !important;
        backdrop-filter: blur(20px) !important;
        border: 1px solid var(--neon-blue) !important;
        border-radius: 24px !important;
        color: #fff !important;
    }
</style>

<div class="container-fluid py-4">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h4 mb-0 text-white font-weight-bold" style="letter-spacing: 1px;">
            <i class="fas fa-layer-group text-info mr-2"></i>CATEGORY ASSETS
        </h1>
    </div>

    <span id="message"></span>

    <div class="card glass-card shadow mb-4">
        <div class="card-header py-3 bg-transparent border-bottom-0 d-flex align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-white-50">MANAGE CATEGORIES</h6>
            <button type="button" name="add_category" id="add_category" class="btn btn-info btn-circle shadow-sm">
                <i class="fas fa-plus"></i>
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table" id="category_table" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Category Name</th>
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

<div id="categoryModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <form method="post" id="category_form">
            <div class="modal-content shadow-lg">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title font-weight-bold" id="modal_title">Add Category</h5>
                    <button type="button" class="close text-white opacity-50" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body px-4">
                    <span id="form_message"></span>
                    <div class="form-group">
                        <label class="small text-white-50 mb-2">CATEGORY NAME</label>
                        <input type="text" name="category_name" id="category_name" class="form-control form-control-lg" required data-parsley-pattern="/^[a-zA-Z0-9 \s]+$/" data-parsley-trigger="keyup" placeholder="e.g. Beverages" />
                    </div>
                </div>
                <div class="modal-footer border-top-0 px-4 pb-4">
                    <input type="hidden" name="hidden_id" id="hidden_id" />
                    <input type="hidden" name="action" id="action" value="Add" />
                    <button type="button" class="btn btn-link text-white-50 btn-sm text-decoration-none mr-auto" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="submit" id="submit_button" class="btn btn-info px-4 rounded-pill">Confirm</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include('footer.php'); ?>

<script>
$(document).ready(function(){
    var dataTable = $('#category_table').DataTable({
        "processing" : true,
        "serverSide" : true,
        "order" : [],
        "autoWidth": false, // Respects CSS width: 1%
        "ajax" : {
            url:"category_action.php",
            type:"POST",
            data:{action:'fetch'}
        },
        "columnDefs":[ 
            { "targets":[2], "orderable":false, "className": "text-center" },
            { "targets":[1], "className": "text-center" }
        ],
    });

    $('#add_category').click(function(){
        $('#category_form')[0].reset();
        $('#category_form').parsley().reset();
        $('#modal_title').text('New Category');
        $('#action').val('Add');
        $('#submit_button').text('Save Category');
        $('#form_message').html('');
        $('#categoryModal').modal('show');
    });

    $('#category_form').on('submit', function(event){
        event.preventDefault();
        if($('#category_form').parsley().isValid()) {     
            $.ajax({
                url:"category_action.php",
                method:"POST",
                data:$(this).serialize(),
                dataType:'json',
                beforeSend:function() {
                    $('#submit_button').attr('disabled', 'disabled').html('<i class="fas fa-spinner fa-spin"></i>');
                },
                success:function(data) {
                    $('#submit_button').attr('disabled', false).text('Confirm');
                    if(data.error != '') {
                        $('#form_message').html('<div class="alert alert-danger">'+data.error+'</div>');
                    } else {
                        $('#categoryModal').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: data.success,
                            customClass: { popup: 'cyber-popup' }
                        });
                        dataTable.ajax.reload();
                    }
                }
            })
        }
    });

    $(document).on('click', '.edit_button', function(){
        var category_id = $(this).data('id');
        $('#category_form').parsley().reset();
        $('#form_message').html('');
        $.ajax({
            url:"category_action.php",
            method:"POST",
            data:{category_id:category_id, action:'fetch_single'},
            dataType:'JSON',
            success:function(data) {
                $('#category_name').val(data.category_name);
                $('#modal_title').text('Update Category');
                $('#action').val('Edit');
                $('#submit_button').text('Update Info');
                $('#hidden_id').val(category_id);
                $('#categoryModal').modal('show');
            }
        })
    });

    $(document).on('click', '.status_button', function(){
        var id = $(this).data('id');
        var status = $(this).data('status');
        var next_status = (status == 'Enable') ? 'Disable' : 'Enable';
        
        Swal.fire({
            title: 'Change Status?',
            text: "Set category to " + next_status + "?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0ea5e9',
            cancelButtonColor: '#ef4444',
            confirmButtonText: 'Yes, change it!',
            customClass: { popup: 'cyber-popup' }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url:"category_action.php",
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
    });

    $(document).on('click', '.delete_button', function(){
        var id = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!',
            customClass: { popup: 'cyber-popup' }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url:"category_action.php",
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
});
</script>