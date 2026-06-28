# Shared Guest Count for Venue + Food

## Problem
- Guest count is only collected in a booking details modal and saved to sessionStorage (never sent to server)
- `cart_items` has no `guest_count` column
- Food items have flat pricing (no per-person model)
- Venue hall capacity doesn't feed into food pricing

## Goal
One shared guest count input that:
- Auto-fills from venue hall capacity when a hall is selected
- Multiplies food per-person pricing to show total
- Persists through cart → checkout → booking

## Changes

### 1. Database Migration

```sql
-- Food items: per-person pricing model
ALTER TABLE food_items
  ADD COLUMN pricing_model ENUM('flat','per_person') DEFAULT 'flat'
    AFTER customize_price;

-- Cart: store guest count
ALTER TABLE cart_items
  ADD COLUMN guest_count INT DEFAULT NULL
    AFTER cake_design_id;
```

### 2. Template: `app/views/main/_service_detail_template.php`

**Add guest count input above the availability section** (visible for venue and food services):
- A number input with label "Guest Count"
- For venues: auto-populates when a hall is selected (from hall capacity)
- For food: used to calculate per-person totals
- Stored as a hidden input in the cart form: `<input name="guest_count">`

**Modify food item rendering**:
- If `pricing_model === 'per_person'`, show "X MMK/person" and calculate total = price × guest_count
- If `pricing_model === 'flat'`, show fixed price (current behavior)
- Update totals live when guest count changes

**Modify venue hall selection JS**:
- When a hall is selected, set the shared guest count input to the hall's capacity
- This automatically updates food totals

**Modify booking details modal**:
- Pre-fill the modal's guests field from the shared guest count input
- When modal guests field changes, sync back to the shared input

**Add guest_count to `saveGuestBookingState()`** and restore logic.

### 3. Cart Controller: `app/controllers/Cart.php`

Read `guest_count` from POST:
```php
'guest_count' => !empty($_POST['guest_count']) ? (int)$_POST['guest_count'] : null,
```

For per-person food items, calculate price:
```php
if ($isPerPersonFood && $itemData['guest_count'] > 0) {
    $itemData['price'] = $basePrice * $itemData['guest_count'];
}
```

### 4. CartModel: `app/models/CartModel.php`

Add `guest_count` to `addItem()` — read from data, include in INSERT if column exists.

### 5. SupplierServiceManager: `app/models/SupplierServiceManager.php`

- Add `pricing_model` to food item CRUD (getFoodItems, saveFoodItems)
- Supplier can set flat vs per-person when creating food items

### 6. Supplier Service Management View

Add pricing model toggle (flat / per person) to the food items form in the supplier's service management page.

## Files Modified

1. `database/migration_food_guest_count.sql` (new)
2. `app/views/main/_service_detail_template.php` (guest count input, food pricing, venue sync)
3. `app/controllers/Cart.php` (read guest_count, per-person price calc)
4. `app/models/CartModel.php` (store guest_count)
5. `app/models/CustomerServiceCatalog.php` (pass pricing_model in food items)
6. `app/models/SupplierServiceManager.php` (CRUD for pricing_model)
7. `app/views/supplier/service_detail.php` or `service_management.html` (pricing model toggle)

## UX Flow

### Venue Service
1. Customer picks a date → sees available halls
2. Selects a hall (e.g., 200 guests) → guest count auto-sets to 200
3. Guest count input shows 200, synced to the sidebar

### Food Service
1. Customer enters guest count (e.g., 200)
2. Sees food items with per-person pricing:
   - "Myanmar Danpauk Set — 13,000 MMK/person → Total: 2,600,000 MMK"
   - "Royal Chef Buffet — 35,000 MMK/person → Total: 7,000,000 MMK"
3. Selects an item → price stored = per_person × guest_count

### Venue + Food in a Package
1. Venue hall selected → guest count = 200
2. Food items automatically show totals for 200 guests
3. One guest count shared across both
