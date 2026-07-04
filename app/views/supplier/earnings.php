<?php
$earnings          = $earnings ?? [];
$earningsBreakdown = $earningsBreakdown ?? [];
$grossEarnings     = $grossEarnings ?? [];
$supplier          = $supplier ?? [];
$supplierId        = (int)($supplierId ?? 0);
$currentPage       = (int)($currentPage ?? 1);
$totalPages        = (int)($totalPages ?? 1);
$totalPayouts      = (int)($totalPayouts ?? 0);
$latestPaidPayout       = $latestPaidPayout ?? null;
$latestProcessingPayout = $latestProcessingPayout ?? null;

$h     = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$money = fn($v) => number_format((float)$v, 0) . ' MMK';
$date  = fn($v) => $v ? date('M d, Y', strtotime($v)) : '—';
$dateShort = fn($v) => $v ? date('M d', strtotime($v)) : '—';

$dashboardTitle          = 'Supplier';
$dashboardCrumb          = 'Earnings';
$dashboardBreadcrumbs    = [
    ['label' => 'Dashboard', 'url' => URLROOT . '/supplier/dashboard'],
    ['label' => 'Earnings',  'url' => null],
];
$dashboardContentClass = 'bg-[#F4F1EE] px-0 py-0 overflow-y-auto';
$dashboardContent = function () use (
    $earnings, $earningsBreakdown, $grossEarnings, $supplier,
    $supplierId, $currentPage, $totalPages, $totalPayouts,
    $latestPaidPayout, $latestProcessingPayout,
    $h, $money, $date, $dateShort
) {
    $pendingAmt    = (float)($earnings['pending_amount']    ?? 0);
    $pendingCnt    = (int)  ($earnings['pending_count']     ?? 0);
    $processingAmt = (float)($earnings['processing_amount'] ?? 0);
    $paidAmt       = (float)($earnings['paid_amount']       ?? 0);
    $paidCnt       = (int)  ($earnings['paid_count']        ?? 0);
    $totalEarned   = (float)($earnings['total_earned']      ?? 0);

    $grossTotal    = (float)($grossEarnings['gross_earnings']        ?? 0);
    $feeTotal      = (float)($grossEarnings['platform_fees']         ?? 0);
    $netTotal      = (float)($grossEarnings['net_earnings']          ?? 0);
    $completedCnt  = (int)  ($grossEarnings['completed_booking_count'] ?? 0);

    $hasPending    = $pendingAmt    > 0;
    $hasProcessing = $processingAmt > 0;

    // Derive fee rate from paid data
    $feeRate = ($grossTotal > 0) ? round(($feeTotal / $grossTotal) * 100, 1) : 5.0;
?>
<link rel="stylesheet" href="<?= URLROOT ?>/public/css/supplier-earnings.css?v=<?= filemtime(APPROOT . '/../public/css/supplier-earnings.css') ?>">
<script src="<?= URLROOT ?>/public/js/supplier-toast.js"></script>

<section class="er-page mx-auto max-w-[1600px] space-y-4 px-5 py-6 text-[13px] antialiased" style="font-family:'Poppins',system-ui,sans-serif;color:#6d4c5b">

    <!-- ── Page header ───────────────────────────────────────────── -->
    <div class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p style="margin-bottom:4px;color:#A8A29E;font-size:11px;font-weight:650">Supplier workspace</p>
            <h1 style="margin:0;color:#6d4c5b;font-family:'Poppins',system-ui,sans-serif;font-size:22px;font-weight:700;letter-spacing:-.02em;line-height:1.2">Your Earnings</h1>
            <p style="margin-top:6px;color:#7b5c69;font-size:12px;font-weight:500">Track your income, fees, and payout history in one place.</p>
        </div>
        <a href="<?= URLROOT ?>/supplier/paymentHistory" class="er-nav-link">
            <i data-lucide="scroll-text"></i>
            Statement
        </a>
    </div>

    <!-- ── KPI row ───────────────────────────────────────────────── -->
    <div class="er-kpi-row">

        <!-- Available Balance -->
        <div class="er-kpi er-kpi--balance">
            <div class="er-kpi-icon" style="background:#fff7ed;color:#b45309">
                <i data-lucide="wallet"></i>
            </div>
            <p class="er-kpi-label">Available to withdraw</p>
            <p class="er-kpi-value"><?= number_format($pendingAmt, 0) ?></p>
            <p class="er-kpi-sub"><?= $pendingCnt ?> booking<?= $pendingCnt !== 1 ? 's' : '' ?> ready · MMK</p>
            <div class="er-kpi-divider">
                <?php if ($hasPending): ?>
                <button id="cashout-btn" type="button" class="er-kpi-action">
                    <i data-lucide="send"></i>
                    Request Payout
                </button>
                <?php elseif ($hasProcessing): ?>
                <span class="er-kpi-processing">
                    <i data-lucide="clock"></i>
                    Payout Under Review
                </span>
                <?php else: ?>
                <p class="er-kpi-sub">No pending payouts</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Total Earned -->
        <div class="er-kpi er-kpi--earned">
            <div class="er-kpi-icon" style="background:#ecfdf5;color:#047857">
                <i data-lucide="badge-check"></i>
            </div>
            <p class="er-kpi-label">Total earned</p>
            <p class="er-kpi-value"><?= number_format($totalEarned, 0) ?></p>
            <p class="er-kpi-sub">Net after fees · MMK</p>
            <div class="er-kpi-divider">
                <div class="er-kpi-stat-row">
                    <div>
                        <p class="er-kpi-stat-label">Platform fee</p>
                        <p class="er-kpi-stat-value er-kpi-stat-value--amber"><?= $feeRate ?>%</p>
                    </div>
                    <div>
                        <p class="er-kpi-stat-label">Bookings</p>
                        <p class="er-kpi-stat-value"><?= $completedCnt ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Paid Out -->
        <div class="er-kpi">
            <div class="er-kpi-icon" style="background:#f0f9ff;color:#0284c7">
                <i data-lucide="banknote"></i>
            </div>
            <p class="er-kpi-label">Paid out</p>
            <p class="er-kpi-value"><?= $money($paidAmt) ?></p>
            <p class="er-kpi-sub"><?= $paidCnt ?> payout<?= $paidCnt !== 1 ? 's' : '' ?> settled</p>
            <?php if ($latestPaidPayout): ?>
            <div class="er-kpi-divider">
                <div class="er-kpi-stat-row">
                    <div>
                        <p class="er-kpi-stat-label">Last payout</p>
                        <p class="er-kpi-stat-value" style="font-size:13px"><?= $date($latestPaidPayout['verified_at'] ?? $latestPaidPayout['created_at']) ?></p>
                    </div>
                    <?php if (!empty($latestPaidPayout['payout_batch_id'])): ?>
                    <div>
                        <p class="er-kpi-stat-label">Ref</p>
                        <p class="er-kpi-stat-value" style="font-size:11px;font-family:ui-monospace,monospace"><?= $h($latestPaidPayout['payout_batch_id']) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ── Processing payout banner (if any) ─────────────────────── -->
    <?php if ($hasProcessing && $latestProcessingPayout): ?>
    <div class="er-status-row">
        <div class="er-status-card er-status-card--processing">
            <div class="er-status-head">
                <div class="er-status-icon er-status-icon--indigo">
                    <i data-lucide="loader"></i>
                </div>
                <div>
                    <p class="er-status-label">In Progress</p>
                    <p class="er-status-value er-status-value--indigo"><?= $money($processingAmt) ?></p>
                </div>
            </div>
            <div class="er-status-meta">
                <div class="er-meta-row">
                    <span>Requested</span>
                    <span class="er-meta-val"><?= $date($latestProcessingPayout['created_at']) ?></span>
                </div>
                <div class="er-meta-row">
                    <span>Est. transfer</span>
                    <span class="er-meta-val">1–3 business days</span>
                </div>
                <?php if (!empty($latestProcessingPayout['payout_batch_id'])): ?>
                <div class="er-meta-row">
                    <span>Ref</span>
                    <span class="er-meta-val er-meta-val--mono"><?= $h($latestProcessingPayout['payout_batch_id']) ?></span>
                </div>
                <?php endif; ?>
                <div class="er-meta-row">
                    <span>Admin reviewing</span>
                    <span class="er-meta-badge er-meta-badge--indigo"><i data-lucide="clock"></i>Pending</span>
                </div>
            </div>
        </div>
        <!-- empty spacer for grid alignment -->
        <div></div>
    </div>
    <?php endif; ?>

    <!-- ── Fee Breakdown ─────────────────────────────────────────── -->
    <?php
    // Pre-compute total refunds from breakdown data
    $totalRefunded = 0;
    foreach ($earningsBreakdown as $_row) {
        $totalRefunded += (float)($_row['refund_amount'] ?? 0);
    }
    ?>
    <?php if ($grossTotal > 0): ?>
    <div class="er-section">
        <div class="er-section-head">
            <div>
                <h2 class="er-section-title">Fee breakdown</h2>
                <p class="er-section-sub">Settled payouts only</p>
            </div>
        </div>
        <div class="er-fb-wrap">
            <div class="er-fb-row">
                <!-- Gross -->
                <div class="er-fb-cell">
                    <p class="er-fb-label">Gross Earnings</p>
                    <p class="er-fb-value er-fb-value--default"><?= number_format($grossTotal, 0) ?></p>
                    <p class="er-fb-unit">MMK</p>
                </div>
                <div class="er-fb-op">−</div>
                <!-- Fee -->
                <div class="er-fb-cell er-fb-cell--fee">
                    <p class="er-fb-label">Platform Fee <span class="er-fb-rate"><?= $feeRate ?>%</span></p>
                    <p class="er-fb-value er-fb-value--fee"><?= number_format($feeTotal, 0) ?></p>
                    <p class="er-fb-unit">MMK</p>
                </div>
                <?php if ($totalRefunded > 0): ?>
                <div class="er-fb-op">−</div>
                <!-- Refunds -->
                <div class="er-fb-cell" style="border-color:#fecaca">
                    <p class="er-fb-label" style="color:#dc2626">Refunds Deducted</p>
                    <p class="er-fb-value" style="color:#dc2626"><?= number_format($totalRefunded, 0) ?></p>
                    <p class="er-fb-unit">MMK</p>
                </div>
                <?php endif; ?>
                <div class="er-fb-op">=</div>
                <!-- Net -->
                <div class="er-fb-cell er-fb-cell--net">
                    <p class="er-fb-label">You Received</p>
                    <p class="er-fb-value er-fb-value--net"><?= number_format($netTotal - $totalRefunded, 0) ?></p>
                    <p class="er-fb-unit">MMK</p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ── Transactions table ────────────────────────────────────── -->
    <div class="er-section">
        <!-- header -->
        <div class="er-section-head">
            <div>
                <h2 class="er-section-title">Earnings timeline</h2>
                <p class="er-section-sub"><?= $totalPayouts ?> total transaction<?= $totalPayouts !== 1 ? 's' : '' ?></p>
            </div>
        </div>

        <?php if (empty($earningsBreakdown)): ?>
        <!-- Empty state -->
        <div class="er-empty">
            <div class="er-empty-icon">
                <i data-lucide="banknote"></i>
            </div>
            <p class="er-empty-title">No earnings yet</p>
            <p class="er-empty-sub">Once bookings are completed, your earnings will appear here.</p>
        </div>
        <?php else: ?>
        <!-- Table -->
        <div style="overflow-x:auto">
            <table class="er-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Booking</th>
                        <th class="er-hide-sm">Services</th>
                        <th class="er-right">Gross</th>
                        <th class="er-right">Fee</th>
                        <th class="er-right">Net</th>
                        <th class="er-right">Refund</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                  foreach ($earningsBreakdown as $row):
                    $status   = $row['status'] ?? 'pending';
                    $net      = (float)($row['net_amount']   ?? 0);
                    $fee      = (float)($row['platform_fee'] ?? 0);
                    $gross    = (float)($row['gross_amount'] ?? ($net + $fee));
                    $services = $h($row['service_names'] ?? '');
                    $bookingId = (int)($row['booking_id'] ?? 0);
                    $rowDate   = $row['created_at'] ?? null;
                    $paidDate  = $row['verified_at'] ?? null;
                    $refundAmt = (float)($row['refund_amount'] ?? 0);
                    $refundSt  = (string)($row['refund_status'] ?? '');
                    if ($refundAmt > 0) $totalRefunded += $refundAmt;

                    $statusCfg = match($status) {
                        'success'    => ['cls' => 'paid',       'label' => $paidDate ? ('Paid ' . date('M d', strtotime($paidDate))) : 'Paid'],
                        'processing' => ['cls' => 'processing', 'label' => 'Processing'],
                        'failed'     => ['cls' => 'rejected',   'label' => 'Rejected'],
                        default      => ['cls' => 'pending',    'label' => 'Pending'],
                    };

                    $note = trim((string)($row['verified_note'] ?? ''));
                ?>
                    <tr>
                        <!-- Date -->
                        <td>
                            <div class="er-date-cell">
                                <span class="er-date-day"><?= $rowDate ? date('d', strtotime($rowDate)) : '—' ?></span>
                                <span class="er-date-month"><?= $rowDate ? date('M', strtotime($rowDate)) : '' ?></span>
                            </div>
                        </td>
                        <!-- Booking -->
                        <td>
                            <span class="er-booking-ref">Booking #<?= $bookingId ?></span>
                        </td>
                        <!-- Services -->
                        <td class="er-hide-sm">
                            <?php if ($services): ?>
                            <span class="er-service-tag"><?= $services ?></span>
                            <?php else: ?>
                            <span style="color:#ddd0c8">—</span>
                            <?php endif; ?>
                        </td>
                        <!-- Gross -->
                        <td class="er-right er-amount"><?= number_format($gross, 0) ?></td>
                        <!-- Fee -->
                        <td class="er-right er-amount er-amount--fee">−<?= number_format($fee, 0) ?></td>
                        <!-- Net -->
                        <td class="er-right er-amount er-amount--net"><?= number_format($net, 0) ?> MMK</td>
                        <!-- Refund -->
                        <td class="er-right er-amount" style="<?= $refundAmt > 0 ? 'color:#dc2626;font-weight:700' : 'color:#d6d3d1' ?>">
                            <?php if ($refundAmt > 0): ?>
                                −<?= number_format($refundAmt, 0) ?>
                                <?php if ($refundSt && $refundSt !== 'completed'): ?>
                                    <span style="font-size:10px;font-weight:500;opacity:0.7">(<?= $refundSt ?>)</span>
                                <?php endif; ?>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                        <!-- Status -->
                        <td>
                            <span class="er-badge er-badge-<?= $statusCfg['cls'] ?>">
                                <span class="er-badge-dot"></span>
                                <?= $statusCfg['label'] ?>
                            </span>
                            <?php if ($note && $status === 'failed'): ?>
                            <p class="er-detail er-detail--reject"><i data-lucide="alert-circle" style="width:10px;height:10px;display:inline;vertical-align:middle"></i> <?= $h($note) ?></p>
                            <?php elseif ($status === 'processing'): ?>
                            <p class="er-detail er-detail--processing">Admin reviewing</p>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="er-pagination">
            <span class="er-page-info">Page <?= $currentPage ?> of <?= $totalPages ?></span>
            <div class="er-page-btns">
                <?php if ($currentPage > 1): ?>
                    <a href="?page=<?= $currentPage - 1 ?>" class="er-page-btn" aria-label="Previous page">
                        <i data-lucide="chevron-left"></i>
                    </a>
                <?php else: ?>
                    <span class="er-page-btn er-page-btn-disabled" aria-disabled="true">
                        <i data-lucide="chevron-left"></i>
                    </span>
                <?php endif; ?>
                <?php
                for ($p = 1; $p <= $totalPages; $p++):
                    $showPage = ($p === 1)
                        || ($p === $totalPages)
                        || ($p >= $currentPage - 1 && $p <= $currentPage + 1);
                    $isEllipsisBefore = ($p === 2 && $currentPage > 3);
                    $isEllipsisAfter  = ($p === $totalPages - 1 && $currentPage < $totalPages - 2);
                ?>
                    <?php if ($showPage): ?>
                        <?php if ($p === $currentPage): ?>
                        <span class="er-page-btn er-page-btn-cur" aria-current="page"><?= $p ?></span>
                        <?php else: ?>
                        <a href="?page=<?= $p ?>" class="er-page-btn"><?= $p ?></a>
                        <?php endif; ?>
                    <?php elseif ($isEllipsisBefore || $isEllipsisAfter): ?>
                        <span style="padding:0 4px;color:#A8A29E;font-size:12px">…</span>
                    <?php endif; ?>
                <?php endfor; ?>
                <?php if ($currentPage < $totalPages): ?>
                    <a href="?page=<?= $currentPage + 1 ?>" class="er-page-btn" aria-label="Next page">
                        <i data-lucide="chevron-right"></i>
                    </a>
                <?php else: ?>
                    <span class="er-page-btn er-page-btn-disabled" aria-disabled="true">
                        <i data-lucide="chevron-right"></i>
                    </span>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>

</section>

<!-- ── Cash Out Modal ─────────────────────────────────────────── -->
<div id="cashout-modal" class="er-modal-overlay">
    <div class="er-modal">
        <div class="er-modal-head">
            <div class="er-modal-head-icon">
                <i data-lucide="send"></i>
            </div>
            <h2 class="er-modal-title">Request Payout</h2>
        </div>
        <form id="cashout-form" class="er-modal-body">
            <?= csrf_field() ?>
            <div class="er-field">
                <label class="er-label">Bank Account Number</label>
                <input type="text" name="bank_account" value="<?= $h($supplier['bank_account'] ?? '') ?>" placeholder="e.g., 1234567890" required class="er-input">
            </div>
            <div class="er-field">
                <label class="er-label">Bank</label>
                <select name="bank_code" required class="er-select">
                    <option value="">Select bank…</option>
                    <?php $selectedBank = (string)($supplier['bank_code'] ?? ''); ?>
                    <option value="AYA"    <?= $selectedBank === 'AYA'    ? 'selected' : '' ?>>AYA Bank</option>
                    <option value="KBZ"    <?= $selectedBank === 'KBZ'    ? 'selected' : '' ?>>KBZ Bank</option>
                    <option value="AGD"    <?= $selectedBank === 'AGD'    ? 'selected' : '' ?>>AGD Bank</option>
                    <option value="CBD"    <?= $selectedBank === 'CBD'    ? 'selected' : '' ?>>CB Bank</option>
                    <option value="MYBANK" <?= $selectedBank === 'MYBANK' ? 'selected' : '' ?>>MyBank</option>
                </select>
            </div>
            <div class="er-field">
                <label class="er-label">Amount</label>
                <input type="number" name="amount" value="<?= number_format((float)($earnings['pending_amount'] ?? 0), 2, '.', '') ?>" readonly required class="er-input er-input--readonly">
                <p class="er-field-hint">Full available balance submitted as one payout batch.</p>
            </div>
            <div class="er-info-box">
                <p>Admin will manually transfer funds to your bank account within 1–3 business days. You'll be notified once the payment is sent.</p>
            </div>
            <div class="er-modal-foot">
                <button type="button" onclick="closeCashoutModal()" style="flex:1;min-height:38px;border-radius:9px;font-size:12px;font-weight:700;cursor:pointer;border:1px solid #ead8c7;background:#fff;color:#6d4c5b;transition:background .15s">Cancel</button>
                <button type="submit" id="cashout-submit" style="flex:1;min-height:38px;border-radius:9px;font-size:12px;font-weight:700;cursor:pointer;border:0;background:#6d4c5b;color:#fff;transition:background .15s">Submit Request</button>
            </div>
        </form>
    </div>
</div>

<script>
function openCashoutModal() {
    var m = document.getElementById('cashout-modal');
    m.style.display = 'flex';
    if (window.lucide) lucide.createIcons();
}
function closeCashoutModal() {
    document.getElementById('cashout-modal').style.display = 'none';
}
document.getElementById('cashout-btn')?.addEventListener('click', openCashoutModal);
document.getElementById('cashout-modal')?.addEventListener('click', function(e) {
    if (e.target.id === 'cashout-modal') closeCashoutModal();
});
document.getElementById('cashout-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    var btn = document.getElementById('cashout-submit');
    btn.disabled = true;
    btn.textContent = 'Submitting…';
    var formData = new FormData(e.target);
    try {
        var resp = await fetch('<?= URLROOT ?>/booking/requestPayoutPost', { method: 'POST', body: formData });
        var text = await resp.text();
        var data;
        try { data = JSON.parse(text); } catch(err) { data = null; }
        if (data && data.success) {
            closeCashoutModal();
            supToastSuccess('Payout request submitted! We\'ll notify you once it\'s processed.');
            setTimeout(function() { location.reload(); }, 2000);
        } else {
            supToastError(data?.error || 'Something went wrong. Please try again.');
            btn.disabled = false;
            btn.textContent = 'Submit Request';
        }
    } catch (err) {
        supToastError('Network error — please try again.');
        btn.disabled = false;
        btn.textContent = 'Submit Request';
    }
});
</script>
<?php
};
?>
<!DOCTYPE html>
<html lang="en">
<head><?php $pageTitle = 'Earnings — Golden Promise'; ?>
    <?php require_once APPROOT . '/views/dashboardLayout/head.php'; ?></head>
<body class="grid h-screen gap-0 bg-app-page" style="grid-template-columns: 280px 1fr;">
    <?php require APPROOT . '/views/dashboardLayout/suppliersidebar.php'; ?>
</body>
</html>
