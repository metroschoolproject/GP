-- Rental pricing matrix for Dress and Accessories.
-- Keeps legacy borrow_price/buy_price as compatibility aliases for package prices.

ALTER TABLE service_rental_pricing
  ADD COLUMN IF NOT EXISTS borrow_package_price decimal(12,2) DEFAULT NULL AFTER service_id,
  ADD COLUMN IF NOT EXISTS borrow_customize_price decimal(12,2) DEFAULT NULL AFTER borrow_package_price,
  ADD COLUMN IF NOT EXISTS buy_package_price decimal(12,2) DEFAULT NULL AFTER return_days,
  ADD COLUMN IF NOT EXISTS buy_customize_price decimal(12,2) DEFAULT NULL AFTER buy_package_price;

UPDATE service_rental_pricing
SET borrow_package_price = COALESCE(borrow_package_price, borrow_price),
    borrow_customize_price = COALESCE(borrow_customize_price, borrow_price),
    buy_package_price = COALESCE(buy_package_price, buy_price),
    buy_customize_price = COALESCE(buy_customize_price, buy_price);
