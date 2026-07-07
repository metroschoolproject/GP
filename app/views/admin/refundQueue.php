<?php
$refunds = $refunds ?? [];
$stats = $stats ?? [];
$currentPage = (int)($currentPage ?? 1);
$totalPages = (int)($totalPages ?? 1);
$totalCount = (int)($totalCount ?? count($refunds));
$perPage = (int)($perPage ?? 20);

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

$dashboardContent = function () use ($refunds, $stats, $h, $money, $dateTime, $pendingCount, $processingCount, $completedCount, $pendingAmount, $completedToday, $flash, $currentPage, $totalPages, $totalCount, $perPage) {
?>
	<style>
	    :root {
	        --rq-bg:#fbfaf8;
	        --rq-surface:#fffdfb;
	        --rq-panel:#fff8ef;
	        --rq-border:#ead8c7;
	        --rq-border-strong:#dec8b6;
	        --rq-text:#5b3f4a;
	        --rq-ink:#3f2a32;
	        --rq-muted:#a98c99;
	        --rq-muted2:#7b5c69;
	        --rq-plum:#6d4c5b;
	        --rq-green:#059669;
	        --rq-red:#dc2626;
	    }
	    .refund-queue-shell{min-height:100%;padding:34px 32px 46px;background:var(--rq-bg)}
	    .rq-page{max-width:1320px;margin:0 auto;color:var(--rq-text)}
	    .rq-header{display:flex;align-items:flex-end;justify-content:space-between;gap:20px}
	    .rq-kicker{margin:0 0 8px;color:var(--rq-muted);font-size:10px;font-weight:850;letter-spacing:.2em;text-transform:uppercase}
	    .rq-title{margin:0;font-family:"Playfair Display",serif;font-size:clamp(34px,3.6vw,48px);font-weight:650;line-height:.96;color:var(--rq-text)}
	    .rq-subtitle{max-width:420px;margin:0;color:#a58b96;font-size:12px;font-weight:600;line-height:1.5}

	    .rq-stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin-top:26px}
	    .rq-stat{position:relative;overflow:hidden;min-height:122px;padding:20px 22px;border:1px solid var(--rq-border);border-radius:8px;background:var(--rq-surface);box-shadow:0 12px 30px rgba(52,35,43,.035)}
	    .rq-stat::after{content:"";position:absolute;left:0;right:0;bottom:0;height:3px;background:linear-gradient(90deg,rgba(109,76,91,.34),rgba(216,180,106,.52));opacity:.75}
	    .rq-stat-label{display:flex;align-items:center;gap:8px;font-size:10px;font-weight:850;text-transform:uppercase;letter-spacing:.12em;color:var(--rq-muted)}
	    .rq-stat-value{margin-top:13px;color:var(--rq-text);font-size:26px;font-weight:800;line-height:1.05;font-variant-numeric:tabular-nums}
	    .rq-stat-note{margin-top:9px;font-size:11px;font-weight:650;color:#a58b96}
	    .rq-stat-badge{display:inline-flex;align-items:center;justify-content:center;min-width:22px;height:22px;padding:0 7px;border-radius:999px;font-size:10px;font-weight:850;letter-spacing:0}
	    .rq-stat-badge.warn{background:#fff4dc;color:#92400e}
	    .rq-stat-badge.info{background:#eef2ff;color:#3730a3}

	    .rq-table-wrap{margin-top:24px;overflow:hidden;border:1px solid var(--rq-border);border-radius:8px;background:var(--rq-surface);box-shadow:0 18px 45px rgba(52,35,43,.06)}
	    .rq-table-scroll{overflow-x:auto}
	    .rq-table{width:100%;min-width:1080px;font-size:13px;border-collapse:collapse;table-layout:fixed}
	    .rq-table th{padding:15px 18px;text-align:left;font-size:10px;font-weight:850;text-transform:uppercase;letter-spacing:.09em;color:var(--rq-muted);border-bottom:1px solid var(--rq-border);background:#fbf7f2}
	    .rq-table td{padding:17px 18px;border-bottom:1px solid #f0e3d8;vertical-align:middle}
	    .rq-table tr:last-child td{border-bottom:none}
	    .rq-table tbody tr{transition:background .16s ease}
	    .rq-table tbody tr:hover{background:#fffaf5}
	    .rq-col-booking{width:14%}
	    .rq-col-customer{width:18%}
	    .rq-col-amount{width:12%}
	    .rq-col-policy{width:22%}
	    .rq-col-status{width:12%}
	    .rq-col-requested{width:13%}
	    .rq-col-actions{width:150px}
	    .rq-booking-link{display:inline-flex;color:var(--rq-plum);font-size:12px;font-weight:800;text-decoration:none}
	    .rq-booking-link:hover{text-decoration:underline;text-underline-offset:3px}
	    .rq-name{font-weight:800;color:var(--rq-ink);line-height:1.2}
	    .rq-email{margin-top:3px;font-size:11px;color:var(--rq-muted);line-height:1.35;word-break:break-word}
	    .rq-amount{font-weight:850;font-variant-numeric:tabular-nums;color:var(--rq-plum);line-height:1.3}
	    .rq-policy{max-width:300px;color:var(--rq-muted2);font-size:12px;font-weight:600;line-height:1.38}
	    .rq-requested{color:var(--rq-muted2);font-size:11.5px;font-weight:650;line-height:1.45}
	    .rq-status-note{margin-top:5px;color:var(--rq-muted);font-size:10.5px;font-weight:700}

	    .rq-badge{display:inline-flex;align-items:center;justify-content:center;min-height:26px;padding:0 12px;border-radius:999px;font-size:10px;font-weight:850;text-transform:uppercase;letter-spacing:.04em}
	    .rq-badge.pending{background:#fff7e8;color:#a85b0b}
	    .rq-badge.processing{background:#eef2ff;color:#3730a3}
	    .rq-badge.completed{background:#ecfdf5;color:#065f46}
	    .rq-badge.rejected{background:#fef2f2;color:#991b1b}

	    .rq-actions{display:grid;grid-template-columns:1fr;gap:7px;justify-items:stretch}
	    .rq-btn{display:inline-flex;align-items:center;justify-content:center;gap:5px;min-height:36px;padding:0 15px;border:1px solid transparent;border-radius:8px;font-family:inherit;font-size:11px;font-weight:850;cursor:pointer;transition:transform .14s ease,box-shadow .14s ease,background .14s ease}
	    .rq-btn:hover{transform:translateY(-1px);box-shadow:0 8px 18px rgba(52,35,43,.12)}
	    .rq-btn-primary{background:var(--rq-plum);color:#fff}
	    .rq-btn-primary:hover{background:#5a3e4a}
	    .rq-btn-success{background:var(--rq-green);color:#fff}
	    .rq-btn-success:hover{background:#047857}
	    .rq-btn-danger{background:var(--rq-red);color:#fff}
	    .rq-btn-danger:hover{background:#b91c1c}
	    .rq-btn-ghost{background:#f8f1ea;color:var(--rq-muted2);border-color:#ead8c7}

	    .rq-empty{text-align:center;padding:54px 20px;color:var(--rq-muted)}
	    .rq-empty svg{margin:auto;color:#b79c8b}
	    .pagination{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:14px 18px;border-top:1px solid var(--rq-border);background:#fff}
	    .page-info{font-size:12px;color:var(--rq-muted);font-weight:650}
	    .page-btns{display:flex;align-items:center;gap:5px;flex-wrap:wrap;justify-content:flex-end}
	    .page-btn{display:inline-flex;align-items:center;justify-content:center;min-width:32px;height:32px;padding:0 10px;border:1px solid var(--rq-border);border-radius:8px;background:#fff;color:var(--rq-muted2);font-size:12px;font-weight:800;text-decoration:none}
	    .page-btn:hover{background:#f9f6f2;color:var(--rq-plum)}
	    .page-btn.active{background:var(--rq-plum);border-color:var(--rq-plum);color:#fff}

	    .rq-modal-overlay{display:none;position:fixed;inset:0;z-index:100;background:rgba(38,24,31,.38);align-items:center;justify-content:center;padding:18px}
	    .rq-modal-overlay.show{display:flex}
	    .rq-modal{width:min(440px,92vw);padding:24px;border:1px solid var(--rq-border);border-radius:8px;background:var(--rq-surface);box-shadow:0 24px 64px rgba(52,35,43,.2)}
	    .rq-modal-title{margin-bottom:16px;color:var(--rq-ink);font-size:18px;font-weight:850}
	    .rq-modal-label{display:block;margin-bottom:5px;color:var(--rq-muted);font-size:10px;font-weight:850;text-transform:uppercase;letter-spacing:.08em}
	    .rq-modal-input{width:100%;box-sizing:border-box;margin-bottom:13px;padding:9px 12px;border:1px solid var(--rq-border);border-radius:8px;background:#fff;color:var(--rq-ink);font-family:inherit;font-size:13px}
	    .rq-modal-input:focus{outline:none;border-color:var(--rq-plum);box-shadow:0 0 0 3px rgba(109,76,91,.12)}
	    .rq-modal-actions{display:flex;gap:8px;justify-content:flex-end;margin-top:8px}

	    .rq-flash{margin:18px 0 0;padding:12px 16px;border-radius:8px;font-size:13px;font-weight:700}
	    .rq-flash-success{background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0}
	    .rq-flash-error{background:#fef2f2;color:#991b1b;border:1px solid #fecaca}
	    @media(max-width:1100px){
	        .rq-stats{grid-template-columns:1fr}
	        .rq-header{align-items:flex-start;flex-direction:column}
	    }
	    @media(max-width:700px){
	        .refund-queue-shell{padding:24px 16px 36px}
	        .rq-title{font-size:32px}
	        .rq-stat{min-height:0}
	        .pagination{align-items:flex-start;flex-direction:column}
	        .rq-modal-actions{flex-direction:column-reverse}
	        .rq-modal-actions .rq-btn{width:100%}
	    }
	</style>

	<div class="rq-page">
	    <div class="rq-header">
	        <div>
	            <p class="rq-kicker">Financial Operations</p>
	            <h1 class="rq-title">Refund Queue</h1>
	        </div>
	        <p class="rq-subtitle">Review pending refund amounts, upload transfer proof, and close completed refunds from one queue.</p>
	    </div>

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
	        <div class="rq-table-scroll">
	        <table class="rq-table">
	            <colgroup>
	                <col class="rq-col-booking">
	                <col class="rq-col-customer">
	                <col class="rq-col-amount">
	                <col class="rq-col-policy">
	                <col class="rq-col-status">
	                <col class="rq-col-requested">
	                <col class="rq-col-actions">
	            </colgroup>
	            <thead><tr>
	                <th>Booking</th><th>Customer</th><th>Amount</th><th>Policy</th><th>Status</th><th>Requested</th><th>Actions</th>
	            </tr></thead>
	            <tbody>
	            <?php foreach ($refunds as $rf):
	                $rId = (int)($rf['id']??0); $rStatus = (string)($rf['status']??'pending'); $bId = (int)($rf['booking_id']??0); ?>
	                <tr>
	                    <td><a class="rq-booking-link" href="<?= URLROOT ?>/admin/bookingDetail/<?= $bId ?>">#<?= $h($rf['booking_ref']??$bId) ?></a></td>
	                    <td>
	                        <div class="rq-name"><?= $h($rf['customer_name']??'—') ?></div>
	                        <div class="rq-email"><?= $h($rf['customer_email']??'') ?></div>
                    </td>
                    <td class="rq-amount"><?= $money($rf['amount']??0) ?></td>
                    <td><div class="rq-policy"><?= $h($rf['policy_reason']??$rf['reason']??'—') ?></div></td>
	                    <td>
	                        <span class="rq-badge <?= $h($rStatus) ?>"><?= ucfirst($rStatus) ?></span>
	                        <?php if(!empty($rf['refund_bank_name'])): ?><div class="rq-status-note">via <?= $h($rf['refund_bank_name']) ?></div><?php endif; ?>
	                    </td>
	                    <td class="rq-requested"><?= $dateTime($rf['requested_at']) ?></td>
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
	        </div>
	        <?php endif; ?>
        <?php
        if (isset($currentPage, $totalPages, $totalCount, $perPage)) {
            $baseParams = '';
            require APPROOT . '/views/partials/_pagination.php';
        }
        ?>
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
	            <button type="button" class="rq-btn rq-btn-ghost" onclick="closeModals()">Cancel</button>
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
	            <button type="button" class="rq-btn rq-btn-ghost" onclick="closeModals()">Cancel</button>
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
<head><?php $pageTitle = 'Refund Queue — Admin'; ?>
    <?php require_once APPROOT . '/views/dashboardLayout/head.php'; ?></head>
<body class="grid h-screen gap-0 bg-app-page" style="grid-template-columns:280px 1fr">
    <?php require APPROOT . '/views/dashboardLayout/adminsidebar.php'; ?>
</body></html>
