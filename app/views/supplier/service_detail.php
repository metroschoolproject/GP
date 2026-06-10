<?php
$supplier = $supplier ?? [];
$service = $service ?? [];
$media = $service['media'] ?? [];

$dashboardTitle = 'Supplier';
$dashboardCrumb = 'Service Detail';
$dashboardSearchPlaceholder = 'Search services, packages...';
$dashboardContentClass = 'bg-[#f6f4f1] px-4 py-5 sm:px-6 lg:px-8';
$serviceId = (int)($service['id'] ?? 0);
$serviceName = htmlspecialchars($service['name'] ?? 'Service detail', ENT_QUOTES, 'UTF-8');
$serviceNameRaw = $service['name'] ?? 'Service detail';
$dashboardBreadcrumbs = [
    ['label' => 'Dashboard', 'url' => URLROOT . '/supplier/dashboard'],
    ['label' => 'Services', 'url' => URLROOT . '/supplier/services'],
    ['label' => $serviceNameRaw, 'url' => null],
];
$serviceCategory = htmlspecialchars($service['category'] ?? 'Others', ENT_QUOTES, 'UTF-8');
$serviceDescription = htmlspecialchars($service['desc'] ?? '', ENT_QUOTES, 'UTF-8');
$servicePrice = number_format((float)($service['price'] ?? 0));
$serviceImage = htmlspecialchars($service['img'] ?? '', ENT_QUOTES, 'UTF-8');
$mediaCreateUrl = URLROOT . '/supplier/serviceMediaCreate/' . $serviceId;
$mediaDeleteUrl = URLROOT . '/supplier/serviceMediaDelete/' . $serviceId . '/';
$availability = $service['availability'] ?? [];
$weeklyRows = $availability['weekly'] ?? [];
$overrideRows = $availability['overrides'] ?? [];
$weeklyByDay = [];
foreach ($weeklyRows as $row) {
    $weeklyByDay[(int)($row['day_of_week'] ?? 0)] = $row;
}
$availabilitySaveUrl = URLROOT . '/supplier/serviceAvailabilitySave/' . $serviceId;
$overrideSaveUrl = URLROOT . '/supplier/serviceAvailabilityOverrideSave/' . $serviceId;
$overrideDeleteUrl = URLROOT . '/supplier/serviceAvailabilityOverrideDelete/' . $serviceId . '/';
$previewUrl = URLROOT . '/supplier/serviceAvailabilityPreview/' . $serviceId;

$dashboardContent = function () use ($serviceId, $serviceName, $serviceCategory, $serviceDescription, $servicePrice, $serviceImage, $media, $mediaCreateUrl, $mediaDeleteUrl, $availability, $weeklyByDay, $overrideRows, $availabilitySaveUrl, $overrideSaveUrl, $overrideDeleteUrl, $previewUrl) {
    $days = [
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
        7 => 'Sunday',
    ];
    $mediaCount = count($media);
    $overrideCount = count($overrideRows);
    $openDaysCount = 0;
    foreach ($days as $dayNumber => $dayName) {
        $row = $weeklyByDay[$dayNumber] ?? [];
        $isAvailable = array_key_exists('is_available', $row) ? !empty($row['is_available']) : in_array($dayNumber, [1, 2, 3, 4, 5], true);
        if ($isAvailable) {
            $openDaysCount++;
        }
    }
?>
<div class="mx-auto max-w-[1500px] font-ui text-app-text antialiased">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <a href="<?= URLROOT ?>/supplier/services" class="inline-flex h-10 items-center gap-2 rounded-xl border border-app-border bg-white px-4 text-sm font-semibold text-app-text shadow-sm transition hover:border-app-focus hover:bg-app-soft focus:outline-none focus:ring-2 focus:ring-app-ring">
            <i data-lucide="arrow-left" class="h-4 w-4"></i>
            Back
        </a>
        <div class="inline-flex items-center gap-2 rounded-full border border-app-border bg-white px-3 py-1.5 text-xs font-semibold text-app-secondary shadow-sm">
            <span class="h-2 w-2 rounded-full bg-app-success"></span>
            Service workspace
        </div>
    </div>

    <section class="overflow-hidden rounded-[1.75rem] border border-app-border bg-white shadow-sm">
        <div class="grid grid-cols-1 lg:grid-cols-[420px_1fr]">
            <div class="relative min-h-[320px] bg-app-soft lg:min-h-[420px]">
                <?php if ($serviceImage !== ''): ?>
                    <img src="<?= $serviceImage ?>" alt="<?= $serviceName ?>" class="h-full w-full object-cover">
                <?php else: ?>
                    <div class="flex h-full min-h-[320px] items-center justify-center bg-app-soft text-app-muted">
                        <div class="flex h-20 w-20 items-center justify-center rounded-2xl border border-dashed border-app-border bg-white text-app-muted">
                            <i data-lucide="image" class="h-9 w-9"></i>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="absolute left-5 top-5 inline-flex items-center gap-2 rounded-full bg-white/90 px-3 py-1.5 text-xs font-bold text-app-primary shadow-sm backdrop-blur">
                    <i data-lucide="sparkles" class="h-3.5 w-3.5"></i>
                    <?= $serviceCategory ?>
                </div>
            </div>

            <div class="flex min-w-0 flex-col justify-between p-6 sm:p-8 lg:p-10">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-2 rounded-full border border-app-border bg-app-soft px-3 py-1 text-xs font-bold uppercase tracking-wide text-app-secondary">
                            <i data-lucide="briefcase-business" class="h-3.5 w-3.5"></i>
                            Service Detail
                        </span>
                        <span class="inline-flex items-center rounded-full border border-app-border bg-white px-3 py-1 text-xs font-semibold text-app-muted">ID #<?= $serviceId ?></span>
                    </div>
                    <h1 class="mt-4 max-w-3xl text-3xl font-bold leading-tight tracking-tight text-app-text sm:text-4xl"><?= $serviceName ?></h1>
                    <div class="mt-4 flex flex-wrap items-end gap-x-6 gap-y-2">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-app-muted">Starting price</p>
                            <p class="mt-1 text-3xl font-bold tracking-tight text-app-primary">RM <?= $servicePrice ?></p>
                        </div>
                    </div>
                </div>

                <?php if ($serviceDescription !== ''): ?>
                    <p class="mt-5 max-w-3xl text-sm leading-6 text-app-secondary"><?= $serviceDescription ?></p>
                <?php endif; ?>

                <div class="mt-8 grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <div class="rounded-2xl border border-app-border bg-[#fbfaf8] p-4">
                        <div class="mb-3 flex h-9 w-9 items-center justify-center rounded-xl bg-app-soft text-app-primary">
                            <i data-lucide="calendar-check-2" class="h-4 w-4"></i>
                        </div>
                        <p class="text-2xl font-bold text-app-text"><?= $openDaysCount ?>/7</p>
                        <p class="mt-1 text-xs font-medium text-app-secondary">Open weekly days</p>
                    </div>
                    <div class="rounded-2xl border border-app-border bg-[#fbfaf8] p-4">
                        <div class="mb-3 flex h-9 w-9 items-center justify-center rounded-xl bg-app-soft text-app-primary">
                            <i data-lucide="images" class="h-4 w-4"></i>
                        </div>
                        <p class="text-2xl font-bold text-app-text"><?= $mediaCount ?></p>
                        <p class="mt-1 text-xs font-medium text-app-secondary">Portfolio photos</p>
                    </div>
                    <div class="rounded-2xl border border-app-border bg-[#fbfaf8] p-4">
                        <div class="mb-3 flex h-9 w-9 items-center justify-center rounded-xl bg-app-soft text-app-primary">
                            <i data-lucide="calendar-clock" class="h-4 w-4"></i>
                        </div>
                        <p class="text-2xl font-bold text-app-text"><?= (int)($availability['duration_minutes'] ?? 60) ?>m</p>
                        <p class="mt-1 text-xs font-medium text-app-secondary">Default slot length</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-[380px_1fr]">
        <div class="rounded-[1.5rem] border border-app-border bg-white p-5 shadow-sm sm:p-6">
            <div class="mb-5 flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-app-muted">Service summary</p>
                    <h2 class="mt-1 text-xl font-bold tracking-tight text-app-text">At a glance</h2>
                    <p class="mt-1 text-sm text-app-secondary">Core service details suppliers check most often.</p>
                </div>
            </div>

            <div class="space-y-3">
                <div class="rounded-2xl border border-app-border bg-[#fbfaf8] p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-app-muted">Category</p>
                    <p class="mt-1 text-base font-bold text-app-text"><?= $serviceCategory ?></p>
                </div>
                <div class="rounded-2xl border border-app-border bg-[#fbfaf8] p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-app-muted">Price</p>
                    <p class="mt-1 text-base font-bold text-app-primary">RM <?= $servicePrice ?></p>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-2xl border border-app-border bg-[#fbfaf8] p-4">
                        <p class="text-2xl font-bold text-app-text"><?= $mediaCount ?></p>
                        <p class="mt-1 text-xs font-medium text-app-secondary">Photos</p>
                    </div>
                    <div class="rounded-2xl border border-app-border bg-[#fbfaf8] p-4">
                        <p class="text-2xl font-bold text-app-text"><?= $overrideCount ?></p>
                        <p class="mt-1 text-xs font-medium text-app-secondary">Overrides</p>
                    </div>
                </div>
                <div class="rounded-2xl border border-app-border bg-[#fbfaf8] p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-app-muted">Description</p>
                    <p class="mt-2 text-sm leading-6 text-app-secondary"><?= $serviceDescription !== '' ? $serviceDescription : 'No description added yet.' ?></p>
                </div>
            </div>
        </div>

        <div class="rounded-[1.5rem] border border-app-border bg-white p-5 shadow-sm sm:p-6">
            <div class="mb-5 flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-app-muted">Portfolio</p>
                    <h2 class="mt-1 text-xl font-bold tracking-tight text-app-text">Media Gallery</h2>
                    <p class="mt-1 text-sm text-app-secondary">Show customers the real look and quality of this service.</p>
                </div>
                <label class="inline-flex h-10 cursor-pointer items-center gap-2 rounded-xl bg-app-primary px-4 text-sm font-semibold text-app-white shadow-sm transition hover:bg-app-accent focus-within:ring-2 focus-within:ring-app-ring">
                    <i data-lucide="image-plus" class="h-4 w-4"></i>
                    Add Photo
                    <input id="serviceMediaInput" type="file" accept="image/*" class="hidden">
                </label>
            </div>

            <p id="serviceMediaMessage" class="mb-4 hidden rounded-xl border border-app-border bg-app-soft px-3 py-2 text-sm text-app-secondary"></p>

            <div id="serviceMediaGrid" class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                <?php foreach ($media as $item): ?>
                    <article class="service-media-card group relative overflow-hidden rounded-2xl border border-app-border bg-app-soft shadow-sm transition hover:-translate-y-0.5 hover:shadow-card" data-media-id="<?= (int)$item['id'] ?>">
                        <img src="<?= htmlspecialchars($item['file_url'] ?? '', ENT_QUOTES, 'UTF-8') ?>" alt="Service media" class="aspect-square w-full object-cover">
                        <div class="absolute inset-0 bg-app-text/0 transition group-hover:bg-app-text/10"></div>
                        <button type="button" onclick="deleteServiceMedia(<?= (int)$item['id'] ?>)" class="absolute right-2 top-2 flex h-9 w-9 items-center justify-center rounded-xl bg-white/95 text-app-danger opacity-0 shadow-sm transition hover:bg-app-danger-soft group-hover:opacity-100">
                            <i data-lucide="trash-2" class="h-4 w-4"></i>
                        </button>
                    </article>
                <?php endforeach; ?>
            </div>

            <div id="serviceMediaEmpty" class="<?= empty($media) ? '' : 'hidden' ?> rounded-2xl border border-dashed border-app-border bg-[#fbfaf8] px-6 py-14 text-center">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-app-muted shadow-sm">
                    <i data-lucide="image-plus" class="h-5 w-5"></i>
                </div>
                <p class="mt-3 text-sm font-semibold text-app-text">No portfolio photos yet</p>
                <p class="mt-1 text-xs text-app-secondary">Add a clear image to help customers understand the service.</p>
            </div>
        </div>
    </section>

    <section class="mt-6 rounded-[1.5rem] border border-app-border bg-white p-5 shadow-sm sm:p-6">
        <div class="mb-5 flex flex-wrap items-start justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-app-muted">Availability management</p>
                <h2 class="mt-1 text-xl font-bold tracking-tight text-app-text">Weekly Schedule</h2>
                <p class="mt-1 text-sm text-app-secondary">This schedule only controls this service. Other services can stay open.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <div class="rounded-full border border-app-border bg-app-soft px-3 py-1.5 text-xs font-semibold text-app-secondary"><?= $openDaysCount ?> days enabled</div>
                <button type="button" id="saveAvailabilityBtn" class="inline-flex h-10 items-center gap-2 rounded-xl bg-app-primary px-4 text-sm font-semibold text-app-white shadow-sm transition hover:bg-app-accent focus:outline-none focus:ring-2 focus:ring-app-ring">
                    <i data-lucide="save" class="h-4 w-4"></i>
                    Save Schedule
                </button>
            </div>
        </div>

        <p id="availabilityMessage" class="mb-4 hidden rounded-xl border border-app-border bg-app-soft px-3 py-2 text-sm text-app-secondary"></p>

        <div class="mb-5 grid grid-cols-1 gap-3 md:grid-cols-3">
            <label class="rounded-2xl border border-app-border bg-[#fbfaf8] p-4">
                <span class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-app-muted">
                    <i data-lucide="timer" class="h-3.5 w-3.5"></i>
                    Slot Duration
                </span>
                <input id="availabilityDuration" type="number" min="15" step="15" value="<?= (int)($availability['duration_minutes'] ?? 60) ?>" class="mt-3 h-11 w-full rounded-xl border border-app-border bg-white px-3 text-sm font-semibold text-app-text outline-none transition focus:border-app-focus focus:ring-2 focus:ring-app-ring">
            </label>
            <label class="rounded-2xl border border-app-border bg-[#fbfaf8] p-4">
                <span class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-app-muted">
                    <i data-lucide="pause" class="h-3.5 w-3.5"></i>
                    Buffer Minutes
                </span>
                <input id="availabilityBuffer" type="number" min="0" step="5" value="<?= (int)($availability['buffer_minutes'] ?? 0) ?>" class="mt-3 h-11 w-full rounded-xl border border-app-border bg-white px-3 text-sm font-semibold text-app-text outline-none transition focus:border-app-focus focus:ring-2 focus:ring-app-ring">
            </label>
            <label class="rounded-2xl border border-app-border bg-[#fbfaf8] p-4">
                <span class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-app-muted">
                    <i data-lucide="users" class="h-3.5 w-3.5"></i>
                    Max Concurrent
                </span>
                <input id="availabilityConcurrent" type="number" min="1" step="1" value="<?= (int)($availability['max_concurrent'] ?? 1) ?>" class="mt-3 h-11 w-full rounded-xl border border-app-border bg-white px-3 text-sm font-semibold text-app-text outline-none transition focus:border-app-focus focus:ring-2 focus:ring-app-ring">
            </label>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-app-border">
            <table class="w-full min-w-[760px] text-sm">
                <thead class="bg-[#fbfaf8] text-xs uppercase tracking-wide text-app-muted">
                    <tr>
                        <th class="px-5 py-4 text-left">Day</th>
                        <th class="px-5 py-4 text-left">Status</th>
                        <th class="px-5 py-4 text-left">Start</th>
                        <th class="px-5 py-4 text-left">End</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-app-border">
                    <?php foreach ($days as $dayNumber => $dayName): ?>
                        <?php
                        $row = $weeklyByDay[$dayNumber] ?? [];
                        $isAvailable = array_key_exists('is_available', $row) ? !empty($row['is_available']) : in_array($dayNumber, [1, 2, 3, 4, 5], true);
                        $open = substr((string)($row['open_time'] ?? '09:00'), 0, 5);
                        $close = substr((string)($row['close_time'] ?? '17:00'), 0, 5);
                        ?>
                        <tr class="availability-day-row bg-white transition hover:bg-[#fbfaf8]" data-day="<?= $dayNumber ?>">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-app-text"><?= $dayName ?></div>
                                <div class="text-xs text-app-muted">Day <?= $dayNumber ?></div>
                            </td>
                            <td class="px-5 py-4">
                                <label class="inline-flex items-center gap-3 rounded-full border border-app-border bg-app-soft px-3 py-2 text-xs font-semibold text-app-secondary">
                                    <input type="checkbox" class="availability-open h-4 w-4 rounded border-app-border accent-[#6e4e58]" <?= $isAvailable ? 'checked' : '' ?>>
                                    Open
                                </label>
                            </td>
                            <td class="px-5 py-4">
                                <input type="time" class="availability-start h-10 rounded-xl border border-app-border bg-app-soft px-3 text-sm font-medium text-app-text outline-none transition focus:border-app-focus focus:bg-white focus:ring-2 focus:ring-app-ring" value="<?= htmlspecialchars($open, ENT_QUOTES, 'UTF-8') ?>">
                            </td>
                            <td class="px-5 py-4">
                                <input type="time" class="availability-end h-10 rounded-xl border border-app-border bg-app-soft px-3 text-sm font-medium text-app-text outline-none transition focus:border-app-focus focus:bg-white focus:ring-2 focus:ring-app-ring" value="<?= htmlspecialchars($close, ENT_QUOTES, 'UTF-8') ?>">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="mt-5 grid grid-cols-1 gap-5 xl:grid-cols-2">
        <div class="rounded-[1.5rem] border border-app-border bg-white p-5 shadow-sm sm:p-6">
            <div class="mb-5 flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-app-muted">Date override</p>
                    <h2 class="mt-1 text-xl font-bold tracking-tight text-app-text">Special Dates</h2>
                    <p class="mt-1 text-sm text-app-secondary">Close a date, open an extra date, or set custom hours.</p>
                </div>
                <span class="rounded-full border border-app-border bg-app-soft px-3 py-1.5 text-xs font-semibold text-app-secondary"><?= $overrideCount ?> saved</span>
            </div>

            <div class="rounded-2xl border border-app-border bg-[#fbfaf8] p-4">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <label class="text-xs font-semibold uppercase tracking-wide text-app-muted">
                        Date
                        <input id="overrideDate" type="date" class="mt-1.5 h-11 w-full rounded-xl border border-app-border bg-white px-3 text-sm font-medium text-app-text outline-none transition focus:border-app-focus focus:ring-2 focus:ring-app-ring">
                    </label>
                    <label class="text-xs font-semibold uppercase tracking-wide text-app-muted">
                        Override Type
                        <select id="overrideType" class="mt-1.5 h-11 w-full rounded-xl border border-app-border bg-white px-3 text-sm font-medium text-app-text outline-none transition focus:border-app-focus focus:ring-2 focus:ring-app-ring">
                        <option value="unavailable">Unavailable</option>
                        <option value="custom_hours">Custom hours</option>
                        <option value="available">Available</option>
                        </select>
                    </label>
                    <label class="text-xs font-semibold uppercase tracking-wide text-app-muted">
                        Opens
                        <input id="overrideOpen" type="time" value="09:00" class="mt-1.5 h-11 w-full rounded-xl border border-app-border bg-white px-3 text-sm font-medium text-app-text outline-none transition focus:border-app-focus focus:ring-2 focus:ring-app-ring">
                    </label>
                    <label class="text-xs font-semibold uppercase tracking-wide text-app-muted">
                        Closes
                        <input id="overrideClose" type="time" value="17:00" class="mt-1.5 h-11 w-full rounded-xl border border-app-border bg-white px-3 text-sm font-medium text-app-text outline-none transition focus:border-app-focus focus:ring-2 focus:ring-app-ring">
                    </label>
                    <label class="sm:col-span-2 text-xs font-semibold uppercase tracking-wide text-app-muted">
                        Reason
                        <input id="overrideReason" type="text" placeholder="Holiday, private booking, extended hours..." class="mt-1.5 h-11 w-full rounded-xl border border-app-border bg-white px-3 text-sm font-medium normal-case tracking-normal text-app-text outline-none transition placeholder:text-app-muted focus:border-app-focus focus:ring-2 focus:ring-app-ring">
                    </label>
                </div>
                <button type="button" id="saveOverrideBtn" class="mt-4 inline-flex h-10 items-center gap-2 rounded-xl border border-app-border bg-white px-4 text-sm font-semibold text-app-text shadow-sm transition hover:border-app-focus hover:bg-app-soft focus:outline-none focus:ring-2 focus:ring-app-ring">
                    <i data-lucide="calendar-plus" class="h-4 w-4"></i>
                    Save Override
                </button>
            </div>

            <div id="overrideList" class="mt-4 space-y-2">
                <?php foreach ($overrideRows as $override): ?>
                    <article class="override-row flex items-center justify-between gap-3 rounded-2xl border border-app-border bg-white px-4 py-3 text-sm shadow-sm transition hover:border-app-focus" data-override-id="<?= (int)$override['id'] ?>">
                        <span class="min-w-0">
                            <strong class="block text-app-text"><?= htmlspecialchars($override['date'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>
                            <span class="mt-0.5 inline-flex rounded-full bg-app-soft px-2.5 py-1 text-xs font-semibold capitalize text-app-secondary"><?= htmlspecialchars(str_replace('_', ' ', $override['type'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                        </span>
                        <button type="button" onclick="deleteOverride(<?= (int)$override['id'] ?>)" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-app-danger transition hover:bg-app-danger-soft"><i data-lucide="trash-2" class="h-4 w-4"></i></button>
                    </article>
                <?php endforeach; ?>
                <div id="overrideEmpty" class="<?= empty($overrideRows) ? '' : 'hidden' ?> rounded-2xl border border-dashed border-app-border bg-[#fbfaf8] px-4 py-8 text-center">
                    <p class="text-sm font-semibold text-app-text">No special dates yet</p>
                    <p class="mt-1 text-xs text-app-secondary">Saved overrides will appear here for quick review.</p>
                </div>
            </div>
        </div>

        <div class="rounded-[1.5rem] border border-app-border bg-white p-5 shadow-sm sm:p-6">
            <div class="mb-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-app-muted">Customer view</p>
                <h2 class="mt-1 text-xl font-bold tracking-tight text-app-text">Preview Available Slots</h2>
                <p class="mt-1 text-sm text-app-secondary">Check the exact slots customers can book for a selected date.</p>
            </div>

            <div class="rounded-2xl border border-app-border bg-[#fbfaf8] p-4">
                <div class="flex flex-col gap-3 sm:flex-row">
                    <label class="min-w-0 flex-1 text-xs font-semibold uppercase tracking-wide text-app-muted">
                        Preview date
                        <input id="previewDate" type="date" class="mt-1.5 h-11 w-full rounded-xl border border-app-border bg-white px-3 text-sm font-medium text-app-text outline-none transition focus:border-app-focus focus:ring-2 focus:ring-app-ring">
                    </label>
                    <button type="button" id="previewSlotsBtn" class="mt-auto inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-app-primary px-5 text-sm font-semibold text-app-white shadow-sm transition hover:bg-app-accent focus:outline-none focus:ring-2 focus:ring-app-ring">
                        <i data-lucide="eye" class="h-4 w-4"></i>
                        Preview
                    </button>
                </div>
                <div id="previewSlotsResult" class="mt-4 flex min-h-[132px] flex-wrap content-start gap-2 rounded-2xl border border-dashed border-app-border bg-white p-4 text-sm text-app-secondary"></div>
            </div>
        </div>
    </section>
</div>

<script>
lucide.createIcons();

const serviceMediaCreateUrl = <?= json_encode($mediaCreateUrl) ?>;
const serviceMediaDeleteUrl = <?= json_encode($mediaDeleteUrl) ?>;
const mediaInput = document.getElementById('serviceMediaInput');
const mediaGrid = document.getElementById('serviceMediaGrid');
const mediaEmpty = document.getElementById('serviceMediaEmpty');
const mediaMessage = document.getElementById('serviceMediaMessage');
const availabilitySaveUrl = <?= json_encode($availabilitySaveUrl) ?>;
const overrideSaveUrl = <?= json_encode($overrideSaveUrl) ?>;
const overrideDeleteUrl = <?= json_encode($overrideDeleteUrl) ?>;
const previewUrl = <?= json_encode($previewUrl) ?>;
const availabilityMessage = document.getElementById('availabilityMessage');
const overrideEmpty = document.getElementById('overrideEmpty');

function showMediaMessage(text) {
    mediaMessage.textContent = text;
    mediaMessage.classList.remove('hidden');
}

function hideMediaMessage() {
    mediaMessage.classList.add('hidden');
}

function fileToDataUrl(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = () => resolve(reader.result);
        reader.onerror = reject;
        reader.readAsDataURL(file);
    });
}

async function serviceMediaRequest(url, payload = {}) {
    const response = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(payload)
    });
    const data = await response.json();

    if (!response.ok || data.status === 'error') {
        throw new Error(data.message || 'Request failed.');
    }

    return data;
}

function showAvailabilityMessage(text) {
    availabilityMessage.textContent = text;
    availabilityMessage.classList.remove('hidden');
}

async function dashboardJsonRequest(url, payload = {}) {
    const response = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(payload)
    });
    const data = await response.json();

    if (!response.ok || data.status === 'error') {
        throw new Error(data.message || 'Request failed.');
    }

    return data;
}

function appendMedia(media) {
    mediaEmpty.classList.add('hidden');
    mediaGrid.insertAdjacentHTML('afterbegin', `
        <article class="service-media-card group relative overflow-hidden rounded-2xl border border-app-border bg-app-soft shadow-sm transition hover:-translate-y-0.5 hover:shadow-card" data-media-id="${media.id}">
            <img src="${media.file_url}" alt="Service media" class="aspect-square w-full object-cover">
            <div class="absolute inset-0 bg-app-text/0 transition group-hover:bg-app-text/10"></div>
            <button type="button" onclick="deleteServiceMedia(${media.id})" class="absolute right-2 top-2 flex h-9 w-9 items-center justify-center rounded-xl bg-white/95 text-app-danger opacity-0 shadow-sm transition hover:bg-app-danger-soft group-hover:opacity-100">
                <i data-lucide="trash-2" class="h-4 w-4"></i>
            </button>
        </article>
    `);
    lucide.createIcons();
}

mediaInput.addEventListener('change', async () => {
    const file = mediaInput.files[0];
    if (!file) return;

    hideMediaMessage();

    try {
        const img = await fileToDataUrl(file);
        const result = await serviceMediaRequest(serviceMediaCreateUrl, { img });
        appendMedia(result.media);
        mediaInput.value = '';
    } catch (error) {
        showMediaMessage(error.message);
    }
});

async function deleteServiceMedia(mediaId) {
    if (!confirm('Delete this photo?')) return;

    hideMediaMessage();

    try {
        await serviceMediaRequest(serviceMediaDeleteUrl + encodeURIComponent(mediaId));
        document.querySelector(`[data-media-id="${mediaId}"]`)?.remove();

        if (!mediaGrid.querySelector('.service-media-card')) {
            mediaEmpty.classList.remove('hidden');
        }
    } catch (error) {
        showMediaMessage(error.message);
    }
}

document.getElementById('saveAvailabilityBtn')?.addEventListener('click', async () => {
    const weekly = Array.from(document.querySelectorAll('.availability-day-row')).map(row => ({
        day_of_week: Number(row.dataset.day),
        is_available: row.querySelector('.availability-open').checked,
        open_time: row.querySelector('.availability-start').value,
        close_time: row.querySelector('.availability-end').value
    }));

    try {
        await dashboardJsonRequest(availabilitySaveUrl, {
            duration_minutes: document.getElementById('availabilityDuration').value,
            buffer_minutes: document.getElementById('availabilityBuffer').value,
            max_concurrent: document.getElementById('availabilityConcurrent').value,
            weekly
        });
        showAvailabilityMessage('Availability saved.');
    } catch (error) {
        showAvailabilityMessage(error.message);
    }
});

document.getElementById('saveOverrideBtn')?.addEventListener('click', async () => {
    try {
        await dashboardJsonRequest(overrideSaveUrl, {
            date: document.getElementById('overrideDate').value,
            type: document.getElementById('overrideType').value,
            open_time: document.getElementById('overrideOpen').value,
            close_time: document.getElementById('overrideClose').value,
            reason: document.getElementById('overrideReason').value
        });
        window.location.reload();
    } catch (error) {
        showAvailabilityMessage(error.message);
    }
});

async function deleteOverride(overrideId) {
    if (!confirm('Delete this override?')) return;

    try {
        await dashboardJsonRequest(overrideDeleteUrl + encodeURIComponent(overrideId));
        document.querySelector(`[data-override-id="${overrideId}"]`)?.remove();
        if (!document.querySelector('.override-row')) {
            overrideEmpty?.classList.remove('hidden');
        }
    } catch (error) {
        showAvailabilityMessage(error.message);
    }
}

document.getElementById('previewSlotsBtn')?.addEventListener('click', async () => {
    const resultBox = document.getElementById('previewSlotsResult');
    resultBox.textContent = '';

    try {
        const result = await dashboardJsonRequest(previewUrl, { date: document.getElementById('previewDate').value });
        const slots = result.preview?.slots || [];

        if (!slots.length) {
            resultBox.textContent = 'Closed or no available slots for this date.';
            return;
        }

        resultBox.innerHTML = slots.map(slot => `<span class="rounded-full border border-app-border bg-app-input px-3 py-1 font-semibold text-app-text">${slot.start_time} - ${slot.end_time} (${slot.confirmed_count}/${slot.max_concurrent})</span>`).join('');
    } catch (error) {
        resultBox.textContent = error.message;
    }
});
</script>
<?php
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php require_once APPROOT . '/views/dashboardLayout/head.php'; ?>
</head>
<body class="grid h-screen gap-0 bg-app-page" style="grid-template-columns: 280px 1fr;">
    <?php require APPROOT . '/views/dashboardLayout/suppliersidebar.php'; ?>
</body>
</html>
