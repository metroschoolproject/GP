<?php
$booking = $booking ?? [];
$total = (float)($total ?? 0);
$paid = (float)($paid ?? 0);
$balance = (float)($balance ?? 0);
$bookingRef = $bookingRef ?? '';
$eventDate = $eventDate ?? null;
$remainingPayments = $remainingPayments ?? [];
$hasPendingRemaining = $hasPendingRemaining ?? false;
$minPayment = (float)($minPayment ?? 1000);

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

.quick-fill{display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border:1px solid var(--rule);border-radius:8px;background:#fff;font-size:11px;font-weight:600;color:var(--plum);cursor:pointer;transition:all .15s;margin-top:6px}
.quick-fill:hover{border-color:var(--plum);background:rgba(107,68,89,.04)}

.amount-hint{font-size:11px;color:var(--muted);margin-top:4px}
.amount-error{font-size:11px;color:#991b1b;margin-top:4px;display:none}

.history-item{display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid var(--rule);font-size:12px}
.history-item:last-child{border-bottom:none}
.history-status{font-size:10px;font-weight:700;padding:2px 8px;border-radius:10px}
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

  <?php if ($hasPendingRemaining): ?>
  <div style="background:#fffbeb;border:1px solid #fcd34d;border-radius:12px;padding:16px 20px;margin-bottom:18px;font-size:13px;color:#92400e;">
    <strong>Payment Under Review</strong> — You have a remaining payment being reviewed. You can submit another payment once it's verified.
  </div>
  <?php endif; ?>

  <!-- Summary -->
  <div class="card">
    <div class="card-head">Booking #<?= $h($bookingRef) ?></div>
    <div class="summary-row"><span>Booking total</span><span><?= $money($total) ?></span></div>
    <div class="summary-row"><span>Already paid</span><span><?= $money($paid) ?></span></div>
    <div class="summary-row total"><span>Remaining balance</span><span><?= $money($balance) ?></span></div>
    <?php if ($eventDate): ?>
    <div class="summary-row" style="margin-top:8px;padding-top:8px;border-top:1px solid var(--rule);">
      <span style="color:#b8924a;font-weight:600;">Due Date</span>
      <span style="font-weight:700;color:#b8924a;"><?= date('M d, Y', strtotime($eventDate)) ?></span>
    </div>
    <?php endif; ?>
  </div>

  <?php if (!empty($remainingPayments)): ?>
  <!-- Payment History -->
  <div class="card">
    <div class="card-head">Payment History</div>
    <?php foreach ($remainingPayments as $rp): ?>
    <div class="history-item">
      <div>
        <span style="font-weight:600;"><?= date('M d, Y', strtotime($rp['created_at'])) ?></span>
        <?php if (($rp['bank_name'] ?? '') !== ''): ?>
        <span style="color:var(--muted);margin-left:8px;"><?= $h($rp['bank_name']) ?></span>
        <?php endif; ?>
      </div>
      <div style="display:flex;align-items:center;gap:8px;">
        <span style="font-weight:700;"><?= $money($rp['paid_amount'] ?? $rp['amount']) ?></span>
        <?php
          $rpStatus = $rp['status'] ?? 'pending';
          $rpColors = [
            'success' => ['bg' => '#f0fdf4', 'color' => '#166534', 'label' => 'Verified'],
            'pending' => ['bg' => '#fffbeb', 'color' => '#92400e', 'label' => 'Under Review'],
            'failed'  => ['bg' => '#fef2f2', 'color' => '#991b1b', 'label' => 'Rejected'],
          ];
          $rpSt = $rpColors[$rpStatus] ?? $rpColors['pending'];
        ?>
        <span class="history-status" style="background:<?= $rpSt['bg'] ?>;color:<?= $rpSt['color'] ?>;"><?= $rpSt['label'] ?></span>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <!-- Bank selection -->
  <form method="POST" action="<?= URLROOT ?>/booking/submitRemainingPayment" enctype="multipart/form-data" id="paymentForm">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="booking_id" value="<?= (int)($booking['id'] ?? 0) ?>">

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
        <input type="text" name="paid_amount" id="paidAmount" placeholder="Enter amount" required
               inputmode="numeric"
               value="<?= number_format($balance, 0, '.', '') ?>">
        <div class="amount-hint">Min: <?= $money($minPayment) ?> · Max: <?= $money($balance) ?></div>
        <div class="amount-error" id="amountError"></div>
        <button type="button" class="quick-fill" id="payFullBtn">Pay full balance (<?= $money($balance) ?>)</button>
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
      You can pay any amount you want (minimum <?= $money($minPayment) ?>) toward your remaining balance. You can make multiple payments until the full balance is paid. After submitting, our team will verify your transfer (usually within a few hours).
    </div>

    <div style="margin-top:16px">
      <button type="submit" class="btn-primary" id="submitBtn" <?= $hasPendingRemaining ? 'disabled' : '' ?>>Submit Payment</button>
    </div>
  </form>
</div>

<script>
const balance = <?= $balance ?>;
const minPayment = <?= $minPayment ?>;
const amountInput = document.getElementById('paidAmount');
const amountError = document.getElementById('amountError');
const submitBtn = document.getElementById('submitBtn');
const payFullBtn = document.getElementById('payFullBtn');

// Bank selection visual toggle
document.querySelectorAll('.bank-option').forEach(el => {
  el.addEventListener('click', () => {
    document.querySelectorAll('.bank-option').forEach(b => b.classList.remove('selected'));
    el.classList.add('selected');
    el.querySelector('input').checked = true;
  });
});

// Quick fill full balance
payFullBtn.addEventListener('click', () => {
  amountInput.value = Math.round(balance).toLocaleString();
  validateAmount();
});

// Format number with commas on blur
amountInput.addEventListener('blur', () => {
  const raw = amountInput.value.replace(/[^0-9]/g, '');
  if (raw) {
    amountInput.value = parseInt(raw, 10).toLocaleString();
  }
});

// Validate amount
function validateAmount() {
  const raw = amountInput.value.replace(/[^0-9]/g, '');
  const val = parseFloat(raw) || 0;

  if (val < minPayment - 0.01) {
    amountError.textContent = 'Minimum payment is <?= $money($minPayment) ?>';
    amountError.style.display = 'block';
    submitBtn.disabled = true;
    return false;
  }
  if (val > balance + 0.01) {
    amountError.textContent = 'Amount cannot exceed remaining balance (<?= $money($balance) ?>)';
    amountError.style.display = 'block';
    submitBtn.disabled = true;
    return false;
  }

  amountError.style.display = 'none';
  submitBtn.disabled = <?= $hasPendingRemaining ? 'true' : 'false' ?>;
  return true;
}

amountInput.addEventListener('input', validateAmount);

// Form submit — set clean numeric value
document.getElementById('paymentForm').addEventListener('submit', () => {
  const raw = amountInput.value.replace(/[^0-9]/g, '');
  amountInput.value = raw;
});
</script>
</body>
</html>
