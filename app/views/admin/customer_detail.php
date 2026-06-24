<?php
$dashboardTitle = 'Customers';
$dashboardCrumb = 'Profile';
$customer = $customer ?? [];
$bookings = $bookings ?? [];
$activeBookings = (int)($activeBookings ?? 0);
$history = $history ?? [];
$message = $message ?? '';
$dashboardContentClass = 'customer-detail-shell';

$h = static fn($value) => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
$money = static fn($value) => number_format((float)$value, 0) . ' MMK';

$cid = (int)($customer['user_id'] ?? 0);
$isDeleted = !empty($customer['deleted_at']);
$rawStatus = strtolower((string)($customer['status'] ?? ''));

$statusMeta = static function ($value, $deleted) {
    if ($deleted) return ['Deleted', '#8c3941', 'trash-2'];
    return match (strtolower((string)$value)) {
        'active' => ['Active', '#4f7c69', 'circle-check'],
        'suspended' => ['Suspended', '#b7792f', 'pause-circle'],
        'banned' => ['Banned', '#b94b4b', 'ban'],
        'locked' => ['Locked', '#7b5c69', 'lock'],
        default => [ucfirst((string)$value ?: 'Unknown'), '#7b5c69', 'circle'],
    };
};
[$statusLabel, $statusColor, $statusIcon] = $statusMeta($rawStatus, $isDeleted);

$bookingStatusColor = static function ($s) {
    return match (strtolower((string)$s)) {
        'completed', 'finalized', 'confirmed', 'paid', 'payment_verified' => '#4f7c69',
        'cancelled' => '#b94b4b',
        'draft' => '#a58b96',
        default => '#b7792f',
    };
};

$dashboardContent = function () use (
    $customer, $bookings, $activeBookings, $history, $message,
    $h, $money, $cid, $isDeleted, $rawStatus, $statusLabel, $statusColor, $statusIcon, $bookingStatusColor
) {
?>
<style>
  .customer-detail-shell{min-height:100%;padding:30px;background:#fbfbf9}
  .cv{--ink:#34232b;--body:#7b5c69;--muted:#a58b96;--line:#ead8c7;--wash:#faf5ef;--wine:#6d4c5b;max-width:1180px;margin:0 auto;color:var(--ink)}
  .cv-back{display:inline-flex;align-items:center;gap:6px;margin-bottom:16px;color:#8e727e;font-size:11px;font-weight:700;text-decoration:none}
  .cv-back:hover{color:var(--wine)}
  .cv-flash{display:flex;align-items:center;gap:9px;margin-bottom:18px;border:1px solid #cfe3d6;border-radius:11px;background:#eef7f1;padding:12px 15px;color:#3c6b51;font-size:12px;font-weight:650}
  .cv-grid{display:grid;grid-template-columns:1.6fr 1fr;gap:18px;align-items:start}
  .cv-card{border:1px solid var(--line);border-radius:15px;background:#fff;box-shadow:0 14px 36px rgba(52,35,43,.05)}
  .cv-card-head{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:15px 18px;border-bottom:1px solid var(--line)}
  .cv-card-title{display:flex;align-items:center;gap:8px;margin:0;font-size:11px;font-weight:800;color:var(--ink)}
  .cv-card-title svg{width:15px;height:15px;color:#b7792f}
  .cv-card-body{padding:18px}

  .cv-profile{display:flex;align-items:center;gap:16px;margin-bottom:18px}
  .cv-avatar{display:inline-flex;width:60px;height:60px;flex:0 0 60px;align-items:center;justify-content:center;border-radius:16px;background:#f2e8e1;color:var(--wine);font-size:22px;font-weight:800}
  .cv-name{margin:0;font-size:20px;font-weight:800;color:var(--ink)}
  .cv-email{margin-top:3px;color:#8e727e;font-size:12px;font-weight:600}
  .cv-badge{display:inline-flex;min-height:25px;align-items:center;gap:6px;border-radius:999px;padding:0 10px;color:var(--bc);background:color-mix(in srgb,var(--bc) 11%,white);font-size:8px;font-weight:800;text-transform:uppercase}
  .cv-badge svg{width:11px;height:11px}
  .cv-facts{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
  .cv-fact{border:1px solid #f0e5dc;border-radius:11px;padding:11px 13px;background:#fdfaf6}
  .cv-fact-label{color:#a58b96;font-size:8px;font-weight:800;letter-spacing:.09em;text-transform:uppercase}
  .cv-fact-value{margin-top:5px;color:var(--ink);font-size:12px;font-weight:700;word-break:break-word}

  .cv-spend{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;margin-bottom:0}
  .cv-spend-card{border:1px solid #f0e5dc;border-radius:12px;padding:14px;background:#fdfaf6}
  .cv-spend-label{color:#a58b96;font-size:8px;font-weight:800;letter-spacing:.09em;text-transform:uppercase}
  .cv-spend-value{margin-top:6px;color:var(--ink);font-size:18px;font-weight:800;font-variant-numeric:tabular-nums}

  .cv-actions{display:flex;flex-direction:column;gap:9px}
  .cv-btn{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:40px;border-radius:10px;padding:0 14px;font-size:11px;font-weight:800;cursor:pointer;border:1px solid transparent;text-decoration:none;width:100%}
  .cv-btn svg{width:14px;height:14px}
  .cv-btn-edit{border-color:#ddc8b9;background:#fff;color:var(--wine)}.cv-btn-edit:hover{background:var(--wash)}
  .cv-btn-warn{border-color:#e6c9a0;background:#fcf3e6;color:#9a6a22}.cv-btn-warn:hover{background:#f8e9d2}
  .cv-btn-danger{border-color:#e4b4b4;background:#fbeaea;color:#a23a3a}.cv-btn-danger:hover{background:#f6d8d8}
  .cv-btn-ok{border-color:#bcdcc8;background:#edf7f1;color:#3c6b51}.cv-btn-ok:hover{background:#ddeee3}
  .cv-warn-note{display:flex;align-items:flex-start;gap:8px;margin-bottom:13px;border:1px solid #e6c9a0;border-radius:10px;background:#fcf3e6;padding:10px 12px;color:#9a6a22;font-size:10px;font-weight:650;line-height:1.5}
  .cv-warn-note svg{width:14px;height:14px;flex:0 0 14px;margin-top:1px}

  .cv-table{width:100%;border-collapse:collapse}
  .cv-table thead th{padding:10px 14px;border-bottom:1px solid var(--line);background:var(--wash);color:#a58b96;font-size:8px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;text-align:left}
  .cv-table tbody td{padding:11px 14px;border-bottom:1px solid #f3ebe3;font-size:11px;color:var(--ink)}
  .cv-table tbody tr:last-child td{border-bottom:0}
  .cv-bk-badge{display:inline-flex;align-items:center;border-radius:999px;padding:2px 8px;color:var(--bc);background:color-mix(in srgb,var(--bc) 12%,white);font-size:8px;font-weight:800;text-transform:uppercase}
  .cv-link{color:var(--wine);font-weight:700;text-decoration:none}.cv-link:hover{text-decoration:underline}

  .cv-timeline{position:relative;padding-left:18px}
  .cv-timeline::before{content:"";position:absolute;left:4px;top:4px;bottom:4px;width:2px;background:#eaddd0}
  .cv-event{position:relative;padding:0 0 15px 8px}
  .cv-event:last-child{padding-bottom:0}
  .cv-event::before{content:"";position:absolute;left:-18px;top:3px;width:9px;height:9px;border-radius:50%;background:var(--ec,#b7792f);box-shadow:0 0 0 3px color-mix(in srgb,var(--ec,#b7792f) 18%,white)}
  .cv-event-action{font-size:11px;font-weight:800;color:var(--ink);text-transform:capitalize}
  .cv-event-meta{margin-top:3px;color:#a58b96;font-size:9px;font-weight:650}
  .cv-event-reason{margin-top:5px;color:var(--body);font-size:10px;line-height:1.5}
  .cv-empty-sm{color:#a58b96;font-size:11px;text-align:center;padding:22px 0}

  .cv-modal{position:fixed;inset:0;z-index:80;display:none;align-items:center;justify-content:center;padding:20px;background:rgba(52,35,43,.45);backdrop-filter:blur(2px)}
  .cv-modal.open{display:flex}
  .cv-modal-box{width:100%;max-width:440px;border-radius:16px;background:#fff;box-shadow:0 30px 70px rgba(52,35,43,.25);overflow:hidden}
  .cv-modal-head{display:flex;align-items:center;justify-content:space-between;padding:15px 18px;border-bottom:1px solid var(--line)}
  .cv-modal-title{margin:0;font-size:13px;font-weight:800;color:var(--ink)}
  .cv-modal-close{border:0;background:transparent;color:#a58b96;cursor:pointer;display:inline-flex}
  .cv-modal-body{padding:18px}
  .cv-field{margin-bottom:13px}
  .cv-field label{display:block;margin-bottom:5px;color:#7b5c69;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.05em}
  .cv-field input,.cv-field textarea{width:100%;box-sizing:border-box;border:1px solid #e4d2c3;border-radius:9px;padding:9px 11px;font:500 12px Inter,sans-serif;color:var(--ink)}
  .cv-field textarea{min-height:78px;resize:vertical}
  .cv-modal-foot{display:flex;justify-content:flex-end;gap:9px;margin-top:4px}
  .cv-modal-foot .cv-btn{width:auto}

  @media(max-width:880px){.cv-grid{grid-template-columns:1fr}}
</style>

<div class="cv">
  <a class="cv-back" href="<?= URLROOT ?>/admin/customers"><i data-lucide="arrow-left" class="h-3.5 w-3.5"></i> Back to customers</a>

  <?php if ($message !== ''): ?>
    <div class="cv-flash"><i data-lucide="check-circle" class="h-4 w-4"></i><?= $h($message) ?></div>
  <?php endif; ?>

  <div class="cv-grid">
    <div style="display:flex;flex-direction:column;gap:18px">
      <!-- Profile -->
      <section class="cv-card">
        <div class="cv-card-body">
          <div class="cv-profile">
            <span class="cv-avatar"><?= $h(mb_strtoupper(mb_substr(trim((string)($customer['name'] ?? 'C')), 0, 1))) ?></span>
            <div style="min-width:0">
              <h1 class="cv-name"><?= $h($customer['name'] ?? 'Unnamed customer') ?></h1>
              <div class="cv-email"><?= $h($customer['email'] ?? '') ?></div>
              <div style="margin-top:8px"><span class="cv-badge" style="--bc:<?= $h($statusColor) ?>"><i data-lucide="<?= $h($statusIcon) ?>"></i><?= $h($statusLabel) ?></span></div>
            </div>
          </div>
          <div class="cv-facts">
            <div class="cv-fact"><div class="cv-fact-label">Phone</div><div class="cv-fact-value"><?= $h($customer['phone'] ?? '—') ?: '—' ?></div></div>
            <div class="cv-fact"><div class="cv-fact-label">Address</div><div class="cv-fact-value"><?= $h($customer['address'] ?? '—') ?: '—' ?></div></div>
            <div class="cv-fact"><div class="cv-fact-label">Joined</div><div class="cv-fact-value"><?= !empty($customer['created_at']) ? date('M j, Y', strtotime($customer['created_at'])) : '—' ?></div></div>
            <div class="cv-fact"><div class="cv-fact-label">Last login</div><div class="cv-fact-value"><?= !empty($customer['last_login']) ? date('M j, Y H:i', strtotime($customer['last_login'])) : 'Never' ?></div></div>
            <div class="cv-fact"><div class="cv-fact-label">Email verified</div><div class="cv-fact-value"><?= !empty($customer['email_verified_at']) ? 'Yes' : 'No' ?></div></div>
            <div class="cv-fact"><div class="cv-fact-label">Customer ID</div><div class="cv-fact-value">#<?= $cid ?></div></div>
          </div>
        </div>
      </section>

      <!-- Spend -->
      <section class="cv-card">
        <div class="cv-card-head"><h2 class="cv-card-title"><i data-lucide="wallet"></i> Booking & spend</h2></div>
        <div class="cv-card-body">
          <div class="cv-spend">
            <div class="cv-spend-card"><div class="cv-spend-label">Total bookings</div><div class="cv-spend-value"><?= number_format((int)($customer['bookings_count'] ?? 0)) ?></div></div>
            <div class="cv-spend-card"><div class="cv-spend-label">Total spent</div><div class="cv-spend-value"><?= $money($customer['total_spent'] ?? 0) ?></div></div>
          </div>
        </div>
      </section>

      <!-- Bookings -->
      <section class="cv-card">
        <div class="cv-card-head"><h2 class="cv-card-title"><i data-lucide="calendar-days"></i> Bookings</h2></div>
        <?php if (empty($bookings)): ?>
          <div class="cv-empty-sm">No bookings yet.</div>
        <?php else: ?>
          <div style="overflow-x:auto">
            <table class="cv-table">
              <thead><tr><th>Reference</th><th>Event date</th><th>Status</th><th>Amount</th><th></th></tr></thead>
              <tbody>
                <?php foreach ($bookings as $b): ?>
                  <tr>
                    <td><strong><?= $h($b['booking_ref'] ?? ('#' . (int)$b['id'])) ?></strong></td>
                    <td><?= !empty($b['event_date']) ? date('M j, Y', strtotime($b['event_date'])) : '—' ?></td>
                    <td><span class="cv-bk-badge" style="--bc:<?= $h($bookingStatusColor($b['status'] ?? '')) ?>"><?= $h(str_replace('_', ' ', (string)($b['status'] ?? ''))) ?></span></td>
                    <td><?= $money($b['total_amount'] ?? 0) ?></td>
                    <td style="text-align:right"><a class="cv-link" href="<?= URLROOT ?>/admin/bookingDetail/<?= (int)$b['id'] ?>">View</a></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </section>
    </div>

    <div style="display:flex;flex-direction:column;gap:18px">
      <!-- Actions -->
      <section class="cv-card">
        <div class="cv-card-head"><h2 class="cv-card-title"><i data-lucide="shield"></i> Account actions</h2></div>
        <div class="cv-card-body">
          <?php if ($activeBookings > 0): ?>
            <div class="cv-warn-note"><i data-lucide="alert-triangle"></i><span>This customer has <strong><?= $activeBookings ?></strong> active booking<?= $activeBookings === 1 ? '' : 's' ?>. Moderating the account does not cancel them.</span></div>
          <?php endif; ?>

          <div class="cv-actions">
            <?php if ($isDeleted): ?>
              <p class="cv-empty-sm" style="padding:8px 0">This account is soft-deleted. Login is blocked.</p>
              <button type="button" class="cv-btn cv-btn-danger" data-open-modal="permanent-delete"><i data-lucide="trash-2"></i> Permanently delete</button>
            <?php else: ?>
              <button type="button" class="cv-btn cv-btn-edit" data-open-modal="edit"><i data-lucide="pencil"></i> Edit contact</button>

              <?php if ($rawStatus === 'active' || $rawStatus === 'locked'): ?>
                <button type="button" class="cv-btn cv-btn-warn" data-open-modal="suspend"><i data-lucide="pause-circle"></i> Suspend</button>
                <button type="button" class="cv-btn cv-btn-danger" data-open-modal="ban"><i data-lucide="ban"></i> Ban</button>
              <?php else: ?>
                <form method="POST" action="<?= URLROOT ?>/admin/customerUnban/<?= $cid ?>"><?= csrf_field() ?>
                  <button type="submit" class="cv-btn cv-btn-ok"><i data-lucide="circle-check"></i> Restore to active</button>
                </form>
              <?php endif; ?>

              <button type="button" class="cv-btn cv-btn-danger" data-open-modal="delete"><i data-lucide="trash-2"></i> Delete account</button>
            <?php endif; ?>
          </div>
        </div>
      </section>

      <!-- Moderation history -->
      <section class="cv-card">
        <div class="cv-card-head"><h2 class="cv-card-title"><i data-lucide="history"></i> Moderation history</h2></div>
        <div class="cv-card-body">
          <?php if (empty($history)): ?>
            <div class="cv-empty-sm">No moderation actions recorded.</div>
          <?php else: ?>
            <div class="cv-timeline">
              <?php foreach ($history as $event): ?>
                <?php
                  $action = (string)($event['action'] ?? '');
                  $ec = match ($action) {
                      'unban', 'edit_contact' => '#4f7c69',
                      'suspend' => '#b7792f',
                      'ban', 'soft_delete' => '#b94b4b',
                      default => '#7b5c69',
                  };
                ?>
                <div class="cv-event" style="--ec:<?= $h($ec) ?>">
                  <div class="cv-event-action"><?= $h(str_replace('_', ' ', $action)) ?></div>
                  <div class="cv-event-meta">
                    <?= $h($event['admin_name'] ?? 'System') ?> ·
                    <?= !empty($event['created_at']) ? date('M j, Y H:i', strtotime($event['created_at'])) : '' ?>
                    <?php if (!empty($event['old_status']) && $event['old_status'] !== $event['new_status']): ?>
                      · <?= $h($event['old_status']) ?> → <?= $h($event['new_status']) ?>
                    <?php endif; ?>
                  </div>
                  <?php if (!empty($event['reason'])): ?>
                    <div class="cv-event-reason"><?= $h($event['reason']) ?></div>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </section>
    </div>
  </div>
</div>

<?php if (!$isDeleted): ?>
<!-- Edit modal -->
<div class="cv-modal" id="modal-edit" role="dialog" aria-modal="true" aria-labelledby="modal-edit-title">
  <div class="cv-modal-box">
    <div class="cv-modal-head"><h3 class="cv-modal-title" id="modal-edit-title">Edit contact details</h3><button type="button" class="cv-modal-close" data-close-modal><i data-lucide="x"></i></button></div>
    <form method="POST" action="<?= URLROOT ?>/admin/customerUpdate/<?= $cid ?>"><?= csrf_field() ?>
      <div class="cv-modal-body">
        <div class="cv-field"><label for="edit-name">Name</label><input id="edit-name" type="text" name="name" value="<?= $h($customer['name'] ?? '') ?>" required></div>
        <div class="cv-field"><label for="edit-phone">Phone</label><input id="edit-phone" type="text" name="phone" value="<?= $h($customer['phone'] ?? '') ?>"></div>
        <div class="cv-field"><label for="edit-address">Address</label><input id="edit-address" type="text" name="address" value="<?= $h($customer['address'] ?? '') ?>"></div>
        <div class="cv-modal-foot">
          <button type="button" class="cv-btn cv-btn-edit" data-close-modal>Cancel</button>
          <button type="submit" class="cv-btn cv-btn-ok"><i data-lucide="save"></i> Save</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Suspend modal -->
<div class="cv-modal" id="modal-suspend" role="dialog" aria-modal="true" aria-labelledby="modal-suspend-title">
  <div class="cv-modal-box">
    <div class="cv-modal-head"><h3 class="cv-modal-title" id="modal-suspend-title">Suspend customer</h3><button type="button" class="cv-modal-close" data-close-modal><i data-lucide="x"></i></button></div>
    <form method="POST" action="<?= URLROOT ?>/admin/customerSuspend/<?= $cid ?>"><?= csrf_field() ?>
      <div class="cv-modal-body">
        <div class="cv-field"><label for="suspend-reason">Reason (required)</label><textarea id="suspend-reason" name="reason" required placeholder="Why is this account being suspended?"></textarea></div>
        <div class="cv-modal-foot">
          <button type="button" class="cv-btn cv-btn-edit" data-close-modal>Cancel</button>
          <button type="submit" class="cv-btn cv-btn-warn"><i data-lucide="pause-circle"></i> Suspend</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Ban modal -->
<div class="cv-modal" id="modal-ban" role="dialog" aria-modal="true" aria-labelledby="modal-ban-title">
  <div class="cv-modal-box">
    <div class="cv-modal-head"><h3 class="cv-modal-title" id="modal-ban-title">Ban customer</h3><button type="button" class="cv-modal-close" data-close-modal><i data-lucide="x"></i></button></div>
    <form method="POST" action="<?= URLROOT ?>/admin/customerBan/<?= $cid ?>"><?= csrf_field() ?>
      <div class="cv-modal-body">
        <?php if ($activeBookings > 0): ?>
          <div class="cv-warn-note"><i data-lucide="alert-triangle"></i><span>This customer has <strong><?= $activeBookings ?></strong> active booking<?= $activeBookings === 1 ? '' : 's' ?>. Banning will block their login but will not cancel bookings.</span></div>
        <?php endif; ?>
        <div class="cv-field"><label for="ban-reason">Reason (required)</label><textarea id="ban-reason" name="reason" required placeholder="Why is this account being banned?"></textarea></div>
        <div class="cv-modal-foot">
          <button type="button" class="cv-btn cv-btn-edit" data-close-modal>Cancel</button>
          <button type="submit" class="cv-btn cv-btn-danger"><i data-lucide="ban"></i> Ban</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Delete modal -->
<div class="cv-modal" id="modal-delete" role="dialog" aria-modal="true" aria-labelledby="modal-delete-title">
  <div class="cv-modal-box">
    <div class="cv-modal-head"><h3 class="cv-modal-title" id="modal-delete-title">Delete account</h3><button type="button" class="cv-modal-close" data-close-modal><i data-lucide="x"></i></button></div>
    <form method="POST" action="<?= URLROOT ?>/admin/customerDelete/<?= $cid ?>"><?= csrf_field() ?>
      <div class="cv-modal-body">
        <div class="cv-warn-note"><i data-lucide="alert-triangle"></i><span>Soft-delete hides the account from default lists and blocks login. Records (bookings, payments) are kept.<?= $activeBookings > 0 ? ' This customer has ' . $activeBookings . ' active booking' . ($activeBookings === 1 ? '' : 's') . '.' : '' ?></span></div>
        <div class="cv-field"><label for="delete-reason">Reason (required)</label><textarea id="delete-reason" name="reason" required placeholder="Why is this account being deleted?"></textarea></div>
        <div class="cv-modal-foot">
          <button type="button" class="cv-btn cv-btn-edit" data-close-modal>Cancel</button>
          <button type="submit" class="cv-btn cv-btn-danger"><i data-lucide="trash-2"></i> Delete account</button>
        </div>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<!-- Permanent delete modal -->
<div class="cv-modal" id="modal-permanent-delete" role="dialog" aria-modal="true">
  <div class="cv-modal-box">
    <div class="cv-modal-head"><h3 class="cv-modal-title">Permanently delete account</h3><button type="button" class="cv-modal-close" data-close-modal><i data-lucide="x"></i></button></div>
    <div class="cv-modal-body">
      <div class="cv-warn-note"><i data-lucide="alert-triangle"></i><span><strong>This cannot be undone.</strong> The account will be anonymized — name, email, password, and personal data will be erased. Booking and payment records are kept but the user will no longer exist. Their email can be used to register a new account.</span></div>
      <div class="cv-field"><label for="perm-delete-confirm">Type <strong style="color:#b94b4b">PERMANENTLY DELETE</strong> to confirm</label><input id="perm-delete-confirm" type="text" placeholder="Type PERMANENTLY DELETE" autocomplete="off"></div>
      <div class="cv-modal-foot">
        <button type="button" class="cv-btn cv-btn-edit" data-close-modal>Cancel</button>
        <form method="POST" action="<?= URLROOT ?>/admin/customerPermanentDelete/<?= $cid ?>" id="permDeleteForm" style="display:inline"><?= csrf_field() ?>
          <button type="submit" class="cv-btn cv-btn-danger" id="permDeleteBtn" disabled><i data-lucide="trash-2"></i> Permanently delete</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  document.querySelectorAll('[data-open-modal]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var m = document.getElementById('modal-' + btn.dataset.openModal);
      if (m) { m.classList.add('open'); var f = m.querySelector('textarea,input'); if (f) f.focus(); }
    });
  });
  function closeModals() { document.querySelectorAll('.cv-modal.open').forEach(function (m) { m.classList.remove('open'); }); }
  document.querySelectorAll('[data-close-modal]').forEach(function (btn) { btn.addEventListener('click', closeModals); });
  document.querySelectorAll('.cv-modal').forEach(function (m) {
    m.addEventListener('click', function (e) { if (e.target === m) closeModals(); });
  });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeModals(); });

  // Permanent delete confirmation
  var permInput = document.getElementById('perm-delete-confirm');
  var permBtn   = document.getElementById('permDeleteBtn');
  if (permInput && permBtn) {
    permInput.addEventListener('input', function() {
      permBtn.disabled = this.value.trim().toUpperCase() !== 'PERMANENTLY DELETE';
    });
  }
</script>
<?php
};
?>
<!DOCTYPE html>
<html lang="en">
<head><?php require_once APPROOT . '/views/dashboardLayout/head.php'; ?></head>
<body class="grid h-screen gap-0 bg-app-page" style="grid-template-columns:280px 1fr">
  <?php require APPROOT . '/views/dashboardLayout/adminsidebar.php'; ?>
</body>
</html>
