<?php
$dashboardTitle = 'Customers';
$dashboardCrumb = 'Directory';
$customers = $customers ?? [];
$stats = $stats ?? [];
$status = $status ?? 'all';
$search = $search ?? '';
$dashboardContentClass = 'customer-directory-shell';

$h = static fn($value) => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');

$statusMeta = static function ($value, $deletedAt = null) {
    if (!empty($deletedAt)) {
        return ['Deleted', '#8c3941', 'trash-2'];
    }
    return match (strtolower((string)$value)) {
        'active' => ['Active', '#4f7c69', 'circle-check'],
        'suspended' => ['Suspended', '#b7792f', 'pause-circle'],
        'banned' => ['Banned', '#b94b4b', 'ban'],
        'locked' => ['Locked', '#7b5c69', 'lock'],
        default => [ucfirst((string)$value ?: 'Unknown'), '#7b5c69', 'circle'],
    };
};

$filterUrl = static function ($filter, $searchTerm = '') {
    $params = ['status' => $filter];
    if ($searchTerm !== '') $params['search'] = $searchTerm;
    return URLROOT . '/admin/customers?' . http_build_query($params);
};

$dashboardContent = function () use (
    $customers, $stats, $status, $search, $h, $statusMeta, $filterUrl,
    $currentPage, $totalPages, $totalCount, $perPage
) {
?>
<style>
  .customer-directory-shell{min-height:100%;padding:30px;background:#fbfbf9}
  .cd{--ink:#6d4c5b;--body:#7b5c69;--muted:#a58b96;--line:#ead8c7;--wash:#FFFFFF;--wine:#6d4c5b;max-width:1380px;margin:0 auto;color:var(--ink)}
  .cd-head{display:flex;align-items:flex-end;justify-content:space-between;gap:22px;margin-bottom:22px}
  .cd-kicker{margin:0 0 7px;color:#9b7d89;font-size:10px;font-weight:800;letter-spacing:.15em;text-transform:uppercase}
  .cd-title{margin:0;color:var(--ink);font:650 clamp(30px,3vw,42px)/1 "Playfair Display",serif}
  .cd-copy{max-width:620px;margin:10px 0 0;color:var(--body);font-size:12px;line-height:1.6}
  .cd-export{display:inline-flex;min-height:40px;align-items:center;gap:8px;border:1px solid var(--line);border-radius:999px;padding:0 16px;background:#fff;color:var(--wine);font-size:11px;font-weight:800;text-decoration:none;box-shadow:0 10px 28px rgba(52,35,43,.06)}
  .cd-export:hover{background:var(--wine);color:#fff;border-color:var(--wine)}
  .cd-export svg{width:14px;height:14px}

  .cd-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:18px}
  .cd-stat{border:1px solid var(--line);border-radius:14px;background:#fff;padding:16px 18px;box-shadow:0 12px 30px rgba(52,35,43,.05)}
  .cd-stat-label{display:flex;align-items:center;gap:7px;color:#a58b96;font-size:9px;font-weight:800;letter-spacing:.1em;text-transform:uppercase}
  .cd-stat-label svg{width:13px;height:13px}
  .cd-stat-value{margin-top:8px;color:var(--ink);font-size:26px;font-weight:800;font-variant-numeric:tabular-nums}

  .cd-toolbar{overflow:hidden;margin-bottom:18px;border:1px solid var(--line);border-radius:15px;background:#fff;box-shadow:0 18px 45px rgba(52,35,43,.055)}
  .cd-search-row{padding:14px}
  .cd-search-form{display:grid;grid-template-columns:minmax(260px,2fr) minmax(150px,.6fr) auto;gap:8px;align-items:center}
  .cd-search-wrap{position:relative;min-width:0}
  .cd-search-icon{position:absolute;left:13px;top:50%;width:16px;height:16px;color:#9b7d89;transform:translateY(-50%)}
  .cd-search{width:100%;min-height:42px;box-sizing:border-box;border:1px solid #e4d2c3;border-radius:10px;background:#fff;padding:0 39px;color:var(--ink);font:500 12px Inter,sans-serif}
  .cd-search::placeholder{color:#b79c8b}
  .cd-search:focus-visible,.cd-select:focus-visible,.cd-search-btn:focus-visible,.cd-filter:focus-visible,.cd-action:focus-visible,.page-btn:focus-visible{outline:3px solid rgba(109,76,91,.2);outline-offset:2px}
  .cd-select-wrap{position:relative;min-width:0}
  .cd-select-icon{position:absolute;left:12px;top:50%;width:14px;height:14px;color:#9b7d89;transform:translateY(-50%);pointer-events:none}
  .cd-select{width:100%;min-height:42px;box-sizing:border-box;appearance:none;border:1px solid #e4d2c3;border-radius:10px;background:#fff;padding:0 31px 0 34px;color:#5f4651;font:650 10px Inter,sans-serif;cursor:pointer}
  .cd-select-chevron{position:absolute;right:10px;top:50%;width:13px;height:13px;color:#9b7d89;transform:translateY(-50%);pointer-events:none}
  .cd-search-btn{display:inline-flex;min-height:42px;align-items:center;justify-content:center;gap:6px;border:1px solid var(--wine);border-radius:10px;padding:0 15px;background:var(--wine);color:#fff;font:750 11px Inter,sans-serif;cursor:pointer}
  .cd-filter-row{display:flex;gap:4px;overflow-x:auto;border-top:1px solid var(--line);padding:9px 12px;background:var(--wash)}
  .cd-filter{display:inline-flex;min-height:35px;flex:0 0 auto;align-items:center;gap:7px;border-radius:9px;padding:0 11px;color:#8e727e;font-size:10px;font-weight:750;text-decoration:none}
  .cd-filter:hover{background:#fff;color:var(--wine)}
  .cd-filter.active{background:var(--wine);color:#fff;box-shadow:0 8px 18px rgba(109,76,91,.17)}

  .cd-table-wrap{overflow:hidden;border:1px solid var(--line);border-radius:15px;background:#fff;box-shadow:0 16px 40px rgba(52,35,43,.05)}
  .cd-table{width:100%;border-collapse:collapse}
  .cd-table thead th{padding:12px 16px;border-bottom:1px solid var(--line);background:var(--wash);color:#a58b96;font-size:9px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;text-align:left}
  .cd-table tbody td{padding:14px 16px;border-bottom:1px solid #f3ebe3;font-size:12px;color:var(--ink);vertical-align:middle}
  .cd-table tbody tr:last-child td{border-bottom:0}
  .cd-table tbody tr:hover{background:#fdf9f5}
  .cd-cust{display:flex;align-items:center;gap:11px;min-width:0}
  .cd-avatar{display:inline-flex;width:38px;height:38px;flex:0 0 38px;align-items:center;justify-content:center;border-radius:11px;background:#f2e8e1;color:var(--wine);font-size:13px;font-weight:800}
  .cd-cust-name{font-weight:800;color:var(--ink)}
  .cd-cust-email{margin-top:2px;color:#8e727e;font-size:10px;font-weight:600}
  .cd-badge{display:inline-flex;min-height:24px;align-items:center;gap:5px;border-radius:999px;padding:0 9px;color:var(--bc,#7b5c69);background:color-mix(in srgb,var(--bc,#7b5c69) 11%,white);font-size:8px;font-weight:800;text-transform:uppercase}
  .cd-badge svg{width:11px;height:11px}
  .cd-num{font-variant-numeric:tabular-nums;font-weight:700}
  .cd-muted{color:#a58b96;font-size:10px}
  .cd-action{display:inline-flex;min-height:32px;align-items:center;gap:6px;border:1px solid #ddc8b9;border-radius:9px;padding:0 11px;background:#fff;color:var(--wine);font-size:9px;font-weight:800;text-decoration:none}
  .cd-action:hover{background:var(--wine);color:#fff;border-color:var(--wine)}
  .cd-action svg{width:12px;height:12px}
  .cd-empty{border:1px dashed #decbbb;border-radius:15px;background:#FFFFFF;padding:70px 24px;text-align:center}
  .cd-empty-icon{display:inline-flex;width:52px;height:52px;align-items:center;justify-content:center;border-radius:16px;background:#fff;color:#9b7d89;box-shadow:0 10px 25px rgba(52,35,43,.06)}
  .cd-empty h2{margin:15px 0 6px;color:var(--ink);font-size:16px}
  .cd-empty p{margin:0;color:#9b7d89;font-size:11px}

  .pagination{display:flex;align-items:center;justify-content:space-between;gap:16px;margin-top:17px;border:1px solid var(--line);border-radius:12px;background:#FFFFFF;padding:13px 15px}
  .page-info{color:#9b7d89;font-size:10px;font-weight:650}.page-btns{display:flex;align-items:center;gap:5px}.page-btn{display:inline-flex;width:30px;height:30px;align-items:center;justify-content:center;border:1px solid #e4d2c3;border-radius:8px;background:#fff;color:#7b5c69;font-size:10px;font-weight:700;text-decoration:none}.page-btn.active{border-color:var(--wine);background:var(--wine);color:#fff}

  @media(max-width:980px){.cd-stats{grid-template-columns:repeat(2,minmax(0,1fr))}.cd-search-form{grid-template-columns:1fr}.cd-table thead{display:none}.cd-table tbody td{display:block;border-bottom:0;padding:5px 16px}.cd-table tbody tr{display:block;border-bottom:1px solid #f0e5dc;padding:10px 0}}
  @media(max-width:560px){.customer-directory-shell{padding:18px}.cd-head{flex-direction:column;align-items:flex-start}.pagination{flex-direction:column;align-items:flex-start}}
</style>

<div class="cd">
  <header class="cd-head">
    <div>
      <p class="cd-kicker">Community operations</p>
      <h1 class="cd-title">Customer directory</h1>
      <p class="cd-copy">Browse customer accounts, review booking activity and spend, and moderate accounts when needed.</p>
    </div>
    <a class="cd-export" href="<?= URLROOT ?>/admin/customers?<?= $h(http_build_query(array_filter(['status' => $status, 'search' => $search, 'export' => 'csv']))) ?>">
      <i data-lucide="download"></i> Export CSV
    </a>
  </header>

  <section class="cd-stats" aria-label="Customer summary">
    <div class="cd-stat"><div class="cd-stat-label"><i data-lucide="users"></i> Total customers</div><div class="cd-stat-value"><?= number_format((int)($stats['total'] ?? 0)) ?></div></div>
    <div class="cd-stat"><div class="cd-stat-label"><i data-lucide="user-check"></i> Active</div><div class="cd-stat-value"><?= number_format((int)($stats['active'] ?? 0)) ?></div></div>
    <div class="cd-stat"><div class="cd-stat-label"><i data-lucide="user-x"></i> Suspended / Banned</div><div class="cd-stat-value"><?= number_format((int)($stats['suspended_banned'] ?? 0)) ?></div></div>
    <div class="cd-stat"><div class="cd-stat-label"><i data-lucide="user-plus"></i> New this month</div><div class="cd-stat-value"><?= number_format((int)($stats['new_this_month'] ?? 0)) ?></div></div>
  </section>

  <section class="cd-toolbar" aria-label="Customer filters">
    <div class="cd-search-row">
      <form class="cd-search-form" method="get" action="<?= URLROOT ?>/admin/customers">
        <input type="hidden" name="status" value="<?= $h($status) ?>">
        <div class="cd-search-wrap">
          <i data-lucide="search" class="cd-search-icon"></i>
          <input class="cd-search" type="search" name="search" value="<?= $h($search) ?>" placeholder="Search by name, email or phone">
        </div>
        <button class="cd-search-btn" type="submit"><i data-lucide="search" class="h-3.5 w-3.5"></i>Search</button>
        <?php if ($search !== ''): ?>
          <a class="cd-action" href="<?= $h($filterUrl($status, '')) ?>"><i data-lucide="x"></i>Clear</a>
        <?php endif; ?>
      </form>
    </div>
    <div class="cd-filter-row">
      <?php
        $tabs = ['all' => 'All', 'active' => 'Active', 'suspended' => 'Suspended', 'banned' => 'Banned', 'deleted' => 'Deleted'];
        foreach ($tabs as $value => $label):
      ?>
        <a class="cd-filter <?= $status === $value ? 'active' : '' ?>" href="<?= $h($filterUrl($value, $search)) ?>"><?= $h($label) ?></a>
      <?php endforeach; ?>
    </div>
  </section>

  <?php if (empty($customers)): ?>
    <div class="cd-empty">
      <span class="cd-empty-icon"><i data-lucide="users"></i></span>
      <h2>No customers found</h2>
      <p>Adjust the status filter or clear the search.</p>
    </div>
  <?php else: ?>
    <div class="cd-table-wrap">
      <table class="cd-table">
        <thead>
          <tr>
            <th>Customer</th><th>Phone</th><th>Status</th><th>Bookings</th><th>Joined</th><th>Last login</th><th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($customers as $c): ?>
            <?php
              [$statusLabel, $statusColor, $statusIcon] = $statusMeta($c['status'] ?? '', $c['deleted_at'] ?? null);
              $initial = mb_strtoupper(mb_substr(trim((string)($c['name'] ?? $c['email'] ?? 'C')), 0, 1));
            ?>
            <tr>
              <td>
                <div class="cd-cust">
                  <span class="cd-avatar"><?= $h($initial) ?></span>
                  <div style="min-width:0">
                    <div class="cd-cust-name"><?= $h($c['name'] ?? 'Unnamed') ?></div>
                    <div class="cd-cust-email"><?= $h($c['email'] ?? '') ?></div>
                  </div>
                </div>
              </td>
              <td><span class="cd-muted"><?= $h($c['phone'] ?? '—') ?: '—' ?></span></td>
              <td><span class="cd-badge" style="--bc:<?= $h($statusColor) ?>"><i data-lucide="<?= $h($statusIcon) ?>"></i><?= $h($statusLabel) ?></span></td>
              <td><span class="cd-num"><?= number_format((int)($c['bookings_count'] ?? 0)) ?></span></td>
              <td><span class="cd-muted"><?= !empty($c['created_at']) ? date('M j, Y', strtotime($c['created_at'])) : '—' ?></span></td>
              <td><span class="cd-muted"><?= !empty($c['last_login']) ? date('M j, Y H:i', strtotime($c['last_login'])) : 'Never' ?></span></td>
              <td style="text-align:right">
                <a class="cd-action" href="<?= URLROOT ?>/admin/customer/<?= (int)$c['user_id'] ?>">Manage<i data-lucide="arrow-up-right"></i></a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>

  <?php
  $baseParams = http_build_query(array_filter([
      'status' => $status,
      'search' => $search,
  ], static fn($value) => $value !== '' && $value !== 'all'));
  require APPROOT . '/views/partials/_pagination.php';
  ?>
</div>
<?php
};
?>
<!DOCTYPE html>
<html lang="en">
<head><?php $pageTitle = 'Customers — Admin'; ?>
    <?php require_once APPROOT . '/views/dashboardLayout/head.php'; ?></head>
<body class="grid h-screen gap-0 bg-app-page" style="grid-template-columns:280px 1fr">
  <?php require APPROOT . '/views/dashboardLayout/adminsidebar.php'; ?>
</body>
</html>
