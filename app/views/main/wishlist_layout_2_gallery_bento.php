<!--
Layout 2: "Gallery Bento" ✦
Pinterest/bento-box grid — featured items get 2x sized cards,
others fill in around them. Light cream card style with gold accents,
a stat banner at the top, and a vertical collections rail on the left.
-->
<?php
$preview = true;
$collections = [
  ['id' => null, 'name' => 'All Saved', 'item_count' => 5, 'is_default' => true],
  ['id' => 1, 'name' => 'Venues I Love', 'item_count' => 2],
  ['id' => 2, 'name' => 'Photographers', 'item_count' => 2],
  ['id' => 3, 'name' => 'Decor Ideas', 'item_count' => 1],
];
$mockItems = [
  ['favorite_id'=>1,'service_id'=>42,'service_name'=>"Governor's Residence",'supplier_name'=>'HsuHive','category'=>'Venue','price_min'=>70000,'price_max'=>600000,'image'=>'','rating'=>4.5,'review_count'=>12,'booking_type'=>'slot','is_active'=>true,'notes'=>'Loved the garden & pool area','collection_id'=>1,'saved_at'=>'2 days ago'],
  ['favorite_id'=>2,'service_id'=>49,'service_name'=>'Zephyr Sein Lann So Pyay','supplier_name'=>'zaw moe','category'=>'Venue','price_min'=>900000,'price_max'=>910000,'image'=>'','rating'=>4.2,'review_count'=>8,'booking_type'=>'slot','is_active'=>true,'notes'=>'','collection_id'=>1,'saved_at'=>'1 week ago'],
  ['favorite_id'=>3,'service_id'=>43,'service_name'=>'Aphrodite Wedding Planning & Decoration','supplier_name'=>'HsuHive','category'=>'Decoration','price_min'=>3400000,'price_max'=>3400000,'image'=>'','rating'=>4.9,'review_count'=>15,'booking_type'=>'fullday','is_active'=>false,'notes'=>'Amazing reviews! Top choice','collection_id'=>3,'saved_at'=>'3 days ago'],
  ['favorite_id'=>4,'service_id'=>50,'service_name'=>'H & H Wedding Studio','supplier_name'=>'zaw moe','category'=>'Photography','price_min'=>200000,'price_max'=>2100000,'image'=>'','rating'=>4.7,'review_count'=>22,'booking_type'=>'slot','is_active'=>true,'notes'=>'','collection_id'=>2,'saved_at'=>'yesterday'],
  ['favorite_id'=>5,'service_id'=>45,'service_name'=>'Dear Brides','supplier_name'=>'zaw moe','category'=>'Attire','price_min'=>800000,'price_max'=>1200000,'image'=>'','rating'=>4.3,'review_count'=>6,'booking_type'=>'fullday','is_active'=>true,'notes'=>'Need to visit showroom','collection_id'=>null,'saved_at'=>'5 days ago'],
];
$activeCollection = null; $total = count($mockItems); $isLoggedIn = true; $wishlistCount = $total; $cartCount = 3;
$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$money = fn($v) => 'MMK ' . number_format((float)$v, 0);
$moneyRange = function($i)use($money){$min=(float)($i['price_min']??0);$max=(float)($i['price_max']??0);if($min>0&&$max>0&&$max>$min)return $money($min).' – '.$money($max);return $money($max>0?$max:$min);};
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Layout 2: Gallery Bento — Golden Promise Wishlist</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400;1,600&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root{
  --c-bg:#f5e8d9;--c-surface:#faf5ef;--c-white:#fcf8f5;--c-rule:#ead8c7;--c-strong:#765a46;--c-accent:#6f625a;--c-muted:#9b7d6b;
  --c-text:#211d1a;--c-pale:#b79c8b;--c-gold:#d8b46a;--c-red:#b94a48;--c-heart:#e55b5b;
  --font-display:'Playfair Display',Georgia,serif;--font-body:'Poppins',system-ui,sans-serif;
  --pad-x:clamp(20px,5vw,72px);--ease:cubic-bezier(.19,1,.22,1);
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{background:#faf5ef;color:var(--c-text);font-family:var(--font-body);font-size:14px;line-height:1.6;-webkit-font-smoothing:antialiased}
a{color:inherit;text-decoration:none}img{display:block;max-width:100%}button,input{font-family:var(--font-body);outline:none}

/* ══ HEADER ══ */
.gp-header{position:sticky;top:0;z-index:100;display:grid;grid-template-columns:auto 1fr auto;align-items:center;gap:24px;padding:16px var(--pad-x);border-bottom:1px solid rgba(184,154,109,.2);background:rgba(255,248,239,.94);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px)}
.gp-brand{display:flex;align-items:center;gap:12px;color:#211b17;font-size:18px;font-weight:800}
.gp-brand-mark{display:grid;place-items:center;width:40px;height:40px;border-radius:50%;background:var(--c-strong);color:#fff4e6;font-size:14px;letter-spacing:1px}
.gp-header-nav{display:flex;align-items:center;justify-content:center;gap:4px}
.gp-header-nav a{padding:8px 18px;border-radius:999px;font-size:13px;font-weight:700;color:#51483f;transition:all .2s}
.gp-header-nav a:hover,.gp-header-nav a.active{color:var(--c-red);background:rgba(185,74,72,.08)}
.gp-header-actions{display:flex;align-items:center;gap:12px}

/* ══ STAT BANNER ══ */
.wl-stat-bar{display:flex;gap:0;padding:0 var(--pad-x);max-width:900px;margin:36px auto 0}
.wl-stat-card{flex:1;text-align:center;padding:24px 16px;background:var(--c-white);border:1px solid var(--c-rule)}
.wl-stat-card:first-child{border-radius:16px 0 0 16px}
.wl-stat-card:last-child{border-radius:0 16px 16px 0}
.wl-stat-card+.wl-stat-card{border-left:0}
.wl-stat-num{font-family:var(--font-display);font-size:32px;font-weight:600;color:var(--c-text);line-height:1}
.wl-stat-label{font-size:11px;color:var(--c-pale);font-weight:500;margin-top:4px}
.wl-stat-card--accent{background:rgba(216,180,106,.12)}
.wl-stat-card--accent .wl-stat-num{color:var(--c-gold)}

/* ══ PAGE TITLE ══ */
.wl-title-row{display:flex;align-items:flex-end;justify-content:space-between;gap:16px;padding:36px var(--pad-x) 8px;max-width:900px;margin:0 auto}
.wl-title{font-family:var(--font-display);font-size:clamp(26px,4vw,38px);font-weight:600;line-height:1}
.wl-subtitle{font-size:13px;color:var(--c-pale)}

/* ══ COLLECTIONS VERTICAL + GRID ══ */
.wl-layout{display:grid;grid-template-columns:200px 1fr;gap:28px;padding:20px var(--pad-x) 80px;max-width:900px;margin:0 auto}
.wl-rail{position:sticky;top:100px;align-self:start}
.wl-rail-label{font-size:10px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--c-pale);margin-bottom:10px}
.wl-rail-item{display:flex;align-items:center;gap:8px;padding:9px 12px;border-radius:10px;font-size:12px;font-weight:600;color:var(--c-accent);cursor:pointer;transition:all .12s;border:none;background:transparent;width:100%;text-align:left}
.wl-rail-item:hover{background:rgba(118,90,70,.04);color:var(--c-strong)}
.wl-rail-item.is-active{background:rgba(185,74,72,.06);color:var(--c-red)}
.wl-rail-count{margin-left:auto;font-size:10px;color:var(--c-pale)}
.wl-rail-add{display:flex;align-items:center;gap:6px;margin-top:8px;padding:7px 12px;border-radius:10px;border:1.5px dashed var(--c-rule);background:transparent;color:var(--c-pale);font-size:11px;font-weight:600;cursor:pointer;width:100%}
.wl-rail-add:hover{border-color:var(--c-gold);color:var(--c-strong)}

/* ══ BENTO GRID ══ */
.wl-bento{display:grid;grid-template-columns:repeat(2,1fr);grid-auto-rows:minmax(220px,auto);gap:14px}
.wl-bento-card{position:relative;background:var(--c-white);border-radius:16px;overflow:hidden;border:1px solid var(--c-rule);display:flex;flex-direction:column;transition:all .3s var(--ease);cursor:pointer}
.wl-bento-card:hover{transform:translateY(-3px);box-shadow:0 14px 36px rgba(118,90,70,.10)}
.wl-bento-card--featured{grid-row:span 2}
.wl-bento-img{aspect-ratio:16/9;overflow:hidden;background:linear-gradient(160deg,#ede0d0,#ddcebb);position:relative;flex-shrink:0}
.wl-bento-card--featured .wl-bento-img{aspect-ratio:4/3}
.wl-bento-img img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;transition:transform .5s var(--ease)}
.wl-bento-card:hover .wl-bento-img img{transform:scale(1.05)}
.wl-bento-img-ph{position:absolute;inset:0;display:grid;place-items:center;font-size:36px;opacity:.15}
.wl-bento-body{padding:14px 16px;flex:1;display:flex;flex-direction:column}
.wl-bento-sup{display:flex;align-items:center;gap:8px;margin-bottom:4px}
.wl-bento-col-tag{font-size:9px;padding:2px 8px;border-radius:999px;background:rgba(216,180,106,.14);color:var(--c-gold);font-weight:600}
.wl-bento-sup-name{font-size:10px;color:var(--c-muted);font-weight:500}
.wl-bento-name{font-family:var(--font-display);font-size:17px;font-weight:600;line-height:1.15;color:var(--c-text);margin-bottom:4px}
.wl-bento-card--featured .wl-bento-name{font-size:22px}
.wl-bento-meta{font-size:10px;color:var(--c-accent);display:flex;gap:10px;align-items:center;flex-wrap:wrap}
.wl-bento-foot{display:flex;align-items:center;justify-content:space-between;margin-top:auto;padding-top:10px;border-top:1px solid var(--c-rule)}
.wl-bento-price{font-family:var(--font-display);font-size:16px;font-weight:600;color:var(--c-red)}
.wl-bento-card--featured .wl-bento-price{font-size:20px}
.wl-bento-note{font-size:10px;color:var(--c-pale);font-style:italic;margin-top:4px}
.wl-bento-badge{position:absolute;top:10px;left:10px;z-index:5;padding:3px 9px;border-radius:999px;font-size:9px;font-weight:700;background:rgba(255,250,246,.9);color:var(--c-strong)}
.wl-bento-badge--alert{background:rgba(185,74,72,.10);color:var(--c-red)}
.wl-bento-heart{position:absolute;top:10px;right:10px;z-index:5;display:grid;place-items:center;width:28px;height:28px;border-radius:50%;border:none;background:rgba(0,0,0,.28);backdrop-filter:blur(6px);color:var(--c-heart);cursor:pointer;font-size:12px;transition:all .2s}
.wl-bento-heart:hover{transform:scale(1.14)}

@media(max-width:800px){.wl-layout{grid-template-columns:1fr}.wl-rail{position:static;display:flex;gap:4px;overflow-x:auto;padding-bottom:8px}.wl-rail-item{flex-shrink:0}}
@media(max-width:600px){.wl-bento{grid-template-columns:1fr}.wl-stat-bar{flex-wrap:wrap}.wl-stat-card{flex:1 1 40%}}
</style>
</head><body>

<header class="gp-header">
  <a class="gp-brand" href="#"><span class="gp-brand-mark">G</span><span>Golden Promise</span></a>
  <nav class="gp-header-nav"><a href="#">Home</a><a href="#">Services</a><a href="#">Packages</a></nav>
  <div class="gp-header-actions">
    <span style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:999px;border:1px solid rgba(229,91,91,.15);background:rgba(229,91,91,.04);color:var(--c-heart);font-size:13px;font-weight:700">♥ <?= $wishlistCount ?></span>
    <span style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:999px;border:1px solid #ead8c7;background:#fcf8f5;color:#765a46;font-size:13px;font-weight:700">🛒 <?= $cartCount ?></span>
    <span style="display:grid;place-items:center;width:40px;height:40px;border-radius:50%;background:#765a46;color:#fff4e6;font-size:12px;font-weight:800">H</span>
  </div>
</header>

<!-- STAT BANNER -->
<div class="wl-stat-bar">
  <div class="wl-stat-card wl-stat-card--accent"><div class="wl-stat-num"><?= $total ?></div><div class="wl-stat-label">Saved Services</div></div>
  <div class="wl-stat-card"><div class="wl-stat-num"><?= count($collections)-1 ?></div><div class="wl-stat-label">Collections</div></div>
  <div class="wl-stat-card"><div class="wl-stat-num"><?= count(array_filter($mockItems,fn($i)=>$i['notes'])) ?></div><div class="wl-stat-label">With Notes</div></div>
</div>

<!-- TITLE -->
<div class="wl-title-row">
  <div><h1 class="wl-title">My Wishlist</h1><div class="wl-subtitle">Curate your dream wedding — compare, note, decide</div></div>
  <span class="wl-rail-add" style="width:auto;padding:8px 16px;margin-top:0">+ New collection</span>
</div>

<!-- LAYOUT -->
<div class="wl-layout">
  <!-- RAIL -->
  <nav class="wl-rail">
    <div class="wl-rail-label">Collections</div>
    <?php foreach($collections as $col): $cid=$col['id'];$active=null===$cid; ?>
      <a class="wl-rail-item <?= $active?'is-active':'' ?>" href="?collection=<?= $cid??'' ?>">
        <?= $col['is_default']?'📋':'📁' ?> <?= $h($col['name']) ?>
        <span class="wl-rail-count"><?= (int)($col['item_count']??0) ?></span>
      </a>
    <?php endforeach; ?>
  </nav>

  <!-- BENTO GRID -->
  <div class="wl-bento">
    <?php foreach($mockItems as $i => $item): $isFeatured = $i === 0; ?>
    <article class="wl-bento-card <?= $isFeatured?'wl-bento-card--featured':'' ?>">
      <button class="wl-bento-heart">♥</button>
      <?php if(!$item['is_active']): ?><span class="wl-bento-badge wl-bento-badge--alert">Unavailable</span>
      <?php else: ?><span class="wl-bento-badge"><?= $h($item['category']) ?></span><?php endif; ?>

      <div class="wl-bento-img">
        <div class="wl-bento-img-ph">🖼️</div>
      </div>

      <div class="wl-bento-body">
        <div class="wl-bento-sup">
          <?php if($item['collection_id']): ?><span class="wl-bento-col-tag">📁 <?= $h($collections[$item['collection_id']]['name']??'') ?></span><?php endif; ?>
          <span class="wl-bento-sup-name"><?= $h($item['supplier_name']) ?></span>
        </div>
        <h3 class="wl-bento-name"><?= $h($item['service_name']) ?></h3>
        <div class="wl-bento-meta">
          <span>⭐ <?= number_format($item['rating'],1) ?> (<?= $item['review_count'] ?>)</span>
          <span>·</span>
          <span><?= $item['booking_type']==='slot'?'Per session':'Full day' ?></span>
          <span>·</span>
          <span><?= $h($item['saved_at']) ?></span>
        </div>
        <?php if($item['notes'] && $isFeatured): ?><div class="wl-bento-note">💬 "<?= $h($item['notes']) ?>"</div><?php endif; ?>
        <div class="wl-bento-foot">
          <span class="wl-bento-price"><?= $moneyRange($item) ?></span>
          <span style="font-size:11px;color:var(--c-strong);font-weight:600">View →</span>
        </div>
      </div>
    </article>
    <?php endforeach; ?>
  </div>
</div>

</body></html>
