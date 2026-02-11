<?php

// rms.php

class rms
{
    public $base_url = 'http://localhost:8000/';
    public $connect;
    public $query;
    public $statement;
    public $cur;

    function __construct()
    {
        try {
            $this->connect = new PDO("mysql:host=localhost;dbname=restaurant-management", "root", "");
            $this->connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }

        if ($this->Set_timezone() != '') {
            date_default_timezone_set($this->Set_timezone());
        }

        $temp_cur = $this->Get_currency_symbol();
        if ($temp_cur != '') {
            $this->cur = $temp_cur;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // --- DATABASE METHODS ---
    function execute($data = null)
    {
        $this->statement = $this->connect->prepare($this->query);
        if ($data) {
            $this->statement->execute($data);
        } else {
            $this->statement->execute();
        }
    }

    function row_count()
    {
        return $this->statement->rowCount();
    }

    function statement_result()
    {
        return $this->statement->fetchAll(PDO::FETCH_ASSOC);
    }

    // Inside your rms class in rms.php

function get_result()
{
    $this->statement = $this->connect->prepare($this->query);
    $this->statement->execute();
    // THE FIX: Use fetchAll to return the data as an associative array
    return $this->statement->fetchAll(PDO::FETCH_ASSOC);
}

    // --- AUTHENTICATION METHODS ---
    function is_login() { return isset($_SESSION['user_id']); }
    function is_master_user() { return (isset($_SESSION['user_type']) && $_SESSION["user_type"] == 'Master'); }
    function is_waiter_user() { return (isset($_SESSION['user_type']) && $_SESSION["user_type"] == 'Waiter'); }
    function is_cashier_user() { return (isset($_SESSION['user_type']) && $_SESSION["user_type"] == 'Cashier'); }

    function clean_input($string) { return htmlspecialchars(stripslashes(trim($string))); }

    // --- SYSTEM SETUP & CONFIG ---
    function Is_set_up_done()
    {
        $this->query = "SELECT restaurant_id FROM restaurant_table";
        $this->execute();
        return ($this->row_count() > 0);
    }

    function Get_restaurant_logo()
    {
        $this->query = "SELECT restaurant_logo FROM restaurant_table LIMIT 1";
        $result = $this->get_result();
        foreach ($result as $row) { return $row['restaurant_logo']; }
    }

    function Set_timezone()
    {
        $this->query = "SELECT restaurant_timezone FROM restaurant_table LIMIT 1";
        $result = $this->get_result();
        foreach ($result as $row) { return $row['restaurant_timezone']; }
        return 'Africa/Nairobi';
    }

    function Get_currency_symbol()
    {
        $this->query = "SELECT restaurant_currency FROM restaurant_table LIMIT 1";
        $result = $this->get_result();
        foreach ($result as $row) {
            $currency = $row['restaurant_currency'];
            $currency_data = $this->currency_array();
            foreach ($currency_data as $c_row) {
                if ($c_row['code'] == $currency) return $c_row["symbol"];
            }
        }
        return 'Ksh';
    }

    function Get_profile_image()
    {
        if (isset($_SESSION["user_id"])) {
            $this->query = "SELECT user_profile FROM user_table WHERE user_id = :user_id";
            $this->execute(['user_id' => $_SESSION["user_id"]]);
            $result = $this->statement_result();
            foreach ($result as $row) { return $row['user_profile']; }
        }
        return 'img/undraw_profile.svg';
    }

    // --- DASHBOARD DATA METHODS ---
    function Get_total_today_sales()
    {
        $this->query = "SELECT SUM(order_net_amount) as total FROM order_table WHERE order_date = CURDATE()";
        $result = $this->get_result();
        foreach ($result as $row) { return $this->cur . ' ' . number_format($row['total'] ?? 0, 2); }
    }

    function Get_total_yesterday_sales()
    {
        $this->query = "SELECT SUM(order_net_amount) as total FROM order_table WHERE order_date = CURDATE() - INTERVAL 1 DAY";
        $result = $this->get_result();
        foreach ($result as $row) { return $this->cur . ' ' . number_format($row['total'] ?? 0, 2); }
    }

    function Get_last_seven_day_total_sales()
    {
        $this->query = "SELECT SUM(order_net_amount) as total FROM order_table WHERE order_date >= CURDATE() - INTERVAL 7 DAY";
        $result = $this->get_result();
        foreach ($result as $row) { return $this->cur . ' ' . number_format($row['total'] ?? 0, 2); }
    }

    function Get_total_sales()
    {
        $this->query = "SELECT SUM(order_net_amount) as total FROM order_table";
        $result = $this->get_result();
        foreach ($result as $row) { return $this->cur . ' ' . number_format($row['total'] ?? 0, 2); }
    }

    // --- REGISTRATION METHODS ---
    function Currency_list()
    {
        $output = '<select name="restaurant_currency" id="restaurant_currency" class="form-control" required>';
        $output .= '<option value="">Select Currency</option>';
        foreach ($this->currency_array() as $row) {
            $output .= '<option value="' . $row["code"] . '">' . $row["name"] . ' (' . $row["code"] . ')</option>';
        }
        $output .= '</select>';
        return $output;
    }

    function Timezone_list()
    {
        $timezones = array(
            'Africa/Nairobi' => '(GMT+03:00) Nairobi',
            'Asia/Kolkata'   => '(GMT+05:30) India',
            'US/Eastern'     => '(GMT-05:00) Eastern Time',
            'Europe/London'  => '(GMT+00:00) London'
        );
        $output = '<select name="restaurant_timezone" id="restaurant_timezone" class="form-control" required>';
        $output .= '<option value="">Select Timezone</option>';
        foreach ($timezones as $key => $value) {
            $output .= '<option value="' . $key . '">' . $value . '</option>';
        }
        $output .= '</select>';
        return $output;
    }

    function make_avatar($character)
    {
        $path = "img/" . time() . ".png";
        $image = imagecreate(200, 200);
        $red = rand(0, 255); $green = rand(0, 255); $blue = rand(0, 255);
        imagecolorallocate($image, $red, $green, $blue);
        $textcolor = imagecolorallocate($image, 255, 255, 255);
        imagestring($image, 5, 90, 90, $character, $textcolor);
        imagepng($image, $path);
        imagedestroy($image);
        return $path;
    }

    function get_datetime() { return date("Y-m-d H:i:s"); }

    function currency_array()
    {
        return array(
            array('code' => 'KSH', 'name' => 'Kenyan Shilling', 'symbol' => 'Ksh'),
            array('code' => 'USD', 'name' => 'United States Dollar', 'symbol' => '&#36;'),
            array('code' => 'INR', 'name' => 'Indian Rupee', 'symbol' => '&#8377;'),
            array('code' => 'GBP', 'name' => 'Pound Sterling', 'symbol' => '&#163;'),
            array('code' => 'EUR', 'name' => 'Euro', 'symbol' => '&#8364;')
        );
    }

    // --- USER DATA METHODS ---
    function Get_user_today_orders($user_id)
    {
        $this->query = "SELECT COUNT(*) as total FROM order_table WHERE order_waiter = :user_id AND order_date = CURDATE()";
        $this->execute(['user_id' => $user_id]);
        $result = $this->statement_result();
        foreach ($result as $row) { return $row['total'] ?? 0; }
    }

    function Get_user_pending_orders($user_id)
    {
        $this->query = "SELECT COUNT(*) as total FROM order_table WHERE order_waiter = :user_id AND order_status = 'Pending'";
        $this->execute(['user_id' => $user_id]);
        $result = $this->statement_result();
        foreach ($result as $row) { return $row['total'] ?? 0; }
    }

    function Generate_order_no() { return 'ORD-' . date('YmdHis') . '-' . rand(100, 999); }

    function Get_user_name($user_id)
    {
        $this->query = "SELECT user_name FROM user_table WHERE user_id = :user_id";
        $this->execute(['user_id' => $user_id]);
        $result = $this->statement_result();
        foreach ($result as $row) { return $row['user_name']; }
    }

    // --- NEW METHODS FOR WAITER DASHBOARD (Properly inside the class) ---

    function Get_categories()
    {
        // Updated to use your correct table name: product_category_table
        $this->query = "SELECT * FROM product_category_table WHERE category_status = 'Enable' ORDER BY category_name ASC";
        $this->execute();
        return $this->statement_result();
    }

    
    function Get_products()
    {
        $this->query = "SELECT * FROM product_table WHERE product_status = 'Enable' ORDER BY product_id DESC";
        $this->execute();
        return $this->statement_result();
    }

    function Get_cart_count()
    {
        return isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
    }

    // --- ADD THESE TO FIX THE ADMIN FATAL ERROR ---
    function Get_total_orders()
    {
        $this->query = "SELECT * FROM order_table";
        $this->execute();
        return $this->row_count();
    }

    function Get_total_tables()
    {
        $this->query = "SELECT * FROM table_data WHERE table_status = 'Enable'";
        $this->execute();
        return $this->row_count();
    }

    // --- ADD THESE TO FIX THE CART REMOVAL ---
    function Get_cart_total()
    {
        $total = 0;
        if(isset($_SESSION['cart'])) {
            foreach($_SESSION['cart'] as $item) {
                $total += ($item['price'] * $item['quantity']);
            }
        }
        return $this->cur . ' ' . number_format($total, 2);
    }

} // End of rms class

?>