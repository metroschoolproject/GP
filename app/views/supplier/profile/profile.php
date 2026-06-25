<?php
$userName      = $name       ?? $_SESSION['session_name'] ?? 'Supplier';
$firstName     = $first_name ?? '';
$lastName      = $last_name  ?? '';
$userEmail     = $email      ?? $_SESSION['session_email'] ?? '';
$userPhone     = $phone      ?? '';
$userAddress   = $address    ?? '';
$userJoined    = $joined     ?? '-';
$profileAvatar = $avatar     ?? $_SESSION['session_avatar'] ?? null;
$initials = strtoupper(substr(trim($userName), 0, 1));

$shopName      = $shop_name ?? '';
$description   = $description ?? '';
$status        = $status ?? 'pending';
$businessUrl   = $business_url ?? '';
$categoryNames = $category_names ?? '';
$paymentStatus     = $payment_status ?? 'unpaid';
$businessLicenseUrl = $business_license_url ?? null;
$serviceCount  = $service_count ?? 0;
$totalBookings = $total_bookings ?? 0;
$avgRating     = $avg_rating ?? null;

$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$statusLabel = ucfirst($status);
$statusBadgeClass = match (strtolower($status)) {
    'approved', 'verified' => 'p-badge-green',
    'pending' => 'p-badge-amber',
    'rejected', 'banned' => 'p-badge-red',
    default => 'p-badge-gray',
};

$dashboardTitle        = 'My Profile';
$dashboardCrumb        = 'Account';
$dashboardBreadcrumbs  = [
    ['label' => 'Dashboard', 'url' => URLROOT . '/supplier/dashboard'],
    ['label' => 'My Profile', 'url' => null],
];
$dashboardContentClass = 'profile-page px-6 py-6';

$dashboardContent = function () use (
    $userName, $firstName, $lastName, $userEmail, $userPhone, $userAddress,
    $userJoined, $profileAvatar, $initials, $h,
    $shopName, $description, $status, $businessUrl, $categoryNames,
    $paymentStatus, $businessLicenseUrl, $serviceCount, $totalBookings, $avgRating,
    $statusLabel, $statusBadgeClass
) {
?>
<style>
.profile-page { --ink:#6d4c5b; --muted:#A8A29E; --soft:#F4F1EE; --panel:#FFFFFF; --line:#ead8c7; --primary:#6d4c5b; color:var(--ink); }
.profile-page h1 { font-size:clamp(28px,3vw,42px); font-weight:900; margin:6px 0 7px; color:var(--ink); }
.profile-page .kicker { font-size:10px; font-weight:900; text-transform:uppercase; letter-spacing:.17em; color:var(--muted); }
.profile-page p.desc { color:var(--muted); font-size:13px; }

/* Header */
.p-header { background:var(--panel); border:1px solid var(--line); border-radius:14px; padding:22px 24px; display:flex; align-items:center; gap:20px; margin-bottom:16px; }
.p-avatar { width:72px; height:72px; border-radius:50%; background:var(--primary); color:#fff; font-size:28px; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; overflow:hidden; }
.p-avatar img { width:100%; height:100%; object-fit:cover; border-radius:50%; }
.p-header-info { flex:1; min-width:0; }
.p-header-name { font-size:22px; font-weight:800; color:var(--ink); margin:0; }
.p-header-meta { display:flex; align-items:center; gap:6px; flex-wrap:wrap; margin-top:4px; font-size:12px; color:var(--muted); }
.p-header-meta .p-dot { width:3px; height:3px; border-radius:50%; background:var(--muted); flex-shrink:0; }
.p-header-actions { display:flex; gap:8px; flex-shrink:0; }

/* Badge */
.p-badge { display:inline-flex; align-items:center; padding:2px 10px; border-radius:999px; font-size:10px; font-weight:800; letter-spacing:.05em; text-transform:uppercase; }
.p-badge-green { background:#ECFDF5; color:#065F46; }
.p-badge-amber { background:#FFFBEB; color:#92400E; }
.p-badge-red { background:#FEF2F2; color:#991B1B; }
.p-badge-gray { background:#F5F5F4; color:#57534E; }

/* Stats row */
.p-stats { display:grid; grid-template-columns:repeat(3,1fr); gap:12px; margin-bottom:16px; }
.p-stat { background:var(--panel); border:1px solid var(--line); border-radius:14px; padding:14px 18px; text-align:center; }
.p-stat-num { font-size:22px; font-weight:800; color:var(--ink); }
.p-stat-lbl { font-size:10px; font-weight:800; letter-spacing:.1em; text-transform:uppercase; color:var(--muted); margin-top:2px; }

/* License card */
.p-license { background:var(--panel); border:1px solid var(--line); border-radius:14px; padding:16px 20px; display:flex; align-items:center; gap:16px; margin-bottom:16px; flex-wrap:wrap; }
.p-license-icon { width:42px; height:42px; border-radius:10px; background:var(--soft); display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.p-license-icon i { width:20px; height:20px; color:var(--muted); }
.p-license-info { flex:1; min-width:0; }
.p-license-label { font-size:10px; font-weight:800; letter-spacing:.1em; text-transform:uppercase; color:var(--muted); margin-bottom:2px; }
.p-license-value { font-size:14px; font-weight:700; color:var(--ink); }
.p-license-status { display:flex; align-items:center; gap:6px; flex-shrink:0; }
.p-license-verified { display:inline-flex; align-items:center; gap:5px; padding:4px 12px; border-radius:999px; font-size:11px; font-weight:700; background:#ECFDF5; color:#065F46; }
.p-license-pending { display:inline-flex; align-items:center; gap:5px; padding:4px 12px; border-radius:999px; font-size:11px; font-weight:700; background:#FFFBEB; color:#92400E; }
.p-license-none { display:inline-flex; align-items:center; gap:5px; padding:4px 12px; border-radius:999px; font-size:11px; font-weight:700; background:var(--soft); color:var(--muted); }
.p-license-view { display:inline-flex; align-items:center; gap:5px; padding:6px 14px; border-radius:999px; border:1px solid var(--line); background:transparent; color:var(--ink); font-size:12px; font-weight:600; cursor:pointer; transition:all .2s; text-decoration:none; }
.p-license-view:hover { border-color:var(--primary); color:var(--primary); }
.p-license-view i { width:14px; height:14px; }

/* Grid */
.p-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; align-items:start; }
.p-col { display:flex; flex-direction:column; gap:16px; }

/* Card */
.p-card { background:var(--panel); border:1px solid var(--line); border-radius:14px; overflow:hidden; }
.p-card-head { padding:14px 18px; border-bottom:1px solid var(--line); display:flex; align-items:center; gap:8px; font-size:14px; font-weight:700; color:var(--ink); }
.p-card-head i { width:18px; height:18px; color:var(--muted); }
.p-card-body { padding:18px; }

/* Section title */
.p-section-title { font-size:12px; font-weight:700; color:var(--ink); margin:0 0 12px; padding-bottom:8px; border-bottom:1px solid var(--line); display:flex; align-items:center; gap:6px; }
.p-section-title i { width:15px; height:15px; color:var(--muted); }

/* Form */
.p-label { display:block; font-size:10px; font-weight:800; letter-spacing:.1em; text-transform:uppercase; color:var(--muted); margin-bottom:6px; }
.p-input { width:100%; height:44px; padding:0 14px; border:1px solid var(--line); border-radius:10px; background:var(--soft); color:var(--ink); font-size:13px; font-family:'DM Sans',sans-serif; transition:border-color .2s, box-shadow .2s, background .2s; box-sizing:border-box; }
.p-input:focus { outline:none; border-color:var(--primary); background:#fff; box-shadow:0 0 0 3px rgba(109,76,91,.08); }
textarea.p-input { height:auto; min-height:80px; padding:12px 14px; resize:vertical; }
.p-row { display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:12px; }
.p-row.single { grid-template-columns:1fr; }
.p-actions { display:flex; gap:8px; justify-content:flex-end; padding-top:14px; }

/* Password */
.pw-wrap { position:relative; display:flex; align-items:center; }
.pw-wrap input { padding-right:42px; }
.pw-eye { position:absolute; right:4px; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; padding:6px; color:var(--muted); opacity:.5; transition:opacity .2s; display:flex; align-items:center; border-radius:6px; line-height:0; }
.pw-eye:hover { opacity:1; background:var(--soft); }
.p-strength { display:flex; align-items:center; gap:8px; margin-bottom:14px; }
.p-str-bar { flex:1; height:3px; border-radius:999px; background:var(--line); transition:background .3s; }
.p-str-bar.active { background:var(--primary); }
.p-str-text { font-size:11px; color:var(--muted); white-space:nowrap; }

/* Buttons */
.p-btn { display:inline-flex; align-items:center; gap:7px; min-height:38px; padding:0 18px; border-radius:10px; font-size:13px; font-weight:600; cursor:pointer; transition:all .2s; border:none; font-family:'DM Sans',sans-serif; }
.p-btn-primary { background:var(--primary); color:#fff; }
.p-btn-primary:hover { opacity:.85; }
.p-btn-outline { background:transparent; border:1px solid var(--line); color:var(--ink); }
.p-btn-outline:hover { border-color:var(--primary); color:var(--primary); }
.p-btn-danger { background:#dc3545; color:#fff; }
.p-btn-danger:hover { background:#c82333; }
.p-btn-danger:disabled { opacity:.5; cursor:not-allowed; }
.p-btn-sm { min-height:34px; padding:0 14px; font-size:12px; }
.p-btn-icon { padding:0 10px; min-width:34px; justify-content:center; }

/* Messages */
.p-msg { display:none; padding:10px 14px; border-radius:10px; font-size:12px; font-weight:600; margin-bottom:12px; }
.p-msg.success { display:block; background:#ECFDF5; color:#065F46; }
.p-msg.error { display:block; background:#FEF2F2; color:#991B1B; }

/* Danger */
.p-danger { background:var(--panel); border:1px solid rgba(220,53,69,.2); border-radius:14px; padding:18px 22px; margin-top:16px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:14px; }
.p-danger-title { display:flex; align-items:center; gap:8px; font-size:14px; font-weight:700; color:#dc3545; margin-bottom:2px; }
.p-danger-title i { width:18px; height:18px; }
.p-danger-desc { font-size:12px; color:var(--muted); }

/* Modal */
.p-modal-bg { position:fixed; inset:0; background:rgba(0,0,0,.4); z-index:50; display:flex; align-items:center; justify-content:center; padding:16px; }
.p-modal { background:var(--panel); border-radius:16px; max-width:440px; width:100%; overflow:hidden; box-shadow:0 24px 60px rgba(0,0,0,.15); }
.p-modal-head { padding:14px 18px; border-bottom:1px solid var(--line); display:flex; align-items:center; justify-content:space-between; }
.p-modal-head h3 { font-size:15px; font-weight:700; color:var(--ink); margin:0; }
.p-modal-x { border:0; background:transparent; color:var(--muted); cursor:pointer; font-size:20px; line-height:1; }
.p-modal-body { padding:18px; }
.p-modal-tip { display:flex; gap:10px; align-items:flex-start; padding:12px 14px; border-radius:10px; background:#FFFBEB; border:1px solid #ead8c7; margin-bottom:14px; }
.p-modal-tip i { width:16px; height:16px; color:#92400e; flex-shrink:0; margin-top:1px; }
.p-modal-tip span { font-size:12px; color:#92400e; line-height:1.5; }
.p-modal-foot { display:flex; gap:8px; justify-content:flex-end; padding:0 18px 18px; }

@media(max-width:900px){
  .p-grid{grid-template-columns:1fr}
  .p-header{flex-direction:column;text-align:center}
  .p-header-meta{justify-content:center}
  .p-header-actions{justify-content:center}
  .p-stats{grid-template-columns:1fr}
  .p-row{grid-template-columns:1fr}
}
</style>

<section class="profile-page">

  <div style="margin-bottom:16px">
    <div class="kicker">Your Account</div>
    <h1>My Profile</h1>
    <p class="desc">Manage your shop information and account settings.</p>
  </div>

  <!-- Header -->
  <div class="p-header">
    <div class="p-avatar" id="profileAvatarFrame">
      <?php if (!empty($profileAvatar)): ?>
        <img src="<?= $h($profileAvatar) ?>" alt="<?= $h($shopName ?: $userName) ?>" id="profileAvatarImg">
      <?php else: ?>
        <span id="profileAvatarInner"><?= $initials ?></span>
      <?php endif; ?>
    </div>
    <div class="p-header-info">
      <p class="p-header-name"><?= $h($shopName ?: $userName) ?></p>
      <div class="p-header-meta">
        <span class="p-badge <?= $statusBadgeClass ?>"><?= $h($statusLabel) ?></span>
        <span class="p-dot"></span>
        <span><?= $h($userEmail) ?></span>
        <span class="p-dot"></span>
        <span>Joined <?= $h($userJoined) ?></span>
        <?php if ($categoryNames): ?>
          <span class="p-dot"></span>
          <span><?= $h($categoryNames) ?></span>
        <?php endif; ?>
      </div>
    </div>
    <div class="p-header-actions">
      <input type="file" id="profilePhotoInput" accept="image/jpeg,image/png,image/webp" style="display:none">
      <button type="button" class="p-btn p-btn-outline p-btn-sm" id="btnChangePhoto">
        <i data-lucide="camera"></i>
        <span id="btnPhotoLabel"><?= empty($profileAvatar) ? 'Add Photo' : 'Change' ?></span>
      </button>
      <?php if (!empty($profileAvatar)): ?>
        <button type="button" class="p-btn p-btn-outline p-btn-sm p-btn-icon" id="btnRemovePhoto">
          <i data-lucide="trash-2"></i>
        </button>
      <?php endif; ?>
    </div>
  </div>

  <!-- Stats -->
  <div class="p-stats">
    <div class="p-stat">
      <div class="p-stat-num"><?= (int)$serviceCount ?></div>
      <div class="p-stat-lbl">Services</div>
    </div>
    <div class="p-stat">
      <div class="p-stat-num"><?= (int)$totalBookings ?></div>
      <div class="p-stat-lbl">Bookings</div>
    </div>
    <div class="p-stat">
      <div class="p-stat-num"><?= $avgRating ? number_format((float)$avgRating, 1) : '—' ?></div>
      <div class="p-stat-lbl">Rating</div>
    </div>
  </div>

  <!-- Business License -->
  <div class="p-license">
    <div class="p-license-icon"><i data-lucide="file-badge"></i></div>
    <div class="p-license-info">
      <div class="p-license-label">Business License</div>
      <?php if (!empty($businessLicenseUrl)): ?>
        <div class="p-license-value">Document on file</div>
      <?php else: ?>
        <div class="p-license-value" style="color:var(--muted)">No license uploaded</div>
      <?php endif; ?>
    </div>
    <div class="p-license-status">
      <?php if (!empty($businessLicenseUrl)): ?>
        <?php if (in_array(strtolower($status), ['approved', 'verified'])): ?>
          <span class="p-license-verified"><i data-lucide="check-circle" style="width:14px;height:14px"></i> Verified</span>
        <?php else: ?>
          <span class="p-license-pending"><i data-lucide="clock" style="width:14px;height:14px"></i> Pending Review</span>
        <?php endif; ?>
        <a href="<?= URLROOT ?>/<?= $h($businessLicenseUrl) ?>" target="_blank" class="p-license-view"><i data-lucide="external-link"></i> View</a>
      <?php else: ?>
        <span class="p-license-none"><i data-lucide="alert-circle" style="width:14px;height:14px"></i> Not uploaded</span>
      <?php endif; ?>
    </div>
  </div>

  <!-- Two Column -->
  <div class="p-grid">

    <!-- Shop & Personal -->
    <div class="p-card">
      <div class="p-card-head"><i data-lucide="store"></i> Shop &amp; Personal Information</div>
      <div class="p-card-body">
        <div id="profileSaveMsg" class="p-msg"></div>

        <div class="p-section-title"><i data-lucide="building-2"></i> Business</div>
        <div class="p-row single">
          <div>
            <label class="p-label">Shop Name</label>
            <input id="profileShopName" class="p-input" type="text" value="<?= $h($shopName) ?>">
          </div>
        </div>
        <div class="p-row single">
          <div>
            <label class="p-label">Description</label>
            <textarea id="profileDescription" class="p-input"><?= $h($description) ?></textarea>
          </div>
        </div>
        <div class="p-row single">
          <div>
            <label class="p-label">Business URL</label>
            <input id="profileBusinessUrl" class="p-input" type="url" value="<?= $h($businessUrl) ?>" placeholder="https://">
          </div>
        </div>

        <div class="p-section-title" style="margin-top:16px"><i data-lucide="user"></i> Owner</div>
        <div class="p-row">
          <div>
            <label class="p-label">First Name</label>
            <input id="profileFirstName" class="p-input" type="text" value="<?= $h($firstName) ?>">
          </div>
          <div>
            <label class="p-label">Last Name</label>
            <input id="profileLastName" class="p-input" type="text" value="<?= $h($lastName) ?>">
          </div>
        </div>
        <div class="p-row single">
          <div>
            <label class="p-label">Email Address</label>
            <input id="profileEmail" class="p-input" type="email" value="<?= $h($userEmail) ?>">
          </div>
        </div>
        <div class="p-row">
          <div>
            <label class="p-label">Phone</label>
            <input id="profilePhone" class="p-input" type="tel" value="<?= $h($userPhone) ?>">
          </div>
          <div>
            <label class="p-label">Address</label>
            <input id="profileAddress" class="p-input" type="text" value="<?= $h($userAddress) ?>">
          </div>
        </div>
        <div class="p-actions">
          <button type="button" class="p-btn p-btn-outline" id="btnCancelProfile">Cancel</button>
          <button type="button" class="p-btn p-btn-primary" id="btnSaveProfile"><i data-lucide="save"></i> Save Changes</button>
        </div>
      </div>
    </div>

    <!-- Change Password -->
    <div class="p-col">
      <div class="p-card">
        <div class="p-card-head"><i data-lucide="lock"></i> Change Password</div>
        <div class="p-card-body">
          <div id="profilePwMsg" class="p-msg"></div>

          <div class="p-row single">
            <div>
              <label class="p-label">Current Password</label>
              <div class="pw-wrap">
                <input id="profileCurrentPw" class="p-input" type="password" autocomplete="current-password" minlength="8" required>
                <button type="button" class="pw-eye" data-target="profileCurrentPw"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
              </div>
            </div>
          </div>

          <div class="p-row single">
            <div>
              <label class="p-label">New Password</label>
              <div class="pw-wrap">
                <input id="profileNewPw" class="p-input" type="password" autocomplete="new-password" minlength="8" required>
                <button type="button" class="pw-eye" data-target="profileNewPw"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
              </div>
            </div>
          </div>

          <div id="profilePwStrength" class="p-strength" style="display:none">
            <span class="p-str-bar" id="seg1"></span>
            <span class="p-str-bar" id="seg2"></span>
            <span class="p-str-bar" id="seg3"></span>
            <span class="p-str-bar" id="seg4"></span>
            <span class="p-str-text" id="strengthText">Weak</span>
          </div>

          <div class="p-row single">
            <div>
              <label class="p-label">Confirm New Password</label>
              <div class="pw-wrap">
                <input id="profileConfirmPw" class="p-input" type="password" autocomplete="new-password" minlength="8" required>
                <button type="button" class="pw-eye" data-target="profileConfirmPw"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
              </div>
            </div>
          </div>
          <p id="profilePwMatchHint" style="display:none;font-size:12px;color:#dc3545;margin:-4px 0 8px">Passwords do not match.</p>

          <div class="p-actions">
            <button type="button" class="p-btn p-btn-primary" id="btnUpdatePw"><i data-lucide="key"></i> Update Password</button>
          </div>
        </div>
      </div>

      <!-- Danger Zone -->
      <div class="p-danger">
        <div>
          <div class="p-danger-title"><i data-lucide="alert-triangle"></i> Danger Zone</div>
          <div class="p-danger-desc">Delete your account and all associated data permanently.</div>
        </div>
        <button type="button" class="p-btn p-btn-danger" id="btnOpenDeleteModal"><i data-lucide="trash-2"></i> Delete Account</button>
      </div>
    </div>

  </div>
</section>

<!-- Delete Modal -->
<div id="deleteAccountModal" class="p-modal-bg" style="display:none">
  <div class="p-modal">
    <div class="p-modal-head">
      <h3>Delete Account</h3>
      <button type="button" id="btnCloseDeleteModal" class="p-modal-x">&times;</button>
    </div>
    <div class="p-modal-body">
      <div class="p-modal-tip">
        <i data-lucide="alert-triangle"></i>
        <span>This will <strong>permanently deactivate</strong> your supplier account. Your services will be hidden and booking records preserved.</span>
      </div>
      <div style="margin-bottom:12px">
        <label class="p-label">Confirm with your password</label>
        <input id="deleteAccountPw" class="p-input" type="password" placeholder="Enter your password" autocomplete="current-password">
      </div>
      <div style="margin-bottom:12px">
        <label class="p-label">Type <strong style="color:#dc3545">DELETE</strong> to confirm</label>
        <input id="deleteAccountConfirm" class="p-input" type="text" placeholder="Type DELETE" autocomplete="off">
      </div>
      <div id="deleteAccountMsg" style="min-height:18px;font-size:12px;margin-bottom:10px"></div>
      <div class="p-modal-foot" style="padding:0">
        <button type="button" id="btnCancelDelete" class="p-btn p-btn-outline">Cancel</button>
        <button type="button" id="btnConfirmDelete" class="p-btn p-btn-danger" disabled>Delete my account</button>
      </div>
    </div>
  </div>
</div>

<script>
(function(){
'use strict';
if (window.lucide) lucide.createIcons();

var eyeOpen='<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
var eyeShut='<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10 10 0 0 1 12 20c-7 0-11-8-11-8a18 18 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9 9 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>';
document.querySelectorAll('.pw-eye').forEach(function(btn){
  btn.addEventListener('click',function(){
    var inp=document.getElementById(this.getAttribute('data-target'));
    if(!inp)return;var s=inp.type==='password';inp.type=s?'text':'password';this.innerHTML=s?eyeShut:eyeOpen;
  });
});

var newPw=document.getElementById('profileNewPw');
var meter=document.getElementById('profilePwStrength');
var strText=document.getElementById('strengthText');
var segs=['seg1','seg2','seg3','seg4'];
var resetStr=function(){segs.forEach(function(id){document.getElementById(id).classList.remove('active')});strText.textContent='Weak';};

newPw.addEventListener('input',function(){
  var v=newPw.value;if(v.trim()===''){meter.style.display='none';resetStr();return;}meter.style.display='flex';
  var s=0;if(v.length>=8)s++;if(/[A-Z]/.test(v))s++;if(/[0-9]/.test(v))s++;if(/[^A-Za-z0-9]/.test(v))s++;
  segs.forEach(function(id,i){document.getElementById(id).classList.toggle('active',i<s)});
  strText.textContent=s<=1?'Weak':s===2?'Fair':s===3?'Good':'Strong';
});

var cfm=document.getElementById('profileConfirmPw');
var hint=document.getElementById('profilePwMatchHint');
var chk=function(){var p=newPw.value,c=cfm.value;if(c===''){hint.style.display='none';cfm.style.borderColor='';return;}
  if(p!==c){hint.style.display='block';cfm.style.borderColor='#dc3545';}else{hint.style.display='none';cfm.style.borderColor='#16a34a';}};
cfm.addEventListener('input',chk);newPw.addEventListener('input',function(){if(cfm.value!=='')chk();});

function msg(id,t,tp){var el=document.getElementById(id);if(!el)return;el.textContent=t;el.className='p-msg '+(tp||'');if(tp)setTimeout(function(){el.className='p-msg';el.textContent='';},5000);}

// Save profile
var btnSave=document.getElementById('btnSaveProfile');
document.getElementById('btnCancelProfile').addEventListener('click',function(){
  document.getElementById('profileFirstName').value='<?= $h($firstName) ?>';
  document.getElementById('profileLastName').value='<?= $h($lastName) ?>';
  document.getElementById('profileEmail').value='<?= $h($userEmail) ?>';
  document.getElementById('profilePhone').value='<?= $h($userPhone) ?>';
  document.getElementById('profileAddress').value='<?= $h($userAddress) ?>';
  document.getElementById('profileShopName').value='<?= $h($shopName) ?>';
  document.getElementById('profileDescription').value='<?= $h($description) ?>';
  document.getElementById('profileBusinessUrl').value='<?= $h($businessUrl) ?>';
  msg('profileSaveMsg','','');
});
btnSave.addEventListener('click',function(){
  btnSave.disabled=true;btnSave.innerHTML='<i data-lucide="loader"></i> Saving…';if(window.lucide)lucide.createIcons();
  fetch('<?= URLROOT ?>/supplier/updateProfile',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({
    name:(document.getElementById('profileFirstName').value.trim()+' '+document.getElementById('profileLastName').value.trim()).trim(),
    email:document.getElementById('profileEmail').value.trim(),
    phone:document.getElementById('profilePhone').value.trim(),
    address:document.getElementById('profileAddress').value.trim(),
    shop_name:document.getElementById('profileShopName').value.trim(),
    description:document.getElementById('profileDescription').value.trim(),
    business_url:document.getElementById('profileBusinessUrl').value.trim()
  })})
  .then(function(r){return r.json()}).then(function(d){btnSave.disabled=false;btnSave.innerHTML='<i data-lucide="save"></i> Save Changes';if(window.lucide)lucide.createIcons();
    if(d.ok)msg('profileSaveMsg','✓ Profile updated.','success');else msg('profileSaveMsg',d.error||'Failed.','error');})
  .catch(function(){btnSave.disabled=false;btnSave.innerHTML='<i data-lucide="save"></i> Save Changes';if(window.lucide)lucide.createIcons();msg('profileSaveMsg','Network error.','error');});
});

// Update password
document.getElementById('btnUpdatePw').addEventListener('click',function(){
  var cur=document.getElementById('profileCurrentPw').value,np=newPw.value,cf=cfm.value;
  [document.getElementById('profileCurrentPw'),newPw,cfm].forEach(function(e){e.style.borderColor='';});
  hint.style.display='none';msg('profilePwMsg','','');
  var ok=true;
  if(cur.trim()===''){document.getElementById('profileCurrentPw').style.borderColor='#dc3545';ok=false;}
  if(np.length<8){newPw.style.borderColor='#dc3545';ok=false;}
  if(np!==cf){cfm.style.borderColor='#dc3545';hint.style.display='block';ok=false;}
  if(!ok)return;
  var btn=document.getElementById('btnUpdatePw');btn.disabled=true;btn.innerHTML='<i data-lucide="loader"></i> Updating…';if(window.lucide)lucide.createIcons();
  fetch('<?= URLROOT ?>/supplier/updatePassword',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({current_password:cur,new_password:np,device:navigator.userAgent||'Unknown'})})
  .then(function(r){return r.json()}).then(function(d){btn.disabled=false;btn.innerHTML='<i data-lucide="key"></i> Update Password';if(window.lucide)lucide.createIcons();
    if(d.ok){document.getElementById('profileCurrentPw').value='';newPw.value='';cfm.value='';meter.style.display='none';resetStr();msg('profilePwMsg','✓ Password updated!','success');}
    else{msg('profilePwMsg',d.error||'Failed.','error');}}).catch(function(){btn.disabled=false;btn.innerHTML='<i data-lucide="key"></i> Update Password';if(window.lucide)lucide.createIcons();msg('profilePwMsg','Network error.','error');});
});

// Photo
var photoIn=document.getElementById('profilePhotoInput');
var btnP=document.getElementById('btnChangePhoto');
var btnR=document.getElementById('btnRemovePhoto');
var pLabel=document.getElementById('btnPhotoLabel');
var avFrame=document.getElementById('profileAvatarFrame');

function setAv(url){
  var img=document.getElementById('profileAvatarImg');
  if(img){img.src=url;}else{
    var sp=document.getElementById('profileAvatarInner');if(sp)sp.remove();
    var n=document.createElement('img');n.id='profileAvatarImg';n.src=url;n.alt='Photo';n.style.cssText='width:100%;height:100%;object-fit:cover;border-radius:50%';avFrame.appendChild(n);
  }pLabel.textContent='Change';
  if(!document.getElementById('btnRemovePhoto')){
    var rm=document.createElement('button');rm.type='button';rm.id='btnRemovePhoto';rm.className='p-btn p-btn-outline p-btn-sm p-btn-icon';rm.innerHTML='<i data-lucide="trash-2"></i>';rm.addEventListener('click',removePhoto);btnP.parentNode.appendChild(rm);if(window.lucide)lucide.createIcons();
  }
}
btnP.addEventListener('click',function(){photoIn.click()});
photoIn.addEventListener('change',function(){
  var f=photoIn.files[0];if(!f)return;var fd=new FormData();fd.append('profile_photo',f);
  btnP.disabled=true;pLabel.textContent='Uploading…';
  fetch('<?= URLROOT ?>/supplier/uploadProfilePhoto',{method:'POST',body:fd})
  .then(function(r){return r.json()}).then(function(d){btnP.disabled=false;if(d.ok){setAv(d.url+'?t='+Date.now());if(window.lucide)lucide.createIcons();}else{pLabel.textContent='Add Photo';alert(d.error||'Failed.');}})
  .catch(function(){btnP.disabled=false;pLabel.textContent='Add Photo';alert('Network error.');});photoIn.value='';
});
function removePhoto(){
  if(!confirm('Remove photo?'))return;
  fetch('<?= URLROOT ?>/supplier/removeProfilePhoto',{method:'POST',headers:{'Content-Type':'application/json'}})
  .then(function(r){return r.json()}).then(function(d){if(d.ok){var img=document.getElementById('profileAvatarImg');if(img)img.remove();var s=document.createElement('span');s.id='profileAvatarInner';s.textContent='<?= $initials ?>';s.style.cssText='font-size:28px;font-weight:700';avFrame.appendChild(s);pLabel.textContent='Add Photo';var rm=document.getElementById('btnRemovePhoto');if(rm)rm.remove();if(window.lucide)lucide.createIcons();}else alert(d.error||'Failed.');})
  .catch(function(){alert('Network error.');});
}
if(btnR)btnR.addEventListener('click',removePhoto);

// Delete modal
var dm=document.getElementById('deleteAccountModal');
var dp=document.getElementById('deleteAccountPw');
var dc=document.getElementById('deleteAccountConfirm');
var db=document.getElementById('btnConfirmDelete');
var dmsg=document.getElementById('deleteAccountMsg');
document.getElementById('btnOpenDeleteModal').addEventListener('click',function(){dm.style.display='flex';dp.value='';dc.value='';dmsg.textContent='';db.disabled=true;dp.focus();});
function cDel(){dm.style.display='none';dp.value='';dc.value='';dmsg.textContent='';}
document.getElementById('btnCloseDeleteModal').addEventListener('click',cDel);
document.getElementById('btnCancelDelete').addEventListener('click',cDel);
dm.addEventListener('click',function(e){if(e.target===dm)cDel();});
document.addEventListener('keydown',function(e){if(e.key==='Escape'&&dm.style.display==='flex')cDel();});
function chkD(){db.disabled=!(dp.value.trim()&&dc.value.trim().toUpperCase()==='DELETE');}
dp.addEventListener('input',chkD);dc.addEventListener('input',chkD);
db.addEventListener('click',function(){db.disabled=true;db.textContent='Deleting…';dmsg.textContent='';
  fetch('<?= URLROOT ?>/supplier/deleteAccount',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({password:dp.value})})
  .then(function(r){return r.json()}).then(function(d){if(d.ok){dmsg.textContent='Deleted. Redirecting…';dmsg.style.color='#065F46';setTimeout(function(){window.location.href=d.redirect||'<?= URLROOT ?>/';},1200);}else{dmsg.textContent=d.error||'Failed.';dmsg.style.color='#dc3545';db.disabled=false;db.textContent='Delete my account';}})
  .catch(function(){dmsg.textContent='Network error.';dmsg.style.color='#dc3545';db.disabled=false;db.textContent='Delete my account';});
});

})();
</script>
<?php
};

?>
<?php
$supplier = [
    'shop_name'   => $shopName,
    'owner_email' => $userEmail,
    'status'      => $status,
    'payment_status' => $paymentStatus,
    'is_available' => true,
];
$dashboardData = [
    'stats' => [
        'total_services'  => $serviceCount,
        'total_bookings'  => $totalBookings,
        'pending_bookings' => 0,
    ],
];
?>
<!DOCTYPE html>
<html lang="en">
<head><?php require APPROOT . '/views/dashboardLayout/head.php'; ?></head>
<body class="grid h-screen grid-cols-[280px_1fr] gap-0 bg-app-page">
  <?php require APPROOT . '/views/dashboardLayout/suppliersidebar.php'; ?>
</body>
</html>
