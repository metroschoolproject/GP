-- Migration: customer moderation audit trail
-- Records every admin status change / contact edit / soft-delete on a customer account.
-- Powers the moderation-history panel on admin/customer/{id}.

CREATE TABLE IF NOT EXISTS `customer_status_logs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `old_status` varchar(20) DEFAULT NULL,
  `new_status` varchar(20) NOT NULL,
  `action` varchar(40) NOT NULL COMMENT 'suspend | ban | unban | soft_delete | edit_contact',
  `reason` text DEFAULT NULL,
  `changed_by` bigint(20) DEFAULT NULL COMMENT 'admin user_id',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_csl_user` (`user_id`),
  KEY `idx_csl_changed_by` (`changed_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
