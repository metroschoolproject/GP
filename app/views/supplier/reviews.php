<?php
$supplier = $supplier ?? [];
$stats    = $stats ?? ['total' => 0, 'avg_rating' => 0, 'distribution' => [5=>0,4=>0,3=>0,2=>0,1=>0]];
$reviews  = $reviews ?? [];

$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$total = (int)$stats['total'];
$avg   = (float)$stats['avg_rating'];
$dist  = $stats['distribution'];

$dashboardTitle = 'Supplier';
$dashboardCrumb = 'Reviews';
$dashboardBreadcrumbs = [
    ['label' => 'Dashboard', 'url' => URLROOT . '/supplier/dashboard'],
    ['label' => 'Reviews',   'url' => null],
];
$dashboardContentClass = 'bg-app-content px-6 py-6 overflow-y-auto';

$dashboardContent = function () use ($supplier, $stats, $reviews, $total, $avg, $dist, $h) {
    $barColors = [5=>'var(--app-primary)',4=>'#d6a72d',3=>'#9ca3af',2=>'#f87171',1=>'#ef4444'];
?>
<div style="max-width:960px;">

  <!-- Header card -->
  <header style="background:#FFFFFF;border:1px solid #ead8c7;border-radius:1.2rem;padding:20px;margin-bottom:20px;box-shadow:0 1px 3px rgba(0,0,0,.04);">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:center;">
      <div style="background:#F5F0F2;border-radius:1rem;padding:20px;">
        <p style="font-size:12px;font-weight:700;color:#6d4c5b;">Customer Rating Summary</p>
        <p style="font-size:11px;color:#A8A29E;margin-top:4px;">Based on <?= $total ?> completed booking<?= $total !== 1 ? 's' : '' ?>.</p>
        <div style="display:flex;align-items:flex-end;gap:12px;margin-top:16px;">
          <span style="font-size:52px;font-weight:900;color:#6d4c5b;line-height:1;"><?= number_format($avg, 1) ?></span>
          <div style="padding-bottom:4px;">
            <div style="color:#d6a72d;font-size:20px;letter-spacing:-1px;"><?= str_repeat('★', (int)round($avg)) . str_repeat('☆', 5 - (int)round($avg)) ?></div>
            <p style="font-size:10px;color:#A8A29E;margin-top:2px;">out of 5 stars</p>
          </div>
        </div>
      </div>
      <div>
        <p style="font-size:11px;font-weight:700;color:#6d4c5b;margin-bottom:12px;">Rating Breakdown</p>
        <?php foreach ([5,4,3,2,1] as $star): ?>
          <?php $cnt = $dist[$star] ?? 0; $pct = $total > 0 ? round(($cnt / $total) * 100) : 0; ?>
          <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;font-size:11px;">
            <span style="width:24px;font-weight:700;"><?= $star ?>★</span>
            <div style="flex:1;height:6px;border-radius:999px;background:#ead8c7;">
              <div style="height:100%;width:<?= $pct ?>%;border-radius:999px;background:<?= $barColors[$star] ?>;"></div>
            </div>
            <span style="width:32px;text-align:right;color:#A8A29E;"><?= $pct ?>%</span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </header>

  <!-- Stats cards -->
  <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:20px;">
    <article style="background:#FFFFFF;border:1px solid #ead8c7;border-radius:1.2rem;padding:16px;box-shadow:0 1px 2px rgba(0,0,0,0.04);">
      <p style="font-size:11px;color:#A8A29E;font-weight:500;">Total Reviews</p>
      <h2 style="font-size:28px;font-weight:800;margin-top:6px;"><?= $total ?></h2>
    </article>
    <article style="background:#FFFFFF;border:1px solid #ead8c7;border-radius:1.2rem;padding:16px;box-shadow:0 1px 2px rgba(0,0,0,0.04);">
      <p style="font-size:11px;color:#A8A29E;font-weight:500;">Average Rating</p>
      <h2 style="font-size:28px;font-weight:800;margin-top:6px;color:#6d4c5b;"><?= number_format($avg, 1) ?></h2>
    </article>
    <article style="background:#FFFFFF;border:1px solid #ead8c7;border-radius:1.2rem;padding:16px;box-shadow:0 1px 2px rgba(0,0,0,0.04);">
      <p style="font-size:11px;color:#A8A29E;font-weight:500;">5-Star Reviews</p>
      <h2 style="font-size:28px;font-weight:800;margin-top:6px;color:#d6a72d;"><?= (int)($dist[5] ?? 0) ?></h2>
    </article>
  </div>

  <!-- Review feed -->
  <div style="background:#FFFFFF;border:1px solid #ead8c7;border-radius:1.2rem;padding:20px;box-shadow:0 1px 2px rgba(0,0,0,0.04);">
    <p style="font-size:11px;font-weight:700;color:#6d4c5b;margin-bottom:16px;text-transform:uppercase;letter-spacing:.08em;">Recent Reviews</p>

    <?php if (empty($reviews)): ?>
      <p style="font-size:13px;color:#A8A29E;text-align:center;padding:32px 0;">No reviews yet. Completed bookings from your customers will appear here.</p>
    <?php else: ?>
      <div style="display:flex;flex-direction:column;gap:14px;">
        <?php foreach ($reviews as $r): ?>
          <?php
            $rName    = $h((string)($r['customer_name'] ?? 'Customer'));
            $rInitial = mb_strtoupper(mb_substr($r['customer_name'] ?? 'C', 0, 1));
            $rRating  = (int)($r['rating'] ?? 0);
            $rService = $h((string)($r['service_name'] ?? ''));
          ?>
          <article style="border:1px solid #ead8c7;border-radius:1rem;padding:14px;">
            <div style="display:flex;align-items:flex-start;gap:12px;">
              <div style="width:36px;height:36px;border-radius:50%;background:#6d4c5b;color:#FFFFFF;display:grid;place-items:center;font-weight:700;font-size:13px;flex-shrink:0;"><?= $h($rInitial) ?></div>
              <div style="flex:1;">
                <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:6px;">
                  <strong style="font-size:13px;"><?= $rName ?></strong>
                  <span style="color:#d6a72d;font-size:15px;letter-spacing:-1px;"><?= str_repeat('★', $rRating) . str_repeat('☆', 5 - $rRating) ?></span>
                </div>
                <?php if ($rService): ?>
                  <p style="font-size:11px;color:#6d4c5b;font-weight:600;margin-top:2px;"><?= $rService ?></p>
                <?php endif; ?>
                <p style="font-size:13px;color:#78716C;margin-top:6px;line-height:1.6;"><?= $h((string)($r['comment'] ?? '')) ?></p>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>
<?php
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php $pageTitle = 'Reviews — Golden Promise'; ?>
    <?php require_once APPROOT . '/views/dashboardLayout/head.php'; ?>
</head>
<body class="grid h-screen gap-0 bg-app-page" style="grid-template-columns: 280px 1fr;">
    <?php require APPROOT . '/views/dashboardLayout/suppliersidebar.php'; ?>
</body>
</html>
