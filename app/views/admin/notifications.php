<?php
$notifications = $notifications ?? [];
$unreadCount = (int)($unreadCount ?? 0);
$message = $message ?? '';

$dashboardTitle = 'Admin';
$dashboardCrumb = 'Notifications';
$dashboardContentClass = 'admin-notifications-content';

$notificationHref = function ($item) {
    return URLROOT . '/admin/notification/' . (int)($item['id'] ?? 0);
};

$h = function ($value) {
    return htmlspecialchars(htmlspecialchars_decode((string)$value, ENT_QUOTES), ENT_QUOTES, 'UTF-8');
};

$dashboardContent = function () use ($notifications, $unreadCount, $message, $notificationHref, $h) {
?>
<style>
    .admin-notifications-content {
        min-height: 100%;
    }
    .noti-page {
        max-width: 960px;
    }
    .noti-head {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 22px;
    }
    .noti-eyebrow {
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .12em;
        text-transform: uppercase;
    }
    .noti-title {
        font-size: 22px;
        font-weight: 700;
    }
    .noti-pill {
        display: inline-flex;
        align-items: center;
        height: 30px;
        border-radius: 999px;
        padding: 0 12px;
        font-size: 12px;
        font-weight: 700;
    }
    .noti-flash {
        border-radius: .75rem;
        padding: 12px 14px;
        margin-bottom: 16px;
        font-weight: 700;
    }
    .noti-list {
        display: grid;
        gap: 10px;
    }
    .noti-item {
        display: grid;
        grid-template-columns: auto 1fr auto;
        gap: 14px;
        align-items: center;
        border-radius: .85rem;
        padding: 15px 18px;
        text-decoration: none;
        color: inherit;
        transition: background .15s ease, border-color .15s ease, box-shadow .15s ease;
    }
    .noti-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        flex-shrink: 0;
    }
    .noti-type {
        display: block;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .12em;
        text-transform: uppercase;
    }
    .noti-item-title {
        display: block;
        margin-top: 3px;
        font-size: 14px;
        font-weight: 800;
        line-height: 1.35;
    }
    .noti-body {
        display: block;
        margin-top: 3px;
        font-size: 12px;
        line-height: 1.5;
        overflow-wrap: anywhere;
    }
    .noti-date {
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }
    .noti-empty {
        border-radius: .85rem;
        padding: 36px;
        text-align: center;
        font-weight: 700;
    }
</style>

<div class="noti-page">
    <div class="noti-head">
        <div>
            <p class="noti-eyebrow text-app-muted">Notifications</p>
            <h1 class="noti-title text-app-text">Admin Notifications</h1>
        </div>
        <span class="noti-pill border border-app-border bg-app-card text-app-secondary">
            <?= $unreadCount ?> unread
        </span>
    </div>

    <?php if ($message !== ''): ?>
        <div class="noti-flash border border-app-border bg-app-card text-app-text">
            <?= $h($message) ?>
        </div>
    <?php endif; ?>

    <div class="noti-list">
        <?php if (empty($notifications)): ?>
            <div class="noti-empty border border-dashed border-app-border bg-app-soft text-app-muted">
                No notifications yet.
            </div>
        <?php endif; ?>

        <?php foreach ($notifications as $item): ?>
            <?php
            $href = $notificationHref($item);
            $createdAt = !empty($item['created_at']) ? date('M j, Y g:i A', strtotime((string)$item['created_at'])) : '';
            $isUnread = empty($item['is_read']);
            ?>
            <a class="noti-item border shadow-sm <?= $isUnread
                ? 'border-app-focus bg-app-card shadow-card'
                : 'border-app-border bg-app-soft' ?> hover:border-app-focus hover:bg-app-card hover:shadow-card"
               href="<?= $h($href) ?>">
                <span class="noti-dot <?= $isUnread ? 'bg-app-primary' : 'bg-app-muted' ?>"></span>
                <span class="min-w-0">
                    <span class="noti-type text-app-muted"><?= $h($item['type'] ?? 'system') ?></span>
                    <span class="noti-item-title text-app-text"><?= $h($item['title'] ?? 'Notification') ?></span>
                    <span class="noti-body text-app-secondary"><?= $h($item['message'] ?? '') ?></span>
                </span>
                <span class="noti-date text-app-muted"><?= $h($createdAt) ?></span>
            </a>
        <?php endforeach; ?>
    </div>

    <?php
    if (isset($currentPage, $totalPages, $totalCount, $perPage)) {
        $h = function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };
        require APPROOT . '/views/partials/_pagination.php';
    }
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
    <?php require APPROOT . '/views/dashboardLayout/adminsidebar.php'; ?>
</body>
</html>
