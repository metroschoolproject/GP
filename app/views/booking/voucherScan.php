<?php
$status = $status ?? 'error';
$title = $title ?? 'Voucher Scan';
$message = $message ?? '';
$voucher = $voucher ?? [];
$plain = function ($v) {
    $text = (string)$v;
    for ($i = 0; $i < 10; $i++) {
        $decoded = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if ($decoded === $text) break;
        $text = $decoded;
    }
    return $text;
};
$h = fn($v) => htmlspecialchars($plain($v), ENT_QUOTES, 'UTF-8');
$tone = $status === 'success' ? 'success' : ($status === 'used' ? 'used' : 'error');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $h($title) ?> - Golden Promise</title>
<?php $v = file_exists(APPROOT . '/../public/css/app.css') ? filemtime(APPROOT . '/../public/css/app.css') : time(); ?>
<link rel="stylesheet" href="<?= URLROOT ?>/public/css/app.css?v=<?= $v ?>">
<style>
body{min-height:100vh;margin:0;display:grid;place-items:center;background:#f2e4d4;color:#1a1118;font-family:Poppins,system-ui,sans-serif;padding:20px}
.scan-card{width:min(100%,420px);background:#fcf8f5;border:1px solid rgba(178,143,110,.28);border-radius:18px;padding:28px;box-shadow:0 22px 70px rgba(65,42,55,.12)}
.scan-icon{display:grid;place-items:center;width:54px;height:54px;border-radius:50%;margin-bottom:16px;font-size:24px;font-weight:900}
.scan-icon.success{background:#ecfdf5;color:#166534}.scan-icon.used{background:#f3f4f6;color:#4b5563}.scan-icon.error{background:#fef2f2;color:#991b1b}
h1{font-family:Georgia,serif;font-size:30px;line-height:1;margin:0 0 8px;color:#6b4459}
p{margin:0;color:#5c4a54;font-size:14px;line-height:1.6}
.scan-details{display:grid;gap:8px;margin-top:20px;padding-top:18px;border-top:1px dashed rgba(178,143,110,.34);font-size:13px}
.scan-row{display:flex;justify-content:space-between;gap:16px}
.scan-label{color:#a08878}.scan-value{font-weight:700;text-align:right;overflow-wrap:anywhere}
.scan-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:22px}
.scan-btn{display:inline-flex;align-items:center;justify-content:center;min-height:38px;padding:0 14px;border:1px solid rgba(178,143,110,.45);border-radius:999px;color:#5c4a54;text-decoration:none;font-size:12px;font-weight:800}
.scan-btn.primary{background:#6b4459;border-color:#6b4459;color:#fffaf3}
.scan-btn:focus-visible{outline:2px solid #b8924a;outline-offset:2px}
</style>
</head>
<body>
<main class="scan-card">
  <div class="scan-icon <?= $h($tone) ?>" aria-hidden="true"><?= $tone === 'success' ? '✓' : ($tone === 'used' ? '•' : '!') ?></div>
  <h1><?= $h($title) ?></h1>
  <p><?= $h($message) ?></p>

  <?php if (!empty($voucher)): ?>
  <div class="scan-details">
    <div class="scan-row"><span class="scan-label">Service</span><span class="scan-value"><?= $h($voucher['service_name'] ?? '-') ?></span></div>
    <div class="scan-row"><span class="scan-label">Supplier</span><span class="scan-value"><?= $h($voucher['supplier_name'] ?? 'Golden Promise') ?></span></div>
    <div class="scan-row"><span class="scan-label">Code</span><span class="scan-value"><?= $h($voucher['voucher_number'] ?? '-') ?></span></div>
    <div class="scan-row"><span class="scan-label">Status</span><span class="scan-value"><?= $h(ucfirst((string)($voucher['status'] ?? '-'))) ?></span></div>
  </div>
  <?php endif; ?>

  <div class="scan-actions">
    <a class="scan-btn primary" href="<?= URLROOT ?>/supplier/bookings">Supplier Bookings</a>
    <a class="scan-btn" href="<?= URLROOT ?>/">Home</a>
  </div>
</main>
</body>
</html>
