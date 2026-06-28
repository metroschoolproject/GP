-- Food items (cakes) for Food category services
-- Mirrors decoration_styles structure with an added description column

CREATE TABLE IF NOT EXISTS `food_items` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `service_id` bigint(20) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` varchar(500) DEFAULT NULL,
  `price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `package_price` decimal(12,2) DEFAULT NULL,
  `customize_price` decimal(12,2) DEFAULT NULL,
  `photo_url` varchar(500) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_food_items_service` (`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
