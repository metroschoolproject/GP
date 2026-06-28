# Auto-Cancel + Missed Response Warning System

## Overview
1. Shorten supplier response deadline from 48h → 24h
2. Track how many times a supplier has had bookings auto-cancelled due to non-response
3. Show a warning banner on the supplier dashboard when missed count reaches 3+

## Current System
- `Booking::createPost()` sets `supplier_response_deadline` = now + 48h
- Hourly cron `Admin::cronExpireBookingRequests()` calls `BookingModel::expireOverdueBookingRequests()`
- That method finds `pending_supplier_response` bookings past deadline → auto-cancels them
- It logs via `logStatusChange()` with note "Auto-expired: 48-hour supplier response deadline passed"
- `suppliers.warning_level` already exists (0=none, 1=warning, 2=final_warning)
- `supplier_warnings` table exists for tracking warnings
- `SupplierProfile::warnSupplier()` sets warning_level + appends admin_note

## Files to Modify

### 1. Database Migration — `database/migration_missed_response_tracking.sql` (NEW)
- Add `missed_response_count` INT DEFAULT 0 to `suppliers` table
- Add `last_warning_at` TIMESTAMP NULL to `suppliers` table

### 2. `app/controllers/Booking.php` — line ~533
- Change deadline from `+48 hours` to `+24 hours`

### 3. `app/models/BookingModel.php` — `expireOverdueBookingRequests()` (line 3538-3569)
After auto-cancelling a booking:
- Look up which `supplier_id` rows were on the booking
- For each supplier, increment `missed_response_count` on `suppliers` table
- If count hits 3, 6, 9... (every 3):
  - Set `warning_level` (1 at 3, 2 at 6+)
  - Insert a row into `supplier_warnings` with `source='system'`, severity escalation
  - Update `last_warning_at`

### 4. `app/views/dashboardLayout/suppliersidebar.php`
- Read `warning_level` and `missed_response_count` from `$supplier`
- If `warning_level > 0`, show a warning banner at the top of the content area:
  - Amber/yellow for level 1
  - Red for level 2
  - Message: "You have {N} missed booking responses. Please respond to bookings within 24 hours to avoid account restrictions."
  - Dismissible (session-based via JS, not stored in DB)

### 5. `app/models/SupplierProfile.php`
- Add `getMissedResponseCount()` method
- Modify `getDashboardData()` to include `missed_response_count` and `warning_level`

## Warning Escalation Logic
| Missed Count | Warning Level | Severity | Action |
|---|---|---|---|
| 0-2 | 0 (none) | — | No action |
| 3-5 | 1 (warning) | medium | Banner: amber warning |
| 6+ | 2 (final_warning) | high | Banner: red warning |

## Verification
- Run migration SQL
- Create a test booking, wait for 24h expiry (or manually trigger cron)
- Verify missed_response_count increments
- Verify warning appears in sidebar at count >= 3
- Verify warning level escalates at count >= 6
