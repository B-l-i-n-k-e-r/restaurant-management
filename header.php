<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Restaurant Management System</title>

    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Poppins:200,300,400,600,700,800,900" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="vendor/parsley/parsley.css"/>
    <link rel="stylesheet" type="text/css" href="vendor/bootstrap-select/bootstrap-select.min.css"/>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

    <style>
        /* 1. GLOBAL UI RESET */
        :root {
            --neon-blue: #0ea5e9;
            --glass-white: rgba(255, 255, 255, 0.05);
            --sidebar-glass: rgba(15, 23, 42, 0.85);
        }

        #wrapper { display: flex; min-height: 100vh; background: transparent !important; }
        #content-wrapper { 
            background: transparent !important; 
            display: flex; 
            flex-direction: column; 
            width: 100%; 
        }
        #content { flex: 1 0 auto; background: transparent !important; }
        
        body {
            background: linear-gradient(rgba(15, 23, 42, 0.9), rgba(15, 23, 42, 0.9)), 
                        url('img/bg1.jpg') no-repeat center center fixed !important;
            background-size: cover !important;
            font-family: 'Poppins', sans-serif;
            color: #f8fafc;
        }

        /* 2. SIDEBAR: Frosted Dark Glass */
        #accordionSidebar {
            background: var(--sidebar-glass) !important;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-right: 1px solid rgba(255, 255, 255, 0.08);
            z-index: 1050;
        }

        .sidebar-brand { height: 5rem !important; }
        
        .sidebar-dark .nav-item .nav-link { 
            color: rgba(248, 250, 252, 0.6) !important; 
            font-weight: 500;
            padding: 1rem 1.5rem;
            transition: 0.2s;
        }

        .sidebar-dark .nav-item .nav-link:hover { 
            color: var(--neon-blue) !important; 
            background: rgba(14, 165, 233, 0.05);
        }
        
        .nav-item.active {
            background: linear-gradient(90deg, rgba(14, 165, 233, 0.15) 0%, transparent 100%) !important;
            border-left: 4px solid var(--neon-blue);
        }
        .nav-item.active .nav-link { color: #ffffff !important; }
        .nav-item.active .nav-link i { color: var(--neon-blue) !important; }

        .sidebar-heading {
            color: rgba(255, 255, 255, 0.25) !important;
            font-size: 0.65rem !important;
            letter-spacing: 2px;
            padding-left: 1.5rem;
            text-transform: uppercase;
        }

        /* 3. CONSTRAINT: Fit Content Global Tables */
        .table td, .table th { 
            white-space: nowrap !important; 
            width: 1% !important; 
            vertical-align: middle;
            border-top: 1px solid rgba(255, 255, 255, 0.05) !important;
        }
        
        .col-expand { width: auto !important; white-space: normal !important; }

        /* 4. TOPBAR: Floating Glass */
        .topbar {
            background: rgba(15, 23, 42, 0.4) !important;
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            height: 4.5rem;
        }

        #sidebarToggleTop {
            color: var(--neon-blue) !important;
            background: rgba(255, 255, 255, 0.03);
        }

        /* Live Clock Styling */
        .topbar-clock {
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.03);
            padding: 0.5rem 1.2rem;
            border-radius: 50px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            margin-left: 1rem;
        }
        #live-time {
            font-weight: 700;
            color: #fff;
            letter-spacing: 1px;
            font-size: 0.95rem;
        }
        #live-date {
            font-size: 0.75rem;
            color: var(--neon-blue);
            text-transform: uppercase;
            font-weight: 600;
            margin-right: 10px;
            padding-right: 10px;
            border-right: 1px solid rgba(255,255,255,0.1);
        }

        /* 5. DROPDOWNS */
        .dropdown-menu {
            background: #1e293b !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.5);
            padding: 10px;
        }

        .dropdown-item { 
            color: #cbd5e1 !important; 
            border-radius: 8px;
            margin-bottom: 2px;
        }
        .dropdown-item:hover { 
            background: var(--neon-blue) !important; 
            color: white !important; 
        }

        .img-profile {
            border: 2px solid var(--neon-blue);
            padding: 2px;
            box-shadow: 0 0 10px rgba(14, 165, 233, 0.3);
        }

        /* ===== FORCE DROPDOWN ALWAYS ON TOP ===== */
.topbar {
    position: relative;
    z-index: 2000;
}

.navbar-nav .dropdown-menu {
    position: absolute !important;
    z-index: 99999 !important;
}
    </style>
</head>

<body id="page-top">
    <div id="wrapper">
        <ul class="navbar-nav sidebar sidebar-dark accordion" id="accordionSidebar">
            
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?php echo ($object->is_user()) ? 'user_dashboard.php' : 'dashboard.php'; ?>">
                <?php
                if($object->Get_restaurant_logo() != '') {
                    echo '<img src="'.$object->Get_restaurant_logo().'" class="img-fluid" style="max-height: 50px; filter: drop-shadow(0 0 5px rgba(255,255,255,0.2));" />';
                } else {
                    echo '<div class="sidebar-brand-icon"><i class="fas fa-utensils text-info"></i></div>';
                    echo '<div class="sidebar-brand-text mx-3 font-weight-bold">RMS<span class="text-info">.</span></div>';
                }
                ?>
            </a>

            <hr class="sidebar-divider my-0" style="opacity: 0.1;">

            <?php 
                $current_page = basename($_SERVER['PHP_SELF']);
                
                if($_SESSION['user_type'] == 'Kitchen') { 
            ?>
                <li class="nav-item <?php if($current_page == 'kitchen_dashboard.php') echo 'active'; ?>">
                    <a class="nav-link" href="kitchen_dashboard.php">
                        <i class="fas fa-fw fa-columns"></i>
                        <span>Kitchen Desk</span>
                    </a>
                </li>
                <li class="nav-item <?php if($current_page == 'kitchen_orders.php') echo 'active'; ?>">
                    <a class="nav-link" href="kitchen_orders.php">
                        <i class="fas fa-fw fa-fire-alt"></i>
                        <span>Live Orders</span>
                    </a>
                </li>
                <li class="nav-item <?php if($current_page == 'kitchen_history.php') echo 'active'; ?>">
                    <a class="nav-link" href="kitchen_history.php">
                        <i class="fas fa-fw fa-history"></i>
                        <span>Preparation Log</span>
                    </a>
                </li>

            <?php 
                } else {
                    if($object->is_user()) { 
            ?>
                <li class="nav-item <?php if($current_page == 'user_dashboard.php') echo 'active'; ?>">
                    <a class="nav-link" href="user_dashboard.php">
                        <i class="fas fa-fw fa-utensils"></i>
                        <span>Menu Gallery</span>
                    </a>
                </li>
                <div class="sidebar-heading">ACCOUNT</div>
                <li class="nav-item <?php if($current_page == 'my_orders.php') echo 'active'; ?>">
                    <a class="nav-link" href="my_orders.php"><i class="fas fa-receipt"></i> <span>Order History</span></a>
                </li>

            <?php 
                } elseif($object->is_cashier_user() && !$object->is_master_user()) { 
            ?>
                <li class="nav-item <?php if($current_page == 'dashboard.php') echo 'active'; ?>">
                    <a class="nav-link" href="dashboard.php">
                        <i class="fas fa-fw fa-tachometer-alt"></i>
                        <span>Home</span>
                    </a>
                </li>
                <li class="nav-item <?php if($current_page == 'billing.php') echo 'active'; ?>">
                    <a class="nav-link" href="billing.php">
                        <i class="fas fa-cash-register"></i> 
                        <span>Checkout Desk</span>
                    </a>
                </li>

            <?php 
                } elseif($object->is_waiter_user() && !$object->is_master_user()) { 
            ?>
                <li class="nav-item <?php if($current_page == 'dashboard.php') echo 'active'; ?>">
                    <a class="nav-link" href="dashboard.php">
                        <i class="fas fa-fw fa-th"></i>
                        <span>Menu</span>
                    </a>
                </li>
                <li class="nav-item <?php if($current_page == 'order.php') echo 'active'; ?>">
                    <a class="nav-link" href="order.php">
                        <i class="fas fa-concierge-bell"></i> 
                        <span>Active Tables</span>
                    </a>
                </li>

            <?php 
                } elseif($object->is_master_user()) { 
            ?>
                <li class="nav-item <?php if($current_page == 'dashboard.php') echo 'active'; ?>">
                    <a class="nav-link" href="dashboard.php">
                        <i class="fas fa-fw fa-chart-line"></i>
                        <span>Overview</span>
                    </a>
                </li>
                <div class="sidebar-heading">CORE ASSETS</div>
                <li class="nav-item <?php if($current_page == 'category.php') echo 'active'; ?>">
                    <a class="nav-link" href="category.php"><i class="fas fa-layer-group"></i> <span>Categories</span></a>
                </li>
                <li class="nav-item <?php if($current_page == 'product.php') echo 'active'; ?>">
                    <a class="nav-link" href="product.php"><i class="fas fa-hamburger"></i> <span>Product List</span></a>
                </li>
                <li class="nav-item <?php if($current_page == 'table.php') echo 'active'; ?>">
                    <a class="nav-link" href="table.php"><i class="fas fa-vector-square"></i> <span>Table Layout</span></a>
                </li>
                
                <div class="sidebar-heading">WORKFLOW</div>
                <li class="nav-item <?php if($current_page == 'order.php') echo 'active'; ?>">
                    <a class="nav-link" href="order.php"><i class="fas fa-concierge-bell"></i> <span>Orders View</span></a>
                </li>
                <li class="nav-item <?php if($current_page == 'billing.php') echo 'active'; ?>">
                    <a class="nav-link" href="billing.php"><i class="fas fa-file-invoice-dollar"></i> <span>Biling</span></a>
                </li>

                <div class="sidebar-heading">SYSTEM</div>
                <li class="nav-item <?php if($current_page == 'user.php') echo 'active'; ?>">
                    <a class="nav-link" href="user.php"><i class="fas fa-user-shield"></i> <span>Staff Access</span></a>
                </li>
                <li class="nav-item <?php if($current_page == 'tax.php') echo 'active'; ?>">
                    <a class="nav-link" href="tax.php"><i class="fas fa-coins"></i> <span>Tax Config</span></a>
                </li>
            <?php } } ?>

            <hr class="sidebar-divider d-none d-md-block" style="opacity: 0.1; margin-top: 1.5rem;">
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle" style="background: rgba(255,255,255,0.05); color: var(--neon-blue);"></button>
            </div>
        </ul>

        <div id="content-wrapper">
            <div id="content">
                <nav class="navbar navbar-expand navbar-light topbar mb-4 static-top shadow">
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <div class="topbar-clock d-none d-sm-flex">
                        <span id="live-date"></span>
                        <span id="live-time"></span>
                    </div>
                    
                    <ul class="navbar-nav ml-auto">
                        <div class="topbar-divider d-none d-sm-block" style="border-right: 1px solid rgba(255,255,255,0.1);"></div>
                        <?php
                        $object->query = "SELECT * FROM user_table WHERE user_id = '".$_SESSION['user_id']."'";
                        $user_result = $object->get_result();
                        $user_name = 'User';
                        $user_profile_image = 'img/undraw_profile.svg';
                        foreach($user_result as $row) {
                            if($row['user_name'] != '') $user_name = $row['user_name'];
                            if($row['user_profile'] != '') $user_profile_image = $row['user_profile'];
                        }
                        ?>
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown">
                                <span class="mr-3 d-none d-lg-inline text-white-50 small"><?php echo $user_name; ?></span>
                                <img class="img-profile rounded-circle" src="<?php echo $user_profile_image; ?>" width="35" height="35">
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                                <a class="dropdown-item" href="profile.php">
                                    <i class="fas fa-user-circle fa-sm fa-fw mr-2 opacity-50"></i> My Profile
                                </a>
                                <?php if($object->is_master_user()) { ?>
                                    <a class="dropdown-item" href="setting.php">
                                        <i class="fas fa-sliders-h fa-sm fa-fw mr-2 opacity-50"></i> Settings
                                    </a>
                                <?php } ?>
                                <div class="dropdown-divider" style="opacity: 0.05;"></div>
                                <a class="dropdown-item text-danger" href="#" data-toggle="modal" data-target="#logoutModal">
                                    <i class="fas fa-power-off fa-sm fa-fw mr-2"></i> Sign Out
                                </a>
                            </div>
                        </li>
                    </ul>
                </nav>

                <script>
                function updateClock() {
                    const now = new Date();
                    const options = { weekday: 'short', month: 'short', day: 'numeric' };
                    const dateStr = now.toLocaleDateString('en-US', options);
                    const timeStr = now.toLocaleTimeString('en-US', { 
                        hour: '2-digit', 
                        minute: '2-digit', 
                        second: '2-digit',
                        hour12: true 
                    });

                    document.getElementById('live-date').textContent = dateStr;
                    document.getElementById('live-time').textContent = timeStr;
                }
                setInterval(updateClock, 1000);
                updateClock();
                </script>

                <div class="container-fluid">