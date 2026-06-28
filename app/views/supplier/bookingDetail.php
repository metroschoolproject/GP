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
$paymentHistory = $paymentHistory ?? [];
$refund = $refund ?? null;

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
    if (in_array($s, ['rejected', 'cancelled', 'canceled', 'failed', 'cancelled_by_customer', 'supplier_rejected', 'supplier_cancellation_requested'], true))
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
    $bookingTotal, $paidTotal, $paidFraction, $paymentStatus, $daysUntil, $otherSuppliers, $bookingStatus, $bookingStatusLabel,
    $customerName, $customerEmail, $customerPhone, $customerInitial,
    $packageSchedules, $isPackage, $declineCutoffDays, $myServiceRows,
    $cancellationReason, $paymentHistory, $activeReplacement, $refund,
    $isCancelledOrReplaced
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

    // Give the supplier a bounded escape hatch: decline confirmed services while
    // the event is still at least $declineCutoffDays away. For package bookings
    // this triggers admin replacement; for non-package bookings it cancels.
    $withinDeclineWindow = $daysUntil === null || $daysUntil >= $declineCutoffDays;
    $declinableRows = $withinDeclineWindow
        ? array_values(array_filter($myServiceRows, static fn($r) => in_array($r['status'] ?? '', ['confirmed', 'in_progress'], true)))
        : [];

    // Supplier-initiated cancellation
    $supplierCancellationRequested = in_array($supplierStatus, ['supplier_cancellation_requested'], true);
    $canRequestCancellation = !$supplierCancellationRequested
        && !$needsResponse
        && in_array($bookingStatus, ['confirmed', 'paid'], true)
        && in_array($supplierStatus, ['confirmed', 'in_progress'], true);


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
<link rel="stylesheet" href="<?= URLROOT ?>/public/css/supplier-booking-detail.css?v=<?= filemtime(APPROOT . "/../public/css/supplier-booking-detail.css") ?>">
<script src="<?= URLROOT ?>/public/js/supplier-toast.js"></script>
<div class="sup-page">

  <!-- ── Header ── -->
  <header class="sup-header">
    <div class="sup-header-top">
      <div class="sup-header-left">
        <nav class="sup-breadcrumb" aria-label="Breadcrumb">
          <a href="<?= URLROOT ?>/supplier/bookings">Bookings</a>
          <span class="sup-breadcrumb-sep">/</span>
          <span class="sup-breadcrumb-current"><?= $h($bookingRef ?: 'Detail') ?></span>
        </nav>
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
                <a href="tel:<?= $h($customerPhone) ?>" class="sup-tel-link"><?= $h($customerPhone) ?></a>
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
    <?php if ($needsResponse):
        $isReplacementAssignment = !empty($activeReplacement);
        $replOrigSupplier = $isReplacementAssignment ? trim((string)($activeReplacement['old_shop_name'] ?? $activeReplacement['old_supplier_name'] ?? '')) : '';
        $replOrigService = $isReplacementAssignment ? trim((string)($activeReplacement['old_service_name'] ?? '')) : '';
    ?>
    <div class="sup-response-bar is-sticky">
      <div class="sup-response-info">
        <div class="sup-response-icon">
          <?php if ($isReplacementAssignment): ?>
            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M2 8a6 6 0 0110-4.5M14 8a6 6 0 01-10 4.5"/><path d="M12 2v3H9M4 14v-3h3"/></svg>
          <?php elseif ($bookingStatus === 'pending_supplier_response'): ?>
            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 2a4 4 0 014 4v2l1 2H3l1-2V6a4 4 0 014-4zm-1 10h2a1 1 0 01-2 0z"/></svg>
          <?php else: ?>
            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="8" cy="8" r="6"/><path d="M8 4v4l3 2"/></svg>
          <?php endif; ?>
        </div>
        <div>
          <?php if ($isReplacementAssignment): ?>
            <div class="sup-response-text">You've been assigned as a replacement</div>
            <div class="sup-response-sub">
              <?php if ($replOrigSupplier !== ''): ?>
                <?= $h($replOrigSupplier) ?><?php if ($replOrigService !== ''): ?>'s <?= $h($replOrigService) ?><?php endif; ?> is no longer available. Please accept or decline this replacement assignment.
              <?php else: ?>
                A previous supplier declined this booking. Please accept or decline this replacement assignment.
              <?php endif; ?>
            </div>
          <?php elseif ($bookingStatus === 'pending_supplier_response'): ?>
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
          <?= $isReplacementAssignment ? 'Accept Assignment' : ($bookingStatus === 'pending_supplier_response' ? 'Accept Request' : 'Accept') ?>
        </button>
        <button type="button" class="booking-action sup-btn sup-btn--decline" data-action="decline">
          <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4l8 8M12 4l-8 8"/></svg>
          <?= $isReplacementAssignment ? 'Decline Assignment' : ($bookingStatus === 'pending_supplier_response' ? 'Decline Request' : 'Decline') ?>
        </button>
        <?php if (!$isReplacementAssignment && $bookingStatus !== 'pending_supplier_response'): ?>
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
          <?php if ($bookingTotal > 0): ?>
          <div style="margin-top:6px;display:flex;gap:16px;font-size:11px;font-weight:600;color:var(--sup-text,#6d4c5b)">
            <span>Booking total: <?= $money($bookingTotal) ?></span>
            <?php if ($paidTotal > 0): ?><span>Paid: <?= $money($paidTotal) ?></span><?php endif; ?>
          </div>
          <?php endif; ?>
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

    <?php if (!empty($isCancelledOrReplaced)):
        $cancelledLabel = match ($supplierStatus) {
            'cancelled' => 'This booking has been cancelled',
            'rejected' => 'You declined this booking',
            'replaced' => 'This assignment was replaced',
            default => 'This booking is no longer active',
        };
        $cancelledSub = match ($supplierStatus) {
            'cancelled' => 'The booking was cancelled. You can still view the details below.',
            'rejected' => 'You declined this booking request. Details are preserved for your records.',
            'replaced' => 'A replacement supplier was assigned. Details are preserved for your records.',
            default => 'Actions are disabled for this booking.',
        };
    ?>
    <div class="sup-response-bar" style="background:#fef2f2;border-color:#fecaca">
      <div class="sup-response-info">
        <div class="sup-response-icon" style="color:#b94b4b">
          <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="8" cy="8" r="6"/><path d="M6 6l4 4M10 6l-4 4"/></svg>
        </div>
        <div>
          <div class="sup-response-text"><?= $h($cancelledLabel) ?></div>
          <div class="sup-response-sub"><?= $h($cancelledSub) ?></div>
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
    <?php $isDayBased = ($primarySchedule['booking_type'] ?? '') === 'fullday'; ?>
    <div class="sup-assignment-facts">
      <div class="sup-assignment-fact">
        <small><?= $isDayBased ? 'Day' : 'Date & time' ?></small>
        <strong><?= $h($formatDate($assignmentDate)) ?></strong>
        <span><?= $isDayBased ? 'Full day' : $h(trim($formatTime($assignmentStart) . ($assignmentEnd !== '' ? ' – ' . $formatTime($assignmentEnd) : '')) ?: 'Time not set') ?></span>
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
        <?php $displayPhone = $firstContactPhone !== '' ? $firstContactPhone : ($customerPhone !== '' ? $customerPhone : ''); ?>
        <?php if ($displayPhone !== ''): ?>
          <span><a href="tel:<?= $h($displayPhone) ?>" class="sup-tel-link"><?= $h($displayPhone) ?></a></span>
        <?php else: ?>
          <span>No phone provided</span>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <?php
    // ── Payment confidence strip ──
    $depositAmount = $bookingTotal * (BOOKING_DEPOSIT_PERCENT / 100);
    $depositCollected = $paidTotal >= $depositAmount;
    $fullyPaid = $paymentStatus === 'paid';

    // Find earliest successful payment date
    $firstPaymentDate = '';
    foreach ($paymentHistory as $ph) {
        if (strtolower((string)($ph['status'] ?? '')) === 'success' && !empty($ph['created_at'])) {
            $firstPaymentDate = $ph['created_at'];
            break;
        }
    }

    // Timeline step states
    $depositStepState = $depositCollected ? 'is-done' : 'is-current';
    $eventStepState = 'is-current';
    $payoutStepState = '';
    if ($fullyPaid) {
        $depositStepState = 'is-done';
        $eventStepState = ($daysUntil !== null && $daysUntil < 0) ? 'is-done' : 'is-current';
    }
    if ($daysUntil !== null && $daysUntil < 0) {
        $eventStepState = 'is-done';
        $payoutStepState = 'is-current';
    }
  ?>
  <section class="sup-payment-confidence" aria-label="Payment status">
    <div class="sup-payment-confidence-head">
      <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 1L2 4v4c0 3.5 2.5 6.5 6 7.5 3.5-1 6-4 6-7.5V4L8 1z"/><path d="M5.5 8l2 2 3.5-3.5"/></svg>
      <strong>Payment managed by <?= $h(APPNAME) ?></strong>
    </div>
    <div class="sup-payment-confidence-body">
      <div class="sup-payment-confidence-stats">
        <div class="sup-payment-confidence-stat">
          <small>Your earnings</small>
          <strong><?= $money($supplierTotal) ?></strong>
        </div>
        <div class="sup-payment-confidence-stat <?= $depositCollected ? 'sup-payment-confidence-stat--highlight' : '' ?>">
          <small>Customer has paid</small>
          <strong><?= $money($paidTotal) ?></strong>
        </div>
        <div class="sup-payment-confidence-stat">
          <small>Remaining</small>
          <strong><?= $money(max(0, $bookingTotal - $paidTotal)) ?></strong>
        </div>
      </div>

      <div class="sup-payment-confidence-bar-wrap">
        <div class="sup-payment-confidence-bar">
          <div class="sup-payment-confidence-bar-fill" style="width:<?= max(0, min(100, $paidFraction * 100)) ?>%"></div>
        </div>
        <span class="sup-payment-confidence-bar-label">
          <?php if ($fullyPaid): ?>
            Fully paid
          <?php elseif ($depositCollected): ?>
            <?= round($paidFraction * 100) ?>% deposited
          <?php else: ?>
            <?= round($paidFraction * 100) ?>% paid
          <?php endif; ?>
        </span>
      </div>

      <div class="sup-payment-confidence-timeline">
        <div class="sup-payment-confidence-step <?= $depositStepState ?>">
          <div class="sup-payment-confidence-dot">
            <?php if ($depositCollected): ?>
              <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 8l3.5 3.5L13 5"/></svg>
            <?php else: ?>
              <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="8" cy="8" r="3"/></svg>
            <?php endif; ?>
          </div>
          <div class="sup-payment-confidence-step-content">
            <div class="sup-payment-confidence-step-title">
              <?= $depositCollected ? 'Deposit collected by ' . $h(APPNAME) : 'Deposit pending' ?>
            </div>
            <div class="sup-payment-confidence-step-meta">
              <?php if ($depositCollected && $firstPaymentDate !== ''): ?>
                <?= $h($formatDate($firstPaymentDate)) ?> · <?= $money($depositAmount) ?> (<?= BOOKING_DEPOSIT_PERCENT ?>%)
              <?php elseif ($depositCollected): ?>
                <?= $money($depositAmount) ?> (<?= BOOKING_DEPOSIT_PERCENT ?>%)
              <?php else: ?>
                <?= $money($depositAmount) ?> required to confirm
              <?php endif; ?>
            </div>
          </div>
        </div>

        <div class="sup-payment-confidence-step <?= $eventStepState ?>">
          <div class="sup-payment-confidence-dot">
            <?php if ($eventStepState === 'is-done'): ?>
              <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 8l3.5 3.5L13 5"/></svg>
            <?php else: ?>
              <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="3" width="12" height="11" rx="1"/><path d="M5 2v2M11 2v2M2 7h12"/></svg>
            <?php endif; ?>
          </div>
          <div class="sup-payment-confidence-step-content">
            <div class="sup-payment-confidence-step-title">
              <?php if ($eventStepState === 'is-done'): ?>
                Event completed
              <?php else: ?>
                Event date — final payment due
              <?php endif; ?>
            </div>
            <div class="sup-payment-confidence-step-meta">
              <?= $h($formatDate($firstDate)) ?>
              <?php if ($daysUntil !== null && $daysUntil >= 0): ?>
                · <?= $daysUntil ?> day<?= $daysUntil === 1 ? '' : 's' ?> away
              <?php elseif ($daysUntil !== null): ?>
                · Event passed
              <?php endif; ?>
            </div>
          </div>
        </div>

        <div class="sup-payment-confidence-step <?= $payoutStepState ?>">
          <div class="sup-payment-confidence-dot">
            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="3" width="12" height="10" rx="1"/><path d="M2 6h12M5 9h3M5 11h2"/></svg>
          </div>
          <div class="sup-payment-confidence-step-content">
            <div class="sup-payment-confidence-step-title">Payout to you</div>
            <div class="sup-payment-confidence-step-meta">
              <?= $h(APPNAME) ?> will process your payout within 7 days after the event is completed
            </div>
          </div>
        </div>
      </div>

      <div class="sup-payment-confidence-note">
        <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="8" cy="8" r="6"/><path d="M8 5v3M8 11h.01"/></svg>
        <span><?= $h(APPNAME) ?> collects all payments from the customer and releases your earnings after the event is completed.</span>
      </div>
    </div>
  </section>

  <?php if ($refund): ?>
    <?php
      $refundStatus = (string)($refund['status'] ?? 'pending');
      $rc = match($refundStatus) {
        'pending'    => ['bg' => '#fffbeb', 'border' => '#fde68a', 'text' => '#92400e', 'icon' => '⏳', 'label' => 'Refund requested — admin will process the refund shortly.'],
        'processing' => ['bg' => '#eff6ff', 'border' => '#bfdbfe', 'text' => '#1e40af', 'icon' => '⚙️', 'label' => 'Refund in progress — admin has initiated the transfer to the customer.'],
        'completed'  => ['bg' => '#f0fdf4', 'border' => '#bbf7d0', 'text' => '#166534', 'icon' => '✅', 'label' => 'Refund completed — the customer has been refunded.'],
        'rejected'   => ['bg' => '#fef2f2', 'border' => '#fecaca', 'text' => '#991b1b', 'icon' => '❌', 'label' => 'Refund request was rejected.'],
        default      => ['bg' => '#fffbeb', 'border' => '#fde68a', 'text' => '#92400e', 'icon' => '⏳', 'label' => 'Refund pending.'],
      };
    ?>
    <div style="background:<?= $rc['bg'] ?>;border:1px solid <?= $rc['border'] ?>;border-radius:12px;padding:16px 18px;margin-top:12px;color:<?= $rc['text'] ?>">
      <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px">
        <span style="font-size:18px"><?= $rc['icon'] ?></span>
        <strong style="font-size:13px">Refund Status</strong>
        <span style="font-size:12px;opacity:0.8">— <?= $rc['label'] ?></span>
      </div>
      <div style="display:flex;flex-wrap:wrap;gap:20px;font-size:12px">
        <div>
          <span style="opacity:0.7;text-transform:uppercase;font-size:10px;font-weight:700;letter-spacing:.06em">Refund Amount</span><br>
          <span style="font-weight:700;font-size:14px"><?= $money($refund['amount'] ?? 0) ?></span>
        </div>
        <?php if (!empty($refund['policy_reason'])): ?>
        <div>
          <span style="opacity:0.7;text-transform:uppercase;font-size:10px;font-weight:700;letter-spacing:.06em">Policy Applied</span><br>
          <span><?= $h($refund['policy_reason']) ?></span>
        </div>
        <?php endif; ?>
        <?php if ($refundStatus === 'completed' && !empty($refund['completed_at'])): ?>
        <div>
          <span style="opacity:0.7;text-transform:uppercase;font-size:10px;font-weight:700;letter-spacing:.06em">Completed On</span><br>
          <span><?= date('M j, Y', strtotime($refund['completed_at'])) ?></span>
        </div>
        <?php endif; ?>
        <?php if (!empty($refund['refund_transaction_ref'])): ?>
        <div>
          <span style="opacity:0.7;text-transform:uppercase;font-size:10px;font-weight:700;letter-spacing:.06em">Transfer Reference</span><br>
          <span style="font-weight:600"><?= $h($refund['refund_transaction_ref']) ?></span>
          <?php if (!empty($refund['refund_bank_name'])): ?>
            <span style="opacity:.7"> via <?= $h($refund['refund_bank_name']) ?></span>
          <?php endif; ?>
        </div>
        <?php endif; ?>
        <?php if ($refundStatus === 'rejected' && !empty($refund['note'])): ?>
        <div>
          <span style="opacity:0.7;text-transform:uppercase;font-size:10px;font-weight:700;letter-spacing:.06em">Reason</span><br>
          <span><?= $h($refund['note']) ?></span>
        </div>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>

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

  <?php if (!empty($declinableRows) && !$needsResponse && empty($isCancelledOrReplaced)): ?>
  <details class="sup-secondary-action">
    <summary>Can’t fulfill this assignment?</summary>
    <div class="sup-selfdecline">
      <div class="sup-selfdecline-head">
        <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 1.5L1 14h14L8 1.5z"/><path d="M8 6v3M8 11.5h.01"/></svg>
        <div>
          <div class="sup-response-text">Decline a specific service</div>
          <?php if ($isPackage): ?>
            <div class="sup-response-sub">Admin will arrange another supplier. Available until <?= (int)$declineCutoffDays ?> days before the event.</div>
          <?php else: ?>
            <div class="sup-response-sub">The customer will be notified and the booking will be cancelled. Available until <?= (int)$declineCutoffDays ?> days before the event.</div>
          <?php endif; ?>
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

  <?php if ($supplierCancellationRequested): ?>
  <div class="sup-response-bar" style="background:#fffbeb;border-color:#fde68a">
    <div class="sup-response-info">
      <div class="sup-response-icon" style="color:#d97706">
        <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 1.5L1 14h14L8 1.5z"/><path d="M8 6v3M8 11.5h.01"/></svg>
      </div>
      <div>
        <div class="sup-response-text">Cancellation request submitted</div>
        <div class="sup-response-sub">Admin will review your request and process the refund. The customer has been notified.</div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <?php if ($canRequestCancellation && empty($isCancelledOrReplaced)): ?>
  <details class="sup-secondary-action">
    <summary>Need to cancel this booking?</summary>
    <div class="sup-selfdecline">
      <div class="sup-selfdecline-head">
        <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 1.5L1 14h14L8 1.5z"/><path d="M8 6v3M8 11.5h.01"/></svg>
        <div>
          <div class="sup-response-text">Request booking cancellation</div>
          <div class="sup-response-sub">The customer and admin will be notified. Admin will review and process the refund.</div>
        </div>
      </div>
      <button type="button" class="sup-btn sup-btn--decline" id="supplier-cancel-btn"
              style="margin-top:10px">
        <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4l8 8M12 4l-8 8"/></svg>
        Request Cancellation
      </button>
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
                        <?php
                          $itemRentalType = $item['rental_type'] ?? null;
                          $itemBorrowDate = $item['borrow_date'] ?? null;
                          $itemReturnDate = $item['return_date'] ?? null;
                        ?>
                        <?php if ($itemRentalType === 'borrow' && $itemBorrowDate): ?>
                          <div class="sup-svc-cat" style="color:var(--sup-accent)">
                            Borrow: <?= $h(date('M j', strtotime($itemBorrowDate))) ?> – <?= $h(date('M j, Y', strtotime($itemReturnDate))) ?>
                          </div>
                        <?php elseif ($itemRentalType === 'buy'): ?>
                          <div class="sup-svc-cat" style="color:var(--sup-accent)">Purchase</div>
                        <?php endif; ?>
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
                  'start' => ($timelineEvent['booking_type'] ?? '') === 'fullday'
                      ? 'Day'
                      : (!empty($timelineEvent['start_time']) ? date('g:i A', strtotime($timelineEvent['start_time'])) : 'TBD'),
                  'end' => ($timelineEvent['booking_type'] ?? '') === 'fullday'
                      ? ''
                      : (!empty($timelineEvent['end_time']) ? date('g:i A', strtotime($timelineEvent['end_time'])) : 'TBD'),
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
                      'time' => ($event['booking_type'] ?? '') === 'fullday'
                          ? 'Full day'
                          : ((!empty($event['start_time']) ? date('g:i A', strtotime($event['start_time'])) : 'TBD')
                              . ' – '
                              . (!empty($event['end_time']) ? date('g:i A', strtotime($event['end_time'])) : 'TBD')),
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
                  <?php if (($event['booking_type'] ?? '') === 'fullday'): ?>
                    <td style="font-weight:700" colspan="2">Day</td>
                  <?php else: ?>
                    <td style="font-weight:700"><?= $h(!empty($event['start_time']) ? date('g:i A', strtotime($event['start_time'])) : 'TBD') ?></td>
                    <td style="font-weight:700"><?= $h(!empty($event['end_time']) ? date('g:i A', strtotime($event['end_time'])) : '—') ?></td>
                  <?php endif; ?>
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
                <a href="tel:<?= $h($firstContactPhone) ?>" class="sup-tel-link"><?= $h($firstContactPhone) ?></a>
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
            <div class="sup-contact-row"><a href="tel:<?= $h($customerPhone) ?>" class="sup-tel-link"><?= $h($customerPhone) ?></a></div>
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
            <textarea id="dec-reason" name="reason" rows="3" required minlength="10" maxlength="500" placeholder="e.g., Already booked for this date, schedule conflict, equipment unavailable" class="sup-textarea"></textarea>
            <span class="sup-char-count" id="dec-char-count">0 / 500</span>
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

  <!-- Supplier Request Cancellation Modal -->
  <div id="supplier-cancel-modal" class="sup-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="supplier-cancel-modal-title">
    <div class="sup-modal">
      <div class="sup-modal-head">
        <h2 id="supplier-cancel-modal-title" class="sup-modal-title">Request Booking Cancellation</h2>
        <button type="button" class="supplier-cancel-close sup-modal-close" aria-label="Close">
          <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 4l8 8M12 4l-8 8"/></svg>
        </button>
      </div>
      <form id="supplier-cancel-form">
        <div class="sup-modal-body">
          <p class="sup-response-sub" style="margin-bottom:14px">
            The customer and admin will be notified of your cancellation request. Admin will review and process any applicable refund.
          </p>
          <div class="sup-field" style="margin-bottom:0">
            <label class="sup-label" for="supplier-cancel-reason">Reason for cancellation <span style="color:var(--sup-danger-text)">*</span></label>
            <textarea id="supplier-cancel-reason" name="reason" rows="3" required minlength="10" maxlength="500" placeholder="e.g., Unforeseen circumstances, double booking, unable to fulfill" class="sup-textarea"></textarea>
            <span class="sup-char-count" id="supplier-cancel-char-count">0 / 500</span>
          </div>
        </div>
        <div class="sup-modal-foot">
          <button type="button" class="supplier-cancel-close sup-btn sup-btn--ghost">Cancel</button>
          <button type="submit" class="sup-btn sup-btn--decline">Submit cancellation request</button>
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
          event.start === 'Day' ? 'Full day' : ((event.start || 'TBD') + ' – ' + (event.end || 'TBD')),
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
          supToastSuccess(data.message || 'Reschedule proposal sent!');
          modal.classList.remove('is-open');
          form.reset();
          setTimeout(function() { window.location.reload(); }, 1200);
        } else {
          supToastError(data.error || 'Could not send reschedule proposal.');
        }
      } catch (err) {
        supToastError('Network error. Please try again.');
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
        if (data.success) {
          supToastSuccess(button.dataset.action === 'accept' ? 'Booking accepted!' : 'Booking declined.');
          setTimeout(function() { window.location.reload(); }, 1200);
          return;
        }
        supToastError(data.error || 'Could not update booking.');
      } catch (err) {
        supToastError('Network error. Please try again.');
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
    /* Character counter for decline textarea */
    var decReason = document.getElementById('dec-reason');
    var decCharCount = document.getElementById('dec-char-count');
    if (decReason && decCharCount) {
      decReason.addEventListener('input', function() {
        var len = decReason.value.length;
        decCharCount.textContent = len + ' / 500';
        decCharCount.classList.toggle('is-over', len > 500);
      });
    }

    declineBtns.forEach(function(btn){
      btn.addEventListener('click', function(){
        declineTarget = btn.dataset.bsid || '';
        if (declineTitle) declineTitle.textContent = 'Decline “' + (btn.dataset.svc || 'this service') + '”';
        document.getElementById('dec-reason').value = '';
        if (decCharCount) decCharCount.textContent = '0 / 500';
        declineModal.classList.add('is-open');
        if (decReason) decReason.focus();
      });
    });
    document.querySelectorAll('.decline-close').forEach(function(b){
      b.addEventListener('click', function(){ declineModal.classList.remove('is-open'); });
    });
    declineModal.addEventListener('click', function(e){ if (e.target === declineModal) declineModal.classList.remove('is-open'); });

    declineForm.addEventListener('submit', async function(e){
      e.preventDefault();
      var reason = (document.getElementById('dec-reason').value || '').trim();
      if (!reason || reason.length < 10) { supToastWarning('Please provide a reason (at least 10 characters).'); return; }
      if (!declineTarget) { supToastWarning('No service selected.'); return; }

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
        if (data.success) {
          supToastSuccess('Service declined. Admin will arrange a replacement.');
          setTimeout(function() { window.location.reload(); }, 1200);
          return;
        }
        supToastError(data.error || 'Could not submit your decline.');
      } catch (err) {
        supToastError('Network error. Please try again.');
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
        if (!reason) { supToastWarning('Please enter a reason.'); return; }
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
      if (data.success) {
        supToastSuccess(action === 'approve' ? 'Cancellation approved.' : 'Cancellation declined.');
        setTimeout(function() { window.location.reload(); }, 1200);
        return;
      }
      supToastError(data.error || 'Could not process your response.');
    } catch (err) {
      supToastError('Network error. Please try again.');
    }
  }

  /* ── Supplier request cancellation ── */
  var supplierCancelBtn   = document.getElementById('supplier-cancel-btn');
  var supplierCancelModal = document.getElementById('supplier-cancel-modal');
  var supplierCancelForm  = document.getElementById('supplier-cancel-form');

  if (supplierCancelBtn && supplierCancelModal && supplierCancelForm) {
    var scReason = document.getElementById('supplier-cancel-reason');
    var scCharCount = document.getElementById('supplier-cancel-char-count');

    if (scReason && scCharCount) {
      scReason.addEventListener('input', function() {
        scCharCount.textContent = scReason.value.length + ' / 500';
      });
    }

    supplierCancelBtn.addEventListener('click', function() {
      scReason.value = '';
      if (scCharCount) scCharCount.textContent = '0 / 500';
      supplierCancelModal.classList.add('is-open');
      scReason.focus();
    });

    document.querySelectorAll('.supplier-cancel-close').forEach(function(b) {
      b.addEventListener('click', function() { supplierCancelModal.classList.remove('is-open'); });
    });
    supplierCancelModal.addEventListener('click', function(e) {
      if (e.target === supplierCancelModal) supplierCancelModal.classList.remove('is-open');
    });

    supplierCancelForm.addEventListener('submit', async function(e) {
      e.preventDefault();
      var reason = (scReason.value || '').trim();
      if (!reason || reason.length < 10) {
        supToastWarning('Please provide a reason (at least 10 characters).');
        return;
      }

      var submitBtn = supplierCancelForm.querySelector('button[type="submit"]');
      var original = submitBtn.innerHTML;
      submitBtn.disabled = true;
      submitBtn.innerHTML = 'Submitting…';

      var formData = new FormData();
      formData.append('booking_id', '<?= (int)($booking['id'] ?? 0) ?>');
      formData.append('reason', reason);
      formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]')?.content || '');

      try {
        var resp = await fetch('<?= URLROOT ?>/supplier/bookingRequestCancellation', { method: 'POST', body: formData });
        var data = await resp.json().catch(function(){ return {}; });
        if (data.success) {
          supplierCancelModal.classList.remove('is-open');
          supToastSuccess('Cancellation request submitted. Admin will review.');
          setTimeout(function() { window.location.reload(); }, 1500);
          return;
        }
        supToastError(data.error || 'Could not submit cancellation request.');
      } catch (err) {
        supToastError('Network error. Please try again.');
      }
      submitBtn.disabled = false;
      submitBtn.innerHTML = original;
    });
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
