# Plan: Pagination for All List-Loading Features

## Pages to Paginate (7 total)

| # | Page | Controller | Model Method | View | Per Page |
|---|------|-----------|--------------|------|----------|
| 1 | Admin Bookings | `Booking::adminBookings()` | `BookingModel::getAllBookings()` | `admin/bookings.php` | 15 |
| 2 | Admin Payments | `Admin::payments()` | `Payment::getAdminPaymentHistory()` | `admin/payments.php` | 20 |
| 3 | Admin Payment Verify | `Admin::paymentVerification()` | custom DB queries | `admin/paymentVerification.php` | 15 |
| 4 | Admin Suppliers | `Admin::supplierApplications()` | `SupplierProfile::getApplications()` | `admin/suppliers.php` | 15 |
| 5 | Admin Notifications | `Admin::notifications()` | `Notification::getAll()` | `admin/notifications.php` | 20 |
| 6 | Customer My Bookings | `Booking::myBookings()` | `BookingModel::getCustomerBookings()` | `booking/myBookings.php` | 12 |
| 7 | Customer Vouchers | `Booking::vouchers()` | `BookingModel::getCustomerVouchers()` | `booking/vouchers.php` | 12 |

Already paginated — skip: Supplier bookings, Supplier earnings

## Pattern

Follow `Booking::supplierBookings()` pattern (lines 988-1028):
- Controller reads `$_GET['page']`, computes offset
- Model method accepts `$limit, $offset` + returns paginated rows
- Model has companion COUNT method
- Controller passes `$currentPage, $totalPages, $totalCount, $perPage` to view

## Files Changed

### New file: `app/views/partials/_pagination.php`
Reusable pagination component — Prev/Next + page numbers with ellipsis. Uses existing view CSS classes from admin bookings view.

### Model changes

1. **`BookingModel::getAllBookings()`** — add `$limit, $offset` params
2. **`BookingModel::getAllBookingsCount()`** — new COUNT method
3. **`Payment::getAdminPaymentHistory()`** — add `$limit, $offset` params
4. **`Payment::getAdminPaymentHistoryCount()`** — new COUNT method
5. **`SupplierProfile::getApplications()`** — add `$limit, $offset` params
6. **`SupplierProfile::getApplicationsCount()`** — new COUNT method
7. **`Notification::getAll()`** — add `$offset` param (already has `$limit`)
8. **`Notification::getAllCount()`** — new COUNT method
9. **`BookingModel::getCustomerBookings()`** — add `$limit, $offset` params
10. **`BookingModel::getCustomerBookingsCount()`** — new COUNT method
11. **`BookingModel::getCustomerVouchers()`** — add `$limit, $offset` params
12. **`BookingModel::getCustomerVouchersCount()`** — new COUNT method

### Controller changes

1. **`Booking::adminBookings()`** — page/offset/totalPages logic
2. **`Admin::payments()`** — page/offset/totalPages logic
3. **`Admin::paymentVerification()`** — page/offset/totalPages logic
4. **`Admin::supplierApplications()`** — page/offset/totalPages logic
5. **`Admin::notifications()`** — page/offset/totalPages logic
6. **`Booking::myBookings()`** — page/offset/totalPages logic
7. **`Booking::vouchers()`** — page/offset/totalPages logic

### View changes

1. **`admin/bookings.php`** — use `_pagination` partial
2. **`admin/payments.php`** — use `_pagination` partial
3. **`admin/paymentVerification.php`** — use `_pagination` partial
4. **`admin/suppliers.php`** — add pagination UI
5. **`admin/notifications.php`** — add pagination UI
6. **`booking/myBookings.php`** — add pagination UI
7. **`booking/vouchers.php`** — add pagination UI
