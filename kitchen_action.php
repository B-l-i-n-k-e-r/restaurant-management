<?php
// kitchen_action.php
include('rms.php');
$object = new rms();

if(isset($_POST["action"])) {

    if($_POST["action"] == 'fetch_kitchen_grid') {
        
        $search_query = "";
        if(isset($_POST["search"]) && $_POST["search"] != '') {
            $search_val = $_POST["search"];
            $search_query = " AND (order_number LIKE '%$search_val%' OR order_table LIKE '%$search_val%') ";
        }

        // Fetch orders that are currently being worked on
        $object->query = "
            SELECT * FROM order_table 
            WHERE order_status IN ('In Process', 'Preparing') 
            $search_query 
            ORDER BY FIELD(order_status, 'Preparing', 'In Process'), order_id ASC
        ";

        $result = $object->get_result();
        $html = '';

        foreach($result as $row) {
            // Calculate time elapsed
            $order_time = strtotime($row["order_date"] . ' ' . $row["order_time"]);
            $diff = time() - $order_time;
            $mins_ago = round($diff / 60);

            // UPDATED STATUS UI FOR DARK MODE VISIBILITY
            if($row['order_status'] == 'Preparing') {
                $status_label = 'COOKING';
                $status_class = 'bg-warning text-dark';
            } else {
                // Changing "In Process" to "WAITING FOR CASHIER" with bright cyan text
                $status_label = 'WAITING FOR CASHIER';
                $status_class = ''; // Clear default bg
                $custom_style = 'background: rgba(0, 212, 255, 0.1); color: #00d4ff; border: 1px solid #00d4ff;';
            }
            
            // Start Ticket Card
            $html .= '
            <div class="ticket-card" id="order_'.$row["order_id"].'">
                <div class="ticket-header">
                    <div class="status-badge '.$status_class.'" style="padding: 4px 12px; border-radius: 8px; font-size: 0.75rem; font-weight: 900; '.($row['order_status'] != 'Preparing' ? $custom_style : '').'">
                        '.$status_label.'
                    </div>
                    <div class="order-time-top text-white-50 small">
                        '.date('H:i A', $order_time).'
                    </div>
                </div>

                <div class="ticket-body" style="padding: 15px 20px;">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h4 class="order-id m-0" style="color: #0ea5e9;">#ORD-'.$row["order_number"].'</h4>
                    </div>
                    <div class="table-info text-white-50 small mb-3">
                        Table: <span class="text-white font-weight-bold">'.$row["order_table"].'</span>
                    </div>
                    
                    <div class="order-items-list" style="border-top: 1px solid rgba(255,255,255,0.05); padding-top: 15px;">';

            // Fetch Items
            $stmt = $object->connect->prepare("SELECT * FROM order_item_table WHERE order_id = :id");
            $stmt->execute(['id' => $row["order_id"]]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach($items as $item) {
                $html .= '
                <div class="order-item d-flex align-items-center mb-2">
                    <span class="item-qty mr-3" style="color: #0ea5e9; font-weight: bold;">'.$item["product_quantity"].'x</span>
                    <span class="item-name text-white">'.$item["product_name"].'</span>
                </div>';
            }

            $html .= '</div></div>'; // End Body

            // Ticket Footer / Action Section
            $html .= '
                <div class="ticket-meta mt-auto" style="padding: 15px 20px; background: rgba(255,255,255,0.02); display: flex; justify-content: space-between; align-items: center; border-top: 1px solid rgba(255,255,255,0.05);">
                    <div class="time-ago" style="font-size: 0.7rem; font-weight: bold; color: rgba(255,255,255,0.3);">
                        '.($mins_ago > 1440 ? "LONG AGO" : $mins_ago . " MINS AGO").'
                    </div>
                    <div class="actions">';
            
            if($row['order_status'] == 'In Process') {
                $html .= '<button type="button" class="btn btn-outline-info btn-sm update_status" data-id="'.$row["order_id"].'" data-status="Preparing" style="font-weight: 800; border-radius: 8px;"><i class="fas fa-fire"></i> START</button>';
            } else {
                $html .= '<button type="button" class="btn btn-success btn-sm update_status" data-id="'.$row["order_id"].'" data-status="Completed" style="font-weight: 800; border-radius: 8px;"><i class="fas fa-check"></i> READY</button>';
            }

            $html .= '
                    </div>
                </div>
            </div>';
        }

        if(empty($result)) {
            $html = '<div class="col-12 text-center opacity-50 mt-5"><h3>NO LIVE TICKETS</h3></div>';
        }

        echo $html;
        exit;
    }

    if($_POST['action'] == 'update_order_status') {
        $order_id = $_POST['order_id'];
        $status = $_POST['status'];
        $user = $_SESSION['user_name'] ?? 'Kitchen';

        if($status == 'Completed') {
            $object->query = "UPDATE order_table SET order_status = :status, order_cashier = :user WHERE order_id = :id";
            $params = [':status' => $status, ':user' => $user, ':id' => $order_id];
        } else {
            $object->query = "UPDATE order_table SET order_status = :status WHERE order_id = :id";
            $params = [':status' => $status, ':id' => $order_id];
        }

        if($object->execute($params)) {
            echo 'success';
        }
        exit;
    }
}
?>