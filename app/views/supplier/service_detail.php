<?php
$supplier = $supplier ?? [];
$service = $service ?? [];
$media = $service['media'] ?? [];

$dashboardTitle = 'Supplier';
$dashboardCrumb = 'Service Detail';
$dashboardSearchPlaceholder = 'Search services, packages...';
$dashboardContentClass = 'bg-app-content px-6 py-6';
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
$servicePriceAmount = (float)($service['price'] ?? 0);
$servicePrice = number_format($servicePriceAmount);
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
$days = [
    1 => 'Monday',
    2 => 'Tuesday',
    3 => 'Wednesday',
    4 => 'Thursday',
    5 => 'Friday',
    6 => 'Saturday',
    7 => 'Sunday',
];
$defaultOpenDays = [1, 2, 3, 4, 5];
$isDayAvailable = function ($dayNumber, $row) use ($defaultOpenDays) {
    return array_key_exists('is_available', $row)
        ? !empty($row['is_available'])
        : in_array($dayNumber, $defaultOpenDays, true);
};

$dashboardContent = function () use ($serviceId, $serviceName, $serviceCategory, $serviceDescription, $servicePriceAmount, $servicePrice, $serviceImage, $media, $mediaCreateUrl, $mediaDeleteUrl, $availability, $weeklyByDay, $overrideRows, $availabilitySaveUrl, $overrideSaveUrl, $overrideDeleteUrl, $previewUrl, $days, $isDayAvailable) {
    $mediaCount = count($media);
    $overrideCount = count($overrideRows);
    $slotDuration = (int)($availability['duration_minutes'] ?? 60);
    $openDaysCount = 0;

    foreach ($days as $dayNumber => $dayName) {
        $row = $weeklyByDay[$dayNumber] ?? [];
        if ($isDayAvailable($dayNumber, $row)) {
            $openDaysCount++;
        }
    }

    $attentionItems = [];
    if ($servicePriceAmount <= 0) {
        $attentionItems[] = ['label' => 'Check pricing', 'detail' => 'Add a customer-facing starting price.', 'icon' => 'badge-dollar-sign'];
    }
    if ($serviceDescription === '') {
        $attentionItems[] = ['label' => 'Add description', 'detail' => 'Explain what customers get with this service.', 'icon' => 'align-left'];
    }
    if ($mediaCount === 0) {
        $attentionItems[] = ['label' => 'Upload photo', 'detail' => 'Add at least one portfolio image.', 'icon' => 'image-plus'];
    }
    if ($openDaysCount === 0) {
        $attentionItems[] = ['label' => 'Set availability', 'detail' => 'Open at least one weekly day.', 'icon' => 'calendar-check-2'];
    }
    $isReady = empty($attentionItems);
?>
<div class="mx-auto max-w-[1600px] px-4 py-5 font-ui text-[13px] text-app-text antialiased">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <a href="<?= URLROOT ?>/supplier/services" class="inline-flex h-9 items-center gap-2 rounded-xl border border-app-border bg-app-input px-3 text-xs font-semibold text-app-primary shadow-sm hover:bg-app-soft hover:text-app-accent focus:outline-none focus:ring-2 focus:ring-app-ring">
            <i data-lucide="arrow-left" class="h-4 w-4"></i>
            Back to services
        </a>
        <span class="inline-flex items-center gap-2 rounded-full border border-app-border bg-app-input px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider text-app-secondary shadow-sm">
            <span class="h-2 w-2 rounded-full <?= $isReady ? 'bg-app-success' : 'bg-app-danger' ?>"></span>
            Service workspace
        </span>
    </div>

    <section class="mb-4 overflow-hidden rounded-card border border-app-border bg-app-input shadow-sm">
        <div class="grid grid-cols-1 lg:grid-cols-[380px_minmax(0,1fr)]">
            <div class="relative min-h-[280px] bg-app-soft lg:min-h-[410px]">
                <?php if ($serviceImage !== ''): ?>
                    <img src="<?= $serviceImage ?>" alt="<?= $serviceName ?>" class="h-full w-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-app-text/45 via-app-text/5 to-transparent"></div>
                <?php else: ?>
                    <div class="flex h-full min-h-[280px] items-center justify-center text-app-muted lg:min-h-[410px]">
                        <div class="flex h-20 w-20 items-center justify-center rounded-card border border-dashed border-app-border bg-app-input text-app-muted shadow-sm">
                            <i data-lucide="image" class="h-9 w-9"></i>
                        </div>
                    </div>
                <?php endif; ?>
                <span class="absolute left-4 top-4 inline-flex items-center gap-1.5 rounded-full border border-app-border bg-app-input/95 px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider text-app-primary shadow-sm backdrop-blur">
                    <i data-lucide="sparkles" class="h-3.5 w-3.5"></i>
                    <?= $serviceCategory ?>
                </span>
            </div>

            <div class="p-5 sm:p-6 lg:p-7">
                <div class="flex flex-wrap gap-2">
                    <span class="inline-flex rounded-full border border-app-border bg-app-soft px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-app-accent">Service detail</span>
                    <span class="inline-flex rounded-full border border-app-border bg-app-input px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-app-muted">ID #<?= $serviceId ?></span>
                </div>

                <div class="mt-5 grid gap-5 xl:grid-cols-[minmax(0,1fr)_220px] xl:items-start">
                    <div>
                        <h1 class="text-3xl font-bold leading-tight tracking-tight text-app-text sm:text-4xl"><?= $serviceName ?></h1>
                        <p class="mt-3 max-w-3xl text-sm leading-6 text-app-secondary"><?= $serviceDescription !== '' ? $serviceDescription : 'No description added yet.' ?></p>
                    </div>
                    <div class="rounded-card border border-app-border bg-app-soft p-4">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-app-muted">Starting price</p>
                        <p class="mt-1 text-3xl font-bold tracking-tight text-app-primary">RM <?= $servicePrice ?></p>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <div class="rounded-card border border-app-border bg-app-input p-4 shadow-sm">
                        <div class="mb-3 flex h-8 w-8 items-center justify-center rounded-lg bg-app-soft text-app-accent">
                            <i data-lucide="calendar-check" class="h-4 w-4"></i>
                        </div>
                        <p class="text-2xl font-bold tracking-tight text-app-text"><?= $openDaysCount ?>/7</p>
                        <p class="mt-1 text-xs text-app-secondary">Days open</p>
                    </div>
                    <div class="rounded-card border border-app-border bg-app-input p-4 shadow-sm">
                        <div class="mb-3 flex h-8 w-8 items-center justify-center rounded-lg bg-app-danger-soft text-app-danger">
                            <i data-lucide="images" class="h-4 w-4"></i>
                        </div>
                        <p class="text-2xl font-bold tracking-tight text-app-text"><?= $mediaCount ?></p>
                        <p class="mt-1 text-xs text-app-secondary">Portfolio photos</p>
                    </div>
                    <div class="rounded-card border border-app-border bg-app-input p-4 shadow-sm">
                        <div class="mb-3 flex h-8 w-8 items-center justify-center rounded-lg bg-app-soft text-app-primary">
                            <i data-lucide="clock" class="h-4 w-4"></i>
                        </div>
                        <p class="text-2xl font-bold tracking-tight text-app-text"><?= $slotDuration ?>m</p>
                        <p class="mt-1 text-xs text-app-secondary">Slot duration</p>
                    </div>
                </div>

                <div class="mt-5 rounded-card border <?= $isReady ? 'border-app-border bg-app-soft' : 'border-app-border bg-app-danger-soft' ?> p-4">
                    <div class="flex items-start gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-app-input <?= $isReady ? 'text-app-success' : 'text-app-danger' ?>">
                            <i data-lucide="<?= $isReady ? 'check-circle-2' : 'alert-circle' ?>" class="h-4 w-4"></i>
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <p class="text-sm font-bold text-app-text"><?= $isReady ? 'Service looks ready' : 'Needs attention' ?></p>
                                <span class="rounded-full border border-app-border bg-app-input px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider <?= $isReady ? 'text-app-success' : 'text-app-danger' ?>"><?= $isReady ? 'Ready' : count($attentionItems) . ' item' . (count($attentionItems) === 1 ? '' : 's') ?></span>
                            </div>
                            <p class="mt-1 text-xs leading-5 text-app-secondary"><?= $isReady ? 'Customers can understand this service, see visuals, and find available booking slots.' : 'Complete these before customers can book confidently.' ?></p>
                            <?php if (!$isReady): ?>
                                <div class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-2">
                                    <?php foreach ($attentionItems as $item): ?>
                                        <div class="flex items-start gap-2 rounded-xl border border-app-border bg-app-input px-3 py-2">
                                            <i data-lucide="<?= htmlspecialchars($item['icon'], ENT_QUOTES, 'UTF-8') ?>" class="mt-0.5 h-3.5 w-3.5 shrink-0 text-app-danger"></i>
                                            <div>
                                                <p class="text-xs font-bold text-app-text"><?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?></p>
                                                <p class="text-[11px] text-app-secondary"><?= htmlspecialchars($item['detail'], ENT_QUOTES, 'UTF-8') ?></p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mb-4 rounded-card border border-app-border bg-app-input p-4 shadow-sm">
        <div class="mb-4 flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 class="text-sm font-bold text-app-text">Portfolio photos</h2>
                <p class="mt-1 text-xs text-app-secondary">Customer-facing visuals for this service.</p>
            </div>
            <label class="inline-flex h-9 cursor-pointer items-center gap-2 rounded-xl bg-app-primary px-4 text-xs font-bold text-app-white shadow-sm hover:bg-app-accent focus-within:ring-2 focus-within:ring-app-ring">
                <i data-lucide="image-plus" class="h-4 w-4"></i>
                Add photo
                <input id="serviceMediaInput" type="file" accept="image/*" class="hidden">
            </label>
        </div>
        <p id="serviceMediaMessage" class="mb-4 hidden rounded-xl border border-app-border bg-app-soft px-3 py-2 text-sm text-app-secondary"></p>
        <div id="serviceMediaGrid" class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5">
            <?php foreach ($media as $item): ?>
                <article class="service-media-card group relative aspect-square overflow-hidden rounded-xl border border-app-border bg-app-soft shadow-sm" data-media-id="<?= (int)$item['id'] ?>">
                    <img src="<?= htmlspecialchars($item['file_url'] ?? '', ENT_QUOTES, 'UTF-8') ?>" alt="Service media" class="h-full w-full object-cover transition group-hover:scale-105">
                    <div class="absolute inset-0 bg-app-text/0 transition group-hover:bg-app-text/10"></div>
                    <button type="button" onclick="deleteServiceMedia(<?= (int)$item['id'] ?>)" class="absolute right-2 top-2 flex h-8 w-8 items-center justify-center rounded-lg bg-app-input/95 text-app-danger opacity-0 shadow-sm transition hover:bg-app-danger-soft group-hover:opacity-100">
                        <i data-lucide="trash-2" class="h-4 w-4"></i>
                    </button>
                </article>
            <?php endforeach; ?>
        </div>
        <div id="serviceMediaEmpty" class="<?= empty($media) ? '' : 'hidden' ?> rounded-xl border border-dashed border-app-border bg-app-soft px-6 py-14 text-center">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-app-input text-app-muted shadow-sm">
                <i data-lucide="image-plus" class="h-5 w-5"></i>
            </div>
            <p class="mt-3 text-sm font-bold text-app-text">No portfolio photos yet</p>
            <p class="mt-1 text-xs text-app-secondary">Upload the first image customers will see.</p>
        </div>
    </section>

    <section class="mb-4 rounded-card border border-app-border bg-app-input shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-app-panel-border p-4">
            <div>
                <h2 class="text-sm font-bold text-app-text">Weekly availability</h2>
                <p class="mt-1 text-xs text-app-secondary">Set service hours, slot duration, buffer time, and capacity.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="rounded-full border border-app-border bg-app-soft px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-app-secondary"><?= $openDaysCount ?> days open</span>
                <button type="button" id="saveAvailabilityBtn" class="inline-flex h-9 items-center gap-2 rounded-xl bg-app-primary px-4 text-xs font-bold text-app-white shadow-sm hover:bg-app-accent focus:outline-none focus:ring-2 focus:ring-app-ring">
                    <i data-lucide="save" class="h-4 w-4"></i>
                    Save schedule
                </button>
            </div>
        </div>
        <div class="p-4">
            <p id="availabilityMessage" class="mb-4 hidden rounded-xl border border-app-border bg-app-soft px-3 py-2 text-sm text-app-secondary"></p>
            <div class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-3">
                <label class="rounded-card border border-app-border bg-app-soft p-4">
                    <span class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-wider text-app-muted"><i data-lucide="clock" class="h-3.5 w-3.5"></i>Slot duration</span>
                    <input id="availabilityDuration" type="number" min="15" step="15" value="<?= $slotDuration ?>" class="mt-2 h-10 w-full rounded-xl border border-app-border bg-app-input px-3 text-sm font-semibold text-app-text outline-none focus:border-app-focus focus:ring-2 focus:ring-app-ring">
                </label>
                <label class="rounded-card border border-app-border bg-app-soft p-4">
                    <span class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-wider text-app-muted"><i data-lucide="pause" class="h-3.5 w-3.5"></i>Buffer minutes</span>
                    <input id="availabilityBuffer" type="number" min="0" step="5" value="<?= (int)($availability['buffer_minutes'] ?? 0) ?>" class="mt-2 h-10 w-full rounded-xl border border-app-border bg-app-input px-3 text-sm font-semibold text-app-text outline-none focus:border-app-focus focus:ring-2 focus:ring-app-ring">
                </label>
                <label class="rounded-card border border-app-border bg-app-soft p-4">
                    <span class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-wider text-app-muted"><i data-lucide="users" class="h-3.5 w-3.5"></i>Max concurrent</span>
                    <input id="availabilityConcurrent" type="number" min="1" step="1" value="<?= (int)($availability['max_concurrent'] ?? 1) ?>" class="mt-2 h-10 w-full rounded-xl border border-app-border bg-app-input px-3 text-sm font-semibold text-app-text outline-none focus:border-app-focus focus:ring-2 focus:ring-app-ring">
                </label>
            </div>
            <div class="overflow-x-auto rounded-xl border border-app-border">
                <table class="w-full min-w-[760px] text-sm">
                    <thead class="bg-app-soft text-[10px] uppercase tracking-wider text-app-muted">
                        <tr>
                            <th class="px-4 py-3 text-left font-bold">Day</th>
                            <th class="px-4 py-3 text-left font-bold">Status</th>
                            <th class="px-4 py-3 text-left font-bold">Opens</th>
                            <th class="px-4 py-3 text-left font-bold">Closes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-app-panel-border">
                        <?php foreach ($days as $dayNumber => $dayName): ?>
                            <?php
                            $row = $weeklyByDay[$dayNumber] ?? [];
                            $isAvailable = $isDayAvailable($dayNumber, $row);
                            $open = substr((string)($row['open_time'] ?? '09:00'), 0, 5);
                            $close = substr((string)($row['close_time'] ?? '17:00'), 0, 5);
                            ?>
                            <tr class="availability-day-row bg-app-input transition hover:bg-app-soft" data-day="<?= $dayNumber ?>">
                                <td class="px-4 py-3">
                                    <div class="text-xs font-bold text-app-text"><?= $dayName ?></div>
                                    <div class="text-[10px] text-app-muted">Day <?= $dayNumber ?></div>
                                </td>
                                <td class="px-4 py-3">
                                    <label class="inline-flex items-center gap-2 rounded-full border border-app-border bg-app-soft px-3 py-1.5 text-[11px] font-semibold text-app-primary">
                                        <input type="checkbox" class="availability-open h-4 w-4 rounded border-app-border accent-[#6d4c5b]" <?= $isAvailable ? 'checked' : '' ?>>
                                        Open
                                    </label>
                                </td>
                                <td class="px-4 py-3">
                                    <input type="time" class="availability-start h-9 w-[96px] rounded-lg border border-app-border bg-app-soft px-2 text-xs font-semibold text-app-text outline-none focus:border-app-focus focus:bg-app-input focus:ring-2 focus:ring-app-ring" value="<?= htmlspecialchars($open, ENT_QUOTES, 'UTF-8') ?>">
                                </td>
                                <td class="px-4 py-3">
                                    <input type="time" class="availability-end h-9 w-[96px] rounded-lg border border-app-border bg-app-soft px-2 text-xs font-semibold text-app-text outline-none focus:border-app-focus focus:bg-app-input focus:ring-2 focus:ring-app-ring" value="<?= htmlspecialchars($close, ENT_QUOTES, 'UTF-8') ?>">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <section class="grid grid-cols-1 gap-4 xl:grid-cols-2">
        <div class="rounded-card border border-app-border bg-app-input p-4 shadow-sm">
            <div class="mb-4 flex items-start justify-between gap-3">
                <div>
                    <h2 class="text-sm font-bold text-app-text">Special dates</h2>
                    <p class="mt-1 text-xs text-app-secondary">Close a date, open an extra date, or set custom hours.</p>
                </div>
                <span class="rounded-full border border-app-border bg-app-soft px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-app-secondary"><?= $overrideCount ?> saved</span>
            </div>
            <div class="rounded-card border border-app-border bg-app-soft p-4">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <label class="text-[10px] font-bold uppercase tracking-wider text-app-muted">Date<input id="overrideDate" type="date" class="mt-1.5 h-10 w-full rounded-xl border border-app-border bg-app-input px-3 text-xs font-medium text-app-text outline-none focus:border-app-focus focus:ring-2 focus:ring-app-ring"></label>
                    <label class="text-[10px] font-bold uppercase tracking-wider text-app-muted">Type<select id="overrideType" class="mt-1.5 h-10 w-full rounded-xl border border-app-border bg-app-input px-3 text-xs font-medium text-app-text outline-none focus:border-app-focus focus:ring-2 focus:ring-app-ring"><option value="unavailable">Unavailable</option><option value="custom_hours">Custom hours</option><option value="available">Available</option></select></label>
                    <label class="text-[10px] font-bold uppercase tracking-wider text-app-muted">Opens<input id="overrideOpen" type="time" value="09:00" class="mt-1.5 h-10 w-full rounded-xl border border-app-border bg-app-input px-3 text-xs font-medium text-app-text outline-none focus:border-app-focus focus:ring-2 focus:ring-app-ring"></label>
                    <label class="text-[10px] font-bold uppercase tracking-wider text-app-muted">Closes<input id="overrideClose" type="time" value="17:00" class="mt-1.5 h-10 w-full rounded-xl border border-app-border bg-app-input px-3 text-xs font-medium text-app-text outline-none focus:border-app-focus focus:ring-2 focus:ring-app-ring"></label>
                    <label class="text-[10px] font-bold uppercase tracking-wider text-app-muted sm:col-span-2">Reason<input id="overrideReason" type="text" placeholder="Holiday, private event..." class="mt-1.5 h-10 w-full rounded-xl border border-app-border bg-app-input px-3 text-xs font-medium normal-case tracking-normal text-app-text outline-none placeholder:text-app-muted focus:border-app-focus focus:ring-2 focus:ring-app-ring"></label>
                </div>
                <button type="button" id="saveOverrideBtn" class="mt-3 inline-flex h-9 items-center gap-2 rounded-xl border border-app-border bg-app-input px-4 text-xs font-semibold text-app-primary shadow-sm hover:bg-app-soft focus:outline-none focus:ring-2 focus:ring-app-ring">
                    <i data-lucide="calendar-plus" class="h-4 w-4"></i>
                    Save override
                </button>
            </div>
            <div id="overrideList" class="mt-4 space-y-2">
                <?php foreach ($overrideRows as $override): ?>
                    <article class="override-row flex items-center justify-between gap-3 rounded-xl border border-app-border bg-app-input px-4 py-3 text-sm transition hover:bg-app-soft" data-override-id="<?= (int)$override['id'] ?>">
                        <span class="min-w-0">
                            <strong class="block text-xs text-app-text"><?= htmlspecialchars($override['date'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>
                            <span class="mt-1 inline-flex rounded-full bg-app-soft px-2.5 py-1 text-[10px] font-semibold capitalize text-app-secondary"><?= htmlspecialchars(str_replace('_', ' ', $override['type'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                        </span>
                        <button type="button" onclick="deleteOverride(<?= (int)$override['id'] ?>)" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-app-danger-soft text-app-danger transition hover:bg-app-danger-soft"><i data-lucide="trash-2" class="h-4 w-4"></i></button>
                    </article>
                <?php endforeach; ?>
                <div id="overrideEmpty" class="<?= empty($overrideRows) ? '' : 'hidden' ?> rounded-xl border border-dashed border-app-border bg-app-soft px-4 py-10 text-center">
                    <p class="text-sm font-bold text-app-text">No special dates yet</p>
                    <p class="mt-1 text-xs text-app-secondary">Saved overrides will appear here.</p>
                </div>
            </div>
        </div>

        <div class="rounded-card border border-app-border bg-app-input p-4 shadow-sm">
            <div class="mb-4">
                <h2 class="text-sm font-bold text-app-text">Booking preview</h2>
                <p class="mt-1 text-xs text-app-secondary">Check exact slots customers can book for a selected date.</p>
            </div>
            <div class="rounded-card border border-app-border bg-app-soft p-4">
                <div class="flex flex-col gap-3 sm:flex-row">
                    <label class="min-w-0 flex-1 text-[10px] font-bold uppercase tracking-wider text-app-muted">Preview date<input id="previewDate" type="date" class="mt-1.5 h-10 w-full rounded-xl border border-app-border bg-app-input px-3 text-xs font-medium text-app-text outline-none focus:border-app-focus focus:ring-2 focus:ring-app-ring"></label>
                    <button type="button" id="previewSlotsBtn" class="mt-auto inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-app-primary px-5 text-xs font-bold text-app-white shadow-sm hover:bg-app-accent focus:outline-none focus:ring-2 focus:ring-app-ring">
                        <i data-lucide="eye" class="h-4 w-4"></i>
                        Show slots
                    </button>
                </div>
                <div id="previewSlotsResult" class="mt-4 flex min-h-[132px] flex-wrap content-start gap-2 rounded-xl border border-dashed border-app-border bg-app-input p-4 text-sm text-app-secondary"></div>
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

function setMessage(element, text = '') {
    if (!element) return;

    element.textContent = text;
    element.classList.toggle('hidden', text === '');
}

function showMediaMessage(text) {
    setMessage(mediaMessage, text);
}

function hideMediaMessage() {
    setMessage(mediaMessage);
}

function fileToDataUrl(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = () => resolve(reader.result);
        reader.onerror = reject;
        reader.readAsDataURL(file);
    });
}

async function jsonPost(url, payload = {}) {
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
    setMessage(availabilityMessage, text);
}

function appendMedia(media) {
    mediaEmpty.classList.add('hidden');
    mediaGrid.insertAdjacentHTML('afterbegin', `
        <article class="service-media-card group relative aspect-square overflow-hidden rounded-xl border border-app-border bg-app-soft shadow-sm" data-media-id="${media.id}">
            <img src="${media.file_url}" alt="Service media" class="h-full w-full object-cover transition group-hover:scale-105">
            <div class="absolute inset-0 bg-app-text/0 transition group-hover:bg-app-text/10"></div>
            <button type="button" onclick="deleteServiceMedia(${media.id})" class="absolute right-2 top-2 flex h-8 w-8 items-center justify-center rounded-lg bg-app-input/95 text-app-danger opacity-0 shadow-sm transition hover:bg-app-danger-soft group-hover:opacity-100">
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
        const result = await jsonPost(serviceMediaCreateUrl, { img });
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
        await jsonPost(serviceMediaDeleteUrl + encodeURIComponent(mediaId));
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
        await jsonPost(availabilitySaveUrl, {
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
        await jsonPost(overrideSaveUrl, {
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
        await jsonPost(overrideDeleteUrl + encodeURIComponent(overrideId));
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
        const result = await jsonPost(previewUrl, { date: document.getElementById('previewDate').value });
        const slots = result.preview?.slots || [];

        if (!slots.length) {
            resultBox.textContent = 'Closed or no available slots for this date.';
            return;
        }

        resultBox.innerHTML = slots.map(slot => `<span class="rounded-full border border-app-border bg-app-input px-3 py-1 text-xs font-semibold text-app-text">${slot.start_time} - ${slot.end_time} (${slot.confirmed_count}/${slot.max_concurrent})</span>`).join('');
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
