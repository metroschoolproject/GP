<?php
$supplierName = htmlspecialchars($supplier['shop_name'] ?? 'Supplier', ENT_QUOTES, 'UTF-8');
$status = strtolower($supplier['status'] ?? 'pending');
$warnLevel = (int)($supplier['warning_level'] ?? 0);
$adminNote = trim((string)($supplier['admin_note'] ?? ''));
$dashboardTitle = 'Suppliers';
$dashboardCrumb = 'Review';
$dashboardContentClass = 'supplier-review-content';
$money = fn($v) => 'MMK ' . number_format((float)$v, 0);
$perf = $performance ?? [];
$supplierFeePayment = $supplierFeePayment ?? null;
$dashboardContent = function () use ($supplier, $supplierName, $status, $warnLevel, $adminNote, $message, $money, $perf, $supplierFeePayment) {
    $rows = [
        'Owner' => $supplier['owner_name'] ?? '-',
        'Email' => $supplier['owner_email'] ?? '-',
        'Phone' => $supplier['phone'] ?? '-',
        'Address' => $supplier['address'] ?? '-',
        'Categories' => $supplier['category_names'] ?? '-',
        'Agreement' => !empty($supplier['agreement_accepted']) ? 'Accepted' : 'Not accepted',
        'Payment' => $supplier['payment_status'] ?? '-',
    ];
    $isApprovedOrVerified = in_array($status, ['approved', 'verified'], true);
    $isPending = $status === 'pending';
    $isBanned = $status === 'banned';
    $revenueEarned = (float)($perf['revenue_earned'] ?? 0);
    $totalBookings = (int)($perf['total_bookings'] ?? 0);
    $completedBookings = (int)($perf['completed_bookings'] ?? 0);
    $cancelledBookings = (int)($perf['cancelled_bookings'] ?? 0);
    $avgRating = round((float)($perf['avg_rating'] ?? 0), 1);
    $reviewCount = (int)($perf['review_count'] ?? 0);
?>
<style>
.supplier-review-content{min-height:100%;background:#FBFBF9;padding:28px 32px;font-family:'DM Sans',system-ui,sans-serif;color:#111827;font-size:13px}
.sr-shell{--s:#fcf8f5;--soft:#faf5ef;--hover:#eddecc;--border:#ead8c7;--b-light:#eddecc;--p:#6d4c5b;--ph:#7b5c69;--ps:#eddecc;--t:#111827;--m:#b79c8b;--b:#7b5c69;--sb:#d1fae5;--st:#065f46;--wb:#fef3c7;--wt:#92400e;--db:#fee2e2;--dt:#991b1b;--nb:#f3f4f6;max-width:1600px;margin:0 auto}
.sr-shell *{box-sizing:border-box}
.sr-header{display:flex;align-items:flex-end;justify-content:space-between;gap:16px;margin-bottom:22px}
.sr-eyebrow,.sr-label{font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--m)}
.sr-title{margin:0;color:var(--t);font-size:22px;font-weight:700;letter-spacing:-.3px}
.sr-sub{margin-top:4px;color:var(--b);font-size:13px}
.sr-flash{margin-bottom:18px;border-radius:.75rem;padding:12px 14px;color:var(--st);background:var(--sb);border:1px solid var(--sb);font-size:13px;font-weight:700}
.sr-warn-banner{margin-bottom:18px;border-radius:.75rem;padding:12px 14px;background:var(--wb);color:var(--wt);font-size:13px;font-weight:600}
.sr-warn-banner.l2{background:var(--db);color:var(--dt)}
.sr-layout{display:grid;grid-template-columns:minmax(0,1fr) 370px;gap:20px;align-items:start}
.sr-panel{background:var(--s);border:1px solid var(--border);border-radius:.75rem;overflow:hidden;box-shadow:0 1px 2px rgba(28,25,23,.04)}
.sr-panel-h{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:14px 20px;border-bottom:1px solid var(--b-light)}
.sr-panel-h-left{display:flex;align-items:center;gap:8px}
.sr-panel-icon{width:28px;height:28px;border-radius:.75rem;background:var(--ps);display:flex;align-items:center;justify-content:center;color:var(--p)}
.sr-panel-title{font-size:13px;font-weight:700;color:var(--t)}
.sr-panel-sub{font-size:11px;color:var(--m);margin-top:2px}
.sr-section{padding:20px}
.sr-section+.sr-section{border-top:1px solid var(--b-light)}
.sr-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:10px}
.sr-stat{border:1px solid var(--border);border-radius:.75rem;padding:14px 16px}
.sr-stat-val{margin-top:4px;font-size:16px;font-weight:700;color:var(--t)}
.sr-stat-val.success{color:var(--st)}
.sr-stat-val.warn{color:var(--wt)}
.sr-stat-val.danger{color:var(--dt)}

/* performance summary */
.sr-perf{display:grid;grid-template-columns:repeat(4,1fr);gap:10px}
.sr-perf-stat{text-align:center;padding:12px 8px;border:1px solid var(--b-light);border-radius:.75rem;background:var(--soft)}
.sr-perf-num{font-size:20px;font-weight:700;color:var(--p)}
.sr-perf-label{font-size:10px;color:var(--m);text-transform:uppercase;letter-spacing:.05em;margin-top:4px}

.sr-detail-list{display:grid;gap:0}
.sr-detail-row{display:grid;grid-template-columns:180px 1fr;gap:12px;padding:13px 0;border-bottom:1px solid var(--b-light);align-items:start}
.sr-detail-row:first-child{padding-top:0}
.sr-detail-row:last-child{border-bottom:0;padding-bottom:0}
.sr-value{color:var(--t);font-size:13px;font-weight:600;overflow-wrap:anywhere}
.sr-desc{border:1px solid var(--b-light);border-radius:.75rem;background:var(--soft);padding:14px 16px}
.sr-desc p{margin-top:8px;color:var(--b);line-height:1.75}

.badge,.sr-badge{display:inline-flex;align-items:center;border-radius:999px;padding:3px 10px;font-size:10px;font-weight:800;letter-spacing:.04em;text-transform:uppercase}
.badge-pending{background:var(--wb);color:var(--wt)}
.badge-approved{background:var(--sb);color:var(--st)}
.badge-verified{background:var(--sb);color:var(--st)}
.badge-rejected{background:var(--db);color:var(--dt)}
.badge-banned{background:var(--db);color:var(--dt)}
.badge-muted{background:var(--nb);color:var(--b);border:1px solid var(--border)}

.sr-rail{display:grid;gap:14px;position:sticky;top:20px}
.sr-file-link{display:flex;align-items:center;gap:10px;min-height:42px;border:1px solid var(--border);border-radius:.75rem;background:var(--s);padding:9px 12px;color:var(--p);font-size:13px;font-weight:700;text-decoration:none;transition:background .12s,border-color .12s}
.sr-file-link:hover{border-color:var(--p);background:var(--hover)}
.sr-empty{border:1px dashed var(--border);border-radius:.75rem;background:var(--soft);padding:18px;color:var(--m);text-align:center}

.btn,.sr-btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;width:100%;min-height:38px;border:none;border-radius:.75rem;padding:0 14px;font-size:12px;font-weight:800;font-family:inherit;cursor:pointer;transition:background .12s,transform .12s}
.btn-primary,.sr-btn-primary{background:var(--p);color:#fcf8f5}
.btn-primary:hover,.sr-btn-primary:hover{background:var(--ph);transform:translateY(-1px)}
.btn-danger,.sr-btn-danger{background:var(--dt);color:#fcf8f5}
.btn-danger:hover,.sr-btn-danger:hover{background:#7f1d1d;transform:translateY(-1px)}
.btn-warn,.sr-btn-warn{background:var(--wt);color:#fcf8f5}
.btn-warn:hover,.sr-btn-warn:hover{background:#78350f;transform:translateY(-1px)}
.btn-outline,.sr-btn-outline{border:1px solid var(--border);background:var(--s);color:var(--t)}
.btn-outline:hover,.sr-btn-outline:hover{background:var(--soft)}

.sr-action-stack{display:grid;gap:10px;padding:14px}
.sr-reviewed{border:1px solid var(--border);border-radius:.75rem;background:var(--soft);padding:14px;color:var(--b);line-height:1.6}
.sr-field{margin-bottom:14px}
.sr-field label{display:block;font-size:11px;font-weight:700;color:var(--m);margin-bottom:4px;text-transform:uppercase;letter-spacing:.05em}
.sr-field input,.sr-field textarea,.sr-field select{width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:.5rem;background:#fcf8f5;color:var(--t);font-size:13px;font-family:inherit;outline:none;resize:vertical}
.sr-field input:focus,.sr-field textarea:focus,.sr-field select:focus{border-color:var(--p);box-shadow:0 0 0 3px rgba(109,76,91,.08)}
.sr-field textarea{min-height:70px}
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:100;align-items:center;justify-content:center;padding:20px}
.modal-overlay.open{display:flex}
.modal-box{background:#fcf8f5;border-radius:1rem;padding:24px;max-width:440px;width:100%;box-shadow:0 20px 60px rgba(0,0,0,.2)}

@media(max-width:1100px){.sr-layout{grid-template-columns:1fr}.sr-rail{position:static;grid-template-columns:1fr 1fr}}
@media(max-width:760px){.supplier-review-content{padding:20px 16px}.sr-rail{grid-template-columns:1fr}.sr-stats,.sr-perf{grid-template-columns:1fr 1fr}.sr-detail-row{grid-template-columns:1fr}}
</style>

<div class="sr-shell">
  <div class="sr-header">
    <div>
      <p class="sr-eyebrow">Supplier <?= $isApprovedOrVerified ? 'Management' : 'Application' ?></p>
      <h1 class="sr-title"><?= $supplierName ?></h1>
      <p class="sr-sub">Supplier ID #<?= (int)$supplier['supplier_id'] ?></p>
    </div>
    <span class="sr-badge badge-<?= $status ?>"><?= htmlspecialchars(strtoupper($status), ENT_QUOTES, 'UTF-8') ?></span>
  </div>

  <?php if (!empty($message)): ?>
    <div class="sr-flash"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <?php if ($warnLevel > 0): ?>
    <div class="sr-warn-banner <?= $warnLevel >= 2 ? 'l2' : '' ?>">
      ⚠️ Warning Level <?= $warnLevel ?> — This supplier has <?= $warnLevel >= 2 ? 'received a final warning' : 'been issued a warning' ?>.
    </div>
  <?php endif; ?>

  <!-- Performance stats (approved/verified only) -->
  <?php if ($isApprovedOrVerified): ?>
  <div class="sr-panel" style="margin-bottom:20px">
    <div class="sr-panel-h">
      <div class="sr-panel-h-left">
        <span class="sr-panel-icon"><i data-lucide="bar-chart-3" class="h-4 w-4"></i></span>
        <span class="sr-panel-title">Performance</span>
      </div>
    </div>
    <div class="sr-section">
      <div class="sr-perf">
        <div class="sr-perf-stat"><div class="sr-perf-num"><?= (int)$totalBookings ?></div><div class="sr-perf-label">Total Bookings</div></div>
        <div class="sr-perf-stat"><div class="sr-perf-num"><?= (int)$completedBookings ?></div><div class="sr-perf-label">Completed</div></div>
        <div class="sr-perf-stat"><div class="sr-perf-num"><?= $money($revenueEarned) ?></div><div class="sr-perf-label">Revenue</div></div>
        <div class="sr-perf-stat"><div class="sr-perf-num"><?= $avgRating > 0 ? number_format($avgRating, 1) . ' ★' : '—' ?></div><div class="sr-perf-label">Rating (<?= $reviewCount ?> reviews)</div></div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Supplier Fee Payment -->
  <?php if ($supplierFeePayment): ?>
  <?php
    $feeStatus = strtolower($supplierFeePayment['status'] ?? 'pending');
    $feeSlipPath = trim((string)($supplierFeePayment['payment_slip_path'] ?? ''));
    $hasFeeSlip = $feeSlipPath !== '' && preg_match('/\.(jpe?g|png|webp|pdf)$/i', $feeSlipPath) === 1;
    $feePaymentId = (int)($supplierFeePayment['id'] ?? 0);
    $isFeePending = $feeStatus === 'pending';
  ?>
  <div class="sr-panel" style="margin-bottom:20px;border:2px solid <?= $isFeePending ? '#f59e0b' : ($feeStatus === 'success' ? '#10b981' : '#ef4444') ?>;border-radius:16px;overflow:hidden">
    <div style="background:<?= $isFeePending ? '#fef3c7' : ($feeStatus === 'success' ? '#d1fae5' : '#fee2e2') ?>;padding:18px 24px;display:flex;align-items:center;gap:16px">
      <div style="width:52px;height:52px;border-radius:14px;background:<?= $isFeePending ? '#f59e0b' : ($feeStatus === 'success' ? '#10b981' : '#ef4444') ?>;display:flex;align-items:center;justify-content:center;color:white;flex-shrink:0">
        <i data-lucide="<?= $isFeePending ? 'wallet' : ($feeStatus === 'success' ? 'check-circle' : 'x-circle') ?>" style="width:26px;height:26px"></i>
      </div>
      <div style="flex:1">
        <div style="font-size:18px;font-weight:800;color:<?= $isFeePending ? '#92400e' : ($feeStatus === 'success' ? '#065f46' : '#991b1b') ?>">
          <?= $isFeePending ? '⚠️ Supplier Fee Payment — Awaiting Review' : ($feeStatus === 'success' ? '✅ Supplier Fee — Approved' : '❌ Supplier Fee — Rejected') ?>
        </div>
        <div style="font-size:13px;color:<?= $isFeePending ? '#92400e' : ($feeStatus === 'success' ? '#065f46' : '#991b1b') ?>;margin-top:2px;opacity:.8">
          <?= $isFeePending ? 'Review the payment proof below and approve or reject.' : ($feeStatus === 'success' ? 'Payment verified. Supplier dashboard is unlocked.' : 'Payment was rejected.') ?>
        </div>
      </div>
      <?php if ($isFeePending): ?>
      <div style="display:flex;gap:10px;flex-shrink:0">
        <form method="POST" action="<?= URLROOT ?>/admin/approvePayment/<?= $feePaymentId ?>" onsubmit="return confirm('Approve this supplier fee payment?')">
          <button type="submit" style="display:inline-flex;align-items:center;gap:6px;padding:12px 20px;border:none;border-radius:10px;background:#10b981;color:white;font-size:13px;font-weight:800;cursor:pointer;box-shadow:0 4px 12px rgba(16,185,129,.3)">
            <i data-lucide="check" style="width:16px;height:16px"></i> Approve
          </button>
        </form>
        <form method="POST" action="<?= URLROOT ?>/admin/rejectPayment/<?= $feePaymentId ?>" onsubmit="return confirm('Reject this supplier fee payment?')">
          <button type="submit" style="display:inline-flex;align-items:center;gap:6px;padding:12px 20px;border:2px solid #ef4444;border-radius:10px;background:white;color:#ef4444;font-size:13px;font-weight:800;cursor:pointer">
            <i data-lucide="x" style="width:16px;height:16px"></i> Reject
          </button>
        </form>
      </div>
      <?php endif; ?>
    </div>

    <div style="padding:20px 24px;display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px">
      <div>
        <div style="font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--m);margin-bottom:4px">Amount</div>
        <div style="font-size:22px;font-weight:800;color:var(--p)"><?= $money($supplierFeePayment['paid_amount'] ?? $supplierFeePayment['amount'] ?? 0) ?></div>
      </div>
      <div>
        <div style="font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--m);margin-bottom:4px">Bank / Method</div>
        <div style="font-size:14px;font-weight:700;color:var(--t)"><?= htmlspecialchars($supplierFeePayment['bank_name'] ?? $supplierFeePayment['method'] ?? '-', ENT_QUOTES, 'UTF-8') ?></div>
      </div>
      <div>
        <div style="font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--m);margin-bottom:4px">Sender Name</div>
        <div style="font-size:14px;font-weight:700;color:var(--t)"><?= htmlspecialchars($supplierFeePayment['account_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?></div>
      </div>
      <div>
        <div style="font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--m);margin-bottom:4px">Phone</div>
        <div style="font-size:14px;font-weight:700;color:var(--t)"><?= htmlspecialchars($supplierFeePayment['mobile_number'] ?? '-', ENT_QUOTES, 'UTF-8') ?></div>
      </div>
      <div>
        <div style="font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--m);margin-bottom:4px">Transaction Ref</div>
        <div style="font-size:13px;font-weight:600;color:var(--t);font-family:monospace;background:var(--soft);padding:4px 8px;border-radius:6px;display:inline-block"><?= htmlspecialchars($supplierFeePayment['transaction_ref'] ?? '-', ENT_QUOTES, 'UTF-8') ?></div>
      </div>
      <div>
        <div style="font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--m);margin-bottom:4px">Submitted</div>
        <div style="font-size:13px;font-weight:600;color:var(--t)"><?= !empty($supplierFeePayment['created_at']) ? date('M j, Y H:i', strtotime($supplierFeePayment['created_at'])) : '-' ?></div>
      </div>
    </div>

    <?php if ($hasFeeSlip): ?>
    <div style="padding:0 24px 20px">
      <div style="font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--m);margin-bottom:8px">Payment Slip</div>
      <a href="<?= URLROOT ?>/<?= htmlspecialchars($feeSlipPath, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" style="display:inline-block;max-width:400px;border-radius:12px;overflow:hidden;border:2px solid var(--border);transition:border-color .2s">
        <img src="<?= URLROOT ?>/<?= htmlspecialchars($feeSlipPath, ENT_QUOTES, 'UTF-8') ?>" alt="Payment slip" style="width:100%;display:block;max-height:300px;object-fit:contain;background:#f9fafb">
      </a>
      <div style="margin-top:6px">
        <a href="<?= URLROOT ?>/<?= htmlspecialchars($feeSlipPath, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" style="font-size:12px;color:var(--p);font-weight:700;text-decoration:underline">Open full size →</a>
      </div>
    </div>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <div class="sr-layout">
    <!-- Left: Profile -->
    <div>
      <div class="sr-panel">
        <div class="sr-panel-h">
          <div class="sr-panel-h-left">
            <span class="sr-panel-icon"><i data-lucide="store" class="h-4 w-4"></i></span>
            <span class="sr-panel-title">Business Profile</span>
          </div>
        </div>
        <div class="sr-section">
          <div class="sr-detail-list">
            <?php foreach ($rows as $label => $value): ?>
              <div class="sr-detail-row">
                <span class="sr-label"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span>
                <span class="sr-value"><?= is_string($value) ? $value : htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8') ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="sr-section">
          <div class="sr-desc">
            <p class="sr-label">Business description</p>
            <p><?= htmlspecialchars($supplier['description'] ?? 'No description provided.', ENT_QUOTES, 'UTF-8') ?></p>
          </div>
        </div>
      </div>
    </div>

    <!-- Right: Actions -->
    <aside class="sr-rail">
      <!-- Verification links -->
      <div class="sr-panel">
        <div class="sr-panel-h">
          <div><span class="sr-panel-title">Verification</span></div>
        </div>
        <div style="padding:14px;display:grid;gap:8px">
          <?php if (!empty($supplier['verify_url'])): ?>
            <a href="<?= htmlspecialchars($supplier['verify_url'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" class="sr-file-link"><i data-lucide="external-link" class="h-4 w-4"></i> Website / social link</a>
          <?php endif; ?>
          <?php if (!empty($supplier['business_license_url'])): ?>
            <a href="<?= htmlspecialchars($supplier['business_license_url'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" class="sr-file-link"><i data-lucide="file-badge" class="h-4 w-4"></i> Business license</a>
          <?php endif; ?>
          <?php if (empty($supplier['verify_url']) && empty($supplier['business_license_url'])): ?>
            <p class="sr-empty">No verification files found.</p>
          <?php endif; ?>
        </div>
      </div>

      <!-- Decision / Management -->
      <div class="sr-panel">
        <div class="sr-panel-h">
          <div><span class="sr-panel-title"><?= $isPending ? 'Decision' : 'Management' ?></span></div>
        </div>
        <div class="sr-action-stack">
          <?php if ($isPending): ?>
            <form method="post" action="<?= URLROOT ?>/admin/approveSupplier/<?= (int)$supplier['supplier_id'] ?>" onsubmit="return confirm('Approve this supplier?')">
              <button class="sr-btn btn-primary" type="submit"><i data-lucide="check" class="h-4 w-4"></i> Approve supplier</button>
            </form>
            <form method="post" action="<?= URLROOT ?>/admin/rejectSupplier/<?= (int)$supplier['supplier_id'] ?>" onsubmit="return confirm('Reject this application?')">
              <button class="sr-btn btn-danger" type="submit"><i data-lucide="x" class="h-4 w-4"></i> Reject</button>
            </form>
          <?php elseif ($isBanned): ?>
            <p class="sr-reviewed" style="margin-bottom:8px">This supplier is <strong>banned</strong>.</p>
            <form method="post" action="<?= URLROOT ?>/admin/unbanSupplier/<?= (int)$supplier['supplier_id'] ?>" onsubmit="return confirm('Unban this supplier?')">
              <button class="sr-btn btn-primary" type="submit"><i data-lucide="refresh-cw" class="h-4 w-4"></i> Unban & restore</button>
            </form>
          <?php elseif ($isApprovedOrVerified): ?>
            <!-- Ban -->
            <button class="sr-btn btn-danger" type="button" onclick="openModal('ban')"><i data-lucide="ban" class="h-4 w-4"></i> Ban supplier</button>
            <!-- Warn level 1 -->
            <button class="sr-btn btn-warn" type="button" onclick="openModal('warn1')"><i data-lucide="alert-triangle" class="h-4 w-4"></i> Issue warning</button>
            <?php if ($warnLevel >= 1): ?>
              <!-- Escalate to final warning -->
              <button class="sr-btn btn-danger" type="button" onclick="openModal('warn2')"><i data-lucide="alert-octagon" class="h-4 w-4"></i> Final warning</button>
            <?php endif; ?>
          <?php else: ?>
            <p class="sr-reviewed">This supplier has already been reviewed (<?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?>).</p>
          <?php endif; ?>
        </div>
      </div>
    </aside>
  </div>
</div>

<!-- Modals for ban / warn -->
<div class="modal-overlay" id="modalBan">
  <div class="modal-box">
    <h3 style="font-size:16px;font-weight:700;margin-bottom:12px">Ban Supplier</h3>
    <form method="post" action="<?= URLROOT ?>/admin/banSupplier/<?= (int)$supplier['supplier_id'] ?>">
      <div class="sr-field"><label>Reason for ban <span style="color:#ef4444">*</span></label><textarea name="reason" required placeholder="Explain why this supplier is being banned..."></textarea></div>
      <div style="display:flex;gap:8px;justify-content:flex-end">
        <button type="button" class="sr-btn btn-outline" style="width:auto" onclick="closeModal('ban')">Cancel</button>
        <button type="submit" class="sr-btn btn-danger" style="width:auto">Confirm Ban</button>
      </div>
    </form>
  </div>
</div>

<div class="modal-overlay" id="modalWarn1">
  <div class="modal-box">
    <h3 style="font-size:16px;font-weight:700;margin-bottom:12px">Issue Warning (Level 1)</h3>
    <form method="post" action="<?= URLROOT ?>/admin/warnSupplier/<?= (int)$supplier['supplier_id'] ?>">
      <input type="hidden" name="warning_level" value="1">
      <div class="sr-field"><label>Warning note <span style="color:#ef4444">*</span></label><textarea name="warn_note" required placeholder="Describe the issue..."></textarea></div>
      <div style="display:flex;gap:8px;justify-content:flex-end">
        <button type="button" class="sr-btn btn-outline" style="width:auto" onclick="closeModal('warn1')">Cancel</button>
        <button type="submit" class="sr-btn btn-warn" style="width:auto">Issue Warning</button>
      </div>
    </form>
  </div>
</div>

<div class="modal-overlay" id="modalWarn2">
  <div class="modal-box">
    <h3 style="font-size:16px;font-weight:700;margin-bottom:12px">Final Warning (Level 2)</h3>
    <form method="post" action="<?= URLROOT ?>/admin/warnSupplier/<?= (int)$supplier['supplier_id'] ?>">
      <input type="hidden" name="warning_level" value="2">
      <div class="sr-field"><label>Final warning note <span style="color:#ef4444">*</span></label><textarea name="warn_note" required placeholder="Describe the serious issue..."></textarea></div>
      <div style="display:flex;gap:8px;justify-content:flex-end">
        <button type="button" class="sr-btn btn-outline" style="width:auto" onclick="closeModal('warn2')">Cancel</button>
        <button type="submit" class="sr-btn btn-danger" style="width:auto">Issue Final Warning</button>
      </div>
    </form>
  </div>
</div>

<script>
function openModal(id) { document.getElementById('modal'+id.charAt(0).toUpperCase()+id.slice(1)).classList.add('open'); }
function closeModal(id) { document.getElementById('modal'+id.charAt(0).toUpperCase()+id.slice(1)).classList.remove('open'); }
document.querySelectorAll('.modal-overlay').forEach(function(m){ m.addEventListener('click',function(e){ if(e.target===m) m.classList.remove('open'); }); });
</script>
<?php
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php require_once APPROOT . '/views/dashboardLayout/head.php' ?>
</head>
<body class="grid h-screen gap-0 bg-app-page" style="grid-template-columns: 280px 1fr;">
    <?php require_once APPROOT . '/views/dashboardLayout/sidebar.php' ?>
</body>
</html>
