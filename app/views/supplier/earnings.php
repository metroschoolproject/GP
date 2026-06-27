<?php
$earnings = $earnings ?? [];
$payouts = $payouts ?? [];
$supplier = $supplier ?? [];
$supplierId = (int)($supplierId ?? 0);
$currentPage = (int)($currentPage ?? 1);
$totalPages = (int)($totalPages ?? 1);
$totalPayouts = (int)($totalPayouts ?? 0);

$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$money = fn($v) => number_format((float)$v, 0) . ' MMK';
$date = fn($v) => date('M d, Y', strtotime($v ?? 'now'));

$dashboardTitle = 'Earnings';
$dashboardCrumb = 'Earnings';
$dashboardContentClass = 'earnings-page';
$dashboardContent = function () use ($earnings, $payouts, $supplier, $supplierId, $currentPage, $totalPages, $totalPayouts, $h, $money, $date) {
?>
<style>
.earnings-page { --ink:#6d4c5b; --muted:#A8A29E; --soft:#F4F1EE; --panel:#FFFFFF; --line:#ead8c7; --primary:#6d4c5b; --green:#166534; --amber:#92400e; --danger:#991b1b; color:var(--ink); padding:28px 32px; }
.earnings-page h1 { font-size:clamp(28px,3vw,42px); font-weight:900; margin:6px 0 7px; }
.earnings-page .kicker { font-size:10px; font-weight:900; text-transform:uppercase; letter-spacing:.17em; color:var(--muted); }
.earnings-top { display:flex; justify-content:space-between; gap:18px; align-items:flex-start; margin-bottom:22px; }
.earnings-top p { color:var(--muted); font-size:13px; }

.earnings-summary { display:grid; grid-template-columns:repeat(4,1fr); gap:10px; margin-bottom:20px; }
.earnings-stat { background:var(--panel); border:1px solid var(--line); border-radius:14px; padding:16px 20px; }
.earnings-stat-label { font-size:10px; font-weight:800; letter-spacing:.1em; text-transform:uppercase; color:var(--muted); margin-bottom:6px; }
.earnings-stat-value { font-size:22px; font-weight:800; color:var(--ink); }
.earnings-stat-value.green { color:var(--green); }
.earnings-stat-value.amber { color:var(--amber); }
.earnings-stat-value.blue { color:#4338ca; }
.earnings-stat-value.purple { color:#6d4c5b; }
.earnings-stat-sub { font-size:11px; color:var(--muted); margin-top:3px; }

.earnings-alert { border-radius:14px; padding:18px 22px; margin-bottom:20px; display:flex; align-items:center; justify-content:space-between; gap:18px; flex-wrap:wrap; }
.earnings-alert.ready { background:#FFFBEB; border:1px solid #ead8c7; }
.earnings-alert.empty { background:var(--soft); border:1px solid var(--line); }
.earnings-alert h3 { font-size:15px; font-weight:700; color:var(--ink); margin-bottom:4px; }
.earnings-alert p { font-size:13px; color:var(--muted); }
.earnings-alert-btn { display:inline-flex; align-items:center; gap:6px; min-height:38px; padding:0 20px; border:0; border-radius:999px; background:var(--primary); color:#fff; font-size:13px; font-weight:700; cursor:pointer; transition:all .2s; }
.earnings-alert-btn:hover { opacity:.85; }

.earnings-table-wrap { background:var(--panel); border:1px solid var(--line); border-radius:14px; overflow:hidden; }
.earnings-table-header { padding:16px 20px; border-bottom:1px solid var(--line); }
.earnings-table-header h2 { font-size:15px; font-weight:700; color:var(--ink); }
.earnings-table-header p { font-size:12px; color:var(--muted); margin-top:2px; }
.earnings-table { width:100%; border-collapse:collapse; }
.earnings-table thead tr { background:var(--soft); }
.earnings-table thead th { padding:10px 16px; font-size:10px; font-weight:800; letter-spacing:.1em; text-transform:uppercase; color:var(--muted); text-align:left; }
.earnings-table tbody tr { border-top:1px solid var(--line); }
.earnings-table tbody tr:hover { background:var(--soft); }
.earnings-table tbody td { padding:12px 16px; font-size:13px; vertical-align:middle; }

.earnings-badge { display:inline-flex; align-items:center; border-radius:20px; padding:3px 10px; font-size:10px; font-weight:800; letter-spacing:.05em; text-transform:uppercase; }
.earnings-badge.success { background:#ECFDF5; color:#065f46; }
.earnings-badge.pending { background:#FFFBEB; color:#92400e; }
.earnings-badge.processing { background:#EEF2FF; color:#3730A3; }
.earnings-badge.failed { background:#FEF2F2; color:#991b1b; }

.earnings-empty { padding:40px 20px; text-align:center; color:var(--muted); font-size:13px; }
.earnings-pagination { display:flex; justify-content:space-between; align-items:center; padding:12px 16px; border-top:1px solid var(--line); }
.earnings-page-info { font-size:12px; color:var(--muted); }
.earnings-page-btns { display:flex; gap:6px; }
.earnings-btn { display:inline-flex; align-items:center; gap:6px; min-height:34px; padding:0 14px; border:1px solid var(--line); border-radius:999px; background:transparent; color:var(--ink); font-size:12px; font-weight:700; cursor:pointer; text-decoration:none; transition:all .14s; }
.earnings-btn:hover { border-color:var(--primary); color:var(--primary); }
.earnings-btn:disabled { opacity:.4; cursor:default; }

/* Modal */
.earnings-modal-overlay { position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:50; display:flex; align-items:center; justify-content:center; padding:16px; }
.earnings-modal { background:var(--panel); border-radius:16px; max-width:440px; width:100%; overflow:hidden; box-shadow:0 24px 60px rgba(0,0,0,0.15); }
.earnings-modal-header { padding:18px 22px; border-bottom:1px solid var(--line); }
.earnings-modal-header h2 { font-size:16px; font-weight:700; color:var(--ink); }
.earnings-modal-body { padding:22px; }
.earnings-modal-body label { display:block; font-size:12px; font-weight:700; color:var(--ink); margin-bottom:6px; }
.earnings-modal-body input,
.earnings-modal-body select { width:100%; padding:10px 14px; border:1px solid var(--line); border-radius:10px; font-size:13px; color:var(--ink); background:var(--panel); transition:border-color .2s; }
.earnings-modal-body input:focus,
.earnings-modal-body select:focus { outline:none; border-color:var(--primary); }
.earnings-modal-body input[readonly] { background:var(--soft); }
.earnings-modal-tip { background:var(--soft); border-radius:10px; padding:12px 16px; margin-top:14px; font-size:12px; color:var(--muted); }
.earnings-modal-footer { display:flex; gap:10px; padding:0 22px 22px; }
.earnings-modal-footer button { flex:1; min-height:40px; border-radius:10px; font-size:13px; font-weight:700; cursor:pointer; transition:all .2s; }
.earnings-modal-cancel { border:1px solid var(--line); background:transparent; color:var(--ink); }
.earnings-modal-cancel:hover { border-color:var(--primary); color:var(--primary); }
.earnings-modal-submit { border:0; background:var(--primary); color:#fff; }
.earnings-modal-submit:hover { opacity:.85; }

@media(max-width:900px){ .earnings-summary{grid-template-columns:repeat(2,1fr)} }
@media(max-width:600px){ .earnings-summary{grid-template-columns:1fr} }

/* Toast */
.earnings-toast{position:fixed;top:24px;right:24px;z-index:100;display:flex;align-items:center;gap:10px;padding:14px 22px;border-radius:12px;font-size:13px;font-weight:700;font-family:'DM Sans',system-ui,sans-serif;box-shadow:0 8px 30px rgba(0,0,0,.12);transform:translateX(120%);transition:transform .35s cubic-bezier(.4,0,.2,1)}
.earnings-toast.show{transform:translateX(0)}
.earnings-toast.success{background:#ECFDF5;color:#065F46;border:1px solid #A7F3D0}
.earnings-toast.error{background:#FEF2F2;color:#991B1B;border:1px solid #FECACA}
</style>

<section class="earnings-page">
  <div class="earnings-top">
    <div>
      <div class="kicker">Supplier workspace</div>
      <h1>Your Earnings</h1>
      <p>Track payouts and request cash withdrawals.</p>
    </div>
  </div>

  <div class="earnings-summary">
    <div class="earnings-stat">
      <div class="earnings-stat-label">Available to Withdraw</div>
      <div class="earnings-stat-value amber"><?= $money($earnings['pending_amount'] ?? 0) ?></div>
      <div class="earnings-stat-sub"><?= (int)($earnings['pending_count'] ?? 0) ?> booking<?= ($earnings['pending_count'] ?? 0) !== 1 ? 's' : '' ?></div>
    </div>
    <div class="earnings-stat">
      <div class="earnings-stat-label">Being Processed</div>
      <div class="earnings-stat-value blue"><?= $money($earnings['processing_amount'] ?? 0) ?></div>
      <div class="earnings-stat-sub">Admin reviewing</div>
    </div>
    <div class="earnings-stat">
      <div class="earnings-stat-label">Paid Out</div>
      <div class="earnings-stat-value green"><?= $money($earnings['paid_amount'] ?? 0) ?></div>
      <div class="earnings-stat-sub"><?= (int)($earnings['paid_count'] ?? 0) ?> payout<?= ($earnings['paid_count'] ?? 0) !== 1 ? 's' : '' ?></div>
    </div>
    <div class="earnings-stat">
      <div class="earnings-stat-label">Total Earned</div>
      <div class="earnings-stat-value"><?= $money($earnings['total_earned'] ?? 0) ?></div>
      <div class="earnings-stat-sub">All time</div>
    </div>
  </div>

  <?php
    $hasPending = ((int)($earnings['pending_amount'] ?? 0)) > 0;
    $hasProcessing = ((int)($earnings['processing_amount'] ?? 0)) > 0;
  ?>
  <?php if ($hasPending): ?>
    <div class="earnings-alert ready">
      <div>
        <h3>Ready to Cash Out?</h3>
        <p>You have <?= $money($earnings['pending_amount'] ?? 0) ?> available to withdraw.</p>
      </div>
      <button id="cashout-btn" class="earnings-alert-btn" type="button">Request Payout</button>
    </div>
  <?php elseif ($hasProcessing): ?>
    <div class="earnings-alert ready" style="background:#EEF2FF;border-color:#C7D2FE">
      <div>
        <h3>Payout Under Review</h3>
        <p>You have <?= $money($earnings['processing_amount'] ?? 0) ?> being processed. Admin will transfer the funds shortly.</p>
      </div>
    </div>
  <?php else: ?>
    <div class="earnings-alert empty">
      <p>No pending payouts yet. Once bookings are completed, payouts will appear here.</p>
    </div>
  <?php endif; ?>

  <div class="earnings-table-wrap">
    <div class="earnings-table-header">
      <h2>Payout History</h2>
      <p><?= $totalPayouts ?> total transaction<?= $totalPayouts !== 1 ? 's' : '' ?></p>
    </div>

    <?php if (empty($payouts)): ?>
      <div class="earnings-empty">No payout history yet. Once bookings are completed, payouts will appear here.</div>
    <?php else: ?>
      <table class="earnings-table">
        <thead>
          <tr>
            <th>Booking</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Details</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($payouts as $payout):
            $status = $payout['status'] ?? 'pending';
            $badgeClass = match($status) {
              'success' => 'success',
              'processing' => 'processing',
              'failed' => 'failed',
              default => 'pending',
            };
            $statusLabel = match($status) {
              'success' => 'Paid',
              'processing' => 'Under Review',
              'failed' => 'Rejected',
              default => 'Pending',
            };
            $note = trim((string)($payout['verified_note'] ?? ''));
          ?>
            <tr>
              <td style="font-weight:700">#<?= (int)($payout['booking_id'] ?? 0) ?></td>
              <td style="font-weight:700"><?= $money((float)($payout['amount'] ?? 0)) ?></td>
              <td><span class="earnings-badge <?= $badgeClass ?>"><?= $statusLabel ?></span></td>
              <td style="font-size:12px;color:var(--muted);max-width:220px">
                <?php if ($status === 'success' && $note): ?>
                  <?= $h($note) ?>
                <?php elseif ($status === 'failed' && $note): ?>
                  <span style="color:var(--danger)">Reason: <?= $h($note) ?></span>
                <?php elseif ($status === 'processing'): ?>
                  Waiting for admin to transfer funds
                <?php else: ?>
                  &mdash;
                <?php endif; ?>
              </td>
              <td><?= $date($payout['created_at'] ?? 'now') ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <?php if ($totalPages > 1): ?>
        <div class="earnings-pagination">
          <div class="earnings-page-info">Page <?= $currentPage ?> of <?= $totalPages ?></div>
          <div class="earnings-page-btns">
            <?php if ($currentPage > 1): ?>
              <a href="?page=<?= $currentPage - 1 ?>" class="earnings-btn">&larr; Previous</a>
            <?php endif; ?>
            <?php if ($currentPage < $totalPages): ?>
              <a href="?page=<?= $currentPage + 1 ?>" class="earnings-btn">Next &rarr;</a>
            <?php endif; ?>
          </div>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</section>

<!-- Cash Out Modal -->
<div id="cashout-modal" class="earnings-modal-overlay" style="display:none">
  <div class="earnings-modal">
    <div class="earnings-modal-header">
      <h2>Request Payout</h2>
    </div>
    <form id="cashout-form" class="earnings-modal-body">
      <?= csrf_field() ?>
      <div style="margin-bottom:14px">
        <label>Bank Account Number</label>
        <input type="text" name="bank_account" value="<?= $h($supplier['bank_account'] ?? '') ?>" placeholder="e.g., 1234567890" required>
      </div>
      <div style="margin-bottom:14px">
        <label>Bank</label>
        <select name="bank_code" required>
          <option value="">Select bank...</option>
          <?php $selectedBank = (string)($supplier['bank_code'] ?? ''); ?>
          <option value="AYA" <?= $selectedBank === 'AYA' ? 'selected' : '' ?>>AYA Bank</option>
          <option value="KBZ" <?= $selectedBank === 'KBZ' ? 'selected' : '' ?>>KBZ Bank</option>
          <option value="AGD" <?= $selectedBank === 'AGD' ? 'selected' : '' ?>>AGD Bank</option>
          <option value="CBD" <?= $selectedBank === 'CBD' ? 'selected' : '' ?>>CB Bank</option>
          <option value="MYBANK" <?= $selectedBank === 'MYBANK' ? 'selected' : '' ?>>MyBank</option>
        </select>
      </div>
      <div style="margin-bottom:14px">
        <label>Amount</label>
        <input type="number" name="amount" value="<?= number_format((float)($earnings['pending_amount'] ?? 0), 2, '.', '') ?>" readonly required>
        <p style="font-size:11px;color:var(--muted);margin-top:4px">Full available balance submitted as one payout batch.</p>
      </div>
      <div class="earnings-modal-tip">
        After you submit, admin will manually transfer the funds to your bank account. This usually takes 1&ndash;3 business days. You'll be notified once the payment is sent.
      </div>
      <div class="earnings-modal-footer">
        <button type="button" onclick="closeCashoutModal()" class="earnings-modal-cancel">Cancel</button>
        <button type="submit" class="earnings-modal-submit">Submit Request</button>
      </div>
    </form>
  </div>
</div>

<div id="earnings-toast" class="earnings-toast"><span style="width:22px;height:22px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:13px;flex-shrink:0" class="earnings-toast-icon"></span><span class="earnings-toast-msg"></span></div>

<script>
function showToast(msg, type = 'success') {
  const t = document.getElementById('earnings-toast');
  t.className = 'earnings-toast ' + type;
  t.style.background = type === 'success' ? '#ECFDF5' : '#FEF2F2';
  t.style.color = type === 'success' ? '#065F46' : '#991B1B';
  t.style.borderColor = type === 'success' ? '#A7F3D0' : '#FECACA';
  const icon = t.querySelector('.earnings-toast-icon');
  icon.textContent = type === 'success' ? '✓' : '✕';
  icon.style.background = type === 'success' ? '#065F46' : '#991B1B';
  icon.style.color = '#FFF';
  t.querySelector('.earnings-toast-msg').textContent = msg;
  t.classList.add('show');
  clearTimeout(t._timer);
  t._timer = setTimeout(() => t.classList.remove('show'), 4000);
}

function openCashoutModal() {
  document.getElementById('cashout-modal').style.display = 'flex';
}
function closeCashoutModal() {
  document.getElementById('cashout-modal').style.display = 'none';
}
document.getElementById('cashout-btn')?.addEventListener('click', openCashoutModal);
document.getElementById('cashout-form')?.addEventListener('submit', async (e) => {
  e.preventDefault();
  const btn = e.target.querySelector('[type="submit"]');
  btn.disabled = true;
  btn.textContent = 'Submitting…';
  const formData = new FormData(e.target);
  try {
    const resp = await fetch('<?= URLROOT ?>/booking/requestPayoutPost', { method: 'POST', body: formData });
    const text = await resp.text();
    let data;
    try { data = JSON.parse(text); } catch(e) { data = null; }
    if (data && data.success) {
      closeCashoutModal();
      showToast('Payout request submitted! We\'ll notify you once it\'s processed.');
      setTimeout(() => location.reload(), 2000);
    } else {
      showToast(data?.error || 'Something went wrong. Please try again.', 'error');
      btn.disabled = false;
      btn.textContent = 'Submit Request';
    }
  } catch (err) {
    showToast('Network error — please try again.', 'error');
    btn.disabled = false;
    btn.textContent = 'Submit Request';
  }
});
document.getElementById('cashout-modal')?.addEventListener('click', (e) => {
  if (e.target.id === 'cashout-modal') closeCashoutModal();
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
