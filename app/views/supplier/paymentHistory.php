<?php
$payments = $payments ?? [];
$supplierId = (int)($supplierId ?? 0);
$currentPage = (int)($currentPage ?? 1);
$totalPages = (int)($totalPages ?? 1);
$totalCount = (int)($totalCount ?? 0);
$totalReceived = (float)($totalReceived ?? 0);
$totalFees = (float)($totalFees ?? 0);
$approvedCount = (int)($approvedCount ?? 0);
$pendingCount = (int)($pendingCount ?? 0);

$dashboardTitle = 'Supplier';
$dashboardCrumb = 'Payment History';
$dashboardSearchPlaceholder = 'Search payments...';
$dashboardContentClass = 'bg-app-content px-6 py-6';
$dashboardBreadcrumbs = [
    ['label' => 'Dashboard', 'url' => URLROOT . '/supplier/dashboard'],
    ['label' => 'Payment History', 'url' => null],
];

$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$money = fn($v) => number_format((float)$v, 0) . ' MMK';
$formatDate = fn($v) => $v ? date('M d, Y', strtotime((string)$v)) : '—';

$dashboardContent = function () use (
    $payments, $supplierId, $currentPage, $totalPages, $totalCount,
    $totalReceived, $totalFees, $approvedCount, $pendingCount,
    $h, $money, $formatDate
) {
$filters = $filters ?? [];
?>
<style>
.payhist-page { --ink:#6d4c5b; --muted:#A8A29E; --soft:#F4F1EE; --panel:#FFFFFF; --line:#ead8c7; --primary:#6d4c5b; --green:#166534; --amber:#92400e; --danger:#991b1b; color:var(--ink); }
.payhist-page h1 { font-size:clamp(28px,3vw,42px); font-weight:900; margin:6px 0 7px; }
.payhist-page .kicker { font-size:10px; font-weight:900; text-transform:uppercase; letter-spacing:.17em; color:#A8A29E; }
.payhist-top { display:flex; justify-content:space-between; gap:18px; align-items:flex-start; margin-bottom:22px; }
.payhist-top p { color:var(--muted); font-size:13px; }

.payhist-summary { display:grid; grid-template-columns:repeat(4,1fr); gap:10px; margin-bottom:20px; }
.payhist-stat { background:var(--panel); border:1px solid var(--line); border-radius:14px; padding:16px 20px; }
.payhist-stat-label { font-size:10px; font-weight:800; letter-spacing:.1em; text-transform:uppercase; color:var(--muted); margin-bottom:6px; }
.payhist-stat-value { font-size:22px; font-weight:800; color:var(--ink); }
.payhist-stat-value.green { color:var(--green); }
.payhist-stat-value.amber { color:var(--amber); }
.payhist-stat-sub { font-size:11px; color:var(--muted); margin-top:3px; }

.payhist-table-wrap { background:var(--panel); border:1px solid var(--line); border-radius:14px; overflow:hidden; }
.payhist-table { width:100%; border-collapse:collapse; }
.payhist-table thead tr { background:var(--soft); }
.payhist-table thead th { padding:10px 16px; font-size:10px; font-weight:800; letter-spacing:.1em; text-transform:uppercase; color:var(--muted); text-align:left; }
.payhist-table tbody tr { border-top:1px solid var(--line); }
.payhist-table tbody tr:hover { background:var(--soft); }
.payhist-table tbody td { padding:12px 16px; font-size:13px; vertical-align:middle; }

.payhist-badge { display:inline-flex; align-items:center; border-radius:20px; padding:3px 10px; font-size:10px; font-weight:800; letter-spacing:.05em; text-transform:uppercase; }
.payhist-badge.success { background:#ECFDF5; color:#065f46; }
.payhist-badge.pending { background:#FFFBEB; color:#92400e; }
.payhist-badge.failed { background:#FEF2F2; color:#991b1b; }

.payhist-empty { padding:40px 20px; text-align:center; color:var(--muted); }
.payhist-pagination { display:flex; justify-content:space-between; align-items:center; padding:12px 16px; border-top:1px solid var(--line); }
.payhist-page-info { font-size:12px; color:var(--muted); }
.payhist-page-btns { display:flex; gap:6px; }
.payhist-btn { display:inline-flex; align-items:center; gap:6px; min-height:34px; padding:0 14px; border:1px solid var(--line); border-radius:999px; background:transparent; color:var(--ink); font-size:12px; font-weight:700; cursor:pointer; text-decoration:none; transition:all .14s; }
.payhist-btn:hover { border-color:var(--primary); color:var(--primary); }
.payhist-btn.active { background:var(--primary); color:#fff; border-color:var(--primary); }
.payhist-btn:disabled { opacity:.4; cursor:default; }

@media(max-width:900px){ .payhist-summary{grid-template-columns:repeat(2,1fr)} }
@media(max-width:600px){ .payhist-summary{grid-template-columns:1fr} }

.payhist-filters { display:flex; flex-wrap:wrap; gap:10px; align-items:center; margin-bottom:16px; }
.payhist-filter { position:relative; }
.payhist-filter select {
  appearance:none; min-height:34px; padding:0 32px 0 12px;
  border:1px solid var(--line); border-radius:999px;
  background:var(--panel); color:var(--ink);
  font-size:12px; font-weight:600; cursor:pointer;
  transition:border-color .15s, box-shadow .15s;
}
.payhist-filter select:focus { outline:none; border-color:var(--primary); box-shadow:0 0 0 3px rgba(109,76,91,.12); }
.payhist-filter::after {
  content:''; position:absolute; right:11px; top:50%; transform:translateY(-50%);
  border:4px solid transparent; border-top-color:var(--muted); pointer-events:none;
}
.payhist-filter-clear {
  display:inline-flex; align-items:center; gap:4px; min-height:34px; padding:0 12px;
  border:1px solid var(--line); border-radius:999px; background:transparent;
  color:var(--muted); font-size:12px; font-weight:600; cursor:pointer;
  transition:all .14s; text-decoration:none;
}
.payhist-filter-clear:hover { border-color:var(--danger); color:var(--danger); }
</style>

<section class="payhist-page">
  <div class="payhist-top">
    <div>
      <div class="kicker">Payment History</div>
      <h1>Customer Payments</h1>
      <p>All deposits and payments received for your services.</p>
    </div>
  </div>

  <div class="payhist-summary">
    <div class="payhist-stat">
      <div class="payhist-stat-label">Total Received</div>
      <div class="payhist-stat-value green"><?= $money($totalReceived) ?></div>
      <div class="payhist-stat-sub"><?= $approvedCount ?> approved payments</div>
    </div>
    <div class="payhist-stat">
      <div class="payhist-stat-label">Pending</div>
      <div class="payhist-stat-value amber"><?= $money($pendingCount > 0 ? 0 : 0) ?></div>
      <div class="payhist-stat-sub"><?= $pendingCount ?> awaiting verification</div>
    </div>
    <div class="payhist-stat">
      <div class="payhist-stat-label">Platform Fee</div>
      <div class="payhist-stat-value"><?= $money($totalFees) ?></div>
      <div class="payhist-stat-sub"><?= (int)get_platform_fee_percent() ?>% paid by customer</div>
    </div>
    <div class="payhist-stat">
      <div class="payhist-stat-label">Total Records</div>
      <div class="payhist-stat-value"><?= $totalCount ?></div>
      <div class="payhist-stat-sub">payment transactions</div>
    </div>
  </div>

  <?php
    $hasActiveFilters = ($filters['status'] ?? '') !== '' || ($filters['type'] ?? '') !== '' || ($filters['escrow'] ?? '') !== '';
    $filterBase = URLROOT . '/supplier/paymentHistory';
    $filterUrl = function ($overrides = []) use ($filters, $filterBase) {
        $params = array_merge($filters, $overrides);
        $params = array_filter($params, fn($v) => $v !== '');
        return $filterBase . ($params ? '?' . http_build_query($params) : '');
    };
  ?>
  <div class="payhist-filters">
    <div class="payhist-filter">
      <select onchange="if(this.value)window.location.href=this.value;else window.location.href='<?= $filterBase ?>'">
        <option value="">All Status</option>
        <option value="<?= $h($filterUrl(['status' => 'success'])) ?>" <?= ($filters['status'] ?? '') === 'success' ? 'selected' : '' ?>>Verified</option>
        <option value="<?= $h($filterUrl(['status' => 'pending'])) ?>" <?= ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
        <option value="<?= $h($filterUrl(['status' => 'failed'])) ?>" <?= ($filters['status'] ?? '') === 'failed' ? 'selected' : '' ?>>Failed</option>
      </select>
    </div>
    <div class="payhist-filter">
      <select onchange="if(this.value)window.location.href=this.value;else window.location.href='<?= $filterBase ?>'">
        <option value="">All Types</option>
        <option value="<?= $h($filterUrl(['type' => 'deposit'])) ?>" <?= ($filters['type'] ?? '') === 'deposit' ? 'selected' : '' ?>>Deposit</option>
        <option value="<?= $h($filterUrl(['type' => 'remaining'])) ?>" <?= ($filters['type'] ?? '') === 'remaining' ? 'selected' : '' ?>>Balance</option>
        <option value="<?= $h($filterUrl(['type' => 'full'])) ?>" <?= ($filters['type'] ?? '') === 'full' ? 'selected' : '' ?>>Full</option>
        <option value="<?= $h($filterUrl(['type' => 'payout'])) ?>" <?= ($filters['type'] ?? '') === 'payout' ? 'selected' : '' ?>>Payout</option>
      </select>
    </div>
    <div class="payhist-filter">
      <select onchange="if(this.value)window.location.href=this.value;else window.location.href='<?= $filterBase ?>'">
        <option value="">All Escrow</option>
        <option value="<?= $h($filterUrl(['escrow' => 'held'])) ?>" <?= ($filters['escrow'] ?? '') === 'held' ? 'selected' : '' ?>>Held</option>
        <option value="<?= $h($filterUrl(['escrow' => 'released'])) ?>" <?= ($filters['escrow'] ?? '') === 'released' ? 'selected' : '' ?>>Released</option>
        <option value="<?= $h($filterUrl(['escrow' => 'refunded'])) ?>" <?= ($filters['escrow'] ?? '') === 'refunded' ? 'selected' : '' ?>>Refunded</option>
      </select>
    </div>
    <?php if ($hasActiveFilters): ?>
      <a href="<?= $filterBase ?>" class="payhist-filter-clear">✕ Clear filters</a>
    <?php endif; ?>
  </div>

  <div class="payhist-table-wrap">
    <?php if (empty($payments)): ?>
      <div class="payhist-empty">
        <?php if ($hasActiveFilters): ?>
          No payments match your filters. <a href="<?= $filterBase ?>" style="color:var(--primary);font-weight:700">Clear filters</a>
        <?php else: ?>
          No payment history yet. Once customers start booking your services, payments will appear here.
        <?php endif; ?>
      </div>
    <?php else: ?>
      <table class="payhist-table">
        <thead>
          <tr>
            <th>Booking</th>
            <th>Customer</th>
            <th>Amount</th>
            <th>Platform Fee</th>
            <th>Your Earnings</th>
            <th>Type</th>
            <th>Status</th>
            <th>Escrow</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($payments as $payment):
            $status = strtolower($payment['status'] ?? 'pending');
            $statusLabel = $status === 'success' ? 'Verified' : ($status === 'failed' ? 'Failed' : 'Pending');
            $badgeClass = $status === 'success' ? 'success' : ($status === 'failed' ? 'failed' : 'pending');
            $typeLabel = ($payment['type'] ?? 'deposit') === 'deposit' ? 'Deposit' : (($payment['type'] ?? '') === 'remaining' ? 'Balance' : (($payment['type'] ?? '') === 'payout' ? 'Payout' : 'Payment'));
            $supplierAmount = (float)($payment['supplier_amount'] ?? (float)($payment['amount'] ?? 0) - (float)($payment['platform_fee'] ?? 0));
            $escrow = strtolower($payment['escrow_status'] ?? 'held');
            $escrowLabel = $escrow === 'released' ? 'Released' : ($escrow === 'refunded' ? 'Refunded' : 'Held');
            $escrowBadge = $escrow === 'released' ? 'success' : ($escrow === 'refunded' ? 'failed' : 'pending');
          ?>
          <tr>
            <td style="font-weight:700">#<?= (int)($payment['booking_id'] ?? 0) ?></td>
            <td><?= $h($payment['customer_name'] ?? '—') ?></td>
            <td style="font-weight:600"><?= $money($payment['amount'] ?? 0) ?></td>
            <td style="color:var(--primary);font-weight:600"><?= ((float)($payment['platform_fee'] ?? 0) > 0) ? $money($payment['platform_fee']) : '—' ?></td>
            <td style="color:var(--green);font-weight:700"><?= $money($supplierAmount) ?></td>
            <td><?= $h($typeLabel) ?></td>
            <td><span class="payhist-badge <?= $badgeClass ?>"><?= $statusLabel ?></span></td>
            <td><span class="payhist-badge <?= $escrowBadge ?>"><?= $escrowLabel ?></span></td>
            <td style="color:var(--muted)"><?= $formatDate($payment['created_at'] ?? null) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>

    <?php if ($totalPages > 1): ?>
    <div class="payhist-pagination">
      <div class="payhist-page-info">Page <?= $currentPage ?> of <?= $totalPages ?></div>
      <div class="payhist-page-btns">
        <?php if ($currentPage > 1): ?>
          <a href="<?= $h($filterUrl(['page' => $currentPage - 1])) ?>" class="payhist-btn">← Prev</a>
        <?php endif; ?>
        <?php if ($currentPage < $totalPages): ?>
          <a href="<?= $h($filterUrl(['page' => $currentPage + 1])) ?>" class="payhist-btn">Next →</a>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php require_once APPROOT . '/views/dashboardLayout/head.php'; ?>
</head>
<body class="grid h-screen gap-0 bg-app-page" style="grid-template-columns: 280px 1fr;">
    <?php require APPROOT . '/views/dashboardLayout/suppliersidebar.php'; ?>
</body>
</html>
