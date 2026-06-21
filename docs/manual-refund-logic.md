# Manual Refund Logic

Date: 2026-06-21

This document records the intended refund business logic for the current Golden Promise payment version, where customer and supplier payments are handled by manual transfer and verified by admin.

## Scope

Refunds in this version are not automatic gateway reversals. The application should track refund decisions, amounts, proof, and status, while the admin sends money back to the customer outside the system through KBZ Pay, Wave Money, AYA Pay, bank transfer, or another supported manual method.

This applies to:

- Customer booking cancellation refunds.
- Customer-paid replacement price-difference refunds.
- Admin audit history for manual refund actions.

## Current Payment Assumption

The system uses a manual payment workflow:

1. Customer transfers money outside the application.
2. Customer uploads payment proof.
3. Admin verifies the payment proof.
4. The system marks payment as successful.
5. If refund is required, admin manually transfers money back.
6. The system records the refund status and refund proof.

Because money movement happens outside the application, a refund status alone must not be treated as proof that the customer received money. A completed refund should require admin-entered refund details.

## Refund Status Lifecycle

Recommended refund statuses:

```text
refund_not_required
refund_required
refund_pending
refund_processing
refunded
refund_rejected
refund_failed
```

Status meaning:

| Status | Meaning |
|---|---|
| `refund_not_required` | No refund is owed. |
| `refund_required` | System or admin determined that customer should receive money back. |
| `refund_pending` | Refund is waiting for admin action. |
| `refund_processing` | Admin has started the manual refund but has not confirmed completion. |
| `refunded` | Admin completed the manual refund and recorded proof/reference. |
| `refund_rejected` | Admin decided no refund should be paid after review. |
| `refund_failed` | Admin attempted refund but transfer failed or details were invalid. |

## Recommended Refund Fields

For each refundable payment, the system should be able to store:

```text
refund_status
refund_amount
refund_reason
refund_method
refund_transaction_ref
refund_slip_path
refund_note
refund_requested_at
refund_processed_at
refunded_at
refunded_by
```

Minimum useful fields for this manual version:

```text
refund_status
refund_amount
refund_reason
refund_method
refund_transaction_ref
refund_slip_path
refunded_at
refunded_by
```

## Booking Cancellation Refund

When a customer requests cancellation:

1. Customer submits cancellation reason.
2. Supplier reviews if the booking type requires supplier review.
3. Admin makes the final cancellation decision.
4. Admin decides whether a refund is required.
5. If refund is required, system records `refund_required` or `refund_pending`.
6. Admin manually transfers refund to the customer.
7. Admin records refund method, transaction reference, amount, and optional proof image/PDF.
8. System marks refund as `refunded`.

The customer-facing message should be careful:

```text
Refund pending: Admin is processing your refund.
Refund completed: Your refund has been sent. Reference: {transaction_ref}
```

Avoid saying the customer has been refunded before admin records the actual manual transfer proof.

## Replacement Price-Difference Refund

This is the important gap for customer-paid replacement.

Scenario:

1. Original supplier cannot continue.
2. Admin selects a replacement supplier.
3. Replacement supplier is more expensive than the original supplier.
4. Customer pays only the price difference.
5. Admin verifies the replacement delta payment.
6. Replacement later fails, expires, or replacement supplier declines.
7. Customer should receive the paid price difference back.

Correct refund flow:

```text
replacement_delta payment success
replacement falls through
system creates refund_required for the delta amount
admin manually refunds customer
admin records refund proof/reference
payment becomes refunded
booking total and paid amount are adjusted
```

The refund amount should be:

```text
refund_amount = replacement_new_price - original_service_price
```

Only the extra customer-paid replacement delta should be refunded in this flow. The original booking deposit or original service amount should not be touched unless the whole booking is cancelled.

## Admin Responsibilities

Admin should be able to:

- See all refunds waiting for action.
- Review original payment proof and customer details.
- Confirm the refund amount.
- Enter refund method.
- Enter refund transaction reference.
- Upload refund proof.
- Mark refund as completed.
- Mark refund as failed with a reason.
- Reject refund with a clear admin note.

Admin should not be able to mark a refund completed without at least:

```text
refund_amount
refund_method
refund_transaction_ref
refunded_by
refunded_at
```

## Customer Visibility

Customer should see refund state in booking/payment history:

| State | Customer Copy |
|---|---|
| `refund_required` | Refund required. Admin will review it. |
| `refund_pending` | Refund pending. Admin is preparing your refund. |
| `refund_processing` | Refund is being processed. |
| `refunded` | Refund completed. |
| `refund_failed` | Refund could not be completed. Admin will contact you. |
| `refund_rejected` | Refund was not approved. |

For completed refunds, customer should see:

```text
refund_amount
refund_method
refund_transaction_ref
refunded_at
```

Refund proof files may be admin-only unless the team wants customers to download them.

## Supplier Impact

For replacement delta refunds:

- Supplier payout should not include a replacement delta that was refunded.
- If a replacement supplier declines before completion, no payout should be created for that replacement line.
- The original supplier should not receive payout for a replaced service row.

For full booking cancellation refunds:

- Supplier payout should not be released for cancelled booking items.
- If any payout was already released, the system needs a separate clawback/manual adjustment process. That is outside the current refund scope.

## Audit Requirements

Every refund decision should create an audit trail:

```text
booking_id
payment_id
old_status
new_status
actor_admin_id
refund_amount
reason
note
created_at
```

Useful audit events:

```text
refund_required_created
refund_processing_started
refund_completed
refund_failed
refund_rejected
replacement_delta_refund_required
replacement_delta_refunded
booking_deposit_refunded
```

## Current Codebase Observation

The current codebase already has partial refund bookkeeping:

- Booking cancellation can mark successful payments with `escrow_status = 'refunded'`.
- Replacement delta reversal can mark a `replacement_delta` payment status as `refunded`.
- A gateway refund method exists, but the current manual-payment version does not rely on it.

The missing business workflow is manual refund tracking: refund queue, admin refund proof/reference, customer-visible refund status, and clear audit history.

## Recommended Rule

In this manual payment version:

```text
Payment status = what happened to the customer's original payment.
Refund status = what happened to money returned to the customer.
```

Do not use only `payments.status = refunded` as the whole refund process. It should be the final result after admin completes and records the manual refund.

