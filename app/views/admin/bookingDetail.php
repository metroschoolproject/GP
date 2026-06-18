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
$statusLabel = ucwords(str_replace('_', ' ', (string)($booking['status'] ?? 'draft')));
$custName = (string)($booking['customer_name'] ?? 'Customer');
$custInitials = strtoupper(mb_substr(trim($custName) !== '' ? $custName : 'C', 0, 1));

$badgeClass = static function (string $status): string {
    return match (strtolower($status)) {
        'paid', 'success', 'completed' => 'badge-success',
        'confirmed' => 'badge-info',
        'cancelled', 'failed', 'rejected' => 'badge-failed',
        'pending', 'pending_payment', 'payment_submitted' => 'badge-pending',
        default => 'badge-neutral',
    };
};

$supplierStatusDot = static function (string $status): string {
    return match (strtolower($status)) {
        'confirmed', 'accepted' => 'dot-success',
        'pending', 'pending_supplier_response' => 'dot-warn',
        'rejected', 'cancelled' => 'dot-danger',
        default => 'dot-neutral',
    };
};

$logDot = static function (string $status): string {
    return match (strtolower($status)) {
        'confirmed', 'completed', 'paid' => 'dot-success',
        'cancelled', 'rejected' => 'dot-danger',
        'pending_payment', 'payment_submitted' => 'dot-warn',
        default => 'dot-neutral',
    };
};

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
    $statusLabel,
    $depositPercent,
    $custName,
    $custInitials,
    $badgeClass,
    $supplierStatusDot,
    $logDot
) {
    $bookingId = (int)($booking['id'] ?? 0);
    $createdAt = $dateOnly($booking['created_at'] ?? null);
    $canCancel = !in_array(($booking['status'] ?? ''), ['cancelled', 'completed'], true);
    $canMarkReceived = !in_array(($booking['status'] ?? ''), ['payment_verified', 'paid', 'confirmed', 'pending_final_payment', 'finalized', 'completed', 'cancelled'], true);
?>
<style>
  .admin-booking-detail-outlet{min-height:100%;background:#FBFBF9;padding:28px 32px;font-family:'DM Sans',Poppins,system-ui,-apple-system,sans-serif;color:#111827;font-size:13px;overflow-y:auto}
  .booking-detail-page *{box-sizing:border-box}
  .booking-detail-page{--surface:#ffffff;--soft:#faf5ef;--hover:#eddecc;--border:#ead8c7;--border-light:#eddecc;--primary:#6d4c5b;--primary-soft:#eddecc;--text:#111827;--muted:#b79c8b;--body:#7b5c69;--success-bg:#d1fae5;--success-text:#065f46;--warn-bg:#fef3c7;--warn-text:#92400e;--danger-bg:#fee2e2;--danger-text:#991b1b;--info-bg:#e8e7ff;--info-text:#4f46a5;--neutral-bg:#f3f4f6;--neutral-text:#57534e;max-width:1600px;margin:0 auto}
  .page-header{display:flex;align-items:flex-end;justify-content:space-between;gap:16px;margin-bottom:22px}
  .eyebrow{font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--muted);margin-bottom:4px}
  .booking-detail-page h1{font-size:22px;font-weight:700;color:var(--text);letter-spacing:-.3px;margin:0}
  .subtitle{margin-top:5px;color:var(--body);font-size:12px;font-weight:600}
  .header-actions{display:flex;gap:8px;flex-wrap:wrap}
  .btn-ghost{display:inline-flex;align-items:center;gap:6px;padding:0 14px;height:34px;border:1px solid var(--border);border-radius:.75rem;background:var(--surface);color:var(--primary);font-size:12px;font-weight:700;font-family:inherit;cursor:pointer;transition:background .12s;text-decoration:none}
  .btn-ghost:hover{background:var(--primary-soft)}
  .btn-primary{display:inline-flex;align-items:center;gap:6px;padding:0 14px;height:34px;border:1px solid var(--primary);border-radius:.75rem;background:var(--primary);color:#fff;font-size:12px;font-weight:800;font-family:inherit;cursor:pointer;transition:background .12s;text-decoration:none}
  .btn-primary:hover{background:#7b5c69}
  .summary-row{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:20px}
  .stat{background:var(--surface);border:1px solid var(--border);border-radius:.75rem;padding:14px 16px}
  .stat-label{font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--muted);margin-bottom:6px}
  .stat-value{font-size:20px;font-weight:700;color:var(--text);letter-spacing:-.3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
  .stat-value.success{color:var(--success-text)}
  .stat-value.danger{color:var(--danger-text)}
  .stat-value.primary{color:var(--primary)}
  .stat-sub{font-size:11px;color:var(--muted);margin-top:3px}
  .detail-grid{display:grid;grid-template-columns:minmax(0,1fr) 360px;gap:20px;align-items:start}
  .stack{display:grid;gap:20px}
  .card{background:var(--surface);border:1px solid var(--border);border-radius:.75rem;overflow:hidden;box-shadow:0 1px 2px rgba(28,25,23,.04)}
  .card-head{padding:14px 20px;border-bottom:1px solid var(--border-light);display:flex;align-items:center;justify-content:space-between;gap:12px}
  .card-head-left{display:flex;align-items:center;gap:8px}
  .card-head-icon{width:28px;height:28px;border-radius:.75rem;background:var(--primary-soft);display:flex;align-items:center;justify-content:center;color:var(--primary)}
  .card-head-title{font-size:13px;font-weight:700;color:var(--text)}
  .card-count{font-size:11px;color:var(--muted);font-weight:600}
  .card-body{padding:16px 20px}
  .badge{display:inline-flex;align-items:center;gap:6px;border-radius:20px;padding:3px 9px;font-size:10px;font-weight:800;letter-spacing:.05em;text-transform:uppercase;white-space:nowrap}
  .badge:before{content:"";width:7px;height:7px;border-radius:999px;background:currentColor}
  .badge-success{background:var(--success-bg);color:var(--success-text)}
  .badge-pending{background:var(--warn-bg);color:var(--warn-text)}
  .badge-failed{background:var(--danger-bg);color:var(--danger-text)}
  .badge-info{background:var(--info-bg);color:var(--info-text)}
  .badge-neutral{background:var(--neutral-bg);color:var(--neutral-text)}
  .kv-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px}
  .kv{border:1px solid var(--border-light);border-radius:.75rem;background:var(--soft);padding:11px 12px}
  .kv-label{font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--muted)}
  .kv-value{margin-top:5px;color:var(--text);font-size:13px;font-weight:800;overflow-wrap:anywhere}
  .kv-sub{margin-top:2px;color:var(--body);font-size:11px;font-weight:600}
  .progress{height:7px;border-radius:999px;background:var(--soft);overflow:hidden;margin-top:10px}
  .progress span{display:block;height:100%;border-radius:999px;background:var(--primary)}
  .detail-table-wrap{overflow-x:auto}
  .detail-table{width:100%;border-collapse:collapse}
  .detail-table thead tr{background:var(--soft)}
  .detail-table th{padding:9px 20px;font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--muted);text-align:left;white-space:nowrap}
  .detail-table th:last-child{text-align:right}
  .detail-table tbody tr{border-top:1px solid var(--border-light);transition:background .1s}
  .detail-table tbody tr:hover{background:var(--soft)}
  .detail-table td{padding:13px 20px;vertical-align:middle}
  .detail-table td:last-child{text-align:right}
  .main-text{font-weight:700;color:var(--text);font-size:13px}
  .sub-text{font-size:11px;color:var(--muted);margin-top:2px}
  .amount{font-weight:800;color:var(--text);white-space:nowrap}
  .proof-link{display:block;overflow:hidden;border:1px solid var(--border);border-radius:.75rem;background:var(--surface);text-decoration:none}
  .proof-link img{width:100%;max-height:260px;object-fit:contain;background:#fff}
  .proof-file{display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:150px;color:var(--primary);font-size:12px;font-weight:800}
  .empty-proof{border:1px dashed var(--border);border-radius:.75rem;background:var(--surface);padding:24px;text-align:center;color:var(--muted);font-size:12px;font-weight:600}
  .review-form{border:1px solid var(--border);border-radius:.75rem;background:var(--soft);padding:12px;margin-top:12px}
  .review-form textarea,.cancel-form textarea{width:100%;min-height:70px;border:1px solid var(--border);border-radius:.75rem;background:var(--surface);padding:10px 12px;color:var(--text);font:inherit;font-size:12px;outline:none;resize:vertical}
  .review-actions{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:8px}
  .action-btn{height:34px;border:0;border-radius:.75rem;padding:0 12px;color:#fff;font-size:11px;font-weight:800;font-family:inherit;cursor:pointer}
  .action-approve{background:var(--primary)}
  .action-reject{background:#991b1b}
  .side-list{display:grid;gap:8px}
  .person{display:flex;align-items:center;gap:10px}
  .avatar{display:grid;place-items:center;width:34px;height:34px;border-radius:999px;background:var(--primary-soft);color:var(--primary);font-size:12px;font-weight:800}
  .row-card{display:flex;align-items:center;gap:10px;border:1px solid var(--border-light);border-radius:.75rem;background:var(--soft);padding:9px 10px}
  .row-card-main{flex:1;min-width:0}
  .row-card-title{font-size:12px;font-weight:800;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
  .row-card-sub{font-size:10px;color:var(--muted);font-weight:700;text-transform:uppercase;letter-spacing:.06em}
  .dot{display:inline-block;width:7px;height:7px;border-radius:999px}
  .dot-success{background:#059669}.dot-warn{background:#d97706}.dot-danger{background:#dc2626}.dot-neutral{background:#a8a29e}
  .timeline{display:grid;gap:0}
  .timeline-item{display:grid;grid-template-columns:16px 1fr;gap:8px}
  .timeline-line{display:flex;flex-direction:column;align-items:center}
  .timeline-line:after{content:"";width:1px;flex:1;background:var(--border-light);margin-top:5px}
  .timeline-item:last-child .timeline-line:after{display:none}
  .timeline-content{padding-bottom:12px}
  .cancel-form{display:grid;gap:9px}
  .cancel-check{display:flex;align-items:center;gap:8px;color:var(--body);font-size:12px;font-weight:700}
  .cancel-btn{height:34px;border:0;border-radius:.75rem;background:#991b1b;color:#fff;font-size:11px;font-weight:800;font-family:inherit;cursor:pointer}
  @media(max-width:1180px){.detail-grid{grid-template-columns:1fr}.summary-row,.kv-grid{grid-template-columns:repeat(2,1fr)}}
  @media(max-width:760px){.admin-booking-detail-outlet{padding:20px 16px}.page-header{align-items:flex-start;flex-direction:column}.summary-row,.kv-grid{grid-template-columns:1fr}.review-actions{grid-template-columns:1fr}}
</style>

<div class="booking-detail-page">
  <div class="page-header">
    <div>
      <p class="eyebrow">Booking detail</p>
      <h1><?= $h($bookingRef ?: ('Booking #' . $bookingId)) ?></h1>
      <p class="subtitle"><?= $h($custName) ?> · <?= $h($booking['customer_email'] ?? '-') ?> · Created <?= $h($createdAt) ?></p>
    </div>
    <div class="header-actions">
      <a href="<?= URLROOT ?>/admin/bookings" class="btn-ghost"><i data-lucide="chevron-left" class="h-3.5 w-3.5"></i>Back</a>
      <button type="button" id="copy-ref-btn" class="btn-ghost" data-ref="<?= $h($bookingRef) ?>"><i data-lucide="copy" class="h-3.5 w-3.5"></i>Copy ref</button>
      <?php if ($isAwaitingReview): ?>
        <button type="button" class="btn-primary verify-payment-btn"><i data-lucide="circle-check" class="h-3.5 w-3.5"></i>Approve payment + notify</button>
      <?php endif; ?>

      <a href="<?= URLROOT ?>/admin/paymentVerification" class="btn-ghost"><i data-lucide="receipt-text" class="h-3.5 w-3.5"></i>Payment review</a>
    </div>
  </div>

  <div class="summary-row">
    <div class="stat">
      <div class="stat-label">Status</div>
      <div class="stat-value primary" id="booking-status-value"><?= $h($statusLabel) ?></div>
      <div class="stat-sub">Current booking state</div>
    </div>
    <div class="stat">
      <div class="stat-label">Total</div>
      <div class="stat-value"><?= $money($totalAmount) ?></div>
      <div class="stat-sub"><?= count($items) ?> booked item<?= count($items) === 1 ? '' : 's' ?></div>
    </div>
    <div class="stat">
      <div class="stat-label">Paid</div>
      <div class="stat-value success" id="booking-paid-value"><?= $money($paidAmount) ?></div>
      <div class="stat-sub" id="booking-paid-percent"><?= $paidPercent ?>% collected</div>
    </div>
    <div class="stat">
      <div class="stat-label">Balance</div>
      <div class="stat-value <?= $balanceDue > 0 ? 'danger' : 'success' ?>" id="booking-balance-value"><?= $money($balanceDue) ?></div>
      <div class="stat-sub">Remaining payment</div>
    </div>
  </div>

  <div class="detail-grid">
    <main class="stack">
      <div class="card">
        <div class="card-head">
          <div class="card-head-left">
            <div class="card-head-icon"><i data-lucide="receipt" class="h-4 w-4"></i></div>
            <span class="card-head-title">Deposit payment</span>
          </div>
          <span class="badge <?= $badgeClass($paymentStatus) ?>" id="payment-status-badge"><?= $h(ucwords(str_replace('_', ' ', $paymentStatus))) ?></span>
        </div>
        <div class="card-body">
          <div class="kv-grid">
            <div class="kv">
              <div class="kv-label">Amount sent</div>
              <div class="kv-value"><?= $money($sentAmount) ?></div>
              <div class="kv-sub">Submitted by customer</div>
            </div>
            <div class="kv">
              <div class="kv-label">Expected deposit</div>
              <div class="kv-value"><?= $money($expectedDeposit) ?></div>
              <div class="kv-sub"><?= (int)$depositPercent ?>% of total</div>
            </div>
            <div class="kv">
              <div class="kv-label">Method</div>
              <div class="kv-value"><?= $h($paymentMethod ?: '-') ?></div>
              <div class="kv-sub"><?= $h($dateTime($reviewPayment['paid_at'] ?? null)) ?></div>
            </div>
            <div class="kv">
              <div class="kv-label">Payment reference</div>
              <div class="kv-value"><?= $h($transactionRef ?: '-') ?></div>
            </div>
            <div class="kv">
              <div class="kv-label">Sender account</div>
              <div class="kv-value"><?= $h($reviewPayment['account_name'] ?? '-') ?></div>
            </div>
            <div class="kv">
              <div class="kv-label">Sender phone</div>
              <div class="kv-value"><?= $h($reviewPayment['mobile_number'] ?? '-') ?></div>
            </div>
          </div>
          <div class="progress"><span id="payment-progress-bar" style="width:<?= min(100, max(0, $paidPercent)) ?>%"></span></div>
        </div>
      </div>

      <div class="card">
        <div class="card-head">
          <div class="card-head-left">
            <div class="card-head-icon"><i data-lucide="calendar-check" class="h-4 w-4"></i></div>
            <span class="card-head-title">Booked services</span>
          </div>
          <span class="card-count"><?= count($items) ?> records</span>
        </div>
        <div class="detail-table-wrap">
          <table class="detail-table">
            <thead>
              <tr>
                <th>Service</th>
                <th>Supplier</th>
                <th>Schedule</th>
                <th>Guests</th>
                <th>Price</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($items)): ?>
                <tr><td colspan="5" class="empty-proof">No services found for this booking.</td></tr>
              <?php endif; ?>
              <?php foreach ($items as $item): ?>
                <?php
                  $hallName = trim((string)($item['venue_room_name'] ?? ''));
                  $venueName = trim((string)($item['venue_name'] ?? ''));
                  $date = $dateOnly($item['booking_date'] ?? null);
                  $time = trim($timeOnly($item['start_time'] ?? null) . ' - ' . $timeOnly($item['end_time'] ?? null), ' -');
                  $itemEvent = $eventDetailsByItem[(int)($item['id'] ?? 0)] ?? [];
                ?>
                <tr>
                  <td>
	                    <div class="main-text"><?= $h($item['service_name'] ?? 'Service') ?></div>
	                    <?php if (!empty($item['addon_package_name'])): ?>
	                      <div class="sub-text">Add-on for <?= $h($item['addon_package_name']) ?></div>
	                    <?php endif; ?>
                    <?php if ($hallName !== '' || $venueName !== ''): ?>
                      <div class="sub-text"><?= $h(trim($hallName . ($venueName !== '' ? ' · ' . $venueName : ''))) ?></div>
                    <?php endif; ?>
                  </td>
                  <td><span class="main-text"><?= $h($item['supplier_name'] ?? 'Supplier') ?></span></td>
                  <td>
                    <div class="main-text"><?= $h($date) ?></div>
                    <div class="sub-text"><?= $h($time !== '' ? $time : '-') ?></div>
                  </td>
                  <td><span class="main-text"><?= $h($itemEvent['guest_count'] ?? '-') ?></span></td>
                  <td><span class="amount"><?= $money($item['price'] ?? 0) ?></span></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div class="card">
        <div class="card-head">
          <div class="card-head-left">
            <div class="card-head-icon"><i data-lucide="map-pin" class="h-4 w-4"></i></div>
            <span class="card-head-title">Event information</span>
          </div>
        </div>
        <div class="card-body">
          <?php
            // Show event info only for standalone (non-addon) items.
            $hasStandaloneEvent = false;
            foreach ($items as $item):
              $itemId = (int)($item['id'] ?? 0);
              if (!in_array($itemId, $nonAddonItemIds, true)) continue;
              $itemEvent = $eventDetailsByItem[$itemId] ?? [];
              if (empty($itemEvent)) continue;
              $hasStandaloneEvent = true;
          ?>
            <div class="main-text" style="margin-bottom:10px;"><?= $h($item['service_name'] ?? 'Service') ?></div>
            <div class="kv-grid" style="margin-bottom:18px;">
              <div class="kv"><div class="kv-label">Date</div><div class="kv-value"><?= $h($dateOnly($itemEvent['event_date'] ?? null)) ?></div></div>
              <div class="kv"><div class="kv-label">Time</div><div class="kv-value"><?= $h($timeOnly($itemEvent['start_time'] ?? null)) ?> - <?= $h($timeOnly($itemEvent['end_time'] ?? null)) ?></div></div>
              <div class="kv"><div class="kv-label">Guests</div><div class="kv-value"><?= $h($itemEvent['guest_count'] ?? '-') ?></div></div>
              <div class="kv"><div class="kv-label">Location</div><div class="kv-value"><?= $h($itemEvent['location'] ?? '-') ?></div></div>
              <div class="kv"><div class="kv-label">Contact</div><div class="kv-value"><?= $h($itemEvent['contact_name'] ?? ($booking['customer_name'] ?? '-')) ?></div><div class="kv-sub"><?= $h($itemEvent['contact_phone'] ?? ($booking['customer_phone'] ?? '-')) ?></div></div>
            </div>
          <?php endforeach; ?>
          <?php if (!$hasStandaloneEvent): ?>
            <div class="kv-grid">
              <div class="kv"><div class="kv-label">Date</div><div class="kv-value"><?= $h($dateOnly($firstEvent['event_date'] ?? null)) ?></div></div>
              <div class="kv"><div class="kv-label">Time</div><div class="kv-value"><?= $h($timeOnly($displayEventStart)) ?> - <?= $h($timeOnly($displayEventEnd)) ?></div></div>
              <div class="kv"><div class="kv-label">Guests</div><div class="kv-value"><?= $h($firstEvent['guest_count'] ?? '-') ?></div></div>
              <div class="kv"><div class="kv-label">Location</div><div class="kv-value"><?= $h($firstEvent['location'] ?? '-') ?></div></div>
              <div class="kv"><div class="kv-label">Contact</div><div class="kv-value"><?= $h($firstEvent['contact_name'] ?? ($booking['customer_name'] ?? '-')) ?></div><div class="kv-sub"><?= $h($firstEvent['contact_phone'] ?? ($booking['customer_phone'] ?? '-')) ?></div></div>
            </div>
          <?php endif; ?>
          <?php foreach ($items as $item): ?>
            <?php
              $schedule = $packageSchedules[(int)($item['id'] ?? 0)] ?? [];
              if (empty($schedule)) continue;
            ?>
            <div style="margin-top:16px">
              <div class="card-head-title"><?= $h($item['service_name'] ?? 'Package') ?> event timeline</div>
              <div class="detail-table-wrap" style="margin-top:8px;border:1px solid var(--border-light);border-radius:.75rem">
                <table class="detail-table">
                  <thead>
                    <tr><th>Package service</th><th>Supplier</th><th>Event date</th><th>Managed time</th></tr>
                  </thead>
                  <tbody>
                    <?php foreach ($schedule as $event): ?>
                      <tr>
                        <td>
                          <div class="main-text"><?= $h($event['service_name'] ?? 'Service') ?></div>
                          <div class="sub-text"><?= $h($event['category_name'] ?? 'Package service') ?></div>
                        </td>
                        <td><span class="main-text"><?= $h($event['supplier_name'] ?? 'Golden Promise') ?></span></td>
                        <td><span class="main-text"><?= $h($dateOnly($event['event_date'] ?? null)) ?></span></td>
                        <td><span class="main-text"><?= $h($timeOnly($event['start_time'] ?? null)) ?> - <?= $h($timeOnly($event['end_time'] ?? null)) ?></span></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </main>

    <aside class="stack">
      <div class="card">
        <div class="card-head">
          <div class="card-head-left">
            <div class="card-head-icon"><i data-lucide="image" class="h-4 w-4"></i></div>
            <span class="card-head-title">Payment proof</span>
          </div>
        </div>
        <div class="card-body">
          <?php if ($slipPath !== ''): ?>
            <a href="<?= URLROOT ?>/<?= $h($slipPath) ?>" target="_blank" class="proof-link">
              <?php if ($isImageSlip): ?>
                <img src="<?= URLROOT ?>/<?= $h($slipPath) ?>" alt="Payment slip">
              <?php else: ?>
                <span class="proof-file"><i data-lucide="file-text" class="h-7 w-7"></i>Open uploaded document</span>
              <?php endif; ?>
            </a>
          <?php else: ?>
            <div class="empty-proof">No payment proof file was saved for this booking.</div>
          <?php endif; ?>

	          <?php if ($isAwaitingReview): ?>
	            <form id="payment-review-form" class="review-form" data-booking-id="<?= $bookingId ?>">
              <textarea name="note" placeholder="Admin note (optional)"></textarea>
              <div class="review-actions">
                <button class="action-btn action-reject reject-payment-btn" type="button">Reject</button>
              </div>
	            </form>
	          <?php endif; ?>
	          <div id="payment-email-result" class="sub-text" style="margin-top:10px;"></div>
        </div>
      </div>

      <div class="card">
        <div class="card-head">
          <div class="card-head-left">
            <div class="card-head-icon"><i data-lucide="user-circle" class="h-4 w-4"></i></div>
            <span class="card-head-title">Customer</span>
          </div>
        </div>
        <div class="card-body">
          <div class="person">
            <div class="avatar"><?= $h($custInitials) ?></div>
            <div>
              <div class="main-text"><?= $h($custName) ?></div>
              <div class="sub-text"><?= $h($booking['customer_email'] ?? '-') ?></div>
            </div>
          </div>
          <div class="kv" style="margin-top:10px">
            <div class="kv-label">Phone</div>
            <div class="kv-value"><?= $h($booking['customer_phone'] ?? '-') ?></div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-head">
          <div class="card-head-left">
            <div class="card-head-icon"><i data-lucide="store" class="h-4 w-4"></i></div>
            <span class="card-head-title">Suppliers</span>
          </div>
        </div>
        <div class="card-body">
          <?php if (empty($suppliers)): ?>
            <div class="empty-proof">No supplier assigned.</div>
          <?php else: ?>
            <div class="side-list">
              <?php foreach ($suppliers as $supplier): ?>
                <?php
                  $sName = (string)($supplier['shop_name'] ?? 'Supplier');
                  $sStatus = (string)($supplier['status'] ?? 'pending');
                ?>
                <div class="row-card">
                  <span class="dot <?= $supplierStatusDot($sStatus) ?>"></span>
                  <div class="row-card-main">
                    <div class="row-card-title"><?= $h($sName) ?></div>
                    <div class="row-card-sub"><?= $h(ucwords(str_replace('_', ' ', $sStatus))) ?></div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <div class="card">
        <div class="card-head">
          <div class="card-head-left">
            <div class="card-head-icon"><i data-lucide="scroll-text" class="h-4 w-4"></i></div>
            <span class="card-head-title">Audit trail</span>
          </div>
        </div>
        <div class="card-body">
          <?php if (empty($logs)): ?>
            <div class="empty-proof">No status history yet.</div>
          <?php else: ?>
            <div class="timeline">
              <?php $reversed = array_reverse($logs); ?>
              <?php foreach ($reversed as $log): ?>
                <?php
                  $logStatus = (string)($log['new_status'] ?? '');
                  $logNote = trim((string)($log['note'] ?? ''));
                ?>
                <div class="timeline-item">
                  <div class="timeline-line"><span class="dot <?= $logDot($logStatus) ?>"></span></div>
                  <div class="timeline-content">
                    <div class="main-text"><?= $h(ucwords(str_replace('_', ' ', $logStatus))) ?></div>
                    <div class="sub-text"><?= $h($dateTime($log['created_at'] ?? null)) ?></div>
                    <?php if ($logNote !== ''): ?><div class="sub-text"><?= $h($logNote) ?></div><?php endif; ?>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <?php if ($canCancel): ?>
        <div class="card">
          <div class="card-head">
            <div class="card-head-left">
              <div class="card-head-icon"><i data-lucide="alert-triangle" class="h-4 w-4"></i></div>
              <span class="card-head-title">Cancel booking</span>
            </div>
          </div>
          <div class="card-body">
            <form id="admin-cancel-form" class="cancel-form">
              <input type="hidden" name="booking_id" value="<?= $bookingId ?>">
              <textarea name="reason" required placeholder="Cancellation reason"></textarea>
              <label class="cancel-check">
                <input type="checkbox" name="refund_deposit" value="1">
                Mark deposit as refunded
              </label>
              <button class="cancel-btn" type="submit">Cancel booking</button>
            </form>
          </div>
        </div>
      <?php endif; ?>
    </aside>
  </div>
</div>

<div id="toast" class="fixed right-4 top-4 z-50 max-w-sm -translate-y-2 opacity-0 pointer-events-none transition-all duration-300"></div>

<script>
function showToast(message, type = 'success') {
  const toast = document.getElementById('toast');
  toast.textContent = message;
  toast.className = 'fixed right-4 top-4 z-50 max-w-sm opacity-100 pointer-events-auto transition-all duration-300 translate-y-0 rounded-lg border px-4 py-3 text-sm font-bold';
  if (type === 'error') {
    toast.classList.add('border-rose-200', 'bg-rose-50', 'text-rose-800');
  } else {
    toast.classList.add('border-emerald-200', 'bg-emerald-50', 'text-emerald-800');
  }
  setTimeout(() => {
    toast.classList.remove('opacity-100', 'pointer-events-auto', 'translate-y-0');
    toast.classList.add('opacity-0', 'pointer-events-none', '-translate-y-2');
  }, 3500);
}

const copyBtn = document.getElementById('copy-ref-btn');
if (copyBtn) {
  copyBtn.addEventListener('click', () => {
    const ref = copyBtn.dataset.ref;
    navigator.clipboard.writeText(ref).then(() => {
      showToast('Booking reference copied.');
    }).catch(() => {
      showToast('Could not copy. Please try manually.', 'error');
    });
  });
}

const paymentReviewForm = document.getElementById('payment-review-form');
if (paymentReviewForm) {
  document.addEventListener('click', async (event) => {
    if (event.target.matches('.verify-payment-btn') || event.target.closest('.verify-payment-btn')) {
      await handlePaymentReview(paymentReviewForm, true);
    }
    if (event.target.matches('.reject-payment-btn') || event.target.closest('.reject-payment-btn')) {
      await handlePaymentReview(paymentReviewForm, false);
    }
  });
}

const markReceivedBtn = document.getElementById('mark-received-btn');
if (markReceivedBtn) {
  markReceivedBtn.addEventListener('click', async () => {
    if (!confirm('Mark this booking as received and notify the customer and suppliers?')) return;
    const note = prompt('Optional admin note for this received action:') || '';
    const formData = new FormData();
    formData.append('booking_id', markReceivedBtn.dataset.bookingId);
    formData.append('note', note);

    try {
      const response = await fetch('<?= URLROOT ?>/admin/markBookingReceived', { method: 'POST', body: formData });
      const data = await response.json();
      if (data.success) {
        showToast(data.message || 'Booking marked as received.');
        setTimeout(() => window.location.reload(), 1200);
      } else {
        showToast(data.error || 'Could not mark booking as received.', 'error');
      }
    } catch (error) {
      showToast('Connection error. Please try again.', 'error');
    }
  });
}

async function handlePaymentReview(form, approve) {
  const bookingId = form.dataset.bookingId;
  const note = form.querySelector('textarea[name="note"]').value;
  const endpoint = approve
    ? '<?= URLROOT ?>/admin/verifyPaymentPost'
    : '<?= URLROOT ?>/admin/rejectPaymentSlipPost';
  const formData = new FormData();
  formData.append('booking_id', bookingId);
  formData.append('note', note);

  if (!approve) {
    const reason = prompt('Reason for rejecting this payment proof:');
    if (!reason) return;
    formData.set('reason', reason);
  }

  const actionButton = approve
    ? document.querySelector('.verify-payment-btn')
    : form.querySelector('.reject-payment-btn');
  if (actionButton) {
    actionButton.disabled = true;
    actionButton.dataset.originalText = actionButton.textContent;
    actionButton.textContent = approve ? 'Verifying...' : 'Rejecting...';
  }

  try {
    const response = await fetch(endpoint, { method: 'POST', body: formData });
    const data = await response.json();
    if (data.success) {
      showToast(data.message || 'Payment review saved.', data.email_sent === false ? 'error' : 'success');
      if (approve) {
        updateVerifiedPaymentState(data, form);
      } else {
        setTimeout(() => window.location.reload(), 900);
      }
    } else {
      showToast(data.error || 'Could not update payment.', 'error');
      if (actionButton) {
        actionButton.disabled = false;
        actionButton.textContent = actionButton.dataset.originalText || (approve ? 'Approve payment + notify' : 'Reject');
      }
    }
  } catch (error) {
    showToast('Connection error. Please try again.', 'error');
    if (actionButton) {
      actionButton.disabled = false;
      actionButton.textContent = actionButton.dataset.originalText || (approve ? 'Approve payment + notify' : 'Reject');
    }
  }
}

function updateVerifiedPaymentState(data, form) {
  const formatMoney = value => new Intl.NumberFormat('en-US', {
    maximumFractionDigits: 0
  }).format(Number(value || 0)) + ' MMK';
  const statusLabel = String(data.booking_status || 'paid')
    .replaceAll('_', ' ')
    .replace(/\b\w/g, char => char.toUpperCase());
  const paid = Number(data.paid_amount || 0);
  const total = Number(data.total_amount || 0);
  const percent = total > 0 ? Math.min(100, Math.round((paid / total) * 100)) : 0;
  const balance = Math.max(0, total - paid);

  const statusValue = document.getElementById('booking-status-value');
  const paidValue = document.getElementById('booking-paid-value');
  const paidPercent = document.getElementById('booking-paid-percent');
  const balanceValue = document.getElementById('booking-balance-value');
  const paymentBadge = document.getElementById('payment-status-badge');
  const progressBar = document.getElementById('payment-progress-bar');
  const emailResult = document.getElementById('payment-email-result');

  if (statusValue) statusValue.textContent = statusLabel;
  if (paidValue) paidValue.textContent = formatMoney(paid);
  if (paidPercent) paidPercent.textContent = percent + '% collected';
  if (balanceValue) balanceValue.textContent = formatMoney(balance);
  if (progressBar) progressBar.style.width = percent + '%';
  if (paymentBadge) {
    paymentBadge.textContent = 'Success';
    paymentBadge.className = 'badge badge-success';
  }
  if (emailResult) {
    emailResult.textContent = data.email_sent
      ? 'Verification email sent to ' + (data.email_to || 'the customer') + '.'
      : 'Payment was verified, but the customer email could not be sent.';
  }

  document.querySelector('.verify-payment-btn')?.remove();
  form.remove();
}

const cancelForm = document.getElementById('admin-cancel-form');
if (cancelForm) {
  cancelForm.addEventListener('submit', async (event) => {
    event.preventDefault();
    if (!confirm('Are you sure you want to cancel this booking? This may not be reversible.')) return;
    const response = await fetch('<?= URLROOT ?>/admin/bookingCancel', { method: 'POST', body: new FormData(cancelForm) });
    const data = await response.json().catch(() => ({}));
    if (data.success) window.location.reload();
    else showToast(data.error || 'Could not cancel booking.', 'error');
  });
}
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
