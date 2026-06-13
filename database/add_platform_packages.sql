-- Platform package management for admin-curated wedding packages.
-- Run once in phpMyAdmin or MySQL against the Golden Promise database.

DELIMITER //

DROP PROCEDURE IF EXISTS gp_add_column_if_missing//
DROP PROCEDURE IF EXISTS gp_add_index_if_missing//

CREATE PROCEDURE gp_add_column_if_missing(
    IN tableName VARCHAR(64),
    IN columnName VARCHAR(64),
    IN alterStatement TEXT
)
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = tableName
          AND COLUMN_NAME = columnName
    ) THEN
        SET @ddl = alterStatement;
        PREPARE stmt FROM @ddl;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END//

CREATE PROCEDURE gp_add_index_if_missing(
    IN tableName VARCHAR(64),
    IN indexName VARCHAR(64),
    IN createStatement TEXT
)
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM INFORMATION_SCHEMA.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = tableName
          AND INDEX_NAME = indexName
    ) THEN
        SET @ddl = createStatement;
        PREPARE stmt FROM @ddl;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END//

DELIMITER ;

CALL gp_add_column_if_missing(
    'packages',
    'slug',
    'ALTER TABLE packages ADD COLUMN slug VARCHAR(100) DEFAULT NULL AFTER name'
);
CALL gp_add_column_if_missing(
    'packages',
    'type',
    'ALTER TABLE packages ADD COLUMN `type` VARCHAR(50) NOT NULL DEFAULT ''curated'' AFTER slug'
);
CALL gp_add_column_if_missing(
    'packages',
    'tagline',
    'ALTER TABLE packages ADD COLUMN tagline VARCHAR(255) DEFAULT NULL AFTER description'
);
CALL gp_add_column_if_missing(
    'packages',
    'image_url',
    'ALTER TABLE packages ADD COLUMN image_url VARCHAR(255) DEFAULT NULL AFTER base_price'
);
CALL gp_add_column_if_missing(
    'packages',
    'is_active',
    'ALTER TABLE packages ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER image_url'
);
CALL gp_add_column_if_missing(
    'packages',
    'sort_order',
    'ALTER TABLE packages ADD COLUMN sort_order INT NOT NULL DEFAULT 0 AFTER is_active'
);

UPDATE packages
SET slug = LOWER(REPLACE(REPLACE(TRIM(name), '&', 'and'), ' ', '-'))
WHERE (slug IS NULL OR slug = '')
  AND name IS NOT NULL
  AND TRIM(name) <> '';

UPDATE packages
SET `type` = COALESCE(NULLIF(`type`, ''), 'curated'),
    is_active = COALESCE(is_active, 1),
    sort_order = COALESCE(sort_order, 0);

DELETE pi
FROM package_items pi
INNER JOIN packages p ON p.package_id = pi.package_id
WHERE p.deleted_at IS NOT NULL;

CALL gp_add_index_if_missing(
    'packages',
    'idx_packages_deleted_active_order',
    'CREATE INDEX idx_packages_deleted_active_order ON packages(deleted_at, is_active, sort_order, package_id)'
);
CALL gp_add_index_if_missing(
    'packages',
    'idx_packages_slug',
    'CREATE INDEX idx_packages_slug ON packages(slug)'
);
CALL gp_add_index_if_missing(
    'package_items',
    'idx_package_items_package_category',
    'CREATE INDEX idx_package_items_package_category ON package_items(package_id, category_id)'
);
CALL gp_add_index_if_missing(
    'package_items',
    'idx_package_items_package_service',
    'CREATE INDEX idx_package_items_package_service ON package_items(package_id, service_id)'
);

CALL gp_add_column_if_missing(
    'package_items',
    'quantity_type',
    'ALTER TABLE package_items ADD COLUMN quantity_type VARCHAR(20) NOT NULL DEFAULT ''fixed'' AFTER default_price'
);
CALL gp_add_column_if_missing(
    'package_items',
    'quantity',
    'ALTER TABLE package_items ADD COLUMN quantity INT NOT NULL DEFAULT 1 AFTER quantity_type'
);

UPDATE package_items pi
LEFT JOIN categories c ON c.id = pi.category_id
SET pi.quantity_type = 'guests',
    pi.quantity = CASE WHEN pi.quantity IS NULL OR pi.quantity < 1 THEN 100 ELSE pi.quantity END
WHERE LOWER(COALESCE(c.slug, c.name, '')) IN ('food', 'catering');

INSERT INTO packages (name, slug, `type`, description, tagline, base_price, image_url, is_active, sort_order)
SELECT 'Classic Wedding', 'classic-wedding', 'curated',
       'Core wedding services for a complete ceremony and reception.',
       'Venue, food, dress, studio, and accessories in one plan.',
       5000000, '', 1, 10
WHERE NOT EXISTS (SELECT 1 FROM packages WHERE slug = 'classic-wedding');

INSERT INTO packages (name, slug, `type`, description, tagline, base_price, image_url, is_active, sort_order)
SELECT 'Venue & Catering', 'venue-catering', 'curated',
       'A reception-focused package for couples who want the place and menu handled together.',
       'Choose a venue and food suppliers for the big day.',
       3500000, '', 1, 20
WHERE NOT EXISTS (SELECT 1 FROM packages WHERE slug = 'venue-catering');

INSERT INTO packages (name, slug, `type`, description, tagline, base_price, image_url, is_active, sort_order)
SELECT 'Bridal Essentials', 'bridal-essentials', 'curated',
       'Dress and accessories for the wedding look.',
       'Everything the bride needs to feel ready.',
       1200000, '', 1, 30
WHERE NOT EXISTS (SELECT 1 FROM packages WHERE slug = 'bridal-essentials');

INSERT INTO packages (name, slug, `type`, description, tagline, base_price, image_url, is_active, sort_order)
SELECT 'Memories Package', 'memories-package', 'curated',
       'Studio and visual coverage services for wedding memories.',
       'Capture the day with trusted studio services.',
       900000, '', 1, 40
WHERE NOT EXISTS (SELECT 1 FROM packages WHERE slug = 'memories-package');

INSERT INTO packages (name, slug, `type`, description, tagline, base_price, image_url, is_active, sort_order)
SELECT 'Accessories & Decor', 'accessories-decor', 'curated',
       'Finishing touches for the ceremony, reception, and wedding party.',
       'Accessories and decor details gathered into one package.',
       700000, '', 1, 50
WHERE NOT EXISTS (SELECT 1 FROM packages WHERE slug = 'accessories-decor');

INSERT INTO packages (name, slug, `type`, description, tagline, base_price, image_url, is_active, sort_order)
SELECT 'Complete Wedding', 'complete-wedding', 'curated',
       'A full wedding package template covering every core service category.',
       'All essential wedding categories in one admin-curated plan.',
       6500000, '', 1, 60
WHERE NOT EXISTS (SELECT 1 FROM packages WHERE slug = 'complete-wedding');

-- Platform packages use real supplier services selected by admin.
-- This seed step removes old category-only placeholder rows for the built-in platform packages,
-- then adds the first approved/paid supplier service found for each required category.
-- It does NOT use supplier_packages; it only uses rows from services.

DELETE pi
FROM package_items pi
INNER JOIN packages p ON p.package_id = pi.package_id
WHERE pi.service_id IS NULL
  AND p.slug IN (
      'classic-wedding',
      'venue-catering',
      'bridal-essentials',
      'memories-package',
      'accessories-decor',
      'complete-wedding'
  );

DROP TEMPORARY TABLE IF EXISTS gp_platform_package_seed_categories;
CREATE TEMPORARY TABLE gp_platform_package_seed_categories (
    package_slug VARCHAR(100) NOT NULL,
    category_slug VARCHAR(100) NOT NULL,
    default_quantity INT NOT NULL DEFAULT 1,
    PRIMARY KEY (package_slug, category_slug)
);

INSERT INTO gp_platform_package_seed_categories (package_slug, category_slug, default_quantity) VALUES
('classic-wedding', 'venue', 1),
('classic-wedding', 'food', 100),
('classic-wedding', 'dress', 1),
('classic-wedding', 'studio', 1),
('classic-wedding', 'accessories', 1),
('venue-catering', 'venue', 1),
('venue-catering', 'food', 100),
('bridal-essentials', 'dress', 1),
('bridal-essentials', 'accessories', 1),
('memories-package', 'studio', 1),
('accessories-decor', 'accessories', 1),
('complete-wedding', 'venue', 1),
('complete-wedding', 'food', 100),
('complete-wedding', 'dress', 1),
('complete-wedding', 'studio', 1),
('complete-wedding', 'accessories', 1);

INSERT INTO package_items (
    package_id,
    category_id,
    service_id,
    default_supplier_id,
    default_price,
    quantity_type,
    quantity
)
SELECT
    p.package_id,
    c.id,
    svc.id,
    svc.supplier_id,
    COALESCE(NULLIF(svc.price_min, 0), NULLIF(svc.price, 0), NULLIF(svc.price_max, 0), 0),
    CASE WHEN c.slug IN ('food', 'catering') THEN 'guests' ELSE 'fixed' END,
    CASE WHEN c.slug IN ('food', 'catering') THEN seed.default_quantity ELSE 1 END
FROM gp_platform_package_seed_categories seed
INNER JOIN packages p ON p.slug = seed.package_slug
INNER JOIN categories c ON c.slug = seed.category_slug
INNER JOIN services svc ON svc.id = (
    SELECT s2.id
    FROM services s2
    INNER JOIN suppliers sup2 ON sup2.supplier_id = s2.supplier_id
    WHERE s2.category_id = c.id
      AND s2.is_active = 1
      AND sup2.deleted_at IS NULL
      AND sup2.status IN ('approved', 'verified')
      AND sup2.payment_status = 'paid'
    ORDER BY COALESCE(NULLIF(s2.price_min, 0), NULLIF(s2.price, 0), NULLIF(s2.price_max, 0), 999999999) ASC,
             s2.id ASC
    LIMIT 1
)
WHERE NOT EXISTS (
    SELECT 1
    FROM package_items existing
    WHERE existing.package_id = p.package_id
      AND existing.category_id = c.id
      AND existing.service_id IS NOT NULL
);

DROP TEMPORARY TABLE IF EXISTS gp_platform_package_seed_categories;

DROP PROCEDURE IF EXISTS gp_add_column_if_missing;
DROP PROCEDURE IF EXISTS gp_add_index_if_missing;
