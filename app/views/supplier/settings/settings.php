<?php
$supplierId          = $supplier_id ?? 0;
$isAvailable         = $is_available ?? 0;
$autoAcceptBookings  = $auto_accept_bookings ?? 0;
$minAdvanceDays      = $min_advance_days ?? 0;
$cancellationPolicy  = $cancellation_policy ?? '';
$bankAccount         = $bank_account ?? '';
$bankCode            = $bank_code ?? '';
$platformFee         = $platform_fee ?? 10;
$notifPrefs          = $notification_prefs ?? ['new_booking' => true, 'payment_received' => true, 'new_review' => true, 'publish_approved' => true];

$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');

$dashboardTitle       = 'Settings';
$dashboardCrumb       = 'Settings';
$dashboardBreadcrumbs = [
    ['label' => 'Dashboard', 'url' => URLROOT . '/supplier/dashboard'],
    ['label' => 'Settings', 'url' => null],
];
$dashboardContentClass = 'settings-page px-6 py-6';

$dashboardContent = function () use (
    $supplierId, $isAvailable, $autoAcceptBookings, $minAdvanceDays,
    $cancellationPolicy, $bankAccount, $bankCode, $platformFee, $notifPrefs, $h
) {
?>
<style>
.settings-page { --ink:#6d4c5b; --muted:#A8A29E; --soft:#F4F1EE; --panel:#FFFFFF; --line:#ead8c7; --primary:#6d4c5b; color:var(--ink); }
.settings-page h1 { font-size:clamp(28px,3vw,42px); font-weight:900; margin:6px 0 7px; color:var(--ink); }
.settings-page .kicker { font-size:10px; font-weight:900; text-transform:uppercase; letter-spacing:.17em; color:var(--muted); }
.settings-page p.desc { color:var(--muted); font-size:13px; }

/* Grid */
.s-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; align-items:start; margin-top:16px; }
.s-col { display:flex; flex-direction:column; gap:16px; }

/* Card */
.s-card { background:var(--panel); border:1px solid var(--line); border-radius:14px; overflow:hidden; }
.s-card-head { padding:14px 18px; border-bottom:1px solid var(--line); display:flex; align-items:center; gap:8px; font-size:14px; font-weight:700; color:var(--ink); }
.s-card-head i { width:18px; height:18px; color:var(--muted); }
.s-card-body { padding:18px; }

/* Toggle row */
.s-toggle-row { display:flex; align-items:center; justify-content:space-between; padding:12px 0; }
.s-toggle-row + .s-toggle-row { border-top:1px solid var(--line); }
.s-toggle-info { flex:1; min-width:0; padding-right:16px; }
.s-toggle-label { font-size:13px; font-weight:700; color:var(--ink); }
.s-toggle-desc { font-size:12px; color:var(--muted); margin-top:2px; }

/* Toggle switch */
.s-switch { position:relative; width:44px; height:24px; flex-shrink:0; }
.s-switch input { opacity:0; width:0; height:0; }
.s-switch-slider { position:absolute; inset:0; background:var(--line); border-radius:999px; cursor:pointer; transition:background .2s; }
.s-switch-slider::before { content:''; position:absolute; left:3px; top:3px; width:18px; height:18px; background:#fff; border-radius:50%; transition:transform .2s; box-shadow:0 1px 3px rgba(0,0,0,.1); }
.s-switch input:checked + .s-switch-slider { background:var(--primary); }
.s-switch input:checked + .s-switch-slider::before { transform:translateX(20px); }

/* Availability banner */
.s-avail-banner { display:flex; align-items:center; gap:14px; padding:16px 18px; background:var(--soft); border:1px solid var(--line); border-radius:14px; margin-bottom:16px; }
.s-avail-banner.active { background:#ECFDF5; border-color:#bbf7d0; }
.s-avail-banner.inactive { background:#FEF2F2; border-color:#fecaca; }
.s-avail-icon { width:40px; height:40px; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.s-avail-banner.active .s-avail-icon { background:#d1fae5; color:#065F46; }
.s-avail-banner.inactive .s-avail-icon { background:#fee2e2; color:#991B1B; }
.s-avail-info { flex:1; }
.s-avail-title { font-size:14px; font-weight:700; color:var(--ink); }
.s-avail-desc { font-size:12px; color:var(--muted); margin-top:2px; }

/* Form */
.s-label { display:block; font-size:10px; font-weight:800; letter-spacing:.1em; text-transform:uppercase; color:var(--muted); margin-bottom:6px; }
.s-input { width:100%; height:44px; padding:0 14px; border:1px solid var(--line); border-radius:10px; background:var(--soft); color:var(--ink); font-size:13px; font-family:'DM Sans',sans-serif; transition:border-color .2s, box-shadow .2s, background .2s; box-sizing:border-box; }
.s-input:focus { outline:none; border-color:var(--primary); background:#fff; box-shadow:0 0 0 3px rgba(109,76,91,.08); }
textarea.s-input { height:auto; min-height:80px; padding:12px 14px; resize:vertical; }
.s-select { appearance:none; background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23A8A29E' stroke-width='2'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E"); background-repeat:no-repeat; background-position:right 12px center; padding-right:32px; }
.s-row { margin-bottom:14px; }
.s-row-inline { display:flex; align-items:center; gap:12px; margin-bottom:14px; }
.s-row-inline .s-input { flex:1; }
.s-hint { font-size:11px; color:var(--muted); margin-top:4px; }

/* Read-only field */
.s-readonly { display:flex; align-items:center; justify-content:space-between; padding:12px 16px; background:var(--soft); border:1px solid var(--line); border-radius:10px; }
.s-readonly-label { font-size:10px; font-weight:800; letter-spacing:.1em; text-transform:uppercase; color:var(--muted); }
.s-readonly-value { font-size:18px; font-weight:800; color:var(--ink); }

/* Buttons */
.s-btn { display:inline-flex; align-items:center; gap:7px; min-height:38px; padding:0 18px; border-radius:10px; font-size:13px; font-weight:600; cursor:pointer; transition:all .2s; border:none; font-family:'DM Sans',sans-serif; }
.s-btn-primary { background:var(--primary); color:#fff; }
.s-btn-primary:hover { opacity:.85; }
.s-btn-outline { background:transparent; border:1px solid var(--line); color:var(--ink); }
.s-btn-outline:hover { border-color:var(--primary); color:var(--primary); }
.s-actions { display:flex; gap:8px; justify-content:flex-end; padding-top:14px; }

/* Messages */
.s-msg { display:none; padding:10px 14px; border-radius:10px; font-size:12px; font-weight:600; margin-bottom:12px; }
.s-msg.success { display:block; background:#ECFDF5; color:#065F46; }
.s-msg.error { display:block; background:#FEF2F2; color:#991B1B; }

@media(max-width:900px){ .s-grid{grid-template-columns:1fr} .s-row-inline{flex-direction:column;align-items:stretch} }
</style>

<section class="settings-page">

  <div>
    <div class="kicker">Supplier workspace</div>
    <h1>Settings</h1>
    <p class="desc">Manage your booking preferences, notifications, and payment details.</p>
  </div>

  <!-- Availability Banner -->
  <div class="s-avail-banner <?= $isAvailable ? 'active' : 'inactive' ?>" id="availBanner">
    <div class="s-avail-icon">
      <i data-lucide="<?= $isAvailable ? 'check-circle' : 'pause-circle' ?>"></i>
    </div>
    <div class="s-avail-info">
      <div class="s-avail-title"><?= $isAvailable ? 'You are accepting bookings' : 'You are currently unavailable' ?></div>
      <div class="s-avail-desc"><?= $isAvailable ? 'Customers can discover and book your services.' : 'Your services are hidden from customers.' ?></div>
    </div>
    <label class="s-switch">
      <input type="checkbox" id="toggleAvailable" <?= $isAvailable ? 'checked' : '' ?>>
      <span class="s-switch-slider"></span>
    </label>
  </div>

  <div class="s-grid">

    <!-- Left Column -->
    <div class="s-col">

      <!-- Booking Preferences -->
      <div class="s-card">
        <div class="s-card-head"><i data-lucide="calendar-check"></i> Booking Preferences</div>
        <div class="s-card-body">
          <div id="bookingMsg" class="s-msg"></div>

          <div class="s-toggle-row">
            <div class="s-toggle-info">
              <div class="s-toggle-label">Auto-accept bookings</div>
              <div class="s-toggle-desc">Automatically confirm new bookings without manual review.</div>
            </div>
            <label class="s-switch">
              <input type="checkbox" id="toggleAutoAccept" <?= $autoAcceptBookings ? 'checked' : '' ?>>
              <span class="s-switch-slider"></span>
            </label>
          </div>

          <div class="s-row" style="margin-top:14px">
            <label class="s-label">Minimum advance booking (days)</label>
            <input id="minAdvanceDays" class="s-input" type="number" min="0" max="365" value="<?= (int)$minAdvanceDays ?>">
            <p class="s-hint">How many days before the event customers must book. Set 0 for no minimum.</p>
          </div>

          <div class="s-row">
            <label class="s-label">Cancellation policy</label>
            <textarea id="cancellationPolicy" class="s-input" placeholder="e.g., Free cancellation up to 7 days before the event..."><?= $h($cancellationPolicy) ?></textarea>
            <p class="s-hint">Displayed to customers on your service pages.</p>
          </div>

          <div class="s-actions">
            <button type="button" class="s-btn s-btn-outline" id="btnCancelBooking">Cancel</button>
            <button type="button" class="s-btn s-btn-primary" id="btnSaveBooking"><i data-lucide="save"></i> Save</button>
          </div>
        </div>
      </div>

      <!-- Platform Fee (read-only) -->
      <div class="s-card">
        <div class="s-card-head"><i data-lucide="info"></i> Platform Fee</div>
        <div class="s-card-body">
          <div class="s-readonly">
            <div>
              <div class="s-readonly-label">Current fee rate</div>
              <p class="s-hint" style="margin-top:2px">Charged to the customer on each booking.</p>
            </div>
            <div class="s-readonly-value"><?= (int)$platformFee ?>%</div>
          </div>
        </div>
      </div>

    </div>

    <!-- Right Column -->
    <div class="s-col">

      <!-- Notification Preferences -->
      <div class="s-card">
        <div class="s-card-head"><i data-lucide="bell"></i> Notification Preferences</div>
        <div class="s-card-body">
          <div id="notifMsg" class="s-msg"></div>

          <div class="s-toggle-row">
            <div class="s-toggle-info">
              <div class="s-toggle-label">New booking</div>
              <div class="s-toggle-desc">Get notified when a customer books your service.</div>
            </div>
            <label class="s-switch">
              <input type="checkbox" id="notifNewBooking" <?= !empty($notifPrefs['new_booking']) ? 'checked' : '' ?>>
              <span class="s-switch-slider"></span>
            </label>
          </div>

          <div class="s-toggle-row">
            <div class="s-toggle-info">
              <div class="s-toggle-label">Payment received</div>
              <div class="s-toggle-desc">Get notified when a customer payment is verified.</div>
            </div>
            <label class="s-switch">
              <input type="checkbox" id="notifPayment" <?= !empty($notifPrefs['payment_received']) ? 'checked' : '' ?>>
              <span class="s-switch-slider"></span>
            </label>
          </div>

          <div class="s-toggle-row">
            <div class="s-toggle-info">
              <div class="s-toggle-label">New review</div>
              <div class="s-toggle-desc">Get notified when a customer leaves a review.</div>
            </div>
            <label class="s-switch">
              <input type="checkbox" id="notifReview" <?= !empty($notifPrefs['new_review']) ? 'checked' : '' ?>>
              <span class="s-switch-slider"></span>
            </label>
          </div>

          <div class="s-toggle-row">
            <div class="s-toggle-info">
              <div class="s-toggle-label">Publish approved</div>
              <div class="s-toggle-desc">Get notified when admin approves your service.</div>
            </div>
            <label class="s-switch">
              <input type="checkbox" id="notifPublish" <?= !empty($notifPrefs['publish_approved']) ? 'checked' : '' ?>>
              <span class="s-switch-slider"></span>
            </label>
          </div>

          <div class="s-actions">
            <button type="button" class="s-btn s-btn-primary" id="btnSaveNotif"><i data-lucide="save"></i> Save</button>
          </div>
        </div>
      </div>

    </div>
  </div>

</section>

<script>
(function(){
'use strict';
if (window.lucide) lucide.createIcons();

function msg(id,t,tp){var el=document.getElementById(id);if(!el)return;el.textContent=t;el.className='s-msg '+(tp||'');if(tp)setTimeout(function(){el.className='s-msg';el.textContent='';},5000);}

function save(data, msgId, btn){
  btn.disabled=true;var orig=btn.innerHTML;btn.innerHTML='<i data-lucide="loader"></i> Saving…';if(window.lucide)lucide.createIcons();
  fetch('<?= URLROOT ?>/supplier/updateSettings',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(data)})
  .then(function(r){return r.json()}).then(function(d){
    btn.disabled=false;btn.innerHTML=orig;if(window.lucide)lucide.createIcons();
    if(d.ok)msg(msgId,'✓ Saved.','success');else msg(msgId,d.error||'Failed.','error');
  }).catch(function(){btn.disabled=false;btn.innerHTML=orig;if(window.lucide)lucide.createIcons();msg(msgId,'Network error.','error');});
}

// Availability toggle
document.getElementById('toggleAvailable').addEventListener('change',function(){
  var on=this.checked;
  fetch('<?= URLROOT ?>/supplier/updateSettings',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({is_available:on?1:0})})
  .then(function(r){return r.json()}).then(function(d){
    if(d.ok){
      var b=document.getElementById('availBanner');
      b.className='s-avail-banner '+(on?'active':'inactive');
      b.querySelector('.s-avail-title').textContent=on?'You are accepting bookings':'You are currently unavailable';
      b.querySelector('.s-avail-desc').textContent=on?'Customers can discover and book your services.':'Your services are hidden from customers.';
      b.querySelector('.s-avail-icon i').setAttribute('data-lucide',on?'check-circle':'pause-circle');
      if(window.lucide)lucide.createIcons();
    }
  });
});

// Booking preferences
var origAutoAccept=<?= $autoAcceptBookings ? 'true' : 'false' ?>;
var origMinDays=<?= (int)$minAdvanceDays ?>;
var origPolicy=<?= json_encode($cancellationPolicy) ?>;

document.getElementById('btnCancelBooking').addEventListener('click',function(){
  document.getElementById('toggleAutoAccept').checked=origAutoAccept;
  document.getElementById('minAdvanceDays').value=origMinDays;
  document.getElementById('cancellationPolicy').value=origPolicy;
  msg('bookingMsg','','');
});

document.getElementById('btnSaveBooking').addEventListener('click',function(){
  save({
    auto_accept_bookings:document.getElementById('toggleAutoAccept').checked?1:0,
    min_advance_days:parseInt(document.getElementById('minAdvanceDays').value)||0,
    cancellation_policy:document.getElementById('cancellationPolicy').value.trim()
  },'bookingMsg',this);
});

// Notification preferences
document.getElementById('btnSaveNotif').addEventListener('click',function(){
  save({
    notification_prefs:{
      new_booking:document.getElementById('notifNewBooking').checked,
      payment_received:document.getElementById('notifPayment').checked,
      new_review:document.getElementById('notifReview').checked,
      publish_approved:document.getElementById('notifPublish').checked
    }
  },'notifMsg',this);
});

})();
</script>
<?php
};

?>
<!DOCTYPE html>
<html lang="en">
<head><?php require APPROOT . '/views/dashboardLayout/head.php'; ?></head>
<body class="grid h-screen grid-cols-[280px_1fr] gap-0 bg-app-page">
  <?php require APPROOT . '/views/dashboardLayout/suppliersidebar.php'; ?>
</body>
</html>
