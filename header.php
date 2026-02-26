<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Restaurant Management System</title>

    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,300,400,600,700,800,900" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="vendor/parsley/parsley.css"/>
    <link rel="stylesheet" type="text/css" href="vendor/bootstrap-select/bootstrap-select.min.css"/>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

    <style>
        /* 1. GLOBAL WRAPPERS */
        #wrapper { display: flex; min-height: 100vh; background: transparent !important; }
        #content-wrapper { 
            background: transparent !important; 
            display: flex; 
            flex-direction: column; 
            width: 100%; 
        }
        #content { flex: 1 0 auto; background: transparent !important; }
        
        body {
            background: linear-gradient(rgba(0, 0, 0, 0.75), rgba(0, 0, 0, 0.75)), 
                        url('img/bg1.jpg') no-repeat center center fixed !important;
            background-size: cover !important;
            min-height: 100vh;
            margin: 0;
            color: white;
        }

        /* 2. SIDEBAR: Frosted Glass */
        #accordionSidebar {
            background: rgba(255, 255, 255, 0.08) !important;
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border-right: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
            z-index: 1050;
        }

        .sidebar-dark .nav-item .nav-link { 
            color: rgba(255,255,255,0.7) !important; 
            font-weight: 500;
        }
        .sidebar-dark .nav-item .nav-link:hover { 
            color: #ffffff !important; 
            background: rgba(255,255,255,0.05);
        }
        
        .nav-item.active {
            background: rgba(255, 255, 255, 0.12) !important;
            border-left: 4px solid #17a2b8;
        }
        .nav-item.active .nav-link { color: #ffffff !important; }
        .nav-item.active .nav-link i { color: #17a2b8 !important; }

        .sidebar-heading {
            color: rgba(255, 255, 255, 0.4) !important;
            font-size: 0.65rem !important;
            letter-spacing: 1px;
            margin-top: 1.5rem;
        }

        /* 3. TOPBAR */
        .topbar {
            background: rgba(255, 255, 255, 0.03) !important;
            backdrop-filter: blur(15px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            z-index: 1040;
        }

        #sidebarToggleTop {
            color: #ffffff !important;
            background: rgba(255, 255, 255, 0.1);
        }

        /* 4. PROFILE DROPDOWN */
        .dropdown-menu {
            background: rgba(30, 30, 35, 0.98) !important;
            backdrop-filter: blur(20px) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            border-radius: 12px;
            margin-top: 10px !important;
        }

        .dropdown-item { 
            color: rgba(255,255,255,0.8) !important; 
            padding: 10px 20px;
        }
        .dropdown-item:hover { 
            background: rgba(23, 162, 184, 0.2) !important; 
            color: #17a2b8 !important; 
        }

        /* 5. NOTIFICATION BADGE */
        @keyframes pulse-badge {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.2); opacity: 0.8; }
            100% { transform: scale(1); opacity: 1; }
        }
        .badge-counter-reset { 
            animation: pulse-badge 2s infinite; 
            font-size: 0.7rem;
            position: absolute;
            top: 10px;
            right: 5px;
        }

        .img-profile {
            border: 2px solid rgba(255, 255, 255, 0.2);
        }
    </style>
</head>

<body id="page-top">
    <div id="wrapper">
        <ul class="navbar-nav sidebar sidebar-dark accordion" id="accordionSidebar">
            
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?php echo ($object->is_user()) ? 'user_dashboard.php' : 'dashboard.php'; ?>">
                <?php
                if($object->Get_restaurant_logo() != '') {
                    echo '<img src="'.$object->Get_restaurant_logo().'" class="img-fluid" style="max-height: 45px;" />';
                } else {
                    echo '<div class="sidebar-brand-icon rotate-n-15"><i class="fas fa-utensils"></i></div>';
                    echo '<div class="sidebar-brand-text mx-3">RMS</div>';
                }
                ?>
            </a>

            <hr class="sidebar-divider my-0">

            <?php 
                $dash_link = ($object->is_user()) ? 'user_dashboard.php' : 'dashboard.php';
                $dash_label = ($object->is_user()) ? 'Main Menu' : 'Dashboard';
                $is_active = (basename($_SERVER['PHP_SELF']) == $dash_link) ? 'active' : '';
            ?>
            <li class="nav-item <?php echo $is_active; ?>">
                <a class="nav-link" href="<?php echo $dash_link; ?>">
                    <i class="fas fa-fw fa-th-large"></i>
                    <span><?php echo $dash_label; ?></span>
                </a>
            </li>

            <?php if($object->is_master_user()) { 
                $object->query = "SELECT COUNT(user_id) as total FROM user_table WHERE reset_request = 1";
                $res_count = $object->get_result();
                $reset_total = 0;
                foreach($res_count as $row_c) { $reset_total = $row_c['total']; }
            ?>
                <div class="sidebar-heading">ADMINISTRATION</div>
                
                <li class="nav-item <?php if(basename($_SERVER['PHP_SELF']) == 'category.php') echo 'active'; ?>">
                    <a class="nav-link" href="category.php"><i class="fas fa-th-list"></i> <span>Categories</span></a>
                </li>
                
                <li class="nav-item <?php if(basename($_SERVER['PHP_SELF']) == 'table.php') echo 'active'; ?>">
                    <a class="nav-link" href="table.php"><i class="fas fa-couch"></i> <span>Tables</span></a>
                </li>
                
                <li class="nav-item <?php if(basename($_SERVER['PHP_SELF']) == 'tax.php') echo 'active'; ?>">
                    <a class="nav-link" href="tax.php"><i class="fas fa-percent"></i> <span>Taxes</span></a>
                </li>
                
                <li class="nav-item <?php if(basename($_SERVER['PHP_SELF']) == 'product.php') echo 'active'; ?>">
                    <a class="nav-link" href="product.php"><i class="fas fa-utensils"></i> <span>Products</span></a>
                </li>
                
                <li class="nav-item <?php if(basename($_SERVER['PHP_SELF']) == 'user.php') echo 'active'; ?>">
                    <a class="nav-link position-relative" href="user.php">
                        <i class="fas fa-users-cog"></i> 
                        <span>Users</span>
                        <?php if($reset_total > 0) { ?>
                            <span class="badge badge-danger badge-counter-reset"><?php echo $reset_total; ?></span>
                        <?php } ?>
                    </a>
                </li>
            <?php } ?>

            <?php if($object->is_user()) { ?>
                <div class="sidebar-heading">PERSONAL</div>
                <li class="nav-item <?php if(basename($_SERVER['PHP_SELF']) == 'my_orders.php') echo 'active'; ?>">
                    <a class="nav-link" href="my_orders.php"><i class="fas fa-receipt"></i> <span>My Orders</span></a>
                </li>
            <?php } ?>

            <?php if($object->is_waiter_user() || $object->is_master_user()) { ?>
                <li class="nav-item <?php if(basename($_SERVER['PHP_SELF']) == 'order.php') echo 'active'; ?>">
                    <a class="nav-link" href="order.php">
                        <i class="fas fa-concierge-bell"></i> 
                        <span>Service Orders</span>
                    </a>
                </li>
            <?php } ?>

            <?php if($object->is_cashier_user() || $object->is_master_user()) { ?>
                <li class="nav-item <?php if(basename($_SERVER['PHP_SELF']) == 'billing.php') echo 'active'; ?>">
                    <a class="nav-link" href="billing.php">
                        <i class="fas fa-file-invoice-dollar"></i> 
                        <span>Billing Area</span>
                    </a>
                </li>
            <?php } ?>

            <hr class="sidebar-divider d-none d-md-block">
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>
        </ul>

        <div id="content-wrapper">
            <div id="content">
                <nav class="navbar navbar-expand navbar-light topbar mb-4 static-top shadow">
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>
                    
                    <ul class="navbar-nav ml-auto">
                        <div class="topbar-divider d-none d-sm-block"></div>
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
                                <span class="mr-3 d-none d-lg-inline text-white small"><?php echo $user_name; ?></span>
                                <img class="img-profile rounded-circle" src="<?php echo $user_profile_image; ?>" width="32" height="32">
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in">
                                <a class="dropdown-item" href="profile.php">
                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-info"></i> Profile
                                </a>
                                <?php if($object->is_master_user()) { ?>
                                    <a class="dropdown-item" href="setting.php">
                                        <i class="fas fa-cogs fa-sm fa-fw mr-2 text-info"></i> Settings
                                    </a>
                                <?php } ?>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-danger"></i> Logout
                                </a>
                            </div>
                        </li>
                    </ul>
                </nav>
                <div class="container-fluid">