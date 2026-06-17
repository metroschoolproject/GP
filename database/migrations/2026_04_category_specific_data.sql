-- Category-specific service data: Decoration styles, Dress/Accessory rental pricing, venue room photos
-- Run this migration before deploying the category-specific service create/edit feature.

-- 1. Add Decoration category
INSERT INTO categories (name, slug)
SELECT 'Decoration', 'decoration'
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE slug = 'decoration');

-- 2. Add photo per venue hall/room
ALTER TABLE venue_rooms
ADD COLUMN IF NOT EXISTS photo_url mediumtext DEFAULT NULL AFTER min_lead_days;

-- 3. Decoration styles (free-text name + price, supplier-defined)
CREATE TABLE IF NOT EXISTS decoration_styles (
  id bigint(20) NOT NULL AUTO_INCREMENT,
  service_id bigint(20) NOT NULL,
  name varchar(150) NOT NULL,
  price decimal(12,2) NOT NULL DEFAULT '0.00',
  sort_order int(11) NOT NULL DEFAULT '0',
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  KEY idx_decoration_styles_service (service_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 4. Rental pricing for Dress and Accessories (borrow or buy, both optional)
CREATE TABLE IF NOT EXISTS service_rental_pricing (
  id bigint(20) NOT NULL AUTO_INCREMENT,
  service_id bigint(20) NOT NULL,
  borrow_price decimal(12,2) DEFAULT NULL,
  return_days int(11) DEFAULT NULL,
  buy_price decimal(12,2) DEFAULT NULL,
  created_at timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  UNIQUE KEY uq_service_rental (service_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
