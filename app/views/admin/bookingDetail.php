<?php
$booking = $booking ?? [];
$items = $items ?? [];
$suppliers = $suppliers ?? [];
$eventDetails = $eventDetails ?? [];
$logs = $logs ?? [];
$payments = $payments ?? [];
$packageSchedules = $packageSchedules ?? [];
$bookingRef = $bookingRef ?? '';
$depositPercent = (float)($depositPercent ?? BOOKING_DEPOSIT_PERCENT);
$refund = $refund ?? null;
$refundEstimate = $refundEstimate ?? null;

$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$money = fn($v) => number_format((float)$v, 0) . ' MMK';
$dateTime = static function ($value, string $fallback = '-') {
    if (empty($value)) return $fallback;
    $timestamp = strtotime((string)$value);
    return $timestamp ? date('M j, Y H:i', $timestamp) : $fallback;
};
$dateOnly = static function ($value, string $fallback = '-') {
    if (empty($value)) return $fallback;
    $timestamp = strtotime((string)$value);
    return $timestamp ? date('M j, Y', $timestamp) : $fallback;
};
$timeOnly = static function ($value, string $fallback = '-') {
    if (empty($value)) return $fallback;
    $timestamp = strtotime((string)$value);
    return $timestamp ? date('H:i', $timestamp) : $fallback;
};

$totalAmount = (float)($booking['total_amount'] ?? 0);
$paidAmount = (float)($booking['paid_amount'] ?? 0);
$expectedDeposit = $totalAmount * ($depositPercent / 100);
$platformFeePercent = get_platform_fee_percent();
$expectedPlatformFee = round($totalAmount * ($platformFeePercent / 100), 2);
$expectedPayment = round($expectedDeposit + $expectedPlatformFee, 2);

// Balance = service total minus deposit portion only.
// paidAmount includes the platform fee, so subtract it to get the
// actual deposit applied to the service price.
$depositPaid = max(0, $paidAmount - $expectedPlatformFee);
$balanceDue = max(0, $totalAmount - $depositPaid);
$paidPercent = $totalAmount > 0 ? round(($depositPaid / $totalAmount) * 100) : 0;

$bookingStatusForPayment = (string)($booking['status'] ?? '');
// Check if there's a pending remaining payment — handles legacy bookings
// where status wasn't updated to 'pending_final_payment'.
$hasPendingRemainingPayment = !empty(array_filter($payments, static fn($p) => ($p['type'] ?? '') === 'remaining' && ($p['status'] ?? '') === 'pending'));
$isRemainingPaymentStage = $bookingStatusForPayment === 'pending_final_payment' || $hasPendingRemainingPayment;
$relevantType = $isRemainingPaymentStage ? 'remaining' : 'deposit';
$typePayments = array_values(array_filter($payments, static fn($p) => ($p['type'] ?? '') === $relevantType));
$pendingOfType = array_values(array_filter($typePayments, static fn($p) => ($p['status'] ?? '') === 'pending'));
$reviewPayment = $pendingOfType[count($pendingOfType) - 1] ?? ($typePayments[count($typePayments) - 1] ?? ($payments[count($payments) - 1] ?? []));

$paymentStatus = (string)($reviewPayment['status'] ?? ($booking['payment_status'] ?? 'unpaid'));
$paymentMethod = (string)($reviewPayment['bank_name'] ?? $reviewPayment['method'] ?? '-');
$transactionRef = (string)($reviewPayment['transaction_ref'] ?? '');
$sentAmount = (float)($reviewPayment['paid_amount'] ?? $reviewPayment['amount'] ?? $paidAmount);
$slipPath = trim((string)($reviewPayment['payment_slip_path'] ?? ''));
$slipExt = strtolower(pathinfo($slipPath, PATHINFO_EXTENSION));
$isImageSlip = $slipPath !== '' && in_array($slipExt, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true);
$isAwaitingReview = $hasPendingRemainingPayment || in_array($bookingStatusForPayment, ['payment_submitted', 'pending_final_payment'], true) || $paymentStatus === 'pending';

$firstEvent = $eventDetails[0] ?? [];
$eventDetailsByItem = [];
foreach ($eventDetails as $eventDetail) {
    $bookingItemId = (int)($eventDetail['booking_item_id'] ?? 0);
    if ($bookingItemId > 0) {
        $eventDetailsByItem[$bookingItemId] = $eventDetail;
    }
}
// Resolve add-on items through to parent package event detail
foreach ($items as $item) {
    $itemId = (int)($item['id'] ?? 0);
    if (!isset($eventDetailsByItem[$itemId])) {
        $parentId = (int)($item['package_booking_item_id'] ?? 0);
        if ($parentId > 0 && isset($eventDetailsByItem[$parentId])) {
            $eventDetailsByItem[$itemId] = $eventDetailsByItem[$parentId];
        }
    }
}
// Items that are NOT add-ons (standalone packages, standalone services)
$nonAddonItemIds = [];
foreach ($items as $item) {
    $itemId = (int)($item['id'] ?? 0);
    if (empty($item['package_booking_item_id'])) {
        $nonAddonItemIds[] = $itemId;
    }
}
$managedPackageEvents = [];
foreach ($packageSchedules as $schedule) {
    foreach ($schedule as $event) {
        $managedPackageEvents[] = $event;
    }
}
$managedStarts = array_filter(array_column($managedPackageEvents, 'start_time'));
$managedEnds = array_filter(array_column($managedPackageEvents, 'end_time'));
sort($managedStarts);
rsort($managedEnds);
$displayEventStart = $managedStarts[0] ?? ($firstEvent['start_time'] ?? null);
$displayEventEnd = $managedEnds[0] ?? ($firstEvent['end_time'] ?? null);

$runSheetEvents = [];
foreach ($items as $item) {
    $itemId = (int)($item['id'] ?? 0);
    if (!in_array($itemId, $nonAddonItemIds, true)) {
        continue;
    }

    $parentEvent = $eventDetailsByItem[$itemId] ?? [];
    $schedule = $packageSchedules[$itemId] ?? [];

    if (!empty($schedule)) {
        foreach ($schedule as $event) {
            $runSheetEvents[] = [
                'date' => $event['event_date'] ?? ($parentEvent['event_date'] ?? null),
                'start' => $event['start_time'] ?? null,
                'end' => $event['end_time'] ?? null,
                'service' => $event['service_name'] ?? ($item['service_name'] ?? 'Package service'),
                'category' => $event['category_name'] ?? 'Package service',
                'supplier' => $event['supplier_name'] ?? ($item['supplier_name'] ?? 'Golden Promise'),
                'location' => $parentEvent['location'] ?? '',
                'guests' => $parentEvent['guest_count'] ?? null,
                'contact_name' => $parentEvent['contact_name'] ?? ($booking['customer_name'] ?? ''),
                'contact_phone' => $parentEvent['contact_phone'] ?? ($booking['customer_phone'] ?? ''),
                'supplier_status' => $event['supplier_status'] ?? null,
                'is_replacement' => !empty($event['is_replacement']),
            ];
        }
        continue;
    }

    if (!empty($parentEvent)) {
        $runSheetEvents[] = [
            'date' => $parentEvent['event_date'] ?? ($item['booking_date'] ?? null),
            'start' => $parentEvent['start_time'] ?? ($item['start_time'] ?? null),
            'end' => $parentEvent['end_time'] ?? ($item['end_time'] ?? null),
            'service' => $item['service_name'] ?? 'Service',
            'category' => $item['category_name'] ?? 'Standalone service',
            'supplier' => $item['supplier_name'] ?? 'Supplier',
            'location' => $parentEvent['location'] ?? '',
            'guests' => $parentEvent['guest_count'] ?? null,
            'contact_name' => $parentEvent['contact_name'] ?? ($booking['customer_name'] ?? ''),
            'contact_phone' => $parentEvent['contact_phone'] ?? ($booking['customer_phone'] ?? ''),
        ];
    }
}

if (empty($runSheetEvents) && !empty($firstEvent)) {
    $runSheetEvents[] = [
        'date' => $firstEvent['event_date'] ?? null,
        'start' => $displayEventStart,
        'end' => $displayEventEnd,
        'service' => 'Main event',
        'category' => 'Event',
        'supplier' => 'Golden Promise',
        'location' => $firstEvent['location'] ?? '',
        'guests' => $firstEvent['guest_count'] ?? null,
        'contact_name' => $firstEvent['contact_name'] ?? ($booking['customer_name'] ?? ''),
        'contact_phone' => $firstEvent['contact_phone'] ?? ($booking['customer_phone'] ?? ''),
    ];
}

usort($runSheetEvents, static function ($a, $b) {
    return strcmp(
        (string)($a['date'] ?? '') . ' ' . (string)($a['start'] ?? ''),
        (string)($b['date'] ?? '') . ' ' . (string)($b['start'] ?? '')
    );
});

$runSheetGroups = [];
foreach ($runSheetEvents as $event) {
    $dateKey = !empty($event['date']) ? (string)$event['date'] : 'unscheduled';
    $runSheetGroups[$dateKey][] = $event;
}

foreach ($runSheetGroups as &$groupEvents) {
    $previousEnd = null;
    foreach ($groupEvents as &$event) {
        $startTimestamp = !empty($event['start']) ? strtotime((string)$event['start']) : false;
        $endTimestamp = !empty($event['end']) ? strtotime((string)$event['end']) : false;
        $event['overlap'] = $previousEnd !== null && $startTimestamp !== false && $startTimestamp < $previousEnd;
        if ($endTimestamp !== false) {
            $previousEnd = $previousEnd === null ? $endTimestamp : max($previousEnd, $endTimestamp);
        }
    }
    unset($event);
}
unset($groupEvents);

$statusLabel = ucwords(str_replace('_', ' ', (string)($booking['status'] ?? 'draft')));
$custName = (string)($booking['customer_name'] ?? 'Customer');
$custInitials = strtoupper(mb_substr(trim($custName) !== '' ? $custName : 'C', 0, 1));

$badgeClass = static function (string $status): string {
    return match (strtolower($status)) {
        'paid', 'success', 'completed' => 'bkd-badge--success',
        'confirmed' => 'bkd-badge--info',
        'cancelled', 'failed', 'rejected' => 'bkd-badge--danger',
        'pending', 'pending_payment', 'payment_submitted' => 'bkd-badge--warn',
        default => 'bkd-badge--neutral',
    };
};

$supplierStatusDot = static function (string $status): string {
    return match (strtolower($status)) {
        'confirmed', 'accepted' => 'bkd-dot--success',
        'pending', 'pending_supplier_response', 'needs_replacement' => 'bkd-dot--warn',
        'rejected', 'cancelled' => 'bkd-dot--danger',
        default => 'bkd-dot--neutral',
    };
};

$suppliersById = [];
$replacementSourceById = [];
foreach ($suppliers as $supplier) {
    $supplierRowId = (int)($supplier['id'] ?? 0);
    if ($supplierRowId > 0) {
        $suppliersById[$supplierRowId] = $supplier;
    }
}
foreach ($suppliers as $supplier) {
    $replacementRowId = (int)($supplier['replaced_by_id'] ?? 0);
    if ($replacementRowId > 0) {
        $replacementSourceById[$replacementRowId] = $supplier;
    }
}

$logDot = static function (string $status): string {
    return match (strtolower($status)) {
        'confirmed', 'completed', 'paid' => 'bkd-dot--success',
        'cancelled', 'rejected' => 'bkd-dot--danger',
        'pending_payment', 'payment_submitted' => 'bkd-dot--warn',
        default => 'bkd-dot--neutral',
    };
};

$showAllLogs = count($logs) > 5;
$visibleLogs = $showAllLogs ? array_slice(array_reverse($logs), 0, 5) : array_reverse($logs);

$dashboardTitle = 'Bookings';
$dashboardCrumb = $bookingRef ?: 'Booking detail';
$dashboardBreadcrumbs = [
    ['label' => 'Bookings', 'url' => URLROOT . '/admin/bookings'],
    ['label' => $bookingRef ?: 'Booking detail', 'url' => null],
];
$dashboardContentClass = 'admin-booking-detail-outlet';
$dashboardContent = function () use (
    $booking,
    $items,
    $suppliers,
    $logs,
    $bookingRef,
    $money,
    $h,
    $dateTime,
    $dateOnly,
    $timeOnly,
    $totalAmount,
    $paidAmount,
    $expectedDeposit,
    $balanceDue,
    $paidPercent,
    $expectedPayment,
    $expectedPlatformFee,
    $platformFeePercent,
    $reviewPayment,
    $paymentStatus,
    $paymentMethod,
    $transactionRef,
    $sentAmount,
    $slipPath,
    $isImageSlip,
    $isAwaitingReview,
    $firstEvent,
    $eventDetailsByItem,
    $displayEventStart,
    $displayEventEnd,
    $eventDetails,
    $packageSchedules,
    $nonAddonItemIds,
    $runSheetGroups,
    $statusLabel,
    $depositPercent,
    $custName,
    $custInitials,
    $badgeClass,
    $supplierStatusDot,
    $suppliersById,
    $replacementSourceById,
    $logDot,
    $showAllLogs,
    $visibleLogs,
    $refund,
    $refundEstimate,
    $isRemainingPaymentStage
) {
    $bookingId = (int)($booking['id'] ?? 0);
    $createdAt = $dateOnly($booking['created_at'] ?? null);
    $canCancel = !in_array(($booking['status'] ?? ''), ['cancelled', 'completed'], true);
    $canMarkReceived = !in_array(($booking['status'] ?? ''), ['payment_verified', 'paid', 'confirmed', 'pending_final_payment', 'finalized', 'completed', 'cancelled'], true);
    $canMarkCompleted = in_array(($booking['status'] ?? ''), ['finalized', 'in_progress'], true);
?>
<style>
  /* ── Booking Detail — Option B: Priority-First ── */
  .admin-booking-detail-outlet {
    min-height: 100%;
    background: #F4F1EE;
    padding: 24px 28px;
    font-size: 13.5px;
    overflow-y: auto;
    -webkit-font-smoothing: antialiased;
  }

  .bkd-page {
    --bkd-surface: #FFFFFF;
    --bkd-soft: #FFFFFF;
    --bkd-border: #ead8c7;
    --bkd-border-light: #eddecc;
    --bkd-primary: #6d4c5b;
    --bkd-primary-soft: #eddecc;
    --bkd-primary-hover: #7b5c69;
    --bkd-text: #111827;
    --bkd-muted: #b79c8b;
    --bkd-body: #7b5c69;
    --bkd-success-bg: #ECFDF5;
    --bkd-success-text: #065F46;
    --bkd-success-border: #059669;
    --bkd-warn-bg: #FFFBEB;
    --bkd-warn-text: #92400E;
    --bkd-warn-border: #d97706;
    --bkd-danger-bg: #FEF2F2;
    --bkd-danger-text: #991B1B;
    --bkd-danger-border: #dc2626;
    --bkd-info-bg: #e8e7ff;
    --bkd-info-text: #4f46a5;
    --bkd-neutral-bg: #F5F5F4;
    --bkd-neutral-text: #78716C;
    max-width: 1200px;
    margin: 0 auto;
  }
  .bkd-page * { box-sizing: border-box; }

  /* ── Compact Header ── */
  .bkd-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 16px;
    flex-wrap: wrap;
  }
  .bkd-top-left {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
    flex-wrap: wrap;
  }
  .bkd-top-right {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
  }
  .bkd-ref {
    font-size: 20px;
    font-weight: 700;
    color: var(--bkd-text);
    letter-spacing: -0.02em;
    white-space: nowrap;
  }
  .bkd-ref em { font-style: normal; color: var(--bkd-muted); font-weight: 400; }
  .bkd-top-meta {
    font-size: 12px;
    color: var(--bkd-body);
    font-weight: 600;
  }

  /* ── Summary Strip ── */
  .bkd-strip {
    display: flex;
    align-items: center;
    gap: 4px;
    background: var(--bkd-surface);
    border: 1px solid var(--bkd-border);
    border-radius: 12px;
    padding: 12px 16px;
    margin-bottom: 16px;
    flex-wrap: wrap;
    box-shadow: 0 1px 3px rgba(52,35,43,.04);
  }
  .bkd-strip-item {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 0 14px;
    border-right: 1px solid var(--bkd-border-light);
  }
  .bkd-strip-item:last-child { border-right: 0; }
  .bkd-strip-label {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: var(--bkd-muted);
  }
  .bkd-strip-value {
    font-size: 14px;
    font-weight: 700;
    color: var(--bkd-text);
    white-space: nowrap;
  }
  .bkd-strip-value.is-success { color: var(--bkd-success-text); }
  .bkd-strip-value.is-danger { color: var(--bkd-danger-text); }

  /* ── Step Progress ── */
  .bkd-steps {
    display: flex;
    background: var(--bkd-surface);
    border: 1px solid var(--bkd-border);
    border-radius: 12px;
    overflow: hidden;
    margin-bottom: 16px;
    box-shadow: 0 1px 3px rgba(52,35,43,.04);
  }
  .bkd-step {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 14px;
    border-right: 1px solid var(--bkd-border-light);
    font-size: 11px;
    font-weight: 700;
    color: var(--bkd-muted);
    background: var(--bkd-soft);
  }
  .bkd-step:last-child { border-right: 0; }
  .bkd-step.is-done { color: var(--bkd-success-text); background: #f0fdf4; }
  .bkd-step.is-current {
    color: var(--bkd-primary);
    background: #fdf9f5;
    box-shadow: inset 0 -3px 0 var(--bkd-primary);
  }
  .bkd-step-num {
    width: 24px; height: 24px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 11px; font-weight: 800;
    background: var(--bkd-border-light);
    color: var(--bkd-muted);
    flex-shrink: 0;
  }
  .bkd-step.is-done .bkd-step-num { background: var(--bkd-success-text); color: #fff; }
  .bkd-step.is-current .bkd-step-num { background: var(--bkd-primary); color: #fff; }
  .bkd-step-sub { font-size: 9px; font-weight: 600; opacity: .7; }

  /* ── Priority Action Card ── */
  .bkd-action {
    background: var(--bkd-surface);
    border: 1px solid var(--bkd-border);
    border-radius: 14px;
    overflow: hidden;
    margin-bottom: 16px;
    box-shadow: 0 4px 16px rgba(52,35,43,.06);
  }
  .bkd-action--urgent { border-top: 4px solid var(--bkd-warn-border); }
  .bkd-action--success { border-top: 4px solid var(--bkd-success-border); }
  .bkd-action--danger { border-top: 4px solid var(--bkd-danger-border); }
  .bkd-action-head {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 16px 20px;
    border-bottom: 1px solid var(--bkd-border-light);
  }
  .bkd-action-icon {
    width: 36px; height: 36px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
  }
  .bkd-action-icon svg { width: 18px; height: 18px; }
  .bkd-action-icon--warn { background: var(--bkd-warn-bg); color: var(--bkd-warn-text); }
  .bkd-action-icon--success { background: var(--bkd-success-bg); color: var(--bkd-success-text); }
  .bkd-action-icon--danger { background: var(--bkd-danger-bg); color: var(--bkd-danger-text); }
  .bkd-action-icon--neutral { background: var(--bkd-neutral-bg); color: var(--bkd-neutral-text); }
  .bkd-action-title { font-size: 14px; font-weight: 700; color: var(--bkd-text); }
  .bkd-action-sub { font-size: 11px; color: var(--bkd-muted); font-weight: 600; margin-top: 1px; }
  .bkd-action-body { padding: 20px; }

  /* ── Payment proof layout ── */
  .bkd-pay-grid {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 320px;
    gap: 20px;
    align-items: start;
  }
  .bkd-proof-link {
    display: block;
    overflow: hidden;
    border: 1px solid var(--bkd-border);
    border-radius: 10px;
    background: var(--bkd-surface);
    text-decoration: none;
    transition: box-shadow .12s;
  }
  .bkd-proof-link:hover { box-shadow: 0 2px 8px rgba(28,25,23,.08); }
  .bkd-proof-link img {
    width: 100%;
    max-height: 260px;
    object-fit: contain;
    background: #FFFFFF;
  }
  .bkd-proof-file {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    min-height: 120px; color: var(--bkd-primary); font-size: 12px; font-weight: 700; gap: 6px;
  }
  .bkd-proof-file svg { width: 28px; height: 28px; }

  /* ── KV grid ── */
  .bkd-kv-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
  .bkd-kv {
    border: 1px solid var(--bkd-border-light);
    border-radius: 10px;
    background: var(--bkd-soft);
    padding: 12px;
  }
  .bkd-kv-label {
    font-size: 10px; font-weight: 700; letter-spacing: .08em;
    text-transform: uppercase; color: var(--bkd-muted); margin-bottom: 4px;
  }
  .bkd-kv-value { color: var(--bkd-text); font-size: 13px; font-weight: 700; overflow-wrap: anywhere; }
  .bkd-kv-sub { margin-top: 2px; color: var(--bkd-body); font-size: 11px; font-weight: 600; }

  /* ── Progress bar ── */
  .bkd-progress { height: 6px; border-radius: 999px; background: var(--bkd-soft); overflow: hidden; margin-top: 12px; }
  .bkd-progress span { display: block; height: 100%; border-radius: 999px; background: var(--bkd-primary); transition: width .4s ease; }

  /* ── Review form ── */
  .bkd-review-form {
    border: 1px solid var(--bkd-warn-border);
    border-radius: 10px;
    background: var(--bkd-warn-bg);
    padding: 14px;
    margin-top: 16px;
  }
  .bkd-review-form textarea {
    width: 100%; min-height: 60px;
    border: 1px solid var(--bkd-border); border-radius: 10px;
    background: var(--bkd-surface); padding: 10px 12px;
    color: var(--bkd-text); font: inherit; font-size: 12px; outline: none; resize: vertical;
  }
  .bkd-review-form textarea:focus { border-color: var(--bkd-primary); }
  .bkd-review-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-top: 8px; }

  /* ── Buttons ── */
  .bkd-btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 0 15px; height: 38px; border-radius: 10px;
    font-size: 12px; font-weight: 700; font-family: inherit;
    cursor: pointer; transition: background .15s, border-color .15s, box-shadow .15s;
    text-decoration: none; white-space: nowrap;
  }
  .bkd-btn svg { width: 14px; height: 14px; flex-shrink: 0; }
  .bkd-btn--ghost { border: 1px solid var(--bkd-border); background: var(--bkd-surface); color: var(--bkd-primary); }
  .bkd-btn--ghost:hover { background: var(--bkd-primary-soft); border-color: var(--bkd-primary); }
  .bkd-btn--primary { border: 1px solid var(--bkd-primary); background: var(--bkd-primary); color: #FFFFFF; font-weight: 800; }
  .bkd-btn--primary:hover { background: var(--bkd-primary-hover); box-shadow: 0 2px 8px rgba(109,76,91,.25); }
  .bkd-btn--success { border: 1px solid var(--bkd-success-border); background: var(--bkd-success-text); color: #FFFFFF; font-weight: 800; }
  .bkd-btn--success:hover { background: #047857; }
  .bkd-btn--danger { border: 1px solid var(--bkd-danger-border); background: #dc2626; color: #FFFFFF; font-weight: 800; }
  .bkd-btn--danger:hover { background: #b91c1c; }

  /* ── Badges ── */
  .bkd-badge {
    display: inline-flex; align-items: center; gap: 5px;
    border-radius: 20px; padding: 3px 10px;
    font-size: 10px; font-weight: 700; letter-spacing: .04em;
    text-transform: uppercase; white-space: nowrap;
  }
  .bkd-badge::before { content: ''; width: 6px; height: 6px; border-radius: 999px; background: currentColor; flex-shrink: 0; }
  .bkd-badge--success { background: var(--bkd-success-bg); color: var(--bkd-success-text); }
  .bkd-badge--warn    { background: var(--bkd-warn-bg);    color: var(--bkd-warn-text); }
  .bkd-badge--danger  { background: var(--bkd-danger-bg);  color: var(--bkd-danger-text); }
  .bkd-badge--info    { background: var(--bkd-info-bg);    color: var(--bkd-info-text); }
  .bkd-badge--neutral { background: var(--bkd-neutral-bg); color: var(--bkd-neutral-text); }

  /* ── Collapsible Sections ── */
  .bkd-sections { display: grid; gap: 10px; margin-bottom: 16px; }
  .bkd-section {
    background: var(--bkd-surface);
    border: 1px solid var(--bkd-border);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(52,35,43,.04);
  }
  .bkd-section summary {
    display: flex; align-items: center; gap: 10px;
    padding: 14px 18px;
    font-size: 13px; font-weight: 700; color: var(--bkd-text);
    cursor: pointer; list-style: none; user-select: none;
    transition: background .12s;
  }
  .bkd-section summary::-webkit-details-marker { display: none; }
  .bkd-section summary::after {
    content: '';
    margin-left: auto;
    width: 0; height: 0;
    border-left: 4px solid transparent; border-right: 4px solid transparent;
    border-top: 5px solid var(--bkd-muted);
    transition: transform .2s;
  }
  .bkd-section[open] summary::after { transform: rotate(180deg); }
  .bkd-section summary:hover { background: #fdf9f5; }
  .bkd-section-count {
    font-size: 11px; font-weight: 600; color: var(--bkd-muted);
    background: var(--bkd-neutral-bg); padding: 2px 8px; border-radius: 999px;
  }
  .bkd-section-body { padding: 0 18px 18px; }

  /* ── Table ── */
  .bkd-table-wrap { overflow-x: auto; }
  .bkd-table { width: 100%; border-collapse: collapse; }
  .bkd-table thead tr { background: var(--bkd-soft); }
  .bkd-table th {
    padding: 9px 14px; font-size: 10px; font-weight: 700;
    letter-spacing: .08em; text-transform: uppercase;
    color: var(--bkd-muted); text-align: left; white-space: nowrap;
  }
  .bkd-table th:last-child, .bkd-table th.is-right { text-align: right; }
  .bkd-table tbody tr { border-top: 1px solid var(--bkd-border-light); transition: background .1s; }
  .bkd-table tbody tr:hover { background: #fdf9f5; }
  .bkd-table td { padding: 12px 14px; vertical-align: middle; }
  .bkd-table td:last-child, .bkd-table td.is-right { text-align: right; }
  .bkd-table-name { font-weight: 700; color: var(--bkd-text); font-size: 13px; }
  .bkd-table-sub { font-size: 11px; color: var(--bkd-muted); margin-top: 2px; }
  .bkd-table-amount { font-weight: 700; color: var(--bkd-text); white-space: nowrap; }
  .bkd-addon-chip {
    display: inline-flex; align-items: center; gap: 3px;
    font-size: 10px; font-weight: 600; padding: 1px 7px;
    border-radius: 999px; background: var(--bkd-info-bg); color: var(--bkd-info-text); margin-top: 3px;
  }

  /* ── Supplier list ── */
  .bkd-sup-list { display: grid; gap: 6px; }
  .bkd-sup-row {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 12px;
    border: 1px solid var(--bkd-border-light); border-radius: 10px; background: var(--bkd-soft);
  }
  .bkd-sup-info { flex: 1; min-width: 0; }
  .bkd-sup-name { font-size: 12px; font-weight: 700; color: var(--bkd-text); overflow-wrap: anywhere; }
  .bkd-sup-sub { font-size: 10px; color: var(--bkd-muted); font-weight: 600; text-transform: uppercase; letter-spacing: .04em; }
  .bkd-sup-svc { margin-top: 3px; color: var(--bkd-body); font-size: 11px; font-weight: 600; }

  /* ── Customer card ── */
  .bkd-cust {
    display: flex; align-items: center; gap: 12px;
    padding: 10px 12px;
    border: 1px solid var(--bkd-border-light); border-radius: 10px; background: var(--bkd-soft);
  }
  .bkd-avatar {
    display: grid; place-items: center;
    width: 36px; height: 36px; border-radius: 999px;
    background: var(--bkd-primary-soft); color: var(--bkd-primary);
    font-size: 13px; font-weight: 800; flex-shrink: 0;
  }
  .bkd-cust-name { font-size: 13px; font-weight: 700; color: var(--bkd-text); }
  .bkd-cust-detail { font-size: 11px; color: var(--bkd-muted); margin-top: 2px; }

  /* ── Timeline ── */
  .bkd-timeline { display: grid; }
  .bkd-timeline-item { display: grid; grid-template-columns: 16px 1fr; gap: 8px; }
  .bkd-timeline-dot { display: flex; flex-direction: column; align-items: center; }
  .bkd-timeline-dot::after { content: ''; width: 1px; flex: 1; background: var(--bkd-border-light); margin-top: 5px; }
  .bkd-timeline-item:last-child .bkd-timeline-dot::after { display: none; }
  .bkd-timeline-body { padding-bottom: 12px; }
  .bkd-timeline-title { font-weight: 700; color: var(--bkd-text); font-size: 12px; }
  .bkd-timeline-time { font-size: 11px; color: var(--bkd-muted); margin-top: 1px; }

  /* ── Status dots ── */
  .bkd-dot { display: inline-block; width: 7px; height: 7px; border-radius: 999px; flex-shrink: 0; }
  .bkd-dot--success { background: #059669; }
  .bkd-dot--warn    { background: #d97706; }
  .bkd-dot--danger  { background: #dc2626; }
  .bkd-dot--neutral { background: #A8A29E; }

  /* ── Toggle more ── */
  .bkd-toggle-more {
    display: block; width: 100%; margin-top: 8px; padding: 6px 0;
    border: 0; background: none; color: var(--bkd-primary);
    font-size: 11px; font-weight: 700; font-family: inherit; cursor: pointer; text-align: center;
  }
  .bkd-toggle-more:hover { text-decoration: underline; }

  /* ── Cancel form ── */
  .bkd-cancel-form { display: grid; gap: 10px; }
  .bkd-cancel-form textarea {
    width: 100%; min-height: 60px;
    border: 1px solid var(--bkd-danger-border); border-radius: 10px;
    background: var(--bkd-surface); padding: 10px 12px;
    color: var(--bkd-text); font: inherit; font-size: 12px; outline: none; resize: vertical;
  }
  .bkd-cancel-check {
    display: flex; align-items: center; gap: 8px;
    color: var(--bkd-body); font-size: 12px; font-weight: 600;
  }

  /* ── Empty state ── */
  .bkd-empty { padding: 24px; text-align: center; color: var(--bkd-muted); font-size: 12px; font-weight: 600; }

  /* ── Bottom actions ── */
  .bkd-bottom {
    display: flex; align-items: center; justify-content: space-between; gap: 10px;
    flex-wrap: wrap;
  }
  .bkd-bottom-left, .bkd-bottom-right { display: flex; gap: 8px; flex-wrap: wrap; }

  /* ── Modal ── */
  .bkd-modal-backdrop {
    position: fixed; inset: 0; z-index: 1000;
    display: none; align-items: center; justify-content: center;
    padding: 20px; background: rgba(17,24,39,.48); backdrop-filter: blur(3px);
    font-family: 'DM Sans', system-ui, -apple-system, sans-serif;
  }
  .bkd-modal-backdrop.is-open { display: flex; }
  .bkd-modal {
    width: min(100%, 440px);
    border: 1px solid var(--bkd-border); border-radius: 1rem;
    background: #fff; box-shadow: 0 24px 70px rgba(17,24,39,.22);
    overflow: hidden; animation: bkd-modal-in .18s ease-out;
  }
  @keyframes bkd-modal-in { from { opacity:0; transform:translateY(8px) scale(.98); } to { opacity:1; transform:translateY(0) scale(1); } }
  .bkd-modal-head { display: block; padding: 26px 26px 0; }
  .bkd-modal-icon {
    display: grid; place-items: center; width: 46px; height: 46px;
    margin-bottom: 16px; border-radius: .75rem; background: #FEE2E2; color: #dc2626;
  }
  .bkd-modal-icon svg { width: 22px; height: 22px; }
  .bkd-modal-title { margin: 0; color: var(--bkd-text); font-size: 18px; font-weight: 800; line-height: 1.3; }
  .bkd-modal-copy { margin: 8px 0 0; color: var(--bkd-body); font-size: 13px; font-weight: 500; line-height: 1.55; }
  .bkd-modal-body { padding: 18px 26px 0; }
  .bkd-modal-label {
    display: block; margin-bottom: 7px; color: var(--bkd-muted);
    font-size: 10px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase;
  }
  .bkd-modal textarea {
    width: 100%; min-height: 108px;
    border: 1px solid var(--bkd-border); border-radius: .75rem;
    background: var(--bkd-surface); padding: 11px 12px;
    color: var(--bkd-text); font: inherit; font-size: 13px; outline: none; resize: vertical;
  }
  .bkd-modal textarea:focus { border-color: var(--bkd-primary); box-shadow: 0 0 0 3px rgba(109,76,91,.12); }
  .bkd-modal-error { display: none; margin-top: 7px; color: #991B1B; font-size: 11px; font-weight: 700; }
  .bkd-modal-error.show { display: block; }
  .bkd-modal-actions { display: flex; justify-content: flex-end; gap: 9px; padding: 16px 0 22px; }
  .bkd-modal-actions .bkd-btn { min-width: 112px; padding: 0 16px; }

  /* ── Responsive ── */
  @media (max-width: 900px) {
    .admin-booking-detail-outlet { padding: 16px; }
    .bkd-pay-grid { grid-template-columns: 1fr; }
    .bkd-strip { flex-direction: column; align-items: flex-start; }
    .bkd-strip-item { border-right: 0; padding: 4px 0; }
    .bkd-steps { flex-direction: column; }
    .bkd-step { border-right: 0; border-bottom: 1px solid var(--bkd-border-light); }
    .bkd-step:last-child { border-bottom: 0; }
    .bkd-kv-grid { grid-template-columns: 1fr; }
    .bkd-top { flex-direction: column; align-items: flex-start; }
  }
</style>
<div class="bkd-page">
  <?php
    $bookingStatus = $booking['status'] ?? '';
    $isPendingSupplier = $bookingStatus === 'pending_supplier_response';
    $isPendingPayment = $bookingStatus === 'pending_payment';
    $isPaymentSubmitted = $bookingStatus === 'payment_submitted' || $paymentStatus === 'pending';
    $isConfirmed = in_array($bookingStatus, ['confirmed', 'paid', 'finalized', 'completed'], true);
    $isCancelled = in_array($bookingStatus, ['cancelled', 'cancellation_requested'], true);
    $canCancel = !in_array($bookingStatus, ['cancelled', 'completed'], true);
    $canMarkCompleted = in_array($bookingStatus, ['finalized', 'in_progress'], true);

    // Step progress
    $currentStep = 1;
    if ($isPendingSupplier) $currentStep = 2;
    elseif ($isRemainingPaymentStage) $currentStep = 5;
    elseif ($isPendingPayment || $isPaymentSubmitted) $currentStep = 3;
    elseif (in_array($bookingStatus, ['confirmed', 'paid'], true)) $currentStep = 4;
    elseif (in_array($bookingStatus, ['finalized', 'in_progress'], true)) $currentStep = 5;
    elseif ($bookingStatus === 'completed') $currentStep = 6;
    elseif ($isCancelled) $currentStep = 0;

    $steps = [
        1 => ['label' => 'Created', 'icon' => 'clipboard-list'],
        2 => ['label' => 'Suppliers', 'icon' => 'users'],
        3 => ['label' => 'Deposit', 'icon' => 'wallet'],
        4 => ['label' => 'Confirmed', 'icon' => 'check-circle'],
        5 => ['label' => 'Balance', 'icon' => 'banknote'],
        6 => ['label' => 'Completed', 'icon' => 'party-popper'],
    ];

    $refParts = $bookingRef ? explode('-', $bookingRef, 2) : ['Booking #' . $bookingId, ''];
    $refDisplay = $h($refParts[0]) . (!empty($refParts[1]) ? '-<em>' . $h($refParts[1]) . '</em>' : '');
    $eventDate = $dateOnly($firstEvent['event_date'] ?? null, 'Not scheduled');
    $createdAt = $dateOnly($booking['created_at'] ?? null);

    // Non-addon items for event schedule
    $nonAddonItemIds = [];
    foreach ($items as $item) {
        if (empty($item['package_booking_item_id'])) $nonAddonItemIds[] = (int)$item['id'];
    }
    $eventDetailsByItem = [];
    foreach ($eventDetails as $ed) {
        $bid = (int)($ed['booking_item_id'] ?? 0);
        if ($bid > 0) $eventDetailsByItem[$bid] = $ed;
    }
    foreach ($items as $item) {
        $iid = (int)($item['id'] ?? 0);
        if (!isset($eventDetailsByItem[$iid])) {
            $pid = (int)($item['package_booking_item_id'] ?? 0);
            if ($pid > 0 && isset($eventDetailsByItem[$pid])) $eventDetailsByItem[$iid] = $eventDetailsByItem[$pid];
        }
    }
  ?>

  <!-- ── Compact Header ── -->
  <div class="bkd-top">
    <div class="bkd-top-left">
      <a href="<?= URLROOT ?>/admin/bookings" class="bkd-btn bkd-btn--ghost" style="height:32px;padding:0 10px;font-size:11px">
        <i data-lucide="chevron-left" style="width:14px;height:14px"></i> Back
      </a>
      <span class="bkd-ref"><?= $refDisplay ?></span>
      <span class="bkd-top-meta"><?= $h($custName) ?> · Event <?= $h($eventDate) ?></span>
    </div>
    <div class="bkd-top-right">
      <span class="bkd-badge <?= $badgeClass($bookingStatus) ?>" id="booking-status-value"><?= $h(ucwords(str_replace('_', ' ', $bookingStatus))) ?></span>
      <button type="button" id="copy-ref-btn" class="bkd-btn bkd-btn--ghost" style="height:32px;padding:0 10px;font-size:11px" data-ref="<?= $h($bookingRef) ?>">
        <i data-lucide="copy" style="width:13px;height:13px"></i> Copy ref
      </button>
    </div>
  </div>

  <!-- ── Summary Strip ── -->
  <div class="bkd-strip">
    <div class="bkd-strip-item">
      <span class="bkd-strip-label">Service total</span>
      <span class="bkd-strip-value"><?= $money($totalAmount) ?></span>
    </div>
    <div class="bkd-strip-item">
      <span class="bkd-strip-label">Platform fee (<?= $platformFeePercent ?>%)</span>
      <span class="bkd-strip-value"><?= $money($expectedPlatformFee) ?></span>
    </div>
    <div class="bkd-strip-item">
      <span class="bkd-strip-label">Grand total</span>
      <span class="bkd-strip-value"><?= $money($totalAmount + $expectedPlatformFee) ?></span>
    </div>
    <div class="bkd-strip-item">
      <span class="bkd-strip-label">Customer paid</span>
      <span class="bkd-strip-value is-success" id="booking-paid-value"><?= $money($paidAmount) ?></span>
    </div>
    <div class="bkd-strip-item">
      <span class="bkd-strip-label">Balance due</span>
      <span class="bkd-strip-value <?= $balanceDue > 0 ? 'is-danger' : 'is-success' ?>" id="booking-balance-value"><?= $money($balanceDue) ?></span>
    </div>
    <div class="bkd-strip-item">
      <span class="bkd-strip-label">Items</span>
      <span class="bkd-strip-value"><?= count($items) ?></span>
    </div>
    <div class="bkd-strip-item">
      <span class="bkd-strip-label">Created</span>
      <span class="bkd-strip-value" style="font-size:12px"><?= $h($createdAt) ?></span>
    </div>
  </div>

  <!-- ── Step Progress ── -->
  <div class="bkd-steps">
    <?php foreach ($steps as $num => $step): ?>
      <?php
        $isDone = $num < $currentStep || ($num === $currentStep && $currentStep === 6);
        $isCur = $num === $currentStep && $currentStep < 6;
        $stepClass = $isDone ? 'is-done' : ($isCur ? 'is-current' : '');
      ?>
      <div class="bkd-step <?= $stepClass ?>">
        <span class="bkd-step-num"><?= $isDone ? '✓' : $num ?></span>
        <span><?= $step['label'] ?><?= $isCur ? '<br><span class="bkd-step-sub">Current</span>' : '' ?></span>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- ── Priority Action Area ── -->
  <?php if ($isPaymentSubmitted): ?>
  <!-- Payment submitted — needs review -->
  <div class="bkd-action bkd-action--urgent">
    <div class="bkd-action-head">
      <div class="bkd-action-icon bkd-action-icon--warn"><i data-lucide="alert-circle"></i></div>
      <div>
        <div class="bkd-action-title"><?= $isRemainingPaymentStage ? 'Remaining Payment Submitted' : 'Payment Proof Submitted' ?> — Needs Review</div>
        <div class="bkd-action-sub">Review the payment proof and verify or reject</div>
      </div>
    </div>
    <div class="bkd-action-body">
      <div class="bkd-pay-grid">
        <div>
          <?php if ($slipPath !== ''): ?>
            <a href="<?= URLROOT ?>/<?= $h($slipPath) ?>" target="_blank" class="bkd-proof-link">
              <?php if ($isImageSlip): ?>
                <img src="<?= URLROOT ?>/<?= $h($slipPath) ?>" alt="Payment slip">
              <?php else: ?>
                <span class="bkd-proof-file"><i data-lucide="file-text"></i> Open uploaded document</span>
              <?php endif; ?>
            </a>
          <?php else: ?>
            <div class="bkd-empty">No payment proof uploaded.</div>
          <?php endif; ?>
        </div>
        <div>
          <div class="bkd-kv-grid" style="grid-template-columns:1fr 1fr">
            <div class="bkd-kv">
              <div class="bkd-kv-label">Sent</div>
              <div class="bkd-kv-value"><?= $money($sentAmount) ?></div>
            </div>
            <div class="bkd-kv">
              <div class="bkd-kv-label">Expected</div>
              <div class="bkd-kv-value"><?= $money($isRemainingPaymentStage ? $balanceDue : $expectedPayment) ?></div>
            </div>
            <div class="bkd-kv">
              <div class="bkd-kv-label">Type</div>
              <div class="bkd-kv-value"><?= $isRemainingPaymentStage ? 'Remaining Balance' : 'Deposit + Fee' ?></div>
            </div>
            <div class="bkd-kv">
              <div class="bkd-kv-label">Method</div>
              <div class="bkd-kv-value"><?= $h($paymentMethod ?: '-') ?></div>
            </div>
          </div>
          <div class="bkd-progress">
            <span id="payment-progress-bar" style="width:<?= min(100, max(0, $paidPercent)) ?>%"></span>
          </div>
        </div>
      </div>
      <form id="payment-review-form" class="bkd-review-form" data-booking-id="<?= $bookingId ?>">
        <textarea name="note" placeholder="Admin note (optional)"></textarea>
        <div class="bkd-review-actions">
          <button class="bkd-btn bkd-btn--danger reject-payment-btn" type="button" style="width:100%;justify-content:center">
            <i data-lucide="x-circle"></i> Reject
          </button>
          <button class="bkd-btn bkd-btn--success verify-payment-btn" type="button" style="width:100%;justify-content:center">
            <i data-lucide="circle-check"></i> Verify
          </button>
        </div>
      </form>
      <div id="payment-email-result" style="margin-top:8px;font-size:11px;color:var(--bkd-muted);font-weight:600"></div>
    </div>
  </div>

  <?php elseif ($isPendingSupplier): ?>
  <div class="bkd-action">
    <div class="bkd-action-head">
      <div class="bkd-action-icon bkd-action-icon--neutral"><i data-lucide="clock"></i></div>
      <div>
        <div class="bkd-action-title">Waiting for Supplier Response</div>
        <div class="bkd-action-sub">Suppliers have 48 hours to accept or decline</div>
      </div>
    </div>
  </div>

  <?php elseif ($isPendingPayment): ?>
  <div class="bkd-action">
    <div class="bkd-action-head">
      <div class="bkd-action-icon bkd-action-icon--neutral"><i data-lucide="wallet"></i></div>
      <div>
        <div class="bkd-action-title">Waiting for Customer Payment</div>
        <div class="bkd-action-sub">Expected: <?= $money($expectedPayment) ?> (<?= (int)$depositPercent ?>% deposit + <?= (int)$platformFeePercent ?>% fee)</div>
      </div>
    </div>
  </div>

  <?php elseif ($isRemainingPaymentStage && !$isPaymentSubmitted): ?>
  <div class="bkd-action">
    <div class="bkd-action-head">
      <div class="bkd-action-icon bkd-action-icon--neutral"><i data-lucide="banknote"></i></div>
      <div>
        <div class="bkd-action-title">Awaiting Remaining Balance Payment</div>
        <div class="bkd-action-sub">Customer has not yet submitted the remaining balance of <?= $money($balanceDue) ?></div>
      </div>
    </div>
  </div>

  <?php elseif ($isConfirmed && !$isRemainingPaymentStage): ?>
  <div class="bkd-action bkd-action--success">
    <div class="bkd-action-head">
      <div class="bkd-action-icon bkd-action-icon--success"><i data-lucide="check-circle"></i></div>
      <div>
        <div class="bkd-action-title">Booking Confirmed</div>
        <div class="bkd-action-sub">Payment verified · Suppliers notified · Balance: <?= $money($balanceDue) ?></div>
      </div>
    </div>
    <?php if (!empty($slipPath)): ?>
    <div class="bkd-action-body" style="padding-top:0">
      <details style="margin-top:8px">
        <summary style="cursor:pointer;font-size:11px;font-weight:700;color:var(--bkd-primary)">View deposit slip</summary>
        <a href="<?= URLROOT ?>/<?= $h($slipPath) ?>" target="_blank" class="bkd-proof-link" style="margin-top:8px;display:block">
          <?php if ($isImageSlip): ?>
            <img src="<?= URLROOT ?>/<?= $h($slipPath) ?>" alt="Payment slip" style="max-height:200px">
          <?php else: ?>
            <span class="bkd-proof-file"><i data-lucide="file-text"></i> Open document</span>
          <?php endif; ?>
        </a>
      </details>
    </div>
    <?php endif; ?>
  </div>

  <?php elseif ($isCancelled): ?>
  <div class="bkd-action bkd-action--danger">
    <div class="bkd-action-head">
      <div class="bkd-action-icon bkd-action-icon--danger"><i data-lucide="x-circle"></i></div>
      <div>
        <div class="bkd-action-title">Booking Cancelled</div>
        <div class="bkd-action-sub">This booking has been cancelled</div>
      </div>
    </div>
  </div>

  <?php else: ?>
  <div class="bkd-action bkd-action--success">
    <div class="bkd-action-head">
      <div class="bkd-action-icon bkd-action-icon--success"><i data-lucide="check-circle"></i></div>
      <div>
        <div class="bkd-action-title">Booking <?= ucwords(str_replace('_', ' ', $bookingStatus)) ?></div>
        <div class="bkd-action-sub">Balance: <?= $money($balanceDue) ?></div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- ── Collapsible Sections ── -->
  <div class="bkd-sections">

    <!-- Booked Services -->
    <details class="bkd-section" open>
      <summary>
        <i data-lucide="calendar-check" style="width:16px;height:16px;color:var(--bkd-primary)"></i>
        Booked Services
        <span class="bkd-section-count"><?= count($items) ?></span>
      </summary>
      <div class="bkd-section-body">
        <div class="bkd-table-wrap">
          <table class="bkd-table">
            <thead>
              <tr>
                <th>Service</th>
                <th>Supplier</th>
                <th>Schedule</th>
                <th>Guests</th>
                <th class="is-right">Price</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($items)): ?>
                <tr><td colspan="5"><div class="bkd-empty">No services found.</div></td></tr>
              <?php endif; ?>
              <?php foreach ($items as $item): ?>
                <?php
                  $hallName = trim((string)($item['venue_room_name'] ?? ''));
                  $venueName = trim((string)($item['venue_name'] ?? ''));
                  $date = $dateOnly($item['booking_date'] ?? null);
                  $time = trim($timeOnly($item['start_time'] ?? null) . ' - ' . $timeOnly($item['end_time'] ?? null), ' -');
                  $rentalType = $item['rental_type'] ?? null;
                  $borrowDate = $item['borrow_date'] ?? null;
                  $returnDate = $item['return_date'] ?? null;
                  $isAttireRental = $rentalType !== null;
                  $itemEvent = $eventDetailsByItem[(int)($item['id'] ?? 0)] ?? [];
                  $isAddon = !empty($item['package_booking_item_id']);
                ?>
                <tr>
                  <td>
                    <div class="bkd-table-name"><?= $h($item['service_name'] ?? 'Service') ?></div>
                    <?php if ($isAddon && !empty($item['addon_package_name'])): ?>
                      <span class="bkd-addon-chip"><i data-lucide="link" style="width:10px;height:10px"></i> Add-on for <?= $h($item['addon_package_name']) ?></span>
                    <?php endif; ?>
                    <?php if ($hallName !== '' || $venueName !== ''): ?>
                      <div class="bkd-table-sub"><?= $h(trim($hallName . ($venueName !== '' ? ' · ' . $venueName : ''))) ?></div>
                    <?php endif; ?>
                  </td>
                  <td><span class="bkd-table-name"><?= $h($item['supplier_name'] ?? 'Supplier') ?></span></td>
                  <td>
                    <?php if ($isAttireRental && $rentalType === 'borrow' && $borrowDate): ?>
                      <div class="bkd-table-name"><?= $h($dateOnly($borrowDate)) ?> – <?= $h($dateOnly($returnDate)) ?></div>
                      <div class="bkd-table-sub">Borrow · <?= (int)round((strtotime($returnDate) - strtotime($borrowDate)) / 86400) + 1 ?> days</div>
                    <?php elseif ($isAttireRental && $rentalType === 'buy'): ?>
                      <div class="bkd-table-name"><?= $h($date) ?></div>
                      <div class="bkd-table-sub">Purchase</div>
                    <?php else: ?>
                      <div class="bkd-table-name"><?= $h($date) ?></div>
                      <div class="bkd-table-sub"><?= $h($time !== '' ? $time : '—') ?></div>
                    <?php endif; ?>
                  </td>
                  <td><span class="bkd-table-name"><?= $h($itemEvent['guest_count'] ?? '—') ?></span></td>
                  <td class="is-right"><span class="bkd-table-amount"><?= $money($item['price'] ?? 0) ?></span></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </details>

    <!-- Suppliers -->
    <details class="bkd-section">
      <summary>
        <i data-lucide="store" style="width:16px;height:16px;color:var(--bkd-primary)"></i>
        Suppliers
        <span class="bkd-section-count"><?= count($suppliers) ?></span>
      </summary>
      <div class="bkd-section-body">
        <?php if (empty($suppliers)): ?>
          <div class="bkd-empty">No suppliers assigned.</div>
        <?php else: ?>
          <div class="bkd-sup-list">
            <?php foreach ($suppliers as $supplier): ?>
              <?php
                $sName = (string)($supplier['shop_name'] ?? 'Supplier');
                $sStatus = (string)($supplier['status'] ?? 'pending');
                $serviceName = (string)($supplier['service_name'] ?? $supplier['category_name'] ?? 'Unspecified');
                $replacementTarget = $suppliersById[(int)($supplier['replaced_by_id'] ?? 0)] ?? null;
                $isNeedsReplacement = $sStatus === 'needs_replacement';
                $isReplaced = $sStatus === 'replaced';
                $isReplacement = ($replacementSourceById[(int)($supplier['id'] ?? 0)] ?? null) !== null;
                $replacementRequestId = (int)($supplier['originated_replacement_request_id'] ?? $supplier['replacement_request_id'] ?? 0);
                $stateLabel = $isNeedsReplacement ? 'Needs replacement'
                    : ($isReplaced ? 'Replaced'
                    : ($isReplacement ? 'Replacement assigned'
                    : ucwords(str_replace('_', ' ', $sStatus))));
              ?>
              <div class="bkd-sup-row" style="<?= $isNeedsReplacement ? 'border-color:#e8b66f;background:#fff8ed' : ($isReplaced ? 'opacity:.6' : '') ?>">
                <span class="bkd-dot <?= $supplierStatusDot($sStatus) ?>"></span>
                <div class="bkd-sup-info">
                  <div class="bkd-sup-name"><?= $h($sName) ?></div>
                  <div class="bkd-sup-sub"><?= $h($stateLabel) ?></div>
                  <div class="bkd-sup-svc"><?= $h($serviceName) ?></div>
                  <?php if ($isNeedsReplacement && $replacementRequestId > 0): ?>
                    <a href="<?= URLROOT ?>/admin/replacementPicker/<?= $replacementRequestId ?>" style="display:inline-block;margin-top:6px;font-size:10px;font-weight:800;color:var(--bkd-warn-text);text-decoration:underline">
                      Choose replacement →
                    </a>
                  <?php endif; ?>
                  <?php if ($isReplaced && $replacementTarget): ?>
                    <div class="bkd-sup-svc" style="color:var(--bkd-muted)">Replaced by: <?= $h($replacementTarget['shop_name'] ?? 'Replacement') ?></div>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </details>

    <!-- Event Schedule -->
    <details class="bkd-section">
      <summary>
        <i data-lucide="route" style="width:16px;height:16px;color:var(--bkd-primary)"></i>
        Event Schedule
      </summary>
      <div class="bkd-section-body">
        <?php
          $runSheetEvents = [];
          foreach ($items as $item) {
              $itemId = (int)($item['id'] ?? 0);
              if (!in_array($itemId, $nonAddonItemIds, true)) continue;
              $parentEvent = $eventDetailsByItem[$itemId] ?? [];
              $schedule = $packageSchedules[$itemId] ?? [];
              if (!empty($schedule)) {
                  foreach ($schedule as $event) {
                      $runSheetEvents[] = [
                          'date' => $event['event_date'] ?? ($parentEvent['event_date'] ?? null),
                          'start' => $event['start_time'] ?? null,
                          'end' => $event['end_time'] ?? null,
                          'service' => $event['service_name'] ?? ($item['service_name'] ?? 'Service'),
                          'supplier' => $event['supplier_name'] ?? ($item['supplier_name'] ?? ''),
                          'category' => $event['category_name'] ?? '',
                      ];
                  }
              } elseif (!empty($parentEvent)) {
                  $runSheetEvents[] = [
                      'date' => $parentEvent['event_date'] ?? null,
                      'start' => $parentEvent['start_time'] ?? ($item['start_time'] ?? null),
                      'end' => $parentEvent['end_time'] ?? ($item['end_time'] ?? null),
                      'service' => $item['service_name'] ?? 'Service',
                      'supplier' => $item['supplier_name'] ?? '',
                      'category' => $item['category_name'] ?? '',
                  ];
              }
          }
          usort($runSheetEvents, fn($a, $b) => strcmp(
              (string)($a['date'] ?? '') . ' ' . (string)($a['start'] ?? ''),
              (string)($b['date'] ?? '') . ' ' . (string)($b['start'] ?? '')
          ));
        ?>
        <?php if (empty($runSheetEvents)): ?>
          <div class="bkd-empty">No event schedule recorded.</div>
        <?php else: ?>
          <div class="bkd-table-wrap">
            <table class="bkd-table">
              <thead><tr><th>Date</th><th>Time</th><th>Service</th><th>Supplier</th></tr></thead>
              <tbody>
                <?php foreach ($runSheetEvents as $ev): ?>
                  <tr>
                    <td><span class="bkd-table-name"><?= $h($dateOnly($ev['date'] ?? null, 'TBD')) ?></span></td>
                    <td><span class="bkd-table-name"><?= $h($timeOnly($ev['start'])) ?><?= $ev['end'] ? ' – ' . $h($timeOnly($ev['end'])) : '' ?></span></td>
                    <td>
                      <div class="bkd-table-name"><?= $h($ev['service']) ?></div>
                      <?php if (!empty($ev['category'])): ?><div class="bkd-table-sub"><?= $h($ev['category']) ?></div><?php endif; ?>
                    </td>
                    <td><span class="bkd-table-sub"><?= $h($ev['supplier'] ?: '—') ?></span></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </details>

    <!-- Customer -->
    <details class="bkd-section">
      <summary>
        <i data-lucide="user-circle" style="width:16px;height:16px;color:var(--bkd-primary)"></i>
        Customer
      </summary>
      <div class="bkd-section-body">
        <div class="bkd-cust">
          <div class="bkd-avatar"><?= $h($custInitials) ?></div>
          <div>
            <div class="bkd-cust-name"><?= $h($custName) ?></div>
            <div class="bkd-cust-detail"><?= $h($booking['customer_email'] ?? '—') ?></div>
            <div class="bkd-cust-detail"><?= $h($booking['customer_phone'] ?? '—') ?></div>
          </div>
        </div>
      </div>
    </details>

    <!-- Activity Log -->
    <details class="bkd-section">
      <summary>
        <i data-lucide="history" style="width:16px;height:16px;color:var(--bkd-primary)"></i>
        Activity Log
        <span class="bkd-section-count"><?= count($logs) ?></span>
      </summary>
      <div class="bkd-section-body">
        <?php if (empty($logs)): ?>
          <div class="bkd-empty">No activity yet.</div>
        <?php else: ?>
          <div class="bkd-timeline" id="audit-timeline">
            <?php foreach ($visibleLogs as $log): ?>
              <?php
                $logStatus = (string)($log['new_status'] ?? '');
                $logNote = trim((string)($log['note'] ?? ''));
              ?>
              <div class="bkd-timeline-item">
                <div class="bkd-timeline-dot"><span class="bkd-dot <?= $logDot($logStatus) ?>"></span></div>
                <div class="bkd-timeline-body">
                  <div class="bkd-timeline-title"><?= $h(ucwords(str_replace('_', ' ', $logStatus))) ?></div>
                  <div class="bkd-timeline-time"><?= $h($dateTime($log['created_at'] ?? null)) ?></div>
                  <?php if ($logNote !== ''): ?>
                    <div class="bkd-timeline-time" style="color:var(--bkd-body)"><?= $h($logNote) ?></div>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          <?php if ($showAllLogs): ?>
            <button type="button" id="show-all-logs" class="bkd-toggle-more">Show all <?= count($logs) ?> entries ↓</button>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </details>

  </div>

  <!-- ── Bottom Actions ── -->
  <div class="bkd-bottom">
    <div class="bkd-bottom-left">
      <?php if ($canCancel): ?>
        <form id="admin-cancel-form" class="bkd-cancel-form" style="display:contents">
          <input type="hidden" name="booking_id" value="<?= $bookingId ?>">
          <button class="bkd-btn bkd-btn--danger" type="button" onclick="this.nextElementSibling.style.display=this.nextElementSibling.style.display==='none'?'block':'none';this.style.display='none'">
            <i data-lucide="ban"></i> Cancel booking
          </button>
          <div style="display:none">
            <textarea name="reason" required placeholder="Cancellation reason…" style="width:100%;min-height:50px;padding:8px;border:1px solid var(--bkd-danger-border);border-radius:8px;font-size:12px;font-family:inherit;resize:vertical;margin-bottom:8px"></textarea>
            <label class="bkd-cancel-check">
              <input type="checkbox" name="refund_deposit" value="1" id="refund-deposit-cb">
              Queue refund for processing
            </label>
            <button class="bkd-btn bkd-btn--danger" type="submit" style="margin-top:8px;width:100%;justify-content:center">
              <i data-lucide="ban"></i> Confirm cancellation
            </button>
          </div>
        </form>
      <?php endif; ?>
    </div>
    <div class="bkd-bottom-right">
      <?php if ($canMarkCompleted): ?>
        <button id="mark-completed-btn" class="bkd-btn bkd-btn--success" type="button">
          <i data-lucide="check-circle"></i> Mark as Completed
        </button>
      <?php endif; ?>
      <a href="<?= URLROOT ?>/admin/paymentVerification" class="bkd-btn bkd-btn--ghost">
        <i data-lucide="receipt-text"></i> Payment queue
      </a>
    </div>
  </div>

  <!-- Mark Completed confirmation -->
  <?php if ($canMarkCompleted): ?>
  <div id="complete-confirm-overlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:60;align-items:center;justify-content:center;padding:16px">
    <div style="background:#FFF;border-radius:16px;max-width:400px;width:100%;padding:24px;box-shadow:0 24px 60px rgba(0,0,0,.15)">
      <h3 style="font-size:16px;font-weight:700;color:#111827;margin:0 0 8px">Confirm Completion</h3>
      <p style="font-size:13px;color:#7b5c69;margin:0 0 20px">This will mark the booking as completed and create payout records for all suppliers.</p>
      <div style="display:flex;gap:10px">
        <button onclick="document.getElementById('complete-confirm-overlay').style.display='none'" style="flex:1;min-height:40px;border-radius:10px;border:1px solid #ead8c7;background:transparent;color:#6d4c5b;font-size:13px;font-weight:700;cursor:pointer;font-family:inherit">Cancel</button>
        <button id="confirm-complete-btn" style="flex:1;min-height:40px;border-radius:10px;border:0;background:#065F46;color:#FFF;font-size:13px;font-weight:700;cursor:pointer;font-family:inherit">Yes, Complete</button>
      </div>
    </div>
  </div>
  <script>
  document.getElementById('mark-completed-btn')?.addEventListener('click', () => {
    document.getElementById('complete-confirm-overlay').style.display = 'flex';
  });
  document.getElementById('confirm-complete-btn')?.addEventListener('click', async () => {
    const btn = document.getElementById('confirm-complete-btn');
    btn.disabled = true; btn.textContent = 'Processing…';
    try {
      const fd = new FormData();
      fd.append('booking_id', '<?= $bookingId ?>');
      fd.append('csrf_token', '<?= csrf_token() ?>');
      const resp = await fetch('<?= URLROOT ?>/admin/markBookingCompleted', { method: 'POST', body: fd });
      const data = await resp.json();
      if (data.success) { alert('✓ ' + data.message); location.reload(); }
      else { alert('✕ ' + (data.error || 'Failed')); btn.disabled = false; btn.textContent = 'Yes, Complete'; }
    } catch (e) { alert('✕ Network error'); btn.disabled = false; btn.textContent = 'Yes, Complete'; }
  });
  </script>
  <?php endif; ?>

  <!-- Refund Status -->
  <?php if ($refund && ($bookingStatus === 'cancelled')): ?>
  <div style="margin-top:16px;padding:14px 18px;background:var(--bkd-info-bg);border:1px solid #c7d2fe;border-radius:12px;font-size:12px;color:var(--bkd-info-text)">
    <strong>Refund:</strong>
    <?= ucfirst((string)($refund['status'] ?? 'pending')) ?> · <?= $money($refund['amount'] ?? 0) ?>
    <?php if (in_array((string)($refund['status'] ?? ''), ['pending', 'processing'], true)): ?>
      <button type="button" class="bkd-btn bkd-btn--primary" style="margin-left:12px;height:28px;font-size:10px;padding:0 10px" onclick="openRefundProcessModal()">
        <i data-lucide="upload"></i> Process
      </button>
    <?php endif; ?>
  </div>
  <?php endif; ?>

</div>

<!-- Process Refund Modal -->
<div class="bkd-modal-backdrop" id="refundProcessModal" aria-hidden="true">
  <div class="bkd-modal" role="dialog" aria-modal="true" style="max-width:480px">
    <div class="bkd-modal-head">
      <div class="bkd-modal-icon" aria-hidden="true"><i data-lucide="undo-2"></i></div>
      <div>
        <h3 id="refundProcessTitle" style="font-size:16px;font-weight:700;margin:0 0 4px">Process Refund</h3>
        <p class="bkd-modal-copy">Upload proof of the bank transfer to the customer.</p>
      </div>
    </div>
    <div class="bkd-modal-body">
      <div id="refundProcessError" class="bkd-modal-error">Please fill in all required fields.</div>
      <input type="hidden" id="rpRefundId" value="<?= $refund['id'] ?? '' ?>">
      <label class="bkd-modal-label" for="rpBank">Bank / Payment Channel <span style="color:var(--bkd-danger-text,#b94b4b)">*</span></label>
      <select id="rpBank" style="width:100%;padding:10px 12px;border:1px solid var(--bkd-border,#ead8c7);border-radius:8px;font-size:13px;font-family:inherit">
        <option value="">Select bank…</option>
        <option value="KBZ Pay">KBZ Pay</option>
        <option value="Wave Money">Wave Money</option>
        <option value="AYA Pay">AYA Pay</option>
        <option value="Yoma Bank">Yoma Bank</option>
        <option value="CB Bank">CB Bank</option>
      </select>
      <label class="bkd-modal-label" for="rpTxnRef" style="margin-top:10px">Transaction Reference</label>
      <input type="text" id="rpTxnRef" placeholder="e.g. TXN123456789" style="width:100%;padding:10px 12px;border:1px solid var(--bkd-border,#ead8c7);border-radius:8px;font-size:13px;font-family:inherit">
      <label class="bkd-modal-label" for="rpSlip" style="margin-top:10px">Proof of Transfer (JPG, PNG, WebP, or PDF)</label>
      <input type="file" id="rpSlip" accept=".jpg,.jpeg,.png,.webp,.pdf" style="font-size:12px">
      <label class="bkd-modal-label" for="rpNote" style="margin-top:10px">Note (optional)</label>
      <textarea id="rpNote" placeholder="Any details…" style="width:100%;min-height:50px;padding:10px 12px;border:1px solid var(--bkd-border,#ead8c7);border-radius:8px;font-size:13px;font-family:inherit;resize:vertical"></textarea>
      <div class="bkd-modal-actions">
        <button type="button" class="bkd-btn bkd-btn--ghost" onclick="closeRefundProcessModal()">Cancel</button>
        <button type="button" class="bkd-btn bkd-btn--primary" id="rpConfirmBtn" onclick="submitRefundProcess()">
          <i data-lucide="upload"></i> Submit Proof
        </button>
      </div>
    </div>
  </div>
</div>

<div class="bkd-modal-backdrop" id="rejectPaymentModal" aria-hidden="true">
  <div class="bkd-modal" role="dialog" aria-modal="true" aria-labelledby="rejectPaymentTitle">
    <div class="bkd-modal-head">
      <div class="bkd-modal-icon" aria-hidden="true">
        <i data-lucide="x-circle"></i>
      </div>
      <div>
        <h2 class="bkd-modal-title" id="rejectPaymentTitle">Reject payment proof</h2>
        <p class="bkd-modal-copy">Add a clear reason for the customer before returning this payment for resubmission.</p>
      </div>
    </div>
    <div class="bkd-modal-body">
      <label class="bkd-modal-label" for="rejectPaymentReason">Reason</label>
      <textarea id="rejectPaymentReason" placeholder="Example: The transferred amount does not match the required deposit plus platform fee."></textarea>
      <div class="bkd-modal-error" id="rejectPaymentError">Please enter a rejection reason.</div>
      <div class="bkd-modal-actions">
        <button type="button" class="bkd-btn bkd-btn--ghost" id="rejectPaymentCancel">Cancel</button>
        <button type="button" class="bkd-btn bkd-btn--danger" id="rejectPaymentConfirm">
          <i data-lucide="x-circle"></i> Reject proof
        </button>
      </div>
    </div>
  </div>
</div>

<div id="toast" class="fixed right-4 top-4 z-50 max-w-sm -translate-y-2 opacity-0 pointer-events-none transition-all duration-300"></div>

<script>
(function(){
  function showToast(message, type) {
    type = type || 'success';
    var toast = document.getElementById('toast');
    toast.textContent = message;
    toast.className = 'fixed right-4 top-4 z-50 max-w-sm opacity-100 pointer-events-auto transition-all duration-300 translate-y-0 rounded-lg border px-4 py-3 text-sm font-bold';
    if (type === 'error') {
      toast.classList.add('border-rose-200', 'bg-rose-50', 'text-rose-800');
    } else {
      toast.classList.add('border-emerald-200', 'bg-emerald-50', 'text-emerald-800');
    }
    setTimeout(function(){
      toast.classList.remove('opacity-100', 'pointer-events-auto', 'translate-y-0');
      toast.classList.add('opacity-0', 'pointer-events-none', '-translate-y-2');
    }, 3500);
  }

  /* Copy ref */
  var copyBtn = document.getElementById('copy-ref-btn');
  if (copyBtn) {
    copyBtn.addEventListener('click', function(){
      navigator.clipboard.writeText(copyBtn.dataset.ref).then(function(){
        showToast('Booking reference copied.');
      }).catch(function(){
        showToast('Could not copy. Please try manually.', 'error');
      });
    });
  }

  /* Payment review */
  var paymentReviewForm = document.getElementById('payment-review-form');
  if (paymentReviewForm) {
    document.addEventListener('click', async function(event){
      if (event.target.matches('.verify-payment-btn') || event.target.closest('.verify-payment-btn')) {
        await handlePaymentReview(paymentReviewForm, true);
      }
      if (event.target.matches('.reject-payment-btn') || event.target.closest('.reject-payment-btn')) {
        await handlePaymentReview(paymentReviewForm, false);
      }
    });
  }

  function requestRejectPaymentReason() {
    var modal = document.getElementById('rejectPaymentModal');
    var textarea = document.getElementById('rejectPaymentReason');
    var error = document.getElementById('rejectPaymentError');
    var cancelBtn = document.getElementById('rejectPaymentCancel');
    var confirmBtn = document.getElementById('rejectPaymentConfirm');
    if (!modal || !textarea || !cancelBtn || !confirmBtn) return Promise.resolve('');

    return new Promise(function(resolve) {
      var lastFocused = document.activeElement;
      var settled = false;

      function close(value) {
        if (settled) return;
        settled = true;
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.removeEventListener('keydown', onKeydown);
        modal.removeEventListener('click', onBackdropClick);
        cancelBtn.removeEventListener('click', onCancel);
        confirmBtn.removeEventListener('click', onConfirm);
        if (lastFocused && typeof lastFocused.focus === 'function') lastFocused.focus();
        resolve(value);
      }

      function onCancel() {
        close('');
      }

      function onConfirm() {
        var reason = textarea.value.trim();
        if (reason === '') {
          error.classList.add('show');
          textarea.focus();
          return;
        }
        close(reason);
      }

      function onKeydown(event) {
        if (event.key === 'Escape') close('');
        if ((event.metaKey || event.ctrlKey) && event.key === 'Enter') onConfirm();
      }

      function onBackdropClick(event) {
        if (event.target === modal) close('');
      }

      textarea.value = '';
      error.classList.remove('show');
      modal.classList.add('is-open');
      modal.setAttribute('aria-hidden', 'false');
      cancelBtn.addEventListener('click', onCancel);
      confirmBtn.addEventListener('click', onConfirm);
      document.addEventListener('keydown', onKeydown);
      modal.addEventListener('click', onBackdropClick);
      setTimeout(function(){ textarea.focus(); }, 30);
    });
  }

  async function handlePaymentReview(form, approve) {
    var bookingId = form.dataset.bookingId;
    var note = form.querySelector('textarea[name="note"]').value;
    var endpoint = approve
      ? '<?= URLROOT ?>/admin/verifyPaymentPost'
      : '<?= URLROOT ?>/admin/rejectPaymentSlipPost';
    var formData = new FormData();
    formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]')?.content || '');
    formData.append('booking_id', bookingId);
    formData.append('note', note);

    if (!approve) {
      var reason = await requestRejectPaymentReason();
      if (!reason) return;
      formData.set('reason', reason);
    }

    var actionButton = approve
      ? document.querySelector('.verify-payment-btn')
      : form.querySelector('.reject-payment-btn');
    if (actionButton) {
      actionButton.disabled = true;
      actionButton.dataset.originalText = actionButton.textContent;
      actionButton.textContent = approve ? 'Verifying…' : 'Rejecting…';
    }

    try {
      var response = await fetch(endpoint, { method: 'POST', body: formData });
      var data = await response.json();
      if (data.success) {
        showToast(data.message || 'Payment review saved.', data.email_sent === false ? 'error' : 'success');
        if (approve) {
          updateVerifiedPaymentState(data, form);
        } else {
          setTimeout(function(){ window.location.reload(); }, 900);
        }
      } else {
        showToast(data.error || 'Could not update payment.', 'error');
        if (actionButton) {
          actionButton.disabled = false;
          actionButton.textContent = actionButton.dataset.originalText || (approve ? 'Approve payment + notify' : 'Reject');
        }
      }
    } catch (e) {
      showToast('Connection error. Please try again.', 'error');
      if (actionButton) {
        actionButton.disabled = false;
        actionButton.textContent = actionButton.dataset.originalText || (approve ? 'Approve payment + notify' : 'Reject');
      }
    }
  }

  function updateVerifiedPaymentState(data, form) {
    var fmt = function(v) { return new Intl.NumberFormat('en-US', {maximumFractionDigits:0}).format(Number(v||0)) + ' MMK'; };
    var statusLabel = String(data.booking_status || 'paid').replace(/_/g, ' ').replace(/\b\w/g, function(c){return c.toUpperCase();});
    var paid = Number(data.paid_amount || 0);
    var total = Number(data.total_amount || 0);
    var percent = total > 0 ? Math.min(100, Math.round((paid / total) * 100)) : 0;
    var balance = Math.max(0, total - paid);

    var el = function(id) { return document.getElementById(id); };
    if (el('booking-status-value')) el('booking-status-value').textContent = statusLabel;
    if (el('booking-paid-value'))    el('booking-paid-value').textContent = fmt(paid);
    if (el('booking-paid-percent'))  el('booking-paid-percent').textContent = percent + '% collected';
    if (el('booking-balance-value')) el('booking-balance-value').textContent = fmt(balance);
    if (el('payment-progress-bar'))  el('payment-progress-bar').style.width = percent + '%';
    if (el('payment-status-badge')) {
      el('payment-status-badge').textContent = 'Success';
      el('payment-status-badge').className = 'bkd-badge bkd-badge--success';
    }
    if (el('payment-email-result')) {
      el('payment-email-result').textContent = data.email_sent
        ? 'Verification email sent to ' + (data.email_to || 'the customer') + '.'
        : 'Payment was verified, but the customer email could not be sent.';
    }
    var verifyBtn = document.querySelector('.verify-payment-btn');
    if (verifyBtn) verifyBtn.remove();
    form.remove();
  }

  /* Cancel booking */
  var cancelForm = document.getElementById('admin-cancel-form');
  if (cancelForm) {
    cancelForm.addEventListener('submit', async function(event){
      event.preventDefault();
      var refundCb = document.getElementById('refund-deposit-cb');
      var refundAmt = '<?= $refundEstimate ? $money($refundEstimate[0]) : '' ?>';
      var msg = 'Are you sure you want to cancel this booking? This cannot be undone.';
      if (refundCb && refundCb.checked && refundAmt) {
        msg += '\n\nA refund of ' + refundAmt + ' will be queued for processing.';
      }
      if (!confirm(msg)) return;
      var cancelData = new FormData(cancelForm);
      cancelData.set('csrf_token', document.querySelector('meta[name="csrf-token"]')?.content || '');
      var response = await fetch('<?= URLROOT ?>/admin/bookingCancel', { method: 'POST', body: cancelData });
      var data = await response.json().catch(function(){ return {}; });
      if (data.success) window.location.reload();
      else showToast(data.error || 'Could not cancel booking.', 'error');
    });
  }

  /* Show all logs toggle */
  var showAllBtn = document.getElementById('show-all-logs');
  if (showAllBtn) {
    var timeline = document.getElementById('audit-timeline');
    var allLogsJson = <?= json_encode(array_map(function($log) use ($h, $dateTime, $logDot) {
        $s = (string)($log['new_status'] ?? '');
        $n = trim((string)($log['note'] ?? ''));
        return [
            'status' => $h(ucwords(str_replace('_', ' ', $s))),
            'time' => $h($dateTime($log['created_at'] ?? null)),
            'note' => $n !== '' ? $h($n) : '',
            'dot' => $logDot($s),
        ];
    }, array_reverse($logs ?: [])), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    var expanded = false;

    showAllBtn.addEventListener('click', function(){
      if (!expanded) {
        timeline.innerHTML = allLogsJson.map(function(l){
          return '<div class="bkd-timeline-item">'
            + '<div class="bkd-timeline-dot"><span class="bkd-dot ' + l.dot + '"></span></div>'
            + '<div class="bkd-timeline-body">'
            + '<div class="bkd-timeline-title">' + l.status + '</div>'
            + '<div class="bkd-timeline-time">' + l.time + '</div>'
            + (l.note ? '<div class="bkd-timeline-time" style="color:var(--bkd-body)">' + l.note + '</div>' : '')
            + '</div></div>';
        }).join('');
        showAllBtn.textContent = 'Show less ↑';
        expanded = true;
      } else {
        window.location.reload();
      }
    });
  }

  /* ── Refund Process Modal ── */
  var CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';
  var ROOT = '<?= URLROOT ?>';

  window.openRefundProcessModal = function() {
    var bank = document.getElementById('rpBank');
    var txnRef = document.getElementById('rpTxnRef');
    var note = document.getElementById('rpNote');
    var slip = document.getElementById('rpSlip');
    var err = document.getElementById('refundProcessError');
    if (bank) bank.value = '';
    if (txnRef) txnRef.value = '';
    if (note) note.value = '';
    if (slip) slip.value = '';
    if (err) err.classList.remove('show');
    var modal = document.getElementById('refundProcessModal');
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
  };

  window.closeRefundProcessModal = function() {
    var modal = document.getElementById('refundProcessModal');
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
  };

  window.submitRefundProcess = async function() {
    var refundId = document.getElementById('rpRefundId').value;
    var bank = document.getElementById('rpBank').value;
    var txnRef = document.getElementById('rpTxnRef').value.trim();
    var slip = document.getElementById('rpSlip').files[0];
    var note = document.getElementById('rpNote').value.trim();
    var err = document.getElementById('refundProcessError');
    var btn = document.getElementById('rpConfirmBtn');

    if (!bank) { err.textContent = 'Please select a bank.'; err.classList.add('show'); return; }

    var fd = new FormData();
    fd.append('csrf_token', CSRF);
    fd.append('refund_id', refundId);
    fd.append('bank_name', bank);
    fd.append('transaction_ref', txnRef);
    fd.append('note', note);
    if (slip) fd.append('slip_image', slip);

    btn.disabled = true; btn.textContent = 'Submitting…';

    try {
      var resp = await fetch(ROOT + '/admin/processRefundPost', { method: 'POST', body: fd });
      var data = await resp.json();
      if (data.success) {
        showToast(data.message || 'Refund updated.');
        closeRefundProcessModal();
        setTimeout(function(){ window.location.reload(); }, 800);
      } else {
        err.textContent = data.error || 'Something went wrong.';
        err.classList.add('show');
      }
    } catch(e) {
      err.textContent = 'Network error.';
      err.classList.add('show');
    }
    btn.disabled = false; btn.innerHTML = '<i data-lucide="upload"></i> Submit Proof';
    lucide.createIcons();
  };

  /* Dismiss refund modal on backdrop / Escape */
  var rpModal = document.getElementById('refundProcessModal');
  if (rpModal) {
    rpModal.addEventListener('click', function(e) { if (e.target === rpModal) closeRefundProcessModal(); });
  }

  lucide.createIcons();
})();
</script>
<?php
};
?>
<!DOCTYPE html>
<html lang="en">
<head><?php require_once APPROOT . '/views/dashboardLayout/head.php'; ?></head>
<body class="grid h-screen grid-cols-[280px_1fr] gap-0 bg-app-page">
  <?php require APPROOT . '/views/dashboardLayout/sidebar.php'; ?>
</body>
</html>
