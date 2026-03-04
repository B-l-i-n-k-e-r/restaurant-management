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
   MODE 1: MASTER SALES REPORT (UPGRADED)
========================================================= */
if($action == 'print_all')
{
    $object->query = "SELECT * FROM order_table WHERE order_status = 'Completed' ORDER BY order_id DESC";
    $object->execute();
    $result = $object->statement_result();
    
    $object->query = "SELECT * FROM restaurant_table LIMIT 1";
    $object->execute();
    $res_info = $object->statement_result();
    $restaurant_name = !empty($res_info) ? $res_info[0]['restaurant_name'] : 'Restaurant';
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Sales_Report_<?php echo date('Y-m-d'); ?></title>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
        <style>
            body { background: #fff; font-family: 'Inter', sans-serif; padding: 30px; color: #1a202c; }
            .report-header { border-bottom: 4px solid #000; margin-bottom: 30px; padding-bottom: 15px; }
            .brand-color { color: #0ea5e9; font-weight: 900; letter-spacing: -1px; }
            
            /* Constraint: Force columns to fit content */
            .fit-content { width: 1% !important; white-space: nowrap !important; }
            
            .table thead th { 
                background: #f8fafc; 
                color: #64748b; 
                text-transform: uppercase; 
                font-size: 0.7rem; 
                letter-spacing: 1px;
                border-top: none !important;
                border-bottom: 2px solid #e2e8f0 !important;
            }
            .table td { vertical-align: middle !important; font-size: 0.85rem; border-bottom: 1px solid #f1f5f9; }
            .summary-box { background: #f8fafc; border-radius: 12px; padding: 20px; border: 1px solid #e2e8f0; }
            
            @media print { 
                @page { margin: 1cm; }
                .no-print { display: none; } 
                body { padding: 0; }
            }
        </style>
    </head>
    <body onload="window.print()">
        <div class="report-header d-flex justify-content-between align-items-end">
            <div>
                <h1 class="mb-0 brand-color"><?php echo strtoupper($restaurant_name); ?></h1>
                <p class="text-muted mb-0 font-weight-bold">MASTER REVENUE ARCHIVE</p>
            </div>
            <div class="text-right">
                <div class="summary-box">
                    <small class="text-uppercase text-muted d-block">Generation Timestamp</small>
                    <strong><?php echo date('d M Y | H:i'); ?></strong>
                </div>
            </div>
        </div>

        <table class="table table-hover">
            <thead>
                <tr>
                    <th class="fit-content"># ORDER</th>
                    <th class="fit-content">UNIT</th>
                    <th class="fit-content">TIMESTAMP</th>
                    <th>STAKEHOLDERS</th>
                    <th class="fit-content text-center">PAYMENT</th> 
                    <th class="text-right fit-content">GROSS</th>
                    <th class="text-right fit-content">TAX</th>
                    <th class="text-right fit-content">NET TOTAL</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $total_gross = 0; $total_tax = 0; $total_net = 0;
                foreach($result as $row)
                {
                    $waiter = ($row["order_table"] == 'Self-Order') ? 'Self-Service' : $row["order_waiter"];
                    
                    echo '
                    <tr>
                        <td class="font-weight-bold fit-content">'.$row["order_number"].'</td>
                        <td class="fit-content"><span class="badge badge-dark">'.$row["order_table"].'</span></td>
                        <td class="fit-content text-muted">'.date('d/m H:i', strtotime($row["order_date"].' '.$row["order_time"])).'</td>
                        <td>
                            <small class="d-block"><b>W:</b> '.$waiter.'</small>
                            <small class="d-block text-muted"><b>C:</b> '.$row["order_cashier"].'</small>
                        </td>
                        <td class="text-center fit-content">
                            <span class="badge border border-info text-info px-2">'.($row["payment_method"] ?? "CASH").'</span>
                        </td>
                        <td class="text-right fit-content">'.number_format($row["order_gross_amount"], 2).'</td>
                        <td class="text-right fit-content text-muted">'.number_format($row["order_tax_amount"], 2).'</td>
                        <td class="text-right fit-content font-weight-bold">'.number_format($row["order_net_amount"], 2).'</td>
                    </tr>';
                    $total_gross += $row["order_gross_amount"];
                    $total_tax += $row["order_tax_amount"];
                    $total_net += $row["order_net_amount"];
                }
                ?>
            </tbody>
            <tfoot>
                <tr style="background: #f1f5f9;">
                    <td colspan="5" class="text-right font-weight-bold text-uppercase p-3">Consolidated Revenue:</td>
                    <td class="text-right font-weight-bold p-3"><?php echo number_format($total_gross, 2); ?></td>
                    <td class="text-right font-weight-bold p-3"><?php echo number_format($total_tax, 2); ?></td>
                    <td class="text-right p-3">
                        <span class="h5 font-weight-bold text-info"><?php echo $object->cur . ' ' . number_format($total_net, 2); ?></span>
                    </td>
                </tr>
            </tfoot>
        </table>

        <div class="mt-5 pt-4 border-top text-center text-muted">
            <p class="small mb-0">WAKANESA DIGITAL TERMINAL | END OF REPORT</p>
            <p style="font-size: 10px;">Security Hash: <?php echo md5(time()); ?></p>
        </div>
    </body>
    </html>
    <?php
    exit;
}

/* =========================================================
   MODE 2: SINGLE THERMAL RECEIPT (UPGRADED)
========================================================= */
if(!isset($_GET["order_id"])) { die("ACCESS_DENIED: MISSING_ID"); }

$object->query = "SELECT * FROM order_table WHERE order_id = :order_id";
$object->execute(['order_id' => $_GET["order_id"]]);
$order_result = $object->statement_result();
if(empty($order_result)) { die("NOT_FOUND"); }
$row = $order_result[0];

$object->query = "SELECT * FROM restaurant_table LIMIT 1";
$object->execute();
$restaurant_result = $object->statement_result();
$restaurant = !empty($restaurant_result) ? $restaurant_result[0] : [];

$receipt_waiter = ($row["order_table"] == 'Self-Order') ? 'SYSTEM_ORDER' : $row["order_waiter"];
$qr_data = "WAKANESA:{$row['order_number']}|TOTAL:{$row['order_net_amount']}";
$qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=".urlencode($qr_data);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Rec_<?php echo $row["order_number"]; ?></title>
    <style>
        @page { size: 80mm auto; margin: 0; }
        body { font-family: 'Courier New', Courier, monospace; background: #eee; color: #000; font-size: 12px; margin: 0; padding: 20px; }
        .receipt { width: 72mm; background: #fff; margin: 0 auto; padding: 10px; border-radius: 2px; }
        .header { text-align: center; border-bottom: 1px solid #000; padding-bottom: 8px; margin-bottom: 10px; }
        .header h2 { margin: 5px 0; font-size: 18px; text-transform: uppercase; font-weight: 900; }
        .info { margin-bottom: 10px; line-height: 1.2; font-size: 11px; }
        .info div { display: flex; justify-content: space-between; }
        .items-table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        .items-table th { border-bottom: 1px dashed #000; text-align: left; padding: 4px 0; font-size: 10px; }
        .items-table td { padding: 4px 0; font-size: 11px; vertical-align: top; }
        .totals { border-top: 1px solid #000; padding-top: 5px; }
        .totals div { display: flex; justify-content: space-between; padding: 2px 0; }
        .grand-total { border-top: 1px double #000; margin-top: 5px; padding: 8px 0 !important; font-size: 16px; font-weight: bold; }
        .qr { text-align: center; margin: 15px 0; }
        .footer { text-align: center; font-size: 10px; border-top: 1px dashed #000; padding-top: 10px; margin-top: 10px; }
        
        @media print { 
            body { background: #fff; padding: 0; } 
            .receipt { width: 100%; padding: 5mm; } 
        }
    </style>
</head>
<body onload="window.print()">
<div class="receipt">
    <div class="header">
        <h2><?php echo strtoupper($restaurant['restaurant_name'] ?? 'RESTAURANT'); ?></h2>
        <div style="font-size: 10px;"><?php echo $restaurant['restaurant_address'] ?? ''; ?></div>
        <div style="font-weight: bold;">TEL: <?php echo $restaurant['restaurant_contact_no'] ?? ''; ?></div>
    </div>
    
    <div class="info">
        <div><span>ORDER NO:</span> <strong>#<?php echo $row["order_number"]; ?></strong></div>
        <div><span>UNIT/TABLE:</span> <strong><?php echo $row["order_table"]; ?></strong></div>
        <div><span>PROTOCOL:</span> <span><?php echo $receipt_waiter; ?></span></div>
        <div><span>DATETIME:</span> <span><?php echo date('d/m/y H:i', strtotime($row["order_date"].' '.$row["order_time"])); ?></span></div>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th width="60%">ITEM</th>
                <th width="10%">QTY</th>
                <th width="30%" style="text-align: right;">AMT</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $object->query = "SELECT * FROM order_item_table WHERE order_id = :order_id";
        $object->execute(['order_id' => $row["order_id"]]);
        foreach($object->statement_result() as $item):
        ?>
            <tr>
                <td><?php echo strtoupper($item["product_name"]); ?></td>
                <td><?php echo $item["product_quantity"]; ?></td>
                <td style="text-align: right;"><?php echo number_format($item["product_amount"], 2); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <div class="totals">
        <div><span>SUBTOTAL</span><span><?php echo number_format($row["order_gross_amount"], 2); ?></span></div>
        <?php
        $tax_total = 0;
        $object->query = "SELECT * FROM tax_table WHERE tax_status = 'Enable'";
        $object->execute();
        foreach($object->statement_result() as $tax):
            $tax_val = ($row["order_gross_amount"] * $tax["tax_percentage"]) / 100;
            $tax_total += $tax_val;
        ?>
        <div style="font-size: 10px;">
            <span><?php echo $tax["tax_name"]; ?> (<?php echo (float)$tax["tax_percentage"]; ?>%)</span>
            <span><?php echo number_format($tax_val, 2); ?></span>
        </div>
        <?php endforeach; ?>
        <div class="grand-total">
            <span>TOTAL (<?php echo $object->cur; ?>)</span>
            <span><?php echo number_format($row["order_net_amount"], 2); ?></span>
        </div>
    </div>

    <div class="qr">
        <img src="<?php echo $qr_url; ?>" width="90">
        <div style="font-size: 8px; margin-top: 5px; letter-spacing: 1px;">DIGITAL VERIFICATION CODE</div>
    </div>

    <div class="footer">
        <strong>THANKS FOR YOUR PATRONAGE</strong><br>
        <span>WAKANESA POS v3.0</span>
    </div>
</div>
</body>
</html>