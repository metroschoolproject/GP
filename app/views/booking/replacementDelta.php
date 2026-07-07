<?php
$replacement = $replacement ?? [];
$delta       = (float)($delta ?? 0);
$bookingRef  = $bookingRef ?? '';
$flash       = $flash ?? null;

$money = fn($v) => number_format((float)$v, 0) . ' MMK';
$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');

$replacementId = (int)($replacement['id'] ?? 0);
$newShop = $replacement['new_shop_name'] ?? 'the new supplier';
$newService = $replacement['new_service_name'] ?? '';
$oldService = $replacement['old_service_name'] ?? 'your service';
$oldSupplier = $replacement['old_shop_name'] ?? $replacement['old_supplier_name'] ?? 'your original supplier';
$oldPrice = (float)($replacement['old_price'] ?? 0);
$newPrice = (float)($replacement['new_price'] ?? 0);
$declineReason = $replacement['decline_reason'] ?? '';
$proposedAt = $replacement['proposed_at'] ?? null;
$deadline = $proposedAt ? date('M d, Y', strtotime($proposedAt . ' + 3 days')) : null;
$daysLeft = $proposedAt ? max(0, (int)((strtotime($proposedAt) + 3*86400 - time()) / 86400)) : null;
$banks = ['KBZ Pay', 'Wave Money', 'AYA Pay', 'Yoma Bank', 'CB Bank', 'Visa / MasterCard'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="icon" type="image/png" href="<?= URLROOT ?>/public/images/home/gp_logo.png">
<title>Approve Replacement · Golden Promise</title>
<style>
  *{box-sizing:border-box}
  body{margin:0;font-family:'DM Sans',system-ui,-apple-system,sans-serif;background:#fbeee0;color:#111827;padding:24px 16px 0}
  body > .gp-shared-footer{margin-top:92px}
  .wrap{width:min(100%,1120px);margin:0 auto;padding-bottom:36px}
  .payment-layout{display:grid;grid-template-columns:minmax(0,1fr) 360px;gap:18px;align-items:start;margin-top:18px}
  .payment-main,.payment-side{min-width:0}
  .payment-side{position:sticky;top:18px}
  .eyebrow{font-size:11px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:#b79c8b}
  h1{font-size:24px;font-weight:700;margin:6px 0 0}
  h1 em{font-style:italic;color:#6d4c5b}
  .card{background:#fcf8f5;border:1px solid rgba(184,146,74,.38);border-radius:18px;padding:0;margin-top:0;margin-bottom:16px;overflow:hidden;box-shadow:0 18px 48px rgba(26,17,24,.07)}
  .card-label{font-family:'DM Sans',system-ui,-apple-system,sans-serif;font-size:10px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:#b79c8b;padding:18px 22px;border-bottom:1px solid #f0e6da;margin-bottom:0}
  .card-body{padding:20px 22px}
  .row{display:flex;justify-content:space-between;align-items:center;gap:14px;padding:9px 0;border-bottom:1px solid #f0e6da;font-size:14px}
  .row:last-child{border-bottom:none}
  .row .v{font-weight:700}
  .delta{font-size:20px;font-weight:800;color:#991b1b;white-space:nowrap}
  .flash{background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;border-radius:10px;padding:11px 14px;margin-top:14px;font-size:13px;font-weight:600}
  .context-banner{background:#fffdf4;border:1px solid rgba(184,146,74,.42);border-radius:10px;padding:12px 14px;margin-bottom:12px;font-size:13px;color:#7a5c35;line-height:1.45}
  label{display:block;font-size:12px;font-weight:700;color:#57534e;margin:12px 0 5px}
  input,select{width:100%;height:40px;padding:0 12px;border:1px solid #ead8c7;border-radius:10px;font-size:14px;font-family:inherit;background:#fcf8f5;outline:none}
  input:focus,select:focus{border-color:#6d4c5b}
  .transfer-amount-field{padding:11px;border:1px solid rgba(22,101,52,.18);border-radius:12px;background:#f0fdf4}
  .transfer-amount-field input{background:#fff}
  .file{display:flex;align-items:center;gap:8px;height:auto;padding:13px 14px;border:1.5px dashed #ead8c7;border-radius:10px;cursor:pointer;color:#7b5c69;font-size:13px}
  .grid2{display:grid;grid-template-columns:1fr 1fr;gap:0 14px}
  .btn{width:100%;height:46px;border:none;border-radius:12px;background:#6d4c5b;color:#fcf8f5;font-size:15px;font-weight:700;margin-top:18px;cursor:pointer}
  .btn:hover{opacity:.93}
  .muted{font-size:12.5px;color:#7b5c69;margin-top:10px;line-height:1.5}
  a.back{display:inline-flex;align-items:center;justify-content:center;gap:6px;min-width:142px;height:36px;padding:0 16px;border-radius:999px;border:1px solid rgba(184,146,74,.58);background:rgba(255,250,245,.62);color:#7a5c35;font-size:12px;font-weight:700;text-decoration:none;transition:all .22s}
  a.back:hover{background:#fffaf5;border-color:#b8924a;color:#6d4c5b;transform:translateY(-1px)}
  .deadline-badge{display:inline-flex;align-items:center;gap:6px;background:#fef3c7;border:1px solid #fcd34d;border-radius:999px;padding:6px 12px;font-size:12px;font-weight:700;color:#92400e;margin-top:10px}
  .vs-comparison{display:grid;grid-template-columns:1fr auto 1fr;align-items:stretch;gap:10px;margin:12px 0;padding:12px;background:#faf6f1;border-radius:10px}
  .vs-original,.vs-new{display:grid;align-content:center;padding:10px 8px;text-align:center}
  .vs-original{border:1px solid #fecaca;border-radius:8px;background:#fff}
  .vs-new{border:1px solid #bbf7d0;border-radius:8px;background:#fff}
  .vs-label{font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:#b79c8b;margin-bottom:4px}
  .vs-name{font-size:12.5px;font-weight:700;margin-bottom:2px;line-height:1.25}
  .vs-shop{font-size:11px;color:#7b5c69;margin-bottom:4px}
  .vs-price{font-size:13.5px;font-weight:800}
  .vs-arrow{font-size:18px;color:#6d4c5b;font-weight:700}
  .decline-form{margin:0 0 18px}
  .btn-decline{background:#fff!important;color:#991b1b!important;border:1px solid #fecaca!important;margin-top:0!important}
  .gp-modal-backdrop{position:fixed;inset:0;z-index:50;display:none;align-items:center;justify-content:center;padding:18px;background:rgba(31,24,20,.58);backdrop-filter:blur(4px)}
  .gp-modal-backdrop.is-open{display:flex}
  .gp-modal{width:min(100%,430px);border:1px solid rgba(184,146,74,.34);border-radius:18px;background:#fcf8f5;box-shadow:0 28px 80px rgba(26,17,24,.28);overflow:hidden}
  .gp-modal-head{padding:22px 22px 12px}
  .gp-modal-kicker{font-size:10px;font-weight:800;letter-spacing:.14em;text-transform:uppercase;color:#b79c8b}
  .gp-modal-title{margin:6px 0 0;font-size:20px;line-height:1.2;color:#3b2632}
  .gp-modal-body{padding:0 22px 18px;color:#6f5662;font-size:13.5px;line-height:1.55}
  .gp-modal-note{margin-top:12px;padding:12px 13px;border:1px solid #f4c7c7;border-radius:12px;background:#fff7f7;color:#991b1b;font-weight:650}
  .gp-modal-actions{display:flex;justify-content:flex-end;gap:10px;padding:14px 22px 20px;border-top:1px solid #f0e6da}
  .gp-modal-btn{height:42px;padding:0 18px;border-radius:999px;border:1px solid #ead8c7;background:#fff;color:#6d4c5b;font:700 13px inherit;cursor:pointer}
  .gp-modal-btn:hover{background:#fff8f8}
  .gp-modal-btn.danger{border-color:#991b1b;background:#991b1b;color:#fff}
  .gp-modal-btn.danger:hover{background:#7f1d1d}
  @media(max-width:900px){.payment-layout{grid-template-columns:1fr}.payment-side{position:static;order:-1}.grid2{grid-template-columns:1fr}body > .gp-shared-footer{margin-top:72px}}
  @media(max-width:520px){body{padding:18px 12px 0}.card-body{padding:18px 16px}.card-label{padding:16px}.vs-comparison{grid-template-columns:1fr}.vs-arrow{transform:rotate(90deg);justify-self:center}.row{align-items:flex-start;flex-direction:column}.delta{white-space:normal}}
</style>
</head>
<body>
<div class="wrap">
  <div style="margin-bottom:18px"><a class="back" href="<?= URLROOT ?>/booking/detail/<?= (int)($replacement['booking_id'] ?? 0) ?>">← Back to booking</a></div>
  <div class="eyebrow">Replacement supplier</div>
  <h1>Approve &amp; <em>Pay the Difference</em></h1>

  <?php if ($flash): ?>
    <div class="flash"><?= $h($flash) ?></div>
  <?php endif; ?>

  <div class="payment-layout">
    <div class="payment-main">
      <form class="card" action="<?= URLROOT ?>/booking/submitReplacementDelta" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="card-label">Bank transfer proof</div>
        <div class="card-body">
          <input type="hidden" name="replacement_id" value="<?= $replacementId ?>">

          <label for="bank_name">Payment method</label>
          <select name="bank_name" id="bank_name" required>
            <option value="">Select…</option>
            <?php foreach ($banks as $b): ?>
              <option value="<?= $h($b) ?>"><?= $h($b) ?></option>
            <?php endforeach; ?>
          </select>

          <div class="grid2">
            <div>
              <label for="account_name">Account name</label>
              <input type="text" name="account_name" id="account_name" required>
            </div>
            <div>
              <label for="mobile_number">Mobile number</label>
              <input type="text" name="mobile_number" id="mobile_number" required>
            </div>
          </div>

          <div class="grid2">
            <div>
              <label for="transaction_ref">Transaction reference</label>
              <input type="text" name="transaction_ref" id="transaction_ref" required>
            </div>
            <div class="transfer-amount-field">
              <label for="paid_amount">Amount paid (MMK)</label>
              <input type="text" name="paid_amount" id="paid_amount" value="<?= (int)$delta ?>" required>
            </div>
          </div>

          <label for="slip_image">Payment slip</label>
          <label class="file" for="slip_image">
            <span id="slipName">Click to upload screenshot or receipt</span>
          </label>
          <input type="file" name="slip_image" id="slip_image" accept="image/*" style="display:none">

          <button type="submit" class="btn">Submit payment &amp; approve replacement</button>
          <p class="muted">Admin verifies the transfer, then confirms your replacement and notifies the new supplier. This usually takes 1–2 hours.</p>
        </div>
      </form>

      <form class="decline-form" id="declineReplacementForm" action="<?= URLROOT ?>/booking/rejectReplacement/<?= $replacementId ?>" method="post">
        <?= csrf_field() ?>
        <button type="button" class="btn btn-decline" id="openDeclineModal">
          Decline — find another option
        </button>
      </form>
    </div>

    <aside class="payment-side">
      <!-- Context banner -->
      <div class="card">
        <div class="card-label">Replacement Overview</div>
        <div class="card-body">
          <div class="context-banner">
            <strong><?= $h($oldService) ?></strong> from <strong><?= $h($oldSupplier) ?></strong> is no longer available.
            <?php if ($declineReason !== ''): ?><div style="margin-top:4px;opacity:.8">Reason: <?= $h($declineReason) ?></div><?php endif; ?>
            Admin has found a replacement supplier for you.
          </div>

          <!-- Side-by-side comparison -->
          <div class="vs-comparison">
            <div class="vs-original">
              <div class="vs-label">Original</div>
              <div class="vs-name"><?= $h($oldService) ?></div>
              <div class="vs-shop"><?= $h($oldSupplier) ?></div>
              <div class="vs-price"><?= $money($oldPrice) ?></div>
            </div>
            <div class="vs-arrow">→</div>
            <div class="vs-new">
              <div class="vs-label">Replacement</div>
              <div class="vs-name"><?= $h($newService !== '' ? $newService : 'Service') ?></div>
              <div class="vs-shop"><?= $h($newShop) ?></div>
              <div class="vs-price" style="color:<?= $delta > 0 ? '#dc2626' : '#16a34a' ?>"><?= $money($newPrice) ?></div>
            </div>
          </div>

          <div class="row"><span>Price difference</span><span class="delta">+<?= $money($delta) ?></span></div>
          <?php if ($deadline): ?>
          <div class="deadline-badge">
            ⏰ Respond by <strong><?= $deadline ?></strong>
            <?php if ($daysLeft !== null): ?>(<?= $daysLeft ?> day<?= $daysLeft === 1 ? '' : 's' ?> left)<?php endif; ?>
          </div>
          <?php endif; ?>
          <p class="muted">Pay the price difference to confirm this replacement. After admin verification, the new supplier will confirm. If you prefer not to pay more, you can decline — admin will find another option at no extra cost.</p>
        </div>
      </div>
    </aside>
  </div>
</div>

<div class="gp-modal-backdrop" id="declineModal" aria-hidden="true">
  <div class="gp-modal" role="dialog" aria-modal="true" aria-labelledby="declineModalTitle" aria-describedby="declineModalText">
    <div class="gp-modal-head">
      <div class="gp-modal-kicker">Replacement decision</div>
      <h2 class="gp-modal-title" id="declineModalTitle">Decline this replacement?</h2>
    </div>
    <div class="gp-modal-body">
      <p id="declineModalText">Admin will find another option for your booking at no extra cost to you.</p>
      <div class="gp-modal-note">This replacement will be removed from your pending approvals.</div>
    </div>
    <div class="gp-modal-actions">
      <button type="button" class="gp-modal-btn" id="cancelDeclineModal">Keep replacement</button>
      <button type="button" class="gp-modal-btn danger" id="confirmDeclineReplacement">Decline replacement</button>
    </div>
  </div>
</div>

<script>
  const slip = document.getElementById('slip_image');
  const slipName = document.getElementById('slipName');
  if (slip) slip.addEventListener('change', function () {
    slipName.textContent = this.files[0] ? this.files[0].name : 'Click to upload screenshot or receipt';
  });

  const declineForm = document.getElementById('declineReplacementForm');
  const declineModal = document.getElementById('declineModal');
  const openDeclineModal = document.getElementById('openDeclineModal');
  const cancelDeclineModal = document.getElementById('cancelDeclineModal');
  const confirmDeclineReplacement = document.getElementById('confirmDeclineReplacement');

  function setDeclineModal(open) {
    if (!declineModal) return;
    declineModal.classList.toggle('is-open', open);
    declineModal.setAttribute('aria-hidden', open ? 'false' : 'true');
    document.body.style.overflow = open ? 'hidden' : '';
    if (open) {
      cancelDeclineModal?.focus();
    } else {
      openDeclineModal?.focus();
    }
  }

  openDeclineModal?.addEventListener('click', () => setDeclineModal(true));
  cancelDeclineModal?.addEventListener('click', () => setDeclineModal(false));
  declineModal?.addEventListener('click', (event) => {
    if (event.target === declineModal) setDeclineModal(false);
  });
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && declineModal?.classList.contains('is-open')) {
      setDeclineModal(false);
    }
  });
  confirmDeclineReplacement?.addEventListener('click', () => {
    declineForm?.submit();
  });
</script>
<?php require APPROOT . '/views/layouts/customerFooter.php'; ?>
</body>
</html>
