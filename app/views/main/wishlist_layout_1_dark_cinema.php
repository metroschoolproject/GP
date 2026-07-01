<!--
Layout 1: "Dark Cinema" ✦
Direct descendant of the service catalog cards — dark translucent cards,
hero banner at top, collections as horizontal filter chips, 3-column grid.
Same gp-card DNA as the browse page.
-->
<?php
// ---- mock data for preview ----
$preview = true;
$collections = [
  ['id' => null, 'name' => 'All Saved', 'item_count' => 5, 'is_default' => true],
  ['id' => 1, 'name' => 'Venues I Love', 'item_count' => 2, 'is_default' => false],
  ['id' => 2, 'name' => 'Photographers', 'item_count' => 2, 'is_default' => false],
  ['id' => 3, 'name' => 'Decor Ideas', 'item_count' => 1, 'is_default' => false],
];
$mockItems = [
  ['favorite_id'=>1,'service_id'=>42,'service_name'=>"Governor's Residence",'supplier_name'=>'HsuHive','category'=>'Venue','price_min'=>70000,'price_max'=>600000,'image'=>'','rating'=>4.5,'review_count'=>12,'booking_type'=>'slot','is_active'=>true,'notes'=>'Loved the garden','collection_id'=>1],
  ['favorite_id'=>2,'service_id'=>49,'service_name'=>'Zephyr Sein Lann So Pyay','supplier_name'=>'zaw moe','category'=>'Venue','price_min'=>900000,'price_max'=>910000,'image'=>'','rating'=>4.2,'review_count'=>8,'booking_type'=>'slot','is_active'=>true,'notes'=>'','collection_id'=>1],
  ['favorite_id'=>3,'service_id'=>43,'service_name'=>'Aphrodite Wedding Planning','supplier_name'=>'HsuHive','category'=>'Decoration','price_min'=>3400000,'price_max'=>3400000,'image'=>'','rating'=>4.9,'review_count'=>15,'booking_type'=>'fullday','is_active'=>false,'notes'=>'','collection_id'=>null],
  ['favorite_id'=>4,'service_id'=>50,'service_name'=>'H & H Wedding Studio','supplier_name'=>'zaw moe','category'=>'Photography','price_min'=>200000,'price_max'=>2100000,'image'=>'','rating'=>4.7,'review_count'=>22,'booking_type'=>'slot','is_active'=>true,'notes'=>'Check availability','collection_id'=>2],
  ['favorite_id'=>5,'service_id'=>45,'service_name'=>'Dear Brides','supplier_name'=>'zaw moe','category'=>'Attire','price_min'=>800000,'price_max'=>1200000,'image'=>'','rating'=>4.3,'review_count'=>6,'booking_type'=>'fullday','is_active'=>true,'notes'=>'','collection_id'=>2],
];
$activeCollection = $_GET['collection'] ?? null;
$total = count($mockItems);
$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$money = fn($v) => 'MMK ' . number_format((float)$v, 0);
$moneyRange = function($i) use($money) {
  $min=(float)($i['price_min']??0); $max=(float)($i['price_max']??0);
  if($min>0&&$max>0&&$max>$min) return $money($min).' – '.$money($max);
  return $money($max>0?$max:$min);
};
$isLoggedIn = true;
$wishlistCount = $total;
$cartCount = 3;
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<link rel="icon" type="image/png" href="<?= URLROOT ?>/public/images/home/gp_logo.png">
<title>Layout 1: Dark Cinema — Golden Promise Wishlist</title>
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
body{overflow-x:hidden;background:var(--c-bg);color:var(--c-text);font-family:var(--font-body);font-size:14px;line-height:1.6;-webkit-font-smoothing:antialiased}
a{color:inherit;text-decoration:none} img{display:block;max-width:100%} button,input{font-family:var(--font-body);outline:none}

/* ══ HEADER ══ */
.gp-header{position:sticky;top:0;z-index:100;display:grid;grid-template-columns:auto 1fr auto;align-items:center;gap:24px;padding:16px var(--pad-x);border-bottom:1px solid rgba(184,154,109,.2);background:rgba(255,248,239,.94);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px)}
.gp-brand{display:flex;align-items:center;gap:12px;color:#211b17;font-size:18px;font-weight:800;white-space:nowrap}
.gp-brand-mark{display:grid;place-items:center;width:40px;height:40px;border-radius:50%;background:var(--c-strong);color:#fff4e6;font-size:14px;letter-spacing:1px}
.gp-header-nav{display:flex;align-items:center;justify-content:center;gap:4px}
.gp-header-nav a{padding:8px 18px;border-radius:999px;font-size:13px;font-weight:700;color:#51483f;transition:all .2s}
.gp-header-nav a:hover,.gp-header-nav a.active{color:var(--c-red);background:rgba(185,74,72,.08)}
.gp-header-actions{display:flex;align-items:center;gap:12px;justify-content:flex-end}

/* ══ HERO BANNER ══ */
.wl-hero{position:relative;min-height:260px;display:flex;align-items:center;justify-content:center;padding:48px var(--pad-x);overflow:hidden}
.wl-hero::before{content:'';position:absolute;inset:0;background:linear-gradient(160deg,rgba(74,48,33,.92) 0%,rgba(74,48,33,.78) 50%,rgba(74,48,33,.88) 100%);z-index:0}
.wl-hero-bg{position:absolute;inset:0;z-index:-1;opacity:.18;background:radial-gradient(circle at 30% 50%,#d8b46a 0%,transparent 60%),radial-gradient(circle at 70% 30%,#b94a48 0%,transparent 40%)}
.wl-hero-inner{position:relative;z-index:1;text-align:center}
.wl-hero-overline{font-size:11px;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:var(--c-gold);margin-bottom:8px}
.wl-hero h1{font-family:var(--font-display);font-size:clamp(32px,5vw,52px);font-weight:600;color:#f8efe5;line-height:1;margin-bottom:8px}
.wl-hero p{font-size:14px;color:rgba(255,248,239,.54);max-width:480px;margin:0 auto}

/* ══ COLLECTION CHIPS ══ */
.wl-chips{padding:28px var(--pad-x) 8px;display:flex;gap:6px;flex-wrap:wrap;justify-content:center}
.wl-chip{display:inline-flex;align-items:center;gap:6px;padding:7px 16px;border-radius:999px;font-size:12px;font-weight:600;cursor:pointer;transition:all .18s;border:1px solid transparent}
.wl-chip--off{background:rgba(245,232,217,.82);border-color:rgba(118,90,70,.16);color:var(--c-muted)}
.wl-chip--off:hover{background:var(--c-white);color:var(--c-strong)}
.wl-chip--on{background:rgba(216,180,106,.18);border-color:rgba(216,180,106,.32);color:#765a46}
.wl-chip-badge{font-size:10px;background:rgba(118,90,70,.10);color:var(--c-muted);padding:2px 7px;border-radius:999px}
.wl-chip--on .wl-chip-badge{background:rgba(216,180,106,.22);color:#765a46}

/* ══ DARK CARD GRID ══ */
.wl-scene{padding:36px var(--pad-x) 80px;background:linear-gradient(180deg,#f5e8d9 0%,#eee0d0 40%,#e7d5c1 100%)}
.wl-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:24px 20px;align-items:start}
.wl-card{position:relative;background:rgba(74,48,33,.84);border-radius:18px;padding:18px;backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px);display:flex;flex-direction:column;height:340px;box-shadow:0 10px 30px rgba(0,0,0,.12);transition:.35s ease;cursor:pointer}
.wl-card:hover{transform:translateY(-4px);box-shadow:0 20px 40px rgba(0,0,0,.18)}
.wl-card-ribbon{position:absolute;top:0;left:20px;background:var(--c-red);color:#fcf8f5;font-size:9px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;padding:4px 10px 6px;border-radius:0 0 6px 6px}
.wl-card-body{display:flex;flex-direction:column;height:100%}
.wl-card-sup{color:#d7c7b8;font-size:11px;letter-spacing:.08em;text-transform:uppercase;margin-bottom:4px;display:flex;align-items:center;gap:6px}
.wl-card-col{font-size:9px;padding:2px 8px;border-radius:999px;border:1px solid rgba(216,180,106,.28);color:var(--c-gold)}
.wl-card-name{font-family:var(--font-display);font-size:22px;line-height:1.15;font-weight:600;color:#f8efe5;margin-bottom:4px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.wl-card-desc{font-size:11px;color:rgba(248,239,229,.45);display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;flex:1;margin-bottom:8px}
.wl-card-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:6px;margin-bottom:10px}
.wl-stat{text-align:center;padding:6px 4px}
.wl-stat+.wl-stat{border-left:1px solid rgba(252,248,245,.16)}
.wl-stat strong{color:#fff5eb;font-size:12px;display:block}
.wl-stat span{color:#cdbba8;font-size:9px}
.wl-card-thumb{border:1px solid rgba(252,248,245,.14);border-radius:10px;overflow:hidden;height:130px;flex-shrink:0;background:linear-gradient(160deg,rgba(252,248,245,.05),rgba(0,0,0,.10))}
.wl-card-thumb img{width:100%;height:100%;object-fit:cover}
.wl-card-note{margin-top:6px;font-size:10px;color:rgba(216,180,106,.8);font-style:italic}

/* heart */
.wl-heart{position:absolute;top:14px;right:14px;z-index:10;display:grid;place-items:center;width:32px;height:32px;border-radius:50%;border:none;background:rgba(0,0,0,.32);backdrop-filter:blur(8px);color:var(--c-heart);cursor:pointer;font-size:14px;transition:all .2s}
.wl-heart:hover{transform:scale(1.14)}

/* empty */
.wl-empty{grid-column:1/-1;text-align:center;padding:80px 24px;border:1px dashed rgba(109,76,91,.18);border-radius:20px;background:rgba(250,245,239,.60)}
.wl-empty h3{font-family:var(--font-display);font-size:30px;margin-bottom:8px}
.wl-empty p{color:var(--c-accent);margin-bottom:20px}

/* responsive */
@media(max-width:1000px){.wl-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:700px){.wl-grid{grid-template-columns:1fr}.wl-card{height:auto}}
</style>
</head><body>

<header class="gp-header">
  <a class="gp-brand" href="#"><span class="gp-brand-mark">G</span><span>Golden Promise</span></a>
  <nav class="gp-header-nav"><a href="#">Home</a><a href="#">Services</a><a href="#">Packages</a></nav>
  <div class="gp-header-actions">
    <span style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:999px;border:1px solid rgba(229,91,91,.18);background:rgba(229,91,91,.04);color:var(--c-heart);font-size:13px;font-weight:700">♥ <?= $wishlistCount ?></span>
    <span style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:999px;border:1px solid #ead8c7;background:#fcf8f5;color:#765a46;font-size:13px;font-weight:700">🛒 <?= $cartCount ?></span>
    <span style="display:grid;place-items:center;width:40px;height:40px;border-radius:50%;background:#765a46;color:#fff4e6;font-size:12px;font-weight:800">H</span>
  </div>
</header>

<!-- HERO -->
<div class="wl-hero"><div class="wl-hero-bg"></div>
  <div class="wl-hero-inner">
    <div class="wl-hero-overline">Your Collection</div>
    <h1>My Wishlist</h1>
    <p><?= $total ?> saved services — curate your dream wedding team</p>
  </div>
</div>

<!-- COLLECTION CHIPS -->
<div class="wl-chips">
  <?php foreach($collections as $col):
    $cid = $col['id']; $isOn = ($activeCollection === ($cid ? (int)$cid : null));
  ?>
    <a class="wl-chip <?= $isOn ? 'wl-chip--on' : 'wl-chip--off' ?>" href="?collection=<?= $cid ?? '' ?>">
      <?= $col['is_default'] ? '📋' : '📁' ?> <?= $h($col['name']) ?>
      <span class="wl-chip-badge"><?= (int)($col['item_count']??0) ?></span>
    </a>
  <?php endforeach; ?>
  <span class="wl-chip wl-chip--off" style="border-style:dashed">+ New</span>
</div>

<!-- CARDS -->
<div class="wl-scene"><div class="wl-grid">
  <?php foreach($mockItems as $item): ?>
  <article class="wl-card">
    <button class="wl-heart">♥</button>
    <?php if(!$item['is_active']): ?><span class="wl-card-ribbon">Unavailable</span><?php endif; ?>
    <div class="wl-card-body">
      <div class="wl-card-sup">
        <?= $h($item['supplier_name']) ?>
        <?php if($item['collection_id']): ?><span class="wl-card-col">📁 <?= $h($collections[$item['collection_id']]['name']??'') ?></span><?php endif; ?>
      </div>
      <div class="wl-card-name"><?= $h($item['service_name']) ?></div>
      <div class="wl-card-desc"><?= $h($item['category']) ?> · <?= $item['booking_type']==='slot'?'Per session':'Full day' ?></div>
      <div class="wl-card-stats">
        <div class="wl-stat"><strong>⭐ <?= number_format($item['rating'],1) ?></strong><span>Rating</span></div>
        <div class="wl-stat"><strong><?= $moneyRange($item) ?></strong><span>Price</span></div>
        <div class="wl-stat"><strong><?= $item['review_count'] ?></strong><span>Reviews</span></div>
      </div>
      <?php if($item['notes']): ?><div class="wl-card-note">💬 <?= $h($item['notes']) ?></div><?php endif; ?>
      <div class="wl-card-thumb"><div style="display:grid;place-items:center;height:100%;color:rgba(252,248,245,.12);font-size:40px">🖼</div></div>
    </div>
  </article>
  <?php endforeach; ?>
</div></div>

</body></html>
