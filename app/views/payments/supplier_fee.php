<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Payment - <?= APPNAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Pinyon+Script&family=Cormorant+Garamond:ital,wght@0,400;0,500;1,400&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --accent: #5d3043;
            --accent-hover: #442231;
            --gold: #b38a52;
            --paper: #fff7ed;
            --panel: rgba(255, 250, 245, 0.82);
            --line: rgba(179, 138, 82, 0.38);
            --body-font: "Cormorant Garamond", Georgia, serif;
            --header-font: "Pinyon Script", cursive;
            --ui-font: system-ui, -apple-system, sans-serif;
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            margin: 0;
            font-family: var(--body-font);
            color: rgba(38, 20, 28, 0.92);
            background:
                linear-gradient(125deg, rgba(255, 248, 240, 0.28), transparent 32%),
                linear-gradient(145deg, #e8b4b8 0%, #ca858f 45%, #965a68 100%);
        }

        .payment-shell {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 48px 18px;
        }

        .payment-surface {
            width: min(100%, 860px);
            position: relative;
            isolation: isolate;
            overflow: hidden;
            border: 1px solid rgba(255, 247, 237, 0.58);
            border-radius: 4px;
            background:
                linear-gradient(90deg, rgba(179, 138, 82, 0.13), transparent 150px),
                rgba(255, 247, 237, 0.95);
            box-shadow: 0 34px 90px rgba(57, 19, 35, 0.28);
        }

        .payment-surface::before {
            content: "";
            position: absolute;
            inset: 18px;
            z-index: 1;
            pointer-events: none;
            border: 1px solid rgba(179, 138, 82, 0.4);
        }

        .payment-surface::after {
            content: "";
            position: absolute;
            top: 0;
            bottom: 0;
            left: 82px;
            z-index: 1;
            width: 1px;
            pointer-events: none;
            background: linear-gradient(to bottom, transparent, rgba(179, 138, 82, 0.64) 18%, rgba(179, 138, 82, 0.64) 82%, transparent);
        }

        .payment-content {
            position: relative;
            z-index: 2;
            padding: 46px 54px 46px 124px;
        }

        .payment-eyebrow,
        .payment-kicker,
        .payment-label,
        .summary-row span {
            font-family: var(--ui-font);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: rgba(93, 48, 67, 0.68);
        }

        .payment-title {
            margin: 4px 0 6px;
            font-family: var(--header-font);
            font-size: clamp(46px, 8vw, 72px);
            font-weight: 600;
            line-height: 1;
            color: var(--accent);
        }

        .payment-intro {
            max-width: 560px;
            margin: 0 0 28px;
            font-family: var(--ui-font);
            font-size: 14px;
            line-height: 1.65;
            color: rgba(38, 20, 28, 0.72);
        }

        .payment-alert {
            margin-bottom: 20px;
            border: 1px solid rgba(190, 18, 60, 0.18);
            border-radius: 3px;
            background: rgba(255, 241, 242, 0.86);
            padding: 12px 14px;
            font-family: var(--ui-font);
            font-size: 13px;
            color: #be123c;
        }

        .payment-form {
            display: grid;
            gap: 16px;
        }

        .payment-panel {
            border: 1px solid var(--line);
            border-radius: 3px;
            background: var(--panel);
            padding: 18px;
        }

        .payment-summary {
            display: grid;
            grid-template-columns: minmax(180px, 0.8fr) 1.2fr;
            gap: 22px;
            align-items: start;
        }

        .payment-amount {
            margin: 4px 0 0;
            font-size: 42px;
            line-height: 1;
            color: rgba(38, 20, 28, 0.92);
        }

        .payment-amount span {
            font-family: var(--ui-font);
            font-size: 12px;
            letter-spacing: 0.12em;
            color: var(--gold);
        }

        .summary-list {
            display: grid;
            gap: 10px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            gap: 18px;
            border-bottom: 1px solid rgba(179, 138, 82, 0.18);
            padding-bottom: 10px;
        }

        .summary-row:last-child {
            border-bottom: 0;
            padding-bottom: 0;
        }

        .summary-row strong {
            max-width: 58%;
            text-align: right;
            font-family: var(--body-font);
            font-size: 17px;
            font-weight: 500;
        }

        .payment-label {
            display: block;
            margin: 0 0 8px;
        }

        .method-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
            margin-bottom: 16px;
        }

        .method-option {
            cursor: pointer;
        }

        .method-option input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .method-option span {
            display: flex;
            min-height: 46px;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(179, 138, 82, 0.34);
            border-radius: 3px;
            background: rgba(255, 250, 245, 0.72);
            padding: 10px 12px;
            font-family: var(--ui-font);
            font-size: 13px;
            font-weight: 700;
            color: rgba(93, 48, 67, 0.78);
            transition: border-color 0.18s, box-shadow 0.18s, background 0.18s;
        }

        .method-option input:checked + span {
            border-color: var(--accent);
            background: rgba(247, 236, 236, 0.82);
            box-shadow: 0 0 0 3px rgba(179, 138, 82, 0.15);
        }

        .payment-form input[type="text"],
        .payment-form textarea {
            width: 100%;
            border: 1px solid rgba(179, 138, 82, 0.34);
            border-radius: 3px;
            background: rgba(255, 250, 245, 0.78);
            padding: 14px 15px;
            font-family: var(--ui-font);
            font-size: 14px;
            color: rgba(38, 20, 28, 0.9);
            outline: none;
        }

        .payment-form input[type="text"]:focus,
        .payment-form textarea:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(179, 138, 82, 0.16);
        }

        .payment-form textarea {
            margin-bottom: 12px;
            resize: vertical;
        }

        .payment-note {
            margin: 0;
            font-family: var(--ui-font);
            font-size: 12px;
            line-height: 1.55;
            color: rgba(38, 20, 28, 0.62);
        }

        .payment-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            padding-top: 4px;
        }

        .payment-back {
            font-family: var(--ui-font);
            font-size: 13px;
            font-weight: 700;
            color: rgba(93, 48, 67, 0.68);
            text-decoration: none;
        }

        .payment-submit {
            border: 0;
            border-radius: 3px;
            background: var(--accent);
            padding: 13px 24px;
            font-family: var(--ui-font);
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 0.04em;
            color: white;
            cursor: pointer;
            box-shadow: 0 14px 28px rgba(64, 20, 35, 0.24);
        }

        .payment-submit:hover {
            background: var(--accent-hover);
        }

        @media (max-width: 760px) {
            .payment-surface::after {
                display: none;
            }

            .payment-content {
                padding: 34px 28px;
            }

            .payment-summary,
            .method-grid {
                grid-template-columns: 1fr;
            }

            .summary-row strong {
                max-width: 62%;
            }
        }
    </style>
</head>
<body>
    <main class="payment-shell">
        <section class="payment-surface">
            <div class="payment-content">
                <p class="payment-eyebrow"><?= htmlspecialchars($paymentContext['eyebrow'] ?? 'Payment', ENT_QUOTES, 'UTF-8') ?></p>
                <h1 class="payment-title"><?= htmlspecialchars($paymentContext['title'] ?? 'Payment', ENT_QUOTES, 'UTF-8') ?></h1>
                <p class="payment-intro"><?= htmlspecialchars($paymentContext['intro'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>

                <?php if (!empty($message)): ?>
                    <div class="payment-alert"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>

                <?php require APPROOT . '/views/payments/_form.php'; ?>
            </div>
        </section>
    </main>
</body>
</html>
