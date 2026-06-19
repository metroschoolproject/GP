# Booking Detail Page Redesign — Plan

## Problem
Both admin and supplier booking detail pages suffer from poor information hierarchy: everything has equal visual weight, users can't quickly scan for critical info, colors are uniformly muted, and layouts feel cluttered.

## Goals
1. **Clear visual hierarchy** — users immediately know what's most important
2. **Scannable** — key metrics and statuses visible at a glance
3. **Action-oriented** — primary actions (approve payment, accept/decline) are prominent
4. **Consistent** — both pages share the same design language with the project's design system

---

## Admin Booking Detail — Changes

### 1. Summary strip (keep, refine)
- 4 stat cards: Status, Total, Paid, Balance
- Add subtle left-border accent colors per card (status=primary, total=neutral, paid=success, balance=danger if >0)
- Already works well — minor polish

### 2. Layout: switch from `1fr 360px` to `1fr 380px`
- Left column gets ~65% width
- Right column gets ~35% width  
- Better balance for content-heavy left side

### 3. Left column: reorder and prioritize
- **Payment review card** moves to TOP of left column (above services) when payment is awaiting review — this is the most actionable item
- **Services table** comes next (or first if no payment review needed)
- **Event information** follows

### 4. Right column: compact sidebar
- **Payment proof** — keep, but smaller thumbnail
- **Customer card** — keep, compact
- **Suppliers list** — keep, compact
- **Audit trail** — collapse to last 5 entries with "show all" toggle
- **Cancel booking** — keep at bottom with warning styling

### 5. Typography & color fixes
- Switch font from DM Sans to Poppins (match rest of project)
- Use project CSS variables (`--color-app-*` from tailwind config)
- Larger stat values (24px)
- Better section spacing (24px gaps between cards)

---

## Supplier Booking Detail — Changes

### 1. Header: simplify
- Remove the yellow action bar; integrate accept/decline buttons into the header
- Booking ref + status badges in one clean row
- Customer info as a compact strip below
- Countdown badge stays but styled consistently

### 2. Stats band: unify card styles
- All 4 stat cards use the same light background (remove the dark card)
- Use colored left-border accents instead: event date (neutral), guests (neutral), venue (neutral), earnings (primary)
- Better typography for values

### 3. Layout: switch to 2-column same as admin
- Left: Services table + Package schedules
- Right: Customer contact, On-site contact, Other suppliers, Special requests
- Consistent with admin layout pattern

### 4. Services table: add visual polish
- Service thumbnails with better sizing
- Status dots in the table for quick scanning
- Add-on indicators with clearer styling

### 5. Color strategy
- Use Poppins consistently (already using it)
- Badge colors match project system (green=success, amber=pending, red=danger)
- Countdown uses the same badge system

---

## Implementation Order
1. Admin booking detail — restructure layout, reorder cards, fix typography
2. Supplier booking detail — unify header, fix stats band, restructure layout
3. Test both pages render correctly with all states (empty data, various statuses, etc.)
