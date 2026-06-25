<?php
$booking = $booking ?? [];
$total = (float)($total ?? 0);
$paid = (float)($paid ?? 0);
$balance = (float)($balance ?? 0);
$bookingRef = $bookingRef ?? '';

$money = fn($v) => number_format((float)$v, 0) . ' MMK';
$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');

$banks = defined('PLATFORM_BANK_ACCOUNTS') ? PLATFORM_BANK_ACCOUNTS : [];
$bankIcons = [
    'KBZ Pay'           => '🏦',
    'Wave Money'        => '🌊',
    'AYA Pay'           => '💙',
    'Yoma Bank'         => '🏧',
    'CB Bank'           => '🟢',
    'Visa / MasterCard' => '💳',
];
$flash = $_SESSION['remaining_payment_flash'] ?? '';
unset($_SESSION['remaining_payment_flash']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pay Remaining Balance — Golden Promise</title>
<?php $publicCssVersion = file_exists(APPROOT . '/../public/css/app.css') ? filemtime(APPROOT . '/../public/css/app.css') : time(); ?>
<link rel="stylesheet" href="<?= URLROOT ?>/public/css/app.css?v=<?= $publicCssVersion ?>">
<style>
:root{--bg:#f2e4d4;--surface:#faf6f1;--card:#fcf8f5;--rule:rgba(178,143,110,.22);--plum:#6b4459;--plum-lt:#9b7289;--gold:#b8924a;--muted:#a08878;--text:#1a1118;--text2:#5c4a54;--green:#166534;--blue:#2563eb}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{background:var(--bg);color:var(--text);font-family:'Poppins',system-ui,sans-serif;font-size:14px;line-height:1.6;min-height:100vh;display:flex;flex-direction:column;align-items:center;padding:40px 20px}
a{color:inherit;text-decoration:none}

.checkout{position:relative;z-index:1;width:100%;max-width:560px}
.page-head{margin-bottom:28px;text-align:center}
.eyebrow{font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--gold);margin-bottom:8px}
.page-title{font-family:'Playfair Display',Georgia,serif;font-size:clamp(28px,4vw,38px);font-weight:600;color:var(--text);line-height:.95}

.card{background:var(--card);border:1px solid var(--rule);border-radius:16px;padding:24px;margin-bottom:18px;box-shadow:0 2px 8px rgba(0,0,0,.03)}
.card-head{font-size:11px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--muted);margin-bottom:14px}

.summary-row{display:flex;justify-content:space-between;padding:6px 0;font-size:13px}
.summary-row.total{font-weight:700;color:var(--plum);border-top:1px solid var(--rule);padding-top:10px;margin-top:4px}

.bank-grid{display:grid;gap:8px}
.bank-option{display:flex;align-items:center;gap:12px;padding:12px 16px;border:2px solid var(--rule);border-radius:12px;cursor:pointer;transition:all .15s;background:#fff}
.bank-option:hover{border-color:var(--plum-lt)}
.bank-option.selected{border-color:var(--plum);background:rgba(107,68,89,.04)}
.bank-option input{display:none}
.bank-icon{font-size:20px;width:28px;text-align:center}
.bank-info{flex:1}
.bank-name{font-weight:700;font-size:13px;color:var(--text)}
.bank-acct{font-size:12px;color:var(--muted);font-family:monospace}
.bank-check{width:20px;height:20px;border:2px solid var(--rule);border-radius:50%;display:flex;align-items:center;justify-content:center;transition:all .15s}
.bank-option.selected .bank-check{border-color:var(--plum);background:var(--plum);color:#fff}

.field{margin-bottom:14px}
.field label{display:block;font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:var(--muted);margin-bottom:6px}
.field input,.field select{width:100%;height:44px;border:1px solid var(--rule);border-radius:10px;padding:0 14px;font-size:13px;font-family:inherit;color:var(--text);background:#fff;outline:none;transition:border-color .2s}
.field input:focus,.field select:focus{border-color:var(--plum)}
.field input[readonly]{background:var(--surface);color:var(--muted)}

.btn-primary{display:flex;align-items:center;justify-content:center;width:100%;height:48px;border:0;border-radius:12px;background:var(--plum);color:#fff;font-size:14px;font-weight:700;font-family:inherit;cursor:pointer;transition:opacity .2s}
.btn-primary:hover{opacity:.9}
.btn-primary:disabled{opacity:.5;cursor:default}

.flash{background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:12px 16px;margin-bottom:16px;font-size:13px;color:#991b1b}

.back-link{display:inline-flex;align-items:center;gap:6px;font-size:12px;font-weight:600;color:var(--muted);margin-bottom:20px}
.back-link:hover{color:var(--plum)}

.tip{background:var(--surface);border:1px solid var(--rule);border-radius:10px;padding:14px 18px;margin-top:12px;font-size:12px;color:var(--muted);line-height:1.7}
</style>
</head>
<body>
<div class="checkout">
  <a href="<?= URLROOT ?>/booking/detail/<?= (int)($booking['id'] ?? 0) ?>" class="back-link">&larr; Back to booking</a>

  <div class="page-head">
    <div class="eyebrow">Payment</div>
    <h1 class="page-title">Pay Remaining <em>Balance</em></h1>
  </div>

  <?php if ($flash): ?>
  <div class="flash"><?= $h($flash) ?></div>
  <?php endif; ?>

  <!-- Summary -->
  <div class="card">
    <div class="card-head">Booking #<?= $h($bookingRef) ?></div>
    <div class="summary-row"><span>Booking total</span><span><?= $money($total) ?></span></div>
    <div class="summary-row"><span>Already paid (deposit)</span><span><?= $money($paid) ?></span></div>
    <div class="summary-row total"><span>Remaining balance</span><span><?= $money($balance) ?></span></div>
  </div>

  <!-- Bank selection -->
  <form method="POST" action="<?= URLROOT ?>/booking/submitRemainingPayment" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="booking_id" value="<?= (int)($booking['id'] ?? 0) ?>">
    <input type="hidden" name="paid_amount" value="<?= number_format($balance, 2, '.', '') ?>">

    <div class="card">
      <div class="card-head">Transfer to Golden Promise</div>
      <div class="bank-grid">
        <?php foreach ($banks as $bankName => $info): ?>
        <label class="bank-option" data-bank="<?= $h($bankName) ?>">
          <input type="radio" name="bank_name" value="<?= $h($bankName) ?>" required>
          <span class="bank-icon"><?= $bankIcons[$bankName] ?? '🏦' ?></span>
          <div class="bank-info">
            <div class="bank-name"><?= $h($bankName) ?></div>
            <div class="bank-acct"><?= $h($info['account']) ?> — <?= $h($info['name']) ?></div>
          </div>
          <span class="bank-check">✓</span>
        </label>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Transfer details -->
    <div class="card">
      <div class="card-head">Your Transfer Details</div>

      <div class="field">
        <label>Amount to Transfer</label>
        <input type="text" value="<?= $money($balance) ?>" readonly>
      </div>

      <div class="field">
        <label>Your Account Name</label>
        <input type="text" name="account_name" placeholder="Name on your account" required>
      </div>

      <div class="field">
        <label>Phone / Account Number</label>
        <input type="text" name="mobile_number" placeholder="09-XXX-XXXXXXX" required>
      </div>

      <div class="field">
        <label>Transaction Reference / ID</label>
        <input type="text" name="transaction_ref" placeholder="e.g., TXN123456789" required>
      </div>

      <div class="field">
        <label>Payment Slip (JPG, PNG, WebP, or PDF)</label>
        <input type="file" name="slip_image" accept="image/jpeg,image/png,image/webp,application/pdf" required>
      </div>
    </div>

    <div class="tip">
      After submitting, our team will verify your transfer. Once confirmed, your booking will be fully paid and finalized. Verification usually takes a few hours.
    </div>

    <div style="margin-top:16px">
      <button type="submit" class="btn-primary">Submit Remaining Payment</button>
    </div>
  </form>
</div>

<script>
// Bank selection visual toggle
document.querySelectorAll('.bank-option').forEach(el => {
  el.addEventListener('click', () => {
    document.querySelectorAll('.bank-option').forEach(b => b.classList.remove('selected'));
    el.classList.add('selected');
    el.querySelector('input').checked = true;
  });
});
</script>
</body>
</html>
