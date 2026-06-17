<?php
$booking = $booking ?? [];
$items = $items ?? [];
$eventDetails = $eventDetails ?? [];
$suppliers = $suppliers ?? [];
$bookingRef = $bookingRef ?? '';
$supplierStatus = strtolower($supplierStatus ?? 'pending');
$supplierId = (int)($supplierId ?? 0);
$depositPercent = $depositPercent ?? 30;

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

/* ── Match event_details to booking items by booking_item_id ── */
$detailByItem = [];
foreach ($eventDetails as $detail) {
    $key = (int)($detail['booking_item_id'] ?? 0);
    if ($key > 0) $detailByItem[$key] = $detail;
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
        return 'bg-app-success/10 text-app-success';
    if (in_array($s, ['pending', 'unpaid', 'partial'], true))
        return 'bg-app-warning/10 text-app-warning';
    if (in_array($s, ['rejected', 'cancelled', 'canceled', 'failed', 'cancelled_by_customer', 'supplier_rejected'], true))
        return 'bg-app-danger/10 text-app-danger';
    return 'bg-app-soft text-app-secondary';
};
$statusIcon = function (string $status): string {
    $s = strtolower($status);
    if (in_array($s, ['confirmed', 'completed', 'accepted', 'paid', 'success', 'supplier_confirmed'], true)) return 'check-circle';
    if (in_array($s, ['rejected', 'cancelled', 'canceled', 'failed', 'supplier_rejected'], true)) return 'x-circle';
    return 'clock';
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
    $statusBadgeClass, $statusIcon,
    $totalGuests, $hasGuestData, $firstDate, $firstStart, $firstEnd,
    $firstLocation, $firstContactName, $firstContactPhone,
    $allSpecialRequests, $supplierTotal, $supplierPaid, $supplierRemaining,
    $paymentStatus, $daysUntil, $otherSuppliers, $bookingStatus,
    $customerName, $customerEmail, $customerPhone, $customerInitial
) {
    $eventTime = trim($formatTime($firstStart) . ($firstEnd ? ' – ' . $formatTime($firstEnd) : ''), ' –');
?>
<style>
  /* ── Scoped editorial styles using project theme ── */
  .ed-page {
    --color-app-text: #111827;
    --color-app-secondary: #7b5c69;
    --color-app-muted: #b79c8b;
    --color-app-border: #ead8c7;
    --color-app-panel-border: #eddecc;
    --color-app-input: #ffffff;
    --color-app-soft: #faf5ef;
    --color-app-primary: #6d4c5b;
    --color-app-success: #16a34a;
    --color-app-warning: #eab308;
    --color-app-danger: #b94b4b;
    container: ed-detail / inline-size;
    width: 100%;
    max-width: 1600px;
    min-width: 0;
    margin: 0 auto;
    overflow-x: hidden;
    display: flex;
    flex-direction: column;
    gap: 14px;
    font-family: 'Poppins', system-ui, sans-serif;
    font-size: 13px;
    color: var(--color-app-text);
    line-height: 1.5;
    -webkit-font-smoothing: antialiased;
  }
  .supplier-main { min-width: 0; }

  .ed-rule-t { border-top: 1px solid var(--color-app-border); }
  .ed-rule-b { border-bottom: 1px solid var(--color-app-border); }
  .ed-rule-r { border-right: 1px solid var(--color-app-border); }

  /* ─── Eyebrow ─── */
  .ed-eyebrow {
    font-size: 9.5px;
    font-weight: 600;
    letter-spacing: .14em;
    text-transform: uppercase;
    color: var(--color-app-muted);
  }

  /* ─── Badges ─── */
  .ed-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 10px;
    font-weight: 600;
    letter-spacing: .06em;
    text-transform: uppercase;
    padding: 3px 10px;
    border-radius: 999px;
  }
  .ed-badge::before {
    content: '';
    display: inline-block;
    width: 5px;
    height: 5px;
    border-radius: 50%;
    background: currentColor;
    flex-shrink: 0;
  }
  .ed-badge--success { background: rgba(22, 163, 74, .1); color: var(--color-app-success, #16a34a); }
  .ed-badge--warning { background: rgba(234, 179, 8, .12); color: var(--color-app-warning, #eab308); }
  .ed-badge--danger  { background: rgba(185, 75, 75, .1);  color: var(--color-app-danger, #b94b4b); }
  .ed-badge--neutral { background: var(--color-app-soft, #faf5ef); color: var(--color-app-secondary, #7b5c69); }

  /* ─── Countdown ─── */
  .ed-countdown {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 10px;
    font-weight: 600;
    letter-spacing: .04em;
    padding: 3px 10px;
    border-radius: 999px;
  }

  /* ─── Header ─── */
  .ed-header {
    background: var(--color-app-input, #fff);
    padding: 22px 24px 0;
    border: 1px solid var(--color-app-border);
    border-radius: 1rem;
    box-shadow: 0 1px 2px rgba(34, 24, 19, .06);
    overflow: hidden;
  }
  .ed-header-top {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-start;
    justify-content: space-between;
    gap: 20px;
    padding-bottom: 20px;
  }
  .ed-header-top > div { min-width: 0; }
  .ed-back {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 11px;
    font-weight: 500;
    color: var(--color-app-muted);
    text-decoration: none;
    letter-spacing: .03em;
    margin-bottom: 14px;
    transition: color .12s;
  }
  .ed-back:hover { color: var(--color-app-text); }
  .ed-back svg { width: 12px; height: 12px; }

  .ed-title-ref {
    font-size: 28px;
    font-weight: 700;
    line-height: 1;
    color: var(--color-app-text);
    letter-spacing: -.01em;
    overflow-wrap: anywhere;
  }
  .ed-title-ref em {
    font-style: normal;
    color: var(--color-app-muted);
    font-weight: 400;
  }
  .ed-title-meta {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px;
    margin-top: 12px;
  }

  .ed-cust-strip {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
  }
  .ed-cust-avatar {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: var(--color-app-soft);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 600;
    color: var(--color-app-secondary);
    flex-shrink: 0;
  }
  .ed-cust-name {
    font-size: 13px;
    font-weight: 600;
    color: var(--color-app-text);
  }
  .ed-cust-sub {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-top: 2px;
  }
  .ed-cust-item {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 11px;
    color: var(--color-app-muted);
    min-width: 0;
    overflow-wrap: anywhere;
  }
  .ed-cust-item svg { width: 11px; height: 11px; flex-shrink: 0; }

  /* ─── Action bar ─── */
  .ed-action-bar {
    background: #fffbeb;
    border-top: 1px solid #fde68a;
    padding: 12px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
  }
  .ed-action-bar--flush {
    margin: 0 -24px;
    padding-left: 24px;
    padding-right: 24px;
  }
  .ed-action-bar-copy {
    display: flex;
    align-items: center;
    gap: 10px;
  }
  .ed-action-bar-icon {
    width: 30px;
    height: 30px;
    border-radius: 0.5rem;
    background: #fef3c7;
    border: 1px solid #fde68a;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--color-app-warning);
    flex-shrink: 0;
  }
  .ed-action-bar-icon svg { width: 14px; height: 14px; }
  .ed-action-bar-title {
    font-size: 12px;
    font-weight: 600;
    color: #92400e;
  }
  .ed-action-bar-desc {
    font-size: 11px;
    color: #b45309;
    margin-top: 1px;
  }
  .ed-action-btns {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
  }

  /* ─── Buttons ─── */
  .ed-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-family: 'Poppins', system-ui, sans-serif;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: .04em;
    text-transform: uppercase;
    padding: 8px 16px;
    border-radius: 0.5rem;
    border: none;
    cursor: pointer;
    transition: opacity .12s, transform .1s;
  }
  .ed-btn:hover { opacity: .88; transform: translateY(-1px); }
  .ed-btn:disabled { cursor: wait; opacity: .55; transform: none; }
  .ed-btn svg { width: 12px; height: 12px; flex-shrink: 0; }

  .ed-btn--accept  { background: var(--color-app-success); color: #fff; }
  .ed-btn--decline { background: var(--color-app-input); color: var(--color-app-text); border: 1px solid var(--color-app-border); }
  .ed-btn--ghost   { background: var(--color-app-input); color: var(--color-app-muted); border: 1px solid var(--color-app-border); }

  /* ─── Stats band ─── */
  .ed-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
  }
  .ed-stat {
    min-width: 0;
    padding: 16px;
    background: var(--color-app-input);
    border: 1px solid var(--color-app-border);
    border-radius: 1rem;
    box-shadow: 0 1px 2px rgba(34, 24, 19, .05);
  }
  .ed-stat:last-child { border-right: 1px solid var(--color-app-border); }
  .ed-stat-label {
    font-size: 9.5px;
    font-weight: 600;
    letter-spacing: .12em;
    text-transform: uppercase;
    color: var(--color-app-muted);
    margin-bottom: 6px;
  }
  .ed-stat-value {
    font-size: 22px;
    font-weight: 700;
    line-height: 1;
    color: var(--color-app-text);
  }
  .ed-stat-value--sm { font-size: 16px; }
  .ed-stat-sub {
    font-size: 10.5px;
    color: var(--color-app-muted);
    margin-top: 4px;
  }
  .ed-stat--dark {
    background: var(--color-app-text);
    border-color: var(--color-app-text);
  }
  .ed-stat--dark .ed-stat-label { color: rgba(255,255,255,.45); }
  .ed-stat--dark .ed-stat-value { color: #fff; }
  .ed-stat--dark .ed-stat-sub   { color: rgba(255,255,255,.4); }

  /* ─── Body: 2-column ─── */
  .ed-body {
    display: grid;
    grid-template-columns: minmax(0,1fr) 300px;
    gap: 14px;
    min-height: 0;
    min-width: 0;
  }

  /* ─── Services panel ─── */
  .ed-services {
    background: var(--color-app-input);
    border: 1px solid var(--color-app-border);
    border-radius: 1rem;
    box-shadow: 0 1px 2px rgba(34, 24, 19, .05);
    overflow: hidden;
    min-width: 0;
  }
  .ed-panel-head {
    padding: 18px 24px;
    border-bottom: 1px solid var(--color-app-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
  }
  .ed-panel-title {
    font-size: 11px;
    font-weight: 600;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: var(--color-app-muted);
  }

  /* Services table */
  .ed-table { width: 100%; min-width: 760px; border-collapse: collapse; }
  .ed-table th {
    font-size: 9.5px;
    font-weight: 600;
    letter-spacing: .12em;
    text-transform: uppercase;
    color: var(--color-app-muted);
    padding: 10px 20px;
    text-align: left;
    background: var(--color-app-soft);
    border-bottom: 1px solid var(--color-app-border);
  }
  .ed-table th:last-child { text-align: right; }
  .ed-table td {
    padding: 14px 20px;
    border-bottom: 1px solid var(--color-app-soft);
    vertical-align: top;
  }
  .ed-table tr:last-child td { border-bottom: none; }
  .ed-table tr:hover td { background: var(--color-app-soft); }

  .ed-svc-thumb {
    width: 38px;
    height: 38px;
    border-radius: 0.5rem;
    object-fit: cover;
    border: 1px solid var(--color-app-border);
    flex-shrink: 0;
  }
  .ed-svc-thumb-placeholder {
    width: 38px;
    height: 38px;
    border-radius: 0.5rem;
    background: var(--color-app-soft);
    border: 1px solid var(--color-app-border);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }
  .ed-svc-thumb-placeholder svg { width: 14px; height: 14px; color: var(--color-app-muted); }
  .ed-svc-name { font-size: 12.5px; font-weight: 600; color: var(--color-app-text); }
  .ed-svc-cat  { font-size: 11px; color: var(--color-app-muted); margin-top: 2px; }
  .ed-svc-note {
    font-size: 10.5px;
    color: var(--color-app-secondary);
    margin-top: 6px;
    padding: 6px 10px;
    background: var(--color-app-soft);
    border-left: 2px solid var(--color-app-border);
    line-height: 1.5;
    overflow-wrap: anywhere;
  }
  .ed-price {
    font-weight: 700;
    font-size: 14px;
    color: var(--color-app-text);
    text-align: right;
    white-space: nowrap;
  }

  /* ─── Empty state ─── */
  .ed-empty { padding: 56px 24px; text-align: center; }
  .ed-empty-icon {
    width: 44px;
    height: 44px;
    border-radius: 0.75rem;
    border: 1px solid var(--color-app-border);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 14px;
  }
  .ed-empty-icon svg { width: 18px; height: 18px; color: var(--color-app-muted); }
  .ed-empty-title { font-size: 16px; font-weight: 700; color: var(--color-app-text); margin-bottom: 4px; }
  .ed-empty-desc  { font-size: 12px; color: var(--color-app-muted); }

  /* ─── Sidebar ─── */
  .ed-sidebar {
    background: var(--color-app-input);
    border: 1px solid var(--color-app-border);
    border-radius: 1rem;
    box-shadow: 0 1px 2px rgba(34, 24, 19, .05);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    min-width: 0;
  }
  .ed-side-block {
    padding: 18px 20px;
    border-bottom: 1px solid var(--color-app-border);
  }
  .ed-side-block:last-child { border-bottom: none; }
  .ed-side-head {
    font-size: 9.5px;
    font-weight: 600;
    letter-spacing: .12em;
    text-transform: uppercase;
    color: var(--color-app-muted);
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 6px;
  }
  .ed-side-head svg { width: 11px; height: 11px; }
  .ed-contact-name {
    font-size: 13px;
    font-weight: 600;
    color: var(--color-app-text);
    margin-bottom: 4px;
  }
  .ed-contact-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    color: var(--color-app-muted);
    margin-bottom: 3px;
  }
  .ed-contact-item svg { width: 11px; height: 11px; flex-shrink: 0; }

  /* Supplier rows */
  .ed-sup-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    padding: 6px 0;
    border-bottom: 1px solid var(--color-app-soft);
  }
  .ed-sup-row:last-child { border-bottom: none; }
  .ed-sup-info { display: flex; align-items: center; gap: 7px; min-width: 0; }
  .ed-sup-thumb {
    width: 22px;
    height: 22px;
    border-radius: 50%;
    object-fit: cover;
    border: 1px solid var(--color-app-border);
    flex-shrink: 0;
  }
  .ed-sup-initial {
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background: var(--color-app-soft);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 9px;
    font-weight: 600;
    color: var(--color-app-muted);
    flex-shrink: 0;
  }
  .ed-sup-name {
    font-size: 12px;
    font-weight: 600;
    color: var(--color-app-text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  /* Special request block */
  .ed-sr-item {
    font-size: 11.5px;
    color: var(--color-app-secondary);
    line-height: 1.6;
    padding: 8px 10px;
    background: var(--color-app-input);
    border: 1px solid var(--color-app-border);
    border-radius: 0.5rem;
    margin-bottom: 6px;
  }
  .ed-sr-item:last-child { margin-bottom: 0; }

  /* ─── Modal ─── */
  .ed-modal-overlay {
    position: fixed;
    inset: 0;
    z-index: 50;
    background: rgba(34,24,19,.45);
    display: none;
    align-items: center;
    justify-content: center;
    padding: 16px;
  }
  .ed-modal-overlay.is-open { display: flex; }
  .ed-modal {
    background: var(--color-app-input);
    border: 1px solid var(--color-app-border);
    border-radius: 0.75rem;
    width: 100%;
    max-width: 440px;
    box-shadow: 0 20px 60px rgba(34,24,19,.18);
  }
  .ed-modal-head {
    padding: 18px 20px;
    border-bottom: 1px solid var(--color-app-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
  .ed-modal-title {
    font-size: 18px;
    font-weight: 700;
    color: var(--color-app-text);
  }
  .ed-modal-close {
    background: none;
    border: none;
    cursor: pointer;
    color: var(--color-app-muted);
    padding: 4px;
    display: flex;
  }
  .ed-modal-close:hover { color: var(--color-app-text); }
  .ed-modal-close svg { width: 16px; height: 16px; }
  .ed-modal-body { padding: 20px; }

  .ed-field { margin-bottom: 14px; }
  .ed-label {
    display: block;
    font-size: 9.5px;
    font-weight: 600;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: var(--color-app-muted);
    margin-bottom: 5px;
  }
  .ed-input, .ed-textarea {
    width: 100%;
    font-family: 'Poppins', system-ui, sans-serif;
    font-size: 13px;
    color: var(--color-app-text);
    background: var(--color-app-input);
    border: 1px solid var(--color-app-border);
    border-radius: 0.5rem;
    padding: 8px 10px;
    outline: none;
    transition: border-color .1s;
  }
  .ed-input:focus, .ed-textarea:focus { border-color: var(--color-app-primary); }
  .ed-textarea { resize: vertical; min-height: 80px; }
  .ed-field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
  .ed-modal-foot {
    padding: 14px 20px;
    border-top: 1px solid var(--color-app-border);
    display: flex;
    gap: 8px;
  }
  .ed-modal-foot .ed-btn { flex: 1; justify-content: center; }

  /* ─── Responsive ─── */
  @container ed-detail (max-width: 1050px) {
    .ed-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .ed-body  { grid-template-columns: 1fr; }
    .ed-sidebar { border-top: 1px solid var(--color-app-border); }
    .ed-services { border-right: none; }
  }
  @container ed-detail (max-width: 680px) {
    .ed-header { padding: 18px 16px 0; }
    .ed-action-bar--flush { margin-left: -16px; margin-right: -16px; padding-left: 16px; padding-right: 16px; }
    .ed-stats { grid-template-columns: 1fr 1fr; }
    .ed-stat { padding: 16px; }
    .ed-panel-head, .ed-side-block { padding: 14px 16px; }
    .ed-title-ref { font-size: 24px; }
  }
  @container ed-detail (max-width: 460px) {
    .ed-stats { grid-template-columns: 1fr; }
    .ed-stat { border-right: none; }
    .ed-action-btns, .ed-action-btns .ed-btn { width: 100%; justify-content: center; }
    .ed-field-row { grid-template-columns: 1fr; }
  }
  @media (max-width: 900px) {
    .ed-stats { grid-template-columns: repeat(2, 1fr); }
    .ed-body  { grid-template-columns: 1fr; }
    .ed-sidebar { border-top: 1px solid var(--color-app-border); }
    .ed-services { border-right: none; }
  }
  @media (max-width: 580px) {
    .ed-header { padding: 18px 16px 0; }
    .ed-action-bar { padding: 12px 16px; }
    .ed-action-bar--flush { margin-left: -16px; margin-right: -16px; }
    .ed-stats { grid-template-columns: 1fr 1fr; }
    .ed-stat { padding: 16px; }
    .ed-table th, .ed-table td { padding: 10px 12px; }
    .ed-panel-head, .ed-side-block { padding: 14px 16px; }
    .ed-title-ref { font-size: 24px; }
  }
</style>

<div class="ed-page">

  <!-- ── Header ── -->
  <header class="ed-header">
    <div class="ed-header-top">
      <div>
        <a href="<?= URLROOT ?>/supplier/bookings" class="ed-back">
          <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M10 12L6 8l4-4"/></svg>
          All bookings
        </a>
        <h1 class="ed-title-ref">
          <?php
            $refParts = $bookingRef ? explode('-', $bookingRef, 2) : ['Booking', ''];
            echo $h($refParts[0]);
            if (!empty($refParts[1])) echo '-<em>' . $h($refParts[1]) . '</em>';
          ?>
        </h1>
        <div class="ed-title-meta">
          <span class="ed-badge <?= $statusBadgeClass($supplierStatus) ?>"><?= $h(ucfirst($supplierStatus)) ?></span>
          <?php if ($daysUntil !== null): ?>
            <span class="ed-countdown inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px] font-semibold
              <?= $daysUntil <= 7 ? 'bg-app-danger/10 text-app-danger' : ($daysUntil <= 21 ? 'bg-app-warning/10 text-app-warning' : 'bg-app-success/10 text-app-success') ?>">
              <i data-lucide="clock" class="h-3 w-3"></i>
              <?php if ($daysUntil < 0): echo abs($daysUntil) . ' days ago';
              elseif ($daysUntil === 0): echo 'Today';
              else: echo $daysUntil . ' day' . ($daysUntil === 1 ? '' : 's') . ' away'; endif; ?>
            </span>
          <?php endif; ?>
          <span class="ed-badge <?= $statusBadgeClass($bookingStatus) ?>">Booking <?= $h(ucfirst($bookingStatus ?: 'pending')) ?></span>
        </div>
      </div>

      <!-- Customer card -->
      <div style="flex-shrink:0">
        <div class="ed-eyebrow" style="margin-bottom:8px">Customer</div>
        <div class="ed-cust-strip">
          <div class="ed-cust-avatar"><?= $h($customerInitial) ?></div>
          <div>
            <div class="ed-cust-name"><?= $h($customerName) ?></div>
            <div class="ed-cust-sub">
              <?php if ($customerPhone !== ''): ?>
                <span class="ed-cust-item">
                  <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 2h2.5l1 3-1.5 1a8 8 0 003 3l1-1.5 3 1V12a1 1 0 01-1 1C6 13 2 8.5 2 4a1 1 0 011-1z"/></svg>
                  <?= $h($customerPhone) ?>
                </span>
              <?php endif; ?>
              <?php if ($customerEmail !== ''): ?>
                <span class="ed-cust-item">
                  <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="4" width="12" height="9" rx="1"/><path d="M2 5l6 5 6-5"/></svg>
                  <?= $h($customerEmail) ?>
                </span>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Pending action bar -->
    <?php if ($supplierStatus === 'pending'): ?>
    <div class="ed-action-bar ed-action-bar--flush">
      <div class="ed-action-bar-copy">
        <div class="ed-action-bar-icon">
          <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 2a4 4 0 014 4v2l1 2H3l1-2V6a4 4 0 014-4zm-1 10h2a1 1 0 01-2 0z"/></svg>
        </div>
        <div>
          <?php if ($bookingStatus === 'pending_supplier_response'): ?>
            <div class="ed-action-bar-title">New booking request</div>
            <div class="ed-action-bar-desc">The customer is requesting your services. Please accept or decline within 48 hours. No payment has been collected yet.</div>
          <?php else: ?>
            <div class="ed-action-bar-title">Response required</div>
            <div class="ed-action-bar-desc">Accept or decline this booking from your queue.</div>
          <?php endif; ?>
        </div>
      </div>
      <div class="ed-action-btns">
        <button type="button" class="booking-action ed-btn ed-btn--accept" data-action="accept">
          <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 8l3.5 3.5L13 5"/></svg>
          <?= $bookingStatus === 'pending_supplier_response' ? 'Accept Request' : 'Accept' ?>
        </button>
        <button type="button" class="booking-action ed-btn ed-btn--decline" data-action="decline">
          <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4l8 8M12 4l-8 8"/></svg>
          <?= $bookingStatus === 'pending_supplier_response' ? 'Decline Request' : 'Decline' ?>
        </button>
        <?php if ($bookingStatus !== 'pending_supplier_response'): ?>
        <button type="button" id="reschedule-btn" class="ed-btn ed-btn--ghost">
          <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="3" width="12" height="11" rx="1"/><path d="M5 2v2M11 2v2M2 7h12"/></svg>
          Propose reschedule
        </button>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>
  </header>

  <!-- ── Stats band ── -->
  <div class="ed-stats">
    <div class="ed-stat">
      <div class="ed-stat-label">Event date</div>
      <div class="ed-stat-value <?= ($firstDate === '') ? 'ed-stat-value--sm' : '' ?>"><?= $h($formatDate($firstDate)) ?></div>
      <div class="ed-stat-sub"><?= $h($eventTime ?: 'Time not set') ?></div>
    </div>
    <div class="ed-stat">
      <div class="ed-stat-label">Guests</div>
      <div class="ed-stat-value"><?= $hasGuestData ? number_format($totalGuests) : '—' ?></div>
      <div class="ed-stat-sub"><?= $hasGuestData ? 'across ' . count($items) . ' service(s)' : 'Not specified' ?></div>
    </div>
    <div class="ed-stat">
      <div class="ed-stat-label">Venue</div>
      <div class="ed-stat-value ed-stat-value--sm" style="margin-top:4px"><?= $h($firstLocation !== '' ? $firstLocation : '—') ?></div>
    </div>
    <div class="ed-stat ed-stat--dark">
      <div class="ed-stat-label">Your earnings</div>
      <div class="ed-stat-value"><?= $money($supplierTotal) ?></div>
      <div class="ed-stat-sub"><?= $money($supplierPaid) ?> paid · <?= $money($supplierRemaining) ?> remaining</div>
    </div>
  </div>

  <!-- ── Body: services + sidebar ── -->
  <div class="ed-body">
    <!-- LEFT: Services -->
    <div class="ed-services">
      <div class="ed-panel-head">
        <span class="ed-panel-title">Services in this booking</span>
        <span class="ed-badge <?= $statusBadgeClass($bookingStatus) ?>"><?= $h(ucfirst($bookingStatus ?: 'pending')) ?></span>
      </div>

      <?php if (empty($items)): ?>
        <div class="ed-empty">
          <div class="ed-empty-icon">
            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 2H3a1 1 0 00-1 1v10a1 1 0 001 1h10a1 1 0 001-1V7M10 2h4v4M8 8l6-6"/></svg>
          </div>
          <p class="ed-empty-title">No items</p>
          <p class="ed-empty-desc">No supplier service lines are attached to this booking.</p>
        </div>
      <?php else: ?>
        <div style="overflow-x:auto">
          <table class="ed-table">
            <thead>
              <tr>
                <th>Service</th>
                <th>Venue</th>
                <th>Guests</th>
                <th>Contact</th>
                <th style="text-align:right">Price</th>
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
              ?>
              <tr>
                <td>
                  <div style="display:flex;align-items:flex-start;gap:10px">
                    <?php if (!empty($item['thumbnail_url'])): ?>
                      <img src="<?= $h($item['thumbnail_url']) ?>" alt="" class="ed-svc-thumb">
                    <?php else: ?>
                      <div class="ed-svc-thumb-placeholder">
                        <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="3" width="12" height="10" rx="1"/><path d="M2 10l3-3 3 3 2-2 4 4"/></svg>
                      </div>
                    <?php endif; ?>
                    <div>
                      <div class="ed-svc-name"><?= $h($item['service_name'] ?? 'Service') ?></div>
                      <div class="ed-svc-cat"><?= $h($item['category_name'] ?? $item['supplier_name'] ?? '') ?></div>
                      <?php if ($itemNotes !== ''): ?>
                        <div class="ed-svc-note"><?= $h($itemNotes) ?></div>
                      <?php endif; ?>
                    </div>
                  </div>
                </td>
                <td style="color:var(--color-app-secondary);font-size:12px;white-space:nowrap"><?= $h($venueText !== '' ? $venueText : '—') ?></td>
                <td style="font-size:13px;font-weight:600;white-space:nowrap"><?= $itemGuests > 0 ? number_format($itemGuests) : '—' ?></td>
                <td>
                  <?php if ($itemContact !== ''): ?>
                    <div class="ed-svc-name" style="font-size:12px"><?= $h($itemContact) ?></div>
                    <?php if ($itemPhone !== ''): ?>
                      <div class="ed-svc-cat"><?= $h($itemPhone) ?></div>
                    <?php endif; ?>
                  <?php else: ?>
                    <span style="color:var(--color-app-muted)">—</span>
                  <?php endif; ?>
                </td>
                <td>
                  <div class="ed-price"><?= $money($item['price'] ?? 0) ?></div>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

    <!-- RIGHT: Sidebar -->
    <aside class="ed-sidebar">
      <!-- On-site contact -->
      <?php if ($firstContactName !== ''): ?>
      <div class="ed-side-block">
        <div class="ed-side-head">
          <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="8" cy="5" r="3"/><path d="M2 14a6 6 0 0112 0"/><path d="M11 8l1.5 1.5L15 7"/></svg>
          On-site contact
        </div>
        <div class="ed-contact-name"><?= $h($firstContactName) ?></div>
        <?php if ($firstContactPhone !== ''): ?>
          <div class="ed-contact-item">
            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 2h2.5l1 3-1.5 1a8 8 0 003 3l1-1.5 3 1V12a1 1 0 01-1 1C6 13 2 8.5 2 4a1 1 0 011-1z"/></svg>
            <?= $h($firstContactPhone) ?>
          </div>
        <?php endif; ?>
      </div>
      <?php endif; ?>

      <!-- Other suppliers -->
      <div class="ed-side-block">
        <div class="ed-side-head">
          <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="5.5" cy="5" r="2.5"/><circle cx="10.5" cy="5" r="2.5"/><path d="M1 14a4.5 4.5 0 019 0M6 14a4.5 4.5 0 019 0"/></svg>
          Other suppliers
        </div>
        <?php if (empty($otherSuppliers)): ?>
          <p style="font-size:11.5px;color:var(--color-app-muted)">No other suppliers on this booking.</p>
        <?php else: ?>
          <?php foreach ($otherSuppliers as $os):
            $osStatus = strtolower((string)($os['status'] ?? 'pending'));
          ?>
            <div class="ed-sup-row">
              <div class="ed-sup-info">
                <?php if (!empty($os['thumbnail_url'])): ?>
                  <img src="<?= $h($os['thumbnail_url']) ?>" alt="" class="ed-sup-thumb">
                <?php else: ?>
                  <div class="ed-sup-initial"><?= strtoupper(substr((string)($os['shop_name'] ?? 'S'), 0, 1)) ?></div>
                <?php endif; ?>
                <span class="ed-sup-name"><?= $h($os['shop_name'] ?? 'Supplier') ?></span>
              </div>
              <span class="ed-badge <?= $statusBadgeClass($osStatus) ?>"><?= $h(ucfirst($osStatus)) ?></span>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <!-- Special requests -->
      <?php if (!empty($allSpecialRequests)): ?>
      <div class="ed-side-block">
        <div class="ed-side-head">
          <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H2a1 1 0 00-1 1v8a1 1 0 001 1h5l3 3v-3h4a1 1 0 001-1V3a1 1 0 00-1-1z"/></svg>
          Special requests
        </div>
        <?php foreach ($allSpecialRequests as $sr): ?>
          <div class="ed-sr-item"><?= $h($sr) ?></div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </aside>
  </div><!-- /.ed-body -->

  <!-- ── Reschedule Modal ── -->
  <div id="reschedule-modal" class="ed-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="reschedule-modal-title">
    <div class="ed-modal">
      <div class="ed-modal-head">
        <h2 id="reschedule-modal-title" class="ed-modal-title">Propose reschedule</h2>
        <button type="button" class="reschedule-close ed-modal-close" aria-label="Close">
          <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 4l8 8M12 4l-8 8"/></svg>
        </button>
      </div>
      <form id="reschedule-form">
        <div class="ed-modal-body">
          <div class="ed-field">
            <label class="ed-label" for="rs-date">Proposed date</label>
            <input type="date" id="rs-date" name="proposed_date" required class="ed-input">
          </div>
          <div class="ed-field ed-field-row">
            <div>
              <label class="ed-label" for="rs-start">Start time</label>
              <input type="time" id="rs-start" name="proposed_start_time" required class="ed-input">
            </div>
            <div>
              <label class="ed-label" for="rs-end">End time</label>
              <input type="time" id="rs-end" name="proposed_end_time" required class="ed-input">
            </div>
          </div>
          <div class="ed-field" style="margin-bottom:0">
            <label class="ed-label" for="rs-reason">Reason</label>
            <textarea id="rs-reason" name="reason" rows="3" placeholder="e.g. Equipment already booked on that date" class="ed-textarea"></textarea>
          </div>
        </div>
        <div class="ed-modal-foot">
          <button type="button" class="reschedule-close ed-btn ed-btn--decline">Cancel</button>
          <button type="submit" class="ed-btn ed-btn--accept">Send proposal</button>
        </div>
      </form>
    </div>
  </div>

</div><!-- /.ed-page -->

<script>
/* ── Reschedule modal ── */
const rescheduleModal = document.getElementById('reschedule-modal');
const rescheduleBtn   = document.getElementById('reschedule-btn');
const rescheduleForm  = document.getElementById('reschedule-form');
const closeButtons    = document.querySelectorAll('.reschedule-close');

if (rescheduleBtn && rescheduleModal) {
  rescheduleBtn.addEventListener('click', () => rescheduleModal.classList.add('is-open'));
  closeButtons.forEach(btn => btn.addEventListener('click', () => rescheduleModal.classList.remove('is-open')));
  rescheduleModal.addEventListener('click', e => { if (e.target === rescheduleModal) rescheduleModal.classList.remove('is-open'); });

  rescheduleForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const submitBtn = rescheduleForm.querySelector('button[type="submit"]');
    const originalHTML = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = 'Sending…';

    const formData = new FormData(rescheduleForm);
    formData.append('booking_id', '<?= (int)($booking['id'] ?? 0) ?>');

    try {
      const response = await fetch('<?= URLROOT ?>/supplier/proposeReschedule', { method: 'POST', body: formData });
      const data = await response.json().catch(() => ({}));
      if (data.success) {
        alert(data.message);
        rescheduleModal.classList.remove('is-open');
        rescheduleForm.reset();
        window.location.reload();
      } else {
        alert(data.error || 'Could not send reschedule proposal.');
      }
    } catch {
      alert('Network error. Please try again.');
    }
    submitBtn.disabled = false;
    submitBtn.innerHTML = originalHTML;
  });
}

/* ── Accept / Decline ── */
document.querySelectorAll('.booking-action').forEach((button) => {
  button.addEventListener('click', async () => {
    button.disabled = true;
    const originalHTML = button.innerHTML;
    button.innerHTML = 'Updating…';

    const formData = new FormData();
    formData.append('booking_id', '<?= (int)($booking['id'] ?? 0) ?>');
    formData.append('action', button.dataset.action);

    try {
      const response = await fetch('<?= URLROOT ?>/supplier/bookingRespond', { method: 'POST', body: formData });
      const data = await response.json().catch(() => ({}));
      if (data.success) { window.location.reload(); return; }
      alert(data.error || 'Could not update booking.');
    } catch {
      alert('Network error. Please try again.');
    }
    button.disabled = false;
    button.innerHTML = originalHTML;
  });
});
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
