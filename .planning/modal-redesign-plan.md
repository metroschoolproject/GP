# Modal Redesign — Human, Not AI

## What's wrong with the current modals

1. **ALL CAPS labels on every field** (`uppercase tracking-wide text-xs font-semibold`) — visually exhausting, every single input screams the same way
2. **Every input is identical** — same `rounded-xl border border-gray-200 px-4 py-2.5 text-sm focus:ring-2` on everything. No hierarchy, no breathing
3. **Service type selector** — generic bordered cards, feels like a stock template
4. **Image upload** — the same SVG icon collage on every modal, tired pattern
5. **Dense, cramped** — too many borders, too many gray backgrounds, too much visual noise
6. **Footer buttons** — same `rounded-xl border-gray-200 px-6 py-2.5` on every Cancel button, template-y

## Design Principles

- **Sentence case labels** — no more SHOUTING at the user
- **Fewer borders** — inputs with subtle bottom-border (underline style) instead of full bordered boxes
- **Variety** — not every field looks the same; some are bold, some are light
- **Warmth** — softer backgrounds, rounded everything but not aggressively so
- **Deliberate asymmetry** — not every grid is 2 equal columns

## Changes by Modal

### 1. Service Type Selector (`serviceTypeModal`)
**Current:** Grid of bordered cards with generic icon + text
**New:** 
- 2-column layout (was 3-col), roomier
- Each option is a large chip with a soft filled background (no border)
- Bigger emoji icons (not Tabler icons — actual emoji for warmth)
- Brief one-line description under each category name
- Selected state: fills with the category's own color tone instead of generic violet
- No Cancel button (clicking overlay closes it)

### 2. Venue Add Modal (`venueModal`)
**Current:** ALL CAPS labels, bordered inputs, icon-collage image upload
**New:**
- Image upload: simple dashed rectangle with "+" and "Photo" text. Clean.
- Inputs: bottom-border only (like a nice form). Lighter, airier.
- Labels: sentence case, smaller, no uppercase
- Room rows: more compact, inline labels, cleaner remove button
- Save button: warm, filled. Cancel: text-only (no border)

### 3. Other Services Modal (`othersModal`)
Same treatment as Venue modal — consistent but not identical.

### 4. Edit Service Modal (`editServiceModal`)
Same form treatment. Price fields get slightly more prominence since they're important.

### 5. Edit / Create Package Modals
Same treatment. Category checkboxes become larger, more tappable.

### 6. Delete Confirmation
This one is actually fine — minimal changes.

## What stays the same

- All JS logic (open/close, save, validation)
- All modal IDs for JS targeting
- Image cropper integration
- Room/hall data collection

## Files to change

| File | Change |
|---|---|
| `app/views/supplier/service_management.html` | Rewrite modal HTML sections (lines 335-645) |
| `public/css/supplier-service-management.css` (new) | ~80 lines of custom modal styles |
