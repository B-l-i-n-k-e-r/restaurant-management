<?php
// billing_action.php

include('rms.php');

$object = new rms();

if(isset($_POST["action"]))
{
    // Fetch all orders for DataTable
    if($_POST["action"] == 'fetch')
    {
        $order_column = array('order_table', 'order_number', 'order_date', 'order_time', 'order_waiter', 'order_status');

        $main_query = "SELECT * FROM order_table ";
        $search_query = '';

        if(isset($_POST["search"]["value"]))
        {
            $search_val = $_POST["search"]["value"];
            $search_query .= 'WHERE order_table LIKE "%'.$search_val.'%" ';
            $search_query .= 'OR order_number LIKE "%'.$search_val.'%" ';
            $search_query .= 'OR order_date LIKE "%'.$search_val.'%" ';
            $search_query .= 'OR order_time LIKE "%'.$search_val.'%" ';
            $search_query .= 'OR order_waiter LIKE "%'.$search_val.'%" ';
            $search_query .= 'OR order_status LIKE "%'.$search_val.'%" ';
        }

        $order_query = isset($_POST["order"]) ? 
            'ORDER BY '.$order_column[$_POST['order']['0']['column']].' '.$_POST['order']['0']['dir'].' ' :
            'ORDER BY order_id DESC ';

        $limit_query = ($_POST["length"] != -1) ? 'LIMIT ' . $_POST['start'] . ', ' . $_POST['length'] : '';

        // Count filtered rows
        $object->query = $main_query . $search_query . $order_query;
        $object->execute();
        $filtered_rows = $object->row_count();

        // Get paginated result
        $object->query .= $limit_query;
        $result = $object->get_result();

        // Count total rows
        $object->query = $main_query;
        $object->execute();
        $total_rows = $object->row_count();

        $data = array();

        foreach($result as $row)
        {
            $sub_array = array();
            $sub_array[] = $row["order_table"];
            $sub_array[] = $row["order_number"];
            $sub_array[] = $row["order_date"];
            $sub_array[] = $row["order_time"];
            $sub_array[] = $row["order_waiter"];
            if($object->is_master_user())
            {
                $sub_array[] = $row["order_cashier"];
            }

            $status = '';
            $print = '';
            if($row["order_status"] == 'In Process')
            {
                $status = '<button type="button" class="btn btn-warning btn-sm">In Process</button>';
            }
            else
            {
                $status = '<button type="button" class="btn btn-success btn-sm">Completed</button>';
                $print = '<a href="print.php?action=print&order_id='.$row["order_id"].'" class="btn btn-warning btn-sm btn-circle"><i class="fas fa-file-pdf"></i></a>&nbsp;';
            }

            $sub_array[] = $status;
            $sub_array[] = '
                <div align="center">
                    <button type="button" class="btn btn-primary btn-circle btn-sm view_button" data-id="'.$row["order_id"].'"><i class="fas fa-eye"></i></button>
                    &nbsp;'.$print.'
                    <button type="button" class="btn btn-danger btn-circle btn-sm delete_button" data-id="'.$row["order_id"].'"><i class="fas fa-times"></i></button>
                </div>
            ';

            $data[] = $sub_array;
        }

        $output = array(
            "draw" => intval($_POST["draw"]),
            "recordsTotal" => $total_rows,
            "recordsFiltered" => $filtered_rows,
            "data" => $data
        );

        echo json_encode($output);
    }

    // Fetch single order details (Updated: Plain Quantity & Waiter Column)
    if($_POST["action"] == 'fetch_single')
    {
        // Get order items and join with order_table to get the waiter name
        $object->query = "
            SELECT oi.*, ot.order_waiter 
            FROM order_item_table oi 
            JOIN order_table ot ON ot.order_id = oi.order_id 
            WHERE oi.order_id = '".$_POST['order_id']."' 
            ORDER BY oi.order_item_id ASC";
        
        $order_items = $object->get_result();

        $html = '
        <table class="table table-striped table-bordered">
            <tr>
                <th width="5%">Sr#</th>
                <th width="40%">Item Name</th>
                <th width="15%" class="text-center">Quantity</th>
                <th width="15%">Rate</th>
                <th width="15%">Amount</th>
                <th width="10%">Waiter</th>
            </tr>
        ';

        $count = 1;
        $gross_total = 0;

        foreach($order_items as $row)
        {
            $html .= '
            <tr>
                <td>'.$count.'</td>
                <td>'.htmlspecialchars($row["product_name"]).'</td>
                <td class="text-center">'.$row["product_quantity"].'</td>
                <td>'.$object->cur . $row["product_rate"].'</td>
                <td>'.$object->cur . $row["product_amount"].'</td>
                <td>'.htmlspecialchars($row["order_waiter"]).'</td>
            </tr>';
            $count++;
            $gross_total += $row["product_amount"];
        }

        // Calculate taxes
        $object->query = "SELECT * FROM tax_table WHERE tax_status = 'Enable' ORDER BY tax_id ASC";
        $taxes = $object->get_result();

        // Clear previous taxes
        $object->query = "DELETE FROM order_tax_table WHERE order_id = '".$_POST['order_id']."'";
        $object->execute();

        $total_tax_amt = 0;

        foreach($taxes as $tax)
        {
            $tax_amt = ($gross_total * $tax["tax_percentage"]) / 100;
            $total_tax_amt += $tax_amt;

            $tax_data = array(
                ':order_id' => $_POST['order_id'],
                ':order_tax_name' => $tax["tax_name"],
                ':order_tax_percentage' => $tax["tax_percentage"],
                ':order_tax_amount' => $tax_amt
            );

            $object->query = "INSERT INTO order_tax_table (order_id, order_tax_name, order_tax_percentage, order_tax_amount)
                              VALUES (:order_id, :order_tax_name, :order_tax_percentage, :order_tax_amount)";
            $object->execute($tax_data);
        }

        $net_total = $gross_total + $total_tax_amt;

        // Update order totals
        $order_data = array(
            ':order_gross_amount' => $gross_total,
            ':order_tax_amount' => $total_tax_amt,
            ':order_net_amount' => $net_total,
            ':order_cashier' => $object->Get_user_name($_SESSION['user_id'])
        );

        $object->query = "UPDATE order_table 
                          SET order_gross_amount = :order_gross_amount, 
                              order_tax_amount = :order_tax_amount, 
                              order_net_amount = :order_net_amount, 
                              order_cashier = :order_cashier 
                          WHERE order_id = '".$_POST["order_id"]."'";
        $object->execute($order_data);

        $html .= '
            <tr>
                <td colspan="4" class="text-right"><b>Total</b></td>
                <td colspan="2">'.$object->cur . number_format($gross_total, 2, '.', '').'</td>
            </tr>
            <tr>
                <td colspan="4" class="text-right"><b>Net Amount</b></td>
                <td colspan="2">'.$object->cur . number_format($net_total, 2, '.', '').'</td>
            </tr>
        </table>';

        echo $html;
    }

    // Mark order as completed (Print)
    if($_POST["action"] == 'Edit')
    {
        $order_data = array(
            ':order_date' => date('Y-m-d'),
            ':order_time' => date('H:i:s'),
            ':order_cashier' => $object->Get_user_name($_SESSION['user_id']),
            ':order_status' => 'Completed'
        );

        $object->query = "UPDATE order_table 
                          SET order_date = :order_date, 
                              order_time = :order_time, 
                              order_cashier = :order_cashier, 
                              order_status = :order_status 
                          WHERE order_id = '".$_POST["hidden_order_id"]."'";
        $object->execute($order_data);

        echo $_POST["hidden_order_id"];
    }

    // Remove an order completely
    if($_POST["action"] == 'remove_bill')
    {
        $order_id = $_POST["order_id"];

        $object->query = "DELETE FROM order_table WHERE order_id = '$order_id'";
        $object->execute();

        $object->query = "DELETE FROM order_item_table WHERE order_id = '$order_id'";
        $object->execute();

        $object->query = "DELETE FROM order_tax_table WHERE order_id = '$order_id'";
        $object->execute();

        echo '<div class="alert alert-success">Order removed successfully...</div>';
    }
}
?>