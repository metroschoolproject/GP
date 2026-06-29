<?php
$supplier = $supplier ?? [];
$service = $service ?? [];
$media = is_array($service['media'] ?? null) ? $service['media'] : [];
$availability = is_array($service['availability'] ?? null) ? $service['availability'] : [];
$weeklyRows = is_array($availability['weekly'] ?? null) ? $availability['weekly'] : [];
$overrideRows = is_array($availability['overrides'] ?? null) ? $availability['overrides'] : [];
$venueRooms = is_array($service['venue_rooms'] ?? null) ? $service['venue_rooms'] : [];
$decorationStyles = is_array($service['decoration_styles'] ?? null) ? $service['decoration_styles'] : [];
$foodItems = is_array($service['food_items'] ?? null) ? $service['food_items'] : [];
$attireItems = is_array($service['attire_items'] ?? null) ? $service['attire_items'] : [];

$dashboardTitle = 'Supplier';
$dashboardCrumb = 'Service Detail';
$dashboardSearchPlaceholder = 'Search services, packages...';
$dashboardContentClass = 'bg-app-content px-6 py-6';
$serviceId = (int)($service['id'] ?? 0);
$serviceNameRaw = $service['name'] ?? 'Service detail';
$dashboardBreadcrumbs = [
    ['label' => 'Dashboard', 'url' => URLROOT . '/supplier/dashboard'],
    ['label' => 'Services', 'url' => URLROOT . '/supplier/services'],
    ['label' => $serviceNameRaw, 'url' => null],
];

$h = function ($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
};
$money = function ($value) {
    return number_format((float)$value, 0) . ' MMK';
};
$durationLabel = function ($minutes) {
    $minutes = max(0, (int)$minutes);

    if ($minutes >= 720) {
        return 'Full day';
    }

    if ($minutes >= 60) {
        $hours = $minutes / 60;
        return rtrim(rtrim(number_format($hours, 1), '0'), '.') . ' hour' . ($hours == 1.0 ? '' : 's');
    }

    return $minutes . ' minutes';
};
$formatTime = function ($value) {
    $timestamp = strtotime((string)$value);
    return $timestamp ? date('g:i A', $timestamp) : (string)$value;
};
$formatDate = function ($value) {
    $timestamp = strtotime((string)$value);
    return $timestamp ? date('j M Y', $timestamp) : (string)$value;
};

$weeklyByDay = [];
foreach ($weeklyRows as $row) {
    $weeklyByDay[(int)($row['day_of_week'] ?? 0)] = $row;
}

$days = [
    1 => 'Monday',
    2 => 'Tuesday',
    3 => 'Wednesday',
    4 => 'Thursday',
    5 => 'Friday',
    6 => 'Saturday',
    7 => 'Sunday',
];
$defaultOpenDays = [1, 2, 3, 4, 5];
$isDayAvailable = function ($dayNumber, $row) use ($defaultOpenDays) {
    return array_key_exists('is_available', $row)
        ? !empty($row['is_available'])
        : in_array($dayNumber, $defaultOpenDays, true);
};

$openDaysCount = 0;
foreach ($days as $dayNumber => $dayName) {
    $row = $weeklyByDay[$dayNumber] ?? null;
    $openTime = is_array($row) ? strtotime((string)($row['open_time'] ?? '')) : false;
    $closeTime = is_array($row) ? strtotime((string)($row['close_time'] ?? '')) : false;
    if (is_array($row) && !empty($row['is_available']) && $openTime && $closeTime && $openTime < $closeTime) {
        $openDaysCount++;
    }
}
$hasSavedOpenSchedule = false;
foreach ($weeklyRows as $row) {
    if (empty($row['is_available'])) {
        continue;
    }

    $openTime = strtotime((string)($row['open_time'] ?? ''));
    $closeTime = strtotime((string)($row['close_time'] ?? ''));

    if ($openTime && $closeTime && $openTime < $closeTime) {
        $hasSavedOpenSchedule = true;
        break;
    }
}

$serviceCategoryRaw = $service['category'] ?? 'Others';
$isVenue = strtolower((string)$serviceCategoryRaw) === 'venue';
$isRental = in_array(strtolower((string)$serviceCategoryRaw), ['attire'], true);
$serviceCategorySlug = strtolower(trim((string)($service['category_slug'] ?? '')));
$slotCategories = defined('SLOT_BOOKING_CATEGORIES') ? SLOT_BOOKING_CATEGORIES : ['venue'];
$isSlotBooking = in_array($serviceCategorySlug, $slotCategories, true);
$rentalPricing = is_array($service['rental_pricing'] ?? null) ? $service['rental_pricing'] : [];
$serviceDescriptionRaw = trim((string)($service['desc'] ?? $service['description'] ?? ''));
$servicePackagePrice = (float)($service['price_min'] ?? $service['package_price'] ?? $service['price'] ?? 0);
$serviceCustomizePrice = (float)($service['price_max'] ?? $service['customize_price'] ?? $servicePackagePrice);
$servicePriceAmount = $servicePackagePrice > 0 ? $servicePackagePrice : (float)($service['price'] ?? 0);
$serviceStatus = ($service['status'] ?? 'active') === 'inactive' ? 'inactive' : 'active';
$serviceImage = trim((string)($service['img'] ?? ''));
if ($serviceImage === '' && !empty($media[0]['file_url'])) {
    $serviceImage = (string)$media[0]['file_url'];
}
$mediaCount = count($media);
$slotDuration = (int)($availability['duration_minutes'] ?? $service['duration_minutes'] ?? 60);
$bufferMinutes = (int)($availability['buffer_minutes'] ?? $service['buffer_minutes'] ?? 0);
$maxConcurrent = (int)($availability['max_concurrent'] ?? $service['capacity'] ?? 1);
$maxConcurrentPackage = (int)($availability['max_concurrent_package'] ?? $service['max_concurrent_package'] ?? 0);
$maxConcurrentCustomize = (int)($availability['max_concurrent_customize'] ?? $service['max_concurrent_customize'] ?? 0);
$overrideCount = count($overrideRows);

$attentionItems = [];
if ($servicePriceAmount <= 0) {
    $attentionItems[] = ['label' => 'Check pricing', 'detail' => 'Add a customer-facing starting price.', 'icon' => 'ti ti-cash'];
}
if ($serviceDescriptionRaw === '') {
    $attentionItems[] = ['label' => 'Write a description', 'detail' => 'Tell customers what they are getting.', 'icon' => 'ti ti-file-description'];
}
if ($mediaCount === 0) {
    $attentionItems[] = ['label' => 'Upload portfolio photos', 'detail' => 'Add at least one image customers will see.', 'icon' => 'ti ti-camera-plus'];
}
if (!$isVenue && !$hasSavedOpenSchedule) {
    $attentionItems[] = ['label' => 'Set weekly availability', 'detail' => 'Open at least one day so slots can be booked.', 'icon' => 'ti ti-calendar-check'];
}
if ($isVenue && count($venueRooms) === 0) {
    $attentionItems[] = ['label' => 'Add halls', 'detail' => 'Add at least one room or hall for this venue.', 'icon' => 'ti ti-door'];
}
$isReady = empty($attentionItems);

$mediaCreateUrl = URLROOT . '/supplier/serviceMediaCreate/' . $serviceId;
$mediaDeleteUrl = URLROOT . '/supplier/serviceMediaDelete/' . $serviceId . '/';
$serviceUpdateUrl = URLROOT . '/supplierServices/serviceUpdate/' . $serviceId;
$serviceStatusUrl = URLROOT . '/supplierServices/serviceStatus/' . $serviceId;
$publishRequestUrl = URLROOT . '/supplier/servicePublishRequest/' . $serviceId;
$publishStatusUrl = URLROOT . '/supplier/servicePublishStatus/' . $serviceId;
$availabilitySaveUrl = URLROOT . '/supplier/serviceAvailabilitySave/' . $serviceId;
$overrideSaveUrl = URLROOT . '/supplier/serviceAvailabilityOverrideSave/' . $serviceId;
$overrideDeleteUrl = URLROOT . '/supplier/serviceAvailabilityOverrideDelete/' . $serviceId . '/';
$serviceManageUrl = URLROOT . '/supplier/services';

$dashboardContent = function () use ($service, $serviceId, $serviceNameRaw, $serviceCategoryRaw, $serviceCategorySlug, $serviceDescriptionRaw, $servicePriceAmount, $servicePackagePrice, $serviceCustomizePrice, $serviceStatus, $serviceImage, $media, $mediaCount, $availability, $weeklyByDay, $overrideRows, $venueRooms, $decorationStyles, $foodItems, $attireItems, $openDaysCount, $slotDuration, $bufferMinutes, $maxConcurrent, $maxConcurrentPackage, $maxConcurrentCustomize, $overrideCount, $attentionItems, $isReady, $isVenue, $isRental, $isSlotBooking, $rentalPricing, $days, $isDayAvailable, $h, $money, $durationLabel, $formatTime, $formatDate, $mediaCreateUrl, $mediaDeleteUrl, $serviceUpdateUrl, $serviceStatusUrl, $publishRequestUrl, $publishStatusUrl, $availabilitySaveUrl, $overrideSaveUrl, $overrideDeleteUrl, $serviceManageUrl) {
?>
<?php
$serviceDetailCssVersion = file_exists(APPROOT . '/../public/css/supplier-service-detail.css') ? filemtime(APPROOT . '/../public/css/supplier-service-detail.css') : time();
$serviceDetailJsVersion = file_exists(APPROOT . '/../public/js/supplier-service-detail.js') ? filemtime(APPROOT . '/../public/js/supplier-service-detail.js') : time();
$serviceDetailConfig = [
    'urls' => [
        'mediaCreate' => $mediaCreateUrl,
        'mediaDelete' => $mediaDeleteUrl,
        'serviceUpdate' => $serviceUpdateUrl,
        'serviceStatus' => $serviceStatusUrl,
        'publishRequest' => $publishRequestUrl,
        'publishStatus' => $publishStatusUrl,
        'availabilitySave' => $availabilitySaveUrl,
        'overrideSave' => $overrideSaveUrl,
        'overrideDelete' => $overrideDeleteUrl,
        'serviceManage' => $serviceManageUrl,
    ],
    'servicePayloadBase' => [
        'name' => $serviceNameRaw,
        'desc' => $serviceDescriptionRaw,
        'price' => $servicePriceAmount,
        'price_min' => (float)($service['price_min'] ?? $servicePriceAmount),
        'price_max' => (float)($service['price_max'] ?? $servicePriceAmount),
        'category' => $serviceCategoryRaw,
        'status' => $serviceStatus,
        'img' => $serviceImage,
        'capacity' => (int)($service['capacity'] ?? $maxConcurrent),
        'venue' => $service['venue_name'] ?? $service['venue'] ?? '',
        'venue_location' => $service['venue_location'] ?? '',
        'rental_pricing' => $rentalPricing ?: null,
    ],
    'decorationStyles' => $decorationStyles,
    'foodItems' => $foodItems,
    'attireItems' => $attireItems,
];
?>
<link rel="stylesheet" href="<?= URLROOT ?>/public/css/supplier-service-detail.css?v=<?= $serviceDetailCssVersion ?>">
<script>window.serviceDetailConfig = <?= json_encode($serviceDetailConfig, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;</script>
<?php require APPROOT . '/views/supplier/partials/service_detail_content.php'; ?>
<script src="<?= URLROOT ?>/public/js/supplier-service-detail.js?v=<?= $serviceDetailJsVersion ?>" defer></script>

<?php
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php require_once APPROOT . '/views/dashboardLayout/head.php'; ?>
</head>
<body class="grid h-screen gap-0 bg-app-page" style="grid-template-columns: 280px 1fr;">
    <?php require APPROOT . '/views/dashboardLayout/suppliersidebar.php'; ?>
</body>
</html>
