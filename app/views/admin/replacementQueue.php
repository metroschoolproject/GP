<?php
$replacements = $replacements ?? [];

$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$money = fn($v) => number_format((float)$v, 0) . ' MMK';
$dateOnly = static function ($value, string $fallback = '-') {
    if (empty($value)) return $fallback;
    $ts = strtotime((string)$value);
    return $ts ? date('M j, Y', $ts) : $fallback;
};

$dashboardTitle = 'Supplier Replacements';
$dashboardCrumb = 'Replacements';
$dashboardContentClass = 'admin-booking-outlet';

$statusLabels = [
    'pending_admin'        => 'Awaiting pick',
    'declined_again'       => 'Re-pick needed',
    'rejected_by_customer' => 'Customer declined',
];

$dashboardContent = function () use ($replacements, $h, $money, $dateOnly, $statusLabels) {
?>
<style>
  .admin-booking-outlet{min-height:100%;background:#FBFBF9;padding:28px 32px;font-family:'DM Sans',system-ui,sans-serif;color:#111827;font-size:13px;overflow-y:auto}
  .repl-page{--surface:#fcf8f5;--border:#ead8c7;--primary:#6d4c5b;--muted:#b79c8b;max-width:1300px;margin:0 auto}
  .repl-page h1{font-size:22px;font-weight:700;margin:0 0 4px}
  .eyebrow{font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--muted);margin-bottom:4px}
  .repl-card{background:var(--surface);border:1px solid var(--border);border-radius:.75rem;overflow:hidden;margin-top:18px}
  table.repl{width:100%;border-collapse:collapse}
  table.repl th{text-align:left;font-size:10px;text-transform:uppercase;letter-spacing:.08em;color:var(--muted);padding:12px 16px;border-bottom:1px solid var(--border);background:#faf5ef}
  table.repl td{padding:13px 16px;border-bottom:1px solid #f0e6da;font-size:12.5px}
  table.repl tr:last-child td{border-bottom:none}
  .badge{display:inline-block;padding:3px 9px;border-radius:999px;font-size:10.5px;font-weight:700;background:#fef3c7;color:#92400e}
  .btn-pick{display:inline-flex;align-items:center;gap:5px;height:30px;padding:0 13px;border-radius:.6rem;background:var(--primary);color:#fcf8f5;font-size:11.5px;font-weight:700;text-decoration:none}
  .btn-pick:hover{opacity:.92}
  .empty{padding:48px;text-align:center;color:var(--muted)}
</style>
<div class="repl-page">
  <div class="eyebrow">Action needed</div>
  <h1>Supplier Replacements</h1>
  <p style="color:#7b5c69;margin:6px 0 0">Suppliers who declined a confirmed package booking. Pick a same-category replacement for each.</p>

  <div class="repl-card">
    <?php if (empty($replacements)): ?>
      <div class="empty">No replacements needed right now. 🎉</div>
    <?php else: ?>
      <table class="repl">
        <thead>
          <tr>
            <th>Booking</th><th>Customer</th><th>Declined service</th>
            <th>Declined supplier</th><th>Wedding date</th><th>Status</th><th></th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($replacements as $r): ?>
          <tr>
            <td><strong><?= $h($r['booking_ref'] ?? ('#' . $r['booking_id'])) ?></strong></td>
            <td><?= $h($r['customer_name'] ?? '-') ?></td>
            <td><?= $h($r['old_service_name'] ?? ($r['category_name'] ?? '-')) ?><br><span style="color:#b79c8b;font-size:11px"><?= $h($r['category_name'] ?? '') ?></span></td>
            <td><?= $h($r['old_shop_name'] ?? '-') ?><br><span style="color:#b79c8b;font-size:11px"><?= $money($r['old_price'] ?? 0) ?></span></td>
            <td><?= $h($dateOnly($r['event_date'] ?? null)) ?></td>
            <td><span class="badge"><?= $h($statusLabels[$r['status']] ?? $r['status']) ?></span></td>
            <td style="text-align:right">
              <a class="btn-pick" href="<?= URLROOT ?>/admin/replacementPicker/<?= (int)$r['id'] ?>">Pick replacement →</a>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
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
