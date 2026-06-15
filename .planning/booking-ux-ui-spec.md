# Booking Feature — UX/UI Specification

> Design language, layout, interactions, and states for all booking-related pages.
> References two existing design systems:
> - **Customer (wedding)**: warm, serif-forward, ambient — used in `cart/index.php`
> - **Dashboard (supplier/admin)**: utilitarian, Tailwind utility classes, Lucide icons — used in `supplier/*` and `admin/*`

---

## Design System Reference

### Customer-Facing (Wedding) Tokens

| Token | Value |
|---|---|
| Font headings | `Playfair Display` (serif) |
| Font body | `Poppins` (sans-serif) |
| Background | `#f2e4d4` (warm beige) |
| Card | `#ffffff` |
| Primary | `#6b4459` (plum) |
| Accent | `#c27a8e` (rose) |
| Gold | `#b8924a` |
| Danger | `#b94b4b` |
| Border radius | `--r-lg: 20px`, `--r-md: 14px` |
| Key pattern | Staggered fade-up animations, ambient orbs, sticky sidebar |

### Dashboard (Supplier / Admin) Tokens

| Token | Value |
|---|---|
| Framework | Tailwind v4 with CSS variables (`--color-app-*`) |
| Icons | Lucide (`<i data-lucide="...">`) |
| Cards | `rounded-card border border-app-border bg-app-input shadow-sm` |
| Table headers | `text-[10px] font-semibold uppercase tracking-widest text-app-muted px-5 py-2.5 text-left` |
| Status badges | `inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide` |
| Primary action | `bg-app-primary text-app-white shadow-sm` |
| Danger action | `bg-app-danger-soft text-app-danger` |
| Navigation pattern | Sidebar (left), breadcrumbs + search top bar |

---

## Page 1: Step 1 — Confirm Booking

**Route:** `/booking/create`  
**Design system:** Wedding (matching cart page)  
**Target audience:** Customer (couple)

### Layout

Two-column grid matching the cart page:
- **Left (flexible)**: Form — service review, customer details, notes
- **Right (360px sticky)**: Order summary sidebar (reused from cart)

### Top Section — Page Header

```
[Eyebrow: "Almost There"]
[Title: Confirm Your <em>Booking</em>]
[Subtitle: "Review your selections and add any details your suppliers need."]
```

### Left Column — Service Items

Each cart item renders as an expanded card with:

```
┌─────────────────────────────────────────────────────────────┐
│  [110px thumb]  Service Name                  RM 12,000     │
│                  Supplier Name                  est. price   │
│                                                        │
│  ┌─ Details ──────────────────────────────────────────┐  │
│  │  Event date:    📅 Saturday, 15 Nov 2026           │  │
│  │  Time slot:     🕐 2:00 PM — 6:00 PM               │  │
│  │  Duration:      Full day / 4 hours / Flexible    │  │
│  └─────────────────────────────────────────────────────┘  │
│                                                             │
│  ┌─ Your Notes ──────────────────────────────────────────┐  │
│  │  [textarea: Special requests, preferences...]        │  │
│  │  [e.g. "Bride is allergic to lilies, prefer soft     │  │
│  │   pink tones, need setup by 10 AM"]                  │  │
│  └─────────────────────────────────────────────────────┘  │
│                                                             │
│  Guests: [─] 120 [+]    ●  Phone: [+60 12-345 6789]        │
│  Event venue: [_________________________________]          │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

**Fields per item:**
- **Event date** — pre-filled from cart, editable dropdown/datepicker
- **Start / end time** — pre-filled from cart, editable
- **Guest count** — number stepper, default 0 (optional per service type)
- **Notes** — textarea, placeholder: "Share any special requests or preferences…"
- **Contact phone** — pre-filled from user profile, editable
- **Event venue / location** — text input, optional

**Validation:**
- At least one item must have notes or guest count > 0? No — all fields optional
- Phone must be valid format on submission

### Right Column — Order Summary Sidebar

Reuse the same sticky sidebar from `cart/index.php` with one addition — a **per-item breakdown showing deposit vs balance**:

```
 ┌─────────────────────────────┐
 │  Order summary              │
 │  Your selection             │
 │  3 services selected        │
 │                             │
 │  Photography         RM 8,000 │
 │  Catering           RM 15,000 │
 │  Florist             RM 4,000 │
 │  ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─  │
 │  Subtotal            RM 27,000 │
 │  Deposit (10%)       RM 2,700 │
 │  Balance due        RM 24,300 │
 │                             │
 │  ┌───────────────────────┐  │
 │  │  Confirm & Proceed    │  │
 │  │  → to Payment         │  │
 │  └───────────────────────┘  │
 │                             │
 │  [Back to Cart]             │
 │                             │
 │  ✓ Secure payment via Stripe│
 │  ✓ 10% deposit locks your   │
 │    date, balance due later   │
 │  ✓ Free cancellation within │
 │    48 hours                 │
 └─────────────────────────────┘
```

**New element:** "Balance due" line showing remaining 90%.

### States

| State | Behaviour |
|---|---|
| **Loading** | Skeleton cards (pulsing grey blocks) for each item |
| **Empty cart** | Redirect to cart page (no business booking with empty cart) |
| **Validation error** | Inline red border on the offending field + message |
| **Submission** | Button shows spinner, all inputs disabled |
| **Network error** | Toast: "Something went wrong. Please try again." |

### Interactions

- Phone input accepts international format
- Guest count stepper: min 0, max 9999, step 1
- Notes textarea auto-grows with content
- "Confirm & Proceed" posts to `/booking/create` → creates draft booking → redirects to Step 2

---

## Page 2: Step 2 — Payment (Stripe)

**Route:** `/booking/pay/{bookingId}`  
**Design system:** Wedding (clean, minimal, trust-focused)  
**Target audience:** Customer

### Layout

Single column, centered, max-width ~540px — **no sidebar**. Minimal distractions.

```
┌──────────────────────────────────────┐
│                                      │
│  [Eyebrow: "Secure Checkout"]        │
│  [Title: Complete Your <em>Payment</em>]  │
│                                      │
│  ── Payment Summary ──               │
│                                      │
│  Booking #BK-20260614-001            │
│                                      │
│  Photography                RM 8,000 │
│  Catering                  RM 15,000 │
│  Florist                    RM 4,000 │
│  ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─ ─  │
│  Total                    RM 27,000  │
│  Today's deposit (10%)    RM 2,700   │
│  Balance due              RM 24,300  │
│                                      │
│  ── Payment Method ──                │
│                                      │
│  [💳 Credit / Debit Card]            │
│  ┌──────────────────────────────┐    │
│  │  Card number                │    │
│  │  [____ ____ ____ ____]      │    │
│  │                              │    │
│  │  Expiry         CVC          │    │
│  │  [MM / YY]      [___]       │    │
│  │                              │    │
│  │  Cardholder name             │    │
│  │  [__________________]       │    │
│  └──────────────────────────────┘    │
│                                      │
│  [Pay Deposit — RM 2,700]           │
│                                      │
│  🔒 Powered by Stripe               │
│  Your card is charged immediately.   │
│  The balance is not collected now.   │
│                                      │
│  [← Back to confirm]                 │
│                                      │
└──────────────────────────────────────┘
```

### Elements

- **Booking reference number** — generated on draft creation, shown prominently
- **Payment summary** — compact, shows total, deposit, and balance separately
- **Stripe card element** — embedded via Stripe.js `elements()` in an iframe-safe container
- **Pay button** — shows exact deposit amount
- **Trust footer** — Stripe badge, lock icon, reassurance text

### States

| State | Behaviour |
|---|---|
| **Initial** | Stripe card element loaded, button enabled |
| **Typing** | Real-time card validation (number format, expiry, CVC) |
| **Processing** | Button shows spinner + "Processing…", all fields disabled, overlay on card element |
| **Success** | Confetti animation → redirect to success page after 2s delay |
| **Declined** | Inline error below card element: "Your card was declined. Please try a different card." |
| **Network error** | Toast: "Connection error. Your card has not been charged. Please try again." |
| **Stripe error** | Red error text below card element with Stripe's error message |

### Interactions

- Stripe Payment Element renders natively; all validation is Stripe-side
- On success: Stripe webhook sets booking to `paid` → frontend polls `/booking/status/{id}` or listens for redirect
- Back button returns to Step 1 (confirm booking) without losing data

---

## Page 3: Booking Success / Confirmation

**Route:** `/booking/success/{bookingId}`  
**Design system:** Wedding (celebratory)  
**Target audience:** Customer

### Layout

Centered, single column, celebratory tone.

```
┌──────────────────────────────────────┐
│                                      │
│      ✨ (Animated sparkle icon)      │
│                                      │
│         Booking Confirmed!           │
│     Your wedding team is forming     │
│                                      │
│   Booking #BK-20260614-001           │
│                                      │
│   ┌─ What happens next? ──────────┐  │
│   │                                │  │
│   │  1. Your suppliers are         │  │
│   │     notified of your booking   │  │
│   │                                │  │
│   │  2. They'll accept within      │  │
│   │     48 hours                   │  │
│   │                                │  │
│   │  3. You'll receive a           │  │
│   │     confirmation email         │  │
│   │                                │  │
│   └────────────────────────────────┘  │
│                                      │
│   Service          Status   Voucher  │
│   Photography    ⏳ Pending    [View] │
│   Catering       ⏳ Pending    [View] │
│   Florist        ⏳ Pending    [View] │
│                                      │
│   [View My Bookings]   [Browse More]  │
│                                      │
└──────────────────────────────────────┘
```

### Elements

- **Animated success icon** — checkmark in a circle with a subtle scale + opacity animation
- **Booking reference** — large, copyable
- **Step timeline** — 3-step visual showing " suppliers notified → suppliers accept → confirmation email"
- **Item status table** — compact, shows each service with status badge and voucher link
- **Two CTAs** — primary: "View My Bookings", secondary: "Browse More Services"

### States

| State | Behaviour |
|---|---|
| **Loading** (polls for webhook) | Pulsing skeleton with "Confirming your payment…" |
| **Payment not yet confirmed** | Auto-polls every 3s; shows "Waiting for payment confirmation…" |
| **Confirmed** | Full success view with animation |
| **Failed** | Redirect to payment failure page with retry option |

---

## Page 4: Customer — My Bookings

**Route:** `/booking/myBookings`  
**Design system:** Wedding (matching cart, with table elements)  
**Target audience:** Customer

### Layout

Full-width page, single column, filterable list.

```
[Eyebrow: "Your Weddings"]
[Title: My <em>Bookings</em>]
[Subtitle: "Track the status of your wedding services."]

[All (5)] [Pending (2)] [Confirmed (2)] [Completed (1)] [Cancelled]

─────────────────────────────────────────────────────────

┌────────────────────────────────────────────────────────┐
│  📅 Photography — Saturday, 15 Nov 2026                │
│  Supplier: Golden Lens Photography                     │
│                                                         │
│  [Status: Pending]     [Paid: RM 2,700 / RM 27,000]    │
│                                                         │
│  [View Details]  [View Voucher]  [Request Cancellation] │
└────────────────────────────────────────────────────────┘

┌────────────────────────────────────────────────────────┐
│  🍽️ Catering — Saturday, 15 Nov 2026                   │
│  Supplier: Feast & Co.                                 │
│                                                         │
│  [Status: Confirmed ✓]  [Paid: RM 1,500 / RM 15,000]   │
│                                                         │
│  [View Details]  [View Voucher]  [Request Cancellation] │
└────────────────────────────────────────────────────────┘
```

### Elements

- **Filter tabs** — pill-style, horizontal scroll on mobile
- **Booking cards** — each card shows:
  - Icon/emoji based on category
  - Service name + date
  - Supplier name
  - **Status badge** with semantic color
  - **Payment progress** — "paid X / Y total"
  - Action buttons row
- **Empty state** — "No bookings yet. Browse our services to get started!" with CTA

### Status Badge Colors

| Status | Badge |
|---|---|
| `pending` | Amber/neutral — "Pending" |
| `paid` | Blue — "Paid" |
| `confirmed` | Green — "Confirmed ✓" |
| `in_progress` | Teal — "In Progress" |
| `completed` | Emerald — "Completed ✓✓" |
| `cancelled` | Red — "Cancelled ✕" |

### States

| State | Behaviour |
|---|---|
| **Loading** | Skeleton cards (3 shimmer blocks) |
| **Empty** | Illustration + "No bookings yet" |
| **Filter active** | Smooth transition, cards animate in/out |
| **Error loading** | "Could not load bookings. [Retry]" |

---

## Page 5: Customer — Booking Detail

**Route:** `/booking/detail/{bookingId}`  
**Design system:** Wedding (detail page)  
**Target audience:** Customer

### Layout

Two-column: timeline + details.

**Left — Status Timeline:**

```
Booking #BK-20260614-001

  ● Confirmed                  15 Jun, 2:30 PM
  │
  ○ Paid (deposit)             15 Jun, 2:28 PM
  │
  ○ Payment initiated          15 Jun, 2:27 PM
  │
  ○ Booking created             15 Jun, 2:25 PM

[Upcoming: Event — 15 Nov 2026]
```

**Right — Item Details:**

```
Service              Status     Amount
─────────────────────────────────────────
Photography      ✓ Confirmed    RM 8,000
  🕐 2:00 PM — 6:00 PM
  📝 "Bride is allergic to lilies..."
  📍 The Grand Ballroom

Catering         ⏳ Pending      RM 15,000
  🕐 12:00 PM — 4:00 PM
  👥 120 guests
  📍 The Grand Ballroom

─────────────────────────────────────────
Total                    RM 27,000
Deposit paid (10%)       RM 2,700
Balance due              RM 24,300
```

**Bottom Actions:**

```
[View All Vouchers]  [Contact Supplier]  [Request Cancellation]
```

### Elements

- **Status timeline** — vertical, connected, with timestamps, using coloured dots
  - Past events: solid filled circles
  - Current: pulsing circle
  - Future: hollow circles
- **Item cards** — expandable on mobile, each with service details
- **Supplier contact** — opens modal with supplier phone + email

### States

| State | Behaviour |
|---|---|
| **Loading** | Timeline skeleton |
| **Cancelled** | Timeline turns grey, cancellation reason shown |
| **No timeline** | Just service details — minimal layout |

---

## Page 6: Customer — My Vouchers

**Route:** `/booking/vouchers`  
**Design system:** Wedding (card-based, ticket-like design)  
**Target audience:** Customer

### Layout

Grid of voucher cards, 2–3 columns on desktop, 1 on mobile.

```
[Eyebrow: "Your Collection"]
[Title: My <em>Vouchers</em>]
[Subtitle: "Present these to your suppliers on the event day."]

[Active (3)]  [Used (1)]  [Expired (0)]

┌────────────────────┐  ┌────────────────────┐
│  GOLDEN PROMISE     │  │  GOLDEN PROMISE     │
│  VOUCHER            │  │  VOUCHER            │
│                     │  │                     │
│  Photography        │  │  Catering           │
│  Golden Lens Photo  │  │  Feast & Co.        │
│                     │  │                     │
│  15 Nov 2026        │  │  15 Nov 2026        │
│  2:00 PM — 6:00 PM  │  │  12:00 PM — 4:00 PM │
│                     │  │                     │
│  VCH-PHOTO-001      │  │  VCH-CATER-001      │
│                     │  │                     │
│  ● Active           │  │  ● Active           │
└────────────────────┘  └────────────────────┘
```

### Voucher Card Design

Each card has:
- **Header** — "GOLDEN PROMISE" branding + decorative line
- **Service name** — large, serif font
- **Supplier name** — smaller, muted
- **Date & time** — with calendar icon
- **Voucher number** — monospace, smaller, grey
- **Status badge** — coloured dot + label
- **Visual decoration** — dotted cut line at bottom (CSS border trick), subtle shadow

### States

| State | Behaviour |
|---|---|
| **Active** | Full colour card, green status |
| **Used** | Greyed out, "Used" stamp overlay, muted colours |
| **Expired** | Greyed out, "Expired" badge |
| **Empty** | "No vouchers yet — they appear here once your booking is confirmed." |
| **Loading** | Grid of skeleton cards |

### Print

Each voucher card has a "Print" button that opens a print-optimised view (clean whites, no nav, QR placeholder, large text).

---

## Page 7: Customer — Cancellation Request

**Route:** `/booking/cancel/{bookingId}`  
**Design system:** Wedding (modal or dedicated page)  
**Target audience:** Customer

### Layout

Centered card, warning tone. Can be a **modal overlay** on the booking detail page, or a standalone page.

```
┌──────────────────────────────────────┐
│                                      │
│  ⚠️  Cancel this booking?            │
│                                      │
│  You're about to request             │
│  cancellation for:                   │
│                                      │
│  Photography — 15 Nov 2026           │
│  Catering — 15 Nov 2026              │
│  Florist — 15 Nov 2026               │
│                                      │
│  Your deposit of RM 2,700 will       │
│  be refunded per our cancellation    │
│  policy (free within 48 hours).      │
│                                      │
│  Reason for cancellation:            │
│  [dropdown or textarea]              │
│  • Changed my mind                   │
│  • Found another supplier            │
│  • Wedding postponed / cancelled     │
│  • Other (please specify)            │
│                                      │
│  [Back]  [Request Cancellation]      │
│                                      │
└──────────────────────────────────────┘
```

### Elements

- **Warning header** — alert icon + "Cancel this booking?" title
- **Item list** — all services in this booking, read-only
- **Refund note** — describes the refund policy
- **Reason selector** — dropdown with common reasons + "Other" text field
- **Two buttons** — secondary "Back", danger "Request Cancellation"

### States

| State | Behaviour |
|---|---|
| **Initial** | Form ready |
| **Submitted** | Button → "Request Sent", success message shown |
| **Error** | "Could not submit your request. Please try again." |

### Post-submission

```
┌──────────────────────────────────────┐
│  ✅ Cancellation requested           │
│                                      │
│  Your request has been sent to       │
│  our team. We'll review it within    │
│  24 hours and process your refund.   │
│                                      │
│  [Back to My Bookings]               │
└──────────────────────────────────────┘
```

---

## Page 8: Supplier Booking Dashboard

**Route:** `/supplier/bookings`  
**Design system:** Supplier dashboard (Tailwind + Lucide)  
**Target audience:** Supplier

### Layout

Dashboard content page, using the standard supplier layout (sidebar, top bar, content area). Follows the pattern of `supplierDashboard.php`.

```
[Top bar: "Bookings" breadcrumb | search | notification bell]

── Filter tabs ──────────────────────────────────
[All (12)] [Pending (5)] [Confirmed (4)] [Completed (3)] [Cancelled]

── Stats row ────────────────────────────────────
┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐
│ Pending  │  │ Confirmed│  │ Completed│  │ Est.     │
│ 5        │  │ 4        │  │ 3        │  │ RM 48k   │
└──────────┘  └──────────┘  └──────────┘  └──────────┘

── Bookings table ───────────────────────────────
┌────────────────────────────────────────────────────────────┐
│ Customer       │ Service      │ Date       │ Status   │ ₿  │
├────────────────────────────────────────────────────────────┤
│ Priya & Raj    │ Photography  │ 15 Nov 26  │ Pending  │✓ ✕│
│ Sarah & Tom    │ Catering     │ 22 Nov 26  │Confirmed │ ✓ │
│ ...                                                       │
└────────────────────────────────────────────────────────────┘
```

### Table Columns

| Column | Content |
|---|---|
| **Customer** | Name + avatar initial |
| **Service** | Service name + category badge |
| **Date** | Formatted event date |
| **Amount** | Price formatted |
| **Status** | Coloured status badge |
| **Actions** | Accept / Decline icons (pending only) |

### Action Buttons

- **Pending status** → two buttons: green checkmark (Accept) + red X (Decline)
- **Confirmed** → single button: Mark Complete
- **Completed/Cancelled** → no actions, read-only

### Interactions

- Accept/Decline uses AJAX POST → row updates without page reload
- Decline triggers a confirmation modal: "Are you sure? This will notify the customer and release your slot."
- Row click → expands inline or navigates to booking detail

### Empty / States

| State | Behaviour |
|---|---|
| **No bookings** | "No bookings yet. Your services are listed and visible to customers." |
| **Loading** | Skeleton table rows |
| **Error** | Toast: "Could not load bookings" |

### Mobile

Table collapses to card list:
```
┌────────────────────────────────────┐
│  Priya & Raj                       │
│  Photography · 15 Nov 2026         │
│  Pending                RM 8,000   │
│  [Accept]  [Decline]               │
└────────────────────────────────────┘
```

---

## Page 9: Supplier — Booking Detail (within dashboard)

**Route:** `/supplier/bookingDetail/{bookingId}`  
**Design system:** Supplier dashboard  
**Target audience:** Supplier

### Layout

Detail page within the dashboard, showing full booking info.

```
[← Back to Bookings]  [Breadcrumb: Bookings > Detail]

── Header ───────────────────────────────────────
Booking #BK-20260614-001  |  Status: Pending  |  [Accept] [Decline]

── Customer Info ──            ── Service Info ──
Name: Priya & Raj              Service: Photography
Phone: +60 12-345 6789         Category: Photography
Email: priya@example.com       Duration: 4 hours
                               Guests: 120

── Event Details ──
Date: Saturday, 15 Nov 2026
Time: 2:00 PM — 6:00 PM
Venue: The Grand Ballroom, KL

── Special Requests ──
"Bride is allergic to lilies, prefer soft pink tones.
Need setup by 10 AM."

── Payment ──
Total: RM 8,000    Deposit paid: RM 800    Status: Partial

── Status History ──
15 Jun, 2:25 PM — Booking created
15 Jun, 2:27 PM — Payment initiated
15 Jun, 2:28 PM — Deposit paid
```

### Sections (responsive grid, 2 columns on desktop, 1 on mobile)

- **Customer Info** — name, phone, email
- **Service Info** — service name, category, duration, guest count
- **Event Details** — date, time, venue
- **Special Requests** — quoted text block
- **Payment** — compact card showing totals
- **Status History** — mini timeline, same design as customer booking detail

### Actions

- **Accept** — POST AJAX, updates status to `confirmed`, row turns green, shows "Confirmed" badge
- **Decline** — opens modal: "Reason for declining?" (optional textarea) → POST → status to `cancelled`

---

## Page 10: Admin Booking Management

**Route:** `/admin/bookings`  
**Design system:** Admin dashboard (Tailwind + Lucide, matching `admin_dashboard.php`)  
**Target audience:** Admin

### Layout

Standard admin dashboard page with sub-navigation.

```
[Subnav: Bookings > All bookings | Pending approval]

── Filters row ─────────────────────────────────
Status: [All ▼]     Date range: [From] [To]     Search: [________]

── Stats cards ─────────────────────────────────
┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐
│ Total    │  │ Active   │  │ Completed│  │ Cancelled│  │ Revenue  │
│ 128      │  │ 42       │  │ 68       │  │ 18       │  │ RM 1.2M  │
└──────────┘  └──────────┘  └──────────┘  └──────────┘  └──────────┘

── Bookings table ──────────────────────────────
┌──────────────────────────────────────────────────────────────────────────┐
│ ID        │ Customer    │ Supplier(s) │ Amount  │ Status     │ Date    │
├──────────────────────────────────────────────────────────────────────────┤
│ BK-001    │ Priya & Raj │ Golden Lens │ 27,000  │▶ Confirmed │15 Nov 26│
│ BK-002    │ Sarah & Tom │ Feast & Co. │ 15,000  │⏳ Paid     │22 Nov 26│
│ BK-003    │ ...         │ ...         │ ...     │            │ ...     │
└──────────────────────────────────────────────────────────────────────────┘

[Row actions: ⋮ → View | Cancel]
```

### Table Columns

| Column | Content |
|---|---|
| **ID** | Booking reference, linked to detail |
| **Customer** | Name |
| **Supplier(s)** | Comma-separated supplier names |
| **Amount** | Total formatted |
| **Status** | Status badge + coloured dot |
| **Created** | Date |
| **Actions** | Dropdown menu (View, Cancel) |

### Filters

- **Status dropdown** — All, Draft, Pending Payment, Paid, Confirmed, Completed, Cancelled
- **Date range** — two date inputs
- **Search** — text search across booking ID, customer name, supplier name

### Row Actions Dropdown

```
[ ⋮ ] → dropdown:
  View Details
  ─────────────
  Cancel Booking  (danger colour)
```

### Empty / States

| State | Behaviour |
|---|---|
| **No bookings** | "No bookings match your filters." + Clear filters button |
| **Loading** | Skeleton rows |
| **Error** | Inline error banner |

---

## Page 10b: Admin — Booking Detail

**Route:** `/admin/bookingDetail/{bookingId}`  
**Design system:** Admin dashboard  
**Target audience:** Admin

### Layout

Detail page, read-only access with action buttons.

```
[← Back to Bookings]

── Header ───────────────────────────────────────
#BK-20260614-001  |  Status: Confirmed  |  [Cancel Booking]

── Grid: 3 columns ─────────────────────────────

[CUSTOMER]          [SUPPLIERS]         [PAYMENT]
Name: Priya & Raj   Golden Lens Photo   Total:     RM 27,000
Phone: +60 12-...   Feast & Co.         Deposit:   RM 2,700
Email: priya@...                         Balance:  RM 24,300
                                         Status:   Partial

── Items table ─────────────────────────────────
Service         | Supplier    | Date       | Price   | Status
──────────────────────────────────────────────────────────
Photography     | Golden Lens | 15 Nov 26  | 8,000   | Confirmed
Catering        | Feast & Co. | 15 Nov 26  | 15,000  | Confirmed
Florist         | Bloom       | 15 Nov 26  | 4,000   | Confirmed

── Cancellation Modal ──────────────────────────
"Cancel this booking?"
Reason: [textarea: e.g. "Customer requested via email"]
Refund deposit? [Yes / No]
[Back]  [Confirm Cancellation]
```

### Actions

- **Cancel Booking** — opens modal with reason field + refund toggle
- **View Customer** — link to customer profile (future)
- **View Supplier** — link to supplier profile (future)

---

## Responsive Behaviour Summary

| Page | Desktop | Tablet | Mobile |
|---|---|---|---|
| Confirm Booking | 2-col grid | 2-col (narrower sidebar) | Stacked: form first, summary below |
| Payment | Centered 540px | Same | Full-width, tighter padding |
| Success | Centered | Same | Same, smaller icons |
| My Bookings | Full-width cards | Same | Cards full-width, tabs scroll |
| Booking Detail | 2-col timeline + items | Stacked | Fully stacked |
| My Vouchers | 3-col grid | 2-col grid | 1-col stack |
| Cancellation | Modal / centered page | Modal | Full-screen modal |
| Supplier Bookings | Full table | Table (responsive columns) | Card list |
| Supplier Detail | 2-col grid | 2-col | Stacked |
| Admin Bookings | Full table | Table scrolls horizontally | Card list |

---

## Shared Components (Build Once)

### Status Badge
```
<span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider
  <?php if ($status === 'confirmed'): ?>bg-green-100 text-green-800
  <?php elseif ($status === 'pending'): ?>bg-amber-100 text-amber-800
  <?php elseif ($status === 'cancelled'): ?>bg-red-100 text-red-800
  <?php elseif ($status === 'paid'): ?>bg-blue-100 text-blue-800
  <?php elseif ($status === 'completed'): ?>bg-emerald-100 text-emerald-800
  <?php endif; ?>">
  <?= $label ?>
</span>
```

### Payment Progress Bar
```
<div class="flex items-center gap-2 text-xs text-app-muted">
  <span>Paid: RM <?= number_format($paid) ?></span>
  <div class="h-1.5 flex-1 rounded-full bg-gray-200">
    <div class="h-full rounded-full bg-green-500" style="width: <?= $percent ?>%"></div>
  </div>
  <span class="font-medium text-app-text">RM <?= number_format($total) ?></span>
</div>
```

### Confirmation Modal
Reusable modal component for:
- Supplier Accept/Decline confirmation
- Admin Cancel confirmation
- Customer Request Cancellation confirmation

---

## Error & Empty State Patterns (All Pages)

### Toast Notification
```
Position: top-right, fixed z-50
Style: rounded-xl shadow-lg border px-4 py-3
Types: success (green), error (red), info (blue)
Auto-dismiss: 5 seconds
```

### Skeleton Loading
```
Pattern: shimmer animation (gradient sweep)
Cards: pulsing grey blocks matching card dimensions
Tables: pulsing rows, 5 rows
```

### Empty State
```
Centered illustration/icon
Headline: descriptive but concise
Subtitle: helpful next step
CTA button: primary action
```
