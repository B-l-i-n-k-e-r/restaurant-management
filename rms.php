<?php
// rms.php - Centralized Restaurant Management Class (Updated)

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
            $this->connect = new PDO("mysql:host=localhost;dbname=restaurant-management", "root", "");
            $this->connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

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
    
    function is_user() { 
        return (isset($_SESSION['user_type']) && strcasecmp($_SESSION["user_type"], 'User') == 0); 
    }
    
    function is_master_user() { 
        return (isset($_SESSION['user_type']) && (strcasecmp($_SESSION["user_type"], 'Master') == 0 || strcasecmp($_SESSION["user_type"], 'Admin') == 0)); 
    }
    
    function is_waiter_user() { 
        return (isset($_SESSION['user_type']) && strcasecmp($_SESSION["user_type"], 'Waiter') == 0); 
    }
    
    function is_cashier_user() { 
        return (isset($_SESSION['user_type']) && strcasecmp($_SESSION["user_type"], 'Cashier') == 0); 
    }

    function is_kitchen_user() {
        return (isset($_SESSION['user_type']) && strcasecmp($_SESSION["user_type"], 'Kitchen') == 0);
    }

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

    // --- KITCHEN SPECIFIC METHODS (FIXED) ---

    // FIX: Include 'Preparing' so the kitchen knows how many active orders are in the room total
    function Get_total_kitchen_queue() {
        $this->query = "SELECT order_id FROM order_table WHERE order_status IN ('In Process', 'Preparing')";
        $this->execute();
        return $this->row_count();
    }

    // FIX: Ensure 'Ready' orders are counted properly for service
    function Get_total_ready_orders() {
        $this->query = "SELECT order_id FROM order_table WHERE order_status = 'Completed'";
        $this->execute();
        return $this->row_count();
    }

    function Get_waiter_name_by_order($order_id) {
        $this->query = "
            SELECT user_table.user_name 
            FROM order_table 
            INNER JOIN user_table ON user_table.user_id = order_table.order_waiter 
            WHERE order_table.order_id = :order_id
        ";
        $this->execute(['order_id' => $order_id]);
        $result = $this->statement_result();
        return $result[0]['user_name'] ?? 'Unknown Waiter';
    }

    // --- UTILITIES ---
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

    public function Get_cart_count() {
        if(isset($_SESSION["cart"])) {
            $count = 0;
            foreach($_SESSION["cart"] as $item) {
                $count += $item['quantity'];
            }
            return $count;
        }
        return 0;
    }

    public function make_avatar($character)
    {
        if (!file_exists("img")) {
            mkdir("img", 0777, true);
        }

        $path = "img/" . time() . ".png";
        $image = imagecreate(200, 200);
        $red = rand(0, 255);
        $green = rand(0, 255);
        $blue = rand(0, 255);

        imagecolorallocate($image, $red, $green, $blue);
        $textcolor = imagecolorallocate($image, 255, 255, 255);
        imagestring($image, 5, 90, 90, $character, $textcolor);
        imagepng($image, $path);
        imagedestroy($image);
        return $path;
    }

    public function get_datetime()
    {
        return date("Y-m-d H:i:s");
    }
}
?>