-- ═══════════════════════════════════════════════════════════════
-- Migration: Add package type columns & seed 6 wedding package types
-- ═══════════════════════════════════════════════════════════════

ALTER TABLE packages
  ADD COLUMN slug VARCHAR(100) AFTER name,
  ADD COLUMN tagline VARCHAR(255) AFTER description,
  ADD COLUMN image_url VARCHAR(255) AFTER base_price,
  ADD COLUMN is_active TINYINT(1) DEFAULT 1 AFTER image_url,
  ADD COLUMN sort_order INT DEFAULT 0 AFTER is_active,
  ADD UNIQUE KEY uk_slug (slug);

-- ───────────────────────────────────────────────────────────────
-- Seed 6 curated package types
-- ───────────────────────────────────────────────────────────────

-- 1. Photography & Videography (Studio)
INSERT INTO packages (package_id, name, slug, description, tagline, base_price, is_active, sort_order)
VALUES (1, 'Photography & Videography', 'photo-video', 'Capture every moment of your wedding with professional photography and videography services. From pre-wedding shoots to full-day coverage and cinematic highlights.', 'Every love story deserves to be captured beautifully', 3500.00, 1, 1);
INSERT INTO package_items (package_id, category_id) VALUES (1, (SELECT id FROM categories WHERE slug = 'studio'));

-- 2. Venue & Catering (Venue + Food)
INSERT INTO packages (package_id, name, slug, description, tagline, base_price, is_active, sort_order)
VALUES (2, 'Venue & Catering', 'venue-catering', 'The perfect venue paired with exquisite catering. Choose from elegant ballrooms, garden settings, or intimate halls — complete with bespoke menu planning.', 'A stunning setting with exceptional flavours', 12000.00, 1, 2);
INSERT INTO package_items (package_id, category_id) SELECT 2, id FROM categories WHERE slug IN ('venue', 'food');

-- 3. Bridal Beauty (Dress + Accessories)
INSERT INTO packages (package_id, name, slug, description, tagline, base_price, is_active, sort_order)
VALUES (3, 'Bridal Beauty', 'bridal-beauty', 'Your complete bridal look — from the perfect wedding dress to hair, makeup, and accessories. Walk down the aisle feeling absolutely radiant.', 'Look and feel your most beautiful on the big day', 2800.00, 1, 3);
INSERT INTO package_items (package_id, category_id) SELECT 3, id FROM categories WHERE slug IN ('dress', 'accessories');

-- 4. Floral & Decor (cross-category — accessible via accessories)
INSERT INTO packages (package_id, name, slug, description, tagline, base_price, is_active, sort_order)
VALUES (4, 'Floral & Decor', 'floral-decor', 'Transform your venue with breathtaking floral arrangements and styling. From bridal bouquets to stage design, every petal placed with love.', 'Blossom into a dream wedding setting', 4500.00, 1, 4);
INSERT INTO package_items (package_id, category_id) SELECT 4, id FROM categories WHERE slug IN ('accessories');

-- 5. Music & Entertainment
INSERT INTO packages (package_id, name, slug, description, tagline, base_price, is_active, sort_order)
VALUES (5, 'Music & Entertainment', 'music-entertainment', 'Set the mood with live bands, DJs, traditional performances, and emcees. Your wedding soundtrack and entertainment, perfectly orchestrated.', 'Celebrate with music that moves the soul', 2000.00, 1, 5);

-- 6. Complete Wedding (All categories — premium)
INSERT INTO packages (package_id, name, slug, description, tagline, base_price, is_active, sort_order)
VALUES (6, 'Complete Wedding Package', 'complete-wedding', 'The ultimate wedding experience. Venue, catering, photography, attire, decor, and entertainment — all coordinated through your personal wedding concierge.', 'Every detail, every moment, perfectly planned', 25000.00, 1, 6);
INSERT INTO package_items (package_id, category_id) SELECT 6, id FROM categories;
