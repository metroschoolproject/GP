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
    return 'RM ' . number_format((float)$value, 0);
};
$calendarCssVersion = file_exists(APPROOT . '/../public/css/supplier-service-calendar.css') ? filemtime(APPROOT . '/../public/css/supplier-service-calendar.css') : time();

$dashboardContent = function () use ($services, $h, $money, $calendarCssVersion) {
?>
<link rel="stylesheet" href="<?= URLROOT ?>/public/css/supplier-service-calendar.css?v=<?= $calendarCssVersion ?>">

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
