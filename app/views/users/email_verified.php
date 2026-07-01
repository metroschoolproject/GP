<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="<?= URLROOT ?>/public/images/home/gp_logo.png">
    <title>Email Verified - <?= APPNAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #f5e8d9;
            --card: #faf5ef;
            --accent: #6d4c5b;
            --muted: #b79c8b;
            --border: #ead8c7;
            --white: #fcf8f5;
            --success: #16a34a;
            --header-font: 'Playfair Display', Georgia, serif;
            --body-font: 'Poppins', system-ui, sans-serif;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            min-height: 100vh;
            background: var(--bg);
            font-family: var(--body-font);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .card {
            width: 100%;
            max-width: 460px;
            background: var(--card);
            border-radius: 24px;
            border: 1.5px solid #dbc6b0;
            box-shadow: 0 20px 40px rgba(15,23,42,0.08), 0 0 0 1px rgba(212,160,71,0.06), 0 0 40px rgba(212,160,71,0.04);
            padding: 40px 36px 36px;
            text-align: center;
            opacity: 0;
            transform: translateY(12px);
            animation: fadeUp 0.7s cubic-bezier(0.4,0,0.2,1) 0.1s forwards;
        }
        @keyframes fadeUp {
            to { opacity: 1; transform: translateY(0); }
        }
        .icon-circle {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            position: relative;
        }
        .icon-circle.success {
            background: linear-gradient(135deg, rgba(22,163,74,0.1), rgba(22,163,74,0.05));
            border: 2px solid rgba(22,163,74,0.2);
        }
        .icon-circle.error {
            background: linear-gradient(135deg, rgba(185,75,75,0.1), rgba(185,75,75,0.05));
            border: 2px solid rgba(185,75,75,0.2);
        }
        .icon-circle svg.success-icon { color: var(--success); }
        .icon-circle svg.error-icon { color: #b94b4b; }
        h1 {
            font-family: var(--header-font);
            font-size: 28px;
            font-weight: 600;
            color: var(--accent);
            margin-bottom: 8px;
            line-height: 1.2;
        }
        .subtitle {
            font-size: 14px;
            color: var(--muted);
            line-height: 1.6;
            margin-bottom: 28px;
        }
        .divider {
            height: 1px;
            background: var(--border);
            margin: 0 0 24px;
            position: relative;
        }
        .divider::after {
            content: '';
            position: absolute;
            left: 50%;
            top: 50%;
            width: 8px;
            height: 8px;
            border: 1px solid var(--border);
            background: var(--card);
            transform: translate(-50%, -50%) rotate(45deg);
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--accent) 0%, #8b5e6f 100%);
            color: var(--white);
            font-family: var(--body-font);
            font-size: 15px;
            font-weight: 600;
            letter-spacing: 0.3px;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(109,76,91,0.22);
            transition: transform 0.4s cubic-bezier(0.4,0,0.2,1), box-shadow 0.4s cubic-bezier(0.4,0,0.2,1);
            text-decoration: none;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(109,76,91,0.28);
        }
        .btn.success-btn {
            background: linear-gradient(135deg, var(--success) 0%, #22c55e 100%);
            box-shadow: 0 4px 14px rgba(22,163,74,0.22);
        }
        .btn.success-btn:hover {
            box-shadow: 0 12px 28px rgba(22,163,74,0.28);
        }
        /* Checkmark animation */
        .check-path {
            stroke-dasharray: 30;
            stroke-dashoffset: 30;
            animation: drawCheck 0.6s cubic-bezier(0.4,0,0.2,1) 0.4s forwards;
        }
        @keyframes drawCheck {
            to { stroke-dashoffset: 0; }
        }
    </style>
</head>
<body>
    <div class="card">
        <?php if (!empty($verified)): ?>
        <div class="icon-circle success">
            <svg class="success-icon" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10" opacity="0.3"/>
                <path class="check-path" d="M8 12l3 3 5-6"/>
            </svg>
        </div>
        <h1>Email verified</h1>
        <p class="subtitle"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
        <div class="divider"></div>
        <?php if (!empty($isPendingSupplier)): ?>
        <p class="subtitle" style="margin-bottom:20px;font-size:13px;color:var(--accent);background:rgba(109,76,91,0.06);border-radius:10px;padding:12px 14px;line-height:1.5;">
            ⏳ You will receive an email once your application is approved. After approval, you can log in to access your supplier dashboard.
        </p>
        <a href="<?= URLROOT ?>/main/home" class="btn">
            Browse as guest
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
        </a>
        <?php else: ?>
        <a href="<?= URLROOT . '/' . $redirect ?>" class="btn success-btn">
            Continue
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
        </a>
        <?php endif; ?>
        <?php else: ?>
        <div class="icon-circle error">
            <svg class="error-icon" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10" opacity="0.3"/>
                <line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
            </svg>
        </div>
        <h1>Link expired</h1>
        <p class="subtitle"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
        <div class="divider"></div>
        <a href="<?= URLROOT ?>/users/auth" class="btn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            Back to sign in
        </a>
        <?php endif; ?>
    </div>
</body>
</html>
