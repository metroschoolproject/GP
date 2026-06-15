<?php
$package = $package ?? null;
$message = $message ?? '';
$categories = $categories ?? [];
$serviceOptions = $serviceOptions ?? [];
$hallOptionsByService = $hallOptionsByService ?? [];

$dashboardTitle = 'Packages';
$dashboardCrumb = htmlspecialchars($package['name'] ?? 'Package Detail', ENT_QUOTES, 'UTF-8');
$dashboardBreadcrumbs = [
  ['label' => 'Dashboard', 'url' => URLROOT . '/admin/dashboard'],
  ['label' => 'Packages', 'url' => URLROOT . '/admin/packages'],
  ['label' => $package['name'] ?? 'Package Detail', 'url' => null],
];
$dashboardContentClass = 'admin-pkg-detail';

$dashboardContent = function () use ($package, $message, $categories, $serviceOptions, $hallOptionsByService) {
  $h = fn($value) => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
  $money = fn($value) => 'MMK ' . number_format((float)$value, 0);

  // ── Price calculations ────────────────────────────────────────────────────
  $includedTotal = 0;
  $includedServiceIds = [];
  $includedCategoryIds = [];
  $hasVenueItems = false;
  foreach (($package['items'] ?? []) as $item) {
    $includedTotal += (float)($item['default_price'] ?? $item['price_min'] ?? $item['price'] ?? 0);
    if (!empty($item['service_id'])) {
      $includedServiceIds[(int)$item['service_id']] = true;
    }
    if (!empty($item['category_id'])) {
      $includedCategoryIds[(int)$item['category_id']] = true;
    }
    if (!empty($item['venue_room_id']) || !empty($item['hall_name']) || !empty($hallOptionsByService[(int)($item['service_id'] ?? 0)])) {
      $hasVenueItems = true;
    }
  }
  $agentFeeRate    = 0.05;
  $agentFee        = $includedTotal * $agentFeeRate;
  $suggestedPrice  = $includedTotal + $agentFee;
  $storedBasePrice = (float)($package['base_price'] ?? 0);
  $packageBasePrice = $storedBasePrice > 0 ? $storedBasePrice : $includedTotal;
  $packageAgentFee  = $packageBasePrice * $agentFeeRate;
  $packagePrice     = $packageBasePrice + $packageAgentFee;
  $saving           = max(0, $suggestedPrice - $packagePrice);

  // ── Package category label ───────────────────────────────────────────────
  $pkgCategoryId   = (int)($package['category_id'] ?? 0);
  $pkgCategoryName = trim((string)($package['category_name'] ?? ''));
  $pkgCategorySlug = strtolower(trim((string)($package['category_slug'] ?? $pkgCategoryName)));
  $isVenuePackage  = str_contains($pkgCategorySlug, 'venue') || str_contains($pkgCategorySlug, 'hall');

  // One package may include many categories, but only one service per category.
  $addableServices = array_filter($serviceOptions, function ($svc) use ($includedServiceIds, $includedCategoryIds) {
    $svcId = (int)($svc['id'] ?? 0);
    if ($svcId > 0 && isset($includedServiceIds[$svcId])) {
      return false;
    }
    $categoryId = (int)($svc['category_id'] ?? 0);
    if ($categoryId > 0 && isset($includedCategoryIds[$categoryId])) {
      return false;
    }
    return true;
  });
  $addableServices = array_values($addableServices);
?>
<style>
/* ── Reset & base ─────────────────────────────────────────────────────── */
.admin-pkg-detail{min-height:100%;background:#FBFBF9;padding:28px 32px;font-family:'DM Sans',system-ui,-apple-system,sans-serif;color:#111827;font-size:13px}
.admin-pkg-page *{box-sizing:border-box}
.admin-pkg-page{
  --bg:#FBFBF9;--surface:#ffffff;--soft:#faf5ef;--hover:#eddecc;
  --border:#ead8c7;--border-light:#eddecc;
  --primary:#6d4c5b;--primary-hover:#7b5c69;--primary-soft:#eddecc;
  --text:#111827;--muted:#b79c8b;--body:#7b5c69;
  --danger:#991b1b;--danger-bg:#fee2e2;
  max-width:1000px;margin:0 auto
}

/* ── Common atoms ─────────────────────────────────────────────────────── */
.back-link{display:inline-flex;align-items:center;gap:6px;color:var(--muted);font-size:12px;font-weight:600;text-decoration:none;margin-bottom:16px}
.back-link:hover{color:var(--primary)}

.card{background:var(--surface);border:1px solid var(--border);border-radius:.75rem;padding:24px;margin-bottom:20px}
.card-title{font-size:14px;font-weight:700;color:var(--text);margin:0 0 16px;padding-bottom:12px;border-bottom:1px solid var(--border-light)}

.field{margin-bottom:16px}
.field label{display:block;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--muted);margin-bottom:4px}
.field .value{font-size:15px;font-weight:600;color:var(--text)}
.field .value.muted{font-weight:400;color:var(--body)}

.two-col{display:grid;grid-template-columns:1fr 1fr;gap:16px}

.flash{border:1px solid var(--border);border-radius:.75rem;background:var(--surface);padding:12px 14px;margin-bottom:18px;color:var(--body);font-size:13px;font-weight:600}

.btn-primary{display:inline-flex;align-items:center;gap:6px;padding:0 18px;height:36px;border:none;border-radius:.75rem;background:var(--primary);color:#fff;font-size:12px;font-weight:700;font-family:inherit;cursor:pointer;transition:background .12s;text-decoration:none}
.btn-primary:hover{background:var(--primary-hover)}
.btn-primary:disabled{opacity:.45;cursor:not-allowed;background:var(--muted)}
.btn-primary:disabled:hover{background:var(--muted)}
.btn-ghost{display:inline-flex;align-items:center;gap:6px;padding:0 14px;height:34px;border:1px solid var(--border);border-radius:.75rem;background:var(--surface);color:var(--primary);font-size:12px;font-weight:700;font-family:inherit;cursor:pointer;transition:background .12s;text-decoration:none}
.btn-ghost:hover{background:var(--primary-soft)}
.btn-sm{height:30px;padding:0 12px;font-size:11px}
.btn-danger{color:var(--danger)!important}
.btn-danger:hover{background:var(--danger-bg)!important}

.badge{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:999px;font-size:11px;font-weight:700}
.badge-active{background:#d1fae5;color:#065f46}
.badge-inactive{background:#fee2e2;color:#991b1b}

.edit-form input,.edit-form textarea,.edit-form select{width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:.5rem;background:var(--bg);color:var(--text);font-size:13px;font-family:inherit;outline:none;transition:border-color .12s}
.edit-form input:focus,.edit-form textarea:focus,.edit-form select:focus{border-color:var(--primary)}
.edit-form textarea{min-height:80px;resize:vertical}
.edit-form input[type=number]{width:140px}

.toggle-wrap{display:flex;align-items:center;gap:10px}
.toggle{position:relative;width:40px;height:22px;border-radius:999px;border:none;cursor:pointer;transition:background .2s}
.toggle.on{background:var(--primary)}
.toggle.off{background:var(--border)}
.toggle::after{content:'';position:absolute;top:2px;left:2px;width:18px;height:18px;border-radius:50%;background:#fff;transition:transform .2s}
.toggle.on::after{transform:translateX(18px)}

.summary-row{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:20px}
.stat{background:var(--surface);border:1px solid var(--border);border-radius:.75rem;padding:14px 16px}
.stat-label{font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--muted);margin-bottom:5px}
.stat-value{font-size:18px;font-weight:800;color:var(--text)}
.stat-sub{font-size:11px;color:var(--muted);margin-top:2px}

.service-table{width:100%;border-collapse:collapse}
.service-table th{padding:9px 12px;background:var(--soft);border-bottom:1px solid var(--border);font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--muted);text-align:left}
.service-table td{padding:12px;border-top:1px solid var(--border-light);vertical-align:middle}
.service-name{font-weight:800;color:var(--text)}
.service-meta{font-size:11px;color:var(--muted);margin-top:2px}
.service-empty{padding:26px;border:1px dashed var(--border);border-radius:.75rem;text-align:center;color:var(--muted);font-weight:700}

.guest-input{width:100px!important;padding:8px 10px;border:1px solid var(--border);border-radius:.5rem;background:var(--bg);color:var(--text);font-size:13px;outline:none}
.guest-form{display:flex;align-items:center;gap:8px}

/* ── Category badge strip ─────────────────────────────────────────────── */
.pkg-category-strip{display:flex;align-items:center;gap:10px;margin-bottom:20px;padding:12px 16px;background:var(--soft);border:1px solid var(--border-light);border-radius:.75rem}
.pkg-category-icon{width:32px;height:32px;border-radius:50%;background:var(--primary);display:grid;place-items:center;flex-shrink:0}
.pkg-category-icon svg{stroke:#fff}
.pkg-category-label{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--muted);margin-bottom:1px}
.pkg-category-name{font-size:14px;font-weight:700;color:var(--text)}
.pkg-category-note{margin-left:auto;font-size:11px;color:var(--muted)}

/* ── Add-service panel ────────────────────────────────────────────────── */
.add-svc-panel{margin-top:18px;padding-top:18px;border-top:1px solid var(--border-light)}
.add-svc-panel-title{font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--muted);margin-bottom:12px}

/* Step indicator */
.add-svc-steps{display:flex;align-items:center;gap:0;margin-bottom:18px}
.add-svc-step{display:flex;align-items:center;gap:7px;font-size:12px;font-weight:600;color:var(--muted);flex-shrink:0}
.add-svc-step-num{width:22px;height:22px;border-radius:50%;border:1.5px solid var(--border);background:var(--bg);display:grid;place-items:center;font-size:11px;font-weight:700;transition:all .2s}
.add-svc-step.active .add-svc-step-num{border-color:var(--primary);background:var(--primary);color:#fff}
.add-svc-step.done .add-svc-step-num{border-color:#065f46;background:#d1fae5;color:#065f46}
.add-svc-step.active{color:var(--text)}
.add-svc-step-connector{flex:1;height:1px;background:var(--border-light);margin:0 8px}

/* Step 1 — service selector */
.svc-select-row{display:flex;gap:10px;align-items:flex-end}
.svc-select-row select{flex:1;padding:10px 12px;border:1px solid var(--border);border-radius:.5rem;background:var(--bg);color:var(--text);font-size:13px;font-family:inherit;outline:none;transition:border-color .12s}
.svc-select-row select:focus{border-color:var(--primary)}
.svc-selected-preview{display:flex;align-items:center;gap:12px;padding:12px 14px;background:var(--soft);border:1px solid var(--border-light);border-radius:.5rem;margin-top:10px}
.svc-selected-preview-icon{width:36px;height:36px;border-radius:.375rem;background:var(--hover);display:grid;place-items:center;flex-shrink:0;color:var(--primary)}
.svc-selected-preview-name{font-size:13px;font-weight:700;color:var(--text)}
.svc-selected-preview-meta{font-size:11px;color:var(--muted);margin-top:1px}

/* Step 2 — hall picker (venue only) */
.hall-picker{display:none;margin-top:16px}
.hall-picker.visible{display:block}
.hall-picker-label{font-size:12px;font-weight:700;color:var(--text);margin-bottom:10px;display:flex;align-items:center;gap:8px}
.hall-picker-label svg{color:var(--primary)}
.hall-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(210px,1fr));gap:10px}
#hallCards{display:contents}
.hall-card{position:relative;border:1.5px solid var(--border);border-radius:.625rem;background:var(--surface);cursor:pointer;overflow:hidden;transition:border-color .15s,box-shadow .15s}
.hall-card:hover{border-color:var(--primary);box-shadow:0 4px 16px rgba(109,76,91,.1)}
.hall-card.selected{border-color:var(--primary);box-shadow:0 0 0 3px rgba(109,76,91,.12)}
.hall-card input[type=radio]{position:absolute;opacity:0;pointer-events:none}
.hall-card-img{height:100px;background:linear-gradient(135deg,#ede0d0,#ddcebb);position:relative;overflow:hidden}
.hall-card-img img{width:100%;height:100%;object-fit:cover}
.hall-card-img-placeholder{width:100%;height:100%;display:grid;place-items:center;color:var(--muted)}
.hall-card-check{position:absolute;top:8px;right:8px;width:22px;height:22px;border-radius:50%;background:#fff;border:1.5px solid var(--border);display:grid;place-items:center;transition:all .15s;opacity:0}
.hall-card.selected .hall-card-check{opacity:1;background:var(--primary);border-color:var(--primary)}
.hall-card-check svg{stroke:#fff}
.hall-card-body{padding:10px 12px 12px}
.hall-card-name{font-size:13px;font-weight:700;color:var(--text);margin-bottom:4px}
.hall-card-meta{display:flex;flex-direction:column;gap:3px}
.hall-card-meta-row{display:flex;align-items:center;gap:5px;font-size:11px;color:var(--muted)}
.hall-card-meta-row svg{flex-shrink:0;color:var(--primary)}
.hall-card-price{font-size:13px;font-weight:700;color:var(--primary);margin-top:6px}
.hall-none-option{display:flex;align-items:center;gap:10px;padding:10px 14px;border:1.5px dashed var(--border);border-radius:.625rem;cursor:pointer;font-size:12px;color:var(--muted);font-weight:600;transition:border-color .15s}
.hall-none-option:hover,.hall-none-option.selected{border-color:var(--primary);color:var(--primary)}

/* Step 2 — guest count row (food/catering) */
.guest-count-row{display:flex;align-items:center;gap:10px;margin-top:12px;padding:12px 14px;background:var(--soft);border:1px solid var(--border-light);border-radius:.5rem}
.guest-count-row label{font-size:12px;font-weight:700;color:var(--text);flex-shrink:0}
.guest-count-row input{width:100px;padding:8px 10px;border:1px solid var(--border);border-radius:.375rem;background:#fff;font-size:13px;outline:none}
.guest-count-row input:focus{border-color:var(--primary)}
.guest-count-note{font-size:11px;color:var(--muted)}

/* Add button row */
.add-svc-actions{display:flex;align-items:center;gap:10px;margin-top:16px}

@media(max-width:760px){
  .two-col,.summary-row{grid-template-columns:1fr}
  .edit-form input[type=number],.guest-input{width:100%!important}
  .svc-select-row{flex-direction:column;align-items:stretch}
  .hall-grid{grid-template-columns:1fr 1fr}
  .add-svc-steps{font-size:11px}
}
@media(max-width:480px){
  .hall-grid{grid-template-columns:1fr}
  .admin-pkg-detail{padding:16px}
}
</style>

<div class="admin-pkg-page">

  <a class="back-link" href="<?= URLROOT ?>/admin/packages">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
    Back to Packages
  </a>

  <?php if ($message !== ''): ?>
    <div class="flash"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <!-- ── Summary stats ─────────────────────────────────────────────────── -->
  <div class="summary-row">
    <div class="stat">
      <div class="stat-label">Package Price</div>
      <div class="stat-value" id="packagePriceCardValue"><?= $money($packagePrice) ?></div>
      <div class="stat-sub" id="packagePriceCardSub">Base <?= $money($packageBasePrice) ?> + 5% agent fee <?= $money($packageAgentFee) ?></div>
    </div>
    <div class="stat">
      <div class="stat-label">Services Included</div>
      <div class="stat-value"><?= count($package['items'] ?? []) ?></div>
      <div class="stat-sub">Total cost <?= $money($includedTotal) ?></div>
    </div>
    <div class="stat">
      <div class="stat-label">Status</div>
      <div class="stat-value" style="font-size:14px;padding-top:4px">
        <span class="badge <?= !empty($package['is_active']) ? 'badge-active' : 'badge-inactive' ?>">
          <?= !empty($package['is_active']) ? '● Active' : '● Inactive' ?>
        </span>
      </div>
      <div class="stat-sub"><?= $pkgCategoryName ?: 'No category set' ?></div>
    </div>
  </div>

  <!-- ── Package category strip ────────────────────────────────────────── -->
  <?php if ($pkgCategoryName): ?>
  <div class="pkg-category-strip">
    <div class="pkg-category-icon">
      <?php if ($isVenuePackage): ?>
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
      <?php else: ?>
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
      <?php endif; ?>
    </div>
    <div>
      <div class="pkg-category-label">Package Category</div>
      <div class="pkg-category-name"><?= $h($pkgCategoryName) ?></div>
    </div>
    <div class="pkg-category-note">Packages can mix categories; add only one service from each category.</div>
  </div>
  <?php endif; ?>

  <!-- ── Basic information ─────────────────────────────────────────────── -->
  <div class="card">
    <div class="card-title">Package Information</div>
    <form class="edit-form" id="packageDetailForm" method="POST"
          action="<?= URLROOT ?>/admin/packageUpdate/<?= (int)$package['package_id'] ?>"
          enctype="multipart/form-data">

      <div class="two-col">
        <div class="field">
          <label>Name</label>
          <input type="text" name="name" value="<?= $h($package['name'] ?? '') ?>" required>
        </div>
        <div class="field">
          <label>Slug</label>
          <input type="text" name="slug" value="<?= $h($package['slug'] ?? '') ?>">
        </div>
      </div>

      <div class="two-col">
        <div class="field">
          <label>Tagline</label>
          <input type="text" name="tagline" value="<?= $h($package['tagline'] ?? '') ?>">
        </div>
        <div class="field">
          <label>Category</label>
          <?php if (!empty($categories)): ?>
            <select name="category_id">
              <option value="">— No category —</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= (int)$cat['id'] ?>"
                  <?= ((int)$cat['id'] === $pkgCategoryId) ? 'selected' : '' ?>>
                  <?= $h($cat['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          <?php else: ?>
            <input type="text" value="<?= $h($pkgCategoryName) ?>" readonly style="background:var(--soft);color:var(--muted)">
            <input type="hidden" name="category_id" value="<?= $pkgCategoryId ?>">
          <?php endif; ?>
        </div>
      </div>

      <div class="field">
        <label>Base Price (MMK)</label>
        <input type="number" name="base_price" id="packagePriceInput" min="0" step="100"
               value="<?= (float)$packageBasePrice ?>" placeholder="<?= (float)$includedTotal ?>">
        <div class="stat-sub" style="margin-top:4px">Customer-facing price = base + 5% agent fee, calculated automatically.</div>
      </div>

      <div class="field">
        <label>Description</label>
        <textarea name="description"><?= $h($package['description'] ?? '') ?></textarea>
      </div>

      <div class="field">
        <label>Package Image</label>
        <?php if (!empty($package['image_url'])): ?>
          <div style="margin-bottom:10px">
            <img src="<?= $h($package['image_url']) ?>" alt="<?= $h($package['name'] ?? 'Package') ?>"
                 style="width:160px;height:90px;object-fit:cover;border-radius:.75rem;border:1px solid var(--border)">
          </div>
        <?php endif; ?>
        <input type="file" name="package_image" accept="image/jpeg,image/png,image/webp">
        <div class="service-meta" style="margin-top:5px">JPG, PNG or WebP. Leave empty to keep existing image.</div>
      </div>

      <div class="field">
        <div class="toggle-wrap" style="justify-content:space-between">
          <label style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--muted)">Active</label>
          <button type="button"
                  class="toggle <?= !empty($package['is_active']) ? 'on' : 'off' ?>"
                  onclick="this.classList.toggle('on');this.classList.toggle('off');document.getElementById('is_active_hidden').value=this.classList.contains('on')?1:0">
          </button>
          <input type="hidden" name="is_active" id="is_active_hidden" value="<?= !empty($package['is_active']) ? 1 : 0 ?>">
        </div>
      </div>

      <div style="margin-top:16px">
        <button class="btn-primary" id="packageSaveButton" type="submit" disabled>Save Changes</button>
      </div>
    </form>
  </div>

  <!-- ── Included services ─────────────────────────────────────────────── -->
  <div class="card">
    <div class="card-title">Included Services</div>

    <?php if (empty($package['items'])): ?>
      <div class="service-empty">No services added yet. Use the form below to add one service from each category.</div>
    <?php else: ?>
      <table class="service-table">
        <thead>
          <tr>
            <th>Service</th>
            <th>Supplier</th>
            <?php if ($hasVenueItems): ?><th>Hall</th><?php endif; ?>
            <th>Guests / Type</th>
            <th>Package Price</th>
            <th style="text-align:right">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach (($package['items'] ?? []) as $item): ?>
            <tr>
              <td>
                <div class="service-name"><?= $h($item['service_name'] ?? 'Service') ?></div>
                <div class="service-meta">#<?= (int)($item['service_id'] ?? 0) ?></div>
              </td>
              <td><?= $h($item['default_supplier_name'] ?? '—') ?></td>
              <?php if ($hasVenueItems): ?>
              <td>
                <?php $itemHallOptions = $hallOptionsByService[(int)($item['service_id'] ?? 0)] ?? []; ?>
                <?php if (!empty($item['hall_name'])): ?>
                  <div class="service-name" style="font-size:12px"><?= $h($item['hall_name']) ?></div>
                  <?php if (!empty($item['hall_capacity'])): ?>
                    <div class="service-meta">Up to <?= (int)$item['hall_capacity'] ?> guests</div>
                  <?php endif; ?>
                <?php else: ?>
                  <span class="service-meta">—</span>
                <?php endif; ?>
                <?php if (!empty($itemHallOptions)): ?>
                  <form class="guest-form" method="POST"
                        action="<?= URLROOT ?>/admin/packageUpdateItem/<?= (int)$item['id'] ?>"
                        style="margin-top:8px">
                    <select name="hall_id" class="guest-input" style="width:180px!important">
                      <option value="">No specific hall</option>
                      <?php foreach ($itemHallOptions as $hall): ?>
                        <option value="<?= (int)$hall['id'] ?>" <?= (int)($item['venue_room_id'] ?? 0) === (int)$hall['id'] ? 'selected' : '' ?>>
                          <?= $h($hall['name'] ?? 'Hall') ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                    <button class="btn-ghost btn-sm" type="submit">Update</button>
                  </form>
                <?php endif; ?>
              </td>
              <?php endif; ?>
              <td>
                <?php if (($item['quantity_type'] ?? 'fixed') === 'guests'): ?>
                  <form class="guest-form" method="POST"
                        action="<?= URLROOT ?>/admin/packageUpdateItem/<?= (int)$item['id'] ?>">
                    <input class="guest-input" type="number" name="quantity" min="1" step="1"
                           value="<?= max(1, (int)($item['quantity'] ?? 1)) ?>">
                    <button class="btn-ghost btn-sm" type="submit">Update</button>
                  </form>
                <?php else: ?>
                  <span class="service-meta">Fixed</span>
                <?php endif; ?>
              </td>
              <td>
                <strong><?= $money($item['default_price'] ?? 0) ?></strong>
                <?php if (($item['quantity_type'] ?? 'fixed') === 'guests'): ?>
                  <div class="service-meta"><?= $money($item['unit_price'] ?? 0) ?> per guest</div>
                <?php endif; ?>
              </td>
              <td style="text-align:right">
                <form method="POST"
                      action="<?= URLROOT ?>/admin/packageRemoveItem/<?= (int)$item['id'] ?>"
                      style="display:inline"
                      onsubmit="return confirm('Remove this service from the package?')">
                  <button class="btn-ghost btn-sm btn-danger" type="submit">Remove</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>

    <!-- ── Add-service panel ──────────────────────────────────────────── -->
    <?php if (!empty($addableServices)): ?>
    <div class="add-svc-panel">
      <div class="add-svc-panel-title">Add a Service</div>

      <!-- Step indicators -->
      <div class="add-svc-steps">
        <div class="add-svc-step active" id="stepIndicator1">
          <span class="add-svc-step-num">1</span>
          <span>Choose service</span>
        </div>
        <div class="add-svc-step-connector"></div>
        <div class="add-svc-step" id="stepIndicator2">
          <span class="add-svc-step-num">2</span>
          <span>Review details</span>
        </div>
        <div class="add-svc-step-connector"></div>
        <div class="add-svc-step" id="stepIndicator3">
          <span class="add-svc-step-num">3</span>
          <span>Confirm</span>
        </div>
      </div>

      <form id="addServiceForm" method="POST"
            action="<?= URLROOT ?>/admin/packageAddItem/<?= (int)$package['package_id'] ?>">

        <!-- Step 1: select service -->
        <div class="svc-select-row">
          <select name="service_id" id="serviceSelect" required onchange="onServiceChange(this)">
            <option value="">Select a service…</option>
            <?php foreach ($addableServices as $svc):
              $svcId = (int)($svc['id'] ?? 0);
              $isFoodSvc = str_contains(strtolower((string)($svc['category_slug'] ?? '') . ' ' . (string)($svc['category_name'] ?? '')), 'food')
                        || str_contains(strtolower((string)($svc['category_slug'] ?? '') . ' ' . (string)($svc['category_name'] ?? '')), 'cater');
              $priceLabel = $money($svc['display_price'] ?? 0) . ($isFoodSvc ? ' per guest' : '');
            ?>
              <option value="<?= $svcId ?>"
                      data-name="<?= $h($svc['name'] ?? '') ?>"
                      data-supplier="<?= $h($svc['supplier_name'] ?? '') ?>"
                      data-price="<?= $h($priceLabel) ?>"
                      data-food="<?= $isFoodSvc ? '1' : '0' ?>"
                      data-room-count="<?= count($hallOptionsByService[$svcId] ?? []) ?>">
                <?= $h(($svc['category_name'] ?? 'Service') . ' — ' . ($svc['name'] ?? '') . ' — ' . ($svc['supplier_name'] ?? '') . ' — ' . $priceLabel) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Selected service preview -->
        <div id="svcPreview" style="display:none">
          <div class="svc-selected-preview">
            <div class="svc-selected-preview-icon">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <div>
              <div class="svc-selected-preview-name" id="svcPreviewName"></div>
              <div class="svc-selected-preview-meta" id="svcPreviewMeta"></div>
            </div>
          </div>
        </div>

        <!-- ── Step 2 for VENUE: Hall picker ──────────────────────────── -->
        <div class="hall-picker" id="hallPicker">
          <div class="hall-picker-label">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            Select a hall for this venue service
          </div>

          <div class="hall-grid" id="hallGrid">
            <!-- "No specific hall" option -->
            <label class="hall-none-option" id="hallNoneOption" onclick="selectHall(null)">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
              No specific hall assigned
              <input type="radio" name="hall_id" value="" checked>
            </label>
            <div id="hallCards"></div>
          </div>
        </div><!-- /hallPicker -->

        <!-- ── Step 2 for food/catering: guest count ──────────────────── -->
        <div id="guestCountRow" class="guest-count-row" style="display:none">
          <label for="guestCountInput">Guest count</label>
          <input id="guestCountInput" type="number" name="guest_count" min="1" step="1" value="100">
          <span class="guest-count-note">Used to calculate total catering price.</span>
        </div>
        <input type="hidden" name="guest_count" id="guestCountHidden" value="100">

        <!-- Confirm / Add button -->
        <div class="add-svc-actions">
          <button class="btn-primary" type="submit" id="addSvcBtn" disabled>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add to Package
          </button>
          <span id="addSvcHint" style="font-size:11px;color:var(--muted)">Select a service above to continue.</span>
        </div>

      </form>
    </div>
    <?php endif; ?><!-- /addableServices check -->

  </div><!-- /card -->

</div><!-- /admin-pkg-page -->

<script>
(function () {
  /* ── Price card live update ─────────────────────────────────────────── */
  const priceInput = document.getElementById('packagePriceInput');
  const priceCardVal = document.getElementById('packagePriceCardValue');
  const priceCardSub = document.getElementById('packagePriceCardSub');
  const agentFeeRate = 0.05;

  function fmtMoney(v) {
    const n = Math.max(0, isFinite(+v) ? +v : 0);
    return 'MMK ' + n.toLocaleString('en-US', { maximumFractionDigits: 0 });
  }

  function updatePriceCard() {
    const base = Math.max(0, isFinite(+priceInput.value) ? +priceInput.value : 0);
    const fee = base * agentFeeRate;
    priceCardVal.textContent = fmtMoney(base + fee);
    priceCardSub.textContent = `Base ${fmtMoney(base)} + 5% agent fee ${fmtMoney(fee)}`;
  }
  if (priceInput) { updatePriceCard(); priceInput.addEventListener('input', updatePriceCard); }

  /* ── Save button enable/disable ─────────────────────────────────────── */
  const detailForm = document.getElementById('packageDetailForm');
  const saveBtn = document.getElementById('packageSaveButton');
  if (detailForm && saveBtn) {
    const fields = Array.from(detailForm.elements).filter(f => f.name && f.type !== 'submit' && f.type !== 'button');
    const original = new Map(fields.map(f => [f.name, f.value]));
    function hasChanges() {
      return fields.some(f => f.type === 'file' ? (f.files && f.files.length > 0) : f.value !== original.get(f.name));
    }
    function syncSaveBtn() { saveBtn.disabled = !hasChanges(); }
    fields.forEach(f => { f.addEventListener('input', syncSaveBtn); f.addEventListener('change', syncSaveBtn); });
    detailForm.querySelectorAll('.toggle').forEach(t => t.addEventListener('click', () => setTimeout(syncSaveBtn, 0)));
    detailForm.addEventListener('submit', () => { saveBtn.disabled = false; });
  }

  /* ── Add-service panel ──────────────────────────────────────────────── */
  const serviceSelect = document.getElementById('serviceSelect');
  const svcPreview    = document.getElementById('svcPreview');
  const svcPreviewName = document.getElementById('svcPreviewName');
  const svcPreviewMeta = document.getElementById('svcPreviewMeta');
  const addSvcBtn     = document.getElementById('addSvcBtn');
  const addSvcHint    = document.getElementById('addSvcHint');
  const hallPicker    = document.getElementById('hallPicker');
  const hallCards     = document.getElementById('hallCards');
  const guestCountRow = document.getElementById('guestCountRow');
  const guestCountHidden = document.getElementById('guestCountHidden');
  const guestCountInput  = document.getElementById('guestCountInput');
  const hallOptionsByService = <?= json_encode($hallOptionsByService, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;

  const stepEl = (n) => document.getElementById('stepIndicator' + n);

  function setStep(n) {
    [1, 2, 3].forEach(i => {
      const el = stepEl(i);
      if (!el) return;
      el.classList.remove('active', 'done');
      if (i < n) el.classList.add('done');
      else if (i === n) el.classList.add('active');
    });
  }

  function renderHallOptions(serviceId) {
    if (!hallCards) return;
    hallCards.innerHTML = '';

    const halls = hallOptionsByService[String(serviceId)] || hallOptionsByService[serviceId] || [];
    if (!halls.length) {
      const empty = document.createElement('div');
      empty.className = 'service-meta';
      empty.style.gridColumn = '1 / -1';
      empty.style.padding = '16px 0';
      empty.style.color = 'var(--muted)';
      empty.textContent = 'No halls have been set up for this venue service yet.';
      hallCards.appendChild(empty);
      selectHall(null);
      return;
    }

    halls.forEach((hall) => {
      const hallId = parseInt(hall.id || 0, 10);
      const card = document.createElement('label');
      card.className = 'hall-card';
      card.dataset.hallId = String(hallId);
      card.addEventListener('click', () => selectHall(hallId));

      const radio = document.createElement('input');
      radio.type = 'radio';
      radio.name = 'hall_id';
      radio.value = String(hallId);

      const visual = document.createElement('div');
      visual.className = 'hall-card-img';
      visual.innerHTML = '<div class="hall-card-img-placeholder"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg></div><div class="hall-card-check"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></div>';

      const body = document.createElement('div');
      body.className = 'hall-card-body';

      const name = document.createElement('div');
      name.className = 'hall-card-name';
      name.textContent = hall.name || 'Hall';
      body.appendChild(name);

      const meta = document.createElement('div');
      meta.className = 'hall-card-meta';

      if (hall.capacity) {
        const capacity = document.createElement('div');
        capacity.className = 'hall-card-meta-row';
        capacity.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>';
        capacity.appendChild(document.createTextNode('Up to ' + Number(hall.capacity).toLocaleString('en-US') + ' guests'));
        meta.appendChild(capacity);
      }

      const locationText = [hall.venue_name || '', hall.venue_location || ''].filter(Boolean).join(' · ');
      if (locationText) {
        const location = document.createElement('div');
        location.className = 'hall-card-meta-row';
        location.textContent = locationText;
        meta.appendChild(location);
      }

      if (hall.venue_description) {
        const description = document.createElement('div');
        description.className = 'hall-card-meta-row';
        description.style.marginTop = '2px';
        description.style.color = 'var(--body)';
        description.style.fontSize = '10px';
        description.style.lineHeight = '1.4';
        description.textContent = String(hall.venue_description).slice(0, 80);
        meta.appendChild(description);
      }

      body.appendChild(meta);

      if (parseFloat(hall.price || 0) > 0) {
        const price = document.createElement('div');
        price.className = 'hall-card-price';
        price.textContent = fmtMoney(hall.price);
        body.appendChild(price);
      }

      card.appendChild(radio);
      card.appendChild(visual);
      card.appendChild(body);
      hallCards.appendChild(card);
    });

    selectHall(null);
  }

  window.onServiceChange = function (sel) {
    const opt = sel.options[sel.selectedIndex];
    const hasVal = !!sel.value;

    if (hasVal) {
      svcPreviewName.textContent = opt.dataset.name || '';
      svcPreviewMeta.textContent = (opt.dataset.supplier || '') + ' · ' + (opt.dataset.price || '');
      svcPreview.style.display = 'block';
    } else {
      svcPreview.style.display = 'none';
    }

    const roomCount = parseInt(opt.dataset.roomCount || '0', 10);
    const hasRooms = hasVal && roomCount > 0;
    const isFood = hasVal && opt.dataset.food === '1';

    /* venue services: show hall picker when the selected service has halls */
    if (hallPicker) {
      if (hasRooms) {
        hallPicker.classList.add('visible');
        renderHallOptions(sel.value);
      } else {
        hallPicker.classList.remove('visible');
        selectHall(null);
      }
    }

    /* food/catering services: show guest count */
    if (guestCountRow) {
      guestCountRow.style.display = hasVal && isFood ? 'flex' : 'none';
      if (guestCountInput && guestCountHidden) {
        guestCountInput.name = isFood ? 'guest_count' : '';
        guestCountHidden.name = isFood ? '' : 'guest_count';
        guestCountHidden.value = isFood ? '' : '1';
      }
    }

    if (hasVal) {
      setStep(hasRooms ? 3 : 2);
      addSvcBtn.disabled = false;
      addSvcHint.textContent = hasRooms
        ? 'Optionally pick a hall, then click Add to Package.'
        : 'Click Add to Package to confirm.';
    } else {
      setStep(1);
      addSvcBtn.disabled = true;
      addSvcHint.textContent = 'Select a service above to continue.';
    }
  };

  /* Hall card selection */
  window.selectHall = function (hallId) {
    document.querySelectorAll('.hall-card').forEach(c => c.classList.remove('selected'));
    const noneOpt = document.getElementById('hallNoneOption');

    if (hallId === null) {
      if (noneOpt) noneOpt.classList.add('selected');
      const radios = document.querySelectorAll('.hall-card input[type=radio]');
      radios.forEach(r => r.checked = false);
      const noneRadio = document.querySelector('#hallNoneOption input[type=radio]');
      if (noneRadio) noneRadio.checked = true;
    } else {
      if (noneOpt) noneOpt.classList.remove('selected');
      const card = document.querySelector(`.hall-card[data-hall-id="${hallId}"]`);
      if (card) {
        card.classList.add('selected');
        const radio = card.querySelector('input[type=radio]');
        if (radio) radio.checked = true;
      }
    }

    /* advance to step 3 */
    if (serviceSelect && serviceSelect.value) {
      setStep(3);
      addSvcBtn.disabled = false;
      addSvcHint.textContent = hallId ? 'Hall selected. Click Add to Package.' : 'No hall assigned. Click Add to Package.';
    }
  };

  /* Guest count sync */
  if (guestCountInput && guestCountHidden) {
    guestCountInput.addEventListener('input', () => { guestCountHidden.value = guestCountInput.value; });
  }
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
