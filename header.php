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

    <style>
        /* 1. GLOBAL WRAPPERS: Background & Transparency */
        #wrapper, #content-wrapper, #content { background: transparent !important; }
        
        body {
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), 
                        url('img/bg1.jpg') no-repeat center center fixed !important;
            background-size: cover !important;
            min-height: 100vh;
        }

        /* 2. SIDEBAR: Frosted Glass */
        #accordionSidebar {
            background: rgba(255, 255, 255, 0.1) !important;
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-right: 1px solid rgba(255, 255, 255, 0.15);
        }

        .sidebar-dark .nav-item .nav-link { color: #ffffff !important; font-weight: 600; }
        .sidebar-dark .nav-item .nav-link i { color: rgba(255, 255, 255, 0.8) !important; }
        .sidebar-dark .nav-item.active .nav-link { 
            background: rgba(255, 255, 255, 0.15); 
            border-radius: 0 50px 50px 0; 
            margin-right: 10px; 
        }

        /* 3. TOPBAR: Frosted Glass */
        .topbar {
            background: rgba(255, 255, 255, 0.05) !important;
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* 4. LIVE TABLE STATUS: Scrollbar Removal & Grid Layout */
        .table-responsive {
            overflow: hidden !important; 
            scrollbar-width: none; 
            -ms-overflow-style: none;
        }
        .table-responsive::-webkit-scrollbar { display: none; }

        #table_status {
            display: flex;
            flex-wrap: wrap; 
            gap: 15px;
            padding: 10px 0;
        }

        /* Styling the cards that come in via AJAX */
        #table_status .card {
            background: rgba(255, 255, 255, 0.05) !important;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            border-radius: 12px;
            color: #fff !important;
            flex: 1 1 calc(25% - 15px); 
            min-width: 180px; 
            transition: transform 0.3s ease;
        }
        
        #table_status .card:hover { transform: translateY(-5px); background: rgba(255, 255, 255, 0.1) !important; }
        #table_status .text-muted, #table_status .small { color: rgba(255, 255, 255, 0.6) !important; }

        /* Profile Dropdown Styling */
        .dropdown-menu {
            background: rgba(30, 30, 30, 0.9) !important;
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .dropdown-item { color: #fff !important; }
        .dropdown-item:hover { background: rgba(255, 255, 255, 0.15) !important; }

        /* Notification Badge Animation */
        @keyframes pulse-badge {
            0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(231, 74, 59, 0.7); }
            70% { transform: scale(1.1); box-shadow: 0 0 0 5px rgba(231, 74, 59, 0); }
            100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(231, 74, 59, 0); }
        }
        .badge-counter-reset {
            font-size: 0.6rem;
            padding: 2px 5px;
            vertical-align: top;
            margin-left: 5px;
            animation: pulse-badge 2s infinite;
        }
    </style>
</head>

<body id="page-top">

    <div id="wrapper">

        <ul class="navbar-nav sidebar sidebar-dark accordion" id="accordionSidebar">

            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="dashboard.php">
                <?php
                if($object->Get_restaurant_logo() != '') {
                    echo '<img src="'.$object->Get_restaurant_logo().'" class="img-fluid" style="max-height: 45px;" />';
                } else {
                    echo '<div class="sidebar-brand-icon rotate-n-15"><i class="fas fa-utensils"></i></div>';
                    echo '<div class="sidebar-brand-text mx-3">Admin</div>';
                }
                ?>
            </a>

            <hr class="sidebar-divider my-0">

            <li class="nav-item">
                <a class="nav-link" href="dashboard.php"><i class="fas fa-fw fa-tachometer-alt"></i><span>Dashboard</span></a>
            </li>

            <?php if($object->is_master_user()) { 
                // FETCH RESET REQUEST COUNT
                $object->query = "SELECT COUNT(user_id) as total FROM user_table WHERE reset_request = 1";
                $res_count = $object->get_result();
                $reset_total = 0;
                foreach($res_count as $row_c) { $reset_total = $row_c['total']; }
            ?>
                <div class="sidebar-heading" style="opacity: 0.5; font-size: 0.7rem; padding-top: 1rem;">MANAGEMENT</div>
                <li class="nav-item"><a class="nav-link" href="category.php"><i class="fas fa-th-list"></i> <span>Categories</span></a></li>
                <li class="nav-item"><a class="nav-link" href="table.php"><i class="fas fa-couch"></i> <span>Tables</span></a></li>
                <li class="nav-item"><a class="nav-link" href="tax.php"><i class="fas fa-percent"></i> <span>Taxes</span></a></li>
                <li class="nav-item"><a class="nav-link" href="product.php"><i class="fas fa-utensils"></i> <span>Products</span></a></li>
                <li class="nav-item">
                    <a class="nav-link" href="user.php">
                        <i class="fas fa-users-cog"></i> 
                        <span>Users</span>
                        <?php if($reset_total > 0) { ?>
                            <span class="badge badge-danger badge-counter-reset"><?php echo $reset_total; ?></span>
                        <?php } ?>
                    </a>
                </li>
            <?php } ?>

            <?php if($object->is_waiter_user() || $object->is_master_user()) { ?>
                <li class="nav-item"><a class="nav-link" href="order.php"><i class="far fa-edit"></i> <span>Orders</span></a></li>
            <?php } ?>

            <?php if($object->is_cashier_user() || $object->is_master_user()) { ?>
                <li class="nav-item"><a class="nav-link" href="billing.php"><i class="fas fa-file-invoice"></i> <span>Billing</span></a></li>
            <?php } ?>

            <hr class="sidebar-divider d-none d-md-block">
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>
        </ul>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">

                <nav class="navbar navbar-expand navbar-light topbar mb-4 static-top shadow">
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars text-white"></i>
                    </button>

                    <ul class="navbar-nav ml-auto">
                        <div class="topbar-divider d-none d-sm-block"></div>

                        <?php
                        $object->query = "SELECT * FROM user_table WHERE user_id = '".$_SESSION['user_id']."'";
                        $user_result = $object->get_result();
                        $user_name = 'Master';
                        $user_profile_image = 'img/undraw_profile.svg';

                        foreach($user_result as $row) {
                            if($row['user_name'] != '') $user_name = $row['user_name'];
                            if($row['user_profile'] != '') $user_profile_image = $row['user_profile'];
                        }
                        ?>

                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown">
                                <span class="mr-2 d-none d-lg-inline text-white small"><?php echo $user_name; ?></span>
                                <img class="img-profile rounded-circle" src="<?php echo $user_profile_image; ?>">
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in">
                                <a class="dropdown-item" href="profile.php"><i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i> Profile</a>
                                <?php if($object->is_master_user()) { ?>
                                    <a class="dropdown-item" href="setting.php"><i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i> Settings</a>
                                <?php } ?>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i> Logout
                                </a>
                            </div>
                        </li>
                    </ul>
                </nav>
                <div class="container-fluid">