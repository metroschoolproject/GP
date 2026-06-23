<?php
/**
 * ADMIN PROFILE — LAYOUT 1: "Elegant Rose"
 *
 * A romantic two-column layout with soft rose-gold tones,
 * Playfair Display headings, a decorative diamond divider,
 * and photo-centric identity card.
 * Feels like a luxury wedding stationery suite.
 */

// ---- Data from controller (extracted by view()) ----
$userName    = $name       ?? $_SESSION['session_name'] ?? 'Admin User';
$firstName   = $first_name ?? '';
$lastName    = $last_name  ?? '';
$userEmail   = $email      ?? $_SESSION['session_email'] ?? '';
$userRole    = $role       ?? 'Administrator';
$userPhone   = $phone      ?? '';
$userJoined  = $joined     ?? '-';
$userLastLogin = $lastLogin  ?? '-';
$userTimezone  = $timezone   ?? 'Asia/Yangon';
$profileAvatar = $avatar     ?? $_SESSION['session_avatar'] ?? null;
$initials = strtoupper(substr(trim($userName), 0, 1));
$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');

$dashboardTitle        = 'My Profile';
$dashboardCrumb        = 'Account';
$dashboardContentClass = 'elegant-rose-profile';

$dashboardContent = function () use ($userName, $firstName, $lastName, $userEmail, $userRole, $userPhone, $userJoined, $userLastLogin, $userTimezone, $profileAvatar, $initials, $h) {
?>
<style>
/* ===== IMPORT WEDDING FONTS ===== */
@import url('https://fonts.googleapis.com/css2?family=Great+Vibes&family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500&family=Poppins:wght@300;400;500;600;700&display=swap');

/* ===== TOKENS ===== */
.elegant-rose-profile {
    --rose:        #9b5d73;
    --rose-deep:   #673049;
    --rose-soft:   #f7edf2;
    --rose-petal:  #fdf2f6;
    --gold:        #c9a96e;
    --gold-soft:   #fdf8f0;
    --champagne:   #faf3e5;
    --cream:       #fbf7f1;
    --warm-white:  #fffdf9;
    --warm-bg:     #f5e8d9;
    --ink:         #2c2420;
    --ink-soft:    #5c4f48;
    --muted:       #ab9589;
    --border:      #e5d5c3;
    --border-light:#f0e5d7;
    --success:     #5b8c5a;
    --success-bg:  #eaf5ea;

    min-height: 100vh;
    background:
        radial-gradient(ellipse at 20% 10%, rgba(201,169,110,0.10) 0%, transparent 50%),
        radial-gradient(ellipse at 80% 90%, rgba(155,93,115,0.07) 0%, transparent 50%),
        linear-gradient(180deg, #fbf7f1 0%, #f5e8d9 100%);
    font-family: 'Poppins', sans-serif;
    font-size: 13px; color: var(--ink);
    padding: 32px 36px;
}
.elegant-rose-profile * { box-sizing: border-box; }

/* ===== PAGE HEADER ===== */
.page-header {
    text-align: center; margin-bottom: 32px;
}
.page-header .eyebrow {
    font-family: 'Poppins', sans-serif;
    font-size: 10px; font-weight: 700; letter-spacing: .22em;
    text-transform: uppercase; color: var(--muted);
    margin-bottom: 8px;
}
.page-header h1 {
    font-family: 'Playfair Display', serif;
    font-size: 36px; font-weight: 600; color: var(--ink);
    margin: 0 0 4px; letter-spacing: -.3px;
}
.page-header .subtitle {
    font-family: 'Great Vibes', cursive;
    font-size: 22px; color: var(--rose); margin: 0;
    font-weight: 400;
}

/* ===== DECORATIVE DIAMOND DIVIDER ===== */
.diamond-divider {
    display: flex; align-items: center; justify-content: center;
    gap: 16px; margin: 0 auto 32px; max-width: 400px;
}
.diamond-divider::before,
.diamond-divider::after {
    content: ''; flex: 1; height: 1px;
    background: linear-gradient(90deg, transparent, var(--border), transparent);
}
.diamond {
    width: 10px; height: 10px; border: 1.5px solid var(--rose);
    transform: rotate(45deg); flex-shrink: 0;
    opacity: .55;
}

/* ===== TWO-COLUMN LAYOUT ===== */
.profile-grid {
    display: grid; grid-template-columns: 340px 1fr;
    gap: 28px; align-items: start; max-width: 1100px; margin: 0 auto;
}

/* ===== CARD BASE ===== */
.card {
    background: var(--warm-white);
    border: 1px solid var(--border);
    border-radius: 1.25rem;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(44,36,32,.04), 0 8px 24px rgba(155,93,115,.06);
    transition: box-shadow .25s;
}
.card:hover { box-shadow: 0 1px 3px rgba(44,36,32,.04), 0 12px 32px rgba(155,93,115,.10); }
.card-head {
    padding: 18px 22px; border-bottom: 1px solid var(--border-light);
    font-family: 'Playfair Display', serif;
    font-size: 16px; font-weight: 600; color: var(--ink);
    display: flex; align-items: center; gap: 10px;
    background: linear-gradient(180deg, var(--warm-white), #fffaf5);
}
.card-head i { width: 20px; height: 20px; color: var(--rose); }
.card-body { padding: 22px; }

/* ===== IDENTITY CARD ===== */
.identity-card { text-align: center; }
.identity-card .card-body { padding: 32px 22px; }
.avatar-frame {
    width: 112px; height: 112px; border-radius: 50%;
    margin: 0 auto 18px; position: relative;
    padding: 4px;
    background: linear-gradient(135deg, var(--rose), var(--gold), var(--rose));
}
.avatar-inner {
    width: 100%; height: 100%; border-radius: 50%;
    background: linear-gradient(145deg, var(--rose-deep), var(--rose));
    color: #fcf8f5; font-family: 'Playfair Display', serif;
    font-size: 40px; font-weight: 500;
    display: flex; align-items: center; justify-content: center;
    letter-spacing: -2px; user-select: none;
    box-shadow: inset 0 2px 8px rgba(252,248,245,.15);
}
.identity-card .name {
    font-family: 'Playfair Display', serif;
    font-size: 22px; font-weight: 600; color: var(--ink);
    margin: 0 0 4px;
}
.identity-card .role-tag {
    display: inline-block;
    font-family: 'Great Vibes', cursive;
    font-size: 20px; color: var(--rose); margin-bottom: 20px;
}
/* Avatar with real photo */
.avatar-img {
    width: 100%; height: 100%; border-radius: 50%;
    object-fit: cover; display: block;
    box-shadow: inset 0 2px 8px rgba(252,248,245,.15);
}
.id-meta { text-align: left; }
.id-meta .meta-row {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 0; font-size: 12px; color: var(--ink-soft);
}
.id-meta .meta-row + .meta-row { border-top: 1px solid var(--border-light); }
.id-meta i { width: 15px; height: 15px; color: var(--muted); flex-shrink: 0; }
.id-meta .meta-val { margin-left: auto; font-weight: 500; color: var(--ink); text-align: right; }
.id-stats { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 18px; padding-top: 18px; border-top: 1px solid var(--border-light); }
.id-stat {
    text-align: center; padding: 12px 8px; border-radius: .75rem;
    background: linear-gradient(135deg, var(--rose-soft), var(--rose-petal));
}
.id-stat .num { font-family: 'Playfair Display', serif; font-size: 24px; font-weight: 700; color: var(--rose-deep); }
.id-stat .lbl { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--rose); margin-top: 4px; }

/* ===== FORM ===== */
.form-section { margin-bottom: 24px; }
.form-section:last-child { margin-bottom: 0; }
.form-section-title {
    font-family: 'Playfair Display', serif;
    font-size: 15px; font-weight: 600; color: var(--ink);
    margin: 0 0 16px; padding-bottom: 10px;
    border-bottom: 1px solid var(--border-light);
    display: flex; align-items: center; gap: 8px;
}
.form-section-title i { width: 17px; height: 17px; color: var(--rose); }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px; }
.form-row.single { grid-template-columns: 1fr; }
.form-label {
    display: block; font-size: 10px; font-weight: 700; letter-spacing: .1em;
    text-transform: uppercase; color: var(--muted); margin-bottom: 6px;
}
.form-input {
    width: 100%; height: 46px; padding: 0 16px;
    border: 1px solid var(--border); border-radius: .85rem;
    background: var(--cream); color: var(--ink); font-size: 13px;
    font-family: 'Poppins', sans-serif;
    transition: border-color .15s, box-shadow .15s, background .15s;
}
.form-input:focus {
    outline: none; border-color: var(--rose); background: #fcf8f5;
    box-shadow: 0 0 0 4px rgba(155,93,115,.06);
}
textarea.form-input { height: auto; min-height: 90px; padding: 14px 16px; resize: vertical; }

/* ===== BUTTONS ===== */
.btn {
    display: inline-flex; align-items: center; gap: 7px;
    height: 44px; padding: 0 22px; border-radius: .85rem;
    font-family: 'Poppins', sans-serif; font-size: 13px; font-weight: 600;
    cursor: pointer; transition: all .15s; border: none;
}
.btn-primary {
    background: linear-gradient(135deg, var(--rose-deep), var(--rose));
    color: #fcf8f5; box-shadow: 0 2px 8px rgba(103,48,73,.25);
}
.btn-primary:hover {
    box-shadow: 0 4px 16px rgba(103,48,73,.35);
    transform: translateY(-1px);
}
.btn-outline {
    background: transparent; border: 1px solid var(--border); color: var(--ink-soft);
}
.btn-outline:hover { border-color: var(--rose); color: var(--rose); background: var(--rose-petal); }
.btn-outline-gold {
    background: transparent; border: 1px solid var(--gold); color: #8b6d3f;
}
.btn-outline-gold:hover { background: var(--gold-soft); border-color: #b8943d; }
.btn-danger {
    background: #dc3545; color: #fff; border: 1px solid #dc3545;
}
.btn-danger:hover { background: #c82333; border-color: #bd2130; box-shadow: 0 4px 12px rgba(220,53,69,.3); }
.btn-danger:disabled { opacity: .55; cursor: not-allowed; transform: none; box-shadow: none; }
.form-actions { display: flex; gap: 10px; justify-content: flex-end; padding-top: 18px; }

/* ===== DECORATED INPUT (floating label + eye toggle) ===== */
.decorated-input {
    position: relative; width: 100%;
}
.decorated-input input {
    width: 100%; height: 58px; padding: 20px 50px 12px 18px;
    border: 1px solid var(--border); border-radius: .9rem;
    background: var(--cream); color: var(--ink); font-size: 15px;
    font-family: 'Poppins', sans-serif;
    outline: none; transition: border-color .2s, box-shadow .2s, background .2s;
    box-shadow: 4px 4px 8px rgba(44,36,32,.06);
}
.decorated-input input::placeholder { color: transparent; }
.decorated-input label {
    position: absolute;
    left: 18px; top: 50%; transform: translateY(-50%);
    font-size: 13px; color: var(--muted);
    pointer-events: none; transition: all .2s ease;
    font-family: 'Poppins', sans-serif;
}
.decorated-input:hover label,
.decorated-input input:focus + label,
.decorated-input input:not(:placeholder-shown) + label {
    top: 6px; transform: translateY(0);
    font-size: 11px; color: var(--rose);
    font-weight: 600; letter-spacing: .3px;
}
.decorated-input input:focus {
    border-color: var(--rose);
    box-shadow: 0 0 0 4px rgba(155,93,115,.08), 4px 4px 8px rgba(44,36,32,.06);
    background: #fcf8f5;
}
.decorated-input:hover input { border-color: rgba(155,93,115,.45); }

/* ===== PASSWORD INPUT WITH EYE TOGGLE ===== */
.pw-input-wrap {
    position: relative; display: flex; align-items: center;
}
.pw-input-wrap .pw-input {
    padding-right: 46px;
}
.pw-eye {
    position: absolute; right: 6px; top: 50%;
    transform: translateY(-50%);
    background: none; border: none; cursor: pointer;
    padding: 6px; color: var(--rose); opacity: .45;
    transition: opacity .2s; display: flex;
    align-items: center; justify-content: center; line-height: 0;
    border-radius: .4rem;
}
.pw-eye:hover { opacity: 1; background: var(--rose-soft); }
.pw-eye svg { display: block; pointer-events: none; }

/* ===== INLINE FEEDBACK MESSAGE ===== */
.profile-msg {
    display: none; padding: 10px 14px; border-radius: .75rem; font-size: 12px; font-weight: 500; margin-bottom: 14px;
}
.profile-msg.success { display: block; background: #eaf5ea; color: #5b8c5a; }
.profile-msg.error   { display: block; background: #fde8ec; color: #b8404a; }

/* ===== PASSWORD STRENGTH METER ===== */
.strength-seg {
    flex: 1; height: 3px; border-radius: 999px;
    background: rgba(155,93,115,.12);
    transition: background .3s;
}
.strength-seg.active { background: var(--rose); }

@media (max-width: 860px) {
    .profile-grid { grid-template-columns: 1fr; }
    .form-row { grid-template-columns: 1fr; }
}
</style>

<div class="elegant-rose-profile">

    <div class="page-header">
        <div class="eyebrow">Your Account</div>
        <h1><?= $h($userName) ?></h1>
        <p class="subtitle">Administrator</p>
    </div>

    <div class="diamond-divider"><div class="diamond"></div></div>

    <div class="profile-grid">

        <!-- ===== LEFT: IDENTITY CARD ===== -->
        <div>
            <div class="card identity-card">
                <div class="card-body">
                    <div class="avatar-frame" id="profileAvatarFrame">
                        <?php if (!empty($profileAvatar)): ?>
                            <img src="<?= $h($profileAvatar) ?>"
                                 alt="<?= $h($userName) ?>"
                                 class="avatar-img"
                                 id="profileAvatarImg">
                        <?php else: ?>
                            <div class="avatar-inner" id="profileAvatarInner"><?= $initials ?></div>
                        <?php endif; ?>
                    </div>
                    <p class="name"><?= $h($userName) ?></p>
                    <span class="role-tag"><?= $h($userRole) ?></span>

                    <div class="id-meta">
                        <div class="meta-row">
                            <i data-lucide="mail"></i> Email
                            <span class="meta-val"><?= $h($userEmail) ?></span>
                        </div>
                        <div class="meta-row">
                            <i data-lucide="phone"></i> Phone
                            <span class="meta-val"><?= $h($userPhone) ?></span>
                        </div>
                        <div class="meta-row">
                            <i data-lucide="calendar"></i> Joined
                            <span class="meta-val"><?= $h($userJoined) ?></span>
                        </div>
                        <div class="meta-row">
                            <i data-lucide="clock"></i> Last Login
                            <span class="meta-val"><?= $h($userLastLogin) ?></span>
                        </div>
                    </div>

                    <div class="id-stats">
                        <div class="id-stat">
                            <div class="num">247</div>
                            <div class="lbl">Approvals</div>
                        </div>
                        <div class="id-stat">
                            <div class="num">6</div>
                            <div class="lbl">Years</div>
                        </div>
                    </div>

                    <!-- Hidden file input -->
                    <input type="file" id="profilePhotoInput" accept="image/jpeg,image/png,image/webp" style="display:none;">

                    <div style="margin-top: 18px; display: flex; gap: 8px;">
                        <button type="button" class="btn btn-outline-gold" id="btnChangePhoto" style="flex:1; justify-content:center;">
                            <i data-lucide="camera"></i> <span id="btnPhotoLabel"><?= empty($profileAvatar) ? 'Add Photo' : 'Change Photo' ?></span>
                        </button>
                        <?php if (!empty($profileAvatar)): ?>
                        <button type="button" class="btn btn-outline" id="btnRemovePhoto" style="justify-content:center;">
                            <i data-lucide="trash-2"></i>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== RIGHT: FORMS ===== -->
        <div style="display:flex;flex-direction:column;gap:20px;">

            <div class="card">
                <div class="card-head"><i data-lucide="user"></i> Personal Information</div>
                <div class="card-body">
                    <div class="form-section">

                        <div id="profileSaveMsg" class="profile-msg"></div>

                        <div class="form-row">
                            <div>
                                <label class="form-label">First Name</label>
                                <input id="profileFirstName" class="form-input" type="text" value="<?= $h($firstName) ?>">
                            </div>
                            <div>
                                <label class="form-label">Last Name</label>
                                <input id="profileLastName" class="form-input" type="text" value="<?= $h($lastName) ?>">
                            </div>
                        </div>
                        <div class="form-row single">
                            <div>
                                <label class="form-label">Email Address</label>
                                <input id="profileEmail" class="form-input" type="email" value="<?= $h($userEmail) ?>">
                            </div>
                        </div>
                        <div class="form-row">
                            <div>
                                <label class="form-label">Phone</label>
                                <input id="profilePhone" class="form-input" type="tel" value="<?= $h($userPhone) ?>">
                            </div>
                            <div>
                                <label class="form-label">Timezone</label>
                                <input class="form-input" type="text" value="<?= $h($userTimezone) ?>">
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="button" class="btn btn-outline" id="btnCancelProfile">Cancel</button>
                            <button type="button" class="btn btn-primary" id="btnSaveProfile"><i data-lucide="save"></i> Save Changes</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-head"><i data-lucide="lock"></i> Change Password</div>
                <div class="card-body">
                    <div class="form-section">

                        <div id="profilePwMsg" class="profile-msg"></div>

                        <div class="form-row single">
                            <div>
                                <label class="form-label">Current Password</label>
                                <div class="pw-input-wrap">
                                    <input id="profileCurrentPw" class="form-input pw-input" type="password" autocomplete="current-password" minlength="8" required>
                                    <button type="button" class="pw-eye" data-target="profileCurrentPw" aria-label="Toggle visibility"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
                                </div>
                            </div>
                        </div>

                        <div class="form-row single">
                            <div>
                                <label class="form-label">New Password</label>
                                <div class="pw-input-wrap">
                                    <input id="profileNewPw" class="form-input pw-input" type="password" autocomplete="new-password" minlength="8" required>
                                    <button type="button" class="pw-eye" data-target="profileNewPw" aria-label="Toggle visibility"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
                                </div>
                            </div>
                        </div>

                        <div id="profilePwStrength" class="strength-meter" style="display:none;margin-bottom:16px;">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex-1 flex gap-[4px]">
                                    <span class="strength-seg" id="profileSeg1"></span>
                                    <span class="strength-seg" id="profileSeg2"></span>
                                    <span class="strength-seg" id="profileSeg3"></span>
                                    <span class="strength-seg" id="profileSeg4"></span>
                                </div>
                                <span id="profileStrengthText" class="text-[11px] text-[var(--rose)] opacity-70 whitespace-nowrap">Weak</span>
                            </div>
                        </div>

                        <div class="form-row single">
                            <div>
                                <label class="form-label">Confirm New Password</label>
                                <div class="pw-input-wrap">
                                    <input id="profileConfirmPw" class="form-input pw-input" type="password" autocomplete="new-password" minlength="8" required>
                                    <button type="button" class="pw-eye" data-target="profileConfirmPw" aria-label="Toggle visibility"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></button>
                                </div>
                            </div>
                        </div>
                        <p id="profilePwMatchHint" class="hidden text-[12px] text-red-400 mt-[-6px] mb-2">Passwords do not match.</p>

                        <div class="form-actions">
                            <button type="button" class="btn btn-primary" id="btnProfileUpdatePw">
                                <i data-lucide="key"></i> Update Password
                            </button>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Card 3: Danger Zone -->
            <div class="card" style="border-color:rgba(185,75,75,0.15);">
                <div class="card-body" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;">
                    <div>
                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
                            <i data-lucide="alert-triangle" style="color:var(--c-danger,#dc3545);width:18px;height:18px;"></i>
                            <span style="font-size:15px;font-weight:700;color:var(--c-danger,#dc3545);">Danger Zone</span>
                        </div>
                        <div style="font-size:12px;color:var(--text-muted,#9ca3af);">Delete your account and all associated data permanently.</div>
                    </div>
                    <button type="button" class="btn btn-danger" id="btnOpenDeleteModal" style="white-space:nowrap;">
                        <i data-lucide="trash-2"></i> Delete Account
                    </button>
                </div>
            </div>

        </div>
    </div>

</div>

<!-- Delete Account Modal -->
<div id="deleteAccountModal" style="display:none;position:fixed;inset:0;z-index:9999;align-items:center;justify-content:center;padding:20px;background:rgba(52,35,43,.45);backdrop-filter:blur(2px);">
  <div style="width:100%;max-width:440px;border-radius:16px;background:#fff;box-shadow:0 30px 70px rgba(52,35,43,.25);overflow:hidden;">
    <div style="display:flex;align-items:center;justify-content:space-between;padding:15px 18px;border-bottom:1px solid #e5e7eb;">
      <h3 style="margin:0;font-size:15px;font-weight:800;color:#1f2937;">Delete Account</h3>
      <button type="button" id="btnCloseDeleteModal" style="border:0;background:transparent;color:#9ca3af;cursor:pointer;font-size:20px;line-height:1;">&times;</button>
    </div>
    <div style="padding:18px;">
      <div style="display:flex;gap:10px;align-items:flex-start;padding:12px 14px;border-radius:10px;background:#fef3cd;border:1px solid #f0d68a;margin-bottom:16px;">
        <i data-lucide="alert-triangle" style="color:#856404;width:18px;height:18px;flex-shrink:0;margin-top:1px;"></i>
        <span style="font-size:12px;color:#856404;line-height:1.5;">This will <strong>permanently deactivate</strong> your admin account. You will be logged out immediately and won't be able to log back in. All records will be preserved.</span>
      </div>

      <label style="display:block;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.04em;margin-bottom:5px;">Confirm with your password</label>
      <input id="deleteAccountPw" type="password" placeholder="Enter your password" style="width:100%;box-sizing:border-box;border:1px solid #d1d5db;border-radius:8px;padding:10px 12px;font-size:13px;color:#1f2937;margin-bottom:8px;" autocomplete="current-password">

      <label style="display:block;font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.04em;margin-bottom:5px;">Type <strong style="color:#dc3545">DELETE</strong> to confirm</label>
      <input id="deleteAccountConfirm" type="text" placeholder="Type DELETE" style="width:100%;box-sizing:border-box;border:1px solid #d1d5db;border-radius:8px;padding:10px 12px;font-size:13px;color:#1f2937;margin-bottom:6px;" autocomplete="off">

      <div id="deleteAccountMsg" style="min-height:18px;font-size:12px;margin-bottom:10px;"></div>

      <div style="display:flex;justify-content:flex-end;gap:9px;">
        <button type="button" id="btnCancelDelete" class="btn btn-outline">Cancel</button>
        <button type="button" id="btnConfirmDelete" class="btn btn-danger" disabled>Delete my account</button>
      </div>
    </div>
  </div>
</div>

<script>
(function(){
'use strict';

if (window.lucide) lucide.createIcons();

// ── Password visibility toggle (data-target based) ──
const eyeOpen  = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
const eyeShut  = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10 10 0 0 1 12 20c-7 0-11-8-11-8a18 18 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9 9 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>';

document.querySelectorAll('.pw-eye').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var input = document.getElementById(this.getAttribute('data-target'));
        if (!input) return;
        var show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        this.innerHTML = show ? eyeShut : eyeOpen;
        this.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
    });
});

    // ── Strength meter ──
    const newPwInput     = document.getElementById('profileNewPw');
    const strengthMeter  = document.getElementById('profilePwStrength');
    const strengthText   = document.getElementById('profileStrengthText');
    const segments       = ['profileSeg1','profileSeg2','profileSeg3','profileSeg4'];

    const resetStrength = () => {
        segments.forEach(id => document.getElementById(id).classList.remove('active'));
        strengthText.textContent = 'Weak';
    };

    newPwInput.addEventListener('input', () => {
        const val = newPwInput.value;
        if (val.trim() === '') {
            strengthMeter.style.display = 'none';
            resetStrength();
            return;
        }
        strengthMeter.style.display = 'block';

        let score = 0;
        if (val.length >= 8)  score++;
        if (/[A-Z]/.test(val)) score++;
        if (/[0-9]/.test(val)) score++;
        if (/[^A-Za-z0-9]/.test(val)) score++;

        segments.forEach((id, i) => {
            document.getElementById(id).classList.toggle('active', i < score);
        });

        if (score <= 1)      strengthText.textContent = 'Weak';
        else if (score === 2) strengthText.textContent = 'Fair';
        else if (score === 3) strengthText.textContent = 'Good';
        else                  strengthText.textContent = 'Strong';
    });

    // ── Confirm password match ──
    const confirmInput = document.getElementById('profileConfirmPw');
    const matchHint    = document.getElementById('profilePwMatchHint');

    const checkMatch = () => {
        const pw  = newPwInput.value;
        const cpw = confirmInput.value;
        if (cpw === '') { matchHint.classList.remove('visible'); confirmInput.style.borderColor = ''; return; }
        if (pw !== cpw) {
            matchHint.classList.add('visible');
            confirmInput.style.borderColor = '#e74c6b';
        } else {
            matchHint.classList.remove('visible');
            confirmInput.style.borderColor = '#5b8c5a';
        }
    };
    confirmInput.addEventListener('input', checkMatch);
    newPwInput.addEventListener('input', () => { if (confirmInput.value !== '') checkMatch(); });

    // ── Helper: show inline message ──
    function showPwMsg(text, type) {
        const el = document.getElementById('profilePwMsg');
        if (!el) return;
        el.textContent = text;
        el.className = 'profile-msg ' + (type || '');
        if (type === 'success') {
            // Keep success message visible for 10s, then fade
            setTimeout(() => { el.className = 'profile-msg'; el.textContent = ''; }, 10000);
        }
    }
    function showProfileMsg(elId, text, type) {
        const el = document.getElementById(elId);
        if (!el) return;
        el.textContent = text;
        el.className = 'profile-msg ' + (type || '');
        if (type) setTimeout(() => { el.className = 'profile-msg'; el.textContent = ''; }, 5000);
    }

    // ── Update password button ──
    document.getElementById('btnProfileUpdatePw').addEventListener('click', () => {
        const currentPw = document.getElementById('profileCurrentPw').value;
        const newPw     = newPwInput.value;
        const confirmPw = confirmInput.value;

        // Reset
        [document.getElementById('profileCurrentPw'), newPwInput, confirmInput].forEach(el => {
            el.style.borderColor = '';
        });
        matchHint.classList.remove('visible');
        showPwMsg('', '');

        let valid = true;

        if (currentPw.trim() === '') {
            document.getElementById('profileCurrentPw').style.borderColor = '#e74c6b';
            valid = false;
        }
        if (newPw.length < 8) {
            newPwInput.style.borderColor = '#e74c6b';
            valid = false;
        }
        if (newPw !== confirmPw) {
            confirmInput.style.borderColor = '#e74c6b';
            matchHint.classList.add('visible');
            valid = false;
        }
        if (!valid) return;

        // Post to backend
        const btnPw = document.getElementById('btnProfileUpdatePw');
        btnPw.disabled = true;
        btnPw.innerHTML = '<i data-lucide="loader"></i> Updating…';
        if (window.lucide) lucide.createIcons();

        fetch('<?= URLROOT ?>/admin/updatePassword', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                current_password: currentPw,
                new_password: newPw,
                device: navigator.userAgent || 'Unknown device',
            }),
        })
        .then(res => res.json())
        .then(data => {
            btnPw.disabled = false;
            btnPw.innerHTML = '<i data-lucide="key"></i> Update Password';
            if (window.lucide) lucide.createIcons();
            if (data.ok) {
                document.getElementById('profileCurrentPw').value = '';
                newPwInput.value = '';
                confirmInput.value = '';
                strengthMeter.style.display = 'none';
                resetStrength();

                // Show prominent success banner
                const pwCard = document.getElementById('profilePwMsg').closest('.card');
                if (pwCard) {
                    // Add green border to password card
                    pwCard.style.borderColor = '#10b981';
                    pwCard.style.boxShadow = '0 0 0 3px rgba(16,185,129,.15)';
                    setTimeout(() => {
                        pwCard.style.borderColor = '';
                        pwCard.style.boxShadow = '';
                    }, 8000);
                }

                // Update or create "last changed" indicator
                let lastChanged = document.getElementById('pwLastChanged');
                if (!lastChanged) {
                    lastChanged = document.createElement('div');
                    lastChanged.id = 'pwLastChanged';
                    lastChanged.style.cssText = 'display:flex;align-items:center;gap:8px;padding:10px 14px;margin-top:12px;border-radius:8px;background:#d1fae5;color:#065f46;font-size:12px;font-weight:600;';
                    document.getElementById('profilePwMsg').after(lastChanged);
                }
                lastChanged.innerHTML = '✓ Password changed just now — confirmation email sent';
                lastChanged.style.display = 'flex';

                showPwMsg('✓ Password updated successfully!', 'success');
            } else {
                showPwMsg(data.error || 'Failed to update password.', 'error');
            }
        })
        .catch(() => {
            btnPw.disabled = false;
            btnPw.innerHTML = '<i data-lucide="key"></i> Update Password';
            if (window.lucide) lucide.createIcons();
            showPwMsg('Network error. Please try again.', 'error');
        });
    });

    // ============ SAVE PROFILE ============
    const btnSaveProfile = document.getElementById('btnSaveProfile');
    const btnCancelProfile = document.getElementById('btnCancelProfile');

    if (btnSaveProfile) {
        btnSaveProfile.addEventListener('click', () => {
            const firstName = document.getElementById('profileFirstName').value.trim();
            const lastName  = document.getElementById('profileLastName').value.trim();
            const email     = document.getElementById('profileEmail').value.trim();
            const phone     = document.getElementById('profilePhone').value.trim();

            btnSaveProfile.disabled = true;
            btnSaveProfile.innerHTML = '<i data-lucide="loader"></i> Saving…';
            if (window.lucide) lucide.createIcons();

            fetch('<?= URLROOT ?>/admin/updateProfile', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    name: (firstName + ' ' + lastName).trim(),
                    email: email,
                    phone: phone,
                }),
            })
            .then(res => res.json())
            .then(data => {
                btnSaveProfile.disabled = false;
                btnSaveProfile.innerHTML = '<i data-lucide="save"></i> Save Changes';
                if (window.lucide) lucide.createIcons();
                if (data.ok) {
                    showProfileMsg('profileSaveMsg', '✓ Profile updated successfully.', 'success');
                } else {
                    showProfileMsg('profileSaveMsg', data.error || 'Failed to update profile.', 'error');
                }
            })
            .catch(() => {
                btnSaveProfile.disabled = false;
                btnSaveProfile.innerHTML = '<i data-lucide="save"></i> Save Changes';
                if (window.lucide) lucide.createIcons();
                showProfileMsg('profileSaveMsg', 'Network error. Please try again.', 'error');
            });
        });
    }

    if (btnCancelProfile) {
        btnCancelProfile.addEventListener('click', () => {
            // Reset to original values
            document.getElementById('profileFirstName').value = '<?= $h($firstName) ?>';
            document.getElementById('profileLastName').value  = '<?= $h($lastName) ?>';
            document.getElementById('profileEmail').value     = '<?= $h($userEmail) ?>';
            document.getElementById('profilePhone').value     = '<?= $h($userPhone) ?>';
        });
    }

    // ============ PROFILE PHOTO UPLOAD ============
    const photoInput  = document.getElementById('profilePhotoInput');
    const btnPhoto    = document.getElementById('btnChangePhoto');
    const btnRemove   = document.getElementById('btnRemovePhoto');
    const photoLabel  = document.getElementById('btnPhotoLabel');
    const avatarFrame = document.getElementById('profileAvatarFrame');

    function setAvatarDisplay(url) {
        // Replace frame contents with an <img> tag
        const existingImg = document.getElementById('profileAvatarImg');
        if (existingImg) {
            existingImg.src = url;
        } else {
            const initialsDiv = document.getElementById('profileAvatarInner');
            if (initialsDiv) initialsDiv.remove();

            const img = document.createElement('img');
            img.id = 'profileAvatarImg';
            img.src = url;
            img.alt = 'Profile photo';
            img.className = 'avatar-img';
            avatarFrame.appendChild(img);
        }
        photoLabel.textContent = 'Change Photo';

        // Show remove button if not already there
        if (!btnRemove) {
            // re-create on next reload — for now just hide initials
        }
    }

    // Clicking the Photo button opens the file picker
    btnPhoto.addEventListener('click', () => photoInput.click());

    // When a file is selected, upload it
    photoInput.addEventListener('change', () => {
        const file = photoInput.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('profile_photo', file);

        // Show uploading state
        btnPhoto.disabled = true;
        photoLabel.textContent = 'Uploading…';

        fetch('<?= URLROOT ?>/admin/uploadProfilePhoto', {
            method: 'POST',
            body: formData,
        })
        .then(res => res.json())
        .then(data => {
            btnPhoto.disabled = false;
            if (data.ok) {
                setAvatarDisplay(data.url + '?t=' + Date.now());
                if (window.lucide) lucide.createIcons();
            } else {
                photoLabel.textContent = 'Add Photo';
                alert(data.error || 'Upload failed.');
            }
        })
        .catch(() => {
            btnPhoto.disabled = false;
            photoLabel.textContent = 'Add Photo';
            alert('Network error. Please try again.');
        });

        // Reset the input so the same file can be reselected
        photoInput.value = '';
    });

    // Remove photo
    if (btnRemove) {
        btnRemove.addEventListener('click', () => {
            if (!confirm('Remove your profile photo?')) return;

            fetch('<?= URLROOT ?>/admin/removeProfilePhoto', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
            })
            .then(res => res.json())
            .then(data => {
                if (data.ok) {
                    // Switch back to initials
                    const img = document.getElementById('profileAvatarImg');
                    if (img) img.remove();

                    const initialsDiv = document.createElement('div');
                    initialsDiv.id = 'profileAvatarInner';
                    initialsDiv.className = 'avatar-inner';
                    initialsDiv.textContent = '<?= $initials ?>';
                    avatarFrame.appendChild(initialsDiv);

                    photoLabel.textContent = 'Add Photo';
                    btnRemove.remove();
                    if (window.lucide) lucide.createIcons();
                } else {
                    alert(data.error || 'Failed to remove photo.');
                }
            })
            .catch(() => alert('Network error.'));
        });
    }

    // ── DELETE ACCOUNT MODAL ──
    const delModal   = document.getElementById('deleteAccountModal');
    const delPwInput = document.getElementById('deleteAccountPw');
    const delConfirm = document.getElementById('deleteAccountConfirm');
    const delBtn     = document.getElementById('btnConfirmDelete');
    const delMsg     = document.getElementById('deleteAccountMsg');

    document.getElementById('btnOpenDeleteModal').addEventListener('click', () => {
        delModal.style.display = 'flex';
        delPwInput.value = '';
        delConfirm.value = '';
        delMsg.textContent = '';
        delMsg.style.color = '';
        delBtn.disabled = true;
        delPwInput.focus();
    });

    function closeDeleteModal() {
        delModal.style.display = 'none';
        delPwInput.value = '';
        delConfirm.value = '';
        delMsg.textContent = '';
    }

    document.getElementById('btnCloseDeleteModal').addEventListener('click', closeDeleteModal);
    document.getElementById('btnCancelDelete').addEventListener('click', closeDeleteModal);
    delModal.addEventListener('click', (e) => { if (e.target === delModal) closeDeleteModal(); });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && delModal.style.display === 'flex') closeDeleteModal(); });

    function checkDeleteReady() {
        delBtn.disabled = !(delPwInput.value.trim() && delConfirm.value.trim().toUpperCase() === 'DELETE');
    }
    delPwInput.addEventListener('input', checkDeleteReady);
    delConfirm.addEventListener('input', checkDeleteReady);

    delBtn.addEventListener('click', () => {
        delBtn.disabled = true;
        delBtn.textContent = 'Deleting…';
        delMsg.textContent = '';
        delMsg.style.color = '';

        fetch('<?= URLROOT ?>/admin/deleteAccount', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ password: delPwInput.value }),
        })
        .then(res => res.json())
        .then(data => {
            if (data.ok) {
                delMsg.textContent = 'Account deleted. Redirecting…';
                delMsg.style.color = '#065f46';
                setTimeout(() => { window.location.href = data.redirect || '<?= URLROOT ?>/'; }, 1200);
            } else {
                delMsg.textContent = data.error || 'Failed to delete account.';
                delMsg.style.color = '#dc3545';
                delBtn.disabled = false;
                delBtn.textContent = 'Delete my account';
            }
        })
        .catch(() => {
            delMsg.textContent = 'Network error. Please try again.';
            delMsg.style.color = '#dc3545';
            delBtn.disabled = false;
            delBtn.textContent = 'Delete my account';
        });
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
  <?php require APPROOT . '/views/dashboardLayout/sidebar.php'; ?>
</body>
</html>
