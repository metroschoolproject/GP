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

$activeCategory = $filters['category'] ?? 'all';
$activeSort     = $filters['sort'] ?? 'featured';
$resetUrl = URLROOT . '/customerServices/packages';
$packageHeroImages = [
    URLROOT . '/app/views/main/images/heroPackage1.png',
    URLROOT . '/app/views/main/images/heroPackage2.png',
];
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
<link href="https://fonts.googleapis.com/css2?family=Ballet:opsz@16..72&family=Playfair+Display:wght@400;500;600;700&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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

.gp-texture {
  display: none;
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
  padding: 44px var(--pad-x) 124px;
  overflow: hidden;
  background: linear-gradient(135deg, #6d4c5b 0%, #c7a078 100%);
  color: #fffaf3;
  border-radius: 0;
  -webkit-mask-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 1440 720' preserveAspectRatio='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath fill='black' d='M0 0H1440V640C1320 688 1200 708 1080 684C960 660 840 590 720 626C600 662 480 708 360 684C240 660 120 590 0 632V0Z'/%3E%3C/svg%3E");
  mask-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 1440 720' preserveAspectRatio='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath fill='black' d='M0 0H1440V640C1320 688 1200 708 1080 684C960 660 840 590 720 626C600 662 480 708 360 684C240 660 120 590 0 632V0Z'/%3E%3C/svg%3E");
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
  margin: 0 auto;
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

/* ─── PACKAGE GRID ───────────────────────── */
.gp-pkg-section {
  padding: 46px var(--pad-x) 84px;
  background: linear-gradient(
    180deg,
    #ead8c8 0%,
    #dfc9b7 48%,
    #d2bba8 100%
  );
}
.gp-tier-showcase {
  position: relative;
  z-index: 5;
  margin-top: 0;
  padding: 64px var(--pad-x) 54px;
  background: #ead8c8;
}
.gp-tier-heading {
  max-width: 1280px;
  margin: 0 auto 28px;
  text-align: center;
  color: #111827;
  font-size: clamp(34px, 4vw, 56px);
  line-height: 1;
  font-weight: 300;
}
.gp-tier-heading strong {
  font-weight: 800;
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
.gp-tier-card {
  position: relative;
  min-height: 390px;
  overflow: hidden;
  border-radius: 18px;
  background: var(--tier-bg);
  border: 1px solid var(--tier-border);
  box-shadow: 0 18px 38px rgba(63,36,26,.14);
  color: #2b1b24;
  isolation: isolate;
  padding: 10px;
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
  --tier-border: #CFCFCF;
  margin-top: 26px;
}
.gp-tier-card--luxury {
  --tier-bg: #FFF9F3;
  --tier-start: #F6E7D5;
  --tier-end: #E8C7A4;
  --tier-button: #9A687F;
  --tier-border: #E8D7C3;
  min-height: 430px;
  margin-top: 10px;
  transform: scale(1.035);
  border-width: 2px;
  box-shadow: 0 30px 62px rgba(207,161,116,.28), inset 0 0 0 1px rgba(255,255,255,.55);
  z-index: 2;
}
.gp-tier-card--premium {
  --tier-bg: #FFF8EF;
  --tier-panel: #E7E7E7;
  --tier-start: #E7E7E7;
  --tier-end: #E7E7E7;
  --tier-button: #9A687F;
  --tier-border: #CFCFCF;
  margin-top: 26px;
}
.gp-tier-panel {
  min-height: 178px;
  border-radius: 14px;
  padding: 20px 22px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  color: #201a1d;
}
.gp-tier-card--standard .gp-tier-panel {
  background: var(--tier-panel);
}
.gp-tier-card--luxury .gp-tier-panel {
  min-height: 226px;
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
  margin: 0 0 6px;
  font-size: 34px;
  line-height: 1.1;
  font-weight: 800;
}
.gp-tier-card p {
  max-width: 300px;
  color: rgba(32,26,29,.76);
  font-size: 13px;
  line-height: 1.35;
  font-weight: 600;
}
.gp-tier-action {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-height: 44px;
  width: 100%;
  margin-top: 22px;
  border-radius: 12px;
  background: var(--tier-button);
  color: #fffaf3;
  font-size: 13px;
  font-weight: 800;
  box-shadow: 0 12px 24px rgba(23,23,25,.18);
}
.gp-tier-features {
  list-style: none;
  display: grid;
  gap: 8px;
  padding: 18px 16px 8px;
  color: #4b5563;
  font-size: 12px;
  font-weight: 700;
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
  animation: cardFlyIn .72s var(--ease-out-expo);
}
.gp-tier-card--luxury.visible {
  animation-name: luxuryCardFlyIn;
  transform: scale(1.035);
}
.gp-tier-card--luxury.visible:hover {
  transform: scale(1.045);
}
.gp-tier-card.visible:nth-child(2),
.gp-pkg-card.visible:nth-child(2) { animation-delay: .05s; }
.gp-tier-card.visible:nth-child(3),
.gp-pkg-card.visible:nth-child(3) { animation-delay: .10s; }
.gp-pkg-card.visible:nth-child(4) { animation-delay: .15s; }
.gp-pkg-card.visible:nth-child(5) { animation-delay: .20s; }
.gp-pkg-card.visible:nth-child(6) { animation-delay: .25s; }
.gp-pkg-card.visible:nth-child(7) { animation-delay: .30s; }
.gp-pkg-card.visible:nth-child(8) { animation-delay: .35s; }
.gp-pkg-card.visible:nth-child(9) { animation-delay: .40s; }
@keyframes cardFlyIn {
  0% { opacity: 0; transform: translate3d(0,34px,0) rotateX(9deg) scale(.96); }
  65% { opacity: 1; transform: translate3d(0,-5px,0) rotateX(-2deg) scale(1.01); }
  100% { opacity: 1; transform: translate3d(0,0,0) rotateX(0) scale(1); }
}
@keyframes luxuryCardFlyIn {
  0% { opacity: 0; transform: translate3d(0,34px,0) rotateX(9deg) scale(.96); }
  65% { opacity: 1; transform: translate3d(0,-5px,0) rotateX(-2deg) scale(1.045); }
  100% { opacity: 1; transform: translate3d(0,0,0) rotateX(0) scale(1.035); }
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
  font-size: 14px; font-weight: 800;
  color: #6D4C5B; line-height: 1;
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
    min-height: 700px;
    padding-top: 30px;
    padding-bottom: 96px;
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
  .gp-tier-grid { grid-template-columns: 1fr; }
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
    background: #ead8c8;
  }
}
@media (max-width: 700px) {
  .gp-pkg-grid { grid-template-columns: 1fr; }
  .gp-header-nav a { display: none; }
  .gp-pkg-hero {
    min-height: 660px;
    padding: 34px var(--pad-x) 88px;
  }
  .gp-pkg-hero-overline { margin-bottom: 30px; }
  .gp-pkg-section { padding-bottom: 40px; }
  .gp-search-boutique { margin-top: 22px; }
  .gp-search-row-fields {
    align-items: stretch;
    gap: 8px;
  }
  .gp-search-filter-row { flex-direction: column; align-items: stretch; }
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
    padding-bottom: 34px;
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

<div class="gp-texture" aria-hidden="true"></div>

<?php $gpNavActive = 'packages'; $gpNavOverlay = true; require APPROOT . '/views/layouts/customerHomeNav.php'; ?>

<main>
  <!-- HERO -->
  <section class="gp-pkg-hero" aria-label="Curated packages">
    <span class="gp-pkg-hero-bg is-active" aria-hidden="true" style="background-image:url('<?= $h($packageHeroImages[0]) ?>');"></span>
    <span class="gp-pkg-hero-bg" aria-hidden="true"></span>
    <div class="gp-pkg-hero-inner">
      <h1>Packages Designed for Your Love Story</h1>
      <p>From venue styling to bridal beauty, explore thoughtfully curated wedding packages made to suit your vision, style, and budget.</p>
      <section class="gp-search-boutique gp-reveal" aria-label="Search and filter">
        <form class="gp-search-panel" method="GET" action="<?= URLROOT ?>/customerServices/packages">
          <div class="gp-search-rows">
            <div class="gp-search-row-fields">
              <div class="gp-search-field-boutique is-search">
                <label for="q">Search</label>
                <svg class="gp-search-leading-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                <input id="q" type="search" name="q" value="<?= $h($filters['search'] ?? '') ?>" placeholder="Wedding, photography, floral…">
                <button class="gp-search-btn" type="submit" aria-label="Search packages">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                </button>
              </div>
              <div class="gp-search-filter-row">
                <div class="gp-search-field-boutique">
                  <label for="f-category">Category</label>
                  <span class="gp-pkg-select-wrap">
                    <select id="f-category" class="gp-pkg-select" name="category">
                      <option value="all" <?= $activeCategory === 'all' ? 'selected' : '' ?>>All categories</option>
                      <?php foreach ($categories as $cat):
                        $slug = $cat['slug'] ?? strtolower($cat['name'] ?? '');
                      ?>
                        <option value="<?= $h($slug) ?>" <?= ($activeCategory === $slug) ? 'selected' : '' ?>>
                          <?= $h($cat['name'] ?? '') ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </span>
                </div>
                <div class="gp-search-field-boutique">
                  <label for="f-sort">Sort by</label>
                  <span class="gp-pkg-select-wrap">
                    <select id="f-sort" class="gp-pkg-select gp-pkg-sort" name="sort">
                      <option value="featured" <?= $activeSort === 'featured' ? 'selected' : '' ?>>Featured</option>
                      <option value="price_low" <?= $activeSort === 'price_low' ? 'selected' : '' ?>>Price: low first</option>
                      <option value="price_high" <?= $activeSort === 'price_high' ? 'selected' : '' ?>>Price: high first</option>
                      <option value="name_az" <?= $activeSort === 'name_az' ? 'selected' : '' ?>>Name: A–Z</option>
                      <option value="name_za" <?= $activeSort === 'name_za' ? 'selected' : '' ?>>Name: Z–A</option>
                    </select>
                  </span>
                </div>
              </div>
            </div>
          </div>
        </form>
      </section>
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
  </div>
  <?php endif; ?>

  <!-- STATIC PACKAGE TIERS -->
  <section class="gp-tier-showcase" aria-label="Package tiers">
    <h2 class="gp-tier-heading">Our <strong>Packages</strong></h2>
    <div class="gp-tier-grid">
      <article class="gp-tier-card gp-tier-card--standard gp-reveal">
        <div class="gp-tier-panel">
          <div>
            <h2>Standard</h2>
            <p>Elegant essentials for a polished celebration with trusted wedding services.</p>
          </div>
          <span class="gp-tier-action">View Standard</span>
        </div>
        <ul class="gp-tier-features">
          <li>Covers the core wedding essentials only</li>
          <li>Includes 6 essential services</li>
          <li>Budget-friendly service mix</li>
          <li>Perfect for intimate weddings</li>
        </ul>
      </article>
      <article class="gp-tier-card gp-tier-card--luxury gp-reveal gp-reveal-d1">
        <div class="gp-tier-panel">
          <div>
            <h2>Luxury</h2>
            <p>Elevated styling, beauty, and planning support for a refined wedding experience.</p>
          </div>
          <span class="gp-tier-action">View Luxury</span>
        </div>
        <ul class="gp-tier-features">
          <li>Most complete premium package</li>
          <li>Includes 10 services</li>
          <li>Grand styling & top-tier suppliers</li>
          <li>Designed for an elegant luxury wedding</li>
        </ul>
      </article>
      <article class="gp-tier-card gp-tier-card--premium gp-reveal gp-reveal-d2">
        <div class="gp-tier-panel">
          <div>
            <h2>Premium</h2>
            <p>A complete, high-touch package for couples who want every detail beautifully handled.</p>
          </div>
          <span class="gp-tier-action">View Premium</span>
        </div>
        <ul class="gp-tier-features">
          <li>More complete service coverage</li>
          <li>Includes 9 services </li>
          <li>Catering, invites & transport included</li>
          <li>Great for a full wedding celebration</li>
        </ul>
      </article>
    </div>
  </section>

  <!-- PACKAGE GRID -->
  <section class="gp-pkg-section" aria-label="Package types">
    <h2 class="gp-pkg-heading">Our <strong>Packages</strong></h2>
    <?php if (empty($packages)): ?>
      <div class="gp-pkg-empty">
        <div class="gp-pkg-empty-icon"><i data-lucide="search-x" size="28"></i></div>
        <h3>No packages found</h3>
        <p>We couldn't find any packages matching your criteria. Try adjusting your search or category filter.</p>
        <div class="gp-pkg-empty-actions">
          <a class="gp-pkg-empty-btn primary" href="<?= $resetUrl ?>">Clear all filters</a>
          <a class="gp-pkg-empty-btn secondary" href="<?= URLROOT ?>/customerServices/packages">Browse all packages</a>
        </div>
      </div>
    <?php else: ?>
      <div class="gp-pkg-grid">
        <?php foreach ($packages as $i => $pkg):
          $revealClass = 'gp-reveal gp-reveal-d' . min($i % 6, 5);
          $pkgImage = trim((string)($pkg['image_url'] ?? ''));
        ?>
        <a class="gp-pkg-card <?= $revealClass ?>" href="<?= URLROOT ?>/customerServices/packageDetail/<?= $h($pkg['slug']) ?>" aria-label="View <?= $h($pkg['name'] ?? 'package') ?> details">
          <span class="gp-pkg-visual" aria-hidden="true">
            <?php if ($pkgImage !== ''): ?>
              <img src="<?= $h($pkgImage) ?>" alt="<?= $h($pkg['name'] ?? 'Package') ?>" loading="lazy">
            <?php endif; ?>
            <span class="gp-pkg-badge"><?= (int)($pkg['item_count'] ?? 0) ?> service types</span>
          </span>
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
                <span class="gp-pkg-price"><?= $money($pkg['package_price'] ?? $pkg['base_price'] ?? 0) ?></span>
                <span class="gp-pkg-price-label">Complete package price</span>
              </div>
            </div>
          </div>
        </a>
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
</script>
</body>
</html>
