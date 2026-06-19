<?php
$logs = $logs ?? [];
$stats = $stats ?? ['total' => 0, 'warnings' => 0, 'failed_logins' => 0, 'critical' => 0];
$filters = $filters ?? ['search' => '', 'event' => 'all', 'status' => 'all', 'date_from' => '', 'date_to' => ''];

$dashboardTitle = 'Settings';
$dashboardCrumb = 'System logs';
$dashboardContentClass = 'log-page-shell';
$dashboardBreadcrumbs = [
    ['label' => 'Settings', 'url' => null],
    ['label' => 'System logs', 'url' => null],
];

$h = static function ($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
};

$eventLabel = static function ($action) {
    $labels = [
        'login_information_correct' => 'Login information accepted',
        'login_information_fail' => 'Failed login attempt',
        'login_success' => 'Successful login',
        'sendingOTP_success' => 'OTP sent',
        'sendingOTP_fail' => 'OTP delivery failed',
        'verifyOTP_success' => 'OTP verified',
        'verifyOTP_fail' => 'OTP verification failed',
        'logout' => 'User logged out',
        'account_locked' => 'Account locked',
        'account_unlocked' => 'Account unlocked',
    ];

    if (isset($labels[$action])) {
        return $labels[$action];
    }

    return ucwords(str_replace('_', ' ', (string)$action));
};

$eventIcon = static function ($action) {
    $action = strtolower((string)$action);
    if (strpos($action, 'lock') !== false) return 'shield-alert';
    if (strpos($action, 'otp') !== false) return 'key-round';
    if (strpos($action, 'logout') !== false) return 'log-out';
    return 'log-in';
};

$deviceLabel = static function ($userAgent) {
    $ua = (string)$userAgent;
    if ($ua === '') return 'Not recorded';

    $browser = 'Browser';
    if (stripos($ua, 'Edg/') !== false) $browser = 'Edge';
    elseif (stripos($ua, 'Chrome/') !== false) $browser = 'Chrome';
    elseif (stripos($ua, 'Firefox/') !== false) $browser = 'Firefox';
    elseif (stripos($ua, 'Safari/') !== false) $browser = 'Safari';

    $platform = 'Unknown device';
    if (stripos($ua, 'Macintosh') !== false) $platform = 'macOS';
    elseif (stripos($ua, 'Windows') !== false) $platform = 'Windows';
    elseif (stripos($ua, 'Android') !== false) $platform = 'Android';
    elseif (stripos($ua, 'iPhone') !== false || stripos($ua, 'iPad') !== false) $platform = 'iOS';
    elseif (stripos($ua, 'Linux') !== false) $platform = 'Linux';

    return $browser . ' on ' . $platform;
};

$queryParams = array_filter([
    'search' => $filters['search'] ?? '',
    'event' => ($filters['event'] ?? 'all') !== 'all' ? $filters['event'] : '',
    'status' => ($filters['status'] ?? 'all') !== 'all' ? $filters['status'] : '',
    'date_from' => $filters['date_from'] ?? '',
    'date_to' => $filters['date_to'] ?? '',
], static fn($value) => $value !== '');
$exportUrl = URLROOT . '/admin/logs?' . http_build_query(array_merge($queryParams, ['export' => 'csv']));

$dashboardContent = function () use (
    $logs,
    $stats,
    $filters,
    $h,
    $eventLabel,
    $eventIcon,
    $deviceLabel,
    $queryParams,
    $exportUrl,
    $currentPage,
    $totalPages,
    $totalCount,
    $perPage
) {
?>
<style>
    .log-page-shell { min-height: 100%; padding: 30px; background: #fbfbf9; }
    .log-page { max-width: 1440px; margin: 0 auto; color: #111827; }
    .log-header { display: flex; align-items: flex-end; justify-content: space-between; gap: 22px; margin-bottom: 24px; }
    .log-kicker { margin: 0 0 7px; color: #9b7d89; font-size: 10px; font-weight: 800; letter-spacing: .18em; text-transform: uppercase; }
    .log-title { margin: 0; font-family: "Playfair Display", serif; font-size: clamp(30px, 3vw, 42px); font-weight: 650; line-height: 1; color: #34232b; }
    .log-subtitle { max-width: 620px; margin: 10px 0 0; color: #7b5c69; font-size: 13px; line-height: 1.6; }
    .log-export { display: inline-flex; align-items: center; gap: 9px; min-height: 42px; padding: 0 16px; border: 1px solid #6d4c5b; border-radius: 11px; background: #6d4c5b; color: #fff; font-size: 12px; font-weight: 700; text-decoration: none; box-shadow: 0 10px 22px rgba(109,76,91,.18); transition: transform .16s ease, box-shadow .16s ease; }
    .log-export:hover { transform: translateY(-1px); box-shadow: 0 14px 28px rgba(109,76,91,.24); }
    .log-export:focus-visible, .log-control:focus-visible, .log-reset:focus-visible, .log-row:focus-visible, .page-btn:focus-visible { outline: 3px solid rgba(109,76,91,.22); outline-offset: 2px; }

    .log-stats { display: grid; grid-template-columns: repeat(4, minmax(0,1fr)); margin-bottom: 18px; overflow: hidden; border: 1px solid #ead8c7; border-radius: 15px; background: #fff; box-shadow: 0 18px 45px rgba(52,35,43,.06); }
    .log-stat { position: relative; min-height: 104px; padding: 22px 24px; border-right: 1px solid #ead8c7; }
    .log-stat:last-child { border-right: 0; }
    .log-stat::after { content: ""; position: absolute; right: 18px; top: 20px; width: 7px; height: 7px; border-radius: 50%; background: var(--stat-color); box-shadow: 0 0 0 5px color-mix(in srgb, var(--stat-color) 12%, transparent); }
    .log-stat-label { color: #9b7d89; font-size: 10px; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; }
    .log-stat-value { display: block; margin-top: 8px; color: #34232b; font-family: "Playfair Display", serif; font-size: 28px; line-height: 1; }
    .log-stat-note { display: block; margin-top: 7px; color: #a58b96; font-size: 11px; }

    .log-panel { overflow: hidden; border: 1px solid #ead8c7; border-radius: 15px; background: #fff; box-shadow: 0 18px 45px rgba(52,35,43,.06); }
    .log-filters { display: grid; grid-template-columns: minmax(240px, 1.6fr) repeat(4, minmax(135px, .7fr)) auto; gap: 10px; padding: 16px; border-bottom: 1px solid #ead8c7; background: #faf5ef; }
    .log-search-wrap { position: relative; }
    .log-search-icon { position: absolute; left: 13px; top: 50%; width: 16px; height: 16px; color: #9b7d89; transform: translateY(-50%); pointer-events: none; }
    .log-control { width: 100%; min-height: 42px; box-sizing: border-box; border: 1px solid #e4d2c3; border-radius: 10px; background: #fff; padding: 0 12px; color: #34232b; font: 500 12px Poppins, sans-serif; }
    .log-search { padding-left: 39px; }
    .log-control::placeholder { color: #b79c8b; }
    .log-filter-actions { display: flex; gap: 8px; }
    .log-apply, .log-reset { display: inline-flex; align-items: center; justify-content: center; min-height: 42px; border-radius: 10px; padding: 0 14px; font-size: 12px; font-weight: 700; cursor: pointer; }
    .log-apply { border: 1px solid #6d4c5b; background: #6d4c5b; color: #fff; }
    .log-reset { border: 1px solid #e4d2c3; background: #fff; color: #7b5c69; text-decoration: none; }

    .log-table-wrap { overflow-x: auto; }
    .log-table { width: 100%; min-width: 940px; border-collapse: collapse; }
    .log-table th { padding: 13px 18px; border-bottom: 1px solid #ead8c7; background: #fff; color: #9b7d89; font-size: 9px; font-weight: 800; letter-spacing: .13em; text-align: left; text-transform: uppercase; }
    .log-table td { padding: 15px 18px; border-bottom: 1px solid #f0e5dc; vertical-align: middle; font-size: 12px; }
    .log-row { position: relative; cursor: pointer; transition: background .14s ease; }
    .log-row:hover, .log-row:focus { background: #fdf9f5; outline: none; }
    .log-row td:first-child { border-left: 3px solid var(--row-color); }
    .log-time { color: #34232b; font-weight: 700; white-space: nowrap; }
    .log-date { display: block; margin-top: 3px; color: #a58b96; font-size: 10px; font-weight: 500; }
    .log-event { display: flex; align-items: center; gap: 11px; min-width: 230px; }
    .log-event-icon { display: inline-flex; width: 34px; height: 34px; flex: 0 0 34px; align-items: center; justify-content: center; border-radius: 10px; color: var(--row-color); background: color-mix(in srgb, var(--row-color) 11%, white); }
    .log-event-icon svg { width: 15px; height: 15px; }
    .log-event-name { color: #34232b; font-weight: 700; }
    .log-event-code { display: block; max-width: 250px; margin-top: 3px; overflow: hidden; color: #a58b96; font: 10px ui-monospace, SFMono-Regular, Menlo, monospace; text-overflow: ellipsis; white-space: nowrap; }
    .log-user { color: #34232b; font-weight: 700; }
    .log-email { display: block; max-width: 210px; margin-top: 3px; overflow: hidden; color: #a58b96; font-size: 10px; font-weight: 500; text-overflow: ellipsis; white-space: nowrap; }
    .log-ip { color: #7b5c69; font: 11px ui-monospace, SFMono-Regular, Menlo, monospace; }
    .log-status { display: inline-flex; align-items: center; gap: 7px; min-height: 26px; border-radius: 999px; padding: 0 10px; color: var(--row-color); background: color-mix(in srgb, var(--row-color) 10%, white); font-size: 10px; font-weight: 800; text-transform: capitalize; }
    .log-status::before { content: ""; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
    .log-more { color: #b79c8b; }
    .log-empty { padding: 70px 24px; text-align: center; }
    .log-empty-icon { display: inline-flex; width: 52px; height: 52px; align-items: center; justify-content: center; border-radius: 16px; background: #faf5ef; color: #9b7d89; }
    .log-empty h2 { margin: 15px 0 6px; color: #34232b; font: 700 17px Poppins, sans-serif; }
    .log-empty p { margin: 0; color: #9b7d89; font-size: 12px; }

    .pagination { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 15px 18px; background: #faf5ef; }
    .page-info { color: #9b7d89; font-size: 11px; font-weight: 600; }
    .page-btns { display: flex; align-items: center; gap: 5px; }
    .page-btn { display: inline-flex; width: 30px; height: 30px; align-items: center; justify-content: center; border: 1px solid #e4d2c3; border-radius: 8px; background: #fff; color: #7b5c69; font-size: 11px; font-weight: 700; text-decoration: none; }
    .page-btn.active { border-color: #6d4c5b; background: #6d4c5b; color: #fff; }

    .log-drawer-backdrop { position: fixed; inset: 0; z-index: 70; visibility: hidden; background: rgba(36,24,30,.2); opacity: 0; backdrop-filter: blur(2px); transition: opacity .2s ease, visibility .2s ease; }
    .log-drawer { position: fixed; top: 0; right: 0; z-index: 80; width: min(430px, 92vw); height: 100vh; box-sizing: border-box; overflow-y: auto; border-left: 1px solid #ead8c7; background: #fff; padding: 25px; box-shadow: -20px 0 50px rgba(52,35,43,.13); transform: translateX(105%); transition: transform .24s ease; }
    .log-drawer-open .log-drawer-backdrop { visibility: visible; opacity: 1; }
    .log-drawer-open .log-drawer { transform: translateX(0); }
    .log-drawer-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 18px; padding-bottom: 20px; border-bottom: 1px solid #ead8c7; }
    .log-drawer-kicker { margin: 0 0 5px; color: #9b7d89; font-size: 9px; font-weight: 800; letter-spacing: .15em; text-transform: uppercase; }
    .log-drawer-title { margin: 0; color: #34232b; font: 700 21px "Playfair Display", serif; }
    .log-drawer-close { display: inline-flex; width: 36px; height: 36px; flex: 0 0 36px; align-items: center; justify-content: center; border: 1px solid #ead8c7; border-radius: 10px; background: #faf5ef; color: #7b5c69; cursor: pointer; }
    .log-detail-list { display: grid; gap: 0; margin-top: 12px; }
    .log-detail { padding: 15px 0; border-bottom: 1px solid #f0e5dc; }
    .log-detail dt { color: #a58b96; font-size: 9px; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; }
    .log-detail dd { margin: 6px 0 0; color: #34232b; font-size: 12px; font-weight: 600; line-height: 1.55; overflow-wrap: anywhere; }
    .log-detail dd.mono { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 11px; font-weight: 500; }

    @media (max-width: 1120px) {
        .log-filters { grid-template-columns: repeat(2, minmax(0,1fr)); }
        .log-search-wrap { grid-column: 1 / -1; }
        .log-filter-actions { justify-content: flex-end; }
    }
    @media (max-width: 820px) {
        .log-page-shell { padding: 20px; }
        .log-stats { grid-template-columns: repeat(2, minmax(0,1fr)); }
        .log-stat:nth-child(2) { border-right: 0; }
        .log-stat:nth-child(-n+2) { border-bottom: 1px solid #ead8c7; }
    }
    @media (max-width: 600px) {
        .log-header { align-items: flex-start; flex-direction: column; }
        .log-export { width: 100%; box-sizing: border-box; justify-content: center; }
        .log-filters { grid-template-columns: 1fr; }
        .log-search-wrap { grid-column: auto; }
        .log-filter-actions { justify-content: stretch; }
        .log-filter-actions > * { flex: 1; }
        .pagination { align-items: flex-start; flex-direction: column; }
    }
    @media (prefers-reduced-motion: reduce) {
        .log-export, .log-row, .log-drawer, .log-drawer-backdrop { transition: none; }
    }
</style>

<div class="log-page">
    <header class="log-header">
        <div>
            <p class="log-kicker">Security &amp; accountability</p>
            <h1 class="log-title">System logs</h1>
            <p class="log-subtitle">Trace sign-ins, OTP activity, session changes, and account lockouts across Golden Promise.</p>
        </div>
        <a class="log-export" href="<?= $h($exportUrl) ?>">
            <i data-lucide="download" class="h-4 w-4"></i>
            Export CSV
        </a>
    </header>

    <section class="log-stats" aria-label="Log summary">
        <article class="log-stat" style="--stat-color:#6d4c5b">
            <span class="log-stat-label">All events</span>
            <strong class="log-stat-value"><?= number_format((int)$stats['total']) ?></strong>
            <span class="log-stat-note">Recorded audit entries</span>
        </article>
        <article class="log-stat" style="--stat-color:#d08a32">
            <span class="log-stat-label">Warnings</span>
            <strong class="log-stat-value"><?= number_format((int)$stats['warnings']) ?></strong>
            <span class="log-stat-note">Events needing attention</span>
        </article>
        <article class="log-stat" style="--stat-color:#b94b4b">
            <span class="log-stat-label">Failed logins</span>
            <strong class="log-stat-value"><?= number_format((int)$stats['failed_logins']) ?></strong>
            <span class="log-stat-note">Rejected credentials</span>
        </article>
        <article class="log-stat" style="--stat-color:#7b5c69">
            <span class="log-stat-label">Account locks</span>
            <strong class="log-stat-value"><?= number_format((int)$stats['critical']) ?></strong>
            <span class="log-stat-note">Critical security actions</span>
        </article>
    </section>

    <section class="log-panel">
        <form class="log-filters" method="get" action="<?= URLROOT ?>/admin/logs">
            <div class="log-search-wrap">
                <i data-lucide="search" class="log-search-icon"></i>
                <input class="log-control log-search" type="search" name="search" value="<?= $h($filters['search']) ?>" placeholder="Search user, email, IP address, or event">
            </div>
            <select class="log-control" name="event" aria-label="Event type">
                <?php foreach (['all' => 'All events', 'login' => 'Login activity', 'otp' => 'OTP activity', 'logout' => 'Logouts', 'lockout' => 'Account locks'] as $value => $label): ?>
                    <option value="<?= $value ?>" <?= $filters['event'] === $value ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
            <select class="log-control" name="status" aria-label="Event status">
                <?php foreach (['all' => 'All statuses', 'success' => 'Success', 'warning' => 'Warning', 'critical' => 'Critical'] as $value => $label): ?>
                    <option value="<?= $value ?>" <?= $filters['status'] === $value ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
            <input class="log-control" type="date" name="date_from" value="<?= $h($filters['date_from']) ?>" aria-label="From date">
            <input class="log-control" type="date" name="date_to" value="<?= $h($filters['date_to']) ?>" aria-label="To date">
            <div class="log-filter-actions">
                <button class="log-apply" type="submit">Apply</button>
                <a class="log-reset" href="<?= URLROOT ?>/admin/logs">Reset</a>
            </div>
        </form>

        <?php if (empty($logs)): ?>
            <div class="log-empty">
                <span class="log-empty-icon"><i data-lucide="file-search"></i></span>
                <h2>No matching events</h2>
                <p>Adjust the filters or clear the search to see more activity.</p>
            </div>
        <?php else: ?>
            <div class="log-table-wrap">
                <table class="log-table">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Event</th>
                            <th>User</th>
                            <th>IP address</th>
                            <th>Status</th>
                            <th><span class="sr-only">Details</span></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($logs as $log): ?>
                        <?php
                        $severity = $log['severity'] ?? 'success';
                        $rowColor = $severity === 'critical' ? '#b94b4b' : ($severity === 'warning' ? '#d08a32' : '#468765');
                        $timestamp = !empty($log['created_at']) ? strtotime($log['created_at']) : false;
                        $detail = [
                            'title' => $eventLabel($log['action'] ?? ''),
                            'code' => $log['action'] ?? '—',
                            'status' => ucfirst($severity),
                            'user' => $log['user_name'] ?? 'Unknown user',
                            'email' => $log['user_email'] ?? '—',
                            'ip' => $log['ip_address'] ?: 'Not recorded',
                            'time' => $timestamp ? date('F j, Y · g:i:s A', $timestamp) : 'Not recorded',
                            'device' => $deviceLabel($log['user_agent'] ?? ''),
                            'agent' => $log['user_agent'] ?: 'Not recorded',
                            'reason' => !empty($log['reason']) ? ucwords(str_replace('_', ' ', $log['reason'])) : '—',
                            'attempts' => $log['attempt_count'] !== null ? (string)$log['attempt_count'] : '—',
                            'lockedUntil' => !empty($log['locked_until']) ? date('F j, Y · g:i A', strtotime($log['locked_until'])) : '—',
                        ];
                        ?>
                        <tr class="log-row" tabindex="0" role="button" aria-label="View <?= $h($detail['title']) ?> details"
                            style="--row-color:<?= $rowColor ?>"
                            data-log="<?= $h(json_encode($detail, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) ?>">
                            <td>
                                <span class="log-time"><?= $timestamp ? date('g:i A', $timestamp) : '—' ?></span>
                                <span class="log-date"><?= $timestamp ? date('M j, Y', $timestamp) : 'Unknown date' ?></span>
                            </td>
                            <td>
                                <span class="log-event">
                                    <span class="log-event-icon"><i data-lucide="<?= $eventIcon($log['action'] ?? '') ?>"></i></span>
                                    <span>
                                        <span class="log-event-name"><?= $h($detail['title']) ?></span>
                                        <span class="log-event-code"><?= $h($detail['code']) ?></span>
                                    </span>
                                </span>
                            </td>
                            <td>
                                <span class="log-user"><?= $h($detail['user']) ?></span>
                                <span class="log-email"><?= $h($detail['email']) ?></span>
                            </td>
                            <td><span class="log-ip"><?= $h($detail['ip']) ?></span></td>
                            <td><span class="log-status"><?= $h($severity) ?></span></td>
                            <td><i data-lucide="chevron-right" class="log-more h-4 w-4"></i></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <?php
        $baseParams = http_build_query($queryParams);
        require APPROOT . '/views/partials/_pagination.php';
        ?>
    </section>
</div>

<div class="log-drawer-backdrop" data-log-close></div>
<aside class="log-drawer" id="log-detail-drawer" aria-hidden="true" aria-labelledby="drawer-title">
    <div class="log-drawer-head">
        <div>
            <p class="log-drawer-kicker">Audit event</p>
            <h2 class="log-drawer-title" id="drawer-title">Event details</h2>
        </div>
        <button class="log-drawer-close" type="button" data-log-close aria-label="Close event details">
            <i data-lucide="x" class="h-4 w-4"></i>
        </button>
    </div>
    <dl class="log-detail-list">
        <div class="log-detail"><dt>Status</dt><dd data-detail="status">—</dd></div>
        <div class="log-detail"><dt>Event code</dt><dd class="mono" data-detail="code">—</dd></div>
        <div class="log-detail"><dt>Date and time</dt><dd data-detail="time">—</dd></div>
        <div class="log-detail"><dt>User</dt><dd data-detail="user">—</dd></div>
        <div class="log-detail"><dt>Email</dt><dd data-detail="email">—</dd></div>
        <div class="log-detail"><dt>IP address</dt><dd class="mono" data-detail="ip">—</dd></div>
        <div class="log-detail"><dt>Device</dt><dd data-detail="device">—</dd></div>
        <div class="log-detail"><dt>User agent</dt><dd class="mono" data-detail="agent">—</dd></div>
        <div class="log-detail"><dt>Security reason</dt><dd data-detail="reason">—</dd></div>
        <div class="log-detail"><dt>Attempt count</dt><dd data-detail="attempts">—</dd></div>
        <div class="log-detail"><dt>Locked until</dt><dd data-detail="lockedUntil">—</dd></div>
    </dl>
</aside>

<script>
(() => {
    const drawer = document.getElementById('log-detail-drawer');
    let lastTrigger = null;

    const closeDrawer = () => {
        document.body.classList.remove('log-drawer-open');
        drawer?.setAttribute('aria-hidden', 'true');
        lastTrigger?.focus();
    };

    const openDrawer = (row) => {
        const detail = JSON.parse(row.dataset.log || '{}');
        lastTrigger = row;
        document.getElementById('drawer-title').textContent = detail.title || 'Event details';
        drawer.querySelectorAll('[data-detail]').forEach((node) => {
            node.textContent = detail[node.dataset.detail] || '—';
        });
        document.body.classList.add('log-drawer-open');
        drawer.setAttribute('aria-hidden', 'false');
        drawer.querySelector('[data-log-close]')?.focus();
    };

    document.querySelectorAll('.log-row').forEach((row) => {
        row.addEventListener('click', () => openDrawer(row));
        row.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                openDrawer(row);
            }
        });
    });
    document.querySelectorAll('[data-log-close]').forEach((button) => button.addEventListener('click', closeDrawer));
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && document.body.classList.contains('log-drawer-open')) closeDrawer();
    });
})();
</script>
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
