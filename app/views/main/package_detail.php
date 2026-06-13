<?php
$package = $package ?? [];
$cartCount = (int)($cartCount ?? 0);
$categoryServices = $package['category_services'] ?? [];

$isLoggedIn = !empty($_SESSION['session_uid']);
$authNavUrl = $isLoggedIn ? URLROOT . '/users/logout' : URLROOT . '/users/auth';
$authNavLabel = $isLoggedIn ? 'Logout' : 'Sign in';

$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$money = fn($v) => 'MMK ' . number_format((float)$v, 0);
$moneyRange = function ($svc) use ($money) {
    if (($svc['quantity_type'] ?? 'fixed') === 'guests') {
        $quantity = max(1, (int)($svc['quantity'] ?? 1));
        $unit = (float)($svc['unit_price'] ?? $svc['price_min'] ?? $svc['price'] ?? 0);
        $total = (float)($svc['package_price'] ?? ($unit * $quantity));
        return $money($total) . ' · ' . $quantity . ' guests at ' . $money($unit);
    }
    return $money($svc['package_price'] ?? $svc['unit_price'] ?? $svc['price_min'] ?? $svc['price'] ?? 0);
};
$serviceDetailUrl = function ($svc) use ($package) {
    $url = URLROOT . '/customerServices/detail/' . (int)($svc['id'] ?? 0);
    $params = [
        'package_id' => (int)($package['package_id'] ?? 0),
        'package_item_id' => (int)($svc['package_item_id'] ?? 0),
    ];
    return $url . '?' . http_build_query($params);
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $h($package['name'] ?? 'Package') ?> — Golden Promise</title>
<?php
$publicCssVersion = file_exists(APPROOT . '/../public/css/app.css') ? filemtime(APPROOT . '/../public/css/app.css') : time();
?>
<link rel="stylesheet" href="<?= URLROOT ?>/public/css/app.css?v=<?= $publicCssVersion ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
<style>
:root {
  --c-bg:        #f5e8d9;
  --c-surface:   #faf5ef;
  --c-white:     #ffffff;
  --c-card:      #faf5ef;
  --c-rule:      #ead8c7;
  --c-strong:    #6d4c5b;
  --c-accent:    #7b5c69;
  --c-muted:     #b79c8b;
  --c-text:      #111827;
  --c-danger:    #b94b4b;
  --c-pale:      #b79c8b;

  --r-card:  0.75rem;
  --sh-card:   0 20px 40px rgba(15, 23, 42, 0.08);
  --sh-panel:  0 18px 45px rgba(15, 23, 42, 0.06);

  --font-display: 'Playfair Display', Georgia, serif;
  --font-body:    'Poppins', system-ui, -apple-system, sans-serif;
  --pad-x: clamp(20px, 5vw, 72px);
  --ease-out-expo: cubic-bezier(0.19, 1, 0.22, 1);
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body {
  background: var(--c-bg);
  color: var(--c-text);
  font-family: var(--font-body);
  font-size: 14px;
  line-height: 1.6;
  -webkit-font-smoothing: antialiased;
}
a { color: inherit; text-decoration: none; }
img { display: block; max-width: 100%; }

.gp-texture {
  position: fixed; inset: 0; z-index: -1; pointer-events: none;
  background-image:
    radial-gradient(ellipse at 20% 8%, rgba(109,76,91,0.04) 0%, transparent 60%),
    radial-gradient(ellipse at 80% 92%, rgba(183,156,139,0.07) 0%, transparent 55%);
}

/* ─── HEADER ─────────────────────────────── */
.gp-header {
  position: sticky; top: 0; z-index: 50;
  display: grid;
  grid-template-columns: auto 1fr auto;
  align-items: center;
  gap: 24px;
  padding: 16px var(--pad-x);
  border-bottom: 1px solid rgba(184, 154, 109, 0.25);
  background: rgba(248, 245, 239, 0.90);
  backdrop-filter: blur(18px);
}
.gp-brand {
  display: flex; align-items: center; gap: 12px;
  color: #211b17;
  font-size: 18px; font-weight: 800; white-space: nowrap;
}
.gp-brand-mark {
  display: grid; place-items: center;
  width: 40px; height: 40px; border-radius: 50%;
  background: var(--c-strong); color: #fffaf3;
  font-size: 14px; letter-spacing: 1px;
}
.gp-header-nav { display: flex; align-items: center; justify-content: center; gap: 4px; }
.gp-header-nav a {
  padding: 8px 18px; border-radius: 999px;
  font-size: 13px; font-weight: 700; color: #51483f;
  transition: all 0.2s;
}
.gp-header-nav a:hover { color: var(--c-strong); background: rgba(109,76,91,0.08); }
.gp-header-nav a.active { color: var(--c-strong); background: rgba(109,76,91,0.08); }
.gp-header-actions { display: flex; align-items: center; gap: 12px; justify-content: flex-end; }
.gp-cart-badge {
  display: inline-flex; align-items: center; gap: 6px;
  position: relative; padding: 8px 14px 8px 10px;
  border-radius: 999px; border: 1px solid var(--c-rule);
  background: var(--c-white); color: var(--c-strong);
  font-size: 13px; font-weight: 700; transition: all 0.2s;
}
.gp-cart-badge:hover { border-color: var(--c-strong); background: rgba(109,76,91,0.06); }
.gp-cart-badge-count {
  display: inline-flex; align-items: center; justify-content: center;
  min-width: 20px; height: 20px; padding: 0 6px;
  border-radius: 999px; background: var(--c-strong); color: #fff;
  font-size: 10px; font-weight: 700;
}
.gp-header-cta {
  display: inline-flex; align-items: center; justify-content: center;
  min-height: 40px; padding: 0 20px; border-radius: 999px; border: none;
  background: var(--c-strong); color: #fffaf3;
  font-size: 13px; font-weight: 800; cursor: pointer;
  box-shadow: 0 14px 30px rgba(109,76,91,0.18); transition: all 0.2s;
}
.gp-header-cta:hover { background: #5a3d4a; transform: translateY(-1px); }

/* ─── PAGE ───────────────────────────────── */
.gp-detail-page { padding: 0 var(--pad-x) 72px; }

/* ─── BREADCRUMB ─────────────────────────── */
.gp-breadcrumb {
  display: flex; align-items: center; gap: 8px;
  padding: 20px 0 24px;
  font-size: 12px; font-weight: 600; color: var(--c-muted);
}
.gp-breadcrumb a:hover { color: var(--c-strong); }
.gp-breadcrumb-sep { color: var(--c-rule); }

/* ─── HERO AREA ──────────────────────────── */
.gp-detail-hero {
  padding: 0 0 48px;
}
.gp-detail-hero-overline {
  font-size: 12px; font-weight: 800; letter-spacing: 0.16em; text-transform: uppercase;
  color: var(--c-danger); margin-bottom: 12px;
}
.gp-detail-hero h1 {
  font-family: var(--font-display);
  font-size: clamp(40px, 5vw, 72px);
  font-weight: 600;
  line-height: 0.92;
  color: var(--c-text);
  margin-bottom: 12px;
}
.gp-detail-hero .tagline {
  font-size: 18px;
  color: var(--c-accent);
  font-weight: 500;
  margin-bottom: 14px;
}
.gp-detail-hero .desc {
  max-width: 680px;
  font-size: 15px;
  line-height: 1.7;
  color: var(--c-muted);
  margin-bottom: 24px;
}
.gp-detail-hero .price-hero {
  display: inline-flex; align-items: baseline; gap: 8px;
  font-family: var(--font-display);
  font-size: 42px; font-weight: 600;
  color: var(--c-strong);
}
.gp-detail-hero .price-hero-label {
  font-size: 14px; font-weight: 500; color: var(--c-pale);
  font-family: var(--font-body);
}
.gp-package-cart-form{margin-top:18px}
.gp-package-cart-btn{
  display:inline-flex;align-items:center;gap:8px;height:44px;padding:0 22px;border:0;border-radius:999px;
  background:var(--c-strong);color:#fff;font-size:13px;font-weight:800;cursor:pointer;
  box-shadow:0 14px 30px rgba(109,76,91,.18);transition:all .2s var(--ease-out-expo)
}
.gp-package-cart-btn:hover{background:#5a3d4a;transform:translateY(-1px)}

/* ─── HOW IT WORKS ───────────────────────── */
.gp-how-it-works {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
  margin-bottom: 48px;
  padding: 28px 0;
  border-top: 1px solid var(--c-rule);
  border-bottom: 1px solid var(--c-rule);
}
.gp-how-step {
  text-align: center;
  padding: 0 8px;
}
.gp-how-step-icon {
  display: grid; place-items: center;
  width: 48px; height: 48px; margin: 0 auto 12px;
  border-radius: 50%;
  background: var(--c-strong); color: #fff;
}
.gp-how-step h3 {
  font-size: 13px; font-weight: 700; color: var(--c-text);
  margin-bottom: 4px;
}
.gp-how-step p {
  font-size: 12px; color: var(--c-muted); line-height: 1.5;
}

/* ─── CATEGORY SECTION ───────────────────── */
.gp-cat-section {
  margin-bottom: 36px;
}
.gp-cat-section:last-child { margin-bottom: 0; }
.gp-cat-header {
  display: flex; align-items: center; justify-content: space-between;
  margin-bottom: 16px;
}
.gp-cat-header h2 {
  font-family: var(--font-display);
  font-size: 28px; font-weight: 600;
  color: var(--c-text);
}
.gp-cat-count {
  font-size: 12px; font-weight: 600; color: var(--c-muted);
}

/* ─── SERVICE CARDS ──────────────────────── */
.gp-svc-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 16px;
}
.gp-svc-card {
  background: var(--c-white);
  border: 1px solid var(--c-rule);
  border-radius: var(--r-card);
  overflow: hidden;
  box-shadow: var(--sh-card);
  transition: all 0.3s var(--ease-out-expo);
  display: flex; flex-direction: column;
}
.gp-svc-card:hover {
  transform: translateY(-3px);
  box-shadow: var(--sh-panel);
}
.gp-svc-img {
  display: block; position: relative;
  aspect-ratio: 4/3; overflow: hidden;
  background: linear-gradient(160deg, #ede0d0, #ddcebb);
  flex-shrink: 0;
}
.gp-svc-img img {
  position: absolute; inset: 0;
  width: 100%; height: 100%; object-fit: cover;
  transition: transform 0.6s var(--ease-out-expo);
}
.gp-svc-card:hover .gp-svc-img img { transform: scale(1.06); }
.gp-svc-img-placeholder {
  position: absolute; inset: 0;
  display: grid; place-items: center;
  color: var(--c-pale); opacity: 0.4;
}
.gp-svc-body {
  padding: 16px 18px 18px;
  flex: 1; display: flex; flex-direction: column;
}
.gp-svc-supplier {
  font-size: 11px; font-weight: 600; color: var(--c-muted);
  margin-bottom: 4px;
}
.gp-svc-name {
  font-family: var(--font-display);
  font-size: 18px; font-weight: 600; line-height: 1.1;
  color: var(--c-text); margin-bottom: 6px;
}
.gp-svc-desc {
  font-size: 12px; line-height: 1.5; color: var(--c-accent);
  display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
  overflow: hidden; flex: 1;
}
.gp-svc-rating {
  display: flex; align-items: center; gap: 4px;
  margin-top: 8px;
  font-size: 11px; font-weight: 700; color: #d4a047;
}
.gp-svc-foot {
  display: flex; align-items: center; justify-content: space-between; gap: 10px;
  margin-top: 10px; padding-top: 10px;
  border-top: 1px solid var(--c-rule);
}
.gp-svc-price {
  font-family: var(--font-display);
  font-size: 20px; font-weight: 600;
  color: var(--c-strong); line-height: 1;
}
.gp-svc-add-btn {
  display: inline-flex; align-items: center; gap: 4px;
  height: 34px; padding: 0 14px; border-radius: 999px;
  border: 1px solid var(--c-rule);
  background: var(--c-white); color: var(--c-strong);
  font-size: 12px; font-weight: 700; cursor: pointer;
  transition: all 0.2s var(--ease-out-expo); white-space: nowrap;
}
.gp-svc-add-btn:hover {
  background: var(--c-strong); color: #fff; border-color: var(--c-strong);
}

/* ─── EMPTY SERVICE ──────────────────────── */
.gp-cat-empty {
  border: 1px dashed var(--c-rule);
  border-radius: var(--r-card);
  padding: 32px 20px;
  text-align: center;
  color: var(--c-pale);
  font-size: 13px;
  grid-column: 1 / -1;
}

/* ─── FOOTER ─────────────────────────────── */
.gp-footer {
  padding: 28px var(--pad-x);
  border-top: 1px solid var(--c-rule);
  display: flex; align-items: center; justify-content: space-between; gap: 16px;
  font-size: 12px; color: var(--c-pale);
}

/* ─── REVEAL ─────────────────────────────── */
.gp-reveal {
  opacity: 0; transform: translateY(24px);
  transition: opacity 0.6s var(--ease-out-expo), transform 0.6s var(--ease-out-expo);
}
.gp-reveal.visible { opacity: 1; transform: translateY(0); }
.gp-reveal-d1 { transition-delay: 0.04s; }
.gp-reveal-d2 { transition-delay: 0.08s; }
.gp-reveal-d3 { transition-delay: 0.12s; }

@media (max-width: 900px) {
  .gp-svc-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
  .gp-how-it-works { grid-template-columns: 1fr; gap: 16px; }
}
@media (max-width: 700px) {
  .gp-svc-grid { grid-template-columns: 1fr; }
  .gp-header-nav a { display: none; }
  .gp-detail-page { padding-bottom: 40px; }
}
@media (max-width: 480px) {
  :root { --pad-x: 16px; }
}
</style>
</head>
<body>

<div class="gp-texture" aria-hidden="true"></div>

<!-- HEADER -->
<header class="gp-header">
  <a class="gp-brand" href="<?= URLROOT ?>/main/index">
    <span class="gp-brand-mark">G</span>
    <span>Golden Promise</span>
  </a>
  <nav class="gp-header-nav" aria-label="Main navigation">
    <a href="<?= URLROOT ?>/main/index">Home</a>
    <a href="<?= URLROOT ?>/customerServices/service">Services</a>
    <a href="<?= URLROOT ?>/customerServices/packages">Packages</a>
    <a class="active" href="#"><?= $h($package['name'] ?? '') ?></a>
  </nav>
  <div class="gp-header-actions">
    <a class="gp-cart-badge" href="<?= URLROOT ?>/cart" aria-label="Cart">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
      <?php if ($cartCount > 0): ?>
      <span class="gp-cart-badge-count"><?= $cartCount ?></span>
      <?php endif; ?>
    </a>
    <a class="gp-header-cta" href="<?= $authNavUrl ?>"><?= $authNavLabel ?></a>
  </div>
</header>

<main class="gp-detail-page">
  <!-- BREADCRUMB -->
  <nav class="gp-breadcrumb" aria-label="Breadcrumb">
    <a href="<?= URLROOT ?>/customerServices/packages">Packages</a>
    <span class="gp-breadcrumb-sep">/</span>
    <span style="color:var(--c-strong)"><?= $h($package['name'] ?? '') ?></span>
  </nav>

  <!-- HERO -->
  <section class="gp-detail-hero" aria-label="Package overview">
    <div class="gp-detail-hero-overline">Curated Wedding Package</div>
    <h1><?= $h($package['name'] ?? '') ?></h1>
    <?php if (!empty($package['tagline'])): ?>
      <p class="tagline"><?= $h($package['tagline']) ?></p>
    <?php endif; ?>
    <?php if (!empty($package['description'])): ?>
      <p class="desc"><?= $h($package['description']) ?></p>
    <?php endif; ?>
    <div class="price-hero">
      <?= $money($package['base_price'] ?? 0) ?>
      <span class="price-hero-label">package price</span>
    </div>
    <form class="gp-package-cart-form" method="POST" action="<?= URLROOT ?>/cart/addPackage">
      <input type="hidden" name="package_id" value="<?= (int)($package['package_id'] ?? 0) ?>">
      <input type="hidden" name="price" value="<?= (float)($package['base_price'] ?? 0) ?>">
      <button class="gp-package-cart-btn" type="submit">
        Add package to cart
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
      </button>
    </form>
  </section>

  <!-- HOW IT WORKS -->
  <section class="gp-how-it-works" aria-label="How this package works">
    <div class="gp-how-step">
      <div class="gp-how-step-icon">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
      </div>
      <h3>1. Review Included Services</h3>
      <p>See the supplier services already selected by Golden Promise for this package.</p>
    </div>
    <div class="gp-how-step">
      <div class="gp-how-step-icon">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
      </div>
      <h3>2. Compare the Value</h3>
      <p>Each package level includes different services based on the package price.</p>
    </div>
    <div class="gp-how-step">
      <div class="gp-how-step-icon">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
      </div>
      <h3>3. Book with Confidence</h3>
      <p>Add the package to your cart, check out, and your selected vendors will confirm.</p>
    </div>
  </section>

  <!-- CATEGORIES WITH SERVICES -->
  <?php if (empty($categoryServices)): ?>
    <section class="gp-cat-section" aria-label="No services available">
      <div class="gp-cat-empty" style="padding:48px;text-align:center">
        <p style="font-size:16px;color:var(--c-accent)">No services are currently included in this package. Check back soon!</p>
      </div>
    </section>
  <?php else: ?>
    <?php foreach ($categoryServices as $cs): ?>
    <section class="gp-cat-section gp-reveal" aria-label="<?= $h($cs['category_name'] ?? '') ?> services">
      <div class="gp-cat-header">
        <h2><?= $h($cs['category_name'] ?? '') ?></h2>
        <span class="gp-cat-count"><?= (int)$cs['service_count'] ?> service<?= (int)$cs['service_count'] === 1 ? '' : 's' ?> included</span>
      </div>

      <?php if (empty($cs['services'])): ?>
        <div class="gp-cat-empty">
          <p>No <?= $h(strtolower($cs['category_name'] ?? '')) ?> services are included yet.</p>
        </div>
      <?php else: ?>
        <div class="gp-svc-grid">
          <?php foreach ($cs['services'] as $si => $svc): ?>
          <article class="gp-svc-card gp-reveal gp-reveal-d<?= min($si % 4, 3) ?>">
            <?php $detailUrl = $serviceDetailUrl($svc); ?>
            <a class="gp-svc-img" href="<?= $h($detailUrl) ?>" tabindex="-1" aria-hidden="true">
              <?php if (!empty($svc['image'])): ?>
                <img src="<?= $h($svc['image']) ?>" alt="<?= $h($svc['name'] ?? '') ?>" loading="lazy">
              <?php else: ?>
                <div class="gp-svc-img-placeholder">
                  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                </div>
              <?php endif; ?>
            </a>
            <div class="gp-svc-body">
              <span class="gp-svc-supplier"><?= $h($svc['supplier_name'] ?? '') ?></span>
              <h3 class="gp-svc-name"><?= $h($svc['name'] ?? '') ?></h3>
              <p class="gp-svc-desc"><?= $h($svc['description'] ?? '') ?></p>
              <?php if ((float)($svc['rating'] ?? 0) > 0): ?>
              <div class="gp-svc-rating">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                <?= number_format((float)$svc['rating'], 1) ?>
                <span style="font-weight:400;opacity:0.6;font-size:10px;">(<?= (int)($svc['review_count'] ?? 0) ?>)</span>
              </div>
              <?php endif; ?>
              <div class="gp-svc-foot">
                <span class="gp-svc-price"><?= $moneyRange($svc) ?></span>
                <a class="gp-svc-add-btn" href="<?= $h($detailUrl) ?>">
                  View
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </a>
              </div>
            </div>
          </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>
    <?php endforeach; ?>
  <?php endif; ?>
</main>

<footer class="gp-footer">
  <span>&copy; <?= date('Y') ?> Golden Promise</span>
  <span>Every vendor is verified and reviewed for quality assurance.</span>
</footer>

<script>
document.addEventListener('DOMContentLoaded', () => {
  if (typeof lucide !== 'undefined') lucide.createIcons();
  const revealBoxes = document.querySelectorAll('.gp-reveal');
  if (revealBoxes.length && 'IntersectionObserver' in window) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.06, rootMargin: '0px 0px -40px 0px' });
    revealBoxes.forEach(el => observer.observe(el));
  } else {
    revealBoxes.forEach(el => el.classList.add('visible'));
  }
});
</script>
</body>
</html>
