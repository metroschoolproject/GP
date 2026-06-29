<?php
$package = $package ?? null;
$message = $message ?? '';
$serviceOptions = $serviceOptions ?? [];
$hallOptionsByService = $hallOptionsByService ?? [];
$attireOptionsByService = $attireOptionsByService ?? [];
$decoOptionsByService = $decoOptionsByService ?? [];

$dashboardTitle = 'Packages';
$dashboardCrumb = htmlspecialchars($package['name'] ?? 'Package Detail', ENT_QUOTES, 'UTF-8');
$dashboardBreadcrumbs = [
  ['label' => 'Dashboard', 'url' => URLROOT . '/admin/dashboard'],
  ['label' => 'Packages', 'url' => URLROOT . '/admin/packages'],
  ['label' => $package['name'] ?? 'Package Detail', 'url' => null],
];
$dashboardContentClass = 'admin-pkg-detail';

$dashboardContent = function () use ($package, $message, $serviceOptions, $hallOptionsByService, $attireOptionsByService, $decoOptionsByService) {
  $h = fn($value) => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
  $money = fn($value) => 'MMK ' . number_format((float)$value, 0);

  // ── Price calculations ────────────────────────────────────────────────────
  $includedTotal = 0;
  $includedServiceIds = [];
  $includedCategoryNames = [];
  $hasVenueItems = false;
  $hasRentalItems = false;
  foreach (($package['items'] ?? []) as $item) {
    $includedTotal += (float)($item['default_price'] ?? $item['price_min'] ?? $item['price'] ?? 0);
    if (!empty($item['service_id'])) {
      $includedServiceIds[(int)$item['service_id']] = true;
    }
    if (!empty($item['category_id'])) {
      $includedCategoryNames[(int)$item['category_id']] = trim((string)($item['category_name'] ?? 'this category'));
    }
    if (!empty($item['venue_room_id']) || !empty($item['hall_name']) || !empty($hallOptionsByService[(int)($item['service_id'] ?? 0)])) {
      $hasVenueItems = true;
    }
    // Check for attire (rental pricing)
    $catSlug = strtolower(trim((string)($item['category_slug'] ?? '')));
    $catName = strtolower(trim((string)($item['category_name'] ?? '')));
    if (in_array($catSlug, ['attire'], true) || in_array($catName, ['attire'], true)) {
      $hasRentalItems = true;
    }
  }
  $agentFeeRate    = get_platform_fee_percent() / 100;
  $agentFee        = $includedTotal * $agentFeeRate;
  $suggestedPrice  = $includedTotal + $agentFee;
  $storedBasePrice = (float)($package['base_price'] ?? 0);
  $packageBasePrice = $storedBasePrice > 0 ? $storedBasePrice : $includedTotal;
  $packageAgentFee  = $packageBasePrice * $agentFeeRate;
  $packagePrice     = $packageBasePrice + $packageAgentFee;
  $saving           = max(0, $suggestedPrice - $packagePrice);

  $isDraft = (($package['status'] ?? '') === 'draft');
  $isPublished = (($package['status'] ?? '') === 'published');

  // Helper: build rental pricing display for attire items (package prices only)
  $rentalPricingHtml = function ($item) use ($money, $h) {
    $borrowPkg = (float)($item['borrow_package_price'] ?? $item['borrow_price'] ?? 0);
    $buyPkg = (float)($item['buy_package_price'] ?? $item['buy_price'] ?? 0);
    $returnDays = (int)($item['return_days'] ?? 0);
    $cols = [];
    if ($borrowPkg > 0) {
      $cols[] = '<span>Borrow <b>' . $money($borrowPkg) . '</b>'
              . ($returnDays > 0 ? ' <small>Return in ' . $returnDays . ' ' . ($returnDays === 1 ? 'day' : 'days') . '</small>' : '')
              . '</span>';
    }
    if ($buyPkg > 0) {
      $cols[] = '<span>Buy <b>' . $money($buyPkg) . '</b></span>';
    }
    if (!empty($cols)) {
      return '<div class="rental-price-row">' . implode('', $cols) . '</div>';
    }
    return '<span class="service-meta">—</span>';
  };

  $addableServices = array_filter($serviceOptions, function ($svc) use ($includedServiceIds) {
    $svcId = (int)($svc['id'] ?? 0);
    if ($svcId > 0 && isset($includedServiceIds[$svcId])) {
      return false;
    }
    return true;
  });
  $addableServices = array_values($addableServices);
?>
<style>
/* ── Reset & base ─────────────────────────────────────────────────────── */
.admin-pkg-detail{min-height:100%;background:#F4F1EE;padding:28px 32px;font-family:'DM Sans',system-ui,-apple-system,sans-serif;color:#6d4c5b;font-size:13px}
.admin-pkg-page *{box-sizing:border-box}
.admin-pkg-page{
  --bg:#F4F1EE;--surface:#ffffff;--soft:#FFFFFF;--hover:#eddecc;
  --border:#ead8c7;--border-light:#eddecc;
  --primary:#6d4c5b;--primary-hover:#7b5c69;--primary-soft:#eddecc;
  --text:#111827;--muted:#b79c8b;--body:#7b5c69;
  --danger:#991B1B;--danger-bg:#FEF2F2;
  max-width:1180px;margin:0 auto
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
.btn-danger{color:var(--danger)!important;border-color:var(--danger)!important}
.btn-danger:hover{background:var(--danger-bg)!important;border-color:var(--danger)!important}
.btn-danger-fill.btn-primary{background:var(--danger)!important;border-color:var(--danger)!important}
.btn-danger-fill.btn-primary:hover{background:#7F1D1D!important;border-color:#7F1D1D!important}

.badge{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:999px;font-size:11px;font-weight:700}
.badge-active{background:#ECFDF5;color:#065F46}
.badge-inactive{background:#FEF2F2;color:#991B1B}
.badge-draft{background:#fffbeb;color:#92400E}

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

/* ── Included service cards ───────────────────────────────────────────── */
.included-card{padding:0;overflow:hidden}
.included-card-head{display:flex;align-items:center;justify-content:space-between;gap:20px;padding:20px 24px;border-bottom:1px solid var(--border-light)}
.included-card-title{margin:0;color:var(--text);font-size:15px;font-weight:800}
.included-card-sub{margin-top:3px;color:var(--muted);font-size:11px}
.included-card-actions{display:flex;align-items:center;justify-content:flex-end;gap:12px}
.included-card-total{text-align:right}
.included-card-total span{display:block;color:var(--muted);font-size:10px;font-weight:800;letter-spacing:.08em;text-transform:uppercase}
.included-card-total strong{display:block;margin-top:3px;color:var(--primary);font-size:16px}
.included-list{display:flex;flex-direction:column}
.included-item{padding:22px 24px;border-bottom:1px solid var(--border-light);background:#fff}
.included-item:last-child{border-bottom:0}
.included-item:hover{background:#fffdfb}
.included-item-top{display:flex;align-items:flex-start;justify-content:space-between;gap:18px;margin-bottom:18px}
.included-identity{display:flex;align-items:flex-start;gap:12px;min-width:0}
.included-number{display:grid;place-items:center;width:34px;height:34px;flex:0 0 34px;border-radius:10px;background:var(--primary-soft);color:var(--primary);font-size:11px;font-weight:800}
.included-service-name{color:var(--text);font-size:15px;font-weight:800;line-height:1.35;overflow-wrap:anywhere}
.included-service-meta{display:flex;align-items:center;gap:7px;flex-wrap:wrap;margin-top:5px;color:var(--muted);font-size:11px}
.included-dot{width:3px;height:3px;border-radius:50%;background:var(--border)}
.included-category{display:inline-flex;align-items:center;padding:3px 8px;border-radius:999px;background:var(--soft);color:var(--body);font-size:10px;font-weight:800}
.included-remove{flex-shrink:0}
.included-grid{display:grid;grid-template-columns:minmax(0,1.35fr) minmax(150px,.65fr) minmax(240px,1fr);gap:14px}
.included-panel{min-width:0;padding:14px;border:1px solid var(--border-light);border-radius:10px;background:var(--bg)}
.included-panel-label{display:flex;align-items:center;gap:6px;margin-bottom:9px;color:var(--muted);font-size:9px;font-weight:800;letter-spacing:.1em;text-transform:uppercase}
.included-panel-label svg{color:var(--primary)}
.included-panel-value{color:var(--text);font-size:12px;font-weight:700}
.included-panel-note{margin-top:3px;color:var(--muted);font-size:10px;line-height:1.45}
.included-panel .guest-form{align-items:stretch;margin-top:10px}
.included-panel .guest-input{width:100%!important;min-width:0;background:#fff}
.included-panel .guest-form .btn-ghost{flex-shrink:0}
.included-hall-form{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:8px;margin-top:10px}
.included-hall-form .guest-input{width:100%!important;background:#fff}
.included-rental{margin-top:10px;padding-top:10px;border-top:1px solid var(--border-light)}
.rental-option+.rental-option{margin-top:9px;padding-top:9px;border-top:1px solid var(--border-light)}
.rental-option-head{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:6px}
.rental-option-head strong{color:var(--primary);font-size:11px}
.rental-option.buy .rental-option-head strong{color:#067647}
.rental-option-head span{color:var(--muted);font-size:9px}
.rental-price-row{display:flex;gap:8px}
.rental-price-row span{color:var(--muted);font-size:9px}
.rental-price-row b{display:block;margin-top:2px;color:var(--text);font-size:11px}
.included-prices{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.included-price{padding:13px;border-radius:9px;background:#fff;border:1px solid var(--border-light)}
.included-price span{display:block;color:var(--muted);font-size:9px;font-weight:800;letter-spacing:.08em;text-transform:uppercase}
.included-price strong{display:block;margin-top:5px;color:var(--text);font-size:14px;line-height:1.25}
.included-price small{display:block;margin-top:4px;color:var(--muted);font-size:10px;line-height:1.4}
.included-price.is-primary{background:#faf6f7;border-color:#decdd4}
.included-price.is-primary strong{color:var(--primary)}
.draft-actions{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:16px 24px;border-top:1px solid var(--border-light);background:var(--soft)}
.draft-actions-copy strong{display:block;color:var(--text);font-size:12px}
.draft-actions-copy span{display:block;margin-top:2px;color:var(--muted);font-size:10px}
.draft-actions-buttons{display:flex;gap:8px}

/* ── Publish confirmation ────────────────────────────────────────────── */
.publish-modal{
  --surface:#fff;--soft:#FFFFFF;--border:#ead8c7;--border-light:#eddecc;
  --primary:#6d4c5b;--primary-hover:#7b5c69;--primary-soft:#eddecc;
  --text:#111827;--body:#7b5c69;
  position:fixed;inset:0;z-index:1000;display:none;align-items:center;justify-content:center;padding:20px;
  font-family:'DM Sans',system-ui,-apple-system,sans-serif
}
.publish-modal.is-open{display:flex}
.publish-modal-backdrop{position:absolute;inset:0;background:rgba(17,24,39,.48);backdrop-filter:blur(3px)}
.publish-modal-dialog{position:relative;width:min(100%,440px);overflow:hidden;border:1px solid var(--border);border-radius:1rem;background:var(--surface);box-shadow:0 24px 70px rgba(17,24,39,.22);animation:publish-modal-in .18s ease-out}
.publish-modal-body{padding:26px 26px 20px}
.publish-modal-icon{width:46px;height:46px;display:grid;place-items:center;margin-bottom:16px;border-radius:.75rem;background:var(--primary-soft);color:var(--primary)}
.publish-modal-title{margin:0;color:var(--text);font-size:18px;font-weight:800}
.publish-modal-copy{margin:8px 0 0;color:var(--body);font-size:13px;line-height:1.55}
.publish-modal-note{display:flex;align-items:flex-start;gap:9px;margin-top:18px;padding:12px;border:1px solid var(--border-light);border-radius:.65rem;background:var(--soft);color:var(--body);font-size:11px;line-height:1.45}
.publish-modal-note svg{flex:0 0 auto;margin-top:1px;color:var(--primary)}
.publish-modal-actions{display:flex;justify-content:flex-end;gap:9px;padding:16px 26px 22px}
@keyframes publish-modal-in{from{opacity:0;transform:translateY(8px) scale(.98)}to{opacity:1;transform:translateY(0) scale(1)}}

.cover-uploader{position:relative;min-height:250px;border:1.5px dashed #d8d5d2;border-radius:14px;background:#fcfcfb;overflow:hidden;transition:border-color .18s,background .18s,box-shadow .18s}
.cover-uploader:hover,.cover-uploader.is-dragging{border-color:var(--primary);background:#fbf7f8;box-shadow:0 0 0 3px rgba(109,76,91,.07)}
.cover-uploader.has-image{border-style:solid;background:#161214}
.cover-uploader-input{position:absolute!important;width:1px!important;height:1px!important;opacity:0;pointer-events:none;padding:0!important}
.cover-uploader-label{display:flex!important;min-height:250px;margin:0!important;align-items:center;justify-content:center;cursor:pointer;text-transform:none!important;letter-spacing:normal!important;color:inherit!important}
.cover-uploader-empty{text-align:center;padding:34px 20px}
.cover-upload-icon{display:grid;place-items:center;width:58px;height:44px;margin:0 auto 18px;color:#d4d5d8}
.cover-upload-title{display:block;font-size:15px;font-weight:800;color:var(--text);margin-bottom:6px}
.cover-upload-title span{color:var(--primary)}
.cover-upload-help{display:block;font-size:12px;color:var(--muted);margin-bottom:18px}
.cover-upload-button{display:inline-flex;align-items:center;justify-content:center;height:36px;padding:0 16px;border:1px solid var(--border);border-radius:.65rem;background:#fff;color:var(--text);font-size:12px;font-weight:700;box-shadow:0 1px 2px rgba(17,24,39,.04)}
.cover-uploader-preview{position:absolute;inset:0;display:none}
.cover-uploader.has-image .cover-uploader-preview{display:block}
.cover-uploader.has-image .cover-uploader-empty{display:none}
.cover-uploader-preview img{width:100%;height:100%;object-fit:cover}
.cover-preview-shade{position:absolute;inset:auto 0 0;padding:54px 20px 18px;background:linear-gradient(transparent,rgba(18,13,15,.82));display:flex;align-items:flex-end;justify-content:space-between;gap:16px;color:#fff}
.cover-preview-name{min-width:0;font-size:12px;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.cover-preview-change{flex-shrink:0;border:1px solid rgba(255,255,255,.55);border-radius:.6rem;background:rgba(255,255,255,.14);color:#fff;padding:8px 12px;font-family:inherit;font-size:11px;font-weight:700;backdrop-filter:blur(8px);cursor:pointer}
.cover-upload-error{display:none;margin-top:7px;color:#b42318;font-size:12px;font-weight:600}
.cover-upload-error.is-visible{display:block}

/* ── Add-service panel ────────────────────────────────────────────────── */
.service-catalog-modal{
  --surface:#fff;--soft:#FFFFFF;--hover:#eddecc;--border:#ead8c7;--border-light:#eddecc;
  --primary:#6d4c5b;--primary-hover:#7b5c69;--primary-soft:#eddecc;
  --text:#111827;--muted:#b79c8b;--body:#7b5c69;
  position:fixed;inset:0;z-index:1100;display:none;align-items:center;justify-content:center;padding:24px;
  font-family:'DM Sans',system-ui,-apple-system,sans-serif
}
.service-catalog-modal.is-open{display:flex}
.service-catalog-modal-backdrop{position:absolute;inset:0;background:rgba(17,24,39,.52);backdrop-filter:blur(4px)}
.add-svc-panel{position:relative;width:min(1180px,100%);max-height:calc(100vh - 48px);overflow:auto;padding:22px;border:1px solid var(--border);border-radius:1rem;background:#fff;box-shadow:0 28px 90px rgba(17,24,39,.28);animation:service-modal-in .18s ease-out}
.included-card .add-svc-panel{margin:0;padding:22px;border:1px solid var(--border);background:#fff}
.add-svc-panel-head{display:flex;align-items:flex-end;justify-content:space-between;gap:18px;margin-bottom:18px}
.add-svc-panel-head-actions{display:flex;align-items:center;gap:9px}
.add-svc-panel-title{font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.1em;color:var(--primary);margin-bottom:4px}
.add-svc-panel-copy{font-size:12px;color:var(--muted)}
.add-svc-panel-count{font-size:11px;font-weight:800;color:var(--primary);padding:6px 10px;border:1px solid var(--border);border-radius:999px;background:var(--soft);white-space:nowrap}
.service-modal-close{width:34px;height:34px;display:grid;place-items:center;border:1px solid var(--border);border-radius:.65rem;background:#fff;color:var(--primary);font-size:20px;line-height:1;cursor:pointer}
.service-modal-close:hover{background:var(--soft)}
@keyframes service-modal-in{from{opacity:0;transform:translateY(10px) scale(.985)}to{opacity:1;transform:translateY(0) scale(1)}}

/* Step indicator */
.add-svc-steps{display:flex;align-items:center;gap:0;margin-bottom:18px}
.add-svc-step{display:flex;align-items:center;gap:7px;font-size:12px;font-weight:600;color:var(--muted);flex-shrink:0}
.add-svc-step-num{width:22px;height:22px;border-radius:50%;border:1.5px solid var(--border);background:var(--bg);display:grid;place-items:center;font-size:11px;font-weight:700;transition:all .2s}
.add-svc-step.active .add-svc-step-num{border-color:var(--primary);background:var(--primary);color:#fff}
.add-svc-step.done .add-svc-step-num{border-color:#065F46;background:#ECFDF5;color:#065F46}
.add-svc-step.active{color:var(--text)}
.add-svc-step-connector{flex:1;height:1px;background:var(--border-light);margin:0 8px}

/* Searchable service catalog */
.service-catalog{display:grid;grid-template-columns:minmax(280px,36%) minmax(0,1fr);min-height:520px;border:1px solid var(--border);border-radius:.8rem;overflow:hidden;background:var(--surface)}
.service-catalog-browser{display:flex;flex-direction:column;min-width:0;background:#fcfaf7;border-right:1px solid var(--border)}
.service-catalog-tools{padding:14px;border-bottom:1px solid var(--border-light)}
.service-search-wrap{position:relative}
.service-search-wrap svg{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--muted);pointer-events:none}
.service-search{width:100%;height:42px;padding:0 36px;border:1px solid var(--border);border-radius:.65rem;background:#fff;color:var(--text);font:inherit;outline:none;transition:border-color .15s,box-shadow .15s}
.service-search:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(109,76,91,.1)}
.service-search-clear{position:absolute;right:8px;top:50%;transform:translateY(-50%);display:none;width:26px;height:26px;border:0;border-radius:50%;background:transparent;color:var(--muted);cursor:pointer}
.service-search-clear.visible{display:grid;place-items:center}
.service-search-clear:hover{background:var(--soft);color:var(--primary)}
.service-filter-row{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:9px}
.service-filter{min-width:0;height:34px;padding:0 28px 0 10px;border:1px solid var(--border);border-radius:.55rem;background:#fff;color:var(--body);font:600 11px/1 'DM Sans',system-ui,sans-serif;outline:none}
.service-filter:focus{border-color:var(--primary)}
.service-results-meta{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:11px 14px 8px;color:var(--muted);font-size:10px;font-weight:800;letter-spacing:.08em;text-transform:uppercase}
.service-results{display:flex;flex-direction:column;gap:6px;max-height:440px;padding:0 10px 12px;overflow:auto;scrollbar-width:thin;scrollbar-color:var(--border) transparent}
.service-result{width:100%;display:grid;grid-template-columns:38px minmax(0,1fr) auto;align-items:center;gap:10px;padding:10px;border:1px solid transparent;border-radius:.65rem;background:transparent;color:inherit;text-align:left;font-family:inherit;cursor:pointer;transition:background .12s,border-color .12s,transform .12s}
.service-result[hidden],.service-detail-empty[hidden]{display:none}
.service-result:hover{background:#fff;border-color:var(--border);transform:translateX(2px)}
.service-result:focus-visible{outline:2px solid var(--primary);outline-offset:1px;background:#fff}
.service-result.selected{background:#fff;border-color:var(--primary);box-shadow:0 4px 16px rgba(109,76,91,.08)}
.service-result-mark{width:38px;height:38px;border-radius:.55rem;display:grid;place-items:center;background:var(--hover);color:var(--primary);font-size:11px;font-weight:900;overflow:hidden}
.service-result-mark img{width:100%;height:100%;object-fit:cover}
.service-result-main{min-width:0}
.service-result-name{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:var(--text);font-size:12px;font-weight:800}
.service-result-supplier{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;margin-top:2px;color:var(--body);font-size:10px}
.service-result-tags{display:flex;align-items:center;gap:5px;margin-top:5px}
.service-result-tag{display:inline-flex;max-width:110px;padding:2px 6px;border-radius:999px;background:var(--soft);color:var(--muted);font-size:9px;font-weight:800;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.service-result-status{align-self:start;padding-top:2px;color:var(--muted);font-size:9px;font-weight:800;white-space:nowrap}
.service-result-status.ready{color:#087443}
.service-results-empty{display:none;margin:8px 4px;padding:28px 16px;border:1px dashed var(--border);border-radius:.65rem;text-align:center;color:var(--muted);font-size:11px;line-height:1.5}
.service-results-empty.visible{display:block}
.service-catalog-detail{min-width:0;padding:18px;background:#fff}
.service-detail-empty{height:100%;min-height:420px;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:36px;text-align:center}
.service-detail-empty-icon{width:54px;height:54px;display:grid;place-items:center;margin-bottom:14px;border:1px solid var(--border);border-radius:50%;background:var(--soft);color:var(--primary)}
.service-detail-empty strong{color:var(--text);font-size:14px}
.service-detail-empty span{max-width:330px;margin-top:5px;color:var(--muted);font-size:11px;line-height:1.5}
.service-detail-workspace{display:none}
.service-detail-workspace.visible{display:block}
.svc-selected-preview{display:flex;align-items:flex-start;gap:12px;padding-bottom:16px;border-bottom:1px solid var(--border-light)}
.svc-selected-preview-icon{width:42px;height:42px;border-radius:.55rem;background:var(--hover);display:grid;place-items:center;flex-shrink:0;color:var(--primary)}
.svc-selected-preview-copy{min-width:0;flex:1}
.svc-selected-preview-eyebrow{margin-bottom:3px;color:var(--muted);font-size:9px;font-weight:900;letter-spacing:.1em;text-transform:uppercase}
.svc-selected-preview-name{font-size:14px;font-weight:800;color:var(--text)}
.svc-selected-preview-meta{font-size:11px;color:var(--body);margin-top:2px}
.svc-selected-preview-price{margin-left:auto;color:var(--primary);font-size:11px;font-weight:800;text-align:right}

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
  .service-catalog{grid-template-columns:1fr}
  .service-catalog-browser{border-right:0;border-bottom:1px solid var(--border)}
  .service-results{max-height:300px}
  .service-detail-empty{min-height:220px}
  .hall-grid{grid-template-columns:1fr 1fr}
  .add-svc-steps{font-size:11px}
  .included-grid{grid-template-columns:1fr}
  .included-card-head,.included-item{padding-left:18px;padding-right:18px}
  .included-card-head{align-items:flex-start}
  .included-card-actions{align-items:flex-end;flex-direction:column}
  .draft-actions{align-items:flex-start;flex-direction:column}
}
@media(max-width:480px){
  .hall-grid{grid-template-columns:1fr}
  .admin-pkg-detail{padding:16px}
  .included-item-top{flex-direction:column}
  .included-prices{grid-template-columns:1fr}
  .draft-actions-buttons{width:100%;flex-direction:column}
  .draft-actions-buttons form,.draft-actions-buttons button{width:100%}
  .service-filter-row{grid-template-columns:1fr}
  .service-catalog-detail{padding:14px}
  .add-svc-panel-head{align-items:flex-start;flex-direction:column}
  .service-catalog-modal{padding:0}
  .add-svc-panel,.included-card .add-svc-panel{width:100%;height:100%;max-height:none;border:0;border-radius:0;padding:16px}
  .add-svc-panel-head-actions{width:100%;justify-content:space-between}
  .included-card-actions{width:100%;align-items:stretch}
  .included-card-actions .btn-primary{justify-content:center}
  .publish-modal-actions{flex-direction:column-reverse}
  .publish-modal-actions button{width:100%;justify-content:center}
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
      <div class="stat-sub" id="packagePriceCardSub">Base <?= $money($packageBasePrice) ?> + <?= (int)($agentFeeRate * 100) ?>% agent fee <?= $money($packageAgentFee) ?></div>
    </div>
    <div class="stat">
      <div class="stat-label">Services Included</div>
      <div class="stat-value"><?= count($package['items'] ?? []) ?></div>
      <div class="stat-sub">Total cost <?= $money($includedTotal) ?></div>
    </div>
    <div class="stat">
      <div class="stat-label">Status</div>
      <div class="stat-value" style="font-size:14px;padding-top:4px">
        <?php if ($isDraft): ?>
          <span class="badge badge-draft">● Draft</span>
          <?php if (!empty($package['replaces_package_id'])): ?>
            <span style="font-size:11px;color:var(--muted)">Editing copy</span>
          <?php endif; ?>
        <?php elseif ($isPublished): ?>
          <span class="badge <?= !empty($package['is_active']) ? 'badge-active' : 'badge-inactive' ?>">
            <?= !empty($package['is_active']) ? '● Published' : '● Inactive' ?>
          </span>
        <?php else: ?>
          <span class="badge badge-inactive">● <?= $h($package['status'] ?? 'Unknown') ?></span>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- ── Basic information ─────────────────────────────────────────────── -->
  <div class="card">
    <div class="card-title">
      Package Information
      <?php if ($isDraft): ?>
        <span class="badge badge-draft" style="margin-left:8px">Draft</span>
      <?php endif; ?>
    </div>

    <?php if ($isPublished): ?>
      <!-- Read-only published view -->
      <div class="field">
        <label>Name</label>
        <div class="value"><?= $h($package['name'] ?? '') ?></div>
      </div>
      <div class="field">
        <label>Slug</label>
        <div class="value muted"><?= $h($package['slug'] ?? '') ?></div>
      </div>
      <div class="field">
        <label>Tagline</label>
        <div class="value muted"><?= $h($package['tagline'] ?? '—') ?></div>
      </div>
      <div class="field">
        <label>Base Price</label>
        <div class="value"><?= $money($packageBasePrice) ?></div>
        <div class="stat-sub" style="margin-top:4px"><?= $h($package['slug'] ?? '') ?>ml + <?= (int)($agentFeeRate * 100) ?>% agent fee = <?= $money($packagePrice) ?></div>
      </div>
      <div class="field">
        <label>Description</label>
        <div class="value muted" style="white-space:pre-wrap"><?= $h($package['description'] ?: 'No description') ?></div>
      </div>
      <?php if (!empty($package['image_url'])): ?>
      <div class="field">
        <label>Image</label>
        <img src="<?= $h($package['image_url']) ?>" alt="<?= $h($package['name'] ?? 'Package') ?>"
             style="width:160px;height:90px;object-fit:cover;border-radius:.75rem;border:1px solid var(--border)">
      </div>
      <?php endif; ?>
      <div style="display:flex;gap:10px;margin-top:20px">
        <form id="editPackageForm" method="POST" action="<?= URLROOT ?>/admin/packageStartEdit/<?= (int)$package['package_id'] ?>">
          <button class="btn-primary" type="submit">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Edit Package
          </button>
        </form>
      </div>

    <?php else: ?>
      <!-- Draft / editable form -->
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

        <div class="field">
          <label>Tagline</label>
          <input type="text" name="tagline" value="<?= $h($package['tagline'] ?? '') ?>">
        </div>

        <div class="field">
          <label>Base Price (MMK)</label>
          <input type="number" name="base_price" id="packagePriceInput" min="0" step="100"
                 value="<?= (float)$packageBasePrice ?>" placeholder="<?= (float)$includedTotal ?>">
          <div class="stat-sub" style="margin-top:4px">Customer-facing price = base + <?= (int)($agentFeeRate * 100) ?>% agent fee, calculated automatically.</div>
        </div>

        <div class="field">
          <label>Package bookings per event date</label>
          <input type="number" name="max_concurrent" min="0" step="1"
                 value="<?= (int)($package['max_concurrent'] ?? 0) ?>">
          <div class="stat-sub" style="margin-top:4px">How many customers can book this whole package for the same wedding date. 0 = unlimited.</div>
        </div>

        <div class="field">
          <label>Description</label>
          <textarea name="description"><?= $h($package['description'] ?? '') ?></textarea>
        </div>

        <div class="field">
          <label>Package Cover</label>
          <div class="cover-uploader <?= !empty($package['image_url']) ? 'has-image' : '' ?>" id="packageCoverUploader" data-existing-image="<?= $h($package['image_url'] ?? '') ?>">
            <input class="cover-uploader-input" id="packageCoverInput" type="file" name="package_image" accept="image/jpeg,image/png,image/webp">
            <label class="cover-uploader-label" for="packageCoverInput">
              <span class="cover-uploader-empty">
                <span class="cover-upload-icon" aria-hidden="true">
                  <svg width="58" height="44" viewBox="0 0 58 44" fill="none"><path d="M46.5 19.2A14.5 14.5 0 0 0 18.7 14 10.5 10.5 0 0 0 20 35h25.5a8 8 0 0 0 1-15.8Z" fill="currentColor"/><path d="m29 14-7 8h4v8h6v-8h4l-7-8Z" fill="#fff"/></svg>
                </span>
                <span class="cover-upload-title">Choose an image or <span>drag &amp; drop it here</span></span>
                <span class="cover-upload-help">JPG, PNG or WebP · Up to 6MB</span>
                <span class="cover-upload-button">Browse files</span>
              </span>
            </label>
            <div class="cover-uploader-preview">
              <img id="packageCoverPreview" src="<?= $h($package['image_url'] ?? '') ?>" alt="<?= $h($package['name'] ?? 'Package') ?> cover">
              <div class="cover-preview-shade">
                <span class="cover-preview-name" id="packageCoverName"><?= !empty($package['image_url']) ? 'Current package cover' : 'Package cover' ?></span>
                <button class="cover-preview-change" type="button" id="packageCoverChange">Change cover</button>
              </div>
            </div>
          </div>
          <p class="cover-upload-error" id="packageCoverError" role="alert"></p>
          <div class="service-meta" style="margin-top:5px">Selecting a new image replaces the current cover after you save.</div>
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

        <div style="margin-top:16px;display:flex;gap:10px">
          <button class="btn-primary" id="packageSaveButton" type="submit" disabled>Save Changes</button>
        </div>
      </form>
    <?php endif; ?>
  </div><!-- /card -->

  <!-- ── Included services ─────────────────────────────────────────────── -->
  <div class="card included-card">
    <div class="included-card-head">
      <div>
        <h2 class="included-card-title">Included Services</h2>
        <div class="included-card-sub">Review suppliers, selected options, quantities, and pricing.</div>
      </div>
      <div class="included-card-actions">
        <?php if (!empty($package['items'])): ?>
          <div class="included-card-total">
            <span><?= count($package['items']) ?> services · Package cost</span>
            <strong><?= $money($includedTotal) ?></strong>
          </div>
        <?php endif; ?>
        <?php if ($isDraft && !empty($addableServices)): ?>
          <button class="btn-primary" id="openServiceCatalog" type="button">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
            Add service
          </button>
        <?php endif; ?>
      </div>
    </div>

    <?php if (empty($package['items'])): ?>
      <div class="service-empty" style="margin:24px">No services added yet.<?= $isDraft ? ' Use Add service to build this package.' : '' ?></div>
    <?php else: ?>
      <div class="included-list">
        <?php foreach (($package['items'] ?? []) as $itemIndex => $item):
          $isGuestPriced = ($item['quantity_type'] ?? '') === 'guests';
          $itemPkgPrice = (float)($item['default_price'] ?? 0);
          $itemCustPrice = (float)($item['customize_price'] ?? $itemPkgPrice);
          $isRentalSvc = in_array(strtolower(trim((string)($item['category_slug'] ?? ''))), ['attire'], true)
                      || in_array(strtolower(trim((string)($item['category_name'] ?? ''))), ['attire'], true);
          $itemHallOptions = $hallOptionsByService[(int)($item['service_id'] ?? 0)] ?? [];
          $quantity = max(1, (int)($item['quantity'] ?? 1));
        ?>
          <article class="included-item">
            <div class="included-item-top">
              <div class="included-identity">
                <span class="included-number"><?= str_pad((string)($itemIndex + 1), 2, '0', STR_PAD_LEFT) ?></span>
                <div>
                  <div class="included-service-name"><?= $h($item['service_name'] ?? 'Service') ?></div>
                  <div class="included-service-meta">
                    <span><?= $h($item['default_supplier_name'] ?? 'Supplier not assigned') ?></span>
                    <span class="included-dot"></span>
                    <span>Service #<?= (int)($item['service_id'] ?? 0) ?></span>
                    <?php if (!empty($item['category_name'])): ?>
                      <span class="included-category"><?= $h($item['category_name']) ?></span>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
              <?php if ($isDraft): ?>
                <form class="included-remove remove-item-form" method="POST"
                      action="<?= URLROOT ?>/admin/packageRemoveItem/<?= (int)$item['id'] ?>"
                      data-service-name="<?= $h($item['service_name'] ?? 'This service') ?>">
                  <button class="btn-ghost btn-sm btn-danger" type="submit">Remove</button>
                </form>
              <?php endif; ?>
            </div>

            <div class="included-grid">
              <div class="included-panel">
                <div class="included-panel-label">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-5h6v5"/></svg>
                  Service details
                </div>
                <?php if (!empty($item['hall_name'])): ?>
                  <div class="included-panel-value"><?= $h($item['hall_name']) ?></div>
                  <div class="included-panel-note"><?= !empty($item['hall_capacity']) ? 'Up to ' . (int)$item['hall_capacity'] . ' guests' : 'Selected venue hall' ?></div>
                <?php elseif (!empty($item['attire_item_name'])): ?>
                  <div class="included-panel-value"><?= $h($item['attire_item_name']) ?></div>
                  <div class="included-panel-note">Selected attire item</div>
                <?php elseif (!empty($item['decoration_style_name'])): ?>
                  <div class="included-panel-value"><?= $h($item['decoration_style_name']) ?></div>
                  <div class="included-panel-note">Selected decoration style</div>
                <?php elseif (!empty($itemHallOptions)): ?>
                  <div class="included-panel-value">No specific hall selected</div>
                  <div class="included-panel-note">Choose which hall this package includes.</div>
                <?php else: ?>
                  <div class="included-panel-value"><?= $isGuestPriced ? 'Guest-based service' : 'Fixed service' ?></div>
                  <div class="included-panel-note"><?= $isGuestPriced ? 'Price adjusts with guest count.' : 'Included once in this package.' ?></div>
                <?php endif; ?>

                <?php if ($isDraft && !empty($itemHallOptions)): ?>
                  <form class="included-hall-form" method="POST"
                        action="<?= URLROOT ?>/admin/packageUpdateItem/<?= (int)$item['id'] ?>">
                    <select name="hall_id" class="guest-input" aria-label="Choose hall">
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

                <?php if ($isRentalSvc): ?>
                  <div class="included-rental"><?= $rentalPricingHtml($item) ?></div>
                <?php endif; ?>
              </div>

              <div class="included-panel">
                <div class="included-panel-label">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                  <?= $isGuestPriced ? 'Guests' : 'Quantity' ?>
                </div>
                <?php if ($isDraft): ?>
                  <form class="guest-form" method="POST"
                        action="<?= URLROOT ?>/admin/packageUpdateItem/<?= (int)$item['id'] ?>">
                    <input class="guest-input" type="number" name="quantity" min="1" step="1"
                           value="<?= $quantity ?>" aria-label="<?= $isGuestPriced ? 'Guest count' : 'Quantity' ?>">
                    <button class="btn-ghost btn-sm" type="submit">Update</button>
                  </form>
                <?php else: ?>
                  <div class="included-panel-value"><?= $isGuestPriced ? number_format($quantity) . ' guests' : number_format($quantity) ?></div>
                  <div class="included-panel-note"><?= $isGuestPriced ? 'Used for per-guest pricing.' : 'Included quantity.' ?></div>
                <?php endif; ?>
              </div>

              <div class="included-prices">
                <div class="included-price is-primary">
                  <span>Package price</span>
                  <strong><?= $money($itemPkgPrice) ?></strong>
                  <small><?= $isGuestPriced ? $money($itemPkgPrice / $quantity) . ' per guest' : 'Included package rate' ?></small>
                </div>
                <div class="included-price">
                  <span>Package slot (per booking)</span>
                  <?php
                    $itemMaxConcurrent = (int)($item['item_max_concurrent'] ?? 0);
                    $svcMaxConcurrentPkg = (int)($item['service_max_concurrent_package'] ?? 0);
                    $effectiveCap = $itemMaxConcurrent > 0 ? $itemMaxConcurrent : $svcMaxConcurrentPkg;
                    $supplierLabel = $svcMaxConcurrentPkg > 0
                        ? 'Supplier allows ' . $svcMaxConcurrentPkg . ' per slot'
                        : 'Supplier has no package limit set';
                  ?>
                  <?php if ($isDraft): ?>
                    <small style="display:block;margin-bottom:5px;color:var(--muted);font-size:10px"><?= $supplierLabel ?></small>
                    <form class="guest-form" method="POST"
                          action="<?= URLROOT ?>/admin/packageUpdateItem/<?= (int)$item['id'] ?>"
                          style="margin-top:0">
                      <input class="guest-input" type="number" name="max_concurrent" min="0" max="65535" step="1"
                             value="<?= $itemMaxConcurrent ?>" aria-label="Package slot limit"
                             style="width:80px!important">
                      <button class="btn-ghost btn-sm" type="submit">Update</button>
                    </form>
                    <small style="margin-top:6px;display:block"><?= $itemMaxConcurrent > 0 ? 'Override: ' . $itemMaxConcurrent . ' bookings per slot' : '0 = no override (uses supplier default)' ?></small>
                  <?php else: ?>
                    <strong><?= $effectiveCap > 0 ? $effectiveCap : '—' ?></strong>
                    <small><?= $supplierLabel ?><?= $itemMaxConcurrent > 0 ? ' · Admin override: ' . $itemMaxConcurrent : ' · No override' ?></small>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if ($isDraft): ?>
      <div class="draft-actions">
        <div class="draft-actions-copy">
          <strong>Ready to make these services live?</strong>
          <span>Publishing replaces the current package version.</span>
        </div>
        <div class="draft-actions-buttons">
          <form id="publishPackageForm" method="POST" action="<?= URLROOT ?>/admin/packagePublishDraft/<?= (int)$package['package_id'] ?>">
            <button class="btn-primary" type="submit">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
              Publish package
            </button>
          </form>
          <form method="POST" action="<?= URLROOT ?>/admin/packageDiscardDraft/<?= (int)$package['package_id'] ?>"
                onsubmit="return confirm('Discard this draft? All unsaved changes will be lost.')">
            <button class="btn-ghost btn-danger" type="submit">Discard draft</button>
          </form>
        </div>
      </div>
    <?php endif; ?>

    <!-- ── Add-service panel ──────────────────────────────────────────── -->
    <?php if ($isDraft && !empty($addableServices)): ?>
    <div class="service-catalog-modal" id="serviceCatalogModal" aria-hidden="true">
      <div class="service-catalog-modal-backdrop" data-close-service-catalog></div>
      <section class="add-svc-panel" role="dialog" aria-modal="true" aria-labelledby="serviceCatalogTitle">
      <?php
        $catalogCategories = [];
        $catalogSuppliers = [];
        foreach ($addableServices as $catalogService) {
          $categoryName = trim((string)($catalogService['category_name'] ?? 'Other')) ?: 'Other';
          $supplierName = trim((string)($catalogService['supplier_name'] ?? 'Unknown supplier')) ?: 'Unknown supplier';
          $catalogCategories[$categoryName] = true;
          $catalogSuppliers[$supplierName] = true;
        }
        $catalogCategories = array_keys($catalogCategories);
        $catalogSuppliers = array_keys($catalogSuppliers);
        natcasesort($catalogCategories);
        natcasesort($catalogSuppliers);
      ?>
      <div class="add-svc-panel-head">
        <div>
          <div class="add-svc-panel-title" id="serviceCatalogTitle">Service catalog</div>
          <div class="add-svc-panel-copy">Find a service, review its available options, then add it to this package.</div>
        </div>
        <div class="add-svc-panel-head-actions">
          <div class="add-svc-panel-count"><?= count($addableServices) ?> available</div>
          <button class="service-modal-close" type="button" data-close-service-catalog aria-label="Close service catalog">×</button>
        </div>
      </div>

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

        <!-- Native field retained as the submitted source of truth. -->
        <select name="service_id" id="serviceSelect" hidden onchange="onServiceChange(this)">
            <option value="">Select a service…</option>
            <?php foreach ($addableServices as $svc):
              $svcId = (int)($svc['id'] ?? 0);
              $categoryLabel = strtolower((string)($svc['category_slug'] ?? '') . ' ' . (string)($svc['category_name'] ?? ''));
              $isAttireSvc = str_contains($categoryLabel, 'attire');
              $attireCount = count($attireOptionsByService[$svcId] ?? []);
              $isGuestPricedSvc = str_contains($categoryLabel, 'food')
                        || str_contains($categoryLabel, 'cater')
                        || str_contains($categoryLabel, 'decor')
                        || str_contains($categoryLabel, 'music')
                        || str_contains($categoryLabel, 'photo')
                        || str_contains($categoryLabel, 'makeup')
                        || str_contains($categoryLabel, 'studio');
              $priceLabel = $isAttireSvc
                ? ($attireCount > 0
                  ? $attireCount . ' dress ' . ($attireCount === 1 ? 'design' : 'designs') . ' available'
                  : 'No dress designs added')
                : $money($svc['display_price'] ?? 0) . ($isGuestPricedSvc ? ' per guest' : '');
            ?>
              <option value="<?= $svcId ?>"
                      data-name="<?= $h($svc['name'] ?? '') ?>"
                      data-supplier="<?= $h($svc['supplier_name'] ?? '') ?>"
                      data-price="<?= $h($priceLabel) ?>"
                      data-category-id="<?= (int)($svc['category_id'] ?? 0) ?>"
                      data-category-name="<?= $h($svc['category_name'] ?? 'this category') ?>"
                      data-guest-priced="<?= $isGuestPricedSvc ? '1' : '0' ?>"
                      data-attire="<?= $isAttireSvc ? '1' : '0' ?>"
                      data-room-count="<?= count($hallOptionsByService[$svcId] ?? []) ?>"
                      data-attire-count="<?= $attireCount ?>"
                      data-deco-count="<?= count($decoOptionsByService[$svcId] ?? []) ?>">
                <?= $h(
                  ($svc['category_name'] ?? 'Service')
                  . ' — #' . $svcId
                  . ' — ' . ($svc['name'] ?? '')
                  . ' — ' . ($svc['supplier_name'] ?? '')
                  . ' — ' . $priceLabel
                ) ?>
              </option>
            <?php endforeach; ?>
        </select>

        <div class="service-catalog">
          <aside class="service-catalog-browser" aria-label="Available services">
            <div class="service-catalog-tools">
              <div class="service-search-wrap">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                <input class="service-search" id="serviceCatalogSearch" type="search"
                       placeholder="Search service or supplier…" autocomplete="off"
                       aria-label="Search available services">
                <button class="service-search-clear" id="serviceSearchClear" type="button" aria-label="Clear search">×</button>
              </div>
              <div class="service-filter-row">
                <select class="service-filter" id="serviceCategoryFilter" aria-label="Filter by category">
                  <option value="">All categories</option>
                  <?php foreach ($catalogCategories as $categoryName): ?>
                    <option value="<?= $h(strtolower($categoryName)) ?>"><?= $h($categoryName) ?></option>
                  <?php endforeach; ?>
                </select>
                <select class="service-filter" id="serviceSupplierFilter" aria-label="Filter by supplier">
                  <option value="">All suppliers</option>
                  <?php foreach ($catalogSuppliers as $supplierName): ?>
                    <option value="<?= $h(strtolower($supplierName)) ?>"><?= $h($supplierName) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <div class="service-results-meta">
              <span>Services</span>
              <span id="serviceResultCount"><?= count($addableServices) ?> results</span>
            </div>

            <div class="service-results" id="serviceCatalogResults">
              <?php foreach ($addableServices as $svc):
                $svcId = (int)($svc['id'] ?? 0);
                $serviceName = trim((string)($svc['name'] ?? 'Service'));
                $supplierName = trim((string)($svc['supplier_name'] ?? 'Unknown supplier'));
                $categoryName = trim((string)($svc['category_name'] ?? 'Other')) ?: 'Other';
                $categoryLabel = strtolower((string)($svc['category_slug'] ?? '') . ' ' . $categoryName);
                $isAttireSvc = str_contains($categoryLabel, 'attire');
                $attireCount = count($attireOptionsByService[$svcId] ?? []);
                $roomCount = count($hallOptionsByService[$svcId] ?? []);
                $decoCount = count($decoOptionsByService[$svcId] ?? []);
                $optionCount = $attireCount + $roomCount + $decoCount;
                $statusLabel = $isAttireSvc
                  ? ($attireCount > 0 ? $attireCount . ' design' . ($attireCount === 1 ? '' : 's') : 'No designs')
                  : ($optionCount > 0 ? $optionCount . ' option' . ($optionCount === 1 ? '' : 's') : $money($svc['display_price'] ?? 0));
                $searchText = strtolower(implode(' ', [
                  $serviceName, $supplierName, $categoryName, (string)$svcId,
                ]));
                $thumbnail = trim((string)($svc['thumbnail_url'] ?? $svc['image'] ?? ''));
              ?>
                <button class="service-result" type="button"
                        data-service-id="<?= $svcId ?>"
                        data-search="<?= $h($searchText) ?>"
                        data-category="<?= $h(strtolower($categoryName)) ?>"
                        data-supplier="<?= $h(strtolower($supplierName)) ?>"
                        aria-pressed="false">
                  <span class="service-result-mark">
                    <?php if ($thumbnail !== ''): ?>
                      <img src="<?= $h($thumbnail) ?>" alt="">
                    <?php else: ?>
                      <?= $h(strtoupper(substr($categoryName, 0, 2))) ?>
                    <?php endif; ?>
                  </span>
                  <span class="service-result-main">
                    <span class="service-result-name"><?= $h($serviceName) ?></span>
                    <span class="service-result-supplier"><?= $h($supplierName) ?> · #<?= $svcId ?></span>
                    <span class="service-result-tags">
                      <span class="service-result-tag"><?= $h($categoryName) ?></span>
                    </span>
                  </span>
                  <span class="service-result-status <?= $optionCount > 0 ? 'ready' : '' ?>"><?= $h($statusLabel) ?></span>
                </button>
              <?php endforeach; ?>
              <div class="service-results-empty" id="serviceResultsEmpty">
                No services match these filters.<br>Try a shorter name or clear one of the filters.
              </div>
            </div>
          </aside>

          <section class="service-catalog-detail" aria-live="polite">
            <div class="service-detail-empty" id="serviceDetailEmpty">
              <div class="service-detail-empty-icon">
                <svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M7 4v6M17 4v6M5 10h14v10H5z"/><path d="M9 14h6"/></svg>
              </div>
              <strong>Choose a service to review</strong>
              <span>Search by service name or supplier, then select a result to see dress designs, halls, styles, and pricing details.</span>
            </div>

            <!-- Selected service workspace -->
            <div id="svcPreview" class="service-detail-workspace">
              <div class="svc-selected-preview">
            <div class="svc-selected-preview-icon">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <div class="svc-selected-preview-copy">
              <div class="svc-selected-preview-eyebrow">Selected service</div>
              <div class="svc-selected-preview-name" id="svcPreviewName"></div>
              <div class="svc-selected-preview-meta" id="svcPreviewMeta"></div>
            </div>
            <div class="svc-selected-preview-price" id="svcPreviewPrice"></div>
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

        <!-- ── Step 2 for ATTIRE: Item picker ────────────────────────── -->
        <div class="hall-picker" id="attirePicker">
          <div class="hall-picker-label">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="6" width="20" height="12" rx="1"/><path d="M6 10h2l1 4h6l1-4h2"/></svg>
            Select a dress design and its listed price
          </div>
          <div class="hall-grid" id="attireCards"></div>
          <input type="hidden" name="attire_item_id" id="attireItemIdHidden" value="">
        </div><!-- /attirePicker -->

        <!-- ── Step 2 for DECORATION: Style picker ───────────────────── -->
        <div class="hall-picker" id="decoPicker">
          <div class="hall-picker-label">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/></svg>
            Select a decoration style
          </div>
          <div class="hall-grid" id="decoCards"></div>
          <input type="hidden" name="decoration_style_id" id="decoStyleIdHidden" value="">
        </div><!-- /decoPicker -->

        <!-- ── Step 2 for guest-priced services: quantity / guest count ─ -->
        <div id="guestCountRow" class="guest-count-row" style="display:none">
          <label for="guestCountInput">Guests / quantity for pricing</label>
          <input id="guestCountInput" type="number" name="guest_count" min="1" step="1" value="100">
          <span class="guest-count-note">Used for guest-priced services, such as catering for 300 guests or makeup for 3 people. This updates the package total.</span>
        </div>
        <input type="hidden" name="guest_count" id="guestCountHidden" value="100">

        <!-- ── Per-item concurrency override ─────────────────────────── -->
        <div id="itemConcurrentRow" class="guest-count-row" style="display:none">
          <label for="itemConcurrentInput">Package bookings per slot</label>
          <input id="itemConcurrentInput" type="number" name="max_concurrent" min="0" max="65535" step="1" value="0">
          <span class="guest-count-note">Optional capacity override for this included service in the same generated time slot. 0 = use the supplier service default.</span>
        </div>

        <!-- Confirm / Add button -->
        <div class="add-svc-actions">
          <button class="btn-primary" type="submit" id="addSvcBtn" disabled>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add to Package
          </button>
          <span id="addSvcHint" style="font-size:11px;color:var(--muted)">Select a service above to continue.</span>
        </div>

            </div><!-- /service-detail-workspace -->
          </section>
        </div><!-- /service-catalog -->
      </form>
      </section>
    </div>
    <?php endif; ?><!-- /addableServices + isDraft check -->

  </div><!-- /card -->

</div><!-- /admin-pkg-page -->

<?php if ($isDraft): ?>
  <div class="publish-modal" id="removeItemModal" aria-hidden="true">
    <div class="publish-modal-backdrop" data-close-remove-modal></div>
    <section class="publish-modal-dialog" role="dialog" aria-modal="true"
             aria-labelledby="removeModalTitle" aria-describedby="removeModalDescription">
      <div class="publish-modal-body">
        <div class="publish-modal-icon" style="background:var(--danger-bg);color:var(--danger)">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
        </div>
        <h2 class="publish-modal-title" id="removeModalTitle">Remove service?</h2>
        <p class="publish-modal-copy" id="removeModalDescription">
          <strong id="removeItemName"></strong> will be removed from this package.
        </p>
      </div>
      <div class="publish-modal-actions">
        <button class="btn-ghost" type="button" data-close-remove-modal>Cancel</button>
        <button class="btn-primary btn-danger-fill" id="confirmRemoveItem" type="button">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
          Remove
        </button>
      </div>
    </section>
  </div>
<?php endif; ?>

<?php if (!$isDraft): ?>
  <div class="publish-modal" id="editConfirmModal" aria-hidden="true">
    <div class="publish-modal-backdrop" data-close-edit-modal></div>
    <section class="publish-modal-dialog" role="dialog" aria-modal="true"
             aria-labelledby="editModalTitle" aria-describedby="editModalDescription">
      <div class="publish-modal-body">
        <div class="publish-modal-icon">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
        </div>
        <h2 class="publish-modal-title" id="editModalTitle">Enter edit mode?</h2>
        <p class="publish-modal-copy" id="editModalDescription">
          A draft copy of <strong><?= $h($package['name'] ?? 'this package') ?></strong> will be created. The live package stays visible to customers while you edit.
        </p>
      </div>
      <div class="publish-modal-actions">
        <button class="btn-ghost" type="button" data-close-edit-modal>Cancel</button>
        <button class="btn-primary" id="confirmEditPackage" type="button">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
          Edit package
        </button>
      </div>
    </section>
  </div>
<?php endif; ?>

<?php if ($isDraft): ?>
  <div class="publish-modal" id="publishPackageModal" aria-hidden="true">
    <div class="publish-modal-backdrop" data-close-publish-modal></div>
    <section class="publish-modal-dialog" role="dialog" aria-modal="true"
             aria-labelledby="publishModalTitle" aria-describedby="publishModalDescription">
      <div class="publish-modal-body">
        <div class="publish-modal-icon">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12"/><path d="m7 10 5 5 5-5"/><path d="M5 21h14"/></svg>
        </div>
        <h2 class="publish-modal-title" id="publishModalTitle">Publish this package?</h2>
        <p class="publish-modal-copy" id="publishModalDescription">
          <strong><?= $h($package['name'] ?? 'This package') ?></strong> will become visible to customers immediately.
        </p>
        <div class="publish-modal-note">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>
          <span>This draft will replace the currently published version. Review the package details before continuing.</span>
        </div>
      </div>
      <div class="publish-modal-actions">
        <button class="btn-ghost" type="button" data-close-publish-modal>Keep editing</button>
        <button class="btn-primary" id="confirmPublishPackage" type="button">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
          Publish now
        </button>
      </div>
    </section>
  </div>
<?php endif; ?>

<script>
(function () {
  /* ── Remove-item confirmation ──────────────────────────────────────── */
  const removeModal = document.getElementById('removeItemModal');
  const removeItemName = document.getElementById('removeItemName');
  const confirmRemoveButton = document.getElementById('confirmRemoveItem');
  let pendingRemoveForm = null;

  function openRemoveModal(serviceName, form) {
    if (!removeModal) return;
    pendingRemoveForm = form;
    if (removeItemName) removeItemName.textContent = serviceName;
    removeModal.classList.add('is-open');
    removeModal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
    confirmRemoveButton?.focus();
  }

  function closeRemoveModal() {
    if (!removeModal) return;
    removeModal.classList.remove('is-open');
    removeModal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
    pendingRemoveForm = null;
  }

  document.querySelectorAll('.remove-item-form').forEach(form => {
    form.addEventListener('submit', event => {
      event.preventDefault();
      openRemoveModal(form.dataset.serviceName || 'This service', form);
    });
  });

  confirmRemoveButton?.addEventListener('click', () => {
    if (!pendingRemoveForm) return;
    confirmRemoveButton.disabled = true;
    confirmRemoveButton.textContent = 'Removing…';
    pendingRemoveForm.submit();
  });

  removeModal?.querySelectorAll('[data-close-remove-modal]').forEach(element => {
    element.addEventListener('click', closeRemoveModal);
  });

  document.addEventListener('keydown', event => {
    if (event.key === 'Escape' && removeModal?.classList.contains('is-open')) {
      closeRemoveModal();
    }
  });

  /* ── Edit confirmation ─────────────────────────────────────────────── */
  const editForm = document.getElementById('editPackageForm');
  const editModal = document.getElementById('editConfirmModal');
  const confirmEditButton = document.getElementById('confirmEditPackage');
  let editConfirmed = false;
  let editTrigger = null;

  function openEditModal() {
    if (!editModal) return;
    editTrigger = document.activeElement;
    editModal.classList.add('is-open');
    editModal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
    confirmEditButton?.focus();
  }

  function closeEditModal() {
    if (!editModal) return;
    editModal.classList.remove('is-open');
    editModal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
    editTrigger?.focus();
  }

  editForm?.addEventListener('submit', event => {
    if (editConfirmed) return;
    event.preventDefault();
    openEditModal();
  });

  confirmEditButton?.addEventListener('click', () => {
    if (!editForm) return;
    editConfirmed = true;
    confirmEditButton.disabled = true;
    confirmEditButton.textContent = 'Opening…';
    editForm.requestSubmit();
  });

  editModal?.querySelectorAll('[data-close-edit-modal]').forEach(element => {
    element.addEventListener('click', closeEditModal);
  });

  document.addEventListener('keydown', event => {
    if (event.key === 'Escape' && editModal?.classList.contains('is-open')) {
      closeEditModal();
    }
  });

  /* ── Publish confirmation ───────────────────────────────────────────── */
  const publishForm = document.getElementById('publishPackageForm');
  const publishModal = document.getElementById('publishPackageModal');
  const confirmPublishButton = document.getElementById('confirmPublishPackage');
  let publishConfirmed = false;
  let publishTrigger = null;

  function openPublishModal() {
    if (!publishModal) return;
    publishTrigger = document.activeElement;
    publishModal.classList.add('is-open');
    publishModal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
    confirmPublishButton?.focus();
  }

  function closePublishModal() {
    if (!publishModal) return;
    publishModal.classList.remove('is-open');
    publishModal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
    publishTrigger?.focus();
  }

  publishForm?.addEventListener('submit', event => {
    if (publishConfirmed) return;
    event.preventDefault();
    openPublishModal();
  });

  confirmPublishButton?.addEventListener('click', () => {
    if (!publishForm) return;
    publishConfirmed = true;
    confirmPublishButton.disabled = true;
    confirmPublishButton.textContent = 'Publishing…';
    publishForm.requestSubmit();
  });

  publishModal?.querySelectorAll('[data-close-publish-modal]').forEach(element => {
    element.addEventListener('click', closePublishModal);
  });

  document.addEventListener('keydown', event => {
    if (event.key === 'Escape' && publishModal?.classList.contains('is-open')) {
      closePublishModal();
    }
  });

  /* ── Price card live update ─────────────────────────────────────────── */
  const priceInput = document.getElementById('packagePriceInput');
  const priceCardVal = document.getElementById('packagePriceCardValue');
  const priceCardSub = document.getElementById('packagePriceCardSub');
  const agentFeeRate = <?= $agentFeeRate ?>;

  function initPackageCoverUploader() {
    const uploader = document.getElementById('packageCoverUploader');
    const input = document.getElementById('packageCoverInput');
    const preview = document.getElementById('packageCoverPreview');
    const fileName = document.getElementById('packageCoverName');
    const changeButton = document.getElementById('packageCoverChange');
    const error = document.getElementById('packageCoverError');
    if (!uploader || !input || !preview) return;

    const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    const maxSize = 6 * 1024 * 1024;

    function showError(message) {
      error.textContent = message;
      error.classList.toggle('is-visible', Boolean(message));
    }

    function setFile(file) {
      if (!file) return;
      if (!allowedTypes.includes(file.type)) {
        input.value = '';
        showError('Choose a JPG, PNG, or WebP image.');
        return;
      }
      if (file.size > maxSize) {
        input.value = '';
        showError('The cover image must be 6MB or smaller.');
        return;
      }
      showError('');
      preview.src = URL.createObjectURL(file);
      fileName.textContent = file.name;
      uploader.classList.add('has-image');
      input.dispatchEvent(new Event('input', { bubbles: true }));
    }

    input.addEventListener('change', () => setFile(input.files[0]));
    changeButton.addEventListener('click', event => {
      event.preventDefault();
      input.click();
    });
    ['dragenter', 'dragover'].forEach(type => uploader.addEventListener(type, event => {
      event.preventDefault();
      uploader.classList.add('is-dragging');
    }));
    ['dragleave', 'drop'].forEach(type => uploader.addEventListener(type, event => {
      event.preventDefault();
      uploader.classList.remove('is-dragging');
    }));
    uploader.addEventListener('drop', event => {
      const file = event.dataTransfer?.files?.[0];
      if (!file) return;
      const transfer = new DataTransfer();
      transfer.items.add(file);
      input.files = transfer.files;
      setFile(file);
    });
  }

  initPackageCoverUploader();

  function fmtMoney(v) {
    const n = Math.max(0, isFinite(+v) ? +v : 0);
    return 'MMK ' + n.toLocaleString('en-US', { maximumFractionDigits: 0 });
  }

  function updatePriceCard() {
    const base = Math.max(0, isFinite(+priceInput.value) ? +priceInput.value : 0);
    const fee = base * agentFeeRate;
    priceCardVal.textContent = fmtMoney(base + fee);
    priceCardSub.textContent = `Base ${fmtMoney(base)} + ${Math.round(agentFeeRate*100)}% agent fee ${fmtMoney(fee)}`;
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
  const serviceCatalogModal = document.getElementById('serviceCatalogModal');
  const openServiceCatalogButton = document.getElementById('openServiceCatalog');
  let serviceCatalogTrigger = null;
  if (serviceCatalogModal) {
    document.body.appendChild(serviceCatalogModal);
  }

  function openServiceCatalog() {
    if (!serviceCatalogModal) return;
    serviceCatalogTrigger = document.activeElement;
    serviceCatalogModal.classList.add('is-open');
    serviceCatalogModal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
    window.setTimeout(() => catalogSearch?.focus(), 0);
  }

  function closeServiceCatalog() {
    if (!serviceCatalogModal) return;
    serviceCatalogModal.classList.remove('is-open');
    serviceCatalogModal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
    serviceCatalogTrigger?.focus();
  }

  openServiceCatalogButton?.addEventListener('click', openServiceCatalog);
  serviceCatalogModal?.querySelectorAll('[data-close-service-catalog]').forEach(element => {
    element.addEventListener('click', closeServiceCatalog);
  });

  const serviceSelect = document.getElementById('serviceSelect');
  const svcPreview    = document.getElementById('svcPreview');
  const serviceDetailEmpty = document.getElementById('serviceDetailEmpty');
  const svcPreviewName = document.getElementById('svcPreviewName');
  const svcPreviewMeta = document.getElementById('svcPreviewMeta');
  const svcPreviewPrice = document.getElementById('svcPreviewPrice');
  const addSvcBtn     = document.getElementById('addSvcBtn');
  const addSvcHint    = document.getElementById('addSvcHint');
  const hallPicker    = document.getElementById('hallPicker');
  const hallCards     = document.getElementById('hallCards');
  const guestCountRow = document.getElementById('guestCountRow');
  const guestCountHidden = document.getElementById('guestCountHidden');
  const guestCountInput  = document.getElementById('guestCountInput');
  const addServiceForm = document.getElementById('addServiceForm');
  const hallOptionsByService = <?= json_encode($hallOptionsByService, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
  const attireOptionsByService = <?= json_encode($attireOptionsByService, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
  const decoOptionsByService = <?= json_encode($decoOptionsByService, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
  const includedCategoryNames = <?= json_encode($includedCategoryNames, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
  const catalogSearch = document.getElementById('serviceCatalogSearch');
  const catalogSearchClear = document.getElementById('serviceSearchClear');
  const catalogCategoryFilter = document.getElementById('serviceCategoryFilter');
  const catalogSupplierFilter = document.getElementById('serviceSupplierFilter');
  const catalogResultCount = document.getElementById('serviceResultCount');
  const catalogEmpty = document.getElementById('serviceResultsEmpty');
  const catalogResults = Array.from(document.querySelectorAll('.service-result'));

  document.addEventListener('keydown', event => {
    if (event.key === 'Escape' && serviceCatalogModal?.classList.contains('is-open')) {
      closeServiceCatalog();
    }
  });

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

  function filterServiceCatalog() {
    const query = (catalogSearch?.value || '').trim().toLocaleLowerCase();
    const category = catalogCategoryFilter?.value || '';
    const supplier = catalogSupplierFilter?.value || '';
    let visibleCount = 0;

    catalogResults.forEach(result => {
      const matchesSearch = !query || (result.dataset.search || '').includes(query);
      const matchesCategory = !category || result.dataset.category === category;
      const matchesSupplier = !supplier || result.dataset.supplier === supplier;
      const visible = matchesSearch && matchesCategory && matchesSupplier;
      result.hidden = !visible;
      if (visible) visibleCount++;
    });

    if (catalogResultCount) {
      catalogResultCount.textContent = visibleCount + (visibleCount === 1 ? ' result' : ' results');
    }
    catalogEmpty?.classList.toggle('visible', visibleCount === 0);
    catalogSearchClear?.classList.toggle('visible', Boolean(catalogSearch?.value));
  }

  function chooseCatalogService(serviceId) {
    if (!serviceSelect) return;
    serviceSelect.value = String(serviceId);
    catalogResults.forEach(result => {
      const selected = result.dataset.serviceId === String(serviceId);
      result.classList.toggle('selected', selected);
      result.setAttribute('aria-pressed', selected ? 'true' : 'false');
    });
    serviceSelect.dispatchEvent(new Event('change', { bubbles: true }));
  }

  catalogResults.forEach(result => {
    result.addEventListener('click', () => chooseCatalogService(result.dataset.serviceId));
  });
  [catalogSearch, catalogCategoryFilter, catalogSupplierFilter].forEach(control => {
    control?.addEventListener(control === catalogSearch ? 'input' : 'change', filterServiceCatalog);
  });
  catalogSearchClear?.addEventListener('click', () => {
    if (!catalogSearch) return;
    catalogSearch.value = '';
    catalogSearch.focus();
    filterServiceCatalog();
  });
  filterServiceCatalog();

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
    const isGuestPriced = hasVal && opt.dataset.guestPriced === '1';

    if (hasVal) {
      svcPreviewName.textContent = opt.dataset.name || '';
      svcPreviewMeta.textContent = (opt.dataset.supplier || '') + ' · Service #' + sel.value + ' · ' + (opt.dataset.categoryName || 'Service');
      svcPreviewPrice.textContent = opt.dataset.price || '';
      svcPreview.classList.add('visible');
      serviceDetailEmpty?.setAttribute('hidden', '');
    } else {
      svcPreview.classList.remove('visible');
      serviceDetailEmpty?.removeAttribute('hidden');
    }

    const roomCount = parseInt(opt.dataset.roomCount || '0', 10);
    const hasRooms = hasVal && roomCount > 0;

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

    /* attire services: show attire picker */
    const attireCount = parseInt(opt.dataset.attireCount || '0', 10);
    const hasAttire = hasVal && attireCount > 0;
    const isAttire = hasVal && opt.dataset.attire === '1';
    if (attirePicker) {
      if (hasAttire) {
        attirePicker.classList.add('visible');
        renderAttireOptions(sel.value);
      } else {
        attirePicker.classList.remove('visible');
        selectAttireItem(null);
      }
    }

    /* decoration services: show style picker */
    const decoCount = parseInt(opt.dataset.decoCount || '0', 10);
    const hasDeco = hasVal && decoCount > 0;
    if (decoPicker) {
      if (hasDeco) {
        decoPicker.classList.add('visible');
        renderDecoOptions(sel.value);
      } else {
        decoPicker.classList.remove('visible');
        selectDecoStyle(null);
      }
    }

    /* show guest count only when it affects per-guest pricing */
    if (guestCountRow) {
      guestCountRow.style.display = isGuestPriced ? 'flex' : 'none';
      if (guestCountInput && guestCountHidden) {
        guestCountInput.name = isGuestPriced ? 'guest_count' : '';
        guestCountHidden.name = hasVal && !isGuestPriced ? 'guest_count' : '';
        guestCountHidden.value = hasVal && !isGuestPriced ? '1' : '';
      }
    }

    /* show per-item concurrency override for all services */
    const itemConcurrentRow = document.getElementById('itemConcurrentRow');
    if (itemConcurrentRow) {
      itemConcurrentRow.style.display = hasVal ? 'flex' : 'none';
    }

    if (hasVal) {
      setStep(hasRooms ? 3 : 2);
      addSvcBtn.disabled = isAttire;
      addSvcHint.textContent = hasAttire
        ? 'Select a dress design before adding this attire service.'
        : isAttire
        ? 'This attire service has no dress designs. Add designs from the supplier service first.'
        : hasRooms
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

  /* Attire item picker */
  const attirePicker = document.getElementById('attirePicker');
  const attireCards = document.getElementById('attireCards');
  const attireItemHidden = document.getElementById('attireItemIdHidden');

  function renderAttireOptions(serviceId) {
    if (!attireCards) return;
    attireCards.innerHTML = '';
    const items = attireOptionsByService[String(serviceId)] || attireOptionsByService[serviceId] || [];
    if (!items.length) {
      attireCards.innerHTML = '<div class="service-meta" style="grid-column:1/-1;padding:16px 0;color:var(--muted)">No individual items set up for this attire service yet.</div>';
      selectAttireItem(null);
      return;
    }
    items.forEach(item => {
      const id = parseInt(item.id || 0, 10);
      const card = document.createElement('label');
      card.className = 'hall-card';
      card.dataset.attireId = String(id);
      card.addEventListener('click', () => selectAttireItem(id));
      const borrow = Number(item.borrow_package_price || 0) > 0 ? 'MMK ' + Number(item.borrow_package_price).toLocaleString() : '';
      const buy = Number(item.buy_package_price || 0) > 0 ? 'MMK ' + Number(item.buy_package_price).toLocaleString() : '';
      const priceText = [borrow ? 'Borrow ' + borrow : '', buy ? 'Buy ' + buy : ''].filter(Boolean).join(' · ');
      const attireVisual = item.photo_url
        ? '<img src="' + String(item.photo_url).replace(/"/g, '&quot;') + '" alt="">'
        : '<div class="hall-card-img-placeholder"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="4" width="14" height="16" rx="1"/><path d="M5 8h14M5 12h14"/></svg></div>';
      card.innerHTML = '<input type="radio" name="attire_radio" value="' + id + '" style="position:absolute;opacity:0;pointer-events:none">' +
        '<div class="hall-card-img">' + attireVisual + '<div class="hall-card-check"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></div></div>' +
        '<div class="hall-card-body"><div class="hall-card-name">' + (item.name || 'Item') + '</div>' +
        '<div class="hall-card-meta">' + (priceText ? '<div class="hall-card-meta-row">' + priceText + '</div>' : '') +
        (item.return_days ? '<div class="hall-card-meta-row">Return: ' + item.return_days + (item.return_days === 1 ? ' day' : ' days') + '</div>' : '') +
        '</div></div>';
      attireCards.appendChild(card);
    });
    selectAttireItem(null);
  }

  window.selectAttireItem = function (itemId) {
    document.querySelectorAll('#attireCards .hall-card').forEach(c => c.classList.remove('selected'));
    const noneOpt = document.getElementById('attireNoneOption');
    if (itemId === null) {
      if (noneOpt) noneOpt.classList.add('selected');
      if (attireItemHidden) attireItemHidden.value = '';
      const selectedOption = serviceSelect?.options[serviceSelect.selectedIndex];
      if (selectedOption?.dataset.attire === '1') {
        addSvcBtn.disabled = true;
        addSvcHint.textContent = 'Select a dress design before adding this attire service.';
        setStep(2);
      }
    } else {
      if (noneOpt) noneOpt.classList.remove('selected');
      const card = document.querySelector('#attireCards .hall-card[data-attire-id="' + itemId + '"]');
      if (card) { card.classList.add('selected'); }
      if (attireItemHidden) attireItemHidden.value = itemId;
      addSvcBtn.disabled = false;
      addSvcHint.textContent = 'Dress design selected. Click Add to Package.';
      setStep(3);
    }
  };

  /* Decoration style picker */
  const decoPicker = document.getElementById('decoPicker');
  const decoCards = document.getElementById('decoCards');
  const decoStyleHidden = document.getElementById('decoStyleIdHidden');

  function renderDecoOptions(serviceId) {
    if (!decoCards) return;
    decoCards.innerHTML = '';
    const styles = decoOptionsByService[String(serviceId)] || decoOptionsByService[serviceId] || [];
    if (!styles.length) {
      decoCards.innerHTML = '<div class="service-meta" style="grid-column:1/-1;padding:16px 0;color:var(--muted)">No decoration styles set up for this service yet.</div>';
      selectDecoStyle(null);
      return;
    }
    styles.forEach(function(style) {
      var id = parseInt(style.id || 0, 10);
      var price = Number(style.package_price || style.price || 0);
      var priceText = price > 0 ? 'MMK ' + price.toLocaleString() : '';
      var card = document.createElement('label');
      card.className = 'hall-card';
      card.dataset.decoId = String(id);
      card.addEventListener('click', function() { selectDecoStyle(id); });
      card.innerHTML = '<input type="radio" name="deco_radio" value="' + id + '" style="position:absolute;opacity:0;pointer-events:none">' +
        '<div class="hall-card-img"><div class="hall-card-img-placeholder"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/></svg></div><div class="hall-card-check"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg></div></div>' +
        '<div class="hall-card-body"><div class="hall-card-name">' + (style.name || 'Style') + '</div>' +
        (priceText ? '<div class="hall-card-price">' + priceText + '</div>' : '') +
        '</div>';
      decoCards.appendChild(card);
    });
    var noneOpt = document.createElement('label');
    noneOpt.className = 'hall-none-option selected';
    noneOpt.id = 'decoNoneOption';
    noneOpt.addEventListener('click', function() { selectDecoStyle(null); });
    noneOpt.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="8" y1="12" x2="16" y2="12"/></svg> No specific style assigned';
    noneOpt.style.gridColumn = '1 / -1';
    decoCards.appendChild(noneOpt);
    selectDecoStyle(null);
  }

  window.selectDecoStyle = function (styleId) {
    document.querySelectorAll('#decoCards .hall-card').forEach(function(c) { c.classList.remove('selected'); });
    var noneOpt = document.getElementById('decoNoneOption');
    if (styleId === null) {
      if (noneOpt) noneOpt.classList.add('selected');
      if (decoStyleHidden) decoStyleHidden.value = '';
    } else {
      if (noneOpt) noneOpt.classList.remove('selected');
      var card = document.querySelector('#decoCards .hall-card[data-deco-id="' + styleId + '"]');
      if (card) { card.classList.add('selected'); }
      if (decoStyleHidden) decoStyleHidden.value = styleId;
    }
  };

  /* Guest count sync */
  if (guestCountInput && guestCountHidden) {
    guestCountInput.addEventListener('input', () => { guestCountHidden.value = guestCountInput.value; });
  }

  if (addServiceForm && serviceSelect) {
    addServiceForm.addEventListener('submit', event => {
      const opt = serviceSelect.options[serviceSelect.selectedIndex];
      if (parseInt(opt?.dataset.attireCount || '0', 10) > 0 && !attireItemHidden?.value) {
        event.preventDefault();
        addSvcHint.textContent = 'Select a dress design before adding this attire service.';
        return;
      }
      const categoryId = opt?.dataset.categoryId || '';
      if (!categoryId || !includedCategoryNames[categoryId]) return;

      const categoryName = opt.dataset.categoryName || includedCategoryNames[categoryId] || 'this category';
      const ok = confirm('This package already includes a ' + categoryName + ' service. Add another ' + categoryName + ' service anyway?');
      if (!ok) event.preventDefault();
    });
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
