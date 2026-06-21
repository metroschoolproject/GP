<?php
$dashboardSearchPlaceholder = $dashboardSearchPlaceholder ?? 'Search dashboard...';
$dashboardSearchAction = $dashboardSearchAction ?? '#';
$dashboardSearchValue = $dashboardSearchValue ?? ($_GET['search'] ?? '');
?>
<form class="dashboard-global-search relative" action="<?= htmlspecialchars($dashboardSearchAction, ENT_QUOTES, 'UTF-8') ?>" method="get" role="search">
    <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-app-header-muted" aria-hidden="true"></i>
    <input
        type="search"
        id="dashboard-search"
        name="search"
        value="<?= htmlspecialchars((string)$dashboardSearchValue, ENT_QUOTES, 'UTF-8') ?>"
        placeholder="<?= htmlspecialchars($dashboardSearchPlaceholder, ENT_QUOTES, 'UTF-8') ?>"
        aria-label="<?= htmlspecialchars($dashboardSearchPlaceholder, ENT_QUOTES, 'UTF-8') ?>"
        aria-keyshortcuts="Meta+K Control+K"
        autocomplete="off"
        class="h-10 w-[260px] rounded-xl border border-app-border bg-white/80 pl-10 pr-14 text-sm text-app-text shadow-sm outline-none transition focus:border-app-focus focus:bg-white focus:ring-2 focus:ring-app-ring"
    >
    <span class="dashboard-search-shortcut pointer-events-none absolute right-2 top-1/2 inline-flex -translate-y-1/2 items-center gap-1 rounded-md border border-app-border bg-app-keycap px-2 py-1 text-[10px] font-semibold text-app-header-muted" aria-hidden="true">
        <i data-lucide="command" class="h-3 w-3"></i>
        K
    </span>
</form>
