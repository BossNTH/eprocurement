-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 14, 2025 at 06:54 PM
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
-- Database: `eprocurement`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) NOT NULL,
  `entity` varchar(64) NOT NULL,
  `entity_no` varchar(64) NOT NULL,
  `action` varchar(32) NOT NULL,
  `changed_by` int(11) NOT NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `department_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `head_employee_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`department_id`, `name`, `head_employee_id`) VALUES
(1, 'IT', 3),
(2, 'Procurement', 5);

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `employee_id` int(11) NOT NULL,
  `full_name` varchar(200) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(190) NOT NULL,
  `status` enum('active','inactive','terminated') NOT NULL DEFAULT 'active',
  `department_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`employee_id`, `full_name`, `phone`, `email`, `status`, `department_id`, `created_at`, `updated_at`) VALUES
(1, 'Administrator', '0800000000', 'admin@example.com', 'active', 1, '2025-10-14 09:43:16', NULL),
(2, 'Employee One', '0811111111', 'employee@example.com', 'active', 1, '2025-10-14 09:43:16', NULL),
(3, 'Manager One', '0822222222', 'manager@example.com', 'active', 1, '2025-10-14 09:43:16', NULL),
(4, 'Procurement One', '0833333333', 'buyer@example.com', 'active', 2, '2025-10-14 09:43:16', NULL),
(5, 'Procurement Manager', '0844444444', 'pmanager@example.com', 'active', 2, '2025-10-14 09:43:16', '2025-10-14 15:23:55');

-- --------------------------------------------------------

--
-- Table structure for table `payment_types`
--

CREATE TABLE `payment_types` (
  `id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_types`
--

INSERT INTO `payment_types` (`id`, `name`) VALUES
(3, 'เครดิตเทอม 30 วัน'),
(2, 'เช็ค'),
(1, 'โอน');

-- --------------------------------------------------------

--
-- Table structure for table `po_items`
--

CREATE TABLE `po_items` (
  `po_no` varchar(40) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` decimal(18,4) NOT NULL,
  `unit_price` decimal(12,2) NOT NULL,
  `uom` varchar(32) NOT NULL,
  `discount_pct` decimal(5,2) NOT NULL DEFAULT 0.00,
  `vat_rate` decimal(5,2) NOT NULL DEFAULT 7.00,
  `delivery_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `product_name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `qty_onhand` decimal(18,4) NOT NULL DEFAULT 0.0000,
  `reorder_point` decimal(18,4) NOT NULL DEFAULT 0.0000,
  `unit_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `uom` varchar(32) NOT NULL,
  `category_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `product_name`, `description`, `qty_onhand`, `reorder_point`, `unit_price`, `uom`, `category_id`, `created_at`, `updated_at`) VALUES
(1, 'Printer Paper A4', '80gsm', 100.0000, 10.0000, 120.00, 'ream', 1, '2025-10-14 09:43:16', NULL),
(2, 'Laptop 14\"', 'i5/16GB/512GB', 10.0000, 2.0000, 28000.00, 'pcs', 2, '2025-10-14 09:43:16', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `product_categories`
--

CREATE TABLE `product_categories` (
  `category_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_categories`
--

INSERT INTO `product_categories` (`category_id`, `name`) VALUES
(2, 'IT Equipment'),
(1, 'Office');

-- --------------------------------------------------------

--
-- Table structure for table `pr_items`
--

CREATE TABLE `pr_items` (
  `pr_no` varchar(40) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` decimal(18,4) NOT NULL,
  `unit_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `uom` varchar(32) NOT NULL,
  `need_by_date` date NOT NULL,
  `spec_text` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_orders`
--

CREATE TABLE `purchase_orders` (
  `po_no` varchar(40) NOT NULL,
  `po_date` date NOT NULL,
  `needed_date` date DEFAULT NULL,
  `buyer_employee_id` int(11) NOT NULL,
  `quote_no` varchar(40) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `issued_at` datetime DEFAULT NULL,
  `ship_to_address` varchar(255) DEFAULT NULL,
  `is_tax_inclusive` tinyint(1) NOT NULL DEFAULT 0,
  `vat_rate` decimal(5,2) NOT NULL DEFAULT 7.00,
  `currency` varchar(10) NOT NULL DEFAULT 'THB',
  `subtotal_before_vat` decimal(14,2) NOT NULL DEFAULT 0.00,
  `vat_total` decimal(14,2) NOT NULL DEFAULT 0.00,
  `grand_total` decimal(14,2) NOT NULL DEFAULT 0.00,
  `status` enum('DRAFT','PENDING_APPROVAL','APPROVED','REJECTED','ISSUED','CANCELLED') NOT NULL DEFAULT 'DRAFT',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_quotes`
--

CREATE TABLE `purchase_quotes` (
  `quote_no` varchar(40) NOT NULL,
  `pr_no` varchar(40) NOT NULL,
  `quote_date` date NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `subtotal_before_vat` decimal(14,2) NOT NULL DEFAULT 0.00,
  `vat_total` decimal(14,2) NOT NULL DEFAULT 0.00,
  `grand_total` decimal(14,2) NOT NULL DEFAULT 0.00,
  `valid_until` date DEFAULT NULL,
  `delivery_terms` varchar(200) DEFAULT NULL,
  `payment_terms` varchar(200) DEFAULT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'THB',
  `status` enum('INVITED','SUPPLIER_SUBMITTED','EVALUATING','SELECTED','NOT_SELECTED','CLOSED') NOT NULL DEFAULT 'SUPPLIER_SUBMITTED',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_requisitions`
--

CREATE TABLE `purchase_requisitions` (
  `pr_no` varchar(40) NOT NULL,
  `request_date` date NOT NULL,
  `need_by_date` date NOT NULL,
  `requested_by` int(11) NOT NULL,
  `submitted_at` datetime DEFAULT NULL,
  `manager_approved_by` int(11) DEFAULT NULL,
  `manager_approved_at` datetime DEFAULT NULL,
  `rejected_reason` varchar(500) DEFAULT NULL,
  `budget_code` varchar(50) DEFAULT NULL,
  `attachment_path` varchar(255) DEFAULT NULL,
  `status` enum('DRAFT','SUBMITTED','MANAGER_APPROVED','REJECTED','CANCELLED') NOT NULL DEFAULT 'DRAFT',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quote_items`
--

CREATE TABLE `quote_items` (
  `quote_no` varchar(40) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` decimal(18,4) NOT NULL,
  `unit_price` decimal(12,2) NOT NULL,
  `uom` varchar(32) NOT NULL,
  `lead_time_days` int(11) DEFAULT NULL,
  `discount_pct` decimal(5,2) NOT NULL DEFAULT 0.00,
  `vat_rate` decimal(5,2) NOT NULL DEFAULT 7.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `supplier_id` int(11) NOT NULL,
  `supplier_code` varchar(50) DEFAULT NULL,
  `supplier_name` varchar(200) NOT NULL,
  `contact_name` varchar(200) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(190) DEFAULT NULL,
  `tax_id` varchar(32) DEFAULT NULL,
  `status` enum('active','inactive','blacklisted') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`supplier_id`, `supplier_code`, `supplier_name`, `contact_name`, `address`, `phone`, `email`, `tax_id`, `status`, `created_at`, `updated_at`) VALUES
(1, 'SUP-001', 'Thai Supplies', 'Khun A', NULL, '029999999', 'supplier@example.com', '0105555555555', 'active', '2025-10-14 09:43:16', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','employee','manager','procurement','procurement_manager','supplier') NOT NULL,
  `status` enum('active','inactive','locked') NOT NULL DEFAULT 'active',
  `employee_id` int(11) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `email`, `password_hash`, `role`, `status`, `employee_id`, `supplier_id`, `created_at`, `updated_at`) VALUES
(1, 'admin@example.com', '$2y$10$TCqgzYUnCqFW25IAlstTPe10tdcqFsRlfIzXL50IAkfQfF652LdWe', 'admin', 'active', 1, NULL, '2025-10-14 09:43:16', NULL),
(2, 'employee@example.com', '$2y$10$KMJyRwL5gYwdwFyguaHWW.3BY0HTN1zTzNftEbtp10NEIQWHW7WpG', 'employee', 'active', 2, NULL, '2025-10-14 09:43:16', NULL),
(3, 'manager@example.com', '$2y$10$oFPCSHAuvzOsbfCan//3TONNBncCvNhbEQca7zFDn2AVyc6JyTgaK', 'manager', 'active', 3, NULL, '2025-10-14 09:43:16', NULL),
(4, 'buyer@example.com', '$2y$10$ql3d6Lxzufn5kl1h/IAVduxWMcH2gmiMMGY.RNiFXLeOM52Cx/ACu', 'procurement', 'active', 4, NULL, '2025-10-14 09:43:16', NULL),
(5, 'pmanager@example.com', '$2y$10$njOwAf2D4B2vJ.NXwOoReOpYMQzZ.qaJ98mrHP8LLVR1zmIMsnPH2', 'procurement_manager', 'active', 5, NULL, '2025-10-14 09:43:16', '2025-10-14 15:23:55'),
(6, 'supplier@example.com', '$2y$10$IAg/flcBd0jlhu1EEWIr/.OToiPyD7g.quhd8BGqQudfjhk.ntXzi', 'supplier', 'active', NULL, 1, '2025-10-14 09:43:16', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_audit_entity` (`entity`,`entity_no`),
  ADD KEY `idx_audit_changed_by` (`changed_by`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`department_id`),
  ADD KEY `fk_departments_head_employee` (`head_employee_id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`employee_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_employees_department` (`department_id`);

--
-- Indexes for table `payment_types`
--
ALTER TABLE `payment_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `po_items`
--
ALTER TABLE `po_items`
  ADD PRIMARY KEY (`po_no`,`product_id`),
  ADD KEY `fk_po_items_product` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `idx_products_category` (`category_id`);

--
-- Indexes for table `product_categories`
--
ALTER TABLE `product_categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `pr_items`
--
ALTER TABLE `pr_items`
  ADD PRIMARY KEY (`pr_no`,`product_id`),
  ADD KEY `fk_pr_items_product` (`product_id`);

--
-- Indexes for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD PRIMARY KEY (`po_no`),
  ADD KEY `fk_po_quote` (`quote_no`),
  ADD KEY `fk_po_supplier` (`supplier_id`),
  ADD KEY `fk_po_buyer` (`buyer_employee_id`),
  ADD KEY `fk_po_approved_by` (`approved_by`);

--
-- Indexes for table `purchase_quotes`
--
ALTER TABLE `purchase_quotes`
  ADD PRIMARY KEY (`quote_no`),
  ADD KEY `fk_quotes_pr` (`pr_no`),
  ADD KEY `fk_quotes_supplier` (`supplier_id`);

--
-- Indexes for table `purchase_requisitions`
--
ALTER TABLE `purchase_requisitions`
  ADD PRIMARY KEY (`pr_no`),
  ADD KEY `fk_pr_requested_by` (`requested_by`),
  ADD KEY `fk_pr_manager_approved_by` (`manager_approved_by`);

--
-- Indexes for table `quote_items`
--
ALTER TABLE `quote_items`
  ADD PRIMARY KEY (`quote_no`,`product_id`),
  ADD KEY `fk_quote_items_product` (`product_id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`supplier_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_users_employee` (`employee_id`),
  ADD KEY `fk_users_supplier` (`supplier_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `department_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `employee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `payment_types`
--
ALTER TABLE `payment_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `product_categories`
--
ALTER TABLE `product_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `supplier_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `departments`
--
ALTER TABLE `departments`
  ADD CONSTRAINT `fk_departments_head_employee` FOREIGN KEY (`head_employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `fk_employees_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`) ON UPDATE CASCADE;

--
-- Constraints for table `po_items`
--
ALTER TABLE `po_items`
  ADD CONSTRAINT `fk_po_items_po` FOREIGN KEY (`po_no`) REFERENCES `purchase_orders` (`po_no`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_po_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON UPDATE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `product_categories` (`category_id`) ON UPDATE CASCADE;

--
-- Constraints for table `pr_items`
--
ALTER TABLE `pr_items`
  ADD CONSTRAINT `fk_pr_items_pr` FOREIGN KEY (`pr_no`) REFERENCES `purchase_requisitions` (`pr_no`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pr_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON UPDATE CASCADE;

--
-- Constraints for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD CONSTRAINT `fk_po_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `employees` (`employee_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_po_buyer` FOREIGN KEY (`buyer_employee_id`) REFERENCES `employees` (`employee_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_po_quote` FOREIGN KEY (`quote_no`) REFERENCES `purchase_quotes` (`quote_no`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_po_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`) ON UPDATE CASCADE;

--
-- Constraints for table `purchase_quotes`
--
ALTER TABLE `purchase_quotes`
  ADD CONSTRAINT `fk_quotes_pr` FOREIGN KEY (`pr_no`) REFERENCES `purchase_requisitions` (`pr_no`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_quotes_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`) ON UPDATE CASCADE;

--
-- Constraints for table `purchase_requisitions`
--
ALTER TABLE `purchase_requisitions`
  ADD CONSTRAINT `fk_pr_manager_approved_by` FOREIGN KEY (`manager_approved_by`) REFERENCES `employees` (`employee_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pr_requested_by` FOREIGN KEY (`requested_by`) REFERENCES `employees` (`employee_id`) ON UPDATE CASCADE;

--
-- Constraints for table `quote_items`
--
ALTER TABLE `quote_items`
  ADD CONSTRAINT `fk_quote_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_quote_items_quote` FOREIGN KEY (`quote_no`) REFERENCES `purchase_quotes` (`quote_no`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_users_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
