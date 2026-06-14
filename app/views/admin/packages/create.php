<?php
$categories = $categories ?? [];
$serviceOptions = $serviceOptions ?? [];
$message = $message ?? '';

$dashboardTitle = 'Packages';
$dashboardCrumb = 'New Package';
$dashboardBreadcrumbs = [
  ['label' => 'Dashboard', 'url' => URLROOT . '/admin/dashboard'],
  ['label' => 'Packages', 'url' => URLROOT . '/admin/packages'],
  ['label' => 'New Package', 'url' => null],
];
$dashboardContentClass = 'admin-pkg-create';

$dashboardContent = function () use ($categories, $serviceOptions, $message) {
  $h = fn($value) => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
  $money = fn($value) => 'MMK ' . number_format((float)$value, 0);
  $servicesByCategory = [];
  foreach ($serviceOptions as $service) {
    $categoryName = trim((string)($service['category_name'] ?? 'Other'));
    $servicesByCategory[$categoryName][] = $service;
  }
?>
<style>
  .admin-pkg-create{min-height:100%;background:#FBFBF9;padding:28px 32px;font-family:'DM Sans',system-ui,-apple-system,sans-serif;color:#111827;font-size:13px}
  .admin-pkg-page *{box-sizing:border-box}
  .admin-pkg-page{--bg:#FBFBF9;--surface:#ffffff;--soft:#faf5ef;--hover:#eddecc;--border:#ead8c7;--border-light:#eddecc;--primary:#6d4c5b;--primary-hover:#7b5c69;--primary-soft:#eddecc;--text:#111827;--muted:#b79c8b;--body:#7b5c69;max-width:800px;margin:0 auto}

  .back-link{display:inline-flex;align-items:center;gap:6px;color:var(--muted);font-size:12px;font-weight:600;text-decoration:none;margin-bottom:16px}
  .back-link:hover{color:var(--primary)}

  .card{background:var(--surface);border:1px solid var(--border);border-radius:.75rem;padding:24px;margin-bottom:20px}
  .card-title{font-size:14px;font-weight:700;color:var(--text);margin:0 0 16px;padding-bottom:12px;border-bottom:1px solid var(--border-light)}

  .field{margin-bottom:16px}
  .field label{display:block;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--muted);margin-bottom:4px}

  .two-col{display:grid;grid-template-columns:1fr 1fr;gap:16px}

  .flash{border:1px solid var(--border);border-radius:.75rem;background:var(--surface);padding:12px 14px;margin-bottom:18px;color:var(--body);font-size:13px;font-weight:600}

  .btn-primary{display:inline-flex;align-items:center;gap:6px;padding:0 18px;height:36px;border:none;border-radius:.75rem;background:var(--primary);color:#fff;font-size:12px;font-weight:700;font-family:inherit;cursor:pointer;transition:background .12s;text-decoration:none}
  .btn-primary:hover{background:var(--primary-hover)}

  input,textarea,select{width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:.5rem;background:var(--bg);color:var(--text);font-size:13px;font-family:inherit;outline:none;transition:border-color .12s}
  input:focus,textarea:focus,select:focus{border-color:var(--primary)}
  textarea{min-height:80px;resize:vertical}
  input[type=number]{width:140px}
  select{width:100%}

  .cat-checklist{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:8px;padding:8px 0}
  .cat-option{display:flex;align-items:center;gap:8px;padding:8px 12px;border:1px solid var(--border-light);border-radius:.5rem;cursor:pointer;transition:all .12s}
  .cat-option:hover{background:var(--soft)}
  .cat-option input[type=checkbox]{width:auto;margin:0}
  .cat-option.selected{background:var(--primary-soft);border-color:var(--primary)}
  .service-select{min-height:220px}
  .hint{font-size:12px;color:var(--muted);margin:0 0 10px;line-height:1.5}

  .toggle-wrap{display:flex;align-items:center;gap:10px}
  .toggle{position:relative;width:40px;height:22px;border-radius:999px;border:none;cursor:pointer;transition:background .2s}
  .toggle.on{background:var(--primary)}
  .toggle.off{background:var(--border)}
  .toggle::after{content:'';position:absolute;top:2px;left:2px;width:18px;height:18px;border-radius:50%;background:#fff;transition:transform .2s}
  .toggle.on::after{transform:translateX(18px)}
</style>
<div class="admin-pkg-page">
  <a class="back-link" href="<?= URLROOT ?>/admin/packages">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
    Back to Packages
  </a>

  <?php if ($message !== ''): ?>
    <div class="flash"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <form method="POST" action="<?= URLROOT ?>/admin/packageCreate" enctype="multipart/form-data">
    <div class="card">
      <div class="card-title">Package Details</div>

      <div class="two-col">
        <div class="field">
          <label>Name *</label>
          <input type="text" name="name" required placeholder="e.g. Standard Complete Wedding">
        </div>
        <div class="field">
          <label>Slug</label>
          <input type="text" name="slug" placeholder="leave blank to auto-generate">
        </div>
      </div>

      <div class="field">
        <label>Tagline</label>
        <input type="text" name="tagline" placeholder="Short, compelling one-liner">
      </div>

      <div class="field">
        <label>Description</label>
        <textarea name="description" placeholder="Describe the complete wedding services included in this package..."></textarea>
      </div>

 

      <div class="field">
        <label>Package Image</label>
        <input type="file" name="package_image" accept="image/jpeg,image/png,image/webp">
        <p class="hint" style="margin-top:6px">Upload JPG, PNG, or WebP. Max size 6MB.</p>
      </div>

      <div class="field">
        <div class="toggle-wrap" style="justify-content:space-between">
          <label style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--muted)">Active</label>
          <button type="button" class="toggle on" onclick="this.classList.toggle('on');this.classList.toggle('off');document.getElementById('is_active').value=this.classList.contains('on')?1:0"></button>
          <input type="hidden" name="is_active" id="is_active" value="1">
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-title">Starting Services</div>
      <p class="hint">Optional. Choose actual supplier services that should be included immediately. You can add or remove more services after creating the package.</p>
      <?php if (empty($serviceOptions)): ?>
        <p class="hint">No approved supplier services are available yet.</p>
      <?php else: ?>
        <div class="field">
          <label>Food Guest Count</label>
          <input type="number" name="guest_count" min="1" step="1" value="100">
          <p class="hint" style="margin-top:6px">Used only for selected Food/Catering services. Other services stay fixed.</p>
        </div>
        <select class="service-select" name="service_ids[]" multiple>
          <?php foreach ($servicesByCategory as $categoryName => $services): ?>
            <optgroup label="<?= $h($categoryName) ?>">
              <?php foreach ($services as $service):
                $isFoodService = strpos(strtolower((string)(($service['category_slug'] ?? '') . ' ' . ($service['category_name'] ?? ''))), 'food') !== false
                  || strpos(strtolower((string)(($service['category_slug'] ?? '') . ' ' . ($service['category_name'] ?? ''))), 'cater') !== false;
                $label = ($service['name'] ?? 'Service')
                  . ' - ' . ($service['supplier_name'] ?? 'Supplier')
                  . ' - ' . $money($service['display_price'] ?? 0)
                  . ($isFoodService ? ' per guest' : '');
              ?>
                <option value="<?= (int)($service['id'] ?? 0) ?>"><?= $h($label) ?></option>
              <?php endforeach; ?>
            </optgroup>
          <?php endforeach; ?>
        </select>
      <?php endif; ?>
    </div>

    <div style="display:flex;gap:8px">
      <button class="btn-primary" type="submit">Create Package</button>
      <a class="btn-ghost" href="<?= URLROOT ?>/admin/packages" style="display:inline-flex;align-items:center;gap:6px;padding:0 14px;height:36px;border:1px solid var(--border);border-radius:.75rem;background:var(--surface);color:var(--body);font-size:12px;font-weight:700;font-family:inherit;text-decoration:none;cursor:pointer">Cancel</a>
    </div>
  </form>
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
