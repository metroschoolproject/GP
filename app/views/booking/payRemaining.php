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
$bankLogos = [
    'KBZ Pay'           => URLROOT . '/app/views/main/images/kbzLogo.png',
    'Wave Money'        => URLROOT . '/app/views/main/images/waveLogo.jpeg',
    'AYA Pay'           => URLROOT . '/app/views/main/images/ayaLogo.png',
    'Yoma Bank'         => URLROOT . '/app/views/main/images/yomaLogo.png',
    'CB Bank'           => URLROOT . '/app/views/main/images/CBLogo.jpg',
    'Visa / MasterCard' => URLROOT . '/app/views/main/images/visaLogo.png',
];
$flash = $_SESSION['remaining_payment_flash'] ?? '';
unset($_SESSION['remaining_payment_flash']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" type="image/png" href="<?= URLROOT ?>/public/images/home/gp_logo.png">
<title>Pay Remaining Balance — Golden Promise</title>
<?php $publicCssVersion = file_exists(APPROOT . '/../public/css/app.css') ? filemtime(APPROOT . '/../public/css/app.css') : time(); ?>
<link rel="stylesheet" href="<?= URLROOT ?>/public/css/app.css?v=<?= $publicCssVersion ?>">
<style>
:root{--bg:#f2e4d4;--surface:#faf6f1;--card:#fcf8f5;--rule:rgba(178,143,110,.22);--rule-s:rgba(178,143,110,.45);--plum:#6b4459;--plum-dk:#4e3141;--plum-lt:#9b7289;--gold:#b8924a;--muted:#a08878;--text:#1a1118;--text2:#5c4a54;--green:#166534;--blue:#2563eb;--danger:#b94b4b;--r-sm:8px;--r-md:14px;--r-lg:20px}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{background:var(--bg);color:var(--text);font-family:'Poppins',system-ui,sans-serif;font-size:14px;line-height:1.6;min-height:100vh;display:flex;flex-direction:column;align-items:center;padding:40px 20px}
a{color:inherit;text-decoration:none}
body > .gp-shared-footer{width:calc(100% + 40px);margin-top:132px;margin-right:-20px;margin-bottom:-40px;margin-left:-20px}

.checkout{position:relative;z-index:1;width:100%;max-width:1120px}
.page-head{margin-bottom:28px;text-align:left}
.eyebrow{font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--gold);margin-bottom:8px}
.page-title{font-family:'Playfair Display',Georgia,serif;font-size:clamp(28px,4vw,42px);font-weight:600;color:var(--text);line-height:.95;letter-spacing:-.02em}
.page-title em{font-style:italic;color:var(--plum-lt)}

.checkout-layout{display:grid;grid-template-columns:minmax(0,1fr) 380px;gap:22px;align-items:start}
.checkout-main,.checkout-side{min-width:0}
.checkout-side{position:sticky;top:28px}
.checkout-side .card:first-child{min-height:420px;display:flex;flex-direction:column}
.checkout-side .card:first-child .card-body{flex:1}
.card{background:var(--card);border:1px solid rgba(184,146,74,.38);border-radius:20px;padding:0;margin-bottom:18px;box-shadow:0 20px 60px rgba(26,17,24,.08);overflow:hidden}
.card-head{font-family:'Poppins',system-ui,sans-serif;font-size:17px;font-weight:600;color:var(--plum);padding:20px 24px;border-bottom:1px solid var(--rule);background:transparent}
.card-label{font-size:10px;font-weight:600;letter-spacing:.12em;text-transform:uppercase;color:var(--muted);margin-bottom:4px}
.card-subline{margin-top:5px;font-size:12px;font-weight:600;color:var(--gold)}
.card-body{padding:22px 24px;display:flex;flex-direction:column;gap:18px}

.summary-row{display:flex;justify-content:space-between;align-items:baseline;gap:16px;padding:6px 0;font-size:13px;color:var(--text2)}
.summary-row strong,.summary-row span:last-child{color:var(--text);font-weight:600}
.summary-row.total{font-weight:600;color:var(--text);border-top:1px solid var(--rule);padding-top:12px;margin-top:4px}
.summary-highlight{padding:18px 16px;border-radius:12px;border:1px solid rgba(22,101,52,.18);background:#f0fdf4}
.summary-highlight-label{font-size:10px;font-weight:600;letter-spacing:.12em;text-transform:uppercase;color:var(--green);margin-bottom:8px}
.summary-highlight-amount{font-size:26px;font-weight:600;line-height:1.25;color:var(--text)}
.summary-note{margin-top:auto;padding-top:14px;border-top:1px solid var(--rule);color:#8e7680;font-size:12px;line-height:1.7}

.bank-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:8px}
.bank-option{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:6px;min-height:94px;padding:10px 8px;border:2px solid var(--rule);border-radius:12px;cursor:pointer;transition:all .22s;background:transparent;text-align:center}
.bank-option:hover{border-color:var(--plum);background:rgba(107,68,89,.05)}
.bank-option.selected{border-color:var(--plum);background:rgba(107,68,89,.14)}
.bank-option input{display:none}
.bank-icon{display:grid;place-items:center;width:40px;height:40px;border:0;background:transparent;border-radius:0;font-size:20px;overflow:visible}
.bank-logo{display:block;max-width:36px;max-height:36px;width:auto;height:auto;object-fit:contain}
.bank-info{display:grid;gap:2px;justify-items:center;min-width:0}
.bank-name{font-weight:600;font-size:10px;color:var(--text2);line-height:1.25}
.bank-acct{font-size:9px;color:var(--muted);font-family:monospace;line-height:1.25;max-width:100%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.bank-check{display:none}
.account-box{border:1px solid var(--rule-s);border-radius:var(--r-md);background:var(--surface);padding:14px 16px;display:none}
.account-box.show{display:block}
.account-title{font-size:10px;font-weight:600;letter-spacing:.1em;text-transform:uppercase;color:var(--gold);margin-bottom:10px}
.account-rows{display:flex;flex-direction:column;gap:7px}
.account-row{display:flex;justify-content:space-between;align-items:baseline;font-size:12px;padding-bottom:7px;border-bottom:1px solid var(--rule)}
.account-row:last-child{border-bottom:none;padding-bottom:0}
.account-row dt{color:var(--muted);font-weight:600;font-size:10px;letter-spacing:.06em;text-transform:uppercase}
.account-row dd{margin:0;font-weight:600;color:var(--text);font-family:monospace;font-size:13px}

.field{display:flex;flex-direction:column;gap:5px;margin-bottom:0}
.field label{display:block;font-size:11px;font-weight:700;letter-spacing:.04em;color:var(--muted)}
.field input,.field select{width:100%;height:48px;border:1px solid var(--rule-s);border-radius:12px;padding:0 14px;font-size:13px;font-family:inherit;color:var(--text);background:var(--card);outline:none;transition:border-color .18s,box-shadow .18s,background .18s}
.field input:focus,.field select:focus{border-color:var(--gold);background:rgba(107,68,89,.055);box-shadow:0 0 0 3px rgba(184,146,74,.12),0 8px 20px rgba(184,146,74,.08)}
.field:hover input,.field:hover select{border-color:rgba(184,146,74,.46);background:rgba(107,68,89,.035)}
.field input[readonly]{background:var(--surface);color:var(--muted)}
.field-row{display:grid;grid-template-columns:1fr 1fr;gap:16px}

.btn-primary{display:flex;align-items:center;justify-content:center;width:100%;height:50px;border:0;border-radius:var(--r-md);background:var(--plum);color:#fffaf3;font-size:14px;font-weight:700;font-family:inherit;cursor:pointer;transition:all .25s;box-shadow:0 10px 28px rgba(107,68,89,.28)}
.btn-primary:hover{background:var(--plum-dk);transform:translateY(-2px);box-shadow:0 18px 40px rgba(107,68,89,.32)}
.btn-primary:disabled{opacity:.5;cursor:default}

.flash{background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:12px 16px;margin-bottom:16px;font-size:13px;color:#991b1b}

.back-link{display:inline-flex;align-items:center;justify-content:center;gap:6px;width:auto;min-width:142px;height:36px;padding:0 16px;border-radius:999px;border:1px solid rgba(184,146,74,.58);background:rgba(255,250,245,.62);color:#7a5c35;font-size:12px;font-weight:700;transition:all .22s;margin-bottom:18px}
.back-link:hover{background:#fffaf5;border-color:var(--gold);color:var(--plum);transform:translateY(-1px)}

.tip{background:#fffdf4;border:1px solid rgba(184,146,74,.42);border-radius:var(--r-sm);padding:12px 14px;margin-top:0;font-size:12px;color:#7a5c35;line-height:1.55;box-shadow:0 8px 20px rgba(184,146,74,.08)}

.amount-hint{font-size:11px;color:var(--muted);margin-top:4px}
.amount-error{font-size:11px;color:#991b1b;margin-top:4px;display:none}
.file-upload{display:grid;grid-template-columns:64px minmax(0,1fr);align-items:center;gap:16px;min-height:76px;padding:12px 18px;border:1px dashed rgba(178,143,110,.58);border-radius:14px;background:#fffaf5;cursor:pointer;transition:border-color .18s,background .18s,box-shadow .18s}
.file-upload:hover{border-color:var(--plum);background:rgba(107,68,89,.04);box-shadow:0 8px 22px rgba(44,36,32,.06)}
.file-upload-icon{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:3px;color:var(--text);font-size:11px;font-weight:600}
.file-upload-icon svg{width:22px;height:22px;stroke:currentColor;stroke-width:1.9}
.file-upload-text{text-align:center;justify-self:center}
.file-upload-text strong{display:block;font-size:13px;font-weight:500;color:var(--text);line-height:1.35;margin-bottom:3px}
.file-upload-text small{display:block;font-size:11px;color:var(--muted);text-align:center}
.file-input{position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0)}

.history-item{display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid var(--rule);font-size:12px}
.history-item:last-child{border-bottom:none}
.history-status{font-size:10px;font-weight:700;padding:2px 8px;border-radius:10px}
.payment-history-pager{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-top:2px;padding-top:12px;border-top:1px solid var(--rule)}
.payment-page-info{font-size:11px;font-weight:700;color:var(--muted)}
.payment-page-buttons{display:flex;align-items:center;justify-content:flex-end;gap:5px;flex-wrap:wrap}
.payment-page-btn{display:inline-flex;align-items:center;justify-content:center;min-width:30px;height:30px;padding:0 9px;border:1px solid var(--rule);border-radius:8px;background:var(--card);color:var(--text2);font-family:inherit;font-size:11px;font-weight:700;cursor:pointer;transition:all .16s}
.payment-page-btn:hover:not(:disabled){border-color:var(--gold);color:var(--plum);background:#fffaf5}
.payment-page-btn.is-active{border-color:var(--plum);background:var(--plum);color:#fcf8f5}
.payment-page-btn:disabled{opacity:.38;cursor:default}
@media(max-width:760px){
  .checkout-layout{grid-template-columns:1fr}
  .checkout-side{position:static;order:-1}
  .bank-grid{grid-template-columns:repeat(2,1fr)}
  .field-row{grid-template-columns:1fr}
  .payment-history-pager{align-items:flex-start;flex-direction:column}
}
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

  <div class="checkout-layout">
    <div class="checkout-main">
      <!-- Bank selection -->
      <form method="POST" action="<?= URLROOT ?>/booking/submitRemainingPayment" enctype="multipart/form-data" id="paymentForm">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
        <input type="hidden" name="booking_id" value="<?= (int)($booking['id'] ?? 0) ?>">

        <div class="card">
          <div class="card-head">
            <div class="card-label">Payment Method</div>
            Transfer to Golden Promise
          </div>
          <div class="card-body">
            <div class="bank-grid">
              <?php foreach ($banks as $bankName => $info): ?>
              <label class="bank-option" data-bank="<?= $h($bankName) ?>">
                <input type="radio" name="bank_name" value="<?= $h($bankName) ?>" required>
                <span class="bank-icon">
                  <?php if (!empty($bankLogos[$bankName])): ?>
                    <img class="bank-logo" src="<?= $h($bankLogos[$bankName]) ?>" alt="<?= $h($bankName) ?> logo" loading="lazy">
                  <?php else: ?>
                    <?= $bankIcons[$bankName] ?? '🏦' ?>
                  <?php endif; ?>
                </span>
                <div class="bank-info">
                  <div class="bank-name"><?= $h($bankName) ?></div>
                </div>
              </label>
              <?php endforeach; ?>
            </div>

            <?php foreach ($banks as $bankName => $info): ?>
            <?php $safeId = preg_replace('/[^a-z0-9]/', '-', strtolower($bankName)); ?>
            <div class="account-box" id="acct-<?= $safeId ?>">
              <div class="account-title">Payment Details</div>
              <dl class="account-rows">
                <div class="account-row"><dt>Bank</dt><dd><?= $h($bankName) ?></dd></div>
                <div class="account-row"><dt>Account Name</dt><dd><?= $h($info['name'] ?? '') ?></dd></div>
                <div class="account-row"><dt>Account / Number</dt><dd><?= $h($info['account'] ?? '') ?></dd></div>
              </dl>
            </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Transfer details -->
        <div class="card" id="remainingTransferCard" style="display:none">
          <div class="card-head">
            <div class="card-label">Payment Proof</div>
            Your Transfer Details
          </div>
          <div class="card-body">
            <div class="field-row">
              <div class="field">
                <label>Your Account Name</label>
                <input type="text" name="account_name" placeholder="Name on your account" required>
              </div>

              <div class="field">
                <label>Phone / Account Number</label>
                <input type="text" name="mobile_number" placeholder="09-XXX-XXXXXXX" required>
              </div>
            </div>

            <div class="field-row">
              <div class="field">
                <label>Transaction Reference / ID</label>
                <input type="text" name="transaction_ref" placeholder="e.g., TXN123456789" required>
              </div>

              <div class="field">
                <label>Amount to Transfer</label>
                <input type="text" name="paid_amount" id="paidAmount" placeholder="Enter amount" required
                       inputmode="numeric"
                       value="<?= number_format($balance, 0, '.', '') ?>">
                <div class="amount-hint">Min: <?= $money($minPayment) ?> · Max: <?= $money($balance) ?></div>
                <div class="amount-error" id="amountError"></div>
              </div>
            </div>

            <div class="field">
              <label>Payment Slip <span style="color:var(--danger)">*</span></label>
              <label class="file-upload" for="remainingSlipImage">
                <span class="file-upload-icon" aria-hidden="true">
                  <svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 16V4"></path>
                    <path d="m7 9 5-5 5 5"></path>
                    <path d="M20 16v3a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-3"></path>
                  </svg>
                  Upload
                </span>
                <span class="file-upload-text">
                  <strong id="remainingSlipName">Drag &amp; drop or click to upload your transfer slips</strong>
                  <small>Up to 5 files. JPG, PNG, WebP, HEIC, HEIF or PDF accepted</small>
                </span>
              </label>
              <input class="file-input" type="file" id="remainingSlipImage" name="slip_image[]" accept="image/jpeg,image/png,image/webp,image/heic,image/heif,.jpg,.jpeg,.png,.webp,.heic,.heif,.pdf,application/pdf" multiple required>
            </div>
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

    <aside class="checkout-side">
      <!-- Summary -->
      <div class="card">
        <div class="card-head">
          <div class="card-label">Payment Overview</div>
          Booking #<?= $h($bookingRef) ?>
          <?php if ($eventDate): ?>
            <div class="card-subline">Due Date : <?= date('M d, Y', strtotime($eventDate)) ?></div>
          <?php endif; ?>
        </div>
        <div class="card-body">
          <div class="summary-row"><span>Booking total</span><span><?= $money($total) ?></span></div>
          <div class="summary-row"><span>Already paid</span><span><?= $money($paid) ?></span></div>
          <div class="summary-highlight">
            <div class="summary-highlight-label">Amount to Transfer</div>
            <div class="summary-highlight-amount"><?= $money($balance) ?></div>
          </div>
          <div class="summary-row total"><span>Remaining balance</span><span><?= $money($balance) ?></span></div>
          <div class="summary-note">
            Submit your remaining payment proof after transfer. Our team will verify it before marking the booking fully paid.
          </div>
        </div>
      </div>

      <?php if (!empty($remainingPayments)): ?>
      <!-- Payment History -->
      <div class="card">
        <div class="card-head">
          <div class="card-label">Previous Transfers</div>
          Payment History
        </div>
        <div class="card-body">
          <div data-payment-history-list data-page-size="5">
          <?php foreach ($remainingPayments as $rp): ?>
          <div class="history-item" data-payment-history-item>
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
          <div class="payment-history-pager" data-payment-history-pager hidden>
            <div class="payment-page-info" data-payment-page-info></div>
            <div class="payment-page-buttons" data-payment-page-buttons></div>
          </div>
        </div>
      </div>
      <?php endif; ?>
    </aside>
  </div>
</div>

<script>
const balance = <?= $balance ?>;
const minPayment = <?= $minPayment ?>;
const amountInput = document.getElementById('paidAmount');
const amountError = document.getElementById('amountError');
const submitBtn = document.getElementById('submitBtn');
const remainingSlipInput = document.getElementById('remainingSlipImage');
const remainingSlipName = document.getElementById('remainingSlipName');
const remainingTransferCard = document.getElementById('remainingTransferCard');
const maxSlipBytes = 10 * 1024 * 1024;
const allowedSlipTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/heic', 'image/heif', 'application/pdf'];
const allowedSlipExtensions = ['jpg', 'jpeg', 'png', 'webp', 'heic', 'heif', 'pdf'];

function safeId(name) {
  return name.toLowerCase().replace(/[^a-z0-9]/g, '-');
}

// Bank selection visual toggle
document.querySelectorAll('.bank-option').forEach(el => {
  el.addEventListener('click', () => {
    document.querySelectorAll('.bank-option').forEach(b => b.classList.remove('selected'));
    document.querySelectorAll('.account-box').forEach(box => box.classList.remove('show'));
    el.classList.add('selected');
    el.querySelector('input').checked = true;
    const box = document.getElementById('acct-' + safeId(el.dataset.bank || ''));
    if (box) box.classList.add('show');
    if (remainingTransferCard) {
      remainingTransferCard.style.display = 'block';
      remainingTransferCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
  });
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

if (remainingSlipInput && remainingSlipName) {
  remainingSlipInput.addEventListener('change', function () {
    const files = Array.from(this.files || []);
    remainingSlipName.textContent = files.length > 1
      ? files.length + ' payment slips selected'
      : (files[0] ? files[0].name : 'Drag & drop or click to upload your transfer slips');
  });
}

function validateSlipFiles() {
  const files = remainingSlipInput ? Array.from(remainingSlipInput.files || []) : [];
  if (!files.length) {
    alert('Please upload your payment slip or receipt.');
    return false;
  }
  if (files.length > 5) {
    alert('Upload no more than 5 payment slips.');
    return false;
  }
  for (const file of files) {
    const extension = file.name.split('.').pop().toLowerCase();
    if (!allowedSlipTypes.includes(file.type) && !allowedSlipExtensions.includes(extension)) {
      alert('Use JPG, PNG, WebP, HEIC, HEIF, or PDF payment proofs only.');
      return false;
    }
    if (file.size > maxSlipBytes) {
      alert('Each file must be under 10 MB.');
      return false;
    }
  }
  return true;
}

// Form submit — set clean numeric value
document.getElementById('paymentForm').addEventListener('submit', (event) => {
  if (!validateSlipFiles()) {
    event.preventDefault();
    remainingSlipInput?.focus();
    return;
  }
  const raw = amountInput.value.replace(/[^0-9]/g, '');
  amountInput.value = raw;
});

document.querySelectorAll('[data-payment-history-list]').forEach(function(list) {
  const items = Array.from(list.querySelectorAll('[data-payment-history-item]'));
  const pageSize = Math.max(1, parseInt(list.dataset.pageSize || '5', 10));
  const pager = list.parentElement?.querySelector('[data-payment-history-pager]');
  const info = pager?.querySelector('[data-payment-page-info]');
  const buttons = pager?.querySelector('[data-payment-page-buttons]');
  const totalPages = Math.ceil(items.length / pageSize);
  let currentPage = 1;

  if (!pager || !info || !buttons || totalPages <= 1) return;
  pager.hidden = false;

  function render() {
    currentPage = Math.min(Math.max(currentPage, 1), totalPages);
    const start = (currentPage - 1) * pageSize;
    const end = Math.min(start + pageSize, items.length);

    items.forEach(function(item, index) {
      item.style.display = index >= start && index < end ? 'flex' : 'none';
    });

    info.textContent = 'Showing ' + (start + 1) + '-' + end + ' of ' + items.length;
    buttons.innerHTML = '';

    const makeButton = function(label, page, options) {
      const button = document.createElement('button');
      button.type = 'button';
      button.className = 'payment-page-btn' + (options?.active ? ' is-active' : '');
      button.textContent = label;
      button.disabled = !!options?.disabled;
      if (options?.active) button.setAttribute('aria-current', 'page');
      button.addEventListener('click', function() {
        currentPage = page;
        render();
      });
      buttons.appendChild(button);
    };

    makeButton('Prev', currentPage - 1, {disabled: currentPage === 1});
    for (let page = 1; page <= totalPages; page++) {
      makeButton(String(page), page, {active: page === currentPage});
    }
    makeButton('Next', currentPage + 1, {disabled: currentPage === totalPages});
  }

  render();
});
</script>
<?php require APPROOT . '/views/layouts/customerFooter.php'; ?>
</body>
</html>
