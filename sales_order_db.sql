-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
<<<<<<< HEAD
-- Generation Time: Nov 05, 2026 at 07:32 PM
=======
-- Generation Time: Nov 05, 2026 at 07:37 PM
>>>>>>> 69f781ceed8cb9ec6d11f6494631e01816036ed0
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
-- Database: `sales_order_db`
--

-- --------------------------------------------------------

--
<<<<<<< HEAD
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `status` enum('Pending','Completed','Cancelled') DEFAULT 'Pending',
=======
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `log_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `details` varchar(255) DEFAULT NULL,
  `log_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`log_id`, `action`, `details`, `log_time`) VALUES
(1, 'CUSTOMER_CREATED', 'Customer Prajwal gokhale (prajwalgokhale93@gmail.com) created', '2026-10-11 20:16:26'),
(2, 'PRODUCT_CREATED', 'Product bangles added', '2026-10-11 20:17:00'),
(3, 'ORDER_PLACED', 'Order #1: 1 x bangles; total 500.00', '2026-10-11 20:17:34'),
(4, 'PAYMENT_MARKED_PAID', 'Payment for order #1 marked paid', '2026-10-11 20:19:38'),
(5, 'CUSTOMER_CREATED', 'Customer shree (shrrre@gmail.com) created', '2026-11-04 20:00:12'),
(6, 'PRODUCT_CREATED', 'Product tv added', '2026-11-04 20:00:37'),
(7, 'ORDER_PLACED', 'Order #2: 1 x tv; total 1999.00', '2026-11-04 20:00:59'),
(8, 'PAYMENT_MARKED_PAID', 'Payment for order #2 marked paid', '2026-11-04 20:01:16');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(120) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
>>>>>>> 69f781ceed8cb9ec6d11f6494631e01816036ed0
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
<<<<<<< HEAD
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_price`, `status`, `created_at`) VALUES
(1, 1, 20.00, 'Pending', '2026-11-05 17:34:45'),
(3, 3, 50000.00, 'Pending', '2026-11-05 18:05:48'),
(4, 3, 50020.00, 'Pending', '2026-11-05 18:09:44'),
(5, 3, 50000.00, 'Pending', '2026-11-05 18:10:51'),
(6, 3, 20.00, 'Completed', '2026-11-05 18:13:00'),
(7, 3, 500.00, 'Pending', '2026-11-05 18:31:28');
=======
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`customer_id`, `full_name`, `email`, `phone`, `created_at`) VALUES
(1, 'Hariom Demo Customer', 'hariom@example.com', '9876543210', '2026-10-11 20:05:49'),
(2, 'Ananya Sharma', 'ananya@example.com', '9876501234', '2026-10-11 20:05:49'),
(3, 'Prajwal gokhale', 'prajwalgokhale93@gmail.com', '09535915476', '2026-10-11 20:16:26'),
(4, 'shree', 'shrrre@gmail.com', '9535915476', '2026-11-04 20:00:12');
>>>>>>> 69f781ceed8cb9ec6d11f6494631e01816036ed0

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
<<<<<<< HEAD
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
=======
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL CHECK (`quantity` > 0),
  `unit_price` decimal(10,2) NOT NULL CHECK (`unit_price` >= 0),
  `line_total` decimal(10,2) NOT NULL
>>>>>>> 69f781ceed8cb9ec6d11f6494631e01816036ed0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

<<<<<<< HEAD
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(1, 1, 1, 1, 20.00),
(4, 3, 2, 1, 50000.00),
(5, 4, 1, 1, 20.00),
(6, 4, 2, 1, 50000.00),
(7, 5, 2, 1, 50000.00),
(8, 6, 1, 1, 20.00),
(9, 7, 3, 1, 500.00);
=======
INSERT INTO `order_items` (`order_item_id`, `order_id`, `product_id`, `quantity`, `unit_price`, `line_total`) VALUES
(1, 1, 5, 1, 500.00, 500.00),
(2, 2, 6, 1, 1999.00, 1999.00);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL CHECK (`amount` >= 0),
  `payment_method` enum('CASH','UPI','CARD','NETBANKING') NOT NULL DEFAULT 'UPI',
  `payment_status` enum('PENDING','PAID','FAILED') NOT NULL DEFAULT 'PENDING',
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `order_id`, `amount`, `payment_method`, `payment_status`, `payment_date`) VALUES
(1, 1, 500.00, 'UPI', 'PAID', '2026-10-11 20:17:34'),
(2, 2, 1999.00, 'UPI', 'PAID', '2026-11-04 20:00:59');
>>>>>>> 69f781ceed8cb9ec6d11f6494631e01816036ed0

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
<<<<<<< HEAD
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(100) DEFAULT 'General',
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) DEFAULT 0,
=======
  `product_id` int(11) NOT NULL,
  `product_name` varchar(120) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL CHECK (`price` >= 0),
  `stock` int(11) NOT NULL DEFAULT 0 CHECK (`stock` >= 0),
>>>>>>> 69f781ceed8cb9ec6d11f6494631e01816036ed0
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

<<<<<<< HEAD
INSERT INTO `products` (`id`, `name`, `description`, `category`, `price`, `stock`, `created_at`) VALUES
(1, 'psnva', 'ufhqoiuehOVLJACS Z', 'General', 20.00, 70, '2026-11-05 17:26:53'),
(2, 'laptop', 'lapyop', 'Electronics', 50000.00, 48, '2026-11-05 17:48:19'),
(3, 'keyboard', 'keboard', 'Gadgets', 500.00, 49, '2026-11-05 18:27:20');
=======
INSERT INTO `products` (`product_id`, `product_name`, `description`, `price`, `stock`, `created_at`) VALUES
(1, 'Wireless Mouse', 'USB wireless mouse', 599.00, 25, '2026-10-11 20:05:49'),
(2, 'Keyboard', 'Standard USB keyboard', 899.00, 20, '2026-10-11 20:05:49'),
(3, 'USB-C Hub', 'Multiport USB-C hub', 1499.00, 12, '2026-10-11 20:05:49'),
(4, 'Headphones', 'Wired stereo headphones', 799.00, 18, '2026-10-11 20:05:49'),
(5, 'bangles', 'na', 500.00, 2, '2026-10-11 20:17:00'),
(6, 'tv', 'fghlklkjkh', 1999.00, 1, '2026-11-04 20:00:37');
>>>>>>> 69f781ceed8cb9ec6d11f6494631e01816036ed0

-- --------------------------------------------------------

--
<<<<<<< HEAD
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'System Admin', 'admin@gmail.com', '$2y$10$kJDsU5Oob6c/ZX2EQprHGedeLj8NPolNduRtMRHSl1FNie67kwKh.', 'admin', '2026-11-05 17:13:36'),
(3, 'Hariom', 'hari@gmail.com', '$2y$10$QTJpm9eerUNKlagZLl3imu7TfkoEvm682txcft6Lh4DDrhDLMiMT2', 'user', '2026-11-05 18:02:54');
=======
-- Table structure for table `sales_orders`
--

CREATE TABLE `sales_orders` (
  `order_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('PLACED','PROCESSING','SHIPPED','CANCELLED') NOT NULL DEFAULT 'PLACED'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales_orders`
--

INSERT INTO `sales_orders` (`order_id`, `customer_id`, `order_date`, `total_amount`, `status`) VALUES
(1, 1, '2026-10-11 20:17:34', 500.00, 'PLACED'),
(2, 4, '2026-11-04 20:00:59', 1999.00, 'PLACED');
>>>>>>> 69f781ceed8cb9ec6d11f6494631e01816036ed0

--
-- Indexes for dumped tables
--

--
<<<<<<< HEAD
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);
=======
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`log_id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`),
  ADD UNIQUE KEY `email` (`email`);
>>>>>>> 69f781ceed8cb9ec6d11f6494631e01816036ed0

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
<<<<<<< HEAD
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);
=======
  ADD PRIMARY KEY (`order_item_id`),
  ADD UNIQUE KEY `uq_order_product` (`order_id`,`product_id`),
  ADD KEY `fk_item_product` (`product_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `fk_payment_order` (`order_id`);
>>>>>>> 69f781ceed8cb9ec6d11f6494631e01816036ed0

--
-- Indexes for table `products`
--
ALTER TABLE `products`
<<<<<<< HEAD
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);
=======
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `sales_orders`
--
ALTER TABLE `sales_orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `fk_order_customer` (`customer_id`);
>>>>>>> 69f781ceed8cb9ec6d11f6494631e01816036ed0

--
-- AUTO_INCREMENT for dumped tables
--

--
<<<<<<< HEAD
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
=======
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
>>>>>>> 69f781ceed8cb9ec6d11f6494631e01816036ed0

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
<<<<<<< HEAD
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
=======
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
>>>>>>> 69f781ceed8cb9ec6d11f6494631e01816036ed0

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
<<<<<<< HEAD
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
=======
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `sales_orders`
--
ALTER TABLE `sales_orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
>>>>>>> 69f781ceed8cb9ec6d11f6494631e01816036ed0

--
-- Constraints for dumped tables
--

--
<<<<<<< HEAD
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
=======
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_item_order` FOREIGN KEY (`order_id`) REFERENCES `sales_orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_item_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payment_order` FOREIGN KEY (`order_id`) REFERENCES `sales_orders` (`order_id`);

--
-- Constraints for table `sales_orders`
--
ALTER TABLE `sales_orders`
  ADD CONSTRAINT `fk_order_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`);
>>>>>>> 69f781ceed8cb9ec6d11f6494631e01816036ed0
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
