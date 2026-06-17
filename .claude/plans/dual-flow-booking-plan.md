# Dual-Flow Booking System: Packages (Auto-Confirm) vs Custom Services (Supplier-First)

## Context

Golden Promise has two booking models:
- **Packages** — pre-set offerings with fixed price, date, scope. Supplier already said "yes" by setting availability. Auto-confirm on payment.
- **Custom Services** — tailored requests. Supplier must review and accept/decline BEFORE the customer pays. Customer only pays after supplier confirms.

Currently everything uses a single "pay first, supplier responds after" flow. This needs to split into two distinct paths based on what the customer is booking.

---

## Key Facts From Codebase

- `booking_items.item_type` already has `'service'`, `'package'`, `'supplier_package'`
- `cart_items.source` already has `'package'` vs `'custom'` — but `booking_items` has no `source` column yet
- `supplierRespond()` in `Booking.php` gates on `isPaymentVerified()` — must be loosened for custom services
- No `autoConfirmAllSuppliers()` method exists — needs to be created
- Supplier action buttons in `bookingDetail.php` show only when `$supplierStatus === 'pending'`
- Notifications: `notifyBookingSuppliers()` and `notifyBookingCustomer()` already exist in `Notification.php`

---

## Two Flows Side by Side

### Flow A: Package Booking (Auto-Confirm)
```
Customer books a package (dates already set by supplier)
         ↓
Booking created → status: 'pending_payment'
         ↓
Customer pays 10% deposit (MM QR / Visa Card)
         ↓
2C2P webhook → payment verified
         ↓
System auto-confirms all suppliers (booking_suppliers.status = 'confirmed')
         ↓
Booking status → 'confirmed'
         ↓
No supplier action needed
```

### Flow B: Custom Service Booking (Supplier-First)
```
Customer submits custom booking request (no payment yet)
         ↓
Booking created → status: 'pending_supplier_response' (NEW STATUS)
         ↓
Supplier notified → sees booking in dashboard
         ↓
Supplier: Accept or Decline
         ↓               ↓
    Accepted          Declined
         ↓               ↓
Customer notified   Customer notified
to pay 10%          (booking cancelled)
         ↓
Customer pays (MM QR / Visa Card)
         ↓
Booking status → 'confirmed'
```

---

## Implementation Plan

### Step 1: Database Schema Changes

**File:** `database/migrations/2026_03_dual_flow_booking.sql`

```sql
-- 1. Add source column to booking_items (mirrors cart_items.source)
ALTER TABLE booking_items
ADD COLUMN source enum('package','custom') NOT NULL DEFAULT 'custom'
AFTER item_type;

-- 2. Add new status to bookings table for supplier-first flow
ALTER TABLE bookings
MODIFY COLUMN status enum(
  'draft',
  'pending_supplier_response',   -- NEW: custom service awaiting supplier
  'pending_payment',
  'payment_submitted',
  'payment_verified',
  'suppliers_responding',
  'confirmed',
  'pending_final_payment',
  'finalized',
  'completed',
  'cancelled'
) NOT NULL DEFAULT 'draft';

-- 3. Add supplier_response_deadline for auto-expiry (48hr window)
ALTER TABLE bookings
ADD COLUMN supplier_response_deadline timestamp NULL DEFAULT NULL
AFTER status;
```

---

### Step 2: BookingModel.php — New Methods

**File:** `app/models/BookingModel.php`

**Add `autoConfirmAllSuppliers(int $bookingId): bool`**
- Sets all `booking_suppliers.status = 'confirmed'` for this booking
- Sets `confirmed_at = NOW()`
- Sets all `booking_items.status = 'accepted'`
- Then sets booking status to `'confirmed'`

**Add `isPackageBooking(int $bookingId): bool`**
- Returns true if ALL `booking_items.source = 'package'` for this booking
- Returns false if ANY item is custom
- Used to decide which flow to trigger

**Add `isCustomServiceBooking(int $bookingId): bool`**
- Returns true if ANY `booking_items.source = 'custom'`
- Mixed bookings (package + custom) treated as custom (supplier approval needed)

**Mixed Booking Handling:**
- If booking has custom item(s): supplier must approve ENTIRE booking (both package + custom items)
- Payment only collected from customer after supplier accepts
- Eliminates partial payment confusion: either all items approved or booking cancelled

**Modify `createFromCart()`** (wherever booking_items are inserted from cart)
- Copy `source` from `cart_items.source` into `booking_items.source`

---

### Step 3: Booking.php Controller — Flow Split

**File:** `app/controllers/Booking.php`

**Modify `createPost()` or wherever booking is first created:**
```php
if ($bookingModel->isPackageBooking($bookingId)) {
    // Package: go straight to payment
    $bookingModel->updateStatus($bookingId, 'pending_payment');
    redirect('booking/payment/' . $bookingId);
} else {
    // Custom: wait for supplier response first
    $bookingModel->updateStatus($bookingId, 'pending_supplier_response');
    $bookingModel->setSupplierResponseDeadline($bookingId, '+48 hours');
    $notification->notifyBookingSuppliers($bookingId,
        'New booking request',
        'A customer is requesting your services. Please respond within 48 hours.',
        'booking_request', 'booking', $bookingId
    );
    redirect('booking/detail/' . $bookingId); // Show "awaiting supplier" page
}
```

**Modify `supplierRespond()` — Remove payment gate for custom services:**
```php
// Old: always requires payment_verified
// New: allow response if status is 'pending_supplier_response' (custom) OR payment_verified (package edge case)

$booking = $this->bookingModel->getById($bookingId);
$allowedStatuses = ['pending_supplier_response', 'payment_verified', 'suppliers_responding'];
if (!in_array($booking['status'], $allowedStatuses)) {
    return error('Cannot respond to this booking at this stage');
}
```

**After supplier accepts a custom service booking:**
```php
if ($booking['status'] === 'pending_supplier_response' && $action === 'accept') {
    $this->bookingModel->updateStatus($bookingId, 'pending_payment');
    $this->notification->notifyBookingCustomer($bookingId,
        'Supplier accepted your request',
        'Your booking request was accepted. Please complete your 10% deposit to confirm.',
        'booking_accepted', 'booking', $bookingId
    );
    // Return JSON with redirect to payment page
}

if ($action === 'decline') {
    $this->bookingModel->updateStatus($bookingId, 'cancelled');
    $this->notification->notifyBookingCustomer($bookingId,
        'Supplier declined your request',
        'Unfortunately the supplier is unavailable. You can search for another supplier.',
        'booking_declined', 'booking', $bookingId
    );
}
```

**Modify `confirmInstantPayment()` — Auto-confirm for package bookings after payment:**
```php
// After payment verified:
if ($this->bookingModel->isPackageBooking($bookingId)) {
    // Pure package booking: auto-confirm all suppliers
    $this->bookingModel->autoConfirmAllSuppliers($bookingId);
    // booking status set to 'confirmed' inside autoConfirmAllSuppliers()
} else if ($this->bookingModel->isCustomServiceBooking($bookingId)) {
    // Custom or mixed booking: supplier already accepted, payment is final step
    // Just mark payment status and confirm booking
    $this->bookingModel->updateStatus($bookingId, 'confirmed', 'partial');
    // All suppliers auto-confirmed when they accepted custom request earlier
}
```

**Payment Handling for Mixed Bookings:**
- No payment is collected until supplier accepts (all items wait together)
- When supplier accepts custom item(s), customer sees payment page
- Payment covers ENTIRE booking (all package items + custom items together)
- Cleaner: one approval gate, one payment gate, no partial states

---

### Step 4: Supplier View — bookingDetail.php

**File:** `app/views/supplier/bookingDetail.php`

**Modify action buttons condition:**
```php
// Currently: show buttons when $supplierStatus === 'pending'
// New: also check booking source to decide button labels

<?php if ($supplierStatus === 'pending'): ?>
  <?php if ($bookingSource === 'custom'): ?>
    // Show: "Accept Request" / "Decline Request"
    // Note: "Customer will be notified to pay after you accept"
  <?php else: ?>
    // Package bookings: no action buttons (auto-confirmed)
    // Show: "Package auto-confirmed on payment" badge
  <?php endif; ?>
<?php endif; ?>
```

---

### Step 5: Customer Booking Views

**File:** `app/views/booking/detail.php` (or equivalent)

Add status messages for new states:
- `pending_supplier_response` → Show: "Waiting for supplier to confirm your request (up to 48 hours)"
- `pending_payment` (after acceptance) → Show: "Supplier confirmed! Please pay your 10% deposit to lock in your booking"
- `cancelled` (after decline) → Show: "The supplier was unavailable. Please find another supplier."

---

### Step 6: 48-Hour Auto-Expiry (Optional but Recommended)

**File:** `app/controllers/Admin.php` — add to existing cron endpoint

```php
// In cronProcessPayouts() or a new cronExpireBookingRequests():
// Find bookings where:
//   status = 'pending_supplier_response'
//   supplier_response_deadline < NOW()
// → Set status = 'cancelled'
// → Notify customer: "No supplier responded. Please try again."
```

---

## Files to Modify

| File | Change |
|------|--------|
| `database/migrations/2026_03_dual_flow_booking.sql` | CREATE — schema changes |
| `app/models/BookingModel.php` | Add `autoConfirmAllSuppliers()`, `isPackageBooking()`, `isCustomServiceBooking()`, copy `source` when inserting booking_items |
| `app/controllers/Booking.php` | Split flow in `createPost()`, modify `supplierRespond()` and `confirmInstantPayment()` |
| `app/views/supplier/bookingDetail.php` | Hide auto-confirm badge for packages, show accept/decline only for custom |
| `app/views/booking/detail.php` | Add status messages for new states |
| `app/controllers/Admin.php` | Add cron for 48hr auto-expiry |

---

## Verification

1. **Package flow**: Book a package → pay → check `booking_suppliers.status = 'confirmed'` and booking status = `'confirmed'` automatically
2. **Custom service flow**: Book a service → status shows `pending_supplier_response` → supplier accepts → customer gets notified → customer pays → booking confirmed
3. **Decline flow**: Supplier declines → booking cancelled → customer notified
4. **48hr expiry**: Set deadline in past, run cron → booking auto-cancelled
5. **Mixed booking** (package + custom item): Treated as custom (supplier must accept)
