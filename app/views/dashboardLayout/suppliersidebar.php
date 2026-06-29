<?php
$supplier = $supplier ?? [];
$dashboardData = $dashboardData ?? [];
$stats = $dashboardData['stats'] ?? [];

$supplierNameRaw = $supplier['shop_name'] ?? 'Supplier Dashboard';
$supplierName = htmlspecialchars($supplierNameRaw, ENT_QUOTES, 'UTF-8');
$ownerEmail = htmlspecialchars($supplier['owner_email'] ?? ($_SESSION['session_email'] ?? ''), ENT_QUOTES, 'UTF-8');
$profileStatus = strtolower($supplier['status'] ?? 'verified');
$paymentStatus = strtolower($supplier['payment_status'] ?? 'paid');
$isAvailable = !empty($supplier['is_available']);
$pendingBookings = (int)($stats['pending_bookings'] ?? 0);
$initialsSource = trim($supplierNameRaw) !== '' ? $supplierNameRaw : 'Supplier';
$supplierInitials = strtoupper(substr($initialsSource, 0, 1));
$currentPath = trim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
$calendarPathActive = strpos($currentPath, 'supplier/calendar') !== false || strpos($currentPath, 'supplier/serviceCalendar') !== false;
$bookingsPathActive = strpos($currentPath, 'supplier/bookings') !== false
    || strpos($currentPath, 'supplier/bookingDetail') !== false;
$assignmentsPathActive = strpos($currentPath, 'supplier/assignments') !== false;
$notificationsPathActive = strpos($currentPath, 'supplier/notifications') !== false
    || strpos($currentPath, 'supplier/notification') !== false;
$reviewsPathActive = strpos($currentPath, 'supplier/reviews') !== false;
$servicesPathActive = strpos($currentPath, 'supplier/services') !== false
    || strpos($currentPath, 'supplier/serviceDetail') !== false
    || strpos($currentPath, 'supplier/serviceCalendar') !== false;
$profilePathActive = strpos($currentPath, 'supplier/profile') !== false;
$dashboardSearchPlaceholder = $dashboardSearchPlaceholder ?? 'Search bookings, services...';
$dashboardSearchAction = $dashboardSearchAction ?? URLROOT . '/supplier/bookings';
$notificationConfig = $notificationConfig ?? [
    'role' => 'supplier',
    'reviewUrl' => URLROOT . '/supplier/notifications',
    'defaultUrl' => URLROOT . '/supplier/dashboard',
    'detailUrlBase' => URLROOT . '/supplier/notification/',
    'referenceUrls' => [
        'booking' => URLROOT . '/supplier/bookingDetail/',
        'service' => URLROOT . '/supplier/serviceDetail/',
        'payment' => URLROOT . '/supplier/dashboard?payment=',
        'publish_request' => URLROOT . '/supplier/serviceDetail/',
    ],
];

if (!function_exists('dashboard_role_badge_class')) {
    function dashboard_role_badge_class($status)
    {
        $status = strtolower((string)$status);

        if (in_array($status, ['verified', 'approved', 'paid', 'confirmed', 'completed', 'success', 'active'], true)) {
            return 'bg-app-soft text-app-success';
        }

        if (in_array($status, ['pending', 'pending_payment', 'in_progress', 'processing'], true)) {
            return 'bg-app-surface text-app-warning';
        }

        if (in_array($status, ['cancelled', 'rejected', 'failed', 'banned'], true)) {
            return 'bg-app-danger-soft text-app-danger';
        }

        return 'bg-app-soft text-app-secondary';
    }
}

if (!function_exists('dashboard_supplier_path_matches')) {
    function dashboard_supplier_path_matches($path, $currentPath, $exact = false)
    {
        $target = trim($path, '/');
        $current = trim($currentPath, '/');

        if ($exact) {
            return $current === $target || substr($current, -strlen('/' . $target)) === '/' . $target;
        }

        return strpos($current, $target) !== false;
    }

    function dashboard_supplier_nav_class($path, $currentPath, $exact = false)
    {
        $base = 'flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition-all duration-300 ease-[cubic-bezier(0.19,1,0.22,1)]';

        return dashboard_supplier_path_matches($path, $currentPath, $exact)
            ? $base . ' bg-app-primary text-app-white shadow-sm'
            : $base . ' text-app-text hover:bg-app-sidebar-hover hover:shadow-sm';
    }
}
?>
<style>
    .supplier-sidebar,
    .supplier-main {
        font-family: 'DM Sans', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
        font-variant-numeric: tabular-nums;
    }

    .supplier-sidebar {
        color: #111827;
        scrollbar-width: thin;
        scrollbar-color: #d8c8bb transparent;
    }

    .supplier-profile-panel {
        padding: 20px;
    }

    .supplier-profile-shell > img,
    .supplier-profile-shell > div:first-child {
        width: 40px;
        height: 40px;
        flex: 0 0 40px;
    }

    .supplier-sidebar-name {
        font-size: 14px;
        line-height: 1.35;
    }

    .supplier-sidebar-email {
        margin-top: 0;
        font-size: 12px;
        line-height: 1.35;
    }

    .supplier-sidebar-section {
        padding: 20px 20px 0;
    }

    .supplier-sidebar-section p {
        padding: 0 12px;
        color: #b79c8b;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: .2em;
    }

    .supplier-sidebar-nav {
        display: grid;
        gap: 6px;
        padding: 12px 16px;
    }

    .supplier-sidebar-nav > a,
    .supplier-sidebar-group-trigger {
        min-height: 44px;
        border-radius: 12px;
        padding: 0 16px;
        font-size: 14px;
        font-weight: 500;
    }

    .supplier-sidebar-nav > a:hover,
    .supplier-sidebar-group-trigger:hover {
        background: var(--color-app-sidebar-hover, #eddecc);
        box-shadow: none;
    }

    .supplier-sidebar-nav svg {
        width: 16px;
        height: 16px;
    }

    .supplier-topbar-title {
        display: flex;
        min-width: 0;
        align-items: flex-start;
        gap: 12px;
    }

    .supplier-mobile-menu-btn,
    .supplier-sidebar-backdrop {
        display: none;
    }

    @media (max-width: 1024px) {
        body {
            grid-template-columns: 1fr !important;
        }

        .supplier-sidebar {
            position: fixed;
            inset: 0 auto 0 0;
            z-index: 70;
            width: 280px;
            max-width: 84vw;
            transform: translateX(-100%);
            transition: transform 350ms cubic-bezier(0.19, 1, 0.22, 1);
            box-shadow: 24px 0 60px rgba(34, 24, 19, 0.18);
            overflow-y: auto;
        }

        body.supplier-sidebar-open .supplier-sidebar {
            transform: translateX(0);
        }

        body.supplier-sidebar-open {
            overflow: hidden;
        }

        .supplier-sidebar-backdrop {
            position: fixed;
            inset: 0;
            z-index: 60;
            border: 0;
            background: rgba(34, 24, 19, 0.42);
            cursor: pointer;
        }

        body.supplier-sidebar-open .supplier-sidebar-backdrop {
            display: block;
        }

        .supplier-mobile-menu-btn {
            display: inline-flex;
            min-height: 38px;
            width: 38px;
            align-items: center;
            justify-content: center;
            border-radius: 0.75rem;
            border: 1px solid var(--color-app-border, #e5e7eb);
            background: var(--color-app-input, #fcf8f5);
            color: var(--color-app-text, #1f2937);
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.08);
        }

        .supplier-dashboard-topbar { gap:8px; padding:12px; }

        .supplier-topbar-title { align-items:center; gap:10px; }

        .supplier-topbar-actions {
            width: 100%;
            gap: 0.5rem;
        }

        .supplier-topbar-actions .relative {
            flex: 1;
            min-width: 0;
        }

        .supplier-topbar-actions input[type="search"] {
            width: 100%;
        }

        .supplier-topbar-actions .dashboard-search-shortcut {
            display: none;
        }

        .supplier-main .px-6 {
            padding-left: 1rem;
            padding-right: 1rem;
        }
    }

    .supplier-sidebar-group-trigger {
        width: 100%;
        border: 0;
        cursor: pointer;
    }

    .supplier-sidebar-subnav {
        display: none;
        margin: 4px 0 8px 34px;
        padding: 4px 0 4px 12px;
        border-left: 1px solid var(--color-app-panel-border, #e5e7eb);
    }

    .supplier-sidebar-group[data-open="true"] .supplier-sidebar-subnav {
        display: grid;
        gap: 2px;
    }

    .supplier-sidebar-subnav a {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        min-height: 36px;
        border-radius: 8px;
        padding: 0 12px;
        font-size: 14px;
        font-weight: 400;
        color: var(--color-app-muted, #6b7280);
        transition: all 300ms cubic-bezier(0.19, 1, 0.22, 1);
    }

    .supplier-sidebar-subnav a:hover,
    .supplier-sidebar-subnav a.is-active {
        background: var(--color-app-sidebar-hover, #eddecc);
        color: var(--color-app-primary, #6d4c5b);
    }

    .supplier-sidebar-group[data-open="true"] .supplier-sidebar-group-chevron {
        transform: rotate(180deg);
    }

    @media (max-width: 480px) {
        .supplier-dashboard-topbar {
            padding: 0.5rem 0.5rem;
            gap: 0.375rem;
        }

        .supplier-topbar-actions {
            flex-wrap: nowrap;
        }

        .supplier-topbar-actions .relative {
            min-width: 0;
        }

        .supplier-topbar-title .min-w-0 nav {
            font-size: 9px;
        }
    }

    @media (min-width: 1025px) {
        .supplier-sidebar {
            position: sticky;
            top: 0;
            height: 100vh;
            width: 280px;
            overflow-y: auto;
        }
    }
</style>

<aside id="supplierSidebar" class="supplier-sidebar border-r border-r-app-sidebar bg-app-sidebar">
    <div class="flex h-full flex-col">
        <div class="supplier-profile-panel relative border-b border-b-app-panel-border bg-app-panel">
            <div class="supplier-profile-shell flex items-center gap-3">
                <?php $sidebarAvatar = $_SESSION['session_avatar'] ?? null; ?>
                <?php if (!empty($sidebarAvatar)): ?>
                    <img src="<?= htmlspecialchars($sidebarAvatar, ENT_QUOTES, 'UTF-8') ?>"
                         alt="<?= $supplierName ?>"
                         class="h-10 w-10 rounded-full object-cover shadow-sm">
                <?php else: ?>
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-app-primary text-sm font-semibold text-app-white shadow-sm"><?= htmlspecialchars($supplierInitials, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
                <div class="min-w-0">
                    <p class="supplier-sidebar-name truncate text-sm font-semibold text-app-text"><?= $supplierName ?></p>
                    <p class="supplier-sidebar-email truncate text-xs text-app-muted"><?= $ownerEmail ?></p>
                </div>
            </div>
        
        </div>

        <div class="supplier-sidebar-section">
            <p class="uppercase text-app-header-muted">Profile</p>
        </div>

        <nav class="supplier-sidebar-nav">
            <a href="<?= URLROOT ?>/supplier/profile" title="My Profile" class="<?= $profilePathActive ? 'flex items-center gap-3 transition bg-app-primary text-app-white shadow-sm' : 'flex items-center gap-3 text-app-text transition hover:bg-app-input' ?>">
                <i data-lucide="circle-user" class="h-4 w-4 text-app-header-muted"></i>
                <span class="supplier-sidebar-label flex-1">My Profile</span>
                <i data-lucide="chevron-right" class="supplier-sidebar-chevron h-4 w-4 text-app-header-muted"></i>
            </a>
        </nav>

        <div class="supplier-sidebar-section">
            <p class="uppercase text-app-header-muted">Workspace</p>
        </div>

        <nav class="supplier-sidebar-nav">
            <a href="<?= URLROOT ?>/supplier/dashboard" title="Dashboard" class="<?= dashboard_supplier_nav_class('supplier/dashboard', $currentPath, true) ?>">
                <i data-lucide="layout-dashboard" class="h-4 w-4"></i>
                <span class="supplier-sidebar-label flex-1">Dashboard</span>
            </a>
            <a href="<?= URLROOT ?>/supplier/bookings" title="Bookings" class="<?= $bookingsPathActive ? 'flex items-center gap-3 transition bg-app-primary text-app-white shadow-sm' : 'flex items-center gap-3 text-app-text transition hover:bg-app-input' ?>">
                <i data-lucide="calendar-check" class="h-4 w-4 <?= $bookingsPathActive ? '' : 'text-app-header-muted' ?>"></i>
                <span class="supplier-sidebar-label flex-1">Bookings</span>
                <?php if ($pendingBookings > 0): ?>
                    <span class="supplier-sidebar-badge rounded-full bg-app-surface px-2 py-0.5 text-[10px] font-semibold text-app-warning"><?= $pendingBookings ?></span>
                <?php endif; ?>
            </a>
            <a href="<?= URLROOT ?>/supplier/assignments" title="My Assignments" class="<?= $assignmentsPathActive ? 'flex items-center gap-3 transition bg-app-primary text-app-white shadow-sm' : 'flex items-center gap-3 text-app-text transition hover:bg-app-input' ?>">
                <i data-lucide="clipboard-check" class="h-4 w-4 <?= $assignmentsPathActive ? '' : 'text-app-header-muted' ?>"></i>
                <span class="supplier-sidebar-label flex-1">My Assignments</span>
            </a>
            <a href="<?= URLROOT ?>/supplier/notifications" title="Notifications" class="<?= $notificationsPathActive ? 'flex items-center gap-3 transition bg-app-primary text-app-white shadow-sm' : 'flex items-center gap-3 text-app-text transition hover:bg-app-input' ?>">
                <i data-lucide="bell" class="h-4 w-4 <?= $notificationsPathActive ? '' : 'text-app-header-muted' ?>"></i>
                <span class="supplier-sidebar-label flex-1">Notifications</span>
            </a>
            <div class="supplier-sidebar-group" data-open="<?= $servicesPathActive ? 'true' : 'false' ?>">
                <button type="button"
                        class="supplier-sidebar-group-trigger <?= $servicesPathActive ? 'flex items-center gap-3 transition bg-app-primary text-app-white shadow-sm' : 'flex items-center gap-3 transition text-app-text hover:bg-app-input' ?>"
                        aria-expanded="<?= $servicesPathActive ? 'true' : 'false' ?>"
                        aria-controls="supplierServicesSubnav"
                        title="Services">
                    <i data-lucide="briefcase-business" class="h-4 w-4"></i>
                    <span class="supplier-sidebar-label flex-1 text-left">Services</span>
                    <i data-lucide="chevron-down" class="supplier-sidebar-chevron supplier-sidebar-group-chevron h-4 w-4 transition-transform"></i>
                </button>
                <div id="supplierServicesSubnav" class="supplier-sidebar-subnav supplier-sidebar-label">
                    <a href="<?= URLROOT ?>/supplier/services"
                       class="<?= dashboard_supplier_path_matches('supplier/services', $currentPath, true) ? 'is-active' : '' ?>">
                        <i data-lucide="list" class="h-3.5 w-3.5"></i>
                        Manage services
                    </a>
                    <a href="<?= URLROOT ?>/supplier/calendar"
                       class="<?= strpos($currentPath, 'supplier/calendar') !== false ? 'is-active' : '' ?>">
                        <i data-lucide="calendar-days" class="h-3.5 w-3.5"></i>
                        Service calendar
                    </a>
                </div>
            </div>
            <a href="<?= URLROOT ?>/supplier/reviews" title="Reviews" class="<?= $reviewsPathActive ? 'flex items-center gap-3 transition bg-app-primary text-app-white shadow-sm' : 'flex items-center gap-3 text-app-text transition hover:bg-app-input' ?>">
                <i data-lucide="star" class="h-4 w-4 <?= $reviewsPathActive ? '' : 'text-app-header-muted' ?>"></i>
                <span class="supplier-sidebar-label flex-1">Reviews</span>
            </a>
        </nav>

        <div class="supplier-sidebar-section">
            <p class="uppercase text-app-header-muted">Finance</p>
        </div>

        <nav class="supplier-sidebar-nav">
            <a href="<?= URLROOT ?>/supplier/earnings" title="Earnings and withdrawals" class="flex items-center gap-3 text-app-text transition hover:bg-app-input">
                <i data-lucide="banknote" class="h-4 w-4 text-app-header-muted"></i>
                <span class="supplier-sidebar-label flex-1">Earnings</span>
            </a>
            <a href="<?= URLROOT ?>/supplier/paymentHistory" title="Customer payment history" class="flex items-center gap-3 text-app-text transition hover:bg-app-input">
                <i data-lucide="receipt" class="h-4 w-4 text-app-header-muted"></i>
                <span class="supplier-sidebar-label flex-1">Payment History</span>
            </a>
        </nav>

        <div class="supplier-sidebar-section">
            <p class="uppercase text-app-header-muted">System</p>
        </div>

        <nav class="supplier-sidebar-nav">
            <a href="<?= URLROOT ?>/supplier/settings" title="Settings" class="<?= strpos($currentPath, 'supplier/settings') !== false ? 'flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition-all duration-300 ease-[cubic-bezier(0.19,1,0.22,1)] bg-app-primary text-app-white shadow-sm' : 'flex items-center gap-3 text-app-text transition hover:bg-app-input' ?>">
                <i data-lucide="settings" class="h-4 w-4 <?= strpos($currentPath, 'supplier/settings') !== false ? '' : 'text-app-header-muted' ?>"></i>
                <span class="supplier-sidebar-label flex-1">Settings</span>
            </a>
        </nav>

        <div class="mt-auto border-t border-app-panel-border px-4 py-4">
            <a href="<?= URLROOT ?>/supplier/logout" title="Log Out" class="group flex w-full items-center gap-3 rounded-xl px-4 py-3 transition hover:bg-app-input hover:shadow-sm">
                <span class="flex h-8 w-8 items-center justify-center rounded-xl text-app-danger transition group-hover:bg-app-danger-soft">
                    <i data-lucide="log-out" class="h-5 w-5"></i>
                </span>
                <span class="supplier-sidebar-label flex-1 text-left text-sm font-semibold text-app-text">Log Out</span>
                <i data-lucide="chevron-right" class="supplier-sidebar-chevron h-4 w-4 text-app-header-muted"></i>
            </a>
        </div>
    </div>
</aside>
<button type="button" id="supplierSidebarBackdrop" class="supplier-sidebar-backdrop" aria-label="Close supplier navigation"></button>

<main class="supplier-main overflow-y-auto">
    <div class="supplier-dashboard-topbar sticky top-0 z-40 flex flex-col gap-4 border-b border-app-border bg-app-sidebar/95 px-6 py-[18px] backdrop-blur-sm sm:flex-row sm:items-center sm:justify-between">
        <div class="supplier-topbar-title">
            <button type="button" id="supplierSidebarToggle" class="supplier-mobile-menu-btn" aria-controls="supplierSidebar" aria-expanded="false" aria-label="Open supplier navigation">
                <i data-lucide="menu" class="h-5 w-5"></i>
            </button>
            <div class="min-w-0">
            <?php
            $dashboardBreadcrumbs = $dashboardBreadcrumbs ?? [
                ['label' => $dashboardTitle ?? 'Supplier', 'url' => null],
                ['label' => $dashboardCrumb ?? 'Overview', 'url' => null],
            ];
            ?>
            <nav aria-label="Breadcrumb" class="mb-1 flex flex-wrap items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.18em] text-app-header-muted">
                <?php foreach ($dashboardBreadcrumbs as $index => $crumb): ?>
                    <?php
                    $crumbLabel = htmlspecialchars($crumb['label'] ?? '', ENT_QUOTES, 'UTF-8');
                    $crumbUrl = $crumb['url'] ?? null;
                    $isLastCrumb = $index === count($dashboardBreadcrumbs) - 1;
                    ?>
                    <?php if ($index > 0): ?>
                        <span class="text-app-header-muted">/</span>
                    <?php endif; ?>
                    <?php if (!$isLastCrumb && $crumbUrl): ?>
                        <a href="<?= htmlspecialchars($crumbUrl, ENT_QUOTES, 'UTF-8') ?>" class="text-app-header-muted transition hover:text-app-text"><?= $crumbLabel ?></a>
                    <?php else: ?>
                        <span class="<?= $isLastCrumb ? 'text-app-text' : 'text-app-header-muted' ?>"><?= $crumbLabel ?></span>
                    <?php endif; ?>
                <?php endforeach; ?>
            </nav>
            </div>
        </div>

        <div class="supplier-topbar-actions flex flex-wrap items-center gap-3">
            <?php require APPROOT . '/views/dashboardLayout/dashboardSearch.php'; ?>
            <?php require APPROOT . '/views/dashboardLayout/customerNotification.php'; ?>
        </div>
    </div>

    <script>
        lucide.createIcons();

        const dashboardSearch = document.getElementById('dashboard-search');
        const supplierSidebarToggle = document.getElementById('supplierSidebarToggle');
        const supplierSidebarBackdrop = document.getElementById('supplierSidebarBackdrop');
        const supplierSidebarServiceTrigger = document.querySelector('.supplier-sidebar-group-trigger');

        function setSupplierSidebarOpen(isOpen) {
            document.body.classList.toggle('supplier-sidebar-open', isOpen);
            supplierSidebarToggle?.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        }

        supplierSidebarToggle?.addEventListener('click', () => {
            setSupplierSidebarOpen(!document.body.classList.contains('supplier-sidebar-open'));
        });

        supplierSidebarBackdrop?.addEventListener('click', () => {
            setSupplierSidebarOpen(false);
        });

        supplierSidebarServiceTrigger?.addEventListener('click', () => {
            const group = supplierSidebarServiceTrigger.closest('.supplier-sidebar-group');
            const nextOpen = group?.dataset.open !== 'true';
            if (group) group.dataset.open = nextOpen ? 'true' : 'false';
            supplierSidebarServiceTrigger.setAttribute('aria-expanded', nextOpen ? 'true' : 'false');
        });

        document.querySelectorAll('#supplierSidebar a').forEach((link) => {
            link.addEventListener('click', () => {
                if (window.matchMedia('(max-width: 1024px)').matches) {
                    setSupplierSidebarOpen(false);
                }
            });
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                setSupplierSidebarOpen(false);
            }
        });

        document.addEventListener('keydown', (event) => {
            const isShortcut = (event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k';

            if (!isShortcut || !dashboardSearch) {
                return;
            }

            event.preventDefault();
            dashboardSearch.focus();
            dashboardSearch.select();
        });
    </script>

    <?php if (isset($dashboardContent) && is_callable($dashboardContent)): ?>
        <?php
        // ── Supplier warning banner ──
        $supplierWarningLevel = (int)($supplier['warning_level'] ?? 0);
        $missedResponseCount = (int)($supplier['missed_response_count'] ?? 0);
        ?>
        <?php if ($supplierWarningLevel > 0): ?>
        <div id="supplier-warning-banner" style="margin:0;padding:12px 24px;display:flex;align-items:flex-start;gap:10px;font-size:13px;font-weight:600;line-height:1.5;
            <?= $supplierWarningLevel >= 2
                ? 'background:#FEF2F2;border-bottom:1px solid #fca5a5;color:#991b1b;'
                : 'background:#FFFBEB;border-bottom:1px solid #fde68a;color:#92400e;' ?>">
            <svg style="width:18px;height:18px;flex-shrink:0;margin-top:2px" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M8 1.5L1 14h14L8 1.5z"/><path d="M8 6v3M8 11.5h.01"/>
            </svg>
            <div style="flex:1;min-width:0">
                <?php if ($supplierWarningLevel >= 2): ?>
                    <strong>Final Warning:</strong> You have <?= $missedResponseCount ?> bookings auto-cancelled due to non-response.
                    Your account may be restricted. Please respond to all booking requests within 24 hours.
                <?php else: ?>
                    <strong>Warning:</strong> You have <?= $missedResponseCount ?> bookings auto-cancelled due to non-response.
                    Please respond to all booking requests within 24 hours to avoid account restrictions.
                <?php endif; ?>
            </div>
            <button type="button" onclick="document.getElementById('supplier-warning-banner').style.display='none'"
                    style="background:none;border:none;cursor:pointer;color:inherit;opacity:.6;padding:2px;flex-shrink:0"
                    aria-label="Dismiss warning">
                <svg style="width:16px;height:16px" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4l8 8M12 4l-8 8"/></svg>
            </button>
        </div>
        <?php endif; ?>
        <div class="<?= htmlspecialchars($dashboardContentClass ?? 'px-6 py-6', ENT_QUOTES, 'UTF-8') ?>">
            <?php $dashboardContent(); ?>
        </div>
    <?php endif; ?>
</main>
<script>if (window.lucide) lucide.createIcons();</script>
