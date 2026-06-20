-- Per-service booking_suppliers
-- ============================================================
-- Previously booking_suppliers held ONE row per supplier per booking
-- (insertBookingSuppliers GROUP BY supplier_id), carrying only the first
-- matched service. That meant a supplier with several services in one package
-- could only be partly declined/replaced. This migration makes the table hold
-- ONE row per package service line (package_item), so decline/replacement is
-- per service.

-- 1. One row per (booking, package_item). package_item_id is NULL for direct
--    (non-package) service rows; MySQL allows multiple NULLs in a UNIQUE index,
--    so those are unaffected.
ALTER TABLE booking_suppliers
  ADD UNIQUE KEY uniq_booking_pkg_item (booking_id, package_item_id);

-- 2. Expand existing package bookings to one row per package_item, inheriting
--    the supplier's current status. INSERT IGNORE skips rows that already exist
--    (the 2 previously-backfilled rows).
INSERT IGNORE INTO booking_suppliers
  (booking_id, supplier_id, service_id, category_id, package_item_id, item_price, status, created_at)
SELECT bi.booking_id,
       pi.default_supplier_id,
       pi.service_id,
       pi.category_id,
       pi.id,
       COALESCE(pi.default_price, pi.customize_price),
       COALESCE((SELECT x.status FROM booking_suppliers x
                 WHERE x.booking_id = bi.booking_id
                   AND x.supplier_id = pi.default_supplier_id
                 ORDER BY x.id ASC LIMIT 1), 'pending'),
       NOW()
FROM booking_items bi
JOIN package_items pi
  ON pi.package_id = bi.item_id AND bi.item_type = 'package' AND pi.deleted_at IS NULL
WHERE pi.default_supplier_id IS NOT NULL;

-- 3. Drop the old supplier-level rows (package_item_id IS NULL) that belonged to
--    package suppliers — now superseded by the per-item rows from step 2.
--    Custom/direct-service rows (supplier not a package default) are preserved.
DELETE bs FROM booking_suppliers bs
WHERE bs.package_item_id IS NULL
  AND EXISTS (
    SELECT 1 FROM booking_items bi
    JOIN package_items pi ON pi.package_id = bi.item_id AND bi.item_type = 'package'
    WHERE bi.booking_id = bs.booking_id AND pi.default_supplier_id = bs.supplier_id
  );
