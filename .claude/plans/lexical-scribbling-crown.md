# Cookie Features Implementation Plan

## Context
The Golden Promise project currently has minimal cookie usage — just the "Remember Me" login cookie and Google Analytics (which fires without consent). The user wants three cookie features:
1. **Cookie consent banner** (GDPR compliance)
2. **Recently viewed services** (personalization)
3. **Guest cart persistence** (conversion optimization)

---

## 1. Cookie Consent Banner

### What
A banner that appears on first visit asking users to accept/reject non-essential cookies. Google Analytics only fires after consent is given.

### Files to create/modify
- **Create** `app/views/partials/cookie-consent.php` — the banner HTML + CSS + JS
- **Modify** `app/views/partials/ga-tracking.php` — wrap GA script in a consent check
- **Modify** key pages to include the banner (same pages that have GA tracking)

### How it works
- Banner shows on first visit (no `cookie_consent` cookie set)
- User clicks "Accept All" → sets `cookie_consent=accepted` cookie (1 year), GA starts tracking
- User clicks "Reject" → sets `cookie_consent=rejected` cookie (1 year), GA stays disabled
- JS checks consent status before loading GA
- Banner matches the wedding theme (warm cream/gold colors, Playfair Display font)

### Cookie details
- Name: `cookie_consent`
- Values: `accepted` or `rejected`
- Lifetime: 365 days
- Path: `/`

---

## 2. Recently Viewed Services

### What
When a user views a service detail page, store the service ID in a cookie. Show "Recently Viewed" section on the homepage and service listing.

### Files to create/modify
- **Create** `app/helpers/recentlyviewed.php` — helper functions to read/write the cookie
- **Modify** `app/views/main/_service_detail_template.php` — add service ID to cookie on page load
- **Modify** `app/views/main/index.php` — show recently viewed section
- **Modify** `app/views/main/service.php` — show recently viewed section

### How it works
- Cookie stores up to 10 service IDs as JSON array: `[12, 45, 3, 67, ...]`
- When a service detail page loads, prepend the service ID to the array (dedup, cap at 10)
- On homepage/service listing, read the cookie, fetch service details from DB, display a "Recently Viewed" carousel
- Only stores service IDs (minimal data), fetches full details from DB on display

### Cookie details
- Name: `gp_recently_viewed`
- Value: JSON array of service IDs, e.g. `[12,45,3]`
- Max items: 10
- Lifetime: 30 days
- Path: `/`

---

## 3. Guest Cart Persistence

### What
When a non-logged-in user adds items to cart, persist them in a cookie (not just session) so they survive browser restarts.

### Files to create/modify
- **Modify** `app/controllers/Cart.php` — on `add()`/`addPackage()` for guests, save to cookie; on `index()`, restore from cookie if session is empty
- **Modify** `app/views/cart/index.php` — no changes needed (data flows through controller)

### How it works
- Currently: guest cart items go to `$_SESSION['cart_pending']` (lost on browser close)
- New: also write the same data to a `gp_guest_cart` cookie
- On page load: if no session cart but cookie exists, restore from cookie
- On login: cookie cart merges into DB cart (same as session cart does now), then cookie is cleared
- Cookie stores the same array structure as `$_SESSION['cart_pending']`

### Cookie details
- Name: `gp_guest_cart`
- Value: JSON-encoded cart item array
- Lifetime: 7 days
- Path: `/`
- Flags: httponly, SameSite=Lax

---

## Implementation Order

1. Cookie consent banner (prerequisite for proper GA consent)
2. Recently viewed services helper + cookie logic
3. Guest cart persistence

---

## Verification

1. **Cookie consent**: Open site in incognito → banner appears → click Accept → check `cookie_consent` in DevTools → refresh → banner gone → GA fires in Network tab
2. **Recently viewed**: Visit 3 service pages → go to homepage → "Recently Viewed" section shows those 3 services → clear cookie → section disappears
3. **Guest cart**: Log out → add service to cart → close browser → reopen → cart still has the item → log in → item persists in DB cart
