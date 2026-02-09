<?php
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
    /* Match your Table Management styling exactly */
    .glass-card {
        background: rgba(255, 255, 255, 0.05) !important;
        backdrop-filter: blur(15px);
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        border-radius: 15px;
        color: white;
    }
    .table { color: white !important; }
    .table-bordered { border: 1px solid rgba(255, 255, 255, 0.1) !important; }
    .table-bordered td, .table-bordered th { border: 1px solid rgba(255, 255, 255, 0.1) !important; }
    
    /* Modal Glass Styling */
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
    .form-control::placeholder { color: rgba(255,255,255,0.5); }
    
    /* DataTables specific override for readability */
    .dataTables_info, .dataTables_length, .dataTables_filter, .dataTables_paginate {
        color: white !important;
    }
    .page-link {
        background-color: rgba(255,255,255,0.1) !important;
        border-color: rgba(255,255,255,0.1) !important;
        color: white !important;
    }
</style>

<h1 class="h3 mb-4 text-white">Category Management</h1>

<span id="message"></span>
<div class="card glass-card shadow mb-4">
    <div class="card-header py-3 bg-transparent border-bottom-0">
        <div class="row">
            <div class="col">
                <h6 class="m-0 font-weight-bold text-info">Category List</h6>
            </div>
            <div class="col text-right">
                <button type="button" name="add_category" id="add_category" class="btn btn-success btn-circle btn-sm shadow">
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

<?php include('footer.php'); ?>

<div id="categoryModal" class="modal fade">
    <div class="modal-dialog">
        <form method="post" id="category_form">
            <div class="modal-content">
                <div class="modal-header border-bottom-0">
                    <h4 class="modal-title" id="modal_title">Add Data</h4>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <span id="form_message"></span>
                    <div class="form-group">
                        <label>Category Name</label>
                        <input type="text" name="category_name" id="category_name" class="form-control" required data-parsley-pattern="/^[a-zA-Z0-9 \s]+$/" data-parsley-trigger="keyup" />
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
    var dataTable = $('#category_table').DataTable({
        "processing" : true,
        "serverSide" : true,
        "order" : [],
        "ajax" : {
            url:"category_action.php",
            type:"POST",
            data:{action:'fetch'}
        },
        "columnDefs":[ { "targets":[2], "orderable":false } ],
    });

    $('#add_category').click(function(){
        $('#category_form')[0].reset();
        $('#category_form').parsley().reset();
        $('#modal_title').text('Add Category');
        $('#action').val('Add');
        $('#submit_button').val('Add');
        $('#categoryModal').modal('show');
        $('#form_message').html('');
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
                    $('#submit_button').attr('disabled', 'disabled').val('wait...');
                },
                success:function(data) {
                    $('#submit_button').attr('disabled', false);
                    if(data.error != '') {
                        $('#form_message').html(data.error);
                        $('#submit_button').val($('#action').val());
                    } else {
                        $('#categoryModal').modal('hide');
                        $('#message').html(data.success);
                        dataTable.ajax.reload();
                        setTimeout(function(){ $('#message').html(''); }, 5000);
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
                $('#modal_title').text('Edit Category');
                $('#action').val('Edit');
                $('#submit_button').val('Edit');
                $('#categoryModal').modal('show');
                $('#hidden_id').val(category_id);
            }
        })
    });

    $(document).on('click', '.status_button', function(){
        var id = $(this).data('id');
        var status = $(this).data('status');
        var next_status = (status == 'Enable') ? 'Disable' : 'Enable';
        if(confirm("Are you sure you want to "+next_status+" it?")) {
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

    $(document).on('click', '.delete_button', function(){
        var id = $(this).data('id');
        if(confirm("Are you sure you want to remove it?")) {
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