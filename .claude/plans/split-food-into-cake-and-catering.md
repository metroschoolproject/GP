# Plan: Split "Food" Category into "Cake" + "Food & Drinks"

## Context

The current "Food" category treats all food services the same, but cakes and catering have fundamentally different pricing models:
- **Cake**: Flat price per cake (design-focused, one-time delivery)
- **Food & Drinks (catering)**: Per-person pricing based on guest count

The user wants to split them into two distinct categories so suppliers get the right wizard experience and customers can search/filter separately.

**Key simplification**: Since Cake items have the same data structure as catering items (name, price, photo — flat pricing only vs. per-person only), we can keep the existing `food_items` table and add a `food_type` discriminator column instead of creating a new table. This means maximum code reuse.

## Architecture Decision: Single Table + Type Column

```
food_items (existing table, add column):
  + food_type ENUM('cake','catering') NOT NULL DEFAULT 'catering'

categories (new row):
  + Cake (slug: cake)
  ~ Food → rename to "Food & Drinks" (slug: food_drinks)

Customer-visible names:
  - "Cake" — simple, clear, wedding industry standard
  - "Food & Drinks" — covers catering, buffets, beverages
  URL slugs: /services/cake, /services/food-drinks
```

**Why one table, not two?** The data structures are identical (name, description, price, package_price, customize_price, photo_url). Splitting into two tables would mean duplicating 17+ file touchpoints for no structural benefit. The `food_type` column cleanly separates them at query time.

---

## Files to Modify (18 files)

### Phase 1: Database Migration

**New file: `database/migration_split_food_category.sql`**
- INSERT new category: "Cake" (slug: `cake`)
- UPDATE existing "Food" category → "Food & Drinks" (slug: `food_drinks`)
- ALTER TABLE `food_items` ADD `food_type` ENUM('cake','catering') NOT NULL DEFAULT 'catering'
- UPDATE existing food_items SET food_type = 'catering'
- Add index on (service_id, food_type)

### Phase 2: Model Layer (5 files)

**`app/models/SupplierServiceManager.php`**
- `findOrCreateCategory()`: Handle new "Cake" and "Food & Drinks" slugs
- `saveFoodItems()` (line ~2844): Change guard from `category === 'food'` to `'food_drinks'` or `'cake'`; pass `food_type` to INSERT
- `getFoodItems()` (line ~2816): Add optional `food_type` filter parameter
- `getServiceById()` (line ~1736): Check for both `'food_drinks'` and `'cake'` when injecting food_items
- Publish readiness (line ~652): Add `$isCake` and `$isCatering` checks; cake always needs flat price > 0, catering always needs per_person price > 0
- `applyFoodPriceRange()` or similar: Update category check

**`app/models/CustomerServiceCatalog.php`**
- `getFoodItems()` (line ~890): Add `food_type` filter parameter
- Service detail data (line ~329): Check for both `'food_drinks'` and `'cake'`
- Search/filter: Add "Cake" and "Food & Drinks" as filterable categories

**`app/models/CartModel.php`**
- `getFoodItem()` (line ~658): No change needed (queries by food_item ID)
- `addToCart()`/`addItem()`: No change (already handles cake_design_id and guest_count)
- Add `getCakeItem()` method or reuse `getFoodItem()` — since same table, reuse

**`app/models/PlatformPackage.php`**
- `isGuestPricedCategory()` (line ~1709): Update to check `'food_drinks'` only (cake is NOT guest-priced)
- `getLockedItemIds()` (line ~1825): Update to handle both categories

### Phase 3: Controller Layer (2 files)

**`app/controllers/Cart.php`**
- Per-person pricing (line ~204): Already works — the `pricing_model` check handles it
- Lock check (line ~169): Update to handle both categories

**`app/controllers/SupplierControllerSupport.php`**
- Image upload (line ~137): Already processes `food_items[].photo_url` — no change needed (same field name)

### Phase 4: Supplier Views + JS (4 files)

**`app/views/supplier/service_management.html`**
- Rename existing food section: "Cake Items" → keep for Cake category
- Add NEW catering section: "Menu Items" with guest count note for Food & Drinks
- Update category pills to show "Cake" and "Food & Drinks"

**`public/js/supplier-service-management.js`**
- Add `'Cake'` and `'Food & Drinks'` to `serviceCategories` array (line ~94), remove `'Food'`
- ICON map: Add Cake and Food & Drinks icons
- `toggleCategoryFields()`: Handle `'Cake'` and `'Food & Drinks'` separately
  - Cake → show cake extras (same food items UI, but no pricing_model toggle)
  - Food & Drinks → show catering extras (same food items UI, always per_person)
- `serviceFormPayload()`: Set `food_type: 'cake'` or `food_type: 'catering'` in payload
- `validateFoodItems()`: Works for both (same validation)
- `collectFoodItems()`: Works for both (same structure)
- `foodItemRowHtml()`: For catering, show "per person" label; for cake, no label
- Edit modal: Same logic, map category to food_type

**`public/js/supplier-service-detail.js`**
- Update category checks from `'food'` to both `'cake'` and `'food_drinks'`
- `foodItemCardHtml()`: Show "/person" suffix only for catering items

### Phase 5: Customer Views (2 files)

**`app/views/main/_service_detail_template.php`**
- Category detection (line ~196): Add `$isCakeCategory = slug === 'cake'` and `$isCateringCategory = slug === 'food_drinks'`
- CTA button (line ~3433): "Choose a cake" for Cake, "Choose a menu item" for Food & Drinks
- Guest count bar (line ~3510): Show for Food & Drinks only (NOT for Cake)
- Food item radio UI (line ~3775): Show "/person" suffix only for catering
- Booking summary (line ~3984): "Selected cake" vs "Selected menu item"
- Session restore: Works for both (same fields)
- JS block (line ~6191): Guest count handling only for catering

### Phase 6: Supporting Views (2 files)

**`app/views/supplier/service_detail.php`**
- Category check (line ~10): Handle both `'cake'` and `'food_drinks'`

**`app/views/supplier/onboarding.php`**
- Keywords (line ~1386): Split into cake keywords (cake, bakery, dessert, tier) and catering keywords (catering, buffet, meal, menu, drinks)

**`app/views/admin/packages/detail.php`**
- Guest-priced check (line ~810): Only Food & Drinks is guest-priced, not Cake

### Phase 7: Build CSS
- `npm run build:css` if any Tailwind classes change

---

## Implementation Order

1. Database migration (new file)
2. SupplierServiceManager.php (core model — everything depends on this)
3. CustomerServiceCatalog.php
4. CartModel.php + Cart.php
5. PlatformPackage.php
6. SupplierControllerSupport.php
7. service_management.html + supplier-service-management.js (supplier wizard)
8. supplier-service-detail.js + service_detail.php (supplier detail view)
9. _service_detail_template.php (customer-facing)
10. admin/packages/detail.php + onboarding.php
11. Build CSS

## Verification

1. **Database**: Run migration, verify new category rows exist, food_items has food_type column
2. **Supplier flow**: Create a Cake service → verify flat pricing wizard; Create a Food & Drinks service → verify per-person pricing wizard
3. **Customer flow**: Browse Cake service → no guest count bar, flat pricing; Browse Food & Drinks → guest count bar, per-person pricing
4. **Cart**: Add cake to cart → flat price; Add catering to cart → per-person × guest count
5. **Packages**: Create package with cake item → works; Create package with catering → guest count required
6. **Onboarding**: Supplier onboarding keywords route correctly to new categories
