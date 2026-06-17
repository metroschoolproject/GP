# Admin Bookings & Booking Detail Redesign Plan

## Files to change

1. `app/views/admin/bookings.php` — Booking list page
2. `app/views/admin/bookingDetail.php` — Booking detail page

## Design principles

- Use existing `app-*` Tailwind color tokens (no new colors)
- Use Lucide icons (already loaded via CDN)
- Match the sidebar's visual language (rounded-xl cards, soft borders, consistent spacing)
- Improve readability and scan-ability for admin staff
- Keep all existing functionality/data intact — pure visual/CSS changes

---

## 1. `app/views/admin/bookings.php` — Redesign

### Stat cards row
- Add Lucide icons to each stat card (CalendarDays, CircleDollarSign, BadgeCheck, CircleX, Coins)
- Use colored icon backgrounds per stat
- Slightly taller cards with better padding

### Search/filter bar
- Keep existing form structure
- Add subtle icon inside search field (Search icon left-aligned)
- Add a "Clear" link next to the Filter button when filters are active

### Table
- Replace plain status badge with color-coded pill badges (match status colors from bookingDetail's `$statusClass`)
- Add hover row highlight (`hover:bg-app-soft/50`)
- Add a subtle left border accent on each row based on status
- Add customer avatar initial circle (first letter of customer name)
- Add booking date column
- Add a right-chevron icon on the "View" button for affordance
- Striped rows for better readability (alternating bg)
- Make table header sticky (optional, nice-to-have)

### Empty state
- Add an illustration icon (Inbox or PackageOpen)
- Better typography

---

## 2. `app/views/admin/bookingDetail.php` — Redesign

### Header section
- Cleaner "Back to bookings" link with chevron-left icon
- Status badge next to title (keep existing color logic)
- Add booking date + time metadata under customer info

### Main content (left column)

**Payment proof card**
- Cleaner layout with better visual hierarchy
- Amount sent vs Expected deposit side-by-side comparison with a visual indicator (check if matched)
- Payment details in a cleaner description list
- Better proof image display with lightbox-style border
- Verify/Reject buttons with icons (Check, X)

**Services table**
- Keep mostly as-is, but add hover states
- Add status chips per service item (if available)
- Better mobile handling

### Sidebar (right column)

**Booking money card**
- Add progress bar showing paid vs remaining
- Better visual weight for the balance due

**Customer card**
- Add avatar circle with initials
- Keep the rest clean

**Event card**
- Add Calendar icon
- Group date/time visually

**Suppliers card**
- Add supplier status dots (green for confirmed, amber for pending, red for rejected)
- Cleaner list with avatar initials

**Audit trail**
- Convert to a proper timeline with colored dots
- Show status changes as timeline entries with connecting line
- Color-code dots: emerald=confirmed/completed, rose=cancelled, amber=pending

**Cancel section**
- Add AlertTriangle icon
- Better visual warning treatment (amber/rose border)
- Confirm checkbox styled better

### JavaScript
- Keep all existing JS for payment review and cancel functionality
- Add a copy booking ref button (nice-to-have)

---

## What stays the same

- All PHP variables, data structures, and logic
- All form endpoints and AJAX handlers
- The dashboard layout wrapper structure (`$dashboardContent`, `$dashboardTitle`, etc.)
- The sidebar include
- All data passed from `Admin::bookings()` and `Admin::bookingDetail()`
