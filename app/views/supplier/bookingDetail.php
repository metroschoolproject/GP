<?php
$booking = $booking ?? [];
$items = $items ?? [];
$eventDetails = $eventDetails ?? [];
$logs = $logs ?? [];
$bookingRef = $bookingRef ?? '';
$supplierStatus = $supplierStatus ?? 'pending';
$money = fn($v) => 'RM ' . number_format((float)$v, 0);
$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');

$dashboardTitle = 'Bookings';
$dashboardCrumb = $bookingRef ?: 'Booking detail';
$dashboardContentClass = 'bg-app-content px-6 py-6 overflow-y-auto';
$dashboardContent = function () use ($booking, $items, $eventDetails, $logs, $bookingRef, $supplierStatus, $money, $h) {
?>
<section class="space-y-5">
  <div class="flex flex-wrap items-center justify-between gap-3">
    <div>
      <a class="text-sm font-semibold text-app-primary" href="<?= URLROOT ?>/supplier/bookings">Back to bookings</a>
      <h1 class="mt-2 text-2xl font-bold text-app-text"><?= $h($bookingRef) ?></h1>
      <p class="text-sm text-app-muted"><?= $h($booking['customer_name'] ?? 'Customer') ?> · <?= $h($booking['customer_phone'] ?? '') ?></p>
    </div>
    <span class="rounded-full bg-app-soft px-3 py-1.5 text-sm font-semibold text-app-secondary"><?= $h(ucfirst($supplierStatus)) ?></span>
  </div>

  <?php if ($supplierStatus === 'pending'): ?>
    <div class="flex gap-2 rounded-xl border border-app-panel-border bg-app-panel p-4">
      <button class="booking-action rounded-lg bg-app-success px-4 py-2 text-sm font-bold text-white" data-action="accept">Accept</button>
      <button class="booking-action rounded-lg bg-app-danger px-4 py-2 text-sm font-bold text-white" data-action="decline">Decline</button>
    </div>
  <?php endif; ?>

  <div class="grid gap-5 lg:grid-cols-[1fr_320px]">
    <div class="space-y-5">
      <div class="rounded-xl border border-app-panel-border bg-app-panel">
        <div class="border-b border-app-panel-border px-5 py-4"><h2 class="font-bold text-app-text">Booked services</h2></div>
        <div class="divide-y divide-app-panel-border">
          <?php foreach ($items as $item): ?>
            <div class="flex items-center justify-between gap-4 px-5 py-4">
              <div><p class="font-semibold text-app-text"><?= $h($item['service_name'] ?? 'Service') ?></p><p class="text-sm text-app-muted"><?= $h($item['supplier_name'] ?? 'Supplier') ?></p></div>
              <div class="text-right"><p class="font-semibold text-app-text"><?= $money($item['price'] ?? 0) ?></p><p class="text-xs text-app-muted"><?= $h(ucfirst($item['status'] ?? 'pending')) ?></p></div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="rounded-xl border border-app-panel-border bg-app-panel">
        <div class="border-b border-app-panel-border px-5 py-4"><h2 class="font-bold text-app-text">Status history</h2></div>
        <div class="space-y-3 p-5">
          <?php foreach ($logs as $log): ?>
            <div class="rounded-lg bg-app-input p-3 text-sm">
              <p class="font-semibold text-app-text"><?= $h($log['new_status'] ?? '') ?></p>
              <p class="text-app-muted"><?= $h($log['note'] ?? '') ?></p>
            </div>
          <?php endforeach; ?>
          <?php if (empty($logs)): ?><p class="text-sm text-app-muted">No status history yet.</p><?php endif; ?>
        </div>
      </div>
    </div>

    <aside class="space-y-5">
      <div class="rounded-xl border border-app-panel-border bg-app-panel p-5">
        <h2 class="font-bold text-app-text">Payment</h2>
        <div class="mt-4 space-y-3 text-sm">
          <div class="flex justify-between"><span class="text-app-muted">Total</span><strong><?= $money($booking['total_amount'] ?? 0) ?></strong></div>
          <div class="flex justify-between"><span class="text-app-muted">Paid</span><strong><?= $money($booking['paid_amount'] ?? 0) ?></strong></div>
          <div class="flex justify-between"><span class="text-app-muted">Payment</span><strong><?= $h(ucfirst($booking['payment_status'] ?? '')) ?></strong></div>
        </div>
      </div>

      <div class="rounded-xl border border-app-panel-border bg-app-panel p-5">
        <h2 class="font-bold text-app-text">Event details</h2>
        <?php $firstDetail = $eventDetails[0] ?? []; ?>
        <div class="mt-4 space-y-3 text-sm">
          <div><p class="text-app-muted">Date</p><p class="font-semibold text-app-text"><?= $h($firstDetail['event_date'] ?? '-') ?></p></div>
          <div><p class="text-app-muted">Time</p><p class="font-semibold text-app-text"><?= $h(trim(($firstDetail['start_time'] ?? '') . ' - ' . ($firstDetail['end_time'] ?? ''), ' -')) ?: '-' ?></p></div>
          <div><p class="text-app-muted">Location</p><p class="font-semibold text-app-text"><?= $h($firstDetail['location'] ?? '-') ?></p></div>
          <div><p class="text-app-muted">Notes</p><p class="font-semibold text-app-text"><?= $h($firstDetail['special_requests'] ?? '-') ?></p></div>
        </div>
      </div>
    </aside>
  </div>
</section>
<script>
document.querySelectorAll('.booking-action').forEach((button) => {
  button.addEventListener('click', async () => {
    button.disabled = true;
    const formData = new FormData();
    formData.append('booking_id', '<?= (int)($booking['id'] ?? 0) ?>');
    formData.append('action', button.dataset.action);
    const response = await fetch('<?= URLROOT ?>/supplier/bookingRespond', { method: 'POST', body: formData });
    const data = await response.json().catch(() => ({}));
    if (data.success) window.location.reload();
    else {
      alert(data.error || 'Could not update booking.');
      button.disabled = false;
    }
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
