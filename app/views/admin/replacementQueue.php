<?php
$replacements = $replacements ?? [];

$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$money = fn($v) => number_format((float)$v, 0) . ' MMK';
$dateOnly = static function ($value, string $fallback = '-') {
    if (empty($value)) return $fallback;
    $ts = strtotime((string)$value);
    return $ts ? date('M j, Y', $ts) : $fallback;
};

$dashboardTitle = 'Supplier Replacements';
$dashboardCrumb = 'Replacements';
$dashboardBreadcrumbs = [
    ['label' => 'Bookings', 'url' => URLROOT . '/admin/bookings'],
    ['label' => 'Replacements', 'url' => URLROOT . '/admin/replacementQueue'],
];
$dashboardContentClass = 'admin-booking-outlet';

$statusLabels = [
    'pending_admin'        => 'Awaiting pick',
    'declined_again'       => 'Re-pick needed',
    'rejected_by_customer' => 'Customer declined',
    'pending_customer'     => 'Awaiting customer',
    'assigned'             => 'Awaiting supplier',
];

$dashboardContent = function () use ($replacements, $h, $money, $dateOnly, $statusLabels) {
?>
<style>
  .admin-booking-outlet{min-height:100%;background:#F4F1EE;padding:28px 32px;font-family:'DM Sans',system-ui,sans-serif;color:#6d4c5b;font-size:13px;overflow-y:auto}
  .repl-page{--surface:#fff;--border:#ead8c7;--primary:#6d4c5b;--muted:#b79c8b;max-width:1300px;margin:0 auto}
  .repl-page h1{font-size:22px;font-weight:700;margin:0 0 4px}
  .eyebrow{font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--muted);margin-bottom:4px}
  .repl-card{background:var(--surface);border:1px solid var(--border);border-radius:.75rem;overflow:hidden;margin-top:18px}
  .repl-table-scroll{overflow-x:auto}
  table.repl{width:100%;border-collapse:collapse}
  table.repl th{text-align:left;font-size:10px;text-transform:uppercase;letter-spacing:.08em;color:var(--muted);padding:12px 16px;border-bottom:1px solid var(--border);background:#FFFFFF}
  table.repl td{padding:13px 16px;border-bottom:1px solid #f0e6da;font-size:12.5px}
  table.repl tr:last-child td{border-bottom:none}
  .badge{display:inline-block;padding:3px 9px;border-radius:999px;font-size:10.5px;font-weight:700;background:#FFFBEB;color:#92400E}
  .badge.is-customer{background:#eff6ff;color:#1d4ed8}
  .badge.is-supplier{background:#f5f3ff;color:#6d28d9}
  .state-copy{display:block;max-width:210px;margin-top:5px;color:#7b5c69;font-size:10.5px;line-height:1.4;white-space:normal}
  .btn-view{display:inline-flex;align-items:center;justify-content:center;min-width:150px;height:38px;padding:0 14px;border:1px solid var(--border);border-radius:.7rem;background:#FFFFFF;color:var(--primary);font-size:11.5px;font-weight:800;text-decoration:none}
  .action-cell{width:1%;white-space:nowrap;text-align:right}
  .btn-pick{display:inline-flex;align-items:center;justify-content:center;gap:9px;min-width:150px;height:38px;padding:0 12px 0 16px;border:1px solid var(--primary);border-radius:.7rem;background:#fff;color:var(--primary);font-size:11.5px;font-weight:800;text-decoration:none;box-shadow:0 1px 2px rgba(17,24,39,.04);transition:background .15s,color .15s,box-shadow .15s,transform .15s}
  .btn-pick-icon{width:24px;height:24px;display:grid;place-items:center;border-radius:.45rem;background:var(--primary);color:#fff;transition:transform .15s}
  .btn-pick:hover{background:#faf6f7;box-shadow:0 5px 14px rgba(109,76,91,.13);transform:translateY(-1px)}
  .btn-pick:hover .btn-pick-icon{transform:translateX(2px)}
  .btn-pick:focus-visible{outline:3px solid rgba(109,76,91,.2);outline-offset:2px}
  .empty{padding:48px;text-align:center;color:var(--muted)}
  .package-name{color:#6d4c5b;font-weight:700}
  .package-meta{display:block;margin-top:3px;color:var(--muted);font-size:10.5px}
  .service-arrow{display:inline-flex;align-items:center;gap:5px;margin-top:4px;color:#7b5c69;font-size:10.5px}
  @media(max-width:900px){
    .admin-booking-outlet{padding:20px 16px}
    table.repl{min-width:1080px}
  }
</style>
<div class="repl-page">
  <div class="eyebrow">Replacement tracking</div>
  <h1>Supplier Replacements</h1>
  <p style="color:#7b5c69;margin:6px 0 0">Track every open replacement and see who needs to act next.</p>

  <div class="repl-card">
    <?php if (empty($replacements)): ?>
      <div class="empty">No open replacements right now. 🎉</div>
    <?php else: ?>
      <div class="repl-table-scroll">
      <table class="repl">
        <thead>
          <tr>
            <th>Booking</th><th>Customer</th><th>Package</th><th>Service to replace</th>
            <th>Declined supplier</th><th>Wedding date</th><th>Status</th><th></th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($replacements as $r): ?>
          <?php
            $replacementStatus = (string)($r['status'] ?? 'pending_admin');
            $canPick = in_array($replacementStatus, ['pending_admin', 'declined_again', 'rejected_by_customer'], true);
            $proofSubmitted = $replacementStatus === 'pending_customer'
                && !empty($r['customer_approved_at'])
                && !empty($r['delta_payment_slip']);
            $badgeClass = $replacementStatus === 'pending_customer'
                ? 'is-customer'
                : ($replacementStatus === 'assigned' ? 'is-supplier' : '');
            $stateCopy = match ($replacementStatus) {
              'pending_customer' => $proofSubmitted
                  ? 'Payment proof submitted—admin verification is required.'
                  : 'Customer must approve and pay the price difference.',
              'assigned' => 'Replacement assigned; waiting for the new supplier to accept.',
              'declined_again' => 'The replacement supplier declined. Choose another.',
              'rejected_by_customer' => 'The customer declined this proposal. Choose another.',
              default => 'Choose an eligible same-category replacement.',
            };
          ?>
          <tr>
            <td><strong><?= $h($r['booking_ref'] ?? ('#' . $r['booking_id'])) ?></strong></td>
            <td><?= $h($r['customer_name'] ?? '-') ?></td>
            <td>
              <span class="package-name"><?= $h($r['package_name'] ?? 'Custom booking') ?></span>
              <?php if (!empty($r['package_id'])): ?>
                <span class="package-meta">Package #<?= (int)$r['package_id'] ?></span>
              <?php endif; ?>
            </td>
            <td>
              <strong><?= $h($r['old_service_name'] ?? ($r['category_name'] ?? '-')) ?></strong>
              <span class="service-arrow">Replace this <?= $h($r['category_name'] ?? 'service') ?> in the package</span>
            </td>
            <td><?= $h($r['old_shop_name'] ?? '-') ?><br><span style="color:#b79c8b;font-size:11px"><?= $money($r['old_price'] ?? 0) ?></span></td>
            <td><?= $h($dateOnly($r['event_date'] ?? null)) ?></td>
            <td>
              <span class="badge <?= $badgeClass ?>">
                <?= $h($proofSubmitted ? 'Verify payment' : ($statusLabels[$replacementStatus] ?? $replacementStatus)) ?>
              </span>
              <span class="state-copy"><?= $h($stateCopy) ?></span>
            </td>
            <td class="action-cell">
              <?php if ($canPick || $proofSubmitted): ?>
                <a class="btn-pick" href="<?= URLROOT ?>/admin/replacementPicker/<?= (int)$r['id'] ?>">
                  <?= $proofSubmitted ? 'Review payment' : 'Choose replacement' ?>
                  <span class="btn-pick-icon" aria-hidden="true">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>
                  </span>
                </a>
              <?php else: ?>
                <a class="btn-view" href="<?= URLROOT ?>/admin/bookingDetail/<?= (int)$r['booking_id'] ?>">
                  View booking
                </a>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      </div>
    <?php endif; ?>
  </div>
</div>
<?php
};
?>
<!DOCTYPE html>
<html lang="en">
<head><?php require_once APPROOT . '/views/dashboardLayout/head.php'; ?></head>
<body class="grid h-screen grid-cols-[280px_1fr] gap-0 bg-app-page">
  <?php require APPROOT . '/views/dashboardLayout/sidebar.php'; ?>
</body>
</html>
