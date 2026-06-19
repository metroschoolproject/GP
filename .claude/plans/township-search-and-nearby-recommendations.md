# Plan: Township Search + Near-Location Recommendations for Service Browsing

## Overview

Add two location-aware features to the customer service browsing experience:

1. **Near-location recommendations** — When a logged-in customer has an address containing a township, services in the same township are boosted/prioritized
2. **Township search filter** — Customers can search/filter services by township name

## Current State

- `venues.location` is a free-text field storing addresses like `"35, Taw Win Road, Dagon Township, Yangon"` or `"အမှတ်-(132) အင်းယားလမ်း၊ ကမာရွတ်မြို့နယ်၊ ရန်ကုန်မြို့။"`
- `users.address` stores user addresses that sometimes include township info: `"Mingalar Taung township"`
- Only **venue** services have structured location data (via the `venues` table linked to services)
- No geocoordinates (lat/lng) exist in any table
- The service listing query joins `venues` for venue-specific services but non-venue services have no location data

## Design Decisions

### Why free-text LIKE matching instead of a normalized township table

The data is Myanmar-focused, with townships in both English (`Dagon Township`) and Burmese (`ဒဂုံမြို့နယ်`). A normalized `township` table with FK references would be the ideal long-term solution but requires:
- A migration to add `township_id` to `venues` and `services`
- Backfill logic to extract townships from existing `venues.location` text
- Supplier-facing UI changes to pick townships from a dropdown

For this iteration, LIKE-based matching on `venues.location` works with the existing data and zero migration. A `township` column can be added to `venues` later as a follow-up.

### Approach

Search `venues.location` for township matches. For non-venue services, also search `users.address` (the supplier's address). This gives reasonable coverage.

---

## Files to Change

### 1. `app/models/CustomerServiceCatalog.php` — Core search/recommendation logic

#### A. Add township extraction helper
```php
private function extractTownshipKeywords(string $address): array
```
Parses a free-text address string and extracts potential township keywords by looking for common patterns:
- `"X Township"` (English)  
- `"X မြို့နယ်"` (Myanmar)
- Falls back to splitting on commas and trimming

#### B. Add `township` filter to `getServices()`
- Add a `township` parameter to `$filters`
- When set, add a condition: `venues.location LIKE :township OR users.address LIKE :township`
- Join the `users` table to access supplier addresses (already has `suppliers` join via `suppliers.supplier_id = services.supplier_id`, need to add `LEFT JOIN users ON users.user_id = suppliers.user_id`)

#### C. Add `township` filter to `getCategories()`
- Same as above but for the category count query

#### D. Add `getNearbyServiceIds()` method
- When customer is logged in, fetch their `users.address`
- Extract township keywords from their address
- Return an array of service IDs whose `venues.location` or `suppliers.user_id -> users.address` match the customer's township
- Used by the `getServicePageData()` method to tag or sort nearby services

#### E. Add `getCustomerTownship()` method  
- Fetches the logged-in customer's address and extracts a township
- Returns `null` if not logged in or no address

#### F. Enhance `formatService()` to include location info
- Add `location` and `township` fields to the formatted service array (when available from venue)

### 2. `app/controllers/CustomerServices.php` — Controller updates

- Read `township` filter from request params
- Fetch customer township from model when user is logged in
- Pass township data to view
- Pass a `nearbyServiceIds` array to the view for recommendation highlighting

### 3. `app/views/main/service.php` — UI updates

#### A. Add township search chip to filter bar
- Add an `<input type="text" name="township">` in the filter bar (similar to the search input, or as a chip with text input)
- Style it to match the existing filter chips

#### B. Add "Near You" recommendation section
- Show a section above the service cards when customer has a matching township
- List nearby services with a location badge showing the township
- Add a visual indicator (e.g., location pin icon) on cards that are "near you"

#### C. Show location on service cards
- For venue services, display the location/township in the card body
- Add a small location icon + township name

### 4. `database/migration_add_township_search.sql` — Migration (optional, deferred)

SQL to add a `township` column to `venues` and populate it from existing `location` data. This is optional for this iteration — the LIKE search works without it.

---

## Implementation Order

1. **Model**: Add township filtering and recommendation logic to `CustomerServiceCatalog.php`
2. **Controller**: Wire up township params and customer location in `CustomerServices.php`
3. **View**: Add township search UI + near-you section in `main/service.php`

## Risks & Limitations

- **Free-text matching is imprecise** — "Dagon" in LIKE search would also match "Mingaladon" etc. Using word-boundary-aware matching where possible mitigates this
- **Non-venue services have no location** — suppliers who don't have venues linked won't show up in township search. The fallback is to search `users.address` for the supplier
- **No distance-based sorting** — purely keyword-based township matching, not geospatial proximity. Adding lat/lng would be the next evolution
- **Performance** — The additional joins and LIKE conditions may slow down queries on large datasets. The current dataset is small enough that this is not a concern yet
