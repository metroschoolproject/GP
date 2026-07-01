<?php
$booking = $booking ?? [];
$items = $items ?? [];
$total = (float)($total ?? 0);
$deposit = (float)($deposit ?? 0);
$depositPercent = (int)($depositPercent ?? BOOKING_DEPOSIT_PERCENT);
$balance = (float)($balance ?? 0);
$bookingRef = $bookingRef ?? '';

$money = fn($v) => number_format((float)$v, 0) . ' MMK';
$deposit = (int)round($deposit);
$platformFee = (int)round((float)($platformFee ?? 0));
$platformFeePercent = (float)($platformFeePercent ?? get_platform_fee_percent());
$depositWithFee = (int)round((float)($depositWithFee ?? $deposit));
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

$banks = defined('PLATFORM_BANK_ACCOUNTS') ? PLATFORM_BANK_ACCOUNTS : [];
$bankIcons = [
    'KBZ Pay'           => '🏦',
    'Wave Money'        => '🌊',
    'AYA Pay'           => '💙',
    'Yoma Bank'         => '🏧',
    'CB Bank'           => '🟢',
    'Visa / MasterCard' => '💳',
];
$flash = $_SESSION['booking_payment_flash'] ?? '';
unset($_SESSION['booking_payment_flash']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" type="image/png" href="<?= URLROOT ?>/public/images/home/gp_logo.png">
<title>Pay Deposit — Golden Promise</title>
<?php $publicCssVersion = file_exists(APPROOT . '/../public/css/app.css') ? filemtime(APPROOT . '/../public/css/app.css') : time(); ?>
<link rel="stylesheet" href="<?= URLROOT ?>/public/css/app.css?v=<?= $publicCssVersion ?>">
<style>
:root {
  --bg:      #f2e4d4;
  --surface: #faf6f1;
  --card:    #fcf8f5;
  --rule:    rgba(178,143,110,0.22);
  --rule-s:  rgba(178,143,110,0.45);
  --plum:    #6b4459;
  --plum-dk: #4e3141;
  --plum-lt: #9b7289;
  --gold:    #b8924a;
  --muted:   #a08878;
  --text:    #1a1118;
  --text2:   #5c4a54;
  --danger:  #b94b4b;
  --green:   #166534;
  --r-sm:    8px;
  --r-md:    14px;
  --r-lg:    20px;
  --font-d:  'Playfair Display', Georgia, serif;
  --font-b:  'Poppins', system-ui, sans-serif;
  --ease:    cubic-bezier(0.19,1,0.22,1);
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{background:var(--bg);color:var(--text);font-family:var(--font-b);font-size:14px;line-height:1.6;-webkit-font-smoothing:antialiased;min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:flex-start;padding:40px 20px}
a{color:inherit;text-decoration:none}

.gp-orb{position:fixed;border-radius:50%;filter:blur(80px);opacity:.3;z-index:0;pointer-events:none}
.gp-orb-1{width:500px;height:500px;background:radial-gradient(circle,rgba(107,68,89,.10) 0%,transparent 70%);top:-150px;right:-80px}
.gp-orb-2{width:400px;height:400px;background:radial-gradient(circle,rgba(184,146,74,.08) 0%,transparent 70%);bottom:-100px;left:-80px}

.gp-checkout{position:relative;z-index:1;width:100%;max-width:560px}
.gp-page-head{margin-bottom:28px;text-align:center}
.gp-eyebrow{font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--gold);margin-bottom:8px}
.gp-page-title{font-family:var(--font-d);font-size:clamp(28px,4vw,42px);font-weight:600;color:var(--text);line-height:.95;letter-spacing:-.02em}
.gp-page-title em{font-style:italic;color:var(--plum-lt)}

.gp-card{background:var(--card);border-radius:var(--r-lg);border:1px solid var(--rule);overflow:hidden;box-shadow:0 20px 60px rgba(26,17,24,.08);margin-bottom:16px}
.gp-card-head{padding:20px 24px;border-bottom:1px solid var(--rule);position:relative}
.gp-card-head::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,var(--plum) 0%,var(--plum-lt) 50%,var(--gold) 100%)}
.gp-card-label{font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--muted);margin-bottom:4px}
.gp-card-title{font-family:var(--font-d);font-size:17px;font-weight:600;color:var(--text)}
.gp-card-body{padding:22px 24px;display:flex;flex-direction:column;gap:18px}

.gp-summary-title{font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--muted);margin-bottom:10px}
.gp-summary-items{display:flex;flex-direction:column;gap:7px}
.gp-row{display:flex;justify-content:space-between;align-items:baseline;font-size:13px}
.gp-row.total{font-weight:600;padding-top:10px;border-top:1px solid var(--rule);margin-top:2px}
.gp-row.deposit{color:var(--plum);font-weight:700}
.gp-row.balance{color:var(--muted);font-size:12px}
.gp-divider{height:1px;background:var(--rule)}

/* Bank grid */
.gp-bank-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px}
.gp-bank-btn{display:flex;flex-direction:column;align-items:center;gap:6px;padding:14px 8px;border:2px solid var(--rule);border-radius:var(--r-md);background:transparent;cursor:pointer;transition:all .22s;font-family:var(--font-b)}
.gp-bank-btn:hover{border-color:var(--plum);background:rgba(107,68,89,.04)}
.gp-bank-btn.active{border-color:var(--plum);background:rgba(107,68,89,.08)}
.gp-bank-icon{font-size:24px}
.gp-bank-label{font-size:11px;font-weight:700;color:var(--text2);text-align:center;line-height:1.3}

/* Account info box */
.gp-account-box{border:1px solid var(--rule-s);border-radius:var(--r-md);background:var(--surface);padding:14px 16px;display:none}
.gp-account-box.show{display:block}
.gp-account-title{font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--gold);margin-bottom:10px}
.gp-account-rows{display:flex;flex-direction:column;gap:7px}
.gp-account-row{display:flex;justify-content:space-between;align-items:baseline;font-size:12px;padding-bottom:7px;border-bottom:1px solid var(--rule)}
.gp-account-row:last-child{border-bottom:none;padding-bottom:0}
.gp-account-row dt{color:var(--muted);font-weight:600;font-size:10px;letter-spacing:.06em;text-transform:uppercase}
.gp-account-row dd{margin:0;font-weight:700;color:var(--text);font-family:monospace;font-size:13px}

/* Transfer form */
.gp-transfer-form{display:none;flex-direction:column;gap:12px}
.gp-transfer-form.show{display:flex}
.gp-field{display:flex;flex-direction:column;gap:5px}
.gp-field label{font-size:11px;font-weight:700;letter-spacing:.04em;color:var(--muted)}
.gp-field label .req{color:var(--danger)}
.gp-field label .opt{color:var(--muted);font-weight:400}
.gp-field input{padding:10px 13px;border:1px solid var(--rule-s);border-radius:var(--r-sm);font-size:13px;font-family:var(--font-b);background:var(--surface);color:var(--text);transition:border-color .18s,box-shadow .18s}
.gp-field input:focus{outline:none;border-color:var(--plum);box-shadow:0 0 0 3px rgba(107,68,89,.1)}
.gp-field-row{display:grid;grid-template-columns:1fr 1fr;gap:12px}

/* Slip upload */
.gp-slip-label{display:flex;align-items:center;gap:12px;padding:14px;border:1px dashed var(--rule-s);border-radius:var(--r-md);background:var(--surface);cursor:pointer;transition:border-color .18s}
.gp-slip-label:hover{border-color:var(--plum)}
.gp-slip-label.has-file{border-style:solid;border-color:rgba(22,101,52,.45);background:#f0fdf4}
.gp-slip-label.has-error{border-color:#fca5a5;background:#fef2f2}
.gp-slip-icon{width:40px;height:40px;border-radius:var(--r-sm);background:rgba(107,68,89,.08);display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0}
.gp-slip-text strong{display:block;font-size:12px;font-weight:700;color:var(--text);margin-bottom:2px}
.gp-slip-text small{font-size:11px;color:var(--muted)}
.gp-file-input{position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0)}
.gp-proof-help{display:flex;flex-wrap:wrap;gap:6px;margin-top:3px}
.gp-proof-chip{display:inline-flex;align-items:center;height:24px;padding:0 8px;border-radius:999px;background:rgba(107,68,89,.07);color:var(--text2);font-size:10px;font-weight:700}
.gp-field-error{display:none;color:var(--danger);font-size:11px;font-weight:700;line-height:1.4}
.gp-field-error.show{display:block}

/* Buttons */
.gp-submit{display:flex;align-items:center;justify-content:center;gap:8px;width:100%;height:50px;border-radius:var(--r-md);border:none;background:var(--plum);color:#fffaf3;font-size:14px;font-weight:700;letter-spacing:.02em;box-shadow:0 10px 28px rgba(107,68,89,.28);cursor:pointer;transition:all .3s var(--ease)}
.gp-submit:hover{background:var(--plum-dk);transform:translateY(-2px);box-shadow:0 18px 40px rgba(107,68,89,.32)}
.gp-submit:disabled{opacity:.5;cursor:not-allowed;transform:none;box-shadow:none}
.gp-back{display:flex;align-items:center;justify-content:center;gap:6px;width:100%;height:40px;border-radius:var(--r-md);border:1px solid var(--rule-s);background:transparent;color:var(--text2);font-size:13px;font-weight:600;transition:all .22s;margin-top:6px}
.gp-back:hover{border-color:var(--plum);color:var(--plum)}

/* Flash */
.gp-flash{padding:12px 14px;border-radius:var(--r-sm);font-size:12px;font-weight:600;margin-bottom:16px}
.gp-flash.error{background:#fef2f2;border:1px solid #fecaca;color:var(--danger)}
.gp-flash.info{background:#fef3c7;border:1px solid #fcd34d;color:#92400e}

/* Note box */
.gp-note{padding:12px 14px;border-radius:var(--r-sm);background:#fef3c7;border:1px solid #fcd34d;color:#92400e;font-size:12px;line-height:1.55}

@media(max-width:600px){
  body{padding:20px 14px}
  .gp-card-body{padding:18px}
  .gp-bank-grid{grid-template-columns:repeat(2,1fr)}
  .gp-field-row{grid-template-columns:1fr}
}
</style>
</head>
<body>

<div class="gp-orb gp-orb-1" aria-hidden="true"></div>
<div class="gp-orb gp-orb-2" aria-hidden="true"></div>

<div class="gp-checkout">

  <div class="gp-page-head">
    <div class="gp-eyebrow">Secure Checkout</div>
    <h1 class="gp-page-title">Pay Your <em>Deposit</em></h1>
  </div>

  <?php if ($flash): ?>
    <div class="gp-flash error"><?= $h($flash) ?></div>
  <?php endif; ?>

  <!-- Summary card -->
  <div class="gp-card">
    <div class="gp-card-head">
      <div class="gp-card-label">Payment Summary</div>
      <div class="gp-card-title">Booking <?= $h($bookingRef) ?></div>
    </div>
    <div class="gp-card-body">
      <div>
        <div class="gp-summary-title">Your selection</div>
        <div class="gp-summary-items">
          <?php foreach ($items as $item): ?>
          <div class="gp-row">
            <span>
              <?= $h($item['service_name'] ?? 'Service') ?>
              <?php if (!empty($item['addon_package_name'])): ?>
                <small> · Add-on for <?= $h($item['addon_package_name']) ?></small>
              <?php endif; ?>
            </span>
            <span><?= $money($item['price'] ?? 0) ?></span>
          </div>
          <?php endforeach; ?>
          <div class="gp-divider" style="margin:4px 0"></div>
          <div class="gp-row"><span>Subtotal</span><span><?= $money($total) ?></span></div>
          <?php if ($platformFee > 0): ?>
          <div class="gp-row"><span>Platform service fee (<?= $platformFeePercent ?>%)</span><span>+<?= $money($platformFee) ?></span></div>
          <?php endif; ?>
          <div class="gp-row total"><span>You pay in total</span><span><?= $money($total + $platformFee) ?></span></div>
          <div class="gp-divider" style="margin:8px 0"></div>
          <div class="gp-row" style="font-weight:600"><span>Paying now (<?= $depositPercent ?>% deposit + service fee)</span><span><?= $money($depositWithFee) ?></span></div>
          <div class="gp-row"><span>Remaining balance</span><span><?= $money($balance) ?></span></div>
          <div style="font-size:11px;color:#8e7680;margin-top:6px">Pay the remaining <?= $money($balance) ?> before your event date. The platform fee is a one-time charge included in today's payment.</div>
        </div>
      </div>
    </div>
  </div>

  <!-- Payment form card -->
  <div class="gp-card">
    <div class="gp-card-head">
      <div class="gp-card-label">Step 1</div>
      <div class="gp-card-title">Select your bank or payment app</div>
    </div>
    <div class="gp-card-body">

      <div class="gp-bank-grid" id="bankGrid">
        <?php foreach ($banks as $bankName => $bankInfo): ?>
        <button type="button" class="gp-bank-btn" data-bank="<?= $h($bankName) ?>">
          <div class="gp-bank-icon"><?= $bankIcons[$bankName] ?? '💰' ?></div>
          <div class="gp-bank-label"><?= $h($bankName) ?></div>
        </button>
        <?php endforeach; ?>
      </div>

      <!-- Account info per bank -->
      <?php foreach ($banks as $bankName => $bankInfo): ?>
      <?php $safeId = preg_replace('/[^a-z0-9]/', '-', strtolower($bankName)); ?>
      <div class="gp-account-box" id="acct-<?= $safeId ?>">
        <div class="gp-account-title">Transfer <?= $money($depositWithFee) ?> to this account</div>
        <dl class="gp-account-rows">
          <div class="gp-account-row"><dt>Bank</dt><dd><?= $h($bankName) ?></dd></div>
          <div class="gp-account-row"><dt>Account Name</dt><dd><?= $h($bankInfo['name'] ?? '') ?></dd></div>
          <div class="gp-account-row"><dt>Account / Number</dt><dd><?= $h($bankInfo['account'] ?? '') ?></dd></div>
          <div class="gp-account-row"><dt>Deposit (<?= $depositPercent ?>%)</dt><dd><?= $money($deposit) ?></dd></div>
          <?php if ($platformFee > 0): ?>
          <div class="gp-account-row"><dt>Service fee (<?= $platformFeePercent ?>%, one-time)</dt><dd><?= $money($platformFee) ?></dd></div>
          <?php endif; ?>
          <div class="gp-account-row"><dt>Transfer now</dt><dd><?= $money($depositWithFee) ?></dd></div>
          <div class="gp-account-row"><dt>Remaining balance</dt><dd><?= $money($balance) ?></dd></div>
        </dl>
      </div>
      <?php endforeach; ?>

    </div>
  </div>

  <!-- Transfer details card -->
  <div class="gp-card" id="transferCard" style="display:none">
    <div class="gp-card-head">
      <div class="gp-card-label">Step 2</div>
      <div class="gp-card-title">Fill in your transfer details</div>
    </div>
    <div class="gp-card-body">
      <div class="gp-note">
        After transferring, fill in the details below so our team can verify your payment quickly.
      </div>

      <form method="POST" action="<?= URLROOT ?>/booking/submitManualPayment" enctype="multipart/form-data" id="paymentForm">
        <?= csrf_field() ?>
        <input type="hidden" name="booking_id" value="<?= (int)($booking['id'] ?? 0) ?>">
        <input type="hidden" name="bank_name" id="bankNameInput" value="">

        <div class="gp-transfer-form show">
          <div class="gp-field-row">
            <div class="gp-field">
              <label for="account_name">Your Account Name <span class="req">*</span></label>
              <input type="text" id="account_name" name="account_name" placeholder="e.g. Ko Kyaw Zin" required>
            </div>
            <div class="gp-field">
              <label for="mobile_number">Your Mobile Number <span class="req">*</span></label>
              <input type="text" id="mobile_number" name="mobile_number" placeholder="09XXXXXXXXX" required>
            </div>
          </div>
          <div class="gp-field">
            <label for="transaction_ref">Payment Reference <span class="req">*</span></label>
            <input type="text" id="transaction_ref" name="transaction_ref" placeholder="e.g. TXN-12345678" required>
          </div>
          <div class="gp-field">
            <label for="paid_amount">Amount Paid (MMK) <span class="req">*</span></label>
            <input type="number" id="paid_amount" name="paid_amount" placeholder="<?= (int)$depositWithFee ?>" value="<?= (int)$depositWithFee ?>" min="1" step="1" required>
          </div>
          <div class="gp-field">
            <label for="remark">Remark <span class="opt">(optional)</span></label>
            <input type="text" id="remark" name="remark" placeholder="Any notes about this transfer">
          </div>
          <div class="gp-field">
            <label>Upload Slip / Screenshot <span class="req">*</span></label>
            <label for="slip_image" class="gp-slip-label" id="slipLabel">
              <div class="gp-slip-icon">📷</div>
              <div class="gp-slip-text">
                <strong id="slipFileName">Click to upload screenshot or receipt</strong>
                <small>Required after transfer</small>
              </div>
            </label>
            <input class="gp-file-input" type="file" id="slip_image" name="slip_image" accept=".jpg,.jpeg,.png,.webp,.pdf" required>
            <div class="gp-proof-help" aria-hidden="true">
              <span class="gp-proof-chip">JPG</span>
              <span class="gp-proof-chip">PNG</span>
              <span class="gp-proof-chip">WebP</span>
              <span class="gp-proof-chip">PDF</span>
              <span class="gp-proof-chip">Max 10 MB</span>
            </div>
            <div class="gp-field-error" id="slipError" aria-live="polite"></div>
          </div>

          <button type="submit" class="gp-submit" id="submitBtn" disabled>
            Submit Payment Proof
          </button>
        </div>
      </form>
    </div>
  </div>

  <a class="gp-back" href="<?= URLROOT ?>/booking/detail/<?= (int)($booking['id'] ?? 0) ?>">
    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
    Back to booking
  </a>

</div>

<script>
const bankBtns = document.querySelectorAll('.gp-bank-btn');
const bankNameInput = document.getElementById('bankNameInput');
const transferCard = document.getElementById('transferCard');
const paymentForm = document.getElementById('paymentForm');
const slipInput = document.getElementById('slip_image');
const slipFileName = document.getElementById('slipFileName');
const slipLabel = document.getElementById('slipLabel');
const slipError = document.getElementById('slipError');
const submitBtn = document.getElementById('submitBtn');
const requiredInputs = ['account_name', 'mobile_number', 'transaction_ref', 'paid_amount']
  .map(id => document.getElementById(id))
  .filter(Boolean);
const maxSlipBytes = 10 * 1024 * 1024;
const allowedSlipTypes = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];
const allowedSlipExtensions = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];

function safeId(name) {
  return name.toLowerCase().replace(/[^a-z0-9]/g, '-');
}

function selectBank(bankName) {
  bankBtns.forEach(btn => btn.classList.toggle('active', btn.dataset.bank === bankName));
  document.querySelectorAll('.gp-account-box').forEach(el => el.classList.remove('show'));

  const box = document.getElementById('acct-' + safeId(bankName));
  if (box) box.classList.add('show');

  bankNameInput.value = bankName;
  transferCard.style.display = 'block';
  transferCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  updateSubmitState();
}

bankBtns.forEach(btn => btn.addEventListener('click', () => selectBank(btn.dataset.bank)));

function getSlipError(file) {
  if (!file) return 'Please upload your payment slip or receipt.';
  const extension = file.name.split('.').pop().toLowerCase();
  if (!allowedSlipTypes.includes(file.type) && !allowedSlipExtensions.includes(extension)) {
    return 'Use a JPG, PNG, WebP, or PDF payment proof.';
  }
  if (file.size > maxSlipBytes) {
    return 'Choose a file under 10 MB.';
  }
  return '';
}

function setSlipError(message) {
  if (!slipError || !slipLabel) return;
  slipError.textContent = message;
  slipError.classList.toggle('show', message !== '');
  slipLabel.classList.toggle('has-error', message !== '');
}

function validateSlip(showMessage = false) {
  if (!slipInput) return false;
  const file = slipInput.files[0] || null;
  const message = getSlipError(file);

  slipFileName.textContent = file ? file.name : 'Click to upload screenshot or receipt';
  slipLabel.classList.toggle('has-file', !!file && message === '');

  if (showMessage || file || message === '') {
    setSlipError(message);
  } else {
    setSlipError('');
  }

  return message === '';
}

function updateSubmitState() {
  if (!submitBtn) return;
  const fieldsReady = bankNameInput.value !== ''
    && requiredInputs.every(input => input.value.trim() !== '');
  submitBtn.disabled = !(fieldsReady && validateSlip(false));
}

requiredInputs.forEach(input => input.addEventListener('input', updateSubmitState));

if (slipInput) {
  slipInput.addEventListener('change', function () {
    validateSlip(true);
    updateSubmitState();
  });
}

if (paymentForm) {
  paymentForm.addEventListener('submit', function (event) {
    updateSubmitState();
    if (submitBtn.disabled || !validateSlip(true)) {
      event.preventDefault();
      if (slipInput) slipInput.focus();
    }
  });
}
</script>
</body>
</html>
