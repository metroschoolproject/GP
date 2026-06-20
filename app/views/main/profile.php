<?php
/**
 * CUSTOMER PROFILE — LAYOUT A: "Boutique"
 *
 * Inspired by the packages page boutique search panel +
 * boutique divider + category cards.
 * Centered, narrow, editorial — feels like a luxury
 * wedding boutique profile card.
 */

$userName    = $name       ?? $_SESSION['session_name'] ?? 'Customer';
$firstName   = $first_name ?? '';
$lastName    = $last_name  ?? '';
$userEmail   = $email      ?? $_SESSION['session_email'] ?? '';
$userPhone   = $phone      ?? '';
$userJoined  = $joined     ?? '-';
$userLastLogin = $lastLogin  ?? $last_login ?? '-';
$userIsOauth = $isOauth    ?? false;
$userHasPw   = $hasPassword ?? true;
$profileAvatar = $avatar     ?? $_SESSION['session_avatar'] ?? null;
$initials   = strtoupper(substr(trim($userName), 0, 1));
$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Profile — Golden Promise</title>
<?php $v = file_exists(APPROOT . '/../public/css/app.css') ? filemtime(APPROOT . '/../public/css/app.css') : time(); ?>
<link rel="stylesheet" href="<?= URLROOT ?>/public/css/app.css?v=<?= $v ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400;1,600&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root {
  --c-bg: #f5e8d9; --c-surface: #faf5ef; --c-white: #ffffff;
  --c-card: #faf5ef; --c-rule: #ead8c7; --c-strong: #6d4c5b;
  --c-accent: #7b5c69; --c-muted: #b79c8b; --c-text: #111827;
  --c-danger: #b94b4b; --c-pale: #b79c8b; --c-gold: #d4a047;
  --c-gold-light: rgba(212,160,71,0.12);
  --r-card: 0.75rem; --sh-card: 0 20px 40px rgba(15,23,42,0.08);
  --sh-panel: 0 18px 45px rgba(15,23,42,0.06);
  --font-display: 'Playfair Display', Georgia, serif;
  --font-body: 'Poppins', system-ui, sans-serif;
  --pad-x: clamp(20px, 5vw, 72px);
  --ease: cubic-bezier(0.19, 1, 0.22, 1);
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body { background: var(--c-bg); color: var(--c-text); font-family: var(--font-body); font-size: 14px; line-height: 1.6; -webkit-font-smoothing: antialiased; }
a { color: inherit; text-decoration: none; }
img { display: block; max-width: 100%; }
button { font-family: var(--font-body); cursor: pointer; }

/* ══ TEXTURE ═══════════════════════════════════ */
.gp-texture { position: fixed; inset: 0; z-index: -1; pointer-events: none;
  background-image: radial-gradient(ellipse at 20% 8%, rgba(109,76,91,0.04) 0%, transparent 60%), radial-gradient(ellipse at 80% 92%, rgba(183,156,139,0.07) 0%, transparent 55%); }

/* ══ HEADER ════════════════════════════════════ */
.gp-header { position: sticky; top: 0; z-index: 50; display: grid; grid-template-columns: auto 1fr auto; align-items: center; gap: 24px; padding: 16px var(--pad-x); border-bottom: 1px solid rgba(184,154,109,0.25); background: rgba(248,245,239,0.90); backdrop-filter: blur(18px); }
.gp-brand { display: flex; align-items: center; gap: 12px; color: #211b17; font-size: 18px; font-weight: 800; }
.gp-brand-mark { display: grid; place-items: center; width: 40px; height: 40px; border-radius: 50%; background: var(--c-strong); color: #fffaf3; font-size: 14px; letter-spacing: 1px; }
.gp-header-nav { display: flex; align-items: center; justify-content: center; gap: 4px; }
.gp-header-nav a { padding: 8px 18px; border-radius: 999px; font-size: 13px; font-weight: 700; color: #51483f; transition: all 0.2s; }
.gp-header-nav a:hover, .gp-header-nav a.active { color: var(--c-strong); background: rgba(109,76,91,0.08); }
.gp-header-actions { display: flex; align-items: center; gap: 12px; }
.gp-profile-dropdown { position: relative; }
.gp-profile-btn { display: flex; align-items: center; gap: 8px; padding: 4px 12px 4px 4px; border-radius: 999px; border: 1px solid var(--c-rule); background: var(--c-white); cursor: pointer; transition: all 0.2s; color: var(--c-strong); font-family: var(--font-body); font-size: 13px; font-weight: 600; }
.gp-profile-btn:hover { border-color: var(--c-strong); background: rgba(109,76,91,0.06); }
.gp-profile-avatar { display: grid; place-items: center; width: 32px; height: 32px; border-radius: 50%; background: var(--c-strong); color: #fffaf3; font-size: 12px; font-weight: 800; letter-spacing: 0.5px; overflow: hidden; }
.gp-profile-avatar img { width: 100%; height: 100%; object-fit: cover; }
.gp-profile-name { white-space: nowrap; max-width: 100px; overflow: hidden; text-overflow: ellipsis; }
.gp-chevron { opacity: 0.6; transition: transform 0.2s; }
.gp-profile-btn[aria-expanded="true"] .gp-chevron { transform: rotate(180deg); }
.gp-profile-menu { position: absolute; top: calc(100% + 8px); right: 0; min-width: 180px; padding: 6px; border-radius: 12px; border: 1px solid var(--c-rule); background: var(--c-white); box-shadow: 0 12px 35px rgba(15,23,42,0.1); opacity: 0; visibility: hidden; transform: translateY(-4px); transition: all 0.15s var(--ease); }
.gp-profile-btn[aria-expanded="true"] + .gp-profile-menu { opacity: 1; visibility: visible; transform: translateY(0); }
.gp-menu-item { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 8px; font-size: 13px; font-weight: 600; color: var(--c-text); transition: background 0.15s; }
.gp-menu-item:hover { background: rgba(109,76,91,0.06); }
.gp-menu-item--danger { color: var(--c-danger); }
.gp-menu-item--danger:hover { background: rgba(185,75,75,0.08); }

/* ══ BOUTIQUE HERO ═══════════════════════════ */
.gp-boutique-hero { padding: 72px var(--pad-x) 48px; text-align: center; }
.gp-boutique-hero-overline { display: inline-flex; align-items: center; gap: 14px; font-size: 12px; font-weight: 800; letter-spacing: 0.16em; text-transform: uppercase; color: var(--c-gold); margin-bottom: 16px; }
.gp-boutique-hero-overline::before, .gp-boutique-hero-overline::after { content: ''; display: block; width: 28px; height: 1.5px; background: var(--c-gold); }
.gp-boutique-hero h1 { font-family: var(--font-display); font-size: clamp(42px, 5vw, 68px); font-weight: 600; line-height: 0.92; color: var(--c-text); letter-spacing: -0.02em; }
.gp-boutique-hero h1 em { font-style: italic; color: var(--c-strong); }
.gp-boutique-hero p { max-width: 480px; margin: 16px auto 0; font-size: 15px; line-height: 1.7; color: var(--c-muted); }

/* ══ BOUTIQUE DIVIDER ═══════════════════════ */
.gp-divider-boutique { display: flex; align-items: center; justify-content: center; gap: 18px; padding: 0 var(--pad-x) 36px; user-select: none; }
.gp-divider-line { flex: 1; max-width: 100px; height: 1px; background: linear-gradient(to right, transparent, var(--c-rule), transparent); }
.gp-divider-diamond { width: 6px; height: 6px; border: 1px solid var(--c-muted); transform: rotate(45deg); opacity: 0.4; }

/* ══ PROFILE CARD (boutique panel style) ════ */
.gp-profile-section { padding: 0 var(--pad-x) 64px; max-width: 620px; margin: 0 auto; }
.gp-boutique-panel { background: linear-gradient(145deg, rgba(250,245,239,0.95), rgba(245,232,217,0.92)); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: 1px solid rgba(212,160,71,0.15); border-radius: 20px; box-shadow: 0 20px 50px -12px rgba(109,76,91,0.12), inset 0 1px 0 rgba(255,255,255,0.60); overflow: hidden; margin-bottom: 20px; }
.gp-boutique-panel:focus-within { border-color: rgba(212,160,71,0.30); box-shadow: 0 0 0 4px rgba(212,160,71,0.08), 0 20px 50px -12px rgba(109,76,91,0.16); }
.gp-panel-head { padding: 22px 28px 0; display: flex; align-items: center; gap: 16px; }
.gp-panel-avatar { width: 72px; height: 72px; border-radius: 50%; flex-shrink: 0; background: linear-gradient(135deg, var(--c-strong), #8b5e6f); color: #fffaf3; display: flex; align-items: center; justify-content: center; font-family: var(--font-display); font-size: 28px; font-weight: 500; letter-spacing: -1px; overflow: hidden; box-shadow: 0 0 0 4px rgba(109,76,91,0.06), 0 8px 24px rgba(109,76,91,0.10); }
.gp-panel-avatar img { width: 100%; height: 100%; object-fit: cover; }
.gp-panel-id { flex: 1; }
.gp-panel-id .name { font-family: var(--font-display); font-size: 22px; font-weight: 600; margin-bottom: 2px; }
.gp-panel-id .email { font-size: 13px; color: var(--c-muted); }
.gp-panel-id .joined { font-size: 11px; color: var(--c-pale); margin-top: 4px; display: flex; align-items: center; gap: 5px; }
.gp-panel-body { padding: 24px 28px 28px; }

/* ══ FORM FIELDS ════════════════════════════ */
.gp-field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px; }
.gp-field-row.single { grid-template-columns: 1fr; }
.gp-field-row:last-child { margin-bottom: 0; }
.gp-field-boutique { display: flex; flex-direction: column; gap: 4px; }
.gp-field-boutique label { font-size: 9px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: var(--c-strong); padding-left: 2px; }
.gp-field-boutique input { height: 44px; padding: 0 14px; border-radius: 10px; border: 1px solid rgba(212,160,71,0.12); background: rgba(255,255,255,0.72); font-size: 13px; font-weight: 500; color: var(--c-text); width: 100%; transition: border-color 0.2s, background 0.2s; font-family: var(--font-body); }
.gp-field-boutique input:hover { background: rgba(255,255,255,0.90); border-color: rgba(212,160,71,0.25); }
.gp-field-boutique input:focus { border-color: var(--c-gold); background: #fff; box-shadow: 0 0 0 3px rgba(212,160,71,0.10); outline: none; }
.gp-field-boutique input::placeholder { color: var(--c-pale); }

/* ══ PASSWORD INPUT WITH EYE ═══════════════ */
.gp-pw-wrap { position: relative; display: flex; align-items: center; }
.gp-pw-wrap input { padding-right: 44px; }
.gp-pw-eye { position: absolute; right: 6px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; padding: 6px; color: var(--c-strong); opacity: 0.45; transition: opacity 0.2s; border-radius: 6px; display: flex; align-items: center; }
.gp-pw-eye:hover { opacity: 1; background: rgba(109,76,91,0.06); }
.gp-pw-eye svg { display: block; pointer-events: none; }

/* ══ STRENGTH ═════════════════════════════ */
.gp-strength { display: none; margin: 6px 0 14px; }
.gp-strength-bars { display: flex; gap: 4px; flex: 1; }
.gp-strength-seg { flex: 1; height: 3px; border-radius: 999px; background: rgba(109,76,91,0.10); transition: background 0.3s; }
.gp-strength-seg.on { background: var(--c-strong); }

/* ══ MATCH HINT ═══════════════════════════ */
.gp-match { display: none; font-size: 12px; color: var(--c-danger); margin: -4px 0 10px; }
.gp-match.show { display: block; }

/* ══ TOGGLE ══════════════════════════════ */

.gp-toggle-row { display: flex; align-items: center; justify-content: space-between; gap: 14px; padding: 14px 0; }
.gp-toggle-row + .gp-toggle-row { border-top: 1px solid var(--c-rule); }
.gp-toggle-label { font-size: 13px; font-weight: 600; }
.gp-toggle-desc { font-size: 11px; color: var(--c-muted); margin-top: 2px; }
.gp-toggle { width: 48px; height: 28px; border-radius: 14px; background: rgba(212,160,71,0.18); cursor: pointer; position: relative; transition: background 0.2s; flex-shrink: 0; }
.gp-toggle.on { background: var(--c-strong); }
.gp-toggle::after { content: ''; position: absolute; top: 3px; left: 3px; width: 22px; height: 22px; border-radius: 50%; background: #fff; transition: transform 0.2s; box-shadow: 0 1px 4px rgba(0,0,0,0.1); }
.gp-toggle.on::after { transform: translateX(20px); }

/* ══ BUTTONS ═════════════════════════════ */
.gp-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; height: 44px; padding: 0 24px; border-radius: 12px; font-family: var(--font-body); font-size: 13px; font-weight: 700; cursor: pointer; transition: all 0.25s var(--ease); border: none; }
.gp-btn-primary { background: linear-gradient(135deg, var(--c-strong) 0%, #8b5e6f 100%); color: #fffaf3; box-shadow: 0 4px 14px rgba(109,76,91,0.22); }
.gp-btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(109,76,91,0.30); }
.gp-btn-primary:active { transform: translateY(0); }
.gp-btn-ghost { background: transparent; border: 1px solid rgba(212,160,71,0.18); color: var(--c-accent); }
.gp-btn-ghost:hover { border-color: var(--c-gold); background: rgba(212,160,71,0.04); }
.gp-btn-danger { background: transparent; border: 1px solid rgba(185,75,75,0.18); color: var(--c-danger); }
.gp-btn-danger:hover { background: rgba(185,75,75,0.06); border-color: var(--c-danger); }
.gp-btn-sm { height: 38px; padding: 0 16px; font-size: 12px; border-radius: 10px; }
.gp-actions { display: flex; gap: 10px; justify-content: flex-end; padding-top: 20px; }

/* ══ SECTION DIVIDER ═════════════════════ */
.gp-section-rule { display: flex; align-items: center; gap: 14px; margin: 0 28px; }
.gp-section-rule::before, .gp-section-rule::after { content: ''; flex: 1; height: 1px; background: linear-gradient(to right, transparent, var(--c-rule), transparent); }
.gp-section-rule span { font-family: var(--font-display); font-size: 13px; font-style: italic; color: var(--c-muted); white-space: nowrap; }

/* ══ PHOTO CHIP ═════════════════════════ */
.gp-photo-chip { display: inline-flex; align-items: center; gap: 8px; padding: 6px 14px 6px 10px; border-radius: 999px; border: 1px solid rgba(212,160,71,0.18); background: rgba(250,245,239,0.80); font-size: 12px; font-weight: 600; color: #8b6f3e; cursor: pointer; transition: all 0.2s; font-family: var(--font-body); }
.gp-photo-chip:hover { border-color: var(--c-gold); background: rgba(212,160,71,0.06); }

/* ══ INLINE MESSAGE ═════════════════════ */
.gp-inline-msg { display: none; padding: 10px 14px; border-radius: 8px; font-size: 12px; font-weight: 500; margin-bottom: 14px; }
.gp-inline-msg.success { display: block; background: #eaf5ea; color: #5b8c5a; }
.gp-inline-msg.error   { display: block; background: #fde8ec; color: #b8404a; }

@media (max-width: 640px) {
  .gp-header-nav { display: none; }
  .gp-field-row { grid-template-columns: 1fr; }
}
</style>
</head>
<body>
<div class="gp-texture" aria-hidden="true"></div>

<!-- HEADER =============================================== -->
<header class="gp-header">
  <a class="gp-brand" href="<?= URLROOT ?>/main/home"><span class="gp-brand-mark">G</span>Golden Promise</a>
  <nav class="gp-header-nav">
    <a href="<?= URLROOT ?>/main/home">Home</a>
    <a href="<?= URLROOT ?>/customerServices/service">Services</a>
    <a href="<?= URLROOT ?>/main/profile" class="active">Profile</a>
  </nav>
  <div class="gp-header-actions">
    <div class="gp-profile-dropdown">
      <button class="gp-profile-btn" type="button" aria-expanded="false">
        <span class="gp-profile-avatar" id="hdAvatar"><?php if (!empty($profileAvatar)): ?><img src="<?= $h($profileAvatar) ?>" alt=""><?php else: ?><?= $initials ?><?php endif; ?></span>
        <span class="gp-profile-name"><?= $h($firstName ?: 'Account') ?></span>
        <svg class="gp-chevron" width="10" height="6" viewBox="0 0 10 6" fill="none"><path d="M1 1l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </button>
      <div class="gp-profile-menu" aria-hidden="true">
        <a class="gp-menu-item" href="<?= URLROOT ?>/main/profile"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg> My Profile</a>
        <a class="gp-menu-item" href="<?= URLROOT ?>/main/wishlist"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg> My Wishlist</a>
        <a class="gp-menu-item" href="<?= URLROOT ?>/booking/myBookings"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg> My Bookings</a>
        <a class="gp-menu-item gp-menu-item--danger" href="<?= URLROOT ?>/users/logout"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg> Logout</a>
      </div>
    </div>
  </div>
</header>

<!-- HERO ================================================= -->
<section class="gp-boutique-hero">
  <div class="gp-boutique-hero-overline">Your Account</div>
  <h1>My <em>Profile</em></h1>
  <p>Manage your personal details, security, and preferences in one place.</p>
</section>

<div class="gp-divider-boutique">
  <div class="gp-divider-line"></div>
  <div class="gp-divider-diamond"></div>
  <div class="gp-divider-line"></div>
</div>

<!-- CONTENT ============================================== -->
<section class="gp-profile-section">

  <!-- PHOTO + IDENTITY panel -->
  <div class="gp-boutique-panel">
    <div class="gp-panel-head">
      <div class="gp-panel-avatar" id="pgAvatar"><?php if (!empty($profileAvatar)): ?><img src="<?= $h($profileAvatar) ?>" alt="" id="pgImg"><?php else: ?><span id="pgInitial"><?= $initials ?></span><?php endif; ?></div>
      <div class="gp-panel-id">
        <div class="name"><?= $h($userName) ?></div>
        <div class="email"><?= $h($userEmail) ?></div>
        <div class="joined">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          Member since <?= $h($userJoined) ?>
        </div>
      </div>
    </div>
    <div class="gp-panel-body" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
      <span style="font-size:9px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--c-muted);">Photo</span>
      <button type="button" class="gp-photo-chip" id="btnPhoto"><?= empty($profileAvatar) ? 'Add Photo' : 'Change' ?></button>
      <?php if (!empty($profileAvatar)): ?>
      <button type="button" class="gp-btn gp-btn-sm gp-btn-ghost" id="btnRemove" style="color:var(--c-danger);">Remove</button>
      <?php endif; ?>
      <input type="file" id="fileInput" accept="image/jpeg,image/png,image/webp" style="display:none;">
    </div>
  </div>

  <!-- PERSONAL INFO panel -->
  <div class="gp-boutique-panel">
    <div class="gp-panel-body">
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--c-strong)" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        <span style="font-family:var(--font-display);font-size:16px;font-weight:600;">Personal Information</span>
      </div>
      <div id="profileSaveMsg" class="gp-inline-msg"></div>
      <div class="gp-field-row">
        <div class="gp-field-boutique"><label>First Name</label><input id="profFirstName" type="text" value="<?= $h($firstName) ?>" placeholder="First name"></div>
        <div class="gp-field-boutique"><label>Last Name</label><input id="profLastName" type="text" value="<?= $h($lastName) ?>" placeholder="Last name"></div>
      </div>
      <div class="gp-field-row single">
        <div class="gp-field-boutique"><label>Email</label><input id="profEmail" type="email" value="<?= $h($userEmail) ?>" placeholder="you@example.com"></div>
      </div>
      <div class="gp-field-row">
        <div class="gp-field-boutique"><label>Phone</label><input id="profPhone" type="tel" value="<?= $h($userPhone) ?>" placeholder="+95"></div>
        <div class="gp-field-boutique"><label>Last Login</label><input type="text" value="<?= $h($userLastLogin) ?>" readonly style="color:var(--c-muted);"></div>
      </div>
      <div class="gp-actions"><button class="gp-btn gp-btn-ghost" id="btnCancelInfo">Cancel</button><button class="gp-btn gp-btn-primary" id="btnSaveInfo">Save Changes</button></div>
    </div>
  </div>

  <!-- PASSWORD panel -->
  <div class="gp-boutique-panel">
    <div class="gp-panel-body">
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--c-strong)" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        <span style="font-family:var(--font-display);font-size:16px;font-weight:600;"><?= $userHasPw ? 'Change Password' : 'Set a Password' ?></span>
      </div>

      <?php if ($userIsOauth): ?>
        <p style="font-size:12px;color:var(--c-muted);margin-bottom:16px;padding:12px 14px;border-radius:8px;background:rgba(212,160,71,0.08);border:1px solid rgba(212,160,71,0.15);">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--c-gold)" stroke-width="2" style="vertical-align:-2px;margin-right:6px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
          You signed in with Google. Set a password below so you can also log in with your email.
        </p>
      <?php else: ?>
      <!-- Current Password (only for email/password accounts) -->
      <div class="gp-field-row single">
        <div class="gp-field-boutique">
          <label>Current Password</label>
          <div class="gp-pw-wrap">
            <input id="pwCur" type="password" autocomplete="current-password" minlength="8" required>
            <button class="gp-pw-eye" data-target="pwCur" aria-label="Toggle visibility"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <div class="gp-field-row single">
        <div class="gp-field-boutique">
          <label>New Password</label>
          <div class="gp-pw-wrap">
            <input id="pwNew" type="password" autocomplete="new-password" minlength="8" required>
            <button class="gp-pw-eye" data-target="pwNew" aria-label="Toggle visibility"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
          </div>
        </div>
      </div>
      <div class="gp-strength" id="strength"><div style="display:flex;align-items:center;gap:10px;"><div class="gp-strength-bars"><span class="gp-strength-seg" id="s1"></span><span class="gp-strength-seg" id="s2"></span><span class="gp-strength-seg" id="s3"></span><span class="gp-strength-seg" id="s4"></span></div><span id="strengthLbl" style="font-size:11px;color:var(--c-muted);">Weak</span></div></div>
      <div class="gp-field-row single">
        <div class="gp-field-boutique">
          <label>Confirm New Password</label>
          <div class="gp-pw-wrap">
            <input id="pwCfm" type="password" autocomplete="new-password" minlength="8" required>
            <button class="gp-pw-eye" data-target="pwCfm" aria-label="Toggle visibility"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
          </div>
        </div>
      </div>
      <p class="gp-match" id="matchHint">Passwords do not match.</p>
      <div class="gp-actions"><button class="gp-btn gp-btn-primary" id="btnUpdatePw"><?= $userIsOauth ? 'Set Password' : 'Update Password' ?></button></div>
    </div>
  </div>

  <!-- PREFERENCES panel -->
  <div class="gp-boutique-panel">
    <div class="gp-panel-body">
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--c-strong)" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
        <span style="font-family:var(--font-display);font-size:16px;font-weight:600;">Notification Preferences</span>
      </div>
      <div class="gp-toggle-row"><div><div class="gp-toggle-label">Booking Updates</div><div class="gp-toggle-desc">When suppliers confirm or update your bookings</div></div><div class="gp-toggle on"></div></div>
      <div class="gp-toggle-row"><div><div class="gp-toggle-label">Payment Reminders</div><div class="gp-toggle-desc">Before deposit and final payment deadlines</div></div><div class="gp-toggle on"></div></div>
      <div class="gp-toggle-row"><div><div class="gp-toggle-label">New Packages</div><div class="gp-toggle-desc">When new wedding packages launch</div></div><div class="gp-toggle on"></div></div>
      <div class="gp-toggle-row"><div><div class="gp-toggle-label">Weekly Inspiration</div><div class="gp-toggle-desc">Tips and highlights every Friday</div></div><div class="gp-toggle"></div></div>
    </div>
  </div>

  <!-- DANGER panel -->
  <div class="gp-boutique-panel" style="border-color:rgba(185,75,75,0.15);">
    <div class="gp-panel-body" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;">
      <div>
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:4px;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--c-danger)" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
          <span style="font-family:var(--font-display);font-size:16px;font-weight:600;color:var(--c-danger);">Danger Zone</span>
        </div>
        <div style="font-size:12px;color:var(--c-muted);">Delete your account and all associated data permanently.</div>
      </div>
      <button class="gp-btn gp-btn-danger gp-btn-sm">Delete Account</button>
    </div>
  </div>

</section>

<script>
(function(){
'use strict';

var eyeOpen = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
var eyeShut = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10 10 0 0 1 12 20c-7 0-11-8-11-8a18 18 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9 9 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>';

// Dropdown
var btn=document.querySelector('.gp-profile-btn');
if(btn){btn.addEventListener('click',function(e){e.stopPropagation();var x=this.getAttribute('aria-expanded')==='true';this.setAttribute('aria-expanded',!x);});document.addEventListener('click',function(){btn.setAttribute('aria-expanded','false');});}

// Eye toggle
document.querySelectorAll('.gp-pw-eye').forEach(function(el){el.addEventListener('click',function(){var i=document.getElementById(this.dataset.target);if(!i)return;var s=i.type==='password';i.type=s?'text':'password';this.innerHTML=s?eyeShut:eyeOpen;});});

// Strength
var nw=document.getElementById('pwNew'),st=document.getElementById('strength'),sl=document.getElementById('strengthLbl'),segs=['s1','s2','s3','s4'];
nw.addEventListener('input',function(){var v=this.value;if(!v.trim()){st.style.display='none';segs.forEach(function(id){document.getElementById(id).classList.remove('on');});sl.textContent='Weak';return;}st.style.display='block';var sc=0;if(v.length>=8)sc++;if(/[A-Z]/.test(v))sc++;if(/[0-9]/.test(v))sc++;if(/[^A-Za-z0-9]/.test(v))sc++;segs.forEach(function(id,i){document.getElementById(id).classList.toggle('on',i<sc);});sl.textContent=sc<=1?'Weak':sc===2?'Fair':sc===3?'Good':'Strong';});

// Match
var cf=document.getElementById('pwCfm'),mh=document.getElementById('matchHint');
function chk(){var p=nw.value,c=cf.value;if(!c){mh.classList.remove('show');cf.style.borderColor='';return;}if(p!==c){mh.classList.add('show');cf.style.borderColor='var(--c-danger)';}else{mh.classList.remove('show');cf.style.borderColor='var(--c-success, #5b8c5a)';}}
cf.addEventListener('input',chk);nw.addEventListener('input',function(){if(cf.value)chk();});

// Toggles
document.querySelectorAll('.gp-toggle').forEach(function(e){e.addEventListener('click',function(){this.classList.toggle('on');});});

// ── Inline msg helper ──
function showMsg(elId, text, type) {
    var el = document.getElementById(elId);
    if (!el) return;
    el.textContent = text;
    el.className = 'gp-inline-msg' + (type ? ' ' + type : '');
    if (type) setTimeout(function(){ el.className = 'gp-inline-msg'; el.textContent = ''; }, 5000);
}

// ── SAVE PROFILE ──
document.getElementById('btnSaveInfo').addEventListener('click', function(){
    var firstName = document.getElementById('profFirstName').value.trim();
    var lastName  = document.getElementById('profLastName').value.trim();
    var email     = document.getElementById('profEmail').value.trim();
    var phone     = document.getElementById('profPhone').value.trim();

    var btn = document.getElementById('btnSaveInfo');
    btn.disabled = true;
    btn.textContent = 'Saving…';

    fetch('<?= URLROOT ?>/main/updateProfile', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            name: (firstName + ' ' + lastName).trim(),
            email: email,
            phone: phone,
        }),
    })
    .then(function(r){ return r.json(); })
    .then(function(data){
        btn.disabled = false;
        btn.textContent = 'Save Changes';
        if (data.ok) {
            showMsg('profileSaveMsg', '✓ Profile updated successfully.', 'success');
        } else {
            showMsg('profileSaveMsg', data.error || 'Failed to update profile.', 'error');
        }
    })
    .catch(function(){
        btn.disabled = false;
        btn.textContent = 'Save Changes';
        showMsg('profileSaveMsg', 'Network error. Please try again.', 'error');
    });
});

// ── CANCEL ──
document.getElementById('btnCancelInfo').addEventListener('click', function(){
    document.getElementById('profFirstName').value = '<?= $h($firstName) ?>';
    document.getElementById('profLastName').value = '<?= $h($lastName) ?>';
    document.getElementById('profEmail').value = '<?= $h($userEmail) ?>';
    document.getElementById('profPhone').value = '<?= $h($userPhone) ?>';
    showMsg('profileSaveMsg', '', '');
});

// ── UPDATE PASSWORD ──
document.getElementById('btnUpdatePw').addEventListener('click', function(){
    var curEl = document.getElementById('pwCur');
    var isOauth = !curEl;  // Google/Facebook users have no current-password field
    var cur = curEl ? curEl.value : '';
    var nP = nw.value;
    var cP = cf.value;

    if (curEl) curEl.style.borderColor = '';
    nw.style.borderColor = '';
    cf.style.borderColor = '';
    mh.classList.remove('show');
    showMsg('profilePwMsg', '', '');

    var ok = true;
    if (!isOauth && !cur.trim()) { curEl.style.borderColor = 'var(--c-danger)'; ok = false; }
    if (nP.length < 8) { nw.style.borderColor = 'var(--c-danger)'; ok = false; }
    if (nP !== cP) { cf.style.borderColor = 'var(--c-danger)'; mh.classList.add('show'); ok = false; }
    if (!ok) return;

    var btnPw = document.getElementById('btnUpdatePw');
    btnPw.disabled = true;
    btnPw.textContent = isOauth ? 'Setting…' : 'Updating…';

    fetch('<?= URLROOT ?>/main/updatePassword', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            current_password: cur,
            new_password: nP,
            is_oauth: isOauth,
            device: navigator.userAgent || 'Unknown device',
        }),
    })
    .then(function(r){ return r.json(); })
    .then(function(data){
        btnPw.disabled = false;
        btnPw.textContent = isOauth ? 'Set Password' : 'Update Password';
        if (data.ok) {
            if (curEl) curEl.value = '';
            nw.value = '';
            cf.value = '';
            st.style.display = 'none';
            segs.forEach(function(id){ document.getElementById(id).classList.remove('on'); });
            showMsg('profilePwMsg', '✓ Password ' + (isOauth ? 'set' : 'updated') + '. A confirmation email has been sent.', 'success');
        } else {
            showMsg('profilePwMsg', data.error || 'Failed to update password.', 'error');
        }
    })
    .catch(function(){
        btnPw.disabled = false;
        btnPw.textContent = isOauth ? 'Set Password' : 'Update Password';
        showMsg('profilePwMsg', 'Network error. Please try again.', 'error');
    });
});

// ── PHOTO UPLOAD ──
var fi=document.getElementById('fileInput'),bp=document.getElementById('btnPhoto'),br=document.getElementById('btnRemove');
bp.addEventListener('click',function(){fi.click();});
fi.addEventListener('change',function(){
    var file = this.files[0];
    if (!file) return;

    var fd = new FormData();
    fd.append('profile_photo', file);
    bp.disabled = true;
    bp.textContent = 'Uploading…';

    fetch('<?= URLROOT ?>/main/uploadProfilePhoto', { method: 'POST', body: fd })
    .then(function(r){ return r.json(); })
    .then(function(data){
        bp.disabled = false;
        if (data.ok) {
            // Update avatar display
            var pg = document.getElementById('pgAvatar');
            var hd = document.getElementById('hdAvatar');
            var imgUrl = data.url + '?t=' + Date.now();
            // In-page avatar
            var pgImgEl = document.getElementById('pgImg');
            if (pgImgEl) { pgImgEl.src = imgUrl; } else {
                var pgInit = document.getElementById('pgInitial');
                if (pgInit) pgInit.remove();
                var img = document.createElement('img');
                img.id = 'pgImg'; img.src = imgUrl; img.alt = '';
                pg.appendChild(img);
            }
            // Header avatar
            if (hd) { hd.innerHTML = '<img src="' + imgUrl + '" alt="">'; }
            bp.textContent = 'Change';
            // Show remove button
            if (!document.getElementById('btnRemove')) {
                var rm = document.createElement('button');
                rm.type = 'button'; rm.id = 'btnRemove';
                rm.className = 'gp-btn gp-btn-sm gp-btn-ghost';
                rm.style.color = 'var(--c-danger)';
                rm.textContent = 'Remove';
                rm.addEventListener('click', removePhoto);
                bp.parentNode.insertBefore(rm, bp.nextSibling);
                br = rm;
            }
        } else {
            bp.textContent = 'Add Photo';
            alert(data.error || 'Upload failed.');
        }
    })
    .catch(function(){
        bp.disabled = false;
        bp.textContent = 'Add Photo';
        alert('Network error. Please try again.');
    });

    this.value = '';
});

function removePhoto() {
    if (!confirm('Remove your profile photo?')) return;

    fetch('<?= URLROOT ?>/main/removeProfilePhoto', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
    })
    .then(function(r){ return r.json(); })
    .then(function(data){
        if (data.ok) {
            var pg = document.getElementById('pgAvatar');
            var hd = document.getElementById('hdAvatar');
            var pgImg = document.getElementById('pgImg');
            if (pgImg) pgImg.remove();
            var initSpan = document.createElement('span');
            initSpan.id = 'pgInitial';
            initSpan.textContent = '<?= $initials ?>';
            pg.appendChild(initSpan);
            if (hd) { hd.innerHTML = '<?= $initials ?>'; }
            bp.textContent = 'Add Photo';
            var rmBtn = document.getElementById('btnRemove');
            if (rmBtn) rmBtn.remove();
        } else {
            alert(data.error || 'Failed to remove photo.');
        }
    })
    .catch(function(){ alert('Network error.'); });
}

if (br) br.addEventListener('click', removePhoto);

})();
</script>
</body>
</html>
