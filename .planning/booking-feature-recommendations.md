# Booking Feature — Design Recommendations

> Based on codebase analysis of existing cart, payment, availability, and database schema.
> Date: 2026-06-14

---

## Current State

| Piece | Status |
|---|---|
| `carts` / `cart_items` tables | ✅ Built |
| `Cart.php` controller (CRUD) | ✅ Built |
| `CartModel.php` | ✅ Built |
| Cart UI (index view) | ✅ Built — polished, with sticky summary & "Proceed to Booking" button |
| `bookings` / `booking_items` / `booking_suppliers` / `booking_vouchers` / `booking_status_logs` tables | ✅ Schema exists in database dump |
| Supplier slot management (reserve/release) | ✅ Partial — methods exist in `SupplierServiceManager` |
| `BookingController` / `BookingModel` | ❌ Does not exist |
| Customer checkout UI | ❌ Route `booking/create` is a dead link |
| Payment integration for bookings | ❌ `Payments.php` only handles supplier registration fees |
| Customer booking history | ❌ Not built |
| Admin booking management | ❌ Not built |
| Supplier booking dashboard | ❌ Not built |
| Voucher generation | ❌ Table exists, no code writes to it |

---

## 1. Booking Flow: Multi-step Checkout

**Recommended: 2-step flow (Confirm Details → Pay)**

1. **Step 1 — Confirm Booking** — review cart items, select/adjust dates, add notes, guest count. Looks right? Proceed.
2. **Step 2 — Payment** — Stripe payment form for the 10% deposit.

**Why:**
- Wedding services are high-consideration purchases (often RM 1k–20k+). A single-page dump creates anxiety.
- Two steps separate "did I get the details right?" from "am I paying right now?" — familiar, trust-building.
- Matches how real wedding vendors work: confirm what you want → put down a deposit.

---

## 2. Deposit vs Full Payment

**Recommended: 10% deposit to confirm the booking, balance due later**

**Why:**
- This is the **universal wedding industry standard**. No couple pays 100% upfront for a vendor 6 months out.
- The cart UI already displays "Deposit (10%)" — the design intent is established.
- Win-win: customer commits smaller amount; supplier gets a financial commitment before reserving their date.
- For MVP: collect the deposit via Stripe, store `paid_amount = deposit`, set `payment_status = 'partial'`. The balance is tracked but not automatically collected. Scheduled balance collection is a post-MVP feature.

**Database mapping:**
| Column | Value |
|---|---|
| `bookings.total_amount` | Full price of all items |
| `bookings.paid_amount` | 10% of total (deposit) |
| `bookings.payment_status` | `'partial'` |

---

## 3. Payment Gateway

**Recommended: Stripe only for MVP**

- Stripe is already a dependency (`stripe/stripe-php ^14.8`) — zero new setup.
- `Fakekbz.php` (KBZ Pay demo) stays wired **only** to supplier registration fee payments. It's a demo simulator, not a real gateway.
- Stripe gives us **webhooks** — critical for reliably confirming payment before releasing supplier slots.
- Adding additional gateways (real KBZ Pay, FPX, etc.) later is straightforward — the `Payment.php` model already has the patterns.

---

## 4. When Supplier Slots Lock

**Recommended: Lock slots only after successful payment is confirmed**

| Strategy | Problem |
|---|---|
| Lock on "add to cart" | Abandoned carts hold slots hostage. Bad for suppliers. |
| Lock on "booking created" | 15-min holds don't work for wedding booking cycles (months ahead). 24h+ holds block other customers. |
| **Lock on payment confirmed** | **Cleanest. Customer knows "I pay → I get the date." Supplier knows "payment received → date is gone."** |

The existing `SupplierServiceManager.reserveServiceSlot()` / `releaseServiceSlot()` methods already exist — wire them into the payment confirmation callback.

---

## 5. Customer Info During Checkout

**Recommended: Collect only what the supplier needs to deliver the service**

| Field | Why |
|---|---|
| Special requests / notes | Weddings are deeply personal — couples need to communicate preferences |
| Guest count | Essential for catering, venue, photography (group shots), etc. |
| Contact phone | Suppliers need to reach the customer day-of; email alone isn't enough |
| Event venue / location | Florists, makeup artists, photographers need to know where to arrive |

Don't re-ask for dates/times already selected in the cart — surface them for confirmation.

---

## 6. Booking Status Lifecycle

**Recommended: Automated flow — skip admin approval for individual bookings**

```
Payment succeeds
    ↓  (automatic, via Stripe webhook)
status = 'paid'
    ↓
Supplier notified → clicks "Accept"
    ↓
status = 'confirmed'
    ↓
Supplier marks complete after event
    ↓
status = 'completed'
```

### Transition logic

| From | To | Trigger |
|---|---|---|
| `draft` | `pending_payment` | Customer clicks "Proceed to Pay" |
| `pending_payment` | `paid` | Stripe webhook confirms successful payment |
| `paid` | `confirmed` | Supplier clicks "Accept" |
| `paid` | `cancelled` | Supplier clicks "Decline" (auto-refund + slot release) |
| `confirmed` | `completed` | Supplier marks service as delivered |
| `in_progress` | `completed` | Supplier marks complete |
| *any* | `cancelled` | Admin action (manual refund) |

### Why skip admin approval?

- Admin already approves **services** before they go live (publish workflow) — that's the quality gate.
- Reviewing every individual booking creates a bottleneck for suppliers and a drowning workload for admins.
- The `booking_suppliers` table already has `status` with values `pending` / `confirmed` / etc. — intended for supplier-level management, not admin gates.

---

## 7. Supplier Booking Dashboard

**Recommended: MVP-essential — minimal incoming bookings view**

Minimum scope:
- New "Bookings" tab in supplier dashboard
- Table columns: customer name, service name, date, price, status
- Action buttons: **Accept** / **Decline**
- Decline auto-releases the supplier's slot and triggers a customer notification (and refund if paid)

**Why it's critical:**
- A marketplace where suppliers can't see their bookings is broken — they can't manage their schedule or plan work.
- `SupplierServiceManager` already has calendar/booking query methods — half the work exists.
- The supplier dashboard already exists; this slots in as a new tab following the existing pattern.

---

## 8. Vouchers

**Recommended: Database records only for MVP — no PDF generation**

- The `booking_vouchers` table exists and is ready to write to.
- Generate one voucher record per `booking_items` row **after successful payment**.
- Display vouchers in a "My Vouchers" page — card-style layout with service name, date, voucher number.
- Add PDF download post-MVP when it becomes a real request (requires a PDF library).

---

## 9. Cancellation

**Recommended: Manual (admin-processed) for MVP**

1. Customer clicks "Request Cancellation" on their booking page
2. Admin receives notification
3. Admin reviews and clicks "Cancel" in the admin panel
4. System auto-releases supplier slots
5. Admin processes refund manually via Stripe dashboard

**Why:**
- Time-based automatic refund logic ("free within 48 hours") is complex — exact timestamps, partial Stripe refunds, multi-party notifications.
- The cart UI says "Free cancellation within 48 hours" — the policy exists, but automated enforcement can be Phase 2.
- Manual handling lets you launch without building a full refund engine. The policy is still honored — just handled by a human.

---

## 10. Admin Booking Management

**Recommended: Minimal — list + cancel**

Minimum scope:
- New "Bookings" tab in admin dashboard
- Table: Booking ID, customer name, total, status, date, supplier(s)
- Filters: by status, by date range
- Action: Cancel (with refund note field)
- (Optional MVP stretch) Booking detail page showing line items, payment info, status history log

**Why minimal:**
- Admin already has a dashboard pattern — this follows it.
- For MVP, admin just needs visibility and the ability to handle the occasional cancellation.
- Detail views are nice-to-have but not launch-blocking.

---

## Implementation Priority

| Priority | Feature | Depends On | Effort |
|---|---|---|---|
| **P1** | `BookingController` + `BookingModel` + checkout views | — | Large |
| **P1** | Stripe payment integration for booking deposits | BookingModel | Medium |
| **P1** | Supplier booking dashboard (list + accept/decline) | BookingModel | Medium |
| **P2** | "My Bookings" customer page | BookingModel | Medium |
| **P2** | Voucher record generation on successful payment | BookingModel + payments | Small |
| **P3** | Admin booking list | BookingModel | Small |
| **P3** | Automated cancellation with time windows | Everything above | Medium |
| **P4** | PDF vouchers | Voucher records | Large |
| **P4** | Supplier payout tracking & automatic balance collection | Payments + Stripe | Large |

---

## Database Tables Already Ready

All schemas exist in `database/goldenpromise (4).sql`:

| Table | Purpose |
|---|---|
| `bookings` | Master booking record — total, payment_status, status lifecycle, approval tracking |
| `booking_items` | Line items per booking — links to services/packages, date, time, slot, venue room |
| `booking_suppliers` | Supplier-level status per booking — confirmation, completion, payout tracking |
| `booking_status_logs` | Audit trail of all status changes |
| `booking_vouchers` | Voucher records — one per booking item after payment |

No schema changes needed to start building.
