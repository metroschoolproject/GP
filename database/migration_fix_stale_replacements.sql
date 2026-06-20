-- Reconcile replacement requests after the per-service booking_suppliers split.
-- Pre-refactor declines created ONE supplier-level replacement row that points
-- at a booking_suppliers row the per-service migration deleted, and with NULL
-- category/service (so the candidate search returns nothing). Regenerate proper
-- per-service requests from the current needs_replacement rows.

-- 1. Drop orphaned, still-open requests whose booking_supplier row no longer exists.
DELETE FROM booking_supplier_replacements
 WHERE status IN ('pending_admin', 'declined_again')
   AND booking_supplier_id NOT IN (SELECT id FROM booking_suppliers);

-- 2. Open one request per declined service row that doesn't already have one.
INSERT INTO booking_supplier_replacements
    (booking_id, booking_supplier_id, category_id,
     old_supplier_id, old_service_id, old_price, status, created_at)
SELECT bs.booking_id, bs.id, bs.category_id,
       bs.supplier_id, bs.service_id, bs.item_price, 'pending_admin', NOW()
FROM booking_suppliers bs
WHERE bs.status = 'needs_replacement'
  AND NOT EXISTS (
      SELECT 1 FROM booking_supplier_replacements r
       WHERE r.booking_supplier_id = bs.id
         AND r.status NOT IN ('rejected', 'cancelled')
  );
