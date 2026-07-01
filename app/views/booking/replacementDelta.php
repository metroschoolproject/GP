<?php
$replacement = $replacement ?? [];
$delta       = (float)($delta ?? 0);
$bookingRef  = $bookingRef ?? '';
$flash       = $flash ?? null;

$money = fn($v) => number_format((float)$v, 0) . ' MMK';
$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');

$replacementId = (int)($replacement['id'] ?? 0);
$newShop = $replacement['new_shop_name'] ?? 'the new supplier';
$newService = $replacement['new_service_name'] ?? '';
$oldService = $replacement['old_service_name'] ?? 'your service';
$oldSupplier = $replacement['old_shop_name'] ?? $replacement['old_supplier_name'] ?? 'your original supplier';
$oldPrice = (float)($replacement['old_price'] ?? 0);
$newPrice = (float)($replacement['new_price'] ?? 0);
$declineReason = $replacement['decline_reason'] ?? '';
$proposedAt = $replacement['proposed_at'] ?? null;
$deadline = $proposedAt ? date('M d, Y', strtotime($proposedAt . ' + 3 days')) : null;
$daysLeft = $proposedAt ? max(0, (int)((strtotime($proposedAt) + 3*86400 - time()) / 86400)) : null;
$banks = ['KBZ Pay', 'Wave Money', 'AYA Pay', 'Yoma Bank', 'CB Bank', 'Visa / MasterCard'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="icon" type="image/png" href="<?= URLROOT ?>/public/images/home/gp_logo.png">
<title>Approve Replacement · Golden Promise</title>
<style>
  *{box-sizing:border-box}
  body{margin:0;font-family:'DM Sans',system-ui,-apple-system,sans-serif;background:#FBFBF9;color:#111827;padding:40px 16px}
  .wrap{max-width:640px;margin:0 auto}
  .eyebrow{font-size:11px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:#b79c8b}
  h1{font-size:24px;font-weight:700;margin:6px 0 0}
  h1 em{font-style:italic;color:#6d4c5b}
  .card{background:#fcf8f5;border:1px solid #ead8c7;border-radius:14px;padding:20px;margin-top:18px}
  .card-label{font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:#b79c8b;margin-bottom:10px}
  .row{display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f0e6da;font-size:14px}
  .row:last-child{border-bottom:none}
  .row .v{font-weight:700}
  .delta{font-size:22px;font-weight:800;color:#991b1b}
  .flash{background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;border-radius:10px;padding:11px 14px;margin-top:16px;font-size:13px;font-weight:600}
  .context-banner{background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:12px 14px;margin-bottom:6px;font-size:13px;color:#1e40af;line-height:1.5}
  label{display:block;font-size:12px;font-weight:700;color:#57534e;margin:14px 0 5px}
  input,select{width:100%;height:42px;padding:0 12px;border:1px solid #ead8c7;border-radius:10px;font-size:14px;font-family:inherit;background:#fcf8f5;outline:none}
  input:focus,select:focus{border-color:#6d4c5b}
  .file{display:flex;align-items:center;gap:8px;height:auto;padding:14px;border:1.5px dashed #ead8c7;border-radius:10px;cursor:pointer;color:#7b5c69;font-size:13px}
  .grid2{display:grid;grid-template-columns:1fr 1fr;gap:0 14px}
  .btn{width:100%;height:48px;border:none;border-radius:12px;background:#6d4c5b;color:#fcf8f5;font-size:15px;font-weight:700;margin-top:20px;cursor:pointer}
  .btn:hover{opacity:.93}
  .muted{font-size:12.5px;color:#7b5c69;margin-top:10px;line-height:1.5}
  a.back{color:#6d4c5b;font-size:13px;font-weight:700;text-decoration:none}
  .deadline-badge{display:inline-flex;align-items:center;gap:6px;background:#fef3c7;border:1px solid #fcd34d;border-radius:999px;padding:6px 14px;font-size:12px;font-weight:700;color:#92400e;margin-top:12px}
  .vs-comparison{display:grid;grid-template-columns:1fr auto 1fr;align-items:center;gap:12px;margin:12px 0;padding:14px;background:#faf6f1;border-radius:10px}
  .vs-original,.vs-new{padding:10px;text-align:center}
  .vs-original{border:1px solid #fecaca;border-radius:8px;background:#fff}
  .vs-new{border:1px solid #bbf7d0;border-radius:8px;background:#fff}
  .vs-label{font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:#b79c8b;margin-bottom:4px}
  .vs-name{font-size:13px;font-weight:700;margin-bottom:2px}
  .vs-shop{font-size:11px;color:#7b5c69;margin-bottom:4px}
  .vs-price{font-size:14px;font-weight:800}
  .vs-arrow{font-size:18px;color:#6d4c5b;font-weight:700}
</style>
</head>
<body>
<div class="wrap">
  <div class="eyebrow">Replacement supplier</div>
  <h1>Approve &amp; <em>Pay the Difference</em></h1>

  <?php if ($flash): ?>
    <div class="flash"><?= $h($flash) ?></div>
  <?php endif; ?>

  <!-- Context banner -->
  <div class="card" style="padding-bottom:16px">
    <div class="context-banner">
      <strong><?= $h($oldService) ?></strong> from <strong><?= $h($oldSupplier) ?></strong> is no longer available.
      <?php if ($declineReason !== ''): ?><div style="margin-top:4px;opacity:.8">Reason: <?= $h($declineReason) ?></div><?php endif; ?>
      Admin has found a replacement supplier for you.
    </div>

    <!-- Side-by-side comparison -->
    <div class="vs-comparison">
      <div class="vs-original">
        <div class="vs-label">Original</div>
        <div class="vs-name"><?= $h($oldService) ?></div>
        <div class="vs-shop"><?= $h($oldSupplier) ?></div>
        <div class="vs-price"><?= $money($oldPrice) ?></div>
      </div>
      <div class="vs-arrow">→</div>
      <div class="vs-new">
        <div class="vs-label">Replacement</div>
        <div class="vs-name"><?= $h($newService !== '' ? $newService : 'Service') ?></div>
        <div class="vs-shop"><?= $h($newShop) ?></div>
        <div class="vs-price" style="color:<?= $delta > 0 ? '#dc2626' : '#16a34a' ?>"><?= $money($newPrice) ?></div>
      </div>
    </div>

    <div class="row"><span>Price difference</span><span class="delta">+<?= $money($delta) ?></span></div>
    <?php if ($deadline): ?>
    <div class="deadline-badge">
      ⏰ Respond by <strong><?= $deadline ?></strong>
      <?php if ($daysLeft !== null): ?>(<?= $daysLeft ?> day<?= $daysLeft === 1 ? '' : 's' ?> left)<?php endif; ?>
    </div>
    <?php endif; ?>
    <p class="muted">Pay the price difference to confirm this replacement. After admin verification, the new supplier will confirm. If you prefer not to pay more, you can decline — admin will find another option at no extra cost.</p>
  </div>

  <form class="card" action="<?= URLROOT ?>/booking/submitReplacementDelta" method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="card-label">Bank transfer proof</div>
    <input type="hidden" name="replacement_id" value="<?= $replacementId ?>">

    <label for="bank_name">Payment method</label>
    <select name="bank_name" id="bank_name" required>
      <option value="">Select…</option>
      <?php foreach ($banks as $b): ?>
        <option value="<?= $h($b) ?>"><?= $h($b) ?></option>
      <?php endforeach; ?>
    </select>

    <div class="grid2">
      <div>
        <label for="account_name">Account name</label>
        <input type="text" name="account_name" id="account_name" required>
      </div>
      <div>
        <label for="mobile_number">Mobile number</label>
        <input type="text" name="mobile_number" id="mobile_number" required>
      </div>
    </div>

    <div class="grid2">
      <div>
        <label for="transaction_ref">Transaction reference</label>
        <input type="text" name="transaction_ref" id="transaction_ref" required>
      </div>
      <div>
        <label for="paid_amount">Amount paid (MMK)</label>
        <input type="text" name="paid_amount" id="paid_amount" value="<?= (int)$delta ?>" required>
      </div>
    </div>

    <label for="slip_image">Payment slip</label>
    <label class="file" for="slip_image">
      <span id="slipName">Click to upload screenshot or receipt</span>
    </label>
    <input type="file" name="slip_image" id="slip_image" accept="image/*" style="display:none">

    <button type="submit" class="btn">Submit payment &amp; approve replacement</button>
    <p class="muted">Admin verifies the transfer, then confirms your replacement and notifies the new supplier. This usually takes 1–2 hours.</p>
  </form>

  <form action="<?= URLROOT ?>/booking/rejectReplacement/<?= $replacementId ?>" method="post"
        onsubmit="return confirm('Decline this replacement? Admin will find another option at no extra cost to you.')">
    <?= csrf_field() ?>
    <button type="submit" class="btn" style="background:#fff;color:#991b1b;border:1px solid #fecaca;margin-top:12px">
      Decline — find another option
    </button>
  </form>

  <div style="margin-top:16px"><a class="back" href="<?= URLROOT ?>/booking/detail/<?= (int)($replacement['booking_id'] ?? 0) ?>">← Back to booking</a></div>
</div>

<script>
  const slip = document.getElementById('slip_image');
  const slipName = document.getElementById('slipName');
  if (slip) slip.addEventListener('change', function () {
    slipName.textContent = this.files[0] ? this.files[0].name : 'Click to upload screenshot or receipt';
  });
</script>
</body>
</html>
