# Plan: Supplier Payment Confidence Strip

## Context
When a booking is confirmed, the supplier has no visibility into whether the customer has paid, how much has been collected, or when they'll receive their payout. All payment info is buried inside a collapsed "View full booking details" section. Since payments are managed by admin (Golden Promise acts as escrow), the supplier needs clear signals that their money is safe and a payout process exists.

## Changes

### 1. Controller: Fetch payment history (`app/controllers/Booking.php`)
In `supplierBookingDetail()`, add a call to `$this->bookingModel->getBookingPayments($bookingId)` and pass the result as `paymentHistory` to the view. This gives us the actual deposit payment date for the timeline.

### 2. View: Add payment confidence strip (`app/views/supplier/bookingDetail.php`)
Insert a new section between the assignment card (`sup-assignment`) and the collapsible "View full booking details" (`sup-booking-details`). The strip shows:

- **Header**: Shield icon + "Payment managed by Golden Promise"
- **Three stat columns**: Your earnings / Customer has paid / Remaining
- **Progress bar**: visual deposit percentage
- **Timeline** (3 steps):
  - Deposit collected — shows date from payment history, green checkmark if paid
  - Event date — shows event date with "final payment due"
  - Payout to you — shows "within 7 days after event completion"
- **Trust note**: "Golden Promise collects all payments from the customer and releases your earnings after the event is completed."

Uses existing CSS variables and design tokens (`sup-*`). New CSS scoped to `.sup-payment-confidence-*` classes.

### Data used (all already available except paymentHistory)
- `$supplierTotal`, `$supplierPaid`, `$supplierRemaining` — computed in view
- `$paidFraction` — computed in view
- `$depositPercent` — from `BOOKING_DEPOSIT_PERCENT` (20%)
- `$booking['payment_status']` — from bookings table
- `$firstDate` — event date
- **NEW**: `$paymentHistory` — from `getBookingPayments()`

## Files touched
- `app/controllers/Booking.php` — 2 lines added
- `app/views/supplier/bookingDetail.php` — CSS + HTML section added
