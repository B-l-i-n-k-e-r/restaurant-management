<?php
// print.php
include('rms.php');
$object = new rms();

if(!$object->is_login())
{
    header("location:".$object->base_url."");
    exit;
}

$action = isset($_GET["action"]) ? $_GET["action"] : 'print';

/* =========================================================
   MODE 1: PRINT ALL BILLS (REPORT MODE)
========================================================= */
if($action == 'print_all')
{
    $object->query = "SELECT * FROM order_table WHERE order_status = 'Completed' ORDER BY order_id DESC";
    $object->execute();
    $result = $object->statement_result();
    
    // Fetch Restaurant Details for Header
    $object->query = "SELECT * FROM restaurant_table LIMIT 1";
    $object->execute();
    $res_info = $object->statement_result();
    $restaurant_name = !empty($res_info) ? $res_info[0]['restaurant_name'] : 'Restaurant';
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>All Bills Report - <?php echo date('Y-m-d'); ?></title>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
        <style>
            body { background: white; font-family: sans-serif; padding: 20px; color: #333; }
            .report-header { border-bottom: 3px solid #17a2b8; margin-bottom: 20px; padding-bottom: 10px; }
            .table thead th { background: #f8f9fa; color: #17a2b8; border-bottom: 2px solid #dee2e6; }
            @media print { .no-print { display: none; } }
        </style>
    </head>
    <body onload="window.print()">
        <div class="report-header d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-0 text-info"><?php echo strtoupper($restaurant_name); ?></h2>
                <h5 class="text-muted">Master Sales Report</h5>
            </div>
            <div class="text-right">
                <p class="mb-0"><strong>Generated:</strong> <?php echo date('d M, Y H:i A'); ?></p>
                <p class="mb-0"><strong>Total Records:</strong> <?php echo count($result); ?></p>
            </div>
        </div>

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Order No.</th>
                    <th>Table</th>
                    <th>Date / Time</th>
                    <th>Waiter</th>
                    <th>Cashier</th>
                    <th class="text-right">Gross Amount</th>
                    <th class="text-right">Tax</th>
                    <th class="text-right">Net Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $total_gross = 0; $total_tax = 0; $total_net = 0;
                foreach($result as $row)
                {
                    echo '
                    <tr>
                        <td>'.$row["order_number"].'</td>
                        <td>'.$row["order_table"].'</td>
                        <td>'.$row["order_date"].' '.$row["order_time"].'</td>
                        <td>'.$row["order_waiter"].'</td>
                        <td>'.$row["order_cashier"].'</td>
                        <td class="text-right">'.number_format($row["order_gross_amount"], 2).'</td>
                        <td class="text-right">'.number_format($row["order_tax_amount"], 2).'</td>
                        <td class="text-right font-weight-bold">'.number_format($row["order_net_amount"], 2).'</td>
                    </tr>';
                    $total_gross += $row["order_gross_amount"];
                    $total_tax += $row["order_tax_amount"];
                    $total_net += $row["order_net_amount"];
                }
                ?>
            </tbody>
            <tfoot class="bg-light">
                <tr class="h5">
                    <td colspan="5" class="text-right font-weight-bold">GRAND TOTALS:</td>
                    <td class="text-right text-dark"><?php echo number_format($total_gross, 2); ?></td>
                    <td class="text-right text-dark"><?php echo number_format($total_tax, 2); ?></td>
                    <td class="text-right text-info font-weight-bold"><?php echo $object->cur . ' ' . number_format($total_net, 2); ?></td>
                </tr>
            </tfoot>
        </table>
        <div class="mt-4 text-center text-muted small">
            End of Report - System Powered by WAKANESA
        </div>
    </body>
    </html>
    <?php
    exit;
}

/* =========================================================
   MODE 2: SINGLE RECEIPT (YOUR ORIGINAL LOGIC)
========================================================= */
if(!isset($_GET["order_id"]))
{
    die("Invalid Request");
}

$object->query = "SELECT * FROM order_table WHERE order_id = :order_id";
$object->execute(['order_id' => $_GET["order_id"]]);
$order_result = $object->statement_result();

if(empty($order_result)) { die("Order Not Found"); }

$row = $order_result[0];

$object->query = "SELECT * FROM restaurant_table LIMIT 1";
$object->execute();
$restaurant_result = $object->statement_result();
$restaurant = !empty($restaurant_result) ? $restaurant_result[0] : [];

$qr_data = "Order No: {$row['order_number']}\n"
         . "Table: {$row['order_table']}\n"
         . "Total: {$object->cur}{$row['order_net_amount']}\n"
         . "Date: {$row['order_date']} {$row['order_time']}";
$qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=".urlencode($qr_data);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Invoice - <?php echo $row["order_number"]; ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        @page { size: 80mm auto; margin: 0; }
        body { font-family: 'Courier New', Courier, monospace; background: #f4f7f6; color: #2c3e50; font-size: 13px; padding: 20px; }
        .receipt { width: 80mm; background: #fff; margin: 0 auto; padding: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); position: relative; border-radius: 4px; }
        .watermark { position: absolute; top: 40%; left: 50%; transform: translate(-50%, -50%) rotate(-30deg); font-size: 40px; font-weight: bold; color: rgba(78, 205, 196, 0.1); white-space: nowrap; pointer-events: none; z-index: 1; }
        .header { text-align: center; border-bottom: 2px solid #4ecdc4; padding-bottom: 10px; margin-bottom: 10px; z-index: 2; position: relative; }
        .header h3 { margin: 0; font-size: 16px; font-weight: bold; color: #2c3e50; }
        .info { margin-bottom: 10px; border-bottom: 1px dashed #ddd; padding-bottom: 5px; }
        .info div { display: flex; justify-content: space-between; line-height: 1.4; }
        .info-label { color: #16a085; font-weight: bold; }
        .items-table { width: 100%; margin-bottom: 10px; }
        .items-table th { border-bottom: 2px solid #2c3e50; color: #16a085; padding: 5px 0; }
        .items-table td { padding: 5px 0; border-bottom: 1px dashed #eee; }
        .totals-section { border-top: 1px solid #2c3e50; padding-top: 5px; }
        .totals-section div { display: flex; justify-content: space-between; padding: 2px 0; }
        .net-total { background: #2c3e50; color: #fff; padding: 8px 5px !important; margin-top: 5px; font-weight: bold; font-size: 15px; }
        .qr { text-align: center; margin: 15px 0; }
        .footer { text-align: center; font-size: 11px; color: #7f8c8d; border-top: 1px dashed #ddd; padding-top: 10px; }
        @media print { body { background: #fff; padding: 0; } .receipt { box-shadow: none; width: 100%; margin: 0; padding: 10px; } }
    </style>
</head>
<body onload="window.print()">
<div class="receipt">
    <div class="watermark">WAKANESA</div>
    <div class="header">
        <?php if(!empty($restaurant['restaurant_logo'])): ?>
            <img src="<?php echo $restaurant['restaurant_logo']; ?>" style="max-width: 70px; border-radius: 50%; margin-bottom: 5px;">
        <?php endif; ?>
        <h3><?php echo strtoupper($restaurant['restaurant_name'] ?? 'RESTAURANT'); ?></h3>
        <div style="font-size: 11px;"><?php echo $restaurant['restaurant_address'] ?? ''; ?></div>
        <div style="font-size: 11px; font-weight: bold;">Tel: <?php echo $restaurant['restaurant_contact_no'] ?? ''; ?></div>
    </div>
    <div class="info">
        <div><span class="info-label">Order:</span> <span><?php echo $row["order_number"]; ?></span></div>
        <div><span class="info-label">Table:</span> <span><?php echo $row["order_table"]; ?></span></div>
        <div><span class="info-label">Waiter:</span><span><?php echo $row["order_waiter"]; ?></span></div>
        <div><span class="info-label">Date:</span>  <span><?php echo $row["order_date"]; ?></span></div>
        <div><span class="info-label">Time:</span>  <span><?php echo $row["order_time"]; ?></span></div>
    </div>
    <table class="items-table">
        <thead>
            <tr>
                <th>ITEM</th>
                <th class="text-center">QTY</th>
                <th class="text-right">AMT</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $object->query = "SELECT * FROM order_item_table WHERE order_id = :order_id";
        $object->execute(['order_id' => $row["order_id"]]);
        foreach($object->statement_result() as $item):
        ?>
            <tr>
                <td><?php echo $item["product_name"]; ?></td>
                <td class="text-center"><?php echo $item["product_quantity"]; ?></td>
                <td class="text-right"><?php echo $object->cur.number_format($item["product_amount"], 2); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <div class="totals-section">
        <div style="font-weight: bold;"><span>SUBTOTAL</span><span><?php echo $object->cur.number_format($row["order_gross_amount"], 2); ?></span></div>
        <?php
        $object->query = "SELECT * FROM tax_table WHERE tax_status = 'Enable'";
        $object->execute();
        foreach($object->statement_result() as $tax):
            $tax_amount = ($row["order_gross_amount"] * $tax["tax_percentage"]) / 100;
        ?>
        <div style="font-size: 12px; color: #555; border-top: 1px dotted #eee;">
            <span><?php echo $tax["tax_name"]; ?> (<?php echo number_format($tax["tax_percentage"], 2); ?>%)</span>
            <span><?php echo $object->cur.number_format($tax_amount, 2); ?></span>
        </div>
        <?php endforeach; ?>
        <div class="net-total"><span>NET PAYABLE</span><span><?php echo $object->cur.number_format($row["order_net_amount"], 2); ?></span></div>
    </div>
    <div class="qr"><img src="<?php echo $qr_url; ?>" width="100"><div style="font-size: 10px; margin-top: 5px;">Scan to Verify Order</div></div>
    <div class="footer">
        <strong style="color: #1abc9c;">THANK YOU FOR DINING WITH US!</strong><br>
        <small>System Powered by WAKANESA</small><br>
        <small><?php echo date('Y-m-d H:i:s'); ?></small>
    </div>
</div>
</body>
</html>