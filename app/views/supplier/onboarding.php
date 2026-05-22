<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Onboarding - <?= APPNAME ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-stone-50 text-stone-900">
    <main class="mx-auto flex min-h-screen w-full max-w-3xl items-center px-4 py-10">
        <section class="w-full rounded-lg border border-stone-200 bg-white p-6 shadow-sm">
            <div class="mb-6">
                <p class="text-sm font-semibold uppercase tracking-wide text-rose-700">Partner application</p>
                <h1 class="mt-1 text-2xl font-bold">Tell us about your service</h1>
                <p class="mt-2 text-sm text-stone-600">This information helps admin review and approve supplier accounts.</p>
            </div>

            <?php if (!empty($message)): ?>
                <div class="mb-5 rounded-md border <?= !empty($submitted) ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-rose-200 bg-rose-50 text-rose-700' ?> px-4 py-3 text-sm">
                    <?= $message ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= URLROOT ?>/supplier/onboarding" class="grid gap-4">
                <label class="grid gap-1 text-sm font-medium">
                    Account email
                    <input required name="email" type="email" class="rounded-md border border-stone-300 px-3 py-2 font-normal outline-none focus:border-rose-600" value="<?= $email ?? '' ?>">
                </label>

                <label class="grid gap-1 text-sm font-medium">
                    Business name
                    <input required name="business_name" type="text" class="rounded-md border border-stone-300 px-3 py-2 font-normal outline-none focus:border-rose-600" value="<?= $business_name ?? '' ?>">
                </label>

                <label class="grid gap-1 text-sm font-medium">
                    Service category
                    <select required name="service_category" class="rounded-md border border-stone-300 px-3 py-2 font-normal outline-none focus:border-rose-600">
                        <?php $selectedCategory = $service_category ?? ''; ?>
                        <option value="">Choose service</option>
                        <option value="Venue" <?= $selectedCategory === 'Venue' ? 'selected' : '' ?>>Venue</option>
                        <option value="Dress" <?= $selectedCategory === 'Dress' ? 'selected' : '' ?>>Dress</option>
                        <option value="Studio" <?= $selectedCategory === 'Studio' ? 'selected' : '' ?>>Studio</option>
                        <option value="Food" <?= $selectedCategory === 'Food' ? 'selected' : '' ?>>Food</option>
                        <option value="Accessories" <?= $selectedCategory === 'Accessories' ? 'selected' : '' ?>>Accessories</option>
                        <option value="Package" <?= $selectedCategory === 'Package' ? 'selected' : '' ?>>Package</option>
                    </select>
                </label>

                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="grid gap-1 text-sm font-medium">
                        Phone
                        <input required name="phone" type="tel" class="rounded-md border border-stone-300 px-3 py-2 font-normal outline-none focus:border-rose-600" value="<?= $phone ?? '' ?>">
                    </label>

                    <label class="grid gap-1 text-sm font-medium">
                        Location
                        <input required name="location" type="text" class="rounded-md border border-stone-300 px-3 py-2 font-normal outline-none focus:border-rose-600" value="<?= $location ?? '' ?>">
                    </label>
                </div>
<!-- 
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="grid gap-1 text-sm font-medium">
                        url 
                        <input required name="url" type="url" class="rounded-md border border-stone-300 px-3 py-2 font-normal outline-none focus:border-rose-600" value="<?= $url ?? '' ?>">
                    </label>

         
                </div> -->


                <label class="grid gap-1 text-sm font-medium">
                    Service description
                    <textarea name="description" rows="4" class="rounded-md border border-stone-300 px-3 py-2 font-normal outline-none focus:border-rose-600"><?= $description ?? '' ?></textarea>
                </label>

                <div class="mt-2 flex flex-wrap items-center gap-3">
                    <button type="submit" class="rounded-md bg-rose-700 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-rose-800">Submit for review</button>
                    <a href="<?= URLROOT ?>/main/home" class="text-sm font-medium text-stone-600 hover:text-stone-900">Back home</a>
                </div>
            </form>
        </section>
    </main>
</body>
</html>
