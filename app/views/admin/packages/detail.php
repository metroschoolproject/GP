<?php
$package = $package ?? null;
$message = $message ?? '';
$categories = $categories ?? [];
$serviceOptions = $serviceOptions ?? [];

$dashboardTitle = 'Packages';
$dashboardCrumb = htmlspecialchars($package['name'] ?? 'Package Detail', ENT_QUOTES, 'UTF-8');
$dashboardBreadcrumbs = [
  ['label' => 'Dashboard', 'url' => URLROOT . '/admin/dashboard'],
  ['label' => 'Packages', 'url' => URLROOT . '/admin/packages'],
  ['label' => $package['name'] ?? 'Package Detail', 'url' => null],
];
$dashboardContentClass = 'admin-pkg-detail';

$dashboardContent = function () use ($package, $message, $categories, $serviceOptions) {
  $h = fn($value) => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
  $money = fn($value) => 'MMK ' . number_format((float)$value, 0);
  $includedTotal = 0;
  $includedServiceIds = [];
  foreach (($package['items'] ?? []) as $item) {
    $includedTotal += (float)($item['default_price'] ?? $item['price_min'] ?? $item['price'] ?? 0);
    if (!empty($item['service_id'])) {
      $includedServiceIds[(int)$item['service_id']] = true;
    }
  }
  $agentFeeRate = 0.05;
  $agentFee = $includedTotal * $agentFeeRate;
  $suggestedPrice = $includedTotal + $agentFee;
  $storedBasePrice = (float)($package['base_price'] ?? 0);
  $packageBasePrice = $storedBasePrice > 0 ? $storedBasePrice : $includedTotal;
  $packageAgentFee = $packageBasePrice * $agentFeeRate;
  $packagePrice = $packageBasePrice + $packageAgentFee;
  $saving = max(0, $suggestedPrice - $packagePrice);
  $servicesByCategory = [];
  foreach ($serviceOptions as $service) {
    $categoryName = trim((string)($service['category_name'] ?? 'Other'));
    $servicesByCategory[$categoryName][] = $service;
  }
?>
<style>
  .admin-pkg-detail{min-height:100%;background:#FBFBF9;padding:28px 32px;font-family:'DM Sans',system-ui,-apple-system,sans-serif;color:#111827;font-size:13px}
  .admin-pkg-page *{box-sizing:border-box}
  .admin-pkg-page{--bg:#FBFBF9;--surface:#ffffff;--soft:#faf5ef;--hover:#eddecc;--border:#ead8c7;--border-light:#eddecc;--primary:#6d4c5b;--primary-hover:#7b5c69;--primary-soft:#eddecc;--text:#111827;--muted:#b79c8b;--body:#7b5c69;--danger:#991b1b;--danger-bg:#fee2e2;max-width:1000px;margin:0 auto}

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
  .badge-cat{padding:4px 12px;background:var(--primary-soft);color:var(--primary);border-radius:999px;font-size:12px;font-weight:600}

  .edit-form input,.edit-form textarea{width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:.5rem;background:var(--bg);color:var(--text);font-size:13px;font-family:inherit;outline:none;transition:border-color .12s}
  .edit-form input:focus,.edit-form textarea:focus{border-color:var(--primary)}
  .edit-form textarea{min-height:80px;resize:vertical}
  .edit-form input[type=number]{width:140px}
  .edit-form .inline{display:flex;align-items:center;gap:10px}

  .item-row{display:flex;align-items:center;justify-content:space-between;padding:10px 0;border-bottom:1px solid var(--border-light)}
  .item-row:last-child{border-bottom:none}

  .cat-grid{display:flex;flex-wrap:wrap;gap:8px;padding:12px 0}
  .cat-pill{display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border:1px solid var(--border);border-radius:999px;background:var(--soft);font-size:12px;font-weight:600;color:var(--body)}
  .cat-pill select{background:transparent;border:none;font-size:12px;font-weight:600;color:var(--primary);outline:none;cursor:pointer}

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
  .add-service-form{display:grid;grid-template-columns:1fr 120px auto;gap:10px;margin-top:14px;padding-top:16px;border-top:1px solid var(--border-light)}
  .add-service-form select{min-width:0;padding:10px 12px;border:1px solid var(--border);border-radius:.5rem;background:var(--bg);color:var(--text);font-size:13px;outline:none}
  .guest-input{width:120px!important;padding:10px 12px;border:1px solid var(--border);border-radius:.5rem;background:var(--bg);color:var(--text);font-size:13px;outline:none}
  .guest-form{display:flex;align-items:center;gap:8px}
  .price-actions{display:flex;align-items:flex-end;gap:10px;flex-wrap:wrap}
  .price-actions .field{margin-bottom:0}
  @media(max-width:760px){.two-col,.summary-row,.add-service-form{grid-template-columns:1fr}.edit-form input[type=number],.guest-input{width:100%!important}.guest-form{align-items:stretch;flex-direction:column}}
</style>
<div class="admin-pkg-page">
  <a class="back-link" href="<?= URLROOT ?>/admin/packages">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
    Back to Packages
  </a>

  <?php if ($message !== ''): ?>
    <div class="flash"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <div class="summary-row">
    <div class="stat">
      <div class="stat-label">Package Price</div>
      <div class="stat-value" id="packagePriceCardValue"><?= $money($packagePrice) ?></div>
      <div class="stat-sub" id="packagePriceCardSub">Base <?= $money($packageBasePrice) ?> + 5% agent fee <?= $money($packageAgentFee) ?></div>
    </div>


  </div>

  <!-- Basic Info -->
  <div class="card">
    <div class="card-title">Package Information</div>
    <form class="edit-form" id="packageDetailForm" method="POST" action="<?= URLROOT ?>/admin/packageUpdate/<?= (int)$package['package_id'] ?>" enctype="multipart/form-data">
      <div class="two-col">
        <div class="field">
          <label>Name</label>
          <input type="text" name="name" value="<?= htmlspecialchars($package['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
        </div>
        <div class="field">
          <label>Slug</label>
          <input type="text" name="slug" value="<?= htmlspecialchars($package['slug'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>
      </div>
      <div class="field">
        <label>Tagline</label>
        <input type="text" name="tagline" value="<?= htmlspecialchars($package['tagline'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
      </div>
      <div class="field">
        <label>Base Price</label>
        <input type="number" name="base_price" id="packagePriceInput" min="0" step="100" value="<?= (float)$packageBasePrice ?>" placeholder="<?= (float)$includedTotal ?>">
        <div class="stat-sub">Admin card/customer price adds 5% agent fee automatically.</div>

      </div>
      <div class="field">
        <label>Description</label>
        <textarea name="description"><?= htmlspecialchars($package['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
      </div>

   

      <div class="field">
        <label>Package Image</label>
        <?php if (!empty($package['image_url'])): ?>
          <div style="margin-bottom:10px">
            <img src="<?= htmlspecialchars($package['image_url'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($package['name'] ?? 'Package', ENT_QUOTES, 'UTF-8') ?>" style="width:160px;height:90px;object-fit:cover;border-radius:.75rem;border:1px solid var(--border)">
          </div>
        <?php endif; ?>
        <input type="file" name="package_image" accept="image/jpeg,image/png,image/webp">
        <div class="service-meta" style="margin-top:6px">Upload JPG, PNG, or WebP. Leave empty to keep the current image.</div>

        
      </div>



      <div class="field">
        <div class="toggle-wrap" style="justify-content:space-between">
          <label style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--muted)">Active</label>
          <button type="button" class="toggle <?= !empty($package['is_active']) ? 'on' : 'off' ?>" onclick="this.classList.toggle('on');this.classList.toggle('off');document.getElementById('is_active_hidden').value=this.classList.contains('on')?1:0"></button>
          <input type="hidden" name="is_active" id="is_active_hidden" value="<?= !empty($package['is_active']) ? 1 : 0 ?>">
        </div>
      </div>
      <div style="margin-top:16px">
        <button class="btn-primary" id="packageSaveButton" type="submit" disabled>Save Changes</button>
      </div>
    </form>
  </div>

  <!-- Included Services -->
  <div class="card">
    <div class="card-title">Included Services</div>

    <?php if (empty($package['items'])): ?>
      <div class="service-empty">No services included yet. Add venue, food, dress, studio, and accessories based on this package price.</div>
    <?php else: ?>
      <table class="service-table">
        <thead>
          <tr>
            <th>Service</th>
            <th>Category</th>
            <th>Supplier</th>
            <th>Guests</th>
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
              <td><?= $h($item['category_name'] ?? 'Uncategorized') ?></td>
              <td><?= $h($item['default_supplier_name'] ?? 'Supplier') ?></td>
              <td>
                <?php if (($item['quantity_type'] ?? 'fixed') === 'guests'): ?>
                  <form class="guest-form" method="POST" action="<?= URLROOT ?>/admin/packageUpdateItem/<?= (int)$item['id'] ?>">
                    <input class="guest-input" type="number" name="quantity" min="1" step="1" value="<?= max(1, (int)($item['quantity'] ?? 1)) ?>">
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
                <form method="POST" action="<?= URLROOT ?>/admin/packageRemoveItem/<?= (int)$item['id'] ?>" style="display:inline" onsubmit="return confirm('Remove this service from the package?')">
                  <button class="btn-ghost btn-sm btn-danger" type="submit">Remove</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>

    <?php if (!empty($serviceOptions)): ?>
      <form class="add-service-form" method="POST" action="<?= URLROOT ?>/admin/packageAddItem/<?= (int)$package['package_id'] ?>">
        <select name="service_id" required>
          <option value="">Add a service to this package...</option>
          <?php foreach ($servicesByCategory as $categoryName => $services): ?>
            <optgroup label="<?= $h($categoryName) ?>">
              <?php foreach ($services as $service):
                $serviceId = (int)($service['id'] ?? 0);
                if (isset($includedServiceIds[$serviceId])) continue;
                $isFoodService = strpos(strtolower((string)(($service['category_slug'] ?? '') . ' ' . ($service['category_name'] ?? ''))), 'food') !== false
                  || strpos(strtolower((string)(($service['category_slug'] ?? '') . ' ' . ($service['category_name'] ?? ''))), 'cater') !== false;
                $label = ($service['name'] ?? 'Service')
                  . ' - ' . ($service['supplier_name'] ?? 'Supplier')
                  . ' - ' . $money($service['display_price'] ?? 0)
                  . ($isFoodService ? ' per guest' : '');
              ?>
                <option value="<?= $serviceId ?>"><?= $h($label) ?></option>
              <?php endforeach; ?>
            </optgroup>
          <?php endforeach; ?>
        </select>
        <input class="guest-input" type="number" name="guest_count" min="1" step="1" value="100" aria-label="Guest count for food services" title="Guest count for food services">
        <button class="btn-primary" type="submit">Add Service</button>
      </form>
    <?php endif; ?>
  </div>
</div>
<script>
  const packagePriceInput = document.getElementById('packagePriceInput');
  const packagePriceCardValue = document.getElementById('packagePriceCardValue');
  const packagePriceCardSub = document.getElementById('packagePriceCardSub');
  const packageDetailForm = document.getElementById('packageDetailForm');
  const packageSaveButton = document.getElementById('packageSaveButton');
  const agentFeeRate = 0.05;

  function formatPackageMoney(value) {
    const amount = Number.parseFloat(value);
    const safeAmount = Number.isFinite(amount) ? Math.max(0, amount) : 0;

    return 'MMK ' + safeAmount.toLocaleString('en-US', {
      maximumFractionDigits: 0
    });
  }

  function updatePackagePriceCard() {
    const basePrice = Number.parseFloat(packagePriceInput.value);
    const safeBasePrice = Number.isFinite(basePrice) ? Math.max(0, basePrice) : 0;
    const agentFee = safeBasePrice * agentFeeRate;
    const finalPrice = safeBasePrice + agentFee;
    packagePriceCardValue.textContent = formatPackageMoney(finalPrice);
    packagePriceCardSub.textContent = `Base ${formatPackageMoney(safeBasePrice)} + 5% agent fee ${formatPackageMoney(agentFee)}`;
  }

  if (packagePriceInput && packagePriceCardValue && packagePriceCardSub) {
    updatePackagePriceCard();
    packagePriceInput.addEventListener('input', updatePackagePriceCard);
  }

  if (packageDetailForm && packageSaveButton) {
    const editableFields = Array.from(packageDetailForm.elements).filter((field) => {
      return field.name && field.type !== 'submit' && field.type !== 'button';
    });
    const originalValues = new Map(editableFields.map((field) => [field.name, field.value]));

    function packageDetailHasChanges() {
      return editableFields.some((field) => {
        if (field.type === 'file') {
          return field.files && field.files.length > 0;
        }

        return field.value !== originalValues.get(field.name);
      });
    }

    window.updatePackageDetailSaveState = function () {
      packageSaveButton.disabled = !packageDetailHasChanges();
    };

    editableFields.forEach((field) => {
      field.addEventListener('input', window.updatePackageDetailSaveState);
      field.addEventListener('change', window.updatePackageDetailSaveState);
    });
    packageDetailForm.querySelectorAll('.toggle').forEach((toggle) => {
      toggle.addEventListener('click', () => {
        window.setTimeout(window.updatePackageDetailSaveState, 0);
      });
    });
    packageDetailForm.addEventListener('submit', () => {
      packageSaveButton.disabled = false;
    });
    window.updatePackageDetailSaveState();
  }
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
