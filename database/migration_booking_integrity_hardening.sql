-- Booking/payment integrity hardening
-- Apply after migration_add_supplier_replacement.sql.

ALTER TABLE payments
  MODIFY COLUMN type ENUM(
    'deposit','remaining','full','supplier_fee','replacement_delta','payout'
  ) DEFAULT NULL;

ALTER TABLE payments
  MODIFY COLUMN status ENUM('pending','processing','success','failed') DEFAULT NULL;

-- Preserve legacy rows while making reused references unique before adding
-- the constraint. The oldest occurrence keeps the original reference.
UPDATE payments p
INNER JOIN (
  SELECT duplicate_ids.id
  FROM (
    SELECT newer.id
    FROM payments newer
    INNER JOIN payments older
      ON older.transaction_ref = newer.transaction_ref
     AND older.id < newer.id
    WHERE newer.transaction_ref IS NOT NULL
    GROUP BY newer.id
  ) duplicate_ids
) duplicates ON duplicates.id = p.id
SET p.transaction_ref = CONCAT(p.transaction_ref, '-legacy-', p.id);

ALTER TABLE payments
  ADD UNIQUE KEY uq_payments_transaction_ref (transaction_ref);

ALTER TABLE payments
  ADD UNIQUE KEY uq_booking_supplier_payout (booking_id, supplier_id, type);

ALTER TABLE booking_supplier_replacements
  ADD COLUMN package_item_id BIGINT NULL AFTER booking_supplier_id,
  ADD COLUMN proposed_at TIMESTAMP NULL DEFAULT NULL AFTER customer_approved_at;

UPDATE booking_supplier_replacements r
INNER JOIN booking_suppliers bs ON bs.id = r.booking_supplier_id
SET r.package_item_id = bs.package_item_id
WHERE r.package_item_id IS NULL;

CREATE INDEX idx_replacement_proposed_status
  ON booking_supplier_replacements (status, proposed_at);

CREATE TABLE IF NOT EXISTS booking_slot_reservations (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  booking_id BIGINT NOT NULL,
  booking_item_id BIGINT NULL,
  package_item_id BIGINT NULL,
  service_id BIGINT NULL,
  slot_id BIGINT NOT NULL,
  source ENUM('custom','package') NOT NULL,
  reserved_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  released_at TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (id),
  KEY idx_booking_slot_active (booking_id, released_at),
  KEY idx_booking_slot_service (booking_id, service_id, released_at),
  KEY idx_booking_slot_slot (slot_id, released_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Repair replacements created before active rows preserved package_item_id.
CREATE TEMPORARY TABLE tmp_replacement_package_lines AS
SELECT r.id AS replacement_id,
       r.booking_supplier_id AS old_booking_supplier_id,
       COALESCE(r.package_item_id, old_bs.package_item_id) AS package_item_id,
       (
         SELECT new_bs.id
         FROM booking_suppliers new_bs
         WHERE new_bs.booking_id = r.booking_id
           AND new_bs.supplier_id = r.new_supplier_id
           AND new_bs.service_id = r.new_service_id
           AND new_bs.status NOT IN ('replaced','rejected','cancelled')
         ORDER BY new_bs.id DESC
         LIMIT 1
       ) AS new_booking_supplier_id
FROM booking_supplier_replacements r
INNER JOIN booking_suppliers old_bs ON old_bs.id = r.booking_supplier_id
WHERE r.status IN ('assigned','accepted')
  AND COALESCE(r.package_item_id, old_bs.package_item_id) IS NOT NULL;

UPDATE booking_suppliers old_bs
INNER JOIN tmp_replacement_package_lines t
        ON t.old_booking_supplier_id = old_bs.id
SET old_bs.package_item_id = NULL
WHERE t.new_booking_supplier_id IS NOT NULL;

UPDATE booking_suppliers new_bs
INNER JOIN tmp_replacement_package_lines t
        ON t.new_booking_supplier_id = new_bs.id
SET new_bs.package_item_id = t.package_item_id
WHERE t.package_item_id IS NOT NULL;

UPDATE booking_items bi
INNER JOIN packages p ON p.package_id = bi.item_id
SET bi.item_name = p.name,
    bi.supplier_name = 'Golden Promise',
    bi.thumbnail_url = p.image_url
WHERE bi.item_type = 'package'
  AND EXISTS (
    SELECT 1
    FROM booking_supplier_replacements r
    WHERE r.booking_id = bi.booking_id
      AND r.status IN ('assigned','accepted')
  );

DROP TEMPORARY TABLE tmp_replacement_package_lines;
