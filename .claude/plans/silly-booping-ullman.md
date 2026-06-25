# Luxury Admin Dashboard — Sidebar + Dashboard Overhaul

## Goal
Port the luxury design language from the customer service detail page (`_service_detail_template.php`) to the admin sidebar and dashboard overview. The customer page uses warm brown shadows, glassmorphism, gold accents, DM Sans typography, and smooth easing curves — the admin should feel the same.

## Scope (Phase 1)
- **Sidebar** (`adminsidebar.php`) — glassmorphism, refined nav, smoother transitions
- **Topbar** — frosted glass, better breadcrumbs
- **Dashboard overview** (`admin_dashboard.php`) — full redesign from CDN-Tailwind to compiled CSS with luxury tokens
- **Shared tokens** (`tailwind.config.js`, `app.css`, `head.php`) — align with the luxury palette

## Files to Modify

| File | Change |
|---|---|
| `tailwind.config.js` | Add gold, glass, warm shadow, DM Sans, easing tokens |
| `resources/css/app.css` | Add luxury base styles, glass utilities, scrollbar, smooth transitions |
| `app/views/dashboardLayout/head.php` | Load DM Sans font alongside Poppins |
| `app/views/dashboardLayout/adminsidebar.php` | Glass sidebar, refined nav hover/active, smoother topbar |
| `app/views/admin/admin_dashboard.php` | Replace CDN Tailwind + inline styles with compiled CSS + luxury tokens |

## Design Token Alignment

Port these from the service detail page into Tailwind config + CSS:

```
Gold accent:     #D8B46A
Warm shadows:    rgba(74, 52, 47, 0.06-0.20)  (replacing cold gray)
Glass bg:        rgba(255, 248, 239, 0.72)
Glass border:    rgba(252, 248, 245, 0.35)
Font sans:       DM Sans (replacing Poppins as primary)
Easing:          cubic-bezier(0.19, 1, 0.22, 1)  (expo-out)
```

## Implementation Steps

### Step 1: Token Foundation (`tailwind.config.js` + `head.php`)
- Add `app-gold`, `app-gold-soft` colors
- Add `app-glass`, `app-glass-border` colors
- Update `shadow-card` and `shadow-panel` to warm brown tones
- Add `shadow-luxury` (larger warm shadow)
- Add DM Sans to `fontFamily.sans`
- Load DM Sans (400-700) in `head.php`

### Step 2: Global Luxury Styles (`app.css`)
- Add `@layer base` scrollbar styling (thin, warm-toned)
- Add smooth transition defaults on interactive elements
- Add `.glass-panel` utility class
- Add keyframe `fadeUp` for entrance animations

### Step 3: Sidebar Redesign (`adminsidebar.php`)
- Sidebar background: subtle glass effect with `backdrop-blur`
- Nav items: larger hover zones, smooth `cubic-bezier` transitions, wine-glow background on hover
- Active nav: gradient or glow instead of flat fill
- User profile section: glass card with soft shadow
- Section labels: gold-tinted uppercase labels
- Collapse/expand chevrons: smooth rotate with spring easing
- Logout button: refined with subtle danger glow on hover

### Step 4: Topbar Redesign (`adminsidebar.php`)
- Frosted glass background: `backdrop-blur-xl` + warm semi-transparent bg
- Breadcrumbs: refined spacing, subtle gold separator
- Search + notification: glass-styled inputs/buttons

### Step 5: Dashboard Overview (`admin_dashboard.php`)
- Remove CDN Tailwind dependency — use compiled `app.css`
- Replace inline `:root` variables with the shared luxury tokens
- Cards: warm shadows, glass backgrounds, smooth hover lift
- Stat numbers: Playfair Display serif, gold accent underlines
- Charts: warm palette integration
- Tables: refined hover states with warm tints
- Badges: glass-styled with wine/gold/sage tones
- Entrance animations: staggered `fadeUp` on page load
- Smooth scrollbar, refined filter pills

## What We're NOT Changing
- Other admin pages (bookings, suppliers, customers, etc.) — Phase 2
- Sidebar navigation structure/hierarchy
- Notification system functionality
- Any backend logic
- Dashboard iframe architecture (keeps JS isolation)

## Verification
- Open `http://localhost/GP/admin/dashboard` — sidebar should feel glassy and refined
- Hover nav items — smooth warm glow transitions
- Dashboard overview — cards with warm shadows, gold accents, smooth entrance animations
- Compare side-by-side with a customer service detail page — should feel like the same design system
