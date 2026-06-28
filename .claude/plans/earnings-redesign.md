# Redesign Supplier Bookings Page — Rich Card Layout

## Problem
- Table only shows customer, ref, amount, status — event date is fetched but not displayed
- Doesn't match the card design of assignments/earnings/overview
- 20 items per page (should be 10)
- Missing useful info: services, venue, days-until-event, payment status

## Available Data (already returned by query + enrichment)
Each booking object has:
- `id`, `booking_ref`, `customer_name`, `event_date`, `created_at`
- `total_amount` (supplier's share), `paid_amount`, `payment_status`
- `supplier_status` (aggregated: pending/confirmed/completed/rejected/needs_replacement)
- `items[]` — each with `service_name`, `category_name`, `venue_name`, `thumbnail_url`
- `item_count`

## Changes

### 1. Controller: `app/controllers/Booking.php` line 1609
- Change `$perPage = 20` → `$perPage = 10`

### 2. CSS: `public/css/supplier-bookings.css`
Add new card classes (`.bk-booking-card` namespace) alongside existing table/pill classes:
- `.bk-booking-card` — white card matching `.asn-card` pattern
- `.bk-booking-card--pending` — left amber border for pending
- `.bk-booking-card-facts` — icon + text fact row
- `.bk-booking-card-services` — service tag list
- `.bk-booking-card-actions` — action buttons row
- `.bk-booking-card-right` — right column with countdown/status
- Keep existing `.bk-kpi`, `.bk-section`, `.bk-pill`, `.bk-badge`, `.bk-pagination` classes

### 3. View: `app/views/supplier/bookings.php`
Replace table with card grid. Full structure:

```
Header (same: kicker + title + nav links)

Pending alert banner (keep)

Section card:
  Section head: "Booking queue" + count
  Toolbar: filter pills + search (keep)

  Card grid (1 col on mobile, 2 on desktop):
    ┌─ Card ──────────────────────────────────────────────┐
    │ BK-0345 · 2h ago                        [Pending]   │
    │ Ko Kyaw Zin                                          │
    │                                                      │
    │ 📅 Jun 30, 2026  ·  💰 546,000 MMK  ·  📦 2 items  │
    │ 🎵 Sound System, 📸 Photography                      │
    │ 📍 Sedona Hotel                                      │
    │                                                      │
    │ [ ✕ Decline ] [ ✓ Accept ]        ⏱ 2 days away    │
    └──────────────────────────────────────────────────────┘

  For confirmed/completed: simpler card, no action buttons
  For needs_replacement: special badge + context

  Pagination: 10 per page, matching earnings pagination pattern
```

### 4. Files to modify
- `app/controllers/Booking.php` — perPage = 10 (one line)
- `app/views/supplier/bookings.php` — rewrite view markup
- `public/css/supplier-bookings.css` — add card styles

### 5. No model changes needed
All data is already available from existing queries.
