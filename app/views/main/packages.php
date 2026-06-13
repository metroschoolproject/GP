<?php
$packages = $packages ?? [];
$cartCount = (int)($cartCount ?? 0);

$isLoggedIn = !empty($_SESSION['session_uid']);
$authNavUrl = $isLoggedIn ? URLROOT . '/users/logout' : URLROOT . '/users/auth';
$authNavLabel = $isLoggedIn ? 'Logout' : 'Sign in';

$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$money = fn($v) => 'MMK ' . number_format((float)$v, 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Packages — Golden Promise</title>
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
  font-size: 18px;
  font-weight: 800;
  white-space: nowrap;
}
.gp-brand-mark {
  display: grid; place-items: center;
  width: 40px; height: 40px;
  border-radius: 50%;
  background: var(--c-strong);
  color: #fffaf3;
  font-size: 14px;
  letter-spacing: 1px;
}
.gp-header-nav {
  display: flex; align-items: center; justify-content: center; gap: 4px;
}
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
  position: relative;
  padding: 8px 14px 8px 10px;
  border-radius: 999px;
  border: 1px solid var(--c-rule);
  background: var(--c-white);
  color: var(--c-strong);
  font-size: 13px; font-weight: 700;
  transition: all 0.2s;
}
.gp-cart-badge:hover { border-color: var(--c-strong); background: rgba(109,76,91,0.06); }
.gp-cart-badge-count {
  display: inline-flex; align-items: center; justify-content: center;
  min-width: 20px; height: 20px; padding: 0 6px;
  border-radius: 999px;
  background: var(--c-strong);
  color: #fff;
  font-size: 10px; font-weight: 700;
}
.gp-header-cta {
  display: inline-flex; align-items: center; justify-content: center;
  min-height: 40px; padding: 0 20px; border-radius: 999px; border: none;
  background: var(--c-strong); color: #fffaf3;
  font-size: 13px; font-weight: 800;
  cursor: pointer;
  box-shadow: 0 14px 30px rgba(109,76,91,0.18);
  transition: all 0.2s;
}
.gp-header-cta:hover { background: #5a3d4a; transform: translateY(-1px); }

/* ─── HERO ───────────────────────────────── */
.gp-pkg-hero {
  padding: 80px var(--pad-x) 60px;
  text-align: center;
}
.gp-pkg-hero-overline {
  display: inline-flex; align-items: center; gap: 12px;
  font-size: 12px; font-weight: 800; letter-spacing: 0.16em; text-transform: uppercase;
  color: var(--c-danger); margin-bottom: 16px;
}
.gp-pkg-hero-overline::before, .gp-pkg-hero-overline::after {
  content: '';
  display: block; width: 28px; height: 1.5px; background: var(--c-danger);
}
.gp-pkg-hero h1 {
  font-family: var(--font-display);
  font-size: clamp(48px, 6vw, 86px);
  font-weight: 600;
  line-height: 0.92;
  color: var(--c-text);
  letter-spacing: -0.02em;
}
.gp-pkg-hero h1 em { font-style: italic; color: var(--c-strong); }
.gp-pkg-hero p {
  max-width: 720px;
  margin: 20px auto 0;
  font-size: 16px;
  line-height: 1.7;
  color: var(--c-muted);
}

/* ─── PACKAGE GRID ───────────────────────── */
.gp-pkg-section {
  padding: 0 var(--pad-x) 72px;
}
.gp-pkg-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 24px;
}
.gp-pkg-card {
  background: var(--c-card);
  border: 1px solid var(--c-rule);
  border-radius: var(--r-card);
  overflow: hidden;
  box-shadow: var(--sh-card);
  transition: all 0.35s var(--ease-out-expo);
  display: flex; flex-direction: column;
}
.gp-pkg-card:hover {
  transform: translateY(-4px);
  box-shadow: var(--sh-panel);
}
.gp-pkg-visual {
  display: block; position: relative;
  aspect-ratio: 16/9; overflow: hidden;
  background: linear-gradient(160deg, #ede0d0, #ddcebb);
  flex-shrink: 0;
}
.gp-pkg-visual .gp-pkg-badge {
  position: absolute; top: 12px; left: 12px; z-index: 2;
  background: rgba(255,250,246,0.88);
  backdrop-filter: blur(6px);
  border: 1px solid rgba(109,76,91,0.10);
  border-radius: 999px;
  padding: 4px 10px;
  font-size: 10px; font-weight: 700; color: var(--c-strong);
  letter-spacing: 0.04em;
  text-transform: uppercase;
}
.gp-pkg-body {
  padding: 22px 22px 24px;
  flex: 1; display: flex; flex-direction: column;
}
.gp-pkg-name {
  font-family: var(--font-display);
  font-size: 24px; font-weight: 600; line-height: 1.1;
  color: var(--c-text); margin-bottom: 4px;
}
.gp-pkg-tagline {
  font-size: 13px;
  color: var(--c-accent);
  line-height: 1.5;
  margin-bottom: 12px;
  flex: 1;
}
.gp-pkg-cats {
  display: flex; gap: 6px; flex-wrap: wrap;
  margin-bottom: 14px;
}
.gp-pkg-cat-pill {
  padding: 3px 10px;
  border-radius: 999px;
  background: rgba(109,76,91,0.08);
  font-size: 10px; font-weight: 700; color: var(--c-strong);
}
.gp-pkg-foot {
  display: flex; align-items: center; justify-content: space-between; gap: 12px;
  margin-top: auto; padding-top: 14px;
  border-top: 1px solid var(--c-rule);
}
.gp-pkg-price {
  font-family: var(--font-display);
  font-size: 28px; font-weight: 600;
  color: var(--c-strong); line-height: 1;
}
.gp-pkg-price-label {
  display: block; margin-top: 1px;
  font-size: 11px; color: var(--c-pale); font-weight: 500;
}
.gp-pkg-btn {
  display: inline-flex; align-items: center; gap: 6px;
  height: 40px; padding: 0 20px; border-radius: 999px;
  border: 1px solid var(--c-rule);
  background: var(--c-white); color: var(--c-strong);
  font-size: 13px; font-weight: 700; cursor: pointer;
  transition: all 0.2s var(--ease-out-expo); white-space: nowrap;
  text-decoration: none;
}
.gp-pkg-btn:hover {
  background: var(--c-strong); color: #fff; border-color: var(--c-strong);
  transform: translateX(2px);
  box-shadow: 0 2px 10px rgba(109,76,91,0.18);
}

/* ─── EMPTY ──────────────────────────────── */
.gp-empty {
  grid-column: 1 / -1;
  border: 1px dashed rgba(109,76,91,0.18);
  border-radius: var(--r-card);
  padding: 64px 24px;
  text-align: center;
  background: rgba(250,245,239,0.60);
}
.gp-empty h3 {
  font-family: var(--font-display); font-size: 32px; font-weight: 600;
  margin-bottom: 8px;
}
.gp-empty p { color: var(--c-accent); max-width: 480px; margin: 0 auto; }

/* ─── FOOTER ─────────────────────────────── */
.gp-footer {
  padding: 28px var(--pad-x);
  border-top: 1px solid var(--c-rule);
  display: flex; align-items: center; justify-content: space-between; gap: 16px;
  font-size: 12px; color: var(--c-pale);
}

/* ─── SCROLL REVEAL ──────────────────────── */
.gp-reveal {
  opacity: 0; transform: translateY(30px);
  transition: opacity 0.7s var(--ease-out-expo), transform 0.7s var(--ease-out-expo);
}
.gp-reveal.visible { opacity: 1; transform: translateY(0); }
.gp-reveal-d1 { transition-delay: 0.04s; }
.gp-reveal-d2 { transition-delay: 0.10s; }
.gp-reveal-d3 { transition-delay: 0.18s; }
.gp-reveal-d4 { transition-delay: 0.26s; }
.gp-reveal-d5 { transition-delay: 0.34s; }

@media (max-width: 900px) {
  .gp-pkg-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
}
@media (max-width: 700px) {
  .gp-pkg-grid { grid-template-columns: 1fr; }
  .gp-header-nav a { display: none; }
  .gp-pkg-hero { padding: 48px var(--pad-x) 36px; }
  .gp-pkg-section { padding-bottom: 40px; }
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
    <a class="active" href="<?= URLROOT ?>/customerServices/packages">Packages</a>
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

<main>
  <!-- HERO -->
  <section class="gp-pkg-hero" aria-label="Curated packages">
    <div class="gp-pkg-hero-overline">Wedding Package Collections</div>
    <h1>Curated <em>Packages</em></h1>
    <p>Hand-picked bundles of wedding services designed to make your planning effortless. Each package type brings together the best vendors in their category.</p>
  </section>

  <!-- PACKAGE GRID -->
  <section class="gp-pkg-section" aria-label="Package types">
    <?php if (empty($packages)): ?>
      <div class="gp-pkg-grid">
        <div class="gp-empty">
          <h3>Packages coming soon</h3>
          <p>We're curating our wedding packages. Check back soon!</p>
        </div>
      </div>
    <?php else: ?>
      <div class="gp-pkg-grid">
        <?php foreach ($packages as $i => $pkg):
          $revealClass = 'gp-reveal gp-reveal-d' . min($i % 6, 5);
        ?>
        <article class="gp-pkg-card <?= $revealClass ?>">
          <a class="gp-pkg-visual" href="<?= URLROOT ?>/customerServices/packageDetail/<?= $h($pkg['slug']) ?>" tabindex="-1" aria-hidden="true">
            <span class="gp-pkg-badge"><?= (int)($pkg['item_count'] ?? 0) ?> service types</span>
          </a>
          <div class="gp-pkg-body">
            <h2 class="gp-pkg-name"><?= $h($pkg['name'] ?? '') ?></h2>
            <p class="gp-pkg-tagline"><?= $h($pkg['tagline'] ?? $pkg['description'] ?? '') ?></p>
            <div class="gp-pkg-cats">
              <?php foreach (($pkg['categories'] ?? []) as $cat): ?>
                <span class="gp-pkg-cat-pill"><?= $h($cat['category_name'] ?? '') ?></span>
              <?php endforeach; ?>
            </div>
            <div class="gp-pkg-foot">
              <div>
                <span class="gp-pkg-price"><?= $money($pkg['base_price'] ?? 0) ?></span>
                <span class="gp-pkg-price-label">Starting from</span>
              </div>
              <a class="gp-pkg-btn" href="<?= URLROOT ?>/customerServices/packageDetail/<?= $h($pkg['slug']) ?>">
                Explore
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
              </a>
            </div>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>
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
