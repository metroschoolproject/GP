<?php
$service = $service ?? [];
$media = $service['media'] ?? [];
$availability = $service['availability'] ?? ['weekly' => [], 'overrides' => [], 'upcoming' => []];
$fallbackImage = IMG_ROOT . '/uploads/suppliers/20/service-management/service/20260610150543-6e1176d1.jpg';
$heroImage = trim((string)($media[0]['file_url'] ?? $service['image'] ?? '')) ?: $fallbackImage;
$upcoming = $availability['upcoming'] ?? [];
$venueRooms = $service['venue_rooms'] ?? [];
$isVenue = ($service['category'] ?? '') === 'Venue';
$reviews = $service['reviews'] ?? [];
$related = $service['related'] ?? [];
$rating = (float)($service['rating'] ?? 0);
$reviewCount = (int)($service['review_count'] ?? count($reviews));
$firstAvailable = $upcoming[0] ?? null;
$firstSlot = $isVenue && !empty($venueRooms) ? null : ($firstAvailable['slots'][0] ?? null);
$firstVenueRoom = $venueRooms[0] ?? null;
$venueCapacity = !empty($venueRooms) ? max(array_map(function ($room) {
    return (int)($room['capacity'] ?? 1);
}, $venueRooms)) : (int)($service['max_concurrent'] ?? 1);
$isLoggedIn = !empty($_SESSION['session_uid']);
$authNavUrl = $isLoggedIn ? URLROOT . '/users/logout' : URLROOT . '/users/auth';
$authNavLabel = $isLoggedIn ? 'Logout' : 'Sign in';

$h = function ($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
};

$money = function ($value) {
    return 'RM ' . number_format((float)$value, 0);
};

$moneyRange = function ($service) use ($money) {
    $min = (float)($service['price_min'] ?? $service['price'] ?? 0);
    $max = (float)($service['price_max'] ?? $min);

    return $max > $min ? $money($min) . ' - ' . $money($max) : $money($min);
};

$dateRange = function ($from, $to) {
    $from = trim((string)$from);
    $to = trim((string)$to);

    if ($from === '' && $to === '') {
        return 'Dates not set';
    }

    if ($from === '' || $from === $to) {
        return date('M j, Y', strtotime($to ?: $from));
    }

    if ($to === '') {
        return 'From ' . date('M j, Y', strtotime($from));
    }

    return date('M j, Y', strtotime($from)) . ' - ' . date('M j, Y', strtotime($to));
};

$timeRange = function ($from, $to) {
    $from = trim((string)$from);
    $to = trim((string)$to);

    if ($from === '' && $to === '') {
        return 'Hours not set';
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

$durationText = function ($service) {
    $bookingType = $service['booking_type'] ?? 'fullday';
    $minutes = (int)($service['duration_minutes'] ?? 0);

    if ($bookingType === 'slot' && $minutes > 0) {
        $hours = $minutes / 60;
        return $hours >= 1 ? rtrim(rtrim(number_format($hours, 1), '0'), '.') . ' hour reservation' : $minutes . ' minute reservation';
    }

    return $bookingType === 'flexible' ? 'Flexible reservation' : 'Full day reservation';
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $h($service['name'] ?? 'Service') ?> | <?= APPNAME ?></title>
    <?php $appCssVersion = file_exists(APPROOT . '/../public/css/app.css') ? filemtime(APPROOT . '/../public/css/app.css') : time(); ?>
    <link rel="stylesheet" href="<?= URLROOT ?>/public/css/app.css?v=<?= $appCssVersion ?>">
    <style>
        @import url("https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800;900&family=Playfair+Display:wght@400;500;600;700&display=swap");

        :root {
            --page: #F5E8D9;
            --panel: #FFF8EF;
            --panel-strong: #FFFFFF;
            --ink: #211D1A;
            --muted: #6F625A;
            --line: rgba(118, 90, 70, 0.16);
            --wine: #B94A48;
            --wine-dark: #7F2F2D;
            --gold: #D8B46A;
            --sage: #765A46;
            --soft: #F8F2EC;
            --shadow: 0 24px 58px rgba(74, 52, 47, 0.16);
            --radius: 8px;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            background: var(--page);
            color: var(--ink);
            font-family: "DM Sans", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        a { color: inherit; text-decoration: none; }

        .detail-shell {
            width: min(1180px, calc(100% - 36px));
            margin: 28px auto 60px;
        }

        .detail-nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 24px;
        }

        .brand {
            color: var(--wine-dark);
            font-family: "Playfair Display", serif;
            font-size: 26px;
            font-weight: 700;
        }

        .back-link,
        .nav-pill {
            min-height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--line);
            border-radius: 999px;
            background: rgba(255,250,247,0.8);
            color: var(--muted);
            padding: 0 14px;
            font-size: 13px;
            font-weight: 800;
        }

        .page-kicker {
            margin: 0 0 18px;
            color: var(--sage);
            font-size: 12px;
            font-weight: 900;
            letter-spacing: 0.18em;
            text-transform: uppercase;
        }

        .top-grid {
            display: grid;
            grid-template-columns: minmax(0, 590px) minmax(320px, 450px);
            gap: 28px;
            align-items: start;
        }

        .service-card,
        .summary-card,
        .section-card,
        .related-card {
            border: 1px solid var(--line);
            border-radius: var(--radius);
            background: rgba(255,248,239,0.94);
            box-shadow: var(--shadow);
        }

        .service-card {
            padding: 22px;
        }

        .hero-image {
            position: relative;
            height: 330px;
            overflow: hidden;
            border-radius: var(--radius);
            background: var(--soft);
        }

        .hero-image img,
        .related-image img {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: cover;
        }

        .category-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 18px;
            color: var(--muted);
            font-size: 12px;
            font-weight: 800;
        }

        .tag,
        .type-chip,
        .related-cat {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            background: rgba(185, 74, 72, 0.10);
            color: var(--wine);
            font-size: 11px;
            font-weight: 900;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .tag { min-height: 24px; padding: 0 11px; }

        .service-main-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 150px;
            gap: 22px;
            align-items: stretch;
            margin-top: 20px;
        }

        .service-name-box,
        .price-box {
            min-height: 72px;
            display: flex;
            align-items: center;
            border-radius: var(--radius);
            background: var(--panel-strong);
            padding: 14px 16px;
        }

        .service-name-box {
            justify-content: flex-start;
            color: var(--wine-dark);
            font-family: "Playfair Display", serif;
            font-size: clamp(24px, 3vw, 36px);
            font-weight: 700;
            line-height: 1.05;
        }

        .price-box {
            justify-content: center;
            color: var(--wine-dark);
            font-size: 25px;
            font-weight: 900;
        }

        .rating-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 18px;
            color: var(--ink);
            font-size: 15px;
            font-weight: 800;
        }

        .rating-row span:first-child {
            color: var(--gold);
            font-size: 20px;
        }

        .info-strip {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            margin: 20px auto 0;
            overflow: hidden;
            border: 1px solid var(--line);
            border-radius: var(--radius);
            background: var(--panel-strong);
        }

        .info-strip div {
            min-height: 72px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 6px;
            border-right: 1px solid var(--line);
            padding: 10px;
            text-align: center;
            color: var(--muted);
            font-size: 11px;
            font-weight: 800;
        }

        .info-strip div:last-child { border-right: 0; }

        .info-strip strong {
            color: var(--ink);
            font-size: 12px;
            font-weight: 900;
        }

        .type-chip {
            min-height: 26px;
            margin-top: 18px;
            padding: 0 12px;
        }

        .summary-card {
            position: sticky;
            top: 24px;
            padding: 18px;
        }

        .summary-fields {
            border-radius: var(--radius);
            background: var(--soft);
            padding: 22px;
        }

        .summary-line {
            display: grid;
            gap: 5px;
            padding: 13px 0;
            border-bottom: 1px solid rgba(118,90,70,0.14);
            font-size: 12px;
            font-weight: 900;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--muted);
        }

        .summary-line:first-child { padding-top: 0; }
        .summary-line:last-child { border-bottom: 0; }

        .summary-line span {
            color: var(--ink);
            font-size: 14px;
            font-weight: 800;
            letter-spacing: 0;
            text-transform: none;
        }

        .estimated {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 22px 0 14px;
            color: var(--muted);
            font-size: 13px;
            font-weight: 900;
        }

        .estimated strong {
            color: var(--wine-dark);
            font-size: 22px;
        }

        .summary-actions {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .add-cart,
        .heart {
            min-height: 46px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 0;
            border-radius: 999px;
            cursor: pointer;
            font: inherit;
            font-weight: 900;
        }

        .add-cart {
            flex: 1;
            gap: 9px;
            background: var(--wine);
            color: #fffaf7;
            padding: 0 18px;
        }

        .heart {
            width: 48px;
            flex: 0 0 48px;
            background: rgba(185,74,72,0.10);
            color: var(--wine);
            font-size: 22px;
        }

        .content-stack {
            width: min(590px, 100%);
            margin-top: 28px;
        }

        .section-card {
            margin-bottom: 24px;
            padding: 20px;
        }

        .section-title {
            margin: 0 0 18px;
            color: var(--wine-dark);
            font-family: "Playfair Display", serif;
            font-size: 26px;
            line-height: 1.05;
        }

        .portfolio-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        .portfolio-image {
            display: block;
            aspect-ratio: 4 / 3;
            overflow: hidden;
            border: 1px solid rgba(118,90,70,0.14);
            border-radius: var(--radius);
            background: var(--soft);
        }

        .portfolio-image:first-child {
            grid-column: span 2;
            grid-row: span 2;
        }

        .portfolio-image img {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: cover;
            transition: transform 0.2s ease;
        }

        .portfolio-image:hover img {
            transform: scale(1.04);
        }

        .availability-list {
            display: grid;
            gap: 14px;
        }

        .availability-row {
            min-height: 76px;
            display: grid;
            grid-template-columns: 32px minmax(0, 1fr);
            gap: 14px;
            align-items: start;
            border: 1px solid rgba(118,90,70,0.14);
            border-radius: var(--radius);
            background: var(--panel-strong);
            padding: 14px 16px;
            transition: border-color 0.16s ease, transform 0.16s ease, box-shadow 0.16s ease;
        }

        .availability-row:hover,
        .availability-row.is-selected {
            border-color: rgba(185,74,72,0.34);
            box-shadow: 0 12px 26px rgba(74, 52, 47, 0.10);
            transform: translateY(-1px);
        }

        .slot-radio {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .radio-dot {
            width: 20px;
            height: 20px;
            border: 2px solid rgba(185,74,72,0.26);
            border-radius: 999px;
            background: #fff;
            margin-top: 5px;
        }

        .availability-row.is-selected .radio-dot {
            border: 6px solid var(--wine);
        }

        .availability-name {
            display: grid;
            gap: 4px;
            color: var(--ink);
            font-size: 15px;
            font-weight: 900;
        }

        .availability-name span {
            color: var(--muted);
            font-size: 12px;
            font-weight: 700;
        }

        .availability-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
        }

        .availability-status {
            border-radius: 999px;
            background: rgba(216,180,106,0.22);
            color: #765A46;
            padding: 7px 10px;
            font-size: 12px;
            font-weight: 900;
            white-space: nowrap;
        }

        .slot-options {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 12px;
        }

        .slot-chip {
            min-height: 34px;
            display: inline-flex;
            align-items: center;
            border: 1px solid rgba(185,74,72,0.18);
            border-radius: 999px;
            background: rgba(185,74,72,0.08);
            color: var(--wine);
            padding: 0 11px;
            font-size: 12px;
            font-weight: 900;
            cursor: pointer;
        }

        .slot-chip input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .slot-chip:has(input:checked) {
            background: var(--wine);
            border-color: var(--wine);
            color: #fffaf7;
        }

        .rating-summary {
            display: grid;
            grid-template-columns: 150px minmax(0, 1fr);
            gap: 18px;
            border-radius: var(--radius);
            background: var(--panel-strong);
            padding: 16px;
        }

        .rating-number {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--wine-dark);
            font-size: 22px;
            font-weight: 900;
        }

        .rating-number span { color: var(--gold); }

        .rating-bars {
            display: grid;
            gap: 7px;
            font-size: 11px;
            color: var(--muted);
            font-weight: 800;
        }

        .bar-row {
            display: grid;
            grid-template-columns: 46px minmax(80px, 1fr) 24px;
            gap: 8px;
            align-items: center;
        }

        .bar-track {
            height: 5px;
            overflow: hidden;
            border-radius: 999px;
            background: rgba(118,90,70,0.18);
        }

        .bar-fill {
            height: 100%;
            display: block;
            border-radius: inherit;
            background: var(--gold);
        }

        .review-list {
            display: grid;
            gap: 10px;
            margin-top: 16px;
        }

        .review-item,
        .review-placeholder {
            border: 1px solid rgba(118,90,70,0.12);
            border-radius: var(--radius);
            background: var(--panel-strong);
            padding: 13px 15px;
        }

        .review-item {
            display: grid;
            grid-template-columns: 38px minmax(0, 1fr) auto;
            gap: 12px;
            align-items: start;
        }

        .avatar {
            width: 32px;
            height: 32px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            background: var(--soft);
            color: var(--wine);
            font-size: 11px;
            font-weight: 900;
        }

        .review-copy strong {
            display: block;
            color: var(--ink);
            font-size: 13px;
        }

        .review-copy span {
            display: block;
            color: var(--muted);
            font-size: 11px;
            margin-top: 2px;
        }

        .review-copy p {
            margin: 6px 0 0;
            color: var(--muted);
            font-size: 12px;
            line-height: 1.45;
        }

        .review-placeholder {
            min-height: 44px;
            background: var(--soft);
        }

        .related-section {
            margin-top: 44px;
            padding: 0 20px 20px;
        }

        .related-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(230px, 1fr));
            gap: clamp(24px, 9vw, 140px);
            margin-top: 28px;
            padding: 0 34px;
        }

        .related-card {
            overflow: hidden;
            padding: 18px;
        }

        .related-image {
            display: block;
            height: 190px;
            overflow: hidden;
            border-radius: var(--radius);
            background: var(--soft);
        }

        .related-cat {
            min-height: 20px;
            margin-top: 14px;
            padding: 0 9px;
            font-size: 9px;
        }

        .related-meta {
            display: flex;
            justify-content: space-between;
            gap: 14px;
            margin-top: 12px;
            color: var(--ink);
            font-size: 14px;
            font-weight: 900;
        }

        .related-meta span:first-child {
            min-width: 0;
        }

        .related-price {
            margin-top: 8px;
            color: var(--muted);
            font-size: 12px;
            font-weight: 800;
        }

        .view-detail {
            display: flex;
            width: 132px;
            min-height: 32px;
            align-items: center;
            justify-content: center;
            margin: 18px auto 0;
            border-radius: 999px;
            background: rgba(185,74,72,0.10);
            color: var(--wine);
            font-size: 11px;
            font-weight: 900;
        }

        .floating-cart {
            position: fixed;
            right: clamp(22px, 7vw, 96px);
            bottom: clamp(22px, 10vw, 150px);
            z-index: 20;
            width: 64px;
            height: 64px;
            display: grid;
            place-items: center;
            border: 1px solid var(--line);
            border-radius: 20px;
            background: var(--panel-strong);
            box-shadow: 0 16px 40px rgba(74, 52, 47, 0.18);
            color: var(--wine);
            font-size: 26px;
        }

        .empty-row {
            border: 1px dashed rgba(185,74,72,0.32);
            border-radius: var(--radius);
            background: rgba(255,248,239,0.78);
            color: var(--muted);
            padding: 18px;
            font-size: 13px;
            font-weight: 700;
            text-align: center;
        }

        @media (max-width: 920px) {
            .detail-shell {
                width: min(100%, calc(100% - 20px));
                margin: 16px auto 40px;
            }

            .top-grid {
                grid-template-columns: 1fr;
            }

            .summary-card {
                position: static;
            }

            .content-stack {
                width: 100%;
            }

            .related-grid {
                grid-template-columns: 1fr;
                gap: 22px;
                padding: 0;
            }
        }

        @media (max-width: 580px) {
            .detail-nav,
            .service-main-row,
            .info-strip,
            .rating-summary,
            .availability-row,
            .portfolio-grid {
                grid-template-columns: 1fr;
            }

            .portfolio-image:first-child {
                grid-column: auto;
                grid-row: auto;
            }

            .detail-nav {
                display: grid;
            }

            .hero-image {
                height: 240px;
            }

            .availability-status {
                width: max-content;
            }

            .floating-cart {
                width: 54px;
                height: 54px;
                border-radius: 16px;
            }
        }
    </style>
</head>
<body>
<main class="detail-shell">
    <nav class="detail-nav" aria-label="Service detail navigation">
        <a class="brand" href="<?= URLROOT ?>/main/home">Golden Promise</a>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <a class="back-link" href="<?= URLROOT ?>/customerServices/service">Back to services</a>
            <a class="nav-pill" href="<?= $authNavUrl ?>"><?= $authNavLabel ?></a>
        </div>
    </nav>

    <p class="page-kicker">Customer Service Detail</p>

    <section class="top-grid">
        <article class="service-card">
            <div class="hero-image">
                <img src="<?= $h($heroImage) ?>" alt="<?= $h($service['name'] ?? 'Service image') ?>">
            </div>

            <div class="category-row">
                <span>Category</span>
                <span class="tag"><?= $h($service['category'] ?? 'Service') ?></span>
            </div>

            <div class="service-main-row">
                <div class="service-name-box"><?= $h($service['name'] ?? '') ?></div>
                <div class="price-box"><?= $moneyRange($service) ?></div>
            </div>

            <div class="rating-row">
                <span>&#9733;</span>
                <strong><?= number_format((float)$rating, 1) ?></strong>
                <span style="color:var(--muted);font-size:13px;"><?= (int)$reviewCount ?> review<?= $reviewCount === 1 ? '' : 's' ?></span>
            </div>

            <div class="info-strip">
                <div>
                    <span>Category</span>
                    <strong><?= $h($service['category'] ?? '') ?></strong>
                </div>
                <div>
                    <span>Booking type</span>
                    <strong><?= $h($durationText($service)) ?></strong>
                </div>
                <div>
                    <span>Capacity</span>
                    <strong><?= $isVenue ? (int)$venueCapacity : (int)($service['max_concurrent'] ?? 1) ?></strong>
                </div>
            </div>

            <span class="type-chip"><?= $h(($service['booking_type'] ?? 'fullday') === 'slot' ? 'Scheduled slots' : 'Full service') ?></span>
        </article>

        <aside class="summary-card">
            <div class="summary-fields">
                <div class="summary-line">
                    <?= $isVenue ? 'Selected hall' : 'Destination date' ?>
                    <span id="selectedDate"><?= $h($isVenue ? ($firstVenueRoom['name'] ?? 'Choose a hall') : ($firstAvailable['day_label'] ?? 'Choose an available date')) ?></span>
                </div>
                <div class="summary-line">
                    Service type
                    <span><?= $h($service['category'] ?? 'Service') ?></span>
                </div>
                <div class="summary-line">
                    Supplier
                    <span><?= $h($service['supplier_name'] ?? 'Supplier') ?></span>
                </div>
                <?php if ($isVenue): ?>
                    <div class="summary-line">
                        Capacity
                        <span id="selectedTime"><?= $firstVenueRoom ? (int)($firstVenueRoom['capacity'] ?? 1) . ' guests' : 'Choose a hall' ?></span>
                    </div>
                <?php elseif ($firstSlot): ?>
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

            <div class="estimated">
                <span>Estimated Total</span>
                <strong><?= $isVenue && $firstVenueRoom ? $money($firstVenueRoom['price'] ?? 0) : $moneyRange($service) ?></strong>
            </div>

            <div class="summary-actions">
                <a class="add-cart" id="addCartLink" href="<?= URLROOT ?>/users/auth">Add to cart <span>&#128722;</span></a>
                <a class="heart" href="<?= URLROOT ?>/users/auth" aria-label="Save service">&#9829;</a>
            </div>
        </aside>
    </section>

    <section class="content-stack">
        <div class="section-card">
            <h2 class="section-title">Portfolio photos</h2>
            <?php if (empty($media)): ?>
                <div class="empty-row">No portfolio photos have been published for this service yet.</div>
            <?php else: ?>
                <div class="portfolio-grid">
                    <?php foreach (array_slice($media, 0, 7) as $item): ?>
                        <?php $imageUrl = trim((string)($item['file_url'] ?? '')); ?>
                        <?php if ($imageUrl === '') continue; ?>
                        <a class="portfolio-image" href="<?= $h($imageUrl) ?>" target="_blank" rel="noopener">
                            <img src="<?= $h($imageUrl) ?>" alt="<?= $h(($service['name'] ?? 'Service') . ' portfolio photo') ?>">
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="section-card">
            <h2 class="section-title"><?= $h($isVenue ? 'Halls' : 'Available Dates & Times') ?></h2>
            <?php if ($isVenue): ?>
                <?php if (empty($venueRooms)): ?>
                    <div class="empty-row">No halls have been published for this venue yet.</div>
                <?php else: ?>
                    <div class="availability-list">
                        <?php foreach ($venueRooms as $index => $room): ?>
                            <?php $checked = $index === 0; ?>
                            <div class="availability-row <?= $checked ? 'is-selected' : '' ?>" data-slot-row>
                                <span class="radio-dot"></span>
                                <div>
                                    <div class="availability-head">
                                        <span class="availability-name">
                                            <?= $h($room['name'] ?: 'Venue hall') ?>
                                            <span>
                                                <?= $h(trim((string)($room['venue_name'] ?? ''))) ?>
                                                <?= !empty($room['venue_location']) ? ' · ' . $h($room['venue_location']) : '' ?>
                                                · <?= $h($timeRange($room['start_time'] ?? '09:00', $room['end_time'] ?? '17:00')) ?>
                                            </span>
                                        </span>
                                        <span class="availability-status"><?= $money($room['price'] ?? 0) ?></span>
                                    </div>
                                    <div class="slot-options">
                                        <label class="slot-chip">
                                            <input
                                                type="radio"
                                                name="service_slot"
                                                value="room|<?= (int)$room['id'] ?>"
                                                data-room-id="<?= (int)$room['id'] ?>"
                                                data-date=""
                                                data-date-label="<?= $h($room['name'] ?: 'Selected hall') ?>"
                                                data-time-label="<?= (int)($room['capacity'] ?? 1) ?> guests"
                                                data-price-label="<?= $money($room['price'] ?? 0) ?>"
                                                <?= $checked ? 'checked' : '' ?>
                                            >
                                            <?= (int)($room['capacity'] ?? 1) ?> guests
                                        </label>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php elseif (empty($upcoming)): ?>
                <div class="empty-row">No available public slots yet. Please check again later.</div>
            <?php else: ?>
                <div class="availability-list">
                    <?php $hasSelectedSlot = false; ?>
                    <?php foreach (array_slice($upcoming, 0, 4) as $index => $day): ?>
                        <?php
                            $dayLabel = $day['day_label'] ?? $day['date'];
                            $slots = $day['slots'] ?? [];
                            $firstDaySlot = $slots[0] ?? null;
                            $slotSummary = count($slots) > 1
                                ? count($slots) . ' time slots'
                                : ($firstDaySlot['label'] ?? ($day['date'] ?? ''));
                            $rowSelected = !$hasSelectedSlot && !empty($slots);
                        ?>
                        <div class="availability-row <?= $rowSelected ? 'is-selected' : '' ?>" data-slot-row>
                            <span class="radio-dot"></span>
                            <div>
                                <div class="availability-head">
                                    <span class="availability-name">
                                        <?= $h($dayLabel) ?>
                                        <span><?= $h($slotSummary) ?></span>
                                    </span>
                                    <span class="availability-status"><?= empty($slots) ? 'Booked' : 'Available' ?></span>
                                </div>
                                <?php if (!empty($slots)): ?>
                                    <div class="slot-options">
                                        <?php foreach ($slots as $slotIndex => $slot): ?>
                                            <?php
                                                $checked = !$hasSelectedSlot && $slotIndex === 0;
                                                if ($checked) {
                                                    $hasSelectedSlot = true;
                                                }
                                            ?>
                                            <label class="slot-chip">
                                                <input
                                                    type="radio"
                                                    name="service_slot"
                                                    value="<?= $h(($day['date'] ?? '') . '|' . ($slot['start_time'] ?? '') . '|' . ($slot['end_time'] ?? '')) ?>"
                                                    data-date="<?= $h($day['date'] ?? '') ?>"
                                                    data-date-label="<?= $h($day['day_label'] ?? $day['date']) ?>"
                                                    data-time-label="<?= $h($slot['label'] ?? '') ?>"
                                                    <?= $checked ? 'checked' : '' ?>
                                                >
                                                <?= $h($slot['label'] ?? '') ?>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="section-card">
            <h2 class="section-title">Reviews & Rating</h2>
            <div class="rating-summary">
                <div class="rating-number"><span>&#9733;</span> <?= number_format((float)$rating, 1) ?></div>
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
                <?php foreach (array_slice($reviews, 0, 3) as $review): ?>
                    <article class="review-item">
                        <div class="avatar">pf</div>
                        <div class="review-copy">
                            <strong>Customer</strong>
                            <span><?= $h(date('Y.m.d', strtotime($review['created_at'] ?? 'now'))) ?></span>
                            <p><?= $h($review['comment'] ?: 'The service is good!') ?></p>
                        </div>
                        <strong style="color:var(--wine-dark);">&#9733; <?= number_format((float)($review['rating'] ?? 0), 1) ?></strong>
                    </article>
                <?php endforeach; ?>

                <?php if (empty($reviews)): ?>
                    <article class="review-item">
                        <div class="avatar">pf</div>
                        <div class="review-copy">
                            <strong>No reviews yet</strong>
                            <span>Be the first customer to review this service.</span>
                            <p>This supplier is currently available for booking.</p>
                        </div>
                        <strong style="color:var(--wine-dark);">&#9733; <?= number_format((float)$rating, 1) ?></strong>
                    </article>
                <?php endif; ?>

                <div class="review-placeholder"></div>
                <div class="review-placeholder"></div>
            </div>
        </div>
    </section>

    <?php if (!empty($related)): ?>
        <section class="related-section">
            <h2 class="section-title">Other related services</h2>
            <div class="related-grid">
                <?php foreach (array_slice($related, 0, 2) as $item): ?>
                    <article class="related-card">
                        <a class="related-image" href="<?= URLROOT ?>/customerServices/detail/<?= (int)$item['id'] ?>">
                            <img src="<?= $h($item['image'] ?: $fallbackImage) ?>" alt="<?= $h($item['name'] ?? 'Related service') ?>">
                        </a>
                        <span class="related-cat"><?= $h($item['category'] ?? 'Service') ?></span>
                        <div class="related-meta">
                            <span><?= $h($item['name'] ?? '') ?></span>
                            <span>&#9733; <?= number_format((float)($item['rating'] ?? 0), 1) ?></span>
                        </div>
                        <div class="related-price"><?= $moneyRange($item) ?></div>
                        <a class="view-detail" href="<?= URLROOT ?>/customerServices/detail/<?= (int)$item['id'] ?>">View detail</a>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <a class="floating-cart" href="<?= URLROOT ?>/users/auth" aria-label="Open cart">&#128722;</a>
</main>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const selectedDate = document.getElementById('selectedDate');
    const selectedTime = document.getElementById('selectedTime');
    const addCartLink = document.getElementById('addCartLink');
    const estimatedTotal = document.querySelector('.estimated strong');
    const authBaseUrl = <?= json_encode(URLROOT . '/users/auth') ?>;
    const serviceId = <?= (int)($service['id'] ?? 0) ?>;

    function updateSelectedSlot(input) {
        if (!input) return;

        document.querySelectorAll('[data-slot-row]').forEach(row => {
            row.classList.toggle('is-selected', row.contains(input));
        });

        if (selectedDate) {
            selectedDate.textContent = input.dataset.dateLabel || 'Selected date';
        }

        if (selectedTime) {
            selectedTime.textContent = input.dataset.timeLabel || 'Selected time';
        }

        if (estimatedTotal && input.dataset.priceLabel) {
            estimatedTotal.textContent = input.dataset.priceLabel;
        }

        if (addCartLink) {
            const params = new URLSearchParams({
                service_id: String(serviceId),
                date: input.dataset.date || '',
                time: input.dataset.timeLabel || ''
            });

            if (input.dataset.roomId) {
                params.set('venue_room_id', input.dataset.roomId);
            }

            addCartLink.href = authBaseUrl + '?' + params.toString();
        }
    }

    document.querySelectorAll("input[name='service_slot']").forEach(input => {
        input.addEventListener('change', () => updateSelectedSlot(input));
    });

    updateSelectedSlot(document.querySelector("input[name='service_slot']:checked"));
});
</script>
</body>
</html>
