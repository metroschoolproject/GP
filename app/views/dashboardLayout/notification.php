<?php
$notificationConfig = $notificationConfig ?? [];
$notificationRole = trim((string)($notificationConfig['role'] ?? $notificationRole ?? ''));
$notificationBasePath = $notificationRole !== '' ? '/' . rawurlencode($notificationRole) : '';

$notificationJsonUrl = $notificationConfig['jsonUrl']
    ?? $notificationJsonUrl
    ?? ($notificationBasePath ? URLROOT . $notificationBasePath . '/notificationsJson' : '');
$notificationMarkReadUrl = $notificationConfig['markReadUrl']
    ?? $notificationMarkReadUrl
    ?? ($notificationBasePath ? URLROOT . $notificationBasePath . '/markNotificationRead/' : '');
$notificationDetailUrlBase = $notificationConfig['detailUrlBase'] ?? $notificationDetailUrlBase ?? null;
$notificationReviewUrl = $notificationConfig['reviewUrl']
    ?? $notificationReviewUrl
    ?? ($notificationBasePath ? URLROOT . $notificationBasePath . '/dashboard' : '#');
$notificationDefaultUrl = $notificationConfig['defaultUrl']
    ?? $notificationDefaultUrl
    ?? ($notificationBasePath ? URLROOT . $notificationBasePath . '/dashboard' : '#');
$notificationReferenceUrls = $notificationConfig['referenceUrls'] ?? $notificationReferenceUrls ?? [];
if ($notificationRole === 'admin') {
    $notificationReferenceUrls = array_merge([
        'booking' => URLROOT . '/admin/bookingDetail/',
        'replacement' => URLROOT . '/admin/replacementPicker/',
        'payment' => URLROOT . '/admin/payments?payment=',
        'supplier' => URLROOT . '/admin/supplier/',
        'service' => URLROOT . '/admin/service/',
    ], $notificationReferenceUrls);
}
$notificationEmptyText = $notificationConfig['emptyText'] ?? 'No notifications yet.';
$notificationReviewLabel = $notificationConfig['reviewLabel'] ?? 'Review all';
?>
<style>
    .dashboard-notification-panel {
        position: absolute;
        right: 0;
        top: calc(100% + 10px);
        z-index: 50;
        width: min(24rem, calc(100vw - 2rem));
        max-width: 24rem;
        transform-origin: top right;
        transition: opacity 0.16s ease, transform 0.16s ease, visibility 0.16s ease;
    }

    .dashboard-notification-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.25rem 0.25rem 0.75rem;
    }

    .dashboard-notification-title {
        font-size: 0.875rem;
        font-weight: 700;
    }

    .dashboard-notification-link {
        font-size: 0.75rem;
        font-weight: 700;
    }

    .dashboard-notification-list {
        display: grid;
        max-height: 24rem;
        gap: 0.5rem;
        overflow-y: auto;
        overflow-x: hidden;
        padding-top: 0.75rem;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    .dashboard-notification-list::-webkit-scrollbar {
        display: none;
        width: 0;
        height: 0;
    }

    .dashboard-notification-empty {
        padding: 1.5rem 0.75rem;
        text-align: center;
        font-size: 0.875rem;
    }

    .dashboard-notification-item {
        display: block;
        padding: 0.75rem;
        transition: background 0.12s ease, border-color 0.12s ease, box-shadow 0.12s ease;
    }

    .dashboard-notification-type {
        display: block;
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 0.12em;
        text-transform: uppercase;
    }

    .dashboard-notification-item-title {
        display: block;
        margin-top: 0.25rem;
        font-size: 0.875rem;
        font-weight: 800;
        line-height: 1.35;
    }

    .dashboard-notification-message {
        display: block;
        margin-top: 0.25rem;
        font-size: 0.75rem;
        line-height: 1.45;
        overflow-wrap: anywhere;
    }

    .dashboard-notification-meta {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.75rem;
    }

    .dashboard-notification-time {
        flex: 0 0 auto;
        font-size: 0.68rem;
        font-weight: 700;
        color: var(--color-app-muted, #8a7180);
        white-space: nowrap;
    }
</style>

<div class="relative">
    <button id="dashboardNotificationBtn" class="relative flex h-11 w-11 items-center justify-center rounded-xl border border-app-border bg-app-input/80 text-app-secondary shadow-sm transition hover:bg-app-input hover:shadow-md" type="button" aria-expanded="false">
        <svg xmlns="http://www.w3.org/2000/svg"
            width="24" height="24"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
            class="h-5 w-5">
            <path d="M10.268 21a2 2 0 0 0 3.464 0"/>
            <path d="M3.262 15.326A1 1 0 0 0 4 17h16a1 1 0 0 0 .74-1.673C19.41 13.956 18 12.499 18 8A6 6 0 0 0 6 8c0 4.499-1.411 5.956-2.738 7.326"/>
        </svg>

        <span id="dashboardNotificationDot" class="absolute right-2 top-2 hidden h-2 w-2 rounded-full bg-app-danger"></span>
        <span id="dashboardNotificationCount" class="absolute -right-1 -top-1 hidden min-w-5 rounded-full bg-app-danger px-1.5 py-0.5 text-[10px] font-bold text-app-white"></span>
    </button>
    <div id="dashboardNotificationPanel" class="dashboard-notification-panel invisible scale-95 rounded-2xl border border-app-border bg-app-sidebar p-3 opacity-0 shadow-panel">
        <div class="dashboard-notification-header border-b border-app-border">
            <p class="dashboard-notification-title text-app-text">Notifications</p>
            <a href="<?= htmlspecialchars($notificationReviewUrl, ENT_QUOTES, 'UTF-8') ?>" class="dashboard-notification-link text-app-primary"><?= htmlspecialchars($notificationReviewLabel, ENT_QUOTES, 'UTF-8') ?></a>
        </div>
        <div id="dashboardNotificationList" class="dashboard-notification-list">
            <p class="dashboard-notification-empty rounded-xl border border-dashed border-app-border bg-app-soft text-app-muted"><?= htmlspecialchars($notificationEmptyText, ENT_QUOTES, 'UTF-8') ?></p>
        </div>
    </div>
</div>

<script>
(() => {
    const notificationBtn = document.getElementById('dashboardNotificationBtn');
    const notificationPanel = document.getElementById('dashboardNotificationPanel');
    const notificationList = document.getElementById('dashboardNotificationList');
    const notificationCount = document.getElementById('dashboardNotificationCount');
    const notificationDot = document.getElementById('dashboardNotificationDot');
    const notificationJsonUrl = <?= json_encode($notificationJsonUrl) ?>;
    const notificationMarkReadUrl = <?= json_encode($notificationMarkReadUrl) ?>;
    const notificationDefaultUrl = <?= json_encode($notificationDefaultUrl) ?>;
    const notificationReferenceUrls = <?= json_encode($notificationReferenceUrls) ?>;
    const notificationDetailUrlBase = <?= json_encode($notificationDetailUrlBase) ?>;
    const notificationEmptyText = <?= json_encode($notificationEmptyText) ?>;

    function notificationHref(item) {
        if (notificationDetailUrlBase) {
            return notificationDetailUrlBase + encodeURIComponent(item.id);
        }

        const baseUrl = notificationReferenceUrls[item.reference_type];

        if (baseUrl && item.reference_id) {
            return baseUrl + encodeURIComponent(item.reference_id);
        }

        return notificationDefaultUrl;
    }

    function escapeNotificationText(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function shouldShowMore(value) {
        return String(value || '').trim().length > 86;
    }

    function notificationIcon(type) {
        const key = String(type || '').toLowerCase();
        if (key.includes('payment')) {
            return '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"></rect><path d="M3 10h18"></path><path d="M7 15h4"></path></svg>';
        }
        if (key.includes('booking')) {
            return '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="5" width="16" height="15" rx="2"></rect><path d="M8 3v4"></path><path d="M16 3v4"></path><path d="M4 10h16"></path><path d="m9 15 2 2 4-5"></path></svg>';
        }
        return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 4 7v6c0 5 3.5 7.5 8 8 4.5-.5 8-3 8-8V7l-8-4Z"></path><path d="m9 12 2 2 4-4"></path></svg>';
    }

    function notificationTone(item) {
        const text = [
            item?.type,
            item?.reference_type,
            item?.title,
            item?.message
        ].join(' ').toLowerCase();

        if (/\b(pending|waiting|awaiting|review|processing|unpaid|unverified)\b/.test(text)) {
            return 'pending';
        }

        if (/\b(cancel|cancelled|canceled|decline|declined|reject|rejected|failed|fail|expired|overdue|unavailable|denied|error|problem)\b/.test(text)) {
            return 'negative';
        }

        if (/\b(approved|confirm|confirmed|success|successful|paid|verified|complete|completed|accepted|available)\b/.test(text)) {
            return 'positive';
        }

        return 'default';
    }

    function notificationTimeLabel(value) {
        if (!value) return '';

        const normalized = String(value).replace(' ', 'T');
        const created = new Date(normalized);
        if (Number.isNaN(created.getTime())) return '';

        const now = new Date();
        const diffMs = now.getTime() - created.getTime();
        const diffMinutes = Math.max(0, Math.floor(diffMs / 60000));

        if (diffMinutes < 1) return 'now';
        if (diffMinutes < 60) return diffMinutes + ' ' + (diffMinutes === 1 ? 'minute' : 'minutes') + ' ago';

        const diffHours = Math.floor(diffMinutes / 60);
        if (diffHours < 24 && created.toDateString() === now.toDateString()) {
            return diffHours + ' ' + (diffHours === 1 ? 'hour' : 'hours') + ' ago';
        }

        const yesterday = new Date(now);
        yesterday.setDate(now.getDate() - 1);
        if (created.toDateString() === yesterday.toDateString()) {
            return 'yesterday';
        }

        return created.toLocaleDateString('en-US', { day: '2-digit', month: 'short' });
    }

    function renderNotifications(payload) {
        const unreadCount = Number(payload.unread_count || 0);

        notificationCount?.classList.toggle('hidden', unreadCount <= 0);
        notificationDot?.classList.toggle('hidden', unreadCount <= 0);

        if (notificationCount) {
            notificationCount.textContent = unreadCount > 9 ? '9+' : String(unreadCount);
        }

        const items = payload.notifications || [];

        if (!notificationList) {
            return;
        }

        if (!items.length) {
            notificationList.innerHTML = `<p class="dashboard-notification-empty rounded-xl border border-dashed border-app-border bg-app-soft text-app-muted">${escapeNotificationText(notificationEmptyText)}</p>`;
            return;
        }

        notificationList.innerHTML = items.map((item) => `
            <a href="${notificationHref(item)}" data-notification-id="${item.id}" data-unread="${Number(item.is_read) ? '0' : '1'}" class="dashboard-notification-item rounded-xl border ${Number(item.is_read) ? 'border-app-border bg-app-soft' : 'is-unread border-app-focus bg-app-surface'} hover:border-app-focus hover:bg-app-input hover:shadow-card">
                <span class="dashboard-notification-icon is-${notificationTone(item)}">${notificationIcon(item.type || item.reference_type)}</span>
                <span class="dashboard-notification-content">
                    <span class="dashboard-notification-meta">
                        <span class="dashboard-notification-type text-app-muted">${escapeNotificationText(item.type || 'system')}</span>
                        <time class="dashboard-notification-time" datetime="${escapeNotificationText(item.created_at || '')}">${escapeNotificationText(notificationTimeLabel(item.created_at))}</time>
                    </span>
                    <strong class="dashboard-notification-item-title text-app-text">${escapeNotificationText(item.title || 'Notification')}</strong>
                    <span class="dashboard-notification-message text-app-secondary">${escapeNotificationText(item.message || '')}</span>
                    ${shouldShowMore(item.message) ? '<span class="dashboard-notification-more">see more</span>' : ''}
                </span>
            </a>
        `).join('');
    }

    function closeNotificationPanel() {
        notificationBtn?.setAttribute('aria-expanded', 'false');
        notificationPanel?.classList.add('invisible', 'opacity-0', 'scale-95');
    }

    async function pollNotifications() {
        if (document.hidden || !notificationJsonUrl) {
            return;
        }

        try {
            const response = await fetch(notificationJsonUrl, {
                headers: { 'Accept': 'application/json' }
            });
            renderNotifications(await response.json());
        } catch (error) {
            // Keep the dashboard quiet if polling fails temporarily.
        }
    }

    notificationBtn?.addEventListener('click', (event) => {
        event.stopPropagation();
        document.querySelectorAll('.home-profile-btn, .tb-profile-btn').forEach((btn) => {
            btn.setAttribute('aria-expanded', 'false');
        });
        const isOpen = notificationBtn.getAttribute('aria-expanded') === 'true';
        notificationBtn.setAttribute('aria-expanded', String(!isOpen));
        notificationPanel?.classList.toggle('invisible', isOpen);
        notificationPanel?.classList.toggle('opacity-0', isOpen);
        notificationPanel?.classList.toggle('scale-95', isOpen);
    });

    notificationPanel?.addEventListener('click', (event) => {
        event.stopPropagation();
    });

    document.addEventListener('click', () => {
        if (notificationBtn?.getAttribute('aria-expanded') === 'true') {
            closeNotificationPanel();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeNotificationPanel();
        }
    });

    notificationList?.addEventListener('click', (event) => {
        const link = event.target.closest('[data-notification-id]');

        if (!link || !notificationMarkReadUrl) {
            return;
        }

        fetch(notificationMarkReadUrl + encodeURIComponent(link.dataset.notificationId), {
            method: 'POST',
            headers: { 'Accept': 'application/json' }
        }).catch(() => {});
    });

    pollNotifications();
    setInterval(pollNotifications, 10000);
})();
</script>
