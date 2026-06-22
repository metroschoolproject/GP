<?php
$payments = $payments ?? [];
$status = $status ?? 'pending';
$selectedPaymentId = $selectedPaymentId ?? null;
$message = $message ?? '';

$dashboardTitle = 'Payments';
$dashboardCrumb = 'History';
$dashboardContentClass = 'admin-payment-outlet';

$filters = [
    'all' => 'All',
    'success' => 'Approved',
    'rejected' => 'Rejected',
    'pending' => 'Pending',
];

$visibleTotal = 0;
$visibleApproved = 0;
$visibleRejected = 0;
$visiblePending = 0;
$approvedAmount = 0;
$rejectedAmount = 0;
$pendingAmount = 0;
$totalPlatformFee = 0;

foreach ($payments as $payment) {
    $amount = (float)($payment['amount'] ?? 0);
    $paymentStatus = strtolower($payment['status'] ?? 'pending');

    $visibleTotal += $amount;
    $totalPlatformFee += (float)($payment['platform_fee'] ?? 0);

    if ($paymentStatus === 'success') {
        $visibleApproved++;
        $approvedAmount += $amount;
    } elseif ($paymentStatus === 'failed') {
        $visibleRejected++;
        $rejectedAmount += $amount;
    } else {
        $visiblePending++;
        $pendingAmount += $amount;
    }
}

$dateTo = date('Y-m-d');
$dateFrom = date('Y-m-d', strtotime('-30 days'));

$dashboardContent = function () use (
    $payments,
    $status,
    $selectedPaymentId,
    $message,
    $filters,
    $visibleTotal,
    $visibleApproved,
    $visibleRejected,
    $visiblePending,
    $approvedAmount,
    $rejectedAmount,
    $pendingAmount,
    $totalPlatformFee,
    $dateFrom,
    $dateTo
) {
?>
<style>
  .admin-payment-outlet{min-height:100%;background:#FBFBF9;padding:28px 32px;font-family:'DM Sans',system-ui,-apple-system,sans-serif;color:#111827;font-size:13px}
  .admin-payment-page *{box-sizing:border-box}
  .admin-payment-page{--bg:#FBFBF9;--surface:#fcf8f5;--soft:#faf5ef;--hover:#eddecc;--border:#ead8c7;--border-light:#eddecc;--primary:#6d4c5b;--primary-hover:#7b5c69;--primary-soft:#eddecc;--text:#111827;--muted:#b79c8b;--body:#7b5c69;--success-bg:#d1fae5;--success-text:#065f46;--warn-bg:#fef3c7;--warn-text:#92400e;--danger-bg:#fee2e2;--danger-text:#991b1b;--neutral-bg:#f3f4f6;--neutral-text:#57534e;max-width:1600px;margin:0 auto}

  .page-header{display:flex;align-items:flex-end;justify-content:space-between;margin-bottom:22px}
  .eyebrow{font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--muted);margin-bottom:4px}
  .admin-payment-page h1{font-size:22px;font-weight:700;color:var(--text);letter-spacing:-.3px;margin:0}

  .btn-ghost{display:inline-flex;align-items:center;gap:6px;padding:0 14px;height:34px;border:1px solid var(--border);border-radius:.75rem;background:var(--surface);color:var(--primary);font-size:12px;font-weight:700;font-family:inherit;cursor:pointer;transition:background .12s;text-decoration:none}
  .btn-ghost:hover{background:var(--primary-soft)}

  .toolbar{display:flex;align-items:center;gap:8px;margin-bottom:20px;flex-wrap:wrap}
  .filters{display:flex;gap:6px;flex-wrap:wrap}
  .filter{display:inline-flex;align-items:center;height:34px;padding:0 14px;border:1px solid var(--border);border-radius:.75rem;background:var(--soft);color:var(--body);font-size:12px;font-weight:700;font-family:inherit;cursor:pointer;transition:all .12s;white-space:nowrap;text-decoration:none}
  .filter:hover{border-color:var(--border);background:var(--hover);color:var(--primary)}
  .filter.active{border-color:var(--primary);background:var(--primary);color:#fcf8f5}

  .divider{width:1px;height:20px;background:var(--border);margin:0 4px}
  .date-range{display:flex;align-items:center;gap:6px}
  .date-label{font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.08em;white-space:nowrap}
  .date-input{height:34px;padding:0 10px;border:1px solid var(--border);border-radius:.75rem;background:var(--surface);color:var(--text);font-size:12px;font-family:inherit;font-weight:500;cursor:pointer;outline:none;transition:border-color .12s;width:130px}
  .date-input:focus{border-color:var(--primary)}
  .date-sep{font-size:11px;color:var(--muted);font-weight:600}

  .quick-dates{display:flex;gap:5px}
  .qd{height:34px;padding:0 11px;border:1px solid var(--border);border-radius:.75rem;background:var(--surface);color:var(--body);font-size:11px;font-weight:700;font-family:inherit;cursor:pointer;transition:all .12s;white-space:nowrap}
  .qd:hover{border-color:var(--border);background:var(--hover);color:var(--primary)}
  .qd.active{border-color:var(--primary);background:var(--primary-soft);color:var(--primary)}

  .btn-export{display:inline-flex;align-items:center;gap:6px;padding:0 14px;height:34px;border:1px solid var(--border);border-radius:.75rem;background:var(--surface);color:var(--body);font-size:12px;font-weight:700;font-family:inherit;cursor:pointer;transition:all .12s;margin-left:auto}
  .btn-export:hover{background:var(--soft)}

  .payment-message{border:1px solid var(--border);border-radius:.75rem;background:var(--surface);padding:12px 14px;margin-bottom:18px;color:var(--body);font-size:13px;font-weight:600}

  .summary-row{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:20px}
  .stat{background:var(--surface);border:1px solid var(--border);border-radius:.75rem;padding:14px 16px}
  .stat-label{font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--muted);margin-bottom:6px}
  .stat-value{font-size:20px;font-weight:700;color:var(--text);letter-spacing:-.3px}
  .stat-sub{font-size:11px;color:var(--muted);margin-top:3px}
  .stat-value.success{color:#065f46}
  .stat-value.warn{color:#92400e}
  .stat-value.danger{color:#991b1b}

  .card{background:var(--surface);border:1px solid var(--border);border-radius:.75rem;overflow:hidden;box-shadow:0 1px 2px rgba(28,25,23,.04)}
  .card-head{padding:14px 20px;border-bottom:1px solid var(--border-light);display:flex;align-items:center;justify-content:space-between}
  .card-head-left{display:flex;align-items:center;gap:8px}
  .card-head-icon{width:28px;height:28px;border-radius:.75rem;background:var(--primary-soft);display:flex;align-items:center;justify-content:center;color:var(--primary);font-size:13px}
  .card-head-title{font-size:13px;font-weight:700;color:var(--text)}
  .card-count{font-size:11px;color:var(--muted);font-weight:600}

  .payment-table-wrap{overflow-x:auto}
  .payment-table{width:100%;border-collapse:collapse}
  .payment-table thead tr{background:var(--soft)}
  .payment-table thead th{padding:9px 20px;font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--muted);text-align:left;white-space:nowrap}
  .payment-table thead th:last-child{text-align:right}
  .payment-table tbody tr{border-top:1px solid var(--border-light);transition:background .1s}
  .payment-table tbody tr:hover,.payment-table tbody tr.is-selected{background:var(--soft)}
  .payment-table tbody td{padding:13px 20px;vertical-align:middle}
  .payment-table tbody td:last-child{text-align:right}

  .biz-name{font-weight:600;color:var(--text);font-size:13px}
  .biz-email{font-size:11px;color:var(--muted);margin-top:2px}
  .amount{font-weight:700;color:var(--text)}
  .ref-code{display:inline-block;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-family:monospace;font-size:12px;background:var(--soft);padding:3px 7px;border-radius:.5rem;border:1px solid var(--border-light);color:var(--body)}
  .slip-link{display:inline-flex;align-items:center;gap:6px;height:28px;border:1px solid var(--border);border-radius:.75rem;background:var(--soft);padding:0 9px;color:var(--primary);font-size:11px;font-weight:800;text-decoration:none;white-space:nowrap}
  .slip-link:hover{background:var(--hover)}
  .method-text{font-size:12px;color:var(--body)}
  .date-text{font-size:12px;color:var(--muted)}
  .reviewed-by{font-size:11px;color:var(--muted)}

  .badge{display:inline-flex;align-items:center;border-radius:20px;padding:3px 9px;font-size:10px;font-weight:800;letter-spacing:.05em;text-transform:uppercase}
  .badge-pending{background:var(--warn-bg);color:var(--warn-text)}
  .badge-success{background:var(--success-bg);color:var(--success-text)}
  .badge-failed{background:var(--danger-bg);color:var(--danger-text)}
  .badge-refunded{background:#e0e7ff;color:#3730a3}

  .payment-actions{display:inline-flex;gap:6px;justify-content:flex-end}
  .action-btn{height:30px;border:0;border-radius:.75rem;padding:0 10px;color:#fcf8f5;font-size:11px;font-weight:800;font-family:inherit;cursor:pointer}
  .action-approve{background:var(--primary)}
  .action-reject{background:#991b1b}
  .empty-row{padding:34px 20px;text-align:center;color:var(--muted)}

  .pagination{display:flex;align-items:center;justify-content:space-between;padding:12px 20px;border-top:1px solid var(--border-light)}
  .page-info{font-size:12px;color:var(--muted)}
  .page-btns{display:flex;gap:4px}
  .page-btn{height:28px;min-width:28px;padding:0 8px;border:1px solid var(--border);border-radius:.75rem;background:var(--surface);color:var(--body);font-size:12px;font-family:inherit;font-weight:600;cursor:pointer;transition:all .12s}
  .page-btn:hover{background:var(--soft)}
  .page-btn.active{background:var(--primary);color:#fcf8f5;border-color:var(--primary)}
  .page-btn:disabled{opacity:.4;cursor:default}

  @media(max-width:1100px){.summary-row{grid-template-columns:repeat(2,1fr)}}
  @media(max-width:760px){.admin-payment-outlet{padding:20px 16px}.summary-row{grid-template-columns:1fr}.date-range{flex-wrap:wrap}.btn-export{margin-left:0}}
</style>

<div class="admin-payment-page">
  <h2 class="sr-only">Payment History - customer deposits and supplier payment transactions</h2>

  <div class="page-header">
    <div>
      <p class="eyebrow">Payments</p>
      <h1>Payment History</h1>
    </div>
  </div>

  <?php if (!empty($message)): ?>
    <div class="payment-message"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <div class="toolbar">
    <div class="filters">
      <?php foreach ($filters as $filter => $label): ?>
        <a href="<?= URLROOT ?>/admin/payments?status=<?= urlencode($filter) ?>" class="filter <?= ($status === $filter || ($filter === 'rejected' && $status === 'failed')) ? 'active' : '' ?>">
          <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
        </a>
      <?php endforeach; ?>
    </div>

    <div class="divider"></div>

    <div class="date-range">
      <span class="date-label">From</span>
      <input type="date" class="date-input" id="date-from" value="<?= htmlspecialchars($dateFrom, ENT_QUOTES, 'UTF-8') ?>">
      <span class="date-sep">-</span>
      <span class="date-label">To</span>
      <input type="date" class="date-input" id="date-to" value="<?= htmlspecialchars($dateTo, ENT_QUOTES, 'UTF-8') ?>">
    </div>

    <div class="divider"></div>

    <div class="quick-dates">
      <button class="qd" onclick="setRange(7,this)">7d</button>
      <button class="qd active" onclick="setRange(30,this)">30d</button>
      <button class="qd" onclick="setRange(90,this)">90d</button>
      <button class="qd" onclick="setRange(365,this)">1y</button>
    </div>

    <button type="button" class="btn-export">
      <i data-lucide="download" class="h-3.5 w-3.5" aria-hidden="true"></i>
      Export CSV
    </button>
  </div>

  <div class="summary-row" style="grid-template-columns:repeat(5,1fr)">
    <div class="stat">
      <div class="stat-label">Total Collected</div>
      <div class="stat-value"><?= number_format($visibleTotal) ?></div>
      <div class="stat-sub">MMK · <?= count($payments) ?> payments</div>
    </div>
    <div class="stat">
      <div class="stat-label">Platform Fees</div>
      <div class="stat-value" style="color:#6d4c5b"><?= number_format($totalPlatformFee) ?></div>
      <div class="stat-sub">MMK · <?= (int)get_platform_fee_percent() ?>% per booking</div>
    </div>
    <div class="stat">
      <div class="stat-label">Approved</div>
      <div class="stat-value success"><?= $visibleApproved ?></div>
      <div class="stat-sub"><?= number_format($approvedAmount) ?> MMK</div>
    </div>
    <div class="stat">
      <div class="stat-label">Rejected</div>
      <div class="stat-value danger"><?= $visibleRejected ?></div>
      <div class="stat-sub"><?= number_format($rejectedAmount) ?> MMK</div>
    </div>
    <div class="stat">
      <div class="stat-label">Pending</div>
      <div class="stat-value warn"><?= $visiblePending ?></div>
      <div class="stat-sub"><?= number_format($pendingAmount) ?> MMK</div>
    </div>
  </div>

  <div class="card">
    <div class="card-head">
      <div class="card-head-left">
        <div class="card-head-icon"><i data-lucide="receipt" class="h-4 w-4" aria-hidden="true"></i></div>
        <span class="card-head-title">All Transactions</span>
      </div>
      <span class="card-count"><?= count($payments) ?> records</span>
    </div>

    <div class="payment-table-wrap">
      <table class="payment-table">
        <thead>
          <tr>
            <th>Transaction</th>
            <th>Amount</th>
            <th>Fee</th>
            <th>Bank</th>
            <th>Sender Name</th>
            <th>Payment reference</th>
            <th>Slip</th>
            <th>Status</th>
            <th>Date</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($payments)): ?>
            <tr>
              <td colspan="10" class="empty-row">No payment transactions found.</td>
            </tr>
          <?php endif; ?>

          <?php foreach ($payments as $payment): ?>
            <?php
              $paymentId = (int)($payment['id'] ?? 0);
              $paymentStatus = strtolower($payment['status'] ?? 'pending');
              $escrowStatus  = strtolower($payment['escrow_status'] ?? '');
              $isRefunded    = $escrowStatus === 'refunded';
              $statusLabel = $paymentStatus === 'success' ? 'Approved' : ($paymentStatus === 'failed' ? 'Rejected' : 'Pending');
              $badgeClass = $paymentStatus === 'success' ? 'badge-success' : ($paymentStatus === 'failed' ? 'badge-failed' : 'badge-pending');
              if ($isRefunded) {
                  $statusLabel = 'Refunded';
                  $badgeClass = 'badge-refunded';
              }
              $submittedAt = !empty($payment['verified_at'] ?? null)
                ? date('M j, Y H:i', strtotime($payment['verified_at']))
                : (!empty($payment['created_at']) ? date('M j, Y H:i', strtotime($payment['created_at'])) : '-');
              $bankDisplay = htmlspecialchars($payment['bank_name'] ?? $payment['method'] ?? '-', ENT_QUOTES, 'UTF-8');
              $senderName  = htmlspecialchars($payment['account_name'] ?? '-', ENT_QUOTES, 'UTF-8');
              $txnRef      = trim((string)($payment['transaction_ref'] ?? ''));
              $slipPath    = trim((string)($payment['payment_slip_path'] ?? ''));
              $hasSlip     = $slipPath !== '' && preg_match('/\.(jpe?g|png|webp|pdf)$/i', $slipPath) === 1;
              $isCustomerPayment = !empty($payment['booking_id']);
              $transactionName = $isCustomerPayment
                ? 'Customer deposit · ' . ($payment['booking_ref'] ?? ('Booking #' . (int)$payment['booking_id']))
                : ($payment['shop_name'] ?? 'Supplier membership');
              $transactionEmail = $isCustomerPayment
                ? ($payment['customer_email'] ?? '-')
                : ($payment['owner_email'] ?? '-');
            ?>
            <tr class="<?= $selectedPaymentId === $paymentId ? 'is-selected' : '' ?>">
              <td>
                <div class="biz-name">
                  <?php if ($isCustomerPayment): ?>
                    <a href="<?= URLROOT ?>/admin/bookingDetail/<?= (int)$payment['booking_id'] ?>" style="color:var(--primary);text-decoration:none">
                      <?= htmlspecialchars($transactionName, ENT_QUOTES, 'UTF-8') ?>
                    </a>
                  <?php else: ?>
                    <?= htmlspecialchars($transactionName, ENT_QUOTES, 'UTF-8') ?>
                  <?php endif; ?>
                </div>
                <div class="biz-email"><?= htmlspecialchars($transactionEmail, ENT_QUOTES, 'UTF-8') ?></div>
              </td>
              <td><span class="amount"><?= number_format((float)($payment['amount'] ?? 0)) ?> MMK</span></td>
              <td><span class="amount" style="color:var(--primary)"><?= ((float)($payment['platform_fee'] ?? 0) > 0) ? number_format((float)$payment['platform_fee'], 0) . ' MMK' : '—' ?></span></td>
              <td><span class="method-text"><?= $bankDisplay ?></span></td>
              <td><span class="method-text"><?= $senderName ?></span></td>
              <td>
                <span class="ref-code"><?= htmlspecialchars($txnRef !== '' ? $txnRef : '-', ENT_QUOTES, 'UTF-8') ?></span>
              </td>
              <td>
                <?php if ($hasSlip): ?>
                  <a class="slip-link" href="<?= URLROOT ?>/<?= htmlspecialchars($slipPath, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">
                    <i data-lucide="image" class="h-3.5 w-3.5"></i>
                    View
                  </a>
                <?php else: ?>
                  <span class="reviewed-by">—</span>
                <?php endif; ?>
              </td>
              <td><span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8') ?></span></td>
              <td><span class="date-text"><?= htmlspecialchars($submittedAt, ENT_QUOTES, 'UTF-8') ?></span></td>
              <td>
                <?php if ($paymentStatus === 'pending'): ?>
                  <?php if ($isCustomerPayment): ?>
                    <a class="btn-ghost" href="<?= URLROOT ?>/admin/paymentVerification?status=pending">Review deposit</a>
                  <?php else: ?>
                    <div class="payment-actions">
                      <form method="POST" action="<?= URLROOT ?>/admin/approvePayment/<?= $paymentId ?>">
                        <button type="submit" class="action-btn action-approve">Approve</button>
                      </form>
                      <form method="POST" action="<?= URLROOT ?>/admin/rejectPayment/<?= $paymentId ?>">
                        <button type="submit" class="action-btn action-reject">Reject</button>
                      </form>
                    </div>
                  <?php endif; ?>
                <?php else: ?>
                  <span class="reviewed-by">Reviewed</span>
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
        $baseParams = 'status=' . urlencode($status ?? 'pending');
        if (!empty($selectedPaymentId)) {
            $baseParams .= '&payment=' . (int)$selectedPaymentId;
        }
        require APPROOT . '/views/partials/_pagination.php';
    }
    ?>
  </div>
</div>

<script>
  function setRange(days, el) {
    document.querySelectorAll('.qd').forEach(b => b.classList.remove('active'));
    el.classList.add('active');
    const to = new Date();
    const from = new Date();
    from.setDate(to.getDate() - days);
    document.getElementById('date-to').value = to.toISOString().split('T')[0];
    document.getElementById('date-from').value = from.toISOString().split('T')[0];
  }
  document.querySelectorAll('.date-input').forEach(input => {
    input.addEventListener('change', () => {
      document.querySelectorAll('.qd').forEach(b => b.classList.remove('active'));
    });
  });
</script>
<?php
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php require_once APPROOT . '/views/dashboardLayout/head.php' ?>
</head>
<body class="grid h-screen gap-0 bg-app-page" style="grid-template-columns: 280px 1fr;">
    <?php require_once APPROOT . '/views/dashboardLayout/sidebar.php' ?>
</body>
</html>
