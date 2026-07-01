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

$firstItem = $items[0] ?? [];
$statusText = ucwords(str_replace('_', ' ', (string)($booking['status'] ?? '')));
$venueRoom = trim((string)($firstItem['venue_room_name'] ?? ''));
$venueName = trim((string)($firstItem['venue_name'] ?? ''));
$venueLocation = trim((string)($firstItem['location'] ?? ''));
$venueTitle = $venueRoom !== '' ? $venueRoom : ($venueName !== '' ? $venueName : ($firstItem['service_name'] ?? 'Event venue'));
$venueSub = $venueName !== '' && $venueName !== $venueTitle ? $venueName : ($firstItem['supplier_name'] ?? '');
$venuePlace = $venueLocation !== '' ? $venueLocation : 'Golden Promise event location';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Request Cancellation - Golden Promise</title>
<?php $publicCssVersion = file_exists(APPROOT . '/../public/css/app.css') ? filemtime(APPROOT . '/../public/css/app.css') : time(); ?>
<link rel="stylesheet" href="<?= URLROOT ?>/public/css/app.css?v=<?= $publicCssVersion ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--bg:#f7eee7;--card:#fffdfb;--surface:#fff8f4;--rule:rgba(178,143,110,.24);--rule-strong:rgba(178,143,110,.45);--plum:#6b4459;--plum-dk:#4e3141;--muted:#8f796d;--text:#1a1118;--danger:#b94b4b;--gold:#b8924a;--green:#28784a;--font-d:'Playfair Display',Georgia,serif;--font-b:'Poppins',system-ui,sans-serif;--pad-x:clamp(18px,5vw,70px);--ease:cubic-bezier(.19,1,.22,1)}
*{box-sizing:border-box}body{margin:0;background:radial-gradient(circle at 85% 15%,rgba(184,146,74,.08),transparent 28%),linear-gradient(180deg,#fffaf7 0%,var(--bg) 55%,#fffaf7 100%);color:var(--text);font-family:var(--font-b);min-height:100vh}
a{color:inherit;text-decoration:none}.gp-page{position:relative;max-width:1160px;margin:0 auto;padding:34px var(--pad-x) 36px}.gp-page::after{content:"";position:fixed;right:46px;top:92px;width:230px;height:330px;pointer-events:none;opacity:.13;background:radial-gradient(ellipse at center,transparent 42%,rgba(184,146,74,.34) 43%,transparent 44%);border-radius:50%;transform:rotate(-14deg)}
.gp-back{margin-bottom:28px}.gp-back a{display:inline-flex;align-items:center;gap:8px;color:var(--plum);font-size:13px;font-weight:700;transition:color .18s}.gp-back a:hover{color:var(--plum-dk)}
.gp-hero{max-width:1020px;margin:0 auto 24px;text-align:left}.gp-title{font-family:var(--font-d);font-size:clamp(38px,4.8vw,62px);line-height:.96;margin:0 0 14px;font-weight:600;letter-spacing:-.02em}.gp-title em{color:#8d5f75;font-style:italic;font-weight:500}.gp-sub{margin:0;color:#7f7471;font-size:14px;line-height:1.7;max-width:640px}
.gp-layout{display:grid;grid-template-columns:minmax(0,1fr) 360px;gap:24px;align-items:stretch;max-width:1020px;margin:0 auto}.gp-card{background:rgba(255,253,251,.88);border:1px solid rgba(178,143,110,.22);border-radius:18px;box-shadow:0 18px 54px rgba(78,49,65,.07);overflow:hidden;backdrop-filter:blur(12px)}.gp-card-body{padding:22px 24px}.gp-card-kicker{font-size:10px;letter-spacing:.16em;text-transform:uppercase;color:var(--plum);font-weight:700;margin-bottom:8px}.gp-card-title{font-size:17px;font-weight:700;margin-bottom:18px;color:var(--text)}
.gp-warning{display:grid;grid-template-columns:38px 1fr;gap:14px;align-items:start;border:1px solid #fecaca;background:linear-gradient(135deg,#fff7f7,#fffafa);color:#7f1d1d;border-radius:14px;padding:16px 18px;font-size:12.5px;line-height:1.6;margin-bottom:16px}.gp-alert-icon{display:grid;place-items:center;width:30px;height:30px;border-radius:999px;background:#ff6464;color:#fff;font-size:18px;font-weight:700;box-shadow:0 10px 24px rgba(255,100,100,.24)}
.gp-refund{display:grid;grid-template-columns:38px 1fr;gap:14px;align-items:start;border:1px solid rgba(40,120,74,.2);background:linear-gradient(135deg,#f7fffa,#fbfffd);border-radius:14px;padding:16px 18px;margin-bottom:18px;color:var(--green)}.gp-refund-icon{display:grid;place-items:center;width:30px;height:30px;border-radius:999px;background:rgba(40,120,74,.68);color:#fff}.gp-refund-label{font-size:10px;font-weight:800;letter-spacing:.14em;text-transform:uppercase;margin-bottom:5px}.gp-refund-amount{font-family:var(--font-d);font-size:26px;font-weight:600;line-height:1}.gp-refund-copy{margin-top:7px;font-size:12.5px;font-weight:600}.gp-refund-note{margin-top:10px;padding-top:10px;border-top:1px solid rgba(40,120,74,.18);font-size:11.5px;line-height:1.6;color:#2f6d47}
.gp-field{display:flex;flex-direction:column;gap:9px}.gp-label{font-size:11px;letter-spacing:.14em;text-transform:uppercase;color:var(--plum);font-weight:700}.gp-textarea-wrap{position:relative}.gp-textarea{width:100%;min-height:106px;resize:vertical;border:1px solid rgba(178,143,110,.34);border-radius:12px;background:rgba(255,250,247,.78);padding:14px 18px 30px;font:inherit;font-size:13px;color:var(--text);outline:none;transition:border-color .2s,box-shadow .2s,background .2s}.gp-textarea:focus{border-color:var(--plum);background:#fffdfb;box-shadow:0 0 0 3px rgba(107,68,89,.08)}.gp-textarea.is-invalid{border-color:#dc2626;background:#fff7f7;box-shadow:0 0 0 3px rgba(220,38,38,.08)}.gp-char-count{position:absolute;right:14px;bottom:10px;font-size:12px;color:#a8a09d}
.gp-actions{display:block;max-width:1020px;margin:22px auto 0}.gp-review-note{display:flex;align-items:center;gap:12px;min-height:46px;border-radius:12px;background:rgba(184,146,74,.09);color:#5f554d;padding:11px 16px;font-size:12.5px;margin-top:18px;width:100%;animation:gpReviewEnter .95s var(--ease) both}.gp-review-note-icon{display:grid;place-items:center;width:27px;height:27px;border-radius:999px;background:rgba(184,146,74,.75);color:#fff;font-weight:800;flex-shrink:0}.gp-btn{position:relative;overflow:hidden;height:46px;border-radius:10px;border:1px solid var(--rule-strong);padding:0 18px;font-weight:700;font-size:13.5px;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;gap:10px;cursor:pointer;transition:all .22s var(--ease);font-family:var(--font-b)}.gp-btn.primary{width:100%;border-color:var(--plum);background:var(--plum);color:#fcf8f5;box-shadow:0 16px 34px rgba(107,68,89,.22)}.gp-btn.primary::after{content:"";position:absolute;top:-35%;bottom:-35%;left:-45%;width:34%;background:linear-gradient(90deg,transparent,rgba(255,255,255,.42),transparent);transform:skewX(-18deg);opacity:0;pointer-events:none}.gp-btn.primary:hover{background:var(--plum-dk);transform:translateY(-1px)}.gp-btn.primary:hover::after{animation:gpMirrorSweep .85s var(--ease) both}.gp-btn.secondary{background:transparent;color:var(--text)}.gp-btn.secondary:hover{border-color:var(--plum);color:var(--plum)}.gp-btn:disabled{opacity:.6;cursor:not-allowed;transform:none}.gp-btn:disabled::after{display:none}.gp-card-submit{margin-top:18px}@keyframes gpReviewEnter{from{opacity:0;transform:translateY(-18px)}to{opacity:1;transform:translateY(0)}}@keyframes gpMirrorSweep{0%{left:-45%;opacity:0}18%{opacity:1}100%{left:112%;opacity:0}}
.gp-summary-row{display:flex;justify-content:space-between;gap:18px;padding:16px 0;border-bottom:1px solid var(--rule);font-size:14px}.gp-items+.gp-summary-row{margin-top:8px;border-top:1px solid var(--rule);border-bottom:0}.gp-summary-row span:first-child{color:var(--text)}.gp-summary-row strong{text-align:right;font-weight:700}.gp-summary-row.status strong{color:var(--green)}.gp-venue{margin-top:22px;border:1px solid rgba(178,143,110,.24);border-radius:14px;padding:20px 22px;background:rgba(255,250,247,.56)}.gp-venue-kicker{display:flex;align-items:center;gap:10px;font-size:11px;font-weight:800;letter-spacing:.14em;text-transform:uppercase;color:var(--plum);margin-bottom:22px}.gp-venue-title{font-weight:700;font-size:14px;margin-bottom:6px}.gp-venue-copy{font-size:13px;color:#7f7471;line-height:1.55}
.gp-items{display:flex;flex-direction:column;gap:8px;margin:14px -8px 6px}.gp-item{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:12px;align-items:center;padding:12px 8px;background:rgba(255,250,247,.38)}.gp-item-name{font-size:13px;font-weight:600;color:var(--text);line-height:1.35}.gp-item-meta{font-size:12px;color:var(--muted);margin-top:3px}.gp-item-price{font-size:13px;font-weight:600;color:var(--text);white-space:nowrap}.gp-toast{position:fixed;right:20px;top:86px;background:#fef2f2;border:1px solid #fecaca;color:var(--danger);border-radius:12px;padding:13px 16px;font-size:13px;box-shadow:0 14px 40px rgba(0,0,0,.12);opacity:0;transform:translateY(-8px);transition:.25s;z-index:20}.gp-toast.show{opacity:1;transform:translateY(0)}
.gp-profile-dropdown{position:relative}.gp-profile-btn{display:flex;align-items:center;gap:8px;padding:4px 12px 4px 4px;border-radius:999px;border:1px solid var(--rule-strong);background:var(--card);cursor:pointer;transition:all .2s;color:var(--plum);font-family:var(--font-b);font-size:13px;font-weight:600}.gp-profile-btn:hover{border-color:var(--plum);background:rgba(107,68,89,.06)}.gp-profile-avatar{display:grid;place-items:center;width:32px;height:32px;border-radius:50%;background:var(--plum);color:#fffaf3;font-size:12px;font-weight:800;letter-spacing:.5px}.gp-profile-name{white-space:nowrap;max-width:100px;overflow:hidden;text-overflow:ellipsis}.gp-profile-chevron{opacity:.6;transition:transform .2s}.gp-profile-btn[aria-expanded="true"] .gp-profile-chevron{transform:rotate(180deg)}.gp-profile-menu{position:absolute;top:calc(100% + 8px);right:0;min-width:180px;padding:6px;border-radius:12px;border:1px solid var(--rule);background:var(--card);box-shadow:0 12px 35px rgba(15,23,42,.1);opacity:0;visibility:hidden;transform:translateY(-4px);transition:all .15s ease}.gp-profile-btn[aria-expanded="true"]+.gp-profile-menu,.gp-profile-menu.show{opacity:1;visibility:visible;transform:translateY(0)}.gp-profile-menu-item{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:8px;font-size:13px;font-weight:600;color:var(--text);transition:all .15s}.gp-profile-menu-item:hover{background:rgba(107,68,89,.06)}.gp-profile-menu-item--danger{color:var(--danger)}.gp-profile-menu-item--danger:hover{background:rgba(185,75,75,.08)}
@media(max-width:920px){.gp-layout,.gp-actions{grid-template-columns:1fr}.gp-page::after{display:none}.gp-hero{margin-left:0}.gp-actions{margin-top:18px}}@media(max-width:560px){:root{--pad-x:16px}.gp-page{padding-top:28px}.gp-title{font-size:42px}.gp-card-body{padding:22px 18px}.gp-warning,.gp-refund{grid-template-columns:1fr}.gp-btn{width:100%}}
@media(prefers-reduced-motion:reduce){.gp-review-note,.gp-btn.primary:hover::after{animation:none}}
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

  <div class="gp-hero">
    <h1 class="gp-title">Request <em>Cancellation</em></h1>
    <p class="gp-sub">Send your cancellation reason to the Golden Promise team.<br>We will review supplier status, payment records, and any deposit/refund handling manually.</p>
    <div class="gp-review-note">
      <span class="gp-review-note-icon">i</span>
      <span>Our team will review your cancellation request. You will be notified once a decision is made.</span>
    </div>
  </div>

  <form id="cancel-form" method="POST" action="<?= URLROOT ?>/booking/submitCancellation" novalidate>
    <?= csrf_field() ?>
    <input type="hidden" name="booking_id" value="<?= (int)($booking['id'] ?? 0) ?>">

    <div class="gp-layout">
      <section class="gp-card">
        <div class="gp-card-body">
          <div class="gp-card-kicker">Cancellation Details</div>
          <div class="gp-card-title">Booking <?= $h($bookingRef) ?></div>

          <div class="gp-warning">
            <div class="gp-alert-icon" aria-hidden="true">!</div>
            <div>
              <strong>Cancellation is not final until the admin team reviews it.</strong><br>
              If a <?= $depositPercent ?>% deposit has been paid, refund handling depends on the event date, supplier work already started, and the cancellation policy.
            </div>
          </div>

          <?php if ($refundEstimate && (float)($booking['paid_amount'] ?? 0) > 0): ?>
          <?php
          $estTotal = (float)($booking['total_amount'] ?? 0);
          $estFee = round($estTotal * ($platformFeePercent / 100), 2);
          ?>
          <div class="gp-refund">
            <div class="gp-refund-icon" aria-hidden="true">
              <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6 9 17l-5-5"/></svg>
            </div>
            <div>
              <div class="gp-refund-label">Estimated Refund</div>
              <div class="gp-refund-amount"><?= number_format($refundEstimate[0], 0) ?> MMK</div>
              <div class="gp-refund-copy"><?= htmlspecialchars($refundEstimate[1], ENT_QUOTES, 'UTF-8') ?></div>
              <?php if ($estFee > 0): ?>
              <div class="gp-refund-note">
                Your payment of <?= $money($booking['paid_amount'] ?? 0) ?> includes a <?= $money($estFee) ?> platform service fee (<?= rtrim(rtrim(number_format($platformFeePercent, 2), '0'), '.') ?>%). The refund amount is calculated on the full payment including this fee.
              </div>
              <?php endif; ?>
              <?php if ($refundEstimate[0] <= 0): ?>
              <div class="gp-refund-note" style="color:#92400e">Based on the cancellation policy, no refund is applicable for cancellations less than 2 days before the event.</div>
              <?php endif; ?>
            </div>
          </div>
          <?php endif; ?>

          <div class="gp-field">
            <label class="gp-label" for="reason">Reason for cancellation</label>
            <div class="gp-textarea-wrap">
              <textarea class="gp-textarea" id="reason" name="reason" required minlength="10" placeholder="Tell us why you need to cancel this booking."></textarea>
              <span class="gp-char-count"><span id="reason-count">0</span>/500</span>
            </div>
          </div>

          <button class="gp-btn primary gp-card-submit" id="submit-btn" type="submit">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>
            Submit Cancellation Request
          </button>
        </div>
      </section>

      <aside class="gp-card">
        <div class="gp-card-body">
          <div class="gp-card-kicker">Booking Summary</div>
          <?php if (!empty($items)): ?>
          <div class="gp-items">
            <?php foreach ($items as $item): ?>
              <div class="gp-item">
                <div>
                  <div class="gp-item-name"><?= $h($item['service_name'] ?? 'Service') ?></div>
                  <div class="gp-item-meta"><?= $h($item['supplier_name'] ?? 'Supplier') ?></div>
                </div>
                <div class="gp-item-price"><?= $money($item['price'] ?? 0) ?></div>
              </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
          <div class="gp-summary-row"><span>Total</span><strong><?= $money($booking['total_amount'] ?? 0) ?></strong></div>
          <div class="gp-summary-row"><span>Paid</span><strong><?= $money($booking['paid_amount'] ?? 0) ?></strong></div>
          <div class="gp-summary-row status"><span>Status</span><strong><?= $h($statusText) ?></strong></div>
        </div>
      </aside>
    </div>

  </form>
</main>

<div class="gp-toast" id="toast" role="alert"></div>

<script>
(function () {
  const form = document.getElementById('cancel-form');
  const submitBtn = document.getElementById('submit-btn');
  const toast = document.getElementById('toast');
  const reason = document.getElementById('reason');
  const reasonCount = document.getElementById('reason-count');
  const submitBtnHtml = submitBtn.innerHTML;

  function showToast(message) {
    toast.textContent = message;
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 4500);
  }

  if (reason && reasonCount) {
    const updateCount = () => {
      reasonCount.textContent = reason.value.length;
      if (reason.value.trim().length >= 10) reason.classList.remove('is-invalid');
    };
    reason.addEventListener('input', updateCount);
    updateCount();
  }

  form.addEventListener('submit', async function (event) {
    event.preventDefault();
    if (reason && reason.value.trim().length < 10) {
      reason.classList.add('is-invalid');
      reason.focus();
      return;
    }
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
    submitBtn.innerHTML = submitBtnHtml;
  });
  document.addEventListener('click',(e)=>{const btn=e.target.closest('.gp-profile-btn');if(btn){const x=btn.getAttribute('aria-expanded')==='true';document.querySelectorAll('.gp-profile-btn').forEach(b=>b.setAttribute('aria-expanded','false'));btn.setAttribute('aria-expanded',String(!x));return}document.querySelectorAll('.gp-profile-btn').forEach(b=>b.setAttribute('aria-expanded','false'))});
})();
</script>
</body>
</html>
