<?php
$currentFee = (float)($currentFee ?? 5);

$dashboardTitle = 'Settings';
$dashboardCrumb = 'Platform fees';
$dashboardContentClass = 'platform-settings-shell';
$dashboardBreadcrumbs = [
    ['label' => 'Settings', 'url' => null],
    ['label' => 'Platform fees', 'url' => null],
];

$h = static fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');

// Flash message (typed — 'platform_flash' with ['type' => ..., 'message' => ...])
$flash = $_SESSION['platform_flash'] ?? null;
unset($_SESSION['platform_flash']);

$dashboardContent = function () use ($currentFee, $h, $flash) {
?>
<style>
    .platform-settings-shell { min-height: 100%; padding: 30px; background: #fbfbf9; }
    .ps-page { max-width: 860px; margin: 0 auto; color: #6d4c5b; }
    .ps-kicker { margin: 0 0 7px; color: #9b7d89; font-size: 10px; font-weight: 800; letter-spacing: .18em; text-transform: uppercase; }
    .ps-title { margin: 0; font-family: "Playfair Display", serif; font-size: clamp(28px, 3vw, 40px); font-weight: 650; line-height: 1; color: #6d4c5b; }
    .ps-subtitle { max-width: 560px; margin: 10px 0 0; color: #7b5c69; font-size: 13px; line-height: 1.6; }

    .ps-card { margin-top: 28px; border: 1px solid #ead8c7; border-radius: 15px; background: #FFFFFF; box-shadow: 0 18px 45px rgba(52,35,43,.06); overflow: hidden; }
    .ps-card-header { padding: 22px 28px; border-bottom: 1px solid #ead8c7; }
    .ps-card-title { margin: 0; font-size: 16px; font-weight: 700; color: #6d4c5b; }
    .ps-card-desc { margin: 6px 0 0; font-size: 13px; color: #7b5c69; }
    .ps-card-body { padding: 28px; }

    .ps-field { margin-bottom: 24px; }
    .ps-field:last-child { margin-bottom: 0; }
    .ps-label { display: block; margin-bottom: 8px; font-size: 12px; font-weight: 700; color: #6d4c5b; text-transform: uppercase; letter-spacing: .08em; }
    .ps-input-wrap { position: relative; max-width: 200px; }
    .ps-input { width: 100%; padding: 10px 40px 10px 14px; border: 1px solid #ead8c7; border-radius: 10px; background: #fff; font-size: 16px; font-weight: 600; color: #6d4c5b; transition: border-color .15s, box-shadow .15s; -moz-appearance: textfield; }
    .ps-input::-webkit-inner-spin-button,
    .ps-input::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
    .ps-input:focus { outline: none; border-color: #6d4c5b; box-shadow: 0 0 0 3px rgba(109,76,91,.12); }
    .ps-input:invalid:not(:placeholder-shown) { border-color: #dc2626; box-shadow: 0 0 0 3px rgba(220,38,38,.1); }
    .ps-input-suffix { position: absolute; right: 14px; top: 50%; transform: translateY(-50%); color: #9b7d89; font-size: 14px; font-weight: 600; pointer-events: none; }
    .ps-hint { margin-top: 6px; font-size: 12px; color: #9b7d89; }

    .ps-preview { margin-top: 24px; padding: 18px 22px; border-radius: 12px; background: rgba(109,76,91,.06); border: 1px dashed rgba(109,76,91,.2); }
    .ps-preview-label { font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: .12em; color: #6d4c5b; margin-bottom: 10px; }
    .ps-preview-row { display: flex; justify-content: space-between; align-items: center; padding: 6px 0; font-size: 13px; color: #6d4c5b; }
    .ps-preview-row span:last-child { font-weight: 700; font-variant-numeric: tabular-nums; }
    .ps-preview-divider { border: none; border-top: 1px solid rgba(109,76,91,.12); margin: 8px 0; }
    .ps-preview-total { font-size: 14px; font-weight: 800; color: #6d4c5b; }

    .ps-actions { display: flex; align-items: center; gap: 14px; margin-top: 28px; }
    .ps-btn { display: inline-flex; align-items: center; justify-content: center; min-height: 44px; padding: 0 28px; border: none; border-radius: 11px; font-size: 13px; font-weight: 700; cursor: pointer; transition: transform .16s, box-shadow .16s, opacity .16s; }
    .ps-btn-primary { background: #6d4c5b; color: #FFFFFF; box-shadow: 0 10px 22px rgba(109,76,91,.18); }
    .ps-btn-primary:hover { transform: translateY(-1px); box-shadow: 0 14px 28px rgba(109,76,91,.24); }
    .ps-btn-primary:focus-visible { outline: 3px solid rgba(109,76,91,.35); outline-offset: 2px; }
    .ps-btn-primary:active { transform: translateY(0); box-shadow: 0 6px 14px rgba(109,76,91,.15); }
    .ps-btn-primary.ps-saving { opacity: .65; pointer-events: none; }

    .ps-flash { display: flex; align-items: center; gap: 10px; margin-bottom: 20px; padding: 14px 18px; border-radius: 10px; font-size: 13px; font-weight: 600; animation: psFlashIn .3s ease; }
    .ps-flash-success { background: #ecfdf5; color: #065F46; border: 1px solid #a7f3d0; }
    .ps-flash-error { background: #fef2f2; color: #991B1B; border: 1px solid #fecaca; }
    .ps-flash-icon { flex-shrink: 0; width: 18px; height: 18px; }

    @keyframes psFlashIn { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: translateY(0); } }

    .ps-info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px; margin-top: 24px; }
    .ps-info-card { padding: 16px 18px; border: 1px solid #ead8c7; border-radius: 12px; background: #fff; }
    .ps-info-card-label { font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: .1em; color: #9b7d89; }
    .ps-info-card-value { margin-top: 6px; font-size: 20px; font-weight: 750; color: #6d4c5b; font-variant-numeric: tabular-nums; }
    .ps-info-card-note { margin-top: 4px; font-size: 11px; color: #a58b96; }

    @media (prefers-reduced-motion: reduce) {
        .ps-flash, .ps-btn, .ps-input { animation: none !important; transition: none !important; }
    }
</style>

<div class="ps-page">
    <p class="ps-kicker">System Configuration</p>
    <h1 class="ps-title">Platform Fees</h1>
    <p class="ps-subtitle">Configure the platform service fee charged to customers on every booking. The fee is added on top of the deposit at checkout.</p>

    <?php if ($flash): ?>
        <?php $flashType = ($flash['type'] ?? 'error') === 'success' ? 'success' : 'error'; ?>
        <div class="ps-flash ps-flash-<?= $flashType ?>" role="alert">
            <svg class="ps-flash-icon" viewBox="0 0 18 18" fill="none">
                <?php if ($flashType === 'success'): ?>
                    <circle cx="9" cy="9" r="8" stroke="currentColor" stroke-width="1.5" fill="none"/>
                    <path d="M5.5 9.5l2 2 5-5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <?php else: ?>
                    <circle cx="9" cy="9" r="8" stroke="currentColor" stroke-width="1.5" fill="none"/>
                    <path d="M9 5.5v4M9 12v.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                <?php endif; ?>
            </svg>
            <span><?= $h($flash['message'] ?? '') ?></span>
        </div>
    <?php endif; ?>

    <div class="ps-info-grid">
        <div class="ps-info-card">
            <div class="ps-info-card-label">Current Rate</div>
            <div class="ps-info-card-value"><?= $currentFee > 0 ? rtrim(rtrim(number_format($currentFee, 2), '0'), '.') . '%' : 'No fee' ?></div>
            <div class="ps-info-card-note">Applied to all bookings</div>
        </div>
        <div class="ps-info-card">
            <div class="ps-info-card-label">Example: 1,000,000 MMK booking</div>
            <div class="ps-info-card-value"><?= $currentFee > 0 ? number_format(1000000 * ($currentFee / 100), 0) . ' MMK' : '0 MMK' ?></div>
            <div class="ps-info-card-note">Platform fee collected</div>
        </div>
        <div class="ps-info-card">
            <div class="ps-info-card-label">Customer pays at checkout</div>
            <div class="ps-info-card-value"><?= number_format(1000000 * 0.20 + 1000000 * ($currentFee / 100), 0) ?> MMK</div>
            <div class="ps-info-card-note">20% deposit<?= $currentFee > 0 ? ' + ' . rtrim(rtrim(number_format($currentFee, 2), '0'), '.') . '% fee' : '' ?></div>
        </div>
    </div>

    <form method="POST" action="<?= URLROOT ?>/admin/settings" id="settingsForm">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">

        <div class="ps-card">
            <div class="ps-card-header">
                <h2 class="ps-card-title">Platform Fee Percentage</h2>
                <p class="ps-card-desc">Set the percentage charged on every booking's total amount. This applies to both package and custom service bookings.</p>
            </div>
            <div class="ps-card-body">
                <div class="ps-field">
                    <label class="ps-label" for="platform_fee_percent">Fee Rate</label>
                    <div class="ps-input-wrap">
                        <input type="number" id="platform_fee_percent" name="platform_fee_percent"
                               class="ps-input" min="0" max="100" step="0.01" required
                               autocomplete="off" inputmode="decimal"
                               value="<?= rtrim(rtrim(number_format($currentFee, 2), '0'), '.') ?>">
                        <span class="ps-input-suffix">%</span>
                    </div>
                    <p class="ps-hint">Enter a value between 0 and 100. Decimals are allowed (e.g. 7.5).</p>
                </div>

                <div class="ps-preview" id="feePreview">
                    <div class="ps-preview-label">Live Preview</div>
                    <div class="ps-preview-row">
                        <span>Booking total</span>
                        <span>1,000,000 MMK</span>
                    </div>
                    <div class="ps-preview-row">
                        <span>Platform fee (<span id="previewRate"><?= rtrim(rtrim(number_format($currentFee, 2), '0'), '.') ?></span>%)</span>
                        <span id="previewFee"><?= number_format(1000000 * ($currentFee / 100), 0) ?> MMK</span>
                    </div>
                    <hr class="ps-preview-divider">
                    <div class="ps-preview-row">
                        <span>Deposit (20%)</span>
                        <span>200,000 MMK</span>
                    </div>
                    <div class="ps-preview-row">
                        <span>Platform fee</span>
                        <span id="previewFee2"><?= number_format(1000000 * ($currentFee / 100), 0) ?> MMK</span>
                    </div>
                    <hr class="ps-preview-divider">
                    <div class="ps-preview-row ps-preview-total">
                        <span>Customer pays at checkout</span>
                        <span id="previewDeposit"><?= number_format(1000000 * 0.20 + 1000000 * ($currentFee / 100), 0) ?> MMK</span>
                    </div>
                </div>

                <div class="ps-actions">
                    <button type="submit" class="ps-btn ps-btn-primary" id="saveBtn">Save Changes</button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
(function () {
    const input = document.getElementById('platform_fee_percent');
    const previewRate = document.getElementById('previewRate');
    const previewFee  = document.getElementById('previewFee');
    const previewFee2 = document.getElementById('previewFee2');
    const previewDeposit = document.getElementById('previewDeposit');
    const saveBtn = document.getElementById('saveBtn');
    const form = document.getElementById('settingsForm');
    const sampleTotal = 1000000;
    const depositRate = 0.20;

    function fmt(n) {
        return Math.round(n).toLocaleString('en-US');
    }

    function update() {
        const rate = Math.max(0, Math.min(100, parseFloat(input.value) || 0));
        const fee = sampleTotal * (rate / 100);
        const deposit = sampleTotal * depositRate + fee;
        const rateText = rate % 1 === 0 ? rate.toFixed(0) : rate.toFixed(2);
        previewRate.textContent = rateText;
        previewFee.textContent  = fmt(fee) + ' MMK';
        previewFee2.textContent = fmt(fee) + ' MMK';
        previewDeposit.textContent = fmt(deposit) + ' MMK';
    }

    input.addEventListener('input', update);

    form.addEventListener('submit', function () {
        saveBtn.classList.add('ps-saving');
        saveBtn.textContent = 'Saving…';
    });

    update();
})();
</script>
<?php
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php require_once APPROOT . '/views/dashboardLayout/head.php'; ?>
</head>
<body class="grid h-screen gap-0 bg-app-page" style="grid-template-columns: 280px 1fr;">
    <?php require APPROOT . '/views/dashboardLayout/adminsidebar.php'; ?>
</body>
</html>
