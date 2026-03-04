<?php
// kitchen_action.php
include('rms.php');
$object = new rms();

if(isset($_POST["action"])) {

    if($_POST["action"] == 'fetch_production') {
        
        $order_column = array('order_number', 'order_table', NULL, 'order_id', 'order_status', NULL);

        // STUBBORN QUERY: Force both statuses
        $main_query = "SELECT * FROM order_table WHERE order_status IN ('In Process', 'Preparing') ";

        $search_query = "";
        if(isset($_POST["search"]["value"]) && $_POST["search"]["value"] != '') {
            $search_val = $_POST["search"]["value"];
            $search_query = 'AND (order_number LIKE "%'.$search_val.'%" ';
            $search_query .= 'OR order_table LIKE "%'.$search_val.'%" ';
            $search_query .= 'OR order_status LIKE "%'.$search_val.'%") ';
        }

        $order_query = "ORDER BY FIELD(order_status, 'Preparing', 'In Process'), order_id ASC ";
        
        if(isset($_POST["order"])) {
            $col_index = $_POST['order']['0']['column'];
            $dir = $_POST['order']['0']['dir'];
            if($order_column[$col_index] != NULL) {
                $order_query = 'ORDER BY '.$order_column[$col_index].' '.$dir.' ';
            }
        }

        $limit_query = '';
        if($_POST["length"] != -1) {
            $limit_query = 'LIMIT ' . $_POST['start'] . ', ' . $_POST['length'];
        }

        // Count rows first
        $object->query = $main_query . $search_query;
        $object->execute();
        $total_rows = $object->row_count();

        // Get Actual Data
        $object->query = $main_query . $search_query . $order_query . $limit_query;
        $result = $object->get_result();
        $data = array();

        foreach($result as $row) {
            $sub_array = array();
            
            $sub_array[] = '<strong>#'.$row["order_number"].'</strong>';
            $sub_array[] = '<span class="badge badge-info">'.$row["order_table"].'</span>';

            // FIX: DO NOT use $object->get_result() inside the loop. 
            // It overwrites the parent query. Use a direct PDO call instead.
            $stmt = $object->connect->prepare("SELECT * FROM order_item_table WHERE order_id = :id");
            $stmt->execute(['id' => $row["order_id"]]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $item_list = '<ul class="list-unstyled mb-0 small">';
            $total_qty = 0;
            foreach($items as $item) {
                $item_list .= '<li>'.$item["product_quantity"].' x '.$item["product_name"].'</li>';
                $total_qty += $item["product_quantity"];
            }
            $item_list .= '</ul>';
            $sub_array[] = $item_list;

            $sub_array[] = '<span class="font-weight-bold">'.$total_qty.'</span>';

            // Status Badge
            if($row['order_status'] == 'In Process') {
                $sub_array[] = '<span class="status-waiting"><i class="fas fa-clock"></i> Waiting</span>';
                $sub_array[] = '<button type="button" class="btn btn-warning btn-sm update_status btn-action" data-id="'.$row["order_id"].'" data-status="Preparing">Start Cook</button>';
            } else {
                $sub_array[] = '<span class="status-preparing"><i class="fas fa-fire"></i> Cooking</span>';
                $sub_array[] = '<button type="button" class="btn btn-success btn-sm update_status btn-action" data-id="'.$row["order_id"].'" data-status="Completed">Mark Ready</button>';
            }

            $data[] = $sub_array;
        }

        $output = array(
            "draw"            => intval($_POST["draw"]),
            "recordsTotal"    => $total_rows,
            "recordsFiltered" => $total_rows,
            "data"            => $data
        );
        echo json_encode($output);
        exit;
    }

    if($_POST['action'] == 'update_order_status') {
        $order_id = $_POST['order_id'];
        $status = $_POST['status'];
        $cashier = $_SESSION['user_name'] ?? 'Kitchen';

        if($status == 'Completed') {
            $object->query = "UPDATE order_table SET order_status = :status, order_cashier = :cashier WHERE order_id = :id";
            $params = [':status' => $status, ':cashier' => $cashier, ':id' => $order_id];
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