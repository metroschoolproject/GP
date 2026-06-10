<?php
$paymentContext = $paymentContext ?? [];
$amount = (float)($paymentContext['amount'] ?? 0);
$currency = htmlspecialchars($paymentContext['currency'] ?? 'MMK', ENT_QUOTES, 'UTF-8');
$methods = $paymentContext['methods'] ?? [];
$summary = $paymentContext['summary'] ?? [];
$action = htmlspecialchars($paymentContext['action'] ?? '', ENT_QUOTES, 'UTF-8');
$backUrl = htmlspecialchars($paymentContext['backUrl'] ?? URLROOT . '/main/home', ENT_QUOTES, 'UTF-8');
$submitLabel = htmlspecialchars($paymentContext['submitLabel'] ?? 'Submit payment', ENT_QUOTES, 'UTF-8');
$gatewayPayment = $paymentContext['gatewayPayment'] ?? null;
$bankTransfer = $paymentContext['bankTransfer'] ?? [];
?>
<?php if ($gatewayPayment): ?>
<div class="payment-form">
    <section class="payment-panel payment-summary" aria-label="Payment summary">
        <div>
            <p class="payment-kicker"><?= htmlspecialchars($paymentContext['amountLabel'] ?? 'Amount due', ENT_QUOTES, 'UTF-8') ?></p>
            <p class="payment-amount"><?= number_format($amount) ?> <span><?= $currency ?></span></p>
        </div>

        <div class="summary-list">
            <?php foreach ($summary as $label => $value): ?>
                <div class="summary-row">
                    <span><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span>
                    <strong><?= htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8') ?></strong>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="payment-panel gateway-panel" aria-label="KBZ Pay QR">
        <div class="gateway-copy">
            <p class="payment-label">KBZ Pay QR</p>
            <h2>Scan with phone</h2>
            <p>The QR opens the fake KBZ checkout on your phone. Enter demo PIN <strong>123456</strong>, then this supplier dashboard unlocks automatically.</p>
            <a class="gateway-link" href="<?= htmlspecialchars($gatewayPayment['checkoutUrl'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">
                <?= htmlspecialchars($gatewayPayment['checkoutUrl'], ENT_QUOTES, 'UTF-8') ?>
            </a>
        </div>
        <div class="qr-card">
            <img src="<?= htmlspecialchars($gatewayPayment['qrUrl'], ENT_QUOTES, 'UTF-8') ?>" alt="KBZ Pay demo QR code">
            <span>Payment #<?= (int)($gatewayPayment['id'] ?? 0) ?></span>
        </div>
    </section>

    <div class="payment-actions">
        <a href="<?= $backUrl ?>" class="payment-back">Back</a>
        <a href="<?= htmlspecialchars($gatewayPayment['checkoutUrl'], ENT_QUOTES, 'UTF-8') ?>" class="payment-submit" target="_blank" rel="noopener">Open checkout</a>
    </div>
</div>
<?php return; ?>
<?php endif; ?>

<form method="POST" action="<?= $action ?>" class="payment-form" enctype="multipart/form-data" novalidate>
    <section class="payment-panel payment-summary" aria-label="Payment summary">
        <div>
            <p class="payment-kicker"><?= htmlspecialchars($paymentContext['amountLabel'] ?? 'Amount due', ENT_QUOTES, 'UTF-8') ?></p>
            <p class="payment-amount"><?= number_format($amount) ?> <span><?= $currency ?></span></p>
        </div>

        <div class="summary-list">
            <?php foreach ($summary as $label => $value): ?>
                <div class="summary-row">
                    <span><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span>
                    <strong><?= htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8') ?></strong>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="payment-panel" aria-label="Payment details">
        <label class="payment-label" for="paymentMethod">Payment method</label>
        <div class="method-grid">
            <?php foreach ($methods as $index => $method): ?>
                <?php $methodId = 'payment_method_' . $index; ?>
                <label class="method-option" for="<?= $methodId ?>">
                    <input id="<?= $methodId ?>" type="radio" name="method" value="<?= htmlspecialchars($method, ENT_QUOTES, 'UTF-8') ?>" <?= $index === 0 ? 'checked' : '' ?>>
                    <span><?= htmlspecialchars($method, ENT_QUOTES, 'UTF-8') ?></span>
                </label>
            <?php endforeach; ?>
        </div>

        <div class="method-info" data-method-info="KBZ Pay">
            <div class="method-info-icon">K</div>
            <div>
                <strong>KBZ Pay gateway demo</strong>
                <p>Creates a pending payment and shows a scannable QR to the fake KBZ checkout page.</p>
            </div>
        </div>

        <div class="method-info" data-method-info="AYA Bank Transfer">
            <div class="method-info-icon">AYA</div>
            <div>
                <strong>AYA Bank Transfer</strong>
                <dl class="bank-detail-list">
                    <?php foreach ($bankTransfer as $label => $value): ?>
                        <div>
                            <dt><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></dt>
                            <dd><?= htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8') ?></dd>
                        </div>
                    <?php endforeach; ?>
                </dl>
            </div>
        </div>

        <div id="paymentSlipField">
            <label class="payment-label" for="paymentSlip">Payment slip screenshot</label>
            <label class="payment-slip-upload" for="paymentSlip">
                <span class="payment-slip-icon">Receipt</span>
                <span>
                    <strong id="paymentSlipName">Upload payment slip</strong>
                    <small>JPG, PNG, or WEBP under 5 MB</small>
                </span>
            </label>
            <input id="paymentSlip" name="payment_slip" type="file" accept="image/jpeg,image/png,image/webp" required>
        </div>

        <label class="payment-label" for="payerNote">Note</label>
        <textarea id="payerNote" name="payer_note" rows="3" placeholder="Optional note for admin"></textarea>

        <?php if (!empty($paymentContext['note'])): ?>
            <p class="payment-note"><?= htmlspecialchars($paymentContext['note'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
    </section>

    <div class="payment-actions">
        <a href="<?= $backUrl ?>" class="payment-back">Back</a>
        <button type="submit" class="payment-submit"><?= $submitLabel ?></button>
    </div>
</form>
<script>
    (function () {
        const methodInputs = document.querySelectorAll('input[name="method"]');
        const methodInfos = document.querySelectorAll('[data-method-info]');
        const paymentSlipField = document.getElementById('paymentSlipField');
        const paymentSlipInput = document.getElementById('paymentSlip');
        const paymentSlipName = document.getElementById('paymentSlipName');
        const submitButton = document.querySelector('.payment-submit');

        function syncPaymentForm() {
            const selectedMethod = document.querySelector('input[name="method"]:checked')?.value || '';
            const needsSlip = selectedMethod === 'AYA Bank Transfer';

            methodInfos.forEach((panel) => {
                panel.hidden = panel.dataset.methodInfo !== selectedMethod;
            });

            paymentSlipField.hidden = !needsSlip;
            paymentSlipInput.required = needsSlip;
            submitButton.textContent = selectedMethod === 'KBZ Pay' ? 'Create KBZ Pay QR' : 'Submit slip for review';
        }

        if (paymentSlipInput) {
            paymentSlipInput.addEventListener('change', () => {
                const file = paymentSlipInput.files && paymentSlipInput.files[0];
                paymentSlipName.textContent = file ? file.name : 'Upload payment slip';
            });
        }

        methodInputs.forEach((input) => input.addEventListener('change', syncPaymentForm));
        syncPaymentForm();
    })();
</script>
