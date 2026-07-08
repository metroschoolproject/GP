<?php
$payments = $payments ?? [];
$status = $status ?? 'pending';
$paymentTypeFilter = $paymentTypeFilter ?? 'all';
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

$typeFilters = [
    'all' => 'All types',
    'deposit' => 'Deposit',
    'remaining' => 'Final',
    'full' => 'Full',
    'replacement_delta' => 'Extra charge',
    'supplier_fee' => 'Supplier fee',
    'payout' => 'Payout',
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

$today = date('Y-m-d');
$dateTo = $dateTo ?? $today;
$dateFrom = $dateFrom ?? $today;

$paymentTypeMeta = static function ($type) {
    $key = strtolower(trim((string)$type));
    $map = [
        'deposit' => ['Deposit', 'type-deposit'],
        'remaining' => ['Final payment', 'type-remaining'],
        'full' => ['Full payment', 'type-full'],
        'replacement_delta' => ['Extra charge', 'type-extra'],
        'supplier_fee' => ['Supplier fee', 'type-supplier-fee'],
        'payout' => ['Supplier payout', 'type-payout'],
    ];

    if (isset($map[$key])) {
        return ['key' => $key, 'label' => $map[$key][0], 'class' => $map[$key][1]];
    }

    $label = $key !== '' ? ucwords(str_replace('_', ' ', $key)) : 'Payment';
    return ['key' => $key, 'label' => $label, 'class' => 'type-other'];
};

$dashboardContent = function () use (
    $payments,
    $status,
    $selectedPaymentId,
	    $message,
	    $filters,
	    $typeFilters,
	    $paymentTypeFilter,
	    $visibleTotal,
    $visibleApproved,
    $visibleRejected,
    $visiblePending,
    $approvedAmount,
    $rejectedAmount,
    $pendingAmount,
    $totalPlatformFee,
    $today,
    $dateFrom,
    $dateTo,
    $currentPage,
    $totalPages,
    $totalCount,
    $perPage,
    $paymentTypeMeta
) {
    $paymentProofPaths = static function ($raw): array {
        $raw = trim((string)$raw);
        if ($raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        $paths = is_array($decoded) ? $decoded : [$raw];

        return array_values(array_filter(array_map(static fn($path) => trim((string)$path), $paths), static function ($path) {
            return $path !== '' && preg_match('/\.(jpe?g|png|webp|pdf)$/i', $path) === 1;
        }));
    };
?>
<style>
  .admin-payment-outlet{min-height:100%;background:#F4F1EE;padding:28px 32px;font-size:13.5px;overflow-y:auto}
  .admin-payment-page *{box-sizing:border-box}
  .admin-payment-page{--bg:#F4F1EE;--surface:#FFFFFF;--soft:#FFFFFF;--hover:#eddecc;--border:#ead8c7;--border-light:#eddecc;--primary:#6d4c5b;--primary-hover:#7b5c69;--primary-soft:#eddecc;--text:#111827;--muted:#b79c8b;--body:#7b5c69;--success-bg:#ECFDF5;--success-text:#065F46;--warn-bg:#FFFBEB;--warn-text:#92400E;--danger-bg:#FEF2F2;--danger-text:#991B1B;--neutral-bg:#F5F5F4;--neutral-text:#78716C;max-width:1600px;margin:0 auto}

  .page-header{display:flex;align-items:flex-end;justify-content:space-between;margin-bottom:22px}
  .eyebrow{font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--muted);margin-bottom:4px}
  .admin-payment-page h1{font-size:22px;font-weight:700;color:var(--text);letter-spacing:-.3px;margin:0}

  .btn-ghost{display:inline-flex;align-items:center;gap:6px;padding:0 14px;height:34px;border:1px solid var(--border);border-radius:.75rem;background:var(--surface);color:var(--primary);font-size:12px;font-weight:700;font-family:inherit;cursor:pointer;transition:background .12s;text-decoration:none}
  .btn-ghost:hover{background:var(--primary-soft)}

  .toolbar{display:flex;align-items:center;gap:8px;margin-bottom:20px;flex-wrap:wrap}
  .filters{display:flex;gap:6px;flex-wrap:wrap}
  .filter{display:inline-flex;align-items:center;height:34px;padding:0 14px;border:1px solid var(--border);border-radius:.75rem;background:var(--soft);color:var(--body);font-size:12px;font-weight:700;font-family:inherit;cursor:pointer;transition:all .12s;white-space:nowrap;text-decoration:none}
  .filter:hover{border-color:var(--border);background:var(--hover);color:var(--primary)}
  .filter.active{border-color:var(--primary);background:var(--primary);color:#FFFFFF}

  .divider{width:1px;height:20px;background:var(--border);margin:0 4px}
  .date-range{display:flex;align-items:center;gap:6px}
  .date-label{font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.08em;white-space:nowrap}
  .date-input{height:34px;padding:0 10px;border:1px solid var(--border);border-radius:.75rem;background:var(--surface);color:var(--text);font-size:12px;font-family:inherit;font-weight:500;cursor:pointer;outline:none;transition:border-color .12s;width:130px}
  .date-input:focus{border-color:var(--primary)}
  .date-sep{font-size:11px;color:var(--muted);font-weight:600}

	  .type-filter-wrap{display:flex;align-items:center;gap:6px}
	  .type-select{height:34px;min-width:142px;padding:0 34px 0 11px;border:1px solid var(--border);border-radius:.75rem;background:var(--surface);color:var(--body);font-size:11px;font-weight:800;font-family:inherit;cursor:pointer;outline:none}
	  .type-select:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(109,76,91,.1)}
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
  .stat-value.success{color:#065F46}
  .stat-value.warn{color:#92400E}
  .stat-value.danger{color:#991B1B}

  .card{background:var(--surface);border:1px solid var(--border);border-radius:.75rem;overflow:hidden;box-shadow:0 1px 2px rgba(28,25,23,.04)}
  .card-head{padding:14px 20px;border-bottom:1px solid var(--border-light);display:flex;align-items:center;justify-content:space-between}
  .card-head-left{display:flex;align-items:center;gap:8px}
  .card-head-icon{width:28px;height:28px;border-radius:.75rem;background:var(--primary-soft);display:flex;align-items:center;justify-content:center;color:var(--primary);font-size:13px}
  .card-head-title{font-size:13px;font-weight:700;color:var(--text)}
  .card-count{font-size:11px;color:var(--muted);font-weight:600}

  .payment-table-wrap{overflow-x:auto}
	  .payment-table{width:100%;min-width:1375px;border-collapse:collapse;table-layout:fixed}
  .payment-table thead tr{background:var(--soft)}
  .payment-table thead th{padding:9px 20px;font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--muted);text-align:left;white-space:nowrap}
	  .payment-table thead th:last-child{text-align:left}
  .payment-table tbody tr{border-top:1px solid var(--border-light);transition:background .1s}
  .payment-table tbody tr:hover,.payment-table tbody tr.is-selected{background:var(--soft)}
  .payment-table tbody td{padding:13px 20px;vertical-align:middle}
	  .payment-table tbody td:last-child{text-align:left}
	  .payment-table .col-transaction{width:230px}
	  .payment-table .col-type{width:130px}
	  .payment-table .col-amount{width:120px}
	  .payment-table .col-fee{width:95px}
	  .payment-table .col-bank{width:105px}
	  .payment-table .col-sender{width:130px}
	  .payment-table .col-ref{width:140px}
	  .payment-table .col-slip{width:80px}
	  .payment-table .col-status{width:95px}
	  .payment-table .col-date{width:120px}
	  .payment-table .col-action{width:150px}

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
  .type-pill{display:inline-flex;align-items:center;border-radius:20px;padding:4px 9px;font-size:10px;font-weight:900;letter-spacing:.04em;text-transform:uppercase;white-space:nowrap}
  .type-deposit{background:#e8e7ff;color:#4f46a5}
  .type-remaining{background:#fdf4ff;color:#86198f}
  .type-full{background:#dcfce7;color:#166534}
  .type-extra{background:#ffedd5;color:#9a3412}
  .type-supplier-fee{background:#e0f2fe;color:#075985}
  .type-payout{background:#ecfdf5;color:#047857}
  .type-other{background:var(--neutral-bg);color:var(--neutral-text)}

	  .action-cell{display:flex;justify-content:flex-start;min-width:0}
	  .payment-action-link{width:100%;min-height:38px;display:grid;grid-template-columns:auto 1fr;align-items:center;column-gap:8px;border:1px solid var(--border);border-radius:.75rem;background:#fff;color:var(--primary);padding:7px 10px;text-decoration:none;transition:background .12s,border-color .12s,box-shadow .12s}
	  .payment-action-link:hover{background:var(--primary-soft);border-color:#ddc8b9;box-shadow:0 6px 14px rgba(109,76,91,.08)}
	  .payment-action-link i{grid-row:1 / span 2;width:14px;height:14px;color:currentColor}
	  .action-main{display:block;font-size:11px;font-weight:900;line-height:1;color:var(--primary)}
	  .action-sub{display:block;margin-top:3px;font-size:9px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:var(--muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
	  .payment-actions{width:100%;display:grid;grid-template-columns:1fr 1fr;gap:6px}
	  .payment-actions form{margin:0;min-width:0}
	  .action-btn{width:100%;height:34px;border:1px solid transparent;border-radius:.75rem;padding:0 8px;color:#FFFFFF;font-size:10px;font-weight:900;font-family:inherit;cursor:pointer;transition:transform .12s,box-shadow .12s,background .12s}
	  .action-btn:hover{transform:translateY(-1px);box-shadow:0 7px 14px rgba(28,25,23,.12)}
	  .action-approve{background:var(--primary)}
	  .action-approve:hover{background:#5a3e4a}
	  .action-reject{background:#b91c1c}
	  .action-reject:hover{background:#991b1b}
	  .action-muted{display:inline-flex;align-items:center;justify-content:center;width:100%;min-height:34px;border:1px solid var(--border-light);border-radius:.75rem;background:#faf7f4;color:var(--muted);font-size:10px;font-weight:850;text-transform:uppercase;letter-spacing:.05em}
	  .empty-row{padding:34px 20px;text-align:center;color:var(--muted)}

  .pagination{display:flex;align-items:center;justify-content:space-between;padding:12px 20px;border-top:1px solid var(--border-light)}
  .page-info{font-size:12px;color:var(--muted)}
  .page-btns{display:flex;gap:4px;flex-wrap:wrap}
  .page-btn{display:inline-flex;align-items:center;justify-content:center;gap:4px;height:28px;min-width:28px;padding:0 8px;border:1px solid var(--border);border-radius:.75rem;background:var(--surface);color:var(--body);font-size:12px;font-family:inherit;font-weight:600;cursor:pointer;transition:all .12s;text-decoration:none}
  .page-btn-label{line-height:1}
  .page-btn:hover{background:var(--soft)}
  .page-btn.active{background:var(--primary);color:#FFFFFF;border-color:var(--primary)}
  .page-btn:disabled{opacity:.4;cursor:default}

  @media(max-width:1100px){.summary-row{grid-template-columns:repeat(2,1fr)}}
	  @media(max-width:760px){.admin-payment-outlet{padding:20px 16px}.summary-row{grid-template-columns:1fr}.date-range,.type-filter-wrap{flex-wrap:wrap}.type-select{flex:1;min-width:180px}.btn-export{margin-left:0}.payment-actions{grid-template-columns:1fr}}
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
      <?php
	        $filterBase = 'date_from=' . urlencode($dateFrom) . '&date_to=' . urlencode($dateTo) . '&type=' . urlencode($paymentTypeFilter);
      ?>
      <?php foreach ($filters as $filter => $label): ?>
        <a href="<?= URLROOT ?>/admin/payments?status=<?= urlencode($filter) ?>&<?= $filterBase ?>" class="filter <?= ($status === $filter || ($filter === 'rejected' && $status === 'failed')) ? 'active' : '' ?>">
          <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
        </a>
      <?php endforeach; ?>
    </div>

    <div class="divider"></div>

    <div class="date-range">
      <span class="date-label">From</span>
      <input type="date" class="date-input" id="date-from" value="<?= htmlspecialchars($dateFrom, ENT_QUOTES, 'UTF-8') ?>" min="<?= htmlspecialchars($today, ENT_QUOTES, 'UTF-8') ?>">
      <span class="date-sep">-</span>
      <span class="date-label">To</span>
      <input type="date" class="date-input" id="date-to" value="<?= htmlspecialchars($dateTo, ENT_QUOTES, 'UTF-8') ?>" min="<?= htmlspecialchars(max($today, $dateFrom), ENT_QUOTES, 'UTF-8') ?>">
      <button type="button" class="qd" id="btn-apply-dates" style="font-weight:700">Apply</button>
    </div>

	    <div class="divider"></div>

	    <div class="type-filter-wrap">
	      <span class="date-label">Type</span>
	      <select class="type-select" id="payment-type-filter" aria-label="Filter by transaction type">
	        <?php foreach ($typeFilters as $typeValue => $typeLabel): ?>
	          <option value="<?= htmlspecialchars($typeValue, ENT_QUOTES, 'UTF-8') ?>" <?= $paymentTypeFilter === $typeValue ? 'selected' : '' ?>>
	            <?= htmlspecialchars($typeLabel, ENT_QUOTES, 'UTF-8') ?>
	          </option>
	        <?php endforeach; ?>
	      </select>
	    </div>

    <button type="button" class="btn-export" id="btn-export-csv">
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
	        <colgroup>
	          <col class="col-transaction">
	          <col class="col-type">
	          <col class="col-amount">
	          <col class="col-fee">
	          <col class="col-bank">
	          <col class="col-sender">
	          <col class="col-ref">
	          <col class="col-slip">
	          <col class="col-status">
	          <col class="col-date">
	          <col class="col-action">
	        </colgroup>
	        <thead>
          <tr>
            <th>Transaction</th>
            <th>Payment type</th>
            <th>Amount</th>
            <th>Fee</th>
            <th>Bank</th>
            <th>Sender Name</th>
            <th>Payment reference</th>
            <th>Slip</th>
            <th>Status</th>
            <th>Date</th>
	            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($payments)): ?>
            <tr>
              <td colspan="11" class="empty-row">No payment transactions found.</td>
            </tr>
          <?php endif; ?>

          <?php foreach ($payments as $payment): ?>
            <?php
              $paymentId = (int)($payment['id'] ?? 0);
              $typeMeta = $paymentTypeMeta($payment['type'] ?? '');
              $paymentType = $typeMeta['key'];
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
              $slipPaths   = $paymentProofPaths($payment['payment_slip_path'] ?? '');
              $hasSlip     = !empty($slipPaths);
              $isCustomerPayment = !empty($payment['booking_id']);
              $recordRef = $isCustomerPayment
                ? ($payment['booking_ref'] ?? ('Booking #' . (int)$payment['booking_id']))
                : ($payment['shop_name'] ?? 'Supplier');
              $transactionName = $typeMeta['label'] . ' · ' . $recordRef;
              $transactionEmail = $isCustomerPayment
                ? ($payment['customer_email'] ?? '-')
                : ($payment['owner_email'] ?? '-');
              $replacementId = (int)($payment['replacement_id'] ?? 0);
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
              <td><span class="type-pill <?= htmlspecialchars($typeMeta['class'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($typeMeta['label'], ENT_QUOTES, 'UTF-8') ?></span></td>
              <td><span class="amount"><?= number_format((float)($payment['amount'] ?? 0)) ?> MMK</span></td>
              <td><span class="amount" style="color:var(--primary)"><?= ((float)($payment['platform_fee'] ?? 0) > 0) ? number_format((float)$payment['platform_fee'], 0) . ' MMK' : '—' ?></span></td>
              <td><span class="method-text"><?= $bankDisplay ?></span></td>
              <td><span class="method-text"><?= $senderName ?></span></td>
              <td>
                <span class="ref-code"><?= htmlspecialchars($txnRef !== '' ? $txnRef : '-', ENT_QUOTES, 'UTF-8') ?></span>
              </td>
              <td>
                <?php if ($hasSlip): ?>
                  <?php foreach ($slipPaths as $index => $slipPath): ?>
                    <a class="slip-link" href="<?= URLROOT ?>/<?= htmlspecialchars($slipPath, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">
                      <i data-lucide="image" class="h-3.5 w-3.5"></i>
                      View<?= count($slipPaths) > 1 ? ' ' . ($index + 1) : '' ?>
                    </a>
                  <?php endforeach; ?>
                <?php else: ?>
                  <span class="reviewed-by">—</span>
                <?php endif; ?>
              </td>
              <td><span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($statusLabel, ENT_QUOTES, 'UTF-8') ?></span></td>
              <td><span class="date-text"><?= htmlspecialchars($submittedAt, ENT_QUOTES, 'UTF-8') ?></span></td>
	              <td>
	                <div class="action-cell">
	                <?php if ($paymentStatus === 'pending'): ?>
	                  <?php if (in_array($paymentType, ['deposit', 'remaining'], true)): ?>
	                    <a class="payment-action-link" href="<?= URLROOT ?>/admin/paymentVerification?status=pending">
	                      <i data-lucide="receipt-text" aria-hidden="true"></i>
	                      <span>
	                        <span class="action-main">Review</span>
	                        <span class="action-sub"><?= $paymentType === 'remaining' ? 'Final' : 'Deposit' ?></span>
	                      </span>
	                    </a>
	                  <?php elseif ($paymentType === 'replacement_delta'): ?>
	                    <?php if ($replacementId > 0): ?>
	                      <a class="payment-action-link" href="<?= URLROOT ?>/admin/replacementPicker/<?= $replacementId ?>">
	                        <i data-lucide="badge-check" aria-hidden="true"></i>
	                        <span>
	                          <span class="action-main">Verify</span>
	                          <span class="action-sub">Extra charge</span>
	                        </span>
	                      </a>
	                    <?php elseif ($isCustomerPayment): ?>
	                      <a class="payment-action-link" href="<?= URLROOT ?>/admin/bookingDetail/<?= (int)$payment['booking_id'] ?>">
	                        <i data-lucide="external-link" aria-hidden="true"></i>
	                        <span>
	                          <span class="action-main">Open</span>
	                          <span class="action-sub">Booking</span>
	                        </span>
	                      </a>
	                    <?php else: ?>
	                      <span class="action-muted">Needs link</span>
	                    <?php endif; ?>
	                  <?php elseif ($paymentType === 'supplier_fee'): ?>
	                    <div class="payment-actions">
                      <form method="POST" action="<?= URLROOT ?>/admin/approvePayment/<?= $paymentId ?>">
                        <button type="submit" class="action-btn action-approve">Approve</button>
                      </form>
                      <form method="POST" action="<?= URLROOT ?>/admin/rejectPayment/<?= $paymentId ?>">
                        <button type="submit" class="action-btn action-reject">Reject</button>
                      </form>
	                    </div>
	                  <?php elseif ($paymentType === 'payout'): ?>
	                    <a class="payment-action-link" href="<?= URLROOT ?>/admin/payouts">
	                      <i data-lucide="wallet-cards" aria-hidden="true"></i>
	                      <span>
	                        <span class="action-main">Review</span>
	                        <span class="action-sub">Payout</span>
	                      </span>
	                    </a>
	                  <?php else: ?>
	                    <?php if ($isCustomerPayment): ?>
	                      <a class="payment-action-link" href="<?= URLROOT ?>/admin/bookingDetail/<?= (int)$payment['booking_id'] ?>">
	                        <i data-lucide="external-link" aria-hidden="true"></i>
	                        <span>
	                          <span class="action-main">Open</span>
	                          <span class="action-sub">Booking</span>
	                        </span>
	                      </a>
	                    <?php else: ?>
	                      <span class="action-muted">No action</span>
	                    <?php endif; ?>
	                  <?php endif; ?>
	                <?php else: ?>
	                  <span class="action-muted">Reviewed</span>
	                <?php endif; ?>
	                </div>
	              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <?php
    if (isset($currentPage, $totalPages, $totalCount, $perPage)) {
        $h = function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };
	        $baseParams = 'status=' . urlencode($status ?? 'pending')
	                    . '&date_from=' . urlencode($dateFrom ?? '')
	                    . '&date_to=' . urlencode($dateTo ?? '')
	                    . '&type=' . urlencode($paymentTypeFilter ?? 'all');
        if (!empty($selectedPaymentId)) {
            $baseParams .= '&payment=' . (int)$selectedPaymentId;
        }
        $showSinglePage = true;
        $prevText = 'Previous';
        $nextText = 'Next';
        require APPROOT . '/views/partials/_pagination.php';
    }
    ?>
  </div>
</div>

<script>
  const ROOT = '<?= URLROOT ?>';
  const currentStatus = '<?= htmlspecialchars($status ?? 'pending', ENT_QUOTES, 'UTF-8') ?>';

  function buildUrl(overrides) {
    const params = new URLSearchParams(window.location.search);
    for (const [k, v] of Object.entries(overrides)) {
      if (v === null || v === undefined) params.delete(k);
      else params.set(k, v);
    }
    // Reset page when filters change
    params.delete('page');
    return ROOT + '/admin/payments?' + params.toString();
  }

  function navigate(overrides) {
    window.location.href = buildUrl(overrides);
  }

  const today = '<?= htmlspecialchars($today, ENT_QUOTES, 'UTF-8') ?>';
  const dateFromInput = document.getElementById('date-from');
  const dateToInput = document.getElementById('date-to');

  function clampPaymentDates() {
    if (!dateFromInput || !dateToInput) return;

    if (!dateFromInput.value || dateFromInput.value < today) {
      dateFromInput.value = today;
    }
    if (!dateToInput.value || dateToInput.value < today) {
      dateToInput.value = today;
    }
    dateToInput.min = dateFromInput.value || today;
    if (dateFromInput.value && dateToInput.value && dateToInput.value < dateFromInput.value) {
      dateToInput.value = dateFromInput.value;
    }
  }

	  // Apply button for custom date range
  document.getElementById('btn-apply-dates').addEventListener('click', () => {
    clampPaymentDates();
    navigate({ date_from: dateFromInput.value, date_to: dateToInput.value });
  });

  dateFromInput?.addEventListener('change', clampPaymentDates);
  dateToInput?.addEventListener('change', clampPaymentDates);
  clampPaymentDates();

	  document.getElementById('payment-type-filter')?.addEventListener('change', event => {
	    navigate({ type: event.target.value || 'all' });
	  });

  // Export CSV
  document.getElementById('btn-export-csv').addEventListener('click', () => {
    const table = document.querySelector('.payment-table');
    if (!table) return;
    const rows = [];
    table.querySelectorAll('tr').forEach(tr => {
      const cells = [];
      tr.querySelectorAll('th, td').forEach(cell => {
        cells.push('"' + cell.textContent.trim().replace(/"/g, '""') + '"');
      });
      rows.push(cells.join(','));
    });
    const blob = new Blob([rows.join('\n')], { type: 'text/csv' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'payments-<?= date('Y-m-d') ?>.csv';
    a.click();
  });
</script>
<?php
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php $pageTitle = 'Payments — Admin'; ?>
    <?php require_once APPROOT . '/views/dashboardLayout/head.php' ?>
</head>
<body class="grid h-screen gap-0 bg-app-page" style="grid-template-columns: 280px 1fr;">
    <?php require_once APPROOT . '/views/dashboardLayout/sidebar.php' ?>
</body>
</html>
