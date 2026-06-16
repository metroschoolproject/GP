<?php
$pendingPayments = $pendingPayments ?? [];
$h = fn($v) => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
$money = fn($v) => number_format((float)$v, 0) . ' MMK';
?>
<div class="container mx-auto px-4 py-8">
  <div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-800">Payment Verification</h1>
    <p class="text-gray-600 mt-2">Review and verify customer payment slips for manual payment methods</p>
  </div>

  <?php if (empty($pendingPayments)): ?>
    <div class="bg-green-50 border border-green-200 rounded-lg p-6 text-center">
      <div class="text-green-700">
        <p class="text-lg font-semibold">✓ All payments verified</p>
        <p class="text-sm mt-2">No pending payment verifications at this time.</p>
      </div>
    </div>
  <?php else: ?>
    <div class="grid gap-6">
      <?php foreach ($pendingPayments as $payment):
        $bookingId = (int)($payment['id'] ?? 0);
        $customerName = $h($payment['name'] ?? 'Unknown');
        $amount = (float)($payment['total_amount'] ?? 0);
        $method = $h($payment['method'] ?? 'Unknown');
        $reference = $h($payment['transaction_ref'] ?? '');
        $slipPath = $payment['payment_slip_path'] ?? '';
        $submittedAt = $payment['payment_created_at'] ?? '';
        $itemCount = (int)($payment['item_count'] ?? 0);
      ?>
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
          <div class="border-b border-gray-200 px-6 py-4 bg-gradient-to-r from-purple-50 to-pink-50">
            <div class="flex justify-between items-start">
              <div>
                <h3 class="text-lg font-semibold text-gray-800">Booking #<?= $bookingId ?></h3>
                <p class="text-sm text-gray-600 mt-1"><?= $customerName ?></p>
                <p class="text-sm text-gray-500 mt-1">Deposit: <strong><?= $money($amount * 0.1) ?></strong></p>
              </div>
              <div class="text-right">
                <div class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                  ⏳ Awaiting Verification
                </div>
              </div>
            </div>
          </div>

          <div class="px-6 py-4">
            <div class="grid grid-cols-2 gap-4 mb-6 pb-6 border-b border-gray-200">
              <div>
                <p class="text-xs text-gray-500 font-semibold uppercase">Payment Method</p>
                <p class="text-sm text-gray-800 mt-1"><?= $method ?></p>
              </div>
              <div>
                <p class="text-xs text-gray-500 font-semibold uppercase">Items</p>
                <p class="text-sm text-gray-800 mt-1"><?= $itemCount ?> service<?= $itemCount !== 1 ? 's' : '' ?></p>
              </div>
              <div>
                <p class="text-xs text-gray-500 font-semibold uppercase">Reference</p>
                <p class="text-sm text-gray-800 mt-1"><code class="bg-gray-100 px-2 py-1 rounded"><?= $reference ?: 'N/A' ?></code></p>
              </div>
              <div>
                <p class="text-xs text-gray-500 font-semibold uppercase">Submitted</p>
                <p class="text-sm text-gray-800 mt-1"><?= date('M d, H:i', strtotime($submittedAt)) ?></p>
              </div>
            </div>

            <?php if ($slipPath): ?>
              <div class="mb-6 pb-6 border-b border-gray-200">
                <p class="text-xs text-gray-500 font-semibold uppercase mb-3">Payment Slip</p>
                <div class="bg-gray-50 rounded-lg p-4">
                  <?php
                    $extension = strtolower(pathinfo($slipPath, PATHINFO_EXTENSION));
                    if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'])):
                  ?>
                    <img src="<?= URLROOT ?>/<?= $slipPath ?>" alt="Payment Slip" class="max-w-sm max-h-64 rounded border border-gray-200">
                  <?php else: ?>
                    <a href="<?= URLROOT ?>/<?= $slipPath ?>" target="_blank" class="text-blue-600 hover:underline">
                      📄 View Document
                    </a>
                  <?php endif; ?>
                </div>
              </div>
            <?php endif; ?>

            <!-- Verification Form -->
            <div class="bg-gray-50 rounded-lg p-4">
              <form class="payment-verification-form" data-booking-id="<?= $bookingId ?>">
                <div class="mb-4">
                  <label class="block text-sm font-semibold text-gray-700 mb-2">Verification Notes (optional)</label>
                  <textarea name="note" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500" rows="2" placeholder="Add any notes about this payment verification..."></textarea>
                </div>

                <div class="flex gap-3">
                  <button type="button" class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition verify-payment-btn">
                    ✓ Verify & Approve
                  </button>
                  <button type="button" class="flex-1 px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold rounded-lg transition reject-payment-btn">
                    ✕ Reject
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<div id="toast" class="fixed top-4 right-4 max-w-sm z-50 opacity-0 pointer-events-none transition-all duration-300"></div>

<script>
function showToast(message, type = 'success') {
  const toast = document.getElementById('toast');
  toast.textContent = message;
  toast.className = 'fixed top-4 right-4 max-w-sm z-50 opacity-100 pointer-events-auto transition-all duration-300 px-4 py-3 rounded-lg font-semibold';

  if (type === 'error') {
    toast.classList.add('bg-red-100', 'text-red-800', 'border', 'border-red-200');
  } else {
    toast.classList.add('bg-green-100', 'text-green-800', 'border', 'border-green-200');
  }

  setTimeout(() => {
    toast.classList.remove('opacity-100', 'pointer-events-auto');
    toast.classList.add('opacity-0', 'pointer-events-none');
  }, 4000);
}

document.querySelectorAll('.payment-verification-form').forEach(form => {
  form.addEventListener('click', async (e) => {
    if (e.target.matches('.verify-payment-btn')) {
      await handleVerification(form, true);
    } else if (e.target.matches('.reject-payment-btn')) {
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
    // For reject, need reason
    const reason = prompt('Please provide a reason for rejection:');
    if (!reason) return;
    formData.set('reason', reason);
  }

  try {
    const resp = await fetch(endpoint, {
      method: 'POST',
      body: formData
    });

    const data = await resp.json();

    if (data.success) {
      showToast(data.message, 'success');
      setTimeout(() => {
        location.reload();
      }, 1500);
    } else {
      showToast(data.error || 'Operation failed', 'error');
    }
  } catch (err) {
    showToast('Connection error. Please try again.', 'error');
  }
}
</script>
