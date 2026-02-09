<?php
// table.php
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

    /* Table Responsiveness & Forced Scrollbar */
    .table-responsive {
        width: 100% !important;
        overflow-x: scroll !important; /* Force the scrollbar */
        -webkit-overflow-scrolling: touch;
        border-radius: 10px;
        padding-bottom: 15px; /* Space for the scrollbar */
    }

    /* Custom Scrollbar Styling */
    .table-responsive::-webkit-scrollbar {
        height: 10px;
    }
    .table-responsive::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.05);
        border-radius: 10px;
    }
    .table-responsive::-webkit-scrollbar-thumb {
        background: rgba(23, 162, 184, 0.5); /* Info Cyan */
        border: 2px solid rgba(0, 0, 0, 0.2);
        border-radius: 10px;
    }
    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: rgba(23, 162, 184, 0.8);
    }

    /* Prevent wrapping to ensure scrollbar appears if content is wide */
    .table { 
        color: white !important; 
        width: 100% !important; 
        white-space: nowrap; 
    }
    
    .table-bordered { border: 1px solid rgba(255, 255, 255, 0.1) !important; }
    .table-bordered td, .table-bordered th { border: 1px solid rgba(255, 255, 255, 0.1) !important; }

    /* Modal Styling */
    .modal-content {
        background: rgba(30, 30, 30, 0.9) !important;
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

    /* DataTables Text Fixes */
    .dataTables_wrapper .dataTables_info, 
    .dataTables_wrapper .dataTables_length, 
    .dataTables_wrapper .dataTables_filter, 
    .dataTables_wrapper .dataTables_paginate {
        color: white !important;
        padding-top: 15px;
    }
    .page-link {
        background-color: rgba(255,255,255,0.1) !important;
        border-color: rgba(255,255,255,0.1) !important;
        color: white !important;
    }
</style>

<h1 class="h3 mb-4 text-white">Table Management</h1>

<span id="message"></span>
<div class="card glass-card shadow mb-4">
    <div class="card-header py-3 bg-transparent border-bottom-0">
        <div class="row align-items-center">
            <div class="col">
                <h6 class="m-0 font-weight-bold text-info">Table List</h6>
            </div>
            <div class="col text-right">
                <button type="button" name="add_table" id="add_table" class="btn btn-success btn-circle btn-sm shadow">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="table_data" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Table Name</th>
                        <th>Table Capacity</th>
                        <th>Assigned Waiter</th>
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

<div id="tableModal" class="modal fade">
    <div class="modal-dialog">
        <form method="post" id="table_form">
            <div class="modal-content">
                <div class="modal-header border-bottom-0">
                    <h4 class="modal-title" id="modal_title">Add Data</h4>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <span id="form_message"></span>
                    <div class="form-group">
                        <label>Table Name</label>
                        <input type="text" name="table_name" id="table_name" class="form-control" required data-parsley-pattern="/^[a-zA-Z0-9 \s]+$/" data-parsley-trigger="keyup" />
                    </div>
                    <div class="form-group">
                        <label>Table Capacity</label>
                        <select name="table_capacity" id="table_capacity" class="form-control" required>
                            <option value="">Select Capacity</option>
                            <?php for($i = 1; $i <= 20; $i++) { echo '<option value="'.$i.'">'.$i.' Person</option>'; } ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Assign Waiter</label>
                        <select name="waiter_id" id="waiter_id" class="form-control" required>
                            <option value="">Select Waiter</option>
                            <?php
                            $object->query = "SELECT * FROM user_table WHERE user_type = 'Waiter' AND user_status = 'Enable'";
                            $waiter_result = $object->get_result();
                            foreach($waiter_result as $waiter) {
                                echo '<option value="'.$waiter["user_id"].'">'.$waiter["user_name"].'</option>';
                            }
                            ?>
                        </select>
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
    var dataTable = $('#table_data').DataTable({
        "processing" : true,
        "serverSide" : true,
        "order" : [],
        "autoWidth": false,
        "ajax" : {
            url:"table_action.php",
            type:"POST",
            data:{action:'fetch'}
        },
        "columnDefs":[ { "targets":[4], "orderable":false } ],
    });

    // Handle window resize
    $(window).on('resize', function() {
        dataTable.columns.adjust();
    });

    $('#add_table').click(function(){
        $('#table_form')[0].reset();
        $('#table_form').parsley().reset();
        $('#modal_title').text('Add Table');
        $('#action').val('Add');
        $('#submit_button').val('Add');
        $('#tableModal').modal('show');
        $('#form_message').html('');
    });

    $('#table_form').on('submit', function(event){
        event.preventDefault();
        if($('#table_form').parsley().isValid()) {     
            $.ajax({
                url:"table_action.php",
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
                        $('#tableModal').modal('hide');
                        $('#message').html(data.success);
                        dataTable.ajax.reload();
                        setTimeout(function(){ $('#message').html(''); }, 5000);
                    }
                }
            })
        }
    });

    $(document).on('click', '.edit_button', function(){
        var table_id = $(this).data('id');
        $('#table_form').parsley().reset();
        $('#form_message').html('');
        $.ajax({
            url:"table_action.php",
            method:"POST",
            data:{table_id:table_id, action:'fetch_single'},
            dataType:'JSON',
            success:function(data) {
                $('#table_name').val(data.table_name);
                $('#table_capacity').val(data.table_capacity);
                $('#waiter_id').val(data.waiter_id); 
                $('#modal_title').text('Edit Table');
                $('#action').val('Edit');
                $('#submit_button').val('Edit');
                $('#tableModal').modal('show');
                $('#hidden_id').val(table_id);
            }
        })
    });

    $(document).on('click', '.status_button', function(){
        var id = $(this).data('id');
        var status = $(this).data('status');
        var next_status = (status == 'Enable') ? 'Disable' : 'Enable';
        if(confirm("Are you sure you want to "+next_status+" it?")) {
            $.ajax({
                url:"table_action.php",
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
                url:"table_action.php",
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