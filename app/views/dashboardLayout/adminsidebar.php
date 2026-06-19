<?php
$adminEmail = htmlspecialchars($_SESSION['session_email'] ?? 'admin@example.com', ENT_QUOTES, 'UTF-8');
$adminName = htmlspecialchars($_SESSION['user_name'] ?? $_SESSION['session_name'] ?? 'Admin User', ENT_QUOTES, 'UTF-8');
$adminInitials = strtoupper(substr(trim($adminName) !== '' ? $adminName : 'Admin', 0, 1));
$currentPath = trim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
$paymentStatusFilter = $_GET['status'] ?? 'pending';
$dashboardSearchPlaceholder = $dashboardSearchPlaceholder ?? 'Search bookings, suppliers...';
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
        $base = 'flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium transition';
        $isActive = dashboard_admin_path_matches($path, $currentPath, $exact);

        return $isActive
            ? $base . ' bg-app-primary text-app-white shadow-sm'
            : $base . ' text-app-text hover:bg-app-input hover:shadow-sm';
    }

    function dashboard_admin_subnav_class($path, $currentPath)
    {
        $base = 'ml-3 flex items-center gap-2 rounded-lg px-3 py-2 text-sm transition';
        $isActive = dashboard_admin_path_matches($path, $currentPath);

        return $isActive
            ? $base . ' bg-app-primary text-app-white shadow-sm'
            : $base . ' text-app-secondary hover:bg-app-input hover:text-app-text';
    }
}
?>
<aside class="border-r border-r-app-sidebar bg-app-sidebar">
    <div class="flex h-full flex-col">
        <div class="border-b border-b-app-panel-border bg-app-panel px-5 py-5">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-app-primary text-sm font-semibold text-app-white shadow-sm"><?= htmlspecialchars($adminInitials, ENT_QUOTES, 'UTF-8') ?></div>
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
            <a href="#" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-app-text transition hover:bg-app-input hover:shadow-sm">
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

            <div class="space-y-1">
                <button type="button" data-subnav-toggle="bookings" aria-expanded="false" class="flex w-full items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-app-text transition hover:bg-app-input hover:shadow-sm">
                    <i data-lucide="calendar-days" class="h-4 w-4 text-app-header-muted"></i>
                    <span class="flex-1 text-left">Bookings</span>
                    <i data-chevron="bookings" data-lucide="chevron-down" class="h-4 w-4 text-app-header-muted transition-transform duration-200"></i>
                </button>
                <div data-subnav-panel="bookings" class="<?= strpos($currentPath, 'admin/booking') !== false ? '' : 'hidden' ?> pl-6">
                    <div class="space-y-0.5 border-l border-app-panel-border py-1">
                        <a href="<?= URLROOT ?>/admin/bookings" class="<?= dashboard_admin_subnav_class('admin/bookings', $currentPath) ?>">
                            <i data-lucide="list-filter" class="h-3.5 w-3.5 text-app-header-muted"></i>
                            <span>All bookings</span>
                        </a>
                        <a href="<?= URLROOT ?>/admin/bookings?status=pending_payment" class="<?= dashboard_admin_subnav_class('admin/bookings/pending', $currentPath) ?>">
                            <i data-lucide="clock" class="h-3.5 w-3.5 text-app-header-muted"></i>
                            <span>Pending payment</span>
                        </a>
                    </div>
                </div>
            </div>

            <a href="<?= URLROOT ?>/admin/packages" class="<?= dashboard_admin_nav_class('admin/packages', $currentPath) ?>">
                <i data-lucide="package" class="h-4 w-4"></i>
                <span class="flex-1">Packages</span>
            </a>

            <div class="space-y-1">
                <button type="button" data-subnav-toggle="suppliers" aria-expanded="false" class="flex w-full items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-app-text transition hover:bg-app-input hover:shadow-sm">
                    <i data-lucide="store" class="h-4 w-4 text-app-header-muted"></i>
                    <span class="flex-1 text-left">Suppliers</span>
                    <i data-chevron="suppliers" data-lucide="chevron-down" class="h-4 w-4 text-app-header-muted transition-transform duration-200"></i>
                </button>
                <div data-subnav-panel="suppliers" class="<?= strpos($currentPath, 'admin/supplier') !== false ? '' : 'hidden' ?> pl-6">
                    <div class="space-y-0.5 border-l border-app-panel-border py-1">
                        <a href="<?= URLROOT ?>/admin/suppliers?status=all" class="<?= dashboard_admin_subnav_class('admin/suppliers', $currentPath) ?>">
                            <i data-lucide="list-filter" class="h-3.5 w-3.5 text-app-header-muted"></i>
                            <span>All suppliers</span>
                        </a>
                        <a href="<?= URLROOT ?>/admin/supplier/application" class="<?= dashboard_admin_subnav_class('admin/supplier/application', $currentPath) ?>">
                            <i data-lucide="clipboard-check" class="h-3.5 w-3.5 text-app-header-muted"></i>
                            <span>Applications</span>
                        </a>
                    
                        <a href="<?= URLROOT ?>/admin/suppliers?status=verified" class="ml-3 flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-app-secondary transition hover:bg-app-input hover:text-app-text">
                            <i data-lucide="badge-check" class="h-3.5 w-3.5 text-app-header-muted"></i>
                            <span>Verified</span>
                        </a>
                        <a href="<?= URLROOT ?>/admin/suppliers?status=rejected" class="ml-3 flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-app-secondary transition hover:bg-app-input hover:text-app-text">
                            <i data-lucide="circle-x" class="h-3.5 w-3.5 text-app-header-muted"></i>
                            <span>Rejected</span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="space-y-1">
                <button type="button" data-subnav-toggle="customers" aria-expanded="false" class="flex w-full items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-app-text transition hover:bg-app-input hover:shadow-sm">
                    <i data-lucide="users" class="h-4 w-4 text-app-header-muted"></i>
                    <span class="flex-1 text-left">Customers</span>
                    <i data-chevron="customers" data-lucide="chevron-down" class="h-4 w-4 text-app-header-muted transition-transform duration-200"></i>
                </button>
                <div data-subnav-panel="customers" class="hidden pl-6">
                    <div class="space-y-0.5 border-l border-app-panel-border py-1">
                        <a href="#" class="ml-3 flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-app-secondary transition hover:bg-app-input hover:text-app-text">
                            <i data-lucide="users" class="h-3.5 w-3.5 text-app-header-muted"></i>
                            <span>All customers</span>
                        </a>
                        <a href="#" class="ml-3 flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-app-secondary transition hover:bg-app-input hover:text-app-text">
                            <i data-lucide="user-x" class="h-3.5 w-3.5 text-app-danger"></i>
                            <span>Suspended / Banned</span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="space-y-1">
                <button type="button" data-subnav-toggle="payments" aria-expanded="false" class="flex w-full items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-app-text transition hover:bg-app-input hover:shadow-sm">
                    <i data-lucide="store" class="h-4 w-4 text-app-header-muted"></i>
                    <span class="flex-1 text-left">Payments</span>
                    <i data-chevron="payments" data-lucide="chevron-down" class="h-4 w-4 text-app-header-muted transition-transform duration-200"></i>
                </button>
                <div data-subnav-panel="payments" class="<?= strpos($currentPath, 'admin/payment') !== false ? '' : 'hidden' ?> pl-6">
                    <div class="space-y-0.5 border-l border-app-panel-border py-1">
                        <a href="<?= URLROOT ?>/admin/paymentVerification" class="<?= strpos($currentPath, 'admin/paymentVerification') !== false ? 'ml-3 flex items-center gap-2 rounded-lg bg-app-primary px-3 py-2 text-sm text-app-white shadow-sm transition' : 'ml-3 flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-app-secondary transition hover:bg-app-input hover:text-app-text' ?>">
                            <i data-lucide="receipt-text" class="h-3.5 w-3.5 text-app-header-muted"></i>
                            <span>Deposit verification</span>
                        </a>
                        <a href="<?= URLROOT ?>/admin/payments?status=all" class="<?= strpos($currentPath, 'admin/paymentVerification') === false && $paymentStatusFilter === 'all' ? 'ml-3 flex items-center gap-2 rounded-lg bg-app-primary px-3 py-2 text-sm text-app-white shadow-sm transition' : 'ml-3 flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-app-secondary transition hover:bg-app-input hover:text-app-text' ?>">
                            <i data-lucide="credit-card" class="h-3.5 w-3.5 text-app-header-muted"></i>
                            <span>History</span>
                        </a>
                        <a href="<?= URLROOT ?>/admin/payments?status=pending" class="<?= strpos($currentPath, 'admin/paymentVerification') === false && $paymentStatusFilter === 'pending' ? 'ml-3 flex items-center gap-2 rounded-lg bg-app-primary px-3 py-2 text-sm text-app-white shadow-sm transition' : 'ml-3 flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-app-secondary transition hover:bg-app-input hover:text-app-text' ?>">
                            <i data-lucide="clock" class="h-3.5 w-3.5 text-app-header-muted"></i>
                            <span>Pending</span>
                        </a>
                    </div>
                </div>
            </div>



            <div>
                <p class="mb-1 mt-8 px-3 text-[11px] font-semibold uppercase tracking-[0.2em] text-app-header-muted">System</p>
                <div class="space-y-1">

                    <a href="<?= URLROOT ?>/admin/notifications" class="<?= dashboard_admin_nav_class('admin/notifications', $currentPath, true) ?>">
                        <i data-lucide="bell" class="h-4 w-4 text-app-header-muted"></i>
                        <span class="flex-1">Notifications</span>
                    </a>
                    <a href="#" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-app-text transition hover:bg-app-input hover:shadow-sm">
                        <i data-lucide="settings" class="h-4 w-4 text-app-header-muted"></i>
                        <span class="flex-1">Settings</span>
                    </a>
                </div>
            </div>
        </nav>

        <div class="mt-auto border-t border-app-panel-border px-4 py-4">
            <a href="<?= URLROOT ?>/admin/logout" class="group flex w-full items-center gap-3 rounded-xl px-4 py-3 transition hover:bg-app-input hover:shadow-sm">
                <span class="flex h-8 w-8 items-center justify-center rounded-xl text-app-danger transition group-hover:bg-app-danger-soft">
                    <i data-lucide="log-out" class="h-5 w-5"></i>
                </span>
                <span class="flex-1 text-left text-sm font-semibold text-app-text">Log Out</span>
                <i data-lucide="chevron-right" class="h-4 w-4 text-app-header-muted"></i>
            </a>
        </div>
    </div>
</aside>

<main class="overflow-y-auto">
    <div class="sticky top-0 z-40 flex flex-col gap-4 border-b border-app-border bg-app-sidebar/95 px-6 py-[18px] backdrop-blur-sm sm:flex-row sm:items-center sm:justify-between">
        <div>
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
                        <a href="<?= htmlspecialchars($crumbUrl, ENT_QUOTES, 'UTF-8') ?>" class="text-app-header-muted transition hover:text-app-text"><?= $crumbLabel ?></a>
                    <?php else: ?>
                        <span class="<?= $isLastCrumb ? 'text-app-text' : 'text-app-header-muted' ?>"><?= $crumbLabel ?></span>
                    <?php endif; ?>
                <?php endforeach; ?>
            </nav>
        </div>

        <div class="flex flex-wrap items-center gap-3">
           

            <?php require APPROOT . '/views/dashboardLayout/notification.php'; ?>
        </div>
    </div>

    <?php if (isset($dashboardContent) && is_callable($dashboardContent)): ?>
        <div class="<?= htmlspecialchars($dashboardContentClass ?? 'px-6 py-6', ENT_QUOTES, 'UTF-8') ?>">
            <?php $dashboardContent(); ?>
        </div>
    <?php endif; ?>
</main>

<script>
    const dashboardSearch = document.getElementById('dashboard-search');

    document.addEventListener('keydown', (event) => {
        const isShortcut = (event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k';

        if (!isShortcut || !dashboardSearch) {
            return;
        }

        event.preventDefault();
        dashboardSearch.focus();
        dashboardSearch.select();
    });

    document.querySelectorAll('[data-subnav-toggle]').forEach((btn) => {
        const key = btn.dataset.subnavToggle;
        const panel = document.querySelector(`[data-subnav-panel="${key}"]`);
        const chevron = document.querySelector(`[data-chevron="${key}"]`);

        if (panel && !panel.classList.contains('hidden')) {
            btn.setAttribute('aria-expanded', 'true');
            chevron?.classList.add('rotate-180');
        }

        btn.addEventListener('click', () => {
            const isExpanded = btn.getAttribute('aria-expanded') === 'true';
            btn.setAttribute('aria-expanded', String(!isExpanded));
            panel?.classList.toggle('hidden', isExpanded);
            chevron?.classList.toggle('rotate-180', !isExpanded);
        });
    });

    lucide.createIcons();
</script>
