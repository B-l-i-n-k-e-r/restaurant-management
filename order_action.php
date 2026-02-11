<?php
include('rms.php');
$object = new rms();

if(isset($_POST["action"])) {

    // 1. ADD TO CART
    if($_POST["action"] == 'add') {
        $product_id = $_POST['product_id'];
        $object->query = "SELECT * FROM product_table WHERE product_id = :id";
        $object->execute([':id' => $product_id]);
        $product = $object->statement_result()[0];

        $cart_item = array(
            'id'       => $product['product_id'],
            'name'     => $product['product_name'],
            'price'    => $product['product_price'],
            'quantity' => 1
        );

        if(isset($_SESSION['cart'])) {
            $item_array_id = array_column($_SESSION['cart'], "id");
            if(!in_array($product_id, $item_array_id)) {
                $_SESSION['cart'][] = $cart_item;
            } else {
                foreach($_SESSION['cart'] as $keys => $values) {
                    if($_SESSION['cart'][$keys]['id'] == $product_id) {
                        $_SESSION['cart'][$keys]['quantity']++;
                    }
                }
            }
        } else {
            $_SESSION['cart'][] = $cart_item;
        }
        echo json_encode(['cart_count' => count($_SESSION['cart'])]);
        exit;
    }

    // 2. FETCH CART MODAL
    if($_POST["action"] == 'fetch_cart') {
        $output = '';
        if(!empty($_SESSION['cart'])) {
            $total = 0;
            foreach($_SESSION['cart'] as $item) {
                $output .= '
                <div class="d-flex justify-content-between align-items-center mb-3 p-2 rounded" style="background: rgba(255,255,255,0.05);">
                    <div>
                        <h6 class="mb-0">'.$item['name'].'</h6>
                        <small class="text-white-50">'.$item['quantity'].' x '.$object->cur.' '.$item['price'].'</small>
                    </div>
                    <div class="text-right">
                        <div class="font-weight-bold mb-1">'.number_format($item['quantity'] * $item['price'], 2).'</div>
                        <button class="btn btn-sm btn-outline-danger border-0 remove_cart_item" data-id="'.$item['id'].'"><i class="fas fa-trash-alt"></i></button>
                    </div>
                </div>';
                $total += ($item['quantity'] * $item['price']);
            }
            $output .= '
            <hr class="border-secondary">
            <div class="d-flex justify-content-between h5 font-weight-bold px-2">
                <span>Total</span>
                <span class="text-success">'.$object->cur.' '.number_format($total, 2).'</span>
            </div>';
        } else {
            $output = '<div class="text-center py-4 text-white-50"><i class="fas fa-shopping-basket fa-3x mb-3"></i><p>Your cart is empty</p></div>';
        }
        echo $output;
        exit;
    }

    // 3. REMOVE FROM CART
    if($_POST["action"] == 'remove_cart_item') {
        if(isset($_SESSION['cart'])) {
            foreach($_SESSION['cart'] as $keys => $values) {
                if($values['id'] == $_POST['product_id']) {
                    unset($_SESSION['cart'][$keys]);
                }
            }
            $_SESSION['cart'] = array_values($_SESSION['cart']); 
        }
        echo json_encode(['cart_count' => count($_SESSION['cart'] ?? [])]);
        exit;
    }

    // 4. FETCH HISTORY (DataTables)
    if($_POST["action"] == 'fetch_history') {
        $order_column = array('order_number', 'order_date', 'order_table', 'order_net_amount');
        $main_query = "SELECT * FROM order_table WHERE order_status = 'Completed' ";
        
        $search_query = "";
        if(!empty($_POST["search"]["value"])) {
            $search_query = "AND (order_number LIKE :search OR order_table LIKE :search) ";
        }

        $order_query = "ORDER BY " . $order_column[$_POST['order']['0']['column']] . " " . $_POST['order']['0']['dir'] . " ";
        $limit_query = "";
        if($_POST["length"] != -1) {
            $limit_query = 'LIMIT ' . $_POST['start'] . ', ' . $_POST['length'];
        }

        $object->query = $main_query . $search_query;
        $total_rows = $object->row_count();

        $object->query = $main_query . $search_query . $order_query . $limit_query;
        
        if(!empty($_POST["search"]["value"])) {
            $object->execute([':search' => '%' . $_POST["search"]["value"] . '%']);
        } else {
            $object->execute();
        }

        $result = $object->statement_result();
        $data = array();

        foreach($result as $row) {
            $data[] = array(
                $row["order_number"],
                $row["order_date"],
                $row["order_table"],
                $object->cur . ' ' . number_format($row["order_net_amount"], 2),
                '<button class="btn btn-sm btn-info print_receipt" data-id="'.$row["order_id"].'"><i class="fas fa-print"></i></button>
                 <button class="btn btn-sm btn-light view_history" data-id="'.$row["order_id"].'"><i class="fas fa-eye"></i></button>'
            );
        }

        echo json_encode([
            "draw" => intval($_POST["draw"]),
            "recordsTotal" => $total_rows,
            "recordsFiltered" => $total_rows,
            "data" => $data
        ]);
        exit;
    }

    // 5. FETCH MODERN TABLES
    if($_POST["action"] == 'fetch_modern_tables') {
        $object->query = "SELECT * FROM table_data WHERE table_status = 'Enable' ORDER BY table_name ASC";
        $result = $object->get_result();
        $output = '<div class="table-grid">';
        foreach($result as $row) {
            $object->query = "SELECT order_id FROM order_table WHERE order_table = :tbl AND order_status = 'In Process'";
            $object->execute([':tbl' => $row['table_name']]);
            $order = $object->statement_result();
            
            $order_id = !empty($order) ? $order[0]['order_id'] : 0;
            $status = ($order_id > 0) ? 'occupied' : 'available';
            $dot = ($order_id > 0) ? 'bg-warning' : 'bg-success';
            
            $output .= '
            <div class="table-item '.$status.'" data-table_name="'.$row['table_name'].'" data-order_id="'.$order_id.'">
                <div class="status-dot '.$dot.'"></div>
                <div class="mb-1 opacity-50"><i class="fas fa-couch"></i></div>
                <div class="font-weight-bold">'.$row['table_name'].'</div>
                <div class="small opacity-50">Cap: '.$row['table_capacity'].'</div>
            </div>';
        }
        echo $output . '</div>';
        exit;
    }

    // 6. FETCH ORDER PREVIEW
    if($_POST["action"] == 'fetch_order_preview') {
        $order_id = $_POST["order_id"];
        $object->query = "SELECT * FROM order_table WHERE order_id = :id";
        $object->execute([':id' => $order_id]);
        $order = $object->statement_result()[0];
        
        $output = '
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0">Table '.$order['order_table'].'</h4>
                <small class="text-white-50">Order #'.$order['order_number'].'</small>
            </div>
            <button class="btn btn-sm btn-info print_receipt" data-id="'.$order_id.'"><i class="fas fa-print"></i></button>
        </div>';

        $output .= '<div class="item-scroll mb-3">';
        $object->query = "SELECT * FROM order_item_table WHERE order_id = :id";
        $object->execute([':id' => $order_id]);
        foreach($object->statement_result() as $item) {
            $output .= '<div class="d-flex justify-content-between mb-2 small"><span>'.$item['product_quantity'].'x '.$item['product_name'].'</span><span>'.number_format($item['product_amount'], 2).'</span></div>';
        }
        $output .= '</div><hr class="border-secondary">';
        
        $output .= '
        <div class="d-flex justify-content-between h5 mb-4">
            <span>Total</span>
            <span class="text-success">'.$object->cur.' '.number_format($order['order_net_amount'], 2).'</span>
        </div>';

        if($order['order_status'] == 'In Process') {
            $output .= '<button class="btn btn-success btn-block btn-lg settle_order_btn" data-id="'.$order_id.'"><i class="fas fa-check-circle mr-2"></i> Settle Order</button>';
        } else {
            $output .= '<div class="alert alert-secondary text-center py-2 small">Completed by '.$order['order_cashier'].'</div>';
        }
        echo $output;
        exit;
    }

    // 7. SETTLE ORDER
    if($_POST["action"] == 'settle_order') {
        $order_id = $_POST["order_id"];
        $cashier = $object->Get_user_name($_SESSION['user_id']);
        $data = [':status' => 'Completed', ':cashier' => $cashier, ':id' => $order_id];
        $object->query = "UPDATE order_table SET order_status = :status, order_cashier = :cashier WHERE order_id = :id";
        if($object->execute($data)) echo 'success';
        exit;
    }

    // 8. SUBMIT CART TO TABLE (The Redirect Trigger)
    if($_POST["action"] == 'submit_cart_to_table') {
        if(empty($_SESSION['cart'])) { echo 'empty'; exit; }
        
        $table = $_POST['table_name'];
        $waiter = $object->Get_user_name($_SESSION['user_id']);
        $total = 0;
        foreach($_SESSION['cart'] as $i) { $total += ($i['price'] * $i['quantity']); }

        $data = [
            ':order_number'       => $object->Generate_order_no(),
            ':order_table'        => $table,
            ':order_gross_amount' => $total,
            ':order_tax_amount'   => 0,
            ':order_net_amount'   => $total,
            ':order_date'         => date('Y-m-d'),
            ':order_time'         => date('H:i:s'),
            ':order_waiter'       => $waiter,
            ':order_cashier'      => '',
            ':order_status'       => 'In Process'
        ];

        $object->query = "
            INSERT INTO order_table 
            (order_number, order_table, order_gross_amount, order_tax_amount, order_net_amount, order_date, order_time, order_waiter, order_cashier, order_status) 
            VALUES (:order_number, :order_table, :order_gross_amount, :order_tax_amount, :order_net_amount, :order_date, :order_time, :order_waiter, :order_cashier, :order_status)
        ";
        
        if($object->execute($data)) {
            $id = $object->connect->lastInsertId();
            foreach($_SESSION['cart'] as $item) {
                $object->query = "
                    INSERT INTO order_item_table 
                    (order_id, product_name, product_quantity, product_rate, product_amount) 
                    VALUES (:id, :name, :qty, :rate, :amt)
                ";
                $object->execute([
                    ':id' => $id, 
                    ':name' => $item['name'], 
                    ':qty' => $item['quantity'], 
                    ':rate' => $item['price'], 
                    ':amt' => ($item['price'] * $item['quantity'])
                ]);
            }
            unset($_SESSION['cart']); 
            echo 'success';
        }
        exit;
    }

    // 9. ADMIN PENDING
    if($_POST["action"] == 'fetch_admin_pending') {
        $object->query = "SELECT * FROM order_table WHERE order_status = 'In Process' ORDER BY order_id DESC";
        $result = $object->get_result();
        $output = '';
        foreach($result as $row) {
            $output .= '
            <tr class="order-row" data-id="'.$row["order_id"].'">
                <td class="pl-4">'.$row["order_number"].'</td>
                <td><span class="badge badge-info">'.$row["order_table"].'</span></td>
                <td>'.$row["order_waiter"].'</td>
                <td class="text-success font-weight-bold">'.$object->cur.' '.number_format($row["order_net_amount"], 2).'</td>
                <td class="text-right pr-4">
                    <button class="btn btn-dark btn-sm print_receipt" data-id="'.$row["order_id"].'"><i class="fas fa-print"></i></button>
                </td>
            </tr>';
        }
        echo $output ?: '<tr><td colspan="5" class="text-center py-4 opacity-50">No active orders</td></tr>';
        exit;
    }
}
?>