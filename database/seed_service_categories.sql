INSERT INTO categories (name, slug)
SELECT 'Accessories', 'accessories'
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE slug = 'accessories');

INSERT INTO categories (name, slug)
SELECT 'Dress', 'dress'
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE slug = 'dress');

INSERT INTO categories (name, slug)
SELECT 'Food', 'food'
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE slug = 'food');

INSERT INTO categories (name, slug)
SELECT 'Package', 'package'
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE slug = 'package');

INSERT INTO categories (name, slug)
SELECT 'Studio', 'studio'
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE slug = 'studio');

INSERT INTO categories (name, slug)
SELECT 'Venue', 'venue'
WHERE NOT EXISTS (SELECT 1 FROM categories WHERE slug = 'venue');
