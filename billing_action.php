<?php
// billing_action.php

include('rms.php');
$object = new rms();

if(isset($_POST["action"]))
{
    /* =========================================================
        FETCH ALL ORDERS (DATATABLE)
    ========================================================= */
    if($_POST["action"] == 'fetch')
    {
        $order_column = array('order_table', 'order_number', 'order_date', 'order_time', 'order_waiter', 'order_status');

        $main_query = "SELECT * FROM order_table ";
        $search_query = '';

        if(isset($_POST["search"]["value"]) && $_POST["search"]["value"] != '')
        {
            $search_val = $_POST["search"]["value"];
            $search_query .= 'WHERE (order_table LIKE "%'.$search_val.'%" ';
            $search_query .= 'OR order_number LIKE "%'.$search_val.'%" ';
            $search_query .= 'OR order_date LIKE "%'.$search_val.'%" ';
            $search_query .= 'OR order_time LIKE "%'.$search_val.'%" ';
            $search_query .= 'OR order_waiter LIKE "%'.$search_val.'%" ';
            $search_query .= 'OR order_status LIKE "%'.$search_val.'%" )';
        }

        $order_query = (isset($_POST["order"])) ? 
            'ORDER BY '.$order_column[$_POST['order']['0']['column']].' '.$_POST['order']['0']['dir'].' ' :
            'ORDER BY order_id DESC ';

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
            $sub_array[] = '<span class="font-weight-bold text-white">'.$row["order_table"].'</span>';
            $sub_array[] = $row["order_number"];
            $sub_array[] = $row["order_date"];
            $sub_array[] = $row["order_time"];
            
            // Logic: If table is Self-Order, show Self-Service as Waiter
            if($row["order_table"] == 'Self-Order') {
                $sub_array[] = '<span class="badge badge-info">Self-Service</span>';
            } else {
                $sub_array[] = $row["order_waiter"];
            }
            
            if($object->is_master_user())
            {
                $sub_array[] = '<small class="text-white-50">'.($row["order_cashier"] ? $row["order_cashier"] : "Not Settled").'</small>';
            }

            if($row["order_status"] == 'Completed')
            {
                $status = '<span class="badge badge-paid">Settled</span>';
            }
            else
            {
                $status = '<span class="badge badge-pending">Pending</span>';
            }
            $sub_array[] = $status;

            /* --- ACTION BUTTON LOGIC --- */
            
            // VIEW BUTTON: Disable for Admin if status is NOT Completed
            $view_attr = '';
            $view_class = 'btn-glass-info';
            
            if($object->is_master_user() && $row["order_status"] != 'Completed') {
                $view_attr = 'disabled style="opacity: 0.4; cursor: not-allowed;"';
                $view_class = 'btn-secondary'; 
            }

            $action_btns = '<button type="button" class="btn '.$view_class.' btn-circle btn-sm view_button" data-id="'.$row["order_id"].'" '.$view_attr.'><i class="fas fa-eye"></i></button>&nbsp;';
            $action_btns .= '<button type="button" class="btn btn-glass-info btn-circle btn-sm print_button" onclick="window.open(\'print.php?order_id='.$row["order_id"].'\', \'_blank\')"><i class="fas fa-print"></i></button>&nbsp;';

            // DELETE BUTTON: Only for Cashiers
            if($object->is_cashier_user())
            {
                $action_btns .= '<button type="button" class="btn btn-glass-danger btn-circle btn-sm delete_button" data-id="'.$row["order_id"].'"><i class="fas fa-trash"></i></button>';
            }

            $sub_array[] = '<div class="text-right">'.$action_btns.'</div>';
            $data[] = $sub_array;
        }

        $output = array(
            "draw"            => intval($_POST["draw"]),
            "recordsTotal"    => $total_rows,
            "recordsFiltered" => $filtered_rows,
            "data"            => $data
        );
        echo json_encode($output);
    }

    /* =========================================================
        FETCH SINGLE ORDER DETAILS (MODAL)
    ========================================================= */
    if($_POST["action"] == 'fetch_single')
    {
        $order_id = $_POST['order_id'];

        $object->query = "SELECT * FROM order_table WHERE order_id = '".$order_id."'";
        $order_row = $object->get_result()[0];

        $object->query = "SELECT * FROM order_item_table WHERE order_id = '".$order_id."' ORDER BY order_item_id ASC";
        $result = $object->get_result();

        $db_payment_method = (!empty($order_row['payment_method'])) ? $order_row['payment_method'] : 'Cash';

        $html = '<div class="row mb-4 align-items-end">';
        
        // Cashier sees controls for Pending; Everyone else (including Admin) gets read-only
        if($object->is_cashier_user() && $order_row['order_status'] != 'Completed') {
            $html .= '
            <div class="col-md-6">
                <label class="small text-white-50">Settlement Method</label>
                <select name="payment_method" id="payment_method" class="form-control bg-dark text-white border-secondary">
                    <option value="Cash" '.($db_payment_method == 'Cash' ? 'selected' : '').'>Cash</option>
                    <option value="Card" '.($db_payment_method == 'Card' ? 'selected' : '').'>Credit/Debit Card</option>
                    <option value="M-Pesa" '.($db_payment_method == 'M-Pesa' ? 'selected' : '').'>M-Pesa</option>
                </select>
                <input type="hidden" id="order_status_check" value="'.$order_row["order_status"].'">
            </div>';
        } else {
            $html .= '
            <div class="col-md-6">
                <label class="small text-white-50">Settlement Method</label>
                <div class="h5 text-info font-weight-bold">
                    '.($order_row['order_status'] == 'Completed' ? 
                        '<i class="fas fa-check-circle mr-2"></i>Paid via '.$db_payment_method : 
                        '<i class="fas fa-user-shield mr-2"></i>Awaiting Cashier Settlement') . '
                </div>
                <input type="hidden" id="order_status_check" value="Completed"> 
            </div>';
        }

        $html .= '
            <div class="col-md-6 text-right">
                <span class="text-white-50 small">Current Status:</span> 
                <span class="ml-2 '.($order_row['order_status'] == 'Completed' ? 'text-success font-weight-bold' : 'text-warning').'">
                    '.($order_row['order_status'] == 'Completed' ? 'SETTLED' : 'PENDING').'
                </span>
            </div>
        </div>

        <div class="table-responsive">
        <table class="table table-borderless text-white">
            <thead style="background: rgba(255,255,255,0.02);">
                <tr>
                    <th width="5%">#</th>
                    <th width="45%">Item Name</th>
                    <th width="15%" class="text-center">Qty</th>
                    <th width="15%">Rate</th>
                    <th width="20%">Amount</th>
                </tr>
            </thead>
            <tbody>';

        $count = 1;
        $gross_total = 0;

        foreach($result as $row)
        {
            $html .= '
            <tr>
                <td class="text-white-50">'.$count.'</td>
                <td>'.$row["product_name"].'</td>
                <td class="text-center">'.$row["product_quantity"].'</td>
                <td>'.number_format($row["product_rate"], 2).'</td>
                <td class="font-weight-bold">'.number_format($row["product_amount"], 2).'</td>
            </tr>';
            $gross_total += $row["product_amount"];
            $count++;
        }

        $html .= '<tr><td colspan="5"><hr style="border-top: 1px solid rgba(255,255,255,0.1);"></td></tr>';

        $object->query = "SELECT * FROM tax_table WHERE tax_status = 'Enable' ORDER BY tax_id ASC";
        $tax_result = $object->get_result();
        $total_tax_amt = 0;
        foreach($tax_result as $tax)
        {
            $tax_amt = ($gross_total * $tax["tax_percentage"]) / 100;
            $total_tax_amt += $tax_amt;
            $html .= '<tr><td colspan="4" class="text-right text-white-50">'.$tax["tax_name"].' ('.$tax["tax_percentage"].'%)</td><td>'.number_format($tax_amt, 2).'</td></tr>';
        }

        $net_total = $gross_total + $total_tax_amt;

        $html .= '
            <tr>
                <td colspan="4" class="text-right"><h4 class="mb-0">Grand Total</h4></td>
                <td><h4 class="mb-0 text-info font-weight-bold">'.$object->cur . number_format($net_total, 2).'</h4></td>
            </tr>
            </tbody>
        </table>
        </div>';

        echo $html;
    }

    /* =========================================================
        SETTLE ORDER (ACTION)
    ========================================================= */
    if($_POST["action"] == 'Edit')
    {
        if(!$object->is_cashier_user()) {
            echo "Unauthorized Access.";
            exit;
        }

        $order_id = $_POST["hidden_order_id"];
        $payment_method = isset($_POST["payment_method"]) ? $_POST["payment_method"] : 'Cash';
        
        $object->query = "SELECT SUM(product_amount) as total FROM order_item_table WHERE order_id = '".$order_id."'";
        $res = $object->get_result();
        $gross = ($res[0]['total'] > 0) ? $res[0]['total'] : 0;
        
        $object->query = "SELECT * FROM tax_table WHERE tax_status = 'Enable'";
        $taxes = $object->get_result();
        $tax_total = 0;
        foreach($taxes as $t) { 
            $tax_total += ($gross * $t['tax_percentage'] / 100); 
        }
        
        $net = $gross + $tax_total;

        $order_data = array(
            ':order_date'     => date('Y-m-d'),
            ':order_time'     => date('H:i:s'),
            ':order_cashier'  => $object->Get_user_name($_SESSION['user_id']),
            ':order_status'   => 'Completed',
            ':payment_method' => $payment_method,
            ':gross'          => $gross,
            ':tax'            => $tax_total,
            ':net'            => $net,
            ':order_id'       => $order_id
        );

        $object->query = "
            UPDATE order_table 
            SET order_date = :order_date, 
                order_time = :order_time, 
                order_cashier = :order_cashier, 
                order_status = :order_status,
                payment_method = :payment_method,
                order_gross_amount = :gross,
                order_tax_amount = :tax,
                order_net_amount = :net
            WHERE order_id = :order_id
        ";
        
        $object->execute($order_data);
        echo $order_id;
    }

    /* =========================================================
        REMOVE BILL (ACTION)
    ========================================================= */
    if($_POST["action"] == 'remove_bill')
    {
        if(!$object->is_cashier_user()) {
            echo "Unauthorized Access.";
            exit;
        }

        $order_id = $_POST["order_id"];
        $object->query = "DELETE FROM order_table WHERE order_id = '".$order_id."'";
        $object->execute();
        $object->query = "DELETE FROM order_item_table WHERE order_id = '".$order_id."'";
        $object->execute();

        echo '<div class="alert alert-success">Order removed successfully.</div>';
    }
}
?>