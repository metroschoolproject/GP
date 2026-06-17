<?php
$booking = $booking ?? [];
$items = $items ?? [];
$eventDetails = $eventDetails ?? [];
$suppliers = $suppliers ?? [];
$logs = $logs ?? [];
$vouchers = $vouchers ?? [];
$bookingRef = $bookingRef ?? '';
$depositPercent = (int)($depositPercent ?? 10);
$canReview = $canReview ?? false;
$existingReview = $existingReview ?? null;
$canEditReview = $canEditReview ?? false;

$statusLabels = ['draft'=>'Draft','pending_supplier_response'=>'Awaiting Supplier Response','pending_payment'=>'Pending Payment','paid'=>'Paid','pending_admin'=>'Pending Admin','confirmed'=>'Confirmed','completed'=>'Completed','cancelled'=>'Cancelled'];
$money = fn($v) => 'RM '.number_format((float)$v,0);
$plain = function($v){ $t=(string)$v; for($i=0;$i<10;$i++){$d=html_entity_decode($t,ENT_QUOTES|ENT_HTML5,'UTF-8');if($d===$t)break;$t=$d;}return $t; };
$h = fn($v)=>htmlspecialchars($plain($v),ENT_QUOTES,'UTF-8');
?>
<!DOCTYPE html>
<html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Booking Detail — Golden Promise</title>
<?php $v=file_exists(APPROOT.'/../public/css/app.css')?filemtime(APPROOT.'/../public/css/app.css'):time();?>
<link rel="stylesheet" href="<?=URLROOT?>/public/css/app.css?v=<?=$v?>">
<link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root{--bg:#f2e4d4;--card:#fff;--rule:rgba(178,143,110,0.22);--rule-strong:rgba(178,143,110,0.45);--plum:#6b4459;--plum-dk:#4e3141;--plum-lt:#9b7289;--rose:#c27a8e;--gold:#b8924a;--muted:#a08878;--text:#1a1118;--text2:#5c4a54;--danger:#b94b4b;--r-sm:8px;--r-md:14px;--r-lg:20px;--font-d:'Playfair Display',Georgia,serif;--font-b:'Poppins',system-ui,sans-serif;--pad-x:clamp(20px,5vw,72px);--ease-expo:cubic-bezier(0.19,1,0.22,1);}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{background:var(--bg);color:var(--text);font-family:var(--font-b);font-size:14px;-webkit-font-smoothing:antialiased;min-height:100vh;display:flex;flex-direction:column}
a{color:inherit;text-decoration:none}
.gp-header{position:sticky;top:0;z-index:100;display:grid;grid-template-columns:auto 1fr auto;align-items:center;gap:24px;padding:0 var(--pad-x);height:68px;border-bottom:1px solid var(--rule);background:rgba(242,228,212,0.82);backdrop-filter:blur(24px) saturate(1.4)}
.gp-brand{display:flex;align-items:center;gap:12px;font-size:17px;font-weight:800}
.gp-brand-mark{display:grid;place-items:center;width:38px;height:38px;border-radius:50%;background:var(--plum);color:#fffaf3;font-size:13px;font-weight:700}
.gp-header-nav{display:flex;align-items:center;gap:2px}
.gp-header-nav a{padding:7px 16px;border-radius:999px;font-size:13px;font-weight:600;color:var(--text2);transition:all .22s}
.gp-header-nav a:hover{color:var(--plum);background:rgba(107,68,89,0.08)}
.gp-header-actions{display:flex;align-items:center;gap:10px}
.gp-btn-sm{display:inline-flex;align-items:center;gap:4px;padding:6px 14px;border-radius:999px;border:1px solid var(--rule-strong);font-size:11px;font-weight:600;color:var(--text2);transition:all .2s;text-decoration:none}
.gp-btn-sm:hover{border-color:var(--plum);color:var(--plum)}
.gp-btn-sm.primary{background:var(--plum);color:#fff;border-color:var(--plum)}
.gp-btn-sm.primary:hover{background:var(--plum-dk)}
.gp-btn-sm.danger{color:var(--danger);border-color:rgba(185,75,75,0.2)}
.gp-btn-sm.danger:hover{background:var(--danger);color:#fff}
.gp-profile-dropdown{position:relative}
.gp-profile-btn{display:flex;align-items:center;gap:8px;padding:4px 12px 4px 4px;border-radius:999px;border:1px solid var(--rule-strong);background:var(--card);cursor:pointer;transition:all .2s;color:var(--plum);font-family:var(--font-b);font-size:13px;font-weight:600}
.gp-profile-btn:hover{border-color:var(--plum);background:rgba(107,68,89,.06)}
.gp-profile-avatar{display:grid;place-items:center;width:32px;height:32px;border-radius:50%;background:var(--plum);color:#fffaf3;font-size:12px;font-weight:800;letter-spacing:.5px}
.gp-profile-name{white-space:nowrap;max-width:100px;overflow:hidden;text-overflow:ellipsis}
.gp-profile-chevron{opacity:.6;transition:transform .2s}
.gp-profile-btn[aria-expanded="true"] .gp-profile-chevron{transform:rotate(180deg)}
.gp-profile-menu{position:absolute;top:calc(100% + 8px);right:0;min-width:180px;padding:6px;border-radius:12px;border:1px solid var(--rule);background:var(--card);box-shadow:0 12px 35px rgba(15,23,42,.1);opacity:0;visibility:hidden;transform:translateY(-4px);transition:all .15s var(--ease-expo)}
.gp-profile-btn[aria-expanded="true"]+.gp-profile-menu,
.gp-profile-menu.show{opacity:1;visibility:visible;transform:translateY(0)}
.gp-profile-menu-item{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:8px;font-size:13px;font-weight:600;color:var(--text);transition:all .15s}
.gp-profile-menu-item:hover{background:rgba(107,68,89,.06)}
.gp-profile-menu-item--danger{color:var(--danger)}
.gp-profile-menu-item--danger:hover{background:rgba(185,75,75,.08)}

.gp-page{position:relative;z-index:1;flex:1;padding:40px var(--pad-x) 80px;max-width:1000px;margin:0 auto;width:100%}
.gp-back{margin-bottom:20px}
.gp-back a{display:inline-flex;align-items:center;gap:6px;font-size:12px;color:var(--muted);transition:color .2s}
.gp-back a:hover{color:var(--plum)}

.gp-head{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;margin-bottom:28px}
.gp-head h1{font-family:var(--font-d);font-size:28px;font-weight:600}
.gp-head em{font-style:italic;color:var(--plum-lt)}
.gp-badge{display:inline-flex;padding:4px 12px;border-radius:999px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.05em}

.gp-layout{display:grid;grid-template-columns:1fr 1fr;gap:20px}
.gp-col{display:flex;flex-direction:column;gap:16px}

.gp-card{background:var(--card);border:1px solid var(--rule);border-radius:var(--r-lg);overflow:hidden}
.gp-card-h{padding:14px 18px;border-bottom:1px solid var(--rule);font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--gold)}
.gp-card-b{padding:14px 18px;display:flex;flex-direction:column;gap:8px}
.gp-field{display:flex;flex-direction:column;gap:0}
.gp-field-l{font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--muted)}
.gp-field-v{font-size:13px;color:var(--text)}
.gp-field-v.quote{font-style:italic;color:var(--text2);padding:8px 12px;background:rgba(107,68,89,0.04);border-radius:var(--r-sm);border-left:3px solid var(--plum-lt)}

.gp-timeline{display:flex;flex-direction:column;gap:0}
.gp-tl-item{display:flex;gap:12px;padding:10px 0;position:relative}
.gp-tl-item:not(:last-child)::after{content:'';position:absolute;left:12px;top:30px;bottom:-4px;width:2px;background:var(--rule)}
.gp-tl-dot{width:26px;height:26px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:10px;flex-shrink:0}
.gp-tl-dot.past{background:var(--plum);color:#fff}
.gp-tl-dot.current{background:var(--rose);color:#fff;animation:pulse 2s ease-in-out infinite}
.gp-tl-dot.future{background:rgba(107,68,89,0.1);color:var(--muted)}
.gp-tl-c{padding-top:2px}
.gp-tl-t{font-size:13px;font-weight:500;color:var(--text)}
.gp-tl-s{font-size:11px;color:var(--muted)}
@keyframes pulse{0%,100%{transform:scale(1)}50%{transform:scale(1.08)}}

.gp-items-table{width:100%;border-collapse:collapse}
.gp-items-table th{text-align:left;font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--muted);padding:6px 8px;border-bottom:1px solid var(--rule)}
.gp-items-table td{padding:10px 8px;font-size:13px;border-bottom:1px solid var(--rule)}
.gp-items-table tr:last-child td{border-bottom:none}
.gp-item-name{font-family:var(--font-d);font-weight:600}
.gp-item-detail{font-size:11px;color:var(--muted);display:flex;align-items:center;gap:4px;margin-top:2px}
.gp-item-status{font-size:10px;font-weight:700;padding:2px 8px;border-radius:999px;white-space:nowrap}

.gp-summary{padding:14px 18px;display:flex;flex-direction:column;gap:6px}
.gp-summary-r{display:flex;justify-content:space-between;font-size:12px;color:var(--text2)}
.gp-summary-r.total{font-size:16px;font-weight:700;color:var(--plum);font-family:var(--font-d);padding-top:10px;border-top:1px solid var(--rule-strong)}

.gp-bottom-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:20px}

.gp-review-section{background:var(--card);border:1px solid var(--rule);border-radius:var(--r-lg);overflow:hidden;margin-top:24px}
.gp-review-section-h{padding:14px 18px;border-bottom:1px solid var(--rule);font-size:10px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--gold)}
.gp-review-section-b{padding:18px}
.gp-star-picker{display:flex;gap:6px;margin-bottom:12px}
.gp-star-btn{background:none;border:none;cursor:pointer;font-size:26px;color:var(--rule-strong);padding:0;line-height:1;transition:color .15s}
.gp-star-btn.active,.gp-star-btn:hover,.gp-star-btn.hover{color:#d6a72d}
.gp-review-textarea{width:100%;padding:10px 12px;border-radius:var(--r-sm);border:1px solid var(--rule-strong);background:#faf7f2;font-family:var(--font-b);font-size:13px;color:var(--text);resize:vertical;min-height:90px;outline:none;transition:border-color .2s}
.gp-review-textarea:focus{border-color:var(--plum)}
.gp-review-chars{font-size:10px;color:var(--muted);text-align:right;margin-top:3px}
.gp-review-existing{display:flex;flex-direction:column;gap:8px}
.gp-review-stars{color:#d6a72d;font-size:18px;letter-spacing:-1px}
.gp-review-body{font-size:13px;color:var(--text2);line-height:1.6}
.gp-review-meta{font-size:11px;color:var(--muted)}
.gp-review-actions{display:flex;gap:8px;margin-top:6px}
.gp-flash-success{background:#e8f5e9;color:#2e7d32;border:1px solid #a5d6a7;border-radius:var(--r-sm);padding:10px 14px;font-size:13px;margin-bottom:16px}
.gp-flash-error{background:#fdecea;color:#c62828;border:1px solid #ef9a9a;border-radius:var(--r-sm);padding:10px 14px;font-size:13px;margin-bottom:16px}
.gp-edit-form{margin-top:12px;display:none}

@media(max-width:768px){.gp-layout{grid-template-columns:1fr}}
@media(max-width:640px){.gp-header-nav{display:none}:root{--pad-x:16px}}
</style>
</head><body>

<header class="gp-header">
  <a class="gp-brand" href="<?=URLROOT?>/main/index"><span class="gp-brand-mark">G</span>Golden Promise</a>
  <nav class="gp-header-nav"><a href="<?=URLROOT?>/main/index">Home</a><a href="<?=URLROOT?>/booking/myBookings">Bookings</a><a href="<?=URLROOT?>/review/my">My Reviews</a></nav>
  <div class="gp-header-actions">
    <?php require APPROOT . '/views/dashboardLayout/customerNotification.php'; ?>
    <div class="gp-profile-dropdown">
      <button class="gp-profile-btn" type="button" aria-expanded="false">
        <span class="gp-profile-avatar"><?= strtoupper(substr($_SESSION['session_name'] ?? 'U', 0, 1)) ?></span>
        <span class="gp-profile-name"><?= htmlspecialchars(explode(' ', $_SESSION['session_name'] ?? 'User')[0], ENT_QUOTES, 'UTF-8') ?></span>
        <svg class="gp-profile-chevron" width="10" height="6" viewBox="0 0 10 6" fill="none"><path d="M1 1l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </button>
      <div class="gp-profile-menu" aria-hidden="true">
        <a class="gp-profile-menu-item" href="<?=URLROOT?>/booking/myBookings">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
          My Bookings
        </a>
        <a class="gp-profile-menu-item" href="<?=URLROOT?>/review/my">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          My Reviews
        </a>
        <a class="gp-profile-menu-item gp-profile-menu-item--danger" href="<?=URLROOT?>/users/logout">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
          Logout
        </a>
      </div>
    </div>
  </div>
</header>

<main class="gp-page">
  <div class="gp-back"><a href="<?=URLROOT?>/booking/myBookings"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg> Back to Bookings</a></div>

  <div class="gp-head">
    <h1>Booking <?= $h($bookingRef) ?></h1>
    <span class="gp-badge <?php
      $s=$booking['status']??'';
      if(in_array($s,['confirmed','completed'])) echo 'background:#f0fdf4;color:#166534';
      elseif($s==='paid') echo 'background:#eff6ff;color:#1d4ed8';
      elseif($s==='cancelled') echo 'background:#fef2f2;color:var(--danger)';
      else echo 'background:#fffbeb;color:#92400e';
    ?>"><?=$statusLabels[$s]??ucfirst($s)?></span>
  </div>

  <?php $currentStatus = $booking['status'] ?? ''; ?>

  <?php if ($currentStatus === 'pending_supplier_response'): ?>
  <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:12px;padding:16px 20px;display:flex;align-items:flex-start;gap:14px;margin-bottom:4px;">
    <svg style="flex-shrink:0;margin-top:2px;color:#d97706" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <div>
      <div style="font-weight:700;font-size:13px;color:#92400e;margin-bottom:2px;">Waiting for supplier confirmation</div>
      <div style="font-size:12px;color:#b45309;">Your booking request has been sent. The supplier has up to 48 hours to accept or decline. You will be notified when they respond.</div>
    </div>
  </div>
  <?php endif; ?>

  <?php if ($currentStatus === 'pending_payment' && ($booking['payment_status'] ?? '') === 'unpaid'): ?>
  <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;padding:16px 20px;display:flex;align-items:flex-start;gap:14px;margin-bottom:4px;">
    <svg style="flex-shrink:0;margin-top:2px;color:#16a34a" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
    <div>
      <div style="font-weight:700;font-size:13px;color:#166534;margin-bottom:2px;">Supplier accepted — please complete payment</div>
      <div style="font-size:12px;color:#15803d;">Your booking request was accepted! Please pay your 10% deposit below to lock in your booking.</div>
    </div>
  </div>
  <?php endif; ?>

  <div class="gp-layout">
    <!-- LEFT: Timeline -->
    <div class="gp-col">
      <div class="gp-card">
        <div class="gp-card-h">Status Timeline</div>
        <div class="gp-card-b">
          <?php if (!empty($logs)): ?>
          <div class="gp-timeline">
            <?php foreach ($logs as $l):
              $isLast = $l === end($logs);
              $isCurrent = $l['new_status'] === ($booking['status']??'');
            ?>
            <div class="gp-tl-item">
              <div class="gp-tl-dot <?=$isCurrent?'current':($isLast?'past':'past')?>">
                <?php if ($isCurrent): ?>●<?php else: ?>●<?php endif; ?>
              </div>
              <div class="gp-tl-c">
                <div class="gp-tl-t"><?=ucfirst(str_replace('_',' ',$l['new_status']??''))?></div>
                <div class="gp-tl-s"><?=date('d M, g:i A',strtotime($l['created_at']))?><?=$l['note']?' — '.$h($l['note']):''?></div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <?php else: ?>
            <div style="color:var(--muted);font-size:13px;padding:8px 0;">No status updates yet</div>
          <?php endif; ?>
        </div>
      </div>

      <div class="gp-card">
        <div class="gp-card-h">Event Details</div>
        <div class="gp-card-b">
          <?php foreach ($eventDetails as $ed): ?>
          <div class="gp-field">
            <span class="gp-field-l">Date</span>
            <span class="gp-field-v"><?=$h(date('l, d M Y',strtotime($ed['event_date']?:'now')))?></span>
          </div>
          <div class="gp-field">
            <span class="gp-field-l">Time</span>
            <span class="gp-field-v"><?=$h(date('g:i A',strtotime($ed['start_time']?:'09:00')))?> — <?=$h(date('g:i A',strtotime($ed['end_time']?:'17:00')))?></span>
          </div>
          <?php if (!empty($ed['guest_count'])): ?>
          <div class="gp-field"><span class="gp-field-l">Guests</span><span class="gp-field-v"><?=(int)$ed['guest_count']?></span></div>
          <?php endif; ?>
          <?php if (!empty($ed['location'])): ?>
          <div class="gp-field"><span class="gp-field-l">Venue</span><span class="gp-field-v"><?=$h($ed['location'])?></span></div>
          <?php endif; ?>
          <?php if (!empty($ed['contact_phone'])): ?>
          <div class="gp-field"><span class="gp-field-l">Contact Phone</span><span class="gp-field-v"><?=$h($ed['contact_phone'])?></span></div>
          <?php endif; ?>
          <?php if (!empty($ed['special_requests'])): ?>
          <div class="gp-field">
            <span class="gp-field-l">Special Requests</span>
            <span class="gp-field-v quote">"<?=$h($ed['special_requests'])?>"</span>
          </div>
          <?php endif; ?>
          <?php endforeach; ?>
          <?php if (empty($eventDetails)): ?>
          <div style="color:var(--muted);font-size:13px;">No event details provided</div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- RIGHT: Items -->
    <div class="gp-col">
      <div class="gp-card">
        <div class="gp-card-h">Services</div>
        <table class="gp-items-table">
          <thead><tr><th>Service</th><th>Status</th><th>Amount</th></tr></thead>
          <tbody>
            <?php foreach ($items as $item):
              $p=(float)($item['price']??0);
              $hallName = trim((string)($item['venue_room_name'] ?? ''));
              $venueName = trim((string)($item['venue_name'] ?? ''));
            ?>
            <tr>
              <td>
                <div class="gp-item-name"><?=$h($item['service_name']??'Service')?></div>
                <?php if ($hallName !== ''): ?>
                <div class="gp-item-detail">Hall: <?=$h($hallName . ($venueName !== '' ? ' · ' . $venueName : ''))?></div>
                <?php endif; ?>
                <div class="gp-item-detail">
                  <?php if ($item['start_time']??''): ?>
                  <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                  <?=$h(date('g:i A',strtotime($item['start_time'])))?>
                  <?php endif; ?>
                  <?php if ($item['supplier_name']??''): ?>
                  &middot; <?=$h($item['supplier_name'])?>
                  <?php endif; ?>
                </div>
              </td>
              <td><span class="gp-item-status" style="<?=($item['status']??'')==='accepted'?'background:#f0fdf4;color:#166534':(($item['status']??'')==='cancelled'?'background:#fef2f2;color:var(--danger)':'background:#fffbeb;color:#92400e')?>"><?=ucfirst($item['status']??'pending')?></span></td>
              <td style="font-weight:600"><?=$money($p)?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <div class="gp-summary">
          <div class="gp-summary-r"><span>Total</span><span><?=$money((float)($booking['total_amount']??0))?></span></div>
          <div class="gp-summary-r" style="color:var(--plum);font-weight:600;"><span>Deposit paid (<?=$depositPercent?>%)</span><span><?=$money((float)($booking['paid_amount']??0))?></span></div>
          <div class="gp-summary-r balance" style="color:var(--muted);font-size:12px;"><span>Balance due</span><span><?=$money(max(0,(float)($booking['total_amount']??0)-(float)($booking['paid_amount']??0)))?></span></div>
        </div>
      </div>

      <?php if (!empty($vouchers)): ?>
      <div class="gp-card">
        <div class="gp-card-h">Vouchers</div>
        <div class="gp-card-b" style="gap:8px;">
          <?php foreach ($vouchers as $vc): ?>
          <div style="display:flex;justify-content:space-between;align-items:center;padding:6px 0;border-bottom:1px solid var(--rule);font-size:12px;">
            <span style="font-weight:500;"><?=$h($vc['service_name'])?></span>
            <span style="font-family:monospace;font-size:11px;color:var(--muted);"><?=$h($vc['voucher_number'])?></span>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <?php $reviewFlashSuccess = $_SESSION['review_success'] ?? null; $reviewFlashError = $_SESSION['review_error'] ?? null; unset($_SESSION['review_success'], $_SESSION['review_error']); ?>
  <?php if ($canReview || $existingReview): ?>
  <div class="gp-review-section">
    <div class="gp-review-section-h">Your Review</div>
    <div class="gp-review-section-b">
      <?php if ($reviewFlashSuccess): ?>
        <div class="gp-flash-success"><?= $h($reviewFlashSuccess) ?></div>
      <?php elseif ($reviewFlashError): ?>
        <div class="gp-flash-error"><?= $h($reviewFlashError) ?></div>
      <?php endif; ?>

      <?php if ($existingReview): ?>
        <div class="gp-review-existing" id="reviewDisplay">
          <div class="gp-review-stars"><?= str_repeat('★', (int)($existingReview['rating'] ?? 0)) . str_repeat('☆', 5 - (int)($existingReview['rating'] ?? 0)) ?></div>
          <div class="gp-review-body" id="reviewCommentDisplay"><?= $h((string)($existingReview['comment'] ?? '')) ?></div>
          <div class="gp-review-actions">
            <?php if ($canEditReview): ?>
              <button class="gp-btn-sm" type="button" onclick="toggleEditReview()">Edit</button>
            <?php endif; ?>
            <form method="POST" action="<?=URLROOT?>/review/delete/<?=(int)$existingReview['id']?>" style="display:inline" onsubmit="return confirm('Remove your review?')">
              <input type="hidden" name="booking_id" value="<?=(int)($booking['id']??0)?>">
              <button class="gp-btn-sm danger" type="submit">Delete</button>
            </form>
          </div>
        </div>
        <?php if ($canEditReview): ?>
        <div class="gp-edit-form" id="editReviewForm">
          <div class="gp-star-picker" id="editStarPicker" data-value="<?=(int)($existingReview['rating']??5)?>">
            <?php for ($s = 1; $s <= 5; $s++): ?>
              <button class="gp-star-btn<?= $s <= (int)($existingReview['rating']??5) ? ' active' : '' ?>" type="button" data-val="<?=$s?>" onclick="setEditStar(<?=$s?>)">★</button>
            <?php endfor; ?>
          </div>
          <textarea class="gp-review-textarea" id="editCommentInput" maxlength="2000" oninput="updateCharCount('editCommentCount',this.value,2000)"><?= $h((string)($existingReview['comment'] ?? '')) ?></textarea>
          <div class="gp-review-chars"><span id="editCommentCount"><?=strlen($existingReview['comment']??'')?></span> / 2000</div>
          <div style="display:flex;gap:8px;margin-top:10px">
            <button class="gp-btn-sm primary" type="button" onclick="submitEditReview(<?=(int)$existingReview['id']?>)">Save</button>
            <button class="gp-btn-sm" type="button" onclick="toggleEditReview()">Cancel</button>
          </div>
        </div>
        <?php endif; ?>

      <?php elseif ($canReview): ?>
        <form method="POST" action="<?=URLROOT?>/review/submit/<?=(int)($booking['id']??0)?>">
          <p style="font-size:13px;color:var(--text2);margin-bottom:12px">How was your experience with this booking?</p>
          <div class="gp-star-picker" id="submitStarPicker">
            <?php for ($s = 1; $s <= 5; $s++): ?>
              <button class="gp-star-btn" type="button" data-val="<?=$s?>" onclick="setSubmitStar(<?=$s?>)">★</button>
            <?php endfor; ?>
          </div>
          <input type="hidden" name="rating" id="submitRatingInput" value="">
          <textarea class="gp-review-textarea" name="comment" placeholder="Share your experience (min 10 characters)…" maxlength="2000" oninput="updateCharCount('submitCommentCount',this.value,2000)"></textarea>
          <div class="gp-review-chars"><span id="submitCommentCount">0</span> / 2000</div>
          <div style="margin-top:12px">
            <button class="gp-btn-sm primary" type="submit" onclick="return validateReviewForm()">Submit Review</button>
          </div>
        </form>
      <?php endif; ?>
    </div>
  </div>
  <?php elseif ($reviewFlashSuccess || $reviewFlashError): ?>
    <?php if ($reviewFlashSuccess): ?>
      <div class="gp-flash-success" style="margin-top:16px"><?= $h($reviewFlashSuccess) ?></div>
    <?php elseif ($reviewFlashError): ?>
      <div class="gp-flash-error" style="margin-top:16px"><?= $h($reviewFlashError) ?></div>
    <?php endif; ?>
  <?php endif; ?>

  <div class="gp-bottom-actions">
    <a class="gp-btn-sm" href="<?=URLROOT?>/booking/myBookings">Back to Bookings</a>
    <?php if (!empty($vouchers)): ?>
    <a class="gp-btn-sm" href="<?=URLROOT?>/booking/vouchers">View All Vouchers</a>
    <?php endif; ?>
    <?php if (in_array($booking['status']??'', ['draft', 'pending_payment']) && ($booking['payment_status'] ?? '') === 'unpaid'): ?>
    <a class="gp-btn-sm primary" href="<?=URLROOT?>/booking/pay/<?=(int)($booking['id']??0)?>">Proceed to Payment</a>
    <?php elseif (($booking['status']??'') === 'pending_supplier_response'): ?>
    <span class="gp-btn-sm" style="cursor:default;opacity:0.6;" title="Payment available after supplier confirms">Awaiting Supplier Response</span>
    <?php endif; ?>
    <?php if (!in_array($booking['status']??'', ['cancelled','completed'])): ?>
    <a class="gp-btn-sm danger" href="<?=URLROOT?>/booking/cancel/<?=(int)($booking['id']??0)?>">Request Cancellation</a>
    <?php endif; ?>
  </div>
</main>
<script>
document.addEventListener('click',(e)=>{const btn=e.target.closest('.gp-profile-btn');if(btn){const x=btn.getAttribute('aria-expanded')==='true';document.querySelectorAll('.gp-profile-btn').forEach(b=>b.setAttribute('aria-expanded','false'));btn.setAttribute('aria-expanded',String(!x));return}document.querySelectorAll('.gp-profile-btn').forEach(b=>b.setAttribute('aria-expanded','false'))});

function updateCharCount(id, val, max) {
  const el = document.getElementById(id);
  if (el) el.textContent = val.length;
}
function setSubmitStar(val) {
  document.getElementById('submitRatingInput').value = val;
  document.querySelectorAll('#submitStarPicker .gp-star-btn').forEach(b => {
    b.classList.toggle('active', parseInt(b.dataset.val) <= val);
  });
}
function setEditStar(val) {
  const picker = document.getElementById('editStarPicker');
  if (picker) picker.dataset.value = val;
  document.querySelectorAll('#editStarPicker .gp-star-btn').forEach(b => {
    b.classList.toggle('active', parseInt(b.dataset.val) <= val);
  });
}
function validateReviewForm() {
  const rating = document.getElementById('submitRatingInput').value;
  if (!rating) { alert('Please select a star rating.'); return false; }
  return true;
}
function toggleEditReview() {
  const form = document.getElementById('editReviewForm');
  const display = document.getElementById('reviewDisplay');
  if (!form) return;
  const showing = form.style.display === 'block';
  form.style.display = showing ? 'none' : 'block';
  if (display) display.style.display = showing ? 'flex' : 'none';
}
function submitEditReview(reviewId) {
  const picker = document.getElementById('editStarPicker');
  const rating = picker ? parseInt(picker.dataset.value) : 0;
  const comment = (document.getElementById('editCommentInput')?.value || '').trim();
  if (!rating || rating < 1 || rating > 5) { alert('Please select a rating.'); return; }
  if (comment.length < 10) { alert('Comment must be at least 10 characters.'); return; }
  fetch('<?=URLROOT?>/review/update/' + reviewId, {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({rating, comment})
  }).then(r => r.json()).then(d => {
    if (d.status === 'success') {
      document.getElementById('reviewCommentDisplay').textContent = comment;
      document.querySelectorAll('.gp-review-stars')[0].textContent = '★'.repeat(rating) + '☆'.repeat(5 - rating);
      toggleEditReview();
    } else {
      alert(d.error || 'Could not update review.');
    }
  }).catch(() => alert('Network error. Please try again.'));
}
</script>
</body></html>
