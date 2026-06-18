ALTER TABLE cart_items
    ADD COLUMN IF NOT EXISTS package_cart_item_id BIGINT(20) NULL AFTER venue_room_id,
    ADD INDEX IF NOT EXISTS idx_cart_package_addon (package_cart_item_id);

ALTER TABLE booking_items
    ADD COLUMN IF NOT EXISTS package_booking_item_id BIGINT(20) NULL AFTER booking_type,
    ADD INDEX IF NOT EXISTS idx_booking_package_addon (package_booking_item_id);

SET @cart_addon_fk_exists = (
    SELECT COUNT(*)
    FROM information_schema.TABLE_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = DATABASE()
      AND TABLE_NAME = 'cart_items'
      AND CONSTRAINT_NAME = 'cart_items_package_addon_fk'
);
SET @cart_addon_fk_sql = IF(
    @cart_addon_fk_exists = 0,
    'ALTER TABLE cart_items ADD CONSTRAINT cart_items_package_addon_fk FOREIGN KEY (package_cart_item_id) REFERENCES cart_items(id) ON DELETE CASCADE ON UPDATE CASCADE',
    'SELECT 1'
);
PREPARE cart_addon_stmt FROM @cart_addon_fk_sql;
EXECUTE cart_addon_stmt;
DEALLOCATE PREPARE cart_addon_stmt;

SET @booking_addon_fk_exists = (
    SELECT COUNT(*)
    FROM information_schema.TABLE_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = DATABASE()
      AND TABLE_NAME = 'booking_items'
      AND CONSTRAINT_NAME = 'booking_items_package_addon_fk'
);
SET @booking_addon_fk_sql = IF(
    @booking_addon_fk_exists = 0,
    'ALTER TABLE booking_items ADD CONSTRAINT booking_items_package_addon_fk FOREIGN KEY (package_booking_item_id) REFERENCES booking_items(id) ON DELETE CASCADE ON UPDATE CASCADE',
    'SELECT 1'
);
PREPARE booking_addon_stmt FROM @booking_addon_fk_sql;
EXECUTE booking_addon_stmt;
DEALLOCATE PREPARE booking_addon_stmt;
