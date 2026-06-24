<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email - <?= APPNAME ?></title>
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
            position: relative;
            overflow: hidden;
            opacity: 0;
            transform: translateY(12px);
            animation: fadeUp 0.7s cubic-bezier(0.4,0,0.2,1) 0.1s forwards;
        }
        @keyframes fadeUp {
            to { opacity: 1; transform: translateY(0); }
        }
        .icon-circle {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(109,76,91,0.08), rgba(212,160,71,0.08));
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        .icon-circle svg {
            color: var(--accent);
        }
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
        .email-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(109,76,91,0.06);
            border: 1px solid rgba(109,76,91,0.1);
            border-radius: 8px;
            padding: 8px 14px;
            font-size: 13px;
            font-weight: 500;
            color: var(--accent);
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
        .help-text {
            margin-top: 20px;
            font-size: 12px;
            color: var(--muted);
        }
        .help-text a {
            color: var(--accent);
            text-decoration: underline;
            text-underline-offset: 2px;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon-circle">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
            </svg>
        </div>
        <h1>Check your email</h1>
        <p class="subtitle">We sent a verification link to your inbox. Open it to activate your account.</p>
        <?php if (!empty($email)): ?>
        <div class="email-badge">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
            <?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>
        </div>
        <?php endif; ?>
        <div class="divider"></div>
        <a href="<?= URLROOT ?>/users/auth" class="btn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            Back to sign in
        </a>
        <p class="help-text">Didn't receive it? Check your spam folder or <a href="<?= URLROOT ?>/users/auth">try again</a>.</p>
    </div>
</body>
</html>
