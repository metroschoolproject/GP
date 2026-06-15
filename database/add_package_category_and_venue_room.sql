-- Add single-category packages and package-level venue room selection.
-- Run once in phpMyAdmin or MySQL against the Golden Promise database.

DELIMITER //

DROP PROCEDURE IF EXISTS gp_add_column_if_missing//
DROP PROCEDURE IF EXISTS gp_add_index_if_missing//
DROP PROCEDURE IF EXISTS gp_add_fk_if_missing//

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

CREATE PROCEDURE gp_add_fk_if_missing(
    IN constraintName VARCHAR(64),
    IN alterStatement TEXT
)
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
        WHERE TABLE_SCHEMA = DATABASE()
          AND CONSTRAINT_NAME = constraintName
          AND CONSTRAINT_TYPE = 'FOREIGN KEY'
    ) THEN
        SET @ddl = alterStatement;
        PREPARE stmt FROM @ddl;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END//

DELIMITER ;

CALL gp_add_column_if_missing(
    'packages',
    'category_id',
    'ALTER TABLE packages ADD COLUMN category_id BIGINT(20) DEFAULT NULL AFTER name'
);

CALL gp_add_column_if_missing(
    'package_items',
    'venue_room_id',
    'ALTER TABLE package_items ADD COLUMN venue_room_id BIGINT(20) DEFAULT NULL AFTER service_id'
);

CALL gp_add_index_if_missing(
    'packages',
    'idx_packages_category',
    'CREATE INDEX idx_packages_category ON packages(category_id)'
);

CALL gp_add_index_if_missing(
    'package_items',
    'idx_package_items_venue_room',
    'CREATE INDEX idx_package_items_venue_room ON package_items(venue_room_id)'
);

CALL gp_add_fk_if_missing(
    'packages_ibfk_category',
    'ALTER TABLE packages ADD CONSTRAINT packages_ibfk_category FOREIGN KEY (category_id) REFERENCES categories(id)'
);

CALL gp_add_fk_if_missing(
    'package_items_ibfk_venue_room',
    'ALTER TABLE package_items ADD CONSTRAINT package_items_ibfk_venue_room FOREIGN KEY (venue_room_id) REFERENCES venue_rooms(id)'
);

-- Backfill category_id for older package rows from their first included service/category.
UPDATE packages p
SET p.category_id = (
    SELECT pi.category_id
    FROM package_items pi
    WHERE pi.package_id = p.package_id
      AND pi.category_id IS NOT NULL
    ORDER BY pi.id ASC
    LIMIT 1
)
WHERE p.category_id IS NULL;

DROP PROCEDURE IF EXISTS gp_add_column_if_missing;
DROP PROCEDURE IF EXISTS gp_add_index_if_missing;
DROP PROCEDURE IF EXISTS gp_add_fk_if_missing;
