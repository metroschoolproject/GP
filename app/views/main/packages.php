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
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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

/* ─── BOUTIQUE DIVIDER ────────────────────────────────── */
.gp-divider-boutique {
  display: flex; align-items: center; justify-content: center; gap: 18px;
  padding: 8px var(--pad-x) 28px;
  user-select: none;
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
  padding: 0 var(--pad-x) 28px;
}
.gp-search-panel {
  background: linear-gradient(145deg, rgba(250,245,239,0.95), rgba(245,232,217,0.92));
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border: 1px solid rgba(212,160,71,0.15);
  border-radius: 20px;
  box-shadow:
    0 20px 50px -12px rgba(109,76,91,0.12),
    inset 0 1px 0 rgba(252,248,245,0.60);
  padding: 24px 28px;
  transition: box-shadow 0.3s;
}
.gp-search-panel:focus-within {
  border-color: rgba(212,160,71,0.30);
  box-shadow:
    0 0 0 4px rgba(212,160,71,0.08),
    0 20px 50px -12px rgba(109,76,91,0.16);
}
.gp-search-rows {
  display: flex; flex-direction: column; gap: 12px;
}
.gp-search-row-fields {
  display: grid;
  grid-template-columns: 1fr 1fr auto auto;
  align-items: end;
  gap: 10px;
}
.gp-search-field-boutique {
  display: flex; flex-direction: column; gap: 4px;
}
.gp-search-field-boutique label {
  font-size: 9px; font-weight: 700; letter-spacing: 0.1em;
  text-transform: uppercase; color: var(--c-strong);
  padding-left: 2px;
}
.gp-search-field-boutique input,
.gp-search-field-boutique select {
  height: 42px;
  padding: 0 14px;
  border-radius: 10px;
  border: 1px solid rgba(212,160,71,0.12);
  background: rgba(252,248,245,0.72);
  font-size: 13px; font-weight: 500; color: var(--c-text);
  width: 100%; appearance: none;
  transition: border-color 0.2s, background 0.2s;
}
.gp-search-field-boutique input:hover,
.gp-search-field-boutique select:hover {
  background: rgba(252,248,245,0.90);
  border-color: rgba(212,160,71,0.25);
}
.gp-search-field-boutique input:focus,
.gp-search-field-boutique select:focus {
  border-color: var(--c-gold);
  background: #fcf8f5;
  box-shadow: 0 0 0 3px rgba(212,160,71,0.10);
}
.gp-search-field-boutique input::placeholder { color: var(--c-pale); font-weight: 400; }
.gp-search-field-boutique select { cursor: pointer; }

.gp-search-btn {
  display: inline-flex; align-items: center; justify-content: center; gap: 8px;
  height: 42px; padding: 0 28px;
  border-radius: 12px; border: none;
  background: linear-gradient(135deg, var(--c-strong) 0%, #8b5e6f 100%);
  color: #fffaf3;
  font-size: 13px; font-weight: 700; letter-spacing: 0.02em;
  cursor: pointer; white-space: nowrap;
  transition: all 0.25s var(--ease-out-expo);
  box-shadow: 0 4px 14px rgba(109,76,91,0.22);
}
.gp-search-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(109,76,91,0.30);
}
.gp-search-btn:active {
  transform: translateY(0);
}

/* ─── BOUTIQUE FILTER CHIPS ──────────────────────────── */
.gp-active-filters-boutique {
  display: flex; gap: 8px; flex-wrap: wrap;
  padding: 0 var(--pad-x) 20px;
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
  background: rgba(212,160,71,0.12); color: #8b6f3e;
  cursor: pointer; font-size: 10px; font-weight: 700;
  transition: all 0.15s;
  line-height: 1;
}
.gp-filter-chip-boutique-remove:hover {
  background: var(--c-gold);
  color: #fcf8f5;
  box-shadow: 0 2px 6px rgba(212,160,71,0.25);
}

/* ─── BOUTIQUE CATEGORY CARDS ────────────────────────── */
.gp-cats-boutique {
  padding: 0 var(--pad-x) 32px;
}
.gp-cats-strip {
  display: flex; gap: 10px; flex-wrap: wrap; align-items: center;
}
.gp-cat-card {
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
.gp-cat-card:hover {
  border-color: rgba(212,160,71,0.30);
  background: rgba(250,245,239,0.90);
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(109,76,91,0.08);
}
.gp-cat-card.active {
  background: linear-gradient(135deg, var(--c-strong) 0%, #8b5e6f 100%);
  color: #fffaf3;
  border-color: transparent;
  box-shadow: 0 4px 14px rgba(109,76,91,0.20);
}
.gp-cat-card.active:hover {
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
.gp-cat-card.active .gp-cat-count {
  background: rgba(252,248,245,0.18);
  color: #fcf8f5;
}
.gp-cat-icon {
  font-size: 16px; line-height: 1;
  flex-shrink: 0;
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
  background: var(--c-strong); color: #fcf8f5; border-color: var(--c-strong);
  transform: translateX(2px);
  box-shadow: 0 2px 10px rgba(109,76,91,0.18);
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
.gp-reveal-d1 { transition-delay: 0.04s; }
.gp-reveal-d2 { transition-delay: 0.10s; }
.gp-reveal-d3 { transition-delay: 0.18s; }
.gp-reveal-d4 { transition-delay: 0.26s; }
.gp-reveal-d5 { transition-delay: 0.34s; }

@media (max-width: 900px) {
  .gp-search-row-fields {
    grid-template-columns: 1fr 1fr;
  }
  .gp-search-row-fields .gp-search-field-boutique:nth-child(1) { grid-column: span 2; }
  .gp-search-row-fields .gp-search-btn { grid-column: span 2; }
  .gp-search-panel { padding: 18px 20px; }
  .gp-pkg-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
}
@media (max-width: 700px) {
  .gp-pkg-grid { grid-template-columns: 1fr; }
  .gp-header-nav a { display: none; }
  .gp-pkg-hero { padding: 48px var(--pad-x) 36px; }
  .gp-pkg-section { padding-bottom: 40px; }
  .gp-search-row-fields { grid-template-columns: 1fr 1fr; gap: 8px; }
  .gp-search-row-fields .gp-search-field-boutique:nth-child(1) { grid-column: span 2; }
  .gp-search-row-fields .gp-search-btn { grid-column: span 2; }
  .gp-search-panel { padding: 14px 16px; border-radius: 16px; }
  .gp-cat-card { height: 42px; padding: 0 14px; font-size: 12px; }
  .gp-cat-icon { font-size: 14px; }
  .gp-cats-strip { gap: 8px; }
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

<?php $gpNavActive = 'packages'; require APPROOT . '/views/layouts/customerHomeNav.php'; ?>

<main>
  <!-- HERO -->
  <section class="gp-pkg-hero" aria-label="Curated packages">
    <div class="gp-pkg-hero-overline">Wedding Package Collections</div>
    <h1>Curated <em>Packages</em></h1>
    <p>Hand-picked bundles of wedding services designed to make your planning effortless. Each package type brings together the best vendors in their category.</p>
  </section>

  <!-- BOUTIQUE DIVIDER -->
  <div class="gp-divider-boutique" aria-hidden="true">
    <span class="gp-divider-line-deco"></span>
    <span class="gp-divider-diamond"></span>
    <span class="gp-divider-text">Find your perfect package</span>
    <span class="gp-divider-diamond"></span>
    <span class="gp-divider-line-deco"></span>
  </div>

  <!-- BOUTIQUE SEARCH PANEL -->
  <section class="gp-search-boutique gp-reveal" aria-label="Search and filter">
    <form class="gp-search-panel" method="GET" action="<?= URLROOT ?>/customerServices/packages">
      <div class="gp-search-rows">
        <div class="gp-search-row-fields">
          <div class="gp-search-field-boutique">
            <label for="q">Search</label>
            <input id="q" type="search" name="q" value="<?= $h($filters['search'] ?? '') ?>" placeholder="Wedding, photography, floral…">
          </div>
          <div class="gp-search-field-boutique">
            <label for="f-category">Category</label>
            <select id="f-category" name="category">
              <option value="all" <?= $activeCategory === 'all' ? 'selected' : '' ?>>All categories</option>
              <?php foreach ($categories as $cat):
                $slug = $cat['slug'] ?? strtolower($cat['name'] ?? '');
              ?>
                <option value="<?= $h($slug) ?>" <?= ($activeCategory === $slug) ? 'selected' : '' ?>>
                  <?= $h($cat['name'] ?? '') ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="gp-search-field-boutique">
            <label for="f-sort">Sort by</label>
            <select id="f-sort" name="sort">
              <option value="featured" <?= $activeSort === 'featured' ? 'selected' : '' ?>>Featured</option>
              <option value="price_low" <?= $activeSort === 'price_low' ? 'selected' : '' ?>>Price: low first</option>
              <option value="price_high" <?= $activeSort === 'price_high' ? 'selected' : '' ?>>Price: high first</option>
              <option value="name_az" <?= $activeSort === 'name_az' ? 'selected' : '' ?>>Name: A–Z</option>
              <option value="name_za" <?= $activeSort === 'name_za' ? 'selected' : '' ?>>Name: Z–A</option>
            </select>
          </div>
          <button class="gp-search-btn" type="submit">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            Find
          </button>
        </div>
      </div>
    </form>
  </section>

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

  <!-- BOUTIQUE CATEGORY CARDS -->
  <?php if (!empty($categories)): ?>
  <div class="gp-cats-boutique gp-reveal" role="list" aria-label="Filter by category">
    <div class="gp-cats-strip">
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
          'attire' => '👗',
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
        function pkgCatIcon($slug, $catIcons) {
          $slugLower = strtolower($slug);
          return $catIcons[$slugLower] ?? '✦';
        }
      ?>
      <a class="gp-cat-card <?= $activeCategory === 'all' ? 'active' : '' ?>"
         href="<?= URLROOT ?>/customerServices/packages?category=all&q=<?= $h($filters['search'] ?? '') ?>&sort=<?= $h($activeSort) ?>"
         role="listitem">
        <span class="gp-cat-icon">✦</span>
        All
        <span class="gp-cat-count"><?= (int)$totalServices ?></span>
      </a>
      <?php foreach ($categories as $cat):
        $slug = $cat['slug'] ?? strtolower($cat['name'] ?? '');
        $isActive = $activeCategory === $slug || $activeCategory === ($cat['name'] ?? '');
      ?>
        <a class="gp-cat-card <?= $isActive ? 'active' : '' ?>"
           href="<?= URLROOT ?>/customerServices/packages?category=<?= $h($slug) ?>&q=<?= $h($filters['search'] ?? '') ?>&sort=<?= $h($activeSort) ?>"
           role="listitem">
          <span class="gp-cat-icon"><?= pkgCatIcon($slug, $catIcons) ?></span>
          <?= $h($cat['name'] ?? '') ?>
          <?php if (!empty($cat['service_count'])): ?>
          <span class="gp-cat-count"><?= (int)$cat['service_count'] ?></span>
          <?php endif; ?>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- PACKAGE GRID -->
  <section class="gp-pkg-section" aria-label="Package types">
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
                <span class="gp-pkg-price"><?= $money($pkg['package_price'] ?? $pkg['base_price'] ?? 0) ?></span>
                <span class="gp-pkg-price-label">Complete package price</span>
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
<?php include APPROOT . '/views/partials/cookie-consent.php'; ?>
</body>
</html>
