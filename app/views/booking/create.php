<?php
$venueService = $venueService ?? null;
$venueLocation = $venueService['location'] ?? '';

$items = $items ?? [];
$total = (float)($total ?? 0);
$cartCount = (int)($cartCount ?? 0);
$user = $user ?? ['name' => '', 'email' => '', 'phone' => ''];
$depositPercent = (int)($depositPercent ?? BOOKING_DEPOSIT_PERCENT);

$isLoggedIn = !empty($_SESSION['session_uid']);
$authNavUrl = $isLoggedIn ? URLROOT . '/users/logout' : URLROOT . '/users/auth';
$authNavLabel = $isLoggedIn ? 'Logout' : 'Sign in';

$money = fn($v) => number_format((float)$v, 0) . ' MMK';
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
<?php include APPROOT . '/views/partials/ga-tracking.php'; ?>
<?php $publicCssVersion = file_exists(APPROOT . '/../public/css/app.css') ? filemtime(APPROOT . '/../public/css/app.css') : time(); ?>
<link rel="stylesheet" href="<?= URLROOT ?>/public/css/app.css?v=<?= $publicCssVersion ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,500;1,600&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
/* ─── Design tokens ──────────────────────── */
:root {
  /* Palette: ivory parchment + dusty mauve + antique gold */
  --ivory:       #fcf8f5;
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
  background-color: #f2e4d4;
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
  color: #fcf8f5;
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
  padding: 0 var(--pad-x) 96px;
  max-width: 1180px;
  margin: 0 auto;
  width: 100%;
}

/* ─── Page header ─────────────────────────── */
.gp-page-head {
  display: grid;
  place-items: center;
  min-height: 160px;
  margin-top: -68px;
  margin-bottom: 34px;
  padding: 0;
  border-radius: 0;
  background: #f4eee9;
  text-align: center;
  width: calc(100% + (var(--pad-x) * 2));
  margin-left: calc(var(--pad-x) * -1);
  margin-right: calc(var(--pad-x) * -1);
  opacity: 0;
  animation: fadeUp 0.8s var(--ease) 0.05s forwards;
}
.gp-page-eyebrow {
  order: 2;
  display: block;
  margin-top: 12px;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: var(--ink2);
  position: relative;
  top: -18px;
}
.gp-page-title {
  font-family: var(--serif);
  font-size: clamp(28px, 3vw, 36px);
  font-weight: 700;
  color: var(--ink);
  line-height: 1.05;
  letter-spacing: 0;
  margin-top: 34px;
}
.gp-page-subtitle {
  display: none;
  margin-top: 0;
  font-size: 13px;
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
.gp-default-field .gp-detail-stepper,
.gp-detail-field .gp-detail-stepper {
  display: flex;
  align-items: center;
  gap: 0;
}
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
.gp-stepper-btn:hover { background: var(--mauve); color: #fcf8f5; border-color: var(--mauve); }
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
.gp-detail-field .gp-stepper-input {
  width: min(100%, 82px);
  flex: 1 1 82px;
  border-radius: 0;
  background: var(--ivory);
}
.gp-detail-field .gp-detail-stepper .gp-stepper-btn {
  flex: 0 0 34px;
}
.gp-stepper-input::-webkit-inner-spin-button,
.gp-stepper-input::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
.gp-stepper-input[type=number] { -moz-appearance: textfield; }

/* ─── Service item cards ─────────────────── */
.gp-items {
  display: flex;
  flex-direction: column;
  gap: 10px;
  max-width: 640px;
}

.gp-item-card {
  display: grid;
  grid-template-columns: 138px minmax(0, 1fr);
  gap: 12px;
  align-items: stretch;
  position: relative;
  min-height: 128px;
  padding: 8px 14px 10px 8px;
  border: 1px solid rgba(255,255,255,0.78);
  border-radius: 16px;
  background: rgba(255,255,255,0.94);
  overflow: visible;
  box-shadow: 0 18px 44px rgba(26,17,24,0.08);
}
.gp-item-card:hover {
  box-shadow: 0 24px 60px rgba(26,17,24,0.12);
  border-color: rgba(184,146,74,0.24);
  transform: translateY(-2px);
}

.gp-item-header {
  display: contents;
}
.gp-item-thumb {
  grid-column: 1;
  position: relative;
  overflow: hidden;
  width: 100%;
  height: 100%;
  min-height: 112px;
  border-radius: 9px;
  background: linear-gradient(160deg, var(--linen), var(--parchment2));
}
.gp-item-thumb img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.55s var(--ease);
}
.gp-item-card:hover .gp-item-thumb img { transform: scale(1.06); }
.gp-item-thumb-placeholder {
  width: 100%; height: 100%;
  min-height: 112px;
  display: grid; place-items: center;
  color: var(--mist);
}
.gp-item-cat-ribbon {
  position: absolute;
  left: 50%;
  bottom: 7px;
  display: block;
  width: auto;
  max-width: calc(100% - 18px);
  padding: 4px 7px;
  border: 1px solid rgba(255,248,237,0.12);
  border-radius: 5px;
  background: rgba(26,17,24,0.22);
  backdrop-filter: blur(7px) saturate(1.04);
  -webkit-backdrop-filter: blur(7px) saturate(1.04);
  transform: translateX(-50%);
  color: #fff8ed;
  font-size: 7px;
  font-weight: 800;
  letter-spacing: 0.14em;
  line-height: 1;
  text-align: center;
  text-shadow: 0 1px 8px rgba(26,17,24,0.45);
  text-transform: uppercase;
  z-index: 1;
}

.gp-item-info {
  grid-column: 2;
  padding: 6px 44px 0 0;
  display: flex;
  flex-direction: column;
  gap: 6px;
  min-width: 0;
}
.gp-item-name {
  font-family: var(--sans);
  font-size: 14px;
  font-weight: 800;
  color: #20151f;
  line-height: 1.18;
  letter-spacing: 0;
}
.gp-item-meta {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 6px;
  color: #40353e;
  font-size: 11px;
  font-weight: 500;
  margin-top: 0;
}
.gp-item-meta svg,
.gp-item-date-line svg {
  width: 12px;
  height: 12px;
  color: #7b5b28;
  stroke-width: 1.9;
  flex-shrink: 0;
}
.gp-item-meta-sep { color: rgba(140,95,114,0.42); }
.gp-item-date-line {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 7px;
  color: #40353e;
  font-size: 11px;
  font-weight: 500;
}
.gp-item-date-line span {
  display: inline-flex;
  align-items: center;
  gap: 5px;
}

.gp-item-price-val {
  font-family: var(--sans);
  margin-top: 0;
  font-size: 13px;
  font-weight: 800;
  color: var(--mauve);
  white-space: nowrap;
  line-height: 1;
}
.gp-item-price-val.is-inline {
  margin-top: 2px;
}

/* Tags */
.gp-tag {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 2px 8px;
  border-radius: 999px;
  font-size: 9px;
  font-weight: 800;
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
  grid-column: 1 / -1;
  margin-top: 4px;
  padding: 14px 8px 4px;
  border-top: 1px solid rgba(178,143,110,0.18);
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
  display: inline-flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  width: fit-content;
  max-width: 100%;
  padding: 8px 9px;
  border: 1px solid rgba(178,143,110,0.22);
  border-radius: 7px;
  background: rgba(252,248,245,0.78);
}
.gp-slot-box {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 8px;
}
.slot-date,
.slot-time {
  display: inline-flex;
  align-items: center;
  white-space: nowrap;
}
.slot-date { font-size: 11px; font-weight: 700; color: var(--ink); }
.slot-time { font-size: 11px; color: var(--ink2); }

.gp-btn-change-slot {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-height: 28px;
  padding: 0 10px;
  border-radius: 7px;
  border: 1px solid rgba(140,95,114,0.26);
  background: transparent;
  color: var(--mauve);
  font-size: 10px;
  font-weight: 800;
  white-space: nowrap;
  transition: all 0.2s;
}
.gp-btn-change-slot:hover { border-color: var(--mauve); background: var(--mauve-xs); }

.gp-slot-selector {
  display: grid;
  grid-template-columns: minmax(150px, 0.85fr) minmax(260px, 1.8fr) auto;
  gap: 10px;
  align-items: start;
  margin-top: 8px;
  padding: 16px;
  border-top: 1px solid rgba(178,143,110,0.18);
  border-radius: 0 0 12px 12px;
  background: rgba(252,248,245,0.78);
}
.gp-slot-selector.hidden { display: none; }
.gp-slot-edit-field {
  display: flex;
  flex-direction: column;
  gap: 4px;
  min-width: 0;
}
.gp-slot-edit-field.is-wide {
  min-width: 0;
}
.gp-btn-cancel-change {
  align-self: end;
  min-height: 36px;
  padding: 0 12px;
  border-radius: 7px;
  border: 1px solid rgba(140,95,114,0.24);
  background: transparent;
  color: var(--mist);
  font-size: 11px;
  font-weight: 800;
  transition: all 0.2s;
}
.gp-btn-cancel-change:hover { border-color: var(--danger); color: var(--danger); }

.gp-slots-container {
  display: flex;
  flex-wrap: wrap;
  gap: 5px;
  align-content: flex-start;
}
.gp-slot-option {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  min-height: 26px;
  padding: 4px 8px;
  border: 1px solid rgba(140,95,114,0.16);
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s;
  background: var(--ivory);
  font-size: 10px;
  color: var(--ink);
  white-space: nowrap;
}
.gp-slot-option:hover { border-color: var(--mauve); background: var(--mauve-xs); }
.gp-slot-option input[type="radio"] { accent-color: var(--mauve); }
.loading { font-size: 13px; color: var(--mist); padding: 10px 0; font-style: italic; }
.error { font-size: 13px; color: var(--danger); padding: 6px 0; }

.gp-package-schedule {
  margin-top: 10px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.gp-package-schedule.is-missing .gp-package-summary,
.gp-package-schedule.is-missing .loading,
.gp-package-schedule.is-missing .error {
  border-color: var(--danger);
}
.gp-package-summary {
  padding: 10px 12px;
  border: 1px solid var(--rule-md);
  border-radius: var(--r-sm);
  background: var(--parchment);
  color: var(--ink);
  font-size: 12px;
}
.gp-package-summary strong {
  display: block;
  margin-bottom: 2px;
  font-size: 13px;
}
.gp-package-timeline {
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.gp-package-service-row {
  display: grid;
  grid-template-columns: minmax(0, 1fr) auto;
  gap: 12px;
  align-items: center;
  padding: 10px 12px;
  border: 1px solid var(--rule-md);
  border-radius: var(--r-sm);
  background: var(--ivory);
}
.gp-package-service-row.is-full {
  border-color: rgba(174, 64, 64, 0.35);
  background: rgba(174, 64, 64, 0.06);
}
.gp-package-service-main {
  min-width: 0;
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.gp-package-service-name {
  font-size: 13px;
  font-weight: 650;
  color: var(--ink);
}
.gp-package-service-meta {
  font-size: 12px;
  color: var(--mist);
}
.gp-package-status {
  justify-self: end;
  padding: 4px 8px;
  border-radius: var(--r-pill);
  background: rgba(55, 126, 92, 0.1);
  color: #377e5c;
  font-size: 11px;
  font-weight: 650;
  white-space: nowrap;
}
.gp-package-service-row.is-full .gp-package-status {
  background: rgba(174, 64, 64, 0.1);
  color: var(--danger);
}

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

/* Unavailable package services panel */
.gp-unavailable-panel { display:none; margin-bottom:12px; border:1px solid #fcd34d; border-radius:var(--r-md); background:#fffdf5; overflow:hidden; }
.gp-unavailable-panel.show { display:block; }
.gp-unavailable-head { padding:14px 16px; border-bottom:1px solid #fcd34d; display:flex; align-items:flex-start; gap:10px; }
.gp-unavailable-head-icon { flex-shrink:0; width:20px; height:20px; color:#d97706; margin-top:1px; }
.gp-unavailable-head-text strong { display:block; color:#92400e; font-size:13px; margin-bottom:2px; }
.gp-unavailable-head-text span { color:#b45309; font-size:12px; }
.gp-unavailable-body { padding:12px 16px; }
.gp-unavailable-services { display:flex; flex-direction:column; gap:10px; }
.gp-svc-status-row { display:flex; align-items:center; gap:10px; padding:8px 12px; border-radius:var(--r-sm); border:1px solid #e5e7eb; background:#fff; }
.gp-svc-status-row.unavailable { border-color:#fecaca; background:#fef2f2; }
.gp-svc-status-icon { flex-shrink:0; width:18px; height:18px; }
.gp-svc-status-icon.available { color:#16a34a; }
.gp-svc-status-icon.unavailable { color:#dc2626; }
.gp-svc-status-name { font-size:13px; font-weight:600; flex:1; min-width:0; }
.gp-svc-status-row.unavailable .gp-svc-status-name { color:#991b1b; }
.gp-svc-status-detail { font-size:11px; color:#7f746d; margin-top:2px; }
.gp-svc-alt-dates { display:flex; flex-wrap:wrap; gap:4px; margin-top:6px; }
.gp-alt-date-pill { display:inline-flex; align-items:center; padding:4px 10px; border:1px solid #d1d5db; border-radius:999px; background:#fff; color:#374151; font-size:11px; font-weight:700; font-family:inherit; cursor:pointer; transition:all .14s; }
.gp-alt-date-pill:hover { border-color:#6d4c5b; color:#6d4c5b; background:#fdf2f8; }
.gp-all-available-section { padding:12px 16px; border-bottom:1px solid #fcd34d; background:#f0fdf4; }
.gp-all-available-label { display:flex; align-items:center; gap:6px; font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:#16a34a; margin-bottom:8px; }
.gp-all-available-dates { display:flex; flex-wrap:wrap; gap:6px; }
.gp-all-date-btn { display:inline-flex; align-items:center; gap:4px; padding:6px 14px; border:2px solid #16a34a; border-radius:999px; background:#fff; color:#16a34a; font-size:12px; font-weight:700; font-family:inherit; cursor:pointer; transition:all .14s; }
.gp-all-date-btn:hover { background:#16a34a; color:#fff; transform:translateY(-1px); box-shadow:0 4px 12px rgba(22,163,74,.2); }
.gp-unavailable-actions { padding:10px 16px; border-top:1px solid #fcd34d; display:flex; gap:8px; flex-wrap:wrap; }
.gp-unavailable-actions button { font-family:inherit; font-size:11px; font-weight:700; padding:6px 14px; border-radius:999px; cursor:pointer; transition:all .14s; text-decoration:none; display:inline-flex; align-items:center; gap:4px; }
.gp-ua-btn-choose { border:1px solid #6d4c5b; background:#6d4c5b; color:#fff; }
.gp-ua-btn-choose:hover { background:#5a3e4a; }
.gp-ua-btn-remove { border:1px solid #e5e7eb; background:#fff; color:#6b7280; }
.gp-ua-btn-remove:hover { border-color:#dc2626; color:#dc2626; }
.gp-detail-textarea { min-height: 70px; resize: vertical; }
.gp-detail-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.gp-detail-field { display: flex; flex-direction: column; gap: 4px; }
.gp-detail-field.full { grid-column: 1 / -1; }

/* Service drawer */
.gp-service-drawer {
  border-top: 0;
  padding-top: 14px;
}
.gp-service-drawer summary {
  list-style: none;
  display: grid;
  grid-template-columns: auto 1fr auto;
  align-items: center;
  gap: 10px;
  padding: 0;
  border: 0;
  border-radius: 0;
  background: transparent;
  color: var(--mist);
  font-size: 10px;
  font-weight: 600;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  cursor: pointer;
  transition: all 0.18s;
}
.gp-service-drawer summary::-webkit-details-marker { display: none; }
.gp-service-drawer summary > span:first-child {
  grid-column: 1;
  color: var(--mist);
}
.gp-service-drawer summary::after {
  content: '⌄';
  display: grid;
  place-items: center;
  width: 34px;
  height: 20px;
  border-radius: 6px;
  background: transparent;
  color: var(--mauve);
  border: 1px solid rgba(140,95,114,0.20);
  font-size: 13px;
  line-height: 1;
  transition: transform 0.18s var(--ease);
  grid-column: 3;
}
.gp-service-drawer summary::before {
  content: none;
}
.gp-service-drawer[open] summary {
  color: var(--mist);
  background: transparent;
}
.gp-service-drawer[open] summary::after {
  transform: rotate(180deg);
}
.gp-drawer-hint {
  grid-column: 1 / -1;
  order: 4;
  margin-top: -4px;
  color: #a85f5f;
  font-size: 11px;
  font-weight: 600;
  letter-spacing: 0;
  line-height: 1.35;
  text-transform: none;
}
.gp-overrides-fieldset {
  margin-top: 12px;
  padding: 16px;
  border: 1px solid var(--rule);
  border-radius: var(--r-sm);
  background: rgba(252,248,245,0.4);
}

/* venue-filled input style */
input[data-venue-filled="true"],
input[data-suggested-filled="true"] {
  border-color: rgba(122,156,130,0.35);
  background-color: rgba(122,156,130,0.04);
}
[data-is-venue="true"].gp-item-card {
  border-color: rgba(184,146,74,0.24);
}

/* ─── Sidebar ────────────────────────────── */
.gp-sidebar {
  position: sticky;
  top: 84px;
  opacity: 0;
  animation: fadeUp 0.8s var(--ease) 0.3s forwards;
}

.gp-summary-card {
  background: rgba(252,248,245,0.96);
  border: 1px solid rgba(178,143,110,0.18);
  border-radius: 14px;
  overflow: hidden;
  box-shadow: 0 18px 52px rgba(26,17,24,0.09);
}

.gp-summary-head {
  padding: 24px 26px 14px;
}
.gp-summary-eyebrow {
  margin-bottom: 8px;
  color: #817476;
  font-size: 10px;
  font-weight: 800;
  letter-spacing: 0.16em;
  text-transform: uppercase;
}
.gp-summary-title {
  color: #0f0c12;
  font-family: var(--sans);
  font-size: clamp(21px, 2.1vw, 27px);
  font-weight: 800;
  letter-spacing: 0;
  line-height: 1.05;
}
.gp-summary-subtitle {
  margin-top: 8px;
  color: #817476;
  font-size: 12px;
  line-height: 1.3;
}

.gp-summary-body { padding: 6px 26px 20px; }
.gp-line-items {
  display: flex;
  flex-direction: column;
  gap: 14px;
  margin-bottom: 18px;
}
.gp-summary-service {
  display: grid;
  grid-template-columns: 48px minmax(0, 1fr) auto;
  gap: 13px;
  align-items: center;
  padding: 10px 0 14px;
}
.gp-summary-service-icon {
  display: grid;
  place-items: center;
  width: 48px;
  height: 48px;
  border-radius: 13px;
  background: rgba(140,95,114,0.09);
  color: var(--mauve);
  overflow: hidden;
}
.gp-summary-service-icon svg {
  width: 24px;
  height: 24px;
  stroke-width: 1.7;
}
.gp-summary-service-icon img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.gp-summary-service-main { min-width: 0; }
.gp-summary-service-name {
  color: #111016;
  font-size: 12px;
  font-weight: 800;
  line-height: 1.25;
}
.gp-summary-service-date {
  display: flex;
  align-items: center;
  gap: 7px;
  margin-top: 6px;
  color: #8d8187;
  font-size: 10px;
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
  font-size: 12px;
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
  font-size: 12px;
  font-weight: 500;
}
.gp-line-dots { display: none; }
.gp-line-val {
  color: #1f1a21;
  font-size: 12px;
  font-weight: 500;
  white-space: nowrap;
}

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
  font-size: 12px;
  font-weight: 800;
}
.gp-total-amount {
  font-family: var(--sans);
  font-size: clamp(18px, 1.8vw, 22px);
  font-weight: 800;
  color: var(--mauve);
  line-height: 1;
  white-space: nowrap;
}

.gp-deposit-breakdown {
  display: flex;
  flex-direction: column;
  gap: 14px;
  margin-top: 0;
  padding: 0;
  border: 0;
  background: transparent;
}
.gp-deposit-line {
  display: flex;
  justify-content: space-between;
  align-items: baseline;
  gap: 16px;
  color: #1f1a21;
  font-size: 12px;
}
.gp-deposit-line strong {
  color: #1f1a21;
  font-weight: 500;
  font-size: 12px;
  white-space: nowrap;
}
.gp-deposit-highlight {
  display: none;
}

/* Buttons */
.gp-summary-footer {
  display: flex;
  flex-direction: column;
  gap: 11px;
  padding: 0 26px 22px;
}

.gp-btn-primary {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  height: 48px;
  border-radius: 5px;
  border: none;
  background: var(--mauve);
  color: #fffaf3;
  font-family: var(--sans);
  font-size: 12px;
  font-weight: 800;
  letter-spacing: 0;
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
  background: linear-gradient(90deg, transparent, rgba(252,248,245,0.10), transparent);
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
  gap: 10px;
  height: 44px;
  border-radius: 7px;
  border: 1px solid rgba(140,95,114,0.48);
  background: transparent;
  color: var(--mauve);
  font-family: var(--sans);
  font-size: 12px;
  font-weight: 800;
  transition: all 0.2s;
}
.gp-btn-secondary:hover { border-color: var(--mauve); color: var(--mauve); background: var(--mauve-xs); }

/* Trust section */
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
.gp-trust-icon-wrap {
  display: grid;
  place-items: center;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: rgba(140,95,114,0.07);
  color: var(--mauve);
}
.gp-trust-icon {
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

/* Spinner */
.gp-spinner {
  display: inline-block;
  width: 16px; height: 16px;
  border: 2px solid rgba(252,248,245,0.3);
  border-top-color: #fcf8f5;
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
  .gp-item-card {
    grid-template-columns: 130px minmax(0, 1fr);
    gap: 12px;
  }
  .gp-item-name { font-size: 14px; }
}
@media (max-width: 640px) {
  .gp-items { max-width: none; }
  .gp-item-card {
    grid-template-columns: 1fr;
    gap: 12px;
    padding: 12px;
  }
  .gp-item-thumb {
    min-height: 124px;
    aspect-ratio: 16 / 10;
  }
  .gp-item-info {
    grid-column: auto;
    padding: 0 92px 0 0;
    gap: 6px;
  }
  .gp-item-name { font-size: 14px; }
  .gp-item-meta,
  .gp-item-date-line { font-size: 11px; }
  .gp-item-price-val { font-size: 13px; }
  .gp-slot-display {
    width: 100%;
  }
  .gp-slot-selector {
    grid-template-columns: 1fr;
  }
  .gp-btn-cancel-change {
    justify-self: end;
  }
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

<?php $gpNavActive = 'bookings'; require APPROOT . '/views/layouts/customerHomeNav.php'; ?>

<!-- ─── Main ──────────────────────────────── -->
<main class="gp-page">

  <!-- Page heading -->
  <div class="gp-page-head">
    <h1 class="gp-page-title">Confirm Your Booking</h1>
    <div class="gp-page-eyebrow">ALMOST THERE</div>
    <p class="gp-page-subtitle">Review your selections and add any details your suppliers need.</p>
  </div>

  <form id="booking-form" method="POST" action="<?= URLROOT ?>/booking/createPost">
    <?= csrf_field() ?>
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
        <?php $isPackageItem = ($item['item_type'] ?? '') === 'package'; ?>
        <article class="gp-card gp-item-card" data-index="<?= $i + 1 ?>"
                 data-has-slot="<?= $hasSlot ? 'yes' : 'no' ?>"
                 data-booking-type="<?= $h($itemBookingType) ?>"
                 data-price-index="<?= $i ?>"
                 data-unit-price="<?= $h($linePrice) ?>"
	                 data-guest-priced="<?= $isGuestPriced ? 'yes' : 'no' ?>"
	                 data-hall-capacity="<?= $venueRoomCapacity ?>"
	                 data-min-lead-days="<?= $minLeadDays ?>"
	                 data-earliest-date="<?= $h($earliestBookingDate) ?>"
	                 data-item-type="<?= $h($item['item_type'] ?? 'service') ?>"
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
              <div class="gp-item-cat-ribbon"><?= $h($item['category_name'] ?? 'Service') ?></div>
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
              <?php if ($formatSlotDate || $formatSlotTime): ?>
              <div class="gp-item-date-line">
                <?php if ($formatSlotDate): ?>
                <span>
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                  <?= $h($formatSlotDate) ?>
                </span>
                <?php endif; ?>
                <?php if ($formatSlotDate && $formatSlotTime): ?>
                <span class="gp-item-meta-sep" aria-hidden="true">|</span>
                <?php endif; ?>
                <?php if ($formatSlotTime): ?>
                <span>
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                  <?= $h($formatSlotTime) ?>
                </span>
                <?php endif; ?>
              </div>
              <?php endif; ?>
              <div class="gp-item-price-val is-inline" data-item-price="<?= $i ?>"><?= $money($linePrice) ?></div>
            </div>
          </div>

          <!-- Details -->
          <div class="gp-item-details">

            <!-- Slot section -->
            <fieldset class="gp-slot-fieldset">
              <legend class="gp-fieldset-legend"><?= $isPackageItem ? 'Event date & package timeline' : 'Date & time' ?></legend>

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
                    Edit
                  </button>
                </div>

                <!-- Hidden fields for current slot (preserved when not changed) -->
                <input type="hidden" name="item_date[<?= $i ?>]" value="<?= $h($slotDate) ?>"
                       data-min-lead-days="<?= (int)$minLeadDays ?>"
                       data-earliest-date="<?= $h($earliestBookingDate) ?>">
	                <input type="hidden" name="item_start_time[<?= $i ?>]" value="<?= $h($slotStart) ?>">
	                <input type="hidden" name="item_end_time[<?= $i ?>]" value="<?= $h($slotEnd) ?>">

	                <?php if (($item['item_type'] ?? '') === 'package'): ?>
	                  <div class="gp-package-schedule" id="package-schedule-<?= $i ?>" aria-live="polite" data-package-schedule-state="empty">
	                    <p class="loading">Loading the package timeline…</p>
	                  </div>
	                <?php endif; ?>

	                <!-- Hidden slot selector -->
                <div class="gp-slot-selector hidden" id="slot-selector-<?= $i ?>">
                  <?php
                    $minLeadDays = (int)($item['min_lead_days'] ?? 0);
                    $today = new DateTimeImmutable('today');
                    $minDate = $today->add(new DateInterval('P' . $minLeadDays . 'D'));
                    $minDateStr = $minDate->format('Y-m-d');
                    $minDateDisplay = $minDate->format('M j, Y');
                  ?>
                  <div class="gp-slot-edit-field">
                    <label class="gp-detail-label" for="slot-date-<?= $i ?>">New date</label>
                    <input class="gp-detail-input" type="date" id="slot-date-<?= $i ?>"
  	                         name="item_date[<?= $i ?>]" value="<?= $h($slotDate) ?>"
  	                         min="<?= $minDateStr ?>"
  	                         <?php if (($item['item_type'] ?? '') !== 'package'): ?>
  	                         data-service-id="<?= (int)($item['item_id'] ?? 0) ?>"
  	                         <?php endif; ?>
  	                         data-min-lead-days="<?= $minLeadDays ?>"
  	                         data-index="<?= $i ?>">
  	                <?php if ($minLeadDays > 0): ?>
  	                  <div class="gp-input-note">Requires <?= $minLeadDays ?> day<?= $minLeadDays === 1 ? '' : 's' ?> advance notice (earliest: <?= $minDateDisplay ?>)</div>
  	                <?php endif; ?>
  	                <?php if ($isPackageItem): ?>
  	                  <div class="gp-input-note">
  	                    Choose the wedding/event date once. Golden Promise will assign each included service to its planned time slot from supplier availability.
  	                  </div>
  	                <?php endif; ?>
                  </div>

                  <div class="gp-slot-edit-field is-wide">
                    <label class="gp-detail-label">Available slots</label>
                    <div class="gp-slots-container" id="slots-<?= $i ?>">
                      <p class="loading">Loading slots…</p>
                    </div>
                  </div>

                  <button type="button" class="gp-btn-cancel-change" data-index="<?= $i ?>">Keep original</button>
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
                <label class="gp-detail-label" for="slot-date-<?= $i ?>"><?= $isPackageItem ? 'Select event date' : 'Select date' ?></label>
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
	                      <?= $isPackageItem ? 'Package timeline' : 'Full-day booking' ?> — estimated window: <?= $h($fmtStart) ?> – <?= $h($fmtEnd) ?>
	                    <?php else: ?>
	                      <?= $isPackageItem ? 'Package service times are managed automatically after you choose the event date' : 'Full-day booking — time is managed automatically' ?>
	                    <?php endif; ?>
                  </div>
	                  <?php if (($item['item_type'] ?? '') === 'package'): ?>
	                    <div class="gp-package-schedule" id="package-schedule-<?= $i ?>" aria-live="polite" data-package-schedule-state="empty">
	                      <p class="loading">Choose the event date. We will build the included-service timeline automatically.</p>
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
	                <span class="gp-drawer-hint"><?= $isPackageItem ? 'Shared guest count, location, contact and notes for this package' : 'Required contact, guests, room and notes' ?></span>
              </summary>

              <fieldset class="gp-overrides-fieldset">
                <div class="gp-detail-row">
                  <div class="gp-detail-field">
	                    <label class="gp-detail-label" for="guests-<?= $i ?>"><?= $isPackageItem ? 'Event guest count' : 'Guests for this service' ?></label>
                    <div class="gp-detail-stepper">
                      <button class="gp-stepper-btn" type="button" data-stepper="minus" data-target="guests-<?= $i ?>" aria-label="Decrease guests">−</button>
                      <input class="gp-detail-input gp-stepper-input" type="number" id="guests-<?= $i ?>"
                             name="item_guests[<?= $i ?>]" min="0"
                             value="<?= $isVenue && $venueRoomCapacity > 0 ? (int)$venueRoomCapacity : '' ?>"
                             <?php if ($isVenue && $venueRoomCapacity > 0): ?>data-venue-filled="true"<?php endif; ?>
                             placeholder="Required">
                      <button class="gp-stepper-btn" type="button" data-stepper="plus" data-target="guests-<?= $i ?>" aria-label="Increase guests">+</button>
                    </div>
                    <?php if ($isVenue && $venueRoomCapacity > 0): ?>
                      <div class="gp-input-note">Suggested from selected hall max: <strong><?= (int)$venueRoomCapacity ?></strong> guests</div>
                    <?php endif; ?>
                  </div>
                  <div class="gp-detail-field">
	                    <label class="gp-detail-label" for="location-<?= $i ?>"><?= $isPackageItem ? 'Event location / venue room' : 'Location / Venue room' ?></label>
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
                           placeholder="09xxxxxxxxx"
                           inputmode="numeric" pattern="[0-9 ]{10,15}"
                           minlength="10" maxlength="15"
                           title="Phone number must be 10 to 11 digits.">
                  </div>
                </div>
                <div class="gp-detail-row" style="margin-top:12px;">
	                  <div class="gp-detail-field full">
	                    <label class="gp-detail-label" for="notes-<?= $i ?>">Special requests / notes</label>
	                    <textarea class="gp-detail-textarea" id="notes-<?= $i ?>"
	                              name="item_notes[<?= $i ?>]"
	                              placeholder="<?= $isPackageItem ? 'Any notes for the whole package event…' : 'Any specific requirements for this service…' ?>"
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
              <?php foreach ($items as $i => $item):
                $linePrice = (float)($item['cart_price'] ?? $item['price_min'] ?? $item['price_max'] ?? 0);
                $lineName  = $item['service_name'] ?? 'Service';
                $lineHall = trim((string)($item['venue_room_name'] ?? ''));
                if ($lineHall !== '') $lineName .= ' · ' . $lineHall;
                $lineDate = trim((string)($item['selected_date'] ?? ''));
                $lineTime = $formatTimeRange($item['start_time'] ?? '', $item['end_time'] ?? '');
                $lineDetails = [];
                if ($lineDate !== '') $lineDetails[] = $formatDate($lineDate);
                if ($lineTime !== '') $lineDetails[] = $lineTime;
                $lineDetail = !empty($lineDetails) ? implode(' · ', $lineDetails) : 'Date to be confirmed';
                $lineImage = trim((string)($item['thumbnail_url'] ?? ''));
              ?>
              <div class="gp-summary-service">
                <div class="gp-summary-service-icon" aria-hidden="true">
                  <?php if ($lineImage !== ''): ?>
                    <img src="<?= $h($lineImage) ?>" alt="" loading="lazy">
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
                <div class="gp-summary-service-price" data-line-price="<?= $i ?>"><?= $money($linePrice) ?></div>
              </div>
              <?php endforeach; ?>

              <div class="gp-summary-divider"></div>
              <div class="gp-line">
                <span class="gp-line-name">Subtotal</span>
                <span class="gp-line-dots" aria-hidden="true"></span>
                <span class="gp-line-val" data-subtotal-amount><?= $money($total) ?></span>
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

            <div class="gp-total-row">
              <span class="gp-total-label">Estimated total</span>
              <span class="gp-total-amount" data-total-amount><?= $money($total) ?></span>
            </div>
          </div>

          <div class="gp-summary-footer">
            <div id="unavailable-panel" class="gp-unavailable-panel" role="alert" aria-live="polite" hidden>
              <div class="gp-unavailable-head">
                <svg class="gp-unavailable-head-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v2m0 4h.01M12 2l10 19H2L12 2z"/></svg>
                <div class="gp-unavailable-head-text">
                  <strong>This package isn't fully available</strong>
                  <span id="unavailable-panel-subtitle">Some services have no time slots on your selected date.</span>
                </div>
              </div>
              <div id="all-available-section" class="gp-all-available-section" style="display:none">
                <div class="gp-all-available-label">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 13l4 4L19 7"/></svg>
                  All services available on these dates
                </div>
                <div id="all-available-dates" class="gp-all-available-dates"></div>
              </div>
              <div class="gp-unavailable-body">
                <div id="unavailable-services-list" class="gp-unavailable-services"></div>
              </div>
              <div class="gp-unavailable-actions">
                <button type="button" id="ua-btn-scroll" class="gp-ua-btn-choose">Change Dates</button>
                <button type="button" id="ua-btn-remove" class="gp-ua-btn-remove">Remove Package</button>
              </div>
            </div>
            <div class="gp-booking-reminder" id="booking-reminder" role="alert" aria-live="polite"></div>
            <button class="gp-btn-primary" type="submit" id="submit-btn">
              Confirm &amp; Proceed
            </button>
            <a class="gp-btn-secondary" href="<?= URLROOT ?>/cart">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
              Back to Cart
            </a>
          </div>

          <div class="gp-trust" aria-label="Assurances">
            <div class="gp-trust-item">
              <div class="gp-trust-icon-wrap" aria-hidden="true">
                <svg class="gp-trust-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
              </div>
              <div>
                <div class="gp-trust-title">Secure payment</div>
                <div class="gp-trust-copy">Processed via Stripe</div>
              </div>
            </div>
            <div class="gp-trust-item">
              <div class="gp-trust-icon-wrap" aria-hidden="true">
                <svg class="gp-trust-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 16 14"/></svg>
              </div>
              <div>
                <div class="gp-trust-title">Deposit booking</div>
                <div class="gp-trust-copy"><?= $depositPercent ?>% locks your date</div>
              </div>
            </div>
            <div class="gp-trust-item">
              <div class="gp-trust-icon-wrap" aria-hidden="true">
                <svg class="gp-trust-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3 14.2 7.5 19 8.2 15.5 11.7 16.4 16.5 12 14.1 7.6 16.5 8.5 11.7 5 8.2 9.8 7.5 12 3Z"/><path d="m9.6 11.8 1.5 1.5 3.2-3.5"/></svg>
              </div>
              <div>
                <div class="gp-trust-title">Free cancellation</div>
                <div class="gp-trust-copy">Within 48 hours</div>
              </div>
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
const packageScheduleState = new Map();

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
    return 'MMK ' + Math.round(Number(value) || 0).toLocaleString('en-US');
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
    document.querySelector('[data-subtotal-amount]')?.replaceChildren(document.createTextNode(money(total)));
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
    if (title === 'service-details') {
      bookingReminder.textContent = 'Please complete service details before proceeding.';
      bookingReminder.classList.add('show');
      return;
    }
    const items = messages.slice(0, 5).map(message => '<li>' + escapeHtml(message) + '</li>').join('');
    const extra = messages.length > 5 ? '<li>And ' + (messages.length - 5) + ' more item(s).</li>' : '';
    bookingReminder.innerHTML = '<div>' + escapeHtml(title) + '</div><ul>' + items + extra + '</ul>';
    bookingReminder.classList.add('show');
  }

  function showUnavailablePanel(packageServices, unavailableItems, allAvailableDates) {
    const panel = document.getElementById('unavailable-panel');
    const list = document.getElementById('unavailable-services-list');
    const subtitle = document.getElementById('unavailable-panel-subtitle');
    if (!panel || !list) return;

    // Show "all available" dates section if any exist
    const allAvailSection = document.getElementById('all-available-section');
    const allAvailDates = document.getElementById('all-available-dates');
    if (allAvailSection && allAvailDates && allAvailableDates) {
      let allDates = [];
      for (const pkgId in allAvailableDates) {
        if (Array.isArray(allAvailableDates[pkgId])) {
          allDates = allDates.concat(allAvailableDates[pkgId]);
        }
      }
      // Dedupe by date
      const seen = new Set();
      allDates = allDates.filter(d => { if (seen.has(d.date)) return false; seen.add(d.date); return true; });
      if (allDates.length) {
        let datesHtml = '';
        allDates.forEach(function(d) {
          datesHtml += '<button type="button" class="gp-all-date-btn" data-date="' + escapeHtml(d.date) + '">';
          datesHtml += '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 13l4 4L19 7"/></svg>';
          datesHtml += escapeHtml(d.label) + '</button>';
        });
        allAvailDates.innerHTML = datesHtml;
        allAvailSection.style.display = '';

        // Wire up all-available date buttons
        allAvailDates.querySelectorAll('.gp-all-date-btn').forEach(function(btn) {
          btn.addEventListener('click', function() {
            const dateInputs = form.querySelectorAll('input[name^="item_date["]');
            for (var i = 0; i < dateInputs.length; i++) {
              if (dateInputs[i].offsetParent !== null) {
                dateInputs[i].value = this.dataset.date;
                dateInputs[i].scrollIntoView({ behavior: 'smooth', block: 'center' });
                dateInputs[i].focus();
                break;
              }
            }
          });
        });
      } else {
        allAvailSection.style.display = 'none';
      }
    }

    const unavailableIds = new Set(unavailableItems.map(u => parseInt(u.service_id)));
    let html = '';

    // Build service status grid from first packageServices entry
    const firstPkg = (packageServices && packageServices.length) ? packageServices[0] : null;
    const services = firstPkg ? (firstPkg.services || []) : [];
    const pkgDate = firstPkg ? firstPkg.date : '';

    if (services.length) {
      if (subtitle) subtitle.textContent = 'On ' + (pkgDate || 'your date') + ':';

      services.forEach(function(svc) {
        const isAvailable = svc.is_available;
        const svcId = parseInt(svc.service_id);
        const alt = unavailableItems.find(function(u) { return parseInt(u.service_id) === svcId; });

        html += '<div class="gp-svc-status-row' + (isAvailable ? '' : ' unavailable') + '">';
        html += '<svg class="gp-svc-status-icon ' + (isAvailable ? 'available' : 'unavailable') + '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">';
        if (isAvailable) {
          html += '<path d="M5 13l4 4L19 7"/>';
        } else {
          html += '<path d="M6 6l12 12M18 6l-12 12"/>';
        }
        html += '</svg>';
        html += '<div style="flex:1;min-width:0">';
        html += '<div class="gp-svc-status-name">' + escapeHtml(svc.service_name) + '</div>';
        if (!isAvailable && alt) {
          html += '<div class="gp-svc-status-detail">' + escapeHtml(alt.message || 'No time slots available') + '</div>';
          if (Array.isArray(alt.alternatives) && alt.alternatives.length) {
            html += '<div class="gp-svc-alt-dates">';
            alt.alternatives.slice(0, 5).forEach(function(a) {
              html += '<button type="button" class="gp-alt-date-pill" data-date="' + escapeHtml(a.date) + '" data-service="' + svcId + '">' + escapeHtml(a.label || a.date) + '</button>';
            });
            html += '</div>';
          }
        } else if (isAvailable) {
          html += '<div class="gp-svc-status-detail" style="color:#16a34a">Available on this date</div>';
        }
        html += '</div></div>';
      });
    } else {
      // Fallback: just list unavailable items
      unavailableItems.forEach(function(u) {
        html += '<div class="gp-svc-status-row unavailable">';
        html += '<svg class="gp-svc-status-icon unavailable" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 6l12 12M18 6l-12 12"/></svg>';
        html += '<div style="flex:1;min-width:0">';
        html += '<div class="gp-svc-status-name">' + escapeHtml(u.service_name || 'A package service') + '</div>';
        html += '<div class="gp-svc-status-detail">' + escapeHtml(u.message || 'Not available') + '</div>';
        if (Array.isArray(u.alternatives) && u.alternatives.length) {
          html += '<div class="gp-svc-alt-dates">';
          u.alternatives.slice(0, 5).forEach(function(a) {
            html += '<button type="button" class="gp-alt-date-pill" data-date="' + escapeHtml(a.date) + '" data-service="' + parseInt(u.service_id) + '">' + escapeHtml(a.label || a.date) + '</button>';
          });
          html += '</div>';
        }
        html += '</div></div>';
      });
    }

    list.innerHTML = html;
    panel.hidden = false;

    // Wire up alternative date pills
    list.querySelectorAll('.gp-alt-date-pill').forEach(function(btn) {
      btn.addEventListener('click', function() {
        // Find first visible date input and set it to the clicked alternative date
        const dateInputs = form.querySelectorAll('input[name^="item_date["]');
        for (var i = 0; i < dateInputs.length; i++) {
          if (dateInputs[i].offsetParent !== null) {
            dateInputs[i].value = this.dataset.date;
            dateInputs[i].scrollIntoView({ behavior: 'smooth', block: 'center' });
            dateInputs[i].focus();
            break;
          }
        }
      });
    });

    // Wire up action buttons
    var scrollBtn = document.getElementById('ua-btn-scroll');
    if (scrollBtn) {
      scrollBtn.onclick = function() {
        var firstDateInput = form.querySelector('input[name^="item_date["]');
        if (firstDateInput) {
          firstDateInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
          firstDateInput.focus();
        }
      };
    }

    var removeBtn = document.getElementById('ua-btn-remove');
    if (removeBtn) {
      removeBtn.onclick = function() {
        if (confirm('Remove this package from your order?')) {
          // Find the first package item's remove button
          var removeBtns = document.querySelectorAll('.gp-btn-item-remove');
          if (removeBtns.length) {
            removeBtns[0].click();
          }
          panel.hidden = true;
        }
      };
    }
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
      if (!itemPhone) {
        missing.push('contact phone');
        rememberMissing(card.querySelector(`[name="item_contact_phone[${index}]"]`));
      } else {
        const phoneDigits = itemPhone.replace(/\D/g, '');
        if (phoneDigits.length < 10 || phoneDigits.length > 11) {
          missing.push('contact phone (must be 10–11 digits)');
          rememberMissing(card.querySelector(`[name="item_contact_phone[${index}]"]`));
        }
      }
      if (!itemLocation) {
        missing.push('location');
        rememberMissing(card.querySelector(`[name="item_location[${index}]"]`));
      }
      if (itemGuests <= 0) {
        missing.push('guest count');
        rememberMissing(card.querySelector(`[name="item_guests[${index}]"]`));
      }

      if (card.dataset.packageId && itemDate) {
        const scheduleState = packageScheduleState.get(String(index));
        const scheduleBox = document.getElementById('package-schedule-' + index);
        const dateInput = card.querySelector(`[name="item_date[${index}]"]`);
        let packageScheduleError = '';

        if (!scheduleState || scheduleState.date !== itemDate) {
          packageScheduleError = 'package timeline is not ready';
          if (!scheduleState || scheduleState.status !== 'loading') {
            loadPackageSchedule(card.dataset.packageId, itemDate, index);
          }
        } else if (scheduleState.status === 'loading') {
          packageScheduleError = 'package timeline is still loading';
        } else if (scheduleState.status === 'error') {
          packageScheduleError = scheduleState.message || 'package timeline could not be built';
        } else if (scheduleState.status === 'unavailable') {
          packageScheduleError = scheduleState.message || 'one or more package services are full';
        } else if (!itemStart || !itemEnd) {
          packageScheduleError = 'package timeline did not resolve a start and end time';
        }

        if (packageScheduleError) {
          missing.push(packageScheduleError);
          rememberMissing(scheduleBox || dateInput);
        }
      }

      if (missing.length) {
        if (drawer) drawer.open = true;
        missingMessages.push(serviceName + ': ' + missing.join(', '));
        const detailMissing = ['contact phone', 'location', 'guest count'].filter((label) => missing.includes(label));
        addRequiredHint(card, 'Please complete: ' + (detailMissing.length ? detailMissing : missing).join(', ') + '.');
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
        showBookingReminder(allErrors, 'service-details');
      }

      showToast(errorMsg, 'error');
      firstMissingField?.scrollIntoView({ behavior: 'smooth', block: 'center' });
      firstMissingField?.focus?.();
      return false;
    }

    return true;
  }

  async function refreshPackageSchedulesBeforeSubmit() {
    const packageCards = Array.from(document.querySelectorAll('.gp-item-card[data-package-id]'));
    const refreshes = [];

    packageCards.forEach(card => {
      const index = card.dataset.priceIndex;
      const dateInput = card.querySelector(`input[name="item_date[${index}]"]`);
      const date = String(dateInput?.value || '').trim();
      if (!date) return;

      const state = packageScheduleState.get(String(index));
      if (!state || state.date !== date || state.status === 'empty' || state.status === 'error') {
        refreshes.push(loadPackageSchedule(card.dataset.packageId, date, index));
      }
    });

    if (refreshes.length > 0) {
      await Promise.allSettled(refreshes);
    }
  }

  form.addEventListener('submit', async function (e) {
    e.preventDefault();
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="gp-spinner"></span> Checking schedule…';

    await refreshPackageSchedulesBeforeSubmit();

    submitBtn.disabled = false;
    submitBtn.innerHTML = originalSubmitHtml;
    if (!validateRequiredBookingInfo()) return;

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="gp-spinner"></span> Creating booking…';
    document.getElementById('unavailable-panel').hidden = true;
    var allAvailSec = document.getElementById('all-available-section');
    if (allAvailSec) allAvailSec.style.display = 'none';
    bookingReminder.classList.remove('show');
    fetch(form.action, { method: 'POST', body: new FormData(form) })
      .then(r => r.json())
      .then(data => {
        if (data.success && data.redirect) {
          window.location.href = data.redirect;
        } else if (Array.isArray(data.unavailable) && data.unavailable.length) {
          showUnavailablePanel(data.packageServices || [], data.unavailable, data.allAvailableDates || {});
          const first = data.unavailable[0];
          const svcName = first.service_name || 'A package service';
          showToast(svcName + ': ' + (first.message || 'not available'), 'error');
          submitBtn.disabled = false;
          submitBtn.innerHTML = originalSubmitHtml;
        } else {
          const error = data.error || 'An unexpected error occurred.';
          const title = data.error ? 'Please complete the following:' : 'Booking could not be completed';
          showBookingReminder([error], title);
          showToast(error, 'error');
          submitBtn.disabled = false;
          submitBtn.innerHTML = originalSubmitHtml;
        }
      })
      .catch(() => {
        showToast('Unable to reach the server. Please check your connection and try again.', 'error');
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
  index = String(index);
  const container = document.getElementById('package-schedule-' + index);
  if (!container) return;
  if (!date) {
    packageScheduleState.set(index, { status: 'empty', date: '' });
    container.dataset.packageScheduleState = 'empty';
    container.innerHTML = '<p class="loading">Select the event date to build the package timeline.</p>';
    return;
  }

  packageScheduleState.set(index, { status: 'loading', date });
  container.dataset.packageScheduleState = 'loading';
  container.innerHTML = '<p class="loading">Building your package timeline…</p>';
  try {
    const response = await fetch('<?= URLROOT ?>/booking/getPackageSchedule', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ package_id: packageId, date })
    });
    const data = await response.json();
    if (!response.ok || !data.success) {
      const message = data.error || 'Could not build the package timeline';
      packageScheduleState.set(index, { status: 'error', date, message });
      container.dataset.packageScheduleState = 'error';
      container.innerHTML = '<p class="error">' + packageEscapeHtml(message) + '</p>';
      return;
    }

    const card = container.closest('.gp-item-card');
    const startField = card?.querySelector(`input[name="item_start_time[${index}]"]`);
    const endField = card?.querySelector(`input[name="item_end_time[${index}]"]`);
    if (startField) startField.value = data.start_time || '';
    if (endField) endField.value = data.end_time || '';

    const schedule = Array.isArray(data.schedule) ? data.schedule : [];
    const unavailable = schedule.filter(item => item.booking_type === 'slot' && !item.is_available);
    const scheduleReady = schedule.length > 0 && unavailable.length === 0 && data.start_time && data.end_time;
    const message = unavailable.length
      ? unavailable.map(item => item.service_name || 'Package service').join(', ') + ' has no package slot left.'
      : 'Package timeline ready.';
    packageScheduleState.set(index, {
      status: scheduleReady ? 'ready' : 'unavailable',
      date,
      message,
      startTime: data.start_time || '',
      endTime: data.end_time || '',
      unavailableCount: unavailable.length
    });
    container.dataset.packageScheduleState = scheduleReady ? 'ready' : 'unavailable';

    container.innerHTML = `
      <div class="gp-package-summary">
        <strong>Automatically managed package timeline</strong>
        ${packageTime(data.start_time)} – ${packageTime(data.end_time)}
      </div>
      <div class="gp-package-timeline">
      ${schedule.map(item => {
        const isSlot = item.booking_type === 'slot';
        const isAvailable = !isSlot || Boolean(item.is_available);
        const status = isSlot
          ? (isAvailable ? packageEscapeHtml(item.availability_message || 'Available') : packageEscapeHtml(item.availability_message || 'Full'))
          : 'Managed';
        return `
        <div class="gp-package-service-row ${isAvailable ? '' : 'is-full'}">
          <div class="gp-package-service-main">
            <div class="gp-package-service-name">${packageEscapeHtml(item.service_name || 'Package service')}</div>
            <div class="gp-package-service-meta">${packageTime(item.start_time)} – ${packageTime(item.end_time)} · ${packageEscapeHtml(item.supplier_name || 'Golden Promise')}</div>
          </div>
          <div class="gp-package-status">${status}</div>
        </div>
        `;
      }).join('')}
      </div>
    `;
  } catch (error) {
    const message = 'Could not build the package timeline. Please try again.';
    packageScheduleState.set(index, { status: 'error', date, message });
    container.dataset.packageScheduleState = 'error';
    container.innerHTML = '<p class="error">' + message + '</p>';
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
<?php include APPROOT . '/views/partials/cookie-consent.php'; ?>
</body>
</html>
