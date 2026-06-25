-- Fix HTML-encoded data stored in the database
-- Root cause: htmlspecialchars() was applied at input layer before DB storage
-- This script decodes HTML entities back to raw characters

-- Fix services.name
UPDATE services SET name = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
  name,
  '&amp;', '&'),
  '&#039;', ''''),
  '&lt;', '<'),
  '&gt;', '>'),
  '&quot;', '"')
WHERE name LIKE '%&amp;%' OR name LIKE '%&#039;%' OR name LIKE '%&lt;%' OR name LIKE '%&gt;%' OR name LIKE '%&quot;%';

-- Fix services.description
UPDATE services SET description = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
  description,
  '&amp;', '&'),
  '&#039;', ''''),
  '&lt;', '<'),
  '&gt;', '>'),
  '&quot;', '"')
WHERE description LIKE '%&amp;%' OR description LIKE '%&#039;%' OR description LIKE '%&lt;%' OR description LIKE '%&gt;%' OR description LIKE '%&quot;%';

-- Fix suppliers.shop_name
UPDATE suppliers SET shop_name = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
  shop_name,
  '&amp;', '&'),
  '&#039;', ''''),
  '&lt;', '<'),
  '&gt;', '>'),
  '&quot;', '"')
WHERE shop_name LIKE '%&amp;%' OR shop_name LIKE '%&#039;%' OR shop_name LIKE '%&lt;%' OR shop_name LIKE '%&gt;%' OR shop_name LIKE '%&quot;%';

-- Fix suppliers.description
UPDATE suppliers SET description = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
  description,
  '&amp;', '&'),
  '&#039;', ''''),
  '&lt;', '<'),
  '&gt;', '>'),
  '&quot;', '"')
WHERE description LIKE '%&amp;%' OR description LIKE '%&#039;%' OR description LIKE '%&lt;%' OR description LIKE '%&gt;%' OR description LIKE '%&quot;%';

-- Fix suppliers.admin_note
UPDATE suppliers SET admin_note = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(
  admin_note,
  '&amp;', '&'),
  '&#039;', ''''),
  '&lt;', '<'),
  '&gt;', '>'),
  '&quot;', '"')
WHERE admin_note LIKE '%&amp;%' OR admin_note LIKE '%&#039;%' OR admin_note LIKE '%&lt;%' OR admin_note LIKE '%&gt;%' OR admin_note LIKE '%&quot;%';
