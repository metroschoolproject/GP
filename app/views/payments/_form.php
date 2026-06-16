<?php
$paymentContext = $paymentContext ?? [];
$amount = (float)($paymentContext['amount'] ?? 0);
$currency = htmlspecialchars($paymentContext['currency'] ?? 'MMK', ENT_QUOTES, 'UTF-8');
$methods = $paymentContext['methods'] ?? [];
$summary = $paymentContext['summary'] ?? [];
$action = htmlspecialchars($paymentContext['action'] ?? '', ENT_QUOTES, 'UTF-8');
$backUrl = htmlspecialchars($paymentContext['backUrl'] ?? URLROOT . '/main/home', ENT_QUOTES, 'UTF-8');
$submitLabel = htmlspecialchars($paymentContext['submitLabel'] ?? 'Continue', ENT_QUOTES, 'UTF-8');
?>

<form method="POST" action="<?= $action ?>" class="payment-form" novalidate>
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
            <?php $index = 0; foreach ($methods as $methodId => $methodLabel): ?>
                <?php $inputId = 'payment_method_' . $index; ?>
                <label class="method-option" for="<?= $inputId ?>">
                    <input id="<?= $inputId ?>" type="radio" name="method" value="<?= htmlspecialchars($methodId, ENT_QUOTES, 'UTF-8') ?>" <?= $index === 0 ? 'checked' : '' ?>>
                    <span><?= htmlspecialchars($methodLabel, ENT_QUOTES, 'UTF-8') ?></span>
                </label>
                <?php $index++; ?>
            <?php endforeach; ?>
        </div>

        <div class="method-info" data-method-info="2c2p_mmqr" hidden>
            <div class="method-info-icon">QR</div>
            <div>
                <strong>MM QR Payment</strong>
                <p>Instant QR code payment. You'll scan the code with your Myanmar QR app to complete the payment.</p>
            </div>
        </div>

        <div class="method-info" data-method-info="2c2p_card" hidden>
            <div class="method-info-icon">💳</div>
            <div>
                <strong>Visa Card Payment</strong>
                <p>Pay with your Visa or Mastercard. You'll be redirected to our secure payment gateway.</p>
            </div>
        </div>

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

        function syncPaymentForm() {
            const selectedMethod = document.querySelector('input[name="method"]:checked')?.value || '';

            methodInfos.forEach((panel) => {
                panel.hidden = panel.dataset.methodInfo !== selectedMethod;
            });
        }

        methodInputs.forEach((input) => input.addEventListener('change', syncPaymentForm));
        syncPaymentForm();
    })();
</script>
