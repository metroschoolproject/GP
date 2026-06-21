<?php
$booking = $booking ?? [];
$items = $items ?? [];
$eventDetails = $eventDetails ?? [];
$suppliers = $suppliers ?? [];
$bookingRef = $bookingRef ?? '';
$supplierStatus = strtolower($supplierStatus ?? 'pending');
$supplierId = (int)($supplierId ?? 0);
$depositPercent = $depositPercent ?? BOOKING_DEPOSIT_PERCENT;
$packageSchedules = $packageSchedules ?? [];

$money = fn($v) => number_format((float)$v, 0) . ' MMK';
$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$formatDate = function ($value, string $fallback = '-') {
    if (empty($value)) return $fallback;
    $time = strtotime((string)$value);
    return $time ? date('d M Y', $time) : $fallback;
};
$formatTime = function ($value) {
    if (empty($value)) return '';
    $time = strtotime((string)$value);
    return $time ? date('h:i A', $time) : (string)$value;
};

/* ── Match event_details to booking items ── */
$detailByItem = [];
foreach ($eventDetails as $detail) {
    $key = (int)($detail['booking_item_id'] ?? 0);
    if ($key > 0) $detailByItem[$key] = $detail;
}
// Resolve add-on items through to parent package event detail
foreach ($items as $item) {
    $itemId = (int)($item['id'] ?? 0);
    if (!isset($detailByItem[$itemId])) {
        $parentId = (int)($item['package_booking_item_id'] ?? 0);
        if ($parentId > 0 && isset($detailByItem[$parentId])) {
            $detailByItem[$itemId] = $detailByItem[$parentId];
        }
    }
}

/* ── Aggregate per-item data ── */
$totalGuests = 0;
$hasGuestData = false;
$firstDate = '';
$firstStart = '';
$firstEnd = '';
$firstLocation = '';
$firstContactName = '';
$firstContactPhone = '';
$allSpecialRequests = [];

foreach ($items as $item) {
    $itemId = (int)($item['id'] ?? 0);
    $isAddon = !empty($item['package_booking_item_id']);
    if ($isAddon) continue;
    $d = $detailByItem[$itemId] ?? [];
    $guests = (int)($d['guest_count'] ?? 0);
    if ($guests > 0) {
        $hasGuestData = true;
        $totalGuests += $guests;
    }
    if ($firstDate === '' && !empty($d['event_date'])) $firstDate = $d['event_date'];
    if ($firstStart === '' && !empty($d['start_time'])) $firstStart = $d['start_time'];
    if ($firstEnd === '' && !empty($d['end_time'])) $firstEnd = $d['end_time'];
    if ($firstLocation === '' && !empty(trim((string)($d['location'] ?? '')))) $firstLocation = trim((string)$d['location']);
    if ($firstContactName === '' && !empty(trim((string)($d['contact_name'] ?? '')))) $firstContactName = trim((string)$d['contact_name']);
    if ($firstContactPhone === '' && !empty(trim((string)($d['contact_phone'] ?? '')))) $firstContactPhone = trim((string)$d['contact_phone']);
    $sr = trim((string)($d['special_requests'] ?? ''));
    if ($sr !== '') $allSpecialRequests[] = $sr;
}

/* ── Financials ── */
$supplierTotal = (float)($booking['supplier_total_amount'] ?? array_sum(array_map(fn($i) => (float)($i['price'] ?? 0), $items)));
$bookingTotal = (float)($booking['total_amount'] ?? 0);
$paidTotal = (float)($booking['paid_amount'] ?? 0);
$paidFraction = $bookingTotal > 0 ? min(1, $paidTotal / $bookingTotal) : 0;
$supplierPaid = $supplierTotal * $paidFraction;
$supplierRemaining = max(0, $supplierTotal - $supplierPaid);
$paymentStatus = strtolower((string)($booking['payment_status'] ?? 'pending'));

/* ── Lead time countdown ── */
$daysUntil = null;
if ($firstDate !== '') {
    $eventDate = new DateTimeImmutable($firstDate);
    $today = new DateTimeImmutable('today');
    $daysUntil = (int)$today->diff($eventDate)->format('%r%a');
}

/* ── Status badges ── */
$statusBadgeClass = function (string $status): string {
    $s = strtolower($status);
    if (in_array($s, ['confirmed', 'completed', 'accepted', 'paid', 'success', 'supplier_confirmed'], true))
        return 'sup-badge--success';
    if (in_array($s, ['pending', 'unpaid', 'partial', 'payment_submitted'], true))
        return 'sup-badge--warn';
    if (in_array($s, ['rejected', 'cancelled', 'canceled', 'failed', 'cancelled_by_customer', 'supplier_rejected'], true))
        return 'sup-badge--danger';
    return 'sup-badge--neutral';
};

/* ── Other suppliers ── */
$otherSuppliers = array_filter($suppliers, fn($s) => (int)($s['supplier_id'] ?? 0) !== $supplierId);

$customerName = trim((string)($booking['customer_name'] ?? 'Customer'));
$customerEmail = trim((string)($booking['customer_email'] ?? ''));
$customerPhone = trim((string)($booking['customer_phone'] ?? ''));
$customerInitial = strtoupper(substr($customerName !== '' ? $customerName : 'C', 0, 1));
$bookingStatus = strtolower((string)($booking['status'] ?? $supplierStatus));
$bookingStatusLabel = match ($bookingStatus) {
    'replacement_pending' => 'Replacement in progress',
    default => ucwords(str_replace('_', ' ', $bookingStatus ?: 'pending')),
};

$dashboardTitle = 'Bookings';
$dashboardCrumb = $bookingRef ?: 'Booking detail';
$dashboardContentClass = 'min-w-0 bg-app-content px-6 py-6 overflow-y-auto';
$dashboardContent = function () use (
    $booking, $items, $eventDetails, $suppliers, $detailByItem,
    $bookingRef, $supplierStatus, $supplierId, $money, $h, $formatDate, $formatTime,
    $statusBadgeClass,
    $totalGuests, $hasGuestData, $firstDate, $firstStart, $firstEnd,
    $firstLocation, $firstContactName, $firstContactPhone,
    $allSpecialRequests, $supplierTotal, $supplierPaid, $supplierRemaining,
    $paymentStatus, $daysUntil, $otherSuppliers, $bookingStatus, $bookingStatusLabel,
    $customerName, $customerEmail, $customerPhone, $customerInitial,
    $packageSchedules, $isPackage, $declineCutoffDays, $myServiceRows,
    $cancellationReason
) {
    $eventTime = trim($formatTime($firstStart) . ($firstEnd ? ' – ' . $formatTime($firstEnd) : ''), ' –');
    $needsResponse = $supplierStatus === 'pending';
    $myServiceRows = $myServiceRows ?? [];
    $hasPackageSchedule = false;
    foreach ($packageSchedules as $scheduleRows) {
        if (!empty($scheduleRows)) {
            $hasPackageSchedule = true;
            break;
        }
    }

    // A supplier auto-accepts a package booking, so give them a bounded escape
    // hatch: decline INDIVIDUAL services on a confirmed package booking (→ admin
    // replacement) while the event is still at least $declineCutoffDays away.
    $withinDeclineWindow = $isPackage && ($daysUntil === null || $daysUntil >= $declineCutoffDays);
    $declinableRows = $withinDeclineWindow
        ? array_values(array_filter($myServiceRows, static fn($r) => in_array($r['status'] ?? '', ['confirmed', 'in_progress'], true)))
        : [];

    // Services of this supplier already routed to replacement.
    $replacementRows = array_values(array_filter($myServiceRows, static fn($r) => in_array($r['status'] ?? '', ['needs_replacement', 'rejected'], true)));
    $acceptedReplacementRows = array_values(array_filter(
        $myServiceRows,
        static fn($r) => ($r['replacement_status'] ?? '') === 'accepted'
    ));
    $assignedReplacementRows = array_values(array_filter(
        $myServiceRows,
        static fn($r) => ($r['replacement_status'] ?? '') === 'assigned'
    ));
    $inReplacement = !empty($replacementRows) || $bookingStatus === 'replacement_pending';
    $primaryAssignment = $myServiceRows[0] ?? [];
    $primaryServiceId = (int)($primaryAssignment['service_id'] ?? 0);
    $primarySchedule = null;
    foreach ($packageSchedules as $scheduleRows) {
        foreach ($scheduleRows as $scheduleRow) {
            if (
                (int)($scheduleRow['service_id'] ?? 0) === $primaryServiceId
                || (int)($scheduleRow['supplier_id'] ?? 0) === $supplierId
            ) {
                $primarySchedule = $scheduleRow;
                break 2;
            }
        }
    }
    $assignmentName = (string)(
        $primarySchedule['service_name']
        ?? $primaryAssignment['service_name']
        ?? $items[0]['service_name']
        ?? 'Assigned service'
    );
    $assignmentCategory = (string)(
        $primarySchedule['category_name']
        ?? $primaryAssignment['category_name']
        ?? $items[0]['category_name']
        ?? ''
    );
    $assignmentDate = (string)($primarySchedule['event_date'] ?? $firstDate);
    $assignmentStart = (string)($primarySchedule['start_time'] ?? $firstStart);
    $assignmentEnd = (string)($primarySchedule['end_time'] ?? $firstEnd);
    $assignmentVenue = trim((string)(
        $primarySchedule['venue_room_name']
        ?? $items[0]['venue_room_name']
        ?? $firstLocation
    ));
    $assignmentStatusLabel = $needsResponse
        ? 'Awaiting your response'
        : (in_array($supplierStatus, ['confirmed', 'accepted'], true) ? 'Your service is confirmed' : ucwords(str_replace('_', ' ', $supplierStatus)));
    $ownTimelineServiceIds = array_values(array_unique(array_filter(array_map(
        static fn($row) => (int)($row['service_id'] ?? 0),
        $myServiceRows
    ))));
?>
<style>
  /* ── Supplier Booking Detail — Redesign ── */
  .sup-page {
    --sup-surface: #ffffff;
    --sup-soft: #faf5ef;
    --sup-border: #ead8c7;
    --sup-border-light: #eddecc;
    --sup-primary: #6d4c5b;
    --sup-primary-soft: #eddecc;
    --sup-primary-hover: #7b5c69;
    --sup-text: #111827;
    --sup-muted: #b79c8b;
    --sup-body: #7b5c69;
    --sup-success-bg: #d1fae5;
    --sup-success-text: #065f46;
    --sup-success-border: #059669;
    --sup-warn-bg: #fef3c7;
    --sup-warn-text: #92400e;
    --sup-warn-border: #d97706;
    --sup-danger-bg: #fee2e2;
    --sup-danger-text: #991b1b;
    --sup-danger-border: #dc2626;
    --sup-info-bg: #e8e7ff;
    --sup-info-text: #4f46a5;
    --sup-neutral-bg: #f3f4f6;
    --sup-neutral-text: #57534e;
    width: 100%;
    max-width: 1600px;
    min-width: 0;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 18px;
    font-family: 'Poppins', system-ui, sans-serif;
    font-size: 13px;
    color: var(--sup-text);
    line-height: 1.5;
    -webkit-font-smoothing: antialiased;
  }
  .sup-page * { box-sizing: border-box; }

  /* ── Header ── */
  .sup-header {
    background: var(--sup-surface);
    border: 1px solid var(--sup-border);
    border-radius: 1rem;
    padding: 20px 24px;
    box-shadow: 0 1px 2px rgba(28, 25, 23, .04);
  }
  .sup-header-top {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-start;
    justify-content: space-between;
    gap: 20px;
  }
  .sup-header-left { min-width: 0; }
  .sup-back {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 11px;
    font-weight: 600;
    color: var(--sup-muted);
    text-decoration: none;
    margin-bottom: 10px;
    transition: color .12s;
  }
  .sup-back:hover { color: var(--sup-text); }
  .sup-back svg { width: 12px; height: 12px; }

  .sup-ref {
    font-size: 24px;
    font-weight: 700;
    color: var(--sup-text);
    letter-spacing: -.02em;
    line-height: 1.2;
    margin: 0;
    overflow-wrap: anywhere;
  }
  .sup-ref em {
    font-style: normal;
    color: var(--sup-muted);
    font-weight: 400;
  }
  .sup-header-meta {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px;
    margin-top: 10px;
  }

  /* ── Customer strip ── */
  .sup-cust {
    display: flex;
    align-items: center;
    gap: 14px;
    flex-shrink: 0;
  }
  .sup-cust-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: var(--sup-primary-soft);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    font-weight: 700;
    color: var(--sup-primary);
    flex-shrink: 0;
  }
  .sup-cust-name {
    font-size: 13px;
    font-weight: 700;
    color: var(--sup-text);
  }
  .sup-cust-items {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 2px;
  }
  .sup-cust-item {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 11px;
    color: var(--sup-muted);
    min-width: 0;
    overflow-wrap: anywhere;
  }
  .sup-cust-item svg { width: 11px; height: 11px; flex-shrink: 0; }

  /* ── Response bar (inline, not yellow) ── */
  .sup-response-bar {
    border-top: 1px solid var(--sup-border);
    margin-top: 16px;
    padding-top: 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
  }
  .sup-selfdecline {
    border-top: 1px solid var(--sup-border);
    margin-top: 16px;
    padding-top: 14px;
  }
  .sup-selfdecline-head {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    margin-bottom: 12px;
  }
  .sup-selfdecline-head > svg {
    width: 18px; height: 18px; flex-shrink: 0; margin-top: 2px;
    color: var(--sup-warn-text);
  }
  .sup-selfdecline-list { list-style: none; margin: 0; padding: 0; display: grid; gap: 8px; }
  .sup-selfdecline-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 10px 12px;
    border: 1px solid var(--sup-border);
    border-radius: .75rem;
    background: var(--sup-soft);
  }
  .sup-selfdecline-svc { display: flex; flex-direction: column; gap: 1px; min-width: 0; }
  .sup-selfdecline-svc strong { font-weight: 700; color: var(--sup-text); font-size: 13px; }
  .sup-selfdecline-svc span { font-size: 11px; color: var(--sup-muted); }
  .sup-replace-banner {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    margin-top: 16px;
    padding: 12px 14px;
    border: 1px solid var(--sup-info-text);
    border-radius: .75rem;
    background: var(--sup-info-bg);
    color: var(--sup-info-text);
    font-size: 13px;
    line-height: 1.5;
  }
  .sup-replace-banner svg { width: 18px; height: 18px; flex-shrink: 0; margin-top: 1px; }
  .sup-replace-banner strong { font-weight: 700; }
  .sup-response-info {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    min-width: 0;
  }
  .sup-response-icon {
    width: 30px;
    height: 30px;
    border-radius: .75rem;
    background: var(--sup-primary-soft);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--sup-primary);
    flex-shrink: 0;
  }
  .sup-response-icon svg { width: 14px; height: 14px; }
  .sup-response-text {
    font-size: 12px;
    font-weight: 600;
    color: var(--sup-body);
  }
  .sup-response-sub {
    font-size: 11px;
    color: var(--sup-muted);
    margin-top: 2px;
  }
  .sup-response-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
  }

  /* ── Buttons ── */
  .sup-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-family: 'Poppins', system-ui, sans-serif;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .03em;
    padding: 8px 16px;
    border-radius: .75rem;
    border: none;
    cursor: pointer;
    transition: background .15s, box-shadow .15s;
    white-space: nowrap;
    text-decoration: none;
  }
  .sup-btn svg { width: 13px; height: 13px; flex-shrink: 0; }
  .sup-btn:disabled { cursor: wait; opacity: .55; }
  .sup-btn--accept  {
    background: var(--sup-success-text);
    color: #fff;
    border: 1px solid var(--sup-success-border);
  }
  .sup-btn--accept:hover {
    background: #047857;
    box-shadow: 0 2px 8px rgba(5, 150, 105, .25);
  }
  .sup-btn--decline {
    background: var(--sup-surface);
    color: var(--sup-text);
    border: 1px solid var(--sup-border);
  }
  .sup-btn--decline:hover {
    background: var(--sup-danger-bg);
    color: var(--sup-danger-text);
    border-color: var(--sup-danger-border);
  }
  .sup-btn--ghost {
    background: var(--sup-surface);
    color: var(--sup-muted);
    border: 1px solid var(--sup-border);
  }
  .sup-btn--ghost:hover {
    background: var(--sup-primary-soft);
    color: var(--sup-primary);
  }

  /* ── Badges ── */
  .sup-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .04em;
    text-transform: uppercase;
    padding: 3px 10px;
    border-radius: 999px;
    white-space: nowrap;
  }
  .sup-badge::before {
    content: '';
    display: inline-block;
    width: 5px;
    height: 5px;
    border-radius: 50%;
    background: currentColor;
    flex-shrink: 0;
  }
  .sup-badge--success { background: var(--sup-success-bg); color: var(--sup-success-text); }
  .sup-badge--warn    { background: var(--sup-warn-bg);    color: var(--sup-warn-text); }
  .sup-badge--danger  { background: var(--sup-danger-bg);  color: var(--sup-danger-text); }
  .sup-badge--neutral { background: var(--sup-neutral-bg); color: var(--sup-neutral-text); }

  /* ── Countdown pill ── */
  .sup-countdown {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 10px;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 999px;
    white-space: nowrap;
  }
  .sup-countdown svg { width: 10px; height: 10px; }
  .sup-countdown--urgent {
    background: var(--sup-danger-bg);
    color: var(--sup-danger-text);
  }
  .sup-countdown--soon {
    background: var(--sup-warn-bg);
    color: var(--sup-warn-text);
  }
  .sup-countdown--ok {
    background: var(--sup-success-bg);
    color: var(--sup-success-text);
  }

  /* ── Stats band ── */
  .sup-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
  }
  .sup-stat {
    background: var(--sup-surface);
    border: 1px solid var(--sup-border);
    border-left-width: 3px;
    border-radius: .75rem;
    padding: 16px 18px;
    transition: box-shadow .15s;
  }
  .sup-stat:hover { box-shadow: 0 2px 8px rgba(28, 25, 23, .06); }
  .sup-stat--neutral { border-left-color: #a8a29e; }
  .sup-stat--primary { border-left-color: var(--sup-primary); }
  .sup-stat-label {
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: var(--sup-muted);
    margin-bottom: 6px;
  }
  .sup-stat-value {
    font-size: 24px;
    font-weight: 700;
    line-height: 1.1;
    color: var(--sup-text);
    letter-spacing: -.02em;
    overflow-wrap: anywhere;
  }
  .sup-stat-value--sm { font-size: 16px; }
  .sup-stat-sub {
    font-size: 11px;
    color: var(--sup-muted);
    margin-top: 4px;
  }

  /* ── 2-column body ── */
  .sup-body {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 320px;
    gap: 18px;
    align-items: start;
    min-width: 0;
  }
  .sup-main, .sup-side { display: flex; flex-direction: column; gap: 18px; min-width: 0; }

  /* ── Cards ── */
  .sup-card {
    background: var(--sup-surface);
    border: 1px solid var(--sup-border);
    border-radius: .75rem;
    overflow: hidden;
    box-shadow: 0 1px 2px rgba(28, 25, 23, .04);
    min-width: 0;
  }
  .sup-card-head {
    padding: 14px 20px;
    border-bottom: 1px solid var(--sup-border-light);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
  }
  .sup-card-title {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .06em;
    text-transform: uppercase;
    color: var(--sup-muted);
  }
  .sup-card-body { padding: 16px 20px; }

  /* ── Services table ── */
  .sup-table-wrap { overflow-x: auto; }
  .sup-table { width: 100%; min-width: 700px; border-collapse: collapse; }
  .sup-table th {
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: var(--sup-muted);
    padding: 10px 20px;
    text-align: left;
    background: var(--sup-soft);
    border-bottom: 1px solid var(--sup-border);
    white-space: nowrap;
  }
  .sup-table th:last-child,
  .sup-table th.is-right { text-align: right; }
  .sup-table td {
    padding: 14px 20px;
    border-bottom: 1px solid var(--sup-soft);
    vertical-align: top;
  }
  .sup-table tr:last-child td { border-bottom: none; }
  .sup-table tr:hover td { background: var(--sup-soft); }
  .sup-table td:last-child,
  .sup-table td.is-right { text-align: right; }

  /* ── Service thumb ── */
  .sup-svc-thumb {
    width: 40px;
    height: 40px;
    border-radius: .6rem;
    object-fit: cover;
    border: 1px solid var(--sup-border);
    flex-shrink: 0;
  }
  .sup-svc-thumb-placeholder {
    width: 40px;
    height: 40px;
    border-radius: .6rem;
    background: var(--sup-soft);
    border: 1px solid var(--sup-border);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }
  .sup-svc-thumb-placeholder svg { width: 16px; height: 16px; color: var(--sup-muted); }
  .sup-svc-name { font-size: 13px; font-weight: 700; color: var(--sup-text); }
  .sup-svc-cat  { font-size: 11px; color: var(--sup-muted); margin-top: 2px; }
  .sup-svc-note {
    font-size: 11px;
    color: var(--sup-body);
    margin-top: 6px;
    padding: 7px 10px;
    background: var(--sup-soft);
    border-left: 3px solid var(--sup-border);
    border-radius: 0 .5rem .5rem 0;
    line-height: 1.5;
    overflow-wrap: anywhere;
  }
  .sup-svc-addon {
    display: inline-flex;
    align-items: center;
    gap: 3px;
    font-size: 10px;
    font-weight: 600;
    padding: 1px 7px;
    border-radius: 999px;
    background: var(--sup-info-bg);
    color: var(--sup-info-text);
    margin-top: 3px;
  }
  .sup-price {
    font-weight: 700;
    font-size: 14px;
    color: var(--sup-text);
    text-align: right;
    white-space: nowrap;
  }

  /* ── Sidebar elements ── */
  .sup-side-head {
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: var(--sup-muted);
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 6px;
  }
  .sup-side-head svg { width: 12px; height: 12px; }
  .sup-contact-name {
    font-size: 13px;
    font-weight: 700;
    color: var(--sup-text);
    margin-bottom: 4px;
  }
  .sup-contact-row {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    color: var(--sup-muted);
    margin-bottom: 3px;
    overflow-wrap: anywhere;
  }
  .sup-contact-row svg { width: 11px; height: 11px; flex-shrink: 0; }

  /* Supplier rows */
  .sup-sup-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    padding: 7px 0;
    border-bottom: 1px solid var(--sup-soft);
  }
  .sup-sup-row:last-child { border-bottom: none; }
  .sup-sup-info { display: flex; align-items: center; gap: 8px; min-width: 0; }
  .sup-sup-thumb {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    object-fit: cover;
    border: 1px solid var(--sup-border);
    flex-shrink: 0;
  }
  .sup-sup-initial {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: var(--sup-soft);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    font-weight: 700;
    color: var(--sup-muted);
    flex-shrink: 0;
  }
  .sup-sup-name {
    font-size: 12px;
    font-weight: 700;
    color: var(--sup-text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  /* Special requests */
  .sup-sr-item {
    font-size: 12px;
    color: var(--sup-body);
    line-height: 1.6;
    padding: 9px 11px;
    background: var(--sup-surface);
    border: 1px solid var(--sup-border);
    border-radius: .6rem;
    margin-bottom: 6px;
    overflow-wrap: anywhere;
  }
  .sup-sr-item:last-child { margin-bottom: 0; }

  /* ── Empty states ── */
  .sup-empty {
    padding: 32px 24px;
    text-align: center;
    font-size: 12px;
    color: var(--sup-muted);
    font-weight: 600;
  }

  /* ── Modal ── */
  .sup-modal-overlay {
    position: fixed;
    inset: 0;
    z-index: 50;
    background: rgba(34, 24, 19, .45);
    display: none;
    align-items: center;
    justify-content: center;
    padding: 16px;
  }
  .sup-modal-overlay.is-open { display: flex; }
  .sup-modal {
    background: var(--sup-surface);
    border: 1px solid var(--sup-border);
    border-radius: .75rem;
    width: 100%;
    max-width: 440px;
    box-shadow: 0 20px 60px rgba(34, 24, 19, .18);
  }
  .sup-modal-head {
    padding: 18px 20px;
    border-bottom: 1px solid var(--sup-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
  .sup-modal-title {
    font-size: 18px;
    font-weight: 700;
    color: var(--sup-text);
  }
  .sup-modal-close {
    background: none;
    border: none;
    cursor: pointer;
    color: var(--sup-muted);
    padding: 4px;
    display: flex;
  }
  .sup-modal-close:hover { color: var(--sup-text); }
  .sup-modal-close svg { width: 16px; height: 16px; }
  .sup-modal-body { padding: 20px; }
  .sup-field { margin-bottom: 14px; }
  .sup-label {
    display: block;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: var(--sup-muted);
    margin-bottom: 5px;
  }
  .sup-input, .sup-textarea {
    width: 100%;
    font-family: 'Poppins', system-ui, sans-serif;
    font-size: 13px;
    color: var(--sup-text);
    background: var(--sup-surface);
    border: 1px solid var(--sup-border);
    border-radius: .6rem;
    padding: 9px 11px;
    outline: none;
    transition: border-color .15s;
  }
  .sup-input:focus, .sup-textarea:focus { border-color: var(--sup-primary); }
  .sup-textarea { resize: vertical; min-height: 80px; }
  .sup-field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
  .sup-modal-foot {
    padding: 14px 20px;
    border-top: 1px solid var(--sup-border);
    display: flex;
    gap: 8px;
  }
  .sup-modal-foot .sup-btn { flex: 1; justify-content: center; }

  /* ── Responsive ── */
  @media (max-width: 1050px) {
    .sup-body { grid-template-columns: 1fr; }
    .sup-stats { grid-template-columns: repeat(2, 1fr); }
  }
  @media (max-width: 680px) {
    .sup-stats { grid-template-columns: 1fr 1fr; }
    .sup-header { padding: 16px; }
    .sup-card-head, .sup-card-body { padding-left: 16px; padding-right: 16px; }
    .sup-ref { font-size: 20px; }
    .sup-response-actions .sup-btn { width: 100%; justify-content: center; }
    .sup-response-actions { width: 100%; }
    .sup-field-row { grid-template-columns: 1fr; }
  }
  @media (max-width: 480px) {
    .sup-stats { grid-template-columns: 1fr; }
    .sup-response-bar { flex-direction: column; align-items: stretch; }
  }

  /* ── Tabbed booking workspace ── */
  .sup-booking-tabs {
    position: sticky;
    top: 76px;
    z-index: 20;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 8px;
    border: 1px solid #e7e5e4;
    border-radius: 14px;
    background: rgba(250,249,248,.94);
    box-shadow: 0 1px 2px rgba(28,25,23,.05);
    backdrop-filter: blur(14px);
  }
  .sup-booking-tab-list {
    display: flex;
    align-items: center;
    gap: 4px;
    min-width: 0;
  }
  .sup-booking-tab {
    display: inline-flex;
    min-height: 38px;
    align-items: center;
    gap: 7px;
    padding: 0 13px;
    border: 0;
    border-radius: 9px;
    color: #78716c;
    background: transparent;
    font-size: 11px;
    font-weight: 750;
    white-space: nowrap;
    cursor: pointer;
  }
  .sup-booking-tab svg { width: 14px; height: 14px; }
  .sup-booking-tab:hover { color: #673049; background: #fde8ef; }
  .sup-booking-tab.is-active {
    color: #673049;
    background: #fff;
    box-shadow: 0 2px 9px rgba(28,25,23,.08);
  }
  .sup-booking-tab:focus-visible {
    outline: 3px solid rgba(103,48,73,.15);
    outline-offset: 2px;
  }
  .sup-booking-tab-status {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding-right: 8px;
    color: #78716c;
    font-size: 10px;
    font-weight: 650;
  }
  .sup-booking-tab-status i {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: <?= $needsResponse ? '#d97706' : '#059669' ?>;
  }
  .sup-tabbed-body {
    display: contents;
  }
  .sup-tabbed-body .sup-main,
  .sup-tabbed-body .sup-side {
    display: contents;
  }
  [data-booking-panel] {
    display: none;
    width: 100%;
    animation: none;
  }
  [data-booking-panel].is-active {
    display: block;
    animation: supTabEnter .22s ease both;
  }
  @keyframes supTabEnter {
    from { opacity: 0; transform: translateY(5px); }
    to { opacity: 1; transform: translateY(0); }
  }
  .sup-stats[data-booking-panel] {
    grid-template-columns: repeat(4, minmax(0,1fr));
  }
  .sup-stats[data-booking-panel].is-active {
    display: grid;
  }
  .sup-payment-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0,1fr));
    gap: 12px;
    padding: 20px;
  }
  .sup-payment-fact {
    padding: 16px;
    border: 1px solid #e7e5e4;
    border-radius: 12px;
    background: #f9f8f6;
  }
  .sup-payment-fact small {
    display: block;
    color: #78716c;
    font-size: 10px;
    font-weight: 700;
  }
  .sup-payment-fact strong {
    display: block;
    margin-top: 5px;
    color: #1c1917;
    font-size: 18px;
  }
  .sup-payment-track {
    height: 7px;
    margin: 0 20px 20px;
    overflow: hidden;
    border-radius: 999px;
    background: #e7e5e4;
  }
  .sup-payment-fill {
    height: 100%;
    border-radius: inherit;
    background: #673049;
  }
  @media (max-width: 820px) {
    .sup-booking-tabs { align-items: flex-start; flex-direction: column; }
    .sup-booking-tab-list { width:100%; overflow-x:auto; }
    .sup-booking-tab-status { padding:0 8px 4px; }
    .sup-stats[data-booking-panel] { grid-template-columns:repeat(2,minmax(0,1fr)); }
    .sup-payment-grid { grid-template-columns:1fr; }
  }
  @media (max-width: 520px) {
    .sup-page { padding: 14px 12px; }
    .sup-booking-tab span { display:none; }
    .sup-booking-tab { min-width:42px; justify-content:center; padding:0 10px; }
    .sup-stats[data-booking-panel] { grid-template-columns:1fr; }
  }

  /* ── Event run sheet layout ── */
  .sup-runsheet-head {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 18px;
    padding: 4px 2px;
  }
  .sup-runsheet-head span {
    color: #673049;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: .11em;
    text-transform: uppercase;
  }
  .sup-runsheet-head h2 {
    margin: 3px 0 0;
    color: #34232b;
    font-family: "Playfair Display", serif;
    font-size: 27px;
    font-weight: 650;
    letter-spacing: -.025em;
  }
  .sup-runsheet-head p {
    margin: 0;
    color: #78716c;
    font-size: 11px;
  }
  .sup-runsheet-layout {
    display: grid;
    grid-template-columns: 260px minmax(0, 1fr);
    gap: 14px;
    align-items: start;
  }
  .sup-runsheet-layout > .sup-stats {
    grid-column: 1;
    grid-row: 1;
    grid-template-columns: 1fr;
    position: sticky;
    top: 84px;
  }
  .sup-runsheet-layout > .sup-body {
    display: contents;
  }
  .sup-runsheet-layout .sup-main {
    grid-column: 2;
    grid-row: 1 / span 3;
  }
  .sup-runsheet-layout .sup-side {
    grid-column: 1;
    grid-row: 2;
  }
  .sup-runsheet-layout > .sup-payment-card {
    grid-column: 2;
  }
  .sup-runsheet-layout .sup-stat {
    border-left-width: 1px;
    border-color: #e7e5e4;
    border-radius: 12px;
    background: #fff;
    padding: 14px;
    box-shadow: 0 1px 2px rgba(28,25,23,.04);
  }
  .sup-runsheet-layout .sup-stat-value {
    font-size: 18px;
  }
  .sup-runsheet-layout .sup-main > .sup-card {
    position: relative;
  }
  .sup-runsheet-layout .sup-main > .sup-card + .sup-card {
    margin-top: 0;
  }
  .sup-runsheet-layout .sup-main > .sup-card:not(:first-child)::before {
    content: '';
    position: absolute;
    top: -19px;
    left: 28px;
    width: 2px;
    height: 19px;
    background: #eadce3;
  }
  .sup-runsheet-layout .sup-card {
    border-color: #e7e5e4;
    border-radius: 16px;
  }
  .sup-runsheet-layout .sup-card-title {
    color: #1c1917;
    font-size: 12px;
    letter-spacing: -.01em;
    text-transform: none;
  }
  .sup-runsheet-layout .sup-table thead {
    background: #f9f8f6;
  }
  .sup-runsheet-layout .sup-table tr:hover td {
    background: #f5f5f3;
  }
  .sup-timeline-row.is-mine td {
    background: #fff8fb;
    border-top: 1px solid #f3cddb;
    border-bottom: 1px solid #f3cddb;
  }
  .sup-timeline-row.is-mine td:first-child {
    border-left: 4px solid #673049;
  }
  .sup-timeline-row.is-mine:hover td {
    background: #fdeef4;
  }
  .sup-timeline-row.is-clickable {
    cursor: pointer;
  }
  .sup-timeline-row.is-clickable:focus {
    outline: 3px solid rgba(103,48,73,.18);
    outline-offset: -3px;
  }
  .sup-own-service {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    margin-top: 5px;
    padding: 2px 7px;
    border-radius: 999px;
    color: #673049;
    background: #fde8ef;
    font-size: 9px;
    font-weight: 800;
  }
  .sup-own-service::before {
    content: '';
    width: 5px;
    height: 5px;
    border-radius: 50%;
    background: currentColor;
  }
  .sup-timeline-guest {
    color: #1c1917;
    font-size: 12px;
    font-weight: 750;
  }
  .sup-service-more {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    color: #673049;
    font-size: 9px;
    font-weight: 750;
  }
  .sup-service-drawer-backdrop {
    position: fixed;
    inset: 0;
    z-index: 70;
    visibility: hidden;
    background: rgba(36,24,30,.2);
    opacity: 0;
    backdrop-filter: blur(2px);
    transition: opacity .2s ease, visibility .2s ease;
  }
  .sup-service-drawer {
    position: fixed;
    top: 0;
    right: 0;
    z-index: 80;
    width: min(430px,92vw);
    height: 100vh;
    overflow-y: auto;
    border-left: 1px solid #ead8c7;
    background: #fff;
    padding: 25px;
    box-shadow: -20px 0 50px rgba(52,35,43,.13);
    transform: translateX(105%);
    transition: transform .24s ease;
  }
  body.sup-service-drawer-open .sup-service-drawer-backdrop {
    visibility: visible;
    opacity: 1;
  }
  body.sup-service-drawer-open .sup-service-drawer {
    transform: translateX(0);
  }
  .sup-service-drawer-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 18px;
    padding-bottom: 20px;
    border-bottom: 1px solid #ead8c7;
  }
  .sup-service-drawer-kicker {
    margin: 0 0 5px;
    color: #9b7d89;
    font-size: 9px;
    font-weight: 800;
    letter-spacing: .15em;
    text-transform: uppercase;
  }
  .sup-service-drawer-title {
    margin: 0;
    color: #34232b;
    font: 700 21px "Playfair Display",serif;
  }
  .sup-service-drawer-close {
    display: inline-flex;
    width: 36px;
    height: 36px;
    flex: 0 0 36px;
    align-items: center;
    justify-content: center;
    border: 1px solid #ead8c7;
    border-radius: 10px;
    background: #faf5ef;
    color: #7b5c69;
    cursor: pointer;
  }
  .sup-service-detail-list {
    display: grid;
    margin: 12px 0 0;
  }
  .sup-service-detail {
    padding: 15px 0;
    border-bottom: 1px solid #f0e5dc;
  }
  .sup-service-detail dt {
    color: #a58b96;
    font-size: 9px;
    font-weight: 800;
    letter-spacing: .12em;
    text-transform: uppercase;
  }
  .sup-service-detail dd {
    margin: 6px 0 0;
    color: #34232b;
    font-size: 12px;
    font-weight: 600;
    line-height: 1.55;
    overflow-wrap: anywhere;
  }
  .sup-drawer-timeline {
    margin-top: 22px;
  }
  .sup-drawer-timeline-head {
    padding-bottom: 12px;
    border-bottom: 1px solid #ead8c7;
  }
  .sup-drawer-timeline-head span {
    color: #9b7d89;
    font-size: 9px;
    font-weight: 800;
    letter-spacing: .14em;
    text-transform: uppercase;
  }
  .sup-drawer-timeline-head h3 {
    margin: 4px 0 0;
    color: #34232b;
    font-size: 14px;
    font-weight: 750;
  }
  .sup-drawer-timeline-list {
    position: relative;
    display: grid;
    gap: 0;
    padding-top: 14px;
  }
  .sup-drawer-timeline-item {
    position: relative;
    display: grid;
    grid-template-columns: 52px 22px minmax(0,1fr);
    gap: 9px;
    padding-bottom: 15px;
  }
  .sup-drawer-timeline-item:not(:last-child)::after {
    content: '';
    position: absolute;
    left: 62px;
    top: 21px;
    bottom: -1px;
    width: 1px;
    background: #ead8c7;
  }
  .sup-drawer-time {
    padding-top: 3px;
    color: #9b7d89;
    font-size: 9px;
    font-weight: 750;
    text-align: right;
  }
  .sup-drawer-dot {
    position: relative;
    z-index: 1;
    display: grid;
    width: 22px;
    height: 22px;
    place-items: center;
    border: 4px solid #fff;
    border-radius: 50%;
    background: #d6c8ce;
  }
  .sup-drawer-timeline-copy {
    padding: 10px 11px;
    border: 1px solid #ead8c7;
    border-radius: 10px;
    background: #fff;
  }
  .sup-drawer-timeline-copy strong {
    display: block;
    color: #34232b;
    font-size: 11px;
  }
  .sup-drawer-timeline-copy small {
    display: block;
    margin-top: 3px;
    color: #9b7d89;
    font-size: 9px;
  }
  .sup-drawer-timeline-item.is-mine .sup-drawer-dot {
    background: #b87994;
  }
  .sup-drawer-timeline-item.is-mine .sup-drawer-timeline-copy {
    border-color: #e8bace;
    background: #fff8fb;
  }
  .sup-drawer-timeline-item.is-current .sup-drawer-dot {
    background: #673049;
    box-shadow: 0 0 0 4px #fde8ef;
  }
  .sup-drawer-timeline-item.is-current .sup-drawer-timeline-copy {
    border-color: #673049;
    background: #fde8ef;
    box-shadow: 0 4px 14px rgba(103,48,73,.1);
  }
  .sup-drawer-timeline-label {
    display: inline-flex;
    margin-top: 6px;
    border-radius: 999px;
    padding: 2px 7px;
    color: #673049;
    background: #fff;
    font-size: 8px;
    font-weight: 800;
  }
  @media (max-width: 980px) {
    .sup-runsheet-layout { grid-template-columns: 1fr; }
    .sup-runsheet-layout > .sup-stats,
    .sup-runsheet-layout .sup-main,
    .sup-runsheet-layout .sup-side,
    .sup-runsheet-layout > .sup-payment-card {
      grid-column: 1;
      grid-row: auto;
      position: static;
    }
    .sup-runsheet-layout > .sup-stats { grid-template-columns: repeat(2,minmax(0,1fr)); }
  }
  @media (max-width: 560px) {
    .sup-runsheet-head { align-items:flex-start; flex-direction:column; }
    .sup-runsheet-layout > .sup-stats { grid-template-columns:1fr; }
  }

  /* ── Task-first supplier view ── */
  .sup-header {
    padding: 0 2px 14px;
    border: 0;
    background: transparent;
    box-shadow: none;
  }
  .sup-header .sup-cust { display: none; }
  .sup-header-meta > .sup-badge { display: none; }
  .sup-assignment {
    margin-top: 18px;
    border: 1px solid #d9c7b8;
    border-radius: 18px;
    background: #fffdfb;
    box-shadow: 0 12px 32px rgba(52,35,43,.06);
    overflow: hidden;
  }
  .sup-assignment-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 18px;
    padding: 22px 24px 18px;
  }
  .sup-assignment-kicker {
    margin: 0 0 5px;
    color: #a58b96;
    font-size: 9px;
    font-weight: 850;
    letter-spacing: .14em;
    text-transform: uppercase;
  }
  .sup-assignment-title {
    margin: 0;
    color: #34232b;
    font-family: "Playfair Display", serif;
    font-size: clamp(24px, 2.4vw, 34px);
    font-weight: 650;
    line-height: 1.1;
  }
  .sup-assignment-category {
    margin-top: 6px;
    color: #8f6e7c;
    font-size: 11px;
    font-weight: 700;
  }
  .sup-assignment-status {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    border-radius: 999px;
    padding: 8px 12px;
    background: <?= $needsResponse ? '#fff3db' : '#e5f7ef' ?>;
    color: <?= $needsResponse ? '#9a5c12' : '#08715a' ?>;
    font-size: 10px;
    font-weight: 850;
    white-space: nowrap;
  }
  .sup-assignment-status::before {
    content: "";
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: currentColor;
  }
  .sup-assignment-facts {
    display: grid;
    grid-template-columns: repeat(4, minmax(0,1fr));
    border-top: 1px solid #eee0d5;
  }
  .sup-assignment-fact {
    min-width: 0;
    padding: 15px 20px 17px;
    border-right: 1px solid #eee0d5;
  }
  .sup-assignment-fact:last-child { border-right: 0; }
  .sup-assignment-fact small {
    display: block;
    margin-bottom: 5px;
    color: #b0929e;
    font-size: 8px;
    font-weight: 850;
    letter-spacing: .1em;
    text-transform: uppercase;
  }
  .sup-assignment-fact strong {
    display: block;
    color: #34232b;
    font-size: 12px;
    font-weight: 750;
    line-height: 1.35;
    overflow-wrap: anywhere;
  }
  .sup-assignment-fact span {
    display: block;
    margin-top: 2px;
    color: #a58b96;
    font-size: 10px;
    font-weight: 600;
  }
  .sup-quiet-notice {
    display: flex;
    align-items: flex-start;
    gap: 9px;
    margin-top: 12px;
    border: 1px solid #dedbea;
    border-radius: 12px;
    padding: 11px 13px;
    background: #f8f7fc;
    color: #665d78;
    font-size: 11px;
    line-height: 1.5;
  }
  .sup-quiet-notice svg { width: 15px; height: 15px; flex: 0 0 15px; margin-top: 1px; }
  .sup-quiet-notice strong { color: #433955; }
  .sup-secondary-action {
    margin-top: 12px;
    border-top: 1px solid #ead8c7;
    padding-top: 10px;
  }
  .sup-secondary-action summary,
  .sup-booking-details > summary {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    cursor: pointer;
    color: #6d4c5b;
    font-size: 11px;
    font-weight: 800;
    list-style: none;
  }
  .sup-secondary-action summary::-webkit-details-marker,
  .sup-booking-details > summary::-webkit-details-marker { display: none; }
  .sup-secondary-action summary::after,
  .sup-booking-details > summary::after { content: "⌄"; font-size: 14px; }
  .sup-secondary-action[open] summary::after,
  .sup-booking-details[open] > summary::after { content: "⌃"; }
  .sup-secondary-action .sup-selfdecline {
    margin-top: 10px;
    border-top: 0;
    padding-top: 0;
  }
  .sup-booking-details {
    margin-top: 18px;
    border: 1px solid #ead8c7;
    border-radius: 14px;
    background: #fff;
    overflow: hidden;
  }
  .sup-booking-details > summary {
    display: flex;
    justify-content: space-between;
    padding: 15px 18px;
    background: #faf6f1;
  }
  .sup-booking-details-content { padding: 22px 0 0; }
  .sup-booking-details .sup-runsheet-head {
    align-items: center;
    padding: 0 18px 14px;
    border-bottom: 1px solid #eee4dc;
  }
  .sup-booking-details .sup-runsheet-head span { display: none; }
  .sup-booking-details .sup-runsheet-head h2 {
    margin: 0;
    font-family: inherit;
    font-size: 14px;
    font-weight: 800;
    letter-spacing: 0;
  }
  .sup-booking-details .sup-runsheet-head p {
    color: #a58b96;
    font-size: 10px;
  }
  .sup-booking-details .sup-runsheet-layout {
    display: block;
    margin-top: 0;
  }
  .sup-booking-details .sup-stats,
  .sup-booking-details .sup-main > .sup-card:first-child,
  .sup-booking-details .sup-side {
    display: none;
  }
  .sup-booking-details .sup-body,
  .sup-booking-details .sup-main {
    display: block;
  }
  .sup-booking-details .sup-main {
    padding: 14px 18px 4px;
  }
  .sup-booking-details .sup-main > .sup-card {
    margin: 0 0 12px;
    border-radius: 12px;
    box-shadow: none;
  }
  .sup-booking-details .sup-main > .sup-card:not(:first-child)::before { display: none; }
  .sup-booking-details .sup-card-head {
    min-height: 42px;
    padding: 10px 13px;
  }
  .sup-booking-details .sup-card-title {
    font-size: 11px;
    line-height: 1.35;
  }
  .sup-booking-details .sup-card-head .sup-badge {
    padding: 3px 7px;
    font-size: 8px;
  }
  .sup-booking-details .sup-main .sup-card-head .sup-badge { display: none; }
  .sup-booking-details .sup-table th {
    padding: 8px 12px;
    font-size: 8px;
    letter-spacing: .07em;
  }
  .sup-booking-details .sup-table td {
    padding: 10px 12px;
    font-size: 10px !important;
  }
  .sup-booking-details .sup-svc-name {
    font-size: 11px;
    line-height: 1.35;
  }
  .sup-booking-details .sup-svc-cat {
    margin-top: 1px;
    font-size: 9px;
  }
  .sup-booking-details .sup-own-service {
    margin-top: 3px;
    padding: 1px 6px;
    font-size: 8px;
  }
  .sup-booking-details .sup-service-more {
    margin-top: 2px;
    font-size: 8px;
  }
  .sup-booking-details .sup-payment-card {
    margin: 2px 18px 18px;
    border-radius: 12px;
    box-shadow: none;
  }
  .sup-booking-details .sup-payment-grid {
    gap: 0;
    padding: 0;
    border-top: 1px solid #eee4dc;
  }
  .sup-booking-details .sup-payment-fact {
    border: 0;
    border-right: 1px solid #eee4dc;
    border-radius: 0;
    padding: 12px 14px;
    background: transparent;
  }
  .sup-booking-details .sup-payment-fact:last-child { border-right: 0; }
  .sup-booking-details .sup-payment-fact small { font-size: 8px; }
  .sup-booking-details .sup-payment-fact strong {
    margin-top: 3px;
    font-size: 13px;
  }
  .sup-booking-details .sup-payment-track {
    height: 4px;
    margin: 0 14px 13px;
  }

  /* Unified compact component system */
  .sup-btn {
    min-height: 36px;
    padding: 8px 13px;
    border-radius: 9px;
    font-family: inherit;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 0;
    box-shadow: none;
  }
  .sup-btn:focus-visible,
  .sup-input:focus-visible,
  .sup-textarea:focus-visible,
  .sup-modal-close:focus-visible,
  .sup-service-drawer-close:focus-visible,
  .sup-secondary-action summary:focus-visible,
  .sup-booking-details > summary:focus-visible {
    outline: 3px solid rgba(109,76,91,.16);
    outline-offset: 2px;
  }
  .sup-btn--accept {
    border-color: #08715a;
    background: #08715a;
  }
  .sup-btn--accept:hover { background: #075f4c; box-shadow: none; }
  .sup-btn--decline,
  .sup-btn--ghost {
    border-color: #dfcec1;
    background: #fff;
    color: #6d4c5b;
  }
  .sup-btn--decline:hover {
    border-color: #dc9f9f;
    background: #fff7f7;
    color: #9b2c2c;
  }
  .sup-btn--ghost:hover {
    border-color: #cdb5bf;
    background: #faf6f8;
  }
  .sup-response-bar {
    margin-top: 14px;
    border: 1px solid #e3c88f;
    border-radius: 12px;
    padding: 12px 13px;
    background: #fffaf0;
  }
  .sup-response-icon {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    background: #f7e8c9;
    color: #8c5b17;
  }
  .sup-response-text {
    color: #4e382f;
    font-size: 11px;
    font-weight: 800;
  }
  .sup-response-sub {
    color: #9d8175;
    font-size: 10px;
    line-height: 1.45;
  }
  .sup-response-actions { gap: 6px; }
  .sup-secondary-action {
    border-top: 0;
    padding-top: 0;
  }
  .sup-secondary-action > summary {
    padding: 5px 1px;
    color: #90727e;
    font-size: 10px;
  }
  .sup-secondary-action .sup-selfdecline {
    margin-top: 7px;
    border: 1px solid #ead8c7;
    border-radius: 12px;
    padding: 13px;
    background: #fffdfb;
  }
  .sup-selfdecline-head {
    gap: 8px;
    margin-bottom: 9px;
  }
  .sup-selfdecline-head > svg {
    width: 15px;
    height: 15px;
  }
  .sup-selfdecline-list { gap: 6px; }
  .sup-selfdecline-item {
    gap: 10px;
    border-color: #eee0d5;
    border-radius: 9px;
    padding: 8px 9px;
    background: #faf7f3;
  }
  .sup-selfdecline-svc strong { font-size: 11px; }
  .sup-selfdecline-svc span { font-size: 9px; }
  .sup-empty {
    padding: 22px 18px;
    font-size: 10px;
  }

  /* Drawer */
  .sup-service-drawer-backdrop {
    background: rgba(44,34,38,.22);
    backdrop-filter: blur(3px);
  }
  .sup-service-drawer {
    width: min(390px,94vw);
    border-left-color: #e4d5ca;
    padding: 0;
    box-shadow: -14px 0 40px rgba(52,35,43,.12);
  }
  .sup-service-drawer-head {
    position: sticky;
    top: 0;
    z-index: 2;
    align-items: center;
    padding: 16px 18px;
    border-bottom-color: #eee4dc;
    background: rgba(255,255,255,.96);
    backdrop-filter: blur(10px);
  }
  .sup-service-drawer-kicker {
    margin-bottom: 3px;
    font-size: 8px;
    letter-spacing: .11em;
  }
  .sup-service-drawer-title {
    font-family: inherit;
    font-size: 15px;
    font-weight: 800;
  }
  .sup-service-drawer-close {
    width: 32px;
    height: 32px;
    flex-basis: 32px;
    border-color: #e4d5ca;
    border-radius: 8px;
    background: #fff;
  }
  .sup-service-detail-list {
    grid-template-columns: repeat(2,minmax(0,1fr));
    gap: 0;
    margin: 0;
    padding: 8px 18px 2px;
  }
  .sup-service-detail {
    min-width: 0;
    padding: 11px 8px 11px 0;
    border-bottom-color: #f0e7e0;
  }
  .sup-service-detail:nth-child(even) { padding-left: 10px; padding-right: 0; }
  .sup-service-detail dt {
    font-size: 8px;
    letter-spacing: .08em;
  }
  .sup-service-detail dd {
    margin-top: 4px;
    font-size: 10px;
    line-height: 1.4;
  }
  .sup-drawer-timeline {
    margin: 14px 18px 20px;
    border: 1px solid #eadfd7;
    border-radius: 11px;
    overflow: hidden;
  }
  .sup-drawer-timeline-head {
    padding: 10px 12px;
    border-bottom-color: #eadfd7;
    background: #faf7f3;
  }
  .sup-drawer-timeline-head span { display: none; }
  .sup-drawer-timeline-head h3 {
    margin: 0;
    font-size: 11px;
  }
  .sup-drawer-timeline-list { padding: 11px 12px 0; }
  .sup-drawer-timeline-item {
    grid-template-columns: 44px 16px minmax(0,1fr);
    gap: 7px;
    padding-bottom: 11px;
  }
  .sup-drawer-timeline-item:not(:last-child)::after {
    left: 51px;
    top: 16px;
  }
  .sup-drawer-time { font-size: 8px; }
  .sup-drawer-dot {
    width: 16px;
    height: 16px;
    border-width: 3px;
  }
  .sup-drawer-timeline-copy {
    border-radius: 8px;
    padding: 7px 8px;
  }
  .sup-drawer-timeline-copy strong { font-size: 9px; }
  .sup-drawer-timeline-copy small { font-size: 8px; }
  .sup-drawer-timeline-label {
    margin-top: 4px;
    padding: 1px 5px;
    font-size: 7px;
  }

  /* Modal and forms */
  .sup-modal-overlay {
    padding: 14px;
    background: rgba(44,34,38,.32);
    backdrop-filter: blur(4px);
  }
  .sup-modal {
    max-width: 410px;
    border-color: #dfcec1;
    border-radius: 14px;
    box-shadow: 0 18px 55px rgba(52,35,43,.16);
    overflow: hidden;
  }
  .sup-modal-head {
    min-height: 52px;
    padding: 13px 16px;
    border-bottom-color: #eee4dc;
  }
  .sup-modal-title {
    font-size: 14px;
    font-weight: 800;
  }
  .sup-modal-close {
    width: 30px;
    height: 30px;
    justify-content: center;
    align-items: center;
    border: 1px solid #e7d9cf;
    border-radius: 8px;
    background: #fff;
  }
  .sup-modal-body { padding: 15px 16px; }
  .sup-field { margin-bottom: 11px; }
  .sup-label {
    margin-bottom: 4px;
    font-size: 8px;
    letter-spacing: .07em;
  }
  .sup-input,
  .sup-textarea {
    min-height: 38px;
    border-color: #dfcec1;
    border-radius: 9px;
    padding: 8px 10px;
    font-family: inherit;
    font-size: 11px;
  }
  .sup-input:focus,
  .sup-textarea:focus {
    border-color: #8a6675;
    box-shadow: 0 0 0 3px rgba(109,76,91,.1);
  }
  .sup-textarea { min-height: 76px; }
  .sup-field-row { gap: 8px; }
  .sup-modal-foot {
    justify-content: flex-end;
    padding: 11px 16px;
    border-top-color: #eee4dc;
    background: #faf7f3;
  }
  .sup-modal-foot .sup-btn {
    flex: 0 0 auto;
    justify-content: center;
  }
  @media (max-width: 820px) {
    .sup-assignment-head { flex-direction: column; }
    .sup-assignment-facts { grid-template-columns: repeat(2,minmax(0,1fr)); }
    .sup-assignment-fact:nth-child(2) { border-right: 0; }
    .sup-assignment-fact:nth-child(-n+2) { border-bottom: 1px solid #eee0d5; }
    .sup-booking-details .sup-runsheet-head { align-items: flex-start; }
    .sup-booking-details .sup-payment-grid { grid-template-columns: 1fr; }
    .sup-booking-details .sup-payment-fact {
      border-right: 0;
      border-bottom: 1px solid #eee4dc;
    }
    .sup-booking-details .sup-payment-fact:last-child { border-bottom: 0; }
    .sup-response-bar { align-items: flex-start; }
    .sup-response-actions { width: 100%; }
    .sup-response-actions .sup-btn { flex: 1; justify-content: center; }
  }
  @media (max-width: 560px) {
    .sup-assignment-facts { grid-template-columns: 1fr; }
    .sup-assignment-fact { border-right: 0; border-bottom: 1px solid #eee0d5; }
    .sup-assignment-fact:last-child { border-bottom: 0; }
    .sup-service-detail-list { grid-template-columns: 1fr; }
    .sup-service-detail:nth-child(even) { padding-left: 0; }
    .sup-field-row { grid-template-columns: 1fr; }
  }
</style>

<div class="sup-page">

  <!-- ── Header ── -->
  <header class="sup-header">
    <div class="sup-header-top">
      <div class="sup-header-left">
        <a href="<?= URLROOT ?>/supplier/bookings" class="sup-back">
          <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M10 12L6 8l4-4"/></svg>
          All bookings
        </a>
        <h1 class="sup-ref">
          <?php
            $refParts = $bookingRef ? explode('-', $bookingRef, 2) : ['Booking', ''];
            echo $h($refParts[0]);
            if (!empty($refParts[1])) echo '-<em>' . $h($refParts[1]) . '</em>';
          ?>
        </h1>
        <div class="sup-header-meta">
          <span class="sup-badge <?= $statusBadgeClass($supplierStatus) ?>"><?= $h(ucfirst($supplierStatus)) ?></span>
          <?php if ($daysUntil !== null): ?>
            <span class="sup-countdown <?= $daysUntil <= 7 ? 'sup-countdown--urgent' : ($daysUntil <= 21 ? 'sup-countdown--soon' : 'sup-countdown--ok') ?>">
              <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="8" cy="8" r="6"/><path d="M8 4v4l3 2"/></svg>
              <?php if ($daysUntil < 0): echo abs($daysUntil) . ' days ago';
              elseif ($daysUntil === 0): echo 'Today';
              else: echo $daysUntil . ' day' . ($daysUntil === 1 ? '' : 's') . ' away'; endif; ?>
            </span>
          <?php endif; ?>
          <span class="sup-badge <?= $statusBadgeClass($bookingStatus) ?>">Booking <?= $h($bookingStatusLabel) ?></span>
        </div>
      </div>

      <!-- Customer mini-card -->
      <div class="sup-cust">
        <div class="sup-cust-avatar"><?= $h($customerInitial) ?></div>
        <div>
          <div class="sup-cust-name"><?= $h($customerName) ?></div>
          <div class="sup-cust-items">
            <?php if ($customerPhone !== ''): ?>
              <span class="sup-cust-item">
                <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 2h2.5l1 3-1.5 1a8 8 0 003 3l1-1.5 3 1V12a1 1 0 01-1 1C6 13 2 8.5 2 4a1 1 0 011-1z"/></svg>
                <?= $h($customerPhone) ?>
              </span>
            <?php endif; ?>
            <?php if ($customerEmail !== ''): ?>
              <span class="sup-cust-item">
                <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="4" width="12" height="9" rx="1"/><path d="M2 5l6 5 6-5"/></svg>
                <?= $h($customerEmail) ?>
              </span>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Response bar — inline, no yellow background -->
    <?php if ($needsResponse): ?>
    <div class="sup-response-bar">
      <div class="sup-response-info">
        <div class="sup-response-icon">
          <?php if ($bookingStatus === 'pending_supplier_response'): ?>
            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 2a4 4 0 014 4v2l1 2H3l1-2V6a4 4 0 014-4zm-1 10h2a1 1 0 01-2 0z"/></svg>
          <?php else: ?>
            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="8" cy="8" r="6"/><path d="M8 4v4l3 2"/></svg>
          <?php endif; ?>
        </div>
        <div>
          <?php if ($bookingStatus === 'pending_supplier_response'): ?>
            <div class="sup-response-text">New booking request — customer is requesting your services</div>
            <div class="sup-response-sub">Please accept or decline within 48 hours. No payment has been collected yet.</div>
          <?php else: ?>
            <div class="sup-response-text">This booking requires your response</div>
            <div class="sup-response-sub">Accept or decline to update the booking status.</div>
          <?php endif; ?>
        </div>
      </div>
      <div class="sup-response-actions">
        <button type="button" class="booking-action sup-btn sup-btn--accept" data-action="accept">
          <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 8l3.5 3.5L13 5"/></svg>
          <?= $bookingStatus === 'pending_supplier_response' ? 'Accept Request' : 'Accept' ?>
        </button>
        <button type="button" class="booking-action sup-btn sup-btn--decline" data-action="decline">
          <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4l8 8M12 4l-8 8"/></svg>
          <?= $bookingStatus === 'pending_supplier_response' ? 'Decline Request' : 'Decline' ?>
        </button>
        <?php if ($bookingStatus !== 'pending_supplier_response'): ?>
        <button type="button" id="reschedule-btn" class="sup-btn sup-btn--ghost">
          <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="3" width="12" height="11" rx="1"/><path d="M5 2v2M11 2v2M2 7h12"/></svg>
          Propose reschedule
        </button>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>

    <?php
    // Cancellation review bar — shown when the supplier needs to approve/decline a cancellation request
    $needsCancellationReview = $bookingStatus === 'cancellation_requested' && $supplierStatus === 'cancellation_pending';
    $supplierApprovedCancellation = $supplierStatus === 'cancellation_approved';
    ?>
    <?php if ($needsCancellationReview): ?>
    <div class="sup-response-bar sup-cancellation-review">
      <div class="sup-response-info">
        <div class="sup-response-icon" style="color:var(--sup-amber)">
          <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 1.5L1 14h14L8 1.5z"/><path d="M8 6v3M8 11.5h.01"/></svg>
        </div>
        <div>
          <div class="sup-response-text">Cancellation request from <?= $h($customerName) ?></div>
          <div class="sup-response-sub">
            <?php if ($cancellationReason): ?>
              <strong>Reason:</strong> <?= $h($cancellationReason) ?>
            <?php else: ?>
              The customer has requested to cancel this booking.
            <?php endif; ?>
          </div>
          <div class="sup-response-sub" style="margin-top:4px;opacity:.7">Your response will be sent to the customer and admin for review.</div>
        </div>
      </div>
      <div class="sup-response-actions">
        <button type="button" class="sup-btn sup-btn--accept cancellation-review-btn" data-action="approve">
          <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 8l3.5 3.5L13 5"/></svg>
          Approve Cancellation
        </button>
        <button type="button" class="sup-btn sup-btn--decline cancellation-review-btn" data-action="decline">
          <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4l8 8M12 4l-8 8"/></svg>
          Decline Cancellation
        </button>
      </div>
    </div>
    <?php elseif ($supplierApprovedCancellation): ?>
    <div class="sup-response-bar" style="background:#f0fdf4;border-color:#bbf7d0">
      <div class="sup-response-info">
        <div class="sup-response-icon" style="color:#16a34a">
          <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="8" cy="8" r="6"/><path d="M5 8l2 2 4-4"/></svg>
        </div>
        <div>
          <div class="sup-response-text">You approved the cancellation</div>
          <div class="sup-response-sub">Admin will review and finalize the cancellation and refund process.</div>
        </div>
      </div>
    </div>
    <?php endif; ?>

  </header>

  <section class="sup-assignment" aria-labelledby="supplier-assignment-title">
    <div class="sup-assignment-head">
      <div>
        <p class="sup-assignment-kicker">Your assignment</p>
        <h2 class="sup-assignment-title" id="supplier-assignment-title"><?= $h($assignmentName) ?></h2>
        <?php if ($assignmentCategory !== ''): ?><div class="sup-assignment-category"><?= $h($assignmentCategory) ?></div><?php endif; ?>
      </div>
      <span class="sup-assignment-status"><?= $h($assignmentStatusLabel) ?></span>
    </div>
    <div class="sup-assignment-facts">
      <div class="sup-assignment-fact">
        <small>Date & time</small>
        <strong><?= $h($formatDate($assignmentDate)) ?></strong>
        <span><?= $h(trim($formatTime($assignmentStart) . ($assignmentEnd !== '' ? ' – ' . $formatTime($assignmentEnd) : '')) ?: 'Time not set') ?></span>
      </div>
      <div class="sup-assignment-fact">
        <small>Venue</small>
        <strong><?= $h($assignmentVenue !== '' ? $assignmentVenue : 'Not specified') ?></strong>
      </div>
      <div class="sup-assignment-fact">
        <small>Guests</small>
        <strong><?= $hasGuestData ? number_format($totalGuests) : 'Not specified' ?></strong>
      </div>
      <div class="sup-assignment-fact">
        <small>Event contact</small>
        <strong><?= $h($firstContactName !== '' ? $firstContactName : $customerName) ?></strong>
        <span><?= $h($firstContactPhone !== '' ? $firstContactPhone : ($customerPhone !== '' ? $customerPhone : 'No phone provided')) ?></span>
      </div>
    </div>
  </section>

  <?php if ($inReplacement && !$needsResponse): ?>
    <div class="sup-quiet-notice">
      <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M2 8a6 6 0 0110-4.5M14 8a6 6 0 01-10 4.5"/><path d="M12 2v3H9M4 14v-3h3"/></svg>
      <div>
        <?php if (!empty($acceptedReplacementRows)): ?>
          <strong>Your replacement service is confirmed.</strong> Other replacement services on this booking are still awaiting responses.
        <?php elseif (!empty($replacementRows)): ?>
          <strong>Your declined service is being reassigned.</strong> No further action is required from you.
        <?php else: ?>
          <strong>Your assignment is confirmed.</strong> Other replacement services on this booking are still being arranged.
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>

  <?php if (!empty($declinableRows) && !$needsResponse): ?>
  <details class="sup-secondary-action">
    <summary>Can’t fulfill this assignment?</summary>
    <div class="sup-selfdecline">
      <div class="sup-selfdecline-head">
        <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 1.5L1 14h14L8 1.5z"/><path d="M8 6v3M8 11.5h.01"/></svg>
        <div>
          <div class="sup-response-text">Decline a specific service</div>
          <div class="sup-response-sub">Admin will arrange another supplier. Available until <?= (int)$declineCutoffDays ?> days before the event.</div>
        </div>
      </div>
      <ul class="sup-selfdecline-list">
        <?php foreach ($declinableRows as $row): ?>
          <li class="sup-selfdecline-item">
            <div class="sup-selfdecline-svc">
              <strong><?= $h($row['service_name'] ?? 'Service') ?></strong>
              <?php if (!empty($row['category_name'])): ?><span><?= $h($row['category_name']) ?></span><?php endif; ?>
            </div>
            <button type="button" class="sup-btn sup-btn--decline selfdecline-btn"
                    data-bsid="<?= (int)$row['id'] ?>"
                    data-svc="<?= $h($row['service_name'] ?? 'this service') ?>">
              <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4l8 8M12 4l-8 8"/></svg>
              Can't fulfill
            </button>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </details>
  <?php endif; ?>

  <details class="sup-booking-details">
    <summary>View full booking details</summary>
    <div class="sup-booking-details-content">
  <div class="sup-runsheet-head">
    <div>
      <h2>Full event schedule</h2>
    </div>
    <p>Your service is highlighted.</p>
  </div>

  <div class="sup-runsheet-layout">
  <!-- ── Stats band ── -->
  <div class="sup-stats">
    <div class="sup-stat sup-stat--neutral">
      <div class="sup-stat-label">Event date</div>
      <div class="sup-stat-value <?= ($firstDate === '') ? 'sup-stat-value--sm' : '' ?>">
        <?= $h($formatDate($firstDate)) ?>
      </div>
      <div class="sup-stat-sub"><?= $h($eventTime ?: 'Time not set') ?></div>
    </div>
    <div class="sup-stat sup-stat--neutral">
      <div class="sup-stat-label">Guests</div>
      <div class="sup-stat-value"><?= $hasGuestData ? number_format($totalGuests) : '—' ?></div>
      <div class="sup-stat-sub"><?= $hasGuestData ? 'across ' . count($items) . ' service(s)' : 'Not specified' ?></div>
    </div>
    <div class="sup-stat sup-stat--neutral">
      <div class="sup-stat-label">Venue</div>
      <div class="sup-stat-value sup-stat-value--sm"><?= $h($firstLocation !== '' ? $firstLocation : '—') ?></div>
    </div>
    <div class="sup-stat sup-stat--primary">
      <div class="sup-stat-label">Your earnings</div>
      <div class="sup-stat-value"><?= $money($supplierTotal) ?></div>
      <div class="sup-stat-sub"><?= $money($supplierPaid) ?> paid · <?= $money($supplierRemaining) ?> remaining</div>
    </div>
  </div>

  <!-- ── Body: services + sidebar ── -->
  <div class="sup-body">
    <div class="sup-main">
      <!-- Services table -->
      <div class="sup-card">
        <div class="sup-card-head">
          <span class="sup-card-title">Services in this booking</span>
          <span class="sup-badge <?= $statusBadgeClass($bookingStatus) ?>"><?= $h($bookingStatusLabel) ?></span>
        </div>
        <?php if (empty($items)): ?>
          <div class="sup-empty">No supplier service lines are attached to this booking.</div>
        <?php else: ?>
          <div class="sup-table-wrap">
            <table class="sup-table">
              <thead>
                <tr>
                  <th>Service</th>
                  <th>Venue</th>
                  <th>Guests</th>
                  <th>Contact</th>
                  <th class="is-right">Price</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($items as $item):
                  $itemId = (int)($item['id'] ?? 0);
                  $d = $detailByItem[$itemId] ?? [];
                  $itemGuests  = (int)($d['guest_count'] ?? 0);
                  $itemContact = trim((string)($d['contact_name'] ?? ''));
                  $itemPhone   = trim((string)($d['contact_phone'] ?? ''));
                  $itemNotes   = trim((string)($d['special_requests'] ?? ''));
                  $venueText   = trim((string)($item['venue_room_name'] ?? ''));
                  $venueName   = trim((string)($item['venue_name'] ?? ''));
                  if ($venueName !== '') $venueText .= ($venueText !== '' ? ' · ' : '') . $venueName;
                  $isAddon = !empty($item['package_booking_item_id']);
                ?>
                <tr>
                  <td>
                    <div style="display:flex;align-items:flex-start;gap:10px">
                      <?php if (!empty($item['thumbnail_url'])): ?>
                        <img src="<?= $h($item['thumbnail_url']) ?>" alt="" class="sup-svc-thumb" loading="lazy">
                      <?php else: ?>
                        <div class="sup-svc-thumb-placeholder">
                          <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="3" width="12" height="10" rx="1"/><path d="M2 10l3-3 3 3 2-2 4 4"/></svg>
                        </div>
                      <?php endif; ?>
                      <div>
                        <div class="sup-svc-name"><?= $h($item['service_name'] ?? 'Service') ?></div>
                        <?php if ($isAddon && !empty($item['addon_package_name'])): ?>
                          <span class="sup-svc-addon">
                            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" style="width:10px;height:10px"><path d="M7 3v5l-3 3"/></svg>
                            Add-on for <?= $h($item['addon_package_name']) ?>
                          </span>
                        <?php endif; ?>
                        <div class="sup-svc-cat"><?= $h($item['category_name'] ?? $item['supplier_name'] ?? '') ?></div>
                        <?php if ($itemNotes !== ''): ?>
                          <div class="sup-svc-note"><?= $h($itemNotes) ?></div>
                        <?php endif; ?>
                      </div>
                    </div>
                  </td>
                  <td style="color:var(--sup-body);font-size:12px;white-space:nowrap"><?= $h($venueText !== '' ? $venueText : '—') ?></td>
                  <td style="font-size:13px;font-weight:700;white-space:nowrap"><?= $itemGuests > 0 ? number_format($itemGuests) : '—' ?></td>
                  <td>
                    <?php if ($itemContact !== ''): ?>
                      <div style="font-size:12px;font-weight:700;color:var(--sup-text)"><?= $h($itemContact) ?></div>
                      <?php if ($itemPhone !== ''): ?>
                        <div class="sup-svc-cat"><?= $h($itemPhone) ?></div>
                      <?php endif; ?>
                    <?php else: ?>
                      <span style="color:var(--sup-muted)">—</span>
                    <?php endif; ?>
                  </td>
                  <td class="is-right">
                    <div class="sup-price"><?= $money($item['price'] ?? 0) ?></div>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>

      <!-- Package event timelines -->
      <?php foreach ($items as $item): ?>
        <?php $schedule = $packageSchedules[(int)($item['id'] ?? 0)] ?? []; if (empty($schedule)) continue; ?>
        <?php
          $packageDetail = $detailByItem[(int)($item['id'] ?? 0)] ?? [];
          $scheduleGuests = (int)($packageDetail['guest_count'] ?? 0);
          $scheduleContact = trim((string)($packageDetail['contact_name'] ?? ''));
          $schedulePhone = trim((string)($packageDetail['contact_phone'] ?? ''));
          $scheduleRequests = trim((string)($packageDetail['special_requests'] ?? ''));
          $relatedTimeline = array_map(static function ($timelineEvent) use ($supplierId, $ownTimelineServiceIds, $scheduleGuests) {
              $timelineServiceId = (int)($timelineEvent['service_id'] ?? 0);
              return [
                  'key' => (string)($timelineEvent['package_item_id'] ?? $timelineServiceId),
                  'service' => $timelineEvent['service_name'] ?? 'Package service',
                  'category' => $timelineEvent['category_name'] ?? 'Service',
                  'supplier' => $timelineEvent['supplier_name'] ?? 'Golden Promise',
                  'guests' => $scheduleGuests > 0 ? number_format($scheduleGuests) : 'Not specified',
                  'start' => !empty($timelineEvent['start_time']) ? date('g:i A', strtotime($timelineEvent['start_time'])) : 'TBD',
                  'end' => !empty($timelineEvent['end_time']) ? date('g:i A', strtotime($timelineEvent['end_time'])) : 'TBD',
                  'venue' => $timelineEvent['venue_room_name'] ?? 'Not specified',
                  'isMine' => (int)($timelineEvent['supplier_id'] ?? 0) === $supplierId
                      || in_array($timelineServiceId, $ownTimelineServiceIds, true),
              ];
          }, $schedule);
        ?>
        <div class="sup-card">
          <div class="sup-card-head">
            <span class="sup-card-title"><?= $h($item['service_name'] ?? 'Package') ?> — service schedule</span>
            <span class="sup-badge sup-badge--neutral">Your services are highlighted</span>
          </div>
          <div class="sup-table-wrap">
            <table class="sup-table">
              <thead>
                <tr>
                  <th>Service</th>
                  <th>Supplier</th>
                  <th>Guests</th>
                  <th>Start</th>
                  <th>End</th>
                  <th>Hall / Room</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($schedule as $event): ?>
                <?php
                  $isOwnTimelineService = (int)($event['supplier_id'] ?? 0) === $supplierId
                      || in_array((int)($event['service_id'] ?? 0), $ownTimelineServiceIds, true);
                  $serviceDetailPayload = [
                      'title' => $event['service_name'] ?? 'Service detail',
                      'category' => $event['category_name'] ?? 'Service',
                      'supplier' => $event['supplier_name'] ?? 'Golden Promise',
                      'guests' => $scheduleGuests > 0 ? number_format($scheduleGuests) : 'Not specified',
                      'date' => $formatDate($event['event_date'] ?? $firstDate),
                      'time' => (!empty($event['start_time']) ? date('g:i A', strtotime($event['start_time'])) : 'TBD')
                          . ' – '
                          . (!empty($event['end_time']) ? date('g:i A', strtotime($event['end_time'])) : 'TBD'),
                      'venue' => $event['venue_room_name'] ?? ($firstLocation !== '' ? $firstLocation : 'Not specified'),
                      'contact' => $scheduleContact !== '' ? $scheduleContact : $customerName,
                      'phone' => $schedulePhone !== '' ? $schedulePhone : ($customerPhone !== '' ? $customerPhone : 'Not provided'),
                      'requests' => $scheduleRequests !== '' ? $scheduleRequests : 'No special requests',
                      'currentKey' => (string)($event['package_item_id'] ?? ($event['service_id'] ?? '')),
                      'timelineTitle' => $item['service_name'] ?? 'Event service timeline',
                      'timeline' => $relatedTimeline,
                  ];
                ?>
                <tr class="sup-timeline-row <?= $isOwnTimelineService ? 'is-mine is-clickable' : '' ?>"
                    <?= $isOwnTimelineService ? 'tabindex="0" role="button" aria-label="View your service detail"' : '' ?>
                    <?php if ($isOwnTimelineService): ?>
                    data-service-detail="<?= $h(json_encode($serviceDetailPayload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) ?>"
                    <?php endif; ?>>
                  <td>
                    <div class="sup-svc-name"><?= $h($event['service_name'] ?? 'Package service') ?></div>
                    <div class="sup-svc-cat"><?= $h($event['category_name'] ?? 'Service') ?></div>
                    <?php if ($isOwnTimelineService): ?>
                      <span class="sup-own-service">Your service</span>
                    <?php endif; ?>
                  </td>
                  <td style="color:var(--sup-body);font-size:12px"><?= $h($event['supplier_name'] ?? 'Golden Promise') ?></td>
                  <td><span class="sup-timeline-guest"><?= $scheduleGuests > 0 ? number_format($scheduleGuests) : '—' ?></span></td>
                  <td style="font-weight:700"><?= $h(!empty($event['start_time']) ? date('g:i A', strtotime($event['start_time'])) : 'TBD') ?></td>
                  <td style="font-weight:700"><?= $h(!empty($event['end_time']) ? date('g:i A', strtotime($event['end_time'])) : '—') ?></td>
                  <td style="color:var(--sup-muted);font-size:12px">
                    <?= $h($event['venue_room_name'] ?? '—') ?>
                    <?php if ($isOwnTimelineService): ?><span class="sup-service-more">View detail →</span><?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      <?php endforeach; ?>
      <?php if (!$hasPackageSchedule): ?>
        <div class="sup-card">
          <div class="sup-card-head"><span class="sup-card-title">Service schedule</span></div>
          <div class="sup-empty">No detailed service schedule has been added for this booking yet.</div>
        </div>
      <?php endif; ?>
    </div>

    <!-- ── Sidebar ── -->
    <aside class="sup-side">
      <div class="sup-card">
        <div class="sup-card-body">
          <!-- On-site contact -->
          <?php if ($firstContactName !== ''): ?>
          <div style="margin-bottom:16px">
            <div class="sup-side-head">
              <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="8" cy="5" r="3"/><path d="M2 14a6 6 0 0112 0"/><path d="M11 8l1.5 1.5L15 7"/></svg>
              On-site contact
            </div>
            <div class="sup-contact-name"><?= $h($firstContactName) ?></div>
            <?php if ($firstContactPhone !== ''): ?>
              <div class="sup-contact-row">
                <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 2h2.5l1 3-1.5 1a8 8 0 003 3l1-1.5 3 1V12a1 1 0 01-1 1C6 13 2 8.5 2 4a1 1 0 011-1z"/></svg>
                <?= $h($firstContactPhone) ?>
              </div>
            <?php endif; ?>
          </div>
          <?php endif; ?>

          <!-- Customer info -->
          <div style="margin-bottom:16px">
            <div class="sup-side-head">
              <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="8" cy="5" r="3"/><path d="M2 14a6 6 0 0112 0"/></svg>
              Customer
            </div>
            <div class="sup-contact-name"><?= $h($customerName) ?></div>
            <div class="sup-contact-row"><?= $h($customerEmail) ?></div>
            <div class="sup-contact-row"><?= $h($customerPhone) ?></div>
          </div>

          <!-- Other suppliers -->
          <div style="margin-bottom:<?= empty($allSpecialRequests) ? '0' : '16px' ?>">
            <div class="sup-side-head">
              <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="5.5" cy="5" r="2.5"/><circle cx="10.5" cy="5" r="2.5"/><path d="M1 14a4.5 4.5 0 019 0M6 14a4.5 4.5 0 019 0"/></svg>
              Other suppliers
            </div>
            <?php if (empty($otherSuppliers)): ?>
              <p style="font-size:11.5px;color:var(--sup-muted)">No other suppliers on this booking.</p>
            <?php else: ?>
              <?php foreach ($otherSuppliers as $os):
                $osStatus = strtolower((string)($os['status'] ?? 'pending'));
              ?>
                <div class="sup-sup-row">
                  <div class="sup-sup-info">
                    <?php if (!empty($os['thumbnail_url'])): ?>
                      <img src="<?= $h($os['thumbnail_url']) ?>" alt="" class="sup-sup-thumb" loading="lazy">
                    <?php else: ?>
                      <div class="sup-sup-initial"><?= strtoupper(substr((string)($os['shop_name'] ?? 'S'), 0, 1)) ?></div>
                    <?php endif; ?>
                    <span class="sup-sup-name"><?= $h($os['shop_name'] ?? 'Supplier') ?></span>
                  </div>
                  <span class="sup-badge <?= $statusBadgeClass($osStatus) ?>"><?= $h(ucfirst($osStatus)) ?></span>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>

          <!-- Special requests -->
          <?php if (!empty($allSpecialRequests)): ?>
          <div>
            <div class="sup-side-head">
              <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H2a1 1 0 00-1 1v8a1 1 0 001 1h5l3 3v-3h4a1 1 0 001-1V3a1 1 0 00-1-1z"/></svg>
              Special requests
            </div>
            <?php foreach ($allSpecialRequests as $sr): ?>
              <div class="sup-sr-item"><?= $h($sr) ?></div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </aside>
  </div>

  <div class="sup-card sup-payment-card">
    <div class="sup-card-head">
      <span class="sup-card-title">Payment and earnings</span>
      <span class="sup-badge <?= $statusBadgeClass($paymentStatus) ?>"><?= $h(ucfirst($paymentStatus ?: 'pending')) ?></span>
    </div>
    <div class="sup-payment-grid">
      <div class="sup-payment-fact"><small>Your total earnings</small><strong><?= $money($supplierTotal) ?></strong></div>
      <div class="sup-payment-fact"><small>Paid to date</small><strong><?= $money($supplierPaid) ?></strong></div>
      <div class="sup-payment-fact"><small>Remaining</small><strong><?= $money($supplierRemaining) ?></strong></div>
    </div>
    <div class="sup-payment-track" aria-label="Payment progress">
      <div class="sup-payment-fill" style="width:<?= max(0, min(100, $paidFraction * 100)) ?>%"></div>
    </div>
  </div>
  </div>
    </div>
  </details>

  <div class="sup-service-drawer-backdrop" data-service-drawer-close></div>
  <aside class="sup-service-drawer" id="supplier-service-detail-drawer" aria-hidden="true" aria-labelledby="supplier-service-drawer-title">
    <div class="sup-service-drawer-head">
      <div>
        <p class="sup-service-drawer-kicker">Your assigned service</p>
        <h2 class="sup-service-drawer-title" id="supplier-service-drawer-title">Service detail</h2>
      </div>
      <button type="button" class="sup-service-drawer-close" data-service-drawer-close aria-label="Close service detail">
        <i data-lucide="x"></i>
      </button>
    </div>
    <dl class="sup-service-detail-list">
      <div class="sup-service-detail"><dt>Category</dt><dd data-service-field="category">—</dd></div>
      <div class="sup-service-detail"><dt>Supplier</dt><dd data-service-field="supplier">—</dd></div>
      <div class="sup-service-detail"><dt>Guests</dt><dd data-service-field="guests">—</dd></div>
      <div class="sup-service-detail"><dt>Event date</dt><dd data-service-field="date">—</dd></div>
      <div class="sup-service-detail"><dt>Service time</dt><dd data-service-field="time">—</dd></div>
      <div class="sup-service-detail"><dt>Hall / room</dt><dd data-service-field="venue">—</dd></div>
      <div class="sup-service-detail"><dt>On-site contact</dt><dd data-service-field="contact">—</dd></div>
      <div class="sup-service-detail"><dt>Phone</dt><dd data-service-field="phone">—</dd></div>
      <div class="sup-service-detail"><dt>Special requests</dt><dd data-service-field="requests">—</dd></div>
    </dl>
    <section class="sup-drawer-timeline" aria-labelledby="supplier-service-timeline-title">
      <div class="sup-drawer-timeline-head">
        <span>Related event timeline</span>
        <h3 id="supplier-service-timeline-title">All package services</h3>
      </div>
      <div class="sup-drawer-timeline-list" data-service-timeline></div>
    </section>
  </aside>

  <!-- ── Reschedule Modal ── -->
  <div id="reschedule-modal" class="sup-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="reschedule-modal-title">
    <div class="sup-modal">
      <div class="sup-modal-head">
        <h2 id="reschedule-modal-title" class="sup-modal-title">Propose reschedule</h2>
        <button type="button" class="reschedule-close sup-modal-close" aria-label="Close">
          <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 4l8 8M12 4l-8 8"/></svg>
        </button>
      </div>
      <form id="reschedule-form">
        <div class="sup-modal-body">
          <div class="sup-field">
            <label class="sup-label" for="rs-date">Proposed date</label>
            <input type="date" id="rs-date" name="proposed_date" required class="sup-input">
          </div>
          <div class="sup-field sup-field-row">
            <div>
              <label class="sup-label" for="rs-start">Start time</label>
              <input type="time" id="rs-start" name="proposed_start_time" required class="sup-input">
            </div>
            <div>
              <label class="sup-label" for="rs-end">End time</label>
              <input type="time" id="rs-end" name="proposed_end_time" required class="sup-input">
            </div>
          </div>
          <div class="sup-field" style="margin-bottom:0">
            <label class="sup-label" for="rs-reason">Reason</label>
            <textarea id="rs-reason" name="reason" rows="3" placeholder="e.g. Equipment already booked on that date" class="sup-textarea"></textarea>
          </div>
        </div>
        <div class="sup-modal-foot">
          <button type="button" class="reschedule-close sup-btn sup-btn--decline">Cancel</button>
          <button type="submit" class="sup-btn sup-btn--accept">Send proposal</button>
        </div>
      </form>
    </div>
  </div>

  <!-- ── Decline (self-decline → replacement) Modal ── -->
  <div id="decline-modal" class="sup-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="decline-modal-title">
    <div class="sup-modal">
      <div class="sup-modal-head">
        <h2 id="decline-modal-title" class="sup-modal-title">Can’t fulfill this service</h2>
        <button type="button" class="decline-close sup-modal-close" aria-label="Close">
          <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 4l8 8M12 4l-8 8"/></svg>
        </button>
      </div>
      <form id="decline-form">
        <div class="sup-modal-body">
          <p class="sup-response-sub" style="margin-bottom:14px">
            The customer keeps the booking. Admin will assign another supplier to this service.
          </p>
          <div class="sup-field" style="margin-bottom:0">
            <label class="sup-label" for="dec-reason">Reason <span style="color:var(--sup-danger-text)">*</span></label>
            <textarea id="dec-reason" name="reason" rows="3" required placeholder="e.g. Already booked another wedding on this date" class="sup-textarea"></textarea>
          </div>
        </div>
        <div class="sup-modal-foot">
          <button type="button" class="decline-close sup-btn sup-btn--ghost">Cancel</button>
          <button type="submit" class="sup-btn sup-btn--decline">Confirm decline</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Cancellation decline modal -->
  <div id="cancel-decline-modal" class="sup-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="cancel-decline-modal-title">
    <div class="sup-modal">
      <div class="sup-modal-head">
        <h2 id="cancel-decline-modal-title" class="sup-modal-title">Decline Cancellation Request</h2>
        <button type="button" class="cancel-decline-close sup-modal-close" aria-label="Close">
          <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 4l8 8M12 4l-8 8"/></svg>
        </button>
      </div>
      <form id="cancel-decline-form">
        <div class="sup-modal-body">
          <p class="sup-response-sub" style="margin-bottom:14px">
            The booking will remain active. The customer and admin will be notified of your decision.
          </p>
          <div class="sup-field" style="margin-bottom:0">
            <label class="sup-label" for="cancel-dec-reason">Reason for declining <span style="color:var(--sup-danger-text)">*</span></label>
            <textarea id="cancel-dec-reason" name="reason" rows="3" required placeholder="e.g. Work already started, materials purchased" class="sup-textarea"></textarea>
          </div>
        </div>
        <div class="sup-modal-foot">
          <button type="button" class="cancel-decline-close sup-btn sup-btn--ghost">Cancel</button>
          <button type="submit" class="sup-btn sup-btn--decline">Submit decline</button>
        </div>
      </form>
    </div>
  </div>

</div>

<script>
/* ── Supplier service detail drawer ── */
(function(){
  var drawer = document.getElementById('supplier-service-detail-drawer');
  var lastTrigger = null;

  function closeDrawer() {
    document.body.classList.remove('sup-service-drawer-open');
    if (drawer) drawer.setAttribute('aria-hidden', 'true');
    if (lastTrigger) lastTrigger.focus();
  }

  function openDrawer(row) {
    if (!drawer) return;
    var detail = {};
    try { detail = JSON.parse(row.dataset.serviceDetail || '{}'); } catch (error) {}
    lastTrigger = row;
    document.getElementById('supplier-service-drawer-title').textContent = detail.title || 'Service detail';
    drawer.querySelectorAll('[data-service-field]').forEach(function(node){
      node.textContent = detail[node.dataset.serviceField] || '—';
    });
    var timelineTitle = document.getElementById('supplier-service-timeline-title');
    var timelineList = drawer.querySelector('[data-service-timeline]');
    if (timelineTitle) timelineTitle.textContent = detail.timelineTitle || 'All package services';
    if (timelineList) {
      timelineList.innerHTML = '';
      var timeline = Array.isArray(detail.timeline) ? detail.timeline : [];
      timeline.forEach(function(event){
        var item = document.createElement('article');
        var isCurrent = String(event.key || '') === String(detail.currentKey || '');
        item.className = 'sup-drawer-timeline-item'
          + (event.isMine ? ' is-mine' : '')
          + (isCurrent ? ' is-current' : '');

        var time = document.createElement('span');
        time.className = 'sup-drawer-time';
        time.textContent = event.start || 'TBD';

        var dot = document.createElement('span');
        dot.className = 'sup-drawer-dot';

        var copy = document.createElement('div');
        copy.className = 'sup-drawer-timeline-copy';
        var service = document.createElement('strong');
        service.textContent = event.service || 'Service';
        var meta = document.createElement('small');
        meta.textContent = [
          event.category || 'Service',
          event.supplier || 'Golden Promise',
          event.guests && event.guests !== 'Not specified' ? event.guests + ' guests' : 'Guests not specified',
          (event.start || 'TBD') + ' – ' + (event.end || 'TBD'),
          event.venue || ''
        ].filter(Boolean).join(' · ');
        copy.appendChild(service);
        copy.appendChild(meta);
        if (isCurrent || event.isMine) {
          var label = document.createElement('span');
          label.className = 'sup-drawer-timeline-label';
          label.textContent = isCurrent ? 'Selected service' : 'Your service';
          copy.appendChild(label);
        }
        item.appendChild(time);
        item.appendChild(dot);
        item.appendChild(copy);
        timelineList.appendChild(item);
      });
      if (!timeline.length) {
        var empty = document.createElement('p');
        empty.className = 'sup-empty';
        empty.textContent = 'No related service timeline is available.';
        timelineList.appendChild(empty);
      }
    }
    document.body.classList.add('sup-service-drawer-open');
    drawer.setAttribute('aria-hidden', 'false');
    drawer.querySelector('[data-service-drawer-close]')?.focus();
  }

  document.querySelectorAll('[data-service-detail]').forEach(function(row){
    row.addEventListener('click', function(){ openDrawer(row); });
    row.addEventListener('keydown', function(event){
      if (event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        openDrawer(row);
      }
    });
  });
  document.querySelectorAll('[data-service-drawer-close]').forEach(function(button){
    button.addEventListener('click', closeDrawer);
  });
  document.addEventListener('keydown', function(event){
    if (event.key === 'Escape' && document.body.classList.contains('sup-service-drawer-open')) closeDrawer();
  });
})();

/* ── Reschedule modal ── */
(function(){
  var modal = document.getElementById('reschedule-modal');
  var btn   = document.getElementById('reschedule-btn');
  var form  = document.getElementById('reschedule-form');
  var closeBtns = document.querySelectorAll('.reschedule-close');

  if (btn && modal) {
    btn.addEventListener('click', function(){ modal.classList.add('is-open'); });
    closeBtns.forEach(function(b){
      b.addEventListener('click', function(){ modal.classList.remove('is-open'); });
    });
    modal.addEventListener('click', function(e){ if (e.target === modal) modal.classList.remove('is-open'); });

    form.addEventListener('submit', async function(e){
      e.preventDefault();
      var submitBtn = form.querySelector('button[type="submit"]');
      var original = submitBtn.innerHTML;
      submitBtn.disabled = true;
      submitBtn.innerHTML = 'Sending…';

      var formData = new FormData(form);
      formData.append('booking_id', '<?= (int)($booking['id'] ?? 0) ?>');
      formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]')?.content || '');

      try {
        var resp = await fetch('<?= URLROOT ?>/supplier/proposeReschedule', { method: 'POST', body: formData });
        var data = await resp.json().catch(function(){ return {}; });
        if (data.success) {
          alert(data.message);
          modal.classList.remove('is-open');
          form.reset();
          window.location.reload();
        } else {
          alert(data.error || 'Could not send reschedule proposal.');
        }
      } catch (err) {
        alert('Network error. Please try again.');
      }
      submitBtn.disabled = false;
      submitBtn.innerHTML = original;
    });
  }

  /* ── Accept / Decline ── */
  document.querySelectorAll('.booking-action').forEach(function(button){
    button.addEventListener('click', async function(){
      button.disabled = true;
      var original = button.innerHTML;
      button.innerHTML = 'Updating…';

      var formData = new FormData();
      formData.append('booking_id', '<?= (int)($booking['id'] ?? 0) ?>');
      formData.append('action', button.dataset.action);
      formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]')?.content || '');

      try {
        var resp = await fetch('<?= URLROOT ?>/supplier/bookingRespond', { method: 'POST', body: formData });
        var data = await resp.json().catch(function(){ return {}; });
        if (data.success) { window.location.reload(); return; }
        alert(data.error || 'Could not update booking.');
      } catch (err) {
        alert('Network error. Please try again.');
      }
      button.disabled = false;
      button.innerHTML = original;
    });
  });

  /* ── Self-decline per service (confirmed package → replacement) ── */
  var declineModal = document.getElementById('decline-modal');
  var declineBtns  = document.querySelectorAll('.selfdecline-btn');
  var declineForm  = document.getElementById('decline-form');
  var declineTitle = document.getElementById('decline-modal-title');
  var declineTarget = null;

  if (declineModal && declineForm && declineBtns.length) {
    declineBtns.forEach(function(btn){
      btn.addEventListener('click', function(){
        declineTarget = btn.dataset.bsid || '';
        if (declineTitle) declineTitle.textContent = 'Decline “' + (btn.dataset.svc || 'this service') + '”';
        document.getElementById('dec-reason').value = '';
        declineModal.classList.add('is-open');
      });
    });
    document.querySelectorAll('.decline-close').forEach(function(b){
      b.addEventListener('click', function(){ declineModal.classList.remove('is-open'); });
    });
    declineModal.addEventListener('click', function(e){ if (e.target === declineModal) declineModal.classList.remove('is-open'); });

    declineForm.addEventListener('submit', async function(e){
      e.preventDefault();
      var reason = (document.getElementById('dec-reason').value || '').trim();
      if (!reason) { alert('Please enter a reason.'); return; }
      if (!declineTarget) { alert('No service selected.'); return; }

      var submitBtn = declineForm.querySelector('button[type="submit"]');
      var original = submitBtn.innerHTML;
      submitBtn.disabled = true;
      submitBtn.innerHTML = 'Submitting…';

      var formData = new FormData();
      formData.append('booking_id', '<?= (int)($booking['id'] ?? 0) ?>');
      formData.append('booking_supplier_id', declineTarget);
      formData.append('action', 'decline');
      formData.append('reason', reason);
      formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]')?.content || '');

      try {
        var resp = await fetch('<?= URLROOT ?>/supplier/bookingRespond', { method: 'POST', body: formData });
        var data = await resp.json().catch(function(){ return {}; });
        if (data.success) { window.location.reload(); return; }
        alert(data.error || 'Could not submit your decline.');
      } catch (err) {
        alert('Network error. Please try again.');
      }
      submitBtn.disabled = false;
      submitBtn.innerHTML = original;
    });
  }

  /* ── Cancellation review (approve / decline) ── */
  var cancelDeclineModal = document.getElementById('cancel-decline-modal');
  var cancelDeclineForm = document.getElementById('cancel-decline-form');
  var cancellationBtns = document.querySelectorAll('.cancellation-review-btn');

  if (cancellationBtns.length) {
    cancellationBtns.forEach(function(btn) {
      btn.addEventListener('click', function() {
        var action = btn.dataset.action;
        if (action === 'decline') {
          // Open modal to get reason
          document.getElementById('cancel-dec-reason').value = '';
          if (cancelDeclineModal) cancelDeclineModal.classList.add('is-open');
        } else {
          // Approve directly
          if (!confirm('Are you sure you want to approve this cancellation? Admin will finalize the refund.')) return;
          submitCancellationReview('approve', '');
        }
      });
    });

    // Close modal handlers
    if (cancelDeclineModal) {
      document.querySelectorAll('.cancel-decline-close').forEach(function(b) {
        b.addEventListener('click', function() { cancelDeclineModal.classList.remove('is-open'); });
      });
      cancelDeclineModal.addEventListener('click', function(e) { if (e.target === cancelDeclineModal) cancelDeclineModal.classList.remove('is-open'); });
    }

    // Decline form submit
    if (cancelDeclineForm) {
      cancelDeclineForm.addEventListener('submit', function(e) {
        e.preventDefault();
        var reason = (document.getElementById('cancel-dec-reason').value || '').trim();
        if (!reason) { alert('Please enter a reason.'); return; }
        submitCancellationReview('decline', reason);
        if (cancelDeclineModal) cancelDeclineModal.classList.remove('is-open');
      });
    }
  }

  async function submitCancellationReview(action, reason) {
    var formData = new FormData();
    formData.append('booking_id', '<?= (int)($booking['id'] ?? 0) ?>');
    formData.append('action', action);
    formData.append('reason', reason);
    formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]')?.content || '');

    try {
      var resp = await fetch('<?= URLROOT ?>/supplier/bookingCancellationRespond', { method: 'POST', body: formData });
      var data = await resp.json().catch(function(){ return {}; });
      if (data.success) { window.location.reload(); return; }
      alert(data.error || 'Could not process your response.');
    } catch (err) {
      alert('Network error. Please try again.');
    }
  }
})();
</script>
<?php
};
?>
<!DOCTYPE html>
<html lang="en">
<head><?php require_once APPROOT . '/views/dashboardLayout/head.php'; ?></head>
<body class="grid h-screen gap-0 bg-app-page" style="grid-template-columns: 280px 1fr;">
  <?php require APPROOT . '/views/dashboardLayout/suppliersidebar.php'; ?>
</body>
</html>
