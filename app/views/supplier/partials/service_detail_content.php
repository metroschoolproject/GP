<div id="supplier-service-detail">

<?php
// Derived rental pricing variables (used in stats bar and rental card below)
$rentBorrowPackagePrice = (float)($rentalPricing['borrow_package_price'] ?? $rentalPricing['borrow_price'] ?? 0);
$rentBorrowCustomizePrice = (float)($rentalPricing['borrow_customize_price'] ?? $rentalPricing['borrow_price'] ?? $rentBorrowPackagePrice);
$rentReturnDays = (int)($rentalPricing['return_days'] ?? 0);
$rentBuyPackagePrice = (float)($rentalPricing['buy_package_price'] ?? $rentalPricing['buy_price'] ?? 0);
$rentBuyCustomizePrice = (float)($rentalPricing['buy_customize_price'] ?? $rentalPricing['buy_price'] ?? $rentBuyPackagePrice);
$hasRentalPricing = $rentBorrowPackagePrice > 0 || $rentBorrowCustomizePrice > 0 || $rentBuyPackagePrice > 0 || $rentBuyCustomizePrice > 0;
$decorationStyles = is_array($service['decoration_styles'] ?? null) ? $service['decoration_styles'] : [];
?>

  <!-- ═══════════════ HERO ═══════════════ -->
  <div class="sd-hero sd-anim-hero">

    <!-- Floating topnav -->
    <div class="sd-topnav" data-service-status="<?= $h($serviceStatus) ?>">
      <div class="sd-topnav-left">
        <a href="<?= URLROOT ?>/supplier/services" class="sd-back-link"><i class="ti ti-arrow-left" style="font-size:13px"></i> Back</a>
        <span class="sd-badge">ID #<?= (int)$serviceId ?></span>
        <span class="sd-status-pill">
          <span class="sd-status-dot <?= $serviceStatus === 'active' || $isReady ? 'is-live' : '' ?>" id="publishStatusDot"></span>
          <span id="publishStatusText"><?= $serviceStatus === 'active' ? 'Live' : ($isReady ? 'Ready' : 'Needs attention') ?></span>
        </span>
      </div>
      <button type="button" id="publishServiceBtn" class="btn btn-primary btn-sm" <?= $serviceStatus === 'active' ? 'disabled' : '' ?>>
        <i class="ti <?= $serviceStatus === 'active' ? 'ti-circle-check' : 'ti-send' ?>" style="font-size:13px"></i>
        <span id="publishServiceBtnText"><?= $serviceStatus === 'active' ? 'Published' : 'Request publish' ?></span>
      </button>
    </div>

    <!-- Hero image -->
    <div class="sd-hero-img">
      <?php if ($serviceImage !== ''): ?>
        <img src="<?= $h($serviceImage) ?>" alt="<?= $h($serviceNameRaw) ?>">
      <?php else: ?>
        <div class="sd-hero-img-placeholder">
          <div style="display:flex;flex-direction:column;align-items:center;gap:8px;color:var(--text-3)">
            <div style="width:60px;height:60px;border-radius:14px;border:1.5px dashed var(--border-strong);display:flex;align-items:center;justify-content:center">
              <i class="ti ti-photo" style="font-size:26px"></i>
            </div>
            <span style="font-size:12px;font-weight:600">No cover photo</span>
          </div>
        </div>
      <?php endif; ?>
      <div class="sd-hero-scrim"></div>
    </div>

    <!-- Hero body overlaid -->
    <div class="sd-hero-body">
      <div class="sd-hero-category"><i class="ti ti-sparkles" style="font-size:11px"></i> <?= $h($serviceCategoryRaw) ?></div>
      <h1 class="sd-hero-title"><?= $h($serviceNameRaw) ?></h1>
      <p class="sd-hero-desc"><?= $serviceDescriptionRaw !== '' ? $h($serviceDescriptionRaw) : 'No description has been added yet.' ?></p>
    </div>

    <!-- Stats bar -->
    <div class="sd-stats">
      <div class="sd-stat sd-stat-price">
        <div class="sd-stat-icon"><i class="ti ti-tag"></i></div>
        <div class="sd-stat-body">
          <div class="sd-stat-label">Pricing</div>
          <div class="sd-stat-price-stack">
            <?php if ($isRental): ?>
            <div class="sd-stat-price-line">
              <span class="sd-stat-price-key">Borrow</span>
              <span class="sd-stat-value"><?= $rentBorrowPackagePrice > 0 ? $money($rentBorrowPackagePrice) : '—' ?></span>
            </div>
            <div class="sd-stat-price-line">
              <span class="sd-stat-price-key">Buy</span>
              <span class="sd-stat-price-subvalue"><?= $rentBuyPackagePrice > 0 ? $money($rentBuyPackagePrice) : '—' ?></span>
            </div>
            <?php else: ?>
            <div class="sd-stat-price-line">
              <span class="sd-stat-price-key">Package</span>
              <span class="sd-stat-value"><?= $money($servicePackagePrice) ?></span>
            </div>
            <div class="sd-stat-price-line">
              <span class="sd-stat-price-key">Customize</span>
              <span class="sd-stat-price-subvalue"><?= $money($serviceCustomizePrice) ?></span>
            </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <div class="sd-stat">
        <div class="sd-stat-icon gold"><i class="ti ti-calendar"></i></div>
        <div class="sd-stat-body">
          <div class="sd-stat-label">Days open</div>
          <div id="heroOpenDaysValue" class="sd-stat-value"><?= (int)$openDaysCount ?><span style="font-size:14px;color:var(--text-3)">/7</span></div>
          <div class="sd-stat-sub" id="heroOpenDaysSub"><?= $openDaysCount > 0 ? 'Schedule active' : 'No days open' ?></div>
        </div>
      </div>
      <div class="sd-stat">
        <div class="sd-stat-icon green"><i class="ti ti-photo"></i></div>
        <div class="sd-stat-body">
          <div class="sd-stat-label">Portfolio</div>
          <div class="sd-stat-value"><?= (int)$mediaCount ?></div>
          <div class="sd-stat-sub"><?= $mediaCount === 1 ? 'photo uploaded' : 'photos uploaded' ?></div>
        </div>
      </div>
    </div>
  </div>

  <!-- ═══════════════ PUBLISH MESSAGE ═══════════════ -->
  <div id="publishMessage" class="sd-message sd-publish-toast error" style="display:none" role="status" aria-live="polite"></div>

  <!-- ═══════════════ ATTENTION BANNER ═══════════════ -->
  <div class="sd-attention <?= $isReady ? 'is-ready' : '' ?> sd-anim-card-1">
    <div class="sd-attention-icon"><i class="ti <?= $isReady ? 'ti-circle-check' : 'ti-alert-triangle' ?>"></i></div>
    <div style="flex:1">
      <div style="display:flex;align-items:center;justify-content:space-between;gap:8px">
        <div class="sd-attention-title">
          <?= $isReady ? 'This service is ready for customers' : count($attentionItems) . ' ' . (count($attentionItems) === 1 ? 'thing' : 'things') . ' to complete before customers can book' ?>
        </div>
        <span class="sd-badge" style="background:#fff;color:<?= $isReady ? 'var(--success)' : 'var(--warning)' ?>"><?= $isReady ? 'Ready' : count($attentionItems) . ' items' ?></span>
      </div>
      <?php if (!$isReady): ?>
        <div class="sd-attention-items">
          <?php foreach ($attentionItems as $item): ?>
            <div class="sd-attention-item">
              <i class="<?= $h($item['icon']) ?>"></i>
              <div>
                <div class="sd-attention-item-label"><?= $h($item['label']) ?></div>
                <div class="sd-attention-item-detail"><?= $h($item['detail']) ?></div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="sd-attention-sub">Pricing, media, and weekly availability are set.</div>
      <?php endif; ?>
    </div>
  </div>

  <!-- ═══════════════ WORKSPACE ═══════════════ -->
  <div class="sd-workspace">

    <!-- ═══ MAIN COLUMN ═══ -->
    <div class="sd-main">

      <!-- === PORTFOLIO === -->
      <div class="sd-card sd-anim-card-2">
        <div class="sd-card-head">
          <div>
            <div class="sd-card-title">Portfolio photos</div>
            <div class="sd-card-sub">Shown to customers browsing your listing</div>
          </div>
          <label class="btn btn-primary btn-sm" style="cursor:pointer">
            <i class="ti ti-photo-plus" style="font-size:13px"></i> Add photo
            <input id="serviceMediaInput" type="file" accept="image/*" style="display:none">
          </label>
        </div>
        <div class="sd-card-body">
          <div id="mediaMessage" class="sd-message error" style="display:none"></div>
          <div id="mediaGrid" class="sd-gallery">
            <?php foreach ($media as $item): ?>
              <div class="sd-gallery-item" data-media-id="<?= (int)($item['id'] ?? 0) ?>">
                <img src="<?= $h($item['file_url'] ?? '') ?>" alt="Service photo">
                <button type="button" class="sd-gallery-del" onclick="deleteServiceMedia(<?= (int)($item['id'] ?? 0) ?>)"><i class="ti ti-trash" style="font-size:13px"></i></button>
              </div>
            <?php endforeach; ?>
            <label class="sd-gallery-add">
              <i class="ti ti-plus"></i>
              <span>Upload photo</span>
              <input type="file" accept="image/*" data-media-picker style="display:none">
            </label>
          </div>
        </div>
      </div>

      <?php if (strtolower((string)$serviceCategoryRaw) === 'decoration'): ?>
        <div class="sd-card sd-anim-card-3">
          <div class="sd-card-head">
            <div>
              <div class="sd-card-title">Decoration styles</div>
              <div class="sd-card-sub">Style options customers can choose from</div>
            </div>
            <div class="sd-head-actions">
              <span id="decorationStyleCount" class="sd-badge"><?= count($decorationStyles) ?> <?= count($decorationStyles) === 1 ? 'style' : 'styles' ?></span>
              <button type="button" class="btn btn-primary btn-sm" id="addDecorationStyleBtn"><i class="ti ti-plus" style="font-size:13px"></i> Add style</button>
            </div>
          </div>
          <div class="sd-card-body">
            <div id="decorationStyleMessage" class="sd-message error" style="display:none"></div>
            <div id="decorationStyleGrid" class="sd-decoration-styles"></div>
          </div>
          <div class="sd-card-foot">
            <button type="button" class="btn btn-primary btn-sm" id="saveDecorationStylesBtn"><i class="ti ti-check" style="font-size:12px"></i> Save styles</button>
          </div>
        </div>
      <?php endif; ?>

      <!-- === ROOMS / HALLS (Venue only) === -->
      <?php if ($isVenue): ?>
        <div class="sd-card sd-anim-card-3">
          <div class="sd-card-head">
            <div>
              <div class="sd-card-title">Rooms &amp; Halls</div>
              <div class="sd-card-sub">Capacity, pricing, and bookable hours</div>
            </div>
            <span id="hallCount" class="sd-badge"><?= count($venueRooms) ?> <?= count($venueRooms) === 1 ? 'hall' : 'halls' ?></span>
          </div>
          <div class="sd-card-body">
            <div id="hallMessage" class="sd-message error" style="display:none"></div>
            <div id="hallGrid" class="sd-halls">
              <?php foreach ($venueRooms as $room): ?>
                <div class="sd-hall-card" data-room-id="<?= (int)($room['id'] ?? 0) ?>">
                  <input type="hidden" class="hall-id" value="<?= (int)($room['id'] ?? 0) ?>">
                  <input type="hidden" class="hall-photo-url" value="<?= $h($room['photo_url'] ?? '') ?>">
                  <div class="sd-hall-head">
                    <div class="sd-hall-head-left">
                      <div class="sd-hall-icon"><i class="ti ti-door"></i></div>
                    </div>
                    <button type="button" class="btn btn-icon btn-danger-ghost btn-sm" onclick="removeHall(this)"><i class="ti ti-trash" style="font-size:13px"></i></button>
                  </div>
                  <div class="sd-hall-photo">
                    <div class="sd-hall-photo-preview <?= empty($room['photo_url']) ? 'is-empty' : '' ?>">
                      <?php if (!empty($room['photo_url'])): ?>
                        <img src="<?= $h($room['photo_url']) ?>" alt="<?= $h(($room['name'] ?? 'Hall') . ' photo') ?>">
                      <?php else: ?>
                        <i class="ti ti-photo"></i>
                        <span>Hall photo</span>
                      <?php endif; ?>
                    </div>
                    <label class="sd-hall-photo-btn">
                      <i class="ti ti-upload"></i>
                      <span><?= empty($room['photo_url']) ? 'Add photo' : 'Change photo' ?></span>
                      <input type="file" accept="image/*" class="hall-photo-input" style="display:none">
                    </label>
                  </div>
                  <div class="sd-hall-fields">
                    <div class="sd-hall-fg full"><label>Hall name</label><input class="sd-hall-input hall-name" value="<?= $h($room['name'] ?? '') ?>"></div>
                    <div class="sd-hall-fg"><label>Capacity</label><input type="number" min="1" class="sd-hall-input hall-capacity" value="<?= (int)($room['capacity'] ?? 1) ?>"></div>
                    <div class="sd-hall-fg"><label>Package price</label><input type="number" min="0" step="0.01" class="sd-hall-input hall-price hall-price-min" value="<?= $h($room['price_min'] ?? $room['price'] ?? 0) ?>"></div>
                    <div class="sd-hall-fg"><label>Customize price</label><input type="number" min="0" step="0.01" class="sd-hall-input hall-price-max" value="<?= $h($room['price_max'] ?? $room['price_min'] ?? $room['price'] ?? 0) ?>"></div>
                    <div class="sd-hall-fg"><label>Start time</label><input type="time" lang="en-GB" class="sd-hall-input hall-start" value="<?= $h(substr((string)($room['start_time'] ?? '09:00'), 0, 5)) ?>"></div>
                    <div class="sd-hall-fg"><label>End time</label><input type="time" lang="en-GB" class="sd-hall-input hall-end" value="<?= $h(substr((string)($room['end_time'] ?? '17:00'), 0, 5)) ?>"></div>
                    <div class="sd-hall-fg"><label>Min. notice (days)</label><input type="number" min="0" max="365" class="sd-hall-input hall-min-lead-days" value="<?= array_key_exists('min_lead_days', $room) && $room['min_lead_days'] !== null ? (int)$room['min_lead_days'] : '' ?>" placeholder="Use service default"></div>
                  </div>
                  <div class="sd-hall-time"><?= $h($formatTime($room['start_time'] ?? '09:00') . ' - ' . $formatTime($room['end_time'] ?? '17:00')) ?></div>
                </div>
              <?php endforeach; ?>
            </div>
            <button type="button" class="sd-add-hall" onclick="addHall()"><i class="ti ti-plus"></i> Add hall</button>
          </div>
          <div class="sd-card-foot">
            <button type="button" class="btn btn-primary btn-sm" id="saveHallsBtn"><i class="ti ti-check" style="font-size:12px"></i> Save halls</button>
          </div>
        </div>
      <?php endif; ?>

      <!-- === WEEKLY AVAILABILITY === -->
      <div class="sd-card sd-anim-card-4">
        <div class="sd-card-head">
          <div>
            <div class="sd-card-title">Weekly availability</div>
            <div class="sd-card-sub">Hours and slot settings for each day</div>
          </div>
          <span id="openDaysBadge" class="sd-badge"><?= (int)$openDaysCount ?> days open</span>
        </div>
        <div class="sd-card-body">
          <div id="availabilityMessage" class="sd-message error" style="display:none"></div>

          <!-- Controls row -->
          <div class="sd-avail-controls">
            <div class="sd-avail-field">
              <label>Slot duration</label>
              <select id="availabilityDuration">
                <?php
                $durationOptions = [15, 30, 45, 60, 90, 120, 150, 180, 210, 240, 480, 720];
                if (!in_array($slotDuration, $durationOptions, true)) {
                    $durationOptions[] = $slotDuration;
                    sort($durationOptions);
                }
                foreach ($durationOptions as $minutes):
                ?>
                  <option value="<?= (int)$minutes ?>" <?= (int)$minutes === $slotDuration ? 'selected' : '' ?>><?= $h($durationLabel($minutes)) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="sd-avail-field">
              <label>Buffer between slots</label>
              <select id="availabilityBuffer">
                <?php
                $bufferOptions = [0, 5, 10, 15, 30, 45, 60];
                if (!in_array($bufferMinutes, $bufferOptions, true)) {
                    $bufferOptions[] = $bufferMinutes;
                    sort($bufferOptions);
                }
                foreach ($bufferOptions as $minutes):
                ?>
                  <option value="<?= (int)$minutes ?>" <?= (int)$minutes === $bufferMinutes ? 'selected' : '' ?>><?= (int)$minutes === 0 ? 'No buffer' : $h($durationLabel($minutes)) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <?php if (!$isVenue): ?>
            <div class="sd-avail-field">
              <label>Max concurrent</label>
              <input id="availabilityConcurrent" type="number" min="1" value="<?= (int)$maxConcurrent ?>">
            </div>
            <?php endif; ?>
            <div class="sd-avail-field">
              <label>Minimum notice</label>
              <input id="availabilityMinLeadDays" type="number" min="0" max="365" value="<?= (int)($service['min_lead_days'] ?? 0) ?>">
            </div>
          </div>

          <!-- Day cards -->
          <div class="sd-days">
            <?php
            $dayAbbr = [1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat', 7 => 'Sun'];
            foreach ($days as $dayNumber => $dayName):
              $row = $weeklyByDay[$dayNumber] ?? [];
              $open = $isDayAvailable($dayNumber, $row);
              $start = substr((string)($row['open_time'] ?? '09:00'), 0, 5);
              $end = substr((string)($row['close_time'] ?? '17:00'), 0, 5);
            ?>
              <div class="sd-day-card <?= $open ? '' : 'is-closed' ?> availability-day-row" data-day="<?= (int)$dayNumber ?>">
                <div class="sd-day-name"><?= $h($dayAbbr[$dayNumber] ?? $dayName) ?></div>
                <div class="sd-day-toggle">
                  <label class="sd-toggle">
                    <input type="checkbox" class="availability-open" <?= $open ? 'checked' : '' ?> onchange="toggleDay(this)">
                    <div class="sd-toggle-track"></div>
                    <div class="sd-toggle-thumb"></div>
                  </label>
                </div>
                <div class="sd-day-time">
                  <?php if ($open): ?>
                    <input class="time-input availability-start" type="time" value="<?= $h($start) ?>">
                    <span style="display:block;font-size:9px;color:var(--text-3);line-height:1">to</span>
                    <input class="time-input availability-end" type="time" value="<?= $h($end) ?>">
                  <?php else: ?>
                    <span class="sd-day-closed">Closed</span>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="sd-card-foot">
          <button type="button" class="btn btn-outline btn-sm" onclick="window.location.reload()">Discard</button>
          <button type="button" id="saveAvailabilityBtn" class="btn btn-primary btn-sm"><i class="ti ti-check" style="font-size:12px"></i> Save schedule</button>
        </div>
      </div>

    </div>

    <!-- ═══ SIDE COLUMN ═══ -->
    <div class="sd-side">

      <!-- === SPECIAL DATES === -->
      <div class="sd-card sd-anim-card-3">
        <div class="sd-card-head">
          <div>
            <div class="sd-card-title">Special dates</div>
            <div class="sd-card-sub">Closures, holidays, custom hours</div>
          </div>
          <span id="overrideCount" class="sd-badge"><?= (int)$overrideCount ?> saved</span>
        </div>
        <div class="sd-card-body" style="padding-bottom:0">
          <div id="overrideMessage" class="sd-message error" style="display:none"></div>
          <div class="sd-override-form">
            <?php if ($isVenue): ?>
            <div class="sd-fg">
              <label>Apply to</label>
              <select id="overrideScope" class="sd-input">
                <option value="service">Entire venue</option>
                <option value="room">Specific hall</option>
              </select>
            </div>
            <div class="sd-fg">
              <label>Hall</label>
              <select id="overrideRoom" class="sd-input" disabled>
                <option value="">Choose hall</option>
                <?php foreach ($venueRooms as $room): ?>
                  <option value="<?= (int)($room['id'] ?? 0) ?>"><?= $h($room['name'] ?? 'Hall') ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <?php endif; ?>
            <div class="sd-fg">
              <label>Date</label>
              <input id="overrideDate" type="date" class="sd-input">
            </div>
            <div class="sd-fg">
              <label>Type</label>
              <select id="overrideType" class="sd-input">
                <option value="unavailable">Unavailable</option>
                <option value="custom_hours">Custom hours</option>
                <option value="available">Available</option>
              </select>
            </div>
            <div class="sd-fg">
              <label>Opens</label>
              <input id="overrideOpen" type="time" class="sd-input" value="09:00">
            </div>
            <div class="sd-fg">
              <label>Closes</label>
              <input id="overrideClose" type="time" class="sd-input" value="17:00">
            </div>
            <div class="sd-fg full">
              <label>Reason (optional)</label>
              <input id="overrideReason" type="text" class="sd-input" placeholder="e.g. Hari Raya holiday">
            </div>
            <div class="sd-fg full" style="margin:0 -16px -16px;background:var(--surface);padding:10px 16px;border-top:1px solid var(--border)">
              <button type="button" id="saveOverrideBtn" class="btn btn-outline btn-sm" style="width:100%"><i class="ti ti-calendar-plus" style="font-size:12px"></i> Add override</button>
            </div>
          </div>
        </div>
        <div class="sd-card-body" style="padding-top:14px">
          <div id="overrideList" class="sd-override-list">
            <?php foreach ($overrideRows as $override): ?>
              <?php
              $type = (string)($override['type'] ?? 'unavailable');
              ?>
              <div class="sd-override-item"
                   data-override-id="<?= (int)($override['id'] ?? 0) ?>"
                   data-override-date="<?= $h($override['date'] ?? '') ?>"
                   data-override-type="<?= $h($type) ?>"
                   data-override-open="<?= $h(substr((string)($override['open_time'] ?? '09:00'), 0, 5)) ?>"
                   data-override-close="<?= $h(substr((string)($override['close_time'] ?? '17:00'), 0, 5)) ?>"
                   data-override-reason="<?= $h($override['reason'] ?? '') ?>"
                   data-override-scope="service"
                   onclick="editOverride(this)">
                <div>
                  <div class="sd-override-date"><?= $h($formatDate($override['date'] ?? '')) ?></div>
                  <div style="margin-top:4px"><span class="sd-override-type <?= $h($type) ?>">Entire venue · <?= $h(str_replace('_', ' ', $type)) ?></span></div>
                </div>
                <button type="button" class="btn btn-icon btn-danger-ghost btn-sm" onclick="event.stopPropagation(); deleteOverride(<?= (int)($override['id'] ?? 0) ?>)"><i class="ti ti-trash" style="font-size:13px"></i></button>
              </div>
            <?php endforeach; ?>
            <?php if ($isVenue): ?>
              <?php foreach ($venueRooms as $room): ?>
                <?php foreach (($room['overrides'] ?? []) as $override): ?>
                  <?php
                  $type = (string)($override['type'] ?? 'unavailable');
                  ?>
                  <div class="sd-override-item"
                       data-override-id="<?= (int)($override['id'] ?? 0) ?>"
                       data-override-date="<?= $h($override['date'] ?? '') ?>"
                       data-override-type="<?= $h($type) ?>"
                       data-override-open="<?= $h(substr((string)($override['open_time'] ?? '09:00'), 0, 5)) ?>"
                       data-override-close="<?= $h(substr((string)($override['close_time'] ?? '17:00'), 0, 5)) ?>"
                       data-override-reason=""
                       data-override-scope="room"
                       data-override-room-id="<?= (int)($room['id'] ?? 0) ?>"
                       onclick="editOverride(this)">
                    <div>
                      <div class="sd-override-date"><?= $h($formatDate($override['date'] ?? '')) ?></div>
                      <div style="margin-top:4px"><span class="sd-override-type <?= $h($type) ?>"><?= $h($room['name'] ?? 'Hall') ?> · <?= $h(str_replace('_', ' ', $type)) ?></span></div>
                    </div>
                    <button type="button" class="btn btn-icon btn-danger-ghost btn-sm" onclick="event.stopPropagation(); deleteRoomOverride(<?= (int)($override['id'] ?? 0) ?>)"><i class="ti ti-trash" style="font-size:13px"></i></button>
                  </div>
                <?php endforeach; ?>
              <?php endforeach; ?>
            <?php endif; ?>
            <div id="overrideEmpty" class="sd-empty" style="<?= empty($overrideRows) ? '' : 'display:none' ?>">
              <i class="ti ti-calendar"></i>
              <p>No special dates yet</p>
              <small>Saved overrides will appear here.</small>
            </div>
          </div>
        </div>
      </div>

      <!-- === RENTAL PRICING (Dress / Accessories) === -->
      <?php if ($isRental): ?>
      <div class="sd-card sd-anim-card-3">
        <div class="sd-card-head">
          <div>
            <div class="sd-card-title">Rental pricing</div>
            <div class="sd-card-sub">Borrow or buy options for this item</div>
          </div>
          <?php if ($hasRentalPricing): ?>
          <span class="sd-badge" style="background:var(--success-bg, #dcfce7);color:var(--success, #166534)">Set</span>
          <?php else: ?>
          <span class="sd-badge" style="background:var(--warning-bg, #fef3c7);color:var(--warning, #92400e)">Not set</span>
          <?php endif; ?>
        </div>
        <div class="sd-card-body">
          <?php if ($hasRentalPricing): ?>
            <?php if ($rentBorrowPackagePrice > 0 || $rentBorrowCustomizePrice > 0): ?>
            <div class="sd-rental-row">
              <div class="sd-rental-icon"><i class="ti ti-clock"></i></div>
              <div class="sd-rental-body">
                <div class="sd-rental-label">Borrow price</div>
                <div class="sd-rental-matrix">
                  <span><small>Package</small><strong><?= $rentBorrowPackagePrice > 0 ? $money($rentBorrowPackagePrice) : '—' ?></strong></span>
                  <span><small>Customize</small><strong><?= $rentBorrowCustomizePrice > 0 ? $money($rentBorrowCustomizePrice) : '—' ?></strong></span>
                </div>
                <?php if ($rentReturnDays > 0): ?>
                <div class="sd-rental-sub">Return within <?= (int)$rentReturnDays ?> <?= $rentReturnDays === 1 ? 'day' : 'days' ?></div>
                <?php endif; ?>
              </div>
            </div>
            <?php endif; ?>
            <?php if ($rentBuyPackagePrice > 0 || $rentBuyCustomizePrice > 0): ?>
            <div class="sd-rental-row">
              <div class="sd-rental-icon"><i class="ti ti-shopping-bag"></i></div>
              <div class="sd-rental-body">
                <div class="sd-rental-label">Buy price</div>
                <div class="sd-rental-matrix">
                  <span><small>Package</small><strong><?= $rentBuyPackagePrice > 0 ? $money($rentBuyPackagePrice) : '—' ?></strong></span>
                  <span><small>Customize</small><strong><?= $rentBuyCustomizePrice > 0 ? $money($rentBuyCustomizePrice) : '—' ?></strong></span>
                </div>
              </div>
            </div>
            <?php endif; ?>
          <?php else: ?>
            <div class="sd-empty">
              <i class="ti ti-tag"></i>
              <p>No rental pricing set</p>
              <small>Add borrow or buy pricing from the service edit form.</small>
            </div>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- === BOOKING PREVIEW === -->
      <div class="sd-card sd-anim-card-4">
        <div class="sd-card-head">
          <div>
            <div class="sd-card-title">Booking preview</div>
            <div class="sd-card-sub">See available slots for any date</div>
          </div>
        </div>
        <div class="sd-card-body">
          <div style="display:flex;gap:10px;margin-bottom:12px">
            <input id="previewDate" type="date" class="sd-input" style="flex:1">
            <button type="button" id="previewSlotsBtn" class="btn btn-primary btn-sm"><i class="ti ti-eye" style="font-size:12px"></i> Show</button>
          </div>
          <div id="previewSlotsResult" class="sd-preview-box">
            <div class="sd-preview-empty">Pick a date to see available slots</div>
          </div>
        </div>
      </div>

      <!-- === SERVICE INFO === -->
      <div class="sd-card sd-anim-card-5">
        <div class="sd-card-head">
          <div>
            <div class="sd-card-title">Service info</div>
            <div class="sd-card-sub">Quick reference</div>
          </div>
          <div style="display:flex;gap:8px;flex-wrap:wrap">
            <a href="<?= URLROOT ?>/supplier/serviceCalendar/<?= (int)$serviceId ?>" class="btn btn-outline btn-sm"><i class="ti ti-calendar" style="font-size:13px"></i> Calendar</a>
            <a href="<?= URLROOT ?>/supplier/services" class="btn btn-ghost btn-sm"><i class="ti ti-edit" style="font-size:13px"></i> Edit</a>
          </div>
        </div>
        <div class="sd-card-body">
          <div class="sd-info-row">
            <span class="sd-info-key">Category</span>
            <span class="sd-info-val"><?= $h($serviceCategoryRaw) ?></span>
          </div>
          <?php if ($isRental): ?>
          <div class="sd-info-row">
            <span class="sd-info-key">Borrow package</span>
            <span class="sd-info-val" id="serviceInfoBorrowPackagePrice"><?= $rentBorrowPackagePrice > 0 ? $money($rentBorrowPackagePrice) : '—' ?></span>
          </div>
          <div class="sd-info-row">
            <span class="sd-info-key">Borrow customize</span>
            <span class="sd-info-val" id="serviceInfoBorrowCustomizePrice"><?= $rentBorrowCustomizePrice > 0 ? $money($rentBorrowCustomizePrice) : '—' ?></span>
          </div>
          <?php if (($rentBorrowPackagePrice > 0 || $rentBorrowCustomizePrice > 0) && $rentReturnDays > 0): ?>
          <div class="sd-info-row">
            <span class="sd-info-key">Return within</span>
            <span class="sd-info-val"><?= (int)$rentReturnDays ?> <?= $rentReturnDays === 1 ? 'day' : 'days' ?></span>
          </div>
          <?php endif; ?>
          <div class="sd-info-row">
            <span class="sd-info-key">Buy package</span>
            <span class="sd-info-val" id="serviceInfoBuyPackagePrice"><?= $rentBuyPackagePrice > 0 ? $money($rentBuyPackagePrice) : '—' ?></span>
          </div>
          <div class="sd-info-row">
            <span class="sd-info-key">Buy customize</span>
            <span class="sd-info-val" id="serviceInfoBuyCustomizePrice"><?= $rentBuyCustomizePrice > 0 ? $money($rentBuyCustomizePrice) : '—' ?></span>
          </div>
          <?php else: ?>
          <div class="sd-info-row">
            <span class="sd-info-key">Package price</span>
            <span class="sd-info-val" id="serviceInfoPackagePrice"><?= $money($servicePackagePrice) ?></span>
          </div>
          <div class="sd-info-row">
            <span class="sd-info-key">Customize price</span>
            <span class="sd-info-val" id="serviceInfoCustomizePrice"><?= $money($serviceCustomizePrice) ?></span>
          </div>
          <?php endif; ?>
          <div class="sd-info-row">
            <span class="sd-info-key">Status</span>
            <span class="sd-info-val">
              <span style="display:inline-flex;align-items:center;gap:4px;color:<?= $serviceStatus === 'active' ? 'var(--success)' : 'var(--text-3)' ?>;font-size:12px">
                <i class="ti ti-circle-check-filled" style="font-size:13px"></i> <?= $h(ucfirst($serviceStatus)) ?>
              </span>
            </span>
          </div>
          <?php if (!$isRental): ?>
          <div class="sd-info-row">
            <span class="sd-info-key">Slot duration</span>
            <span class="sd-info-val"><?= $h($durationLabel($slotDuration)) ?></span>
          </div>
          <?php endif; ?>
          <div class="sd-info-row">
            <span class="sd-info-key">Minimum notice</span>
            <span class="sd-info-val" id="serviceInfoMinLeadDays"><?= (int)($service['min_lead_days'] ?? 0) ?> days</span>
          </div>
          <?php if ($isVenue): ?>
            <div class="sd-info-row">
              <span class="sd-info-key">Venue</span>
              <span class="sd-info-val" id="serviceInfoVenue"><?= $h($service['venue_name'] ?? $service['venue'] ?? '-') ?></span>
            </div>
            <div class="sd-info-row">
              <span class="sd-info-key">Halls</span>
              <span class="sd-info-val" id="serviceInfoHalls"><?= count($venueRooms) ?></span>
            </div>
          <?php elseif (!$isRental): ?>
            <div class="sd-info-row">
              <span class="sd-info-key">Concurrent bookings</span>
              <span class="sd-info-val" id="serviceInfoConcurrent"><?= (int)$maxConcurrent ?></span>
            </div>
          <?php endif; ?>
        </div>
      </div>

    </div>
  </div>

</div>
