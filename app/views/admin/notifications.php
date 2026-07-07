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

$actionLabel = static function ($item) use ($notificationRole) {
    $referenceType = strtolower((string)($item['reference_type'] ?? ''));
    $type = strtolower((string)($item['type'] ?? ''));
    $title = strtolower((string)($item['title'] ?? ''));

    if ($referenceType === 'replacement' && ($type === 'payment' || strpos($title, 'delta') !== false)) return 'Verify extra charge';
    if ($referenceType === 'replacement') return 'Open replacement';
    if ($referenceType === 'replacement_invitation') return $notificationRole === 'supplier' ? 'Open assignments' : 'Open replacement';
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
], static fn($value) => $value !== '');

$tabUrl = static function ($type, $state = 'all') use ($filters, $notificationBaseUrl) {
    $params = [];
    if ($type !== 'all') $params['type'] = $type;
    if ($state !== 'all') $params['state'] = $state;
    return $notificationBaseUrl . ($params ? '?' . http_build_query($params) : '');
};

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
    $notificationKicker,
    $notificationSubtitle,
    $notificationRole,
    $notificationMarkAllUrl,
    $currentPage,
    $totalPages,
    $totalCount,
    $perPage
) {
?>
<style>
    .notification-inbox-shell { min-height: 100%; padding: 28px 32px; background: #F4F1EE; font-size: 13.5px; overflow-y: auto; }
    .inbox-page { --bg:#F4F1EE; --surface:#FFFFFF; --soft:#FFFFFF; --hover:#eddecc; --border:#ead8c7; --border-light:#eddecc; --primary:#6d4c5b; --primary-hover:#7b5c69; --primary-soft:#eddecc; --text:#111827; --muted:#b79c8b; --body:#7b5c69; max-width: 1600px; margin: 0 auto; color: var(--body); }
    .inbox-header { display: flex; align-items: flex-end; justify-content: space-between; gap: 16px; margin-bottom: 22px; }
    .inbox-kicker { margin: 0 0 4px; color: var(--muted); font-size: 10px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; }
    .inbox-title { margin: 0; color: #6d4c5b; font-size: 22px; font-weight: 700; line-height: 1.2; letter-spacing: -.02em; }
    .inbox-subtitle { max-width: 620px; margin: 8px 0 0; color: #7b5c69; font-size: 12px; font-weight: 500; line-height: 1.55; }
    .inbox-unread { display: inline-flex; align-items: center; gap: 9px; min-height: 34px; padding: 0 14px; border: 1px solid var(--border); border-radius: .75rem; background: var(--surface); color: var(--primary); font-size: 12px; font-weight: 700; box-shadow: 0 1px 2px rgba(28,25,23,.04); }
    .inbox-unread-dot { width: 8px; height: 8px; border-radius: 50%; background: #b94b4b; box-shadow: 0 0 0 5px rgba(185,75,75,.1); }

    .inbox-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 12px; overflow: hidden; margin-bottom: 20px; border: 1px solid var(--border); border-radius: .75rem; background: var(--surface); padding: 10px 12px; box-shadow: 0 1px 2px rgba(28,25,23,.04); }
    .inbox-tabs { display: flex; flex: 1; gap: 6px; min-width: 0; overflow-x: auto; background: var(--surface); }
    .inbox-tab { display: inline-flex; min-height: 34px; flex: 0 0 auto; align-items: center; gap: 7px; border: 1px solid var(--border); border-radius: .75rem; padding: 0 14px; background: var(--soft); color: var(--body); font-size: 12px; font-weight: 700; font-family: inherit; text-decoration: none; transition: all .12s; }
    .inbox-tab:hover { background: var(--hover); color: var(--primary); }
    .inbox-tab.active { border-color: var(--primary); background: var(--primary); color: #FFFFFF; box-shadow: none; }
    .inbox-tab-count { display: inline-flex; min-width: 20px; height: 20px; align-items: center; justify-content: center; border-radius: 999px; padding: 0 5px; background: rgba(109,76,91,.09); font-size: 9px; }
    .inbox-tab.active .inbox-tab-count { background: rgba(252,248,245,.16); }
    .inbox-tab:focus-visible, .inbox-action:focus-visible, .inbox-mark-all:focus-visible, .page-btn:focus-visible { outline: 3px solid rgba(109,76,91,.2); outline-offset: 2px; }
    .inbox-mark-all-form { flex: 0 0 auto; }
    .inbox-mark-all { display: inline-flex; min-height: 34px; align-items: center; gap: 7px; border: 1px solid var(--primary); border-radius: .75rem; padding: 0 14px; background: var(--primary); color: #FFFFFF; font-family: inherit; font-size: 12px; font-weight: 800; white-space: nowrap; cursor: pointer; transition: background .12s, border-color .12s; }
    .inbox-mark-all:hover { border-color: var(--primary-hover); background: var(--primary-hover); }
    .inbox-mark-all svg { width: 14px; height: 14px; }
    .inbox-mark-all:disabled { cursor: not-allowed; opacity: .55; }

    .inbox-flash { display: flex; align-items: center; gap: 9px; margin-bottom: 15px; border: 1px solid #d8e5de; border-radius: 11px; background: #f3f8f5; padding: 12px 14px; color: #4f7c69; font-size: 12px; font-weight: 700; }
    .inbox-day { margin-top: 22px; }
    .inbox-day:first-child { margin-top: 0; }
    .inbox-day-label { display: flex; align-items: center; gap: 10px; margin: 0 0 9px; color: var(--muted); font-size: 10px; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; }
    .inbox-day-label::after { content: ""; height: 1px; flex: 1; background: #ead8c7; }
    .inbox-list { display: grid; gap: 9px; }
    .inbox-item { position: relative; display: grid; grid-template-columns: auto minmax(0,1fr) auto; gap: 15px; align-items: center; overflow: hidden; border: 1px solid var(--border); border-radius: .75rem; background: var(--surface); padding: 17px 18px 17px 21px; box-shadow: 0 1px 2px rgba(28,25,23,.04); transition: background .1s, transform .15s ease, border-color .15s ease; }
    .inbox-item:hover { transform: translateY(-1px); background: var(--soft); border-color: var(--border); box-shadow: 0 1px 2px rgba(28,25,23,.04); }
    .inbox-item.unread { background: linear-gradient(90deg, color-mix(in srgb, var(--type-color) 7%, white), #FFFFFF 38%); }
    .inbox-item.unread::before { content: ""; position: absolute; left: 0; top: 0; bottom: 0; width: 4px; background: var(--type-color); }
    .inbox-icon { display: inline-flex; width: 43px; height: 43px; flex: 0 0 43px; align-items: center; justify-content: center; border-radius: 12px; color: var(--type-color); background: color-mix(in srgb, var(--type-color) 10%, white); }
    .inbox-icon svg { width: 18px; height: 18px; }
    .inbox-content { min-width: 0; }
    .inbox-meta { display: flex; flex-wrap: wrap; align-items: center; gap: 7px; margin-bottom: 4px; }
    .inbox-type { color: var(--type-color); font-size: 9px; font-weight: 800; letter-spacing: .13em; text-transform: uppercase; }
    .inbox-new { display: inline-flex; height: 18px; align-items: center; border-radius: 999px; padding: 0 7px; color: #FFFFFF; background: var(--type-color); font-size: 8px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }
    .inbox-time-mobile { display: none; color: #a58b96; font-size: 10px; font-weight: 600; }
    .inbox-item-title { margin: 0; color: var(--text); font-size: 13px; font-weight: 700; line-height: 1.4; }
    .inbox-message { max-width: 720px; margin: 4px 0 0; color: var(--body); font-size: 12px; font-weight: 600; line-height: 1.55; overflow-wrap: anywhere; }
    .inbox-side { display: grid; min-width: 135px; gap: 10px; justify-items: end; }
    .inbox-time { color: #a58b96; font-size: 10px; font-weight: 700; white-space: nowrap; }
    .inbox-action { display: inline-flex; min-height: 28px; align-items: center; gap: 6px; border: 1px solid var(--border); border-radius: .75rem; padding: 0 9px; color: var(--primary); background: var(--soft); font-size: 11px; font-weight: 800; text-decoration: none; white-space: nowrap; transition: background .12s; }
    .inbox-action:hover { color: var(--primary); background: var(--hover); }
    .inbox-action svg { width: 13px; height: 13px; }
    .inbox-empty { border: 1px dashed #decbbb; border-radius: 15px; background: #FFFFFF; padding: 70px 24px; text-align: center; }
    .inbox-empty-icon { display: inline-flex; width: 54px; height: 54px; align-items: center; justify-content: center; border-radius: 17px; background: #FFFFFF; color: #9b7d89; box-shadow: 0 10px 25px rgba(52,35,43,.06); }
    .inbox-empty h2 { margin: 16px 0 6px; color: #6d4c5b; font-size: 17px; }
    .inbox-empty p { margin: 0; color: #9b7d89; font-size: 12px; }

    .pagination { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-top: 18px; border: 1px solid var(--border); border-radius: .75rem; background: var(--surface); padding: 12px 20px; }
    .page-info { color: var(--muted); font-size: 12px; font-weight: 600; }
    .page-btns { display: flex; align-items: center; gap: 5px; }
    .page-btn { display: inline-flex; min-width: 28px; height: 28px; align-items: center; justify-content: center; border: 1px solid var(--border); border-radius: .75rem; background: var(--surface); color: var(--body); padding: 0 8px; font-family: inherit; font-size: 12px; font-weight: 600; text-decoration: none; }
    .page-btn.active { border-color: var(--primary); background: var(--primary); color: #FFFFFF; }

    @media (max-width: 760px) {
        .notification-inbox-shell { padding: 20px; }
        .inbox-header { align-items: flex-start; flex-direction: column; }
        .inbox-toolbar { align-items: stretch; flex-direction: column; }
        .inbox-tabs { width: 100%; }
        .inbox-mark-all-form { width: 100%; }
        .inbox-mark-all { width: 100%; justify-content: center; }
        .inbox-item { grid-template-columns: auto minmax(0,1fr); align-items: start; }
        .inbox-side { grid-column: 2; min-width: 0; justify-items: start; }
        .inbox-time { display: none; }
        .inbox-time-mobile { display: inline; }
    }
    @media (max-width: 520px) {
        .inbox-item { gap: 11px; padding: 15px 13px 15px 17px; }
        .inbox-icon { width: 38px; height: 38px; flex-basis: 38px; }
        .pagination { align-items: flex-start; flex-direction: column; }
    }
    @media (prefers-reduced-motion: reduce) {
        .inbox-item, .inbox-tab, .inbox-action, .inbox-mark-all { transition: none; }
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
        <?php if ($notificationRole === 'supplier'): ?>
            <form class="inbox-mark-all-form" method="post" action="<?= $h($notificationMarkAllUrl) ?>">
                <button type="submit" class="inbox-mark-all" <?= (int)$stats['unread'] <= 0 ? 'disabled' : '' ?>>
                    Mark as read
                    <i data-lucide="check-check"></i>
                </button>
            </form>
        <?php endif; ?>
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
    <?php $pageTitle = 'Notifications — Admin'; ?>
    <?php require_once APPROOT . '/views/dashboardLayout/head.php'; ?>
</head>
<body class="grid h-screen gap-0 bg-app-page" style="grid-template-columns: 280px 1fr;">
    <?php require $notificationSidebar; ?>
</body>
</html>
