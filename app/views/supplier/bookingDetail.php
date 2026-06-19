<?php
$booking = $booking ?? [];
$items = $items ?? [];
$eventDetails = $eventDetails ?? [];
$suppliers = $suppliers ?? [];
$bookingRef = $bookingRef ?? '';
$supplierStatus = strtolower($supplierStatus ?? 'pending');
$supplierId = (int)($supplierId ?? 0);
$depositPercent = $depositPercent ?? 30;
$packageSchedules = $packageSchedules ?? [];

$money = fn($v) => 'RM ' . number_format((float)$v, 0);
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
    $paymentStatus, $daysUntil, $otherSuppliers, $bookingStatus,
    $customerName, $customerEmail, $customerPhone, $customerInitial,
    $packageSchedules
) {
    $eventTime = trim($formatTime($firstStart) . ($firstEnd ? ' – ' . $formatTime($firstEnd) : ''), ' –');
    $needsResponse = $supplierStatus === 'pending';
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
          <span class="sup-badge <?= $statusBadgeClass($bookingStatus) ?>">Booking <?= $h(ucfirst($bookingStatus ?: 'pending')) ?></span>
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
  </header>

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
          <span class="sup-badge <?= $statusBadgeClass($bookingStatus) ?>"><?= $h(ucfirst($bookingStatus ?: 'pending')) ?></span>
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
        <div class="sup-card">
          <div class="sup-card-head">
            <span class="sup-card-title"><?= $h($item['service_name'] ?? 'Package') ?> — service schedule</span>
          </div>
          <div class="sup-table-wrap">
            <table class="sup-table">
              <thead>
                <tr>
                  <th>Service</th>
                  <th>Supplier</th>
                  <th>Start</th>
                  <th>End</th>
                  <th>Hall / Room</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($schedule as $event): ?>
                <tr>
                  <td>
                    <div class="sup-svc-name"><?= $h($event['service_name'] ?? 'Package service') ?></div>
                    <div class="sup-svc-cat"><?= $h($event['category_name'] ?? 'Service') ?></div>
                  </td>
                  <td style="color:var(--sup-body);font-size:12px"><?= $h($event['supplier_name'] ?? 'Golden Promise') ?></td>
                  <td style="font-weight:700"><?= $h(!empty($event['start_time']) ? date('g:i A', strtotime($event['start_time'])) : 'TBD') ?></td>
                  <td style="font-weight:700"><?= $h(!empty($event['end_time']) ? date('g:i A', strtotime($event['end_time'])) : '—') ?></td>
                  <td style="color:var(--sup-muted);font-size:12px"><?= $h($event['venue_room_name'] ?? '—') ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      <?php endforeach; ?>
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

</div>

<script>
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
