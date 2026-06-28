-- Supplier settings columns
ALTER TABLE suppliers
  ADD COLUMN IF NOT EXISTS auto_accept_bookings TINYINT(1) NOT NULL DEFAULT 0 AFTER is_available,
  ADD COLUMN IF NOT EXISTS min_advance_days INT NOT NULL DEFAULT 0 AFTER auto_accept_bookings,
  ADD COLUMN IF NOT EXISTS cancellation_policy TEXT DEFAULT NULL AFTER min_advance_days,
  ADD COLUMN IF NOT EXISTS bank_account VARCHAR(50) DEFAULT NULL AFTER cancellation_policy,
  ADD COLUMN IF NOT EXISTS bank_code VARCHAR(20) DEFAULT NULL AFTER bank_account;

-- Notification preferences (stored as JSON)
ALTER TABLE suppliers
  ADD COLUMN IF NOT EXISTS notification_prefs JSON DEFAULT NULL AFTER bank_code;

-- Customer notification preferences
ALTER TABLE users
  ADD COLUMN IF NOT EXISTS notification_prefs JSON DEFAULT NULL;
