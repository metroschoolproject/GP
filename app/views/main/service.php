<?php
$catalog = $catalog ?? ['services' => [], 'categories' => [], 'featured' => []];
$filters = $filters ?? ['search' => '', 'category' => 'all', 'sort' => 'featured', 'date' => '', 'price_min' => '', 'price_max' => ''];
$services = $catalog['services'] ?? [];
$categories = $catalog['categories'] ?? [];
$featured = $catalog['featured'] ?? [];

$hasActiveFilters = trim((string)($filters['search'] ?? '')) !== ''
    || !in_array(($filters['category'] ?? 'all'), ['', 'all'], true)
    || trim((string)($filters['date'] ?? '')) !== ''
    || trim((string)($filters['price_min'] ?? '')) !== ''
    || trim((string)($filters['price_max'] ?? '')) !== '';

if (empty($featured)) {
    $featured = array_slice($services, 0, 3);
}

$plain = function ($v) {
    $text = (string)$v;
    for ($i = 0; $i < 10; $i++) {
        $decoded = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if ($decoded === $text) {
            break;
        }
        $text = $decoded;
    }

    return $text;
};
$h = fn($v) => htmlspecialchars($plain($v), ENT_QUOTES, 'UTF-8');
$money = fn($v) => 'RM ' . number_format((float)$v, 0);
$moneyRange = function ($service) use ($money) {
    $min = (float)($service['price_min'] ?? $service['price'] ?? 0);
    $max = (float)($service['price_max'] ?? $min);
    return $max > $min ? $money($min) . ' — ' . $money($max) : $money($min);
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

// Cart count passed from controller or default to 0
$cartCount = (int)($cartCount ?? 0);

$heroService = $featured[0] ?? $services[0] ?? null;

$totalServices = count($services);
$totalCategories = count($categories);

// Build filter reset URL
$resetUrl = URLROOT . '/customerServices/service';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Services — Golden Promise</title>
<?php
$publicCssVersion = file_exists(APPROOT . '/../public/css/app.css') ? filemtime(APPROOT . '/../public/css/app.css') : time();
$indexCssVersion = file_exists(APPROOT . '/../public/css/index.css') ? filemtime(APPROOT . '/../public/css/index.css') : time();
?>
<link rel="stylesheet" href="<?= URLROOT ?>/public/css/app.css?v=<?= $publicCssVersion ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
<style>
/* ═══════════════════════════════════════════════════════════
   GOLDEN PROMISE — SERVICES DIRECTORY
   Using app-* tokens from tailwind.config.js
   ═══════════════════════════════════════════════════════════ */

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
  --r-field: 0.5rem;

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
  -moz-osx-font-smoothing: grayscale;
}

a { color: inherit; text-decoration: none; }
img { display: block; max-width: 100%; }
button, input, select, textarea { font-family: var(--font-body); outline: none; }

/* ─── DECORATIVE TEXTURE ─────────────────────────────── */
.gp-texture {
  position: fixed; inset: 0; z-index: -1; pointer-events: none;
  background-image:
    radial-gradient(ellipse at 20% 8%, rgba(109,76,91,0.04) 0%, transparent 60%),
    radial-gradient(ellipse at 80% 92%, rgba(183,156,139,0.07) 0%, transparent 55%);
}

/* ─── HEADER (matching home page) ────────────────────── */
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
  -webkit-backdrop-filter: blur(18px);
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

.gp-header-actions {
  display: flex; align-items: center; gap: 12px; justify-content: flex-end;
}

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
  line-height: 1;
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

/* ─── HERO ───────────────────────────────────────────── */
.gp-hero {
  position: relative;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0;
  min-height: 75vh;
  padding: 0 var(--pad-x);
  overflow: hidden;
}

.gp-hero-col {
  display: flex; flex-direction: column; justify-content: center;
  padding: 80px 60px 80px 0;
}

.gp-hero-overline {
  display: inline-flex; align-items: center; gap: 12px;
  font-size: 12px; font-weight: 800; letter-spacing: 0.16em; text-transform: uppercase;
  color: var(--c-danger); margin-bottom: 20px;
}
.gp-hero-overline::before {
  content: '';
  display: block; width: 28px; height: 1.5px; background: var(--c-danger); flex-shrink: 0;
}

.gp-hero-h1 {
  font-family: var(--font-display);
  font-size: clamp(44px, 6vw, 86px);
  font-weight: 600;
  line-height: 0.92;
  color: var(--c-text);
  letter-spacing: -0.02em;
}
.gp-hero-h1 em { font-style: italic; color: var(--c-strong); }

.gp-hero-sub {
  margin-top: 22px;
  font-size: 16px; line-height: 1.7;
  color: var(--c-muted);
  max-width: 440px;
}

.gp-hero-stats {
  display: flex; gap: 36px; margin-top: 36px;
}

.gp-hero-stat + .gp-hero-stat {
  position: relative; padding-left: 36px;
}
.gp-hero-stat + .gp-hero-stat::before {
  content: '';
  position: absolute; left: 0; top: 6px; bottom: 6px;
  width: 1px; background: var(--c-rule);
}

.gp-hero-stat-num {
  font-family: var(--font-display);
  font-size: 40px; font-weight: 600;
  color: var(--c-strong);
  line-height: 1;
}
.gp-hero-stat-label {
  display: block; margin-top: 4px;
  font-size: 12px; color: var(--c-pale); font-weight: 500;
}

.gp-hero-visual {
  position: relative;
  display: flex; align-items: flex-end;
  padding: 40px 0 40px 40px;
}

.gp-hero-frame {
  position: relative;
  width: 100%; height: 70vh;
  min-height: 480px;
  border-radius: var(--r-card) var(--r-card) 0 0;
  overflow: hidden;
  box-shadow: var(--sh-card);
  background: linear-gradient(160deg, #ede0d0 0%, #ddcebb 100%);
}

.gp-hero-frame img {
  position: absolute; inset: 0;
  width: 100%; height: 100%; object-fit: cover;
  transition: transform 6s var(--ease-out-expo);
}
.gp-hero-frame:hover img { transform: scale(1.03); }

.gp-hero-frame-overlay {
  position: absolute; inset: 0;
  background: linear-gradient(to top, rgba(31,26,23,0.45) 0%, transparent 50%);
  z-index: 1; pointer-events: none;
}

.gp-hero-frame-tag {
  position: absolute; bottom: 24px; left: 24px; right: 24px; z-index: 2;
  display: flex; align-items: center; gap: 14px;
  background: rgba(255,250,246,0.14);
  backdrop-filter: blur(14px);
  border: 1px solid rgba(255,255,255,0.16);
  border-radius: var(--r-card);
  padding: 14px 18px;
  color: #fff;
}
.gp-hero-frame-tag-icon {
  width: 42px; height: 42px; border-radius: 50%;
  background: rgba(255,255,255,0.10);
  display: grid; place-items: center; flex-shrink: 0;
}
.gp-hero-frame-tag-text strong { display: block; font-size: 15px; font-weight: 600; }
.gp-hero-frame-tag-text span  { font-size: 12px; opacity: 0.72; }

.gp-hero-frame-badge {
  position: absolute; top: 20px; right: 20px; z-index: 2;
  background: rgba(255,250,246,0.12);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255,255,255,0.14);
  border-radius: 999px;
  padding: 6px 14px;
  color: #fff;
  font-size: 11px; font-weight: 700; letter-spacing: 0.06em;
  display: flex; align-items: center; gap: 6px;
}

/* ─── DIVIDER ────────────────────────────────────────── */
.gp-divider {
  display: flex; align-items: center; gap: 16px;
  padding: 4px var(--pad-x) 20px;
  user-select: none;
}
.gp-divider-line { flex: 1; height: 1px; background: var(--c-rule); }
.gp-divider-icon { color: var(--c-muted); opacity: 0.4; }

/* ─── SEARCH BAR ─────────────────────────────────────── */
.gp-search-wrap {
  padding: 0 var(--pad-x) 24px;
}

.gp-search-bar {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr 1fr auto;
  align-items: center;
  background: var(--c-white);
  border: 1px solid var(--c-rule);
  border-radius: 999px;
  box-shadow: var(--sh-card);
  padding: 4px;
  transition: box-shadow 0.3s, border-color 0.3s;
}
.gp-search-bar:focus-within {
  border-color: rgba(109,76,91,0.20);
  box-shadow: 0 0 0 4px rgba(109,76,91,0.06), var(--sh-card);
}

.gp-search-field {
  position: relative;
  padding: 6px 16px;
  border-radius: 999px;
  transition: background 0.2s;
}
.gp-search-field:hover { background: rgba(245,232,217,0.40); }

.gp-search-field label {
  display: block;
  font-size: 9px; font-weight: 700; letter-spacing: 0.12em;
  text-transform: uppercase; color: var(--c-strong); margin-bottom: 2px;
}

.gp-search-field input,
.gp-search-field select {
  border: none; outline: none; background: transparent;
  font-size: 13px; font-weight: 500; color: var(--c-text);
  width: 100%; appearance: none;
}
.gp-search-field select { cursor: pointer; }
.gp-search-field input::placeholder { color: var(--c-pale); font-weight: 400; }

.gp-search-submit {
  display: inline-flex; align-items: center; justify-content: center; gap: 8px;
  height: 46px; padding: 0 30px; border-radius: 999px; border: none;
  background: var(--c-strong); color: #fff;
  font-size: 13px; font-weight: 700; letter-spacing: 0.02em;
  cursor: pointer; white-space: nowrap;
  transition: all 0.2s var(--ease-out-expo);
  box-shadow: 0 2px 10px rgba(109,76,91,0.18);
}
.gp-search-submit:hover {
  background: #5a3d4a;
  transform: translateY(-1px);
  box-shadow: 0 6px 20px rgba(109,76,91,0.22);
}

/* ─── ACTIVE FILTER CHIPS ────────────────────────────── */
.gp-active-filters {
  display: flex; gap: 8px; flex-wrap: wrap;
  padding: 0 var(--pad-x) 16px;
}
.gp-filter-chip {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 5px 10px 5px 14px;
  border-radius: 999px;
  background: rgba(109,76,91,0.08);
  border: 1px solid rgba(109,76,91,0.12);
  font-size: 12px; font-weight: 600; color: var(--c-strong);
}
.gp-filter-chip-remove {
  display: grid; place-items: center;
  width: 16px; height: 16px; border-radius: 50%;
  border: none; background: rgba(109,76,91,0.12); color: var(--c-strong);
  cursor: pointer; font-size: 11px; transition: background 0.15s;
  line-height: 1;
}
.gp-filter-chip-remove:hover { background: var(--c-strong); color: #fff; }

/* ─── CATEGORY PILLS ─────────────────────────────────── */
.gp-cats {
  padding: 0 var(--pad-x) 24px;
  display: flex; gap: 8px; flex-wrap: wrap; align-items: center;
}

.gp-cat-pill {
  height: 36px; padding: 0 18px; border-radius: 999px;
  border: 1px solid var(--c-rule);
  background: var(--c-white);
  font-size: 13px; font-weight: 600; color: var(--c-accent);
  cursor: pointer; transition: all 0.2s var(--ease-out-expo);
  white-space: nowrap;
  text-decoration: none; display: inline-flex; align-items: center; gap: 4px;
}
.gp-cat-pill:hover {
  border-color: var(--c-strong); color: var(--c-strong);
  background: rgba(109,76,91,0.06); transform: translateY(-1px);
}
.gp-cat-pill.active {
  background: var(--c-strong); color: #fff; border-color: var(--c-strong);
  box-shadow: 0 2px 8px rgba(109,76,91,0.18);
}

/* ─── SECTION SHELL ──────────────────────────────────── */
.gp-section {
  padding: 0 var(--pad-x) 56px;
}

.gp-section-head {
  display: flex; align-items: flex-end; justify-content: space-between; gap: 16px;
  margin-bottom: 24px;
}
.gp-section-title {
  font-family: var(--font-display);
  font-size: clamp(30px, 3.5vw, 42px); font-weight: 600;
  color: var(--c-text); line-height: 0.95;
}
.gp-section-count {
  font-size: 13px; font-weight: 500; color: var(--c-pale);
  padding-bottom: 4px; white-space: nowrap;
}

/* ─── FEATURED STRIP ─────────────────────────────────── */
.gp-featured-grid {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr;
  grid-template-rows: 300px;
  gap: 14px;
}

.gp-feat-card {
  position: relative; overflow: hidden;
  border-radius: var(--r-card);
  background: linear-gradient(160deg, #ede0d0, #ddcebb);
  cursor: pointer;
}

.gp-feat-card img {
  position: absolute; inset: 0;
  width: 100%; height: 100%; object-fit: cover;
  transition: transform 0.6s var(--ease-out-expo);
}
.gp-feat-card:hover img { transform: scale(1.05); }

.gp-feat-card::after {
  content: '';
  position: absolute; inset: 0;
  background: linear-gradient(to top, rgba(31,26,23,0.50) 0%, transparent 45%);
  z-index: 1;
}

.gp-feat-info {
  position: absolute; bottom: 18px; left: 18px; right: 18px; z-index: 2;
  color: #fff;
}
.gp-feat-cat {
  display: inline-block;
  font-size: 10px; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase;
  background: rgba(255,255,255,0.10);
  backdrop-filter: blur(6px);
  border: 1px solid rgba(255,255,255,0.10);
  border-radius: 999px;
  padding: 3px 10px;
  margin-bottom: 6px;
}
.gp-feat-name {
  font-family: var(--font-display);
  font-size: 22px; font-weight: 600; line-height: 1.1;
}
.gp-feat-card:first-child .gp-feat-name { font-size: 30px; }
.gp-feat-price {
  display: inline-block;
  margin-top: 8px;
  font-size: 12px; font-weight: 600;
  background: rgba(255,255,255,0.08);
  backdrop-filter: blur(6px);
  border: 1px solid rgba(255,255,255,0.10);
  border-radius: 999px;
  padding: 3px 10px;
}

/* ─── SERVICES GRID ──────────────────────────────────── */
.gp-services-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 22px;
}

.gp-svc-card {
  background: var(--c-card);
  border: 1px solid var(--c-rule);
  border-radius: var(--r-card);
  overflow: hidden;
  box-shadow: var(--sh-card);
  transition: all 0.35s var(--ease-out-expo);
  display: flex; flex-direction: column;
}
.gp-svc-card:hover {
  transform: translateY(-4px);
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

.gp-svc-badge {
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

.gp-svc-body {
  padding: 20px 20px 22px;
  flex: 1; display: flex; flex-direction: column;
}

.gp-svc-topline {
  display: flex; align-items: center; justify-content: space-between; gap: 8px;
  margin-bottom: 8px;
}

.gp-svc-supplier {
  font-size: 12px; font-weight: 600; color: var(--c-muted);
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
  display: flex; align-items: center; gap: 4px;
}

.gp-svc-rating {
  display: flex; align-items: center; gap: 4px;
  font-size: 12px; font-weight: 700; color: #d4a047; white-space: nowrap; flex-shrink: 0;
}

.gp-svc-name {
  font-family: var(--font-display);
  font-size: 22px; font-weight: 600; line-height: 1.1;
  color: var(--c-text); margin-bottom: 6px;
}
.gp-svc-name a {
  transition: color 0.2s;
}
.gp-svc-card:hover .gp-svc-name a { color: var(--c-strong); }

.gp-svc-desc {
  font-size: 13px; line-height: 1.6; color: var(--c-accent);
  display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
  overflow: hidden;
  flex: 1;
}

/* Info row: duration & booking type */
.gp-svc-meta {
  display: flex; align-items: center; gap: 12px;
  margin: 10px 0 12px;
  font-size: 12px; color: var(--c-muted);
}
.gp-svc-meta span {
  display: inline-flex; align-items: center; gap: 4px;
}

.gp-svc-foot {
  display: flex; align-items: center; justify-content: space-between; gap: 12px;
  margin-top: 12px; padding-top: 14px;
  border-top: 1px solid var(--c-rule);
}

.gp-svc-price-amount {
  font-family: var(--font-display);
  font-size: 26px; font-weight: 600;
  color: var(--c-strong); line-height: 1;
}
.gp-svc-price-unit {
  display: block; margin-top: 1px;
  font-size: 11px; color: var(--c-pale); font-weight: 500;
}

.gp-svc-btn {
  display: inline-flex; align-items: center; gap: 6px;
  height: 40px; padding: 0 20px; border-radius: 999px;
  border: 1px solid var(--c-rule);
  background: var(--c-white); color: var(--c-strong);
  font-size: 13px; font-weight: 700; cursor: pointer;
  transition: all 0.2s var(--ease-out-expo); white-space: nowrap;
  text-decoration: none;
}
.gp-svc-btn:hover {
  background: var(--c-strong); color: #fff; border-color: var(--c-strong);
  transform: translateX(2px);
  box-shadow: 0 2px 10px rgba(109,76,91,0.18);
}

/* ─── EMPTY STATE ────────────────────────────────────── */
.gp-empty {
  border: 1px dashed rgba(109,76,91,0.18);
  border-radius: var(--r-card);
  padding: 64px 24px;
  text-align: center;
  background: rgba(250,245,239,0.60);
}
.gp-empty-icon {
  display: inline-flex; align-items: center; justify-content: center;
  width: 72px; height: 72px; border-radius: 50%;
  background: rgba(109,76,91,0.08);
  color: var(--c-strong);
  margin-bottom: 20px;
}
.gp-empty h3 {
  font-family: var(--font-display); font-size: 32px; font-weight: 600; color: var(--c-text);
  margin-bottom: 8px;
  line-height: 1.05;
}
.gp-empty p {
  color: var(--c-accent); font-size: 14px; line-height: 1.7;
  max-width: 480px; margin: 0 auto;
}
.gp-empty-actions { margin-top: 24px; display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; }
.gp-empty-btn {
  display: inline-flex; align-items: center; gap: 6px;
  height: 44px; padding: 0 24px; border-radius: 999px;
  font-size: 14px; font-weight: 700; cursor: pointer; border: none;
  transition: all 0.2s var(--ease-out-expo);
}
.gp-empty-btn.primary { background: var(--c-strong); color: #fff; box-shadow: 0 2px 8px rgba(109,76,91,0.18); }
.gp-empty-btn.primary:hover { background: #5a3d4a; transform: translateY(-2px); box-shadow: 0 6px 20px rgba(109,76,91,0.20); }
.gp-empty-btn.secondary { background: var(--c-card); color: var(--c-accent); border: 1px solid var(--c-rule); }
.gp-empty-btn.secondary:hover { border-color: var(--c-strong); color: var(--c-strong); background: rgba(109,76,91,0.06); }

/* ─── FOOTER ─────────────────────────────────────────── */
.gp-footer {
  padding: 28px var(--pad-x);
  border-top: 1px solid var(--c-rule);
  display: flex; align-items: center; justify-content: space-between; gap: 16px;
  font-size: 12px; color: var(--c-pale);
}

/* ─── SCROLL REVEAL ──────────────────────────────────── */
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

/* ─── RESPONSIVE ─────────────────────────────────────── */
@media (max-width: 1200px) {
  .gp-hero { grid-template-columns: 1fr; min-height: auto; }
  .gp-hero-col { padding: 60px 0 20px; }
  .gp-hero-visual { padding: 20px 0 40px; }
  .gp-hero-frame { height: 55vh; min-height: 380px; }
  .gp-hero-stats { margin-top: 28px; }
  .gp-featured-grid { grid-template-columns: 1fr 1fr; grid-template-rows: 240px 240px; }
  .gp-feat-card:first-child { grid-column: span 2; }
}

@media (max-width: 900px) {
  .gp-search-bar {
    grid-template-columns: 1fr;
    border-radius: var(--r-card);
    padding: 0;
  }
  .gp-search-field { padding: 10px 16px; border-bottom: 1px solid var(--c-rule); }
  .gp-search-submit { border-radius: 0; height: 44px; }
  .gp-services-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
  .gp-feat-card:first-child .gp-feat-name { font-size: 26px; }
}

@media (max-width: 700px) {
  .gp-hero-col { padding: 40px 0 0; }
  .gp-hero-h1 { font-size: clamp(36px, 12vw, 48px); }
  .gp-hero-stats { gap: 20px; }
  .gp-hero-stat + .gp-hero-stat { padding-left: 20px; }
  .gp-hero-stat-num { font-size: 30px; }
  .gp-hero-frame { height: 45vh; min-height: 300px; }
  .gp-featured-grid { grid-template-columns: 1fr; grid-template-rows: auto; }
  .gp-feat-card { height: 220px; }
  .gp-feat-card:first-child { grid-column: auto; height: 260px; }
  .gp-feat-card:first-child .gp-feat-name { font-size: 22px; }
  .gp-services-grid { grid-template-columns: 1fr; }
  .gp-header-nav a { display: none; }
  .gp-header-cta { min-height: 36px; padding: 0 14px; font-size: 12px; }
  .gp-hero-frame-tag { left: 16px; right: 16px; bottom: 16px; }
  .gp-section { padding-bottom: 36px; }
}

@media (max-width: 480px) {
  :root { --pad-x: 16px; }
  .gp-hero { padding: 0 16px; }
  .gp-brand { font-size: 15px; }
  .gp-brand-mark { width: 34px; height: 34px; font-size: 12px; }
}
</style>
</head>
<body>

<div class="gp-texture" aria-hidden="true"></div>

<!-- HEADER (matches home page styling) -->
<header class="gp-header">
  <a class="gp-brand" href="<?= URLROOT ?>/main/index">
    <span class="gp-brand-mark">G</span>
    <span>Golden Promise</span>
  </a>
  <nav class="gp-header-nav" aria-label="Main navigation">
    <a href="<?= URLROOT ?>/main/index">Home</a>
    <a class="active" href="<?= URLROOT ?>/customerServices/service">Services</a>
    <a href="<?= URLROOT ?>/main/package">Packages</a>
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
  <?php if ($heroService): ?>
  <section class="gp-hero" aria-label="Hero">
    <div class="gp-hero-col">
      <div class="gp-hero-overline">Curated for your wedding</div>
      <h1 class="gp-hero-h1">
        Find your<br>
        perfect <em>vendor</em>
      </h1>
      <p class="gp-hero-sub">
        Malaysia's most trusted wedding service collective — every vendor hand-picked and verified for your special day.
      </p>
      <div class="gp-hero-stats">
        <div class="gp-hero-stat">
          <span class="gp-hero-stat-num"><?= $totalServices ?></span>
          <span class="gp-hero-stat-label">Vendors</span>
        </div>
        <div class="gp-hero-stat">
          <span class="gp-hero-stat-num"><?= $totalCategories ?></span>
          <span class="gp-hero-stat-label">Categories</span>
        </div>
        <div class="gp-hero-stat">
          <span class="gp-hero-stat-num">100%</span>
          <span class="gp-hero-stat-label">Verified</span>
        </div>
      </div>
    </div>
    <div class="gp-hero-visual">
      <div class="gp-hero-frame">
        <?php if (trim((string)($heroService['image'] ?? '')) !== ''): ?>
          <img src="<?= $h($heroService['image']) ?>" alt="<?= $h($heroService['name'] ?? '') ?>" loading="eager">
        <?php endif; ?>
        <div class="gp-hero-frame-overlay"></div>
        <div class="gp-hero-frame-badge">
          <span>✦</span> Featured
        </div>
        <div class="gp-hero-frame-tag">
          <div class="gp-hero-frame-tag-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          </div>
          <div class="gp-hero-frame-tag-text">
            <strong><?= $h($heroService['name'] ?? '') ?></strong>
            <span>by <?= $h($heroService['supplier_name'] ?? '') ?> — start from <?= $moneyRange($heroService) ?></span>
          </div>
        </div>
      </div>
    </div>
  </section>
  <?php else: ?>
  <section class="gp-hero" aria-label="Hero" style="min-height:40vh;">
    <div class="gp-hero-col">
      <div class="gp-hero-overline">Curated for your wedding</div>
      <h1 class="gp-hero-h1">
        Find your<br>
        perfect <em>vendor</em>
      </h1>
      <p class="gp-hero-sub">
        Malaysia's most trusted wedding service collective — every vendor hand-picked and verified.
      </p>
      <div class="gp-hero-stats">
        <div class="gp-hero-stat">
          <span class="gp-hero-stat-num"><?= $totalServices ?></span>
          <span class="gp-hero-stat-label">Vendors</span>
        </div>
        <div class="gp-hero-stat">
          <span class="gp-hero-stat-num"><?= $totalCategories ?></span>
          <span class="gp-hero-stat-label">Categories</span>
        </div>
      </div>
    </div>
    <div class="gp-hero-visual">
      <div class="gp-hero-frame">
        <div class="gp-hero-frame-overlay"></div>
        <div style="position:absolute;inset:0;display:grid;place-items:center;color:rgba(109,76,91,0.15);font-size:56px;">
          <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
        </div>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- DIVIDER -->
  <div class="gp-divider" aria-hidden="true">
    <span class="gp-divider-line"></span>
    <svg class="gp-divider-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
    <span class="gp-divider-line"></span>
  </div>

  <!-- SEARCH BAR -->
  <section class="gp-search-wrap gp-reveal" aria-label="Search and filter">
    <form class="gp-search-bar" method="GET" action="<?= URLROOT ?>/customerServices/service">
      <div class="gp-search-field">
        <label for="q">Search</label>
        <input id="q" type="search" name="q" value="<?= $h($filters['search'] ?? '') ?>" placeholder="Photography, florals, styling…">
      </div>
      <div class="gp-search-field">
        <label for="f-date">Wedding date</label>
        <?php $todayDate = date('Y-m-d'); $maxDate = date('Y-m-d', strtotime('+365 days')); ?>
        <input id="f-date" type="date" name="date" value="<?= $h($activeDate) ?>"
               min="<?= $todayDate ?>" max="<?= $maxDate ?>">
      </div>
      <div class="gp-search-field">
        <label for="f-price-min">Budget (RM)</label>
        <div style="display:flex;align-items:center;gap:4px;">
          <input id="f-price-min" type="number" name="price_min" min="0" step="50" value="<?= $h($activePriceMin) ?>" placeholder="Min" style="width:64px;">
          <span style="color:var(--c-pale);font-size:12px;">–</span>
          <input type="number" name="price_max" min="0" step="50" value="<?= $h($activePriceMax) ?>" placeholder="Max" style="width:64px;">
        </div>
      </div>
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
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        Search
      </button>
    </form>
  </section>

  <!-- ACTIVE FILTER CHIPS -->
  <?php if ($hasActiveFilters): ?>
  <div class="gp-active-filters">
    <?php if (trim((string)($filters['search'] ?? '')) !== ''): ?>
    <span class="gp-filter-chip">
      "<?= $h($filters['search']) ?>"
      <a class="gp-filter-chip-remove" href="<?= URLROOT ?>/customerServices/service?category=<?= $h($activeCategory) ?>&sort=<?= $h($activeSort) ?><?= $activeDate !== '' ? '&date=' . $h($activeDate) : '' ?>" aria-label="Clear search">✕</a>
    </span>
    <?php endif; ?>
    <?php if ($activeDate !== ''): ?>
    <span class="gp-filter-chip">
      <?= $h(date('M j, Y', strtotime($activeDate))) ?>
      <a class="gp-filter-chip-remove" href="<?= URLROOT ?>/customerServices/service?q=<?= $h($filters['search'] ?? '') ?>&category=<?= $h($activeCategory) ?>&sort=<?= $h($activeSort) ?>" aria-label="Clear date">✕</a>
    </span>
    <?php endif; ?>
    <?php if ($activeCategory !== 'all'): ?>
    <span class="gp-filter-chip">
      <?= $h($activeCategory) ?>
      <a class="gp-filter-chip-remove" href="<?= URLROOT ?>/customerServices/service?q=<?= $h($filters['search'] ?? '') ?>&sort=<?= $h($activeSort) ?><?= $activeDate !== '' ? '&date=' . $h($activeDate) : '' ?>" aria-label="Clear category">✕</a>
    </span>
    <?php endif; ?>
    <?php if ($activePriceMin !== '' || $activePriceMax !== ''): ?>
    <span class="gp-filter-chip">
      RM <?= $activePriceMin !== '' ? $h($activePriceMin) : '0' ?> – RM <?= $activePriceMax !== '' ? $h($activePriceMax) : '∞' ?>
      <a class="gp-filter-chip-remove" href="<?= URLROOT ?>/customerServices/service?q=<?= $h($filters['search'] ?? '') ?>&category=<?= $h($activeCategory) ?>&sort=<?= $h($activeSort) ?><?= $activeDate !== '' ? '&date=' . $h($activeDate) : '' ?>" aria-label="Clear budget">✕</a>
    </span>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <!-- CATEGORY PILLS -->
  <?php if (!empty($categories)): ?>
  <div class="gp-cats gp-reveal" role="list" aria-label="Filter by category">
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
        <?php if (!empty($cat['service_count'])): ?><span style="opacity:0.65;">(<?= (int)$cat['service_count'] ?>)</span><?php endif; ?>
      </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <!-- FEATURED (only when no active filters) -->
  <?php if (!empty($featured) && !$hasActiveFilters): ?>
  <section class="gp-section" aria-label="Featured services">
    <div class="gp-section-head gp-reveal">
      <h2 class="gp-section-title">Featured</h2>
      <span class="gp-section-count">Hand-picked for you</span>
    </div>
    <div class="gp-featured-grid gp-reveal">
      <?php foreach ($featured as $fi => $feat):
        $featUrl = URLROOT . '/customerServices/detail/' . (int)$feat['id'];
      ?>
      <a class="gp-feat-card" href="<?= $h($featUrl) ?>">
        <?php if (trim((string)($feat['image'] ?? '')) !== ''): ?>
          <img src="<?= $h($feat['image']) ?>" alt="<?= $h($feat['name'] ?? '') ?>" loading="lazy">
        <?php else: ?>
          <div style="position:absolute;inset:0;display:grid;place-items:center;color:rgba(109,76,91,0.2);">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
          </div>
        <?php endif; ?>
        <div class="gp-feat-info">
          <div class="gp-feat-cat"><?= $h($feat['category'] ?? 'Service') ?></div>
          <div class="gp-feat-name"><?= $h($feat['name'] ?? '') ?></div>
          <div class="gp-feat-price"><?= $moneyRange($feat) ?></div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <!-- ALL SERVICES -->
  <section class="gp-section" aria-label="All services">
    <div class="gp-section-head gp-reveal">
      <h2 class="gp-section-title">All Services</h2>
      <span class="gp-section-count">
        <?= count($services) ?> result<?= count($services) === 1 ? '' : 's' ?>
        <?php if (($filters['search'] ?? '') !== ''): ?> for "<?= $h($filters['search']) ?>"<?php endif; ?>
        <?php if ($activeDate !== ''): ?> on <?= $h(date('M j, Y', strtotime($activeDate))) ?><?php endif; ?>
      </span>
    </div>

    <?php if (empty($services)): ?>
      <div class="gp-empty gp-reveal">
        <div class="gp-empty-icon"><i data-lucide="search-x" size="28"></i></div>
        <h3>No services found</h3>
        <p>We couldn't find any services matching your criteria. Try adjusting your search, date, or budget.</p>
        <div class="gp-empty-actions">
          <a class="gp-empty-btn primary" href="<?= $resetUrl ?>">Clear all filters</a>
          <a class="gp-empty-btn secondary" href="<?= URLROOT ?>/customerServices/service?category=all">Browse all services</a>
        </div>
      </div>
    <?php else: ?>
      <div class="gp-services-grid">
        <?php foreach ($services as $i => $svc): ?>
        <?php
          $detailUrl = URLROOT . '/customerServices/detail/' . (int)$svc['id'] . $detailDateQuery;
          $revealClass = 'gp-reveal gp-reveal-d' . min($i % 6, 5);
        ?>
        <article class="gp-svc-card <?= $revealClass ?>">
          <a class="gp-svc-img" href="<?= $h($detailUrl) ?>" tabindex="-1" aria-hidden="true">
            <?php if (trim((string)($svc['image'] ?? '')) !== ''): ?>
              <img src="<?= $h($svc['image']) ?>" alt="<?= $h($svc['name'] ?? '') ?>" loading="lazy">
            <?php else: ?>
              <div class="gp-svc-img-placeholder">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
              </div>
            <?php endif; ?>
            <span class="gp-svc-badge"><?= $h($svc['category'] ?? 'Service') ?></span>
          </a>
          <div class="gp-svc-body">
            <!-- Top line: supplier + rating -->
            <div class="gp-svc-topline">
              <span class="gp-svc-supplier" title="<?= $h($svc['supplier_name'] ?? '') ?>">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="opacity:0.5;flex-shrink:0;"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                <?= $h($svc['supplier_name'] ?? 'Supplier') ?>
              </span>
              <?php if ((float)($svc['rating'] ?? 0) > 0): ?>
              <div class="gp-svc-rating" title="<?= (int)($svc['review_count'] ?? 0) ?> review<?= (int)($svc['review_count'] ?? 0) === 1 ? '' : 's' ?>">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                <?= number_format((float)$svc['rating'], 1) ?>
                <span style="font-weight:400;opacity:0.6;font-size:10px;">(<?= (int)($svc['review_count'] ?? 0) ?>)</span>
              </div>
              <?php endif; ?>
            </div>

            <!-- Name -->
            <h3 class="gp-svc-name">
              <a href="<?= $h($detailUrl) ?>"><?= $h($svc['name'] ?? '') ?></a>
            </h3>

            <!-- Description -->
            <p class="gp-svc-desc"><?= $h($svc['description'] ?? '') ?></p>

            <!-- Meta info: duration & booking type -->
            <div class="gp-svc-meta">
              <span>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                <?= $h($durationText($svc)) ?>
              </span>
              <span>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                <?= $pricingUnit($svc) === '/hr' ? 'Per hour' : 'Per session' ?>
              </span>
            </div>

            <!-- Footer: price + CTA -->
            <div class="gp-svc-foot">
              <div class="gp-svc-price">
                <span class="gp-svc-price-amount"><?= $moneyRange($svc) ?></span>
                <span class="gp-svc-price-unit"><?= $h($durationText($svc)) ?> <?= $pricingUnit($svc) ?></span>
              </div>
              <a class="gp-svc-btn" href="<?= $h($detailUrl) ?>">
                View details
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
  // Lucide icons
  if (typeof lucide !== 'undefined') lucide.createIcons();

  // Scroll reveal
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
