<?php
$booking = $booking ?? [];
$items = $items ?? [];
$total = (float)($total ?? 0);
$deposit = (float)($deposit ?? 0);
$depositPercent = (int)($depositPercent ?? 10);
$balance = (float)($balance ?? 0);
$bookingRef = $bookingRef ?? '';

$money = fn($v) => number_format((float)$v, 0) . ' MMK';
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Choose Payment Method — Golden Promise</title>
<?php $publicCssVersion = file_exists(APPROOT . '/../public/css/app.css') ? filemtime(APPROOT . '/../public/css/app.css') : time(); ?>
<link rel="stylesheet" href="<?= URLROOT ?>/public/css/app.css?v=<?= $publicCssVersion ?>">
<style>
:root {
  --bg:          #f2e4d4;
  --surface:     #faf6f1;
  --card:        #ffffff;
  --rule:        rgba(178,143,110,0.22);
  --rule-strong: rgba(178,143,110,0.45);
  --plum:        #6b4459;
  --plum-dk:     #4e3141;
  --plum-lt:     #9b7289;
  --rose:        #c27a8e;
  --gold:        #b8924a;
  --muted:       #a08878;
  --text:        #1a1118;
  --text2:       #5c4a54;
  --danger:      #b94b4b;
  --green:       #166534;
  --r-sm: 8px;
  --r-md: 14px;
  --r-lg: 20px;
  --font-d: 'Playfair Display', Georgia, serif;
  --font-b: 'Poppins', system-ui, sans-serif;
  --ease-expo: cubic-bezier(0.19, 1, 0.22, 1);
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body { background: var(--bg); color: var(--text); font-family: var(--font-b); font-size: 14px; line-height: 1.6; -webkit-font-smoothing: antialiased; min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 40px 20px; }
a { color: inherit; text-decoration: none; }

.gp-orb { position: fixed; border-radius: 50%; filter: blur(80px); opacity: 0.3; z-index: 0; pointer-events: none; }
.gp-orb-1 { width: 500px; height: 500px; background: radial-gradient(circle, rgba(107,68,89,0.10) 0%, transparent 70%); top: -150px; right: -80px; }
.gp-orb-2 { width: 400px; height: 400px; background: radial-gradient(circle, rgba(184,146,74,0.08) 0%, transparent 70%); bottom: -100px; left: -80px; }

.gp-checkout { position: relative; z-index: 1; width: 100%; max-width: 540px; }
.gp-page-head { margin-bottom: 32px; text-align: center; }
.gp-page-eyebrow { font-size: 11px; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; color: var(--gold); margin-bottom: 8px; }
.gp-page-title { font-family: var(--font-d); font-size: clamp(32px, 4.5vw, 48px); font-weight: 600; color: var(--text); line-height: 0.92; letter-spacing: -0.02em; }
.gp-page-title em { font-style: italic; color: var(--plum-lt); }

.gp-card { background: var(--card); border-radius: var(--r-lg); border: 1px solid var(--rule); overflow: hidden; box-shadow: 0 20px 60px rgba(26,17,24,0.08); }
.gp-card-head { padding: 24px; border-bottom: 1px solid var(--rule); position: relative; }
.gp-card-head::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, var(--plum) 0%, var(--rose) 50%, var(--gold) 100%); }
.gp-card-label { font-size: 10px; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; color: var(--muted); margin-bottom: 4px; }
.gp-card-title { font-family: var(--font-d); font-size: 18px; font-weight: 600; color: var(--text); }

.gp-card-body { padding: 24px; display: flex; flex-direction: column; gap: 20px; }

.gp-summary-section { }
.gp-summary-title { font-size: 10px; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; color: var(--muted); margin-bottom: 12px; }
.gp-summary-items { display: flex; flex-direction: column; gap: 8px; }
.gp-summary-row { display: flex; justify-content: space-between; align-items: baseline; font-size: 13px; }
.gp-summary-row.total { font-weight: 600; color: var(--text); padding-top: 12px; border-top: 1px solid var(--rule); margin-top: 4px; }
.gp-summary-row.deposit { color: var(--plum); font-weight: 600; }
.gp-summary-row.balance { color: var(--muted); font-size: 12px; }
.gp-summary-divider { height: 1px; background: var(--rule); }

.gp-divider { height: 1px; background: var(--rule); }

.gp-methods-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px; }
.gp-method-btn {
  display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px;
  padding: 20px 16px; border-radius: var(--r-md); border: 2px solid var(--rule);
  background: transparent; cursor: pointer;
  transition: all 0.25s;
  font-size: 13px; font-weight: 600; color: var(--text);
}
.gp-method-btn:hover { border-color: var(--plum); background: rgba(107,68,89,0.04); }
.gp-method-btn.active { border-color: var(--plum); background: rgba(107,68,89,0.08); }
.gp-method-icon { font-size: 28px; }

.gp-method-details { display: none; margin-top: 12px; padding: 16px; border-radius: var(--r-md); background: var(--surface); }
.gp-method-details.show { display: block; }
.gp-bank-account { font-size: 12px; line-height: 1.8; color: var(--text2); }
.gp-bank-account strong { display: block; margin-bottom: 4px; }
.gp-field { margin-bottom: 12px; }
.gp-field label { display: block; font-size: 12px; font-weight: 600; margin-bottom: 6px; color: var(--muted); }
.gp-field input { width: 100%; padding: 10px 12px; border: 1px solid var(--rule-strong); border-radius: var(--r-sm); font-size: 13px; font-family: var(--font-b); }
.gp-field input:focus { outline: none; border-color: var(--plum); box-shadow: 0 0 0 3px rgba(107,68,89,0.1); }

.gp-pay-btn {
  display: flex; align-items: center; justify-content: center; gap: 8px;
  width: 100%; height: 52px; border-radius: var(--r-md); border: none;
  background: var(--plum); color: #fffaf3;
  font-size: 14px; font-weight: 700;
  letter-spacing: 0.02em;
  box-shadow: 0 10px 28px rgba(107,68,89,0.28);
  transition: all 0.3s var(--ease-expo);
  cursor: pointer;
  margin-top: 12px;
}
.gp-pay-btn:hover { background: var(--plum-dk); transform: translateY(-2px); box-shadow: 0 18px 40px rgba(107,68,89,0.32); }
.gp-pay-btn:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

.gp-btn-back {
  display: flex; align-items: center; justify-content: center; gap: 6px;
  width: 100%; height: 42px; border-radius: var(--r-md);
  border: 1px solid var(--rule-strong);
  background: transparent; color: var(--text2);
  font-size: 13px; font-weight: 600;
  transition: all 0.22s;
  margin-top: 8px;
}
.gp-btn-back:hover { border-color: var(--plum); color: var(--plum); }

.gp-spinner { display: inline-block; width: 18px; height: 18px; border: 2px solid rgba(255,255,255,0.3); border-top-color: #fff; border-radius: 50%; animation: spin 0.6s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

.gp-toast { position: fixed; top: 20px; right: 20px; z-index: 999; padding: 14px 20px; border-radius: var(--r-md); box-shadow: 0 12px 40px rgba(0,0,0,0.12); font-size: 13px; font-weight: 500; max-width: 380px; opacity: 0; transform: translateY(-12px); transition: all 0.35s var(--ease-expo); pointer-events: none; }
.gp-toast.show { opacity: 1; transform: translateY(0); pointer-events: auto; }
.gp-toast.error { background: #fef2f2; border: 1px solid #fecaca; color: var(--danger); }
.gp-toast.success { background: #f0fdf4; border: 1px solid #bbf7d0; color: var(--green); }

.gp-alert { padding: 12px 14px; border-radius: var(--r-sm); background: #fef3c7; border: 1px solid #fcd34d; color: #92400e; font-size: 12px; line-height: 1.5; margin-bottom: 12px; }

@media (max-width: 600px) {
  body { padding: 20px 16px; }
  .gp-card-body { padding: 16px; }
  .gp-methods-grid { grid-template-columns: 1fr; }
}
</style>
</head>
<body>

<div class="gp-orb gp-orb-1" aria-hidden="true"></div>
<div class="gp-orb gp-orb-2" aria-hidden="true"></div>

<div class="gp-checkout">

  <div class="gp-page-head">
    <div class="gp-page-eyebrow">Secure Checkout</div>
    <h1 class="gp-page-title">Choose Payment <em>Method</em></h1>
  </div>

  <div class="gp-card">
    <div class="gp-card-head">
      <div class="gp-card-label">Payment Summary</div>
      <div class="gp-card-title">Booking <?= $h($bookingRef) ?></div>
      <p style="font-size: 13px; color: var(--muted); margin-top: 8px;">10% deposit secures your date. Balance due 3 days before event.</p>
    </div>

    <div class="gp-card-body">

      <!-- Summary -->
      <div class="gp-summary-section">
        <div class="gp-summary-title">Your selection</div>
        <div class="gp-summary-items">
          <?php foreach ($items as $item):
            $linePrice = (float)($item['price'] ?? 0);
            $lineName  = $item['service_name'] ?? 'Service';
          ?>
          <div class="gp-summary-row">
            <span><?= $h($lineName) ?></span>
            <span><?= $money($linePrice) ?></span>
          </div>
          <?php endforeach; ?>
          <div class="gp-summary-divider"></div>
          <div class="gp-summary-row">
            <span>Total</span>
            <span><?= $money($total) ?></span>
          </div>
          <div class="gp-summary-row deposit">
            <span>Deposit to pay (<?= $depositPercent ?>%)</span>
            <span><?= $money($deposit) ?></span>
          </div>
          <div class="gp-summary-row balance">
            <span>Balance after booking confirmed</span>
            <span><?= $money($balance) ?></span>
          </div>
        </div>
      </div>

      <div class="gp-divider"></div>

      <!-- Method Selection -->
      <div>
        <div class="gp-summary-title" style="margin-bottom: 16px;">Select payment method</div>

        <div class="gp-methods-grid">
          <button type="button" class="gp-method-btn" data-method="kbz-pay">
            <div class="gp-method-icon">📱</div>
            <div>KBZ Pay</div>
            <div style="font-size: 10px; color: var(--muted);">Mobile Wallet</div>
          </button>

          <button type="button" class="gp-method-btn" data-method="aya-bank">
            <div class="gp-method-icon">🏦</div>
            <div>AYA Bank</div>
            <div style="font-size: 10px; color: var(--muted);">Bank Transfer</div>
          </button>

          <button type="button" class="gp-method-btn" data-method="mm-qr">
            <div class="gp-method-icon">📲</div>
            <div>MM QR</div>
            <div style="font-size: 10px; color: var(--muted);">Instant QR Pay</div>
          </button>

          <button type="button" class="gp-method-btn" data-method="visa-card">
            <div class="gp-method-icon">💳</div>
            <div>Visa/Card</div>
            <div style="font-size: 10px; color: var(--muted);">Credit/Debit</div>
          </button>
        </div>

        <!-- Method Details -->
        <form id="payment-form">
          <input type="hidden" name="booking_id" value="<?= (int)($booking['id'] ?? 0) ?>">
          <input type="hidden" name="payment_method" id="payment_method" value="">

          <!-- KBZ Pay Details -->
          <div id="kbz-pay-details" class="gp-method-details">
            <div class="gp-alert">
              ✓ KBZ Pay account: 09XXXXXXXXX<br>
              Account holder: Golden Promise Co.
            </div>
            <div class="gp-alert" style="background: #dbeafe; border-color: #93c5fd; color: #1e40af;">
              Steps: Open KBZ Pay → Scan QR code below → Enter amount <?= $money($deposit) ?> → Confirm
            </div>
            <div style="text-align: center; padding: 20px; background: var(--surface); border-radius: var(--r-md); margin-bottom: 12px;">
              <div style="font-size: 12px; color: var(--muted); margin-bottom: 8px;">Scan this QR code:</div>
              <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=https://kbzpay.goldenpromise.com" alt="KBZ Pay QR" style="width: 160px; height: 160px;">
            </div>
            <div class="gp-field">
              <label>Reference Number (from KBZ Pay)</label>
              <input type="text" name="reference" required placeholder="e.g., KBZ20260616123456">
            </div>
            <div class="gp-field">
              <label>Upload Payment Slip</label>
              <input type="file" name="slip_image" accept="image/*,application/pdf" required>
            </div>
            <button type="submit" class="gp-pay-btn">Submit Payment Slip</button>
          </div>

          <!-- AYA Bank Details -->
          <div id="aya-bank-details" class="gp-method-details">
            <div class="gp-alert">
              <strong>AYA Bank Account Details:</strong>
              Account Number: 1234567890<br>
              Account Name: Golden Promise Co.
            </div>
            <div class="gp-bank-account">
              <strong>What to do:</strong>
              1. Transfer <?= $money($deposit) ?> to the account above<br>
              2. Use reference: Booking <?= $h($bookingRef) ?> as description<br>
              3. Upload the transaction receipt below
            </div>
            <div class="gp-field" style="margin-top: 12px;">
              <label>Reference Number (from AYA Bank)</label>
              <input type="text" name="reference" required placeholder="e.g., AYA-20260616-123456">
            </div>
            <div class="gp-field">
              <label>Upload Payment Receipt</label>
              <input type="file" name="slip_image" accept="image/*,application/pdf" required>
            </div>
            <button type="submit" class="gp-pay-btn">Submit Payment Proof</button>
          </div>

          <!-- MM QR Details -->
          <div id="mm-qr-details" class="gp-method-details">
            <div class="gp-alert" style="background: #dbeafe; border-color: #93c5fd; color: #1e40af;">
              Your payment will be processed instantly. Please do not close this page during processing.
            </div>
            <div style="text-align: center; padding: 20px; background: var(--surface); border-radius: var(--r-md); margin-bottom: 12px;">
              <div style="font-size: 12px; color: var(--muted); margin-bottom: 8px;">Scan QR Code to Pay:</div>
              <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=https://mmqr.goldenpromise.com/pay" alt="MM QR Code" style="width: 160px; height: 160px;">
            </div>
            <button type="button" class="gp-pay-btn" onclick="processMMQRPayment(<?= (int)($booking['id'] ?? 0) ?>, <?= $deposit ?>)">
              Open MM QR Paymet
            </button>
          </div>

          <!-- Visa/Card Details -->
          <div id="visa-card-details" class="gp-method-details">
            <div class="gp-alert" style="background: #dbeafe; border-color: #93c5fd; color: #1e40af;">
              Your card will be charged immediately for <?= $money($deposit) ?>
            </div>
            <div class="gp-field">
              <label>Card Number</label>
              <input type="text" name="card_number" placeholder="1234 5678 9012 3456" required>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
              <div class="gp-field">
                <label>Expiry Date</label>
                <input type="text" name="card_expiry" placeholder="MM/YY" required>
              </div>
              <div class="gp-field">
                <label>CVV</label>
                <input type="text" name="card_cvv" placeholder="123" required>
              </div>
            </div>
            <button type="submit" class="gp-pay-btn">Pay <?= $money($deposit) ?> Now</button>
          </div>
        </form>

        <a class="gp-btn-back" href="<?= URLROOT ?>/booking/detail/<?= (int)($booking['id'] ?? 0) ?>">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
          Back to booking
        </a>
      </div>

    </div>
  </div>
</div>

<div class="gp-toast" id="gp-toast" role="alert"></div>

<script>
const methodBtns = document.querySelectorAll('[data-method]');
const paymentForm = document.getElementById('payment-form');
const paymentMethodInput = document.getElementById('payment_method');
const toast = document.getElementById('gp-toast');

function showToast(msg, type = 'success') {
  toast.textContent = msg;
  toast.className = 'gp-toast ' + type + ' show';
  setTimeout(() => toast.classList.remove('show'), 5000);
}

function selectMethod(method) {
  // Hide all details
  document.querySelectorAll('.gp-method-details').forEach(el => el.classList.remove('show'));
  methodBtns.forEach(btn => btn.classList.remove('active'));

  // Show selected
  const methodId = method.replace('-', '-') + '-details';
  const detailEl = document.getElementById(methodId);
  if (detailEl) detailEl.classList.add('show');

  // Mark button active
  document.querySelector(`[data-method="${method}"]`)?.classList.add('active');

  // Set hidden input
  paymentMethodInput.value = method;

  // Clear and update form validation
  document.querySelectorAll('input[name="reference"], input[name="slip_image"]').forEach(el => el.value = '');
}

methodBtns.forEach(btn => {
  btn.addEventListener('click', () => selectMethod(btn.dataset.method));
});

paymentForm.addEventListener('submit', async (e) => {
  e.preventDefault();

  const method = paymentMethodInput.value;
  if (!method) {
    showToast('Please select a payment method', 'error');
    return;
  }

  const bookingId = document.querySelector('input[name="booking_id"]').value;
  const formData = new FormData(paymentForm);

  const endpoint = method === 'kbz-pay' || method === 'aya-bank'
    ? '<?= URLROOT ?>/booking/submitPaymentSlip'
    : '<?= URLROOT ?>/booking/confirmInstantPayment';

  try {
    const resp = await fetch(endpoint, { method: 'POST', body: formData });
    const data = await resp.json();

    if (!resp.ok || data.error) {
      showToast(data.error || 'Payment failed', 'error');
      return;
    }

    showToast(data.message, 'success');
    setTimeout(() => {
      window.location.href = '<?= URLROOT ?>/booking/detail/' + bookingId;
    }, 1500);
  } catch (err) {
    showToast('Connection error. Please try again.', 'error');
  }
});

function processMMQRPayment(bookingId, amount) {
  // For now, mock the MM QR payment
  // In production, this would redirect to MM QR gateway
  showToast('Opening MM QR payment gateway...', 'success');
  setTimeout(() => {
    // Mock success - in real implementation, wait for callback from MM QR
    handleInstantPaymentSuccess(bookingId, 'MM QR', 'TEST_TXN_' + Date.now(), amount);
  }, 2000);
}

function handleInstantPaymentSuccess(bookingId, method, transactionId, amount) {
  const formData = new FormData();
  formData.append('booking_id', bookingId);
  formData.append('method', method);
  formData.append('transaction_id', transactionId);
  formData.append('amount', amount);

  fetch('<?= URLROOT ?>/booking/confirmInstantPayment', {
    method: 'POST',
    body: formData
  }).then(r => r.json()).then(data => {
    if (data.success) {
      showToast(data.message, 'success');
      setTimeout(() => {
        window.location.href = '<?= URLROOT ?>/booking/detail/' + bookingId;
      }, 1500);
    } else {
      showToast(data.error || 'Payment confirmation failed', 'error');
    }
  }).catch(err => {
    showToast('Connection error. Please try again.', 'error');
  });
}
</script>
</body>
</html>
