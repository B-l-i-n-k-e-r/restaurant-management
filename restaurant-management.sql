-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 26, 2026 at 02:46 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `restaurant-management`
--

-- --------------------------------------------------------

--
-- Table structure for table `order_item_table`
--

CREATE TABLE `order_item_table` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_name` varchar(250) NOT NULL,
  `product_quantity` int(11) NOT NULL,
  `product_rate` decimal(12,2) NOT NULL,
  `product_amount` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `order_item_table`
--

INSERT INTO `order_item_table` (`order_item_id`, `order_id`, `product_name`, `product_quantity`, `product_rate`, `product_amount`) VALUES
(18, 43, 'Coffee', 1, 129.56, 129.56),
(19, 44, 'Chicken noodle soup', 1, 54.00, 54.00),
(20, 44, 'Caesar salad', 1, 120.87, 120.87),
(21, 44, 'Wine', 1, 3334.08, 3334.08),
(22, 45, 'Beef Stirfry', 1, 350.11, 350.11),
(23, 45, 'Bread rolls', 1, 43.87, 43.87),
(24, 45, 'Bruschetta', 1, 117.00, 117.00);

-- --------------------------------------------------------

--
-- Table structure for table `order_table`
--

CREATE TABLE `order_table` (
  `order_id` int(11) NOT NULL,
  `order_number` varchar(30) NOT NULL,
  `order_table` varchar(250) NOT NULL,
  `order_gross_amount` decimal(12,2) NOT NULL,
  `order_tax_amount` decimal(12,2) NOT NULL,
  `order_net_amount` decimal(12,2) NOT NULL,
  `order_date` date NOT NULL,
  `order_time` time NOT NULL,
  `order_waiter` varchar(250) NOT NULL,
  `order_cashier` varchar(250) NOT NULL,
  `order_status` enum('In Process','Completed') NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `order_table`
--

INSERT INTO `order_table` (`order_id`, `order_number`, `order_table`, `order_gross_amount`, `order_tax_amount`, `order_net_amount`, `order_date`, `order_time`, `order_waiter`, `order_cashier`, `order_status`, `payment_method`) VALUES
(39, 'ORD-20260217172127-977', 'Banquet', 0.00, 0.00, 0.00, '2026-02-17', '17:21:27', 'Vince', 'Kate', 'Completed', NULL),
(40, 'ORD-20260225222016-169', 'Walk-in', 0.00, 0.00, 0.00, '2026-02-26', '02:23:43', 'Vince', 'Kate', 'Completed', NULL),
(41, 'ORD-20260225222017-307', 'Walk-in', 0.00, 0.00, 0.00, '2026-02-26', '02:23:32', 'Vince', 'Kate', 'Completed', NULL),
(42, 'ORD-20260225222017-101', 'Walk-in', 0.00, 0.00, 0.00, '2026-02-26', '02:23:23', 'Vince', 'Kate', 'Completed', NULL),
(43, 'ORD-20260226000726-250', 'Hightop', 129.56, 10.36, 139.92, '2026-02-26', '00:07:26', 'Nancy', '', '', NULL),
(44, 'ORD-20260226003423-835', 'Bistro table', 3508.95, 280.72, 3789.67, '2026-02-26', '02:22:12', 'Nancy', 'Kate', 'Completed', NULL),
(45, 'ORD-6C834', 'Self-Order', 510.98, 0.00, 510.98, '2026-02-26', '04:29:03', 'Vince', '', 'In Process', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_tax_table`
--

CREATE TABLE `order_tax_table` (
  `order_tax_table_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `order_tax_name` varchar(200) NOT NULL,
  `order_tax_percentage` decimal(4,2) NOT NULL,
  `order_tax_amount` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `order_tax_table`
--

INSERT INTO `order_tax_table` (`order_tax_table_id`, `order_id`, `order_tax_name`, `order_tax_percentage`, `order_tax_amount`) VALUES
(119, 39, 'Service Tax', 6.00, 0.00),
(120, 39, 'Tip', 2.00, 0.00),
(121, 43, 'Service Tax', 6.00, 7.77),
(122, 43, 'Tip', 2.00, 2.59),
(123, 44, 'Service Tax', 6.00, 210.54),
(124, 44, 'Tip', 2.00, 70.18),
(125, 42, 'Service Tax', 6.00, 0.00),
(126, 42, 'Tip', 2.00, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `product_category_table`
--

CREATE TABLE `product_category_table` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(250) NOT NULL,
  `product_image` varchar(255) NOT NULL,
  `category_status` enum('Enable','Disable') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `product_category_table`
--

INSERT INTO `product_category_table` (`category_id`, `category_name`, `product_image`, `category_status`) VALUES
(2, 'Main Courses', '', 'Enable'),
(3, 'Vegan Dishes', '', 'Enable'),
(4, 'Desserts', '', 'Enable'),
(5, 'Seafood', '', 'Enable'),
(6, 'Beverages', '', 'Enable'),
(7, 'Salads', '', 'Enable'),
(8, 'Soups', '', 'Enable'),
(9, 'Appetizers', '', 'Enable'),
(14, 'Noodles', '', 'Enable'),
(15, 'Snacks', '', 'Enable');

-- --------------------------------------------------------

--
-- Table structure for table `product_table`
--

CREATE TABLE `product_table` (
  `product_id` int(11) NOT NULL,
  `category_name` varchar(250) NOT NULL,
  `product_name` varchar(250) NOT NULL,
  `product_image` varchar(255) NOT NULL,
  `product_price` decimal(10,2) NOT NULL,
  `product_status` enum('Enable','Disable') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `product_table`
--

INSERT INTO `product_table` (`product_id`, `category_name`, `product_name`, `product_image`, `product_price`, `product_status`) VALUES
(2, 'Appetizers', 'Spring rolls', '1772035997_Spring rolls.jpg', 100.01, 'Enable'),
(3, 'Appetizers', 'Garlic bread', '1772036028_Garlic bread.jpg', 111.00, 'Enable'),
(4, 'Appetizers', 'Mozzarella sticks', '1772036050_Mozzarella sticks.jpg', 115.00, 'Enable'),
(5, 'Appetizers', 'Bruschetta', '1772036081_Bruschetta.jpg', 117.00, 'Enable'),
(6, 'Beverages', 'Wine', '1772032221_Wine.jpg', 3334.08, 'Enable'),
(7, 'Beverages', 'Coffee', '1772035729_Coffee.jpg', 129.56, 'Enable'),
(8, 'Beverages', 'Lemonade', '1772035764_Lemonade.jpg', 134.33, 'Enable'),
(9, 'Desserts', 'Tiramisu', '1772035500_Tiramisu.jpg', 346.80, 'Enable'),
(10, 'Desserts', 'Chocolate Cake', '1772035434_Chocolate Cake.jpg', 765.08, 'Enable'),
(11, 'Desserts', 'Cheesecake', '1772035374_Cheesecake.jpg', 142.45, 'Enable'),
(12, 'Desserts', 'Ice cream sundae', '1772035473_Ice cream sundae.jpg', 59.01, 'Enable'),
(13, 'Main Courses', 'Roast Chicken', '1772035165_Roast chicken.jpg', 863.50, 'Enable'),
(14, 'Main Courses', 'Beef Stirfry', '1772035174_Beef stir-fry.jpg', 350.11, 'Enable'),
(15, 'Main Courses', 'Fish and Chips', '1772035188_Fish and chips.jpg', 560.43, 'Enable'),
(16, 'Main Courses', 'Spaghetti Bolognese', '1772035109_Spaghetti Bolognese.jpg', 320.02, 'Enable'),
(17, 'Main Courses', 'Grilled steak', '1772035151_Grilled steak.jpg', 1100.91, 'Enable'),
(19, 'Desserts', 'Fruit tart', '1772035551_Fruit tart.jpg', 87.90, 'Enable'),
(20, 'Beverages', 'Smoothies', '1772035808_Smoothies.jpg', 65.01, 'Enable'),
(21, 'Beverages', 'Iced tea', '1772035835_Iced tea.jpg', 54.00, 'Enable'),
(22, 'Appetizers', 'Chicken wings', '1772036122_Chicken wings.jpg', 143.03, 'Enable'),
(23, 'Soups', 'Tomato soup', '1772036292_Tomato soup.jpg', 43.09, 'Enable'),
(24, 'Soups', 'Chicken noodle soup', '1772036318_Chicken noodle soup.jpg', 54.00, 'Enable'),
(25, 'Soups', 'French onion soup', '1772036341_French onion soup.jpg', 56.66, 'Enable'),
(26, 'Soups', 'Miso soup', '1772036372_Miso soup.jpg', 122.11, 'Enable'),
(27, 'Soups', 'Clam chowder', '1772036399_Clam chowder.jpg', 131.76, 'Enable'),
(28, 'Salads', 'Caesar salad', '1772036539_Caesar salad.jpg', 120.87, 'Enable'),
(29, 'Salads', 'Greek salad', '1772036574_Greek salad.jpg', 143.90, 'Enable'),
(30, 'Salads', 'Cobb salad', '1772036599_Cobb salad.jpg', 122.11, 'Enable'),
(31, 'Salads', 'Caprese salad', '1772036639_Caprese salad.jpg', 99.67, 'Enable'),
(32, 'Salads', 'Garden salad', '1772036670_Garden salad.jpg', 87.77, 'Enable'),
(33, 'Seafood', 'Tuna tartare', '1772036816_Tuna tartare.jpg', 231.66, 'Enable'),
(34, 'Seafood', 'Crab cakes', '1772036844_Crab cakes.jpg', 221.32, 'Enable'),
(35, 'Seafood', 'Lobster bisque', '1772037049_Lobster bisque.jpg', 214.03, 'Enable'),
(36, 'Seafood', 'Shrimp scampi', '1772037081_Shrimp scampi.jpg', 265.12, 'Enable'),
(37, 'Seafood', 'Grilled salmon', '1772037102_Grilled salmon.jpg', 243.03, 'Enable'),
(38, 'Snacks', 'Bread rolls', '1772037665_Bread rolls.jpg', 43.87, 'Enable'),
(39, 'Snacks', 'Mashed potatoes', '1772037713_Mashed potatoes.jpg', 54.88, 'Enable'),
(40, 'Snacks', 'Coleslaw', '1772037753_Coleslaw.jpg', 55.65, 'Enable'),
(41, 'Snacks', 'Onion rings', '1772037785_Onion rings.jpg', 76.08, 'Enable'),
(42, 'Snacks', 'French fries', '1772037826_French fries.jpg', 76.08, 'Enable'),
(43, 'Noodles', 'Fettuccine Alfredo', '1772037889_Fettuccine Alfredo.jpg', 106.08, 'Enable'),
(44, 'Noodles', 'Spaghetti Carbonara', '1772037946_Spaghetti Carbonara.jpg', 88.70, 'Enable'),
(45, 'Noodles', 'Pad Thai', '1772037982_Pad Thai.jpg', 67.08, 'Enable'),
(46, 'Noodles', 'Macaroni and cheese', '1772038012_Macaroni and cheese.jpg', 112.98, 'Enable'),
(47, 'Noodles', 'Lasagna', '1772038044_Lasagna.jpg', 123.07, 'Enable'),
(48, 'Vegan Dishes', 'Mushroom risotto', '1772038097_Mushroom risotto.jpg', 231.67, 'Enable'),
(49, 'Vegan Dishes', 'Tofu stirfry', '1772038136_Tofu stir-fry.jpg', 180.01, 'Enable'),
(50, 'Vegan Dishes', 'Stuffed bell peppers', '1772038167_Stuffed bell peppers.jpg', 170.01, 'Enable'),
(51, 'Vegan Dishes', 'Lentil curry', '1772038224_Lentil curry.jpg', 150.81, 'Enable'),
(52, 'Vegan Dishes', 'Veggie burger', '1772038254_Veggie burger.jpg', 241.70, 'Enable');

-- --------------------------------------------------------

--
-- Table structure for table `restaurant_table`
--

CREATE TABLE `restaurant_table` (
  `restaurant_id` int(11) NOT NULL,
  `restaurant_name` varchar(250) NOT NULL,
  `restaurant_tag_line` varchar(300) NOT NULL,
  `restaurant_address` text NOT NULL,
  `restaurant_contact_no` varchar(30) NOT NULL,
  `restaurant_email` varchar(250) NOT NULL,
  `restaurant_currency` varchar(10) NOT NULL,
  `restaurant_timezone` varchar(250) NOT NULL,
  `restaurant_logo` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `restaurant_table`
--

INSERT INTO `restaurant_table` (`restaurant_id`, `restaurant_name`, `restaurant_tag_line`, `restaurant_address`, `restaurant_contact_no`, `restaurant_email`, `restaurant_currency`, `restaurant_timezone`, `restaurant_logo`) VALUES
(1, 'Wakanesa Restaurant', '#wakanesa', 'City Square 00200, Nairobi Kenya', '0797369845', 'wakanesarestaurant@admin.com', 'KSH', 'Africa/Nairobi', 'images/60986845.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `table_data`
--

CREATE TABLE `table_data` (
  `table_id` int(11) NOT NULL,
  `table_name` varchar(250) NOT NULL,
  `table_capacity` int(11) NOT NULL,
  `table_status` enum('Enable','Disable') NOT NULL,
  `waiter_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `table_data`
--

INSERT INTO `table_data` (`table_id`, `table_name`, `table_capacity`, `table_status`, `waiter_id`) VALUES
(4, 'Booth table', 6, 'Enable', 4),
(5, 'Banquet table', 12, 'Enable', 4),
(6, 'Outdoor table', 6, 'Enable', 4),
(7, 'Hightop', 4, 'Enable', 4),
(10, 'Bistro table', 4, 'Enable', 4),
(12, 'Portable table', 8, 'Enable', 4),
(13, 'Round dining table', 8, 'Enable', 4),
(14, 'Communal', 12, 'Enable', 4);

-- --------------------------------------------------------

--
-- Table structure for table `tax_table`
--

CREATE TABLE `tax_table` (
  `tax_id` int(11) NOT NULL,
  `tax_name` varchar(250) NOT NULL,
  `tax_percentage` decimal(4,2) NOT NULL,
  `tax_status` enum('Enable','Disable') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `tax_table`
--

INSERT INTO `tax_table` (`tax_id`, `tax_name`, `tax_percentage`, `tax_status`) VALUES
(1, 'Service Tax', 6.00, 'Enable'),
(2, 'Tip', 2.00, 'Enable');

-- --------------------------------------------------------

--
-- Table structure for table `user_table`
--

CREATE TABLE `user_table` (
  `user_id` int(11) NOT NULL,
  `user_name` varchar(250) NOT NULL,
  `user_contact_no` varchar(30) NOT NULL,
  `user_email` varchar(30) NOT NULL,
  `user_password` varchar(250) NOT NULL,
  `user_profile` varchar(250) NOT NULL,
  `user_type` enum('Master','Waiter','Cashier','User') NOT NULL,
  `user_status` enum('Enable','Disable') NOT NULL,
  `user_created_on` datetime NOT NULL,
  `reset_request` int(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `user_table`
--

INSERT INTO `user_table` (`user_id`, `user_name`, `user_contact_no`, `user_email`, `user_password`, `user_profile`, `user_type`, `user_status`, `user_created_on`, `reset_request`) VALUES
(1, 'Admin', '0123456789', 'admin@admin.com', '123456', 'images/1404676967.png', 'Master', 'Enable', '2023-12-12 00:46:38', 0),
(3, 'Kate', '0234156789', 'kathrynmwamburi@gmail.com', '$2y$10$JpzpRoi/ugMxQ/X/LDgsUOXa7kjZ9q.jplSdqUGuWrPUllyjLpMWq', 'images/1770407092_698644b4628e1.jpg', 'Cashier', 'Enable', '2026-02-07 03:44:52', 0),
(4, 'Nancy', '0705681215', 'nancy@gmail.com', '$2y$10$LbqhlaR189FxPxj2ALQlPuC.xY2rRp9YuPwbxq2QlBoue4eR8P30.', 'img/2071241283.jpg', 'Waiter', 'Enable', '2026-02-07 06:07:34', 0),
(8, 'Vince', '12345678', 'vinniemariba2004@gmail.com', '$2y$10$SWeQWA.7Rj.m4LZU1pYeSuAaK3bcjMPQHqoGF5BmqAcrNjb.I8laq', 'img/1772041050.png', 'User', 'Enable', '2026-02-25 20:37:30', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `order_item_table`
--
ALTER TABLE `order_item_table`
  ADD PRIMARY KEY (`order_item_id`);

--
-- Indexes for table `order_table`
--
ALTER TABLE `order_table`
  ADD PRIMARY KEY (`order_id`);

--
-- Indexes for table `order_tax_table`
--
ALTER TABLE `order_tax_table`
  ADD PRIMARY KEY (`order_tax_table_id`);

--
-- Indexes for table `product_category_table`
--
ALTER TABLE `product_category_table`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `product_table`
--
ALTER TABLE `product_table`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `restaurant_table`
--
ALTER TABLE `restaurant_table`
  ADD PRIMARY KEY (`restaurant_id`);

--
-- Indexes for table `table_data`
--
ALTER TABLE `table_data`
  ADD PRIMARY KEY (`table_id`);

--
-- Indexes for table `tax_table`
--
ALTER TABLE `tax_table`
  ADD PRIMARY KEY (`tax_id`);

--
-- Indexes for table `user_table`
--
ALTER TABLE `user_table`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `order_item_table`
--
ALTER TABLE `order_item_table`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `order_table`
--
ALTER TABLE `order_table`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `order_tax_table`
--
ALTER TABLE `order_tax_table`
  MODIFY `order_tax_table_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=127;

--
-- AUTO_INCREMENT for table `product_category_table`
--
ALTER TABLE `product_category_table`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `product_table`
--
ALTER TABLE `product_table`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `restaurant_table`
--
ALTER TABLE `restaurant_table`
  MODIFY `restaurant_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `table_data`
--
ALTER TABLE `table_data`
  MODIFY `table_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `tax_table`
--
ALTER TABLE `tax_table`
  MODIFY `tax_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_table`
--
ALTER TABLE `user_table`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
