# Admin Customer Management — Design

**Date:** 2026-06-20
**Status:** Approved for planning
**Author:** hsumyatmoe (with Claude)

## Goal

Give admins a full customer-management area in the admin dashboard: browse/search customers,
view a rich profile with their bookings and spend, and moderate accounts (suspend, ban, unban,
edit contact info, soft-delete). Customers are `users` rows with `role_id = 1`.

## Context

- No customer-management feature exists today. Supplier management (`Admin::suppliers`,
  `supplier_review.php`, ban/warn/unban) is the established pattern to mirror.
- The admin sidebar (`app/views/dashboardLayout/adminsidebar.php`) already has a **Customers**
  subnav with two dead `#` links ("All customers", "Suspended / Banned") to be wired.
- `users` table already has the columns we need: `status enum('active','suspended','banned','locked')`,
  `deleted_at timestamp NULL`, `name`, `email`, `phone`, `address`, `created_at`, `last_login`.
- Customer role lookup: `user_roles.role_id = 1` joined to `users`.

## Critical Pre-existing Bug (must fix as part of this work)

`User::login()` (app/models/User.php:149) authenticates on **email + password only** and never
checks `status` or `deleted_at`. `getchallenge()` (line 136) also does no status check. Only the
"remember me" path (`getRememberedUser`, line 224) checks `status = 'active'`.

**Consequence:** setting a customer's status to `banned`/`suspended` does NOT block a fresh login.
The entire moderation feature is cosmetic without this fix.

**Fix:** in `login()`, after the password/challenge verification succeeds, reject the login if the
user's `status` is not `active` or `deleted_at` is set. Return a distinguishable result so the
`Users` controller can show "Your account has been suspended/banned. Contact support." Keep the
`locked` status behavior (timed lockout) working as-is — only add rejection for `suspended`,
`banned`, and soft-deleted accounts. Fetch `status` and `deleted_at` in the `login()` SELECT.

## Decisions

- **Soft-delete:** set `deleted_at = NOW()` and force `status = 'banned'`. Soft-deleted customers
  are hidden from default lists and blocked from login. Reversible by clearing `deleted_at`
  (out of scope for UI in this iteration — restoring deleted accounts is a future enhancement;
  unban only restores `suspended`/`banned` accounts that are not soft-deleted).
- **Active-booking guard:** WARN but ALLOW. When banning/deleting a customer who has active
  (confirmed / upcoming, not cancelled/completed) bookings, show a warning with the count; admin
  may proceed.
- **No email-to-customer on moderation** (future enhancement). **No admin password reset.**
  **No hard delete.**

## Backend

### Routes — `app/controllers/Admin.php`

| Route | Method | Behavior |
|---|---|---|
| `admin/customers` | GET | List. Query params: `status` (all/active/suspended/banned), `search` (name/email/phone), `page`. `?export=csv` streams filtered CSV instead of rendering. |
| `admin/customer/{id}` | GET | Rich detail page. Redirects to `admin/customers` if id missing/not a customer. |
| `admin/customerSuspend/{id}` | POST | `status` → `suspended`; `reason` required. |
| `admin/customerBan/{id}` | POST | `status` → `banned`; `reason` required. |
| `admin/customerUnban/{id}` | POST | `status` → `active` (only if not soft-deleted). |
| `admin/customerUpdate/{id}` | POST | Edit `name`, `phone`, `address` (validated; email/password not editable). |
| `admin/customerDelete/{id}` | POST | Soft-delete: `deleted_at = NOW()`, `status` → `banned`; `reason` required. |

All POST handlers: enforce `REQUEST_METHOD === 'POST'`, require reason where noted, write a
`customer_status_logs` row, set `$_SESSION['admin_flash']`, and `redirect('admin/customer/{id}')`.
Follows the exact guard/redirect shape of `Admin::banSupplier` / `warnSupplier`.

### New model — `app/models/CustomerModel.php`

All reads scoped to `role_id = 1`. Default lists exclude `deleted_at IS NOT NULL`.

| Method | Returns |
|---|---|
| `getCustomers(string $status, string $search, int $limit, int $offset)` | rows: user_id, name, email, phone, status, created_at, last_login, bookings_count |
| `getCustomersCount(string $status, string $search)` | int |
| `getCustomerStats()` | total, active, suspended_banned, new_this_month |
| `getCustomerById(int $id)` | profile row + bookings_count + total_spent, or null if not a customer |
| `getCustomerBookings(int $id)` | their bookings (ref, event date, status, amount) |
| `getActiveBookingCount(int $id)` | int — confirmed/upcoming bookings (for the warn guard) |
| `getModerationHistory(int $id)` | rows from `customer_status_logs` (newest first) joined to admin name |
| `setStatus(int $id, string $newStatus, ?string $reason, int $adminId)` | bool — updates `users.status`, logs the transition |
| `updateContact(int $id, array $data)` | bool |
| `softDelete(int $id, ?string $reason, int $adminId)` | bool — sets deleted_at + status banned, logs |

`total_spent` = sum of successful `payments` (paid_amount fallback amount) across the customer's
bookings, matching the convention used in `Payment::getAdminPaymentHistory`.

### Database — new migration file `database/migration_add_customer_status_logs.sql`

```sql
CREATE TABLE IF NOT EXISTS `customer_status_logs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `old_status` varchar(20) DEFAULT NULL,
  `new_status` varchar(20) NOT NULL,
  `action` varchar(40) NOT NULL,        -- suspend | ban | unban | soft_delete | edit_contact
  `reason` text DEFAULT NULL,
  `changed_by` bigint(20) DEFAULT NULL, -- admin user_id
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_csl_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

No changes to the `users` table.

## Frontend

Both views use the existing admin shell (`adminsidebar.php` layout, `dashboardContent` closure,
breadcrumbs) and reuse existing badge / table styles (see `admin/payments.php`, `admin/suppliers.php`).

### `app/views/admin/customers.php`
- **Stat cards:** Total customers · Active · Suspended/Banned · New this month.
- **Filter tabs:** All / Active / Suspended / Banned (preserve `search` across tabs).
- **Search box:** name / email / phone.
- **Table:** Name (+email), Phone, Status badge, Bookings count, Joined date, Actions (View).
- **CSV export** button → `admin/customers?status=...&search=...&export=csv`.
- **Pagination** via the existing reusable pagination partial.

### `app/views/admin/customer_detail.php`
- **Profile header:** name, email, phone, address, avatar/initials, status badge, joined, last login.
- **Spend summary:** total spent, bookings count.
- **Action panel:** Suspend / Ban (reason modal), Unban, Edit contact (modal), Soft-delete
  (reason modal + active-booking warning). Buttons shown conditionally on current status.
- **Bookings table:** ref, event date, status, amount, link to `admin/bookingDetail/{id}`.
- **Moderation history:** timeline of `customer_status_logs` (action, reason, admin, timestamp).

### Sidebar
Wire the two existing dead links in `adminsidebar.php`:
`#` → `URLROOT/admin/customers` ("All customers") and `URLROOT/admin/customers?status=banned`
("Suspended / Banned"). Add active-state highlighting consistent with the other nav items.

## Error Handling

- POST without correct method → redirect to list.
- Missing/blank required reason → flash error, redirect back without mutating.
- `{id}` not a customer (`role_id != 1`) or soft-deleted (for unban) → flash error, redirect.
- Unban on a soft-deleted account → refused with flash (deleted accounts aren't restored here).
- Login of suspended/banned/deleted account → rejected with a clear message via `Users` controller.

## Testing

Manual verification (no automated test harness in this PHP project):
1. Run migration; confirm `customer_status_logs` exists.
2. List page: search, each filter tab, pagination, stat-card counts, CSV export.
3. Detail page renders for a real customer; bookings + spend correct.
4. Suspend → status changes, log row written, customer can no longer log in (fresh login blocked).
5. Ban with active bookings → warning shown, proceed works.
6. Unban → status active, login works again.
7. Edit contact → values persist, log row written.
8. Soft-delete → `deleted_at` set, hidden from default list, login blocked.
9. Confirm `php -l` (via XAMPP php) clean on all changed PHP files.

## Out of Scope (YAGNI)

Admin password reset, email notifications to customers on moderation, hard delete, restoring
soft-deleted accounts via UI, customer-facing messaging.
