<?php
$supplier = $supplier ?? [];
$services = is_array($services ?? null) ? $services : [];
$dashboardTitle = 'Supplier';
$dashboardCrumb = 'Calendar';
$dashboardSearchPlaceholder = 'Search calendars...';
$dashboardContentClass = 'bg-app-content px-6 py-6';
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

<section class="calendar-page">
  <div class="calendar-top">
    <div>
      <div class="calendar-kicker">Supplier workspace</div>
      <h1>Calendar</h1>
      <p>Choose a service to manage real dates, special availability, and unavailable days.</p>
    </div>
    <a class="calendar-btn ghost" href="<?= URLROOT ?>/supplier/services ?>">
      <i class="ti ti-briefcase"></i>
      Services
    </a>
  </div>

  <?php if (!empty($services)): ?>
  <section class="capacity-overview">
    <div class="capacity-overview-head">
      <div>
        <div class="calendar-kicker">All services</div>
        <h2>Date capacity overview</h2>
        <p>Pick a date to see remaining capacity across all your services.</p>
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
  </section>
  <?php endif; ?>

  <?php if (empty($services)): ?>
    <div class="calendar-empty-panel">
      <i class="ti ti-calendar-off"></i>
      <h2>No service calendars yet</h2>
      <p>Create a service first, then set weekly availability and special dates from its calendar.</p>
      <a class="calendar-btn" href="<?= URLROOT ?>/supplier/services ?>">Go to services</a>
    </div>
  <?php else: ?>
    <div class="calendar-service-grid">
      <?php foreach ($services as $service): ?>
        <?php
        $serviceId = (int)($service['id'] ?? 0);
        $status = ($service['status'] ?? 'inactive') === 'active' ? 'active' : 'inactive';
        ?>
        <article class="calendar-service-card">
          <div class="service-card-media">
            <?php if (!empty($service['img'])): ?>
              <img src="<?= $h($service['img']) ?>" alt="<?= $h($service['name'] ?? 'Service') ?>">
            <?php else: ?>
              <div class="service-card-placeholder"><i class="ti ti-photo"></i></div>
            <?php endif; ?>
            <span class="service-status <?= $status ?>"><?= $h($status) ?></span>
          </div>
          <div class="service-card-body">
            <div class="calendar-kicker"><?= $h($service['category'] ?? 'Service') ?></div>
            <h2><?= $h($service['name'] ?? 'Untitled service') ?></h2>
            <div class="service-card-meta">
              <span><?= $money($service['price_min'] ?? $service['price'] ?? 0) ?></span>
              <span><?= $h(($service['booking_type'] ?? 'fullday') === 'slot' ? 'Slots' : 'Full day') ?></span>
            </div>
            <a class="calendar-btn" href="<?= URLROOT ?>/supplier/serviceCalendar/<?= $serviceId ?>">
              <i class="ti ti-calendar"></i>
              Open calendar
            </a>
          </div>
        </article>
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
    <?php require_once APPROOT . '/views/dashboardLayout/head.php'; ?>
</head>
<body class="grid h-screen gap-0 bg-app-page" style="grid-template-columns: 280px 1fr;">
    <?php require APPROOT . '/views/dashboardLayout/suppliersidebar.php'; ?>
</body>
</html>
