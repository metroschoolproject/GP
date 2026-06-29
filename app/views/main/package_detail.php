<?php
$package = $package ?? [];
$cartCount = (int)($cartCount ?? 0);
$categoryServices = $package['category_services'] ?? [];
$addonServices = $package['addon_services'] ?? [];

$isLoggedIn = !empty($_SESSION['session_uid']);
$authNavUrl = $isLoggedIn ? URLROOT . '/users/logout' : URLROOT . '/users/auth';
$authNavLabel = $isLoggedIn ? 'Logout' : 'Sign in';
$customerName = trim((string)($_SESSION['session_name'] ?? 'Guest customer'));
$customerEmail = trim((string)($_SESSION['session_email'] ?? 'Email not provided'));
$customerAvatar = trim((string)($_SESSION['session_avatar'] ?? ''));
$customerInitial = strtoupper(substr($customerName !== '' ? $customerName : 'G', 0, 1));

$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$money = fn($v) => 'MMK ' . number_format((float)$v, 0);
$packageCustomerPrice = (float)($package['package_price'] ?? $package['base_price'] ?? 0);
$inferPackageTier = static function (array $pkg): string {
    $haystack = strtolower(implode(' ', [
        $pkg['tier'] ?? '',
        $pkg['type'] ?? '',
        $pkg['package_type'] ?? '',
        $pkg['category_name'] ?? '',
        $pkg['name'] ?? '',
        $pkg['slug'] ?? '',
        $pkg['tagline'] ?? '',
        $pkg['description'] ?? '',
    ]));
    if (str_contains($haystack, 'luxury')) return 'luxury';
    if (str_contains($haystack, 'premium')) return 'premium';
    if (str_contains($haystack, 'standard')) return 'standard';
    return 'standard';
};
$packageTierKey = $inferPackageTier((array)$package);
$packageTierLabel = ['standard' => 'Standard', 'premium' => 'Premium', 'luxury' => 'Luxury'][$packageTierKey] ?? 'Standard';
$includedServices = [];
foreach ($categoryServices as $cs) {
    foreach (($cs['services'] ?? []) as $svc) {
        $svc['category_name'] = $svc['category_name'] ?? ($cs['category_name'] ?? '');
        $includedServices[] = $svc;
    }
}
$packageImage = trim((string)($package['image_url'] ?? ''));
if ($packageImage === '') {
    foreach ($includedServices as $svc) {
        if (!empty($svc['image'])) {
            $packageImage = trim((string)$svc['image']);
            break;
        }
    }
}
$moneyRange = function ($svc) use ($money) {
    if (($svc['quantity_type'] ?? 'fixed') === 'guests') {
        $quantity = max(1, (int)($svc['quantity'] ?? 1));
        $unit = (float)($svc['unit_price'] ?? $svc['price_min'] ?? $svc['price'] ?? 0);
        $total = (float)($svc['package_price'] ?? ($unit * $quantity));
        return $money($total) . ' · ' . $quantity . ' guests at ' . $money($unit);
    }
    return $money($svc['package_price'] ?? $svc['unit_price'] ?? $svc['price_min'] ?? $svc['price'] ?? 0);
};
$addonDurationText = static function ($svc): string {
    $type = $svc['booking_type'] ?? 'fullday';
    $min = (int)($svc['duration_minutes'] ?? 0);
    if ($type === 'slot' && $min > 0) {
        $hours = $min / 60;
        return $hours >= 1 ? rtrim(rtrim(number_format($hours, 1), '0'), '.') . ' hr' : $min . ' min';
    }
    return $type === 'flexible' ? 'Flexible' : 'Full day';
};
$addonLocation = static function ($svc): string {
    $location = trim((string)($svc['venue_location'] ?? $svc['service_location'] ?? $svc['location'] ?? ''));
    return $location !== '' ? $location : 'Location available after booking';
};
$isRentalCategory = static function ($svc): bool {
    $slug = strtolower(trim((string)($svc['category_slug'] ?? '')));
    $name = strtolower(trim((string)($svc['category_name'] ?? '')));

    return in_array($slug, ['attire'], true)
        || in_array($name, ['attire'], true);
};
$rentalRows = static function ($svc) use ($money, $isRentalCategory): array {
    if (!$isRentalCategory($svc)) {
        return [];
    }

    $rows = [];
    $borrowPackagePrice = (float)($svc['borrow_package_price'] ?? $svc['borrow_price'] ?? 0);
    $borrowCustomizePrice = (float)($svc['borrow_customize_price'] ?? $svc['borrow_price'] ?? $borrowPackagePrice);
    $buyPackagePrice = (float)($svc['buy_package_price'] ?? $svc['buy_price'] ?? 0);
    $buyCustomizePrice = (float)($svc['buy_customize_price'] ?? $svc['buy_price'] ?? $buyPackagePrice);
    if ($borrowPackagePrice > 0 || $borrowCustomizePrice > 0) {
        $returnDays = (int)($svc['return_days'] ?? 0);
        $rows[] = [
            'label' => 'Borrow',
            'package' => $borrowPackagePrice > 0 ? $money($borrowPackagePrice) : '—',
            'customize' => $borrowCustomizePrice > 0 ? $money(max($borrowPackagePrice, $borrowCustomizePrice)) : '—',
            'meta' => $returnDays > 0 ? $returnDays . ' ' . ($returnDays === 1 ? 'day' : 'days') . ' return' : 'Rental option',
        ];
    }
    if ($buyPackagePrice > 0 || $buyCustomizePrice > 0) {
        $rows[] = [
            'label' => 'Buy',
            'package' => $buyPackagePrice > 0 ? $money($buyPackagePrice) : '—',
            'customize' => $buyCustomizePrice > 0 ? $money(max($buyPackagePrice, $buyCustomizePrice)) : '—',
            'meta' => 'Purchase option',
        ];
    }

    return $rows;
};
$serviceDetailUrl = function ($svc) use ($package) {
    $url = URLROOT . '/customerServices/detail/' . (int)($svc['id'] ?? 0);
    $params = [
        'package_id' => (int)($package['package_id'] ?? 0),
        'package_item_id' => (int)($svc['package_item_id'] ?? 0),
    ];
    return $url . '?' . http_build_query($params);
};
$addonDetailUrl = function ($svc) use ($package) {
    return URLROOT . '/customerServices/detail/' . (int)($svc['id'] ?? 0)
        . '?' . http_build_query(['addon_package_id' => (int)($package['package_id'] ?? 0)]);
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $h($package['name'] ?? 'Package') ?> — Golden Promise</title>
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
  font-size: 18px; font-weight: 800; white-space: nowrap;
}
.gp-brand-mark {
  display: grid; place-items: center;
  width: 40px; height: 40px; border-radius: 50%;
  background: var(--c-strong); color: #fffaf3;
  font-size: 14px; letter-spacing: 1px;
}
.gp-header-nav { display: flex; align-items: center; justify-content: center; gap: 4px; }
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
  position: relative; padding: 8px 14px 8px 10px;
  border-radius: 999px; border: 1px solid var(--c-rule);
  background: var(--c-white); color: var(--c-strong);
  font-size: 13px; font-weight: 700; transition: all 0.2s;
}
.gp-cart-badge:hover { border-color: var(--c-strong); background: rgba(109,76,91,0.06); }
.gp-cart-badge-count {
  display: inline-flex; align-items: center; justify-content: center;
  min-width: 20px; height: 20px; padding: 0 6px;
  border-radius: 999px; background: var(--c-strong); color: #fcf8f5;
  font-size: 10px; font-weight: 700;
}
.gp-header-cta {
  display: inline-flex; align-items: center; justify-content: center;
  min-height: 40px; padding: 0 20px; border-radius: 999px; border: none;
  background: var(--c-strong); color: #fffaf3;
  font-size: 13px; font-weight: 800; cursor: pointer;
  box-shadow: 0 14px 30px rgba(109,76,91,0.18); transition: all 0.2s;
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

/* ─── PAGE ───────────────────────────────── */
.gp-detail-page {
  max-width: none;
  margin: 0 auto;
  padding: 0 0 72px;
}

/* ─── BREADCRUMB ─────────────────────────── */
.gp-breadcrumb {
  display: flex; align-items: center; gap: 8px;
  padding: 20px 0 24px;
  font-size: 12px; font-weight: 600; color: var(--c-muted);
}
.gp-breadcrumb a:hover { color: var(--c-strong); }
.gp-breadcrumb-sep { color: var(--c-rule); }

/* ─── HERO AREA ──────────────────────────── */
.gp-detail-hero { padding: 18px 0 28px; }
.gp-detail-hero-overline {
  font-size: 12px; font-weight: 800; letter-spacing: 0.16em; text-transform: uppercase;
  color: var(--c-danger); margin-bottom: 12px;
}
.gp-detail-hero h1 {
  font-family: var(--font-display);
  font-size: clamp(30px, 3.4vw, 46px);
  font-weight: 600;
  line-height: 1.02;
  color: var(--c-text);
  margin-bottom: 12px;
}
.gp-detail-hero .tagline {
  font-size: 18px;
  color: var(--c-accent);
  font-weight: 500;
  margin-bottom: 14px;
}
.gp-detail-hero .desc {
  max-width: 680px;
  font-size: 15px;
  line-height: 1.7;
  color: var(--c-muted);
  margin-bottom: 24px;
}
.gp-detail-hero .price-hero {
  display: inline-flex; align-items: baseline; gap: 8px;
  font-family: var(--font-display);
  font-size: 42px; font-weight: 600;
  color: var(--c-strong);
}
.gp-detail-hero .price-hero-label {
  font-size: 14px; font-weight: 500; color: var(--c-pale);
  font-family: var(--font-body);
}
.gp-package-cart-form{margin-top:18px}
.gp-package-cart-btn{
  display:inline-flex;align-items:center;gap:8px;height:44px;padding:0 22px;border:0;border-radius:999px;
  background:var(--c-strong);color:#fcf8f5;font-size:13px;font-weight:800;cursor:pointer;
  box-shadow:0 14px 30px rgba(109,76,91,.18);transition:all .2s var(--ease-out-expo)
}
.gp-package-cart-btn:hover{background:#5a3d4a;transform:translateY(-1px)}

/* ─── PACKAGE DETAIL LAYOUT ──────────────── */
.gp-package-detail-shell {
  display: block;
  margin-bottom: 34px;
}
.gp-package-media-column {
  min-width: 0;
  max-width: none;
}
.gp-package-main-image {
    position: relative;
    overflow: hidden;

    height: clamp(480px, 55vw, 620px);
    min-height: 480px;

    border: 0;
    border-radius: 0;
    background: linear-gradient(140deg, #ead8c8, #d8c2af);
    box-shadow: 0 20px 48px rgba(63,36,26,.12);
}
.gp-package-main-image::after {
  content: '';
  position: absolute;
  inset: 0;
  background:
    linear-gradient(180deg, rgba(17,14,15,.22) 0%, rgba(17,14,15,.14) 42%, rgba(17,14,15,.68) 100%),
    rgba(17,14,15,.08);
  backdrop-filter: blur(1.6px);
  -webkit-backdrop-filter: blur(1.6px);
  pointer-events: none;
}
.gp-package-main-image img {
  width: 100%;
  height: 100%;
  min-height: 340px;
  object-fit: cover;
}
.gp-package-image-placeholder {
  display: grid;
  place-items: center;
  min-height: 340px;
  color: rgba(109,76,91,.35);
}
.gp-package-type-tag {
  display: inline-flex;
  align-items: center;
  min-height: 30px;
  width: fit-content;
  margin-bottom: 10px;
  padding: 0 12px;
  border: 1px solid rgba(255,248,239,.54);
  border-radius: 999px;
  background: rgba(255,248,239,.86);
  color: var(--c-strong);
  font-size: 10px;
  font-weight: 800;
  letter-spacing: .08em;
  text-transform: uppercase;
  box-shadow: 0 8px 18px rgba(17,14,15,.12);
}
.gp-package-image-title {
  position: absolute;
  left: 60px;
  right: min(360px, 28vw);
  bottom: 30px;
  z-index: 2;
  color: #fffaf3;
}
.gp-package-image-title h1 {
  max-width: 820px;
  color: #fffaf3;
  font-family: var(--font-display);
  font-size: clamp(38px, 6vw, 76px);
  font-weight: 650;
  line-height: 1;
  text-shadow: 0 12px 32px rgba(0,0,0,.34);
}
.gp-package-hero-form {
  position: absolute;
  right: 30px;
  bottom: 30px;
  z-index: 2;
  margin: 0;
}
.gp-package-hero-form .gp-package-cart-btn {
  min-width: 160px;
  min-height: 58px;
  height: 58px;
  display: inline-flex;
  align-items: center;
  justify-content: space-between;
  gap: 14px;
  padding: 8px 10px 8px 24px;
  border: 1px solid rgba(154,104,127,.22);
  border-radius: 16px;
  background: #f9ece2; /* change button background here */
  color: #1b000f;
  font-size: 16px;
  font-weight: 800;
  box-shadow: 0 18px 40px rgba(17,14,15,.28);
  transition: all .2s var(--ease-out-expo);
}

.gp-package-hero-form .gp-package-cart-btn:hover {
  background: #9A687F; /* hover color */
  transform: translateY(-1px);
  color : #fdf1f3;
}

.gp-package-hero-form .gc-book-btn-icon {
  display: inline-grid;
  place-items: center;
  width: 36px;
  height: 36px;
  flex: 0 0 36px;
  border-radius: 50%;
  background: #1e0116;
  color: #f8ecee; /* icon arrow color */
}

.gp-package-hero-form .gc-book-btn-icon svg {
  width: 18px;
  height: 18px;
  stroke: currentColor;
}
.gp-package-content-grid {
  display: grid;
  grid-template-columns: minmax(0, 7fr) minmax(300px, 3fr);
  gap: 28px;
  align-items: start;
  margin: 0 var(--pad-x);
  padding: 34px 0 14px;
}
.gp-included-services-block h2,
.gp-package-description-card h2 {
  font-family: var(--font-display);
  font-size: 28px;
  font-weight: 600;
  color: var(--c-text);
  margin-bottom: 4px;
}
.gp-included-subtitle {
  color: var(--c-muted);
  font-size: 12px;
  font-weight: 600;
  margin-bottom: 18px;
}
.gp-included-list {
  display: grid;
  gap: 16px;
  max-height: 642px;
  overflow-y: scroll;
  padding-right: 12px;
  scrollbar-width: thin;
  scrollbar-color: rgba(109,76,91,.34) rgba(234,216,199,.38);
}
.gp-included-list::-webkit-scrollbar {
  width: 7px;
}
.gp-included-list::-webkit-scrollbar-track {
  background: rgba(234,216,199,.38);
  border-radius: 999px;
}
.gp-included-list::-webkit-scrollbar-thumb {
  background: rgba(109,76,91,.34);
  border-radius: 999px;
}
/* ─── INCLUDED SERVICES : COMPACT VERSION ───────────────── */
.gp-included-list {
  gap: 14px;
}

/* hide calendar line */
.gp-included-date {
  display: none !important;
}

/* compact included service card */
.gp-included-item {
  display: grid;
  grid-template-columns: 116px minmax(0, 1fr);
  align-items: center;
  gap: 16px;
  min-height: 118px;
  padding: 16px 18px;
  border: 1px solid rgba(224, 196, 167, 0.72);
  border-radius: 18px;
  background: rgba(255, 250, 244, 0.82);
  box-shadow: none;
}

.gp-included-thumb {
  width: 116px;
  height: 86px;
  border-radius: 14px;
  overflow: hidden;
}

.gp-included-thumb img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

/* make the right side relative so the tag can sit at top-right */
.gp-included-copy {
  position: relative;
  min-width: 0;
  padding-top: 28px; /* space for the tag */
}

/* service type tag at top-right of card */
.gp-included-category {
  position: absolute;
  top: 0;
  right: 0;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-height: 30px;
  padding: 0 12px;
  border-radius: 999px;
  background: rgba(232, 214, 223, 0.95);
  color: #7E4F65;
  font-size: 10px;
  font-weight: 800;
  letter-spacing: .08em;
  text-transform: uppercase;
  white-space: nowrap;
}

.gp-included-name {
  display: block;
  margin-bottom: 6px;
  font-size: 15px;
  line-height: 1.3;
  font-weight: 800;
  color: var(--c-text);
}

.gp-included-price {
  display: block;
  font-size: 12px;
  line-height: 1.45;
  font-weight: 700;
  color: rgba(109,76,91,.78);
}
.gp-package-description-card {
  position: relative;
  min-height: auto;
  padding: 26px;
  border: 1px solid rgba(234,216,199,.95);
  border-radius: 18px;
  background: rgba(255,250,244,.82);
  box-shadow: 0 16px 34px rgba(63,36,26,.08);
}
.gp-package-description-card p {
  color: var(--c-accent);
  font-size: 14px;
  line-height: 1.85;
  padding-bottom: 86px;
}
.gp-package-description-price {
  position: absolute;
  right: 26px;
  bottom: 24px;
  display: grid;
  justify-items: end;
  gap: 3px;
}
.gp-package-description-price span {
  color: rgba(74,52,47,.52);
  font-size: 12px;
  font-weight: 600;
  letter-spacing: .08em;
  text-transform: uppercase;
}
.gp-package-description-price strong {
  color: var(--c-strong);
  font-family: var(--font-display);
  font-size: clamp(28px, 3vw, 42px);
  font-weight: 500;
  line-height: 1;
}
.gp-package-facilities {
  max-width: 900px;
  margin: 0 0 24px;
}
.gp-package-facilities h2 {
  font-family: var(--font-display);
  font-size: 30px;
  font-weight: 600;
  color: var(--c-text);
  margin-bottom: 18px;
}
.gp-package-facility-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 18px 28px;
}
.gp-package-facility {
  display: flex;
  align-items: center;
  gap: 12px;
  color: var(--c-accent);
  font-size: 14px;
  font-weight: 500;
}
.gp-package-facility i {
  color: #2f2a28;
  width: 24px;
  height: 24px;
  flex: 0 0 24px;
}
.gp-package-booking-card {
  margin-top: 26px;
  padding: 24px;
  border: 1px solid rgba(234,216,199,.95);
  border-radius: 18px;
  background: #fffaf4;
  box-shadow: 0 18px 42px rgba(63,36,26,.10);
}
.gp-included-panel > .gp-package-booking-card {
  margin-top: 0;
}
.gp-included-services-block {
  min-width: 0;
  padding: 22px;
  border: 1px solid rgba(234,216,199,.95);
  border-radius: 18px;
  background: rgba(255,250,244,.78);
  box-shadow: 0 16px 34px rgba(63,36,26,.08);
}
.gp-booking-facts {
  padding: 22px;
  border: 1px solid rgba(234,216,199,.95);
  border-radius: 14px;
  background: rgba(255,248,239,.72);
}
.gp-booking-fact {
  padding: 0 0 18px;
  margin-bottom: 18px;
  border-bottom: 1px solid var(--c-rule);
}
.gp-booking-fact:last-child {
  padding-bottom: 0;
  margin-bottom: 0;
  border-bottom: 0;
}
.gp-booking-fact span {
  display: block;
  color: rgba(74,52,47,.72);
  font-size: 12px;
  font-weight: 800;
  letter-spacing: .12em;
  text-transform: uppercase;
  margin-bottom: 6px;
}
.gp-booking-fact strong {
  color: var(--c-text);
  font-size: 18px;
  font-weight: 800;
}
.gp-package-booking-details {
  display: grid;
  gap: 14px;
  margin: 22px 0 18px;
  padding: 18px;
  border: 1px solid rgba(234,216,199,.95);
  border-radius: 12px;
  background: rgba(255,248,239,.72);
}
.gp-package-booking-price {
  display: grid;
  justify-items: center;
  gap: 6px;
  padding: 8px 0 22px;
  text-align: center;
}
.gp-package-booking-price span {
  color: rgba(74,52,47,.42);
  font-size: 14px;
  font-weight: 700;
}
.gp-package-booking-price strong {
  color: var(--c-strong);
  font-family: var(--font-display);
  font-size: clamp(42px, 5vw, 64px);
  font-weight: 700;
  line-height: 1;
}
.gp-package-booking-row {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  padding-top: 12px;
  border-top: 1px solid var(--c-rule);
}
.gp-package-booking-row:first-of-type {
  padding-top: 0;
  border-top: 0;
}
.gp-package-booking-row i {
  flex: 0 0 17px;
  width: 17px;
  height: 17px;
  margin-top: 2px;
  color: var(--c-strong);
}
.gp-package-booking-row span {
  display: block;
  color: rgba(74,52,47,.62);
  font-size: 11px;
  font-weight: 800;
  letter-spacing: .1em;
  text-transform: uppercase;
  margin-bottom: 3px;
}
.gp-package-booking-row strong {
  display: block;
  min-width: 0;
  color: var(--c-text);
  font-size: 14px;
  font-weight: 800;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.gp-package-booking-row small {
  display: block;
  min-width: 0;
  color: var(--c-accent);
  font-size: 12px;
  font-weight: 600;
  line-height: 1.45;
}
.gp-package-booking-card .gp-package-cart-form {
  margin-top: 0;
}
.gp-package-booking-card .gp-package-cart-btn {
  width: 100%;
  justify-content: center;
  height: 54px;
  border-radius: 10px;
  font-size: 15px;
}


/* ─── HOW IT WORKS ───────────────────────── */
.gp-how-it-works {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
  margin-bottom: 48px;
  padding: 28px 0;
  border-top: 1px solid var(--c-rule);
  border-bottom: 1px solid var(--c-rule);
}
.gp-how-step {
  text-align: center;
  padding: 0 8px;
}
.gp-how-step-icon {
  display: grid; place-items: center;
  width: 48px; height: 48px; margin: 0 auto 12px;
  border-radius: 50%;
  background: var(--c-strong); color: #fcf8f5;
}
.gp-how-step h3 {
  font-size: 13px; font-weight: 700; color: var(--c-text);
  margin-bottom: 4px;
}
.gp-how-step p {
  font-size: 12px; color: var(--c-muted); line-height: 1.5;
}

/* ─── CATEGORY SECTION ───────────────────── */
.gp-cat-section {
  margin: 0 var(--pad-x) 36px;
}
.gp-cat-section:last-child { margin-bottom: 0; }
.gp-cat-header {
  display: flex; align-items: center; justify-content: space-between;
  margin-bottom: 16px;
}
.gp-cat-header h2 {
  font-family: var(--font-display);
  font-size: 28px; font-weight: 600;
  color: var(--c-text);
}
.gp-cat-count {
  font-size: 12px; font-weight: 600; color: var(--c-muted);
}

/* ─── SERVICE CARDS ──────────────────────── */
.gp-svc-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 16px;
}
.gp-svc-card {
  background: var(--c-white);
  border: 1px solid var(--c-rule);
  border-radius: var(--r-card);
  overflow: hidden;
  box-shadow: var(--sh-card);
  transition: all 0.3s var(--ease-out-expo);
  display: flex; flex-direction: column;
}
.gp-svc-card:hover {
  transform: translateY(-3px);
  box-shadow: var(--sh-panel);
}
.gp-svc-img {
  display: block; position: relative;
  aspect-ratio: 4/3; overflow: hidden;
  background: linear-gradient(160deg, #ede0d0, #ddcebb);
  flex-shrink: 0;
}
.gp-svc-img img {
  position: absolute; inset: 0;
  width: 100%; height: 100%; object-fit: cover;
  transition: transform 0.6s var(--ease-out-expo);
}
.gp-svc-card:hover .gp-svc-img img { transform: scale(1.06); }
.gp-svc-img-placeholder {
  position: absolute; inset: 0;
  display: grid; place-items: center;
  color: var(--c-pale); opacity: 0.4;
}
.gp-svc-body {
  padding: 16px 18px 18px;
  flex: 1; display: flex; flex-direction: column;
}
.gp-svc-supplier {
  font-size: 11px; font-weight: 600; color: var(--c-muted);
  margin-bottom: 4px;
}
.gp-svc-hall {
  display: inline-flex; align-items: center; gap: 5px;
  max-width: 100%;
  width: fit-content;
  margin: 0 0 8px;
  padding: 4px 8px;
  border-radius: 999px;
  background: rgba(109,76,91,0.08);
  color: var(--c-strong);
  font-size: 10px;
  font-weight: 800;
  line-height: 1.2;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.gp-svc-hall svg {
  flex-shrink: 0;
}
.gp-svc-name {
  font-family: var(--font-display);
  font-size: 18px; font-weight: 600; line-height: 1.1;
  color: var(--c-text); margin-bottom: 6px;
}
.gp-svc-desc {
  font-size: 12px; line-height: 1.5; color: var(--c-accent);
  display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
  overflow: hidden; flex: 1;
}
.gp-svc-rating {
  display: flex; align-items: center; gap: 4px;
  margin-top: 8px;
  font-size: 11px; font-weight: 700; color: #d4a047;
}
.gp-svc-rental {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 8px;
  margin-top: 12px;
}
.gp-svc-rental-pill {
  min-width: 0;
  padding: 9px 10px;
  border: 1px solid rgba(109,76,91,0.16);
  border-radius: 8px;
  background: rgba(109,76,91,0.05);
}
.gp-svc-rental-label {
  display: flex;
  align-items: center;
  gap: 5px;
  color: var(--c-accent);
  font-size: 10px;
  font-weight: 800;
  text-transform: uppercase;
}
.gp-svc-rental-value {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 6px;
  margin-top: 6px;
}
.gp-svc-rental-price {
  min-width: 0;
}
.gp-svc-rental-price small {
  display: block;
  color: var(--c-muted);
  font-size: 9px;
  font-weight: 800;
  line-height: 1.2;
  text-transform: uppercase;
}
.gp-svc-rental-price strong {
  display: block;
  color: var(--c-strong);
  font-size: 12px;
  font-weight: 800;
  line-height: 1.2;
  overflow-wrap: anywhere;
}
.gp-svc-rental-meta {
  display: block;
  margin-top: 2px;
  color: var(--c-muted);
  font-size: 10px;
  line-height: 1.3;
}
.gp-svc-foot {
  display: flex; align-items: center; justify-content: space-between; gap: 10px;
  margin-top: 10px; padding-top: 10px;
  border-top: 1px solid var(--c-rule);
}
.gp-svc-price {
  font-family: var(--font-display);
  font-size: 20px; font-weight: 600;
  color: var(--c-strong); line-height: 1;
}
.gp-svc-add-btn {
  display: inline-flex; align-items: center; gap: 4px;
  height: 34px; padding: 0 14px; border-radius: 999px;
  border: 1px solid var(--c-rule);
  background: var(--c-white); color: var(--c-strong);
  font-size: 12px; font-weight: 700; cursor: pointer;
  transition: all 0.2s var(--ease-out-expo); white-space: nowrap;
}
.gp-svc-add-btn:hover {
  background: var(--c-strong); color: #fcf8f5; border-color: var(--c-strong);
}
.gp-addon-section .gp-svc-card {
  min-height: 390px;
  border-radius: 14px;
  background: #fff8ef;
  border: 1.5px solid rgba(216,180,106,.68);
  box-shadow: 0 14px 34px rgba(63,36,26,.12);
}
.gp-addon-section .gp-svc-img {
  aspect-ratio: 16 / 10;
}
.gp-addon-section .gp-svc-name {
  font-family: var(--font-body);
  font-size: 16px;
  font-weight: 800;
}
.gp-addon-section .gp-svc-add-btn {
  min-height: 42px;
  padding: 0 22px;
  border: 0;
  border-radius: 6px;
  background: #6D4C5B;
  color: #fff8ef;
  font-size: 12px;
  font-weight: 800;
  letter-spacing: .06em;
  text-transform: uppercase;
  box-shadow: 0 12px 28px rgba(109,76,91,.22);
}
.gp-addon-section .gp-svc-add-btn:hover {
  background: #563847;
  color: #fff8ef;
  transform: translateY(-1px);
  box-shadow: 0 18px 36px rgba(109,76,91,.28);
}
.gp-addon-section .gp-track {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 26px 24px;
  align-items: start;
}
.gp-addon-section .gp-card {
  position: relative;
  display: flex;
  flex-direction: column;
  height: 430px;
  min-height: 430px;
  padding: 12px;
  overflow: hidden;
  border: 1px solid rgba(201,193,187,.58);
  border-radius: 14px;
  background: #fff8ef;
  box-shadow: 0 18px 42px rgba(63,36,26,.13);
  cursor: pointer;
  transition: transform .22s var(--ease-out-expo), box-shadow .22s var(--ease-out-expo), border-color .22s var(--ease-out-expo);
}
.gp-addon-section .gp-card:hover {
  transform: translateY(-7px);
  border-color: rgba(154,104,127,.28);
  box-shadow: 0 24px 52px rgba(63,36,26,.17);
}
.gp-addon-section .gc-body {
  display: flex;
  flex-direction: column;
  height: 100%;
}
.gp-addon-section .gc-image-frame {
  order: 1;
  position: relative;
  height: 250px;
  overflow: hidden;
  border: 0;
  border-radius: 8px;
  background: linear-gradient(160deg, #ede0d0, #ddcebb);
}
.gp-addon-section .gc-image-frame img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 8px;
  transition: transform .45s var(--ease-out-expo);
}
.gp-addon-section .gp-card:hover .gc-image-frame img {
  transform: scale(1.035);
}
.gp-addon-section .gc-image-placeholder {
  display: grid;
  place-items: center;
  height: 100%;
  color: var(--c-pale);
  opacity: .45;
}
.gp-addon-section .gc-top {
  order: 2;
  margin: 14px 4px 0;
}
.gp-addon-section .gc-name {
  display: -webkit-box;
  margin-bottom: 3px;
  overflow: hidden;
  color: #211d1a;
  font-family: var(--font-body);
  font-size: 16px;
  font-weight: 700;
  line-height: 1.25;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}
.gp-addon-section .gc-sup {
  overflow: hidden;
  color: #6f625a;
  font-size: 13px;
  font-weight: 700;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.gp-addon-section .gc-location {
  order: 3;
  margin: 7px 4px 0;
  overflow: hidden;
  color: #7f6758;
  font-size: 12px;
  font-weight: 600;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.gp-addon-section .gc-tags {
  position: absolute;
  left: 12px;
  bottom: 12px;
  z-index: 2;
  display: block;
  margin: 0;
}
.gp-addon-section .gc-tag {
  display: inline-flex;
  padding: 5px 10px;
  border: 1px solid rgba(154,104,127,.14);
  border-radius: 7px;
  background: #f0dfe7;
  color: #7E4F65;
  font-size: 11px;
  font-weight: 800;
}
.gp-addon-section .gc-stats {
  order: 5;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  margin: auto 4px 0;
}
.gp-addon-section .gc-stat {
  min-width: 0;
  text-align: left;
}
.gp-addon-section .gc-stat strong {
  display: block;
  overflow: hidden;
  color: #211d1a;
  font-size: 15px;
  font-weight: 900;
  line-height: 1.2;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.gp-addon-section .gc-stat span {
  color: #8f7666;
  font-size: 11px;
  font-weight: 700;
}
.gp-addon-section .gc-book-btn {
  display: inline-flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  min-height: 48px;
  padding: 6px 8px 6px 20px;
  border: 1px solid rgba(154,104,127,.22);
  border-radius: 12px;
  background: #6D4C5B;
  color: #fff8ef;
  font-size: 13px;
  font-weight: 800;
  white-space: nowrap;
  transition: background .18s var(--ease-out-expo), transform .18s var(--ease-out-expo);
}
.gp-addon-section .gc-book-btn:hover {
  background: #7E4F65;
  transform: translateY(-1px);
}
.gp-addon-section .gc-book-btn-icon {
  display: inline-grid;
  place-items: center;
  width: 30px;
  height: 30px;
  flex: 0 0 auto;
  border-radius: 50%;
  background: #fff8ef;
  color: #6D4C5B;
}
.gp-addon-section .gc-book-btn-icon svg {
  width: 15px;
  height: 15px;
  stroke: currentColor;
}

/* ─── EMPTY SERVICE ──────────────────────── */
.gp-cat-empty {
  border: 1px dashed var(--c-rule);
  border-radius: var(--r-card);
  padding: 32px 20px;
  text-align: center;
  color: var(--c-pale);
  font-size: 13px;
  grid-column: 1 / -1;
}

/* ─── FOOTER ─────────────────────────────── */
.gp-footer {
  padding: 28px var(--pad-x);
  border-top: 1px solid var(--c-rule);
  display: flex; align-items: center; justify-content: space-between; gap: 16px;
  font-size: 12px; color: var(--c-pale);
}

/* ─── REVEAL ─────────────────────────────── */
.gp-reveal {
  opacity: 0; transform: translateY(24px);
  transition: opacity 0.6s var(--ease-out-expo), transform 0.6s var(--ease-out-expo);
}
.gp-reveal.visible { opacity: 1; transform: translateY(0); }
.gp-reveal-d1 { transition-delay: 0.04s; }
.gp-reveal-d2 { transition-delay: 0.08s; }
.gp-reveal-d3 { transition-delay: 0.12s; }

@media (max-width: 900px) {
  .gp-package-content-grid { grid-template-columns: 1fr; }
  .gp-package-main-image,
  .gp-package-main-image img,
  .gp-package-image-placeholder {
    height: 340px;
    min-height: 340px;
  }
  .gp-package-description-card { min-height: auto; }
  .gp-package-facility-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
  .gp-svc-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
  .gp-addon-section .gp-track { grid-template-columns: repeat(2, minmax(0, 1fr)); }
  .gp-how-it-works { grid-template-columns: 1fr; gap: 16px; }
}
@media (max-width: 700px) {
  .gp-svc-grid { grid-template-columns: 1fr; }
  .gp-addon-section .gp-track { grid-template-columns: 1fr; gap: 20px; }
  .gp-addon-section .gp-card { height: 390px; min-height: 390px; }
  .gp-addon-section .gc-image-frame { height: 210px; }
  .gp-package-facility-grid { grid-template-columns: 1fr; }
  .gp-package-main-image,
  .gp-package-main-image img,
  .gp-package-image-placeholder {
    height: 320px;
    min-height: 320px;
  }
  .gp-package-image-title {
    left: 20px;
    right: 20px;
    bottom: 96px;
  }
  .gp-package-hero-form {
    left: 20px;
    right: 20px;
    bottom: 22px;
  }
  .gp-package-hero-form .gp-package-cart-btn {
    width: 100%;
  }
  .gp-included-item {
    grid-template-columns: 92px minmax(0, 1fr);
    min-height: 112px;
  }
  .gp-included-thumb {
    width: 92px;
    height: 72px;
  }
  .gp-included-name { font-size: 16px; }
  .gp-included-price { font-size: 12px; }
  .gp-header-nav a { display: none; }
  .gp-detail-page { padding-bottom: 40px; }
}
@media (max-width: 480px) {
  :root { --pad-x: 16px; }
}

</style>
</head>
<body>

<div class="gp-texture" aria-hidden="true"></div>

<?php $gpNavActive = 'packages'; $gpNavOverlay = true; require APPROOT . '/views/layouts/customerHomeNav.php'; ?>

<main class="gp-detail-page">

  <section class="gp-package-detail-shell gp-reveal" aria-label="Package details">
    <div class="gp-package-media-column">
      <div class="gp-package-main-image">
        <?php if ($packageImage !== ''): ?>
          <img src="<?= $h($packageImage) ?>" alt="<?= $h($package['name'] ?? 'Package') ?>">
        <?php else: ?>
          <div class="gp-package-image-placeholder">
            <i data-lucide="image" style="width:44px;height:44px"></i>
          </div>
        <?php endif; ?>
        <div class="gp-package-image-title">
          <span class="gp-package-type-tag"><?= $h($packageTierLabel) ?></span>
          <h1><?= $h($package['name'] ?? 'Package') ?></h1>
        </div>
        <form class="gp-package-cart-form gp-package-hero-form" method="POST" action="<?= URLROOT ?>/cart/addPackage">
          <input type="hidden" name="package_id" value="<?= (int)($package['package_id'] ?? 0) ?>">
          <input type="hidden" name="price" value="<?= $packageCustomerPrice ?>">
          <input type="hidden" name="selected_date" class="pkg-selected-date" value="">
          <button class="gp-package-cart-btn" type="submit">
            <span>Book now</span>
            <span class="gc-book-btn-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17 17 7"/><path d="M9 7h8v8"/></svg>
            </span>
          </button>
        </form>
      </div>
    </div>

    <div class="gp-package-content-grid">
      <div class="gp-included-services-block">
        <h2>Included services</h2>
        <p class="gp-included-subtitle"><?= count($includedServices) ?> service<?= count($includedServices) === 1 ? '' : 's' ?> selected for this package</p>
        <?php if (empty($includedServices)): ?>
          <div class="gp-cat-empty">
            <p>No services are currently included in this package.</p>
          </div>
        <?php else: ?>
          <div class="gp-included-list">
            <?php foreach ($includedServices as $svc): ?>
              <?php $detailUrl = $serviceDetailUrl($svc); ?>
              <a class="gp-included-item" href="<?= $h($detailUrl) ?>">
  <span class="gp-included-thumb">
    <?php if (!empty($svc['image'])): ?>
      <img src="<?= $h($svc['image']) ?>" alt="<?= $h($svc['name'] ?? '') ?>" loading="lazy">
    <?php endif; ?>
  </span>

  <span class="gp-included-copy">
    <span class="gp-included-category"><?= $h($svc['category_name'] ?? '') ?></span>
    <span class="gp-included-name"><?= $h($svc['name'] ?? '') ?></span>
    <span class="gp-included-price"><?= $moneyRange($svc) ?></span>
  </span>
</a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <aside class="gp-package-description-card" aria-label="Package description">
        <h2>Description</h2>
        <p><?= $h(trim((string)($package['description'] ?? '')) !== '' ? $package['description'] : 'A curated wedding package designed to bring your selected services together with Golden Promise care and coordination.') ?></p>
        <div class="gp-package-description-price">
          <span>Sub Total</span>
          <strong><?= $money($packageCustomerPrice) ?></strong>
        </div>
      </aside>
    </div>
  </section>

  <?php if (!empty($addonServices)): ?>
    <section class="gp-cat-section gp-addon-section gp-reveal" aria-label="Optional package add-ons">
      <div class="gp-cat-header">
        <h2>Optional add-ons</h2>
        <span class="gp-cat-count">Extra services priced separately</span>
      </div>
      <div class="gp-track">
        <?php foreach ($addonServices as $si => $svc): ?>
          <?php $addonUrl = $addonDetailUrl($svc); ?>
          <article class="gp-card gp-reveal gp-reveal-d<?= min($si % 4, 3) ?>" data-url="<?= $h($addonUrl) ?>" role="link" tabindex="0" aria-label="View details for <?= $h($svc['name'] ?? 'add-on service') ?>">
            <div class="gc-body">
              <a class="gc-image-frame" href="<?= $h($addonUrl) ?>" tabindex="-1" aria-hidden="true">
                <?php if (!empty($svc['image'])): ?>
                  <img src="<?= $h($svc['image']) ?>" alt="<?= $h($svc['name'] ?? '') ?>" loading="lazy">
                <?php else: ?>
                  <span class="gc-image-placeholder"><i data-lucide="image" style="width:24px;height:24px"></i></span>
                <?php endif; ?>
                <span class="gc-tags">
                  <span class="gc-tag"><?= $h($svc['category_name'] ?? $svc['category'] ?? 'Service') ?></span>
                </span>
              </a>

              <div class="gc-top">
                <div class="gc-name"><?= $h($svc['name'] ?? 'Add-on service') ?></div>
                <div class="gc-sup"><?= $h($svc['supplier_name'] ?? 'Golden Promise') ?></div>
              </div>

              <div class="gc-location"><?= $h($addonLocation($svc)) ?></div>

              <div class="gc-stats">
                <div class="gc-stat">
                  <strong><?= $money($svc['display_price'] ?? $svc['price'] ?? 0) ?></strong>
                  <span><?= $h($addonDurationText($svc)) ?></span>
                </div>
                <a class="gc-book-btn" href="<?= $h($addonUrl) ?>">
                  <span>Add</span>
                  <span class="gc-book-btn-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                  </span>
                </a>
              </div>
            </div>
          </article>
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

  document.querySelectorAll('.gp-addon-section .gp-card[data-url]').forEach(card => {
    const openCard = () => {
      const url = card.getAttribute('data-url');
      if (url) window.location.href = url;
    };
    card.addEventListener('click', event => {
      if (event.target.closest('a, button')) return;
      openCard();
    });
    card.addEventListener('keydown', event => {
      if (event.key !== 'Enter' && event.key !== ' ') return;
      event.preventDefault();
      openCard();
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

// Package cart form - check login
document.querySelector('.gp-package-cart-form')?.addEventListener('submit', function(e) {
  var isLoggedIn = <?= $isLoggedIn ? 'true' : 'false' ?>;
  if (!isLoggedIn) {
    e.preventDefault();
    showAuthModal();
    return false;
  }
});
</script>

<!-- Auth Required Modal -->
<div id="authRequiredModal" style="display:none;position:fixed;inset:0;z-index:9999;align-items:center;justify-content:center;background:rgba(0,0,0,0.5);backdrop-filter:blur(4px);">
  <div style="background:#fdf8f3;border-radius:16px;padding:32px;max-width:400px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,0.3);text-align:center;position:relative;animation:modalIn 0.3s ease-out;">
    <button onclick="closeAuthModal()" style="position:absolute;top:12px;right:16px;background:none;border:none;font-size:24px;cursor:pointer;color:#7a6255;">&times;</button>
    <div style="font-size:48px;margin-bottom:16px;">💍</div>
    <h2 style="font-family:'Playfair Display',serif;font-size:24px;color:#211d1a;margin:0 0 8px;">Sign in to Book</h2>
    <p style="color:#7a6255;font-size:14px;margin:0 0 24px;line-height:1.5;">Create an account or sign in to add this package to your cart and complete your booking.</p>
    <a href="<?= URLROOT ?>/users/auth" style="display:block;width:100%;padding:14px;background:linear-gradient(135deg,#b8860b,#d4a574);color:#fff;border:none;border-radius:10px;font-size:16px;font-weight:600;cursor:pointer;text-decoration:center;margin-bottom:10px;font-family:'Poppins',sans-serif;">Sign In</a>
    <a href="<?= URLROOT ?>/users/register" style="display:block;width:100%;padding:14px;background:transparent;color:#7a6255;border:1.5px solid #d4a574;border-radius:10px;font-size:16px;font-weight:600;cursor:pointer;text-decoration:center;font-family:'Poppins',sans-serif;">Create Account</a>
  </div>
</div>
<style>
@keyframes modalIn {
  from { transform: scale(0.9); opacity: 0; }
  to { transform: scale(1); opacity: 1; }
}
</style>
<script>
function showAuthModal() {
  var modal = document.getElementById('authRequiredModal');
  modal.style.display = 'flex';
  document.body.style.overflow = 'hidden';
}
function closeAuthModal() {
  var modal = document.getElementById('authRequiredModal');
  modal.style.display = 'none';
  document.body.style.overflow = '';
}
document.getElementById('authRequiredModal').addEventListener('click', function(e) {
  if (e.target === this) closeAuthModal();
});
</script>

</body>
</html>
