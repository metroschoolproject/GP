<?php
$paymentContext = $paymentContext ?? [];
$amount = (float)($paymentContext['amount'] ?? 0);
$currency = htmlspecialchars($paymentContext['currency'] ?? 'MMK', ENT_QUOTES, 'UTF-8');
$methods = $paymentContext['methods'] ?? [];
$summary = $paymentContext['summary'] ?? [];
$action = htmlspecialchars($paymentContext['action'] ?? '', ENT_QUOTES, 'UTF-8');
$backUrl = htmlspecialchars($paymentContext['backUrl'] ?? URLROOT . '/main/home', ENT_QUOTES, 'UTF-8');
$submitLabel = htmlspecialchars($paymentContext['submitLabel'] ?? 'Submit payment', ENT_QUOTES, 'UTF-8');
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
            <?php foreach ($methods as $index => $method): ?>
                <?php $methodId = 'payment_method_' . $index; ?>
                <label class="method-option" for="<?= $methodId ?>">
                    <input id="<?= $methodId ?>" type="radio" name="method" value="<?= htmlspecialchars($method, ENT_QUOTES, 'UTF-8') ?>" <?= $index === 0 ? 'checked' : '' ?>>
                    <span><?= htmlspecialchars($method, ENT_QUOTES, 'UTF-8') ?></span>
                </label>
            <?php endforeach; ?>
        </div>

        <label class="payment-label" for="transactionRef">Transaction reference</label>
        <input id="transactionRef" required name="transaction_ref" type="text" placeholder="Enter payment transaction ID or receipt reference">

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
