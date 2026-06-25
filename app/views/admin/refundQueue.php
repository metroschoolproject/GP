<?php
$refunds = $refunds ?? [];
$stats = $stats ?? [];

$dashboardTitle = 'Payments';
$dashboardCrumb = 'Refund queue';
$dashboardContentClass = 'refund-queue-shell';
$dashboardBreadcrumbs = [
    ['label' => 'Payments', 'url' => null],
    ['label' => 'Refund queue', 'url' => null],
];

$h = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$money = fn($v) => number_format((float)$v, 0) . ' MMK';
$dateTime = static function ($value, string $fallback = '-') {
    if (empty($value)) return $fallback;
    $ts = strtotime((string)$value);
    return $ts ? date('M j, Y — g:i A', $ts) : $fallback;
};

$pendingCount = (int)($stats['pending_count'] ?? 0);
$processingCount = (int)($stats['processing_count'] ?? 0);
$completedCount = (int)($stats['completed_count'] ?? 0);
$pendingAmount = (float)($stats['pending_amount'] ?? 0);
$completedToday = (float)($stats['completed_today'] ?? 0);

$flash = $_SESSION['admin_flash'] ?? null;
unset($_SESSION['admin_flash']);

$dashboardContent = function () use ($refunds, $stats, $h, $money, $dateTime, $pendingCount, $processingCount, $completedCount, $pendingAmount, $completedToday, $flash) {
?>
<style>
    :root { --rq-bg: #fbfbf9; --rq-surface: #FFFFFF; --rq-border: #ead8c7; --rq-text: #6d4c5b; --rq-muted: #9b7d89; --rq-muted2: #7b5c69; --rq-plum: #6d4c5b; }
    .refund-queue-shell { min-height:100%; padding:30px; background:var(--rq-bg) }
    .rq-page { max-width:1200px; margin:0 auto; color:var(--rq-text) }
    .rq-kicker { margin:0 0 7px; color:var(--rq-muted); font-size:10px; font-weight:800; letter-spacing:.18em; text-transform:uppercase }
    .rq-title { margin:0; font-family:"Playfair Display",serif; font-size:clamp(28px,3vw,40px); font-weight:650; line-height:1 }

    .rq-stats { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:12px; margin-top:24px }
    .rq-stat { padding:18px; border:1px solid var(--rq-border); border-radius:12px; background:var(--rq-surface) }
    .rq-stat-label { font-size:10px; font-weight:800; text-transform:uppercase; letter-spacing:.1em; color:var(--rq-muted) }
    .rq-stat-value { margin-top:6px; font-size:22px; font-weight:750; font-variant-numeric:tabular-nums }
    .rq-stat-note { margin-top:4px; font-size:11px; color:#a58b96 }
    .rq-stat-badge { display:inline-block; padding:2px 8px; border-radius:999px; font-size:10px; font-weight:700; margin-left:6px; vertical-align:2px }
    .rq-stat-badge.warn { background:#FFFBEB; color:#92400E }
    .rq-stat-badge.info { background:#EEF2FF; color:#3730A3 }

    .rq-table-wrap { margin-top:24px; overflow-x:auto; border:1px solid var(--rq-border); border-radius:15px; background:var(--rq-surface); box-shadow:0 18px 45px rgba(52,35,43,.06) }
    .rq-table { width:100%; font-size:13px; border-collapse:collapse }
    .rq-table th { padding:12px 16px; text-align:left; font-size:10px; font-weight:800; text-transform:uppercase; letter-spacing:.08em; color:var(--rq-muted); border-bottom:1px solid var(--rq-border); background:#f9f6f2 }
    .rq-table td { padding:12px 16px; border-bottom:1px solid var(--rq-border); vertical-align:middle }
    .rq-table tr:last-child td { border-bottom:none }

    .rq-name { font-weight:600 }
    .rq-email { font-size:11px; color:var(--rq-muted) }
    .rq-amount { font-weight:700; font-variant-numeric:tabular-nums; color:var(--rq-plum) }
    .rq-policy { font-size:11px; color:var(--rq-muted2); max-width:200px; line-height:1.35 }

    .rq-badge { display:inline-flex; align-items:center; padding:3px 10px; border-radius:999px; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.04em }
    .rq-badge.pending { background:#fffbeb; color:#92400E }
    .rq-badge.processing { background:#eff6ff; color:#3730A3 }
    .rq-badge.completed { background:#ecfdf5; color:#065F46 }
    .rq-badge.rejected { background:#fef2f2; color:#991B1B }

    .rq-actions { display:flex; gap:6px; flex-wrap:wrap }
    .rq-btn { display:inline-flex; align-items:center; gap:4px; min-height:32px; padding:0 14px; border:none; border-radius:8px; font-size:11px; font-weight:700; cursor:pointer; transition:.14s; font-family:inherit }
    .rq-btn-primary { background:var(--rq-plum); color:#FFFFFF }
    .rq-btn-primary:hover { background:#5a3e4a }
    .rq-btn-success { background:#16a34a; color:#fff }
    .rq-btn-success:hover { background:#15803d }
    .rq-btn-danger { background:#dc2626; color:#fff }
    .rq-btn-danger:hover { background:#b91c1c }

    .rq-empty { text-align:center; padding:40px 20px; color:var(--rq-muted) }

    .rq-modal-overlay { display:none; position:fixed; inset:0; z-index:100; background:rgba(0,0,0,.3); align-items:center; justify-content:center }
    .rq-modal-overlay.show { display:flex }
    .rq-modal { width:min(420px,92vw); padding:24px; border-radius:16px; background:var(--rq-surface); box-shadow:0 24px 64px rgba(52,35,43,.18) }
    .rq-modal-title { font-size:16px; font-weight:700; margin-bottom:16px }
    .rq-modal-label { display:block; font-size:10px; font-weight:800; text-transform:uppercase; letter-spacing:.08em; color:var(--rq-muted); margin-bottom:4px }
    .rq-modal-input { width:100%; padding:8px 12px; border:1px solid var(--rq-border); border-radius:8px; font-size:13px; font-family:inherit; margin-bottom:12px; box-sizing:border-box }
    .rq-modal-input:focus { outline:none; border-color:var(--rq-plum); box-shadow:0 0 0 3px rgba(109,76,91,.12) }
    .rq-modal-actions { display:flex; gap:8px; justify-content:flex-end; margin-top:8px }

    .rq-flash { margin-bottom:18px; padding:12px 16px; border-radius:10px; font-size:13px; font-weight:600 }
    .rq-flash-success { background:#ecfdf5; color:#065F46; border:1px solid #a7f3d0 }
    .rq-flash-error { background:#fef2f2; color:#991B1B; border:1px solid #fecaca }
</style>

<div class="rq-page">
    <p class="rq-kicker">Financial Operations</p>
    <h1 class="rq-title">Refund Queue</h1>

    <?php if ($flash): ?>
        <?php $flashOK = strpos($flash,'updated')!==false||strpos($flash,'completed')!==false||strpos($flash,'processed')!==false; ?>
        <div class="rq-flash <?= $flashOK?'rq-flash-success':'rq-flash-error' ?>" role="alert"><?= $h($flash) ?></div>
    <?php endif; ?>

    <div class="rq-stats">
        <div class="rq-stat">
            <div class="rq-stat-label">Pending<span class="rq-stat-badge warn"><?= $pendingCount ?></span></div>
            <div class="rq-stat-value"><?= $money($pendingAmount) ?></div>
            <div class="rq-stat-note">Awaiting processing</div>
        </div>
        <div class="rq-stat">
            <div class="rq-stat-label">In Progress<span class="rq-stat-badge info"><?= $processingCount ?></span></div>
            <div class="rq-stat-value"><?= $processingCount ?> active</div>
            <div class="rq-stat-note">Proof submitted</div>
        </div>
        <div class="rq-stat">
            <div class="rq-stat-label">Completed Today</div>
            <div class="rq-stat-value"><?= $money($completedToday) ?></div>
            <div class="rq-stat-note"><?= $completedCount ?> total</div>
        </div>
    </div>

    <div class="rq-table-wrap">
        <?php if (empty($refunds)): ?>
        <div class="rq-empty">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M22 12c0-5.52-4.48-10-10-10S2 6.48 2 12s4.48 10 10 10 10-4.48 10-10z"/><path d="M8 15s1.5-2 4-2 4 2 4 2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>
            <p style="font-size:14px;margin:8px 0 0">No pending refunds</p>
            <p style="font-size:12px;color:#a58b96;margin:4px 0 0">All refund requests have been processed.</p>
        </div>
        <?php else: ?>
        <table class="rq-table">
            <thead><tr>
                <th>Booking</th><th>Customer</th><th>Amount</th><th>Policy</th><th>Status</th><th>Requested</th><th class="sr-only">Actions</th>
            </tr></thead>
            <tbody>
            <?php foreach ($refunds as $rf):
                $rId = (int)($rf['id']??0); $rStatus = (string)($rf['status']??'pending'); $bId = (int)($rf['booking_id']??0); ?>
                <tr>
                    <td><a href="<?= URLROOT ?>/admin/bookingDetail/<?= $bId ?>" style="color:var(--rq-plum);font-weight:600;text-decoration:none;font-size:12px">#<?= $h($rf['booking_ref']??$bId) ?></a></td>
                    <td>
                        <div class="rq-name"><?= $h($rf['customer_name']??'—') ?></div>
                        <div class="rq-email"><?= $h($rf['customer_email']??'') ?></div>
                    </td>
                    <td class="rq-amount"><?= $money($rf['amount']??0) ?></td>
                    <td><div class="rq-policy"><?= $h($rf['policy_reason']??$rf['reason']??'—') ?></div></td>
                    <td>
                        <span class="rq-badge <?= $h($rStatus) ?>"><?= ucfirst($rStatus) ?></span>
                        <?php if(!empty($rf['refund_bank_name'])): ?><div style="font-size:10px;color:var(--rq-muted);margin-top:2px">via <?= $h($rf['refund_bank_name']) ?></div><?php endif; ?>
                    </td>
                    <td style="font-size:11px;color:var(--rq-muted2)"><?= $dateTime($rf['requested_at']) ?></td>
                    <td>
                        <div class="rq-actions">
                            <?php if($rStatus==='pending'): ?>
                            <button class="rq-btn rq-btn-primary" onclick="openProcessModal(<?=$rId?>,<?=(float)($rf['amount']??0)?>)">Process</button>
                            <button class="rq-btn rq-btn-danger" onclick="openRejectModal(<?=$rId?>)">Reject</button>
                            <?php elseif($rStatus==='processing'): ?>
                            <button class="rq-btn rq-btn-success" onclick="completeRefund(<?=$rId?>)">Complete</button>
                            <button class="rq-btn rq-btn-primary" onclick="openProcessModal(<?=$rId?>,<?=(float)($rf['amount']??0)?>)">Update</button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<!-- Process Modal -->
<div class="rq-modal-overlay" id="processModal"><div class="rq-modal">
    <div class="rq-modal-title">Process Refund</div>
    <form id="processForm" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="refund_id" id="processRefundId">
        <div id="processAmount" style="margin-bottom:12px;font-size:14px;font-weight:700;color:var(--rq-plum)"></div>
        <label class="rq-modal-label">Bank / Channel</label>
        <select name="bank_name" class="rq-modal-input" required>
            <option value="">Select</option><option>KBZ Pay</option><option>Wave Money</option><option>AYA Pay</option><option>Yoma Bank</option><option>CB Bank</option>
        </select>
        <label class="rq-modal-label">Transaction Ref</label>
        <input type="text" name="transaction_ref" class="rq-modal-input" required>
        <label class="rq-modal-label">Proof of Transfer</label>
        <input type="file" name="slip_image" class="rq-modal-input" accept=".jpg,.jpeg,.png,.webp,.pdf" required>
        <label class="rq-modal-label">Note (optional)</label>
        <input type="text" name="note" class="rq-modal-input">
        <div class="rq-modal-actions">
            <button type="button" class="rq-btn" onclick="closeModals()" style="background:#f1f1f1;color:#374151">Cancel</button>
            <button type="submit" class="rq-btn rq-btn-primary">Submit Proof</button>
        </div>
    </form>
</div></div>

<!-- Reject Modal -->
<div class="rq-modal-overlay" id="rejectModal"><div class="rq-modal">
    <div class="rq-modal-title">Reject Refund</div>
    <form id="rejectForm" method="POST">
        <input type="hidden" name="refund_id" id="rejectRefundId">
        <label class="rq-modal-label">Reason</label>
        <textarea name="reason" class="rq-modal-input" rows="3" required></textarea>
        <div class="rq-modal-actions">
            <button type="button" class="rq-btn" onclick="closeModals()" style="background:#f1f1f1;color:#374151">Cancel</button>
            <button type="submit" class="rq-btn rq-btn-danger">Reject</button>
        </div>
    </form>
</div></div>

<script>
(function(){
var csrf = '<?= $h(csrf_token()) ?>';
function api(url, body, file) {
    var headers = { 'X-CSRF-Token': csrf };
    if (!file) { headers['Content-Type'] = 'application/json'; body = JSON.stringify(Object.fromEntries(body)); }
    return fetch(url, { method:'POST', headers:headers, body:body }).then(r=>r.json());
}
window.openProcessModal = function(id, amt) {
    document.getElementById('processRefundId').value = id;
    document.getElementById('processAmount').textContent = 'Amount: ' + amt.toLocaleString('en-US') + ' MMK';
    document.getElementById('processModal').classList.add('show');
};
window.openRejectModal = function(id) {
    document.getElementById('rejectRefundId').value = id;
    document.getElementById('rejectModal').classList.add('show');
};
window.closeModals = function() {
    document.querySelectorAll('.rq-modal-overlay').forEach(m=>m.classList.remove('show'));
};
window.completeRefund = function(id) {
    var fd = new FormData(); fd.append('csrf_token', csrf); fd.append('refund_id', id);
    api('<?= URLROOT ?>/booking/adminCompleteRefund', fd, true).then(function(d) {
        if (d.error) alert(d.error); else window.location.reload();
    }).catch(function(){alert('Network error')});
};
document.querySelectorAll('.rq-modal-overlay').forEach(function(o){
    o.addEventListener('click', function(e){if(e.target===this)closeModals();});
});
document.getElementById('processForm').addEventListener('submit', function(e){
    e.preventDefault();
    var fd = new FormData(this); fd.append('csrf_token', csrf);
    api('<?= URLROOT ?>/booking/adminProcessRefund', fd, true).then(function(d){
        if (d.error) alert(d.error); else { closeModals(); window.location.reload(); }
    }).catch(function(){alert('Network error')});
});
document.getElementById('rejectForm').addEventListener('submit', function(e){
    e.preventDefault();
    var fd = new FormData(this); fd.append('csrf_token', csrf);
    api('<?= URLROOT ?>/booking/adminRejectRefund', fd, true).then(function(d){
        if (d.error) alert(d.error); else { closeModals(); window.location.reload(); }
    }).catch(function(){alert('Network error')});
});
})();
</script>
<?php }; ?>
<!DOCTYPE html><html lang="en">
<head><?php require_once APPROOT . '/views/dashboardLayout/head.php'; ?></head>
<body class="grid h-screen gap-0 bg-app-page" style="grid-template-columns:280px 1fr">
    <?php require APPROOT . '/views/dashboardLayout/adminsidebar.php'; ?>
</body></html>
