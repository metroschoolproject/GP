# Plan: Day-Based Attire Rental Booking System

## Context

Currently, all bookings in the system use time-slot or fullday mode. Attire (wedding dress) rentals need a **day-based** model where the customer picks a borrow date, selects a rental duration (e.g., 3-day, 5-day), and the system auto-calculates the return date. The existing `attire_items` table already has per-item pricing and a single `return_days` field, but there's no concept of configurable rental periods, date-range blocking, or rental-specific booking fields.

**Goal:** Restructure the booking system to support both time-slot bookings (photography, venue, etc.) and day-based rental bookings (attire) side by side.

---

## Decisions Made

| Decision | Answer |
|---|---|
| Return days | Configurable — supplier defines multiple duration options per item (3-day, 5-day, 7-day) with different prices |
| Buffer days | Fixed — set per attire item (default 1 day for cleaning) |
| Availability conflicts | Auto-block — when a dress is borrowed for a date range, those dates + buffer are blocked |

---

## Phase 1: Database Schema Changes

### 1A. New table: `attire_rental_options`

Suppliers define multiple rental duration tiers per attire item.

```sql
CREATE TABLE attire_rental_options (
  id              BIGINT AUTO_INCREMENT PRIMARY KEY,
  attire_item_id  BIGINT NOT NULL,
  days            INT NOT NULL COMMENT 'Rental duration in days',
  price           DECIMAL(12,2) NOT NULL COMMENT 'Package price for this duration',
  customize_price DECIMAL(12,2) DEFAULT NULL COMMENT 'Customize price for this duration',
  sort_order      INT DEFAULT 0,
  created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (attire_item_id) REFERENCES attire_items(id) ON DELETE CASCADE
);
```

### 1B. Modify `attire_items` — add `buffer_days`

```sql
ALTER TABLE attire_items ADD COLUMN buffer_days INT NOT NULL DEFAULT 1 AFTER return_days;
```

Keep existing `return_days`, `borrow_package_price`, `borrow_customize_price` as legacy/fallback. The new `attire_rental_options` table takes precedence when rows exist.

### 1C. New table: `attire_rental_bookings`

Tracks which items are borrowed for which date ranges. This is the availability/blocking ledger.

```sql
CREATE TABLE attire_rental_bookings (
  id               BIGINT AUTO_INCREMENT PRIMARY KEY,
  booking_item_id  BIGINT NOT NULL,
  attire_item_id   BIGINT NOT NULL,
  rental_type      ENUM('borrow','buy') NOT NULL,
  borrow_date      DATE DEFAULT NULL,
  return_date      DATE DEFAULT NULL,
  rental_days      INT DEFAULT NULL,
  buffer_until     DATE DEFAULT NULL COMMENT 'return_date + buffer_days — date range to block',
  status           ENUM('reserved','picked_up','returned','cancelled') NOT NULL DEFAULT 'reserved',
  created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (booking_item_id) REFERENCES booking_items(id) ON DELETE CASCADE,
  FOREIGN KEY (attire_item_id) REFERENCES attire_items(id)
);
```

### 1D. Modify `cart_items` — add rental fields

```sql
ALTER TABLE cart_items
  ADD COLUMN rental_type ENUM('borrow','buy') DEFAULT NULL AFTER attire_item_id,
  ADD COLUMN borrow_date DATE DEFAULT NULL AFTER rental_type,
  ADD COLUMN rental_option_id BIGINT DEFAULT NULL AFTER borrow_date;
```

### 1E. Modify `booking_items` — add rental fields

```sql
ALTER TABLE booking_items
  ADD COLUMN rental_type ENUM('borrow','buy') DEFAULT NULL AFTER attire_item_id,
  ADD COLUMN borrow_date DATE DEFAULT NULL AFTER rental_type,
  ADD COLUMN return_date DATE DEFAULT NULL AFTER borrow_date;
```

---

## Phase 2: Supplier-Side — Attire Rental Options Management

**Files:** `app/models/SupplierServiceManager.php`, `app/controllers/SupplierServices.php`, `app/views/supplier/`

### 2A. Model: `SupplierServiceManager` — rental options CRUD

New methods:
- `saveAttireRentalOptions($attireItemId, $options[])` — delete-and-reinsert pattern (same as `saveWeeklyAvailability`)
- `getAttireRentalOptions($attireItemId)` — fetch options for an item
- `deleteAttireRentalOptions($attireItemId)` — cascade delete

Update existing:
- `saveAttireItem()` — accept and save `buffer_days` and rental options alongside the item
- `getAttireItemsByService()` — JOIN `attire_rental_options` to return options with each item
- `checkServiceReadiness()` for attire category — require at least 1 rental option per item (if no buy_price set)

### 2B. View: Supplier attire item management

Update the attire item form to include:
- `buffer_days` input (default 1)
- Rental options section: dynamic rows with `days` + `price` + `customize_price`
  - e.g., "3 days — 750,000 MMK / 800,000 MMK", "5 days — 900,000 MMK / 950,000 MMK"
  - Add/remove rows with JS (same pattern as decoration styles)

---

## Phase 3: Customer-Facing — Attire Service Detail & Cart

**Files:** `app/controllers/CustomerServices.php`, `app/views/customerServices/detail.php`, `app/controllers/Cart.php`, `app/models/CustomerServiceCatalog.php`

### 3A. Service detail page for Attire category

When the service category is "Attire", the detail page shows:
1. **Attire items** as cards (gowns, suits, etc.) — already partially done
2. **For each item, a "Borrow" or "Buy" toggle**
3. **If Borrow selected:**
   - Rental duration dropdown (populated from `attire_rental_options`): "3 days — 750,000 MMK"
   - Borrow date picker (date input)
   - Price updates based on selected duration
   - Availability check via AJAX — grey out dates where the item is already reserved
4. **If Buy selected:**
   - Just show buy price, no date picker needed

### 3B. Availability checking

New method in `CustomerServiceCatalog` or `BookingModel`:
- `getAttireItemBlockedDates($attireItemId)` — returns array of blocked date ranges
  - Queries `attire_rental_bookings` WHERE status IN ('reserved','picked_up') AND borrow_date/buffer_until overlap
- AJAX endpoint: `customerServices/checkAttireAvailability/$serviceId` — returns blocked dates JSON for calendar disabling

### 3C. Cart add flow

Update the add-to-cart logic in `CustomerServices` controller:
- Accept new POST fields: `rental_type`, `borrow_date`, `rental_option_id`
- For borrow: calculate `return_date = borrow_date + rental_days - 1`
- Validate: check attire item availability for the date range before adding
- Store `rental_type`, `borrow_date`, `rental_option_id` in `cart_items`

### 3D. Cart display

Update cart view to show for attire items:
- "Borrow: June 28 – June 30 (3 days)" or "Buy: Bridal Gown"
- Price from selected rental option

---

## Phase 4: Booking Creation — Reserve Attire Dates

**Files:** `app/models/BookingModel.php`, `app/controllers/Booking.php`

### 4A. New methods in `BookingModel`

- `reserveAttireItem($bookingItemId, $attireItemId, $rentalType, $borrowDate, $returnDate, $rentalDays, $bufferDays)`
  - Calculates `buffer_until = return_date + buffer_days`
  - Checks for conflicts: any existing `attire_rental_bookings` where the date ranges overlap
  - INSERTs into `attire_rental_bookings` with status = 'reserved'
  - Returns false if conflict detected

- `releaseAttireItems($bookingId)`
  - UPDATEs `attire_rental_bookings` SET status = 'cancelled' WHERE booking_item_id IN (SELECT id FROM booking_items WHERE booking_id = :bid)
  - Called during booking cancellation (same pattern as `releaseBookingSlots`)

- `checkAttireConflict($attireItemId, $borrowDate, $bufferUntil)`
  - Returns true if any active rental overlaps with the given range
  - Used by both cart-add validation and booking creation

### 4B. Modify `createBookingFromCart()`

After creating `booking_items` from `cart_items`:
- For items with `rental_type` set: call `reserveAttireItem()`
- Copy `rental_type`, `borrow_date`, `return_date` to `booking_items`
- If any reservation fails → rollback (same pattern as slot reservation failure)

### 4C. Modify booking cancellation

In `cancelBooking()`:
- Call `releaseAttireItems()` alongside existing `releaseBookingSlots()`

---

## Phase 5: Display Rental Info in Bookings

**Files:** `app/views/admin/bookings.php`, `app/views/admin/bookingDetail.php`, `app/views/supplier/bookingDetail.php`

### 5A. Admin booking detail

For attire booking items, show:
- Rental type: Borrow / Buy
- Borrow date → Return date
- Rental days
- Item name (already shown)

### 5B. Supplier booking detail

Same rental info display for suppliers viewing their bookings.

### 5C. Booking list views

In the bookings table, show rental date range in the item details column for attire items.

---

## Phase 6: Package Integration

**Files:** `app/models/Package.php` (or equivalent), `app/views/main/package_detail.php`

### 6A. Package item selection for attire

When a package includes an attire service:
- Customer selects specific attire item (gown/suit) from cards — already done
- **New:** Customer picks rental duration from the item's `attire_rental_options`
- **New:** Customer picks borrow date
- Price in package uses the rental option price (package price tier)

### 6B. Package booking flow

When a package booking is created with attire items:
- Same `reserveAttireItem()` flow applies
- `attire_rental_options` price is used instead of base `attire_items` price

---

## Files Changed Summary

| File | Changes |
|---|---|
| `database/goldenpromise11.sql` (or migration script) | New tables + ALTER statements |
| `app/models/SupplierServiceManager.php` | Rental options CRUD, attire readiness check update |
| `app/models/BookingModel.php` | `reserveAttireItem()`, `releaseAttireItems()`, `checkAttireConflict()`, modify `createBookingFromCart()` and `cancelBooking()` |
| `app/models/CustomerServiceCatalog.php` | Attire availability query, rental options in service detail |
| `app/controllers/CustomerServices.php` | Attire detail rental UI data, availability AJAX endpoint |
| `app/controllers/Cart.php` | Accept rental fields in add-to-cart |
| `app/controllers/Booking.php` | Pass rental fields through checkout |
| `app/controllers/SupplierServices.php` | Attire rental options save/load |
| `app/views/customerServices/detail.php` | Rental options UI, date picker, availability calendar |
| `app/views/supplier/` (attire management) | Rental options form, buffer_days input |
| `app/views/admin/bookingDetail.php` | Show rental period for attire items |
| `app/views/supplier/bookingDetail.php` | Show rental period for attire items |

---

## Implementation Order

1. **Phase 1** — Database schema (tables + ALTER) — foundation for everything
2. **Phase 2** — Supplier-side rental options management — so suppliers can configure durations/prices
3. **Phase 3** — Customer-facing detail page + cart — so customers can browse and select
4. **Phase 4** — Booking creation with date blocking — the core reservation logic
5. **Phase 5** — Display rental info in admin/supplier booking views
6. **Phase 6** — Package integration (if packages include attire services)

---

## Risks & Edge Cases

- **Overlapping rentals:** The `checkAttireConflict()` method must use proper date range overlap logic: `(borrow_date <= buffer_until) AND (buffer_until >= borrow_date)`
- **Multiple items same service:** A customer could rent multiple different items from the same attire service (e.g., gown + suit) — each item's availability is tracked independently
- **Cancellation timing:** When a booking is cancelled, blocked dates must be released immediately so other customers can book
- **Package + custom conflict:** If a package reserves an attire item for June 28, a custom booking for the same item on June 27 with 3-day rental would conflict — the conflict check must handle both sources
