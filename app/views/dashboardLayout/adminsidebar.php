<?php
$adminEmail = htmlspecialchars($_SESSION['session_email'] ?? 'admin@example.com', ENT_QUOTES, 'UTF-8');
$adminName = htmlspecialchars($_SESSION['user_name'] ?? $_SESSION['session_name'] ?? 'Admin User', ENT_QUOTES, 'UTF-8');
$adminInitials = strtoupper(substr(trim($adminName) !== '' ? $adminName : 'Admin', 0, 1));
$currentPath = trim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
$paymentStatusFilter = (strpos($currentPath, 'admin/payments') !== false) ? ($_GET['status'] ?? 'pending') : '';
$dashboardSearchPlaceholder = $dashboardSearchPlaceholder ?? 'Search bookings, suppliers, payments...';
$dashboardSearchEndpoint = $dashboardSearchEndpoint ?? URLROOT . '/admin/globalSearch';
$notificationConfig = $notificationConfig ?? [
    'role' => 'admin',
    'reviewUrl' => URLROOT . '/admin/notifications',
    'defaultUrl' => URLROOT . '/admin/dashboard',
    'detailUrlBase' => URLROOT . '/admin/notification/',
    'referenceUrls' => [
        'supplier' => URLROOT . '/admin/supplier/',
        'payment' => URLROOT . '/admin/payments?payment=',
        'service' => URLROOT . '/admin/service/',
    ],
];

if (!function_exists('dashboard_admin_nav_class')) {
    function dashboard_admin_path_matches($path, $currentPath, $exact = false)
    {
        $target = trim($path, '/');
        $current = trim($currentPath, '/');

        if ($exact) {
            return $current === $target || substr($current, -strlen('/' . $target)) === '/' . $target;
        }

        return strpos($current, $target) !== false;
    }

    function dashboard_admin_nav_class($path, $currentPath, $exact = false)
    {
        $base = 'flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition-all duration-300 ease-[cubic-bezier(0.19,1,0.22,1)]';
        $isActive = dashboard_admin_path_matches($path, $currentPath, $exact);

        return $isActive
            ? $base . ' bg-app-primary text-app-white shadow-sm'
            : $base . ' text-app-text hover:bg-app-sidebar-hover hover:shadow-sm';
    }

    function dashboard_admin_subnav_class($path, $currentPath)
    {
        $base = 'admin-sidebar-subnav-link flex min-h-10 items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition-all duration-300 ease-[cubic-bezier(0.19,1,0.22,1)]';
        $isActive = dashboard_admin_path_matches($path, $currentPath);

        return $isActive
            ? $base . ' is-active bg-app-sidebar-hover text-app-primary'
            : $base . ' text-app-muted hover:bg-app-sidebar-hover hover:text-app-text';
    }

    function dashboard_admin_subnav_wrap_class($isOpen)
    {
        $base = 'admin-sidebar-subnav ml-8 mb-2 mt-1 border-l border-app-panel-border py-1 pl-3';
        return $base . ($isOpen ? ' grid gap-1' : ' hidden gap-1');
    }

    function dashboard_admin_subnav_link_class($isActive)
    {
        $base = 'admin-sidebar-subnav-link flex min-h-10 items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition-all duration-300 ease-[cubic-bezier(0.19,1,0.22,1)]';
        return $isActive
            ? $base . ' is-active bg-app-sidebar-hover text-app-primary'
            : $base . ' text-app-muted hover:bg-app-sidebar-hover hover:text-app-text';
    }
}
?>
<aside class="admin-sidebar-shell bg-app-sidebar border-r border-app-panel-border">
    <div class="flex h-full min-h-0 flex-col">
        <div class="border-b border-app-panel-border bg-app-panel px-5 py-5">
            <div class="flex items-center gap-3">
                <?php $sidebarAvatar = $_SESSION['session_avatar'] ?? null; ?>
                <?php if (!empty($sidebarAvatar)): ?>
                    <img src="<?= htmlspecialchars($sidebarAvatar, ENT_QUOTES, 'UTF-8') ?>"
                         alt="<?= $adminName ?>"
                         class="h-10 w-10 rounded-full object-cover shadow-sm">
                <?php else: ?>
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-app-primary text-sm font-semibold text-app-white shadow-sm"><?= htmlspecialchars($adminInitials, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold text-app-text"><?= $adminName ?></p>
                    <p class="truncate text-xs text-app-muted"><?= $adminEmail ?></p>
                </div>
            </div>
        </div>

        <div class="px-5 pt-5">
            <p class="px-3 text-[11px] font-semibold uppercase tracking-[0.2em] text-app-header-muted">Profile</p>
        </div>

        <nav class="px-4 py-3 space-y-1.5">
            <a href="<?= URLROOT ?>/admin/profile" class="<?= dashboard_admin_nav_class('admin/profile', $currentPath, true) ?>">
                <i data-lucide="circle-user" class="h-4 w-4 text-app-header-muted"></i>
                <span class="flex-1">My Profile</span>
                <i data-lucide="chevron-right" class="h-4 w-4 text-app-header-muted"></i>
            </a>
        </nav>

        <div class="px-5 pt-5">
            <p class="px-3 text-[11px] font-semibold uppercase tracking-[0.2em] text-app-header-muted">Workspace</p>
        </div>

        <nav class="px-4 py-3 space-y-1.5">
            <a href="<?= URLROOT ?>/admin/dashboard" class="<?= dashboard_admin_nav_class('admin/dashboard', $currentPath, true) ?>">
                <i data-lucide="layout-dashboard" class="h-4 w-4"></i>
                <span class="flex-1">Dashboard</span>
            </a>

            <?php $bookingsActive = strpos($currentPath, 'admin/booking') !== false || strpos($currentPath, 'admin/replacement') !== false; ?>
            <div class="admin-sidebar-group" data-open="<?= $bookingsActive ? 'true' : 'false' ?>">
                <button type="button"
                        class="admin-sidebar-group-trigger <?= $bookingsActive ? 'flex items-center gap-3 rounded-xl px-4 py-3 transition bg-app-primary text-app-white shadow-sm' : 'flex items-center gap-3 rounded-xl px-4 py-3 transition text-app-text hover:bg-app-sidebar-hover' ?>"
                        aria-expanded="<?= $bookingsActive ? 'true' : 'false' ?>"
                        title="Bookings">
                    <i data-lucide="calendar-days" class="h-4 w-4 <?= $bookingsActive ? '' : 'text-app-header-muted' ?>"></i>
                    <span class="flex-1 text-left text-sm font-medium">Bookings</span>
                    <i data-lucide="chevron-down" class="admin-sidebar-group-chevron h-4 w-4 text-app-header-muted transition-transform duration-200"></i>
                </button>
                <div class="<?= dashboard_admin_subnav_wrap_class($bookingsActive) ?>">
                    <a href="<?= URLROOT ?>/admin/bookings" class="<?= dashboard_admin_subnav_link_class(dashboard_admin_path_matches('admin/bookings', $currentPath, true)) ?>">
                        <i data-lucide="list-filter" class="h-3.5 w-3.5"></i>
                        All bookings
                    </a>
                    <a href="<?= URLROOT ?>/admin/replacementQueue" class="<?= dashboard_admin_subnav_link_class(strpos($currentPath, 'admin/replacementQueue') !== false) ?>">
                        <i data-lucide="refresh-cw" class="h-3.5 w-3.5"></i>
                        Replacements
                    </a>
                </div>
            </div>

            <a href="<?= URLROOT ?>/admin/packages" class="<?= dashboard_admin_nav_class('admin/packages', $currentPath) ?>">
                <i data-lucide="package" class="h-4 w-4"></i>
                <span class="flex-1">Packages</span>
            </a>

            <a href="<?= URLROOT ?>/admin/categories" class="<?= dashboard_admin_nav_class('admin/categories', $currentPath) ?>">
                <i data-lucide="tags" class="h-4 w-4"></i>
                <span class="flex-1">Categories</span>
            </a>

            <a href="<?= URLROOT ?>/admin/suppliers" class="<?= dashboard_admin_nav_class('admin/supplier', $currentPath) ?>">
                <i data-lucide="store" class="h-4 w-4"></i>
                <span class="flex-1">Suppliers</span>
            </a>

            <?php $customersActive = strpos($currentPath, 'admin/customer') !== false; ?>
            <div class="admin-sidebar-group" data-open="<?= $customersActive ? 'true' : 'false' ?>">
                <button type="button"
                        class="admin-sidebar-group-trigger <?= $customersActive ? 'flex items-center gap-3 rounded-xl px-4 py-3 transition bg-app-primary text-app-white shadow-sm' : 'flex items-center gap-3 rounded-xl px-4 py-3 transition text-app-text hover:bg-app-sidebar-hover' ?>"
                        aria-expanded="<?= $customersActive ? 'true' : 'false' ?>"
                        title="Customers">
                    <i data-lucide="users" class="h-4 w-4 <?= $customersActive ? '' : 'text-app-header-muted' ?>"></i>
                    <span class="flex-1 text-left text-sm font-medium">Customers</span>
                    <i data-lucide="chevron-down" class="admin-sidebar-group-chevron h-4 w-4 text-app-header-muted transition-transform duration-200"></i>
                </button>
                <div class="<?= dashboard_admin_subnav_wrap_class($customersActive) ?>">
                    <a href="<?= URLROOT ?>/admin/customers" class="<?= dashboard_admin_subnav_link_class(dashboard_admin_path_matches('admin/customers', $currentPath, true)) ?>">
                        <i data-lucide="users" class="h-3.5 w-3.5"></i>
                        All customers
                    </a>
                    <a href="<?= URLROOT ?>/admin/customers?status=banned" class="<?= dashboard_admin_subnav_link_class(strpos($currentPath, 'admin/customers') !== false && isset($_GET['status']) && $_GET['status'] === 'banned') ?>">
                        <i data-lucide="user-x" class="h-3.5 w-3.5"></i>
                        Suspended / Banned
                    </a>
                </div>
            </div>

            <?php $paymentsActive = strpos($currentPath, 'admin/payment') !== false || strpos($currentPath, 'admin/refund') !== false || strpos($currentPath, 'admin/payouts') !== false; ?>
            <div class="admin-sidebar-group" data-open="<?= $paymentsActive ? 'true' : 'false' ?>">
                <button type="button"
                        class="admin-sidebar-group-trigger <?= $paymentsActive ? 'flex items-center gap-3 rounded-xl px-4 py-3 transition bg-app-primary text-app-white shadow-sm' : 'flex items-center gap-3 rounded-xl px-4 py-3 transition text-app-text hover:bg-app-sidebar-hover' ?>"
                        aria-expanded="<?= $paymentsActive ? 'true' : 'false' ?>"
                        title="Payments">
                    <i data-lucide="wallet" class="h-4 w-4 <?= $paymentsActive ? '' : 'text-app-header-muted' ?>"></i>
                    <span class="flex-1 text-left text-sm font-medium">Payments</span>
                    <i data-lucide="chevron-down" class="admin-sidebar-group-chevron h-4 w-4 text-app-header-muted transition-transform duration-200"></i>
                </button>
                <div class="<?= dashboard_admin_subnav_wrap_class($paymentsActive) ?>">
                    <a href="<?= URLROOT ?>/admin/paymentVerification" class="<?= dashboard_admin_subnav_link_class(strpos($currentPath, 'admin/paymentVerification') !== false) ?>">
                        <i data-lucide="receipt-text" class="h-3.5 w-3.5"></i>
                        Deposit verification
                    </a>
                    <a href="<?= URLROOT ?>/admin/payments?status=all" class="<?= dashboard_admin_subnav_link_class(strpos($currentPath, 'admin/payments') !== false && ($paymentStatusFilter ?? '') === 'all') ?>">
                        <i data-lucide="credit-card" class="h-3.5 w-3.5"></i>
                        History
                    </a>
                    <a href="<?= URLROOT ?>/admin/payments?status=pending" class="<?= dashboard_admin_subnav_link_class(strpos($currentPath, 'admin/payments') !== false && ($paymentStatusFilter ?? '') === 'pending') ?>">
                        <i data-lucide="clock" class="h-3.5 w-3.5"></i>
                        Pending
                    </a>
                    <a href="<?= URLROOT ?>/admin/refundQueue" class="<?= dashboard_admin_subnav_link_class(strpos($currentPath, 'admin/refund') !== false) ?>">
                        <i data-lucide="undo-2" class="h-3.5 w-3.5"></i>
                        Refunds
                    </a>
                    <a href="<?= URLROOT ?>/admin/payouts" class="<?= dashboard_admin_subnav_link_class(strpos($currentPath, 'admin/payouts') !== false) ?>">
                        <i data-lucide="banknote" class="h-3.5 w-3.5"></i>
                        Supplier Payouts
                    </a>
                </div>
            </div>



            <div>
                <p class="mb-1 mt-8 px-3 text-[11px] font-semibold uppercase tracking-[0.2em] text-app-header-muted">System</p>
                <div class="space-y-1">

                    <a href="<?= URLROOT ?>/admin/notifications" class="<?= dashboard_admin_nav_class('admin/notifications', $currentPath, true) ?>">
                        <i data-lucide="bell" class="h-4 w-4 text-app-header-muted"></i>
                        <span class="flex-1">Notifications</span>
                    </a>
                    <?php $settingsActive = strpos($currentPath, 'admin/logs') !== false || strpos($currentPath, 'admin/settings') !== false; ?>
                    <div class="admin-sidebar-group" data-open="<?= $settingsActive ? 'true' : 'false' ?>">
                        <button type="button"
                                class="admin-sidebar-group-trigger <?= $settingsActive ? 'flex items-center gap-3 rounded-xl px-4 py-3 transition bg-app-primary text-app-white shadow-sm' : 'flex items-center gap-3 rounded-xl px-4 py-3 transition text-app-text hover:bg-app-sidebar-hover' ?>"
                                aria-expanded="<?= $settingsActive ? 'true' : 'false' ?>"
                                title="Settings">
                            <i data-lucide="settings" class="h-4 w-4 <?= $settingsActive ? '' : 'text-app-header-muted' ?>"></i>
                            <span class="flex-1 text-left text-sm font-medium">Settings</span>
                            <i data-lucide="chevron-down" class="admin-sidebar-group-chevron h-4 w-4 text-app-header-muted transition-transform duration-200"></i>
                        </button>
                        <div class="<?= dashboard_admin_subnav_wrap_class($settingsActive) ?>">
                            <a href="<?= URLROOT ?>/admin/settings" class="<?= dashboard_admin_subnav_link_class(strpos($currentPath, 'admin/settings') !== false) ?>">
                                <i data-lucide="percent" class="h-3.5 w-3.5"></i>
                                Platform fees
                            </a>
                            <a href="<?= URLROOT ?>/admin/logs" class="<?= dashboard_admin_subnav_link_class(strpos($currentPath, 'admin/logs') !== false) ?>">
                                <i data-lucide="scroll-text" class="h-3.5 w-3.5"></i>
                                System logs
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <div class="flex-shrink-0 border-t border-app-panel-border px-4 py-4">
            <a href="<?= URLROOT ?>/admin/logout" class="group flex w-full items-center gap-3 rounded-xl px-4 py-3 transition-all duration-300 ease-[cubic-bezier(0.19,1,0.22,1)] hover:bg-app-sidebar-hover">
                <span class="flex h-8 w-8 items-center justify-center rounded-xl text-app-header-muted transition-all duration-300 group-hover:text-app-danger group-hover:bg-app-danger-soft">
                    <i data-lucide="log-out" class="h-5 w-5"></i>
                </span>
                <span class="flex-1 text-left text-sm font-semibold text-app-text">Log Out</span>
                <i data-lucide="chevron-right" class="h-4 w-4 text-app-header-muted transition-transform duration-300 group-hover:translate-x-0.5"></i>
            </a>
        </div>
    </div>
</aside>

<main class="admin-shell-type overflow-y-auto">
    <div class="admin-dashboard-topbar sticky top-0 z-40 flex flex-col gap-4 border-b border-app-border bg-app-sidebar/95 px-6 py-[18px] backdrop-blur-sm sm:flex-row sm:items-center sm:justify-between">
        <div class="admin-topbar-title min-w-0">
            <?php
            $dashboardBreadcrumbs = $dashboardBreadcrumbs ?? [
                ['label' => $dashboardTitle ?? 'Dashboard', 'url' => null],
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
                        <a href="<?= htmlspecialchars($crumbUrl, ENT_QUOTES, 'UTF-8') ?>" class="text-app-header-muted transition-colors duration-150 hover:text-app-primary"><?= $crumbLabel ?></a>
                    <?php else: ?>
                        <span class="<?= $isLastCrumb ? 'text-app-text' : 'text-app-header-muted' ?>"><?= $crumbLabel ?></span>
                    <?php endif; ?>
                <?php endforeach; ?>
            </nav>
        </div>

        <div class="admin-topbar-actions flex flex-wrap items-center gap-3">
            <?php require APPROOT . '/views/dashboardLayout/dashboardSearch.php'; ?>
            <?php require APPROOT . '/views/dashboardLayout/customerNotification.php'; ?>
        </div>
    </div>

    <?php if (isset($dashboardContent) && is_callable($dashboardContent)): ?>
        <div class="<?= htmlspecialchars($dashboardContentClass ?? 'px-6 py-6', ENT_QUOTES, 'UTF-8') ?>">
            <?php $dashboardContent(); ?>
        </div>
    <?php endif; ?>
</main>

<script>
    // Cmd+K search shortcut handled by dashboardSearch.php partial

    document.querySelectorAll('.admin-sidebar-group-trigger').forEach((btn) => {
        btn.addEventListener('click', () => {
            const group = btn.closest('.admin-sidebar-group');
            const subnav = group ? group.querySelector('.admin-sidebar-subnav') : null;
            const isOpen = group.dataset.open === 'true';
            group.dataset.open = isOpen ? 'false' : 'true';
            btn.setAttribute('aria-expanded', String(!isOpen));
            if (subnav) {
                subnav.classList.toggle('hidden', isOpen);
                subnav.classList.toggle('grid', !isOpen);
            }
        });
    });

    lucide.createIcons();
</script>
<style>
    .admin-shell-type {
        --admin-text: #111827;
        --admin-muted: #b79c8b;
        --admin-primary: #6d4c5b;
        --admin-secondary: #7b5c69;
        --admin-border: #ead8c7;
        --admin-focus: #c8b1a1;
        --admin-sidebar-hover: #eddecc;
        --admin-soft: #faf5ef;
        --admin-danger-soft: #f9dede;
        font-family: 'DM Sans', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
        font-variant-numeric: tabular-nums;
    }

    .admin-sidebar-shell {
        height: 100vh;
        min-height: 0;
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: #d8c8bb transparent;
    }

    .admin-sidebar-shell::-webkit-scrollbar {
        width: 6px;
    }

    .admin-sidebar-shell::-webkit-scrollbar-thumb {
        border-radius: 999px;
        background: #d8c8bb;
    }

    .admin-sidebar-group-trigger.bg-app-primary .admin-sidebar-group-chevron,
    .admin-sidebar-group-trigger.bg-app-primary [data-lucide] {
        color: #FFFFFF;
    }

    .admin-dashboard-topbar nav[aria-label="Breadcrumb"] {
        font-size: 10px;
        letter-spacing: .08em;
    }

    /* Sub-dropdown — matches supplier sidebar */
    .admin-sidebar-group-trigger {
        width: 100%;
        border: 0;
        cursor: pointer;
    }
    .admin-sidebar-subnav {
        display: none;
        margin: 4px 0 8px 34px;
        padding: 4px 0 4px 12px;
        border-left: 1px solid var(--color-app-panel-border, #eddecc);
    }
    .admin-sidebar-group[data-open="true"] .admin-sidebar-subnav {
        display: grid;
        gap: 2px;
    }
    .admin-sidebar-subnav a {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        min-height: 44px;
        border-radius: 12px;
        padding: 0 16px;
        font-size: 14px;
        font-weight: 500;
        color: var(--color-app-muted, #b79c8b);
        transition: all 300ms cubic-bezier(0.19, 1, 0.22, 1);
    }
    .admin-sidebar-subnav a:hover,
    .admin-sidebar-subnav a.is-active {
        background: var(--color-app-sidebar-hover, #eddecc);
        color: var(--color-app-primary, #6d4c5b);
    }
    .admin-sidebar-group[data-open="true"] .admin-sidebar-group-chevron {
        transform: rotate(180deg);
    }
</style>
<?php include APPROOT . '/views/partials/cookie-consent.php'; ?>
