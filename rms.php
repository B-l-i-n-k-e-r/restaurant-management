<?php
// rms.php - Centralized Restaurant Management Class

class rms
{
    public $base_url = 'http://localhost:8000/'; 
    public $connect;
    public $query;
    public $statement;
    public $cur = 'Ksh';

    function __construct()
    {
        try {
            // Connect to your restaurant-management database
            $this->connect = new PDO("mysql:host=localhost;dbname=restaurant-management", "root", "");
            $this->connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Set system configurations
        date_default_timezone_set($this->Set_timezone());
        $this->cur = $this->Get_currency_symbol();
    }

    // --- DATABASE CORE METHODS ---
    function execute($data = null)
    {
        $this->statement = $this->connect->prepare($this->query);
        return $this->statement->execute($data);
    }

    function row_count()
    {
        return $this->statement->rowCount();
    }

    function statement_result()
    {
        return $this->statement->fetchAll(PDO::FETCH_ASSOC);
    }

    function get_result()
    {
        $this->statement = $this->connect->prepare($this->query);
        $this->statement->execute();
        return $this->statement->fetchAll(PDO::FETCH_ASSOC);
    }

    // --- AUTHENTICATION & SECURITY ---
    function is_login() { return isset($_SESSION['user_id']); }
    
    function is_user() { return (isset($_SESSION['user_type']) && $_SESSION["user_type"] == 'User'); }
    
    function is_master_user() { return (isset($_SESSION['user_type']) && ($_SESSION["user_type"] == 'Master' || $_SESSION["user_type"] == 'Admin')); }
    
    function is_waiter_user() { return (isset($_SESSION['user_type']) && $_SESSION["user_type"] == 'Waiter'); }
    
    function is_cashier_user() { return (isset($_SESSION['user_type']) && $_SESSION["user_type"] == 'Cashier'); }

    function Get_user_name($user_id)
    {
        $this->query = "SELECT user_name FROM user_table WHERE user_id = :user_id";
        $this->execute(['user_id' => $user_id]);
        $result = $this->statement_result();
        return $result[0]['user_name'] ?? 'Unknown';
    }

    function clean_input($string) { 
        return htmlspecialchars(stripslashes(trim($string))); 
    }

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
        return $result[0]['restaurant_logo'] ?? '';
    }

    function Set_timezone()
    {
        $this->query = "SELECT restaurant_timezone FROM restaurant_table LIMIT 1";
        $this->execute();
        $result = $this->statement_result();
        return $result[0]['restaurant_timezone'] ?? 'Africa/Nairobi';
    }

    function Get_currency_symbol()
    {
        $this->query = "SELECT restaurant_currency FROM restaurant_table LIMIT 1";
        $this->execute();
        $result = $this->statement_result();
        if($this->row_count() > 0) {
            $currency_code = strtoupper($result[0]['restaurant_currency']);
            foreach ($this->currency_array() as $c_row) {
                if ($c_row['code'] == $currency_code) return $c_row["symbol"];
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
            return $result[0]['user_profile'] ?? 'img/undraw_profile.svg';
        }
        return 'img/undraw_profile.svg';
    }

    // --- MENU & CATEGORY METHODS ---
    function Get_categories()
    {
        $this->query = "SELECT * FROM product_category_table WHERE category_status = 'Enable' ORDER BY category_name ASC";
        $this->execute();
        return $this->statement_result();
    }

    function Get_category_name($category_id)
    {
        $this->query = "SELECT category_name FROM product_category_table WHERE category_id = :category_id";
        $this->execute(['category_id' => $category_id]);
        $result = $this->statement_result();
        return $result[0]["category_name"] ?? '';
    }

    function Get_product_image($product_id)
    {
        $this->query = "SELECT product_image FROM product_table WHERE product_id = :product_id";
        $this->execute(['product_id' => $product_id]);
        $result = $this->statement_result();
        return $result[0]["product_image"] ?? 'img/no-image.png';
    }

    // --- ORDER & SALES PROCESSING ---
    function Generate_order_no()
    {
        $this->query = "SELECT MAX(order_id) as last_id FROM order_table";
        $this->execute();
        $result = $this->statement_result();
        $next_id = !empty($result[0]['last_id']) ? $result[0]['last_id'] + 1 : 1;
        return 'ORD-' . strtoupper(substr(md5($next_id), 0, 5));
    }

    function Get_total_today_sales()
    {
        $this->query = "SELECT SUM(order_net_amount) as total FROM order_table WHERE order_date = CURDATE() AND order_status = 'Completed'";
        $this->execute();
        $res = $this->statement_result();
        return $this->cur . ' ' . number_format($res[0]['total'] ?? 0, 2);
    }

    function Get_total_yesterday_sales()
    {
        $this->query = "SELECT SUM(order_net_amount) as total FROM order_table WHERE order_date = CURDATE() - INTERVAL 1 DAY AND order_status = 'Completed'";
        $this->execute();
        $res = $this->statement_result();
        return $this->cur . ' ' . number_format($res[0]['total'] ?? 0, 2);
    }

    function Get_last_seven_day_total_sales()
    {
        $this->query = "SELECT SUM(order_net_amount) as total FROM order_table WHERE order_date >= CURDATE() - INTERVAL 7 DAY AND order_status = 'Completed'";
        $this->execute();
        $res = $this->statement_result();
        return $this->cur . ' ' . number_format($res[0]['total'] ?? 0, 2);
    }

    function Get_total_sales()
    {
        $this->query = "SELECT SUM(order_net_amount) as total FROM order_table WHERE order_status = 'Completed'";
        $this->execute();
        $res = $this->statement_result();
        return $this->cur . ' ' . number_format($res[0]['total'] ?? 0, 2);
    }

    function Get_total_orders() {
        $this->query = "SELECT * FROM order_table";
        $this->execute();
        return $this->row_count();
    }

    function Get_total_tables() {
        $this->query = "SELECT * FROM table_data WHERE table_status = 'Enable'";
        $this->execute();
        return $this->row_count();
    }

    // --- UTILITIES ---
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

    function currency_array()
    {
        return array(
            array('code' => 'KSH', 'name' => 'Kenyan Shilling', 'symbol' => 'Ksh'),
            array('code' => 'USD', 'name' => 'United States Dollar', 'symbol' => '$'),
            array('code' => 'INR', 'name' => 'Indian Rupee', 'symbol' => '₹'),
            array('code' => 'GBP', 'name' => 'Pound Sterling', 'symbol' => '£'),
            array('code' => 'EUR', 'name' => 'Euro', 'symbol' => '€')
        );
    }

    function Get_user_today_orders($user_id)
    {
        $this->query = "SELECT COUNT(*) as total FROM order_table WHERE order_waiter = :user_id AND order_date = CURDATE()";
        $this->execute(['user_id' => $user_id]);
        $result = $this->statement_result();
        return $result[0]['total'] ?? 0;
    }

    function Get_user_pending_orders($user_id)
    {
        $this->query = "SELECT COUNT(*) as total FROM order_table WHERE order_waiter = :user_id AND order_status = 'In Process'";
        $this->execute(['user_id' => $user_id]);
        $result = $this->statement_result();
        return $result[0]['total'] ?? 0;
    }

    // --- CART FUNCTIONALITY ---
    public function Get_cart_count() {
        if(isset($_SESSION["cart"])) {
            $count = 0;
            foreach($_SESSION["cart"] as $item) {
                // Summing individual item quantities to get total items in cart
                $count += $item['quantity'];
            }
            return $count;
        }
        return 0;
    }
}
?>