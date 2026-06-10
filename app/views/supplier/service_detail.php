<?php
$supplier = $supplier ?? [];
$service = $service ?? [];
$media = $service['media'] ?? [];

$dashboardTitle = 'Supplier';
$dashboardCrumb = 'Service Detail';
$dashboardSearchPlaceholder = 'Search services, packages...';
$dashboardContentClass = 'bg-[#f7f3ed] px-6 py-6';
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
?>
<div class="mx-auto max-w-7xl font-ui text-app-text">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <a href="<?= URLROOT ?>/supplier/services" class="inline-flex items-center gap-2 rounded-xl border border-app-border bg-app-input px-4 py-2 text-sm font-semibold text-app-text shadow-sm hover:bg-app-soft">
            <i data-lucide="arrow-left" class="h-4 w-4"></i>
            Back
        </a>
    </div>

    <section class="grid grid-cols-1 gap-5 lg:grid-cols-[360px_1fr]">
        <div class="overflow-hidden rounded-card border border-app-border bg-app-input shadow-sm">
            <div class="aspect-[4/3] bg-app-soft">
                <?php if ($serviceImage !== ''): ?>
                    <img src="<?= $serviceImage ?>" alt="<?= $serviceName ?>" class="h-full w-full object-cover">
                <?php else: ?>
                    <div class="flex h-full items-center justify-center text-5xl text-app-muted">◇</div>
                <?php endif; ?>
            </div>
            <div class="p-5">
                <span class="inline-flex rounded-full bg-app-soft px-3 py-1 text-xs font-semibold text-app-accent"><?= $serviceCategory ?></span>
                <h1 class="mt-3 text-2xl font-bold text-app-text"><?= $serviceName ?></h1>
                <p class="mt-2 text-xl font-bold text-app-primary">RM <?= $servicePrice ?></p>
                <?php if ($serviceDescription !== ''): ?>
                    <p class="mt-3 text-sm leading-6 text-app-secondary"><?= $serviceDescription ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="rounded-card border border-app-border bg-app-input p-5 shadow-sm">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-bold text-app-text">Portfolio Media</h2>
                    <p class="mt-1 text-sm text-app-secondary">Photos here can be shown to customers for this service.</p>
                </div>
                <label class="inline-flex cursor-pointer items-center gap-2 rounded-xl bg-app-primary px-4 py-2 text-sm font-semibold text-app-white shadow-sm hover:bg-app-accent">
                    <i data-lucide="image-plus" class="h-4 w-4"></i>
                    Add Photo
                    <input id="serviceMediaInput" type="file" accept="image/*" class="hidden">
                </label>
            </div>

            <p id="serviceMediaMessage" class="mb-3 hidden rounded-xl border border-app-border bg-app-soft px-3 py-2 text-sm text-app-secondary"></p>

            <div id="serviceMediaGrid" class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-4">
                <?php foreach ($media as $item): ?>
                    <article class="service-media-card group relative overflow-hidden rounded-xl border border-app-border bg-app-soft" data-media-id="<?= (int)$item['id'] ?>">
                        <img src="<?= htmlspecialchars($item['file_url'] ?? '', ENT_QUOTES, 'UTF-8') ?>" alt="Service media" class="aspect-square w-full object-cover">
                        <button type="button" onclick="deleteServiceMedia(<?= (int)$item['id'] ?>)" class="absolute right-2 top-2 hidden rounded-lg bg-white/90 p-2 text-app-danger shadow-sm group-hover:block">
                            <i data-lucide="trash-2" class="h-4 w-4"></i>
                        </button>
                    </article>
                <?php endforeach; ?>
            </div>

            <p id="serviceMediaEmpty" class="<?= empty($media) ? '' : 'hidden' ?> py-16 text-center text-sm text-app-muted">No portfolio photos yet.</p>
        </div>
    </section>

    <section class="mt-5 rounded-card border border-app-border bg-app-input p-5 shadow-sm">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-bold text-app-text">Availability</h2>
                <p class="mt-1 text-sm text-app-secondary">This schedule only controls this service. Other services can stay open.</p>
            </div>
            <button type="button" id="saveAvailabilityBtn" class="inline-flex items-center gap-2 rounded-xl bg-app-primary px-4 py-2 text-sm font-semibold text-app-white shadow-sm hover:bg-app-accent">
                <i data-lucide="save" class="h-4 w-4"></i>
                Save Schedule
            </button>
        </div>

        <p id="availabilityMessage" class="mb-3 hidden rounded-xl border border-app-border bg-app-soft px-3 py-2 text-sm text-app-secondary"></p>

        <div class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-3">
            <label class="text-xs font-semibold uppercase tracking-wide text-app-muted">
                Slot Duration
                <input id="availabilityDuration" type="number" min="15" step="15" value="<?= (int)($availability['duration_minutes'] ?? 60) ?>" class="mt-1 w-full rounded-xl border border-app-border bg-app-soft px-3 py-2 text-sm normal-case tracking-normal text-app-text outline-none focus:ring-2 focus:ring-app-ring">
            </label>
            <label class="text-xs font-semibold uppercase tracking-wide text-app-muted">
                Buffer Minutes
                <input id="availabilityBuffer" type="number" min="0" step="5" value="<?= (int)($availability['buffer_minutes'] ?? 0) ?>" class="mt-1 w-full rounded-xl border border-app-border bg-app-soft px-3 py-2 text-sm normal-case tracking-normal text-app-text outline-none focus:ring-2 focus:ring-app-ring">
            </label>
            <label class="text-xs font-semibold uppercase tracking-wide text-app-muted">
                Max Concurrent
                <input id="availabilityConcurrent" type="number" min="1" step="1" value="<?= (int)($availability['max_concurrent'] ?? 1) ?>" class="mt-1 w-full rounded-xl border border-app-border bg-app-soft px-3 py-2 text-sm normal-case tracking-normal text-app-text outline-none focus:ring-2 focus:ring-app-ring">
            </label>
        </div>

        <div class="overflow-x-auto rounded-xl border border-app-border">
            <table class="w-full min-w-[720px] text-sm">
                <thead class="bg-app-soft text-xs uppercase tracking-wide text-app-muted">
                    <tr>
                        <th class="px-4 py-3 text-left">Day</th>
                        <th class="px-4 py-3 text-left">Open</th>
                        <th class="px-4 py-3 text-left">Start</th>
                        <th class="px-4 py-3 text-left">End</th>
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
                        <tr class="availability-day-row" data-day="<?= $dayNumber ?>">
                            <td class="px-4 py-3 font-semibold text-app-text"><?= $dayName ?></td>
                            <td class="px-4 py-3">
                                <input type="checkbox" class="availability-open accent-[#6e4e58]" <?= $isAvailable ? 'checked' : '' ?>>
                            </td>
                            <td class="px-4 py-3">
                                <input type="time" class="availability-start rounded-lg border border-app-border bg-app-soft px-3 py-2 text-sm" value="<?= htmlspecialchars($open, ENT_QUOTES, 'UTF-8') ?>">
                            </td>
                            <td class="px-4 py-3">
                                <input type="time" class="availability-end rounded-lg border border-app-border bg-app-soft px-3 py-2 text-sm" value="<?= htmlspecialchars($close, ENT_QUOTES, 'UTF-8') ?>">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-5 grid grid-cols-1 gap-4 lg:grid-cols-[1fr_1fr]">
            <div class="rounded-xl border border-app-border bg-app-soft p-4">
                <h3 class="text-sm font-bold text-app-text">Date Override</h3>
                <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <input id="overrideDate" type="date" class="rounded-xl border border-app-border bg-app-input px-3 py-2 text-sm">
                    <select id="overrideType" class="rounded-xl border border-app-border bg-app-input px-3 py-2 text-sm">
                        <option value="unavailable">Unavailable</option>
                        <option value="custom_hours">Custom hours</option>
                        <option value="available">Available</option>
                    </select>
                    <input id="overrideOpen" type="time" value="09:00" class="rounded-xl border border-app-border bg-app-input px-3 py-2 text-sm">
                    <input id="overrideClose" type="time" value="17:00" class="rounded-xl border border-app-border bg-app-input px-3 py-2 text-sm">
                    <input id="overrideReason" type="text" placeholder="Reason" class="sm:col-span-2 rounded-xl border border-app-border bg-app-input px-3 py-2 text-sm">
                </div>
                <button type="button" id="saveOverrideBtn" class="mt-3 rounded-xl border border-app-border bg-app-input px-4 py-2 text-sm font-semibold text-app-text shadow-sm hover:bg-app-surface">Save Override</button>

                <div id="overrideList" class="mt-4 space-y-2">
                    <?php foreach ($overrideRows as $override): ?>
                        <article class="override-row flex items-center justify-between gap-3 rounded-xl border border-app-border bg-app-input px-3 py-2 text-sm" data-override-id="<?= (int)$override['id'] ?>">
                            <span>
                                <strong><?= htmlspecialchars($override['date'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>
                                <span class="text-app-secondary"><?= htmlspecialchars($override['type'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                            </span>
                            <button type="button" onclick="deleteOverride(<?= (int)$override['id'] ?>)" class="text-app-danger"><i data-lucide="trash-2" class="h-4 w-4"></i></button>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="rounded-xl border border-app-border bg-app-soft p-4">
                <h3 class="text-sm font-bold text-app-text">Preview Customer Slots</h3>
                <div class="mt-3 flex gap-2">
                    <input id="previewDate" type="date" class="min-w-0 flex-1 rounded-xl border border-app-border bg-app-input px-3 py-2 text-sm">
                    <button type="button" id="previewSlotsBtn" class="rounded-xl bg-app-primary px-4 py-2 text-sm font-semibold text-app-white">Preview</button>
                </div>
                <div id="previewSlotsResult" class="mt-4 flex flex-wrap gap-2 text-sm text-app-secondary"></div>
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
        <article class="service-media-card group relative overflow-hidden rounded-xl border border-app-border bg-app-soft" data-media-id="${media.id}">
            <img src="${media.file_url}" alt="Service media" class="aspect-square w-full object-cover">
            <button type="button" onclick="deleteServiceMedia(${media.id})" class="absolute right-2 top-2 hidden rounded-lg bg-white/90 p-2 text-app-danger shadow-sm group-hover:block">
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

        resultBox.innerHTML = slots.map(slot => `<span class="rounded-full border border-app-border bg-app-input px-3 py-1 font-semibold text-app-text">${slot.start_time} - ${slot.end_time}</span>`).join('');
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
