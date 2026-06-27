<?php
$categories = $categories ?? [];
$stats = $stats ?? ['total' => 0];
$search = $search ?? '';
$message = $message ?? '';

$dashboardTitle = 'Categories';
$dashboardCrumb = 'Manage Categories';
$dashboardBreadcrumbs = [
    ['label' => 'Dashboard', 'url' => URLROOT . '/admin/dashboard'],
    ['label' => 'Categories', 'url' => null],
];
$dashboardContentClass = 'admin-cat-outlet';

$dashboardContent = function () use ($categories, $stats, $search, $message) {
?>
<style>
  .admin-cat-outlet{min-height:100%;background:#F4F1EE;padding:28px 32px;font-size:13.5px;overflow-y:auto}
  .admin-cat-page *{box-sizing:border-box}
  .admin-cat-page{--bg:#F4F1EE;--surface:#FFFFFF;--soft:#FFFFFF;--hover:#eddecc;--border:#ead8c7;--border-light:#eddecc;--primary:#6d4c5b;--primary-hover:#7b5c69;--primary-soft:#eddecc;--text:#111827;--muted:#b79c8b;--body:#7b5c69;--danger:#991B1B;--danger-bg:#FEF2F2;--success:#065F46;--success-bg:#ECFDF5;max-width:1600px;margin:0 auto}

  .page-header{display:flex;align-items:flex-end;justify-content:space-between;margin-bottom:22px}
  .admin-cat-page h1{font-size:22px;font-weight:700;color:var(--text);letter-spacing:-.3px;margin:0}

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

  /* Toast notification — bottom-right */
  .cat-toast{position:fixed;bottom:24px;right:24px;z-index:9999;display:flex;align-items:center;gap:10px;padding:14px 20px;border-radius:12px;font-size:13px;font-weight:600;font-family:'DM Sans',system-ui,sans-serif;box-shadow:0 8px 30px rgba(52,35,43,.22);transform:translateX(120%);transition:transform .35s cubic-bezier(.4,0,.2,1),opacity .35s ease;pointer-events:none;opacity:0;max-width:380px}
  .cat-toast.show{transform:translateX(0);opacity:1;pointer-events:auto}
  .cat-toast-success{background:#166534;color:#fff}
  .cat-toast-error{background:#991B1B;color:#fff}
  .cat-toast-icon{flex:0 0 auto;display:inline-flex}
  .cat-toast-close{border:0;background:transparent;color:rgba(255,255,255,.7);cursor:pointer;display:inline-flex;padding:2px;margin-left:4px;flex:0 0 auto}
  .cat-toast-close:hover{color:#fff}

  .cat-table{width:100%;border-collapse:separate;border-spacing:0;background:var(--surface);border:1px solid var(--border);border-radius:.75rem;overflow:hidden}
  .cat-table th{padding:9px 20px;text-align:left;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.1em;color:var(--muted);background:var(--soft);border-bottom:1px solid var(--border)}
  .cat-table td{padding:13px 20px;border-bottom:1px solid var(--border-light);font-size:13px;color:var(--text);vertical-align:middle}
  .cat-table tr:last-child td{border-bottom:none}
  .cat-table tr:hover td{background:var(--soft)}

  .badge{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:999px;font-size:11px;font-weight:700}
  .badge-neutral{background:#F5F5F4;color:#78716C}

  .actions{display:flex;gap:4px}
  .cat-name{font-weight:600;color:var(--primary)}
  .cat-slug{font-size:11px;color:var(--muted);font-family:'SF Mono',SFMono-Regular,Consolas,monospace}
  .cat-count{font-weight:600;color:var(--text)}
  .cat-count-zero{color:var(--muted);font-weight:400}
  .cat-date{font-size:11px;color:var(--muted)}
  .empty-state{padding:40px;text-align:center;color:var(--muted)}
  .empty-state h3{font-size:18px;color:var(--text);margin:0 0 8px}

  /* Modals — matched to customer_detail pattern */
  .cat-modal{position:fixed;inset:0;z-index:80;display:none;align-items:center;justify-content:center;padding:20px;background:rgba(52,35,43,.45);backdrop-filter:blur(2px)}
  .cat-modal.open{display:flex}
  .cat-modal-box{width:100%;max-width:440px;border-radius:16px;background:#fff;box-shadow:0 30px 70px rgba(52,35,43,.25);overflow:hidden}
  .cat-modal-head{display:flex;align-items:center;justify-content:space-between;padding:15px 18px;border-bottom:1px solid #ead8c7}
  .cat-modal-title{margin:0;font-size:13px;font-weight:800;color:#6d4c5b}
  .cat-modal-close{border:0;background:transparent;color:#a58b96;cursor:pointer;display:inline-flex;padding:4px;border-radius:6px}
  .cat-modal-close:hover{background:#FFFFFF;color:#6d4c5b}
  .cat-modal-body{padding:18px}
  .cat-field{margin-bottom:13px}
  .cat-field label{display:block;margin-bottom:5px;color:#7b5c69;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.05em}
  .cat-field input{width:100%;box-sizing:border-box;border:1px solid #e4d2c3;border-radius:9px;padding:9px 11px;font:500 12px Inter,sans-serif;color:#6d4c5b;outline:none;transition:border-color .12s}
  .cat-field input:focus{border-color:#6d4c5b}
  .cat-field .hint{font-size:11px;color:#a58b96;margin-top:6px}
  .cat-modal-foot{display:flex;justify-content:flex-end;gap:9px;margin-top:4px}
  .cat-btn{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:40px;border-radius:10px;padding:0 14px;font-size:11px;font-weight:800;cursor:pointer;border:1px solid transparent;text-decoration:none;font-family:inherit;transition:background .12s}
  .cat-btn svg{width:14px;height:14px}
  .cat-btn-edit{border-color:#ddc8b9;background:#fff;color:#6d4c5b}
  .cat-btn-edit:hover{background:#FFFFFF}
  .cat-btn-ok{border-color:#bcdcc8;background:#edf7f1;color:#3c6b51}
  .cat-btn-ok:hover{background:#ddeee3}
  .cat-btn-danger{border-color:#e4b4b4;background:#fbeaea;color:#a23a3a}
  .cat-btn-danger:hover{background:#f6d8d8}
  .cat-modal-warning{font-size:12px;color:#7b5c69;line-height:1.5;margin-bottom:4px}
  .cat-modal-warning strong{color:#a23a3a}
</style>

<div class="admin-cat-page">
  <div class="page-header">
    <div>
      <p class="eyebrow" style="font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--muted);margin-bottom:4px">Wedding Service Categories</p>
      <h1>Categories</h1>
    </div>
    <button type="button" class="btn-primary" id="addCatBtn">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Add Category
    </button>
  </div>

  <?php if ($message !== ''): ?>
    <div id="catFlashMsg" data-message="<?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>" data-type="<?= (strpos($message, 'success') !== false || strpos($message, 'created') !== false || strpos($message, 'updated') !== false || strpos($message, 'deleted') !== false) ? 'success' : 'error' ?>" style="display:none"></div>
  <?php endif; ?>

  <div class="toolbar">
    <form method="get" action="<?= URLROOT ?>/admin/categories" style="display:flex;gap:8px;flex:1;max-width:320px">
      <input type="text" name="search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>" placeholder="Search categories..." class="search-input">
      <?php if ($search !== ''): ?>
        <a href="<?= URLROOT ?>/admin/categories" class="btn-ghost btn-sm">Clear</a>
      <?php endif; ?>
    </form>
    <span style="font-size:12px;color:var(--muted);margin-left:auto"><?= $stats['total'] ?> total categories</span>
  </div>

  <?php if (empty($categories)): ?>
    <div class="empty-state">
      <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin:0 auto 12px;display:block;color:var(--muted)"><path d="M12 2 2 7l10 5 10-5-10-5Z"/><path d="m2 17 10 5 10-5"/><path d="m2 12 10 5 10-5"/></svg>
      <h3>No categories found</h3>
      <p><?= $search !== '' ? 'Try a different search term.' : 'Get started by adding your first category.' ?></p>
    </div>
  <?php else: ?>
    <table class="cat-table">
      <thead>
        <tr>
          <th>Category Name</th>
          <th>Slug</th>
          <th>Suppliers</th>
          <th>Services</th>
          <th>Created</th>
          <th style="text-align:right">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($categories as $cat): ?>
        <tr>
          <td><span class="cat-name"><?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?></span></td>
          <td><span class="cat-slug"><?= htmlspecialchars($cat['slug'], ENT_QUOTES, 'UTF-8') ?></span></td>
          <td><span class="badge badge-neutral"><?= $cat['supplier_count'] ?></span></td>
          <td><span class="badge badge-neutral"><?= $cat['service_count'] ?></span></td>
          <td><span class="cat-date"><?= date('M j, Y', strtotime($cat['created_at'])) ?></span></td>
          <td>
            <div class="actions" style="justify-content:flex-end">
              <button type="button" class="btn-ghost btn-sm edit-cat-btn"
                      data-id="<?= (int)$cat['id'] ?>"
                      data-name="<?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?>">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                Edit
              </button>
              <button type="button" class="btn-ghost btn-sm btn-danger delete-cat-btn"
                      data-id="<?= (int)$cat['id'] ?>"
                      data-name="<?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?>"
                      data-suppliers="<?= $cat['supplier_count'] ?>"
                      data-services="<?= $cat['service_count'] ?>">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                Delete
              </button>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<!-- Add Category Modal -->
<div class="cat-modal" id="addCatModal">
  <div class="cat-modal-box">
    <div class="cat-modal-head">
      <h3 class="cat-modal-title">Add New Category</h3>
      <button type="button" class="cat-modal-close close-modal-btn"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
    </div>
    <form method="post" action="<?= URLROOT ?>/admin/categoryCreate">
      <?= csrf_field() ?>
      <div class="cat-modal-body">
        <div class="cat-field">
          <label for="addCatName">Category Name</label>
          <input type="text" id="addCatName" name="name" maxlength="100" placeholder="e.g. Photography" required>
          <p class="hint">A URL-friendly slug will be generated automatically.</p>
        </div>
      </div>
      <div class="cat-modal-body" style="padding-top:0">
        <div class="cat-modal-foot">
          <button type="button" class="cat-btn cat-btn-edit close-modal-btn">Cancel</button>
          <button type="submit" class="cat-btn cat-btn-ok"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Create Category</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Edit Category Modal -->
<div class="cat-modal" id="editCatModal">
  <div class="cat-modal-box">
    <div class="cat-modal-head">
      <h3 class="cat-modal-title">Edit Category</h3>
      <button type="button" class="cat-modal-close close-modal-btn"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
    </div>
    <form method="post" id="editCatForm" action="">
      <?= csrf_field() ?>
      <div class="cat-modal-body">
        <div class="cat-field">
          <label for="editCatName">Category Name</label>
          <input type="text" id="editCatName" name="name" maxlength="100" required>
          <p class="hint">The URL slug will be updated automatically.</p>
        </div>
      </div>
      <div class="cat-modal-body" style="padding-top:0">
        <div class="cat-modal-foot">
          <button type="button" class="cat-btn cat-btn-edit close-modal-btn">Cancel</button>
          <button type="submit" class="cat-btn cat-btn-ok"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg> Save Changes</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Delete Category Modal -->
<div class="cat-modal" id="deleteCatModal">
  <div class="cat-modal-box">
    <div class="cat-modal-head">
      <h3 class="cat-modal-title">Delete Category</h3>
      <button type="button" class="cat-modal-close close-modal-btn"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
    </div>
    <form method="post" id="deleteCatForm" action="">
      <?= csrf_field() ?>
      <div class="cat-modal-body">
        <p class="cat-modal-warning">Are you sure you want to delete <strong id="deleteCatName"></strong>?</p>
        <p class="cat-modal-warning" id="deleteCatWarning" style="display:none"></p>
      </div>
      <div class="cat-modal-body" style="padding-top:0">
        <div class="cat-modal-foot">
          <button type="button" class="cat-btn cat-btn-edit close-modal-btn">Cancel</button>
          <button type="submit" class="cat-btn cat-btn-danger"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg> Delete Category</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Toast container -->
<div id="catToast" class="cat-toast">
  <span class="cat-toast-icon"></span>
  <span class="cat-toast-text"></span>
  <button type="button" class="cat-toast-close" aria-label="Close"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
</div>

<script>
(() => {
  const addModal = document.getElementById('addCatModal');
  const editModal = document.getElementById('editCatModal');
  const deleteModal = document.getElementById('deleteCatModal');

  // ── Toast ──────────────────────────────────────────────────
  const toast = document.getElementById('catToast');
  let toastTimer;

  function showCatToast(message, type) {
    type = type || 'success';
    toast.className = 'cat-toast cat-toast-' + type;
    toast.querySelector('.cat-toast-icon').innerHTML = type === 'success'
      ? '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>'
      : '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>';
    toast.querySelector('.cat-toast-text').textContent = message;
    clearTimeout(toastTimer);
    requestAnimationFrame(() => toast.classList.add('show'));
    toastTimer = setTimeout(() => toast.classList.remove('show'), 4000);
  }

  toast.querySelector('.cat-toast-close').addEventListener('click', () => {
    clearTimeout(toastTimer);
    toast.classList.remove('show');
  });

  // Show flash message as toast on page load
  const flashEl = document.getElementById('catFlashMsg');
  if (flashEl) {
    showCatToast(flashEl.dataset.message, flashEl.dataset.type);
    flashEl.remove();
  }

  function openModal(overlay) {
    overlay.classList.add('open');
    const input = overlay.querySelector('input[type="text"]');
    if (input) setTimeout(() => input.focus(), 80);
  }

  function closeModal(overlay) {
    overlay.classList.remove('open');
  }

  document.getElementById('addCatBtn')?.addEventListener('click', () => openModal(addModal));

  document.querySelectorAll('.edit-cat-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const id = btn.dataset.id;
      const name = btn.dataset.name;
      document.getElementById('editCatForm').action = '<?= URLROOT ?>/admin/categoryUpdate/' + id;
      document.getElementById('editCatName').value = name;
      openModal(editModal);
    });
  });

  document.querySelectorAll('.delete-cat-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const id = btn.dataset.id;
      const name = btn.dataset.name;
      const suppliers = parseInt(btn.dataset.suppliers, 10);
      const services = parseInt(btn.dataset.services, 10);
      document.getElementById('deleteCatForm').action = '<?= URLROOT ?>/admin/categoryDelete/' + id;
      document.getElementById('deleteCatName').textContent = name;
      const warning = document.getElementById('deleteCatWarning');
      if (suppliers > 0 || services > 0) {
        warning.style.display = 'block';
        warning.innerHTML = '<strong>Warning:</strong> This category is used by ' + suppliers + ' supplier(s) and ' + services + ' service(s). Deletion will fail until those are removed.';
      } else {
        warning.style.display = 'none';
      }
      openModal(deleteModal);
    });
  });

  document.querySelectorAll('.close-modal-btn').forEach(btn => {
    btn.addEventListener('click', () => closeModal(btn.closest('.cat-modal')));
  });

  document.querySelectorAll('.cat-modal').forEach(overlay => {
    overlay.addEventListener('click', (e) => {
      if (e.target === overlay) closeModal(overlay);
    });
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      [addModal, editModal, deleteModal].forEach(m => closeModal(m));
    }
  });
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
