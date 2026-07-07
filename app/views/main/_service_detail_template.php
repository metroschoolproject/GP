<?php
$service = $service ?? [];
$media = $service['media'] ?? [];
$availability = $service['availability'] ?? ['weekly' => [], 'overrides' => [], 'upcoming' => []];

// Track recently viewed service
if (!empty($service['id'])) {
    addRecentlyViewed((int)$service['id']);
}

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
$isRentalCategory = strpos($categoryKey, 'attire') !== false;
$attireItems = is_array($service['attire_items'] ?? null) ? $service['attire_items'] : [];
$bookingType = $service['booking_type'] ?? 'fullday';
$isSlotBooking = $bookingType === 'slot';
$reviews = $service['reviews'] ?? [];
$related = $service['related'] ?? [];
$recentServices = $recentlyViewedServices ?? [];
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
    : ($isRentalCategory ? !empty($attireItems) : ($isSlotBooking ? $firstSlot !== null : $firstAvailable !== null));
$selectedDateHasBookOption = $hasInitialBookOption;
if ($selectedDate !== '') {
    if ($isVenue) {
        $selectedDateHasBookOption = $firstVenueRoom !== null;
    } else {
        $selectedDateHasBookOption = false;
        foreach ($upcoming as $day) {
            $isSelectedDay = (!empty($day['is_selected_date']) || (($day['date'] ?? '') === $selectedDate));
            if ($isSelectedDay && !empty($day['slots'])) {
                $selectedDateHasBookOption = true;
                break;
            }
        }
    }
}
$initialBookingHref = $hasInitialBookOption ? URLROOT . '/users/auth' : '#detail-date';
$initialBookingLabel = $hasInitialBookOption ? 'Book now' : (($isVenue || !$isSlotBooking) ? 'Choose date' : 'Choose slot');
$venueCapacity = !empty($venueRooms) ? max(array_map(function ($room) {
    return (int)($room['capacity'] ?? 1);
}, $venueRooms)) : (int)($service['max_concurrent'] ?? 1);
$serviceMaxBooking = max(1, $isVenue ? (int)$venueCapacity : (int)($service['max_concurrent'] ?? 9999));
$modalQuantityLabel = 'Number Needed';
if ($isVenue || strpos($categoryKey, 'venue') !== false || strpos($categoryKey, 'cater') !== false) {
    $modalQuantityLabel = 'Guest Count';
} elseif (
    strpos($categoryKey, 'bridal') !== false
    || strpos($categoryKey, 'makeup') !== false
    || strpos($categoryKey, 'make up') !== false
    || strpos($categoryKey, 'hair') !== false
) {
    $modalQuantityLabel = 'People to Be Styled';
} elseif (
    strpos($categoryKey, 'media') !== false
    || strpos($categoryKey, 'photo') !== false
    || strpos($categoryKey, 'video') !== false
) {
    $modalQuantityLabel = 'People Included';
} elseif (
    strpos($categoryKey, 'invitation') !== false
    || strpos($categoryKey, 'invite') !== false
    || strpos($categoryKey, 'stationery') !== false
    || strpos($categoryKey, 'stationary') !== false
) {
    $modalQuantityLabel = 'Quantity Needed';
}
$metricCount = (int)($service['max_concurrent'] ?? 1);
$pluralizeMetric = function ($count, $singular, $plural = null) {
    return (int)$count . ' ' . ((int)$count === 1 ? $singular : ($plural ?? $singular . 's'));
};
$capacityMetricLabel = 'Bookings';
$capacityMetricValue = $pluralizeMetric($metricCount, 'booking') . ' per day';
$summaryCapacityMetricLabel = $capacityMetricLabel;
$summaryCapacityMetricValue = $capacityMetricValue;
$capacityCategoryKey = $categoryKey;
if ($isRentalCategory) {
    $itemCount = count($attireItems);
    $capacityMetricLabel = 'Items';
    $capacityMetricValue = $itemCount . ' item' . ($itemCount !== 1 ? 's' : '') . ' available';
    $summaryCapacityMetricLabel = 'Items';
    $summaryCapacityMetricValue = $itemCount . ' item' . ($itemCount !== 1 ? 's' : '');
} elseif ($isVenue) {
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
$cartCount = (int)($cartCount ?? 0);
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

$assetUrl = function ($path) use ($plain) {
    $path = trim($plain($path));
    if ($path === '') return '';
    if (preg_match('#^(https?:)?//#i', $path) || str_starts_with($path, 'data:')) return $path;
    if (str_starts_with($path, '/')) return $path;
    return rtrim(URLROOT, '/') . '/' . ltrim($path, '/');
};

$money = function ($value) {
    return number_format((float)$value, 0) . ' MMK';
};

$moneyRange = function ($service) use ($money) {
    return $money($service['display_price'] ?? $service['customize_price'] ?? $service['price_max'] ?? $service['price'] ?? 0);
};
$serviceLocation = function ($service) {
    $location = trim((string)($service['venue_location'] ?? $service['service_location'] ?? $service['location'] ?? ''));
    return $location !== '' ? $location : 'Location available after booking';
};
$decorationStyles = is_array($service['decoration_styles'] ?? null) ? $service['decoration_styles'] : [];
$isDecorationCategory = strtolower(trim((string)($service['category_slug'] ?? ''))) === 'decoration'
    || strtolower(trim((string)($service['category'] ?? ''))) === 'decoration';
$foodItems = is_array($service['food_items'] ?? null) ? $service['food_items'] : [];
$_foodCatSlug = strtolower(trim((string)($service['category_slug'] ?? '')));
$isCakeCategory = $_foodCatSlug === 'cake';
$isCateringCategory = $_foodCatSlug === 'food_drinks';
$isFoodCategory = $isCakeCategory || $isCateringCategory;
$rentalPricing = is_array($service['rental_pricing'] ?? null) ? $service['rental_pricing'] : [];
$rentalOptions = [];
if ($isRentalCategory) {
    // Prefer building from attire_items if available
    if (!empty($attireItems) && !empty($attireItems[0]['rental_options'])) {
        $firstItem = $attireItems[0];
        $opts = $firstItem['rental_options'] ?? [];
        // Pick cheapest borrow option for display
        $cheapest = null;
        foreach ($opts as $opt) {
            $p = (float)($opt['price'] ?? 0);
            if ($p > 0 && ($cheapest === null || $p < (float)($cheapest['price'] ?? 0))) {
                $cheapest = $opt;
            }
        }
        if ($cheapest) {
            $rentalOptions[] = [
                'label' => 'Borrow',
                'package' => $money((float)$cheapest['price']),
                'customize' => $money((float)$cheapest['price']),
                'meta' => ((int)($cheapest['days'] ?? 0)) > 0 ? (int)$cheapest['days'] . ' ' . ((int)$cheapest['days'] === 1 ? 'day' : 'days') . ' return' : 'Rental option',
                'icon' => 'refresh-cw',
            ];
        }
        // Buy price from attire item
        $buyPrice = (float)($firstItem['buy_package_price'] ?? 0);
        if ($buyPrice > 0) {
            $rentalOptions[] = [
                'label' => 'Buy',
                'package' => $money($buyPrice),
                'customize' => $money($buyPrice),
                'meta' => 'Purchase option',
                'icon' => 'shopping-bag',
            ];
        }
    } else {
        // Fallback to rental_pricing
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
$addonPackageDate = trim((string)($addonContext['selected_date'] ?? ''));
$addonPackageTime = trim((string)($addonContext['selected_time'] ?? ''));
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

// Platform fee calculation for transparent pricing
$platformFeePercent = get_platform_fee_percent();
$platformFeeAmount = round($activeServicePrice * $platformFeePercent / 100, 0);
$totalWithFee = $activeServicePrice + $platformFeeAmount;
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
    $cat = strtolower(trim((string)($service['category_slug'] ?? $service['category'] ?? '')));
    if ($cat === 'attire') {
        return 'Day-based rental';
    }
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
<link rel="icon" type="image/png" href="<?= URLROOT ?>/public/images/home/gp_logo.png">
<title><?= $h($service['name'] ?? 'Service') ?> | <?= APPNAME ?></title>
<?php include APPROOT . '/views/partials/ga-tracking.php'; ?>
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
  --panel-strong: #fcf8f5;
  --cream: #F8F2EC;
  --ink: #211D1A;
  --ink-soft: #3A2E29;
  --muted: #6F625A;
  --muted-light: #9A8C84;
  --wine: #6D4C5B;
  --wine-dark: #7E4F65;
  --wine-glow: rgba(154, 104, 127, 0.12);
  --gold: #D8B46A;
  --sage: #765A46;
  --green: #2a7a4b;
  --line: rgba(118, 90, 70, 0.16);
  --line-soft: rgba(118, 90, 70, 0.08);

  --glass-bg: rgba(255, 248, 239, 0.72);
  --glass-strong: rgba(255, 248, 239, 0.92);
  --glass-border: rgba(252,248,245, 0.35);
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
  color: #fcf8f5;
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
  border: 1px solid rgba(252,248,245, 0.4);
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
  color: #fcf8f5; letter-spacing: 0.01em;
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
  min-height: 40px;
  display: inline-flex; align-items: center; justify-content: center;
  padding: 0 19px;
  border-radius: 999px;
  background: rgba(252,248,245,0.12);
  backdrop-filter: blur(8px);
  border: 1px solid rgba(252,248,245,0.18);
  color: #fcf8f5;
  font-size: 13px; font-weight: 700;
  text-shadow: 0 1px 4px rgba(0,0,0,0.15);
  transition: background 0.2s ease, color 0.2s ease, border-color 0.2s ease;
}
.top-pill:hover { background: rgba(252,248,245,0.22); }

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
  display: grid; place-items: center;
  width: 36px; height: 36px;
  padding: 3px;
  border-radius: 7px;
  background: transparent;
  border: 0;
  cursor: pointer;
  transition: all 0.2s;
  color: #fcf8f5;
  font-family: 'Poppins', system-ui, -apple-system, sans-serif;
}
.tb-profile-btn:hover { background: rgba(252,248,245,0.22); }
.tb-profile-btn[aria-expanded="true"] { background: rgba(252,248,245,0.16); }

.top-bar.scrolled .tb-profile-btn {
  background: transparent;
  color: var(--muted);
  text-shadow: none;
}
.top-bar.scrolled .tb-profile-btn:hover {
  background: rgba(109,76,91,0.07);
  color: var(--wine);
}

.tb-profile-avatar {
  display: grid; place-items: center;
  width: 30px; height: 30px;
  border-radius: 50%;
  background: #D8B46A;
  color: #3F2F24;
  font-size: 11px;
  font-weight: 800;
  overflow: hidden;
  transition: box-shadow 0.18s ease;
}
.tb-profile-avatar img { width: 100%; height: 100%; object-fit: cover; }
.top-bar.scrolled .tb-profile-avatar { background: var(--wine); color: #fcf8f5; }
.tb-profile-btn[aria-expanded="true"] .tb-profile-avatar { box-shadow: 0 0 0 2px #fff8ef, 0 0 0 4px rgba(216,180,106,0.76); }

.tb-profile-menu {
  position: absolute; top: calc(100% + 10px); right: 0; z-index: 100;
  width: min(330px, calc(100vw - 24px));
  padding: 18px;
  border-radius: 22px;
  border: 1px solid rgba(107,68,89,0.12);
  background: #eef2f8;
  box-shadow: 0 18px 48px rgba(43,27,36,0.18);
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

.tb-profile-menu-top { position: relative; display: grid; place-items: center; padding: 8px 38px 10px; text-align: center; }
.tb-profile-email { max-width: 100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 15px; font-weight: 700; color: var(--text); }
.tb-profile-close { position: absolute; right: 0; top: 0; display: grid; place-items: center; width: 30px; height: 30px; border: 0; border-radius: 7px; background: transparent; color: #4f454b; cursor: pointer; transition: background 0.15s ease, color 0.15s ease; }
.tb-profile-close:hover { background: rgba(43,27,36,0.08); color: var(--text); }
.tb-profile-hero { display: grid; place-items: center; gap: 9px; padding: 10px 0 14px; text-align: center; }
.tb-profile-photo { display: grid; place-items: center; width: 74px; height: 74px; border-radius: 50%; background: #D8B46A; color: #3F2F24; font-size: 27px; font-weight: 800; overflow: hidden; }
.tb-profile-photo img { width: 100%; height: 100%; object-fit: cover; }
.tb-profile-greeting { font-size: 23px; font-weight: 500; color: var(--text); line-height: 1.15; }
.tb-profile-edit { display: inline-flex; align-items: center; justify-content: center; min-height: 36px; padding: 0 28px; border: 1px solid rgba(107,68,89,0.46); border-radius: 8px; color: var(--wine); background: transparent; font-size: 13px; font-weight: 600; text-decoration: none; transition: all 0.15s ease; }
.tb-profile-edit:hover { background: rgba(154,104,127,0.09); color: var(--wine-dark); border-color: var(--wine); }
.tb-profile-activity { margin-top: 14px; padding: 10px; border-radius: 18px; background: #fcf8f5; border: 1px solid rgba(107,68,89,0.08); }
.tb-profile-activity-title { display: flex; align-items: center; justify-content: space-between; padding: 8px 10px 10px; color: var(--text); font-size: 15px; font-weight: 700; }
.tb-profile-menu-item {
  display: flex; align-items: center; gap: 10px;
  padding: 10px;
  border-radius: 8px;
  font-size: 12px;
  font-weight: 600;
  color: #4f454b;
  transition: all 0.15s;
}
.tb-profile-menu-item svg { width: 15px; height: 15px; color: var(--wine); }
.tb-profile-menu-item:hover { background: rgba(109,76,91,0.06); color: var(--wine); }

.tb-profile-menu-item--danger { margin-top: 10px; color: var(--danger); }
.tb-profile-menu-item--danger svg { color: var(--danger); }
.tb-profile-menu-item--danger:hover { background: rgba(185,75,75,0.08); }

./* Outer container */
.package-context-strip{
    margin: 20px 0 18px;
}

/* Keep content aligned with page */
.package-context-inner{
    max-width: 1280px;
    margin: 0 auto;
    padding: 0 14px;   /* Was 32px */
}

/* Floating breadcrumb */
.package-breadcrumb{
    display: inline-flex;
    align-items: center;
    gap: 8px;

    padding: 8px 16px;

    background: #fbf3ea;
    border: 1px solid #efe2d3;
    border-radius: 6px;

    font-size: 13px;
    font-weight: 500;
}

/* Links */
.package-breadcrumb a{
    color: #6e6258;
    text-decoration: none;
    transition: .25s;
}

.package-breadcrumb a:hover{
    color: #8c6b42;
}

/* Separator */
.package-breadcrumb-sep{
    display: inline-flex;
    align-items: center;
    justify-content: center;

    margin: 0 2px;

    color: #c8b29b;
    font-size: 20px;
    font-weight: 700;
    line-height: 1;
}

/* Current page */
.package-current{
    color: #b78655;
    font-weight: 700;
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
  color: rgba(252,248,245,0.7);
  margin-bottom: 16px;
  padding: 6px 14px;
  border: 1px solid rgba(252,248,245,0.15);
  border-radius: 999px;
  backdrop-filter: blur(4px);
}

.hero-title {
  font-family: var(--font-serif);
  font-size: clamp(42px, 6vw, 88px);
  font-weight: 600;
  color: #fcf8f5;
  line-height: 1.0;
  letter-spacing: -0.02em;
  text-shadow: 0 2px 40px rgba(0,0,0,0.25);
}

.hero-sub {
  margin-top: 20px;
  display: flex; align-items: center; justify-content: center; gap: 24px;
  flex-wrap: wrap;
  color: rgba(252,248,245,0.75);
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
  color: rgba(252,248,245,0.5);
  animation: bounceDown 2s ease-in-out infinite;
  cursor: pointer;
  transition: color 0.2s;
}
.hero-scroll-indicator:hover { color: rgba(252,248,245,0.85); }

/* ─── PAGE SHELL ────────────────────────────────────── */
.page-shell {
  position: relative;
  z-index: 3;
  max-width: 1320px;
  margin: 0 auto;
  padding: 12px 28px 72px;
}

/* Product-style selected service detail */
.product-detail {
  display: grid;
  grid-template-columns: minmax(0, 540px) minmax(0, 1fr);
  gap: clamp(34px, 4.5vw, 64px);
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
  height: clamp(340px, 42vw, 500px);
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
  display: flex;
  gap: 10px;
  padding: 14px 0 10px;
  overflow-x: auto;
  overflow-y: hidden;
  scrollbar-color: rgba(109,76,91,.55) rgba(234,216,199,.55);
  scrollbar-width: thin;
}

.product-media .gallery-thumbs::-webkit-scrollbar {
  display: block;
  height: 7px;
}

.product-media .gallery-thumbs::-webkit-scrollbar-track {
  background: rgba(234,216,199,.55);
  border-radius: 999px;
}

.product-media .gallery-thumbs::-webkit-scrollbar-thumb {
  background: rgba(109,76,91,.55);
  border-radius: 999px;
}

.product-media .gallery-thumb {
  flex: 0 0 92px;
  width: 92px;
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
  font-size: clamp(28px, 2.8vw, 40px);
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
  font-size: clamp(23px, 2.2vw, 32px);
  font-weight: 600;
}

.product-divider {
  border: 0;
  border-top: 1px solid rgba(33,29,26,.10);
  margin: 24px 0;
}

.product-about-title {
  color: var(--ink);
  font-size: 14px;
  font-weight: 800;
  letter-spacing: .12em;
  text-transform: uppercase;
}

.product-about-text {
  margin-top: 10px;
  color: var(--muted);
  font-size: 14px;
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
  grid-template-columns: 170px minmax(0, 1fr);
  gap: 16px;
  align-items: center;
  color: var(--muted);
  font-size: 14px;
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
  color: #fcf8f5;
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
  min-height: 48px;
  padding: 0 34px;
  border-radius: 6px;
  font-size: 12px;
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
  background: rgba(252,248,245,0.88);
  color: var(--wine);
  font-size: 28px;
  box-shadow: 0 8px 28px rgba(0,0,0,0.20);
  transition: transform 0.3s var(--ease-spring), background 0.2s;
}
.gallery-play-btn:hover { transform: scale(1.08); background: #fcf8f5; }

.gallery-nav-btn {
  position: absolute; top: 50%; transform: translateY(-50%); z-index: 5;
  width: 38px; height: 38px;
  display: grid; place-items: center;
  border: 0; border-radius: 50%;
  background: rgba(252,248,245,0.80);
  color: var(--ink);
  cursor: pointer;
  box-shadow: 0 2px 12px rgba(0,0,0,0.10);
  opacity: 0;
  transition: opacity 0.25s, background 0.15s, transform 0.15s;
}
.gallery-nav-btn:hover { background: #fcf8f5; transform: translateY(-50%) scale(1.08); }
.gallery-frame:hover .gallery-nav-btn { opacity: 1; }
.gallery-nav-btn.prev { left: 14px; }
.gallery-nav-btn.next { right: 14px; }

.gallery-dots {
  position: absolute; bottom: 14px; left: 50%; transform: translateX(-50%);
  display: flex; gap: 7px; z-index: 5;
}

.gallery-dot {
  width: 6px; height: 6px; border-radius: 50%;
  background: rgba(252,248,245,0.45);
  border: 0; cursor: pointer; padding: 0;
  transition: background 0.15s, width 0.2s var(--ease-spring);
}
.gallery-dot.active { background: #fcf8f5; width: 22px; border-radius: 4px; }

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
  background: rgba(0,0,0,0.15); color: #fcf8f5; font-size: 13px;
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
  background: rgba(252,248,245,0.56);
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
  color: #fcf8f5; font-size: 30px;
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
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 14px;
  flex-wrap: wrap;
  margin-top: 18px;
  padding: 14px 16px;
  border: 1px solid rgba(154,104,127,0.14);
  border-radius: 14px;
  background: rgba(255,250,247,0.78);
  box-shadow: 0 14px 34px rgba(63,36,26,0.08);
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

.date-picker-btn {
  min-height: 32px;
  display: none;
  align-items: center;
  justify-content: center;
  gap: 7px;
  border: 0;
  border-radius: 999px;
  background: var(--wine);
  color: #fcf8f5;
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

.date-picker-card .venue-date-input-wrap {
  min-width: 172px;
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
  gap: 6px;
  border: 1px solid rgba(63, 36, 26, .18);
  border-radius: 6px;
  background: #FFF8EF;
  color: #3F241A;
  padding: 0 8px;
  font-size: 11px;
  font-weight: 800;
  cursor: pointer;
  overflow: hidden;
  box-shadow: 0 4px 14px rgba(63, 36, 26, .06);
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
  color: #7A4E3D;
  width: 12px !important;
  height: 12px !important;
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
.gp-calendar-nav svg {
  width: 14px;
  height: 14px;
  stroke: currentColor;
  stroke-width: 2.2;
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

.booking-section .section-title {
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

.booking-section .venue-date-change {
  margin-top: 8px;
}

.booking-section .venue-date-change .venue-date-input-wrap {
  min-width: 172px;
}

.booking-section .booking-grid {
  gap: 20px;
  margin-top: 0;
}

.booking-section .booking-grid {
  align-items: start;
}

.availability-list,
.sticky-summary {
  border: 1px solid rgba(154,104,127,0.14);
  border-radius: 14px;
  background: rgba(255,250,247,0.78);
  box-shadow: 0 18px 44px rgba(63,36,26,0.08);
}

.booking-section.is-venue-booking .availability-list {
  gap: 14px;
}

.booking-section.is-venue-booking .availability-row {
  min-height: 72px;
  padding: 14px 16px;
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
  padding: 14px;
}

.availability-row {
  position: relative;
  min-height: 72px;
  display: grid;
  grid-template-columns: 24px minmax(0, 1fr);
  gap: 14px;
  align-items: start;
  overflow: hidden;
  border: 1px solid rgba(154,104,127,0.18);
  border-radius: 10px;
  background: #fff8ef;
  padding: 14px 16px;
  transition: transform 0.3s var(--ease-out-expo), box-shadow 0.3s ease, border-color 0.2s ease;
  cursor: pointer;
}
.availability-row:hover,
.availability-row.is-selected {
  border-color: rgba(154,104,127,0.34);
  box-shadow: 0 12px 28px rgba(74,52,47,0.10);
  transform: translateY(-2px);
}
.availability-row.is-available {
  background: #fff8ef;
  border-color: rgba(45,190,114,0.56);
}
.availability-row.is-selected,
.availability-row.is-selected.is-available {
  border-color: #6D4C5B;
  background: #faebdd;
  box-shadow: 0 12px 28px rgba(74,52,47,0.10), inset 0 0 0 1px rgba(154,104,127,0.42);
}

.availability-row.is-available:hover,
.availability-row.is-selected.is-available {
  border-color: rgba(45,190,114,0.78);
  box-shadow: none;
}

.availability-row.is-requested-date {
  border-color: rgba(216,180,106,0.58);
  background: linear-gradient(135deg, rgba(216,180,106,0.12), var(--panel-strong));
}

.availability-row.is-unavailable {
  cursor: default;
  opacity: 0.82;
  background: linear-gradient(135deg, rgba(185,74,72,0.10), #fff8ef 52%);
  border-color: rgba(185,74,72,0.26);
}

.availability-row.is-today-closed {
  cursor: default;
  opacity: 0.65;
  background: #f9f6f2;
  border-color: rgba(109,76,91,0.15);
  filter: blur(0.3px);
}

.availability-row.is-today-closed .availability-name {
  color: #9b8c91;
}

.availability-status.is-today-closed {
  background: rgba(109,76,91,0.09);
  color: #8b7180;
}

.today-closed-message {
  display: flex;
  align-items: flex-start;
  gap: 6px;
  margin-top: 6px;
  padding: 8px 10px;
  border-radius: 8px;
  background: rgba(109,76,91,0.06);
  border: 1px dashed rgba(109,76,91,0.15);
  font-size: 11px;
  color: #8b7180;
  line-height: 1.45;
}

.availability-row.is-unavailable:hover {
  transform: none;
}

.availability-row.is-today-closed:hover {
  transform: none;
}

.availability-row.is-package-selected {
  border-color: rgba(154,104,127,0.38);
  background:
    linear-gradient(135deg, rgba(154,104,127,0.10), rgba(216,180,106,0.11)),
    rgba(255,248,239,0.92);
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
    linear-gradient(135deg, rgba(216,180,106,0.14), rgba(154,104,127,0.08)),
    rgba(255,250,247,0.8);
}

.hall-photo img {
  width: 100%;
  height: 100%;
  display: block;
  object-fit: cover;
}
.availability-row.is-unavailable .hall-photo:not(.is-empty) img {
  filter: blur(2px) saturate(.82);
  transform: scale(1.03);
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
    linear-gradient(135deg, rgba(216,180,106,0.14), rgba(154,104,127,0.08)),
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
  background: #fcf8f5;
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
  border-radius: 8px;
  background: rgba(216,180,106,0.20);
  color: var(--sage);
  padding: 6px 10px;
  font-size: 12px; font-weight: 800;
  white-space: nowrap;
  flex-shrink: 0;
}
.availability-status.is-available {
  background: rgba(42,122,75,0.16);
  color: var(--green);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
}
.availability-row.is-available .availability-status {
  background: rgba(42,122,75,0.16);
  color: var(--green);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
}
.availability-status.is-closed,
.availability-row.is-unavailable .availability-status {
  background: #b94a48;
  color: #fff8ef;
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
  display: flex !important; flex-wrap: wrap; gap: 8px;
  margin-top: 12px;
}

.slot-chip {
  min-height: 34px;
  display: inline-flex; align-items: center;
  border: 1px solid rgba(185,74,72,0.18);
  border-radius: 8px;
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
  background: var(--wine); border-color: #6D4C5B;
  box-shadow: 0 0 0 2px rgba(154,104,127,0.18);
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
  background: rgba(255,250,247,0.88);
  border: 1px solid rgba(154,104,127,0.14);
  border-radius: 14px;
  padding: clamp(18px, 2.5vw, 24px);
  box-shadow: 0 18px 44px rgba(63,36,26,0.08);
}
.sticky-summary.is-unavailable {
  filter: blur(1.2px);
  opacity: .62;
  pointer-events: none;
  user-select: none;
}

.summary-fields {
  border-radius: 10px;
  background: #fff8ef;
  border: 1px solid rgba(154,104,127,0.12);
  padding: 16px;
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
  font-size: 17px; font-weight: 700;
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
  font-size: 11px;
  font-weight: 800;
}
.package-price-line strong {
  color: var(--ink);
  font-size: 12px;
}
.package-price-line.is-package strong {
  color: var(--wine-dark);
  font-family: var(--font-serif);
  font-size: 15px;
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

.gp-booking-modal-backdrop {
  position: fixed;
  inset: 0;
  z-index: 220;
  display: none;
  align-items: center;
  justify-content: center;
  padding: 22px;
  background: rgba(26,17,24,0.42);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
}
.gp-booking-modal-backdrop.is-open {
  display: flex;
}
.gp-booking-modal {
  width: min(520px, 100%);
  max-height: calc(100vh - 44px);
  overflow-y: auto;
  border: 1px solid rgba(178,143,110,0.28);
  border-radius: 18px;
  background: #fffaf7;
  box-shadow: 0 28px 80px rgba(26,17,24,0.24);
}
.gp-booking-modal-head {
  padding: 22px 24px 14px;
  border-bottom: 1px solid rgba(178,143,110,0.18);
}
.gp-booking-modal-title {
  margin: 0;
  color: var(--ink);
  font-family: var(--font-serif);
  font-size: 26px;
  font-weight: 700;
  line-height: 1.05;
}
.gp-booking-modal-copy {
  margin-top: 8px;
  color: var(--muted);
  font-size: 13px;
  line-height: 1.55;
}
.gp-booking-modal-body {
  display: grid;
  gap: 13px;
  padding: 18px 24px 20px;
}
.gp-booking-modal-field {
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.gp-booking-modal-field[hidden] {
  display: none !important;
}
.gp-booking-modal-field label {
  color: var(--wine-dark);
  font-size: 10px;
  font-weight: 800;
  letter-spacing: .08em;
  text-transform: uppercase;
}
.gp-booking-modal-field.is-required label::after {
  content: ' *';
  color: #b94b4b;
  font-weight: 900;
}
.gp-booking-modal-field input,
.gp-booking-modal-field textarea {
  width: 100%;
  border: 1px solid rgba(118,90,70,0.18);
  border-radius: 10px;
  background: #fcf8f5;
  color: var(--ink);
  font: inherit;
  font-size: 13px;
  padding: 11px 12px;
  outline: none;
  transition: border-color .2s, box-shadow .2s, background .2s;
}
.gp-booking-modal-field textarea {
  min-height: 82px;
  resize: vertical;
}
.gp-booking-modal-field input:focus,
.gp-booking-modal-field textarea:focus {
  border-color: rgba(107,68,89,0.44);
  background: #fff;
  box-shadow: 0 0 0 3px rgba(107,68,89,0.08);
}
.gp-booking-modal-field.is-missing input,
.gp-booking-modal-field.is-missing textarea {
  border-color: #b94b4b;
  background: #fff7f6;
  box-shadow: 0 0 0 3px rgba(185,75,75,0.10);
}
.gp-booking-modal-limit {
  display: none;
  color: #b94b4b;
  font-size: 11px;
  font-weight: 700;
  line-height: 1.35;
}
.gp-booking-modal-limit.is-visible {
  display: block;
}
.gp-booking-modal-error {
  display: none;
  margin: -2px 0 2px;
  color: #b94b4b;
  font-size: 12px;
  font-weight: 700;
  line-height: 1.4;
}
.gp-booking-modal-error.is-visible {
  display: block;
}
.gp-booking-modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  padding: 0 24px 24px;
}
.gp-booking-modal-btn {
  min-height: 42px;
  border-radius: 8px;
  border: 1px solid rgba(107,68,89,0.34);
  padding: 0 16px;
  background: transparent;
  color: var(--wine);
  font: inherit;
  font-size: 12px;
  font-weight: 800;
  cursor: pointer;
}
.gp-booking-modal-btn.primary {
  border-color: var(--wine);
  background: var(--wine);
  color: #fffaf7;
}
.gp-booking-modal-btn:hover {
  transform: translateY(-1px);
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
  background: #fcf8f5;
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

.related-carousel {
  position: relative;
  margin-top: 28px;
}

.related-grid {
  display: grid;
  grid-auto-flow: column;
  grid-auto-columns: calc((100% - 36px) / 3);
  gap: 20px 18px;
  overflow-x: auto;
  overflow-y: visible;
  scroll-behavior: smooth;
  scroll-snap-type: x proximity;
  padding: 8px 0 14px;
  scrollbar-color: rgba(154,104,127,.55) transparent;
  scrollbar-width: thin;
}

.related-grid::-webkit-scrollbar {
  height: 7px;
}

.related-grid::-webkit-scrollbar-track {
  background: transparent;
  border-radius: 999px;
}

.related-grid::-webkit-scrollbar-thumb {
  background: rgba(154,104,127,.55);
  border-radius: 999px;
}

.related-item {
  position: relative;
  scroll-snap-align: start;
  height: 360px;
  min-height: 360px;
  background: #fff8ef;
  border: 1.5px solid #D8B46A;
  border-radius: 16px;
  padding: 10px;
  overflow: hidden;
  box-shadow: 0 14px 34px rgba(63,36,26,.12);
  transition: transform 0.22s var(--ease-out-expo), box-shadow 0.22s ease, border-color 0.22s ease;
  cursor: pointer;
}
.related-item:hover {
  transform: translateY(-6px);
  border-color: rgba(216,180,106,.72);
  box-shadow: 0 20px 42px rgba(63,36,26,.16);
}

.related-img {
  display: block;
  height: 178px;
  overflow: hidden;
  border: 1px solid rgba(216,180,106,.46);
  border-radius: 13px;
  background: var(--cream);
}
.related-img img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 12px;
  transition: transform 0.45s var(--ease-out-expo);
}
.related-item:hover .related-img img { transform: scale(1.035); }

.related-body {
  display: flex;
  flex-direction: column;
  height: calc(100% - 178px);
  padding: 10px 2px 2px;
}
.related-cat {
  display: inline-block;
  align-self: flex-start;
  order: 4;
  margin-top: 7px;
  border-radius: 7px;
  background: #f0dfe7;
  color: #7E4F65;
  padding: 4px 8px;
  font-size: 10px; font-weight: 800;
  letter-spacing: 0; text-transform: uppercase;
  border: 1px solid rgba(154,104,127,.14);
}
.related-name {
  order: 1;
  font-family: var(--font-sans);
  font-size: 13px; font-weight: 800;
  color: var(--ink);
  margin: 0 0 3px;
  line-height: 1.35;
  display: -webkit-box;
  overflow: hidden;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 2;
}

.related-supplier {
  order: 2;
  color: #6f625a;
  font-size: 12px;
  font-weight: 700;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.related-location {
  order: 3;
  margin-top: 5px;
  color: #7f6758;
  font-size: 11px;
  font-weight: 600;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.related-stats {
  order: 5;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  margin-top: auto;
  padding-top: 10px;
}

.related-price {
  color: var(--ink);
  font-size: 13px;
  font-weight: 900;
  white-space: nowrap;
}

.related-duration {
  display: block;
  color: #8f7666;
  font-size: 10px;
  font-weight: 700;
}

.related-btn {
  display: inline-flex; align-items: center; justify-content: space-between; gap: 10px;
  min-height: 34px; padding: 4px 5px 4px 14px;
  border: 1px solid rgba(154,104,127,.22);
  border-radius: 16px;
  background: #6D4C5B;
  font-size: 11px; font-weight: 800; color: #fff8ef;
  transition: background 0.15s, color 0.15s, border-color 0.15s;
}
.related-btn:hover { background: #7E4F65; color: #fff8ef; border-color: #7E4F65; transform: translateY(-1px); }

.related-btn-icon {
  display: inline-grid;
  place-items: center;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: #fff8ef;
  color: #6D4C5B;
  flex: 0 0 auto;
}

.related-btn-icon svg {
  width: 13px;
  height: 13px;
  stroke: currentColor;
}

.related-next {
  position: absolute;
  right: -10px;
  top: 50%;
  z-index: 2;
  width: 38px;
  height: 38px;
  display: inline-grid;
  place-items: center;
  border: 1px solid rgba(216,180,106,.62);
  border-radius: 50%;
  background: #6D4C5B;
  color: #fff8ef;
  box-shadow: 0 12px 28px rgba(63,36,26,.18);
  transform: translateY(-50%);
  cursor: pointer;
  transition: transform .18s ease, box-shadow .18s ease, opacity .18s ease;
}

.related-next:hover {
  transform: translateY(-50%) translateX(2px);
  box-shadow: 0 16px 34px rgba(63,36,26,.22);
}

.related-next:focus-visible {
  outline: 2px solid rgba(154,104,127,.55);
  outline-offset: 3px;
}

/* ─── RECENTLY VIEWED ───────────────────────────────── */
.recent-detail-section {
  margin-top: var(--pad-section);
  padding: clamp(24px, 4vw, 34px);
  border: 1px solid var(--line);
  border-radius: var(--radius-xl);
  background:
    linear-gradient(135deg, rgba(255,248,239,.88), rgba(245,232,217,.72)),
    radial-gradient(circle at 8% 8%, rgba(216,180,106,.18), transparent 34%);
  box-shadow: var(--shadow-sm);
}

.recent-detail-head {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 18px;
  margin-bottom: 18px;
}

.recent-detail-kicker {
  display: block;
  color: var(--wine);
  font-size: 11px;
  font-weight: 800;
  letter-spacing: .14em;
  text-transform: lowercase;
}

.recent-detail-title {
  margin-top: 4px;
  font-family: var(--font-serif);
  font-size: clamp(24px, 3vw, 36px);
  font-weight: 700;
  line-height: 1;
  color: var(--ink);
}

.recent-detail-copy {
  max-width: 390px;
  color: var(--muted);
  font-size: 13px;
  line-height: 1.55;
}

.recent-detail-rail {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 14px;
}

.recent-detail-card {
  display: grid;
  grid-template-columns: 118px minmax(0, 1fr);
  min-height: 126px;
  overflow: hidden;
  border: 1px solid rgba(216,180,106,.46);
  border-radius: var(--radius-lg);
  background: var(--panel);
  box-shadow: 0 12px 28px rgba(63,36,26,.08);
  transition: transform .22s var(--ease-out-expo), box-shadow .22s ease, border-color .22s ease;
}

.recent-detail-card:hover {
  transform: translateY(-3px);
  border-color: rgba(154,104,127,.42);
  box-shadow: 0 18px 36px rgba(63,36,26,.12);
}

.recent-detail-card:focus-visible {
  outline: 2px solid rgba(154,104,127,.55);
  outline-offset: 4px;
}

.recent-detail-img {
  min-height: 126px;
  overflow: hidden;
  background: var(--cream);
}

.recent-detail-img img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform .4s var(--ease-out-expo);
}

.recent-detail-card:hover .recent-detail-img img {
  transform: scale(1.04);
}

.recent-detail-placeholder {
  display: grid;
  width: 100%;
  height: 100%;
  place-items: center;
  color: var(--wine);
  font-family: var(--font-serif);
  font-size: 22px;
  font-weight: 800;
  background: linear-gradient(135deg, var(--cream), var(--panel));
}

.recent-detail-body {
  display: flex;
  min-width: 0;
  flex-direction: column;
  padding: 13px 14px;
}

.recent-detail-cat {
  align-self: flex-start;
  max-width: 100%;
  overflow: hidden;
  padding: 4px 8px;
  border-radius: 7px;
  background: #f0dfe7;
  color: var(--wine-dark);
  font-size: 10px;
  font-weight: 800;
  text-overflow: ellipsis;
  text-transform: uppercase;
  white-space: nowrap;
}

.recent-detail-name {
  margin-top: 8px;
  color: var(--ink);
  font-size: 13px;
  font-weight: 900;
  line-height: 1.3;
  display: -webkit-box;
  overflow: hidden;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 2;
}

.recent-detail-price {
  margin-top: auto;
  padding-top: 8px;
  color: var(--muted);
  font-size: 11px;
  font-weight: 700;
}

.recent-detail-price strong {
  color: var(--wine);
  font-size: 13px;
}

.gp-package-notice .gp-view-package-btn {
  margin-top: 12px;
  min-width: 190px;
  height: 52px;
  padding: 0 26px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  border-radius: 10px;
  font-size: 15px;
  font-weight: 800;
}

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
  transition: transform 0.3s var(--ease-spring), box-shadow 0.3s ease, background 0.2s ease, color 0.2s ease, border-color 0.2s ease;
}
.floating-cart:hover {
  transform: translateY(-3px);
  background: #6D4C5B;
  color: #fcf8f5;
  border-color: #6D4C5B;
  box-shadow: 0 18px 44px rgba(74,52,47,0.18);
}
.floating-cart-count {
  position: absolute;
  right: -6px;
  top: -7px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 20px;
  height: 20px;
  padding: 0 6px;
  border: 2px solid var(--panel-strong);
  border-radius: 999px;
  background: #6D4C5B;
  color: #fff8ef;
  font-family: Arial, sans-serif;
  font-size: 10px;
  font-weight: 800;
  line-height: 1;
}

.floating-cart-count:empty,
.floating-cart-count[hidden] {
  display: none;
}
/* Hide availability section only for included package service detail */
body:has(.gp-package-notice) .availability-list {
  display: none;
}

body:has(.gp-package-notice) .booking-grid {
  grid-template-columns: 1fr;
}

body:has(.gp-package-notice) .sticky-summary {
  max-width: 420px;
  margin-left: auto;
}

/* Make included service summary cleaner */
.gp-package-notice {
  margin-top: 18px;
  padding: 16px;
  border-radius: 14px;
  background: rgba(255,248,239,.9);
  border: 1px solid rgba(118,90,70,.14);
}

.gp-package-notice span {
  display: block;
  color: #3A2E29;
  font-size: 15px;
  line-height: 1.6;
  font-weight: 600;
}

.gp-package-notice .btn-cart {
  margin-top: 14px !important;
  min-width: 220px;
  height: 52px;
  justify-content: center;
}

/* Hide the included-service summary card too */
body:has(.gp-package-notice) .sticky-summary {
  display: none;
}

/* Remove the empty right column */
body:has(.gp-package-notice) .booking-grid {
  display: block;
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
  flex: 0 0 auto;
   min-height: 44px; 
   padding: 0 22px;
  border: 0; 
  border-radius: 999px;
  background: var(--wine); color: #fcf8f5;
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
  background: rgba(252,248,245,0.10);
  color: #fcf8f5; cursor: pointer; font-size: 22px;
  transition: background 0.15s;
}
.lightbox-close:hover { background: rgba(252,248,245,0.20); }

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
  background: rgba(252,248,245,0.10);
  color: #fcf8f5; cursor: pointer; font-size: 20px;
  transition: background 0.15s;
}
.lightbox-prev:hover, .lightbox-next:hover { background: rgba(252,248,245,0.20); }
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
  .product-media .gallery-main { height: clamp(340px, 58vw, 500px); }
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
  .related-grid { grid-auto-columns: calc((100% - 36px) / 3); gap: 18px; }
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
  .top-pill { min-height: 34px; padding: 0 14px; font-size: 12px; }
  .page-shell { padding: 8px 16px 40px; margin-top: 18px; }
  .product-detail { gap: 24px; margin-top: 0; }
  .product-media .gallery-main { height: 280px; }
  .product-media .gallery-thumbs { gap: 8px; padding-bottom: 9px; }
  .product-media .gallery-thumb { flex-basis: 74px; width: 74px; height: 52px; }
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
  .date-picker-control { display: flex; width: 100%; }
  .date-picker-control .venue-date-input-wrap { width: 100%; }
  .sticky-summary { display: none; }
  .mobile-book-bar { display: block; }
  .availability-row { grid-template-columns: 1fr; gap: 8px; padding: 12px; }
  .hall-row-body { grid-template-columns: 1fr; }
  .hall-photo { width: 100%; max-height: 190px; }
  .availability-status { width: max-content; }
  .reviews-grid { grid-template-columns: 1fr; }
  .review-item { grid-template-columns: 1fr; }
  .supplier-spotlight { flex-direction: column; padding: 24px; }
  .related-grid { grid-auto-columns: minmax(240px, 82vw); }
  .related-item { height: 330px; min-height: 330px; }
  .related-img { height: 160px; }
  .related-next { right: 4px; width: 34px; height: 34px; }
  .recent-detail-section { padding: 24px 16px; }
  .recent-detail-head { align-items: flex-start; flex-direction: column; }
  .recent-detail-rail {
    display: flex;
    gap: 12px;
    overflow-x: auto;
    padding: 2px 2px 12px;
    scroll-snap-type: x mandatory;
    -webkit-overflow-scrolling: touch;
  }
  .recent-detail-card {
    grid-template-columns: 1fr;
    min-width: min(78vw, 280px);
    scroll-snap-align: start;
  }
  .recent-detail-img { aspect-ratio: 4 / 3; min-height: 0; }
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
  padding:10px 18px;border-radius:999px;border:1px solid rgba(252,248,245,.28);
  background:rgba(0,0,0,.22);backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);
  color:rgba(252,248,245,.82);cursor:pointer;
  font-family:var(--font-body);font-size:13px;font-weight:600;
  transition:all .2s var(--ease);margin-top:12px;
}
.dt-heart:hover{background:rgba(0,0,0,.38);border-color:rgba(252,248,245,.48)}
.dt-heart.is-saved{color:#ff7b7b;border-color:rgba(229,91,91,.28);background:rgba(0,0,0,.32)}
.dt-heart.is-loading{pointer-events:none;opacity:.6}
.dt-heart-emoji{font-size:16px;line-height:1}
</style>
</head>
<body>

<?php $gpNavActive = 'services'; $gpShowFloatingCart = false; require APPROOT . '/views/layouts/customerHomeNav.php'; ?>

<?php if ($isPackageContext): ?>
<section class="package-context-strip" aria-label="Package context">
  <div class="package-context-inner">

    <nav class="package-breadcrumb" aria-label="Breadcrumb">
      <a href="<?= URLROOT ?>/customerServices/packages">Packages</a>
      <span class="package-breadcrumb-sep">&rsaquo;</span>
      <a href="<?= $h($packageDetailUrl) ?>"><?= $h($packageName) ?></a>
      <span class="package-breadcrumb-sep">&rsaquo;</span>
      <span><?= $h($service['name'] ?? 'Service detail') ?></span>
    </nav>

    <!-- package-banner removed -->

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
      <div class="product-fee-note" style="font-size:12px;color:#8e7680;margin-top:4px">+ <?= $money($platformFeeAmount) ?> platform service fee (<?= $platformFeePercent ?>%)</div>

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
        <?php if ($isPackageContext): ?>
  <a class="product-action-primary is-guidance" id="bookNowBtn" href="<?= $h($packageDetailUrl) ?>">
    <i data-lucide="arrow-left" size="16"></i>
    View Package
  </a>
<?php elseif ($isRentalCategory && !empty($attireItems)): ?>
  <a class="product-action-primary" id="bookNowBtn" href="#attire-gallery">
    Choose an item
  </a>
<?php elseif ($isDecorationCategory && !empty($decorationStyles)): ?>
  <a class="product-action-primary" id="bookNowBtn" href="#availability">
    Choose a style
  </a>
<?php elseif ($isFoodCategory && !empty($foodItems)): ?>
  <a class="product-action-primary" id="bookNowBtn" href="#availability">
    <?= $isCakeCategory ? 'Choose a cake' : 'Choose a menu item' ?>
  </a>
<?php else: ?>
  <a class="product-action-primary" id="bookNowBtn" href="<?= URLROOT ?>/cart">
    <?= $isAddonContext ? 'Add to package' : 'Book now' ?>
  </a>
<?php endif; ?>
        <a class="product-action-secondary" href="#reviews">View reviews</a>
      </div>
    </div>
  </section>

  <?php if ($isRentalCategory && !empty($attireItems)): ?>
  <!-- SECTION: ATTIRE GALLERY -->
  <section class="attire-gallery-section" id="attire-gallery" data-aos="fade-up" data-aos-duration="800">
    <h2 class="section-title">Choose Your Dress &amp; Accessories</h2>
    <p class="section-sub">Select an item to view rental options and availability</p>
    <?php if (!empty($_SESSION['cart_attire_error'])): ?>
      <div style="background:#fef2f2;color:#b91c1c;padding:10px 14px;border-radius:8px;font-size:13px;margin-bottom:16px">
        <?= $h($_SESSION['cart_attire_error']) ?>
      </div>
      <?php unset($_SESSION['cart_attire_error']); ?>
    <?php endif; ?>
    <div class="attire-grid">
      <?php $lockedAttireIds = is_array($service['locked_items']['attire_item_ids'] ?? null) ? $service['locked_items']['attire_item_ids'] : []; ?>
      <?php foreach ($attireItems as $idx => $ai): ?>
        <?php
          $isLockedAttire = in_array((int)$ai['id'], $lockedAttireIds, true);
          $aiPhoto = trim((string)($ai['photo_url'] ?? ''));
          $aiBorrowPrice = null;
          foreach (($ai['rental_options'] ?? []) as $opt) {
              $p = (float)($opt['price'] ?? 0);
              if ($p > 0 && ($aiBorrowPrice === null || $p < $aiBorrowPrice)) { $aiBorrowPrice = $p; }
          }
          $aiBuyPrice = (float)($ai['buy_package_price'] ?? 0);
        ?>
        <div class="attire-card <?= $isLockedAttire ? 'is-locked' : '' ?>" data-attire-card data-attire-idx="<?= $idx ?>" data-attire-id="<?= (int)$ai['id'] ?>" data-locked="<?= $isLockedAttire ? '1' : '0' ?>">
          <div class="attire-card-media">
            <?php if ($aiPhoto): ?>
              <img src="<?= $h($aiPhoto) ?>" alt="<?= $h($ai['name']) ?>">
            <?php else: ?>
              <div class="attire-card-placeholder"><i data-lucide="shirt" size="32"></i></div>
            <?php endif; ?>
            <?php if ($isLockedAttire): ?>
              <span class="attire-card-badge">Package only</span>
            <?php endif; ?>
          </div>
          <div class="attire-card-body">
            <h3 class="attire-card-name"><?= $h($ai['name']) ?></h3>
            <?php if (!empty($ai['description'])): ?>
              <p class="attire-card-desc"><?= $h(mb_strimwidth($ai['description'], 0, 100, '...')) ?></p>
            <?php endif; ?>
            <div class="attire-card-prices">
              <?php if ($aiBorrowPrice !== null && $aiBorrowPrice > 0): ?>
                <span class="attire-price-tag is-borrow">Borrow: <?= $money($aiBorrowPrice) ?></span>
              <?php endif; ?>
              <?php if ($aiBuyPrice > 0): ?>
                <span class="attire-price-tag is-buy">Buy: <?= $money($aiBuyPrice) ?></span>
              <?php endif; ?>
            </div>
            <?php if (!$isLockedAttire): ?>
              <div class="attire-card-actions" aria-label="Choose <?= $h($ai['name']) ?>">
                <?php if ($aiBorrowPrice !== null && $aiBorrowPrice > 0): ?>
                  <button class="attire-card-action is-borrow" type="button" data-attire-action="borrow">Borrow</button>
                <?php endif; ?>
                <?php if ($aiBuyPrice > 0): ?>
                  <button class="attire-card-action is-buy" type="button" data-attire-action="buy">Buy</button>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          </div>
          <div class="attire-card-check" aria-hidden="true"><i data-lucide="check-circle" size="20"></i></div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <!-- SECTION: AVAILABILITY / BOOKING -->
  <section class="booking-section <?= $isVenue ? 'is-venue-booking' : '' ?>" id="<?= $isVenue ? 'available-halls' : 'availability' ?>" data-aos="fade-up" data-aos-duration="800">
    <div class="booking-grid <?= ($isVenue && $selectedDate === '') || (!$isVenue && $selectedDate === $todayDate && !$selectedDateHasBookOption) ? 'is-date-pending' : '' ?>">
      <div class="availability-list">
        <?php if (($isVenue || $isCateringCategory) && !$isRentalCategory): ?>
          <div class="guest-count-bar" id="guestCountBar">
            <label for="guestCountInput" class="guest-count-label">
              <i data-lucide="users" size="16"></i>
              Guest Count
            </label>
            <input type="number" id="guestCountInput" min="1" max="9999" value="" placeholder="Enter guest count" class="guest-count-input">
            <input type="hidden" name="guest_count" id="cartGuestCount" form="serviceCartForm" value="">
          </div>
        <?php endif; ?>
        <?php if ($isRentalCategory && !empty($attireItems)): ?>
          <!-- Attire rental controls (shown when item is selected) -->
          <div id="attireBookingPanel">
            <div id="attireNotSelected" class="attire-prompt-state">
              <div class="attire-prompt-icon"><i data-lucide="mouse-pointer-click" size="28"></i></div>
              <h3>Select a dress or accessory above</h3>
              <p>Choose an item from the gallery to see rental options and availability.</p>
            </div>
            <div id="attireSelectedPanel" style="display:none">
              <div class="attire-selected-header">
                <img id="attireSelectedPhoto" src="" alt="" class="attire-selected-thumb">
                <div>
                  <h3 id="attireSelectedName" class="attire-selected-name"></h3>
                  <p id="attireSelectedDesc" class="attire-selected-desc"></p>
                </div>
              </div>
              <div class="rental-type-section" id="attireRentalTypeSection">
                <div class="rental-section-label">Rental type</div>
                <div class="rental-type-toggle" id="rentalTypeToggleMain">
                  <button type="button" class="rental-type-btn-main" data-rental-type="borrow" id="rentalBorrowBtn">
                    <i data-lucide="refresh-cw" size="16"></i>
                    <span>Borrow</span>
                  </button>
                  <button type="button" class="rental-type-btn-main" data-rental-type="buy" id="rentalBuyBtn">
                    <i data-lucide="shopping-bag" size="16"></i>
                    <span>Buy</span>
                  </button>
                </div>
              </div>
              <div id="borrowSection" style="display:none">
                <div class="attire-borrow-grid">
                  <div>
                    <div class="rental-section-label">Rental duration</div>
                    <div id="durationOptionsMain" class="duration-options-grid"></div>
                  </div>
                  <div id="borrowDateSection" style="display:none">
                    <div class="rental-section-label">Pick-up date</div>
                    <span class="venue-date-input-wrap attire-date-input-wrap">
                      <i class="venue-date-icon" data-lucide="calendar-days" size="8"></i>
                      <span class="venue-date-display">Choose date</span>
                      <i class="venue-date-chevron" data-lucide="chevron-down" size="8"></i>
                      <input type="date" id="borrowDateMain" class="gp-calendar-input rental-date-input" data-placeholder="Choose date" aria-label="Pick-up date">
                    </span>
                    <div id="borrowDateErrorMain" class="rental-date-error" style="display:none"></div>
                    <div id="rentalDateSummaryMain" class="rental-date-summary" style="display:none"></div>
                  </div>
                </div>
              </div>
              <div id="buySection" style="display:none">
                <div class="buy-price-card">
                  <span>Purchase price</span>
                  <strong id="buyPriceMain"></strong>
                </div>
              </div>
            </div>
          </div>
        <?php elseif (!$isVenue): ?>
          <div class="venue-halls-heading">
            <h2 class="section-title"><?= $isSlotBooking ? 'Available Dates &amp; Times' : 'Available Dates' ?></h2>
            <form class="venue-date-form venue-date-change" method="GET" action="<?= $h($datePickerAction) ?>#availability">
              <?php foreach ($packageQueryFields as $fieldName => $fieldValue): ?>
                <?php if ($fieldValue > 0): ?>
                  <input type="hidden" name="<?= $h($fieldName) ?>" value="<?= (int)$fieldValue ?>">
                <?php endif; ?>
              <?php endforeach; ?>
              <span class="venue-date-input-wrap">
                <i class="venue-date-icon" data-lucide="calendar-days" size="8"></i>
                <span class="venue-date-display"><?= $h($selectedDate !== '' ? date('M j, Y', strtotime($selectedDate)) : 'Today') ?></span>
                <i class="venue-date-chevron" data-lucide="chevron-down" size="8"></i>
                <input class="gp-calendar-input" type="date" id="detail-date-inline" name="date" value="<?= $h($selectedDate !== '' ? $selectedDate : $venueDateInputValue) ?>" min="<?= $h($datePickerMin) ?>" max="<?= $h($datePickerMax) ?>" aria-label="Wedding date">
              </span>
            </form>
          </div>
          <?php if ($selectedDateLabel === '' && (int)($service['min_lead_days'] ?? 0) > 0): ?>
            <div class="date-picker-copy">
              <span>Earliest booking date: <?= $h(date('M j, Y', strtotime($datePickerMin))) ?>.</span>
            </div>
          <?php endif; ?>
        <?php endif; ?>
        <?php if (!$isRentalCategory || empty($attireItems)): ?>
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
            <?php $lockedRoomIds = is_array($service['locked_items']['venue_room_ids'] ?? null) ? $service['locked_items']['venue_room_ids'] : []; ?>
            <?php foreach ($venueRooms as $index => $room): ?>
              <?php
                $isPackageHallRow = $isPackageContext && (int)($packageContext['venue_room_id'] ?? 0) > 0 && (int)($room['id'] ?? 0) === (int)($packageContext['venue_room_id'] ?? 0);
                $isLockedRoom = !$isPackageContext && in_array((int)$room['id'], $lockedRoomIds, true);
                $roomDisplayPrice = $isPackageContext ? $packageServicePrice : (float)($room['price'] ?? 0);
                $roomPhotoUrl = trim((string)($room['photo_url'] ?? ''));
                $roomAvailable = $selectedDate !== '' && (!array_key_exists('is_available_on_date', $room) || !empty($room['is_available_on_date'])) && !$isLockedRoom;
                $roomEarliestDate = trim((string)($room['earliest_booking_date'] ?? ''));
                $roomStatus = $isLockedRoom
                  ? 'Package only'
                  : ($roomAvailable
                    ? $money($roomDisplayPrice)
                    : ($selectedDate === ''
                      ? 'Choose date'
                      : (!empty($room['lead_time_blocked']) && $roomEarliestDate !== ''
                        ? 'Too soon'
                        : (!empty($room['service_closed_on_date']) || !empty($room['room_closed_on_date']) ? 'Closed' : 'Booked'))));
                $checked = !$hasSelectedRoom && $roomAvailable;
                if ($checked) { $hasSelectedRoom = true; }
              ?>
              <div class="availability-row <?= $checked ? 'is-selected' : '' ?> <?= $isPackageHallRow ? 'is-package-selected' : '' ?> <?= $roomAvailable ? 'is-available' : 'is-unavailable' ?>" data-slot-row data-aos="fade-up" data-aos-delay="<?= min($index * 80, 300) ?>">
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
                      <span class="availability-status <?= !$roomAvailable ? 'is-closed' : '' ?>"><?= $h($roomStatus) ?></span>
                    </div>
                    <?php if ($isPackageHallRow): ?>
                      <span class="package-hall-badge"><i data-lucide="badge-check" size="13"></i>Selected for your package</span>
                    <?php endif; ?>
                    <?php if ($isLockedRoom): ?>
                      <span class="package-hall-badge"><i data-lucide="lock" size="13"></i>Package only</span>
                    <?php endif; ?>
                    <?php if (!empty($room['lead_time_blocked']) && $roomEarliestDate !== ''): ?>
                      <span class="package-hall-badge"><i data-lucide="calendar-clock" size="13"></i>Earliest: <?= $h(date('M j, Y', strtotime($roomEarliestDate))) ?></span>
                    <?php endif; ?>
                    <?php if ($roomAvailable || $isLockedRoom): ?>
                      <div class="slot-options">
                        <label class="slot-chip <?= ($isPackageHallRow || $isLockedRoom) ? 'is-locked' : '' ?>" <?= $isPackageHallRow ? 'title="Included in your package"' : ($isLockedRoom ? 'title="Available in package only"' : '') ?>>
                          <input type="radio" name="service_slot"
                            value="room|<?= (int)$room['id'] ?>"
                            data-room-id="<?= (int)$room['id'] ?>"
                            data-venue-room-id="<?= (int)$room['id'] ?>"
                            data-date="<?= $h($selectedDate) ?>"
                            data-date-label="<?= $h($selectedDateLabel ?: 'Choose a wedding date') ?>"
                            data-hall-label="<?= $h($room['name'] ?: 'Selected hall') ?>"
                            data-time-label="<?= (int)($room['capacity'] ?? 1) ?> guests"
                            data-max-booking="<?= (int)($room['capacity'] ?? 1) ?>"
                            data-price-label="<?= $money($roomDisplayPrice) ?>"
                            data-price-value="<?= $h($roomDisplayPrice) ?>"
                            data-slot-id=""
                            data-start-time="<?= $h($room['start_time'] ?? '') ?>"
                            data-end-time="<?= $h($room['end_time'] ?? '') ?>"
                            <?= $checked ? 'checked' : '' ?>
                            <?= ($isPackageHallRow || $isLockedRoom) ? 'disabled' : '' ?>>
                          <?= $isPackageHallRow ? 'Included in your package' : ($isLockedRoom ? 'Package only' : (int)($room['capacity'] ?? 1) . ' guests') ?>
                        </label>
                      </div>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        <?php elseif ($isDecorationCategory && !empty($decorationStyles)): ?>
          <div class="venue-halls-heading">
            <h2 class="section-title">Decoration Styles</h2>
          </div>
          <?php if ($selectedDate === ''): ?>
            <div class="empty-state venue-date-prompt">
              <span>Choose a wedding date above to see which styles are available.</span>
            </div>
          <?php elseif (!$selectedDateHasBookOption): ?>
            <div class="empty-state"><i data-lucide="calendar-x" size="22"></i>Not available on your selected date. Please choose a different date.</div>
          <?php else: ?>
            <?php $lockedStyleIds = is_array($service['locked_items']['decoration_style_ids'] ?? null) ? $service['locked_items']['decoration_style_ids'] : []; ?>
            <?php $hasSelectedStyle = false; ?>
            <?php foreach ($decorationStyles as $styleIdx => $ds): ?>
              <?php
                $styleId = (int)$ds['id'];
                $isLockedStyle = in_array($styleId, $lockedStyleIds, true);
                $stylePhoto = trim((string)($ds['photo_url'] ?? ''));
                $stylePrice = (float)($ds['package_price'] ?? $ds['price'] ?? 0);
                $styleCustomizePrice = (float)($ds['customize_price'] ?? $ds['price'] ?? 0);
                $checked = !$hasSelectedStyle && !$isLockedStyle;
                if ($checked) { $hasSelectedStyle = true; }
              ?>
              <div class="availability-row is-fullday is-available <?= $checked ? 'is-selected' : '' ?> <?= $isLockedStyle ? 'is-unavailable' : '' ?>" data-deco-row data-deco-id="<?= $styleId ?>" data-deco-name="<?= $h($ds['name']) ?>" data-deco-price="<?= $h($styleCustomizePrice) ?>" data-deco-photo="<?= $h($stylePhoto) ?>" data-aos="fade-up" data-aos-delay="<?= min($styleIdx * 80, 300) ?>">
                <span class="radio-dot"></span>
                <div style="display:flex;align-items:center;gap:12px;flex:1;min-width:0">
                  <?php if ($stylePhoto): ?>
                    <img src="<?= $h($stylePhoto) ?>" alt="" style="width:52px;height:52px;border-radius:var(--radius-lg);object-fit:cover;flex-shrink:0">
                  <?php else: ?>
                    <div style="width:52px;height:52px;border-radius:var(--radius-lg);background:var(--cream);display:flex;align-items:center;justify-content:center;flex-shrink:0"><i data-lucide="palette" size="20" style="color:var(--muted-light)"></i></div>
                  <?php endif; ?>
                  <div style="flex:1;min-width:0">
                    <div class="availability-head">
                      <span class="availability-name">
                        <?= $h($ds['name']) ?>
                        <?php if ($isLockedStyle): ?>
                          <span>Package only</span>
                        <?php endif; ?>
                      </span>
                      <span class="availability-status <?= $isLockedStyle ? 'is-closed' : '' ?>"><?= $isLockedStyle ? 'Package only' : $money($styleCustomizePrice) ?></span>
                    </div>
                    <?php if (!$isLockedStyle && $stylePrice > 0 && $styleCustomizePrice !== $stylePrice): ?>
                      <span class="availability-range"><i data-lucide="tag" size="14"></i>Package: <?= $money($stylePrice) ?></span>
                    <?php endif; ?>
                  </div>
                  <div style="flex-shrink:0">
                    <label class="slot-chip" style="margin:0">
                      <input type="radio" name="decoration_style"
                        value="<?= $styleId ?>"
                        data-deco-id="<?= $styleId ?>"
                        data-deco-name="<?= $h($ds['name']) ?>"
                        data-deco-price="<?= $h($styleCustomizePrice) ?>"
                        data-deco-photo="<?= $h($stylePhoto) ?>"
                        <?= $checked ? 'checked' : '' ?>
                        <?= $isLockedStyle ? 'disabled' : '' ?>>
                      <?= $isLockedStyle ? 'Locked' : 'Select' ?>
                    </label>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        <?php elseif ($isFoodCategory && !empty($foodItems)): ?>
          <div class="venue-halls-heading">
            <h2 class="section-title"><?= $isCakeCategory ? 'Cake Items' : 'Menu Items' ?></h2>
          </div>
          <?php if ($selectedDate === ''): ?>
            <div class="empty-state venue-date-prompt">
              <span>Choose a wedding date above to see which <?= $isCakeCategory ? 'cakes' : 'menu items' ?> are available.</span>
            </div>
          <?php elseif (!$selectedDateHasBookOption): ?>
            <div class="empty-state"><i data-lucide="calendar-x" size="22"></i>Not available on your selected date. Please choose a different date.</div>
          <?php else: ?>
            <?php $lockedFoodIds = is_array($service['locked_items']['food_item_ids'] ?? null) ? $service['locked_items']['food_item_ids'] : []; ?>
            <?php $hasSelectedFood = false; ?>
            <?php foreach ($foodItems as $foodIdx => $fi): ?>
              <?php
                $foodId = (int)$fi['id'];
                $isLockedFood = in_array($foodId, $lockedFoodIds, true);
                $foodPhoto = trim((string)($fi['photo_url'] ?? ''));
                $foodPrice = (float)($fi['package_price'] ?? $fi['price'] ?? 0);
                $foodCustomizePrice = (float)($fi['customize_price'] ?? $fi['price'] ?? 0);
                $foodDesc = trim((string)($fi['description'] ?? ''));
                $foodPricingModel = $fi['pricing_model'] ?? 'flat';
                $isPerPerson = $foodPricingModel === 'per_person';
                $checked = !$hasSelectedFood && !$isLockedFood;
                if ($checked) { $hasSelectedFood = true; }
              ?>
              <div class="availability-row is-fullday is-available <?= $checked ? 'is-selected' : '' ?> <?= $isLockedFood ? 'is-unavailable' : '' ?>" data-food-row data-food-id="<?= $foodId ?>" data-food-name="<?= $h($fi['name']) ?>" data-food-price="<?= $h($foodPrice) ?>" data-food-photo="<?= $h($foodPhoto) ?>" data-aos="fade-up" data-aos-delay="<?= min($foodIdx * 80, 300) ?>">
                <span class="radio-dot"></span>
                <div style="display:flex;align-items:center;gap:12px;flex:1;min-width:0">
                  <?php if ($foodPhoto): ?>
                    <img src="<?= $h($foodPhoto) ?>" alt="" style="width:52px;height:52px;border-radius:var(--radius-lg);object-fit:cover;flex-shrink:0">
                  <?php else: ?>
                    <div style="width:52px;height:52px;border-radius:var(--radius-lg);background:var(--cream);display:flex;align-items:center;justify-content:center;flex-shrink:0"><i data-lucide="utensils" size="20" style="color:var(--muted-light)"></i></div>
                  <?php endif; ?>
                  <div style="flex:1;min-width:0">
                    <div class="availability-head">
                      <span class="availability-name">
                        <?= $h($fi['name']) ?>
                        <?php if ($isLockedFood): ?>
                          <span>Package only</span>
                        <?php elseif ($foodDesc): ?>
                          <span><?= $h(mb_strimwidth($foodDesc, 0, 60, '...')) ?></span>
                        <?php endif; ?>
                      </span>
                      <span class="availability-status <?= $isLockedFood ? 'is-closed' : '' ?>" data-food-total-id="<?= $foodId ?>"><?= $isLockedFood ? 'Package only' : ($isPerPerson ? $money($foodPrice) . '/person' : $money($foodPrice)) ?></span>
                    </div>
                    <?php if (!$isLockedFood && $isPerPerson): ?>
                      <span class="availability-range food-total-display" data-food-total-id="<?= $foodId ?>" style="display:none"><i data-lucide="calculator" size="14"></i>Total: <strong class="food-total-value"></strong></span>
                    <?php elseif (!$isLockedFood && $foodCustomizePrice > 0 && $foodCustomizePrice !== $foodPrice): ?>
                      <span class="availability-range"><i data-lucide="chef-hat" size="14"></i>Customize: <?= $money($foodCustomizePrice) ?></span>
                    <?php endif; ?>
                  </div>
                  <div style="flex-shrink:0">
                    <label class="slot-chip" style="margin:0">
                      <input type="radio" name="food_item"
                        value="<?= $foodId ?>"
                        data-food-id="<?= $foodId ?>"
                        data-food-name="<?= $h($fi['name']) ?>"
                        data-food-price="<?= $h($foodPrice) ?>"
                        data-food-photo="<?= $h($foodPhoto) ?>"
                        data-pricing-model="<?= $h($foodPricingModel) ?>"
                        <?= $checked ? 'checked' : '' ?>
                        <?= $isLockedFood ? 'disabled' : '' ?>>
                      <?= $isLockedFood ? 'Locked' : 'Select' ?>
                    </label>
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
              $dayStatus = $day['status'] ?? (empty($slots) ? 'Booked' : 'Available');
              $isToday = !empty($day['is_today']) || $day['date'] === date('Y-m-d');
              $isClosedToday = $isToday && empty($slots) && $dayStatus !== 'Too soon';

              if ($isSlotBooking) {
                if ($isClosedToday) {
                  $slotSummary = 'Check tomorrow';
                } elseif (count($slots) > 1) {
                  $slotSummary = count($slots) . ' times available';
                } else {
                  $slotSummary = $firstDaySlot['label'] ?? ($day['reason'] ?? ($selectedDate !== '' && !empty($day['is_selected_date']) ? 'No available time on your selected date' : ($day['date'] ?? '')));
                }
              } else {
                $slotSummary = $firstDaySlot['label'] ?? ($day['reason'] ?? ($selectedDate !== '' && !empty($day['is_selected_date']) ? 'Not available on your selected date' : ($day['date'] ?? '')));
              }
              $rowSelected = !$hasSelectedSlot && !empty($slots);
              $isRequestedDate = !empty($day['is_selected_date']);
            ?>
            <?php if ($isSlotBooking): ?>
            <div class="availability-row <?= $rowSelected ? 'is-selected' : '' ?> <?= $isRequestedDate ? 'is-requested-date' : '' ?> <?= $isClosedToday ? 'is-today-closed' : (empty($slots) ? 'is-unavailable' : 'is-available') ?>" data-slot-row data-aos="fade-up" data-aos-delay="<?= min($dayIdx * 80, 300) ?>">
              <span class="radio-dot"></span>
              <div>
                <div class="availability-head">
                  <span class="availability-name">
                    <?= $h($dayLabel) ?>
                    <span><?= $h($slotSummary) ?></span>
                  </span>
                  <span class="availability-status <?= $isClosedToday ? 'is-today-closed' : (empty($slots) ? 'is-closed' : '') ?>"><?= $isClosedToday ? 'Closed today' : $h($dayStatus) ?></span>
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
                <?php elseif ($isClosedToday): ?>
                  <div class="today-closed-message">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="6" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    All time slots for today have ended. View tomorrow or later for available times.
                  </div>
                <?php endif; ?>
              </div>
            </div>
            <?php else: ?>
            <?php if (!empty($slots)): ?>
              <?php $checked = !$hasSelectedSlot; if ($checked) { $hasSelectedSlot = true; } ?>
              <div class="availability-row is-fullday is-available <?= $rowSelected ? 'is-selected' : '' ?> <?= $isRequestedDate ? 'is-requested-date' : '' ?>" data-fullday-row data-date="<?= $h($day['date'] ?? '') ?>" data-date-label="<?= $h($day['day_label'] ?? $day['date']) ?>" data-price-value="<?= $h($isPackageContext ? $packageServicePrice : $activeServicePrice) ?>" data-aos="fade-up" data-aos-delay="<?= min($dayIdx * 80, 300) ?>">
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
            <?php elseif ($isClosedToday): ?>
              <div class="availability-row is-today-closed" data-aos="fade-up" data-aos-delay="<?= min($dayIdx * 80, 300) ?>">
                <span class="radio-dot"></span>
                <div>
                  <div class="availability-head">
                    <span class="availability-name">
                      <?= $h($dayLabel) ?>
                      <span>Closed today</span>
                    </span>
                    <span class="availability-status is-today-closed">Closed today</span>
                  </div>
                  <div class="today-closed-message" style="margin-top:6px">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="6" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    Closed for today. Please select a future date.
                  </div>
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
                    <span class="availability-status is-closed"><?= $h($day['status'] ?? 'Booked') ?></span>
                  </div>
                </div>
              </div>
            <?php endif; ?>
            <?php endif; ?>
          <?php endforeach; ?>
        <?php endif; ?>
        <?php endif; // end !isRentalCategory guard ?>
      </div>

      <!-- Sticky booking summary (desktop) -->
      <aside class="sticky-summary <?= !$selectedDateHasBookOption ? 'is-unavailable' : '' ?>" id="desktopSummary">
        <div class="summary-fields">
          <div class="summary-line">
            <?= $isRentalCategory ? 'Pick-up date' : ($isVenue ? 'Wedding date' : 'Wedding date') ?>
            <span id="selectedDate"><?= $h($selectedDateLabel ?: ($isVenue ? 'Choose a wedding date' : ($isRentalCategory ? 'Select an item first' : ($firstAvailable['day_label'] ?? 'Choose an available date')))) ?></span>
          </div>
          <?php if ($isVenue): ?>
            <div class="summary-line">
              Selected hall
              <span id="selectedHall"><?= $h($firstVenueRoom['name'] ?? 'Select after date') ?></span>
            </div>
          <?php endif; ?>
          <?php if ($isDecorationCategory && !empty($decorationStyles)): ?>
            <div class="summary-line">
              Selected style
              <span id="selectedDecoStyle">Choose a date first</span>
            </div>
          <?php endif; ?>
          <?php if ($isFoodCategory && !empty($foodItems)): ?>
            <div class="summary-line">
              <?= $isCakeCategory ? 'Selected cake' : 'Selected menu item' ?>
              <span id="selectedFoodItem">Choose a date first</span>
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
          <span><?= $isPackageContext ? 'Package service price' : ($isRentalCategory ? 'Estimated total' : 'Estimated total') ?></span>
          <strong id="sidebarEstimatedTotal"><?= $isPackageContext ? $money($packageServicePrice) : ($isVenue && $firstVenueRoom ? $money($firstVenueRoom['price'] ?? 0) : $moneyRange($service)) ?></strong>
        </div>
        <div class="estimated-row" style="font-size:12px;color:#8e7680">
          <span>Platform service fee (<?= $platformFeePercent ?>%)</span>
          <strong style="font-weight:600"><?= $money($platformFeeAmount) ?></strong>
        </div>
        <?php if (!$isRentalCategory && !empty($rentalOptions)): ?>
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
        <?php if ($isRentalCategory && !empty($attireItems)): ?>
          <div id="sidebarAttireSummary" class="sidebar-attire-summary" style="display:none">
            <div class="sidebar-attire-item">
              <img id="sidebarAttirePhoto" src="" alt="" class="sidebar-attire-thumb">
              <div class="sidebar-attire-info">
                <strong id="sidebarAttireName"></strong>
                <small id="sidebarAttireRental"></small>
              </div>
            </div>
          </div>
        <?php endif; ?>
        <div class="summary-actions">
          <?php if ($isPackageContext): ?>
          <div class="gp-package-notice">
            <span>This service is included in the <strong><?= $h($packageName) ?></strong> package.</span>
            <a href="<?= $h($packageDetailUrl) ?>" class="btn-cart is-guidance gp-view-package-btn">
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
            <input type="hidden" name="source" value="<?= $isAddonContext ? 'package' : 'custom' ?>">
            <?php if ($isAddonContext): ?>
              <input type="hidden" name="addon_package_id" value="<?= (int)$addonContext['package_id'] ?>">
              <input type="hidden" name="addon_package_date" value="<?= $h($addonPackageDate) ?>">
              <input type="hidden" name="addon_package_time" value="<?= $h($addonPackageTime) ?>">
            <?php endif; ?>
            <?php if ($isRentalCategory && !empty($attireItems)): ?>
              <input type="hidden" name="attire_item_id" id="cartAttireItemId" value="">
              <input type="hidden" name="rental_type" id="cartRentalType" value="">
              <input type="hidden" name="borrow_date" id="cartBorrowDate" value="">
              <input type="hidden" name="rental_option_id" id="cartRentalOptionId" value="">
            <?php endif; ?>
            <?php if ($isDecorationCategory && !empty($decorationStyles)): ?>
              <input type="hidden" name="decoration_style_id" id="cartDecorationStyleId" value="">
            <?php endif; ?>
            <?php if ($isFoodCategory && !empty($foodItems)): ?>
              <input type="hidden" name="cake_design_id" id="cartCakeDesignId" value="">
            <?php endif; ?>
            <button class="btn-cart" id="addCartLink" type="submit">
              <?= $isAddonContext ? 'Add to package' : ($isRentalCategory ? 'Add to Cart' : 'Book now') ?>
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
          <button class="review-sort-btn active" data-sort="recent" onclick="sortReviews('recent',this)" style="padding:5px 14px;border-radius:999px;border:1px solid var(--rule-strong);background:var(--wine-dark);color:#fcf8f5;font-size:11px;font-weight:600;cursor:pointer;">Most Recent</button>
          <button class="review-sort-btn" data-sort="highest" onclick="sortReviews('highest',this)" style="padding:5px 14px;border-radius:999px;border:1px solid var(--rule-strong);background:none;font-size:11px;font-weight:600;cursor:pointer;color:var(--text2);">Highest Rated</button>
          <button class="review-sort-btn" data-sort="lowest" onclick="sortReviews('lowest',this)" style="padding:5px 14px;border-radius:999px;border:1px solid var(--rule-strong);background:none;font-size:11px;font-weight:600;cursor:pointer;color:var(--text2);">Lowest Rated</button>
        </div>

        <div class="review-list" id="reviewList">
          <?php foreach ($reviews as $idx => $review): ?>
            <?php $rName = $review['customer_name'] ?? 'Customer'; $rInitial = mb_strtoupper(mb_substr($rName, 0, 1)); ?>
            <article class="review-item" data-aos="fade-up" data-aos-delay="<?= min($idx * 80, 200) ?>">
              <div class="review-avatar" style="background:var(--wine-dark);color:#fcf8f5;font-weight:700;display:grid;place-items:center;"><?= $h($rInitial) ?></div>
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
    btn.style.background = 'var(--wine-dark)'; btn.style.color = '#fcf8f5';
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
  <?php if (!$isAddonContext && !empty($related)): ?>
  <section class="related-section" data-aos="fade-up" data-aos-duration="800">
    <span class="related-kicker">you may also like</span>
    <h2 class="section-title">Explore Our Related Service</h2>
    <div class="related-carousel">
      <div class="related-grid" id="relatedGrid">
        <?php foreach ($related as $idx => $item): ?>
          <?php
            $relatedUrl = URLROOT . '/customerServices/detail/' . (int)$item['id'] . $detailDateQuery;
            $relatedCategoryKey = strtolower(trim((string)($item['category_slug'] ?? $item['category'] ?? '')));
            $relatedAvailabilityAnchor = (strpos($relatedCategoryKey, 'venue') !== false || strpos($relatedCategoryKey, 'hall') !== false) ? 'available-halls' : 'availability';
            $relatedBookUrl = $relatedUrl . '#' . $relatedAvailabilityAnchor;
          ?>
          <article class="related-item" data-url="<?= $h($relatedUrl) ?>" role="link" tabindex="0" aria-label="View details for <?= $h($item['name'] ?? 'related service') ?>" data-aos="flip-up" data-aos-delay="<?= min($idx, 3) * 100 ?>">
            <div class="related-img">
              <img src="<?= $h($item['image'] ?: $fallbackImage) ?>" alt="<?= $h($item['name'] ?? 'Related service') ?>">
            </div>
            <div class="related-body">
              <div class="related-name"><?= $h($item['name'] ?? '') ?></div>
              <div class="related-supplier"><?= $h($item['supplier_name'] ?? 'Golden Promise supplier') ?></div>
              <div class="related-location"><?= $h($serviceLocation($item)) ?></div>
              <span class="related-cat"><?= $h($item['category'] ?? 'Service') ?></span>
              <div class="related-stats">
                <div>
                  <div class="related-price"><?= $moneyRange($item) ?></div>
                  <span class="related-duration"><?= $h($durationText($item)) ?></span>
                </div>
                <a class="related-btn" href="<?= $h($relatedBookUrl) ?>">
                  <span>Book Now</span>
                  <span class="related-btn-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17 17 7"/><path d="M9 7h8v8"/></svg>
                  </span>
                </a>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
      <?php if (count($related) > 3): ?>
        <button class="related-next" type="button" aria-label="Show more related services" data-related-next>
          <i data-lucide="arrow-right" size="18"></i>
        </button>
      <?php endif; ?>
    </div>
  </section>
  <?php endif; ?>

</main>

<?php if ($isLoggedIn): ?>
<!-- Floating cart -->
<a class="floating-cart" href="<?= URLROOT ?>/cart" aria-label="Open cart<?= $cartCount > 0 ? ' with ' . $cartCount . ' selected service' . ($cartCount === 1 ? '' : 's') : '' ?>">
  <i data-lucide="shopping-bag" size="20"></i>
  <span class="floating-cart-count" data-cart-count-badge<?= $cartCount > 0 ? '' : ' hidden' ?>><?= $cartCount > 0 ? ($cartCount > 99 ? '99+' : $cartCount) : '' ?></span>
</a>
<?php endif; ?>

<div class="cart-feedback" id="cartFeedback" role="status" aria-live="polite">
  <i data-lucide="check-circle" size="16"></i>
  <span><?= $isPackageContext ? 'Adding to package booking...' : 'Adding to cart...' ?></span>
</div>

<div class="gp-booking-modal-backdrop" id="bookingDetailsModal" role="dialog" aria-modal="true" aria-labelledby="bookingDetailsModalTitle" hidden
     data-service-id="<?= (int)($service['id'] ?? 0) ?>"
     data-service-name="<?= $h($service['name'] ?? $service['service_name'] ?? 'Service') ?>"
     data-category-key="<?= $h($categoryKey) ?>"
     data-field-mode="<?= $isVenue ? 'venue' : (strpos($categoryKey, 'cater') !== false ? 'catering' : ((strpos($categoryKey, 'photo') !== false || strpos($categoryKey, 'video') !== false || strpos($categoryKey, 'media') !== false) ? 'media' : ((strpos($categoryKey, 'beauty') !== false || strpos($categoryKey, 'bridal') !== false || strpos($categoryKey, 'makeup') !== false || strpos($categoryKey, 'make up') !== false) ? 'beauty' : ((strpos($categoryKey, 'invitation') !== false || strpos($categoryKey, 'stationery') !== false || strpos($categoryKey, 'stationary') !== false) ? 'stationery' : 'general')))) ?>">
  <div class="gp-booking-modal">
    <div class="gp-booking-modal-head">
      <h2 class="gp-booking-modal-title" id="bookingDetailsModalTitle">Details for this service</h2>
      <p class="gp-booking-modal-copy">You can add these details now, or skip and complete them on the confirmation page.</p>
    </div>
    <div class="gp-booking-modal-body">
      <div class="gp-booking-modal-field" data-modal-field="guests">
        <label for="booking-modal-guests" data-quantity-label><?= $h($modalQuantityLabel) ?></label>
        <input id="booking-modal-guests" type="number" min="0" max="<?= (int)$serviceMaxBooking ?>" data-max-booking="<?= (int)$serviceMaxBooking ?>" inputmode="numeric" placeholder="e.g. 120">
        <p class="gp-booking-modal-limit is-visible" data-booking-modal-limit>
          <?= $isVenue ? 'Suggested from selected hall max: ' . (int)$serviceMaxBooking . ' guests' : 'Suggested maximum booking: ' . (int)$serviceMaxBooking ?>
        </p>
      </div>
      <div class="gp-booking-modal-field" data-modal-field="location">
        <label for="booking-modal-location" data-location-label>Venue / hall / room / location</label>
        <input id="booking-modal-location" type="text" placeholder="e.g. Ballroom A">
      </div>
      <div class="gp-booking-modal-field" data-modal-field="contactName">
        <label for="booking-modal-contact-name">Contact name</label>
        <input id="booking-modal-contact-name" type="text" placeholder="Contact name">
      </div>
      <div class="gp-booking-modal-field" data-modal-field="contactPhone">
        <label for="booking-modal-contact-phone">Contact phone</label>
        <input id="booking-modal-contact-phone" type="tel" placeholder="+95 9 123 456 789">
      </div>
      <div class="gp-booking-modal-field" data-modal-field="notes">
        <label for="booking-modal-notes" data-notes-label>Notes</label>
        <textarea id="booking-modal-notes" placeholder="Any specific requirements for this service..."></textarea>
      </div>
      <p class="gp-booking-modal-error" id="bookingDetailsModalError">Please complete the highlighted fields, or choose Skip for now.</p>
    </div>
    <div class="gp-booking-modal-actions">
      <button class="gp-booking-modal-btn" type="button" data-booking-modal-skip>Skip for now</button>
      <button class="gp-booking-modal-btn primary" type="button" data-booking-modal-continue>Continue to Selected Services</button>
    </div>
  </div>
</div>

<!-- Mobile bottom booking bar -->
<div class="mobile-book-bar" id="mobileBookBar">
  <div class="mobile-book-row">
    <?php if ($isPackageContext): ?>
    <div>
      <div class="mobile-book-price"><?= $money($packageServicePrice) ?></div>
      <div class="mobile-book-label">Package service price · +<?= $money($platformFeeAmount) ?> fee</div>
    </div>
    <a class="mobile-book-btn is-guidance" href="<?= $h($packageDetailUrl) ?>">
      <i data-lucide="arrow-left" size="16"></i>
      View Package
    </a>
    <?php elseif ($isRentalCategory && !empty($attireItems)): ?>
    <div>
      <div class="mobile-book-price" id="mobileBookPrice"><?= $moneyRange($service) ?></div>
      <div class="mobile-book-label" id="mobileBookLabel">Select an item to continue · +<?= $money($platformFeeAmount) ?> fee</div>
    </div>
    <button class="mobile-book-btn is-guidance" id="mobileBookBtn" type="button" onclick="document.getElementById('attire-gallery')?.scrollIntoView({behavior:'smooth'})">
      Choose item
    </button>
    <?php else: ?>
    <div>
      <div class="mobile-book-price"><?= $isVenue && $firstVenueRoom ? $money($firstVenueRoom['price'] ?? 0) : $moneyRange($service) ?></div>
      <div class="mobile-book-label"><?= $isAddonContext ? 'Package add-on' : $pricingUnitLabel($service) ?> · +<?= $money($platformFeeAmount) ?> fee</div>
    </div>
    <a class="mobile-book-btn <?= $hasInitialBookOption ? '' : 'is-guidance' ?>" id="mobileBookBtn" href="<?= URLROOT ?>/cart">
      <?= $isAddonContext ? 'Add to package' : 'Book now' ?>
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
  const bookingDetailsModal = document.getElementById('bookingDetailsModal');
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
    if (!input.value) display.textContent = input.dataset.placeholder || 'Today';
    else if (input.value === todayValue) display.textContent = 'Today';
    else {
      const parsed = parseDateValue(input.value);
      display.textContent = parsed ? parsed.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : (input.dataset.placeholder || 'Today');
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
      '<button class="gp-calendar-nav" type="button" data-cal-prev aria-label="Previous month"><svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg></button>' +
      '<span>' + monthTitle + '</span>' +
      '<button class="gp-calendar-nav" type="button" data-cal-next aria-label="Next month"><svg viewBox="0 0 24 24" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg></button>' +
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
    if (!wrap.hasAttribute('role')) wrap.setAttribute('role', 'button');
    if (!wrap.hasAttribute('tabindex')) wrap.setAttribute('tabindex', '0');
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
    if (event.target.closest('.gp-calendar-popover') || event.target.closest('.venue-date-input-wrap') || event.target.closest('.date-picker-control')) return;
    gpCalendar.hidden = true;
  });

  window.addEventListener('resize', () => {
    if (!gpCalendar?.hidden && gpCalendarInput) positionCalendar(gpCalendarInput.closest('.venue-date-input-wrap') || gpCalendarInput);
  });
  window.addEventListener('scroll', () => {
    if (gpCalendar && !gpCalendar.hidden) gpCalendar.hidden = true;
  }, { passive: true });

  document.querySelectorAll('.venue-date-form input[name="date"], .date-picker-card input[name="date"]').forEach(input => {
    input.addEventListener('change', () => {
      if (input.value && input.form) {
        if (typeof input.form.requestSubmit === 'function') input.form.requestSubmit();
        else input.form.submit();
      }
    });
  });

  function updateSelectedSlot(input) {
    if (!input) return;
    const formatPriceLabel = (value) => {
      const numeric = String(value || '').replace(/[^0-9.]/g, '');
      if (!numeric) return '';
      return 'MMK ' + Number(numeric).toLocaleString();
    };
    document.querySelectorAll('[data-slot-row]').forEach(row => {
      row.classList.toggle('is-selected', row.contains(input));
    });
    if (selectedDate) selectedDate.textContent = input.dataset.dateLabel || 'Selected date';
    if (selectedTime) selectedTime.textContent = input.dataset.timeLabel || 'Selected time';
    if (selectedHall && input.dataset.hallLabel) selectedHall.textContent = input.dataset.hallLabel;
    const slotPriceLabel = input.dataset.priceLabel || formatPriceLabel(input.dataset.priceValue);
    if (estimatedTotal && slotPriceLabel) estimatedTotal.textContent = slotPriceLabel;
    if (mobileBookPrice && slotPriceLabel) mobileBookPrice.textContent = slotPriceLabel;

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

    // Sync guest count from venue hall capacity
    var maxBooking = parseInt(input.dataset.maxBooking || '0', 10);
    var guestInput = document.getElementById('guestCountInput');
    var guestHidden = document.getElementById('cartGuestCount');
    if (maxBooking > 0 && guestInput) {
      guestInput.value = maxBooking;
      if (guestHidden) guestHidden.value = maxBooking;
      window.dispatchEvent(new CustomEvent('gp:guestCountChanged', { detail: { guestCount: maxBooking } }));
    }
  }

  function updateSelectedFulldayRow(row) {
    if (!row) return;
    const formatPriceLabel = (value) => {
      const numeric = String(value || '').replace(/[^0-9.]/g, '');
      if (!numeric) return '';
      return 'MMK ' + Number(numeric).toLocaleString();
    };
    document.querySelectorAll('[data-fullday-row]').forEach(r => r.classList.remove('is-selected'));
    row.classList.add('is-selected');
    if (selectedDate) selectedDate.textContent = row.dataset.dateLabel || 'Selected date';
    if (selectedTime) selectedTime.textContent = summaryCapacityText || 'Full day';
    const rowPriceLabel = formatPriceLabel(row.dataset.priceValue);
    if (estimatedTotal && rowPriceLabel) estimatedTotal.textContent = rowPriceLabel;
    if (mobileBookPrice && rowPriceLabel) mobileBookPrice.textContent = rowPriceLabel;

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

  // ── Restore guest booking state after login redirect ──
  (function restoreGuestBookingState() {
    var raw = null;
    try { raw = sessionStorage.getItem('gpGuestBookingState'); } catch(e) {}
    if (!raw) return;
    try {
      var state = JSON.parse(raw);
      sessionStorage.removeItem('gpGuestBookingState');
    } catch(e) { return; }
    if (!state || Number(state.serviceId) !== serviceId) return;
    // Expire after 30 minutes
    if (Date.now() - (state.savedAt || 0) > 30 * 60 * 1000) return;

    // Restore venue room / slot selection
    if (state.venueRoomId) {
      var hallRadio = document.querySelector('input[name="service_slot"][data-venue-room-id="' + state.venueRoomId + '"]');
      if (hallRadio && !hallRadio.disabled) {
        hallRadio.checked = true;
        hallRadio.dispatchEvent(new Event('change', { bubbles: true }));
        var hallRow = hallRadio.closest('[data-slot-row]');
        if (hallRow) updateSelectedSlot(hallRadio);
      }
    } else if (state.slotId) {
      var slotInput = document.querySelector('input[name="service_slot"][data-slot-id="' + state.slotId + '"]');
      if (slotInput) {
        slotInput.checked = true;
        updateSelectedSlot(slotInput);
      }
    } else if (state.date) {
      var fulldayRow = document.querySelector('[data-fullday-row][data-date="' + state.date + '"]');
      if (fulldayRow) updateSelectedFulldayRow(fulldayRow);
    }

    // Restore hidden form fields that might not be set by row clicks
    if (state.price && cartPrice && !cartPrice.value) cartPrice.value = state.price;
    if (state.startTime && cartStartTime && !cartStartTime.value) cartStartTime.value = state.startTime;
    if (state.endTime && cartEndTime && !cartEndTime.value) cartEndTime.value = state.endTime;

    // Restore guest count
    if (state.guest_count) {
      var gc = Number(state.guest_count);
      var guestInput = document.getElementById('guestCountInput');
      var guestHidden = document.getElementById('cartGuestCount');
      if (guestInput && gc > 0) {
        guestInput.value = gc;
        if (guestHidden) guestHidden.value = gc;
        window.dispatchEvent(new CustomEvent('gp:guestCountChanged', { detail: { guestCount: gc } }));
      }
    }

    // Restore decoration style selection
    if (state.decorationStyleId) {
      var decoRadio = document.querySelector('input[name="decoration_style"][data-deco-id="' + state.decorationStyleId + '"]');
      if (decoRadio && !decoRadio.disabled) {
        decoRadio.checked = true;
        decoRadio.dispatchEvent(new Event('change', { bubbles: true }));
      }
    }

    // Restore food item selection
    var foodId = state.cake_design_id || state.foodItemId;
    if (foodId) {
      var foodRadio = document.querySelector('input[name="food_item"][data-food-id="' + foodId + '"]');
      if (foodRadio && !foodRadio.disabled) {
        foodRadio.checked = true;
        foodRadio.dispatchEvent(new Event('change', { bubbles: true }));
      }
    }

    // Scroll to availability section
    var availSection = document.getElementById('availability') || document.getElementById('available-halls');
    if (availSection) {
      setTimeout(function() { availSection.scrollIntoView({ behavior: 'smooth', block: 'start' }); }, 300);
    }
  })();

  if (mobileBookBtn && addCartLink) {
    var isLoggedInMobile = <?= $isLoggedIn ? 'true' : 'false' ?>;
    mobileBookBtn.addEventListener('click', (event) => {
      event.preventDefault();
      if (!isLoggedInMobile) {
        showAuthModal();
        return;
      }
      addCartLink.click();
    });
  }

  if (serviceCartForm && bookingDetailsModal) {
    const modalFields = {
      guests: document.getElementById('booking-modal-guests'),
      location: document.getElementById('booking-modal-location'),
      contactName: document.getElementById('booking-modal-contact-name'),
      contactPhone: document.getElementById('booking-modal-contact-phone'),
      notes: document.getElementById('booking-modal-notes')
    };
    const modalMode = bookingDetailsModal.dataset.fieldMode || 'general';
    const locationLabel = bookingDetailsModal.querySelector('[data-location-label]');
    const notesLabel = bookingDetailsModal.querySelector('[data-notes-label]');
    const modalError = document.getElementById('bookingDetailsModalError');
    const modalLimitMessage = bookingDetailsModal.querySelector('[data-booking-modal-limit]');
    const fieldWraps = Array.from(bookingDetailsModal.querySelectorAll('[data-modal-field]'));
    const modes = {
      venue: ['guests', 'location', 'contactName', 'contactPhone', 'notes'],
      catering: ['guests', 'location', 'contactName', 'contactPhone', 'notes'],
      beauty: ['guests', 'location', 'contactName', 'contactPhone', 'notes'],
      media: ['guests', 'location', 'contactName', 'contactPhone', 'notes'],
      stationery: ['guests', 'contactName', 'contactPhone', 'notes'],
      general: ['guests', 'location', 'contactName', 'contactPhone', 'notes']
    };
    const labels = {
      venue: { location: 'Venue / hall / room / location', notes: 'Notes' },
      catering: { location: 'Serving location', notes: 'Notes' },
      beauty: { location: 'Preparation location', notes: 'Notes' },
      media: { location: 'Shoot location', notes: 'Notes' },
      stationery: { notes: 'Delivery / pickup notes' },
      general: { location: 'Location', notes: 'Notes' }
    };
    const visibleFields = modes[modalMode] || modes.general;
    fieldWraps.forEach(wrap => {
      const fieldName = wrap.dataset.modalField || '';
      wrap.hidden = !visibleFields.includes(fieldName);
      wrap.classList.toggle('is-required', !wrap.hidden && fieldName !== 'notes');
    });
    if (locationLabel && labels[modalMode]?.location) locationLabel.textContent = labels[modalMode].location;
    if (notesLabel && labels[modalMode]?.notes) notesLabel.textContent = labels[modalMode].notes;
    const updateModalGuestMax = () => {
      const guestField = modalFields.guests;
      if (!guestField) return;
      const selectedOption = document.querySelector("input[name='service_slot']:checked");
      const selectedMax = parseInt(selectedOption?.dataset?.maxBooking || '', 10);
      const max = Number.isFinite(selectedMax) && selectedMax > 0
        ? selectedMax
        : (parseInt(guestField.dataset.maxBooking || guestField.getAttribute('max') || '9999', 10) || 9999);
      guestField.dataset.maxBooking = String(max);
      guestField.max = String(max);
      if (modalLimitMessage) {
        modalLimitMessage.textContent = modalMode === 'venue'
          ? 'Suggested from selected hall max: ' + max.toLocaleString('en-US') + ' guests'
          : 'Suggested maximum booking: ' + max.toLocaleString('en-US');
        modalLimitMessage.classList.add('is-visible');
      }
    };
    const clampModalGuestLimit = (showMessage = false) => {
      const guestField = modalFields.guests;
      if (!guestField) return;
      const max = parseInt(guestField.dataset.maxBooking || guestField.getAttribute('max') || '9999', 10) || 9999;
      let value = parseInt(guestField.value || '0', 10);
      const wasAboveMax = Number.isFinite(value) && value > max;
      if (wasAboveMax) {
        guestField.value = String(max);
      }
      if (modalLimitMessage) {
        modalLimitMessage.textContent = wasAboveMax
          ? 'This supplier can accept up to ' + max.toLocaleString('en-US') + ' for this booking.'
          : (modalMode === 'venue'
            ? 'Suggested from selected hall max: ' + max.toLocaleString('en-US') + ' guests'
            : 'Suggested maximum booking: ' + max.toLocaleString('en-US'));
        modalLimitMessage.classList.toggle('is-visible', true);
      }
    };
    const clearModalErrors = () => {
      fieldWraps.forEach(wrap => wrap.classList.remove('is-missing'));
      modalError?.classList.remove('is-visible');
    };
    Object.values(modalFields).forEach(field => {
      field?.addEventListener('input', () => {
        if (field === modalFields.guests) {
          clampModalGuestLimit(true);
          // Sync back to shared guest count input
          var guestInput = document.getElementById('guestCountInput');
          var guestHidden = document.getElementById('cartGuestCount');
          var gc = parseInt(field.value, 10) || 0;
          if (guestInput && gc > 0) {
            guestInput.value = gc;
            if (guestHidden) guestHidden.value = gc;
            window.dispatchEvent(new CustomEvent('gp:guestCountChanged', { detail: { guestCount: gc } }));
          }
        }
        field.closest('.gp-booking-modal-field')?.classList.remove('is-missing');
        if (!fieldWraps.some(wrap => wrap.classList.contains('is-missing'))) {
          modalError?.classList.remove('is-visible');
        }
      });
      field?.addEventListener('keydown', (event) => {
        if (event.key !== 'Enter' || field.tagName === 'TEXTAREA') return;
        event.preventDefault();
        const visibleInputs = fieldWraps
          .filter(wrap => !wrap.hidden)
          .map(wrap => wrap.querySelector('input, textarea'))
          .filter(Boolean);
        const currentIndex = visibleInputs.indexOf(field);
        const nextField = visibleInputs[currentIndex + 1];
        if (nextField) nextField.focus();
        else bookingDetailsModal.querySelector('[data-booking-modal-continue]')?.focus();
      });
    });
    const validateModalDetails = () => {
      clearModalErrors();
      let firstMissing = null;
      visibleFields.forEach(fieldName => {
        if (fieldName === 'notes') return;
        const field = modalFields[fieldName];
        const wrap = field?.closest('.gp-booking-modal-field');
        if (!field || !wrap || wrap.hidden) return;
        const empty = field.type === 'number'
          ? String(field.value || '').trim() === '' || Number(field.value) <= 0
          : String(field.value || '').trim() === '';
        if (fieldName === 'guests') clampModalGuestLimit(true);
        if (!empty) return;
        wrap.classList.add('is-missing');
        if (!firstMissing) firstMissing = field;
      });
      if (firstMissing) {
        modalError?.classList.add('is-visible');
        firstMissing.focus();
        return false;
      }
      return true;
    };

    const openBookingDetailsModal = () => {
      // Pre-fill guests from shared guest count input
      var guestCountBar = document.getElementById('guestCountInput');
      if (guestCountBar && modalFields.guests && !modalFields.guests.value) {
        var gc = parseInt(guestCountBar.value, 10);
        if (gc > 0) modalFields.guests.value = gc;
      }
      updateModalGuestMax();
      clampModalGuestLimit(false);
      clearModalErrors();
      bookingDetailsModal.hidden = false;
      bookingDetailsModal.classList.add('is-open');
      document.body.style.overflow = 'hidden';
      const firstVisible = fieldWraps.find(wrap => !wrap.hidden)?.querySelector('input, textarea');
      firstVisible?.focus();
    };
    const closeBookingDetailsModal = () => {
      bookingDetailsModal.classList.remove('is-open');
      bookingDetailsModal.hidden = true;
      document.body.style.overflow = '';
    };
    const submitOriginalCartForm = () => {
      serviceCartForm.dataset.bookingModalBypass = '1';
      if (typeof serviceCartForm.requestSubmit === 'function') {
        serviceCartForm.requestSubmit();
      } else {
        serviceCartForm.submit();
      }
    };
    const saveModalDraft = () => {
      const detailDraft = {
        serviceId: String(bookingDetailsModal.dataset.serviceId || serviceId || ''),
        serviceName: bookingDetailsModal.dataset.serviceName || '',
        categoryKey: bookingDetailsModal.dataset.categoryKey || '',
        values: {
          guests: modalFields.guests?.value || '',
          location: modalFields.location?.value || '',
          contactName: modalFields.contactName?.value || '',
          contactPhone: modalFields.contactPhone?.value || '',
          notes: modalFields.notes?.value || ''
        },
        savedAt: Date.now()
      };
      try {
        const storageKey = 'gpBookingDetailDrafts';
        const existing = JSON.parse(sessionStorage.getItem(storageKey) || '[]').filter(item => String(item.serviceId || '') !== detailDraft.serviceId);
        existing.push(detailDraft);
        sessionStorage.setItem(storageKey, JSON.stringify(existing.slice(-12)));
      } catch (error) {
        // Optional enhancement only; the normal booking flow still continues.
      }
    };

    serviceCartForm.addEventListener('submit', (event) => {
      if (serviceCartForm.dataset.bookingModalBypass === '1') {
        return;
      }
      // Skip booking details modal for attire (handled by attire-specific flow)
      if (document.getElementById('cartAttireItemId')) {
        serviceCartForm.dataset.bookingModalBypass = '1';
        return;
      }
      // Validate food/decoration item selection before opening modal
      var foodInput = document.getElementById('cartCakeDesignId');
      if (foodInput && !foodInput.value) {
        event.preventDefault();
        event.stopImmediatePropagation();
        var section = document.querySelector('.booking-section');
        if (section) section.scrollIntoView({ behavior: 'smooth' });
        return;
      }
      var decoInput = document.getElementById('cartDecorationStyleId');
      if (decoInput && !decoInput.value) {
        event.preventDefault();
        event.stopImmediatePropagation();
        var section = document.querySelector('.booking-section');
        if (section) section.scrollIntoView({ behavior: 'smooth' });
        return;
      }
      const currentSelection = document.querySelector("input[name='service_slot']:checked");
      if (currentSelection) updateSelectedSlot(currentSelection);
      const currentFullday = document.querySelector('[data-fullday-row].is-selected');
      if (currentFullday) updateSelectedFulldayRow(currentFullday);
      event.preventDefault();
      event.stopImmediatePropagation();
      openBookingDetailsModal();
    }, true);

    bookingDetailsModal.querySelector('[data-booking-modal-skip]')?.addEventListener('click', () => {
      clearModalErrors();
      closeBookingDetailsModal();
      submitOriginalCartForm();
    });
    bookingDetailsModal.querySelector('[data-booking-modal-continue]')?.addEventListener('click', () => {
      if (!validateModalDetails()) return;
      saveModalDraft();
      closeBookingDetailsModal();
      submitOriginalCartForm();
    });
    bookingDetailsModal.addEventListener('click', (event) => {
      if (event.target === bookingDetailsModal) closeBookingDetailsModal();
    });
    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape' && !bookingDetailsModal.hidden) closeBookingDetailsModal();
    });
  }

  if (serviceCartForm && addCartLink) {
    var isLoggedIn = <?= $isLoggedIn ? 'true' : 'false' ?>;
    serviceCartForm.addEventListener('submit', function(e) {
      if (!isLoggedIn) {
        e.preventDefault();
        showAuthModal();
        return false;
      }
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
  const galleryHeroItems = <?= json_encode($heroItems) ?>;

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

  document.querySelectorAll('[data-related-next]').forEach((btn) => {
    const grid = btn.closest('.related-carousel')?.querySelector('.related-grid');
    if (!grid) return;
    btn.addEventListener('click', () => {
      const card = grid.querySelector('.related-item');
      const gap = parseFloat(getComputedStyle(grid).columnGap || '18') || 18;
      const step = card ? card.getBoundingClientRect().width + gap : grid.clientWidth;
      grid.scrollBy({ left: step, behavior: 'smooth' });
    });
  });

  document.querySelectorAll('.related-item[data-url]').forEach((card) => {
    card.addEventListener('click', (event) => {
      if (event.target.closest('a, button')) return;
      window.location.href = card.dataset.url;
    });

    card.addEventListener('keydown', (event) => {
      if (event.key !== 'Enter' && event.key !== ' ') return;
      if (event.target.closest('a, button')) return;
      event.preventDefault();
      window.location.href = card.dataset.url;
    });
  });

  const cartBadge = document.querySelector('[data-cart-count-badge]');
  if (cartBadge) {
    fetch('<?= URLROOT ?>/cart/cartCount', {headers: {'Accept': 'application/json'}})
      .then(response => response.ok ? response.json() : null)
      .then(data => {
        if (!data || typeof data.count === 'undefined') return;
        const count = parseInt(data.count, 10) || 0;
        cartBadge.textContent = count > 0 ? (count > 99 ? '99+' : String(count)) : '';
        cartBadge.hidden = count <= 0;
        const cartLink = cartBadge.closest('.floating-cart');
        if (cartLink) {
          cartLink.setAttribute('aria-label', count > 0 ? 'Open cart with ' + count + ' selected service' + (count === 1 ? '' : 's') : 'Open cart');
        }
      })
      .catch(() => {});
  }

  // Profile dropdown toggle
  document.addEventListener('click', (e) => {
    const closeBtn = e.target.closest('[data-profile-close], .tb-profile-close');
    if (closeBtn) {
      closeBtn.closest('.tb-profile-dropdown')?.querySelector('.tb-profile-btn')?.setAttribute('aria-expanded', 'false');
      return;
    }
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

  if (!document.getElementById('gp-wishlist-fly-style')) {
    var flyStyle = document.createElement('style');
    flyStyle.id = 'gp-wishlist-fly-style';
    flyStyle.textContent = '.gp-flying-heart{position:fixed;z-index:12000;display:grid;place-items:center;width:30px;height:30px;border-radius:50%;background:#fff8ef;color:#e55b5b;font-size:20px;font-weight:800;box-shadow:0 12px 28px rgba(43,27,36,.22);pointer-events:none;will-change:transform,opacity}.gp-wishlist-target-pulse{animation:gpWishTargetPulse .72s ease both}@keyframes gpWishTargetPulse{0%,100%{transform:scale(1)}45%{transform:scale(1.08);filter:brightness(1.04)}}';
    document.head.appendChild(flyStyle);
  }

  function flyHeartToWishlist(btn) {
    if (!btn || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    var start = btn.getBoundingClientRect();
    var target = Array.from(document.querySelectorAll('.tb-profile-menu-item[href*="wishlist"], .home-profile-menu-item[href*="wishlist"]')).find(function(el) {
      var style = getComputedStyle(el);
      return style.visibility !== 'hidden' && style.opacity !== '0';
    }) || document.querySelector('.tb-profile-avatar') || document.querySelector('.tb-profile-btn') || document.querySelector('.home-profile-avatar') || document.querySelector('.home-profile-btn');
    if (!target) return;

    var end = target.getBoundingClientRect();
    var flying = document.createElement('span');
    flying.className = 'gp-flying-heart';
    flying.textContent = '♥';
    flying.style.left = (start.left + start.width / 2 - 15) + 'px';
    flying.style.top = (start.top + start.height / 2 - 15) + 'px';
    document.body.appendChild(flying);

    var dx = end.left + end.width / 2 - (start.left + start.width / 2);
    var dy = end.top + end.height / 2 - (start.top + start.height / 2);
    flying.animate([
      { transform: 'translate(0,0) scale(1)', opacity: 1 },
      { transform: 'translate(' + (dx * .48) + 'px,' + (dy * .22 - 34) + 'px) scale(1.18)', opacity: .96 },
      { transform: 'translate(' + dx + 'px,' + dy + 'px) scale(.38)', opacity: 0 }
    ], { duration: 900, easing: 'cubic-bezier(.2,.86,.24,1)', fill: 'forwards' }).onfinish = function() {
      flying.remove();
      target.classList.add('gp-wishlist-target-pulse');
      setTimeout(function(){ target.classList.remove('gp-wishlist-target-pulse'); }, 560);
    };
  }

  heart.addEventListener('click', function(){
    var isLoggedIn = <?= !empty($_SESSION['session_uid']) ? 'true' : 'false' ?>;
    if (!isLoggedIn) {
      showAuthModal();
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
          flyHeartToWishlist(heart);
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

<!-- Auth Required Modal -->
<div id="authRequiredModal" style="display:none;position:fixed;inset:0;z-index:9999;align-items:center;justify-content:center;background:rgba(0,0,0,0.5);backdrop-filter:blur(4px);">
  <div style="background:#fdf8f3;border-radius:16px;padding:32px;max-width:400px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,0.3);text-align:center;position:relative;animation:modalIn 0.3s ease-out;">
    <button onclick="closeAuthModal()" style="position:absolute;top:12px;right:16px;background:none;border:none;font-size:24px;cursor:pointer;color:#7a6255;">&times;</button>
    <div style="font-size:48px;margin-bottom:16px;">💍</div>
    <h2 style="font-family:'Playfair Display',serif;font-size:24px;color:#211d1a;margin:0 0 8px;">Sign in to Book</h2>
    <p style="color:#7a6255;font-size:14px;margin:0 0 24px;line-height:1.5;">Create an account or sign in to add this service to your cart and complete your booking.</p>
    <a href="<?= URLROOT ?>/users/auth" id="modalLoginBtn" style="display:block;width:100%;padding:14px;background:linear-gradient(135deg,#b8860b,#d4a574);color:#fff;border:none;border-radius:10px;font-size:16px;font-weight:600;cursor:pointer;text-decoration:center;margin-bottom:10px;font-family:'Poppins',sans-serif;">Sign In</a>
    <a href="<?= URLROOT ?>/users/register" id="modalRegisterBtn" style="display:block;width:100%;padding:14px;background:transparent;color:#7a6255;border:1.5px solid #d4a574;border-radius:10px;font-size:16px;font-weight:600;cursor:pointer;text-decoration:center;font-family:'Poppins',sans-serif;">Create Account</a>
  </div>
</div>
<style>
@keyframes modalIn {
  from { transform: scale(0.9); opacity: 0; }
  to { transform: scale(1); opacity: 1; }
}
/* ── Attire Gallery Section ── */
.attire-gallery-section {
  max-width: 1200px;
  margin: 0 auto;
  padding: 40px clamp(20px, 4vw, 48px) 0;
}
.attire-gallery-section .section-title {
  font-family: var(--font-serif);
  font-size: clamp(22px, 3vw, 30px);
  color: var(--ink);
  margin-bottom: 6px;
}
.attire-gallery-section .section-sub {
  color: var(--muted);
  font-size: 14px;
  margin-bottom: 28px;
}
.attire-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
  gap: 16px;
}
.attire-card {
  background: var(--panel);
  border: 1.5px solid var(--line-soft);
  border-radius: 18px;
  overflow: hidden;
  cursor: pointer;
  transition: border-color 0.2s, box-shadow 0.2s, transform 0.2s;
  position: relative;
}
.attire-card:hover {
  border-color: var(--wine);
  box-shadow: var(--shadow-sm);
  transform: translateY(-2px);
}
.attire-card.is-selected {
  border-color: var(--wine);
  box-shadow: 0 0 0 3px var(--wine-glow), var(--shadow-sm);
}
.attire-card.is-locked {
  opacity: 0.55;
  cursor: default;
  pointer-events: none;
}
.attire-card-media {
  aspect-ratio: 4/3;
  background: var(--cream);
  position: relative;
  overflow: hidden;
}
.attire-card-media img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.4s var(--ease-out-expo);
}
.attire-card:hover .attire-card-media img {
  transform: scale(1.04);
}
.attire-card-placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--muted-light);
}
.attire-card-badge {
  position: absolute;
  top: 12px;
  left: 12px;
  background: rgba(146, 64, 14, 0.9);
  color: #fff;
  font-size: 11px;
  font-weight: 700;
  padding: 4px 10px;
  border-radius: var(--radius-full);
}
.attire-card-body {
  padding: 12px;
}
.attire-card-name {
  font-family: var(--font-serif);
  font-size: 15px;
  font-weight: 600;
  color: var(--ink);
  margin-bottom: 3px;
  line-height: 1.2;
}
.attire-card-desc {
  font-size: 11px;
  color: var(--muted);
  line-height: 1.4;
  margin-bottom: 8px;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.attire-card-prices {
  display: flex;
  gap: 6px;
  flex-wrap: wrap;
}
.attire-price-tag {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 11px;
  font-weight: 700;
  padding: 4px 8px;
  border-radius: var(--radius-full);
}
.attire-price-tag.is-borrow {
  background: rgba(118, 90, 70, 0.08);
  color: var(--sage);
}
.attire-price-tag.is-buy {
  background: rgba(212, 180, 106, 0.15);
  color: var(--gold);
}
.attire-card-actions {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 8px;
  margin-top: 10px;
}
.attire-card-action {
  min-height: 34px;
  border: 1px solid var(--line-soft);
  border-radius: 10px;
  background: #fffaf5;
  color: var(--ink-soft);
  font-family: var(--font-sans);
  font-size: 12px;
  font-weight: 800;
  cursor: pointer;
  transition: background .18s, border-color .18s, color .18s, transform .18s;
}
.attire-card-action:hover {
  transform: translateY(-1px);
  border-color: var(--wine);
  color: var(--wine);
}
.attire-card-action.is-buy {
  background: var(--wine);
  border-color: var(--wine);
  color: #fffaf5;
}
.attire-card-action.is-buy:hover {
  background: #5d3445;
  color: #fffaf5;
}
.attire-card-check {
  position: absolute;
  top: 10px;
  right: 10px;
  color: var(--wine);
  background: rgba(255,250,245,.96);
  border: 1px solid rgba(109,76,91,.18);
  border-radius: 999px;
  width: 30px;
  height: 30px;
  display: grid;
  place-items: center;
  opacity: 0;
  transform: translateY(-4px) scale(.86);
  transition: opacity 0.2s, transform 0.2s var(--ease-spring), background .2s, color .2s;
  box-shadow: 0 10px 24px rgba(63,36,26,.10);
}
.attire-card-check svg {
  width: 16px;
  height: 16px;
  stroke-width: 2.4;
}
.attire-card.is-selected .attire-card-check {
  opacity: 1;
  transform: translateY(0) scale(1);
  background: var(--wine);
  color: #fffaf5;
  border-color: var(--wine);
}

/* ── Guest Count Bar ── */
.guest-count-bar {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 14px 18px;
  margin-bottom: 16px;
  background: var(--cream);
  border-radius: var(--radius-lg);
  border: 1.5px solid var(--line-soft);
}
.guest-count-label {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  font-weight: 700;
  color: var(--ink-soft);
  white-space: nowrap;
}
.guest-count-input {
  flex: 1;
  max-width: 160px;
  padding: 8px 12px;
  border: 1.5px solid var(--line);
  border-radius: var(--radius);
  font-size: 14px;
  font-weight: 600;
  background: var(--panel);
  text-align: center;
  transition: border-color 0.2s;
}
.guest-count-input:focus {
  border-color: var(--wine);
  outline: none;
}

/* ── Attire Booking Panel (availability section replacement) ── */
#attireBookingPanel {
  padding: 20px;
}
.attire-prompt-state {
  text-align: center;
  padding: 48px 20px;
  color: var(--muted);
}
.attire-prompt-icon {
  width: 56px;
  height: 56px;
  margin: 0 auto 16px;
  display: grid;
  place-items: center;
  background: var(--cream);
  border-radius: 50%;
  color: var(--wine);
}
.attire-prompt-state h3 {
  font-family: var(--font-serif);
  font-size: 18px;
  color: var(--ink);
  margin-bottom: 6px;
}
.attire-prompt-state p {
  font-size: 13px;
}
.attire-selected-header {
  display: flex;
  gap: 14px;
  align-items: center;
  margin-bottom: 24px;
  padding-bottom: 18px;
  border-bottom: 1px solid var(--line-soft);
}
.attire-selected-thumb {
  width: 64px;
  height: 64px;
  border-radius: var(--radius-lg);
  object-fit: cover;
  flex-shrink: 0;
}
.attire-selected-name {
  font-family: var(--font-serif);
  font-size: 16px;
  color: var(--ink);
  margin-bottom: 2px;
}
.attire-selected-desc {
  font-size: 12px;
  color: var(--muted);
}
.rental-section-label {
  font-size: 12px;
  font-weight: 700;
  color: var(--ink-soft);
  margin-bottom: 8px;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}
.rental-type-section {
  margin-bottom: 20px;
}
.rental-type-toggle {
  display: flex;
  gap: 10px;
}
.rental-type-btn-main {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 12px 16px;
  border: 2px solid var(--line);
  border-radius: var(--radius-lg);
  background: var(--panel);
  cursor: pointer;
  font-size: 14px;
  font-weight: 600;
  color: var(--ink-soft);
  transition: border-color 0.2s, background 0.2s, color 0.2s;
}
.rental-type-btn-main:hover {
  border-color: var(--wine);
}
.rental-type-btn-main.is-active {
  border-color: var(--wine);
  background: var(--wine-glow);
  color: var(--wine);
}
.attire-borrow-grid {
  display: grid;
  grid-template-columns: minmax(0, 1.1fr) minmax(220px, .9fr);
  gap: 14px;
  align-items: start;
}
.attire-date-input-wrap {
  width: 100%;
  min-height: 46px;
  border: 1.5px solid rgba(212,180,106,.34);
  border-radius: 14px;
  background: #fffaf5;
  padding: 0 14px;
  box-shadow: 0 10px 24px rgba(63,36,26,.05);
}
.attire-date-input-wrap .venue-date-display {
  min-width: 0;
  font-size: 14px;
  font-weight: 800;
  color: var(--ink);
}
.attire-date-input-wrap .venue-date-icon,
.attire-date-input-wrap .venue-date-chevron {
  color: var(--wine);
}
.duration-options-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(92px, 104px));
  justify-content: start;
  gap: 8px;
  margin-bottom: 16px;
}
.duration-option-btn {
  display: grid;
  place-items: center;
  gap: 4px;
  min-height: 52px;
  padding: 8px 10px;
  border: 1.5px solid var(--line-soft);
  border-radius: 14px;
  background: #fffaf5;
  cursor: pointer;
  font-size: 14px;
  transition: border-color 0.2s, background 0.2s, box-shadow .2s;
}
.duration-option-btn:hover {
  border-color: var(--wine);
}
.duration-option-btn.is-active {
  border-color: var(--wine);
  background: var(--wine-glow);
  box-shadow: 0 10px 22px rgba(109,76,91,.08);
}
.duration-option-btn .dur-days {
  font-weight: 800;
  color: var(--ink);
  text-align: center;
  font-size: 12px;
}
.duration-option-btn .dur-price {
  font-weight: 700;
  color: var(--wine);
  font-size: 12px;
}
.rental-date-input {
  width: 100%;
  min-height: 46px;
  padding: 10px 14px;
  border: 1.5px solid rgba(212,180,106,.34);
  border-radius: 14px;
  font-size: 14px;
  font-weight: 700;
  color: var(--ink);
  background: #fffaf5;
  box-shadow: 0 10px 24px rgba(63,36,26,.05);
  transition: border-color 0.2s, box-shadow .2s;
  color-scheme: light;
}
.rental-date-input:focus {
  border-color: var(--wine);
  box-shadow: 0 12px 28px rgba(109,76,91,.10);
  outline: none;
}
.rental-date-error {
  font-size: 12px;
  color: #dc2626;
  margin-top: 6px;
  padding: 8px 12px;
  background: #fef2f2;
  border-radius: var(--radius);
}
.rental-date-summary {
  font-size: 12px;
  color: var(--sage);
  margin-top: 8px;
  padding: 8px 12px;
  background: rgba(118, 90, 70, 0.06);
  border-radius: var(--radius);
}
.rental-date-summary strong {
  color: var(--ink);
}
.buy-price-card {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 20px;
  background: rgba(212, 180, 106, 0.08);
  border: 1.5px solid rgba(212, 180, 106, 0.25);
  border-radius: var(--radius-lg);
  margin-bottom: 16px;
}
.buy-price-card span {
  font-size: 14px;
  color: var(--muted);
}
.buy-price-card strong {
  font-size: 20px;
  color: var(--ink);
}

/* ── Sidebar Attire Summary ── */
.sidebar-attire-summary {
  margin-bottom: 12px;
}
.sidebar-attire-item {
  display: flex;
  gap: 10px;
  align-items: center;
  padding: 10px;
  background: var(--cream);
  border-radius: var(--radius-lg);
}
.sidebar-attire-thumb {
  width: 44px;
  height: 44px;
  border-radius: var(--radius);
  object-fit: cover;
  flex-shrink: 0;
}
.sidebar-attire-info strong {
  display: block;
  font-size: 13px;
  color: var(--ink);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 160px;
}
.sidebar-attire-info small {
  font-size: 11px;
  color: var(--muted);
}

/* ── Responsive ── */
@media (max-width: 640px) {
  .attire-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
  }
  .attire-card-body {
    padding: 10px;
  }
  .attire-card-name { font-size: 14px; }
  .attire-card-desc { display: none; }
  .attire-card-select-hint { display: none; }
  .attire-borrow-grid { grid-template-columns: 1fr; }
}
</style>
<script>
function saveGuestBookingState() {
  var state = {
    serviceId: <?= (int)($service['id'] ?? 0) ?>,
    date: (document.getElementById('cartDate') || {}).value || '',
    slotId: (document.getElementById('cartSlotId') || {}).value || '',
    venueRoomId: (document.getElementById('cartVenueRoomId') || {}).value || '',
    startTime: (document.getElementById('cartStartTime') || {}).value || '',
    endTime: (document.getElementById('cartEndTime') || {}).value || '',
    price: (document.getElementById('cartPrice') || {}).value || '',
    attireItemId: (document.getElementById('cartAttireItemId') || {}).value || '',
    rentalType: (document.getElementById('cartRentalType') || {}).value || '',
    borrowDate: (document.getElementById('cartBorrowDate') || {}).value || '',
    rentalOptionId: (document.getElementById('cartRentalOptionId') || {}).value || '',
    decorationStyleId: (document.getElementById('cartDecorationStyleId') || {}).value || '',
    cake_design_id: (document.getElementById('cartCakeDesignId') || {}).value || '',
    guest_count: (document.getElementById('cartGuestCount') || {}).value || '',
    // Booking details modal draft
    modalDraft: null,
    savedAt: Date.now()
  };
  try { sessionStorage.setItem('gpGuestBookingState', JSON.stringify(state)); } catch(e) {}
}
function showAuthModal() {
  saveGuestBookingState();
  var modal = document.getElementById('authRequiredModal');
  modal.style.display = 'flex';
  document.body.style.overflow = 'hidden';
}
function closeAuthModal() {
  var modal = document.getElementById('authRequiredModal');
  modal.style.display = 'none';
  document.body.style.overflow = '';
  // Reset book button state
  var btn = document.getElementById('addCartLink');
  if (btn) {
    btn.classList.remove('is-submitting');
    btn.disabled = false;
    btn.innerHTML = '<i data-lucide="shopping-cart" size="16"></i> <?= $isAddonContext ? 'Add to package' : ($isRentalCategory ? 'Add to Cart' : 'Book now') ?>';
    lucide.createIcons();
  }
  var mobileBtn = document.getElementById('mobileBookBtn');
  if (mobileBtn) mobileBtn.classList.remove('is-submitting');
}
// Close modal on backdrop click
document.getElementById('authRequiredModal').addEventListener('click', function(e) {
  if (e.target === this) closeAuthModal();
});
// Set redirect URL for after login
var loginBtn = document.getElementById('modalLoginBtn');
if (loginBtn) {
  loginBtn.href = '<?= URLROOT ?>/users/auth?redirect=' + encodeURIComponent(window.location.pathname + window.location.search);
}
// Also update register link
var registerBtn = document.getElementById('modalRegisterBtn');
if (registerBtn) {
  registerBtn.href = '<?= URLROOT ?>/users/register?redirect=' + encodeURIComponent(window.location.pathname + window.location.search);
}
</script>

<?php if ($isRentalCategory && !empty($attireItems)): ?>
<script>
(function() {
  const attireItems = <?= json_encode($attireItems, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
  const minLeadDays = <?= (int)($service['min_lead_days'] ?? 0) ?>;
  const serviceId = <?= (int)($service['id'] ?? 0) ?>;
  const URLROOT = '<?= URLROOT ?>';
  const money = (v) => Number(v).toLocaleString() + ' MMK';

  let selectedAttireIdx = null;
  let selectedRentalType = null;
  let selectedRentalOptionId = null;
  let blockedDates = [];

  // ── DOM refs: Gallery ──
  const galleryCards = document.querySelectorAll('[data-attire-card]');

  // ── DOM refs: Booking panel (availability section) ──
  const attireNotSelected = document.getElementById('attireNotSelected');
  const attireSelectedPanel = document.getElementById('attireSelectedPanel');
  const attireSelectedPhoto = document.getElementById('attireSelectedPhoto');
  const attireSelectedName = document.getElementById('attireSelectedName');
  const attireSelectedDesc = document.getElementById('attireSelectedDesc');
  const attireRentalTypeSection = document.getElementById('attireRentalTypeSection');
  const rentalBorrowBtn = document.getElementById('rentalBorrowBtn');
  const rentalBuyBtn = document.getElementById('rentalBuyBtn');
  const borrowSection = document.getElementById('borrowSection');
  const buySection = document.getElementById('buySection');
  const durationOptionsMain = document.getElementById('durationOptionsMain');
  const borrowDateSection = document.getElementById('borrowDateSection');
  const borrowDateMain = document.getElementById('borrowDateMain');
  const borrowDateErrorMain = document.getElementById('borrowDateErrorMain');
  const rentalDateSummaryMain = document.getElementById('rentalDateSummaryMain');
  const buyPriceMain = document.getElementById('buyPriceMain');

  // ── DOM refs: Sidebar ──
  const sidebarAttireSummary = document.getElementById('sidebarAttireSummary');
  const sidebarAttirePhoto = document.getElementById('sidebarAttirePhoto');
  const sidebarAttireName = document.getElementById('sidebarAttireName');
  const sidebarAttireRental = document.getElementById('sidebarAttireRental');
  const sidebarEstimatedTotal = document.getElementById('sidebarEstimatedTotal');

  // ── DOM refs: Cart form ──
  const cartAttireItemId = document.getElementById('cartAttireItemId');
  const cartRentalType = document.getElementById('cartRentalType');
  const cartBorrowDate = document.getElementById('cartBorrowDate');
  const cartRentalOptionId = document.getElementById('cartRentalOptionId');
  const cartPrice = document.getElementById('cartPrice');
  const cartDate = document.getElementById('cartDate');

  // ── DOM refs: Mobile book bar ──
  const mobileBookPrice = document.getElementById('mobileBookPrice');
  const mobileBookLabel = document.getElementById('mobileBookLabel');
  const mobileBookBtn = document.getElementById('mobileBookBtn');
  const resetBorrowDateDisplay = () => {
    const display = borrowDateMain?.closest('.venue-date-input-wrap')?.querySelector('.venue-date-display');
    if (display) display.textContent = borrowDateMain?.dataset.placeholder || 'Choose date';
  };

  function clearRentalState() {
    selectedRentalType = null;
    selectedRentalOptionId = null;
    blockedDates = [];
    if (borrowSection) borrowSection.style.display = 'none';
    if (buySection) buySection.style.display = 'none';
    if (borrowDateSection) borrowDateSection.style.display = 'none';
    if (borrowDateErrorMain) borrowDateErrorMain.style.display = 'none';
    if (rentalDateSummaryMain) rentalDateSummaryMain.style.display = 'none';
    if (durationOptionsMain) durationOptionsMain.innerHTML = '';
    if (rentalBorrowBtn) rentalBorrowBtn.classList.remove('is-active');
    if (rentalBuyBtn) rentalBuyBtn.classList.remove('is-active');
    if (cartRentalType) cartRentalType.value = '';
    if (cartBorrowDate) cartBorrowDate.value = '';
    if (cartRentalOptionId) cartRentalOptionId.value = '';
  }

  function selectItem(idx, preferredType = null) {
    if (idx < 0 || idx >= attireItems.length) return;
    const item = attireItems[idx];
    selectedAttireIdx = idx;

    // Update gallery cards
    galleryCards.forEach(c => c.classList.remove('is-selected'));
    const activeCard = document.querySelector(`[data-attire-card][data-attire-idx="${idx}"]`);
    if (activeCard) activeCard.classList.add('is-selected');

    // Show booking panel
    if (attireNotSelected) attireNotSelected.style.display = 'none';
    if (attireSelectedPanel) attireSelectedPanel.style.display = 'block';

    // Update booking panel header
    if (attireSelectedPhoto) {
      attireSelectedPhoto.src = item.photo_url || '';
      attireSelectedPhoto.alt = item.name;
      attireSelectedPhoto.style.display = item.photo_url ? '' : 'none';
    }
    if (attireSelectedName) attireSelectedName.textContent = item.name;
    if (attireSelectedDesc) attireSelectedDesc.textContent = item.description || '';

    // Update sidebar summary
    if (sidebarAttireSummary) {
      sidebarAttireSummary.style.display = 'block';
      if (sidebarAttirePhoto) {
        sidebarAttirePhoto.src = item.photo_url || '';
        sidebarAttirePhoto.alt = item.name;
        sidebarAttirePhoto.style.display = item.photo_url ? '' : 'none';
      }
      if (sidebarAttireName) sidebarAttireName.textContent = item.name;
      if (sidebarAttireRental) sidebarAttireRental.textContent = 'Select rental type below';
    }

    // Update cart hidden field
    if (cartAttireItemId) cartAttireItemId.value = item.id;

    // Show/hide rental type buttons
    const hasRentalOptions = (item.rental_options || []).length > 0;
    const hasBuyPrice = Number(item.buy_package_price) > 0;
    if (rentalBorrowBtn) rentalBorrowBtn.style.display = hasRentalOptions ? '' : 'none';
    if (rentalBuyBtn) rentalBuyBtn.style.display = hasBuyPrice ? '' : 'none';
    if (attireRentalTypeSection) attireRentalTypeSection.style.display = preferredType ? 'none' : '';

    clearRentalState();
    if (preferredType === 'borrow') {
      window.setTimeout(() => rentalBorrowBtn?.click(), 0);
    } else if (preferredType === 'buy') {
      window.setTimeout(() => rentalBuyBtn?.click(), 0);
    }

    // Scroll to availability section on mobile
    if (window.innerWidth < 900) {
      const availSection = document.getElementById('availability');
      if (availSection) setTimeout(() => availSection.scrollIntoView({ behavior: 'smooth', block: 'start' }), 200);
    }
  }

  // ── Gallery card clicks ──
  galleryCards.forEach(card => {
    card.addEventListener('click', function() {
      if (this.dataset.locked === '1') return;
      selectItem(Number(this.dataset.attireIdx));
    });
  });

  document.querySelectorAll('[data-attire-action]').forEach(btn => {
    btn.addEventListener('click', function(event) {
      event.preventDefault();
      event.stopPropagation();
      const card = this.closest('[data-attire-card]');
      if (!card || card.dataset.locked === '1') return;
      selectItem(Number(card.dataset.attireIdx), this.dataset.attireAction);
    });
  });

  // ── Rental type buttons ──
  function bindRentalTypeBtn(btn, type) {
    if (!btn) return;
    btn.addEventListener('click', function() {
      const item = attireItems[selectedAttireIdx];
      if (!item) return;

      selectedRentalType = type;
      selectedRentalOptionId = null;
      if (cartRentalType) cartRentalType.value = type;
      if (cartBorrowDate) cartBorrowDate.value = '';
      if (cartRentalOptionId) cartRentalOptionId.value = '';
      if (cartPrice) cartPrice.value = '';

      rentalBorrowBtn.classList.remove('is-active');
      rentalBuyBtn.classList.remove('is-active');
      this.classList.add('is-active');

      borrowSection.style.display = 'none';
      buySection.style.display = 'none';
      borrowDateSection.style.display = 'none';
      borrowDateErrorMain.style.display = 'none';
      rentalDateSummaryMain.style.display = 'none';
      durationOptionsMain.innerHTML = '';

      if (type === 'borrow') {
        borrowSection.style.display = 'block';
        const options = item.rental_options || [];
        options.forEach((opt, i) => {
          const b = document.createElement('button');
          b.type = 'button';
          b.className = 'duration-option-btn';
          b.dataset.optionIdx = i;
          b.dataset.optionId = opt.id;
          b.innerHTML = `<span class="dur-days">${opt.days} day${opt.days > 1 ? 's' : ''}</span>`;
          b.addEventListener('click', function() {
            selectedRentalOptionId = Number(opt.id);
            if (cartRentalOptionId) cartRentalOptionId.value = opt.id;
            if (cartPrice) cartPrice.value = opt.price;
            if (sidebarEstimatedTotal) sidebarEstimatedTotal.textContent = money(opt.price);

            durationOptionsMain.querySelectorAll('.duration-option-btn').forEach(x => x.classList.remove('is-active'));
            this.classList.add('is-active');

            borrowDateSection.style.display = 'block';
            const today = new Date();
            today.setDate(today.getDate() + minLeadDays);
            borrowDateMain.min = today.toISOString().split('T')[0];
            borrowDateMain.value = '';
            resetBorrowDateDisplay();
            rentalDateSummaryMain.style.display = 'none';
            borrowDateErrorMain.style.display = 'none';

            if (sidebarAttireRental) sidebarAttireRental.textContent = `${opt.days}-day borrow · ${money(opt.price)}`;

            fetch(`${URLROOT}/customerServices/attireAvailability/${serviceId}?attire_item_id=${item.id}`)
              .then(r => r.json())
              .then(data => { blockedDates = data.blocked || []; })
              .catch(() => { blockedDates = []; });
          });
          durationOptionsMain.appendChild(b);
        });
        if (durationOptionsMain.firstElementChild) {
          durationOptionsMain.firstElementChild.click();
        }
      } else if (type === 'buy') {
        buySection.style.display = 'block';
        buyPriceMain.textContent = money(item.buy_package_price);
        if (cartPrice) cartPrice.value = item.buy_package_price;
        if (sidebarEstimatedTotal) sidebarEstimatedTotal.textContent = money(item.buy_package_price);
        if (sidebarAttireRental) sidebarAttireRental.textContent = `Purchase · ${money(item.buy_package_price)}`;
        if (cartDate) cartDate.value = '';
      }
    });
  }
  bindRentalTypeBtn(rentalBorrowBtn, 'borrow');
  bindRentalTypeBtn(rentalBuyBtn, 'buy');

  // ── Borrow date change ──
  if (borrowDateMain) {
    borrowDateMain.addEventListener('change', function() {
      const date = this.value;
      borrowDateErrorMain.style.display = 'none';
      rentalDateSummaryMain.style.display = 'none';
      if (cartBorrowDate) cartBorrowDate.value = '';
      if (cartDate) cartDate.value = '';

      if (!date || selectedAttireIdx === null || !selectedRentalOptionId) return;

      const item = attireItems[selectedAttireIdx];
      const opt = (item.rental_options || []).find(o => Number(o.id) === selectedRentalOptionId);
      if (!opt) return;

      const rentalDays = Number(opt.days);
      const bufferDays = Number(item.buffer_days || 1);
      const borrowTs = new Date(date).getTime();
      let conflict = false;
      for (let d = 0; d < rentalDays + bufferDays; d++) {
        const checkDate = new Date(borrowTs + d * 86400000).toISOString().split('T')[0];
        if (blockedDates.includes(checkDate)) { conflict = true; break; }
      }

      if (conflict) {
        borrowDateErrorMain.textContent = 'This item is not available for the selected dates. Please choose a different date.';
        borrowDateErrorMain.style.display = 'block';
        return;
      }

      const returnDate = new Date(borrowTs + (rentalDays - 1) * 86400000);
      const bufferUntil = new Date(borrowTs + (rentalDays + bufferDays - 1) * 86400000);
      rentalDateSummaryMain.innerHTML = `Return by: <strong>${returnDate.toLocaleDateString()}</strong> · Buffer until: ${bufferUntil.toLocaleDateString()}`;
      rentalDateSummaryMain.style.display = 'block';

      if (cartBorrowDate) cartBorrowDate.value = date;
      if (cartDate) cartDate.value = date;

      // Update sidebar date
      const selectedDateEl = document.getElementById('selectedDate');
      if (selectedDateEl) selectedDateEl.textContent = new Date(borrowTs).toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' });
    });
  }

  // ── Cart form validation for attire ──
  const serviceCartForm = document.getElementById('serviceCartForm');
  if (serviceCartForm) {
    serviceCartForm.addEventListener('submit', function(e) {
      if (selectedAttireIdx === null) {
        e.preventDefault();
        document.getElementById('attire-gallery')?.scrollIntoView({ behavior: 'smooth' });
        return;
      }
      if (!cartAttireItemId.value) {
        e.preventDefault();
        return;
      }
      if (selectedRentalType === 'borrow' && (!cartBorrowDate.value || !cartRentalOptionId.value)) {
        e.preventDefault();
        const availSection = document.getElementById('availability');
        if (availSection) availSection.scrollIntoView({ behavior: 'smooth' });
        return;
      }
      if (selectedRentalType === 'buy' && !cartPrice.value) {
        e.preventDefault();
        return;
      }
      // Check auth for attire
      var isLogged = <?= !empty($_SESSION['session_uid']) ? 'true' : 'false' ?>;
      if (!isLogged) {
        e.preventDefault();
        showAuthModal();
        return;
      }
      // Bypass the booking details modal for attire (not relevant for dress rental)
      serviceCartForm.dataset.bookingModalBypass = '1';
    });
  }

  // ── Update mobile book bar on item selection ──
  const origMobileClick = mobileBookBtn?.onclick;
  function updateMobileBar(item, rentalLabel) {
    if (mobileBookPrice) {
      const price = rentalLabel || money(item.buy_package_price || item.borrow_package_price || 0);
      mobileBookPrice.textContent = price;
    }
    if (mobileBookLabel) mobileBookLabel.textContent = item.name;
    if (mobileBookBtn) {
      mobileBookBtn.classList.remove('is-guidance');
      mobileBookBtn.textContent = 'Add to Cart';
      mobileBookBtn.onclick = function(e) {
        if (serviceCartForm) {
          serviceCartForm.requestSubmit ? serviceCartForm.requestSubmit() : serviceCartForm.submit();
        }
      };
    }
  }

  // ── Restore guest booking state for attire ──
  var savedState = null;
  try { var raw = sessionStorage.getItem('gpGuestBookingState'); if (raw) savedState = JSON.parse(raw); } catch(e) {}
  if (savedState && Number(savedState.serviceId) === serviceId && Date.now() - (savedState.savedAt || 0) < 30 * 60 * 1000) {
    // Don't remove here - the main restore function will remove it
    if (savedState.attireItemId) {
      var itemIdx = attireItems.findIndex(function(it) { return String(it.id) === String(savedState.attireItemId); });
      if (itemIdx >= 0) {
        selectItem(itemIdx);
        // Restore rental type
        if (savedState.rentalType) {
          var typeBtn = savedState.rentalType === 'borrow' ? rentalBorrowBtn : rentalBuyBtn;
          if (typeBtn) typeBtn.click();
          // Restore rental option (for borrow)
          if (savedState.rentalType === 'borrow' && savedState.rentalOptionId) {
            setTimeout(function() {
              var optBtn = document.querySelector('.duration-option-btn[data-option-id="' + savedState.rentalOptionId + '"]');
              if (optBtn) optBtn.click();
              // Restore borrow date
              if (savedState.borrowDate && borrowDateMain) {
                setTimeout(function() {
                  borrowDateMain.value = savedState.borrowDate;
                  borrowDateMain.dispatchEvent(new Event('change'));
                }, 100);
              }
            }, 100);
          }
        }
      }
    }
  } else if (attireItems.length === 1) {
    // Auto-select if only one item (no saved state)
    selectItem(0);
  }
})();
</script>
<?php endif; ?>

<?php if ($isDecorationCategory && !empty($decorationStyles)): ?>
<script>
(function() {
  const decoStyles = <?= json_encode($decorationStyles, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
  const cartDecorationStyleId = document.getElementById('cartDecorationStyleId');
  const selectedDecoStyleEl = document.getElementById('selectedDecoStyle');
  const sidebarEstimatedTotal = document.getElementById('sidebarEstimatedTotal');
  const serviceCartForm = document.getElementById('serviceCartForm');
  const money = (v) => Number(v).toLocaleString() + ' MMK';

  let selectedStyleId = null;

  // Handle decoration style radio selection
  document.querySelectorAll('input[name="decoration_style"]').forEach(radio => {
    radio.addEventListener('change', function() {
      if (this.disabled) return;
      const styleId = Number(this.dataset.decoId);
      const styleName = this.dataset.decoName;
      const stylePrice = this.dataset.decoPrice;

      selectedStyleId = styleId;
      if (cartDecorationStyleId) cartDecorationStyleId.value = styleId;

      // Update row visual state
      document.querySelectorAll('[data-deco-row]').forEach(row => {
        row.classList.remove('is-selected');
      });
      const activeRow = document.querySelector(`[data-deco-row][data-deco-id="${styleId}"]`);
      if (activeRow) activeRow.classList.add('is-selected');

      // Update sidebar
      if (selectedDecoStyleEl) selectedDecoStyleEl.textContent = styleName;
      if (sidebarEstimatedTotal) sidebarEstimatedTotal.textContent = money(stylePrice);
    });
  });

  // Auto-select first available style
  const firstRadio = document.querySelector('input[name="decoration_style"]:not(:disabled)');
  if (firstRadio) {
    firstRadio.checked = true;
    firstRadio.dispatchEvent(new Event('change'));
  }

  // Cart form validation
  if (serviceCartForm) {
    serviceCartForm.addEventListener('submit', function(e) {
      if (!cartDecorationStyleId || !cartDecorationStyleId.value) {
        e.preventDefault();
        // Scroll to availability section
        const section = document.querySelector('.booking-section');
        if (section) section.scrollIntoView({ behavior: 'smooth' });
      }
    });
  }
})();
</script>
<?php endif; ?>

<?php if ($isFoodCategory && !empty($foodItems)): ?>
<script>
(function() {
  const foodItems = <?= json_encode($foodItems, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
  const cartCakeDesignId = document.getElementById('cartCakeDesignId');
  const selectedFoodItemEl = document.getElementById('selectedFoodItem');
  const sidebarEstimatedTotal = document.getElementById('sidebarEstimatedTotal');
  const serviceCartForm = document.getElementById('serviceCartForm');
  const guestCountInput = document.getElementById('guestCountInput');
  const cartGuestCount = document.getElementById('cartGuestCount');
  const money = (v) => Number(v).toLocaleString() + ' MMK';

  let selectedFoodId = null;
  let currentGuestCount = 0;

  function getGuestCount() {
    return currentGuestCount > 0 ? currentGuestCount : 0;
  }

  function updateFoodTotals() {
    const gc = getGuestCount();
    foodItems.forEach(item => {
      const totalDisplays = document.querySelectorAll(`.food-total-display[data-food-total-id="${item.id}"]`);
      const totalValueEls = document.querySelectorAll(`.food-total-value`);
      const priceStatusEl = document.querySelector(`[data-food-total-id="${item.id}"].availability-status`);
      if (item.pricing_model === 'per_person' && gc > 0) {
        const total = Number(item.price) * gc;
        totalDisplays.forEach(el => el.style.display = '');
        document.querySelectorAll(`.food-total-display[data-food-total-id="${item.id}"] .food-total-value`).forEach(el => {
          el.textContent = money(total);
        });
      } else {
        totalDisplays.forEach(el => el.style.display = 'none');
      }
    });
    // Update sidebar total for selected item
    updateSidebarTotal();
  }

  function updateSidebarTotal() {
    const checked = document.querySelector('input[name="food_item"]:checked:not(:disabled)');
    if (!checked) return;
    const gc = getGuestCount();
    const pricingModel = checked.dataset.pricingModel;
    const basePrice = Number(checked.dataset.foodPrice);
    const displayPrice = (pricingModel === 'per_person' && gc > 0) ? basePrice * gc : basePrice;
    if (sidebarEstimatedTotal) sidebarEstimatedTotal.textContent = money(displayPrice);
    if (selectedFoodItemEl) {
      const name = checked.dataset.foodName;
      selectedFoodItemEl.textContent = gc > 0 && pricingModel === 'per_person'
        ? name + ' (' + gc + ' guests)'
        : name;
    }
  }

  // Handle guest count input
  if (guestCountInput) {
    guestCountInput.addEventListener('input', function() {
      currentGuestCount = parseInt(this.value) || 0;
      if (cartGuestCount) cartGuestCount.value = currentGuestCount > 0 ? currentGuestCount : '';
      updateFoodTotals();
    });
  }

  // Handle food item radio selection
  document.querySelectorAll('input[name="food_item"]').forEach(radio => {
    radio.addEventListener('change', function() {
      if (this.disabled) return;
      const foodId = Number(this.dataset.foodId);
      const foodName = this.dataset.foodName;
      const foodPrice = Number(this.dataset.foodPrice);
      const pricingModel = this.dataset.pricingModel;

      selectedFoodId = foodId;
      if (cartCakeDesignId) cartCakeDesignId.value = foodId;

      // Update row visual state
      document.querySelectorAll('[data-food-row]').forEach(row => {
        row.classList.remove('is-selected');
      });
      const activeRow = document.querySelector(`[data-food-row][data-food-id="${foodId}"]`);
      if (activeRow) activeRow.classList.add('is-selected');

      // Update sidebar
      const gc = getGuestCount();
      const displayPrice = (pricingModel === 'per_person' && gc > 0) ? foodPrice * gc : foodPrice;
      if (selectedFoodItemEl) {
        selectedFoodItemEl.textContent = gc > 0 && pricingModel === 'per_person'
          ? foodName + ' (' + gc + ' guests)'
          : foodName;
      }
      if (sidebarEstimatedTotal) sidebarEstimatedTotal.textContent = money(displayPrice);
    });
  });

  // Auto-select first available food item
  const firstRadio = document.querySelector('input[name="food_item"]:not(:disabled)');
  if (firstRadio) {
    firstRadio.checked = true;
    firstRadio.dispatchEvent(new Event('change'));
  }

  // ── Listen for guest count changes from venue hall selection ──
  window.addEventListener('gp:guestCountChanged', function(e) {
    currentGuestCount = e.detail.guestCount || 0;
    if (guestCountInput) guestCountInput.value = currentGuestCount > 0 ? currentGuestCount : '';
    if (cartGuestCount) cartGuestCount.value = currentGuestCount > 0 ? currentGuestCount : '';
    updateFoodTotals();
  });

  // ── Restore guest booking state for food ──
  var savedState = null;
  try { var raw = sessionStorage.getItem('gpGuestBookingState'); if (raw) savedState = JSON.parse(raw); } catch(e) {}
  if (savedState && Number(savedState.serviceId) === <?= (int)($service['id'] ?? 0) ?> && Date.now() - (savedState.savedAt || 0) < 30 * 60 * 1000) {
    if (savedState.guest_count) {
      currentGuestCount = Number(savedState.guest_count) || 0;
      if (guestCountInput) guestCountInput.value = currentGuestCount > 0 ? currentGuestCount : '';
      if (cartGuestCount) cartGuestCount.value = currentGuestCount > 0 ? currentGuestCount : '';
    }
    if (savedState.cake_design_id || savedState.foodItemId) {
      var foodId = savedState.cake_design_id || savedState.foodItemId;
      var foodRadio = document.querySelector('input[name="food_item"][data-food-id="' + foodId + '"]');
      if (foodRadio && !foodRadio.disabled) {
        foodRadio.checked = true;
        foodRadio.dispatchEvent(new Event('change'));
      }
    }
    updateFoodTotals();
  }

  // Cart form validation
  if (serviceCartForm) {
    serviceCartForm.addEventListener('submit', function(e) {
      if (!cartCakeDesignId || !cartCakeDesignId.value) {
        e.preventDefault();
        var section = document.querySelector('.booking-section');
        if (section) section.scrollIntoView({ behavior: 'smooth' });
      }
    });
  }
})();
</script>
<?php endif; ?>

<?php include APPROOT . '/views/partials/cookie-consent.php'; ?>
</body>
</html>
