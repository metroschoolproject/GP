<?php
$venueService = $venueService ?? null;
$venueLocation = $venueService['location'] ?? '';

$items = $items ?? [];
$total = (float)($total ?? 0);
$cartCount = (int)($cartCount ?? 0);
$user = $user ?? ['name' => '', 'email' => '', 'phone' => ''];
$depositPercent = (int)($depositPercent ?? 10);

$isLoggedIn = !empty($_SESSION['session_uid']);
$authNavUrl = $isLoggedIn ? URLROOT . '/users/logout' : URLROOT . '/users/auth';
$authNavLabel = $isLoggedIn ? 'Logout' : 'Sign in';

$money = fn($v) => 'RM ' . number_format((float)$v, 0);
$formatDate = function ($value) {
    $timestamp = strtotime((string)$value);
    return $timestamp ? date('M j, Y', $timestamp) : '';
};
$formatTimeRange = function ($from, $to) {
    $from = trim((string)$from);
    $to = trim((string)$to);
    if ($from === '' && $to === '') {
        return '';
    }
    $format = function ($value) {
        $timestamp = strtotime($value);
        return $timestamp ? date('g:i A', $timestamp) : $value;
    };
    if ($from === '' || $from === $to) {
        return $format($to ?: $from);
    }
    return $format($from) . ' - ' . $format($to);
};
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

$defaultDate = '';
$defaultStartTime = '';
$defaultEndTime = '';
foreach ($items as $defaultItem) {
    if ($defaultDate === '' && !empty($defaultItem['selected_date'])) {
        $defaultDate = (string)$defaultItem['selected_date'];
    }
    if ($defaultStartTime === '' && !empty($defaultItem['start_time'])) {
        $defaultStartTime = (string)$defaultItem['start_time'];
    }
    if ($defaultEndTime === '' && !empty($defaultItem['end_time'])) {
        $defaultEndTime = (string)$defaultItem['end_time'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Confirm Booking — Golden Promise</title>
<?php $publicCssVersion = file_exists(APPROOT . '/../public/css/app.css') ? filemtime(APPROOT . '/../public/css/app.css') : time(); ?>
<link rel="stylesheet" href="<?= URLROOT ?>/public/css/app.css?v=<?= $publicCssVersion ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,500;1,600&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
/* ─── Design tokens ──────────────────────── */
:root {
  /* Palette: ivory parchment + dusty mauve + antique gold */
  --ivory:       #faf7f2;
  --parchment:   #f3ece0;
  --parchment2:  #e8ddd0;
  --linen:       #ede5d8;

  --mauve:       #8c5f72;
  --mauve-dk:    #6b4457;
  --mauve-lt:    #b8899e;
  --mauve-xs:    rgba(140,95,114,0.08);

  --gold:        #c4973b;
  --gold-lt:     #e4c07a;
  --gold-xs:     rgba(196,151,59,0.12);

  --sage:        #7a9c82;
  --sage-xs:     rgba(122,156,130,0.10);

  --ink:         #2c1f28;
  --ink2:        #5a4350;
  --mist:        #9c8893;
  --rule:        rgba(140,95,114,0.14);
  --rule-md:     rgba(140,95,114,0.26);
  --danger:      #a84040;

  /* Type */
  --serif:       'Cormorant Garamond', 'Georgia', serif;
  --sans:        'Jost', system-ui, sans-serif;

  /* Geometry */
  --r-xs: 4px;
  --r-sm: 8px;
  --r-md: 14px;
  --r-lg: 20px;
  --r-xl: 28px;
  --r-pill: 999px;

  /* Layout */
  --pad-x: clamp(20px, 5vw, 72px);
  --ease: cubic-bezier(0.22, 1, 0.36, 1);
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html { scroll-behavior: smooth; }

body {
  background-color: var(--parchment);
  color: var(--ink);
  font-family: var(--sans);
  font-size: 14px;
  line-height: 1.65;
  -webkit-font-smoothing: antialiased;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  overflow-x: hidden;
}

a { color: inherit; text-decoration: none; }
img { display: block; max-width: 100%; }
button { font-family: var(--sans); cursor: pointer; }
textarea { font-family: var(--sans); }

/* ─── Botanical background motif ─────────── */
/* Repeating SVG fern/leaf silhouette stamped into the parchment */
.gp-bg {
  position: fixed;
  inset: 0;
  z-index: 0;
  pointer-events: none;
  overflow: hidden;
  opacity: 0.038;
}
.gp-bg svg {
  position: absolute;
  width: 520px;
  height: auto;
}
.gp-bg-tl { top: -60px; left: -80px; transform: rotate(-20deg); }
.gp-bg-br { bottom: -80px; right: -60px; transform: rotate(160deg); }
.gp-bg-mid { top: 38%; left: 55%; transform: rotate(30deg) scale(0.7); }

/* ─── Thin decorative top bar ─────────────── */
.gp-crown {
  position: fixed;
  top: 0; left: 0; right: 0;
  z-index: 200;
  height: 3px;
  background: linear-gradient(90deg, var(--mauve-lt) 0%, var(--gold) 50%, var(--mauve-lt) 100%);
}

/* ─── Header ─────────────────────────────── */
.gp-header {
  position: sticky;
  top: 0;
  z-index: 100;
  display: grid;
  grid-template-columns: auto 1fr auto;
  align-items: center;
  gap: 24px;
  padding: 0 var(--pad-x);
  height: 68px;
  border-bottom: 1px solid var(--rule-md);
  background: rgba(243,236,224,0.88);
  backdrop-filter: blur(20px) saturate(1.5);
  -webkit-backdrop-filter: blur(20px) saturate(1.5);
  transition: background 0.3s;
}

.gp-brand {
  display: flex;
  align-items: center;
  gap: 11px;
  font-family: var(--serif);
  font-size: 22px;
  font-weight: 600;
  color: var(--ink);
  white-space: nowrap;
  letter-spacing: 0.01em;
}
.gp-brand-monogram {
  display: grid;
  place-items: center;
  width: 36px; height: 36px;
  border-radius: 50%;
  border: 1.5px solid var(--mauve-lt);
  background: var(--mauve);
  color: #faf7f2;
  font-family: var(--serif);
  font-size: 16px;
  font-weight: 600;
  font-style: italic;
}

.gp-header-nav {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 2px;
}
.gp-header-nav a {
  padding: 7px 16px;
  border-radius: var(--r-pill);
  font-size: 13px;
  font-weight: 500;
  letter-spacing: 0.02em;
  color: var(--ink2);
  transition: all 0.2s;
}
.gp-header-nav a:hover {
  color: var(--mauve);
  background: var(--mauve-xs);
}

.gp-header-actions { display: flex; align-items: center; gap: 10px; }

.gp-cart-badge {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  padding: 7px 13px 7px 10px;
  border-radius: var(--r-pill);
  border: 1px solid var(--rule-md);
  background: rgba(250,247,242,0.7);
  color: var(--mauve);
  font-size: 13px;
  font-weight: 600;
  backdrop-filter: blur(8px);
  transition: all 0.2s;
}
.gp-cart-badge:hover { border-color: var(--mauve); background: var(--mauve-xs); }
.gp-cart-count {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 20px; height: 20px;
  padding: 0 5px;
  border-radius: var(--r-pill);
  background: var(--mauve);
  color: #fff;
  font-size: 10px;
  font-weight: 700;
}

.gp-cta-header {
  display: inline-flex;
  align-items: center;
  height: 36px;
  padding: 0 20px;
  border-radius: var(--r-pill);
  border: 1px solid var(--mauve-lt);
  background: var(--mauve);
  color: #faf7f2;
  font-size: 13px;
  font-weight: 500;
  letter-spacing: 0.03em;
  transition: all 0.2s var(--ease);
}
.gp-cta-header:hover { background: var(--mauve-dk); border-color: var(--mauve-dk); transform: translateY(-1px); }

/* Profile dropdown */
.gp-profile-dropdown { position: relative; }
.gp-profile-btn {
  display: flex; align-items: center; gap: 8px;
  padding: 4px 12px 4px 4px;
  border-radius: var(--r-pill);
  border: 1px solid var(--rule-md);
  background: rgba(250,247,242,0.7);
  cursor: pointer;
  transition: all 0.2s;
  color: var(--mauve);
  font-family: var(--sans);
  font-size: 13px;
  font-weight: 600;
}
.gp-profile-btn:hover { border-color: var(--mauve); background: var(--mauve-xs); }
.gp-profile-avatar {
  display: grid; place-items: center;
  width: 32px; height: 32px;
  border-radius: 50%;
  background: var(--mauve);
  color: #faf7f2;
  font-size: 13px;
  font-weight: 600;
}
.gp-profile-name { white-space: nowrap; max-width: 100px; overflow: hidden; text-overflow: ellipsis; }
.gp-profile-chevron { opacity: 0.6; transition: transform 0.2s; }
.gp-profile-btn[aria-expanded="true"] .gp-profile-chevron { transform: rotate(180deg); }
.gp-profile-menu {
  position: absolute; top: calc(100% + 8px); right: 0;
  min-width: 180px;
  padding: 6px;
  border-radius: var(--r-md);
  border: 1px solid var(--rule-md);
  background: var(--ivory);
  box-shadow: 0 8px 32px rgba(44,31,40,0.10);
  opacity: 0; visibility: hidden; transform: translateY(-4px);
  transition: all 0.15s var(--ease);
}
.gp-profile-btn[aria-expanded="true"] + .gp-profile-menu,
.gp-profile-menu.show { opacity: 1; visibility: visible; transform: translateY(0); }
.gp-profile-menu-item {
  display: flex; align-items: center; gap: 10px;
  padding: 10px 12px;
  border-radius: var(--r-sm);
  font-size: 13px;
  font-weight: 500;
  color: var(--ink);
  transition: all 0.15s;
}
.gp-profile-menu-item:hover { background: var(--mauve-xs); }
.gp-profile-menu-item--danger { color: var(--danger); }
.gp-profile-menu-item--danger:hover { background: rgba(168,64,64,0.07); }

/* ─── Page shell ─────────────────────────── */
.gp-page {
  position: relative;
  z-index: 1;
  flex: 1;
  padding: 56px var(--pad-x) 96px;
  max-width: 1180px;
  margin: 0 auto;
  width: 100%;
}

/* ─── Page header ─────────────────────────── */
.gp-page-head {
  margin-bottom: 48px;
  text-align: center;
  opacity: 0;
  animation: fadeUp 0.8s var(--ease) 0.05s forwards;
}
.gp-head-ornament {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 18px;
  margin-bottom: 20px;
}
.gp-head-ornament-line {
  flex: 1;
  max-width: 140px;
  height: 1px;
  background: linear-gradient(90deg, transparent, var(--gold-lt), transparent);
}
.gp-head-ornament-diamond {
  width: 8px; height: 8px;
  background: var(--gold);
  transform: rotate(45deg);
}
.gp-head-ornament-dot {
  width: 4px; height: 4px;
  background: var(--mauve-lt);
  border-radius: 50%;
}
.gp-page-eyebrow {
  font-size: 11px;
  font-weight: 500;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  color: var(--gold);
  margin-bottom: 10px;
}
.gp-page-title {
  font-family: var(--serif);
  font-size: clamp(42px, 6vw, 68px);
  font-weight: 500;
  color: var(--ink);
  line-height: 0.93;
  letter-spacing: -0.01em;
}
.gp-page-title em {
  font-style: italic;
  color: var(--mauve);
}
.gp-page-subtitle {
  margin-top: 16px;
  font-size: 14px;
  color: var(--mist);
  font-weight: 400;
}

/* ─── Two-column layout ──────────────────── */
.gp-layout {
  display: grid;
  grid-template-columns: 1fr 348px;
  gap: 28px;
  align-items: start;
}

/* ─── Section label ──────────────────────── */
.gp-section-label {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 16px;
  font-size: 10px;
  font-weight: 600;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  color: var(--mist);
}
.gp-section-label::after {
  content: '';
  flex: 1;
  height: 1px;
  background: var(--rule);
}

/* ─── Cards (shared) ─────────────────────── */
.gp-card {
  background: var(--ivory);
  border: 1px solid var(--rule-md);
  border-radius: var(--r-lg);
  overflow: hidden;
  opacity: 0;
  transform: translateY(20px);
  transition: box-shadow 0.35s var(--ease), border-color 0.25s, opacity 0.5s var(--ease), transform 0.5s var(--ease);
}
.gp-card.visible { opacity: 1; transform: translateY(0); }
.gp-card:hover { box-shadow: 0 8px 32px rgba(44,31,40,0.07); border-color: var(--rule-md); }

/* ─── Customer info card ─────────────────── */
.gp-customer-card .gp-card-band {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 18px;
  padding: 20px 22px;
  border-bottom: 1px solid var(--rule);
  background: linear-gradient(135deg, rgba(140,95,114,0.06), rgba(196,151,59,0.04));
}
.gp-card-band-left { display: flex; align-items: center; gap: 14px; }
.gp-card-icon {
  display: grid;
  place-items: center;
  width: 44px; height: 44px;
  border-radius: 50%;
  border: 1px solid var(--rule-md);
  background: var(--ivory);
  color: var(--mauve);
  font-family: var(--serif);
  font-size: 20px;
  font-weight: 500;
  font-style: italic;
  flex-shrink: 0;
}
.gp-card-eyebrow {
  font-size: 10px;
  font-weight: 600;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: var(--gold);
}
.gp-card-title {
  font-family: var(--serif);
  font-size: 22px;
  font-weight: 500;
  color: var(--ink);
  line-height: 1.05;
  margin-top: 1px;
}
.gp-card-action {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: 12px;
  font-weight: 600;
  color: var(--mauve);
  letter-spacing: 0.02em;
  white-space: nowrap;
  border-bottom: 1px solid rgba(140,95,114,0.3);
  transition: color 0.2s;
}
.gp-card-action:hover { color: var(--mauve-dk); border-color: var(--mauve-dk); }
.gp-profile-facts {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 0;
}
.gp-fact {
  padding: 16px 20px;
  border-right: 1px solid var(--rule);
}
.gp-fact:last-child { border-right: none; }
.gp-fact-label {
  display: block;
  font-size: 10px;
  font-weight: 600;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: var(--mist);
  margin-bottom: 5px;
}
.gp-fact-value {
  font-size: 14px;
  font-weight: 500;
  color: var(--ink);
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.gp-fact-value:empty::after {
  content: 'Not provided';
  color: var(--mist);
  font-style: italic;
  font-weight: 400;
}

/* ─── Default contact card ───────────────── */
.gp-defaults-card .gp-card-band {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 18px;
  padding: 20px 22px;
  border-bottom: 1px solid var(--rule);
}
.gp-card-note {
  font-size: 12px;
  color: var(--mist);
  max-width: 220px;
  text-align: right;
  line-height: 1.5;
}
.gp-defaults-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0;
}
.gp-default-field {
  padding: 14px 20px;
  border-right: 1px solid var(--rule);
  border-bottom: 1px solid var(--rule);
}
.gp-default-field:nth-child(2n) { border-right: none; }
.gp-default-field:nth-last-child(-n+2) { border-bottom: none; }
.gp-default-label {
  display: block;
  font-size: 10px;
  font-weight: 600;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: var(--mist);
  margin-bottom: 6px;
}
.gp-default-field .gp-detail-input {
  border: none;
  border-bottom: 1px solid var(--rule-md);
  border-radius: 0;
  background: transparent;
  padding: 4px 0;
  width: 100%;
  font-size: 14px;
  font-weight: 500;
  color: var(--ink);
  transition: border-color 0.2s;
}
.gp-default-field .gp-detail-input:focus {
  outline: none;
  border-color: var(--mauve);
  box-shadow: none;
}
.gp-default-field .gp-detail-stepper { display: flex; align-items: center; gap: 0; }
.gp-stepper-btn {
  width: 32px; height: 32px;
  display: grid; place-items: center;
  border: 1px solid var(--rule-md);
  background: var(--ivory);
  color: var(--ink2);
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.15s;
}
.gp-stepper-btn:hover { background: var(--mauve); color: #fff; border-color: var(--mauve); }
.gp-stepper-btn:first-child { border-radius: var(--r-xs) 0 0 var(--r-xs); }
.gp-stepper-btn:last-child { border-radius: 0 var(--r-xs) var(--r-xs) 0; }
.gp-stepper-input {
  width: 52px; height: 32px;
  text-align: center;
  border: 1px solid var(--rule-md);
  border-left: none; border-right: none;
  font-size: 14px; font-weight: 600;
  color: var(--ink);
  background: var(--parchment);
}
.gp-stepper-input::-webkit-inner-spin-button,
.gp-stepper-input::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
.gp-stepper-input[type=number] { -moz-appearance: textfield; }

/* ─── Service item cards ─────────────────── */
.gp-items { display: flex; flex-direction: column; gap: 20px; }

.gp-item-card { /* inherits .gp-card */ }

.gp-item-header {
  display: grid;
  grid-template-columns: 110px 1fr auto;
  border-bottom: 1px solid var(--rule);
}
.gp-item-thumb {
  position: relative;
  overflow: hidden;
  min-height: 110px;
  background: linear-gradient(160deg, var(--linen), var(--parchment2));
}
.gp-item-thumb img { width: 100%; height: 100%; object-fit: cover; }
.gp-item-thumb-placeholder {
  width: 100%; height: 100%;
  min-height: 110px;
  display: grid; place-items: center;
  color: var(--mist);
}

/* Delicate floral corner on thumbnail */
.gp-item-thumb::after {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(160deg, rgba(140,95,114,0.12) 0%, transparent 55%);
  pointer-events: none;
}

.gp-item-info {
  padding: 16px 18px;
  display: flex;
  flex-direction: column;
  gap: 3px;
  min-width: 0;
}
.gp-item-name {
  font-family: var(--serif);
  font-size: 20px;
  font-weight: 500;
  color: var(--ink);
  line-height: 1.1;
}
.gp-item-meta {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  color: var(--mist);
  margin-top: 2px;
}
.gp-item-meta-sep { color: var(--rule-md); }

.gp-item-price-box {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  justify-content: center;
  padding: 16px 20px 16px 12px;
  flex-shrink: 0;
}
.gp-item-price-val {
  font-family: var(--serif);
  font-size: 22px;
  font-weight: 500;
  color: var(--mauve);
  white-space: nowrap;
}

/* Tags */
.gp-tag {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 2px 8px;
  border-radius: var(--r-xs);
  font-size: 10px;
  font-weight: 600;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  white-space: nowrap;
}
.gp-tag-venue {
  background: var(--gold-xs);
  color: #7a5318;
  border: 1px solid rgba(196,151,59,0.25);
}
.gp-tag-auto {
  background: var(--sage-xs);
  color: #3a5c40;
  border: 1px solid rgba(122,156,130,0.25);
}
.gp-input-note {
  margin-top: 6px;
  font-size: 11px;
  color: var(--mist);
}
.gp-input-note strong {
  color: var(--mauve);
  font-weight: 600;
}

/* ─── Item details ───────────────────────── */
.gp-item-details {
  padding: 18px 20px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

/* Slot fieldset */
.gp-slot-fieldset {
  border: none;
  padding: 0;
}
.gp-fieldset-legend {
  font-size: 10px;
  font-weight: 600;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: var(--mist);
  margin-bottom: 10px;
  display: flex;
  align-items: center;
  gap: 10px;
}
.gp-fieldset-legend::after {
  content: '';
  flex: 1;
  height: 1px;
  background: var(--rule);
}

.gp-slot-display {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding: 12px 14px;
  border: 1px solid var(--rule-md);
  border-radius: var(--r-md);
  background: var(--parchment);
}
.gp-slot-box { display: flex; flex-direction: column; gap: 2px; }
.slot-date { font-size: 13px; font-weight: 600; color: var(--ink); }
.slot-time { font-size: 12px; color: var(--mist); }

.gp-btn-change-slot {
  padding: 6px 14px;
  border-radius: var(--r-pill);
  border: 1px solid var(--rule-md);
  background: var(--ivory);
  color: var(--mauve);
  font-size: 12px;
  font-weight: 600;
  white-space: nowrap;
  transition: all 0.2s;
}
.gp-btn-change-slot:hover { border-color: var(--mauve); background: var(--mauve-xs); }

.gp-slot-selector { display: flex; flex-direction: column; gap: 10px; margin-top: 8px; }
.gp-slot-selector.hidden { display: none; }
.gp-btn-cancel-change {
  align-self: flex-start;
  padding: 5px 13px;
  border-radius: var(--r-pill);
  border: 1px solid var(--rule-md);
  background: transparent;
  color: var(--mist);
  font-size: 12px;
  font-weight: 500;
  transition: all 0.2s;
}
.gp-btn-cancel-change:hover { border-color: var(--danger); color: var(--danger); }

.gp-slots-container {
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.gp-slot-option {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 14px;
  border: 1px solid var(--rule-md);
  border-radius: var(--r-sm);
  cursor: pointer;
  transition: all 0.2s;
  background: var(--ivory);
  font-size: 13px;
  color: var(--ink);
}
.gp-slot-option:hover { border-color: var(--mauve); background: var(--mauve-xs); }
.gp-slot-option input[type="radio"] { accent-color: var(--mauve); }
.loading { font-size: 13px; color: var(--mist); padding: 10px 0; font-style: italic; }
.error { font-size: 13px; color: var(--danger); padding: 6px 0; }

/* Form fields */
.gp-detail-label {
  display: block;
  font-size: 10px;
  font-weight: 600;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: var(--mist);
  margin-bottom: 5px;
}
.gp-detail-input, .gp-detail-textarea, .gp-detail-select {
  width: 100%;
  padding: 9px 12px;
  border: 1px solid var(--rule-md);
  border-radius: var(--r-sm);
  font-size: 13px;
  font-family: var(--sans);
  color: var(--ink);
  background: var(--ivory);
  transition: border-color 0.2s, box-shadow 0.2s;
}
.gp-detail-input:focus, .gp-detail-textarea:focus, .gp-detail-select:focus {
  outline: none;
  border-color: var(--mauve);
  box-shadow: 0 0 0 3px rgba(140,95,114,0.1);
}
.gp-detail-input.error, .gp-detail-textarea.error { border-color: var(--danger); }
.gp-detail-input.is-missing,
.gp-detail-textarea.is-missing,
.gp-stepper-input.is-missing {
  border-color: var(--danger) !important;
  box-shadow: 0 0 0 3px rgba(185,75,75,0.08) !important;
}
.gp-required-hint {
  margin-top: 8px;
  color: var(--danger);
  font-size: 12px;
  font-weight: 600;
}
.gp-booking-reminder {
  display: none;
  margin-bottom: 12px;
  padding: 10px 12px;
  border: 1px solid #fecaca;
  border-radius: var(--r-sm);
  background: #fef2f2;
  color: var(--danger);
  font-size: 12px;
  font-weight: 600;
  line-height: 1.45;
}
.gp-booking-reminder.show { display: block; }
.gp-booking-reminder ul {
  margin: 6px 0 0;
  padding-left: 18px;
}
.gp-detail-textarea { min-height: 70px; resize: vertical; }
.gp-detail-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.gp-detail-field { display: flex; flex-direction: column; gap: 4px; }
.gp-detail-field.full { grid-column: 1 / -1; }

/* Service drawer */
.gp-service-drawer {
  border-top: 1px solid var(--rule);
  padding-top: 14px;
}
.gp-service-drawer summary {
  list-style: none;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding: 9px 13px;
  border: 1px solid var(--rule-md);
  border-radius: var(--r-sm);
  background: var(--parchment);
  font-size: 12px;
  font-weight: 600;
  color: var(--ink2);
  cursor: pointer;
  letter-spacing: 0.02em;
  transition: all 0.18s;
}
.gp-service-drawer summary::-webkit-details-marker { display: none; }
.gp-service-drawer summary::after {
  content: '+';
  display: grid;
  place-items: center;
  width: 22px; height: 22px;
  border-radius: 50%;
  background: var(--ivory);
  color: var(--mauve);
  border: 1px solid var(--rule-md);
  font-size: 14px;
  line-height: 1;
  transition: transform 0.18s var(--ease);
}
.gp-service-drawer[open] summary {
  border-color: rgba(140,95,114,0.28);
  color: var(--mauve);
  background: var(--mauve-xs);
}
.gp-service-drawer[open] summary::after {
  content: '−';
  transform: rotate(180deg);
}
.gp-drawer-hint {
  font-size: 11px;
  font-weight: 400;
  color: var(--mist);
}
.gp-overrides-fieldset {
  margin-top: 12px;
  padding: 16px;
  border: 1px solid var(--rule);
  border-radius: var(--r-sm);
  background: rgba(255,255,255,0.4);
}

/* venue-filled input style */
input[data-venue-filled="true"],
input[data-suggested-filled="true"] {
  border-color: rgba(122,156,130,0.35);
  background-color: rgba(122,156,130,0.04);
}
[data-is-venue="true"] .gp-item-header {
  border-left: 3px solid rgba(196,151,59,0.45);
}

/* ─── Sidebar ────────────────────────────── */
.gp-sidebar {
  position: sticky;
  top: 84px;
  opacity: 0;
  animation: fadeUp 0.8s var(--ease) 0.3s forwards;
}

.gp-summary-card {
  background: var(--ivory);
  border: 1px solid var(--rule-md);
  border-radius: var(--r-xl);
  overflow: hidden;
  box-shadow: 0 20px 60px rgba(44,31,40,0.09);
}

/* Petal accent bar */
.gp-summary-card::before {
  content: '';
  display: block;
  height: 3px;
  background: linear-gradient(90deg, var(--mauve) 0%, var(--gold) 50%, var(--mauve-lt) 100%);
}

.gp-summary-head {
  padding: 24px 24px 18px;
  border-bottom: 1px solid var(--rule);
  background: linear-gradient(160deg, rgba(140,95,114,0.04), transparent 70%);
}
.gp-summary-eyebrow {
  font-size: 10px;
  font-weight: 600;
  letter-spacing: 0.16em;
  text-transform: uppercase;
  color: var(--gold);
  margin-bottom: 4px;
}
.gp-summary-title {
  font-family: var(--serif);
  font-size: 24px;
  font-weight: 500;
  color: var(--ink);
}
.gp-summary-subtitle { font-size: 12px; color: var(--mist); margin-top: 2px; }

/* Decorative vine divider inside summary */
.gp-vine-divider {
  display: flex;
  align-items: center;
  gap: 10px;
  margin: 6px 0;
  color: var(--rule-md);
  font-size: 11px;
}
.gp-vine-line { flex: 1; height: 1px; background: var(--rule); }
.gp-vine-diamond { width: 5px; height: 5px; background: var(--gold-lt); transform: rotate(45deg); }

.gp-summary-body { padding: 20px 24px; }
.gp-line-items { display: flex; flex-direction: column; gap: 10px; margin-bottom: 16px; }
.gp-line {
  display: flex;
  justify-content: space-between;
  align-items: baseline;
  gap: 10px;
}
.gp-line-name { font-size: 13px; color: var(--ink2); font-weight: 400; flex: 1; min-width: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.gp-line-dots { flex: 1; border-bottom: 1px dashed rgba(140,95,114,0.2); margin: 0 4px 3px; }
.gp-line-val { font-size: 13px; color: var(--ink); font-weight: 500; white-space: nowrap; }

.gp-total-row {
  display: flex;
  justify-content: space-between;
  align-items: baseline;
  padding: 16px 0 0;
  border-top: 1px solid var(--rule-md);
  margin-top: 4px;
}
.gp-total-label { font-size: 13px; font-weight: 500; color: var(--ink2); }
.gp-total-amount {
  font-family: var(--serif);
  font-size: 34px;
  font-weight: 500;
  color: var(--mauve);
  line-height: 1;
}

.gp-deposit-breakdown {
  margin-top: 14px;
  padding: 14px;
  border-radius: var(--r-sm);
  background: var(--parchment);
  border: 1px solid var(--rule);
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.gp-deposit-line {
  display: flex;
  justify-content: space-between;
  align-items: baseline;
  font-size: 12px;
  color: var(--mist);
}
.gp-deposit-line strong { color: var(--ink); font-weight: 600; font-size: 13px; }
.gp-deposit-highlight {
  font-size: 11px;
  color: var(--sage);
  font-style: italic;
  margin-top: 4px;
}

/* Buttons */
.gp-summary-footer {
  padding: 0 24px 22px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.gp-btn-primary {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  height: 52px;
  border-radius: var(--r-md);
  border: none;
  background: var(--mauve);
  color: #faf7f2;
  font-family: var(--sans);
  font-size: 14px;
  font-weight: 600;
  letter-spacing: 0.04em;
  box-shadow: 0 10px 28px rgba(140,95,114,0.28);
  transition: all 0.3s var(--ease);
  position: relative;
  overflow: hidden;
}
.gp-btn-primary::before {
  content: '';
  position: absolute;
  top: 0; left: -100%;
  width: 100%; height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.10), transparent);
  transition: left 0.5s var(--ease);
}
.gp-btn-primary:hover { background: var(--mauve-dk); transform: translateY(-2px); box-shadow: 0 16px 38px rgba(140,95,114,0.32); }
.gp-btn-primary:hover::before { left: 100%; }
.gp-btn-primary:active { transform: translateY(0); }
.gp-btn-primary:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

.gp-btn-secondary {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  height: 42px;
  border-radius: var(--r-md);
  border: 1px solid var(--rule-md);
  background: transparent;
  color: var(--ink2);
  font-family: var(--sans);
  font-size: 13px;
  font-weight: 500;
  transition: all 0.2s;
}
.gp-btn-secondary:hover { border-color: var(--mauve); color: var(--mauve); background: var(--mauve-xs); }

/* Trust section */
.gp-trust {
  display: flex;
  flex-direction: column;
  gap: 8px;
  padding: 16px 24px;
  border-top: 1px solid var(--rule);
}
.gp-trust-item {
  display: flex;
  align-items: flex-start;
  gap: 9px;
  font-size: 11px;
  color: var(--mist);
  line-height: 1.5;
}
.gp-trust-icon { width: 14px; height: 14px; flex-shrink: 0; color: var(--gold); margin-top: 1px; }

/* Spinner */
.gp-spinner {
  display: inline-block;
  width: 16px; height: 16px;
  border: 2px solid rgba(255,255,255,0.3);
  border-top-color: #fff;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}

/* Toast */
.gp-toast {
  position: fixed;
  top: 20px; right: 20px;
  z-index: 999;
  padding: 14px 20px;
  border-radius: var(--r-md);
  box-shadow: 0 12px 40px rgba(0,0,0,0.10);
  font-size: 13px;
  font-weight: 500;
  max-width: 380px;
  opacity: 0;
  transform: translateY(-10px);
  transition: all 0.35s var(--ease);
  pointer-events: none;
}
.gp-toast.show { opacity: 1; transform: translateY(0); pointer-events: auto; }
.gp-toast.error { background: #fef2f2; border: 1px solid #fecaca; color: var(--danger); }
.gp-toast.success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; }

/* Footer */
.gp-footer {
  position: relative; z-index: 1;
  padding: 24px var(--pad-x);
  border-top: 1px solid var(--rule);
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  font-size: 12px;
  color: var(--mist);
}
.gp-footer-ornament {
  display: flex;
  align-items: center;
  gap: 8px;
  font-family: var(--serif);
  font-size: 14px;
  font-style: italic;
  color: var(--mist);
}

/* Lead time helper text and warnings */
.gp-input-note {
  font-size: 12px;
  margin-top: 6px;
  color: var(--mist);
  font-style: italic;
}

.gp-lead-time-warning {
  padding: 8px 12px;
  background-color: rgba(196, 151, 59, 0.1);
  border-left: 3px solid var(--gold);
  margin-top: 8px;
  font-size: 13px;
  color: var(--ink2);
}

input[type="date"]:invalid {
  border-color: var(--danger);
  background-color: rgba(168, 64, 64, 0.05);
}

/* Keyframes */
@keyframes fadeUp { from { opacity: 0; transform: translateY(22px); } to { opacity: 1; transform: translateY(0); } }
@keyframes spin { to { transform: rotate(360deg); } }

/* ─── Responsive ─────────────────────────── */
@media (max-width: 940px) {
  .gp-layout { grid-template-columns: 1fr; }
  .gp-sidebar { position: static; }
  .gp-detail-row { grid-template-columns: 1fr; }
}
@media (max-width: 640px) {
  .gp-item-header { grid-template-columns: 80px 1fr; }
  .gp-item-price-box { display: none; }
  .gp-header-nav { display: none; }
  .gp-profile-facts { grid-template-columns: 1fr; }
  .gp-fact { border-right: none; border-bottom: 1px solid var(--rule); }
  .gp-defaults-grid { grid-template-columns: 1fr; }
  .gp-default-field { border-right: none; }
  :root { --pad-x: 16px; }
}
@media (prefers-reduced-motion: reduce) {
  .gp-card, .gp-page-head, .gp-sidebar { animation: none; opacity: 1; transform: none; }
}
</style>
</head>
<body>

<!-- Thin crown bar -->
<div class="gp-crown" aria-hidden="true"></div>

<!-- Botanical background -->
<div class="gp-bg" aria-hidden="true">
  <svg class="gp-bg-tl" viewBox="0 0 400 500" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path d="M200 480 C200 480 80 380 60 260 C40 140 120 60 200 20 C280 60 360 140 340 260 C320 380 200 480 200 480Z" stroke="#6b4457" stroke-width="1.5" fill="none"/>
    <path d="M200 20 L200 480" stroke="#6b4457" stroke-width="1"/>
    <path d="M200 120 C200 120 130 140 100 200" stroke="#6b4457" stroke-width="1"/>
    <path d="M200 160 C200 160 270 185 295 250" stroke="#6b4457" stroke-width="1"/>
    <path d="M200 230 C200 230 135 255 115 310" stroke="#6b4457" stroke-width="1"/>
    <path d="M200 270 C200 270 265 295 280 355" stroke="#6b4457" stroke-width="1"/>
    <path d="M200 340 C200 340 145 365 140 420" stroke="#6b4457" stroke-width="1"/>
    <ellipse cx="100" cy="205" rx="40" ry="22" transform="rotate(-30 100 205)" stroke="#6b4457" stroke-width="1" fill="none"/>
    <ellipse cx="295" cy="252" rx="40" ry="22" transform="rotate(25 295 252)" stroke="#6b4457" stroke-width="1" fill="none"/>
    <ellipse cx="115" cy="315" rx="35" ry="20" transform="rotate(-25 115 315)" stroke="#6b4457" stroke-width="1" fill="none"/>
    <ellipse cx="280" cy="358" rx="35" ry="20" transform="rotate(20 280 358)" stroke="#6b4457" stroke-width="1" fill="none"/>
    <ellipse cx="140" cy="423" rx="30" ry="18" transform="rotate(-15 140 423)" stroke="#6b4457" stroke-width="1" fill="none"/>
  </svg>
  <svg class="gp-bg-br" viewBox="0 0 400 500" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path d="M200 480 C200 480 80 380 60 260 C40 140 120 60 200 20 C280 60 360 140 340 260 C320 380 200 480 200 480Z" stroke="#c4973b" stroke-width="1.5" fill="none"/>
    <path d="M200 20 L200 480" stroke="#c4973b" stroke-width="1"/>
    <path d="M200 120 C200 120 130 140 100 200" stroke="#c4973b" stroke-width="1"/>
    <path d="M200 160 C200 160 270 185 295 250" stroke="#c4973b" stroke-width="1"/>
    <path d="M200 230 C200 230 135 255 115 310" stroke="#c4973b" stroke-width="1"/>
    <path d="M200 270 C200 270 265 295 280 355" stroke="#c4973b" stroke-width="1"/>
    <ellipse cx="100" cy="205" rx="40" ry="22" transform="rotate(-30 100 205)" stroke="#c4973b" stroke-width="1" fill="none"/>
    <ellipse cx="295" cy="252" rx="40" ry="22" transform="rotate(25 295 252)" stroke="#c4973b" stroke-width="1" fill="none"/>
  </svg>
</div>

<!-- ─── Header ─────────────────────────── -->
<header class="gp-header">
  <a class="gp-brand" href="<?= URLROOT ?>/main/index">
    <span class="gp-brand-monogram">G</span>
    <span>Golden Promise</span>
  </a>
  <nav class="gp-header-nav" aria-label="Main navigation">
    <a href="<?= URLROOT ?>/main/index">Home</a>
    <a href="<?= URLROOT ?>/customerServices/service">Services</a>
    <a href="<?= URLROOT ?>/customerServices/packages">Packages</a>
  </nav>
  <div class="gp-header-actions">
    <a class="gp-cart-badge" href="<?= URLROOT ?>/cart" aria-label="Cart (<?= $cartCount ?> items)">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
      <?php if ($cartCount > 0): ?><span class="gp-cart-count"><?= $cartCount ?></span><?php endif; ?>
    </a>
    <?php if ($isLoggedIn): ?>
    <?php require APPROOT . '/views/dashboardLayout/customerNotification.php'; ?>
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

<!-- ─── Main ──────────────────────────────── -->
<main class="gp-page">

  <!-- Page heading -->
  <div class="gp-page-head">
    <div class="gp-head-ornament">
      <div class="gp-head-ornament-line"></div>
      <div class="gp-head-ornament-dot"></div>
      <div class="gp-head-ornament-diamond"></div>
      <div class="gp-head-ornament-dot"></div>
      <div class="gp-head-ornament-line"></div>
    </div>
    <div class="gp-page-eyebrow">Almost There</div>
    <h1 class="gp-page-title">Confirm Your <em>Booking</em></h1>
    <p class="gp-page-subtitle">Review your selections and add any details your suppliers need.</p>
  </div>

  <form id="booking-form" method="POST" action="<?= URLROOT ?>/booking/createPost">
    <div class="gp-layout">

      <!-- LEFT: item cards -->
      <div class="gp-items" id="gp-items">

        <!-- Customer info card (read-only display) -->
        <div class="gp-section-label">Your details</div>
        <section class="gp-card gp-customer-card" data-index="-1">
          <div class="gp-card-band">
            <div class="gp-card-band-left">
              <div class="gp-card-icon">♡</div>
              <div>
                <div class="gp-card-eyebrow">Booking under</div>
                <h2 class="gp-card-title" id="customer-info-title"><?= $h($user['name'] ?? 'You') ?></h2>
              </div>
            </div>
            <a class="gp-card-action" href="<?= URLROOT ?>/users/profile" target="_blank" rel="noopener">
              Edit profile
              <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
            </a>
          </div>
          <div class="gp-profile-facts">
            <div class="gp-fact">
              <span class="gp-fact-label">Full name</span>
              <div class="gp-fact-value"><?= $h($user['name'] ?? '') ?></div>
            </div>
            <div class="gp-fact">
              <span class="gp-fact-label">Email</span>
              <div class="gp-fact-value"><?= $h($user['email'] ?? '') ?></div>
            </div>
            <div class="gp-fact">
              <span class="gp-fact-label">Phone</span>
              <div class="gp-fact-value"><?= $h($user['phone'] ?? '') ?></div>
            </div>
          </div>
        </section>

        <!-- Per-service cards -->
        <?php if (!empty($items)): ?>
        <div class="gp-section-label" style="margin-top:8px;">Your services</div>
        <?php endif; ?>

        <?php foreach ($items as $i => $item):
          $hasSlot = !empty($item['selected_date']);
          $slotDate = $item['selected_date'] ?? '';
          $slotStart = $item['start_time'] ?? '';
          $slotEnd = $item['end_time'] ?? '';
          $formatSlotDate = $slotDate ? date('l, M j, Y', strtotime($slotDate)) : '';
          $formatSlotTime = ($slotStart && $slotEnd)
            ? date('g:i A', strtotime($slotStart)) . ' – ' . date('g:i A', strtotime($slotEnd))
            : '';
          $venueRoomName = trim((string)($item['venue_room_name'] ?? ''));
          $venueName = trim((string)($item['venue_name'] ?? ''));
          $venueRoomCapacity = (int)($item['venue_room_capacity'] ?? 0);
          $minLeadDays = max(0, (int)($item['min_lead_days'] ?? 0));
          $earliestBookingDate = date('Y-m-d', strtotime('+' . $minLeadDays . ' days'));
          $earliestBookingLabel = date('M j, Y', strtotime($earliestBookingDate));
          $serviceDisplayName = $venueRoomName !== ''
            ? ($item['service_name'] ?? 'Service') . ' · ' . $venueRoomName
            : ($item['service_name'] ?? 'Service');
          $linePrice = (float)($item['cart_price'] ?? $item['price_min'] ?? $item['price_max'] ?? 0);
          $categoryText = strtolower((string)($item['category_name'] ?? ''));
          $serviceNameText = strtolower((string)($item['service_name'] ?? ''));
          $isVenue = str_contains($categoryText, 'venue')
            || str_contains($serviceNameText, 'venue')
            || $venueRoomName !== '';
	          $isGuestPriced = str_contains($categoryText, 'makeup')
            || str_contains($categoryText, 'make up')
            || str_contains($serviceNameText, 'makeup')
	            || str_contains($serviceNameText, 'make up');
	          $addonPackageName = trim((string)($item['addon_package_name'] ?? ''));
        ?>

        <?php $itemBookingType = $item['booking_type'] ?? 'fullday'; ?>
        <article class="gp-card gp-item-card" data-index="<?= $i + 1 ?>"
                 data-has-slot="<?= $hasSlot ? 'yes' : 'no' ?>"
                 data-booking-type="<?= $h($itemBookingType) ?>"
                 data-price-index="<?= $i ?>"
                 data-unit-price="<?= $h($linePrice) ?>"
                 data-guest-priced="<?= $isGuestPriced ? 'yes' : 'no' ?>"
                 data-hall-capacity="<?= $venueRoomCapacity ?>"
                 data-min-lead-days="<?= $minLeadDays ?>"
                 data-earliest-date="<?= $h($earliestBookingDate) ?>"
                 <?php if (($item['item_type'] ?? '') === 'package'): ?>data-package-id="<?= (int)($item['item_id'] ?? 0) ?>"<?php endif; ?>
                 <?php if ($isVenue): ?>data-is-venue="true"<?php endif; ?>>

          <div class="gp-item-header">
            <a class="gp-item-thumb" href="<?= URLROOT ?>/customerServices/detail/<?= (int)($item['item_id'] ?? 0) ?>"
               tabindex="-1" aria-hidden="true">
              <?php if (!empty($item['thumbnail_url'])): ?>
                <img src="<?= $h($item['thumbnail_url']) ?>" alt="<?= $h($item['service_name'] ?? 'Service') ?>" loading="lazy">
              <?php else: ?>
                <div class="gp-item-thumb-placeholder">
                  <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                </div>
              <?php endif; ?>
            </a>

            <div class="gp-item-info">
	              <h2 class="gp-item-name">
	                <?= $h($serviceDisplayName) ?>
	                <?php if ($isVenue): ?><span class="gp-tag gp-tag-venue" style="margin-left:8px;vertical-align:2px;">Venue</span><?php endif; ?>
	                <?php if ($addonPackageName !== ''): ?><span class="gp-tag" style="margin-left:8px;vertical-align:2px;">Add-on</span><?php endif; ?>
	              </h2>
              <div class="gp-item-meta">
                <span><?= $h($item['category_name'] ?? 'Service') ?></span>
                <span class="gp-item-meta-sep">·</span>
                <span><?= $h($item['supplier_name'] ?? 'Golden Promise') ?></span>
	                <?php if ($venueRoomName !== ''): ?>
                  <span class="gp-item-meta-sep">·</span>
                  <span><?= $h($venueRoomName . ($venueName !== '' ? ' · ' . $venueName : '')) ?></span>
	                <?php endif; ?>
	                <?php if ($addonPackageName !== ''): ?>
	                  <span class="gp-item-meta-sep">·</span>
	                  <span>Add-on for <?= $h($addonPackageName) ?></span>
	                <?php endif; ?>
              </div>
            </div>

            <div class="gp-item-price-box">
              <div class="gp-item-price-val" data-item-price="<?= $i ?>"><?= $money($linePrice) ?></div>
            </div>
          </div>

          <!-- Details -->
          <div class="gp-item-details">

            <!-- Slot section -->
            <fieldset class="gp-slot-fieldset">
              <legend class="gp-fieldset-legend">Date &amp; time</legend>

              <?php if ($hasSlot): ?>
                <!-- Has existing slot -->
                <input type="hidden" name="item_start_time[<?= $i ?>]" value="<?= $h($slotStart) ?>">
                <input type="hidden" name="item_end_time[<?= $i ?>]" class="end-time-hidden-<?= $i ?>" value="<?= $h($slotEnd) ?>">
                <div class="gp-slot-display">
                  <div class="gp-slot-box">
                    <div class="slot-date">
                      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-2px;margin-right:5px;opacity:.6"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg><?= $formatSlotDate ?>
                    </div>
                    <div class="slot-time">
                      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-2px;margin-right:5px;opacity:.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg><?= $formatSlotTime ?>
                    </div>
                  </div>
                  <button type="button" class="gp-btn-change-slot"
                          data-index="<?= $i ?>" aria-label="Change slot for <?= $h($item['service_name'] ?? 'service') ?>">
                    ↻ Change
                  </button>
                </div>

                <!-- Hidden fields for current slot (preserved when not changed) -->
                <input type="hidden" name="item_date[<?= $i ?>]" value="<?= $h($slotDate) ?>"
                       data-min-lead-days="<?= (int)$minLeadDays ?>"
                       data-earliest-date="<?= $h($earliestBookingDate) ?>">
                <input type="hidden" name="item_start_time[<?= $i ?>]" value="<?= $h($slotStart) ?>">
                <input type="hidden" name="item_end_time[<?= $i ?>]" value="<?= $h($slotEnd) ?>">

                <!-- Hidden slot selector -->
                <div class="gp-slot-selector hidden" id="slot-selector-<?= $i ?>">
                  <?php
                    $minLeadDays = (int)($item['min_lead_days'] ?? 0);
                    $today = new DateTimeImmutable('today');
                    $minDate = $today->add(new DateInterval('P' . $minLeadDays . 'D'));
                    $minDateStr = $minDate->format('Y-m-d');
                    $minDateDisplay = $minDate->format('M j, Y');
                  ?>
                  <label class="gp-detail-label" for="slot-date-<?= $i ?>">New date</label>
                  <input class="gp-detail-input" type="date" id="slot-date-<?= $i ?>"
                         name="item_date[<?= $i ?>]" value="<?= $h($slotDate) ?>"
                         min="<?= $minDateStr ?>"
                         data-service-id="<?= (int)($item['item_id'] ?? 0) ?>"
                         data-min-lead-days="<?= $minLeadDays ?>"
                         data-index="<?= $i ?>">
                  <?php if ($minLeadDays > 0): ?>
                    <div class="gp-input-note">Requires <?= $minLeadDays ?> day<?= $minLeadDays === 1 ? '' : 's' ?> advance notice (earliest: <?= $minDateDisplay ?>)</div>
                  <?php endif; ?>

                  <label class="gp-detail-label">Available slots</label>
                  <div class="gp-slots-container" id="slots-<?= $i ?>">
                    <p class="loading">Loading slots…</p>
                  </div>

                  <button type="button" class="gp-btn-cancel-change" data-index="<?= $i ?>">
                    ✕ Keep original slot
                  </button>
                </div>

              <?php else: ?>
                <!-- No slot yet -->
                <?php
                  $minLeadDays = (int)($item['min_lead_days'] ?? 0);
                  $today = new DateTimeImmutable('today');
                  $minDate = $today->add(new DateInterval('P' . $minLeadDays . 'D'));
                  $minDateStr = $minDate->format('Y-m-d');
                  $minDateDisplay = $minDate->format('M j, Y');
                  $isFulldayItem = ($itemBookingType === 'fullday');
                ?>
                <label class="gp-detail-label" for="slot-date-<?= $i ?>">Select date</label>
                <input class="gp-detail-input" type="date" id="slot-date-<?= $i ?>"
                       name="item_date[<?= $i ?>]"
                       min="<?= $minDateStr ?>"
                       <?php if (!$isFulldayItem): ?>
                       data-service-id="<?= (int)($item['item_id'] ?? 0) ?>"
                       <?php endif; ?>
                       data-min-lead-days="<?= $minLeadDays ?>"
                       data-index="<?= $i ?>" required>
                <?php if ($minLeadDays > 0): ?>
                  <div class="gp-input-note">Requires <?= $minLeadDays ?> day<?= $minLeadDays === 1 ? '' : 's' ?> advance notice (earliest: <?= $minDateDisplay ?>)</div>
                <?php endif; ?>

                <?php if ($isFulldayItem):
                  // Three-layer time resolution for fullday items
                  $categoryId = (int)($item['category_id'] ?? 0);
                  $categoryTimes = defined('CATEGORY_DEFAULT_TIMES') ? (CATEGORY_DEFAULT_TIMES[$categoryId] ?? null) : null;
                  $autoStart = $item['resolved_start_time']
                      ?? ($categoryTimes['start'] ?? '00:00:00');
                  $autoEnd   = $item['resolved_end_time']
                      ?? ($categoryTimes['end'] ?? '23:59:59');
                  $showTimeHint = ($autoStart !== '00:00:00' || $autoEnd !== '23:59:59');
                  $fmtStart = $showTimeHint ? date('g:i A', strtotime($autoStart)) : '';
                  $fmtEnd   = $showTimeHint ? date('g:i A', strtotime($autoEnd))   : '';
                ?>
                  <!-- Full-day booking: time resolved via schedule → service default → category fallback -->
                  <input type="hidden" name="item_start_time[<?= $i ?>]" value="<?= $h($autoStart) ?>">
                  <input type="hidden" name="item_end_time[<?= $i ?>]" value="<?= $h($autoEnd) ?>">
                  <div class="gp-input-note" style="margin-top:8px;">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-2px;margin-right:4px;opacity:.6"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    <?php if ($showTimeHint): ?>
                      Full-day booking — estimated service window: <?= $h($fmtStart) ?> – <?= $h($fmtEnd) ?>
                    <?php else: ?>
                      Full-day booking — time is managed automatically
                    <?php endif; ?>
                  </div>
                  <?php if (($item['item_type'] ?? '') === 'package'): ?>
                    <div class="gp-package-schedule" id="package-schedule-<?= $i ?>" aria-live="polite">
                      <p class="loading">Select the event date to build the package timeline.</p>
                    </div>
                  <?php endif; ?>
                <?php else: ?>
                  <label class="gp-detail-label" style="margin-top:10px;">Available time slots</label>
                  <div class="gp-slots-container" id="slots-<?= $i ?>">
                    <p class="loading">Select a date to see available slots</p>
                  </div>
                <?php endif; ?>
              <?php endif; ?>
            </fieldset>

            <!-- Customise drawer -->
            <details class="gp-service-drawer" open>
              <summary>
                <span>Service details</span>
                <span class="gp-drawer-hint">Required contact, guests, room and notes</span>
              </summary>

              <fieldset class="gp-overrides-fieldset">
                <div class="gp-detail-row">
                  <div class="gp-detail-field">
                    <label class="gp-detail-label" for="guests-<?= $i ?>">Guests for this service</label>
                    <input class="gp-detail-input" type="number" id="guests-<?= $i ?>"
                           name="item_guests[<?= $i ?>]" min="0"
                           value="<?= $isVenue && $venueRoomCapacity > 0 ? (int)$venueRoomCapacity : '' ?>"
                           <?php if ($isVenue && $venueRoomCapacity > 0): ?>data-venue-filled="true"<?php endif; ?>
                           placeholder="Required">
                    <?php if ($isVenue && $venueRoomCapacity > 0): ?>
                      <div class="gp-input-note">Suggested from selected hall max: <strong><?= (int)$venueRoomCapacity ?></strong> guests</div>
                    <?php endif; ?>
                  </div>
                  <div class="gp-detail-field">
                    <label class="gp-detail-label" for="location-<?= $i ?>">Location / Venue room</label>
                    <input class="gp-detail-input" type="text" id="location-<?= $i ?>"
                           name="item_location[<?= $i ?>]"
                           value="<?= $h($venueLocation) ?>"
                           <?php if ($venueLocation !== ''): ?>data-venue-filled="true"<?php endif; ?>
                           placeholder="e.g. Ballroom A">
                  </div>
                </div>
                <div class="gp-detail-row" style="margin-top:12px;">
                  <div class="gp-detail-field">
                    <label class="gp-detail-label" for="contact-name-<?= $i ?>">Contact person</label>
                    <input class="gp-detail-input" type="text" id="contact-name-<?= $i ?>"
                           name="item_contact_name[<?= $i ?>]"
                           value="<?= $h($user['name'] ?? '') ?>"
                           placeholder="Contact name">
                  </div>
                  <div class="gp-detail-field">
                    <label class="gp-detail-label" for="contact-phone-<?= $i ?>">Contact phone</label>
                    <input class="gp-detail-input" type="tel" id="contact-phone-<?= $i ?>"
                           name="item_contact_phone[<?= $i ?>]"
                           value="<?= $h($user['phone'] ?? '') ?>"
                           placeholder="+60 12 345 6789">
                  </div>
                </div>
                <div class="gp-detail-row" style="margin-top:12px;">
                  <div class="gp-detail-field full">
                    <label class="gp-detail-label" for="notes-<?= $i ?>">Special requests / notes</label>
                    <textarea class="gp-detail-textarea" id="notes-<?= $i ?>"
                              name="item_notes[<?= $i ?>]"
                              placeholder="Any specific requirements for this service…"
                              data-autogrow></textarea>
                  </div>
                </div>
              </fieldset>
            </details>

          </div>
        </article>

        <?php endforeach; ?>
      </div><!-- /gp-items -->

      <!-- RIGHT: summary sidebar -->
      <aside class="gp-sidebar" aria-label="Order summary">
        <div class="gp-summary-card">

          <div class="gp-summary-head">
            <div class="gp-summary-eyebrow">Order summary</div>
            <div class="gp-summary-title">Your selection</div>
            <div class="gp-summary-subtitle"><?= $cartCount ?> service<?= $cartCount === 1 ? '' : 's' ?> selected</div>
          </div>

          <div class="gp-summary-body">
            <div class="gp-line-items">
              <?php foreach ($items as $item):
                $linePrice = (float)($item['cart_price'] ?? $item['price_min'] ?? $item['price_max'] ?? 0);
                $lineName  = $item['service_name'] ?? 'Service';
                $lineHall = trim((string)($item['venue_room_name'] ?? ''));
                if ($lineHall !== '') $lineName .= ' · ' . $lineHall;
              ?>
              <div class="gp-line">
                <span class="gp-line-name" title="<?= $h($lineName) ?>"><?= $h($lineName) ?></span>
                <span class="gp-line-dots" aria-hidden="true"></span>
                <span class="gp-line-val" data-line-price="<?= $i ?>"><?= $money($linePrice) ?></span>
              </div>
              <?php endforeach; ?>
            </div>

            <div class="gp-vine-divider">
              <span class="gp-vine-line"></span>
              <span class="gp-vine-diamond"></span>
              <span class="gp-vine-line"></span>
            </div>

            <div class="gp-total-row">
              <span class="gp-total-label">Estimated total</span>
              <span class="gp-total-amount" data-total-amount><?= $money($total) ?></span>
            </div>

            <div class="gp-deposit-breakdown">
              <div class="gp-deposit-line">
                <span>Deposit (<?= $depositPercent ?>%)</span>
                <strong data-deposit-amount><?= $money($total * $depositPercent / 100) ?></strong>
              </div>
              <div class="gp-deposit-line">
                <span>Balance due later</span>
                <strong data-balance-amount><?= $money($total - ($total * $depositPercent / 100)) ?></strong>
              </div>
              <div class="gp-deposit-highlight">Pay just the deposit today to lock in your date.</div>
            </div>
          </div>

          <div class="gp-summary-footer">
            <div class="gp-booking-reminder" id="booking-reminder" role="alert" aria-live="polite"></div>
            <button class="gp-btn-primary" type="submit" id="submit-btn">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
              Confirm &amp; Proceed
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </button>
            <a class="gp-btn-secondary" href="<?= URLROOT ?>/cart">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
              Back to Cart
            </a>
          </div>

          <div class="gp-trust" aria-label="Assurances">
            <div class="gp-trust-item">
              <svg class="gp-trust-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
              Secure payment via Stripe
            </div>
            <div class="gp-trust-item">
              <svg class="gp-trust-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
              <?= $depositPercent ?>% deposit locks your date — balance due later
            </div>
            <div class="gp-trust-item">
              <svg class="gp-trust-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="4 7 4 4 20 4 20 7"/><line x1="9" y1="20" x2="15" y2="20"/><line x1="12" y1="4" x2="12" y2="20"/></svg>
              Free cancellation within 48 hours
            </div>
          </div>

        </div>
      </aside>

    </div>
  </form>
</main>

<footer class="gp-footer">
  <span>&copy; <?= date('Y') ?> Golden Promise</span>
  <span class="gp-footer-ornament">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="opacity:.4"><path d="M12 21.593c-5.63-5.539-11-10.297-11-14.402 0-3.791 3.068-5.191 5.281-5.191 1.312 0 4.151.501 5.719 4.457 1.59-3.968 4.464-4.447 5.726-4.447 2.54 0 5.274 1.621 5.274 5.181 0 4.069-5.136 8.625-11 14.402z"/></svg>
    Your curated wedding collection
  </span>
  <span>Golden Promise Sdn. Bhd.</span>
</footer>

<div class="gp-toast" id="gp-toast" role="alert"></div>

<script>
(function () {
  /* ─── Venue auto-fill ─────────────────────── */
  (function() {
    const venueLocation = '<?= addslashes($venueLocation) ?>';
    if (venueLocation) {
      document.querySelectorAll('[name^="item_location"]').forEach(field => {
        if (!field.value || field.value.trim() === '') {
          field.value = venueLocation;
        }
        if (field.value.trim() === venueLocation) {
          field.setAttribute('data-venue-filled', 'true');
          field.style.borderColor = 'rgba(122,156,130,0.35)';
          field.style.backgroundColor = 'rgba(122,156,130,0.04)';
        }
      });
      const sharedLoc = document.getElementById('shared-location');
      if (sharedLoc && !sharedLoc.value) sharedLoc.value = venueLocation;
    }

    const syncFields = (source, selector) => {
      const value = source.value.trim();
      if (!value) return;

      document.querySelectorAll(selector).forEach(field => {
        if (field === source) return;
        const canSuggest = !field.value.trim()
          || field.dataset.venueFilled === 'true'
          || field.dataset.suggestedFilled === 'true';
        if (!canSuggest) return;

        field.value = value;
        field.dataset.suggestedFilled = 'true';
      });
    };

    document.querySelectorAll('[name^="item_location"]').forEach(field => {
      field.addEventListener('input', function () {
        this.dataset.venueFilled = 'false';
        this.dataset.suggestedFilled = 'false';
      });
      field.addEventListener('change', function () {
        syncFields(this, '[name^="item_location"]');
      });
      field.addEventListener('blur', function () {
        syncFields(this, '[name^="item_location"]');
      });
    });

    document.querySelectorAll('[name^="item_guests"]').forEach(field => {
      if (field.value.trim() && field.closest('[data-is-venue="true"]')) {
        field.dataset.venueFilled = 'true';
      }
      field.addEventListener('input', function () {
        this.dataset.venueFilled = 'false';
        this.dataset.suggestedFilled = 'false';
      });
      field.addEventListener('change', function () {
        syncFields(this, '[name^="item_guests"]');
        updateBookingPricing();
      });
      field.addEventListener('blur', function () {
        syncFields(this, '[name^="item_guests"]');
        updateBookingPricing();
      });
    });

    document.querySelectorAll('.gp-item-card[data-is-venue="true"]').forEach(card => {
      const capacity = parseInt(card.dataset.hallCapacity || '0', 10) || 0;
      const index = card.dataset.priceIndex;
      const guestInput = document.querySelector(`[name="item_guests[${index}]"]`);
      if (capacity > 0 && guestInput && !guestInput.value.trim()) {
        guestInput.value = capacity;
        guestInput.dataset.venueFilled = 'true';
      }
    });
  })();

  /* ─── Staggered card reveal ───────────────── */
  const cards = document.querySelectorAll('.gp-card');
  if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const el = entry.target;
          const delay = Math.max(0, parseInt(el.dataset.index || 0)) * 80;
          setTimeout(() => el.classList.add('visible'), delay);
          observer.unobserve(el);
        }
      });
    }, { threshold: 0.06 });
    cards.forEach(el => observer.observe(el));
  } else {
    cards.forEach(el => el.classList.add('visible'));
  }

  /* ─── Stepper buttons ─────────────────────── */
  document.querySelectorAll('[data-stepper]').forEach(btn => {
    btn.addEventListener('click', function () {
      const target = document.getElementById(this.dataset.target);
      if (!target) return;
      let val = parseInt(target.value) || 0;
      val = this.dataset.stepper === 'plus' ? Math.min(9999, val + 1) : Math.max(0, val - 1);
      target.value = val;
      target.dispatchEvent(new Event('input', { bubbles: true }));
    });
  });

  /* ─── Guest-priced service totals ─────────── */
  const depositPercent = <?= (int)$depositPercent ?>;

  function money(value) {
    return 'RM ' + Math.round(Number(value) || 0).toLocaleString('en-US');
  }

  function inputNumber(selector) {
    const input = document.querySelector(selector);
    return parseInt(input?.value || '0', 10) || 0;
  }

  function updateBookingPricing() {
    let total = 0;

    document.querySelectorAll('.gp-item-card[data-price-index]').forEach((card) => {
      const index = card.dataset.priceIndex;
      const unitPrice = Number(card.dataset.unitPrice || 0);
      const isGuestPriced = card.dataset.guestPriced === 'yes';
      const itemGuests = inputNumber(`[name="item_guests[${index}]"]`);
      const linePrice = isGuestPriced ? unitPrice * Math.max(0, itemGuests) : unitPrice;

      total += linePrice;
      document.querySelector(`[data-item-price="${index}"]`)?.replaceChildren(document.createTextNode(money(linePrice)));
      document.querySelector(`[data-line-price="${index}"]`)?.replaceChildren(document.createTextNode(money(linePrice)));
    });

    document.querySelector('[data-total-amount]')?.replaceChildren(document.createTextNode(money(total)));
    document.querySelector('[data-deposit-amount]')?.replaceChildren(document.createTextNode(money(total * depositPercent / 100)));
    document.querySelector('[data-balance-amount]')?.replaceChildren(document.createTextNode(money(total - (total * depositPercent / 100))));
  }

  document.querySelectorAll('[name^="item_guests"]').forEach(input => {
    input.addEventListener('input', updateBookingPricing);
    input.addEventListener('change', updateBookingPricing);
  });
  updateBookingPricing();

  /* ─── Auto-grow textareas ─────────────────── */
  document.querySelectorAll('[data-autogrow]').forEach(ta => {
    ta.addEventListener('input', function () {
      this.style.height = 'auto';
      this.style.height = this.scrollHeight + 'px';
    });
  });

  /* ─── Toast ───────────────────────────────── */
  const toast = document.getElementById('gp-toast');
  function showToast(msg, type) {
    toast.textContent = msg;
    toast.className = 'gp-toast ' + type + ' show';
    setTimeout(() => toast.classList.remove('show'), 5000);
  }

  /* ─── Form submission ─────────────────────── */
  const form = document.getElementById('booking-form');
  const submitBtn = document.getElementById('submit-btn');
  const bookingReminder = document.getElementById('booking-reminder');
  const originalSubmitHtml = submitBtn.innerHTML;

  function clearRequiredHighlights() {
    form.querySelectorAll('.is-missing').forEach(el => el.classList.remove('is-missing'));
    form.querySelectorAll('.gp-required-hint').forEach(el => el.remove());
    if (bookingReminder) {
      bookingReminder.classList.remove('show');
      bookingReminder.innerHTML = '';
    }
  }

  function markMissing(field) {
    if (!field) return;
    field.classList.add('is-missing');
  }

  function fieldValue(formData, name) {
    return String(formData.get(name) || '').trim();
  }

  function numberValue(formData, name) {
    return parseInt(formData.get(name) || '0', 10) || 0;
  }

  function addRequiredHint(target, message) {
    const host = target?.closest('.gp-card') || target?.parentElement;
    if (!host || host.querySelector('.gp-required-hint')) return;
    const hint = document.createElement('div');
    hint.className = 'gp-required-hint';
    hint.textContent = message;
    host.appendChild(hint);
  }

  function showBookingReminder(messages, title) {
    if (!bookingReminder || !messages.length) return;
    const items = messages.slice(0, 5).map(message => '<li>' + escapeHtml(message) + '</li>').join('');
    const extra = messages.length > 5 ? '<li>And ' + (messages.length - 5) + ' more item(s).</li>' : '';
    bookingReminder.innerHTML = '<div>' + escapeHtml(title) + '</div><ul>' + items + extra + '</ul>';
    bookingReminder.classList.add('show');
  }

  function escapeHtml(value) {
    return String(value).replace(/[&<>"']/g, char => ({
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#039;'
    }[char]));
  }

  function validateRequiredBookingInfo() {
    clearRequiredHighlights();

    if (!form.reportValidity()) {
      return false;
    }

    const formData = new FormData(form);
    const cards = Array.from(document.querySelectorAll('.gp-item-card'));
    const missingMessages = [];
    const leadTimeViolations = [];
    let firstMissingField = null;

    cards.forEach((card, index) => {
      const serviceName = card.querySelector('.gp-item-name')?.textContent.trim() || 'Service';
      const itemDate = fieldValue(formData, `item_date[${index}]`);
      const itemStart = fieldValue(formData, `item_start_time[${index}]`);
      const itemEnd = fieldValue(formData, `item_end_time[${index}]`);
      const itemPhone = fieldValue(formData, `item_contact_phone[${index}]`);
      const itemContactName = fieldValue(formData, `item_contact_name[${index}]`);
      const itemLocation = fieldValue(formData, `item_location[${index}]`);
      const itemGuests = numberValue(formData, `item_guests[${index}]`);
      const missing = [];

      const drawer = card.querySelector('.gp-service-drawer');
      const rememberMissing = (field) => {
        if (!firstMissingField && field) firstMissingField = field;
        markMissing(field);
      };

      // Validate lead time requirement
      if (itemDate) {
        const dateInput = card.querySelector(`[name="item_date[${index}]"]`);
        const minLeadDays = parseInt(dateInput?.dataset?.minLeadDays || 0);
        if (minLeadDays > 0) {
          const selectedDate = new Date(itemDate + 'T00:00:00');
          const minDate = getMinDateForService(minLeadDays);
          if (selectedDate < minDate) {
            const formattedMinDate = formatDateForDisplay(minDate);
            const days = minLeadDays === 1 ? 'day' : 'days';
            leadTimeViolations.push(serviceName + ': requires ' + minLeadDays + ' ' + days + ' advance notice (earliest: ' + formattedMinDate + ')');
            rememberMissing(dateInput);
          }
        }
      }

      const isFullday = card.dataset.bookingType === 'fullday';

      if (!itemDate) {
        missing.push('date');
        rememberMissing(card.querySelector(`[name="item_date[${index}]"]`));
      }
      if (!isFullday && !itemStart) {
        missing.push('time slot');
        rememberMissing(card.querySelector(`[name="item_start_time[${index}]"]`));
      }
      if (!isFullday && itemStart && !itemEnd) {
        missing.push('time slot end');
        rememberMissing(card.querySelector(`[name="item_start_time[${index}]"]`));
      }
      if (!itemContactName) {
        missing.push('contact name');
        rememberMissing(card.querySelector(`[name="item_contact_name[${index}]"]`));
      }
      if (!itemPhone) {
        missing.push('contact phone');
        rememberMissing(card.querySelector(`[name="item_contact_phone[${index}]"]`));
      }
      if (!itemLocation) {
        missing.push('location');
        rememberMissing(card.querySelector(`[name="item_location[${index}]"]`));
      }
      if (itemGuests <= 0) {
        missing.push('guest count');
        rememberMissing(card.querySelector(`[name="item_guests[${index}]"]`));
      }

      if (missing.length) {
        if (drawer) drawer.open = true;
        missingMessages.push(serviceName + ': ' + missing.join(', '));
        addRequiredHint(card, 'Please complete: ' + missing.join(', ') + '.');
      }
    });

    if (missingMessages.length || leadTimeViolations.length) {
      const allErrors = [...missingMessages, ...leadTimeViolations];
      let errorMsg = allErrors.length === 1
        ? allErrors[0] + '.'
        : allErrors.join('. ') + '.';

      if (leadTimeViolations.length) {
        errorMsg = 'Lead time requirement not met: ' + errorMsg;
        showBookingReminder(allErrors, 'Please choose a later date before proceeding.');
      } else {
        errorMsg = 'Please complete the required booking details before continuing. ' + errorMsg;
        showBookingReminder(allErrors, 'Please complete these details before proceeding.');
      }

      showToast(errorMsg, 'error');
      firstMissingField?.scrollIntoView({ behavior: 'smooth', block: 'center' });
      firstMissingField?.focus?.();
      return false;
    }

    return true;
  }

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    if (!validateRequiredBookingInfo()) return;

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="gp-spinner"></span> Creating booking…';
    fetch(form.action, { method: 'POST', body: new FormData(form) })
      .then(r => r.json())
      .then(data => {
        if (data.success && data.redirect) {
          window.location.href = data.redirect;
        } else {
          const error = data.error || 'Something went wrong. Please try again.';
          showBookingReminder([error], 'Please fix this before proceeding.');
          showToast(error, 'error');
          submitBtn.disabled = false;
          submitBtn.innerHTML = originalSubmitHtml;
        }
      })
      .catch(() => {
        const error = 'Something went wrong. Please try again.';
        showBookingReminder([error], 'Please fix this before proceeding.');
        showToast(error, 'error');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalSubmitHtml;
      });
  });

  /* ─── Header scroll tint ──────────────────── */
  const header = document.querySelector('.gp-header');
  if (header) {
    window.addEventListener('scroll', () => {
      header.style.background = window.scrollY > 10
        ? 'rgba(235,224,208,0.95)'
        : 'rgba(243,236,224,0.88)';
    }, { passive: true });
  }

  /* ─── Profile dropdown ────────────────────── */
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

/* ─── Slot changing ───────────────────────── */
document.querySelectorAll('.gp-btn-change-slot').forEach(btn => {
  btn.addEventListener('click', function(e) {
    e.preventDefault();
    const idx = this.dataset.index;
    document.getElementById('slot-selector-' + idx).classList.remove('hidden');
    document.getElementById('slot-date-' + idx).focus();
  });
});
document.querySelectorAll('.gp-btn-cancel-change').forEach(btn => {
  btn.addEventListener('click', function(e) {
    e.preventDefault();
    document.getElementById('slot-selector-' + this.dataset.index).classList.add('hidden');
  });
});

/* ─── Lead time helper functions ──────────── */
function getMinDateForService(minLeadDays) {
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  const minDate = new Date(today);
  minDate.setDate(minDate.getDate() + minLeadDays);
  return minDate;
}

function formatDateForInput(date) {
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
}

function formatDateForDisplay(date) {
  const options = { month: 'short', day: 'numeric', year: 'numeric' };
  return date.toLocaleDateString('en-US', options);
}

/* ─── Fetch available slots ───────────────── */
async function loadSlots(serviceId, date, index) {
  const container = document.getElementById('slots-' + index);
  if (!date) { container.innerHTML = '<p class="loading">Select a date first</p>'; return; }

  // Validate lead time requirement
  const dateInput = document.querySelector(`input[type="date"][data-index="${index}"]`);
  const minLeadDays = parseInt(dateInput?.dataset.minLeadDays || 0);
  if (minLeadDays > 0) {
    const selectedDate = new Date(date + 'T00:00:00');
    const minDate = getMinDateForService(minLeadDays);
    if (selectedDate < minDate) {
      const formattedMinDate = formatDateForDisplay(minDate);
      const days = minLeadDays === 1 ? 'day' : 'days';
      container.innerHTML = `<p class="gp-lead-time-warning">This service requires ${minLeadDays} ${days} advance notice. Earliest available: ${formattedMinDate}</p>`;
      return;
    }
  }

  container.innerHTML = '<p class="loading">Loading available slots…</p>';
  try {
    const res = await fetch('<?= URLROOT ?>/booking/getAvailableSlots', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ service_id: serviceId, date: date })
    });
    const data = await res.json();
    if (!data.success) {
      container.innerHTML = '<p class="error">' + (data.error || 'Could not load slots') + '</p>';
      return;
    }
    if (data.slots && data.slots.length > 0) {
      container.innerHTML = data.slots.map((slot, idx) => `
        <label class="gp-slot-option">
          <input type="radio" name="item_start_time[${index}]"
                 value="${slot.start_time}"
                 data-end-time="${slot.end_time}"
                 ${idx === 0 ? 'checked' : ''}>
          <input type="hidden" name="item_end_time[${index}]" class="end-time-hidden-${index}"
                 value="${slot.end_time}">
          <span>${slot.display}${slot.available > 0 ? ' · ' + slot.available + ' available' : ''}</span>
        </label>
      `).join('');
      document.querySelectorAll(`input[name="item_start_time[${index}]"]`).forEach(radio => {
        radio.addEventListener('change', function() {
          if (this.checked) {
            document.querySelector('.end-time-hidden-' + index).value = this.dataset.endTime;
          }
        });
      });
    } else {
      container.innerHTML = '<p class="error">' + (data.message || 'No available slots for this date') + '</p>';
    }
  } catch (err) {
    console.error('Slot load error:', err);
    container.innerHTML = '<p class="error">Error loading slots. Please try again.</p>';
  }
}

function packageTime(value) {
  if (!value) return '—';
  const [hourText, minute = '00'] = String(value).split(':');
  let hour = Number(hourText);
  const suffix = hour >= 12 ? 'PM' : 'AM';
  hour = hour % 12 || 12;
  return `${hour}:${minute} ${suffix}`;
}

function packageEscapeHtml(value) {
  return String(value).replace(/[&<>"']/g, char => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;'
  }[char]));
}

async function loadPackageSchedule(packageId, date, index) {
  const container = document.getElementById('package-schedule-' + index);
  if (!container) return;
  if (!date) {
    container.innerHTML = '<p class="loading">Select the event date to build the package timeline.</p>';
    return;
  }

  container.innerHTML = '<p class="loading">Building your package timeline…</p>';
  try {
    const response = await fetch('<?= URLROOT ?>/booking/getPackageSchedule', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ package_id: packageId, date })
    });
    const data = await response.json();
    if (!response.ok || !data.success) {
      container.innerHTML = '<p class="error">' + packageEscapeHtml(data.error || 'Could not build the package timeline') + '</p>';
      return;
    }

    const card = container.closest('.gp-item-card');
    const startField = card?.querySelector(`input[name="item_start_time[${index}]"]`);
    const endField = card?.querySelector(`input[name="item_end_time[${index}]"]`);
    if (startField) startField.value = data.start_time || '';
    if (endField) endField.value = data.end_time || '';

    container.innerHTML = `
      <div class="gp-input-note"><strong>Automatically managed event timeline</strong></div>
      ${data.schedule.map(item => `
        <div class="gp-slot-box" style="margin-top:6px;">
          <div class="slot-date">${packageEscapeHtml(item.service_name || 'Package service')}</div>
          <div class="slot-time">${packageTime(item.start_time)} – ${packageTime(item.end_time)} · ${packageEscapeHtml(item.supplier_name || 'Golden Promise')}</div>
        </div>
      `).join('')}
    `;
  } catch (error) {
    container.innerHTML = '<p class="error">Could not build the package timeline. Please try again.</p>';
  }
}

document.querySelectorAll('[data-service-id]').forEach(input => {
  input.addEventListener('change', function() {
    loadSlots(this.dataset.serviceId, this.value, this.dataset.index);
  });
  if (input.value) {
    loadSlots(input.dataset.serviceId, input.value, input.dataset.index);
  }
});

document.querySelectorAll('.gp-item-card[data-package-id]').forEach(card => {
  const index = card.dataset.priceIndex;
  const dateInput = card.querySelector(`input[name="item_date[${index}]"]`);
  if (!dateInput) return;
  dateInput.addEventListener('change', () => {
    loadPackageSchedule(card.dataset.packageId, dateInput.value, index);
  });
  if (dateInput.value) {
    loadPackageSchedule(card.dataset.packageId, dateInput.value, index);
  }
});
</script>
</body>
</html>
