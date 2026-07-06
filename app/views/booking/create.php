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
$customerName = trim((string)($user['name'] ?? ''));
$customerEmail = trim((string)($user['email'] ?? ''));
$customerPhone = trim((string)($user['phone'] ?? ''));
$customerAvatar = trim((string)($user['avatar'] ?? ($_SESSION['session_avatar'] ?? '')));
$customerDisplayName = $customerName !== '' ? $customerName : 'You';
$customerInitials = '';
foreach (preg_split('/\s+/', $customerDisplayName) as $part) {
    if ($part === '') continue;
    $customerInitials .= function_exists('mb_substr') ? mb_substr($part, 0, 1, 'UTF-8') : substr($part, 0, 1);
    if (strlen($customerInitials) >= 2) break;
}
$customerInitials = $customerInitials !== '' ? strtoupper($customerInitials) : 'Y';

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
<link rel="icon" type="image/png" href="<?= URLROOT ?>/public/images/home/gp_logo.png">
<title>Confirm Booking — Golden Promise</title>
<?php include APPROOT . '/views/partials/ga-tracking.php'; ?>
<?php $publicCssVersion = file_exists(APPROOT . '/../public/css/app.css') ? filemtime(APPROOT . '/../public/css/app.css') : time(); ?>
<link rel="stylesheet" href="<?= URLROOT ?>/public/css/app.css?v=<?= $publicCssVersion ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,500;1,600&family=Jost:wght@300;400;500;600&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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

.gp-floating-cart {
  display: none !important;
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
  max-width: 1320px;
  margin: 0 auto;
  width: 100%;
}

/* ─── Page header ─────────────────────────── */
.gp-page-head{
    position:relative;
    display:grid;
    place-items:center;
    min-height:220px;
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
    linear-gradient(rgba(27, 16, 21, 0.55), rgba(0, 0, 0, 0.39)),
    url("<?= URLROOT ?>/app/views/main/images/imageBanner2.jpg") center center / cover no-repeat;
  transform: scale(1.03);
  filter: blur(3px);
  z-index: 0;
}

.gp-page-head::after {
  content: "";
  position: absolute;
  inset: 0;
  background: linear-gradient(to bottom, rgba(249, 241, 233, 0.52), rgba(249, 241, 233, 0.15));
  z-index: 1;
}

.gp-page-head > * {
  position: relative;
  z-index: 2;
}
.gp-page-title {
  font-family: 'Playfair Display', serif;
font-weight: 700;
  font-size: clamp(34px, 4vw, 58px);
  
  color: #fffaf5;
  line-height: 1.05;
  letter-spacing: 0;
  margin-top: 92px;
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
.gp-page-subtitle {
  display: block;
  margin-top: 8px;
  font-size: 14px;
  color: rgba(255, 248, 239, 0.85);
  font-weight: 400;
}

/* ─── Two-column layout ──────────────────── */
.gp-layout {
  display: grid;
  grid-template-columns: minmax(0, 1fr) 420px;
  gap: 34px;
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

/* ─── Package details grid (C1) ──────────── */
.gp-details-section {
  margin-top: 16px;
  padding-top: 16px;
  border-top: 1px solid var(--rule);
}
.gp-details-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
}
.gp-details-grid .gp-detail-field {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

/* ─── Card completion indicator ──────────── */
.gp-card-status {
  position: absolute;
  top: 12px;
  right: 12px;
  z-index: 3;
}
.gp-card-status-pill {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 4px 10px;
  border-radius: var(--r-pill);
  font-size: 10px;
  font-weight: 700;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  transition: all 0.3s var(--ease);
  white-space: nowrap;
}
.gp-card-status-pill.is-empty {
  background: var(--mauve-xs);
  color: var(--mauve);
  border: 1px solid rgba(140,95,114,0.18);
}
.gp-card-status-pill.is-done {
  background: var(--sage-xs);
  color: var(--sage);
  border: 1px solid rgba(122,156,130,0.25);
}

/* ─── Attention pulse ────────────────────── */
@keyframes gp-attention-pulse {
  0%   { box-shadow: 0 0 0 0 rgba(140,95,114,0.3); }
  50%  { box-shadow: 0 0 0 8px rgba(140,95,114,0.08); }
  100% { box-shadow: 0 0 0 0 rgba(140,95,114,0); }
}
.gp-attention-hint {
  animation: gp-attention-pulse 1.5s ease 2;
  border-color: var(--mauve-lt) !important;
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
.gp-customer-card {
  border-radius: var(--r-md);
  overflow: visible;
}
.gp-customer-row {
  display: flex;
  align-items: center;
  gap: 20px;
  padding: 22px 24px;
}
.gp-customer-avatar {
  display: grid;
  place-items: center;
  width: 66px;
  height: 66px;
  border-radius: 50%;
  background: var(--mauve);
  color: #fffaf3;
  font-size: 18px;
  font-weight: 700;
  letter-spacing: 0.03em;
  overflow: hidden;
  flex: 0 0 66px;
  box-shadow: 0 0 0 4px rgba(140,95,114,0.08);
}
.gp-customer-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.gp-customer-copy {
  min-width: 0;
  flex: 1;
}
.gp-card-title {
  font-family: var(--sans);
  font-size: 23px;
  font-weight: 600;
  color: var(--ink);
  line-height: 1.2;
  letter-spacing: 0;
}
.gp-customer-meta {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 4px 10px;
  margin-top: 4px;
  color: #a28778;
  font-size: 15px;
  line-height: 1.45;
}
.gp-customer-meta span {
  min-width: 0;
  overflow-wrap: anywhere;
}
.gp-customer-meta span:not(:last-child)::after {
  content: '-';
  margin-left: 10px;
  color: rgba(162,135,120,0.75);
}
.gp-customer-missing {
  color: var(--mist);
  font-style: italic;
}
.gp-card-action {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  align-self: flex-start;
  margin-left: auto;
  font-size: 12px;
  font-weight: 600;
  color: var(--mauve);
  letter-spacing: 0.02em;
  white-space: nowrap;
  border-bottom: 1px solid rgba(140,95,114,0.3);
  transition: color 0.2s;
}
.gp-card-action:hover { color: var(--mauve-dk); border-color: var(--mauve-dk); }

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
.gp-stepper-btn:disabled {
  cursor: not-allowed;
  opacity: 0.42;
  background: rgba(156,136,147,0.12);
  color: var(--mist);
  border-color: var(--rule);
}
.gp-stepper-btn:disabled:hover {
  background: rgba(156,136,147,0.12);
  color: var(--mist);
  border-color: var(--rule);
}
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
  gap: 14px;
  max-width: 760px;
}

.gp-item-card {
  display: grid;
  grid-template-columns: 164px minmax(0, 1fr);
  align-items: start;              /* important: don't stretch image column */
  gap: 16px;
  position: relative;
  padding: 10px 18px 12px 10px;
  border: 1px solid rgba(255,255,255,0.78);
  border-radius: 16px;
  background: rgba(255,255,255,0.94);
  overflow: visible;
  box-shadow: 0 18px 44px rgba(26,17,24,0.08);
}

.gp-item-thumb {
  grid-column: 1;
  position: relative;
  overflow: hidden;
  width: 100%;
  height: 132px;                   /* fixed image height */
  min-height: 132px;
  max-height: 132px;
  align-self: center;              /* keep image centered vertically */
  border-radius: 9px;
  background: linear-gradient(160deg, var(--linen), var(--parchment2));
}

.gp-item-thumb img,
.gp-item-thumb-placeholder {
  width: 100%;
  height: 100%;
}

.gp-item-thumb img {
  object-fit: cover;
  object-position: center;
  transition: transform 0.55s var(--ease);
}

.gp-item-info {
  grid-column: 2;
  padding: 8px 52px 0 0;
  display: flex;
  flex-direction: column;
  gap: 8px;
  min-width: 0;
  min-height: 132px;               /* match image block height for cleaner alignment */
  justify-content: center;
}
.gp-item-name {
  font-family: var(--sans);
  font-size: 16px;
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
  font-size: 12px;
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
  font-size: 12px;
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
  font-size: 15px;
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
.gp-input-note.is-limit-warning {
  display: none;
  color: var(--danger);
  font-weight: 600;
}
.gp-input-note.is-limit-warning.show {
  display: block;
}
.gp-input-note strong {
  color: var(--mauve);
  font-weight: 600;
}

/* ─── Item details ───────────────────────── */
.gp-item-details {
  grid-column: 1 / -1;
  margin-top: 6px;
  padding: 16px 10px 6px;
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
  grid-template-columns: minmax(116px, 0.55fr) minmax(0, 2.35fr) auto;
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
.venue-date-input-wrap {
  position: relative;
  min-height: 32px;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  width: 100%;
  border: 1px solid rgba(63,36,26,0.18);
  border-radius: 6px;
  background: #fff8ef;
  color: #3f241a;
  padding: 0 8px;
  font-size: 11px;
  font-weight: 800;
  cursor: pointer;
  overflow: hidden;
  box-shadow: 0 4px 14px rgba(63,36,26,0.06);
}
.venue-date-input-wrap:focus-visible {
  outline: 2px solid rgba(140,95,114,0.28);
  outline-offset: 2px;
}
.venue-date-input-wrap input {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  opacity: 0;
  pointer-events: none;
  appearance: none;
  -webkit-appearance: none;
}
.venue-date-input-wrap input::-webkit-calendar-picker-indicator {
  display: none;
}
.venue-date-display {
  min-width: 0;
  flex: 1;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
  pointer-events: none;
}
.venue-date-icon,
.venue-date-chevron {
  flex: 0 0 auto;
  pointer-events: none;
  color: #9A687F;
  width: 13px;
  height: 13px;
}
.gp-calendar-popover {
  position: fixed;
  z-index: 10010;
  width: min(228px, calc(100vw - 32px));
  padding: 10px;
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
  gap: 10px;
  color: #3F241A;
  font-size: 11px;
  font-weight: 900;
  margin-bottom: 8px;
}
.gp-calendar-nav {
  display: inline-grid;
  place-items: center;
  width: 20px;
  height: 20px;
  border: 0;
  border-radius: 7px;
  background: transparent;
  color: #7a4e3d;
  cursor: pointer;
}
.gp-calendar-nav [data-lucide] {
  width: 14px;
  height: 14px;
}
.gp-calendar-nav:hover { background: rgba(63,36,26,0.08); }
.gp-calendar-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 2px;
}
.gp-calendar-day-name,
.gp-calendar-day {
  display: grid;
  place-items: center;
  height: 22px;
  color: #6f5448;
  font-size: 10px;
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
  display: flex;
  flex-direction: column;
  gap: 0;
}
.gp-package-schedule.is-missing .loading,
.gp-package-schedule.is-missing .error {
  border-color: var(--danger);
}
.gp-package-timeline {
  display: flex;
  flex-direction: column;
  gap: 0;
  border: 1px solid var(--rule-md);
  border-radius: var(--r-md);
  overflow: hidden;
}
.gp-package-service-row {
  display: grid;
  grid-template-columns: minmax(0, 1fr) auto;
  gap: 12px;
  align-items: center;
  padding: 10px 14px;
  background: var(--ivory);
  transition: background 0.15s;
}
.gp-package-service-row:not(:last-child) {
  border-bottom: 1px solid var(--rule);
}
.gp-package-service-row:hover {
  background: var(--mauve-xs);
}
.gp-package-service-row.is-full {
  background: rgba(174, 64, 64, 0.04);
}
.gp-package-service-main {
  min-width: 0;
  display: flex;
  flex-direction: column;
  gap: 1px;
}
.gp-package-service-name {
  font-size: 13px;
  font-weight: 600;
  color: var(--ink);
}
.gp-package-service-meta {
  font-size: 11px;
  color: var(--mist);
}
.gp-package-status {
  justify-self: end;
  padding: 3px 8px;
  border-radius: var(--r-pill);
  background: rgba(55, 126, 92, 0.1);
  color: #377e5c;
  font-size: 10px;
  font-weight: 650;
  white-space: nowrap;
  text-transform: uppercase;
  letter-spacing: 0.03em;
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
.gp-detail-label.is-required::after {
  content: ' *';
  color: var(--danger);
  font-weight: 900;
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
.gp-detail-input[type="time"][name^="preferred_time"] {
  min-height: 40px;
  border: 0.5px solid rgba(118,90,70,.20);
  border-radius: 7px;
  background: transparent;
  color: #3F241A;
  font-size: 13px;
  font-weight: 800;
  box-shadow: none;
  color-scheme: light;
}
.gp-time-control {
  position: relative;
  display: flex;
  align-items: center;
  width: min(160px, 100%);
  min-height: 40px;
  border: 0.5px solid rgba(118,90,70,.20);
  border-radius: 7px;
  background: linear-gradient(180deg,#fff8ef,#fcf8f5);
  box-shadow: 0 4px 14px rgba(63,36,26,0.06);
  transition: border-color .18s ease, box-shadow .18s ease, background .18s ease;
  cursor: pointer;
}
.gp-time-control:hover {
  border-color: rgba(154,104,127,.36);
  background: #fff8ef;
}
.gp-time-control:focus-within {
  border-color: rgba(154,104,127,.42);
  box-shadow: 0 0 0 3px rgba(140,95,114,0.10), 0 6px 18px rgba(63,36,26,0.08);
}
.gp-time-control svg {
  position: absolute;
  left: 12px;
  width: 14px;
  height: 14px;
  color: #7A4E3D;
  pointer-events: none;
  stroke-width: 2;
}
.gp-time-control .gp-time-chevron {
  left: auto;
  right: 10px;
  width: 13px;
  height: 13px;
}
.gp-time-display {
  display: block;
  width: 100%;
  min-width: 0;
  padding: 9px 30px 9px 34px;
  color: #3F241A;
  font-size: 13px;
  font-weight: 800;
  line-height: 1.4;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.gp-time-control .gp-detail-input[type="time"][name^="preferred_time"] {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  border: 0;
  padding: 0;
  opacity: 0;
  pointer-events: none;
}
.gp-time-control .gp-detail-input[type="time"][name^="preferred_time"]:focus {
  box-shadow: none;
}
.gp-time-menu {
  position: absolute;
  left: 0;
  top: calc(100% + 8px);
  z-index: 10030;
  display: none;
  width: 188px;
  max-height: 214px;
  overflow-y: auto;
  padding: 6px;
  border: 1px solid rgba(154,104,127,.20);
  border-radius: 10px;
  background: #fff8ef;
  box-shadow: 0 18px 40px rgba(63,36,26,.18);
}
.gp-time-control.is-open .gp-time-menu { display: grid; gap: 2px; }
.gp-time-menu::-webkit-scrollbar { width: 5px; }
.gp-time-menu::-webkit-scrollbar-track { background: rgba(154,104,127,.08); border-radius: 999px; }
.gp-time-menu::-webkit-scrollbar-thumb { background: rgba(154,104,127,.45); border-radius: 999px; }
.gp-time-option {
  width: 100%;
  border: 0;
  border-radius: 7px;
  padding: 9px 10px;
  background: transparent;
  color: #5b3b2d;
  font-size: 12px;
  font-weight: 800;
  text-align: left;
  cursor: pointer;
}
.gp-time-option:hover,
.gp-time-option:focus { background: rgba(154,104,127,.14); color: #3F241A; }
.gp-time-option.is-selected { background: #6D4C5B; color: #fff8ef; }
.gp-detail-input[type="time"][name^="preferred_time"]::-webkit-calendar-picker-indicator {
  opacity: .68;
  cursor: pointer;
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
  font-size: 11px;
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
  font-size: 12px;
  font-weight: 600;
  letter-spacing: 0;
  line-height: 1.35;
  text-transform: none;
}
.gp-overrides-fieldset {
  margin-top: 12px;
  padding: 18px;
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
  padding: 28px 30px 16px;
}
.gp-summary-eyebrow {
  margin-bottom: 8px;
  color: #817476;
  font-size: 11px;
  font-weight: 800;
  letter-spacing: 0.16em;
  text-transform: uppercase;
}
.gp-summary-title {
  color: #0f0c12;
  font-family: var(--sans);
  font-size: clamp(24px, 2.1vw, 30px);
  font-weight: 800;
  letter-spacing: 0;
  line-height: 1.05;
}
.gp-summary-subtitle {
  margin-top: 8px;
  color: #817476;
  font-size: 13px;
  line-height: 1.3;
}

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
  padding: 10px 0 14px;
}
.gp-summary-service-icon {
  display: grid;
  place-items: center;
  width: 56px;
  height: 56px;
  border-radius: 8px;
  background: rgba(107,68,89,0.09);
  color: var(--mauve-dk);
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
  font-family: var(--sans);
  font-size: clamp(21px, 1.9vw, 25px);
  font-weight: 800;
  color: var(--mauve-dk);
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
  font-size: 13px;
}
.gp-deposit-line strong {
  color: #1f1a21;
  font-weight: 500;
  font-size: 13px;
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
  padding: 0 30px 26px;
}

.gp-btn-primary {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  height: 52px;
  border-radius: 5px;
  border: none;
  background: var(--mauve-dk);
  color: #fffaf3;
  font-family: var(--sans);
  font-size: 13px;
  font-weight: 800;
  letter-spacing: 0;
  box-shadow: 0 10px 28px rgba(107,68,89,0.28);
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
.gp-btn-primary:hover { background: #4e3141; transform: translateY(-2px); box-shadow: 0 18px 40px rgba(107,68,89,0.32); }
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
  border: 1px solid rgba(107,68,89,0.48);
  background: transparent;
  color: var(--mauve-dk);
  font-family: var(--sans);
  font-size: 12px;
  font-weight: 800;
  transition: all 0.2s;
}
.gp-btn-secondary:hover { border-color: var(--mauve-dk); color: var(--mauve-dk); background: rgba(107,68,89,0.05); }

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
  background: rgba(107,68,89,0.07);
  color: var(--mauve-dk);
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

/* Match selected-service order summary */
.gp-sidebar .gp-summary-card {
  background: rgba(252,248,245,0.96);
  border: 1px solid rgba(178,143,110,0.18);
  border-radius: 14px;
  box-shadow: 0 18px 52px rgba(26,17,24,0.09);
}
.gp-sidebar .gp-summary-eyebrow {
  color: #817476;
  font-family: 'Poppins', var(--sans);
  font-size: 11px;
  font-weight: 800;
  letter-spacing: 0.16em;
}
.gp-sidebar .gp-summary-title {
  color: #0f0c12;
  font-family: 'Poppins', var(--sans);
  font-size: clamp(24px, 2.1vw, 30px);
  font-weight: 600;
  line-height: 1.05;
}
.gp-sidebar .gp-summary-subtitle,
.gp-sidebar .gp-summary-service-name,
.gp-sidebar .gp-summary-service-date,
.gp-sidebar .gp-summary-service-price,
.gp-sidebar .gp-line-name,
.gp-sidebar .gp-line-val,
.gp-sidebar .gp-deposit-line,
.gp-sidebar .gp-deposit-line strong,
.gp-sidebar .gp-total-label,
.gp-sidebar .gp-btn-primary,
.gp-sidebar .gp-btn-secondary,
.gp-sidebar .gp-trust-title,
.gp-sidebar .gp-trust-copy {
  font-family: 'Poppins', var(--sans);
}
.gp-sidebar .gp-summary-service-icon {
  border-radius: 8px;
  background: rgba(107,68,89,0.09);
  color: #6b4459;
}
.gp-sidebar .gp-total-amount {
  color: #6b4459;
  font-family: 'Poppins', var(--sans);
}
.gp-sidebar .gp-btn-primary {
  background: #6b4459;
  box-shadow: 0 10px 28px rgba(107,68,89,0.28);
}
.gp-sidebar .gp-btn-primary:hover {
  background: #4e3141;
  box-shadow: 0 18px 40px rgba(107,68,89,0.32);
}
.gp-sidebar .gp-btn-secondary {
  border-color: rgba(107,68,89,0.48);
  color: #6b4459;
}
.gp-sidebar .gp-btn-secondary:hover {
  border-color: #6b4459;
  color: #6b4459;
  background: rgba(107,68,89,0.05);
}
.gp-sidebar .gp-trust-icon-wrap {
  background: rgba(107,68,89,0.07);
  color: #6b4459;
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
  .gp-customer-row {
    align-items: flex-start;
    gap: 12px;
    padding: 15px;
  }
  .gp-customer-avatar {
    width: 48px;
    height: 48px;
    flex-basis: 48px;
    font-size: 15px;
  }
  .gp-card-title { font-size: 18px; }
  .gp-customer-meta { font-size: 12px; }
  .gp-card-action { margin-left: 0; }
  .gp-defaults-grid { grid-template-columns: 1fr; }
  .gp-default-field { border-right: none; }
  :root { --pad-x: 16px; }
}
@media (prefers-reduced-motion: reduce) {
  .gp-card, .gp-page-head, .gp-sidebar { animation: none; opacity: 1; transform: none; }
}
/* FIX: keep selected-service image fixed height even when edit/details opens */
.gp-item-card {
  align-items: center !important;
}

.gp-item-header {
  display: contents !important;
}

.gp-item-thumb {
  height: 132px !important;
  min-height: 132px !important;
  max-height: 132px !important;
  align-self: center !important;
}

.gp-item-thumb img,
.gp-item-thumb-placeholder {
  height: 132px !important;
  min-height: 132px !important;
  max-height: 132px !important;
  object-fit: cover !important;
  object-position: center !important;
}

.gp-item-details {
  grid-column: 1 / -1 !important;
}

/* Mobile fix */
@media (max-width: 640px) {
  .gp-item-thumb {
    height: 150px !important;
    min-height: 150px !important;
    max-height: 150px !important;
    aspect-ratio: auto !important;
  }

  .gp-item-thumb img,
  .gp-item-thumb-placeholder {
    height: 150px !important;
    min-height: 150px !important;
    max-height: 150px !important;
  }
}
</style>
<script src="https://unpkg.com/lucide@latest"></script>
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

  <form id="booking-form" method="POST" action="<?= URLROOT ?>/booking/createPost" novalidate>
    <?= csrf_field() ?>
    <div class="gp-layout">

      <!-- LEFT: item cards -->
      <div class="gp-items" id="gp-items">

        <!-- Customer info card (read-only display) -->
        <div class="gp-section-label">Your details</div>
        <section class="gp-card gp-customer-card" data-index="-1">
          <div class="gp-customer-row">
            <div class="gp-customer-avatar" aria-hidden="true">
              <?php if ($customerAvatar !== ''): ?>
                <img src="<?= $h($customerAvatar) ?>" alt="">
              <?php else: ?>
                <span><?= $h($customerInitials) ?></span>
              <?php endif; ?>
            </div>
            <div class="gp-customer-copy">
              <h2 class="gp-card-title" id="customer-info-title"><?= $h($customerDisplayName) ?></h2>
              <div class="gp-customer-meta">
                <span class="<?= $customerEmail === '' ? 'gp-customer-missing' : '' ?>"><?= $h($customerEmail !== '' ? $customerEmail : 'Email not provided') ?></span>
                <span class="<?= $customerPhone === '' ? 'gp-customer-missing' : '' ?>"><?= $h($customerPhone !== '' ? $customerPhone : 'Phone not provided') ?></span>
              </div>
            </div>
            <a class="gp-card-action" href="<?= URLROOT ?>/users/profile" target="_blank" rel="noopener">
              Edit profile
              <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
            </a>
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
          $isBeforeWedding = str_contains($categoryText, 'makeup')
            || str_contains($categoryText, 'make up')
            || str_contains($categoryText, 'beauty')
            || str_contains($categoryText, 'hair')
            || str_contains($categoryText, 'bridal')
            || str_contains($categoryText, 'esthetic')
            || str_contains($categoryText, 'spa')
            || str_contains($serviceNameText, 'makeup')
            || str_contains($serviceNameText, 'make up')
            || str_contains($serviceNameText, 'hair')
            || str_contains($serviceNameText, 'bridal');
          $addonPackageName = trim((string)($item['addon_package_name'] ?? ''));
          $itemBookingType = $item['booking_type'] ?? 'fullday';
          $isPackageItem = ($item['item_type'] ?? '') === 'package';
          $itemMaxBooking = max(1, (int)($item['item_max_booking'] ?? 9999));
          $quantitySourceText = trim($categoryText . ' ' . $serviceNameText);
          $quantityLabel = $isPackageItem ? 'Guest Count' : 'Number Needed';
          if (str_contains($quantitySourceText, 'venue') || str_contains($quantitySourceText, 'catering') || $venueRoomName !== '') {
              $quantityLabel = 'Guest Count';
          } elseif (str_contains($quantitySourceText, 'bridal') || str_contains($quantitySourceText, 'makeup') || str_contains($quantitySourceText, 'make up') || str_contains($quantitySourceText, 'hair')) {
              $quantityLabel = 'People to Be Styled';
          } elseif (str_contains($quantitySourceText, 'media') || str_contains($quantitySourceText, 'photo') || str_contains($quantitySourceText, 'video')) {
              $quantityLabel = 'People Included';
          } elseif (str_contains($quantitySourceText, 'invitation') || str_contains($quantitySourceText, 'invite') || str_contains($quantitySourceText, 'stationery') || str_contains($quantitySourceText, 'stationary')) {
              $quantityLabel = 'Quantity Needed';
          }
        ?>

        <article class="gp-card gp-item-card" data-index="<?= $i + 1 ?>"
                 data-service-id="<?= (int)($item['item_id'] ?? 0) ?>"
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

          <div class="gp-card-status" data-status-for="<?= $i ?>">
            <span class="gp-card-status-pill is-empty">Not filled</span>
          </div>

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
                    <span class="venue-date-input-wrap" role="button" tabindex="0" aria-label="Open date calendar">
                      <i data-lucide="calendar-days" class="venue-date-icon"></i>
                      <span class="venue-date-display"><?= $h($slotDate !== '' ? date('M j, Y', strtotime($slotDate)) : 'Choose date') ?></span>
                      <i data-lucide="chevron-down" class="venue-date-chevron" size="13"></i>
                      <input class="gp-calendar-input gp-detail-input" type="date" id="slot-date-<?= $i ?>"
    	                         name="item_date[<?= $i ?>]" value="<?= $h($slotDate) ?>"
    	                         min="<?= $minDateStr ?>"
    	                         <?php if (($item['item_type'] ?? '') !== 'package'): ?>
    	                         data-service-id="<?= (int)($item['item_id'] ?? 0) ?>"
    	                         <?php endif; ?>
    	                         data-min-lead-days="<?= $minLeadDays ?>"
    	                         data-index="<?= $i ?>">
                    </span>
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
                <?php if ($isPackageItem): ?>
                  <?php
                    $packagePreferredTime = !empty($item['start_time'])
                        ? date('H:i', strtotime((string)$item['start_time']))
                        : '10:00';
                  ?>
                  <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div>
                      <label class="gp-detail-label" for="slot-date-<?= $i ?>">Select event date</label>
                      <span class="venue-date-input-wrap" role="button" tabindex="0" aria-label="Open date calendar">
                        <i data-lucide="calendar-days" class="venue-date-icon"></i>
                        <span class="venue-date-display">Choose date</span>
                        <i data-lucide="chevron-down" class="venue-date-chevron" size="13"></i>
                        <input class="gp-calendar-input gp-detail-input" type="date" id="slot-date-<?= $i ?>"
                               name="item_date[<?= $i ?>]"
                               min="<?= $minDateStr ?>"
                               data-min-lead-days="<?= $minLeadDays ?>"
                               data-index="<?= $i ?>" required>
                      </span>
                    </div>
                    <div>
                      <label class="gp-detail-label">Wedding time</label>
                      <span class="gp-time-control">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                        <span class="gp-time-display" data-time-display><?= $h(date('g:i A', strtotime($packagePreferredTime))) ?></span>
                        <svg class="gp-time-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                        <input class="gp-detail-input" type="time" id="preferred-time-<?= $i ?>"
                               name="preferred_time[<?= $i ?>]"
                               value="<?= $h($packagePreferredTime) ?>" required readonly tabindex="-1" aria-hidden="true">
                        <span class="gp-time-menu" data-time-menu></span>
                      </span>
                    </div>
                  </div>
                <?php else: ?>
                  <label class="gp-detail-label" for="slot-date-<?= $i ?>">Select date</label>
                  <span class="venue-date-input-wrap" role="button" tabindex="0" aria-label="Open date calendar">
                    <i data-lucide="calendar-days" class="venue-date-icon"></i>
                    <span class="venue-date-display">Choose date</span>
                    <i data-lucide="chevron-down" class="venue-date-chevron" size="13"></i>
                    <input class="gp-calendar-input gp-detail-input" type="date" id="slot-date-<?= $i ?>"
                           name="item_date[<?= $i ?>]"
                           min="<?= $minDateStr ?>"
                           data-service-id="<?= (int)($item['item_id'] ?? 0) ?>"
                           data-min-lead-days="<?= $minLeadDays ?>"
                           data-before-wedding="<?= $isBeforeWedding ? 'yes' : 'no' ?>"
                           data-index="<?= $i ?>" required>
                  </span>
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
                <?php else: ?>
                  <div style="display:grid;grid-template-columns:auto 1fr;gap:12px;align-items:end;margin-top:10px;">
                    <div>
                      <label class="gp-detail-label">Wedding time</label>
                      <span class="gp-time-control" style="width:min(140px,100%);">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                        <span class="gp-time-display" data-time-display>10:00 AM</span>
                        <svg class="gp-time-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                        <input class="gp-detail-input" type="time" id="preferred-time-<?= $i ?>"
                               name="preferred_time[<?= $i ?>]"
                               value="10:00"
                               data-slot-index="<?= $i ?>" readonly tabindex="-1" aria-hidden="true">
                        <span class="gp-time-menu" data-time-menu></span>
                      </span>
                    </div>
                    <div>
                      <label class="gp-detail-label" style="margin:0;">Time slots (auto-suggested<?= $isBeforeWedding ? ' before' : ' around' ?> wedding time)</label>
                      <div class="gp-input-note" style="margin-top:0;">Select a date to see available slots</div>
                    </div>
                  </div>
                  <div class="gp-slots-container" id="slots-<?= $i ?>" style="margin-top:10px;">
                    <p class="loading">Select a date to see available slots</p>
                  </div>
                <?php endif; ?>
              <?php endif; ?>
            </fieldset>

            <?php if ($isPackageItem): ?>
            <!-- Included Services + Your Details (package layout) -->
            <fieldset class="gp-slot-fieldset">
              <legend class="gp-fieldset-legend">Included services</legend>
              <div class="gp-package-schedule" id="package-schedule-<?= $i ?>" aria-live="polite" data-package-schedule-state="empty">
                <p class="loading">Loading the package timeline...</p>
              </div>
            </fieldset>

            <div class="gp-details-section">
              <div class="gp-fieldset-legend" style="margin-bottom:12px;">Your details</div>
              <div class="gp-details-grid">
                <?php $pkgGuestCount = max(1, (int)($item['package_guest_count'] ?? 0)); ?>
                <input type="hidden" name="item_guests[<?= $i ?>]" value="<?= $pkgGuestCount ?>">
                <div class="gp-detail-field" style="grid-column: 1 / -1;">
                  <label class="gp-detail-label is-required" for="location-<?= $i ?>">Location / venue room</label>
                  <input class="gp-detail-input" type="text" id="location-<?= $i ?>"
                         name="item_location[<?= $i ?>]"
                         value="<?= $h($venueLocation) ?>"
                         <?php if ($venueLocation !== ''): ?>data-venue-filled="true"<?php endif; ?>
                         placeholder="e.g. Ballroom A">
                </div>
                <div class="gp-detail-field">
                  <label class="gp-detail-label is-required" for="contact-name-<?= $i ?>">Contact name</label>
                  <input class="gp-detail-input" type="text" id="contact-name-<?= $i ?>"
                         name="item_contact_name[<?= $i ?>]"
                         value="<?= $h($user['name'] ?? '') ?>"
                         placeholder="Full name">
                </div>
                <div class="gp-detail-field">
                  <label class="gp-detail-label is-required" for="contact-phone-<?= $i ?>">Contact phone</label>
                  <input class="gp-detail-input" type="tel" id="contact-phone-<?= $i ?>"
                         name="item_contact_phone[<?= $i ?>]"
                         value="<?= $h($user['phone'] ?? '') ?>"
                         placeholder="09xxxxxxxxx"
                         inputmode="numeric" pattern="[0-9 ]{10,15}"
                         minlength="10" maxlength="15"
                         title="Phone number must be 10 to 11 digits.">
                </div>
                <div class="gp-detail-field" style="grid-column: 1 / -1;">
                  <label class="gp-detail-label" for="notes-<?= $i ?>">Special requests / notes</label>
                  <textarea class="gp-detail-textarea" id="notes-<?= $i ?>"
                            name="item_notes[<?= $i ?>]"
                            placeholder="Any notes for the whole package event..."
                            data-autogrow></textarea>
                </div>
              </div>
            </div>

            <?php else: ?>
            <!-- Service details drawer (non-package items) -->
            <details class="gp-service-drawer" open>
              <summary>
	                <span>Service details</span>
	                <span class="gp-drawer-hint">Required contact, guests, room and notes</span>
              </summary>

              <fieldset class="gp-overrides-fieldset">
                <div class="gp-detail-row">
                  <div class="gp-detail-field">
	                    <label class="gp-detail-label is-required" for="guests-<?= $i ?>"><?= $h($quantityLabel) ?></label>
                    <div class="gp-detail-stepper">
                      <button class="gp-stepper-btn" type="button" data-stepper="minus" data-target="guests-<?= $i ?>" aria-label="Decrease guests">−</button>
                      <input class="gp-detail-input gp-stepper-input" type="number" id="guests-<?= $i ?>"
                             name="item_guests[<?= $i ?>]" min="0" max="<?= $itemMaxBooking ?>"
                             data-max-booking="<?= $itemMaxBooking ?>"
                             value="<?= $isVenue && $venueRoomCapacity > 0 ? min((int)$venueRoomCapacity, $itemMaxBooking) : '' ?>"
                             <?php if ($isVenue && $venueRoomCapacity > 0): ?>data-venue-filled="true"<?php endif; ?>
                             placeholder="Required">
                      <button class="gp-stepper-btn" type="button" data-stepper="plus" data-target="guests-<?= $i ?>" aria-label="Increase guests">+</button>
                    </div>
                    <?php if ($isVenue && $venueRoomCapacity > 0): ?>
                      <div class="gp-input-note">Suggested from selected hall max: <strong><?= (int)$venueRoomCapacity ?></strong> guests</div>
                    <?php else: ?>
                      <div class="gp-input-note">Suggested maximum booking: <strong><?= $itemMaxBooking ?></strong></div>
                    <?php endif; ?>
                    <div class="gp-input-note is-limit-warning" data-limit-message-for="guests-<?= $i ?>">This supplier can accept up to <?= $itemMaxBooking ?> for this booking.</div>
                  </div>
                  <div class="gp-detail-field">
	                    <label class="gp-detail-label is-required" for="location-<?= $i ?>">Location / Venue room</label>
                    <input class="gp-detail-input" type="text" id="location-<?= $i ?>"
                           name="item_location[<?= $i ?>]"
                           value="<?= $h($venueLocation) ?>"
                           <?php if ($venueLocation !== ''): ?>data-venue-filled="true"<?php endif; ?>
                           placeholder="e.g. Ballroom A">
                  </div>
                </div>
                <div class="gp-detail-row" style="margin-top:12px;">
                  <div class="gp-detail-field">
                    <label class="gp-detail-label is-required" for="contact-name-<?= $i ?>">Contact person</label>
                    <input class="gp-detail-input" type="text" id="contact-name-<?= $i ?>"
                           name="item_contact_name[<?= $i ?>]"
                           value="<?= $h($user['name'] ?? '') ?>"
                           placeholder="Contact name">
                  </div>
                  <div class="gp-detail-field">
                    <label class="gp-detail-label is-required" for="contact-phone-<?= $i ?>">Contact phone</label>
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
	                              placeholder="Any specific requirements for this service..."
	                              data-autogrow></textarea>
                  </div>
                </div>
              </fieldset>
            </details>
            <?php endif; ?>

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
<div class="gp-calendar-popover" id="gpCalendarPopover" hidden></div>

<script>
const packageScheduleState = new Map();

(function () {
  const getQuantityMax = (input) => {
    const max = parseInt(input?.dataset?.maxBooking || input?.getAttribute('max') || '9999', 10);
    return Number.isFinite(max) && max > 0 ? max : 9999;
  };

  const updateQuantityLimitState = (input, showMessage = false) => {
    if (!input) return 0;
    const max = getQuantityMax(input);
    let value = parseInt(input.value || '0', 10);
    if (!Number.isFinite(value) || value < 0) value = 0;

    const wasAboveMax = value > max;
    if (wasAboveMax) {
      value = max;
      input.value = String(max);
    }

    const plusButton = document.querySelector(`[data-stepper="plus"][data-target="${input.id}"]`);
    if (plusButton) plusButton.disabled = value >= max;

    const limitMessage = document.querySelector(`[data-limit-message-for="${input.id}"]`);
    if (limitMessage) {
      limitMessage.textContent = 'This supplier can accept up to ' + max.toLocaleString('en-US') + ' for this booking.';
      limitMessage.classList.toggle('show', showMessage && wasAboveMax);
    }

    return value;
  };

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

    let modalDrafts = [];
    const modalDraftStorageKey = 'gpBookingDetailDrafts';
    try {
      modalDrafts = JSON.parse(sessionStorage.getItem(modalDraftStorageKey) || '[]');
    } catch (error) {
      modalDrafts = [];
    }
    if (Array.isArray(modalDrafts) && modalDrafts.length > 0) {
      const usedModalDraftServices = new Set();
      document.querySelectorAll('.gp-item-card[data-service-id]').forEach((card, index) => {
        const serviceId = String(card.dataset.serviceId || '');
        if (!serviceId || usedModalDraftServices.has(serviceId)) return;
        const draft = modalDrafts.find(item => String(item.serviceId || '') === serviceId);
        if (!draft || !draft.values) return;
        usedModalDraftServices.add(serviceId);

        const setDraftField = (selector, value) => {
          if (value === undefined || value === null || String(value).trim() === '') return;
          const field = card.querySelector(selector);
          if (!field) return;
          field.value = value;
          field.dispatchEvent(new Event('input', { bubbles: true }));
          field.dispatchEvent(new Event('change', { bubbles: true }));
        };

        setDraftField(`[name="item_guests[${index}]"]`, draft.values.guests);
        setDraftField(`[name="item_location[${index}]"]`, draft.values.location);
        setDraftField(`[name="item_contact_name[${index}]"]`, draft.values.contactName);
        setDraftField(`[name="item_contact_phone[${index}]"]`, draft.values.contactPhone);
        setDraftField(`[name="item_notes[${index}]"]`, draft.values.notes);
      });

      if (usedModalDraftServices.size > 0) {
        const remainingDrafts = modalDrafts.filter(item => !usedModalDraftServices.has(String(item.serviceId || '')));
        try {
          if (remainingDrafts.length) sessionStorage.setItem(modalDraftStorageKey, JSON.stringify(remainingDrafts));
          else sessionStorage.removeItem(modalDraftStorageKey);
        } catch (error) {
          // Optional draft cleanup only.
        }
      }
    }

    /* ─── Persist booking form drafts to sessionStorage ─── */
    const DRAFT_FIELD_PREFIXES = ['item_guests', 'item_location', 'item_contact_name', 'item_contact_phone', 'item_notes'];
    const DRAFT_VALUE_KEYS = ['guests', 'location', 'contactName', 'contactPhone', 'notes'];

    function saveBookingDrafts() {
      const cards = document.querySelectorAll('.gp-item-card[data-service-id]');
      if (!cards.length) return;
      const drafts = [];
      cards.forEach(function (card) {
        const serviceId = String(card.dataset.serviceId || '');
        if (!serviceId) return;
        const values = {};
        DRAFT_FIELD_PREFIXES.forEach(function (prefix, idx) {
          const field = card.querySelector('[name^="' + prefix + '["]');
          values[DRAFT_VALUE_KEYS[idx]] = field ? field.value : '';
        });
        drafts.push({ serviceId: serviceId, values: values });
      });
      try {
        if (drafts.length) sessionStorage.setItem(modalDraftStorageKey, JSON.stringify(drafts));
        else sessionStorage.removeItem(modalDraftStorageKey);
      } catch (e) {}
    }

    function isDraftField(name) {
      if (!name) return false;
      for (var k = 0; k < DRAFT_FIELD_PREFIXES.length; k++) {
        if (name.indexOf(DRAFT_FIELD_PREFIXES[k] + '[') === 0) return true;
      }
      return false;
    }

    var draftSaveTimer = null;
    function scheduleDraftSave() {
      if (draftSaveTimer) return;
      draftSaveTimer = setTimeout(function () {
        draftSaveTimer = null;
        saveBookingDrafts();
      }, 200);
    }

    var bookingForm = document.getElementById('booking-form');
    if (bookingForm) {
      bookingForm.addEventListener('input', function (e) {
        if (isDraftField(e.target.name)) scheduleDraftSave();
      });
      bookingForm.addEventListener('change', function (e) {
        if (isDraftField(e.target.name)) scheduleDraftSave();
      });
    }
    document.addEventListener('visibilitychange', function () {
      if (document.visibilityState === 'hidden') saveBookingDrafts();
    });
    window.addEventListener('beforeunload', saveBookingDrafts);

    saveBookingDrafts();

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
        if (field.matches('[name^="item_guests"]')) {
          updateQuantityLimitState(field);
        }
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
        updateQuantityLimitState(this, true);
      });
      field.addEventListener('change', function () {
        updateQuantityLimitState(this, true);
        syncFields(this, '[name^="item_guests"]');
        updateBookingPricing();
      });
      field.addEventListener('blur', function () {
        updateQuantityLimitState(this, true);
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
        updateQuantityLimitState(guestInput, true);
        guestInput.dataset.venueFilled = 'true';
      }
    });

    document.querySelectorAll('[name^="item_guests"]').forEach(input => updateQuantityLimitState(input));
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
      const max = getQuantityMax(target);
      val = this.dataset.stepper === 'plus' ? Math.min(max, val + 1) : Math.max(0, val - 1);
      target.value = val;
      updateQuantityLimitState(target, true);
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

  /* ─── Form element (needed by card status + submission) ── */
  const form = document.getElementById('booking-form');

  /* ─── Card completion statuses ──────────── */
  function updateCardStatuses() {
    document.querySelectorAll('.gp-item-card').forEach((card, index) => {
      const pill = card.querySelector('.gp-card-status-pill');
      if (!pill) return;
      const fd = new FormData(form);
      const itemDate = fieldValue(fd, 'item_date[' + index + ']');
      const itemPhone = fieldValue(fd, 'item_contact_phone[' + index + ']');
      const itemLocation = fieldValue(fd, 'item_location[' + index + ']');
      const itemGuests = numberValue(fd, 'item_guests[' + index + ']');
      const isComplete = itemDate && itemPhone && itemLocation && itemGuests > 0;
      pill.classList.toggle('is-done', isComplete);
      pill.classList.toggle('is-empty', !isComplete);
      pill.textContent = isComplete ? '✓ Complete' : 'Not filled';
    });
  }

  // Listen to all form input changes (debounced)
  let _statusTimer;
  form.addEventListener('input', function () {
    clearTimeout(_statusTimer);
    _statusTimer = setTimeout(updateCardStatuses, 200);
  });
  form.addEventListener('change', function () {
    clearTimeout(_statusTimer);
    _statusTimer = setTimeout(updateCardStatuses, 100);
  });

  // Initial call
  updateCardStatuses();

  // Attention pulse on first empty date field
  (function highlightFirstIncomplete() {
    const itemCards = document.querySelectorAll('.gp-item-card');
    for (const card of itemCards) {
      const idx = card.dataset.priceIndex;
      const dateInput = card.querySelector('[name="item_date[' + idx + ']"]');
      if (dateInput && !dateInput.value) {
        const delay = Math.max(0, parseInt(card.dataset.index || 0)) * 80 + 600;
        setTimeout(() => {
          dateInput.classList.add('gp-attention-hint');
          setTimeout(() => dateInput.classList.remove('gp-attention-hint'), 3200);
        }, delay);
        break;
      }
    }
  })();

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
      const guestInput = card.querySelector(`[name="item_guests[${index}]"]`);
      const itemMaxBooking = getQuantityMax(guestInput);
      const isPackageCard = Boolean(card.dataset.packageId);
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
      if (!itemContactName) {
        missing.push('contact name');
        rememberMissing(card.querySelector(`[name="item_contact_name[${index}]"]`));
      }
      if (!itemLocation) {
        missing.push('location');
        rememberMissing(card.querySelector(`[name="item_location[${index}]"]`));
      }
      if (itemGuests <= 0) {
        missing.push('guest count');
        rememberMissing(guestInput);
      } else if (!isPackageCard && itemGuests > itemMaxBooking) {
        missing.push('supplier limit: ' + itemMaxBooking);
        updateQuantityLimitState(guestInput, true);
        rememberMissing(guestInput);
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
          try { sessionStorage.removeItem('gpBookingDetailDrafts'); } catch (error) {}
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

const gpCalendar = document.getElementById('gpCalendarPopover');
let gpCalendarInput = null;
let gpCalendarMonth = null;

function parseDateValue(value) {
  if (!value) return null;
  const parts = String(value).split('-').map(Number);
  if (parts.length !== 3 || parts.some(Number.isNaN)) return null;
  return new Date(parts[0], parts[1] - 1, parts[2]);
}

function updateCalendarDisplay(input) {
  const display = input.closest('.venue-date-input-wrap')?.querySelector('.venue-date-display');
  if (!display) return;
  const parsed = parseDateValue(input.value);
  display.textContent = parsed ? formatDateForDisplay(parsed) : 'Choose date';
}

function positionCalendar(anchor) {
  if (!gpCalendar || !anchor) return;
  const rect = anchor.getBoundingClientRect();
  const width = Math.min(228, window.innerWidth - 32);
  const left = Math.max(16, Math.min(rect.left, window.innerWidth - width - 16));
  gpCalendar.style.width = width + 'px';
  gpCalendar.style.left = left + 'px';
  gpCalendar.style.top = (rect.bottom + 10) + 'px';
}

function renderCalendar() {
  if (!gpCalendar || !gpCalendarInput || !gpCalendarMonth) return;
  const monthStart = new Date(gpCalendarMonth.getFullYear(), gpCalendarMonth.getMonth(), 1);
  const selectedValue = gpCalendarInput.value;
  const todayValue = formatDateForInput(new Date());
  const minValue = gpCalendarInput.min || '';
  const maxValue = gpCalendarInput.max || '';
  const daysInMonth = new Date(monthStart.getFullYear(), monthStart.getMonth() + 1, 0).getDate();
  const leadingBlanks = monthStart.getDay();
  const monthTitle = monthStart.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
  const dayNames = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'];

  let html = '<div class="gp-calendar-head">' +
    '<button class="gp-calendar-nav" type="button" data-cal-prev aria-label="Previous month"><i data-lucide="chevron-left" size="16"></i></button>' +
    '<span>' + monthTitle + '</span>' +
    '<button class="gp-calendar-nav" type="button" data-cal-next aria-label="Next month"><i data-lucide="chevron-right" size="16"></i></button>' +
    '</div><div class="gp-calendar-grid">';

  dayNames.forEach(day => { html += '<div class="gp-calendar-day-name">' + day + '</div>'; });
  for (let i = 0; i < leadingBlanks; i++) html += '<span></span>';
  for (let day = 1; day <= daysInMonth; day++) {
    const value = formatDateForInput(new Date(monthStart.getFullYear(), monthStart.getMonth(), day));
    const disabled = (minValue && value < minValue) || (maxValue && value > maxValue);
    const classes = ['gp-calendar-day'];
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
  if (!gpCalendar || !input) return;
  gpCalendarInput = input;
  gpCalendarMonth = parseDateValue(input.value) || parseDateValue(input.min) || new Date();
  renderCalendar();
  gpCalendar.hidden = false;
  positionCalendar(input.closest('.venue-date-input-wrap') || input);
}

document.querySelectorAll('.gp-calendar-input').forEach(input => {
  updateCalendarDisplay(input);
  input.addEventListener('click', (event) => {
    event.preventDefault();
    event.stopPropagation();
    openCalendar(input);
  });
  input.addEventListener('focus', () => openCalendar(input));
});

document.querySelectorAll('.venue-date-input-wrap').forEach(wrap => {
  const input = wrap.querySelector('.gp-calendar-input');
  if (!input) return;
  wrap.addEventListener('click', (event) => {
    event.preventDefault();
    event.stopPropagation();
    openCalendar(input);
  });
  wrap.addEventListener('keydown', (event) => {
    if (event.key !== 'Enter' && event.key !== ' ') return;
    event.preventDefault();
    event.stopPropagation();
    openCalendar(input);
  });
});

gpCalendar?.addEventListener('click', (event) => {
  event.stopPropagation();
  const prev = event.target.closest('[data-cal-prev]');
  const next = event.target.closest('[data-cal-next]');
  const day = event.target.closest('[data-date]');
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
    gpCalendarInput.dispatchEvent(new Event('change', { bubbles: true }));
  }
});

gpCalendar?.addEventListener('mousedown', (event) => {
  event.preventDefault();
  event.stopPropagation();
});

document.addEventListener('click', (event) => {
  if (!gpCalendar || gpCalendar.hidden) return;
  if (event.target.closest('.gp-calendar-popover') || event.target.closest('.venue-date-input-wrap')) return;
  gpCalendar.hidden = true;
});

window.addEventListener('resize', () => {
  if (!gpCalendar?.hidden && gpCalendarInput) positionCalendar(gpCalendarInput.closest('.venue-date-input-wrap') || gpCalendarInput);
});
window.addEventListener('scroll', () => {
  if (gpCalendar && !gpCalendar.hidden) gpCalendar.hidden = true;
}, { passive: true });

/* ─── Fetch available slots ───────────────── */
async function loadSlots(serviceId, date, index, preferredTime) {
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
    // Determine booking context from the card's item type for correct pool filtering
    const card = document.querySelector(`.gp-item-card[data-price-index="${index}"]`);
    const context = card?.dataset.itemType === 'package' ? 'package' : '';
    const res = await fetch('<?= URLROOT ?>/booking/getAvailableSlots', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ service_id: serviceId, date: date, context: context })
    });
    const data = await res.json();
    if (!data.success) {
      container.innerHTML = '<p class="error">' + (data.error || 'Could not load slots') + '</p>';
      return;
    }
    if (data.slots && data.slots.length > 0) {
      // Smart select based on service category and preferred (wedding) time
      let bestIdx = 0;
      if (preferredTime) {
        const prefParts = preferredTime.split(':');
        const prefSec = parseInt(prefParts[0]) * 3600 + parseInt(prefParts[1]) * 60;
        const dateInput = document.querySelector(`input[type="date"][data-index="${index}"]`);
        const isBeforeWedding = dateInput?.dataset.beforeWedding === 'yes';

        if (isBeforeWedding) {
          // Makeup/Hair/Beauty: prefer the latest slot that starts at or before wedding time
          let bestDiff = Infinity;
          data.slots.forEach((slot, idx) => {
            const startStr = String(slot.start_time || '');
            const startParts = startStr.split(':');
            const startSec = parseInt(startParts[0]) * 3600 + parseInt(startParts[1]) * 60;
            const diff = prefSec - startSec;          // positive = slot is before wedding
            if (diff >= 0 && diff < bestDiff) {        // slot at or before preferred
              bestDiff = diff;
              bestIdx = idx;
            }
          });
        } else {
          // Photography/Videography/Other: prefer the closest slot to wedding time
          let bestScore = Infinity;
          data.slots.forEach((slot, idx) => {
            const startStr = String(slot.start_time || '');
            const startParts = startStr.split(':');
            const startSec = parseInt(startParts[0]) * 3600 + parseInt(startParts[1]) * 60;
            const diff = startSec - prefSec;
            let score = Math.abs(diff);
            if (diff < 0) score += 1800;     // small penalty for slots before wedding
            if (score < bestScore) {
              bestScore = score;
              bestIdx = idx;
            }
          });
        }
      }
      container.innerHTML = data.slots.map((slot, idx) => `
        <label class="gp-slot-option">
          <input type="radio" name="item_start_time[${index}]"
                 value="${slot.start_time}"
                 data-end-time="${slot.end_time}"
                 ${idx === bestIdx ? 'checked' : ''}>
          <input type="hidden" name="item_end_time[${index}]" class="end-time-hidden-${index}"
                 value="${slot.end_time}">
          <span>${slot.display}${(() => { const n = context === 'package' ? (slot.available_package ?? slot.available) : slot.available; return n > 0 ? ' · ' + n + ' available' : ''; })()}</span>
        </label>
      `).join('');
      // Sync end-time hidden field to match the pre-selected slot
      const checkedRadio = container.querySelector(`input[name="item_start_time[${index}"]:checked`);
      if (checkedRadio) {
        document.querySelector('.end-time-hidden-' + index).value = checkedRadio.dataset.endTime;
      }
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
  const preferredTimeInput = document.getElementById('preferred-time-' + index);
  const preferredTime = preferredTimeInput ? preferredTimeInput.value : '';
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
      body: JSON.stringify({ package_id: packageId, date, preferred_time: preferredTime })
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
      <div class="gp-package-timeline">
      ${schedule.map(item => {
        const isSlot = item.booking_type === 'slot';
        const isAvailable = !isSlot || Boolean(item.is_available);
        const status = isSlot
          ? (isAvailable ? packageEscapeHtml(item.availability_message || 'Available') : packageEscapeHtml(item.availability_message || 'Full'))
          : 'Managed';
        const quantity = Number(item.quantity || 0);
        const quantityType = String(item.quantity_type || '').toLowerCase();
        const quantityText = quantity > 0
          ? (quantityType === 'guests'
              ? quantity.toLocaleString('en-US') + ' guests'
              : (quantity > 1 ? 'Qty ' + quantity.toLocaleString('en-US') : 'Included'))
          : 'Included';
        return `
        <div class="gp-package-service-row ${isAvailable ? '' : 'is-full'}">
          <div class="gp-package-service-main">
            <div class="gp-package-service-name">${packageEscapeHtml(item.service_name || 'Package service')}</div>
            <div class="gp-package-service-meta">${packageTime(item.start_time)} – ${packageTime(item.end_time)} · ${packageEscapeHtml(item.supplier_name || 'Golden Promise')} · ${packageEscapeHtml(quantityText)}</div>
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

/* Helper: read wedding time for a given slot index */
function getPreferredTime(index) {
  const el = document.getElementById('preferred-time-' + index);
  return el ? el.value : '';
}

function formatPreferredTimeLabel(value) {
  const parts = String(value || '').split(':').map(Number);
  if (parts.length < 2 || parts.some(Number.isNaN)) return 'Choose time';
  const hours = parts[0];
  const minutes = parts[1];
  const period = hours >= 12 ? 'PM' : 'AM';
  const hour12 = hours % 12 || 12;
  return hour12 + ':' + String(minutes).padStart(2, '0') + ' ' + period;
}

function closeTimeMenus(exceptControl) {
  document.querySelectorAll('.gp-time-control.is-open').forEach(control => {
    if (control !== exceptControl) control.classList.remove('is-open');
  });
}

function buildTimeOptions(control, input) {
  const menu = control.querySelector('[data-time-menu]');
  if (!menu || menu.dataset.ready === '1') return;
  const values = [];
  for (let hour = 6; hour <= 23; hour++) {
    for (let minute = 0; minute < 60; minute += 30) {
      values.push(String(hour).padStart(2, '0') + ':' + String(minute).padStart(2, '0'));
    }
  }
  if (input.value && !values.includes(input.value)) values.unshift(input.value);
  menu.innerHTML = values.map(value => (
    '<button class="gp-time-option" type="button" data-time-value="' + value + '">' +
      formatPreferredTimeLabel(value) +
    '</button>'
  )).join('');
  menu.dataset.ready = '1';
}

document.querySelectorAll('.gp-time-control').forEach(control => {
  const input = control.querySelector('input[type="time"][name^="preferred_time"]');
  const display = control.querySelector('[data-time-display]');
  if (!input || !display) return;

  const syncDisplay = () => {
    display.textContent = formatPreferredTimeLabel(input.value);
    control.querySelectorAll('.gp-time-option').forEach(option => {
      option.classList.toggle('is-selected', option.dataset.timeValue === input.value);
    });
  };

  buildTimeOptions(control, input);
  syncDisplay();

  control.addEventListener('click', event => {
    const option = event.target.closest('[data-time-value]');
    if (option) {
      event.preventDefault();
      input.value = option.dataset.timeValue;
      syncDisplay();
      control.classList.remove('is-open');
      input.dispatchEvent(new Event('input', { bubbles: true }));
      input.dispatchEvent(new Event('change', { bubbles: true }));
      return;
    }
    if (event.target.closest('[data-time-menu]')) return;
    event.preventDefault();
    event.stopPropagation();
    const willOpen = !control.classList.contains('is-open');
    closeTimeMenus(control);
    control.classList.toggle('is-open', willOpen);
    syncDisplay();
  });

  control.addEventListener('keydown', event => {
    if (event.key !== 'Enter' && event.key !== ' ') return;
    event.preventDefault();
    closeTimeMenus(control);
    control.classList.toggle('is-open');
  });
});

document.addEventListener('click', event => {
  if (event.target.closest('.gp-time-control')) return;
  closeTimeMenus();
});

document.addEventListener('keydown', event => {
  if (event.key === 'Escape') closeTimeMenus();
});

document.querySelectorAll('[data-service-id]').forEach(input => {
  input.addEventListener('change', function() {
    const idx = this.dataset.index;
    const prefTime = getPreferredTime(idx);
    loadSlots(this.dataset.serviceId, this.value, idx, prefTime);

    // Propagate selected date to other unfilled individual services
    const pickedDate = this.value;
    if (!pickedDate) return;
    document.querySelectorAll('[data-service-id]').forEach(other => {
      if (other === this) return;
      if (other.value) return;             // already has a date
      const card = other.closest('.gp-item-card');
      if (card && card.dataset.packageId) return;  // skip package date inputs
      other.value = pickedDate;
      updateCalendarDisplay(other);
      const otherPrefTime = getPreferredTime(other.dataset.index);
      loadSlots(other.dataset.serviceId, pickedDate, other.dataset.index, otherPrefTime);
    });
  });
  if (input.value) {
    const prefTime = getPreferredTime(input.dataset.index);
    loadSlots(input.dataset.serviceId, input.value, input.dataset.index, prefTime);
  }
});

/* Re-load slots when wedding time changes (individual services only) */
document.querySelectorAll('[data-slot-index]').forEach(input => {
  input.addEventListener('change', function() {
    const idx = this.dataset.slotIndex;
    const dateInput = document.querySelector(`input[type="date"][data-index="${idx}"]`);
    const serviceId = dateInput?.dataset.serviceId;
    if (!dateInput || !dateInput.value || !serviceId) return;
    loadSlots(serviceId, dateInput.value, idx, this.value);
  });
});

document.querySelectorAll('.gp-item-card[data-package-id]').forEach(card => {
  const index = card.dataset.priceIndex;
  const dateInput = card.querySelector(`input[name="item_date[${index}]"]`);
  if (!dateInput) return;
  dateInput.addEventListener('change', () => {
    loadPackageSchedule(card.dataset.packageId, dateInput.value, index);
  });
  const prefTimeInput = document.getElementById('preferred-time-' + index);
  if (prefTimeInput) {
    prefTimeInput.addEventListener('change', () => {
      if (dateInput.value) loadPackageSchedule(card.dataset.packageId, dateInput.value, index);
    });
  }
  if (dateInput.value) {
    loadPackageSchedule(card.dataset.packageId, dateInput.value, index);
  }
});
</script>
<?php require APPROOT . '/views/layouts/customerFooter.php'; ?>
<?php include APPROOT . '/views/partials/cookie-consent.php'; ?>
</body>
</html>
