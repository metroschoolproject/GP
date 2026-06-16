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
$dashboardContentClass = 'bg-app-content px-6 py-6 overflow-y-auto';
$dashboardContent = function () use ($bookings, $stats, $activeFilter, $filters, $money, $h, $statusBadgeClass, $currentPage, $totalPages, $totalCount, $perPage, $searchQuery) {
    $pendingCount  = (int)($stats['pending_count'] ?? 0);
    $confirmedCount = (int)($stats['confirmed_count'] ?? 0);
    $completedCount = (int)($stats['completed_count'] ?? 0);
    $estRevenue     = (float)($stats['est_revenue'] ?? 0);
    $currentPage = $currentPage ?? 1;
    $totalPages = $totalPages ?? 1;
    $totalCount = $totalCount ?? 0;
    $perPage = $perPage ?? 20;
    $searchQuery = $searchQuery ?? '';
?>
<style>
  .bk-stat-card {
    transition: transform 140ms ease, box-shadow 140ms ease;
  }
  .bk-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(34,24,19,0.08);
  }
  .bk-table-row {
    transition: background 120ms ease;
  }
  .bk-table-row:hover {
    background: var(--color-app-input, #f9fafb);
  }
  .bk-filter-pill {
    transition: all 160ms ease;
  }
  @media (max-width: 640px) {
    .bk-stats-grid {
      grid-template-columns: repeat(2, 1fr) !important;
    }
    .bk-filter-bar {
      flex-wrap: nowrap;
      overflow-x: auto;
      -webkit-overflow-scrolling: touch;
      scrollbar-width: none;
      padding-bottom: 4px;
    }
    .bk-filter-bar::-webkit-scrollbar { display: none; }
    .bk-table th, .bk-table td {
      padding-left: 0.75rem !important;
      padding-right: 0.75rem !important;
    }
  }
</style>

<section class="mx-auto max-w-[1600px] space-y-5 font-ui text-[13px] text-app-text antialiased">

  <!-- ===== Stat cards row ===== -->
  <div class="bk-stats-grid grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
    <!-- Pending -->
    <div class="bk-stat-card rounded-card border border-app-border bg-app-input p-4 shadow-sm">
      <div class="mb-3 flex h-8 w-8 items-center justify-center rounded-lg bg-app-warning/10 text-app-warning">
        <i data-lucide="clock" class="h-4 w-4"></i>
      </div>
      <p class="text-xs font-semibold text-app-muted uppercase tracking-wide">Pending</p>
      <p class="mt-1 text-2xl font-bold tracking-tight text-app-text"><?= $pendingCount ?></p>
      <p class="mt-0.5 text-[11px] text-app-muted">Awaiting your response</p>
    </div>

    <!-- Confirmed -->
    <div class="bk-stat-card rounded-card border border-app-border bg-app-input p-4 shadow-sm">
      <div class="mb-3 flex h-8 w-8 items-center justify-center rounded-lg bg-app-success/10 text-app-success">
        <i data-lucide="check-circle" class="h-4 w-4"></i>
      </div>
      <p class="text-xs font-semibold text-app-muted uppercase tracking-wide">Confirmed</p>
      <p class="mt-1 text-2xl font-bold tracking-tight text-app-text"><?= $confirmedCount ?></p>
      <p class="mt-0.5 text-[11px] text-app-muted">Upcoming bookings</p>
    </div>

    <!-- Completed -->
    <div class="bk-stat-card rounded-card border border-app-border bg-app-input p-4 shadow-sm">
      <div class="mb-3 flex h-8 w-8 items-center justify-center rounded-lg bg-app-soft text-app-accent">
        <i data-lucide="badge-check" class="h-4 w-4"></i>
      </div>
      <p class="text-xs font-semibold text-app-muted uppercase tracking-wide">Completed</p>
      <p class="mt-1 text-2xl font-bold tracking-tight text-app-text"><?= $completedCount ?></p>
      <p class="mt-0.5 text-[11px] text-app-muted">Successfully delivered</p>
    </div>

    <!-- Est. Revenue (dark standout card) -->
    <div class="bk-stat-card relative overflow-hidden rounded-card bg-app-text p-4 text-app-white shadow-xl">
      <div class="relative z-10">
        <div class="mb-3 flex h-8 w-8 items-center justify-center rounded-lg bg-app-white/10 text-app-white">
          <i data-lucide="wallet" class="h-4 w-4"></i>
        </div>
        <p class="text-xs font-semibold uppercase tracking-wide text-app-white/70">Est. Revenue</p>
        <p class="mt-1 text-2xl font-bold tracking-tight text-app-white"><?= $money($estRevenue) ?></p>
        <p class="mt-0.5 text-[11px] text-app-white/50">Across all bookings</p>
      </div>
      <svg class="absolute -right-4 -top-4 h-20 w-20 rotate-12 text-app-white/5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3-1.9 5.8a2 2 0 0 1-1.287 1.288L3 12l5.8 1.9a2 2 0 0 1 1.288 1.287L12 21l1.9-5.8a2 2 0 0 1 1.287-1.288L21 12l-5.8-1.9a2 2 0 0 1-1.288-1.287Z"/></svg>
    </div>
  </div>

  <!-- ===== Header + Search + Filter bar ===== -->
  <div class="flex flex-col gap-3">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h2 class="text-base font-bold text-app-text">All Bookings</h2>
        <p class="mt-0.5 text-xs text-app-muted">Manage and respond to your customer bookings</p>
      </div>
    </div>

    <!-- Search bar -->
    <form method="get" class="flex items-center gap-2">
      <input type="hidden" name="status" value="<?= $h($activeFilter) ?>">
      <div class="relative flex-1 sm:max-w-xs">
        <i data-lucide="search" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-app-muted"></i>
        <input type="text" name="search" placeholder="Search by customer name or booking..."
               value="<?= $h($searchQuery) ?>"
               class="h-9 w-full rounded-lg border border-app-border bg-app-input pl-9 pr-3 text-sm text-app-text placeholder-app-muted focus:border-app-primary focus:outline-none focus:ring-1 focus:ring-app-primary/20">
      </div>
      <?php if ($searchQuery): ?>
      <a href="<?= URLROOT ?>/supplier/bookings?status=<?= $h($activeFilter) ?>"
         class="inline-flex items-center gap-1.5 rounded-lg border border-app-border bg-app-input px-3 py-2 text-xs font-semibold text-app-secondary hover:text-app-text transition-colors">
        <i data-lucide="x" class="h-3.5 w-3.5"></i>
        Clear
      </a>
      <?php endif; ?>
    </form>

    <!-- Filter pills -->
    <div class="bk-filter-bar flex items-center gap-1.5">
      <?php foreach ($filters as $key => $f):
        $filterUrl = URLROOT . '/supplier/bookings?status=' . $h($key);
        if ($searchQuery) {
          $filterUrl .= '&search=' . urlencode($searchQuery);
        }
      ?>
        <a href="<?= $filterUrl ?>"
           class="bk-filter-pill inline-flex items-center gap-1.5 rounded-full px-3.5 py-1.5 text-xs font-semibold transition-all
                  <?= $activeFilter === $key
                    ? 'bg-app-primary text-app-white shadow-sm'
                    : 'border border-app-border bg-app-input text-app-secondary hover:text-app-text hover:border-app-muted' ?>">
          <i data-lucide="<?= $h($f['icon']) ?>" class="h-3 w-3"></i>
          <?= $h($f['label']) ?>
        </a>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- ===== Bookings table ===== -->
  <div class="overflow-hidden rounded-card border border-app-border bg-app-input shadow-sm">
    <?php if (empty($bookings)): ?>
      <div class="flex flex-col items-center justify-center px-6 py-16 text-center">
        <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-app-soft text-app-muted">
          <i data-lucide="calendar-x" class="h-6 w-6"></i>
        </div>
        <p class="text-base font-semibold text-app-text">No bookings found</p>
        <p class="mt-1 max-w-sm text-sm text-app-muted">Incoming customer bookings will appear here. When a customer books your service, you'll be able to review and respond.</p>
      </div>
    <?php else: ?>
      <div class="overflow-x-auto">
        <table class="bk-table min-w-full text-left text-sm">
          <thead>
            <tr class="border-b border-app-border bg-app-soft/60">
              <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-app-muted">Booking</th>
              <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-app-muted">Customer</th>
              <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-app-muted">Services</th>
              <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-app-muted">Amount</th>
              <th class="px-4 py-3 text-[10px] font-semibold uppercase tracking-wider text-app-muted">Status</th>
              <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-app-muted">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-app-border">
            <?php foreach ($bookings as $booking):
              $bStatus = strtolower($booking['supplier_status'] ?? 'pending');
            ?>
              <tr class="bk-table-row">
                <td class="px-4 py-3.5">
                  <p class="font-semibold text-app-text"><?= $h($booking['booking_ref'] ?? '') ?></p>
                </td>
                <td class="px-4 py-3.5">
                  <div class="flex items-center gap-2.5">
                    <div class="flex h-7 w-7 items-center justify-center rounded-full bg-app-soft text-[10px] font-semibold text-app-secondary">
                      <?= $h(strtoupper(substr($booking['customer_name'] ?? 'C', 0, 1))) ?>
                    </div>
                    <span class="text-app-secondary"><?= $h($booking['customer_name'] ?? 'Customer') ?></span>
                  </div>
                </td>
                <td class="px-4 py-3.5 text-app-secondary">
                  <?= (int)($booking['item_count'] ?? count($booking['items'] ?? [])) ?> item(s)
                </td>
                <td class="px-4 py-3.5 font-semibold text-app-text">
                  <?= $money($booking['total_amount'] ?? 0) ?>
                </td>
                <td class="px-4 py-3.5">
                  <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[11px] font-semibold <?= $statusBadgeClass($bStatus) ?>">
                    <span class="h-1.5 w-1.5 rounded-full bg-current opacity-70"></span>
                    <?= $h(ucfirst($bStatus)) ?>
                  </span>
                </td>
                <td class="px-4 py-3.5 text-right">
                  <a class="inline-flex items-center gap-1.5 rounded-lg border border-app-border bg-app-panel px-3 py-2 text-xs font-semibold text-app-text transition hover:bg-app-primary hover:text-app-white hover:border-app-primary hover:shadow-sm"
                     href="<?= URLROOT ?>/supplier/bookingDetail/<?= (int)$booking['id'] ?>">
                    <i data-lucide="eye" class="h-3.5 w-3.5"></i>
                    View
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- ===== Pagination ===== -->
      <?php if ($totalPages > 1): ?>
      <div class="flex items-center justify-between border-t border-app-border px-4 py-3.5">
        <p class="text-xs font-medium text-app-muted">
          Showing <?= (($currentPage - 1) * $perPage) + 1 ?> to <?= min($currentPage * $perPage, $totalCount) ?> of <?= $totalCount ?> bookings
        </p>
        <div class="flex items-center gap-1.5">
          <?php
            $pageParams = 'status=' . $h($activeFilter);
            if ($searchQuery) {
              $pageParams .= '&search=' . urlencode($searchQuery);
            }
          ?>
          <?php if ($currentPage > 1): ?>
          <a href="<?= URLROOT ?>/supplier/bookings?<?= $pageParams ?>&page=<?= $currentPage - 1 ?>"
             class="flex h-7 w-7 items-center justify-center rounded-md border border-app-border text-app-secondary hover:bg-app-soft transition-colors">
            <i data-lucide="chevron-left" class="h-3.5 w-3.5"></i>
          </a>
          <?php else: ?>
          <span class="flex h-7 w-7 items-center justify-center rounded-md border border-app-border text-app-muted opacity-50">
            <i data-lucide="chevron-left" class="h-3.5 w-3.5"></i>
          </span>
          <?php endif; ?>

          <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <?php if ($p === 1 || $p === $totalPages || ($p >= $currentPage - 1 && $p <= $currentPage + 1)): ?>
              <?php if ($p === $currentPage): ?>
              <button class="flex h-7 w-7 items-center justify-center rounded-md bg-app-primary text-app-white text-[11px] font-semibold" disabled>
                <?= $p ?>
              </button>
              <?php else: ?>
              <a href="<?= URLROOT ?>/supplier/bookings?<?= $pageParams ?>&page=<?= $p ?>"
                 class="flex h-7 w-7 items-center justify-center rounded-md border border-app-border text-app-secondary hover:bg-app-soft transition-colors text-[11px] font-semibold">
                <?= $p ?>
              </a>
              <?php endif; ?>
            <?php elseif ($p === 2 && $currentPage > 3): ?>
              <span class="px-1.5 text-app-muted">...</span>
            <?php elseif ($p === $totalPages - 1 && $currentPage < $totalPages - 2): ?>
              <span class="px-1.5 text-app-muted">...</span>
            <?php endif; ?>
          <?php endfor; ?>

          <?php if ($currentPage < $totalPages): ?>
          <a href="<?= URLROOT ?>/supplier/bookings?<?= $pageParams ?>&page=<?= $currentPage + 1 ?>"
             class="flex h-7 w-7 items-center justify-center rounded-md border border-app-border text-app-secondary hover:bg-app-soft transition-colors">
            <i data-lucide="chevron-right" class="h-3.5 w-3.5"></i>
          </a>
          <?php else: ?>
          <span class="flex h-7 w-7 items-center justify-center rounded-md border border-app-border text-app-muted opacity-50">
            <i data-lucide="chevron-right" class="h-3.5 w-3.5"></i>
          </span>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>

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
