<?php
$dashboardTitle = 'Suppliers';
$dashboardCrumb = 'Directory';
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
    $perPage
) {
    $counts = [
        'all' => (int)($stats['total'] ?? 0),
        'pending' => (int)($stats['pending'] ?? 0),
        'approved' => (int)($stats['approved'] ?? 0),
        'verified' => (int)($stats['verified'] ?? 0),
        'rejected' => (int)($stats['rejected'] ?? 0),
        'banned' => (int)($stats['banned'] ?? 0),
    ];
?>
<style>
  .supplier-directory-shell{min-height:100%;padding:30px;background:#fbfbf9}
  .supplier-directory{--ink:#6d4c5b;--body:#7b5c69;--muted:#a58b96;--line:#ead8c7;--paper:#FFFFFF;--wash:#FFFFFF;--wine:#6d4c5b;max-width:1380px;margin:0 auto;color:var(--ink)}
  .sd-head{display:flex;align-items:flex-end;justify-content:space-between;gap:22px;margin-bottom:22px}
  .sd-kicker{margin:0 0 7px;color:#9b7d89;font-size:10px;font-weight:800;letter-spacing:.15em;text-transform:uppercase}
  .sd-title{margin:0;color:var(--ink);font:650 clamp(30px,3vw,42px)/1 "Playfair Display",serif}
  .sd-copy{max-width:620px;margin:10px 0 0;color:var(--body);font-size:12px;line-height:1.6}
  .sd-total{display:inline-flex;min-height:40px;align-items:center;gap:8px;border:1px solid var(--line);border-radius:999px;padding:0 14px;background:#FFFFFF;color:var(--wine);font-size:11px;font-weight:800;box-shadow:0 10px 28px rgba(52,35,43,.06)}
  .sd-total strong{font-size:15px;font-variant-numeric:tabular-nums}

  .sd-toolbar{overflow:hidden;margin-bottom:18px;border:1px solid var(--line);border-radius:15px;background:#FFFFFF;box-shadow:0 18px 45px rgba(52,35,43,.055)}
  .sd-search-row{padding:14px}
  .sd-search-form{display:grid;grid-template-columns:minmax(180px,1fr) minmax(220px,1fr) minmax(180px,1fr) auto;gap:8px;align-items:center}
  .sd-select:focus-visible,.sd-search-btn:focus-visible,.sd-filter:focus-visible,.sd-action:focus-visible,.page-btn:focus-visible{outline:3px solid rgba(109,76,91,.2);outline-offset:2px}
  .sd-search-btn{display:inline-flex;min-height:42px;align-items:center;justify-content:center;gap:6px;border:1px solid var(--wine);border-radius:10px;padding:0 15px;background:var(--wine);color:#FFFFFF;font:750 11px Inter,sans-serif;cursor:pointer}
  .sd-select-wrap{position:relative;min-width:0}
  .sd-select-icon{position:absolute;left:12px;top:50%;width:14px;height:14px;color:#9b7d89;transform:translateY(-50%);pointer-events:none}
  .sd-select{width:100%;min-height:42px;box-sizing:border-box;appearance:none;border:1px solid #e4d2c3;border-radius:10px;background:#FFFFFF;padding:0 31px 0 34px;color:#5f4651;font:650 10px Inter,sans-serif;cursor:pointer}
  .sd-select-chevron{position:absolute;right:10px;top:50%;width:13px;height:13px;color:#9b7d89;transform:translateY(-50%);pointer-events:none}
  .sd-active-filters{display:flex;flex-wrap:wrap;align-items:center;gap:7px;border-top:1px solid var(--line);padding:10px 13px;background:#FFFFFF}
  .sd-active-label{margin-right:2px;color:#a58b96;font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase}
  .sd-active-chip{display:inline-flex;min-height:27px;align-items:center;gap:6px;border-radius:999px;padding:0 10px;background:#f2e8e1;color:#6d4c5b;font-size:11px;font-weight:750;text-decoration:none}
  .sd-active-chip:hover{background:#e9d8cc}
  .sd-active-chip svg{width:11px;height:11px}
  .sd-clear-all{margin-left:auto;color:#8e727e;font-size:11px;font-weight:750;text-decoration:none}
  .sd-clear-all:hover{color:var(--wine);text-decoration:underline}
  .sd-filter-row{display:flex;gap:4px;overflow-x:auto;border-top:1px solid var(--line);padding:9px 12px;background:var(--wash)}
  .sd-filter{display:inline-flex;min-height:35px;flex:0 0 auto;align-items:center;gap:7px;border-radius:9px;padding:0 11px;color:#8e727e;font-size:12px;font-weight:750;text-decoration:none}
  .sd-filter:hover{background:#FFFFFF;color:var(--wine)}
  .sd-filter.active{background:var(--wine);color:#FFFFFF;box-shadow:0 8px 18px rgba(109,76,91,.17)}
  .sd-filter-count{display:inline-flex;min-width:20px;height:20px;align-items:center;justify-content:center;border-radius:999px;padding:0 5px;background:rgba(109,76,91,.09);font-size:10px;font-variant-numeric:tabular-nums}
  .sd-filter.active .sd-filter-count{background:rgba(252,248,245,.16)}

  .sd-featured{margin-bottom:18px;border:1px solid var(--line);border-radius:15px;background:#FFFFFF;box-shadow:0 16px 40px rgba(52,35,43,.05)}
  .sd-section-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:14px 17px;border-bottom:1px solid var(--line)}
  .sd-section-title{display:flex;align-items:center;gap:8px;margin:0;color:var(--ink);font-size:11px;font-weight:800}
  .sd-section-title svg{width:15px;height:15px;color:#b7792f}
  .sd-section-note{color:#a58b96;font-size:11px;font-weight:700}
  .sd-featured-grid{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:0}
  .sd-featured-item{min-width:0;padding:15px;border-right:1px solid #f0e5dc;text-decoration:none;transition:background .15s ease}
  .sd-featured-item:last-child{border-right:0}
  .sd-featured-item:hover{background:#fdf9f5}
  .sd-featured-rank{color:#b7792f;font-size:10px;font-weight:800;letter-spacing:.12em;text-transform:uppercase}
  .sd-featured-name{margin-top:5px;overflow:hidden;color:var(--ink);font-size:13px;font-weight:800;text-overflow:ellipsis;white-space:nowrap}
  .sd-featured-stats{margin-top:6px;color:#8e727e;font-size:11px;font-weight:650;line-height:1.5}

  .sd-directory-list{display:grid;gap:10px}
  .sd-row{position:relative;display:grid;grid-template-columns:minmax(250px,1.35fr) minmax(190px,.9fr) minmax(255px,1.1fr) auto;gap:18px;align-items:center;overflow:hidden;border:1px solid var(--line);border-radius:14px;background:#FFFFFF;padding:17px 18px;box-shadow:0 10px 28px rgba(52,35,43,.04);transition:transform .15s ease,border-color .15s ease,box-shadow .15s ease}
  .sd-row:hover{transform:translateY(-1px);border-color:#d8c1b1;box-shadow:0 16px 34px rgba(52,35,43,.075)}
  .sd-business{display:flex;min-width:0;align-items:center;gap:12px}
  .sd-avatar{display:inline-flex;width:44px;height:44px;flex:0 0 44px;align-items:center;justify-content:center;border-radius:12px;background:color-mix(in srgb,var(--status-color) 10%,white);color:var(--status-color);font-size:14px;font-weight:800}
  .sd-business-copy{min-width:0}
  .sd-name{overflow:hidden;color:var(--ink);font-size:13px;font-weight:800;text-overflow:ellipsis;white-space:nowrap}
  .sd-owner{margin-top:3px;overflow:hidden;color:#8e727e;font-size:12px;font-weight:600;text-overflow:ellipsis;white-space:nowrap}
  .sd-category{display:flex;flex-wrap:wrap;gap:5px}
  .sd-chip{display:inline-flex;min-height:26px;align-items:center;border-radius:999px;padding:0 10px;background:#FFFFFF;color:#7b5c69;font-size:10px;font-weight:750}
  .sd-chip-more{background:#f0e6df;color:#6d4c5b}
  .sd-signals{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:8px}
  .sd-signal{min-width:0;border-left:1px solid #f0e5dc;padding-left:10px}
  .sd-signal:first-child{border-left:0;padding-left:0}
  .sd-signal-label{color:#a58b96;font-size:10px;font-weight:800;letter-spacing:.08em;text-transform:uppercase}
  .sd-signal-value{display:block;margin-top:4px;overflow:hidden;color:var(--ink);font-size:13px;font-weight:750;text-overflow:ellipsis;white-space:nowrap;font-variant-numeric:tabular-nums}
  .sd-state{display:grid;min-width:125px;gap:8px;justify-items:end}
  .sd-badge{display:inline-flex;min-height:27px;align-items:center;gap:6px;border-radius:999px;padding:0 11px;color:var(--status-color);background:color-mix(in srgb,var(--status-color) 10%,white);font-size:10px;font-weight:800;text-transform:uppercase}
  .sd-badge svg{width:11px;height:11px}
  .sd-payment{display:inline-flex;align-items:center;gap:5px;color:#8e727e;font-size:11px;font-weight:700}
  .sd-payment::before{content:"";width:6px;height:6px;border-radius:50%;background:#a58b96}
  .sd-payment.is-paid::before{background:#4f7c69}.sd-payment.is-pending::before{background:#b7792f}.sd-payment.is-unpaid::before{background:#b94b4b}
  .sd-warning{color:#b94b4b;font-size:10px;font-weight:800}
  .sd-action{display:inline-flex;min-height:34px;align-items:center;gap:6px;border:1px solid #ddc8b9;border-radius:9px;padding:0 12px;background:#FFFFFF;color:var(--wine);font-size:11px;font-weight:800;text-decoration:none}
  .sd-action:hover{background:var(--wine);color:#FFFFFF;border-color:var(--wine)}
  .sd-action svg{width:12px;height:12px}
  .sd-empty{border:1px dashed #decbbb;border-radius:15px;background:#FFFFFF;padding:70px 24px;text-align:center}
  .sd-empty-icon{display:inline-flex;width:52px;height:52px;align-items:center;justify-content:center;border-radius:16px;background:#FFFFFF;color:#9b7d89;box-shadow:0 10px 25px rgba(52,35,43,.06)}
  .sd-empty h2{margin:15px 0 6px;color:var(--ink);font-size:16px}
  .sd-empty p{margin:0;color:#9b7d89;font-size:11px}

  .pagination{display:flex;align-items:center;justify-content:space-between;gap:16px;margin-top:17px;border:1px solid var(--line);border-radius:12px;background:#FFFFFF;padding:13px 15px}
  .page-info{color:#9b7d89;font-size:12px;font-weight:650}.page-btns{display:flex;align-items:center;gap:5px}.page-btn{display:inline-flex;width:32px;height:32px;align-items:center;justify-content:center;border:1px solid #e4d2c3;border-radius:8px;background:#FFFFFF;color:#7b5c69;font-size:12px;font-weight:700;text-decoration:none}.page-btn.active{border-color:var(--wine);background:var(--wine);color:#FFFFFF}

  @media(max-width:1150px){.sd-search-form{grid-template-columns:1fr 1fr 1fr}.sd-search-btn{grid-column:3}.sd-featured-grid{grid-template-columns:repeat(3,minmax(0,1fr))}.sd-featured-item:nth-child(3){border-right:0}.sd-row{grid-template-columns:minmax(230px,1.2fr) minmax(170px,.8fr) minmax(220px,1fr)}.sd-state{grid-column:1/-1;display:flex;align-items:center;justify-content:flex-end}}
  @media(max-width:780px){.supplier-directory-shell{padding:20px}.sd-head{align-items:flex-start;flex-direction:column}.sd-search-form{grid-template-columns:1fr}.sd-search-btn{grid-column:auto}.sd-active-filters{align-items:flex-start}.sd-clear-all{width:100%;margin:2px 0 0}.sd-featured-grid{grid-template-columns:1fr}.sd-featured-item{border-right:0;border-bottom:1px solid #f0e5dc}.sd-featured-item:last-child{border-bottom:0}.sd-row{grid-template-columns:1fr;gap:14px;padding:17px 16px}.sd-state{grid-column:auto;justify-content:flex-start}.sd-signals{padding-top:12px;border-top:1px solid #f0e5dc}.pagination{align-items:flex-start;flex-direction:column}}
  @media(prefers-reduced-motion:reduce){.sd-row,.sd-featured-item{transition:none}}
</style>

<div class="supplier-directory">
  <header class="sd-head">
    <div>
      <p class="sd-kicker">Partner operations</p>
      <h1 class="sd-title">Supplier directory</h1>
      <p class="sd-copy">Review applications, monitor account health, and open each supplier’s operational record.</p>
    </div>
    <span class="sd-total"><strong><?= number_format($counts['all']) ?></strong> suppliers</span>
  </header>

  <section class="sd-toolbar" aria-label="Supplier filters">
    <div class="sd-search-row">
      <form class="sd-search-form" method="get" action="<?= URLROOT ?>/admin/suppliers">
        <div class="sd-select-wrap">
          <i data-lucide="activity" class="sd-select-icon"></i>
          <select class="sd-select" name="status" aria-label="Supplier status">
            <?php foreach (['all' => 'All statuses', 'pending' => 'Pending', 'approved' => 'Approved', 'verified' => 'Verified', 'rejected' => 'Rejected', 'banned' => 'Banned'] as $value => $label): ?>
              <option value="<?= $value ?>" <?= $status === $value ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
          </select>
          <i data-lucide="chevron-down" class="sd-select-chevron"></i>
        </div>
        <div class="sd-select-wrap">
          <i data-lucide="tags" class="sd-select-icon"></i>
          <select class="sd-select" name="category" aria-label="Supplier category">
            <option value="0">All categories</option>
            <?php foreach ($categories as $category): ?>
              <option value="<?= (int)$category['id'] ?>" <?= $categoryId === (int)$category['id'] ? 'selected' : '' ?>><?= $h($category['name'] ?? '') ?></option>
            <?php endforeach; ?>
          </select>
          <i data-lucide="chevron-down" class="sd-select-chevron"></i>
        </div>
        <div class="sd-select-wrap">
          <i data-lucide="wallet-cards" class="sd-select-icon"></i>
          <select class="sd-select" name="payment" aria-label="Payment status">
            <option value="all" <?= $paymentStatus === 'all' ? 'selected' : '' ?>>All payments</option>
            <option value="paid" <?= $paymentStatus === 'paid' ? 'selected' : '' ?>>Paid</option>
            <option value="unpaid" <?= $paymentStatus === 'unpaid' ? 'selected' : '' ?>>Unpaid</option>
          </select>
          <i data-lucide="chevron-down" class="sd-select-chevron"></i>
        </div>
        <button class="sd-search-btn" type="submit"><i data-lucide="sliders-horizontal" class="h-3.5 w-3.5"></i>Apply</button>
      </form>
    </div>
    <?php
      $activeCategoryName = '';
      foreach ($categories as $category) {
          if ((int)$category['id'] === $categoryId) $activeCategoryName = (string)($category['name'] ?? '');
      }
      $hasActiveFilters = $search !== '' || $status !== 'all' || $categoryId > 0 || $paymentStatus !== 'all';
    ?>
    <?php if ($hasActiveFilters): ?>
      <div class="sd-active-filters">
        <span class="sd-active-label">Active filters</span>
        <?php if ($search !== ''): ?>
          <a class="sd-active-chip" href="<?= $h($filterUrl($status, '', $categoryId, $paymentStatus)) ?>">Search: “<?= $h($search) ?>” <i data-lucide="x"></i></a>
        <?php endif; ?>
        <?php if ($status !== 'all'): ?>
          <a class="sd-active-chip" href="<?= $h($filterUrl('all', $search, $categoryId, $paymentStatus)) ?>"><?= $h(ucfirst($status)) ?> <i data-lucide="x"></i></a>
        <?php endif; ?>
        <?php if ($categoryId > 0): ?>
          <a class="sd-active-chip" href="<?= $h($filterUrl($status, $search, 0, $paymentStatus)) ?>"><?= $h($activeCategoryName) ?> <i data-lucide="x"></i></a>
        <?php endif; ?>
        <?php if ($paymentStatus !== 'all'): ?>
          <a class="sd-active-chip" href="<?= $h($filterUrl($status, $search, $categoryId, 'all')) ?>"><?= $h(ucfirst($paymentStatus)) ?> <i data-lucide="x"></i></a>
        <?php endif; ?>
        <a class="sd-clear-all" href="<?= URLROOT ?>/admin/suppliers">Clear all</a>
      </div>
    <?php endif; ?>
  </section>

  <?php if ($status === 'all' && $search === '' && $categoryId === 0 && $paymentStatus === 'all' && !empty($topSuppliers)): ?>
    <section class="sd-featured">
      <div class="sd-section-head">
        <h2 class="sd-section-title"><i data-lucide="trophy"></i> Top-performing partners</h2>
        <span class="sd-section-note">Ranked by confirmed revenue</span>
      </div>
      <div class="sd-featured-grid">
        <?php foreach ($topSuppliers as $index => $top): ?>
          <a class="sd-featured-item" href="<?= URLROOT ?>/admin/supplier/<?= (int)$top['supplier_id'] ?>">
            <div class="sd-featured-rank">Rank <?= $index + 1 ?></div>
            <div class="sd-featured-name"><?= $h($top['shop_name'] ?? 'Supplier') ?></div>
            <div class="sd-featured-stats"><?= $money($top['revenue_earned'] ?? 0) ?><br><?= (int)($top['completed_bookings'] ?? 0) ?> bookings · <?= number_format((float)($top['avg_rating'] ?? 0), 1) ?> ★</div>
          </a>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>

  <?php if (empty($suppliers)): ?>
    <div class="sd-empty">
      <span class="sd-empty-icon"><i data-lucide="store"></i></span>
      <h2>No suppliers found</h2>
      <p>Adjust the filters or clear the active filters above.</p>
    </div>
  <?php else: ?>
    <section class="sd-directory-list" aria-label="Supplier directory results">
      <?php foreach ($suppliers as $supplier): ?>
        <?php
        [$statusLabel, $statusColor, $statusIcon] = $statusMeta($supplier['status'] ?? '');
        [$paymentLabel, $paymentClass] = $paymentMeta($supplier['payment_status'] ?? '');
        $supplierCategories = array_values(array_filter(array_map('trim', explode(',', (string)($supplier['category_names'] ?? '')))));
        $initial = mb_strtoupper(mb_substr(trim((string)($supplier['shop_name'] ?? 'S')), 0, 1));
        $warningLevel = (int)($supplier['warning_level'] ?? 0);
        ?>
        <article class="sd-row" style="--status-color:<?= $h($statusColor) ?>">
          <div class="sd-business">
            <span class="sd-avatar"><?= $h($initial) ?></span>
            <div class="sd-business-copy">
              <div class="sd-name"><?= $h($supplier['shop_name'] ?? 'Supplier') ?></div>
              <div class="sd-owner"><?= $h($supplier['owner_name'] ?? 'Unknown owner') ?> · <?= $h($supplier['owner_email'] ?? 'No email') ?></div>
            </div>
          </div>

          <div class="sd-category">
            <?php if (empty($supplierCategories)): ?><span class="sd-chip">Uncategorized</span><?php endif; ?>
            <?php foreach (array_slice($supplierCategories, 0, 2) as $category): ?><span class="sd-chip"><?= $h($category) ?></span><?php endforeach; ?>
            <?php if (count($supplierCategories) > 2): ?><span class="sd-chip sd-chip-more">+<?= count($supplierCategories) - 2 ?></span><?php endif; ?>
          </div>

          <div class="sd-signals">
            <div class="sd-signal"><span class="sd-signal-label">Bookings</span><strong class="sd-signal-value"><?= number_format((int)($supplier['booking_count'] ?? 0)) ?></strong></div>
            <div class="sd-signal"><span class="sd-signal-label">Rating</span><strong class="sd-signal-value"><?= (int)($supplier['review_count'] ?? 0) > 0 ? number_format((float)$supplier['avg_rating'], 1) . ' ★' : 'New' ?></strong></div>
            <div class="sd-signal"><span class="sd-signal-label">Payment</span><strong class="sd-signal-value"><?= $h($paymentLabel) ?></strong></div>
          </div>

          <div class="sd-state">
            <span class="sd-badge"><i data-lucide="<?= $h($statusIcon) ?>"></i><?= $h($statusLabel) ?></span>
            <span class="sd-payment <?= $h($paymentClass) ?>"><?= $h($paymentLabel) ?></span>
            <?php if ($warningLevel > 0): ?><span class="sd-warning">Warning level <?= $warningLevel ?></span><?php endif; ?>
            <a class="sd-action" href="<?= URLROOT ?>/admin/supplier/<?= (int)$supplier['supplier_id'] ?>">
              <?= strtolower((string)($supplier['status'] ?? '')) === 'pending' ? 'Review' : 'Manage' ?>
              <i data-lucide="arrow-up-right"></i>
            </a>
          </div>
        </article>
      <?php endforeach; ?>
    </section>
  <?php endif; ?>

  <?php
  $baseParams = http_build_query(array_filter([
      'status' => $status,
      'search' => $search,
      'category' => $categoryId > 0 ? $categoryId : '',
      'payment' => $paymentStatus !== 'all' ? $paymentStatus : '',
  ], static fn($value) => $value !== ''));
  require APPROOT . '/views/partials/_pagination.php';
  ?>
</div>
<?php
};
?>
<!DOCTYPE html>
<html lang="en">
<head><?php require_once APPROOT . '/views/dashboardLayout/head.php'; ?></head>
<body class="grid h-screen gap-0 bg-app-page" style="grid-template-columns:280px 1fr">
  <?php require APPROOT . '/views/dashboardLayout/adminsidebar.php'; ?>
</body>
</html>
