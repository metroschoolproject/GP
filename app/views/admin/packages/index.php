<?php
$packages = $packages ?? [];
$total = $total ?? 0;
$page = $page ?? 1;
$totalPages = $totalPages ?? 1;
$filters = $filters ?? ['search' => '', 'status' => ''];
$message = $message ?? '';

$dashboardTitle = 'Packages';
$dashboardCrumb = 'All Packages';
$dashboardBreadcrumbs = [
  ['label' => 'Dashboard', 'url' => URLROOT . '/admin/dashboard'],
  ['label' => 'Packages', 'url' => null],
];
$dashboardContentClass = 'admin-pkg-outlet';

$dashboardContent = function () use ($packages, $total, $page, $totalPages, $filters, $message) {
  $agentFeeRate = get_platform_fee_percent() / 100;
?>
<style>
  .admin-pkg-outlet{min-height:100%;background:#F4F1EE;padding:28px 32px;font-size:13.5px;overflow-y:auto}
  .admin-pkg-page *{box-sizing:border-box}
  .admin-pkg-page{--bg:#F4F1EE;--surface:#FFFFFF;--soft:#FFFFFF;--hover:#eddecc;--border:#ead8c7;--border-light:#eddecc;--primary:#6d4c5b;--primary-hover:#7b5c69;--primary-soft:#eddecc;--text:#111827;--muted:#b79c8b;--body:#7b5c69;--danger:#991B1B;--danger-bg:#FEF2F2;max-width:1600px;margin:0 auto}

  .page-header{display:flex;align-items:flex-end;justify-content:space-between;margin-bottom:22px}
  .admin-pkg-page h1{font-size:22px;font-weight:700;color:var(--text);letter-spacing:-.3px;margin:0}

  .btn-primary{display:inline-flex;align-items:center;gap:6px;padding:0 18px;height:36px;border:none;border-radius:.75rem;background:var(--primary);color:#FFFFFF;font-size:12px;font-weight:700;font-family:inherit;cursor:pointer;transition:background .12s;text-decoration:none}
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
  .filter.active{border-color:var(--primary);background:var(--primary);color:#FFFFFF}

  .flash{border:1px solid var(--border);border-radius:.75rem;background:var(--surface);padding:12px 14px;margin-bottom:18px;color:var(--body);font-size:13px;font-weight:600}

  .pkg-table{width:100%;border-collapse:separate;border-spacing:0;background:var(--surface);border:1px solid var(--border);border-radius:.75rem;overflow:hidden}
  .pkg-table th{padding:9px 20px;text-align:left;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.1em;color:var(--muted);background:var(--soft);border-bottom:1px solid var(--border)}
  .pkg-table td{padding:13px 20px;border-bottom:1px solid var(--border-light);font-size:13px;color:var(--text);vertical-align:middle}
  .pkg-table tr:last-child td{border-bottom:none}
  .pkg-table tr:hover td{background:var(--soft)}

  .badge{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:999px;font-size:11px;font-weight:700}
  .badge-active{background:#ECFDF5;color:#065F46}
  .badge-inactive{background:#FEF2F2;color:#991B1B}
  .badge-draft{background:#fffbeb;color:#92400E}
  .badge-neutral{background:#F5F5F4;color:#78716C}

  .pagination{display:flex;align-items:center;justify-content:center;gap:6px;margin-top:20px}
  .page-link{padding:6px 12px;border:1px solid var(--border);border-radius:.5rem;background:var(--surface);color:var(--body);font-size:12px;font-weight:600;text-decoration:none;transition:all .12s}
  .page-link:hover{background:var(--soft);color:var(--primary)}
  .page-link.active{background:var(--primary);color:#FFFFFF;border-color:var(--primary)}
  .page-link.disabled{opacity:.4;pointer-events:none}

  .actions{display:flex;gap:4px}
  .pkg-name{font-weight:600;color:var(--primary);text-decoration:none}
  .pkg-name:hover{text-decoration:underline}
  .empty-state{padding:40px;text-align:center;color:var(--muted)}
  .empty-state h3{font-size:18px;color:var(--text);margin:0 0 8px}

  /* Delete confirmation modal — matches supplier_review modal pattern */
  .modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:100;align-items:center;justify-content:center;padding:20px}
  .modal-overlay.open{display:flex}
  .modal-box{background:#FFFFFF;border-radius:1rem;padding:24px;max-width:440px;width:100%;box-shadow:0 20px 60px rgba(0,0,0,.2)}
  .modal-box h3{font-size:16px;font-weight:700;margin:0 0 12px;color:var(--text)}
  .modal-box p{font-size:13px;color:var(--body);margin:0 0 16px;line-height:1.6}
  .modal-box strong{color:var(--text)}
  .pkg-modal-warn{display:flex;gap:10px;align-items:flex-start;margin:0 0 16px;padding:12px;border-radius:10px;background:var(--danger-bg);border:1px solid #e5c4c4;color:var(--danger);font-size:12px;font-weight:600;line-height:1.5}
  .pkg-modal-warn svg{flex-shrink:0;margin-top:1px}
  .modal-btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;min-height:38px;border:none;border-radius:.75rem;padding:0 14px;font-size:12px;font-weight:800;font-family:inherit;cursor:pointer;transition:background .12s,transform .12s}
  .modal-btn.btn-danger{background:var(--danger);color:#FFFFFF}
  .modal-btn.btn-danger:hover{background:#7f1d1d;transform:translateY(-1px)}
  .modal-btn.btn-outline{border:1px solid var(--border);background:var(--surface);color:var(--text)}
  .modal-btn.btn-outline:hover{background:var(--hover)}
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
        <a class="filter <?= ($filters['status'] ?? '') === 'draft' ? 'active' : '' ?>" href="<?= URLROOT ?>/admin/packages?status=draft<?= $filters['search'] ? '&search=' . urlencode($filters['search']) : '' ?>">Drafts</a>
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
          <th>Package Price</th>
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
            <td><strong>MMK <?= number_format((float)($pkg['base_price'] ?? 0) * (1 + $agentFeeRate), 0) ?></strong></td>
            <td><span class="badge badge-neutral"><?= (int)($pkg['item_count'] ?? 0) ?> svc.</span></td>
            <td>
              <?php if (($pkg['status'] ?? '') === 'draft'): ?>
                <span class="badge badge-draft">● Draft</span>
              <?php elseif (!empty($pkg['is_active'])): ?>
                <span class="badge badge-active">● Published</span>
              <?php else: ?>
                <span class="badge badge-inactive">● Inactive</span>
              <?php endif; ?>
            </td>
            <td style="color:var(--muted)"><?= (int)$pkg['sort_order'] ?></td>
            <td>
              <div class="actions">
                <a class="btn-ghost btn-sm" href="<?= URLROOT ?>/admin/packageDetail/<?= (int)$pkg['package_id'] ?>">Edit</a>
                <button class="btn-ghost btn-sm btn-danger delete-pkg-btn" type="button"
                        data-id="<?= (int)$pkg['package_id'] ?>"
                        data-name="<?= htmlspecialchars($pkg['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        data-status="<?= htmlspecialchars($pkg['status'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        data-has-bookings="<?= !empty($pkg['has_bookings']) ? '1' : '0' ?>">Del</button>
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

  <!-- Delete Confirmation Modal -->
  <div class="modal-overlay" id="modalDelete">
    <div class="modal-box">
      <h3 id="deleteModalTitle">Delete Package</h3>
      <p>Are you sure you want to delete <strong id="deletePkgName"></strong>?</p>
      <div class="pkg-modal-warn" id="deletePkgArchiveNote" style="display:none">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>
        <span>This package has existing bookings. It will be <strong>archived</strong> — hidden from all listings but booking history is preserved.</span>
      </div>
      <div class="pkg-modal-warn" id="deletePkgPermanentNote" style="display:none;background:#f0fdf4;border-color:#bbf7d0;color:#166534">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        <span>This package has no bookings and will be <strong>permanently deleted</strong> from the database.</span>
      </div>
      <form method="POST" id="deletePkgForm" action="">
        <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:16px">
          <button type="button" class="modal-btn btn-outline" style="width:auto" onclick="closeDeleteModal()">Cancel</button>
          <button type="submit" class="modal-btn btn-danger" id="deleteConfirmBtn" style="width:auto">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
            <span id="deleteConfirmLabel">Delete Package</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
<script>
(function() {
  var modal = document.getElementById('modalDelete');
  var form = document.getElementById('deletePkgForm');
  var nameEl = document.getElementById('deletePkgName');
  var archiveNote = document.getElementById('deletePkgArchiveNote');
  var permanentNote = document.getElementById('deletePkgPermanentNote');
  var confirmLabel = document.getElementById('deleteConfirmLabel');

  document.querySelectorAll('.delete-pkg-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
      var id = btn.dataset.id;
      var name = btn.dataset.name;
      var hasBookings = btn.dataset.hasBookings === '1';

      form.action = '<?= URLROOT ?>/admin/packageDelete/' + id;
      nameEl.textContent = name;

      archiveNote.style.display = hasBookings ? 'flex' : 'none';
      permanentNote.style.display = hasBookings ? 'none' : 'flex';
      confirmLabel.textContent = hasBookings ? 'Archive Package' : 'Delete Package';

      modal.classList.add('open');
    });
  });

  window.closeDeleteModal = function() { modal.classList.remove('open'); };
  modal.addEventListener('click', function(e) { if (e.target === modal) closeDeleteModal(); });
  document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeDeleteModal(); });
})();
</script>
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
