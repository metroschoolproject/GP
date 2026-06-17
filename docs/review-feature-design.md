# Review Feature — Design Document

> **Status**: Planning (decisions finalized)  
> **Date**: 2026-06-17  
> **Context**: Wedding service marketplace (Golden Promise) — customers book services from suppliers, events happen, customers should be able to review their overall booking experience.

---

## 1. Decisions (Finalized)

| Decision | Choice | Rationale |
|---|---|---|
| **Eligibility gate** | Booking status = `completed` only | Ensures final payment collected & supplier payouts settled before review |
| **Granularity** | One review per **booking** | Simpler UX — one form covers the whole experience, not per-service |
| **Edit window** | 7 days from submission | Lets customer refine, but prevents rewriting history months later |
| **Delete** | Soft-delete (`deleted_at` column) | Preserves data for analytics; customer can re-submit after deleting |
| **Reviewer identity** | Real customer names shown publicly | Transparency builds trust in a wedding marketplace |
| **Photo uploads** | No | Defer — adds moderation complexity; text reviews are sufficient for MVP |
| **Display enhancements** | "Load more" pagination + sort by recent / highest / lowest | Already have basic display; these two additions round out the UX |

---

## 2. Current State

### 2.1 What Already Exists

| Component | Status | Location |
|---|---|---|
| `reviews` DB table | ✅ Exists | `database/goldenpromise6.sql` |
| Aggregate rating display (avg + count per service) | ✅ Exists | `CustomerServiceCatalog.php` subquery |
| Individual review display on service detail pages | ✅ Exists | `_service_detail_template.php` |
| Star rating distribution bars (5★–1★ buckets) | ✅ Exists | `_service_detail_template.php` |
| Foreign keys (bookings, services, users, suppliers) | ✅ Exists | Schema constraints |

**The existing reviews table schema:**
```sql
reviews (
    id              BIGINT AUTO_INCREMENT PRIMARY KEY,
    booking_id      BIGINT NOT NULL,         -- FK → bookings
    booking_item_id BIGINT NOT NULL,         -- FK → booking_items (will become nullable)
    service_id      BIGINT NOT NULL,         -- FK → services (will become nullable)
    customer_id     BIGINT DEFAULT NULL,     -- FK → users (nullable!)
    supplier_id     BIGINT DEFAULT NULL,     -- FK → suppliers (nullable!)
    rating          TINYINT(1) DEFAULT NULL,  -- 1–5 star rating (nullable!)
    comment         TEXT DEFAULT NULL,        -- Written review text
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
```

### 2.2 What's Missing

- **No review submission** — no POST endpoint, no form, no `INSERT INTO reviews` anywhere in `app/`
- **No `ReviewModel.php`** — no dedicated model class
- **No eligibility gating** — no logic that checks booking status before allowing review
- **No customer "My Reviews" page** — customers can't see their own past reviews
- **No supplier review dashboard** — suppliers can't see or respond to their reviews
- **No admin moderation** — no approve/reject/flag workflow
- **No pagination/sorting on display** — review list has no "load more" or sort controls

---

## 3. Core Concept: Who Can Review & When?

### 3.1 The Principle

> A customer whose **booking has reached `completed` status** should be able to write **one review** covering their overall booking experience.

This ensures:

- **Authenticity** — only actual customers can review (no fake reviews)
- **Finality** — `completed` means final payment collected + supplier payouts settled; the transaction is truly done
- **One review per booking** — prevents spam/duplicate reviews; the customer reflects on the whole experience
- **Supplier trust** — suppliers know reviews come from genuine clients

### 3.2 Eligibility Rules

A customer is eligible to write a review when ALL of these conditions are true:

| # | Condition | Why |
|---|---|---|
| 1 | `booking.customer_id = current_user.id` | The booking belongs to this customer |
| 2 | `booking.status = 'completed'` | Booking fully closed: final payment collected, suppliers paid out |
| 3 | No existing (non-deleted) review for this `(booking_id, customer_id)` pair | One review per booking |

> **Note**: We do NOT separately check `event_date`. If the booking reached `completed`, the event has necessarily passed — the status lifecycle guarantees this (`completed` only follows `finalized`, which only triggers 3 days before the event). No redundant check needed.

### 3.3 When to Show the "Write Review" Button

- **Booking Detail page** (`booking/detail/{id}`) — if booking is `completed` and no review exists, show a single "Write a Review" button for the whole booking.
- **Booking Detail page** — if already reviewed, show the existing review with rating stars, comment, and an "Edit" link (if within 7 days).
- **Customer Dashboard** — a "Pending Reviews" card showing count of completed bookings that haven't been reviewed yet.
- **"My Reviews" page** (`review/my`) — dedicated page listing all submitted reviews with edit/delete actions.

---

## 4. Backend Logic Design

### 4.1 New Model: `ReviewModel.php`

**Location**: `app/models/ReviewModel.php`

```
ReviewModel
├── canReview($customerId, $bookingId) → bool
├── create($bookingId, $customerId, $rating, $comment) → review_id
├── update($reviewId, $customerId, $rating, $comment) → bool
├── delete($reviewId, $customerId) → bool                  (soft-delete)
├── getByBooking($bookingId) → review | null
├── getByCustomer($customerId, $limit, $offset) → [reviews] (excludes soft-deleted)
├── getByService($serviceId, $sort, $limit, $offset) → [reviews]
├── getBySupplier($supplierId, $limit, $offset) → [reviews]
├── getPendingBookings($customerId) → [completed bookings without review]
├── getAverageRating($serviceId) → {avg, count, distribution}
├── exists($bookingId, $customerId) → bool                  (excludes soft-deleted)
└── isWithinEditWindow($reviewId) → bool
```

### 4.2 Core Method Logic

#### `canReview(int $customerId, int $bookingId): bool`

```php
// 1. SELECT status, customer_id FROM bookings WHERE id = $bookingId
// 2. Verify bookings.customer_id = $customerId
// 3. Verify bookings.status = 'completed'
// 4. Check no existing non-deleted review:
//    SELECT COUNT(*) FROM reviews
//    WHERE booking_id = $bookingId AND customer_id = $customerId AND deleted_at IS NULL
// 5. Return true only if count = 0 AND all checks pass
```

#### `create(int $bookingId, int $customerId, int $rating, string $comment): int`

```php
// 1. Validate: canReview($customerId, $bookingId) — throw 403 if false
// 2. Validate: $rating 1-5, $comment 10-2000 chars
// 3. Determine supplier_id(s): the booking may have multiple suppliers.
//    For simplicity, supplier_id = NULL for bookings with multiple suppliers.
//    For single-supplier bookings, set supplier_id from the booking_suppliers table.
//    (or leave NULL always — the review is about the overall experience)
// 4. service_id and booking_item_id = NULL (review is at booking level)
// 5. INSERT INTO reviews (booking_id, booking_item_id, service_id, customer_id,
//                         supplier_id, rating, comment)
//    VALUES ($bookingId, NULL, NULL, $customerId, $supplierId, $rating, $comment)
// 6. Return the new review_id
```

#### `getByService(int $serviceId, string $sort, int $limit, int $offset): array`

```php
// Find reviews for bookings that included this service:
//
// SELECT r.*, u.full_name, u.profile_picture
// FROM reviews r
// JOIN booking_items bi ON bi.booking_id = r.booking_id
// JOIN users u ON u.user_id = r.customer_id
// WHERE bi.service_id = $serviceId
//   AND r.deleted_at IS NULL
//
// $sort options:
//   'recent'   → ORDER BY r.created_at DESC   (default)
//   'highest'  → ORDER BY r.rating DESC, r.created_at DESC
//   'lowest'   → ORDER BY r.rating ASC, r.created_at DESC
//
// LIMIT $limit OFFSET $offset
```

#### `getAverageRating(int $serviceId): array`

```php
// Returns:
// {
//   avg_rating: 4.3,
//   review_count: 27,
//   distribution: { 5: 15, 4: 7, 3: 3, 2: 1, 1: 1 }
// }
//
// Counts reviews where deleted_at IS NULL and a booking_item
// exists linking this service to the review's booking.
```

> **Note**: This logic already exists inline in `CustomerServiceCatalog.php`. Move it into `ReviewModel` and call it from both places.

#### `getPendingBookings(int $customerId): array`

```php
// SELECT b.*, COUNT(bi.id) AS service_count
// FROM bookings b
// LEFT JOIN booking_items bi ON bi.booking_id = b.id
// WHERE b.customer_id = $customerId
//   AND b.status = 'completed'
//   AND NOT EXISTS (
//       SELECT 1 FROM reviews r
//       WHERE r.booking_id = b.id
//       AND r.customer_id = $customerId
//       AND r.deleted_at IS NULL
//   )
// GROUP BY b.id
// ORDER BY b.updated_at DESC  (or join event_details for event_date)
// LIMIT 12 (last 12 months worth — avoid overwhelming old data)
```

### 4.3 Controller: `Review.php` (New)

**Location**: `app/controllers/Review.php`

New controller, separate from `Booking.php` (which is already 1709 lines):

| Method | URL | Description |
|---|---|---|
| `submit($bookingId)` | `POST /review/submit/{bookingId}` | Submit a new review for a completed booking |
| `update($reviewId)` | `POST /review/update/{reviewId}` | Edit an existing review (7-day window) |
| `delete($reviewId)` | `POST /review/delete/{reviewId}` | Soft-delete a review |
| `myReviews()` | `GET /review/my` | Customer's review dashboard (submitted + pending) |
| `serviceReviews($serviceId)` | `GET /review/service/{serviceId}` | Public reviews for a service (AJAX "load more") |

### 4.4 Validation Flow

```
┌─────────────────────────────────────────────┐
│              REVIEW SUBMISSION               │
│              VALIDATION FLOW                │
└─────────────────────────────────────────────┘
                      │
          ┌───────────▼───────────┐
          │ Is user logged in?    │──No──▶ Redirect to login
          │ (customer role)       │
          └───────────┬───────────┘
                      │ Yes
          ┌───────────▼───────────┐
          │ Does booking exist    │──No──▶ 404 Not Found
          │ and belong to this    │
          │ customer?             │
          └───────────┬───────────┘
                      │ Yes
          ┌───────────▼───────────┐
          │ Is booking status     │──No──▶ "Your booking is not
          │ 'completed'?          │        yet completed"
          └───────────┬───────────┘
                      │ Yes
          ┌───────────▼───────────┐
          │ Already reviewed?     │──Yes──▶ "You've already
          │ (non-deleted check)   │        reviewed this booking"
          └───────────┬───────────┘
                      │ No
          ┌───────────▼───────────┐
          │ Rating 1-5?           │──No──▶ "Invalid rating"
          │ Comment 10-2000 chars?│──No──▶ "Comment too short/long"
          └───────────┬───────────┘
                      │ Valid
          ┌───────────▼───────────┐
          │ INSERT INTO reviews   │
          │ Return success +      │
          │ redirect to booking   │
          │ detail with flash msg │
          └───────────────────────┘
```

### 4.5 Editing & Deletion Rules

| Action | Allowed When | Constraint |
|---|---|---|
| **Edit review** | Within 7 days of `created_at` | Customer can only edit their own review; updates rating and/or comment |
| **Delete review** | Any time | Soft-delete (sets `deleted_at`); customer can only delete their own |
| **Re-submit after delete** | Any time (if booking still `completed`) | Deleting clears the slot; customer can write a fresh review for the same booking |

**Why 7-day edit window?** Lets customer add detail or correct typos, but prevents revisiting a year-old review to change the narrative. After 7 days, they must delete and re-submit (a deliberate action that clears the old version entirely).

---

## 5. Display Enhancements

The review display on service detail pages already exists. Two enhancements are needed:

### 5.1 "Load More" Pagination

**Current behavior**: `CustomerServiceCatalog::getServiceReviews()` fetches the last 4 reviews with a hardcoded `LIMIT 4`.

**New behavior**:
- Initial load: show the last 4 reviews (keep existing behavior as the default)
- A "Load more reviews" button at the bottom triggers an AJAX call to `GET /review/service/{serviceId}?offset=4&limit=4&sort=recent`
- Each click increments offset by 4
- When the response returns fewer than 4 results, hide the button (no more reviews)
- The controller returns a JSON partial (review HTML fragments) that get appended to the list

**Endpoint**: `GET /review/service/{serviceId}`  
**Query params**: `sort` (recent/highest/lowest), `offset` (int), `limit` (int, default 4, max 20)

### 5.2 Sorting

Add sort controls above the review list on the service detail page:

| Sort Option | Label | Query |
|---|---|---|
| `recent` (default) | Most Recent | `ORDER BY created_at DESC` |
| `highest` | Highest Rated | `ORDER BY rating DESC, created_at DESC` |
| `lowest` | Lowest Rated | `ORDER BY rating ASC, created_at DESC` |

Implementation:
- Three buttons/tabs: "Most Recent" | "Highest Rated" | "Lowest Rated"
- Changing sort resets offset to 0 and reloads the review list via AJAX
- Active sort is visually highlighted
- The `ReviewModel::getByService()` method accepts `$sort` as a parameter

**Display mapping** — how reviews appear depends on what's being viewed:

| Context | How reviews are found | What's shown |
|---|---|---|
| **Service detail page** (public) | Find all reviews where the review's booking included this service (`JOIN booking_items`) | Reviewer name (real), avatar, star rating, comment, date, "load more" + sort |
| **Booking detail page** (customer) | The single review for this booking (if any) | Full review with edit/delete actions |
| **My Reviews page** (customer) | All reviews by this customer | Table: booking ref, rating, comment snippet, date, edit/delete |
| **Supplier review dashboard** | All reviews for bookings containing this supplier's services | Same as public display + service name label |

---

## 6. Database Changes

### 6.1 Schema Fixes

Since reviews are now at the **booking level** (not booking-item level):

| Issue | Current | Change |
|---|---|---|
| `booking_item_id` required | `BIGINT NOT NULL` | Make `DEFAULT NULL` (review is at booking level) |
| `service_id` required | `BIGINT NOT NULL` | Make `DEFAULT NULL` (a booking can have multiple services) |
| `customer_id` nullable | `DEFAULT NULL` | Make `NOT NULL` (every review has an identified customer) |
| `rating` nullable | `DEFAULT NULL` | Make `NOT NULL` |
| No edit timestamp | — | Add `updated_at` column |
| No soft-delete | — | Add `deleted_at` column |
| No duplicate prevention | — | Add `UNIQUE (booking_id, customer_id)` (where `deleted_at IS NULL`) |
| `supplier_id` nullable | `DEFAULT NULL` | Keep nullable (booking may have multiple suppliers) |

> **On `supplier_id`**: Since one review covers the whole booking, and a booking can involve multiple suppliers, `supplier_id` is set to `NULL` for multi-supplier bookings. For single-supplier bookings, it can be populated (set from `booking_suppliers`). The supplier review dashboard finds reviews via `JOIN booking_items` + `services.supplier_id` rather than relying on `reviews.supplier_id`.

### 6.2 Migration

```sql
-- Migration: 2026_06_review_table_improvements.sql

ALTER TABLE reviews
    MODIFY booking_item_id BIGINT DEFAULT NULL,
    MODIFY service_id BIGINT DEFAULT NULL,
    MODIFY customer_id BIGINT NOT NULL,
    MODIFY rating TINYINT(1) NOT NULL,
    ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at,
    ADD COLUMN deleted_at TIMESTAMP DEFAULT NULL AFTER updated_at,
    DROP INDEX booking_item_id,
    ADD UNIQUE KEY unique_review (booking_id, customer_id, deleted_at);
```

> **Note on the unique key**: Including `deleted_at` in the unique index allows a customer to delete and re-submit. MySQL treats NULLs as distinct in unique indexes, so `(booking_id=5, customer_id=10, deleted_at=NULL)` and `(booking_id=5, customer_id=10, deleted_at='2026-06-20')` don't conflict.

### 6.3 Future: Supplier Reply Table

```sql
CREATE TABLE review_replies (
    id          BIGINT AUTO_INCREMENT PRIMARY KEY,
    review_id   BIGINT NOT NULL,
    supplier_id BIGINT NOT NULL,
    reply_text  TEXT NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at  TIMESTAMP DEFAULT NULL,
    FOREIGN KEY (review_id)   REFERENCES reviews(id),
    FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id)
);
```

---

## 7. Route & URL Design

### Customer Routes (new `Review.php` controller)

| Method | URL | Handler | Description |
|---|---|---|---|
| `GET` | `/review/my` | `Review::myReviews()` | Customer's review dashboard (pending + submitted) |
| `POST` | `/review/submit/{bookingId}` | `Review::submit($bookingId)` | Submit a review for a completed booking |
| `POST` | `/review/update/{reviewId}` | `Review::update($reviewId)` | Edit review (within 7-day window) |
| `POST` | `/review/delete/{reviewId}` | `Review::delete($reviewId)` | Soft-delete a review |
| `GET` | `/review/service/{serviceId}` | `Review::serviceReviews($serviceId)` | AJAX: load more reviews + sort |

### Supplier Routes (new `SupplierReviews.php` controller)

| Method | URL | Handler | Description |
|---|---|---|---|
| `GET` | `/supplier/reviews` | `SupplierReviews::index()` | Review dashboard (all reviews across services) |
| `GET` | `/supplier/reviews/service/{serviceId}` | `SupplierReviews::byService($serviceId)` | Reviews filtered by a specific service |

### Admin Routes

| Method | URL | Handler | Description |
|---|---|---|---|
| `GET` | `/admin/reviews` | `Admin::reviews()` | All reviews with filters |
| `POST` | `/admin/reviews/hide/{reviewId}` | `Admin::hideReview($reviewId)` | Hide/flag a review |
| `POST` | `/admin/reviews/unhide/{reviewId}` | `Admin::unhideReview($reviewId)` | Restore a hidden review |

---

## 8. Security & Edge Cases

### 8.1 Security

| Concern | Mitigation |
|---|---|
| **Customer reviews a booking they don't own** | `canReview()` validates `bookings.customer_id = session user_id` |
| **Customer reviews before booking is completed** | Only `status = 'completed'` qualifies |
| **Duplicate review submission** | UNIQUE constraint on `(booking_id, customer_id, deleted_at)` + application check |
| **XSS in review comment** | `htmlspecialchars()` on output; strip tags on input |
| **Customer edits another's review** | Only allow if `review.customer_id = session user_id` |
| **Customer deletes another's review** | Same ownership check |
| **Rate limiting / spam** | Max 10 review submissions per customer per hour (application-level throttle) |
| **SQL injection** | PDO prepared statements (existing project standard) |
| **Deleted service still shows reviews** | Recalculate aggregate: only count reviews whose bookings include non-deleted services |

### 8.2 Edge Cases

| Scenario | Behavior |
|---|---|
| **Booking has multiple suppliers** | `supplier_id` = NULL on the review. Supplier dashboards find reviews via `booking_items → services.supplier_id` |
| **Booking cancelled after review written** | Review stays. Cancellation doesn't erase the experience that led to the review. |
| **Customer account deleted** | Keep the review. Replace displayed name with "Former Customer" or anonymize. Review data still has value. |
| **Supplier account deleted** | Reviews remain. Aggregate scores exclude deleted-supplier services. |
| **Customer books same services again (different booking)** | Each booking gets its own review slot. UNIQUE key is on `(booking_id, customer_id)`. |
| **Very old completed bookings** | All are eligible, but the "Pending Reviews" list caps at last 12 months to avoid clutter. Still, the customer can navigate to any old booking detail page and review from there. |
| **Rating without comment / comment without rating** | Both required. Rating alone is low-value feedback; comment alone without stars is confusing for aggregate scoring. |
| **Edit window expires mid-edit** | Check `isWithinEditWindow()` at submission time (server-side), not just when rendering the form. Show error if expired. |
| **Booking has no services** (edge case, shouldn't happen) | `canReview()` returns false — nothing to review. |

---

## 9. Implementation Phases

### Phase 1: Core Submission (MVP)

- [ ] **Schema migration** — fix nullable columns, add `updated_at`, `deleted_at`, unique key
- [ ] **`ReviewModel.php`** — `canReview()`, `create()`, `exists()`, `getByBooking()`, `getAverageRating()`
- [ ] **`Review.php` controller** — `submit()`, `myReviews()` methods
- [ ] **Booking Detail view** — add "Write a Review" section when booking is `completed`
- [ ] **My Reviews page** (`app/views/review/my.php`) — list submitted reviews
- [ ] **Refactor `CustomerServiceCatalog`** — move rating aggregation to `ReviewModel::getAverageRating()`
- [ ] **Route registration** — add `/review/*` routes in `Core.php`

### Phase 2: Management & Display

- [ ] **Edit review** — `update()` method, 7-day window check
- [ ] **Soft-delete review** — `delete()` method, re-submit after delete
- [ ] **"Load more" pagination** — AJAX endpoint + append logic on service detail page
- [ ] **Sort controls** — recent / highest / lowest on service detail page
- [ ] **Pending Reviews card** — customer dashboard widget
- [ ] **Real customer names** — update `_service_detail_template.php` to JOIN `users` table for reviewer name

### Phase 3: Supplier & Admin

- [ ] **Supplier review dashboard** — all reviews for this supplier's services, aggregate stats
- [ ] **Admin review management** — list, filter, hide/flag inappropriate reviews
- [ ] **Supplier reply to reviews** (future — table already designed in section 6.3)

---

## 10. Files Summary

### New Files

| File | Purpose |
|---|---|
| `app/models/ReviewModel.php` | All review database operations |
| `app/controllers/Review.php` | Customer-facing review endpoints (submit, edit, delete, my reviews, AJAX load) |
| `app/controllers/SupplierReviews.php` | Supplier review dashboard |
| `app/views/review/my.php` | Customer's "My Reviews" page (submitted + pending) |
| `app/views/review/_form.php` | Reusable star rating + comment form partial |
| `app/views/review/_card.php` | Reusable review card partial (used in service detail + my reviews) |
| `database/migrations/2026_06_review_table_improvements.sql` | Schema fixes |

### Modified Files

| File | Change |
|---|---|
| `app/controllers/Booking.php` | Pass review eligibility + existing review data to the booking detail view |
| `app/views/booking/detail.php` | Add "Write a Review" / "Your Review" section for completed bookings |
| `app/views/main/_service_detail_template.php` | Show real customer names (JOIN users), add "Load More" button + sort controls |
| `app/models/CustomerServiceCatalog.php` | Refactor rating aggregation to call `ReviewModel::getAverageRating()` |
| `app/libraries/Core.php` | Register routes for `/review/*` and `/supplier/reviews/*` |
| `public/css/supplier-service-detail.css` (or relevant CSS) | Star rating input widget styling, sort tab styling |

---

## 11. Architecture Diagram

```
                         ┌──────────────────────┐
                         │    reviews table      │
                         │  (already exists)     │
                         │  - booking_id (FK)    │
                         │  - customer_id (FK)   │
                         │  - rating, comment    │
                         │  - deleted_at (new)   │
                         └──────────┬───────────┘
                                    │
                    ┌───────────────┴───────────────┐
                    │                               │
            ┌───────▼────────┐            ┌─────────▼──────────┐
            │  ReviewModel   │            │ CustomerService    │
            │  (new)         │            │ Catalog (modify)   │
            │                │            │                    │
            │  canReview()   │            │ getServiceDetail() │
            │  create()      │            │  → calls           │
            │  update()      │            │  ReviewModel::     │
            │  delete()      │            │  getAverageRating()│
            │  getByService()│            │  getByService()    │
            │  getByBooking()│            └────────────────────┘
            │  getPending()  │
            └───────┬────────┘
                    │
        ┌───────────┴───────────┐
        │                       │
┌───────▼────────┐    ┌─────────▼──────────┐
│ Review.php     │    │ SupplierReviews.php │
│ (new)          │    │ (new)               │
│                │    │                     │
│ submit()       │    │ index()             │
│ update()       │    │ byService()         │
│ delete()       │    └─────────────────────┘
│ myReviews()    │
│ serviceReviews │
│ (AJAX load)    │
└───────┬────────┘
        │
┌───────▼──────────────┐
│ Views                 │
│                       │
│ review/my.php         │
│ review/_form.php      │
│ review/_card.php      │
│ booking/detail.php    │
│   (modified)          │
│ _service_detail_      │
│   template.php        │
│   (modified)          │
└───────────────────────┘
```

---

## 12. Summary

The review feature is **~70% built on the read side** — the `reviews` table, foreign keys, aggregate rating queries, and review display all exist. What's missing is the **write path**: submission, validation, eligibility gating, and management.

### Finalized Design

```
Eligibility:  booking.status = 'completed' (no separate event_date check)
Granularity:  ONE review per BOOKING (not per service item)
Edit window:  7 days from submission
Deletion:     soft-delete (deleted_at), re-submit allowed
Identity:     real customer names shown
Photos:       not supported
Display:      add "Load More" pagination + sort (recent / highest / lowest)
```

The cleanest architecture is a new `ReviewModel` + `Review` controller that plugs into the existing `reviews` table and display templates, with the booking detail page serving as the primary review submission surface.
