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

$dashboardTitle = 'Supplier';
$dashboardCrumb = 'Earnings';
$dashboardBreadcrumbs = [
    ['label' => 'Dashboard', 'url' => URLROOT . '/supplier/dashboard'],
    ['label' => 'Earnings',   'url' => null],
];
$dashboardContentClass = 'bg-[#F4F1EE] px-0 py-0 overflow-y-auto';
$dashboardContent = function () use ($earnings, $payouts, $supplier, $supplierId, $currentPage, $totalPages, $totalPayouts, $h, $money, $date) {

    $pendingAmt   = (int)($earnings['pending_amount'] ?? 0);
    $pendingCnt   = (int)($earnings['pending_count'] ?? 0);
    $processingAmt = (int)($earnings['processing_amount'] ?? 0);
    $paidAmt      = (int)($earnings['paid_amount'] ?? 0);
    $paidCnt      = (int)($earnings['paid_count'] ?? 0);
    $totalEarned  = (int)($earnings['total_earned'] ?? 0);
    $hasPending   = $pendingAmt > 0;
    $hasProcessing = $processingAmt > 0;
?>
<script src="<?= URLROOT ?>/public/js/supplier-toast.js"></script>

<section class="mx-auto max-w-[1600px] space-y-4 px-5 py-6 text-[13px] antialiased" style="font-family:'Poppins',system-ui,sans-serif;color:#6d4c5b">

    <!-- Page header -->
    <div class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p style="margin-bottom:4px;color:#A8A29E;font-size:11px;font-weight:650">Supplier workspace</p>
            <h1 style="margin:0;color:#6d4c5b;font-family:'Playfair Display',serif;font-size:clamp(27px,2.5vw,36px);font-weight:650;letter-spacing:-.025em;line-height:1.08">Your Earnings</h1>
            <p style="margin-top:6px;color:#7b5c69;font-size:12px;font-weight:500">Track payouts and request cash withdrawals.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="<?= URLROOT ?>/supplier/paymentHistory" style="display:inline-flex;align-items:center;gap:6px;min-height:34px;padding:0 14px;border:1px solid #ead8c7;border-radius:999px;background:transparent;color:#6d4c5b;font-size:12px;font-weight:600;text-decoration:none;transition:all .15s">
                <i data-lucide="receipt" style="width:14px;height:14px"></i>
                Payment History
            </a>
        </div>
    </div>

    <!-- KPI row -->
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:4px">
        <div style="background:#FFFFFF;border:1px solid #ead8c7;border-radius:1.2rem;padding:18px 20px;box-shadow:0 1px 3px rgba(0,0,0,.04)">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px">
                <div style="width:36px;height:36px;border-radius:10px;background:#fff7ed;display:flex;align-items:center;justify-content:center"><i data-lucide="wallet" style="width:18px;height:18px;color:#c2410c"></i></div>
                <span style="font-size:11px;color:#A8A29E;font-weight:500">Available to Withdraw</span>
            </div>
            <p style="font-size:26px;font-weight:800;margin:0;color:#b45309"><?= $money($pendingAmt) ?></p>
            <p style="font-size:11px;color:#A8A29E;margin-top:4px"><?= $pendingCnt ?> booking<?= $pendingCnt !== 1 ? 's' : '' ?></p>
        </div>
        <div style="background:#FFFFFF;border:1px solid #ead8c7;border-radius:1.2rem;padding:18px 20px;box-shadow:0 1px 3px rgba(0,0,0,.04)">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px">
                <div style="width:36px;height:36px;border-radius:10px;background:#eef2ff;display:flex;align-items:center;justify-content:center"><i data-lucide="loader" style="width:18px;height:18px;color:#4338ca"></i></div>
                <span style="font-size:11px;color:#A8A29E;font-weight:500">Being Processed</span>
            </div>
            <p style="font-size:26px;font-weight:800;margin:0;color:#4338ca"><?= $money($processingAmt) ?></p>
            <p style="font-size:11px;color:#A8A29E;margin-top:4px">Admin reviewing</p>
        </div>
        <div style="background:#FFFFFF;border:1px solid #ead8c7;border-radius:1.2rem;padding:18px 20px;box-shadow:0 1px 3px rgba(0,0,0,.04)">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px">
                <div style="width:36px;height:36px;border-radius:10px;background:#ecfdf5;display:flex;align-items:center;justify-content:center"><i data-lucide="badge-check" style="width:18px;height:18px;color:#047857"></i></div>
                <span style="font-size:11px;color:#A8A29E;font-weight:500">Paid Out</span>
            </div>
            <p style="font-size:26px;font-weight:800;margin:0;color:#07825f"><?= $money($paidAmt) ?></p>
            <p style="font-size:11px;color:#A8A29E;margin-top:4px"><?= $paidCnt ?> payout<?= $paidCnt !== 1 ? 's' : '' ?></p>
        </div>
        <div style="background:#FFFFFF;border:1px solid #ead8c7;border-radius:1.2rem;padding:18px 20px;box-shadow:0 1px 3px rgba(0,0,0,.04)">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px">
                <div style="width:36px;height:36px;border-radius:10px;background:#fdf2f8;display:flex;align-items:center;justify-content:center"><i data-lucide="trending-up" style="width:18px;height:18px;color:#9d174d"></i></div>
                <span style="font-size:11px;color:#A8A29E;font-weight:500">Total Earned</span>
            </div>
            <p style="font-size:26px;font-weight:800;margin:0;color:#6d4c5b"><?= $money($totalEarned) ?></p>
            <p style="font-size:11px;color:#A8A29E;margin-top:4px">All time</p>
        </div>
    </div>

    <!-- CTA banner -->
    <?php if ($hasPending): ?>
    <div style="background:#FFFBEB;border:1px solid #ead8c7;border-radius:1.2rem;padding:20px 24px;display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap">
        <div style="display:flex;align-items:center;gap:14px">
            <div style="width:40px;height:40px;border-radius:10px;background:#fef3c7;display:flex;align-items:center;justify-content:center;flex-shrink:0"><i data-lucide="banknote" style="width:20px;height:20px;color:#b45309"></i></div>
            <div>
                <p style="font-size:14px;font-weight:700;margin:0;color:#6d4c5b">Ready to Cash Out?</p>
                <p style="font-size:12px;color:#A8A29E;margin-top:2px">You have <?= $money($pendingAmt) ?> available to withdraw.</p>
            </div>
        </div>
        <button id="cashout-btn" type="button" style="display:inline-flex;align-items:center;gap:6px;min-height:38px;padding:0 22px;border:0;border-radius:999px;background:#6d4c5b;color:#fff;font-size:13px;font-weight:700;cursor:pointer;transition:all .2s;white-space:nowrap">
            <i data-lucide="send" style="width:14px;height:14px"></i>
            Request Payout
        </button>
    </div>
    <?php elseif ($hasProcessing): ?>
    <div style="background:#EEF2FF;border:1px solid #C7D2FE;border-radius:1.2rem;padding:20px 24px;display:flex;align-items:center;gap:14px">
        <div style="width:40px;height:40px;border-radius:10px;background:#e0e7ff;display:flex;align-items:center;justify-content:center;flex-shrink:0"><i data-lucide="clock" style="width:20px;height:20px;color:#4338ca"></i></div>
        <div>
            <p style="font-size:14px;font-weight:700;margin:0;color:#6d4c5b">Payout Under Review</p>
            <p style="font-size:12px;color:#A8A29E;margin-top:2px">You have <?= $money($processingAmt) ?> being processed. Admin will transfer the funds shortly.</p>
        </div>
    </div>
    <?php else: ?>
    <div style="background:#FFFFFF;border:1px solid #ead8c7;border-radius:1.2rem;padding:20px 24px;display:flex;align-items:center;gap:14px">
        <div style="width:40px;height:40px;border-radius:10px;background:#F4F1EE;display:flex;align-items:center;justify-content:center;flex-shrink:0"><i data-lucide="info" style="width:20px;height:20px;color:#A8A29E"></i></div>
        <p style="font-size:13px;color:#A8A29E;margin:0">No pending payouts yet. Once bookings are completed, payouts will appear here.</p>
    </div>
    <?php endif; ?>

    <!-- Payout history table -->
    <div style="background:#FFFFFF;border:1px solid #ead8c7;border-radius:1.2rem;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.04)">
        <div style="padding:18px 22px;border-bottom:1px solid #ead8c7;display:flex;align-items:center;justify-content:space-between">
            <div>
                <h2 style="margin:0;font-size:14px;font-weight:750;color:#6d4c5b;letter-spacing:-.015em">Payout History</h2>
                <p style="margin-top:3px;font-size:11px;color:#A8A29E"><?= $totalPayouts ?> total transaction<?= $totalPayouts !== 1 ? 's' : '' ?></p>
            </div>
            <span style="font-size:11px;color:#A8A29E;font-weight:650"><?= $totalPayouts ?> records</span>
        </div>

        <?php if (empty($payouts)): ?>
            <div style="padding:48px 20px;text-align:center">
                <div style="width:48px;height:48px;border-radius:12px;background:#F4F1EE;display:flex;align-items:center;justify-content:center;margin:0 auto 14px"><i data-lucide="banknote" style="width:22px;height:22px;color:#A8A29E"></i></div>
                <p style="font-size:14px;font-weight:600;color:#6d4c5b;margin:0 0 4px">No payouts yet</p>
                <p style="font-size:12px;color:#A8A29E;margin:0">Once bookings are completed, payouts will appear here.</p>
            </div>
        <?php else: ?>
            <table style="width:100%;border-collapse:collapse">
                <thead>
                    <tr style="background:#F4F1EE">
                        <th style="padding:10px 20px;font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:#A8A29E;text-align:left">Booking</th>
                        <th style="padding:10px 20px;font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:#A8A29E;text-align:left">Amount</th>
                        <th style="padding:10px 20px;font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:#A8A29E;text-align:left">Status</th>
                        <th style="padding:10px 20px;font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:#A8A29E;text-align:left">Details</th>
                        <th style="padding:10px 20px;font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:#A8A29E;text-align:left">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payouts as $payout):
                        $status = $payout['status'] ?? 'pending';
                        $badgeCfg = match($status) {
                            'success'    => ['bg' => '#ECFDF5', 'fg' => '#065f46', 'label' => 'Paid'],
                            'processing' => ['bg' => '#EEF2FF', 'fg' => '#3730A3', 'label' => 'Under Review'],
                            'failed'     => ['bg' => '#FEF2F2', 'fg' => '#991b1b', 'label' => 'Rejected'],
                            default      => ['bg' => '#FFFBEB', 'fg' => '#92400e', 'label' => 'Pending'],
                        };
                        $note = trim((string)($payout['verified_note'] ?? ''));
                    ?>
                    <tr style="border-top:1px solid #ead8c7;transition:background .15s" onmouseenter="this.style.background='#F4F1EE'" onmouseleave="this.style.background='transparent'">
                        <td style="padding:14px 20px;font-size:13px;font-weight:700;color:#6d4c5b">#<?= (int)($payout['booking_id'] ?? 0) ?></td>
                        <td style="padding:14px 20px;font-size:13px;font-weight:700;color:#6d4c5b"><?= $money((float)($payout['amount'] ?? 0)) ?></td>
                        <td style="padding:14px 20px"><span style="display:inline-flex;align-items:center;border-radius:20px;padding:3px 10px;font-size:10px;font-weight:800;letter-spacing:.05em;text-transform:uppercase;background:<?= $badgeCfg['bg'] ?>;color:<?= $badgeCfg['fg'] ?>"><?= $badgeCfg['label'] ?></span></td>
                        <td style="padding:14px 20px;font-size:12px;color:#A8A29E;max-width:240px">
                            <?php if ($status === 'success' && $note): ?>
                                <?= $h($note) ?>
                            <?php elseif ($status === 'failed' && $note): ?>
                                <span style="color:#991b1b">Reason: <?= $h($note) ?></span>
                            <?php elseif ($status === 'processing'): ?>
                                Waiting for admin to transfer funds
                            <?php else: ?>
                                &mdash;
                            <?php endif; ?>
                        </td>
                        <td style="padding:14px 20px;font-size:12px;color:#A8A29E"><?= $date($payout['created_at'] ?? 'now') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if ($totalPages > 1): ?>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:14px 20px;border-top:1px solid #ead8c7">
                <span style="font-size:12px;color:#A8A29E">Page <?= $currentPage ?> of <?= $totalPages ?></span>
                <div style="display:flex;gap:6px">
                    <?php if ($currentPage > 1): ?>
                        <a href="?page=<?= $currentPage - 1 ?>" style="display:inline-flex;align-items:center;gap:6px;min-height:34px;padding:0 14px;border:1px solid #ead8c7;border-radius:999px;background:transparent;color:#6d4c5b;font-size:12px;font-weight:600;text-decoration:none;transition:all .15s">&larr; Previous</a>
                    <?php endif; ?>
                    <?php if ($currentPage < $totalPages): ?>
                        <a href="?page=<?= $currentPage + 1 ?>" style="display:inline-flex;align-items:center;gap:6px;min-height:34px;padding:0 14px;border:1px solid #ead8c7;border-radius:999px;background:transparent;color:#6d4c5b;font-size:12px;font-weight:600;text-decoration:none;transition:all .15s">Next &rarr;</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<!-- Cash Out Modal -->
<div id="cashout-modal" style="position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:50;display:none;align-items:center;justify-content:center;padding:16px;backdrop-filter:blur(2px)">
    <div style="background:#FFFFFF;border-radius:1.2rem;max-width:460px;width:100%;overflow:hidden;box-shadow:0 24px 60px rgba(0,0,0,.15)">
        <div style="padding:20px 24px;border-bottom:1px solid #ead8c7;display:flex;align-items:center;gap:12px">
            <div style="width:36px;height:36px;border-radius:10px;background:#fff7ed;display:flex;align-items:center;justify-content:center;flex-shrink:0"><i data-lucide="send" style="width:18px;height:18px;color:#b45309"></i></div>
            <h2 style="margin:0;font-size:16px;font-weight:750;color:#6d4c5b">Request Payout</h2>
        </div>
        <form id="cashout-form" style="padding:22px 24px">
            <?= csrf_field() ?>
            <div style="margin-bottom:16px">
                <label style="display:block;font-size:12px;font-weight:700;color:#6d4c5b;margin-bottom:6px">Bank Account Number</label>
                <input type="text" name="bank_account" value="<?= $h($supplier['bank_account'] ?? '') ?>" placeholder="e.g., 1234567890" required style="width:100%;padding:10px 14px;border:1px solid #ead8c7;border-radius:10px;font-size:13px;color:#6d4c5b;background:#FFFFFF;transition:border-color .2s;box-sizing:border-box">
            </div>
            <div style="margin-bottom:16px">
                <label style="display:block;font-size:12px;font-weight:700;color:#6d4c5b;margin-bottom:6px">Bank</label>
                <select name="bank_code" required style="width:100%;padding:10px 14px;border:1px solid #ead8c7;border-radius:10px;font-size:13px;color:#6d4c5b;background:#FFFFFF;transition:border-color .2s;box-sizing:border-box">
                    <option value="">Select bank...</option>
                    <?php $selectedBank = (string)($supplier['bank_code'] ?? ''); ?>
                    <option value="AYA" <?= $selectedBank === 'AYA' ? 'selected' : '' ?>>AYA Bank</option>
                    <option value="KBZ" <?= $selectedBank === 'KBZ' ? 'selected' : '' ?>>KBZ Bank</option>
                    <option value="AGD" <?= $selectedBank === 'AGD' ? 'selected' : '' ?>>AGD Bank</option>
                    <option value="CBD" <?= $selectedBank === 'CBD' ? 'selected' : '' ?>>CB Bank</option>
                    <option value="MYBANK" <?= $selectedBank === 'MYBANK' ? 'selected' : '' ?>>MyBank</option>
                </select>
            </div>
            <div style="margin-bottom:16px">
                <label style="display:block;font-size:12px;font-weight:700;color:#6d4c5b;margin-bottom:6px">Amount</label>
                <input type="number" name="amount" value="<?= number_format((float)($earnings['pending_amount'] ?? 0), 2, '.', '') ?>" readonly required style="width:100%;padding:10px 14px;border:1px solid #ead8c7;border-radius:10px;font-size:13px;color:#6d4c5b;background:#F4F1EE;box-sizing:border-box">
                <p style="font-size:11px;color:#A8A29E;margin-top:4px;margin-bottom:0">Full available balance submitted as one payout batch.</p>
            </div>
            <div style="background:#F4F1EE;border-radius:10px;padding:14px 18px;margin-bottom:20px">
                <p style="font-size:12px;color:#A8A29E;margin:0;line-height:1.6">After you submit, admin will manually transfer the funds to your bank account. This usually takes 1&ndash;3 business days. You'll be notified once the payment is sent.</p>
            </div>
            <div style="display:flex;gap:10px">
                <button type="button" onclick="closeCashoutModal()" style="flex:1;min-height:40px;border-radius:10px;font-size:13px;font-weight:700;cursor:pointer;border:1px solid #ead8c7;background:transparent;color:#6d4c5b;transition:all .2s">Cancel</button>
                <button type="submit" id="cashout-submit" style="flex:1;min-height:40px;border-radius:10px;font-size:13px;font-weight:700;cursor:pointer;border:0;background:#6d4c5b;color:#fff;transition:all .2s">Submit Request</button>
            </div>
        </form>
    </div>
</div>

<script>
function openCashoutModal() {
    var m = document.getElementById('cashout-modal');
    m.style.display = 'flex';
    lucide.createIcons();
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
<head><?php require_once APPROOT . '/views/dashboardLayout/head.php'; ?></head>
<body class="grid h-screen gap-0 bg-app-page" style="grid-template-columns: 280px 1fr;">
    <?php require APPROOT . '/views/dashboardLayout/suppliersidebar.php'; ?>
</body>
</html>
