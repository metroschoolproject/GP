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
$replacementProofSubmitted = $pendingCustomer
    && !empty($replacement['customer_approved_at'])
    && !empty($replacement['delta_payment_slip']);

$dashboardTitle = 'Pick Replacement';
$dashboardCrumb = 'Replacements';
$dashboardBreadcrumbs = [
    ['label' => 'Bookings', 'url' => URLROOT . '/admin/bookings'],
    ['label' => 'Replacements', 'url' => URLROOT . '/admin/replacementQueue'],
    ['label' => 'Pick replacement', 'url' => null],
];
$dashboardContentClass = 'admin-booking-outlet';

$dashboardContent = function () use (
    $replacement,
    $candidates,
    $bookingRef,
    $maxUpchargePct,
    $pendingCustomer,
    $replacementProofSubmitted,
    $h,
    $money,
    $dateOnly
) {
    $oldPrice = (float)($replacement['old_price'] ?? 0);
?>
<style>
  .admin-booking-outlet{min-height:100%;background:#F4F1EE;padding:28px 32px;font-family:'DM Sans',system-ui,sans-serif;color:#6d4c5b;font-size:13px;overflow-y:auto}
  .repl-page{--surface:#fff;--soft:#FFFFFF;--border:#ead8c7;--border-light:#eddecc;--primary:#6d4c5b;--primary-hover:#7b5c69;--muted:#b79c8b;--body:#7b5c69;--text:#111827;max-width:1100px;margin:0 auto}
  .repl-page h1{font-size:22px;font-weight:700;margin:0 0 4px}
  .eyebrow{font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--muted)}
  .back{color:#6d4c5b;font-size:12px;font-weight:700;text-decoration:none}
  .panel{background:var(--surface);border:1px solid var(--border);border-radius:.75rem;padding:18px 20px;margin-top:16px}
  .meta{display:grid;grid-template-columns:repeat(3,1fr);gap:14px}
  .meta .k{font-size:10px;text-transform:uppercase;letter-spacing:.08em;color:var(--muted);margin-bottom:3px}
  .meta .v{font-size:14px;font-weight:700}
  table.cand{width:100%;border-collapse:collapse;margin-top:6px}
  table.cand th{text-align:left;font-size:10px;text-transform:uppercase;letter-spacing:.08em;color:var(--muted);padding:11px 14px;border-bottom:1px solid var(--border);background:#FFFFFF}
  table.cand td{padding:12px 14px;border-bottom:1px solid #f0e6da;font-size:12.5px}
  .pill{display:inline-block;padding:2px 8px;border-radius:999px;font-size:10px;font-weight:700}
  .pill.auto{background:#ECFDF5;color:#065F46}
  .pill.appr{background:#FFFBEB;color:#92400E}
  .btn{display:inline-flex;align-items:center;justify-content:center;gap:7px;height:36px;padding:0 15px;border:1px solid var(--primary);border-radius:.65rem;background:var(--primary);color:#fff;font-family:inherit;font-size:11.5px;font-weight:800;cursor:pointer;box-shadow:0 2px 6px rgba(109,76,91,.15);transition:background .15s,box-shadow .15s,transform .15s}
  .btn:hover{background:var(--primary-hover);box-shadow:0 5px 14px rgba(109,76,91,.2);transform:translateY(-1px)}
  .btn:focus-visible{outline:3px solid rgba(109,76,91,.2);outline-offset:2px}
  .btn:disabled{opacity:.5;cursor:not-allowed}
  .btn-verify{border-color:#065F46;background:#065F46;box-shadow:0 2px 6px rgba(6,95,70,.16)}
  .btn-verify:hover{background:#047857}
  .note{font-size:11.5px;color:#7b5c69;margin-top:4px}
  .swap-summary{display:grid;grid-template-columns:1fr auto 1fr;align-items:stretch;gap:14px;margin-top:14px}
  .swap-service{padding:16px;border:1px solid var(--border);border-radius:.7rem;background:#fff}
  .swap-service.is-new{border-color:#a7d7c5;background:#f0fdf7}
  .swap-label{font-size:9px;font-weight:900;letter-spacing:.1em;text-transform:uppercase;color:var(--muted)}
  .swap-name{margin-top:6px;color:var(--text);font-size:15px;font-weight:800}
  .swap-meta{margin-top:4px;color:var(--body);font-size:11px;line-height:1.5}
  .swap-arrow{display:grid;place-items:center;color:var(--primary)}
  .payment-state{display:flex;align-items:center;justify-content:space-between;gap:14px;margin-top:14px;padding-top:14px;border-top:1px solid #f0ddb0}
  .payment-state-copy{color:#7b5c69;font-size:12px;line-height:1.5}
  .empty{padding:36px;text-align:center;color:var(--muted)}
  #replMsg{margin-top:12px;font-size:12.5px;font-weight:700}
  .confirm-modal{position:fixed;inset:0;z-index:1000;display:none;align-items:center;justify-content:center;padding:20px;font-family:'DM Sans',system-ui,sans-serif}
  .confirm-modal.is-open{display:flex}
  .confirm-modal-backdrop{position:absolute;inset:0;background:rgba(17,24,39,.48);backdrop-filter:blur(3px)}
  .confirm-dialog{position:relative;width:min(100%,440px);overflow:hidden;border:1px solid var(--border);border-radius:1rem;background:var(--surface);box-shadow:0 24px 70px rgba(17,24,39,.22);animation:confirm-in .18s ease-out}
  .confirm-body{padding:26px 26px 20px}
  .confirm-icon{width:46px;height:46px;display:grid;place-items:center;margin-bottom:16px;border-radius:.75rem;background:#FFFBEB;color:#92400E}
  .confirm-title{margin:0;color:var(--text);font-size:18px;font-weight:800}
  .confirm-copy{margin:8px 0 0;color:var(--body);font-size:13px;line-height:1.55}
  .confirm-note{display:flex;align-items:flex-start;gap:9px;margin-top:18px;padding:12px;border:1px solid var(--border-light);border-radius:.65rem;background:var(--soft);color:var(--body);font-size:11px;line-height:1.45}
  .confirm-note svg{flex:0 0 auto;margin-top:1px;color:var(--primary)}
  .confirm-actions{display:flex;justify-content:flex-end;gap:9px;padding:16px 26px 22px}
  .btn-secondary{height:34px;padding:0 14px;border:1px solid var(--border);border-radius:.65rem;background:var(--surface);color:var(--primary);font-family:inherit;font-size:12px;font-weight:700;cursor:pointer}
  .btn-secondary:hover{background:var(--soft)}
  .btn-confirm{height:34px;padding:0 16px;border:0;border-radius:.65rem;background:var(--primary);color:#fff;font-family:inherit;font-size:12px;font-weight:700;cursor:pointer}
  .btn-confirm:hover{background:var(--primary-hover)}
  @keyframes confirm-in{from{opacity:0;transform:translateY(8px) scale(.98)}to{opacity:1;transform:translateY(0) scale(1)}}
  @media(max-width:600px){
    .admin-booking-outlet{padding:20px 16px}
    .meta{grid-template-columns:1fr 1fr}
    .swap-summary{grid-template-columns:1fr}
    .swap-arrow{transform:rotate(90deg)}
    .confirm-actions{flex-direction:column-reverse}
    .confirm-actions button{width:100%}
  }
</style>
<div class="repl-page" data-replacement-id="<?= (int)($replacement['id'] ?? 0) ?>">
  <a class="back" href="<?= URLROOT ?>/admin/replacementQueue">← Back to queue</a>
  <h1 style="margin-top:8px">Pick a replacement for <?= $h($bookingRef) ?></h1>

  <div class="panel">
    <div class="meta">
      <div><div class="k">Replacing in package</div><div class="v"><?= $h($replacement['package_name'] ?? 'Custom booking') ?></div></div>
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
    <div class="panel" style="border-color:#92400E;background:#fffbeb">
      <strong><?= $replacementProofSubmitted ? 'Customer payment submitted.' : 'Waiting on customer.' ?></strong>
      <div class="swap-summary">
        <div class="swap-service">
          <div class="swap-label">Original service</div>
          <div class="swap-name"><?= $h($replacement['old_service_name'] ?? '-') ?></div>
          <div class="swap-meta">
            <?= $h($replacement['old_shop_name'] ?? '-') ?><br>
            <?= $money($replacement['old_price'] ?? 0) ?>
          </div>
        </div>
        <div class="swap-arrow" aria-hidden="true">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>
        </div>
        <div class="swap-service is-new">
          <div class="swap-label">Proposed replacement</div>
          <div class="swap-name"><?= $h($replacement['new_service_name'] ?? 'Replacement service') ?></div>
          <div class="swap-meta">
            <?= $h($replacement['new_shop_name'] ?? 'Replacement supplier') ?><br>
            <?= $money($replacement['new_price'] ?? 0) ?>
            · +<?= $money($replacement['price_delta'] ?? 0) ?>
          </div>
        </div>
      </div>
      <div class="payment-state">
        <div class="payment-state-copy">
          <?= $replacementProofSubmitted
              ? 'The customer submitted payment proof for ' . $money($replacement['delta_paid_amount'] ?? $replacement['price_delta'] ?? 0) . '. Verify it to finalize this exact service swap.'
              : 'The customer must approve this service and pay the price difference before the swap can be finalized.' ?>
        </div>
        <?php if ($replacementProofSubmitted): ?>
          <button class="btn btn-verify" id="verifyBtn">Verify payment &amp; finalize swap</button>
        <?php else: ?>
          <span class="pill appr">Awaiting customer action</span>
        <?php endif; ?>
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
              <td style="color:<?= $delta > 0 ? '#991B1B' : '#065F46' ?>">
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
                        data-needs="<?= $needs ? 1 : 0 ?>"
                        data-service-name="<?= $h($c['service_name'] ?? 'this replacement') ?>">
                  <?= $needs ? 'Propose' : 'Assign' ?>
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>
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

  <div class="confirm-modal" id="replacementConfirmModal" aria-hidden="true">
    <div class="confirm-modal-backdrop" data-close-confirm></div>
    <section class="confirm-dialog" role="dialog" aria-modal="true"
             aria-labelledby="confirmModalTitle" aria-describedby="confirmModalCopy">
      <div class="confirm-body">
        <div class="confirm-icon">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v4"/><path d="M12 17h.01"/><path d="M10.3 3.7 2.6 17a2 2 0 0 0 1.7 3h15.4a2 2 0 0 0 1.7-3L13.7 3.7a2 2 0 0 0-3.4 0Z"/></svg>
        </div>
        <h2 class="confirm-title" id="confirmModalTitle"></h2>
        <p class="confirm-copy" id="confirmModalCopy"></p>
        <div class="confirm-note" id="confirmModalNote">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>
          <span></span>
        </div>
      </div>
      <div class="confirm-actions">
        <button class="btn-secondary" type="button" data-close-confirm>Cancel</button>
        <button class="btn-confirm" id="confirmModalAction" type="button">Continue</button>
      </div>
    </section>
  </div>
</div>

<script>
(function () {
  const root = document.querySelector('.repl-page');
  const replId = root ? root.getAttribute('data-replacement-id') : 0;
  const msg = document.getElementById('replMsg');
  const base = '<?= URLROOT ?>';
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
  const confirmModal = document.getElementById('replacementConfirmModal');
  const confirmTitle = document.getElementById('confirmModalTitle');
  const confirmCopy = document.getElementById('confirmModalCopy');
  const confirmNote = document.querySelector('#confirmModalNote span');
  const confirmAction = document.getElementById('confirmModalAction');
  let pendingConfirmation = null;
  let confirmTrigger = null;

  function openConfirmation(options, onConfirm, trigger) {
    if (!confirmModal) {
      onConfirm();
      return;
    }
    confirmTitle.textContent = options.title;
    confirmCopy.textContent = options.copy;
    confirmNote.textContent = options.note;
    confirmAction.textContent = options.action;
    pendingConfirmation = onConfirm;
    confirmTrigger = trigger || document.activeElement;
    confirmModal.classList.add('is-open');
    confirmModal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
    confirmAction.focus();
  }

  function closeConfirmation() {
    if (!confirmModal) return;
    confirmModal.classList.remove('is-open');
    confirmModal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
    pendingConfirmation = null;
    confirmTrigger?.focus();
  }

  confirmAction?.addEventListener('click', () => {
    const action = pendingConfirmation;
    closeConfirmation();
    action?.();
  });

  confirmModal?.querySelectorAll('[data-close-confirm]').forEach(element => {
    element.addEventListener('click', closeConfirmation);
  });

  document.addEventListener('keydown', event => {
    if (event.key === 'Escape' && confirmModal?.classList.contains('is-open')) {
      closeConfirmation();
    }
  });

  function post(url, data, btn) {
    if (btn) btn.disabled = true;
    const body = new URLSearchParams(data);
    body.set('csrf_token', csrfToken);
    return fetch(url, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body })
      .then(r => r.json())
      .then(j => {
        if (j.success) {
          msg.style.color = '#065F46';
          msg.textContent = j.message || 'Done.';
          setTimeout(() => { window.location.href = base + '/admin/replacementQueue'; }, 1100);
        } else {
          msg.style.color = '#991B1B';
          msg.textContent = j.error || 'Something went wrong.';
          if (btn) btn.disabled = false;
        }
      })
      .catch(() => {
        msg.style.color = '#991B1B';
        msg.textContent = 'Network error.';
        if (btn) btn.disabled = false;
      });
  }

  document.querySelectorAll('.assign-btn').forEach(b => {
    b.addEventListener('click', () => {
      const needs = b.getAttribute('data-needs') === '1';
      const assign = () => post(base + '/admin/assignReplacement', {
        replacement_id: replId,
        service_id: b.getAttribute('data-service')
      }, b);

      if (!needs) {
        assign();
        return;
      }

      openConfirmation({
        title: 'Propose this replacement?',
        copy: (b.getAttribute('data-service-name') || 'This replacement') + ' costs more than the original service.',
        note: 'The customer will be asked to approve the replacement and pay the price difference before the swap is finalized.',
        action: 'Send proposal'
      }, assign, b);
    });
  });

  const verifyBtn = document.getElementById('verifyBtn');
  if (verifyBtn) {
    verifyBtn.addEventListener('click', () => {
      openConfirmation({
        title: 'Verify customer payment?',
        copy: 'Confirm that the customer has paid the full price difference for this replacement.',
        note: 'This will finalize the service swap and assign the replacement supplier to the booking.',
        action: 'Verify and finalize'
      }, () => {
        post(base + '/admin/verifyReplacementPayment', { replacement_id: replId }, verifyBtn);
      }, verifyBtn);
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
