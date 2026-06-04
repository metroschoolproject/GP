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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Pinyon+Script&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --env-bg: #e8b4b8;
            --env-border: #f4c7c4e5;
            --paper: #f5e8d9;
            --accent: #6d4c5b;
            --focus-color: rgb(247, 236, 236);
            --header-font: "Pinyon Script", cursive;
            --body-font: serif;
        }

        body {
            min-height: 100vh;
            margin: 0;
            font-family: var(--body-font);
            color: rgba(38, 20, 28, 0.92);
            background:
                radial-gradient(circle at top left, rgba(255, 250, 246, 0.42), transparent 28rem),
                var(--env-bg);
        }

        .supplier-card {
            position: relative;
            overflow: hidden;
            background: rgba(245, 232, 217, 0.94);
            border: 1px solid var(--env-border);
            box-shadow: 12px 14px 30px rgba(64, 20, 35, 0.22);
        }

        .supplier-card::before,
        .supplier-card::after {
            content: "";
            position: absolute;
            left: 24px;
            right: 24px;
            height: 1px;
            background: rgba(109, 76, 91, 0.22);
        }

        .supplier-card::before {
            top: 22px;
        }

        .supplier-card::after {
            bottom: 22px;
        }

        .script-heading {
            font-family: var(--header-font);
            color: var(--accent);
            font-weight: 600;
            letter-spacing: 0;
        }

        .supplier-eyebrow,
        .supplier-label {
            color: var(--accent);
        }

        .supplier-status-panel {
            border-color: var(--env-border);
            background: rgba(255, 250, 246, 0.48);
        }

        .supplier-status-row {
            border-color: rgba(109, 76, 91, 0.16);
        }

        .supplier-status-pill {
            background: rgba(232, 180, 184, 0.62);
            color: var(--accent);
            border: 1px solid rgba(109, 76, 91, 0.28);
        }

        .supplier-note {
            border-color: var(--env-border);
            background: rgba(249, 237, 228, 0.72);
            color: rgba(38, 20, 28, 0.72);
        }

        .supplier-stage {
            border-color: rgba(109, 76, 91, 0.16);
        }

        .supplier-stage-dot {
            border: 1px solid rgba(109, 76, 91, 0.26);
            background: rgba(232, 180, 184, 0.54);
            color: var(--accent);
        }

        .supplier-primary-btn {
            background: var(--accent);
            color: white;
            box-shadow: 5px 5px 8px rgba(0, 0, 0, 0.28);
        }

        .supplier-primary-btn:hover {
            background: #5b3f4c;
        }

        .supplier-secondary-link {
            color: rgba(109, 76, 91, 0.82);
        }

        .supplier-secondary-link:hover {
            color: var(--accent);
        }
    </style>
</head>
<body>
    <main class="mx-auto flex min-h-screen w-full max-w-3xl items-center px-4 py-10">
        <section class="supplier-card w-full rounded-2xl p-6 sm:p-8">
            <div class="relative z-10 mb-7 text-center">
                <p class="supplier-eyebrow text-sm font-semibold uppercase tracking-wide">Partner application</p>
                <h1 class="script-heading mt-1 text-5xl">Review in progress</h1>
                <p class="mx-auto mt-2 max-w-xl text-sm leading-6 text-stone-700">
                    Thanks for submitting your supplier information. Admin will review your application before your services become available.
                </p>
            </div>

            <div class="supplier-status-panel relative z-10 grid gap-0 rounded-lg border px-4 py-2 text-sm">
                <div class="supplier-status-row flex flex-wrap items-center justify-between gap-2 border-b py-3">
                    <span class="supplier-label font-semibold">Current status</span>
                    <span class="supplier-status-pill rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-wide">
                        <?= $supplierStatus ?>
                    </span>
                </div>
                <div class="supplier-status-row flex flex-wrap items-center justify-between gap-2 border-b py-3">
                    <span class="supplier-label font-semibold">Business name</span>
                    <span class="font-semibold text-stone-900"><?= $supplierName ?></span>
                </div>
                <div class="supplier-status-row flex flex-wrap items-center justify-between gap-2 <?= $emailAddress !== '' ? 'border-b' : '' ?> py-3">
                    <span class="supplier-label font-semibold">Service</span>
                    <span class="font-semibold text-stone-900"><?= $serviceName ?></span>
                </div>
                <?php if ($emailAddress !== ''): ?>
                    <div class="flex flex-wrap items-center justify-between gap-2 py-3">
                        <span class="supplier-label font-semibold">Account email</span>
                        <span class="font-semibold text-stone-900"><?= $emailAddress ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <div class="supplier-note relative z-10 mt-6 rounded-md border px-4 py-3 text-sm">
                You can come back later and sign in with the same account to check this status again.
            </div>

            <div class="relative z-10 mt-6 grid gap-3 text-sm">
                <div class="supplier-stage flex gap-3 rounded-md border bg-white/30 px-4 py-3">
                    <span class="supplier-stage-dot grid h-7 w-7 shrink-0 place-items-center rounded-full text-xs font-bold">1</span>
                    <div>
                        <p class="supplier-label font-semibold">Application submitted</p>
                        <p class="mt-1 text-stone-600">Your supplier profile and agreement are saved.</p>
                    </div>
                </div>
                <div class="supplier-stage flex gap-3 rounded-md border bg-white/30 px-4 py-3">
                    <span class="supplier-stage-dot grid h-7 w-7 shrink-0 place-items-center rounded-full text-xs font-bold">2</span>
                    <div>
                        <p class="supplier-label font-semibold">Admin profile review</p>
                        <p class="mt-1 text-stone-600">Admin checks your business details before dashboard access.</p>
                    </div>
                </div>
                <div class="supplier-stage flex gap-3 rounded-md border bg-white/30 px-4 py-3">
                    <span class="supplier-stage-dot grid h-7 w-7 shrink-0 place-items-center rounded-full text-xs font-bold">3</span>
                    <div>
                        <p class="supplier-label font-semibold">Payment unlock</p>
                        <p class="mt-1 text-stone-600">After approval, your dashboard opens in locked mode until membership payment is verified.</p>
                    </div>
                </div>
            </div>

            <div class="relative z-10 mt-6 flex flex-wrap items-center justify-center gap-4">
                <a href="<?= URLROOT ?>/main/home" class="supplier-primary-btn rounded-md px-5 py-2.5 text-sm font-semibold transition">Back home</a>
                <a href="<?= URLROOT ?>/users/logout" class="supplier-secondary-link text-sm font-semibold transition">Sign out</a>
            </div>
        </section>
    </main>
</body>
</html>
