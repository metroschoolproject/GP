<?php
$notifPrefs = $notification_prefs ?? ['booking_updates' => true, 'payment_updates' => true, 'replacement_updates' => true];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" type="image/png" href="<?= URLROOT ?>/public/images/home/gp_logo.png">
<title>Notification Settings — Golden Promise</title>
<?php $v = file_exists(APPROOT . '/../public/css/app.css') ? filemtime(APPROOT . '/../public/css/app.css') : time(); ?>
<link rel="stylesheet" href="<?= URLROOT ?>/public/css/app.css?v=<?= $v ?>">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
<script src="https://unpkg.com/lucide@latest"></script>
<style>
:root {
  --c-bg: #F4F1EE; --c-white: #FFFFFF; --c-text: #6d4c5b; --c-strong: #6d4c5b;
  --c-muted: #A8A29E; --c-rule: #ead8c7; --c-soft: #FAFAF9;
  --font-body: 'DM Sans', system-ui, sans-serif;
}
body { background:var(--c-bg); color:var(--c-text); font-family:var(--font-body); font-size:14px; line-height:1.6; -webkit-font-smoothing:antialiased; margin:0; }
* { box-sizing:border-box; }

/* Nav */
.gp-nav { display:flex; align-items:center; justify-content:space-between; padding:16px 32px; background:var(--c-white); border-bottom:1px solid var(--c-rule); }
.gp-nav-brand { font-family:'Playfair Display',serif; font-size:20px; font-weight:700; color:var(--c-strong); text-decoration:none; }
.gp-nav-links { display:flex; align-items:center; gap:8px; }
.gp-nav-link { display:inline-flex; align-items:center; gap:6px; padding:8px 14px; border-radius:10px; font-size:13px; font-weight:600; color:var(--c-muted); text-decoration:none; transition:all .2s; }
.gp-nav-link:hover { background:var(--c-soft); color:var(--c-text); }
.gp-nav-link.active { background:var(--c-strong); color:#fff; }

/* Layout */
.gp-wrap { max-width:640px; margin:0 auto; padding:32px 20px; }
.gp-title { font-family:'Playfair Display',serif; font-size:28px; font-weight:700; color:var(--c-strong); margin:0 0 4px; }
.gp-desc { font-size:13px; color:var(--c-muted); margin:0 0 24px; }

/* Card */
.gp-card { background:var(--c-white); border:1px solid var(--c-rule); border-radius:14px; overflow:hidden; }
.gp-card-head { padding:14px 20px; border-bottom:1px solid var(--c-rule); display:flex; align-items:center; gap:8px; font-size:14px; font-weight:700; color:var(--c-strong); }
.gp-card-head i { width:18px; height:18px; color:var(--c-muted); }
.gp-card-body { padding:8px 20px; }

/* Toggle row */
.gp-toggle { display:flex; align-items:center; justify-content:space-between; padding:14px 0; }
.gp-toggle + .gp-toggle { border-top:1px solid var(--c-rule); }
.gp-toggle-info { flex:1; padding-right:16px; }
.gp-toggle-label { font-size:13px; font-weight:700; color:var(--c-strong); }
.gp-toggle-desc { font-size:12px; color:var(--c-muted); margin-top:2px; }

/* Switch */
.s-switch { position:relative; width:44px; height:24px; flex-shrink:0; }
.s-switch input { opacity:0; width:0; height:0; }
.s-switch-slider { position:absolute; inset:0; background:var(--c-rule); border-radius:999px; cursor:pointer; transition:background .2s; }
.s-switch-slider::before { content:''; position:absolute; left:3px; top:3px; width:18px; height:18px; background:#fff; border-radius:50%; transition:transform .2s; box-shadow:0 1px 3px rgba(0,0,0,.1); }
.s-switch input:checked + .s-switch-slider { background:var(--c-strong); }
.s-switch input:checked + .s-switch-slider::before { transform:translateX(20px); }

/* Button */
.gp-btn { display:inline-flex; align-items:center; gap:7px; min-height:40px; padding:0 20px; border-radius:10px; font-size:13px; font-weight:600; cursor:pointer; transition:all .2s; border:none; font-family:var(--font-body); }
.gp-btn-primary { background:var(--c-strong); color:#fff; }
.gp-btn-primary:hover { opacity:.85; }
.gp-btn-outline { background:transparent; border:1px solid var(--c-rule); color:var(--c-text); }
.gp-btn-outline:hover { border-color:var(--c-strong); color:var(--c-strong); }
.gp-actions { display:flex; gap:8px; justify-content:flex-end; padding:14px 20px; border-top:1px solid var(--c-rule); }

/* Message */
.gp-msg { display:none; padding:10px 16px; font-size:12px; font-weight:600; border-radius:10px; margin-bottom:14px; }
.gp-msg.success { display:block; background:#ECFDF5; color:#065F46; }
.gp-msg.error { display:block; background:#FEF2F2; color:#991B1B; }

/* Back link */
.gp-back { display:inline-flex; align-items:center; gap:6px; font-size:13px; font-weight:600; color:var(--c-muted); text-decoration:none; margin-bottom:20px; transition:color .2s; }
.gp-back:hover { color:var(--c-text); }
</style>
</head>
<body>

<nav class="gp-nav">
  <a href="<?= URLROOT ?>" class="gp-nav-brand">Golden Promise</a>
  <div class="gp-nav-links">
    <a href="<?= URLROOT ?>/main/profile" class="gp-nav-link"><i data-lucide="user" style="width:16px;height:16px"></i> Profile</a>
    <a href="<?= URLROOT ?>/main/notificationSettings" class="gp-nav-link active"><i data-lucide="bell" style="width:16px;height:16px"></i> Notifications</a>
    <a href="<?= URLROOT ?>/main/bookings" class="gp-nav-link"><i data-lucide="calendar-check" style="width:16px;height:16px"></i> My Bookings</a>
  </div>
</nav>

<div class="gp-wrap">

  <a href="<?= URLROOT ?>/main/profile" class="gp-back"><i data-lucide="arrow-left" style="width:16px;height:16px"></i> Back to profile</a>

  <h1 class="gp-title">Notification Settings</h1>
  <p class="gp-desc">Choose which notifications you want to receive.</p>

  <div id="notifMsg" class="gp-msg"></div>

  <div class="gp-card">
    <div class="gp-card-head"><i data-lucide="bell"></i> Email Notifications</div>
    <div class="gp-card-body">

      <div class="gp-toggle">
        <div class="gp-toggle-info">
          <div class="gp-toggle-label">Booking updates</div>
          <div class="gp-toggle-desc">Get notified when your booking is confirmed, completed, or cancelled.</div>
        </div>
        <label class="s-switch">
          <input type="checkbox" id="notifBooking" <?= !empty($notifPrefs['booking_updates']) ? 'checked' : '' ?>>
          <span class="s-switch-slider"></span>
        </label>
      </div>

      <div class="gp-toggle">
        <div class="gp-toggle-info">
          <div class="gp-toggle-label">Payment confirmations</div>
          <div class="gp-toggle-desc">Get notified when your payment is verified and processed.</div>
        </div>
        <label class="s-switch">
          <input type="checkbox" id="notifPayment" <?= !empty($notifPrefs['payment_updates']) ? 'checked' : '' ?>>
          <span class="s-switch-slider"></span>
        </label>
      </div>

      <div class="gp-toggle">
        <div class="gp-toggle-info">
          <div class="gp-toggle-label">Replacement updates</div>
          <div class="gp-toggle-desc">Get notified when a supplier replacement is processed for your booking.</div>
        </div>
        <label class="s-switch">
          <input type="checkbox" id="notifReplacement" <?= !empty($notifPrefs['replacement_updates']) ? 'checked' : '' ?>>
          <span class="s-switch-slider"></span>
        </label>
      </div>

    </div>
    <div class="gp-actions">
      <a href="<?= URLROOT ?>/main/profile" class="gp-btn gp-btn-outline">Cancel</a>
      <button type="button" class="gp-btn gp-btn-primary" id="btnSaveNotif"><i data-lucide="save"></i> Save</button>
    </div>
  </div>

</div>

<script>
if (window.lucide) lucide.createIcons();

function showMsg(t,tp){var el=document.getElementById('notifMsg');el.textContent=t;el.className='gp-msg '+(tp||'');if(tp)setTimeout(function(){el.className='gp-msg';el.textContent='';},5000);}

document.getElementById('btnSaveNotif').addEventListener('click',function(){
  var btn=this;btn.disabled=true;var orig=btn.innerHTML;btn.innerHTML='<i data-lucide="loader"></i> Saving…';if(window.lucide)lucide.createIcons();
  fetch('<?= URLROOT ?>/main/updateNotificationSettings',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({
    notification_prefs:{
      booking_updates:document.getElementById('notifBooking').checked,
      payment_updates:document.getElementById('notifPayment').checked,
      replacement_updates:document.getElementById('notifReplacement').checked
    }
  })})
  .then(function(r){return r.json()}).then(function(d){
    btn.disabled=false;btn.innerHTML=orig;if(window.lucide)lucide.createIcons();
    if(d.ok)showMsg('✓ Settings saved.','success');else showMsg(d.error||'Failed.','error');
  }).catch(function(){btn.disabled=false;btn.innerHTML=orig;if(window.lucide)lucide.createIcons();showMsg('Network error.','error');});
});
</script>
<?php require APPROOT . '/views/layouts/customerFooter.php'; ?>
</body>
</html>
