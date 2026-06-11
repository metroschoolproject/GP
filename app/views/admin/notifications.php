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
  .admin-notifications-content{min-height:100%;background:#FBFBF9;padding:28px 32px;color:#111827;font-size:13px}
  .admin-notification-page{--surface:#fff;--soft:#faf5ef;--hover:#eddecc;--border:#ead8c7;--primary:#6d4c5b;--text:#111827;--muted:#b79c8b;--body:#7b5c69;--success-bg:#d1fae5;--success:#065f46;--warn-bg:#fef3c7;--warn:#92400e;max-width:1200px;margin:0 auto}
  .page-head{display:flex;align-items:flex-end;justify-content:space-between;gap:16px;margin-bottom:22px}
  .eyebrow{font-size:10px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;color:var(--muted);margin-bottom:4px}
  .page-title{font-size:22px;font-weight:700;color:var(--text);margin:0}
  .count-pill{display:inline-flex;align-items:center;height:32px;border:1px solid var(--border);border-radius:999px;background:var(--surface);padding:0 12px;color:var(--body);font-size:12px;font-weight:800}
  .flash{border:1px solid var(--border);border-radius:.75rem;background:var(--surface);padding:12px 14px;margin-bottom:16px;color:var(--body);font-weight:700}
  .list{display:grid;gap:10px}
  .item{display:grid;grid-template-columns:auto 1fr auto;gap:12px;align-items:center;border:1px solid var(--border);border-radius:.75rem;background:var(--surface);padding:14px 16px;text-decoration:none;color:inherit;box-shadow:0 1px 2px rgba(28,25,23,.04)}
  .item:hover{background:var(--soft);border-color:var(--primary)}
  .dot{width:9px;height:9px;border-radius:50%;background:var(--muted)}
  .item.unread .dot{background:var(--primary)}
  .type{font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--muted)}
  .title{margin-top:3px;font-size:14px;font-weight:800;color:var(--text)}
  .body{margin-top:3px;color:var(--body);font-size:12px;line-height:1.45}
  .date{color:var(--muted);font-size:11px;font-weight:700;white-space:nowrap}
  .empty{border:1px dashed var(--border);border-radius:.75rem;background:var(--surface);padding:34px;text-align:center;color:var(--muted);font-weight:700}
</style>

<div class="admin-notification-page">
  <div class="page-head">
    <div>
      <p class="eyebrow">Notifications</p>
      <h1 class="page-title">Admin Notifications</h1>
    </div>
    <span class="count-pill"><?= $unreadCount ?> unread</span>
  </div>

  <?php if ($message !== ''): ?>
    <div class="flash"><?= $h($message) ?></div>
  <?php endif; ?>

  <div class="list">
    <?php if (empty($notifications)): ?>
      <div class="empty">No notifications yet.</div>
    <?php endif; ?>

    <?php foreach ($notifications as $item): ?>
      <?php
      $href = $notificationHref($item);
      $createdAt = !empty($item['created_at']) ? date('M j, Y g:i A', strtotime((string)$item['created_at'])) : '';
      ?>
      <a class="item <?= empty($item['is_read']) ? 'unread' : '' ?>" href="<?= $h($href) ?>">
        <span class="dot"></span>
        <span>
          <span class="type"><?= $h($item['type'] ?? 'system') ?></span>
          <span class="title"><?= $h($item['title'] ?? 'Notification') ?></span>
          <span class="body"><?= $h($item['message'] ?? '') ?></span>
        </span>
        <span class="date"><?= $h($createdAt) ?></span>
      </a>
    <?php endforeach; ?>
  </div>
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
