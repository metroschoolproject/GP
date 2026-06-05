<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KBZ Pay Demo Checkout - <?= APPNAME ?></title>
    <style>
        :root {
            --kbz-blue: #0057a8;
            --kbz-red: #e51b23;
            --ink: #172033;
            --muted: #637083;
            --line: #d9e2ef;
            --panel: #ffffff;
            --bg: #f3f7fc;
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, #f7fbff 0%, var(--bg) 100%);
            color: var(--ink);
            font-family: Arial, Helvetica, sans-serif;
        }

        .checkout {
            width: min(100% - 32px, 430px);
            border: 1px solid var(--line);
            border-radius: 8px;
            background: var(--panel);
            box-shadow: 0 24px 60px rgba(25, 48, 83, 0.16);
            overflow: hidden;
        }

        .checkout-header {
            padding: 22px 24px;
            background: var(--kbz-blue);
            color: #fff;
        }

        .brand {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .brand strong {
            font-size: 22px;
            letter-spacing: 0.02em;
        }

        .badge {
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.16);
            padding: 6px 10px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .checkout-body {
            padding: 24px;
        }

        .label {
            margin: 0;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--muted);
        }

        .amount {
            margin: 6px 0 22px;
            font-size: 38px;
            font-weight: 800;
        }

        .amount span {
            font-size: 14px;
            color: var(--kbz-red);
        }

        .details {
            display: grid;
            gap: 12px;
            border-top: 1px solid var(--line);
            border-bottom: 1px solid var(--line);
            padding: 18px 0;
        }

        .row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 18px;
            font-size: 14px;
        }

        .row span {
            color: var(--muted);
        }

        .row strong {
            max-width: 58%;
            text-align: right;
        }

        .actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-top: 22px;
        }

        .button {
            display: inline-flex;
            min-height: 46px;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            padding: 12px 16px;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
        }

        .button-success {
            background: var(--kbz-blue);
            color: #fff;
        }

        .button-fail {
            border: 1px solid var(--line);
            color: var(--kbz-red);
        }

        .note {
            margin: 16px 0 0;
            font-size: 12px;
            line-height: 1.5;
            color: var(--muted);
        }
    </style>
</head>
<body>
    <main class="checkout">
        <header class="checkout-header">
            <div class="brand">
                <strong>KBZ Pay</strong>
                <span class="badge">Demo gateway</span>
            </div>
        </header>

        <section class="checkout-body">
            <p class="label">Amount</p>
            <p class="amount"><?= number_format((float)($payment['amount'] ?? 0)) ?> <span>MMK</span></p>

            <div class="details">
                <div class="row">
                    <span>Merchant</span>
                    <strong><?= htmlspecialchars(APPNAME, ENT_QUOTES, 'UTF-8') ?></strong>
                </div>
                <div class="row">
                    <span>Payment type</span>
                    <strong>Supplier membership</strong>
                </div>
                <div class="row">
                    <span>Supplier</span>
                    <strong><?= htmlspecialchars($supplier['shop_name'] ?? 'Supplier account', ENT_QUOTES, 'UTF-8') ?></strong>
                </div>
            </div>

            <div class="actions">
                <a class="button button-success" href="<?= htmlspecialchars($successUrl, ENT_QUOTES, 'UTF-8') ?>">Pay Success</a>
                <a class="button button-fail" href="<?= htmlspecialchars($failUrl, ENT_QUOTES, 'UTF-8') ?>">Pay Fail</a>
            </div>

            <p class="note">This is a local payment simulator for development. It does not connect to real KBZ Pay services.</p>
        </section>
    </main>
</body>
</html>
