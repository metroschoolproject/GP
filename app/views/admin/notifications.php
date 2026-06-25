<?php
$notifications = $notifications ?? [];
$stats = $stats ?? ['total' => 0, 'unread' => 0, 'booking' => 0, 'payment' => 0, 'approval' => 0, 'system' => 0];
$filters = $filters ?? ['type' => 'all', 'state' => 'all', 'search' => ''];
$message = $message ?? '';

$notificationRole = $notificationRole ?? 'admin';
$notificationBaseUrl = URLROOT . '/' . $notificationRole . '/notifications';
$notificationDetailUrl = URLROOT . '/' . $notificationRole . '/notification/';
$notificationMarkAllUrl = URLROOT . '/' . $notificationRole . '/markAllNotificationsRead';
$notificationSidebar = $notificationSidebar ?? APPROOT . '/views/dashboardLayout/adminsidebar.php';
$notificationKicker = $notificationKicker ?? 'Operations inbox';
$notificationSubtitle = $notificationSubtitle ?? 'Review payments, booking changes, supplier approvals, and system updates from one focused queue.';

$dashboardTitle = $notificationRole === 'supplier' ? 'Supplier' : 'Admin';
$dashboardCrumb = 'Notifications';
$dashboardContentClass = 'notification-inbox-shell';

$h = static function ($value) {
    return htmlspecialchars(htmlspecialchars_decode((string)$value, ENT_QUOTES), ENT_QUOTES, 'UTF-8');
};

$notificationHref = static function ($item) use ($notificationDetailUrl) {
    return $notificationDetailUrl . (int)($item['id'] ?? 0);
};

$typeMeta = static function ($type) {
    $meta = [
        'booking' => ['label' => 'Booking', 'icon' => 'calendar-check', 'color' => '#6d4c5b'],
        'payment' => ['label' => 'Payment', 'icon' => 'wallet-cards', 'color' => '#b7792f'],
        'approval' => ['label' => 'Approval', 'icon' => 'badge-check', 'color' => '#4f7c69'],
        'system' => ['label' => 'System', 'icon' => 'shield-check', 'color' => '#66758f'],
    ];

    return $meta[$type] ?? $meta['system'];
};

$actionLabel = static function ($item) {
    $referenceType = strtolower((string)($item['reference_type'] ?? ''));
    $title = strtolower((string)($item['title'] ?? ''));

    if ($referenceType === 'payment') return 'Review payment';
    if ($referenceType === 'supplier') return 'Review supplier';
    if ($referenceType === 'service') return 'Review service';
    if ($referenceType === 'booking' && strpos($title, 'replacement') !== false) return 'Resolve replacement';
    if ($referenceType === 'booking') return 'Open booking';
    return 'View details';
};

$timeAgo = static function ($date) {
    $timestamp = strtotime((string)$date);
    if (!$timestamp) return '';
    $seconds = max(0, time() - $timestamp);
    if ($seconds < 60) return 'Just now';
    if ($seconds < 3600) return floor($seconds / 60) . ' min ago';
    if ($seconds < 86400) return floor($seconds / 3600) . ' hr ago';
    if ($seconds < 172800) return 'Yesterday';
    return date('M j', $timestamp);
};

$groupLabel = static function ($date) {
    $day = date('Y-m-d', strtotime((string)$date));
    if ($day === date('Y-m-d')) return 'Today';
    if ($day === date('Y-m-d', strtotime('-1 day'))) return 'Yesterday';
    return 'Earlier';
};

$queryParams = array_filter([
    'type' => ($filters['type'] ?? 'all') !== 'all' ? $filters['type'] : '',
    'state' => ($filters['state'] ?? 'all') !== 'all' ? $filters['state'] : '',
    'search' => $filters['search'] ?? '',
], static fn($value) => $value !== '');

$tabUrl = static function ($type, $state = 'all') use ($filters, $notificationBaseUrl) {
    $params = [];
    if ($type !== 'all') $params['type'] = $type;
    if ($state !== 'all') $params['state'] = $state;
    if (($filters['search'] ?? '') !== '') $params['search'] = $filters['search'];
    return $notificationBaseUrl . ($params ? '?' . http_build_query($params) : '');
};

$clearSearchParams = array_filter([
    'type' => ($filters['type'] ?? 'all') !== 'all' ? $filters['type'] : '',
    'state' => ($filters['state'] ?? 'all') !== 'all' ? $filters['state'] : '',
], static fn($value) => $value !== '');
$clearSearchUrl = $notificationBaseUrl . ($clearSearchParams ? '?' . http_build_query($clearSearchParams) : '');

$dashboardContent = function () use (
    $notifications,
    $stats,
    $filters,
    $message,
    $h,
    $notificationHref,
    $typeMeta,
    $actionLabel,
    $timeAgo,
    $groupLabel,
    $queryParams,
    $tabUrl,
    $clearSearchUrl,
    $notificationBaseUrl,
    $notificationMarkAllUrl,
    $notificationKicker,
    $notificationSubtitle,
    $currentPage,
    $totalPages,
    $totalCount,
    $perPage
) {
?>
<style>
    .notification-inbox-shell { min-height: 100%; padding: 30px; background: #fbfbf9; }
    .inbox-page { max-width: 1180px; margin: 0 auto; color: #6d4c5b; }
    .inbox-header { display: flex; align-items: flex-end; justify-content: space-between; gap: 20px; margin-bottom: 23px; }
    .inbox-kicker { margin: 0 0 7px; color: #9b7d89; font-size: 10px; font-weight: 800; letter-spacing: .18em; text-transform: uppercase; }
    .inbox-title { margin: 0; font: 650 clamp(30px, 3vw, 42px)/1 "Playfair Display", serif; color: #6d4c5b; }
    .inbox-subtitle { max-width: 620px; margin: 10px 0 0; color: #7b5c69; font-size: 13px; line-height: 1.6; }
    .inbox-unread { display: inline-flex; align-items: center; gap: 9px; min-height: 40px; padding: 0 14px; border: 1px solid #ead8c7; border-radius: 999px; background: #FFFFFF; color: #6d4c5b; font-size: 12px; font-weight: 800; box-shadow: 0 10px 28px rgba(52,35,43,.06); }
    .inbox-unread-dot { width: 8px; height: 8px; border-radius: 50%; background: #b94b4b; box-shadow: 0 0 0 5px rgba(185,75,75,.1); }

    .inbox-toolbar { overflow: hidden; margin-bottom: 18px; border: 1px solid #ead8c7; border-radius: 15px; background: #FFFFFF; box-shadow: 0 18px 45px rgba(52,35,43,.06); }
    .inbox-tabs { display: flex; gap: 3px; overflow-x: auto; padding: 10px 12px; border-bottom: 1px solid #ead8c7; background: #FFFFFF; }
    .inbox-tab { display: inline-flex; min-height: 36px; flex: 0 0 auto; align-items: center; gap: 7px; border-radius: 9px; padding: 0 12px; color: #8e727e; font-size: 11px; font-weight: 700; text-decoration: none; transition: background .15s ease, color .15s ease; }
    .inbox-tab:hover { background: #FFFFFF; color: #6d4c5b; }
    .inbox-tab.active { background: #6d4c5b; color: #FFFFFF; box-shadow: 0 8px 18px rgba(109,76,91,.18); }
    .inbox-tab-count { display: inline-flex; min-width: 20px; height: 20px; align-items: center; justify-content: center; border-radius: 999px; padding: 0 5px; background: rgba(109,76,91,.09); font-size: 9px; }
    .inbox-tab.active .inbox-tab-count { background: rgba(252,248,245,.16); }
    .inbox-tools { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 13px; }
    .inbox-search-form { display: flex; flex: 1; gap: 8px; }
    .inbox-search-wrap { position: relative; width: min(500px, 100%); }
    .inbox-search-icon { position: absolute; left: 13px; top: 50%; width: 16px; height: 16px; color: #9b7d89; transform: translateY(-50%); pointer-events: none; }
    .inbox-search { width: 100%; min-height: 41px; box-sizing: border-box; border: 1px solid #e4d2c3; border-radius: 10px; background: #FFFFFF; padding: 0 38px; color: #6d4c5b; font: 500 12px Inter, sans-serif; }
    .inbox-search::placeholder { color: #b79c8b; }
    .inbox-clear { position: absolute; right: 7px; top: 50%; display: inline-flex; width: 28px; height: 28px; align-items: center; justify-content: center; border-radius: 7px; color: #9b7d89; text-decoration: none; transform: translateY(-50%); }
    .inbox-search-button, .inbox-mark-all { display: inline-flex; min-height: 41px; align-items: center; justify-content: center; gap: 7px; border-radius: 10px; padding: 0 13px; font: 700 11px Inter, sans-serif; cursor: pointer; }
    .inbox-search-button { border: 1px solid #6d4c5b; background: #6d4c5b; color: #FFFFFF; }
    .inbox-mark-all { border: 1px solid #e4d2c3; background: #FFFFFF; color: #7b5c69; white-space: nowrap; }
    .inbox-mark-all:disabled { cursor: default; opacity: .45; }
    .inbox-search:focus-visible, .inbox-tab:focus-visible, .inbox-search-button:focus-visible, .inbox-mark-all:focus-visible, .inbox-action:focus-visible, .page-btn:focus-visible { outline: 3px solid rgba(109,76,91,.2); outline-offset: 2px; }

    .inbox-flash { display: flex; align-items: center; gap: 9px; margin-bottom: 15px; border: 1px solid #d8e5de; border-radius: 11px; background: #f3f8f5; padding: 12px 14px; color: #4f7c69; font-size: 12px; font-weight: 700; }
    .inbox-day { margin-top: 22px; }
    .inbox-day:first-child { margin-top: 0; }
    .inbox-day-label { display: flex; align-items: center; gap: 10px; margin: 0 0 9px; color: #9b7d89; font-size: 9px; font-weight: 800; letter-spacing: .16em; text-transform: uppercase; }
    .inbox-day-label::after { content: ""; height: 1px; flex: 1; background: #ead8c7; }
    .inbox-list { display: grid; gap: 9px; }
    .inbox-item { position: relative; display: grid; grid-template-columns: auto minmax(0,1fr) auto; gap: 15px; align-items: center; overflow: hidden; border: 1px solid #ead8c7; border-radius: 14px; background: #FFFFFF; padding: 17px 18px 17px 21px; box-shadow: 0 10px 28px rgba(52,35,43,.045); transition: transform .15s ease, border-color .15s ease, box-shadow .15s ease; }
    .inbox-item:hover { transform: translateY(-1px); border-color: #d8c1b1; box-shadow: 0 16px 34px rgba(52,35,43,.08); }
    .inbox-item.unread { background: linear-gradient(90deg, color-mix(in srgb, var(--type-color) 7%, white), #FFFFFF 38%); }
    .inbox-item.unread::before { content: ""; position: absolute; left: 0; top: 0; bottom: 0; width: 4px; background: var(--type-color); }
    .inbox-icon { display: inline-flex; width: 43px; height: 43px; flex: 0 0 43px; align-items: center; justify-content: center; border-radius: 12px; color: var(--type-color); background: color-mix(in srgb, var(--type-color) 10%, white); }
    .inbox-icon svg { width: 18px; height: 18px; }
    .inbox-content { min-width: 0; }
    .inbox-meta { display: flex; flex-wrap: wrap; align-items: center; gap: 7px; margin-bottom: 4px; }
    .inbox-type { color: var(--type-color); font-size: 9px; font-weight: 800; letter-spacing: .13em; text-transform: uppercase; }
    .inbox-new { display: inline-flex; height: 18px; align-items: center; border-radius: 999px; padding: 0 7px; color: #FFFFFF; background: var(--type-color); font-size: 8px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }
    .inbox-time-mobile { display: none; color: #a58b96; font-size: 10px; font-weight: 600; }
    .inbox-item-title { margin: 0; color: #6d4c5b; font-size: 14px; font-weight: 800; line-height: 1.4; }
    .inbox-message { max-width: 720px; margin: 4px 0 0; color: #7b5c69; font-size: 11.5px; line-height: 1.55; overflow-wrap: anywhere; }
    .inbox-side { display: grid; min-width: 135px; gap: 10px; justify-items: end; }
    .inbox-time { color: #a58b96; font-size: 10px; font-weight: 700; white-space: nowrap; }
    .inbox-action { display: inline-flex; min-height: 34px; align-items: center; gap: 7px; border: 1px solid color-mix(in srgb, var(--type-color) 28%, white); border-radius: 9px; padding: 0 11px; color: var(--type-color); background: #FFFFFF; font-size: 10px; font-weight: 800; text-decoration: none; white-space: nowrap; transition: color .15s ease, background .15s ease; }
    .inbox-action:hover { color: #FFFFFF; background: var(--type-color); }
    .inbox-action svg { width: 13px; height: 13px; }
    .inbox-empty { border: 1px dashed #decbbb; border-radius: 15px; background: #FFFFFF; padding: 70px 24px; text-align: center; }
    .inbox-empty-icon { display: inline-flex; width: 54px; height: 54px; align-items: center; justify-content: center; border-radius: 17px; background: #FFFFFF; color: #9b7d89; box-shadow: 0 10px 25px rgba(52,35,43,.06); }
    .inbox-empty h2 { margin: 16px 0 6px; color: #6d4c5b; font-size: 17px; }
    .inbox-empty p { margin: 0; color: #9b7d89; font-size: 12px; }

    .pagination { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-top: 18px; border: 1px solid #ead8c7; border-radius: 12px; background: #FFFFFF; padding: 13px 15px; }
    .page-info { color: #9b7d89; font-size: 11px; font-weight: 600; }
    .page-btns { display: flex; align-items: center; gap: 5px; }
    .page-btn { display: inline-flex; width: 30px; height: 30px; align-items: center; justify-content: center; border: 1px solid #e4d2c3; border-radius: 8px; background: #FFFFFF; color: #7b5c69; font-size: 11px; font-weight: 700; text-decoration: none; }
    .page-btn.active { border-color: #6d4c5b; background: #6d4c5b; color: #FFFFFF; }

    @media (max-width: 760px) {
        .notification-inbox-shell { padding: 20px; }
        .inbox-header { align-items: flex-start; flex-direction: column; }
        .inbox-tools { align-items: stretch; flex-direction: column; }
        .inbox-search-form { width: 100%; }
        .inbox-mark-all { width: 100%; }
        .inbox-item { grid-template-columns: auto minmax(0,1fr); align-items: start; }
        .inbox-side { grid-column: 2; min-width: 0; justify-items: start; }
        .inbox-time { display: none; }
        .inbox-time-mobile { display: inline; }
    }
    @media (max-width: 520px) {
        .inbox-search-form { flex-wrap: wrap; }
        .inbox-search-wrap { flex: 1 0 100%; }
        .inbox-search-button { width: 100%; }
        .inbox-item { gap: 11px; padding: 15px 13px 15px 17px; }
        .inbox-icon { width: 38px; height: 38px; flex-basis: 38px; }
        .pagination { align-items: flex-start; flex-direction: column; }
    }
    @media (prefers-reduced-motion: reduce) {
        .inbox-item, .inbox-tab, .inbox-action { transition: none; }
    }
</style>

<div class="inbox-page">
    <header class="inbox-header">
        <div>
            <p class="inbox-kicker"><?= $h($notificationKicker) ?></p>
            <h1 class="inbox-title">Notifications</h1>
            <p class="inbox-subtitle"><?= $h($notificationSubtitle) ?></p>
        </div>
        <span class="inbox-unread">
            <span class="inbox-unread-dot"></span>
            <?= number_format((int)$stats['unread']) ?> unread
        </span>
    </header>

    <?php if ($message !== ''): ?>
        <div class="inbox-flash"><i data-lucide="circle-check" class="h-4 w-4"></i><?= $h($message) ?></div>
    <?php endif; ?>

    <section class="inbox-toolbar" aria-label="Notification filters">
        <nav class="inbox-tabs" aria-label="Notification categories">
            <?php
            $tabs = [
                'all' => ['All', $stats['total']],
                'booking' => ['Bookings', $stats['booking']],
                'payment' => ['Payments', $stats['payment']],
                'approval' => ['Approvals', $stats['approval']],
                'system' => ['System', $stats['system']],
            ];
            foreach ($tabs as $value => [$label, $count]):
            ?>
                <a class="inbox-tab <?= $filters['type'] === $value ? 'active' : '' ?>" href="<?= $h($tabUrl($value)) ?>">
                    <?= $h($label) ?>
                    <span class="inbox-tab-count"><?= number_format((int)$count) ?></span>
                </a>
            <?php endforeach; ?>
            <a class="inbox-tab <?= $filters['state'] === 'unread' ? 'active' : '' ?>" href="<?= $h($tabUrl($filters['type'], 'unread')) ?>">
                Unread
                <span class="inbox-tab-count"><?= number_format((int)$stats['unread']) ?></span>
            </a>
        </nav>

        <div class="inbox-tools">
            <form class="inbox-search-form" method="get" action="<?= $h($notificationBaseUrl) ?>">
                <?php if ($filters['type'] !== 'all'): ?><input type="hidden" name="type" value="<?= $h($filters['type']) ?>"><?php endif; ?>
                <?php if ($filters['state'] !== 'all'): ?><input type="hidden" name="state" value="<?= $h($filters['state']) ?>"><?php endif; ?>
                <div class="inbox-search-wrap">
                    <i data-lucide="search" class="inbox-search-icon"></i>
                    <input class="inbox-search" type="search" name="search" value="<?= $h($filters['search']) ?>" placeholder="Search notifications">
                    <?php if ($filters['search'] !== ''): ?>
                        <a class="inbox-clear" href="<?= $h($clearSearchUrl) ?>" aria-label="Clear search"><i data-lucide="x" class="h-3.5 w-3.5"></i></a>
                    <?php endif; ?>
                </div>
                <button class="inbox-search-button" type="submit">Search</button>
            </form>
            <form method="post" action="<?= $h($notificationMarkAllUrl) ?>">
                <button class="inbox-mark-all" type="submit" <?= (int)$stats['unread'] === 0 ? 'disabled' : '' ?>>
                    <i data-lucide="check-check" class="h-4 w-4"></i>
                    Mark all read
                </button>
            </form>
        </div>
    </section>

    <?php if (empty($notifications)): ?>
        <div class="inbox-empty">
            <span class="inbox-empty-icon"><i data-lucide="inbox"></i></span>
            <h2>Your queue is clear</h2>
            <p>No notifications match the selected filters.</p>
        </div>
    <?php else: ?>
        <?php $activeGroup = null; ?>
        <?php foreach ($notifications as $item): ?>
            <?php
            $group = $groupLabel($item['created_at'] ?? '');
            if ($group !== $activeGroup):
                if ($activeGroup !== null) echo '</div></section>';
                $activeGroup = $group;
            ?>
                <section class="inbox-day">
                    <h2 class="inbox-day-label"><?= $h($group) ?></h2>
                    <div class="inbox-list">
            <?php endif; ?>
            <?php
            $meta = $typeMeta($item['type'] ?? 'system');
            $isUnread = empty($item['is_read']);
            $href = $notificationHref($item);
            ?>
                <article class="inbox-item <?= $isUnread ? 'unread' : '' ?>" style="--type-color:<?= $h($meta['color']) ?>">
                    <span class="inbox-icon"><i data-lucide="<?= $h($meta['icon']) ?>"></i></span>
                    <div class="inbox-content">
                        <div class="inbox-meta">
                            <span class="inbox-type"><?= $h($meta['label']) ?></span>
                            <?php if ($isUnread): ?><span class="inbox-new">New</span><?php endif; ?>
                            <span class="inbox-time-mobile"><?= $h($timeAgo($item['created_at'] ?? '')) ?></span>
                        </div>
                        <h3 class="inbox-item-title"><?= $h($item['title'] ?? 'Notification') ?></h3>
                        <p class="inbox-message"><?= $h($item['message'] ?? '') ?></p>
                    </div>
                    <div class="inbox-side">
                        <time class="inbox-time" datetime="<?= $h($item['created_at'] ?? '') ?>"><?= $h($timeAgo($item['created_at'] ?? '')) ?></time>
                        <a class="inbox-action" href="<?= $h($href) ?>">
                            <?= $h($actionLabel($item)) ?>
                            <i data-lucide="arrow-up-right"></i>
                        </a>
                    </div>
                </article>
        <?php endforeach; ?>
        </div></section>
    <?php endif; ?>

    <?php
    $baseParams = http_build_query($queryParams);
    require APPROOT . '/views/partials/_pagination.php';
    ?>
</div>
<?php
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php require_once APPROOT . '/views/dashboardLayout/head.php'; ?>
</head>
<body class="grid h-screen gap-0 bg-app-page" style="grid-template-columns: 280px 1fr;">
    <?php require $notificationSidebar; ?>
</body>
</html>
