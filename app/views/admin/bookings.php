<?php
$bookings = $bookings ?? [];
$stats = $stats ?? [];
$activeFilter = $activeFilter ?? 'all';
$search = $search ?? '';
$money = fn($v) => 'RM ' . number_format((float)$v, 0);
$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$filters = ['all' => 'All', 'draft' => 'Draft', 'pending_payment' => 'Pending payment', 'paid' => 'Paid', 'confirmed' => 'Confirmed', 'completed' => 'Completed', 'cancelled' => 'Cancelled'];

$dashboardTitle = 'Bookings';
$dashboardCrumb = 'All bookings';
$dashboardContentClass = 'bg-app-content px-6 py-6 overflow-y-auto';
$dashboardContent = function () use ($bookings, $stats, $activeFilter, $search, $filters, $money, $h) {
?>
<section class="space-y-5">
  <div class="grid gap-3 md:grid-cols-5">
    <div class="rounded-xl border border-app-panel-border bg-app-panel p-4"><p class="text-xs font-semibold text-app-muted">Total</p><p class="mt-1 text-2xl font-bold text-app-text"><?= (int)($stats['total'] ?? 0) ?></p></div>
    <div class="rounded-xl border border-app-panel-border bg-app-panel p-4"><p class="text-xs font-semibold text-app-muted">Paid</p><p class="mt-1 text-2xl font-bold text-app-text"><?= (int)($stats['paid_count'] ?? 0) ?></p></div>
    <div class="rounded-xl border border-app-panel-border bg-app-panel p-4"><p class="text-xs font-semibold text-app-muted">Confirmed</p><p class="mt-1 text-2xl font-bold text-app-text"><?= (int)($stats['confirmed_count'] ?? 0) ?></p></div>
    <div class="rounded-xl border border-app-panel-border bg-app-panel p-4"><p class="text-xs font-semibold text-app-muted">Cancelled</p><p class="mt-1 text-2xl font-bold text-app-text"><?= (int)($stats['cancelled_count'] ?? 0) ?></p></div>
    <div class="rounded-xl border border-app-panel-border bg-app-panel p-4"><p class="text-xs font-semibold text-app-muted">Revenue</p><p class="mt-1 text-2xl font-bold text-app-primary"><?= $money($stats['total_revenue'] ?? 0) ?></p></div>
  </div>

  <form class="flex flex-wrap items-center gap-2" method="GET" action="<?= URLROOT ?>/admin/bookings">
    <input class="h-10 min-w-[240px] rounded-lg border border-app-panel-border bg-app-panel px-3 text-sm text-app-text outline-none" type="search" name="search" value="<?= $h($search) ?>" placeholder="Search customer or booking id">
    <select class="h-10 rounded-lg border border-app-panel-border bg-app-panel px-3 text-sm text-app-text outline-none" name="status">
      <?php foreach ($filters as $key => $label): ?>
        <option value="<?= $h($key) ?>" <?= $activeFilter === $key ? 'selected' : '' ?>><?= $h($label) ?></option>
      <?php endforeach; ?>
    </select>
    <button class="h-10 rounded-lg bg-app-primary px-4 text-sm font-bold text-app-white" type="submit">Filter</button>
  </form>

  <div class="overflow-hidden rounded-xl border border-app-panel-border bg-app-panel">
    <?php if (empty($bookings)): ?>
      <div class="p-10 text-center"><p class="text-lg font-semibold text-app-text">No bookings found</p><p class="mt-1 text-sm text-app-muted">Try another filter or search term.</p></div>
    <?php else: ?>
      <div class="overflow-x-auto">
        <table class="min-w-full text-left text-sm">
          <thead class="bg-app-input text-xs uppercase tracking-wide text-app-muted">
            <tr><th class="px-4 py-3">Booking</th><th class="px-4 py-3">Customer</th><th class="px-4 py-3">Suppliers</th><th class="px-4 py-3">Total</th><th class="px-4 py-3">Paid</th><th class="px-4 py-3">Status</th><th class="px-4 py-3 text-right">Action</th></tr>
          </thead>
          <tbody class="divide-y divide-app-panel-border">
            <?php foreach ($bookings as $booking): ?>
              <tr>
                <td class="px-4 py-3 font-semibold text-app-text"><?= $h($booking['booking_ref'] ?? '') ?></td>
                <td class="px-4 py-3 text-app-secondary"><?= $h($booking['customer_name'] ?? 'Customer') ?></td>
                <td class="max-w-[260px] truncate px-4 py-3 text-app-secondary"><?= $h($booking['supplier_names'] ?? '-') ?></td>
                <td class="px-4 py-3 font-semibold text-app-text"><?= $money($booking['total_amount'] ?? 0) ?></td>
                <td class="px-4 py-3 text-app-secondary"><?= $money($booking['paid_amount'] ?? 0) ?></td>
                <td class="px-4 py-3"><span class="rounded-full bg-app-soft px-2.5 py-1 text-xs font-semibold text-app-secondary"><?= $h(ucwords(str_replace('_', ' ', $booking['status'] ?? ''))) ?></span></td>
                <td class="px-4 py-3 text-right"><a class="rounded-lg border border-app-panel-border px-3 py-2 text-xs font-semibold text-app-text hover:bg-app-input" href="<?= URLROOT ?>/admin/bookingDetail/<?= (int)$booking['id'] ?>">View</a></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</section>
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
