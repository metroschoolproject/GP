-- Performance migration for supplier service management.
-- Run this once against the goldenpromise database.

DELIMITER //

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
    'supplier_packages',
    'thumbnail_url',
    'ALTER TABLE supplier_packages ADD COLUMN thumbnail_url VARCHAR(255) DEFAULT NULL AFTER total_price'
);
CALL gp_add_column_if_missing(
    'supplier_packages',
    'is_active',
    'ALTER TABLE supplier_packages ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER thumbnail_url'
);
CALL gp_add_column_if_missing(
    'supplier_packages',
    'categories_json',
    'ALTER TABLE supplier_packages ADD COLUMN categories_json TEXT DEFAULT NULL AFTER is_active'
);
CALL gp_add_column_if_missing(
    'services',
    'price_min',
    'ALTER TABLE services ADD COLUMN price_min DECIMAL(10,2) DEFAULT NULL AFTER price'
);
CALL gp_add_column_if_missing(
    'services',
    'price_max',
    'ALTER TABLE services ADD COLUMN price_max DECIMAL(10,2) DEFAULT NULL AFTER price_min'
);
CALL gp_add_column_if_missing(
    'venues',
    'service_id',
    'ALTER TABLE venues ADD COLUMN service_id BIGINT(20) DEFAULT NULL AFTER supplier_id'
);

UPDATE services
SET price_min = COALESCE(price_min, price),
    price_max = COALESCE(price_max, price);

ALTER TABLE services
    MODIFY max_concurrent SMALLINT UNSIGNED NOT NULL DEFAULT 1;

ALTER TABLE service_time_slots
    MODIFY max_concurrent SMALLINT UNSIGNED NOT NULL DEFAULT 1;

CALL gp_add_index_if_missing(
    'services',
    'idx_services_supplier_created',
    'CREATE INDEX idx_services_supplier_created ON services(supplier_id, created_at, id)'
);
CALL gp_add_index_if_missing(
    'venues',
    'idx_venues_service_id',
    'CREATE INDEX idx_venues_service_id ON venues(service_id)'
);
CALL gp_add_index_if_missing(
    'supplier_packages',
    'idx_supplier_packages_supplier_deleted_created',
    'CREATE INDEX idx_supplier_packages_supplier_deleted_created ON supplier_packages(supplier_id, deleted_at, created_at, id)'
);
CALL gp_add_index_if_missing(
    'service_media',
    'idx_service_media_service_id',
    'CREATE INDEX idx_service_media_service_id ON service_media(service_id, id)'
);
CALL gp_add_index_if_missing(
    'service_schedules',
    'idx_service_schedules_service_day',
    'CREATE INDEX idx_service_schedules_service_day ON service_schedules(service_id, day_of_week)'
);
CALL gp_add_index_if_missing(
    'service_availability',
    'idx_service_availability_service_date',
    'CREATE INDEX idx_service_availability_service_date ON service_availability(service_id, date)'
);
CALL gp_add_index_if_missing(
    'service_time_slots',
    'idx_service_time_slots_service_date_time',
    'CREATE INDEX idx_service_time_slots_service_date_time ON service_time_slots(service_id, date, start_time, end_time)'
);
CALL gp_add_index_if_missing(
    'notifications',
    'idx_notifications_user_read_id',
    'CREATE INDEX idx_notifications_user_read_id ON notifications(user_id, is_read, id)'
);
CALL gp_add_index_if_missing(
    'payments',
    'idx_payments_supplier_type_status_created',
    'CREATE INDEX idx_payments_supplier_type_status_created ON payments(supplier_id, type, status, created_at)'
);

DROP PROCEDURE gp_add_column_if_missing;
DROP PROCEDURE gp_add_index_if_missing;
