<?php
$pendingPayments = $pendingPayments ?? [];
$activeStatus = $activeStatus ?? 'pending';
$isPending = $activeStatus === 'pending';
$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$money = fn($v) => number_format((float)$v, 0) . ' MMK';
$dateTime = static function ($value, string $fallback = '-') {
    if (empty($value)) return $fallback;
    $timestamp = strtotime((string)$value);
    return $timestamp ? date('M j, Y', $timestamp) : $fallback;
};

$pendingCount = count($pendingPayments);
$pendingTotal = 0.0;
$expectedTotal = 0.0;
$missingCount = 0;

foreach ($pendingPayments as $payment) {
    $totalAmount = (float)($payment['total_amount'] ?? 0);
    $expectedDeposit = $totalAmount * (BOOKING_DEPOSIT_PERCENT / 100);
    $paidAmountRaw = $payment['paid_amount'] ?? $payment['payment_amount'] ?? null;
    $paidAmount = $paidAmountRaw !== null && $paidAmountRaw !== '' ? (float)$paidAmountRaw : $expectedDeposit;

    $pendingTotal += $paidAmount;
    $expectedTotal += $expectedDeposit;

    if (empty($payment['payment_id'])) {
        $missingCount++;
    }
}

// Per-tab copy.
$tabCopy = [
    'pending'  => ['title' => 'Payment Verification', 'subtitle' => 'Review submitted booking deposits before confirming payment.', 'card' => 'Verification Queue', 'note' => $pendingCount . ' proofs waiting'],
    'verified' => ['title' => 'Verified Deposits',    'subtitle' => 'Deposits you have confirmed as received.',                  'card' => 'Verified deposits', 'note' => $pendingCount . ' verified'],
    'rejected' => ['title' => 'Rejected Deposits',    'subtitle' => 'Deposit proofs that were rejected and returned to the customer.', 'card' => 'Rejected deposits', 'note' => $pendingCount . ' rejected'],
];
$copy = $tabCopy[$activeStatus] ?? $tabCopy['pending'];

$dashboardTitle = 'Payments';
$dashboardCrumb = ucfirst($activeStatus);
$dashboardContentClass = 'admin-payment-outlet';
$dashboardContent = function () use ($pendingPayments, $pendingCount, $pendingTotal, $expectedTotal, $missingCount, $h, $money, $dateTime, $activeStatus, $isPending, $copy) {
?>
<style>
.pv{min-height:100%;background:#F4F1EE;padding:28px 32px;font-family:'DM Sans',system-ui,sans-serif;color:#6d4c5b;font-size:13px}
.pv *{box-sizing:border-box}
.pv{--s:#FFF;--bg:#F4F1EE;--b:#ead8c7;--bl:#eddecc;--p:#6d4c5b;--ps:#eddecc;--tx:#111827;--mt:#b79c8b;--bd:#7b5c69;--ok-bg:#ECFDF5;--ok:#065F46;--wn-bg:#FFFBEB;--wn:#92400E;--er-bg:#FEF2F2;--er:#991B1B;max-width:1400px;margin:0 auto}

.pv-hdr{display:flex;align-items:flex-end;justify-content:space-between;gap:16px;margin-bottom:20px}
.pv-eyebrow{font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--mt);margin:0 0 4px}
.pv h1{font-size:22px;font-weight:700;color:var(--tx);letter-spacing:-.3px;margin:0}
.pv-sub{margin:4px 0 0;color:var(--bd);font-size:12px;font-weight:600}

.pv-tabs{display:flex;align-items:center;gap:6px;margin-bottom:18px;flex-wrap:wrap}
.pv-tab{display:inline-flex;align-items:center;height:34px;padding:0 14px;border:1px solid var(--b);border-radius:.75rem;background:var(--s);color:var(--bd);font-size:12px;font-weight:700;font-family:inherit;text-decoration:none;cursor:pointer;transition:all .15s}
.pv-tab:hover{border-color:var(--p);color:var(--p)}
.pv-tab.on{border-color:var(--p);background:var(--p);color:#FFF}
.pv-note{height:34px;display:inline-flex;align-items:center;border:1px solid var(--b);border-radius:.75rem;background:var(--s);padding:0 12px;color:var(--bd);font-size:12px;font-weight:700}

.pv-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:18px}
.pv-stat{background:var(--s);border:1px solid var(--b);border-radius:.75rem;padding:14px 16px}
.pv-stat-l{font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--mt);margin-bottom:6px}
.pv-stat-v{font-size:20px;font-weight:700;color:var(--tx);letter-spacing:-.3px}
.pv-stat-v.wn{color:var(--wn)}
.pv-stat-v.er{color:var(--er)}
.pv-stat-s{font-size:11px;color:var(--mt);margin-top:3px}

.pv-empty{padding:60px 20px;text-align:center;color:var(--mt);font-size:14px}

/* Payment Card */
.pv-card{background:var(--s);border:1px solid var(--b);border-radius:14px;overflow:hidden;margin-bottom:14px;box-shadow:0 1px 3px rgba(28,25,23,.04);transition:box-shadow .15s}
.pv-card:hover{box-shadow:0 4px 12px rgba(28,25,23,.08)}
.pv-card-top{display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid var(--bl);gap:12px}
.pv-card-top-l{display:flex;align-items:center;gap:12px;flex:1;min-width:0}
.pv-card-ref{font-size:15px;font-weight:700;color:var(--tx)}
.pv-card-ref a{color:var(--p);text-decoration:none}
.pv-card-ref a:hover{text-decoration:underline}
.pv-card-cust{font-size:12px;color:var(--mt);margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.pv-badges{display:flex;gap:6px;flex-shrink:0}
.pv-badge{display:inline-flex;align-items:center;border-radius:20px;padding:3px 10px;font-size:10px;font-weight:800;letter-spacing:.05em;text-transform:uppercase;white-space:nowrap}
.pv-badge-dep{background:#EEF2FF;color:#3730A3}
.pv-badge-rem{background:#FDF2F8;color:#9D174D}
.pv-badge-pend{background:var(--wn-bg);color:var(--wn)}
.pv-badge-ok{background:var(--ok-bg);color:var(--ok)}
.pv-badge-er{background:var(--er-bg);color:var(--er)}

.pv-card-body{display:grid;grid-template-columns:200px 1fr 1fr;gap:16px;padding:20px}

/* Slip preview */
.pv-slip{position:relative;border-radius:10px;overflow:hidden;border:1px solid var(--b);background:var(--bg);cursor:pointer;aspect-ratio:3/4;display:flex;align-items:center;justify-content:center}
.pv-slip img{width:100%;height:100%;object-fit:cover}
.pv-slip-pdf{display:flex;flex-direction:column;align-items:center;gap:8px;color:var(--mt);font-size:12px;font-weight:600}
.pv-slip-pdf svg{width:32px;height:32px}
.pv-slip-zoom{position:absolute;bottom:8px;right:8px;background:rgba(0,0,0,.6);color:#fff;border:0;border-radius:6px;padding:4px 8px;font-size:10px;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:4px}
.pv-slip-empty{color:var(--mt);font-size:12px;text-align:center}

/* Transfer details */
.pv-detail{display:flex;flex-direction:column;gap:10px}
.pv-detail-title{font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--mt);margin-bottom:2px}
.pv-detail-row{display:flex;justify-content:space-between;gap:8px;font-size:12px}
.pv-detail-k{color:var(--mt);font-weight:600;white-space:nowrap}
.pv-detail-v{color:var(--tx);font-weight:600;text-align:right;word-break:break-all}
.pv-detail-v.mono{font-family:monospace;font-size:11px;background:var(--bg);padding:2px 6px;border-radius:4px}

/* Booking summary */
.pv-summary{background:var(--bg);border:1px solid var(--b);border-radius:10px;padding:14px 16px;display:flex;flex-direction:column;gap:8px}
.pv-summary-title{font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--mt);margin-bottom:2px}
.pv-summary-row{display:flex;justify-content:space-between;font-size:12px}
.pv-summary-row span:first-child{color:var(--mt)}
.pv-summary-row span:last-child{font-weight:700;color:var(--tx)}
.pv-summary-row.total{border-top:1px solid var(--b);padding-top:8px;margin-top:2px}
.pv-summary-row.total span:last-child{color:var(--p);font-size:14px}
.pv-match{display:flex;align-items:center;gap:6px;padding:8px 12px;border-radius:8px;font-size:12px;font-weight:700;margin-top:4px}
.pv-match.ok{background:var(--ok-bg);color:var(--ok)}
.pv-match.er{background:var(--er-bg);color:var(--er)}

/* Card footer — actions */
.pv-card-foot{display:flex;align-items:center;gap:10px;padding:14px 20px;border-top:1px solid var(--bl);background:var(--bg)}
.pv-card-foot .pv-note-input{flex:1;height:38px;border:1px solid var(--b);border-radius:10px;background:var(--s);padding:0 14px;font-size:12px;font-family:inherit;color:var(--tx);outline:none;transition:border-color .2s}
.pv-card-foot .pv-note-input:focus{border-color:var(--p)}
.pv-card-foot .pv-note-input::placeholder{color:var(--mt)}
.pv-btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;min-height:38px;padding:0 20px;border:0;border-radius:10px;font-size:12px;font-weight:700;font-family:inherit;cursor:pointer;transition:all .15s;white-space:nowrap}
.pv-btn-ok{background:var(--p);color:#FFF}
.pv-btn-ok:hover{background:#593e49}
.pv-btn-er{background:var(--er-bg);color:var(--er);border:1px solid #FECACA}
.pv-btn-er:hover{background:#FEE2E2}
.pv-btn:disabled{opacity:.5;cursor:default}

/* Reviewed card footer */
.pv-reviewed{display:flex;align-items:center;gap:10px;padding:12px 20px;border-top:1px solid var(--bl);font-size:12px;color:var(--mt)}

/* Lightbox */
.pv-lb{position:fixed;inset:0;background:rgba(0,0,0,.8);z-index:80;display:flex;align-items:center;justify-content:center;padding:40px;cursor:zoom-out}
.pv-lb img{max-width:90vw;max-height:90vh;border-radius:8px;box-shadow:0 8px 40px rgba(0,0,0,.3)}
.pv-lb-close{position:absolute;top:20px;right:20px;background:rgba(255,255,255,.15);color:#fff;border:0;border-radius:8px;width:40px;height:40px;font-size:18px;cursor:pointer;display:flex;align-items:center;justify-content:center}

/* Reject Modal */
.pv-modal{position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:70;display:flex;align-items:center;justify-content:center;padding:16px}
.pv-modal-box{background:#FFF;border-radius:16px;max-width:440px;width:100%;overflow:hidden;box-shadow:0 24px 60px rgba(0,0,0,.15)}
.pv-modal-head{padding:18px 22px;border-bottom:1px solid var(--b)}
.pv-modal-head h2{font-size:16px;font-weight:700;color:var(--tx);margin:0}
.pv-modal-body{padding:22px}
.pv-modal-body label{display:block;font-size:12px;font-weight:700;color:var(--tx);margin-bottom:6px}
.pv-modal-body textarea{width:100%;min-height:80px;padding:10px 14px;border:1px solid var(--b);border-radius:10px;font-size:13px;font-family:inherit;color:var(--tx);resize:vertical;outline:none}
.pv-modal-body textarea:focus{border-color:var(--p)}
.pv-modal-foot{display:flex;gap:10px;padding:0 22px 22px}
.pv-modal-foot button{flex:1;min-height:40px;border-radius:10px;font-size:13px;font-weight:700;cursor:pointer;font-family:inherit;transition:all .15s}
.pv-modal-cancel{border:1px solid var(--b);background:transparent;color:var(--tx)}
.pv-modal-submit{border:0;background:var(--er);color:#FFF}

/* Toast */
.pv-toast{position:fixed;right:20px;top:20px;z-index:90;display:flex;align-items:center;gap:10px;padding:14px 20px;border-radius:12px;font-size:13px;font-weight:700;box-shadow:0 8px 30px rgba(0,0,0,.12);transform:translateX(120%);transition:transform .35s cubic-bezier(.4,0,.2,1)}
.pv-toast.show{transform:translateX(0)}
.pv-toast.ok{background:var(--ok-bg);color:var(--ok);border:1px solid #A7F3D0}
.pv-toast.er{background:var(--er-bg);color:var(--er);border:1px solid #FECACA}

@media(max-width:1100px){.pv-stats{grid-template-columns:repeat(2,1fr)}.pv-card-body{grid-template-columns:1fr 1fr}}
@media(max-width:760px){.pv{padding:20px 16px}.pv-stats{grid-template-columns:1fr}.pv-card-body{grid-template-columns:1fr}.pv-slip{max-height:200px}}
</style>

<div class="pv">
  <div class="pv-hdr">
    <div>
      <p class="pv-eyebrow">Payments</p>
      <h1><?= $h($copy['title']) ?></h1>
      <p class="pv-sub"><?= $h($copy['subtitle']) ?></p>
    </div>
  </div>

  <div class="pv-tabs">
    <a href="<?= URLROOT ?>/admin/paymentVerification?status=pending" class="pv-tab <?= $activeStatus === 'pending' ? 'on' : '' ?>">Pending review</a>
    <a href="<?= URLROOT ?>/admin/paymentVerification?status=verified" class="pv-tab <?= $activeStatus === 'verified' ? 'on' : '' ?>">Verified</a>
    <a href="<?= URLROOT ?>/admin/paymentVerification?status=rejected" class="pv-tab <?= $activeStatus === 'rejected' ? 'on' : '' ?>">Rejected</a>
    <div style="flex:1"></div>
    <span class="pv-note"><?= $h($copy['note']) ?></span>
  </div>

  <div class="pv-stats">
    <div class="pv-stat">
      <div class="pv-stat-l"><?= $isPending ? 'Awaiting Review' : ($activeStatus === 'verified' ? 'Verified' : 'Rejected') ?></div>
      <div class="pv-stat-v <?= $isPending ? 'wn' : '' ?>"><?= (int)$pendingCount ?></div>
      <div class="pv-stat-s">Payment proofs</div>
    </div>
    <div class="pv-stat">
      <div class="pv-stat-l">Submitted Total</div>
      <div class="pv-stat-v"><?= $money($pendingTotal) ?></div>
      <div class="pv-stat-s"><?= $isPending ? 'From pending records' : 'Across these records' ?></div>
    </div>
    <div class="pv-stat">
      <div class="pv-stat-l">Expected Total</div>
      <div class="pv-stat-v"><?= $money($expectedTotal) ?></div>
      <div class="pv-stat-s"><?= BOOKING_DEPOSIT_PERCENT ?>% deposits</div>
    </div>
    <?php if ($isPending && $missingCount > 0): ?>
    <div class="pv-stat">
      <div class="pv-stat-l">Needs Fix</div>
      <div class="pv-stat-v er"><?= (int)$missingCount ?></div>
      <div class="pv-stat-s">Missing payment records</div>
    </div>
    <?php endif; ?>
  </div>

  <?php if (empty($pendingPayments)): ?>
    <div class="pv-empty">
      <?php if ($isPending): ?>
        <p>No payment proofs awaiting review. All caught up!</p>
      <?php elseif ($activeStatus === 'verified'): ?>
        <p>No verified deposits yet.</p>
      <?php else: ?>
        <p>No rejected deposits.</p>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <?php foreach ($pendingPayments as $payment): ?>
    <?php
      $bookingId = (int)($payment['id'] ?? 0);
      $paymentId = (int)($payment['payment_id'] ?? 0);
      $bookingRef = (string)($payment['booking_ref'] ?? ('BK-' . $bookingId));
      $customerName = (string)($payment['name'] ?? 'Unknown');
      $customerEmail = (string)($payment['email'] ?? '');
      $customerPhone = (string)($payment['phone'] ?? '');
      $totalAmount = (float)($payment['total_amount'] ?? 0);
      $paymentType = (string)($payment['payment_type'] ?? 'deposit');
      $isRemaining = ($paymentType === 'remaining');
      $alreadyPaid = (float)($payment['paid_amount'] ?? 0);
      $feePercent = get_platform_fee_percent();
      $expectedDeposit = $isRemaining
        ? max(0, $totalAmount - $alreadyPaid)
        : round($totalAmount * (BOOKING_DEPOSIT_PERCENT / 100), 2);
      $platformFee = $isRemaining ? 0 : round($totalAmount * ($feePercent / 100), 2);
      $expectedTotal = round($expectedDeposit + $platformFee, 2);
      $paidAmountRaw = $payment['payment_amount'] ?? null;
      $paidAmount = $paidAmountRaw !== null && $paidAmountRaw !== '' ? (float)$paidAmountRaw : $expectedTotal;
      $method = (string)($payment['bank_name'] ?? $payment['method'] ?? '');
      $accountName = (string)($payment['account_name'] ?? '');
      $mobileNumber = (string)($payment['mobile_number'] ?? '');
      $reference = trim((string)($payment['transaction_ref'] ?? ''));
      $slipPath = trim((string)($payment['payment_slip_path'] ?? ''));
      $hasSlip = $slipPath !== '' && preg_match('/\.(jpe?g|png|webp|gif|pdf)$/i', $slipPath) === 1;
      $isPdf = $hasSlip && strtolower(pathinfo($slipPath, PATHINFO_EXTENSION)) === 'pdf';
      $submittedAt = $dateTime($payment['payment_created_at'] ?? $payment['paid_at'] ?? null);
      $paymentStatus = (string)($payment['payment_status'] ?? ($isPending ? 'pending' : ''));
      $reviewedAt = $dateTime($payment['verified_at'] ?? null);
      $reviewNote = trim((string)($payment['verified_note'] ?? ''));
      $amountMatch = abs($paidAmount - $expectedTotal) < 0.01;
      $amountDiff = $paidAmount - $expectedTotal;
    ?>
    <div class="pv-card" data-booking="<?= $bookingId ?>">
      <!-- Card header -->
      <div class="pv-card-top">
        <div class="pv-card-top-l">
          <div>
            <div class="pv-card-ref"><a href="<?= URLROOT ?>/admin/bookingDetail/<?= $bookingId ?>"><?= $h($bookingRef) ?></a></div>
            <div class="pv-card-cust"><?= $h($customerName) ?><?= $customerEmail !== '' ? ' · ' . $h($customerEmail) : '' ?><?= $customerPhone !== '' ? ' · ' . $h($customerPhone) : '' ?></div>
          </div>
        </div>
        <div class="pv-badges">
          <span class="pv-badge <?= $isRemaining ? 'pv-badge-rem' : 'pv-badge-dep' ?>"><?= $isRemaining ? 'Remaining' : 'Deposit' ?></span>
          <?php if ($paymentStatus === 'success'): ?>
            <span class="pv-badge pv-badge-ok">Verified</span>
          <?php elseif ($paymentStatus === 'failed'): ?>
            <span class="pv-badge pv-badge-er">Rejected</span>
          <?php else: ?>
            <span class="pv-badge pv-badge-pend">Pending</span>
          <?php endif; ?>
        </div>
      </div>

      <!-- Card body -->
      <div class="pv-card-body">
        <!-- Slip preview -->
        <div>
          <?php if ($hasSlip): ?>
            <div class="pv-slip" onclick="<?= $isPdf ? "window.open('" . URLROOT . "/" . h($slipPath) . "')" : "openLightbox('" . URLROOT . "/" . h($slipPath) . "')" ?>">
              <?php if ($isPdf): ?>
                <div class="pv-slip-pdf"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg> PDF Document</div>
              <?php else: ?>
                <img src="<?= URLROOT ?>/<?= $h($slipPath) ?>" alt="Payment slip" loading="lazy">
              <?php endif; ?>
              <span class="pv-slip-zoom">🔍 <?= $isPdf ? 'Open' : 'Zoom' ?></span>
            </div>
          <?php else: ?>
            <div class="pv-slip"><div class="pv-slip-empty">No slip uploaded</div></div>
          <?php endif; ?>
        </div>

        <!-- Transfer details -->
        <div class="pv-detail">
          <div class="pv-detail-title">Transfer Details</div>
          <div class="pv-detail-row"><span class="pv-detail-k">Method</span><span class="pv-detail-v"><?= $h($method ?: '—') ?></span></div>
          <div class="pv-detail-row"><span class="pv-detail-k">Sender</span><span class="pv-detail-v"><?= $h($accountName ?: '—') ?></span></div>
          <div class="pv-detail-row"><span class="pv-detail-k">Phone</span><span class="pv-detail-v"><?= $h($mobileNumber ?: '—') ?></span></div>
          <div class="pv-detail-row"><span class="pv-detail-k">Reference</span><span class="pv-detail-v mono"><?= $h($reference !== '' ? $reference : '—') ?></span></div>
          <div class="pv-detail-row"><span class="pv-detail-k">Submitted</span><span class="pv-detail-v"><?= $h($submittedAt) ?></span></div>
          <?php if (!$isRemaining): ?>
          <div class="pv-detail-row"><span class="pv-detail-k">Platform Fee</span><span class="pv-detail-v"><?= $money($platformFee) ?></span></div>
          <?php endif; ?>
        </div>

        <!-- Booking summary -->
        <div class="pv-summary">
          <div class="pv-summary-title">Booking Summary</div>
          <div class="pv-summary-row"><span>Booking total</span><span><?= $money($totalAmount) ?></span></div>
          <?php if ($isRemaining): ?>
            <div class="pv-summary-row"><span>Already paid</span><span><?= $money($alreadyPaid) ?></span></div>
            <div class="pv-summary-row total"><span>Remaining balance</span><span><?= $money($expectedDeposit) ?></span></div>
          <?php else: ?>
            <div class="pv-summary-row"><span>Deposit (<?= BOOKING_DEPOSIT_PERCENT ?>%)</span><span><?= $money($expectedDeposit) ?></span></div>
            <div class="pv-summary-row"><span>Platform fee (<?= rtrim(rtrim(number_format($feePercent, 2), '0'), '.') ?>%)</span><span><?= $money($platformFee) ?></span></div>
            <div class="pv-summary-row total"><span>Expected total</span><span><?= $money($expectedTotal) ?></span></div>
          <?php endif; ?>
          <div class="pv-summary-row"><span>Customer submitted</span><span><?= $money($paidAmount) ?></span></div>
          <?php if ($amountMatch): ?>
            <div class="pv-match ok">✓ Amount matches exactly</div>
          <?php else: ?>
            <div class="pv-match er">✕ Mismatch: <?= $amountDiff > 0 ? '+' : '' ?><?= $money($amountDiff) ?></div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Card footer — actions -->
      <?php if ($isPending && $paymentId > 0): ?>
      <div class="pv-card-foot">
        <input type="text" class="pv-note-input" placeholder="Add a note (optional)…" data-booking="<?= $bookingId ?>">
        <button type="button" class="pv-btn pv-btn-er" onclick="openRejectModal(<?= $bookingId ?>)">✕ Reject</button>
        <button type="button" class="pv-btn pv-btn-ok" onclick="approvePayment(<?= $bookingId ?>)">✓ Approve Payment</button>
      </div>
      <?php elseif ($isPending): ?>
      <div class="pv-reviewed">
        <span>No payment record found.</span>
        <a href="<?= URLROOT ?>/admin/bookingDetail/<?= $bookingId ?>" style="color:var(--p);font-weight:700;text-decoration:none">Open booking →</a>
      </div>
      <?php else: ?>
      <div class="pv-reviewed">
        <span>Reviewed <?= $h($reviewedAt) ?></span>
        <?php if ($reviewNote !== ''): ?>
          <span style="color:var(--bd)">— <?= $h($reviewNote) ?></span>
        <?php endif; ?>
      </div>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>

  <?php
  if (isset($currentPage, $totalPages, $totalCount, $perPage)) {
      $h = function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };
      $baseParams = 'status=' . urlencode($activeStatus ?? 'pending');
      require APPROOT . '/views/partials/_pagination.php';
  }
  ?>
</div>

<!-- Lightbox -->
<div id="pv-lb" class="pv-lb" style="display:none" onclick="closeLightbox()">
  <button class="pv-lb-close" onclick="closeLightbox()">✕</button>
  <img id="pv-lb-img" src="" alt="Payment slip full size">
</div>

<!-- Reject Modal -->
<div id="pv-reject-modal" class="pv-modal" style="display:none">
  <div class="pv-modal-box">
    <div class="pv-modal-head"><h2>Reject Payment Proof</h2></div>
    <div class="pv-modal-body">
      <label>Reason for rejection</label>
      <textarea id="pv-reject-reason" placeholder="Explain why this payment proof is being rejected…"></textarea>
    </div>
    <div class="pv-modal-foot">
      <button type="button" class="pv-modal-cancel" onclick="closeRejectModal()">Cancel</button>
      <button type="button" class="pv-modal-submit" id="pv-reject-submit" onclick="confirmReject()">Reject Payment</button>
    </div>
  </div>
</div>

<!-- Toast -->
<div id="pv-toast" class="pv-toast"><span id="pv-toast-msg"></span></div>

<script>
// CSRF token
const CSRF = '<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>';
const URLROOT = '<?= URLROOT ?>';

// Toast
function showToast(msg, type = 'ok') {
  const t = document.getElementById('pv-toast');
  document.getElementById('pv-toast-msg').textContent = msg;
  t.className = 'pv-toast ' + type;
  t.classList.add('show');
  clearTimeout(t._t);
  t._t = setTimeout(() => t.classList.remove('show'), 4000);
}

// Lightbox
function openLightbox(src) {
  document.getElementById('pv-lb-img').src = src;
  document.getElementById('pv-lb').style.display = 'flex';
}
function closeLightbox() {
  document.getElementById('pv-lb').style.display = 'none';
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeLightbox(); });

// Reject modal
let rejectBookingId = null;
function openRejectModal(bookingId) {
  rejectBookingId = bookingId;
  document.getElementById('pv-reject-reason').value = '';
  document.getElementById('pv-reject-modal').style.display = 'flex';
  document.getElementById('pv-reject-reason').focus();
}
function closeRejectModal() {
  document.getElementById('pv-reject-modal').style.display = 'none';
  rejectBookingId = null;
}
document.getElementById('pv-reject-modal')?.addEventListener('click', e => {
  if (e.target.id === 'pv-reject-modal') closeRejectModal();
});

async function confirmReject() {
  if (!rejectBookingId) return;
  const reason = document.getElementById('pv-reject-reason').value.trim();
  if (!reason) { showToast('Please enter a rejection reason.', 'er'); return; }

  const btn = document.getElementById('pv-reject-submit');
  btn.disabled = true; btn.textContent = 'Rejecting…';

  const fd = new FormData();
  fd.append('booking_id', rejectBookingId);
  fd.append('reason', reason);
  fd.append('csrf_token', CSRF);

  try {
    const resp = await fetch(URLROOT + '/admin/rejectPaymentSlipPost', { method: 'POST', body: fd });
    const data = await resp.json();
    if (data.success) {
      closeRejectModal();
      showToast('Payment rejected. Customer has been notified.', 'ok');
      const card = document.querySelector('[data-booking="' + rejectBookingId + '"]');
      if (card) {
        const badge = card.querySelector('.pv-badge-pend');
        if (badge) { badge.className = 'pv-badge pv-badge-er'; badge.textContent = 'Rejected'; }
        const foot = card.querySelector('.pv-card-foot');
        if (foot) foot.innerHTML = '<div class="pv-reviewed"><span>Rejected — ' + reason + '</span></div>';
      }
    } else {
      showToast(data.error || 'Rejection failed.', 'er');
    }
  } catch (e) {
    showToast('Network error.', 'er');
  }
  btn.disabled = false; btn.textContent = 'Reject Payment';
}

// Approve
async function approvePayment(bookingId) {
  const card = document.querySelector('[data-booking="' + bookingId + '"]');
  const noteInput = card?.querySelector('.pv-note-input');
  const note = noteInput ? noteInput.value.trim() : '';
  const btn = card?.querySelector('.pv-btn-ok');
  if (btn) { btn.disabled = true; btn.textContent = 'Verifying…'; }

  const fd = new FormData();
  fd.append('booking_id', bookingId);
  fd.append('note', note);
  fd.append('csrf_token', CSRF);

  try {
    const resp = await fetch(URLROOT + '/admin/verifyPaymentPost', { method: 'POST', body: fd });
    const data = await resp.json();
    if (data.success) {
      showToast(data.message || 'Payment approved!', 'ok');
      if (card) {
        const badge = card.querySelector('.pv-badge-pend');
        if (badge) { badge.className = 'pv-badge pv-badge-ok'; badge.textContent = 'Verified'; }
        const foot = card.querySelector('.pv-card-foot');
        if (foot) foot.innerHTML = '<div class="pv-reviewed"><span>✓ Approved' + (data.email_sent ? ' — email sent to ' + (data.email_to || 'customer') : '') + '</span></div>';
      }
    } else {
      showToast(data.error || 'Verification failed.', 'er');
      if (btn) { btn.disabled = false; btn.textContent = '✓ Approve Payment'; }
    }
  } catch (e) {
    showToast('Network error.', 'er');
    if (btn) { btn.disabled = false; btn.textContent = '✓ Approve Payment'; }
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
  <?php require_once APPROOT . '/views/dashboardLayout/adminsidebar.php'; ?>
</body>
</html>
