<?php
$bookings = $bookings ?? [];
$stats = $stats ?? [];
$activeFilter = $activeFilter ?? 'all';
$money = fn($v) => number_format((float)$v, 0) . ' MMK';
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
$dashboardContentClass = 'bg-[#F4F1EE] px-0 py-0 overflow-y-auto';
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
<link rel="stylesheet" href="<?= URLROOT ?>/public/css/supplier-bookings.css?v=<?= filemtime(APPROOT . '/../public/css/supplier-bookings.css') ?>">
<script src="<?= URLROOT ?>/public/js/supplier-toast.js"></script>

<section class="mx-auto max-w-[1600px] space-y-4 px-5 py-6 text-[13px] antialiased" style="font-family:'Poppins',system-ui,sans-serif;color:#6d4c5b">

    <!-- Page header -->
    <div class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p style="margin-bottom:4px;color:#A8A29E;font-size:11px;font-weight:650">Supplier workspace</p>
            <h1 style="margin:0;color:#6d4c5b;font-family:'Playfair Display',serif;font-size:clamp(27px,2.5vw,36px);font-weight:650;letter-spacing:-.025em;line-height:1.08">Booking operations</h1>
            <p style="margin-top:6px;color:#7b5c69;font-size:12px;font-weight:500">Review requests, track upcoming events, and follow completed work.</p>
        </div>
        <div class="bk-nav-group">
            <a href="<?= URLROOT ?>/supplier/assignments" class="bk-nav-link">
                <i data-lucide="clipboard-list"></i>
                Assignments
            </a>
            <a href="<?= URLROOT ?>/supplier/calendar" class="bk-nav-link">
                <i data-lucide="calendar-days"></i>
                Calendar
            </a>
        </div>
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

        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;padding:20px;border-bottom:1px solid #ead8c7">
            <div>
                <h2 style="margin:0;color:#6d4c5b;font-size:14px;font-weight:750;letter-spacing:-.015em">Booking queue</h2>
                <p style="margin-top:3px;color:#A8A29E;font-size:11px">Customer requests and confirmed event work</p>
            </div>
            <span style="color:#A8A29E;font-size:11px;font-weight:650"><?= number_format($totalCount) ?> records</span>
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
                                        <div class="bk-cdate"><?= $h($eventDate) ?></div>
                                    </div>
                                </div>
                            </td>

                            <!-- Ref -->
                            <td class="bk-hide-sm">
                                <span class="bk-ref"><?= $h($booking['booking_ref'] ?? '') ?></span>
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
                                    <?php if ($bStatus === 'pending'): ?>
                                    <button type="button"
                                            class="bk-btn bk-btn-accept-sm bk-quick-accept"
                                            data-booking-id="<?= (int)$booking['id'] ?>"
                                            title="Accept this booking">
                                        <i data-lucide="check"></i>
                                        Accept
                                    </button>
                                    <?php endif; ?>
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
                            <span style="padding: 0 4px; color: #A8A29E; font-size: 12px;">…</span>
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
<script>
/* Quick accept from table row */
document.querySelectorAll('.bk-quick-accept').forEach(function(btn) {
    btn.addEventListener('click', async function(e) {
        e.preventDefault();
        var bookingId = btn.dataset.bookingId;
        if (!bookingId) return;
        btn.disabled = true;
        btn.innerHTML = '<svg style="width:13px;height:13px;animation:spin .6s linear infinite" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><circle cx="8" cy="8" r="6" stroke-dasharray="30" stroke-dashoffset="10"/></svg> Accepting…';
        var formData = new FormData();
        formData.append('booking_id', bookingId);
        formData.append('action', 'accept');
        formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]')?.content || '');
        try {
            var resp = await fetch('<?= URLROOT ?>/supplier/bookingRespond', { method: 'POST', body: formData });
            var data = await resp.json().catch(function() { return {}; });
            if (data.success) {
                supToastSuccess('Booking accepted successfully!');
                setTimeout(function() { window.location.reload(); }, 1200);
                return;
            }
            supToastError(data.error || 'Could not accept booking. Please try again.');
        } catch (err) {
            supToastError('Network error. Please try again.');
        }
        btn.disabled = false;
        btn.innerHTML = '<i data-lucide="check"></i> Accept';
        if (window.lucide) lucide.createIcons();
    });
});
</script>
<style>@keyframes spin{to{transform:rotate(360deg)}}</style>
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
