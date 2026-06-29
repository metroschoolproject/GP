<?php
$packages = $packages ?? [];
$cartCount = (int)($cartCount ?? 0);
$filters = $filters ?? ['search' => '', 'sort' => 'featured', 'category' => 'all'];
$categories = $categories ?? [];
$hasActiveFilters = $hasActiveFilters ?? false;
$totalServices = $totalServices ?? count($packages);

$isLoggedIn = !empty($_SESSION['session_uid']);
$authNavUrl = $isLoggedIn ? URLROOT . '/users/logout' : URLROOT . '/users/auth';
$authNavLabel = $isLoggedIn ? 'Logout' : 'Sign in';

$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$money = fn($v) => 'MMK ' . number_format((float)$v, 0);

$activeCategory = $_GET['category'] ?? ($filters['category'] ?? 'all');
$activeSort     = $_GET['sort'] ?? ($filters['sort'] ?? 'featured');
$hasPackageTypeFilter = $activeCategory !== 'all';
$hasPriceFilter = $activeSort !== 'featured';
$resetUrl = URLROOT . '/customerServices/packages';
$packageHeroImages = [
    URLROOT . '/app/views/main/images/heroPackage1.png',
    URLROOT . '/app/views/main/images/heroPackage2.png',
];
$packageTierLabels = [
    'standard' => 'Standard',
    'luxury' => 'Luxury',
    'premium' => 'Premium',
];
$packagesByTier = array_fill_keys(array_keys($packageTierLabels), []);
$inferPackageTier = static function (array $pkg): string {
    $haystack = strtolower(trim(implode(' ', [
        $pkg['name'] ?? '',
        $pkg['slug'] ?? '',
        $pkg['tagline'] ?? '',
        $pkg['description'] ?? '',
    ])));
    foreach (($pkg['categories'] ?? []) as $cat) {
        $haystack .= ' ' . strtolower((string)($cat['category_name'] ?? ''));
        $haystack .= ' ' . strtolower((string)($cat['category_slug'] ?? ''));
    }
    if (str_contains($haystack, 'luxury')) return 'luxury';
    if (str_contains($haystack, 'premium')) return 'premium';
    if (str_contains($haystack, 'standard')) return 'standard';
    return 'standard';
};
foreach ($packages as $pkg) {
    $tierKey = $inferPackageTier((array)$pkg);
    $packagesByTier[$tierKey][] = $pkg;
}

$sortPackageList = static function (array &$list) use ($activeSort): void {
    if (!in_array($activeSort, ['price_low', 'price_high'], true)) {
        return;
    }
    usort($list, function ($a, $b) use ($activeSort) {
        $priceA = (float)($a['package_price'] ?? $a['base_price'] ?? 0);
        $priceB = (float)($b['package_price'] ?? $b['base_price'] ?? 0);
        return $activeSort === 'price_low' ? ($priceA <=> $priceB) : ($priceB <=> $priceA);
    });
};

foreach ($packagesByTier as $tierKey => $tierPackages) {
    $sortPackageList($tierPackages);
    $packagesByTier[$tierKey] = $tierPackages;
}

$visiblePackages = $packages;
if ($hasPackageTypeFilter) {
    $visiblePackages = array_filter($visiblePackages, function ($pkg) use ($activeCategory, $inferPackageTier) {
        return $inferPackageTier((array)$pkg) === $activeCategory;
    });
}
$visiblePackages = array_values($visiblePackages);
$sortPackageList($visiblePackages);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Packages — Golden Promise</title>
<?php include APPROOT . '/views/partials/ga-tracking.php'; ?>
<?php
$publicCssVersion = file_exists(APPROOT . '/../public/css/app.css') ? filemtime(APPROOT . '/../public/css/app.css') : time();
?>
<link rel="stylesheet" href="<?= URLROOT ?>/public/css/app.css?v=<?= $publicCssVersion ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Ballet:opsz@16..72&family=Great+Vibes&family=Playfair+Display:wght@400;500;600;700&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
<style>
:root {
  --c-bg:        #f5e8d9;
  --c-surface:   #faf5ef;
  --c-white:     #fcf8f5;
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
  --sh-card:   0 20px 40px rgba(15, 23, 42, 0.08);
  --sh-panel:  0 18px 45px rgba(15, 23, 42, 0.06);

  --font-display: 'Playfair Display', Georgia, serif;
  --font-body:    'Poppins', system-ui, -apple-system, sans-serif;
  --pad-x: clamp(20px, 5vw, 72px);
  --ease-out-expo: cubic-bezier(0.19, 1, 0.22, 1);
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body {
  background: #ead8c8;
  color: var(--c-text);
  font-family: var(--font-body);
  font-size: 14px;
  line-height: 1.6;
  -webkit-font-smoothing: antialiased;
}
a { color: inherit; text-decoration: none; }
img { display: block; max-width: 100%; }

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
  color: #fcf8f5;
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

/* ─── HERO ───────────────────────────────── */
.gp-pkg-hero {
  position: relative;
  min-height: min(880px, 84vh);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 54px var(--pad-x) 150px;
  overflow: hidden;
  background: linear-gradient(135deg, #6d4c5b 0%, #c7a078 100%);
  color: #fffaf3;
  border-radius: 0;
  filter: drop-shadow(0 24px 22px rgba(65, 42, 53, 0.26));
 -webkit-mask-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 1440 720' preserveAspectRatio='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath fill='black' d='M0 0H1440V655C1320 672 1200 682 1080 674C960 666 840 640 720 652C600 664 480 682 360 674C240 666 120 640 0 654V0Z'/%3E%3C/svg%3E");
mask-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 1440 720' preserveAspectRatio='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath fill='black' d='M0 0H1440V655C1320 672 1200 682 1080 674C960 666 840 640 720 652C600 664 480 682 360 674C240 666 120 640 0 654V0Z'/%3E%3C/svg%3E");
  -webkit-mask-size: 100% 100%;
  mask-size: 100% 100%;
  -webkit-mask-repeat: no-repeat;
  mask-repeat: no-repeat;
}
.gp-pkg-hero::before {
  content: none;
}
.gp-pkg-hero-bg {
  position: absolute;
  inset: 0;
  background-size: cover;
  background-position: center 64%;
  background-repeat: no-repeat;
  filter: none;
  opacity: 0;
  transform: none;
  transition: opacity 1s ease-in-out;
  pointer-events: none;
}
.gp-pkg-hero-bg.is-active {
  opacity: 1;
}
.gp-pkg-hero::after {
  content: '';
  position: absolute;
  inset: 0;
  z-index: 0;
  background: linear-gradient(rgba(0,0,0,.65), rgba(0,0,0,.45));
  pointer-events: none;
}
.gp-pkg-hero-inner {
  position: relative;
  z-index: 2;
  width: min(760px, 100%);
  max-width: 760px;
  margin: 42px auto 0;
  text-align: center;
}
.gp-pkg-hero-overline {
  display: none;
  margin-bottom: 38px;
  color: #fffaf3;
  font-size: clamp(22px, 2.6vw, 34px);
  font-weight: 800;
  letter-spacing: 0;
  text-transform: lowercase;
  font-style: italic;
}
.gp-pkg-hero h1 {
  max-width: 620px;
  margin: 0 auto;
  font-family: 'Playfair Display', serif;
  font-size: clamp(42px, 5.5vw, 62px);
  font-weight: 500;
  line-height: 1.04;
  color: #fffaf3;
  letter-spacing: 4px;
}
.gp-pkg-hero h1 span {
  display: block;
}
.gp-pkg-hero .gp-script-word {
  display: inline-block;
  font-family: 'Great Vibes', 'Ballet', cursive;
  font-size: 1.28em;
  font-style: normal;
  font-weight: 400;
  letter-spacing: 0;
  line-height: .84;
  text-transform: none;
}
.gp-pkg-hero h1 em { font-style: normal; color: inherit; }
.gp-pkg-hero p {
  max-width: 600px;
  margin: 24px auto 0;
  font-size: 14px;
  line-height: 1.7;
  color: rgba(255,250,243,0.86);
}
.gp-pkg-hero h1,
.gp-pkg-hero p,
.gp-pkg-hero .gp-search-boutique,
.gp-pkg-hero .gp-search-field-boutique.is-search,
.gp-pkg-hero .gp-search-filter-row > * {
  opacity: 0;
  transform: translateY(18px) scale(.94);
}
.gp-pkg-hero.is-in h1 { animation: heroPopOut .72s var(--ease-out-expo) .04s forwards; }
.gp-pkg-hero.is-in p { animation: heroPopOut .66s var(--ease-out-expo) .18s forwards; }
.gp-pkg-hero.is-in .gp-search-boutique { animation: heroPopOut .68s var(--ease-out-expo) .30s forwards; }
.gp-pkg-hero.is-in .gp-search-field-boutique.is-search { animation: heroPopOut .62s var(--ease-out-expo) .42s forwards; }
.gp-pkg-hero.is-in .gp-search-filter-row > * { animation: heroPopOut .52s var(--ease-out-expo) forwards; }
.gp-pkg-hero.is-in .gp-search-filter-row > *:nth-child(1) { animation-delay: .52s; }
.gp-pkg-hero.is-in .gp-search-filter-row > *:nth-child(2) { animation-delay: .58s; }
@keyframes heroPopOut {
  0% { opacity: 0; transform: translateY(18px) scale(.94); }
  68% { opacity: 1; transform: translateY(-4px) scale(1.035); }
  100% { opacity: 1; transform: translateY(0) scale(1); }
}

/* ─── BOUTIQUE DIVIDER ────────────────────────────────── */
.gp-divider-boutique {
  display: none;
}
.gp-divider-line-deco {
  flex: 1; max-width: 120px;
  height: 1px;
  background: linear-gradient(to right, transparent, var(--c-rule), transparent);
}
.gp-divider-text {
  font-family: var(--font-display);
  font-size: 15px; font-weight: 500; letter-spacing: 0.08em;
  color: var(--c-muted); font-style: italic;
  white-space: nowrap;
}
.gp-divider-diamond {
  width: 6px; height: 6px;
  border: 1px solid var(--c-muted);
  transform: rotate(45deg);
  opacity: 0.4;
}

/* ─── BOUTIQUE SEARCH PANEL ──────────────────────────── */
.gp-search-boutique {
  position: relative;
  z-index: 4;
  width: min(620px, 100%);
  margin: 24px auto 0;
  padding: 0;
}
.gp-search-panel {
  width: 100%;
  max-width: none;
  margin: 0;
  background: transparent;
  border: 0;
  border-radius: 0;
  box-shadow: none;
  padding: 0;
  transition: background 0.2s, border-color 0.2s, box-shadow 0.3s;
}
.gp-search-panel:focus-within {
  background: transparent;
  border-color: transparent;
  box-shadow: none;
}
.gp-search-rows {
  display: flex; flex-direction: column; gap: 8px;
}
.gp-search-row-fields {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 8px;
  min-width: 0;
}
.gp-search-filter-row {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  width: 100%;
  flex-wrap: wrap;
}
.gp-search-field-boutique {
  position: relative;
  display: inline-flex;
  align-items: center;
  min-height: 50px;
  padding: 0;
  border-right: 0;
  flex-shrink: 0;
}
.gp-search-field-boutique label {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0,0,0,0);
  white-space: nowrap;
  border: 0;
}
.gp-search-field-boutique input,
.gp-search-field-boutique select {
  appearance: none;
  -webkit-appearance: none;
  transition: border-color 0.2s, background 0.2s, color 0.2s;
}
.gp-search-field-boutique input {
  width: 100%;
  min-height: 50px;
  padding: 0 52px 0 40px;
  border: 0.5px solid rgba(118,90,70,.24);
  border-radius: 14px;
  background: rgba(245,232,217,.88);
  color: #4f382a;
  font-size: 14px;
  font-weight: 600;
}
.gp-search-field-boutique select {
  min-height: 34px;
  min-width: 128px;
  max-width: 172px;
  padding: 7px 34px 7px 14px;
  border: 0.5px solid rgba(118,90,70,.20);
  border-radius: 8px;
  background-color: rgba(245,232,217,.82);
  background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 24 24' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='m6 9 6 6 6-6' stroke='%239A687F' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 12px center;
  background-size: 12px 12px;
  color: #765a46;
  font-size: 11px;
  font-weight: 700;
  box-shadow: 0 10px 24px rgba(43,31,24,.12);
  cursor: pointer;
}
.gp-search-field-boutique.is-search input {
  padding-right: 34px;
}
.gp-search-field-boutique.is-search {
  width: 100%;
}
.gp-search-leading-icon {
  position: absolute;
  left: 18px;
  top: 50%;
  transform: translateY(-50%);
  width: 13px;
  height: 13px;
  color: #765a46;
  opacity: .72;
  pointer-events: none;
  z-index: 1;
}
.gp-search-field-boutique input:hover,
.gp-search-field-boutique select:hover {
  background-color: #fff8ef;
  border-color: rgba(154,104,127,.36);
  color: #4f382a;
}
.gp-search-field-boutique input:focus,
.gp-search-field-boutique select:focus {
  outline: none;
  background-color: #fff8ef;
  border-color: rgba(154,104,127,.42);
  box-shadow: 0 10px 26px rgba(63,36,26,.10);
}
.gp-search-field-boutique input::placeholder { color: rgba(118,90,70,.58); font-weight: 400; }
.gp-search-field-boutique select { cursor: pointer; }
.gp-pkg-select-wrap {
  position: relative;
  display: inline-flex;
  align-items: center;
  flex-shrink: 0;
}
.gp-pkg-select { accent-color: #6D4C5B; }
.gp-pkg-select.is-native-hidden {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  opacity: 0;
  pointer-events: none;
}
.gp-pkg-select-trigger {
  min-width: 142px;
  max-width: 190px;
  min-height: 40px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  border: 0.5px solid rgba(118,90,70,.20);
  border-radius: 8px;
  padding: 9px 14px 9px 16px;
  background: rgba(245,232,217,.90);
  color: #765a46;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  box-shadow: 0 10px 24px rgba(43,31,24,.12);
}
.gp-pkg-select-trigger:hover,
.gp-pkg-select-wrap.is-open .gp-pkg-select-trigger {
  background: #FFF8EF;
  border-color: rgba(154,104,127,.36);
  color: #4f382a;
}
.gp-pkg-select-trigger-text {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.gp-pkg-select-trigger svg {
  width: 14px;
  height: 14px;
  flex: 0 0 14px;
  stroke: #6D4C5B;
}
.gp-pkg-select-popover {
  position: fixed;
  left: 0;
  top: 0;
  z-index: 10020;
  min-width: 156px;
  max-height: 220px;
  overflow-y: auto;
  overscroll-behavior: contain;
  padding: 6px;
  border: 1px solid rgba(154,104,127,.20);
  border-radius: 9px;
  background: #FFF8EF;
  box-shadow: 0 18px 40px rgba(63,36,26,.18);
}
.gp-pkg-select-popover::-webkit-scrollbar { width: 5px; }
.gp-pkg-select-popover::-webkit-scrollbar-track { background: rgba(154,104,127,.08); border-radius: 999px; }
.gp-pkg-select-popover::-webkit-scrollbar-thumb { background: rgba(154,104,127,.45); border-radius: 999px; }
.gp-pkg-select-item {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  border: 0;
  border-radius: 7px;
  padding: 10px 12px;
  background: transparent;
  color: #5b3b2d;
  font-size: 13px;
  font-weight: 700;
  text-align: left;
  cursor: pointer;
}
.gp-pkg-select-item:hover,
.gp-pkg-select-item:focus {
  background: rgba(154,104,127,.14);
  color: #3F241A;
}
.gp-pkg-select-item.is-selected {
  background: #6D4C5B;
  color: #fff8ef;
}
.gp-pkg-select-dot { display: none !important; }
.gp-pkg-sort ~ .gp-pkg-select-trigger {
  min-width: 126px;
  max-width: 148px;
}

.gp-search-btn {
  position: absolute;
  right: 6px;
  top: 50%;
  transform: translateY(-50%);
  display: inline-grid;
  place-items: center;
  width: 38px;
  height: 38px;
  padding: 0;
  border-radius: 10px;
  border: 0;
  background: #765a46;
  color: #fcf8f5;
  opacity: 1;
  cursor: pointer;
  transition: color 0.2s, background 0.2s;
  box-shadow: none;
}
.gp-search-btn svg {
  width: 13px;
  height: 13px;
  stroke: currentColor;
}
.gp-search-btn:hover {
  color: #fcf8f5;
  background: #4f382a;
  transform: translateY(-50%);
  box-shadow: none;
}
.gp-search-btn:active {
  transform: translateY(-50%);
}

/* ─── BOUTIQUE FILTER CHIPS ──────────────────────────── */
.gp-active-filters-boutique {
  display: flex; gap: 8px; flex-wrap: wrap;
  padding: 18px var(--pad-x) 0;
  margin-top: 0;
}
.gp-filter-chip-boutique {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 6px 8px 6px 14px;
  border-radius: 999px;
  background: linear-gradient(135deg, rgba(212,160,71,0.08), rgba(212,160,71,0.04));
  border: 1px solid rgba(212,160,71,0.18);
  font-size: 12px; font-weight: 600; color: #8b6f3e;
  box-shadow: 0 1px 3px rgba(212,160,71,0.06);
}
.gp-filter-chip-boutique-remove {
  display: grid; place-items: center;
  width: 18px; height: 18px; border-radius: 50%;
  border: none;
  background: rgba(107,114,128,0.12); color: #6b7280;
  cursor: pointer; font-size: 10px; font-weight: 700;
  transition: all 0.15s;
  line-height: 1;
}
.gp-filter-chip-boutique-remove:hover {
  background: #6b7280;
  color: #ffffff;
  box-shadow: 0 2px 6px rgba(107,114,128,0.22);
}

/* ─── HERO DATE PICKER (service-detail style) ──────── */
.gp-pkg-date-form {
  display: flex;
  align-items: center;
  justify-content: center;
  margin-top: 14px;
}
.venue-date-input-wrap {
  position: relative;
  min-height: 36px;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  border: 1px solid rgba(255, 248, 239, .28);
  border-radius: 8px;
  background: rgba(255, 248, 239, .14);
  color: #FFF8EF;
  padding: 0 12px;
  font-size: 13px;
  font-weight: 700;
  cursor: pointer;
  overflow: hidden;
  backdrop-filter: blur(6px);
  -webkit-backdrop-filter: blur(6px);
  box-shadow: 0 4px 14px rgba(0, 0, 0, .12);
  transition: background .15s, border-color .15s;
}
.venue-date-input-wrap:hover {
  background: rgba(255, 248, 239, .24);
  border-color: rgba(255, 248, 239, .42);
}
.venue-date-input-wrap input {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  opacity: 0;
  cursor: pointer;
}
.venue-date-display {
  min-width: 68px;
  pointer-events: none;
  font-size: 13px;
  font-weight: 700;
}
.venue-date-icon,
.venue-date-chevron {
  flex: 0 0 auto;
  pointer-events: none;
  color: rgba(255, 248, 239, .72);
  width: 14px !important;
  height: 14px !important;
  stroke-width: 2.2;
}
.venue-date-chevron {
  margin-left: auto;
}
.gp-calendar-popover {
  position: fixed;
  z-index: 10010;
  width: min(250px, calc(100vw - 32px));
  padding: 12px;
  border: 1px solid rgba(63, 36, 26, .14);
  border-radius: 10px;
  background: rgba(255, 248, 239, .98);
  box-shadow: 0 24px 60px rgba(63, 36, 26, .18);
  backdrop-filter: blur(18px);
  -webkit-backdrop-filter: blur(18px);
}
.gp-calendar-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  color: #3F241A;
  font-size: 12px;
  font-weight: 900;
  margin-bottom: 9px;
}
.gp-calendar-nav {
  width: 22px;
  height: 22px;
  display: inline-grid;
  place-items: center;
  border: 0;
  border-radius: 7px;
  background: transparent;
  color: #7A4E3D;
  cursor: pointer;
}
.gp-calendar-nav:hover {
  background: rgba(63, 36, 26, .08);
}
.gp-calendar-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 3px;
}
.gp-calendar-day-name,
.gp-calendar-day {
  display: grid;
  place-items: center;
  height: 24px;
  color: #6F5448;
  font-size: 11px;
}
.gp-calendar-day-name {
  color: rgba(63, 36, 26, .52);
  font-weight: 800;
}
.gp-calendar-day {
  border: 0;
  border-radius: 6px;
  background: transparent;
  font-weight: 800;
  cursor: pointer;
}
.gp-calendar-day:hover {
  background: rgba(122, 78, 61, .12);
}
.gp-calendar-day.is-selected {
  background: #3F241A;
  color: #FFF8EF;
}
.gp-calendar-day.is-today:not(.is-selected) {
  outline: 1px solid rgba(63, 36, 26, .28);
}
.gp-calendar-day.is-disabled {
  color: rgba(63, 36, 26, .24);
  cursor: not-allowed;
}

.gp-tier-showcase {
  position: relative;
  z-index: 5;
  margin-top: 0;
  padding: 42px var(--pad-x) 0;
  background: #ead8c8;
}
.gp-tier-top {
  position: relative;
  max-width: 1280px;
  margin: 0 auto 26px;
  display: flex;
  align-items: end;
  justify-content: center;
  gap: 24px;
}
.gp-tier-heading {
  margin: 0;
  text-align: center;
  color: #111827;
  font-size: clamp(34px, 4vw, 56px);
  line-height: 1;
  font-weight: 300;
}
.gp-tier-heading strong {
  font-weight: 800;
}
.gp-tier-top::after {
  content: '';
  position: absolute;
  left: 50%;
  bottom: -20px;
  width: min(560px, 72vw);
  height: 1px;
  transform: translateX(-50%);
  background: linear-gradient(90deg, transparent 0%, rgba(216,180,106,.26) 14%, rgba(216,180,106,.82) 50%, rgba(216,180,106,.26) 86%, transparent 100%);
}
.gp-package-type-sections {
  display: grid;
  gap: 44px;
  max-width: 1440px;
  margin: 58px auto 0;
}
.gp-package-type-section {
  display: grid;
  grid-template-columns: minmax(240px, 340px) minmax(0, 1fr);
  align-items: center;
  gap: 42px;
  min-height: auto;
  padding: 34px 0;
}
.gp-package-type-intro{
    display:flex;
    flex-direction:column;
    justify-content:center;
    align-items:flex-start;
    min-height:360px;
    margin:0;
    padding:24px 10px 24px 0;
    background:transparent;
    border:none;
    border-radius:0;
    text-align:left;
}

.gp-package-type-title{
    margin:0;
    font-family:'Playfair Display', serif;
    font-size:clamp(44px, 4.6vw, 68px);
    font-weight:700;
    line-height:.95;
    color:#211d1a;
    letter-spacing:0;
}
.gp-package-type-label{
    display:flex;
    align-items:center;
    gap:12px;
    margin-bottom:20px;
    color:#9A687F;
    font-size:12px;
    font-weight:900;
    letter-spacing:.20em;
    text-transform:uppercase;
}
.gp-package-type-label::before{
    content:'';
    width:48px;
    height:2px;
    background:rgba(154,104,127,.32);
}
.gp-package-type-copy{
    display:block;
    margin-top:16px;
    max-width:310px;
    font-size:15px;
    line-height:1.65;
    color:#6f625a;
    font-weight:700;
}

.gp-package-type-list {
  display: none;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 12px;
  list-style: none;
  color: #4b5563;
  font-size: 12px;
  font-weight: 700;
}
.gp-package-type-list li {
  display: flex;
  gap: 9px;
  align-items: center;
  min-height: 0;
  padding: 0;
  border: 0;
  border-radius: 0;
  background: transparent;
}
.gp-package-type-cards {
  position: relative;
  min-width: 0;
  display: grid;
  align-items: center;
}
.gp-package-type-carousel-head {
  display: flex;
  align-items: center;
  justify-content: flex-start;
  min-height: 0;
  margin: 24px 0 0;
}
.gp-package-type-nav {
  display: inline-flex;
  align-items: center;
  gap: 8px;
}
.gp-package-type-nav[hidden] {
  display: none;
}
.gp-package-type-nav button {
  display: inline-grid;
  place-items: center;
  width: 32px;
  height: 32px;
  border: 1px solid rgba(216,180,106,.72);
  border-radius: 50%;
  background: rgba(255,248,239,.72);
  color: #6D4C5B;
  cursor: pointer;
  transition: transform .2s var(--ease-out-expo), background .2s var(--ease-out-expo), opacity .2s var(--ease-out-expo);
}
.gp-package-type-nav button:hover {
  transform: translateY(-2px);
  background: #fff8ef;
}
.gp-package-type-nav button:disabled {
  cursor: default;
  opacity: .38;
  transform: none;
}
.gp-package-type-viewport {
  overflow: hidden;
  min-width: 0;
  min-height: 470px;
  padding: 18px 0 0;
  grid-row: 1;
}
.gp-package-type-track{
    display:flex;
    justify-content:flex-start;
    gap:28px;
}

.gp-filtered-package-results {
  padding-top: 42px;
}
.gp-filtered-package-viewport {
  max-width: 1280px;
  margin: 0 auto;
  min-height: 0;
  overflow: visible;
  padding: 0 0 46px;
}
.gp-filtered-package-track{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(360px,420px));
    justify-content:center;
    gap:30px;
}
.gp-filtered-package-track .gp-package-type-card {
  flex: none;
}
.gp-package-type-card{
    position:relative;
    display:flex;
    flex-direction:column;
    align-items:center;
    text-align:center;

    width:400px;
    max-width:400px;
    flex:0 0 400px;

    min-height:470px;
    padding:12px;
    border-radius:24px;
    background:#fff8ef;
    border:1px solid rgba(201,193,187,.58);
    box-shadow:0 18px 42px rgba(63,36,26,.13);
}

.gp-package-type-card:hover{
  transform:translateY(-6px);
  box-shadow:0 20px 42px rgba(63,36,26,.16);
}





.gp-package-type-card p{
  margin-top:8px;
  color:#6f625c;
  font-size:13px;
  line-height:1.55;
  font-weight:400;
}

.gp-package-type-meta{
  margin-top:auto;
  padding-top:18px;
  border-top:0;
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:14px;
}

.gp-package-type-price{
  color:#7d3f3f;
  font-size:18px;
  font-weight:700;
  white-space:nowrap;
}

.gp-card-book{
    width:100%;          /* Change to 65%, 70%, or 75% as you prefer */
    max-width:100%;

    min-height:48px;
    margin:0 auto;

    padding:6px 8px 6px 20px;

    border-radius:20px;

    display:flex;
    align-items:center;
    justify-content:space-between;

    background:#6D4C5B;
    color:#fff8ef;

    font-size:15px;
    font-weight:700;
}
.gp-package-type-card.is-jumping {
  animation: packageJumpUp .58s var(--ease-out-expo) both;
}
.gp-package-type-card h4 {
  text-align: center;
  color: #111827;
  font-size: clamp(16px, 1.35vw, 21px);
  line-height: 1.05;
  font-weight: 500;
}

.gp-package-type-card{
  position:relative;
  display:flex;
  flex-direction:column;
  align-items:center;
  text-align:center;

  min-height:470px;
  padding:12px;
  border-radius:24px;
  background:#fff8ef;
  border:1px solid rgba(201,193,187,.58);
  box-shadow:0 18px 42px rgba(63,36,26,.13);
  color:#211d1a;
  overflow:hidden;
  transition:.25s ease;
}

.gp-package-type-card:hover{
  transform:translateY(-7px);
  box-shadow:0 24px 52px rgba(63,36,26,.17);
}

.gp-package-type-image{
  position:relative;
  width:100%;
  height:250px;
  border-radius:18px;
  overflow:hidden;
  border:0;
  background:#f5e8d9;
  
}

.gp-package-type-image img{
  width:100%;
  height:100%;
  object-fit:cover;
}
.gp-package-type-image .gp-heart{
  position:absolute;
  top:14px;
  right:14px;
  z-index:10;
  display:grid;
  place-items:center;
  width:34px;
  height:34px;
  border-radius:50%;
  border:none;
  background:rgba(255,248,239,.94);
  color:#6D4C5B;
  cursor:pointer;
  box-shadow:0 8px 18px rgba(63,36,26,.14);
}

.gp-package-type-image .gp-heart:hover{
  transform:translateY(-1px);
  background:#fff8ef;
}

.gp-package-type-image .gp-heart.is-saved{
  color:#e55b5b;
  background:#fff8ef;
}
.gp-service-count-tag{
  position:absolute;
  left:14px;
  bottom:14px;
  z-index:3;
  padding:6px 12px;
  border-radius:8px;
  background:#f0dfe7;
  color:#7E4F65;
  font-size:11px;
  font-weight:800;
}

.gp-package-type-card h4{
  margin:18px 0 8px;
  text-align:center;
  font-size:18px;
  font-weight:800;
  color:#211d1a;
}

.gp-package-type-card p{
  max-width:300px;
  margin:0 auto 12px;
  text-align:center;
  color:#6f625a;
  font-size:13px;
  line-height:1.6;
  font-weight:500;
}

.gp-package-type-meta{
  width:100%;
  margin-top:auto;
  padding-top:0;
  border-top:0;
  display:flex;
  flex-direction:column;
  align-items:center;
  justify-content:center;
  gap:14px;
}

.gp-package-type-price{
  text-align:center;
  color:#7E4F65;
  font-size:18px;
  font-weight:800;
}



.gp-card-book-icon{
  width:40px;
  height:40px;
  border-radius:50%;
  background:#fff8ef;
  color:#6D4C5B;

  display:grid;
  place-items:center;
}

.gp-card-book-icon svg{
  width:18px;
  height:18px;
  stroke:currentColor;
}

/* same style as service card heart */
.gp-heart{
  position:absolute;
  top:22px;
  right:22px;
  z-index:10;
  display:grid;
  place-items:center;
  width:34px;
  height:34px;
  border-radius:50%;
  border:none;
  background:rgba(255,248,239,.94);
  color:#6D4C5B;
  cursor:pointer;
  box-shadow:0 8px 18px rgba(63,36,26,.14);
  transition:all .2s ease;
}

.gp-heart:hover{
  transform:translateY(-1px);
  background:#fff8ef;
}

.gp-heart.is-saved{
  color:#e55b5b;
  background:#fff8ef;
}
.gp-heart.is-loading{
  pointer-events:none;
  opacity:.62;
}
.gp-package-type-count {
  color: rgba(32,26,29,.54);
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .08em;
  white-space: nowrap;
}
.gp-package-type-empty {
  display: grid;
  place-items: center;
  min-height: 260px;
  grid-column: 1 / -1;
  border: 1px dashed rgba(154,104,127,.28);
  border-radius: 22px;
  color: rgba(32,26,29,.54);
  font-size: 13px;
  font-weight: 600;
  background: rgba(255,248,239,.38);
}
.gp-tier-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  align-items: start;
  gap: 28px;
  max-width: 1280px;
  margin: 18px auto 0;
  padding-top: 36px;
}
.gp-tier-grid.is-filtered {
  grid-template-columns: minmax(280px, 420px);
  justify-content: center;
}
.gp-tier-card {
  position: relative;
  min-height: 430px;
  overflow: hidden;
  border-radius: 24px;
  background: #fffdf9;
  border: 1px solid rgba(216,180,106,.72);
  box-shadow: 0 16px 34px rgba(63,36,26,.10);
  color: #2b1b24;
  isolation: isolate;
  padding: 12px;
  display: flex;
  flex-direction: column;
}
.gp-tier-card .gp-tier-panel h2 {
  transform: translateX(205px);
  margin: 0 0 22px;
  font-size: 25px;
  line-height: 1.1;
  font-weight: 400;
}
.gp-tier-card.is-switching {
  animation: packageJumpUp .58s var(--ease-out-expo) both;
}
.gp-tier-card::before {
  content: '';
  position: absolute;
  top: -42%;
  bottom: -42%;
  left: -82%;
  width: 54%;
  z-index: 4;
  background: linear-gradient(115deg, transparent 0%, rgba(255,255,255,.10) 34%, rgba(255,255,255,.62) 50%, rgba(255,255,255,.12) 66%, transparent 100%);
  transform: skewX(-22deg);
  pointer-events: none;
}
.gp-tier-card:hover {
  transform: translateY(-6px) scale(1.035);
  box-shadow: 0 36px 70px rgba(207,161,116,.32), inset 0 0 0 1px rgba(255,255,255,.62);
}
.gp-tier-card:hover::before {
  animation: pkgMirror .78s var(--ease-out-expo);
}
.gp-tier-card--standard {
  --tier-bg: #FFF8EF;
  --tier-panel: #E7E7E7;
  --tier-start: #E7E7E7;
  --tier-end: #E7E7E7;
  --tier-button: #9A687F;
  --tier-border: #D8B46A;
  margin-top: 26px;
}
.gp-tier-card--luxury {
  --tier-bg: #FFF9F3;
  --tier-start: #F6E7D5;
  --tier-end: #E8C7A4;
  --tier-button: #9A687F;
  --tier-border: #D8B46A;
  min-height: 468px;
  margin-top: 10px;
  transform: scale(1.035);
  border-width: 1px;
  box-shadow: 0 28px 58px rgba(207,161,116,.22), inset 0 0 0 1px rgba(255,255,255,.55);
  z-index: 2;
}
.gp-tier-card--premium {
  --tier-bg: #FFF8EF;
  --tier-panel: #E7E7E7;
  --tier-start: #E7E7E7;
  --tier-end: #E7E7E7;
  --tier-button: #9A687F;
  --tier-border: #D8B46A;
  margin-top: 26px;
}
.gp-tier-panel {
  min-height: 250px;
  border-radius: 18px;
  padding: 24px 24px 22px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  color: #201a1d;
  border: 1px solid rgba(255,255,255,.56);
}
.gp-tier-card--standard .gp-tier-panel {
  background: var(--tier-panel);
}
.gp-tier-card--luxury .gp-tier-panel {
  min-height: 284px;
  background: linear-gradient(135deg, var(--tier-start) 0%, var(--tier-end) 100%);
}
.gp-tier-card--premium .gp-tier-panel {
  background: var(--tier-panel);
}
.gp-tier-kicker {
  display: none;
  margin-bottom: 0;
  color: rgba(32,26,29,.72);
  font-size: 13px;
  font-weight: 800;
  letter-spacing: 0;
  text-transform: uppercase;
}
.gp-tier-card h2 {
  padding-left: 20px;
  margin: 0 0 22px;
  font-size: 30px;
  line-height: 1.1;
  font-weight: 700;
}
.gp-tier-card p {
  max-width: 340px;
  color: rgba(32,26,29,.76);
  font-size: 14px;
  line-height: 1.42;
  font-weight: 600;
}
.gp-tier-action {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-height: 44px;
  width: 100%;
  margin-top: 20px;
  border-radius: 10px;
  background: var(--tier-button);
  color: #fffaf3;
  font-size: 13px;
  font-weight: 700;
  box-shadow: 0 12px 24px rgba(23,23,25,.18);
}
.gp-tier-price {
  display: block;
  margin: 0 0 10px;
  text-align: left;
  color: #201a1d;
  font-size: clamp(30px, 3.4vw, 46px);
  font-weight: 500;
  line-height: .95;
}
.gp-tier-features {
  list-style: none;
  display: grid;
  gap: 14px;
  padding: 24px 18px 8px;
  color: #4b5563;
  font-size: 14px;
  font-weight: 600;
  flex: 1;
}
.gp-tier-features li {
  display: flex;
  align-items: center;
  gap: 10px;
  line-height: 1.4;
}
.gp-tier-features li::before {
  content: '✓';
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 18px;
  height: 18px;
  flex: 0 0 18px;
  border-radius: 50%;
  border: 1px solid rgba(107,114,128,.38);
  color: #6b7280;
  font-size: 10px;
  font-weight: 900;
}
.gp-pkg-heading {
  margin: 0 auto 46px;
  text-align: center;
  color: #111827;
  font-size: clamp(34px, 4vw, 56px);
  line-height: 1;
  font-weight: 300;
}
.gp-pkg-heading strong {
  font-weight: 800;
}
.gp-pkg-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 20px 18px;
}
.gp-pkg-card {
  position: relative;
  height: 360px;
  min-height: 360px;
  color: var(--c-text);
  text-decoration: none;
  background: #fff8ef;
  border: 1.5px solid #D8B46A;
  border-radius: 16px;
  padding: 2px 2px 2px;
  overflow: hidden;
  box-shadow: 0 14px 34px rgba(63,36,26,.12);
  transition: transform .22s var(--ease-out-expo), box-shadow .22s var(--ease-out-expo), border-color .22s var(--ease-out-expo);
  display: flex;
  flex-direction: column;
}
.gp-pkg-card:hover {
  transform: translateY(-6px);
  border-color: rgba(216,180,106,.72);
  box-shadow: 0 20px 42px rgba(63,36,26,.16);
}
@keyframes pkgMirror {
  from { left: -82%; }
  to { left: 128%; }
}
.gp-tier-card.visible,
.gp-pkg-card.visible {
  animation: cardFlyIn 1.05s var(--ease-out-expo) both;
  animation-delay: var(--card-reveal-delay, 0s);
}
.gp-tier-card--luxury.visible {
  animation-name: luxuryCardFlyIn;
  transform: scale(1.035);
}
.gp-tier-card--luxury.visible:hover {
  transform: scale(1.045);
}
@keyframes cardFlyIn {
  0% { opacity: 0; transform: translate3d(0,38px,0) scale(.985); }
  62% { opacity: 1; transform: translate3d(0,-5px,0) scale(1.01); }
  100% { opacity: 1; transform: translate3d(0,0,0) scale(1); }
}
@keyframes luxuryCardFlyIn {
  0% { opacity: 0; transform: translate3d(0,38px,0) scale(.985); }
  62% { opacity: 1; transform: translate3d(0,-5px,0) scale(1.045); }
  100% { opacity: 1; transform: translate3d(0,0,0) scale(1.035); }
}
@keyframes packageJumpUp {
  0% { opacity: 0; transform: translateY(28px) scale(.98); }
  58% { opacity: 1; transform: translateY(-8px) scale(1.01); }
  100% { opacity: 1; transform: translateY(0) scale(1); }
}
.gp-pkg-visual {
  position: relative;
  display: block;
  height: 198px;
  flex: 0 0 198px;
  overflow: hidden;
  border: 1px solid rgba(216,180,106,.46);
  border-radius: 13px;
  background: linear-gradient(160deg, #ede0d0, #ddcebb);
}
.gp-pkg-visual::after {
  content: none;
}
.gp-pkg-visual img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center center;
  border-radius: 12px;
  transition: transform 0.5s var(--ease-out-expo);
}
.gp-pkg-card:hover .gp-pkg-visual img { transform: scale(1.04); }
.gp-pkg-visual .gp-pkg-badge {
  position: absolute; top: 10px; left: 10px; z-index: 2;
  background: rgba(255,248,239,0.9);
  border: 1px solid rgba(216,180,106,.36);
  border-radius: 999px;
  padding: 4px 10px;
  font-size: 9px; font-weight: 800; color: #6D4C5B;
  letter-spacing: 0.04em;
  text-transform: uppercase;
}
.gp-pkg-body {
  position: relative;
  z-index: 1;
  padding: 14px 14px 0;
  display: flex;
  flex: 1;
  flex-direction: column;
  min-height: 0;
  background: transparent;
  color: var(--c-text);
}
.gp-pkg-name {
  font-family: var(--font-body);
  font-size: 15px; font-weight: 800; line-height: 1.18;
  color: #2b1b24; margin-bottom: 4px;
}
.gp-pkg-tagline {
  font-size: 11px;
  color: #7f6758;
  line-height: 1.35;
  margin-bottom: 8px;
  flex: 1;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.gp-pkg-cats {
  display: flex; gap: 5px; flex-wrap: wrap;
  margin-bottom: 8px;
}
.gp-pkg-cat-pill {
  padding: 3px 8px;
  border-radius: 999px;
  background: rgba(109,76,91,0.08);
  font-size: 9px; font-weight: 800; color: #6D4C5B;
}
.gp-pkg-foot {
  display: flex; align-items: center; justify-content: space-between; gap: 12px;
  margin-top: auto;
  padding-top: 8px;
  border-top: 1px solid rgba(216,180,106,.28);
}
.gp-pkg-price {
  font-family: var(--font-body);
  font-size: 14px;
  font-weight: 500;
  color: #6D4C5B;
  line-height: 1;
}
.gp-pkg-price-label {
  display: block; margin-top: 1px;
  font-size: 9px; color: #a28c7e; font-weight: 600;
}
/* ─── EMPTY STATE ─────────────────────────── */
.gp-pkg-empty {
  border: 1px dashed rgba(109,76,91,0.18);
  border-radius: var(--r-card);
  padding: 64px 24px;
  text-align: center;
  background: rgba(250,245,239,0.60);
}
.gp-pkg-empty-icon {
  display: inline-flex; align-items: center; justify-content: center;
  width: 72px; height: 72px; border-radius: 50%;
  background: rgba(109,76,91,0.08);
  color: var(--c-strong);
  margin-bottom: 20px;
}
.gp-pkg-empty h3 {
  font-family: var(--font-display); font-size: 32px; font-weight: 600;
  color: var(--c-text);
  margin-bottom: 8px;
  line-height: 1.05;
}
.gp-pkg-empty p {
  color: var(--c-accent); font-size: 14px; line-height: 1.7;
  max-width: 480px; margin: 0 auto;
}
.gp-pkg-empty-actions { margin-top: 24px; display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; }
.gp-pkg-empty-btn {
  display: inline-flex; align-items: center; gap: 6px;
  height: 44px; padding: 0 24px; border-radius: 999px;
  font-size: 14px; font-weight: 700; cursor: pointer; border: none;
  transition: all 0.2s var(--ease-out-expo);
}
.gp-pkg-empty-btn.primary { background: var(--c-strong); color: #fcf8f5; box-shadow: 0 2px 8px rgba(109,76,91,0.18); }
.gp-pkg-empty-btn.primary:hover { background: #5a3d4a; transform: translateY(-2px); box-shadow: 0 6px 20px rgba(109,76,91,0.20); }
.gp-pkg-empty-btn.secondary { background: var(--c-card); color: var(--c-accent); border: 1px solid var(--c-rule); }
.gp-pkg-empty-btn.secondary:hover { border-color: var(--c-strong); color: var(--c-strong); background: rgba(109,76,91,0.06); }

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
.gp-tier-card.gp-reveal,
.gp-pkg-card.gp-reveal {
  opacity: 0;
  transform: translate3d(0, 38px, 0) scale(.985);
  transform-origin: center;
}
.gp-tier-card.gp-reveal.visible:hover {
  transform: translateY(-6px) scale(1.035);
  box-shadow: 0 36px 70px rgba(207,161,116,.32), inset 0 0 0 1px rgba(255,255,255,.62);
}
.gp-tier-card--luxury.gp-reveal.visible {
  transform: scale(1.035);
}
.gp-tier-card--luxury.gp-reveal.visible:hover {
  transform: translateY(-6px) scale(1.045);
}
.gp-reveal-d1 { transition-delay: 0.04s; }
.gp-reveal-d2 { transition-delay: 0.10s; }
.gp-reveal-d3 { transition-delay: 0.18s; }
.gp-reveal-d4 { transition-delay: 0.26s; }
.gp-reveal-d5 { transition-delay: 0.34s; }

@media (max-width: 900px) {
  .gp-pkg-hero {
    min-height: 780px;
    padding-top: 30px;
    padding-bottom: 118px;
  }
  .gp-search-row-fields {
    justify-content: flex-start;
    gap: 8px;
  }
  .gp-search-field-boutique.is-search {
    flex: 1 1 100%;
  }
  .gp-search-field-boutique input {
    width: 100%;
  }
  .gp-search-panel { padding: 0; }
  .gp-pkg-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
  .gp-tier-top {
    align-items: flex-start;
    flex-direction: column;
  }
  .gp-package-type-sections { gap: 32px; }
  .gp-package-type-section {
    grid-template-columns: 1fr;
    gap: 18px;
    min-height: auto;
    padding: 28px 0;
  }
  .gp-package-type-intro {
    min-height: auto;
    padding: 0;
    align-items: center;
    text-align: center;
  }
  .gp-package-type-label {
    justify-content: center;
    margin-bottom: 14px;
  }
  .gp-package-type-label::before {
    width: 38px;
  }
  .gp-package-type-copy {
    max-width: 520px;
    margin-left: auto;
    margin-right: auto;
  }
  .gp-package-type-carousel-head {
    justify-content: center;
  }
  .gp-package-type-track {
    justify-content: center;
  }
  .gp-package-type-card {
    flex-basis: min(72vw, 230px);
    max-width: min(72vw, 230px);
  }
  .gp-package-type-list {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
  .gp-tier-grid { grid-template-columns: 1fr; }
  .gp-tier-card { width: 100%; }
  .gp-tier-card--luxury,
  .gp-tier-card--luxury:hover {
    transform: none;
  }
  .gp-tier-card--standard,
  .gp-tier-card--premium {
    margin-top: 0;
  }
  .gp-tier-card--luxury {
    margin-top: 0;
    min-height: 420px;
  }
  .gp-tier-card--luxury .gp-tier-panel {
    min-height: 226px;
  }
  .gp-tier-showcase {
    margin-top: 0;
    padding-top: 54px;
    padding-bottom: 0;
    background: #ead8c8;
  }
}
@media (max-width: 700px) {
  .gp-pkg-grid { grid-template-columns: 1fr; }
  .gp-header-nav a { display: none; }
  .gp-pkg-hero {
    min-height: 720px;
    padding: 38px var(--pad-x) 106px;
  }
  .gp-pkg-hero-overline { margin-bottom: 30px; }
  .gp-search-boutique { margin-top: 22px; }
  .gp-search-row-fields {
    align-items: stretch;
    gap: 8px;
  }
  .gp-search-filter-row { flex-direction: column; align-items: stretch; }
  .gp-package-type-list {
    grid-template-columns: 1fr;
  }
  .gp-search-field-boutique {
    width: 100%;
    min-height: 42px;
    padding: 0;
  }
  .gp-search-field-boutique input,
  .gp-search-field-boutique select,
  .gp-pkg-select-wrap,
  .gp-pkg-select-trigger {
    width: 100%;
    max-width: none;
  }
  .gp-search-field-boutique input { min-height: 46px; }
  .gp-search-field-boutique select { min-height: 38px; }
  .gp-search-panel { padding: 0; }
  .gp-tier-showcase {
    margin-top: 0;
    padding-top: 42px;
    padding-bottom: 0;
    background: #ead8c8;
  }
  .gp-cat-card { font-size: 12px; }
  .gp-divider-text { font-size: 13px; }
  .gp-divider-line-deco { max-width: 60px; }
}
@media (max-width: 480px) {
  :root { --pad-x: 16px; }
}
</style>
</head>
<body>

<?php $gpNavActive = 'packages'; $gpNavOverlay = true; require APPROOT . '/views/layouts/customerHomeNav.php'; ?>

<main>
  <!-- HERO -->
  <section class="gp-pkg-hero" aria-label="Curated packages">
    <span class="gp-pkg-hero-bg is-active" aria-hidden="true" style="background-image:url('<?= $h($packageHeroImages[0]) ?>');"></span>
    <span class="gp-pkg-hero-bg" aria-hidden="true"></span>
    <div class="gp-pkg-hero-inner">
      <h1><span class="gp-script-word">Packages</span> Designed for Your <span class="gp-script-word">Love Story</span></h1>
      <p>From venue styling to bridal beauty, explore thoughtfully curated wedding packages made to suit your vision, style, and budget.</p>
      <section class="gp-search-boutique gp-reveal" aria-label="Search and filter">
        <form class="gp-search-panel" method="GET" action="<?= URLROOT ?>/customerServices/packages">
          <div class="gp-search-rows">
            <div class="gp-search-row-fields">
              
              <div class="gp-search-filter-row">
                <div class="gp-search-field-boutique">
                  <label for="f-category">Package Types</label>
                  <span class="gp-pkg-select-wrap">
                    <select id="f-category" class="gp-pkg-select" name="category">
                      <option value="all" <?= $activeCategory === 'all' ? 'selected' : '' ?>>All package types</option>
<option value="standard" <?= $activeCategory === 'standard' ? 'selected' : '' ?>>Standard</option>
<option value="premium" <?= $activeCategory === 'premium' ? 'selected' : '' ?>>Premium</option>
<option value="luxury" <?= $activeCategory === 'luxury' ? 'selected' : '' ?>>Luxury</option>
                      
                    </select>
                  </span>
                </div>
                <div class="gp-search-field-boutique">
                  <label for="f-sort">Sort by</label>
                  <span class="gp-pkg-select-wrap">
                    <select id="f-sort" class="gp-pkg-select gp-pkg-sort" name="sort">
  <option value="featured" <?= $activeSort === 'featured' ? 'selected' : '' ?>>Price</option>
  <option value="price_low" <?= $activeSort === 'price_low' ? 'selected' : '' ?>>Low to High</option>
  <option value="price_high" <?= $activeSort === 'price_high' ? 'selected' : '' ?>>High to Low</option>
</select>
                  </span>
                </div>
              </div>
            </div>
          </div>
        </form>
      </section>

      <!-- Date picker (small dropdown) -->
      <?php
        $selectedDateVal = $filters['date'] ?? '';
        $todayStr = date('Y-m-d');
        $maxDateStr = date('Y-m-d', strtotime('+18 months'));
        $dateDisplay = $selectedDateVal !== '' ? date('M j, Y', strtotime($selectedDateVal)) : 'Select date';
      ?>
      <form class="gp-pkg-date-form" method="GET" action="<?= URLROOT ?>/customerServices/packages">
        <input type="hidden" name="q" value="<?= $h($filters['search'] ?? '') ?>">
        <input type="hidden" name="category" value="<?= $h($activeCategory) ?>">
        <input type="hidden" name="sort" value="<?= $h($activeSort) ?>">
        <span class="venue-date-input-wrap">
          <i class="venue-date-icon" data-lucide="calendar-days"></i>
          <span class="venue-date-display"><?= $h($dateDisplay) ?></span>
          <i class="venue-date-chevron" data-lucide="chevron-down"></i>
          <input class="gp-calendar-input" type="date" name="date" value="<?= $h($selectedDateVal) ?>" min="<?= $h($todayStr) ?>" max="<?= $h($maxDateStr) ?>" aria-label="Filter by date">
        </span>
      </form>

    </div>
  </section>

  <!-- BOUTIQUE DIVIDER -->
  <div class="gp-divider-boutique" aria-hidden="true">
    <span class="gp-divider-line-deco"></span>
    <span class="gp-divider-diamond"></span>
    <span class="gp-divider-text">Find your perfect package</span>
    <span class="gp-divider-diamond"></span>
    <span class="gp-divider-line-deco"></span>
  </div>

  <!-- BOUTIQUE FILTER CHIPS -->
  <?php if ($hasActiveFilters): ?>
  <div class="gp-active-filters-boutique">
    <?php if (trim((string)($filters['search'] ?? '')) !== ''): ?>
    <span class="gp-filter-chip-boutique">
      "<?= $h($filters['search']) ?>"
      <a class="gp-filter-chip-boutique-remove" href="<?= URLROOT ?>/customerServices/packages?category=<?= $h($activeCategory) ?>&sort=<?= $h($activeSort) ?>" aria-label="Clear search">✕</a>
    </span>
    <?php endif; ?>
    <?php if ($activeCategory !== 'all'): ?>
    <span class="gp-filter-chip-boutique">
      <?= $h($activeCategory) ?>
      <a class="gp-filter-chip-boutique-remove" href="<?= URLROOT ?>/customerServices/packages?q=<?= $h($filters['search'] ?? '') ?>&sort=<?= $h($activeSort) ?>" aria-label="Clear category">✕</a>
    </span>
    <?php endif; ?>
    <?php if (!empty($filters['date'])): ?>
    <span class="gp-filter-chip-boutique">
      📅 <?= $h($filters['date']) ?>
      <a class="gp-filter-chip-boutique-remove" href="<?= URLROOT ?>/customerServices/packages?q=<?= $h($filters['search'] ?? '') ?>&category=<?= $h($activeCategory) ?>&sort=<?= $h($activeSort) ?>" aria-label="Clear date">✕</a>
    </span>
    <?php endif; ?>
  </div>
  <?php endif; ?>
  <!-- STATIC PACKAGE TIERS -->
  <?php if ($hasPriceFilter): ?>
  <section class="gp-tier-showcase gp-filtered-package-results" aria-label="Package results">
    <div class="gp-package-type-viewport gp-filtered-package-viewport">
      <div class="gp-package-type-track gp-filtered-package-track">
        <?php if (empty($visiblePackages)): ?>
          <div class="gp-package-type-empty">No packages match your selected filter.</div>
        <?php else: ?>
          <?php foreach ($visiblePackages as $pkg): ?>
            <?php $pkgImage = trim((string)($pkg['image_url'] ?? '')); ?>
            <a class="gp-package-type-card" href="<?= URLROOT ?>/customerServices/packageDetail/<?= $h($pkg['slug']) ?>">

  <span class="gp-package-type-image">
    <?php if ($pkgImage !== ''): ?>
      <img src="<?= $h($pkgImage) ?>" alt="<?= $h($pkg['name'] ?? 'Package') ?>" loading="lazy">
    <?php endif; ?>

     <button 
    class="gp-heart"
    type="button"
    aria-label="Add to wishlist"
    data-item-type="package"
    data-item-id="<?= (int)($pkg['id'] ?? $pkg['package_id'] ?? 0) ?>"
    data-saved="0">
    ♡
  </button>

    <span class="gp-service-count-tag">
      <?= count($pkg['services'] ?? []) ?: 5 ?> Services
    </span>
  </span>

  <h4><?= $h($pkg['name'] ?? '') ?></h4>

  <p><?= $h($pkg['tagline'] ?? $pkg['description'] ?? '') ?></p>

  <div class="gp-package-type-meta">
    <span class="gp-package-type-price">
      <?= $money($pkg['package_price'] ?? $pkg['base_price'] ?? 0) ?>
    </span>

    <span class="gp-card-book">
      Book Now
      <span class="gp-card-book-icon">
        <i data-lucide="arrow-up-right"></i>
      </span>
    </span>
  </div>

</a>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </section>
  <?php else: ?>
  <section class="gp-tier-showcase" aria-label="Package tiers">
    <?php if (!$hasPackageTypeFilter): ?>
    <div class="gp-tier-top">
      <h2 class="gp-tier-heading">Our <strong>Packages</strong></h2>
    </div>
    <div class="gp-tier-grid <?= $activeCategory !== 'all' ? 'is-filtered' : '' ?>">
      <?php if ($activeCategory === 'all' || $activeCategory === 'standard'): ?>
      <article class="gp-tier-card gp-tier-card--standard gp-reveal" data-tier-card="standard">
        <div class="gp-tier-panel">
          <div>
            <h2>Standard</h2>
            <span class="gp-tier-price">+ 30M MMK</span>
            <p>Elegant essentials for a polished celebration with trusted wedding services.</p>
          </div>
          <a class="gp-tier-action" href="#package-standard">View Standard</a>
        </div>
        <ul class="gp-tier-features">
          <li>Covers the core wedding essentials only</li>
          <li>Includes 6 essential services</li>
          <li>Budget-friendly service mix</li>
          <li>Perfect for intimate weddings</li>
        </ul>
      </article>
      <?php endif; ?>
      <?php if ($activeCategory === 'all' || $activeCategory === 'luxury'): ?>
      <article class="gp-tier-card gp-tier-card--luxury gp-reveal gp-reveal-d1" data-tier-card="luxury">
        <div class="gp-tier-panel">
          <div>
            <h2>Luxury</h2>
            <span class="gp-tier-price">+ 100M MMK</span>
            <p>Elevated styling, beauty, and planning support for a refined wedding experience.</p>
          </div>
          <a class="gp-tier-action" href="#package-luxury">View Luxury</a>
        </div>
        <ul class="gp-tier-features">
          <li>Most complete premium package</li>
          <li>Includes 10 services</li>
          <li>Grand styling & top-tier suppliers</li>
          <li>Designed for an elegant luxury wedding</li>
        </ul>
      </article>
      <?php endif; ?>
      <?php if ($activeCategory === 'all' || $activeCategory === 'premium'): ?>
      <article class="gp-tier-card gp-tier-card--premium gp-reveal gp-reveal-d2" data-tier-card="premium">
        <div class="gp-tier-panel">
          <div>
            <h2>Premium</h2>
            <span class="gp-tier-price">+ 60M MMK</span>
            <p>A complete, high-touch package for couples who want every detail beautifully handled.</p>
          </div>
          <a class="gp-tier-action" href="#package-premium">View Premium</a>
        </div>
        <ul class="gp-tier-features">
          <li>More complete service coverage</li>
          <li>Includes 9 services </li>
          <li>Catering, invites & transport included</li>
          <li>Great for a full wedding celebration</li>
        </ul>
      </article>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="gp-package-type-sections" aria-label="Package type details">
      <?php
        $typeSectionCopy = [
          'standard' => ['Essential Coverage', 'A graceful foundation for intimate weddings and carefully managed essentials.'],
          'premium' => ['Full Celebration', 'A fuller package for couples who want guest experience and details handled together.'],
          'luxury' => ['Grand Experience', 'The highest level package with elevated styling and top-tier supplier coordination.'],
        ];
        $typeOrder = array_key_exists($activeCategory, $packageTierLabels)
          ? [$activeCategory]
          : ['standard', 'premium', 'luxury'];
      ?>
      <?php foreach ($typeOrder as $sectionIndex => $tierKey): ?>
      <?php $tierPackageCount = count($packagesByTier[$tierKey]); ?>
      <section class="gp-package-type-section gp-reveal gp-reveal-d<?= min($sectionIndex, 2) ?>" id="package-<?= $h($tierKey) ?>" aria-label="<?= $h($packageTierLabels[$tierKey]) ?> details" data-tier-section="<?= $h($tierKey) ?>">
       <div class="gp-package-type-intro">
  <span class="gp-package-type-label"><?= $h($packageTierLabels[$tierKey]) ?></span>
  <h3 class="gp-package-type-title">
    <?= $h($typeSectionCopy[$tierKey][0] ?? $packageTierLabels[$tierKey]) ?>
  </h3>
  <p class="gp-package-type-copy"><?= $h($typeSectionCopy[$tierKey][1] ?? '') ?></p>

  <div class="gp-package-type-carousel-head">
    <div class="gp-package-type-nav" <?= $tierPackageCount > 3 ? '' : 'hidden' ?> aria-label="<?= $h($packageTierLabels[$tierKey]) ?> package navigation">
      <button type="button" class="gp-package-type-prev" aria-label="Previous <?= $h($packageTierLabels[$tierKey]) ?> packages">
        <i data-lucide="chevron-left" size="16"></i>
      </button>
      <button type="button" class="gp-package-type-next" aria-label="Next <?= $h($packageTierLabels[$tierKey]) ?> packages">
        <i data-lucide="chevron-right" size="16"></i>
      </button>
    </div>
  </div>
</div>
        <div class="gp-package-type-cards" data-package-type-carousel>
          <?php if (empty($packagesByTier[$tierKey])): ?>
            <div class="gp-package-type-empty">No <?= $h(strtolower($packageTierLabels[$tierKey])) ?> packages yet.</div>
          <?php else: ?>
            <div class="gp-package-type-viewport">
              <div class="gp-package-type-track">
                <?php foreach ($packagesByTier[$tierKey] as $pkg): 
                  $pkgImage = trim((string)($pkg['image_url'] ?? ''));
                ?>
                 <a class="gp-package-type-card" href="<?= URLROOT ?>/customerServices/packageDetail/<?= $h($pkg['slug']) ?>">

  <span class="gp-package-type-image">
    <?php if ($pkgImage !== ''): ?>
      <img src="<?= $h($pkgImage) ?>" alt="<?= $h($pkg['name'] ?? 'Package') ?>" loading="lazy">
    <?php endif; ?>

     <button 
    class="gp-heart"
    type="button"
    aria-label="Add to wishlist"
    data-item-type="package"
    data-item-id="<?= (int)($pkg['id'] ?? $pkg['package_id'] ?? 0) ?>"
    data-saved="0">
    ♡
  </button>

    <span class="gp-service-count-tag">
      <?= count($pkg['services'] ?? []) ?: 5 ?> Services
    </span>
  </span>

  <h4><?= $h($pkg['name'] ?? '') ?></h4>

  <p><?= $h($pkg['tagline'] ?? $pkg['description'] ?? '') ?></p>

  <div class="gp-package-type-meta">
    <span class="gp-package-type-price">
      <?= $money($pkg['package_price'] ?? $pkg['base_price'] ?? 0) ?>
    </span>

    <span class="gp-card-book">
      Book Now
      <span class="gp-card-book-icon">
        <i data-lucide="arrow-up-right"></i>
      </span>
    </span>
  </div>

</a>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </section>
      <?php endforeach; ?>
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

  const packageHero = document.querySelector('.gp-pkg-hero');
  if (packageHero) {
    const packageHeroLayers = Array.from(packageHero.querySelectorAll('.gp-pkg-hero-bg'));
    const packageHeroImages = [
      <?php foreach ($packageHeroImages as $heroPath): ?>
      "<?= $h($heroPath) ?>",
      <?php endforeach; ?>
    ];
    let packageHeroIndex = 0;
    let activeHeroLayer = 0;
    window.setInterval(() => {
      if (packageHeroLayers.length < 2 || packageHeroImages.length < 2) return;
      packageHeroIndex = (packageHeroIndex + 1) % packageHeroImages.length;
      const nextLayer = activeHeroLayer === 0 ? 1 : 0;
      packageHeroLayers[nextLayer].style.backgroundImage = `url('${packageHeroImages[packageHeroIndex]}')`;
      packageHeroLayers[nextLayer].classList.add('is-active');
      packageHeroLayers[activeHeroLayer].classList.remove('is-active');
      activeHeroLayer = nextLayer;
    }, 5000);

    if ('IntersectionObserver' in window) {
      const heroObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            packageHero.classList.add('is-in');
            heroObserver.disconnect();
          }
        });
      }, { threshold: 0.35 });
      heroObserver.observe(packageHero);
    } else {
      packageHero.classList.add('is-in');
    }
  }

  const revealBoxes = document.querySelectorAll('.gp-reveal');
  if (revealBoxes.length && 'IntersectionObserver' in window) {
    let revealCardIndex = 0;
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.06, rootMargin: '0px 0px -40px 0px' });
    revealBoxes.forEach(el => {
      if (el.matches('.gp-tier-card, .gp-pkg-card')) {
        el.style.setProperty('--card-reveal-delay', `${Math.min(revealCardIndex * 0.14, 1.12).toFixed(2)}s`);
        revealCardIndex += 1;
      }
      observer.observe(el);
    });
  } else {
    revealBoxes.forEach(el => el.classList.add('visible'));
  }

  document.querySelectorAll('[data-package-type-carousel]').forEach(carousel => {
    const section = carousel.closest('.gp-package-type-section');
    const viewport = carousel.querySelector('.gp-package-type-viewport');
    const track = carousel.querySelector('.gp-package-type-track');
    const cards = Array.from(carousel.querySelectorAll('.gp-package-type-card'));
    const prev = section?.querySelector('.gp-package-type-prev');
    const next = section?.querySelector('.gp-package-type-next');
    if (!viewport || !track || cards.length <= 3 || !prev || !next) return;

    let index = 0;
    const maxIndex = Math.max(cards.length - 3, 0);
    const getStep = () => {
      const first = cards[0];
      if (!first) return 0;
      const gap = parseFloat(window.getComputedStyle(track).columnGap || window.getComputedStyle(track).gap || '0') || 0;
      return first.getBoundingClientRect().width + gap;
    };
    const update = () => {
      index = Math.max(0, Math.min(index, maxIndex));
      track.style.transform = `translateX(-${index * getStep()}px)`;
      prev.disabled = index === 0;
      next.disabled = index === maxIndex;
    };
    prev.addEventListener('click', () => {
      index -= 1;
      update();
    });
    next.addEventListener('click', () => {
      index += 1;
      update();
    });
    window.addEventListener('resize', update);
    update();
  });

  const closePackageSelects = (exceptWrap = null) => {
    document.querySelectorAll('.gp-pkg-select-wrap.is-open').forEach(wrap => {
      if (wrap === exceptWrap) return;
      wrap.classList.remove('is-open');
      wrap.querySelector('.gp-pkg-select-trigger')?.setAttribute('aria-expanded', 'false');
      if (wrap._packageSelectMenu) wrap._packageSelectMenu.hidden = true;
    });
    document.querySelectorAll('.gp-pkg-select-popover').forEach(menu => {
      if (exceptWrap && exceptWrap._packageSelectMenu === menu) return;
      menu.hidden = true;
    });
  };

  const positionPackageSelect = (wrap) => {
    const trigger = wrap.querySelector('.gp-pkg-select-trigger');
    const menu = wrap._packageSelectMenu;
    if (!trigger || !menu) return;
    const rect = trigger.getBoundingClientRect();
    const width = Math.max(rect.width, 136);
    const left = Math.max(12, Math.min(rect.left, window.innerWidth - width - 12));
    menu.style.width = width + 'px';
    menu.style.left = left + 'px';
    menu.style.top = (rect.bottom + 8) + 'px';
  };

  document.querySelectorAll('.gp-pkg-select-wrap').forEach((wrap, index) => {
    const select = wrap.querySelector('.gp-pkg-select');
    if (!select) return;
    select.classList.add('is-native-hidden');

    const trigger = document.createElement('button');
    trigger.type = 'button';
    trigger.className = 'gp-pkg-select-trigger';
    trigger.setAttribute('aria-haspopup', 'listbox');
    trigger.setAttribute('aria-expanded', 'false');
    trigger.innerHTML = '<span class="gp-pkg-select-trigger-text"></span><svg viewBox="0 0 24 24" fill="none" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>';

    const menu = document.createElement('div');
    menu.className = 'gp-pkg-select-popover';
    menu.hidden = true;
    menu.id = 'packageFilterSelect' + index;
    menu.setAttribute('role', 'listbox');
    trigger.setAttribute('aria-controls', menu.id);
    wrap._packageSelectMenu = menu;

    const syncSelectDisplay = () => {
      const chosen = select.options[select.selectedIndex];
      trigger.querySelector('.gp-pkg-select-trigger-text').textContent = chosen?.textContent || '';
      menu.querySelectorAll('.gp-pkg-select-item').forEach(item => {
        const selected = item.dataset.value === select.value;
        item.classList.toggle('is-selected', selected);
        item.setAttribute('aria-selected', selected ? 'true' : 'false');
      });
    };

    Array.from(select.options).forEach(option => {
      const item = document.createElement('button');
      item.type = 'button';
      item.className = 'gp-pkg-select-item';
      item.dataset.value = option.value;
      item.setAttribute('role', 'option');
      item.innerHTML = '<span></span><span class="gp-pkg-select-dot" aria-hidden="true"></span>';
      item.querySelector('span').textContent = option.textContent;
      item.addEventListener('click', event => {
        event.preventDefault();
        select.value = option.value;
        syncSelectDisplay();
        closePackageSelects();
        select.dispatchEvent(new Event('change', { bubbles: true }));
      });
      menu.appendChild(item);
    });

    trigger.addEventListener('click', event => {
      event.preventDefault();
      event.stopPropagation();
      const opening = menu.hidden;
      closePackageSelects(wrap);
      wrap.classList.toggle('is-open', opening);
      menu.hidden = !opening;
      if (opening) positionPackageSelect(wrap);
      trigger.setAttribute('aria-expanded', opening ? 'true' : 'false');
    });
    menu.addEventListener('click', event => event.stopPropagation());

    wrap.append(trigger);
    document.body.appendChild(menu);
    syncSelectDisplay();
  });

  document.addEventListener('click', () => closePackageSelects());
  document.addEventListener('keydown', event => {
    if (event.key === 'Escape') closePackageSelects();
  });
  window.addEventListener('resize', () => closePackageSelects());
  window.addEventListener('scroll', () => closePackageSelects(), { passive: true });

  document.querySelectorAll('.gp-pkg-select').forEach(select => {
    select.addEventListener('change', () => {
      select.closest('form')?.submit();
    });
  });

  // Profile dropdown toggle
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

(function(){
  const isLoggedIn = <?= $isLoggedIn ? 'true' : 'false' ?>;
  const authUrl = '<?= URLROOT ?>/users/auth';
  const wishlistUrl = '<?= URLROOT ?>/main/toggleWishlist';
  const storageKey = 'gpPackageWishlist';

  function savedPackages(){
    try {
      return JSON.parse(localStorage.getItem(storageKey) || '[]').map(Number).filter(Boolean);
    } catch (error) {
      return [];
    }
  }

  function storePackages(ids){
    try {
      localStorage.setItem(storageKey, JSON.stringify(Array.from(new Set(ids.map(Number).filter(Boolean)))));
    } catch (error) {}
  }

  function paintHeart(btn, isSaved){
    btn.classList.toggle('is-saved', isSaved);
    btn.dataset.saved = isSaved ? '1' : '0';
    btn.setAttribute('aria-label', isSaved ? 'Remove from wishlist' : 'Add to wishlist');
    btn.textContent = isSaved ? '♥' : '♡';
  }

  const localSaved = savedPackages();
  document.querySelectorAll('.gp-heart[data-item-type="package"]').forEach(function(btn){
    const itemId = parseInt(btn.dataset.itemId, 10);
    if (!itemId) return;

    if (localSaved.includes(itemId)) {
      paintHeart(btn, true);
    }

    btn.addEventListener('click', function(e){
      e.preventDefault();
      e.stopPropagation();

      if (!isLoggedIn) {
        window.location.href = authUrl + '?redirect=' + encodeURIComponent('customerServices/packages');
        return;
      }

      const nextSaved = !(btn.dataset.saved === '1' || btn.classList.contains('is-saved'));
      btn.classList.add('is-loading');

      fetch(wishlistUrl, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
          item_type: 'package',
          item_id: itemId,
          collection_id: null
        })
      })
      .then(function(response){ return response.json(); })
      .then(function(data){
        btn.classList.remove('is-loading');
        if (!data.ok) return;

        const isSaved = data.action === 'added';
        paintHeart(btn, isSaved);

        let ids = savedPackages();
        ids = isSaved ? ids.concat(itemId) : ids.filter(function(id){ return id !== itemId; });
        storePackages(ids);
      })
      .catch(function(){
        btn.classList.remove('is-loading');
        paintHeart(btn, !nextSaved);
      });
    });
  });
})();
</script>

<div class="gp-calendar-popover" id="gpCalendarPopover" hidden></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  var gpCalendar = document.getElementById('gpCalendarPopover');
  var gpCalendarInput = null;
  var gpCalendarMonth = null;

  function formatDateValue(date) {
    var year = date.getFullYear();
    var month = String(date.getMonth() + 1).padStart(2, '0');
    var day = String(date.getDate()).padStart(2, '0');
    return year + '-' + month + '-' + day;
  }

  function parseDateValue(value) {
    if (!value) return null;
    var parts = value.split('-').map(Number);
    if (parts.length !== 3 || parts.some(isNaN)) return null;
    return new Date(parts[0], parts[1] - 1, parts[2]);
  }

  function updateCalendarDisplay(input) {
    var display = input.closest('.venue-date-input-wrap')?.querySelector('.venue-date-display');
    if (!display) return;
    if (!input.value) { display.textContent = 'Select date'; return; }
    var parsed = parseDateValue(input.value);
    display.textContent = parsed ? parsed.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : 'Select date';
  }

  function positionCalendar(anchor) {
    if (!gpCalendar || !anchor) return;
    var rect = anchor.getBoundingClientRect();
    var width = Math.min(250, window.innerWidth - 32);
    var left = Math.max(16, Math.min(rect.left, window.innerWidth - width - 16));
    gpCalendar.style.width = width + 'px';
    gpCalendar.style.left = left + 'px';
    gpCalendar.style.top = (rect.bottom + 10) + 'px';
  }

  function renderCalendar() {
    if (!gpCalendar || !gpCalendarInput || !gpCalendarMonth) return;
    var monthStart = new Date(gpCalendarMonth.getFullYear(), gpCalendarMonth.getMonth(), 1);
    var selectedValue = gpCalendarInput.value;
    var todayValue = formatDateValue(new Date());
    var minValue = gpCalendarInput.min || '';
    var maxValue = gpCalendarInput.max || '';
    var daysInMonth = new Date(monthStart.getFullYear(), monthStart.getMonth() + 1, 0).getDate();
    var leadingBlanks = monthStart.getDay();
    var monthTitle = monthStart.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
    var dayNames = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'];

    var html = '<div class="gp-calendar-head">' +
      '<button class="gp-calendar-nav" type="button" data-cal-prev aria-label="Previous month"><i data-lucide="chevron-left" size="16"></i></button>' +
      '<span>' + monthTitle + '</span>' +
      '<button class="gp-calendar-nav" type="button" data-cal-next aria-label="Next month"><i data-lucide="chevron-right" size="16"></i></button>' +
      '</div><div class="gp-calendar-grid">';

    dayNames.forEach(function(day) { html += '<div class="gp-calendar-day-name">' + day + '</div>'; });
    for (var i = 0; i < leadingBlanks; i++) html += '<span></span>';
    for (var day = 1; day <= daysInMonth; day++) {
      var value = formatDateValue(new Date(monthStart.getFullYear(), monthStart.getMonth(), day));
      var disabled = (minValue && value < minValue) || (maxValue && value > maxValue);
      var classes = ['gp-calendar-day'];
      if (value === selectedValue) classes.push('is-selected');
      if (value === todayValue) classes.push('is-today');
      if (disabled) classes.push('is-disabled');
      html += '<button class="' + classes.join(' ') + '" type="button" data-date="' + value + '"' + (disabled ? ' disabled' : '') + '>' + day + '</button>';
    }
    html += '</div>';
    gpCalendar.innerHTML = html;
    if (typeof lucide !== 'undefined') lucide.createIcons({ nodes: [gpCalendar] });
  }

  function openCalendar(input) {
    gpCalendarInput = input;
    gpCalendarMonth = parseDateValue(input.value) || parseDateValue(input.min) || new Date();
    renderCalendar();
    gpCalendar.hidden = false;
    positionCalendar(input.closest('.venue-date-input-wrap') || input);
  }

  document.querySelectorAll('.gp-calendar-input').forEach(function(input) {
    updateCalendarDisplay(input);
    input.addEventListener('click', function(event) {
      event.preventDefault();
      event.stopPropagation();
      openCalendar(input);
    });
    input.addEventListener('focus', function() { openCalendar(input); });
  });

  document.querySelectorAll('.venue-date-input-wrap').forEach(function(wrap) {
    var input = wrap.querySelector('.gp-calendar-input');
    if (!input) return;
    wrap.addEventListener('click', function(event) {
      event.preventDefault();
      event.stopPropagation();
      openCalendar(input);
    });
  });

  gpCalendar?.addEventListener('click', function(event) {
    event.stopPropagation();
    var prev = event.target.closest('[data-cal-prev]');
    var next = event.target.closest('[data-cal-next]');
    var day = event.target.closest('[data-date]');
    if (prev) {
      gpCalendarMonth = new Date(gpCalendarMonth.getFullYear(), gpCalendarMonth.getMonth() - 1, 1);
      renderCalendar();
      return;
    }
    if (next) {
      gpCalendarMonth = new Date(gpCalendarMonth.getFullYear(), gpCalendarMonth.getMonth() + 1, 1);
      renderCalendar();
      return;
    }
    if (day && gpCalendarInput) {
      gpCalendarInput.value = day.dataset.date;
      updateCalendarDisplay(gpCalendarInput);
      gpCalendar.hidden = true;
      // Submit the form to filter packages by date
      if (gpCalendarInput.form) {
        if (typeof gpCalendarInput.form.requestSubmit === 'function') gpCalendarInput.form.requestSubmit();
        else gpCalendarInput.form.submit();
      }
    }
  });
  gpCalendar?.addEventListener('mousedown', function(event) {
    event.preventDefault();
    event.stopPropagation();
  });

  document.addEventListener('click', function(event) {
    if (!gpCalendar || gpCalendar.hidden) return;
    if (event.target.closest('.gp-calendar-popover') || event.target.closest('.venue-date-input-wrap')) return;
    gpCalendar.hidden = true;
  });

  window.addEventListener('resize', function() {
    if (!gpCalendar?.hidden && gpCalendarInput) positionCalendar(gpCalendarInput.closest('.venue-date-input-wrap') || gpCalendarInput);
  });
  window.addEventListener('scroll', function() {
    if (gpCalendar && !gpCalendar.hidden) gpCalendar.hidden = true;
  }, { passive: true });
});
</script>

<?php include APPROOT . '/views/partials/cookie-consent.php'; ?>
</body>
</html>
