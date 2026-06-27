-- Migration: Add missed response tracking to suppliers
-- Run this against the goldenpromise database

ALTER TABLE `suppliers`
  ADD COLUMN `missed_response_count` int(11) NOT NULL DEFAULT 0
    COMMENT 'Number of bookings auto-cancelled due to supplier non-response'
    AFTER `warning_level`,
  ADD COLUMN `last_warning_at` timestamp NULL DEFAULT NULL
    COMMENT 'When the supplier was last issued a system warning for missed responses'
    AFTER `missed_response_count`;
