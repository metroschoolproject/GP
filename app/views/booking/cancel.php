<?php
$booking = $booking ?? [];
$items = $items ?? [];
$bookingRef = $bookingRef ?? '';
$depositPercent = (int)($depositPercent ?? BOOKING_DEPOSIT_PERCENT);
$refundEstimate = $refundEstimate ?? null;
$platformFeePercent = (float)($platformFeePercent ?? get_platform_fee_percent());

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
<link rel="icon" type="image/png" href="<?= URLROOT ?>/public/images/home/gp_logo.png">
<title>Request Cancellation - Golden Promise</title>
<?php $publicCssVersion = file_exists(APPROOT . '/../public/css/app.css') ? filemtime(APPROOT . '/../public/css/app.css') : time(); ?>
<link rel="stylesheet" href="<?= URLROOT ?>/public/css/app.css?v=<?= $publicCssVersion ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--bg:#f2e4d4;--card:#fcf8f5;--surface:#faf6f1;--rule:rgba(178,143,110,.28);--rule-strong:rgba(178,143,110,.48);--plum:#6b4459;--plum-dk:#4e3141;--muted:#8f796d;--text:#1a1118;--danger:#b94b4b;--gold:#b8924a;--font-d:'Playfair Display',Georgia,serif;--font-b:'Poppins',system-ui,sans-serif;--pad-x:clamp(18px,5vw,70px)}
*{box-sizing:border-box}body{margin:0;background:var(--bg);color:var(--text);font-family:var(--font-b);min-height:100vh}.gp-header{height:68px;padding:0 var(--pad-x);display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid var(--rule);background:rgba(242,228,212,.9);backdrop-filter:blur(18px);position:sticky;top:0;z-index:5}.gp-brand{display:flex;align-items:center;gap:12px;color:var(--text);text-decoration:none;font-weight:800}.gp-brand-mark{width:38px;height:38px;border-radius:50%;display:grid;place-items:center;background:var(--plum);color:#fffaf3;font-size:13px}.gp-nav{display:flex;gap:8px}.gp-nav a{font-size:13px;font-weight:600;color:#5c4a54;text-decoration:none;padding:8px 12px;border-radius:999px}.gp-nav a:hover{background:rgba(107,68,89,.08);color:var(--plum)}.gp-page{max-width:960px;margin:0 auto;padding:52px var(--pad-x) 80px}.gp-back{margin-bottom:22px}.gp-back a{display:inline-flex;align-items:center;gap:6px;color:var(--plum);font-size:13px;font-weight:700;text-decoration:none}.gp-title{font-family:var(--font-d);font-size:clamp(34px,5vw,54px);line-height:.95;margin:0 0 10px}.gp-title em{color:#9b7289;font-style:italic}.gp-sub{margin:0 0 30px;color:var(--muted);font-size:14px;max-width:640px}.gp-layout{display:grid;grid-template-columns:1fr 340px;gap:24px;align-items:start}.gp-card{background:var(--card);border:1px solid var(--rule);border-radius:20px;overflow:hidden;box-shadow:0 18px 50px rgba(26,17,24,.07)}.gp-card-head{padding:22px 24px;border-bottom:1px solid var(--rule);background:linear-gradient(180deg,#fcf8f5,#fff9f3)}.gp-card-kicker{font-size:10px;letter-spacing:.12em;text-transform:uppercase;color:var(--gold);font-weight:800}.gp-card-title{font-family:var(--font-d);font-size:24px;font-weight:600;margin-top:4px}.gp-card-body{padding:22px 24px}.gp-warning{border:1px solid #fecaca;background:#fff7f7;color:#8f2f2f;border-radius:14px;padding:14px 16px;font-size:13px;line-height:1.55;margin-bottom:18px}.gp-field{display:flex;flex-direction:column;gap:7px}.gp-label{font-size:11px;letter-spacing:.1em;text-transform:uppercase;color:var(--muted);font-weight:800}.gp-textarea{min-height:150px;resize:vertical;border:1px solid var(--rule-strong);border-radius:12px;background:var(--surface);padding:13px 14px;font:inherit;color:var(--text);outline:none}.gp-textarea:focus{border-color:var(--plum);box-shadow:0 0 0 3px rgba(107,68,89,.1)}.gp-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:18px}.gp-btn{height:44px;border-radius:12px;border:1px solid var(--rule-strong);padding:0 18px;font-weight:800;font-size:13px;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;gap:8px;cursor:pointer}.gp-btn.primary{border-color:var(--danger);background:var(--danger);color:#fcf8f5}.gp-btn.primary:hover{filter:brightness(.94)}.gp-btn.secondary{background:transparent;color:var(--text)}.gp-btn.secondary:hover{border-color:var(--plum);color:var(--plum)}.gp-btn:disabled{opacity:.6;cursor:not-allowed}.gp-summary-row{display:flex;justify-content:space-between;gap:14px;padding:11px 0;border-bottom:1px solid var(--rule);font-size:13px}.gp-summary-row:last-child{border-bottom:none}.gp-summary-row span:first-child{color:var(--muted)}.gp-summary-row strong{text-align:right}.gp-items{display:flex;flex-direction:column;gap:10px;margin-top:16px}.gp-item{border:1px solid var(--rule);border-radius:14px;padding:12px;background:#fffaf7}.gp-item-name{font-weight:700;font-size:13px}.gp-item-meta{color:var(--muted);font-size:12px;margin-top:3px}.gp-toast{position:fixed;right:20px;top:86px;background:#fef2f2;border:1px solid #fecaca;color:var(--danger);border-radius:12px;padding:13px 16px;font-size:13px;box-shadow:0 14px 40px rgba(0,0,0,.12);opacity:0;transform:translateY(-8px);transition:.25s}.gp-toast.show{opacity:1;transform:translateY(0)}.gp-profile-dropdown{position:relative}.gp-profile-btn{display:flex;align-items:center;gap:8px;padding:4px 12px 4px 4px;border-radius:999px;border:1px solid var(--rule-strong);background:var(--card);cursor:pointer;transition:all .2s;color:var(--plum);font-family:var(--font-b);font-size:13px;font-weight:600}.gp-profile-btn:hover{border-color:var(--plum);background:rgba(107,68,89,.06)}.gp-profile-avatar{display:grid;place-items:center;width:32px;height:32px;border-radius:50%;background:var(--plum);color:#fffaf3;font-size:12px;font-weight:800;letter-spacing:.5px}.gp-profile-name{white-space:nowrap;max-width:100px;overflow:hidden;text-overflow:ellipsis}.gp-profile-chevron{opacity:.6;transition:transform .2s}.gp-profile-btn[aria-expanded="true"] .gp-profile-chevron{transform:rotate(180deg)}.gp-profile-menu{position:absolute;top:calc(100% + 8px);right:0;min-width:180px;padding:6px;border-radius:12px;border:1px solid var(--rule);background:var(--card);box-shadow:0 12px 35px rgba(15,23,42,.1);opacity:0;visibility:hidden;transform:translateY(-4px);transition:all .15s ease}.gp-profile-btn[aria-expanded="true"]+.gp-profile-menu,.gp-profile-menu.show{opacity:1;visibility:visible;transform:translateY(0)}.gp-profile-menu-item{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:8px;font-size:13px;font-weight:600;color:var(--text);transition:all .15s}.gp-profile-menu-item:hover{background:rgba(107,68,89,.06)}.gp-profile-menu-item--danger{color:var(--danger)}.gp-profile-menu-item--danger:hover{background:rgba(185,75,75,.08)}@media(max-width:820px){.gp-layout{grid-template-columns:1fr}.gp-nav{display:none}}@media(max-width:520px){:root{--pad-x:16px}.gp-actions{flex-direction:column}.gp-btn{width:100%}}
</style>
</head>
<body>
<?php $gpNavActive = 'bookings'; require APPROOT . '/views/layouts/customerHomeNav.php'; ?>

<main class="gp-page">
  <div class="gp-back">
    <a href="<?= URLROOT ?>/booking/detail/<?= (int)($booking['id'] ?? 0) ?>">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
      Back to booking
    </a>
  </div>

  <h1 class="gp-title">Request <em>Cancellation</em></h1>
  <p class="gp-sub">Send your cancellation reason to the Golden Promise team. We will review supplier status, payment records, and any deposit/refund handling manually.</p>

  <div class="gp-layout">
    <section class="gp-card">
      <div class="gp-card-head">
        <div class="gp-card-kicker">Booking <?= $h($bookingRef) ?></div>
        <div class="gp-card-title">Cancellation details</div>
      </div>
      <div class="gp-card-body">
        <div class="gp-warning">
          Cancellation is not final until the admin team reviews it. If a <?= $depositPercent ?>% deposit has been paid, refund handling depends on the event date, supplier work already started, and the cancellation policy.
        </div>

        <?php if ($refundEstimate && (float)($booking['paid_amount'] ?? 0) > 0): ?>
        <div style="border:1px solid #bbf7d0;background:#f0fdf4;border-radius:14px;padding:14px 16px;font-size:13px;color:#166534;margin-bottom:18px;line-height:1.55">
          <strong style="display:flex;align-items:center;gap:6px;margin-bottom:6px">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"/><path d="m9 12 2 2 4-4"/></svg>
            Estimated Refund
          </strong>
          <div style="display:flex;flex-wrap:wrap;gap:14px;align-items:baseline">
            <span style="font-size:20px;font-weight:800"><?= number_format($refundEstimate[0], 0) ?> MMK</span>
            <span style="opacity:.75;font-size:12px"><?= htmlspecialchars($refundEstimate[1], ENT_QUOTES, 'UTF-8') ?></span>
          </div>
          <?php
          $estTotal = (float)($booking['total_amount'] ?? 0);
          $estFee = round($estTotal * ($platformFeePercent / 100), 2);
          $estDeposit = (float)($booking['paid_amount'] ?? 0) - $estFee;
          ?>
          <?php if ($estFee > 0): ?>
          <div style="margin-top:8px;padding-top:8px;border-top:1px solid #bbf7d0;font-size:11px;color:#15803d;opacity:.85">
            Your payment of <?= $money($booking['paid_amount'] ?? 0) ?> includes a <?= $money($estFee) ?> platform service fee (<?= rtrim(rtrim(number_format($platformFeePercent, 2), '0'), '.') ?>%). The refund amount is calculated on the full payment including this fee.
          </div>
          <?php endif; ?>
          <?php if ($refundEstimate[0] <= 0): ?>
            <p style="margin-top:6px;font-size:12px;color:#92400e;background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:8px 10px">
              ⚠️ Based on the cancellation policy, no refund is applicable for cancellations less than 2 days before the event.
            </p>
          <?php endif; ?>
        </div>
        <?php endif; ?>

        <form id="cancel-form" method="POST" action="<?= URLROOT ?>/booking/submitCancellation">
          <?= csrf_field() ?>
          <input type="hidden" name="booking_id" value="<?= (int)($booking['id'] ?? 0) ?>">
          <div class="gp-field">
            <label class="gp-label" for="reason">Reason for cancellation</label>
            <textarea class="gp-textarea" id="reason" name="reason" required minlength="10" placeholder="Tell us why you need to cancel this booking."></textarea>
          </div>
          <div class="gp-actions">
            <button class="gp-btn primary" id="submit-btn" type="submit">Submit request</button>
            <a class="gp-btn secondary" href="<?= URLROOT ?>/booking/detail/<?= (int)($booking['id'] ?? 0) ?>">Keep booking</a>
          </div>
        </form>
      </div>
    </section>

    <aside class="gp-card">
      <div class="gp-card-head">
        <div class="gp-card-kicker">Summary</div>
        <div class="gp-card-title">Booking total</div>
      </div>
      <div class="gp-card-body">
        <div class="gp-summary-row"><span>Total</span><strong><?= $money($booking['total_amount'] ?? 0) ?></strong></div>
        <div class="gp-summary-row"><span>Paid</span><strong><?= $money($booking['paid_amount'] ?? 0) ?></strong></div>
        <div class="gp-summary-row"><span>Status</span><strong><?= $h(ucwords(str_replace('_', ' ', $booking['status'] ?? ''))) ?></strong></div>

        <div class="gp-items">
          <?php foreach ($items as $item): ?>
            <div class="gp-item">
              <div class="gp-item-name"><?= $h($item['service_name'] ?? 'Service') ?></div>
              <div class="gp-item-meta"><?= $h($item['supplier_name'] ?? 'Supplier') ?> · <?= $money($item['price'] ?? 0) ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </aside>
  </div>
</main>

<div class="gp-toast" id="toast" role="alert"></div>

<script>
(function () {
  const form = document.getElementById('cancel-form');
  const submitBtn = document.getElementById('submit-btn');
  const toast = document.getElementById('toast');

  function showToast(message) {
    toast.textContent = message;
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 4500);
  }

  form.addEventListener('submit', async function (event) {
    event.preventDefault();
    submitBtn.disabled = true;
    submitBtn.textContent = 'Submitting...';

    try {
      const response = await fetch(form.action, { method: 'POST', body: new FormData(form) });
      const data = await response.json();

      if (data.success) {
        window.location.href = '<?= URLROOT ?>/booking/detail/<?= (int)($booking['id'] ?? 0) ?>';
        return;
      }

      showToast(data.error || 'Could not submit cancellation request.');
    } catch (error) {
      showToast('Could not submit cancellation request.');
    }

    submitBtn.disabled = false;
    submitBtn.textContent = 'Submit request';
  });
  document.addEventListener('click',(e)=>{const btn=e.target.closest('.gp-profile-btn');if(btn){const x=btn.getAttribute('aria-expanded')==='true';document.querySelectorAll('.gp-profile-btn').forEach(b=>b.setAttribute('aria-expanded','false'));btn.setAttribute('aria-expanded',String(!x));return}document.querySelectorAll('.gp-profile-btn').forEach(b=>b.setAttribute('aria-expanded','false'))});
})();
</script>
</body>
</html>
