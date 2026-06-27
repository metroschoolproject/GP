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
<script src="<?= URLROOT ?>/public/js/supplier-toast.js"></script>

<section class="mx-auto max-w-[1200px] space-y-6 px-6 py-7 text-[13px] antialiased" style="font-family:'Poppins',system-ui,sans-serif;color:#4a3240">

    <!-- ── Page header ───────────────────────────────────────────── -->
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p style="color:#A8A29E;font-size:11px;font-weight:600;letter-spacing:.06em;text-transform:uppercase;margin-bottom:4px">Supplier Workspace</p>
            <h1 style="margin:0;color:#4a3240;font-family:'Playfair Display',serif;font-size:clamp(26px,2.4vw,34px);font-weight:700;letter-spacing:-.025em;line-height:1.1">Your Earnings</h1>
            <p style="margin-top:5px;color:#7b5c69;font-size:12px;font-weight:500">Track your income, fees, and payout history in one place.</p>
        </div>
        <a href="<?= URLROOT ?>/supplier/paymentHistory"
           style="display:inline-flex;align-items:center;gap:6px;min-height:36px;padding:0 16px;border:1.5px solid #ddd0c8;border-radius:999px;background:#fff;color:#6d4c5b;font-size:12px;font-weight:600;text-decoration:none;white-space:nowrap;transition:all .15s"
           onmouseenter="this.style.background='#F4F1EE'" onmouseleave="this.style.background='#fff'">
            <i data-lucide="scroll-text" style="width:13px;height:13px"></i>
            Statement
        </a>
    </div>

    <!-- ── Hero balance card ──────────────────────────────────────── -->
    <div style="background:linear-gradient(135deg,#6d4c5b 0%,#4a3240 100%);border-radius:1.5rem;padding:28px 32px;color:#fff;position:relative;overflow:hidden;box-shadow:0 8px 32px rgba(74,50,64,.25)">
        <!-- subtle texture ring -->
        <div style="position:absolute;right:-60px;top:-60px;width:220px;height:220px;border-radius:50%;background:rgba(255,255,255,.05);pointer-events:none"></div>
        <div style="position:absolute;right:30px;bottom:-80px;width:180px;height:180px;border-radius:50%;background:rgba(255,255,255,.04);pointer-events:none"></div>

        <div style="display:flex;flex-wrap:wrap;gap:20px;align-items:flex-start;justify-content:space-between;position:relative;z-index:1">
            <div>
                <p style="font-size:11px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:rgba(255,255,255,.6);margin:0 0 10px">Available to Withdraw</p>
                <p style="font-size:clamp(34px,4vw,52px);font-weight:800;margin:0;line-height:1;letter-spacing:-.03em"><?= number_format($pendingAmt, 0) ?></p>
                <p style="font-size:13px;color:rgba(255,255,255,.6);margin:4px 0 0">MMK &nbsp;·&nbsp; <?= $pendingCnt ?> booking<?= $pendingCnt !== 1 ? 's' : '' ?> ready</p>
            </div>
            <div style="display:flex;flex-direction:column;align-items:flex-end;gap:10px">
                <?php if ($hasPending): ?>
                <button id="cashout-btn" type="button"
                    style="display:inline-flex;align-items:center;gap:7px;min-height:42px;padding:0 24px;border:0;border-radius:999px;background:#fff;color:#6d4c5b;font-size:13px;font-weight:700;cursor:pointer;transition:all .2s;white-space:nowrap;box-shadow:0 2px 12px rgba(0,0,0,.12)">
                    <i data-lucide="send" style="width:14px;height:14px"></i>
                    Request Payout
                </button>
                <?php elseif ($hasProcessing): ?>
                <span style="display:inline-flex;align-items:center;gap:6px;min-height:36px;padding:0 18px;border-radius:999px;background:rgba(255,255,255,.12);color:rgba(255,255,255,.9);font-size:12px;font-weight:600">
                    <i data-lucide="clock" style="width:13px;height:13px"></i>
                    Payout Under Review
                </span>
                <?php endif; ?>
                <!-- Mini stats row -->
                <div style="display:flex;gap:20px;text-align:right">
                    <div>
                        <p style="font-size:10px;color:rgba(255,255,255,.5);margin:0 0 2px;font-weight:600;letter-spacing:.05em;text-transform:uppercase">Total Earned</p>
                        <p style="font-size:15px;font-weight:700;margin:0;color:rgba(255,255,255,.9)"><?= number_format($totalEarned, 0) ?></p>
                    </div>
                    <div>
                        <p style="font-size:10px;color:rgba(255,255,255,.5);margin:0 0 2px;font-weight:600;letter-spacing:.05em;text-transform:uppercase">Platform Fee</p>
                        <p style="font-size:15px;font-weight:700;margin:0;color:rgba(255,255,255,.9)"><?= $feeRate ?>%</p>
                    </div>
                    <div>
                        <p style="font-size:10px;color:rgba(255,255,255,.5);margin:0 0 2px;font-weight:600;letter-spacing:.05em;text-transform:uppercase">Bookings</p>
                        <p style="font-size:15px;font-weight:700;margin:0;color:rgba(255,255,255,.9)"><?= $completedCnt ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Two status cards ───────────────────────────────────────── -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">

        <!-- Paid Out card -->
        <div style="background:#fff;border:1.5px solid #ddd0c8;border-radius:1.2rem;padding:22px 24px;box-shadow:0 1px 4px rgba(0,0,0,.04)">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px">
                <div style="width:36px;height:36px;border-radius:10px;background:#ecfdf5;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <i data-lucide="badge-check" style="width:18px;height:18px;color:#047857"></i>
                </div>
                <div>
                    <p style="font-size:11px;font-weight:600;color:#A8A29E;margin:0;letter-spacing:.04em;text-transform:uppercase">Paid Out</p>
                    <p style="font-size:18px;font-weight:800;color:#07825f;margin:0;letter-spacing:-.02em"><?= $money($paidAmt) ?></p>
                </div>
            </div>
            <?php if ($latestPaidPayout): ?>
            <div style="border-top:1px solid #ead8c7;padding-top:14px;display:flex;flex-direction:column;gap:5px">
                <div style="display:flex;justify-content:space-between;font-size:11px;color:#A8A29E">
                    <span>Last payout</span>
                    <span style="font-weight:600;color:#4a3240"><?= $date($latestPaidPayout['verified_at'] ?? $latestPaidPayout['created_at']) ?></span>
                </div>
                <?php if (!empty($latestPaidPayout['payout_batch_id'])): ?>
                <div style="display:flex;justify-content:space-between;font-size:11px;color:#A8A29E">
                    <span>Ref</span>
                    <span style="font-weight:600;color:#4a3240;font-family:monospace;font-size:10px"><?= $h($latestPaidPayout['payout_batch_id']) ?></span>
                </div>
                <?php endif; ?>
                <div style="display:flex;justify-content:space-between;font-size:11px;color:#A8A29E">
                    <span><?= $paidCnt ?> payout<?= $paidCnt !== 1 ? 's' : '' ?> total</span>
                    <span style="display:inline-flex;align-items:center;gap:4px;color:#047857;font-weight:600"><i data-lucide="check-circle-2" style="width:11px;height:11px"></i>Settled</span>
                </div>
            </div>
            <?php else: ?>
            <p style="font-size:12px;color:#A8A29E;margin:10px 0 0">No payouts settled yet.</p>
            <?php endif; ?>
        </div>

        <!-- In Progress card -->
        <div style="background:#fff;border:1.5px solid <?= $hasProcessing ? '#c7d2fe' : '#ddd0c8' ?>;border-radius:1.2rem;padding:22px 24px;box-shadow:0 1px 4px rgba(0,0,0,.04)">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px">
                <div style="width:36px;height:36px;border-radius:10px;background:#eef2ff;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <i data-lucide="loader" style="width:18px;height:18px;color:#4338ca"></i>
                </div>
                <div>
                    <p style="font-size:11px;font-weight:600;color:#A8A29E;margin:0;letter-spacing:.04em;text-transform:uppercase">In Progress</p>
                    <p style="font-size:18px;font-weight:800;color:#4338ca;margin:0;letter-spacing:-.02em"><?= $money($processingAmt) ?></p>
                </div>
            </div>
            <?php if ($latestProcessingPayout): ?>
            <div style="border-top:1px solid #ead8c7;padding-top:14px;display:flex;flex-direction:column;gap:5px">
                <div style="display:flex;justify-content:space-between;font-size:11px;color:#A8A29E">
                    <span>Requested</span>
                    <span style="font-weight:600;color:#4a3240"><?= $date($latestProcessingPayout['created_at']) ?></span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:11px;color:#A8A29E">
                    <span>Est. transfer</span>
                    <span style="font-weight:600;color:#4a3240">1–3 business days</span>
                </div>
                <?php if (!empty($latestProcessingPayout['payout_batch_id'])): ?>
                <div style="display:flex;justify-content:space-between;font-size:11px;color:#A8A29E">
                    <span>Ref</span>
                    <span style="font-weight:600;color:#4a3240;font-family:monospace;font-size:10px"><?= $h($latestProcessingPayout['payout_batch_id']) ?></span>
                </div>
                <?php endif; ?>
                <div style="display:flex;justify-content:space-between;font-size:11px;color:#A8A29E">
                    <span>Admin reviewing</span>
                    <span style="display:inline-flex;align-items:center;gap:4px;color:#4338ca;font-weight:600"><i data-lucide="clock" style="width:11px;height:11px"></i>Pending</span>
                </div>
            </div>
            <?php else: ?>
            <p style="font-size:12px;color:#A8A29E;margin:10px 0 0">No active payout request.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- ── Fee Transparency block ─────────────────────────────────── -->
    <?php if ($grossTotal > 0): ?>
    <div style="background:#fff;border:1.5px solid #ddd0c8;border-radius:1.2rem;padding:20px 24px;box-shadow:0 1px 4px rgba(0,0,0,.04)">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:16px">
            <i data-lucide="info" style="width:15px;height:15px;color:#A8A29E"></i>
            <h2 style="margin:0;font-size:13px;font-weight:700;color:#4a3240;letter-spacing:-.01em">Fee Breakdown <span style="font-size:11px;font-weight:500;color:#A8A29E">(settled payouts only)</span></h2>
        </div>
        <div style="display:flex;align-items:center;gap:0;flex-wrap:wrap">
            <!-- Gross -->
            <div style="flex:1;min-width:120px;text-align:center;padding:14px 10px">
                <p style="font-size:10px;font-weight:600;letter-spacing:.06em;text-transform:uppercase;color:#A8A29E;margin:0 0 6px">Gross Earnings</p>
                <p style="font-size:20px;font-weight:800;color:#4a3240;margin:0"><?= number_format($grossTotal, 0) ?></p>
                <p style="font-size:10px;color:#A8A29E;margin:3px 0 0">MMK</p>
            </div>
            <!-- arrow -->
            <div style="color:#ddd0c8;font-size:18px;padding:0 4px">−</div>
            <!-- Fee -->
            <div style="flex:1;min-width:120px;text-align:center;padding:14px 10px;background:#fff9f5;border-radius:10px">
                <p style="font-size:10px;font-weight:600;letter-spacing:.06em;text-transform:uppercase;color:#A8A29E;margin:0 0 6px">Platform Fee
                    <span style="display:inline-block;background:#f3e8d6;color:#b45309;border-radius:99px;padding:1px 7px;font-size:9px;font-weight:700;margin-left:4px"><?= $feeRate ?>%</span>
                </p>
                <p style="font-size:20px;font-weight:800;color:#b45309;margin:0"><?= number_format($feeTotal, 0) ?></p>
                <p style="font-size:10px;color:#A8A29E;margin:3px 0 0">MMK</p>
            </div>
            <!-- arrow -->
            <div style="color:#ddd0c8;font-size:18px;padding:0 4px">=</div>
            <!-- Net -->
            <div style="flex:1;min-width:120px;text-align:center;padding:14px 10px;background:#f0fdf4;border-radius:10px">
                <p style="font-size:10px;font-weight:600;letter-spacing:.06em;text-transform:uppercase;color:#A8A29E;margin:0 0 6px">You Received</p>
                <p style="font-size:20px;font-weight:800;color:#047857;margin:0"><?= number_format($netTotal, 0) ?></p>
                <p style="font-size:10px;color:#A8A29E;margin:3px 0 0">MMK</p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ── Timeline transactions ──────────────────────────────────── -->
    <div style="background:#fff;border:1.5px solid #ddd0c8;border-radius:1.2rem;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.04)">
        <!-- header -->
        <div style="padding:18px 24px;border-bottom:1px solid #ead8c7;display:flex;align-items:center;justify-content:space-between">
            <div>
                <h2 style="margin:0;font-size:14px;font-weight:700;color:#4a3240;letter-spacing:-.015em">Earnings Timeline</h2>
                <p style="margin-top:3px;font-size:11px;color:#A8A29E"><?= $totalPayouts ?> total transaction<?= $totalPayouts !== 1 ? 's' : '' ?></p>
            </div>
        </div>

        <?php if (empty($earningsBreakdown)): ?>
        <div style="padding:56px 20px;text-align:center">
            <div style="width:52px;height:52px;border-radius:14px;background:#F4F1EE;display:flex;align-items:center;justify-content:center;margin:0 auto 14px">
                <i data-lucide="banknote" style="width:24px;height:24px;color:#A8A29E"></i>
            </div>
            <p style="font-size:14px;font-weight:600;color:#4a3240;margin:0 0 4px">No earnings yet</p>
            <p style="font-size:12px;color:#A8A29E;margin:0">Once bookings are completed, your earnings will appear here.</p>
        </div>
        <?php else: ?>

        <div style="padding:8px 0">
        <?php foreach ($earningsBreakdown as $i => $row):
            $status   = $row['status'] ?? 'pending';
            $net      = (float)($row['net_amount']   ?? 0);
            $fee      = (float)($row['platform_fee'] ?? 0);
            $gross    = (float)($row['gross_amount'] ?? ($net + $fee));
            $services = $h($row['service_names'] ?? '');
            $bookingId = (int)($row['booking_id'] ?? 0);
            $rowDate   = $row['created_at'] ?? null;
            $paidDate  = $row['verified_at'] ?? null;

            $statusCfg = match($status) {
                'success'    => ['bg' => '#ecfdf5', 'fg' => '#065f46', 'icon' => 'check-circle-2', 'label' => 'Paid',         'dot' => '#047857'],
                'processing' => ['bg' => '#eef2ff', 'fg' => '#3730a3', 'icon' => 'clock',          'label' => 'Processing',   'dot' => '#4338ca'],
                'failed'     => ['bg' => '#fef2f2', 'fg' => '#991b1b', 'icon' => 'x-circle',       'label' => 'Rejected',     'dot' => '#dc2626'],
                default      => ['bg' => '#fffbeb', 'fg' => '#92400e', 'icon' => 'circle-dot',     'label' => 'Pending',      'dot' => '#d97706'],
            };

            $note = trim((string)($row['verified_note'] ?? ''));
        ?>
        <!-- timeline row -->
        <div style="display:grid;grid-template-columns:56px 1fr;gap:0;padding:0 24px;<?= $i > 0 ? 'border-top:1px solid #f5ede8' : '' ?>"
             onmouseenter="this.style.background='#fdfaf8'" onmouseleave="this.style.background='transparent'">

            <!-- date column -->
            <div style="padding:16px 0;display:flex;flex-direction:column;align-items:center;gap:2px">
                <p style="font-size:16px;font-weight:800;color:#4a3240;margin:0;line-height:1"><?= $rowDate ? date('d', strtotime($rowDate)) : '—' ?></p>
                <p style="font-size:10px;font-weight:600;color:#A8A29E;margin:0;text-transform:uppercase;letter-spacing:.06em"><?= $rowDate ? date('M', strtotime($rowDate)) : '' ?></p>
            </div>

            <!-- content column -->
            <div style="padding:14px 0 14px 16px;border-left:2px solid <?= $statusCfg['dot'] ?>;margin-left:8px">
                <!-- top line: booking + service + status -->
                <div style="display:flex;flex-wrap:wrap;align-items:center;gap:8px;margin-bottom:6px">
                    <span style="font-size:13px;font-weight:700;color:#4a3240">Booking #<?= $bookingId ?></span>
                    <?php if ($services): ?>
                    <span style="font-size:11px;color:#7b5c69;font-weight:500">· <?= $services ?></span>
                    <?php endif; ?>
                    <span style="display:inline-flex;align-items:center;gap:4px;border-radius:20px;padding:3px 10px;font-size:10px;font-weight:700;letter-spacing:.04em;text-transform:uppercase;background:<?= $statusCfg['bg'] ?>;color:<?= $statusCfg['fg'] ?>">
                        <i data-lucide="<?= $statusCfg['icon'] ?>" style="width:10px;height:10px"></i>
                        <?= $status === 'success' && $paidDate ? ('Paid ' . date('M d', strtotime($paidDate))) : $statusCfg['label'] ?>
                    </span>
                </div>

                <!-- fee breakdown line -->
                <div style="display:flex;flex-wrap:wrap;align-items:center;gap:10px;font-size:12px;color:#7b5c69">
                    <span>Gross <strong style="color:#4a3240"><?= number_format($gross, 0) ?></strong></span>
                    <span style="color:#ddd0c8">│</span>
                    <span>Fee <strong style="color:#b45309">−<?= number_format($fee, 0) ?></strong></span>
                    <span style="color:#ddd0c8">│</span>
                    <span style="font-weight:700;color:#047857">→ Net <?= number_format($net, 0) ?> MMK</span>
                </div>

                <!-- note line -->
                <?php if ($note && $status === 'failed'): ?>
                <p style="font-size:11px;color:#991b1b;margin:5px 0 0"><i data-lucide="alert-circle" style="width:10px;height:10px;display:inline;vertical-align:middle"></i> <?= $h($note) ?></p>
                <?php elseif ($status === 'processing'): ?>
                <p style="font-size:11px;color:#4338ca;margin:5px 0 0">Admin reviewing · funds transfer within 1–3 business days</p>
                <?php elseif ($note && $status === 'success'): ?>
                <p style="font-size:11px;color:#A8A29E;margin:5px 0 0"><?= $h($note) ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
        </div>

        <!-- pagination -->
        <?php if ($totalPages > 1): ?>
        <div style="display:flex;justify-content:space-between;align-items:center;padding:14px 24px;border-top:1px solid #ead8c7">
            <span style="font-size:12px;color:#A8A29E">Page <?= $currentPage ?> of <?= $totalPages ?></span>
            <div style="display:flex;gap:6px">
                <?php if ($currentPage > 1): ?>
                    <a href="?page=<?= $currentPage - 1 ?>" style="display:inline-flex;align-items:center;gap:5px;min-height:32px;padding:0 14px;border:1.5px solid #ddd0c8;border-radius:999px;background:transparent;color:#6d4c5b;font-size:12px;font-weight:600;text-decoration:none">← Previous</a>
                <?php endif; ?>
                <?php if ($currentPage < $totalPages): ?>
                    <a href="?page=<?= $currentPage + 1 ?>" style="display:inline-flex;align-items:center;gap:5px;min-height:32px;padding:0 14px;border:1.5px solid #ddd0c8;border-radius:999px;background:transparent;color:#6d4c5b;font-size:12px;font-weight:600;text-decoration:none">Next →</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>

</section>

<!-- ── Cash Out Modal ─────────────────────────────────────────────── -->
<div id="cashout-modal" style="position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:50;display:none;align-items:center;justify-content:center;padding:16px;backdrop-filter:blur(2px)">
    <div style="background:#FFFFFF;border-radius:1.4rem;max-width:460px;width:100%;overflow:hidden;box-shadow:0 24px 60px rgba(0,0,0,.18)">
        <div style="padding:22px 26px;border-bottom:1px solid #ead8c7;display:flex;align-items:center;gap:12px">
            <div style="width:38px;height:38px;border-radius:10px;background:#fff7ed;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <i data-lucide="send" style="width:18px;height:18px;color:#b45309"></i>
            </div>
            <h2 style="margin:0;font-size:16px;font-weight:700;color:#4a3240">Request Payout</h2>
        </div>
        <form id="cashout-form" style="padding:22px 26px">
            <?= csrf_field() ?>
            <div style="margin-bottom:16px">
                <label style="display:block;font-size:12px;font-weight:700;color:#4a3240;margin-bottom:6px">Bank Account Number</label>
                <input type="text" name="bank_account" value="<?= $h($supplier['bank_account'] ?? '') ?>" placeholder="e.g., 1234567890" required
                       style="width:100%;padding:10px 14px;border:1.5px solid #ddd0c8;border-radius:10px;font-size:13px;color:#4a3240;background:#fff;transition:border-color .2s;box-sizing:border-box">
            </div>
            <div style="margin-bottom:16px">
                <label style="display:block;font-size:12px;font-weight:700;color:#4a3240;margin-bottom:6px">Bank</label>
                <select name="bank_code" required style="width:100%;padding:10px 14px;border:1.5px solid #ddd0c8;border-radius:10px;font-size:13px;color:#4a3240;background:#fff;transition:border-color .2s;box-sizing:border-box">
                    <option value="">Select bank…</option>
                    <?php $selectedBank = (string)($supplier['bank_code'] ?? ''); ?>
                    <option value="AYA"    <?= $selectedBank === 'AYA'    ? 'selected' : '' ?>>AYA Bank</option>
                    <option value="KBZ"    <?= $selectedBank === 'KBZ'    ? 'selected' : '' ?>>KBZ Bank</option>
                    <option value="AGD"    <?= $selectedBank === 'AGD'    ? 'selected' : '' ?>>AGD Bank</option>
                    <option value="CBD"    <?= $selectedBank === 'CBD'    ? 'selected' : '' ?>>CB Bank</option>
                    <option value="MYBANK" <?= $selectedBank === 'MYBANK' ? 'selected' : '' ?>>MyBank</option>
                </select>
            </div>
            <div style="margin-bottom:16px">
                <label style="display:block;font-size:12px;font-weight:700;color:#4a3240;margin-bottom:6px">Amount</label>
                <input type="number" name="amount" value="<?= number_format((float)($earnings['pending_amount'] ?? 0), 2, '.', '') ?>" readonly required
                       style="width:100%;padding:10px 14px;border:1.5px solid #ddd0c8;border-radius:10px;font-size:13px;color:#4a3240;background:#F4F1EE;box-sizing:border-box">
                <p style="font-size:11px;color:#A8A29E;margin:4px 0 0">Full available balance submitted as one payout batch.</p>
            </div>
            <div style="background:#F4F1EE;border-radius:10px;padding:14px 16px;margin-bottom:20px">
                <p style="font-size:12px;color:#7b5c69;margin:0;line-height:1.6">Admin will manually transfer funds to your bank account within 1–3 business days. You'll be notified once the payment is sent.</p>
            </div>
            <div style="display:flex;gap:10px">
                <button type="button" onclick="closeCashoutModal()" style="flex:1;min-height:40px;border-radius:10px;font-size:13px;font-weight:700;cursor:pointer;border:1.5px solid #ddd0c8;background:transparent;color:#4a3240;transition:all .2s">Cancel</button>
                <button type="submit" id="cashout-submit" style="flex:1;min-height:40px;border-radius:10px;font-size:13px;font-weight:700;cursor:pointer;border:0;background:#6d4c5b;color:#fff;transition:all .2s">Submit Request</button>
            </div>
        </form>
    </div>
</div>

<script>
function openCashoutModal() {
    document.getElementById('cashout-modal').style.display = 'flex';
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
