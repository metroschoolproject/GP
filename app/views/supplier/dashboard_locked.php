<?php
$supplierName = htmlspecialchars($supplier['shop_name'] ?? 'Your supplier account', ENT_QUOTES, 'UTF-8');
$serviceName = htmlspecialchars($supplier['service_name'] ?? 'Service information', ENT_QUOTES, 'UTF-8');
$paymentMethod = htmlspecialchars($payment['method'] ?? '-', ENT_QUOTES, 'UTF-8');
$paymentRef = htmlspecialchars($payment['transaction_ref'] ?? '-', ENT_QUOTES, 'UTF-8');
$paymentDate = !empty($payment['created_at']) ? htmlspecialchars(date('M j, Y', strtotime($payment['created_at'])), ENT_QUOTES, 'UTF-8') : '-';
$isPaymentPending = ($lockState ?? '') === 'payment_pending';
$isPaymentRequired = ($lockState ?? '') === 'payment_required';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Dashboard Locked - <?= APPNAME ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --accent: #673049;
            --bg: #fbfbf9;
            --border: #e7e5e4;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: var(--bg);
            color: #1c1917;
            font-family: Inter, system-ui, -apple-system, sans-serif;
        }

        .blurred-dashboard {
            position: fixed;
            inset: 0;
            opacity: 0.32;
            filter: blur(3px);
            pointer-events: none;
            overflow: hidden;
            padding: 22px;
            display: grid;
            grid-template-columns: 1fr 320px;
            gap: 18px;
        }

        .preview-grid {
            display: grid;
            gap: 14px;
        }

        .preview-cards {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
        }

        .preview-card,
        .preview-chart,
        .preview-side {
            border: 1px solid var(--border);
            border-radius: 18px;
            background: white;
            box-shadow: 0 1px 2px rgba(28, 25, 23, 0.05);
        }

        .preview-card {
            height: 136px;
        }

        .preview-chart {
            height: 320px;
        }

        .preview-side {
            min-height: 470px;
        }

        .lock-shell {
            position: relative;
            z-index: 2;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 18px;
            background: linear-gradient(180deg, rgba(251, 251, 249, 0.72), rgba(251, 251, 249, 0.94));
        }

        .lock-panel {
            width: min(100%, 680px);
            border: 1px solid rgba(103, 48, 73, 0.16);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.96);
            box-shadow: 0 30px 80px rgba(28, 25, 23, 0.16);
            overflow: hidden;
        }

        .lock-header {
            border-bottom: 1px solid var(--border);
            padding: 26px;
        }

        .lock-badge {
            display: inline-flex;
            border: 1px solid rgba(103, 48, 73, 0.18);
            border-radius: 999px;
            background: #fcefe8;
            padding: 6px 10px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--accent);
        }

        .lock-title {
            margin: 14px 0 8px;
            font-size: clamp(28px, 5vw, 42px);
            line-height: 1.05;
            letter-spacing: -0.03em;
        }

        .lock-copy {
            margin: 0;
            max-width: 560px;
            color: #57534e;
            font-size: 14px;
            line-height: 1.7;
        }

        .lock-body {
            display: grid;
            gap: 14px;
            padding: 22px 26px 26px;
        }

        .status-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .status-card {
            border: 1px solid var(--border);
            border-radius: 8px;
            background: #fff;
            padding: 14px;
        }

        .status-card span {
            display: block;
            margin-bottom: 5px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #a8a29e;
        }

        .status-card strong {
            display: block;
            color: #1c1917;
            font-size: 14px;
        }

        .lock-actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 12px;
            border-top: 1px solid var(--border);
            padding-top: 18px;
        }

        .primary-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            background: var(--accent);
            padding: 12px 18px;
            color: white;
            font-size: 13px;
            font-weight: 800;
            text-decoration: none;
            box-shadow: 0 14px 28px rgba(103, 48, 73, 0.22);
        }

        .secondary-action {
            color: #78716c;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
        }

        @media (max-width: 640px) {
            .blurred-dashboard {
                grid-template-columns: 1fr;
            }

            .preview-cards {
                grid-template-columns: 1fr;
            }

            .preview-side {
                display: none;
            }

            .status-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="blurred-dashboard" aria-hidden="true">
        <div class="preview-grid">
            <div class="preview-cards">
                <div class="preview-card"></div>
                <div class="preview-card"></div>
                <div class="preview-card"></div>
            </div>
            <div class="preview-chart"></div>
        </div>
        <div class="preview-side"></div>
    </div>

    <main class="lock-shell">
        <section class="lock-panel">
            <div class="lock-header">
                <span class="lock-badge"><?= $isPaymentPending ? 'Payment under review' : 'Dashboard locked' ?></span>
                <h1 class="lock-title">
                    <?= $isPaymentPending ? 'Your payment is waiting for admin verification.' : 'Complete your membership payment to unlock the dashboard.' ?>
                </h1>
                <p class="lock-copy">
                    Your supplier profile has been approved. Full dashboard tools stay locked until the membership payment is submitted and verified by admin.
                </p>
            </div>

            <div class="lock-body">
                <div class="status-grid">
                    <div class="status-card">
                        <span>Business</span>
                        <strong><?= $supplierName ?></strong>
                    </div>
                    <div class="status-card">
                        <span>Service</span>
                        <strong><?= $serviceName ?></strong>
                    </div>
                    <div class="status-card">
                        <span>Profile approval</span>
                        <strong>Approved</strong>
                    </div>
                    <div class="status-card">
                        <span>Payment status</span>
                        <strong><?= $isPaymentPending ? 'Submitted for review' : 'Required' ?></strong>
                    </div>
                    <?php if ($isPaymentPending): ?>
                        <div class="status-card">
                            <span>Method</span>
                            <strong><?= $paymentMethod ?></strong>
                        </div>
                        <div class="status-card">
                            <span>Reference</span>
                            <strong><?= $paymentRef ?></strong>
                        </div>
                        <div class="status-card">
                            <span>Submitted</span>
                            <strong><?= $paymentDate ?></strong>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="lock-actions">
                    <?php if ($isPaymentRequired): ?>
                        <a class="primary-action" href="<?= URLROOT ?>/payments/supplierFee">Pay membership fee</a>
                    <?php endif; ?>
                    <a class="secondary-action" href="<?= URLROOT ?>/main/home">Back home</a>
                    <a class="secondary-action" href="<?= URLROOT ?>/users/logout">Sign out</a>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
