ALTER TABLE services
  ADD COLUMN price_min DECIMAL(10,2) DEFAULT NULL AFTER price,
  ADD COLUMN price_max DECIMAL(10,2) DEFAULT NULL AFTER price_min;

UPDATE services
SET price_min = COALESCE(price_min, price, 0),
    price_max = COALESCE(price_max, price_min, price, 0);
