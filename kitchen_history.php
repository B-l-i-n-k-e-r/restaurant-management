<?php
// kitchen_history.php
include('rms.php');
$object = new rms();

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
    :root {
        --neon-blue: #00d2ff;
        --neon-purple: #9d50bb;
        --cyber-black: #050608;
    }

    body { 
        background: radial-gradient(circle at top right, #0d1117, var(--cyber-black)); 
        color: #fff; 
        font-family: 'Inter', sans-serif;
    }

    /* Constraint: Force columns to fit content tightly */
    .fit-content { 
        width: 1% !important; 
        white-space: nowrap !important; 
    }

    /* Glassmorphism Card */
    .card-cyber {
        background: rgba(13, 14, 18, 0.8) !important;
        backdrop-filter: blur(15px);
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
        border-radius: 15px !important;
        overflow: hidden;
    }

    .card-header-cyber {
        background: rgba(0, 210, 255, 0.03) !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05) !important;
        padding: 20px !important;
    }

    /* Table Styling */
    .table { color: #e0e0e0 !important; margin-bottom: 0 !important; }
    .table thead th { 
        background: rgba(0, 0, 0, 0.2);
        color: var(--neon-blue);
        border: none !important;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 1.5px;
        padding: 15px !important;
    }
    .table td { 
        border-color: rgba(255, 255, 255, 0.03) !important; 
        vertical-align: top !important; /* Changed to top for better list alignment */
        padding: 15px !important;
    }

    /* List Item Styling */
    .item-list-container {
        line-height: 1.6;
        display: block;
    }

    .status-ready { color: var(--neon-blue); font-weight: 800; text-shadow: 0 0 10px rgba(0, 210, 255, 0.3); }
    .status-completed { color: #00ff9d; font-weight: 800; text-shadow: 0 0 10px rgba(0, 255, 157, 0.3); }

    .ticket-gradient {
        background: linear-gradient(135deg, var(--neon-blue), var(--neon-purple));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-weight: 900;
        font-family: 'Monaco', monospace;
    }

    /* DataTable Customization */
    .dataTables_wrapper .dataTables_length select,
    .dataTables_wrapper .dataTables_filter input {
        background: rgba(255, 255, 255, 0.05) !important;
        border: 1px solid rgba(0, 210, 255, 0.2) !important;
        color: #fff !important;
        border-radius: 8px;
        padding: 8px 15px;
    }

    .page-item.active .page-link {
        background: linear-gradient(135deg, var(--neon-blue), var(--neon-purple)) !important;
        border: none;
    }
    
    .page-link {
        background: rgba(255, 255, 255, 0.05) !important;
        color: var(--neon-blue) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
    }
</style>

<div class="container-fluid py-4 px-md-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div style="border-left: 4px solid var(--neon-purple); padding-left: 20px;">
            <h1 class="h2 text-white font-weight-bold mb-0">SERVICE <span style="color: var(--neon-blue);">RECORDS</span></h1>
            <p class="text-muted small text-uppercase mb-0" style="letter-spacing: 2px;">Daily Archive Stream</p>
        </div>
        <div class="text-right">
            <span class="badge px-4 py-2" style="background: rgba(157, 80, 187, 0.1); color: var(--neon-purple); border: 1px solid var(--neon-purple);">
                <i class="fas fa-history mr-2"></i> <?php echo date('d M Y'); ?>
            </span>
        </div>
    </div>

    <div class="card card-cyber shadow">
        <div class="card-header card-header-cyber">
            <h6 class="m-0 font-weight-bold" style="color: var(--neon-blue);"><i class="fas fa-list-ul mr-2"></i>COMPLETED TASKS</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive p-3">
                <table class="table" id="history_table" width="100%">
                    <thead>
                        <tr>
                            <th class="fit-content">TICKET</th>
                            <th class="fit-content">TABLE</th>
                            <th>ITEMS SERVED</th>
                            <th class="fit-content">TIMESTAMP</th>
                            <th class="fit-content">STATUS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $object->query = "SET SESSION group_concat_max_len = 10000";
                        $object->execute();

                        $object->query = "
                            SELECT o.order_number, o.order_table, o.order_time, o.order_status,
                                   GROUP_CONCAT(CONCAT('<div class=\"item-row\"><span style=\"color:var(--neon-purple); font-weight:bold;\">', oi.product_quantity, 'x</span> ', oi.product_name, '</div>') SEPARATOR '') as item_details
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
                                $status_class = ($row["order_status"] == 'Completed') ? 'status-completed' : 'status-ready';
                                $status_icon = ($row["order_status"] == 'Completed') ? 'fa-check-double' : 'fa-bell';
                                
                                echo '
                                <tr>
                                    <td class="fit-content"><span class="ticket-gradient">#'.$row["order_number"].'</span></td>
                                    <td class="fit-content"><span class="badge" style="background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1);">'.$row["order_table"].'</span></td>
                                    <td><div class="item-list-container">'.$row["item_details"].'</div></td>
                                    <td class="fit-content text-muted">'.date('H:i', strtotime($row["order_time"])).'</td>
                                    <td class="fit-content">
                                        <span class="'.$status_class.'">
                                            <i class="fas '.$status_icon.' mr-1"></i> '.$row["order_status"].'
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
        "pageLength": 10,
        "autoWidth": false,
        "language": {
            "search": "",
            "searchPlaceholder": "Search History...",
            "lengthMenu": "_MENU_ ENTRIES"
        },
        "columnDefs": [
            { "targets": [0, 1, 3, 4], "className": "fit-content" }
        ],
        "drawCallback": function() {
            $('.dataTables_paginate > .pagination').addClass('pagination-sm');
        }
    });
});
</script>