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
$notificationsPathActive = strpos($currentPath, 'supplier/notifications') !== false
    || strpos($currentPath, 'supplier/notification') !== false;
$reviewsPathActive = strpos($currentPath, 'supplier/reviews') !== false;
$servicesPathActive = strpos($currentPath, 'supplier/services') !== false
    || strpos($currentPath, 'supplier/serviceDetail') !== false
    || strpos($currentPath, 'supplier/serviceCalendar') !== false;
$profilePathActive = strpos($currentPath, 'supplier/profile') !== false;
$servicesPackageTabActive = strpos($currentPath, 'supplier/services') !== false && ($_GET['tab'] ?? '') === 'packages';
$dashboardSearchPlaceholder = $dashboardSearchPlaceholder ?? 'Search bookings, services...';
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
        $base = 'flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition';

        return dashboard_supplier_path_matches($path, $currentPath, $exact)
            ? $base . ' bg-app-primary text-app-white shadow-sm'
            : $base . ' text-app-text hover:bg-app-input hover:shadow-sm';
    }
}
?>
<style>
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
            transition: transform 180ms ease;
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
            background: var(--color-app-input, #fff);
            color: var(--color-app-text, #1f2937);
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.08);
        }

        .supplier-dashboard-topbar {
            gap: 0.5rem;
            padding: 0.75rem 0.75rem;
        }

        .supplier-topbar-title {
            display: flex;
            min-width: 0;
            align-items: flex-start;
            gap: 0.75rem;
        }

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

        .supplier-topbar-actions .relative .rounded-md {
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
        margin: 0.25rem 0 0.5rem 2.15rem;
        padding-left: 0.75rem;
        border-left: 1px solid var(--color-app-border, #e5e7eb);
    }

    .supplier-sidebar-group[data-open="true"] .supplier-sidebar-subnav {
        display: grid;
        gap: 0.25rem;
    }

    .supplier-sidebar-subnav a {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        min-height: 34px;
        border-radius: 0.75rem;
        padding: 0 0.75rem;
        font-size: 12px;
        font-weight: 700;
        color: var(--color-app-muted, #6b7280);
        transition: background 160ms ease, color 160ms ease;
    }

    .supplier-sidebar-subnav a:hover,
    .supplier-sidebar-subnav a.is-active {
        background: var(--color-app-input, #fff);
        color: var(--color-app-text, #1f2937);
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
        .supplier-collapse-btn {
            display: inline-flex;
        }

        body.supplier-sidebar-collapsed {
            grid-template-columns: 84px 1fr !important;
        }

        .supplier-sidebar {
            position: sticky;
            top: 0;
            height: 100vh;
            width: 280px;
            overflow-y: auto;
            transition: width 180ms ease;
        }

        body.supplier-sidebar-collapsed .supplier-sidebar {
            width: 84px;
        }

        body.supplier-sidebar-collapsed .supplier-sidebar-label,
        body.supplier-sidebar-collapsed .supplier-sidebar-section,
        body.supplier-sidebar-collapsed .supplier-sidebar-email,
        body.supplier-sidebar-collapsed .supplier-sidebar-chevron,
        body.supplier-sidebar-collapsed .supplier-sidebar-subnav,
        body.supplier-sidebar-collapsed .supplier-sidebar-badge {
            display: none;
        }

        body.supplier-sidebar-collapsed .supplier-sidebar-name {
            position: absolute;
            width: 1px;
            height: 1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
        }

        body.supplier-sidebar-collapsed .supplier-sidebar nav {
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }

        body.supplier-sidebar-collapsed .supplier-sidebar a {
            justify-content: center;
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }

        body.supplier-sidebar-collapsed .supplier-sidebar-group-trigger {
            justify-content: center;
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }

        body.supplier-sidebar-collapsed .supplier-profile-shell {
            justify-content: center;
        }

        body.supplier-sidebar-collapsed .supplier-collapse-btn {
            right: -14px;
            transform: rotate(180deg);
        }
    }
</style>

<aside id="supplierSidebar" class="supplier-sidebar border-r border-r-app-sidebar bg-app-sidebar">
    <div class="flex h-full flex-col">
        <div class="relative border-b border-b-app-panel-border bg-app-panel px-5 py-5">
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

        <div class="supplier-sidebar-section px-5 pt-5">
            <p class="px-3 text-[11px] font-semibold uppercase tracking-[0.2em] text-app-header-muted">Profile</p>
        </div>

        <nav class="px-4 py-3 space-y-1.5">
            <a href="<?= URLROOT ?>/supplier/profile" title="My Profile" class="<?= $profilePathActive ? 'flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition bg-app-primary text-app-white shadow-sm' : 'flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-app-text transition hover:bg-app-input hover:shadow-sm' ?>">
                <i data-lucide="circle-user" class="h-4 w-4 text-app-header-muted"></i>
                <span class="supplier-sidebar-label flex-1">My Profile</span>
                <i data-lucide="chevron-right" class="supplier-sidebar-chevron h-4 w-4 text-app-header-muted"></i>
            </a>
        </nav>

        <div class="supplier-sidebar-section px-5 pt-5">
            <p class="px-3 text-[11px] font-semibold uppercase tracking-[0.2em] text-app-header-muted">Workspace</p>
        </div>

        <nav class="px-4 py-3 space-y-1.5">
            <a href="<?= URLROOT ?>/supplier/dashboard" title="Dashboard" class="<?= dashboard_supplier_nav_class('supplier/dashboard', $currentPath, true) ?>">
                <i data-lucide="layout-dashboard" class="h-4 w-4"></i>
                <span class="supplier-sidebar-label flex-1">Dashboard</span>
            </a>
            <a href="<?= URLROOT ?>/supplier/bookings" title="Bookings" class="<?= $bookingsPathActive ? 'flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition bg-app-primary text-app-white shadow-sm' : 'flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-app-text transition hover:bg-app-input hover:shadow-sm' ?>">
                <i data-lucide="calendar-check" class="h-4 w-4 <?= $bookingsPathActive ? '' : 'text-app-header-muted' ?>"></i>
                <span class="supplier-sidebar-label flex-1">Bookings</span>
                <?php if ($pendingBookings > 0): ?>
                    <span class="supplier-sidebar-badge rounded-full bg-app-surface px-2 py-0.5 text-[10px] font-semibold text-app-warning"><?= $pendingBookings ?></span>
                <?php endif; ?>
            </a>
            <a href="<?= URLROOT ?>/supplier/notifications" title="Notifications" class="<?= $notificationsPathActive ? 'flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition bg-app-primary text-app-white shadow-sm' : 'flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-app-text transition hover:bg-app-input hover:shadow-sm' ?>">
                <i data-lucide="bell" class="h-4 w-4 <?= $notificationsPathActive ? '' : 'text-app-header-muted' ?>"></i>
                <span class="supplier-sidebar-label flex-1">Notifications</span>
            </a>
            <div class="supplier-sidebar-group" data-open="<?= $servicesPathActive ? 'true' : 'false' ?>">
                <button type="button"
                        class="supplier-sidebar-group-trigger <?= $servicesPathActive ? 'flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition bg-app-primary text-app-white shadow-sm' : 'flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition text-app-text hover:bg-app-input hover:shadow-sm' ?>"
                        aria-expanded="<?= $servicesPathActive ? 'true' : 'false' ?>"
                        aria-controls="supplierServicesSubnav"
                        title="Services">
                    <i data-lucide="briefcase-business" class="h-4 w-4"></i>
                    <span class="supplier-sidebar-label flex-1 text-left">Services</span>
                    <i data-lucide="chevron-down" class="supplier-sidebar-chevron supplier-sidebar-group-chevron h-4 w-4 transition-transform"></i>
                </button>
                <div id="supplierServicesSubnav" class="supplier-sidebar-subnav supplier-sidebar-label">
                    <a href="<?= URLROOT ?>/supplier/services"
                       class="<?= dashboard_supplier_path_matches('supplier/services', $currentPath, true) && !$servicesPackageTabActive ? 'is-active' : '' ?>">
                        <i data-lucide="list" class="h-3.5 w-3.5"></i>
                        Manage services
                    </a>
                    <a href="<?= URLROOT ?>/supplier/calendar"
                       class="<?= strpos($currentPath, 'supplier/calendar') !== false ? 'is-active' : '' ?>">
                        <i data-lucide="calendar-days" class="h-3.5 w-3.5"></i>
                        Service calendar
                    </a>
                    <a href="<?= URLROOT ?>/supplier/services?tab=packages"
                       class="<?= $servicesPackageTabActive ? 'is-active' : '' ?>">
                        <i data-lucide="package" class="h-3.5 w-3.5"></i>
                        Packages
                    </a>
                </div>
            </div>
            <a href="#" title="Portfolio" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-app-text transition hover:bg-app-input hover:shadow-sm">
                <i data-lucide="image" class="h-4 w-4 text-app-header-muted"></i>
                <span class="supplier-sidebar-label flex-1">Portfolio</span>
            </a>
            <a href="<?= URLROOT ?>/supplier/reviews" title="Reviews" class="<?= $reviewsPathActive ? 'flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition bg-app-primary text-app-white shadow-sm' : 'flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-app-text transition hover:bg-app-input hover:shadow-sm' ?>">
                <i data-lucide="star" class="h-4 w-4 <?= $reviewsPathActive ? '' : 'text-app-header-muted' ?>"></i>
                <span class="supplier-sidebar-label flex-1">Reviews</span>
            </a>
        </nav>

        <div class="supplier-sidebar-section px-5 pt-5">
            <p class="px-3 text-[11px] font-semibold uppercase tracking-[0.2em] text-app-header-muted">Finance</p>
        </div>

        <nav class="px-4 py-3 space-y-1.5">
            <a href="#" title="Wallet" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-app-text transition hover:bg-app-input hover:shadow-sm">
                <i data-lucide="wallet" class="h-4 w-4 text-app-header-muted"></i>
                <span class="supplier-sidebar-label flex-1">Wallet</span>
            </a>
            <a href="#" title="Payments" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-app-text transition hover:bg-app-input hover:shadow-sm">
                <i data-lucide="receipt-text" class="h-4 w-4 text-app-header-muted"></i>
                <span class="supplier-sidebar-label flex-1">Payments</span>
            </a>
            <a href="#" title="Withdrawals" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-app-text transition hover:bg-app-input hover:shadow-sm">
                <i data-lucide="banknote" class="h-4 w-4 text-app-header-muted"></i>
                <span class="supplier-sidebar-label flex-1">Withdrawals</span>
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

            <?php require APPROOT . '/views/dashboardLayout/notification.php'; ?>
        </div>
    </div>

    <script>
        lucide.createIcons();

        const dashboardSearch = document.getElementById('dashboard-search');
        const supplierSidebarToggle = document.getElementById('supplierSidebarToggle');
        const supplierSidebarBackdrop = document.getElementById('supplierSidebarBackdrop');
        const supplierSidebarCollapse = document.getElementById('supplierSidebarCollapse');
        const supplierSidebarServiceTrigger = document.querySelector('.supplier-sidebar-group-trigger');

        function setSupplierSidebarCollapsed(isCollapsed) {
            document.body.classList.toggle('supplier-sidebar-collapsed', isCollapsed);
            supplierSidebarCollapse?.setAttribute('aria-expanded', isCollapsed ? 'false' : 'true');
            supplierSidebarCollapse?.setAttribute('aria-label', isCollapsed ? 'Expand supplier navigation' : 'Collapse supplier navigation');
            try {
                localStorage.setItem('supplierSidebarCollapsed', isCollapsed ? '1' : '0');
            } catch (error) {}
        }

        try {
            setSupplierSidebarCollapsed(localStorage.getItem('supplierSidebarCollapsed') === '1');
        } catch (error) {}

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

        supplierSidebarCollapse?.addEventListener('click', () => {
            if (window.matchMedia('(min-width: 1025px)').matches) {
                setSupplierSidebarCollapsed(!document.body.classList.contains('supplier-sidebar-collapsed'));
            }
        });

        supplierSidebarServiceTrigger?.addEventListener('click', () => {
            const group = supplierSidebarServiceTrigger.closest('.supplier-sidebar-group');
            const nextOpen = group?.dataset.open !== 'true';
            if (group) group.dataset.open = nextOpen ? 'true' : 'false';
            supplierSidebarServiceTrigger.setAttribute('aria-expanded', nextOpen ? 'true' : 'false');
            if (document.body.classList.contains('supplier-sidebar-collapsed')) {
                setSupplierSidebarCollapsed(false);
            }
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
        <div class="<?= htmlspecialchars($dashboardContentClass ?? 'px-6 py-6', ENT_QUOTES, 'UTF-8') ?>">
            <?php $dashboardContent(); ?>
        </div>
    <?php endif; ?>
</main>
