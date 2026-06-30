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
  .repl-page{--surface:#fff;--border:#ead8c7;--primary:#6d4c5b;--primary-hover:#7b5c69;--muted:#b79c8b;--body:#7b5c69;--text:#111827;max-width:960px;margin:0 auto}
  .repl-page h1{font-size:22px;font-weight:700;margin:0 0 4px}
  .eyebrow{font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--muted);margin-bottom:4px}
  .empty{padding:48px;text-align:center;color:var(--muted);background:var(--surface);border:1px solid var(--border);border-radius:.75rem;margin-top:18px}

  /* ── Card ── */
  .repl-card{background:var(--surface);border:1px solid var(--border);border-radius:.75rem;margin-top:14px;overflow:hidden;transition:border-color .15s}
  .repl-card:hover{border-color:#d4c4b5}

  /* ── Header ── */
  .repl-card-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:14px 20px;border-bottom:1px solid #f0e6da;background:#fdfbf9}
  .repl-card-ref{font-size:14px;font-weight:800;color:var(--text)}
  .repl-card-customer{font-size:11.5px;color:var(--body);margin-top:1px}
  .repl-card-badge{display:inline-block;padding:4px 10px;border-radius:999px;font-size:10.5px;font-weight:700;white-space:nowrap}
  .repl-card-badge.is-pending{background:#FFFBEB;color:#92400E}
  .repl-card-badge.is-customer{background:#eff6ff;color:#1d4ed8}
  .repl-card-badge.is-supplier{background:#f5f3ff;color:#6d28d9}

  /* ── Service info ── */
  .repl-card-svc-row{display:flex;align-items:center;flex-wrap:wrap;gap:8px;padding:12px 20px;border-bottom:1px solid #f0e6da;background:#fff}
  .repl-card-svc-name{font-size:15px;font-weight:800;color:var(--text)}
  .repl-card-tag{display:inline-block;padding:2px 8px;border-radius:999px;font-size:10px;font-weight:700;background:#f5f0eb;color:#7b5c69}
  .repl-card-date{display:inline-flex;align-items:center;gap:4px;font-size:11.5px;color:var(--body);margin-left:auto}

  /* ── Two-box comparison ── */
  .repl-card-boxes{display:grid;grid-template-columns:1fr 1fr;gap:0}
  .repl-card-box{padding:16px 20px}
  .repl-card-box + .repl-card-box{border-left:1px solid #f0e6da}
  .repl-card-box-label{font-size:9px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;margin-bottom:8px}
  .repl-card-box-label.is-old{color:#991B1B}
  .repl-card-box-label.is-new{color:#065F46}
  .repl-card-box-shop{font-size:14px;font-weight:700;color:var(--text)}
  .repl-card-box-svc{font-size:12px;color:var(--body);margin-top:2px}
  .repl-card-box-price{font-size:12px;color:var(--body);margin-top:4px;font-weight:600}
  .repl-card-box-reason{font-size:11.5px;color:#92400e;font-style:italic;margin-top:6px}
  .repl-card-box-delta{display:inline-block;margin-top:4px;padding:2px 7px;border-radius:999px;font-size:10px;font-weight:700}
  .repl-card-box-delta.is-up{background:#FEF2F2;color:#991B1B}
  .repl-card-box-delta.is-down{background:#ECFDF5;color:#065F46}
  .repl-card-box-empty{padding:16px 20px;display:flex;align-items:center;justify-content:center;border-left:1px solid #f0e6da}
  .repl-card-box-empty-text{font-size:12px;color:var(--muted);font-style:italic;text-align:center}

  /* ── Footer ── */
  .repl-card-foot{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 20px;border-top:1px solid #f0e6da;background:#fdfbf9}
  .repl-card-state{font-size:11.5px;color:var(--body);line-height:1.4;flex:1}
  .btn-pick{display:inline-flex;align-items:center;justify-content:center;gap:8px;height:36px;padding:0 16px;border:1px solid var(--primary);border-radius:.65rem;background:#fff;color:var(--primary);font-family:inherit;font-size:11.5px;font-weight:800;text-decoration:none;box-shadow:0 1px 2px rgba(17,24,39,.04);transition:background .15s,box-shadow .15s,transform .15s;white-space:nowrap;flex-shrink:0}
  .btn-pick:hover{background:#faf6f7;box-shadow:0 5px 14px rgba(109,76,91,.13);transform:translateY(-1px)}
  .btn-view{display:inline-flex;align-items:center;justify-content:center;height:36px;padding:0 14px;border:1px solid var(--border);border-radius:.65rem;background:#fff;color:var(--primary);font-family:inherit;font-size:11.5px;font-weight:800;text-decoration:none;white-space:nowrap;flex-shrink:0}

  @media(max-width:700px){
    .admin-booking-outlet{padding:20px 16px}
    .repl-card-boxes{grid-template-columns:1fr}
    .repl-card-box + .repl-card-box{border-left:0;border-top:1px solid #f0e6da}
    .repl-card-box-empty{border-left:0;border-top:1px solid #f0e6da}
    .repl-card-head{flex-direction:column;align-items:flex-start}
    .repl-card-date{margin-left:0}
  }
</style>
<div class="repl-page">
  <div class="eyebrow">Replacement tracking</div>
  <h1>Supplier Replacements</h1>
  <p style="color:#7b5c69;margin:6px 0 0">Track every open replacement and see who needs to act next.</p>

  <?php if (empty($replacements)): ?>
    <div class="empty">No open replacements right now.</div>
  <?php else: ?>
    <?php foreach ($replacements as $r):
      $replacementStatus = (string)($r['status'] ?? 'pending_admin');
      $canPick = in_array($replacementStatus, ['pending_admin', 'declined_again', 'rejected_by_customer'], true);
      $proofSubmitted = $replacementStatus === 'pending_customer'
          && !empty($r['customer_approved_at'])
          && !empty($r['delta_payment_slip']);
      $isOptOut = $replacementStatus === 'assigned'
          && !empty($r['requires_customer_approval'])
          && !empty($r['customer_opt_out_deadline'])
          && strtotime((string)$r['customer_opt_out_deadline']) > time();
      $badgeLabel = $proofSubmitted ? 'Verify payment'
          : ($isOptOut ? 'Assigned (48h opt-out)'
          : ($statusLabels[$replacementStatus] ?? $replacementStatus));
      $badgeMod = $replacementStatus === 'pending_customer' ? 'is-customer'
          : ($replacementStatus === 'assigned' ? ($isOptOut ? 'is-customer' : 'is-supplier')
          : 'is-pending');
      $stateCopy = match (true) {
        $proofSubmitted => 'Payment proof submitted — admin verification required.',
        $replacementStatus === 'pending_customer' => 'Customer must approve and pay the price difference.',
        $isOptOut => 'Assigned; customer has 48 hours to opt out.',
        $replacementStatus === 'assigned' => 'Replacement assigned; waiting for the new supplier to accept.',
        $replacementStatus === 'declined_again' => 'The replacement supplier declined. Choose another.',
        $replacementStatus === 'rejected_by_customer' => 'Customer declined this proposal. Choose another.',
        default => 'Choose an eligible replacement from the same category.',
      };
      $serviceName  = $r['old_service_name'] ?? null;
      $categoryName = $r['category_name'] ?? null;
      $packageName  = $r['package_name'] ?? null;
      $hasNew       = !empty($r['new_shop_name']);
      $delta        = (float)($r['price_delta'] ?? 0);
    ?>
    <div class="repl-card">
      <!-- Header: booking ref + customer + badge -->
      <div class="repl-card-head">
        <div>
          <div class="repl-card-ref"><?= $h($r['booking_ref'] ?? ('#' . $r['booking_id'])) ?></div>
          <div class="repl-card-customer"><?= $h($r['customer_name'] ?? 'Customer') ?><?= $packageName ? ' · ' . $h($packageName) : '' ?></div>
        </div>
        <span class="repl-card-badge <?= $badgeMod ?>"><?= $h($badgeLabel) ?></span>
      </div>

      <!-- Service info row -->
      <div class="repl-card-svc-row">
        <span class="repl-card-svc-name"><?= $h($serviceName ?: ($categoryName ?: 'Service')) ?></span>
        <?php if ($categoryName): ?>
          <span class="repl-card-tag"><?= $h($categoryName) ?></span>
        <?php endif; ?>
        <span class="repl-card-date">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
          <?= $h($dateOnly($r['event_date'] ?? null, 'Not scheduled')) ?>
        </span>
      </div>

      <!-- Two-box comparison -->
      <div class="repl-card-boxes">
        <!-- Declined supplier box -->
        <div class="repl-card-box">
          <div class="repl-card-box-label is-old">Declined supplier</div>
          <div class="repl-card-box-shop"><?= $h($r['old_shop_name'] ?? 'Unknown') ?></div>
          <div class="repl-card-box-svc"><?= $h($serviceName ?: ($categoryName ?: 'Service')) ?></div>
          <div class="repl-card-box-price"><?= $money($r['old_price'] ?? 0) ?></div>
          <?php if (!empty($r['decline_reason'])): ?>
            <div class="repl-card-box-reason">Reason: <?= $h($r['decline_reason']) ?></div>
          <?php endif; ?>
        </div>

        <!-- Replacement supplier box (or empty state) -->
        <?php if ($hasNew): ?>
          <div class="repl-card-box">
            <div class="repl-card-box-label is-new">Replacement supplier</div>
            <div class="repl-card-box-shop"><?= $h($r['new_shop_name']) ?></div>
            <div class="repl-card-box-svc"><?= $h($r['new_service_name'] ?? $serviceName ?? 'Service') ?></div>
            <div class="repl-card-box-price"><?= $money($r['new_price'] ?? 0) ?></div>
            <?php if ($delta > 0): ?>
              <span class="repl-card-box-delta is-up">+<?= $money($delta) ?> more</span>
            <?php elseif ($delta < 0): ?>
              <span class="repl-card-box-delta is-down"><?= $money($delta) ?> saved</span>
            <?php endif; ?>
          </div>
        <?php else: ?>
          <div class="repl-card-box-empty">
            <div class="repl-card-box-empty-text">No replacement assigned yet.<br>Click below to pick one.</div>
          </div>
        <?php endif; ?>
      </div>

      <!-- Footer: state + action -->
      <div class="repl-card-foot">
        <div class="repl-card-state"><?= $h($stateCopy) ?></div>
        <?php if ($canPick || $proofSubmitted): ?>
          <a class="btn-pick" href="<?= URLROOT ?>/admin/replacementPicker/<?= (int)$r['id'] ?>">
            <?= $proofSubmitted ? 'Review payment' : 'Choose replacement' ?>
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>
          </a>
        <?php else: ?>
          <a class="btn-view" href="<?= URLROOT ?>/admin/bookingDetail/<?= (int)$r['booking_id'] ?>">View booking</a>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  <?php endif; ?>
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
