<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>KBZ Pay Demo Checkout - <?= APPNAME ?></title>

<style>
:root {
    --kbz-blue: #005baa;
    --kbz-blue-dark: #003f78;
    --kbz-red: #e31e24;
    --ink: #111827;
    --muted: #667085;
    --line: #e4eaf2;
    --soft: #f6f9fd;
    --panel: #ffffff;
    --success: #16a34a;
}

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    min-height: 100vh;
    display: grid;
    place-items: center;
    background: linear-gradient(180deg, #f4f8fd 0%, #eaf2fb 100%);
    color: var(--ink);
    font-family: Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif;
}

.checkout-wrap {
    width: min(100% - 32px, 430px);
    padding: 24px 0;
}

.phone-shell {
    overflow: hidden;
    background: var(--panel);
    border: 1px solid rgba(0, 91, 170, 0.12);
    border-radius: 30px;
    box-shadow: 0 24px 70px rgba(16, 24, 40, 0.14);
}

.checkout-header {
    padding: 22px;
    background: var(--kbz-blue);
    color: #fff;
}

.brand-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.brand {
    display: flex;
    align-items: center;
    gap: 12px;
}

.brand-mark {
    width: 42px;
    height: 42px;
    display: grid;
    place-items: center;
    border-radius: 14px;
    background: #fff;
    color: var(--kbz-blue);
    font-size: 18px;
    font-weight: 900;
}

.brand strong {
    display: block;
    font-size: 17px;
    line-height: 1.1;
}

.brand span {
    display: block;
    margin-top: 3px;
    color: rgba(255,255,255,.72);
    font-size: 12px;
}

.demo-pill {
    padding: 6px 10px;
    border: 1px solid rgba(255,255,255,.3);
    border-radius: 999px;
    color: #fff;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
}

.amount-panel {
    margin-top: 22px;
    padding: 18px;
    border-radius: 20px;
    background: #fff;
    text-align: center;
}

.amount-label {
    margin: 0;
    color: var(--muted);
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
}

.amount {
    margin: 8px 0 0;
    color: var(--kbz-blue-dark);
    font-size: 38px;
    font-weight: 900;
    line-height: 1;
    letter-spacing: -0.04em;
}

.amount span {
    color: var(--kbz-red);
    font-size: 13px;
    letter-spacing: .04em;
}

.stepper {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 7px;
    margin-top: 18px;
}

.step-dot {
    height: 4px;
    border-radius: 999px;
    background: rgba(255,255,255,.26);
}

.step-dot.active,
.step-dot.done {
    background: #fff;
}

.checkout-body {
    padding: 22px;
}

.screen {
    display: none;
}

.screen.active {
    display: block;
}

.screen-title {
    margin: 0 0 14px;
    font-size: 17px;
    font-weight: 850;
    letter-spacing: -0.01em;
}

.merchant-card,
.pin-card,
.processing-card {
    border: 1px solid var(--line);
    border-radius: 18px;
    background: #fff;
}

.merchant-card {
    padding: 16px;
}

.merchant-main {
    display: flex;
    align-items: center;
    gap: 12px;
    padding-bottom: 14px;
    border-bottom: 1px solid var(--line);
}

.merchant-icon {
    width: 42px;
    height: 42px;
    flex: 0 0 42px;
    display: grid;
    place-items: center;
    border-radius: 14px;
    background: var(--soft);
    color: var(--kbz-blue);
    font-size: 14px;
    font-weight: 900;
}

.merchant-main p {
    margin: 0 0 3px;
    color: var(--muted);
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
}

.merchant-main strong {
    display: block;
    font-size: 14px;
}

.details {
    display: grid;
    gap: 12px;
    padding-top: 14px;
}

.row {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 18px;
    font-size: 13px;
}

.row span {
    color: var(--muted);
}

.row strong {
    max-width: 62%;
    text-align: right;
    font-weight: 750;
}

.security-line {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 12px;
    padding: 12px 14px;
    border: 1px solid var(--line);
    border-radius: 16px;
    background: var(--soft);
    font-size: 13px;
}

.security-line strong {
    color: var(--kbz-blue-dark);
}

.timer {
    color: var(--kbz-red);
    font-weight: 900;
}

.pin-card {
    padding: 18px;
    text-align: center;
}

.pin-card p {
    margin: 0 0 14px;
    color: var(--muted);
    font-size: 12px;
    line-height: 1.5;
}

.pin-input {
    width: 100%;
    height: 54px;
    border: 1px solid var(--line);
    border-radius: 16px;
    background: var(--soft);
    color: var(--ink);
    font-size: 24px;
    font-weight: 900;
    letter-spacing: .35em;
    text-align: center;
    outline: none;
}

.pin-input:focus {
    border-color: var(--kbz-blue);
    box-shadow: 0 0 0 4px rgba(0, 91, 170, .11);
}

.demo-pin {
    margin-top: 12px;
    color: var(--kbz-blue-dark);
    font-size: 12px;
    font-weight: 800;
}

.error-text {
    min-height: 18px;
    margin: 10px 0 0;
    color: var(--kbz-red);
    font-size: 12px;
    font-weight: 800;
}

.processing-card {
    padding: 34px 20px;
    text-align: center;
}

.loader {
    width: 50px;
    height: 50px;
    margin: 0 auto 18px;
    border: 4px solid var(--line);
    border-top-color: var(--kbz-blue);
    border-radius: 50%;
    animation: spin .8s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.processing-card h2 {
    margin: 0 0 8px;
    font-size: 18px;
}

.processing-card p {
    margin: 0;
    color: var(--muted);
    font-size: 13px;
    line-height: 1.5;
}

.actions {
    display: grid;
    gap: 10px;
    margin-top: 16px;
}

.actions.two {
    grid-template-columns: 1fr 1fr;
}

.button {
    width: 100%;
    min-height: 48px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 15px;
    border: 0;
    padding: 12px 16px;
    font-size: 14px;
    font-weight: 800;
    text-decoration: none;
    cursor: pointer;
    transition: background .15s ease, transform .12s ease, border-color .15s ease;
}

.button:active {
    transform: translateY(1px);
}

.button-success {
    background: var(--kbz-blue);
    color: #fff;
}

.button-success:hover {
    background: var(--kbz-blue-dark);
}

.button-fail {
    border: 1px solid var(--line);
    background: #fff;
    color: var(--kbz-red);
}

.button-ghost {
    border: 1px solid var(--line);
    background: #fff;
    color: var(--kbz-blue-dark);
}

.notice {
    margin: 14px 0 0;
    color: var(--muted);
    font-size: 11px;
    line-height: 1.45;
    text-align: center;
}

@media (max-width: 480px) {
    body {
        display: block;
        background: #fff;
    }

    .checkout-wrap {
        width: 100%;
        padding: 0;
    }

    .phone-shell {
        min-height: 100vh;
        border: 0;
        border-radius: 0;
        box-shadow: none;
    }

    .checkout-header {
        padding: 20px 18px;
        border-radius: 0 0 24px 24px;
    }

    .checkout-body {
        padding: 20px 18px 24px;
    }

    .amount {
        font-size: 34px;
    }

    .actions.two {
        grid-template-columns: 1fr;
    }
}
</style>
</head>

<body>
<?php
$completedStatus = $completedStatus ?? '';
$isCompleted = in_array($completedStatus, ['success', 'failed'], true);
?>
<main class="checkout-wrap">
    <section class="phone-shell" aria-label="KBZ Pay demo checkout">

        <header class="checkout-header">
            <div class="brand-row">
                <div class="brand">
                    <span class="brand-mark">K</span>
                    <div>
                        <strong>KBZ Pay</strong>
                        <span>Secure checkout</span>
                    </div>
                </div>
                <span class="demo-pill">Demo</span>
            </div>

            <div class="amount-panel">
                <p class="amount-label">Payment amount</p>
                <p class="amount"><?= number_format((float)($payment['amount'] ?? 0)) ?> <span>MMK</span></p>
            </div>

            <div class="stepper" aria-hidden="true">
                <span class="step-dot active" data-step-dot="review"></span>
                <span class="step-dot" data-step-dot="pin"></span>
                <span class="step-dot" data-step-dot="processing"></span>
            </div>
        </header>

        <section class="checkout-body">
            <?php if ($isCompleted): ?>
            <div class="screen active">
                <div class="processing-card">
                    <div style="width:54px;height:54px;margin:0 auto 18px;border-radius:18px;display:grid;place-items:center;background:<?= $completedStatus === 'success' ? '#dcfce7' : '#fee2e2' ?>;color:<?= $completedStatus === 'success' ? '#15803d' : '#b91c1c' ?>;font-size:26px;font-weight:900;">
                        <?= $completedStatus === 'success' ? '&#10003;' : '!' ?>
                    </div>
                    <h2><?= $completedStatus === 'success' ? 'Payment successful' : 'Payment cancelled' ?></h2>
                    <p>
                        <?= $completedStatus === 'success'
                            ? 'The demo KBZ payment is complete. The supplier dashboard is now unlocked on the main app.'
                            : 'The demo KBZ payment was not completed. The supplier can create a new payment from the main app.' ?>
                    </p>
                </div>

                <div class="actions">
                    <a class="button button-success" href="<?= htmlspecialchars($returnUrl ?? URLROOT . '/supplier/dashboard', ENT_QUOTES, 'UTF-8') ?>">Return to app</a>
                </div>
            </div>
            <?php else: ?>
            <div class="screen active" data-screen="review">
                <h1 class="screen-title">Review payment</h1>

                <div class="merchant-card">
                    <div class="merchant-main">
                        <span class="merchant-icon">GP</span>
                        <div>
                            <p>Merchant</p>
                            <strong><?= htmlspecialchars(APPNAME, ENT_QUOTES, 'UTF-8') ?></strong>
                        </div>
                    </div>

                    <div class="details">
                        <div class="row">
                            <span>Payment type</span>
                            <strong>Supplier membership</strong>
                        </div>
                        <div class="row">
                            <span>Supplier</span>
                            <strong><?= htmlspecialchars($supplier['shop_name'] ?? 'Supplier account', ENT_QUOTES, 'UTF-8') ?></strong>
                        </div>
                        <div class="row">
                            <span>Order ID</span>
                            <strong>#<?= (int)($payment['id'] ?? 0) ?></strong>
                        </div>
                    </div>
                </div>

                <div class="security-line">
                    <strong>Session expires</strong>
                    <span class="timer" id="checkoutTimer">05:00</span>
                </div>

                <div class="actions">
                    <button class="button button-success" type="button" id="continueToPin">Continue</button>
                    <a class="button button-fail" href="<?= htmlspecialchars($failUrl, ENT_QUOTES, 'UTF-8') ?>">Cancel Payment</a>
                </div>
            </div>

            <div class="screen" data-screen="pin">
                <h1 class="screen-title">Enter PIN</h1>

                <div class="pin-card">
                    <p>Use demo PIN only.</p>
                    <input class="pin-input" id="demoPin" type="password" inputmode="numeric" maxlength="6" autocomplete="off" aria-label="Demo PIN">
                    <div class="demo-pin">Demo PIN: 123456</div>
                    <p class="error-text" id="pinError"></p>
                </div>

                <div class="actions two">
                    <button class="button button-ghost" type="button" id="backToReview">Back</button>
                    <button class="button button-success" type="button" id="verifyPin">Pay Now</button>
                </div>

                <a class="button button-fail" href="<?= htmlspecialchars($failUrl, ENT_QUOTES, 'UTF-8') ?>" style="margin-top:10px;">Cancel Payment</a>
            </div>

            <div class="screen" data-screen="processing">
                <div class="processing-card">
                    <div class="loader" aria-hidden="true"></div>
                    <h2>Processing payment</h2>
                    <p>Please wait. Do not close this page.</p>
                </div>
            </div>

            <p class="notice">Demo payment simulator. No real KBZ Pay transaction will be made.</p>
            <?php endif; ?>
        </section>
    </section>
</main>

<?php if (!$isCompleted): ?>
<script>
(function () {
    const successUrl = <?= json_encode($successUrl, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    const failUrl = <?= json_encode($failUrl, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    const screens = document.querySelectorAll('[data-screen]');
    const dots = document.querySelectorAll('[data-step-dot]');
    const pinInput = document.getElementById('demoPin');
    const pinError = document.getElementById('pinError');
    const timer = document.getElementById('checkoutTimer');

    let remainingSeconds = 300;
    let isCompleting = false;

    function showScreen(name) {
        screens.forEach((screen) => {
            screen.classList.toggle('active', screen.dataset.screen === name);
        });

        dots.forEach((dot) => {
            const step = dot.dataset.stepDot;
            dot.classList.toggle('active', step === name);
            dot.classList.toggle(
                'done',
                (name === 'pin' && step === 'review') ||
                (name === 'processing' && step !== 'processing')
            );
        });

        if (name === 'pin') {
            pinInput.focus();
        }
    }

    function formatTime(seconds) {
        const minutes = Math.floor(seconds / 60).toString().padStart(2, '0');
        const secs = (seconds % 60).toString().padStart(2, '0');
        return `${minutes}:${secs}`;
    }

    const countdown = setInterval(() => {
        if (isCompleting) {
            clearInterval(countdown);
            return;
        }

        remainingSeconds -= 1;
        timer.textContent = formatTime(Math.max(remainingSeconds, 0));

        if (remainingSeconds <= 0) {
            clearInterval(countdown);
            window.location.href = failUrl;
        }
    }, 1000);

    document.getElementById('continueToPin').addEventListener('click', () => {
        pinError.textContent = '';
        showScreen('pin');
    });

    document.getElementById('backToReview').addEventListener('click', () => {
        pinError.textContent = '';
        showScreen('review');
    });

    document.getElementById('verifyPin').addEventListener('click', () => {
        if (pinInput.value !== '123456') {
            pinError.textContent = 'Incorrect PIN. Use 123456.';
            pinInput.select();
            return;
        }

        isCompleting = true;
        pinError.textContent = '';
        showScreen('processing');

        setTimeout(() => {
            window.location.href = successUrl;
        }, 1600);
    });

    pinInput.addEventListener('input', () => {
        pinInput.value = pinInput.value.replace(/\D/g, '').slice(0, 6);
        pinError.textContent = '';
    });

    pinInput.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            document.getElementById('verifyPin').click();
        }
    });
})();
</script>
<?php endif; ?>
</body>
</html>
