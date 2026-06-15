<?php
$service = $service ?? [];
$media = $service['media'] ?? [];
$availability = $service['availability'] ?? ['weekly' => [], 'overrides' => [], 'upcoming' => []];
$fallbackImage = IMG_ROOT . '/uploads/suppliers/20/service-management/service/20260610150543-6e1176d1.jpg';
$heroImage = trim((string)($media[0]['file_url'] ?? $service['image'] ?? '')) ?: $fallbackImage;
$upcoming = $availability['upcoming'] ?? [];
$selectedDate = trim((string)($selectedDate ?? $service['selected_date'] ?? ''));
$selectedDateLabel = $selectedDate !== '' ? date('l, M j, Y', strtotime($selectedDate)) : '';
$datePickerMin = $service['earliest_booking_date'] ?? date('Y-m-d');
$datePickerMax = date('Y-m-d', strtotime('+365 days'));
$datePickerAction = URLROOT . '/customerServices/detail/' . (int)($service['id'] ?? 0);
$venueRooms = $service['venue_rooms'] ?? [];
$category = strtolower(trim((string)($service['category'] ?? '')));
$isVenue = ($detailPageType ?? '') === 'venue' || $category === 'venue';
$bookingType = $service['booking_type'] ?? 'fullday';
$isSlotBooking = $bookingType === 'slot';
$reviews = $service['reviews'] ?? [];
$related = $service['related'] ?? [];
$rating = (float)($service['rating'] ?? 0);
$reviewCount = (int)($service['review_count'] ?? count($reviews));
$firstAvailable = $upcoming[0] ?? null;
$firstSlot = null;
if (!$isVenue) {
    foreach ($upcoming as $day) {
        if (!empty($day['slots'][0])) {
            $firstSlot = $day['slots'][0];
            break;
        }
    }
}
$availableVenueRooms = array_values(array_filter($venueRooms, function ($room) use ($selectedDate) {
    if ($selectedDate === '') {
        return false;
    }
    return !array_key_exists('is_available_on_date', $room) || !empty($room['is_available_on_date']);
}));
$firstVenueRoom = $availableVenueRooms[0] ?? null;
$hasInitialBookOption = $isVenue ? $firstVenueRoom !== null : $firstSlot !== null;
$initialBookingHref = $hasInitialBookOption ? URLROOT . '/users/auth' : '#detail-date';
$initialBookingLabel = $hasInitialBookOption ? 'Add to cart' : (($isVenue || !$isSlotBooking) ? 'Choose date' : 'Choose slot');
$venueCapacity = !empty($venueRooms) ? max(array_map(function ($room) {
    return (int)($room['capacity'] ?? 1);
}, $venueRooms)) : (int)($service['max_concurrent'] ?? 1);
$isLoggedIn = !empty($_SESSION['session_uid']);
$authNavUrl = $isLoggedIn ? URLROOT . '/users/logout' : URLROOT . '/users/auth';
$authNavLabel = $isLoggedIn ? 'Logout' : 'Sign in';

$description = trim((string)($service['description'] ?? ''));
$supplierName = $service['supplier_name'] ?? 'Golden Promise supplier';
$supplierDesc = trim((string)($service['supplier_description'] ?? ''));
$supplierUrl = $service['supplier_url'] ?? '';
$detailDateQuery = $selectedDate !== '' ? '?date=' . rawurlencode($selectedDate) : '';

$plain = function ($value) {
    $text = (string)$value;
    for ($i = 0; $i < 10; $i++) {
        $decoded = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if ($decoded === $text) {
            break;
        }
        $text = $decoded;
    }

    return $text;
};

$h = function ($value) use ($plain) {
    return htmlspecialchars($plain($value), ENT_QUOTES, 'UTF-8');
};

$money = function ($value) {
    return 'RM ' . number_format((float)$value, 0);
};

$moneyRange = function ($service) use ($money) {
    return $money($service['display_price'] ?? $service['customize_price'] ?? $service['price_max'] ?? $service['price'] ?? 0);
};
$activeServicePrice = (float)($service['display_price'] ?? $service['customize_price'] ?? $service['price_max'] ?? $service['price'] ?? 0);
$isPackageContext = ($service['price_context'] ?? '') === 'package' && !empty($service['package_context']);
$packageContext = $isPackageContext ? ($service['package_context'] ?? []) : [];
$packageName = trim((string)($packageContext['package_name'] ?? 'Wedding package'));
$packageSlug = trim((string)($packageContext['package_slug'] ?? ''));
$packageDetailUrl = $packageSlug !== ''
    ? URLROOT . '/customerServices/packageDetail/' . rawurlencode($packageSlug)
    : URLROOT . '/customerServices/packages';
$packageServicePrice = (float)($packageContext['package_price'] ?? $activeServicePrice);
$standalonePrice = (float)($service['standalone_price'] ?? 0);
if ($isPackageContext && $isVenue && (float)($packageContext['venue_room_price'] ?? 0) > 0) {
    $standalonePrice = (float)$packageContext['venue_room_price'];
}
$packageSavings = max(0, $standalonePrice - $packageServicePrice);
$packageQueryFields = $isPackageContext
    ? [
        'package_id' => (int)($packageContext['package_id'] ?? 0),
        'package_item_id' => (int)($packageContext['package_item_id'] ?? 0),
    ]
    : [];

$timeRange = function ($from, $to) {
    $from = trim((string)$from);
    $to = trim((string)$to);
    if ($from === '' && $to === '') return 'Hours not set';
    $format = function ($value) {
        $timestamp = strtotime($value);
        return $timestamp ? date('H:i', $timestamp) : $value;
    };
    if ($from === '' || $from === $to) return $format($to ?: $from);
    $fromClock = substr($from, 0, 5);
    $toClock = substr($to, 0, 5);
    $overnight = $fromClock !== '' && $toClock !== '' && $toClock < $fromClock;
    return $format($from) . ' - ' . $format($to) . ($overnight ? ' (next day)' : '');
};

$durationText = function ($service) {
    $bookingType = $service['booking_type'] ?? 'fullday';
    $minutes = (int)($service['duration_minutes'] ?? 0);
    if ($bookingType === 'slot' && $minutes > 0) {
        $hours = $minutes / 60;
        return $hours >= 1 ? rtrim(rtrim(number_format($hours, 1), '0'), '.') . ' hour reservation' : $minutes . ' minute reservation';
    }
    return $bookingType === 'flexible' ? 'Flexible reservation' : 'Full day reservation';
};

$pricingUnitLabel = function ($service) {
    $unit = $service['pricing_unit'] ?? 'per_session';
    return $unit === 'per_hour' ? 'per hour' : 'per session';
};

$ratingBuckets = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
foreach ($reviews as $review) {
    $bucket = max(1, min(5, (int)round((float)($review['rating'] ?? 0))));
    $ratingBuckets[$bucket]++;
}
if (array_sum($ratingBuckets) === 0 && $reviewCount > 0 && $rating > 0) {
    $ratingBuckets[max(1, min(5, (int)round($rating)))] = $reviewCount;
}
$maxBucket = max(1, max($ratingBuckets));
$firstMediaType = !empty($media) ? ($media[0]['type'] ?? 'image') : 'image';
$heroBgUrl = $firstMediaType === 'video'
    ? ($media[1]['file_url'] ?? $service['image'] ?? $fallbackImage)
    : $heroImage;
$heroItems = array_values(array_filter($media, function ($m) {
    return trim((string)($m['file_url'] ?? ''));
}));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $h($service['name'] ?? 'Service') ?> | <?= APPNAME ?></title>
<?php $appCssVersion = file_exists(APPROOT . '/../public/css/app.css') ? filemtime(APPROOT . '/../public/css/app.css') : time(); ?>
<link rel="stylesheet" href="<?= URLROOT ?>/public/css/app.css?v=<?= $appCssVersion ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800;900&family=DM+Sans:wght@300;400;500;600;700;800;900&display=swap">
<link rel="stylesheet" href="https://unpkg.com/aos@2.3.4/dist/aos.css">
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
<script src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
<style>
/* ─── TOKENS ────────────────────────────────────────── */
:root {
  --page: #F5E8D9;
  --page-dark: #EADCCD;
  --panel: #FFF8EF;
  --panel-strong: #FFFFFF;
  --cream: #F8F2EC;
  --ink: #211D1A;
  --ink-soft: #3A2E29;
  --muted: #6F625A;
  --muted-light: #9A8C84;
  --wine: #B94A48;
  --wine-dark: #7F2F2D;
  --wine-glow: rgba(185, 74, 72, 0.10);
  --gold: #D8B46A;
  --sage: #765A46;
  --green: #2a7a4b;
  --line: rgba(118, 90, 70, 0.16);
  --line-soft: rgba(118, 90, 70, 0.08);

  --glass-bg: rgba(255, 248, 239, 0.72);
  --glass-strong: rgba(255, 248, 239, 0.92);
  --glass-border: rgba(255, 255, 255, 0.35);
  --glass-shadow: 0 8px 32px rgba(74, 52, 47, 0.10);

  --shadow-sm: 0 4px 12px rgba(74, 52, 47, 0.06);
  --shadow: 0 24px 58px rgba(74, 52, 47, 0.16);
  --shadow-lg: 0 40px 100px rgba(74, 52, 47, 0.20);
  --shadow-glow: 0 0 30px rgba(185, 74, 72, 0.12);

  --radius: 8px;
  --radius-lg: 12px;
  --radius-xl: 20px;
  --radius-2xl: 32px;
  --radius-full: 999px;

  --font-serif: 'Playfair Display', Georgia, serif;
  --font-sans: 'DM Sans', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;

  --ease-out-expo: cubic-bezier(0.19, 1, 0.22, 1);
  --ease-spring: cubic-bezier(0.34, 1.56, 0.64, 1);
  --duration-slow: 1000ms;
  --duration-base: 500ms;

  --pad-section: clamp(60px, 8vw, 120px);
}

/* ─── RESET ─────────────────────────────────────────── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

body {
  background: var(--page);
  color: var(--ink);
  font-family: var(--font-sans);
  font-size: 14px;
  line-height: 1.6;
  -webkit-font-smoothing: antialiased;
  overflow-x: hidden;
}

a { color: inherit; text-decoration: none; }
img { display: block; width: 100%; height: 100%; object-fit: cover; }
button, input, select, textarea { font-family: var(--font-sans); }

::-webkit-scrollbar { width: 6px; }
::-webkit-scrollbar-track { background: var(--page); }
::-webkit-scrollbar-thumb { background: var(--muted-light); border-radius: 999px; }

/* ─── KEYFRAMES ────────────────────────────────────── */
@keyframes heroZoom {
  0%   { transform: scale(1.12); }
  100% { transform: scale(1); }
}

@keyframes heroReveal {
  0%   { opacity: 0; transform: translateY(30px); }
  100% { opacity: 1; transform: translateY(0); }
}

@keyframes float {
  0%, 100% { transform: translateY(0); }
  50%      { transform: translateY(-5px); }
}

@keyframes bounceDown {
  0%, 100% { transform: translateY(0); opacity: 0.5; }
  50%      { transform: translateY(8px); opacity: 1; }
}

@keyframes pulseGlow {
  0%, 100% { box-shadow: 0 0 0 0 var(--wine-glow); }
  50%      { box-shadow: 0 0 0 16px transparent; }
}

@keyframes shimmer {
  0%   { background-position: -200% center; }
  100% { background-position: 200% center; }
}

@keyframes scaleIn {
  from { opacity: 0; transform: scale(0.95); }
  to   { opacity: 1; transform: scale(1); }
}

/* ─── PACKAGE CONTEXT UI ─────────────────────────────── */
.pkg-breadcrumb {
  display: flex; align-items: center; gap: 6px;
  flex-wrap: wrap;
  margin-top: 14px;
  padding: 0 clamp(20px, 4vw, 48px);
  font-size: 12px; font-weight: 600; color: var(--muted-light);
}
.pkg-breadcrumb a {
  color: var(--muted);
  transition: color 0.15s;
}
.pkg-breadcrumb a:hover { color: var(--wine); }
.pkg-breadcrumb-sep { color: var(--line); font-size: 10px; }
.pkg-breadcrumb-current {
  color: var(--wine-dark);
  font-weight: 700;
}

.pkg-banner {
  margin: 12px clamp(20px, 4vw, 48px) 0;
  padding: 14px 18px;
  border-radius: var(--radius-lg);
  background: linear-gradient(135deg, rgba(185, 74, 72, 0.07), rgba(185, 74, 72, 0.03));
  border: 1px solid rgba(185, 74, 72, 0.14);
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
  animation: scaleIn 0.4s var(--ease-out-expo);
}
.pkg-banner-icon {
  flex: 0 0 36px; width: 36px; height: 36px;
  display: grid; place-items: center;
  border-radius: 50%;
  background: rgba(185, 74, 72, 0.12);
  color: var(--wine);
}
.pkg-banner-text {
  flex: 1; min-width: 200px;
}
.pkg-banner-text strong {
  display: block;
  color: var(--ink);
  font-size: 13px; font-weight: 800;
}
.pkg-banner-text span {
  display: block;
  color: var(--muted);
  font-size: 12px; font-weight: 500;
  margin-top: 1px;
}
.pkg-banner-link {
  display: inline-flex; align-items: center; gap: 6px;
  height: 34px; padding: 0 16px;
  border-radius: 999px;
  background: var(--wine);
  color: #fff;
  font-size: 11px; font-weight: 800;
  white-space: nowrap;
  transition: background 0.15s, transform 0.2s var(--ease-spring);
}
.pkg-banner-link:hover {
  background: var(--wine-dark);
  transform: translateY(-1px);
}

.pkg-room-badge {
  display: inline-flex; align-items: center; gap: 5px;
  padding: 4px 12px 4px 10px;
  border-radius: 999px;
  background: linear-gradient(135deg, rgba(185, 74, 72, 0.10), rgba(185, 74, 72, 0.05));
  border: 1px solid rgba(185, 74, 72, 0.18);
  color: var(--wine);
  font-size: 10px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  white-space: nowrap;
  margin-bottom: 8px;
  width: fit-content;
}
.pkg-room-badge i { flex-shrink: 0; }

.pkg-room-lock {
  pointer-events: none;
  cursor: default;
}
.pkg-room-lock .radio-dot {
  border-color: var(--wine) !important;
  background: rgba(185, 74, 72, 0.08) !important;
  position: relative;
}
.pkg-room-lock .radio-dot::after {
  content: '';
  position: absolute; inset: 3px;
  border-radius: 50%;
  background: var(--wine);
  opacity: 0.5;
}
.pkg-room-lock:hover {
  transform: none !important;
  box-shadow: none !important;
}
.pkg-room-lock .slot-chip {
  cursor: default;
  background: rgba(185, 74, 72, 0.08);
  border-color: rgba(185, 74, 72, 0.25);
  color: var(--wine);
  opacity: 0.85;
  pointer-events: none;
}

.pkg-savings {
  padding: 16px 0;
  border-bottom: 1px solid var(--line-soft);
  text-align: center;
}
.pkg-savings-label {
  font-size: 10px;
  font-weight: 700;
  letter-spacing: 0.1em;
  text-transform: uppercase;
  color: var(--green);
  margin-bottom: 4px;
}
.pkg-savings-amount {
  font-family: var(--font-serif);
  font-size: 24px;
  font-weight: 700;
  color: var(--green);
  line-height: 1;
}
.pkg-savings-note {
  font-size: 11px;
  color: var(--muted);
  margin-top: 4px;
  font-weight: 500;
}
.pkg-price-compare {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  font-size: 11px;
  margin-top: 2px;
}
.pkg-price-strikethrough {
  color: var(--muted-light);
  text-decoration: line-through;
  font-weight: 500;
}
.pkg-price-package {
  color: var(--green);
  font-weight: 800;
}

/* ─── UTILITY ───────────────────────────────────────── */
.glass {
  background: var(--glass-bg);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  border: 1px solid var(--glass-border);
  box-shadow: var(--glass-shadow);
}

.glass-strong {
  background: var(--glass-strong);
  backdrop-filter: blur(24px);
  -webkit-backdrop-filter: blur(24px);
  border: 1px solid rgba(255, 255, 255, 0.4);
}

.section-label {
  display: inline-flex; align-items: center; gap: 8px;
  font-size: 11px; font-weight: 700; letter-spacing: 0.18em; text-transform: uppercase;
  color: var(--wine); margin-bottom: 12px;
}
.section-label::before {
  content: '';
  width: 20px; height: 1px; background: var(--wine); flex-shrink: 0;
}

.section-title {
  font-family: var(--font-serif);
  font-size: clamp(32px, 4.5vw, 56px);
  font-weight: 500;
  color: var(--ink);
  line-height: 1.08;
  letter-spacing: -0.01em;
}

.section-sub {
  margin-top: 12px;
  color: var(--muted);
  font-size: 15px;
  max-width: 520px;
  line-height: 1.7;
}

/* ─── TOP BAR ───────────────────────────────────────── */
.top-bar {
  position: fixed; top: 0; left: 0; right: 0; z-index: 50;
  display: flex; align-items: center; justify-content: space-between;
  padding: 12px clamp(20px, 4vw, 48px);
  background: transparent;
  transition: background 0.3s ease, backdrop-filter 0.3s ease;
}

.top-bar.scrolled {
  background: rgba(255, 248, 239, 0.95);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border-bottom: 1px solid var(--line);
}

.top-bar-brand {
  display: flex; align-items: center; gap: 8px;
  font-family: var(--font-serif);
  font-size: 20px; font-weight: 700;
  color: #fff; letter-spacing: 0.01em;
  text-shadow: 0 2px 8px rgba(0,0,0,0.2);
  transition: color 0.3s ease, text-shadow 0.3s ease;
}
.top-bar.scrolled .top-bar-brand {
  color: var(--wine-dark);
  text-shadow: none;
}

.top-bar-actions {
  display: flex; align-items: center; gap: 8px;
}

.top-pill {
  min-height: 36px;
  display: inline-flex; align-items: center; justify-content: center;
  padding: 0 16px;
  border-radius: 999px;
  background: rgba(255,255,255,0.12);
  backdrop-filter: blur(8px);
  border: 1px solid rgba(255,255,255,0.18);
  color: #fff;
  font-size: 12px; font-weight: 700;
  text-shadow: 0 1px 4px rgba(0,0,0,0.15);
  transition: background 0.2s ease, color 0.2s ease, border-color 0.2s ease;
}
.top-pill:hover { background: rgba(255,255,255,0.22); }

.top-bar.scrolled .top-pill {
  background: var(--cream);
  border-color: var(--line);
  color: var(--muted);
  text-shadow: none;
}
.top-bar.scrolled .top-pill:hover {
  background: var(--panel);
  color: var(--wine);
  border-color: rgba(185,74,72,0.24);
}

/* ─── TOP-BAR PROFILE DROPDOWN ────────────────────── */
.tb-profile-dropdown { position: relative; }

.tb-profile-btn {
  display: flex; align-items: center; gap: 6px;
  padding: 2px 10px 2px 2px;
  border-radius: 999px;
  background: rgba(255,255,255,0.12);
  backdrop-filter: blur(8px);
  border: 1px solid rgba(255,255,255,0.18);
  cursor: pointer;
  transition: all 0.2s;
  color: #fff;
  font-family: 'Poppins', system-ui, -apple-system, sans-serif;
  font-size: 12px;
  font-weight: 700;
  text-shadow: 0 1px 4px rgba(0,0,0,0.15);
}
.tb-profile-btn:hover { background: rgba(255,255,255,0.22); }

.top-bar.scrolled .tb-profile-btn {
  background: var(--cream);
  border-color: var(--line);
  color: var(--muted);
  text-shadow: none;
}
.top-bar.scrolled .tb-profile-btn:hover {
  background: var(--panel);
  color: var(--wine);
  border-color: rgba(185,74,72,0.24);
}

.tb-profile-avatar {
  display: grid; place-items: center;
  width: 28px; height: 28px;
  border-radius: 50%;
  background: #D8B46A;
  color: #3F2F24;
  font-size: 11px;
  font-weight: 800;
}
.top-bar.scrolled .tb-profile-avatar { background: var(--wine); color: #fff; }

.tb-profile-name { white-space: nowrap; max-width: 80px; overflow: hidden; text-overflow: ellipsis; }

.tb-profile-chevron { opacity: 0.7; transition: transform 0.2s; }
.tb-profile-btn[aria-expanded="true"] .tb-profile-chevron { transform: rotate(180deg); }

.tb-profile-menu {
  position: absolute; top: calc(100% + 6px); right: 0; z-index: 100;
  min-width: 170px;
  padding: 6px;
  border-radius: 12px;
  background: #fff;
  box-shadow: 0 12px 35px rgba(15,23,42,0.12);
  opacity: 0;
  visibility: hidden;
  transform: translateY(-4px);
  transition: all 0.15s ease;
  color: var(--text);
  text-shadow: none;
}
.tb-profile-btn[aria-expanded="true"] + .tb-profile-menu,
.tb-profile-menu.show {
  opacity: 1;
  visibility: visible;
  transform: translateY(0);
}

.tb-profile-menu-item {
  display: flex; align-items: center; gap: 10px;
  padding: 9px 12px;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 600;
  color: var(--text);
  transition: all 0.15s;
}
.tb-profile-menu-item:hover { background: rgba(109,76,91,0.06); color: var(--wine); }

.tb-profile-menu-item--danger { color: var(--danger); }
.tb-profile-menu-item--danger:hover { background: rgba(185,75,75,0.08); }

.package-context-strip {
  position: relative;
  z-index: 4;
  background: var(--panel);
  border-bottom: 1px solid var(--line);
  padding: 92px clamp(18px, 4vw, 56px) 18px;
}
.package-context-inner {
  max-width: 1180px;
  margin: 0 auto;
  display: grid;
  gap: 12px;
}
.package-breadcrumb {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 8px;
  color: var(--muted);
  font-size: 12px;
  font-weight: 800;
}
.package-breadcrumb a {
  color: var(--wine);
}
.package-breadcrumb-sep {
  color: var(--muted-light);
}
.package-banner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 18px;
  border: 1px solid rgba(185,74,72,0.18);
  border-left: 4px solid var(--wine);
  border-radius: var(--radius-lg);
  background: linear-gradient(135deg, rgba(185,74,72,0.08), rgba(216,180,106,0.10));
  padding: 14px 16px;
}
.package-banner-copy {
  display: grid;
  gap: 3px;
}
.package-banner-copy strong {
  color: var(--wine-dark);
  font-size: 13px;
  font-weight: 900;
}
.package-banner-copy span {
  color: var(--ink);
  font-size: 13px;
  font-weight: 600;
}
.package-banner-link {
  flex-shrink: 0;
  min-height: 34px;
  display: inline-flex;
  align-items: center;
  gap: 7px;
  border-radius: 999px;
  background: var(--wine);
  color: #fffaf7;
  padding: 0 14px;
  font-size: 12px;
  font-weight: 800;
}
.package-banner-link:hover {
  background: var(--wine-dark);
}

/* ─── CINEMATIC HERO ────────────────────────────────── */
.hero-cover {
  position: relative;
  width: 100%;
  height: 100vh;
  min-height: 600px;
  max-height: 1080px;
  overflow: hidden;
  display: grid;
  place-items: center;
}

.hero-cover-bg {
  position: absolute;
  inset: 0;
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
  animation: heroZoom 1.8s var(--ease-out-expo) forwards;
  will-change: transform;
}

.hero-cover-bg::after {
  content: '';
  position: absolute; inset: 0;
  background:
    linear-gradient(180deg,
      rgba(245, 232, 217, 0.08) 0%,
      rgba(33, 29, 26, 0.35) 45%,
      rgba(33, 29, 26, 0.80) 100%
    ),
    radial-gradient(ellipse at 50% 25%,
      transparent 0%,
      rgba(33, 29, 26, 0.20) 100%
    );
}

.hero-cover-content {
  position: relative;
  z-index: 2;
  text-align: center;
  padding: 0 24px;
  max-width: 820px;
  animation: heroReveal 1.2s var(--ease-out-expo) 0.3s both;
}

.hero-category {
  display: inline-block;
  font-size: 11px; font-weight: 700; letter-spacing: 0.2em; text-transform: uppercase;
  color: rgba(255,255,255,0.7);
  margin-bottom: 16px;
  padding: 6px 14px;
  border: 1px solid rgba(255,255,255,0.15);
  border-radius: 999px;
  backdrop-filter: blur(4px);
}

.hero-title {
  font-family: var(--font-serif);
  font-size: clamp(42px, 6vw, 88px);
  font-weight: 600;
  color: #fff;
  line-height: 1.0;
  letter-spacing: -0.02em;
  text-shadow: 0 2px 40px rgba(0,0,0,0.25);
}

.hero-sub {
  margin-top: 20px;
  display: flex; align-items: center; justify-content: center; gap: 24px;
  flex-wrap: wrap;
  color: rgba(255,255,255,0.75);
  font-size: 14px; font-weight: 500;
}

.hero-sub-item {
  display: flex; align-items: center; gap: 6px;
}
.hero-sub-item i { opacity: 0.6; }

.hero-scroll-indicator {
  position: absolute;
  bottom: 32px; left: 50%;
  transform: translateX(-50%);
  z-index: 3;
  color: rgba(255,255,255,0.5);
  animation: bounceDown 2s ease-in-out infinite;
  cursor: pointer;
  transition: color 0.2s;
}
.hero-scroll-indicator:hover { color: rgba(255,255,255,0.85); }

/* ─── PAGE SHELL ────────────────────────────────────── */
.page-shell {
  position: relative;
  z-index: 3;
  max-width: 1200px;
  margin: -80px auto 0;
  padding: 0 24px 60px;
}

/* ─── SECTION: GALLERY ──────────────────────────────── */
.section-gallery {
  background: linear-gradient(180deg, transparent 0%, var(--page) 60px);
  padding: 60px 0 0;
}

.gallery-frame {
  position: relative;
  border-radius: var(--radius-2xl);
  overflow: hidden;
  background: var(--cream);
  box-shadow: var(--shadow-lg);
}

.gallery-main {
  position: relative;
  height: clamp(320px, 45vw, 520px);
  cursor: pointer;
  background: var(--cream);
}

.gallery-main img,
.gallery-main video {
  width: 100%; height: 100%; display: block; object-fit: cover;
}

.gallery-video-overlay {
  position: absolute; inset: 0;
  display: grid; place-items: center;
  background: rgba(0,0,0,0.20);
  cursor: pointer;
  transition: opacity 0.25s;
}
.gallery-video-overlay.playing { opacity: 0; pointer-events: none; }

.gallery-play-btn {
  width: 64px; height: 64px;
  display: grid; place-items: center;
  border-radius: 50%;
  background: rgba(255,255,255,0.88);
  color: var(--wine);
  font-size: 28px;
  box-shadow: 0 8px 28px rgba(0,0,0,0.20);
  transition: transform 0.3s var(--ease-spring), background 0.2s;
}
.gallery-play-btn:hover { transform: scale(1.08); background: #fff; }

.gallery-nav-btn {
  position: absolute; top: 50%; transform: translateY(-50%); z-index: 5;
  width: 38px; height: 38px;
  display: grid; place-items: center;
  border: 0; border-radius: 50%;
  background: rgba(255,255,255,0.80);
  color: var(--ink);
  cursor: pointer;
  box-shadow: 0 2px 12px rgba(0,0,0,0.10);
  opacity: 0;
  transition: opacity 0.25s, background 0.15s, transform 0.15s;
}
.gallery-nav-btn:hover { background: #fff; transform: translateY(-50%) scale(1.08); }
.gallery-frame:hover .gallery-nav-btn { opacity: 1; }
.gallery-nav-btn.prev { left: 14px; }
.gallery-nav-btn.next { right: 14px; }

.gallery-dots {
  position: absolute; bottom: 14px; left: 50%; transform: translateX(-50%);
  display: flex; gap: 7px; z-index: 5;
}

.gallery-dot {
  width: 6px; height: 6px; border-radius: 50%;
  background: rgba(255,255,255,0.45);
  border: 0; cursor: pointer; padding: 0;
  transition: background 0.15s, width 0.2s var(--ease-spring);
}
.gallery-dot.active { background: #fff; width: 22px; border-radius: 4px; }

.gallery-thumbs {
  display: flex; gap: 8px; padding: 10px 14px 14px;
  overflow-x: auto; scrollbar-width: none;
}
.gallery-thumbs::-webkit-scrollbar { display: none; }

.gallery-thumb {
  flex: 0 0 72px; height: 52px;
  border-radius: var(--radius); overflow: hidden;
  border: 2px solid transparent;
  cursor: pointer; opacity: 0.5;
  transition: opacity 0.2s, border-color 0.2s, transform 0.3s var(--ease-spring);
  background: var(--cream);
  position: relative;
}
.gallery-thumb:hover { opacity: 0.85; transform: scale(1.05); }
.gallery-thumb.active { opacity: 1; border-color: var(--wine); }
.gallery-thumb img, .gallery-thumb video { width: 100%; height: 100%; object-fit: cover; pointer-events: none; }
.gallery-thumb-video {
  position: absolute; inset: 0;
  display: grid; place-items: center;
  background: rgba(0,0,0,0.15); color: #fff; font-size: 13px;
}

/* Quick stats strip below gallery */
.quick-stats {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1px;
  background: var(--line-soft);
  border-radius: var(--radius-xl);
  overflow: hidden;
  margin-top: 20px;
}

.quick-stat {
  background: var(--panel);
  padding: 18px 14px;
  text-align: center;
}

.quick-stat-value {
  font-family: var(--font-serif);
  font-size: 26px; font-weight: 600;
  color: var(--wine-dark);
  line-height: 1;
}
.quick-stat-label {
  font-size: 11px; font-weight: 600; color: var(--muted-light);
  margin-top: 4px; text-transform: uppercase; letter-spacing: 0.08em;
}

/* ─── SPLIT SECTIONS ───────────────────────────────── */
.split-section {
  display: grid;
  grid-template-columns: 1fr 380px;
  gap: 40px;
  align-items: start;
  margin-top: var(--pad-section);
}

.split-content { min-width: 0; }
.split-sidebar { min-width: 0; }

/* ─── SECTION CARD ──────────────────────────────────── */
.section-card {
  background: var(--panel);
  border: 1px solid var(--line);
  border-radius: var(--radius-xl);
  padding: clamp(28px, 4vw, 40px);
  box-shadow: var(--shadow);
}

.section-card + .section-card {
  margin-top: 24px;
}

.card-title {
  font-family: var(--font-serif);
  font-size: clamp(22px, 2.5vw, 28px);
  font-weight: 600;
  color: var(--ink);
  line-height: 1.1;
  margin-bottom: 6px;
}

.card-sub {
  color: var(--muted);
  font-size: 13px; font-weight: 500;
  margin-bottom: 20px;
}

/* ─── DESCRIPTION ───────────────────────────────────── */
.desc-text {
  color: var(--muted);
  font-size: 14.5px;
  line-height: 1.75;
  white-space: pre-line;
}

/* ─── GLASS STATS PANEL ─────────────────────────────── */
.glass-stats {
  position: sticky; top: 90px;
  background: var(--glass-bg);
  backdrop-filter: blur(18px);
  -webkit-backdrop-filter: blur(18px);
  border: 1px solid var(--glass-border);
  border-radius: var(--radius-xl);
  padding: clamp(22px, 3vw, 32px);
  box-shadow: var(--glass-shadow);
}

.stat-item {
  display: flex; align-items: center; gap: 12px;
  padding: 14px 0;
  border-bottom: 1px solid var(--line-soft);
}
.stat-item:first-child { padding-top: 0; }
.stat-item:last-child { border-bottom: 0; padding-bottom: 0; }

.stat-icon {
  flex: 0 0 36px; width: 36px; height: 36px;
  display: grid; place-items: center;
  border-radius: var(--radius);
  background: var(--wine-glow);
  color: var(--wine);
  font-size: 16px;
}

.stat-content { min-width: 0; }
.stat-content strong {
  display: block;
  color: var(--ink);
  font-size: 15px; font-weight: 800;
}
.stat-content span {
  display: block;
  color: var(--muted);
  font-size: 12px; font-weight: 500;
}

.stat-price {
  font-family: var(--font-serif);
  font-size: 28px; font-weight: 700;
  color: var(--wine-dark);
  line-height: 1;
}

/* ─── PORTFOLIO GRID ────────────────────────────────── */
.portfolio-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 10px;
}

.portfolio-item {
  position: relative;
  display: block;
  aspect-ratio: 4 / 3;
  overflow: hidden;
  border-radius: var(--radius-lg);
  background: var(--cream);
  cursor: pointer;
  transition: transform 0.4s var(--ease-out-expo), box-shadow 0.4s ease;
}
.portfolio-item:first-child { grid-column: span 2; grid-row: span 2; }
.portfolio-item:hover {
  transform: translateY(-3px) scale(1.02);
  box-shadow: var(--shadow-lg);
  z-index: 2;
}

.portfolio-item img,
.portfolio-item video {
  width: 100%; height: 100%; display: block; object-fit: cover;
  transition: transform 0.6s var(--ease-out-expo);
}
.portfolio-item:hover img,
.portfolio-item:hover video { transform: scale(1.06); }

.portfolio-vid-badge {
  position: absolute; inset: 0;
  display: grid; place-items: center;
  background: rgba(0,0,0,0.12);
  color: #fff; font-size: 30px;
  pointer-events: none;
  transition: background 0.25s;
}
.portfolio-item:hover .portfolio-vid-badge { background: rgba(0,0,0,0.22); }

/* ─── BOOKING SECTION ───────────────────────────────── */
.booking-section {
  margin-top: var(--pad-section);
}

.booking-grid {
  display: grid;
  grid-template-columns: 1fr 340px;
  gap: 28px;
  align-items: start;
  margin-top: 24px;
}

.date-picker-card {
  display: grid;
  grid-template-columns: minmax(0, 1fr) auto;
  gap: 18px;
  align-items: end;
  margin-top: 22px;
  padding: 18px;
  border: 1px solid var(--line);
  border-radius: var(--radius-xl);
  background: var(--glass-bg);
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
  box-shadow: var(--glass-shadow);
}

.date-picker-copy {
  display: grid;
  gap: 5px;
}

.date-picker-copy strong {
  color: var(--ink);
  font-size: 14px;
  font-weight: 800;
}

.date-picker-copy span {
  color: var(--muted);
  font-size: 12px;
  line-height: 1.5;
}

.date-picker-control {
  display: flex;
  align-items: center;
  gap: 10px;
}

.date-picker-control input {
  min-height: 42px;
  min-width: 170px;
  border: 1px solid var(--line);
  border-radius: var(--radius);
  background: var(--panel-strong);
  color: var(--ink);
  padding: 0 12px;
  font-size: 13px;
  font-weight: 700;
  outline: none;
}

.date-picker-control input:focus {
  border-color: rgba(185,74,72,0.38);
  box-shadow: 0 0 0 4px rgba(185,74,72,0.08);
}

.date-picker-btn {
  min-height: 42px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 7px;
  border: 0;
  border-radius: 999px;
  background: var(--wine);
  color: #fff;
  padding: 0 16px;
  font-size: 12px;
  font-weight: 800;
  cursor: pointer;
  transition: background 0.15s, transform 0.2s var(--ease-spring);
}

.date-picker-btn:hover {
  background: var(--wine-dark);
  transform: translateY(-1px);
}

.availability-list {
  display: grid;
  gap: 14px;
}

.availability-row {
  min-height: 72px;
  display: grid;
  grid-template-columns: 24px minmax(0, 1fr);
  gap: 14px;
  align-items: start;
  border: 1px solid var(--line);
  border-radius: var(--radius-lg);
  background: var(--panel-strong);
  padding: 14px 16px;
  transition: transform 0.3s var(--ease-out-expo), box-shadow 0.3s ease, border-color 0.2s ease;
  cursor: pointer;
}
.availability-row:hover,
.availability-row.is-selected {
  border-color: rgba(185,74,72,0.30);
  box-shadow: 0 12px 28px rgba(74,52,47,0.10);
  transform: translateY(-2px);
}

.availability-row.is-requested-date {
  border-color: rgba(216,180,106,0.58);
  background: linear-gradient(135deg, rgba(216,180,106,0.12), var(--panel-strong));
}

.availability-row.is-unavailable {
  cursor: default;
  opacity: 0.82;
}

.availability-row.is-unavailable:hover {
  transform: none;
}

.availability-row.is-package-selected {
  border-color: rgba(185,74,72,0.38);
  background:
    linear-gradient(135deg, rgba(185,74,72,0.10), rgba(216,180,106,0.11)),
    var(--panel-strong);
}

.radio-dot {
  width: 20px; height: 20px;
  border: 2px solid rgba(185,74,72,0.22);
  border-radius: 50%;
  background: #fff;
  margin-top: 5px;
  transition: border-width 0.15s;
  flex-shrink: 0;
}
.availability-row.is-selected .radio-dot {
  border: 6px solid var(--wine);
}

.availability-row.is-requested-date .radio-dot {
  border-color: var(--gold);
}

.availability-row.is-unavailable .radio-dot {
  border-color: rgba(118,90,70,0.14);
  background: rgba(118,90,70,0.05);
}

.availability-name {
  display: grid; gap: 4px;
  color: var(--ink);
  font-size: 15px; font-weight: 800;
}
.availability-name span {
  color: var(--muted);
  font-size: 12px; font-weight: 500;
}

.availability-head {
  display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;
}

.availability-status {
  border-radius: 999px;
  background: rgba(216,180,106,0.20);
  color: var(--sage);
  padding: 6px 10px;
  font-size: 12px; font-weight: 800;
  white-space: nowrap;
  flex-shrink: 0;
}

.package-hall-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  width: max-content;
  margin-top: 8px;
  border-radius: 999px;
  background: rgba(185,74,72,0.12);
  color: var(--wine-dark);
  padding: 6px 10px;
  font-size: 11px;
  font-weight: 900;
}

.slot-options {
  display: flex; flex-wrap: wrap; gap: 8px;
  margin-top: 12px;
}

.slot-chip {
  min-height: 34px;
  display: inline-flex; align-items: center;
  border: 1px solid rgba(185,74,72,0.18);
  border-radius: 999px;
  background: rgba(185,74,72,0.05);
  color: var(--wine);
  padding: 0 12px;
  font-size: 12px; font-weight: 800;
  cursor: pointer;
  transition: background 0.15s, color 0.15s, border-color 0.15s, transform 0.2s var(--ease-spring);
}
.slot-chip:hover { background: rgba(185,74,72,0.12); transform: scale(1.03); }
.slot-chip input { position: absolute; opacity: 0; pointer-events: none; }
.slot-chip:has(input:checked) {
  background: var(--wine); border-color: var(--wine);
  color: #fffaf7; transform: scale(1.03);
}
.slot-chip.is-locked {
  cursor: help;
  background: var(--wine);
  border-color: var(--wine);
  color: #fffaf7;
}

.availability-radio-input {
  position: absolute;
  opacity: 0;
  pointer-events: none;
}

.availability-range {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  margin-top: 10px;
  color: var(--ink);
  font-size: 13px;
  font-weight: 800;
}

/* ─── STICKY SUMMARY ────────────────────────────────── */
.sticky-summary {
  position: sticky; top: 90px;
  background: var(--glass-strong);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border: 1px solid var(--glass-border);
  border-radius: var(--radius-xl);
  padding: clamp(20px, 3vw, 28px);
  box-shadow: var(--glass-shadow);
}

.summary-fields {
  border-radius: var(--radius-lg);
  background: var(--cream);
  padding: 20px;
}

.summary-line {
  display: grid; gap: 4px;
  padding: 12px 0;
  border-bottom: 1px solid var(--line-soft);
  font-size: 11px; font-weight: 700;
  letter-spacing: 0.08em; text-transform: uppercase;
  color: var(--muted);
}
.summary-line:first-child { padding-top: 0; }
.summary-line:last-child { border-bottom: 0; padding-bottom: 0; }
.summary-line span {
  color: var(--ink);
  font-size: 14px; font-weight: 800;
  letter-spacing: 0; text-transform: none;
}

.estimated-row {
  display: flex; align-items: center; justify-content: space-between;
  margin: 20px 0 16px;
  color: var(--muted);
  font-size: 13px; font-weight: 700;
}
.estimated-row strong {
  color: var(--wine-dark);
  font-family: var(--font-serif);
  font-size: 26px; font-weight: 700;
}

.package-price-panel {
  display: grid;
  gap: 8px;
  border-top: 1px solid var(--line-soft);
  border-bottom: 1px solid var(--line-soft);
  padding: 14px 0;
  margin: 14px 0 6px;
}
.package-price-line {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  color: var(--muted);
  font-size: 12px;
  font-weight: 800;
}
.package-price-line strong {
  color: var(--ink);
  font-size: 14px;
}
.package-price-line.is-package strong {
  color: var(--wine-dark);
  font-family: var(--font-serif);
  font-size: 22px;
}
.package-price-line.is-saving {
  color: var(--sage);
}
.package-price-line.is-saving strong {
  color: var(--sage);
}

.summary-actions {
  display: flex; align-items: center; gap: 12px;
}

.btn-cart {
  flex: 1; min-height: 48px;
  display: inline-flex; align-items: center; justify-content: center; gap: 10px;
  border: 0; border-radius: 999px;
  background: var(--wine); color: #fffaf7;
  font: inherit; font-size: 14px; font-weight: 800;
  cursor: pointer;
  transition: background 0.2s ease, transform 0.3s var(--ease-spring), box-shadow 0.2s ease;
}
.btn-cart:hover {
  background: var(--wine-dark);
  transform: translateY(-2px);
  box-shadow: var(--shadow-glow);
}
.btn-cart:active { transform: scale(0.97); }
.btn-cart.is-submitting,
.mobile-book-btn.is-submitting {
  background: var(--sage);
  pointer-events: none;
}
.btn-cart.is-guidance,
.mobile-book-btn.is-guidance {
  background: var(--sage);
}
.btn-cart.is-guidance:hover,
.mobile-book-btn.is-guidance:hover {
  background: var(--wine-dark);
}

.btn-heart {
  width: 48px; height: 48px; flex: 0 0 48px;
  display: grid; place-items: center;
  border: 0; border-radius: 50%;
  background: var(--wine-glow);
  color: var(--wine);
  font-size: 20px;
  cursor: pointer;
  transition: background 0.2s ease, transform 0.3s var(--ease-spring);
}
.btn-heart:hover { background: rgba(185,74,72,0.18); transform: scale(1.06) translateY(-1px); }
.btn-heart:active { transform: scale(0.94); }

.cart-feedback {
  position: fixed;
  top: 86px;
  right: 20px;
  z-index: 120;
  max-width: min(340px, calc(100vw - 40px));
  display: inline-flex;
  align-items: center;
  gap: 10px;
  padding: 13px 16px;
  border: 1px solid rgba(118,90,70,0.18);
  border-radius: 14px;
  background: #fffaf7;
  color: var(--wine-dark);
  font-size: 13px;
  font-weight: 800;
  box-shadow: 0 16px 44px rgba(74,52,47,0.14);
  opacity: 0;
  transform: translateY(-8px);
  pointer-events: none;
  transition: opacity 0.22s ease, transform 0.22s ease;
}
.cart-feedback.show {
  opacity: 1;
  transform: translateY(0);
}

/* ─── REVIEWS ───────────────────────────────────────── */
.reviews-grid {
  display: grid;
  grid-template-columns: 1fr 1.4fr;
  gap: 28px;
  align-items: start;
  margin-top: 24px;
}

.rating-summary-card {
  background: var(--panel-strong);
  border: 1px solid var(--line);
  border-radius: var(--radius-xl);
  padding: clamp(22px, 3vw, 32px);
}

.rating-big {
  display: flex; align-items: center; gap: 10px;
  color: var(--wine-dark);
  font-size: 28px; font-weight: 900;
}
.rating-big .star-icon { color: var(--gold); }

.rating-bars {
  display: grid; gap: 8px;
  margin-top: 16px;
  font-size: 12px; color: var(--muted); font-weight: 600;
}

.bar-row {
  display: grid;
  grid-template-columns: 44px minmax(80px, 1fr) 22px;
  gap: 8px; align-items: center;
}

.bar-track { height: 4px; overflow: hidden; border-radius: 999px; background: rgba(118,90,70,0.15); }
.bar-fill { height: 100%; display: block; border-radius: inherit; background: var(--gold); transition: width 0.4s ease; }

.review-list {
  display: grid; gap: 12px;
}

.review-item {
  display: grid;
  grid-template-columns: 36px minmax(0, 1fr) auto;
  gap: 12px; align-items: start;
  background: var(--panel-strong);
  border: 1px solid var(--line);
  border-radius: var(--radius-lg);
  padding: 16px 18px;
  transition: box-shadow 0.2s ease, transform 0.2s ease;
}
.review-item:hover {
  box-shadow: var(--shadow-sm);
  transform: translateY(-1px);
}

.review-avatar {
  width: 32px; height: 32px;
  display: grid; place-items: center;
  border-radius: 50%;
  background: var(--cream);
  color: var(--wine);
  font-size: 12px; font-weight: 800;
}

.review-text strong { display: block; color: var(--ink); font-size: 13px; }
.review-text span { display: block; color: var(--muted); font-size: 11px; margin-top: 2px; }
.review-text p { margin: 6px 0 0; color: var(--muted); font-size: 13px; line-height: 1.5; }

.review-score {
  color: var(--wine-dark);
  font-size: 13px; font-weight: 800;
}

/* ─── SUPPLIER SPOTLIGHT ────────────────────────────── */
.supplier-spotlight {
  background: linear-gradient(135deg, var(--panel), var(--cream));
  border: 1px solid var(--line);
  border-radius: var(--radius-xl);
  padding: clamp(28px, 4vw, 40px);
  margin-top: var(--pad-section);
  display: flex; gap: 24px; align-items: flex-start;
  box-shadow: var(--shadow);
}

.supplier-spotlight:hover { box-shadow: var(--shadow-lg); transition: box-shadow 0.3s ease; }

.supplier-avatar-lg {
  flex: 0 0 56px; width: 56px; height: 56px;
  display: grid; place-items: center;
  border-radius: 50%;
  background: var(--wine-glow);
  color: var(--wine);
  font-size: 22px; font-weight: 800;
}

.supplier-spot-info { min-width: 0; flex: 1; }
.supplier-spot-info h3 {
  font-family: var(--font-serif);
  font-size: 22px; font-weight: 600;
  color: var(--ink);
}
.supplier-spot-info p {
  color: var(--muted);
  font-size: 14px; line-height: 1.6;
  margin-top: 6px;
  max-width: 520px;
}
.supplier-badge {
  display: inline-flex; align-items: center; gap: 5px;
  margin-top: 10px;
  color: var(--green);
  font-size: 12px; font-weight: 700;
}

/* ─── RELATED SERVICES ──────────────────────────────── */
.related-section {
  margin-top: var(--pad-section);
}

.related-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 24px;
  margin-top: 28px;
}

.related-item {
  background: var(--panel);
  border: 1px solid var(--line);
  border-radius: var(--radius-xl);
  overflow: hidden;
  box-shadow: var(--shadow);
  transition: transform 0.4s var(--ease-out-expo), box-shadow 0.4s ease;
}
.related-item:hover {
  transform: translateY(-5px);
  box-shadow: var(--shadow-lg);
}

.related-img {
  display: block; height: 200px; overflow: hidden;
  background: var(--cream);
}
.related-img img { transition: transform 0.5s var(--ease-out-expo); }
.related-item:hover .related-img img { transform: scale(1.06); }

.related-body { padding: 20px; }
.related-cat {
  display: inline-block;
  border-radius: 999px;
  background: var(--wine-glow);
  color: var(--wine);
  padding: 3px 10px;
  font-size: 10px; font-weight: 800;
  letter-spacing: 0.06em; text-transform: uppercase;
}
.related-name {
  font-family: var(--font-serif);
  font-size: 20px; font-weight: 600;
  color: var(--ink);
  margin: 10px 0 4px;
}
.related-rating {
  display: flex; align-items: center; gap: 4px;
  color: var(--gold); font-size: 13px; font-weight: 700;
  margin-bottom: 8px;
}
.related-price {
  color: var(--muted); font-size: 13px; font-weight: 600;
}
.related-btn {
  display: inline-flex; align-items: center; gap: 6px;
  margin-top: 14px;
  height: 36px; padding: 0 18px;
  border: 1px solid var(--line);
  border-radius: 999px;
  font-size: 12px; font-weight: 700; color: var(--wine);
  transition: background 0.15s, color 0.15s, border-color 0.15s;
}
.related-btn:hover { background: var(--wine); color: #fff; border-color: var(--wine); }

/* ─── FLOATING CART ─────────────────────────────────── */
.floating-cart {
  position: fixed;
  right: clamp(20px, 5vw, 60px);
  bottom: clamp(24px, 6vw, 60px);
  z-index: 30;
  width: 54px; height: 54px;
  display: grid; place-items: center;
  border: 1px solid var(--line);
  border-radius: 16px;
  background: var(--panel-strong);
  box-shadow: 0 12px 36px rgba(74,52,47,0.15);
  color: var(--wine);
  font-size: 20px;
  transition: transform 0.3s var(--ease-spring), box-shadow 0.3s ease;
}
.floating-cart:hover {
  transform: translateY(-3px);
  box-shadow: 0 18px 44px rgba(74,52,47,0.18);
}

/* ─── MOBILE BOTTOM BAR ─────────────────────────────── */
.mobile-book-bar {
  display: none;
  position: fixed; bottom: 0; left: 0; right: 0; z-index: 40;
  background: var(--glass-strong);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border-top: 1px solid var(--line);
  padding: 12px 16px;
  padding-bottom: max(12px, env(safe-area-inset-bottom));
  box-shadow: 0 -8px 32px rgba(74,52,47,0.08);
  animation: slideUp 0.4s var(--ease-out-expo);
}

@keyframes slideUp {
  from { transform: translateY(100%); }
  to { transform: translateY(0); }
}

.mobile-book-row {
  display: flex; align-items: center; justify-content: space-between; gap: 12px;
}

.mobile-book-price {
  font-family: var(--font-serif);
  font-size: 24px; font-weight: 700;
  color: var(--wine-dark);
}
.mobile-book-label { font-size: 11px; color: var(--muted); font-weight: 600; }

.mobile-book-btn {
  flex: 0 0 auto; min-height: 44px; padding: 0 22px;
  border: 0; border-radius: 999px;
  background: var(--wine); color: #fff;
  font-size: 13px; font-weight: 800;
  display: inline-flex; align-items: center; gap: 8px;
  cursor: pointer;
  transition: background 0.15s, transform 0.2s;
}
.mobile-book-btn:hover { background: var(--wine-dark); }
.mobile-book-btn:active { transform: scale(0.96); }

/* ─── LIGHTBOX ──────────────────────────────────────── */
.lightbox {
  position: fixed; inset: 0; z-index: 100;
  display: none; place-items: center;
  background: rgba(0,0,0,0.88);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  padding: 20px;
}
.lightbox.open { display: grid; }

.lightbox-close {
  position: absolute; top: 16px; right: 20px;
  width: 40px; height: 40px;
  display: grid; place-items: center;
  border: 0; border-radius: 50%;
  background: rgba(255,255,255,0.10);
  color: #fff; cursor: pointer; font-size: 22px;
  transition: background 0.15s;
}
.lightbox-close:hover { background: rgba(255,255,255,0.20); }

.lightbox img, .lightbox video {
  max-width: min(90vw, 900px);
  max-height: 85vh;
  border-radius: var(--radius-lg);
  object-fit: contain;
}

.lightbox-prev, .lightbox-next {
  position: absolute; top: 50%; transform: translateY(-50%);
  width: 44px; height: 44px;
  display: grid; place-items: center;
  border: 0; border-radius: 50%;
  background: rgba(255,255,255,0.10);
  color: #fff; cursor: pointer; font-size: 20px;
  transition: background 0.15s;
}
.lightbox-prev:hover, .lightbox-next:hover { background: rgba(255,255,255,0.20); }
.lightbox-prev { left: 16px; }
.lightbox-next { right: 16px; }

/* ─── CURSOR FOLLOWER ───────────────────────────────── */
.cursor-follower {
  position: fixed; pointer-events: none; z-index: 999;
  width: 10px; height: 10px;
  border-radius: 50%;
  background: var(--wine);
  transform: translate(-50%, -50%);
  transition: width 0.2s, height 0.2s, background 0.2s;
  mix-blend-mode: difference;
  opacity: 0.4;
}

/* ─── EMPTY STATES ──────────────────────────────────── */
.empty-state {
  border: 1px dashed rgba(185,74,72,0.28);
  border-radius: var(--radius-xl);
  background: rgba(255,248,239,0.70);
  color: var(--muted);
  padding: 24px;
  font-size: 13px; font-weight: 600;
  text-align: center;
}
.empty-state i {
  display: block; margin: 0 auto 10px;
  font-size: 26px; opacity: 0.5;
}

/* ─── LOADING SPINNER ───────────────────────────────── */
.loading-spinner {
  width: 24px; height: 24px;
  border: 3px solid rgba(185,74,72,0.12);
  border-top-color: var(--wine);
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
  margin: 0 auto;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ─── RESPONSIVE ────────────────────────────────────── */

/* Tablet: 768-1024px */
@media (max-width: 1024px) {
  .hero-cover { height: 70vh; min-height: 480px; }
  .package-context-strip { padding-top: 82px; }
  .hero-title { font-size: clamp(36px, 5vw, 56px); }
  .quick-stats { grid-template-columns: repeat(2, 1fr); }
  .split-section { grid-template-columns: 1fr; }
  .split-sidebar { display: none; }
  .booking-grid { grid-template-columns: 1fr; }
  .date-picker-card { grid-template-columns: 1fr; }
  .sticky-summary { display: none; }
  .mobile-book-bar { display: block; }
  .reviews-grid { grid-template-columns: 1fr; }
  .portfolio-grid { grid-template-columns: repeat(2, 1fr); }
  .portfolio-item:first-child { grid-column: span 2; grid-row: span 1; }
  .related-grid { gap: 18px; }
  .floating-cart { width: 48px; height: 48px; font-size: 18px; border-radius: 14px; }
}

/* Mobile: below 768px */
@media (max-width: 768px) {
  .hero-cover { height: 60vh; min-height: 420px; }
  .package-context-strip { padding: 76px 16px 14px; }
  .package-banner { align-items: stretch; flex-direction: column; }
  .package-banner-link { justify-content: center; }
  .hero-title { font-size: clamp(30px, 8vw, 42px); }
  .hero-sub { font-size: 12px; gap: 14px; }
  .top-bar { padding: 10px 16px; }
  .top-bar-brand { font-size: 17px; }
  .top-pill { min-height: 32px; padding: 0 12px; font-size: 11px; }
  .page-shell { padding: 0 16px 40px; margin-top: -60px; }
  .gallery-main { height: 240px; }
  .gallery-thumb { flex: 0 0 56px; height: 42px; }
  .quick-stats { grid-template-columns: repeat(2, 1fr); }
  .quick-stat-value { font-size: 22px; }
  .quick-stat { padding: 14px 10px; }
  .split-section { grid-template-columns: 1fr; }
  .split-sidebar { display: none; }
  .section-card { padding: 20px; }
  .portfolio-grid { grid-template-columns: 1fr 1fr; gap: 8px; }
  .portfolio-item:first-child { grid-column: span 2; grid-row: span 1; }
  .booking-grid { grid-template-columns: 1fr; }
  .date-picker-card { grid-template-columns: 1fr; padding: 14px; }
  .date-picker-control { display: grid; grid-template-columns: 1fr; }
  .date-picker-control input,
  .date-picker-btn { width: 100%; }
  .sticky-summary { display: none; }
  .mobile-book-bar { display: block; }
  .availability-row { grid-template-columns: 1fr; gap: 8px; padding: 12px; }
  .availability-status { width: max-content; }
  .reviews-grid { grid-template-columns: 1fr; }
  .review-item { grid-template-columns: 1fr; }
  .supplier-spotlight { flex-direction: column; padding: 24px; }
  .related-grid { grid-template-columns: 1fr; }
  .related-img { height: 180px; }
  .floating-cart { width: 44px; height: 44px; font-size: 16px; border-radius: 12px; bottom: 80px; }
}

/* Reduced motion */
@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
    scroll-behavior: auto !important;
  }
  .hero-cover-bg { transform: scale(1) !important; }
  .cursor-follower { display: none !important; }
  [data-aos] { opacity: 1 !important; transform: none !important; }
}
</style>
</head>
<body>

<!-- ─── TOP BAR ──────────────────────────────────── -->
<header class="top-bar" id="topBar">
  <a class="top-bar-brand" href="<?= URLROOT ?>/main/home">Golden Promise</a>
  <nav class="top-bar-actions">
    <a class="top-pill" href="<?= $h($isPackageContext ? $packageDetailUrl : URLROOT . '/customerServices/service') ?>">
      <?= $isPackageContext ? 'Back to package' : 'Explore' ?>
    </a>
    <a class="top-pill" href="<?= URLROOT ?>/cart" aria-label="Cart" style="display:inline-flex;align-items:center;gap:4px;">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
      Cart
    </a>
    <?php if ($isLoggedIn): ?>
    <div class="tb-profile-dropdown">
      <button class="tb-profile-btn" type="button" aria-expanded="false">
        <span class="tb-profile-avatar"><?= strtoupper(substr($_SESSION['session_name'] ?? 'U', 0, 1)) ?></span>
        <span class="tb-profile-name"><?= htmlspecialchars(explode(' ', $_SESSION['session_name'] ?? 'User')[0], ENT_QUOTES, 'UTF-8') ?></span>
        <svg class="tb-profile-chevron" width="10" height="6" viewBox="0 0 10 6" fill="none"><path d="M1 1l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </button>
      <div class="tb-profile-menu" aria-hidden="true">
        <a class="tb-profile-menu-item" href="<?= URLROOT ?>/booking/myBookings">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
          My Bookings
        </a>
        <a class="tb-profile-menu-item tb-profile-menu-item--danger" href="<?= URLROOT ?>/users/logout">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
          Logout
        </a>
      </div>
    </div>
    <?php else: ?>
    <a class="top-pill" href="<?= URLROOT ?>/users/auth">Sign in</a>
    <?php endif; ?>
  </nav>
</header>

<?php if ($isPackageContext): ?>
<section class="package-context-strip" aria-label="Package context">
  <div class="package-context-inner">
    <nav class="package-breadcrumb" aria-label="Breadcrumb">
      <a href="<?= URLROOT ?>/customerServices/packages">Packages</a>
      <span class="package-breadcrumb-sep">/</span>
      <a href="<?= $h($packageDetailUrl) ?>"><?= $h($packageName) ?></a>
      <span class="package-breadcrumb-sep">/</span>
      <span><?= $h($service['name'] ?? 'Service detail') ?></span>
    </nav>
    <div class="package-banner">
      <div class="package-banner-copy">
        <strong>Part of <?= $h($packageName) ?></strong>
        <span>This service is being reviewed inside your wedding package. Package pricing and assigned selections are shown here.</span>
      </div>
      <a class="package-banner-link" href="<?= $h($packageDetailUrl) ?>">
        View package details
        <i data-lucide="arrow-right" size="14"></i>
      </a>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ─── CINEMATIC HERO ───────────────────────────── -->
<section class="hero-cover" id="heroCover">
  <div class="hero-cover-bg" id="heroCoverBg"
       style="background-image: url('<?= $h($heroBgUrl) ?>');"></div>
  <div class="hero-cover-content">
    <span class="hero-category"><?= $h($service['category'] ?? 'Service') ?></span>
    <h1 class="hero-title"><?= $h($service['name'] ?? '') ?></h1>
    <div class="hero-sub">
      <span class="hero-sub-item"><i data-lucide="star" size="14" fill="currentColor"></i> <?= number_format((float)$rating, 1) ?> (<?= (int)$reviewCount ?>)</span>
      <?php if ($supplierName !== ''): ?>
        <span class="hero-sub-item"><i data-lucide="store" size="14"></i> <?= $h($supplierName) ?></span>
      <?php endif; ?>
      <span class="hero-sub-item"><i data-lucide="tag" size="14"></i> <?= $moneyRange($service) ?></span>
    </div>
  </div>
  <div class="hero-scroll-indicator" id="scrollIndicator" aria-label="Scroll down">
    <i data-lucide="chevron-down" size="28"></i>
  </div>
</section>

<!-- ─── PAGE SHELL ───────────────────────────────── -->
<main class="page-shell">

  <!-- SECTION: GALLERY + QUICK STATS -->
  <section class="section-gallery" data-aos="fade-up" data-aos-duration="1000">
    <div class="gallery-frame" id="heroSection">
      <div class="gallery-main" id="heroMain">
        <?php if (empty($heroItems)): ?>
          <img src="<?= $h($fallbackImage) ?>" alt="Service placeholder">
        <?php else:
          $firstMedia = $heroItems[0];
          $isVideo = ($firstMedia['type'] ?? 'image') === 'video';
        ?>
          <?php if ($isVideo): ?>
            <video id="heroVideo" src="<?= $h($firstMedia['file_url']) ?>" muted playsinline preload="metadata"></video>
            <div class="gallery-video-overlay" id="heroVideoOverlay">
              <span class="gallery-play-btn"><i data-lucide="play" fill="currentColor"></i></span>
            </div>
          <?php else: ?>
            <img id="heroImg" src="<?= $h($firstMedia['file_url']) ?>" alt="<?= $h($service['name'] ?? '') ?>">
          <?php endif; ?>
          <?php if (count($heroItems) > 1): ?>
            <button class="gallery-nav-btn prev" id="heroPrev" aria-label="Previous" type="button"><i data-lucide="chevron-left" size="16"></i></button>
            <button class="gallery-nav-btn next" id="heroNext" aria-label="Next" type="button"><i data-lucide="chevron-right" size="16"></i></button>
            <div class="gallery-dots" id="heroDots">
              <?php foreach ($heroItems as $i => $item): ?>
                <button class="gallery-dot <?= $i === 0 ? 'active' : '' ?>" data-index="<?= $i ?>" type="button" aria-label="View media <?= $i + 1 ?>"></button>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        <?php endif; ?>
      </div>

      <?php if (count($heroItems) > 1): ?>
        <div class="gallery-thumbs" id="heroThumbs">
          <?php foreach ($heroItems as $i => $item):
            $tv = ($item['type'] ?? 'image') === 'video';
          ?>
            <div class="gallery-thumb <?= $i === 0 ? 'active' : '' ?>" data-index="<?= $i ?>">
              <?php if ($tv): ?>
                <video src="<?= $h($item['file_url']) ?>" muted preload="metadata"></video>
                <span class="gallery-thumb-video"><i data-lucide="play" size="12" fill="currentColor"></i></span>
              <?php else: ?>
                <img src="<?= $h($item['file_url']) ?>" alt="">
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <div class="quick-stats">
      <div class="quick-stat">
        <div class="quick-stat-value"><?= $moneyRange($service) ?></div>
        <div class="quick-stat-label"><?= $pricingUnitLabel($service) ?></div>
      </div>
      <div class="quick-stat">
        <div class="quick-stat-value"><?= $h($durationText($service)) ?></div>
        <div class="quick-stat-label">Duration</div>
      </div>
      <div class="quick-stat">
        <div class="quick-stat-value"><?= $isVenue ? (int)$venueCapacity : (int)($service['max_concurrent'] ?? 1) ?></div>
        <div class="quick-stat-label"><?= $isVenue ? 'Guest capacity' : 'Capacity' ?></div>
      </div>
      <div class="quick-stat">
        <div class="quick-stat-value"><?= $rating > 0 ? number_format((float)$rating, 1) : '—' ?></div>
        <div class="quick-stat-label">Rating</div>
      </div>
    </div>
  </section>

  <!-- SECTION: ABOUT + GLASS STATS (SPLIT) -->
  <section class="split-section" data-aos="fade-up" data-aos-duration="800">
    <div class="split-content">
      <?php if ($description !== ''): ?>
        <div class="section-card">
          <h2 class="card-title">About this service</h2>
          <p class="card-sub">What <?= $h($service['name'] ?? '') ?> offers you</p>
          <div class="desc-text"><?= $h($description) ?></div>
        </div>
      <?php endif; ?>

      <!-- Portfolio moved inline here -->
      <div class="section-card" style="<?= $description === '' ? '' : 'margin-top:24px;' ?>">
        <h2 class="card-title">Portfolio</h2>
        <p class="card-sub">Photos &amp; media from this service</p>
        <?php if (empty($media)): ?>
          <div class="empty-state">
            <i data-lucide="image" size="22"></i>
            No portfolio photos have been published for this service yet.
          </div>
        <?php else: ?>
          <div class="portfolio-grid">
            <?php foreach (array_slice($media, 0, 7) as $i => $item): ?>
              <?php $assetUrl = trim((string)($item['file_url'] ?? '')); ?>
              <?php if ($assetUrl === '') continue; ?>
              <?php $isVid = ($item['type'] ?? 'image') === 'video'; ?>
              <div class="portfolio-item" data-src="<?= $h($assetUrl) ?>" data-type="<?= $isVid ? 'video' : 'image' ?>"
                   data-aos="zoom-in" data-aos-delay="<?= min($i * 80, 400) ?>">
                <?php if ($isVid): ?>
                  <video src="<?= $h($assetUrl) ?>" muted preload="metadata"></video>
                  <span class="portfolio-vid-badge"><i data-lucide="play-circle" size="32"></i></span>
                <?php else: ?>
                  <img src="<?= $h($assetUrl) ?>" alt="<?= $h(($service['name'] ?? 'Service') . ' portfolio') ?>">
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <aside class="split-sidebar">
      <div class="glass-stats" data-aos="fade-left" data-aos-delay="200">
        <div class="stat-price"><?= $moneyRange($service) ?></div>
        <div class="stat-item" style="padding-top:18px;">
          <div class="stat-icon"><i data-lucide="clock" size="16"></i></div>
          <div class="stat-content">
            <strong><?= $h($durationText($service)) ?></strong>
            <span>Booking type</span>
          </div>
        </div>
        <div class="stat-item">
          <div class="stat-icon"><i data-lucide="users" size="16"></i></div>
          <div class="stat-content">
            <strong><?= $isVenue ? (int)$venueCapacity . ' guests' : (int)($service['max_concurrent'] ?? 1) ?></strong>
            <span><?= $isVenue ? 'Hall capacity' : 'Maximum concurrent' ?></span>
          </div>
        </div>
        <?php if ($supplierName !== ''): ?>
        <div class="stat-item">
          <div class="stat-icon"><i data-lucide="store" size="16"></i></div>
          <div class="stat-content">
            <strong><?= $h($supplierName) ?></strong>
            <span>Wedding supplier</span>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </aside>
  </section>

  <!-- SECTION: AVAILABILITY / BOOKING -->
  <section class="booking-section" data-aos="fade-up" data-aos-duration="800">
    <span class="section-label"><?= $isVenue ? 'Choose a hall' : 'Pick a date' ?></span>
    <h2 class="section-title"><?= $isVenue ? 'Available Halls' : ($isSlotBooking ? 'Available Dates &amp; Times' : 'Available Dates') ?></h2>
    <p class="section-sub">
      <?= $selectedDateLabel !== ''
        ? $h(($isVenue ? 'Reserve a hall for ' : ($isSlotBooking ? 'Available times for ' : 'Available range for ')) . $selectedDateLabel)
        : ($isVenue ? 'Choose your wedding date first so hall availability is accurate' : ($isSlotBooking ? 'Choose your preferred date and time' : 'Choose an available date')) ?>
    </p>

    <form class="date-picker-card" method="GET" action="<?= $h($datePickerAction) ?>">
      <?php foreach ($packageQueryFields as $fieldName => $fieldValue): ?>
        <?php if ($fieldValue > 0): ?>
          <input type="hidden" name="<?= $h($fieldName) ?>" value="<?= (int)$fieldValue ?>">
        <?php endif; ?>
      <?php endforeach; ?>
      <div class="date-picker-copy">
        <strong><?= $selectedDateLabel !== '' ? 'Wedding date selected' : 'Start with your wedding date' ?></strong>
        <span>
          <?= $selectedDateLabel !== ''
            ? $h('Showing availability for ' . $selectedDateLabel . '. You can change the date anytime.')
            : ($isVenue
              ? 'Halls are checked against the wedding date before you choose one.'
              : ($isSlotBooking ? 'If you came from the service list, that date is prefilled here.' : 'Full-day services show one available time range for each open date.')) ?>
        </span>
      </div>
      <div class="date-picker-control">
        <input type="date" id="detail-date" name="date" value="<?= $h($selectedDate) ?>" min="<?= $h($datePickerMin) ?>" max="<?= $h($datePickerMax) ?>" aria-label="Wedding date">
        <button class="date-picker-btn" type="submit">
          <i data-lucide="calendar-check" size="15"></i>
          Check
        </button>
      </div>
      <?php if ((int)($service['min_lead_days'] ?? 0) > 0): ?>
      <div class="date-picker-copy" style="grid-column:1 / -1">
        <span>Earliest booking date: <?= $h(date('M j, Y', strtotime($datePickerMin))) ?>.</span>
      </div>
      <?php endif; ?>
    </form>

    <div class="booking-grid">
      <div class="availability-list">
        <?php if ($isVenue): ?>
          <?php if ($selectedDate === ''): ?>
            <div class="empty-state"><i data-lucide="calendar-days" size="22"></i>Please choose a wedding date to see which halls are available.</div>
          <?php endif; ?>
          <?php if (empty($venueRooms)): ?>
            <div class="empty-state"><i data-lucide="door-open" size="22"></i>No halls have been published for this venue yet.</div>
          <?php else: ?>
            <?php $hasSelectedRoom = false; ?>
            <?php foreach ($venueRooms as $index => $room): ?>
              <?php
                $isPackageHallRow = $isPackageContext && (int)($packageContext['venue_room_id'] ?? 0) > 0 && (int)($room['id'] ?? 0) === (int)($packageContext['venue_room_id'] ?? 0);
                $roomDisplayPrice = $isPackageContext ? $packageServicePrice : (float)($room['price'] ?? 0);
                $roomAvailable = $selectedDate !== '' && (!array_key_exists('is_available_on_date', $room) || !empty($room['is_available_on_date']));
                $roomEarliestDate = trim((string)($room['earliest_booking_date'] ?? ''));
                $roomStatus = $roomAvailable
                  ? $money($roomDisplayPrice)
                  : ($selectedDate === ''
                    ? 'Choose date'
                    : (!empty($room['lead_time_blocked']) && $roomEarliestDate !== ''
                      ? 'Too soon'
                      : (!empty($room['service_closed_on_date']) || !empty($room['room_closed_on_date']) ? 'Closed' : 'Booked')));
                $checked = !$hasSelectedRoom && $roomAvailable;
                if ($checked) { $hasSelectedRoom = true; }
              ?>
              <div class="availability-row <?= $checked ? 'is-selected' : '' ?> <?= $isPackageHallRow ? 'is-package-selected' : '' ?> <?= $roomAvailable ? '' : 'is-unavailable' ?>" data-slot-row data-aos="fade-up" data-aos-delay="<?= min($index * 80, 300) ?>">
                <span class="radio-dot"></span>
                <div>
                  <div class="availability-head">
                    <span class="availability-name">
                      <?= $h($room['name'] ?: 'Venue hall') ?>
                      <span>
                        <?= $h(trim((string)($room['venue_name'] ?? ''))) ?>
                        <?= !empty($room['venue_location']) ? ' · ' . $h($room['venue_location']) : '' ?>
                        · <?= $h($timeRange($room['start_time'] ?? '09:00', $room['end_time'] ?? '17:00')) ?>
                        <?= $selectedDateLabel !== '' ? ' · ' . $h($selectedDateLabel) : '' ?>
                      </span>
                    </span>
                    <span class="availability-status"><?= $h($roomStatus) ?></span>
                  </div>
                  <?php if ($isPackageHallRow): ?>
                    <span class="package-hall-badge"><i data-lucide="badge-check" size="13"></i>Selected for your package</span>
                  <?php endif; ?>
                  <?php if (!empty($room['lead_time_blocked']) && $roomEarliestDate !== ''): ?>
                    <span class="package-hall-badge"><i data-lucide="calendar-clock" size="13"></i>Earliest: <?= $h(date('M j, Y', strtotime($roomEarliestDate))) ?></span>
                  <?php endif; ?>
                  <?php if ($roomAvailable): ?>
                    <div class="slot-options">
                      <label class="slot-chip <?= $isPackageHallRow ? 'is-locked' : '' ?>" <?= $isPackageHallRow ? 'title="Included in your package"' : '' ?>>
                        <input type="radio" name="service_slot"
                          value="room|<?= (int)$room['id'] ?>"
                          data-room-id="<?= (int)$room['id'] ?>"
                          data-venue-room-id="<?= (int)$room['id'] ?>"
                          data-date="<?= $h($selectedDate) ?>"
                          data-date-label="<?= $h($selectedDateLabel ?: 'Choose a wedding date') ?>"
                          data-hall-label="<?= $h($room['name'] ?: 'Selected hall') ?>"
                          data-time-label="<?= (int)($room['capacity'] ?? 1) ?> guests"
                          data-price-label="<?= $money($roomDisplayPrice) ?>"
                          data-price-value="<?= $h($roomDisplayPrice) ?>"
                          data-slot-id=""
                          data-start-time="<?= $h($room['start_time'] ?? '') ?>"
                          data-end-time="<?= $h($room['end_time'] ?? '') ?>"
                          <?= $checked ? 'checked' : '' ?>
                          <?= $isPackageHallRow ? 'disabled' : '' ?>>
                        <?= $isPackageHallRow ? 'Included in your package' : (int)($room['capacity'] ?? 1) . ' guests' ?>
                      </label>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        <?php elseif (empty($upcoming)): ?>
          <div class="empty-state"><i data-lucide="calendar-x" size="22"></i>No available dates yet. Please check again later.</div>
        <?php else: ?>
          <?php $hasSelectedSlot = false; ?>
          <?php foreach ($upcoming as $dayIdx => $day): ?>
            <?php
              $dayLabel = !empty($day['date'])
                ? date('l, M j, Y', strtotime($day['date']))
                : ($day['day_label'] ?? '');
              $slots = $day['slots'] ?? [];
              $firstDaySlot = $slots[0] ?? null;
              $slotSummary = $isSlotBooking
                ? (count($slots) > 1
                  ? count($slots) . ' time slots'
                  : ($firstDaySlot['label'] ?? ($day['reason'] ?? ($selectedDate !== '' && !empty($day['is_selected_date']) ? 'No available time on your selected date' : ($day['date'] ?? '')))))
                : ($firstDaySlot['label'] ?? ($day['reason'] ?? ($selectedDate !== '' && !empty($day['is_selected_date']) ? 'Not available on your selected date' : ($day['date'] ?? ''))));
              $rowSelected = !$hasSelectedSlot && !empty($slots);
              $isRequestedDate = !empty($day['is_selected_date']);
            ?>
            <?php if ($isSlotBooking): ?>
            <div class="availability-row <?= $rowSelected ? 'is-selected' : '' ?> <?= $isRequestedDate ? 'is-requested-date' : '' ?> <?= empty($slots) ? 'is-unavailable' : '' ?>" data-slot-row data-aos="fade-up" data-aos-delay="<?= min($dayIdx * 80, 300) ?>">
              <span class="radio-dot"></span>
              <div>
                <div class="availability-head">
                  <span class="availability-name">
                    <?= $h($dayLabel) ?>
                    <span><?= $h($slotSummary) ?></span>
                  </span>
                  <span class="availability-status"><?= $h($day['status'] ?? (empty($slots) ? 'Booked' : 'Available')) ?></span>
                </div>
                <?php if (!empty($slots)): ?>
                  <div class="slot-options">
                    <?php foreach ($slots as $slotIndex => $slot): ?>
                      <?php $checked = !$hasSelectedSlot && $slotIndex === 0; if ($checked) { $hasSelectedSlot = true; } ?>
                      <label class="slot-chip">
                        <input type="radio" name="service_slot"
                          value="<?= $h(($day['date'] ?? '') . '|' . ($slot['start_time'] ?? '') . '|' . ($slot['end_time'] ?? '')) ?>"
                          data-date="<?= $h($day['date'] ?? '') ?>"
                          data-date-label="<?= $h($day['day_label'] ?? $day['date']) ?>"
                          data-time-label="<?= $h($slot['label'] ?? '') ?>"
                          data-slot-id="<?= $h($slot['slot_id'] ?? '') ?>"
                          data-start-time="<?= $h($slot['start_time'] ?? '') ?>"
                          data-end-time="<?= $h($slot['end_time'] ?? '') ?>"
                          data-price-value="<?= $h($isPackageContext ? $packageServicePrice : $activeServicePrice) ?>"
                          <?= $checked ? 'checked' : '' ?>>
                        <?= $h($slot['label'] ?? '') ?>
                      </label>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>
              </div>
            </div>
            <?php else: ?>
            <label class="availability-row <?= $rowSelected ? 'is-selected' : '' ?> <?= $isRequestedDate ? 'is-requested-date' : '' ?> <?= empty($slots) ? 'is-unavailable' : '' ?>" data-slot-row data-aos="fade-up" data-aos-delay="<?= min($dayIdx * 80, 300) ?>">
              <?php if (!empty($firstDaySlot)): ?>
                <?php $checked = !$hasSelectedSlot; if ($checked) { $hasSelectedSlot = true; } ?>
                <input class="availability-radio-input" type="radio" name="service_slot"
                  value="<?= $h(($day['date'] ?? '') . '|' . ($firstDaySlot['start_time'] ?? '') . '|' . ($firstDaySlot['end_time'] ?? '')) ?>"
                  data-date="<?= $h($day['date'] ?? '') ?>"
                  data-date-label="<?= $h($day['day_label'] ?? $day['date']) ?>"
                  data-time-label="<?= $h($firstDaySlot['label'] ?? '') ?>"
                  data-slot-id="<?= $h($firstDaySlot['slot_id'] ?? '') ?>"
                  data-start-time="<?= $h($firstDaySlot['start_time'] ?? '') ?>"
                  data-end-time="<?= $h($firstDaySlot['end_time'] ?? '') ?>"
                  data-price-value="<?= $h($isPackageContext ? $packageServicePrice : $activeServicePrice) ?>"
                  <?= $checked ? 'checked' : '' ?>>
              <?php endif; ?>
              <span class="radio-dot"></span>
              <div>
                <div class="availability-head">
                  <span class="availability-name">
                    <?= $h($dayLabel) ?>
                    <span><?= empty($slots) ? $h($slotSummary) : 'Full-day availability' ?></span>
                  </span>
                  <span class="availability-status"><?= $h($day['status'] ?? (empty($slots) ? 'Booked' : 'Available')) ?></span>
                </div>
                <?php if (!empty($firstDaySlot)): ?>
                  <span class="availability-range"><i data-lucide="clock" size="14"></i><?= $h($firstDaySlot['label'] ?? '') ?></span>
                <?php endif; ?>
              </div>
            </label>
            <?php endif; ?>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <!-- Sticky booking summary (desktop) -->
      <aside class="sticky-summary" id="desktopSummary">
        <div class="summary-fields">
          <div class="summary-line">
            <?= $isVenue ? 'Wedding date' : 'Wedding date' ?>
            <span id="selectedDate"><?= $h($selectedDateLabel ?: ($isVenue ? 'Choose a wedding date' : ($firstAvailable['day_label'] ?? 'Choose an available date'))) ?></span>
          </div>
          <?php if ($isVenue): ?>
            <div class="summary-line">
              Selected hall
              <span id="selectedHall"><?= $h($firstVenueRoom['name'] ?? 'Choose a hall') ?></span>
            </div>
          <?php endif; ?>
          <div class="summary-line">
            Service type
            <span><?= $h($service['category'] ?? 'Service') ?></span>
          </div>
          <div class="summary-line">
            Supplier
            <span><?= $h($supplierName) ?></span>
          </div>
          <?php if ($isVenue): ?>
            <div class="summary-line">
              Capacity
              <span id="selectedTime"><?= $firstVenueRoom ? (int)($firstVenueRoom['capacity'] ?? 1) . ' guests' : 'Choose a hall' ?></span>
            </div>
          <?php elseif ($firstSlot): ?>
            <div class="summary-line">
              <?= $isSlotBooking ? 'Time' : 'Available range' ?>
              <span id="selectedTime"><?= $h($firstSlot['label'] ?? '') ?></span>
            </div>
          <?php else: ?>
            <div class="summary-line">
              <?= $isSlotBooking ? 'Time' : 'Available range' ?>
              <span id="selectedTime"><?= $isSlotBooking ? 'Choose a time slot' : 'Choose an available date' ?></span>
            </div>
          <?php endif; ?>
        </div>
        <div class="estimated-row">
          <span><?= $isPackageContext ? 'Package service price' : 'Estimated total' ?></span>
          <strong><?= $isPackageContext ? $money($packageServicePrice) : ($isVenue && $firstVenueRoom ? $money($firstVenueRoom['price'] ?? 0) : $moneyRange($service)) ?></strong>
        </div>
        <?php if ($isPackageContext): ?>
          <div class="package-price-panel" aria-label="Package pricing comparison">
            <div class="package-price-line is-package">
              <span>Package price</span>
              <strong><?= $money($packageServicePrice) ?></strong>
            </div>
            <?php if ($standalonePrice > 0): ?>
              <div class="package-price-line">
                <span>Standalone price</span>
                <strong><?= $money($standalonePrice) ?></strong>
              </div>
              <div class="package-price-line is-saving">
                <span>You save</span>
                <strong><?= $packageSavings > 0 ? $money($packageSavings) : 'Included value' ?></strong>
              </div>
            <?php endif; ?>
          </div>
        <?php endif; ?>
        <div class="summary-actions">
          <?php if ($hasInitialBookOption): ?>
          <form method="POST" action="<?= URLROOT ?>/cart/add" id="serviceCartForm" style="display:contents;">
            <input type="hidden" name="service_id" value="<?= (int)($service['id'] ?? 0) ?>">
            <input type="hidden" name="date" id="cartDate" value="<?= $h($selectedDate) ?>">
            <input type="hidden" name="slot_id" id="cartSlotId" value="<?= $h($isVenue ? '' : ($firstSlot['slot_id'] ?? '')) ?>">
            <input type="hidden" name="venue_room_id" id="cartVenueRoomId" value="<?= $h($isVenue ? ($firstVenueRoom['id'] ?? '') : '') ?>">
            <input type="hidden" name="start_time" id="cartStartTime" value="<?= $h($isVenue ? ($firstVenueRoom['start_time'] ?? '') : ($firstSlot['start_time'] ?? '')) ?>">
            <input type="hidden" name="end_time" id="cartEndTime" value="<?= $h($isVenue ? ($firstVenueRoom['end_time'] ?? '') : ($firstSlot['end_time'] ?? '')) ?>">
            <input type="hidden" name="price" id="cartPrice" value="<?= $h($isPackageContext ? $packageServicePrice : ($isVenue && $firstVenueRoom ? ($firstVenueRoom['price'] ?? 0) : $activeServicePrice)) ?>">
            <input type="hidden" name="source" value="<?= $isPackageContext ? 'package' : 'custom' ?>">
            <?php foreach ($packageQueryFields as $fieldName => $fieldValue): ?>
              <?php if ($fieldValue > 0): ?>
                <input type="hidden" name="<?= $h($fieldName) ?>" value="<?= (int)$fieldValue ?>">
              <?php endif; ?>
            <?php endforeach; ?>
            <button class="btn-cart" id="addCartLink" type="submit">
              <i data-lucide="shopping-cart" size="16"></i>
              <?= $isPackageContext ? 'Add to package booking' : 'Add to cart' ?>
            </button>
          </form>
          <?php else: ?>
          <a class="btn-cart is-guidance" id="addCartLink" href="#detail-date">
            <i data-lucide="calendar-days" size="16"></i>
            <?= $h($initialBookingLabel) ?>
          </a>
          <?php endif; ?>
        </div>
      </aside>
    </div>
  </section>

  <!-- SECTION: SUPPLIER SPOTLIGHT -->
  <?php if ($supplierName !== ''): ?>
  <section class="supplier-spotlight" data-aos="fade-up" data-aos-duration="800">
    <div class="supplier-avatar-lg">
      <?= $h(strtoupper(substr($supplierName, 0, 1))) ?>
    </div>
    <div class="supplier-spot-info">
      <h3><?= $h($supplierName) ?></h3>
      <?php if ($supplierDesc !== ''): ?>
        <p><?= $h($supplierDesc) ?></p>
      <?php endif; ?>
      <?php if ($supplierUrl !== ''): ?>
        <span class="supplier-badge">
          <i data-lucide="badge-check" size="14"></i>
          Verified supplier
        </span>
      <?php endif; ?>
    </div>
  </section>
  <?php endif; ?>

  <!-- SECTION: REVIEWS -->
  <section class="booking-section" data-aos="fade-up" data-aos-duration="800" style="margin-top:40px;">
    <span class="section-label">Social proof</span>
    <h2 class="section-title">Reviews &amp; Rating</h2>
    <p class="section-sub">What customers are saying about this service</p>

    <div class="reviews-grid">
      <div class="rating-summary-card">
        <div class="rating-big">
          <span class="star-icon"><i data-lucide="star" size="22" fill="currentColor"></i></span>
          <?= number_format((float)$rating, 1) ?>
          <span style="font-size:13px;color:var(--muted);font-weight:600;">(<?= (int)$reviewCount ?>)</span>
        </div>
        <div class="rating-bars">
          <?php foreach ($ratingBuckets as $stars => $count): ?>
            <div class="bar-row">
              <span><?= (int)$stars ?> stars</span>
              <span class="bar-track"><span class="bar-fill" style="width: <?= (int)round(($count / $maxBucket) * 100) ?>%;"></span></span>
              <span><?= (int)$count ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="review-list">
        <?php foreach (array_slice($reviews, 0, 3) as $idx => $review): ?>
          <article class="review-item" data-aos="fade-up" data-aos-delay="<?= min($idx * 80, 200) ?>">
            <div class="review-avatar"><i data-lucide="user" size="14"></i></div>
            <div class="review-text">
              <strong>Customer</strong>
              <span><?= $h(date('Y.m.d', strtotime($review['created_at'] ?? 'now'))) ?></span>
              <p><?= $h($review['comment'] ?: 'The service is good!') ?></p>
            </div>
            <strong class="review-score">&#9733; <?= number_format((float)($review['rating'] ?? 0), 1) ?></strong>
          </article>
        <?php endforeach; ?>

        <?php if (empty($reviews)): ?>
          <article class="review-item">
            <div class="review-avatar"><i data-lucide="message-square" size="14"></i></div>
            <div class="review-text">
              <strong>No reviews yet</strong>
              <span>Be the first to share your experience</span>
              <p>This supplier is currently available for booking. Book now and leave a review after your event!</p>
            </div>
          </article>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- SECTION: RELATED SERVICES -->
  <?php if (!empty($related)): ?>
  <section class="related-section" data-aos="fade-up" data-aos-duration="800">
    <span class="section-label">You may also like</span>
    <h2 class="section-title">Other related services</h2>
    <div class="related-grid">
      <?php foreach (array_slice($related, 0, 2) as $idx => $item): ?>
        <article class="related-item" data-aos="flip-up" data-aos-delay="<?= $idx * 100 ?>">
          <a class="related-img" href="<?= URLROOT ?>/customerServices/detail/<?= (int)$item['id'] ?><?= $h($detailDateQuery) ?>">
            <img src="<?= $h($item['image'] ?: $fallbackImage) ?>" alt="<?= $h($item['name'] ?? 'Related service') ?>">
          </a>
          <div class="related-body">
            <span class="related-cat"><?= $h($item['category'] ?? 'Service') ?></span>
            <div class="related-name"><?= $h($item['name'] ?? '') ?></div>
            <?php if ((float)($item['rating'] ?? 0) > 0): ?>
            <div class="related-rating">
              &#9733; <?= number_format((float)$item['rating'], 1) ?>
            </div>
            <?php endif; ?>
            <div class="related-price"><?= $moneyRange($item) ?></div>
            <a class="related-btn" href="<?= URLROOT ?>/customerServices/detail/<?= (int)$item['id'] ?><?= $h($detailDateQuery) ?>">
              View detail <i data-lucide="arrow-right" size="12"></i>
            </a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

</main>

<!-- Floating cart -->
<a class="floating-cart" href="<?= URLROOT ?>/cart" aria-label="Open cart">
  <i data-lucide="shopping-bag" size="20"></i>
</a>

<div class="cart-feedback" id="cartFeedback" role="status" aria-live="polite">
  <i data-lucide="check-circle" size="16"></i>
  <span><?= $isPackageContext ? 'Adding to package booking...' : 'Adding to cart...' ?></span>
</div>

<!-- Mobile bottom booking bar -->
<div class="mobile-book-bar" id="mobileBookBar">
  <div class="mobile-book-row">
    <div>
      <div class="mobile-book-price"><?= $isPackageContext ? $money($packageServicePrice) : ($isVenue && $firstVenueRoom ? $money($firstVenueRoom['price'] ?? 0) : $moneyRange($service)) ?></div>
      <div class="mobile-book-label"><?= $isPackageContext ? 'Package service price' : $pricingUnitLabel($service) ?></div>
    </div>
    <a class="mobile-book-btn <?= $hasInitialBookOption ? '' : 'is-guidance' ?>" id="mobileBookBtn" href="<?= URLROOT ?>/cart">
      <i data-lucide="shopping-cart" size="16"></i>
      <?= $isPackageContext ? 'Add to package' : 'Add to cart' ?>
    </a>
  </div>
</div>

<!-- LIGHTBOX -->
<div class="lightbox" id="lightbox">
  <button class="lightbox-close" id="lightboxClose" type="button" aria-label="Close"><i data-lucide="x" size="22"></i></button>
  <button class="lightbox-prev" id="lightboxPrev" type="button" aria-label="Previous"><i data-lucide="chevron-left" size="22"></i></button>
  <button class="lightbox-next" id="lightboxNext" type="button" aria-label="Next"><i data-lucide="chevron-right" size="22"></i></button>
  <div id="lightboxContent"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  lucide.createIcons();

  // ── AOS Init (with reduced-motion check) ──
  const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (!prefersReduced && typeof AOS !== 'undefined') {
    AOS.init({
      duration: 800,
      easing: 'ease-out-cubic',
      once: true,
      offset: 100,
    });
  }

  // ── Scroll-linked top bar ──
  const topBar = document.getElementById('topBar');
  const heroCover = document.getElementById('heroCover');
  if (topBar && heroCover) {
    const hasPackageContext = <?= $isPackageContext ? 'true' : 'false' ?>;
    const onScroll = () => {
      const heroHeight = heroCover.offsetHeight;
      const progress = Math.min(1, window.scrollY / heroHeight);
      topBar.classList.toggle('scrolled', hasPackageContext || progress > 0.15);
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }

  // ── Parallax hero background ──
  const heroBg = document.getElementById('heroCoverBg');
  if (heroBg && !prefersReduced) {
    window.addEventListener('scroll', () => {
      const rect = heroCover.getBoundingClientRect();
      const progress = Math.max(0, Math.min(1, -rect.top / rect.height));
      heroBg.style.transform = 'scale(1.12) translateY(' + (progress * 40) + 'px)';
    }, { passive: true });
  }

  // ── Scroll down indicator ──
  const scrollIndicator = document.getElementById('scrollIndicator');
  if (scrollIndicator) {
    scrollIndicator.addEventListener('click', () => {
      document.querySelector('.section-gallery')?.scrollIntoView({ behavior: 'smooth' });
    });
  }

  // ── Slot selection ──
  const selectedDate = document.getElementById('selectedDate');
  const selectedHall = document.getElementById('selectedHall');
  const selectedTime = document.getElementById('selectedTime');
  const addCartLink = document.getElementById('addCartLink');
  const mobileBookBtn = document.getElementById('mobileBookBtn');
  const serviceCartForm = document.getElementById('serviceCartForm');
  const cartFeedback = document.getElementById('cartFeedback');
  const cartDate = document.getElementById('cartDate');
  const cartSlotId = document.getElementById('cartSlotId');
  const cartVenueRoomId = document.getElementById('cartVenueRoomId');
  const cartStartTime = document.getElementById('cartStartTime');
  const cartEndTime = document.getElementById('cartEndTime');
  const cartPrice = document.getElementById('cartPrice');
  const estimatedTotal = document.querySelector('.estimated-row strong');
  const mobileBookPrice = document.querySelector('.mobile-book-price');
  const serviceId = <?= (int)($service['id'] ?? 0) ?>;

  function updateSelectedSlot(input) {
    if (!input) return;
    document.querySelectorAll('[data-slot-row]').forEach(row => {
      row.classList.toggle('is-selected', row.contains(input));
    });
    if (selectedDate) selectedDate.textContent = input.dataset.dateLabel || 'Selected date';
    if (selectedTime) selectedTime.textContent = input.dataset.timeLabel || 'Selected time';
    if (selectedHall && input.dataset.hallLabel) selectedHall.textContent = input.dataset.hallLabel;
    if (estimatedTotal && input.dataset.priceLabel) estimatedTotal.textContent = input.dataset.priceLabel;
    if (mobileBookPrice && input.dataset.priceLabel) mobileBookPrice.textContent = input.dataset.priceLabel;

    // Update hidden form fields for cart
    if (cartDate) cartDate.value = input.dataset.date || '';
    if (cartSlotId) cartSlotId.value = input.dataset.slotId || '';
    if (cartVenueRoomId) cartVenueRoomId.value = input.dataset.venueRoomId || input.dataset.roomId || '';
    if (cartStartTime) cartStartTime.value = input.dataset.startTime || '';
    if (cartEndTime) cartEndTime.value = input.dataset.endTime || '';
    if (cartPrice) {
      const numeric = input.dataset.priceValue || (input.dataset.priceLabel || '').replace(/[^0-9.]/g, '');
      if (numeric) cartPrice.value = numeric;
    }
  }

  document.querySelectorAll("input[name='service_slot']").forEach(input => {
    input.addEventListener('change', () => updateSelectedSlot(input));
  });
  updateSelectedSlot(document.querySelector("input[name='service_slot']:checked"));

  if (mobileBookBtn && addCartLink) {
    mobileBookBtn.addEventListener('click', (event) => {
      event.preventDefault();
      addCartLink.click();
    });
  }

  if (serviceCartForm && addCartLink) {
    serviceCartForm.addEventListener('submit', () => {
      const currentSelection = document.querySelector("input[name='service_slot']:checked");
      updateSelectedSlot(currentSelection);
      addCartLink.classList.add('is-submitting');
      addCartLink.disabled = true;
      addCartLink.innerHTML = '<i data-lucide="check-circle" size="16"></i><?= $isPackageContext ? 'Adding...' : 'Adding...' ?>';
      mobileBookBtn?.classList.add('is-submitting');
      if (cartFeedback) cartFeedback.classList.add('show');
      lucide.createIcons();
    });
  }

  // ── Hero gallery ──
  const heroMain = document.getElementById('heroMain');
  const heroThumbs = document.getElementById('heroThumbs');
  const heroDots = document.getElementById('heroDots');
  const heroPrev = document.getElementById('heroPrev');
  const heroNext = document.getElementById('heroNext');
  const galleryHeroItems = <?= json_encode($heroItems) ?>;

  let currentIndex = 0;
  const totalItems = galleryHeroItems.length;

  function renderHeroMedia(index) {
    if (!heroMain || totalItems === 0) return;
    const item = galleryHeroItems[index];
    if (!item) return;
    const isVideo = (item.type || 'image') === 'video';
    const src = item.file_url;
    const existingMedia = heroMain.querySelector('img, video');
    const overlay = document.getElementById('heroVideoOverlay');

    if (isVideo) {
      const vid = document.createElement('video');
      vid.src = src; vid.muted = true; vid.playsInline = true;
      vid.preload = 'metadata'; vid.id = 'heroVideo';
      if (existingMedia) existingMedia.replaceWith(vid);
      if (!overlay) {
        const newOverlay = document.createElement('div');
        newOverlay.className = 'gallery-video-overlay';
        newOverlay.id = 'heroVideoOverlay';
        newOverlay.innerHTML = '<span class="gallery-play-btn"><i data-lucide="play" fill="currentColor"></i></span>';
        heroMain.appendChild(newOverlay);
        lucide.createIcons({ nodes: [newOverlay] });
        setupVideoToggle();
      } else { overlay.classList.remove('playing'); }
    } else {
      const img = document.createElement('img');
      img.src = src; img.alt = '<?= $h($service['name'] ?? '') ?>'; img.id = 'heroImg';
      if (existingMedia) existingMedia.replaceWith(img);
      if (overlay) overlay.remove();
    }
    if (heroThumbs) heroThumbs.querySelectorAll('.gallery-thumb').forEach((t, i) => t.classList.toggle('active', i === index));
    if (heroDots) heroDots.querySelectorAll('.gallery-dot').forEach((d, i) => d.classList.toggle('active', i === index));
    if (heroThumbs) {
      const activeThumb = heroThumbs.querySelector('.gallery-thumb.active');
      if (activeThumb) activeThumb.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
    }
  }

  function setupVideoToggle() {
    const overlay = document.getElementById('heroVideoOverlay');
    const video = document.getElementById('heroVideo');
    if (!overlay || !video) return;
    overlay.replaceWith?.(overlay.cloneNode(true));
    video.replaceWith?.(video.cloneNode(true));
    const newOverlay = document.getElementById('heroVideoOverlay');
    const newVideo = document.getElementById('heroVideo');
    if (newOverlay) newOverlay.addEventListener('click', () => {
      if (newVideo) {
        if (newVideo.paused) { newVideo.play().catch(() => {}); newOverlay.classList.add('playing'); }
        else { newVideo.pause(); newOverlay.classList.remove('playing'); }
      }
    });
    if (newVideo) newVideo.addEventListener('pause', () => newOverlay?.classList.remove('playing'));
  }

  if (heroPrev && heroNext && totalItems > 1) {
    heroPrev.addEventListener('click', (e) => { e.stopPropagation(); currentIndex = (currentIndex - 1 + totalItems) % totalItems; renderHeroMedia(currentIndex); });
    heroNext.addEventListener('click', (e) => { e.stopPropagation(); currentIndex = (currentIndex + 1) % totalItems; renderHeroMedia(currentIndex); });
    heroDots?.addEventListener('click', (e) => {
      const dot = e.target.closest('.gallery-dot');
      if (dot) { currentIndex = parseInt(dot.dataset.index); renderHeroMedia(currentIndex); }
    });
    heroThumbs?.addEventListener('click', (e) => {
      const thumb = e.target.closest('.gallery-thumb');
      if (thumb) { currentIndex = parseInt(thumb.dataset.index); renderHeroMedia(currentIndex); }
    });
  }
  setupVideoToggle();

  // ── Lightbox ──
  const lightbox = document.getElementById('lightbox');
  const lightboxContent = document.getElementById('lightboxContent');
  const lightboxClose = document.getElementById('lightboxClose');
  const lightboxPrevBtn = document.getElementById('lightboxPrev');
  const lightboxNextBtn = document.getElementById('lightboxNext');
  let lightboxItems = [];
  let lbIndex = 0;

  function openLightbox(index) {
    lbIndex = index; renderLightbox();
    lightbox.classList.add('open');
    document.body.style.overflow = 'hidden';
  }
  function closeLightbox() {
    lightbox.classList.remove('open');
    document.body.style.overflow = '';
    const vid = lightboxContent.querySelector('video');
    if (vid) vid.pause();
  }
  function renderLightbox() {
    const item = lightboxItems[lbIndex];
    if (!item) return;
    lightboxContent.innerHTML = item.type === 'video'
      ? '<video src="' + item.file_url + '" controls autoplay></video>'
      : '<img src="' + item.file_url + '" alt="">';
  }

  document.querySelectorAll('.portfolio-item').forEach((el) => {
    lightboxItems = [];
    document.querySelectorAll('.portfolio-item').forEach((item) => {
      lightboxItems.push({ file_url: item.dataset.src, type: item.dataset.type });
    });
    const idx = Array.from(document.querySelectorAll('.portfolio-item')).indexOf(el);
    el.addEventListener('click', () => openLightbox(idx));
  });

  if (lightboxClose) lightboxClose.addEventListener('click', closeLightbox);
  if (lightboxPrevBtn) lightboxPrevBtn.addEventListener('click', () => { lbIndex = (lbIndex - 1 + lightboxItems.length) % lightboxItems.length; renderLightbox(); });
  if (lightboxNextBtn) lightboxNextBtn.addEventListener('click', () => { lbIndex = (lbIndex + 1) % lightboxItems.length; renderLightbox(); });
  lightbox?.addEventListener('click', (e) => { if (e.target === lightbox) closeLightbox(); });
  document.addEventListener('keydown', (e) => {
    if (!lightbox?.classList.contains('open')) return;
    if (e.key === 'Escape') closeLightbox();
    if (e.key === 'ArrowLeft') { lbIndex = (lbIndex - 1 + lightboxItems.length) % lightboxItems.length; renderLightbox(); }
    if (e.key === 'ArrowRight') { lbIndex = (lbIndex + 1) % lightboxItems.length; renderLightbox(); }
  });

  // ── Cursor follower (desktop only) ──
  if (window.innerWidth > 1024 && !prefersReduced) {
    const cursor = document.createElement('div');
    cursor.className = 'cursor-follower';
    document.body.appendChild(cursor);
    document.addEventListener('mousemove', (e) => {
      cursor.style.transform = 'translate(' + (e.clientX - 5) + 'px, ' + (e.clientY - 5) + 'px)';
    });
  }

  // Profile dropdown toggle
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.tb-profile-btn');
    if (btn) {
      const expanded = btn.getAttribute('aria-expanded') === 'true';
      document.querySelectorAll('.tb-profile-btn').forEach(b => b.setAttribute('aria-expanded', 'false'));
      btn.setAttribute('aria-expanded', String(!expanded));
      return;
    }
    document.querySelectorAll('.tb-profile-btn').forEach(b => b.setAttribute('aria-expanded', 'false'));
  });
});
</script>
</body>
</html>
