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
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-stone-50 text-stone-900">
    <main class="mx-auto flex min-h-screen w-full max-w-3xl items-center px-4 py-10">
        <section class="w-full rounded-lg border border-stone-200 bg-white p-6 shadow-sm">
            <div class="mb-6">
                <p class="text-sm font-semibold uppercase tracking-wide text-amber-700">Partner application</p>
                <h1 class="mt-1 text-2xl font-bold">Your application is pending review</h1>
                <p class="mt-2 text-sm leading-6 text-stone-600">
                    Thanks for submitting your supplier information. Admin will review your application before your services become available.
                </p>
            </div>

            <div class="grid gap-3 rounded-md border border-amber-200 bg-amber-50 px-4 py-4 text-sm">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <span class="font-medium text-stone-600">Current status</span>
                    <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-amber-800">
                        <?= $supplierStatus ?>
                    </span>
                </div>
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <span class="font-medium text-stone-600">Business name</span>
                    <span class="font-semibold text-stone-900"><?= $supplierName ?></span>
                </div>
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <span class="font-medium text-stone-600">Service</span>
                    <span class="font-semibold text-stone-900"><?= $serviceName ?></span>
                </div>
                <?php if ($emailAddress !== ''): ?>
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="font-medium text-stone-600">Account email</span>
                        <span class="font-semibold text-stone-900"><?= $emailAddress ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mt-6 rounded-md border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-600">
                You can come back later and sign in with the same account to check this status again.
            </div>

            <div class="mt-6 flex flex-wrap items-center gap-3">
                <a href="<?= URLROOT ?>/main/home" class="rounded-md bg-rose-700 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-rose-800">Back home</a>
                <a href="<?= URLROOT ?>/users/logout" class="text-sm font-medium text-stone-600 hover:text-stone-900">Sign out</a>
            </div>
        </section>
    </main>
</body>
</html>
