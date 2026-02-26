<?php
// print_order.php
include('rms.php');
$object = new rms();

if(isset($_GET["id"])) {
    $object->query = "SELECT * FROM order_table WHERE order_id = :order_id";
    $object->execute([':order_id' => $_GET["id"]]);
    $order_result = $object->statement_result();
} elseif(isset($_GET["all"])) {
    $object->query = "SELECT * FROM order_table ORDER BY order_date DESC, order_time DESC";
    $order_result = $object->get_result();
} else {
    die("Invalid Request");
}

// Fetch Restaurant Info for the Header
$object->query = "SELECT * FROM restaurant_table LIMIT 1";
$object->execute();
$rest = $object->statement_result()[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo isset($_GET["id"]) ? "Receipt_" . $order_result[0]["order_number"] : "Order_Report_" . date('Y-m-d'); ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&family=Inconsolata&display=swap" rel="stylesheet">
    <style>
        :root { --accent: #000; }
        body { background: #f4f7f6; font-family: 'Inter', sans-serif; color: #333; }
        
        /* Receipt Aesthetics */
        .receipt-card { 
            background: #fff; 
            max-width: 450px; 
            margin: 40px auto; 
            padding: 30px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            border-radius: 8px;
            position: relative;
        }

        /* Continuous Report Aesthetics */
        .all-orders-container { max-width: 900px; margin: 40px auto; }
        .report-header { text-align: center; margin-bottom: 30px; }

        .receipt-header { text-align: center; border-bottom: 2px dashed #eee; padding-bottom: 20px; margin-bottom: 20px; }
        .receipt-header h2 { font-weight: 800; letter-spacing: -1px; text-transform: uppercase; font-size: 1.5rem; }
        
        .mono { font-family: 'Inconsolata', monospace; font-size: 0.95rem; }
        
        .table-receipt thead th { border-top: none; border-bottom: 1px solid #000; text-transform: uppercase; font-size: 0.75rem; }
        .table-receipt td { border: none; padding: 5px 0; }
        
        .total-section { border-top: 1px solid #000; padding-top: 10px; margin-top: 10px; }
        .total-row { display: flex; justify-content: space-between; margin-bottom: 5px; }
        .grand-total { font-size: 1.25rem; font-weight: 800; border-top: 2px solid #000; padding-top: 10px; margin-top: 10px; }

        .qr-placeholder { margin: 20px 0; text-align: center; opacity: 0.8; }
        
        .btn-cool { border-radius: 50px; padding: 10px 25px; font-weight: 600; transition: 0.3s; }

        @media print {
            body { background: #fff; }
            .no-print { display: none; }
            .receipt-card { 
                box-shadow: none; 
                margin: 0 auto; 
                padding: 10px; 
                max-width: 100%;
                border: none;
            }
            .page-break { page-break-after: always; }
        }
    </style>
</head>
<body>

<div class="container no-print text-center mt-5 mb-2">
    <div class="btn-group shadow-sm" style="border-radius: 50px; overflow: hidden;">
        <button class="btn btn-dark btn-cool" onclick="window.print()">
            <i class="fas fa-print mr-2"></i> Print Document
        </button>
        <button class="btn btn-light btn-cool" onclick="window.close()">
            Close Preview
        </button>
    </div>
</div>

<div class="<?php echo isset($_GET["id"]) ? '' : 'all-orders-container'; ?>">

<?php foreach($order_result as $index => $row): ?>
    <div class="receipt-card <?php echo (count($order_result) > 1) ? 'page-break' : ''; ?>">
        <div class="receipt-header">
            <h2><?php echo $rest['restaurant_name'] ?? 'RESTO PRO'; ?></h2>
            <p class="small text-muted mb-1"><?php echo $rest['restaurant_address'] ?? 'Main Street, City'; ?></p>
            <p class="small text-muted">Tel: <?php echo $rest['restaurant_contact_no'] ?? '000-000'; ?></p>
        </div>

        <div class="row mono mb-4">
            <div class="col-7">
                <strong># <?php echo $row["order_number"]; ?></strong><br>
                Tbl: <?php echo $row["order_table"]; ?><br>
                Staff: <?php echo $row["order_waiter"]; ?>
            </div>
            <div class="col-5 text-right">
                <?php echo date('d M, Y', strtotime($row["order_date"])); ?><br>
                <?php echo date('H:i', strtotime($row["order_time"])); ?><br>
                <span class="badge badge-dark"><?php echo strtoupper($row["order_status"]); ?></span>
            </div>
        </div>

        <table class="table table-receipt mono">
            <thead>
                <tr>
                    <th>Item</th>
                    <th class="text-center">Qty</th>
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
                        <td>'.strtolower($item["product_name"]).'</td>
                        <td class="text-center">'.$item["product_quantity"].'</td>
                        <td class="text-right">'.number_format($item["product_amount"], 2).'</td>
                    </tr>';
                }
                ?>
            </tbody>
        </table>

        <div class="total-section mono">
            <div class="total-row">
                <span>Subtotal</span>
                <span><?php echo number_format($row["order_gross_amount"], 2); ?></span>
            </div>
            <div class="total-row text-muted small">
                <span>Tax Total</span>
                <span><?php echo number_format($row["order_tax_amount"], 2); ?></span>
            </div>
            <div class="total-row grand-total">
                <span>TOTAL</span>
                <span><?php echo $object->cur . ' ' . number_format($row["order_net_amount"], 2); ?></span>
            </div>
        </div>

        <div class="qr-placeholder">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=<?php echo $row['order_number']; ?>" alt="QR" style="width: 80px;">
            <p class="small mt-2 mb-0">Scan to verify order</p>
        </div>

        <div class="text-center mt-4">
            <p class="small">--- Thank You! Come Again ---</p>
            <p style="font-size: 10px;" class="text-muted">Generated by RMS Pro v2.0</p>
        </div>
    </div>
<?php endforeach; ?>

</div>

<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html>