<?php
$catalog = $catalog ?? ['services' => [], 'categories' => [], 'featured' => []];
$filters = $filters ?? ['search' => '', 'category' => 'all', 'sort' => 'featured', 'date' => '', 'price_min' => '', 'price_max' => ''];
$services = $catalog['services'] ?? [];
$categories = $catalog['categories'] ?? [];
$featured = $catalog['featured'] ?? [];

$hasActiveFilters = trim((string)($filters['search'] ?? '')) !== ''
    || !in_array(($filters['category'] ?? 'all'), ['', 'all'], true)
    || trim((string)($filters['date'] ?? '')) !== '';

if (empty($featured)) {
    $featured = array_slice($services, 0, 3);
}

$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$money = fn($v) => 'RM ' . number_format((float)$v, 0);
$moneyRange = function ($service) use ($money) {
    $min = (float)($service['price_min'] ?? $service['price'] ?? 0);
    $max = (float)($service['price_max'] ?? $min);
    return $max > $min ? $money($min) . ' - ' . $money($max) : $money($min);
};
$durationText = function ($service) {
    $type = $service['booking_type'] ?? 'fullday';
    $min = (int)($service['duration_minutes'] ?? 0);
    if ($type === 'slot' && $min > 0) {
        $h = $min / 60;
        return $h >= 1 ? rtrim(rtrim(number_format($h, 1), '0'), '.') . ' hr' : $min . ' min';
    }
    return $type === 'flexible' ? 'Flexible' : 'Full day';
};
$pricingUnit = function ($service) {
    $unit = $service['pricing_unit'] ?? 'per_session';
    return $unit === 'per_hour' ? '/hr' : '/session';
};

$activeCategory = $filters['category'] ?? 'all';
$activeSort     = $filters['sort'] ?? 'featured';
$activeDate     = $filters['date'] ?? '';
$activePriceMin = $filters['price_min'] ?? '';
$activePriceMax = $filters['price_max'] ?? '';
$detailDateQuery = $activeDate !== '' ? '?date=' . rawurlencode($activeDate) : '';
$isLoggedIn = !empty($_SESSION['session_uid']);
$authNavUrl = $isLoggedIn ? URLROOT . '/users/logout' : URLROOT . '/users/auth';
$authNavLabel = $isLoggedIn ? 'Logout' : 'Sign in';

$heroService = $featured[0] ?? $services[0] ?? null;

$totalServices = count($services);
$totalCategories = count($categories);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Services — Golden Promise</title>
<?php $v = file_exists(APPROOT . '/../public/css/app.css') ? filemtime(APPROOT . '/../public/css/app.css') : time(); ?>
<link rel="stylesheet" href="<?= URLROOT ?>/public/css/app.css?v=<?= $v ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=DM+Sans:wght@300;400;500;600;700&display=swap">
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
<style>
/* ─── RESET & TOKENS ─────────────────────────────────── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
  --c-bg:       #F5E8D9;
  --c-surface:  #FFF8EF;
  --c-cream:    #F8F2EC;
  --c-rule:     rgba(118,90,70,0.16);
  --c-wine:     #B94A48;
  --c-wine-mid: #7F2F2D;
  --c-wine-lt:  rgba(185,74,72,0.10);
  --c-ink:      #211D1A;
  --c-muted:    #6F625A;
  --c-pale:     rgba(74,52,47,0.55);
  --c-gold:     #D8B46A;

  --r-sm:  6px;
  --r-md:  12px;
  --r-lg:  18px;
  --r-xl:  24px;

  --sh-card: 0 18px 42px rgba(74,52,47,0.12), 0 4px 14px rgba(74,52,47,0.08);
  --sh-hero: 0 34px 82px rgba(54,35,28,0.22);

  --font-display: 'Playfair Display', Georgia, serif;
  --font-body:    'DM Sans', system-ui, -apple-system, sans-serif;

  --pad-x: clamp(20px, 5vw, 72px);
}

body {
  background: var(--c-bg);
  color: var(--c-ink);
  font-family: var(--font-body);
  font-size: 14px;
  line-height: 1.5;
  -webkit-font-smoothing: antialiased;
}

a { color: inherit; text-decoration: none; }
img { display: block; width: 100%; height: 100%; object-fit: cover; }
button, select, input { font-family: var(--font-body); }

/* ─── NAV ────────────────────────────────────────────── */
.gp-nav {
  position: sticky; top: 0; z-index: 40;
  display: flex; align-items: center; justify-content: space-between; gap: 16px;
  padding: 14px var(--pad-x);
  background: rgba(253,250,246,0.82);
  backdrop-filter: blur(20px);
  border-bottom: 1px solid var(--c-rule);
}

.gp-logo {
  display: flex; align-items: center; gap: 10px;
  font-family: var(--font-display);
  font-size: 22px; font-weight: 600;
  color: var(--c-wine);
  letter-spacing: 0.01em;
  white-space: nowrap;
}

.gp-logo-ring {
  width: 32px; height: 32px; border-radius: 50%;
  background: var(--c-wine); color: #fff;
  font-family: var(--font-display); font-size: 18px; font-style: italic;
  display: grid; place-items: center; flex-shrink: 0;
}

.gp-nav-links {
  display: flex; align-items: center; gap: 4px;
}

.gp-nav-link {
  padding: 8px 14px; border-radius: 99px;
  font-size: 13px; font-weight: 500; color: var(--c-muted);
  transition: color 0.15s, background 0.15s;
}
.gp-nav-link:hover, .gp-nav-link.active { color: var(--c-wine); background: var(--c-wine-lt); }

.gp-nav-cta {
  height: 38px; padding: 0 18px; border-radius: 99px; border: none;
  background: var(--c-wine); color: #fff;
  font-size: 13px; font-weight: 600; cursor: pointer;
  transition: background 0.15s, transform 0.15s;
}
.gp-nav-cta:hover { background: var(--c-wine-mid); transform: translateY(-1px); }

/* ─── HERO ───────────────────────────────────────────── */
.gp-hero {
  display: grid;
  grid-template-columns: 1fr 480px;
  gap: 40px;
  align-items: end;
  padding: 60px var(--pad-x) 0;
}

.gp-hero-left { padding-bottom: 40px; }

.gp-overline {
  display: inline-flex; align-items: center; gap: 8px;
  font-size: 11px; font-weight: 700; letter-spacing: 0.16em; text-transform: uppercase;
  color: var(--c-wine); margin-bottom: 16px;
}
.gp-overline::before {
  content: '';
  display: block; width: 20px; height: 1px; background: var(--c-wine); flex-shrink: 0;
}

.gp-hero-h1 {
  font-family: var(--font-display);
  font-size: clamp(48px, 6.5vw, 96px);
  font-weight: 400;
  line-height: 0.92;
  color: var(--c-ink);
  letter-spacing: -0.01em;
}
.gp-hero-h1 em { font-style: italic; color: var(--c-wine); }

.gp-hero-sub {
  margin-top: 20px;
  font-size: 15px; line-height: 1.7;
  color: var(--c-muted);
  max-width: 480px;
}

.gp-hero-stat-row {
  display: flex; gap: 28px; margin-top: 36px;
}

.gp-hero-stat strong {
  display: block;
  font-family: var(--font-display); font-size: 36px; font-weight: 600; color: var(--c-wine);
  line-height: 1;
}
.gp-hero-stat span { font-size: 12px; color: var(--c-pale); font-weight: 500; }

.gp-hero-img-wrap {
  position: relative; overflow: hidden;
  border-radius: var(--r-xl) var(--r-xl) 0 0;
  min-height: 480px;
  box-shadow: var(--sh-hero);
  background: linear-gradient(135deg, var(--c-cream), var(--c-surface));
}

.gp-hero-img-wrap img {
  position: absolute; inset: 0;
  width: 100%; height: 100%; object-fit: cover;
  transform: scale(1.02);
  transition: transform 6s ease;
}
.gp-hero-img-wrap:hover img { transform: scale(1.0); }

.gp-hero-img-placeholder {
  position: absolute; inset: 0;
  display: grid; place-items: center;
  color: var(--c-pale);
}

.gp-hero-img-overlay {
  position: absolute; inset: 0;
  background: rgba(33,29,26,0.24);
  z-index: 1;
  pointer-events: none;
}

.gp-hero-img-tag {
  position: absolute; bottom: 20px; left: 20px; z-index: 2;
  background: rgba(255,248,239,0.14);
  backdrop-filter: blur(12px);
  border: 1px solid rgba(255,255,255,0.22);
  border-radius: var(--r-md);
  padding: 10px 14px;
  color: #fff;
}
.gp-hero-img-tag strong { display: block; font-size: 15px; font-weight: 600; }
.gp-hero-img-tag span  { font-size: 12px; opacity: 0.72; }

/* ─── SEARCH BAR ─────────────────────────────────────── */
.gp-search-wrap {
  padding: 0 var(--pad-x) 36px;
  margin-top: -1px;
}

.gp-search-bar {
  display: grid;
  grid-template-columns: 1fr 1px 1fr 1px 1fr 1px 1fr auto;
  align-items: stretch;
  background: var(--c-surface);
  border: 1px solid var(--c-rule);
  border-radius: var(--r-lg);
  box-shadow: var(--sh-card);
  overflow: hidden;
}

.gp-search-divider {
  background: var(--c-rule); width: 1px; align-self: stretch; margin: 10px 0;
}

.gp-search-field {
  display: flex; flex-direction: column; justify-content: center;
  padding: 14px 20px;
  cursor: pointer;
  transition: background 0.15s;
}
.gp-search-field:hover { background: var(--c-cream); }
.gp-search-field label {
  font-size: 10px; font-weight: 700; letter-spacing: 0.1em;
  text-transform: uppercase; color: var(--c-wine); margin-bottom: 4px;
  cursor: pointer;
}
.gp-search-field input,
.gp-search-field select {
  border: none; outline: none; background: transparent;
  font-size: 14px; font-weight: 500; color: var(--c-ink);
  width: 100%; appearance: none; cursor: pointer;
}
.gp-search-field input::placeholder { color: var(--c-pale); font-weight: 400; }
.gp-search-field select { color: var(--c-ink); }

.gp-search-submit {
  display: flex; align-items: center; justify-content: center;
  padding: 0 28px;
  background: var(--c-wine); color: #fff; border: none;
  font-size: 13px; font-weight: 700; letter-spacing: 0.04em;
  cursor: pointer; gap: 8px; white-space: nowrap;
  transition: background 0.15s;
}
.gp-search-submit:hover { background: var(--c-wine-mid); }

/* ─── CATEGORY PILLS ─────────────────────────────────── */
.gp-cats {
  padding: 0 var(--pad-x) 28px;
  display: flex; gap: 8px; flex-wrap: wrap; align-items: center;
}

.gp-cat-pill {
  height: 34px; padding: 0 16px; border-radius: 99px;
  border: 1px solid var(--c-rule);
  background: var(--c-surface);
  font-size: 12px; font-weight: 600; color: var(--c-muted);
  cursor: pointer; transition: all 0.15s;
  white-space: nowrap;
  text-decoration: none; display: inline-flex; align-items: center;
}
.gp-cat-pill:hover { border-color: var(--c-wine); color: var(--c-wine); background: var(--c-wine-lt); }
.gp-cat-pill.active { background: var(--c-wine); color: #fff; border-color: var(--c-wine); }

/* ─── SECTION SHELL ──────────────────────────────────── */
.gp-section { padding: 0 var(--pad-x) 48px; }

.gp-section-head {
  display: flex; align-items: baseline; justify-content: space-between; gap: 16px;
  margin-bottom: 22px;
}
.gp-section-title {
  font-family: var(--font-display);
  font-size: clamp(30px, 3.5vw, 44px); font-weight: 400;
  color: var(--c-ink); line-height: 1;
}
.gp-section-count {
  font-size: 12px; font-weight: 600; color: var(--c-pale);
}

/* ─── FEATURED STRIP ─────────────────────────────────── */
.gp-featured-grid {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr;
  grid-template-rows: 280px;
  gap: 12px;
}

.gp-feat-card {
  position: relative; overflow: hidden;
  border-radius: var(--r-lg);
  background: linear-gradient(135deg, var(--c-cream), var(--c-surface));
}
.gp-feat-card:first-child { grid-row: span 1; }

.gp-feat-card img {
  position: absolute; inset: 0;
  width: 100%; height: 100%; object-fit: cover;
  transition: transform 0.5s ease;
}
.gp-feat-card:hover img { transform: scale(1.04); }

.gp-feat-card::after {
  content: '';
  position: absolute; inset: 0;
  background: rgba(33,29,26,0.42);
}

.gp-feat-card .gp-feat-img-placeholder {
  position: absolute; inset: 0;
  display: grid; place-items: center;
  color: var(--c-pale);
}

.gp-feat-info {
  position: absolute; bottom: 16px; left: 16px; right: 16px; z-index: 2;
  color: #fff;
}
.gp-feat-cat {
  font-size: 10px; font-weight: 700; letter-spacing: 0.14em; text-transform: uppercase;
  opacity: 0.75; margin-bottom: 4px;
}
.gp-feat-name {
  font-family: var(--font-display);
  font-size: 22px; font-weight: 600; line-height: 1.1;
}
.gp-feat-card:first-child .gp-feat-name { font-size: 32px; }
.gp-feat-price {
  margin-top: 8px; font-size: 13px; font-weight: 600;
  background: rgba(255,255,255,0.15);
  backdrop-filter: blur(8px);
  border: 1px solid rgba(255,255,255,0.2);
  border-radius: 99px; display: inline-block;
  padding: 3px 10px;
}

/* ─── SERVICES GRID ──────────────────────────────────── */
.gp-services-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 20px;
}

.gp-svc-card {
  background: var(--c-surface);
  border: 1px solid var(--c-rule);
  border-radius: var(--r-xl);
  overflow: hidden;
  box-shadow: var(--sh-card);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  display: flex; flex-direction: column;
}
.gp-svc-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 6px 24px rgba(40,18,24,0.10), 0 16px 48px rgba(40,18,24,0.08);
}

.gp-svc-img {
  display: block; position: relative;
  aspect-ratio: 16/10; overflow: hidden;
  background: linear-gradient(135deg, var(--c-cream), var(--c-surface));
  flex-shrink: 0;
}
.gp-svc-img img { transition: transform 0.5s ease; }
.gp-svc-card:hover .gp-svc-img img { transform: scale(1.05); }

.gp-svc-img-placeholder {
  position: absolute; inset: 0;
  display: grid; place-items: center;
  color: var(--c-pale);
  font-size: 28px;
}

.gp-svc-badge {
  position: absolute; top: 12px; left: 12px;
  background: rgba(255,248,239,0.92);
  backdrop-filter: blur(8px);
  border: 1px solid rgba(185,74,72,0.16);
  border-radius: 99px;
  padding: 4px 11px;
  font-size: 11px; font-weight: 700; color: var(--c-wine);
  letter-spacing: 0.05em;
}

.gp-svc-body {
  padding: 18px 20px 20px;
  flex: 1; display: flex; flex-direction: column;
}

.gp-svc-topline {
  display: flex; align-items: center; justify-content: space-between; gap: 8px;
  margin-bottom: 10px;
}
.gp-svc-supplier {
  font-size: 12px; font-weight: 600; color: var(--c-pale);
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.gp-svc-rating {
  display: flex; align-items: center; gap: 4px;
  font-size: 12px; font-weight: 700; color: var(--c-gold); white-space: nowrap; flex-shrink: 0;
}
.gp-svc-rating svg { width: 12px; height: 12px; fill: var(--c-gold); }

.gp-svc-name {
  font-family: var(--font-display);
  font-size: 22px; font-weight: 600; line-height: 1.1;
  color: var(--c-ink); margin-bottom: 8px;
}

.gp-svc-desc {
  font-size: 13px; line-height: 1.6; color: var(--c-muted);
  display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
  overflow: hidden;
  flex: 1;
}

.gp-svc-foot {
  display: flex; align-items: center; justify-content: space-between; gap: 12px;
  margin-top: 16px; padding-top: 14px;
  border-top: 1px solid var(--c-rule);
}

.gp-svc-price {
  display: flex; flex-direction: column; gap: 1px;
}
.gp-svc-price strong {
  font-family: var(--font-display); font-size: 26px; font-weight: 600;
  color: var(--c-wine); line-height: 1;
}
.gp-svc-price span {
  font-size: 11px; color: var(--c-pale); font-weight: 500;
}

.gp-svc-btn {
  height: 38px; padding: 0 18px; border-radius: 99px; border: 1px solid var(--c-rule);
  background: transparent; color: var(--c-wine);
  font-size: 12px; font-weight: 700; cursor: pointer;
  transition: all 0.15s; white-space: nowrap;
  text-decoration: none; display: inline-flex; align-items: center; gap: 6px;
}
.gp-svc-btn:hover { background: var(--c-wine); color: #fff; border-color: var(--c-wine); }

/* ─── EMPTY STATE ────────────────────────────────────── */
.gp-empty {
  border: 1px dashed rgba(185,74,72,0.22);
  border-radius: var(--r-xl);
  padding: 64px 24px;
  text-align: center;
  background: rgba(255,248,239,0.72);
}
.gp-empty-icon {
  display: inline-flex; align-items: center; justify-content: center;
  width: 64px; height: 64px; border-radius: 50%;
  background: var(--c-wine-lt);
  color: var(--c-wine);
  margin-bottom: 20px;
}
.gp-empty h3 {
  font-family: var(--font-display); font-size: 32px; font-weight: 500; color: var(--c-ink);
  margin-bottom: 8px;
}
.gp-empty p { color: var(--c-muted); font-size: 14px; line-height: 1.7; max-width: 480px; margin: 0 auto; }
.gp-empty-actions { margin-top: 24px; display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; }
.gp-empty-btn {
  display: inline-flex; align-items: center; gap: 6px;
  height: 40px; padding: 0 20px; border-radius: 99px;
  font-size: 13px; font-weight: 600; cursor: pointer; border: none;
  transition: background 0.15s;
}
.gp-empty-btn.primary { background: var(--c-wine); color: #fff; }
.gp-empty-btn.primary:hover { background: var(--c-wine-mid); }
.gp-empty-btn.secondary { background: var(--c-cream); color: var(--c-muted); border: 1px solid var(--c-rule); }
.gp-empty-btn.secondary:hover { border-color: var(--c-wine); color: var(--c-wine); }

/* ─── FOOTER ─────────────────────────────────────────── */
.gp-footer {
  padding: 24px var(--pad-x);
  border-top: 1px solid var(--c-rule);
  display: flex; align-items: center; justify-content: space-between; gap: 16px;
  font-size: 12px; color: var(--c-pale);
}

/* ─── RESPONSIVE ─────────────────────────────────────── */
@media (max-width: 1024px) {
  .gp-hero { grid-template-columns: 1fr; }
  .gp-hero-img-wrap { min-height: 380px; border-radius: var(--r-xl); }
  .gp-featured-grid { grid-template-columns: 1fr 1fr; grid-template-rows: 240px 240px; }
  .gp-feat-card:first-child { grid-column: span 2; }
  .gp-services-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}

@media (max-width: 768px) {
  .gp-search-bar {
    grid-template-columns: 1fr;
    border-radius: var(--r-md);
  }
  .gp-search-divider { display: none; }
  .gp-search-field { border-bottom: 1px solid var(--c-rule); padding: 12px 16px; }
  .gp-search-submit { padding: 16px; border-radius: 0; }
  .gp-featured-grid { grid-template-columns: 1fr; grid-template-rows: auto; }
  .gp-feat-card { height: 220px; }
  .gp-feat-card:first-child { grid-column: auto; height: 260px; }
  .gp-services-grid { grid-template-columns: 1fr; }
  .gp-nav-links .gp-nav-link { display: none; }
  .gp-hero { padding-top: 32px; }
  .gp-hero-stat-row { gap: 20px; }
}
</style>
</head>
<body>

<!-- NAV -->
<header class="gp-nav">
  <a class="gp-logo" href="<?= URLROOT ?>/main/home">
    <span class="gp-logo-ring">G</span>
    Golden Promise
  </a>
  <nav class="gp-nav-links" aria-label="Main navigation">
    <a class="gp-nav-link" href="<?= URLROOT ?>/main/home">Home</a>
    <a class="gp-nav-link active" href="<?= URLROOT ?>/customerServices/service">Services</a>
    <a class="gp-nav-link" href="<?= URLROOT ?>/main/package">Packages</a>
  </nav>
  <a class="gp-nav-cta" href="<?= $authNavUrl ?>"><?= $authNavLabel ?></a>
</header>

b
<main>



  <!-- SEARCH BAR -->
  <section class="gp-search-wrap mt-10" aria-label="Search and filter">
    <form class="gp-search-bar" method="GET" action="<?= URLROOT ?>/customerServices/service">
      <div class="gp-search-field">
        <label for="q">What are you looking for?</label>
        <input id="q" type="search" name="q" value="<?= $h($filters['search'] ?? '') ?>" placeholder="Photography, florals, styling…">
      </div>
      <div class="gp-search-divider" aria-hidden="true"></div>
      <div class="gp-search-field">
        <label for="f-date">Wedding date</label>
        <?php $todayDate = date('Y-m-d'); $maxDate = date('Y-m-d', strtotime('+365 days')); ?>
        <input id="f-date" type="date" name="date" value="<?= $h($activeDate) ?>"
               min="<?= $todayDate ?>" max="<?= $maxDate ?>">
      </div>
      <div class="gp-search-divider" aria-hidden="true"></div>
      <div class="gp-search-field">
        <label for="f-price-min">Budget range (RM)</label>
        <div style="display:flex;align-items:center;gap:6px;">
          <input id="f-price-min" type="number" name="price_min" min="0" step="50" value="<?= $h($activePriceMin) ?>" placeholder="Min" style="width:72px;">
          <span style="color:var(--c-pale); font-size:13px;">–</span>
          <input type="number" name="price_max" min="0" step="50" value="<?= $h($activePriceMax) ?>" placeholder="Max" style="width:72px;">
        </div>
      </div>
      <div class="gp-search-divider" aria-hidden="true"></div>
      <div class="gp-search-field">
        <label for="f-sort">Sort by</label>
        <select id="f-sort" name="sort">
          <option value="featured" <?= $activeSort === 'featured' ? 'selected' : '' ?>>Featured</option>
          <option value="price_low" <?= $activeSort === 'price_low' ? 'selected' : '' ?>>Price: low first</option>
          <option value="price_high" <?= $activeSort === 'price_high' ? 'selected' : '' ?>>Price: high first</option>
          <option value="newest" <?= $activeSort === 'newest' ? 'selected' : '' ?>>Newest</option>
          <option value="rating" <?= $activeSort === 'rating' ? 'selected' : '' ?>>Top rated</option>
        </select>
      </div>
      <button class="gp-search-submit" type="submit">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        Search
      </button>
    </form>
  </section>

  <!-- CATEGORY PILLS -->
  <?php if (!empty($categories)): ?>
  <div class="gp-cats" role="list" aria-label="Filter by category">
    <?php $dateQuery = $activeDate !== '' ? '&date=' . $h($activeDate) : ''; ?>
    <a class="gp-cat-pill <?= $activeCategory === 'all' ? 'active' : '' ?>"
       href="<?= URLROOT ?>/customerServices/service?category=all<?= $dateQuery ?>"
       role="listitem">All</a>
    <?php foreach ($categories as $cat):
      $slug = $cat['slug'] ?? strtolower($cat['name']);
      $isActive = $activeCategory === $slug || $activeCategory === ($cat['name'] ?? '');
    ?>
      <a class="gp-cat-pill <?= $isActive ? 'active' : '' ?>"
         href="<?= URLROOT ?>/customerServices/service?category=<?= $h($slug) ?><?= $dateQuery ?>"
         role="listitem">
        <?= $h($cat['name'] ?? '') ?>
        <?php if (!empty($cat['service_count'])): ?><span style="opacity:0.65;margin-left:4px;">(<?= (int)$cat['service_count'] ?>)</span><?php endif; ?>
      </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>



  <!-- ALL SERVICES -->
  <section class="gp-section" aria-label="All services">
    <div class="gp-section-head">
      <h2 class="gp-section-title">All Services</h2>
      <span class="gp-section-count">
        <?= count($services) ?> result<?= count($services) === 1 ? '' : 's' ?>
        <?php if (($filters['search'] ?? '') !== ''): ?> for "<?= $h($filters['search']) ?>"<?php endif; ?>
        <?php if ($activeDate !== ''): ?> on <?= $h(date('M j, Y', strtotime($activeDate))) ?><?php endif; ?>
      </span>
    </div>

    <?php if (empty($services)): ?>
      <div class="gp-empty">
        <div class="gp-empty-icon"><i data-lucide="search-x" size="28"></i></div>
        <h3>No services found</h3>
        <p>We couldn't find any services matching your criteria. Try adjusting your search, date, or budget.</p>
        <div class="gp-empty-actions">
          <a class="gp-empty-btn primary" href="<?= URLROOT ?>/customerServices/service">Clear all filters</a>
          <a class="gp-empty-btn secondary" href="<?= URLROOT ?>/customerServices/service?category=all">Browse all services</a>
        </div>
      </div>
    <?php else: ?>
      <div class="gp-services-grid">
        <?php foreach ($services as $i => $svc): ?>
        <?php $detailUrl = URLROOT . '/customerServices/detail/' . (int)$svc['id'] . $detailDateQuery; ?>
        <article class="gp-svc-card">
          <a class="gp-svc-img" href="<?= $h($detailUrl) ?>">
            <?php if (trim((string)($svc['image'] ?? '')) !== ''): ?>
              <img src="<?= $h($svc['image']) ?>" alt="<?= $h($svc['name'] ?? '') ?>" loading="lazy">
            <?php else: ?>
              <div class="gp-svc-img-placeholder">
                <i data-lucide="image" size="28"></i>
              </div>
            <?php endif; ?>
            <span class="gp-svc-badge"><?= $h($svc['category'] ?? 'Service') ?></span>
          </a>
          <div class="gp-svc-body">
            <div class="gp-svc-topline">
              <span class="gp-svc-supplier" title="<?= $h($svc['supplier_name'] ?? '') ?>">
                <i data-lucide="store" size="11" style="margin-right:3px;opacity:0.6;"></i>
                <?= $h($svc['supplier_name'] ?? 'Supplier') ?>
              </span>
              <?php if ((float)($svc['rating'] ?? 0) > 0): ?>
              <div class="gp-svc-rating" title="<?= (int)($svc['review_count'] ?? 0) ?> review<?= (int)($svc['review_count'] ?? 0) === 1 ? '' : 's' ?>">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                <?= number_format((float)$svc['rating'], 1) ?>
              </div>
              <?php endif; ?>
            </div>
            <h3 class="gp-svc-name">
              <a href="<?= $h($detailUrl) ?>"><?= $h($svc['name'] ?? '') ?></a>
            </h3>
            <p class="gp-svc-desc"><?= $h($svc['description'] ?? '') ?></p>
            <div class="gp-svc-foot">
              <div class="gp-svc-price">
                <strong><?= $moneyRange($svc) ?></strong>
                <span><?= $h($durationText($svc)) ?> <?= $pricingUnit($svc) ?></span>
              </div>
              <a class="gp-svc-btn" href="<?= $h($detailUrl) ?>">
                View details
                <i data-lucide="arrow-right" size="14"></i>
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
  <span>Services are listed after supplier approval and payment verification.</span>
</footer>

<script>
document.addEventListener('DOMContentLoaded', () => {
  if (typeof lucide !== 'undefined') lucide.createIcons();
});
</script>
</body>
</html>
