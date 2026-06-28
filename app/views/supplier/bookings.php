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

$dashboardTitle = 'Bookings';
$dashboardCrumb = 'Incoming bookings';
$dashboardContentClass = 'bg-[#F4F1EE] px-0 py-0 overflow-y-auto';
$dashboardContent = function () use ($bookings, $stats, $activeFilter, $filters, $money, $h, $currentPage, $totalPages, $totalCount, $perPage, $searchQuery) {
    $pendingCount   = (int)($stats['pending_count'] ?? 0);
    $confirmedCount = (int)($stats['confirmed_count'] ?? 0);
    $completedCount = (int)($stats['completed_count'] ?? 0);
    $currentPage    = $currentPage ?? 1;
    $totalPages     = $totalPages ?? 1;
    $totalCount     = $totalCount ?? 0;
    $perPage        = $perPage ?? 10;
    $searchQuery    = $searchQuery ?? '';

    // Days-until helper
    $daysUntil = function (?string $date): ?int {
        if (empty($date)) return null;
        $ts = new DateTimeImmutable($date);
        $now = new DateTimeImmutable('today');
        return (int)$now->diff($ts)->format('%r%a');
    };

    // Date formatter
    $formatDate = function (?string $v): string {
        if (empty($v)) return '—';
        $t = strtotime($v);
        return $t ? date('M d, Y', $t) : '—';
    };
    $formatDateShort = function (?string $v): string {
        if (empty($v)) return '—';
        $t = strtotime($v);
        return $t ? date('M d', $t) : '—';
    };
?>
<link rel="stylesheet" href="<?= URLROOT ?>/public/css/supplier-bookings.css?v=<?= filemtime(APPROOT . '/../public/css/supplier-bookings.css') ?>">
<script src="<?= URLROOT ?>/public/js/supplier-toast.js"></script>

<section class="mx-auto max-w-[1600px] space-y-4 px-5 py-6 text-[13px] antialiased" style="font-family:'Poppins',system-ui,sans-serif;color:#6d4c5b">

    <!-- ── Page header ───────────────────────────────────────────── -->
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

    <!-- ── Main section ──────────────────────────────────────────── -->
    <div class="bk-section">

        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;padding:20px;border-bottom:1px solid #ead8c7">
            <div>
                <h2 style="margin:0;color:#6d4c5b;font-size:14px;font-weight:750;letter-spacing:-.015em">Booking queue</h2>
                <p style="margin-top:3px;color:#A8A29E;font-size:11px">Your bookings across all statuses</p>
            </div>
            <span style="color:#A8A29E;font-size:11px;font-weight:650"><?= number_format($totalCount) ?> records</span>
        </div>

        <!-- Pending alert banner -->
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
                    $isActive = $activeFilter === $key;
                    $activeClass = $isActive ? 'bk-pill-active-' . $h($key) : '';
                    $label = $h($f['label']);
                    if ($key === 'pending' && $pendingCount > 0) {
                        $label .= ' (' . $pendingCount . ')';
                    }
                ?>
                <a href="<?= $filterUrl ?>" class="bk-pill <?= $activeClass ?>">
                    <?= $label ?>
                </a>
                <?php endforeach; ?>
            </div>
            <form method="get" class="bk-search-wrap">
                <input type="hidden" name="status" value="<?= $h($activeFilter) ?>">
                <i data-lucide="search"></i>
                <input type="text" name="search" placeholder="Search bookings…"
                       value="<?= $h($searchQuery) ?>" class="bk-search-input" aria-label="Search bookings">
            </form>
        </div>

        <!-- ── Booking table ───────────────────────────────────────── -->
        <?php if (empty($bookings)): ?>
            <div class="bk-empty">
                <div class="bk-empty-icon"><i data-lucide="calendar-x"></i></div>
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
                    <i data-lucide="x"></i> Clear search
                </a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div style="overflow-x:auto">
                <table class="bk-table">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Event date</th>
                            <th class="bk-hide-sm">Services</th>
                            <th class="bk-right">Amount</th>
                            <th>Status</th>
                            <th class="bk-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($bookings as $booking):
                        $bStatus   = strtolower($booking['supplier_status'] ?? 'pending');
                        $bookingId = (int)$booking['id'];
                        $customer  = $h($booking['customer_name'] ?? 'Customer');
                        $initials  = strtoupper(mb_substr($booking['customer_name'] ?? 'C', 0, 1));
                        $eventDate = $booking['event_date'] ?? null;
                        $amount    = (float)($booking['total_amount'] ?? 0);
                        $items     = $booking['items'] ?? [];

                        // Days until event
                        $days = $daysUntil($eventDate);
                        $daysLabel = '';
                        $daysColor = '#A8A29E';
                        if ($days !== null) {
                            if ($days < 0)       { $daysLabel = 'Passed'; $daysColor = '#78716C'; }
                            elseif ($days === 0)  { $daysLabel = 'Today';  $daysColor = '#dc2626'; }
                            elseif ($days <= 7)   { $daysLabel = $days . 'd';  $daysColor = '#dc2626'; }
                            elseif ($days <= 21)  { $daysLabel = $days . 'd';  $daysColor = '#b45309'; }
                            else                  { $daysLabel = $days . 'd'; }
                        }

                        // Service names from items
                        $serviceNames = [];
                        foreach ($items as $item) {
                            $name = trim((string)($item['service_name'] ?? $item['item_name'] ?? ''));
                            if ($name !== '') $serviceNames[] = $name;
                        }
                        $serviceNames = array_unique($serviceNames);
                        $servicesStr = implode(', ', array_slice($serviceNames, 0, 3));
                        if (count($serviceNames) > 3) $servicesStr .= ' +' . (count($serviceNames) - 3);

                        // Badge
                        $isReplacement = $bStatus === 'needs_replacement';
                        $badgeClass = $isReplacement ? 'replacement' : $bStatus;
                        $badgeLabel = $isReplacement ? 'Replacement' : ucfirst($bStatus);
                    ?>
                        <tr>
                            <!-- Customer -->
                            <td>
                                <div class="bk-customer-cell">
                                    <div class="bk-avatar"><?= $h($initials) ?></div>
                                    <div>
                                        <div class="bk-cname"><?= $customer ?></div>
                                        <div class="bk-cdate"><?= $h($booking['booking_ref'] ?? '') ?></div>
                                    </div>
                                </div>
                            </td>
                            <!-- Event date + countdown -->
                            <td>
                                <div style="font-weight:600;color:#6d4c5b"><?= $h($formatDate($eventDate)) ?></div>
                                <?php if ($daysLabel): ?>
                                <div style="font-size:10px;font-weight:650;color:<?= $daysColor ?>;margin-top:1px"><?= $daysLabel ?><?= $days > 0 ? ' away' : '' ?></div>
                                <?php endif; ?>
                            </td>
                            <!-- Services -->
                            <td class="bk-hide-sm">
                                <?php if ($servicesStr): ?>
                                <span style="font-size:12px;color:#7b5c69"><?= $h($servicesStr) ?></span>
                                <?php else: ?>
                                <span style="color:#ddd0c8">—</span>
                                <?php endif; ?>
                            </td>
                            <!-- Amount -->
                            <td class="bk-right bk-amount"><?= $money($amount) ?></td>
                            <!-- Status -->
                            <td>
                                <span class="bk-badge bk-badge-<?= $h($badgeClass) ?>">
                                    <span class="bk-badge-dot"></span>
                                    <?= $h($badgeLabel) ?>
                                </span>
                            </td>
                            <!-- Actions -->
                            <td class="bk-right">
                                <div class="bk-actions">
                                    <?php if ($bStatus === 'pending'): ?>
                                    <button type="button" class="bk-btn bk-btn-accept-sm bk-quick-accept"
                                            data-booking-id="<?= $bookingId ?>" title="Accept">
                                        <i data-lucide="check"></i> Accept
                                    </button>
                                    <?php endif; ?>
                                    <a class="bk-btn bk-btn-view"
                                       href="<?= URLROOT ?>/supplier/bookingDetail/<?= $bookingId ?>">
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
                    <?php if ($currentPage > 1): ?>
                    <a href="<?= URLROOT ?>/supplier/bookings?<?= $pageParams ?>&page=<?= $currentPage - 1 ?>"
                       class="bk-page-btn" aria-label="Previous page"><i data-lucide="chevron-left"></i></a>
                    <?php else: ?>
                    <span class="bk-page-btn bk-page-btn-disabled" aria-disabled="true"><i data-lucide="chevron-left"></i></span>
                    <?php endif; ?>
                    <?php for ($p = 1; $p <= $totalPages; $p++):
                        $showPage = ($p === 1) || ($p === $totalPages) || ($p >= $currentPage - 1 && $p <= $currentPage + 1);
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
                            <span style="padding:0 4px;color:#A8A29E;font-size:12px">…</span>
                        <?php endif; ?>
                    <?php endfor; ?>
                    <?php if ($currentPage < $totalPages): ?>
                    <a href="<?= URLROOT ?>/supplier/bookings?<?= $pageParams ?>&page=<?= $currentPage + 1 ?>"
                       class="bk-page-btn" aria-label="Next page"><i data-lucide="chevron-right"></i></a>
                    <?php else: ?>
                    <span class="bk-page-btn bk-page-btn-disabled" aria-disabled="true"><i data-lucide="chevron-right"></i></span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        <?php endif; ?>

    </div><!-- /.bk-section -->

</section>

<script>
/* Quick accept from card */
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

/* Quick decline from card — redirect to detail for reason */
document.querySelectorAll('.bk-quick-decline').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        var bookingId = btn.dataset.bookingId;
        if (bookingId) window.location.href = '<?= URLROOT ?>/supplier/bookingDetail/' + bookingId;
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
