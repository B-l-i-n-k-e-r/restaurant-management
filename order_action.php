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
                $line_total = $item['quantity'] * $item['price'];
                $output .= '
                <div class="d-flex justify-content-between align-items-center mb-3 p-2 rounded" style="background: rgba(255,255,255,0.05);">
                    <div style="flex: 1;">
                        <h6 class="mb-0 text-white" style="white-space: nowrap;">'.$item['name'].'</h6>
                        <small class="text-white-50">'.$item['quantity'].' x '.$object->cur.' '.number_format($item['price'], 2).'</small>
                    </div>
                    <div class="text-right ml-2">
                        <div class="font-weight-bold mb-1 text-white" style="white-space: nowrap;">'.number_format($line_total, 2).'</div>
                        <button class="btn btn-sm btn-outline-danger border-0 remove_cart_item" data-id="'.$item['id'].'"><i class="fas fa-trash-alt"></i></button>
                    </div>
                </div>';
                $total += $line_total;
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

    // 5. FETCH COMPLETED ORDER HISTORY (DataTables)
    if($_POST["action"] == 'fetch_history') {
        $order_column = array('order_number', 'order_date', 'order_table', 'order_cashier', 'order_net_amount');
        $main_query = "SELECT * FROM order_table WHERE order_status = 'Completed' ";
        
        $params = [];
        $search_query = "";
        if(!empty($_POST["search"]["value"])) {
            $search_query = "AND (order_number LIKE :search OR order_table LIKE :search OR order_cashier LIKE :search) ";
            $params[':search'] = '%' . $_POST["search"]["value"] . '%';
        }

        $order_query = "ORDER BY " . $order_column[$_POST['order']['0']['column']] . " " . $_POST['order']['0']['dir'] . " ";
        $limit_query = ($_POST["length"] != -1) ? 'LIMIT ' . (int)$_POST['start'] . ', ' . (int)$_POST['length'] : "";

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

    // 6. FETCH MODERN TABLES GRID
    if($_POST["action"] == 'fetch_modern_tables') {
        $object->query = "SELECT * FROM table_data WHERE table_status = 'Enable' ORDER BY table_name ASC";
        $result = $object->get_result();
        $output = '<div class="table-grid">';
        foreach($result as $row) {
            $object->query = "SELECT order_id FROM order_table WHERE order_table = :tbl AND order_status IN ('In Process', 'Preparing', 'Ready')";
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
                    <span style="white-space: nowrap;">'.number_format($item['product_amount'], 2).'</span>
                </div>';
            }
            $output .= '</div>
            <div class="bg-dark p-3 rounded">
                <div class="d-flex justify-content-between h4 mb-0 text-white font-weight-bold">
                    <span>Total</span><span class="text-success" style="white-space: nowrap;">'.$object->cur.' '.number_format($order['order_net_amount'], 2).'</span>
                </div>
            </div>';

            if($order['order_status'] != 'Completed') {
                $output .= '<button class="btn btn-success btn-block mt-3 settle_order_btn" data-id="'.$order_id.'">Settle Bill</button>';
            } else {
                $output .= '<div class="mt-3 text-center small text-white-50">Settled by '.$order['order_cashier'].'</div>';
            }
            echo $output; 
        }
        exit;
    }

    // 8. SUBMIT CART TO TABLE
    if($_POST["action"] == 'submit_cart_to_table') {
        if(empty($_SESSION['cart'])) { echo 'empty'; exit; }
        
        $table = $_POST['table_name'];
        $waiter_identifier = $_SESSION['user_name'] ?? 'Staff'; 
        
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

    // 9. SETTLE ORDER
    if($_POST["action"] == 'settle_order') {
        $order_id = $_POST["order_id"];
        $cashier = $_SESSION['user_name'] ?? 'Admin';
        $object->query = "UPDATE order_table SET order_status = 'Completed', order_cashier = :cashier WHERE order_id = :id";
        if($object->execute([':cashier' => $cashier, ':id' => $order_id])) echo 'success';
        exit;
    }

    // 10. FETCH LIVE PENDING ORDERS (Admin Dashboard)
    if($_POST["action"] == 'fetch_admin_pending') {
        $object->query = "SELECT * FROM order_table WHERE order_status NOT IN ('Completed') ORDER BY order_id DESC";
        $result = $object->get_result();
        $output = '';
        foreach($result as $row) {
            $output .= '<tr class="order-row text-white" data-id="'.$row["order_id"].'">
                <td class="pl-4">'.$row["order_number"].'</td>
                <td><span class="badge badge-warning">'.$row["order_table"].'</span></td>
                <td>'.$row["order_waiter"].'</td>
                <td class="text-success font-weight-bold" style="white-space: nowrap;">'.$object->cur.' '.number_format($row["order_net_amount"], 2).'</td>
                <td class="text-right pr-4"><i class="fas fa-eye"></i></td>
            </tr>';
        }
        echo $output ?: '<tr><td colspan="5" class="text-center py-4 text-white-50">No pending orders</td></tr>';
        exit;
    }

   // 11. FETCH CUSTOMER ACTIVE ORDERS (FIXED FOR BUTTON ALIGNMENT)
    if($_POST["action"] == 'fetch_customer_active_orders') {
        $user_name = $_SESSION['user_name'] ?? 'Staff';
        $object->query = "SELECT * FROM order_table WHERE order_waiter = :user AND order_status NOT IN ('Completed') ORDER BY order_id DESC";
        $object->execute([':user' => $user_name]);
        $result = $object->statement_result();
        
        $output = '';
        if(!empty($result)) {
            foreach($result as $row) {
                $status_class = ($row['order_status'] == 'Ready') ? 'badge-success' : 'badge-warning';
                
                $pay_button = '';
                if($row['order_status'] == 'Ready') {
                    $pay_button = '
                    <button class="btn btn-sm pay_now_btn" 
                            data-id="'.$row["order_id"].'" 
                            data-bill="'.$object->cur.' '.number_format($row["order_net_amount"], 2).'"
                            style="background: #22c55e; color: white; border: none;">
                        <i class="fas fa-money-bill-wave mr-1"></i> PAY
                    </button>';
                }

                $output .= '
                <div class="col-md-4 mb-4">
                    <div class="order-card text-white p-3 shadow-sm" style="background: rgba(255,255,255,0.05); border-radius:20px; border: 1px solid rgba(255,255,255,0.1);">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <span class="badge '.$status_class.' mb-2 px-3 py-1">'.strtoupper($row['order_status']).'</span>
                                <h5 class="mb-0 font-weight-bold">#'.$row["order_number"].'</h5>
                            </div>
                            <div class="text-right">
                                <small class="text-white-50">'.date('h:i A', strtotime($row["order_time"])).'</small>
                            </div>
                        </div>
                        <div class="small text-white-50 mb-3 border-bottom border-secondary pb-2">
                             Table: <span class="text-white">'.$row["order_table"].'</span>
                        </div>
                        
                        <div class="order-footer-wrapper">
                            <span class="h5 mb-0 text-success font-weight-bold" style="white-space: nowrap;">'.$object->cur.' '.number_format($row["order_net_amount"], 2).'</span>
                            <div class="order-action-btns">
                                <button class="btn btn-sm btn-outline-info view_receipt" data-id="'.$row["order_id"].'"><i class="fas fa-eye"></i></button>
                                '.$pay_button.'
                            </div>
                        </div>
                    </div>
                </div>';
            }
        } else {
            $output = '<div class="col-12 text-center py-5 text-white-50"><i class="fas fa-utensils fa-3x mb-3"></i><p>No active orders found.</p></div>';
        }
        echo $output;
        exit;
    }

    // 12. FETCH CUSTOMER HISTORY
if($_POST["action"] == 'fetch_customer_history') {
    $user_name = $_SESSION['user_name'] ?? '';
    $user_id = $_SESSION['user_id'] ?? 0;
    
    $order_column = array('order_number', 'order_date', 'order_table', 'order_net_amount');
    
    // Simplified query to ensure it works
    $main_query = "SELECT * FROM order_table WHERE order_status = 'Completed' ";
    
    $params = [];
    $search_query = "";
    if(!empty($_POST["search"]["value"])) {
        $search_query = " AND (order_number LIKE :search OR order_table LIKE :search) ";
        $params[':search'] = '%' . $_POST["search"]["value"] . '%';
    }

    $order_query = " ORDER BY " . $order_column[$_POST['order']['0']['column']] . " " . $_POST['order']['0']['dir'] . " ";
    $limit_query = ($_POST["length"] != -1) ? ' LIMIT ' . (int)$_POST['start'] . ', ' . (int)$_POST['length'] : "";

    $object->query = $main_query . $search_query . $order_query . $limit_query;
    $object->execute($params);
    $result = $object->statement_result();
    
    $object->query = $main_query . $search_query;
    $object->execute($params);
    $total_rows = $object->row_count();

    $data = array();
    foreach($result as $row) {
        $data[] = array(
            "order_number"   => '<strong class="text-sky-blue">'.$row["order_number"].'</strong>',
            "order_date"     => date('M d, Y', strtotime($row["order_date"])),
            "order_table"    => '<span class="badge border border-secondary text-white">'.$row["order_table"].'</span>',
            "order_total"    => '<span class="text-success font-weight-bold">'.$object->cur . ' ' . number_format($row["order_net_amount"], 2).'</span>',
            "payment_method" => '<span class="text-white-50">'.($row["payment_method"] ?? 'N/A').'</span>',
            "action"         => '<button class="btn btn-sm btn-info view_receipt" data-id="'.$row["order_id"].'" style="border-radius:12px;"><i class="fas fa-file-invoice"></i> Receipt</button>'
        );
    }

    $output = array(
        "draw"            => intval($_POST["draw"]),
        "recordsTotal"    => $total_rows,
        "recordsFiltered" => $total_rows,
        "data"            => $data
    );

    echo json_encode($output);
    exit; // Ensure no other output follows
}

    // 13. GET RECEIPT HTML
    if($_POST["action"] == 'get_receipt_html') {
        $order_id = $_POST["order_id"];
        $object->query = "SELECT * FROM order_table WHERE order_id = :id";
        $object->execute([':id' => $order_id]);
        $order_res = $object->statement_result();

        if(!empty($order_res)){
            $order = $order_res[0];
            $output = '
            <div class="text-center text-dark" style="font-family: \'Courier New\', Courier, monospace; width: 100%;">
                <h4 class="font-weight-bold mb-1">WAKANESA RESTAURANT</h4>
                <p class="small mb-3">123 Culinary Drive, Food City<br>Tel: +254 700 000 000</p>
                <div style="border-top: 1px dashed #333; margin: 10px 0;"></div>
                <div class="d-flex justify-content-between small">
                    <span style="white-space: nowrap;">Order: #'.$order['order_number'].'</span>
                    <span style="white-space: nowrap;">Date: '.date('d/m/y', strtotime($order['order_date'])).'</span>
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
                    <span class="text-left">'.$item['product_quantity'].' x '.$item['product_name'].'</span>
                    <span class="text-right" style="white-space: nowrap;">'.number_format($item['product_amount'], 2).'</span>
                </div>';
            }

            $output .= '
                <div style="border-top: 1px dashed #333; margin: 10px 0;"></div>
                <div class="d-flex justify-content-between font-weight-bold">
                    <span>TOTAL</span>
                    <span style="white-space: nowrap;">'.$object->cur.' '.number_format($order['order_net_amount'], 2).'</span>
                </div>
                <div style="border-top: 1px dashed #333; margin: 10px 0;"></div>
                <div class="mt-4 small">Thank you for dining with us!<br>See you soon!</div>
            </div>';
            echo $output;
        }
        exit;
    }

    // 14. FETCH CUSTOMER MENU
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
                            <div class="price-tag mb-3" style="width: fit-content;">'.$object->cur.' '.number_format($item['product_price'], 2).'</div>
                        </div>
                        <button class="btn btn-warning btn-block font-weight-bold add_to_cart" style="border-radius: 12px;" data-id="'.$item['product_id'].'">Add to Cart</button>
                    </div>
                </div>
            </div>';
        }
        echo $output ?: '<div class="col-12 text-center py-5 text-white-50">No items found in this category.</div>';
        exit;
    }

    // 15. SUBMIT CUSTOMER ONLINE ORDER
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

    // 16. KITCHEN DISPLAY: FETCH ACTIVE TICKETS
    if($_POST['action'] == 'fetch_kitchen_grid') {
        $search = $_POST['search'] ?? '';
        $filter = $_POST['filter'] ?? 'All';
        $is_dashboard = isset($_POST['origin']) && $_POST['origin'] == 'dashboard';

        $query = "SELECT * FROM order_table WHERE order_status NOT IN ('Completed')";
        $params = [];

        if($filter != 'All') {
            $query .= " AND order_status = :status ";
            $params[':status'] = $filter;
        }
        
        if(!empty($search)) {
            $query .= " AND (order_number LIKE :search OR order_table LIKE :search) ";
            $params[':search'] = '%' . $search . '%';
        }

        $query .= " ORDER BY order_id ASC";
        
        $object->query = $query;
        $object->execute($params);
        $orders = $object->statement_result();
        $html = '';

        if(!empty($orders)) {
            foreach($orders as $order) {
                $order_id = $order['order_id'];
                $status = $order['order_status'];
                
                $object->query = "SELECT * FROM order_item_table WHERE order_id = :id";
                $object->execute([':id' => $order_id]);
                $items = $object->statement_result();

                $start_time = strtotime($order['order_date'] . ' ' . $order['order_time']);
                $mins_ago = round((time() - $start_time) / 60);

                if($status == 'In Process') {
                    $btn_text = 'START PREPARING';
                    $btn_class = 'btn-start';
                    $next_status = 'Preparing';
                    $btn_state = '';
                } else if($status == 'Preparing') {
                    $btn_text = 'MARK READY';
                    $btn_class = 'btn-ready';
                    $next_status = 'Ready';
                    $btn_state = '';
                } else {
                    $btn_text = '<span style="color: #bbb8b8; text-shadow: 0 0 10px rgba(233, 242, 250, 0.93);">AWAITING PAYMENT</span>';                    
                    $btn_class = 'btn-waiting'; 
                    $next_status = ''; 
                    $btn_state = 'disabled';
                }

                $html .= '
                <div class="ticket-card">
                    <div class="ticket-header">
                        <span class="order-id">#'.$order['order_number'].'</span>
                        <span class="table-name" style="white-space: nowrap;">TABLE '.$order['order_table'].'</span>
                    </div>
                    <div class="ticket-meta">
                        <span class="customer-name">'.strtoupper($order['order_waiter']).'</span>
                        <span class="time-ago">'.$mins_ago.' MINS AGO</span>
                    </div>
                    <div class="ticket-body">';
                
                foreach($items as $item) {
                    $html .= '
                    <div class="order-item">
                        <span class="item-qty">'.$item['product_quantity'].'x</span>
                        <span>'.$item['product_name'].'</span>
                    </div>';
                }

                $html .= '</div>';

                if(!$is_dashboard) {
                    $html .= '
                    <button type="button" class="btn-status '.$btn_class.' change_status" data-id="'.$order_id.'" data-next="'.$next_status.'" '.$btn_state.'>
                        '.$btn_text.'
                    </button>';
                }

                $html .= '</div>';
            }
        } else {
            $html = '<div class="col-12 text-center py-5 text-white-50"><h3>NO ACTIVE ORDERS</h3></div>';
        }
        echo $html;
        exit;
    }

    // 17. UPDATE STATUS
    if($_POST['action'] == 'update_order_status') {
        $order_id = $_POST['order_id'];
        $status = $_POST['status']; 
        $object->query = "UPDATE order_table SET order_status = :status WHERE order_id = :id";
        if($object->execute([':status' => $status, ':id' => $order_id])) echo 'success';
        else echo 'error';
        exit;
    }

    // 18. FETCH LIVE PRODUCTION (List View)
    if($_POST["action"] == 'fetch_production') {
        $order_column = array('order_number', 'order_table', 'order_id', 'order_id', 'order_status');
        $main_query = "SELECT * FROM order_table WHERE order_status != 'Completed' ";

        $params = [];
        $search_query = "";
        if(!empty($_POST["search"]["value"])) {
            $search_query = "AND (order_number LIKE :search OR order_table LIKE :search OR order_status LIKE :search) ";
            $params[':search'] = '%' . $_POST["search"]["value"] . '%';
        }

        $order_query = "ORDER BY " . $order_column[$_POST['order']['0']['column']] . " " . $_POST['order']['0']['dir'] . " ";
        $limit_query = ($_POST["length"] != -1) ? 'LIMIT ' . (int)$_POST['start'] . ', ' . (int)$_POST['length'] : "";

        $object->query = $main_query . $search_query;
        $object->execute($params);
        $total_rows = $object->row_count();

        $object->query = $main_query . $search_query . $order_query . $limit_query;
        $object->execute($params);
        $result = $object->statement_result();
        
        $data = array();
        foreach($result as $row) {
            $object->query = "SELECT * FROM order_item_table WHERE order_id = :id";
            $object->execute([':id' => $row["order_id"]]);
            $items_result = $object->statement_result();
            
            $items_list = '<ul class="list-unstyled mb-0 small">';
            $total_qty = 0;
            foreach($items_result as $item) {
                $items_list .= '<li>'.$item["product_name"].'</li>';
                $total_qty += $item["product_quantity"];
            }
            $items_list .= '</ul>';

            $status = $row["order_status"];
            $status_html = '';
            $management_html = '';

            if($status == 'In Process') {
                $status_html = '<span class="status-waiting">WAITING</span>';
                $management_html = '<button class="btn btn-info btn-sm change_status" data-id="'.$row["order_id"].'" data-next="Preparing">START</button>';
            } else if($status == 'Preparing') {
                $status_html = '<span class="status-preparing">COOKING</span>';
                $management_html = '<button class="btn btn-warning btn-sm change_status" data-id="'.$row["order_id"].'" data-next="Ready">READY</button>';
            } else if($status == 'Ready') {
                $status_html = '<span class="badge badge-success py-2">READY</span>';
                $management_html = '<button class="btn btn-dark btn-sm" disabled>WAITING CASHIER</button>';
            }

            $data[] = array(
                "order_number" => '<strong>#'.$row["order_number"].'</strong>',
                "order_table"  => $row["order_table"],
                "items"        => $items_list,
                "qty"          => $total_qty,
                "status"       => $status_html,
                "action"       => $management_html
            );
        }

        echo json_encode([
            "draw"            => intval($_POST["draw"]),
            "recordsTotal"    => $total_rows,
            "recordsFiltered" => $total_rows,
            "data"            => $data
        ]);
        exit;
    }

    // 19. FETCH CASHIER BILLING QUEUE
    if($_POST["action"] == 'fetch_cashier_queue') {
        $object->query = "SELECT * FROM order_table WHERE order_status = 'Ready' ORDER BY order_id DESC";
        $result = $object->get_result();
        $output = '';

        if($object->row_count() > 0) {
            foreach($result as $row) {
                $output .= '
                <tr>
                    <td style="width: fit-content; white-space: nowrap;">
                        <div class="font-weight-bold text-white">#'.$row["order_number"].'</div>
                    </td>
                    <td style="width: fit-content; white-space: nowrap;">
                        <span class="text-white">'.$row["order_table"].'</span>
                    </td>
                    <td style="width: fit-content; white-space: nowrap;">
                        <div class="text-white-50">'.($row["order_cashier"] ?: 'Self-Order').'</div>
                    </td>
                    <td style="width: fit-content; white-space: nowrap;" class="text-right">
                        <span class="font-weight-bold text-success">
                            '.$object->cur.' '.number_format($row["order_net_amount"], 2).'
                        </span>
                    </td>
                    <td style="width: fit-content; white-space: nowrap;" class="text-center">
                        <button class="btn btn-sm settle_order_btn" 
                                data-id="'.$row["order_id"].'" 
                                style="background: #22c55e; color: white; border-radius: 6px; font-weight: 600; padding: 4px 12px; border: none;">
                            Collect Pay
                        </button>
                    </td>
                </tr>';
            }
        } else {
            $output = '<tr><td colspan="5" class="text-center py-5 text-white-50">No orders ready for billing</td></tr>';
        }
        echo $output;
        exit;
    }

    // 20. PROCESS CUSTOMER PAYMENT
if($_POST["action"] == 'process_customer_payment') {
    $order_id = $_POST["order_id"];
    $amount_paid = (float)$_POST["amount_paid"];
    $payment_method = $_POST["payment_method"];
    $cashier = $_SESSION['user_name'] ?? 'Admin';

    // Fetch order details to verify total
    $object->query = "SELECT order_net_amount FROM order_table WHERE order_id = :id";
    $object->execute([':id' => $order_id]);
    $order = $object->statement_result();

    if(!empty($order)) {
        $net_amount = (float)$order[0]['order_net_amount'];

        // If Payment is Card/M-Pesa, we treat amount_paid as net_amount automatically
        if($payment_method !== 'Cash') {
            $amount_paid = $net_amount;
        }

        if($amount_paid >= $net_amount) {
            $change = $amount_paid - $net_amount;

            $object->query = "
                UPDATE order_table 
                SET order_status = 'Completed', 
                    order_cashier = :cashier,
                    payment_method = :method,
                    amount_paid = :paid,
                    balance_given = :change
                WHERE order_id = :id
            ";

            $data = [
                ':cashier' => $cashier,
                ':method'  => $payment_method,
                ':paid'    => $amount_paid,
                ':change'  => $change,
                ':id'      => $order_id
            ];

            if($object->execute($data)) {
                echo json_encode([
                    'status' => 'success',
                    'change' => number_format($change, 2)
                ]);
            }
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Insufficient amount. Required: ' . number_format($net_amount, 2)
            ]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Order not found.']);
    }
    exit;
}
}
?>