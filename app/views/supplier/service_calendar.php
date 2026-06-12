<?php
$supplier = $supplier ?? [];
$service = $service ?? [];
$serviceId = (int)($service['id'] ?? 0);
$serviceNameRaw = $service['name'] ?? 'Service calendar';
$serviceCategoryRaw = $service['category'] ?? 'Service';
$dashboardTitle = 'Supplier';
$dashboardCrumb = 'Calendar';
$dashboardSearchPlaceholder = 'Search calendar...';
$dashboardContentClass = 'bg-app-content px-6 py-6';
$dashboardBreadcrumbs = [
    ['label' => 'Dashboard', 'url' => URLROOT . '/supplier/dashboard'],
    ['label' => 'Services', 'url' => URLROOT . '/supplier/services'],
    ['label' => $serviceNameRaw, 'url' => URLROOT . '/supplier/serviceDetail/' . $serviceId],
    ['label' => 'Calendar', 'url' => null],
];
$h = function ($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
};
$calendarCssVersion = file_exists(APPROOT . '/../public/css/supplier-service-calendar.css') ? filemtime(APPROOT . '/../public/css/supplier-service-calendar.css') : time();
$calendarJsVersion = file_exists(APPROOT . '/../public/js/supplier-service-calendar.js') ? filemtime(APPROOT . '/../public/js/supplier-service-calendar.js') : time();
$calendarConfig = [
    'urls' => [
        'data' => URLROOT . '/supplier/serviceCalendarData/' . $serviceId,
        'overrideSave' => URLROOT . '/supplier/serviceAvailabilityOverrideSave/' . $serviceId,
        'overrideDelete' => URLROOT . '/supplier/serviceAvailabilityOverrideDelete/' . $serviceId . '/',
        'detail' => URLROOT . '/supplier/serviceDetail/' . $serviceId,
    ],
    'service' => [
        'id' => $serviceId,
        'name' => $serviceNameRaw,
        'category' => $serviceCategoryRaw,
    ],
];

$dashboardContent = function () use ($h, $serviceId, $serviceNameRaw, $serviceCategoryRaw, $calendarConfig, $calendarCssVersion, $calendarJsVersion) {
?>
<link rel="stylesheet" href="<?= URLROOT ?>/public/css/supplier-service-calendar.css?v=<?= $calendarCssVersion ?>">
<script>window.serviceCalendarConfig = <?= json_encode($calendarConfig, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;</script>

<section id="supplier-service-calendar" class="calendar-page">
  <div class="calendar-top">
    <div>
      <div class="calendar-kicker"><?= $h($serviceCategoryRaw) ?></div>
      <h1><?= $h($serviceNameRaw) ?> calendar</h1>
      <p>Manage real dates for this service while keeping weekly availability as the default pattern.</p>
    </div>
    <div class="calendar-actions">
      <a class="calendar-btn ghost" href="<?= URLROOT ?>/supplier/serviceDetail/<?= (int)$serviceId ?>">
        <i class="ti ti-arrow-left"></i>
        Service detail
      </a>
      <button type="button" class="calendar-btn" id="todayCalendarBtn">
        <i class="ti ti-calendar-dot"></i>
        Today
      </button>
    </div>
  </div>

  <div id="calendarMessage" class="calendar-message" style="display:none"></div>

  <div class="calendar-workspace">
    <div class="calendar-shell">
      <div class="calendar-toolbar">
        <button type="button" class="icon-btn" id="prevMonthBtn" aria-label="Previous month"><i class="ti ti-chevron-left"></i></button>
        <div>
          <div id="calendarMonthLabel" class="month-label">Loading...</div>
          <div class="month-sub">Click any date to manage availability</div>
        </div>
        <button type="button" class="icon-btn" id="nextMonthBtn" aria-label="Next month"><i class="ti ti-chevron-right"></i></button>
      </div>

      <div class="weekday-row">
        <span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span><span>Sun</span>
      </div>
      <div id="calendarGrid" class="calendar-grid" aria-live="polite"></div>
    </div>

    <aside class="calendar-side">
      <section class="calendar-side-card calendar-focus-card">
        <div class="calendar-kicker">Selected date</div>
        <h2 id="calendarFocusDate">Choose a date</h2>
        <p id="calendarFocusStatus">Review availability, bookings, and overrides.</p>
        <div id="calendarFocusMeta" class="focus-meta"></div>
      </section>

      <section class="calendar-side-card">
        <div class="side-card-head">
          <div>
            <div class="calendar-kicker">Month rhythm</div>
            <h2>Bookings &amp; overrides</h2>
          </div>
          <span id="calendarAgendaCount" class="agenda-count">0</span>
        </div>
        <div id="calendarAgenda" class="calendar-agenda">
          <p class="agenda-empty">Loading calendar notes...</p>
        </div>
      </section>
    </aside>
  </div>

  <div class="calendar-legend" aria-label="Calendar legend">
    <span><b class="dot open"></b>Open</span>
    <span><b class="dot custom_hours"></b>Custom</span>
    <span><b class="dot booked"></b>Booked</span>
    <span><b class="dot unavailable"></b>Unavailable</span>
    <span><b class="dot closed"></b>Closed</span>
  </div>

  <div id="calendarModal" class="calendar-modal" hidden>
    <div class="calendar-modal-backdrop" data-close-calendar-modal></div>
    <form id="calendarOverrideForm" class="calendar-dialog">
      <div class="dialog-head">
        <div>
          <div class="calendar-kicker">Date override</div>
          <h2 id="modalDateLabel">Selected date</h2>
        </div>
        <button type="button" class="icon-btn" data-close-calendar-modal aria-label="Close"><i class="ti ti-x"></i></button>
      </div>

      <input type="hidden" id="overrideDate">
      <input type="hidden" id="overrideId">

      <div id="modalBookings" class="booking-list" hidden></div>

      <label class="field">
        <span>Status</span>
        <select id="overrideType">
          <option value="unavailable">Unavailable</option>
          <option value="custom_hours">Custom hours</option>
          <option value="available">Available</option>
        </select>
      </label>

      <div id="customHoursFields" class="time-row">
        <label class="field">
          <span>Open</span>
          <input id="overrideOpenTime" type="time" value="09:00">
        </label>
        <label class="field">
          <span>Close</span>
          <input id="overrideCloseTime" type="time" value="17:00">
        </label>
      </div>

      <label class="field">
        <span>Note</span>
        <input id="overrideReason" type="text" maxlength="255" placeholder="Optional note">
      </label>

      <div class="dialog-actions">
        <button type="button" class="calendar-btn danger ghost" id="clearOverrideBtn">Clear override</button>
        <button type="submit" class="calendar-btn"><i class="ti ti-check"></i>Save date</button>
      </div>
    </form>
  </div>
</section>

<script src="<?= URLROOT ?>/public/js/supplier-service-calendar.js?v=<?= $calendarJsVersion ?>" defer></script>
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
