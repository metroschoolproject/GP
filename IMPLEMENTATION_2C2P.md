# 2C2P Payment Gateway Implementation Guide

## Overview

This document covers the complete implementation of 2C2P payment gateway integration for Golden Promise, replacing KBZ Pay and AYA Bank Transfer with MM QR and Visa Card payment methods.

## What Changed

### ✅ Phase 1: Removed KBZ Pay & AYA Bank (Completed)

**Deleted:**
- `app/controllers/Fakekbz.php` — Demo KBZ checkout controller
- `app/views/fakekbz/checkout.php` — Demo KBZ UI

**Updated:**
- `app/controllers/Payments.php` — Removed KBZ/AYA-specific methods
- `app/views/payments/supplier_fee.php` — Updated sidebar instructions
- `app/views/payments/_form.php` — Rewritten for 2C2P methods only
- `app/views/booking/paymentMethods.php` — Removed KBZ/AYA buttons

**Result:** Payment method selection now shows only:
- MM QR (Myanmar Instant QR Payment)
- Visa Card (Credit/Debit Card)

---

### ✅ Phase 2: 2C2P Gateway Integration (Completed)

**Created:**
- `app/controllers/Webhook.php` — Handles 2C2P payment callbacks

**Updated:**
- `app/controllers/Payments.php` — Now calls PaymentGatewayService
  - `supplierFee()` — Creates payment intent with 2C2P
  - `supplierFeeCallback()` — Handles payment completion
  - `mapPaymentMethod()` — Converts local codes to 2C2P codes
- `app/config/config.php` — Added 2C2P credentials placeholders

**Payment Flow:**
```
User selects MM QR or Visa Card
    ↓
System creates payment intent with 2C2P
    ↓
2C2P returns payment URL or QR code
    ↓
User redirected to 2C2P payment page
    ↓
User completes payment
    ↓
2C2P sends webhook callback (HMAC-SHA256 verified)
    ↓
Payment status updated in database
    ↓
Supplier account marked as "paid"
```

---

### ✅ Phase 3: Database Schema Updates (Completed)

**Migration:** `database/migrations/2026_01_remove_kbz_aya_add_2c2p.sql`

**Changes:**
- Added `gateway_reference` column for 2C2P transaction IDs
- Added `gateway_response` column for storing API responses
- Normalized existing payment method names to:
  - `'2c2p_mmqr'` — Myanmar QR
  - `'2c2p_card'` — Credit/Debit Card

**Run Migration:**
```bash
mysql -u root -p goldenpromise < database/migrations/2026_01_remove_kbz_aya_add_2c2p.sql
```

---

### ✅ Phase 4: Supplier Payouts via 2C2P (Completed)

**Migration:** `database/migrations/2026_02_add_bank_fields_to_suppliers.sql`

**Changes:**
- Added `bank_code` column (AYA, KBZ, AGD, etc.)
- Added `bank_account` column (supplier's bank account number)

**Updated:**
- `app/controllers/Admin.php` — `cronProcessPayouts()` now uses 2C2P
  - Fetches pending payouts
  - Validates supplier bank details
  - Calls 2C2P `createSupplierPayout()` API
  - Updates payment status based on result

**Payout Flow:**
```
Supplier fee payment succeeds
    ↓
Payout record created (status: 'pending')
    ↓
Daily cron job runs: /admin/cronProcessPayouts
    ↓
System calls 2C2P to disburse funds to supplier bank
    ↓
Payout status updated to 'processing' or 'failed'
    ↓
2C2P webhook confirms successful settlement
```

**Run Migration:**
```bash
mysql -u root -p goldenpromise < database/migrations/2026_02_add_bank_fields_to_suppliers.sql
```

---

## Setup Instructions

### Step 1: Get 2C2P Credentials

1. Visit https://www.2c2p.com/
2. Sign up as a merchant
3. Complete KYC verification
4. Access your merchant dashboard
5. Generate API credentials:
   - Merchant ID
   - API Key
   - API Secret

### Step 2: Configure Credentials

Edit `app/config/config.php`:

```php
define('PAYMENT_GATEWAY_SANDBOX', true);  // true for testing, false for live
define('PAYMENT_GATEWAY_API_KEY', 'your_2c2p_api_key');
define('PAYMENT_GATEWAY_SECRET', 'your_2c2p_api_secret');
define('MERCHANT_ID', 'your_2c2p_merchant_id');
```

### Step 3: Run Database Migrations

```bash
mysql -u root -p goldenpromise < database/migrations/2026_01_remove_kbz_aya_add_2c2p.sql
mysql -u root -p goldenpromise < database/migrations/2026_02_add_bank_fields_to_suppliers.sql
```

### Step 4: Configure Webhook Endpoint

In 2C2P dashboard, set webhook callback URL:
```
https://yourdomain.com/webhook/paymentGatewayCallback
```

2C2P will POST to this endpoint with payment status updates.

### Step 5: Test with Sandbox Credentials

**Test MM QR Payment:**
- 2C2P provides test QR codes in sandbox
- Scan with Myanmar QR app to complete test payment

**Test Visa Card Payment:**
- Use test card: `4111111111111111`
- Expiry: Any future date (e.g., 12/25)
- CVV: Any 3 digits (e.g., 123)

### Step 6: Switch to Production

Once testing is complete:
1. Update config.php:
   ```php
   define('PAYMENT_GATEWAY_SANDBOX', false);
   ```
2. Replace credentials with production values
3. Update webhook endpoint in 2C2P dashboard to production URL
4. Test with real payments
5. Monitor transactions in 2C2P dashboard

---

## Key Files

| File | Purpose |
|------|---------|
| `app/services/PaymentGatewayService.php` | 2C2P API abstraction layer |
| `app/controllers/Payments.php` | Payment intent creation and callbacks |
| `app/controllers/Webhook.php` | Webhook endpoint for 2C2P callbacks |
| `app/controllers/Admin.php` | Supplier payout processing |
| `app/config/config.php` | 2C2P credentials |

---

## Payment Methods

### MM QR (Myanmar QR)
- **Code:** `2c2p_mmqr`
- **Gateway Code:** `mm_qr`
- **How it works:** User scans QR code with Myanmar QR mobile app
- **Settlement:** Instant to merchant account
- **Currency:** MMK (no decimals)

### Visa Card
- **Code:** `2c2p_card`
- **Gateway Code:** `credit_card`
- **Supported cards:** Visa, Mastercard, international cards
- **How it works:** User enters card details on 2C2P payment page
- **Settlement:** Next business day
- **Currency:** MMK (2C2P handles conversion)

---

## API Integration Points

### Creating Payment Intent
```php
$gateway = new PaymentGatewayService();
$result = $gateway->createPaymentIntent(
    $bookingId,        // int
    $amount,            // float (150000 for 150,000 MMK)
    'mm_qr',            // or 'credit_card'
    $returnUrl          // string
);

if ($result['success']) {
    // Redirect to $result['payment_url']
    // or display $result['qr_code_url']
}
```

### Verifying Payment
```php
$result = $gateway->verifyTransaction($transactionId);

if ($result['success'] && $result['status'] === 'completed') {
    // Mark payment as successful
}
```

### Creating Supplier Payout
```php
$result = $gateway->createSupplierPayout(
    $supplierId,        // int
    $amount,            // float
    $bankAccount,       // string
    'AYA'               // bank code
);

if ($result['success']) {
    // Payout initiated, status: 'pending' or 'processing'
}
```

### Webhook Validation
```php
$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_2C2P_SIGNATURE'] ?? '';

if ($gateway->validateWebhookSignature($payload, $signature)) {
    // Process webhook safely
}
```

---

## Testing Checklist

- [ ] Supplier registration with MM QR payment
- [ ] Supplier registration with Visa Card payment
- [ ] Customer booking with MM QR payment
- [ ] Customer booking with Visa Card payment
- [ ] Webhook callback processing (status update)
- [ ] Supplier payout processing via cron
- [ ] Payment slip upload removed
- [ ] Old KBZ/AYA options not visible in UI
- [ ] Admin dashboard shows correct payment methods
- [ ] Payment history shows `2c2p_mmqr` and `2c2p_card`
- [ ] HMAC signature validation working
- [ ] Sandbox credentials verified
- [ ] Production credentials tested

---

## Troubleshooting

### "Payment gateway unavailable"
- Check 2C2P credentials in config.php
- Verify API URL is correct (sandbox vs production)
- Check network connectivity to 2C2P API
- Review 2C2P API logs for errors

### Webhook not received
- Verify webhook URL is publicly accessible
- Check 2C2P dashboard for webhook delivery logs
- Ensure HMAC signature validation is not rejecting valid signatures
- Check server logs for webhook processing errors

### Payout failed
- Verify supplier has bank_account set in database
- Check bank_code is valid (AYA, KBZ, AGD, etc.)
- Confirm supplier account is in good standing
- Review 2C2P API error response for specifics

### Currency/Amount issues
- Ensure amount has no decimals for MMK (use `(int)round($amount)`)
- Verify amount is in Kyats (not cents)
- Check 2C2P dashboard for currency settings

---

## Security Notes

1. **Never commit credentials** — Use environment variables or .env file
2. **HMAC validation** — All webhooks are cryptographically signed with HMAC-SHA256
3. **SSL verification** — Enabled in production, disabled in sandbox
4. **Token rotation** — Implement periodic credential rotation in 2C2P dashboard
5. **Audit logs** — Monitor /admin/payments for suspicious activity

---

## Next Steps (Future Enhancements)

1. **Customer Booking Payments** — Extend 2C2P integration to customer bookings
2. **Refund Processing** — Implement `requestRefund()` for booking cancellations
3. **Reconciliation** — Add `getTransactionHistory()` for financial reports
4. **Bank Verification** — Add bank account validation during supplier onboarding
5. **Multi-currency** — Support other currencies if expanding to other countries
6. **Receipt Generation** — Auto-generate payment receipts for customers
7. **Payment Reminders** — Send email reminders for pending payments
8. **Analytics Dashboard** — Show payment trends and conversion rates

---

## Support

For 2C2P API documentation: https://developer.2c2p.com/

For Golden Promise issues: https://github.com/metroschoolproject/gp/issues

---

**Last Updated:** 2026-06-16  
**Implementation Status:** ✅ Complete (Phases 1-4)  
**Branch:** `claude/eager-einstein-piizkb`
