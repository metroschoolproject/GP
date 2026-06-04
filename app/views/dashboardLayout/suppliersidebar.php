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
$dashboardSearchPlaceholder = $dashboardSearchPlaceholder ?? 'Search bookings, services...';
$notificationConfig = $notificationConfig ?? [
    'role' => 'supplier',
    'reviewUrl' => URLROOT . '/supplier/dashboard',
    'defaultUrl' => URLROOT . '/supplier/dashboard',
    'referenceUrls' => [
        'supplier' => URLROOT . '/supplier/dashboard?supplier=',
        'payment' => URLROOT . '/supplier/dashboard?payment=',
        'booking' => URLROOT . '/supplier/dashboard?booking=',
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
<aside class="border-r border-r-app-sidebar bg-app-sidebar">
    <div class="flex h-full flex-col">
        <div class="border-b border-b-app-panel-border bg-app-panel px-5 py-5">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-app-primary text-sm font-semibold text-app-white shadow-sm"><?= htmlspecialchars($supplierInitials, ENT_QUOTES, 'UTF-8') ?></div>
                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold text-app-text"><?= $supplierName ?></p>
                    <p class="truncate text-xs text-app-muted"><?= $ownerEmail ?></p>
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
            <a href="<?= URLROOT ?>/supplier/dashboard" class="<?= dashboard_supplier_nav_class('supplier/dashboard', $currentPath, true) ?>">
                <i data-lucide="layout-dashboard" class="h-4 w-4"></i>
                <span class="flex-1">Dashboard</span>
            </a>
            <a href="#" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-app-text transition hover:bg-app-input hover:shadow-sm">
                <i data-lucide="calendar-check" class="h-4 w-4 text-app-header-muted"></i>
                <span class="flex-1">Bookings</span>
                <?php if ($pendingBookings > 0): ?>
                    <span class="rounded-full bg-app-surface px-2 py-0.5 text-[10px] font-semibold text-app-warning"><?= $pendingBookings ?></span>
                <?php endif; ?>
            </a>
            <a href="#" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-app-text transition hover:bg-app-input hover:shadow-sm">
                <i data-lucide="briefcase-business" class="h-4 w-4 text-app-header-muted"></i>
                <span class="flex-1">Services</span>
            </a>
            <a href="#" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-app-text transition hover:bg-app-input hover:shadow-sm">
                <i data-lucide="image" class="h-4 w-4 text-app-header-muted"></i>
                <span class="flex-1">Portfolio</span>
            </a>
            <a href="#" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-app-text transition hover:bg-app-input hover:shadow-sm">
                <i data-lucide="star" class="h-4 w-4 text-app-header-muted"></i>
                <span class="flex-1">Reviews</span>
            </a>
        </nav>

        <div class="px-5 pt-5">
            <p class="px-3 text-[11px] font-semibold uppercase tracking-[0.2em] text-app-header-muted">Finance</p>
        </div>

        <nav class="px-4 py-3 space-y-1.5">
            <a href="#" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-app-text transition hover:bg-app-input hover:shadow-sm">
                <i data-lucide="wallet" class="h-4 w-4 text-app-header-muted"></i>
                <span class="flex-1">Wallet</span>
            </a>
            <a href="#" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-app-text transition hover:bg-app-input hover:shadow-sm">
                <i data-lucide="receipt-text" class="h-4 w-4 text-app-header-muted"></i>
                <span class="flex-1">Payments</span>
            </a>
            <a href="#" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-app-text transition hover:bg-app-input hover:shadow-sm">
                <i data-lucide="banknote" class="h-4 w-4 text-app-header-muted"></i>
                <span class="flex-1">Withdrawals</span>
            </a>
        </nav>

        <div class="mt-auto border-t border-app-panel-border px-4 py-4">
            <a href="<?= URLROOT ?>/users/logout" class="group flex w-full items-center gap-3 rounded-xl px-4 py-3 transition hover:bg-app-input hover:shadow-sm">
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
            <p class="mb-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-app-header-muted">
                <span class="text-app-text"><?= htmlspecialchars($dashboardTitle ?? 'Supplier', ENT_QUOTES, 'UTF-8') ?></span> / <?= htmlspecialchars($dashboardCrumb ?? 'Overview', ENT_QUOTES, 'UTF-8') ?>
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <div class="relative">
                <i data-lucide="search" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-app-header-muted"></i>
                <input
                    type="search"
                    id="dashboard-search"
                    placeholder="<?= htmlspecialchars($dashboardSearchPlaceholder, ENT_QUOTES, 'UTF-8') ?>"
                    aria-keyshortcuts="Meta+K Control+K"
                    class="w-[260px] rounded-xl border border-app-border bg-app-input/80 py-2.5 pl-10 pr-14 text-sm text-app-text shadow-sm outline-none transition focus:border-app-focus focus:bg-app-input focus:ring-2 focus:ring-app-ring"
                >
                <div class="absolute right-2 top-1/2 flex -translate-y-1/2 items-center gap-1 rounded-md border border-app-border bg-app-keycap px-2 py-1 text-[10px] font-semibold text-app-header-muted">
                    <i data-lucide="command" class="h-3 w-3"></i>
                    K
                </div>
            </div>

            <?php require APPROOT . '/views/dashboardLayout/notification.php'; ?>
        </div>
    </div>

    <script>
        lucide.createIcons();

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
    </script>

    <?php if (isset($dashboardContent) && is_callable($dashboardContent)): ?>
        <div class="<?= htmlspecialchars($dashboardContentClass ?? 'px-6 py-6', ENT_QUOTES, 'UTF-8') ?>">
            <?php $dashboardContent(); ?>
        </div>
    <?php endif; ?>
</main>
