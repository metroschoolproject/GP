<?php
$items = $items ?? [];
$total = (float)($total ?? 0);
$cartCount = (int)($cartCount ?? 0);
$includedServiceWarning = $includedServiceWarning ?? null;

$isLoggedIn = !empty($_SESSION['session_uid']);
$authNavUrl = $isLoggedIn ? URLROOT . '/users/logout' : URLROOT . '/users/auth';
$authNavLabel = $isLoggedIn ? 'Logout' : 'Sign in';

$money = fn($v) => 'RM ' . number_format((float)$v, 0);
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
<title>My Cart — Golden Promise</title>
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
  --card:        #ffffff;
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
  background: linear-gradient(135deg, rgba(255,255,255,0.18) 0%, transparent 60%);
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
  background: rgba(255,255,255,0.7);
  color: var(--plum); font-size: 13px; font-weight: 700;
  backdrop-filter: blur(8px);
  transition: all 0.22s;
}
.gp-cart-badge:hover { border-color: var(--plum); background: rgba(107,68,89,0.07); }
.gp-cart-count {
  display: inline-flex; align-items: center; justify-content: center;
  min-width: 20px; height: 20px; padding: 0 5px; border-radius: 999px;
  background: var(--plum); color: #fff;
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
  box-shadow: 0 12px 35px rgba(15,23,42,0.1);
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
  position: relative; z-index: 1;
  flex: 1;
  padding: 52px var(--pad-x) 80px;
  max-width: 1180px;
  margin: 0 auto;
  width: 100%;
}

/* ─── Page header ────────────────────────────────────── */
.gp-page-head {
  margin-bottom: 44px;
  opacity: 0;
  animation: fadeUp 0.7s var(--ease-expo) 0.1s forwards;
}

.gp-page-eyebrow {
  display: flex; align-items: center; gap: 8px;
  font-size: 11px; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase;
  color: var(--gold); margin-bottom: 10px;
}
.gp-page-eyebrow::before {
  content: ''; display: block; width: 24px; height: 1px; background: var(--gold);
}

.gp-page-title {
  font-family: var(--font-d);
  font-size: clamp(38px, 5vw, 58px); font-weight: 600;
  color: var(--text); line-height: 0.92;
  letter-spacing: -0.02em;
}
.gp-page-title em {
  font-style: italic; color: var(--plum-lt);
}

/* ─── Two-column layout ──────────────────────────────── */
.gp-layout {
  display: grid;
  grid-template-columns: 1fr 360px;
  gap: 28px;
  align-items: start;
}

/* ─── Cart items ─────────────────────────────────────── */
.gp-items { display: flex; flex-direction: column; gap: 14px; }

.gp-item {
  display: grid;
  grid-template-columns: 110px 1fr auto;
  gap: 0;
  background: var(--card);
  border: 1px solid var(--rule);
  border-radius: var(--r-lg);
  overflow: hidden;
  opacity: 0;
  transform: translateY(24px);
  transition: box-shadow 0.35s var(--ease-expo), border-color 0.25s, transform 0.35s var(--ease-expo);
}
.gp-item.visible {
  opacity: 1; transform: translateY(0);
}
.gp-item:hover {
  box-shadow: 0 24px 56px rgba(26,17,24,0.10);
  border-color: var(--rule-strong);
  transform: translateY(-2px);
}

/* Image column */
.gp-item-thumb {
  position: relative; overflow: hidden;
  width: 110px;
  background: linear-gradient(160deg, #e8d9c8, #d9c8b5);
  flex-shrink: 0;
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
  min-height: 120px;
}

/* category ribbon on image */
.gp-item-cat-ribbon {
  position: absolute; bottom: 0; left: 0; right: 0;
  padding: 20px 8px 6px;
  background: linear-gradient(to top, rgba(26,17,24,0.55) 0%, transparent 100%);
  font-size: 9px; font-weight: 700; letter-spacing: 0.12em;
  text-transform: uppercase; color: rgba(255,255,255,0.85);
}

/* Body */
.gp-item-body {
  padding: 16px 14px 16px 18px;
  display: flex; flex-direction: column; gap: 3px;
  min-width: 0;
}

.gp-item-name {
  font-family: var(--font-d);
  font-size: 19px; font-weight: 600;
  color: var(--text); line-height: 1.1;
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.gp-item-name a { transition: color 0.2s; }
.gp-item-name a:hover { color: var(--plum); }

.gp-item-supplier {
  display: flex; align-items: center; gap: 5px;
  font-size: 12px; font-weight: 500; color: var(--plum-lt);
  margin-top: 2px;
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

.gp-edit-form {
  margin-top: 10px;
  border-top: 1px solid rgba(178,143,110,0.18);
  padding-top: 10px;
}
.gp-edit-toggle {
  display: inline-flex; align-items: center; gap: 5px;
  border: 0;
  background: transparent;
  color: var(--plum);
  font-size: 11px;
  font-weight: 700;
  padding: 0;
}
.gp-edit-fields {
  display: none;
  grid-template-columns: repeat(4, minmax(96px, 1fr)) auto;
  gap: 8px;
  align-items: end;
  margin-top: 10px;
}
.gp-edit-form.is-open .gp-edit-fields { display: grid; }
.gp-edit-field {
  display: flex;
  flex-direction: column;
  gap: 4px;
  min-width: 0;
}
.gp-edit-field.is-wide {
  grid-column: span 2;
}
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
  border-radius: 10px;
  background: #fffaf7;
  color: var(--text);
  font: inherit;
  font-size: 12px;
  padding: 7px 9px;
}
.gp-edit-field input[readonly] {
  background: rgba(107,68,89,0.05);
  color: var(--text2);
}
.gp-edit-slots {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  min-height: 36px;
}
.gp-edit-slot-option {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  min-height: 32px;
  padding: 6px 9px;
  border: 1px solid rgba(107,68,89,0.14);
  border-radius: 999px;
  background: rgba(107,68,89,0.05);
  color: var(--plum);
  font-size: 11px;
  font-weight: 700;
}
.gp-edit-slot-option input { width: auto; min-height: auto; padding: 0; }
.gp-edit-slot-note {
  color: var(--muted);
  font-size: 11px;
  font-weight: 600;
}
.gp-save-btn {
  min-height: 36px;
  border: 0;
  border-radius: 10px;
  background: var(--plum);
  color: #fffaf3;
  font-size: 12px;
  font-weight: 800;
  padding: 0 14px;
  white-space: nowrap;
}
.gp-save-btn:hover { background: var(--plum-dk); }

/* Right column */
.gp-item-right {
  display: flex; flex-direction: column;
  align-items: flex-end; justify-content: space-between;
  padding: 16px 16px 16px 8px;
  gap: 12px; flex-shrink: 0;
}

.gp-item-price {
  font-family: var(--font-d);
  font-size: 22px; font-weight: 600;
  color: var(--plum);
  white-space: nowrap;
  line-height: 1;
}
.gp-item-price-label {
  font-size: 10px; font-weight: 500; color: var(--muted);
  text-align: right; margin-top: 2px;
  font-family: var(--font-b);
}

.gp-remove-btn {
  display: inline-flex; align-items: center; gap: 4px;
  padding: 5px 11px; border-radius: 999px;
  border: 1px solid rgba(185,75,75,0.2);
  background: rgba(185,75,75,0.04);
  color: var(--danger);
  font-size: 11px; font-weight: 600;
  transition: all 0.2s;
}
.gp-remove-btn:hover { background: var(--danger); color: #fff; border-color: var(--danger); }

/* ─── Sticky sidebar ─────────────────────────────────── */
.gp-sidebar {
  position: sticky; top: 84px;
  opacity: 0;
  animation: fadeUp 0.7s var(--ease-expo) 0.35s forwards;
}

.gp-summary-card {
  background: var(--card);
  border: 1px solid var(--rule);
  border-radius: var(--r-xl);
  overflow: hidden;
  box-shadow: 0 20px 60px rgba(26,17,24,0.08);
}

/* Summary header with decorative band */
.gp-summary-head {
  padding: 24px 24px 20px;
  border-bottom: 1px solid var(--rule);
  position: relative; overflow: hidden;
}
.gp-summary-head::before {
  content: '';
  position: absolute; top: 0; left: 0; right: 0; height: 3px;
  background: linear-gradient(90deg, var(--plum) 0%, var(--rose) 50%, var(--gold) 100%);
}

.gp-summary-label {
  font-size: 10px; font-weight: 700; letter-spacing: 0.12em;
  text-transform: uppercase; color: var(--muted);
  margin-bottom: 4px;
}
.gp-summary-title {
  font-family: var(--font-d);
  font-size: 22px; font-weight: 600; color: var(--text);
}
.gp-summary-subtitle {
  font-size: 12px; color: var(--muted); margin-top: 2px;
}

/* Line items */
.gp-summary-body { padding: 20px 24px; }

.gp-line-items { display: flex; flex-direction: column; gap: 12px; margin-bottom: 20px; }

.gp-line {
  display: flex; justify-content: space-between; align-items: baseline; gap: 12px;
}
.gp-line-name { font-size: 13px; color: var(--text2); font-weight: 400; flex: 1; min-width: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.gp-line-dots { flex: 1; border-bottom: 1px dashed var(--rule-strong); margin: 0 6px 3px; }
.gp-line-val { font-size: 13px; color: var(--text); font-weight: 500; white-space: nowrap; }
.gp-line-meta {
  margin-top: -8px;
  font-size: 11px;
  color: var(--muted);
  line-height: 1.45;
}

.gp-line-divider { height: 1px; background: var(--rule); margin: 4px 0; }

.gp-total-row {
  display: flex; justify-content: space-between; align-items: baseline;
  padding: 16px 0 0;
  border-top: 1px solid var(--rule-strong);
}
.gp-total-label { font-size: 13px; font-weight: 600; color: var(--text2); }
.gp-total-amount {
  font-family: var(--font-d);
  font-size: 30px; font-weight: 600; color: var(--plum);
  line-height: 1;
}

/* CTAs */
.gp-summary-footer { padding: 0 24px 24px; display: flex; flex-direction: column; gap: 10px; }

.gp-btn-book {
  display: flex; align-items: center; justify-content: center; gap: 8px;
  height: 52px; border-radius: var(--r-md); border: none;
  background: var(--plum); color: #fffaf3;
  font-size: 14px; font-weight: 700;
  letter-spacing: 0.02em;
  box-shadow: 0 10px 28px rgba(107,68,89,0.28);
  transition: all 0.3s var(--ease-expo);
  position: relative; overflow: hidden;
}
.gp-btn-book::before {
  content: '';
  position: absolute; top: 0; left: -100%; width: 100%; height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.12), transparent);
  transition: left 0.5s var(--ease-expo);
}
.gp-btn-book:hover { background: var(--plum-dk); transform: translateY(-2px); box-shadow: 0 18px 40px rgba(107,68,89,0.32); }
.gp-btn-book:hover::before { left: 100%; }
.gp-btn-book:active { transform: translateY(0); }

.gp-btn-more {
  display: flex; align-items: center; justify-content: center; gap: 6px;
  height: 42px; border-radius: var(--r-md);
  border: 1px solid var(--rule-strong);
  background: transparent; color: var(--text2);
  font-size: 13px; font-weight: 600;
  transition: all 0.22s;
}
.gp-btn-more:hover { border-color: var(--plum); color: var(--plum); background: rgba(107,68,89,0.05); }

/* Trust badges */
.gp-trust {
  display: flex; flex-direction: column; gap: 8px;
  padding: 16px 24px;
  border-top: 1px solid var(--rule);
}
.gp-trust-item {
  display: flex; align-items: center; gap: 8px;
  font-size: 11px; color: var(--muted);
}
.gp-trust-icon {
  width: 16px; height: 16px; flex-shrink: 0;
  color: var(--gold);
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

.gp-btn-browse {
  display: inline-flex; align-items: center; gap: 8px;
  height: 50px; padding: 0 32px; border-radius: 999px; border: none;
  background: var(--plum); color: #fff;
  font-size: 14px; font-weight: 700;
  box-shadow: 0 8px 24px rgba(107,68,89,0.22);
  transition: all 0.25s var(--ease-expo);
}
.gp-btn-browse:hover { background: var(--plum-dk); transform: translateY(-2px); box-shadow: 0 16px 36px rgba(107,68,89,0.28); }

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
  background: #fffaf1;
  box-shadow: 0 18px 44px rgba(184,146,74,0.12);
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
  .gp-save-btn { grid-column: 1 / -1; }
}
@media (max-width: 640px) {
  .gp-item { grid-template-columns: 88px 1fr; }
  .gp-item-right { display: none; }
  .gp-item-body { padding: 12px; }
  .gp-item-name { white-space: normal; }
  .gp-edit-fields { grid-template-columns: 1fr; }
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
<body>

<!-- Ambient orbs -->
<div class="gp-orbs" aria-hidden="true">
  <div class="gp-orb gp-orb-1"></div>
  <div class="gp-orb gp-orb-2"></div>
  <div class="gp-orb gp-orb-3"></div>
</div>

<!-- ─── Header ─────────────────────────────────────────── -->
<header class="gp-header">
  <a class="gp-brand" href="<?= URLROOT ?>/main/index">
    <span class="gp-brand-mark">G</span>
    <span>Golden Promise</span>
  </a>
  <nav class="gp-header-nav" aria-label="Main navigation">
    <a href="<?= URLROOT ?>/main/index">Home</a>
    <a href="<?= URLROOT ?>/customerServices/service">Services</a>
    <a href="<?= URLROOT ?>/customerServices/packages">Packages</a>
  </nav>
  <div class="gp-header-actions">
    <a class="gp-cart-badge" href="<?= URLROOT ?>/cart" aria-label="Cart (<?= $cartCount ?> items)">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
      <?php if ($cartCount > 0): ?><span class="gp-cart-count"><?= $cartCount ?></span><?php endif; ?>
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
    <a class="gp-cta-header" href="<?= URLROOT ?>/users/auth">Sign in</a>
    <?php endif; ?>
  </div>
</header>

<!-- ─── Main ──────────────────────────────────────────── -->
<main class="gp-page">

  <!-- Page heading -->
  <div class="gp-page-head">
    <div class="gp-page-eyebrow">Your Selection</div>
    <h1 class="gp-page-title">My <em>Cart</em></h1>
  </div>

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
        <input type="hidden" name="confirm_included_service" value="1">
        <button class="gp-included-btn primary" type="submit">Add anyway</button>
      </form>
      <form method="POST" action="<?= URLROOT ?>/cart/dismissIncludedReminder">
        <button class="gp-included-btn secondary" type="submit">Keep package only</button>
      </form>
    </div>
  </section>
  <?php endif; ?>

  <?php if (empty($items)): ?>
  <!-- ── Empty state ───────────────────────────────────── -->
  <div class="gp-empty">
    <div class="gp-empty-icon">
      <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
    </div>
    <h2>Your cart is empty</h2>
    <p>Browse our curated collection of wedding services and add the ones that make your day perfect.</p>
    <a class="gp-btn-browse" href="<?= URLROOT ?>/customerServices/service">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
      Explore services
    </a>
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
        $timeRange   = $formatTimeRange($startTime, $endTime);
        $itemType    = $item['item_type'] ?? 'service';
        $venueRoomName = trim((string)($item['venue_room_name'] ?? ''));
        $venueName = trim((string)($item['venue_name'] ?? ''));
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
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            <?= $h($supplier) ?>
          </div>
          <?php if ($venueRoomName !== ''): ?>
          <div class="gp-item-meta">
            <span class="gp-item-pill">
              <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/><path d="M9 9h1"/><path d="M9 13h1"/><path d="M9 17h1"/></svg>
              <?= $h($venueRoomName . ($venueName !== '' ? ' · ' . $venueName : '')) ?>
            </span>
          </div>
          <?php endif; ?>
          <?php if ($selectedDate || $timeRange): ?>
          <div class="gp-item-meta">
            <?php if ($selectedDate): ?>
            <span class="gp-item-pill">
              <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
              <?= $h($formatDate($selectedDate)) ?>
            </span>
            <?php endif; ?>
            <?php if ($timeRange): ?>
            <span class="gp-item-pill">
              <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
              <?= $h($timeRange) ?>
            </span>
            <?php endif; ?>
          </div>
          <?php endif; ?>

          <?php if ($itemType === 'service'): ?>
          <form class="gp-edit-form" method="POST" action="<?= URLROOT ?>/cart/update">
            <input type="hidden" name="cart_item_id" value="<?= $itemId ?>">
            <button class="gp-edit-toggle" type="button" aria-expanded="false">
              <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
              Edit details
            </button>
            <div class="gp-edit-fields">
              <div class="gp-edit-field">
                <label for="cart-date-<?= $itemId ?>">Date</label>
                <input id="cart-date-<?= $itemId ?>" type="date" name="date" value="<?= $h($selectedDate) ?>"
                       min="<?= date('Y-m-d') ?>"
                       data-service-id="<?= (int)($item['item_id'] ?? 0) ?>"
                       data-current-start="<?= $h($startTime) ?>"
                       data-current-end="<?= $h($endTime) ?>">
              </div>
              <div class="gp-edit-field is-wide">
                <label>Available time slots</label>
                <div class="gp-edit-slots" data-slot-container>
                  <span class="gp-edit-slot-note">Open edit details to load available slots.</span>
                </div>
                <input type="hidden" name="end_time" value="<?= $h(substr((string)$endTime, 0, 5)) ?>" data-end-time-field>
                <input type="hidden" name="slot_id" value="<?= $h($item['slot_id'] ?? '') ?>" data-slot-id-field>
              </div>
              <div class="gp-edit-field">
                <label for="cart-price-<?= $itemId ?>">Price</label>
                <input id="cart-price-<?= $itemId ?>" type="number" name="price" min="0" step="0.01" value="<?= $h($price) ?>" readonly>
              </div>
              <button class="gp-save-btn" type="submit">Save</button>
            </div>
          </form>
          <?php endif; ?>
        </div>

        <!-- Right: price + remove -->
        <div class="gp-item-right">
          <div>
            <div class="gp-item-price"><?= $money($price) ?></div>
            <div class="gp-item-price-label">est. price</div>
          </div>
          <form method="POST" action="<?= URLROOT ?>/cart/remove" onsubmit="return confirm('Remove this item?');">
            <input type="hidden" name="cart_item_id" value="<?= $itemId ?>">
            <button class="gp-remove-btn" type="submit" aria-label="Remove <?= $h($name) ?> from cart">
              <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
              Remove
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
              $lineTime  = $formatTimeRange($item['start_time'] ?? '', $item['end_time'] ?? '');
              $lineHall = trim((string)($item['venue_room_name'] ?? ''));
              $lineMetaParts = [];
              if ($lineHall !== '') $lineMetaParts[] = $lineHall;
              if ($lineHall !== '') $lineName .= ' · ' . $lineHall;
              if ($lineDate !== '') $lineMetaParts[] = $formatDate($lineDate);
              if ($lineTime !== '') $lineMetaParts[] = $lineTime;
            ?>
            <div class="gp-line">
              <span class="gp-line-name" title="<?= $h($lineName) ?>"><?= $h($lineName) ?></span>
              <span class="gp-line-dots" aria-hidden="true"></span>
              <span class="gp-line-val"><?= $money($linePrice) ?></span>
            </div>
            <?php if (!empty($lineMetaParts)): ?>
            <div class="gp-line-meta"><?= $h(implode(' · ', $lineMetaParts)) ?></div>
            <?php endif; ?>
            <?php endforeach; ?>

            <div class="gp-line-divider"></div>

            <div class="gp-line">
              <span class="gp-line-name">Subtotal</span>
              <span class="gp-line-dots" aria-hidden="true"></span>
              <span class="gp-line-val"><?= $money($total) ?></span>
            </div>
            <div class="gp-line">
              <span class="gp-line-name">Deposit (10%)</span>
              <span class="gp-line-dots" aria-hidden="true"></span>
              <span class="gp-line-val"><?= $money($total * 0.10) ?></span>
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
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            Proceed to Booking
          </a>
          <a class="gp-btn-more" href="<?= URLROOT ?>/customerServices/service">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add more services
          </a>
        </div>

        <!-- Trust badges -->
        <div class="gp-trust" aria-label="Assurances">
          <div class="gp-trust-item">
            <svg class="gp-trust-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            Secure booking — your data is protected
          </div>
          <div class="gp-trust-item">
            <svg class="gp-trust-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            Verified suppliers, reviewed by couples
          </div>
          <div class="gp-trust-item">
            <svg class="gp-trust-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            Free cancellation within 48 hours
          </div>
        </div>

      </div><!-- /gp-summary-card -->
    </aside>

  </div><!-- /gp-layout -->
  <?php endif; ?>

</main>

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
        container.innerHTML = '<span class="gp-edit-slot-note">No available slots for this date.</span>';
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
          <span>${escapeHtml(slot.display)}${slot.available ? ' · ' + Number(slot.available) + ' available' : ''}</span>
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
      if (isOpen) loadCartSlots(form);
    });
  });

  document.querySelectorAll('.gp-edit-form input[name="date"]').forEach((input) => {
    input.addEventListener('change', () => {
      input.dataset.currentStart = '';
      input.dataset.currentEnd = '';
      const form = input.closest('.gp-edit-form');
      if (form?.classList.contains('is-open')) loadCartSlots(form);
    });
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
    const btn = e.target.closest('.gp-profile-btn');
    if (btn) {
      const expanded = btn.getAttribute('aria-expanded') === 'true';
      document.querySelectorAll('.gp-profile-btn').forEach(b => b.setAttribute('aria-expanded', 'false'));
      btn.setAttribute('aria-expanded', String(!expanded));
      return;
    }
    document.querySelectorAll('.gp-profile-btn').forEach(b => b.setAttribute('aria-expanded', 'false'));
  });
})();
</script>
</body>
</html>
