<?php
$booking = $booking ?? [];
$items = $items ?? [];
$suppliers = $suppliers ?? [];
$eventDetails = $eventDetails ?? [];
$logs = $logs ?? [];
$payments = $payments ?? [];
$vouchers = $vouchers ?? [];
$bookingRef = $bookingRef ?? '';
$money = fn($v) => 'RM ' . number_format((float)$v, 0);
$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');

$dashboardTitle = 'Bookings';
$dashboardCrumb = $bookingRef ?: 'Booking detail';
$dashboardContentClass = 'bg-app-content px-6 py-6 overflow-y-auto';
$dashboardContent = function () use ($booking, $items, $suppliers, $eventDetails, $logs, $payments, $vouchers, $bookingRef, $money, $h) {
?>
<section class="space-y-5">
  <div class="flex flex-wrap items-center justify-between gap-3">
    <div>
      <a class="text-sm font-semibold text-app-primary" href="<?= URLROOT ?>/admin/bookings">Back to bookings</a>
      <h1 class="mt-2 text-2xl font-bold text-app-text"><?= $h($bookingRef) ?></h1>
      <p class="text-sm text-app-muted"><?= $h($booking['customer_name'] ?? 'Customer') ?> · <?= $h($booking['customer_email'] ?? '') ?></p>
    </div>
    <span class="rounded-full bg-app-soft px-3 py-1.5 text-sm font-semibold text-app-secondary"><?= $h(ucwords(str_replace('_', ' ', $booking['status'] ?? ''))) ?></span>
  </div>

  <div class="grid gap-5 lg:grid-cols-[1fr_340px]">
    <div class="space-y-5">
      <div class="rounded-xl border border-app-panel-border bg-app-panel">
        <div class="border-b border-app-panel-border px-5 py-4"><h2 class="font-bold text-app-text">Services</h2></div>
        <div class="divide-y divide-app-panel-border">
          <?php foreach ($items as $item): ?>
            <div class="flex items-center justify-between gap-4 px-5 py-4">
              <div><p class="font-semibold text-app-text"><?= $h($item['service_name'] ?? 'Service') ?></p><p class="text-sm text-app-muted"><?= $h($item['supplier_name'] ?? 'Supplier') ?></p></div>
              <div class="text-right"><p class="font-semibold text-app-text"><?= $money($item['price'] ?? 0) ?></p><p class="text-xs text-app-muted"><?= $h(ucfirst($item['status'] ?? 'pending')) ?></p></div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="grid gap-5 md:grid-cols-2">
        <div class="rounded-xl border border-app-panel-border bg-app-panel p-5">
          <h2 class="font-bold text-app-text">Suppliers</h2>
          <div class="mt-4 space-y-3">
            <?php foreach ($suppliers as $supplier): ?>
              <div class="rounded-lg bg-app-input p-3 text-sm"><p class="font-semibold text-app-text"><?= $h($supplier['shop_name'] ?? 'Supplier') ?></p><p class="text-app-muted"><?= $h(ucfirst($supplier['status'] ?? 'pending')) ?></p></div>
            <?php endforeach; ?>
            <?php if (empty($suppliers)): ?><p class="text-sm text-app-muted">No supplier assigned.</p><?php endif; ?>
          </div>
        </div>

        <div class="rounded-xl border border-app-panel-border bg-app-panel p-5">
          <h2 class="font-bold text-app-text">Payments</h2>
          <div class="mt-4 space-y-3">
            <?php foreach ($payments as $payment): ?>
              <div class="rounded-lg bg-app-input p-3 text-sm"><p class="font-semibold text-app-text"><?= $money($payment['amount'] ?? 0) ?> · <?= $h($payment['type'] ?? '') ?></p><p class="text-app-muted"><?= $h($payment['status'] ?? '') ?> <?= $h($payment['transaction_ref'] ?? '') ?></p></div>
            <?php endforeach; ?>
            <?php if (empty($payments)): ?><p class="text-sm text-app-muted">No payment records.</p><?php endif; ?>
          </div>
        </div>
      </div>

      <div class="rounded-xl border border-app-panel-border bg-app-panel p-5">
        <h2 class="font-bold text-app-text">Status history</h2>
        <div class="mt-4 space-y-3">
          <?php foreach ($logs as $log): ?>
            <div class="rounded-lg bg-app-input p-3 text-sm"><p class="font-semibold text-app-text"><?= $h($log['new_status'] ?? '') ?></p><p class="text-app-muted"><?= $h($log['note'] ?? '') ?></p></div>
          <?php endforeach; ?>
          <?php if (empty($logs)): ?><p class="text-sm text-app-muted">No status history yet.</p><?php endif; ?>
        </div>
      </div>
    </div>

    <aside class="space-y-5">
      <div class="rounded-xl border border-app-panel-border bg-app-panel p-5">
        <h2 class="font-bold text-app-text">Booking summary</h2>
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
          <div><p class="text-app-muted">Location</p><p class="font-semibold text-app-text"><?= $h($firstDetail['location'] ?? '-') ?></p></div>
          <div><p class="text-app-muted">Contact</p><p class="font-semibold text-app-text"><?= $h($firstDetail['contact_name'] ?? ($booking['customer_name'] ?? '-')) ?> · <?= $h($firstDetail['contact_phone'] ?? ($booking['customer_phone'] ?? '')) ?></p></div>
        </div>
      </div>

      <div class="rounded-xl border border-app-panel-border bg-app-panel p-5">
        <h2 class="font-bold text-app-text">Vouchers</h2>
        <div class="mt-4 space-y-2 text-sm">
          <?php foreach ($vouchers as $voucher): ?><p class="rounded-lg bg-app-input p-2 font-mono text-xs text-app-secondary"><?= $h($voucher['voucher_number'] ?? '') ?></p><?php endforeach; ?>
          <?php if (empty($vouchers)): ?><p class="text-sm text-app-muted">No vouchers generated.</p><?php endif; ?>
        </div>
      </div>

      <?php if (($booking['status'] ?? '') !== 'cancelled'): ?>
        <div class="rounded-xl border border-app-danger-soft bg-app-panel p-5">
          <h2 class="font-bold text-app-text">Cancel booking</h2>
          <form id="admin-cancel-form" class="mt-4 space-y-3">
            <input type="hidden" name="booking_id" value="<?= (int)($booking['id'] ?? 0) ?>">
            <textarea class="min-h-[90px] w-full rounded-lg border border-app-panel-border bg-app-input p-3 text-sm outline-none" name="reason" required placeholder="Cancellation reason"></textarea>
            <label class="flex items-center gap-2 text-sm text-app-secondary"><input type="checkbox" name="refund_deposit" value="1"> Mark deposit as refunded</label>
            <button class="w-full rounded-lg bg-app-danger px-4 py-2 text-sm font-bold text-white" type="submit">Cancel booking</button>
          </form>
        </div>
      <?php endif; ?>
    </aside>
  </div>
</section>
<script>
const cancelForm = document.getElementById('admin-cancel-form');
if (cancelForm) {
  cancelForm.addEventListener('submit', async (event) => {
    event.preventDefault();
    if (!confirm('Cancel this booking?')) return;
    const response = await fetch('<?= URLROOT ?>/admin/bookingCancel', { method: 'POST', body: new FormData(cancelForm) });
    const data = await response.json().catch(() => ({}));
    if (data.success) window.location.reload();
    else alert(data.error || 'Could not cancel booking.');
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
