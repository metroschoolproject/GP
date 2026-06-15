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
    $featured = array_slice($services, 0, 6);
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
    return $money($service['display_price'] ?? $service['customize_price'] ?? $service['price_max'] ?? $service['price'] ?? 0);
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

$cartCount = (int)($cartCount ?? 0);

$totalServices = count($services);
$totalCategories = count($categories);

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
   GOLDEN PROMISE — SERVICES DIRECTORY (Hero Cards)
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
  --c-gold:      #d4a047;
  --c-gold-light: rgba(212, 160, 71, 0.12);

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

/* ─── HEADER ─────────────────────────────────────────── */
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

/* ─── PROFILE DROPDOWN ──────────────────────────────── */
.gp-profile-dropdown { position: relative; }

.gp-profile-btn {
  display: flex; align-items: center; gap: 8px;
  padding: 4px 12px 4px 4px;
  border-radius: 999px;
  border: 1px solid var(--c-rule);
  background: var(--c-white);
  cursor: pointer;
  transition: all 0.2s;
  color: var(--c-strong);
  font-family: var(--font-body);
  font-size: 13px;
  font-weight: 600;
}
.gp-profile-btn:hover { border-color: var(--c-strong); background: rgba(109,76,91,0.06); }

.gp-profile-avatar {
  display: grid; place-items: center;
  width: 32px; height: 32px;
  border-radius: 50%;
  background: var(--c-strong);
  color: #fffaf3;
  font-size: 12px;
  font-weight: 800;
  letter-spacing: 0.5px;
}

.gp-profile-name { white-space: nowrap; max-width: 100px; overflow: hidden; text-overflow: ellipsis; }

.gp-profile-chevron { opacity: 0.6; transition: transform 0.2s; }
.gp-profile-btn[aria-expanded="true"] .gp-profile-chevron { transform: rotate(180deg); }

.gp-profile-menu {
  position: absolute; top: calc(100% + 8px); right: 0;
  min-width: 180px;
  padding: 6px;
  border-radius: 12px;
  border: 1px solid var(--c-rule);
  background: var(--c-white);
  box-shadow: 0 12px 35px rgba(15,23,42,0.1);
  opacity: 0;
  visibility: hidden;
  transform: translateY(-4px);
  transition: all 0.15s var(--ease-out-expo);
}
.gp-profile-btn[aria-expanded="true"] + .gp-profile-menu,
.gp-profile-menu.show {
  opacity: 1;
  visibility: visible;
  transform: translateY(0);
}

.gp-profile-menu-item {
  display: flex; align-items: center; gap: 10px;
  padding: 10px 12px;
  border-radius: 8px;
  font-size: 13px;
  font-weight: 600;
  color: var(--c-text);
  transition: all 0.15s;
}
.gp-profile-menu-item:hover { background: rgba(109,76,91,0.06); }
.gp-profile-menu-item--danger { color: var(--c-danger); }
.gp-profile-menu-item--danger:hover { background: rgba(185,75,75,0.08); }

/* ═══════════════════════════════════════════════════════════
   HERO INTRO — compact intro bar
   ═══════════════════════════════════════════════════════════ */
.gp-hero-intro {
  padding: 48px var(--pad-x) 12px;
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 24px;
}
.gp-hero-intro-text {
  max-width: 580px;
}
.gp-hero-intro-overline {
  font-size: 11px; font-weight: 800; letter-spacing: 0.16em; text-transform: uppercase;
  color: var(--c-gold); margin-bottom: 8px;
  display: flex; align-items: center; gap: 10px;
}
.gp-hero-intro-overline::before {
  content: '';
  display: block; width: 24px; height: 1.5px; background: var(--c-gold);
}
.gp-hero-intro h1 {
  font-family: var(--font-display);
  font-size: clamp(34px, 4vw, 52px);
  font-weight: 600;
  line-height: 0.95;
  color: var(--c-text);
  letter-spacing: -0.02em;
}
.gp-hero-intro h1 em { font-style: italic; color: var(--c-strong); }
.gp-hero-intro-stats {
  display: flex; gap: 24px; flex-shrink: 0;
}
.gp-hero-intro-stat {
  text-align: right;
}
.gp-hero-intro-stat-num {
  font-family: var(--font-display);
  font-size: 32px; font-weight: 600;
  color: var(--c-strong); line-height: 1;
}
.gp-hero-intro-stat-label {
  font-size: 11px; color: var(--c-pale); font-weight: 500;
}

/* ─── SEARCH PANEL ──────────────────────────────────── */
.gp-search-boutique {
  padding: 24px var(--pad-x) 20px;
}
.gp-search-panel {
  background: linear-gradient(145deg, rgba(250,245,239,0.95), rgba(245,232,217,0.92));
  backdrop-filter: blur(20px);
  border: 1px solid rgba(212,160,71,0.15);
  border-radius: 20px;
  box-shadow: 0 20px 50px -12px rgba(109,76,91,0.12), inset 0 1px 0 rgba(255,255,255,0.60);
  padding: 20px 24px;
  transition: box-shadow 0.3s;
}
.gp-search-panel:focus-within {
  border-color: rgba(212,160,71,0.30);
  box-shadow: 0 0 0 4px rgba(212,160,71,0.08), 0 20px 50px -12px rgba(109,76,91,0.16);
}
.gp-search-row-fields {
  display: grid;
  grid-template-columns: 1.4fr 0.9fr 1.1fr 0.8fr auto;
  align-items: end;
  gap: 10px;
}
.gp-search-field {
  display: flex; flex-direction: column; gap: 4px;
}
.gp-search-field label {
  font-size: 9px; font-weight: 700; letter-spacing: 0.1em;
  text-transform: uppercase; color: var(--c-strong);
  padding-left: 2px;
}
.gp-search-field input,
.gp-search-field select {
  height: 42px;
  padding: 0 14px;
  border-radius: 10px;
  border: 1px solid rgba(212,160,71,0.12);
  background: rgba(255,255,255,0.72);
  font-size: 13px; font-weight: 500; color: var(--c-text);
  width: 100%; appearance: none;
  transition: border-color 0.2s, background 0.2s;
}
.gp-search-field input:hover,
.gp-search-field select:hover {
  background: rgba(255,255,255,0.90);
  border-color: rgba(212,160,71,0.25);
}
.gp-search-field input:focus,
.gp-search-field select:focus {
  border-color: var(--c-gold);
  background: #fff;
  box-shadow: 0 0 0 3px rgba(212,160,71,0.10);
}
.gp-search-field input::placeholder { color: var(--c-pale); font-weight: 400; }
.gp-search-field select { cursor: pointer; }

.gp-price-group {
  display: flex; align-items: center; gap: 4px;
}
.gp-price-group input { width: 100%; min-width: 0; }
.gp-price-sep {
  color: var(--c-pale); font-size: 12px; font-weight: 500;
  flex-shrink: 0;
}

.gp-search-btn {
  display: inline-flex; align-items: center; justify-content: center; gap: 8px;
  height: 42px; padding: 0 28px;
  border-radius: 12px; border: none;
  background: linear-gradient(135deg, var(--c-strong) 0%, #8b5e6f 100%);
  color: #fffaf3;
  font-size: 13px; font-weight: 700;
  cursor: pointer; white-space: nowrap;
  transition: all 0.25s var(--ease-out-expo);
  box-shadow: 0 4px 14px rgba(109,76,91,0.22);
}
.gp-search-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(109,76,91,0.30);
}
.gp-search-btn:active { transform: translateY(0); }

/* ─── FILTER CHIPS ──────────────────────────────────── */
.gp-active-filters {
  display: flex; gap: 8px; flex-wrap: wrap;
  padding: 0 var(--pad-x) 16px;
}
.gp-filter-chip {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 6px 8px 6px 14px;
  border-radius: 999px;
  background: linear-gradient(135deg, rgba(212,160,71,0.08), rgba(212,160,71,0.04));
  border: 1px solid rgba(212,160,71,0.18);
  font-size: 12px; font-weight: 600; color: #8b6f3e;
}
.gp-filter-chip-remove {
  display: grid; place-items: center;
  width: 18px; height: 18px; border-radius: 50%;
  border: none;
  background: rgba(212,160,71,0.12); color: #8b6f3e;
  cursor: pointer; font-size: 10px; font-weight: 700;
  transition: all 0.15s; line-height: 1;
}
.gp-filter-chip-remove:hover {
  background: var(--c-gold); color: #fff;
  box-shadow: 0 2px 6px rgba(212,160,71,0.25);
}

/* ─── CATEGORY PILLS ────────────────────────────────── */
.gp-cats-strip {
  display: flex; gap: 10px; flex-wrap: wrap; align-items: center;
  padding: 0 var(--pad-x) 28px;
}
.gp-cat-pill {
  display: flex; align-items: center; gap: 10px;
  height: 48px;
  padding: 0 20px 0 18px;
  border-radius: 14px;
  border: 1px solid rgba(212,160,71,0.12);
  background: var(--c-white);
  font-size: 13px; font-weight: 600; color: var(--c-accent);
  cursor: pointer;
  transition: all 0.25s var(--ease-out-expo);
  white-space: nowrap;
  text-decoration: none;
  box-shadow: 0 2px 8px rgba(109,76,91,0.04);
}
.gp-cat-pill:hover {
  border-color: rgba(212,160,71,0.30);
  background: rgba(250,245,239,0.90);
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(109,76,91,0.08);
}
.gp-cat-pill.active {
  background: linear-gradient(135deg, var(--c-strong) 0%, #8b5e6f 100%);
  color: #fffaf3;
  border-color: transparent;
  box-shadow: 0 4px 14px rgba(109,76,91,0.20);
}
.gp-cat-pill.active:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(109,76,91,0.28);
}
.gp-cat-count {
  display: inline-flex; align-items: center; justify-content: center;
  min-width: 22px; height: 22px;
  padding: 0 6px;
  border-radius: 8px;
  font-size: 11px; font-weight: 700;
  background: rgba(109,76,91,0.08);
  color: var(--c-strong);
  line-height: 1;
}
.gp-cat-pill.active .gp-cat-count {
  background: rgba(255,255,255,0.18);
  color: #fff;
}
.gp-cat-icon {
  font-size: 16px; line-height: 1;
  flex-shrink: 0;
}

/* ═══════════════════════════════════════════════════════════
   HERO CARDS — large, visual, full-width masonry
   ═══════════════════════════════════════════════════════════ */

.gp-hero-cards-section {
  padding: 8px var(--pad-x) 48px;
}

.gp-hero-cards-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
}

/* The big hero card */
.gp-hero-card {
  position: relative;
  border-radius: 20px;
  overflow: hidden;
  background: var(--c-card);
  border: 1px solid var(--c-rule);
  box-shadow: 0 20px 44px -16px rgba(109,76,91,0.14);
  transition: all 0.5s var(--ease-out-expo);
  display: flex;
  flex-direction: column;
  height: 100%;
}
.gp-hero-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 28px 56px -16px rgba(109,76,91,0.20);
  border-color: rgba(212,160,71,0.20);
}

/* Card 1 spans 2 cols, gets hero treatment */
.gp-hero-card.featured-hero {
  grid-column: span 2;
  display: grid;
  grid-template-columns: 1.1fr 0.9fr;
  min-height: 440px;
}
.gp-hero-card.featured-hero .gp-hero-card-img {
  aspect-ratio: auto;
  height: 100%;
  min-height: 440px;
}
.gp-hero-card.featured-hero .gp-hero-card-body {
  padding: 32px 32px 30px;
  justify-content: center;
}
.gp-hero-card.featured-hero .gp-hero-card-name {
  font-size: 30px;
  line-height: 1.05;
}
.gp-hero-card.featured-hero .gp-hero-card-price-amount {
  font-size: 32px;
}
.gp-hero-card.featured-hero .gp-hero-card-desc {
  font-size: 14px;
  -webkit-line-clamp: 3;
}

/* Regular cards in right col */
.gp-hero-card:not(.featured-hero) {
  min-height: 380px;
}

.gp-hero-card-img {
  position: relative;
  aspect-ratio: 4/3;
  overflow: hidden;
  background: linear-gradient(160deg, #ede0d0, #ddcebb);
  flex-shrink: 0;
}
.gp-hero-card-img img {
  position: absolute; inset: 0;
  width: 100%; height: 100%; object-fit: cover;
  transition: transform 0.7s var(--ease-out-expo);
}
.gp-hero-card:hover .gp-hero-card-img img { transform: scale(1.07); }

.gp-hero-card-img-placeholder {
  position: absolute; inset: 0;
  display: grid; place-items: center;
  color: var(--c-pale); opacity: 0.4;
}

.gp-hero-card-badge {
  position: absolute; top: 14px; left: 14px; z-index: 2;
  background: rgba(255,250,246,0.92);
  backdrop-filter: blur(8px);
  border: 1px solid rgba(109,76,91,0.10);
  border-radius: 999px;
  padding: 5px 12px;
  font-size: 10px; font-weight: 700; color: var(--c-strong);
  letter-spacing: 0.04em;
  text-transform: uppercase;
}

.gp-hero-card-body {
  padding: 20px 22px 24px;
  flex: 1;
  display: flex;
  flex-direction: column;
}

.gp-hero-card-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  margin-bottom: 6px;
}

.gp-hero-card-supplier {
  font-size: 12px; font-weight: 600; color: var(--c-muted);
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
  display: flex; align-items: center; gap: 4px;
}

.gp-hero-card-rating {
  display: flex; align-items: center; gap: 4px;
  font-size: 12px; font-weight: 700; color: #d4a047; white-space: nowrap; flex-shrink: 0;
}

.gp-hero-card-name {
  font-family: var(--font-display);
  font-size: 24px; font-weight: 600;
  line-height: 1.1;
  color: var(--c-text); margin-bottom: 6px;
}
.gp-hero-card-name a { transition: color 0.2s; }
.gp-hero-card:hover .gp-hero-card-name a { color: var(--c-strong); }

.gp-hero-card-desc {
  font-size: 13px; line-height: 1.6; color: var(--c-accent);
  display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
  overflow: hidden;
  flex: 1;
}

.gp-hero-card-meta {
  display: flex; align-items: center; gap: 12px;
  margin: 10px 0 14px;
  font-size: 12px; color: var(--c-muted);
}
.gp-hero-card-meta span {
  display: inline-flex; align-items: center; gap: 4px;
}

.gp-hero-card-foot {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding-top: 14px;
  border-top: 1px solid var(--c-rule);
}

.gp-hero-card-price-amount {
  font-family: var(--font-display);
  font-size: 26px; font-weight: 600;
  color: var(--c-strong); line-height: 1;
}
.gp-hero-card-price-unit {
  display: block; margin-top: 1px;
  font-size: 11px; color: var(--c-pale); font-weight: 500;
}

.gp-hero-card-btn {
  display: inline-flex; align-items: center; gap: 6px;
  height: 40px; padding: 0 22px; border-radius: 999px;
  border: 1px solid var(--c-rule);
  background: var(--c-white); color: var(--c-strong);
  font-size: 13px; font-weight: 700; cursor: pointer;
  transition: all 0.2s var(--ease-out-expo); white-space: nowrap;
  text-decoration: none;
}
.gp-hero-card-btn:hover {
  background: var(--c-strong); color: #fff; border-color: var(--c-strong);
  transform: translateX(2px);
  box-shadow: 0 2px 10px rgba(109,76,91,0.18);
}

/* ─── SECTION HEADER ────────────────────────────────── */
.gp-section-head {
  display: flex; align-items: flex-end; justify-content: space-between; gap: 16px;
  padding: 0 var(--pad-x);
  margin-bottom: 24px;
}
.gp-section-title {
  font-family: var(--font-display);
  font-size: clamp(26px, 3vw, 36px); font-weight: 600;
  color: var(--c-text); line-height: 0.95;
}
.gp-section-count {
  font-size: 13px; font-weight: 500; color: var(--c-pale);
  padding-bottom: 4px; white-space: nowrap;
}

/* ─── REMAINING SERVICES GRID ───────────────────────── */
.gp-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
  padding: 0 var(--pad-x) 56px;
}

.gp-card {
  background: var(--c-card);
  border: 1px solid var(--c-rule);
  border-radius: 18px;
  overflow: hidden;
  box-shadow: 0 12px 28px -10px rgba(109,76,91,0.08);
  transition: all 0.4s var(--ease-out-expo);
  display: flex; flex-direction: column;
}
.gp-card:hover {
  transform: translateY(-4px);
  box-shadow: var(--sh-panel);
}

.gp-card-img {
  display: block; position: relative;
  aspect-ratio: 4/3; overflow: hidden;
  background: linear-gradient(160deg, #ede0d0, #ddcebb);
  flex-shrink: 0;
}
.gp-card-img img {
  position: absolute; inset: 0;
  width: 100%; height: 100%; object-fit: cover;
  transition: transform 0.6s var(--ease-out-expo);
}
.gp-card:hover .gp-card-img img { transform: scale(1.06); }

.gp-card-img-placeholder {
  position: absolute; inset: 0;
  display: grid; place-items: center;
  color: var(--c-pale); opacity: 0.4;
}

.gp-card-badge {
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

.gp-card-body {
  padding: 18px 20px 22px;
  flex: 1; display: flex; flex-direction: column;
}

.gp-card-top {
  display: flex; align-items: center; justify-content: space-between; gap: 8px;
  margin-bottom: 6px;
}

.gp-card-supplier {
  font-size: 11px; font-weight: 600; color: var(--c-muted);
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
  display: flex; align-items: center; gap: 4px;
}

.gp-card-rating {
  display: flex; align-items: center; gap: 4px;
  font-size: 11px; font-weight: 700; color: #d4a047; white-space: nowrap; flex-shrink: 0;
}

.gp-card-name {
  font-family: var(--font-display);
  font-size: 20px; font-weight: 600; line-height: 1.1;
  color: var(--c-text); margin-bottom: 4px;
}
.gp-card-name a { transition: color 0.2s; }
.gp-card:hover .gp-card-name a { color: var(--c-strong); }

.gp-card-desc {
  font-size: 12px; line-height: 1.6; color: var(--c-accent);
  display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
  overflow: hidden;
  flex: 1;
}

.gp-card-meta {
  display: flex; align-items: center; gap: 10px;
  margin: 8px 0 10px;
  font-size: 11px; color: var(--c-muted);
}
.gp-card-meta span { display: inline-flex; align-items: center; gap: 4px; }

.gp-card-foot {
  display: flex; align-items: center; justify-content: space-between; gap: 12px;
  margin-top: 10px; padding-top: 12px;
  border-top: 1px solid var(--c-rule);
}

.gp-card-price-amount {
  font-family: var(--font-display);
  font-size: 22px; font-weight: 600;
  color: var(--c-strong); line-height: 1;
}
.gp-card-price-unit {
  display: block; margin-top: 1px;
  font-size: 10px; color: var(--c-pale); font-weight: 500;
}

.gp-card-btn {
  display: inline-flex; align-items: center; gap: 6px;
  height: 36px; padding: 0 16px; border-radius: 999px;
  border: 1px solid var(--c-rule);
  background: var(--c-white); color: var(--c-strong);
  font-size: 12px; font-weight: 700; cursor: pointer;
  transition: all 0.2s var(--ease-out-expo); white-space: nowrap;
  text-decoration: none;
}
.gp-card-btn:hover {
  background: var(--c-strong); color: #fff; border-color: var(--c-strong);
  transform: translateX(2px);
  box-shadow: 0 2px 10px rgba(109,76,91,0.18);
}

/* ─── EMPTY STATE ────────────────────────────────────── */
.gp-empty {
  grid-column: 1 / -1;
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
  margin-bottom: 8px; line-height: 1.05;
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
  .gp-hero-card.featured-hero {
    grid-template-columns: 1fr;
    grid-column: span 3;
    min-height: auto;
  }
  .gp-hero-card.featured-hero .gp-hero-card-img {
    min-height: 320px;
  }
  .gp-hero-card.featured-hero .gp-hero-card-body {
    padding: 24px 24px 28px;
  }
  .gp-hero-card.featured-hero .gp-hero-card-name {
    font-size: 26px;
  }
}

@media (max-width: 900px) {
  .gp-hero-cards-grid { grid-template-columns: 1fr 1fr; }
  .gp-hero-card.featured-hero { grid-column: span 2; }
  .gp-grid { grid-template-columns: 1fr 1fr; gap: 16px; }

  .gp-search-row-fields { grid-template-columns: 1fr 1fr; }
  .gp-search-row-fields .gp-search-field:nth-child(1) { grid-column: span 2; }
  .gp-search-row-fields .gp-search-btn { grid-column: span 2; }
}

@media (max-width: 700px) {
  .gp-hero-intro { flex-direction: column; align-items: flex-start; gap: 12px; }
  .gp-hero-intro-stats { display: none; }
  .gp-hero-cards-grid { grid-template-columns: 1fr; }
  .gp-hero-card.featured-hero { grid-column: 1; }
  .gp-hero-card.featured-hero .gp-hero-card-name { font-size: 22px; }
  .gp-hero-card.featured-hero .gp-hero-card-price-amount { font-size: 26px; }
  .gp-hero-card.featured-hero .gp-hero-card-img { min-height: 240px; }
  .gp-grid { grid-template-columns: 1fr; }
  .gp-header-nav a { display: none; }
  .gp-header-cta { min-height: 36px; padding: 0 14px; font-size: 12px; }
  .gp-search-panel { padding: 14px 16px; border-radius: 16px; }
  .gp-cat-pill { height: 42px; padding: 0 14px; font-size: 12px; }
  .gp-cat-icon { font-size: 14px; }
  .gp-cats-strip { gap: 8px; }
}

@media (max-width: 480px) {
  :root { --pad-x: 16px; }
  .gp-brand { font-size: 15px; }
  .gp-brand-mark { width: 34px; height: 34px; font-size: 12px; }
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
    <a class="active" href="<?= URLROOT ?>/customerServices/service">Services</a>
    <a href="<?= URLROOT ?>/customerServices/packages">Packages</a>
  </nav>
  <div class="gp-header-actions">
    <a class="gp-cart-badge" href="<?= URLROOT ?>/cart" aria-label="Cart">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
      <?php if ($cartCount > 0): ?>
      <span class="gp-cart-badge-count"><?= $cartCount ?></span>
      <?php endif; ?>
    </a>
    <?php if ($isLoggedIn): ?>
    <div class="gp-profile-dropdown">
      <button class="gp-profile-btn" type="button" aria-expanded="false">
        <span class="gp-profile-avatar"><?= strtoupper(substr($_SESSION['session_name'] ?? 'U', 0, 1)) ?></span>
        <span class="gp-profile-name"><?= htmlspecialchars(explode(' ', $_SESSION['session_name'] ?? 'User')[0], ENT_QUOTES, 'UTF-8') ?></span>
        <svg class="gp-profile-chevron" width="10" height="6" viewBox="0 0 10 6" fill="none"><path d="M1 1l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </button>
      <div class="gp-profile-menu" aria-hidden="true">
        <a class="gp-profile-menu-item" href="<?= URLROOT ?>/booking/myBookings">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
          My Bookings
        </a>
        <a class="gp-profile-menu-item gp-profile-menu-item--danger" href="<?= URLROOT ?>/users/logout">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
          Logout
        </a>
      </div>
    </div>
    <?php else: ?>
    <a class="gp-header-cta" href="<?= URLROOT ?>/users/auth">Sign in</a>
    <?php endif; ?>
  </div>
</header>

<main>

  <!-- ════════════════════════════════════════════════════════
       HERO INTRO — compact
       ════════════════════════════════════════════════════════ -->


  <!-- SEARCH -->
  <section class="gp-search-boutique gp-reveal" aria-label="Search and filter">
    <form class="gp-search-panel" method="GET" action="<?= URLROOT ?>/customerServices/service">
      <div class="gp-search-row-fields">
        <div class="gp-search-field">
          <label for="q">Search</label>
          <input id="q" type="search" name="q" value="<?= $h($filters['search'] ?? '') ?>" placeholder="Photography, florals, styling…">
        </div>
        <div class="gp-search-field">
          <label for="f-date">Wedding date</label>
          <?php $todayDate = date('Y-m-d'); $maxDate = date('Y-m-d', strtotime('+365 days')); ?>
          <input id="f-date" type="date" name="date" value="<?= $h($activeDate) ?>" min="<?= $todayDate ?>" max="<?= $maxDate ?>">
        </div>
        <div class="gp-search-field">
          <label for="f-price-min">Budget (RM)</label>
          <div class="gp-price-group">
            <input id="f-price-min" type="number" name="price_min" min="0" step="50" value="<?= $h($activePriceMin) ?>" placeholder="Min">
            <span class="gp-price-sep">–</span>
            <input type="number" name="price_max" min="0" step="50" value="<?= $h($activePriceMax) ?>" placeholder="Max">
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
        <button class="gp-search-btn" type="submit">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
          Find
        </button>
      </div>
    </form>
  </section>

  <!-- FILTER CHIPS -->
  <?php if ($hasActiveFilters): ?>
  <div class="gp-active-filters">
    <?php if (trim((string)($filters['search'] ?? '')) !== ''): ?>
    <span class="gp-filter-chip">
      "<?= $h($filters['search']) ?>"
      <a class="gp-filter-chip-remove" href="<?= URLROOT ?>/customerServices/service?category=<?= $h($activeCategory) ?>&sort=<?= $h($activeSort) ?><?= $activeDate !== '' ? '&date=' . $h($activeDate) : '' ?>">✕</a>
    </span>
    <?php endif; ?>
    <?php if ($activeDate !== ''): ?>
    <span class="gp-filter-chip">
      <?= $h(date('M j, Y', strtotime($activeDate))) ?>
      <a class="gp-filter-chip-remove" href="<?= URLROOT ?>/customerServices/service?q=<?= $h($filters['search'] ?? '') ?>&category=<?= $h($activeCategory) ?>&sort=<?= $h($activeSort) ?>">✕</a>
    </span>
    <?php endif; ?>
    <?php if ($activeCategory !== 'all'): ?>
    <span class="gp-filter-chip">
      <?= $h($activeCategory) ?>
      <a class="gp-filter-chip-remove" href="<?= URLROOT ?>/customerServices/service?q=<?= $h($filters['search'] ?? '') ?>&sort=<?= $h($activeSort) ?><?= $activeDate !== '' ? '&date=' . $h($activeDate) : '' ?>">✕</a>
    </span>
    <?php endif; ?>
    <?php if ($activePriceMin !== '' || $activePriceMax !== ''): ?>
    <span class="gp-filter-chip">
      RM <?= $activePriceMin !== '' ? $h($activePriceMin) : '0' ?> – RM <?= $activePriceMax !== '' ? $h($activePriceMax) : '∞' ?>
      <a class="gp-filter-chip-remove" href="<?= URLROOT ?>/customerServices/service?q=<?= $h($filters['search'] ?? '') ?>&category=<?= $h($activeCategory) ?>&sort=<?= $h($activeSort) ?><?= $activeDate !== '' ? '&date=' . $h($activeDate) : '' ?>">✕</a>
    </span>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <!-- CATEGORY PILLS -->
  <?php if (!empty($categories)): ?>
  <div class="gp-cats-strip gp-reveal" role="list" aria-label="Filter by category">
    <?php $dateQuery = $activeDate !== '' ? '&date=' . $h($activeDate) : ''; ?>
    <?php
      $catIcons = [
        'all' => '✦',
        'photography' => '📸',
        'videography' => '🎥',
        'florals' => '💐',
        'floral' => '💐',
        'makeup' => '💄',
        'mua' => '💄',
        'music' => '🎶',
        'entertainment' => '🎭',
        'catering' => '🍽️',
        'cake' => '🍰',
        'dress' => '👗',
        'attire' => '👔',
        'jewelry' => '💍',
        'jewellery' => '💍',
        'decor' => '🏵️',
        'decoration' => '🏵️',
        'lighting' => '✨',
        'transport' => '🚗',
        'planning' => '📋',
        'planner' => '📋',
        'invitation' => '💌',
        'stationery' => '✉️',
        'favors' => '🎁',
        'favour' => '🎁',
        'hair' => '💇',
        'beauty' => '🧴',
        'rental' => '🪑',
        'venue' => '🏛️',
      ];
      function catIcon($slug, $icons) {
        return $icons[strtolower($slug)] ?? '✦';
      }
    ?>
    <a class="gp-cat-pill <?= $activeCategory === 'all' ? 'active' : '' ?>"
       href="<?= URLROOT ?>/customerServices/service?category=all<?= $dateQuery ?>" role="listitem">
      <span class="gp-cat-icon"><?= catIcon('all', $catIcons) ?></span>
      All
      <span class="gp-cat-count"><?= (int)$totalServices ?></span>
    </a>
    <?php foreach ($categories as $cat):
      $slug = $cat['slug'] ?? strtolower($cat['name'] ?? '');
      $isActive = $activeCategory === $slug || $activeCategory === ($cat['name'] ?? '');
    ?>
      <a class="gp-cat-pill <?= $isActive ? 'active' : '' ?>"
         href="<?= URLROOT ?>/customerServices/service?category=<?= $h($slug) ?><?= $dateQuery ?>" role="listitem">
        <span class="gp-cat-icon"><?= catIcon($slug, $catIcons) ?></span>
        <?= $h($cat['name'] ?? '') ?>
        <span class="gp-cat-count"><?= (int)($cat['service_count'] ?? 0) ?></span>
      </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <!-- ════════════════════════════════════════════════════════
       HERO CARDS — the services themselves are the hero
       ════════════════════════════════════════════════════════ -->
  <?php if (!empty($services)): ?>
  <section class="gp-hero-cards-section" aria-label="Services">

    <?php
    // First 3 services get hero card treatment (or fewer if less than 3)
    $heroCards = array_slice($services, 0, 3);
    $remaining = array_slice($services, 3);
    ?>

    <div class="gp-hero-cards-grid">
      <?php foreach ($heroCards as $hcIndex => $hcard):
        $isFeatured = ($hcIndex === 0);
        $hDetailUrl = URLROOT . '/customerServices/detail/' . (int)$hcard['id'] . $detailDateQuery;
      ?>
      <article class="gp-hero-card <?= $isFeatured ? 'featured-hero' : '' ?> gp-reveal gp-reveal-d<?= min($hcIndex, 5) ?>">
        <?php if ($isFeatured && trim((string)($hcard['image'] ?? '')) !== ''): ?>
        <div class="gp-hero-card-img">
          <img src="<?= $h($hcard['image']) ?>" alt="<?= $h($hcard['name'] ?? '') ?>" loading="eager">
          <span class="gp-hero-card-badge"><?= $h($hcard['category'] ?? 'Featured') ?></span>
        </div>
        <?php elseif ($isFeatured): ?>
        <div class="gp-hero-card-img">
          <div class="gp-hero-card-img-placeholder">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
          </div>
          <span class="gp-hero-card-badge"><?= $h($hcard['category'] ?? 'Featured') ?></span>
        </div>
        <?php else: ?>
        <div class="gp-hero-card-img">
          <?php if (trim((string)($hcard['image'] ?? '')) !== ''): ?>
            <img src="<?= $h($hcard['image']) ?>" alt="<?= $h($hcard['name'] ?? '') ?>" loading="eager">
          <?php else: ?>
            <div class="gp-hero-card-img-placeholder">
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
            </div>
          <?php endif; ?>
          <span class="gp-hero-card-badge"><?= $h($hcard['category'] ?? 'Service') ?></span>
        </div>
        <?php endif; ?>

        <div class="gp-hero-card-body">
          <div class="gp-hero-card-top">
            <span class="gp-hero-card-supplier" title="<?= $h($hcard['supplier_name'] ?? '') ?>">
              <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="opacity:0.5;flex-shrink:0;"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
              <?= $h($hcard['supplier_name'] ?? 'Supplier') ?>
            </span>
            <?php if ((float)($hcard['rating'] ?? 0) > 0): ?>
            <div class="gp-hero-card-rating" title="<?= (int)($hcard['review_count'] ?? 0) ?> reviews">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
              <?= number_format((float)$hcard['rating'], 1) ?>
            </div>
            <?php endif; ?>
          </div>
          <h2 class="gp-hero-card-name">
            <a href="<?= $h($hDetailUrl) ?>"><?= $h($hcard['name'] ?? '') ?></a>
          </h2>
          <p class="gp-hero-card-desc"><?= $h($hcard['description'] ?? '') ?></p>
          <div class="gp-hero-card-meta">
            <span>
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
              <?= $h($durationText($hcard)) ?>
            </span>
            <span>
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
              <?= $pricingUnit($hcard) === '/hr' ? 'Per hour' : 'Per session' ?>
            </span>
          </div>
          <div class="gp-hero-card-foot">
            <div>
              <span class="gp-hero-card-price-amount"><?= $moneyRange($hcard) ?></span>
              <span class="gp-hero-card-price-unit"><?= $h($durationText($hcard)) ?> <?= $pricingUnit($hcard) ?></span>
            </div>
            <a class="gp-hero-card-btn" href="<?= $h($hDetailUrl) ?>">
              View details
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
            </a>
          </div>
        </div>
      </article>
      <?php endforeach; ?>
    </div>

    <!-- ════════════════════════════════════════════════════════
         REMAINING SERVICES
         ════════════════════════════════════════════════════════ -->
    <?php if (!empty($remaining)): ?>
    <div class="gp-section-head gp-reveal" style="margin-top:44px;">
      <h2 class="gp-section-title">More Services</h2>
      <span class="gp-section-count">
        <?= count($remaining) ?> more service<?= count($remaining) === 1 ? '' : 's' ?>
      </span>
    </div>

    <div class="gp-grid">
      <?php foreach ($remaining as $ri => $svc):
        $rDetailUrl = URLROOT . '/customerServices/detail/' . (int)$svc['id'] . $detailDateQuery;
      ?>
      <article class="gp-card gp-reveal gp-reveal-d<?= min($ri % 6, 5) ?>">
        <a class="gp-card-img" href="<?= $h($rDetailUrl) ?>" tabindex="-1" aria-hidden="true">
          <?php if (trim((string)($svc['image'] ?? '')) !== ''): ?>
            <img src="<?= $h($svc['image']) ?>" alt="<?= $h($svc['name'] ?? '') ?>" loading="lazy">
          <?php else: ?>
            <div class="gp-card-img-placeholder">
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
            </div>
          <?php endif; ?>
          <span class="gp-card-badge"><?= $h($svc['category'] ?? 'Service') ?></span>
        </a>
        <div class="gp-card-body">
          <div class="gp-card-top">
            <span class="gp-card-supplier" title="<?= $h($svc['supplier_name'] ?? '') ?>">
              <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="opacity:0.5;flex-shrink:0;"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
              <?= $h($svc['supplier_name'] ?? 'Supplier') ?>
            </span>
            <?php if ((float)($svc['rating'] ?? 0) > 0): ?>
            <div class="gp-card-rating">
              <svg width="11" height="11" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
              <?= number_format((float)$svc['rating'], 1) ?>
            </div>
            <?php endif; ?>
          </div>
          <h3 class="gp-card-name">
            <a href="<?= $h($rDetailUrl) ?>"><?= $h($svc['name'] ?? '') ?></a>
          </h3>
          <p class="gp-card-desc"><?= $h($svc['description'] ?? '') ?></p>
          <div class="gp-card-meta">
            <span>
              <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
              <?= $h($durationText($svc)) ?>
            </span>
            <span>
              <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
              <?= $pricingUnit($svc) === '/hr' ? 'Per hour' : 'Per session' ?>
            </span>
          </div>
          <div class="gp-card-foot">
            <div>
              <span class="gp-card-price-amount"><?= $moneyRange($svc) ?></span>
              <span class="gp-card-price-unit"><?= $h($durationText($svc)) ?> <?= $pricingUnit($svc) ?></span>
            </div>
            <a class="gp-card-btn" href="<?= $h($rDetailUrl) ?>">
              View <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
            </a>
          </div>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

  </section>
  <?php else: ?>
  <!-- Empty state -->
  <section class="gp-hero-cards-section">
    <div class="gp-empty gp-reveal">
      <div class="gp-empty-icon"><i data-lucide="search-x" size="28"></i></div>
      <h3>No services found</h3>
      <p>We couldn't find any services matching your criteria. Try adjusting your search, date, or budget.</p>
      <div class="gp-empty-actions">
        <a class="gp-empty-btn primary" href="<?= $resetUrl ?>">Clear all filters</a>
        <a class="gp-empty-btn secondary" href="<?= URLROOT ?>/customerServices/service?category=all">Browse all services</a>
      </div>
    </div>
  </section>
  <?php endif; ?>

</main>

<footer class="gp-footer">
  <span>&copy; <?= date('Y') ?> Golden Promise</span>
  <span>Every vendor is verified and reviewed for quality assurance.</span>
</footer>

<script>
document.addEventListener('DOMContentLoaded', () => {
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

  // Profile dropdown
  const profileBtns = document.querySelectorAll('.gp-profile-btn');
  profileBtns.forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.stopPropagation();
      const expanded = btn.getAttribute('aria-expanded') === 'true';
      document.querySelectorAll('.gp-profile-btn').forEach(b => b.setAttribute('aria-expanded', 'false'));
      btn.setAttribute('aria-expanded', String(!expanded));
    });
  });
  document.addEventListener('click', () => {
    document.querySelectorAll('.gp-profile-btn').forEach(b => b.setAttribute('aria-expanded', 'false'));
  });
});
</script>
</body>
</html>
