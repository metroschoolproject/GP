<?php
$bookings = $bookings ?? [];
$stats = $stats ?? [];
$activeFilter = $activeFilter ?? 'all';
$search = $search ?? '';
$sort = $sort ?? 'event_asc';
$dateFrom = $dateFrom ?? '';
$dateTo = $dateTo ?? '';
$currentPage = max(1, (int)($currentPage ?? 1));
$totalPages = max(1, (int)($totalPages ?? 1));
$totalCount = max(0, (int)($totalCount ?? count($bookings)));
$perPage = max(1, (int)($perPage ?? 15));

$money = fn($v) => number_format((float)$v, 0) . ' MMK';
$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$dateOnly = static function ($value, string $fallback = '-') {
    if (empty($value)) return $fallback;
    $timestamp = strtotime((string)$value);
    return $timestamp ? date('M j, Y', $timestamp) : $fallback;
};
$timeOnly = static function ($value, string $fallback = '') {
    if (empty($value)) return $fallback;
    $timestamp = strtotime((string)$value);
    return $timestamp ? date('H:i', $timestamp) : $fallback;
};

$dashboardTitle = 'Bookings';
$dashboardCrumb = 'All bookings';
$dashboardContentClass = 'admin-booking-outlet';

$filters = [
    'all' => 'All',
    'draft' => 'Draft',
    'pending_payment' => 'Pending',
    'paid' => 'Paid',
    'confirmed' => 'Confirmed',
    'completed' => 'Completed',
    'cancelled' => 'Cancelled',
];

$filterCounts = [
    'all' => (int)($stats['total'] ?? 0),
    'draft' => (int)($stats['draft_count'] ?? 0),
    'pending_payment' => (int)($stats['pending_payment_count'] ?? 0) + (int)($stats['payment_submitted_count'] ?? 0),
    'paid' => (int)($stats['paid_count'] ?? 0),
    'confirmed' => (int)($stats['confirmed_count'] ?? 0),
    'completed' => (int)($stats['completed_count'] ?? 0),
    'cancelled' => (int)($stats['cancelled_count'] ?? 0),
];

$statusBadge = static function (string $status) use ($h) {
    $label = match ($status) {
        'pending_payment', 'payment_submitted' => 'Pending',
        default => ucwords(str_replace('_', ' ', $status ?: 'draft')),
    };
    $class = match ($status) {
        'paid', 'completed' => 'badge-success',
        'confirmed' => 'badge-info',
        'cancelled' => 'badge-failed',
        'pending_payment', 'payment_submitted' => 'badge-pending',
        default => 'badge-neutral',
    };

    return '<span class="badge ' . $class . '">' . $h($label) . '</span>';
};

$summaryItems = [
    ['label' => 'Total bookings', 'value' => (int)($stats['total'] ?? 0), 'sub' => 'All booking records', 'class' => ''],
    ['label' => 'Paid', 'value' => (int)($stats['paid_count'] ?? 0), 'sub' => $money($stats['total_revenue'] ?? 0), 'class' => 'success'],
    ['label' => 'Confirmed', 'value' => (int)($stats['confirmed_count'] ?? 0), 'sub' => 'Supplier accepted', 'class' => ''],
    ['label' => 'Cancelled', 'value' => (int)($stats['cancelled_count'] ?? 0), 'sub' => 'Stopped bookings', 'class' => 'danger'],
];

$dashboardContent = function () use (
    $bookings,
    $activeFilter,
    $search,
    $sort,
    $dateFrom,
    $dateTo,
    $filters,
    $filterCounts,
    $summaryItems,
    $money,
    $h,
    $dateOnly,
    $timeOnly,
    $statusBadge,
    $currentPage,
    $totalPages,
    $totalCount,
    $perPage
) {
    $rangeStart = $totalCount > 0 ? (($currentPage - 1) * $perPage) + 1 : 0;
    $rangeEnd = min($currentPage * $perPage, $totalCount);
?>
<style>
  .admin-booking-outlet{min-height:100%;background:#FBFBF9;padding:28px 32px;font-family:'DM Sans',system-ui,-apple-system,sans-serif;color:#111827;font-size:13px;overflow-y:auto}
  .admin-booking-page *{box-sizing:border-box}
  .admin-booking-page{--bg:#FBFBF9;--surface:#fcf8f5;--soft:#faf5ef;--hover:#eddecc;--border:#ead8c7;--border-light:#eddecc;--primary:#6d4c5b;--primary-hover:#7b5c69;--primary-soft:#eddecc;--text:#111827;--muted:#b79c8b;--body:#7b5c69;--success-bg:#d1fae5;--success-text:#065f46;--warn-bg:#fef3c7;--warn-text:#92400e;--danger-bg:#fee2e2;--danger-text:#991b1b;--info-bg:#e8e7ff;--info-text:#4f46a5;--neutral-bg:#f3f4f6;--neutral-text:#57534e;max-width:1600px;margin:0 auto}
  .page-header{display:flex;align-items:flex-end;justify-content:space-between;gap:16px;margin-bottom:22px}
  .eyebrow{font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--muted);margin-bottom:4px}
  .admin-booking-page h1{font-size:22px;font-weight:700;color:var(--text);letter-spacing:-.3px;margin:0}
  .btn-ghost{display:inline-flex;align-items:center;gap:6px;padding:0 14px;height:34px;border:1px solid var(--border);border-radius:.75rem;background:var(--surface);color:var(--primary);font-size:12px;font-weight:700;font-family:inherit;cursor:pointer;transition:background .12s;text-decoration:none}
  .btn-ghost:hover{background:var(--primary-soft)}
  .toolbar{display:flex;align-items:center;gap:8px;margin-bottom:20px;flex-wrap:wrap}
  .filters{display:flex;gap:6px;flex-wrap:wrap}
  .filter{display:inline-flex;align-items:center;height:34px;padding:0 14px;border:1px solid var(--border);border-radius:.75rem;background:var(--soft);color:var(--body);font-size:12px;font-weight:700;font-family:inherit;cursor:pointer;transition:all .12s;white-space:nowrap;text-decoration:none}
  .filter:hover{background:var(--hover);color:var(--primary)}
  .filter.active{border-color:var(--primary);background:var(--primary);color:#fcf8f5}
  .divider{width:1px;height:20px;background:var(--border);margin:0 4px}
  .booking-search{display:flex;align-items:center;gap:6px;margin-left:auto;flex-wrap:wrap}
  .search-input{height:34px;min-width:280px;padding:0 10px;border:1px solid var(--border);border-radius:.75rem;background:var(--surface);color:var(--text);font-size:12px;font-family:inherit;font-weight:600;outline:none}
  .search-input::placeholder{color:var(--muted)}
  .search-input:focus,.control-input:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(109,76,91,.1)}
  .control-input{height:34px;padding:0 10px;border:1px solid var(--border);border-radius:.75rem;background:var(--surface);color:var(--body);font-family:inherit;font-size:11px;font-weight:600;outline:none}
  .sort-select{min-width:154px}
  .date-input{width:132px}
  .date-range{display:flex;align-items:center;gap:5px}
  .date-range-label{font-size:9px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:var(--muted)}
  .active-filter-note{display:flex;align-items:center;gap:6px;width:100%;margin-top:2px;color:var(--body);font-size:10px;font-weight:700}
  .active-filter-note svg{width:12px;height:12px;color:var(--primary)}
  .summary-row{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:20px}
  .stat{background:var(--surface);border:1px solid var(--border);border-radius:.75rem;padding:14px 16px}
  .stat-label{font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--muted);margin-bottom:6px}
  .stat-value{font-size:20px;font-weight:700;color:var(--text);letter-spacing:-.3px}
  .stat-value.success{color:var(--success-text)}
  .stat-value.danger{color:var(--danger-text)}
  .stat-sub{font-size:11px;color:var(--muted);margin-top:3px}
  .card{background:var(--surface);border:1px solid var(--border);border-radius:.75rem;overflow:hidden;box-shadow:0 1px 2px rgba(28,25,23,.04)}
  .card-head{padding:14px 20px;border-bottom:1px solid var(--border-light);display:flex;align-items:center;justify-content:space-between}
  .card-head-left{display:flex;align-items:center;gap:8px}
  .card-head-icon{width:28px;height:28px;border-radius:.75rem;background:var(--primary-soft);display:flex;align-items:center;justify-content:center;color:var(--primary);font-size:13px}
  .card-head-title{font-size:13px;font-weight:700;color:var(--text)}
  .card-count{font-size:11px;color:var(--muted);font-weight:600}
  .booking-table-wrap{overflow-x:auto}
  .booking-table{width:100%;border-collapse:collapse}
  .booking-table thead tr{background:var(--soft)}
  .booking-table thead th{padding:9px 20px;font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--muted);text-align:left;white-space:nowrap}
  .booking-table thead th:last-child{text-align:right}
  .booking-table tbody tr{border-top:1px solid var(--border-light);transition:background .1s}
  .booking-table tbody tr:hover{background:var(--soft)}
  .booking-table tbody td{padding:13px 20px;vertical-align:middle}
  .booking-table tbody td:last-child{text-align:right}
  .booking-ref{display:inline-block;max-width:130px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-family:monospace;font-size:12px;background:var(--soft);padding:3px 7px;border-radius:.5rem;border:1px solid var(--border-light);color:var(--body);font-weight:800}
  .customer-name{font-weight:700;color:var(--text);font-size:13px}
  .customer-email{font-size:11px;color:var(--muted);margin-top:2px}
  .supplier-text{display:inline-block;max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:var(--body);font-size:12px;font-weight:600}
  .amount{font-weight:700;color:var(--text);white-space:nowrap}
  .paid-text,.date-text{font-size:12px;color:var(--muted);white-space:nowrap}
  .event-date{font-weight:700;color:var(--text)}
  .event-time{display:block;margin-top:2px;color:var(--muted);font-size:10px;font-weight:700}
  .badge{display:inline-flex;align-items:center;border-radius:20px;padding:3px 9px;font-size:10px;font-weight:800;letter-spacing:.05em;text-transform:uppercase;white-space:nowrap}
  .badge-pending{background:var(--warn-bg);color:var(--warn-text)}
  .badge-success{background:var(--success-bg);color:var(--success-text)}
  .badge-failed{background:var(--danger-bg);color:var(--danger-text)}
  .badge-info{background:var(--info-bg);color:var(--info-text)}
  .badge-neutral{background:var(--neutral-bg);color:var(--neutral-text)}
  .action-link{display:inline-flex;align-items:center;gap:6px;height:28px;border:1px solid var(--border);border-radius:.75rem;background:var(--soft);padding:0 9px;color:var(--primary);font-size:11px;font-weight:800;text-decoration:none;white-space:nowrap}
  .action-link:hover{background:var(--hover)}
  .empty-row{padding:34px 20px;text-align:center;color:var(--muted)}
  .pagination{display:flex;align-items:center;justify-content:space-between;padding:12px 20px;border-top:1px solid var(--border-light)}
  .page-info{font-size:12px;color:var(--muted)}
  .page-btns{display:flex;gap:4px}
  .page-btn{height:28px;min-width:28px;padding:0 8px;border:1px solid var(--border);border-radius:.75rem;background:var(--surface);color:var(--body);font-size:12px;font-family:inherit;font-weight:600;cursor:pointer;transition:all .12s}
  .page-btn.active{background:var(--primary);color:#fcf8f5;border-color:var(--primary)}
  .page-btn:disabled{opacity:.4;cursor:default}
  @media(max-width:1250px){.summary-row{grid-template-columns:repeat(2,1fr)}.booking-search{margin-left:0;width:100%}.search-input{flex:1;min-width:180px}}
  @media(max-width:760px){.admin-booking-outlet{padding:20px 16px}.page-header{align-items:flex-start;flex-direction:column}.summary-row{grid-template-columns:1fr}.divider{display:none}.booking-search{display:grid;grid-template-columns:1fr}.search-input,.control-input,.booking-search .btn-ghost,.date-input{width:100%;min-width:0}.date-range{display:grid;grid-template-columns:auto 1fr 1fr}.active-filter-note{grid-column:1/-1}}
</style>

<div class="admin-booking-page">
  <div class="page-header">
    <div>
      <p class="eyebrow">Bookings</p>
      <h1>All Bookings</h1>
    </div>
    <a href="<?= URLROOT ?>/admin/paymentVerification" class="btn-ghost">
      <i data-lucide="receipt-text" class="h-3.5 w-3.5" aria-hidden="true"></i>
      Payment review
    </a>
  </div>

  <div class="toolbar">
    <div class="filters">
      <?php foreach ($filters as $key => $label): ?>
        <?php
          $params = [];
          if ($key !== 'all') $params['status'] = $key;
          if ($search !== '') $params['search'] = $search;
          if ($sort !== 'event_asc') $params['sort'] = $sort;
          if ($dateFrom !== '') $params['date_from'] = $dateFrom;
          if ($dateTo !== '') $params['date_to'] = $dateTo;
          $url = URLROOT . '/admin/bookings' . (!empty($params) ? '?' . http_build_query($params) : '');
        ?>
        <a href="<?= $h($url) ?>" class="filter <?= $activeFilter === $key ? 'active' : '' ?>">
          <?= $h($label) ?>
        </a>
      <?php endforeach; ?>
    </div>

    <div class="divider"></div>

    <form class="booking-search" method="get" action="<?= URLROOT ?>/admin/bookings">
      <?php if ($activeFilter !== 'all'): ?>
        <input type="hidden" name="status" value="<?= $h($activeFilter) ?>">
      <?php endif; ?>
      <input class="search-input" type="search" name="search" value="<?= $h($search) ?>" placeholder="Search booking, customer, supplier">
      <select class="control-input sort-select" name="sort" aria-label="Sort bookings">
        <option value="event_asc" <?= $sort === 'event_asc' ? 'selected' : '' ?>>Event date · upcoming first</option>
        <option value="event_desc" <?= $sort === 'event_desc' ? 'selected' : '' ?>>Event date · latest</option>
        <option value="created_desc" <?= $sort === 'created_desc' ? 'selected' : '' ?>>Booked · newest</option>
        <option value="created_asc" <?= $sort === 'created_asc' ? 'selected' : '' ?>>Booked · oldest</option>
        <option value="total_desc" <?= $sort === 'total_desc' ? 'selected' : '' ?>>Total · highest</option>
        <option value="total_asc" <?= $sort === 'total_asc' ? 'selected' : '' ?>>Total · lowest</option>
      </select>
      <div class="date-range">
        <span class="date-range-label">Event</span>
        <input class="control-input date-input" type="date" name="date_from" value="<?= $h($dateFrom) ?>" aria-label="Event date from">
        <input class="control-input date-input" type="date" name="date_to" value="<?= $h($dateTo) ?>" aria-label="Event date to">
      </div>
      <button type="submit" class="btn-ghost">
        <i data-lucide="sliders-horizontal" class="h-3.5 w-3.5" aria-hidden="true"></i>
        Apply
      </button>
      <?php if ($search !== '' || $dateFrom !== '' || $dateTo !== '' || $sort !== 'event_asc'): ?>
        <a class="btn-ghost" href="<?= URLROOT ?>/admin/bookings<?= $activeFilter !== 'all' ? '?status=' . urlencode($activeFilter) : '' ?>">Reset</a>
      <?php endif; ?>
      <div class="active-filter-note">
        <i data-lucide="calendar-clock" aria-hidden="true"></i>
        Upcoming events appear first; past events follow newest-first. Unscheduled bookings appear last.
      </div>
    </form>
  </div>

  <div class="summary-row">
    <?php foreach ($summaryItems as $item): ?>
      <div class="stat">
        <div class="stat-label"><?= $h($item['label']) ?></div>
        <div class="stat-value <?= $h($item['class']) ?>"><?= $h($item['value']) ?></div>
        <div class="stat-sub"><?= $h($item['sub']) ?></div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="card">
    <div class="card-head">
      <div class="card-head-left">
        <div class="card-head-icon"><i data-lucide="calendar-check" class="h-4 w-4" aria-hidden="true"></i></div>
        <span class="card-head-title">Booking records</span>
      </div>
      <span class="card-count">
        <?= $totalCount > 0 ? $rangeStart . '–' . $rangeEnd . ' of ' . $totalCount : '0 records' ?>
      </span>
    </div>

    <div class="booking-table-wrap">
      <table class="booking-table">
        <thead>
          <tr>
            <th>Booking</th>
            <th>Customer</th>
            <th>Suppliers</th>
            <th>Total</th>
            <th>Paid</th>
            <th>Status</th>
            <th>Event date</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($bookings)): ?>
            <tr>
              <td colspan="8" class="empty-row">No bookings found.</td>
            </tr>
          <?php endif; ?>

          <?php foreach ($bookings as $booking): ?>
            <?php
              $bookingId = (int)($booking['id'] ?? 0);
              $bookingRef = (string)($booking['booking_ref'] ?? ('BK-' . str_pad((string)$bookingId, 5, '0', STR_PAD_LEFT)));
              $customerName = (string)($booking['customer_name'] ?? 'Customer');
              $customerEmail = (string)($booking['customer_email'] ?? '');
              $supplierNames = trim((string)($booking['supplier_names'] ?? ''));
              $status = (string)($booking['status'] ?? 'draft');
              $eventDate = $dateOnly($booking['event_date'] ?? null, 'Not scheduled');
              $eventTime = $timeOnly($booking['event_start_time'] ?? null);
            ?>
            <tr>
              <td><span class="booking-ref"><?= $h($bookingRef) ?></span></td>
              <td>
                <div class="customer-name"><?= $h($customerName) ?></div>
                <?php if ($customerEmail !== ''): ?>
                  <div class="customer-email"><?= $h($customerEmail) ?></div>
                <?php endif; ?>
              </td>
              <td><span class="supplier-text"><?= $h($supplierNames !== '' ? $supplierNames : '-') ?></span></td>
              <td><span class="amount"><?= $money($booking['total_amount'] ?? 0) ?></span></td>
              <td><span class="paid-text"><?= $money($booking['paid_amount'] ?? 0) ?></span></td>
              <td><?= $statusBadge($status) ?></td>
              <td>
                <span class="date-text event-date"><?= $h($eventDate) ?></span>
                <?php if ($eventTime !== ''): ?>
                  <span class="event-time"><?= $h($eventTime) ?></span>
                <?php endif; ?>
              </td>
              <td>
                <a class="action-link" href="<?= URLROOT ?>/admin/bookingDetail/<?= $bookingId ?>">
                  <i data-lucide="eye" class="h-3.5 w-3.5" aria-hidden="true"></i>
                  View
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <?php
    $filterParam = $activeFilter ?? 'all';
    $searchParam = $search ?? '';
    $paginationParams = [];
    if ($filterParam !== 'all') $paginationParams['status'] = $filterParam;
    if ($searchParam !== '') $paginationParams['search'] = $searchParam;
    if ($sort !== 'event_asc') $paginationParams['sort'] = $sort;
    if ($dateFrom !== '') $paginationParams['date_from'] = $dateFrom;
    if ($dateTo !== '') $paginationParams['date_to'] = $dateTo;
    $baseParams = http_build_query($paginationParams);
    $showSinglePage = true;
    require APPROOT . '/views/partials/_pagination.php';
    ?>
  </div>
</div>
<?php
};
?>
<!DOCTYPE html>
<html lang="en">
<head><?php require_once APPROOT . '/views/dashboardLayout/head.php'; ?></head>
<body class="grid h-screen grid-cols-[280px_1fr] gap-0 bg-app-page">
  <?php require APPROOT . '/views/dashboardLayout/sidebar.php'; ?>
</body>
</html>
