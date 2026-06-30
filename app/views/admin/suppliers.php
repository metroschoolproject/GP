<?php
$dashboardTitle = 'Suppliers';
$dashboardCrumb = 'Directory';
$dashboardBreadcrumbs = [
    ['label' => 'Suppliers', 'url' => URLROOT . '/admin/suppliers'],
    ['label' => 'Directory', 'url' => null],
];
$suppliers = $suppliers ?? [];
$status = $status ?? 'all';
$search = $search ?? '';
$categoryId = (int)($categoryId ?? 0);
$paymentStatus = $paymentStatus ?? 'all';
$categories = $categories ?? [];
$topSuppliers = $topSuppliers ?? [];
$stats = $stats ?? [];
$dashboardContentClass = 'supplier-directory-shell';
$dashboardSearchAction = URLROOT . '/admin/suppliers';
$dashboardSearchPlaceholder = 'Search suppliers...';

$h = static fn($value) => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
$money = static fn($value) => number_format((float)$value, 0) . ' MMK';

$statusMeta = static function ($value) {
    return match (strtolower((string)$value)) {
        'pending' => ['Pending review', '#b7792f', 'clock-3'],
        'approved' => ['Approved', '#4f7c69', 'circle-check'],
        'verified' => ['Verified', '#39708a', 'badge-check'],
        'rejected' => ['Rejected', '#b94b4b', 'circle-x'],
        'banned' => ['Banned', '#8c3941', 'ban'],
        default => [ucfirst((string)$value ?: 'Unknown'), '#7b5c69', 'circle'],
    };
};

$paymentMeta = static function ($value) {
    $normalized = strtolower((string)$value);
    if (in_array($normalized, ['paid', 'verified', 'success'], true)) {
        return ['Paid', 'is-paid'];
    }
    if (in_array($normalized, ['pending', 'submitted'], true)) {
        return ['Pending payment', 'is-pending'];
    }
    return [ucfirst($normalized ?: 'Not recorded'), 'is-unpaid'];
};

$filterUrl = static function ($filter, $searchTerm = '', $selectedCategory = 0, $selectedPayment = 'all') {
    $params = ['status' => $filter];
    if ($searchTerm !== '') $params['search'] = $searchTerm;
    if ($selectedCategory > 0) $params['category'] = $selectedCategory;
    if ($selectedPayment !== 'all') $params['payment'] = $selectedPayment;
    return URLROOT . '/admin/suppliers?' . http_build_query($params);
};

$dashboardContent = function () use (
    $suppliers,
    $status,
    $search,
    $categoryId,
    $paymentStatus,
    $categories,
    $topSuppliers,
    $stats,
    $h,
    $money,
    $statusMeta,
    $paymentMeta,
    $filterUrl,
    $currentPage,
    $totalPages,
    $totalCount,
    $perPage,
    $supplierScores
) {
    $counts = [
        'all' => (int)($stats['total'] ?? 0),
        'pending' => (int)($stats['pending'] ?? 0),
        'approved' => (int)($stats['approved'] ?? 0),
        'verified' => (int)($stats['verified'] ?? 0),
        'rejected' => (int)($stats['rejected'] ?? 0),
        'banned' => (int)($stats['banned'] ?? 0),
    ];

    // Relative time helper
    $relativeTime = static function (?string $date): string {
        if (!$date) return '—';
        $ts = strtotime($date);
        if (!$ts) return '—';
        $diff = time() - $ts;
        if ($diff < 60) return 'Just now';
        if ($diff < 3600) return floor($diff / 60) . 'm ago';
        if ($diff < 86400) return floor($diff / 3600) . 'h ago';
        if ($diff < 604800) return floor($diff / 86400) . 'd ago';
        return date('M j', $ts);
    };

    $statusBadge = static function (string $status): array {
        return match (strtolower($status)) {
            'pending' => ['Pending', '#b7792f', '#fdf6e8'],
            'approved', 'verified' => ['Active', '#166534', '#ecfdf5'],
            'rejected' => ['Rejected', '#991b1b', '#fef2f2'],
            'banned' => ['Banned', '#8c3941', '#fef2f2'],
            default => [ucfirst($status), '#7b5c69', '#f5f1ec'],
        };
    };
?>
<style>
  .suppliers-page{min-height:100%;background:#F4F1EE;padding:28px 32px;font-size:13.5px;overflow-y:auto}
  .suppliers-page *{box-sizing:border-box}
  .sp-inner{--border:#ead8c7;--border-light:#eddecc;--primary:#6d4c5b;--primary-hover:#7b5c69;--primary-soft:#eddecc;--text:#111827;--muted:#b79c8b;--body:#7b5c69;--surface:#FFFFFF;--soft:#FFFFFF;--hover:#eddecc;max-width:1600px;margin:0 auto}
  .sp-head{display:flex;align-items:flex-end;justify-content:space-between;gap:16px;margin-bottom:18px}
  .sp-head-left{min-width:0}
  .sp-eyebrow{font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--muted);margin:0 0 4px}
  .sp-title{margin:0;font-size:22px;font-weight:700;color:var(--text);letter-spacing:-.3px}
  .sp-count{display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border:1px solid var(--border);border-radius:999px;background:var(--surface);font-size:11px;font-weight:700;color:var(--primary)}
  .sp-count strong{font-size:14px}
  .sp-view-toggle{display:inline-flex;border:1px solid var(--border);border-radius:.75rem;background:var(--soft);padding:2px}
  .sp-view-btn{display:flex;align-items:center;justify-content:center;width:30px;height:28px;border:0;border-radius:.5rem;background:transparent;color:var(--muted);cursor:pointer;transition:all .12s}
  .sp-view-btn:hover{color:var(--primary)}
  .sp-view-btn.is-active{background:var(--surface);color:var(--primary);box-shadow:0 1px 3px rgba(0,0,0,.06)}

  .sp-card{background:var(--surface);border:1px solid var(--border);border-radius:.75rem;overflow:hidden;box-shadow:0 1px 2px rgba(28,25,23,.04)}
  .toolbar{display:flex;align-items:center;gap:8px;padding:12px 16px;border-bottom:1px solid var(--border-light);flex-wrap:wrap}
  .filters{display:flex;gap:6px;flex-wrap:wrap}
  .filter{display:inline-flex;align-items:center;height:34px;padding:0 14px;border:1px solid var(--border);border-radius:.75rem;background:var(--soft);color:var(--body);font-size:12px;font-weight:700;font-family:inherit;cursor:pointer;transition:all .12s;white-space:nowrap;text-decoration:none}
  .filter:hover{border-color:var(--border);background:var(--hover);color:var(--primary)}
  .filter.active{border-color:var(--primary);background:var(--primary);color:#fff}
  .filter-count{font-size:10px;font-weight:800;margin-left:5px;opacity:.7}

  .sp-table-wrap{overflow-x:auto}
  .sp-table{width:100%;border-collapse:collapse}
  .sp-table thead tr{background:var(--soft)}
  .sp-table th{padding:9px 20px;text-align:left;font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--muted);white-space:nowrap}
  .sp-table th:last-child{text-align:right}
  .sp-table tbody tr{border-top:1px solid var(--border-light);transition:background .1s}
  .sp-table tbody tr:hover{background:var(--soft)}
  .sp-table td{padding:13px 20px;vertical-align:middle;font-size:13px}
  .sp-table td:last-child{text-align:right}

  .sp-name-cell{display:flex;align-items:center;gap:10px;min-width:0}
  .sp-avatar{width:36px;height:36px;border-radius:.75rem;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:800;flex-shrink:0}
  .sp-name-text{min-width:0}
  .sp-shop{font-weight:600;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:180px}
  .sp-owner{font-size:11px;color:var(--muted);margin-top:1px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:180px}

  .sp-status{display:inline-flex;align-items:center;border-radius:20px;padding:3px 9px;font-size:10px;font-weight:800;letter-spacing:.05em;text-transform:uppercase;white-space:nowrap}

  .sp-online{display:inline-flex;align-items:center;gap:5px;font-size:12px;font-weight:600;color:var(--body)}
  .sp-dot{width:7px;height:7px;border-radius:50%;flex-shrink:0}
  .sp-dot.on{background:#059669}
  .sp-dot.off{background:#d4d4d4}

  .sp-time{font-size:12px;color:var(--body);font-variant-numeric:tabular-nums;white-space:nowrap}

  .sp-action{display:inline-flex;align-items:center;gap:4px;height:30px;padding:0 12px;border:1px solid var(--border);border-radius:.75rem;background:var(--surface);color:var(--primary);font-size:11px;font-weight:700;text-decoration:none;white-space:nowrap;transition:all .12s}
  .sp-action:hover{background:var(--hover)}

  .sp-empty{padding:34px 20px;text-align:center;color:var(--muted)}

  .sp-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:14px;padding:14px}
  .sp-grid-card{border:1px solid var(--border);border-radius:.75rem;background:var(--surface);padding:16px;transition:transform .15s,border-color .15s,box-shadow .15s}
  .sp-grid-card:hover{transform:translateY(-2px);border-color:#d4c0b0;box-shadow:0 4px 12px rgba(28,25,23,.06)}
  .sp-grid-top{display:flex;align-items:center;gap:10px;margin-bottom:12px}
  .sp-grid-avatar{width:40px;height:40px;border-radius:.75rem;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:800;flex-shrink:0}
  .sp-grid-name{font-weight:600;color:var(--text);font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
  .sp-grid-owner{font-size:11px;color:var(--muted);margin-top:1px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
  .sp-grid-meta{display:flex;flex-wrap:wrap;gap:6px;margin-bottom:12px}
  .sp-grid-meta .sp-status{font-size:9px;padding:2px 8px}
  .sp-grid-meta .sp-online{font-size:11px}
  .sp-grid-row{display:flex;align-items:center;justify-content:space-between;padding:8px 0;border-top:1px solid var(--border-light);font-size:12px}
  .sp-grid-row:first-child{border-top:0;padding-top:0}
  .sp-grid-label{color:var(--muted);font-weight:600;font-size:10px;text-transform:uppercase;letter-spacing:.05em}
  .sp-grid-val{color:var(--body);font-weight:600;font-variant-numeric:tabular-nums}
  .sp-grid-foot{display:flex;align-items:center;justify-content:space-between;margin-top:12px;padding-top:10px;border-top:1px solid var(--border-light)}
  .sp-grid-cat{font-size:10px;font-weight:700;color:var(--body);background:var(--soft);padding:2px 8px;border-radius:999px;border:1px solid var(--border-light)}
  .sp-grid .sp-action{font-size:10px;height:28px;padding:0 10px}

  .pagination{display:flex;align-items:center;justify-content:space-between;padding:12px 20px;border-top:1px solid var(--border-light)}
  .page-info{font-size:12px;color:var(--muted)}
  .page-btns{display:flex;gap:4px}
  .page-btn{height:28px;min-width:28px;padding:0 8px;border:1px solid var(--border);border-radius:.75rem;background:var(--surface);color:var(--body);font-size:12px;font-family:inherit;font-weight:600;cursor:pointer;transition:all .12s}
  .page-btn:hover{background:var(--soft)}
  .page-btn.active{background:var(--primary);color:#fff;border-color:var(--primary)}

  @media(max-width:900px){.suppliers-page{padding:20px 16px}.sp-table-wrap{overflow-x:auto}}

  /* KPI Score */
  .sp-score{display:flex;align-items:center;gap:8px}
  .sp-score-ring{position:relative;width:36px;height:36px}
  .sp-score-ring svg{transform:rotate(-90deg)}
  .sp-score-ring circle{fill:none;stroke-width:3}
  .sp-score-ring .ring-bg{stroke:var(--border-light)}
  .sp-score-ring .ring-fill{stroke-linecap:round;transition:stroke-dashoffset .4s}
  .sp-score-num{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800}
  .sp-tier{display:inline-flex;align-items:center;border-radius:999px;padding:2px 8px;font-size:9px;font-weight:800;letter-spacing:.06em;text-transform:uppercase}

  /* KPI distribution summary */
  .kpi-summary{display:flex;align-items:center;gap:12px;padding:10px 16px;border-bottom:1px solid var(--border-light)}
  .kpi-bar{display:flex;height:6px;border-radius:3px;overflow:hidden;flex:1;max-width:300px}
  .kpi-bar-seg{height:100%;transition:width .3s}
  .kpi-legend{display:flex;gap:10px;flex-wrap:wrap}
  .kpi-legend-item{display:flex;align-items:center;gap:4px;font-size:10px;font-weight:700;color:var(--body)}
  .kpi-legend-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0}
</style>

<div class="suppliers-page">
  <div class="sp-inner">
    <div class="sp-head">
      <div class="sp-head-left">
        <p class="sp-eyebrow">Partner operations</p>
        <h1 class="sp-title">Suppliers</h1>
      </div>
      <div style="display:flex;align-items:center;gap:10px">
        <span class="sp-count"><strong><?= number_format($counts['all']) ?></strong> total</span>
        <div class="sp-view-toggle">
          <button type="button" class="sp-view-btn is-active" data-view="table" onclick="setSupplierView('table')" title="Table view">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
          </button>
          <button type="button" class="sp-view-btn" data-view="grid" onclick="setSupplierView('grid')" title="Grid view">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
          </button>
        </div>
      </div>
    </div>

    <div class="sp-card">
      <div class="toolbar">
        <div class="filters">
          <?php foreach (['all' => 'All', 'pending' => 'Pending', 'approved' => 'Active', 'verified' => 'Verified', 'rejected' => 'Rejected', 'banned' => 'Banned'] as $val => $label): ?>
            <a href="<?= URLROOT ?>/admin/suppliers?status=<?= $val ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>"
               class="filter <?= $status === $val ? 'active' : '' ?>">
              <?= $label ?>
              <span class="filter-count"><?= number_format($counts[$val] ?? 0) ?></span>
            </a>
          <?php endforeach; ?>
        </div>
      </div>

      <?php if (!empty($suppliers) && !empty($supplierScores)): ?>
      <div class="kpi-summary">
        <span style="font-size:11px;font-weight:800;color:var(--primary);white-space:nowrap">Quality:</span>
        <div class="kpi-bar">
          <?php
          $tierCounts = ['platinum' => 0, 'gold' => 0, 'silver' => 0, 'bronze' => 0, 'needs_improvement' => 0];
          foreach ($suppliers as $_s) {
              $_sid = (int)$_s['supplier_id'];
              $_tier = $supplierScores[$_sid]['tier'] ?? 'needs_improvement';
              $tierCounts[$_tier]++;
          }
          $_total = count($suppliers) ?: 1;
          $tierColors = ['platinum' => '#6d4c5b', 'gold' => '#b7792f', 'silver' => '#78716C', 'bronze' => '#92400E', 'needs_improvement' => '#991b1b'];
          $tierLabels = ['platinum' => 'Plat', 'gold' => 'Gold', 'silver' => 'Silver', 'bronze' => 'Bronze', 'needs_improvement' => 'Needs'];
          foreach ($tierCounts as $_key => $_cnt):
              if ($_cnt === 0) continue;
              $_pct = round(($_cnt / $_total) * 100);
          ?>
            <div class="kpi-bar-seg" style="width:<?= $_pct ?>%;background:<?= $tierColors[$_key] ?>"></div>
          <?php endforeach; ?>
        </div>
        <div class="kpi-legend">
          <?php foreach ($tierCounts as $_key => $_cnt): if ($_cnt === 0) continue; ?>
            <span class="kpi-legend-item"><span class="kpi-legend-dot" style="background:<?= $tierColors[$_key] ?>"></span><?= $tierLabels[$_key] ?> (<?= $_cnt ?>)</span>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <?php if (empty($suppliers)): ?>
        <div class="sp-empty">
          <strong>No suppliers found</strong>
          <p>Try adjusting your filters.</p>
        </div>
      <?php else: ?>
        <!-- Table View -->
        <div id="tableView">
          <div class="sp-table-wrap">
          <table class="sp-table">
            <thead>
              <tr>
                <th>Supplier</th>
                <th>Category</th>
                <th>Status</th>
                <th>Score</th>
                <th>Online</th>
                <th>Last Login</th>
                <th style="text-align:right">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($suppliers as $supplier): ?>
                <?php
                  [$sLabel, $sColor, $sBg] = $statusBadge($supplier['status'] ?? '');
                  $cats = array_values(array_filter(array_map('trim', explode(',', (string)($supplier['category_names'] ?? '')))));
                  $initial = mb_strtoupper(mb_substr(trim((string)($supplier['shop_name'] ?? 'S')), 0, 1));
                  $isOnline = !empty($supplier['is_online']);
                  $lastLogin = $relativeTime($supplier['last_login'] ?? null);
                ?>
                <tr>
                  <td>
                    <div class="sp-name-cell">
                      <span class="sp-avatar" style="background:<?= $sBg ?>;color:<?= $sColor ?>"><?= $h($initial) ?></span>
                      <div class="sp-name-text">
                        <div class="sp-shop"><?= $h($supplier['shop_name'] ?? 'Supplier') ?></div>
                        <div class="sp-owner"><?= $h($supplier['owner_name'] ?? '') ?></div>
                      </div>
                    </div>
                  </td>
                  <td>
                    <?php if (!empty($cats)): ?>
                      <?= $h($cats[0]) ?><?= count($cats) > 1 ? ' <span style="color:#A8A29E;font-size:11px">+' . (count($cats) - 1) . '</span>' : '' ?>
                    <?php else: ?>
                      <span style="color:#A8A29E">—</span>
                    <?php endif; ?>
                  </td>
                  <td><span class="sp-status" style="color:<?= $sColor ?>;background:<?= $sBg ?>"><?= $h($sLabel) ?></span></td>
                  <td>
                    <?php
                      $_sid = (int)$supplier['supplier_id'];
                      $_kpi = $supplierScores[$_sid] ?? null;
                      if ($_kpi):
                          $_score = $_kpi['score'];
                          $_circ = 2 * M_PI * 14;
                          $_off = $_circ - ($_score / 100) * $_circ;
                    ?>
                    <div class="sp-score">
                      <div class="sp-score-ring">
                        <svg width="36" height="36" viewBox="0 0 36 36">
                          <circle class="ring-bg" cx="18" cy="18" r="14" />
                          <circle class="ring-fill" cx="18" cy="18" r="14"
                            stroke="<?= $_kpi['tier_color'] ?>"
                            stroke-dasharray="<?= round($_circ, 2) ?>"
                            stroke-dashoffset="<?= round($_off, 2) ?>" />
                        </svg>
                        <span class="sp-score-num"><?= $_score ?></span>
                      </div>
                      <span class="sp-tier" style="color:<?= $_kpi['tier_color'] ?>;background:<?= $_kpi['tier_color'] ?>1A"><?= $_kpi['tier_label'] ?></span>
                    </div>
                    <?php else: ?>
                      <span style="color:#A8A29E">&mdash;</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <span class="sp-online">
                      <span class="sp-dot <?= $isOnline ? 'on' : 'off' ?>"></span>
                      <?= $isOnline ? 'Online' : 'Offline' ?>
                    </span>
                  </td>
                  <td><span class="sp-time"><?= $h($lastLogin) ?></span></td>
                  <td style="text-align:right">
                    <a class="sp-action" href="<?= URLROOT ?>/admin/supplier/<?= (int)$supplier['supplier_id'] ?>">
                      View <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M7 17L17 7M17 7H7M17 7v10"/></svg>
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          </div>
        </div>

        <!-- Grid View -->
        <div id="gridView" style="display:none">
          <div class="sp-grid">
            <?php foreach ($suppliers as $supplier): ?>
              <?php
                [$sLabel, $sColor, $sBg] = $statusBadge($supplier['status'] ?? '');
                $cats = array_values(array_filter(array_map('trim', explode(',', (string)($supplier['category_names'] ?? '')))));
                $initial = mb_strtoupper(mb_substr(trim((string)($supplier['shop_name'] ?? 'S')), 0, 1));
                $isOnline = !empty($supplier['is_online']);
                $lastLogin = $relativeTime($supplier['last_login'] ?? null);
              ?>
              <div class="sp-grid-card">
                <div class="sp-grid-top">
                  <span class="sp-grid-avatar" style="background:<?= $sBg ?>;color:<?= $sColor ?>"><?= $h($initial) ?></span>
                  <div style="min-width:0;flex:1">
                    <div class="sp-grid-name"><?= $h($supplier['shop_name'] ?? 'Supplier') ?></div>
                    <div class="sp-grid-owner"><?= $h($supplier['owner_name'] ?? '') ?></div>
                  </div>
                </div>
                <div class="sp-grid-meta">
                  <span class="sp-status" style="color:<?= $sColor ?>;background:<?= $sBg ?>"><?= $h($sLabel) ?></span>
                  <?php
                    $_gsid = (int)$supplier['supplier_id'];
                    $_gkpi = $supplierScores[$_gsid] ?? null;
                    if ($_gkpi): ?>
                    <span class="sp-tier" style="color:<?= $_gkpi['tier_color'] ?>;background:<?= $_gkpi['tier_color'] ?>1A">
                      <?= $_gkpi['tier_label'] ?> <?= $_gkpi['score'] ?>
                    </span>
                  <?php endif; ?>
                  <span class="sp-online">
                    <span class="sp-dot <?= $isOnline ? 'on' : 'off' ?>"></span>
                    <?= $isOnline ? 'Online' : 'Offline' ?>
                  </span>
                </div>
                <div class="sp-grid-row">
                  <span class="sp-grid-label">Category</span>
                  <span class="sp-grid-val"><?= !empty($cats) ? $h($cats[0]) : '—' ?></span>
                </div>
                <div class="sp-grid-row">
                  <span class="sp-grid-label">Last Login</span>
                  <span class="sp-grid-val"><?= $h($lastLogin) ?></span>
                </div>
                <div class="sp-grid-foot">
                  <?php if (!empty($cats)): ?>
                    <span class="sp-grid-cat"><?= $h($cats[0]) ?></span>
                  <?php else: ?>
                    <span></span>
                  <?php endif; ?>
                  <a class="sp-action" href="<?= URLROOT ?>/admin/supplier/<?= (int)$supplier['supplier_id'] ?>">
                    View <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M7 17L17 7M17 7H7M17 7v10"/></svg>
                  </a>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

      <?php
      $baseParams = http_build_query(array_filter([
          'status' => $status,
          'search' => $search,
      ], static fn($value) => $value !== ''));
      require APPROOT . '/views/partials/_pagination.php';
      ?>
    </div>
  </div>
</div>
<?php
};
?>
<!DOCTYPE html>
<html lang="en">
<head><?php require_once APPROOT . '/views/dashboardLayout/head.php'; ?></head>
<body class="grid h-screen gap-0 bg-app-page" style="grid-template-columns:280px 1fr">
  <?php require APPROOT . '/views/dashboardLayout/adminsidebar.php'; ?>
  <script>
    function setSupplierView(mode) {
      var table = document.getElementById('tableView');
      var grid = document.getElementById('gridView');
      if (table) table.style.display = mode === 'table' ? '' : 'none';
      if (grid) grid.style.display = mode === 'grid' ? '' : 'none';
      document.querySelectorAll('.sp-view-btn').forEach(function(btn) {
        btn.classList.toggle('is-active', btn.dataset.view === mode);
      });
      try { localStorage.setItem('supplierViewMode', mode); } catch(e) {}
    }
    (function() {
      try {
        var saved = localStorage.getItem('supplierViewMode');
        if (saved === 'grid') setSupplierView('grid');
      } catch(e) {}
    })();
  </script>
</body>
</html>