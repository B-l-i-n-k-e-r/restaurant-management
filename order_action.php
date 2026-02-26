<?php
// order_action.php - Centralized Action Controller
include('rms.php');
$object = new rms();

if(isset($_POST["action"])) {

    // 1. ADD TO SESSION CART
    if($_POST["action"] == 'add') {
        $product_id = $_POST['product_id'];
        $object->query = "SELECT * FROM product_table WHERE product_id = :id";
        $object->execute([':id' => $product_id]);
        $result = $object->statement_result();
        
        if(!empty($result)) {
            $product = $result[0];

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
        }
        echo json_encode(['cart_count' => count($_SESSION['cart'] ?? [])]);
        exit;
    }

    // 2. GET CURRENT CART COUNT
    if($_POST["action"] == 'get_cart_count') {
        $count = 0;
        if(isset($_SESSION["cart"])) {
            foreach($_SESSION["cart"] as $item) {
                $count += $item['quantity'];
            }
        }
        echo json_encode(['cart_count' => $count]);
        exit;
    }

    // 3. FETCH CART MODAL HTML
    if($_POST["action"] == 'fetch_cart') {
        $output = '';
        if(!empty($_SESSION['cart'])) {
            $total = 0;
            foreach($_SESSION['cart'] as $item) {
                $output .= '
                <div class="d-flex justify-content-between align-items-center mb-3 p-2 rounded" style="background: rgba(255,255,255,0.05);">
                    <div>
                        <h6 class="mb-0 text-white">'.$item['name'].'</h6>
                        <small class="text-white-50">'.$item['quantity'].' x '.$object->cur.' '.$item['price'].'</small>
                    </div>
                    <div class="text-right">
                        <div class="font-weight-bold mb-1 text-white">'.number_format($item['quantity'] * $item['price'], 2).'</div>
                        <button class="btn btn-sm btn-outline-danger border-0 remove_cart_item" data-id="'.$item['id'].'"><i class="fas fa-trash-alt"></i></button>
                    </div>
                </div>';
                $total += ($item['quantity'] * $item['price']);
            }
            $output .= '
            <hr class="border-secondary">
            <div class="d-flex justify-content-between h5 font-weight-bold px-2">
                <span class="text-white">Total</span>
                <span class="text-warning">'.$object->cur.' '.number_format($total, 2).'</span>
            </div>';
        } else {
            $output = '<div class="text-center py-4 text-white-50"><i class="fas fa-shopping-basket fa-3x mb-3"></i><p>Your cart is empty</p></div>';
        }
        echo $output;
        exit;
    }

    // 4. REMOVE SINGLE ITEM FROM CART
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

    // 5. FETCH COMPLETED ORDER HISTORY (DataTables for Admin)
    if($_POST["action"] == 'fetch_history') {
        $order_column = array('order_number', 'order_date', 'order_table', 'order_cashier', 'order_net_amount');
        $main_query = "SELECT * FROM order_table WHERE order_status = 'Completed' ";
        
        $search_query = "";
        if(!empty($_POST["search"]["value"])) {
            $search_query = "AND (order_number LIKE :search OR order_table LIKE :search OR order_cashier LIKE :search) ";
        }

        $order_query = "ORDER BY " . $order_column[$_POST['order']['0']['column']] . " " . $_POST['order']['0']['dir'] . " ";
        $limit_query = ($_POST["length"] != -1) ? 'LIMIT ' . $_POST['start'] . ', ' . $_POST['length'] : "";

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
                "order_number" => '<strong>'.$row["order_number"].'</strong>',
                "order_date"   => date('M d, Y', strtotime($row["order_date"])),
                "order_table"  => '<span class="badge badge-info">'.$row["order_table"].'</span>',
                "order_cashier"=> $row["order_cashier"] ?: 'Admin',
                "order_total"  => '<span class="text-success font-weight-bold">'.$object->cur . ' ' . number_format($row["order_net_amount"], 2).'</span>',
                "action"       => '<div class="text-right">
                                    <button class="btn btn-sm btn-info print_receipt" data-id="'.$row["order_id"].'"><i class="fas fa-print"></i></button>
                                    <button class="btn btn-sm btn-outline-light view_history_btn" data-id="'.$row["order_id"].'"><i class="fas fa-eye"></i></button>
                                   </div>'
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

    // 6. FETCH MODERN TABLES GRID (Point of Sale View)
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

    // 7. FETCH ORDER PREVIEW
    if($_POST["action"] == 'fetch_order_preview') {
        $order_id = $_POST["order_id"];
        $object->query = "SELECT * FROM order_table WHERE order_id = :id";
        $object->execute([':id' => $order_id]);
        $order_res = $object->statement_result();
        
        if(!empty($order_res)){
            $order = $order_res[0];
            $output = '
            <div class="d-flex justify-content-between align-items-center mb-4 text-white">
                <div><h4 class="mb-0">Table '.$order['order_table'].'</h4><small class="text-white-50">#'.$order['order_number'].'</small></div>
                <button class="btn btn-sm btn-info print_receipt" data-id="'.$order_id.'"><i class="fas fa-print"></i></button>
            </div>';

            $output .= '<div class="item-scroll mb-3" style="max-height: 250px; overflow-y: auto;">';
            $object->query = "SELECT * FROM order_item_table WHERE order_id = :id";
            $object->execute([':id' => $order_id]);
            foreach($object->statement_result() as $item) {
                $output .= '<div class="d-flex justify-content-between mb-2 small text-white border-bottom border-secondary pb-1">
                    <span>'.$item['product_quantity'].'x '.$item['product_name'].'</span>
                    <span>'.number_format($item['product_amount'], 2).'</span>
                </div>';
            }
            $output .= '</div>
            <div class="bg-dark p-3 rounded">
                <div class="d-flex justify-content-between h4 mb-0 text-white font-weight-bold">
                    <span>Total</span><span class="text-success">'.$object->cur.' '.number_format($order['order_net_amount'], 2).'</span>
                </div>
            </div>';

            if($order['order_status'] == 'In Process') {
                $output .= '<button class="btn btn-success btn-block mt-3 settle_order_btn" data-id="'.$order_id.'">Settle Bill</button>';
            } else {
                $output .= '<div class="mt-3 text-center small text-white-50">Settled by '.$order['order_cashier'].'</div>';
            }
            echo $output; 
        }
        exit;
    }

    // 8. SUBMIT CART TO TABLE (Waiter / Admin)
    if($_POST["action"] == 'submit_cart_to_table') {
        if(empty($_SESSION['cart'])) { echo 'empty'; exit; }
        
        $table = $_POST['table_name'];
        $waiter_identifier = $_SESSION['user_id'] ?? 0; 
        
        $gross_total = 0;
        foreach($_SESSION['cart'] as $i) { $gross_total += ($i['price'] * $i['quantity']); }

        $data = [
            ':order_number'       => $object->Generate_order_no(),
            ':order_table'        => $table,
            ':order_gross_amount' => $gross_total,
            ':order_tax_amount'   => 0,
            ':order_net_amount'   => $gross_total,
            ':order_date'         => date('Y-m-d'),
            ':order_time'         => date('H:i:s'),
            ':order_waiter'       => $waiter_identifier,
            ':order_cashier'      => '',
            ':order_status'       => 'In Process'
        ];

        $object->query = "INSERT INTO order_table (order_number, order_table, order_gross_amount, order_tax_amount, order_net_amount, order_date, order_time, order_waiter, order_cashier, order_status) VALUES (:order_number, :order_table, :order_gross_amount, :order_tax_amount, :order_net_amount, :order_date, :order_time, :order_waiter, :order_cashier, :order_status)";
        
        if($object->execute($data)) {
            $id = $object->connect->lastInsertId();
            foreach($_SESSION['cart'] as $item) {
                $object->query = "INSERT INTO order_item_table (order_id, product_name, product_quantity, product_rate, product_amount) VALUES (:id, :name, :qty, :rate, :amt)";
                $object->execute([
                    ':id'   => $id, 
                    ':name' => $item['name'], 
                    ':qty'  => $item['quantity'], 
                    ':rate' => $item['price'], 
                    ':amt'  => ($item['price'] * $item['quantity'])
                ]);
            }
            unset($_SESSION['cart']); 
            echo 'success';
        }
        exit;
    }

    // 9. SETTLE ORDER (Finalizing Payment)
    if($_POST["action"] == 'settle_order') {
        $order_id = $_POST["order_id"];
        $cashier = $_SESSION['user_name'] ?? 'Admin';
        $object->query = "UPDATE order_table SET order_status = 'Completed', order_cashier = :cashier WHERE order_id = :id";
        if($object->execute([':cashier' => $cashier, ':id' => $order_id])) echo 'success';
        exit;
    }

    // 10. FETCH LIVE PENDING ORDERS (Admin Dashboard)
    if($_POST["action"] == 'fetch_admin_pending') {
        $object->query = "SELECT * FROM order_table WHERE order_status = 'In Process' ORDER BY order_id DESC";
        $result = $object->get_result();
        $output = '';
        foreach($result as $row) {
            $output .= '<tr class="order-row text-white" data-id="'.$row["order_id"].'">
                <td class="pl-4">'.$row["order_number"].'</td>
                <td><span class="badge badge-warning">'.$row["order_table"].'</span></td>
                <td>'.$row["order_waiter"].'</td>
                <td class="text-success font-weight-bold">'.$object->cur.' '.number_format($row["order_net_amount"], 2).'</td>
                <td class="text-right pr-4"><i class="fas fa-eye"></i></td>
            </tr>';
        }
        echo $output ?: '<tr><td colspan="5" class="text-center py-4 text-white-50">No pending orders</td></tr>';
        exit;
    }

    // 11. FETCH CUSTOMER ACTIVE ORDERS (Real-time tracking)
    if($_POST["action"] == 'fetch_customer_active_orders') {
        $user_name = $_SESSION['user_name'];
        $object->query = "SELECT * FROM order_table WHERE order_waiter = :user AND order_status = 'In Process' ORDER BY order_id DESC";
        $object->execute([':user' => $user_name]);
        $result = $object->statement_result();
        
        $output = '';
        if(!empty($result)) {
            foreach($result as $row) {
                $output .= '
                <div class="col-md-4 mb-4">
                    <div class="order-card text-white p-3" style="background: rgba(255,255,255,0.05); border-radius:15px;">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <span class="badge badge-warning mb-2">In Preparation</span>
                                <h5 class="mb-0 font-weight-bold">#'.$row["order_number"].'</h5>
                            </div>
                            <div class="text-right">
                                <small class="text-white-50">'.date('h:i A', strtotime($row["order_time"])).'</small>
                            </div>
                        </div>
                        <div class="small text-white-50 mb-3 border-bottom border-secondary pb-2">
                             Table Location: '.$row["order_table"].'
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="h5 mb-0 text-success">'.$object->cur.' '.number_format($row["order_net_amount"], 2).'</span>
                            <button class="btn btn-sm btn-outline-info view_receipt" data-id="'.$row["order_id"].'"><i class="fas fa-eye"></i> Details</button>
                        </div>
                    </div>
                </div>';
            }
        } else {
            $output = '<div class="col-12 text-center py-5 text-white-50"><i class="fas fa-utensils fa-3x mb-3"></i><p>No active orders in the kitchen.</p></div>';
        }
        echo $output;
        exit;
    }

    // 12. FETCH CUSTOMER HISTORY (Personal DataTables)
    if($_POST["action"] == 'fetch_customer_history') {
        $user_name = $_SESSION['user_name'];
        $order_column = array('order_number', 'order_date', 'order_table', 'order_net_amount');
        
        $main_query = "SELECT * FROM order_table WHERE order_waiter = :user AND order_status = 'Completed' ";
        
        $params = [':user' => $user_name];
        $search_query = "";
        if(!empty($_POST["search"]["value"])) {
            $search_query = "AND (order_number LIKE :search OR order_table LIKE :search) ";
            $params[':search'] = '%' . $_POST["search"]["value"] . '%';
        }

        $order_query = "ORDER BY " . $order_column[$_POST['order']['0']['column']] . " " . $_POST['order']['0']['dir'] . " ";
        $limit_query = ($_POST["length"] != -1) ? 'LIMIT ' . $_POST['start'] . ', ' . $_POST['length'] : "";

        $object->query = $main_query . $search_query;
        $object->execute($params);
        $total_rows = $object->row_count();

        $object->query = $main_query . $search_query . $order_query . $limit_query;
        $object->execute($params);
        
        $result = $object->statement_result();
        $data = array();

        foreach($result as $row) {
            $data[] = array(
                "order_number" => '<strong>'.$row["order_number"].'</strong>',
                "order_date"   => date('M d, Y', strtotime($row["order_date"])),
                "order_table"  => '<span class="badge badge-secondary">'.$row["order_table"].'</span>',
                "order_total"  => '<span class="text-success font-weight-bold">'.$object->cur . ' ' . number_format($row["order_net_amount"], 2).'</span>',
                "action"       => '<button class="btn btn-sm btn-info view_receipt" data-id="'.$row["order_id"].'"><i class="fas fa-file-invoice"></i> Receipt</button>'
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

    // 13. GET RECEIPT HTML (Thermal Paper Style)
    if($_POST["action"] == 'get_receipt_html') {
        $order_id = $_POST["order_id"];
        $object->query = "SELECT * FROM order_table WHERE order_id = :id";
        $object->execute([':id' => $order_id]);
        $order_res = $object->statement_result();

        if(!empty($order_res)){
            $order = $order_res[0];
            $output = '
            <div class="text-center text-dark" style="font-family: \'Courier New\', Courier, monospace;">
                <h4 class="font-weight-bold mb-1">WAKANESA RESTAURANT</h4>
                <p class="small mb-3">123 Culinary Drive, Food City<br>Tel: +254 700 000 000</p>
                <div style="border-top: 1px dashed #333; margin: 10px 0;"></div>
                <div class="d-flex justify-content-between small">
                    <span>Order: #'.$order['order_number'].'</span>
                    <span>Date: '.date('d/m/y', strtotime($order['order_date'])).'</span>
                </div>
                <div class="d-flex justify-content-between small">
                    <span>Table: '.$order['order_table'].'</span>
                    <span>Waitstaff: '.$order['order_waiter'].'</span>
                </div>
                <div style="border-top: 1px dashed #333; margin: 10px 0;"></div>';

            $object->query = "SELECT * FROM order_item_table WHERE order_id = :id";
            $object->execute([':id' => $order_id]);
            foreach($object->statement_result() as $item) {
                $output .= '
                <div class="d-flex justify-content-between small mb-1">
                    <span>'.$item['product_quantity'].' x '.$item['product_name'].'</span>
                    <span>'.number_format($item['product_amount'], 2).'</span>
                </div>';
            }

            $output .= '
                <div style="border-top: 1px dashed #333; margin: 10px 0;"></div>
                <div class="d-flex justify-content-between font-weight-bold">
                    <span>TOTAL</span>
                    <span>'.$object->cur.' '.number_format($order['order_net_amount'], 2).'</span>
                </div>
                <div style="border-top: 1px dashed #333; margin: 10px 0;"></div>
                <div class="mt-4 small">Thank you for dining with us!<br>See you soon!</div>
            </div>';
            echo $output;
        }
        exit;
    }

    // 14. FETCH CUSTOMER MENU (Dashboard Filtering)
    if($_POST["action"] == 'fetch_customer_menu') {
        $cat = $_POST['category_id'];
        if($cat == 'all') {
            $object->query = "SELECT * FROM product_table WHERE product_status='Enable' ORDER BY product_name ASC";
            $items = $object->get_result();
        } else {
            $object->query = "SELECT * FROM product_table WHERE product_status='Enable' AND product_category_id=:cat ORDER BY product_name ASC";
            $object->execute([':cat' => $cat]);
            $items = $object->statement_result();
        }

        $output = '';
        foreach($items as $item) {
            $img = $object->Get_product_image($item['product_id']);
            $output .= '
            <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                <div class="menu-card">
                    <div class="menu-img-container"><img src="'.$img.'" alt="'.$item['product_name'].'"></div>
                    <div class="p-4 d-flex flex-column justify-content-between flex-grow-1">
                        <div>
                            <h5 class="text-white font-weight-bold mb-2">'.$item['product_name'].'</h5>
                            <div class="price-tag mb-3">'.$object->cur.' '.number_format($item['product_price'], 2).'</div>
                        </div>
                        <button class="btn btn-warning btn-block font-weight-bold add_to_cart" style="border-radius: 12px;" data-id="'.$item['product_id'].'">Add to Cart</button>
                    </div>
                </div>
            </div>';
        }
        echo $output ?: '<div class="col-12 text-center py-5 text-white-50">No items found in this category.</div>';
        exit;
    }

    // 15. SUBMIT CUSTOMER ONLINE ORDER (Final Step)
    if($_POST["action"] == 'submit_customer_order') {
        if(empty($_SESSION['cart'])) { echo 'empty'; exit; }

        $user = $_SESSION['user_name'] ?? 'Customer';
        $gross_total = 0;
        foreach($_SESSION['cart'] as $i) $gross_total += ($i['price'] * $i['quantity']);

        $data = [
            ':order_number'       => $object->Generate_order_no(),
            ':order_table'        => 'Self-Order',
            ':order_gross_amount' => $gross_total,
            ':order_tax_amount'   => 0,
            ':order_net_amount'   => $gross_total,
            ':order_date'         => date('Y-m-d'),
            ':order_time'         => date('H:i:s'),
            ':order_waiter'       => $user,
            ':order_cashier'      => '',
            ':order_status'       => 'In Process'
        ];

        $object->query = "INSERT INTO order_table (order_number, order_table, order_gross_amount, order_tax_amount, order_net_amount, order_date, order_time, order_waiter, order_cashier, order_status) 
                          VALUES (:order_number, :order_table, :order_gross_amount, :order_tax_amount, :order_net_amount, :order_date, :order_time, :order_waiter, :order_cashier, :order_status)";

        if($object->execute($data)) {
            $id = $object->connect->lastInsertId();
            foreach($_SESSION['cart'] as $item) {
                $object->query = "INSERT INTO order_item_table (order_id, product_name, product_quantity, product_rate, product_amount) 
                                  VALUES (:id, :name, :qty, :rate, :amt)";
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
}
?>