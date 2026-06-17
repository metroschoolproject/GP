<?php
$service = $service ?? [];
$message = $message ?? '';
$readiness = is_array($service['readiness'] ?? null) ? $service['readiness'] : ['ready' => false, 'missing' => []];
$media = is_array($service['media'] ?? null) ? $service['media'] : [];
$rooms = is_array($service['venue_rooms'] ?? null) ? $service['venue_rooms'] : [];
$weekly = is_array($service['availability']['weekly'] ?? null) ? $service['availability']['weekly'] : [];
$isVenue = strtolower((string)($service['category'] ?? '')) === 'venue';
$isApproved = ($service['status'] ?? 'inactive') === 'active';

$dashboardTitle = 'Admin';
$dashboardCrumb = 'Service Review';
$dashboardContentClass = 'admin-service-review-content';

$h = function ($value) {
    return htmlspecialchars(htmlspecialchars_decode((string)$value, ENT_QUOTES), ENT_QUOTES, 'UTF-8');
};

$money = function ($value) {
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

$dashboardContent = function () use ($service, $message, $readiness, $media, $rooms, $weekly, $isVenue, $isApproved, $h, $money, $dayNames) {
?>
<style>
  .admin-service-review-content{min-height:100%;background:#FBFBF9;padding:28px 32px;color:#111827;font-size:13px}
  .service-review-page{--surface:#fff;--soft:#faf5ef;--hover:#eddecc;--border:#ead8c7;--primary:#6d4c5b;--text:#111827;--muted:#b79c8b;--body:#7b5c69;--success-bg:#d1fae5;--success:#065f46;--warn-bg:#fef3c7;--warn:#92400e;--danger-bg:#fee2e2;--danger:#991b1b;max-width:1300px;margin:0 auto}
  .page-head{display:flex;align-items:flex-end;justify-content:space-between;gap:16px;margin-bottom:22px}
  .eyebrow{font-size:10px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;color:var(--muted);margin-bottom:4px}
  .page-title{font-size:22px;font-weight:700;color:var(--text);margin:0}
  .sub{margin-top:4px;color:var(--body);font-size:13px}
  .actions{display:flex;gap:8px;align-items:center}
  .btn{display:inline-flex;align-items:center;justify-content:center;height:34px;border-radius:.75rem;border:1px solid var(--border);padding:0 14px;font-size:12px;font-weight:800;text-decoration:none;cursor:pointer;font-family:inherit}
  .btn-primary{background:var(--primary);border-color:var(--primary);color:#fff}
  .btn-ghost{background:var(--surface);color:var(--primary)}
  .btn:disabled{opacity:.55;cursor:not-allowed}
  .review-grid{display:grid;grid-template-columns:minmax(0,1fr) 360px;gap:18px;align-items:start}
  .card{border:1px solid var(--border);border-radius:.75rem;background:var(--surface);box-shadow:0 1px 2px rgba(28,25,23,.04);overflow:hidden}
  .card-head{display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid var(--border);padding:14px 18px}
  .card-title{font-size:13px;font-weight:800;color:var(--text)}
  .card-body{padding:18px}
  .cover{width:100%;aspect-ratio:16/8;border-radius:.75rem;object-fit:cover;background:var(--soft);border:1px solid var(--border)}
  .desc{margin-top:14px;color:var(--body);line-height:1.55}
  .facts{display:grid;gap:10px}
  .fact{display:flex;justify-content:space-between;gap:12px;border-bottom:1px solid var(--border);padding-bottom:9px}
  .fact:last-child{border-bottom:0;padding-bottom:0}
  .key{color:var(--muted);font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.08em}
  .val{color:var(--text);font-weight:700;text-align:right}
  .badge{display:inline-flex;border-radius:999px;padding:4px 10px;font-size:10px;font-weight:900;text-transform:uppercase;letter-spacing:.08em}
  .badge-ready{background:var(--success-bg);color:var(--success)}
  .badge-warn{background:var(--warn-bg);color:var(--warn)}
  .review-toast-stack{position:fixed;right:24px;bottom:24px;z-index:90;display:grid;gap:10px;width:min(410px,calc(100vw - 32px))}
  .review-toast{display:flex;align-items:flex-start;gap:12px;border-radius:14px;padding:15px 16px;background:#fff;box-shadow:0 22px 70px rgba(44,31,40,.18),0 2px 10px rgba(44,31,40,.08);animation:reviewToastIn .22s ease}
  .review-toast.success{border:1px solid #a8d5bc;background:#f4fbf7;color:var(--success)}
  .review-toast.info{border:1px solid var(--border);background:#fff;color:var(--body)}
  .review-toast-icon{display:grid;width:34px;height:34px;flex:0 0 34px;place-items:center;border-radius:10px;background:rgba(255,255,255,.72);font-weight:900}
  .review-toast-copy{min-width:0;flex:1}
  .review-toast-copy strong{display:block;margin-bottom:3px;color:var(--text);font-size:13px;font-weight:900}
  .review-toast-copy p{margin:0;color:var(--body);font-size:12px;font-weight:700;line-height:1.5}
  .review-toast-close{display:grid;width:24px;height:24px;flex:0 0 24px;place-items:center;border:0;border-radius:999px;background:transparent;color:var(--muted);font-size:20px;line-height:1;cursor:pointer}
  .review-toast-close:hover{background:rgba(44,31,40,.08);color:var(--text)}
  @keyframes reviewToastIn{from{opacity:0;transform:translateY(12px) scale(.98)}to{opacity:1;transform:translateY(0) scale(1)}}
  .missing{display:grid;gap:8px;margin:0;padding:0;list-style:none}
  .missing li{border:1px solid var(--border);border-radius:.65rem;background:var(--soft);padding:9px 11px;color:var(--body);font-weight:700}
  .thumbs{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:8px}
  .thumbs img{width:100%;aspect-ratio:1;border-radius:.65rem;object-fit:cover;border:1px solid var(--border)}
  .table{width:100%;border-collapse:collapse}
  .table th,.table td{padding:9px;border-bottom:1px solid var(--border);text-align:left;font-size:12px}
  .table th{color:var(--muted);font-size:10px;text-transform:uppercase;letter-spacing:.08em}
  @media(max-width:1000px){.review-grid{grid-template-columns:1fr}.thumbs{grid-template-columns:repeat(2,minmax(0,1fr))}}
  @media(max-width:640px){.review-toast-stack{right:16px;bottom:16px}}
</style>

<div class="service-review-page">
  <div class="page-head">
    <div>
      <p class="eyebrow">Service publish request</p>
      <h1 class="page-title"><?= $h($service['name'] ?? 'Service') ?></h1>
      <div class="sub"><?= $h($service['supplier_name'] ?? 'Supplier') ?> · <?= $h($service['category'] ?? 'Service') ?></div>
    </div>
    <div class="actions">
      <a class="btn btn-ghost" href="<?= URLROOT ?>/admin/notifications">Back</a>
      <form method="post" action="<?= URLROOT ?>/admin/approveService/<?= (int)($service['id'] ?? 0) ?>">
        <button class="btn btn-primary" type="submit" <?= ($isApproved || empty($readiness['ready'])) ? 'disabled' : '' ?>><?= $isApproved ? 'Already approved' : 'Approve publish' ?></button>
      </form>
    </div>
  </div>

  <?php if ($isApproved || $message !== ''): ?>
    <div class="review-toast-stack" role="status" aria-live="polite">
      <?php if ($isApproved): ?>
        <div class="review-toast success">
          <div class="review-toast-icon">✓</div>
          <div class="review-toast-copy">
            <strong>Already approved</strong>
            <p>This service is already approved and live for customers.</p>
          </div>
          <button type="button" class="review-toast-close" aria-label="Dismiss notification">&times;</button>
        </div>
      <?php endif; ?>
      <?php if ($message !== ''): ?>
        <div class="review-toast info">
          <div class="review-toast-icon">i</div>
          <div class="review-toast-copy">
            <strong>Review update</strong>
            <p><?= $h($message) ?></p>
          </div>
          <button type="button" class="review-toast-close" aria-label="Dismiss notification">&times;</button>
        </div>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <div class="review-grid">
    <div class="card">
      <div class="card-head">
        <div class="card-title">Customer Listing Preview</div>
        <span class="badge <?= ($isApproved || !empty($readiness['ready'])) ? 'badge-ready' : 'badge-warn' ?>"><?= $isApproved ? 'Approved' : (!empty($readiness['ready']) ? 'Ready' : 'Incomplete') ?></span>
      </div>
      <div class="card-body">
        <?php if (!empty($service['img'])): ?>
          <img class="cover" src="<?= $h($service['img']) ?>" alt="<?= $h($service['name'] ?? 'Service') ?>">
        <?php elseif (!empty($media[0]['file_url'])): ?>
          <img class="cover" src="<?= $h($media[0]['file_url']) ?>" alt="<?= $h($service['name'] ?? 'Service') ?>">
        <?php else: ?>
          <div class="cover"></div>
        <?php endif; ?>
        <p class="desc"><?= $h($service['desc'] ?? 'No description.') ?></p>
      </div>
    </div>

    <div class="card">
      <div class="card-head"><div class="card-title">Review Details</div></div>
      <div class="card-body">
        <div class="facts">
          <div class="fact"><span class="key">Status</span><span class="val"><?= $h($service['status'] ?? '-') ?></span></div>
          <div class="fact"><span class="key">Price</span><span class="val"><?= $money($service['price_min'] ?? $service['price'] ?? 0) ?></span></div>
          <div class="fact"><span class="key">Supplier</span><span class="val"><?= $h($service['supplier_name'] ?? '-') ?></span></div>
          <div class="fact"><span class="key">Owner</span><span class="val"><?= $h($service['owner_name'] ?? '-') ?></span></div>
          <div class="fact"><span class="key">Email</span><span class="val"><?= $h($service['owner_email'] ?? '-') ?></span></div>
          <div class="fact"><span class="key">Payment</span><span class="val"><?= $h($service['supplier_payment_status'] ?? '-') ?></span></div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-head"><div class="card-title"><?= $isVenue ? 'Halls' : 'Weekly Time Slots' ?></div></div>
      <div class="card-body">
        <table class="table">
          <thead>
            <tr>
              <?php if ($isVenue): ?>
                <th>Hall</th><th>Capacity</th><th>Price</th><th>Hours</th>
              <?php else: ?>
                <th>Day</th><th>Status</th><th>Hours</th>
              <?php endif; ?>
            </tr>
          </thead>
          <tbody>
            <?php if ($isVenue): ?>
              <?php foreach ($rooms as $room): ?>
                <tr><td><?= $h($room['name'] ?? '-') ?></td><td><?= (int)($room['capacity'] ?? 0) ?></td><td><?= $money($room['price'] ?? 0) ?></td><td><?= $h(substr((string)($room['start_time'] ?? ''), 0, 5)) ?> - <?= $h(substr((string)($room['end_time'] ?? ''), 0, 5)) ?></td></tr>
              <?php endforeach; ?>
            <?php else: ?>
              <?php foreach ($weekly as $row): ?>
                <?php $dayNumber = (int)($row['day_of_week'] ?? 0); ?>
                <tr><td><?= $h($dayNames[$dayNumber] ?? ('Day ' . $dayNumber)) ?></td><td><?= empty($row['is_available']) ? 'Closed' : 'Open' ?></td><td><?= $h(substr((string)($row['open_time'] ?? ''), 0, 5)) ?> - <?= $h(substr((string)($row['close_time'] ?? ''), 0, 5)) ?></td></tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="card">
      <div class="card-head"><div class="card-title">Publish Checks</div></div>
      <div class="card-body">
        <?php if (!empty($readiness['ready'])): ?>
          <span class="badge badge-ready">All required details confirmed</span>
        <?php else: ?>
          <ul class="missing">
            <?php foreach (($readiness['missing'] ?? []) as $missing): ?>
              <li><?= $h($missing) ?></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>

    <div class="card">
      <div class="card-head"><div class="card-title">Photos</div></div>
      <div class="card-body">
        <div class="thumbs">
          <?php foreach ($media as $item): ?>
            <img src="<?= $h($item['file_url'] ?? '') ?>" alt="Service photo">
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
  document.querySelectorAll('.review-toast-close').forEach(button => {
    button.addEventListener('click', () => button.closest('.review-toast')?.remove());
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
    <?php require APPROOT . '/views/dashboardLayout/adminsidebar.php'; ?>
</body>
</html>
