# Booking Logic Audit and Remediation

Date: 2026-06-20

This document records the fourteen booking-flow issues identified during the backend audit and the remediation applied.

| # | Issue | Severity | Remediation |
|---|---|---|---|
| 1 | Admin booking actions accepted any authenticated user | Critical | Require the `admin` role in the Admin controller and every directly routable `Booking::admin*` method. |
| 2 | Browser-submitted instant payments were trusted | Critical | Require server-side gateway inquiry and validate successful status, amount, and method before confirmation. |
| 3 | Instant payment amount was not persisted | Critical | Store `amount` and `paid_amount`, update booking `paid_amount`, and make transaction references unique. |
| 4 | Package payout calculation duplicated the package total for every supplier | Critical | Calculate payouts from each active `booking_suppliers.item_price` allocation. |
| 5 | Payment enum omitted payout/replacement types | Critical | Add a migration containing all used payment types. |
| 6 | Booking mutations lacked CSRF protection | High | Add session-bound synchronizer tokens to booking/admin/supplier mutation requests. |
| 7 | Manual deposits accepted arbitrary amounts and optional proof | High | Require the exact configured deposit and a valid uploaded proof. |
| 8 | “Mark received” could verify a booking without payment | High | Require an actual pending deposit proof and delegate to payment verification. |
| 9 | Booking creation could leave partial records | High | Wrap booking persistence, slot reservation, supplier linking, and cart clearing in one transaction. |
| 10 | Capacity was held by unpaid package bookings indefinitely | High | Add reservation expiry/release support and release slots on cancellation/failed creation. |
| 11 | Cancellation did not release service capacity | High | Release all package/custom slot reservations during cancellation. |
| 12 | Failed capacity reservations were ignored | High | Treat any failed slot reservation as a booking creation failure and roll back. |
| 13 | Deposit percentage copy/calculations disagreed | Medium | Use one 20% constant/config value across backend and customer views; final balance is `total - paid`. |
| 14 | Supplier responses could be repeated from invalid states | Medium | Restrict responses to pending/confirmed rows appropriate to the current flow and use conditional updates. |

## Replacement rejection follow-up

The customer now has an explicit rejection path. Rejection fails the pending delta payment, marks the proposal `rejected_by_customer`, clears the selected candidate, returns the request to the admin queue, and notifies both sides. Timeout cleanup uses proposal/payment timestamps and does not expire submitted payment proofs awaiting verification.

## Deployment

Apply:

```sql
SOURCE database/migration_booking_integrity_hardening.sql;
SOURCE database/migration_booking_replacement_projection_fix.sql;
```

Set real payment gateway credentials and a non-placeholder cron token before enabling instant payments or scheduled expiry jobs.
