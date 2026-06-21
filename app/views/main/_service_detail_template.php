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
$todayDate = date('Y-m-d');
$venueDateInputValue = $selectedDate !== ''
    ? $selectedDate
    : (strtotime($datePickerMin) > strtotime($todayDate) ? $datePickerMin : $todayDate);
$venueDateDisplayLabel = $selectedDate !== '' ? date('M j, Y', strtotime($selectedDate)) : 'Today';
$datePickerAction = URLROOT . '/customerServices/detail/' . (int)($service['id'] ?? 0);
$venueRooms = $service['venue_rooms'] ?? [];
$category = strtolower(trim((string)($service['category'] ?? '')));
$categorySlug = strtolower(trim((string)($service['category_slug'] ?? '')));
$categoryKey = str_replace(['-', '_'], ' ', trim($categorySlug . ' ' . $category));
$isVenue = ($detailPageType ?? '') === 'venue'
    || strpos($categoryKey, 'venue') !== false
    || strpos($categoryKey, 'hall') !== false;
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
$hasInitialBookOption = $isVenue
    ? $firstVenueRoom !== null
    : ($isSlotBooking ? $firstSlot !== null : $firstAvailable !== null);
$initialBookingHref = $hasInitialBookOption ? URLROOT . '/users/auth' : '#detail-date';
$initialBookingLabel = $hasInitialBookOption ? 'Add to cart' : (($isVenue || !$isSlotBooking) ? 'Choose date' : 'Choose slot');
$venueCapacity = !empty($venueRooms) ? max(array_map(function ($room) {
    return (int)($room['capacity'] ?? 1);
}, $venueRooms)) : (int)($service['max_concurrent'] ?? 1);
$metricCount = (int)($service['max_concurrent'] ?? 1);
$pluralizeMetric = function ($count, $singular, $plural = null) {
    return (int)$count . ' ' . ((int)$count === 1 ? $singular : ($plural ?? $singular . 's'));
};
$capacityMetricLabel = 'Bookings';
$capacityMetricValue = $pluralizeMetric($metricCount, 'booking') . ' per day';
$summaryCapacityMetricLabel = $capacityMetricLabel;
$summaryCapacityMetricValue = $capacityMetricValue;
$capacityCategoryKey = $categoryKey;
if ($isVenue) {
    $capacityMetricLabel = 'Guest Capacity';
    $capacityMetricValue = (int)$venueCapacity . ' guests';
    $summaryCapacityMetricLabel = 'Capacity';
    $summaryCapacityMetricValue = (int)$venueCapacity . ' guests';
} elseif (strpos($capacityCategoryKey, 'photo') !== false) {
    $capacityMetricLabel = 'Bookings';
    $capacityMetricValue = $pluralizeMetric($metricCount, 'booking') . ' per day';
    $summaryCapacityMetricLabel = 'Bookings per day';
    $summaryCapacityMetricValue = $pluralizeMetric($metricCount, 'booking') . ' per day';
} elseif (strpos($capacityCategoryKey, 'makeup') !== false || strpos($capacityCategoryKey, 'make up') !== false) {
    $capacityMetricLabel = 'Appointments';
    $capacityMetricValue = $pluralizeMetric($metricCount, 'appointment') . ' per day';
    $summaryCapacityMetricLabel = 'Appointments per day';
    $summaryCapacityMetricValue = $pluralizeMetric($metricCount, 'appointment') . ' per day';
} elseif (strpos($capacityCategoryKey, 'cater') !== false) {
    $capacityMetricLabel = 'Serving Capacity';
    $capacityMetricValue = $pluralizeMetric($metricCount, 'serving') . ' per event';
    $summaryCapacityMetricLabel = 'Serving Capacity';
    $summaryCapacityMetricValue = $pluralizeMetric($metricCount, 'serving') . ' per event';
} elseif (strpos($capacityCategoryKey, 'planner') !== false || strpos($capacityCategoryKey, 'planning') !== false) {
    $capacityMetricLabel = 'Events handled';
    $capacityMetricValue = $pluralizeMetric($metricCount, 'event') . ' per day';
    $summaryCapacityMetricLabel = 'Events handled per day';
    $summaryCapacityMetricValue = $pluralizeMetric($metricCount, 'event') . ' per day';
}
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
    return number_format((float)$value, 0) . ' MMK';
};

$moneyRange = function ($service) use ($money) {
    return $money($service['display_price'] ?? $service['customize_price'] ?? $service['price_max'] ?? $service['price'] ?? 0);
};
$isRentalCategory = in_array(strtolower(trim((string)($service['category_slug'] ?? ''))), ['attire'], true)
    || in_array(strtolower(trim((string)($service['category'] ?? ''))), ['attire'], true);
$decorationStyles = is_array($service['decoration_styles'] ?? null) ? $service['decoration_styles'] : [];
$isDecorationCategory = strtolower(trim((string)($service['category_slug'] ?? ''))) === 'decoration'
    || strtolower(trim((string)($service['category'] ?? ''))) === 'decoration';
$rentalPricing = is_array($service['rental_pricing'] ?? null) ? $service['rental_pricing'] : [];
$rentalOptions = [];
if ($isRentalCategory) {
    $borrowPackagePrice = (float)($rentalPricing['borrow_package_price'] ?? $rentalPricing['borrow_price'] ?? 0);
    $borrowCustomizePrice = (float)($rentalPricing['borrow_customize_price'] ?? $rentalPricing['borrow_price'] ?? $borrowPackagePrice);
    $buyPackagePrice = (float)($rentalPricing['buy_package_price'] ?? $rentalPricing['buy_price'] ?? 0);
    $buyCustomizePrice = (float)($rentalPricing['buy_customize_price'] ?? $rentalPricing['buy_price'] ?? $buyPackagePrice);
    if ($borrowPackagePrice > 0 || $borrowCustomizePrice > 0) {
        $returnDays = (int)($rentalPricing['return_days'] ?? 0);
        $rentalOptions[] = [
            'label' => 'Borrow',
            'package' => $borrowPackagePrice > 0 ? $money($borrowPackagePrice) : '—',
            'customize' => $borrowCustomizePrice > 0 ? $money(max($borrowPackagePrice, $borrowCustomizePrice)) : '—',
            'meta' => $returnDays > 0 ? $returnDays . ' ' . ($returnDays === 1 ? 'day' : 'days') . ' return' : 'Rental option',
            'icon' => 'refresh-cw',
        ];
    }
    if ($buyPackagePrice > 0 || $buyCustomizePrice > 0) {
        $rentalOptions[] = [
            'label' => 'Buy',
            'package' => $buyPackagePrice > 0 ? $money($buyPackagePrice) : '—',
            'customize' => $buyCustomizePrice > 0 ? $money(max($buyPackagePrice, $buyCustomizePrice)) : '—',
            'meta' => 'Purchase option',
            'icon' => 'shopping-bag',
        ];
    }
}
$activeServicePrice = (float)($service['display_price'] ?? $service['customize_price'] ?? $service['price_max'] ?? $service['price'] ?? 0);
$isPackageContext = ($service['price_context'] ?? '') === 'package' && !empty($service['package_context']);
$packageContext = $isPackageContext ? ($service['package_context'] ?? []) : [];
$addonContext = is_array($service['addon_context'] ?? null) ? $service['addon_context'] : [];
$isAddonContext = !$isPackageContext && !empty($addonContext['package_id']);
$packageName = trim((string)($packageContext['package_name'] ?? 'Wedding package'));
$packageSlug = trim((string)($packageContext['package_slug'] ?? ''));
$addonPackageName = trim((string)($addonContext['package_name'] ?? 'Wedding package'));
$addonPackageSlug = trim((string)($addonContext['package_slug'] ?? ''));
$packageDetailUrl = $packageSlug !== ''
    ? URLROOT . '/customerServices/packageDetail/' . rawurlencode($packageSlug)
    : ($addonPackageSlug !== ''
        ? URLROOT . '/customerServices/packageDetail/' . rawurlencode($addonPackageSlug)
        : URLROOT . '/customerServices/packages');
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
    : ($isAddonContext ? ['addon_package_id' => (int)$addonContext['package_id']] : []);

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
$reviewDistribution = $service['review_distribution'] ?? null;
if ($reviewDistribution) {
    foreach ([5,4,3,2,1] as $star) {
        $ratingBuckets[$star] = (int)($reviewDistribution[$star] ?? 0);
    }
} else {
    foreach ($reviews as $review) {
        $bucket = max(1, min(5, (int)round((float)($review['rating'] ?? 0))));
        $ratingBuckets[$bucket]++;
    }
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
  --wine: #9A687F;
  --wine-dark: #7E4F65;
  --wine-glow: rgba(154, 104, 127, 0.12);
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
  margin: 0 auto;
  padding: 8px 24px 60px;
}

/* Product-style selected service detail */
.product-detail {
  display: grid;
  grid-template-columns: minmax(0, 480px) minmax(0, 1fr);
  gap: clamp(28px, 4vw, 48px);
  align-items: start;
  margin: 0 0 var(--pad-section);
}

.product-media {
  min-width: 0;
}

.product-media .gallery-frame {
  border-radius: 0;
  box-shadow: none;
  background: transparent;
  overflow: visible;
}

.product-media .gallery-main {
  height: clamp(300px, 38vw, 440px);
  background: #f3f1ef;
  border-radius: 8px;
  overflow: hidden;
  cursor: pointer;
}

.product-media .gallery-main img,
.product-media .gallery-main video {
  object-fit: cover;
  opacity: 1;
  transition: opacity .45s ease;
}

.product-media .gallery-main.is-fading img,
.product-media .gallery-main.is-fading video {
  opacity: 0;
}

.product-media .gallery-thumbs {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 10px;
  padding: 14px 0 0;
  overflow: visible;
}

.product-media .gallery-thumb {
  flex: initial;
  width: 100%;
  height: 64px;
  border-radius: 6px;
  border: 1px solid transparent;
  opacity: 1;
  background: #f3f1ef;
}

.product-media .gallery-thumb.active {
  border-color: #c9c1bb;
}

.product-copy {
  min-width: 0;
  padding-top: 4px;
}

.product-title {
  font-family: var(--font-sans);
  font-size: clamp(24px, 2.5vw, 34px);
  font-weight: 600;
  color: var(--ink);
  letter-spacing: 0;
  line-height: 1.15;
}

.product-rating-row {
  display: flex;
  align-items: center;
  gap: 9px;
  margin-top: 10px;
  color: var(--muted);
  font-size: 12px;
  font-weight: 600;
}

.product-stars {
  display: inline-flex;
  gap: 1px;
  color: #d4d4d4;
  font-size: 13px;
  letter-spacing: 0;
}

.product-stars .is-active {
  color: #d8a514;
}

.product-price {
  margin-top: 10px;
  color: var(--ink);
  font-size: clamp(20px, 2vw, 28px);
  font-weight: 600;
}

.product-divider {
  border: 0;
  border-top: 1px solid rgba(33,29,26,.10);
  margin: 24px 0;
}

.product-about-title {
  color: var(--ink);
  font-size: 13px;
  font-weight: 800;
  letter-spacing: .12em;
  text-transform: uppercase;
}

.product-about-text {
  margin-top: 10px;
  color: var(--muted);
  font-size: 13px;
  line-height: 1.75;
  white-space: pre-line;
}

.product-facts {
  display: grid;
  gap: 14px;
  margin-top: 22px;
}

.product-fact {
  display: grid;
  grid-template-columns: 150px minmax(0, 1fr);
  gap: 16px;
  align-items: center;
  color: var(--muted);
  font-size: 13px;
}

.product-fact-label {
  color: var(--ink-soft);
  font-weight: 800;
}

.verified-supplier {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  color: var(--ink);
  font-weight: 700;
}

.verified-mark {
  display: inline-grid;
  place-items: center;
  width: 17px;
  height: 17px;
  border-radius: 50%;
  background: #1f9d55;
  color: #fff;
  font-size: 11px;
  font-weight: 900;
  line-height: 1;
}

.product-actions {
  display: flex;
  gap: 14px;
  align-items: center;
  margin-top: 28px;
}

.product-action-primary,
.product-action-secondary {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-height: 42px;
  padding: 0 28px;
  border-radius: 6px;
  font-size: 11px;
  font-weight: 800;
  letter-spacing: .08em;
  text-transform: uppercase;
}

.product-action-primary {
  border: 1px solid #3F241A;
  background: #3F241A;
  color: #FFF8EF;
}

.product-action-secondary {
  border: 1px solid rgba(63,36,26,.24);
  background: #FFF8EF;
  color: #3F241A;
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
.rental-price-stack {
  display: grid;
  gap: 10px;
  margin-top: 16px;
  padding-top: 16px;
  border-top: 1px solid var(--line-soft);
}
.rental-price-card {
  display: grid;
  grid-template-columns: 36px minmax(0, 1fr);
  gap: 12px;
  align-items: center;
  padding: 12px;
  border: 1px solid var(--line-soft);
  border-radius: var(--radius);
  background: rgba(255,255,255,0.56);
}
.rental-price-card .rental-icon {
  width: 36px;
  height: 36px;
  display: grid;
  place-items: center;
  border-radius: var(--radius);
  background: var(--wine-glow);
  color: var(--wine);
}
.rental-price-card span {
  display: block;
  color: var(--muted);
  font-size: 11px;
  font-weight: 800;
  text-transform: uppercase;
}
.rental-price-card strong {
  display: block;
  color: var(--wine-dark);
  font-size: 16px;
  font-weight: 900;
}
.rental-price-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 8px;
  margin: 5px 0 2px;
}
.rental-price-grid em {
  display: block;
  color: var(--muted);
  font-size: 10px;
  font-style: normal;
  font-weight: 800;
  text-transform: uppercase;
}
.rental-price-grid strong {
  font-size: 14px;
  overflow-wrap: anywhere;
}
.rental-price-card small {
  display: block;
  color: var(--muted);
  font-size: 11px;
  font-weight: 600;
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

.booking-section.is-venue-booking {
  margin-top: clamp(34px, 4vw, 54px);
  scroll-margin-top: 150px;
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
  border: 1px solid rgba(63, 36, 26, .18);
  border-radius: 14px;
  background: #FFF8EF;
  color: #3F241A;
  padding: 0 12px;
  font-size: 13px;
  font-weight: 800;
  outline: none;
  cursor: pointer;
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

.venue-date-form {
  display: contents;
}

.venue-date-prompt {
  display: flex;
  align-items: center;
  justify-content: center;
  flex-wrap: wrap;
  gap: 12px;
  padding: 16px 18px;
  text-align: center;
}

.venue-date-prompt > span:first-child {
  color: var(--ink);
  font-size: 15px;
  font-weight: 800;
}

.venue-date-input-wrap {
  position: relative;
  min-height: 32px;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  border: 1px solid rgba(63, 36, 26, .18);
  border-radius: 6px;
  background: #FFF8EF;
  color: #3F241A;
  padding: 0 10px;
  font-size: 12px;
  font-weight: 800;
  cursor: pointer;
  overflow: hidden;
  box-shadow: 0 4px 14px rgba(63, 36, 26, .06);
}

.venue-date-input-wrap input {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  opacity: 0;
  cursor: pointer;
}

.venue-date-display {
  min-width: 54px;
  pointer-events: none;
}

.venue-date-icon,
.venue-date-chevron {
  flex: 0 0 auto;
  pointer-events: none;
  color: #7A4E3D;
  width: 13px !important;
  height: 13px !important;
  stroke-width: 2.2;
}

.venue-date-chevron {
  margin-left: auto;
}

.gp-calendar-popover {
  position: fixed;
  z-index: 10010;
  width: min(250px, calc(100vw - 32px));
  padding: 12px;
  border: 1px solid rgba(63, 36, 26, .14);
  border-radius: 10px;
  background: rgba(255, 248, 239, .98);
  box-shadow: 0 24px 60px rgba(63, 36, 26, .18);
  backdrop-filter: blur(18px);
  -webkit-backdrop-filter: blur(18px);
}

.gp-calendar-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  color: #3F241A;
  font-size: 12px;
  font-weight: 900;
  margin-bottom: 9px;
}

.gp-calendar-nav {
  width: 22px;
  height: 22px;
  display: inline-grid;
  place-items: center;
  border: 0;
  border-radius: 7px;
  background: transparent;
  color: #7A4E3D;
  cursor: pointer;
}

.gp-calendar-nav:hover {
  background: rgba(63, 36, 26, .08);
}

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
  color: #6F5448;
  font-size: 11px;
}

.gp-calendar-day-name {
  color: rgba(63, 36, 26, .52);
  font-weight: 800;
}

.gp-calendar-day {
  border: 0;
  border-radius: 6px;
  background: transparent;
  font-weight: 800;
  cursor: pointer;
}

.gp-calendar-day:hover {
  background: rgba(122, 78, 61, .12);
}

.gp-calendar-day.is-selected {
  background: #3F241A;
  color: #FFF8EF;
}

.gp-calendar-day.is-today:not(.is-selected) {
  outline: 1px solid rgba(63, 36, 26, .28);
}

.gp-calendar-day.is-disabled {
  color: rgba(63, 36, 26, .24);
  cursor: not-allowed;
}

.venue-date-prompt input,
.venue-date-change input {
  min-height: 40px;
  border: 1px solid var(--line);
  border-radius: 8px;
  background: var(--panel-strong);
  color: var(--ink);
  padding: 0 12px;
  font-size: 13px;
  font-weight: 700;
  outline: none;
  cursor: pointer;
}

.venue-date-change {
  display: flex;
  justify-content: flex-start;
  margin-top: 14px;
}

.booking-section.is-venue-booking .section-title {
  font-size: clamp(26px, 3vw, 38px);
  margin: 0;
}

.venue-halls-heading {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 14px;
  padding-top: 10px;
}

.booking-section.is-venue-booking .venue-date-change {
  margin-top: 8px;
}

.booking-section.is-venue-booking .venue-date-change .venue-date-input-wrap {
  min-width: 172px;
}

.booking-section.is-venue-booking .booking-grid {
  gap: 20px;
  margin-top: 0;
}

.booking-section.is-venue-booking .availability-list {
  gap: 10px;
}

.booking-section.is-venue-booking .availability-row {
  min-height: 64px;
  padding: 12px 14px;
}

.booking-grid.is-date-pending .availability-row,
.booking-grid.is-date-pending .sticky-summary {
  filter: blur(1.4px);
  opacity: .62;
  pointer-events: none;
  user-select: none;
}

.booking-grid.is-date-pending .availability-row::after,
.booking-grid.is-date-pending .sticky-summary::after {
  content: "";
  position: absolute;
  inset: 0;
  border-radius: inherit;
  background: rgba(63, 36, 26, .18);
  pointer-events: none;
}

.availability-list {
  display: grid;
  gap: 14px;
}

.availability-row {
  position: relative;
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

.hall-row-body {
  display: grid;
  grid-template-columns: 112px minmax(0, 1fr);
  gap: 14px;
  align-items: start;
}

.hall-photo {
  width: 112px;
  aspect-ratio: 4 / 3;
  overflow: hidden;
  border: 1px solid rgba(118,90,70,0.14);
  border-radius: 8px;
  background:
    linear-gradient(135deg, rgba(216,180,106,0.14), rgba(185,74,72,0.10)),
    rgba(255,250,247,0.8);
}

.hall-photo img {
  width: 100%;
  height: 100%;
  display: block;
  object-fit: cover;
}

.hall-photo.is-empty {
  display: grid;
  place-items: center;
  color: rgba(118,90,70,0.45);
}

.style-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
  gap: 14px;
}

.style-card {
  border: 1px solid rgba(118,90,70,0.14);
  border-radius: 8px;
  overflow: hidden;
  background: rgba(255,250,247,0.78);
}

.style-photo {
  aspect-ratio: 4 / 3;
  display: grid;
  place-items: center;
  color: rgba(118,90,70,0.45);
  background:
    linear-gradient(135deg, rgba(216,180,106,0.14), rgba(185,74,72,0.10)),
    rgba(255,250,247,0.8);
}

.style-photo img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.style-body {
  padding: 12px 14px;
}

.style-body strong {
  display: block;
  color: var(--ink);
  font-size: 14px;
}

.style-body span {
  display: block;
  color: var(--wine);
  font-size: 13px;
  font-weight: 800;
  margin-top: 4px;
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
  border: 0; border-radius: 8px;
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
  background: #fff;
  border: 1px solid rgba(63, 36, 26, .10);
  border-radius: 8px;
  padding: 20px 22px;
  box-shadow: 0 12px 28px rgba(63, 36, 26, .06);
}

.rating-big {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 14px;
  color: var(--ink);
  font-size: 18px;
  font-weight: 900;
}

.rating-stars {
  display: inline-flex;
  gap: 3px;
  color: #E5C33A;
  font-size: 17px;
  letter-spacing: 0;
}

.rating-bars {
  display: grid; gap: 8px;
  margin-top: 18px;
  font-size: 11px; color: rgba(63,36,26,.62); font-weight: 700;
}

.bar-row {
  display: grid;
  grid-template-columns: 14px minmax(90px, 1fr) 24px;
  gap: 8px; align-items: center;
}

.bar-track { height: 7px; overflow: hidden; border-radius: 0; background: rgba(63,36,26,0.10); }
.bar-fill { height: 100%; display: block; border-radius: inherit; background: #E8C94B; transition: width 0.4s ease; }

.reviews-section .section-title {
  font-size: clamp(24px, 3vw, 36px);
}

.related-section .section-title {
  font-size: clamp(24px, 3vw, 36px);
}

.related-kicker {
  display: block;
  margin-bottom: 8px;
  color: var(--wine);
  font-size: 11px;
  font-weight: 800;
  letter-spacing: .14em;
  text-transform: lowercase;
}

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
  .product-detail { grid-template-columns: 1fr; }
  .product-media .gallery-main { height: clamp(320px, 58vw, 460px); }
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
  .page-shell { padding: 8px 16px 40px; margin-top: 18px; }
  .product-detail { gap: 24px; margin-top: 0; }
  .product-media .gallery-main { height: 250px; }
  .product-media .gallery-thumbs { grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 8px; }
  .product-media .gallery-thumb { height: 52px; }
  .product-fact { grid-template-columns: 1fr; gap: 4px; }
  .product-actions { flex-direction: column; align-items: stretch; }
  .product-action-primary,
  .product-action-secondary { justify-content: center; }
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
  .hall-row-body { grid-template-columns: 1fr; }
  .hall-photo { width: 100%; max-height: 190px; }
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

/* ── WISHLIST HEART (detail page) ── */
.dt-heart{
  display:inline-flex;align-items:center;gap:8px;
  padding:10px 18px;border-radius:999px;border:1px solid rgba(255,255,255,.28);
  background:rgba(0,0,0,.22);backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);
  color:rgba(255,255,255,.82);cursor:pointer;
  font-family:var(--font-body);font-size:13px;font-weight:600;
  transition:all .2s var(--ease);margin-top:12px;
}
.dt-heart:hover{background:rgba(0,0,0,.38);border-color:rgba(255,255,255,.48)}
.dt-heart.is-saved{color:#ff7b7b;border-color:rgba(229,91,91,.28);background:rgba(0,0,0,.32)}
.dt-heart.is-loading{pointer-events:none;opacity:.6}
.dt-heart-emoji{font-size:16px;line-height:1}
</style>
</head>
<body>

<?php $gpNavActive = 'services'; require APPROOT . '/views/layouts/customerHomeNav.php'; ?>

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

<!-- ─── PAGE SHELL ───────────────────────────────── -->
<main class="page-shell">

  <section class="product-detail" data-aos="fade-up" data-aos-duration="700">
    <div class="product-media">
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
          <?php endif; ?>
        </div>

        <?php if (count($heroItems) > 1): ?>
          <div class="gallery-thumbs" id="heroThumbs">
            <?php foreach (array_slice($heroItems, 0, 4) as $i => $item):
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
    </div>

    <div class="product-copy">
      <h1 class="product-title"><?= $h($service['name'] ?? '') ?></h1>
      <div class="product-rating-row">
        <span class="product-stars" aria-label="<?= $h(number_format((float)$rating, 1)) ?> star rating">
          <?php $roundedRating = (int)round($rating); ?>
          <?php for ($star = 1; $star <= 5; $star++): ?>
            <span class="<?= $star <= $roundedRating ? 'is-active' : '' ?>">★</span>
          <?php endfor; ?>
        </span>
        <span><?= number_format((float)$rating, 1) ?> (<?= (int)$reviewCount ?> reviews)</span>
      </div>
      <div class="product-price"><?= $moneyRange($service) ?></div>

      <hr class="product-divider">

      <h2 class="product-about-title">About service</h2>
      <div class="product-about-text">
        <?= $description !== '' ? $h($description) : 'This wedding service is available from a Golden Promise supplier. Choose your preferred date below to view availability and booking options.' ?>
      </div>

      <div class="product-facts" aria-label="Service facts">
        <div class="product-fact">
          <span class="product-fact-label"><?= $h($capacityMetricLabel) ?>:</span>
          <span><?= $h($capacityMetricValue) ?></span>
        </div>
        <div class="product-fact">
          <span class="product-fact-label">Wedding supply:</span>
          <span class="verified-supplier">
            <?= $h($supplierName) ?>
            <span class="verified-mark" aria-label="Verified supplier">✓</span>
            <span>Verified supplier</span>
          </span>
        </div>
        <div class="product-fact">
          <span class="product-fact-label">Booking type:</span>
          <span><?= $h($durationText($service)) ?></span>
        </div>
      </div>

      <div class="product-actions">
        <a class="product-action-primary" href="#detail-date">Book service</a>
        <a class="product-action-secondary" href="#reviews">View reviews</a>
      </div>
    </div>
  </section>

  <!-- SECTION: AVAILABILITY / BOOKING -->
  <section class="booking-section <?= $isVenue ? 'is-venue-booking' : '' ?>" id="<?= $isVenue ? 'available-halls' : 'availability' ?>" data-aos="fade-up" data-aos-duration="800">
    <?php if (!$isVenue): ?>
    <span class="section-label">Pick a date</span>
    <?php endif; ?>
    <?php if (!$isVenue): ?>
    <h2 class="section-title"><?= $isSlotBooking ? 'Available Dates &amp; Times' : 'Available Dates' ?></h2>
    <?php endif; ?>
    <?php if (!$isVenue): ?>
    <p class="section-sub">
      <?= $selectedDateLabel !== ''
        ? $h(($isSlotBooking ? 'Available times for ' : 'Available range for ') . $selectedDateLabel)
        : ($isSlotBooking ? 'Choose your preferred date and time' : 'Choose an available date') ?>
    </p>
    <?php endif; ?>

    <?php if (!$isVenue): ?>
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
            : ($isSlotBooking ? 'If you came from the service list, that date is prefilled here.' : 'Full-day services show one available time range for each open date.') ?>
        </span>
      </div>
      <div class="date-picker-control">
        <span class="venue-date-input-wrap">
          <i class="venue-date-icon" data-lucide="calendar-days" size="8"></i>
          <span class="venue-date-display"><?= $h($selectedDate !== '' ? date('M j, Y', strtotime($selectedDate)) : 'Today') ?></span>
          <i class="venue-date-chevron" data-lucide="chevron-down" size="8"></i>
          <input class="gp-calendar-input" type="date" id="detail-date" name="date" value="<?= $h($selectedDate) ?>" min="<?= $h($datePickerMin) ?>" max="<?= $h($datePickerMax) ?>" aria-label="Wedding date">
        </span>
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
    <?php endif; ?>

    <div class="booking-grid <?= $isVenue && $selectedDate === '' ? 'is-date-pending' : '' ?>">
      <div class="availability-list">
        <?php if ($isVenue): ?>
          <div class="venue-halls-heading">
            <h2 class="section-title">Available Halls</h2>
            <?php if ($selectedDateLabel !== ''): ?>
              <form class="venue-date-form venue-date-change" method="GET" action="<?= $h($datePickerAction) ?>#available-halls">
                <?php foreach ($packageQueryFields as $fieldName => $fieldValue): ?>
                  <?php if ($fieldValue > 0): ?>
                    <input type="hidden" name="<?= $h($fieldName) ?>" value="<?= (int)$fieldValue ?>">
                  <?php endif; ?>
                <?php endforeach; ?>
                <span class="venue-date-input-wrap">
          <i class="venue-date-icon" data-lucide="calendar-days" size="8"></i>
          <span class="venue-date-display"><?= $h($venueDateDisplayLabel) ?></span>
          <i class="venue-date-chevron" data-lucide="chevron-down" size="8"></i>
                  <input class="gp-calendar-input" type="date" id="detail-date" name="date" value="<?= $h($venueDateInputValue) ?>" min="<?= $h($datePickerMin) ?>" max="<?= $h($datePickerMax) ?>" aria-label="Wedding date">
                </span>
              </form>
            <?php endif; ?>
          </div>
          <?php if ($selectedDate === ''): ?>
            <form class="venue-date-form" method="GET" action="<?= $h($datePickerAction) ?>#available-halls">
              <?php foreach ($packageQueryFields as $fieldName => $fieldValue): ?>
                <?php if ($fieldValue > 0): ?>
                  <input type="hidden" name="<?= $h($fieldName) ?>" value="<?= (int)$fieldValue ?>">
                <?php endif; ?>
              <?php endforeach; ?>
              <div class="empty-state venue-date-prompt">
                <span>Please choose a wedding date to see which halls are available.</span>
                <span class="venue-date-input-wrap">
                  <i class="venue-date-icon" data-lucide="calendar-days" size="8"></i>
                  <span class="venue-date-display"><?= $h($venueDateDisplayLabel) ?></span>
                  <i class="venue-date-chevron" data-lucide="chevron-down" size="8"></i>
                  <input class="gp-calendar-input" type="date" id="detail-date" name="date" value="<?= $h($venueDateInputValue) ?>" min="<?= $h($datePickerMin) ?>" max="<?= $h($datePickerMax) ?>" aria-label="Wedding date">
                </span>
              </div>
            </form>
          <?php endif; ?>
          <?php if (empty($venueRooms)): ?>
            <div class="empty-state"><i data-lucide="door-open" size="22"></i>No halls have been published for this venue yet.</div>
          <?php else: ?>
            <?php $hasSelectedRoom = false; ?>
            <?php foreach ($venueRooms as $index => $room): ?>
              <?php
                $isPackageHallRow = $isPackageContext && (int)($packageContext['venue_room_id'] ?? 0) > 0 && (int)($room['id'] ?? 0) === (int)($packageContext['venue_room_id'] ?? 0);
                $roomDisplayPrice = $isPackageContext ? $packageServicePrice : (float)($room['price'] ?? 0);
                $roomPhotoUrl = trim((string)($room['photo_url'] ?? ''));
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
                <div class="hall-row-body">
                  <div class="hall-photo <?= $roomPhotoUrl === '' ? 'is-empty' : '' ?>">
                    <?php if ($roomPhotoUrl !== ''): ?>
                      <img src="<?= $h($roomPhotoUrl) ?>" alt="<?= $h(($room['name'] ?: 'Venue hall') . ' photo') ?>" loading="lazy">
                    <?php else: ?>
                      <i data-lucide="image" size="20"></i>
                    <?php endif; ?>
                  </div>
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
            <?php if (!empty($slots)): ?>
              <?php $checked = !$hasSelectedSlot; if ($checked) { $hasSelectedSlot = true; } ?>
              <div class="availability-row is-fullday <?= $rowSelected ? 'is-selected' : '' ?> <?= $isRequestedDate ? 'is-requested-date' : '' ?>" data-fullday-row data-date="<?= $h($day['date'] ?? '') ?>" data-date-label="<?= $h($day['day_label'] ?? $day['date']) ?>" data-price-value="<?= $h($isPackageContext ? $packageServicePrice : $activeServicePrice) ?>" data-aos="fade-up" data-aos-delay="<?= min($dayIdx * 80, 300) ?>">
                <span class="radio-dot"></span>
                <div>
                  <div class="availability-head">
                    <span class="availability-name">
                      <?= $h($dayLabel) ?>
                      <span>Full day</span>
                    </span>
                    <span class="availability-status"><?= $h($day['status'] ?? 'Available') ?></span>
                  </div>
                  <span class="availability-range"><i data-lucide="calendar" size="14"></i>Full day</span>
                </div>
              </div>
            <?php else: ?>
              <div class="availability-row is-unavailable" data-aos="fade-up" data-aos-delay="<?= min($dayIdx * 80, 300) ?>">
                <span class="radio-dot"></span>
                <div>
                  <div class="availability-head">
                    <span class="availability-name">
                      <?= $h($dayLabel) ?>
                      <span><?= $h($slotSummary) ?></span>
                    </span>
                    <span class="availability-status"><?= $h($day['status'] ?? 'Booked') ?></span>
                  </div>
                </div>
              </div>
            <?php endif; ?>
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
              <span id="selectedHall"><?= $h($firstVenueRoom['name'] ?? 'Select after date') ?></span>
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
              <span id="selectedTime"><?= $firstVenueRoom ? (int)($firstVenueRoom['capacity'] ?? 1) . ' guests' : 'Select after date' ?></span>
            </div>
          <?php elseif (!$isSlotBooking): ?>
            <div class="summary-line">
              <?= $h($summaryCapacityMetricLabel) ?>
              <span id="selectedTime"><?= $h($summaryCapacityMetricValue) ?></span>
            </div>
          <?php elseif ($isSlotBooking && $firstSlot): ?>
            <div class="summary-line">
              Time
              <span id="selectedTime"><?= $h($firstSlot['label'] ?? '') ?></span>
            </div>
          <?php else: ?>
            <div class="summary-line">
              Time
              <span id="selectedTime">Choose a time slot</span>
            </div>
          <?php endif; ?>
        </div>
        <div class="estimated-row">
          <span><?= $isPackageContext ? 'Package service price' : 'Estimated total' ?></span>
          <strong><?= $isPackageContext ? $money($packageServicePrice) : ($isVenue && $firstVenueRoom ? $money($firstVenueRoom['price'] ?? 0) : $moneyRange($service)) ?></strong>
        </div>
        <?php if (!empty($rentalOptions)): ?>
          <div class="package-price-panel" aria-label="Dress and accessory pricing">
            <?php foreach ($rentalOptions as $option): ?>
              <div class="package-price-line">
                <span><?= $h($option['label']) ?> package</span>
                <strong><?= $h($option['package']) ?></strong>
              </div>
              <div class="package-price-line">
                <span><?= $h($option['label']) ?> customize</span>
                <strong><?= $h($option['customize']) ?></strong>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
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
          <?php if ($isPackageContext): ?>
          <div class="gp-package-notice">
            <span>This service is included in the <strong><?= $h($packageName) ?></strong> package.</span>
            <a href="<?= $h($packageDetailUrl) ?>" class="btn-cart is-guidance" style="margin-top:10px;">
              <i data-lucide="arrow-left" size="16"></i>
              View Package
            </a>
          </div>
          <?php elseif ($hasInitialBookOption): ?>
          <?php if ($isAddonContext): ?>
          <div class="gp-package-notice">
            <span>Add this service as an extra for <strong><?= $h($addonPackageName) ?></strong>.</span>
          </div>
          <?php endif; ?>
          <form method="POST" action="<?= URLROOT ?>/cart/add" id="serviceCartForm" style="display:contents;">
            <input type="hidden" name="service_id" value="<?= (int)($service['id'] ?? 0) ?>">
            <input type="hidden" name="date" id="cartDate" value="<?= $h($isVenue ? '' : ($isSlotBooking ? ($firstSlot['date'] ?? $selectedDate) : ($firstAvailable['date'] ?? $selectedDate))) ?>">
            <input type="hidden" name="slot_id" id="cartSlotId" value="<?= $h($isSlotBooking ? ($firstSlot['slot_id'] ?? '') : '') ?>">
            <input type="hidden" name="venue_room_id" id="cartVenueRoomId" value="<?= $h($isVenue ? ($firstVenueRoom['id'] ?? '') : '') ?>">
            <input type="hidden" name="start_time" id="cartStartTime" value="<?= $h($isSlotBooking ? ($firstSlot['start_time'] ?? '') : ($isVenue ? ($firstVenueRoom['start_time'] ?? '') : '')) ?>">
            <input type="hidden" name="end_time" id="cartEndTime" value="<?= $h($isSlotBooking ? ($firstSlot['end_time'] ?? '') : ($isVenue ? ($firstVenueRoom['end_time'] ?? '') : '')) ?>">
            <input type="hidden" name="price" id="cartPrice" value="<?= $h($activeServicePrice) ?>">
            <input type="hidden" name="source" value="custom">
            <?php if ($isAddonContext): ?>
              <input type="hidden" name="addon_package_id" value="<?= (int)$addonContext['package_id'] ?>">
            <?php endif; ?>
            <button class="btn-cart" id="addCartLink" type="submit">
              <i data-lucide="shopping-cart" size="16"></i>
              <?= $isAddonContext ? 'Add to package' : 'Add to cart' ?>
            </button>
          </form>
          <?php else: ?>
          <a class="btn-cart is-guidance" id="addCartLink" href="#detail-date">
            <?= $h($initialBookingLabel) ?>
          </a>
          <?php endif; ?>
        </div>
      </aside>
    </div>
  </section>

  <!-- SECTION: REVIEWS -->
  <section class="booking-section reviews-section" id="reviews" data-aos="fade-up" data-aos-duration="800" style="margin-top:40px;">
    <h2 class="section-title">Reviews &amp; Rating</h2>
    <p class="section-sub">What customers are saying about this service</p>

    <div class="reviews-grid">
      <div class="rating-summary-card">
        <div class="rating-big">
          <span class="rating-stars" aria-label="<?= $h(number_format((float)$rating, 1)) ?> star rating">
            <?php for ($star = 1; $star <= 5; $star++): ?>
              <span>★</span>
            <?php endfor; ?>
          </span>
          <span><?= number_format((float)$rating, 1) ?></span>
        </div>
        <div class="rating-bars">
          <?php foreach ($ratingBuckets as $stars => $count): ?>
            <div class="bar-row">
              <span><?= (int)$stars ?></span>
              <span class="bar-track"><span class="bar-fill" style="width: <?= (int)round(($count / $maxBucket) * 100) ?>%;"></span></span>
              <span><?= (int)$count ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div>
        <div class="review-sort-tabs" style="display:flex;gap:6px;margin-bottom:14px;">
          <button class="review-sort-btn active" data-sort="recent" onclick="sortReviews('recent',this)" style="padding:5px 14px;border-radius:999px;border:1px solid var(--rule-strong);background:var(--wine-dark);color:#fff;font-size:11px;font-weight:600;cursor:pointer;">Most Recent</button>
          <button class="review-sort-btn" data-sort="highest" onclick="sortReviews('highest',this)" style="padding:5px 14px;border-radius:999px;border:1px solid var(--rule-strong);background:none;font-size:11px;font-weight:600;cursor:pointer;color:var(--text2);">Highest Rated</button>
          <button class="review-sort-btn" data-sort="lowest" onclick="sortReviews('lowest',this)" style="padding:5px 14px;border-radius:999px;border:1px solid var(--rule-strong);background:none;font-size:11px;font-weight:600;cursor:pointer;color:var(--text2);">Lowest Rated</button>
        </div>

        <div class="review-list" id="reviewList">
          <?php foreach ($reviews as $idx => $review): ?>
            <?php $rName = $review['customer_name'] ?? 'Customer'; $rInitial = mb_strtoupper(mb_substr($rName, 0, 1)); ?>
            <article class="review-item" data-aos="fade-up" data-aos-delay="<?= min($idx * 80, 200) ?>">
              <div class="review-avatar" style="background:var(--wine-dark);color:#fff;font-weight:700;display:grid;place-items:center;"><?= $h($rInitial) ?></div>
              <div class="review-text">
                <strong><?= $h($rName) ?></strong>
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

        <?php if (count($reviews) >= 4): ?>
        <div style="margin-top:14px;text-align:center;">
          <button id="loadMoreReviews" data-service-id="<?= (int)($service['id'] ?? 0) ?>" data-offset="4" data-sort="recent" onclick="loadMoreReviews(this)" style="padding:7px 20px;border-radius:999px;border:1px solid var(--rule-strong);background:none;font-size:12px;font-weight:600;cursor:pointer;color:var(--wine-dark);">Load more reviews</button>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </section>
  <script>
  function sortReviews(sort, btn) {
    const serviceId = document.getElementById('loadMoreReviews')?.dataset?.serviceId
      || <?= (int)($service['id'] ?? 0) ?>;
    document.querySelectorAll('.review-sort-btn').forEach(b => {
      b.style.background = 'none'; b.style.color = 'var(--text2)';
      b.classList.remove('active');
    });
    btn.style.background = 'var(--wine-dark)'; btn.style.color = '#fff';
    btn.classList.add('active');
    fetch('<?= URLROOT ?>/review/service/' + serviceId + '?sort=' + sort + '&offset=0&limit=4')
      .then(r => r.json()).then(d => {
        document.getElementById('reviewList').innerHTML = d.html || '<article class="review-item"><div class="review-avatar"><i data-lucide="message-square" size="14"></i></div><div class="review-text"><strong>No reviews yet</strong></div></article>';
        const lm = document.getElementById('loadMoreReviews');
        if (lm) { lm.dataset.offset = '4'; lm.dataset.sort = sort; lm.style.display = d.has_more ? '' : 'none'; }
      });
  }
  function loadMoreReviews(btn) {
    const serviceId = btn.dataset.serviceId;
    const offset = parseInt(btn.dataset.offset) || 4;
    const sort = btn.dataset.sort || 'recent';
    fetch('<?= URLROOT ?>/review/service/' + serviceId + '?sort=' + sort + '&offset=' + offset + '&limit=4')
      .then(r => r.json()).then(d => {
        document.getElementById('reviewList').insertAdjacentHTML('beforeend', d.html || '');
        btn.dataset.offset = offset + (d.count || 0);
        if (!d.has_more) btn.style.display = 'none';
      });
  }
  </script>

  <!-- SECTION: RELATED SERVICES -->
  <?php if (!empty($related)): ?>
  <section class="related-section" data-aos="fade-up" data-aos-duration="800">
    <span class="related-kicker">you may also like</span>
    <h2 class="section-title">Explore Our Related Service</h2>
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
    <?php if ($isPackageContext): ?>
    <div>
      <div class="mobile-book-price"><?= $money($packageServicePrice) ?></div>
      <div class="mobile-book-label">Package service price</div>
    </div>
    <a class="mobile-book-btn is-guidance" href="<?= $h($packageDetailUrl) ?>">
      <i data-lucide="arrow-left" size="16"></i>
      View Package
    </a>
    <?php else: ?>
    <div>
      <div class="mobile-book-price"><?= $isVenue && $firstVenueRoom ? $money($firstVenueRoom['price'] ?? 0) : $moneyRange($service) ?></div>
      <div class="mobile-book-label"><?= $isAddonContext ? 'Package add-on' : $pricingUnitLabel($service) ?></div>
    </div>
    <a class="mobile-book-btn <?= $hasInitialBookOption ? '' : 'is-guidance' ?>" id="mobileBookBtn" href="<?= URLROOT ?>/cart">
      <i data-lucide="shopping-cart" size="16"></i>
      <?= $isAddonContext ? 'Add to package' : 'Add to cart' ?>
    </a>
    <?php endif; ?>
  </div>
</div>

<!-- LIGHTBOX -->
<div class="lightbox" id="lightbox">
  <button class="lightbox-close" id="lightboxClose" type="button" aria-label="Close"><i data-lucide="x" size="22"></i></button>
  <button class="lightbox-prev" id="lightboxPrev" type="button" aria-label="Previous"><i data-lucide="chevron-left" size="22"></i></button>
  <button class="lightbox-next" id="lightboxNext" type="button" aria-label="Next"><i data-lucide="chevron-right" size="22"></i></button>
  <div id="lightboxContent"></div>
</div>

<div class="gp-calendar-popover" id="gpCalendarPopover" hidden></div>

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
  const summaryCapacityText = <?= json_encode($summaryCapacityMetricValue) ?>;

  const gpCalendar = document.getElementById('gpCalendarPopover');
  let gpCalendarInput = null;
  let gpCalendarMonth = null;

  function formatDateValue(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return year + '-' + month + '-' + day;
  }

  function parseDateValue(value) {
    if (!value) return null;
    const parts = value.split('-').map(Number);
    if (parts.length !== 3 || parts.some(Number.isNaN)) return null;
    return new Date(parts[0], parts[1] - 1, parts[2]);
  }

  function updateCalendarDisplay(input) {
    const display = input.closest('.venue-date-input-wrap')?.querySelector('.venue-date-display');
    if (!display) return;
    const todayValue = formatDateValue(new Date());
    if (input.value === todayValue) display.textContent = 'Today';
    else {
      const parsed = parseDateValue(input.value);
      display.textContent = parsed ? parsed.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : 'Today';
    }
  }

  function positionCalendar(anchor) {
    if (!gpCalendar || !anchor) return;
    const rect = anchor.getBoundingClientRect();
    const width = Math.min(250, window.innerWidth - 32);
    const left = Math.max(16, Math.min(rect.left, window.innerWidth - width - 16));
    gpCalendar.style.width = width + 'px';
    gpCalendar.style.left = left + 'px';
    gpCalendar.style.top = (rect.bottom + 10) + 'px';
  }

  function renderCalendar() {
    if (!gpCalendar || !gpCalendarInput || !gpCalendarMonth) return;
    const monthStart = new Date(gpCalendarMonth.getFullYear(), gpCalendarMonth.getMonth(), 1);
    const selectedValue = gpCalendarInput.value;
    const todayValue = formatDateValue(new Date());
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
      const value = formatDateValue(new Date(monthStart.getFullYear(), monthStart.getMonth(), day));
      const disabled = (minValue && value < minValue) || (maxValue && value > maxValue);
      const classes = ['gp-calendar-day'];
      if (value === selectedValue) classes.push('is-selected');
      if (value === todayValue) classes.push('is-today');
      if (disabled) classes.push('is-disabled');
      html += '<button class="' + classes.join(' ') + '" type="button" data-date="' + value + '"' + (disabled ? ' disabled' : '') + '>' + day + '</button>';
    }
    html += '</div>';
    gpCalendar.innerHTML = html;
    lucide.createIcons({ nodes: [gpCalendar] });
  }

  function openCalendar(input) {
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
    if (event.target.closest('.gp-calendar-popover') || event.target.closest('.venue-date-input-wrap') || event.target.closest('.date-picker-control')) return;
    gpCalendar.hidden = true;
  });

  window.addEventListener('resize', () => {
    if (!gpCalendar?.hidden && gpCalendarInput) positionCalendar(gpCalendarInput.closest('.venue-date-input-wrap') || gpCalendarInput);
  });
  window.addEventListener('scroll', () => {
    if (gpCalendar && !gpCalendar.hidden) gpCalendar.hidden = true;
  }, { passive: true });

  document.querySelectorAll('.venue-date-form input[name="date"]').forEach(input => {
    input.addEventListener('change', () => {
      if (input.value && input.form) {
        if (typeof input.form.requestSubmit === 'function') input.form.requestSubmit();
        else input.form.submit();
      }
    });
  });

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

  function updateSelectedFulldayRow(row) {
    if (!row) return;
    document.querySelectorAll('[data-fullday-row]').forEach(r => r.classList.remove('is-selected'));
    row.classList.add('is-selected');
    if (selectedDate) selectedDate.textContent = row.dataset.dateLabel || 'Selected date';
    if (selectedTime) selectedTime.textContent = summaryCapacityText || 'Full day';
    if (estimatedTotal && row.dataset.priceValue) estimatedTotal.textContent = row.dataset.priceValue;
    if (mobileBookPrice && row.dataset.priceValue) mobileBookPrice.textContent = row.dataset.priceValue;

    if (cartDate) cartDate.value = row.dataset.date || '';
    if (cartSlotId) cartSlotId.value = '';
    if (cartStartTime) cartStartTime.value = '';
    if (cartEndTime) cartEndTime.value = '';
    if (cartPrice) cartPrice.value = row.dataset.priceValue || '';
  }

  document.querySelectorAll("input[name='service_slot']").forEach(input => {
    input.addEventListener('change', () => updateSelectedSlot(input));
  });
  updateSelectedSlot(document.querySelector("input[name='service_slot']:checked"));

  document.querySelectorAll('[data-fullday-row]').forEach(row => {
    row.addEventListener('click', () => updateSelectedFulldayRow(row));
  });
  const activeFulldayRow = document.querySelector('[data-fullday-row].is-selected');
  if (activeFulldayRow) updateSelectedFulldayRow(activeFulldayRow);

  if (mobileBookBtn && addCartLink) {
    mobileBookBtn.addEventListener('click', (event) => {
      event.preventDefault();
      addCartLink.click();
    });
  }

  if (serviceCartForm && addCartLink) {
    serviceCartForm.addEventListener('submit', () => {
      const currentSelection = document.querySelector("input[name='service_slot']:checked");
      if (currentSelection) updateSelectedSlot(currentSelection);
      const currentFullday = document.querySelector('[data-fullday-row].is-selected');
      if (currentFullday) updateSelectedFulldayRow(currentFullday);
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
  const galleryHeroItems = <?= json_encode(array_slice($heroItems, 0, 4)) ?>;

  let currentIndex = 0;
  const totalItems = galleryHeroItems.length;
  let heroAutoTimer = null;

  function swapHeroMedia(index) {
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
  }

  function renderHeroMedia(index) {
    if (!heroMain || totalItems === 0) return;
    heroMain.classList.add('is-fading');
    window.setTimeout(() => {
      swapHeroMedia(index);
      window.requestAnimationFrame(() => {
        heroMain.classList.remove('is-fading');
      });
    }, 180);
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

  function goToHeroMedia(index) {
    currentIndex = (index + totalItems) % totalItems;
    renderHeroMedia(currentIndex);
  }

  function restartHeroAuto() {
    if (heroAutoTimer) window.clearInterval(heroAutoTimer);
    if (prefersReduced || totalItems <= 1) return;
    heroAutoTimer = window.setInterval(() => {
      goToHeroMedia(currentIndex + 1);
    }, 4000);
  }

  if (totalItems > 1) {
    heroPrev?.addEventListener('click', (e) => { e.stopPropagation(); goToHeroMedia(currentIndex - 1); restartHeroAuto(); });
    heroNext?.addEventListener('click', (e) => { e.stopPropagation(); goToHeroMedia(currentIndex + 1); restartHeroAuto(); });
    heroDots?.addEventListener('click', (e) => {
      const dot = e.target.closest('.gallery-dot');
      if (dot) { goToHeroMedia(parseInt(dot.dataset.index)); restartHeroAuto(); }
    });
    heroThumbs?.addEventListener('click', (e) => {
      const thumb = e.target.closest('.gallery-thumb');
      if (thumb) { goToHeroMedia(parseInt(thumb.dataset.index)); restartHeroAuto(); }
    });
    heroMain?.addEventListener('mouseenter', () => {
      if (heroAutoTimer) window.clearInterval(heroAutoTimer);
    });
    heroMain?.addEventListener('mouseleave', restartHeroAuto);
    restartHeroAuto();
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

/* ── wishlist heart toggle ── */
(function(){
  var heart = document.getElementById('dtHeart');
  if (!heart) return;

  heart.addEventListener('click', function(){
    var isLoggedIn = <?= !empty($_SESSION['session_uid']) ? 'true' : 'false' ?>;
    if (!isLoggedIn) {
      window.location.href = '<?= URLROOT ?>/users/auth?redirect=' + encodeURIComponent('customerServices/detail/<?= (int)($service['id'] ?? 0) ?>');
      return;
    }

    var itemId  = parseInt(heart.dataset.itemId, 10);
    var isSaved = heart.dataset.saved === '1' || heart.classList.contains('is-saved');

    heart.classList.add('is-loading');

    fetch('<?= URLROOT ?>/main/toggleWishlist', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({item_type: 'service', item_id: itemId, collection_id: null})
    })
    .then(function(r){ return r.json(); })
    .then(function(d){
      heart.classList.remove('is-loading');
      if (d.ok) {
        if (d.action === 'added') {
          heart.classList.add('is-saved');
          heart.dataset.saved = '1';
          heart.innerHTML = '<span class="dt-heart-emoji">♥</span> Saved';
        } else {
          heart.classList.remove('is-saved');
          heart.dataset.saved = '0';
          heart.innerHTML = '<span class="dt-heart-emoji">♡</span> Save to wishlist';
        }
      }
    })
    .catch(function(){ heart.classList.remove('is-loading'); });
  });
})();
</script>
</body>
</html>
