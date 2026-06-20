-- Seed: Ei Ei Phyu venue research rows  ->  suppliers + venue services + schedules
-- ============================================================================
-- Source: requireData/Categories Breakdown/Ei Ei Phyu ...md (venue/restaurant rows).
-- Idempotent: each block is guarded by NOT EXISTS on the shop_name / service name,
-- so re-running is safe. Wyndham Grand and Excel River View already exist and are
-- intentionally omitted. Category 6 = Venue. Prices are the venue-usage fee in MMK.
-- Schedules: open every day 09:00–22:00 (evening weddings).

-- ── 1. Golden Inya Restaurant ───────────────────────────────────────────────
INSERT INTO suppliers (shop_name, description, status, payment_status, is_available, agreement_accepted, agreement_version, verify_url, created_at)
SELECT 'Golden Inya Restaurant',
       'Lakeside fine-dining restaurant on Inya Lake with indoor and outdoor space (outdoor seats 700-800). Popular for weddings, engagements and receptions, buffet and set/custom menus available.',
       'approved', 'paid', 1, 1, 'supplier-v1', 'golden-inya-restaurant.business.site', NOW()
WHERE NOT EXISTS (SELECT 1 FROM suppliers WHERE shop_name = 'Golden Inya Restaurant');

INSERT INTO services (supplier_id, category_id, name, description, price, is_active, booking_type, max_concurrent, max_concurrent_package, created_at)
SELECT s.supplier_id, 6, 'Golden Inya - Lakeside Wedding Venue',
       'Indoor/outdoor lakeside venue (outdoor up to 700-800 guests). Grass-lawn usage, buffet lunch/dinner 22,000-35,000 per head.',
       2000000.00, 1, 'fullday', 2, 1, NOW()
FROM suppliers s
WHERE s.shop_name = 'Golden Inya Restaurant'
  AND NOT EXISTS (SELECT 1 FROM services x WHERE x.supplier_id = s.supplier_id AND x.name = 'Golden Inya - Lakeside Wedding Venue');

-- ── 2. Western Park Ruby (People's Park) ─────────────────────────────────────
INSERT INTO suppliers (shop_name, description, status, payment_status, is_available, agreement_accepted, agreement_version, verify_url, created_at)
SELECT 'Western Park Ruby - People''s Park',
       'Garden venue inside People''s Park, Dagon Township. Indoor (100-200) and outdoor (200-800) wedding space, guests skip the park entrance fee. Reception buffet on request.',
       'approved', 'paid', 1, 1, 'supplier-v1', NULL, NOW()
WHERE NOT EXISTS (SELECT 1 FROM suppliers WHERE shop_name = 'Western Park Ruby - People''s Park');

INSERT INTO services (supplier_id, category_id, name, description, price, is_active, booking_type, max_concurrent, max_concurrent_package, created_at)
SELECT s.supplier_id, 6, 'Western Park Ruby - Garden Wedding Venue',
       'Upper lawn usage 500,000 / lower lawn 200,000. Set menus 400,000-500,000 per table (10 pax), 10-pax table from 190,000.',
       500000.00, 1, 'fullday', 2, 1, NOW()
FROM suppliers s
WHERE s.shop_name = 'Western Park Ruby - People''s Park'
  AND NOT EXISTS (SELECT 1 FROM services x WHERE x.supplier_id = s.supplier_id AND x.name = 'Western Park Ruby - Garden Wedding Venue');

-- ── 3. Zephyr (Sein Lann So Pyay Garden) ─────────────────────────────────────
INSERT INTO suppliers (shop_name, description, status, payment_status, is_available, agreement_accepted, agreement_version, verify_url, created_at)
SELECT 'Zephyr (Sein Lann So Pyay Garden)',
       'Calm garden fine-dining and event venue beside Inya Lake. Outdoor lawn seats up to 400. Offers stage decoration, floral arrangement and theme-based decoration, Asian & Western set/buffet menus.',
       'approved', 'paid', 1, 1, 'supplier-v1', 'zephyrcafe2018@gmail.com', NOW()
WHERE NOT EXISTS (SELECT 1 FROM suppliers WHERE shop_name = 'Zephyr (Sein Lann So Pyay Garden)');

INSERT INTO services (supplier_id, category_id, name, description, price, is_active, booking_type, max_concurrent, max_concurrent_package, created_at)
SELECT s.supplier_id, 6, 'Zephyr - Garden Wedding Venue',
       'Lawn usage 900,000 (200+ guests) / 1,000,000 (under 200). Set menus 325,000-365,000 per table. Decoration, MC, photographer add-ons on request.',
       900000.00, 1, 'fullday', 2, 1, NOW()
FROM suppliers s
WHERE s.shop_name = 'Zephyr (Sein Lann So Pyay Garden)'
  AND NOT EXISTS (SELECT 1 FROM services x WHERE x.supplier_id = s.supplier_id AND x.name = 'Zephyr - Garden Wedding Venue');

-- ── 4. The White Cottage Restaurant & Lounge ────────────────────────────────
INSERT INTO suppliers (shop_name, description, status, payment_status, is_available, agreement_accepted, agreement_version, verify_url, created_at)
SELECT 'The White Cottage Restaurant & Lounge',
       'European cottage-style restaurant and lounge in Shwe Taung Kyar, Bahan. Romantic indoor space and green garden (outdoor 100-150), suited to Western-style civil weddings. Decor/planner/MC not included.',
       'approved', 'paid', 1, 1, 'supplier-v1', 'thewhitecottageyangon.com', NOW()
WHERE NOT EXISTS (SELECT 1 FROM suppliers WHERE shop_name = 'The White Cottage Restaurant & Lounge');

INSERT INTO services (supplier_id, category_id, name, description, price, is_active, booking_type, max_concurrent, max_concurrent_package, created_at)
SELECT s.supplier_id, 6, 'The White Cottage - Garden & Lounge Venue',
       'Indoor lounge and garden venue (outdoor 100-150). Asian/Western buffet and set menus, decoration and planning arranged by the couple.',
       NULL, 1, 'fullday', 2, 1, NOW()
FROM suppliers s
WHERE s.shop_name = 'The White Cottage Restaurant & Lounge'
  AND NOT EXISTS (SELECT 1 FROM services x WHERE x.supplier_id = s.supplier_id AND x.name = 'The White Cottage - Garden & Lounge Venue');

-- ── Schedules: open Mon-Sun 09:00-22:00 for every seeded venue service ───────
INSERT INTO service_schedules (service_id, day_of_week, open_time, close_time, is_available)
SELECT sv.id, d.dow, '09:00:00', '22:00:00', 1
FROM services sv
CROSS JOIN (SELECT 1 AS dow UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7) d
WHERE sv.name IN (
        'Golden Inya - Lakeside Wedding Venue',
        'Western Park Ruby - Garden Wedding Venue',
        'Zephyr - Garden Wedding Venue',
        'The White Cottage - Garden & Lounge Venue'
      )
  AND NOT EXISTS (
        SELECT 1 FROM service_schedules ss
         WHERE ss.service_id = sv.id AND ss.day_of_week = d.dow
      );
