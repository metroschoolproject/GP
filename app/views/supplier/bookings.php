<?php
$bookings = $bookings ?? [];
$stats = $stats ?? [];
$activeFilter = $activeFilter ?? 'all';
$money = fn($v) => 'RM ' . number_format((float)$v, 0);
$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$filters = [
    'all'       => ['label' => 'All',       'icon' => 'list'],
    'pending'   => ['label' => 'Pending',   'icon' => 'clock'],
    'confirmed' => ['label' => 'Confirmed', 'icon' => 'check-circle'],
    'completed' => ['label' => 'Completed', 'icon' => 'badge-check'],
    'rejected'  => ['label' => 'Rejected',  'icon' => 'x-circle'],
];

// Status badge color helper matching sidebar role-badge logic
$statusBadgeClass = function (string $status): string {
    $s = strtolower($status);
    if (in_array($s, ['confirmed', 'completed'], true)) {
        return 'bg-app-success/10 text-app-success';
    }
    if ($s === 'pending') {
        return 'bg-app-warning/10 text-app-warning';
    }
    if (in_array($s, ['rejected', 'cancelled'], true)) {
        return 'bg-app-danger/10 text-app-danger';
    }
    return 'bg-app-soft text-app-secondary';
};

$dashboardTitle = 'Bookings';
$dashboardCrumb = 'Incoming bookings';
$dashboardContentClass = 'bg-[#FBFBF9] px-0 py-0 overflow-y-auto';
$dashboardContent = function () use ($bookings, $stats, $activeFilter, $filters, $money, $h, $statusBadgeClass, $currentPage, $totalPages, $totalCount, $perPage, $searchQuery, $performanceMetrics, $upcomingBookings) {
    $pendingCount   = (int)($stats['pending_count'] ?? 0);
    $confirmedCount = (int)($stats['confirmed_count'] ?? 0);
    $completedCount = (int)($stats['completed_count'] ?? 0);
    $estRevenue     = (float)($stats['est_revenue'] ?? 0);
    $currentPage    = $currentPage ?? 1;
    $totalPages     = $totalPages ?? 1;
    $totalCount     = $totalCount ?? 0;
    $perPage        = $perPage ?? 20;
    $searchQuery    = $searchQuery ?? '';
    $performanceMetrics = $performanceMetrics ?? [];
    $upcomingBookings   = $upcomingBookings ?? [];
?>
<style>
/* ── Bookings view ────────────────────────────────────────────── */
.bk-kpi-row {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
    margin-bottom: 16px;
}
.bk-kpi {
    background: #fff;
    border: 1px solid #e7e5e4;
    border-radius: 1.2rem;
    padding: 20px;
    box-shadow: 0 1px 2px rgba(28,25,23,.05);
    transition: box-shadow .18s ease;
}
.bk-kpi:hover { box-shadow: 0 4px 12px rgba(28,25,23,.08); }
.bk-kpi-label {
    font-size: 11px;
    font-weight: 650;
    letter-spacing: .01em;
    color: #78716c;
}
.bk-kpi-value {
    font-size: 24px;
    font-weight: 750;
    letter-spacing: -.025em;
    color: #1c1917;
    margin-top: 6px;
    line-height: 1;
}
.bk-kpi-sub {
    font-size: 11px;
    color: #a8a29e;
    margin-top: 5px;
}
.bk-kpi-icon {
    display: flex;
    width: 32px;
    height: 32px;
    align-items: center;
    justify-content: center;
    margin-bottom: 12px;
    border-radius: 8px;
}
.bk-kpi-icon svg { width: 16px; height: 16px; }
.bk-kpi-breakdown {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
    margin-top: 16px;
    padding-top: 12px;
    border-top: 1px solid #e7e5e4;
}
.bk-kpi-breakdown strong { display:block; margin-top:3px; font-size:16px; color:#1c1917; }

/* Pending alert banner */
.bk-pending-banner {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 11px 16px;
    background: #fffbeb;
    border-bottom: 1px solid #fde68a;
}
.bk-pending-banner .bk-pb-icon {
    color: #92400e;
    flex-shrink: 0;
    margin-top: 1px;
}
.bk-pending-banner .bk-pb-title {
    font-size: 13px;
    font-weight: 600;
    color: #92400e;
}
.bk-pending-banner .bk-pb-sub {
    font-size: 12px;
    color: #b45309;
    margin-top: 1px;
}

/* Section card */
.bk-section {
    background: #fff;
    border: 1px solid #e7e5e4;
    border-radius: 1.2rem;
    overflow: hidden;
    box-shadow: 0 1px 2px rgba(28,25,23,.05);
}

/* Toolbar */
.bk-toolbar {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 16px 20px;
    border-bottom: 1px solid #e7e5e4;
    flex-wrap: wrap;
}
.bk-filter-group {
    display: flex;
    align-items: center;
    gap: 4px;
    flex-wrap: wrap;
}
.bk-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 12px;
    font-weight: 600;
    padding: 4px 12px;
    border-radius: 999px;
    border: 1px solid #e7e5e4;
    background: #f5f5f3;
    color: #57534e;
    text-decoration: none;
    transition: all 0.12s ease;
    white-space: nowrap;
}
.bk-pill:hover {
    background: var(--color-app-soft, #f9fafb);
    color: var(--color-app-text, #111827);
}
.bk-pill-active-all {
    background: #673049;
    color: #fff;
    border-color: #673049;
}
.bk-pill-active-pending {
    background: #92400e;
    color: #fff;
    border-color: #92400e;
}
.bk-pill-active-confirmed {
    background: #065f46;
    color: #fff;
    border-color: #065f46;
}
.bk-pill-active-completed {
    background: #1e40af;
    color: #fff;
    border-color: #1e40af;
}
.bk-pill-active-rejected {
    background: #991b1b;
    color: #fff;
    border-color: #991b1b;
}

/* Search */
.bk-search-wrap {
    margin-left: auto;
    position: relative;
}
.bk-search-wrap i[data-lucide] {
    position: absolute;
    left: 9px;
    top: 50%;
    transform: translateY(-50%);
    width: 14px;
    height: 14px;
    color: var(--color-app-muted, #9ca3af);
    pointer-events: none;
}
.bk-search-input {
    font-size: 13px;
    height: 34px;
    padding: 0 10px 0 30px;
    border-radius: 8px;
    border: 1px solid #e7e5e4;
    background: #fff;
    color: #1c1917;
    width: 220px;
    transition: border-color 0.12s;
}
.bk-search-input::placeholder { color: var(--color-app-muted, #9ca3af); }
.bk-search-input:focus {
    outline: none;
    border-color: #673049;
    box-shadow: 0 0 0 3px rgba(103,48,73,.08);
}

/* Table */
.bk-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.bk-table thead tr {
    background: #f9f8f6;
    border-bottom: 1px solid #e7e5e4;
}
.bk-table th {
    padding: 10px 16px;
    font-size: 10px;
    font-weight: 750;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #78716c;
    text-align: left;
    white-space: nowrap;
}
.bk-table th.bk-right,
.bk-table td.bk-right { text-align: right; }
.bk-table tbody tr {
    border-bottom: 1px solid #e7e5e4;
    transition: background 0.1s;
}
.bk-table tbody tr:last-child { border-bottom: none; }
.bk-table tbody tr:hover { background: #f5f5f3; }
.bk-table td {
    padding: 13px 16px;
    color: #1c1917;
    vertical-align: middle;
}

/* Customer cell */
.bk-customer-cell { display: flex; align-items: center; gap: 9px; }
.bk-avatar {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: 600;
    flex-shrink: 0;
    background: #fde8ef;
    color: #673049;
}
.bk-cname { font-weight: 650; font-size: 13px; color: #1c1917; }
.bk-cdate { font-size: 11px; color: #a8a29e; margin-top: 1px; }
.bk-ref {
    font-size: 12px;
    font-weight: 500;
    color: var(--color-app-secondary, #6b7280);
    font-family: ui-monospace, monospace;
}
.bk-amount { font-weight: 600; }

/* Status badges */
.bk-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 11px;
    font-weight: 600;
    padding: 3px 9px;
    border-radius: 999px;
    white-space: nowrap;
}
.bk-badge-dot { width: 5px; height: 5px; border-radius: 50%; }
.bk-badge-pending   { background: #fef3c7; color: #92400e; }
.bk-badge-pending .bk-badge-dot   { background: #d97706; }
.bk-badge-confirmed { background: #d1fae5; color: #065f46; }
.bk-badge-confirmed .bk-badge-dot { background: #059669; }
.bk-badge-completed { background: #dbeafe; color: #1e40af; }
.bk-badge-completed .bk-badge-dot { background: #2563eb; }
.bk-badge-rejected  { background: #fee2e2; color: #991b1b; }
.bk-badge-rejected .bk-badge-dot  { background: #ef4444; }
.bk-badge-cancelled { background: #fee2e2; color: #991b1b; }
.bk-badge-cancelled .bk-badge-dot { background: #ef4444; }
.bk-badge-replacement { background: #e8e7ff; color: #4f46a5; }
.bk-badge-replacement .bk-badge-dot { background: #6366f1; }

/* Action buttons */
.bk-actions { display: flex; align-items: center; gap: 6px; justify-content: flex-end; }
.bk-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 12px;
    font-weight: 600;
    padding: 5px 11px;
    border-radius: 7px;
    border: 1px solid var(--color-app-border, #e5e7eb);
    cursor: pointer;
    background: transparent;
    color: var(--color-app-text, #111827);
    text-decoration: none;
    transition: all 0.12s ease;
    white-space: nowrap;
}
.bk-btn i[data-lucide] { width: 13px; height: 13px; }
.bk-btn-confirm {
    background: #d1fae5;
    color: #065f46;
    border-color: #6ee7b7;
}
.bk-btn-confirm:hover {
    background: #a7f3d0;
    border-color: #34d399;
}
.bk-btn-decline {
    background: transparent;
    color: var(--color-app-secondary, #6b7280);
    border-color: var(--color-app-border, #e5e7eb);
}
.bk-btn-decline:hover {
    background: #fee2e2;
    color: #991b1b;
    border-color: #fca5a5;
}
.bk-btn-view {
    color: #673049;
    background: #fff;
    border-color: #e7e5e4;
}
.bk-btn-view:hover {
    background: #fde8ef;
    color: #673049;
    border-color: #f9c0d2;
}

/* Empty state */
.bk-empty {
    text-align: center;
    padding: 56px 24px;
}
.bk-empty-icon {
    width: 48px;
    height: 48px;
    margin: 0 auto 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 14px;
    background: var(--color-app-soft, #f9fafb);
    color: var(--color-app-muted, #9ca3af);
}
.bk-empty-icon i[data-lucide] { width: 22px; height: 22px; }
.bk-empty-title {
    font-size: 15px;
    font-weight: 600;
    color: var(--color-app-text, #111827);
}
.bk-empty-sub {
    font-size: 13px;
    color: var(--color-app-muted, #9ca3af);
    margin-top: 4px;
    max-width: 320px;
    margin-left: auto;
    margin-right: auto;
}

/* Pagination */
.bk-pagination {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 11px 16px;
    border-top: 1px solid #e7e5e4;
}
.bk-page-info { font-size: 12px; color: #78716c; }
.bk-page-btns { display: flex; align-items: center; gap: 4px; }
.bk-page-btn {
    width: 28px;
    height: 28px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 7px;
    border: 1px solid #e7e5e4;
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
    color: #57534e;
    background: #fff;
    transition: all 0.1s;
}
.bk-page-btn:hover:not(.bk-page-btn-cur):not(.bk-page-btn-disabled) {
    background: var(--color-app-soft, #f9fafb);
    color: var(--color-app-text, #111827);
}
.bk-page-btn-cur {
    background: #673049;
    color: #fff;
    border-color: #673049;
}
.bk-page-btn-disabled { opacity: 0.35; pointer-events: none; }
.bk-page-btn i[data-lucide] { width: 13px; height: 13px; }

/* Responsive */
@media (max-width: 768px) {
    .bk-kpi-row { grid-template-columns: repeat(2, 1fr); }
    .bk-hide-sm { display: none !important; }
    .bk-search-wrap { margin-left: 0; width: 100%; }
    .bk-search-input { width: 100%; }
    .bk-toolbar { gap: 6px; }
}
@media (max-width: 480px) {
    .bk-kpi-row { grid-template-columns: 1fr; }
    .bk-filter-group {
        overflow-x: auto;
        flex-wrap: nowrap;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        padding-bottom: 2px;
    }
    .bk-filter-group::-webkit-scrollbar { display: none; }
}
</style>

<section class="mx-auto max-w-[1600px] space-y-4 px-5 py-6 text-[13px] antialiased" style="font-family:Inter,sans-serif;color:#1c1917">

    <!-- Page header -->
    <div class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p style="margin-bottom:4px;color:#78716c;font-size:11px;font-weight:650">Supplier workspace</p>
            <h1 style="margin:0;color:#34232b;font-family:'Playfair Display',serif;font-size:clamp(27px,2.5vw,36px);font-weight:650;letter-spacing:-.025em;line-height:1.08">Booking operations</h1>
            <p style="margin-top:6px;color:#7b5c69;font-size:12px;font-weight:500">Review requests, track upcoming events, and follow completed work.</p>
        </div>
        <a href="<?= URLROOT ?>/supplier/calendar"
           class="inline-flex h-8 items-center gap-1.5 rounded-xl border bg-white px-3 text-xs font-semibold shadow-sm"
           style="border-color:#e7e5e4;color:#673049;text-decoration:none">
            <i data-lucide="calendar-days" class="h-3.5 w-3.5"></i>
            Open calendar
        </a>
    </div>

    <!-- ── KPI row ───────────────────────────────────────────────── -->
    <div class="bk-kpi-row">
        <div class="bk-kpi">
            <div class="bk-kpi-icon" style="background:#fff7ed;color:#c2410c"><i data-lucide="calendar-check"></i></div>
            <p class="bk-kpi-label">Total bookings</p>
            <p class="bk-kpi-value"><?= number_format($totalCount) ?></p>
            <div class="bk-kpi-breakdown">
                <div><span class="bk-kpi-label">Pending</span><strong style="color:#b45309"><?= $pendingCount ?></strong></div>
                <div><span class="bk-kpi-label">Confirmed</span><strong style="color:#07825f"><?= $confirmedCount ?></strong></div>
            </div>
        </div>
        <div class="bk-kpi">
            <div class="bk-kpi-icon" style="background:#ecfdf5;color:#047857"><i data-lucide="badge-check"></i></div>
            <p class="bk-kpi-label">Completed</p>
            <p class="bk-kpi-value"><?= $completedCount ?></p>
            <p class="bk-kpi-sub">Successfully delivered</p>
        </div>
        <div class="bk-kpi">
            <div class="bk-kpi-icon" style="background:#fdf2f8;color:#9d174d"><i data-lucide="badge-dollar-sign"></i></div>
            <p class="bk-kpi-label">Est. revenue</p>
            <p class="bk-kpi-value"><?= $money($estRevenue) ?></p>
            <p class="bk-kpi-sub">Across all bookings</p>
        </div>
    </div>

    <!-- ── Main table section ────────────────────────────────────── -->
    <div class="bk-section">

        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;padding:20px;border-bottom:1px solid #e7e5e4">
            <div>
                <h2 style="margin:0;color:#1c1917;font-size:14px;font-weight:750;letter-spacing:-.015em">Booking queue</h2>
                <p style="margin-top:3px;color:#a8a29e;font-size:11px">Customer requests and confirmed event work</p>
            </div>
            <span style="color:#78716c;font-size:11px;font-weight:650"><?= number_format($totalCount) ?> records</span>
        </div>

        <!-- Pending alert banner — only shown when there are pending bookings -->
        <?php if ($pendingCount > 0): ?>
        <div class="bk-pending-banner">
            <i data-lucide="alert-circle" class="bk-pb-icon h-4 w-4"></i>
            <div>
                <p class="bk-pb-title"><?= $pendingCount ?> booking<?= $pendingCount !== 1 ? 's' : '' ?> waiting for your response</p>
                <p class="bk-pb-sub">Respond within 24 hours to maintain a good acceptance rate</p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Toolbar: filters + search -->
        <div class="bk-toolbar">
            <div class="bk-filter-group">
                <?php foreach ($filters as $key => $f):
                    $filterUrl = URLROOT . '/supplier/bookings?status=' . $h($key);
                    if ($searchQuery) {
                        $filterUrl .= '&search=' . urlencode($searchQuery);
                    }
                    // Build active class name
                    $isActive = $activeFilter === $key;
                    $activeClass = $isActive ? 'bk-pill-active-' . $h($key) : '';
                    // Label with count badge for pending
                    $label = $h($f['label']);
                    if ($key === 'pending' && $pendingCount > 0) {
                        $label .= ' (' . $pendingCount . ')';
                    }
                ?>
                <a href="<?= $filterUrl ?>"
                   class="bk-pill <?= $activeClass ?>">
                    <?= $label ?>
                </a>
                <?php endforeach; ?>
            </div>

            <!-- Search -->
            <form method="get" class="bk-search-wrap">
                <input type="hidden" name="status" value="<?= $h($activeFilter) ?>">
                <i data-lucide="search"></i>
                <input
                    type="text"
                    name="search"
                    placeholder="Search bookings…"
                    value="<?= $h($searchQuery) ?>"
                    class="bk-search-input"
                    aria-label="Search bookings"
                >
            </form>
        </div>

        <!-- Table -->
        <?php if (empty($bookings)): ?>
            <div class="bk-empty">
                <div class="bk-empty-icon">
                    <i data-lucide="calendar-x"></i>
                </div>
                <p class="bk-empty-title">
                    <?= $searchQuery ? 'No results for "' . $h($searchQuery) . '"' : 'No bookings found' ?>
                </p>
                <p class="bk-empty-sub">
                    <?= $searchQuery
                        ? 'Try a different name or booking reference.'
                        : 'Incoming customer bookings will appear here.' ?>
                </p>
                <?php if ($searchQuery): ?>
                <a href="<?= URLROOT ?>/supplier/bookings?status=<?= $h($activeFilter) ?>"
                   class="bk-btn bk-btn-view mt-4 inline-flex">
                    <i data-lucide="x"></i>
                    Clear search
                </a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="bk-table">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th class="bk-hide-sm">Booking ref</th>
                            <th class="bk-hide-sm">Event date</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th class="bk-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking):
                            $bStatus  = strtolower($booking['supplier_status'] ?? 'pending');
                            $initials = strtoupper(substr($booking['customer_name'] ?? 'C', 0, 1));
                            $eventDate = $booking['event_date'] ?? ($booking['booking_date'] ?? '—');
                        ?>
                        <tr>
                            <!-- Customer -->
                            <td>
                                <div class="bk-customer-cell">
                                    <div class="bk-avatar"><?= $h($initials) ?></div>
                                    <div>
                                        <div class="bk-cname"><?= $h($booking['customer_name'] ?? 'Customer') ?></div>
                                        <div class="bk-cdate bk-hide-sm"><?= $h($eventDate) ?></div>
                                    </div>
                                </div>
                            </td>

                            <!-- Ref -->
                            <td class="bk-hide-sm">
                                <span class="bk-ref"><?= $h($booking['booking_ref'] ?? '') ?></span>
                            </td>

                            <!-- Event date (desktop column) -->
                            <td class="bk-hide-sm" style="color: var(--color-app-secondary, #6b7280);">
                                <?= $h($eventDate) ?>
                            </td>

                            <!-- Amount -->
                            <td class="bk-amount">
                                <?= $money($booking['total_amount'] ?? 0) ?>
                            </td>

                            <!-- Status badge -->
                            <td>
                                <?php
                                $isReplacementChip = $bStatus === 'needs_replacement';
                                $badgeClass = $isReplacementChip ? 'replacement' : $bStatus;
                                $badgeLabel = $isReplacementChip ? 'Replacement pending' : ucfirst($bStatus);
                                ?>
                                <span class="bk-badge bk-badge-<?= $h($badgeClass) ?>">
                                    <span class="bk-badge-dot"></span>
                                    <?= $h($badgeLabel) ?>
                                </span>
                            </td>

	                            <!-- Actions -->
	                            <td class="bk-right">
	                                <div class="bk-actions">
	                                    <a class="bk-btn bk-btn-view"
	                                       href="<?= URLROOT ?>/supplier/bookingDetail/<?= (int)$booking['id'] ?>">
	                                        <i data-lucide="<?= $bStatus === 'pending' ? 'clipboard-check' : 'eye' ?>"></i>
	                                        <?= $bStatus === 'pending' ? 'Review' : 'View' ?>
	                                    </a>
	                                </div>
	                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1):
                $pageParams = 'status=' . $h($activeFilter);
                if ($searchQuery) {
                    $pageParams .= '&search=' . urlencode($searchQuery);
                }
            ?>
            <div class="bk-pagination">
                <span class="bk-page-info">
                    Showing <?= (($currentPage - 1) * $perPage) + 1 ?>–<?= min($currentPage * $perPage, $totalCount) ?> of <?= $totalCount ?>
                </span>
                <div class="bk-page-btns">
                    <!-- Prev -->
                    <?php if ($currentPage > 1): ?>
                    <a href="<?= URLROOT ?>/supplier/bookings?<?= $pageParams ?>&page=<?= $currentPage - 1 ?>"
                       class="bk-page-btn" aria-label="Previous page">
                        <i data-lucide="chevron-left"></i>
                    </a>
                    <?php else: ?>
                    <span class="bk-page-btn bk-page-btn-disabled" aria-disabled="true">
                        <i data-lucide="chevron-left"></i>
                    </span>
                    <?php endif; ?>

                    <!-- Page numbers -->
                    <?php for ($p = 1; $p <= $totalPages; $p++):
                        $showPage = ($p === 1)
                            || ($p === $totalPages)
                            || ($p >= $currentPage - 1 && $p <= $currentPage + 1);
                        $isEllipsisBefore = ($p === 2 && $currentPage > 3);
                        $isEllipsisAfter  = ($p === $totalPages - 1 && $currentPage < $totalPages - 2);
                    ?>
                        <?php if ($showPage): ?>
                            <?php if ($p === $currentPage): ?>
                            <span class="bk-page-btn bk-page-btn-cur" aria-current="page"><?= $p ?></span>
                            <?php else: ?>
                            <a href="<?= URLROOT ?>/supplier/bookings?<?= $pageParams ?>&page=<?= $p ?>"
                               class="bk-page-btn"><?= $p ?></a>
                            <?php endif; ?>
                        <?php elseif ($isEllipsisBefore || $isEllipsisAfter): ?>
                            <span style="padding: 0 4px; color: var(--color-app-muted, #9ca3af); font-size: 12px;">…</span>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <!-- Next -->
                    <?php if ($currentPage < $totalPages): ?>
                    <a href="<?= URLROOT ?>/supplier/bookings?<?= $pageParams ?>&page=<?= $currentPage + 1 ?>"
                       class="bk-page-btn" aria-label="Next page">
                        <i data-lucide="chevron-right"></i>
                    </a>
                    <?php else: ?>
                    <span class="bk-page-btn bk-page-btn-disabled" aria-disabled="true">
                        <i data-lucide="chevron-right"></i>
                    </span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        <?php endif; ?>

    </div><!-- /.bk-section -->

</section>
<?php
};
?>
<!DOCTYPE html>
<html lang="en">
<head><?php require_once APPROOT . '/views/dashboardLayout/head.php'; ?></head>
<body class="grid h-screen gap-0 bg-app-page" style="grid-template-columns: 280px 1fr;">
  <?php require APPROOT . '/views/dashboardLayout/suppliersidebar.php'; ?>
</body>
</html>
