<?php
// order_action.php
include('rms.php');
$object = new rms();

if(isset($_POST["action"]))
{
    // --- FETCH ORDER HISTORY (For DataTables) ---
    if($_POST["action"] == 'fetch_history')
    {
        $order_column = array('order_number', 'order_table', 'order_waiter', 'order_net_amount', 'order_date', 'order_id');
        $main_query = "SELECT * FROM order_table WHERE order_status = 'Completed' ";

        $search_query = '';
        if(isset($_POST["search"]["value"]))
        {
            $search_query .= 'AND (order_number LIKE "%'.$_POST["search"]["value"].'%" ';
            $search_query .= 'OR order_table LIKE "%'.$_POST["search"]["value"].'%" ';
            $search_query .= 'OR order_waiter LIKE "%'.$_POST["search"]["value"].'%" ';
            $search_query .= 'OR order_date LIKE "%'.$_POST["search"]["value"].'%") ';
        }

        if(isset($_POST["order"]))
            $order_query = 'ORDER BY '.$order_column[$_POST['order']['0']['column']].' '.$_POST['order']['0']['dir'].' ';
        else
            $order_query = 'ORDER BY order_id DESC ';

        $limit_query = ($_POST["length"] != -1) ? 'LIMIT ' . $_POST['start'] . ', ' . $_POST['length'] : '';

        $object->query = $main_query . $search_query . $order_query;
        $object->execute();
        $filtered_rows = $object->row_count();

        $object->query .= $limit_query;
        $result = $object->get_result();

        $object->query = $main_query;
        $object->execute();
        $total_rows = $object->row_count();

        $data = array();
        foreach($result as $row)
        {
            $sub_array = array();
            $sub_array[] = $row["order_number"];
            $sub_array[] = htmlspecialchars($row["order_table"]);
            $sub_array[] = htmlspecialchars($row["order_waiter"]);
            $sub_array[] = $object->cur . number_format($row["order_net_amount"], 2);
            $sub_array[] = $row["order_date"];
            $sub_array[] = '<div align="center"><button type="button" class="btn btn-info btn-sm print_button" data-id="'.$row["order_id"].'"><i class="fas fa-print"></i> Print</button></div>';
            $data[] = $sub_array;
        }

        echo json_encode(["draw" => intval($_POST["draw"]), "recordsTotal" => $total_rows, "recordsFiltered" => $filtered_rows, "data" => $data]);
    }

    // --- RESET TABLE STATUS ---
    if($_POST["action"] == 'reset')
    {
        $object->query = "SELECT * FROM table_data WHERE table_status = 'Enable' ORDER BY table_id ASC";
        $tables = $object->get_result();
        $html = '';
        foreach($tables as $table)
        {
            $object->query = "SELECT * FROM order_table WHERE order_table = :table_name AND order_status = 'In Process'";
            $object->execute([':table_name' => $table['table_name']]);
            if($object->row_count() > 0)
            {
                $order = $object->statement_result()[0];
                $html .= '<button type="button" class="btn btn-warning mb-4 table_button" data-index="'.$table["table_id"].'" data-order_id="'.$order["order_id"].'" data-table_name="'.htmlspecialchars($table["table_name"]).'">'.htmlspecialchars($table["table_name"]).'<br />'.intval($table["table_capacity"]).' Person</button>';
            }
            else
            {
                $html .= '<button type="button" class="btn btn-secondary mb-4 table_button" data-index="'.$table["table_id"].'" data-order_id="0" data-table_name="'.htmlspecialchars($table["table_name"]).'">'.htmlspecialchars($table["table_name"]).'<br />'.intval($table["table_capacity"]).' Person</button>';
            }
        }
        echo $html;
    }

    // --- FETCH ORDER ITEMS (Updated: Plain Quantity & Selective Delete) ---
    if($_POST["action"] == 'fetch_order')
    {
        $object->query = "
            SELECT oi.*, ot.order_waiter 
            FROM order_item_table oi 
            JOIN order_table ot ON ot.order_id = oi.order_id 
            WHERE oi.order_id = :order_id 
            ORDER BY oi.order_item_id ASC";
        
        $object->execute([':order_id' => $_POST['order_id']]);
        $result = $object->statement_result();
        
        $is_waiter = $object->is_waiter_user();

        $html = '<table class="table table-striped table-bordered">
            <tr>
                <th>Item Name</th>
                <th class="text-center">Qty</th>
                <th>Rate</th>
                <th>Amount</th>
                <th>Waiter</th>
            </tr>';
        foreach($result as $row)
        {
            // Delete button ONLY for waiters
            $delete_btn = ($is_waiter) ? '<button type="button" class="btn btn-danger btn-sm remove_item float-right" data-item_id="'.$row["order_item_id"].'" data-order_id="'.$row["order_id"].'"><i class="fas fa-minus-square"></i></button>' : '';

            $html .= '<tr>
                <td>'.htmlspecialchars($row["product_name"]).'</td>
                <td class="text-center">'.$row["product_quantity"].'</td>
                <td>'.$object->cur . $row["product_rate"].'</td>
                <td>'.$object->cur . $row["product_amount"].'</td>
                <td>'.htmlspecialchars($row["order_waiter"]).' '.$delete_btn.'</td>
            </tr>';
        }
        $html .= '</table>';
        echo $html;
    }

    // --- ADD ORDER OR ITEM ---
    if($_POST["action"] == 'Add')
    {
        if(!$object->is_waiter_user() && !$object->is_master_user()) { exit("Unauthorized"); }
        $order_id = $_POST['hidden_order_id'];
        $product_amount = $_POST['product_quantity'] * $_POST['hidden_product_rate'];

        if($order_id > 0)
        {
            $object->query = "INSERT INTO order_item_table (order_id, product_name, product_quantity, product_rate, product_amount) VALUES (:order_id, :product_name, :product_quantity, :product_rate, :product_amount)";
            $object->execute([':order_id' => $order_id, ':product_name' => $_POST['product_name'], ':product_quantity' => $_POST['product_quantity'], ':product_rate' => $_POST['hidden_product_rate'], ':product_amount' => $product_amount]);
            echo $order_id;
        }
        else
        {
            $order_no = $object->Generate_order_no();
            $object->query = "INSERT INTO order_table (order_number, order_table, order_gross_amount, order_tax_amount, order_net_amount, order_date, order_time, order_waiter, order_cashier, order_status) VALUES (:order_number, :order_table, 0, 0, 0, :order_date, :order_time, :order_waiter, '', 'In Process')";
            $object->execute([':order_number' => $order_no, ':order_table' => $_POST['hidden_table_name'], ':order_date' => date('Y-m-d'), ':order_time' => date('H:i:s'), ':order_waiter' => $object->Get_user_name($_SESSION['user_id'])]);
            $order_id = $object->connect->lastInsertId();

            $object->query = "INSERT INTO order_item_table (order_id, product_name, product_quantity, product_rate, product_amount) VALUES (:order_id, :product_name, :product_quantity, :product_rate, :product_amount)";
            $object->execute([':order_id' => $order_id, ':product_name' => $_POST['product_name'], ':product_quantity' => $_POST['product_quantity'], ':product_rate' => $_POST['hidden_product_rate'], ':product_amount' => $product_amount]);
            echo $order_id;
        }
    }

    // --- REMOVE ITEM ---
    if($_POST['action'] == 'remove_item')
    {
        if(!$object->is_waiter_user()) { exit("Unauthorized"); }
        $object->query = "DELETE FROM order_item_table WHERE order_id = :order_id AND order_item_id = :item_id";
        $object->execute([':order_id' => $_POST['order_id'], ':item_id' => $_POST['item_id']]);
        
        $object->query = "SELECT order_item_id FROM order_item_table WHERE order_id = :order_id";
        $object->execute([':order_id' => $_POST['order_id']]);
        if($object->row_count() == 0) {
            $object->query = "DELETE FROM order_table WHERE order_id = :order_id";
            $object->execute([':order_id' => $_POST['order_id']]);
        }
        echo $object->row_count();
    }

    // --- LOAD PRODUCTS ---
    if($_POST["action"] == 'load_product')
    {
        $object->query = "SELECT * FROM product_table WHERE category_name = :category_name AND product_status = 'Enable'";
        $object->execute([':category_name' => $_POST['category_name']]);
        $html = '<option value="">Select Product</option>';
        foreach($object->statement_result() as $row) { $html .= '<option value="'.htmlspecialchars($row["product_name"]).'" data-price="'.$row["product_price"].'">'.htmlspecialchars($row["product_name"]).'</option>'; }
        echo $html;
    }
}
?>