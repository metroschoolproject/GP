<?php
$supplierName = htmlspecialchars($supplier['shop_name'] ?? 'Your supplier account', ENT_QUOTES, 'UTF-8');
$supplierStatus = htmlspecialchars(ucfirst($supplier['status'] ?? 'pending'), ENT_QUOTES, 'UTF-8');
$serviceName = htmlspecialchars($supplier['service_name'] ?? 'Service information', ENT_QUOTES, 'UTF-8');
$emailAddress = htmlspecialchars($email ?? '', ENT_QUOTES, 'UTF-8');
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
            --env-bg: #FBFBF9;
            --env-border: #ead8c7;
            --paper: #faf5ef;
            --accent: #6d4c5b;
            --accent-hover: #7b5c69;
            --surface: #fcf8f5;
            --soft-hover: #eddecc;
            --muted: #b79c8b;
            --body: #7b5c69;
            --text: #111827;
            --warning-bg: #fef3c7;
            --warning-text: #92400e;
            --body-font: system-ui, -apple-system, sans-serif;
        }

        body {
            min-height: 100vh;
            margin: 0;
            font-family: var(--body-font);
            color: var(--text);
            background: var(--env-bg);
        }

        .supplier-card {
            position: relative;
            overflow: hidden;
            background: var(--surface);
            border: 1px solid var(--env-border);
            box-shadow: 0 18px 45px rgba(15, 23, 42, 0.06);
        }

        .script-heading {
            color: var(--accent);
            font-weight: 800;
            letter-spacing: -0.03em;
        }

        .supplier-eyebrow,
        .supplier-label {
            color: var(--body);
        }

        .supplier-status-panel {
            border-color: var(--env-border);
            background: var(--paper);
        }

        .supplier-status-row {
            border-color: var(--env-border);
        }

        .supplier-status-pill {
            background: var(--warning-bg);
            color: var(--warning-text);
            border: 1px solid var(--env-border);
        }

        .supplier-note {
            border-color: var(--env-border);
            background: var(--paper);
            color: var(--body);
        }

        .supplier-stage {
            border-color: var(--env-border);
            background: var(--surface);
        }

        .supplier-stage-dot {
            border: 1px solid var(--env-border);
            background: var(--soft-hover);
            color: var(--accent);
        }

        .supplier-primary-btn {
            background: var(--accent);
            color: #fcf8f5;
            box-shadow: 0 1px 2px rgba(28, 25, 23, 0.05);
        }

        .supplier-primary-btn:hover {
            background: var(--accent-hover);
        }

        .supplier-secondary-link {
            color: var(--body);
        }

        .supplier-secondary-link:hover {
            color: var(--accent);
        }
    </style>
</head>
<body>
    <main class="mx-auto flex min-h-screen w-full max-w-3xl items-center px-4 py-10">
        <section class="supplier-card w-full rounded-card p-6 sm:p-8">
            <div class="relative z-10 mb-7 text-center">
                <p class="supplier-eyebrow text-sm font-semibold uppercase tracking-wide">Partner application</p>
                <h1 class="script-heading mt-1 text-3xl">Review in progress</h1>
                <p class="mx-auto mt-2 max-w-xl text-sm leading-6 text-app-secondary">
                    Thanks for submitting your supplier information. Admin will review your application before your services become available.
                </p>
            </div>

            <div class="supplier-status-panel relative z-10 grid gap-0 rounded-card border px-4 py-2 text-sm">
                <div class="supplier-status-row flex flex-wrap items-center justify-between gap-2 border-b py-3">
                    <span class="supplier-label font-semibold">Current status</span>
                    <span class="supplier-status-pill rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-wide">
                        <?= $supplierStatus ?>
                    </span>
                </div>
                <div class="supplier-status-row flex flex-wrap items-center justify-between gap-2 border-b py-3">
                    <span class="supplier-label font-semibold">Business name</span>
                    <span class="font-semibold text-app-text"><?= $supplierName ?></span>
                </div>
                <div class="supplier-status-row flex flex-wrap items-center justify-between gap-2 <?= $emailAddress !== '' ? 'border-b' : '' ?> py-3">
                    <span class="supplier-label font-semibold">Service</span>
                    <span class="font-semibold text-app-text"><?= $serviceName ?></span>
                </div>
                <?php if ($emailAddress !== ''): ?>
                    <div class="flex flex-wrap items-center justify-between gap-2 py-3">
                        <span class="supplier-label font-semibold">Account email</span>
                        <span class="font-semibold text-app-text"><?= $emailAddress ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <div class="supplier-note relative z-10 mt-6 rounded-card border px-4 py-3 text-sm">
                You can come back later and sign in with the same account to check this status again.
            </div>

            <div class="relative z-10 mt-6 grid gap-3 text-sm">
                <div class="supplier-stage flex gap-3 rounded-card border px-4 py-3">
                    <span class="supplier-stage-dot grid h-7 w-7 shrink-0 place-items-center rounded-full text-xs font-bold">1</span>
                    <div>
                        <p class="supplier-label font-semibold">Application submitted</p>
                        <p class="mt-1 text-app-secondary">Your supplier profile and agreement are saved.</p>
                    </div>
                </div>
                <div class="supplier-stage flex gap-3 rounded-card border px-4 py-3">
                    <span class="supplier-stage-dot grid h-7 w-7 shrink-0 place-items-center rounded-full text-xs font-bold">2</span>
                    <div>
                        <p class="supplier-label font-semibold">Admin profile review</p>
                        <p class="mt-1 text-app-secondary">Admin checks your business details before dashboard access.</p>
                    </div>
                </div>
                <div class="supplier-stage flex gap-3 rounded-card border px-4 py-3">
                    <span class="supplier-stage-dot grid h-7 w-7 shrink-0 place-items-center rounded-full text-xs font-bold">3</span>
                    <div>
                        <p class="supplier-label font-semibold">Payment unlock</p>
                        <p class="mt-1 text-app-secondary">After approval, your dashboard opens in locked mode until membership payment is verified.</p>
                    </div>
                </div>
            </div>

            <div class="relative z-10 mt-6 flex flex-wrap items-center justify-center gap-4">
                <a href="<?= URLROOT ?>/main/home" class="supplier-primary-btn rounded-card px-5 py-2.5 text-sm font-semibold transition">Back home</a>
                <a href="<?= URLROOT ?>/users/logout" class="supplier-secondary-link text-sm font-semibold transition">Sign out</a>
            </div>
        </section>
    </main>
</body>
</html>
