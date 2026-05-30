-- Backup of database: konnyusu
-- Generated: 2026-05-30T09:39:18+00:00
SET FOREIGN_KEY_CHECKS=0;

-- Table structure for table `cart`

CREATE TABLE `cart` (
  `cart_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('active','checkout') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`cart_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `cart`
INSERT INTO `cart` (`cart_id`, `user_id`, `date`, `status`, `created_at`) VALUES ('1', '2', '2026-05-29 12:29:36', 'active', '2026-05-29 12:29:36');
INSERT INTO `cart` (`cart_id`, `user_id`, `date`, `status`, `created_at`) VALUES ('2', '1', '2026-05-29 12:40:04', 'active', '2026-05-29 12:40:04');
INSERT INTO `cart` (`cart_id`, `user_id`, `date`, `status`, `created_at`) VALUES ('3', '3', '2026-05-30 13:09:06', 'active', '2026-05-30 13:09:06');

-- Table structure for table `cart_item`

CREATE TABLE `cart_item` (
  `cart_item_id` int NOT NULL AUTO_INCREMENT,
  `cart_id` int NOT NULL,
  `menu_id` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `price` int NOT NULL,
  `subtotal` int NOT NULL,
  `ice_level` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sugar_level` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `size` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`cart_item_id`),
  KEY `cart_id` (`cart_id`),
  KEY `menu_id` (`menu_id`),
  CONSTRAINT `cart_item_ibfk_1` FOREIGN KEY (`cart_id`) REFERENCES `cart` (`cart_id`) ON DELETE CASCADE,
  CONSTRAINT `cart_item_ibfk_2` FOREIGN KEY (`menu_id`) REFERENCES `menu` (`menu_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `cart_item`
INSERT INTO `cart_item` (`cart_item_id`, `cart_id`, `menu_id`, `quantity`, `price`, `subtotal`, `ice_level`, `sugar_level`, `size`, `created_at`) VALUES ('8', '3', '2', '2', '19000', '38000', 'Normal Ice', 'Normal', 'Regular', '2026-05-30 14:15:27');
INSERT INTO `cart_item` (`cart_item_id`, `cart_id`, `menu_id`, `quantity`, `price`, `subtotal`, `ice_level`, `sugar_level`, `size`, `created_at`) VALUES ('9', '3', '3', '1', '13000', '13000', 'Normal Ice', 'Normal', 'Regular', '2026-05-30 14:15:30');

-- Table structure for table `menu`

CREATE TABLE `menu` (
  `menu_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `price` int NOT NULL,
  `category` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stock` int DEFAULT '0',
  `is_new` tinyint(1) DEFAULT '0',
  `is_best` tinyint(1) DEFAULT '0',
  `sold` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`menu_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `menu`
INSERT INTO `menu` (`menu_id`, `name`, `description`, `price`, `category`, `image`, `stock`, `is_new`, `is_best`, `sold`, `created_at`, `updated_at`) VALUES ('1', 'Ice Aren Latte', 'Es kopi susu dengan gula aren dan kopi espresso premium', '11000', 'coffee', 'prod_6a0efb7f134ce.jpeg', '50', '0', '1', '9', '2026-05-21 19:33:03', '2026-05-30 14:14:54');
INSERT INTO `menu` (`menu_id`, `name`, `description`, `price`, `category`, `image`, `stock`, `is_new`, `is_best`, `sold`, `created_at`, `updated_at`) VALUES ('2', 'Ice Shaken Caramel Latte', 'Es Kopi Susu dengan Gula Aren, Krimer dan Kopi Espresso Premium dan Topping Whippe Cream dan Saus Caramel Premium', '19000', 'coffee', 'prod_6a0efbc5c3c3c.jpeg', '50', '0', '1', '4', '2026-05-21 19:34:13', '2026-05-30 15:15:28');
INSERT INTO `menu` (`menu_id`, `name`, `description`, `price`, `category`, `image`, `stock`, `is_new`, `is_best`, `sold`, `created_at`, `updated_at`) VALUES ('3', 'Ice Cappucinno', 'Es Kopi Susu dengan Kopi Espresso Premium', '13000', 'coffee', 'prod_6a0efc0ebdfd9.jpeg', '30', '0', '0', '3', '2026-05-21 19:35:26', '2026-05-29 11:12:27');
INSERT INTO `menu` (`menu_id`, `name`, `description`, `price`, `category`, `image`, `stock`, `is_new`, `is_best`, `sold`, `created_at`, `updated_at`) VALUES ('4', 'Ice Butter Aren Latte', 'Es kopi susu dengan gula aren dan topping biscuit crumble dan kopi espresso premium', '19000', 'coffee', 'prod_6a0efc5c2df8f.jpeg', '20', '1', '0', '0', '2026-05-21 19:36:44', '2026-05-21 19:36:44');
INSERT INTO `menu` (`menu_id`, `name`, `description`, `price`, `category`, `image`, `stock`, `is_new`, `is_best`, `sold`, `created_at`, `updated_at`) VALUES ('5', 'Nyusu Oreo', 'Susu dengan campuran dna topping oreo memiliki rasa yang pas', '12000', 'non-coffee', 'prod_6a0efce2694de.jpeg', '100', '0', '1', '0', '2026-05-21 19:38:58', '2026-05-21 19:38:58');
INSERT INTO `menu` (`menu_id`, `name`, `description`, `price`, `category`, `image`, `stock`, `is_new`, `is_best`, `sold`, `created_at`, `updated_at`) VALUES ('6', 'Nyusu Regal', 'Susu sapi segar dengan campuran Mari Regal yang khas', '12000', 'non-coffee', 'prod_6a0efd355fc96.jpeg', '18', '0', '0', '1', '2026-05-21 19:40:21', '2026-05-30 14:14:54');
INSERT INTO `menu` (`menu_id`, `name`, `description`, `price`, `category`, `image`, `stock`, `is_new`, `is_best`, `sold`, `created_at`, `updated_at`) VALUES ('7', 'Nyusu Pisang', 'Susu sapi segar dengan campuran buah pisang yang segar', '12000', 'non-coffee', 'prod_6a0efd75392aa.jpeg', '25', '0', '0', '5', '2026-05-21 19:41:25', '2026-05-30 14:14:54');
INSERT INTO `menu` (`menu_id`, `name`, `description`, `price`, `category`, `image`, `stock`, `is_new`, `is_best`, `sold`, `created_at`, `updated_at`) VALUES ('8', 'Nyusu Kurma', 'Susu sapi dengan campuran buah kurma yang manis', '12000', 'non-coffee', 'prod_6a0efdbb720bb.jpeg', '30', '0', '0', '2', '2026-05-21 19:42:35', '2026-05-29 12:13:28');
INSERT INTO `menu` (`menu_id`, `name`, `description`, `price`, `category`, `image`, `stock`, `is_new`, `is_best`, `sold`, `created_at`, `updated_at`) VALUES ('9', 'Nyusu Matcha', 'Susu sapi dengan matcha pilihan', '12000', 'non-coffee', 'prod_6a0efe0c72bd1.jpeg', '28', '0', '1', '0', '2026-05-21 19:43:56', '2026-05-21 19:43:56');
INSERT INTO `menu` (`menu_id`, `name`, `description`, `price`, `category`, `image`, `stock`, `is_new`, `is_best`, `sold`, `created_at`, `updated_at`) VALUES ('10', 'Siomay Goreng', 'Siomay goreng yang enak untuk camilan', '15000', 'makanan', 'prod_6a0f095fc6f31.jpg', '18', '1', '0', '3', '2026-05-21 20:32:15', '2026-05-29 12:13:28');

-- Table structure for table `order_item`

CREATE TABLE `order_item` (
  `order_item_id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `menu_id` int NOT NULL,
  `quantity` int NOT NULL,
  `price` int NOT NULL,
  `ice_level` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sugar_level` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `size` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`order_item_id`),
  KEY `order_id` (`order_id`),
  KEY `menu_id` (`menu_id`),
  CONSTRAINT `order_item_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  CONSTRAINT `order_item_ibfk_2` FOREIGN KEY (`menu_id`) REFERENCES `menu` (`menu_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `order_item`
INSERT INTO `order_item` (`order_item_id`, `order_id`, `menu_id`, `quantity`, `price`, `ice_level`, `sugar_level`, `size`, `created_at`) VALUES ('1', '1', '1', '1', '11000', 'Less Ice', 'Less Sugar', 'Regular', '2026-05-30 12:23:34');
INSERT INTO `order_item` (`order_item_id`, `order_id`, `menu_id`, `quantity`, `price`, `ice_level`, `sugar_level`, `size`, `created_at`) VALUES ('2', '2', '1', '1', '11000', 'Less Ice', 'Less Sugar', 'Regular', '2026-05-30 12:28:43');
INSERT INTO `order_item` (`order_item_id`, `order_id`, `menu_id`, `quantity`, `price`, `ice_level`, `sugar_level`, `size`, `created_at`) VALUES ('3', '3', '1', '1', '11000', 'Normal Ice', 'Normal', 'Regular', '2026-05-30 13:23:56');
INSERT INTO `order_item` (`order_item_id`, `order_id`, `menu_id`, `quantity`, `price`, `ice_level`, `sugar_level`, `size`, `created_at`) VALUES ('4', '4', '1', '1', '11000', 'Normal Ice', 'Normal', 'Regular', '2026-05-30 13:24:18');
INSERT INTO `order_item` (`order_item_id`, `order_id`, `menu_id`, `quantity`, `price`, `ice_level`, `sugar_level`, `size`, `created_at`) VALUES ('5', '4', '7', '2', '12000', 'Less Ice', 'Normal', 'Regular', '2026-05-30 13:24:18');
INSERT INTO `order_item` (`order_item_id`, `order_id`, `menu_id`, `quantity`, `price`, `ice_level`, `sugar_level`, `size`, `created_at`) VALUES ('6', '5', '6', '1', '12000', 'Normal Ice', 'Less Sugar', 'Regular', '2026-05-30 14:14:54');
INSERT INTO `order_item` (`order_item_id`, `order_id`, `menu_id`, `quantity`, `price`, `ice_level`, `sugar_level`, `size`, `created_at`) VALUES ('7', '5', '1', '2', '11000', 'Normal Ice', 'Normal', 'Regular', '2026-05-30 14:14:54');
INSERT INTO `order_item` (`order_item_id`, `order_id`, `menu_id`, `quantity`, `price`, `ice_level`, `sugar_level`, `size`, `created_at`) VALUES ('8', '5', '7', '2', '12000', 'Less Ice', 'Normal', 'Regular', '2026-05-30 14:14:54');
INSERT INTO `order_item` (`order_item_id`, `order_id`, `menu_id`, `quantity`, `price`, `ice_level`, `sugar_level`, `size`, `created_at`) VALUES ('9', '6', '2', '1', '19000', 'Normal Ice', 'Normal', 'Regular', '2026-05-30 15:15:28');

-- Table structure for table `orders`

CREATE TABLE `orders` (
  `order_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `repeat_order_id` int DEFAULT NULL,
  `order_status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `cancellation_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `viewed_by_admin` tinyint(1) DEFAULT '0',
  `viewed_by_user` tinyint(1) DEFAULT '0',
  `order_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `order_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'dine_in',
  `total` int NOT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `recipient_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `recipient_phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `recipient_address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `recipient_city` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `recipient_postal` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `delivery_fee` int DEFAULT '0',
  `tax` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`order_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `orders`
INSERT INTO `orders` (`order_id`, `user_id`, `repeat_order_id`, `order_status`, `cancellation_note`, `viewed_by_admin`, `viewed_by_user`, `order_date`, `order_type`, `total`, `notes`, `recipient_name`, `recipient_phone`, `recipient_address`, `recipient_city`, `recipient_postal`, `delivery_fee`, `tax`, `created_at`) VALUES ('1', '2', NULL, 'pending', NULL, '0', '1', '2026-05-30 12:23:34', 'standard', '16110', '', 'Zuhrufatin Nisya', '089603593717', 'Jalan Prof. Hamka, Jawa Tengah, Indonesia\r\n\r\n', 'Kota Semarang', '50185', '5000', '110', '2026-05-30 12:23:34');
INSERT INTO `orders` (`order_id`, `user_id`, `repeat_order_id`, `order_status`, `cancellation_note`, `viewed_by_admin`, `viewed_by_user`, `order_date`, `order_type`, `total`, `notes`, `recipient_name`, `recipient_phone`, `recipient_address`, `recipient_city`, `recipient_postal`, `delivery_fee`, `tax`, `created_at`) VALUES ('2', '1', NULL, 'pending', NULL, '0', '0', '2026-05-30 12:28:43', 'priority', '19110', '', 'Zuhrufatin Nisya', '089603593717', 'Jalan Prof. Hamka, Jawa Tengah, Indonesia\r\n\r\n', 'Kota Semarang', '50185', '8000', '110', '2026-05-30 12:28:43');
INSERT INTO `orders` (`order_id`, `user_id`, `repeat_order_id`, `order_status`, `cancellation_note`, `viewed_by_admin`, `viewed_by_user`, `order_date`, `order_type`, `total`, `notes`, `recipient_name`, `recipient_phone`, `recipient_address`, `recipient_city`, `recipient_postal`, `delivery_fee`, `tax`, `created_at`) VALUES ('3', '3', NULL, 'pending', NULL, '0', '0', '2026-05-30 13:23:56', 'priority', '19110', '', 'Zuhrufatin Nisya', '089603593717', 'Jalan Prof. Hamka, Jawa Tengah, Indonesia\r\n\r\n', 'Kota Semarang', '50185', '8000', '110', '2026-05-30 13:23:56');
INSERT INTO `orders` (`order_id`, `user_id`, `repeat_order_id`, `order_status`, `cancellation_note`, `viewed_by_admin`, `viewed_by_user`, `order_date`, `order_type`, `total`, `notes`, `recipient_name`, `recipient_phone`, `recipient_address`, `recipient_city`, `recipient_postal`, `delivery_fee`, `tax`, `created_at`) VALUES ('4', '3', NULL, 'pending', NULL, '0', '0', '2026-05-30 13:24:18', 'standard', '40350', '', 'Zuhrufatin Nisya', '089603593717', 'Jalan Prof. Hamka, Jawa Tengah, Indonesia\r\n\r\n', 'Kota Semarang', '50185', '5000', '350', '2026-05-30 13:24:18');
INSERT INTO `orders` (`order_id`, `user_id`, `repeat_order_id`, `order_status`, `cancellation_note`, `viewed_by_admin`, `viewed_by_user`, `order_date`, `order_type`, `total`, `notes`, `recipient_name`, `recipient_phone`, `recipient_address`, `recipient_city`, `recipient_postal`, `delivery_fee`, `tax`, `created_at`) VALUES ('5', '3', NULL, 'pending', NULL, '0', '0', '2026-05-30 14:14:54', 'priority', '58580', 'kasih sedotan dua ya kaa', 'Zuhrufatin Nisya', '089603593717', 'Jalan Prof. Hamka, Jawa Tengah, Indonesia', 'Kota Semarang', '50185', '0', '580', '2026-05-30 14:14:54');
INSERT INTO `orders` (`order_id`, `user_id`, `repeat_order_id`, `order_status`, `cancellation_note`, `viewed_by_admin`, `viewed_by_user`, `order_date`, `order_type`, `total`, `notes`, `recipient_name`, `recipient_phone`, `recipient_address`, `recipient_city`, `recipient_postal`, `delivery_fee`, `tax`, `created_at`) VALUES ('6', '3', NULL, 'shipped', '', '1', '0', '2026-05-30 15:15:28', 'priority', '27190', '', 'Zuhrufatin Nisya', '089603593717', 'Tembalang', 'Kota Semarang', '50185', '8000', '190', '2026-05-30 15:15:28');

-- Table structure for table `payment`

CREATE TABLE `payment` (
  `payment_id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `payment_status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `payment_proof` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_date` timestamp NULL DEFAULT NULL,
  `payment_method` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`payment_id`),
  KEY `order_id` (`order_id`),
  CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `payment`
INSERT INTO `payment` (`payment_id`, `order_id`, `payment_status`, `payment_proof`, `payment_date`, `payment_method`, `amount`, `created_at`) VALUES ('1', '1', 'pending', NULL, NULL, 'qris', '16110', '2026-05-30 12:23:34');
INSERT INTO `payment` (`payment_id`, `order_id`, `payment_status`, `payment_proof`, `payment_date`, `payment_method`, `amount`, `created_at`) VALUES ('2', '2', 'pending', NULL, NULL, 'gopay', '19110', '2026-05-30 12:28:43');
INSERT INTO `payment` (`payment_id`, `order_id`, `payment_status`, `payment_proof`, `payment_date`, `payment_method`, `amount`, `created_at`) VALUES ('3', '3', 'pending', NULL, NULL, 'gopay', '19110', '2026-05-30 13:23:56');
INSERT INTO `payment` (`payment_id`, `order_id`, `payment_status`, `payment_proof`, `payment_date`, `payment_method`, `amount`, `created_at`) VALUES ('4', '4', 'pending', NULL, NULL, 'gopay', '40350', '2026-05-30 13:24:18');
INSERT INTO `payment` (`payment_id`, `order_id`, `payment_status`, `payment_proof`, `payment_date`, `payment_method`, `amount`, `created_at`) VALUES ('5', '5', 'pending', NULL, NULL, 'gopay', '58580', '2026-05-30 14:14:54');
INSERT INTO `payment` (`payment_id`, `order_id`, `payment_status`, `payment_proof`, `payment_date`, `payment_method`, `amount`, `created_at`) VALUES ('6', '6', 'pending', NULL, NULL, 'qris', '27190', '2026-05-30 15:15:28');

-- Table structure for table `users`

CREATE TABLE `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `role` enum('user','admin') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'user',
  `notifications_enabled` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `birthdate` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bio` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reset_token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reset_token_expires` datetime DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `users`
INSERT INTO `users` (`user_id`, `name`, `username`, `email`, `password`, `phone`, `address`, `role`, `notifications_enabled`, `created_at`, `updated_at`, `birthdate`, `gender`, `bio`, `reset_token`, `reset_token_expires`) VALUES ('1', 'Admin Konnyusu', 'admin', 'admin@konnyusu.com', '$2y$10$fyCrNtYeL06NpTOaT0Z/zO58QWnTu7j1ryxiu/EZU2pYlVnG6jOJK', NULL, NULL, 'admin', '1', '2026-05-18 21:27:54', '2026-05-29 12:32:34', NULL, NULL, NULL, 'bec8900e3358eb81a63d77a44da3258109d96810397b43de2069a84329896794', '2026-05-29 06:32:34');
INSERT INTO `users` (`user_id`, `name`, `username`, `email`, `password`, `phone`, `address`, `role`, `notifications_enabled`, `created_at`, `updated_at`, `birthdate`, `gender`, `bio`, `reset_token`, `reset_token_expires`) VALUES ('2', 'Zuhrufatin Nisya', NULL, 'zuhruffatin@gmail.com', '$2y$10$Hjh43Hz.tkrrF4FApDH9NO44ZaVJb2I60DjWQnyhcNoEJd70H.D32', '089603593717', '', 'user', '1', '2026-05-18 23:07:47', '2026-05-30 16:27:05', '', 'female', '', '74fa46626a047a6efa21bf8152c4ccc5255a9b2688bd4c836248f8968f7952ce', '2026-05-30 10:27:05');
INSERT INTO `users` (`user_id`, `name`, `username`, `email`, `password`, `phone`, `address`, `role`, `notifications_enabled`, `created_at`, `updated_at`, `birthdate`, `gender`, `bio`, `reset_token`, `reset_token_expires`) VALUES ('3', 'Muhammad Husein Assidiq', NULL, 'fasyazhf@gmail.com', '$2y$10$434qjyqCbEAwsD6Nzm7qF.mdMynDjEs9mTecWQkAFirCDmzFaAvzG', '081901229318', 'Ngemplak Kidul, Margoyoso Pati', 'user', '1', '2026-05-30 13:08:39', '2026-05-30 15:35:05', NULL, NULL, NULL, NULL, NULL);
SET FOREIGN_KEY_CHECKS=1;
