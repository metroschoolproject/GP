<?php
$replacement = $replacement ?? [];
$candidates  = $candidates ?? [];
$bookingRef  = $bookingRef ?? ('#' . ($replacement['booking_id'] ?? ''));
$maxUpchargePct = $maxUpchargePct ?? 25;

$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$money = fn($v) => number_format((float)$v, 0) . ' MMK';
$dateOnly = static function ($value, string $fallback = '-') {
    if (empty($value)) return $fallback;
    $ts = strtotime((string)$value);
    return $ts ? date('M j, Y', $ts) : $fallback;
};

$pendingCustomer = ($replacement['status'] ?? '') === 'pending_customer';

$dashboardTitle = 'Pick Replacement';
$dashboardCrumb = 'Replacements';
$dashboardContentClass = 'admin-booking-outlet';

$dashboardContent = function () use ($replacement, $candidates, $bookingRef, $maxUpchargePct, $pendingCustomer, $h, $money, $dateOnly) {
    $oldPrice = (float)($replacement['old_price'] ?? 0);
?>
<style>
  .admin-booking-outlet{min-height:100%;background:#FBFBF9;padding:28px 32px;font-family:'DM Sans',system-ui,sans-serif;color:#111827;font-size:13px;overflow-y:auto}
  .repl-page{--surface:#fff;--border:#ead8c7;--primary:#6d4c5b;--muted:#b79c8b;max-width:1100px;margin:0 auto}
  .repl-page h1{font-size:22px;font-weight:700;margin:0 0 4px}
  .eyebrow{font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--muted)}
  .back{color:#6d4c5b;font-size:12px;font-weight:700;text-decoration:none}
  .panel{background:var(--surface);border:1px solid var(--border);border-radius:.75rem;padding:18px 20px;margin-top:16px}
  .meta{display:grid;grid-template-columns:repeat(4,1fr);gap:14px}
  .meta .k{font-size:10px;text-transform:uppercase;letter-spacing:.08em;color:var(--muted);margin-bottom:3px}
  .meta .v{font-size:14px;font-weight:700}
  table.cand{width:100%;border-collapse:collapse;margin-top:6px}
  table.cand th{text-align:left;font-size:10px;text-transform:uppercase;letter-spacing:.08em;color:var(--muted);padding:11px 14px;border-bottom:1px solid var(--border);background:#faf5ef}
  table.cand td{padding:12px 14px;border-bottom:1px solid #f0e6da;font-size:12.5px}
  .pill{display:inline-block;padding:2px 8px;border-radius:999px;font-size:10px;font-weight:700}
  .pill.auto{background:#d1fae5;color:#065f46}
  .pill.appr{background:#fef3c7;color:#92400e}
  .btn{height:30px;padding:0 13px;border:none;border-radius:.6rem;background:#6d4c5b;color:#fff;font-size:11.5px;font-weight:700;cursor:pointer}
  .btn:hover{opacity:.92}
  .btn:disabled{opacity:.5;cursor:not-allowed}
  .btn-verify{background:#065f46}
  .note{font-size:11.5px;color:#7b5c69;margin-top:4px}
  .empty{padding:36px;text-align:center;color:var(--muted)}
  #replMsg{margin-top:12px;font-size:12.5px;font-weight:700}
</style>
<div class="repl-page" data-replacement-id="<?= (int)($replacement['id'] ?? 0) ?>">
  <a class="back" href="<?= URLROOT ?>/admin/replacementQueue">← Back to queue</a>
  <h1 style="margin-top:8px">Pick a replacement for <?= $h($bookingRef) ?></h1>

  <div class="panel">
    <div class="meta">
      <div><div class="k">Declined service</div><div class="v"><?= $h($replacement['old_service_name'] ?? '-') ?></div></div>
      <div><div class="k">Declined supplier</div><div class="v"><?= $h($replacement['old_shop_name'] ?? '-') ?></div></div>
      <div><div class="k">Category</div><div class="v"><?= $h($replacement['category_name'] ?? '-') ?></div></div>
      <div><div class="k">Original price</div><div class="v"><?= $money($oldPrice) ?></div></div>
      <div><div class="k">Wedding date</div><div class="v"><?= $h($dateOnly($replacement['event_date'] ?? null)) ?></div></div>
    </div>
    <?php if (!empty($replacement['decline_reason'])): ?>
      <div class="note">Reason given: <?= $h($replacement['decline_reason']) ?></div>
    <?php endif; ?>
  </div>

  <?php if ($pendingCustomer): ?>
    <div class="panel" style="border-color:#92400e;background:#fffbeb">
      <strong>Waiting on customer.</strong> A pricier replacement (<?= $h($replacement['new_price'] !== null ? $money($replacement['new_price']) : '-') ?>,
      +<?= $money($replacement['price_delta'] ?? 0) ?>) was proposed. Once the customer has paid the difference, verify it to finalize.
      <div style="margin-top:10px">
        <button class="btn btn-verify" id="verifyBtn">Verify payment &amp; finalize swap</button>
      </div>
    </div>
  <?php else: ?>
    <div class="panel">
      <div class="eyebrow" style="margin-bottom:10px">Available replacements &middot; same category, free on the date &middot; over-budget allowed (customer approves the extra)</div>
      <?php if (empty($candidates)): ?>
        <div class="empty">No eligible suppliers in this category are free on the wedding date.<br>Try cancelling this item with a refund instead.</div>
      <?php else: ?>
        <table class="cand">
          <thead>
            <tr><th>Supplier</th><th>Service</th><th>Price</th><th>vs original</th><th>Handling</th><th></th></tr>
          </thead>
          <tbody>
          <?php foreach ($candidates as $c):
              $delta = (float)($c['price_delta'] ?? 0);
              $needs = !empty($c['needs_customer_approval']);
              $overCap = !empty($c['over_cap']);
          ?>
            <tr>
              <td><strong><?= $h($c['shop_name'] ?? '-') ?></strong></td>
              <td><?= $h($c['service_name'] ?? '-') ?></td>
              <td><?= $money($c['price'] ?? 0) ?></td>
              <td style="color:<?= $delta > 0 ? '#991b1b' : '#065f46' ?>">
                <?= $delta > 0 ? '+' . $money($delta) : ($delta < 0 ? '-' . $money(abs($delta)) : 'same') ?>
                <?php if ($overCap): ?>
                  <br><span style="font-size:10px;font-weight:700;color:#b45309">over +<?= (int)$maxUpchargePct ?>% cap</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($needs): ?>
                  <span class="pill appr">Customer approves + pays</span>
                <?php else: ?>
                  <span class="pill auto">Auto (platform absorbs)</span>
                <?php endif; ?>
              </td>
              <td style="text-align:right">
                <button class="btn assign-btn" data-service="<?= (int)$c['service_id'] ?>"
                        data-needs="<?= $needs ? 1 : 0 ?>">
                  <?= $needs ? 'Propose' : 'Assign' ?>
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <div id="replMsg"></div>
</div>

<script>
(function () {
  const root = document.querySelector('.repl-page');
  const replId = root ? root.getAttribute('data-replacement-id') : 0;
  const msg = document.getElementById('replMsg');
  const base = '<?= URLROOT ?>';

  function post(url, data, btn) {
    if (btn) btn.disabled = true;
    const body = new URLSearchParams(data);
    return fetch(url, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body })
      .then(r => r.json())
      .then(j => {
        if (j.success) {
          msg.style.color = '#065f46';
          msg.textContent = j.message || 'Done.';
          setTimeout(() => { window.location.href = base + '/admin/replacementQueue'; }, 1100);
        } else {
          msg.style.color = '#991b1b';
          msg.textContent = j.error || 'Something went wrong.';
          if (btn) btn.disabled = false;
        }
      })
      .catch(() => {
        msg.style.color = '#991b1b';
        msg.textContent = 'Network error.';
        if (btn) btn.disabled = false;
      });
  }

  document.querySelectorAll('.assign-btn').forEach(b => {
    b.addEventListener('click', () => {
      const needs = b.getAttribute('data-needs') === '1';
      if (needs && !confirm('This replacement costs more. The customer will be asked to approve and pay the difference. Continue?')) return;
      post(base + '/admin/assignReplacement', { replacement_id: replId, service_id: b.getAttribute('data-service') }, b);
    });
  });

  const verifyBtn = document.getElementById('verifyBtn');
  if (verifyBtn) {
    verifyBtn.addEventListener('click', () => {
      if (!confirm('Confirm the customer has paid the difference?')) return;
      post(base + '/admin/verifyReplacementPayment', { replacement_id: replId }, verifyBtn);
    });
  }
})();
</script>
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
