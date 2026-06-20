# Plan — Admin Supplier Replacement on Decline (Package Bookings)

**Date:** 2026-06-19
**Status:** Draft, not started
**Migration:** `database/migration_add_supplier_replacement.sql`

## Problem

A customer books a **platform package** (admin-built, multiple suppliers, each
locked at package-creation time via `package_items.default_supplier_id`). After
the booking is **confirmed**, one supplier says they can't do the wedding date.

**Current behaviour (verified):** the entire booking is **cancelled**.
- `Booking.php:1232` (`supplierRespond`, `action='decline'`) → `updateStatus(..., 'cancelled')` + `cancelAllSuppliers()` (`BookingModel.php:1801`).
- No replace / swap / reassign anywhere (`Admin.php` only view + cancel).
- Customer must re-book from scratch.

**Desired behaviour:** the platform (**admin**) swaps in another available
same-category supplier. The booking survives; other suppliers keep their slots.

## Decisions (locked)

| Question | Decision |
|---|---|
| Who picks replacement? | **Admin picks** from a system-generated candidate list |
| Cheaper / same-price swap | **Auto-finalize** — platform absorbs difference, customer pays nothing |
| Pricier swap | **Customer must approve + pay the delta** (Stripe) before it finalizes |
| Candidate range | **Capped at +25%** over original item price (`MAX_REPLACEMENT_UPCHARGE_PCT`); admin override beyond cap |
| Scope | **Package bookings only** (custom-booking decline keeps current cancel flow) |

## Pricing flow (the core rule)

```
new_price <= old_price   →  status=assigned  (auto, platform absorbs, customer untouched)
old_price < new_price <= old_price*(1+CAP)  →  status=pending_customer
                                                → customer approves + pays delta
                                                → status=assigned
new_price > cap          →  not shown to admin (admin override only)
```

## Reusable infrastructure (already exists)

- **Candidate search:** `CustomerServiceCatalog::getServices($filters)` (`CustomerServiceCatalog.php:126-244`) filters by category + date (`service_schedules.day_of_week`) + valid supplier (`approved/verified`, `paid`, `is_available`). Reuse, add price-ceiling + slot-free filters.
- **Supplier accept/decline + 48h deadline:** `supplierRespond()` (`Booking.php:1147`), `setSupplierResponseDeadline()`, `expireOverdueBookingRequests()` cron. Reuse for the replacement supplier's acceptance.
- **Stripe payments:** `Payment.php` — reuse its charge flow for the delta payment.
- **Concurrency slots:** `service_time_slots` package/customize pools (`migration_add_concurrency_pools.sql`). Release old supplier's slot, reserve new.
- **Status audit:** `logStatusChange()` (`BookingModel.php`).

## Schema changes (in migration)

1. `bookings.status` += `replacement_pending`.
2. `booking_suppliers.status` += `needs_replacement`, `replaced`.
3. `booking_suppliers` += `service_id`, `category_id`, `package_item_id`, `item_price`, `replaced_by_id`, `declined_at` — the table only stored `supplier_id`, no link to *what* the supplier covers. Backfilled from `package_items`.
4. New table `booking_supplier_replacements` — one row per swap. State machine: `pending_admin → (pending_customer →) assigned → accepted | declined_again | rejected_by_customer`. Stores `price_delta`, `requires_customer_approval`, `customer_approved_at`, `delta_payment_id`, `chosen_by_admin_id`, `decline_reason`.
5. Config constant `MAX_REPLACEMENT_UPCHARGE_PCT = 25` in `app/config/config.php`.

## Build order

### Step 1 — Migration + config
Apply `migration_add_supplier_replacement.sql` on a dev DB copy first (verify enum
modify + backfill). Add `MAX_REPLACEMENT_UPCHARGE_PCT` to config.

### Step 2 — Decline handling (replace, don't cancel)
`Booking.php:supplierRespond()`, `action='decline'`:
- If booking is a **package** booking AND status `confirmed`/`paid`:
  - Mark only that `booking_suppliers` row `needs_replacement`; set `declined_at`, store `decline_reason`.
  - Insert `booking_supplier_replacements` (`status=pending_admin`) capturing old supplier/service/price/category.
  - Set `bookings.status = replacement_pending` (if not already).
  - **Do NOT** call `cancelAllSuppliers`; leave other suppliers untouched.
  - `logStatusChange(..., 'confirmed', 'replacement_pending', ...)`.
  - Notify **admin** ("pick replacement") + customer ("arranging replacement, no action needed").
- Custom bookings → keep existing cancel behaviour (no change).

### Step 3 — Replacement candidate finder (model)
`BookingModel::findReplacementCandidates(int $replacementId): array`:
- Load replacement → `category_id`, wedding date (`event_details.event_date`), `old_price`, `old_supplier_id`.
- Call/adapt `CustomerServiceCatalog::getServices()` with:
  - `category` = old category, `date` = wedding date, exclude `old_supplier_id`.
  - **price ceiling:** `price <= old_price * (1 + MAX_REPLACEMENT_UPCHARGE_PCT/100)`.
- Exclude services whose `service_time_slots` for that date are **full** in the package pool (`confirmed_package_count >= max_concurrent_package`).
- Return ranked: cheapest-first, then rating. Tag each candidate `needs_approval = price > old_price`.

### Step 4 — Admin replacement UI + controller
- `Admin.php` new actions (delegate to `Booking`, pattern at `Admin.php:467`):
  - `replacementQueue()` — bookings in `replacement_pending`.
  - `replacementPicker($replacementId)` — declined item + candidate list (Step 3), each row shows price + "auto" vs "needs customer approval" badge.
  - `assignReplacement($replacementId)` (POST) — admin submits chosen candidate.
- View: `app/views/admin/bookings/replacement_picker.php`.

### Step 5 — Assign branch (price-gated)
`BookingModel::assignReplacement($replacementId, $newServiceId, $adminId)`:
- Compute `new_price`, `price_delta = new_price - old_price`.
- **If `price_delta <= 0`:** auto-path → run **Step 6 swap txn** immediately, `requires_customer_approval=0`.
- **If `price_delta > 0`:** set `new_*`, `price_delta`, `requires_customer_approval=1`, `status='pending_customer'`. Notify customer with approve/pay CTA. **No swap yet.** Goto Step 5b.

### Step 5b — Customer approval + delta payment
- New customer action `Booking::approveReplacement($replacementId)` → shows delta + new supplier → Stripe checkout for `price_delta` (reuse `Payment.php`).
- On payment success: set `customer_approved_at`, `delta_payment_id`; update `bookings.total_amount`/`paid_amount` by delta → run **Step 6 swap txn**.
- Customer declines proposal → `status='rejected_by_customer'` → back to admin (`pending_admin`) to pick a cheaper candidate.

### Step 6 — Swap logic (atomic transaction)
1. Begin txn.
2. Old `booking_suppliers` row → `status='replaced'`, set `replaced_by_id` after insert.
3. Release old slot: decrement `service_time_slots.confirmed_package_count` for old service+date; recompute `status`.
4. Insert NEW `booking_suppliers`: new supplier/service, `status='pending'`, copy `category_id`/`package_item_id`, `item_price = new_price`.
5. Reserve new supplier's package-pool slot for the date (increment / create row); **fail txn if no capacity** (re-pick).
6. Update `booking_items` snapshot display fields (`supplier_name`, `item_name`, `thumbnail_url`); set customer-facing `price` only when delta was paid.
7. `booking_supplier_replacements` → `status='assigned'`, `assigned_at`.
8. Set new supplier's 48h deadline (`setSupplierResponseDeadline`).
9. Commit. Notify new supplier (accept/decline request) + customer (replacement set).

### Step 7 — New supplier responds (reuse `supplierRespond`)
- **Accept:** new row → `confirmed`; replacement → `accepted`, `resolved_at`. If no open replacements/`needs_replacement` left on booking → `bookings.status` back to `confirmed`.
- **Decline:** new row → `rejected`; replacement → `declined_again` → re-notify admin (Step 4). If delta was already paid for this pick, hold credit toward next pick or refund (see edge cases).

### Step 8 — Cron / expiry
Extend `expireOverdueBookingRequests()`:
- New supplier no response in 48h → auto-decline candidate, replacement → `pending_admin`, notify admin (do NOT kill booking).
- Customer doesn't approve a `pending_customer` proposal within N days → expire proposal back to `pending_admin`.

### Step 9 — Notifications + emails
Reuse `Notification` + `EmailService`: admin-pick-needed, pricier-proposal (customer, with delta + pay link), replacement-assigned (customer), new-supplier-request, replacement-confirmed (customer), re-pick-needed.

## Edge cases

- **No candidates under cap:** replacement stays `pending_admin`; admin sees "no candidates within +25%". Admin override (pick beyond cap, still customer-approves) or manual item cancel + partial refund (v1: flag in UI).
- **Delta paid, then new supplier declines:** don't lose customer money. Either (a) auto-apply paid delta as credit to the next pick if same/closer price, or (b) refund delta and reset to `pending_admin`. Pick one in build; default = refund delta via `Payment.php`, reset proposal.
- **Multiple suppliers decline:** one replacement row each; booking `replacement_pending` until all resolved.
- **Concurrency race:** slot reserve (Step 6.5) inside txn with capacity check; reject + re-pick if full.
- **Booking immutability:** package-definition edits must not retro-change this booking — handled by booking_item snapshots (S45). Swap edits *booking* rows, not the package.

## Out of scope (v1)

- Customer-initiated replacement (admin-driven only).
- Replacement for **custom** (non-package) bookings.
- Refund flow when replacement is *cheaper* (platform keeps margin; no customer refund).
- The broader **wedding date-change** feature — but it reuses this decline→replace engine, so build this first.

## Files touched (estimate)

| File | Change |
|---|---|
| `database/migration_add_supplier_replacement.sql` | NEW (done) |
| `app/config/config.php` | `MAX_REPLACEMENT_UPCHARGE_PCT` |
| `app/controllers/Booking.php` | decline branch (S2), admin actions (S4), assign (S5), customer approve (S5b), accept/decline (S7) |
| `app/controllers/Admin.php` | delegate replacement actions |
| `app/models/BookingModel.php` | findReplacementCandidates, assignReplacement, swap txn, slot release/reserve, expiry |
| `app/models/CustomerServiceCatalog.php` | price-ceiling + slot-free filter params |
| `app/models/Payment.php` | charge replacement delta; refund-on-redecline |
| `app/views/admin/bookings/replacement_picker.php` | NEW UI |
| `app/views/main/...` | customer approve/pay-delta page |
| notifications/emails | new message types |
