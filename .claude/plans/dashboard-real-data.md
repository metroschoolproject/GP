# Plan: Admin Dashboard — Real Backend Data

## Summary

Replace the mock/fake data in `admin_dashboard.php` with real database queries via a new JSON API endpoint `Admin::overviewData()`.

## What Changes

### 1. New method: `Admin::overviewData()` (in `app/controllers/Admin.php`)

A JSON endpoint that accepts `?filter=today|week|month|year` and returns all dashboard metrics from real DB tables.

**Data sources:**

| Dashboard Field | SQL Source |
|---|---|
| Escrow (total/pending/available) | `payments` WHERE `escrow_status='held'`, `type='deposit'` |
| Total Revenue | SUM of `payments.amount` WHERE `type='deposit'` AND `status='success'` |
| Avg Customer Spend | AVG(`bookings.total_amount`) WHERE status IN (paid, confirmed, completed) |
| Total Bookings | COUNT `bookings` WHERE `status != 'draft'` |
| Confirmed / Cancelled | COUNT by `bookings.status` |
| Pending Booking Confirm | COUNT `booking_suppliers` WHERE `status='pending'` (non-draft bookings) |
| Pending Payments | COUNT `bookings` WHERE `status='payment_submitted'` |
| Pending Vendor Approval | COUNT `suppliers` WHERE `status='pending'` |
| Revenue Trend (by filter) | Bookings total_amount grouped by hour/day/week/month |
| Supplier Categories | COUNT from `supplier_categories` JOIN `categories` GROUP BY category |
| Top Partners | Top 3 suppliers by `booking_suppliers` count |
| Vendor Status | COUNT `suppliers` GROUP BY `status` |
| Community (customers/suppliers) | COUNT `user_roles` WHERE `role_id=1` (customers) and `role_id=2` (suppliers) |
| Upcoming Events | `bookings` JOIN `event_details` WHERE `event_date >= CURDATE()` |
| Popular Packages | `booking_items` WHERE `item_type='package'` GROUP BY `item_id`, top 4 |

### 2. Update `fetchDashboardData()` in `app/views/admin/admin_dashboard.php`

- Replace the 200ms `setTimeout` mock data with a real `fetch()` call to `/admin/overviewData?filter=week`
- Pass `currentFilter` and optional `eventDateFilter.value` as query params
- Keep the same return structure so `renderDashboard()` works unchanged
- Add error handling for the fetch

### 3. Revenue Trend by Filter

The filter controls how revenue data is grouped:

| Filter | GROUP BY | Labels | Period |
|---|---|---|---|
| `today` | HOUR(created_at) | 0-23 hr | Today only |
| `week` | DAYNAME(created_at) | Mon-Sun | Past 7 days |
| `month` | WEEK(created_at) | Week-1 .. Week-4 | Past 4 weeks |
| `year` | MONTHNAME(created_at) | Jan-Dec | Past 12 months |

Revenue is derived from `bookings` where status is in paid/confirmed/completed states (not drafts or cancelled).

## Files Modified

1. **`app/controllers/Admin.php`** — Add `overviewData()` method (~150 lines of clean SQL queries)
2. **`app/views/admin/admin_dashboard.php`** — Replace mock `fetchDashboardData()` with real API fetch

## What Stays the Same

- All CSS classes, layout, HTML structure
- `renderDashboard(data)` function and all chart rendering logic
- Date filter dropdown UI
- Chart.js configuration
