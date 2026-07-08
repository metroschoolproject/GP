-- Widen money columns for MMK values.
-- DECIMAL(10,2) tops out at 99,999,999.99, which is too small for
-- quantity-based package totals and high-value bookings in MMK.

DELIMITER //

DROP PROCEDURE IF EXISTS gp_widen_decimal_if_narrow//

CREATE PROCEDURE gp_widen_decimal_if_narrow(
    IN tableName VARCHAR(64),
    IN columnName VARCHAR(64),
    IN columnDefinition TEXT
)
BEGIN
    IF EXISTS (
        SELECT 1
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = tableName
          AND COLUMN_NAME = columnName
          AND DATA_TYPE = 'decimal'
          AND NUMERIC_PRECISION < 12
    ) THEN
        SET @ddl = CONCAT('ALTER TABLE `', tableName, '` MODIFY COLUMN `', columnName, '` ', columnDefinition);
        PREPARE stmt FROM @ddl;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END//

DELIMITER ;

CALL gp_widen_decimal_if_narrow('cart_items', 'price', 'DECIMAL(12,2) DEFAULT NULL');

CALL gp_widen_decimal_if_narrow('bookings', 'total_amount', 'DECIMAL(12,2) DEFAULT NULL');
CALL gp_widen_decimal_if_narrow('bookings', 'paid_amount', 'DECIMAL(12,2) DEFAULT NULL');
CALL gp_widen_decimal_if_narrow('booking_items', 'price', 'DECIMAL(12,2) DEFAULT NULL');
CALL gp_widen_decimal_if_narrow('booking_suppliers', 'item_price', 'DECIMAL(12,2) DEFAULT NULL');

CALL gp_widen_decimal_if_narrow('payments', 'amount', 'DECIMAL(12,2) DEFAULT NULL');
CALL gp_widen_decimal_if_narrow('payments', 'platform_fee', 'DECIMAL(12,2) DEFAULT NULL');
CALL gp_widen_decimal_if_narrow('payments', 'supplier_amount', 'DECIMAL(12,2) DEFAULT NULL');
CALL gp_widen_decimal_if_narrow('payments', 'paid_amount', 'DECIMAL(12,2) DEFAULT NULL');

CALL gp_widen_decimal_if_narrow('refunds', 'amount', 'DECIMAL(12,2) DEFAULT NULL');
CALL gp_widen_decimal_if_narrow('booking_vouchers', 'price', 'DECIMAL(12,2) DEFAULT NULL');

CALL gp_widen_decimal_if_narrow('packages', 'base_price', 'DECIMAL(12,2) DEFAULT NULL');
CALL gp_widen_decimal_if_narrow('package_items', 'default_price', 'DECIMAL(12,2) DEFAULT NULL');
CALL gp_widen_decimal_if_narrow('package_items', 'customize_price', 'DECIMAL(12,2) DEFAULT NULL');

CALL gp_widen_decimal_if_narrow('services', 'price', 'DECIMAL(12,2) DEFAULT NULL');
CALL gp_widen_decimal_if_narrow('services', 'price_min', 'DECIMAL(12,2) DEFAULT NULL');
CALL gp_widen_decimal_if_narrow('services', 'price_max', 'DECIMAL(12,2) DEFAULT NULL');
CALL gp_widen_decimal_if_narrow('venue_rooms', 'price', 'DECIMAL(12,2) DEFAULT NULL');
CALL gp_widen_decimal_if_narrow('venue_rooms', 'price_min', 'DECIMAL(12,2) DEFAULT NULL');
CALL gp_widen_decimal_if_narrow('venue_rooms', 'price_max', 'DECIMAL(12,2) DEFAULT NULL');

CALL gp_widen_decimal_if_narrow('cake_designs', 'price', 'DECIMAL(12,2) DEFAULT 0');
CALL gp_widen_decimal_if_narrow('cake_designs', 'package_price', 'DECIMAL(12,2) DEFAULT NULL');
CALL gp_widen_decimal_if_narrow('cake_designs', 'customize_price', 'DECIMAL(12,2) DEFAULT NULL');

CALL gp_widen_decimal_if_narrow('booking_supplier_replacements', 'old_price', 'DECIMAL(12,2) DEFAULT NULL');
CALL gp_widen_decimal_if_narrow('booking_supplier_replacements', 'new_price', 'DECIMAL(12,2) DEFAULT NULL');
CALL gp_widen_decimal_if_narrow('booking_supplier_replacements', 'price_delta', 'DECIMAL(12,2) DEFAULT NULL');
CALL gp_widen_decimal_if_narrow('booking_supplier_replacement_invitations', 'price', 'DECIMAL(12,2) DEFAULT NULL');
CALL gp_widen_decimal_if_narrow('booking_supplier_replacement_invitations', 'price_delta', 'DECIMAL(12,2) DEFAULT NULL');

DROP PROCEDURE IF EXISTS gp_widen_decimal_if_narrow;
