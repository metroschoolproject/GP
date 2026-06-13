# Package System Implementation Plan

## Overview

We're building a **platform-level package system** for Golden Promise. 6 curated package types that bundle services from different categories. Admin curates them; customers browse and book them.

## Architecture

```
packages (platform-level curated bundles)
  └── package_items (links to categories + optional default services)
  
customerServices/packages → package type listing
customerServices/package/{slug} → package type detail w/ available services
admin/packages → admin CRUD for package types
```

---

## Phase 1: Database Migration

### Alter `packages` table
Add columns:
- `slug VARCHAR(100)` — URL-friendly identifier
- `type VARCHAR(50) DEFAULT 'curated'` — for future extensibility
- `image_url VARCHAR(255)` — hero image for the package type
- `is_active TINYINT(1) DEFAULT 1`
- `sort_order INT DEFAULT 0`

### Seed 6 package types

| # | Name | Slug | Categories |
|---|---|---|---|
| 1 | Photography & Videography | photo-video | Studio |
| 2 | Venue & Catering | venue-catering | Venue, Food |
| 3 | Bridal Beauty | bridal-beauty | Dress, Accessories |
| 4 | Floral & Decor | floral-decor | Accessories, cross |
| 5 | Music & Entertainment | music-entertainment | cross-category |
| 6 | Complete Wedding | complete-wedding | All categories |

Each seeded as a `packages` row with corresponding `package_items` linking to categories.

---

## Phase 2: Model — `PlatformPackage.php`

`app/models/PlatformPackage.php`

Methods:
- `getPackageTypes()` — all active package types with item count & price range
- `getPackageBySlug($slug)` — single package type with its category items
- `getPackageById($id)` — admin detail
- `getAllPackageTypesAdmin($filters, $page)` — admin listing with pagination
- `createPackageType($data)` — creates package + its category items
- `updatePackageType($id, $data)` — updates package metadata
- `deletePackageType($id)` — soft delete
- `getPackageItems($packageId)` — items with category details
- `addPackageItem($packageId, $categoryId)` — add a category to a package type
- `removePackageItem($id)` — remove category from package type
- `getFeaturedPackages($limit = 3)` — for homepage (top 3 by sort_order)
- `getServicesForCategory($categoryId, $excludePackageId)` — available services for a category (customer-facing)

---

## Phase 3: Admin Controller Updates

Add to `app/controllers/Admin.php`:

| Method | URL | Purpose |
|---|---|---|
| `packages()` | `admin/packages` | Package type list |
| `packageDetail($id)` | `admin/package/42` | View/edit a package type |
| `packageCreate()` | `admin/package-create` | Create new package type |
| `packageUpdate($id)` | `admin/package-update/42` | Update metadata |
| `packageDelete($id)` | `admin/package-delete/42` | Soft delete |
| `packageAddItem($id)` | `admin/package-add-item/42` | Add category to package |
| `packageRemoveItem($id)` | `admin/package-remove-item/42` | Remove category from package |

---

## Phase 4: Admin Views

- `app/views/admin/packages/index.php` — List of package types (table with status toggle, actions)
- `app/views/admin/packages/detail.php` — Single package type detail w/ items management
- `app/views/admin/packages/create.php` — Create form
- Reuse existing `admin/dashboard.php` layout pattern

---

## Phase 5: Admin Sidebar

Add "Packages" nav item to `app/views/dashboardLayout/adminsidebar.php` under the **Workspace** section, after "Bookings".

---

## Phase 6: Customer-Facing Controller

Add to `app/controllers/CustomerServices.php`:

| Method | URL | Purpose |
|---|---|---|
| `packages()` | `customerServices/packages` | Package type listing page |
| `packageDetail($slug)` | `customerServices/package/{slug}` | Single package type detail |

Or route through `Main.php` which delegates to `CustomerServices` (matching the existing `service()` pattern).

---

## Phase 7: Customer-Facing Views

- `app/views/main/packages.php` — Package type listing (hero + grid of 6 package types)
- `app/views/main/package_detail.php` — Single package type detail showing included categories, available services from suppliers, and a "Build your package" flow

---

## Phase 8: Homepage Update

Replace hardcoded "Most Popular Packages" section in `app/views/main/index.php` with dynamic data from `getFeaturedPackages()`.

---

## Phase 9: Navigation

Update header nav in `app/views/main/service.php` — the "Packages" link currently goes to `main/package` — update to `customerServices/packages`.

---

## Data Flow: Customer Browsing a Package Type

1. Customer visits `customerServices/packages` → sees 6 package type cards
2. Clicks "Photography & Videography" → `customerServices/package/photo-video`
3. Page shows:
   - Package type hero (name, description, image)
   - Categories included (e.g., "Studio")
   - Available services from suppliers in each category
   - Option to "Build this package" → adds category items to cart
4. Cart handles `item_type = 'package'` with multiple items

---

## Files Changed/Added Summary

| Action | File |
|---|---|
| 🆕 Create | `database/migration_add_package_columns.sql` |
| 🆕 Create | `app/models/PlatformPackage.php` |
| 🔧 Modify | `app/controllers/Admin.php` |
| 🔧 Modify | `app/controllers/CustomerServices.php` |
| 🆕 Create | `app/views/admin/packages/index.php` |
| 🆕 Create | `app/views/admin/packages/detail.php` |
| 🆕 Create | `app/views/admin/packages/create.php` |
| 🔧 Modify | `app/views/dashboardLayout/adminsidebar.php` |
| 🆕 Create | `app/views/main/packages.php` |
| 🆕 Create | `app/views/main/package_detail.php` |
| 🔧 Modify | `app/views/main/service.php` (nav link) |
| 🔧 Modify | `app/views/main/index.php` (homepage packages) |
| 🔧 Modify | `app/views/main/index.php` (nav "Packages" link) |
