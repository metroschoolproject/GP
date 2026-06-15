<?php
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
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
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
textarea { font-family: var(--font-b); }

/* ─── Ambient orbs ───────────────────────── */
.gp-orbs {
  position: fixed; inset: 0; z-index: 0; pointer-events: none; overflow: hidden;
}
.gp-orb {
  position: absolute; border-radius: 50%;
  filter: blur(80px);
  opacity: 0;
  animation: orbFloat 20s ease-in-out infinite;
}
.gp-orb-1 { width: 600px; height: 600px; background: radial-gradient(circle, rgba(107,68,89,0.12) 0%, transparent 70%); top: -200px; right: -100px; animation-delay: 0s; animation-duration: 18s; }
.gp-orb-2 { width: 500px; height: 500px; background: radial-gradient(circle, rgba(184,146,74,0.08) 0%, transparent 70%); bottom: -150px; left: -100px; animation-delay: -9s; animation-duration: 22s; }
.gp-orb-3 { width: 350px; height: 350px; background: radial-gradient(circle, rgba(194,122,142,0.10) 0%, transparent 70%); top: 40%; left: 40%; animation-delay: -5s; animation-duration: 15s; }
@keyframes orbFloat {
  0%   { opacity: 0; transform: translate(0,0) scale(1); }
  15%  { opacity: 1; }
  50%  { transform: translate(40px,-30px) scale(1.08); }
  85%  { opacity: 1; }
  100% { opacity: 0; transform: translate(0,0) scale(1); }
}

/* ─── Header ─────────────────────────────── */
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
.gp-brand { display: flex; align-items: center; gap: 12px; font-size: 17px; font-weight: 800; color: var(--text); white-space: nowrap; }
.gp-brand-mark { position: relative; display: grid; place-items: center; width: 38px; height: 38px; border-radius: 50%; background: var(--plum); color: #fffaf3; font-size: 13px; font-weight: 700; letter-spacing: 1px; overflow: hidden; }
.gp-brand-mark::after { content: ''; position: absolute; inset: 0; border-radius: 50%; background: linear-gradient(135deg, rgba(255,255,255,0.18) 0%, transparent 60%); }
.gp-header-nav { display: flex; align-items: center; justify-content: center; gap: 2px; }
.gp-header-nav a { padding: 7px 16px; border-radius: 999px; font-size: 13px; font-weight: 600; color: var(--text2); transition: all 0.22s; }
.gp-header-nav a:hover { color: var(--plum); background: rgba(107,68,89,0.08); }
.gp-header-actions { display: flex; align-items: center; gap: 10px; }
.gp-cart-badge { display: inline-flex; align-items: center; gap: 6px; padding: 7px 13px 7px 9px; border-radius: 999px; border: 1px solid var(--rule-strong); background: rgba(255,255,255,0.7); color: var(--plum); font-size: 13px; font-weight: 700; backdrop-filter: blur(8px); transition: all 0.22s; }
.gp-cart-badge:hover { border-color: var(--plum); background: rgba(107,68,89,0.07); }
.gp-cart-count { display: inline-flex; align-items: center; justify-content: center; min-width: 20px; height: 20px; padding: 0 5px; border-radius: 999px; background: var(--plum); color: #fff; font-size: 10px; font-weight: 700; }
.gp-cta-header { display: inline-flex; align-items: center; gap: 6px; height: 38px; padding: 0 18px; border-radius: 999px; border: none; background: var(--plum); color: #fffaf3; font-size: 13px; font-weight: 700; box-shadow: 0 8px 24px rgba(107,68,89,0.22); transition: all 0.22s var(--ease-expo); }
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

/* ─── Page shell ─────────────────────────── */
.gp-page { position: relative; z-index: 1; flex: 1; padding: 52px var(--pad-x) 80px; max-width: 1180px; margin: 0 auto; width: 100%; }
.gp-page-head { margin-bottom: 44px; opacity: 0; animation: fadeUp 0.7s var(--ease-expo) 0.1s forwards; }
.gp-page-eyebrow { display: flex; align-items: center; gap: 8px; font-size: 11px; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; color: var(--gold); margin-bottom: 10px; }
.gp-page-eyebrow::before { content: ''; display: block; width: 24px; height: 1px; background: var(--gold); }
.gp-page-title { font-family: var(--font-d); font-size: clamp(38px, 5vw, 58px); font-weight: 600; color: var(--text); line-height: 0.92; letter-spacing: -0.02em; }
.gp-page-title em { font-style: italic; color: var(--plum-lt); }
.gp-page-subtitle { font-size: 14px; color: var(--muted); margin-top: 14px; max-width: 480px; }

/* ─── Two-column layout ──────────────────── */
.gp-layout { display: grid; grid-template-columns: 1fr 360px; gap: 28px; align-items: start; }

/* ─── Service item cards ─────────────────── */
.gp-items { display: flex; flex-direction: column; gap: 16px; }
.gp-item-card {
  background: var(--card);
  border: 1px solid var(--rule);
  border-radius: var(--r-lg);
  overflow: hidden;
  opacity: 0;
  transform: translateY(24px);
  transition: box-shadow 0.35s var(--ease-expo), border-color 0.25s;
}
.gp-item-card.visible { opacity: 1; transform: translateY(0); }

.gp-item-header {
  display: grid;
  grid-template-columns: 100px 1fr auto;
  gap: 0;
  border-bottom: 1px solid var(--rule);
}
.gp-item-thumb {
  position: relative; overflow: hidden;
  width: 100px; min-height: 100px;
  background: linear-gradient(160deg, #e8d9c8, #d9c8b5);
  flex-shrink: 0;
}
.gp-item-thumb img { width: 100%; height: 100%; object-fit: cover; }
.gp-item-thumb-placeholder { width: 100%; height: 100%; display: grid; place-items: center; color: var(--muted); min-height: 100px; }
.gp-item-info { padding: 14px 14px 14px 18px; display: flex; flex-direction: column; gap: 2px; min-width: 0; }
.gp-item-name { font-family: var(--font-d); font-size: 18px; font-weight: 600; color: var(--text); line-height: 1.1; }
.gp-item-supplier { display: flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 500; color: var(--plum-lt); }
.gp-item-price-box { display: flex; flex-direction: column; align-items: flex-end; justify-content: center; padding: 14px 16px 14px 8px; flex-shrink: 0; }
.gp-item-price-val { font-family: var(--font-d); font-size: 20px; font-weight: 600; color: var(--plum); white-space: nowrap; }
.gp-schedule-note { display: flex; flex-wrap: wrap; align-items: center; gap: 6px; margin-top: 8px; font-size: 11px; color: var(--muted); }
.gp-schedule-pill { display: inline-flex; align-items: center; gap: 5px; min-height: 24px; padding: 4px 8px; border-radius: 999px; border: 1px solid var(--rule); background: rgba(250,246,241,0.9); color: var(--text2); font-weight: 600; }
.gp-schedule-pill.fixed { border-color: rgba(42,122,75,0.24); background: rgba(42,122,75,0.08); color: #24613d; }
.gp-schedule-pill.shared { border-color: rgba(184,146,74,0.28); background: rgba(184,146,74,0.10); color: #7a5c22; }
.gp-schedule-pill svg { width: 12px; height: 12px; flex-shrink: 0; }

/* Item details section */
.gp-item-details { padding: 16px 18px; display: flex; flex-direction: column; gap: 14px; }
.gp-event-card { background: var(--card); border: 1px solid var(--rule); border-radius: var(--r-lg); padding: 22px; opacity: 0; transform: translateY(24px); transition: box-shadow 0.35s var(--ease-expo), border-color 0.25s; }
.gp-event-card.visible { opacity: 1; transform: translateY(0); }
.gp-event-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 18px; margin-bottom: 18px; }
.gp-event-kicker { font-size: 10px; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; color: var(--gold); }
.gp-event-title { margin-top: 2px; font-family: var(--font-d); font-size: 24px; font-weight: 600; line-height: 1.05; color: var(--text); }
.gp-event-copy { max-width: 360px; font-size: 12px; color: var(--muted); }
.gp-detail-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.gp-detail-field { display: flex; flex-direction: column; gap: 4px; }
.gp-detail-field.full { grid-column: 1 / -1; }
.gp-detail-label { font-size: 10px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: var(--muted); }
.gp-detail-input, .gp-detail-textarea, .gp-detail-select {
  padding: 9px 12px; border: 1px solid var(--rule-strong); border-radius: var(--r-sm);
  font-size: 13px; color: var(--text); background: var(--surface);
  transition: border-color 0.2s;
  width: 100%;
}
.gp-detail-input:focus, .gp-detail-textarea:focus, .gp-detail-select:focus { outline: none; border-color: var(--plum); box-shadow: 0 0 0 3px rgba(107,68,89,0.1); }
.gp-detail-input.error, .gp-detail-textarea.error { border-color: var(--danger); }
.gp-detail-textarea { min-height: 70px; resize: vertical; }
.gp-detail-stepper { display: flex; align-items: center; gap: 0; }
.gp-stepper-btn { width: 36px; height: 36px; display: grid; place-items: center; border: 1px solid var(--rule-strong); background: var(--card); color: var(--text); font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.15s; }
.gp-stepper-btn:hover { background: var(--plum); color: #fff; border-color: var(--plum); }
.gp-stepper-btn:first-child { border-radius: var(--r-sm) 0 0 var(--r-sm); }
.gp-stepper-btn:last-child { border-radius: 0 var(--r-sm) var(--r-sm) 0; }
.gp-stepper-input { width: 56px; height: 36px; text-align: center; border: 1px solid var(--rule-strong); border-left: none; border-right: none; font-size: 14px; font-weight: 600; color: var(--text); background: var(--surface); }
.gp-stepper-input::-webkit-inner-spin-button, .gp-stepper-input::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
.gp-stepper-input[type=number] { -moz-appearance: textfield; }

/* ─── Sidebar ────────────────────────────── */
.gp-sidebar { position: sticky; top: 84px; opacity: 0; animation: fadeUp 0.7s var(--ease-expo) 0.35s forwards; }
.gp-summary-card { background: var(--card); border: 1px solid var(--rule); border-radius: var(--r-xl); overflow: hidden; box-shadow: 0 20px 60px rgba(26,17,24,0.08); }
.gp-summary-head { padding: 24px 24px 20px; border-bottom: 1px solid var(--rule); position: relative; overflow: hidden; }
.gp-summary-head::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, var(--plum) 0%, var(--rose) 50%, var(--gold) 100%); }
.gp-summary-label { font-size: 10px; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; color: var(--muted); margin-bottom: 4px; }
.gp-summary-title { font-family: var(--font-d); font-size: 22px; font-weight: 600; color: var(--text); }
.gp-summary-subtitle { font-size: 12px; color: var(--muted); margin-top: 2px; }
.gp-summary-body { padding: 20px 24px; }
.gp-line-items { display: flex; flex-direction: column; gap: 12px; margin-bottom: 20px; }
.gp-line { display: flex; justify-content: space-between; align-items: baseline; gap: 12px; }
.gp-line-name { font-size: 13px; color: var(--text2); font-weight: 400; flex: 1; min-width: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.gp-line-dots { flex: 1; border-bottom: 1px dashed var(--rule-strong); margin: 0 6px 3px; }
.gp-line-val { font-size: 13px; color: var(--text); font-weight: 500; white-space: nowrap; }
.gp-line-divider { height: 1px; background: var(--rule); margin: 4px 0; }
.gp-total-row { display: flex; justify-content: space-between; align-items: baseline; padding: 16px 0 0; border-top: 1px solid var(--rule-strong); }
.gp-total-label { font-size: 13px; font-weight: 600; color: var(--text2); }
.gp-total-amount { font-family: var(--font-d); font-size: 30px; font-weight: 600; color: var(--plum); line-height: 1; }

.gp-deposit-breakdown { margin-top: 12px; padding-top: 12px; border-top: 1px solid var(--rule); display: flex; flex-direction: column; gap: 6px; }
.gp-deposit-line { display: flex; justify-content: space-between; align-items: baseline; font-size: 12px; color: var(--muted); }
.gp-deposit-line strong { color: var(--text); font-size: 13px; }

.gp-summary-footer { padding: 0 24px 24px; display: flex; flex-direction: column; gap: 10px; }
.gp-btn-primary {
  display: flex; align-items: center; justify-content: center; gap: 8px;
  height: 52px; border-radius: var(--r-md); border: none;
  background: var(--plum); color: #fffaf3;
  font-size: 14px; font-weight: 700;
  letter-spacing: 0.02em;
  box-shadow: 0 10px 28px rgba(107,68,89,0.28);
  transition: all 0.3s var(--ease-expo);
  position: relative; overflow: hidden;
}
.gp-btn-primary::before { content: ''; position: absolute; top: 0; left: -100%; width: 100%; height: 100%; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.12), transparent); transition: left 0.5s var(--ease-expo); }
.gp-btn-primary:hover { background: var(--plum-dk); transform: translateY(-2px); box-shadow: 0 18px 40px rgba(107,68,89,0.32); }
.gp-btn-primary:hover::before { left: 100%; }
.gp-btn-primary:active { transform: translateY(0); }
.gp-btn-primary:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

.gp-btn-secondary {
  display: flex; align-items: center; justify-content: center; gap: 6px;
  height: 42px; border-radius: var(--r-md);
  border: 1px solid var(--rule-strong);
  background: transparent; color: var(--text2);
  font-size: 13px; font-weight: 600;
  transition: all 0.22s;
}
.gp-btn-secondary:hover { border-color: var(--plum); color: var(--plum); background: rgba(107,68,89,0.05); }

.gp-trust { display: flex; flex-direction: column; gap: 8px; padding: 16px 24px; border-top: 1px solid var(--rule); }
.gp-trust-item { display: flex; align-items: center; gap: 8px; font-size: 11px; color: var(--muted); }
.gp-trust-icon { width: 16px; height: 16px; flex-shrink: 0; color: var(--gold); }

/* ─── Spinner ────────────────────────────── */
.gp-spinner { display: inline-block; width: 18px; height: 18px; border: 2px solid rgba(255,255,255,0.3); border-top-color: #fff; border-radius: 50%; animation: spin 0.6s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* ─── Toast ──────────────────────────────── */
.gp-toast { position: fixed; top: 20px; right: 20px; z-index: 999; padding: 14px 20px; border-radius: var(--r-md); box-shadow: 0 12px 40px rgba(0,0,0,0.12); font-size: 13px; font-weight: 500; max-width: 380px; opacity: 0; transform: translateY(-12px); transition: all 0.35s var(--ease-expo); pointer-events: none; }
.gp-toast.show { opacity: 1; transform: translateY(0); pointer-events: auto; }
.gp-toast.error { background: #fef2f2; border: 1px solid #fecaca; color: var(--danger); }
.gp-toast.success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; }

/* ─── Keyframes ──────────────────────────── */
@keyframes fadeUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

.gp-footer { position: relative; z-index: 1; padding: 24px var(--pad-x); border-top: 1px solid var(--rule); display: flex; align-items: center; justify-content: space-between; gap: 16px; font-size: 12px; color: var(--muted); }

@media (max-width: 900px) {
  .gp-layout { grid-template-columns: 1fr; }
  .gp-sidebar { position: static; }
  .gp-detail-row { grid-template-columns: 1fr; }
  .gp-event-head { flex-direction: column; }
}
@media (max-width: 640px) {
  .gp-item-header { grid-template-columns: 80px 1fr; }
  .gp-item-price-box { display: none; }
  .gp-header-nav { display: none; }
  :root { --pad-x: 16px; }
}
@media (prefers-reduced-motion: reduce) {
  .gp-item-card, .gp-event-card, .gp-page-head, .gp-sidebar { animation: none; opacity: 1; transform: none; }
  .gp-orb { animation: none; }
}
</style>
</head>
<body>

<div class="gp-orbs" aria-hidden="true">
  <div class="gp-orb gp-orb-1"></div>
  <div class="gp-orb gp-orb-2"></div>
  <div class="gp-orb gp-orb-3"></div>
</div>

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

<main class="gp-page">

  <div class="gp-page-head">
    <div class="gp-page-eyebrow">Almost There</div>
    <h1 class="gp-page-title">Confirm Your <em>Booking</em></h1>
    <p class="gp-page-subtitle">Review your selections and add any details your suppliers need.</p>
  </div>

  <form id="booking-form" method="POST" action="<?= URLROOT ?>/booking/createPost">
    <div class="gp-layout">

      <!-- LEFT: Items -->
      <div class="gp-items" id="gp-items">
        <section class="gp-event-card" data-index="0" aria-labelledby="event-details-title">
          <div class="gp-event-head">
            <div>
              <div class="gp-event-kicker">Event details</div>
              <h2 class="gp-event-title" id="event-details-title">Tell suppliers once</h2>
            </div>
            <p class="gp-event-copy">These details fill services without a fixed date or slot. Chosen slots stay unchanged.</p>
          </div>

          <div class="gp-item-details">
            <div class="gp-detail-row">
              <div class="gp-detail-field">
                <label class="gp-detail-label" for="event-date">Event date</label>
                <input class="gp-detail-input" type="date" id="event-date" name="event_date"
                       value="<?= $h($defaultDate) ?>">
              </div>
              <div class="gp-detail-field">
                <label class="gp-detail-label" for="contact-phone">Contact phone</label>
                <input class="gp-detail-input" type="tel" id="contact-phone" name="contact_phone"
                       placeholder="+60 12-345 6789" value="<?= $h($user['phone']) ?>">
              </div>
            </div>

            <div class="gp-detail-row">
              <div class="gp-detail-field">
                <label class="gp-detail-label" for="event-start-time">Start time</label>
                <input class="gp-detail-input" type="time" id="event-start-time" name="event_start_time"
                       value="<?= $h($defaultStartTime) ?>">
              </div>
              <div class="gp-detail-field">
                <label class="gp-detail-label" for="event-end-time">End time</label>
                <input class="gp-detail-input" type="time" id="event-end-time" name="event_end_time"
                       value="<?= $h($defaultEndTime) ?>">
              </div>
            </div>

            <div class="gp-detail-row">
              <div class="gp-detail-field">
                <label class="gp-detail-label">Number of guests</label>
                <div class="gp-detail-stepper">
                  <button type="button" class="gp-stepper-btn" data-stepper="minus" data-target="guest-count" aria-label="Decrease guests">-</button>
                  <input class="gp-stepper-input" type="number" id="guest-count" name="guest_count"
                         value="0" min="0" max="9999" readonly>
                  <button type="button" class="gp-stepper-btn" data-stepper="plus" data-target="guest-count" aria-label="Increase guests">+</button>
                </div>
              </div>
              <div class="gp-detail-field">
                <label class="gp-detail-label" for="event-location">Event venue / location</label>
                <input class="gp-detail-input" type="text" id="event-location" name="event_location"
                       placeholder="e.g. The Grand Ballroom" value="">
              </div>
            </div>
          </div>
        </section>

        <?php foreach ($items as $i => $item):
          $itemId      = (int)($item['cart_item_id'] ?? 0);
          $name        = $item['service_name'] ?? 'Service';
          $supplier    = $item['supplier_name'] ?? 'Supplier';
          $category    = $item['category_name'] ?? 'Service';
          $img         = trim($item['thumbnail_url'] ?? '');
          $price       = (float)($item['cart_price'] ?? $item['price_min'] ?? $item['price_max'] ?? 0);
          $bookingType = $item['booking_type'] ?? 'fullday';
          $selectedDate = trim((string)($item['selected_date'] ?? ''));
          $startTime = trim((string)($item['start_time'] ?? ''));
          $endTime = trim((string)($item['end_time'] ?? ''));
          $hasFixedSchedule = $selectedDate !== '' || $startTime !== '' || $endTime !== '';
          $scheduleDateLabel = $selectedDate !== '' ? $formatDate($selectedDate) : '';
          $scheduleTimeLabel = $formatTimeRange($startTime, $endTime);
          $scheduleTypeLabel = $bookingType === 'slot'
            ? 'Fixed slot'
            : ($bookingType === 'flexible' ? 'Flexible schedule' : 'Full-day date');
          $detailUrl   = URLROOT . '/customerServices/detail/' . (int)($item['item_id'] ?? 0);
        ?>
        <article class="gp-item-card" data-index="<?= $i + 1 ?>">

          <div class="gp-item-header">
            <a class="gp-item-thumb" href="<?= $h($detailUrl) ?>" tabindex="-1" aria-hidden="true">
              <?php if ($img): ?>
                <img src="<?= $h($img) ?>" alt="<?= $h($name) ?>" loading="lazy">
              <?php else: ?>
                <div class="gp-item-thumb-placeholder">
                  <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                </div>
              <?php endif; ?>
            </a>
            <div class="gp-item-info">
              <h2 class="gp-item-name"><?= $h($name) ?></h2>
              <div class="gp-item-supplier">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                <?= $h($supplier) ?>
              </div>
              <div class="gp-schedule-note">
                <?php if ($hasFixedSchedule): ?>
                  <span class="gp-schedule-pill fixed">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                    <?= $h($scheduleTypeLabel) ?>
                  </span>
                  <?php if ($scheduleDateLabel !== ''): ?><span><?= $h($scheduleDateLabel) ?></span><?php endif; ?>
                  <?php if ($scheduleTimeLabel !== ''): ?><span><?= $h($scheduleTimeLabel) ?></span><?php endif; ?>
                <?php else: ?>
                  <span class="gp-schedule-pill shared">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                    Uses shared event details
                  </span>
                <?php endif; ?>
              </div>
            </div>
            <div class="gp-item-price-box">
              <div class="gp-item-price-val"><?= $money($price) ?></div>
            </div>
          </div>

          <div class="gp-item-details">
            <div class="gp-detail-field full">
              <label class="gp-detail-label" for="notes-<?= $i ?>">Special requests / notes</label>
              <textarea class="gp-detail-textarea" id="notes-<?= $i ?>" name="item_notes[<?= $i ?>]"
                        placeholder="Optional details for this service only…" data-autogrow></textarea>
            </div>

            <input type="hidden" name="item_name[<?= $i ?>]" value="<?= $h($name) ?>">
          </div>

        </article>
        <?php endforeach; ?>
      </div>

      <!-- RIGHT: Summary sidebar -->
      <aside class="gp-sidebar" aria-label="Order summary">
        <div class="gp-summary-card">
          <div class="gp-summary-head">
            <div class="gp-summary-label">Order summary</div>
            <div class="gp-summary-title">Your selection</div>
            <div class="gp-summary-subtitle"><?= $cartCount ?> service<?= $cartCount === 1 ? '' : 's' ?> selected</div>
          </div>

          <div class="gp-summary-body">
            <div class="gp-line-items">
              <?php foreach ($items as $item):
                $linePrice = (float)($item['cart_price'] ?? $item['price_min'] ?? $item['price_max'] ?? 0);
                $lineName  = $item['service_name'] ?? 'Service';
              ?>
              <div class="gp-line">
                <span class="gp-line-name" title="<?= $h($lineName) ?>"><?= $h($lineName) ?></span>
                <span class="gp-line-dots" aria-hidden="true"></span>
                <span class="gp-line-val"><?= $money($linePrice) ?></span>
              </div>
              <?php endforeach; ?>

              <div class="gp-line-divider"></div>

              <div class="gp-line">
                <span class="gp-line-name">Subtotal</span>
                <span class="gp-line-dots" aria-hidden="true"></span>
                <span class="gp-line-val"><?= $money($total) ?></span>
              </div>
            </div>

            <div class="gp-total-row">
              <span class="gp-total-label">Estimated total</span>
              <span class="gp-total-amount"><?= $money($total) ?></span>
            </div>

            <div class="gp-deposit-breakdown">
              <div class="gp-deposit-line">
                <span>Deposit (<?= $depositPercent ?>%)</span>
                <strong><?= $money($total * $depositPercent / 100) ?></strong>
              </div>
              <div class="gp-deposit-line">
                <span>Balance due</span>
                <strong><?= $money($total - ($total * $depositPercent / 100)) ?></strong>
              </div>
            </div>
          </div>

          <div class="gp-summary-footer">
            <button class="gp-btn-primary" type="submit" id="submit-btn">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
              Confirm &amp; Proceed
              <span class="gp-btn-arrow">→</span>
            </button>
            <a class="gp-btn-secondary" href="<?= URLROOT ?>/cart">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
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
              10% deposit locks your date, balance due later
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
  <span>Your curated wedding service collection</span>
</footer>

<div class="gp-toast" id="gp-toast" role="alert"></div>

<script>
(function () {
  /* Staggered card reveal */
  const cards = document.querySelectorAll('.gp-event-card, .gp-item-card');
  if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const el = entry.target;
          const delay = parseInt(el.dataset.index || 0) * 100;
          setTimeout(() => el.classList.add('visible'), delay);
          observer.unobserve(el);
        }
      });
    }, { threshold: 0.08 });
    cards.forEach(el => observer.observe(el));
  } else {
    cards.forEach(el => el.classList.add('visible'));
  }

  /* Stepper buttons */
  document.querySelectorAll('[data-stepper]').forEach(btn => {
    btn.addEventListener('click', function () {
      const target = document.getElementById(this.dataset.target);
      if (!target) return;
      let val = parseInt(target.value) || 0;
      if (this.dataset.stepper === 'plus') {
        val = Math.min(9999, val + 1);
      } else {
        val = Math.max(0, val - 1);
      }
      target.value = val;
    });
  });

  /* Auto-grow textareas */
  document.querySelectorAll('[data-autogrow]').forEach(ta => {
    ta.addEventListener('input', function () {
      this.style.height = 'auto';
      this.style.height = (this.scrollHeight) + 'px';
    });
  });

  /* Toast */
  const toast = document.getElementById('gp-toast');
  function showToast(msg, type) {
    toast.textContent = msg;
    toast.className = 'gp-toast ' + type + ' show';
    setTimeout(() => toast.classList.remove('show'), 5000);
  }

  /* Form submission */
  const form = document.getElementById('booking-form');
  const submitBtn = document.getElementById('submit-btn');

  form.addEventListener('submit', function (e) {
    e.preventDefault();

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="gp-spinner"></span> Creating booking…';

    const formData = new FormData(form);

    fetch(form.action, {
      method: 'POST',
      body: formData,
    })
    .then(r => r.json())
    .then(data => {
      if (data.success && data.redirect) {
        window.location.href = data.redirect;
      } else {
        showToast(data.error || 'Something went wrong. Please try again.', 'error');
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Confirm &amp; Proceed <span class="gp-btn-arrow">→</span>';
      }
    })
    .catch(() => {
      showToast('Something went wrong. Please try again.', 'error');
      submitBtn.disabled = false;
      submitBtn.innerHTML = 'Confirm &amp; Proceed <span class="gp-btn-arrow">→</span>';
    });
  });

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
