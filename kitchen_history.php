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
        --sky-blue: #0ea5e9;
        --sky-glow: rgba(14, 165, 233, 0.3);
        --deep-navy: #0f172a;
        --glass-border: rgba(255, 255, 255, 0.08);
        --accent-green: #22c55e;
    }

    body { 
        background-color: var(--deep-navy);
        color: #fff; 
        font-family: 'Poppins', sans-serif;
    }

    /* Force columns to fit content tightly */
    .fit-content { 
        width: 1% !important; 
        white-space: nowrap !important; 
    }

    .card-cyber {
        background: rgba(15, 23, 42, 0.7) !important;
        backdrop-filter: blur(15px);
        -webkit-backdrop-filter: blur(15px);
        border: 1px solid var(--glass-border) !important;
        border-radius: 20px !important;
        overflow: hidden;
    }

    .card-header-cyber {
        background: rgba(255, 255, 255, 0.02) !important;
        border-bottom: 1px solid var(--glass-border) !important;
        padding: 20px !important;
    }

    /* Table Styling */
    .table { color: #cbd5e1 !important; margin-bottom: 0 !important; }
    .table thead th { 
        background: rgba(255, 255, 255, 0.03);
        color: var(--sky-blue);
        border: none !important;
        text-transform: uppercase;
        font-size: 0.7rem;
        letter-spacing: 1.5px;
        padding: 15px !important;
    }
    .table td { 
        border-color: rgba(255, 255, 255, 0.03) !important; 
        vertical-align: middle !important;
        padding: 15px !important;
    }

    .item-row {
        font-size: 0.9rem;
        margin-bottom: 2px;
    }

    .status-ready { color: var(--sky-blue); font-weight: 700; }
    .status-completed { color: var(--accent-green); font-weight: 700; }

    .ticket-id {
        color: var(--sky-blue);
        font-weight: 800;
        font-family: 'JetBrains Mono', monospace;
    }

    /* DataTable Customization */
    .dataTables_wrapper .dataTables_length select,
    .dataTables_wrapper .dataTables_filter input {
        background: rgba(255, 255, 255, 0.05) !important;
        border: 1px solid var(--glass-border) !important;
        color: #fff !important;
        border-radius: 10px;
        padding: 6px 12px;
    }
    
    .dataTables_filter input:focus {
        border-color: var(--sky-blue) !important;
        outline: none;
    }

    .page-item.active .page-link {
        background: var(--sky-blue) !important;
        border: none;
        box-shadow: 0 0 10px var(--sky-glow);
    }
    
    .page-link {
        background: rgba(255, 255, 255, 0.03) !important;
        color: var(--sky-blue) !important;
        border: 1px solid var(--glass-border) !important;
        margin: 0 2px;
        border-radius: 8px !important;
    }
</style>

<div class="container-fluid py-4 px-md-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div style="border-left: 4px solid var(--sky-blue); padding-left: 20px;">
            <h1 class="h3 text-white font-weight-bold mb-0">SERVICE <span style="color: var(--sky-blue);">ARCHIVE</span></h1>
            <p class="text-muted small text-uppercase mb-0" style="letter-spacing: 2px;">Daily Telemetry Stream</p>
        </div>
        <div class="text-right">
            <span class="badge px-3 py-2" style="background: rgba(14, 165, 233, 0.1); color: var(--sky-blue); border: 1px solid var(--sky-blue); border-radius: 10px;">
                <i class="fas fa-history mr-2"></i> <?php echo date('d M Y'); ?>
            </span>
        </div>
    </div>

    <div class="card card-cyber shadow">
        <div class="card-header card-header-cyber">
            <h6 class="m-0 font-weight-bold text-white"><i class="fas fa-stream mr-2 text-sky-blue"></i>COMPLETED SESSIONS</h6>
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
                        // Increase limit for GROUP_CONCAT to prevent item cutoff
                        $object->query = "SET SESSION group_concat_max_len = 10000";
                        $object->execute();

                        $object->query = "
                            SELECT o.order_number, o.order_table, o.order_time, o.order_status,
                                   GROUP_CONCAT(CONCAT('<div class=\"item-row\"><span class=\"text-white-50\">', oi.product_quantity, 'x</span> ', oi.product_name, '</div>') SEPARATOR '') as item_details
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
                                    <td class="fit-content"><span class="ticket-id">#'.$row["order_number"].'</span></td>
                                    <td class="fit-content">
                                        <div class="px-2 py-1 rounded text-center" style="background:rgba(255,255,255,0.05); border:1px solid var(--glass-border); font-size: 0.85rem;">
                                            '.$row["order_table"].'
                                        </div>
                                    </td>
                                    <td>'.$row["item_details"].'</td>
                                    <td class="fit-content text-white-50" style="font-size: 0.85rem;">'.date('H:i', strtotime($row["order_time"])).'</td>
                                    <td class="fit-content">
                                        <span class="'.$status_class.' small text-uppercase">
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
            "searchPlaceholder": "Filter records...",
            "lengthMenu": "_MENU_",
            "paginate": {
                "previous": "<i class='fas fa-chevron-left'></i>",
                "next": "<i class='fas fa-chevron-right'></i>"
            }
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