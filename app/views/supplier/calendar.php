<?php
$supplier = $supplier ?? [];
$services = is_array($services ?? null) ? $services : [];
$dashboardTitle = 'Supplier';
$dashboardCrumb = 'Calendar';
$dashboardSearchPlaceholder = 'Search calendars...';
$dashboardContentClass = 'bg-[#F4F1EE] px-0 py-0 overflow-y-auto';
$dashboardBreadcrumbs = [
    ['label' => 'Dashboard', 'url' => URLROOT . '/supplier/dashboard'],
    ['label' => 'Calendar', 'url' => null],
];
$h = function ($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
};
$money = function ($value) {
    return number_format((float)$value, 0) . ' MMK';
};
$allCapacityUrl = $allCapacityUrl ?? (URLROOT . '/supplier/allServicesCapacityPreview');
$calendarCssVersion = file_exists(APPROOT . '/../public/css/supplier-service-calendar.css') ? filemtime(APPROOT . '/../public/css/supplier-service-calendar.css') : time();
$overviewJsVersion = file_exists(APPROOT . '/../public/js/supplier-calendar-overview.js') ? filemtime(APPROOT . '/../public/js/supplier-calendar-overview.js') : time();
$overviewConfig = [
    'urls' => [
        'capacity' => $allCapacityUrl,
    ],
];

$dashboardContent = function () use ($services, $h, $money, $calendarCssVersion, $overviewJsVersion, $overviewConfig) {
?>
<link rel="stylesheet" href="<?= URLROOT ?>/public/css/supplier-service-calendar.css?v=<?= $calendarCssVersion ?>">
<script>window.calendarOverviewConfig = <?= json_encode($overviewConfig, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;</script>

<section class="calendar-page" style="padding:28px 32px;font-family:'Poppins',system-ui,sans-serif">
  <div class="page-header" style="display:flex;align-items:flex-end;justify-content:space-between;gap:16px;margin-bottom:22px">
    <div>
      <p style="margin-bottom:4px;color:#b79c8b;font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase">Supplier workspace</p>
      <h1 style="margin:0;font-size:22px;font-weight:700;color:#111827;letter-spacing:-.3px">Calendar</h1>
    </div>
    <a href="<?= URLROOT ?>/supplier/services ?>" class="btn-ghost" style="display:inline-flex;align-items:center;gap:6px;padding:0 14px;height:34px;border:1px solid #ead8c7;border-radius:.75rem;background:#fff;color:#6d4c5b;font-size:12px;font-weight:700;font-family:inherit;text-decoration:none;cursor:pointer;transition:background .12s">
      <i class="ti ti-briefcase" style="font-size:14px"></i>
      Services
    </a>
  </div>

  <?php if (!empty($services)): ?>
  <div style="background:#fff;border:1px solid #ead8c7;border-radius:.75rem;overflow:hidden;box-shadow:0 1px 2px rgba(28,25,23,.04);margin-bottom:20px">
    <div style="padding:14px 20px;border-bottom:1px solid #eddecc;display:flex;align-items:center;gap:8px">
      <div style="width:28px;height:28px;border-radius:.75rem;background:#eddecc;display:flex;align-items:center;justify-content:center;color:#6d4c5b"><i class="ti ti-calendar-event" style="font-size:14px"></i></div>
      <div>
        <span style="font-size:13px;font-weight:700;color:#111827">Date capacity overview</span>
        <span style="display:block;font-size:11px;color:#b79c8b;margin-top:1px">Pick a date to see remaining capacity across all your services</span>
      </div>
    </div>

    <div class="capacity-overview-body">
      <div class="mini-calendar">
        <div class="mini-cal-toolbar">
          <button type="button" class="icon-btn" id="miniPrevBtn" aria-label="Previous month"><i class="ti ti-chevron-left"></i></button>
          <span id="miniCalLabel" class="mini-cal-label">June 2026</span>
          <button type="button" class="icon-btn" id="miniNextBtn" aria-label="Next month"><i class="ti ti-chevron-right"></i></button>
        </div>
        <div class="mini-cal-weekdays">
          <span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span><span>Su</span>
        </div>
        <div id="miniCalGrid" class="mini-cal-grid"></div>
      </div>

      <div class="capacity-panel">
        <div id="capacityPanelEmpty" class="capacity-panel-empty">
          <i class="ti ti-calendar-event"></i>
          <p>Select a date on the mini calendar to view all services capacity.</p>
        </div>
        <div id="capacityPanelContent" class="capacity-panel-content" hidden>
          <div class="capacity-panel-head">
            <h3 id="capacityDateLabel">—</h3>
            <span id="capacitySummary" class="capacity-summary-badge"></span>
          </div>
          <div id="capacityGrid" class="capacity-grid"></div>
        </div>
        <div id="capacityPanelLoading" class="capacity-panel-empty" hidden>
          <i class="ti ti-loader-2 ti-spin"></i>
          <p>Loading capacity…</p>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <?php if (empty($services)): ?>
    <div style="background:#fff;border:1px solid #ead8c7;border-radius:.75rem;padding:48px 20px;text-align:center;box-shadow:0 1px 2px rgba(28,25,23,.04)">
      <i class="ti ti-calendar-off" style="font-size:32px;color:#b79c8b"></i>
      <h2 style="margin:12px 0 6px;font-size:15px;font-weight:700;color:#111827">No service calendars yet</h2>
      <p style="color:#b79c8b;font-size:12px;max-width:380px;margin:0 auto 16px">Create a service first, then set weekly availability and special dates from its calendar.</p>
      <a href="<?= URLROOT ?>/supplier/services ?>" style="display:inline-flex;align-items:center;gap:6px;padding:0 14px;height:34px;border:1px solid #ead8c7;border-radius:.75rem;background:#6d4c5b;color:#fff;font-size:12px;font-weight:700;text-decoration:none">Go to services</a>
    </div>
  <?php else: ?>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:14px">
      <?php foreach ($services as $service): ?>
        <?php
        $serviceId = (int)($service['id'] ?? 0);
        $status = ($service['status'] ?? 'inactive') === 'active' ? 'active' : 'inactive';
        ?>
        <div style="background:#fff;border:1px solid #ead8c7;border-radius:.75rem;overflow:hidden;box-shadow:0 1px 2px rgba(28,25,23,.04);transition:box-shadow .15s">
          <div style="position:relative;height:140px;overflow:hidden;background:#f7f1ec">
            <?php if (!empty($service['img'])): ?>
              <img src="<?= $h($service['img']) ?>" alt="<?= $h($service['name'] ?? 'Service') ?>" style="width:100%;height:100%;object-fit:cover">
            <?php else: ?>
              <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:#b79c8b"><i class="ti ti-photo" style="font-size:28px"></i></div>
            <?php endif; ?>
            <span style="position:absolute;top:10px;right:10px;display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:20px;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.04em;background:<?= $status === 'active' ? '#ECFDF5;color:#065F46' : '#F5F5F4;color:#78716C' ?>">
              <span style="width:6px;height:6px;border-radius:50%;background:<?= $status === 'active' ? '#10B981' : '#A8A29E' ?>"></span>
              <?= $h($status) ?>
            </span>
          </div>
          <div style="padding:14px 16px">
            <div style="font-size:10px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:#b79c8b;margin-bottom:4px"><?= $h($service['category'] ?? 'Service') ?></div>
            <div style="font-size:14px;font-weight:700;color:#111827;margin-bottom:8px"><?= $h($service['name'] ?? 'Untitled service') ?></div>
            <div style="display:flex;gap:12px;font-size:11px;color:#7b5c69;margin-bottom:12px">
              <span style="font-weight:700"><?= $money($service['price_min'] ?? $service['price'] ?? 0) ?></span>
              <span style="color:#b79c8b"><?= $h(($service['booking_type'] ?? 'fullday') === 'slot' ? 'Slots' : 'Full day') ?></span>
            </div>
            <a href="<?= URLROOT ?>/supplier/serviceCalendar/<?= $serviceId ?>" style="display:inline-flex;align-items:center;gap:6px;padding:0 14px;height:32px;border:1px solid #ead8c7;border-radius:.75rem;background:#fff;color:#6d4c5b;font-size:11px;font-weight:700;text-decoration:none;cursor:pointer;transition:background .12s">
              <i class="ti ti-calendar" style="font-size:13px"></i>
              Open calendar
            </a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<script src="<?= URLROOT ?>/public/js/supplier-calendar-overview.js?v=<?= $overviewJsVersion ?>" defer></script>
<?php
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php $pageTitle = 'Calendar — Golden Promise'; ?>
    <?php require_once APPROOT . '/views/dashboardLayout/head.php'; ?>
</head>
<body class="grid h-screen gap-0 bg-app-page" style="grid-template-columns: 280px 1fr;">
    <?php require APPROOT . '/views/dashboardLayout/suppliersidebar.php'; ?>
</body>
</html>
