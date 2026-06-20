<?php
$booking = $booking ?? [];
$items = $items ?? [];
$suppliers = $suppliers ?? [];
$eventDetails = $eventDetails ?? [];
$logs = $logs ?? [];
$payments = $payments ?? [];
$packageSchedules = $packageSchedules ?? [];
$bookingRef = $bookingRef ?? '';
$depositPercent = (float)($depositPercent ?? 10);

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
$balanceDue = max(0, $totalAmount - $paidAmount);
$paidPercent = $totalAmount > 0 ? round(($paidAmount / $totalAmount) * 100) : 0;

$depositPayments = array_values(array_filter($payments, static fn($p) => ($p['type'] ?? '') === 'deposit'));
$pendingDeposits = array_values(array_filter($depositPayments, static fn($p) => ($p['status'] ?? '') === 'pending'));
$reviewPayment = $pendingDeposits[count($pendingDeposits) - 1] ?? ($depositPayments[count($depositPayments) - 1] ?? ($payments[count($payments) - 1] ?? []));

$paymentStatus = (string)($reviewPayment['status'] ?? ($booking['payment_status'] ?? 'unpaid'));
$paymentMethod = (string)($reviewPayment['bank_name'] ?? $reviewPayment['method'] ?? '-');
$transactionRef = (string)($reviewPayment['transaction_ref'] ?? '');
$sentAmount = (float)($reviewPayment['paid_amount'] ?? $reviewPayment['amount'] ?? $paidAmount);
$slipPath = trim((string)($reviewPayment['payment_slip_path'] ?? ''));
$slipExt = strtolower(pathinfo($slipPath, PATHINFO_EXTENSION));
$isImageSlip = $slipPath !== '' && in_array($slipExt, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true);
$isAwaitingReview = in_array(($booking['status'] ?? ''), ['payment_submitted'], true) || $paymentStatus === 'pending';

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
        'pending', 'pending_supplier_response' => 'bkd-dot--warn',
        'rejected', 'cancelled' => 'bkd-dot--danger',
        default => 'bkd-dot--neutral',
    };
};

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
    $logDot,
    $showAllLogs,
    $visibleLogs
) {
    $bookingId = (int)($booking['id'] ?? 0);
    $createdAt = $dateOnly($booking['created_at'] ?? null);
    $canCancel = !in_array(($booking['status'] ?? ''), ['cancelled', 'completed'], true);
    $canMarkReceived = !in_array(($booking['status'] ?? ''), ['payment_verified', 'paid', 'confirmed', 'pending_final_payment', 'finalized', 'completed', 'cancelled'], true);
?>
<style>
  /* ── Booking Detail — Admin Redesign ── */
  .admin-booking-detail-outlet {
    min-height: 100%;
    background: #FBFBF9;
    padding: 32px 36px;
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
    color: #111827;
    font-size: 13px;
    overflow-y: auto;
    -webkit-font-smoothing: antialiased;
  }

  .bkd-page {
    --bkd-surface: #ffffff;
    --bkd-soft: #faf5ef;
    --bkd-border: #ead8c7;
    --bkd-border-light: #eddecc;
    --bkd-primary: #6d4c5b;
    --bkd-primary-soft: #eddecc;
    --bkd-primary-hover: #7b5c69;
    --bkd-text: #111827;
    --bkd-muted: #b79c8b;
    --bkd-body: #7b5c69;
    --bkd-success-bg: #d1fae5;
    --bkd-success-text: #065f46;
    --bkd-success-border: #059669;
    --bkd-warn-bg: #fef3c7;
    --bkd-warn-text: #92400e;
    --bkd-warn-border: #d97706;
    --bkd-danger-bg: #fee2e2;
    --bkd-danger-text: #991b1b;
    --bkd-danger-border: #dc2626;
    --bkd-info-bg: #e8e7ff;
    --bkd-info-text: #4f46a5;
    --bkd-neutral-bg: #f3f4f6;
    --bkd-neutral-text: #57534e;
    max-width: 1600px;
    margin: 0 auto;
  }
  .bkd-page * { box-sizing: border-box; }

  /* ── Header ── */
  .bkd-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 24px;
  }
  .bkd-header-left { min-width: 0; }
  .bkd-eyebrow {
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .12em;
    text-transform: uppercase;
    color: var(--bkd-muted);
    margin: 0 0 4px;
  }
  .bkd-ref {
    font-size: 24px;
    font-weight: 700;
    color: var(--bkd-text);
    letter-spacing: -.02em;
    line-height: 1.2;
    margin: 0;
    overflow-wrap: anywhere;
  }
  .bkd-ref em {
    font-style: normal;
    color: var(--bkd-muted);
    font-weight: 400;
  }
  .bkd-subtitle {
    margin-top: 6px;
    color: var(--bkd-body);
    font-size: 12px;
    font-weight: 600;
  }
  .bkd-header-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    flex-shrink: 0;
    align-items: center;
  }

  /* ── Buttons ── */
  .bkd-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 0 15px;
    height: 36px;
    border-radius: .75rem;
    font-size: 12px;
    font-weight: 700;
    font-family: inherit;
    cursor: pointer;
    transition: background .15s, border-color .15s, box-shadow .15s;
    text-decoration: none;
    white-space: nowrap;
  }
  .bkd-btn svg { width: 14px; height: 14px; flex-shrink: 0; }
  .bkd-btn--ghost {
    border: 1px solid var(--bkd-border);
    background: var(--bkd-surface);
    color: var(--bkd-primary);
  }
  .bkd-btn--ghost:hover {
    background: var(--bkd-primary-soft);
    border-color: var(--bkd-primary);
  }
  .bkd-btn--primary {
    border: 1px solid var(--bkd-primary);
    background: var(--bkd-primary);
    color: #fff;
    font-weight: 800;
  }
  .bkd-btn--primary:hover {
    background: var(--bkd-primary-hover);
    box-shadow: 0 2px 8px rgba(109, 76, 91, .25);
  }
  .bkd-btn--success {
    border: 1px solid var(--bkd-success-border);
    background: var(--bkd-success-text);
    color: #fff;
    font-weight: 800;
  }
  .bkd-btn--success:hover {
    background: #047857;
    box-shadow: 0 2px 8px rgba(5, 150, 105, .3);
  }
  .bkd-btn--danger {
    border: 1px solid var(--bkd-danger-border);
    background: var(--bkd-danger-text);
    color: #fff;
    font-weight: 800;
  }
  .bkd-btn--danger:hover {
    background: #7f1d1d;
    box-shadow: 0 2px 8px rgba(153, 27, 27, .25);
  }

  /* ── Stats band ── */
  .bkd-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    margin-bottom: 24px;
  }
  .bkd-stat {
    background: var(--bkd-surface);
    border: 1px solid var(--bkd-border);
    border-left-width: 3px;
    border-radius: .75rem;
    padding: 16px 18px;
    transition: box-shadow .15s;
  }
  .bkd-stat:hover { box-shadow: 0 2px 8px rgba(28, 25, 23, .06); }
  .bkd-stat--primary { border-left-color: var(--bkd-primary); }
  .bkd-stat--neutral { border-left-color: #a8a29e; }
  .bkd-stat--success { border-left-color: var(--bkd-success-border); }
  .bkd-stat--danger { border-left-color: var(--bkd-danger-border); }
  .bkd-stat-label {
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: var(--bkd-muted);
    margin-bottom: 6px;
  }
  .bkd-stat-value {
    font-size: 24px;
    font-weight: 700;
    line-height: 1.1;
    color: var(--bkd-text);
    letter-spacing: -.02em;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .bkd-stat-value.is-success { color: var(--bkd-success-text); }
  .bkd-stat-value.is-danger  { color: var(--bkd-danger-text); }
  .bkd-stat-value.is-primary { color: var(--bkd-primary); }
  .bkd-stat-sub {
    font-size: 11px;
    color: var(--bkd-muted);
    margin-top: 4px;
  }

  /* ── 2-column body ── */
  .bkd-body {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 380px;
    gap: 20px;
    align-items: start;
  }
  .bkd-main, .bkd-side { display: grid; gap: 20px; }

  /* ── Cards ── */
  .bkd-card {
    background: var(--bkd-surface);
    border: 1px solid var(--bkd-border);
    border-radius: .75rem;
    overflow: hidden;
    box-shadow: 0 1px 2px rgba(28, 25, 23, .04);
  }
  .bkd-card--highlight {
    border-left: 3px solid var(--bkd-warn-border);
  }
  .bkd-card-head {
    padding: 14px 20px;
    border-bottom: 1px solid var(--bkd-border-light);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
  }
  .bkd-card-head-left {
    display: flex;
    align-items: center;
    gap: 8px;
  }
  .bkd-card-icon {
    width: 30px;
    height: 30px;
    border-radius: .75rem;
    background: var(--bkd-primary-soft);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--bkd-primary);
    flex-shrink: 0;
  }
  .bkd-card-icon svg { width: 15px; height: 15px; }
  .bkd-card-icon--warn {
    background: var(--bkd-warn-bg);
    color: var(--bkd-warn-text);
  }
  .bkd-card-title {
    font-size: 13px;
    font-weight: 700;
    color: var(--bkd-text);
  }
  .bkd-card-meta {
    font-size: 11px;
    color: var(--bkd-muted);
    font-weight: 600;
    white-space: nowrap;
  }
  .bkd-card-body { padding: 16px 20px; }

  /* ── Badges ── */
  .bkd-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    border-radius: 20px;
    padding: 3px 10px;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .04em;
    text-transform: uppercase;
    white-space: nowrap;
  }
  .bkd-badge::before {
    content: '';
    width: 6px;
    height: 6px;
    border-radius: 999px;
    background: currentColor;
    flex-shrink: 0;
  }
  .bkd-badge--success { background: var(--bkd-success-bg); color: var(--bkd-success-text); }
  .bkd-badge--warn    { background: var(--bkd-warn-bg);    color: var(--bkd-warn-text); }
  .bkd-badge--danger  { background: var(--bkd-danger-bg);  color: var(--bkd-danger-text); }
  .bkd-badge--info    { background: var(--bkd-info-bg);    color: var(--bkd-info-text); }
  .bkd-badge--neutral { background: var(--bkd-neutral-bg); color: var(--bkd-neutral-text); }

  /* ── KV grid ── */
  .bkd-kv-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
  }
  .bkd-kv {
    border: 1px solid var(--bkd-border-light);
    border-radius: .75rem;
    background: var(--bkd-soft);
    padding: 12px;
  }
  .bkd-kv-label {
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: var(--bkd-muted);
    margin-bottom: 4px;
  }
  .bkd-kv-value {
    color: var(--bkd-text);
    font-size: 13px;
    font-weight: 700;
    overflow-wrap: anywhere;
  }
  .bkd-kv-sub {
    margin-top: 2px;
    color: var(--bkd-body);
    font-size: 11px;
    font-weight: 600;
  }

  /* ── Payment progress ── */
  .bkd-progress {
    height: 6px;
    border-radius: 999px;
    background: var(--bkd-soft);
    overflow: hidden;
    margin-top: 12px;
  }
  .bkd-progress span {
    display: block;
    height: 100%;
    border-radius: 999px;
    background: var(--bkd-primary);
    transition: width .4s ease;
  }

  /* ── Table ── */
  .bkd-table-wrap { overflow-x: auto; }
  .bkd-table { width: 100%; border-collapse: collapse; }
  .bkd-table thead tr { background: var(--bkd-soft); }
  .bkd-table th {
    padding: 10px 20px;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: var(--bkd-muted);
    text-align: left;
    white-space: nowrap;
  }
  .bkd-table th:last-child,
  .bkd-table th.is-right { text-align: right; }
  .bkd-table tbody tr {
    border-top: 1px solid var(--bkd-border-light);
    transition: background .1s;
  }
  .bkd-table tbody tr:hover { background: var(--bkd-soft); }
  .bkd-table td {
    padding: 14px 20px;
    vertical-align: middle;
  }
  .bkd-table td:last-child,
  .bkd-table td.is-right { text-align: right; }
  .bkd-table-name {
    font-weight: 700;
    color: var(--bkd-text);
    font-size: 13px;
  }
  .bkd-table-sub {
    font-size: 11px;
    color: var(--bkd-muted);
    margin-top: 2px;
  }
  .bkd-table-amount {
    font-weight: 700;
    color: var(--bkd-text);
    white-space: nowrap;
  }

  /* ── Add-on chip ── */
  .bkd-addon-chip {
    display: inline-flex;
    align-items: center;
    gap: 3px;
    font-size: 10px;
    font-weight: 600;
    padding: 1px 7px;
    border-radius: 999px;
    background: var(--bkd-info-bg);
    color: var(--bkd-info-text);
    margin-top: 3px;
  }

  /* ── Payment proof ── */
  .bkd-proof-link {
    display: block;
    overflow: hidden;
    border: 1px solid var(--bkd-border);
    border-radius: .75rem;
    background: var(--bkd-surface);
    text-decoration: none;
    transition: box-shadow .12s;
  }
  .bkd-proof-link:hover { box-shadow: 0 2px 8px rgba(28, 25, 23, .08); }
  .bkd-proof-link img {
    width: 100%;
    max-height: 220px;
    object-fit: contain;
    background: #fff;
  }
  .bkd-proof-file {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 120px;
    color: var(--bkd-primary);
    font-size: 12px;
    font-weight: 700;
    gap: 6px;
  }
  .bkd-proof-file svg { width: 28px; height: 28px; }

  /* ── Review form ── */
  .bkd-review-form {
    border: 1px solid var(--bkd-warn-border);
    border-radius: .75rem;
    background: var(--bkd-warn-bg);
    padding: 14px;
    margin-top: 12px;
  }
  .bkd-review-form textarea {
    width: 100%;
    min-height: 70px;
    border: 1px solid var(--bkd-border);
    border-radius: .75rem;
    background: var(--bkd-surface);
    padding: 10px 12px;
    color: var(--bkd-text);
    font: inherit;
    font-size: 12px;
    outline: none;
    resize: vertical;
  }
  .bkd-review-form textarea:focus { border-color: var(--bkd-primary); }
  .bkd-review-actions {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
    margin-top: 8px;
  }

  /* ── Sidebar elements ── */
  .bkd-side-list { display: grid; gap: 6px; }
  .bkd-side-person {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    border: 1px solid var(--bkd-border-light);
    border-radius: .75rem;
    background: var(--bkd-soft);
  }
  .bkd-avatar {
    display: grid;
    place-items: center;
    width: 36px;
    height: 36px;
    border-radius: 999px;
    background: var(--bkd-primary-soft);
    color: var(--bkd-primary);
    font-size: 13px;
    font-weight: 800;
    flex-shrink: 0;
  }
  .bkd-side-row {
    display: flex;
    align-items: center;
    gap: 10px;
    border: 1px solid var(--bkd-border-light);
    border-radius: .75rem;
    background: var(--bkd-soft);
    padding: 9px 12px;
  }
  .bkd-side-row-main { flex: 1; min-width: 0; }
  .bkd-side-row-title {
    font-size: 12px;
    font-weight: 700;
    color: var(--bkd-text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .bkd-side-row-sub {
    font-size: 10px;
    color: var(--bkd-muted);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .04em;
  }

  /* ── Status dots ── */
  .bkd-dot {
    display: inline-block;
    width: 7px;
    height: 7px;
    border-radius: 999px;
    flex-shrink: 0;
  }
  .bkd-dot--success { background: #059669; }
  .bkd-dot--warn    { background: #d97706; }
  .bkd-dot--danger  { background: #dc2626; }
  .bkd-dot--neutral { background: #a8a29e; }

  /* ── Timeline ── */
  .bkd-timeline { display: grid; }
  .bkd-timeline-item {
    display: grid;
    grid-template-columns: 16px 1fr;
    gap: 8px;
  }
  .bkd-timeline-dot {
    display: flex;
    flex-direction: column;
    align-items: center;
  }
  .bkd-timeline-dot::after {
    content: '';
    width: 1px;
    flex: 1;
    background: var(--bkd-border-light);
    margin-top: 5px;
  }
  .bkd-timeline-item:last-child .bkd-timeline-dot::after { display: none; }
  .bkd-timeline-body { padding-bottom: 12px; }
  .bkd-timeline-title {
    font-weight: 700;
    color: var(--bkd-text);
    font-size: 12px;
  }
  .bkd-timeline-time {
    font-size: 11px;
    color: var(--bkd-muted);
    margin-top: 1px;
  }

  /* ── Cancel form ── */
  .bkd-cancel-form { display: grid; gap: 10px; }
  .bkd-cancel-form textarea {
    width: 100%;
    min-height: 70px;
    border: 1px solid var(--bkd-danger-border);
    border-radius: .75rem;
    background: var(--bkd-surface);
    padding: 10px 12px;
    color: var(--bkd-text);
    font: inherit;
    font-size: 12px;
    outline: none;
    resize: vertical;
  }
  .bkd-cancel-form textarea:focus { border-color: var(--bkd-danger-text); }
  .bkd-cancel-check {
    display: flex;
    align-items: center;
    gap: 8px;
    color: var(--bkd-body);
    font-size: 12px;
    font-weight: 600;
  }

  /* ── Empty state ── */
  .bkd-empty {
    padding: 32px 24px;
    text-align: center;
    color: var(--bkd-muted);
    font-size: 12px;
    font-weight: 600;
  }

  /* ── Show more toggle ── */
  .bkd-toggle-more {
    display: block;
    width: 100%;
    margin-top: 8px;
    padding: 6px 0;
    border: 0;
    background: none;
    color: var(--bkd-primary);
    font-size: 11px;
    font-weight: 700;
    font-family: inherit;
    cursor: pointer;
    text-align: center;
  }
  .bkd-toggle-more:hover { text-decoration: underline; }

  /* ── Note area ── */
  .bkd-result-note {
    margin-top: 10px;
    font-size: 11px;
    color: var(--bkd-muted);
    font-weight: 600;
  }

  /* ── Responsive ── */
  @media (max-width: 1180px) {
    .bkd-body { grid-template-columns: 1fr; }
    .bkd-stats { grid-template-columns: repeat(2, 1fr); }
    .bkd-kv-grid { grid-template-columns: repeat(2, 1fr); }
  }
  @media (max-width: 760px) {
    .admin-booking-detail-outlet { padding: 20px 16px; }
    .bkd-header { flex-direction: column; }
    .bkd-stats { grid-template-columns: 1fr; }
    .bkd-kv-grid { grid-template-columns: 1fr; }
    .bkd-review-actions { grid-template-columns: 1fr; }
  }

  /* ── Booking Command Center ── */
  .admin-booking-detail-outlet {
    padding: 30px;
  }
  .bkd-page {
    --bkd-text: #34232b;
    --bkd-body: #7b5c69;
    --bkd-muted: #a58b96;
    max-width: 1450px;
  }
  .bkd-header {
    align-items: flex-end;
    margin-bottom: 20px;
  }
  .bkd-eyebrow {
    margin-bottom: 7px;
    letter-spacing: .18em;
    font-weight: 800;
  }
  .bkd-ref {
    font-family: "Playfair Display", serif;
    font-size: clamp(31px, 3vw, 43px);
    font-weight: 650;
    line-height: 1;
    color: #34232b;
  }
  .bkd-ref em {
    color: #7b5c69;
    font-weight: 500;
  }
  .bkd-subtitle {
    margin-top: 10px;
    line-height: 1.6;
  }
  .bkd-header-status {
    display: inline-flex;
    min-height: 38px;
    align-items: center;
    gap: 8px;
    border: 1px solid var(--bkd-border);
    border-radius: 999px;
    padding: 0 13px;
    background: #fff;
    color: var(--bkd-primary);
    font-size: 10px;
    font-weight: 800;
    white-space: nowrap;
    box-shadow: 0 10px 28px rgba(52,35,43,.06);
  }
  .bkd-header-status::before {
    content: "";
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: currentColor;
    box-shadow: 0 0 0 5px color-mix(in srgb, currentColor 10%, transparent);
  }
  .bkd-btn {
    min-height: 40px;
    height: 40px;
    border-radius: 10px;
    padding-inline: 14px;
    font-size: 11px;
  }
  .bkd-stats {
    gap: 0;
    overflow: hidden;
    margin-bottom: 14px;
    border: 1px solid var(--bkd-border);
    border-radius: 15px;
    background: #fff;
    box-shadow: 0 18px 45px rgba(52,35,43,.055);
  }
  .bkd-stat {
    min-height: 105px;
    border: 0;
    border-right: 1px solid var(--bkd-border);
    border-radius: 0;
    padding: 21px 23px;
  }
  .bkd-stat:last-child { border-right: 0; }
  .bkd-stat-value {
    font-family: "Inter", sans-serif;
    font-size: 25px;
    font-weight: 750;
    color: #34232b;
    font-variant-numeric: tabular-nums;
    letter-spacing: -.025em;
  }
  .bkd-stat-label {
    letter-spacing: .13em;
    font-weight: 800;
  }
  .bkd-command-nav {
    display: flex;
    gap: 4px;
    overflow-x: auto;
    margin-bottom: 18px;
    border: 1px solid var(--bkd-border);
    border-radius: 13px;
    padding: 8px;
    background: #faf5ef;
  }
  .bkd-command-nav a {
    display: inline-flex;
    min-height: 34px;
    flex: 0 0 auto;
    align-items: center;
    gap: 7px;
    border-radius: 8px;
    padding: 0 11px;
    color: #8e727e;
    font-size: 10px;
    font-weight: 800;
    text-decoration: none;
    transition: background .15s ease, color .15s ease;
  }
  .bkd-command-nav a:hover,
  .bkd-command-nav a:focus-visible {
    background: #fff;
    color: var(--bkd-primary);
    outline: none;
  }
  .bkd-command-nav svg { width: 13px; height: 13px; }
  .bkd-body {
    grid-template-columns: minmax(0,1fr) 370px;
    gap: 20px;
  }
  .bkd-main { gap: 18px; min-width: 0; }
  .bkd-side {
    gap: 14px;
  }
  .bkd-card {
    scroll-margin-top: 100px;
    border-radius: 15px;
    box-shadow: 0 16px 40px rgba(52,35,43,.05);
  }
  .bkd-card--highlight {
    border-left: 1px solid var(--bkd-border);
    border-top: 4px solid var(--bkd-warn-border);
  }
  .bkd-card-head {
    min-height: 61px;
    padding: 14px 18px;
  }
  .bkd-card-title {
    color: #34232b;
    font-size: 12px;
    font-weight: 800;
  }
  .bkd-card-icon {
    width: 32px;
    height: 32px;
    border-radius: 9px;
  }
  .bkd-card-body { padding: 18px; }
  .bkd-kv {
    border-radius: 10px;
    padding: 13px;
  }
  .bkd-kv-label { font-size: 9px; font-weight: 800; }
  .bkd-table th { font-size: 9px; font-weight: 800; }
  .bkd-table tbody tr:hover { background: #fdf9f5; }
  .bkd-action-center {
    position: sticky;
    top: 100px;
    z-index: 2;
    border-color: #d8c1b1;
    box-shadow: 0 20px 52px rgba(52,35,43,.09);
  }
  .bkd-action-center .bkd-card-head {
    border-bottom-color: rgba(255,255,255,.12);
    background: linear-gradient(145deg,#6d4c5b,#7b5c69);
  }
  .bkd-action-center .bkd-card-title,
  .bkd-action-center .bkd-card-meta { color: #fff; }
  .bkd-action-center .bkd-card-icon {
    background: rgba(255,255,255,.13);
    color: #fff;
  }
  .bkd-proof-link {
    border-radius: 11px;
    background: #faf5ef;
  }
  .bkd-proof-link img { max-height: 260px; }
  .bkd-review-form {
    border-color: #e1c69b;
    border-radius: 11px;
    background: #fff9ef;
  }
  .bkd-review-actions .bkd-btn {
    width: 100%;
    justify-content: center;
  }
  .bkd-side-person,
  .bkd-side-row {
    border-radius: 10px;
  }
  .bkd-danger-zone {
    border-color: #efcaca;
    box-shadow: none;
  }
  .bkd-danger-zone .bkd-card-head { background: #fff7f7; }

  /* ── Event Day Run Sheet ── */
  .bkd-run-sheet-card {
    overflow: visible;
  }
  .bkd-run-sheet-card > .bkd-card-head {
    overflow: hidden;
    border-radius: 15px 15px 0 0;
  }
  .bkd-run-sheet {
    padding: 0;
  }
  .bkd-run-day {
    border-bottom: 1px solid var(--bkd-border);
  }
  .bkd-run-day:last-child { border-bottom: 0; }
  .bkd-run-day-head {
    display: grid;
    grid-template-columns: 96px minmax(0,1fr);
    gap: 18px;
    align-items: stretch;
    border-bottom: 1px solid var(--bkd-border-light);
    background: linear-gradient(120deg,#f2e5d8,#faf5ef 55%,#fff);
  }
  .bkd-run-date {
    display: flex;
    min-height: 112px;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    border-right: 1px solid #dcc5b4;
    color: var(--bkd-primary);
  }
  .bkd-run-date-day {
    font-family: "Playfair Display", serif;
    font-size: 37px;
    font-weight: 650;
    line-height: 1;
  }
  .bkd-run-date-month {
    margin-top: 5px;
    font-size: 9px;
    font-weight: 800;
    letter-spacing: .14em;
    text-transform: uppercase;
  }
  .bkd-run-date-year {
    margin-top: 2px;
    color: #a58b96;
    font-size: 9px;
    font-weight: 700;
  }
  .bkd-run-summary {
    display: flex;
    min-width: 0;
    flex-direction: column;
    justify-content: center;
    padding: 18px 20px 18px 0;
  }
  .bkd-run-summary-kicker {
    color: #9b7d89;
    font-size: 8px;
    font-weight: 800;
    letter-spacing: .14em;
    text-transform: uppercase;
  }
  .bkd-run-summary-title {
    margin: 5px 0 0;
    color: #34232b;
    font-family: "Playfair Display", serif;
    font-size: 20px;
    font-weight: 650;
  }
  .bkd-run-summary-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 7px 15px;
    margin-top: 10px;
    color: #7b5c69;
    font-size: 10px;
    font-weight: 650;
  }
  .bkd-run-summary-meta span {
    display: inline-flex;
    min-width: 0;
    align-items: center;
    gap: 6px;
  }
  .bkd-run-summary-meta svg {
    width: 12px;
    height: 12px;
    flex: 0 0 12px;
    color: #9b7d89;
  }
  .bkd-run-events {
    padding: 20px 22px 22px;
  }
  .bkd-run-event {
    display: grid;
    grid-template-columns: 74px 20px minmax(0,1fr);
    gap: 12px;
    min-height: 78px;
  }
  .bkd-run-time {
    padding-top: 2px;
    color: #34232b;
    font-family: ui-monospace,SFMono-Regular,Menlo,monospace;
    font-size: 11px;
    font-weight: 800;
    text-align: right;
  }
  .bkd-run-time-end {
    display: block;
    margin-top: 3px;
    color: #b79c8b;
    font-size: 9px;
    font-weight: 600;
  }
  .bkd-run-track {
    display: flex;
    flex-direction: column;
    align-items: center;
  }
  .bkd-run-track::after {
    content: "";
    width: 1px;
    flex: 1;
    margin-top: 5px;
    background: #ddcabb;
  }
  .bkd-run-event:last-child .bkd-run-track::after { display: none; }
  .bkd-run-node {
    width: 11px;
    height: 11px;
    flex: 0 0 11px;
    border: 3px solid #fff;
    border-radius: 50%;
    background: var(--bkd-primary);
    box-shadow: 0 0 0 2px #ceb4a3;
  }
  .bkd-run-event.has-overlap .bkd-run-node {
    background: #b7792f;
    box-shadow: 0 0 0 2px #e5c38f;
  }
  .bkd-run-event-body {
    padding: 0 0 20px 2px;
  }
  .bkd-run-service {
    color: #34232b;
    font-size: 12px;
    font-weight: 800;
    line-height: 1.4;
  }
  .bkd-run-category {
    display: inline-flex;
    min-height: 19px;
    align-items: center;
    margin-left: 7px;
    border-radius: 999px;
    padding: 0 7px;
    background: #faf5ef;
    color: #9b7d89;
    font-size: 7px;
    font-weight: 800;
    letter-spacing: .07em;
    text-transform: uppercase;
    vertical-align: 1px;
  }
  .bkd-run-detail {
    display: flex;
    flex-wrap: wrap;
    gap: 6px 14px;
    margin-top: 6px;
    color: #7b5c69;
    font-size: 10px;
    font-weight: 600;
  }
  .bkd-run-detail span {
    display: inline-flex;
    align-items: center;
    gap: 5px;
  }
  .bkd-run-detail svg {
    width: 11px;
    height: 11px;
    color: #b79c8b;
  }
  .bkd-run-overlap {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    margin-top: 8px;
    border-radius: 7px;
    padding: 5px 8px;
    background: #fff5e6;
    color: #9a6527;
    font-size: 8px;
    font-weight: 800;
  }
  .bkd-run-overlap svg { width: 11px; height: 11px; }
  .bkd-run-empty {
    padding: 52px 22px;
    text-align: center;
    color: #9b7d89;
    font-size: 11px;
    font-weight: 700;
  }
  @media (max-width: 1180px) {
    .bkd-action-center { position: static; }
    .bkd-stat:nth-child(2) { border-right: 0; }
    .bkd-stat:nth-child(-n+2) { border-bottom: 1px solid var(--bkd-border); }
  }
  @media (max-width: 760px) {
    .admin-booking-detail-outlet { padding: 20px 16px; }
    .bkd-header { align-items: flex-start; }
    .bkd-header-actions { width: 100%; }
    .bkd-header-actions .bkd-btn { flex: 1; justify-content: center; }
    .bkd-stats { grid-template-columns: 1fr; }
    .bkd-stat,
    .bkd-stat:nth-child(2) { border-right: 0; border-bottom: 1px solid var(--bkd-border); }
    .bkd-stat:last-child { border-bottom: 0; }
    .bkd-run-day-head { grid-template-columns: 78px minmax(0,1fr); }
    .bkd-run-date-day { font-size: 31px; }
    .bkd-run-event { grid-template-columns: 62px 18px minmax(0,1fr); gap: 9px; }
  }
  @media (prefers-reduced-motion: reduce) {
    .bkd-command-nav a { transition: none; }
  }
</style>

<div class="bkd-page">
  <!-- ── Header ── -->
  <header class="bkd-header">
    <div class="bkd-header-left">
      <p class="bkd-eyebrow">Booking detail</p>
      <h1 class="bkd-ref">
        <?php
          $refParts = $bookingRef ? explode('-', $bookingRef, 2) : ['Booking #' . $bookingId, ''];
          echo $h($refParts[0]);
          if (!empty($refParts[1])) echo '-<em>' . $h($refParts[1]) . '</em>';
        ?>
      </h1>
      <p class="bkd-subtitle">
        <?= $h($custName) ?> · <?= $h($booking['customer_email'] ?? '-') ?> · Created <?= $h($createdAt) ?>
      </p>
    </div>
    <div class="bkd-header-actions">
      <span class="bkd-header-status" id="booking-status-value"><?= $h($statusLabel) ?></span>
      <a href="<?= URLROOT ?>/admin/bookings" class="bkd-btn bkd-btn--ghost">
        <i data-lucide="chevron-left"></i> Back
      </a>
      <button type="button" id="copy-ref-btn" class="bkd-btn bkd-btn--ghost" data-ref="<?= $h($bookingRef) ?>">
        <i data-lucide="copy"></i> Copy ref
      </button>
      <a href="<?= URLROOT ?>/admin/paymentVerification" class="bkd-btn bkd-btn--ghost">
        <i data-lucide="receipt-text"></i> Payment review
      </a>
    </div>
  </header>

  <!-- ── Stats band ── -->
  <div class="bkd-stats">
    <div class="bkd-stat bkd-stat--primary">
      <div class="bkd-stat-label">Event date</div>
      <div class="bkd-stat-value is-primary"><?= $h($dateOnly($firstEvent['event_date'] ?? null)) ?></div>
      <div class="bkd-stat-sub"><?= $h($timeOnly($displayEventStart)) ?> – <?= $h($timeOnly($displayEventEnd)) ?></div>
    </div>
    <div class="bkd-stat bkd-stat--neutral">
      <div class="bkd-stat-label">Total amount</div>
      <div class="bkd-stat-value"><?= $money($totalAmount) ?></div>
      <div class="bkd-stat-sub"><?= count($items) ?> booked item<?= count($items) === 1 ? '' : 's' ?></div>
    </div>
    <div class="bkd-stat bkd-stat--success">
      <div class="bkd-stat-label">Paid</div>
      <div class="bkd-stat-value is-success" id="booking-paid-value"><?= $money($paidAmount) ?></div>
      <div class="bkd-stat-sub" id="booking-paid-percent"><?= $paidPercent ?>% collected</div>
    </div>
    <div class="bkd-stat <?= $balanceDue > 0 ? 'bkd-stat--danger' : 'bkd-stat--success' ?>">
      <div class="bkd-stat-label">Balance due</div>
      <div class="bkd-stat-value <?= $balanceDue > 0 ? 'is-danger' : 'is-success' ?>" id="booking-balance-value"><?= $money($balanceDue) ?></div>
      <div class="bkd-stat-sub">Remaining payment</div>
    </div>
  </div>

  <nav class="bkd-command-nav" aria-label="Booking detail sections">
    <a href="#booking-payment"><i data-lucide="wallet-cards"></i>Payment</a>
    <a href="#booking-services"><i data-lucide="calendar-check"></i>Services</a>
    <a href="#booking-event"><i data-lucide="map-pin"></i>Event</a>
    <a href="#booking-suppliers"><i data-lucide="store"></i>Suppliers</a>
    <a href="#booking-activity"><i data-lucide="history"></i>Activity</a>
  </nav>

  <!-- ── 2-column body ── -->
  <div class="bkd-body">
    <main class="bkd-main">
      <?php if ($isAwaitingReview): ?>
      <!-- Priority: Payment review (when awaiting) -->
      <div class="bkd-card bkd-card--highlight" id="booking-payment">
        <div class="bkd-card-head">
          <div class="bkd-card-head-left">
            <div class="bkd-card-icon bkd-card-icon--warn">
              <i data-lucide="alert-circle"></i>
            </div>
            <span class="bkd-card-title">⚠ Payment requires review</span>
          </div>
          <span class="bkd-badge <?= $badgeClass($paymentStatus) ?>" id="payment-status-badge">
            <?= $h(ucwords(str_replace('_', ' ', $paymentStatus))) ?>
          </span>
        </div>
        <div class="bkd-card-body">
          <div class="bkd-kv-grid">
            <div class="bkd-kv">
              <div class="bkd-kv-label">Amount sent</div>
              <div class="bkd-kv-value"><?= $money($sentAmount) ?></div>
              <div class="bkd-kv-sub">Submitted by customer</div>
            </div>
            <div class="bkd-kv">
              <div class="bkd-kv-label">Expected deposit</div>
              <div class="bkd-kv-value"><?= $money($expectedDeposit) ?></div>
              <div class="bkd-kv-sub"><?= (int)$depositPercent ?>% of total</div>
            </div>
            <div class="bkd-kv">
              <div class="bkd-kv-label">Method</div>
              <div class="bkd-kv-value"><?= $h($paymentMethod ?: '-') ?></div>
              <div class="bkd-kv-sub"><?= $h($dateTime($reviewPayment['paid_at'] ?? null)) ?></div>
            </div>
            <div class="bkd-kv">
              <div class="bkd-kv-label">Payment reference</div>
              <div class="bkd-kv-value"><?= $h($transactionRef ?: '-') ?></div>
            </div>
            <div class="bkd-kv">
              <div class="bkd-kv-label">Sender account</div>
              <div class="bkd-kv-value"><?= $h($reviewPayment['account_name'] ?? '-') ?></div>
            </div>
            <div class="bkd-kv">
              <div class="bkd-kv-label">Sender phone</div>
              <div class="bkd-kv-value"><?= $h($reviewPayment['mobile_number'] ?? '-') ?></div>
            </div>
          </div>
          <div class="bkd-progress">
            <span id="payment-progress-bar" style="width:<?= min(100, max(0, $paidPercent)) ?>%"></span>
          </div>
        </div>
      </div>
      <?php else: ?>
      <!-- Non-urgent: Payment summary -->
      <div class="bkd-card" id="booking-payment">
        <div class="bkd-card-head">
          <div class="bkd-card-head-left">
            <div class="bkd-card-icon">
              <i data-lucide="receipt"></i>
            </div>
            <span class="bkd-card-title">Deposit payment</span>
          </div>
          <span class="bkd-badge <?= $badgeClass($paymentStatus) ?>" id="payment-status-badge">
            <?= $h(ucwords(str_replace('_', ' ', $paymentStatus))) ?>
          </span>
        </div>
        <div class="bkd-card-body">
          <div class="bkd-kv-grid">
            <div class="bkd-kv">
              <div class="bkd-kv-label">Amount sent</div>
              <div class="bkd-kv-value"><?= $money($sentAmount) ?></div>
              <div class="bkd-kv-sub">Submitted by customer</div>
            </div>
            <div class="bkd-kv">
              <div class="bkd-kv-label">Expected deposit</div>
              <div class="bkd-kv-value"><?= $money($expectedDeposit) ?></div>
              <div class="bkd-kv-sub"><?= (int)$depositPercent ?>% of total</div>
            </div>
            <div class="bkd-kv">
              <div class="bkd-kv-label">Method</div>
              <div class="bkd-kv-value"><?= $h($paymentMethod ?: '-') ?></div>
              <div class="bkd-kv-sub"><?= $h($dateTime($reviewPayment['paid_at'] ?? null)) ?></div>
            </div>
            <div class="bkd-kv">
              <div class="bkd-kv-label">Payment reference</div>
              <div class="bkd-kv-value"><?= $h($transactionRef ?: '-') ?></div>
            </div>
            <div class="bkd-kv">
              <div class="bkd-kv-label">Sender account</div>
              <div class="bkd-kv-value"><?= $h($reviewPayment['account_name'] ?? '-') ?></div>
            </div>
            <div class="bkd-kv">
              <div class="bkd-kv-label">Sender phone</div>
              <div class="bkd-kv-value"><?= $h($reviewPayment['mobile_number'] ?? '-') ?></div>
            </div>
          </div>
          <div class="bkd-progress">
            <span id="payment-progress-bar" style="width:<?= min(100, max(0, $paidPercent)) ?>%"></span>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <!-- Services table -->
      <div class="bkd-card" id="booking-services">
        <div class="bkd-card-head">
          <div class="bkd-card-head-left">
            <div class="bkd-card-icon">
              <i data-lucide="calendar-check"></i>
            </div>
            <span class="bkd-card-title">Booked services</span>
          </div>
          <span class="bkd-card-meta"><?= count($items) ?> record<?= count($items) === 1 ? '' : 's' ?></span>
        </div>
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
                <tr><td colspan="5"><div class="bkd-empty">No services found for this booking.</div></td></tr>
              <?php endif; ?>
              <?php foreach ($items as $item): ?>
                <?php
                  $hallName = trim((string)($item['venue_room_name'] ?? ''));
                  $venueName = trim((string)($item['venue_name'] ?? ''));
                  $date = $dateOnly($item['booking_date'] ?? null);
                  $time = trim($timeOnly($item['start_time'] ?? null) . ' - ' . $timeOnly($item['end_time'] ?? null), ' -');
                  $itemEvent = $eventDetailsByItem[(int)($item['id'] ?? 0)] ?? [];
                  $isAddon = !empty($item['package_booking_item_id']);
                ?>
                <tr>
                  <td>
                    <div class="bkd-table-name"><?= $h($item['service_name'] ?? 'Service') ?></div>
                    <?php if ($isAddon && !empty($item['addon_package_name'])): ?>
                      <span class="bkd-addon-chip">
                        <i data-lucide="link" style="width:10px;height:10px"></i>
                        Add-on for <?= $h($item['addon_package_name']) ?>
                      </span>
                    <?php endif; ?>
                    <?php if ($hallName !== '' || $venueName !== ''): ?>
                      <div class="bkd-table-sub"><?= $h(trim($hallName . ($venueName !== '' ? ' · ' . $venueName : ''))) ?></div>
                    <?php endif; ?>
                  </td>
                  <td><span class="bkd-table-name"><?= $h($item['supplier_name'] ?? 'Supplier') ?></span></td>
                  <td>
                    <div class="bkd-table-name"><?= $h($date) ?></div>
                    <div class="bkd-table-sub"><?= $h($time !== '' ? $time : '—') ?></div>
                  </td>
                  <td><span class="bkd-table-name"><?= $h($itemEvent['guest_count'] ?? '—') ?></span></td>
                  <td class="is-right"><span class="bkd-table-amount"><?= $money($item['price'] ?? 0) ?></span></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Event information -->
      <div class="bkd-card bkd-run-sheet-card" id="booking-event">
        <div class="bkd-card-head">
          <div class="bkd-card-head-left">
            <div class="bkd-card-icon">
              <i data-lucide="route"></i>
            </div>
            <span class="bkd-card-title">Event day run sheet</span>
          </div>
          <span class="bkd-card-meta"><?= count($runSheetGroups) ?> event <?= count($runSheetGroups) === 1 ? 'date' : 'dates' ?></span>
        </div>
        <div class="bkd-run-sheet">
          <?php if (empty($runSheetGroups)): ?>
            <div class="bkd-run-empty">No event schedule has been recorded for this booking.</div>
          <?php endif; ?>

          <?php foreach ($runSheetGroups as $dateKey => $events): ?>
            <?php
              $scheduledDate = $dateKey !== 'unscheduled' ? strtotime($dateKey) : false;
              $dayLocation = '';
              $dayGuests = null;
              $dayContact = '';
              $dayPhone = '';
              foreach ($events as $event) {
                  if ($dayLocation === '' && !empty($event['location'])) $dayLocation = (string)$event['location'];
                  if ($dayGuests === null && isset($event['guests'])) $dayGuests = $event['guests'];
                  if ($dayContact === '' && !empty($event['contact_name'])) $dayContact = (string)$event['contact_name'];
                  if ($dayPhone === '' && !empty($event['contact_phone'])) $dayPhone = (string)$event['contact_phone'];
              }
              $dayStart = $events[0]['start'] ?? null;
              $dayEndValues = array_values(array_filter(array_column($events, 'end')));
              sort($dayEndValues);
              $dayEnd = $dayEndValues ? end($dayEndValues) : null;
            ?>
            <section class="bkd-run-day">
              <header class="bkd-run-day-head">
                <div class="bkd-run-date">
                  <span class="bkd-run-date-day"><?= $scheduledDate ? date('d', $scheduledDate) : '—' ?></span>
                  <span class="bkd-run-date-month"><?= $scheduledDate ? date('M', $scheduledDate) : 'TBD' ?></span>
                  <span class="bkd-run-date-year"><?= $scheduledDate ? date('Y', $scheduledDate) : '' ?></span>
                </div>
                <div class="bkd-run-summary">
                  <span class="bkd-run-summary-kicker"><?= $scheduledDate ? date('l', $scheduledDate) : 'Schedule pending' ?></span>
                  <h3 class="bkd-run-summary-title"><?= count($events) ?> scheduled <?= count($events) === 1 ? 'service' : 'services' ?></h3>
                  <div class="bkd-run-summary-meta">
                    <span><i data-lucide="clock-3"></i><?= $h($timeOnly($dayStart)) ?> – <?= $h($timeOnly($dayEnd)) ?></span>
                    <?php if ($dayGuests !== null): ?><span><i data-lucide="users"></i><?= $h($dayGuests) ?> guests</span><?php endif; ?>
                    <?php if ($dayLocation !== ''): ?><span><i data-lucide="map-pin"></i><?= $h($dayLocation) ?></span><?php endif; ?>
                    <?php if ($dayContact !== ''): ?><span><i data-lucide="phone"></i><?= $h($dayContact) ?><?= $dayPhone !== '' ? ' · ' . $h($dayPhone) : '' ?></span><?php endif; ?>
                  </div>
                </div>
              </header>

              <div class="bkd-run-events">
                <?php foreach ($events as $event): ?>
                  <article class="bkd-run-event <?= !empty($event['overlap']) ? 'has-overlap' : '' ?>">
                    <time class="bkd-run-time">
                      <?= $h($timeOnly($event['start'])) ?>
                      <span class="bkd-run-time-end"><?= $h($timeOnly($event['end'])) ?></span>
                    </time>
                    <div class="bkd-run-track"><span class="bkd-run-node"></span></div>
                    <div class="bkd-run-event-body">
                      <div class="bkd-run-service">
                        <?= $h($event['service'] ?? 'Service') ?>
                        <span class="bkd-run-category"><?= $h($event['category'] ?? 'Event') ?></span>
                      </div>
                      <div class="bkd-run-detail">
                        <span><i data-lucide="store"></i><?= $h($event['supplier'] ?? 'Supplier') ?></span>
                        <?php if (!empty($event['location'])): ?><span><i data-lucide="map-pin"></i><?= $h($event['location']) ?></span><?php endif; ?>
                      </div>
                      <?php if (!empty($event['overlap'])): ?>
                        <span class="bkd-run-overlap"><i data-lucide="triangle-alert"></i>Overlaps another scheduled service</span>
                      <?php endif; ?>
                    </div>
                  </article>
                <?php endforeach; ?>
              </div>
            </section>
          <?php endforeach; ?>
        </div>
      </div>
    </main>

    <!-- ── Sidebar ── -->
    <aside class="bkd-side">
      <!-- Payment proof -->
      <div class="bkd-card bkd-action-center">
        <div class="bkd-card-head">
          <div class="bkd-card-head-left">
            <div class="bkd-card-icon">
              <i data-lucide="image"></i>
            </div>
            <span class="bkd-card-title"><?= $isAwaitingReview ? 'Action center · Payment review' : 'Payment proof' ?></span>
          </div>
        </div>
        <div class="bkd-card-body">
          <?php if ($slipPath !== ''): ?>
            <a href="<?= URLROOT ?>/<?= $h($slipPath) ?>" target="_blank" class="bkd-proof-link">
              <?php if ($isImageSlip): ?>
                <img src="<?= URLROOT ?>/<?= $h($slipPath) ?>" alt="Payment slip">
              <?php else: ?>
                <span class="bkd-proof-file">
                  <i data-lucide="file-text"></i>
                  Open uploaded document
                </span>
              <?php endif; ?>
            </a>
          <?php else: ?>
            <div class="bkd-empty">No payment proof uploaded.</div>
          <?php endif; ?>

          <?php if ($isAwaitingReview): ?>
            <form id="payment-review-form" class="bkd-review-form" data-booking-id="<?= $bookingId ?>">
              <textarea name="note" placeholder="Admin note (optional)"></textarea>
              <div class="bkd-review-actions">
                <button class="bkd-btn bkd-btn--danger reject-payment-btn" type="button">
                  <i data-lucide="x-circle"></i> Reject
                </button>
                <button class="bkd-btn bkd-btn--success verify-payment-btn" type="button">
                  <i data-lucide="circle-check"></i> Verify
                </button>
              </div>
            </form>
          <?php endif; ?>
          <div id="payment-email-result" class="bkd-result-note"></div>
        </div>
      </div>

      <!-- Customer card -->
      <div class="bkd-card" id="booking-suppliers">
        <div class="bkd-card-head">
          <div class="bkd-card-head-left">
            <div class="bkd-card-icon">
              <i data-lucide="user-circle"></i>
            </div>
            <span class="bkd-card-title">Customer</span>
          </div>
        </div>
        <div class="bkd-card-body">
          <div class="bkd-side-person">
            <div class="bkd-avatar"><?= $h($custInitials) ?></div>
            <div>
              <div class="bkd-table-name"><?= $h($custName) ?></div>
              <div class="bkd-table-sub"><?= $h($booking['customer_email'] ?? '—') ?></div>
            </div>
          </div>
          <div class="bkd-kv" style="margin-top:10px">
            <div class="bkd-kv-label">Phone</div>
            <div class="bkd-kv-value"><?= $h($booking['customer_phone'] ?? '—') ?></div>
          </div>
        </div>
      </div>

      <!-- Suppliers list -->
      <div class="bkd-card" id="booking-activity">
        <div class="bkd-card-head">
          <div class="bkd-card-head-left">
            <div class="bkd-card-icon">
              <i data-lucide="store"></i>
            </div>
            <span class="bkd-card-title">Suppliers</span>
          </div>
          <span class="bkd-card-meta"><?= count($suppliers) ?></span>
        </div>
        <div class="bkd-card-body">
          <?php if (empty($suppliers)): ?>
            <div class="bkd-empty">No suppliers assigned.</div>
          <?php else: ?>
            <div class="bkd-side-list">
              <?php foreach ($suppliers as $supplier): ?>
                <?php
                  $sName = (string)($supplier['shop_name'] ?? 'Supplier');
                  $sStatus = (string)($supplier['status'] ?? 'pending');
                ?>
                <div class="bkd-side-row">
                  <span class="bkd-dot <?= $supplierStatusDot($sStatus) ?>"></span>
                  <div class="bkd-side-row-main">
                    <div class="bkd-side-row-title"><?= $h($sName) ?></div>
                    <div class="bkd-side-row-sub"><?= $h(ucwords(str_replace('_', ' ', $sStatus))) ?></div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Audit trail -->
      <div class="bkd-card bkd-danger-zone">
        <div class="bkd-card-head">
          <div class="bkd-card-head-left">
            <div class="bkd-card-icon">
              <i data-lucide="scroll-text"></i>
            </div>
            <span class="bkd-card-title">Audit trail</span>
          </div>
          <span class="bkd-card-meta"><?= count($logs) ?> events</span>
        </div>
        <div class="bkd-card-body">
          <?php if (empty($logs)): ?>
            <div class="bkd-empty">No status history yet.</div>
          <?php else: ?>
            <div class="bkd-timeline" id="audit-timeline">
              <?php foreach ($visibleLogs as $log): ?>
                <?php
                  $logStatus = (string)($log['new_status'] ?? '');
                  $logNote = trim((string)($log['note'] ?? ''));
                ?>
                <div class="bkd-timeline-item">
                  <div class="bkd-timeline-dot">
                    <span class="bkd-dot <?= $logDot($logStatus) ?>"></span>
                  </div>
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
              <button type="button" id="show-all-logs" class="bkd-toggle-more">
                Show all <?= count($logs) ?> entries ↓
              </button>
            <?php endif; ?>
          <?php endif; ?>
        </div>
      </div>

      <!-- Cancel booking -->
      <?php if ($canCancel): ?>
      <div class="bkd-card">
        <div class="bkd-card-head">
          <div class="bkd-card-head-left">
            <div class="bkd-card-icon" style="background:var(--bkd-danger-bg);color:var(--bkd-danger-text)">
              <i data-lucide="alert-triangle"></i>
            </div>
            <span class="bkd-card-title">Cancel booking</span>
          </div>
        </div>
        <div class="bkd-card-body">
          <form id="admin-cancel-form" class="bkd-cancel-form">
            <input type="hidden" name="booking_id" value="<?= $bookingId ?>">
            <textarea name="reason" required placeholder="Cancellation reason…"></textarea>
            <label class="bkd-cancel-check">
              <input type="checkbox" name="refund_deposit" value="1">
              Mark deposit as refunded
            </label>
            <button class="bkd-btn bkd-btn--danger" type="submit" style="width:100%;justify-content:center">
              <i data-lucide="ban"></i> Cancel booking
            </button>
          </form>
        </div>
      </div>
      <?php endif; ?>
    </aside>
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

  async function handlePaymentReview(form, approve) {
    var bookingId = form.dataset.bookingId;
    var note = form.querySelector('textarea[name="note"]').value;
    var endpoint = approve
      ? '<?= URLROOT ?>/admin/verifyPaymentPost'
      : '<?= URLROOT ?>/admin/rejectPaymentSlipPost';
    var formData = new FormData();
    formData.append('booking_id', bookingId);
    formData.append('note', note);

    if (!approve) {
      var reason = prompt('Reason for rejecting this payment proof:');
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
      if (!confirm('Are you sure you want to cancel this booking? This may not be reversible.')) return;
      var response = await fetch('<?= URLROOT ?>/admin/bookingCancel', { method: 'POST', body: new FormData(cancelForm) });
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
