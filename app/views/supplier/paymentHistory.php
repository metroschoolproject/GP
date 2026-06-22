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
?>
<style>
.payhist-page { --ink:#241f1c; --muted:#7f746d; --soft:#f7f1ec; --panel:#fffdf9; --line:rgba(77,65,55,.12); --primary:#6d4c5b; --green:#166534; --amber:#92400e; --danger:#991b1b; color:var(--ink); }
.payhist-page h1 { font-size:clamp(28px,3vw,42px); font-weight:900; margin:6px 0 7px; }
.payhist-page .kicker { font-size:10px; font-weight:900; text-transform:uppercase; letter-spacing:.17em; color:#a36b5a; }
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
.payhist-badge.success { background:#d1fae5; color:#065f46; }
.payhist-badge.pending { background:#fef3c7; color:#92400e; }
.payhist-badge.failed { background:#fee2e2; color:#991b1b; }

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

  <div class="payhist-table-wrap">
    <?php if (empty($payments)): ?>
      <div class="payhist-empty">No payment history yet. Once customers start booking your services, payments will appear here.</div>
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
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($payments as $payment):
            $status = strtolower($payment['status'] ?? 'pending');
            $statusLabel = $status === 'success' ? 'Verified' : ($status === 'failed' ? 'Failed' : 'Pending');
            $badgeClass = $status === 'success' ? 'success' : ($status === 'failed' ? 'failed' : 'pending');
            $typeLabel = ($payment['type'] ?? 'deposit') === 'deposit' ? 'Deposit' : (($payment['type'] ?? '') === 'remaining' ? 'Balance' : 'Payment');
            $supplierAmount = (float)($payment['supplier_amount'] ?? (float)($payment['amount'] ?? 0) - (float)($payment['platform_fee'] ?? 0));
          ?>
          <tr>
            <td style="font-weight:700">#<?= (int)($payment['booking_id'] ?? 0) ?></td>
            <td><?= $h($payment['customer_name'] ?? '—') ?></td>
            <td style="font-weight:600"><?= $money($payment['amount'] ?? 0) ?></td>
            <td style="color:var(--primary);font-weight:600"><?= ((float)($payment['platform_fee'] ?? 0) > 0) ? $money($payment['platform_fee']) : '—' ?></td>
            <td style="color:var(--green);font-weight:700"><?= $money($supplierAmount) ?></td>
            <td><?= $h($typeLabel) ?></td>
            <td><span class="payhist-badge <?= $badgeClass ?>"><?= $statusLabel ?></span></td>
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
          <a href="?page=<?= $currentPage - 1 ?>" class="payhist-btn">← Prev</a>
        <?php endif; ?>
        <?php if ($currentPage < $totalPages): ?>
          <a href="?page=<?= $currentPage + 1 ?>" class="payhist-btn">Next →</a>
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
