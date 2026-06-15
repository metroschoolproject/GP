<?php
$bookings = $bookings ?? [];
$stats = $stats ?? [];
$activeFilter = $activeFilter ?? 'all';
$money = fn($v) => 'RM ' . number_format((float)$v, 0);
$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$filters = ['all' => 'All', 'pending' => 'Pending', 'confirmed' => 'Confirmed', 'completed' => 'Completed', 'rejected' => 'Rejected'];

$dashboardTitle = 'Bookings';
$dashboardCrumb = 'Incoming bookings';
$dashboardContentClass = 'bg-app-content px-6 py-6 overflow-y-auto';
$dashboardContent = function () use ($bookings, $stats, $activeFilter, $filters, $money, $h) {
?>
<section class="space-y-5">
  <div class="grid gap-3 md:grid-cols-4">
    <div class="rounded-xl border border-app-panel-border bg-app-panel p-4"><p class="text-xs font-semibold text-app-muted">Pending</p><p class="mt-1 text-2xl font-bold text-app-text"><?= (int)($stats['pending_count'] ?? 0) ?></p></div>
    <div class="rounded-xl border border-app-panel-border bg-app-panel p-4"><p class="text-xs font-semibold text-app-muted">Confirmed</p><p class="mt-1 text-2xl font-bold text-app-text"><?= (int)($stats['confirmed_count'] ?? 0) ?></p></div>
    <div class="rounded-xl border border-app-panel-border bg-app-panel p-4"><p class="text-xs font-semibold text-app-muted">Completed</p><p class="mt-1 text-2xl font-bold text-app-text"><?= (int)($stats['completed_count'] ?? 0) ?></p></div>
    <div class="rounded-xl border border-app-panel-border bg-app-panel p-4"><p class="text-xs font-semibold text-app-muted">Est. revenue</p><p class="mt-1 text-2xl font-bold text-app-primary"><?= $money($stats['est_revenue'] ?? 0) ?></p></div>
  </div>

  <div class="flex flex-wrap gap-2">
    <?php foreach ($filters as $key => $label): ?>
      <a href="<?= URLROOT ?>/supplier/bookings?status=<?= $h($key) ?>" class="rounded-lg px-3 py-2 text-sm font-semibold <?= $activeFilter === $key ? 'bg-app-primary text-app-white' : 'border border-app-panel-border bg-app-panel text-app-secondary hover:text-app-text' ?>"><?= $h($label) ?></a>
    <?php endforeach; ?>
  </div>

  <div class="overflow-hidden rounded-xl border border-app-panel-border bg-app-panel">
    <?php if (empty($bookings)): ?>
      <div class="p-10 text-center">
        <p class="text-lg font-semibold text-app-text">No bookings found</p>
        <p class="mt-1 text-sm text-app-muted">Incoming customer bookings will appear here.</p>
      </div>
    <?php else: ?>
      <div class="overflow-x-auto">
        <table class="min-w-full text-left text-sm">
          <thead class="bg-app-input text-xs uppercase tracking-wide text-app-muted">
            <tr><th class="px-4 py-3">Booking</th><th class="px-4 py-3">Customer</th><th class="px-4 py-3">Services</th><th class="px-4 py-3">Amount</th><th class="px-4 py-3">Status</th><th class="px-4 py-3 text-right">Action</th></tr>
          </thead>
          <tbody class="divide-y divide-app-panel-border">
            <?php foreach ($bookings as $booking): ?>
              <tr>
                <td class="px-4 py-3 font-semibold text-app-text"><?= $h($booking['booking_ref'] ?? '') ?></td>
                <td class="px-4 py-3 text-app-secondary"><?= $h($booking['customer_name'] ?? 'Customer') ?></td>
                <td class="px-4 py-3 text-app-secondary"><?= (int)($booking['item_count'] ?? count($booking['items'] ?? [])) ?> item(s)</td>
                <td class="px-4 py-3 font-semibold text-app-text"><?= $money($booking['total_amount'] ?? 0) ?></td>
                <td class="px-4 py-3"><span class="rounded-full bg-app-soft px-2.5 py-1 text-xs font-semibold text-app-secondary"><?= $h(ucfirst($booking['supplier_status'] ?? 'pending')) ?></span></td>
                <td class="px-4 py-3 text-right"><a class="rounded-lg border border-app-panel-border px-3 py-2 text-xs font-semibold text-app-text hover:bg-app-input" href="<?= URLROOT ?>/supplier/bookingDetail/<?= (int)$booking['id'] ?>">View</a></td>
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
<body class="grid h-screen gap-0 bg-app-page" style="grid-template-columns: 280px 1fr;">
  <?php require APPROOT . '/views/dashboardLayout/suppliersidebar.php'; ?>
</body>
</html>
