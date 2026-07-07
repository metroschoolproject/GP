<?php
$items = $items ?? [];
$total = (float)($total ?? 0);
$cartCount = (int)($cartCount ?? 0);
$includedServiceWarning = $includedServiceWarning ?? null;
$addonError = trim((string)($addonError ?? ''));
$isGuest = $isGuest ?? false;
$guestItems = $guestItems ?? [];

$isLoggedIn = !empty($_SESSION['session_uid']);
$authNavUrl = $isLoggedIn ? URLROOT . '/users/logout' : URLROOT . '/users/auth';
$authNavLabel = $isLoggedIn ? 'Logout' : 'Sign in';

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
$formatDate = function ($value) {
    $timestamp = strtotime((string)$value);
    return $timestamp ? date('d M Y', $timestamp) : (string)$value;
};
$formatTime = function ($value) {
    $timestamp = strtotime((string)$value);
    return $timestamp ? date('g:i A', $timestamp) : (string)$value;
};
$formatTimeRange = function ($start, $end) use ($formatTime) {
    $start = trim((string)$start);
    $end = trim((string)$end);
    if ($start === '' && $end === '') return '';
    if ($start === '' || $start === $end) return $formatTime($end ?: $start);
    return $formatTime($start) . ' - ' . $formatTime($end);
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" type="image/png" href="<?= URLROOT ?>/public/images/home/gp_logo.png">
<title>My Cart — Golden Promise</title>
<?php include APPROOT . '/views/partials/ga-tracking.php'; ?>
<?php
$publicCssVersion = file_exists(APPROOT . '/../public/css/app.css') ? filemtime(APPROOT . '/../public/css/app.css') : time();
?>
<link rel="stylesheet" href="<?= URLROOT ?>/public/css/app.css?v=<?= $publicCssVersion ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
/* ─── Tokens ─────────────────────────────────────────── */
:root {
  --bg:          #f2e4d4;
  --bg2:         #ede0cf;
  --surface:     #faf6f1;
  --card:        rgba(242,230,218,0.85);
  --rule:        rgba(178,143,110,0.22);
  --rule-strong: rgba(178,143,110,0.45);
  --plum:        #6b4459;
  --plum-dk:     #4e3141;
  --plum-lt:     #9b7289;
  --rose:        #c27a8e;
  --gold:        #b8924a;
  --gold-lt:     #e8c882;
  --muted:       #a08878;
  --text:        #1a1118;
  --text2:       #5c4a54;
  --danger:      #b94b4b;

  --r-sm: 8px;
  --r-md: 14px;
  --r-lg: 20px;
  --r-xl: 28px;

  --font-d: 'Playfair Display', Georgia, serif;
  --font-b: 'Poppins', system-ui, sans-serif;
  --pad-x: clamp(20px, 5vw, 72px);

  --ease-expo: cubic-bezier(0.19, 1, 0.22, 1);
  --ease-back: cubic-bezier(0.34, 1.56, 0.64, 1);
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html { scroll-behavior: smooth; }

body {
  background: var(--bg);
  color: var(--text);
  font-family: var(--font-b);
  font-size: 14px;
  line-height: 1.6;
  -webkit-font-smoothing: antialiased;
  min-height: 100vh;
  display: flex; flex-direction: column;
  overflow-x: hidden;
}

a { color: inherit; text-decoration: none; }
img { display: block; max-width: 100%; }
button { font-family: var(--font-b); outline: none; cursor: pointer; }

/* ─── Ambient background orbs ───────────────────────── */
.gp-cart-page .gp-floating-cart,
.gp-cart-page .floating-cart {
  display: none !important;
}

.gp-orbs {
  position: fixed; inset: 0; z-index: 0; pointer-events: none; overflow: hidden;
}
.gp-orb {
  position: absolute; border-radius: 50%;
  filter: blur(80px);
  opacity: 0;
  animation: orbFloat 20s ease-in-out infinite;
}
.gp-orb-1 {
  width: 600px; height: 600px;
  background: radial-gradient(circle, rgba(107,68,89,0.12) 0%, transparent 70%);
  top: -200px; right: -100px;
  animation-delay: 0s; animation-duration: 18s;
}
.gp-orb-2 {
  width: 500px; height: 500px;
  background: radial-gradient(circle, rgba(184,146,74,0.08) 0%, transparent 70%);
  bottom: -150px; left: -100px;
  animation-delay: -9s; animation-duration: 22s;
}
.gp-orb-3 {
  width: 350px; height: 350px;
  background: radial-gradient(circle, rgba(194,122,142,0.10) 0%, transparent 70%);
  top: 40%; left: 40%;
  animation-delay: -5s; animation-duration: 15s;
}
@keyframes orbFloat {
  0%   { opacity: 0; transform: translate(0,0) scale(1); }
  15%  { opacity: 1; }
  50%  { transform: translate(40px,-30px) scale(1.08); }
  85%  { opacity: 1; }
  100% { opacity: 0; transform: translate(0,0) scale(1); }
}

/* ─── Header ─────────────────────────────────────────── */
.gp-header {
  position: sticky; top: 0; z-index: 100;
  display: grid;
  grid-template-columns: auto 1fr auto;
  align-items: center;
  gap: 24px;
  padding: 0 var(--pad-x);
  height: 68px;
  border-bottom: 1px solid var(--rule);
  background: rgba(242,228,212,0.82);
  backdrop-filter: blur(24px) saturate(1.4);
  -webkit-backdrop-filter: blur(24px) saturate(1.4);
  transition: background 0.3s;
}

.gp-brand {
  display: flex; align-items: center; gap: 12px;
  font-size: 17px; font-weight: 800;
  color: var(--text); white-space: nowrap;
}

.gp-brand-mark {
  position: relative;
  display: grid; place-items: center;
  width: 38px; height: 38px; border-radius: 50%;
  background: var(--plum); color: #fffaf3;
  font-size: 13px; font-weight: 700; letter-spacing: 1px;
  overflow: hidden;
}
.gp-brand-mark::after {
  content: '';
  position: absolute; inset: 0; border-radius: 50%;
  background: linear-gradient(135deg, rgba(252,248,245,0.18) 0%, transparent 60%);
}

.gp-header-nav {
  display: flex; align-items: center; justify-content: center; gap: 2px;
}
.gp-header-nav a {
  padding: 7px 16px; border-radius: 999px;
  font-size: 13px; font-weight: 600; color: var(--text2);
  transition: all 0.22s;
  position: relative;
}
.gp-header-nav a:hover { color: var(--plum); background: rgba(107,68,89,0.08); }

.gp-header-actions { display: flex; align-items: center; gap: 10px; }

.gp-cart-badge {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 7px 13px 7px 9px; border-radius: 999px;
  border: 1px solid var(--rule-strong);
  background: rgba(252,248,245,0.7);
  color: var(--plum); font-size: 13px; font-weight: 700;
  backdrop-filter: blur(8px);
  transition: all 0.22s;
}
.gp-cart-badge:hover { border-color: var(--plum); background: rgba(107,68,89,0.07); }
.gp-cart-count {
  display: inline-flex; align-items: center; justify-content: center;
  min-width: 20px; height: 20px; padding: 0 5px; border-radius: 999px;
  background: var(--plum); color: #fcf8f5;
  font-size: 10px; font-weight: 700;
}

.gp-cta-header {
  display: inline-flex; align-items: center; gap: 6px;
  height: 38px; padding: 0 18px; border-radius: 999px; border: none;
  background: var(--plum); color: #fffaf3;
  font-size: 13px; font-weight: 700;
  box-shadow: 0 8px 24px rgba(107,68,89,0.22);
  transition: all 0.22s var(--ease-expo);
}
.gp-cta-header:hover { background: var(--plum-dk); transform: translateY(-1px); box-shadow: 0 12px 32px rgba(107,68,89,0.28); }

/* ─── PROFILE DROPDOWN ──────────────────────────────── */
.gp-profile-dropdown { position: relative; }

.gp-profile-btn {
  display: flex; align-items: center; gap: 8px;
  padding: 4px 12px 4px 4px;
  border-radius: 999px;
  border: 1px solid var(--cream-dk);
  background: var(--white);
  cursor: pointer;
  transition: all 0.2s;
  color: var(--plum);
  font-family: var(--font-body);
  font-size: 13px;
  font-weight: 600;
}
.gp-profile-btn:hover { border-color: var(--plum); background: rgba(107,68,89,0.06); }

.gp-profile-avatar {
  display: grid; place-items: center;
  width: 32px; height: 32px;
  border-radius: 50%;
  background: var(--plum);
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
  border: 1px solid var(--cream-dk);
  background: var(--white);
  opacity: 0;
  visibility: hidden;
  transform: translateY(-4px);
  transition: all 0.15s var(--ease-expo);
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
  color: var(--text);
  transition: all 0.15s;
}
.gp-profile-menu-item:hover { background: rgba(107,68,89,0.06); }

.gp-profile-menu-item--danger { color: var(--danger); }
.gp-profile-menu-item--danger:hover { background: rgba(185,75,75,0.08); }

/* ─── Page shell ─────────────────────────────────────── */
.gp-page {
  position: relative;
  z-index: 1;
  flex: 1;
  padding: 0 var(--pad-x) 80px;   /* remove top space */
  max-width: 1320px;
  margin: 0 auto;
  width: 100%;
}

/* ─── Page header ────────────────────────────────────── */
.gp-page-head{
    position:relative;
    display:grid;
    place-items:center;
    min-height:240px;
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

.gp-page-head::before {
  content: "";
  position: absolute;
  inset: 0;
  background:
    linear-gradient(rgba(0, 0, 0, 0.28), rgba(44, 26, 34, 0.35)),
    url("<?= URLROOT ?>/app/views/main/images/imageBanner.png") center center / cover no-repeat;
  transform: scale(1.08);
  filter: blur(3px);
  z-index: 0;
}
.gp-page-head::after {
  content: "";
  position: absolute;
  inset: 0;
  background: linear-gradient(to bottom, rgba(249,241,233,0.08), rgba(249,241,233,0.18));
  z-index: 1;
}

.gp-page-head > * {
  position: relative;
  z-index: 2;
}
.gp-page-title {
  font-family: var(--font-d);
  font-size: clamp(34px, 4vw, 58px);
  font-weight: 700;
  color: #fffaf5;
  line-height: 1.05;
  letter-spacing: 0;
  margin-top: 112px;
  text-shadow: 0 10px 30px rgba(26, 17, 24, 0.22);
}

.gp-page-eyebrow {
  order: 2;
  display: block;
  margin-top: 14px;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  color: rgba(255, 248, 239, 0.92);
  position: relative;
  top: -4px;
}
/* ─── Two-column layout ──────────────────────────────── */
.gp-layout {
  display: grid;
  grid-template-columns: minmax(0, 1fr) 420px;
  gap: 34px;
  align-items: start;
}

/* ─── Cart items ─────────────────────────────────────── */
.gp-items {
  display: flex;
  flex-direction: column;
  gap: 14px;
  max-width: 760px;
}

.gp-item {
  display: grid;
  grid-template-columns: 164px minmax(0, 1fr);
  min-height: 152px;
  gap: 16px;
  align-items: stretch;
  position: relative;
  padding: 10px 18px 12px 10px;
  background: rgba(252,248,245,0.78);
  border: 1px solid var(--rule);
  border-radius: 16px;
  overflow: visible;
  opacity: 0;
  transform: translateY(24px);
  transition: border-color 0.25s, transform 0.35s var(--ease-expo);
}
.gp-item.visible {
  opacity: 1; transform: translateY(0);
}
.gp-item:hover {
  border-color: rgba(184,146,74,0.32);
  transform: translateY(-2px);
}

/* Image column */
.gp-item-thumb {
    width: 164px;
    height: 132px;      /* Fixed image height */
    min-height: 132px;
    max-height: 132px;
    overflow: hidden;
    border-radius: 9px;
    background: linear-gradient(160deg, #e8d9c8, #d9c8b5);
    flex-shrink: 0;
    position: relative;
    overflow: hidden;
    border-radius: 9px;

}
.gp-item-thumb img {
  width: 100%; height: 100%; object-fit: cover;
  transition: transform 0.55s var(--ease-expo);
}
.gp-item:hover .gp-item-thumb img { transform: scale(1.06); }

.gp-item-thumb-placeholder {
  width: 100%; height: 100%;
  display: grid; place-items: center;
  color: var(--muted);
  min-height: 132px;
}

/* category ribbon on image */
.gp-item-cat-ribbon{
    position: absolute;
    left: 50%;
    bottom: 8px;
    transform: translateX(-50%);

    display: inline-flex;
    align-items: center;
    justify-content: center;

    padding: 3px 8px;          /* Smaller tag */
    min-width: auto;
    max-width: calc(100% - 20px);

    background: rgba(0,0,0,0.45);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);

    color: #fff;
    border-radius: 5px;        /* Less rounded */

    font-size: 8px;            /* Smaller text */
    font-weight: 700;
    letter-spacing: 0.08em;
    line-height: 1;

    text-transform: uppercase;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;

    z-index: 10;
}
.gp-item-cat-ribbon::before,
.gp-item-cat-ribbon::after {
  content: none;
}

/* Body */
.gp-item-body {
  grid-column: 2;
  padding: 8px 52px 0 0;
  display: flex; flex-direction: column; gap: 8px;
  min-width: 0;
}

.gp-item-name {
  font-family: var(--font-b);
  font-size: 16px; font-weight: 800;
  color: #20151f; line-height: 1.18;
  letter-spacing: 0;
}
.gp-item-name a { transition: color 0.2s; }
.gp-item-name a:hover { color: var(--plum); }

.gp-item-supplier {
  display: flex; align-items: center; gap: 6px;
  font-size: 12px; font-weight: 500; color: #40353e;
}
.gp-item-supplier svg,
.gp-item-date-line svg {
  width: 12px;
  height: 12px;
  color: #7b5b28;
  stroke-width: 1.9;
  flex-shrink: 0;
}

.gp-item-meta {
  display: flex; flex-wrap: wrap; gap: 8px;
  margin-top: 10px;
}
.gp-item-pill {
  display: inline-flex; align-items: center; gap: 4px;
  padding: 4px 10px; border-radius: 999px;
  background: rgba(107,68,89,0.07);
  font-size: 11px; font-weight: 600; color: var(--plum);
  border: 1px solid rgba(107,68,89,0.12);
}
.gp-item-date-line {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 7px;
  color: #40353e;
  font-size: 12px;
  font-weight: 500;
}
.gp-item-date-line span {
  display: inline-flex;
  align-items: center;
  gap: 5px;
}
.gp-item-meta-separator {
  color: rgba(107,68,89,0.42);
  font-weight: 300;
}
.gp-item-category-divider {
  display: none;
}
.gp-item-category-divider::before,
.gp-item-category-divider::after {
  content: '';
  border-top: 1px dotted rgba(184,129,53,0.72);
}

.gp-package-includes {
  margin-top: 12px;
  padding: 14px 14px 12px;
  border: 1px solid rgba(184,146,74,0.24);
  border-radius: 13px;
  background: rgba(252,248,245,0.78);
}
.gp-package-includes-head {
  display: flex; align-items: center; justify-content: space-between; gap: 10px;
  margin-bottom: 12px;
}
.gp-package-includes-title {
  display: flex; align-items: center; gap: 6px;
  color: var(--plum);
  font-size: 10px; font-weight: 800; letter-spacing: .09em;
  text-transform: uppercase;
}
.gp-package-includes-count {
  color: var(--muted);
  font-size: 10px; font-weight: 700;
}
/* Timeline list */
.gp-package-service-list {
  display: flex;
  flex-direction: column;
  gap: 0;
  position: relative;
  padding-left: 18px;
}
.gp-package-service-list::before {
  content: '';
  position: absolute;
  left: 7px;
  top: 6px;
  bottom: 6px;
  width: 1.5px;
  background: rgba(107,68,89,0.16);
}
.gp-package-service {
  display: flex;
  align-items: flex-start;
  gap: 14px;
  position: relative;
  padding: 0 0 18px 0;
  min-width: 0;
  border: 0;
  background: transparent;
}
.gp-package-service:last-child { padding-bottom: 0; }
/* Timeline dot */
.gp-package-tl-dot {
  position: relative;
  z-index: 1;
  flex-shrink: 0;
  width: 14px;
  height: 14px;
  border-radius: 50%;
  margin-top: 1px;
  margin-left: -18px;
  border: 2px solid var(--plum);
  background: #fffaf5;
}
.gp-package-service-time:not(.is-pending) ~ .gp-package-tl-dot,
.gp-package-service[data-scheduled="true"] .gp-package-tl-dot {
  background: var(--plum);
  border-color: var(--plum);
}
.gp-package-tl-dot.is-pending {
  border-color: var(--gold);
  background: rgba(184,146,74,0.15);
}
/* Content */
.gp-package-tl-content {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
  gap: 3px;
}
.gp-package-service-name {
  overflow: hidden;
  color: var(--text);
  font-size: 12px; font-weight: 700;
  line-height: 1.3;
  text-overflow: ellipsis; white-space: nowrap;
}
.gp-package-service-meta {
  overflow: hidden;
  margin-top: 0;
  color: var(--muted);
  font-size: 10px; font-weight: 500;
  line-height: 1.35;
  text-overflow: ellipsis; white-space: nowrap;
}
.gp-package-service-time {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  margin-top: 2px;
  padding: 2px 8px;
  border-radius: 999px;
  background: rgba(107,68,89,0.06);
  color: var(--plum);
  font-size: 9px;
  font-weight: 700;
  line-height: 1.3;
  max-width: fit-content;
}
.gp-package-service-time.is-pending {
  background: rgba(184,146,74,0.10);
  color: #8a682d;
}
/* Thumb — hidden in timeline mode */
.gp-package-service-thumb { display: none; }
.gp-package-schedule-note {
  display: flex;
  align-items: flex-start;
  gap: 7px;
  margin-top: 10px;
  padding-top: 10px;
  border-top: 1px solid rgba(184,146,74,0.18);
  color: var(--muted);
  font-size: 10px;
  font-weight: 600;
  line-height: 1.45;
}

.gp-edit-form {
  grid-column: 1 / -1;
  margin-top: 0;
}
.gp-edit-form.is-open {
  margin-top: 2px;
}
.gp-edit-toggle {
  position: absolute;
  top: 12px;
  right: 12px;
  display: inline-flex; align-items: center; justify-content: center;
  width: 32px;
  height: 32px;
  border: 0;
  border-radius: 12px;
  background: #f2eeec;
  color: #170f16;
  padding: 0;
  transition: background 0.2s, transform 0.2s;
}
.gp-edit-toggle:hover { background: #ece4df; transform: translateY(-1px); }
.gp-edit-toggle svg { width: 15px; height: 15px; stroke-width: 2.1; }
.gp-edit-fields {
  display: none;
  grid-template-columns: minmax(150px, 0.85fr) minmax(260px, 1.8fr) minmax(110px, 0.7fr);
  gap: 10px;
  align-items: start;
  position: relative;
  margin-top: 8px;
  padding: 16px 84px 56px 16px;
  border-top: 1px solid rgba(178,143,110,0.18);
  border-radius: 0 0 12px 12px;
  background: rgba(252,248,245,0.78);
}
.gp-edit-form.is-open .gp-edit-fields { display: grid; }
.gp-edit-field {
  display: flex;
  flex-direction: column;
  gap: 4px;
  min-width: 0;
}
.gp-edit-field.is-wide { min-width: 0; }
.gp-edit-field label {
  font-size: 10px;
  font-weight: 700;
  color: var(--muted);
  text-transform: uppercase;
  letter-spacing: 0.08em;
}
.gp-edit-field input {
  width: 100%;
  min-height: 36px;
  border: 1px solid var(--rule-strong);
  border-radius: 7px;
  background: #fffaf7;
  color: var(--text);
  font: inherit;
  font-size: 12px;
  padding: 7px 9px;
}
.gp-edit-date-control {
  position: relative;
  display: grid;
  grid-template-columns: 16px minmax(0, 1fr) 14px;
  align-items: center;
  gap: 7px;
  min-height: 36px;
  padding: 0 10px;
  border: 1px solid var(--rule-strong);
  border-radius: 7px;
  background: #fffaf7;
  color: var(--text);
  overflow: hidden;
  cursor: pointer;
  box-shadow: 0 4px 14px rgba(63,36,26,0.06);
}
.gp-edit-date-control:focus-visible {
  outline: 2px solid rgba(107,68,89,0.28);
  outline-offset: 2px;
}
.gp-edit-date-control svg {
  width: 14px;
  height: 14px;
  color: var(--plum);
  stroke-width: 2;
}
.gp-edit-date-display {
  min-width: 0;
  color: var(--text);
  font-size: 12px;
  font-weight: 700;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.gp-edit-date-control input[type="date"] {
  position: absolute;
  inset: 0;
  width: 100%;
  min-height: 100%;
  opacity: 0;
  pointer-events: none;
}
.gp-edit-field input[readonly] {
  background: rgba(107,68,89,0.05);
  color: var(--text2);
}
.gp-edit-slots {
  display: flex;
  flex-wrap: wrap;
  gap: 5px;
  min-height: 34px;
  align-content: flex-start;
}
.gp-edit-slot-option {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0;
  min-width: 72px;
  min-height: 24px;
  padding: 4px 6px;
  border: 1px solid rgba(107,68,89,0.14);
  border-radius: 6px;
  background: rgba(107,68,89,0.05);
  color: var(--plum);
  font-size: 9px;
  font-weight: 700;
  line-height: 1.2;
  white-space: nowrap;
  cursor: pointer;
}
.gp-edit-slot-option input {
  position: absolute;
  opacity: 0;
  pointer-events: none;
}
.gp-edit-slot-option:has(input:checked) {
  border-color: var(--plum);
  background: var(--plum);
  color: #fffaf3;
}
.gp-edit-slot-note {
  color: var(--muted);
  font-size: 10px;
  font-weight: 600;
}
.gp-save-btn {
  position: absolute;
  top: auto;
  right: 14px;
  bottom: 14px;
  min-height: 36px;
  border: 0;
  border-radius: 7px;
  background: var(--plum);
  color: #fffaf3;
  font-size: 11px;
  font-weight: 800;
  padding: 0 16px;
  white-space: nowrap;
}
.gp-save-btn:hover { background: var(--plum-dk); }

.gp-calendar-popover {
  position: fixed;
  z-index: 10010;
  width: min(250px, calc(100vw - 32px));
  padding: 12px;
  border: 1px solid rgba(63,36,26,0.14);
  border-radius: 10px;
  background: rgba(255,248,239,0.98);
  box-shadow: 0 24px 60px rgba(63,36,26,0.18);
  backdrop-filter: blur(18px);
  -webkit-backdrop-filter: blur(18px);
}
.gp-calendar-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  color: #3f241a;
  font-size: 12px;
  font-weight: 900;
  margin-bottom: 9px;
}
.gp-calendar-nav {
  display: inline-grid;
  place-items: center;
  width: 22px;
  height: 22px;
  border: 0;
  border-radius: 7px;
  background: transparent;
  color: #7a4e3d;
  cursor: pointer;
}
.gp-calendar-nav svg {
  width: 14px;
  height: 14px;
  stroke: currentColor;
  stroke-width: 2.2;
}
.gp-calendar-nav:hover { background: rgba(63,36,26,0.08); }
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
  color: #6f5448;
  font-size: 11px;
}
.gp-calendar-day-name {
  color: rgba(63,36,26,0.52);
  font-weight: 800;
}
.gp-calendar-day {
  border: 0;
  border-radius: 6px;
  background: transparent;
  font-weight: 800;
  cursor: pointer;
}
.gp-calendar-day:hover { background: rgba(122,78,61,0.12); }
.gp-calendar-day.is-selected {
  background: #3f241a;
  color: #fff8ef;
}
.gp-calendar-day.is-today:not(.is-selected) { outline: 1px solid rgba(63,36,26,0.28); }
.gp-calendar-day.is-disabled {
  color: rgba(63,36,26,0.24);
  cursor: not-allowed;
}

/* Right column */
.gp-item-right {
  display: contents;
}

.gp-item-price {
  font-family: var(--font-b);
  margin-top: 2px;
  font-size: 15px; font-weight: 800;
  color: var(--plum);
  white-space: nowrap;
  line-height: 1;
}
.gp-item-price-label { display: none; }

.gp-remove-btn {
  position: absolute;
  right: 16px;
  top: 104px;
  bottom: auto;
  display: inline-flex; align-items: center; justify-content: center;
  width: 32px;
  height: 32px;
  padding: 0;
  border-radius: 50%;
  border: 0;
  background: #ee2f2b;
  color: #fff;
  transition: background 0.2s, transform 0.2s;
}
.gp-remove-btn svg { width: 14px; height: 14px; stroke-width: 2.2; }
.gp-remove-btn:hover { background: #d92522; transform: translateY(-1px); }

/* ─── Sticky sidebar ─────────────────────────────────── */
.gp-sidebar {
  position: sticky; top: 84px;
  opacity: 0;
  animation: fadeUp 0.7s var(--ease-expo) 0.35s forwards;
}

.gp-summary-card {
  background: rgba(252,248,245,0.78);
  border: 1px solid var(--rule);
  border-radius: 14px;
  overflow: hidden;
}

.gp-summary-head {
  padding: 28px 30px 16px;
}

.gp-summary-label {
  margin-bottom: 8px;
  color: #817476;
  font-size: 11px;
  font-weight: 800;
  letter-spacing: 0.16em;
  text-transform: uppercase;
}
.gp-summary-title {
  color: #0f0c12;
  font-family: var(--font-a);
  font-size: clamp(24px, 2.1vw, 30px);
  font-weight: 600;
  letter-spacing: 0;
  line-height: 1.05;
}
.gp-summary-subtitle {
  margin-top: 8px;
  color: #817476;
  font-size: 13px;
  line-height: 1.3;
}

/* Line items */
.gp-summary-body { padding: 8px 30px 22px; }

.gp-line-items {
  display: flex;
  flex-direction: column;
  gap: 14px;
  margin-bottom: 18px;
}

.gp-summary-service {
  display: grid;
  grid-template-columns: 56px minmax(0, 1fr) auto;
  gap: 15px;
  align-items: center;
  padding: 10px 12px 14px;
  border: 1px solid rgba(178,143,110,0.18);
  border-radius: 12px;
  background: rgba(252,248,245,0.78);
}

.gp-summary-service-icon {
  display: grid;
  place-items: center;
  width: 56px;
  height: 56px;
  border-radius: 8px;
  background: rgba(107,68,89,0.09);
  color: var(--plum);
  overflow: hidden;
}
.gp-summary-service-icon img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.gp-summary-service-icon svg {
  width: 24px;
  height: 24px;
  stroke-width: 1.7;
}

.gp-summary-service-main {
  min-width: 0;
}

.gp-summary-service-name {
  color: #111016;
  font-size: 13px;
  font-weight: 800;
  line-height: 1.25;
}

.gp-summary-service-date {
  display: flex;
  align-items: center;
  gap: 7px;
  margin-top: 6px;
  color: #8d8187;
  font-size: 11px;
  font-weight: 500;
}
.gp-summary-service-date svg {
  width: 13px;
  height: 13px;
  color: #9b8d98;
  stroke-width: 2;
  flex-shrink: 0;
}

.gp-summary-service-price {
  color: #111016;
  font-size: 13px;
  font-weight: 800;
  white-space: nowrap;
}

.gp-summary-divider {
  height: 1px;
  background: rgba(178,143,110,0.20);
  margin: 0 0 14px;
}

.gp-line {
  display: flex;
  justify-content: space-between;
  align-items: baseline;
  gap: 16px;
}
.gp-line-name {
  color: #1f1a21;
  font-size: 13px;
  font-weight: 500;
}
.gp-line-dots { display: none; }
.gp-line-val {
  color: #1f1a21;
  font-size: 13px;
  font-weight: 500;
  white-space: nowrap;
}

.gp-line-divider { height: 1px; background: rgba(178,143,110,0.20); margin: 10px 0 4px; }

.gp-total-row {
  display: flex;
  justify-content: space-between;
  align-items: baseline;
  gap: 16px;
  padding: 20px 0 0;
  border-top: 1px solid rgba(178,143,110,0.20);
}
.gp-total-label {
  color: #111016;
  font-size: 13px;
  font-weight: 800;
}
.gp-total-amount {
  color: var(--plum);
  font-family: var(--font-b);
  font-size: clamp(21px, 1.9vw, 25px);
  font-weight: 800;
  line-height: 1;
  white-space: nowrap;
}

/* CTAs */
.gp-summary-footer {
  display: flex;
  flex-direction: column;
  gap: 11px;
  padding: 0 30px 26px;
}

.gp-btn-book {
  display: flex; align-items: center; justify-content: center; gap: 8px;
  height: 52px; border-radius: 5px; border: none;
  background: var(--plum); color: #fffaf3;
  font-size: 13px; font-weight: 800;
  letter-spacing: 0;
  box-shadow: 0 10px 28px rgba(107,68,89,0.28);
  transition: all 0.3s var(--ease-expo);
  position: relative; overflow: hidden;
}
.gp-btn-book::before {
  content: '';
  position: absolute; top: 0; left: -100%; width: 100%; height: 100%;
  background: linear-gradient(90deg, transparent, rgba(252,248,245,0.12), transparent);
  transition: left 0.5s var(--ease-expo);
}
.gp-btn-book:hover { background: var(--plum-dk); transform: translateY(-2px); box-shadow: 0 18px 40px rgba(107,68,89,0.32); }
.gp-btn-book:hover::before { left: 100%; }
.gp-btn-book:active { transform: translateY(0); }

.gp-btn-more {
  display: flex; align-items: center; justify-content: center; gap: 10px;
  height: 44px; border-radius: 7px;
  border: 1px solid rgba(107,68,89,0.48);
  background: transparent; color: var(--plum);
  font-size: 12px; font-weight: 800;
  transition: all 0.22s;
}
.gp-btn-more:hover { border-color: var(--plum); color: var(--plum); background: rgba(107,68,89,0.05); }

/* Trust badges */
.gp-trust {
  display: flex;
  flex-direction: column;
  gap: 13px;
  padding: 20px 26px 22px;
  border-top: 1px solid rgba(178,143,110,0.16);
}
.gp-trust-item {
  display: grid;
  grid-template-columns: 36px 1fr;
  gap: 11px;
  align-items: center;
}
.gp-trust-icon {
  display: grid;
  place-items: center;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: rgba(107,68,89,0.07);
  color: var(--plum);
}
.gp-trust-icon svg {
  width: 18px;
  height: 18px;
  stroke-width: 1.9;
}
.gp-trust-title {
  color: #3e353b;
  font-size: 11px;
  font-weight: 800;
  line-height: 1.25;
}
.gp-trust-copy {
  margin-top: 2px;
  color: #8d8187;
  font-size: 10px;
  line-height: 1.35;
}

/* ─── Empty state ────────────────────────────────────── */
.gp-empty {
  text-align: center; padding: 96px 24px;
  border: 1px dashed rgba(107,68,89,0.18);
  border-radius: var(--r-xl); background: var(--card);
  opacity: 0;
  animation: fadeUp 0.8s var(--ease-expo) 0.2s forwards;
}
.gp-empty-icon {
  display: inline-flex; align-items: center; justify-content: center;
  width: 88px; height: 88px; border-radius: 50%;
  background: rgba(107,68,89,0.07); color: var(--plum);
  margin-bottom: 28px;
}
.gp-empty h2 { font-family: var(--font-d); font-size: 36px; font-weight: 600; color: var(--text); margin-bottom: 12px; }
.gp-empty p { color: var(--muted); font-size: 14px; max-width: 420px; margin: 0 auto 32px; }

.gp-empty-actions {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  flex-wrap: wrap;
}
.gp-btn-browse {
  display: inline-flex; align-items: center; gap: 8px;
  height: 50px; padding: 0 32px; border-radius: 999px; border: none;
  background: var(--plum); color: #fcf8f5;
  font-size: 14px; font-weight: 700;
  box-shadow: 0 8px 24px rgba(107,68,89,0.22);
  transition: all 0.25s var(--ease-expo);
}
.gp-btn-browse:hover { background: var(--plum-dk); transform: translateY(-2px); box-shadow: 0 16px 36px rgba(107,68,89,0.28); }
.gp-btn-browse.secondary {
  border: 1px solid var(--rule-strong);
  background: rgba(252,248,245,0.75);
  color: var(--plum);
  box-shadow: none;
}
.gp-btn-browse.secondary:hover {
  border-color: var(--plum);
  background: rgba(107,68,89,0.08);
  color: var(--plum-dk);
  box-shadow: none;
}

/* ─── Guest cart state ─────────────────────────────────── */
.gp-guest-cart { max-width: 600px; margin: 40px auto; text-align: center; }
.gp-guest-cart-header { display: flex; flex-direction: column; align-items: center; gap: 16px; margin-bottom: 24px; }
.gp-guest-cart-icon { color: #d97706; }
.gp-guest-cart-header h2 { font-family: var(--font-d); font-size: 24px; font-weight: 600; color: var(--text); margin: 0; }
.gp-guest-cart-header p { max-width: 380px; margin: 4px auto 0; font-size: 13px; color: var(--muted); line-height: 1.5; }
.gp-guest-cart-items { display: flex; flex-direction: column; gap: 8px; margin-bottom: 24px; }
.gp-guest-cart-item { display: flex; justify-content: space-between; align-items: center; padding: 10px 14px; border: 1px solid var(--rule); border-radius: 10px; background: var(--card); font-size: 13px; }
.gp-guest-cart-date { color: var(--muted); font-size: 12px; }
.gp-guest-cart-actions { display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; }
.gp-guest-cart-actions .gp-btn-secondary { display: inline-flex; align-items: center; gap: 6px; padding: 12px 24px; border: 1px solid var(--rule-strong, #ead8c7); border-radius: 999px; background: #fff; color: var(--text, #34232b); font-size: 13px; font-weight: 600; cursor: pointer; text-decoration: none; transition: all .14s; }
.gp-guest-cart-actions .gp-btn-secondary:hover { border-color: var(--plum); color: var(--plum); background: rgba(107,68,89,0.05); }

/* ─── Included service reminder ───────────────────────── */
.gp-included-reminder {
  display: grid;
  grid-template-columns: 40px 1fr auto;
  gap: 16px;
  align-items: start;
  margin-bottom: 24px;
  padding: 18px;
  border: 1px solid rgba(184,146,74,0.42);
  border-radius: var(--r-lg);
  background: var(--card);
}
.gp-included-icon {
  display: grid;
  place-items: center;
  width: 40px;
  height: 40px;
  border-radius: 14px;
  background: rgba(184,146,74,0.14);
  color: var(--gold);
}
.gp-included-title {
  font-family: var(--font-d);
  font-size: 20px;
  font-weight: 600;
  color: var(--text);
  line-height: 1.15;
}
.gp-included-copy {
  margin-top: 5px;
  color: var(--text2);
  font-size: 13px;
}
.gp-included-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  justify-content: flex-end;
}
.gp-included-actions form { display: contents; }
.gp-included-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-height: 38px;
  padding: 0 14px;
  border-radius: var(--r-sm);
  border: 1px solid var(--rule-strong);
  font-size: 12px;
  font-weight: 800;
  white-space: nowrap;
  cursor: pointer;
}
.gp-included-btn.primary {
  border-color: var(--plum);
  background: var(--plum);
  color: #fffaf3;
}
.gp-included-btn.secondary {
  background: transparent;
  color: var(--text2);
}
.gp-included-btn.secondary:hover { color: var(--plum); border-color: var(--plum); }

/* ─── Remove confirmation modal ──────────────────────── */
.gp-remove-modal {
  position: fixed;
  inset: 0;
  z-index: 1000;
  display: grid;
  place-items: center;
  padding: 20px;
  background: rgba(26,17,24,0.38);
  opacity: 0;
  pointer-events: none;
  transition: opacity 0.2s ease;
}
.gp-remove-modal.is-open {
  opacity: 1;
  pointer-events: auto;
}
.gp-remove-dialog {
  width: min(100%, 360px);
  padding: 24px;
  border: 1px solid rgba(178,143,110,0.24);
  border-radius: 14px;
  background: #fcf8f5;
  box-shadow: 0 24px 70px rgba(26,17,24,0.20);
  text-align: center;
  transform: translateY(10px) scale(0.98);
  transition: transform 0.22s var(--ease-expo);
}
.gp-remove-modal.is-open .gp-remove-dialog {
  transform: translateY(0) scale(1);
}
.gp-remove-dialog-icon {
  display: grid;
  place-items: center;
  width: 48px;
  height: 48px;
  margin: 0 auto 14px;
  border-radius: 50%;
  background: rgba(185,75,75,0.10);
  color: var(--danger);
}
.gp-remove-dialog-icon svg {
  width: 22px;
  height: 22px;
  stroke-width: 2.2;
}
.gp-remove-dialog h2 {
  color: var(--text);
  font-family: var(--font-b);
  font-size: 18px;
  font-weight: 800;
  line-height: 1.25;
}
.gp-remove-dialog p {
  margin-top: 8px;
  color: var(--text2);
  font-size: 12px;
  line-height: 1.55;
}
.gp-remove-dialog-actions {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
  margin-top: 20px;
}
.gp-remove-dialog-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-height: 42px;
  border-radius: 8px;
  border: 1px solid rgba(107,68,89,0.22);
  font-size: 12px;
  font-weight: 800;
  cursor: pointer;
}
.gp-remove-dialog-btn.cancel {
  background: transparent;
  color: var(--text2);
}
.gp-remove-dialog-btn.confirm {
  border-color: #d92522;
  background: #d92522;
  color: #fffaf3;
}
.gp-remove-dialog-btn.cancel:hover {
  border-color: var(--plum);
  color: var(--plum);
}
.gp-remove-dialog-btn.confirm:hover {
  background: #bd211f;
}

/* ─── Keyframes ──────────────────────────────────────── */
@keyframes fadeUp {
  from { opacity: 0; transform: translateY(20px); }
  to   { opacity: 1; transform: translateY(0); }
}

/* ─── Footer ─────────────────────────────────────────── */
.gp-footer {
  position: relative; z-index: 1;
  padding: 24px var(--pad-x);
  border-top: 1px solid var(--rule);
  display: flex; align-items: center; justify-content: space-between; gap: 16px;
  font-size: 12px; color: var(--muted);
}

/* ─── Responsive ─────────────────────────────────────── */
@media (max-width: 900px) {
  .gp-layout { grid-template-columns: 1fr; }
  .gp-sidebar { position: static; }
  .gp-included-reminder { grid-template-columns: 40px 1fr; }
  .gp-included-actions { grid-column: 1 / -1; justify-content: flex-start; }
  .gp-edit-fields { grid-template-columns: repeat(2, minmax(0, 1fr)); }
  .gp-item {
    grid-template-columns: 130px minmax(0, 1fr);
    gap: 12px;
  }
  .gp-item-name { font-size: 14px; }
}
@media (max-width: 640px) {
  .gp-summary-head,
  .gp-summary-body,
  .gp-summary-footer,
  .gp-trust {
    padding-left: 18px;
    padding-right: 18px;
  }
  .gp-summary-service {
    grid-template-columns: 44px minmax(0, 1fr);
    gap: 12px;
  }
  .gp-summary-service-icon {
    width: 44px;
    height: 44px;
  }
  .gp-summary-service-price {
    grid-column: 2;
    justify-self: start;
    margin-top: -4px;
  }
  .gp-total-row {
    align-items: flex-start;
    flex-direction: column;
    gap: 10px;
  }
  .gp-items { max-width: none; }
  .gp-item {
    grid-template-columns: 1fr;
    gap: 12px;
    padding: 12px;
  }
  .gp-item-thumb { min-height: 124px; aspect-ratio: 16 / 10; }
  .gp-item-body {
    grid-column: auto;
    padding: 0 92px 0 0;
    gap: 6px;
  }
  .gp-item-name { font-size: 14px; }
  .gp-item-supplier,
  .gp-item-date-line { font-size: 11px; }
  .gp-item-right {
    display: contents;
  }
  .gp-item-price { font-size: 13px; }
  .gp-edit-toggle { top: 12px; right: 12px; bottom: auto; width: 32px; height: 32px; }
  .gp-remove-btn { top: auto; right: 12px; bottom: 12px; width: 32px; height: 32px; }
  .gp-edit-toggle svg { width: 15px; height: 15px; }
  .gp-remove-btn svg { width: 14px; height: 14px; }
  .gp-edit-fields {
    grid-template-columns: 1fr;
    padding: 12px 12px 56px;
  }
  .gp-save-btn {
    top: auto;
    right: 12px;
    bottom: 12px;
  }
  .gp-header-nav { display: none; }
  .gp-included-reminder { grid-template-columns: 1fr; }
  .gp-included-actions { flex-direction: column; }
  .gp-included-btn { width: 100%; }
  :root { --pad-x: 16px; }
}

/* ─── Reduced motion ─────────────────────────────────── */
@media (prefers-reduced-motion: reduce) {
  .gp-item, .gp-page-head, .gp-sidebar, .gp-empty { animation: none; opacity: 1; transform: none; }
  .gp-orb { animation: none; }
}
</style>
</head>
<body class="gp-cart-page">

<!-- Ambient orbs -->
<div class="gp-orbs" aria-hidden="true">
  <div class="gp-orb gp-orb-1"></div>
  <div class="gp-orb gp-orb-2"></div>
  <div class="gp-orb gp-orb-3"></div>
</div>

<?php $gpNavActive = 'cart'; require APPROOT . '/views/layouts/customerHomeNav.php'; ?>

<!-- ─── Main ──────────────────────────────────────────── -->
<main class="gp-page">

  <!-- Page heading -->
  <div class="gp-page-head">
    <h1 class="gp-page-title">Selected Services</h1>
    <div class="gp-page-eyebrow">YOUR SELECTION</div>
  </div>

  <?php if ($addonError !== ''): ?>
  <section class="gp-included-reminder" aria-live="polite">
    <div class="gp-included-icon" aria-hidden="true"><i data-lucide="circle-alert"></i></div>
    <div>
      <div class="gp-included-title">Add-on could not be linked</div>
      <p class="gp-included-copy"><?= $h($addonError) ?></p>
    </div>
  </section>
  <?php endif; ?>

  <?php if (!empty($includedServiceWarning['item']) && !empty($includedServiceWarning['conflict'])):
    $warningItem = $includedServiceWarning['item'];
    $warningConflict = $includedServiceWarning['conflict'];
  ?>
  <section class="gp-included-reminder" aria-live="polite">
    <div class="gp-included-icon" aria-hidden="true">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v5"/><path d="M12 17h.01"/></svg>
    </div>
    <div>
      <div class="gp-included-title">This service is already included</div>
      <p class="gp-included-copy">
        <?= $h($warningConflict['service_name'] ?? 'This service') ?> is already part of
        <?= $h($warningConflict['package_name'] ?? 'a package') ?> in your cart. Add it again only if you want an extra separate booking.
      </p>
    </div>
    <div class="gp-included-actions">
      <form method="POST" action="<?= URLROOT ?>/cart/add">
        <input type="hidden" name="service_id" value="<?= (int)($warningItem['item_id'] ?? 0) ?>">
        <input type="hidden" name="date" value="<?= $h($warningItem['selected_date'] ?? '') ?>">
        <input type="hidden" name="price" value="<?= $h($warningItem['price'] ?? '') ?>">
        <input type="hidden" name="source" value="<?= $h($warningItem['source'] ?? 'custom') ?>">
        <input type="hidden" name="venue_room_id" value="<?= $h($warningItem['venue_room_id'] ?? '') ?>">
        <input type="hidden" name="slot_id" value="<?= $h($warningItem['slot_id'] ?? '') ?>">
        <input type="hidden" name="start_time" value="<?= $h($warningItem['start_time'] ?? '') ?>">
        <input type="hidden" name="end_time" value="<?= $h($warningItem['end_time'] ?? '') ?>">
        <input type="hidden" name="addon_package_id" value="<?= $h($warningItem['addon_package_id'] ?? '') ?>">
        <input type="hidden" name="addon_package_date" value="<?= $h($warningItem['addon_package_date'] ?? '') ?>">
        <input type="hidden" name="addon_package_time" value="<?= $h($warningItem['addon_package_time'] ?? '') ?>">
        <input type="hidden" name="confirm_included_service" value="1">
        <button class="gp-included-btn primary" type="submit">Add anyway</button>
      </form>
      <form method="POST" action="<?= URLROOT ?>/cart/dismissIncludedReminder">
        <button class="gp-included-btn secondary" type="submit">Keep package only</button>
      </form>
    </div>
  </section>
  <?php endif; ?>

  <?php if (empty($items) && empty($guestItems)): ?>
  <!-- ── Empty state ───────────────────────────────────── -->
  <div class="gp-empty">
    <div class="gp-empty-icon">
      <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
    </div>
    <h2>No services selected yet</h2>
    <p>Browse our curated collection of wedding services and add the ones that make your day perfect.</p>
    <div class="gp-empty-actions">
      <a class="gp-btn-browse" href="<?= URLROOT ?>/customerServices/service">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
        Services
      </a>
      <a class="gp-btn-browse secondary" href="<?= URLROOT ?>/customerServices/packages">
        Packages
      </a>
    </div>
  </div>

  <?php elseif (!empty($guestItems)): ?>
  <!-- ── Guest cart state ─────────────────────────────── -->
  <div class="gp-guest-cart">
    <div class="gp-guest-cart-header">
      <div class="gp-guest-cart-icon">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="6" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      </div>
      <div>
        <h2>You have <?= count($guestItems) ?> item<?= count($guestItems) !== 1 ? 's' : '' ?> saved</h2>
        <p>Sign in or create an account to keep your selections and complete your booking.</p>
      </div>
    </div>
    <div class="gp-guest-cart-items">
      <?php foreach ($guestItems as $gIdx => $gItem):
        $gName = htmlspecialchars($gItem['name'] ?? $gItem['item_type'] ?? 'Service', ENT_QUOTES, 'UTF-8');
        $gDate = !empty($gItem['selected_date']) ? date('M j, Y', strtotime($gItem['selected_date'])) : 'Any date';
      ?>
      <div class="gp-guest-cart-item">
        <span><?= $gName ?></span>
        <span class="gp-guest-cart-date"><?= $gDate ?></span>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="gp-guest-cart-actions">
      <a href="<?= URLROOT ?>/users/auth" class="gp-btn-browse">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
        Sign in to continue
      </a>
      <a href="<?= URLROOT ?>/users/register" class="gp-btn-secondary">
        Create account
      </a>
    </div>
  </div>

  <?php else: ?>
  <!-- ── Two-column layout ─────────────────────────────── -->
  <div class="gp-layout">

    <!-- LEFT: items list -->
    <div class="gp-items" id="gp-items">
      <?php foreach ($items as $i => $item):
        $itemId      = (int)($item['cart_item_id'] ?? 0);
        $name        = $item['service_name'] ?? 'Service';
        $supplier    = $item['supplier_name'] ?? 'Supplier';
        $category    = $item['category_name'] ?? 'Service';
        $img         = trim($item['thumbnail_url'] ?? '');
        $price       = (float)($item['cart_price'] ?? $item['price_min'] ?? $item['price_max'] ?? 0);
        $selectedDate = $item['selected_date'] ?? '';
        $startTime   = $item['start_time'] ?? '';
        $endTime     = $item['end_time'] ?? '';
        $itemType    = $item['item_type'] ?? 'service';
        $isFulldayItem = $itemType === 'service' && ($item['booking_type'] ?? 'fullday') !== 'slot';
        $timeRange   = $isFulldayItem ? 'Full day' : $formatTimeRange($startTime, $endTime);
        $includedServices = $item['included_services'] ?? [];
        $packageSchedule = $item['package_schedule'] ?? [];
        $packageScheduleByItem = [];
        foreach ($packageSchedule as $scheduledService) {
          $packageScheduleByItem[(int)($scheduledService['package_item_id'] ?? 0)] = $scheduledService;
        }
        $addonPackageName = trim((string)($item['addon_package_name'] ?? ''));
        $venueRoomName = trim((string)($item['venue_room_name'] ?? ''));
        $venueName = trim((string)($item['venue_name'] ?? ''));
        $minLeadDays = max(0, (int)($item['min_lead_days'] ?? 0));
        $earliestBookingDate = date('Y-m-d', strtotime('+' . $minLeadDays . ' days'));
        $displayName = $venueRoomName !== '' ? $name . ' · ' . $venueRoomName : $name;
        $detailUrl   = ($itemType === 'package' && !empty($item['package_slug']))
          ? URLROOT . '/customerServices/packageDetail/' . $h($item['package_slug'])
          : URLROOT . '/customerServices/detail/' . (int)($item['item_id'] ?? 0) . ($selectedDate ? '?date=' . $h($selectedDate) : '');
      ?>
      <article class="gp-item" data-index="<?= $i ?>" aria-label="<?= $h($displayName) ?>">

        <!-- Thumbnail -->
        <a class="gp-item-thumb" href="<?= $h($detailUrl) ?>" tabindex="-1" aria-hidden="true">
          <?php if ($img): ?>
            <img src="<?= $h($img) ?>" alt="<?= $h($name) ?>" loading="lazy">
          <?php else: ?>
            <div class="gp-item-thumb-placeholder">
              <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
            </div>
          <?php endif; ?>
          <div class="gp-item-cat-ribbon"><?= $h($category) ?></div>
        </a>

        <!-- Body -->
        <div class="gp-item-body">
          <h2 class="gp-item-name">
            <a href="<?= $h($detailUrl) ?>"><?= $h($displayName) ?></a>
          </h2>
          <div class="gp-item-supplier">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            <?= $h($supplier) ?>
          </div>
          <?php if ($addonPackageName !== ''): ?>
          <div class="gp-item-meta">
            <span class="gp-item-pill">
              <i data-lucide="plus-circle" style="width:11px;height:11px"></i>
              Add-on for <?= $h($addonPackageName) ?>
            </span>
          </div>
          <?php endif; ?>
          <?php if ($venueRoomName !== ''): ?>
          <div class="gp-item-meta">
            <span class="gp-item-pill">
              <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/><path d="M9 9h1"/><path d="M9 13h1"/><path d="M9 17h1"/></svg>
              <?= $h($venueRoomName . ($venueName !== '' ? ' · ' . $venueName : '')) ?>
            </span>
          </div>
          <?php endif; ?>
          <?php
            // Attire rental info
            $rentalType = $item['rental_type'] ?? null;
            $attireItemName = $item['attire_item_name'] ?? null;
            $rentalDays = $item['rental_days'] ?? null;
            $borrowDate = $item['borrow_date'] ?? null;
          ?>
          <?php if ($rentalType && $attireItemName): ?>
          <div class="gp-item-meta">
            <span class="gp-item-pill">
              <i data-lucide="hanger" style="width:11px;height:11px"></i>
              <?= $h($attireItemName) ?>
            </span>
            <span class="gp-item-pill">
              <?php if ($rentalType === 'borrow'): ?>
                <i data-lucide="refresh-cw" style="width:11px;height:11px"></i>
                Borrow <?= (int)$rentalDays ?> day<?= (int)$rentalDays !== 1 ? 's' : '' ?>
              <?php else: ?>
                <i data-lucide="shopping-bag" style="width:11px;height:11px"></i>
                Buy
              <?php endif; ?>
            </span>
          </div>
          <?php endif; ?>
          <?php if ($selectedDate || $timeRange): ?>
          <div class="gp-item-date-line">
            <?php if ($selectedDate): ?>
            <span>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
              <?= $h($formatDate($selectedDate)) ?>
            </span>
            <?php endif; ?>
            <?php if ($selectedDate && $timeRange): ?>
            <span class="gp-item-meta-separator" aria-hidden="true">|</span>
            <?php endif; ?>
            <?php if ($timeRange): ?>
            <span>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
              <?= $h($timeRange) ?>
            </span>
            <?php endif; ?>
          </div>
          <?php endif; ?>

          <div class="gp-item-price"><?= $money($price) ?></div>

          <?php if ($itemType === 'package'): ?>
          <section class="gp-package-includes" aria-label="Services included in <?= $h($name) ?>">
            <div class="gp-package-includes-head">
              <div class="gp-package-includes-title">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v18"/><path d="M3 12h18"/><path d="m5 5 14 14"/><path d="m19 5-14 14"/></svg>
                Services included
              </div>
              <span class="gp-package-includes-count"><?= count($includedServices) ?> service<?= count($includedServices) === 1 ? '' : 's' ?></span>
            </div>
            <?php if (!empty($includedServices)): ?>
            <div class="gp-package-service-list">
              <?php foreach ($includedServices as $tlIdx => $service): ?>
                <?php
                  $schedule = $packageScheduleByItem[(int)($service['package_item_id'] ?? 0)] ?? [];
                  $serviceHall = trim((string)($service['venue_room_name'] ?? ''));
                  $serviceVenue = trim((string)($service['venue_name'] ?? ''));
                  $serviceDate = trim((string)($schedule['event_date'] ?? ''));
                  $serviceTime = $formatTimeRange($schedule['start_time'] ?? '', $schedule['end_time'] ?? '');
                  $isScheduled = $serviceDate !== '' || $serviceTime !== '';
                  $serviceMeta = array_filter([
                      $service['category_name'] ?? 'Service',
                      $service['supplier_name'] ?? 'Golden Promise',
                      $serviceHall !== '' ? $serviceHall . ($serviceVenue !== '' ? ' · ' . $serviceVenue : '') : '',
                  ]);
                ?>
                <div class="gp-package-service" data-scheduled="<?= $isScheduled ? 'true' : 'false' ?>">
                  <div class="gp-package-tl-dot <?= !$isScheduled ? 'is-pending' : '' ?>" aria-hidden="true"></div>
                  <div class="gp-package-tl-content">
                    <div class="gp-package-service-name"><?= $h($service['service_name'] ?? 'Service') ?></div>
                    <div class="gp-package-service-meta"><?= $h(implode(' · ', $serviceMeta)) ?></div>
                    <?php if ($isScheduled): ?>
                      <div class="gp-package-service-time">
                        <i data-lucide="clock" style="width:9px;height:9px"></i>
                        <?= $h(trim(($serviceDate !== '' ? $formatDate($serviceDate) : '') . ($serviceDate !== '' && $serviceTime !== '' ? ' · ' : '') . $serviceTime)) ?>
                      </div>
                    <?php else: ?>
                      <div class="gp-package-service-time is-pending">
                        <i data-lucide="calendar-clock" style="width:9px;height:9px"></i>
                        Scheduled after event date
                      </div>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endforeach; ?>
	            </div>
	            <div class="gp-package-schedule-note">
	              <i data-lucide="calendar-days" style="width:13px;height:13px;flex-shrink:0;margin-top:1px"></i>
	              <span>
	                <?php if (!empty($packageSchedule)): ?>
	                  Times are based on the selected event date and may be confirmed during booking.
	                <?php else: ?>
	                  Choose the event date on the booking page to build the full service timeline.
	                <?php endif; ?>
	              </span>
	            </div>
	            <?php else: ?>
	              <div class="gp-edit-slot-note">Package service details are not available.</div>
	            <?php endif; ?>
          </section>
          <?php endif; ?>

        </div>

        <?php if ($itemType === 'service'):
          $isFullday = ($item['booking_type'] ?? 'fullday') !== 'slot';
        ?>
        <form class="gp-edit-form" method="POST" action="<?= URLROOT ?>/cart/update" data-booking-type="<?= $isFullday ? 'fullday' : 'slot' ?>">
          <input type="hidden" name="cart_item_id" value="<?= $itemId ?>">
          <button class="gp-edit-toggle" type="button" aria-expanded="false" aria-label="Edit <?= $h($name) ?> details" title="Edit details">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
          </button>
          <div class="gp-edit-fields">
            <div class="gp-edit-field">
              <label for="cart-date-<?= $itemId ?>">Date</label>
              <span class="gp-edit-date-control" role="button" tabindex="0" aria-label="Open date calendar">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <span class="gp-edit-date-display" data-date-display><?= $h($selectedDate !== '' ? $formatDate($selectedDate) : 'Choose date') ?></span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                <input class="gp-calendar-input" id="cart-date-<?= $itemId ?>" type="date" name="date" value="<?= $h($selectedDate) ?>"
                       min="<?= $h($earliestBookingDate) ?>"
                       data-service-id="<?= (int)($item['item_id'] ?? 0) ?>"
                       data-current-start="<?= $h($startTime) ?>"
                       data-current-end="<?= $h($endTime) ?>">
              </span>
              <?php if ($minLeadDays > 0): ?>
                <span class="gp-edit-slot-note">Earliest: <?= $h(date('M j, Y', strtotime($earliestBookingDate))) ?></span>
              <?php endif; ?>
            </div>
            <?php if ($isFullday): ?>
            <div class="gp-edit-field is-wide">
              <div class="gp-edit-slot-note" style="margin-top:4px;">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-2px;margin-right:4px;opacity:.6"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                Full-day booking — time is managed automatically
              </div>
              <input type="hidden" name="start_time" value="<?= $h($startTime ?: '00:00') ?>">
              <input type="hidden" name="end_time" value="<?= $h($endTime ?: '23:59') ?>" data-end-time-field>
              <input type="hidden" name="slot_id" value="" data-slot-id-field>
            </div>
            <?php else: ?>
            <div class="gp-edit-field is-wide">
              <label>Available times</label>
              <div class="gp-edit-slots" data-slot-container>
                <span class="gp-edit-slot-note">Open edit details to load available times.</span>
              </div>
              <input type="hidden" name="end_time" value="<?= $h(substr((string)$endTime, 0, 5)) ?>" data-end-time-field>
              <input type="hidden" name="slot_id" value="<?= $h($item['slot_id'] ?? '') ?>" data-slot-id-field>
            </div>
            <?php endif; ?>
            <div class="gp-edit-field">
              <label for="cart-price-<?= $itemId ?>">Price</label>
              <input id="cart-price-<?= $itemId ?>" type="number" name="price" min="0" step="0.01" value="<?= $h($price) ?>" readonly>
            </div>
            <button class="gp-save-btn" type="submit">Save</button>
          </div>
        </form>
        <?php endif; ?>

        <!-- Right: price + remove -->
        <div class="gp-item-right">
          <form class="gp-remove-form" method="POST" action="<?= URLROOT ?>/cart/remove">
            <input type="hidden" name="cart_item_id" value="<?= $itemId ?>">
            <button class="gp-remove-btn" type="submit" aria-label="Remove <?= $h($name) ?> from cart" title="Remove">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
            </button>
          </form>
        </div>

      </article>
      <?php endforeach; ?>
    </div><!-- /gp-items -->

    <!-- RIGHT: sticky order summary -->
    <aside class="gp-sidebar" aria-label="Order summary">
      <div class="gp-summary-card">

        <!-- Summary head -->
        <div class="gp-summary-head">
          <div class="gp-summary-label">Order summary</div>
          <div class="gp-summary-title">Your selection</div>
          <div class="gp-summary-subtitle"><?= $cartCount ?> service<?= $cartCount === 1 ? '' : 's' ?> selected</div>
        </div>

        <!-- Line items -->
        <div class="gp-summary-body">
          <div class="gp-line-items">
            <?php foreach ($items as $item):
              $linePrice = (float)($item['cart_price'] ?? $item['price_min'] ?? $item['price_max'] ?? 0);
              $lineName  = $item['service_name'] ?? 'Service';
              $lineDate  = trim((string)($item['selected_date'] ?? ''));
              $lineIsFullday = ($item['item_type'] ?? '') === 'service' && ($item['booking_type'] ?? 'fullday') !== 'slot';
              $lineTime  = $lineIsFullday ? 'Full day' : $formatTimeRange($item['start_time'] ?? '', $item['end_time'] ?? '');
              $lineHall = trim((string)($item['venue_room_name'] ?? ''));
              $lineImage = trim((string)($item['thumbnail_url'] ?? ''));
              $lineMetaParts = [];
              if ($lineHall !== '') $lineMetaParts[] = $lineHall;
              if ($lineHall !== '') $lineName .= ' · ' . $lineHall;
              if ($lineDate !== '') $lineMetaParts[] = $formatDate($lineDate);
              if ($lineTime !== '') $lineMetaParts[] = $lineTime;
              $lineDetail = !empty($lineMetaParts) ? implode(' · ', $lineMetaParts) : 'Date to be confirmed';
            ?>
            <div class="gp-summary-service">
              <div class="gp-summary-service-icon" aria-hidden="true">
                <?php if ($lineImage !== ''): ?>
                  <img src="<?= $h($lineImage) ?>" alt="">
                <?php else: ?>
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M4 18h16"/><path d="M6 18a6 6 0 0 1 12 0"/><path d="M12 8v-2"/><path d="M9 6h6"/><path d="M4 21h16"/></svg>
                <?php endif; ?>
              </div>
              <div class="gp-summary-service-main">
                <div class="gp-summary-service-name" title="<?= $h($lineName) ?>"><?= $h($lineName) ?></div>
                <div class="gp-summary-service-date">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                  <?= $h($lineDetail) ?>
                </div>
              </div>
              <div class="gp-summary-service-price"><?= $money($linePrice) ?></div>
            </div>
            <?php endforeach; ?>

            <div class="gp-summary-divider"></div>

            <div class="gp-line">
              <span class="gp-line-name">Subtotal</span>
              <span class="gp-line-dots" aria-hidden="true"></span>
              <span class="gp-line-val"><?= $money($total) ?></span>
            </div>
            <div class="gp-line">
              <span class="gp-line-name">Deposit (<?= BOOKING_DEPOSIT_PERCENT ?>%)</span>
              <span class="gp-line-dots" aria-hidden="true"></span>
              <span class="gp-line-val"><?= $money($total * BOOKING_DEPOSIT_PERCENT / 100) ?></span>
            </div>
          </div>

          <!-- Total -->
          <div class="gp-total-row">
            <span class="gp-total-label">Estimated total</span>
            <span class="gp-total-amount"><?= $money($total) ?></span>
          </div>
        </div>

        <!-- CTAs -->
        <div class="gp-summary-footer">
          <a class="gp-btn-book" href="<?= URLROOT ?>/booking/create">
            Proceed to Booking
          </a>
          <a class="gp-btn-more" href="<?= URLROOT ?>/customerServices/service">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add more services
          </a>
        </div>

      </div><!-- /gp-summary-card -->
    </aside>

  </div><!-- /gp-layout -->
  <?php endif; ?>

</main>

<div class="gp-calendar-popover" id="cartCalendarPopover" hidden></div>

<div class="gp-remove-modal" id="removeConfirmModal" aria-hidden="true">
  <div class="gp-remove-dialog" role="dialog" aria-modal="true" aria-labelledby="removeConfirmTitle" aria-describedby="removeConfirmText">
    <div class="gp-remove-dialog-icon" aria-hidden="true">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
    </div>
    <h2 id="removeConfirmTitle">Remove item?</h2>
    <p id="removeConfirmText">Are you sure you want to delete this service from your cart?</p>
    <div class="gp-remove-dialog-actions">
      <button class="gp-remove-dialog-btn cancel" type="button" data-remove-cancel>Cancel</button>
      <button class="gp-remove-dialog-btn confirm" type="button" data-remove-confirm>Remove</button>
    </div>
  </div>
</div>

<footer class="gp-footer">
  <span>&copy; <?= date('Y') ?> Golden Promise</span>
  <span>Your curated wedding service collection</span>
</footer>

<script>
(function () {
  /* Staggered item reveal on load */
  const items = document.querySelectorAll('.gp-item');
  if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const el = entry.target;
          const delay = parseInt(el.dataset.index || 0) * 90;
          setTimeout(() => el.classList.add('visible'), delay);
          observer.unobserve(el);
        }
      });
    }, { threshold: 0.08 });
    items.forEach(el => observer.observe(el));
  } else {
    items.forEach(el => el.classList.add('visible'));
  }

  /* Inline cart item editing */
  function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, (char) => ({
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#039;'
    }[char]));
  }

  function normalizeClock(value) {
    return String(value || '').slice(0, 5);
  }

  const cartCalendar = document.getElementById('cartCalendarPopover');
  let cartCalendarInput = null;
  let cartCalendarMonth = null;

  function formatDateValue(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return year + '-' + month + '-' + day;
  }

  function parseDateValue(value) {
    if (!value) return null;
    const parts = String(value).split('-').map(Number);
    if (parts.length !== 3 || parts.some(Number.isNaN)) return null;
    return new Date(parts[0], parts[1] - 1, parts[2]);
  }

  function formatEditDateLabel(value) {
    if (!value) return 'Choose date';
    const date = parseDateValue(value);
    if (!date) return value;
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
  }

  function updateEditDateDisplay(input) {
    const display = input.closest('.gp-edit-date-control')?.querySelector('[data-date-display]');
    if (display) display.textContent = formatEditDateLabel(input.value);
  }

  function positionCartCalendar(anchor) {
    if (!cartCalendar || !anchor) return;
    const rect = anchor.getBoundingClientRect();
    const width = Math.min(250, window.innerWidth - 32);
    const left = Math.max(16, Math.min(rect.left, window.innerWidth - width - 16));
    cartCalendar.style.width = width + 'px';
    cartCalendar.style.left = left + 'px';
    cartCalendar.style.top = (rect.bottom + 10) + 'px';
  }

  function renderCartCalendar() {
    if (!cartCalendar || !cartCalendarInput || !cartCalendarMonth) return;
    const monthStart = new Date(cartCalendarMonth.getFullYear(), cartCalendarMonth.getMonth(), 1);
    const selectedValue = cartCalendarInput.value;
    const todayValue = formatDateValue(new Date());
    const minValue = cartCalendarInput.min || '';
    const maxValue = cartCalendarInput.max || '';
    const daysInMonth = new Date(monthStart.getFullYear(), monthStart.getMonth() + 1, 0).getDate();
    const leadingBlanks = monthStart.getDay();
    const monthTitle = monthStart.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
    const dayNames = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'];

    let html = '<div class="gp-calendar-head">' +
      '<button class="gp-calendar-nav" type="button" data-cart-cal-prev aria-label="Previous month"><svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg></button>' +
      '<span>' + monthTitle + '</span>' +
      '<button class="gp-calendar-nav" type="button" data-cart-cal-next aria-label="Next month"><svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg></button>' +
      '</div><div class="gp-calendar-grid">';

    dayNames.forEach((day) => { html += '<div class="gp-calendar-day-name">' + day + '</div>'; });
    for (let i = 0; i < leadingBlanks; i++) html += '<span></span>';
    for (let day = 1; day <= daysInMonth; day++) {
      const value = formatDateValue(new Date(monthStart.getFullYear(), monthStart.getMonth(), day));
      const disabled = (minValue && value < minValue) || (maxValue && value > maxValue);
      const classes = ['gp-calendar-day'];
      if (value === selectedValue) classes.push('is-selected');
      if (value === todayValue) classes.push('is-today');
      if (disabled) classes.push('is-disabled');
      html += '<button class="' + classes.join(' ') + '" type="button" data-cart-date="' + value + '"' + (disabled ? ' disabled' : '') + '>' + day + '</button>';
    }
    html += '</div>';
    cartCalendar.innerHTML = html;
  }

  function openCartCalendar(input) {
    if (!cartCalendar || !input) return;
    cartCalendarInput = input;
    cartCalendarMonth = parseDateValue(input.value) || parseDateValue(input.min) || new Date();
    renderCartCalendar();
    cartCalendar.hidden = false;
    positionCartCalendar(input.closest('.gp-edit-date-control') || input);
  }

  async function loadCartSlots(form) {
    const dateInput = form.querySelector('input[name="date"]');
    const container = form.querySelector('[data-slot-container]');
    const saveBtn = form.querySelector('.gp-save-btn');
    const endTimeField = form.querySelector('[data-end-time-field]');
    const slotIdField = form.querySelector('[data-slot-id-field]');
    if (!dateInput || !container) return;

    const serviceId = parseInt(dateInput.dataset.serviceId || '0', 10);
    const date = dateInput.value;
    if (!serviceId || !date) {
      container.innerHTML = '<span class="gp-edit-slot-note">Choose a date first.</span>';
      if (saveBtn) saveBtn.disabled = true;
      return;
    }

    container.innerHTML = '<span class="gp-edit-slot-note">Loading slots...</span>';
    if (saveBtn) saveBtn.disabled = true;

    try {
      const response = await fetch('<?= URLROOT ?>/booking/getAvailableSlots', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ service_id: serviceId, date })
      });
      const data = await response.json();
      const slots = Array.isArray(data.slots) ? data.slots : [];

      if (!slots.length) {
        container.innerHTML = '<span class="gp-edit-slot-note">No available times for this date.</span>';
        if (endTimeField) endTimeField.value = '';
        if (slotIdField) slotIdField.value = '';
        return;
      }

      const currentStart = normalizeClock(dateInput.dataset.currentStart);
      const currentEnd = normalizeClock(dateInput.dataset.currentEnd);
      const selectedIndex = Math.max(0, slots.findIndex((slot) =>
        normalizeClock(slot.start_time) === currentStart && normalizeClock(slot.end_time) === currentEnd
      ));

      container.innerHTML = slots.map((slot, index) => `
        <label class="gp-edit-slot-option">
          <input type="radio"
                 name="start_time"
                 value="${escapeHtml(slot.start_time)}"
                 data-end-time="${escapeHtml(slot.end_time)}"
                 data-slot-id="${escapeHtml(slot.slot_id || '')}"
                 ${index === selectedIndex ? 'checked' : ''}>
          <span>${escapeHtml(slot.display)}</span>
        </label>
      `).join('');

      const syncSlotFields = (radio) => {
        if (!radio) return;
        if (endTimeField) endTimeField.value = radio.dataset.endTime || '';
        if (slotIdField) slotIdField.value = radio.dataset.slotId || '';
      };

      container.querySelectorAll('input[name="start_time"]').forEach((radio) => {
        radio.addEventListener('change', () => {
          if (radio.checked) syncSlotFields(radio);
        });
      });
      syncSlotFields(container.querySelector('input[name="start_time"]:checked'));
      if (saveBtn) saveBtn.disabled = false;
    } catch (error) {
      container.innerHTML = '<span class="gp-edit-slot-note">Could not load slots. Please try again.</span>';
    }
  }

  document.querySelectorAll('.gp-edit-toggle').forEach((button) => {
    button.addEventListener('click', () => {
      const form = button.closest('.gp-edit-form');
      if (!form) return;
      const isOpen = form.classList.toggle('is-open');
      button.setAttribute('aria-expanded', String(isOpen));
      if (isOpen && form.dataset.bookingType !== 'fullday') loadCartSlots(form);
    });
  });

  document.querySelectorAll('.gp-edit-form input[name="date"]').forEach((input) => {
    updateEditDateDisplay(input);
    input.addEventListener('click', (event) => {
      event.preventDefault();
      event.stopPropagation();
      openCartCalendar(input);
    });
    input.addEventListener('focus', () => openCartCalendar(input));
    input.addEventListener('change', () => {
      updateEditDateDisplay(input);
      input.dataset.currentStart = '';
      input.dataset.currentEnd = '';
      const form = input.closest('.gp-edit-form');
      if (form?.classList.contains('is-open') && form.dataset.bookingType !== 'fullday') loadCartSlots(form);
    });
  });

  document.querySelectorAll('.gp-edit-date-control').forEach((control) => {
    const input = control.querySelector('.gp-calendar-input');
    if (!input) return;
    control.addEventListener('click', (event) => {
      event.preventDefault();
      event.stopPropagation();
      openCartCalendar(input);
    });
    control.addEventListener('keydown', (event) => {
      if (event.key !== 'Enter' && event.key !== ' ') return;
      event.preventDefault();
      event.stopPropagation();
      openCartCalendar(input);
    });
  });

  cartCalendar?.addEventListener('click', (event) => {
    event.stopPropagation();
    const prev = event.target.closest('[data-cart-cal-prev]');
    const next = event.target.closest('[data-cart-cal-next]');
    const day = event.target.closest('[data-cart-date]');
    if (prev) {
      cartCalendarMonth = new Date(cartCalendarMonth.getFullYear(), cartCalendarMonth.getMonth() - 1, 1);
      renderCartCalendar();
      return;
    }
    if (next) {
      cartCalendarMonth = new Date(cartCalendarMonth.getFullYear(), cartCalendarMonth.getMonth() + 1, 1);
      renderCartCalendar();
      return;
    }
    if (day && cartCalendarInput) {
      cartCalendarInput.value = day.dataset.cartDate;
      updateEditDateDisplay(cartCalendarInput);
      cartCalendar.hidden = true;
      cartCalendarInput.dispatchEvent(new Event('change', { bubbles: true }));
    }
  });

  cartCalendar?.addEventListener('mousedown', (event) => {
    event.preventDefault();
    event.stopPropagation();
  });

  /* Remove item confirmation */
  const removeModal = document.getElementById('removeConfirmModal');
  const removeConfirmBtn = removeModal?.querySelector('[data-remove-confirm]');
  const removeCancelBtn = removeModal?.querySelector('[data-remove-cancel]');
  let pendingRemoveForm = null;
  let allowRemoveSubmit = false;

  function openRemoveModal(form) {
    pendingRemoveForm = form;
    removeModal?.classList.add('is-open');
    removeModal?.setAttribute('aria-hidden', 'false');
    removeCancelBtn?.focus();
  }

  function closeRemoveModal() {
    removeModal?.classList.remove('is-open');
    removeModal?.setAttribute('aria-hidden', 'true');
    pendingRemoveForm = null;
    allowRemoveSubmit = false;
  }

  document.querySelectorAll('.gp-remove-form').forEach((form) => {
    form.addEventListener('submit', (event) => {
      if (allowRemoveSubmit) return;
      event.preventDefault();
      openRemoveModal(form);
    });
  });

  removeConfirmBtn?.addEventListener('click', () => {
    if (!pendingRemoveForm) return;
    allowRemoveSubmit = true;
    pendingRemoveForm.submit();
  });

  removeCancelBtn?.addEventListener('click', closeRemoveModal);

  removeModal?.addEventListener('click', (event) => {
    if (event.target === removeModal) closeRemoveModal();
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && removeModal?.classList.contains('is-open')) {
      closeRemoveModal();
    }
    if (event.key === 'Escape' && cartCalendar && !cartCalendar.hidden) {
      cartCalendar.hidden = true;
    }
  });

  /* Shimmer effect on Book button hover — already handled by CSS ::before */

  /* Header scroll tint */
  const header = document.querySelector('.gp-header');
  if (header) {
    window.addEventListener('scroll', () => {
      header.style.background = window.scrollY > 10
        ? 'rgba(235,220,204,0.94)'
        : 'rgba(242,228,212,0.82)';
    }, { passive: true });
  }

  /* Profile dropdown toggle */
  document.addEventListener('click', (e) => {
    if (cartCalendar && !cartCalendar.hidden) {
      if (!e.target.closest('.gp-calendar-popover') && !e.target.closest('.gp-edit-date-control')) {
        cartCalendar.hidden = true;
      }
    }

    const btn = e.target.closest('.gp-profile-btn');
    if (btn) {
      const expanded = btn.getAttribute('aria-expanded') === 'true';
      document.querySelectorAll('.gp-profile-btn').forEach(b => b.setAttribute('aria-expanded', 'false'));
      btn.setAttribute('aria-expanded', String(!expanded));
      return;
    }
    document.querySelectorAll('.gp-profile-btn').forEach(b => b.setAttribute('aria-expanded', 'false'));
  });

  window.addEventListener('resize', () => {
    if (cartCalendar && !cartCalendar.hidden && cartCalendarInput) {
      positionCartCalendar(cartCalendarInput.closest('.gp-edit-date-control') || cartCalendarInput);
    }
  });
  window.addEventListener('scroll', () => {
    if (cartCalendar && !cartCalendar.hidden) cartCalendar.hidden = true;
  }, { passive: true });
})();
</script>
<?php require APPROOT . '/views/layouts/customerFooter.php'; ?>
<?php include APPROOT . '/views/partials/cookie-consent.php'; ?>
</body>
</html>
