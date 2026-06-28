<?php
$message = $message ?? '';
$old = $old ?? [];
$errors = $errors ?? [];

$dashboardTitle = 'Packages';
$dashboardCrumb = 'New Package';
$dashboardBreadcrumbs = [
  ['label' => 'Dashboard', 'url' => URLROOT . '/admin/dashboard'],
  ['label' => 'Packages', 'url' => URLROOT . '/admin/packages'],
  ['label' => 'New Package', 'url' => null],
];
$dashboardContentClass = 'admin-pkg-create';

$dashboardContent = function () use ($message, $old, $errors) {
  $h = fn($value) => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
  $agentFeeRate = get_platform_fee_percent() / 100;
?>
<style>
  .admin-pkg-create{min-height:100%;background:#F4F1EE;padding:28px 32px;font-family:'DM Sans',system-ui,-apple-system,sans-serif;color:#6d4c5b;font-size:13px}
  .admin-pkg-page *{box-sizing:border-box}
  .admin-pkg-page{--bg:#F4F1EE;--surface:#FFFFFF;--soft:#FFFFFF;--hover:#eddecc;--border:#ead8c7;--border-light:#eddecc;--primary:#6d4c5b;--primary-hover:#7b5c69;--primary-soft:#eddecc;--text:#111827;--muted:#b79c8b;--body:#7b5c69;max-width:980px;margin:0 auto}

  .back-link{display:inline-flex;align-items:center;gap:6px;color:var(--muted);font-size:12px;font-weight:600;text-decoration:none;margin-bottom:16px}
  .back-link:hover{color:var(--primary)}

  .card{background:var(--surface);border:1px solid var(--border);border-radius:.75rem;padding:24px;margin-bottom:20px}
  .card-title{font-size:14px;font-weight:700;color:var(--text);margin:0 0 16px;padding-bottom:12px;border-bottom:1px solid var(--border-light)}

  .field{margin-bottom:16px}
  .field label{display:block;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--muted);margin-bottom:4px}

  .two-col{display:grid;grid-template-columns:1fr 1fr;gap:16px}

  .flash{border:1px solid var(--border);border-radius:.75rem;background:var(--surface);padding:12px 14px;margin-bottom:18px;color:var(--body);font-size:13px;font-weight:600}
  .flash.flash-error{border-color:#e5c4c4;background:#fef2f2;color:#991B1B}
  .field-error{color:#b42318;font-size:11px;font-weight:600;margin-top:4px}
  .field.has-error input,.field.has-error textarea,.field.has-error select{border-color:#e5a3a3}

  .btn-primary{display:inline-flex;align-items:center;gap:6px;padding:0 18px;height:36px;border:none;border-radius:.75rem;background:var(--primary);color:#FFFFFF;font-size:12px;font-weight:700;font-family:inherit;cursor:pointer;transition:background .12s;text-decoration:none}
  .btn-primary:hover{background:var(--primary-hover)}

  input,textarea,select{width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:.5rem;background:var(--bg);color:var(--text);font-size:13px;font-family:inherit;outline:none;transition:border-color .12s}
  input:focus,textarea:focus,select:focus{border-color:var(--primary)}
  textarea{min-height:80px;resize:vertical}
  input[type=number]{width:140px}
  select{width:100%}

  .hint{font-size:12px;color:var(--muted);margin:0 0 10px;line-height:1.5}
  .stat-sub{font-size:11px;color:var(--muted);margin-top:2px}

  .toggle-wrap{display:flex;align-items:center;gap:10px}
  .toggle{position:relative;width:40px;height:22px;border-radius:999px;border:none;cursor:pointer;transition:background .2s}
  .toggle.on{background:var(--primary)}
  .toggle.off{background:var(--border)}
  .toggle::after{content:'';position:absolute;top:2px;left:2px;width:18px;height:18px;border-radius:50%;background:#FFFFFF;transition:transform .2s}
  .toggle.on::after{transform:translateX(18px)}

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
  .cover-upload-button{display:inline-flex;align-items:center;justify-content:center;height:36px;padding:0 16px;border:1px solid var(--border);border-radius:.65rem;background:#FFFFFF;color:var(--text);font-size:12px;font-weight:700;box-shadow:0 1px 2px rgba(17,24,39,.04)}
  .cover-uploader-preview{position:absolute;inset:0;display:none}
  .cover-uploader.has-image .cover-uploader-preview{display:block}
  .cover-uploader.has-image .cover-uploader-empty{display:none}
  .cover-uploader-preview img{width:100%;height:100%;object-fit:cover}
  .cover-preview-shade{position:absolute;inset:auto 0 0;padding:54px 20px 18px;background:linear-gradient(transparent,rgba(18,13,15,.82));display:flex;align-items:flex-end;justify-content:space-between;gap:16px;color:#FFFFFF}
  .cover-preview-name{min-width:0;font-size:12px;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
  .cover-preview-change{flex-shrink:0;border:1px solid rgba(252,248,245,.55);border-radius:.6rem;background:rgba(252,248,245,.14);color:#FFFFFF;padding:8px 12px;font-family:inherit;font-size:11px;font-weight:700;backdrop-filter:blur(8px);cursor:pointer}
  .cover-upload-error{display:none;margin-top:7px;color:#b42318;font-size:12px;font-weight:600}
  .cover-upload-error.is-visible{display:block}
</style>
<div class="admin-pkg-page">
  <a class="back-link" href="<?= URLROOT ?>/admin/packages">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
    Back to Packages
  </a>

  <?php if ($message !== ''): ?>
    <?php $isError = stripos($message, 'fix') !== false || stripos($message, 'error') !== false || stripos($message, 'required') !== false || stripos($message, 'failed') !== false; ?>
    <div class="flash<?= $isError ? ' flash-error' : '' ?>"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <form id="packageCreateForm" method="POST" action="<?= URLROOT ?>/admin/packageCreate" enctype="multipart/form-data">
    <div class="card">
      <div class="card-title">Package Details</div>

      <div class="two-col">
        <div class="field<?= !empty($errors['name']) ? ' has-error' : '' ?>">
          <label>Name *</label>
          <input type="text" name="name" required placeholder="e.g. Standard Complete Wedding" value="<?= $h($old['name'] ?? '') ?>">
          <?php if (!empty($errors['name'])): ?>
            <p class="field-error"><?= $h($errors['name']) ?></p>
          <?php endif; ?>
        </div>
        <div class="field">
          <label>Slug</label>
          <input type="text" name="slug" placeholder="leave blank to auto-generate" value="<?= $h($old['slug'] ?? '') ?>">
        </div>
      </div>

      <div class="field">
        <label>Tagline</label>
        <input type="text" name="tagline" placeholder="Short, compelling one-liner" value="<?= $h($old['tagline'] ?? '') ?>">
      </div>

      <div class="field">
        <label>Description</label>
        <textarea name="description" placeholder="Describe the complete wedding services included in this package..."><?= $h($old['description'] ?? '') ?></textarea>
      </div>

      <div class="field">
        <label>Base Price (MMK)</label>
        <input type="number" name="base_price" id="basePriceInput" min="0" step="100" value="<?= (float)($old['base_price'] ?? 0) ?>">
        <p class="stat-sub" style="margin-top:6px">Customer-facing price = base + <?= (int)($agentFeeRate * 100) ?>% agent fee, calculated automatically.</p>
      </div>

      <div class="field">
        <label>Max Concurrent Bookings (per date)</label>
        <input type="number" name="max_concurrent" min="0" step="1" value="<?= (int)($old['max_concurrent'] ?? 0) ?>">
        <p class="hint" style="margin-top:6px">How many of this package can be booked for the same wedding date. 0 = unlimited.</p>
      </div>

      <div class="field">
        <label>Package Cover</label>
        <div class="cover-uploader" id="packageCoverUploader">
          <input class="cover-uploader-input" id="packageCoverInput" type="file" name="package_image" accept="image/jpeg,image/png,image/webp">
          <label class="cover-uploader-label" for="packageCoverInput">
            <span class="cover-uploader-empty">
              <span class="cover-upload-icon" aria-hidden="true">
                <svg width="58" height="44" viewBox="0 0 58 44" fill="none"><path d="M46.5 19.2A14.5 14.5 0 0 0 18.7 14 10.5 10.5 0 0 0 20 35h25.5a8 8 0 0 0 1-15.8Z" fill="currentColor"/><path d="m29 14-7 8h4v8h6v-8h4l-7-8Z" fill="#FFFFFF"/></svg>
              </span>
              <span class="cover-upload-title">Choose an image or <span>drag &amp; drop it here</span></span>
              <span class="cover-upload-help">JPG, PNG or WebP</span>
              <span class="cover-upload-button">Browse files</span>
            </span>
          </label>
          <div class="cover-uploader-preview">
            <img id="packageCoverPreview" src="" alt="Selected package cover">
            <div class="cover-preview-shade">
              <span class="cover-preview-name" id="packageCoverName">Package cover</span>
              <button class="cover-preview-change" type="button" id="packageCoverChange">Change cover</button>
            </div>
          </div>
        </div>
        <p class="cover-upload-error<?= !empty($errors['image']) ? ' is-visible' : '' ?>" id="packageCoverError" role="alert"><?= $h($errors['image'] ?? '') ?></p>
      </div>

      <div class="field">
        <div class="toggle-wrap" style="justify-content:space-between">
          <label style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--muted)">Active</label>
          <button type="button" class="toggle on" onclick="this.classList.toggle('on');this.classList.toggle('off');document.getElementById('is_active').value=this.classList.contains('on')?1:0"></button>
          <input type="hidden" name="is_active" id="is_active" value="1">
        </div>
      </div>
    </div>

    <div style="display:flex;gap:8px">
      <button class="btn-primary" type="submit">Create Package</button>
      <a class="btn-ghost" href="<?= URLROOT ?>/admin/packages" style="display:inline-flex;align-items:center;gap:6px;padding:0 14px;height:36px;border:1px solid var(--border);border-radius:.75rem;background:var(--surface);color:var(--body);font-size:12px;font-weight:700;font-family:inherit;text-decoration:none;cursor:pointer">Cancel</a>
    </div>
  </form>
</div>
<script>
  function initPackageCoverUploader() {
    const uploader = document.getElementById('packageCoverUploader');
    const input = document.getElementById('packageCoverInput');
    const preview = document.getElementById('packageCoverPreview');
    const fileName = document.getElementById('packageCoverName');
    const changeButton = document.getElementById('packageCoverChange');
    const error = document.getElementById('packageCoverError');
    if (!uploader || !input || !preview) return;

    const allowedExts = ['jpg', 'jpeg', 'png', 'webp'];

    function showError(message) {
      error.textContent = message;
      error.classList.toggle('is-visible', Boolean(message));
    }

    function setFile(file) {
      if (!file) return;
      const ext = file.name.split('.').pop().toLowerCase();
      if (!allowedExts.includes(ext)) {
        input.value = '';
        showError('Choose a JPG, PNG, or WebP image.');
        return;
      }
      showError('');
      preview.src = URL.createObjectURL(file);
      fileName.textContent = file.name;
      uploader.classList.add('has-image');
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
