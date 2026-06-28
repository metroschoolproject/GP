# Decoration Styles: Show with Date-Based Availability

## Problem

Decoration styles are fetched from the database and passed to the template (`$decorationStyles`), but **never rendered** on the customer-facing service detail page. CSS classes exist (`.style-grid`, `.style-card`) but no HTML uses them. The Cart controller also doesn't read `decoration_style_id` from POST data, even though `CartModel::addItem()` already handles it.

Customers see only the generic "Available Dates" list for decoration services — they can't browse or pick a specific decoration style.

## What Changes

### 1. Template: `app/views/main/_service_detail_template.php`

#### A. Add Decoration Styles Section (inside availability area)

For decoration services (`$isDecorationCategory && !empty($decorationStyles)`), add a **styles grid** inside the availability section, below the date picker. Similar to how venue halls display after a date is chosen.

**When no date selected:** Show a prompt "Choose a wedding date to see available styles" (same pattern as venue halls).

**When date is selected:** Show each decoration style as a card with:
- Photo (from `photo_url`)
- Style name
- Price (from `price` / `package_price` / `customize_price`)
- Available/Unavailable state based on service availability on that date
- Radio button for selection (same pattern as venue hall rows)

Since decoration doesn't have per-style booking tracking (unlike attire/venue), all styles are available when the service is available on the selected date. The service's availability on the date is already determined by the existing `$upcoming` / `$selectedDateHasBookOption` logic.

#### B. Add Sidebar Summary for Selected Style

When a decoration style is selected, update the sidebar sticky summary to show:
- Style name in a summary line
- Price update in the estimated total

#### C. Add Hidden Form Input

Add `<input type="hidden" name="decoration_style_id" id="cartDecorationStyleId">` to the cart form.

#### D. Add JavaScript

- Style card click → selects style, updates sidebar, updates hidden input
- Auto-select first available style
- Cart form validation: require style selection before submit

#### E. CSS

Add styles for `.decoration-style-grid` and `.decoration-style-card` (reuse existing dormant `.style-grid` / `.style-card` CSS or create new ones matching the venue hall card pattern).

### 2. Controller: `app/controllers/Cart.php`

Add `decoration_style_id` to the `$itemData` array in the `add()` method (line ~134):
```php
'decoration_style_id' => !empty($_POST['decoration_style_id']) ? (int)$_POST['decoration_style_id'] : null,
```

### 3. No Model Changes

- `CustomerServiceCatalog::getDecorationStyles()` already fetches the data
- `CartModel::addItem()` already stores `decoration_style_id`
- No availability tables needed (all styles available when service is available)

## Files Modified

1. `app/views/main/_service_detail_template.php` — styles section HTML, sidebar, JS, CSS
2. `app/controllers/Cart.php` — add `decoration_style_id` to POST reading

## UI Pattern Reference

- Follow the venue hall pattern (date → shows sub-items → select → add to cart)
- Reuse CSS custom properties (--wine, --gold, --sage, etc.)
- Style cards similar to venue hall rows but with photo emphasis
