# Flexible Remaining Balance Payments

## Problem
After a customer pays the deposit, they must pay the **full remaining balance** in a single payment. The customer wants to pay any amount they choose, whenever they want, before the event date (due date).

## Current Behavior
- `submitRemainingPayment()` validates `paid_amount == exact remaining balance` (line 978 of Booking.php)
- "Pay Remaining Balance" button only shows for `confirmed`, `pending_final_payment`, `finalized` statuses
- `adminVerifyRemainingPayment()` finalizes the booking after a single remaining payment
- No due date concept — event date is only used in reminder emails

## Changes

### 1. Controller — `app/controllers/Booking.php`

**`payRemaining()` (line 900):**
- Pass `$eventDate` (earliest event date) to the view as the due date
- Pass `$remainingPayments` (list of successful + pending remaining payments) for history display
- Remove the restriction that blocks access when a pending remaining payment exists — instead show the pending status but still allow viewing the page

**`submitRemainingPayment()` (line 937):**
- Change validation: accept any amount between `1000 MMK` (minimum) and the current remaining balance
- Keep all other validations (bank name, account name, transaction ref, slip upload)

**`detail()` (line 1336):**
- Pass `$eventDate` and `$remainingPayments` to the view
- No other changes needed — the view handles display logic

### 2. Model — `app/models/BookingModel.php`

**`submitRemainingPaymentSlip()` (line 4445):**
- When creating the payment record, keep `type = 'remaining'`
- Only update booking status to `pending_final_payment` if not already in that status
- For partial payments: leave `payment_status` as `partial` (don't change to `full`)

**`adminVerifyRemainingPayment()` (line 4243):**
- After verifying a remaining payment, calculate total paid from all successful payments
- If `total_paid >= total_amount`: finalize booking (`status = 'finalized'`, `payment_status = 'full'`, `paid_amount = total_amount`)
- If `total_paid < total_amount`: just update `paid_amount` to the new total, keep `status = 'confirmed'`, keep `payment_status = 'partial'`
- Remove the validation that requires submitted amount to equal the full remaining balance — instead validate it's between minimum and remaining balance

**New method `getRemainingPayments(int $bookingId): array`:**
- Returns all `type = 'remaining'` payments for a booking, ordered by created_at ASC
- Used by controller and view to show payment history

**New method `getFirstEventDate(int $bookingId): ?string`:**
- Returns the earliest `event_date` from `event_details` for a booking
- Already exists as inline query in `daysUntilFirstEvent()` — extract to reusable method

### 3. View — `app/views/booking/detail.php`

**Remaining balance section (lines 328-334, 692-696):**
- Expand `$isConfirmedOrLater` to also include `paid` and `payment_verified` statuses so the button shows right after deposit verification
- Show due date (event date) prominently
- Show payment history: list of remaining payments made so far (amount, status, date)
- Show remaining balance amount
- If a pending remaining payment exists, show "Under Review" badge but also show the remaining balance and due date

**Summary card (lines 563-578):**
- Add a "Remaining balance" row that's more prominent
- Add due date display

### 4. View — `app/views/booking/payRemaining.php`

**Amount field (line 127-129):**
- Change from readonly to editable input
- Show remaining balance as placeholder/helper text
- Add min (1000 MMK) and max (remaining balance) validation via JS
- Add a "Pay full balance" quick-fill button

**Summary card (lines 92-97):**
- Add due date display
- Add payment history section showing previous remaining payments
- Show remaining balance after this payment

### 5. Admin — `app/controllers/Admin.php` (if needed)

**Verify remaining payment flow:**
- The `adminVerifyRemainingPayment()` model method handles the logic change
- Admin UI may need to show that this is a partial payment vs full payment
- No structural changes to admin flow needed — the model handles it

## Due Date Logic
- Use the earliest `event_date` from `event_details` as the due date
- If no event date is set, show "No due date set" (no restriction)
- No additional `balance_due_date` column needed — event date is the natural deadline

## Minimum Payment
- 1000 MMK minimum per partial payment (configurable constant)
- Define `MIN_REMAINING_PAYMENT` in `config.php`

## Files to Modify
1. `app/config/config.php` — add `MIN_REMAINING_PAYMENT` constant
2. `app/controllers/Booking.php` — `payRemaining()`, `submitRemainingPayment()`, `detail()`
3. `app/models/BookingModel.php` — `submitRemainingPaymentSlip()`, `adminVerifyRemainingPayment()`, add `getRemainingPayments()`, add `getFirstEventDate()`
4. `app/views/booking/detail.php` — remaining balance section, summary card
5. `app/views/booking/payRemaining.php` — editable amount, due date, payment history

## Verification
- Create a booking with deposit paid (status `confirmed` or `paid`)
- Verify "Pay Remaining Balance" button appears on detail page
- Make a partial payment (e.g., 50% of balance) — verify it submits correctly
- Admin verifies the partial payment — verify booking stays `confirmed` with `payment_status = 'partial'`
- Make another partial payment for the rest — verify it submits
- Admin verifies — verify booking finalizes to `finalized` with `payment_status = 'full'`
- Verify due date shows correctly on both detail and payment pages
- Verify payment history shows all partial payments
