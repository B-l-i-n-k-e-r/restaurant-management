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

<style>
    /* Glassmorphism Card & Container */
    .glass-card {
        background: rgba(255, 255, 255, 0.05) !important;
        backdrop-filter: blur(15px);
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        border-radius: 15px;
        color: white;
    }

    /* Table Responsiveness & Scrollbar */
    .table-responsive {
        width: 100% !important;
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch;
        border-radius: 10px;
        padding-bottom: 15px;
    }

    .table-responsive::-webkit-scrollbar {
        height: 8px;
    }
    .table-responsive::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.05);
        border-radius: 10px;
    }
    .table-responsive::-webkit-scrollbar-thumb {
        background: rgba(23, 162, 184, 0.5);
        border-radius: 10px;
    }

    /* Table Styling */
    .table { 
        color: white !important; 
        width: 100% !important; 
        white-space: nowrap; 
    }
    
    .table-bordered { border: 1px solid rgba(255, 255, 255, 0.1) !important; }
    .table-bordered td, .table-bordered th { border: 1px solid rgba(255, 255, 255, 0.1) !important; }

    /* Modal Glass Styling */
    .modal-content {
        background: rgba(30, 30, 30, 0.95) !important;
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: white;
        border-radius: 15px;
    }
    .form-control {
        background: rgba(255, 255, 255, 0.1) !important;
        border: 1px solid rgba(255, 255, 255, 0.2) !important;
        color: white !important;
        border-radius: 8px;
    }
    .form-control:focus {
        background: rgba(255, 255, 255, 0.15) !important;
        box-shadow: 0 0 0 0.2rem rgba(23, 162, 184, 0.25);
    }

    /* DataTables Pagination & Info */
    .dataTables_wrapper .dataTables_info, 
    .dataTables_wrapper .dataTables_length, 
    .dataTables_wrapper .dataTables_filter, 
    .dataTables_wrapper .dataTables_paginate {
        color: rgba(255, 255, 255, 0.7) !important;
        padding-top: 15px;
        font-size: 0.9rem;
    }
    .page-link {
        background-color: rgba(255,255,255,0.05) !important;
        border-color: rgba(255,255,255,0.1) !important;
        color: white !important;
    }
    .page-item.active .page-link {
        background-color: #17a2b8 !important;
        border-color: #17a2b8 !important;
    }

    .btn-circle {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        text-align: center;
        padding: 6px 0;
        font-size: 12px;
        line-height: 1.42857;
    }
</style>

<div class="container-fluid">
    <h1 class="h3 mb-4 text-white">Category Management</h1>

    <span id="message"></span>

    <div class="card glass-card shadow mb-4">
        <div class="card-header py-3 bg-transparent border-bottom-0">
            <div class="row align-items-center">
                <div class="col">
                    <h6 class="m-0 font-weight-bold text-info"><i class="fas fa-list mr-2"></i>Category List</h6>
                </div>
                <div class="col text-right">
                    <button type="button" name="add_category" id="add_category" class="btn btn-success btn-circle shadow-sm">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="category_table" width="100%" cellspacing="0">
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

<?php include('footer.php'); ?>

<div id="categoryModal" class="modal fade">
    <div class="modal-dialog">
        <form method="post" id="category_form">
            <div class="modal-content shadow-lg">
                <div class="modal-header border-bottom-0">
                    <h4 class="modal-title" id="modal_title">Add Category</h4>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <span id="form_message"></span>
                    <div class="form-group">
                        <label>Category Name</label>
                        <input type="text" name="category_name" id="category_name" class="form-control" required data-parsley-pattern="/^[a-zA-Z0-9 \s]+$/" data-parsley-trigger="keyup" placeholder="e.g. Main Course" />
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <input type="hidden" name="hidden_id" id="hidden_id" />
                    <input type="hidden" name="action" id="action" value="Add" />
                    <button type="submit" name="submit" id="submit_button" class="btn btn-info px-4">Add</button>
                    <button type="button" class="btn btn-outline-light" data-dismiss="modal">Close</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function(){
    var dataTable = $('#category_table').DataTable({
        "processing" : true,
        "serverSide" : true,
        "order" : [],
        "autoWidth": false,
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

    // Reset Form for New Entry
    $('#add_category').click(function(){
        $('#category_form')[0].reset();
        $('#category_form').parsley().reset();
        $('#modal_title').text('Add New Category');
        $('#action').val('Add');
        $('#submit_button').text('Add');
        $('#form_message').html('');
        $('#categoryModal').modal('show');
    });

    // Handle Form Submission
    $('#category_form').on('submit', function(event){
        event.preventDefault();
        if($('#category_form').parsley().isValid()) {     
            $.ajax({
                url:"category_action.php",
                method:"POST",
                data:$(this).serialize(),
                dataType:'json',
                beforeSend:function() {
                    $('#submit_button').attr('disabled', 'disabled').text('Wait...');
                },
                success:function(data) {
                    $('#submit_button').attr('disabled', false);
                    if(data.error != '') {
                        $('#form_message').html('<div class="alert alert-danger">'+data.error+'</div>');
                        $('#submit_button').text($('#action').val());
                    } else {
                        $('#categoryModal').modal('hide');
                        $('#message').html('<div class="alert alert-success">'+data.success+'</div>');
                        dataTable.ajax.reload();
                        setTimeout(function(){ $('#message').html(''); }, 5000);
                    }
                }
            })
        }
    });

    // Edit Button Click
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
                $('#modal_title').text('Edit Category Name');
                $('#action').val('Edit');
                $('#submit_button').text('Update');
                $('#hidden_id').val(category_id);
                $('#categoryModal').modal('show');
            }
        })
    });

    // Status Toggle
    $(document).on('click', '.status_button', function(){
        var id = $(this).data('id');
        var status = $(this).data('status');
        var next_status = (status == 'Enable') ? 'Disable' : 'Enable';
        if(confirm("Change category status to "+next_status+"?")) {
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

    // Delete Category
    $(document).on('click', '.delete_button', function(){
        var id = $(this).data('id');
        if(confirm("Remove this category permanently? This may affect linked products.")) {
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
</script>