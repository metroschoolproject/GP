# Supplier Booking Pages — UX/UI Improvements Plan

## Overview
Improve UX/UI across all three supplier booking pages: bookings list, assignments, and booking detail. Address 22 issues identified in the audit.

## Files to Modify
- `app/views/supplier/bookings.php`
- `app/views/supplier/assignments.php`
- `app/views/supplier/bookingDetail.php`

## Files to Create
- `public/css/supplier-bookings.css` — shared styles for bookings list + assignments
- `public/css/supplier-booking-detail.css` — extracted from bookingDetail.php inline CSS
- `public/js/supplier-toast.js` — lightweight toast notification system

---

## Phase 1: Shared Infrastructure

### 1a. Create toast notification system (`public/js/supplier-toast.js`)
- Replace all `alert()` calls across all 3 pages with toast notifications
- Success toast (green) for accept/decline actions
- Error toast (red) for failures
- Auto-dismiss after 4 seconds
- Add script tag to `head.php` or include per-page

### 1b. Create shared CSS file (`public/css/supplier-bookings.css`)
- Extract all CSS from `bookings.php` inline `<style>` block
- Extract all CSS from `assignments.php` inline `<style>` block
- Standardize font to Poppins (match bookingDetail.php) via a CSS variable
- Add `<link>` tags to both views

### 1c. Create booking detail CSS (`public/css/supplier-booking-detail.css`)
- Extract all ~1,920 lines of inline CSS from `bookingDetail.php`
- Add `<link>` tag in the view

---

## Phase 2: Bookings List (`bookings.php`)

### 2a. Fix duplicate event date (#2)
- Remove the separate "Event date" column from the table (keep date under customer name)
- Replace with a "Service" column showing first service name or "—"

### 2b. Show date on mobile (#1)
- Remove `bk-hide-sm` from the customer cell subtitle date line
- Keep booking ref and service column hidden on mobile

### 2c. Add quick accept button for pending rows (#4)
- Add inline "Accept" button next to "View/Review" for pending bookings
- AJAX POST to `supplier/bookingRespond` with success toast

### 2d. Add navigation link to assignments (#9)
- Add "Switch to assignments view" link/button in the page header next to "Open calendar"

### 2e. Add date range quick filters (#6)
- Add "This week" and "This month" filter pills after the status filters
- These append `&range=week` or `&range=month` to the URL

---

## Phase 3: Assignments (`assignments.php`)

### 3a. Styled decline confirmation modal (#7)
- Replace native `confirm()` with a modal matching the booking detail decline modal pattern
- Add reason textarea field
- Reuse `.sup-modal-overlay` / `.sup-modal` CSS pattern from detail page

### 3b. Add guest count to confirmed cards (#10)
- Show guest count in the fact row (date, venue, guests, amount)

### 3c. Make cards clickable (#11)
- Wrap entire card content in an `<a>` tag linking to booking detail
- Prevent click from interfering with Accept/Decline buttons using `e.stopPropagation()`

### 3d. Add navigation link to bookings list (#9)
- Add "Switch to list view" link in the header

### 3e. Add success feedback before reload (#8)
- Show success toast before `window.location.reload()`

---

## Phase 4: Booking Detail (`bookingDetail.php`)

### 4a. Extract CSS to external file (#12, #21)
- Move all `<style>` content to `public/css/supplier-booking-detail.css`
- Add `<link>` tag

### 4b. Add tap-to-call for phone numbers (#19)
- Wrap all phone number displays in `<a href="tel:...">` links
- Apply to: customer phone, contact phone in sidebar, contact phone in assignment facts, phone in drawer

### 4c. Replace alert() with toast (#22)
- Replace all `alert()` in the inline JS with `window.supToast()` calls
- Success toasts for accept/decline/reschedule actions
- Error toasts for failures

### 4d. Add sticky action bar for response (#16)
- Make the response bar sticky at the bottom of the viewport when it exists
- Add CSS: `position: sticky; bottom: 0; z-index: 30;`
- Add subtle shadow to indicate stickiness

### 4e. Improve decline modal guidance (#17)
- Add placeholder text: "e.g., Already booked for this date, schedule conflict"
- Add character counter (min 10 chars)
- Show inline validation message

### 4f. Add visual affordance for clickable timeline rows (#14)
- Add a subtle hover effect with cursor pointer and background change
- Add a small "→" icon or "View" text that appears on hover

### 4g. Add breadcrumbs (#25)
- Replace the plain "All bookings" back link with a breadcrumb: Bookings > BK-042

---

## Phase 5: Cross-cutting

### 5a. Standardize fonts (#24)
- Update bookings.php to use Poppins (from DM Sans)
- Update assignments.php to use Poppins for body (keep Playfair Display for headings)
- All three pages will use: Poppins for body, Playfair Display for display headings

### 5b. Add link from bookings to assignments and vice versa (#9)
- Already covered in Phase 2d and 3d

---

## Implementation Order
1. Phase 1 (infrastructure) — toast JS + CSS extraction
2. Phase 4 (detail page) — most impactful, complex page
3. Phase 2 (bookings list) — medium complexity
4. Phase 3 (assignments) — simpler changes
5. Phase 5 (cross-cutting font standardization) — final polish

## Verification
- Check all 3 pages load correctly with external CSS
- Verify toast notifications appear for all AJAX actions
- Test mobile responsiveness on all pages
- Verify tap-to-call works on mobile
- Verify sticky action bar behavior
- Confirm no `alert()` calls remain in any of the 3 pages
