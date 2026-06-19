# Plan: Individual Attire Items (like Venue Halls)

## Problem
Venue services have individual **halls/rooms** (`venue_rooms` table) that admins can pick from when building packages. Attire services have no equivalent — the whole service is added as one item. A supplier offering multiple dresses can't list them individually for package builders to choose from.

## Solution
Create `attire_items` table — same pattern as `venue_rooms` — each attire service gets multiple individual dress/accessory items with their own name, photo, and rental pricing. Admins pick a specific attire item when adding an attire service to a package (just like picking a hall for a venue).

### Database

#### NEW: `attire_items` table
```sql
CREATE TABLE attire_items (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  service_id BIGINT NOT NULL,
  name VARCHAR(150) NOT NULL,
  description TEXT DEFAULT NULL,
  photo_url VARCHAR(500) DEFAULT NULL,
  borrow_package_price DECIMAL(12,2) DEFAULT NULL,
  borrow_customize_price DECIMAL(12,2) DEFAULT NULL,
  buy_package_price DECIMAL(12,2) DEFAULT NULL,
  buy_customize_price DECIMAL(12,2) DEFAULT NULL,
  return_days INT DEFAULT NULL,
  sort_order INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### MODIFY: `package_items` — add `attire_item_id`
```sql
ALTER TABLE package_items ADD COLUMN attire_item_id BIGINT DEFAULT NULL AFTER venue_room_id;
```

---

## Implementation

### 1. Supplier side — manage attire items (like manage halls)

**NEW model method** `SupplierServiceManager.php`:
- `getAttireItems(int $serviceId): array` — fetch all items for a service
- `saveAttireItems(int $serviceId, array $items): void` — upsert/delete items (like `saveVenueRooms`)
- `normalizeAttireItems(array $items, ...): array` — validate/clean input

**`formatService()`** — add `'attire_items'` when category is `attire` (like `venue_rooms` for venue):
```php
'attire_items' => $category === 'attire' ? $this->getAttireItems((int)$service['id']) : [],
```

**`servicePublishReadiness()`** — add attire check:
- If attire and has `attire_items`, each item needs a name and at least one price

**View `service_management.html`** — add attire items management section (like hall/room management for venue)

**View `supplier/partials/service_detail_content.php`** — show attire items table (like venue rooms table)

### 2. Admin package detail — dress picker (like hall picker)

**`PlatformPackage.php`**:
- NEW: `getAttireItemsForService(int $serviceId): array` — fetch items for the add-service dropdown
- Add `attire_item_id` column to `getPackageItems()` SELECT
- JOIN `attire_items` for name/photo display
- Add `attire_item_id` support in `addPackageService()` (like `venue_room_id`)

**`Admin.php` `packageDetail()`**:
- Add `$attireOptionsByService` — fetch attire items for each attire-category service (like `$hallOptionsByService`)

**`admin/packages/detail.php`**:
- Add "Attire Item" column in services table (like "Hall" column)
- Add attire item picker in the add-service panel (like hall picker)
- Show selected attire item name/photo in the table

### 3. Customer-facing views
- `package_detail.php` — show selected attire item name instead of generic service name
- `_service_detail_template.php` — show individual attire items when browsing an attire service
- `CartModel.php` — carry `attire_item_id` through cart/booking (like `venue_room_id`)

### 4. Service-level rental pricing stays as fallback
- `service_rental_pricing` still used if an attire service has no individual items defined
- Individual items override the service-level pricing

---

## Implementation order
1. Create migration: `attire_items` table + `package_items.attire_item_id` column
2. Update `goldenpromise8.sql` schema
3. Add attire item CRUD to `SupplierServiceManager`
4. Add attire items UI to supplier service management (JS + HTML)
5. Add attire items display to supplier service detail
6. Add `attireOptionsByService` to admin `packageDetail()`
7. Add attire items to `PlatformPackage::getPackageItems()` + `addPackageService()`
8. Add attire item picker + display column to admin package detail view
9. Update customer views for individual attire item display
10. Test end-to-end
