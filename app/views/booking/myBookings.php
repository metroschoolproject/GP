<?php
$bookings = $bookings ?? [];
$activeFilter = $activeFilter ?? 'all';

$statusLabels = [
    'draft' => 'Draft', 'pending_payment' => 'Pending Payment', 'payment_submitted' => 'Verifying Payment',
    'paid' => 'Paid', 'pending_admin' => 'Pending Admin', 'confirmed' => 'Confirmed',
    'completed' => 'Completed', 'cancelled' => 'Cancelled', 'cancellation_requested' => 'Cancellation Requested',
];
$statusColors = [
    'draft' => 'bg-gray-100 text-gray-600', 'pending_payment' => 'bg-amber-100 text-amber-700',
    'payment_submitted' => 'bg-yellow-100 text-yellow-800',
    'paid' => 'bg-blue-100 text-blue-700', 'pending_admin' => 'bg-amber-100 text-amber-700',
    'confirmed' => 'bg-green-100 text-green-700', 'completed' => 'bg-emerald-100 text-emerald-700',
    'cancelled' => 'bg-red-100 text-red-700', 'cancellation_requested' => 'bg-orange-100 text-orange-700',
];
$filterLabels = ['all' => 'All', 'pending_payment' => 'Pending', 'paid' => 'Paid', 'confirmed' => 'Confirmed', 'completed' => 'Completed', 'cancelled' => 'Cancelled', 'cancellation_requested' => 'Cancellation Requested'];

$money = fn($v) => 'RM ' . number_format((float)$v, 0);
$plain = function ($v) {
    $text = (string)$v;
    for ($i = 0; $i < 10; $i++) { $decoded = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8'); if ($decoded === $text) break; $text = $decoded; }
    return $text;
};
$h = fn($v) => htmlspecialchars($plain($v), ENT_QUOTES, 'UTF-8');

$getCountByStatus = function ($status) use ($bookings) {
    return count(array_filter($bookings, fn($b) => $b['status'] === $status));
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Bookings — Golden Promise</title>
<?php $publicCssVersion = file_exists(APPROOT . '/../public/css/app.css') ? filemtime(APPROOT . '/../public/css/app.css') : time(); ?>
<link rel="stylesheet" href="<?= URLROOT ?>/public/css/app.css?v=<?= $publicCssVersion ?>">
<link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root { --bg: #f2e4d4; --card: #fcf8f5; --rule: rgba(178,143,110,0.22); --rule-strong: rgba(178,143,110,0.45); --plum: #6b4459; --plum-dk: #4e3141; --plum-lt: #9b7289; --rose: #c27a8e; --gold: #b8924a; --muted: #a08878; --text: #1a1118; --text2: #5c4a54; --danger: #b94b4b; --r-sm: 8px; --r-md: 14px; --r-lg: 20px; --r-xl: 28px; --font-d: 'Playfair Display', Georgia, serif; --font-b: 'Poppins', system-ui, sans-serif; --pad-x: clamp(20px, 5vw, 72px); --ease-expo: cubic-bezier(0.19, 1, 0.22, 1); }
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html { scroll-behavior: smooth; }
body { background: var(--bg); color: var(--text); font-family: var(--font-b); font-size: 14px; line-height: 1.6; -webkit-font-smoothing: antialiased; min-height: 100vh; display: flex; flex-direction: column; overflow-x: hidden; }
a { color: inherit; text-decoration: none; }
img { display: block; max-width: 100%; }
button { font-family: var(--font-b); cursor: pointer; }

.gp-orbs { position: fixed; inset: 0; z-index: 0; pointer-events: none; overflow: hidden; }
.gp-orb { position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0; animation: orbFloat 20s ease-in-out infinite; }
.gp-orb-1 { width: 600px; height: 600px; background: radial-gradient(circle, rgba(107,68,89,0.12) 0%, transparent 70%); top: -200px; right: -100px; animation-delay: 0s; }
.gp-orb-2 { width: 500px; height: 500px; background: radial-gradient(circle, rgba(184,146,74,0.08) 0%, transparent 70%); bottom: -150px; left: -100px; animation-delay: -9s; }
@keyframes orbFloat { 0% { opacity: 0; transform: translate(0,0) scale(1); } 15% { opacity: 1; } 50% { transform: translate(40px,-30px) scale(1.08); } 85% { opacity: 1; } 100% { opacity: 0; transform: translate(0,0) scale(1); } }

.gp-header { position: sticky; top: 0; z-index: 100; display: grid; grid-template-columns: auto 1fr auto; align-items: center; gap: 24px; padding: 0 var(--pad-x); height: 68px; border-bottom: 1px solid var(--rule); background: rgba(242,228,212,0.82); backdrop-filter: blur(24px) saturate(1.4); transition: background 0.3s; }
.gp-brand { display: flex; align-items: center; gap: 12px; font-size: 17px; font-weight: 800; color: var(--text); }
.gp-brand-mark { display: grid; place-items: center; width: 38px; height: 38px; border-radius: 50%; background: var(--plum); color: #fffaf3; font-size: 13px; font-weight: 700; }
.gp-header-nav { display: flex; align-items: center; gap: 2px; }
.gp-header-nav a { padding: 7px 16px; border-radius: 999px; font-size: 13px; font-weight: 600; color: var(--text2); transition: all 0.22s; }
.gp-header-nav a:hover { color: var(--plum); background: rgba(107,68,89,0.08); }
.gp-header-actions { display: flex; align-items: center; gap: 10px; }

.gp-page {
    position: relative;
    z-index: 1;
    flex: 1;
    padding: 0 var(--pad-x) 80px;
    max-width: 1200px;
    margin: 0 auto;
    width: 100%;
}
.gp-page-head{
    position:relative;
    display:grid;
    place-items:center;
    min-height:220px;
    margin-top:-92px;
    margin-bottom:36px;
    padding:0 24px;
    overflow:hidden;
    text-align:center;
    border-radius:0 0 28px 28px;
    width:100vw;
    margin-left:calc(50% - 50vw);
    margin-right:calc(50% - 50vw);

    background:#e9ddd0;
}

.gp-page-head::before{
    content:"";
    position:absolute;
    inset:0;
    background:
        linear-gradient(rgba(0, 0, 0, 0.52),rgba(0, 0, 0, 0.47)),
        url("<?= URLROOT ?>/app/views/main/images/bookingBanner.png") center center/cover no-repeat;

    transform:scale(1.08);
    filter:blur(3px);
}

.gp-page-head::after{
    content:"";
    position:absolute;
    inset:0;
    background:linear-gradient(to bottom,
        rgba(249,241,233,.08),
        rgba(249,241,233,.18));
}

.gp-page-head>*{
    position:relative;
    z-index:2;
}

.gp-page-title{
    font-family:var(--font-d);
    font-size:clamp(34px,4vw,58px);
    font-weight:700;
    color:#fffaf5;
    margin-top:92px;
    line-height:1.05;
}

.gp-page-eyebrow{
    margin-top:14px;
    font-size:12px;
    font-weight:700;
    letter-spacing:.18em;
    text-transform:uppercase;
    color:rgba(255,248,239,.92);
}

.gp-page-subtitle{
    margin-top:10px;
    color:#fff;
    font-size:14px;
    opacity:.9;
}

.gp-filters { display: flex; gap: 6px; margin-bottom: 28px; flex-wrap: wrap; overflow-x: auto; padding-bottom: 4px; }
.gp-filter { padding: 6px 16px; border-radius: 9px; border: 1px solid var(--rule-strong); background: transparent; font-size: 12px; font-weight: 600; color: var(--text2); transition: all 0.2s; white-space: nowrap; text-decoration: none; }
.gp-filter:hover { border-color: var(--plum); color: var(--plum); }
.gp-filter.active { background: var(--plum); color: #fcf8f5; border-color: var(--plum); }

.gp-card { background: var(--card); border: 1px solid var(--rule); border-radius: var(--r-lg); padding: 20px 24px; opacity: 0; transform: translateY(16px); transition: all 0.4s var(--ease-expo); }
.gp-card.visible { opacity: 1; transform: translateY(0); }
.gp-card + .gp-card { margin-top: 12px; }

.gp-card-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; }
.gp-card-service { font-family: var(--font-d); font-size: 18px; font-weight: 600; color: var(--text); line-height: 1.2; }
.gp-card-supplier { font-size: 12px; color: var(--plum-lt); margin-top: 2px; }
.gp-card-date { font-size: 12px; color: var(--muted); margin-top: 4px; display: flex; align-items: center; gap: 6px; }
.gp-card-status-badge { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 999px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; white-space: nowrap; }

.gp-card-divider { height: 1px; background: var(--rule); margin: 14px 0; }

.gp-card-bottom { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 12px; }
.gp-card-payment { font-size: 12px; color: var(--text2); }
.gp-card-payment strong { color: var(--plum); }
.gp-card-actions { display: flex; gap: 8px; flex-wrap: wrap; }
.gp-btn-sm { display: inline-flex; align-items: center; gap: 4px; padding: 6px 14px; border-radius: 999px; border: 1px solid var(--rule-strong); font-size: 11px; font-weight: 600; color: var(--text2); background: transparent; transition: all 0.2s; text-decoration: none; }
.gp-btn-sm:hover { border-color: var(--plum); color: var(--plum); background: rgba(107,68,89,0.04); }
.gp-btn-sm.primary { background: var(--plum); color: #fcf8f5; border-color: var(--plum); }
.gp-btn-sm.primary:hover { background: var(--plum-dk); }
.gp-profile-dropdown{position:relative}
.gp-profile-btn{display:flex;align-items:center;gap:8px;padding:4px 12px 4px 4px;border-radius:999px;border:1px solid var(--rule-strong);background:var(--card);cursor:pointer;transition:all .2s;color:var(--plum);font-family:var(--font-b);font-size:13px;font-weight:600}
.gp-profile-btn:hover{border-color:var(--plum);background:rgba(107,68,89,.06)}
.gp-profile-avatar{display:grid;place-items:center;width:32px;height:32px;border-radius:50%;background:var(--plum);color:#fffaf3;font-size:12px;font-weight:800;letter-spacing:.5px}
.gp-profile-name{white-space:nowrap;max-width:100px;overflow:hidden;text-overflow:ellipsis}
.gp-profile-chevron{opacity:.6;transition:transform .2s}
.gp-profile-btn[aria-expanded="true"] .gp-profile-chevron{transform:rotate(180deg)}
.gp-profile-menu{position:absolute;top:calc(100% + 8px);right:0;min-width:180px;padding:6px;border-radius:12px;border:1px solid var(--rule);background:var(--card);box-shadow:0 12px 35px rgba(15,23,42,.1);opacity:0;visibility:hidden;transform:translateY(-4px);transition:all .15s ease}
.gp-profile-btn[aria-expanded="true"]+.gp-profile-menu,.gp-profile-menu.show{opacity:1;visibility:visible;transform:translateY(0)}
.gp-profile-menu-item{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:8px;font-size:13px;font-weight:600;color:var(--text);transition:all .15s}
.gp-profile-menu-item:hover{background:rgba(107,68,89,.06)}
.gp-profile-menu-item--danger{color:var(--danger)}
.gp-profile-menu-item--danger:hover{background:rgba(185,75,75,.08)}
.gp-btn-sm.danger { color: var(--danger); border-color: rgba(185,75,75,0.2); }
.gp-btn-sm.danger:hover { background: var(--danger); color: #fcf8f5; }

.gp-empty { text-align: center; padding: 80px 24px; border: 1px dashed rgba(107,68,89,0.18); border-radius: var(--r-xl); background: var(--card); }
.gp-empty-icon { display: inline-flex; align-items: center; justify-content: center; width: 72px; height: 72px; border-radius: 50%; background: rgba(107,68,89,0.07); color: var(--plum); margin-bottom: 20px; }
.gp-empty h2 { font-family: var(--font-d); font-size: 28px; color: var(--text); margin-bottom: 8px; }
.gp-empty p { color: var(--muted); font-size: 14px; margin-bottom: 24px; }

.gp-footer { padding: 24px var(--pad-x); border-top: 1px solid var(--rule); display: flex; justify-content: space-between; font-size: 12px; color: var(--muted); position: relative; z-index: 1; }

@keyframes fadeUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

@media (max-width: 640px) {
  .gp-card-top { flex-direction: column; }
  .gp-card-bottom { flex-direction: column; align-items: flex-start; }
  .gp-header-nav { display: none; }
  :root { --pad-x: 16px; }
}
@media (prefers-reduced-motion: reduce) { .gp-card, .gp-page-head { animation: none; opacity: 1; transform: none; } .gp-orb { animation: none; } }

/* Pagination */
.gp-pagination { display: flex; align-items: center; justify-content: space-between; padding: 14px 0; margin-top: 20px; border-top: 1px solid var(--rule); }
.gp-pagination-info { font-size: 12px; color: var(--muted); }
.gp-pagination-btns { display: flex; align-items: center; gap: 5px; }
.gp-pagination-btn { display: inline-flex; align-items: center; justify-content: center; min-width: 32px; height: 32px; padding: 0 8px; border: 1px solid var(--rule); border-radius: var(--r-sm); background: var(--card); color: var(--text2); font-size: 12px; font-weight: 600; font-family: var(--font-b); text-decoration: none; transition: all 0.15s; cursor: pointer; }
.gp-pagination-btn:hover { background: var(--bg); color: var(--text); border-color: var(--gold); }
.gp-pagination-btn-cur { background: var(--plum); color: #fcf8f5; border-color: var(--plum); }
.gp-pagination-btn-cur:hover { background: var(--plum-dk); }
.gp-pagination-btn-disabled { opacity: 0.3; pointer-events: none; }
</style>
</head>
<body>

<div class="gp-orbs" aria-hidden="true"><div class="gp-orb gp-orb-1"></div><div class="gp-orb gp-orb-2"></div></div>

<?php $gpNavActive = 'bookings'; require APPROOT . '/views/layouts/customerHomeNav.php'; ?>

<main class="gp-page">
  <div class="gp-page-head">
    <h1 class="gp-page-title">My Bookings</h1>

    <div class="gp-page-eyebrow">
        TRACK YOUR WEDDING JOURNEY
    </div>

    
</div>

  <div class="gp-filters">
    <?php foreach ($filterLabels as $key => $label):
      $count = $key === 'all' ? count($bookings) : $getCountByStatus($key);
    ?>
      <a class="gp-filter <?= $activeFilter === $key ? 'active' : '' ?>"
         href="<?= URLROOT ?>/booking/myBookings?status=<?= $key ?>">
        <?= $label ?> (<?= $count ?>)
      </a>
    <?php endforeach; ?>
  </div>

  <?php if (empty($bookings)): ?>
    <div class="gp-empty">
      <div class="gp-empty-icon">
        <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
      </div>
      <h2>No bookings yet</h2>
      <p>Browse our services to get started on planning your perfect wedding.</p>
      <a class="gp-btn-sm primary" href="<?= URLROOT ?>/customerServices/service">Explore services</a>
    </div>
  <?php else: ?>
    <?php foreach ($bookings as $b):
      $items = $b['items'] ?? [];
      $firstItem = $items[0] ?? null;
      $firstHall = $firstItem ? trim((string)($firstItem['venue_room_name'] ?? '')) : '';
      $itemCount = count($items);
      $deposit = (float)$b['total_amount'] * 0.10;
    ?>
    <div class="gp-card" data-index="<?= $loop->index ?? 0 ?>">
      <div class="gp-card-top">
        <div>
          <?php if ($firstItem): ?>
            <div class="gp-card-service"><?= $h($firstItem['service_name'] ?? 'Wedding Service') ?></div>
            <div class="gp-card-supplier"><?= $h($firstHall !== '' ? 'Hall: ' . $firstHall : ($firstItem['supplier_name'] ?? '')) ?></div>
          <?php else: ?>
            <div class="gp-card-service">Wedding Booking</div>
          <?php endif; ?>
          <div class="gp-card-date">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            Ref: <?= $h($b['booking_ref'] ?? '') ?>
            <?php if ($itemCount > 1): ?> &middot; +<?= $itemCount - 1 ?> more service<?= $itemCount > 2 ? 's' : '' ?><?php endif; ?>
          </div>
        </div>
        <span class="gp-card-status-badge <?= $statusColors[$b['status']] ?? 'bg-gray-100 text-gray-600' ?>">
          <?= $statusLabels[$b['status']] ?? ucfirst($b['status']) ?>
        </span>
      </div>

      <div class="gp-card-divider"></div>

      <div class="gp-card-bottom">
        <div class="gp-card-payment">
          Paid: <strong><?= $money((float)$b['paid_amount']) ?></strong>
          / <?= $money((float)$b['total_amount']) ?>
          <?php if ((float)$b['total_amount'] > 0): ?>
            <span style="color:var(--muted);font-size:11px;">
              (<?= round((float)$b['paid_amount'] / (float)$b['total_amount'] * 100) ?>%)
            </span>
          <?php endif; ?>
        </div>
        <div class="gp-card-actions">
          <a class="gp-btn-sm" href="<?= URLROOT ?>/booking/detail/<?= (int)$b['id'] ?>">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            View Details
          </a>
          <?php if (in_array($b['status'], ['paid', 'confirmed'])): ?>
            <a class="gp-btn-sm" href="<?= URLROOT ?>/booking/vouchers">View Voucher</a>
          <?php endif; ?>
          <?php if (!in_array($b['status'], ['cancelled', 'cancellation_requested', 'completed'])): ?>
            <a class="gp-btn-sm danger" href="<?= URLROOT ?>/booking/cancel/<?= (int)$b['id'] ?>">Request Cancellation</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  <?php endif; ?>

  <?php
  if (isset($currentPage, $totalPages, $totalCount, $perPage) && $totalPages > 1) {
      $h = function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };
      $classPrefix = 'customer';
      require APPROOT . '/views/partials/_pagination.php';
  }
  ?>
</main>

<footer class="gp-footer">
  <span>&copy; <?= date('Y') ?> Golden Promise</span>
  <span>Your curated wedding service collection</span>
</footer>

<script>
(function () {
  const cards = document.querySelectorAll('.gp-card');
  if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const el = entry.target;
          setTimeout(() => el.classList.add('visible'), 80);
          observer.unobserve(el);
        }
      });
    }, { threshold: 0.05 });
    cards.forEach(el => observer.observe(el));
  } else {
    cards.forEach(el => el.classList.add('visible'));
  }

  const header = document.querySelector('.gp-header');
  if (header) {
    window.addEventListener('scroll', () => {
      header.style.background = window.scrollY > 10 ? 'rgba(235,220,204,0.94)' : 'rgba(242,228,212,0.82)';
    }, { passive: true });
  }
  document.addEventListener('click',(e)=>{const btn=e.target.closest('.gp-profile-btn');if(btn){const x=btn.getAttribute('aria-expanded')==='true';document.querySelectorAll('.gp-profile-btn').forEach(b=>b.setAttribute('aria-expanded','false'));btn.setAttribute('aria-expanded',String(!x));return}document.querySelectorAll('.gp-profile-btn').forEach(b=>b.setAttribute('aria-expanded','false'))});
})();
</script>
</body>
</html>
