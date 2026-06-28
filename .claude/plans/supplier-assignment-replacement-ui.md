# Supplier Assignment & Replacement UI Improvements

## Problem
When admin assigns a replacement supplier, that supplier doesn't get a clear signal on the **Assignments** page that they need to accept/decline. The controller already splits pending vs active but the view ignores it and shows a flat list. Replacement context (original supplier, price delta) is queried but never displayed.

## Changes

### 1. Restructure `app/views/supplier/assignments.php`

**Split into two sections:**

- **"Action Required"** — cards for pending assignments (`supplier_status === 'pending'`), shown prominently at the top with:
  - An amber "Awaiting your response" badge
  - Accept / Decline buttons directly on the card (calls `POST /supplier/bookingRespond`)
  - For **replacement assignments** (`replacement_id` is not null): a blue info line saying "You've been chosen as a replacement for [original_supplier]'s [original_service]"
  - If `price_delta > 0`: show the price difference and "Customer approval required" note
  - Deadline display from `supplier_response_deadline`

- **"Confirmed Assignments"** — cards for active/confirmed bookings (the existing card design, no changes needed beyond minor cleanup)

**Add inline Accept/Decline JS** — reuse the same `POST /supplier/bookingRespond` pattern from `bookingDetail.php` (with CSRF token).

### 2. Enhance `app/views/supplier/bookingDetail.php` response bar

When `$activeRepl` exists (replacement assignment), change the response bar text to:
- "You've been assigned as a replacement" (heading)
- "The previous supplier ([original_supplier]) declined this booking. Please accept or decline." (subtext)

This requires passing `$activeRepl` from the controller (currently only fetched in `supplierRespond`, not in `supplierBookingDetail`). Need to add a lookup in `supplierBookingDetail()`.

### 3. Controller: pass replacement info to booking detail

In `Booking::supplierBookingDetail()`, fetch `getActiveReplacementForSupplier()` and pass it as `$activeReplacement` to the view. Also pass the original supplier/service info from `myServiceRows` replacement data.

### 4. Remove unused code

- In `assignments.php`: the `$daysUntil` closure is defined but its calculation uses a flawed day-of-year approach; replace with the same `DateTimeImmutable::diff()` pattern used in `bookingDetail.php`, or simply rely on the `event_date` already available.
- No other dead code found — the replacement model methods are all used.

## Files to modify

| File | Change |
|---|---|
| `app/views/supplier/assignments.php` | Major: split pending/active sections, add accept/decline UI, show replacement context |
| `app/views/supplier/bookingDetail.php` | Minor: enhance response bar text for replacement assignments |
| `app/controllers/Booking.php` | Minor: pass `$activeReplacement` in `supplierBookingDetail()` |

## Verification
- Supplier with a pending replacement assignment should see an "Action Required" section at the top with accept/decline buttons and "You're a replacement for..." context
- Supplier with confirmed assignments should see them in a separate section below
- On the booking detail page, a replacement supplier should see "You've been assigned as a replacement" in the response bar
- Accept/Decline from the assignments page should work (POST to bookingRespond, page reloads)
