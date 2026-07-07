-- Allow admin to invite multiple suppliers for one package-service replacement.
-- Suppliers accept/decline the invitation first; the customer then chooses
-- from accepted suppliers before the existing replacement swap is finalized.

CREATE TABLE IF NOT EXISTS `booking_supplier_replacement_invitations` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `replacement_id` bigint(20) NOT NULL,
  `booking_id` bigint(20) NOT NULL,
  `supplier_id` bigint(20) NOT NULL,
  `service_id` bigint(20) NOT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `price_delta` decimal(10,2) DEFAULT NULL,
  `status` enum('invited','accepted','declined','chosen','expired','cancelled') NOT NULL DEFAULT 'invited',
  `invited_by_admin_id` bigint(20) DEFAULT NULL,
  `responded_at` timestamp NULL DEFAULT NULL,
  `chosen_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_repl_service` (`replacement_id`,`service_id`),
  KEY `idx_repl_inv_replacement` (`replacement_id`),
  KEY `idx_repl_inv_supplier_status` (`supplier_id`,`status`),
  KEY `idx_repl_inv_booking` (`booking_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
