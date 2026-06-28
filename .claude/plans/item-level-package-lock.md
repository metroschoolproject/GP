# Plan: Item-Level Package Lock (Hide Customize for Locked Items)

## Context

When admin builds a package and selects specific sub-items (venue room, attire item, decoration style), those items should only be bookable through the package — not as standalone "customize" bookings. Services without sub-items (Make Up, Car, Food, Studio, etc.) remain available in both customize and package modes, with the dual-pool concurrency system controlling capacity.

## Logic Summary

| Service Type | Item in Package? | Customer Sees |
|---|---|---|
| Has sub-items (Venue, Attire, Decoration) | Specific item locked | Item shows "Package only" — can't book standalone |
| Has sub-items | Item NOT in any package | Normal customize booking |
| No sub-items (Makeup, Car, Food, Studio, etc.) | Service in package | Both customize AND package — pools control capacity |

---

## Phase 1: Query Locked Items

**File:** `app/models/PlatformPackage.php` (or `CustomerServiceCatalog.php`)

### 1A. New method: `getLockedItemIds()`

Query `package_items` for active packages to find which sub-items are locked:

```php
function getLockedItemIds(): array
```

Returns:
```php
[
    'venue_room_ids' => [21, 22],
    'attire_item_ids' => [3, 34],
    'decoration_style_ids' => [11],
]
```

Query logic:
- `SELECT DISTINCT venue_room_id, attire_item_id, decoration_style_id FROM package_items pi JOIN packages p ON p.package_id = pi.package_id WHERE pi.deleted_at IS NULL AND p.is_active = 1 AND p.status = 'published'`
- Filter out NULLs, return arrays of IDs

### 1B. Pass locked IDs to the service detail page

In `CustomerServices::detail()`, call `getLockedItemIds()` and attach to `$service['locked_items']`.

---

## Phase 2: Customer Service Detail — Mark Locked Items

**File:** `app/views/main/_service_detail_template.php`

### 2A. Venue rooms — mark locked rooms

Currently (line 3477): `$isPackageHallRow` checks if the room matches the package context URL param.

**Change:** Also check if the room ID is in `$lockedItems['venue_room_ids']`. If so:
- Add `is-locked` class to the row
- Show "Package only" badge instead of the radio button
- Disable selection
- Show which package it's in (optional)

### 2B. Attire items — mark locked items

Currently: attire item cards are rendered as selectable buttons.

**Change:** For each attire item, check if its ID is in `$lockedItems['attire_item_ids']`. If so:
- Add a "Package only" badge to the card
- Disable the click handler (don't allow selection)
- Grey out the card slightly
- Show which package it's in (optional)

### 2C. Decoration styles — mark locked styles (future)

No selection UI exists yet for decoration styles on the customer side. Skip for now — implement when the decoration style picker is built.

---

## Phase 3: Cart Validation — Block Locked Items

**File:** `app/controllers/Cart.php`

### 3A. Validate locked items aren't added as customize

In `Cart::add()`, after extracting `venue_room_id` and `attire_item_id`:
- If `venue_room_id` is in locked venue room IDs → reject with error
- If `attire_item_id` is in locked attire item IDs → reject with error
- The item can only be booked through a package

---

## Phase 4: Service Listing — Optional Enhancement

**File:** `app/views/main/service.php` or `_service_detail_template.php`

For services where ALL sub-items are locked (e.g., an attire service where every item is in a package), the service listing could show a "Available in packages" chip instead of "Book now". This is optional and can be done later.

---

## Files Changed

| File | Changes |
|---|---|
| `app/models/PlatformPackage.php` | `getLockedItemIds()` method |
| `app/controllers/CustomerServices.php` | Pass locked items to service detail |
| `app/views/main/_service_detail_template.php` | Mark locked venue rooms and attire items |
| `app/controllers/Cart.php` | Validate locked items can't be added as customize |

---

## Edge Cases

- **Same item in multiple packages:** Still locked — customer must pick one package
- **All items locked:** Service still shows in listing but all items say "Package only"
- **Package is deactivated:** Items become unlocked (query filters on `p.is_active = 1`)
- **No sub-items services:** No change — available in both modes
