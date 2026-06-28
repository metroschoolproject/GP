<?php
$payouts = $payouts ?? [];
$activeStatus = $activeStatus ?? 'processing';
$currentPage = (int)($currentPage ?? 1);
$totalPages = (int)($totalPages ?? 1);
$totalCount = (int)($totalCount ?? 0);
$stats = $stats ?? [];

$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$money = fn($v) => number_format((float)$v, 0) . ' MMK';
$dateTime = fn($v) => empty($v) ? '-' : date('M j, Y g:i A', strtotime($v));

$dashboardTitle = 'Supplier Payouts';
$dashboardCrumb = 'Payouts';
$dashboardContentClass = 'admin-payouts-page';

$tabs = [
    'processing' => ['label' => 'Pending Review', 'icon' => 'clock'],
    'pending'    => ['label' => 'Not Yet Requested', 'icon' => 'hourglass'],
    'success'    => ['label' => 'Paid', 'icon' => 'check-circle'],
    'failed'     => ['label' => 'Rejected', 'icon' => 'x-circle'],
];

$dashboardContent = function () use ($payouts, $activeStatus, $currentPage, $totalPages, $totalCount, $stats, $h, $money, $dateTime, $tabs) {
?>
<style>
/* Toast */
.pg-toast{position:fixed;top:24px;right:24px;z-index:100;display:flex;align-items:center;gap:10px;padding:14px 22px;border-radius:12px;font-size:13px;font-weight:700;font-family:'DM Sans',system-ui,sans-serif;box-shadow:0 8px 30px rgba(0,0,0,.12);transform:translateX(120%);transition:transform .35s cubic-bezier(.4,0,.2,1)}
.pg-toast.show{transform:translateX(0)}
.pg-toast.success{background:#ECFDF5;color:#065F46;border:1px solid #A7F3D0}
.pg-toast.error{background:#FEF2F2;color:#991B1B;border:1px solid #FECACA}
.pg-toast-icon{width:22px;height:22px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:13px;flex-shrink:0}
.pg-toast.success .pg-toast-icon{background:#065F46;color:#FFF}
.pg-toast.error .pg-toast-icon{background:#991B1B;color:#FFF}

.admin-payouts-page{min-height:100%;background:#F4F1EE;padding:28px 32px;font-family:'DM Sans',system-ui,sans-serif;color:#6d4c5b;font-size:13px}
.admin-payouts-page *{box-sizing:border-box}
.admin-payouts-page{--surface:#FFF;--border:#ead8c7;--primary:#6d4c5b;--primary-soft:#eddecc;--text:#111827;--muted:#b79c8b;--body:#7b5c69;max-width:1600px;margin:0 auto}

.pg-header{display:flex;align-items:flex-end;justify-content:space-between;gap:16px;margin-bottom:22px}
.pg-eyebrow{font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--muted);margin:0 0 4px}
.admin-payouts-page h1{font-size:22px;font-weight:700;color:var(--text);letter-spacing:-.3px;margin:0}
.pg-subtitle{margin:5px 0 0;color:var(--body);font-size:12px;font-weight:600}

.pg-summary{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:20px}
.pg-stat{background:var(--surface);border:1px solid var(--border);border-radius:.75rem;padding:14px 16px}
.pg-stat-label{font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--muted);margin-bottom:6px}
.pg-stat-value{font-size:20px;font-weight:700;color:var(--text)}
.pg-stat-sub{font-size:11px;color:var(--muted);margin-top:3px}
.pg-stat-value.amber{color:#92400E}
.pg-stat-value.blue{color:#3730A3}
.pg-stat-value.green{color:#065F46}

.pg-tabs{display:flex;gap:6px;margin-bottom:20px;flex-wrap:wrap}
.pg-tab{display:inline-flex;align-items:center;gap:6px;height:34px;padding:0 14px;border:1px solid var(--border);border-radius:.75rem;background:var(--surface);color:var(--body);font-size:12px;font-weight:700;text-decoration:none;transition:all .15s}
.pg-tab:hover{border-color:var(--primary);color:var(--primary)}
.pg-tab.active{border-color:var(--primary);background:var(--primary);color:#FFF}

.pg-card{background:var(--surface);border:1px solid var(--border);border-radius:.75rem;overflow:hidden;box-shadow:0 1px 2px rgba(28,25,23,.04)}
.pg-card-head{padding:14px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.pg-card-head h2{font-size:14px;font-weight:700;color:var(--text)}
.pg-card-count{font-size:11px;color:var(--muted);font-weight:600}

.pg-table{width:100%;border-collapse:collapse}
.pg-table thead tr{background:#F4F1EE}
.pg-table th{padding:10px 16px;font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--muted);text-align:left}
.pg-table tbody tr{border-top:1px solid var(--border);transition:background .1s}
.pg-table tbody tr:hover{background:#FAF8F6}
.pg-table td{padding:12px 16px;font-size:13px;vertical-align:middle}

.pg-badge{display:inline-flex;align-items:center;border-radius:20px;padding:3px 10px;font-size:10px;font-weight:800;letter-spacing:.05em;text-transform:uppercase}
.pg-badge.success{background:#ECFDF5;color:#065F46}
.pg-badge.processing{background:#EEF2FF;color:#3730A3}
.pg-badge.pending{background:#FFFBEB;color:#92400E}
.pg-badge.failed{background:#FEF2F2;color:#991B1B}

.pg-actions{display:flex;gap:6px}
.pg-btn{display:inline-flex;align-items:center;gap:4px;min-height:30px;padding:0 12px;border:1px solid var(--border);border-radius:.6rem;background:var(--surface);color:var(--primary);font-size:11px;font-weight:700;cursor:pointer;transition:all .15s;font-family:inherit}
.pg-btn:hover{background:var(--primary-soft);border-color:var(--primary)}
.pg-btn.primary{background:var(--primary);color:#FFF;border-color:var(--primary)}
.pg-btn.primary:hover{opacity:.85}
.pg-btn.danger{background:#FEF2F2;color:#991B1B;border-color:#FECACA}
.pg-btn.danger:hover{background:#FEE2E2}

.pg-empty{padding:48px 20px;text-align:center;color:var(--muted);font-size:13px}

.pg-pagination{display:flex;justify-content:space-between;align-items:center;padding:12px 16px;border-top:1px solid var(--border)}
.pg-page-info{font-size:12px;color:var(--muted)}
.pg-page-btns{display:flex;gap:6px}
.pg-page-btn{display:inline-flex;align-items:center;gap:6px;min-height:32px;padding:0 14px;border:1px solid var(--border);border-radius:999px;background:transparent;color:var(--primary);font-size:12px;font-weight:700;cursor:pointer;text-decoration:none;transition:all .14s;font-family:inherit}
.pg-page-btn:hover{border-color:var(--primary);background:var(--primary-soft)}
.pg-page-btn:disabled{opacity:.4;cursor:default}

/* Modal — matches supplier payout modal style */
.pg-modal-overlay{position:fixed;inset:0;background:rgba(34,24,19,.45);z-index:60;display:none;align-items:center;justify-content:center;padding:16px;backdrop-filter:blur(4px)}
.pg-modal{background:#FFFFFF;border:1px solid #ead8c7;border-radius:14px;max-width:460px;width:100%;overflow:hidden;box-shadow:0 18px 55px rgba(52,35,43,.16)}
.pg-modal-head{padding:16px 20px;border-bottom:1px solid #eee4dc;display:flex;align-items:center;gap:12px}
.pg-modal-head-icon{width:34px;height:34px;border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.pg-modal-head-icon svg{width:16px;height:16px}
.pg-modal-head h2{margin:0;font-size:14px;font-weight:750;color:#6d4c5b}
.pg-modal-body{padding:18px 20px}
.pg-modal-field{margin-bottom:14px}
.pg-modal-field label{display:block;font-size:10px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#a58b96;margin-bottom:5px}
.pg-modal-body textarea{width:100%;padding:9px 12px;border:1px solid #ead8c7;border-radius:9px;font-size:13px;color:#6d4c5b;font-family:inherit;resize:vertical;min-height:70px;transition:border-color .2s;box-sizing:border-box}
.pg-modal-body textarea:focus{outline:none;border-color:#6d4c5b;box-shadow:0 0 0 3px rgba(103,48,73,.08)}
.pg-modal-summary{background:#FAFAF9;border-radius:9px;padding:14px 16px;margin-bottom:16px}
.pg-modal-summary-row{display:flex;justify-content:space-between;align-items:center;padding:5px 0;font-size:12px;color:#7b5c69}
.pg-modal-summary-row:not(:last-child){border-bottom:1px solid #f0ebe5}
.pg-modal-summary-label{font-weight:600;color:#a58b96;font-size:11px}
.pg-modal-summary-value{font-weight:700;color:#6d4c5b}
.pg-modal-summary-value.amount{font-size:15px;color:#111827}
.pg-modal-info{background:#FAFAF9;border-radius:9px;padding:12px 14px;margin-bottom:16px}
.pg-modal-info p{font-size:12px;color:#A8A29E;margin:0;line-height:1.6}
.pg-modal-foot{padding:14px 20px;border-top:1px solid #eee4dc;display:flex;gap:8px;background:#faf7f3}
.pg-modal-foot button{flex:1;min-height:38px;border-radius:9px;font-size:12px;font-weight:700;cursor:pointer;transition:all .15s;font-family:inherit}
.pg-modal-cancel{border:1px solid #ead8c7;background:#fff;color:#6d4c5b}
.pg-modal-cancel:hover{background:#f5f0ea}
.pg-modal-submit{border:0;background:#6d4c5b;color:#fff}
.pg-modal-submit:hover{opacity:.85}
.pg-modal-submit.danger{background:#991B1B}
.pg-modal-submit.danger:hover{opacity:.85}
.pg-upload-area{border:1.5px dashed #ead8c7;border-radius:9px;padding:14px;cursor:pointer;transition:all .15s;text-align:center}
.pg-upload-area:hover{border-color:#6d4c5b;background:#faf7f3}
.pg-upload-placeholder{display:flex;flex-direction:column;align-items:center;gap:4px;color:#a58b96;font-size:12px;font-weight:600}
.pg-upload-preview{display:flex;align-items:center;gap:8px;font-size:12px;font-weight:600;color:#6d4c5b}
</style>

<section class="admin-payouts-page">
  <div class="pg-header">
    <div>
      <div class="pg-eyebrow">Finance</div>
      <h1>Supplier Payouts</h1>
      <p class="pg-subtitle">Review payout requests and manually transfer funds to suppliers.</p>
    </div>
  </div>

  <!-- Summary -->
  <div class="pg-summary">
    <div class="pg-stat">
      <div class="pg-stat-label">Pending Review</div>
      <div class="pg-stat-value amber"><?= $money($stats['review_total'] ?? 0) ?></div>
      <div class="pg-stat-sub"><?= (int)($stats['review_count'] ?? 0) ?> payout item<?= ($stats['review_count'] ?? 0) != 1 ? 's' : '' ?></div>
    </div>
    <div class="pg-stat">
      <div class="pg-stat-label">Not Yet Requested</div>
      <div class="pg-stat-value blue"><?= $money($stats['pending_total'] ?? 0) ?></div>
      <div class="pg-stat-sub"><?= (int)($stats['pending_count'] ?? 0) ?> payout item<?= ($stats['pending_count'] ?? 0) != 1 ? 's' : '' ?></div>
    </div>
    <div class="pg-stat">
      <div class="pg-stat-label">Total Paid Out</div>
      <div class="pg-stat-value green"><?= $money($stats['paid_total'] ?? 0) ?></div>
      <div class="pg-stat-sub"><?= (int)($stats['paid_count'] ?? 0) ?> payout<?= ($stats['paid_count'] ?? 0) != 1 ? 's' : '' ?></div>
    </div>
  </div>

  <!-- Tabs -->
  <div class="pg-tabs">
    <?php foreach ($tabs as $key => $tab): ?>
      <a href="<?= URLROOT ?>/admin/payouts?status=<?= $key ?>"
         class="pg-tab <?= $activeStatus === $key ? 'active' : '' ?>">
        <?= $tab['label'] ?>
      </a>
    <?php endforeach; ?>
  </div>

  <!-- Table -->
  <div class="pg-card">
    <div class="pg-card-head">
      <h2><?= $tabs[$activeStatus]['label'] ?? 'Payouts' ?></h2>
      <span class="pg-card-count"><?= $totalCount ?> supplier<?= $totalCount != 1 ? 's' : '' ?></span>
    </div>

    <?php if (empty($payouts)): ?>
      <div class="pg-empty">No payouts in this category.</div>
    <?php else: ?>
      <div style="overflow-x:auto">
        <table class="pg-table">
          <thead>
            <tr>
              <th>Supplier</th>
              <th>Bank Details</th>
              <th>Items</th>
              <th>Amount</th>
              <th>Requested</th>
              <?php if ($activeStatus === 'processing'): ?><th>Actions</th><?php endif; ?>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($payouts as $p): ?>
              <tr>
                <td>
                  <div style="font-weight:700"><?= $h($p['shop_name'] ?: $p['owner_name']) ?></div>
                  <div style="font-size:11px;color:var(--muted)"><?= $h($p['email']) ?></div>
                </td>
                <td>
                  <div style="font-weight:600"><?= $h($p['bank_code'] ?? '-') ?></div>
                  <div style="font-size:11px;color:var(--muted)"><?= $h($p['bank_account'] ?? '-') ?></div>
                </td>
                <td><?= (int)$p['payout_count'] ?> booking<?= (int)$p['payout_count'] != 1 ? 's' : '' ?></td>
                <td style="font-weight:700"><?= $money($p['total_amount']) ?></td>
                <td><?= $dateTime($p['last_requested'] ?? $p['first_requested']) ?></td>
                <?php if ($activeStatus === 'processing'): ?>
                  <td>
                    <div class="pg-actions">
                      <button type="button" class="pg-btn primary"
                              onclick="openMarkPaidModal(<?= (int)$p['supplier_id'] ?>, '<?= $h($p['shop_name'] ?: $p['owner_name']) ?>', <?= (float)$p['total_amount'] ?>, '<?= $h($p['bank_code'] ?? '-') ?>', '<?= $h($p['bank_account'] ?? '-') ?>')">
                        Mark Paid
                      </button>
                      <button type="button" class="pg-btn danger"
                              onclick="openRejectModal(<?= (int)$p['supplier_id'] ?>, '<?= $h($p['shop_name'] ?: $p['owner_name']) ?>')">
                        Reject
                      </button>
                    </div>
                  </td>
                <?php endif; ?>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <?php if ($totalPages > 1): ?>
        <div class="pg-pagination">
          <div class="pg-page-info">Page <?= $currentPage ?> of <?= $totalPages ?></div>
          <div class="pg-page-btns">
            <?php if ($currentPage > 1): ?>
              <a href="?status=<?= $activeStatus ?>&page=<?= $currentPage - 1 ?>" class="pg-page-btn">&larr; Previous</a>
            <?php endif; ?>
            <?php if ($currentPage < $totalPages): ?>
              <a href="?status=<?= $activeStatus ?>&page=<?= $currentPage + 1 ?>" class="pg-page-btn">Next &rarr;</a>
            <?php endif; ?>
          </div>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</section>

<!-- Mark Paid Modal -->
<div id="mark-paid-modal" class="pg-modal-overlay">
  <div class="pg-modal">
    <div class="pg-modal-head">
      <div class="pg-modal-head-icon" style="background:#ecfdf5;color:#047857">
        <i data-lucide="check-circle"></i>
      </div>
      <h2>Confirm Payout</h2>
    </div>
    <form id="mark-paid-form" class="pg-modal-body">
      <?= csrf_field() ?>
      <input type="hidden" name="supplier_id" id="paid-supplier-id">
      <div class="pg-modal-summary">
        <div class="pg-modal-summary-row">
          <span class="pg-modal-summary-label">Supplier</span>
          <span class="pg-modal-summary-value" id="paid-supplier-name"></span>
        </div>
        <div class="pg-modal-summary-row">
          <span class="pg-modal-summary-label">Bank</span>
          <span class="pg-modal-summary-value" id="paid-bank-info"></span>
        </div>
        <div class="pg-modal-summary-row">
          <span class="pg-modal-summary-label">Amount</span>
          <span class="pg-modal-summary-value amount" id="paid-amount"></span>
        </div>
      </div>
      <div class="pg-modal-field">
        <label>Transfer Note (optional)</label>
        <textarea name="note" placeholder="e.g., Transferred via KBZ Pay on 2026-06-25, ref #12345"></textarea>
      </div>
      <div class="pg-modal-field">
        <label>Payment Proof (optional)</label>
        <div class="pg-upload-area" id="upload-area" onclick="document.getElementById('proof-input').click()">
          <input type="file" name="proof" id="proof-input" accept="image/jpeg,image/png,image/webp,application/pdf" style="display:none">
          <div class="pg-upload-placeholder" id="upload-placeholder">
            <i data-lucide="upload-cloud" style="width:24px;height:24px;color:#a58b96"></i>
            <span>Click to upload transfer receipt</span>
            <span style="font-size:10px;color:#A8A29E">JPG, PNG, WebP or PDF · Max 5 MB</span>
          </div>
          <div class="pg-upload-preview" id="upload-preview" style="display:none">
            <i data-lucide="file-check" style="width:18px;height:18px;color:#047857"></i>
            <span id="upload-filename"></span>
            <button type="button" onclick="event.stopPropagation();clearProofUpload()" style="margin-left:auto;background:none;border:0;cursor:pointer;color:#991B1B;font-size:11px;font-weight:700">Remove</button>
          </div>
        </div>
      </div>
      <div class="pg-modal-info">
        <p>Upload a screenshot or receipt of the bank transfer as proof of payment.</p>
      </div>
      <div class="pg-modal-foot">
        <button type="button" onclick="closeModal('mark-paid-modal')" class="pg-modal-cancel">Cancel</button>
        <button type="submit" class="pg-modal-submit">Confirm Paid</button>
      </div>
    </form>
  </div>
</div>

<!-- Reject Modal -->
<div id="reject-modal" class="pg-modal-overlay">
  <div class="pg-modal">
    <div class="pg-modal-head">
      <div class="pg-modal-head-icon" style="background:#FEF2F2;color:#991B1B">
        <i data-lucide="x-circle"></i>
      </div>
      <h2>Reject Payout</h2>
    </div>
    <form id="reject-form" class="pg-modal-body">
      <?= csrf_field() ?>
      <input type="hidden" name="supplier_id" id="reject-supplier-id">
      <div class="pg-modal-summary">
        <div class="pg-modal-summary-row">
          <span class="pg-modal-summary-label">Supplier</span>
          <span class="pg-modal-summary-value" id="reject-supplier-name"></span>
        </div>
      </div>
      <div class="pg-modal-field">
        <label>Rejection Reason</label>
        <textarea name="reason" placeholder="e.g., Invalid bank account number" required></textarea>
      </div>
      <div class="pg-modal-info">
        <p>Funds will be returned to pending status. The supplier will be notified with your reason.</p>
      </div>
      <div class="pg-modal-foot">
        <button type="button" onclick="closeModal('reject-modal')" class="pg-modal-cancel">Cancel</button>
        <button type="submit" class="pg-modal-submit danger">Reject Payout</button>
      </div>
    </form>
  </div>
</div>

<div id="pg-toast" class="pg-toast"><span class="pg-toast-icon"></span><span class="pg-toast-msg"></span></div>

<script>
function showToast(msg, type = 'success') {
  const t = document.getElementById('pg-toast');
  t.className = 'pg-toast ' + type;
  t.querySelector('.pg-toast-icon').textContent = type === 'success' ? '✓' : '✕';
  t.querySelector('.pg-toast-msg').textContent = msg;
  t.classList.add('show');
  clearTimeout(t._timer);
  t._timer = setTimeout(() => t.classList.remove('show'), 3500);
}

function openMarkPaidModal(supplierId, name, amount, bankCode, bankAcct) {
  document.getElementById('paid-supplier-id').value = supplierId;
  document.getElementById('paid-supplier-name').textContent = name;
  document.getElementById('paid-bank-info').textContent = (bankCode || '-') + ' · ' + (bankAcct || '-');
  document.getElementById('paid-amount').textContent = new Intl.NumberFormat().format(amount) + ' MMK';
  document.getElementById('mark-paid-modal').style.display = 'flex';
  if (window.lucide) lucide.createIcons();
}
function openRejectModal(supplierId, name) {
  document.getElementById('reject-supplier-id').value = supplierId;
  document.getElementById('reject-supplier-name').textContent = name;
  document.getElementById('reject-modal').style.display = 'flex';
  if (window.lucide) lucide.createIcons();
}
function closeModal(id) {
  document.getElementById(id).style.display = 'none';
  clearProofUpload();
}

document.querySelectorAll('.pg-modal-overlay').forEach(el => {
  el.addEventListener('click', e => { if (e.target === el) { el.style.display = 'none'; clearProofUpload(); } });
});

// Proof upload preview
document.getElementById('proof-input')?.addEventListener('change', function() {
  const file = this.files[0];
  if (!file) return;
  document.getElementById('upload-placeholder').style.display = 'none';
  document.getElementById('upload-preview').style.display = 'flex';
  document.getElementById('upload-filename').textContent = file.name;
  if (window.lucide) lucide.createIcons();
});
function clearProofUpload() {
  const input = document.getElementById('proof-input');
  if (input) input.value = '';
  const ph = document.getElementById('upload-placeholder');
  const pv = document.getElementById('upload-preview');
  if (ph) ph.style.display = '';
  if (pv) pv.style.display = 'none';
}

async function submitPayoutAction(formId, endpoint, successMsg) {
  const form = document.getElementById(formId);
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = form.querySelector('[type="submit"]');
    const origText = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'Processing…';
    try {
      const resp = await fetch('<?= URLROOT ?>/admin/' + endpoint, {
        method: 'POST',
        body: new FormData(form)
      });
      const text = await resp.text();
      let data;
      try { data = JSON.parse(text); } catch(e) { data = null; }
      if (data && data.success) {
        closeModal(form.closest('.pg-modal-overlay')?.id || '');
        showToast(successMsg || data.message);
        setTimeout(() => location.reload(), 1200);
      } else {
        showToast(data?.error || 'Something went wrong. Please try again.', 'error');
        btn.disabled = false;
        btn.textContent = origText;
      }
    } catch (err) {
      showToast('Network error — please check your connection.', 'error');
      btn.disabled = false;
      btn.textContent = origText;
    }
  });
}

submitPayoutAction('mark-paid-form', 'markPayoutPaid', 'Payout confirmed! Supplier has been notified.');
submitPayoutAction('reject-form', 'rejectPayout', 'Payout rejected. Supplier has been notified.');
</script>
<?php
};
?>
<!DOCTYPE html>
<html lang="en">
<head><?php require_once APPROOT . '/views/dashboardLayout/head.php'; ?></head>
<body class="grid h-screen gap-0 bg-app-page" style="grid-template-columns: 280px 1fr;">
  <?php require APPROOT . '/views/dashboardLayout/adminsidebar.php'; ?>
</body>
</html>
