<?php
$vouchers = $vouchers ?? [];
$activeFilter = $activeFilter ?? 'all';
$voucherStatusCounts = $voucherStatusCounts ?? ['all' => 0, 'active' => 0, 'used' => 0, 'expired' => 0];

$filterLabels = ['all' => 'All', 'active' => 'Active', 'used' => 'Used', 'expired' => 'Expired'];
$money = fn($v) => number_format((float)$v, 0) . ' MMK';
$plain = function($v){ $t=(string)$v; for($i=0;$i<10;$i++){$d=html_entity_decode($t,ENT_QUOTES|ENT_HTML5,'UTF-8');if($d===$t)break;$t=$d;}return $t; };
$h = fn($v)=>htmlspecialchars($plain($v),ENT_QUOTES,'UTF-8');
$formatDate = fn($date) => $date ? date('d M Y', strtotime($date)) : '';
$formatTime = fn($time) => $time ? date('g:i A', strtotime($time)) : '';
$imageUrl = function ($path) {
    $path = trim((string)$path);
    if ($path === '') {
        return '';
    }
    if (preg_match('~^(https?:)?//~i', $path) || str_starts_with($path, '/')) {
        return $path;
    }
    return URLROOT . '/' . ltrim($path, '/');
};
?>
<!DOCTYPE html>
<html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<link rel="icon" type="image/png" href="<?= URLROOT ?>/public/images/home/gp_logo.png">
<title>My Vouchers — Golden Promise</title>
<?php $v=file_exists(APPROOT.'/../public/css/app.css')?filemtime(APPROOT.'/../public/css/app.css'):time();?>
<link rel="stylesheet" href="<?=URLROOT?>/public/css/app.css?v=<?=$v?>">
<link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root{--bg:#ead8c8;--card:#e6ddd8;--rule:rgba(8,29,43,0.18);--rule-strong:rgba(8,29,43,0.28);--plum:#6b4459;--plum-lt:#9b7289;--rose:#c27a8e;--gold:#d7ad45;--gold-hi:#ffe886;--muted:#756a68;--text:#101010;--text2:#423c3b;--ticket-navy:#6b4459;--ticket-blue:#aebdc0;--ticket-cream:#d8d0cb;--ticket-ink:#071b2a;--ticket-paper:#f3eee8;--r-sm:8px;--r-md:14px;--r-lg:20px;--r-xl:24px;--font-d:'Playfair Display',Georgia,serif;--font-b:'Poppins',system-ui,sans-serif;--pad-x:clamp(20px,5vw,72px);--ease-expo:cubic-bezier(0.19,1,0.22,1);}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{background:var(--bg);color:var(--text);font-family:var(--font-b);font-size:14px;-webkit-font-smoothing:antialiased;min-height:100vh;display:flex;flex-direction:column;overflow-x:hidden}
a{color:inherit;text-decoration:none}

.gp-header{position:sticky;top:0;z-index:100;display:grid;grid-template-columns:auto 1fr auto;align-items:center;gap:24px;padding:0 var(--pad-x);height:68px;border-bottom:1px solid var(--rule);background:rgba(170,154,153,0.82);backdrop-filter:blur(24px) saturate(1.4)}
.gp-brand{display:flex;align-items:center;gap:12px;font-size:17px;font-weight:800}
.gp-brand-mark{display:grid;place-items:center;width:38px;height:38px;border-radius:50%;background:var(--plum);color:#fffaf3;font-size:13px;font-weight:700}
.gp-btn-sm{display:inline-flex;align-items:center;gap:4px;padding:6px 14px;border-radius:999px;border:1px solid var(--rule-strong);font-size:11px;font-weight:600;color:var(--text2);transition:border-color .2s,color .2s,background .2s}
.gp-btn-sm:hover{border-color:var(--plum);color:var(--plum)}
.gp-btn-sm.primary{background:var(--plum);color:#fcf8f5;border-color:var(--plum)}
.gp-profile-dropdown{position:relative}
.gp-profile-btn{display:flex;align-items:center;gap:8px;padding:4px 12px 4px 4px;border-radius:999px;border:1px solid var(--rule-strong);background:var(--card);cursor:pointer;transition:border-color .2s,background .2s;color:var(--plum);font-family:var(--font-b);font-size:13px;font-weight:600}
.gp-profile-btn:hover{border-color:var(--plum);background:rgba(107,68,89,.06)}
.gp-profile-avatar{display:grid;place-items:center;width:32px;height:32px;border-radius:50%;background:var(--plum);color:#fffaf3;font-size:12px;font-weight:800;letter-spacing:.5px}
.gp-profile-name{white-space:nowrap;max-width:100px;overflow:hidden;text-overflow:ellipsis}
.gp-profile-chevron{opacity:.6;transition:transform .2s}
.gp-profile-btn[aria-expanded="true"] .gp-profile-chevron{transform:rotate(180deg)}
.gp-profile-menu{position:absolute;top:calc(100% + 8px);right:0;min-width:180px;padding:6px;border-radius:12px;border:1px solid var(--rule);background:var(--card);box-shadow:0 12px 35px rgba(15,23,42,.1);opacity:0;visibility:hidden;transform:translateY(-4px);transition:opacity .15s var(--ease-expo),visibility .15s var(--ease-expo),transform .15s var(--ease-expo)}
.gp-profile-btn[aria-expanded="true"]+.gp-profile-menu,
.gp-profile-menu.show{opacity:1;visibility:visible;transform:translateY(0)}
.gp-profile-menu-item{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:8px;font-size:13px;font-weight:600;color:var(--text);transition:background .15s}
.gp-profile-menu-item:hover{background:rgba(107,68,89,.06)}
.gp-profile-menu-item--danger{color:var(--danger)}
.gp-profile-menu-item--danger:hover{background:rgba(185,75,75,.08)}

.gp-page{position:relative;z-index:1;flex:1;padding:52px var(--pad-x) 80px;max-width:1220px;margin:0 auto;width:100%}
.gp-page-head{margin-bottom:32px;opacity:0;animation:fadeUp .7s var(--ease-expo) .1s forwards}
.gp-page-eyebrow{font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#fffaf3;margin-bottom:8px}
.gp-page-title{font-family:var(--font-d);font-size:clamp(34px,5vw,52px);font-weight:600;line-height:.92}
.gp-page-title em{font-style:italic;color:#f3eee8}
.gp-page-subtitle{font-size:14px;color:rgba(16,16,16,.58);margin-top:8px}

.gp-filters{display:flex;gap:6px;margin-bottom:28px}
.gp-filter{padding:6px 16px;border-radius:999px;border:1px solid rgba(243,238,232,.52);font-size:12px;font-weight:700;color:#241f1f;transition:border-color .2s,color .2s,background .2s;white-space:nowrap}
.gp-filter:hover{border-color:#fffaf3;color:#071b2a;background:rgba(255,250,243,.16)}
.gp-filter.active{background:var(--plum);color:#f3eee8;border-color:var(--plum)}

.gp-grid{display:grid;grid-template-columns:1fr;gap:20px}
.gp-voucher{position:relative;min-height:310px;opacity:0;transform:translateY(20px);transition:opacity .5s var(--ease-expo),transform .5s var(--ease-expo);perspective:1600px}
.gp-voucher[data-voucher-toggle]{cursor:pointer}
.gp-voucher.visible{opacity:1;transform:translateY(0)}
.gp-voucher:focus-visible{outline:3px solid #fffaf3;outline-offset:6px}
.gp-voucher.used,.gp-voucher.expired{opacity:.78}
.gp-voucher-shell{position:relative;min-height:310px;transform-style:preserve-3d;transition:transform .72s var(--ease-expo)}
.gp-voucher.is-flipped .gp-voucher-shell{transform:rotateY(180deg)}
.gp-voucher-face{position:absolute;inset:0;display:grid;grid-template-columns:minmax(0,1fr) 168px;border:0;border-radius:0;overflow:hidden;backface-visibility:hidden;box-shadow:0 16px 34px rgba(40,29,28,.14)}
.gp-voucher-face::before,.gp-voucher-face::after{content:'';position:absolute;z-index:5;width:30px;height:30px;border-radius:50%;background:var(--bg);pointer-events:none}
.gp-voucher-face::before{left:-18px;top:38px;box-shadow:0 26px 0 var(--bg),0 52px 0 var(--bg),0 78px 0 var(--bg),0 104px 0 var(--bg),0 130px 0 var(--bg),0 156px 0 var(--bg),0 182px 0 var(--bg),0 208px 0 var(--bg)}
.gp-voucher-face::after{right:-18px;top:38px;box-shadow:0 26px 0 var(--bg),0 52px 0 var(--bg),0 78px 0 var(--bg),0 104px 0 var(--bg),0 130px 0 var(--bg),0 156px 0 var(--bg),0 182px 0 var(--bg),0 208px 0 var(--bg)}
.gp-voucher:nth-of-type(3n+1) .gp-voucher-face{--ticket-bg:var(--ticket-navy);--ticket-fg:#f3eee8;--ticket-soft:rgba(243,238,232,.70);--ticket-faint:rgba(243,238,232,.38);--stub-bg:var(--ticket-navy);--stub-fg:#f3eee8}
.gp-voucher:nth-of-type(3n+2) .gp-voucher-face{--ticket-bg:var(--ticket-blue);--ticket-fg:#071b2a;--ticket-soft:rgba(7,27,42,.72);--ticket-faint:rgba(7,27,42,.42);--stub-bg:var(--ticket-blue);--stub-fg:#071b2a}
.gp-voucher:nth-of-type(3n) .gp-voucher-face{--ticket-bg:var(--ticket-cream);--ticket-fg:#071b2a;--ticket-soft:rgba(7,27,42,.72);--ticket-faint:rgba(7,27,42,.42);--stub-bg:var(--ticket-cream);--stub-fg:#071b2a}
.gp-voucher-back{transform:rotateY(180deg);background:var(--card)}
.gp-ticket-main{position:relative;min-height:310px;display:grid;grid-template-columns:minmax(0,1fr) 330px;gap:42px;align-items:center;padding:42px 42px 42px 54px;overflow:hidden;background:var(--ticket-bg);color:var(--ticket-fg)}
.gp-ticket-main::before{content:'';position:absolute;left:0;top:0;width:74px;height:74px;border-right:0;border-bottom:0;border-radius:0 0 52px 0;pointer-events:none}
.gp-ticket-main::after{content:'';position:absolute;right:-2px;top:0;bottom:0;width:4px;background:transparent;border-right:4px dashed var(--ticket-paper);pointer-events:none}
.gp-ticket-brand{position:absolute;right:26px;top:16px;z-index:1;color:var(--ticket-soft);font-family:var(--font-d);font-size:13px;line-height:1}
.gp-ticket-brand span:first-child{font-size:13px;font-weight:400;letter-spacing:0;text-transform:none;color:inherit}
.gp-ticket-mark{display:none}
.gp-ticket-title{position:relative;z-index:1;min-width:0}
.gp-ticket-title span{display:none}
.gp-ticket-title .gp-ticket-service{display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;font-family:var(--font-d);font-size:clamp(38px,5.2vw,62px);font-weight:500;line-height:.95;letter-spacing:0;text-transform:none;color:var(--ticket-fg)}
.gp-ticket-title .gp-ticket-supplier{display:block;margin-top:10px;font-family:var(--font-d);font-size:17px;line-height:1.1;color:var(--ticket-soft)}
.gp-ticket-title .gp-ticket-time{display:block;margin-top:18px;font-family:var(--font-d);font-size:13px;line-height:1.2;color:var(--ticket-soft)}
.gp-ticket-art{position:relative;z-index:1;align-self:center;justify-self:stretch;height:210px;background:rgba(255,255,255,.14);overflow:hidden}
.gp-ticket-art img{display:block;width:100%;height:100%;object-fit:cover}
.gp-ticket-art.has-image::before,.gp-ticket-art.has-image::after{display:none}
.gp-ticket-art::before{content:'GP';position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);font-family:var(--font-d);font-size:72px;color:var(--ticket-faint)}
.gp-ticket-art::after{content:'';position:absolute;left:18%;right:18%;bottom:24px;height:42px;border-radius:50% 50% 8px 8px;border:2px solid var(--ticket-faint);border-top:0}
.gp-ticket-foot{position:absolute;left:42px;right:22px;bottom:12px;z-index:1;display:flex;align-items:end;justify-content:space-between;gap:22px}
.gp-ticket-code{display:none}
.gp-ticket-qr{display:grid;place-items:center;width:112px;height:112px;padding:0;border:0;border-radius:0;background:transparent;box-shadow:none}
.gp-ticket-qr img{display:block;width:100%;height:100%;object-fit:cover;border-radius:6px}
.gp-ticket-noqr{display:grid;place-items:center;width:112px;height:112px;border:2px solid currentColor;border-radius:0;color:var(--stub-fg);font-size:10px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;text-align:center}
.gp-ticket-stub{position:relative;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:12px;padding:18px 16px;background:var(--stub-bg);border-left:4px dashed var(--ticket-paper);color:var(--stub-fg)}
.gp-ticket-stub::before,.gp-ticket-stub::after{content:'';position:absolute;left:-20px;width:38px;height:38px;border-radius:50%;background:var(--bg)}
.gp-ticket-stub::before{top:-22px}.gp-ticket-stub::after{bottom:-22px}
.gp-stub-action{display:grid;gap:8px;justify-items:center;width:100%}
.gp-stub-code{font-family:monospace;font-size:10px;letter-spacing:.06em;writing-mode:vertical-rl;text-orientation:mixed;color:currentColor}
.gp-ticket-hint{display:none}
.gp-back-main{display:grid;grid-template-rows:auto 1fr auto;gap:18px;padding:24px 30px;background:var(--ticket-bg);color:var(--ticket-fg)}
.gp-back-head{display:flex;align-items:flex-start;justify-content:space-between;gap:18px;padding-bottom:14px;border-bottom:1px solid rgba(178,143,110,.22)}
.gp-back-kicker{font-size:10px;font-weight:800;letter-spacing:.16em;text-transform:uppercase;color:var(--ticket-soft)}
.gp-back-title{margin-top:3px;font-family:var(--font-d);font-size:clamp(24px,3vw,34px);font-weight:600;line-height:1.05;color:var(--ticket-fg)}
.gp-back-code{font-family:monospace;font-size:11px;color:var(--ticket-fg);background:rgba(255,255,255,.16);padding:6px 9px;border-radius:0;white-space:nowrap}
.gp-back-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px 24px;align-content:start}
.gp-back-row{min-width:0}
.gp-back-label{display:block;margin-bottom:3px;font-size:10px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:var(--ticket-soft)}
.gp-back-value{display:block;font-size:13px;font-weight:600;line-height:1.35;color:var(--ticket-fg);overflow-wrap:anywhere}
.gp-back-value.feature{font-family:var(--font-d);font-size:20px;font-weight:600;color:var(--ticket-fg)}
.gp-back-foot{display:flex;align-items:center;justify-content:space-between;gap:14px;padding-top:12px;border-top:1px solid rgba(178,143,110,.22)}
.gp-back-note{font-size:11px;line-height:1.45;color:var(--ticket-soft)}
.gp-back-stub{display:flex;flex-direction:column;justify-content:space-between;gap:16px;padding:20px 16px;border-left:4px dashed var(--ticket-paper);background:var(--stub-bg);color:var(--stub-fg)}
.gp-back-verify{display:grid;gap:10px}
.gp-back-qr{display:grid;place-items:center;min-height:106px;padding:0;border:0;border-radius:0;background:transparent;color:inherit}
.gp-back-qr img{display:block;width:92px;height:92px;border-radius:0}
.gp-back-qr span{font-size:11px;font-weight:800;color:currentColor;text-transform:uppercase;text-align:center}
.gp-voucher.used::after,.gp-voucher.expired::after{content:attr(data-stamp);position:absolute;top:24px;right:-30px;z-index:4;transform:rotate(38deg);font-size:11px;font-weight:800;letter-spacing:1px;text-transform:uppercase;padding:4px 38px;border:2px solid;color:var(--plum);background:rgba(255,250,243,.88)}

.gp-empty{text-align:center;padding:80px 24px;grid-column:1/-1;border:1px dashed rgba(107,68,89,0.18);border-radius:var(--r-xl);background:var(--card)}
.gp-empty h2{font-family:var(--font-d);font-size:28px;margin-bottom:8px}
.gp-empty p{color:var(--muted);font-size:14px;margin-bottom:20px}

@keyframes fadeUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
@media(max-width:900px){
  .gp-voucher,.gp-voucher-shell{min-height:620px}
  .gp-voucher-face{grid-template-columns:1fr;grid-template-rows:minmax(0,1fr) auto}
  .gp-ticket-stub,.gp-back-stub{border-left:0;border-top:4px dashed var(--ticket-paper)}
  .gp-ticket-main{grid-template-columns:minmax(0,1fr) 280px}
  .gp-ticket-stub{display:grid;grid-template-columns:1fr auto;align-items:center}
  .gp-ticket-stub::before,.gp-ticket-stub::after{left:auto;top:-14px}
  .gp-ticket-stub::before{left:-14px}.gp-ticket-stub::after{right:-14px;bottom:auto}
  .gp-back-stub{display:grid;grid-template-columns:1fr auto;align-items:center}
  .gp-back-qr{min-height:96px}
}
@media(max-width:640px){
  .gp-grid{grid-template-columns:1fr}.gp-header-nav{display:none}:root{--pad-x:16px}
  .gp-voucher,.gp-voucher-shell{min-height:760px}
  .gp-ticket-main,.gp-back-main{padding:24px 20px}
  .gp-ticket-main{grid-template-columns:1fr;gap:18px}
  .gp-ticket-art{height:220px}
  .gp-ticket-brand{position:static;margin-bottom:0}
  .gp-ticket-foot{left:20px;right:20px;bottom:22px;align-items:flex-end}
  .gp-ticket-qr,.gp-ticket-noqr{width:82px;height:82px}
  .gp-ticket-stub,.gp-back-stub{grid-template-columns:1fr;align-items:start}
  .gp-stub-code{writing-mode:horizontal-tb}
  .gp-back-head,.gp-back-foot{flex-direction:column;align-items:flex-start}
  .gp-back-grid{grid-template-columns:1fr;gap:12px}
}

/* Pagination */
.gp-pagination { display: flex; align-items: center; justify-content: space-between; padding: 14px 0; margin-top: 20px; border-top: 1px solid rgba(178,143,110,0.22); }
.gp-pagination-info { font-size: 12px; color: var(--muted, #a08878); }
.gp-pagination-btns { display: flex; align-items: center; gap: 5px; }
.gp-pagination-btn { display: inline-flex; align-items: center; justify-content: center; min-width: 32px; height: 32px; padding: 0 8px; border: 1px solid rgba(178,143,110,0.22); border-radius: 8px; background: #fcf8f5; color: #5c4a54; font-size: 12px; font-weight: 600; font-family: inherit; text-decoration: none; transition: background 0.15s, color 0.15s, border-color 0.15s; cursor: pointer; }
.gp-pagination-btn:hover { background: #f2e4d4; color: #1a1118; border-color: #b8924a; }
.gp-pagination-btn-cur { background: #6b4459; color: #fcf8f5; border-color: #6b4459; }
.gp-pagination-btn-disabled { opacity: 0.3; pointer-events: none; }
.gp-btn-sm:focus-visible,.gp-filter:focus-visible,.gp-pagination-btn:focus-visible,.gp-profile-btn:focus-visible{outline:2px solid var(--gold);outline-offset:2px}
@media(prefers-reduced-motion:reduce){.gp-voucher-shell{transition:none}}
@media print{.gp-header,.gp-page-head,.gp-filters,.gp-pagination{display:none!important}.gp-page{padding:0;max-width:none}.gp-grid{display:block}.gp-voucher{break-inside:avoid;opacity:1;transform:none;margin:0 0 14px;min-height:0}.gp-voucher::before{display:none}.gp-voucher-shell{min-height:0;transform:none!important}.gp-voucher-face{position:relative;inset:auto;transform:none!important;box-shadow:none}.gp-voucher-front{display:none}.gp-voucher-back{display:grid}}
</style>
</head><body>

<?php $gpNavActive = 'bookings'; require APPROOT . '/views/layouts/customerHomeNav.php'; ?>

<main class="gp-page">
  <div class="gp-page-head">
    <div class="gp-page-eyebrow">Your Collection</div>
    <h1 class="gp-page-title">My <em>Vouchers</em></h1>
    <p class="gp-page-subtitle">Present these to your suppliers on the event day.</p>
  </div>

  <div class="gp-filters">
    <?php foreach ($filterLabels as $key=>$label): ?>
      <a class="gp-filter <?=$activeFilter===$key?'active':''?>" href="<?=URLROOT?>/booking/vouchers?status=<?=$key?>"><?=$label?> (<?=(int)($voucherStatusCounts[$key]??0)?>)</a>
    <?php endforeach; ?>
  </div>

  <div class="gp-grid">
    <?php if (empty($vouchers)): ?>
    <div class="gp-empty">
      <h2>No vouchers yet</h2>
      <p><?= ($voucherStatusCounts['all'] ?? 0) > 0 ? 'No vouchers match this filter yet.' : 'They appear here once your booking is confirmed.' ?></p>
      <a class="gp-btn-sm primary" href="<?=URLROOT?>/booking/myBookings">View My Bookings</a>
    </div>
    <?php else: ?>
      <?php foreach ($vouchers as $vc):
        $st = $vc['status']??'active';
        $stamp = $st === 'used' ? 'USED' : ($st === 'expired' ? 'EXPIRED' : '');
        $ticketDate = !empty($vc['event_date']) ? $formatDate($vc['event_date']) : 'Golden Promise';
        $ticketTime = !empty($vc['start_time'])
            ? $formatTime($vc['start_time']) . (!empty($vc['end_time']) ? ' - ' . $formatTime($vc['end_time']) : '')
            : 'Event day access';
        $ticketImage = $imageUrl($vc['thumbnail_url'] ?? '');
      ?>
      <div class="gp-voucher <?=$st!=='active'?$h($st):''?>" data-stamp="<?=$stamp?>" data-index="<?=$loop->index??0?>" data-voucher-toggle role="button" tabindex="0" aria-expanded="false" data-label-front="See voucher details for <?=$h($vc['service_name']??'this service')?>" data-label-back="Show voucher front for <?=$h($vc['service_name']??'this service')?>" aria-label="See voucher details for <?=$h($vc['service_name']??'this service')?>">
        <div class="gp-voucher-shell">
          <section class="gp-voucher-face gp-voucher-front" aria-label="Voucher front">
            <div class="gp-ticket-main">
              <div class="gp-ticket-brand">
                <span><?=$h($ticketDate)?></span>
              </div>
              <div class="gp-ticket-title">
                <span class="gp-ticket-service"><?=$h($vc['service_name']??'Wedding Voucher')?></span>
                <span class="gp-ticket-supplier"><?=$h($vc['supplier_name']??'Golden Promise')?></span>
                <span class="gp-ticket-time"><?=$h($ticketTime)?></span>
              </div>
              <div class="gp-ticket-art <?=$ticketImage !== '' ? 'has-image' : ''?>" aria-hidden="true">
                <?php if ($ticketImage !== ''): ?>
                  <img src="<?=$h($ticketImage)?>" alt="">
                <?php endif; ?>
              </div>
              <div class="gp-ticket-foot">
                <span class="gp-ticket-code"><?=$h($vc['voucher_number']??'')?></span>
              </div>
            </div>
            <aside class="gp-ticket-stub">
              <div class="gp-stub-action">
                <?php if (($st === 'active') && !empty($vc['qr_image_url'])): ?>
                  <div class="gp-ticket-qr">
                    <img src="<?=$h($vc['qr_image_url'])?>" alt="QR code for voucher <?=$h($vc['voucher_number']??'')?>">
                  </div>
                <?php else: ?>
                  <div class="gp-ticket-noqr"><?= $st === 'used' ? 'Used' : 'Expired' ?></div>
                <?php endif; ?>
                <span class="gp-stub-code"><?=$h($vc['voucher_number']??'')?></span>
                <p class="gp-ticket-hint">Front keeps the voucher clean. Flip for booking information.</p>
              </div>
            </aside>
          </section>

          <section class="gp-voucher-face gp-voucher-back" aria-label="Voucher details">
            <div class="gp-back-main">
              <div class="gp-back-head">
                <div>
                  <div class="gp-back-kicker">Voucher Details</div>
                  <h2 class="gp-back-title"><?=$h($vc['service_name']??'Service Voucher')?></h2>
                </div>
                <span class="gp-back-code"><?=$h($vc['voucher_number']??'')?></span>
              </div>
              <div class="gp-back-grid">
                <div class="gp-back-row">
                  <span class="gp-back-label">Service</span>
                  <span class="gp-back-value feature"><?=$h($vc['service_name']??'')?></span>
                </div>
                <div class="gp-back-row">
                  <span class="gp-back-label">Supplier</span>
                  <span class="gp-back-value"><?=$h($vc['supplier_name']??'Golden Promise')?></span>
                </div>
                <?php if ($vc['event_date']??''): ?>
                <div class="gp-back-row">
                  <span class="gp-back-label">Date</span>
                  <span class="gp-back-value"><?=$h($formatDate($vc['event_date']))?></span>
                </div>
                <?php endif; ?>
                <?php if ($vc['start_time']??''): ?>
                <div class="gp-back-row">
                  <span class="gp-back-label">Time</span>
                  <span class="gp-back-value"><?=$h($formatTime($vc['start_time']))?><?=!empty($vc['end_time'])?' - '.$h($formatTime($vc['end_time'])):''?></span>
                </div>
                <?php endif; ?>
                <?php if ($vc['location']??''): ?>
                <div class="gp-back-row">
                  <span class="gp-back-label">Location</span>
                  <span class="gp-back-value"><?=$h($vc['location'])?></span>
                </div>
                <?php endif; ?>
                <?php if (isset($vc['price'])): ?>
                <div class="gp-back-row">
                  <span class="gp-back-label">Value</span>
                  <span class="gp-back-value"><?=$h($money($vc['price']))?></span>
                </div>
                <?php endif; ?>
              </div>
              <div class="gp-back-foot">
                <p class="gp-back-note">Assigned supplier scans this QR on event day. It marks the voucher as used after supplier login.</p>
                <button class="gp-btn-sm" type="button" onclick="window.print()" style="font-size:10px;padding:4px 10px;">
                  <svg aria-hidden="true" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                  Print
                </button>
              </div>
            </div>
            <aside class="gp-back-stub">
              <div class="gp-back-verify">
                <div class="gp-back-qr">
                  <?php if (($st === 'active') && !empty($vc['qr_image_url'])): ?>
                    <img src="<?=$h($vc['qr_image_url'])?>" alt="QR code for voucher <?=$h($vc['voucher_number']??'')?>">
                  <?php else: ?>
                    <span><?= $st === 'used' ? 'Already used' : 'Not active' ?></span>
                  <?php endif; ?>
                </div>
                <?php if (($st === 'active') && !empty($vc['scan_url'])): ?>
                  <a class="gp-btn-sm primary" href="<?=$h($vc['scan_url'])?>" style="justify-content:center">Open scan link</a>
                <?php endif; ?>
              </div>
            </aside>
          </section>
        </div>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>

    <?php
    if (isset($currentPage, $totalPages, $totalCount, $perPage) && $totalPages > 1) {
        $h = function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };
        $classPrefix = 'customer';
        $baseParams = 'status=' . urlencode((string)$activeFilter);
        require APPROOT . '/views/partials/_pagination.php';
    }
    ?>
  </div>
</main>

<script>
(function(){
  const cards=document.querySelectorAll('.gp-voucher');
  if(!window.matchMedia('(prefers-reduced-motion:reduce)').matches){
    const obs=new IntersectionObserver(entries=>{entries.forEach(e=>{if(e.isIntersecting){e.target.classList.add('visible');obs.unobserve(e.target);}})},{threshold:.05});
    cards.forEach(el=>obs.observe(el));
  }else cards.forEach(el=>el.classList.add('visible'));

  function toggleVoucher(voucher){
    const isFlipped=!voucher.classList.contains('is-flipped');
    voucher.classList.toggle('is-flipped',isFlipped);
    voucher.setAttribute('aria-expanded',String(isFlipped));
    voucher.setAttribute('aria-label',isFlipped ? (voucher.dataset.labelBack || 'Show voucher front') : (voucher.dataset.labelFront || 'See voucher details'));
  }

  document.addEventListener('click',(e)=>{
    const voucher=e.target.closest('[data-voucher-toggle]');
    if(!voucher || e.target.closest('a'))return;
    toggleVoucher(voucher);
  });

  document.addEventListener('keydown',(e)=>{
    const voucher=e.target.closest('[data-voucher-toggle]');
    if(!voucher || (e.key!=='Enter' && e.key!==' '))return;
    e.preventDefault();
    toggleVoucher(voucher);
  });

  document.addEventListener('click',(e)=>{const btn=e.target.closest('.gp-profile-btn');if(btn){const x=btn.getAttribute('aria-expanded')==='true';document.querySelectorAll('.gp-profile-btn').forEach(b=>b.setAttribute('aria-expanded','false'));btn.setAttribute('aria-expanded',String(!x));return}document.querySelectorAll('.gp-profile-btn').forEach(b=>b.setAttribute('aria-expanded','false'))});
})();
</script>
</body></html>
