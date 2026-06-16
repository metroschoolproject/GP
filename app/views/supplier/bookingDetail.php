<?php
$booking = $booking ?? [];
$items = $items ?? [];
$eventDetails = $eventDetails ?? [];
$logs = $logs ?? [];
$bookingRef = $bookingRef ?? '';
$supplierStatus = strtolower($supplierStatus ?? 'pending');

$money = fn($v) => 'RM ' . number_format((float)$v, 0);
$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$formatDate = function ($value, string $fallback = '-') {
    if (empty($value)) {
        return $fallback;
    }
    $time = strtotime((string)$value);
    return $time ? date('d M Y', $time) : $fallback;
};
$formatDateTime = function ($value, string $fallback = '-') {
    if (empty($value)) {
        return $fallback;
    }
    $time = strtotime((string)$value);
    return $time ? date('d M Y, h:i A', $time) : $fallback;
};
$formatTime = function ($value) {
    if (empty($value)) {
        return '';
    }
    $time = strtotime((string)$value);
    return $time ? date('h:i A', $time) : (string)$value;
};

$statusBadgeClass = function (string $status): string {
    $s = strtolower($status);
    if (in_array($s, ['confirmed', 'completed', 'accepted', 'paid', 'success', 'supplier_confirmed'], true)) {
        return 'bg-app-success/10 text-app-success';
    }
    if (in_array($s, ['pending', 'unpaid', 'partial'], true)) {
        return 'bg-app-warning/10 text-app-warning';
    }
    if (in_array($s, ['rejected', 'cancelled', 'canceled', 'failed', 'cancelled_by_customer', 'supplier_rejected'], true)) {
        return 'bg-app-danger/10 text-app-danger';
    }
    return 'bg-app-soft text-app-secondary';
};

$statusIcon = function (string $status): string {
    $s = strtolower($status);
    if (in_array($s, ['confirmed', 'completed', 'accepted', 'paid', 'success', 'supplier_confirmed'], true)) {
        return 'check-circle';
    }
    if (in_array($s, ['rejected', 'cancelled', 'canceled', 'failed', 'supplier_rejected'], true)) {
        return 'x-circle';
    }
    return 'clock';
};

$statusDotClass = function (string $status): string {
    $s = strtolower($status);
    if (in_array($s, ['confirmed', 'completed', 'accepted', 'paid', 'success', 'supplier_confirmed'], true)) {
        return 'bg-app-success';
    }
    if (in_array($s, ['rejected', 'cancelled', 'canceled', 'failed', 'supplier_rejected'], true)) {
        return 'bg-app-danger';
    }
    if (in_array($s, ['pending', 'unpaid', 'partial'], true)) {
        return 'bg-app-warning';
    }
    return 'bg-app-muted';
};

$dashboardTitle = 'Bookings';
$dashboardCrumb = $bookingRef ?: 'Booking detail';
$dashboardContentClass = 'bg-app-content px-6 py-6 overflow-y-auto';
$dashboardContent = function () use (
    $booking,
    $items,
    $eventDetails,
    $logs,
    $bookingRef,
    $supplierStatus,
    $money,
    $h,
    $formatDate,
    $formatDateTime,
    $formatTime,
    $statusBadgeClass,
    $statusIcon,
    $statusDotClass
) {
    $customerName = trim((string)($booking['customer_name'] ?? 'Customer'));
    $customerEmail = trim((string)($booking['customer_email'] ?? ''));
    $customerPhone = trim((string)($booking['customer_phone'] ?? ''));
    $customerInitial = strtoupper(substr($customerName !== '' ? $customerName : 'C', 0, 1));
    $firstDetail = $eventDetails[0] ?? [];
    $startTime = $formatTime($firstDetail['start_time'] ?? '');
    $endTime = $formatTime($firstDetail['end_time'] ?? '');
    $eventTime = trim($startTime . ' - ' . $endTime, ' -');
    $totalAmount = (float)($booking['total_amount'] ?? 0);
    $paidAmount = (float)($booking['paid_amount'] ?? 0);
    $remainingAmount = max(0, $totalAmount - $paidAmount);
    $paymentStatus = strtolower((string)($booking['payment_status'] ?? 'pending'));
    $bookingStatus = strtolower((string)($booking['status'] ?? $supplierStatus));
?>
<style>
  .bk-detail-card {
    transition: transform 140ms ease, box-shadow 140ms ease, border-color 140ms ease;
  }
  .bk-detail-card:hover {
    transform: translateY(-1px);
    box-shadow: 0 8px 25px rgba(34,24,19,0.07);
  }
  .bk-detail-row {
    transition: background 120ms ease;
  }
  .bk-detail-row:hover {
    background: var(--color-app-panel, #fff);
  }
  .bk-action-btn {
    transition: transform 140ms ease, box-shadow 140ms ease, opacity 140ms ease;
  }
  .bk-action-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 8px 18px rgba(34,24,19,0.14);
  }
  .bk-action-btn:disabled {
    cursor: wait;
    opacity: 0.65;
    transform: none;
  }
  .bk-timeline-line {
    min-height: 2.75rem;
  }
  @media (max-width: 1024px) {
    .bk-detail-shell {
      grid-template-columns: 1fr !important;
    }
  }
  @media (max-width: 640px) {
    .bk-summary-grid {
      grid-template-columns: 1fr !important;
    }
    .bk-detail-table th,
    .bk-detail-table td {
      padding-left: 0.75rem !important;
      padding-right: 0.75rem !important;
    }
  }
</style>

<section class="mx-auto max-w-[1600px] space-y-5 font-ui text-[13px] text-app-text antialiased">
  <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
    <div class="min-w-0">
      <a href="<?= URLROOT ?>/supplier/bookings"
         class="inline-flex items-center gap-1.5 rounded-full border border-app-border bg-app-input px-3 py-1.5 text-xs font-semibold text-app-secondary transition hover:border-app-primary hover:text-app-primary">
        <i data-lucide="arrow-left" class="h-3.5 w-3.5"></i>
        Back to bookings
      </a>
      <div class="mt-4 flex flex-wrap items-center gap-3">
        <h1 class="text-2xl font-bold tracking-tight text-app-text"><?= $h($bookingRef ?: 'Booking detail') ?></h1>
        <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-semibold <?= $statusBadgeClass($supplierStatus) ?>">
          <i data-lucide="<?= $h($statusIcon($supplierStatus)) ?>" class="h-3.5 w-3.5"></i>
          <?= $h(ucfirst($supplierStatus)) ?>
        </span>
      </div>
      <div class="mt-2 flex flex-wrap items-center gap-3 text-sm text-app-muted">
        <span class="inline-flex items-center gap-2">
          <span class="flex h-7 w-7 items-center justify-center rounded-full bg-app-soft text-[10px] font-semibold text-app-secondary"><?= $h($customerInitial) ?></span>
          <?= $h($customerName ?: 'Customer') ?>
        </span>
        <?php if ($customerPhone !== ''): ?>
          <span class="inline-flex items-center gap-1.5"><i data-lucide="phone" class="h-3.5 w-3.5"></i><?= $h($customerPhone) ?></span>
        <?php endif; ?>
        <?php if ($customerEmail !== ''): ?>
          <span class="inline-flex items-center gap-1.5"><i data-lucide="mail" class="h-3.5 w-3.5"></i><?= $h($customerEmail) ?></span>
        <?php endif; ?>
      </div>
    </div>

    <?php if ($supplierStatus === 'pending'): ?>
      <div class="bk-detail-card w-full rounded-card border border-app-border bg-app-input p-4 shadow-sm xl:max-w-xl">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-app-warning/10 text-app-warning">
              <i data-lucide="bell-ring" class="h-5 w-5"></i>
            </div>
            <div>
              <p class="font-bold text-app-text">Response needed</p>
              <p class="text-xs text-app-muted">Accept this booking or decline it from your queue.</p>
            </div>
          </div>
          <div class="flex gap-2">
            <button type="button" class="booking-action bk-action-btn inline-flex items-center gap-1.5 rounded-lg bg-app-success px-4 py-2 text-xs font-bold text-white" data-action="accept">
              <i data-lucide="check" class="h-3.5 w-3.5"></i>
              Accept
            </button>
            <button type="button" class="booking-action bk-action-btn inline-flex items-center gap-1.5 rounded-lg bg-app-danger px-4 py-2 text-xs font-bold text-white" data-action="decline">
              <i data-lucide="x" class="h-3.5 w-3.5"></i>
              Decline
            </button>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <div class="bk-summary-grid grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
    <div class="bk-detail-card rounded-card border border-app-border bg-app-input p-4 shadow-sm">
      <div class="mb-3 flex h-8 w-8 items-center justify-center rounded-lg bg-app-soft text-app-accent">
        <i data-lucide="calendar-days" class="h-4 w-4"></i>
      </div>
      <p class="text-xs font-semibold uppercase tracking-wide text-app-muted">Event date</p>
      <p class="mt-1 text-xl font-bold text-app-text"><?= $h($formatDate($firstDetail['event_date'] ?? '')) ?></p>
      <p class="mt-0.5 text-[11px] text-app-muted"><?= $h($eventTime ?: 'Time not set') ?></p>
    </div>
    <div class="bk-detail-card rounded-card border border-app-border bg-app-input p-4 shadow-sm">
      <div class="mb-3 flex h-8 w-8 items-center justify-center rounded-lg bg-app-success/10 text-app-success">
        <i data-lucide="wallet" class="h-4 w-4"></i>
      </div>
      <p class="text-xs font-semibold uppercase tracking-wide text-app-muted">Total amount</p>
      <p class="mt-1 text-xl font-bold text-app-text"><?= $money($totalAmount) ?></p>
      <p class="mt-0.5 text-[11px] text-app-muted"><?= $money($paidAmount) ?> paid</p>
    </div>
    <div class="bk-detail-card rounded-card border border-app-border bg-app-input p-4 shadow-sm">
      <div class="mb-3 flex h-8 w-8 items-center justify-center rounded-lg bg-app-warning/10 text-app-warning">
        <i data-lucide="package" class="h-4 w-4"></i>
      </div>
      <p class="text-xs font-semibold uppercase tracking-wide text-app-muted">Booked services</p>
      <p class="mt-1 text-xl font-bold text-app-text"><?= count($items) ?></p>
      <p class="mt-0.5 text-[11px] text-app-muted">Service item(s)</p>
    </div>
    <div class="bk-detail-card rounded-card border border-app-border bg-app-text p-4 text-app-white shadow-xl">
      <div class="mb-3 flex h-8 w-8 items-center justify-center rounded-lg bg-app-white/10 text-app-white">
        <i data-lucide="<?= $h($statusIcon($paymentStatus)) ?>" class="h-4 w-4"></i>
      </div>
      <p class="text-xs font-semibold uppercase tracking-wide text-app-white/70">Payment status</p>
      <p class="mt-1 text-xl font-bold text-app-white"><?= $h(ucfirst($paymentStatus ?: 'pending')) ?></p>
      <p class="mt-0.5 text-[11px] text-app-white/55"><?= $money($remainingAmount) ?> remaining</p>
    </div>
  </div>

  <div class="bk-detail-shell grid gap-5 xl:grid-cols-[minmax(0,1fr)_360px]">
    <div class="space-y-5">
      <div class="overflow-hidden rounded-card border border-app-border bg-app-input shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-app-border bg-app-soft/60 px-5 py-4">
          <div>
            <h2 class="text-base font-bold text-app-text">Services in this booking</h2>
            <p class="mt-0.5 text-xs text-app-muted">Line items, venue assignment, and supplier-side status.</p>
          </div>
          <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-semibold <?= $statusBadgeClass($bookingStatus) ?>">
            <i data-lucide="<?= $h($statusIcon($bookingStatus)) ?>" class="h-3.5 w-3.5"></i>
            Booking <?= $h(ucfirst($bookingStatus ?: 'pending')) ?>
          </span>
        </div>

        <?php if (empty($items)): ?>
          <div class="flex flex-col items-center justify-center px-6 py-14 text-center">
            <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-app-soft text-app-muted">
              <i data-lucide="package-x" class="h-6 w-6"></i>
            </div>
            <p class="text-base font-semibold text-app-text">No service items found</p>
            <p class="mt-1 max-w-sm text-sm text-app-muted">This booking does not currently have supplier service lines attached.</p>
          </div>
        <?php else: ?>
          <div class="overflow-x-auto">
            <table class="bk-detail-table min-w-full text-left text-sm">
              <thead>
                <tr class="border-b border-app-border bg-app-soft/40">
                  <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-app-muted">Service</th>
                  <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-app-muted">Venue</th>
                  <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-app-muted">Price</th>
                  <th class="px-5 py-3 text-[10px] font-semibold uppercase tracking-wider text-app-muted">Status</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-app-border">
                <?php foreach ($items as $item):
                  $itemStatus = strtolower((string)($item['status'] ?? 'pending'));
                  $venueText = trim((string)($item['venue_room_name'] ?? ''));
                  $venueName = trim((string)($item['venue_name'] ?? ''));
                  if ($venueName !== '') {
                      $venueText .= ($venueText !== '' ? ' - ' : '') . $venueName;
                  }
                ?>
                  <tr class="bk-detail-row">
                    <td class="px-5 py-4">
                      <div class="flex items-center gap-3">
                        <?php if (!empty($item['thumbnail_url'])): ?>
                          <img src="<?= $h($item['thumbnail_url']) ?>" alt="" class="h-11 w-11 rounded-lg object-cover">
                        <?php else: ?>
                          <span class="flex h-11 w-11 items-center justify-center rounded-lg bg-app-soft text-app-secondary">
                            <i data-lucide="image" class="h-4 w-4"></i>
                          </span>
                        <?php endif; ?>
                        <div class="min-w-0">
                          <p class="truncate font-semibold text-app-text"><?= $h($item['service_name'] ?? 'Service') ?></p>
                          <p class="mt-0.5 text-xs text-app-muted"><?= $h($item['category_name'] ?? $item['supplier_name'] ?? 'Supplier service') ?></p>
                        </div>
                      </div>
                    </td>
                    <td class="px-5 py-4 text-app-secondary"><?= $h($venueText !== '' ? $venueText : '-') ?></td>
                    <td class="px-5 py-4 font-semibold text-app-text"><?= $money($item['price'] ?? 0) ?></td>
                    <td class="px-5 py-4">
                      <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[11px] font-semibold <?= $statusBadgeClass($itemStatus) ?>">
                        <i data-lucide="<?= $h($statusIcon($itemStatus)) ?>" class="h-3 w-3"></i>
                        <?= $h(ucfirst($itemStatus ?: 'pending')) ?>
                      </span>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>

      <div class="bk-detail-card rounded-card border border-app-border bg-app-input p-5 shadow-sm">
        <div class="mb-4 flex items-center justify-between gap-3">
          <div>
            <h2 class="text-base font-bold text-app-text">Status history</h2>
            <p class="mt-0.5 text-xs text-app-muted">Recent booking updates and supplier responses.</p>
          </div>
          <span class="rounded-full bg-app-soft px-2.5 py-1 text-[11px] font-semibold text-app-secondary"><?= count($logs) ?></span>
        </div>

        <?php if (empty($logs)): ?>
          <div class="flex flex-col items-center justify-center rounded-card border border-dashed border-app-border px-6 py-10 text-center">
            <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-app-soft text-app-muted">
              <i data-lucide="history" class="h-5 w-5"></i>
            </div>
            <p class="font-semibold text-app-text">No history yet</p>
            <p class="mt-1 text-sm text-app-muted">Updates will appear once this booking changes status.</p>
          </div>
        <?php else: ?>
          <div class="space-y-0">
            <?php foreach ($logs as $index => $log):
              $logStatus = strtolower((string)($log['new_status'] ?? 'update'));
              $isLast = $index === count($logs) - 1;
            ?>
              <div class="grid grid-cols-[1rem_minmax(0,1fr)] gap-3">
                <div class="flex flex-col items-center">
                  <span class="mt-1 h-2.5 w-2.5 rounded-full <?= $statusDotClass($logStatus) ?>"></span>
                  <?php if (!$isLast): ?>
                    <span class="bk-timeline-line mt-1 w-px bg-app-border"></span>
                  <?php endif; ?>
                </div>
                <div class="<?= $isLast ? '' : 'pb-4' ?>">
                  <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[11px] font-semibold <?= $statusBadgeClass($logStatus) ?>">
                      <i data-lucide="<?= $h($statusIcon($logStatus)) ?>" class="h-3 w-3"></i>
                      <?= $h(ucfirst(str_replace('_', ' ', $logStatus))) ?>
                    </span>
                    <span class="text-xs text-app-muted"><?= $h($formatDateTime($log['created_at'] ?? '')) ?></span>
                  </div>
                  <?php if (!empty(trim((string)($log['note'] ?? '')))): ?>
                    <p class="mt-2 rounded-lg bg-app-panel px-3 py-2 text-sm leading-relaxed text-app-secondary"><?= $h($log['note']) ?></p>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <aside class="space-y-5">
      <div class="bk-detail-card rounded-card border border-app-border bg-app-input p-5 shadow-sm">
        <div class="mb-4 flex items-center gap-3">
          <span class="flex h-11 w-11 items-center justify-center rounded-full bg-app-soft text-sm font-bold text-app-secondary"><?= $h($customerInitial) ?></span>
          <div class="min-w-0">
            <h2 class="truncate text-base font-bold text-app-text"><?= $h($customerName ?: 'Customer') ?></h2>
            <p class="text-xs text-app-muted">Customer contact</p>
          </div>
        </div>
        <div class="space-y-3 text-sm">
          <div class="flex items-start gap-3">
            <i data-lucide="phone" class="mt-0.5 h-4 w-4 shrink-0 text-app-muted"></i>
            <span class="text-app-secondary"><?= $h($customerPhone !== '' ? $customerPhone : '-') ?></span>
          </div>
          <div class="flex items-start gap-3">
            <i data-lucide="mail" class="mt-0.5 h-4 w-4 shrink-0 text-app-muted"></i>
            <span class="break-all text-app-secondary"><?= $h($customerEmail !== '' ? $customerEmail : '-') ?></span>
          </div>
        </div>
      </div>

      <div class="bk-detail-card rounded-card border border-app-border bg-app-input p-5 shadow-sm">
        <div class="mb-4 flex items-center gap-2.5">
          <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-app-soft text-app-accent">
            <i data-lucide="map-pinned" class="h-4 w-4"></i>
          </div>
          <h2 class="text-base font-bold text-app-text">Event details</h2>
        </div>
        <div class="space-y-4 text-sm">
          <div>
            <p class="text-[10px] font-semibold uppercase tracking-wider text-app-muted">Date and time</p>
            <p class="mt-1 font-semibold text-app-text"><?= $h($formatDate($firstDetail['event_date'] ?? '')) ?></p>
            <p class="text-app-secondary"><?= $h($eventTime ?: '-') ?></p>
          </div>
          <div>
            <p class="text-[10px] font-semibold uppercase tracking-wider text-app-muted">Location</p>
            <p class="mt-1 leading-relaxed text-app-secondary"><?= $h($firstDetail['location'] ?? '-') ?></p>
          </div>
          <div>
            <p class="text-[10px] font-semibold uppercase tracking-wider text-app-muted">Notes</p>
            <p class="mt-1 leading-relaxed text-app-secondary"><?= $h($firstDetail['special_requests'] ?? '-') ?></p>
          </div>
        </div>
      </div>

      <div class="bk-detail-card rounded-card border border-app-border bg-app-input p-5 shadow-sm">
        <div class="mb-4 flex items-center gap-2.5">
          <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-app-success/10 text-app-success">
            <i data-lucide="receipt" class="h-4 w-4"></i>
          </div>
          <h2 class="text-base font-bold text-app-text">Payment</h2>
        </div>
        <div class="space-y-3 text-sm">
          <div class="flex items-center justify-between gap-4">
            <span class="text-app-muted">Total</span>
            <strong class="text-app-text"><?= $money($totalAmount) ?></strong>
          </div>
          <div class="flex items-center justify-between gap-4">
            <span class="text-app-muted">Paid</span>
            <strong class="text-app-success"><?= $money($paidAmount) ?></strong>
          </div>
          <div class="flex items-center justify-between gap-4">
            <span class="text-app-muted">Remaining</span>
            <strong class="<?= $remainingAmount > 0 ? 'text-app-danger' : 'text-app-success' ?>"><?= $money($remainingAmount) ?></strong>
          </div>
          <div class="border-t border-app-border pt-3">
            <div class="flex items-center justify-between gap-4">
              <span class="text-app-muted">Status</span>
              <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[11px] font-semibold <?= $statusBadgeClass($paymentStatus) ?>">
                <i data-lucide="<?= $h($statusIcon($paymentStatus)) ?>" class="h-3 w-3"></i>
                <?= $h(ucfirst($paymentStatus ?: 'pending')) ?>
              </span>
            </div>
          </div>
        </div>
      </div>

      <div class="bk-detail-card rounded-card border border-app-border bg-app-input p-5 shadow-sm">
        <div class="mb-4 flex items-center gap-2.5">
          <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-app-soft text-app-secondary">
            <i data-lucide="info" class="h-4 w-4"></i>
          </div>
          <h2 class="text-base font-bold text-app-text">Booking info</h2>
        </div>
        <div class="space-y-3 text-sm">
          <div class="flex items-center justify-between gap-4">
            <span class="text-app-muted">Reference</span>
            <span class="font-mono text-xs font-semibold text-app-text"><?= $h($bookingRef ?: '-') ?></span>
          </div>
          <div class="flex items-center justify-between gap-4">
            <span class="text-app-muted">Created</span>
            <span class="font-semibold text-app-text"><?= $h($formatDate($booking['created_at'] ?? '')) ?></span>
          </div>
          <div class="flex items-center justify-between gap-4">
            <span class="text-app-muted">Items</span>
            <span class="font-semibold text-app-text"><?= count($items) ?></span>
          </div>
        </div>
      </div>
    </aside>
  </div>
</section>

<script>
document.querySelectorAll('.booking-action').forEach((button) => {
  button.addEventListener('click', async () => {
    button.disabled = true;
    const originalHTML = button.innerHTML;
    button.innerHTML = '<i data-lucide="loader-circle" class="h-3.5 w-3.5 animate-spin"></i> Updating';
    lucide.createIcons();

    const formData = new FormData();
    formData.append('booking_id', '<?= (int)($booking['id'] ?? 0) ?>');
    formData.append('action', button.dataset.action);

    try {
      const response = await fetch('<?= URLROOT ?>/supplier/bookingRespond', {
        method: 'POST',
        body: formData
      });
      const data = await response.json().catch(() => ({}));
      if (data.success) {
        window.location.reload();
        return;
      }
      alert(data.error || 'Could not update booking.');
    } catch (error) {
      alert('Network error. Please try again.');
    }

    button.disabled = false;
    button.innerHTML = originalHTML;
    lucide.createIcons();
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
