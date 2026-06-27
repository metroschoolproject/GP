# Supplier-Initiated Cancellation Request

## Context
After a booking is confirmed, suppliers currently have no way to request cancellation of the entire booking. They can only "self-decline" individual services (which triggers replacement for package bookings). The user wants suppliers to be able to request full booking cancellation, which notifies both the customer and admin. Admin then processes the refund.

## Changes

### 1. Database Migration — new `booking_suppliers` status
**File:** `database/migration_supplier_cancellation_requested.sql` (new)

Add `supplier_cancellation_requested` to the `booking_suppliers.status` enum.

### 2. Model — `BookingModel.php`
Add method `supplierRequestCancellation(int $bookingId, int $supplierId, string $reason): bool`:
- Update `bookings.status` → `cancellation_requested` (reuse existing status)
- Update `booking_suppliers.status` → `supplier_cancellation_requested` for this supplier's confirmed/in_progress rows
- Log status change in `booking_status_logs`

### 3. Controller — `Booking.php`
Add method `supplierRequestCancellation(): void`:
- Validate supplier ownership, CSRF, POST
- Call model method
- Notify **customer** via `notifyBookingCustomer()`
- Notify **admin** via `notifyAdmins()`
- Send emails to customer and admin via `EmailService`
- Return JSON success response

### 4. Supplier Route — `Supplier.php`
Add route forwarding: `bookingRequestCancellation` → `Booking::supplierRequestCancellation`

### 5. Supplier Booking Detail View — UI (`bookingDetail.php`)
- Add "Request Cancellation" button visible when booking is confirmed/paid and supplier status is confirmed/in_progress
- Add a modal with reason textarea (follows existing self-decline modal pattern)
- Add JS to handle form submission via fetch

### 6. Status Display in Supplier View
When supplier status is `supplier_cancellation_requested`, show a notice bar: "Cancellation request submitted. Admin will review and process the refund."

### 7. Email Service — `EmailService.php`
- `sendSupplierInitiatedCancellationToCustomer()` — tells customer their supplier wants to cancel
- `sendSupplierInitiatedCancellationToAdmin()` — tells admin to process refund

## Verification
1. Run migration SQL
2. Log in as supplier, open a confirmed booking → click "Request Cancellation"
3. Verify booking status changes to `cancellation_requested`
4. Verify customer and admin receive notifications
5. Verify admin can process refund via existing refund queue
