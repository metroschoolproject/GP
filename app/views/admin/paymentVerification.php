<?php
$pendingPayments = $pendingPayments ?? [];
$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$money = fn($v) => number_format((float)$v, 0) . ' MMK';
$dateTime = static function ($value, string $fallback = '-') {
    if (empty($value)) return $fallback;
    $timestamp = strtotime((string)$value);
    return $timestamp ? date('M d, Y H:i', $timestamp) : $fallback;
};

$pendingCount = count($pendingPayments);
$pendingTotal = 0.0;
foreach ($pendingPayments as $payment) {
    $pendingTotal += (float)($payment['paid_amount'] ?? $payment['payment_amount'] ?? ((float)($payment['total_amount'] ?? 0) * 0.1));
}

$dashboardTitle = 'Payments';
$dashboardCrumb = 'Verification';
$dashboardContentClass = 'bg-app-content px-6 py-6 overflow-y-auto';
$dashboardContent = function () use ($pendingPayments, $pendingCount, $pendingTotal, $h, $money, $dateTime) {
?>
<section class="mx-auto max-w-7xl space-y-5">
  <div class="flex flex-wrap items-end justify-between gap-4">
    <div>
      <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-app-header-muted">Manual payment queue</p>
      <h1 class="mt-2 text-3xl font-bold tracking-tight text-app-text">Payment verification</h1>
      <p class="mt-1 text-sm text-app-muted">Review customer transfer proof, confirm the deposit, or request a new proof.</p>
    </div>
    <a href="<?= URLROOT ?>/admin/payments" class="rounded-lg border border-app-panel-border bg-white px-4 py-2 text-sm font-bold text-app-secondary hover:bg-app-input">Payment history</a>
  </div>

  <div class="grid gap-4 md:grid-cols-3">
    <div class="rounded-xl border border-app-panel-border bg-white p-5 shadow-sm">
      <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-app-muted">Awaiting review</p>
      <p class="mt-2 text-2xl font-bold text-app-text"><?= (int)$pendingCount ?></p>
    </div>
    <div class="rounded-xl border border-app-panel-border bg-white p-5 shadow-sm">
      <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-app-muted">Submitted amount</p>
      <p class="mt-2 text-2xl font-bold text-app-text"><?= $money($pendingTotal) ?></p>
    </div>
    <div class="rounded-xl border border-app-panel-border bg-white p-5 shadow-sm">
      <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-app-muted">Next step</p>
      <p class="mt-2 text-sm font-semibold text-app-secondary">Match amount, transaction ID, sender, and proof file before verifying.</p>
    </div>
  </div>

  <?php if (empty($pendingPayments)): ?>
    <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-8 text-center">
      <p class="text-lg font-bold text-emerald-800">All payment proofs are reviewed</p>
      <p class="mt-2 text-sm text-emerald-700">New customer deposits will appear here after submission.</p>
    </div>
  <?php else: ?>
    <div class="space-y-5">
      <?php foreach ($pendingPayments as $payment): ?>
        <?php
          $bookingId = (int)($payment['id'] ?? 0);
          $paymentId = (int)($payment['payment_id'] ?? 0);
          $bookingRef = (string)($payment['booking_ref'] ?? ('Booking #' . $bookingId));
          $customerName = (string)($payment['name'] ?? 'Unknown customer');
          $customerEmail = (string)($payment['email'] ?? '');
          $customerPhone = (string)($payment['phone'] ?? '');
          $totalAmount = (float)($payment['total_amount'] ?? 0);
          $expectedDeposit = $totalAmount * 0.1;
          $paidAmountRaw = $payment['paid_amount'] ?? $payment['payment_amount'] ?? null;
          $paidAmount = $paidAmountRaw !== null && $paidAmountRaw !== '' ? (float)$paidAmountRaw : 0.0;
          $displayPaid = $paidAmount > 0 ? $paidAmount : $expectedDeposit;
          $method = (string)($payment['bank_name'] ?? $payment['method'] ?? '-');
          $accountName = (string)($payment['account_name'] ?? '');
          $mobileNumber = (string)($payment['mobile_number'] ?? '');
          $reference = (string)($payment['transaction_ref'] ?? '');
          $slipPath = trim((string)($payment['payment_slip_path'] ?? ''));
          $submittedAt = $dateTime($payment['payment_created_at'] ?? null);
          $paidAt = $dateTime($payment['paid_at'] ?? null);
          $itemCount = (int)($payment['item_count'] ?? 0);
          $slipExt = strtolower(pathinfo($slipPath, PATHINFO_EXTENSION));
          $isImageSlip = $slipPath !== '' && in_array($slipExt, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true);
        ?>
        <article class="overflow-hidden rounded-xl border border-app-panel-border bg-white shadow-sm">
          <div class="flex flex-wrap items-start justify-between gap-4 border-b border-app-panel-border bg-app-soft px-5 py-4">
            <div>
              <div class="flex flex-wrap items-center gap-3">
                <h2 class="text-xl font-bold text-app-text"><?= $h($bookingRef) ?></h2>
                <?php if ($paymentId > 0): ?>
                  <span class="rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-bold uppercase tracking-[0.12em] text-amber-800">Awaiting review</span>
                <?php else: ?>
                  <span class="rounded-full border border-rose-200 bg-rose-50 px-3 py-1 text-xs font-bold uppercase tracking-[0.12em] text-rose-800">Payment record missing</span>
                <?php endif; ?>
              </div>
              <p class="mt-1 text-sm text-app-muted"><?= $h($customerName) ?><?php if ($customerEmail !== ''): ?> · <?= $h($customerEmail) ?><?php endif; ?></p>
            </div>
            <a href="<?= URLROOT ?>/admin/bookingDetail/<?= $bookingId ?>" class="rounded-lg border border-app-panel-border bg-white px-4 py-2 text-sm font-bold text-app-secondary hover:bg-app-input">Open booking</a>
          </div>

          <div class="grid gap-0 xl:grid-cols-[minmax(0,1fr)_340px]">
            <div class="p-5">
              <div class="grid gap-3 md:grid-cols-3">
                <div class="rounded-lg border border-app-panel-border bg-app-input p-4">
                  <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-app-muted">Amount sent</p>
                  <p class="mt-2 text-2xl font-bold text-app-text"><?= $money($displayPaid) ?></p>
                </div>
                <div class="rounded-lg border border-app-panel-border bg-app-input p-4">
                  <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-app-muted">Expected deposit</p>
                  <p class="mt-2 text-2xl font-bold text-app-text"><?= $money($expectedDeposit) ?></p>
                </div>
                <div class="rounded-lg border border-app-panel-border bg-app-input p-4">
                  <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-app-muted">Booking total</p>
                  <p class="mt-2 text-2xl font-bold text-app-text"><?= $money($totalAmount) ?></p>
                </div>
              </div>

              <dl class="mt-5 grid gap-x-6 gap-y-4 md:grid-cols-3">
                <div>
                  <dt class="text-[11px] font-bold uppercase tracking-[0.14em] text-app-muted">Payment method</dt>
                  <dd class="mt-1 font-semibold text-app-text"><?= $h($method ?: '-') ?></dd>
                </div>
                <div>
                  <dt class="text-[11px] font-bold uppercase tracking-[0.14em] text-app-muted">Transaction ID</dt>
                  <dd class="mt-1 break-all font-mono text-sm font-semibold text-app-text"><?= $h($reference ?: '-') ?></dd>
                </div>
                <div>
                  <dt class="text-[11px] font-bold uppercase tracking-[0.14em] text-app-muted">Items</dt>
                  <dd class="mt-1 font-semibold text-app-text"><?= $itemCount ?> service<?= $itemCount === 1 ? '' : 's' ?></dd>
                </div>
                <div>
                  <dt class="text-[11px] font-bold uppercase tracking-[0.14em] text-app-muted">Sender account</dt>
                  <dd class="mt-1 font-semibold text-app-text"><?= $h($accountName ?: '-') ?></dd>
                </div>
                <div>
                  <dt class="text-[11px] font-bold uppercase tracking-[0.14em] text-app-muted">Sender phone</dt>
                  <dd class="mt-1 font-semibold text-app-text"><?= $h($mobileNumber ?: '-') ?></dd>
                </div>
                <div>
                  <dt class="text-[11px] font-bold uppercase tracking-[0.14em] text-app-muted">Customer phone</dt>
                  <dd class="mt-1 font-semibold text-app-text"><?= $h($customerPhone ?: '-') ?></dd>
                </div>
                <div>
                  <dt class="text-[11px] font-bold uppercase tracking-[0.14em] text-app-muted">Transfer time</dt>
                  <dd class="mt-1 font-semibold text-app-text"><?= $h($paidAt) ?></dd>
                </div>
                <div>
                  <dt class="text-[11px] font-bold uppercase tracking-[0.14em] text-app-muted">Submitted</dt>
                  <dd class="mt-1 font-semibold text-app-text"><?= $h($submittedAt) ?></dd>
                </div>
              </dl>

              <?php if ($paymentId > 0): ?>
                <form class="payment-verification-form mt-5 rounded-lg border border-app-panel-border bg-app-soft p-4" data-booking-id="<?= $bookingId ?>">
                  <label class="text-xs font-bold uppercase tracking-[0.14em] text-app-muted" for="note-<?= $bookingId ?>">Admin note</label>
                  <textarea id="note-<?= $bookingId ?>" name="note" class="mt-2 min-h-[76px] w-full rounded-lg border border-app-panel-border bg-white p-3 text-sm text-app-text outline-none focus:border-app-primary" placeholder="Optional note for this verification"></textarea>
                  <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    <button type="button" class="verify-payment-btn rounded-lg bg-emerald-700 px-4 py-3 text-sm font-bold text-white hover:bg-emerald-800">Verify deposit</button>
                    <button type="button" class="reject-payment-btn rounded-lg border border-rose-200 bg-white px-4 py-3 text-sm font-bold text-rose-700 hover:bg-rose-50">Reject proof</button>
                  </div>
                </form>
              <?php else: ?>
                <div class="mt-5 rounded-lg border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">
                  This booking is marked as payment submitted, but no pending deposit payment record was saved. Ask the customer to resubmit after the payment fields migration is applied.
                </div>
              <?php endif; ?>
            </div>

            <aside class="border-t border-app-panel-border bg-app-soft p-5 xl:border-l xl:border-t-0">
              <p class="text-xs font-bold uppercase tracking-[0.16em] text-app-muted">Payment proof</p>
              <?php if ($slipPath !== ''): ?>
                <a href="<?= URLROOT ?>/<?= $h($slipPath) ?>" target="_blank" class="mt-3 block overflow-hidden rounded-lg border border-app-panel-border bg-white">
                  <?php if ($isImageSlip): ?>
                    <img src="<?= URLROOT ?>/<?= $h($slipPath) ?>" alt="Payment slip for <?= $h($bookingRef) ?>" class="max-h-[360px] w-full object-contain">
                  <?php else: ?>
                    <span class="block px-4 py-6 text-sm font-bold text-app-primary">Open uploaded payment document</span>
                  <?php endif; ?>
                </a>
              <?php else: ?>
                <div class="mt-3 rounded-lg border border-dashed border-app-panel-border bg-white p-5 text-sm text-app-muted">No proof file was saved for this payment.</div>
              <?php endif; ?>
            </aside>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<div id="toast" class="fixed right-4 top-4 z-50 max-w-sm opacity-0 pointer-events-none transition-all duration-300"></div>

<script>
function showToast(message, type = 'success') {
  const toast = document.getElementById('toast');
  toast.textContent = message;
  toast.className = 'fixed right-4 top-4 z-50 max-w-sm opacity-100 pointer-events-auto transition-all duration-300 rounded-lg border px-4 py-3 text-sm font-bold';
  if (type === 'error') {
    toast.classList.add('border-rose-200', 'bg-rose-50', 'text-rose-800');
  } else {
    toast.classList.add('border-emerald-200', 'bg-emerald-50', 'text-emerald-800');
  }

  setTimeout(() => {
    toast.classList.remove('opacity-100', 'pointer-events-auto');
    toast.classList.add('opacity-0', 'pointer-events-none');
  }, 3500);
}

document.querySelectorAll('.payment-verification-form').forEach(form => {
  form.addEventListener('click', async event => {
    if (event.target.matches('.verify-payment-btn')) {
      await handleVerification(form, true);
    }
    if (event.target.matches('.reject-payment-btn')) {
      await handleVerification(form, false);
    }
  });
});

async function handleVerification(form, approve) {
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

  try {
    const response = await fetch(endpoint, { method: 'POST', body: formData });
    const data = await response.json();

    if (data.success) {
      showToast(data.message || 'Payment review saved.');
      setTimeout(() => location.reload(), 1200);
    } else {
      showToast(data.error || 'Operation failed.', 'error');
    }
  } catch (error) {
    showToast('Connection error. Please try again.', 'error');
  }
}
</script>
<?php
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php require_once APPROOT . '/views/dashboardLayout/head.php'; ?>
</head>
<body class="grid h-screen gap-0 bg-app-page" style="grid-template-columns: 280px 1fr;">
  <?php require_once APPROOT . '/views/dashboardLayout/sidebar.php'; ?>
</body>
</html>
