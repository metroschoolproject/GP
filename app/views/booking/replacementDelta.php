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
$banks = ['KBZ Pay', 'Wave Money', 'AYA Pay', 'Yoma Bank', 'CB Bank', 'Visa / MasterCard'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Approve Replacement · Golden Promise</title>
<style>
  *{box-sizing:border-box}
  body{margin:0;font-family:'DM Sans',system-ui,-apple-system,sans-serif;background:#FBFBF9;color:#111827;padding:40px 16px}
  .wrap{max-width:640px;margin:0 auto}
  .eyebrow{font-size:11px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:#b79c8b}
  h1{font-size:24px;font-weight:700;margin:6px 0 0}
  h1 em{font-style:italic;color:#6d4c5b}
  .card{background:#fff;border:1px solid #ead8c7;border-radius:14px;padding:20px;margin-top:18px}
  .card-label{font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:#b79c8b;margin-bottom:10px}
  .row{display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f0e6da;font-size:14px}
  .row:last-child{border-bottom:none}
  .row .v{font-weight:700}
  .delta{font-size:22px;font-weight:800;color:#991b1b}
  .flash{background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;border-radius:10px;padding:11px 14px;margin-top:16px;font-size:13px;font-weight:600}
  label{display:block;font-size:12px;font-weight:700;color:#57534e;margin:14px 0 5px}
  input,select{width:100%;height:42px;padding:0 12px;border:1px solid #ead8c7;border-radius:10px;font-size:14px;font-family:inherit;background:#fff;outline:none}
  input:focus,select:focus{border-color:#6d4c5b}
  .file{display:flex;align-items:center;gap:8px;height:auto;padding:14px;border:1.5px dashed #ead8c7;border-radius:10px;cursor:pointer;color:#7b5c69;font-size:13px}
  .grid2{display:grid;grid-template-columns:1fr 1fr;gap:0 14px}
  .btn{width:100%;height:48px;border:none;border-radius:12px;background:#6d4c5b;color:#fff;font-size:15px;font-weight:700;margin-top:20px;cursor:pointer}
  .btn:hover{opacity:.93}
  .muted{font-size:12.5px;color:#7b5c69;margin-top:10px;line-height:1.5}
  a.back{color:#6d4c5b;font-size:13px;font-weight:700;text-decoration:none}
</style>
</head>
<body>
<div class="wrap">
  <div class="eyebrow">Replacement supplier</div>
  <h1>Approve &amp; <em>Pay the Difference</em></h1>

  <?php if ($flash): ?>
    <div class="flash"><?= $h($flash) ?></div>
  <?php endif; ?>

  <div class="card">
    <div class="card-label">Replacement for booking <?= $h($bookingRef) ?></div>
    <div class="row"><span>New supplier</span><span class="v"><?= $h($newShop) ?></span></div>
    <?php if ($newService !== ''): ?>
      <div class="row"><span>Service</span><span class="v"><?= $h($newService) ?></span></div>
    <?php endif; ?>
    <div class="row"><span>Original price</span><span class="v"><?= $money($replacement['old_price'] ?? 0) ?></span></div>
    <div class="row"><span>New price</span><span class="v"><?= $money($replacement['new_price'] ?? 0) ?></span></div>
    <div class="row"><span>Extra to pay</span><span class="delta">+<?= $money($delta) ?></span></div>
    <p class="muted">Your original supplier was unavailable. This replacement costs more — pay the difference to confirm it. Decline by simply not paying; we'll find another option.</p>
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
    <p class="muted">Our team verifies the transfer, then confirms your replacement supplier. You'll be notified.</p>
  </form>

  <form action="<?= URLROOT ?>/booking/rejectReplacement/<?= $replacementId ?>" method="post"
        onsubmit="return confirm('Decline this replacement? We will ask the admin to find another option.')">
    <?= csrf_field() ?>
    <button type="submit" class="btn" style="background:#fff;color:#991b1b;border:1px solid #fecaca;margin-top:12px">
      Decline this replacement
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
