<?php
include('rms.php');
$object = new rms();

if(!$object->is_login()) {
    header("location:".$object->base_url);
    exit;
}
include('header.php');
?>

<style>
/* Card styles */
.glass-card {
    backdrop-filter: blur(15px);
    -webkit-backdrop-filter: blur(15px);
    border-radius: 16px;
    box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3);
    transition: all 0.3s ease-in-out;
    opacity: 0;
    animation: dashFadeUp 0.6s ease forwards;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.glass-primary { background: rgba(78, 115, 223, 0.15) !important; border-left: 5px solid #4e73df !important; }
.glass-success { background: rgba(28, 200, 138, 0.15) !important; border-left: 5px solid #1cc88a !important; }
.glass-info    { background: rgba(54, 185, 204, 0.15) !important; border-left: 5px solid #36b9cc !important; }
.glass-warning { background: rgba(246, 194, 62, 0.15) !important; border-left: 5px solid #f6c23e !important; }

.glass-card:hover { transform: translateY(-8px); filter: brightness(1.2); }

h1.h3 { color: #ffffff !important; font-weight: 700; text-shadow: 2px 2px 10px rgba(0,0,0,0.5); }
.glass-card .h5 { color: #ffffff !important; font-weight: 800; }
.text-primary { color: #85a5ff !important; }
.text-success { color: #42eba8 !important; }
.text-info    { color: #5adbe2 !important; }
.text-warning { color: #ffd970 !important; }

@keyframes dashFadeUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
.row > div:nth-child(1) .glass-card { animation-delay: 0.1s; }
.row > div:nth-child(2) .glass-card { animation-delay: 0.2s; }
.row > div:nth-child(3) .glass-card { animation-delay: 0.3s; }
.row > div:nth-child(4) .glass-card { animation-delay: 0.4s; }
</style>

<div class="container-fluid">
    <h1 class="h3 mb-4">Dashboard</h1>

    <div class="row">
    <?php 
    if($object->is_master_user()) { 
        $cards = [
            ["Today Sales", "primary", "chart-line", $object->Get_total_today_sales()],
            ["Yesterday Sales", "success", "history", $object->Get_total_yesterday_sales()],
            ["Last 7 Days Sales", "info", "calendar-week", $object->Get_last_seven_day_total_sales()],
            ["All Time Sales", "warning", "coins", $object->Get_total_sales()]
        ];
    } else {
        $cards = [
            ["Your Orders Today", "primary", "receipt", $object->Get_user_today_orders($_SESSION['user_id'])],
            ["Pending Orders", "success", "hourglass-half", $object->Get_user_pending_orders($_SESSION['user_id'])]
        ];
    }

    foreach($cards as $card) {
        echo '
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card glass-card glass-'.$card[1].' h-100 border-0">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-'.$card[1].' text-uppercase mb-1">'.$card[0].'</div>
                            <div class="h5 mb-0 font-weight-bold">'.$card[3].'</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-'.$card[2].' fa-2x text-white-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>';
    }
    ?>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card glass-card mb-4" style="background: rgba(255,255,255,0.08) !important;">
                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-white"><i class="fas fa-broadcast-tower text-success mr-2"></i>Live Table Status</h6>
                    <span class="badge badge-pill badge-primary">Auto-refreshing</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <div id="table_status"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>

<script>
$(document).ready(function(){
    function refresh_table_status() {
        $.ajax({
            url: "order_action.php",
            method: "POST",
            data: { action: 'dashboard_reset' },
            success: function(data) {
                $('#table_status').html(data);
            }
        });
    }
    refresh_table_status();
    setInterval(refresh_table_status, 5000);
});
</script>