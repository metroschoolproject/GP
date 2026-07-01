<?php
$vouchers = $vouchers ?? [];
$activeFilter = $activeFilter ?? 'all';

$filterLabels = ['all' => 'Active', 'used' => 'Used', 'expired' => 'Expired'];
$money = fn($v) => number_format((float)$v, 0) . ' MMK';
$plain = function($v){ $t=(string)$v; for($i=0;$i<10;$i++){$d=html_entity_decode($t,ENT_QUOTES|ENT_HTML5,'UTF-8');if($d===$t)break;$t=$d;}return $t; };
$h = fn($v)=>htmlspecialchars($plain($v),ENT_QUOTES,'UTF-8');

$activeCount = count(array_filter($vouchers, fn($v)=>($v['status']??'')==='active'));
$usedCount = count(array_filter($vouchers, fn($v)=>($v['status']??'')==='used'));
$expiredCount = count(array_filter($vouchers, fn($v)=>($v['status']??'')==='expired'));
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
:root{--bg:#f2e4d4;--card:#fcf8f5;--rule:rgba(178,143,110,0.22);--plum:#6b4459;--plum-lt:#9b7289;--rose:#c27a8e;--gold:#b8924a;--muted:#a08878;--text:#1a1118;--text2:#5c4a54;--r-sm:8px;--r-md:14px;--r-lg:20px;--font-d:'Playfair Display',Georgia,serif;--font-b:'Poppins',system-ui,sans-serif;--pad-x:clamp(20px,5vw,72px);--ease-expo:cubic-bezier(0.19,1,0.22,1);}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{background:var(--bg);color:var(--text);font-family:var(--font-b);font-size:14px;-webkit-font-smoothing:antialiased;min-height:100vh;display:flex;flex-direction:column;overflow-x:hidden}
a{color:inherit;text-decoration:none}

.gp-header{position:sticky;top:0;z-index:100;display:grid;grid-template-columns:auto 1fr auto;align-items:center;gap:24px;padding:0 var(--pad-x);height:68px;border-bottom:1px solid var(--rule);background:rgba(242,228,212,0.82);backdrop-filter:blur(24px) saturate(1.4)}
.gp-brand{display:flex;align-items:center;gap:12px;font-size:17px;font-weight:800}
.gp-brand-mark{display:grid;place-items:center;width:38px;height:38px;border-radius:50%;background:var(--plum);color:#fffaf3;font-size:13px;font-weight:700}
.gp-btn-sm{display:inline-flex;align-items:center;gap:4px;padding:6px 14px;border-radius:999px;border:1px solid var(--rule-strong);font-size:11px;font-weight:600;color:var(--text2);transition:all .2s}
.gp-btn-sm:hover{border-color:var(--plum);color:var(--plum)}
.gp-btn-sm.primary{background:var(--plum);color:#fcf8f5;border-color:var(--plum)}
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

.gp-page{position:relative;z-index:1;flex:1;padding:52px var(--pad-x) 80px;max-width:1060px;margin:0 auto;width:100%}
.gp-page-head{margin-bottom:32px;opacity:0;animation:fadeUp .7s var(--ease-expo) .1s forwards}
.gp-page-eyebrow{font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--gold);margin-bottom:8px}
.gp-page-title{font-family:var(--font-d);font-size:clamp(34px,5vw,52px);font-weight:600;line-height:.92}
.gp-page-title em{font-style:italic;color:var(--plum-lt)}
.gp-page-subtitle{font-size:14px;color:var(--muted);margin-top:8px}

.gp-filters{display:flex;gap:6px;margin-bottom:28px}
.gp-filter{padding:6px 16px;border-radius:999px;border:1px solid var(--rule-strong);font-size:12px;font-weight:600;color:var(--text2);transition:all .2s;white-space:nowrap}
.gp-filter:hover{border-color:var(--plum);color:var(--plum)}
.gp-filter.active{background:var(--plum);color:#fcf8f5;border-color:var(--plum)}

.gp-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:20px}
.gp-voucher{background:var(--card);border:1px solid var(--rule);border-radius:var(--r-lg);overflow:hidden;position:relative;opacity:0;transform:translateY(20px);transition:all .5s var(--ease-expo)}
.gp-voucher.visible{opacity:1;transform:translateY(0)}
.gp-voucher.used,.gp-voucher.expired{opacity:0.7}
.gp-voucher.used::after,.gp-voucher.expired::after{content:attr(data-stamp);position:absolute;top:20px;right:-32px;transform:rotate(45deg);font-size:11px;font-weight:800;letter-spacing:1px;text-transform:uppercase;padding:4px 40px;border:2px solid;z-index:2}

.gp-vh{padding:20px 20px 12px;position:relative}
.gp-vh-brand{font-size:10px;font-weight:700;letter-spacing:2px;color:var(--plum-lt)}
.gp-vh-title{font-family:var(--font-d);font-size:24px;font-weight:600;color:var(--plum);margin-top:2px}
.gp-vh-line{height:2px;background:linear-gradient(90deg,var(--plum),var(--rose),var(--gold));margin-top:8px;border-radius:2px;width:60px}

.gp-vb{padding:12px 20px 20px;display:flex;flex-direction:column;gap:10px}
.gp-vb-row{display:flex;justify-content:space-between;align-items:center;font-size:12px}
.gp-vb-label{color:var(--muted)}
.gp-vb-value{font-weight:500;color:var(--text)}
.gp-vb-code{font-family:monospace;font-size:11px;color:var(--muted);background:rgba(107,68,89,0.04);padding:4px 8px;border-radius:4px;letter-spacing:.5px}

.gp-vf{padding:14px 20px;border-top:1px dashed var(--rule);display:flex;justify-content:space-between;align-items:center}
.gp-vf-status{display:flex;align-items:center;gap:6px;font-size:11px;font-weight:600}
.gp-vf-dot{width:8px;height:8px;border-radius:50%}

.gp-empty{text-align:center;padding:80px 24px;grid-column:1/-1;border:1px dashed rgba(107,68,89,0.18);border-radius:var(--r-xl);background:var(--card)}
.gp-empty h2{font-family:var(--font-d);font-size:28px;margin-bottom:8px}
.gp-empty p{color:var(--muted);font-size:14px;margin-bottom:20px}

@keyframes fadeUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
@media(max-width:640px){.gp-grid{grid-template-columns:1fr}.gp-header-nav{display:none}:root{--pad-x:16px}}

/* Pagination */
.gp-pagination { display: flex; align-items: center; justify-content: space-between; padding: 14px 0; margin-top: 20px; border-top: 1px solid rgba(178,143,110,0.22); }
.gp-pagination-info { font-size: 12px; color: var(--muted, #a08878); }
.gp-pagination-btns { display: flex; align-items: center; gap: 5px; }
.gp-pagination-btn { display: inline-flex; align-items: center; justify-content: center; min-width: 32px; height: 32px; padding: 0 8px; border: 1px solid rgba(178,143,110,0.22); border-radius: 8px; background: #fcf8f5; color: #5c4a54; font-size: 12px; font-weight: 600; font-family: inherit; text-decoration: none; transition: all 0.15s; cursor: pointer; }
.gp-pagination-btn:hover { background: #f2e4d4; color: #1a1118; border-color: #b8924a; }
.gp-pagination-btn-cur { background: #6b4459; color: #fcf8f5; border-color: #6b4459; }
.gp-pagination-btn-disabled { opacity: 0.3; pointer-events: none; }
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
    <?php $counts=['all'=>count($vouchers),'active'=>$activeCount,'used'=>$usedCount,'expired'=>$expiredCount]; ?>
    <?php foreach ($filterLabels as $key=>$label): ?>
      <a class="gp-filter <?=$activeFilter===$key?'active':''?>" href="<?=URLROOT?>/booking/vouchers?status=<?=$key?>"><?=$label?> (<?=$counts[$key]??0?>)</a>
    <?php endforeach; ?>
  </div>

  <div class="gp-grid">
    <?php if (empty($vouchers)): ?>
    <div class="gp-empty">
      <h2>No vouchers yet</h2>
      <p>They appear here once your booking is confirmed.</p>
      <a class="gp-btn-sm primary" href="<?=URLROOT?>/booking/myBookings">View My Bookings</a>
    </div>
    <?php else: ?>
      <?php foreach ($vouchers as $vc):
        $st = $vc['status']??'active';
        $stamp = $st === 'used' ? 'USED' : ($st === 'expired' ? 'EXPIRED' : '');
        $stampColor = $st === 'used' ? '#6b7280' : '#b94b4b';
      ?>
      <div class="gp-voucher <?=$st!=='active'?$st:''?>" data-stamp="<?=$stamp?>" data-index="<?=$loop->index??0?>">
        <div class="gp-vh">
          <div class="gp-vh-brand">GOLDEN PROMISE</div>
          <div class="gp-vh-title">VOUCHER</div>
          <div class="gp-vh-line"></div>
        </div>
        <div class="gp-vb">
          <div class="gp-vb-row">
            <span class="gp-vb-label">Service</span>
            <span class="gp-vb-value" style="font-family:var(--font-d);font-weight:600;"><?=$h($vc['service_name']??'')?></span>
          </div>
          <div class="gp-vb-row">
            <span class="gp-vb-label">Supplier</span>
            <span class="gp-vb-value"><?=$h($vc['supplier_name']??'')?></span>
          </div>
          <?php if ($vc['event_date']??''): ?>
          <div class="gp-vb-row">
            <span class="gp-vb-label">Date</span>
            <span class="gp-vb-value"><?=$h(date('d M Y',strtotime($vc['event_date'])))?></span>
          </div>
          <?php endif; ?>
          <?php if ($vc['start_time']??''): ?>
          <div class="gp-vb-row">
            <span class="gp-vb-label">Time</span>
            <span class="gp-vb-value"><?=$h(date('g:i A',strtotime($vc['start_time'])))?><?=$vc['end_time']?' — '.$h(date('g:i A',strtotime($vc['end_time']))):''?></span>
          </div>
          <?php endif; ?>
          <div class="gp-vb-row">
            <span class="gp-vb-label">Code</span>
            <span class="gp-vb-code"><?=$h($vc['voucher_number']??'')?></span>
          </div>
        </div>
        <div class="gp-vf">
          <span class="gp-vf-status">
            <span class="gp-vf-dot" style="background:<?=$st==='active'?'#166534':($st==='used'?'#6b7280':'#b94b4b')?>"></span>
            <?=ucfirst($st)?>
          </span>
          <button class="gp-btn-sm" onclick="window.print()" style="font-size:10px;padding:4px 10px;">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            Print
          </button>
        </div>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>

    <?php
    if (isset($currentPage, $totalPages, $totalCount, $perPage) && $totalPages > 1) {
        $h = function ($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); };
        $classPrefix = 'customer';
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

  document.addEventListener('click',(e)=>{const btn=e.target.closest('.gp-profile-btn');if(btn){const x=btn.getAttribute('aria-expanded')==='true';document.querySelectorAll('.gp-profile-btn').forEach(b=>b.setAttribute('aria-expanded','false'));btn.setAttribute('aria-expanded',String(!x));return}document.querySelectorAll('.gp-profile-btn').forEach(b=>b.setAttribute('aria-expanded','false'))});
})();
</script>
</body></html>
