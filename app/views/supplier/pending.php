<?php
$supplierName = htmlspecialchars($supplier['shop_name'] ?? 'Your supplier account', ENT_QUOTES, 'UTF-8');
$supplierStatus = htmlspecialchars(ucfirst($supplier['status'] ?? 'pending'), ENT_QUOTES, 'UTF-8');
$serviceName = htmlspecialchars($supplier['service_name'] ?? 'Service information', ENT_QUOTES, 'UTF-8');
$emailAddress = htmlspecialchars($email ?? '', ENT_QUOTES, 'UTF-8');
$submittedDate = !empty($supplier['created_at']) ? date('M j, Y', strtotime($supplier['created_at'])) : 'Recently';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Status - <?= APPNAME ?></title>
    <?php $dashboardCssVersion = file_exists(APPROOT . '/../public/css/app.css') ? filemtime(APPROOT . '/../public/css/app.css') : time(); ?>
    <link rel="stylesheet" href="<?= URLROOT ?>/public/css/app.css?v=<?= $dashboardCssVersion ?>">
    <style>
        :root {
            --env-bg: #F4F1EE;
            --env-border: #ead8c7;
            --paper: #FFFFFF;
            --accent: #6d4c5b;
            --accent-hover: #7b5c69;
            --surface: #FFFFFF;
            --soft-hover: #eddecc;
            --muted: #b79c8b;
            --body: #7b5c69;
            --text: #111827;
            --warning-bg: #FFFBEB;
            --warning-text: #92400e;
            --info-bg: #fdf6ee;
            --info-border: #f0d9b5;
            --info-text: #8a6534;
            --body-font: system-ui, -apple-system, sans-serif;
        }

        body {
            min-height: 100vh;
            margin: 0;
            font-family: var(--body-font);
            color: var(--text);
            background: var(--env-bg);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        /* --- Animated spinner --- */
        .review-spinner {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: var(--soft-hover);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            position: relative;
        }

        .review-spinner::before {
            content: '';
            position: absolute;
            inset: -6px;
            border-radius: 50%;
            border: 2px solid transparent;
            border-top-color: var(--accent);
            animation: spin-ring 1.4s linear infinite;
        }

        .review-spinner::after {
            content: '';
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: var(--accent);
            opacity: 0.18;
            animation: pulse-dot 2s ease-in-out infinite;
        }

        @keyframes spin-ring {
            to { transform: rotate(360deg); }
        }

        @keyframes pulse-dot {
            0%, 100% { transform: scale(0.8); opacity: 0.12; }
            50% { transform: scale(1.2); opacity: 0.28; }
        }

        /* --- Card --- */
        .review-card {
            width: 100%;
            max-width: 28rem;
            background: var(--surface);
            border: 1px solid var(--env-border);
            border-radius: 1rem;
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.06);
            padding: 2.5rem 2rem 2rem;
            text-align: center;
        }

        .review-title {
            color: var(--accent);
            font-size: 1.75rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            margin: 0 0 0.5rem;
        }

        .review-copy {
            color: var(--body);
            font-size: 0.875rem;
            line-height: 1.65;
            margin: 0 auto 1.75rem;
            max-width: 22rem;
        }

        /* --- Status summary --- */
        .review-summary {
            background: var(--paper);
            border: 1px solid var(--env-border);
            border-radius: 0.75rem;
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            margin-bottom: 1.75rem;
            text-align: left;
        }

        .review-summary-info {
            min-width: 0;
        }

        .review-summary-name {
            font-size: 0.9375rem;
            font-weight: 700;
            color: var(--text);
            margin: 0 0 0.125rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .review-summary-service {
            font-size: 0.75rem;
            color: var(--muted);
            margin: 0;
        }

        .review-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            background: var(--warning-bg);
            color: var(--warning-text);
            border: 1px solid var(--env-border);
            border-radius: 9999px;
            padding: 0.3rem 0.75rem;
            font-size: 0.625rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .badge-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--warning-text);
            animation: pulse-dot 2s ease-in-out infinite;
        }

        /* --- Timeline --- */
        .review-timeline {
            display: flex;
            flex-direction: column;
            gap: 0;
            margin-bottom: 1.75rem;
            text-align: left;
            position: relative;
            padding-left: 1.75rem;
        }

        .review-timeline::before {
            content: '';
            position: absolute;
            left: 11px;
            top: 12px;
            bottom: 12px;
            width: 2px;
            background: var(--env-border);
            border-radius: 9999px;
        }

        .timeline-step {
            display: flex;
            align-items: flex-start;
            gap: 0.875rem;
            padding: 0.625rem 0;
            position: relative;
        }

        .timeline-dot {
            position: absolute;
            left: -1.75rem;
            top: 0.625rem;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.625rem;
            font-weight: 800;
            flex-shrink: 0;
            z-index: 1;
        }

        .timeline-dot.completed {
            background: var(--accent);
            color: #FFFFFF;
        }

        .timeline-dot.active {
            background: var(--surface);
            border: 2px solid var(--accent);
            color: var(--accent);
            animation: pulse-border 2.5s ease-in-out infinite;
        }

        .timeline-dot.pending {
            background: var(--surface);
            border: 2px solid var(--env-border);
            color: var(--muted);
        }

        @keyframes pulse-border {
            0%, 100% { box-shadow: 0 0 0 0 rgba(109, 76, 91, 0.15); }
            50% { box-shadow: 0 0 0 6px rgba(109, 76, 91, 0); }
        }

        .timeline-content {
            min-width: 0;
            flex: 1;
        }

        .timeline-label {
            font-size: 0.8125rem;
            font-weight: 700;
            color: var(--text);
            margin: 0;
        }

        .timeline-date {
            font-size: 0.6875rem;
            color: var(--muted);
            margin: 0.125rem 0 0;
        }

        /* --- Info tip --- */
        .review-tip {
            background: var(--info-bg);
            border: 1px solid var(--info-border);
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
            display: flex;
            align-items: center;
            gap: 0.625rem;
            text-align: left;
            margin-bottom: 1.75rem;
            font-size: 0.8125rem;
            color: var(--info-text);
            line-height: 1.5;
        }

        .review-tip-icon {
            flex-shrink: 0;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0.7;
        }

        /* --- Actions --- */
        .review-actions {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            padding-top: 0.25rem;
        }

        .btn-primary {
            background: var(--accent);
            color: #FFFFFF;
            border: none;
            border-radius: 0.75rem;
            padding: 0.625rem 1.5rem;
            font-size: 0.8125rem;
            font-weight: 700;
            text-decoration: none;
            transition: background 0.15s;
            cursor: pointer;
        }

        .btn-primary:hover {
            background: var(--accent-hover);
        }

        .btn-ghost {
            color: var(--body);
            background: none;
            border: none;
            font-size: 0.8125rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: color 0.15s;
        }

        .btn-ghost:hover {
            color: var(--accent);
        }

        .review-support {
            margin-top: 1.25rem;
            font-size: 0.75rem;
            color: var(--muted);
        }

        .review-support a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
        }

        .review-support a:hover {
            text-decoration: underline;
        }

        /* --- Responsive --- */
        @media (max-width: 480px) {
            body { padding: 1rem 0.75rem; }
            .review-card { padding: 2rem 1.25rem 1.5rem; }
            .review-summary { flex-direction: column; align-items: flex-start; gap: 0.5rem; }
            .review-actions { flex-direction: column; gap: 0.5rem; }
            .btn-primary { width: 100%; text-align: center; }
        }
    </style>
</head>
<body>
    <main>
        <section class="review-card">
            <!-- Animated spinner -->
            <div class="review-spinner" aria-hidden="true"></div>

            <!-- Header -->
            <h1 class="review-title">Review in progress</h1>
            <p class="review-copy">
                Thanks for submitting your application. Our admin team is reviewing your supplier profile — we'll notify you by email once it's approved.
            </p>

            <!-- Status summary -->
            <div class="review-summary">
                <div class="review-summary-info">
                    <p class="review-summary-name"><?= $supplierName ?></p>
                    <p class="review-summary-service"><?= $serviceName ?></p>
                </div>
                <span class="review-badge">
                    <span class="badge-dot"></span>
                    <?= $supplierStatus ?>
                </span>
            </div>

            <!-- Timeline -->
            <div class="review-timeline">
                <div class="timeline-step">
                    <span class="timeline-dot completed" aria-label="Completed">&#10003;</span>
                    <div class="timeline-content">
                        <p class="timeline-label">Application submitted</p>
                        <p class="timeline-date"><?= $submittedDate ?></p>
                    </div>
                </div>
                <div class="timeline-step">
                    <span class="timeline-dot active" aria-label="In progress">2</span>
                    <div class="timeline-content">
                        <p class="timeline-label">Admin review</p>
                        <p class="timeline-date">In progress</p>
                    </div>
                </div>
                <div class="timeline-step">
                    <span class="timeline-dot pending" aria-label="Pending">3</span>
                    <div class="timeline-content">
                        <p class="timeline-label">Dashboard unlock</p>
                        <p class="timeline-date">Pending</p>
                    </div>
                </div>
            </div>

            <!-- Info tip -->
            <div class="review-tip">
                <span class="review-tip-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                </span>
                <span>Typically reviewed within <strong>24–48 hours</strong>. You can close this page and come back anytime.</span>
            </div>

            <!-- Actions -->
            <div class="review-actions">
                <a href="<?= URLROOT ?>/main/home" class="btn-primary">Back home</a>
                <a href="<?= URLROOT ?>/users/logout" class="btn-ghost">Sign out</a>
            </div>

            <!-- Support -->
            <p class="review-support">
                Need help? <a href="mailto:support@goldenpromise.com">Contact support</a>
            </p>
        </section>
    </main>
</body>
</html>
