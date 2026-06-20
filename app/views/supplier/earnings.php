<?php
$earnings = $earnings ?? [];
$payouts = $payouts ?? [];
$supplier = $supplier ?? [];
$supplierId = (int)($supplierId ?? 0);
$currentPage = (int)($currentPage ?? 1);
$totalPages = (int)($totalPages ?? 1);
$totalPayouts = (int)($totalPayouts ?? 0);

$h = fn($v) => htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
$money = fn($v) => number_format((float)$v, 0) . ' MMK';
$date = fn($v) => date('M d, Y', strtotime($v ?? 'now'));
?>
<div class="container mx-auto px-4 py-8">
  <div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-800">Your Earnings</h1>
    <p class="text-gray-600 mt-2">Track payouts and request cash withdrawals</p>
  </div>

  <!-- Earnings Summary Cards -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <!-- Pending Earnings -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
      <div class="p-6 border-b border-gray-200">
        <p class="text-sm text-gray-500 font-semibold uppercase tracking-wide">Pending Payout</p>
      </div>
      <div class="p-6">
        <div class="text-4xl font-bold text-amber-600"><?= $money($earnings['pending_amount'] ?? 0) ?></div>
        <p class="text-sm text-gray-600 mt-2"><?= (int)($earnings['pending_count'] ?? 0) ?> booking<?= ($earnings['pending_count'] ?? 0) !== 1 ? 's' : '' ?></p>
        <p class="text-xs text-gray-500 mt-4">From completed bookings waiting for cash out</p>
      </div>
    </div>

    <!-- Total Paid -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
      <div class="p-6 border-b border-gray-200">
        <p class="text-sm text-gray-500 font-semibold uppercase tracking-wide">Already Paid</p>
      </div>
      <div class="p-6">
        <div class="text-4xl font-bold text-green-600"><?= $money($earnings['paid_amount'] ?? 0) ?></div>
        <p class="text-sm text-gray-600 mt-2"><?= (int)($earnings['paid_count'] ?? 0) ?> payout<?= ($earnings['paid_count'] ?? 0) !== 1 ? 's' : '' ?></p>
        <p class="text-xs text-gray-500 mt-4">Disbursed to your bank account</p>
      </div>
    </div>

    <!-- Total Earned -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
      <div class="p-6 border-b border-gray-200">
        <p class="text-sm text-gray-500 font-semibold uppercase tracking-wide">Total Earned</p>
      </div>
      <div class="p-6">
        <div class="text-4xl font-bold text-purple-600"><?= $money($earnings['total_earned'] ?? 0) ?></div>
        <p class="text-sm text-gray-600 mt-2">All time</p>
        <p class="text-xs text-gray-500 mt-4">Pending + Paid</p>
      </div>
    </div>
  </div>

  <!-- Cash Out Card -->
  <?php if (((int)($earnings['pending_amount'] ?? 0)) > 0): ?>
    <div class="bg-gradient-to-r from-amber-50 to-orange-50 rounded-lg border border-amber-200 overflow-hidden mb-8">
      <div class="p-6">
        <div class="flex items-start justify-between">
          <div>
            <h3 class="text-lg font-semibold text-amber-900 mb-2">Ready to Cash Out?</h3>
            <p class="text-sm text-amber-800 mb-4">You have <?= $money($earnings['pending_amount'] ?? 0) ?> available to withdraw to your bank account.</p>
          </div>
          <button id="cashout-btn" class="flex-shrink-0 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-semibold rounded-lg transition">
            Request Payout
          </button>
        </div>
      </div>
    </div>
  <?php else: ?>
    <div class="bg-blue-50 rounded-lg border border-blue-200 p-6 mb-8">
      <p class="text-sm text-blue-900">
        ℹ️ You don't have any pending payouts yet. Once your bookings are completed, payouts will be available here.
      </p>
    </div>
  <?php endif; ?>

  <!-- Payout History -->
  <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
    <div class="p-6 border-b border-gray-200">
      <h2 class="text-lg font-semibold text-gray-800">Payout History</h2>
      <p class="text-sm text-gray-600 mt-1"><?= $totalPayouts ?> total transaction<?= $totalPayouts !== 1 ? 's' : '' ?></p>
    </div>

    <?php if (empty($payouts)): ?>
      <div class="p-6 text-center">
        <p class="text-gray-500">No payout history yet.</p>
      </div>
    <?php else: ?>
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Booking</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Amount</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Status</th>
              <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Date</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($payouts as $payout):
              $status = $payout['status'] ?? 'pending';
              $statusColor = match($status) {
                'pending' => 'text-yellow-700 bg-yellow-50',
                'processing' => 'text-blue-700 bg-blue-50',
                'success' => 'text-green-700 bg-green-50',
                'failed' => 'text-red-700 bg-red-50',
                default => 'text-gray-700 bg-gray-50',
              };
              $statusLabel = ucfirst($status);
            ?>
              <tr class="border-b border-gray-200 hover:bg-gray-50">
                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                  #<?= (int)($payout['booking_id'] ?? 0) ?>
                </td>
                <td class="px-6 py-4 text-sm font-semibold text-gray-900">
                  <?= $money((float)($payout['amount'] ?? 0)) ?>
                </td>
                <td class="px-6 py-4">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $statusColor ?>">
                    <?= $statusLabel ?>
                  </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">
                  <?= $date($payout['created_at'] ?? 'now') ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <?php if ($totalPages > 1): ?>
        <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
          <div class="text-sm text-gray-600">
            Page <?= $currentPage ?> of <?= $totalPages ?>
          </div>
          <div class="flex gap-2">
            <?php if ($currentPage > 1): ?>
              <a href="?page=<?= $currentPage - 1 ?>" class="px-3 py-1 border border-gray-300 rounded text-sm hover:bg-gray-50">← Previous</a>
            <?php endif; ?>
            <?php if ($currentPage < $totalPages): ?>
              <a href="?page=<?= $currentPage + 1 ?>" class="px-3 py-1 border border-gray-300 rounded text-sm hover:bg-gray-50">Next →</a>
            <?php endif; ?>
          </div>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>

<!-- Cash Out Modal -->
<div id="cashout-modal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
  <div class="bg-white rounded-lg max-w-md w-full overflow-hidden shadow-lg">
    <div class="p-6 border-b border-gray-200">
      <h2 class="text-lg font-semibold text-gray-800">Request Payout</h2>
    </div>

    <form id="cashout-form" class="p-6 space-y-4">
      <?= csrf_field() ?>
      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">Bank Account Number</label>
        <input type="text" name="bank_account" placeholder="e.g., 1234567890" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500">
      </div>

      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">Bank</label>
        <select name="bank_code" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500">
          <option value="">Select bank...</option>
          <option value="AYA">AYA Bank</option>
          <option value="KBZ">KBZ Bank</option>
          <option value="AGD">Agile Bank</option>
          <option value="CBD">CB Bank</option>
          <option value="MYBANK">MyBank</option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">Amount</label>
        <input type="number" name="amount" min="0" max="<?= (int)($earnings['pending_amount'] ?? 0) ?>" placeholder="Amount in MMK" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-purple-500">
        <p class="text-xs text-gray-600 mt-1">Available: <?= $money($earnings['pending_amount'] ?? 0) ?></p>
      </div>

      <div class="bg-blue-50 p-4 rounded-lg">
        <p class="text-xs text-blue-900">
          ℹ️ Payouts are processed within 1-2 business days. A small transaction fee may apply depending on your bank.
        </p>
      </div>

      <div class="flex gap-3 pt-4">
        <button type="button" onclick="closeCashoutModal()" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 font-semibold hover:bg-gray-50">
          Cancel
        </button>
        <button type="submit" class="flex-1 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-semibold rounded-lg">
          Request Payout
        </button>
      </div>
    </form>
  </div>
</div>

<script>
function openCashoutModal() {
  document.getElementById('cashout-modal').classList.remove('hidden');
}

function closeCashoutModal() {
  document.getElementById('cashout-modal').classList.add('hidden');
}

document.getElementById('cashout-btn')?.addEventListener('click', openCashoutModal);

document.getElementById('cashout-form')?.addEventListener('submit', async (e) => {
  e.preventDefault();

  const formData = new FormData(e.target);
  formData.append('suppress_method_token', '1');

  try {
    const resp = await fetch('<?= URLROOT ?>/booking/requestPayoutPost', {
      method: 'POST',
      body: formData,
    });

    const data = await resp.json();

    if (data.success) {
      alert('✓ Payout request submitted! You will receive funds within 1-2 business days.');
      closeCashoutModal();
      setTimeout(() => location.reload(), 1000);
    } else {
      alert('✕ ' + (data.error || 'Request failed'));
    }
  } catch (err) {
    alert('✕ Connection error. Please try again.');
  }
});

// Close modal when clicking outside
document.getElementById('cashout-modal')?.addEventListener('click', (e) => {
  if (e.target === document.getElementById('cashout-modal')) {
    closeCashoutModal();
  }
});
</script>
