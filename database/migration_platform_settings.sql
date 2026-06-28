-- Migration: Create platform_settings table
-- Purpose: Store admin-configurable platform settings (fee percentages, etc.)
-- Run: mysql -u root goldenpromise < database/migration_platform_settings.sql

CREATE TABLE IF NOT EXISTS platform_settings (
    setting_key   VARCHAR(100)  NOT NULL PRIMARY KEY,
    setting_value VARCHAR(255)  NOT NULL,
    updated_at    TIMESTAMP     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed default platform fee percent (5%)
INSERT INTO platform_settings (setting_key, setting_value)
VALUES ('platform_fee_percent', '5')
ON DUPLICATE KEY UPDATE setting_value = setting_value;
