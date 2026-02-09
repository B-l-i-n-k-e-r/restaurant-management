<?php
// print_order.php
include('rms.php');
$object = new rms();

if(isset($_GET["id"])) {
    // SINGLE ORDER
    $object->query = "SELECT * FROM order_table WHERE order_id = :order_id";
    $object->execute([':order_id' => $_GET["id"]]);
    $order_result = $object->statement_result();
} elseif(isset($_GET["all"])) {
    // ALL ORDERS
    $object->query = "SELECT * FROM order_table ORDER BY order_date DESC, order_time DESC";
    $order_result = $object->get_result();
} else {
    die("Invalid Request");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo isset($_GET["id"]) ? "Order Report - " . $order_result[0]["order_number"] : "All Orders Report"; ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body { background: #fff; color: #000; font-family: 'Courier New', Courier, monospace; }
        .receipt-card { max-width: 800px; margin: 20px auto; padding: 20px; border: 1px solid #eee; }
        .all-orders { max-width: 1200px; margin: 20px auto; }
        @media print {
            .no-print { display: none; }
            .receipt-card, .all-orders { border: none; width: 100%; margin: 0; }
        }
        .header-text { text-align: center; margin-bottom: 20px; }
        .mb-4 { margin-bottom: 1.5rem; }
    </style>
</head>
<body>
<div class="container no-print text-center mt-3 mb-4">
    <button class="btn btn-primary" onclick="window.print()">Print Report</button>
    <button class="btn btn-secondary" onclick="window.close()">Close</button>
</div>

<?php
if(isset($_GET["id"])) {
    // SINGLE ORDER DISPLAY
    $row = $order_result[0];
?>
<div class="receipt-card">
    <div class="header-text">
        <h2>RESTAURANT MANAGEMENT SYSTEM</h2>
        <p>Order Report / Receipt</p>
    </div>

    <div class="row mb-4">
        <div class="col-6">
            <strong>Order No:</strong> <?php echo $row["order_number"]; ?><br>
            <strong>Table:</strong> <?php echo $row["order_table"]; ?><br>
            <strong>Waiter:</strong> <?php echo $row["order_waiter"]; ?>
        </div>
        <div class="col-6 text-right">
            <strong>Date:</strong> <?php echo $row["order_date"]; ?><br>
            <strong>Time:</strong> <?php echo $row["order_time"]; ?><br>
            <strong>Status:</strong> <?php echo $row["order_status"]; ?>
        </div>
    </div>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Item</th>
                <th class="text-center">Qty</th>
                <th class="text-right">Rate</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $object->query = "SELECT * FROM order_item_table WHERE order_id = :order_id";
            $object->execute([':order_id' => $row["order_id"]]);
            $items = $object->statement_result();
            foreach($items as $item) {
                echo '<tr>
                    <td>'.$item["product_name"].'</td>
                    <td class="text-center">'.$item["product_quantity"].'</td>
                    <td class="text-right">'.$object->cur . number_format($item["product_rate"], 2).'</td>
                    <td class="text-right">'.$object->cur . number_format($item["product_amount"], 2).'</td>
                </tr>';
            }
            ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3" class="text-right">Gross Amount</th>
                <th class="text-right"><?php echo $object->cur . number_format($row["order_gross_amount"], 2); ?></th>
            </tr>
            <tr>
                <th colspan="3" class="text-right">Tax</th>
                <th class="text-right"><?php echo $object->cur . number_format($row["order_tax_amount"], 2); ?></th>
            </tr>
            <tr>
                <th colspan="3" class="text-right">Net Total</th>
                <th class="text-right"><?php echo $object->cur . number_format($row["order_net_amount"], 2); ?></th>
            </tr>
        </tfoot>
    </table>

    <div class="mt-5 text-center">
        <p>Thank you for your business!</p>
        <p><small>Generated on <?php echo date('Y-m-d H:i:s'); ?></small></p>
    </div>
</div>
<?php
} else {
    // ALL ORDERS DISPLAY
    echo '<div class="all-orders">';
    foreach($order_result as $row) {
        ?>
        <div class="receipt-card mb-4">
            <div class="header-text">
                <h2>RESTAURANT MANAGEMENT SYSTEM</h2>
                <p>Order Report / Receipt</p>
            </div>

            <div class="row mb-4">
                <div class="col-6">
                    <strong>Order No:</strong> <?php echo $row["order_number"]; ?><br>
                    <strong>Table:</strong> <?php echo $row["order_table"]; ?><br>
                    <strong>Waiter:</strong> <?php echo $row["order_waiter"]; ?>
                </div>
                <div class="col-6 text-right">
                    <strong>Date:</strong> <?php echo $row["order_date"]; ?><br>
                    <strong>Time:</strong> <?php echo $row["order_time"]; ?><br>
                    <strong>Status:</strong> <?php echo $row["order_status"]; ?>
                </div>
            </div>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th class="text-center">Qty</th>
                        <th class="text-right">Rate</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $object->query = "SELECT * FROM order_item_table WHERE order_id = :order_id";
                    $object->execute([':order_id' => $row["order_id"]]);
                    $items = $object->statement_result();
                    foreach($items as $item) {
                        echo '<tr>
                            <td>'.$item["product_name"].'</td>
                            <td class="text-center">'.$item["product_quantity"].'</td>
                            <td class="text-right">'.$object->cur . number_format($item["product_rate"], 2).'</td>
                            <td class="text-right">'.$object->cur . number_format($item["product_amount"], 2).'</td>
                        </tr>';
                    }
                    ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3" class="text-right">Gross Amount</th>
                        <th class="text-right"><?php echo $object->cur . number_format($row["order_gross_amount"], 2); ?></th>
                    </tr>
                    <tr>
                        <th colspan="3" class="text-right">Tax</th>
                        <th class="text-right"><?php echo $object->cur . number_format($row["order_tax_amount"], 2); ?></th>
                    </tr>
                    <tr>
                        <th colspan="3" class="text-right">Net Total</th>
                        <th class="text-right"><?php echo $object->cur . number_format($row["order_net_amount"], 2); ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
        <?php
    }
    echo '</div>';
}
?>
