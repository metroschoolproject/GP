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
  .admin-payment-outlet{min-height:100%;background:#FBFBF9;padding:28px 32px;font-family:'DM Sans',system-ui,-apple-system,sans-serif;color:#111827;font-size:13px}
  .admin-payment-page *{box-sizing:border-box}
  .admin-payment-page{--surface:#fcf8f5;--soft:#faf5ef;--hover:#eddecc;--border:#ead8c7;--border-light:#eddecc;--primary:#6d4c5b;--primary-soft:#eddecc;--text:#111827;--muted:#b79c8b;--body:#7b5c69;--success-bg:#d1fae5;--success-text:#065f46;--warn-bg:#fef3c7;--warn-text:#92400e;--danger-bg:#fee2e2;--danger-text:#991b1b;--neutral-bg:#f3f4f6;--neutral-text:#57534e;max-width:1600px;margin:0 auto}

  .page-header{display:flex;align-items:flex-end;justify-content:space-between;gap:16px;margin-bottom:22px}
  .eyebrow{font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--muted);margin:0 0 4px}
  .admin-payment-page h1{font-size:22px;font-weight:700;color:var(--text);letter-spacing:-.3px;margin:0}
  .page-subtitle{margin:5px 0 0;color:var(--body);font-size:12px;font-weight:600}

  .btn-ghost{display:inline-flex;align-items:center;gap:6px;padding:0 14px;height:34px;border:1px solid var(--border);border-radius:.75rem;background:var(--surface);color:var(--primary);font-size:12px;font-weight:700;font-family:inherit;cursor:pointer;transition:background .12s;text-decoration:none;white-space:nowrap}
  .btn-ghost:hover{background:var(--primary-soft)}

  .toolbar{display:flex;align-items:center;gap:8px;margin-bottom:20px;flex-wrap:wrap}
  .filters{display:flex;gap:6px;flex-wrap:wrap}
  .filter{display:inline-flex;align-items:center;height:34px;padding:0 14px;border:1px solid var(--border);border-radius:.75rem;background:var(--soft);color:var(--body);font-size:12px;font-weight:700;font-family:inherit;white-space:nowrap;text-decoration:none}
  .filter.active{border-color:var(--primary);background:var(--primary);color:#fcf8f5}
  .divider{width:1px;height:20px;background:var(--border);margin:0 4px}
  .queue-note{height:34px;display:inline-flex;align-items:center;border:1px solid var(--border);border-radius:.75rem;background:var(--surface);padding:0 12px;color:var(--body);font-size:12px;font-weight:700}

  .summary-row{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:20px}
  .stat{background:var(--surface);border:1px solid var(--border);border-radius:.75rem;padding:14px 16px}
  .stat-label{font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--muted);margin-bottom:6px}
  .stat-value{font-size:20px;font-weight:700;color:var(--text);letter-spacing:-.3px}
  .stat-sub{font-size:11px;color:var(--muted);margin-top:3px}
  .stat-value.warn{color:#92400e}
  .stat-value.danger{color:#991b1b}

  .card{background:var(--surface);border:1px solid var(--border);border-radius:.75rem;overflow:hidden;box-shadow:0 1px 2px rgba(28,25,23,.04)}
  .card-head{padding:14px 20px;border-bottom:1px solid var(--border-light);display:flex;align-items:center;justify-content:space-between;gap:12px}
  .card-head-left{display:flex;align-items:center;gap:8px}
  .card-head-icon{width:28px;height:28px;border-radius:.75rem;background:var(--primary-soft);display:flex;align-items:center;justify-content:center;color:var(--primary);font-size:13px}
  .card-head-title{font-size:13px;font-weight:700;color:var(--text)}
  .card-count{font-size:11px;color:var(--muted);font-weight:600;white-space:nowrap}

  .payment-table-wrap{overflow-x:auto}
  .payment-table{width:100%;border-collapse:collapse}
  .payment-table thead tr{background:var(--soft)}
  .payment-table thead th{padding:9px 20px;font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--muted);text-align:left;white-space:nowrap}
  .payment-table thead th:last-child{text-align:right}
  .payment-table tbody tr{border-top:1px solid var(--border-light);transition:background .1s}
  .payment-table tbody tr:hover{background:var(--soft)}
  .payment-table tbody td{padding:13px 20px;vertical-align:middle}
  .payment-table tbody td:last-child{text-align:right}

  .biz-name{font-weight:700;color:var(--text);font-size:13px}
  .biz-email{font-size:11px;color:var(--muted);margin-top:2px;max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
  .booking-link{color:var(--primary);text-decoration:none}
  .booking-link:hover{text-decoration:underline}
  .amount{font-weight:700;color:var(--text);white-space:nowrap}
  .expected{font-size:11px;color:var(--muted);margin-top:2px;white-space:nowrap}
  .method-text{font-size:12px;color:var(--body);font-weight:600}
  .date-text{font-size:12px;color:var(--muted);white-space:nowrap}
  .ref-code{display:inline-block;max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-family:monospace;font-size:12px;background:var(--soft);padding:3px 7px;border-radius:.5rem;border:1px solid var(--border-light);color:var(--body)}
  .slip-link{display:inline-flex;align-items:center;gap:6px;height:28px;border:1px solid var(--border);border-radius:.75rem;background:var(--soft);padding:0 9px;color:var(--primary);font-size:11px;font-weight:800;text-decoration:none;white-space:nowrap}
  .slip-link:hover{background:var(--hover)}
  .muted-text{font-size:11px;color:var(--muted)}

  .badge{display:inline-flex;align-items:center;border-radius:20px;padding:3px 9px;font-size:10px;font-weight:800;letter-spacing:.05em;text-transform:uppercase;white-space:nowrap}
  .badge-pending{background:var(--warn-bg);color:var(--warn-text)}
  .badge-success{background:var(--success-bg);color:var(--success-text)}
  .badge-failed{background:var(--danger-bg);color:var(--danger-text)}
  .filter{cursor:pointer}
  .review-meta{font-size:11px;color:var(--muted);white-space:nowrap}
  .review-note{font-size:11px;color:var(--body);max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}

  .payment-verification-form{display:flex;align-items:center;justify-content:flex-end;gap:6px}
  .note-input{width:150px;height:30px;border:1px solid var(--border);border-radius:.75rem;background:var(--surface);color:var(--text);font-size:11px;font-family:inherit;font-weight:600;padding:0 9px;outline:none}
  .note-input:focus{border-color:var(--primary)}
  .payment-actions{display:inline-flex;gap:6px;justify-content:flex-end}
  .action-btn{height:30px;border:0;border-radius:.75rem;padding:0 10px;color:#fcf8f5;font-size:11px;font-weight:800;font-family:inherit;cursor:pointer;white-space:nowrap}
  .action-approve{background:var(--primary)}
  .action-approve:hover{background:#7b5c69}
  .action-reject{background:#991b1b}
  .action-reject:hover{background:#7f1d1d}
  .empty-row{padding:34px 20px;text-align:center;color:var(--muted)}

  .pagination{display:flex;align-items:center;justify-content:space-between;padding:12px 20px;border-top:1px solid var(--border-light)}
  .page-info{font-size:12px;color:var(--muted)}
  .page-btns{display:flex;gap:4px}
  .page-btn{height:28px;min-width:28px;padding:0 8px;border:1px solid var(--border);border-radius:.75rem;background:var(--surface);color:var(--body);font-size:12px;font-family:inherit;font-weight:600;cursor:pointer;transition:all .12s}
  .page-btn.active{background:var(--primary);color:#fcf8f5;border-color:var(--primary)}
  .page-btn:disabled{opacity:.4;cursor:default}

  .toast{position:fixed;right:16px;top:16px;z-index:50;max-width:360px;opacity:0;pointer-events:none;transition:all .2s;border-radius:.75rem;border:1px solid var(--border);background:var(--surface);padding:12px 14px;font-size:12px;font-weight:800;color:var(--body)}
  .toast.show{opacity:1;pointer-events:auto}
  .toast.success{border-color:#a7f3d0;background:#ecfdf5;color:#065f46}
  .toast.error{border-color:#fecdd3;background:#fff1f2;color:#991b1b}

  @media(max-width:1200px){.summary-row{grid-template-columns:repeat(2,1fr)}.payment-verification-form{align-items:flex-end;flex-direction:column}.note-input{width:170px}}
  @media(max-width:760px){.admin-payment-outlet{padding:20px 16px}.page-header{align-items:flex-start;flex-direction:column}.summary-row{grid-template-columns:1fr}.pagination{align-items:flex-start;flex-direction:column;gap:10px}}
</style>

<div class="admin-payment-page">
  <h2 class="sr-only">Payment Verification - review customer manual deposit proofs</h2>

  <div class="page-header">
    <div>
      <p class="eyebrow">Payments</p>
      <h1><?= $h($copy['title']) ?></h1>
      <p class="page-subtitle"><?= $h($copy['subtitle']) ?></p>
    </div>
  </div>

  <div class="toolbar">
    <div class="filters">
      <a href="<?= URLROOT ?>/admin/paymentVerification?status=pending" class="filter <?= $activeStatus === 'pending' ? 'active' : '' ?>">Pending review</a>
      <a href="<?= URLROOT ?>/admin/paymentVerification?status=verified" class="filter <?= $activeStatus === 'verified' ? 'active' : '' ?>">Verified</a>
      <a href="<?= URLROOT ?>/admin/paymentVerification?status=rejected" class="filter <?= $activeStatus === 'rejected' ? 'active' : '' ?>">Rejected</a>
    </div>
    <div class="divider"></div>
    <span class="queue-note"><?= $h($copy['note']) ?></span>
  </div>

  <div class="summary-row">
    <div class="stat">
      <div class="stat-label"><?= $isPending ? 'Awaiting Review' : ($activeStatus === 'verified' ? 'Verified' : 'Rejected') ?></div>
      <div class="stat-value <?= $isPending ? 'warn' : '' ?>"><?= (int)$pendingCount ?></div>
      <div class="stat-sub">Customer deposit proofs</div>
    </div>
    <div class="stat">
      <div class="stat-label"><?= $isPending ? 'Submitted Amount' : 'Total Amount' ?></div>
      <div class="stat-value"><?= $money($pendingTotal) ?></div>
      <div class="stat-sub"><?= $isPending ? 'From pending records' : 'Across these deposits' ?></div>
    </div>
    <div class="stat">
      <div class="stat-label">Expected Deposit</div>
      <div class="stat-value"><?= $money($expectedTotal) ?></div>
      <div class="stat-sub"><?= BOOKING_DEPOSIT_PERCENT ?>% booking deposits</div>
    </div>
    <?php if ($isPending): ?>
    <div class="stat">
      <div class="stat-label">Needs Fix</div>
      <div class="stat-value <?= $missingCount > 0 ? 'danger' : '' ?>"><?= (int)$missingCount ?></div>
      <div class="stat-sub">Missing payment records</div>
    </div>
    <?php endif; ?>
  </div>

  <div class="card">
    <div class="card-head">
      <div class="card-head-left">
        <div class="card-head-icon"><i data-lucide="badge-check" class="h-4 w-4" aria-hidden="true"></i></div>
        <span class="card-head-title"><?= $h($copy['card']) ?></span>
      </div>
      <span class="card-count"><?= (int)$pendingCount ?> records</span>
    </div>

    <div class="payment-table-wrap">
      <table class="payment-table">
        <thead>
          <tr>
            <th>Booking</th>
            <th>Amount</th>
            <th>Method</th>
            <th>Sender</th>
            <th>Transaction ID</th>
            <th>Slip</th>
            <th>Submitted</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($pendingPayments)): ?>
            <tr>
              <td colspan="9" class="empty-row"><?= $isPending ? 'All payment proofs are reviewed.' : ($activeStatus === 'verified' ? 'No verified deposits yet.' : 'No rejected deposits.') ?></td>
            </tr>
          <?php endif; ?>

          <?php foreach ($pendingPayments as $payment): ?>
            <?php
              $bookingId = (int)($payment['id'] ?? 0);
              $paymentId = (int)($payment['payment_id'] ?? 0);
              $bookingRef = (string)($payment['booking_ref'] ?? ('Booking #' . $bookingId));
              $customerName = (string)($payment['name'] ?? 'Unknown customer');
              $customerEmail = (string)($payment['email'] ?? '');
              $totalAmount = (float)($payment['total_amount'] ?? 0);
              $expectedDeposit = $totalAmount * (BOOKING_DEPOSIT_PERCENT / 100);
              $paidAmountRaw = $payment['paid_amount'] ?? $payment['payment_amount'] ?? null;
              $paidAmount = $paidAmountRaw !== null && $paidAmountRaw !== '' ? (float)$paidAmountRaw : $expectedDeposit;
              $method = (string)($payment['bank_name'] ?? $payment['method'] ?? '-');
              $accountName = (string)($payment['account_name'] ?? '-');
              $mobileNumber = (string)($payment['mobile_number'] ?? '');
              $reference = trim((string)($payment['transaction_ref'] ?? ''));
              $slipPath = trim((string)($payment['payment_slip_path'] ?? ''));
              $hasSlip = $slipPath !== '' && preg_match('/\.(jpe?g|png|webp|gif|pdf)$/i', $slipPath) === 1;
              $submittedAt = $dateTime($payment['payment_created_at'] ?? $payment['paid_at'] ?? null);
              $paymentStatus = (string)($payment['payment_status'] ?? ($isPending ? 'pending' : ''));
              $reviewedAt = $dateTime($payment['verified_at'] ?? null);
              $reviewNote = trim((string)($payment['verified_note'] ?? ''));
            ?>
            <tr data-payment-row="<?= $bookingId ?>">
              <td>
                <div class="biz-name">
                  <a class="booking-link" href="<?= URLROOT ?>/admin/bookingDetail/<?= $bookingId ?>"><?= $h($bookingRef) ?></a>
                </div>
                <div class="biz-email"><?= $h($customerName) ?><?= $customerEmail !== '' ? ' - ' . $h($customerEmail) : '' ?></div>
              </td>
              <td>
                <div class="amount"><?= $money($paidAmount) ?></div>
                <div class="expected">Expected <?= $money($expectedDeposit) ?></div>
              </td>
              <td><span class="method-text"><?= $h($method ?: '-') ?></span></td>
              <td>
                <div class="method-text"><?= $h($accountName ?: '-') ?></div>
                <?php if ($mobileNumber !== ''): ?>
                  <div class="biz-email"><?= $h($mobileNumber) ?></div>
                <?php endif; ?>
              </td>
              <td><span class="ref-code"><?= $h($reference !== '' ? $reference : '-') ?></span></td>
              <td>
                <?php if ($hasSlip): ?>
                  <a class="slip-link" href="<?= URLROOT ?>/<?= $h($slipPath) ?>" target="_blank" rel="noopener">
                    <i data-lucide="image" class="h-3.5 w-3.5" aria-hidden="true"></i>
                    View
                  </a>
                <?php else: ?>
                  <span class="muted-text">-</span>
                <?php endif; ?>
              </td>
              <td><span class="date-text"><?= $h($submittedAt) ?></span></td>
              <td>
                <?php if ($paymentStatus === 'success'): ?>
                  <span class="badge badge-success">Verified</span>
                <?php elseif ($paymentStatus === 'failed'): ?>
                  <span class="badge badge-failed">Rejected</span>
                <?php elseif ($paymentId > 0): ?>
                  <span class="badge badge-pending">Pending</span>
                <?php else: ?>
                  <span class="badge badge-failed">Missing</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($isPending && $paymentId > 0): ?>
                  <form class="payment-verification-form" data-booking-id="<?= $bookingId ?>">
                    <input type="text" name="note" class="note-input" placeholder="Note">
                    <div class="payment-actions">
                      <button type="button" class="action-btn action-approve verify-payment-btn">Approve</button>
                      <button type="button" class="action-btn action-reject reject-payment-btn">Reject</button>
                    </div>
                  </form>
                <?php elseif ($isPending): ?>
                  <a href="<?= URLROOT ?>/admin/bookingDetail/<?= $bookingId ?>" class="btn-ghost">Open</a>
                <?php else: ?>
                  <div class="review-meta"><?= $h($reviewedAt) ?></div>
                  <?php if ($reviewNote !== ''): ?>
                    <div class="review-note" title="<?= $h($reviewNote) ?>"><?= $h($reviewNote) ?></div>
                  <?php endif; ?>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <?php
    if (isset($currentPage, $totalPages, $totalCount, $perPage)) {
        $h = function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };
        $baseParams = 'status=' . urlencode($activeStatus ?? 'pending');
        require APPROOT . '/views/partials/_pagination.php';
    }
    ?>
  </div>
</div>

<div id="toast" class="toast"></div>

<script>
function showToast(message, type = 'success') {
  const toast = document.getElementById('toast');
  toast.textContent = message;
  toast.className = `toast show ${type === 'error' ? 'error' : 'success'}`;

  setTimeout(() => {
    toast.classList.remove('show');
  }, 3500);
}

document.querySelectorAll('.payment-verification-form').forEach(form => {
  form.addEventListener('click', async event => {
    const verifyButton = event.target.closest('.verify-payment-btn');
    const rejectButton = event.target.closest('.reject-payment-btn');

    if (verifyButton) {
      await handleVerification(form, true);
    }

    if (rejectButton) {
      await handleVerification(form, false);
    }
  });
});

async function handleVerification(form, approve) {
  const bookingId = form.dataset.bookingId;
  const noteField = form.querySelector('[name="note"]');
  const endpoint = approve
    ? '<?= URLROOT ?>/admin/verifyPaymentPost'
    : '<?= URLROOT ?>/admin/rejectPaymentSlipPost';

  const formData = new FormData();
  formData.append('booking_id', bookingId);
  formData.append('note', noteField ? noteField.value : '');

  if (!approve) {
    const reason = prompt('Reason for rejecting this payment proof:');
    if (!reason) return;
    formData.set('reason', reason);
  }

  const actionButton = approve
    ? form.querySelector('.verify-payment-btn')
    : form.querySelector('.reject-payment-btn');
  if (actionButton) {
    actionButton.disabled = true;
    actionButton.dataset.originalText = actionButton.textContent;
    actionButton.textContent = approve ? 'Verifying...' : 'Rejecting...';
  }

  try {
    const response = await fetch(endpoint, { method: 'POST', body: formData });
    const data = await response.json();

    if (data.success) {
      showToast(data.message || 'Payment review saved.', data.email_sent === false ? 'error' : 'success');
      if (approve) {
        const row = form.closest('tr');
        const statusBadge = row?.querySelector('.badge');
        if (statusBadge) {
          statusBadge.className = 'badge badge-success';
          statusBadge.textContent = 'Verified';
        }
        const actionCell = form.closest('td');
        if (actionCell) {
          actionCell.innerHTML = `<div class="review-meta">Email ${data.email_sent ? 'sent to ' + escapeHtml(data.email_to || 'customer') : 'could not be sent'}</div>`;
        }
      } else {
        window.location.reload();
      }
    } else {
      showToast(data.error || 'Operation failed.', 'error');
      if (actionButton) {
        actionButton.disabled = false;
        actionButton.textContent = actionButton.dataset.originalText || (approve ? 'Approve' : 'Reject');
      }
    }
  } catch (error) {
    showToast('Connection error. Please try again.', 'error');
    if (actionButton) {
      actionButton.disabled = false;
      actionButton.textContent = actionButton.dataset.originalText || (approve ? 'Approve' : 'Reject');
    }
  }
}

function escapeHtml(value) {
  const element = document.createElement('span');
  element.textContent = String(value);
  return element.innerHTML;
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
  <?php require_once APPROOT . '/views/dashboardLayout/sidebar.php'; ?>
</body>
</html>
