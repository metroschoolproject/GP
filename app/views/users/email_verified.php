<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verified - <?= APPNAME ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-stone-50 text-stone-900">
    <main class="mx-auto flex min-h-screen w-full max-w-xl items-center px-4 py-10">
        <section class="w-full rounded-lg border border-stone-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-semibold uppercase tracking-wide text-rose-700">Email verification</p>
            <h1 class="mt-2 text-2xl font-bold"><?= !empty($verified) ? 'Email verified' : 'Verification link invalid' ?></h1>
            <p class="mt-3 text-sm leading-6 text-stone-600"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
            <a href="<?= !empty($verified) ? URLROOT . '/' . $redirect : URLROOT . '/users/auth' ?>" class="mt-6 inline-flex rounded-md bg-rose-700 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-800">
                <?= !empty($verified) ? 'Continue' : 'Back to login' ?>
            </a>
        </section>
    </main>
</body>
</html>
