# Wishlist with Collections — Implementation Plan

## Overview
Add a wishlist/favorites system for customers to save services while browsing. Includes **folder-based collections** so couples can organize saved items (e.g., "Venues I love", "Photographers to check").

## What Already Exists
- A `favorites` table exists in the DB with columns `id`, `user_id`, `item_type`, `item_id` — but **zero code references it**. We'll extend it.
- The customer-facing views (service catalog, detail, packages) each have a self-contained `gp-header` nav bar with a profile dropdown menu.
- The existing customer dashboard sidebar is just an include of the admin sidebar — there's no dedicated customer sidebar.

---

## Database Changes

### 1. New `wishlist_collections` table
```sql
CREATE TABLE `wishlist_collections` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 2. Extend existing `favorites` table
```sql
ALTER TABLE `favorites` 
  ADD COLUMN `collection_id` bigint(20) DEFAULT NULL,
  ADD COLUMN `notes` text DEFAULT NULL,
  ADD COLUMN `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  ADD KEY `collection_id` (`collection_id`),
  ADD CONSTRAINT FOREIGN KEY (`collection_id`) REFERENCES `wishlist_collections` (`id`) ON DELETE SET NULL;
```

### 3. Migration file
Place in `database/migration_add_wishlist_collections.sql`

---

## New Files

### `app/models/WishlistModel.php`
- Extends nothing (same pattern as `CartModel`, `CustomerServiceCatalog`)
- Constructor: `$this->db = new Database();`
- Methods:
  - `toggle(int $userId, string $itemType, int $itemId, ?int $collectionId): array` — returns `{action: 'added'|'removed', id: ?}`
  - `getUserWishlist(int $userId, ?int $collectionId): array` — returns collections + items with service details
  - `getCollections(int $userId): array`
  - `createCollection(int $userId, string $name): int`
  - `renameCollection(int $collectionId, int $userId, string $name): bool`
  - `deleteCollection(int $collectionId, int $userId): bool`
  - `moveToCollection(int $favoriteId, int $collectionId, int $userId): bool`
  - `addNote(int $favoriteId, int $userId, string $note): bool`
  - `isFavorited(int $userId, string $itemType, int $itemId): bool`
  - `getWishlistCount(int $userId): int` — for the nav badge

---

## Modified Files

### `app/controllers/Main.php`
Add 5 new methods:
- `wishlist()` — render the wishlist page view
- `toggleWishlist()` — JSON endpoint, toggle heart
- `collectionCreate()` — JSON, create collection
- `collectionRename()` — JSON, rename collection  
- `collectionDelete()` — JSON, delete collection
- `moveToCollection()` — JSON, move item
- `addNote()` — JSON, add note to item

### `app/controllers/CustomerServices.php`
- In `service()`: pass `wishlistServiceIds` to the view for pre-filling heart states
- In `detail()`: pass `isWishlisted` flag to the view

### `app/views/main/service.php` (catalog page)
- Add heart icon to each `.gp-card` (the dark cards) — positioned top-right
- Add heart icon to each `.gp-gc` (the "More Services" grid cards) — positioned top-right
- Include inline JS for AJAX wishlist toggle
- Add wishlist heart icon + badge count to the `gp-header-actions` nav area
- Add "My Wishlist" link in the `gp-profile-menu` dropdown

### `app/views/main/_service_detail_template.php` (detail page)
- Add heart toggle button near the service title / hero section
- Include AJAX toggle logic

### `app/views/main/packages.php`
- Same nav additions: heart icon + badge in `gp-header-actions`, link in profile dropdown

### `app/views/main/index.php` (homepage)
- Same nav additions: heart icon in nav if logged in

### `app/views/main/profile.php` (customer profile)
- Same nav additions

### New View: `app/views/main/wishlist.php`
Full wishlist page with:
- gp-header (same as other customer pages)
- Collections sidebar/list on the left
- Service cards grid on the right
- "All Saved" default view
- Inline create/rename/delete collection UI
- Drag-drop or move-to-collection dropdown on each card
- Empty state: "No saved services yet — browse services to find your perfect match"

### `app/views/dashboardLayout/sidebar.php`
- Currently just includes `adminsidebar.php`
- Add a customer sidebar variant or add a wishlist link to an existing customer-facing sidebar

---

## Heart Button Component Design

### Visual
```
┌──────────────────────────┐
│  ♡  →  ♥                │   Heart icon, filled red when saved
│  (22px, top-right)       │   White/light stroke on dark cards
└──────────────────────────┘
```
- **Unsaved**: Outline heart (♡), semi-transparent white/gold
- **Saved**: Filled heart (♥), red (`#b94a48`), with a subtle scale animation
- **Loading**: Subtle pulse animation while AJAX is in-flight

### Interaction
1. Click heart → fire AJAX `POST /main/toggleWishlist`
2. If user not logged in → redirect to auth page (with `?redirect=` param)
3. On success: toggle heart state, show brief toast/feedback
4. If saved with no collection → goes to default "All Saved" (collection_id = NULL)
5. Toast shows "Saved to wishlist" / "Removed from wishlist" with a link to the wishlist page

---

## Implementation Order

1. **Database migration** — create `wishlist_collections`, alter `favorites`
2. **WishlistModel** — all DB methods
3. **Main controller endpoints** — JSON APIs + wishlist page route
4. **Wishlist view page** — full UI for managing wishlist
5. **Service catalog page** — heart buttons on cards
6. **Service detail page** — heart button on detail
7. **Navigation updates** — heart icon + badge in gp-header, link in profile dropdown
8. **Packages page** — heart buttons (optional for packages)
9. **Test the full flow**

---

## Data Flow for "Toggle Wishlist"

```
1. User clicks heart on service card
2. JS sends: POST /main/toggleWishlist
   Body: { item_type: 'service', item_id: 42, collection_id: null }
3. Main::toggleWishlist():
   - Check session_uid (require login)
   - Check if already in favorites
   - If yes → DELETE from favorites → return {action: 'removed'}
   - If no → INSERT into favorites → return {action: 'added', id: 123}
4. JS updates heart icon + shows toast
```

## Data Flow for "View Wishlist Page"

```
1. GET /main/wishlist
2. Main::wishlist():
   - Check session_uid (require login)
   - WishlistModel::getUserWishlist(userId)
   - Returns:
     {
       collections: [{id, name, count}],
       items: [{favorite_id, service_name, image, price, category, 
                supplier_name, collection_id, notes, ...}]
     }
3. Render wishlist.php view
```

---

## Edge Cases
- **Guest user clicks heart**: Redirect to login with `?redirect=` to return after auth
- **Service gets deleted by supplier**: Show "no longer available" placeholder in wishlist
- **Service gets unpublished**: Still show in wishlist but with "currently unavailable" badge
- **Duplicate favorites**: UNIQUE constraint on `(user_id, item_type, item_id)` prevents this
- **Many collections**: Limit to ~20 collections per user (enforced in model)
- **Empty state**: Friendly message with CTA to browse services
