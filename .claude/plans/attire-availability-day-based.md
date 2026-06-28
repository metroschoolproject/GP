# Plan: Attire Supplier Availability — Day-Based + Cleanup

## Context

The supplier's availability section for attire services currently shows time-slot fields (slot duration, buffer minutes, concurrent per slot) which don't apply to day-based rental. Need to simplify to just "available days" and clean up old rental_pricing code that's superseded by attire_rental_options.

---

## Phase 1: Supplier Availability View — Hide Time-Slot Fields for Attire

**File:** `app/views/supplier/partials/service_detail_content.php` (lines 261-350)

For attire services (`$isRental`), the availability card should:

### Keep:
- Weekly schedule grid (Mon–Sun) — but simplified to just "Available / Closed" checkboxes (no time pickers)
- Min lead days input
- Special dates section (date overrides)

### Hide:
- Slot duration dropdown
- Buffer minutes input
- Bookings per time slot (total, package, customize) inputs
- The "Preview slots" button and slot preview area

### Add:
- A "Blocked dates" section showing upcoming dates that are reserved from `attire_rental_bookings`
- Simple list or mini calendar showing blocked date ranges

---

## Phase 2: JS Availability Handler — Attire-Specific Save

**File:** `public/js/supplier-service-detail.js`

### 2A. Update `saveAvailabilityBtn` handler
When `$isRental`, the save payload should:
- Send weekly availability (day → available/not available, no times)
- NOT send slot_duration, buffer_minutes, concurrent fields
- Use a different endpoint or flag to indicate day-based save

### 2B. Update weekly schedule rendering for attire
When rendering the weekly schedule cards for attire:
- Show just a toggle (Available / Closed) — no time inputs
- Keep the existing time-based UI for non-attire services

---

## Phase 3: Backend — Day-Based Availability Save

**File:** `app/models/SupplierServiceManager.php`

### 3A. Update `saveWeeklyAvailability()`
For attire services:
- Save weekly schedule with default open/close times (e.g., 09:00–17:00) even though times aren't shown to the supplier — the system uses them internally for schedule resolution
- Skip setting `duration_minutes`, `buffer_minutes` (not relevant)
- Still set `max_concurrent` (can limit total bookings per day)
- Set `booking_type = 'day'` (new value for day-based services)

### 3B. New method: `getAttireBlockedDates($serviceId)`
Query `attire_rental_bookings` joined with `attire_items` for this service's items, returning upcoming blocked date ranges.

---

## Phase 4: Cleanup Old Rental Pricing Code

### 4A. `SupplierServiceManager::applyRentalPriceRange()` (line 1518)
This method computes price ranges for attire services. Currently falls back to `service_rental_pricing` table. Update to:
- Primary: compute from `attire_items.rental_options` (cheapest option price)
- Fallback: keep `service_rental_pricing` for backward compatibility

### 4B. `SupplierServiceManager::formatService()` (line 1653)
Currently returns `'rental_pricing' => $this->getRentalPricing(...)`. Keep but mark as legacy — the primary pricing now comes from `attire_items[].rental_options`.

### 4C. Customer-facing `CustomerServiceCatalog::getServiceDetail()`
The `rental_pricing` field is still referenced in `_service_detail_template.php` as fallback. Keep but ensure `attire_items[].rental_options` takes precedence.

### 4D. Remove dead code
- `service_rental_pricing` references in views that show old single-price borrow/buy — replace with rental_options display
- Old `$rentalOptions` array building in `_service_detail_template.php` (lines 191-216) — update to use `attire_items[].rental_options`

---

## Phase 5: CSS Updates

**File:** `public/css/supplier-service-detail.css`

Add rules to `.is-attire-workspace`:
- Hide time-slot specific controls in the availability section
- Style the blocked dates display

---

## Files Changed

| File | Changes |
|---|---|
| `app/views/supplier/partials/service_detail_content.php` | Hide time-slot fields for attire, add blocked dates section |
| `public/js/supplier-service-detail.js` | Attire-specific availability save, simplified weekly UI |
| `app/models/SupplierServiceManager.php` | Day-based save, getAttireBlockedDates(), price range cleanup |
| `app/views/main/_service_detail_template.php` | Update rental pricing fallback to use rental_options |
| `public/css/supplier-service-detail.css` | Hide time-slot controls for attire workspace |
