<?php
// kitchen_history.php
include('rms.php');
$object = new rms();

// Security check: Ensure user is logged in and is Kitchen/Master staff
if(!$object->is_login()) { 
    header("location:".$object->base_url.""); 
    exit;
}

if($_SESSION['user_type'] != 'Kitchen' && $_SESSION['user_type'] != 'Master') { 
    header("location:".$object->base_url."dashboard.php"); 
    exit;
}

include('header.php');
?>

<style>
    /* Dark Theme & Glassmorphism Styling */
    body { background: #121212; color: #fff; }
    .card { 
        background: rgba(255,255,255,0.05) !important; 
        border: 1px solid rgba(255,255,255,0.1) !important; 
        backdrop-filter: blur(10px); 
    }
    .table { color: #fff !important; }
    .table-bordered { border: 1px solid rgba(255,255,255,0.1) !important; }
    
    .table thead th { 
        border: none;
        background: rgba(255,255,255,0.03);
        text-transform: uppercase; 
        font-size: 0.75rem; 
        letter-spacing: 1px;
    }

    /* Constraint: Force columns to fit content tightly */
    .fit-content { 
        width: 1% !important; 
        white-space: nowrap !important; 
    }
    
    .text-success-bright { color: #00ff9d; font-weight: bold; }
    .badge-outline { background: transparent; border: 1px solid rgba(255,255,255,0.3); color: #fff; }

    /* DataTables Dark Mode Adjustments */
    .dataTables_wrapper .dataTables_length, 
    .dataTables_wrapper .dataTables_filter, 
    .dataTables_wrapper .dataTables_info, 
    .dataTables_wrapper .dataTables_paginate {
        color: rgba(255,255,255,0.7) !important;
    }
    .dataTables_wrapper .dataTables_filter input {
        background-color: rgba(255,255,255,0.1);
        border: 1px solid rgba(255,255,255,0.2);
        color: #fff;
        border-radius: 4px;
        padding: 5px;
    }
</style>

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 text-white mb-0">Kitchen Order History</h1>
            <p class="text-white-50 small">Records of completed service for today</p>
        </div>
        <span class="badge badge-pill badge-info px-3 py-2">
            <i class="fas fa-calendar-day mr-1"></i> <?php echo date('l, d M Y'); ?>
        </span>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3" style="background: transparent; border-bottom: 1px solid rgba(255,255,255,0.1);">
            <h6 class="m-0 font-weight-bold text-info"><i class="fas fa-history mr-2"></i>Completed Service</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="history_table" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="fit-content">Ticket #</th>
                            <th class="fit-content">Table</th>
                            <th>Items Served</th>
                            <th class="fit-content">Ordered At</th>
                            <th class="fit-content">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Corrected way to set session variables using your RMS class structure
                        $object->query = "SET SESSION group_concat_max_len = 10000";
                        $object->execute();

                        $object->query = "
                            SELECT o.order_number, o.order_table, o.order_time, o.order_status,
                                   GROUP_CONCAT(CONCAT(oi.product_quantity, 'x ', oi.product_name) SEPARATOR '<br>') as item_details
                            FROM order_table o
                            INNER JOIN order_item_table oi ON oi.order_id = o.order_id 
                            WHERE (o.order_status = 'Ready' OR o.order_status = 'Completed')
                            AND o.order_date = CURDATE()
                            GROUP BY o.order_id
                            ORDER BY o.order_id DESC
                        ";
                        
                        $result = $object->get_result();
                        if($result) {
                            foreach($result as $row) {
                                echo '
                                <tr>
                                    <td class="fit-content font-weight-bold text-info">#'.$row["order_number"].'</td>
                                    <td class="fit-content"><span class="badge badge-outline">'.$row["order_table"].'</span></td>
                                    <td>'.$row["item_details"].'</td>
                                    <td class="fit-content">'.date('h:i A', strtotime($row["order_time"])).'</td>
                                    <td class="fit-content">
                                        <span class="text-success-bright">
                                            <i class="fas fa-check-double mr-1"></i> '.$row["order_status"].'
                                        </span>
                                    </td>
                                </tr>';
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>

<script>
$(document).ready(function(){
    $('#history_table').DataTable({
        "order": [[0, "desc"]], 
        "pageLength": 15,
        "autoWidth": false,
        "language": {
            "search": "_INPUT_",
            "searchPlaceholder": "Filter history..."
        },
        "columnDefs": [
            { "targets": [0, 1, 3, 4], "className": "fit-content" }
        ]
    });
});
</script>