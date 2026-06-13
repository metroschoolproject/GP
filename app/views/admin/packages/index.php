<?php
$packages = $packages ?? [];
$total = $total ?? 0;
$page = $page ?? 1;
$totalPages = $totalPages ?? 1;
$filters = $filters ?? ['search' => '', 'status' => ''];
$message = $message ?? '';

$dashboardTitle = 'Packages';
$dashboardCrumb = 'All Packages';
$dashboardContentClass = 'admin-pkg-outlet';

$dashboardContent = function () use ($packages, $total, $page, $totalPages, $filters, $message) {
?>
<style>
  .admin-pkg-outlet{min-height:100%;background:#FBFBF9;padding:28px 32px;font-family:'DM Sans',system-ui,-apple-system,sans-serif;color:#111827;font-size:13px}
  .admin-pkg-page *{box-sizing:border-box}
  .admin-pkg-page{--bg:#FBFBF9;--surface:#ffffff;--soft:#faf5ef;--hover:#eddecc;--border:#ead8c7;--border-light:#eddecc;--primary:#6d4c5b;--primary-hover:#7b5c69;--primary-soft:#eddecc;--text:#111827;--muted:#b79c8b;--body:#7b5c69;--danger:#991b1b;--danger-bg:#fee2e2;max-width:1600px;margin:0 auto}

  .page-header{display:flex;align-items:flex-end;justify-content:space-between;margin-bottom:22px}
  .admin-pkg-page h1{font-size:22px;font-weight:700;color:var(--text);letter-spacing:-.3px;margin:0}

  .btn-primary{display:inline-flex;align-items:center;gap:6px;padding:0 18px;height:36px;border:none;border-radius:.75rem;background:var(--primary);color:#fff;font-size:12px;font-weight:700;font-family:inherit;cursor:pointer;transition:background .12s;text-decoration:none}
  .btn-primary:hover{background:var(--primary-hover)}
  .btn-ghost{display:inline-flex;align-items:center;gap:6px;padding:0 14px;height:34px;border:1px solid var(--border);border-radius:.75rem;background:var(--surface);color:var(--primary);font-size:12px;font-weight:700;font-family:inherit;cursor:pointer;transition:background .12s;text-decoration:none}
  .btn-ghost:hover{background:var(--primary-soft)}
  .btn-sm{height:30px;padding:0 12px;font-size:11px}
  .btn-danger{border-color:var(--danger);color:var(--danger);background:var(--surface)}
  .btn-danger:hover{background:var(--danger-bg)}

  .toolbar{display:flex;align-items:center;gap:8px;margin-bottom:20px;flex-wrap:wrap}
  .search-input{height:36px;padding:0 12px;border:1px solid var(--border);border-radius:.75rem;background:var(--surface);color:var(--text);font-size:12px;font-family:inherit;flex:1;min-width:200px;max-width:320px;outline:none;transition:border-color .12s}
  .search-input:focus{border-color:var(--primary)}
  .filters{display:flex;gap:6px;flex-wrap:wrap}
  .filter{display:inline-flex;align-items:center;height:34px;padding:0 14px;border:1px solid var(--border);border-radius:.75rem;background:var(--soft);color:var(--body);font-size:12px;font-weight:700;font-family:inherit;cursor:pointer;transition:all .12s;white-space:nowrap;text-decoration:none}
  .filter:hover{border-color:var(--border);background:var(--hover);color:var(--primary)}
  .filter.active{border-color:var(--primary);background:var(--primary);color:#fff}

  .flash{border:1px solid var(--border);border-radius:.75rem;background:var(--surface);padding:12px 14px;margin-bottom:18px;color:var(--body);font-size:13px;font-weight:600}

  .pkg-table{width:100%;border-collapse:separate;border-spacing:0;background:var(--surface);border:1px solid var(--border);border-radius:.75rem;overflow:hidden}
  .pkg-table th{padding:12px 14px;text-align:left;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--muted);background:var(--soft);border-bottom:1px solid var(--border)}
  .pkg-table td{padding:12px 14px;border-bottom:1px solid var(--border-light);font-size:13px;color:var(--text);vertical-align:middle}
  .pkg-table tr:last-child td{border-bottom:none}
  .pkg-table tr:hover td{background:var(--soft)}

  .badge{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:999px;font-size:11px;font-weight:700}
  .badge-active{background:#d1fae5;color:#065f46}
  .badge-inactive{background:#fee2e2;color:#991b1b}
  .badge-neutral{background:#f3f4f6;color:#57534e}

  .pagination{display:flex;align-items:center;justify-content:center;gap:6px;margin-top:20px}
  .page-link{padding:6px 12px;border:1px solid var(--border);border-radius:.5rem;background:var(--surface);color:var(--body);font-size:12px;font-weight:600;text-decoration:none;transition:all .12s}
  .page-link:hover{background:var(--soft);color:var(--primary)}
  .page-link.active{background:var(--primary);color:#fff;border-color:var(--primary)}
  .page-link.disabled{opacity:.4;pointer-events:none}

  .actions{display:flex;gap:4px}
  .pkg-name{font-weight:600;color:var(--primary);text-decoration:none}
  .pkg-name:hover{text-decoration:underline}
  .empty-state{padding:40px;text-align:center;color:var(--muted)}
  .empty-state h3{font-size:18px;color:var(--text);margin:0 0 8px}
</style>
<div class="admin-pkg-page">
  <div class="page-header">
    <div>
      <p class="eyebrow" style="font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--muted);margin-bottom:4px">Complete Wedding Packages</p>
      <h1>All Packages</h1>
    </div>
    <a class="btn-primary" href="<?= URLROOT ?>/admin/packageCreate">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      New Package
    </a>
  </div>

  <?php if ($message !== ''): ?>
    <div class="flash"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <div class="toolbar">
    <form method="GET" action="<?= URLROOT ?>/admin/packages" style="display:flex;gap:8px;flex:1;align-items:center">
      <input class="search-input" type="search" name="search" placeholder="Search packages..." value="<?= htmlspecialchars($filters['search'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
      <div class="filters">
        <a class="filter <?= ($filters['status'] ?? '') === '' ? 'active' : '' ?>" href="<?= URLROOT ?>/admin/packages<?= $filters['search'] ? '?search=' . urlencode($filters['search']) : '' ?>">All</a>
        <a class="filter <?= ($filters['status'] ?? '') === 'active' ? 'active' : '' ?>" href="<?= URLROOT ?>/admin/packages?status=active<?= $filters['search'] ? '&search=' . urlencode($filters['search']) : '' ?>">Active</a>
        <a class="filter <?= ($filters['status'] ?? '') === 'inactive' ? 'active' : '' ?>" href="<?= URLROOT ?>/admin/packages?status=inactive<?= $filters['search'] ? '&search=' . urlencode($filters['search']) : '' ?>">Inactive</a>
      </div>
      <?php if ($filters['search'] !== ''): ?>
        <a class="btn-ghost btn-sm" href="<?= URLROOT ?>/admin/packages">Clear</a>
      <?php endif; ?>
    </form>
  </div>

  <?php if (empty($packages)): ?>
    <div class="empty-state" style="background:var(--surface);border:1px solid var(--border);border-radius:.75rem;">
      <h3>No packages found</h3>
      <p style="color:var(--muted);margin-bottom:16px">No wedding package types match your criteria.</p>
      <a class="btn-primary" href="<?= URLROOT ?>/admin/packageCreate">Create your first package</a>
    </div>
  <?php else: ?>
    <table class="pkg-table">
      <thead>
        <tr>
          <th style="width:30px">#</th>
          <th>Name</th>
          <th>Slug</th>
          <th>Base Price</th>
          <th>Services</th>
          <th>Status</th>
          <th>Order</th>
          <th style="width:100px">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($packages as $pkg): ?>
          <tr>
            <td style="color:var(--muted);font-weight:600"><?= (int)$pkg['package_id'] ?></td>
            <td><a class="pkg-name" href="<?= URLROOT ?>/admin/packageDetail/<?= (int)$pkg['package_id'] ?>"><?= htmlspecialchars($pkg['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></a></td>
            <td style="color:var(--muted);font-size:12px"><?= htmlspecialchars($pkg['slug'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
            <td><strong>MMK <?= number_format((float)($pkg['base_price'] ?? 0), 0) ?></strong></td>
            <td><span class="badge badge-neutral"><?= (int)($pkg['item_count'] ?? 0) ?> svc.</span></td>
            <td>
              <?php if (!empty($pkg['is_active'])): ?>
                <span class="badge badge-active">● Active</span>
              <?php else: ?>
                <span class="badge badge-inactive">● Inactive</span>
              <?php endif; ?>
            </td>
            <td style="color:var(--muted)"><?= (int)$pkg['sort_order'] ?></td>
            <td>
              <div class="actions">
                <a class="btn-ghost btn-sm" href="<?= URLROOT ?>/admin/packageDetail/<?= (int)$pkg['package_id'] ?>">Edit</a>
                <form method="POST" action="<?= URLROOT ?>/admin/packageDelete/<?= (int)$pkg['package_id'] ?>" onsubmit="return confirm('Delete this package type?')" style="display:inline">
                  <button class="btn-ghost btn-sm btn-danger" type="submit">Del</button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <?php if ($totalPages > 1): ?>
      <div class="pagination">
        <a class="page-link <?= $page <= 1 ? 'disabled' : '' ?>" href="<?= URLROOT ?>/admin/packages?page=<?= $page - 1 ?>&status=<?= urlencode($filters['status'] ?? '') ?>&search=<?= urlencode($filters['search'] ?? '') ?>">‹ Prev</a>
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
          <a class="page-link <?= $i === $page ? 'active' : '' ?>" href="<?= URLROOT ?>/admin/packages?page=<?= $i ?>&status=<?= urlencode($filters['status'] ?? '') ?>&search=<?= urlencode($filters['search'] ?? '') ?>"><?= $i ?></a>
        <?php endfor; ?>
        <a class="page-link <?= $page >= $totalPages ? 'disabled' : '' ?>" href="<?= URLROOT ?>/admin/packages?page=<?= $page + 1 ?>&status=<?= urlencode($filters['status'] ?? '') ?>&search=<?= urlencode($filters['search'] ?? '') ?>">Next ›</a>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>
<?php
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php require_once APPROOT . '/views/dashboardLayout/head.php'; ?>
</head>
<body class="grid h-screen gap-0 bg-app-page" style="grid-template-columns: 280px 1fr;">
    <?php require_once APPROOT . '/views/dashboardLayout/sidebar.php'; ?>
</body>
</html>
