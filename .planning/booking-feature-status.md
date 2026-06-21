# Booking Feature Status

Last updated: 2026-06-14

This file tracks what is already implemented for the booking feature and what should go ahead next.

## Done So Far

### Planning

- Booking feature recommendations are documented in `booking-feature-recommendations.md`.
- Booking UX/UI specification is documented in `booking-ux-ui-spec.md`.
- The intended booking flow is defined as:
  1. Cart
  2. Confirm booking
  3. Deposit payment
  4. Booking success
  5. Customer booking management
  6. Supplier/admin follow-up

### Customer Cart Entry Point

- Cart page links to the booking flow with `Proceed to Booking`.
- Service detail pages support adding selected service/date/time data to cart.
- Package detail page supports adding a package to cart.
- Cart item removal and cart count behavior already exist.

### Booking Backend

- `app/controllers/Booking.php` exists.
- `app/models/BookingModel.php` exists.
- Booking creation flow is implemented:
  - Reads cart items.
  - Creates a draft booking.
  - Copies cart items into `booking_items`.
  - Saves event details such as event date, time, guest count, location, phone, contact name, and special requests.
  - Links booking suppliers through `booking_suppliers`.
  - Clears the cart after booking creation.
  - Logs booking status changes.

### Customer Booking Pages

These customer-facing views exist in `app/views/booking/`:

- `create.php` — confirm booking page.
- `pay.php` — Stripe deposit payment page.
- `success.php` — booking confirmation page.
- `myBookings.php` — customer booking list.
- `detail.php` — customer booking detail.
- `vouchers.php` — customer voucher list.
- `cancel.php` — customer cancellation request page.

### Payment

- Stripe checkout flow is implemented in the booking controller.
- Deposit payment flow exists:
  - `processPayment`
  - `confirmPayment`
  - `handleSuccessfulPayment`
- Successful payment updates booking status and payment status.
- Payment records are inserted into the `payments` table.
- Paid amount is stored against the booking.

### Booking Status And History

- Booking status updates exist in the model.
- Booking status logs are inserted into `booking_status_logs`.
- Customer can view booking status in booking list/detail pages.
- Booking references are generated in the format `BK-YYYYMMDD-001`.

### Vouchers

- Voucher generation exists after successful payment.
- Voucher records are stored in `booking_vouchers`.
- Customer can view vouchers from the voucher page.
- Booking detail can show vouchers attached to the booking.

### Supplier/Admin Backend Draft

The controller and model already contain backend methods for supplier/admin booking management:

- Supplier:
  - `supplierBookings`
  - `supplierBookingDetail`
  - `supplierRespond`
  - Supplier accept/decline status updates.
  - Supplier booking statistics.
- Admin:
  - `adminBookings`
  - `adminBookingDetail`
  - `adminCancelBooking`
  - Admin booking statistics.
  - Admin cancellation logic.

### Customer Cancellation

- Customer cancellation request page exists at `app/views/booking/cancel.php`.
- Customer can submit a cancellation reason through `booking/submitCancellation`.
- Cancellation requests are logged to `booking_status_logs` for admin follow-up.

### Supplier Booking Management

- Supplier booking dashboard exists at `app/views/supplier/bookings.php`.
- Supplier booking detail page exists at `app/views/supplier/bookingDetail.php`.
- Supplier can accept or decline pending bookings through `supplier/bookingRespond`.
- Supplier routes are forwarded through the existing `Supplier` controller:
  - `supplier/bookings`
  - `supplier/bookingDetail/{id}`
  - `supplier/bookingRespond`
- Supplier sidebar links to the booking dashboard.

### Admin Booking Management

- Admin booking list exists at `app/views/admin/bookings.php`.
- Admin booking detail page exists at `app/views/admin/bookingDetail.php`.
- Admin can view booking items, suppliers, payments, vouchers, and status history.
- Admin can cancel a booking through `admin/bookingCancel`.
- Admin routes are forwarded through the existing `Admin` controller:
  - `admin/bookings`
  - `admin/bookingDetail/{id}`
  - `admin/bookingCancel`
- Admin sidebar links to booking management.

### Correctness Fixes Applied

- Booking creation now stores the real `cart_id` instead of `0`, so it matches the `bookings.cart_id` foreign key.
- Booking items are inserted with `pending` status.
- Deposit payment now stores `payment_status = 'partial'` while booking status becomes `paid`.
- Supplier accept/decline maps to valid `booking_items.status` values.
- Supplier lookup works from the signed-in supplier profile, not only `$_SESSION['supplier_id']`.
- Supplier assignment now supports services, platform package items, and supplier packages.
- Voucher generation avoids duplicate vouchers and does not insert invalid service IDs for package bookings.

## Need To Go Ahead Next

### 1. Confirm Database Schema Compatibility

Before full testing, confirm these tables/columns exist and match the code:

- `bookings`
- `booking_items`
- `booking_suppliers`
- `booking_status_logs`
- `booking_vouchers`
- `event_details`
- `payments`
- `cart_items`
- service/supplier relationship columns used by joins.

Important fields to verify:

- Booking statuses: `draft`, `pending_payment`, `paid`, `confirmed`, `completed`, `cancelled`.
- Supplier statuses: `pending`, `confirmed`, `rejected`, `completed`, `cancelled`.
- Payment statuses: `pending`, `success`, `failed`.
- Voucher statuses: `active`, `used`, `expired`.

### 2. Verify 2C2P Configuration

Needed work:

- Add sandbox `MERCHANT_ID` and `PAYMENT_GATEWAY_SECRET` values to `.env`.
- Apply `database/migrations/2026_08_payout_lifecycle.sql`.
- Test successful card payment.
- Test failed card payment.
- Test MM QR and card callback signature validation.
- Confirm payment record and booking status update correctly.

### 3. Run End-To-End Booking Test

Test path:

1. Add a service to cart.
2. Add a package to cart.
3. Go to cart.
4. Click `Proceed to Booking`.
5. Fill booking confirmation details.
6. Submit booking.
7. Pay deposit.
8. Confirm success page.
9. Check `My Bookings`.
10. Check booking detail.
11. Check vouchers.
12. Check supplier sees incoming booking.
13. Supplier accepts/declines.
14. Admin sees booking.
15. Customer requests cancellation.
16. Admin handles cancellation.

### 4. Runtime Verification

PHP linting was completed with XAMPP PHP because the Homebrew PHP binary fails with a missing `libtidy.58.dylib` dependency.

Verified with XAMPP PHP:

- `app/controllers/Booking.php`
- `app/models/BookingModel.php`
- customer booking views
- supplier booking views
- admin booking views
- touched dashboard sidebars

Still needed:

- Open pages in browser through XAMPP and check for runtime errors.

## Suggested Build Order

1. Database/schema verification against the live MySQL database.
2. Full end-to-end booking test.
3. 2C2P success/failure/MM QR callback test.
4. Browser pass for customer, supplier, and admin booking pages.
5. Production safety pass for Stripe keys and admin/supplier authorization.

## Current Summary

The customer booking, payment, voucher, cancellation, supplier management, and admin management surfaces are now implemented at a first working pass. The main remaining work is live database/browser verification, Stripe test coverage, and production hardening.
