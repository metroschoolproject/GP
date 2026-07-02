<?php
$booking = $booking ?? [];
$items = $items ?? [];
$total = (float)($total ?? 0);
$deposit = (float)($deposit ?? 0);
$depositPercent = (int)($depositPercent ?? BOOKING_DEPOSIT_PERCENT);
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
<meta name="csrf-token" content="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
<link rel="icon" type="image/png" href="<?= URLROOT ?>/public/images/home/gp_logo.png">
<title>Payment — Golden Promise</title>
<?php $publicCssVersion = file_exists(APPROOT . '/../public/css/app.css') ? filemtime(APPROOT . '/../public/css/app.css') : time(); ?>
<link rel="stylesheet" href="<?= URLROOT ?>/public/css/app.css?v=<?= $publicCssVersion ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://js.stripe.com/v3/"></script>
<style>
:root {
  --bg:          #f2e4d4;
  --surface:     #faf6f1;
  --card:        #fcf8f5;
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
body { background: var(--bg); color: var(--text); font-family: var(--font-b); font-size: 14px; line-height: 1.6; -webkit-font-smoothing: antialiased; min-height: 100vh; display: flex; flex-direction: column; align-items: center; padding: 40px 20px; }
a { color: inherit; text-decoration: none; }

.gp-orb { position: fixed; border-radius: 50%; filter: blur(80px); opacity: 0.3; z-index: 0; pointer-events: none; }
.gp-orb-1 { width: 500px; height: 500px; background: radial-gradient(circle, rgba(107,68,89,0.10) 0%, transparent 70%); top: -150px; right: -80px; }
.gp-orb-2 { width: 400px; height: 400px; background: radial-gradient(circle, rgba(184,146,74,0.08) 0%, transparent 70%); bottom: -100px; left: -80px; }

.gp-checkout { position: relative; z-index: 1; width: 100%; max-width: 1120px; }
.gp-page-head { margin-bottom: 32px; text-align: left; }
.gp-page-eyebrow { font-size: 11px; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; color: var(--gold); margin-bottom: 8px; }
.gp-page-title { font-family: var(--font-d); font-size: clamp(32px, 4.5vw, 48px); font-weight: 600; color: var(--text); line-height: 0.92; letter-spacing: -0.02em; }
.gp-page-title em { font-style: italic; color: var(--plum-lt); }

.gp-checkout-layout { display: grid; grid-template-columns: minmax(0, 1fr) 380px; gap: 22px; align-items: start; }
.gp-checkout-main, .gp-checkout-side { min-width: 0; }
.gp-checkout-side { position: sticky; top: 28px; }

.gp-card { background: var(--card); border-radius: var(--r-lg); border: 1px solid rgba(184,146,74,0.38); overflow: hidden; box-shadow: 0 20px 60px rgba(26,17,24,0.08); margin-bottom: 16px; }
.gp-checkout-side .gp-card { min-height: 420px; display: flex; flex-direction: column; }
.gp-checkout-side .gp-card-body { flex: 1; }

.gp-card-head { padding: 20px 24px; border-bottom: 1px solid var(--rule); position: relative; background: transparent; }
.gp-card-label { font-size: 10px; font-weight: 600; letter-spacing: 0.12em; text-transform: uppercase; color: var(--muted); margin-bottom: 4px; }
.gp-card-title { font-family: var(--font-b); font-size: 17px; font-weight: 600; color: var(--plum); }
.gp-card-ref { font-size: 12px; color: var(--muted); margin-top: 2px; }

.gp-card-body { padding: 24px; display: flex; flex-direction: column; gap: 20px; }

.gp-summary-section { }
.gp-summary-title { font-size: 10px; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; color: var(--muted); margin-bottom: 12px; }
.gp-summary-items { display: flex; flex-direction: column; gap: 8px; }
.gp-summary-row { display: flex; justify-content: space-between; align-items: baseline; font-size: 13px; }
.gp-summary-row.total { font-weight: 600; color: var(--text); padding-top: 12px; border-top: 1px solid var(--rule); margin-top: 4px; }
.gp-summary-row.deposit { color: var(--plum); font-weight: 600; }
.gp-summary-row.balance { color: var(--muted); font-size: 12px; }
.gp-summary-divider { height: 1px; background: var(--rule); }
.gp-summary-highlight { padding: 18px 16px; border-radius: 12px; border: 1px solid rgba(22,101,52,0.18); background: #f0fdf4; }
.gp-summary-highlight .gp-summary-title { color: var(--green); margin-bottom: 8px; }
.gp-summary-amount { font-family: var(--font-b); font-size: 26px; font-weight: 600; line-height: 1.25; color: var(--text); }

.gp-divider { height: 1px; background: var(--rule); }

.gp-payment-section { }
.gp-payment-title { font-size: 10px; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; color: var(--muted); margin-bottom: 12px; }
#stripe-card-element {
  padding: 12px 14px;
  border: 1px solid var(--rule-strong);
  border-radius: var(--r-sm);
  background: var(--surface);
  transition: border-color 0.2s;
}
#stripe-card-element.StripeElement--focus { border-color: var(--plum); box-shadow: 0 0 0 3px rgba(107,68,89,0.1); }
#stripe-card-element.StripeElement--invalid { border-color: var(--danger); }
#card-errors { font-size: 12px; color: var(--danger); margin-top: 6px; min-height: 18px; }
#payment-overlay { display: none; position: absolute; inset: 0; background: rgba(252,248,245,0.7); z-index: 10; border-radius: var(--r-lg); place-items: center; }
#payment-overlay.show { display: grid; }

.gp-pay-btn {
  display: flex; align-items: center; justify-content: center; gap: 8px;
  width: 100%; height: 52px; border-radius: var(--r-md); border: none;
  background: var(--plum); color: #fffaf3;
  font-size: 14px; font-weight: 700;
  letter-spacing: 0.02em;
  box-shadow: 0 10px 28px rgba(107,68,89,0.28);
  transition: all 0.3s var(--ease-expo);
  cursor: pointer;
}
.gp-pay-btn:hover { background: var(--plum-dk); transform: translateY(-2px); box-shadow: 0 18px 40px rgba(107,68,89,0.32); }
.gp-pay-btn:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

.gp-btn-back { display: inline-flex; align-items: center; justify-content: center; gap: 6px; width: auto; min-width: 142px; height: 36px; padding: 0 16px; border-radius: 999px; border: 1px solid rgba(184,146,74,0.58); background: rgba(255,250,245,0.62); color: #7a5c35; font-size: 12px; font-weight: 700; transition: all 0.22s; margin-bottom: 18px; }
.gp-btn-back:hover { background: #fffaf5; border-color: var(--gold); color: var(--plum); transform: translateY(-1px); }

.gp-trust-footer { display: flex; flex-direction: column; gap: 8px; padding: 20px 24px; border-top: 1px solid var(--rule); }
.gp-trust-item { display: flex; align-items: center; gap: 8px; font-size: 11px; color: var(--muted); }
.gp-trust-icon { width: 14px; height: 14px; flex-shrink: 0; }

.gp-spinner { display: inline-block; width: 18px; height: 18px; border: 2px solid rgba(252,248,245,0.3); border-top-color: #fcf8f5; border-radius: 50%; animation: spin 0.6s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

.gp-toast { position: fixed; top: 20px; right: 20px; z-index: 999; padding: 14px 20px; border-radius: var(--r-md); box-shadow: 0 12px 40px rgba(0,0,0,0.12); font-size: 13px; font-weight: 500; max-width: 380px; opacity: 0; transform: translateY(-12px); transition: all 0.35s var(--ease-expo); pointer-events: none; }
.gp-toast.show { opacity: 1; transform: translateY(0); pointer-events: auto; }
.gp-toast.error { background: #fef2f2; border: 1px solid #fecaca; color: var(--danger); }
.gp-toast.success { background: #f0fdf4; border: 1px solid #bbf7d0; color: var(--green); }

@media (max-width: 600px) {
  body { padding: 20px 16px; }
  .gp-checkout-layout { grid-template-columns: 1fr; }
  .gp-checkout-side { position: static; order: -1; }
  .gp-card-body { padding: 16px; }
}
</style>
</head>
<body>

<div class="gp-orb gp-orb-1" aria-hidden="true"></div>
<div class="gp-orb gp-orb-2" aria-hidden="true"></div>

<div class="gp-checkout">

  <a class="gp-btn-back" href="<?= URLROOT ?>/booking/create">
    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
    Back to confirm
  </a>

  <div class="gp-page-head">
    <div class="gp-page-eyebrow">Secure Checkout</div>
    <h1 class="gp-page-title">Complete Your <em>Payment</em></h1>
  </div>

  <div class="gp-checkout-layout">
    <div class="gp-checkout-main">
      <div class="gp-card" id="payment-card">
        <div class="gp-card-head">
          <div class="gp-card-title">Credit / Debit Card</div>
          <div class="gp-card-ref">Powered by Stripe</div>
        </div>

        <div class="gp-card-body" style="position:relative;">

          <!-- Overlay during processing -->
          <div id="payment-overlay">
            <div style="text-align:center;color:var(--plum);">
              <div class="gp-spinner" style="border-color:rgba(107,68,89,0.2);border-top-color:var(--plum);width:32px;height:32px;margin:0 auto 12px;"></div>
              <div style="font-weight:600;font-size:14px;">Processing payment…</div>
              <div style="font-size:12px;color:var(--muted);margin-top:4px;">Please don't close this page</div>
            </div>
          </div>

          <!-- Payment -->
          <div class="gp-payment-section">
            <div class="gp-payment-title">
              <span>💳 Credit / Debit Card</span>
            </div>
            <form id="payment-form">
              <input type="hidden" name="booking_id" value="<?= (int)($booking['id'] ?? 0) ?>">
              <div id="stripe-card-element"></div>
              <div id="card-errors" role="alert"></div>
              <button class="gp-pay-btn" type="submit" id="pay-button" style="margin-top:16px;">
                Pay Deposit — <?= $money($deposit) ?>
              </button>
            </form>
          </div>

        </div>

        <div class="gp-trust-footer">
          <div class="gp-trust-item">
            <svg class="gp-trust-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            🔒 Powered by Stripe — your card is charged immediately
          </div>
          <div class="gp-trust-item">
            <svg class="gp-trust-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            The balance is not collected now
          </div>
        </div>
      </div>
    </div>

    <aside class="gp-checkout-side">
      <div class="gp-card">
        <div class="gp-card-head">
          <div class="gp-card-label">Payment Summary</div>
          <div class="gp-card-title">Booking <?= $h($bookingRef) ?></div>
          <div class="gp-card-ref">Deposit of <?= $depositPercent ?>% secures your date</div>
        </div>
        <div class="gp-card-body">
          <!-- Summary -->
          <div class="gp-summary-section">
            <div class="gp-summary-title">Your selection</div>
            <div class="gp-summary-items">
              <?php foreach ($items as $item):
                $linePrice = (float)($item['price'] ?? 0);
                $lineName  = $item['service_name'] ?? 'Service';
                $lineHall = trim((string)($item['venue_room_name'] ?? ''));
              ?>
              <div class="gp-summary-row">
                <span>
                  <?= $h($lineName) ?>
                  <?php if ($lineHall !== ''): ?>
                    <br><small style="color:var(--muted);font-size:11px;">Hall: <?= $h($lineHall) ?></small>
                  <?php endif; ?>
                </span>
                <span><?= $money($linePrice) ?></span>
              </div>
              <?php endforeach; ?>
              <div class="gp-summary-divider"></div>
              <div class="gp-summary-row">
                <span>Total</span>
                <span><?= $money($total) ?></span>
              </div>
              <div class="gp-summary-highlight">
                <div class="gp-summary-title">Today's Deposit (<?= $depositPercent ?>%)</div>
                <div class="gp-summary-amount"><?= $money($deposit) ?></div>
              </div>
              <div class="gp-summary-row balance">
                <span>Balance due</span>
                <span><?= $money($balance) ?></span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </aside>
  </div>
</div>

<div class="gp-toast" id="gp-toast" role="alert"></div>

<script>
(function () {
  const stripe = Stripe('<?= $stripePublishableKey ?>');
  const elements = stripe.elements();
  const cardElement = elements.create('card', {
    style: {
      base: {
        fontSize: '14px',
        fontFamily: '"Poppins", system-ui, sans-serif',
        color: '#1a1118',
        '::placeholder': { color: '#a08878' },
      },
      invalid: { color: '#b94b4b' },
    },
  });
  cardElement.mount('#stripe-card-element');

  const form = document.getElementById('payment-form');
  const payBtn = document.getElementById('pay-button');
  const overlay = document.getElementById('payment-overlay');
  const cardErrors = document.getElementById('card-errors');
  const toast = document.getElementById('gp-toast');

  function showToast(msg, type) {
    toast.textContent = msg;
    toast.className = 'gp-toast ' + type + ' show';
    setTimeout(() => toast.classList.remove('show'), 5000);
  }

  function setProcessing(processing) {
    payBtn.disabled = processing;
    overlay.classList.toggle('show', processing);
    if (processing) {
      payBtn.innerHTML = '<span class="gp-spinner"></span> Processing…';
    } else {
      payBtn.innerHTML = 'Pay Deposit — <?= $money($deposit) ?>';
    }
  }

  form.addEventListener('submit', async function (e) {
    e.preventDefault();
    setProcessing(true);
    cardErrors.textContent = '';

    const bookingId = document.querySelector('input[name="booking_id"]').value;

    // Create payment method and confirm
    const { error, paymentMethod } = await stripe.createPaymentMethod({
      type: 'card',
      card: cardElement,
    });

    if (error) {
      cardErrors.textContent = error.message;
      setProcessing(false);
      return;
    }

    // Send to our server to create and confirm PaymentIntent
    try {
      const formData = new FormData();
      formData.append('booking_id', bookingId);
      formData.append('payment_method_id', paymentMethod.id);

      const resp = await fetch('<?= URLROOT ?>/booking/processPayment', {
        method: 'POST',
        body: formData,
      });

      const data = await resp.json();

      if (data.error) {
        cardErrors.textContent = data.error;
        setProcessing(false);
        return;
      }

      if (data.requires_action) {
        // 3D Secure
        const { error: confirmError } = await stripe.confirmCardPayment(
          data.payment_intent_client_secret
        );

        if (confirmError) {
          cardErrors.textContent = confirmError.message;
          setProcessing(false);
          return;
        }

        // After 3DS, confirm with our server
        const confirmResp = await fetch('<?= URLROOT ?>/booking/confirmPayment', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: 'payment_intent_id=' + data.payment_intent_client_secret.split('_secret_')[0] + '&booking_id=' + bookingId,
        });
        const confirmData = await confirmResp.json();

        if (confirmData.success && confirmData.redirect) {
          window.location.href = confirmData.redirect;
        } else {
          cardErrors.textContent = confirmData.error || 'Payment could not be confirmed.';
          setProcessing(false);
        }
        return;
      }

      if (data.success && data.redirect) {
        window.location.href = data.redirect;
      } else {
        cardErrors.textContent = 'Unexpected response from server.';
        setProcessing(false);
      }
    } catch (err) {
      showToast('Connection error. Your card has not been charged. Please try again.', 'error');
      setProcessing(false);
    }
  });
})();
</script>
<?php require APPROOT . '/views/layouts/customerFooter.php'; ?>
</body>
</html>
