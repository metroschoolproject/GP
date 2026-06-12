<div id="supplier-service-detail" class="page">

  <div class="topbar" data-service-status="<?= $h($serviceStatus) ?>">
    <div style="display:flex;align-items:center;gap:8px">
      <span class="count-badge">ID #<?= (int)$serviceId ?></span>
      <div id="publishStatusPill" class="status-pill"><span id="publishStatusDot" class="dot <?= $serviceStatus === 'active' || $isReady ? 'ready' : '' ?>"></span> <span id="publishStatusText"><?= $serviceStatus === 'active' ? 'Live' : ($isReady ? 'Ready' : 'Needs attention') ?></span></div>
    </div>
    <button type="button" id="publishServiceBtn" class="btn btn-primary btn-sm" <?= $serviceStatus === 'active' ? 'disabled' : '' ?>>
      <i class="ti <?= $serviceStatus === 'active' ? 'ti-circle-check' : 'ti-send' ?>" style="font-size:13px"></i> <span id="publishServiceBtnText"><?= $serviceStatus === 'active' ? 'Published' : 'Request publish' ?></span>
    </button>
  </div>
  <div id="publishMessage" class="message-bar error" style="display:none"></div>

  <div class="hero">
    <div class="hero-img">
      <?php if ($serviceImage !== ''): ?>
        <img src="<?= $h($serviceImage) ?>" alt="<?= $h($serviceNameRaw) ?>">
      <?php else: ?>
        <div class="hero-img-placeholder">
          <div style="display:flex;flex-direction:column;align-items:center;gap:8px;color:var(--text-3)">
            <div style="width:60px;height:60px;border-radius:14px;border:1.5px dashed var(--border-strong);display:flex;align-items:center;justify-content:center">
              <i class="ti ti-photo" style="font-size:26px"></i>
            </div>
            <span style="font-size:11px;font-weight:600">No cover photo</span>
          </div>
        </div>
      <?php endif; ?>
      <div class="category-badge"><i class="ti ti-sparkles" style="font-size:11px"></i> <?= $h($serviceCategoryRaw) ?></div>
    </div>

    <div class="hero-body">
      <div>
        <div class="hero-tags">
          <span class="tag tag-accent">Service detail</span>
          <span class="tag tag-muted"><?= $h($serviceCategoryRaw) ?></span>
        </div>
        <h1 class="hero-title" style="margin-top:10px"><?= $h($serviceNameRaw) ?></h1>
        <p class="hero-desc"><?= $serviceDescriptionRaw !== '' ? $h($serviceDescriptionRaw) : 'No description has been added yet.' ?></p>
      </div>

      <div class="hero-meta">
        <div class="price-callout">
          <div class="stat-label">Starting price</div>
          <div class="stat-value" style="font-size:26px"><?= $money($servicePriceAmount) ?></div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Days open</div>
          <div id="heroOpenDaysValue" class="stat-value"><?= (int)$openDaysCount ?><span style="font-size:14px;color:var(--text-3)">/7</span></div>
          <div id="heroOpenDaysSub" class="stat-sub"><?= $openDaysCount > 0 ? 'Schedule active' : 'No days open' ?></div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Portfolio</div>
          <div class="stat-value"><?= (int)$mediaCount ?></div>
          <div class="stat-sub"><?= $mediaCount === 1 ? 'photo uploaded' : 'photos uploaded' ?></div>
        </div>
      </div>

      <div class="attention-bar <?= $isReady ? 'ready' : '' ?>">
        <div class="attention-icon <?= $isReady ? 'ready' : '' ?>"><i class="ti <?= $isReady ? 'ti-circle-check' : 'ti-alert-triangle' ?>" style="font-size:16px"></i></div>
        <div style="flex:1">
          <div style="display:flex;align-items:center;justify-content:space-between;gap:8px">
            <div class="attention-title"><?= $isReady ? 'This service is ready for customers' : count($attentionItems) . ' ' . (count($attentionItems) === 1 ? 'thing' : 'things') . ' to complete before customers can book' ?></div>
            <span style="padding:2px 8px;border-radius:999px;background:var(--white);border:1px solid var(--border);font-size:10px;font-weight:700;color:<?= $isReady ? 'var(--success)' : 'var(--warning)' ?>"><?= $isReady ? 'Ready' : count($attentionItems) . ' items' ?></span>
          </div>
          <?php if (!$isReady): ?>
            <div class="attention-items">
              <?php foreach ($attentionItems as $item): ?>
                <div class="attention-item">
                  <i class="<?= $h($item['icon']) ?>"></i>
                  <div><div class="attention-item-label"><?= $h($item['label']) ?></div><div class="attention-item-detail"><?= $h($item['detail']) ?></div></div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <div class="attention-sub">Pricing, media, and weekly availability are set.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <div class="workspace">
    <div class="main-col">

      <div class="card">
        <div class="card-header">
          <div>
            <div class="card-title">Portfolio photos</div>
            <div class="card-sub">Shown to customers browsing your listing</div>
          </div>
          <label class="btn btn-primary btn-sm" style="cursor:pointer">
            <i class="ti ti-photo-plus" style="font-size:13px"></i> Add photo
            <input id="serviceMediaInput" type="file" accept="image/*" style="display:none">
          </label>
        </div>
        <div class="card-body">
          <div id="mediaMessage" class="message-bar error" style="display:none"></div>
          <div id="mediaGrid" class="photo-grid">
            <?php foreach ($media as $item): ?>
              <div class="photo-card" data-media-id="<?= (int)($item['id'] ?? 0) ?>">
                <img src="<?= $h($item['file_url'] ?? '') ?>" alt="Service photo">
                <button type="button" class="del-btn" onclick="deleteServiceMedia(<?= (int)($item['id'] ?? 0) ?>)"><i class="ti ti-trash" style="font-size:13px"></i></button>
              </div>
            <?php endforeach; ?>
            <label class="photo-add">
              <i class="ti ti-plus"></i>
              <span>Upload photo</span>
              <input type="file" accept="image/*" data-media-picker style="display:none">
            </label>
          </div>
        </div>
      </div>

      <?php if ($isVenue): ?>
        <div class="card">
          <div class="card-header">
            <div>
              <div class="card-title">Rooms / Halls</div>
              <div class="card-sub">Room capacity, price, and bookable hours</div>
            </div>
            <span id="hallCount" class="count-badge"><?= count($venueRooms) ?> <?= count($venueRooms) === 1 ? 'hall' : 'halls' ?></span>
          </div>
          <div class="card-body">
            <div id="hallMessage" class="message-bar error" style="display:none"></div>
            <div id="hallGrid" class="hall-grid">
              <?php foreach ($venueRooms as $room): ?>
                <div class="hall-card" data-room-id="<?= (int)($room['id'] ?? 0) ?>">
                  <input type="hidden" class="hall-id" value="<?= (int)($room['id'] ?? 0) ?>">
                  <div class="hall-card-head">
                    <div class="hall-card-icon"><i class="ti ti-door"></i></div>
                    <button type="button" class="btn btn-icon btn-danger-ghost btn-sm" onclick="removeHall(this)"><i class="ti ti-trash" style="font-size:13px"></i></button>
                  </div>
                  <div class="hall-inputs">
                    <div class="hall-input-group full"><label>Hall name</label><input class="hall-input hall-name" value="<?= $h($room['name'] ?? '') ?>"></div>
                    <div class="hall-input-group"><label>Capacity</label><input type="number" min="1" class="hall-input hall-capacity" value="<?= (int)($room['capacity'] ?? 1) ?>"></div>
                    <div class="hall-input-group"><label>Price</label><input type="number" min="0" step="0.01" class="hall-input hall-price" value="<?= $h($room['price'] ?? 0) ?>"></div>
                    <div class="hall-input-group"><label>Start time</label><input type="time" lang="en-GB" class="hall-input hall-start" value="<?= $h(substr((string)($room['start_time'] ?? '09:00'), 0, 5)) ?>"></div>
                    <div class="hall-input-group"><label>End time</label><input type="time" lang="en-GB" class="hall-input hall-end" value="<?= $h(substr((string)($room['end_time'] ?? '17:00'), 0, 5)) ?>"></div>
                  </div>
                  <div class="hall-time-display"><?= $h($formatTime($room['start_time'] ?? '09:00') . ' - ' . $formatTime($room['end_time'] ?? '17:00')) ?></div>
                </div>
              <?php endforeach; ?>
            </div>
            <button type="button" class="add-hall-btn" onclick="addHall()"><i class="ti ti-plus"></i> Add hall</button>
          </div>
          <div class="card-footer">
            <button type="button" class="btn btn-primary btn-sm" id="saveHallsBtn"><i class="ti ti-check" style="font-size:12px"></i> Save halls</button>
          </div>
        </div>
      <?php endif; ?>

      <div class="card">
        <div class="card-header">
          <div>
            <div class="card-title">Weekly availability</div>
            <div class="card-sub">Hours and slot settings for each day</div>
          </div>
          <span id="openDaysBadge" class="count-badge"><?= (int)$openDaysCount ?> days open</span>
        </div>
        <div class="card-body">
          <div id="availabilityMessage" class="message-bar error" style="display:none"></div>
          <div class="avail-controls">
            <div class="avail-control">
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
            <div class="avail-control">
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
            <div class="avail-control">
              <label>Max concurrent</label>
              <input id="availabilityConcurrent" type="number" min="1" value="<?= (int)$maxConcurrent ?>">
            </div>
          </div>

          <table class="avail-table">
            <thead>
              <tr>
                <th style="width:120px">Day</th>
                <th style="width:120px">Status</th>
                <th>Open</th>
                <th>Close</th>
              </tr>
            </thead>
            <tbody id="avail-body">
              <?php foreach ($days as $dayNumber => $dayName): ?>
                <?php
                $row = $weeklyByDay[$dayNumber] ?? [];
                $open = $isDayAvailable($dayNumber, $row);
                $start = substr((string)($row['open_time'] ?? '09:00'), 0, 5);
                $end = substr((string)($row['close_time'] ?? '17:00'), 0, 5);
                ?>
                <tr class="availability-day-row" data-day="<?= (int)$dayNumber ?>">
                  <td><div class="day-name"><?= $h($dayName) ?></div><div class="day-num">Day <?= (int)$dayNumber ?></div></td>
                  <td>
                    <label class="toggle-wrap">
                      <label class="toggle">
                        <input type="checkbox" class="availability-open" <?= $open ? 'checked' : '' ?> onchange="toggleDay(this)">
                        <div class="toggle-track"></div>
                        <div class="toggle-thumb"></div>
                      </label>
                      <span class="toggle-label"><?= $open ? 'Open' : 'Closed' ?></span>
                    </label>
                  </td>
                  <td class="start-cell"><?= $open ? '<input class="time-input availability-start" type="time" value="' . $h($start) . '">' : '<span class="closed-indicator">-</span>' ?></td>
                  <td class="end-cell"><?= $open ? '<input class="time-input availability-end" type="time" value="' . $h($end) . '">' : '<span class="closed-indicator">-</span>' ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <div class="card-footer">
          <button type="button" class="btn btn-outline btn-sm" onclick="window.location.reload()">Discard</button>
          <button type="button" id="saveAvailabilityBtn" class="btn btn-primary btn-sm"><i class="ti ti-check" style="font-size:12px"></i> Save schedule</button>
        </div>
      </div>

    </div>

    <div class="side-col">

      <div class="card">
        <div class="card-header">
          <div>
            <div class="card-title">Special dates</div>
            <div class="card-sub">Closures, holidays, custom hours</div>
          </div>
          <span id="overrideCount" class="count-badge"><?= (int)$overrideCount ?> saved</span>
        </div>
        <div class="card-body" style="padding-bottom:0">
          <div id="overrideMessage" class="message-bar error" style="display:none"></div>
          <div class="override-form">
            <?php if ($isVenue): ?>
            <div class="form-group">
              <div class="form-label">Apply to</div>
              <select id="overrideScope" class="form-input">
                <option value="service">Entire venue</option>
                <option value="room">Specific hall</option>
              </select>
            </div>
            <div class="form-group">
              <div class="form-label">Hall</div>
              <select id="overrideRoom" class="form-input" disabled>
                <option value="">Choose hall</option>
                <?php foreach ($venueRooms as $room): ?>
                  <option value="<?= (int)($room['id'] ?? 0) ?>"><?= $h($room['name'] ?? 'Hall') ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <?php endif; ?>
            <div class="form-group">
              <div class="form-label">Date</div>
              <input id="overrideDate" type="date" class="form-input">
            </div>
            <div class="form-group">
              <div class="form-label">Type</div>
              <select id="overrideType" class="form-input">
                <option value="unavailable">Unavailable</option>
                <option value="custom_hours">Custom hours</option>
                <option value="available">Available</option>
              </select>
            </div>
            <div class="form-group">
              <div class="form-label">Opens</div>
              <input id="overrideOpen" type="time" class="form-input" value="09:00">
            </div>
            <div class="form-group">
              <div class="form-label">Closes</div>
              <input id="overrideClose" type="time" class="form-input" value="17:00">
            </div>
            <div class="form-group full">
              <div class="form-label">Reason (optional)</div>
              <input id="overrideReason" type="text" class="form-input" placeholder="e.g. Hari Raya holiday">
            </div>
            <div class="form-group full" style="margin:0 -14px -14px;background:var(--white);padding:10px 14px;border-top:1px solid var(--border)">
              <button type="button" id="saveOverrideBtn" class="btn btn-outline btn-sm" style="width:100%"><i class="ti ti-calendar-plus" style="font-size:12px"></i> Add override</button>
            </div>
          </div>
        </div>
        <div class="card-body" style="padding-top:14px">
          <div id="overrideList" class="override-list">
            <?php foreach ($overrideRows as $override): ?>
              <?php
              $type = (string)($override['type'] ?? 'unavailable');
              $typeClass = $type === 'custom_hours' ? 'type-custom' : ($type === 'available' ? 'type-available' : 'type-unavailable');
              ?>
              <div class="override-item"
                   data-override-id="<?= (int)($override['id'] ?? 0) ?>"
                   data-override-date="<?= $h($override['date'] ?? '') ?>"
                   data-override-type="<?= $h($type) ?>"
                   data-override-open="<?= $h(substr((string)($override['open_time'] ?? '09:00'), 0, 5)) ?>"
                   data-override-close="<?= $h(substr((string)($override['close_time'] ?? '17:00'), 0, 5)) ?>"
                   data-override-reason="<?= $h($override['reason'] ?? '') ?>"
                   data-override-scope="service"
                   onclick="editOverride(this)">
                <div>
                  <div class="override-date"><?= $h($formatDate($override['date'] ?? '')) ?></div>
                  <div style="margin-top:3px"><span class="override-type <?= $h($typeClass) ?>">Entire venue · <?= $h(str_replace('_', ' ', $type)) ?></span></div>
                </div>
                <button type="button" class="btn btn-icon btn-danger-ghost btn-sm" onclick="event.stopPropagation(); deleteOverride(<?= (int)($override['id'] ?? 0) ?>)"><i class="ti ti-trash" style="font-size:13px"></i></button>
              </div>
            <?php endforeach; ?>
            <?php if ($isVenue): ?>
              <?php foreach ($venueRooms as $room): ?>
                <?php foreach (($room['overrides'] ?? []) as $override): ?>
                  <?php
                  $type = (string)($override['type'] ?? 'unavailable');
                  $typeClass = $type === 'custom_hours' ? 'type-custom' : ($type === 'available' ? 'type-available' : 'type-unavailable');
                  ?>
                  <div class="override-item"
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
                      <div class="override-date"><?= $h($formatDate($override['date'] ?? '')) ?></div>
                      <div style="margin-top:3px"><span class="override-type <?= $h($typeClass) ?>"><?= $h($room['name'] ?? 'Hall') ?> · <?= $h(str_replace('_', ' ', $type)) ?></span></div>
                    </div>
                    <button type="button" class="btn btn-icon btn-danger-ghost btn-sm" onclick="event.stopPropagation(); deleteRoomOverride(<?= (int)($override['id'] ?? 0) ?>)"><i class="ti ti-trash" style="font-size:13px"></i></button>
                  </div>
                <?php endforeach; ?>
              <?php endforeach; ?>
            <?php endif; ?>
            <div id="overrideEmpty" class="empty-state" style="<?= empty($overrideRows) ? '' : 'display:none' ?>">
              <i class="ti ti-calendar"></i>
              <p>No special dates yet</p>
              <small>Saved overrides will appear here.</small>
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <div>
            <div class="card-title">Booking preview</div>
            <div class="card-sub">See what customers can book</div>
          </div>
        </div>
        <div class="card-body">
          <div style="display:flex;gap:8px;margin-bottom:10px">
            <input id="previewDate" type="date" class="form-input" style="flex:1">
            <button type="button" id="previewSlotsBtn" class="btn btn-primary btn-sm"><i class="ti ti-eye" style="font-size:12px"></i> Show</button>
          </div>
          <div id="previewSlotsResult" class="preview-box">
            <div class="preview-empty">Pick a date to see available slots</div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <div>
            <div class="card-title">Service info</div>
            <div class="card-sub">Quick reference</div>
          </div>
          <div style="display:flex;gap:8px;flex-wrap:wrap">
            <a href="<?= URLROOT ?>/supplier/serviceCalendar/<?= (int)$serviceId ?>" class="btn btn-outline btn-sm"><i class="ti ti-calendar" style="font-size:13px"></i> Calendar</a>
            <a href="<?= URLROOT ?>/supplier/services" class="btn btn-ghost btn-sm"><i class="ti ti-edit" style="font-size:13px"></i> Edit</a>
          </div>
        </div>
        <div class="card-body">
          <div class="info-row">
            <span class="info-key">Category</span>
            <span class="info-val"><?= $h($serviceCategoryRaw) ?></span>
          </div>
          <div class="info-row">
            <span class="info-key">Starting price</span>
            <span class="info-val" id="serviceInfoPrice"><?= $money($servicePriceAmount) ?></span>
          </div>
          <div class="info-row">
            <span class="info-key">Status</span>
            <span class="info-val"><span style="display:inline-flex;align-items:center;gap:4px;color:<?= $serviceStatus === 'active' ? 'var(--success)' : 'var(--text-3)' ?>;font-size:12px"><i class="ti ti-circle-check-filled" style="font-size:13px"></i> <?= $h(ucfirst($serviceStatus)) ?></span></span>
          </div>
          <div class="info-row">
            <span class="info-key">Slot duration</span>
            <span class="info-val"><?= $h($durationLabel($slotDuration)) ?></span>
          </div>
          <?php if ($isVenue): ?>
            <div class="info-row">
              <span class="info-key">Venue</span>
              <span class="info-val" id="serviceInfoVenue"><?= $h($service['venue_name'] ?? $service['venue'] ?? '-') ?></span>
            </div>
            <div class="info-row">
              <span class="info-key">Halls</span>
              <span class="info-val" id="serviceInfoHalls"><?= count($venueRooms) ?></span>
            </div>
          <?php endif; ?>
          <div class="info-row">
            <span class="info-key">Concurrent bookings</span>
            <span class="info-val" id="serviceInfoConcurrent"><?= (int)$maxConcurrent ?></span>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>
