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
$notificationReviewUrl = $notificationConfig['reviewUrl']
    ?? $notificationReviewUrl
    ?? ($notificationBasePath ? URLROOT . $notificationBasePath . '/dashboard' : '#');
$notificationDefaultUrl = $notificationConfig['defaultUrl']
    ?? $notificationDefaultUrl
    ?? ($notificationBasePath ? URLROOT . $notificationBasePath . '/dashboard' : '#');
$notificationReferenceUrls = $notificationConfig['referenceUrls'] ?? $notificationReferenceUrls ?? [];
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
        padding-top: 0.75rem;
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
    const notificationEmptyText = <?= json_encode($notificationEmptyText) ?>;

    function notificationHref(item) {
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
            <a href="${notificationHref(item)}" data-notification-id="${item.id}" class="dashboard-notification-item rounded-xl border ${Number(item.is_read) ? 'border-app-border bg-app-soft' : 'border-app-focus bg-app-surface'} hover:border-app-focus hover:bg-app-input hover:shadow-card">
                <span class="dashboard-notification-type text-app-muted">${escapeNotificationText(item.type || 'system')}</span>
                <strong class="dashboard-notification-item-title text-app-text">${escapeNotificationText(item.title || 'Notification')}</strong>
                <span class="dashboard-notification-message text-app-secondary">${escapeNotificationText(item.message || '')}</span>
            </a>
        `).join('');
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

    notificationBtn?.addEventListener('click', () => {
        const isOpen = notificationBtn.getAttribute('aria-expanded') === 'true';
        notificationBtn.setAttribute('aria-expanded', String(!isOpen));
        notificationPanel?.classList.toggle('invisible', isOpen);
        notificationPanel?.classList.toggle('opacity-0', isOpen);
        notificationPanel?.classList.toggle('scale-95', isOpen);
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
