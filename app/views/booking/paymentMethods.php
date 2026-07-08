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
$firstItem = $items[0] ?? [];
$selectedServiceName = trim((string)($firstItem['service_name'] ?? 'Selected Service'));

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
<?php include APPROOT . '/views/partials/ga-tracking.php'; ?>
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
body > .gp-shared-footer{width:calc(100% + 40px);margin-top:132px;margin-right:-20px;margin-bottom:-40px;margin-left:-20px}

.gp-orb{position:fixed;border-radius:50%;filter:blur(80px);opacity:.3;z-index:0;pointer-events:none}
.gp-orb-1{width:500px;height:500px;background:radial-gradient(circle,rgba(107,68,89,.10) 0%,transparent 70%);top:-150px;right:-80px}
.gp-orb-2{width:400px;height:400px;background:radial-gradient(circle,rgba(184,146,74,.08) 0%,transparent 70%);bottom:-100px;left:-80px}

.gp-checkout{position:relative;z-index:1;width:100%;max-width:1120px}
.gp-page-head{margin-bottom:28px;text-align:left}
.gp-eyebrow{font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--gold);margin-bottom:8px}
.gp-page-title{font-family:var(--font-d);font-size:clamp(28px,4vw,42px);font-weight:600;color:var(--text);line-height:.95;letter-spacing:-.02em}
.gp-page-title em{font-style:italic;color:var(--plum-lt)}

.gp-checkout-layout{display:grid;grid-template-columns:minmax(0,1fr) 380px;gap:22px;align-items:start}
.gp-checkout-main{min-width:0}
.gp-checkout-side{position:sticky;top:28px;min-width:0}

.gp-card{background:var(--card);border-radius:var(--r-lg);border:1px solid rgba(184,146,74,.38);overflow:hidden;box-shadow:0 20px 60px rgba(26,17,24,.08);margin-bottom:16px}
.gp-checkout-side .gp-card{min-height:560px;display:flex;flex-direction:column}
.gp-checkout-side .gp-card-body{flex:1}
.gp-card-head{padding:20px 24px;border-bottom:1px solid var(--rule);position:relative;background:transparent}
.gp-card-label{font-size:10px;font-weight:600;letter-spacing:.12em;text-transform:uppercase;color:var(--muted);margin-bottom:4px}
.gp-card-title{font-family:var(--font-b);font-size:17px;font-weight:600;color:var(--plum)}
.gp-card-body{padding:22px 24px;display:flex;flex-direction:column;gap:18px}

.gp-summary-title{font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--muted);margin-bottom:10px}
.gp-summary-items{display:flex;flex-direction:column;gap:7px}
.gp-row{display:flex;justify-content:space-between;align-items:baseline;font-size:13px}
.gp-row span:first-child{padding-right:14px}
.gp-row.total{font-weight:600;padding-top:10px;border-top:1px solid var(--rule);margin-top:2px}
.gp-row.deposit{color:var(--plum);font-weight:700}
.gp-row.balance{color:var(--muted);font-size:12px}
.gp-divider{height:1px;background:var(--rule)}
.gp-summary-paynow{margin:6px 0;padding:16px;border:1px solid rgba(107,68,89,.16);border-radius:var(--r-md);background:rgba(107,68,89,.06)}
.gp-summary-paynow-label{font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--muted);margin-bottom:4px}
.gp-summary-paynow-amount{font-family:var(--font-d);font-size:30px;font-weight:600;line-height:1;color:var(--plum)}
.gp-summary-note{margin-top:auto;padding-top:14px;border-top:1px solid var(--rule);color:#8e7680;font-size:11px;line-height:1.65}
.gp-summary-assurance{display:grid;gap:8px;margin-top:14px}
.gp-summary-assurance span{display:flex;align-items:center;gap:8px;color:var(--text2);font-size:11px;font-weight:700}
.gp-summary-assurance span::before{content:'';width:7px;height:7px;border-radius:999px;background:var(--gold);box-shadow:0 0 0 4px rgba(184,146,74,.12)}
.gp-overview{display:flex;flex-direction:column;gap:18px}
.gp-overview-ref{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;color:var(--plum);font-size:13px;font-weight:800;letter-spacing:.02em}
.gp-overview-section{display:grid;gap:8px}
.gp-overview-section.is-selected-service{padding-bottom:14px;border-bottom:1px solid var(--rule)}
.gp-overview-label{font-size:10px;font-weight:600;letter-spacing:.12em;text-transform:uppercase;color:var(--muted)}
.gp-overview-service{color:var(--text);font-size:15px;font-weight:600;line-height:1.35}
.gp-overview-row{display:flex;align-items:baseline;justify-content:space-between;gap:16px;color:var(--text2);font-size:13px}
.gp-overview-row strong{color:var(--text);font-weight:600}
.gp-overview-divider{height:1px;background:var(--rule);margin:2px 0}
.gp-overview-total{display:flex;align-items:baseline;justify-content:space-between;gap:16px;color:var(--text);font-size:14px;font-weight:600}
.gp-overview-transfer{padding:18px 16px;border-radius:12px;border:1px solid rgba(22,101,52,.18);background:#f0fdf4}
.gp-overview-transfer .gp-overview-label{color:var(--green)}
.gp-overview-amount{margin-top:8px;font-family:var(--font-b);font-size:26px;font-weight:600;line-height:1.25;color:var(--text)}
.gp-overview-due{margin-top:8px;color:var(--text2);font-size:12px;font-weight:600;line-height:1.45}
.gp-overview-note{margin-top:auto;padding-top:14px;border-top:1px solid var(--rule);color:#8e7680;font-size:12px;line-height:1.7}

/* Bank grid */
.gp-bank-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:8px}
.gp-bank-btn{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:6px;min-height:86px;padding:10px 8px;border:2px solid var(--rule);border-radius:12px;background:transparent;cursor:pointer;transition:all .22s;font-family:var(--font-b)}
.gp-bank-btn:hover{border-color:var(--plum);background:rgba(107,68,89,.05)}
.gp-bank-btn.active{border-color:var(--plum);background:rgba(107,68,89,.14)}
.gp-bank-icon{display:grid;place-items:center;width:40px;height:40px;border-radius:0;background:transparent;border:0;font-size:20px;overflow:visible}
.gp-bank-logo{display:block;max-width:36px;max-height:36px;width:auto;height:auto;object-fit:contain}
.gp-bank-label{font-size:10px;font-weight:600;color:var(--text2);text-align:center;line-height:1.25}

/* Account info box */
.gp-account-box{border:1px solid var(--rule-s);border-radius:var(--r-md);background:var(--surface);padding:14px 16px;display:none}
.gp-account-box.show{display:block}
.gp-account-title{font-size:10px;font-weight:600;letter-spacing:.1em;text-transform:uppercase;color:var(--gold);margin-bottom:10px}
.gp-account-rows{display:flex;flex-direction:column;gap:7px}
.gp-account-row{display:flex;justify-content:space-between;align-items:baseline;font-size:12px;padding-bottom:7px;border-bottom:1px solid var(--rule)}
.gp-account-row:last-child{border-bottom:none;padding-bottom:0}
.gp-account-row dt{color:var(--muted);font-weight:600;font-size:10px;letter-spacing:.06em;text-transform:uppercase}
.gp-account-row dd{margin:0;font-weight:600;color:var(--text);font-family:monospace;font-size:13px}

/* Transfer form */
.gp-transfer-form{display:none;flex-direction:column;gap:16px}
.gp-transfer-form.show{display:flex}
.gp-field{display:flex;flex-direction:column;gap:5px}
.gp-field label{font-size:11px;font-weight:700;letter-spacing:.04em;color:var(--muted)}
.gp-field label .req{color:var(--danger)}
.gp-field label .opt{color:var(--muted);font-weight:400}
.gp-field input{padding:10px 13px;border:1px solid var(--rule-s);border-radius:var(--r-sm);font-size:13px;font-family:var(--font-b);background:var(--surface);color:var(--text);transition:border-color .18s,box-shadow .18s}
.gp-field input:focus{outline:none;border-color:var(--plum);box-shadow:0 0 0 3px rgba(107,68,89,.1)}
.gp-float-field{position:relative;gap:0;padding-top:9px}
.gp-float-field label{
  position:absolute;
  left:15px;
  top:0;
  z-index:1;
  transform:translateY(0);
  color:var(--plum);
  font-size:10px;
  font-weight:700;
  letter-spacing:.04em;
  text-transform:uppercase;
  background:var(--card);
  padding:0 6px;
  border-radius:999px;
  pointer-events:none;
  transition:none;
}
.gp-float-field input{
  width:100%;
  min-height:48px;
  padding:13px 14px;
  border-radius:12px;
  background:var(--card);
  box-shadow:0 1px 3px rgba(44,36,32,.04);
  transition:none;
}
.gp-float-field input::placeholder{color:rgba(160,136,120,.72)}
.gp-float-field input:focus{
  border-color:var(--gold);
  background:rgba(107,68,89,.055);
  box-shadow:0 0 0 3px rgba(184,146,74,.12),0 8px 20px rgba(184,146,74,.08);
  transform:none;
}
.gp-float-field:focus-within input{background:rgba(107,68,89,.055)}
.gp-float-field:hover input{
  border-color:rgba(184,146,74,.46);
  background:rgba(107,68,89,.035);
  box-shadow:0 2px 8px rgba(44,36,32,.06);
}
.gp-float-field input:-webkit-autofill,
.gp-float-field input:-webkit-autofill:hover,
.gp-float-field input:-webkit-autofill:focus{
  -webkit-box-shadow:0 0 0 1000px var(--card) inset;
  box-shadow:0 0 0 1000px var(--card) inset;
  -webkit-text-fill-color:var(--text);
  transition:background-color 5000s ease-in-out 0s;
}
.gp-field-row{display:grid;grid-template-columns:1fr 1fr;gap:16px}

/* Slip upload */
.gp-slip-label{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;min-height:112px;padding:16px 18px;border:1px dashed rgba(178,143,110,.58);border-radius:14px;background:#fffaf5;cursor:pointer;transition:border-color .18s,background .18s,box-shadow .18s}
.gp-slip-label:hover{border-color:var(--plum);background:rgba(107,68,89,.04);box-shadow:0 8px 22px rgba(44,36,32,.06)}
.gp-slip-label.has-file{border-style:solid;border-color:rgba(22,101,52,.45);background:#f0fdf4}
.gp-slip-label.has-error{border-color:#fca5a5;background:#fef2f2}
.gp-slip-icon{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:3px;color:var(--text);font-size:11px;font-weight:600}
.gp-slip-icon svg{width:22px;height:22px;stroke:currentColor;stroke-width:1.9}
.gp-slip-icon span{line-height:1;color:var(--text)}
.gp-slip-text{text-align:center;justify-self:center}
.gp-slip-text strong{display:block;font-size:13px;font-weight:500;color:var(--text);line-height:1.35;margin-bottom:3px}
.gp-slip-text small{display:block;font-size:11px;color:var(--muted);text-align:center}
.gp-file-input{position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0)}
.gp-proof-help{display:flex;flex-wrap:wrap;gap:6px;margin-top:3px}
.gp-proof-chip{display:inline-flex;align-items:center;height:24px;padding:0 8px;border-radius:999px;background:rgba(107,68,89,.07);color:var(--text2);font-size:10px;font-weight:700}
.gp-field-error{display:none;color:var(--danger);font-size:11px;font-weight:700;line-height:1.4}
.gp-field-error.show{display:block}

/* Buttons */
.gp-submit{display:flex;align-items:center;justify-content:center;gap:8px;width:100%;height:50px;border-radius:var(--r-md);border:none;background:var(--plum);color:#fffaf3;font-size:14px;font-weight:700;letter-spacing:.02em;box-shadow:0 10px 28px rgba(107,68,89,.28);cursor:pointer;transition:all .3s var(--ease)}
.gp-submit:hover{background:var(--plum-dk);transform:translateY(-2px);box-shadow:0 18px 40px rgba(107,68,89,.32)}
.gp-submit:disabled{opacity:.5;cursor:not-allowed;transform:none;box-shadow:none}
.gp-back{display:inline-flex;align-items:center;justify-content:center;gap:6px;width:auto;min-width:142px;height:36px;padding:0 16px;border-radius:999px;border:1px solid rgba(184,146,74,.58);background:rgba(255,250,245,.62);color:#7a5c35;font-size:12px;font-weight:700;transition:all .22s;margin-bottom:18px}
.gp-back:hover{background:#fffaf5;border-color:var(--gold);color:var(--plum);transform:translateY(-1px)}

/* Flash */
.gp-flash{padding:12px 14px;border-radius:var(--r-sm);font-size:12px;font-weight:600;margin-bottom:16px}
.gp-flash.error{background:#fef2f2;border:1px solid #fecaca;color:var(--danger)}
.gp-flash.info{background:#fef3c7;border:1px solid #fcd34d;color:#92400e}

/* Note box */
.gp-note{padding:12px 14px;border-radius:var(--r-sm);background:#fffdf4;border:1px solid rgba(184,146,74,.42);color:#7a5c35;font-size:12px;line-height:1.55;box-shadow:0 8px 20px rgba(184,146,74,.08)}

@media(max-width:600px){
  body{padding:20px 14px}
  .gp-page-head{text-align:left}
  .gp-checkout-layout{grid-template-columns:1fr}
  .gp-checkout-side{position:static;order:-1}
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

  <a class="gp-back" href="<?= URLROOT ?>/booking/detail/<?= (int)($booking['id'] ?? 0) ?>">
    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
    Back to booking
  </a>

  <div class="gp-page-head">
    <div class="gp-eyebrow">Secure Checkout</div>
    <h1 class="gp-page-title">Pay Your <em>Deposit</em></h1>
  </div>

  <?php if ($flash): ?>
    <div class="gp-flash error"><?= $h($flash) ?></div>
  <?php endif; ?>

  <div class="gp-checkout-layout">
    <div class="gp-checkout-main">
      <!-- Payment form card -->
      <div class="gp-card">
        <div class="gp-card-head">
         
          <div class="gp-card-title">Select payment method</div>
        </div>
        <div class="gp-card-body">

          <div class="gp-bank-grid" id="bankGrid">
            <?php foreach ($banks as $bankName => $bankInfo): ?>
            <button type="button" class="gp-bank-btn" data-bank="<?= $h($bankName) ?>">
              <div class="gp-bank-icon">
                <?php if (!empty($bankLogos[$bankName])): ?>
                  <img class="gp-bank-logo" src="<?= $h($bankLogos[$bankName]) ?>" alt="<?= $h($bankName) ?> logo" loading="lazy">
                <?php else: ?>
                  <?= $bankIcons[$bankName] ?? '💰' ?>
                <?php endif; ?>
              </div>
              <div class="gp-bank-label"><?= $h($bankName) ?></div>
            </button>
            <?php endforeach; ?>
          </div>

          <!-- Account info per bank -->
          <?php foreach ($banks as $bankName => $bankInfo): ?>
          <?php $safeId = preg_replace('/[^a-z0-9]/', '-', strtolower($bankName)); ?>
          <div class="gp-account-box" id="acct-<?= $safeId ?>">
            <div class="gp-account-title">Payment Details</div>
            <dl class="gp-account-rows">
              <div class="gp-account-row"><dt>Bank</dt><dd><?= $h($bankName) ?></dd></div>
              <div class="gp-account-row"><dt>Account Name</dt><dd><?= $h($bankInfo['name'] ?? '') ?></dd></div>
              <div class="gp-account-row"><dt>Account / Number</dt><dd><?= $h($bankInfo['account'] ?? '') ?></dd></div>
            </dl>
          </div>
          <?php endforeach; ?>

        </div>
      </div>

      <!-- Transfer details card -->
      <div class="gp-card" id="transferCard" style="display:none">
        <div class="gp-card-head">
          
          <div class="gp-card-title">Submit Your Transfer Information</div>
        </div>
        <div class="gp-card-body">
          
          <form method="POST" action="<?= URLROOT ?>/booking/submitManualPayment" enctype="multipart/form-data" id="paymentForm">
            <?= csrf_field() ?>
            <input type="hidden" name="booking_id" value="<?= (int)($booking['id'] ?? 0) ?>">
            <input type="hidden" name="bank_name" id="bankNameInput" value="">

            <div class="gp-transfer-form show">
              <div class="gp-field-row">
                <div class="gp-field gp-float-field">
                  <label for="account_name">Your Account Name <span class="req">*</span></label>
                  <input type="text" id="account_name" name="account_name" placeholder="e.g. Ko Kyaw Zin" required>
                </div>
                <div class="gp-field gp-float-field">
                  <label for="mobile_number">Your Mobile Number <span class="req">*</span></label>
                  <input type="text" id="mobile_number" name="mobile_number" placeholder="09XXXXXXXXX" required>
                </div>
              </div>
              <div class="gp-field-row">
                <div class="gp-field gp-float-field">
                  <label for="transaction_ref">Payment Reference <span class="req">*</span></label>
                  <input type="text" id="transaction_ref" name="transaction_ref" placeholder="e.g. TXN-12345678" required>
                </div>
                <div class="gp-field gp-float-field">
                  <label for="paid_amount">Amount Paid (MMK) <span class="req">*</span></label>
                  <input type="number" id="paid_amount" name="paid_amount" placeholder="<?= (int)$depositWithFee ?>" value="<?= (int)$depositWithFee ?>" min="1" step="1" required>
                </div>
              </div>
              <div class="gp-field gp-float-field">
                <label for="remark">Remark <span class="opt">(optional)</span></label>
                <input type="text" id="remark" name="remark" placeholder="Any notes about this transfer">
              </div>
              <div class="gp-field">
                <label>Upload Slip / Screenshot <span class="req">*</span></label>
                <label for="slip_image" class="gp-slip-label" id="slipLabel">
                  <div class="gp-slip-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M12 16V4"></path>
                      <path d="m7 9 5-5 5 5"></path>
                      <path d="M20 16v3a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-3"></path>
                    </svg>
                    <span>Upload</span>
                  </div>
                  <div class="gp-slip-text">
                    <strong id="slipFileName">Drag &amp; drop or click to upload your transfer slips</strong>
                    <small>Up to 5 files. JPG, PNG, WebP or PDF accepted</small>
                  </div>
                </label>
                <input class="gp-file-input" type="file" id="slip_image" name="slip_image[]" accept=".jpg,.jpeg,.png,.webp,.pdf" multiple required>
                
                
                <div class="gp-field-error" id="slipError" aria-live="polite"></div>
              </div>

              <button type="submit" class="gp-submit" id="submitBtn" disabled>
                Submit Payment Proof
              </button>
            </div>
          </form>
        </div>
      </div>

    </div>

    <aside class="gp-checkout-side">
  <!-- Summary card -->
  <div class="gp-card">
    <div class="gp-card-head">
      <div class="gp-card-label">Payment Overview</div>
      <div class="gp-card-title"><?= $h($bookingRef) ?></div>
    </div>
    <div class="gp-card-body">
      <div class="gp-overview">
        <div class="gp-overview-section is-selected-service">
          <div class="gp-overview-label">Selected Service</div>
          <div class="gp-overview-service"><?= $h($selectedServiceName) ?></div>
        </div>

        <div class="gp-overview-section">
          <div class="gp-overview-row"><span>Booking Total</span><strong><?= $money($total) ?></strong></div>
          <div class="gp-overview-row"><span>Platform Fee (<?= $platformFeePercent ?>%)</span><strong><?= $money($platformFee) ?></strong></div>
          <div class="gp-overview-divider"></div>
          <div class="gp-overview-total"><span>Total Amount</span><span><?= $money($total + $platformFee) ?></span></div>
        </div>

        <div class="gp-overview-transfer">
          <div class="gp-overview-label">Amount to Transfer</div>
          <div class="gp-overview-amount"><?= $money($depositWithFee) ?></div>
          <div class="gp-overview-due">Due Today<br>(<?= $depositPercent ?>% Deposit + Platform Fee)</div>
        </div>

        <div class="gp-overview-section">
          <div class="gp-overview-label">Balance Due</div>
          <div class="gp-overview-row"><span>Remaining Balance</span><strong><?= $money($balance) ?></strong></div>
        </div>

        <div class="gp-overview-note">
          The remaining balance of <?= $money($balance) ?> is payable before your event date.
          Today's payment already includes the one-time platform fee.
        </div>
      </div>
    </div>
  </div>
    </aside>
  </div>

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

function getSlipFiles() {
  return slipInput ? Array.from(slipInput.files || []) : [];
}

function describeSlipFiles(files) {
  if (!files.length) return 'Drag & drop or click to upload your transfer slips';
  if (files.length === 1) return files[0].name;
  return files.length + ' payment slips selected';
}

function getSlipError(files) {
  if (!files.length) return 'Please upload your payment slip or receipt.';
  if (files.length > 5) return 'Upload no more than 5 payment slips.';
  for (const file of files) {
    const extension = file.name.split('.').pop().toLowerCase();
    if (!allowedSlipTypes.includes(file.type) && !allowedSlipExtensions.includes(extension)) {
      return 'Use JPG, PNG, WebP, or PDF payment proofs only.';
    }
    if (file.size > maxSlipBytes) {
      return 'Each file must be under 10 MB.';
    }
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
  const files = getSlipFiles();
  const message = getSlipError(files);

  slipFileName.textContent = describeSlipFiles(files);
  slipLabel.classList.toggle('has-file', files.length > 0 && message === '');

  if (showMessage || files.length > 0 || message === '') {
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
<?php include APPROOT . '/views/partials/cookie-consent.php'; ?>
<?php require APPROOT . '/views/layouts/customerFooter.php'; ?>
</body>
</html>
