-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主机： 127.0.0.1
-- 生成日期： 2026-04-14 19:50:03
-- 服务器版本： 10.4.32-MariaDB
-- PHP 版本： 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 数据库： `faifa_db`
--

-- --------------------------------------------------------

--
-- 表的结构 `coupons`
--

CREATE TABLE `coupons` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `discount_type` enum('percentage','fixed') NOT NULL DEFAULT 'percentage',
  `discount_value` decimal(10,2) NOT NULL,
  `usage_limit` int(11) DEFAULT NULL COMMENT 'NULL means unlimited',
  `used_count` int(11) NOT NULL DEFAULT 0,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `expiry_date` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_id` int(11) DEFAULT 0,
  `is_used` tinyint(1) DEFAULT 0,
  `discount_percent` int(11) DEFAULT 10
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `coupons`
--

INSERT INTO `coupons` (`id`, `code`, `discount_type`, `discount_value`, `usage_limit`, `used_count`, `start_date`, `end_date`, `expiry_date`, `created_at`, `user_id`, `is_used`, `discount_percent`) VALUES
(5, 'SUMMER1', 'fixed', 1.00, 1, 0, '2026-02-26', '2026-02-26', NULL, '2026-02-27 07:12:14', 0, 0, 10),
(6, 'COUPON123', 'percentage', 10.00, 1, 1, '2026-03-01', '2026-03-05', NULL, '2026-03-01 08:22:27', 0, 0, 10),
(9, 'FAIFA10-AC1935', 'percentage', 10.00, NULL, 0, '2026-03-13', '2027-03-13', NULL, '2026-03-13 04:03:08', 4, 0, 10);

-- --------------------------------------------------------

--
-- 表的结构 `coupon_usage`
--

CREATE TABLE `coupon_usage` (
  `id` int(11) NOT NULL,
  `coupon_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `used_count` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `coupon_usage`
--

INSERT INTO `coupon_usage` (`id`, `coupon_id`, `user_id`, `used_count`) VALUES
(1, 6, 3, 1);

-- --------------------------------------------------------

--
-- 表的结构 `customer_inquiries`
--

CREATE TABLE `customer_inquiries` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `customer_inquiries`
--

INSERT INTO `customer_inquiries` (`id`, `name`, `email`, `subject`, `message`, `created_at`) VALUES
(1, 'Sabrina 3 EP Basketball Shoe', 'cheong863@gmail.com', 's', 's', '2026-04-11 08:55:59');

-- --------------------------------------------------------

--
-- 表的结构 `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `is_read`, `created_at`) VALUES
(2, 4, '🎁 Your 10% Reward is here!', 'Congratulations! Your daily check-in reward has been approved. Use code: **FAIFA10-AC1935** at checkout to get 10% off your order!', 1, '2026-03-13 04:03:08');

-- --------------------------------------------------------

--
-- 表的结构 `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `shipping_amount` decimal(10,2) DEFAULT 0.00,
  `status` varchar(20) DEFAULT 'Paid',
  `created_at` datetime DEFAULT current_timestamp(),
  `delivery_request` tinyint(1) DEFAULT 0,
  `estimated_delivery` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_price`, `shipping_amount`, `status`, `created_at`, `delivery_request`, `estimated_delivery`) VALUES
(4, 0, 12.51, 0.00, 'Paid', '2026-02-20 22:27:17', 0, NULL),
(5, 0, 12.51, 0.00, 'Paid', '2026-02-20 22:33:29', 0, NULL),
(6, 0, 12.51, 0.00, 'Paid', '2026-02-20 22:38:07', 0, NULL),
(7, 0, 12.51, 0.00, 'Paid', '2026-02-20 22:39:52', 0, NULL),
(8, 0, 12.51, 0.00, 'Paid', '2026-02-20 22:40:11', 0, NULL),
(9, 1, 12.51, 0.00, 'Paid', '2026-02-20 22:41:19', 0, NULL),
(10, 1, 62.33, 0.00, 'Paid', '2026-02-21 01:01:02', 0, NULL),
(11, 1, 62.33, 0.00, 'Paid', '2026-02-21 01:03:06', 0, NULL),
(12, 1, 31.72, 0.00, 'Paid', '2026-02-21 01:39:45', 0, NULL),
(13, 1, 12.51, 0.00, 'Paid', '2026-02-21 01:40:21', 0, NULL),
(14, 1, 12.51, 0.00, 'Paid', '2026-02-21 01:50:57', 0, NULL),
(15, 1, 30.61, 0.00, 'Paid', '2026-02-21 01:58:35', 0, NULL),
(16, 2, 742.00, 0.00, 'Refunded', '2026-02-25 23:03:05', 0, NULL),
(17, 2, 1484.00, 0.00, 'Shipped', '2026-02-26 04:18:47', 1, NULL),
(18, 2, 742.00, 0.00, 'Paid', '2026-02-26 04:22:19', 0, NULL),
(19, 2, 12.51, 0.00, 'Paid', '2026-02-26 04:22:45', 0, NULL),
(20, 2, 12.51, 0.00, 'Paid', '2026-02-26 04:27:47', 0, NULL),
(21, 2, 0.00, 0.00, 'Paid', '2026-02-26 04:30:39', 0, NULL),
(22, 2, 12.51, 0.00, 'Paid', '2026-02-26 05:19:16', 0, NULL),
(23, 2, 12.51, 0.00, 'Refunded', '2026-02-26 05:35:39', 0, NULL),
(24, 2, 12.51, 0.00, 'Refunded', '2026-02-27 10:20:12', 0, NULL),
(25, 3, 11.88, 0.00, 'Delivered', '2026-02-27 14:50:40', 0, NULL),
(26, 3, 667.80, 0.00, 'Paid', '2026-03-01 16:23:49', 0, NULL),
(27, 2, 742.00, 0.00, 'Paid', '2026-03-02 12:34:08', 0, NULL),
(28, 2, 12.51, 0.00, 'Paid', '2026-03-02 14:20:04', 0, NULL),
(29, 2, 12.51, 0.00, 'Paid', '2026-03-03 12:01:41', 0, NULL),
(30, 2, 12.61, 0.10, 'Delivered', '2026-03-03 12:12:02', 0, NULL),
(31, 2, 12.51, 0.00, 'Paid', '2026-03-05 10:26:09', 0, NULL),
(32, 2, 12.61, 0.10, 'Paid', '2026-03-08 15:09:05', 0, NULL),
(33, 2, 12.61, 0.10, 'Processing', '2026-03-08 15:10:10', 0, NULL),
(34, 2, 12.61, 0.10, 'Shipped', '2026-03-09 15:18:26', 0, '2026-04-07');

-- --------------------------------------------------------

--
-- 表的结构 `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(4, 4, 6, 1, 0.00),
(5, 5, 6, 1, 0.00),
(6, 9, 6, 1, 0.00),
(7, 11, 3, 1, 28.88),
(8, 11, 2, 1, 29.92),
(9, 12, 2, 1, 29.92),
(10, 13, 6, 1, 11.80),
(11, 14, 6, 1, 11.80),
(12, 15, 3, 1, 28.88),
(13, 16, 9, 1, 700.00),
(14, 17, 9, 2, 700.00),
(15, 18, 9, 1, 700.00),
(16, 19, 6, 1, 11.80),
(17, 20, 6, 1, 11.80),
(18, 21, 6, 1, 11.80),
(19, 22, 6, 1, 11.80),
(20, 23, 6, 1, 11.80),
(21, 24, 6, 1, 11.80),
(22, 25, 6, 1, 11.80),
(23, 26, 9, 1, 700.00),
(24, 27, 9, 1, 700.00),
(25, 28, 6, 1, 11.80),
(26, 29, 6, 1, 11.80),
(27, 30, 6, 1, 11.80),
(28, 31, 6, 1, 11.80),
(29, 32, 6, 1, 11.80),
(30, 33, 6, 1, 11.80),
(31, 34, 6, 1, 11.80);

-- --------------------------------------------------------

--
-- 表的结构 `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `product_code` varchar(50) DEFAULT NULL,
  `category` varchar(50) NOT NULL,
  `tags` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `weight` varchar(50) DEFAULT '180g',
  `material` varchar(100) DEFAULT 'Titanium',
  `warranty` varchar(100) DEFAULT '2 Years',
  `price` decimal(10,2) NOT NULL,
  `discount_price` decimal(10,2) DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `status` enum('active','draft') DEFAULT 'active',
  `stock` int(11) DEFAULT 0,
  `rating` decimal(2,1) DEFAULT 4.5
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `products`
--

INSERT INTO `products` (`id`, `product_code`, `category`, `tags`, `name`, `description`, `weight`, `material`, `warranty`, `price`, `discount_price`, `image`, `status`, `stock`, `rating`) VALUES
(1, 'MEN-TOPS-002', 'MEN', '', 'Casual Men Long Sleeve Shirt', 'Crafted from premium combed cotton, this slim-fit long sleeve shirt offers a versatile aesthetic for both business and casual environments.', '280g', '100% Combed Cotton', '2 Years', 25.90, NULL, 'korean.png', 'active', 0, 4.9),
(2, 'MEN-TOPS-001', 'MEN', '', 'Men Short Sleeve  T-Shirt', 'An essential everyday staple featuring a soft-touch finish. This heavyweight cotton tee is designed to retain its shape after multiple washes.', '210g', 'Heavyweight Cotton', '2 Years', 29.92, NULL, 'tshirt.png', 'active', 0, 4.8),
(3, 'WOMEN-TOPS-001', 'WOMEN', '', 'ZANZEN Korean-Style Sleeve Shirt', 'Embracing Korean minimalist aesthetics, this lightweight shirt offers a natural drape. The fluid chiffon blend moves gracefully.', '190g', 'Chiffon Blend', '2 Years', 28.88, NULL, 'zanzen.png', 'active', 0, 4.7),
(4, 'WOMEN-BOTTOMS-001', 'WOMEN', '', 'Korean Style Short Jeans', 'High-waisted denim shorts engineered from premium stretch fabric. Featuring a vintage-inspired wash and a fit designed to enhance the leg line.', '320g', 'Stretch Denim', '2 Years', 51.67, NULL, 'pants.png', 'active', 0, 4.5),
(5, 'MEN-WATCH-001', 'ACCESSORIES', '', 'Tissot T-Race 45mm', 'Inspired by the adrenaline of racing, this 45mm timepiece features a bold sapphire crystal face and precision Swiss movement.', '130g', '316L Stainless Steel / Sapphire Crystal', '2 Years', 2850.00, NULL, 'trace.png', 'active', 0, 5.0),
(6, 'KEYCHAIN-001', 'ACCESSORIES', '', 'Capybara Keychain Soft', 'The ultra-popular Capybara plush keychain, crafted with high-density soft fibers. A perfect minimalist accessory for your bags.', '45g', 'Eco-friendly Soft Plush', '2 Years', 11.80, NULL, 'cute.png', 'active', 193, 4.6),
(9, 'MEN-PERFUME-001', 'Men', '', 'CHANEL BLUE DE Men Perfume Spray 100ml', 'An intense, woody-aromatic fragrance. BLEU DE CHANEL Parfum reveals the essence of determination, opening with powerful freshness and lingering with a precious accord of New Caledonian sandalwood.', '100ml / 3.4 fl oz', 'Signature Glass Bottle with Magnetic Cap', '2 Years', 700.00, NULL, 'bleu.png', 'active', 0, 4.5);

-- --------------------------------------------------------

--
-- 表的结构 `product_reviews`
--

CREATE TABLE `product_reviews` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(1) NOT NULL DEFAULT 5,
  `comment` text NOT NULL,
  `status` varchar(20) DEFAULT 'Approved',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `product_reviews`
--

INSERT INTO `product_reviews` (`id`, `product_id`, `user_id`, `rating`, `comment`, `status`, `created_at`) VALUES
(2, 5, 2, 5, 'cool', 'Approved', '2026-02-26 11:23:13'),
(3, 6, 3, 5, 'good quality', 'Approved', '2026-02-27 14:55:10');

-- --------------------------------------------------------

--
-- 表的结构 `reward_requests`
--

CREATE TABLE `reward_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `reward_requests`
--

INSERT INTO `reward_requests` (`id`, `user_id`, `status`, `created_at`) VALUES
(1, 3, 'approved', '2026-03-01 20:34:20'),
(2, 2, 'approved', '2026-03-01 20:44:32'),
(3, 4, 'approved', '2026-03-13 04:02:50');

-- --------------------------------------------------------

--
-- 表的结构 `shipping_rules`
--

CREATE TABLE `shipping_rules` (
  `id` int(11) NOT NULL,
  `zone` varchar(50) NOT NULL DEFAULT 'Domestic',
  `rule_name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `condition_label` varchar(100) DEFAULT NULL,
  `rate` decimal(10,2) NOT NULL DEFAULT 0.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `shipping_rules`
--

INSERT INTO `shipping_rules` (`id`, `zone`, `rule_name`, `description`, `condition_label`, `rate`, `is_active`) VALUES
(6, 'Domestic', 'Next Day Air', 'Delivered within 24 Hours', 'All orders', 0.10, 1);

-- --------------------------------------------------------

--
-- 表的结构 `subscribers`
--

CREATE TABLE `subscribers` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `status` varchar(20) DEFAULT 'Active',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 表的结构 `tax_rules`
--

CREATE TABLE `tax_rules` (
  `id` int(11) NOT NULL,
  `jurisdiction` varchar(100) NOT NULL,
  `region_detail` varchar(100) DEFAULT NULL,
  `tax_type` varchar(50) NOT NULL DEFAULT 'Sales Tax',
  `rate` decimal(5,3) NOT NULL DEFAULT 0.000,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `flag_icon` varchar(10) DEFAULT '?️'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `tax_rules`
--

INSERT INTO `tax_rules` (`id`, `jurisdiction`, `region_detail`, `tax_type`, `rate`, `is_active`, `flag_icon`) VALUES
(6, 'Malaysia', 'All Region', 'SST', 6.000, 1, 'MY');

-- --------------------------------------------------------

--
-- 表的结构 `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `phone` varchar(20) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `profile_icon` varchar(255) DEFAULT 'default-avatar.png',
  `faifa_coins` int(11) DEFAULT 0,
  `last_checkin` date DEFAULT NULL,
  `bear_food` int(11) DEFAULT 0,
  `bear_hunger` int(11) DEFAULT 40,
  `last_bear_claim` date DEFAULT NULL,
  `last_treat_time` datetime DEFAULT NULL,
  `bear_stage` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `created_at`, `phone`, `location`, `profile_icon`, `faifa_coins`, `last_checkin`, `bear_food`, `bear_hunger`, `last_bear_claim`, `last_treat_time`, `bear_stage`) VALUES
(2, 'Chun Mun', 'cheong863@gmail.com', '$2y$10$beW/E5K9KpEFTbJZtQ5qHetkFf4zFgagmRyN.ncgeXDGotYObgi12', '2026-02-25 15:02:10', '+60 123456789', 'Malaysia', 'user_2_1772056221.jpg', 100, '2026-04-14', 5, 35, '2026-04-11', '2026-04-13 21:49:57', 1),
(3, 'John Doe', 'john123@gmail.com', '$2y$10$eCQh0sSLJZ1fO.btHn5pnumBg79eUei9W4PZ3lFwe0m3oS6FGdcKK', '2026-02-27 06:43:29', '+60 0146382694', 'Malaysia', 'user_3_1772174656.jpg', 950, NULL, 0, 40, NULL, NULL, 1),
(4, 'johndoe1233', 'johndoe123@gmail.com', '$2y$10$HLAPjpJfYOS6r/OeM4K3GOVS0nPp7l.UxO4N55z5l/5Mjsw10/GTq', '2026-03-13 04:02:15', NULL, NULL, 'default-avatar.png', 0, '2026-03-13', 0, 40, NULL, NULL, 1);

-- --------------------------------------------------------

--
-- 表的结构 `user_addresses`
--

CREATE TABLE `user_addresses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `receiver_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address_line` varchar(255) DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `user_addresses`
--

INSERT INTO `user_addresses` (`id`, `user_id`, `receiver_name`, `phone`, `address_line`, `is_default`) VALUES
(3, 1, 'Chun Mun', '+60 123456790', 'sfs', 1),
(6, 3, 'cheong mun chun', '+60 0146382694', 'WISMA YPC (Plaza Pandan, Jalan Perdana 4/1, Pandan Perdana, 55300 Kuala Lumpur, Wilayah Persekutuan Kuala Lumpur', 1),
(8, 2, 'Cheong Mun Chun', '012-3456789', 'WISMA YPC (Plaza Pandan, Jalan Perdana 4/1, Pandan Perdana, 55300 Kuala Lumpur, Wilayah Persekutuan Kuala Lumpur', 1),
(9, 4, 'Chun Mun', '+60 123456790', 's', 1);

-- --------------------------------------------------------

--
-- 表的结构 `user_payments`
--

CREATE TABLE `user_payments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `card_type` varchar(20) DEFAULT NULL,
  `card_last_four` varchar(4) DEFAULT NULL,
  `expiry_date` varchar(10) DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `user_payments`
--

INSERT INTO `user_payments` (`id`, `user_id`, `card_type`, `card_last_four`, `expiry_date`, `is_default`) VALUES
(4, 1, 'Visa', 'fd', 'df', 1),
(5, 1, 'Visa', 's', 's', 1),
(6, 2, 'Visa', '123', '123', 1),
(7, 3, 'Visa', '1234', '12/28', 1),
(8, 2, 'Visa', '31', '1/31', 0);

--
-- 转储表的索引
--

--
-- 表的索引 `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- 表的索引 `coupon_usage`
--
ALTER TABLE `coupon_usage`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_coupon_user` (`coupon_id`,`user_id`);

--
-- 表的索引 `customer_inquiries`
--
ALTER TABLE `customer_inquiries`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- 表的索引 `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_code` (`product_code`);

--
-- 表的索引 `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `reward_requests`
--
ALTER TABLE `reward_requests`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `shipping_rules`
--
ALTER TABLE `shipping_rules`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `subscribers`
--
ALTER TABLE `subscribers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- 表的索引 `tax_rules`
--
ALTER TABLE `tax_rules`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- 表的索引 `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `user_payments`
--
ALTER TABLE `user_payments`
  ADD PRIMARY KEY (`id`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- 使用表AUTO_INCREMENT `coupon_usage`
--
ALTER TABLE `coupon_usage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `customer_inquiries`
--
ALTER TABLE `customer_inquiries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- 使用表AUTO_INCREMENT `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- 使用表AUTO_INCREMENT `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- 使用表AUTO_INCREMENT `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- 使用表AUTO_INCREMENT `product_reviews`
--
ALTER TABLE `product_reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- 使用表AUTO_INCREMENT `reward_requests`
--
ALTER TABLE `reward_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- 使用表AUTO_INCREMENT `shipping_rules`
--
ALTER TABLE `shipping_rules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- 使用表AUTO_INCREMENT `subscribers`
--
ALTER TABLE `subscribers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `tax_rules`
--
ALTER TABLE `tax_rules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- 使用表AUTO_INCREMENT `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- 使用表AUTO_INCREMENT `user_addresses`
--
ALTER TABLE `user_addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- 使用表AUTO_INCREMENT `user_payments`
--
ALTER TABLE `user_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- 限制导出的表
--

--
-- 限制表 `coupon_usage`
--
ALTER TABLE `coupon_usage`
  ADD CONSTRAINT `coupon_usage_ibfk_1` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE CASCADE;

--
-- 限制表 `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
