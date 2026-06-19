<?php
$service = $service ?? [];
$message = $message ?? '';
$readiness = is_array($service['readiness'] ?? null) ? $service['readiness'] : ['ready' => false, 'missing' => []];
$media = is_array($service['media'] ?? null) ? $service['media'] : [];
$rooms = is_array($service['venue_rooms'] ?? null) ? $service['venue_rooms'] : [];
$weekly = is_array($service['availability']['weekly'] ?? null) ? $service['availability']['weekly'] : [];
$isVenue = strtolower((string)($service['category'] ?? '')) === 'venue';
$isApproved = ($service['status'] ?? 'inactive') === 'active';
$isReady = !empty($readiness['ready']);
$coverImage = $service['img'] ?? ($media[0]['file_url'] ?? '');
$priceMin = (float)($service['price_min'] ?? $service['price'] ?? 0);
$priceMax = max($priceMin, (float)($service['price_max'] ?? $priceMin));
$openDays = count(array_filter($weekly, static fn($row) => !empty($row['is_available'])));

$dashboardTitle = 'Admin';
$dashboardCrumb = 'Service review';
$dashboardContentClass = 'review-workbench-shell';
$dashboardBreadcrumbs = [
    ['label' => 'Notifications', 'url' => URLROOT . '/admin/notifications'],
    ['label' => 'Service review', 'url' => null],
];

$h = static function ($value) {
    return htmlspecialchars(htmlspecialchars_decode((string)$value, ENT_QUOTES), ENT_QUOTES, 'UTF-8');
};

$money = static function ($value) {
    return 'RM ' . number_format((float)$value, 0);
};

$dayNames = [
    1 => 'Monday',
    2 => 'Tuesday',
    3 => 'Wednesday',
    4 => 'Thursday',
    5 => 'Friday',
    6 => 'Saturday',
    7 => 'Sunday',
];

$dashboardContent = function () use (
    $service,
    $message,
    $readiness,
    $media,
    $rooms,
    $weekly,
    $isVenue,
    $isApproved,
    $isReady,
    $coverImage,
    $priceMin,
    $priceMax,
    $openDays,
    $h,
    $money,
    $dayNames
) {
?>
<style>
  .review-workbench-shell{min-height:100%;padding:30px;background:#fbfbf9}
  .review-workbench{--ink:#34232b;--body:#7b5c69;--muted:#a58b96;--line:#ead8c7;--paper:#fff;--wash:#faf5ef;--wine:#6d4c5b;--green:#4f7c69;--amber:#b7792f;max-width:1400px;margin:0 auto;color:var(--ink)}
  .rw-header{display:flex;align-items:flex-end;justify-content:space-between;gap:22px;margin-bottom:22px}
  .rw-kicker{margin:0 0 7px;color:#9b7d89;font-size:10px;font-weight:800;letter-spacing:.18em;text-transform:uppercase}
  .rw-title{max-width:780px;margin:0;color:var(--ink);font:650 clamp(29px,3vw,41px)/1.08 "Playfair Display",serif}
  .rw-byline{display:flex;flex-wrap:wrap;align-items:center;gap:8px;margin-top:10px;color:var(--body);font-size:12px;font-weight:600}
  .rw-byline-dot{width:4px;height:4px;border-radius:50%;background:#c6aa98}
  .rw-status{display:inline-flex;min-height:38px;align-items:center;gap:9px;border:1px solid var(--line);border-radius:999px;padding:0 14px;background:#fff;color:<?= $isApproved || $isReady ? '#4f7c69' : '#b7792f' ?>;font-size:11px;font-weight:800;white-space:nowrap;box-shadow:0 10px 28px rgba(52,35,43,.06)}
  .rw-status::before{content:"";width:8px;height:8px;border-radius:50%;background:currentColor;box-shadow:0 0 0 5px color-mix(in srgb,currentColor 11%,transparent)}
  .rw-layout{display:grid;grid-template-columns:minmax(0,1fr) 365px;gap:20px;align-items:start}
  .rw-main{display:grid;gap:18px;min-width:0}
  .rw-card{overflow:hidden;border:1px solid var(--line);border-radius:16px;background:var(--paper);box-shadow:0 18px 45px rgba(52,35,43,.055)}
  .rw-card-head{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:15px 18px;border-bottom:1px solid var(--line);background:#fff}
  .rw-card-title{display:flex;align-items:center;gap:9px;margin:0;color:var(--ink);font-size:12px;font-weight:800}
  .rw-card-title svg{width:15px;height:15px;color:#9b7d89}
  .rw-card-note{color:var(--muted);font-size:10px;font-weight:600}
  .rw-card-body{padding:18px}

  .rw-preview{padding:14px;background:#f1e5d8}
  .rw-listing{overflow:hidden;border-radius:13px;background:#fff;box-shadow:0 18px 42px rgba(52,35,43,.12)}
  .rw-cover-wrap{position:relative;overflow:hidden;aspect-ratio:16/7;background:linear-gradient(135deg,#e9d8ca,#f8f0e8)}
  .rw-cover{width:100%;height:100%;object-fit:cover;display:block;transition:transform .5s ease}
  .rw-listing:hover .rw-cover{transform:scale(1.018)}
  .rw-cover-empty{display:flex;width:100%;height:100%;align-items:center;justify-content:center;color:#9b7d89}
  .rw-cover-empty svg{width:38px;height:38px}
  .rw-preview-category{position:absolute;left:16px;top:16px;display:inline-flex;min-height:26px;align-items:center;border-radius:999px;padding:0 10px;background:rgba(255,255,255,.9);color:var(--wine);font-size:9px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;backdrop-filter:blur(8px)}
  .rw-listing-copy{padding:20px 22px 22px}
  .rw-listing-top{display:flex;align-items:flex-start;justify-content:space-between;gap:18px}
  .rw-listing-name{margin:0;color:var(--ink);font:650 clamp(22px,2.2vw,31px)/1.15 "Playfair Display",serif}
  .rw-listing-supplier{margin:6px 0 0;color:#9b7d89;font-size:11px;font-weight:700}
  .rw-price{flex:0 0 auto;text-align:right}
  .rw-price-label{display:block;color:#a58b96;font-size:8px;font-weight:800;letter-spacing:.12em;text-transform:uppercase}
  .rw-price-value{display:block;margin-top:4px;color:var(--wine);font-size:18px;font-weight:800}
  .rw-description{max-width:820px;margin:17px 0 0;color:var(--body);font-size:12px;line-height:1.75;white-space:pre-line}
  .rw-listing-meta{display:flex;flex-wrap:wrap;gap:8px;margin-top:18px;padding-top:16px;border-top:1px solid #f0e5dc}
  .rw-meta-pill{display:inline-flex;align-items:center;gap:7px;min-height:29px;border-radius:999px;padding:0 10px;background:#faf5ef;color:#7b5c69;font-size:9px;font-weight:700}
  .rw-meta-pill svg{width:12px;height:12px}

  .rw-gallery{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:9px}
  .rw-photo{position:relative;overflow:hidden;aspect-ratio:1;border:0;border-radius:11px;background:#f3e7dc;padding:0;cursor:zoom-in}
  .rw-photo img{width:100%;height:100%;object-fit:cover;display:block;transition:transform .25s ease}
  .rw-photo:hover img{transform:scale(1.05)}
  .rw-gallery-empty{grid-column:1/-1;display:flex;min-height:130px;align-items:center;justify-content:center;border:1px dashed #decbbb;border-radius:11px;background:#faf5ef;color:#9b7d89;font-size:11px;font-weight:700}

  .rw-schedule{width:100%;border-collapse:collapse}
  .rw-schedule th{padding:0 12px 10px;color:#a58b96;font-size:8px;font-weight:800;letter-spacing:.13em;text-align:left;text-transform:uppercase}
  .rw-schedule td{padding:12px;border-top:1px solid #f0e5dc;color:var(--body);font-size:11px}
  .rw-schedule td:first-child{color:var(--ink);font-weight:700}
  .rw-open,.rw-closed{display:inline-flex;min-height:23px;align-items:center;border-radius:999px;padding:0 8px;font-size:8px;font-weight:800;text-transform:uppercase}
  .rw-open{background:#edf6f1;color:#4f7c69}
  .rw-closed{background:#f5eeee;color:#9b6d72}
  .rw-empty-row{text-align:center!important;color:#a58b96!important;padding:28px!important}

  .rw-side{position:sticky;top:100px;display:grid;gap:14px}
  .rw-decision{border-color:#d9c2b3}
  .rw-decision-top{padding:21px 20px 17px;background:linear-gradient(145deg,#6d4c5b,#7b5c69);color:#fff}
  .rw-decision-kicker{margin:0;color:rgba(255,255,255,.64);font-size:9px;font-weight:800;letter-spacing:.15em;text-transform:uppercase}
  .rw-decision-title{margin:7px 0 0;font:650 24px/1.15 "Playfair Display",serif}
  .rw-decision-copy{margin:8px 0 0;color:rgba(255,255,255,.74);font-size:10.5px;line-height:1.55}
  .rw-checks{display:grid;gap:0;padding:6px 18px}
  .rw-check{display:grid;grid-template-columns:auto minmax(0,1fr) auto;gap:10px;align-items:center;padding:12px 0;border-bottom:1px solid #f0e5dc}
  .rw-check:last-child{border-bottom:0}
  .rw-check-icon{display:inline-flex;width:27px;height:27px;align-items:center;justify-content:center;border-radius:8px;background:#edf6f1;color:#4f7c69}
  .rw-check-icon.warn{background:#fff5e6;color:#b7792f}
  .rw-check-icon svg{width:13px;height:13px}
  .rw-check-label{color:var(--ink);font-size:10.5px;font-weight:700}
  .rw-check-value{max-width:125px;overflow:hidden;color:#a58b96;font-size:9px;font-weight:700;text-align:right;text-overflow:ellipsis;white-space:nowrap}
  .rw-missing{display:grid;gap:7px;margin:0;padding:0;list-style:none}
  .rw-missing-wrap{padding:15px 18px;border-top:1px solid var(--line);background:#fffaf2}
  .rw-missing-title{display:flex;align-items:center;gap:7px;margin:0 0 9px;color:#9a6527;font-size:9px;font-weight:800;letter-spacing:.1em;text-transform:uppercase}
  .rw-missing li{display:flex;gap:8px;color:#8a6944;font-size:10px;font-weight:600;line-height:1.45}
  .rw-missing li::before{content:"•";color:#b7792f}
  .rw-decision-actions{display:grid;gap:8px;padding:17px 18px;border-top:1px solid var(--line);background:#faf5ef}
  .rw-btn{display:inline-flex;min-height:42px;box-sizing:border-box;align-items:center;justify-content:center;gap:8px;border-radius:10px;padding:0 14px;font:800 11px Poppins,sans-serif;text-decoration:none;cursor:pointer}
  .rw-btn-primary{border:1px solid var(--wine);background:var(--wine);color:#fff;box-shadow:0 10px 22px rgba(109,76,91,.17)}
  .rw-btn-secondary{border:1px solid #e0ccbd;background:#fff;color:var(--wine)}
  .rw-btn:disabled{box-shadow:none;cursor:not-allowed;opacity:.45}
  .rw-btn:focus-visible,.rw-photo:focus-visible{outline:3px solid rgba(109,76,91,.2);outline-offset:2px}
  .rw-supplier{display:grid;gap:12px;padding:17px 18px}
  .rw-fact{display:flex;justify-content:space-between;gap:14px;padding-bottom:10px;border-bottom:1px solid #f0e5dc}
  .rw-fact:last-child{padding-bottom:0;border-bottom:0}
  .rw-fact-label{color:#a58b96;font-size:8px;font-weight:800;letter-spacing:.1em;text-transform:uppercase}
  .rw-fact-value{max-width:215px;color:var(--ink);font-size:10px;font-weight:700;text-align:right;overflow-wrap:anywhere}

  .rw-toast-stack{position:fixed;right:24px;bottom:24px;z-index:90;display:grid;gap:10px;width:min(410px,calc(100vw - 32px))}
  .rw-toast{display:flex;align-items:flex-start;gap:11px;border:1px solid var(--line);border-radius:13px;padding:14px;background:#fff;box-shadow:0 22px 60px rgba(44,31,40,.17)}
  .rw-toast.success{border-color:#bcd8c8;background:#f4faf6}
  .rw-toast-icon{display:inline-flex;width:31px;height:31px;flex:0 0 31px;align-items:center;justify-content:center;border-radius:9px;background:#edf6f1;color:#4f7c69}
  .rw-toast strong{display:block;color:var(--ink);font-size:11px}
  .rw-toast p{margin:3px 0 0;color:var(--body);font-size:10px;line-height:1.5}
  .rw-toast-close{margin-left:auto;border:0;background:transparent;color:#9b7d89;cursor:pointer}

  .rw-lightbox{position:fixed;inset:0;z-index:100;display:none;align-items:center;justify-content:center;background:rgba(35,23,29,.82);padding:28px;backdrop-filter:blur(5px)}
  .rw-lightbox.open{display:flex}
  .rw-lightbox img{max-width:min(1100px,94vw);max-height:88vh;border-radius:14px;object-fit:contain;box-shadow:0 28px 90px rgba(0,0,0,.35)}
  .rw-lightbox-close{position:absolute;right:24px;top:22px;display:inline-flex;width:42px;height:42px;align-items:center;justify-content:center;border:1px solid rgba(255,255,255,.2);border-radius:12px;background:rgba(255,255,255,.1);color:#fff;cursor:pointer}

  @media(max-width:1080px){.rw-layout{grid-template-columns:1fr}.rw-side{position:static}.rw-gallery{grid-template-columns:repeat(3,minmax(0,1fr))}}
  @media(max-width:680px){.review-workbench-shell{padding:20px}.rw-header{align-items:flex-start;flex-direction:column}.rw-status{white-space:normal}.rw-listing-top{flex-direction:column}.rw-price{text-align:left}.rw-gallery{grid-template-columns:repeat(2,minmax(0,1fr))}.rw-card-body{padding:14px}.rw-preview{padding:9px}.rw-listing-copy{padding:17px}.rw-schedule{min-width:620px}.rw-table-wrap{overflow-x:auto}}
  @media(prefers-reduced-motion:reduce){.rw-cover,.rw-photo img{transition:none}}
</style>

<div class="review-workbench">
  <header class="rw-header">
    <div>
      <p class="rw-kicker">Service publish request</p>
      <h1 class="rw-title"><?= $h($service['name'] ?? 'Service review') ?></h1>
      <div class="rw-byline">
        <span><?= $h($service['supplier_name'] ?? 'Supplier') ?></span>
        <span class="rw-byline-dot"></span>
        <span><?= $h($service['category'] ?? 'Service') ?></span>
        <span class="rw-byline-dot"></span>
        <span>Service #<?= (int)($service['id'] ?? 0) ?></span>
      </div>
    </div>
    <span class="rw-status"><?= $isApproved ? 'Published and live' : ($isReady ? 'Ready to publish' : 'Needs attention') ?></span>
  </header>

  <?php if ($isApproved || $message !== ''): ?>
    <div class="rw-toast-stack" role="status" aria-live="polite">
      <?php if ($isApproved): ?>
        <div class="rw-toast success">
          <span class="rw-toast-icon"><i data-lucide="circle-check" class="h-4 w-4"></i></span>
          <div><strong>Service is live</strong><p>This listing is approved and visible to customers.</p></div>
          <button type="button" class="rw-toast-close" aria-label="Dismiss"><i data-lucide="x" class="h-4 w-4"></i></button>
        </div>
      <?php endif; ?>
      <?php if ($message !== ''): ?>
        <div class="rw-toast">
          <span class="rw-toast-icon"><i data-lucide="info" class="h-4 w-4"></i></span>
          <div><strong>Review update</strong><p><?= $h($message) ?></p></div>
          <button type="button" class="rw-toast-close" aria-label="Dismiss"><i data-lucide="x" class="h-4 w-4"></i></button>
        </div>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <div class="rw-layout">
    <main class="rw-main">
      <section class="rw-card">
        <div class="rw-card-head">
          <h2 class="rw-card-title"><i data-lucide="monitor-check"></i> Customer listing preview</h2>
          <span class="rw-card-note">This is what customers evaluate first</span>
        </div>
        <div class="rw-preview">
          <article class="rw-listing">
            <div class="rw-cover-wrap">
              <?php if ($coverImage !== ''): ?>
                <img class="rw-cover" src="<?= $h($coverImage) ?>" alt="<?= $h($service['name'] ?? 'Service') ?>">
              <?php else: ?>
                <div class="rw-cover-empty"><i data-lucide="image-off"></i></div>
              <?php endif; ?>
              <span class="rw-preview-category"><?= $h($service['category'] ?? 'Service') ?></span>
            </div>
            <div class="rw-listing-copy">
              <div class="rw-listing-top">
                <div>
                  <h2 class="rw-listing-name"><?= $h($service['name'] ?? 'Unnamed service') ?></h2>
                  <p class="rw-listing-supplier">By <?= $h($service['supplier_name'] ?? 'Supplier') ?></p>
                </div>
                <div class="rw-price">
                  <span class="rw-price-label"><?= $priceMax > $priceMin ? 'Price range' : 'Starting from' ?></span>
                  <strong class="rw-price-value"><?= $money($priceMin) ?><?= $priceMax > $priceMin ? ' – ' . $money($priceMax) : '' ?></strong>
                </div>
              </div>
              <p class="rw-description"><?= $h(trim((string)($service['desc'] ?? '')) !== '' ? $service['desc'] : 'No service description has been provided.') ?></p>
              <div class="rw-listing-meta">
                <span class="rw-meta-pill"><i data-lucide="clock-3"></i><?= (int)($service['duration_minutes'] ?? 0) ?> min service</span>
                <span class="rw-meta-pill"><i data-lucide="calendar-clock"></i><?= (int)($service['min_lead_days'] ?? 0) ?> day lead time</span>
                <?php if ($isVenue): ?>
                  <span class="rw-meta-pill"><i data-lucide="building-2"></i><?= count($rooms) ?> <?= count($rooms) === 1 ? 'hall' : 'halls' ?></span>
                <?php else: ?>
                  <span class="rw-meta-pill"><i data-lucide="calendar-days"></i><?= $openDays ?> open <?= $openDays === 1 ? 'day' : 'days' ?> weekly</span>
                <?php endif; ?>
              </div>
            </div>
          </article>
        </div>
      </section>

      <section class="rw-card">
        <div class="rw-card-head">
          <h2 class="rw-card-title"><i data-lucide="images"></i> Portfolio photos</h2>
          <span class="rw-card-note"><?= count($media) ?> uploaded</span>
        </div>
        <div class="rw-card-body">
          <div class="rw-gallery">
            <?php if (empty($media)): ?>
              <div class="rw-gallery-empty">No portfolio photos uploaded.</div>
            <?php endif; ?>
            <?php foreach ($media as $index => $item): ?>
              <?php $photoUrl = (string)($item['file_url'] ?? ''); ?>
              <?php if ($photoUrl !== ''): ?>
                <button class="rw-photo" type="button" data-lightbox-image="<?= $h($photoUrl) ?>" aria-label="View portfolio photo <?= $index + 1 ?>">
                  <img src="<?= $h($photoUrl) ?>" alt="Portfolio photo <?= $index + 1 ?>">
                </button>
              <?php endif; ?>
            <?php endforeach; ?>
          </div>
        </div>
      </section>

      <section class="rw-card">
        <div class="rw-card-head">
          <h2 class="rw-card-title"><i data-lucide="<?= $isVenue ? 'building-2' : 'calendar-range' ?>"></i><?= $isVenue ? 'Venue halls' : 'Weekly availability' ?></h2>
          <span class="rw-card-note"><?= $isVenue ? count($rooms) . ' configured' : $openDays . ' days open' ?></span>
        </div>
        <div class="rw-card-body rw-table-wrap">
          <table class="rw-schedule">
            <thead>
              <tr>
                <?php if ($isVenue): ?>
                  <th>Hall</th><th>Capacity</th><th>Price</th><th>Hours</th>
                <?php else: ?>
                  <th>Day</th><th>Status</th><th>Operating hours</th>
                <?php endif; ?>
              </tr>
            </thead>
            <tbody>
              <?php if ($isVenue && empty($rooms)): ?>
                <tr><td class="rw-empty-row" colspan="4">No halls have been configured.</td></tr>
              <?php elseif (!$isVenue && empty($weekly)): ?>
                <tr><td class="rw-empty-row" colspan="3">No weekly schedule has been configured.</td></tr>
              <?php elseif ($isVenue): ?>
                <?php foreach ($rooms as $room): ?>
                  <tr>
                    <td><?= $h($room['name'] ?? 'Unnamed hall') ?></td>
                    <td><?= number_format((int)($room['capacity'] ?? 0)) ?> guests</td>
                    <td><?= $money($room['price_min'] ?? $room['price'] ?? 0) ?></td>
                    <td><?= $h(substr((string)($room['start_time'] ?? ''),0,5)) ?> – <?= $h(substr((string)($room['end_time'] ?? ''),0,5)) ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <?php foreach ($weekly as $row): ?>
                  <?php $dayNumber = (int)($row['day_of_week'] ?? 0); $open = !empty($row['is_available']); ?>
                  <tr>
                    <td><?= $h($dayNames[$dayNumber] ?? ('Day ' . $dayNumber)) ?></td>
                    <td><span class="<?= $open ? 'rw-open' : 'rw-closed' ?>"><?= $open ? 'Open' : 'Closed' ?></span></td>
                    <td><?= $open ? $h(substr((string)($row['open_time'] ?? ''),0,5)) . ' – ' . $h(substr((string)($row['close_time'] ?? ''),0,5)) : 'Not available' ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </section>
    </main>

    <aside class="rw-side">
      <section class="rw-card rw-decision">
        <div class="rw-decision-top">
          <p class="rw-decision-kicker">Review summary</p>
          <h2 class="rw-decision-title"><?= $isApproved ? 'Already published' : ($isReady ? 'All checks passed' : 'Not ready yet') ?></h2>
          <p class="rw-decision-copy"><?= $isApproved ? 'Customers can currently discover and book this service.' : ($isReady ? 'The required listing information is complete and ready for your decision.' : 'The supplier needs to complete the missing setup before this service can go live.') ?></p>
        </div>

        <div class="rw-checks">
          <?php
          $checks = [
            ['Service information', trim((string)($service['name'] ?? '')) !== '' && trim((string)($service['desc'] ?? '')) !== '', trim((string)($service['desc'] ?? '')) !== '' ? 'Complete' : 'Missing'],
            ['Pricing', $priceMin > 0 || in_array(strtolower((string)($service['category'] ?? '')), ['decoration','attire'], true), $priceMin > 0 ? $money($priceMin) : 'Category pricing'],
            ['Portfolio', !empty($media), count($media) . ' photos'],
            [$isVenue ? 'Venue halls' : 'Availability', $isVenue ? !empty($rooms) : $openDays > 0, $isVenue ? count($rooms) . (count($rooms) === 1 ? ' hall' : ' halls') : $openDays . ( $openDays === 1 ? ' open day' : ' open days')],
            ['Supplier payment', strtolower((string)($service['supplier_payment_status'] ?? '')) === 'paid', ucfirst((string)($service['supplier_payment_status'] ?? 'Not recorded'))],
          ];
          foreach ($checks as [$label,$passed,$value]):
          ?>
            <div class="rw-check">
              <span class="rw-check-icon <?= $passed ? '' : 'warn' ?>"><i data-lucide="<?= $passed ? 'check' : 'alert-circle' ?>"></i></span>
              <span class="rw-check-label"><?= $h($label) ?></span>
              <span class="rw-check-value"><?= $h($value) ?></span>
            </div>
          <?php endforeach; ?>
        </div>

        <?php if (!$isReady && !empty($readiness['missing'])): ?>
          <div class="rw-missing-wrap">
            <h3 class="rw-missing-title"><i data-lucide="triangle-alert" class="h-3.5 w-3.5"></i> Required before publishing</h3>
            <ul class="rw-missing">
              <?php foreach ($readiness['missing'] as $missing): ?><li><?= $h($missing) ?></li><?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <div class="rw-decision-actions">
          <form method="post" action="<?= URLROOT ?>/admin/approveService/<?= (int)($service['id'] ?? 0) ?>">
            <button class="rw-btn rw-btn-primary" style="width:100%" type="submit" <?= ($isApproved || !$isReady) ? 'disabled' : '' ?>>
              <i data-lucide="<?= $isApproved ? 'badge-check' : 'send' ?>" class="h-4 w-4"></i>
              <?= $isApproved ? 'Already published' : 'Approve and publish' ?>
            </button>
          </form>
          <a class="rw-btn rw-btn-secondary" href="<?= URLROOT ?>/admin/notifications"><i data-lucide="arrow-left" class="h-4 w-4"></i>Back to notifications</a>
        </div>
      </section>

      <section class="rw-card">
        <div class="rw-card-head"><h2 class="rw-card-title"><i data-lucide="store"></i> Supplier details</h2></div>
        <div class="rw-supplier">
          <div class="rw-fact"><span class="rw-fact-label">Business</span><span class="rw-fact-value"><?= $h($service['supplier_name'] ?? '—') ?></span></div>
          <div class="rw-fact"><span class="rw-fact-label">Owner</span><span class="rw-fact-value"><?= $h($service['owner_name'] ?? '—') ?></span></div>
          <div class="rw-fact"><span class="rw-fact-label">Email</span><span class="rw-fact-value"><?= $h($service['owner_email'] ?? '—') ?></span></div>
          <div class="rw-fact"><span class="rw-fact-label">Phone</span><span class="rw-fact-value"><?= $h($service['supplier_phone'] ?? '—') ?></span></div>
          <div class="rw-fact"><span class="rw-fact-label">Supplier status</span><span class="rw-fact-value"><?= $h(ucfirst((string)($service['supplier_status'] ?? '—'))) ?></span></div>
        </div>
      </section>
    </aside>
  </div>
</div>

<div class="rw-lightbox" id="review-lightbox" aria-hidden="true">
  <button class="rw-lightbox-close" type="button" aria-label="Close image preview"><i data-lucide="x"></i></button>
  <img src="" alt="Expanded portfolio photo">
</div>

<script>
(() => {
  document.querySelectorAll('.rw-toast-close').forEach(button => {
    button.addEventListener('click', () => button.closest('.rw-toast')?.remove());
  });

  const lightbox = document.getElementById('review-lightbox');
  const lightboxImage = lightbox?.querySelector('img');
  let lastPhoto = null;
  const closeLightbox = () => {
    lightbox?.classList.remove('open');
    lightbox?.setAttribute('aria-hidden', 'true');
    if (lightboxImage) lightboxImage.src = '';
    lastPhoto?.focus();
  };

  document.querySelectorAll('[data-lightbox-image]').forEach(button => {
    button.addEventListener('click', () => {
      lastPhoto = button;
      if (lightboxImage) lightboxImage.src = button.dataset.lightboxImage;
      lightbox?.classList.add('open');
      lightbox?.setAttribute('aria-hidden', 'false');
      lightbox?.querySelector('button')?.focus();
    });
  });
  lightbox?.querySelector('button')?.addEventListener('click', closeLightbox);
  lightbox?.addEventListener('click', event => {
    if (event.target === lightbox) closeLightbox();
  });
  document.addEventListener('keydown', event => {
    if (event.key === 'Escape' && lightbox?.classList.contains('open')) closeLightbox();
  });
})();
</script>
<?php
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php require_once APPROOT . '/views/dashboardLayout/head.php'; ?>
</head>
<body class="grid h-screen gap-0 bg-app-page" style="grid-template-columns:280px 1fr">
  <?php require APPROOT . '/views/dashboardLayout/adminsidebar.php'; ?>
</body>
</html>
