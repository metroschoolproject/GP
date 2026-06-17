<?php
$paymentContext = $paymentContext ?? [];
$amount = (float)($paymentContext['amount'] ?? 0);
$currency = htmlspecialchars($paymentContext['currency'] ?? 'MMK', ENT_QUOTES, 'UTF-8');
$summary = $paymentContext['summary'] ?? [];
$action = htmlspecialchars($paymentContext['action'] ?? '', ENT_QUOTES, 'UTF-8');
$backUrl = htmlspecialchars($paymentContext['backUrl'] ?? URLROOT . '/main/home', ENT_QUOTES, 'UTF-8');
$banks = defined('PLATFORM_BANK_ACCOUNTS') ? PLATFORM_BANK_ACCOUNTS : [];
$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$bankIcons = [
    'KBZ Pay'           => '🏦',
    'Wave Money'        => '🌊',
    'AYA Pay'           => '💙',
    'Yoma Bank'         => '🏧',
    'CB Bank'           => '🟢',
    'Visa / MasterCard' => '💳',
];
?>

<style>
.bank-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:14px}
.bank-btn{display:flex;flex-direction:column;align-items:center;gap:6px;padding:14px 8px;border:1px solid var(--border);border-radius:.75rem;background:var(--soft);cursor:pointer;transition:all .12s;font-family:inherit}
.bank-btn:hover{border-color:var(--primary);background:var(--hover)}
.bank-btn.active{border-color:var(--primary);background:var(--primary);color:#fff}
.bank-icon{font-size:22px}
.bank-label{font-size:11px;font-weight:800;text-align:center;line-height:1.3;color:inherit}
.bank-account-info{border:1px solid var(--border);border-radius:.75rem;background:var(--soft);padding:14px 16px;margin-bottom:14px;display:none}
.bank-account-info.show{display:block}
.bank-acct-title{font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--muted);margin-bottom:10px}
.bank-detail-list{display:grid;gap:7px}
.bank-detail-list div{display:flex;justify-content:space-between;gap:14px;border-top:1px solid var(--border-light);padding-top:7px}
.bank-detail-list dt{color:var(--muted);font-size:10px;font-weight:800;letter-spacing:.08em;text-transform:uppercase}
.bank-detail-list dd{margin:0;max-width:60%;text-align:right;color:var(--text);font-size:12px;font-weight:800;word-break:break-word;font-family:monospace}
.transfer-section{display:none}
.transfer-section.show{display:block}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.form-field{margin-bottom:12px}
.form-field label{display:block;font-size:12px;font-weight:700;color:var(--muted);margin-bottom:6px}
.form-field label .req{color:#b94b4b}
.form-field label .opt{font-weight:400}
.form-field input[type=text],.form-field input[type=number],.form-field input[type=datetime-local]{width:100%;border:1px solid var(--border);border-radius:.75rem;background:var(--soft);padding:10px 12px;color:var(--text);font-family:inherit;font-size:13px;outline:none;transition:border-color .12s,box-shadow .12s}
.form-field input:focus{border-color:var(--primary);background:#fff;box-shadow:0 0 0 3px rgba(109,76,91,.1)}
@media(max-width:640px){.bank-grid{grid-template-columns:repeat(2,1fr)}.form-grid{grid-template-columns:1fr}}
</style>

<form method="POST" action="<?= $action ?>" class="payment-form" enctype="multipart/form-data" novalidate>

    <!-- Amount Summary -->
    <section class="payment-panel payment-summary" aria-label="Payment summary">
        <div>
            <p class="payment-kicker"><?= $h($paymentContext['amountLabel'] ?? 'Amount due') ?></p>
            <p class="payment-amount"><?= number_format($amount) ?> <span><?= $currency ?></span></p>
        </div>
        <div class="summary-list">
            <?php foreach ($summary as $label => $value): ?>
                <div class="summary-row">
                    <span><?= $h($label) ?></span>
                    <strong><?= $h((string)$value) ?></strong>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Bank Selection -->
    <section class="payment-panel" aria-label="Select bank">
        <label class="payment-label">Select your bank or payment app</label>
        <div class="bank-grid" id="bankGrid">
            <?php foreach ($banks as $bankName => $bankInfo): ?>
            <button type="button" class="bank-btn" data-bank="<?= $h($bankName) ?>">
                <span class="bank-icon"><?= $bankIcons[$bankName] ?? '💰' ?></span>
                <span class="bank-label"><?= $h($bankName) ?></span>
            </button>
            <?php endforeach; ?>
        </div>

        <!-- Platform account info per bank -->
        <?php foreach ($banks as $bankName => $bankInfo): ?>
        <?php $safeId = preg_replace('/[^a-z0-9]/', '-', strtolower($bankName)); ?>
        <div class="bank-account-info" id="acct-<?= $safeId ?>">
            <p class="bank-acct-title">Transfer <?= number_format($amount) ?> <?= $currency ?> to this account</p>
            <dl class="bank-detail-list">
                <div><dt>Bank</dt><dd><?= $h($bankName) ?></dd></div>
                <div><dt>Account Name</dt><dd><?= $h($bankInfo['name'] ?? '') ?></dd></div>
                <div><dt>Account / Number</dt><dd><?= $h($bankInfo['account'] ?? '') ?></dd></div>
            </dl>
        </div>
        <?php endforeach; ?>
    </section>

    <!-- Transfer Details -->
    <section class="payment-panel transfer-section" id="transferSection" aria-label="Transfer details">
        <label class="payment-label">Your transfer details</label>

        <input type="hidden" name="bank_name" id="bankNameInput" value="">

        <div class="form-grid">
            <div class="form-field">
                <label for="account_name">Your Account Name <span class="req">*</span></label>
                <input type="text" id="account_name" name="account_name" placeholder="e.g. Ko Kyaw Zin" required>
            </div>
            <div class="form-field">
                <label for="mobile_number">Your Mobile Number <span class="req">*</span></label>
                <input type="text" id="mobile_number" name="mobile_number" placeholder="09XXXXXXXXX" required>
            </div>
        </div>
        <div class="form-field">
            <label for="transaction_ref">Payment Reference <span class="req">*</span></label>
            <input type="text" id="transaction_ref" name="transaction_ref" placeholder="e.g. TXN-12345678" required>
        </div>
        <div class="form-grid">
            <div class="form-field">
                <label for="paid_amount">Amount Paid (<?= $currency ?>) <span class="req">*</span></label>
                <input type="number" id="paid_amount" name="paid_amount" placeholder="<?= number_format($amount, 0, '.', '') ?>" min="1" step="1" required>
            </div>
            <div class="form-field">
                <label for="paid_at">Date &amp; Time of Transfer <span class="req">*</span></label>
                <input type="datetime-local" id="paid_at" name="paid_at" required>
            </div>
        </div>
        <div class="form-field">
            <label for="remark">Remark <span class="opt">(optional)</span></label>
            <input type="text" id="remark" name="remark" placeholder="Any notes about this transfer">
        </div>
        <div class="form-field">
            <label>Upload Slip / Screenshot <span class="opt">(optional but recommended)</span></label>
            <label for="slip_image" class="payment-slip-upload" id="slipUploadLabel">
                <div class="payment-slip-icon">IMG</div>
                <div>
                    <strong id="slipFileName">Click to upload screenshot or receipt</strong>
                    <small>JPG, PNG, PDF — max 5 MB</small>
                </div>
            </label>
            <input type="file" id="slip_image" name="slip_image" accept=".jpg,.jpeg,.png,.webp,.pdf" class="payment-form input[type=file]" style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0)">
        </div>
    </section>

    <div class="payment-actions">
        <a href="<?= $backUrl ?>" class="payment-back">Back</a>
        <button type="submit" class="payment-submit" id="submitBtn" disabled>Submit Payment Proof</button>
    </div>
</form>

<script>
(function () {
    const bankBtns = document.querySelectorAll('.bank-btn');
    const bankInput = document.getElementById('bankNameInput');
    const transferSection = document.getElementById('transferSection');
    const submitBtn = document.getElementById('submitBtn');
    const slipInput = document.getElementById('slip_image');
    const slipFileName = document.getElementById('slipFileName');

    function safeId(name) {
        return name.toLowerCase().replace(/[^a-z0-9]/g, '-');
    }

    function selectBank(bankName) {
        bankBtns.forEach(btn => btn.classList.toggle('active', btn.dataset.bank === bankName));
        document.querySelectorAll('.bank-account-info').forEach(el => el.classList.remove('show'));

        const box = document.getElementById('acct-' + safeId(bankName));
        if (box) box.classList.add('show');

        bankInput.value = bankName;
        transferSection.classList.add('show');
        submitBtn.disabled = false;
    }

    bankBtns.forEach(btn => btn.addEventListener('click', () => selectBank(btn.dataset.bank)));

    if (slipInput) {
        slipInput.addEventListener('change', function () {
            slipFileName.textContent = this.files[0] ? this.files[0].name : 'Click to upload screenshot or receipt';
        });
    }
})();
</script>
