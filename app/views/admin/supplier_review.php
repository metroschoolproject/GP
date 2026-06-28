<?php
$supplierName = htmlspecialchars($supplier['shop_name'] ?? 'Supplier', ENT_QUOTES, 'UTF-8');
$status = strtolower($supplier['status'] ?? 'pending');
$warnLevel = (int)($supplier['warning_level'] ?? 0);
$adminNote = trim((string)($supplier['admin_note'] ?? ''));
$dashboardTitle = 'Suppliers';
$dashboardCrumb = 'Review';
$dashboardBreadcrumbs = [
    ['label' => 'Suppliers', 'url' => URLROOT . '/admin/suppliers'],
    ['label' => 'Review', 'url' => null],
];
$dashboardContentClass = 'admin-supplier-detail-outlet';
$money = fn($v) => 'MMK ' . number_format((float)$v, 0);
$perf = $performance ?? [];
$supplierFeePayment = $supplierFeePayment ?? null;
$dashboardContent = function () use ($supplier, $supplierName, $status, $warnLevel, $adminNote, $message, $money, $perf, $supplierFeePayment, $supplierServices, $recentBookings, $supplierReviews, $supplierWarnings) {
    $h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
    $isApprovedOrVerified = in_array($status, ['approved', 'verified'], true);
    $isPending = $status === 'pending';
    $isBanned = $status === 'banned';
    $revenueEarned = (float)($perf['revenue_earned'] ?? 0);
    $totalBookings = (int)($perf['total_bookings'] ?? 0);
    $completedBookings = (int)($perf['completed_bookings'] ?? 0);
    $cancelledBookings = (int)($perf['cancelled_bookings'] ?? 0);
    $avgRating = round((float)($perf['avg_rating'] ?? 0), 1);
    $reviewCount = (int)($perf['review_count'] ?? 0);
    $feePaymentId = (int)(($supplierFeePayment ?? [])['id'] ?? 0);
    $supplierServices = $supplierServices ?? [];
    $feePaymentId = $feePaymentId ?: 0;
    $recentBookings = $recentBookings ?? [];
    $supplierReviews = $supplierReviews ?? [];
    $supplierWarnings = $supplierWarnings ?? [];
    $isOnline = !empty($supplier['is_online']);
    $lastLogin = $supplier['last_login'] ?? null;
    $activeServices = array_filter($supplierServices, fn($s) => !empty($s['is_active']));
    $draftServices = array_filter($supplierServices, fn($s) => empty($s['is_active']));

    $relativeTime = static function (?string $date): string {
        if (!$date) return '—';
        $ts = strtotime($date);
        if (!$ts) return '—';
        $diff = time() - $ts;
        if ($diff < 60) return 'Just now';
        if ($diff < 3600) return floor($diff / 60) . 'm ago';
        if ($diff < 86400) return floor($diff / 3600) . 'h ago';
        if ($diff < 604800) return floor($diff / 86400) . 'd ago';
        return date('M j, Y', $ts);
    };

    $bookingStatusLabel = static function (string $s): array {
        return match ($s) {
            'pending' => ['Pending', '#92400E', '#FFFBEB'],
            'confirmed', 'accepted' => ['Confirmed', '#065F46', '#ECFDF5'],
            'in_progress' => ['In Progress', '#1d4ed8', '#EFF6FF'],
            'completed' => ['Completed', '#065F46', '#ECFDF5'],
            'cancelled', 'rejected' => ['Cancelled', '#991B1B', '#FEF2F2'],
            default => [ucfirst($s), '#78716C', '#F5F5F4'],
        };
    };
?>
<style>
  .admin-supplier-detail-outlet {
    min-height: 100%;
    background: #F4F1EE;
    padding: 32px 36px;
    font-size: 13.5px;
    overflow-y: auto;
  }
  .srd-page { --s:#FFFFFF; --border:#ead8c7; --b-light:#eddecc; --p:#6d4c5b; --ph:#7b5c69; --ps:#eddecc; --t:#111827; --m:#b79c8b; --b:#7b5c69; --sb:#ECFDF5; --st:#065F46; --wb:#FFFBEB; --wt:#92400E; --db:#FEF2F2; --dt:#991B1B; --nb:#F5F5F4; max-width:1600px; margin:0 auto; }
  .srd-page * { box-sizing: border-box; }

  .srd-header { display:flex; align-items:flex-start; justify-content:space-between; gap:20px; margin-bottom:24px; }
  .srd-eyebrow { font-size:10px; font-weight:700; letter-spacing:.12em; text-transform:uppercase; color:var(--m); margin:0 0 4px; }
  .srd-title { margin:0; color:var(--t); font-size:24px; font-weight:700; letter-spacing:-.02em; line-height:1.2; }
  .srd-subtitle { margin-top:6px; color:var(--b); font-size:12px; font-weight:600; }

  .srd-flash { margin-bottom:18px; border-radius:.75rem; padding:12px 14px; color:var(--st); background:var(--sb); border:1px solid #a7f3d0; font-size:13px; font-weight:600; }
  .srd-warn-banner { margin-bottom:18px; border-radius:.75rem; padding:12px 14px; background:var(--wb); color:var(--wt); font-size:13px; font-weight:600; }
  .srd-warn-banner.l2 { background:var(--db); color:var(--dt); }

  .srd-layout { display:grid; grid-template-columns:minmax(0,1fr) 380px; gap:20px; align-items:start; }
  .srd-panel { background:var(--s); border:1px solid var(--border); border-radius:.75rem; overflow:hidden; box-shadow:0 1px 2px rgba(28,25,23,.04); }
  .srd-panel.srd-panel-urgent { background:#FFFBEB; border-color:#e8d5b0; }
  .srd-panel.srd-panel-urgent .srd-panel-head { background:rgba(217,119,6,.06); }
  .srd-panel-head { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:14px 20px; border-bottom:1px solid var(--b-light); }
  .srd-panel-head-left { display:flex; align-items:center; gap:8px; }
  .srd-panel-icon { width:28px; height:28px; border-radius:.75rem; background:var(--ps); display:flex; align-items:center; justify-content:center; color:var(--p); }
  .srd-panel-title { font-size:13px; font-weight:700; color:var(--t); }
  .srd-section { padding:20px; }
  .srd-section + .srd-section { border-top:1px solid var(--b-light); }

  .srd-perf { display:grid; grid-template-columns:repeat(4,1fr); gap:10px; }
  .srd-perf-stat { text-align:center; padding:12px 8px; border:1px solid var(--b-light); border-radius:.75rem; background:var(--s); }
  .srd-perf-num { font-size:20px; font-weight:700; color:var(--p); }
  .srd-perf-label { font-size:10px; color:var(--m); text-transform:uppercase; letter-spacing:.05em; margin-top:4px; }

  .srd-detail-list { display:grid; gap:0; }
  .srd-detail-row { display:grid; grid-template-columns:160px 1fr; gap:12px; padding:13px 0; border-bottom:1px solid var(--b-light); align-items:start; }
  .srd-detail-row:first-child { padding-top:0; }
  .srd-detail-row:last-child { border-bottom:0; padding-bottom:0; }
  .srd-label { font-size:10px; font-weight:800; letter-spacing:.1em; text-transform:uppercase; color:var(--m); }
  .srd-value { color:var(--t); font-size:13px; font-weight:600; overflow-wrap:anywhere; }
  .srd-desc { border:1px solid var(--b-light); border-radius:.75rem; background:#FAFAF9; padding:14px 16px; }
  .srd-desc p { margin:8px 0 0; color:var(--b); line-height:1.75; }

  .srd-badge { display:inline-flex; align-items:center; border-radius:999px; padding:3px 10px; font-size:10px; font-weight:800; letter-spacing:.04em; text-transform:uppercase; }
  .srd-badge-pending { background:var(--wb); color:var(--wt); }
  .srd-badge-active { background:var(--sb); color:var(--st); }
  .srd-badge-rejected { background:var(--db); color:var(--dt); }
  .srd-badge-banned { background:var(--db); color:var(--dt); }

  .srd-rail { display:grid; gap:14px; position:sticky; top:20px; }
  .srd-file-link { display:flex; align-items:center; gap:10px; min-height:42px; border:1px solid var(--border); border-radius:.75rem; background:var(--s); padding:9px 12px; color:var(--p); font-size:13px; font-weight:700; text-decoration:none; transition:background .12s,border-color .12s; }
  .srd-file-link:hover { border-color:var(--p); background:#f5f1ec; }
  .srd-empty { border:1px dashed var(--border); border-radius:.75rem; background:#FAFAF9; padding:18px; color:var(--m); text-align:center; font-size:12px; }

  .srd-btn { display:inline-flex; align-items:center; justify-content:center; gap:6px; width:100%; min-height:38px; border:none; border-radius:.75rem; padding:0 14px; font-size:12px; font-weight:800; font-family:inherit; cursor:pointer; transition:background .12s,transform .12s; }
  .srd-btn-primary { background:var(--p); color:#FFFFFF; }
  .srd-btn-primary:hover { background:var(--ph); transform:translateY(-1px); }
  .srd-btn-danger { background:var(--dt); color:#FFFFFF; }
  .srd-btn-danger:hover { background:#7f1d1d; transform:translateY(-1px); }
  .srd-btn-warn { background:var(--wt); color:#FFFFFF; }
  .srd-btn-warn:hover { background:#78350f; transform:translateY(-1px); }
  .srd-btn-outline { border:1px solid var(--border); background:var(--s); color:var(--t); }
  .srd-btn-outline:hover { background:#f5f1ec; }

  .srd-action-stack { display:grid; gap:10px; padding:14px; }
  .srd-reviewed { border:1px solid var(--border); border-radius:.75rem; background:#FAFAF9; padding:14px; color:var(--b); line-height:1.6; font-size:12px; }
  .srd-field { margin-bottom:14px; }
  .srd-field label { display:block; font-size:11px; font-weight:700; color:var(--m); margin-bottom:4px; text-transform:uppercase; letter-spacing:.05em; }
  .srd-field input,.srd-field textarea,.srd-field select { width:100%; padding:10px 12px; border:1px solid var(--border); border-radius:.5rem; background:#FFFFFF; color:var(--t); font-size:13px; font-family:inherit; outline:none; resize:vertical; }
  .srd-field input:focus,.srd-field textarea:focus,.srd-field select:focus { border-color:var(--p); box-shadow:0 0 0 3px rgba(109,76,91,.08); }
  .srd-field textarea { min-height:70px; }

  .sp-online { display:inline-flex; align-items:center; gap:5px; font-size:12px; font-weight:600; color:#7b5c69; }
  .sp-dot { width:7px; height:7px; border-radius:50%; flex-shrink:0; }
  .sp-dot.on { background:#16a34a; }
  .sp-dot.off { background:#d4d4d4; }

  .srd-timeline { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:14px; }
  .srd-tl-item { border:1px solid var(--b-light); border-radius:.75rem; padding:14px 16px; }
  .srd-tl-label { font-size:10px; font-weight:800; letter-spacing:.1em; text-transform:uppercase; color:var(--m); margin-bottom:4px; }
  .srd-tl-value { font-size:15px; font-weight:700; color:var(--t); }
  .srd-tl-sub { font-size:11px; color:var(--m); margin-top:2px; }

  .srd-tabs { display:flex; gap:0; border-bottom:1px solid var(--b-light); padding:0 20px; background:#FAFAF9; }
  .srd-tab-btn { display:inline-flex; align-items:center; gap:6px; height:42px; padding:0 16px; border:0; border-bottom:2px solid transparent; background:transparent; color:var(--m); font-size:12px; font-weight:700; font-family:inherit; cursor:pointer; transition:all .12s; white-space:nowrap; }
  .srd-tab-btn:hover { color:var(--p); }
  .srd-tab-btn.is-active { color:var(--p); border-bottom-color:var(--p); }
  .srd-tab-count { font-size:10px; font-weight:800; min-width:18px; height:18px; display:inline-flex; align-items:center; justify-content:center; border-radius:999px; padding:0 5px; background:var(--b-light); }
  .srd-tab-btn.is-active .srd-tab-count { background:var(--ps); }
  .srd-tab-panel { display:none; }
  .srd-tab-panel.is-active { display:block; }

  .srd-service-row { display:flex; align-items:center; gap:10px; padding:10px 0; border-bottom:1px solid var(--b-light); }
  .srd-service-row:last-child { border-bottom:0; }
  .srd-service-dot { width:8px; height:8px; border-radius:50%; flex-shrink:0; }
  .srd-service-dot.active { background:#16a34a; }
  .srd-service-dot.draft { background:#d4d4d4; }
  .srd-service-name { font-weight:600; color:var(--t); font-size:13px; }
  .srd-service-cat { font-size:11px; color:var(--m); }

  .srd-booking-row { display:grid; grid-template-columns:1fr auto auto; gap:12px; align-items:center; padding:10px 0; border-bottom:1px solid var(--b-light); font-size:12px; }
  .srd-booking-row:last-child { border-bottom:0; }

  .srd-review-row { padding:12px 0; border-bottom:1px solid var(--b-light); }
  .srd-review-row:last-child { border-bottom:0; }
  .srd-review-head { display:flex; align-items:center; gap:8px; margin-bottom:6px; }
  .srd-review-stars { color:#d4a017; font-size:12px; letter-spacing:1px; }
  .srd-review-meta { font-size:11px; color:var(--m); }
  .srd-review-comment { font-size:12px; color:var(--b); line-height:1.6; }

  .srd-warn-row { display:flex; gap:12px; padding:12px 0; border-bottom:1px solid var(--b-light); }
  .srd-warn-row:last-child { border-bottom:0; }
  .srd-warn-severity { width:8px; height:8px; border-radius:50%; flex-shrink:0; margin-top:4px; }
  .srd-warn-severity.low { background:#d97706; }
  .srd-warn-severity.medium { background:#ea580c; }
  .srd-warn-severity.high { background:#dc2626; }

  .srd-notes-area { width:100%; min-height:100px; padding:12px; border:1px solid var(--border); border-radius:.5rem; background:#FAFAF9; font-size:13px; font-family:inherit; color:var(--t); resize:vertical; outline:none; }
  .srd-notes-area:focus { border-color:var(--p); box-shadow:0 0 0 3px rgba(109,76,91,.08); }

  .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.4); z-index:100; align-items:center; justify-content:center; padding:20px; }
  .modal-overlay.open { display:flex; }
  .modal-box { background:#FFFFFF; border-radius:1rem; padding:24px; max-width:440px; width:100%; box-shadow:0 20px 60px rgba(0,0,0,.2); }
  .modal-box h3 { margin:0 0 12px; font-size:16px; font-weight:700; color:var(--t); }
  .modal-box p { color:var(--b); font-size:13px; }

  @media(max-width:1100px) { .srd-layout{grid-template-columns:1fr} .srd-rail{position:static;grid-template-columns:1fr 1fr} }
  @media(max-width:760px) { .admin-supplier-detail-outlet{padding:20px 16px} .srd-rail{grid-template-columns:1fr} .srd-perf{grid-template-columns:1fr 1fr} .srd-detail-row{grid-template-columns:1fr} }
</style>

<div class="srd-page">
  <div class="srd-header">
    <div>
      <p class="srd-eyebrow">Supplier <?= $isApprovedOrVerified ? 'Management' : 'Application' ?></p>
      <h1 class="srd-title"><?= $supplierName ?></h1>
      <p class="srd-subtitle">ID #<?= (int)$supplier['supplier_id'] ?></p>
    </div>
    <div style="display:flex;align-items:center;gap:10px">
      <span class="sp-online" style="font-size:12px"><span class="sp-dot <?= $isOnline ? 'on' : 'off' ?>"></span> <?= $isOnline ? 'Online' : 'Offline' ?></span>
      <span class="srd-badge srd-badge-<?= $status ?>"><?= htmlspecialchars(strtoupper($status), ENT_QUOTES, 'UTF-8') ?></span>
    </div>
  </div>

  <!-- Account Timeline -->
  <div class="srd-panel" style="margin-bottom:20px">
    <div class="srd-section" style="padding:16px 20px">
      <div class="srd-timeline">
        <div class="srd-tl-item">
          <div class="srd-tl-label">Registered</div>
          <div class="srd-tl-value"><?= $supplier['created_at'] ? date('M j, Y', strtotime($supplier['created_at'])) : '—' ?></div>
          <div class="srd-tl-sub"><?= $relativeTime($supplier['created_at'] ?? null) ?></div>
        </div>
        <div class="srd-tl-item">
          <div class="srd-tl-label">Last Login</div>
          <div class="srd-tl-value"><?= $lastLogin ? date('M j, Y', strtotime($lastLogin)) : '—' ?></div>
          <div class="srd-tl-sub"><?= $relativeTime($lastLogin) ?></div>
        </div>
        <div class="srd-tl-item">
          <div class="srd-tl-label">Agreement</div>
          <div class="srd-tl-value"><?= !empty($supplier['agreement_accepted']) ? 'Accepted' : 'Not accepted' ?></div>
          <?php if (!empty($supplier['agreement_accepted_at'])): ?>
            <div class="srd-tl-sub"><?= $relativeTime($supplier['agreement_accepted_at']) ?></div>
          <?php endif; ?>
        </div>
        <div class="srd-tl-item">
          <div class="srd-tl-label">Payment</div>
          <div class="srd-tl-value" style="text-transform:capitalize"><?= $h($supplier['payment_status'] ?? '—') ?></div>
        </div>
        <div class="srd-tl-item">
          <div class="srd-tl-label">Available</div>
          <div class="srd-tl-value"><?= !empty($supplier['is_available']) ? 'Yes' : 'No' ?></div>
          <div class="srd-tl-sub"><?= !empty($supplier['is_available']) ? 'Accepting bookings' : 'Not accepting' ?></div>
        </div>
        <?php if ($isApprovedOrVerified && !empty($supplier['approved_by'])): ?>
        <div class="srd-tl-item">
          <div class="srd-tl-label">Approved By</div>
          <div class="srd-tl-value">Admin #<?= (int)$supplier['approved_by'] ?></div>
        </div>
        <?php endif; ?>
        <?php if ($isApprovedOrVerified && !empty($supplier['verified_by'])): ?>
        <div class="srd-tl-item">
          <div class="srd-tl-label">Verified By</div>
          <div class="srd-tl-value">Admin #<?= (int)$supplier['verified_by'] ?></div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <?php if (!empty($message)): ?>
    <div class="srd-flash"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <?php if ($warnLevel > 0): ?>
    <div class="srd-warn-banner <?= $warnLevel >= 2 ? 'l2' : '' ?>">
      ⚠️ Warning Level <?= $warnLevel ?> — This supplier has <?= $warnLevel >= 2 ? 'received a final warning' : 'been issued a warning' ?>.
    </div>
  <?php endif; ?>

  <?php if ($isApprovedOrVerified): ?>
  <div class="srd-panel" style="margin-bottom:20px">
    <div class="srd-panel-head">
      <div class="srd-panel-head-left">
        <span class="srd-panel-icon"><i data-lucide="bar-chart-3" class="h-4 w-4"></i></span>
        <span class="srd-panel-title">Performance</span>
      </div>
    </div>
    <div class="srd-section">
      <div class="srd-perf">
        <div class="srd-perf-stat"><div class="srd-perf-num"><?= (int)$totalBookings ?></div><div class="srd-perf-label">Total Bookings</div></div>
        <div class="srd-perf-stat"><div class="srd-perf-num"><?= (int)$completedBookings ?></div><div class="srd-perf-label">Completed</div></div>
        <div class="srd-perf-stat"><div class="srd-perf-num"><?= $money($revenueEarned) ?></div><div class="srd-perf-label">Revenue</div></div>
        <div class="srd-perf-stat"><div class="srd-perf-num"><?= $avgRating > 0 ? number_format($avgRating, 1) . ' ★' : '—' ?></div><div class="srd-perf-label">Rating (<?= $reviewCount ?> reviews)</div></div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <?php if ($supplierFeePayment): ?>
  <?php
    $feeStatus = strtolower($supplierFeePayment['status'] ?? 'pending');
    $feeSlipPath = trim((string)($supplierFeePayment['payment_slip_path'] ?? ''));
    $hasFeeSlip = $feeSlipPath !== '' && preg_match('/\.(jpe?g|png|webp|pdf)$/i', $feeSlipPath) === 1;
    $feePaymentId = (int)($supplierFeePayment['id'] ?? 0);
    $isFeePending = $feeStatus === 'pending';
    $isFeeSuccess = in_array($feeStatus, ['success', 'approved', 'verified', 'paid'], true);
    $feeStatusLabel = $isFeePending ? 'Awaiting Review' : ($isFeeSuccess ? 'Approved' : 'Rejected');
    $feeStatusIcon = $isFeePending ? 'clock' : ($isFeeSuccess ? 'check-circle' : 'x-circle');
  ?>
  <div class="srd-panel <?= $isFeePending ? 'srd-panel-urgent' : '' ?>" style="margin-bottom:20px">
    <div class="srd-panel-head">
      <div class="srd-panel-head-left">
        <span class="srd-panel-icon"><i data-lucide="wallet" class="h-4 w-4"></i></span>
        <span class="srd-panel-title">Supplier Fee Payment</span>
      </div>
      <span class="srd-badge <?= $isFeePending ? 'srd-badge-pending' : ($isFeeSuccess ? 'srd-badge-active' : 'srd-badge-rejected') ?>"><?= $feeStatusLabel ?></span>
    </div>
    <div class="srd-section">
      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:16px">
        <div>
          <div class="srd-label">Amount</div>
          <div style="font-size:22px;font-weight:700;color:var(--p);margin-top:4px"><?= $money($supplierFeePayment['paid_amount'] ?? $supplierFeePayment['amount'] ?? 0) ?></div>
        </div>
        <div>
          <div class="srd-label">Bank / Method</div>
          <div class="srd-value" style="margin-top:4px"><?= htmlspecialchars($supplierFeePayment['bank_name'] ?? $supplierFeePayment['method'] ?? '-', ENT_QUOTES, 'UTF-8') ?></div>
        </div>
        <div>
          <div class="srd-label">Sender Name</div>
          <div class="srd-value" style="margin-top:4px"><?= htmlspecialchars($supplierFeePayment['account_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?></div>
        </div>
        <div>
          <div class="srd-label">Phone</div>
          <div class="srd-value" style="margin-top:4px"><?= htmlspecialchars($supplierFeePayment['mobile_number'] ?? '-', ENT_QUOTES, 'UTF-8') ?></div>
        </div>
        <div>
          <div class="srd-label">Transaction Ref</div>
          <div style="margin-top:4px;font-size:12px;font-weight:600;color:var(--t);font-family:monospace;background:#FAFAF9;padding:4px 8px;border-radius:6px;border:1px solid var(--b-light);display:inline-block"><?= htmlspecialchars($supplierFeePayment['transaction_ref'] ?? '-', ENT_QUOTES, 'UTF-8') ?></div>
        </div>
        <div>
          <div class="srd-label">Submitted</div>
          <div class="srd-value" style="margin-top:4px"><?= !empty($supplierFeePayment['created_at']) ? date('M j, Y H:i', strtotime($supplierFeePayment['created_at'])) : '-' ?></div>
        </div>
      </div>
    </div>

    <?php if ($hasFeeSlip): ?>
    <div class="srd-section">
      <div class="srd-label" style="margin-bottom:10px">Payment Slip</div>
      <a href="<?= URLROOT ?>/<?= htmlspecialchars($feeSlipPath, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" style="display:inline-block;max-width:360px;border-radius:10px;overflow:hidden;border:1px solid var(--border);transition:border-color .2s">
        <img src="<?= URLROOT ?>/<?= htmlspecialchars($feeSlipPath, ENT_QUOTES, 'UTF-8') ?>" alt="Payment slip" style="width:100%;display:block;max-height:260px;object-fit:contain;background:#FAFAF9">
      </a>
      <div style="margin-top:6px">
        <a href="<?= URLROOT ?>/<?= htmlspecialchars($feeSlipPath, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" style="font-size:11px;color:var(--p);font-weight:700;text-decoration:none">Open full size <i data-lucide="external-link" style="width:11px;height:11px;display:inline;vertical-align:middle"></i></a>
      </div>
    </div>
    <?php endif; ?>

    <?php if ($isFeePending): ?>
    <div class="srd-section" style="display:flex;gap:10px;justify-content:flex-end;border-top:1px solid var(--b-light)">
      <button type="button" onclick="openModal('rejectPayment')" class="srd-btn srd-btn-outline" style="width:auto;padding:0 16px"><i data-lucide="x" style="width:14px;height:14px"></i> Reject</button>
      <button type="button" onclick="openModal('approvePayment')" class="srd-btn srd-btn-primary" style="width:auto;padding:0 16px"><i data-lucide="check" style="width:14px;height:14px"></i> Approve</button>
    </div>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <div class="srd-layout">
    <div style="display:grid;gap:16px">
      <!-- Business Profile -->
      <div class="srd-panel">
        <div class="srd-panel-head">
          <div class="srd-panel-head-left">
            <span class="srd-panel-icon"><i data-lucide="store" class="h-4 w-4"></i></span>
            <span class="srd-panel-title">Business Profile</span>
          </div>
        </div>
        <div class="srd-section">
          <div class="srd-detail-list">
            <?php foreach ([
                'Owner' => $supplier['owner_name'] ?? '-',
                'Email' => $supplier['owner_email'] ?? '-',
                'Phone' => $supplier['phone'] ?? '-',
                'Address' => $supplier['address'] ?? '-',
                'Categories' => $supplier['category_names'] ?? '-',
            ] as $label => $value): ?>
              <div class="srd-detail-row">
                <span class="srd-label"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span>
                <span class="srd-value"><?= htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8') ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="srd-section">
          <div class="srd-desc">
            <p class="srd-label">Business description</p>
            <p><?= htmlspecialchars($supplier['description'] ?? 'No description provided.', ENT_QUOTES, 'UTF-8') ?></p>
          </div>
        </div>
      </div>

      <!-- Activity Panel (tabbed: Services / Bookings / Reviews) -->
      <div class="srd-panel">
        <div class="srd-tabs">
          <button type="button" class="srd-tab-btn is-active" data-srd-tab="services" onclick="switchSrdTab(this,'services')">
            <i data-lucide="briefcase" style="width:14px;height:14px"></i> Services
            <span class="srd-tab-count"><?= count($supplierServices) ?></span>
          </button>
          <button type="button" class="srd-tab-btn" data-srd-tab="bookings" onclick="switchSrdTab(this,'bookings')">
            <i data-lucide="calendar-check" style="width:14px;height:14px"></i> Bookings
            <span class="srd-tab-count"><?= count($recentBookings) ?></span>
          </button>
          <button type="button" class="srd-tab-btn" data-srd-tab="reviews" onclick="switchSrdTab(this,'reviews')">
            <i data-lucide="star" style="width:14px;height:14px"></i> Reviews
            <span class="srd-tab-count"><?= $reviewCount ?></span>
          </button>
        </div>

        <!-- Services Tab -->
        <div class="srd-tab-panel is-active" data-srd-panel="services">
          <div class="srd-section">
            <?php if (empty($supplierServices)): ?>
              <p class="srd-empty">No services yet.</p>
            <?php else: ?>
              <?php foreach (array_slice($supplierServices, 0, 8) as $svc): ?>
                <div class="srd-service-row">
                  <span class="srd-service-dot <?= !empty($svc['is_active']) ? 'active' : 'draft' ?>"></span>
                  <div style="min-width:0;flex:1">
                    <div class="srd-service-name"><?= htmlspecialchars($svc['name'] ?? 'Untitled', ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="srd-service-cat"><?= htmlspecialchars($svc['category_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                  </div>
                  <span style="font-size:11px;font-weight:600;color:var(--m)"><?= !empty($svc['is_active']) ? 'Active' : 'Draft' ?></span>
                </div>
              <?php endforeach; ?>
              <?php if (count($supplierServices) > 8): ?>
                <p style="font-size:11px;color:var(--m);margin-top:8px">+<?= count($supplierServices) - 8 ?> more</p>
              <?php endif; ?>
            <?php endif; ?>
          </div>
        </div>

        <!-- Bookings Tab -->
        <div class="srd-tab-panel" data-srd-panel="bookings">
          <div class="srd-section">
            <?php if (empty($recentBookings)): ?>
              <p class="srd-empty">No bookings yet.</p>
            <?php else: ?>
              <?php foreach ($recentBookings as $bk): ?>
                <?php [$bkLabel, $bkColor, $bkBg] = $bookingStatusLabel($bk['status'] ?? ''); ?>
                <div class="srd-booking-row">
                  <div>
                    <div style="font-weight:600;color:var(--t)"><?= htmlspecialchars($bk['service_name'] ?? 'Booking #' . ($bk['booking_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                    <div style="font-size:11px;color:var(--m);margin-top:2px"><?= $relativeTime($bk['created_at'] ?? null) ?></div>
                  </div>
                  <span class="srd-badge" style="background:<?= $bkBg ?>;color:<?= $bkColor ?>;font-size:9px"><?= $bkLabel ?></span>
                  <a href="<?= URLROOT ?>/admin/booking/<?= (int)($bk['booking_id'] ?? 0) ?>" style="font-size:11px;font-weight:700;color:var(--p);text-decoration:none">View</a>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>

        <!-- Reviews Tab -->
        <div class="srd-tab-panel" data-srd-panel="reviews">
          <div class="srd-section">
            <?php if (empty($supplierReviews)): ?>
              <p class="srd-empty">No reviews yet.</p>
            <?php else: ?>
              <?php foreach ($supplierReviews as $rv): ?>
                <div class="srd-review-row">
                  <div class="srd-review-head">
                    <span class="srd-review-stars"><?= str_repeat('★', (int)($rv['rating'] ?? 0)) . str_repeat('☆', 5 - (int)($rv['rating'] ?? 0)) ?></span>
                    <span class="srd-review-meta"><?= htmlspecialchars($rv['customer_name'] ?? 'Customer', ENT_QUOTES, 'UTF-8') ?> · <?= $relativeTime($rv['created_at'] ?? null) ?></span>
                  </div>
                  <?php if (!empty($rv['comment'])): ?>
                    <div class="srd-review-comment"><?= htmlspecialchars($rv['comment'], ENT_QUOTES, 'UTF-8') ?></div>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <aside class="srd-rail">
      <!-- Verification -->
      <div class="srd-panel">
        <div class="srd-panel-head">
          <span class="srd-panel-title">Verification</span>
        </div>
        <div style="padding:14px;display:grid;gap:8px">
          <?php if (!empty($supplier['verify_url'])): ?>
            <a href="<?= htmlspecialchars($supplier['verify_url'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" class="srd-file-link"><i data-lucide="external-link" class="h-4 w-4"></i> Website / social link</a>
          <?php endif; ?>
          <?php if (!empty($supplier['business_license_url'])): ?>
            <a href="<?= htmlspecialchars($supplier['business_license_url'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" class="srd-file-link"><i data-lucide="file-badge" class="h-4 w-4"></i> Business license</a>
          <?php endif; ?>
          <?php if (empty($supplier['verify_url']) && empty($supplier['business_license_url'])): ?>
            <p class="srd-empty">No verification files found.</p>
          <?php endif; ?>
        </div>
      </div>

      <!-- Decision / Management -->
      <div class="srd-panel">
        <div class="srd-panel-head">
          <span class="srd-panel-title"><?= $isPending ? 'Decision' : 'Management' ?></span>
        </div>
        <div class="srd-action-stack">
          <?php if ($isPending): ?>
            <button class="srd-btn srd-btn-primary" type="button" onclick="openModal('approve')"><i data-lucide="check" class="h-4 w-4"></i> Approve supplier</button>
            <button class="srd-btn srd-btn-danger" type="button" onclick="openModal('reject')"><i data-lucide="x" class="h-4 w-4"></i> Reject</button>
          <?php elseif ($isBanned): ?>
            <p class="srd-reviewed" style="margin-bottom:8px">This supplier is <strong>banned</strong>.</p>
            <button class="srd-btn srd-btn-primary" type="button" onclick="openModal('unban')"><i data-lucide="refresh-cw" class="h-4 w-4"></i> Unban & restore</button>
          <?php elseif ($isApprovedOrVerified): ?>
            <button class="srd-btn srd-btn-danger" type="button" onclick="openModal('ban')"><i data-lucide="ban" class="h-4 w-4"></i> Ban supplier</button>
            <button class="srd-btn srd-btn-warn" type="button" onclick="openModal('warn1')"><i data-lucide="alert-triangle" class="h-4 w-4"></i> Issue warning</button>
            <?php if ($warnLevel >= 1): ?>
              <button class="srd-btn srd-btn-danger" type="button" onclick="openModal('warn2')"><i data-lucide="alert-octagon" class="h-4 w-4"></i> Final warning</button>
            <?php endif; ?>
          <?php else: ?>
            <p class="srd-reviewed">Already reviewed (<?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?>).</p>
          <?php endif; ?>
          <button class="srd-btn srd-btn-danger" type="button" onclick="openModal('permanent-delete')"><i data-lucide="trash-2" class="h-4 w-4"></i> Permanently delete</button>
        </div>
      </div>

      <!-- Warnings (compact) -->
      <div class="srd-panel">
        <div class="srd-panel-head">
          <span class="srd-panel-title">Warnings</span>
          <span style="font-size:11px;color:var(--m);font-weight:600"><?= count($supplierWarnings) ?></span>
        </div>
        <div class="srd-section" style="padding:12px 14px">
          <?php if (empty($supplierWarnings)): ?>
            <p style="font-size:11px;color:var(--m);text-align:center">No warnings</p>
          <?php else: ?>
            <?php foreach (array_slice($supplierWarnings, 0, 3) as $w): ?>
              <div style="display:flex;gap:8px;padding:6px 0;border-bottom:1px solid var(--b-light);font-size:11px">
                <span class="srd-warn-severity <?= htmlspecialchars($w['severity'] ?? 'medium', ENT_QUOTES, 'UTF-8') ?>" style="margin-top:3px"></span>
                <div style="min-width:0;flex:1">
                  <div style="font-weight:600;color:var(--t)"><?= htmlspecialchars(mb_strimwidth($w['reason'] ?? '', 0, 60, '…'), ENT_QUOTES, 'UTF-8') ?></div>
                  <div style="color:var(--m);margin-top:1px"><?= $relativeTime($w['created_at'] ?? null) ?></div>
                </div>
              </div>
            <?php endforeach; ?>
            <?php if (count($supplierWarnings) > 3): ?>
              <p style="font-size:10px;color:var(--m);margin-top:6px">+<?= count($supplierWarnings) - 3 ?> more</p>
            <?php endif; ?>
          <?php endif; ?>
        </div>
      </div>

      <!-- Admin Notes (compact) -->
      <div class="srd-panel">
        <div class="srd-panel-head">
          <span class="srd-panel-title">Notes</span>
        </div>
        <div style="padding:12px 14px">
          <form method="POST" action="<?= URLROOT ?>/admin/supplierNote/<?= (int)$supplier['supplier_id'] ?>">
            <?= csrf_field() ?>
            <textarea name="admin_note" class="srd-notes-area" style="min-height:70px;font-size:12px" placeholder="Internal notes..."><?= htmlspecialchars($adminNote, ENT_QUOTES, 'UTF-8') ?></textarea>
            <button type="submit" class="srd-btn srd-btn-outline" style="width:auto;margin-top:8px;padding:0 12px;font-size:11px;height:30px">Save</button>
          </form>
        </div>
      </div>
    </aside>
  </div>
</div>

<!-- Modals for approve / reject / unban -->
<div class="modal-overlay" id="modalApprove">
  <div class="modal-box">
    <h3 style="font-size:16px;font-weight:700;margin-bottom:12px">Approve Supplier</h3>
    <p style="font-size:13px;color:var(--m);margin-bottom:16px">Are you sure you want to approve <strong><?= $supplierName ?></strong>? They will be notified and their profile will go live.</p>
    <form method="post" action="<?= URLROOT ?>/admin/approveSupplier/<?= (int)$supplier['supplier_id'] ?>"><?= csrf_field() ?>
      <div style="display:flex;gap:8px;justify-content:flex-end">
        <button type="button" class="srd-btn srd-btn-outline" style="width:auto" onclick="closeModal('approve')">Cancel</button>
        <button type="submit" class="srd-btn srd-btn-primary" style="width:auto"><i data-lucide="check" class="h-4 w-4"></i> Approve Supplier</button>
      </div>
    </form>
  </div>
</div>

<div class="modal-overlay" id="modalReject">
  <div class="modal-box">
    <h3 style="font-size:16px;font-weight:700;margin-bottom:12px">Reject Application</h3>
    <form method="post" action="<?= URLROOT ?>/admin/rejectSupplier/<?= (int)$supplier['supplier_id'] ?>"><?= csrf_field() ?>
      <div class="srd-field"><label>Reason for rejection <span style="color:#ef4444">*</span></label><textarea name="reason" required placeholder="Explain why this application is being rejected..."></textarea></div>
      <div style="display:flex;gap:8px;justify-content:flex-end">
        <button type="button" class="srd-btn srd-btn-outline" style="width:auto" onclick="closeModal('reject')">Cancel</button>
        <button type="submit" class="srd-btn srd-btn-danger" style="width:auto"><i data-lucide="x" class="h-4 w-4"></i> Reject Application</button>
      </div>
    </form>
  </div>
</div>

<div class="modal-overlay" id="modalUnban">
  <div class="modal-box">
    <h3 style="font-size:16px;font-weight:700;margin-bottom:12px">Unban Supplier</h3>
    <p style="font-size:13px;color:var(--m);margin-bottom:16px">Are you sure you want to unban <strong><?= $supplierName ?></strong>? Their access will be restored.</p>
    <form method="post" action="<?= URLROOT ?>/admin/unbanSupplier/<?= (int)$supplier['supplier_id'] ?>"><?= csrf_field() ?>
      <div style="display:flex;gap:8px;justify-content:flex-end">
        <button type="button" class="srd-btn srd-btn-outline" style="width:auto" onclick="closeModal('unban')">Cancel</button>
        <button type="submit" class="srd-btn srd-btn-primary" style="width:auto"><i data-lucide="refresh-cw" class="h-4 w-4"></i> Unban & Restore</button>
      </div>
    </form>
  </div>
</div>

<div class="modal-overlay" id="modalApprovePayment">
  <div class="modal-box">
    <h3 style="font-size:16px;font-weight:700;margin-bottom:12px">Approve Payment</h3>
    <p style="font-size:13px;color:var(--m);margin-bottom:16px">Approve this supplier fee payment? The supplier's dashboard will be unlocked.</p>
    <form method="POST" action="<?= URLROOT ?>/admin/approvePayment/<?= $feePaymentId ?>"><?= csrf_field() ?>
      <div style="display:flex;gap:8px;justify-content:flex-end">
        <button type="button" class="srd-btn srd-btn-outline" style="width:auto" onclick="closeModal('approvePayment')">Cancel</button>
        <button type="submit" class="srd-btn srd-btn-primary" style="width:auto"><i data-lucide="check" class="h-4 w-4"></i> Approve</button>
      </div>
    </form>
  </div>
</div>

<div class="modal-overlay" id="modalRejectPayment">
  <div class="modal-box">
    <h3 style="font-size:16px;font-weight:700;margin-bottom:12px">Reject Payment</h3>
    <p style="font-size:13px;color:var(--m);margin-bottom:16px">Reject this supplier fee payment? The supplier will be notified.</p>
    <form method="POST" action="<?= URLROOT ?>/admin/rejectPayment/<?= $feePaymentId ?>"><?= csrf_field() ?>
      <div style="display:flex;gap:8px;justify-content:flex-end">
        <button type="button" class="srd-btn srd-btn-outline" style="width:auto" onclick="closeModal('rejectPayment')">Cancel</button>
        <button type="submit" class="srd-btn srd-btn-danger" style="width:auto"><i data-lucide="x" class="h-4 w-4"></i> Reject</button>
      </div>
    </form>
  </div>
</div>

<!-- Modals for ban / warn -->
<div class="modal-overlay" id="modalBan">
  <div class="modal-box">
    <h3 style="font-size:16px;font-weight:700;margin-bottom:12px">Ban Supplier</h3>
    <form method="post" action="<?= URLROOT ?>/admin/banSupplier/<?= (int)$supplier['supplier_id'] ?>"><?= csrf_field() ?>
      <div class="srd-field"><label>Reason for ban <span style="color:#ef4444">*</span></label><textarea name="reason" required placeholder="Explain why this supplier is being banned..."></textarea></div>
      <div style="display:flex;gap:8px;justify-content:flex-end">
        <button type="button" class="srd-btn srd-btn-outline" style="width:auto" onclick="closeModal('ban')">Cancel</button>
        <button type="submit" class="srd-btn srd-btn-danger" style="width:auto">Confirm Ban</button>
      </div>
    </form>
  </div>
</div>

<div class="modal-overlay" id="modalWarn1">
  <div class="modal-box">
    <h3 style="font-size:16px;font-weight:700;margin-bottom:12px">Issue Warning (Level 1)</h3>
    <form method="post" action="<?= URLROOT ?>/admin/warnSupplier/<?= (int)$supplier['supplier_id'] ?>"><?= csrf_field() ?>
      <input type="hidden" name="warning_level" value="1">
      <div class="srd-field"><label>Warning note <span style="color:#ef4444">*</span></label><textarea name="warn_note" required placeholder="Describe the issue..."></textarea></div>
      <div style="display:flex;gap:8px;justify-content:flex-end">
        <button type="button" class="srd-btn srd-btn-outline" style="width:auto" onclick="closeModal('warn1')">Cancel</button>
        <button type="submit" class="srd-btn srd-btn-warn" style="width:auto">Issue Warning</button>
      </div>
    </form>
  </div>
</div>

<div class="modal-overlay" id="modalWarn2">
  <div class="modal-box">
    <h3 style="font-size:16px;font-weight:700;margin-bottom:12px">Final Warning (Level 2)</h3>
    <form method="post" action="<?= URLROOT ?>/admin/warnSupplier/<?= (int)$supplier['supplier_id'] ?>"><?= csrf_field() ?>
      <input type="hidden" name="warning_level" value="2">
      <div class="srd-field"><label>Final warning note <span style="color:#ef4444">*</span></label><textarea name="warn_note" required placeholder="Describe the serious issue..."></textarea></div>
      <div style="display:flex;gap:8px;justify-content:flex-end">
        <button type="button" class="srd-btn srd-btn-outline" style="width:auto" onclick="closeModal('warn2')">Cancel</button>
        <button type="submit" class="srd-btn srd-btn-danger" style="width:auto">Issue Final Warning</button>
      </div>
    </form>
  </div>
</div>

<!-- Permanent delete modal -->
<div class="modal-overlay" id="modalPermanentDelete">
  <div class="modal-box">
    <h3 style="margin:0 0 12px;font-size:16px;font-weight:700;color:#3a2030">Permanently delete supplier</h3>
    <div class="srd-reviewed" style="margin-bottom:14px"><i data-lucide="alert-triangle"></i><span><strong>This cannot be undone.</strong> The account will be anonymized — name, email, shop name, and personal data will be erased. Booking and payment records are kept. The email can be used to register a new account.</span></div>
    <label style="display:block;font-size:11px;font-weight:700;color:#7b5c69;text-transform:uppercase;letter-spacing:.04em;margin-bottom:5px">Type <strong style="color:#b94b4b">PERMANENTLY DELETE</strong> to confirm</label>
    <input id="perm-delete-confirm" type="text" placeholder="Type PERMANENTLY DELETE" autocomplete="off" style="width:100%;box-sizing:border-box;border:1px solid #ead8c7;border-radius:8px;padding:10px 12px;font-size:13px;color:#6d4c5b;margin-bottom:12px">
    <div style="display:flex;justify-content:flex-end;gap:8px">
      <button type="button" class="srd-btn srd-btn-outline" style="width:auto" onclick="closeModal('permanentDelete')">Cancel</button>
      <form method="POST" action="<?= URLROOT ?>/admin/supplierPermanentDelete/<?= (int)$supplier['supplier_id'] ?>" style="display:inline"><?= csrf_field() ?>
        <button type="submit" class="srd-btn srd-btn-danger" id="permDeleteBtn" style="width:auto" disabled><i data-lucide="trash-2" class="h-4 w-4"></i> Permanently delete</button>
      </form>
    </div>
  </div>
</div>

<script>
function switchSrdTab(btn, name) {
  btn.closest('.srd-panel').querySelectorAll('.srd-tab-btn').forEach(function(b) { b.classList.remove('is-active'); });
  btn.classList.add('is-active');
  btn.closest('.srd-panel').querySelectorAll('.srd-tab-panel').forEach(function(p) { p.classList.toggle('is-active', p.dataset.srdPanel === name); });
}
function openModal(id) { document.getElementById('modal'+id.charAt(0).toUpperCase()+id.slice(1)).classList.add('open'); }
function closeModal(id) { document.getElementById('modal'+id.charAt(0).toUpperCase()+id.slice(1)).classList.remove('open'); }
document.querySelectorAll('.modal-overlay').forEach(function(m){ m.addEventListener('click',function(e){ if(e.target===m) m.classList.remove('open'); }); });

var permInput = document.getElementById('perm-delete-confirm');
var permBtn   = document.getElementById('permDeleteBtn');
if (permInput && permBtn) {
  permInput.addEventListener('input', function() {
    permBtn.disabled = this.value.trim().toUpperCase() !== 'PERMANENTLY DELETE';
  });
}
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
