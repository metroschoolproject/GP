<?php
$catalog  = $catalog  ?? ['services' => [], 'categories' => [], 'featured' => []];
$filters  = $filters  ?? ['search' => '', 'category' => 'all', 'sort' => 'featured', 'date' => '', 'price_min' => '', 'price_max' => ''];
$services   = $catalog['services']   ?? [];
$categories = $catalog['categories'] ?? [];
$featured   = $catalog['featured']   ?? [];

$hasActiveFilters =
    trim((string)($filters['search']    ?? '')) !== ''
    || !in_array(($filters['category']  ?? 'all'), ['', 'all'], true)
    || !in_array(($filters['sort']      ?? 'featured'), ['', 'featured'], true)
    || trim((string)($filters['date']   ?? '')) !== ''
    || trim((string)($filters['price_min'] ?? '')) !== ''
    || trim((string)($filters['price_max'] ?? '')) !== '';

$plain = function ($v) {
    $text = (string)$v;
    for ($i = 0; $i < 10; $i++) {
        $decoded = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if ($decoded === $text) break;
        $text = $decoded;
    }
    return $text;
};
$h          = fn($v) => htmlspecialchars($plain($v), ENT_QUOTES, 'UTF-8');
$money      = fn($v) => 'MMK ' . number_format((float)$v, 0);
$moneyRange = function ($s) use ($money) {
    $category = strtolower(trim((string)($s['category_slug'] ?? $s['category'] ?? '')));
    $isVenue = $category === 'venue';
    $min = (float)($s['price_min'] ?? $s['price'] ?? 0);
    $max = (float)($s['price_max'] ?? $s['customize_price'] ?? $s['display_price'] ?? $s['price'] ?? $min);

    if ($isVenue && $min > 0 && $max > 0 && $max > $min) {
        return $money($min) . ' – ' . $money($max);
    }

    return $money($s['display_price'] ?? $s['customize_price'] ?? $s['price_max'] ?? $s['price'] ?? 0);
};
$durationText = function ($s) {
    $type = $s['booking_type'] ?? 'fullday';
    $min  = (int)($s['duration_minutes'] ?? 0);
    if ($type === 'slot' && $min > 0) {
        $h = $min / 60;
        return $h >= 1 ? rtrim(rtrim(number_format($h, 1), '0'), '.') . ' hr' : $min . ' min';
    }
    return $type === 'flexible' ? 'Flexible' : 'Full day';
};
$pricingUnit = fn($s) => ($s['pricing_unit'] ?? 'per_session') === 'per_hour' ? '/hr' : '/session';

$activeCategory = $filters['category']  ?? 'all';
$activeSort     = $filters['sort']      ?? 'featured';
$activeDate     = $filters['date']      ?? '';
$activePriceMin = $filters['price_min'] ?? '';
$activePriceMax = $filters['price_max'] ?? '';
$detailDateQuery = $activeDate !== '' ? '?date=' . rawurlencode($activeDate) : '';

$serviceUrl = function (array $overrides = []) use ($filters) {
    $params = [
        'q' => $filters['search'] ?? '',
        'category' => $filters['category'] ?? 'all',
        'sort' => $filters['sort'] ?? 'featured',
        'date' => $filters['date'] ?? '',
        'price_min' => $filters['price_min'] ?? '',
        'price_max' => $filters['price_max'] ?? '',
        'page' => $_GET['page'] ?? 1,
    ];
    foreach ($overrides as $key => $value) {
        if ($value === null) {
            unset($params[$key]);
            continue;
        }
        $params[$key] = $value;
    }
    $params = array_filter($params, function ($value, $key) {
        if ($value === '' || $value === null) return false;
        if ($key === 'category' && $value === 'all') return false;
        if ($key === 'sort' && $value === 'featured') return false;
        if ($key === 'page' && (int)$value <= 1) return false;
        return true;
    }, ARRAY_FILTER_USE_BOTH);

    $query = http_build_query($params);
    return URLROOT . '/customerServices/service' . ($query !== '' ? '?' . $query : '');
};

$servicesPerPage = 9;
$totalServices   = count($services);
$totalPages      = max(1, (int)ceil($totalServices / $servicesPerPage));
$currentPage     = max(1, min($totalPages, (int)($_GET['page'] ?? 1)));
$pageOffset      = ($currentPage - 1) * $servicesPerPage;
$visibleServices = array_slice($services, $pageOffset, $servicesPerPage);

$isLoggedIn   = !empty($_SESSION['session_uid']);
$cartCount    = (int)($cartCount ?? 0);
$resetUrl = URLROOT . '/customerServices/service';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Services — Golden Promise</title>
<?php $publicCssVersion = file_exists(APPROOT . '/../public/css/app.css') ? filemtime(APPROOT . '/../public/css/app.css') : time(); ?>
<link rel="stylesheet" href="<?= URLROOT ?>/public/css/app.css?v=<?= $publicCssVersion ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400;1,600&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root{
  --c-bg:#f5e8d9;--c-white:#ffffff;--c-rule:#ead8c7;
  --c-strong:#765a46;--c-accent:#6f625a;--c-muted:#9b7d6b;
  --c-text:#211d1a;--c-pale:#b79c8b;
  --c-gold:#d8b46a;--c-red:#b94a48;
  --font-display:'Playfair Display',Georgia,serif;
  --font-body:'Poppins',system-ui,-apple-system,sans-serif;
  --pad-x:clamp(20px,5vw,72px);
  --ease:cubic-bezier(.19,1,.22,1);
  --header-h:73px;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{overflow-x:hidden;background:var(--c-bg);color:var(--c-text);font-family:var(--font-body);font-size:14px;line-height:1.6;-webkit-font-smoothing:antialiased}
a{color:inherit;text-decoration:none}
img{display:block;max-width:100%}
button,input,select{font-family:var(--font-body);outline:none}

/* ══ HEADER ══════════════════════════════════════════════ */
.gp-header{
  position:sticky;top:0;z-index:100;
  display:grid;grid-template-columns:auto 1fr auto;
  align-items:center;gap:24px;
  padding:16px var(--pad-x);
  border-bottom:1px solid rgba(184,154,109,.2);
  background:rgba(255,248,239,.94);
  backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);
}
.gp-brand{display:flex;align-items:center;gap:12px;color:#211b17;font-size:18px;font-weight:800;white-space:nowrap}
.gp-brand-mark{display:grid;place-items:center;width:40px;height:40px;border-radius:50%;background:var(--c-strong);color:#fff4e6;font-size:14px;letter-spacing:1px}
.gp-header-nav{display:flex;align-items:center;justify-content:center;gap:4px}
.gp-header-nav a{padding:8px 18px;border-radius:999px;font-size:13px;font-weight:700;color:#51483f;transition:all .2s}
.gp-header-nav a:hover,.gp-header-nav a.active{color:var(--c-red);background:rgba(185,74,72,.08)}
.gp-header-actions{display:flex;align-items:center;gap:12px;justify-content:flex-end}
.gp-cart-badge{display:inline-flex;align-items:center;gap:6px;padding:8px 14px 8px 10px;border-radius:999px;border:1px solid var(--c-rule);background:var(--c-white);color:var(--c-strong);font-size:13px;font-weight:700;transition:all .2s}
.gp-cart-badge:hover{border-color:var(--c-red);color:var(--c-red)}
.gp-cart-count{display:inline-flex;align-items:center;justify-content:center;min-width:20px;height:20px;padding:0 6px;border-radius:999px;background:var(--c-strong);color:#fff;font-size:10px;font-weight:700}
.gp-header-cta{display:inline-flex;align-items:center;justify-content:center;min-height:40px;padding:0 20px;border-radius:999px;border:none;background:var(--c-strong);color:#fffaf3;font-size:13px;font-weight:800;cursor:pointer;box-shadow:0 8px 24px rgba(118,90,70,.22);transition:all .2s}
.gp-header-cta:hover{background:var(--c-red);transform:translateY(-1px)}
.gp-profile-wrap{position:relative}
.gp-profile-btn{display:flex;align-items:center;gap:8px;padding:4px 12px 4px 4px;border-radius:999px;border:1px solid var(--c-rule);background:var(--c-white);cursor:pointer;color:var(--c-strong);font-family:var(--font-body);font-size:13px;font-weight:600;transition:all .2s}
.gp-profile-btn:hover{border-color:var(--c-red);color:var(--c-red)}
.gp-profile-avatar{display:grid;place-items:center;width:32px;height:32px;border-radius:50%;background:var(--c-strong);color:#fff4e6;font-size:12px;font-weight:800}
.gp-chevron{opacity:.6;transition:transform .2s}
.gp-profile-btn[aria-expanded="true"] .gp-chevron{transform:rotate(180deg)}
.gp-profile-menu{position:absolute;top:calc(100% + 8px);right:0;min-width:180px;padding:6px;border-radius:12px;border:1px solid var(--c-rule);background:var(--c-white);box-shadow:0 12px 35px rgba(15,23,42,.10);opacity:0;visibility:hidden;transform:translateY(-4px);transition:all .15s var(--ease);z-index:200}
.gp-profile-btn[aria-expanded="true"]+.gp-profile-menu{opacity:1;visibility:visible;transform:translateY(0)}
.gp-menu-item{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:8px;font-size:13px;font-weight:600;color:var(--c-text);transition:background .15s}
.gp-menu-item:hover{background:rgba(185,74,72,.06)}
.gp-menu-item--danger{color:var(--c-red)}
.gp-menu-item--danger:hover{background:rgba(185,74,72,.08)}

/* ══ SERVICES SEARCH + GRID ══════════════════════════════ */
.gp-scene{
    position:relative;
    padding:0;
    background:#fff;
}
.hero-banner{
    position:relative;
    min-height:calc(100vh - var(--header-h) - 70px);
    background:url('../public/uploads/serviceHero1.png');
    background-size:cover;
    background-position:center;
    background-repeat:no-repeat;

    /* Add this line below for a smooth crossfade effect */
    transition: background-image 0.8s ease-in-out; 

}
.hero-banner::before{
    content:'';
    position:absolute;
    inset:0;
    background:rgba(0,0,0,0.25);
}

.hero-overlay{
    position:absolute;
    inset:0;

    display:flex;
    flex-direction:column;
    justify-content:center;
    align-items:center;

    text-align:center;
    color:white;
    z-index:2;
}
.hero-banner::before{
    content:'';
    position:absolute;
    inset:0;
    background:
        linear-gradient(
            rgba(0,0,0,.65),
            rgba(0,0,0,.45)
        );
}

.hero-overlay h1{
    font-family:'Playfair Display', serif;
    font-size:70px;
    font-weight:500;
    letter-spacing:6px;
    margin-bottom:10px;
}

.hero-overlay p{
    font-size:16px;
    letter-spacing:2px;
    margin-bottom:28px;
}

.hero-overlay .gp-float-bar{
  margin-top:0;
}

.hero-overlay h1,
.hero-overlay p,
.hero-overlay .gp-float-bar,
.hero-overlay .fb-search,
.hero-overlay .fb-controls > *{
  opacity:0;
  transform:translateY(18px) scale(.94);
}

.hero-overlay.is-in h1{
  animation:heroPopOut .72s var(--ease) .04s forwards;
}
.hero-overlay.is-in p{
  animation:heroPopOut .66s var(--ease) .18s forwards;
}
.hero-overlay.is-in .gp-float-bar{
  animation:heroPopOut .68s var(--ease) .30s forwards;
}
.hero-overlay.is-in .fb-search{
  animation:heroPopOut .62s var(--ease) .42s forwards;
}
.hero-overlay.is-in .fb-controls > *{
  animation:heroPopOut .52s var(--ease) forwards;
}
.hero-overlay.is-in .fb-controls > *:nth-child(1){animation-delay:.52s}
.hero-overlay.is-in .fb-controls > *:nth-child(2){animation-delay:.58s}
.hero-overlay.is-in .fb-controls > *:nth-child(3){animation-delay:.64s}
.hero-overlay.is-in .fb-controls > *:nth-child(4){animation-delay:.70s}
.hero-overlay.is-in .fb-controls > *:nth-child(5){animation-delay:.76s}
.hero-overlay.is-in .fb-controls > *:nth-child(6){animation-delay:.82s}
.hero-overlay.is-in .fb-controls > *:nth-child(7){animation-delay:.88s}
.hero-overlay.is-in .fb-controls > *:nth-child(8){animation-delay:.94s}

@keyframes heroPopOut{
  0%{opacity:0;transform:translateY(18px) scale(.94)}
  68%{opacity:1;transform:translateY(-4px) scale(1.035)}
  100%{opacity:1;transform:translateY(0) scale(1)}
}

@media (prefers-reduced-motion:reduce){
  .hero-overlay h1,
  .hero-overlay p,
  .hero-overlay .gp-float-bar,
  .hero-overlay .fb-search,
  .hero-overlay .fb-controls > *{
    opacity:1;
    transform:none;
    animation:none !important;
  }
}
/* blurred background image layer */
.gp-scene-bg{
  display:none;
}
/* colour vignette / depth overlay */
.gp-scene-vignette{
  display:none;
}

/* ── SEARCH FILTER BAR ── */
.gp-float-bar{
  position:relative;
  order:1;
  margin:0 auto;
  z-index:35;
  display:flex;
  flex-direction:column;
  align-items:center;
  gap:10px;
  background:transparent;
  border:0;
  border-radius:12px;
  padding:10px;
  width:min(760px,calc(100vw - 32px));
  backdrop-filter:none;-webkit-backdrop-filter:none;
  box-shadow:none;
}
.supplier-marquee{
    height:70px;
    display:flex;
    align-items:center;
    overflow:hidden;
    background:#fff8ef;
    border-top:1px solid rgba(118,90,70,.12);
    border-bottom:1px solid rgba(118,90,70,.12);
}

.supplier-track{
    display:flex;
    width:max-content;
    animation:supplierScroll 28s linear infinite;
}

.supplier-item{
    flex-shrink:0;
    margin:0 50px;
    font-size:14px;
    font-weight:600;
    color:#765a46;
    letter-spacing:.08em;
    text-transform:uppercase;
}

.supplier-item::after{
    content:"•";
    margin-left:50px;
    color:#d8b46a;
}

@keyframes supplierScroll{
    from{
        transform:translateX(0);
    }
    to{
        transform:translateX(-50%);
    }
}

.supplier-marquee:hover .supplier-track{
    animation-play-state:paused;
}
.fb-search{
    display:flex;
    align-items:center;
    gap:10px;
    width:min(560px,100%);
    min-height:50px;
    padding:0 8px 0 20px;
    background:rgba(245,232,217,.88);
    border:0.5px solid rgba(118,90,70,.24);
    border-radius:14px;
    overflow:hidden;
}

.fb-search svg{flex-shrink:0;opacity:.72;color:#765a46}
.fb-search input{
    flex:1;
    min-width:0;
    width:auto;
}
/* ရှာဖွေရေးသေတ္တာထဲ စာရိုက်လျှင် ညိုရင့်ရောင်ပြောင်းရန် */
.fb-search input {
    color: #4f382a !important;
}
.fb-search input::placeholder{color:rgba(118,90,70,.58)}
.fb-search input:focus{outline:none}

.fb-controls{
  display:flex;
  align-items:center;
  justify-content:center;
  gap:6px;
  width:100%;
  min-width:0;
  white-space:nowrap;
  overflow-x:auto;
  scrollbar-width:none;
  padding:0 2px;
}
.fb-controls::-webkit-scrollbar{display:none}
.fb-div{display:none}

.fb-chip{
  flex-shrink:0;
  display:flex;align-items:center;gap:5px;
  background:rgba(245,232,217,.82);
  border:0.5px solid rgba(118,90,70,.20);
  border-radius:999px;padding:7px 13px;
  color:#765a46;font-size:11px;font-weight:600;
  cursor:pointer;transition:all .15s;
  box-shadow:0 10px 24px rgba(43,31,24,.12);
}
.fb-chip:hover{background:rgba(255,248,239,.94);color:#4f382a}
.fb-chip.on{background:rgba(216,180,106,.88);border-color:rgba(255,248,239,.58);color:#4f382a}
.fb-budget{gap:6px}
.fb-budget input{
  width:72px;
  border:none;
  background:transparent;
  color:#4f382a;
  font-size:11px;
  font-weight:600;
}
.fb-budget input::placeholder{color:rgba(118,90,70,.58)}
.fb-budget input:focus{outline:none}
.fb-budget-sep{color:rgba(118,90,70,.54);font-size:10px}

.fb-select{
  flex-shrink:0;
  background:rgba(245,232,217,.82);
  border:0.5px solid rgba(118,90,70,.20);
  border-radius:999px;
  padding:7px 28px 7px 14px;
  color:#765a46;
  font-size:11px;font-weight:600;
  cursor:pointer;appearance:none;
  min-width:128px;max-width:172px;
  box-shadow:0 10px 24px rgba(43,31,24,.12);
}
.fb-sort{min-width:108px;max-width:132px}
.fb-find{
    display:flex;
    justify-content:center;
    align-items:center;
    width:38px;
    height:38px;
    border:none;
    border-radius:10px;
    background:#765a46;
    color:#fff;      /* SVG uses currentColor */
    cursor:pointer;
    flex-shrink:0;
}

/* Filter chip တွေနဲ့ Select dropdown တွေကြားက Shadow ကို ဖျောက်ရန် */
.fb-chip, 
.fb-select {
    box-shadow: none !important;
}



.fb-find:hover{
    background:#4f382a;
}
.fb-find svg {
    stroke: #ffffff !important;
    display: block;
}
/* ဘယ်ဘက်အခြမ်းက search icon ကို ဖျောက်ရန် */
.fb-search > svg:first-of-type {
    display: none !important;
}


/* ── TRACK + CARDS ── */
.gp-track-wrap{
    position:relative;
    width:100%;
    padding:64px var(--pad-x);
    z-index:1;

    /* Theme gradient */
    background:linear-gradient(
        180deg,
        #fff8ef 0%,
        #f5e8d9 35%,
        #eee0d0 70%,
        #e7d5c1 100%
    );
}
.gp-track{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:24px 20px;
    align-items:start;
}


.gp-card{
    background:rgba(74,48,33,.84);
    border-radius:18px;
    padding:18px;
    overflow:hidden;
    cursor:pointer;
    backdrop-filter:blur(14px);
    -webkit-backdrop-filter:blur(14px);

    display:flex;
    flex-direction:column;

    height:360px;
    min-height:360px;

    border:none;
    box-shadow:
        0 10px 30px rgba(0,0,0,.12);

    transition:.35s ease;
}
.gp-card:focus-visible{
    outline:2px solid rgba(216,180,106,.78);
    outline-offset:3px;
}




@keyframes cardFlyIn{
    0%{opacity:0;transform:translate3d(0,34px,0) rotateX(9deg) scale(.96)}
    65%{opacity:1;transform:translate3d(0,-5px,0) rotateX(-2deg) scale(1.01)}
    100%{opacity:1;transform:translate3d(0,0,0) rotateX(0) scale(1)}
}
.gp-card:nth-child(2){animation-delay:.05s}
.gp-card:nth-child(3){animation-delay:.10s}
.gp-card:nth-child(4){animation-delay:.15s}
.gp-card:nth-child(5){animation-delay:.20s}
.gp-card:nth-child(6){animation-delay:.25s}
.gp-card:nth-child(7){animation-delay:.30s}
.gp-card:nth-child(8){animation-delay:.35s}
.gp-card:nth-child(9){animation-delay:.40s}




@media(max-width:1000px){

.gp-track{
    grid-template-columns:repeat(2,1fr);
}



}
@media(max-width:700px){

.gp-track{
    grid-template-columns:1fr;
}



}


.gc-grad{display:none}
.gc-badge{display:none}

.gc-body{
    display:flex;
    flex-direction:column;
    height:100%;
}
.gc-top{
    margin-bottom:12px;
}
.gc-head{
    display:block;
}

.gc-thumb{
    display:none;
}

.gc-thumb img{
    width:100%;
    height:100%;
    object-fit:cover;
}

.gc-head-text{
    flex:1;
    min-width:0;
}
.gc-sup{
    color:#d7c7b8;
    font-size:11px;
    letter-spacing:.08em;
    text-transform:uppercase;
}
.gc-name{
    font-family:'Playfair Display', serif;
    font-size:24px;
    line-height:1.1;
    font-weight:600;
    color:#f8efe5;
    margin-bottom:6px;
    display:-webkit-box;
    -webkit-line-clamp:2;
    -webkit-box-orient:vertical;
    overflow:hidden;
}
.gc-tags{
    display:flex;
    flex-wrap:wrap;
    gap:6px;
    margin-bottom:12px;
}
.gc-tag{
    background:rgba(255,255,255,.08);
    color:#efe4d7;

    padding:5px 9px;
    border-radius:999px;

    font-size:10px;
    font-weight:500;

    border:1px solid rgba(255,255,255,.08);
}
.gc-stats{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:6px;

    margin-bottom:12px;
}
.gc-stat{
    background:transparent;
    border-radius:0;
    padding:4px 6px;

    text-align:center;
    border:none;
}
.gc-stat+.gc-stat{border-left:1px solid rgba(255,255,255,.20)}
.gc-stat strong{
    color:#fff5eb;
    font-size:12px;
    font-weight:600;
    display:block;
    overflow:hidden;
    text-overflow:ellipsis;
    white-space:nowrap;
    
}
.gc-stat span{
    color:#cdbba8;
    font-size:9px;
}
.gc-stat svg{flex:0 0 auto;color:#020304}
.gc-image-frame{
    margin-top:auto;

    border:1px solid rgba(255,255,255,.16);
    border-radius:10px;
    overflow:hidden;

    height:170px;

    padding:0;
}

.gc-image-frame img{
    width:100%;
    height:100%;
    object-fit:cover;

    border-radius:9px;
}
@media (prefers-reduced-motion:reduce){
  .gp-card{
    opacity:1;
    animation:none !important;
    transform:none !important;
  }
}

.gp-pagination{
  display:flex;
  justify-content:center;
  align-items:center;
  gap:8px;
  padding:0 var(--pad-x) 76px;
  flex-wrap:wrap;
}
.gp-page-link{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  min-width:38px;
  height:38px;
  padding:0 13px;
  border-radius:12px;
  border:1px solid rgba(118,90,70,.16);
  background:rgba(255,248,239,.74);
  color:#6f625a;
  font-size:12px;
  font-weight:700;
  transition:transform .18s var(--ease), background .18s, color .18s, border-color .18s;
}
.gp-page-link:hover{transform:translateY(-2px);background:#fff;border-color:rgba(118,90,70,.28);color:#4f382a}
.gp-page-link.is-active{background:#211d1a;border-color:#211d1a;color:#fff}
.gp-page-link.is-edge{padding:0 16px}


/* ── BOTTOM HUD: cats + dots ── */
.gp-hud{
  position:absolute;
  bottom:0;left:0;right:0;
  z-index:30;
  display:flex;flex-direction:column;align-items:center;gap:0;
  padding-bottom:18px;
  pointer-events:none;
}
.gp-cats{
  pointer-events:auto;
  display:flex;gap:4px;align-items:center;
  padding:5px 10px;
  background:rgba(8,5,2,.42);
  backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);
  border-radius:999px;
  border:0.5px solid rgba(255,255,255,.07);
  max-width:calc(100vw - 32px);
  overflow-x:auto;scrollbar-width:none;
  margin-bottom:10px;
}
.gp-cats::-webkit-scrollbar{display:none}
.gp-cat-btn{
  flex-shrink:0;
  font-size:11px;font-weight:600;
  padding:5px 14px;border-radius:999px;
  color:rgba(255,255,255,.48);
  cursor:pointer;transition:all .18s;
  text-decoration:none;border:none;background:transparent;
}
.gp-cat-btn:hover{color:#fff}
.gp-cat-btn.on{background:rgba(216,180,106,.20);color:#f3d9a4;border:0.5px solid rgba(216,180,106,.36)}

.gp-dots{
  display:none;
  position:absolute;
  left:50%;
  bottom:18px;
  transform:translateX(-50%);
  z-index:35;
  pointer-events:auto;
  display:flex;gap:6px;
}
.gp-dot{
  width:6px;height:6px;border-radius:50%;
  background:rgba(255,255,255,.24);
  border:none;cursor:pointer;padding:0;
  transition:all .35s;
}
.gp-dot.on{width:22px;border-radius:3px;background:#d8b46a}

/* ── AUTO-PLAY PROGRESS BAR ── */
.gp-progress{
  position:absolute;bottom:0;left:0;
  height:3px;background:rgba(216,180,106,.75);
  border-radius:0 3px 3px 0;z-index:40;
}

/* ══ BELOW: remaining grid ═══════════════════════════════ */
.gp-below{padding:52px var(--pad-x) 72px}
.gp-below-head{display:flex;align-items:flex-end;justify-content:space-between;gap:16px;margin-bottom:28px}
.gp-below-title{font-family:var(--font-display);font-size:clamp(28px,3vw,38px);font-weight:600;color:var(--c-text);line-height:1}
.gp-below-count{font-size:13px;color:var(--c-pale);padding-bottom:4px}

.gp-grid{
    display:grid;
    grid-template-columns:repeat(3, minmax(0,1fr));
    gap:28px 22px;
    align-items:start;
}
.gp-gc:hover{transform:translateY(-4px);box-shadow:0 24px 48px -12px rgba(118,90,70,.18)}
.gp-gc-img{display:block;position:relative;aspect-ratio:4/3;overflow:hidden;background:linear-gradient(160deg,#ede0d0,#ddcebb);flex-shrink:0}
.gp-gc-img img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;transition:transform .6s var(--ease)}
.gp-gc:hover .gp-gc-img img{transform:scale(1.06)}
.gp-gc-img-ph{position:absolute;inset:0;display:grid;place-items:center;color:var(--c-pale);opacity:.4}
.gp-gc-bdg{position:absolute;top:12px;left:12px;z-index:2;background:rgba(255,250,246,.92);backdrop-filter:blur(6px);border:1px solid rgba(185,74,72,.12);border-radius:999px;padding:4px 10px;font-size:10px;font-weight:700;color:var(--c-red);letter-spacing:.04em;text-transform:uppercase}
.gp-gc-body{padding:18px 20px 22px;flex:1;display:flex;flex-direction:column}
.gp-gc-top{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:6px}
.gp-gc-sup{font-size:11px;font-weight:600;color:var(--c-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;display:flex;align-items:center;gap:4px}
.gp-gc-rat{display:flex;align-items:center;gap:4px;font-size:11px;font-weight:700;color:#d8b46a;flex-shrink:0}
.gp-gc-name{font-family:var(--font-display);font-size:20px;font-weight:600;line-height:1.1;color:var(--c-text);margin-bottom:4px}
.gp-gc-name a{transition:color .2s}
.gp-gc:hover .gp-gc-name a{color:var(--c-strong)}
.gp-gc-desc{font-size:12px;line-height:1.6;color:var(--c-accent);display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;flex:1}
.gp-gc-meta{display:flex;align-items:center;gap:10px;margin:8px 0 10px;font-size:11px;color:var(--c-muted)}
.gp-gc-meta span{display:inline-flex;align-items:center;gap:4px}
.gp-gc-foot{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:10px;padding-top:12px;border-top:1px solid var(--c-rule)}
.gp-gc-price{display:block;font-family:var(--font-display);font-size:clamp(18px,2vw,22px);font-weight:600;color:var(--c-red);line-height:1}
.gp-gc-unit{display:block;margin-top:1px;font-size:10px;color:var(--c-pale);font-weight:500}
.gp-gc-btn{display:inline-flex;align-items:center;gap:6px;height:36px;padding:0 16px;border-radius:999px;border:1px solid var(--c-rule);background:var(--c-white);color:var(--c-strong);font-size:12px;font-weight:700;cursor:pointer;transition:all .2s var(--ease);white-space:nowrap;text-decoration:none}
.gp-gc-btn:hover{background:var(--c-red);color:#fff;border-color:var(--c-red);transform:translateX(2px)}

/* empty */
.gp-empty{grid-column:1/-1;border:1px dashed rgba(109,76,91,.18);border-radius:20px;padding:64px 24px;text-align:center;background:rgba(250,245,239,.60)}
.gp-empty h3{font-family:var(--font-display);font-size:32px;font-weight:600;color:var(--c-text);margin-bottom:8px}
.gp-empty p{color:var(--c-accent);font-size:14px;max-width:480px;margin:0 auto}
.gp-empty-btns{margin-top:24px;display:flex;gap:10px;justify-content:center;flex-wrap:wrap}
.gp-ebtn{display:inline-flex;align-items:center;gap:6px;height:44px;padding:0 24px;border-radius:999px;font-size:14px;font-weight:700;cursor:pointer;border:none;transition:all .2s var(--ease)}
.gp-ebtn.p{background:var(--c-red);color:#fff}.gp-ebtn.p:hover{background:#8f2e2c;transform:translateY(-2px)}
.gp-ebtn.s{background:#faf5ef;color:var(--c-accent);border:1px solid var(--c-rule)}.gp-ebtn.s:hover{border-color:var(--c-strong);color:var(--c-strong)}

/* active filter chips */
.gp-chips{display:flex;gap:8px;flex-wrap:wrap;padding:20px var(--pad-x) 4px}
.gp-track-wrap .gp-chips{padding:0 0 22px}
.gp-chip{display:inline-flex;align-items:center;gap:6px;padding:6px 8px 6px 14px;border-radius:999px;background:rgba(216,180,106,.12);border:1px solid rgba(216,180,106,.26);font-size:12px;font-weight:600;color:#765a46}
.gp-chip-x{display:grid;place-items:center;width:18px;height:18px;border-radius:50%;border:none;background:rgba(216,180,106,.18);color:#765a46;cursor:pointer;font-size:10px;font-weight:700;transition:all .15s}
.gp-chip-x:hover{background:var(--c-gold);color:#fff}

/* scroll reveal */
.rev{opacity:0;transform:translateY(24px);transition:opacity .7s var(--ease),transform .7s var(--ease)}
.rev.in{opacity:1;transform:translateY(0)}
.rev-d1{transition-delay:.06s}.rev-d2{transition-delay:.12s}.rev-d3{transition-delay:.20s}
.rev-d4{transition-delay:.28s}.rev-d5{transition-delay:.36s}

/* footer */
.gp-footer{padding:28px var(--pad-x);border-top:1px solid var(--c-rule);display:flex;align-items:center;justify-content:space-between;gap:16px;font-size:12px;color:var(--c-pale)}



/* ── RESPONSIVE ── */
@media(max-width:900px){
  .gp-header{grid-template-columns:auto auto;justify-content:space-between}
  .gp-header-nav{display:none}
  .gp-scene{min-height:calc(100svh - 65px)}
}
@media(max-width:700px){
  .gp-header{padding:12px var(--pad-x)}
  .gp-brand-mark{width:34px;height:34px;font-size:12px}
  .gp-brand{font-size:15px}
  .gp-scene{min-height:calc(100svh - 59px);padding:28px 12px 44px}
  .gp-float-bar{border-radius:12px;width:100%;padding:8px}
  .fb-search{min-height:46px;padding:0 16px}
  .fb-search input{font-size:14px}
  .fb-controls{justify-content:flex-start}
  .gp-track{grid-template-columns:1fr;gap:16px}
  .gp-card{height:330px;min-height:330px;padding:16px;border-radius:16px}
  .gc-top{margin-bottom:10px}
  .gc-name{font-size:23px}
  .gc-tags{gap:6px;margin-bottom:10px}
  .gc-tag{padding:5px 9px;font-size:10px}
  .gc-stats{margin-bottom:10px}
  .gc-stat{padding:4px 5px}
  .gc-stat strong{font-size:12px}
  .gc-stat span{font-size:9px}
  .gc-image-frame{height:150px}
}
@media(max-width:480px){
  :root{--pad-x:16px}
  .gp-footer{flex-direction:column;align-items:flex-start}
}
/* Filter အသေးလေးများ (Date, Min-Max, Categories, Sort) ၏ ထောင့်ဝိုင်းနှုန်းကို လျှော့ချရန် */
.fb-chip, 
.fb-select {
    border-radius: 8px !important;
}


@keyframes filterSettle{

    from{
        opacity:0;
        transform:translateY(-18px) scale(.97);
    }

    60%{
        opacity:1;
        transform:translateY(4px) scale(1.01);
    }

    to{
        opacity:1;
        transform:translateY(0) scale(1);
    }

}
</style>
</head>
<body>

<!-- HEADER -->
<header class="gp-header">
  <a class="gp-brand" href="<?= URLROOT ?>/main/index">
    <span class="gp-brand-mark">G</span>
    <span>Golden Promise</span>
  </a>
  <nav class="gp-header-nav" aria-label="Main navigation">
    <a href="<?= URLROOT ?>/main/index">Home</a>
    <a class="active" href="<?= URLROOT ?>/customerServices/service">Services</a>
    <a href="<?= URLROOT ?>/customerServices/packages">Packages</a>
  </nav>
  <div class="gp-header-actions">
    <a class="gp-cart-badge" href="<?= URLROOT ?>/cart" aria-label="Cart">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
      <?php if ($cartCount > 0): ?>
        <span class="gp-cart-count"><?= $cartCount ?></span>
      <?php endif; ?>
    </a>
    <?php if ($isLoggedIn): ?>
    <div class="gp-profile-wrap">
      <button class="gp-profile-btn" type="button" aria-expanded="false">
        <span class="gp-profile-avatar"><?= strtoupper(substr($_SESSION['session_name'] ?? 'U', 0, 1)) ?></span>
        <span style="white-space:nowrap;max-width:100px;overflow:hidden;text-overflow:ellipsis"><?= $h(explode(' ', $_SESSION['session_name'] ?? 'User')[0]) ?></span>
        <svg class="gp-chevron" width="10" height="6" viewBox="0 0 10 6" fill="none"><path d="M1 1l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
      </button>
      <div class="gp-profile-menu">
        <a class="gp-menu-item" href="<?= URLROOT ?>/booking/myBookings">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
          My Bookings
        </a>
        <a class="gp-menu-item gp-menu-item--danger" href="<?= URLROOT ?>/users/logout">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
          Logout
        </a>
      </div>
    </div>
    <?php else: ?>
    <a class="gp-header-cta" href="<?= URLROOT ?>/users/auth">Sign in</a>
    <?php endif; ?>
  </div>
</header>

<main>

<!-- ══ SEARCH + SERVICES GRID ═══════════════════════════ -->
<section class="gp-scene" id="gpScene" aria-label="Service cards">
  <div class="hero-banner">
    <div class="hero-overlay">
      <h1>SPECIAL OCCASION</h1>
      <p>Create unforgettable moments with Golden Promise</p>
      <!-- Filter bar -->
      <form class="gp-float-bar" method="GET" action="<?= URLROOT ?>/customerServices/service" role="search">
        <div class="fb-search">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
          <input type="search" name="q" value="<?= $h($filters['search'] ?? '') ?>" placeholder="Search services…" aria-label="Search">
          <button class="fb-find" type="submit" aria-label="Find services">
    <svg width="13" height="13" viewBox="0 0 24 24"
         fill="none"
         stroke="currentColor"
         stroke-width="2.5"
         stroke-linecap="round"
         stroke-linejoin="round">
        <circle cx="11" cy="11" r="8"/>
        <path d="m21 21-4.35-4.35"/>
    </svg>
</button>
        </div>
        <div class="fb-controls">
          <label class="fb-chip <?= $activeDate !== '' ? 'on' : '' ?>">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            <span><?= $activeDate !== '' ? $h(date('M j', strtotime($activeDate))) : 'Date' ?></span>
            <input type="date" name="date" value="<?= $h($activeDate) ?>" min="<?= date('Y-m-d') ?>" id="datePick" style="position:absolute;opacity:0;pointer-events:none;width:1px;height:1px">
          </label>
          <label class="fb-chip fb-budget <?= ($activePriceMin !== '' || $activePriceMax !== '') ? 'on' : '' ?>">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            <input type="number" name="price_min" value="<?= $h($activePriceMin) ?>" min="0" step="1000" placeholder="Min" aria-label="Minimum budget">
            <span class="fb-budget-sep">–</span>
            <input type="number" name="price_max" value="<?= $h($activePriceMax) ?>" min="0" step="1000" placeholder="Max" aria-label="Maximum budget">
          </label>
          <div class="fb-div"></div>
          <?php if (!empty($categories)): ?>
          <select class="fb-select" name="category">
            <option value="all" <?= $activeCategory === 'all' ? 'selected' : '' ?>>All categories</option>
            <?php foreach ($categories as $cat):
              $slug = $cat['slug'] ?? strtolower($cat['name'] ?? '');
            ?>
              <option value="<?= $h($slug) ?>" <?= $activeCategory === $slug ? 'selected' : '' ?>><?= $h($cat['name'] ?? '') ?></option>
            <?php endforeach; ?>
          </select>
          <div class="fb-div"></div>
          <?php endif; ?>
          <select class="fb-select fb-sort" name="sort" aria-label="Sort services">
            <option value="featured" <?= $activeSort === 'featured' ? 'selected' : '' ?>>Featured</option>
            <option value="price_low" <?= $activeSort === 'price_low' ? 'selected' : '' ?>>Price low</option>
            <option value="price_high" <?= $activeSort === 'price_high' ? 'selected' : '' ?>>Price high</option>
            <option value="newest" <?= $activeSort === 'newest' ? 'selected' : '' ?>>Newest</option>
            <option value="rating" <?= $activeSort === 'rating' ? 'selected' : '' ?>>Rating</option>
          </select>
          <div class="fb-div"></div>
          
        </div>
      </form>
    </div>

  </div>
  <section class="supplier-marquee">
    <div class="supplier-track">
        <?php
        $supplierNames = [];
        foreach ($services as $service) {
            $name = trim($service['supplier_name'] ?? '');
            if ($name !== '') {
                $supplierNames[$name] = true;
            }
        }

        $supplierNames = array_keys($supplierNames);
        ?>

        <?php for ($i = 0; $i < 2; $i++): ?>
            <?php foreach ($supplierNames as $supplier): ?>
                <span class="supplier-item">
                    <?= $h($supplier) ?>
                </span>
            <?php endforeach; ?>
        <?php endfor; ?>
    </div>
</section>
<div id="filterHolder"></div>




  <div class="gp-track-wrap" id="trackWrap">
  <!-- Active filter chips -->
  <?php if ($hasActiveFilters): ?>
  <div class="gp-chips">
    <?php if (trim((string)($filters['search'] ?? '')) !== ''): ?>
    <span class="gp-chip">"<?= $h($filters['search']) ?>"
      <a class="gp-chip-x" href="<?= $h($serviceUrl(['q' => null, 'page' => null])) ?>">✕</a>
    </span>
    <?php endif; ?>
    <?php if ($activeDate !== ''): ?>
    <span class="gp-chip"><?= $h(date('M j, Y', strtotime($activeDate))) ?>
      <a class="gp-chip-x" href="<?= $h($serviceUrl(['date' => null, 'page' => null])) ?>">✕</a>
    </span>
    <?php endif; ?>
    <?php if ($activeCategory !== 'all'): ?>
    <span class="gp-chip"><?= $h($activeCategory) ?>
      <a class="gp-chip-x" href="<?= $h($serviceUrl(['category' => 'all', 'page' => null])) ?>">✕</a>
    </span>
    <?php endif; ?>
    <?php if ($activePriceMin !== '' || $activePriceMax !== ''): ?>
    <span class="gp-chip">MMK <?= $h($activePriceMin ?: '0') ?> – <?= $activePriceMax !== '' ? 'MMK ' . $h($activePriceMax) : '∞' ?>
      <a class="gp-chip-x" href="<?= $h($serviceUrl(['price_min' => null, 'price_max' => null, 'page' => null])) ?>">✕</a>
    </span>
    <?php endif; ?>
    <?php if ($activeSort !== 'featured'): ?>
    <span class="gp-chip"><?= $h(ucwords(str_replace('_', ' ', $activeSort))) ?>
      <a class="gp-chip-x" href="<?= $h($serviceUrl(['sort' => 'featured', 'page' => null])) ?>">✕</a>
    </span>
    <?php endif; ?>
  </div>
  <?php endif; ?>
  <div class="gp-track" id="gpTrack">
    
    <?php foreach ($visibleServices as $ci => $svc):
      $dUrl = URLROOT . '/customerServices/detail/' . (int)$svc['id'] . $detailDateQuery;
    ?>
      <article class="gp-card" data-idx="<?= $ci ?>" data-url="<?= $h($dUrl) ?>" data-img="<?= $h(trim((string)($svc['image'] ?? ''))) ?>" role="link" tabindex="0" aria-label="View details for <?= $h($svc['name'] ?? 'service') ?>">
        <div class="gc-body">
          <div class="gc-top">
            <div class="gc-head">
              <div class="gc-head-text">
                <div class="gc-name"><?= $h($svc['name'] ?? '') ?></div>
                <div class="gc-sup"><?= $h($svc['supplier_name'] ?? '') ?></div>
              </div>
            </div>
          </div>

          <div class="gc-tags">
            <span class="gc-tag"><?= $h($svc['category'] ?? 'Service') ?></span>
            <span class="gc-tag"><?= $h($durationText($svc)) ?></span>
            <span class="gc-tag"><?= $pricingUnit($svc) === '/hr' ? 'Per Hour' : 'Per Session' ?></span>
          </div>

          <div class="gc-stats">
            <div class="gc-stat">
              <strong><?= (float)($svc['rating'] ?? 0) > 0 ? number_format((float)$svc['rating'],1) : 'New' ?></strong>
              <span>Rating</span>
            </div>
            <div class="gc-stat">
              <strong><?= $moneyRange($svc) ?></strong>
              <span>Price</span>
            </div>
            <div class="gc-stat">
              <strong><?= $pricingUnit($svc) === '/hr' ? '/hr' : 'Each' ?></strong>
              <span>Rate</span>
            </div>
          </div>

          <div class="gc-image-frame">
            <?php if(trim((string)($svc['image'] ?? '')) !== ''): ?>
              <img src="<?= $h($svc['image']) ?>" alt="<?= $h($svc['name'] ?? '') ?>">
            <?php endif; ?>
          </div>
        </div>
      </article>
    <?php endforeach; ?> </div> </div> <?php if ($totalPages > 1): ?>
<nav class="gp-pagination" aria-label="Service pages">
  <?php if ($currentPage > 1): ?>
    <a class="gp-page-link is-edge" href="<?= $h($serviceUrl(['page' => $currentPage - 1])) ?>">Previous</a>
  <?php endif; ?>
  <?php for ($page = 1; $page <= $totalPages; $page++): ?>
    <a class="gp-page-link <?= $page === $currentPage ? 'is-active' : '' ?>" href="<?= $h($serviceUrl(['page' => $page])) ?>" aria-label="Page <?= $page ?>" <?= $page === $currentPage ? 'aria-current="page"' : '' ?>><?= $page ?></a>
  <?php endfor; ?>
  <?php if ($currentPage < $totalPages): ?>
    <a class="gp-page-link is-edge" href="<?= $h($serviceUrl(['page' => $currentPage + 1])) ?>">Next</a>
  <?php endif; ?>
</nav>
<?php endif; ?>

<div class="gp-dots" id="gpDots" aria-hidden="true"></div>



<!-- Empty state -->
<?php $remaining = []; ?>
<?php if (!empty($remaining) || empty($services)): ?>
<section class="gp-below" aria-label="More services">
  <?php if (!empty($remaining)): ?>
  <div class="gp-below-head rev"><h2 class="gp-below-title">More Services</h2><span class="gp-below-count"><?= count($remaining) ?> more</span></div>
  <div class="gp-grid">
    <?php foreach ($remaining as $ri => $svc):
      $rUrl = URLROOT . '/customerServices/detail/' . (int)$svc['id'] . $detailDateQuery;
    ?>
    <article class="gp-gc rev rev-d<?= min(($ri % 5) + 1, 5) ?>">
      <a class="gp-gc-img" href="<?= $h($rUrl) ?>" tabindex="-1" aria-hidden="true">
        <?php if (trim((string)($svc['image'] ?? '')) !== ''): ?>
          <img src="<?= $h($svc['image']) ?>" alt="<?= $h($svc['name'] ?? '') ?>" loading="lazy">
        <?php else: ?><div class="gp-gc-img-ph"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg></div><?php endif; ?>
        <span class="gp-gc-bdg"><?= $h($svc['category'] ?? 'Service') ?></span>
      </a>
      <div class="gp-gc-body">
        <div class="gp-gc-top">
          <span class="gp-gc-sup">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="opacity:.5;flex-shrink:0"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            <?= $h($svc['supplier_name'] ?? '') ?>
          </span>
          <?php if ((float)($svc['rating'] ?? 0) > 0): ?>
          <div class="gp-gc-rat">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
            <?= number_format((float)$svc['rating'], 1) ?>
          </div>
          <?php endif; ?>
        </div>
        <h3 class="gp-gc-name"><a href="<?= $h($rUrl) ?>"><?= $h($svc['name'] ?? '') ?></a></h3>
        <p class="gp-gc-desc"><?= $h($svc['description'] ?? '') ?></p>
        <div class="gp-gc-meta">
          <span><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg><?= $h($durationText($svc)) ?></span>
          <span><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg><?= $pricingUnit($svc) === '/hr' ? 'Per hour' : 'Per session' ?></span>
        </div>
        <div class="gp-gc-foot">
          <div><span class="gp-gc-price"><?= $moneyRange($svc) ?></span><span class="gp-gc-unit"><?= $h($durationText($svc)) . ' ' . $pricingUnit($svc) ?></span></div>
          <a class="gp-gc-btn" href="<?= $h($rUrl) ?>">View <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg></a>
        </div>
      </div>
    </article>
    <?php endforeach; ?>
  </div>
  <?php elseif (empty($services)): ?>
  <div class="gp-grid"><div class="gp-empty rev">
    <h3>No services found</h3>
    <p>Try adjusting your search or browse all categories.</p>
    <div class="gp-empty-btns">
      <a class="gp-ebtn p" href="<?= $resetUrl ?>">Clear filters</a>
      <a class="gp-ebtn s" href="<?= URLROOT ?>/customerServices/service?category=all">Browse all</a>
    </div>
  </div></div>
  <?php endif; ?>
</section>
<?php endif; ?>

</main>


<script>
(function(){
'use strict';

/* ── profile dropdown ─────────────────────── */
document.querySelectorAll('.gp-profile-btn').forEach(btn=>{
  btn.addEventListener('click',e=>{
    e.stopPropagation();
    const was=btn.getAttribute('aria-expanded')==='true';
    document.querySelectorAll('.gp-profile-btn').forEach(b=>b.setAttribute('aria-expanded','false'));
    btn.setAttribute('aria-expanded',String(!was));
  });
});
document.addEventListener('click',()=>document.querySelectorAll('.gp-profile-btn').forEach(b=>b.setAttribute('aria-expanded','false')));

/* ── date picker chip ─────────────────────── */
const datePick=document.getElementById('datePick');
document.querySelectorAll('.fb-chip').forEach(chip=>{
  if(chip.contains(datePick)){
    chip.addEventListener('click',()=>datePick.showPicker?.());
    datePick.addEventListener('change',()=>chip.closest('form').submit());
  }
});
document.querySelectorAll('.fb-select').forEach(select=>{
  select.addEventListener('change',()=>select.closest('form').submit());
});

/* ── service card navigation ───────────────── */
document.querySelectorAll('.gp-card[data-url]').forEach(card=>{
  const openCard=()=>{
    const url=card.dataset.url;
    if(url) window.location.href=url;
  };
  card.addEventListener('click',openCard);
  card.addEventListener('keydown',event=>{
    if(event.key==='Enter' || event.key===' '){
      event.preventDefault();
      openCard();
    }
  });
});

/* ── hero pop-out reveal ──────────────────── */
const heroOverlay=document.querySelector('.hero-overlay');
if(heroOverlay){
  if('IntersectionObserver' in window){
    const heroIo=new IntersectionObserver(entries=>entries.forEach(entry=>{
      if(entry.isIntersecting){
        heroOverlay.classList.add('is-in');
        heroIo.disconnect();
      }
    }),{threshold:.35});
    heroIo.observe(heroOverlay);
  } else {
    heroOverlay.classList.add('is-in');
  }
}

/* ── scroll reveal ────────────────────────── */
const revEls=document.querySelectorAll('.rev');
if('IntersectionObserver' in window){
  const io=new IntersectionObserver(entries=>entries.forEach(e=>{
    if(e.isIntersecting){ e.target.classList.add('in'); io.unobserve(e.target); }
  }),{threshold:.06,rootMargin:'0px 0px -40px 0px'});
  revEls.forEach(el=>io.observe(el));
} else revEls.forEach(el=>el.classList.add('in'));

})();

(function() {
    const heroBanner = document.querySelector('.hero-banner');
    if (!heroBanner) return;

    // Array of your images
    const images = [
        "<?= URLROOT ?>/public/uploads/serviceHero1.png",
        "<?= URLROOT ?>/public/uploads/serviceHero2.png",
        "<?= URLROOT ?>/public/uploads/serviceHero3.png",
        "<?= URLROOT ?>/public/uploads/serviceHero4.png"
    ];

    let currentIndex = 0;

    function rotateHeroImage() {
        // Increment index and loop back to 0 when hitting the end
        currentIndex = (currentIndex + 1) % images.length;
        
        // Apply the new background image smoothly
        heroBanner.style.backgroundImage = `url('${images[currentIndex]}')`;
    }

    // Change image every 5000 milliseconds (5 seconds)
    setInterval(rotateHeroImage, 5000);
})();
</script>
</body>
</html>
