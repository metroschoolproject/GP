<!--
Layout 3: "Collection Rows" ✦
Each collection gets its own horizontal-scrolling row of cards (Netflix/Spotify style).
A "hero" shelf at top with the most recent saves, then collection-specific rows.
Full-bleed dark warm gradient background, cinematic feel.
-->
<?php
$preview = true;
$collections = [
  ['id' => null, 'name' => 'Recently Saved', 'item_count' => 5, 'is_default' => true],
  ['id' => 1, 'name' => 'Venues I Love', 'item_count' => 2],
  ['id' => 2, 'name' => 'Photographers', 'item_count' => 2],
  ['id' => 3, 'name' => 'Decor Ideas', 'item_count' => 1],
];
$mockItems = [
  ['favorite_id'=>1,'service_id'=>42,'service_name'=>"Governor's Residence",'supplier_name'=>'HsuHive','category'=>'Venue','price_min'=>70000,'price_max'=>600000,'image'=>'','rating'=>4.5,'review_count'=>12,'booking_type'=>'slot','is_active'=>true,'notes'=>'Beautiful garden venue','collection_id'=>1,'saved_at'=>'2d ago'],
  ['favorite_id'=>2,'service_id'=>49,'service_name'=>'Zephyr Sein Lann','supplier_name'=>'zaw moe','category'=>'Venue','price_min'=>900000,'price_max'=>910000,'image'=>'','rating'=>4.2,'review_count'=>8,'booking_type'=>'slot','is_active'=>true,'notes'=>'','collection_id'=>1,'saved_at'=>'1w ago'],
  ['favorite_id'=>3,'service_id'=>43,'service_name'=>'Aphrodite Wedding Planning','supplier_name'=>'HsuHive','category'=>'Decoration','price_min'=>3400000,'price_max'=>3400000,'image'=>'','rating'=>4.9,'review_count'=>15,'booking_type'=>'fullday','is_active'=>false,'notes'=>'','collection_id'=>3,'saved_at'=>'3d ago'],
  ['favorite_id'=>4,'service_id'=>50,'service_name'=>'H & H Wedding Studio','supplier_name'=>'zaw moe','category'=>'Photography','price_min'=>200000,'price_max'=>2100000,'image'=>'','rating'=>4.7,'review_count'=>22,'booking_type'=>'slot','is_active'=>true,'notes'=>'','collection_id'=>2,'saved_at'=>'1d ago'],
  ['favorite_id'=>5,'service_id'=>45,'service_name'=>'Dear Brides','supplier_name'=>'zaw moe','category'=>'Attire','price_min'=>800000,'price_max'=>1200000,'image'=>'','rating'=>4.3,'review_count'=>6,'booking_type'=>'fullday','is_active'=>true,'notes'=>'Need to visit showroom','collection_id'=>null,'saved_at'=>'5d ago'],
];
$total = count($mockItems); $isLoggedIn = true; $wishlistCount = $total; $cartCount = 3;
$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$money = fn($v) => 'MMK ' . number_format((float)$v, 0);
$moneyRange = function($i)use($money){$min=(float)($i['price_min']??0);$max=(float)($i['price_max']??0);if($min>0&&$max>0&&$max>$min)return $money($min).' – '.$money($max);return $money($max>0?$max:$min);};

// Group items by collection (null -> recently saved)
$grouped = [null => []];
foreach($collections as $c) if(!$c['is_default']) $grouped[$c['id']] = [];
foreach($mockItems as $item) {
  $cid = $item['collection_id'] ?? null;
  $grouped[$cid][] = $item;
}
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Layout 3: Collection Rows — Golden Promise Wishlist</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400;1,600&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root{
  --c-bg:#f5e8d9;--c-white:#fcf8f5;--c-rule:#ead8c7;--c-strong:#765a46;--c-accent:#6f625a;--c-muted:#9b7d6b;
  --c-text:#211d1a;--c-pale:#b79c8b;--c-gold:#d8b46a;--c-red:#b94a48;--c-heart:#e55b5b;
  --font-display:'Playfair Display',Georgia,serif;--font-body:'Poppins',system-ui,sans-serif;
  --pad-x:clamp(20px,5vw,72px);--ease:cubic-bezier(.19,1,.22,1);
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{background:linear-gradient(180deg,#f5e8d9 0%,#eee0d0 50%,#e7d5c1 100%);color:var(--c-text);font-family:var(--font-body);font-size:14px;line-height:1.6;-webkit-font-smoothing:antialiased}
a{color:inherit;text-decoration:none}img{display:block;max-width:100%}button,input{font-family:var(--font-body);outline:none}

/* ══ HEADER ══ */
.gp-header{position:sticky;top:0;z-index:100;display:grid;grid-template-columns:auto 1fr auto;align-items:center;gap:24px;padding:16px var(--pad-x);border-bottom:1px solid rgba(184,154,109,.2);background:rgba(255,248,239,.94);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px)}
.gp-brand{display:flex;align-items:center;gap:12px;color:#211b17;font-size:18px;font-weight:800}
.gp-brand-mark{display:grid;place-items:center;width:40px;height:40px;border-radius:50%;background:var(--c-strong);color:#fff4e6;font-size:14px;letter-spacing:1px}
.gp-header-nav{display:flex;align-items:center;justify-content:center;gap:4px}
.gp-header-nav a{padding:8px 18px;border-radius:999px;font-size:13px;font-weight:700;color:#51483f;transition:all .2s}
.gp-header-nav a:hover,.gp-header-nav a.active{color:var(--c-red);background:rgba(185,74,72,.08)}
.gp-header-actions{display:flex;align-items:center;gap:12px}

/* ══ HERO SHELF ══ */
.wl-hero-shelf{padding:48px var(--pad-x) 36px;text-align:center}
.wl-hero-kicker{font-size:10px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:var(--c-gold);margin-bottom:8px}
.wl-hero-shelf h1{font-family:var(--font-display);font-size:clamp(28px,5vw,48px);font-weight:600;line-height:1;margin-bottom:6px}
.wl-hero-shelf p{font-size:13px;color:var(--c-pale);margin-bottom:20px}
.wl-hero-actions{display:flex;gap:8px;justify-content:center;flex-wrap:wrap}
.wl-hbtn{display:inline-flex;align-items:center;gap:6px;padding:10px 20px;border-radius:999px;font-size:12px;font-weight:700;cursor:pointer;transition:all .2s;border:none}
.wl-hbtn--primary{background:var(--c-strong);color:#fcf8f5;box-shadow:0 8px 24px rgba(118,90,70,.18)}
.wl-hbtn--primary:hover{background:var(--c-red);transform:translateY(-1px)}
.wl-hbtn--ghost{border:1.5px solid var(--c-rule);background:transparent;color:var(--c-strong)}
.wl-hbtn--ghost:hover{border-color:var(--c-gold)}

/* ══ ROW ══ */
.wl-row{position:relative;padding:0 var(--pad-x) 44px}
.wl-row:last-child{padding-bottom:80px}
.wl-row-head{display:flex;align-items:baseline;justify-content:space-between;gap:12px;margin-bottom:16px}
.wl-row-title{font-family:var(--font-display);font-size:22px;font-weight:600;color:var(--c-text);line-height:1}
.wl-row-meta{font-size:12px;color:var(--c-pale)}
.wl-row-scroll{display:flex;gap:16px;overflow-x:auto;scroll-snap-type:x mandatory;scrollbar-width:none;padding-bottom:4px}
.wl-row-scroll::-webkit-scrollbar{display:none}

/* ══ CARD (horizontal scroller style) ══ */
.wl-h-card{flex:0 0 260px;scroll-snap-align:start;position:relative;background:var(--c-white);border-radius:16px;border:1px solid var(--c-rule);overflow:hidden;cursor:pointer;transition:all .3s var(--ease)}
.wl-h-card:hover{transform:translateY(-4px);box-shadow:0 16px 40px rgba(118,90,70,.12)}
.wl-h-card-img{aspect-ratio:4/3;overflow:hidden;background:linear-gradient(160deg,#ede0d0,#ddcebb);position:relative}
.wl-h-card-img-ph{position:absolute;inset:0;display:grid;place-items:center;font-size:38px;opacity:.12}
.wl-h-card-body{padding:14px 16px}
.wl-h-card-sup{font-size:10px;font-weight:600;color:var(--c-muted);margin-bottom:3px;display:flex;align-items:center;gap:6px}
.wl-h-card-col-tag{font-size:8px;padding:2px 7px;border-radius:999px;background:rgba(216,180,106,.14);color:var(--c-gold);font-weight:700}
.wl-h-card-name{font-family:var(--font-display);font-size:17px;font-weight:600;line-height:1.15;color:var(--c-text)}
.wl-h-card-meta{font-size:10px;color:var(--c-accent);margin:4px 0 8px}
.wl-h-card-foot{display:flex;align-items:center;justify-content:space-between}
.wl-h-card-price{font-family:var(--font-display);font-size:15px;font-weight:600;color:var(--c-red)}
.wl-h-card-note{font-size:10px;color:var(--c-pale);font-style:italic;margin-top:6px;padding-top:6px;border-top:1px solid var(--c-rule)}
.wl-h-card-badge{position:absolute;top:10px;left:10px;z-index:5;font-size:9px;font-weight:700;padding:3px 9px;border-radius:999px;background:rgba(255,250,246,.9);color:var(--c-strong)}
.wl-h-card-badge--alert{background:rgba(185,74,72,.10);color:var(--c-red)}
.wl-h-card-heart{position:absolute;top:10px;right:10px;z-index:5;display:grid;place-items:center;width:28px;height:28px;border-radius:50%;border:none;background:rgba(0,0,0,.28);color:var(--c-heart);cursor:pointer;font-size:12px;transition:all .2s}
.wl-h-card-heart:hover{transform:scale(1.14)}

/* scroll arrows */
.wl-row-arrows{display:flex;gap:4px;align-items:center}
.wl-row-arrow{display:grid;place-items:center;width:30px;height:30px;border-radius:50%;border:1px solid var(--c-rule);background:var(--c-white);color:var(--c-accent);cursor:pointer;font-size:12px;transition:all .12s}
.wl-row-arrow:hover{border-color:var(--c-gold);color:var(--c-strong)}

/* empty row */
.wl-row-empty{padding:32px;text-align:center;border:1px dashed rgba(109,76,91,.12);border-radius:16px;color:var(--c-pale);font-size:13px}

@media(max-width:700px){.wl-h-card{flex:0 0 200px}}
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

<!-- HERO SHELF -->
<section class="wl-hero-shelf">
  <div class="wl-hero-kicker">★ Your Wishlist</div>
  <h1>Build your dream wedding</h1>
  <p><?= $total ?> services saved across <?= count($collections)-1 ?> collections</p>
  <div class="wl-hero-actions">
    <button class="wl-hbtn wl-hbtn--primary">Browse Services</button>
    <button class="wl-hbtn wl-hbtn--ghost">New Collection</button>
  </div>
</section>

<!-- ROWS -->
<?php foreach($grouped as $cid => $items):
  $colName = 'Recently Saved';
  foreach($collections as $c) if($c['id'] === $cid) { $colName = $c['name']; break; }
  if(empty($items)) continue;
?>
<section class="wl-row">
  <div class="wl-row-head">
    <div>
      <h2 class="wl-row-title"><?= $h($colName) ?></h2>
      <div class="wl-row-meta"><?= count($items) ?> service<?= count($items)!==1?'s':'' ?></div>
    </div>
    <div class="wl-row-arrows">
      <button class="wl-row-arrow" title="Scroll left">←</button>
      <button class="wl-row-arrow" title="Scroll right">→</button>
    </div>
  </div>

  <?php if(empty($items)): ?>
    <div class="wl-row-empty">No services in this collection yet — browse and tap ♥ to add some!</div>
  <?php else: ?>
  <div class="wl-row-scroll">
    <?php foreach($items as $item): ?>
    <article class="wl-h-card">
      <button class="wl-h-card-heart">♥</button>
      <?php if(!$item['is_active']): ?><span class="wl-h-card-badge wl-h-card-badge--alert">Unavailable</span>
      <?php else: ?><span class="wl-h-card-badge"><?= $h($item['category']) ?></span><?php endif; ?>

      <div class="wl-h-card-img"><div class="wl-h-card-img-ph">🖼️</div></div>

      <div class="wl-h-card-body">
        <div class="wl-h-card-sup">
          <?= $h($item['supplier_name']) ?>
          <?php if($item['collection_id'] && $cid === null): ?><span class="wl-h-card-col-tag">📁 <?= $h($collections[$item['collection_id']]['name']??'') ?></span><?php endif; ?>
        </div>
        <h3 class="wl-h-card-name"><?= $h($item['service_name']) ?></h3>
        <div class="wl-h-card-meta">⭐ <?= number_format($item['rating'],1) ?> · <?= $item['booking_type']==='slot'?'Per session':'Full day' ?></div>
        <div class="wl-h-card-foot">
          <span class="wl-h-card-price"><?= $moneyRange($item) ?></span>
          <span style="font-size:11px;font-weight:600;color:var(--c-strong)">View →</span>
        </div>
        <?php if($item['notes']): ?><div class="wl-h-card-note">💬 "<?= $h($item['notes']) ?>"</div><?php endif; ?>
      </div>
    </article>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</section>
<?php endforeach; ?>

</body></html>
