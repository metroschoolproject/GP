<?php
$booking = $booking ?? [];
$items = $items ?? [];
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
<link rel="icon" type="image/png" href="<?= URLROOT ?>/public/images/home/gp_logo.png">
<title>Booking Confirmed — Golden Promise</title>
<?php include APPROOT . '/views/partials/ga-tracking.php'; ?>
<?php $publicCssVersion = file_exists(APPROOT . '/../public/css/app.css') ? filemtime(APPROOT . '/../public/css/app.css') : time(); ?>
<link rel="stylesheet" href="<?= URLROOT ?>/public/css/app.css?v=<?= $publicCssVersion ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root {
  --bg: #f2e4d4; --card: #fcf8f5; --rule: rgba(178,143,110,0.22);
  --plum: #6b4459; --plum-lt: #9b7289; --rose: #c27a8e; --gold: #b8924a;
  --muted: #a08878; --text: #1a1118; --text2: #5c4a54;
  --r-md: 14px; --r-lg: 20px;
  --font-d: 'Playfair Display', Georgia, serif;
  --font-b: 'Poppins', system-ui, sans-serif;
  --ease-expo: cubic-bezier(0.19, 1, 0.22, 1);
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body { background: var(--bg); color: var(--text); font-family: var(--font-b); font-size: 14px; -webkit-font-smoothing: antialiased; min-height: 100vh; display: flex; flex-direction: column; align-items: center; padding: 60px 20px; }
a { color: inherit; text-decoration: none; }

.gp-card { background: var(--card); border-radius: var(--r-lg); border: 1px solid var(--rule); max-width: 560px; width: 100%; overflow: hidden; box-shadow: 0 20px 60px rgba(26,17,24,0.08); position: relative; }
.gp-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, var(--plum) 0%, var(--rose) 50%, var(--gold) 100%); }

.gp-success-icon { width: 80px; height: 80px; margin: 48px auto 20px; border-radius: 50%; background: #f0fdf4; display: flex; align-items: center; justify-content: center; animation: popIn 0.6s var(--ease-expo); }
.gp-success-icon svg { width: 40px; height: 40px; color: #166534; stroke-width: 2.5; }

.gp-success-head { text-align: center; padding: 0 32px; }
.gp-success-title { font-family: var(--font-d); font-size: 32px; font-weight: 600; color: #166534; }
.gp-success-sub { color: var(--muted); font-size: 14px; margin-top: 6px; }
.gp-booking-ref { text-align: center; margin: 16px 0; font-size: 13px; color: var(--text2); }
.gp-booking-ref strong { font-family: var(--font-d); font-size: 22px; color: var(--plum); display: block; margin-top: 4px; letter-spacing: 1px; }

.gp-section { padding: 24px 32px; border-top: 1px solid var(--rule); }
.gp-section-title { font-size: 10px; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; color: var(--gold); margin-bottom: 16px; }

.gp-steps { display: flex; flex-direction: column; gap: 0; }
.gp-step { display: flex; gap: 14px; padding: 12px 0; position: relative; }
.gp-step:not(:last-child)::after { content: ''; position: absolute; left: 13px; top: 36px; bottom: -4px; width: 2px; background: var(--rule); }
.gp-step-num { width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; flex-shrink: 0; }
.gp-step.active .gp-step-num { background: var(--plum); color: #fcf8f5; }
.gp-step.future .gp-step-num { background: rgba(107,68,89,0.1); color: var(--muted); }
.gp-step-content { padding-top: 3px; }
.gp-step-title { font-size: 13px; font-weight: 600; color: var(--text); }
.gp-step-desc { font-size: 12px; color: var(--muted); }

.gp-item-row { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid var(--rule); font-size: 13px; }
.gp-item-row:last-child { border-bottom: none; }
.gp-item-status { font-size: 10px; font-weight: 700; letter-spacing: 0.05em; padding: 3px 8px; border-radius: 999px; background: rgba(107,68,89,0.08); color: var(--plum-lt); }

.gp-actions { padding: 24px 32px 32px; display: flex; flex-direction: column; gap: 10px; }
.gp-btn-primary { display: flex; align-items: center; justify-content: center; gap: 8px; height: 50px; border-radius: var(--r-md); border: none; background: var(--plum); color: #fffaf3; font-size: 14px; font-weight: 700; box-shadow: 0 10px 28px rgba(107,68,89,0.28); transition: all 0.3s var(--ease-expo); text-decoration: none; }
.gp-btn-primary:hover { background: var(--plum-dk); transform: translateY(-2px); }
.gp-btn-secondary { display: flex; align-items: center; justify-content: center; gap: 6px; height: 44px; border-radius: var(--r-md); border: 1px solid var(--rule); background: transparent; color: var(--text2); font-size: 13px; font-weight: 600; transition: all 0.22s; text-decoration: none; }
.gp-btn-secondary:hover { border-color: var(--plum); color: var(--plum); }

.gp-spinner { display: inline-block; width: 24px; height: 24px; border: 3px solid rgba(107,68,89,0.15); border-top-color: var(--plum); border-radius: 50%; animation: spin 0.7s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
@keyframes popIn { 0% { transform: scale(0); opacity: 0; } 60% { transform: scale(1.15); } 100% { transform: scale(1); opacity: 1; } }

@media (max-width: 600px) {
  body { padding: 24px 16px; }
  .gp-section { padding: 20px 20px; }
  .gp-success-head { padding: 0 20px; }
  .gp-actions { padding: 20px; }
}
</style>
</head>
<body>

<div class="gp-card">
  <div class="gp-success-icon">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
  </div>

  <div class="gp-success-head">
    <h1 class="gp-success-title">Booking Confirmed!</h1>
    <p class="gp-success-sub">Your wedding team is forming</p>
  </div>

  <div class="gp-booking-ref">
    <span>Reference</span>
    <strong><?= $h($bookingRef) ?></strong>
  </div>

  <div class="gp-section">
    <div class="gp-section-title">What happens next</div>
    <div class="gp-steps">
      <div class="gp-step active">
        <div class="gp-step-num">1</div>
        <div class="gp-step-content">
          <div class="gp-step-title">Your suppliers are notified</div>
          <div class="gp-step-desc">They'll receive your booking details instantly</div>
        </div>
      </div>
      <div class="gp-step future">
        <div class="gp-step-num">2</div>
        <div class="gp-step-content">
          <div class="gp-step-title">Suppliers accept within 48 hours</div>
          <div class="gp-step-desc">Each supplier will confirm availability</div>
        </div>
      </div>
      <div class="gp-step future">
        <div class="gp-step-num">3</div>
        <div class="gp-step-content">
          <div class="gp-step-title">Confirmation email sent</div>
          <div class="gp-step-desc">You'll receive a summary at <?= $h($_SESSION['session_email'] ?? 'your email') ?></div>
        </div>
      </div>
    </div>
  </div>

  <div class="gp-section">
    <div class="gp-section-title">Services booked</div>
    <?php foreach ($items as $item):
      $status = ($item['status'] ?? 'pending') === 'pending' ? 'Pending' : ucfirst($item['status'] ?? 'pending');
      $hallName = trim((string)($item['venue_room_name'] ?? ''));
    ?>
    <div class="gp-item-row">
      <div>
        <div style="font-weight:500;">
          <?= $h($item['service_name'] ?? 'Service') ?>
          <?php if (!empty($item['addon_package_name'])): ?>
            <small> · Add-on for <?= $h($item['addon_package_name']) ?></small>
          <?php endif; ?>
        </div>
        <?php if ($hallName !== ''): ?>
        <div style="font-size:11px;color:var(--muted);">Hall: <?= $h($hallName) ?></div>
        <?php endif; ?>
        <div style="font-size:11px;color:var(--muted);"><?= $h($item['supplier_name'] ?? '') ?></div>
      </div>
      <span class="gp-item-status"><?= $status ?></span>
    </div>
    <?php endforeach; ?>
  </div>

  <div class="gp-actions">
    <a class="gp-btn-primary" href="<?= URLROOT ?>/booking/myBookings">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
      View My Bookings
    </a>
    <a class="gp-btn-secondary" href="<?= URLROOT ?>/customerServices/service">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Browse More Services
    </a>
  </div>
</div>

<script>
(function () {
  // Poll for payment confirmation if still pending
  const bookingId = <?= (int)($booking['id'] ?? 0) ?>;
  const statusEl = document.querySelector('.gp-success-title');
  let polls = 0;

  function pollStatus() {
    if (polls > 15) return; // Stop after ~45s
    polls++;

    fetch('<?= URLROOT ?>/booking/status/' + bookingId)
      .then(r => r.json())
      .then(data => {
        if (data.status === 'paid' || data.status === 'confirmed') {
          // Already showing success — all good
          return;
        }
        if (data.status === 'draft' || data.status === 'pending_payment') {
          if (statusEl) statusEl.textContent = 'Waiting for payment confirmation…';
          setTimeout(pollStatus, 3000);
        }
      })
      .catch(() => setTimeout(pollStatus, 3000));
  }

  // Start polling if status might not be confirmed yet
  <?php if (($booking['status'] ?? '') === 'paid' || ($booking['status'] ?? '') === 'confirmed'): ?>
    // Already confirmed, no polling needed
  <?php else: ?>
    setTimeout(pollStatus, 3000);
  <?php endif; ?>
})();
</script>
<?php include APPROOT . '/views/partials/cookie-consent.php'; ?>
<?php require APPROOT . '/views/layouts/customerFooter.php'; ?>
</body>
</html>
