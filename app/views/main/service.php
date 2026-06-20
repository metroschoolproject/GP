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
$serviceLocation = function ($s) {
    $location = trim((string)($s['venue_location'] ?? $s['service_location'] ?? $s['location'] ?? ''));
    return $location !== '' ? $location : 'Location available after booking';
};

$activeCategory = $filters['category']  ?? 'all';
$activeSort     = $filters['sort']      ?? 'featured';
$activeDate     = $filters['date']      ?? '';
$activeDateLabel = $activeDate !== ''
    ? ($activeDate === date('Y-m-d') ? 'Today' : date('M j', strtotime($activeDate)))
    : 'Today';
$activePriceMin = $filters['price_min'] ?? '';
$activePriceMax = $filters['price_max'] ?? '';
$detailDateQuery = $activeDate !== '' ? '?date=' . rawurlencode($activeDate) : '';
$fromFilterRequest = ($_GET['from_filter'] ?? '') === '1';

$serviceUrl = function (array $overrides = []) use ($filters) {
    $params = [
        'q' => $filters['search'] ?? '',
        'category' => $filters['category'] ?? 'all',
        'sort' => $filters['sort'] ?? 'featured',
        'date' => $filters['date'] ?? '',
        'price_min' => $filters['price_min'] ?? '',
        'price_max' => $filters['price_max'] ?? '',
        'page' => $_GET['page'] ?? 1,
        'from_filter' => 1,
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

/* ══ HOME-STYLE HEADER ═══════════════════════════════════ */
.site-header{
  position:fixed;
  inset:0 0 auto;
  z-index:1000;
  padding:0;
  pointer-events:none;
  font-family:var(--font-display);
}
.navbar{
  position:fixed;
  top:0;
  left:0;
  z-index:1000;
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:12px;
  width:100%;
  min-height:58px;
  padding:9px 18px;
  border-radius:0 0 6px 6px;
  border-bottom:0;
  background:transparent;
  box-shadow:none;
  pointer-events:auto;
}
.nav-left-spacer{width:82px;height:36px;flex:0 0 82px}
.nav-center-logo{
  position:absolute;
  left:24px;
  top:50%;
  z-index:2;
  display:grid;
  width:68px;
  height:68px;
  place-items:center;
  overflow:hidden;
  border-radius:50%;
  transform:translateY(-50%);
}
.nav-center-logo img{width:100%;height:100%;object-fit:cover}
.nav-links{
  position:absolute;
  left:50%;
  top:50%;
  display:flex;
  align-items:center;
  gap:6px;
  padding:4px;
  border-radius:8px;
  background:rgba(0,0,0,.52);
  transform:translate(-50%,-50%);
  color:#fff4e6;
  font-size:12px;
  font-weight:700;
  box-shadow:inset 0 1px 0 rgba(255,255,255,.14);
  -webkit-backdrop-filter:blur(12px);
  backdrop-filter:blur(12px);
}
.nav-links a{
  border:0;
  border-radius:7px;
  background:transparent;
  padding:6px 15px;
  color:#fff4e6;
  font:inherit;
  white-space:nowrap;
  cursor:pointer;
  transition:all .2s ease;
}
.nav-links a:hover,
.nav-links a.active{background:rgba(255,255,255,.92);color:#3f2f24}
.nav-actions{display:flex;align-items:center;gap:8px;margin-left:auto}
.nav-partner,
.nav-login{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  min-height:31px;
  border-radius:7px;
  font-size:12px;
  font-weight:800;
  transition:all .2s ease;
}
.nav-partner{
  padding:5px 12px;
  background:#3f241a;
  color:#fff8ef;
  box-shadow:none;
}
.nav-partner:hover{transform:translateY(-1px);background:#4a2d22;color:#fff8ef}
.nav-login{
  padding:5px 11px;
  background:#fff8ef;
  color:#3f2f24;
}
.nav-login:hover{background:#f3d9a4;color:#3f2f24}
.home-profile-dropdown{position:relative}
.home-profile-btn{
  display:flex;
  align-items:center;
  gap:6px;
  padding:3px 10px 3px 3px;
  border-radius:7px;
  border:1px solid rgba(255,255,255,.15);
  background:rgba(255,255,255,.08);
  cursor:pointer;
  color:#fff4e6;
  font-family:var(--font-display);
  font-size:12px;
  font-weight:600;
  transition:all .2s;
}
.home-profile-btn:hover{background:rgba(255,255,255,.15)}
.home-profile-avatar{
  display:grid;
  place-items:center;
  width:28px;
  height:28px;
  border-radius:50%;
  background:#d8b46a;
  color:#3f2f24;
  font-size:12px;
  font-weight:800;
  letter-spacing:.5px;
}
.home-profile-name{max-width:86px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.home-profile-chevron{opacity:.7;transition:transform .2s}
.home-profile-btn[aria-expanded="true"] .home-profile-chevron{transform:rotate(180deg)}
.nav-actions .gp-customer-notification{z-index:1100}
.nav-actions .gp-customer-notification #dashboardNotificationBtn{
  width:31px;
  height:31px;
  border-radius:7px;
  border-color:rgba(255,248,239,.22);
  background:#fff8ef;
  color:#3f2f24;
  box-shadow:none;
}
.nav-actions .gp-customer-notification #dashboardNotificationBtn svg{
  width:15px;
  height:15px;
}
.nav-actions .gp-customer-notification .dashboard-notification-panel{
  right:0;
  top:calc(100% + 9px);
}
.home-profile-menu{
  position:absolute;
  top:calc(100% + 8px);
  right:0;
  z-index:1100;
  min-width:180px;
  padding:6px;
  border-radius:10px;
  border:1px solid rgba(255,255,255,.10);
  background:#765a46;
  box-shadow:0 12px 35px rgba(92,67,48,.25);
  opacity:0;
  visibility:hidden;
  transform:translateY(-4px);
  transition:all .15s ease;
}
.home-profile-btn[aria-expanded="true"]+.home-profile-menu{
  opacity:1;
  visibility:visible;
  transform:translateY(0);
}
.home-profile-menu-item{
  display:flex;
  align-items:center;
  gap:10px;
  padding:10px 12px;
  border-radius:10px;
  color:#fff4e6;
  font-size:13px;
  font-weight:600;
  transition:all .15s;
}
.home-profile-menu-item:hover{background:rgba(216,180,106,.16);color:#f3d9a4}
.home-profile-menu-item--danger{color:#f5a0a0}
.home-profile-menu-item--danger:hover{background:rgba(185,75,75,.20);color:#ffcccc}
.mobile-menu-btn{
  display:none;
  align-items:center;
  justify-content:center;
  min-height:40px;
  padding:0 14px;
  border:1px solid transparent;
  border-radius:8px;
  background:rgba(255,255,255,.10);
  color:#fff4e6;
  cursor:pointer;
  font-family:var(--font-display);
  font-size:13px;
  font-weight:800;
  box-shadow:0 6px 18px rgba(92,67,48,.14);
}
.mobile-menu{
  position:fixed;
  top:74px;
  left:50%;
  z-index:999;
  display:none;
  width:min(calc(100% - 24px),1152px);
  padding:10px;
  border:1px solid transparent;
  border-radius:10px;
  background:#765a46;
  box-shadow:0 18px 36px rgba(92,67,48,.18);
  transform:translateX(-50%);
  pointer-events:auto;
}
.mobile-menu.open{display:grid}
.mobile-menu a{
  padding:12px 14px;
  border-radius:8px;
  color:#fff4e6;
  font-weight:800;
}
.mobile-menu a:hover{background:rgba(216,180,106,.16);color:#f3d9a4}
.mobile-menu .mobile-partner{background:#3f241a;color:#fff8ef}
.mobile-menu .mobile-partner:hover{background:#4a2d22;color:#fff8ef}
.mobile-menu .mobile-login{background:#fff8ef;color:#3f2f24}
.mobile-menu .mobile-login:hover{background:#f3d9a4;color:#3f2f24}

/* ══ SERVICES SEARCH + GRID ══════════════════════════════ */
.gp-scene{
    position:relative;
    padding:0;
    background:#fff;
}
.hero-banner{
    position:relative;
    min-height:calc(100vh - var(--header-h) - 1px);
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
    font-size:clamp(42px, 5.5vw, 62px);
    font-weight:500;
    letter-spacing:4px;
    margin-bottom:8px;
}

.hero-overlay p{
    font-size:14px;
    letter-spacing:1.4px;
    margin-bottom:20px;
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

.hero-overlay.no-pop h1,
.hero-overlay.no-pop p,
.hero-overlay.no-pop .gp-float-bar,
.hero-overlay.no-pop .fb-search,
.hero-overlay.no-pop .fb-controls > *{
  opacity:1;
  transform:none;
  animation:none !important;
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
    height:58px;
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
    animation:supplierScroll var(--marquee-duration, 34s) linear infinite;
}

.supplier-item{
    flex-shrink:0;
    margin:0 42px;
    font-size:12px;
    font-weight:600;
    color:#765a46;
    letter-spacing:.08em;
    text-transform:uppercase;
}

.supplier-item::after{
    content:"•";
    margin-left:42px;
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
.fb-date-chip{
  position:relative;
  min-height:32px;
  gap:8px;
  border-radius:6px;
  background:#FFF8EF;
  color:#3F241A;
  padding:0 10px;
  font-size:12px;
  font-weight:800;
  box-shadow:0 4px 14px rgba(63,36,26,.06);
}
.fb-date-chip.on{
  background:#FFF8EF;
  border-color:rgba(154,104,127,.34);
  color:#3F241A;
}
.fb-date-chip svg{flex:0 0 auto;stroke:#7A4E3D}
.fb-date-chip input{
  position:absolute;
  inset:0;
  width:100%;
  height:100%;
  opacity:0;
  pointer-events:none;
}
.service-calendar-popover{
  position:fixed;
  z-index:10010;
  width:min(250px,calc(100vw - 32px));
  padding:12px;
  border:1px solid rgba(63,36,26,.14);
  border-radius:10px;
  background:rgba(255,248,239,.98);
  box-shadow:0 24px 60px rgba(63,36,26,.18);
  backdrop-filter:blur(18px);
  -webkit-backdrop-filter:blur(18px);
}
.service-calendar-head{
  display:flex;align-items:center;justify-content:space-between;gap:12px;
  color:#3F241A;font-size:12px;font-weight:900;margin-bottom:9px;
}
.service-calendar-nav{
  width:22px;height:22px;display:inline-grid;place-items:center;
  border:0;border-radius:7px;background:transparent;color:#7A4E3D;cursor:pointer;
}
.service-calendar-nav svg{width:16px;height:16px;stroke:currentColor}
.service-calendar-nav:hover{background:rgba(63,36,26,.08)}
.service-calendar-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:3px}
.service-calendar-day-name,
.service-calendar-day{
  display:grid;place-items:center;height:24px;color:#6F5448;font-size:11px;
}
.service-calendar-day-name{color:rgba(63,36,26,.52);font-weight:800}
.service-calendar-day{
  border:0;border-radius:6px;background:transparent;font-weight:800;cursor:pointer;
}
.service-calendar-day:hover{background:rgba(122,78,61,.12)}
.service-calendar-day.is-selected{background:#3F241A;color:#FFF8EF}
.service-calendar-day.is-today:not(.is-selected){outline:1px solid rgba(63,36,26,.28)}
.service-calendar-day.is-disabled{color:rgba(63,36,26,.24);cursor:not-allowed}
.fb-budget{gap:6px}
.fb-budget:has(input:invalid){
  position:relative;
  background:#fff8ef;
  border-color:rgba(154,104,127,.45);
  color:#3F241A;
}
.fb-budget:has(input:invalid)::after{
  content:"Enter a valid MMK amount";
  position:absolute;
  left:50%;
  top:calc(100% + 8px);
  z-index:80;
  width:max-content;
  max-width:210px;
  transform:translateX(-50%);
  padding:8px 10px;
  border:1px solid rgba(154,104,127,.22);
  border-radius:8px;
  background:#fff8ef;
  color:#7E4F65;
  font-size:10px;
  font-weight:800;
  box-shadow:0 12px 28px rgba(63,36,26,.14);
}
.fb-number-wrap{
  position:relative;
  display:inline-flex;
  align-items:center;
}
.fb-budget input{
  width:72px;
  border:none;
  background:transparent;
  color:#4f382a;
  font-size:11px;
  font-weight:600;
  padding-right:12px;
  -moz-appearance:textfield;
}
.fb-budget input::-webkit-outer-spin-button,
.fb-budget input::-webkit-inner-spin-button{
  -webkit-appearance:none;
  margin:0;
}
.fb-budget input::placeholder{color:rgba(118,90,70,.58)}
.fb-budget input:focus{outline:none}
.fb-budget input:invalid{color:#9A687F}
.fb-number-stepper{
  position:absolute;
  right:-3px;
  top:50%;
  transform:translateY(-50%);
  display:grid;
  gap:2px;
  color:#9A687F;
}
.fb-number-stepper button{
  width:14px;
  height:10px;
  display:grid;
  place-items:center;
  border:0;
  padding:0;
  border-radius:3px;
  background:rgba(154,104,127,.10);
  color:inherit;
  cursor:pointer;
}
.fb-number-stepper button:hover{background:rgba(154,104,127,.20)}
.fb-number-stepper svg{
  width:9px;
  height:9px;
  stroke:currentColor;
}
.fb-budget-sep{color:rgba(118,90,70,.54);font-size:10px}

.fb-select-wrap{
  position:relative;
  flex-shrink:0;
  display:inline-flex;
  align-items:center;
  z-index:45;
}
.fb-select{
  background-color:rgba(245,232,217,.82);
  background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 24 24' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='m6 9 6 6 6-6' stroke='%239A687F' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
  background-repeat:no-repeat;
  background-position:right 12px center;
  background-size:12px 12px;
  border:0.5px solid rgba(118,90,70,.20);
  border-radius:999px;
  padding:7px 34px 7px 14px;
  color:#765a46;
  font-size:11px;font-weight:600;
  cursor:pointer;
  appearance:none;
  -webkit-appearance:none;
  min-width:128px;max-width:172px;
  box-shadow:0 10px 24px rgba(43,31,24,.12);
  accent-color:#9A687F;
}
.fb-select:hover,
.fb-select:focus{
  background-color:#FFF8EF;
  border-color:rgba(154,104,127,.36);
  color:#4f382a;
}
.fb-select option{
  background:#FFF8EF;
  color:#4f382a;
  font-weight:700;
}
.fb-select option:hover,
.fb-select option:focus{
  background:#ead7df !important;
  color:#3F241A;
}
.fb-select option:checked{
  background:#9A687F !important;
  color:#fff8ef;
}
.fb-select.is-native-hidden{
  position:absolute;
  inset:0;
  width:100%;
  height:100%;
  opacity:0;
  pointer-events:none;
}
.fb-select-trigger{
  min-width:112px;
  max-width:148px;
  min-height:32px;
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:12px;
  border:0.5px solid rgba(118,90,70,.20);
  border-radius:8px;
  padding:7px 12px 7px 14px;
  background:rgba(245,232,217,.90);
  color:#765a46;
  font-size:11px;
  font-weight:700;
  cursor:pointer;
  box-shadow:0 10px 24px rgba(43,31,24,.12);
}
.fb-select-trigger:hover,
.fb-select-wrap.is-open .fb-select-trigger{
  background:#FFF8EF;
  border-color:rgba(154,104,127,.36);
  color:#4f382a;
}
.fb-select-trigger-text{
  overflow:hidden;
  text-overflow:ellipsis;
  white-space:nowrap;
}
.fb-select-trigger svg{
  width:12px;
  height:12px;
  flex:0 0 12px;
  stroke:#9A687F;
}
.fb-select-popover{
  position:fixed;
  left:0;
  top:0;
  z-index:10020;
  min-width:136px;
  padding:6px;
  border:1px solid rgba(154,104,127,.20);
  border-radius:9px;
  background:#FFF8EF;
  box-shadow:0 18px 40px rgba(63,36,26,.18);
}
.fb-select-item{
  width:100%;
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:10px;
  border:0;
  border-radius:7px;
  padding:8px 10px;
  background:transparent;
  color:#5b3b2d;
  font-size:11px;
  font-weight:700;
  text-align:left;
  cursor:pointer;
}
.fb-select-item:hover,
.fb-select-item:focus{
  background:rgba(154,104,127,.14);
  color:#3F241A;
}
.fb-select-item.is-selected{
  background:#9A687F;
  color:#fff8ef;
}
.fb-select-dot{
  width:6px;
  height:6px;
  border-radius:999px;
  background:currentColor;
  opacity:0;
}
.fb-select-item.is-selected .fb-select-dot{opacity:1}
.fb-sort{min-width:108px;max-width:132px}
.fb-sort ~ .fb-select-trigger{min-width:96px;max-width:118px}
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
    padding:46px var(--pad-x) 34px;
    z-index:1;

    /* Theme gradient */
    background:linear-gradient(
        180deg,
        #ead8c8 0%,
        #dfc9b7 48%,
        #d2bba8 100%
    );
}
.gp-track{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:20px 18px;
    align-items:start;
}


.gp-card{
    background:#fff8ef;
    border-radius:16px;
    padding:10px;
    overflow:hidden;
    cursor:pointer;

    display:flex;
    flex-direction:column;

    height:360px;
    min-height:360px;

    border:2px solid rgba(216,180,106,.46);
    box-shadow:
        0 14px 34px rgba(63,36,26,.12);

    transition:transform .22s var(--ease), box-shadow .22s var(--ease), border-color .22s var(--ease);
}
.gp-card:hover{
    transform:translateY(-6px);
    border-color:rgba(216,180,106,.72);
    box-shadow:0 20px 42px rgba(63,36,26,.16);
}
.gp-card:focus-visible{
    outline:2px solid rgba(154,104,127,.62);
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
    order:2;
    margin:10px 2px 0;
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
    color:#6f625a;
    font-size:12px;
    letter-spacing:0;
    text-transform:none;
    font-weight:700;
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
}
.gc-name{
    font-family:var(--font-body);
    font-size:13px;
    line-height:1.35;
    font-weight:800;
    color:#211d1a;
    margin-bottom:3px;
    display:-webkit-box;
    -webkit-line-clamp:2;
    -webkit-box-orient:vertical;
    overflow:hidden;
}
.gc-tags{
    order:3;
    display:block;
    margin:7px 2px 0;
}
.gc-tag{
    display:none;
}
.gc-tag:first-child{
    display:inline-flex;
    background:#f0dfe7;
    color:#7E4F65;

    padding:4px 8px;
    border-radius:7px;

    font-size:10px;
    font-weight:800;

    border:1px solid rgba(154,104,127,.14);
}
.gc-stats{
    order:4;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:10px;
    margin:10px 2px 0;
}
.gc-stat{
    background:transparent;
    border-radius:0;
    padding:0;
    text-align:left;
    border:none;
}
.gc-stat+.gc-stat{border-left:0}
.gc-stat strong{
    color:#211d1a;
    font-size:13px;
    font-weight:900;
    display:block;
    overflow:hidden;
    text-overflow:ellipsis;
    white-space:nowrap;
    
}
.gc-stat span{
    color:#8f7666;
    font-size:10px;
    font-weight:700;
}
.gc-stat svg{flex:0 0 auto;color:#020304}
.gc-image-frame{
    order:1;
    position:relative;
    margin-top:0;

    border:1px solid rgba(216,180,106,.46);
    border-radius:13px;
    overflow:hidden;

    height:178px;

    padding:0;
}

.gc-image-frame img{
    width:100%;
    height:100%;
    object-fit:cover;

    border-radius:12px;
    transition:transform .45s var(--ease);
}
.gp-card:hover .gc-image-frame img{transform:scale(1.035)}
.gc-location{
    order:3;
    margin:5px 2px 0;
    color:#7f6758;
    font-size:11px;
    font-weight:600;
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
}
.gc-book-btn{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    min-height:32px;
    padding:0 12px;
    border:1px solid rgba(154,104,127,.22);
    border-radius:8px;
    background:#9A687F;
    color:#fff8ef;
    font-size:11px;
    font-weight:800;
    white-space:nowrap;
    cursor:pointer;
    transition:background .18s var(--ease), transform .18s var(--ease);
}
.gc-book-btn:hover{background:#7E4F65;transform:translateY(-1px)}
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
  padding:26px 0 0;
  flex-wrap:wrap;
}
.gp-page-link{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  min-width:38px;
  height:38px;
  padding:0 13px;
  border-radius:9px;
  border:1px solid rgba(118,90,70,.16);
  background:rgba(255,248,239,.74);
  color:#6f625a;
  font-size:12px;
  font-weight:700;
  transition:transform .18s var(--ease), background .18s, color .18s, border-color .18s;
}
.gp-page-link svg{width:15px;height:15px;stroke:currentColor}
.gp-page-link:hover{transform:translateY(-2px);background:#fff;border-color:rgba(154,104,127,.28);color:#7E4F65}
.gp-page-link.is-active{background:#9A687F;border-color:#9A687F;color:#fff}
.gp-page-link.is-edge{padding:0;width:38px}


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
  .nav-links,.nav-actions{display:none}
  .mobile-menu-btn{display:inline-flex}
  .gp-scene{min-height:calc(100svh - 65px)}
  .hero-banner{min-height:calc(100svh - 76px)}
}
@media(max-width:700px){
  .navbar{min-height:59px;padding:10px 12px}
  .nav-left-spacer{width:70px;flex-basis:70px}
  .nav-center-logo{left:12px;width:64px;height:64px}
  .mobile-menu{top:68px}
  .gp-scene{min-height:calc(100svh - 59px);padding:28px 12px 44px}
  .hero-banner{min-height:calc(100svh - 28px)}
  .gp-float-bar{border-radius:12px;width:100%;padding:8px}
  .fb-search{min-height:46px;padding:0 16px}
  .fb-search input{font-size:14px}
  .fb-controls{justify-content:flex-start}
  .gp-track{grid-template-columns:1fr;gap:16px}
  .gp-card{height:330px;min-height:330px;padding:10px;border-radius:16px}
  .gc-top{margin:9px 2px 0}
  .gc-name{font-size:15px}
  .gc-tags{gap:6px;margin:7px 2px 0}
  .gc-tag{padding:5px 9px;font-size:10px}
  .gc-stats{margin:10px 2px 0}
  .gc-stat{padding:0}
  .gc-stat strong{font-size:13px}
  .gc-stat span{font-size:10px}
  .gc-image-frame{height:160px}
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
<header class="site-header">
  <nav class="navbar" aria-label="Main navigation">
    <div class="nav-left-spacer" aria-hidden="true"></div>

    <a class="nav-center-logo" href="<?= URLROOT ?>/main/index#top" aria-label="Golden Promise home">
      <img src="<?= URLROOT ?>/public/images/home/gp_logo.png" alt="Golden Promise logo">
    </a>

    <div class="nav-links">
      <a href="<?= URLROOT ?>/main/index#top">Home</a>
      <a href="<?= URLROOT ?>/customerServices/packages">Packages</a>

      <a class="active" href="<?= URLROOT ?>/customerServices/service">Services</a>
    </div>

    <div class="nav-actions">
      <a class="nav-partner" href="<?= URLROOT ?>/users/register?type=supplier">Be a Partner</a>
      <?php if ($isLoggedIn): ?>
      <?php if (defined('APPROOT') && file_exists(APPROOT . '/views/dashboardLayout/customerNotification.php')) require APPROOT . '/views/dashboardLayout/customerNotification.php'; ?>
      <div class="home-profile-dropdown">
        <button class="home-profile-btn" type="button" aria-expanded="false">
          <span class="home-profile-avatar"><?= strtoupper(substr($_SESSION['session_name'] ?? 'U', 0, 1)) ?></span>
          <span class="home-profile-name"><?= $h(explode(' ', $_SESSION['session_name'] ?? 'User')[0]) ?></span>
          <svg class="home-profile-chevron" width="10" height="6" viewBox="0 0 10 6" fill="none"><path d="M1 1l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
        <div class="home-profile-menu" aria-hidden="true">
          <a class="home-profile-menu-item" href="<?= URLROOT ?>/booking/myBookings">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            My Bookings
          </a>
          <a class="home-profile-menu-item home-profile-menu-item--danger" href="<?= URLROOT ?>/users/logout">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            Logout
          </a>
        </div>
      </div>
      <?php else: ?>
      <a class="nav-login" href="<?= URLROOT ?>/users/auth">Log In</a>
      <?php endif; ?>
    </div>

    <button class="mobile-menu-btn" id="menuButton" type="button" aria-label="Open navigation" aria-expanded="false">
      Menu
    </button>
  </nav>

  <div class="mobile-menu" id="mobileMenu">
    <a href="<?= URLROOT ?>/main/index#top">Home</a>
    <a href="<?= URLROOT ?>/customerServices/service">Our Service</a>
    <a href="<?= URLROOT ?>/customerServices/packages">Packages</a>
    <a class="mobile-partner" href="<?= URLROOT ?>/users/register?type=supplier">Be a Partner</a>
    <?php if ($isLoggedIn): ?>
    <a href="<?= URLROOT ?>/booking/myBookings">My Bookings</a>
    <a href="<?= URLROOT ?>/users/logout">Logout</a>
    <?php else: ?>
    <a class="mobile-login" href="<?= URLROOT ?>/users/auth">Log In</a>
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
        <input type="hidden" name="from_filter" value="1">
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
          <label class="fb-chip fb-date-chip <?= $activeDate !== '' ? 'on' : '' ?>" id="serviceDateChip">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            <span><?= $h($activeDateLabel) ?></span>
            <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
            <input type="date" name="date" value="<?= $h($activeDate) ?>" min="<?= date('Y-m-d') ?>" id="datePick" style="position:absolute;opacity:0;pointer-events:none;width:1px;height:1px">
          </label>
          <label class="fb-chip fb-budget <?= ($activePriceMin !== '' || $activePriceMax !== '') ? 'on' : '' ?>">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            <span class="fb-number-wrap">
              <input type="number" name="price_min" value="<?= $h($activePriceMin) ?>" min="0" step="1000" placeholder="Min" aria-label="Minimum budget">
              <span class="fb-number-stepper">
                <button type="button" tabindex="-1" data-number-step="up" aria-label="Increase minimum budget"><svg viewBox="0 0 24 24" fill="none" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="m6 15 6-6 6 6"/></svg></button>
                <button type="button" tabindex="-1" data-number-step="down" aria-label="Decrease minimum budget"><svg viewBox="0 0 24 24" fill="none" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg></button>
              </span>
            </span>
            <span class="fb-budget-sep">–</span>
            <span class="fb-number-wrap">
              <input type="number" name="price_max" value="<?= $h($activePriceMax) ?>" min="0" step="1000" placeholder="Max" aria-label="Maximum budget">
              <span class="fb-number-stepper">
                <button type="button" tabindex="-1" data-number-step="up" aria-label="Increase maximum budget"><svg viewBox="0 0 24 24" fill="none" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="m6 15 6-6 6 6"/></svg></button>
                <button type="button" tabindex="-1" data-number-step="down" aria-label="Decrease maximum budget"><svg viewBox="0 0 24 24" fill="none" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg></button>
              </span>
            </span>
          </label>
          <div class="fb-div"></div>
          <?php if (!empty($categories)): ?>
          <span class="fb-select-wrap">
            <select class="fb-select" name="category">
              <option value="all" <?= $activeCategory === 'all' ? 'selected' : '' ?>>All categories</option>
              <?php foreach ($categories as $cat):
                $slug = $cat['slug'] ?? strtolower($cat['name'] ?? '');
              ?>
                <option value="<?= $h($slug) ?>" <?= $activeCategory === $slug ? 'selected' : '' ?>><?= $h($cat['name'] ?? '') ?></option>
              <?php endforeach; ?>
            </select>
          </span>
          <div class="fb-div"></div>
          <?php endif; ?>
          <span class="fb-select-wrap">
            <select class="fb-select fb-sort" name="sort" aria-label="Sort services">
              <option value="featured" <?= $activeSort === 'featured' ? 'selected' : '' ?>>Featured</option>
              <option value="price_low" <?= $activeSort === 'price_low' ? 'selected' : '' ?>>Price low</option>
              <option value="price_high" <?= $activeSort === 'price_high' ? 'selected' : '' ?>>Price high</option>
              <option value="newest" <?= $activeSort === 'newest' ? 'selected' : '' ?>>Newest</option>
              <option value="rating" <?= $activeSort === 'rating' ? 'selected' : '' ?>>Rating</option>
            </select>
          </span>
          <div class="fb-div"></div>
          
        </div>
      </form>
    </div>

  </div>
  <section class="supplier-marquee">
        <?php
        $supplierNames = [];
        foreach ($services as $service) {
            $name = trim($service['supplier_name'] ?? '');
            if ($name !== '') {
                $supplierNames[$name] = true;
            }
        }

        $supplierNames = array_keys($supplierNames);
        $marqueeDuration = max(34, count($supplierNames) * 7);
        ?>
    <div class="supplier-track" style="--marquee-duration: <?= (int)$marqueeDuration ?>s">

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
      $svcCategoryKey = strtolower(trim((string)($svc['category_slug'] ?? $svc['category'] ?? '')));
      $availabilityAnchor = (strpos($svcCategoryKey, 'venue') !== false || strpos($svcCategoryKey, 'hall') !== false) ? 'available-halls' : 'availability';
      $bookUrl = $dUrl . '#' . $availabilityAnchor;
    ?>
      <article class="gp-card" data-idx="<?= $ci ?>" data-url="<?= $h($dUrl) ?>" data-img="<?= $h(trim((string)($svc['image'] ?? ''))) ?>" role="link" tabindex="0" aria-label="View details for <?= $h($svc['name'] ?? 'service') ?>">
        <div class="gc-body">
          <div class="gc-image-frame">
            <?php if(trim((string)($svc['image'] ?? '')) !== ''): ?>
              <img src="<?= $h($svc['image']) ?>" alt="<?= $h($svc['name'] ?? '') ?>">
            <?php endif; ?>
          </div>

          <div class="gc-top">
            <div class="gc-head">
              <div class="gc-head-text">
                <div class="gc-name"><?= $h($svc['name'] ?? '') ?></div>
                <div class="gc-sup"><?= $h($svc['supplier_name'] ?? '') ?></div>
              </div>
            </div>
          </div>

          <div class="gc-location"><?= $h($serviceLocation($svc)) ?></div>

          <div class="gc-tags">
            <span class="gc-tag"><?= $h($svc['category'] ?? 'Service') ?></span>
          </div>

          <div class="gc-stats">
            <div class="gc-stat">
              <strong><?= $moneyRange($svc) ?></strong>
              <span><?= $h($durationText($svc)) ?></span>
            </div>
            <a class="gc-book-btn" href="<?= $h($bookUrl) ?>">Book now</a>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
  <?php if ($totalPages > 1): ?>
  <nav class="gp-pagination" aria-label="Service pages">
  <?php if ($currentPage > 1): ?>
    <a class="gp-page-link is-edge" href="<?= $h($serviceUrl(['page' => $currentPage - 1]) . '#trackWrap') ?>" aria-label="Previous page">
      <svg viewBox="0 0 24 24" fill="none" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
    </a>
  <?php endif; ?>
  <?php for ($page = 1; $page <= $totalPages; $page++): ?>
    <a class="gp-page-link <?= $page === $currentPage ? 'is-active' : '' ?>" href="<?= $h($serviceUrl(['page' => $page]) . '#trackWrap') ?>" aria-label="Page <?= $page ?>" <?= $page === $currentPage ? 'aria-current="page"' : '' ?>><?= $page ?></a>
  <?php endfor; ?>
  <?php if ($currentPage < $totalPages): ?>
    <a class="gp-page-link is-edge" href="<?= $h($serviceUrl(['page' => $currentPage + 1]) . '#trackWrap') ?>" aria-label="Next page">
      <svg viewBox="0 0 24 24" fill="none" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
    </a>
  <?php endif; ?>
  </nav>
  <?php endif; ?>
</div>

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

<div class="service-calendar-popover" id="serviceCalendarPopover" hidden></div>

<script>
(function(){
'use strict';

/* ── navigation toggles ───────────────────── */
const menuButton=document.getElementById('menuButton');
const mobileMenu=document.getElementById('mobileMenu');

menuButton?.addEventListener('click',e=>{
  e.stopPropagation();
  const isOpen=mobileMenu.classList.toggle('open');
  menuButton.setAttribute('aria-expanded',String(isOpen));
});

document.addEventListener('click',e=>{
  const profileBtn=e.target.closest('.home-profile-btn');
  if(profileBtn){
    const expanded=profileBtn.getAttribute('aria-expanded')==='true';
    document.querySelectorAll('.home-profile-btn').forEach(btn=>btn.setAttribute('aria-expanded','false'));
    profileBtn.setAttribute('aria-expanded',String(!expanded));
    mobileMenu?.classList.remove('open');
    menuButton?.setAttribute('aria-expanded','false');
    return;
  }

  document.querySelectorAll('.home-profile-btn').forEach(btn=>btn.setAttribute('aria-expanded','false'));
  mobileMenu?.classList.remove('open');
  menuButton?.setAttribute('aria-expanded','false');
});

mobileMenu?.querySelectorAll('a').forEach(link=>{
  link.addEventListener('click',()=>{
    mobileMenu.classList.remove('open');
    menuButton?.setAttribute('aria-expanded','false');
  });
});

/* ── date picker chip ─────────────────────── */
const datePick=document.getElementById('datePick');
const serviceDateChip=document.getElementById('serviceDateChip');
const serviceCalendar=document.getElementById('serviceCalendarPopover');
let serviceCalendarMonth=null;
function svcDateValue(date){
  const y=date.getFullYear();
  const m=String(date.getMonth()+1).padStart(2,'0');
  const d=String(date.getDate()).padStart(2,'0');
  return `${y}-${m}-${d}`;
}
function svcParseDate(value){
  if(!value) return null;
  const parts=value.split('-').map(Number);
  if(parts.length!==3 || parts.some(Number.isNaN)) return null;
  return new Date(parts[0],parts[1]-1,parts[2]);
}
function svcPositionCalendar(){
  if(!serviceCalendar || !serviceDateChip) return;
  const rect=serviceDateChip.getBoundingClientRect();
  const width=Math.min(250,window.innerWidth-32);
  const left=Math.max(16,Math.min(rect.left,window.innerWidth-width-16));
  serviceCalendar.style.width=width+'px';
  serviceCalendar.style.left=left+'px';
  serviceCalendar.style.top=(rect.bottom+10)+'px';
}
function svcRenderCalendar(){
  if(!serviceCalendar || !datePick || !serviceCalendarMonth) return;
  const monthStart=new Date(serviceCalendarMonth.getFullYear(),serviceCalendarMonth.getMonth(),1);
  const selected=datePick.value;
  const today=svcDateValue(new Date());
  const min=datePick.min || '';
  const daysInMonth=new Date(monthStart.getFullYear(),monthStart.getMonth()+1,0).getDate();
  const leading=monthStart.getDay();
  const title=monthStart.toLocaleDateString('en-US',{month:'long',year:'numeric'});
  const names=['Su','Mo','Tu','We','Th','Fr','Sa'];
  let html='<div class="service-calendar-head">'+
    '<button class="service-calendar-nav" type="button" data-svc-prev aria-label="Previous month"><svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg></button>'+
    '<span>'+title+'</span>'+
    '<button class="service-calendar-nav" type="button" data-svc-next aria-label="Next month"><svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg></button>'+
    '</div><div class="service-calendar-grid">';
  names.forEach(day=>{html+='<div class="service-calendar-day-name">'+day+'</div>';});
  for(let i=0;i<leading;i++) html+='<span></span>';
  for(let day=1;day<=daysInMonth;day++){
    const value=svcDateValue(new Date(monthStart.getFullYear(),monthStart.getMonth(),day));
    const disabled=min && value<min;
    const classes=['service-calendar-day'];
    if(value===selected) classes.push('is-selected');
    if(value===today) classes.push('is-today');
    if(disabled) classes.push('is-disabled');
    html+='<button class="'+classes.join(' ')+'" type="button" data-svc-date="'+value+'"'+(disabled?' disabled':'')+'>'+day+'</button>';
  }
  html+='</div>';
  serviceCalendar.innerHTML=html;
}
function svcOpenCalendar(){
  if(!datePick || !serviceCalendar) return;
  serviceCalendarMonth=svcParseDate(datePick.value) || svcParseDate(datePick.min) || new Date();
  svcRenderCalendar();
  serviceCalendar.hidden=false;
  svcPositionCalendar();
}
if(datePick && serviceDateChip && serviceCalendar){
  serviceDateChip.addEventListener('click',event=>{
    event.preventDefault();
    event.stopPropagation();
    svcOpenCalendar();
  });
  serviceCalendar.addEventListener('click',event=>{
    event.stopPropagation();
    const prev=event.target.closest('[data-svc-prev]');
    const next=event.target.closest('[data-svc-next]');
    const day=event.target.closest('[data-svc-date]');
    if(prev){serviceCalendarMonth=new Date(serviceCalendarMonth.getFullYear(),serviceCalendarMonth.getMonth()-1,1);svcRenderCalendar();return;}
    if(next){serviceCalendarMonth=new Date(serviceCalendarMonth.getFullYear(),serviceCalendarMonth.getMonth()+1,1);svcRenderCalendar();return;}
    if(day){
      datePick.value=day.dataset.svcDate;
      serviceCalendar.hidden=true;
      datePick.closest('form').submit();
    }
  });
  serviceCalendar.addEventListener('mousedown',event=>{
    event.preventDefault();
    event.stopPropagation();
  });
  document.addEventListener('click',event=>{
    if(serviceCalendar.hidden) return;
    if(event.target.closest('.service-calendar-popover') || event.target.closest('#serviceDateChip')) return;
    serviceCalendar.hidden=true;
  });
  window.addEventListener('resize',()=>{if(!serviceCalendar.hidden) svcPositionCalendar();});
  window.addEventListener('scroll',()=>{if(!serviceCalendar.hidden) serviceCalendar.hidden=true;},{passive:true});
}
document.querySelectorAll('.fb-number-stepper button').forEach(button=>{
  button.addEventListener('click',event=>{
    event.preventDefault();
    event.stopPropagation();
    const wrap=button.closest('.fb-number-wrap');
    const input=wrap?.querySelector('input[type="number"]');
    if(!input) return;
    const direction=button.dataset.numberStep === 'down' ? -1 : 1;
    const step=Number(input.step) || 1;
    const min=input.min === '' ? null : Number(input.min);
    const max=input.max === '' ? null : Number(input.max);
    let value=input.value === '' ? (min ?? 0) : Number(input.value);
    if(Number.isNaN(value)) value=min ?? 0;
    value+=direction*step;
    if(direction<0 && input.value === '') value=min ?? 0;
    if(min !== null) value=Math.max(min,value);
    if(max !== null) value=Math.min(max,value);
    input.value=Number.isInteger(step) ? String(Math.round(value)) : String(value);
    input.dispatchEvent(new Event('input',{bubbles:true}));
    input.dispatchEvent(new Event('change',{bubbles:true}));
  });
});

function closeServiceSelects(exceptWrap=null){
  document.querySelectorAll('.fb-select-wrap.is-open').forEach(wrap=>{
    if(wrap===exceptWrap) return;
    wrap.classList.remove('is-open');
    wrap.querySelector('.fb-select-trigger')?.setAttribute('aria-expanded','false');
    if(wrap._serviceSelectMenu) wrap._serviceSelectMenu.hidden=true;
  });
  document.querySelectorAll('.fb-select-popover').forEach(menu=>{
    if(exceptWrap && exceptWrap._serviceSelectMenu===menu) return;
    menu.hidden=true;
  });
}
function positionServiceSelect(wrap){
  const trigger=wrap.querySelector('.fb-select-trigger');
  const menu=wrap._serviceSelectMenu;
  if(!trigger || !menu) return;
  const rect=trigger.getBoundingClientRect();
  const width=Math.max(rect.width,136);
  const left=Math.max(12,Math.min(rect.left,window.innerWidth-width-12));
  menu.style.width=width+'px';
  menu.style.left=left+'px';
  menu.style.top=(rect.bottom+8)+'px';
}
document.querySelectorAll('.fb-select-wrap').forEach((wrap,index)=>{
  const select=wrap.querySelector('.fb-select');
  if(!select) return;
  select.classList.add('is-native-hidden');

  const trigger=document.createElement('button');
  trigger.type='button';
  trigger.className='fb-select-trigger';
  trigger.setAttribute('aria-haspopup','listbox');
  trigger.setAttribute('aria-expanded','false');
  trigger.innerHTML='<span class="fb-select-trigger-text"></span><svg viewBox="0 0 24 24" fill="none" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>';

  const menu=document.createElement('div');
  menu.className='fb-select-popover';
  menu.hidden=true;
  menu.id='serviceFilterSelect'+index;
  menu.setAttribute('role','listbox');
  trigger.setAttribute('aria-controls',menu.id);
  wrap._serviceSelectMenu=menu;

  const syncSelectDisplay=()=>{
    const chosen=select.options[select.selectedIndex];
    trigger.querySelector('.fb-select-trigger-text').textContent=chosen?.textContent || '';
    menu.querySelectorAll('.fb-select-item').forEach(item=>{
      const selected=item.dataset.value===select.value;
      item.classList.toggle('is-selected',selected);
      item.setAttribute('aria-selected',selected ? 'true' : 'false');
    });
  };

  Array.from(select.options).forEach(option=>{
    const item=document.createElement('button');
    item.type='button';
    item.className='fb-select-item';
    item.dataset.value=option.value;
    item.setAttribute('role','option');
    item.innerHTML='<span></span><span class="fb-select-dot" aria-hidden="true"></span>';
    item.querySelector('span').textContent=option.textContent;
    item.addEventListener('click',event=>{
      event.preventDefault();
      select.value=option.value;
      syncSelectDisplay();
      closeServiceSelects();
      select.dispatchEvent(new Event('change',{bubbles:true}));
    });
    menu.appendChild(item);
  });

  trigger.addEventListener('click',event=>{
    event.preventDefault();
    event.stopPropagation();
    const opening=menu.hidden;
    closeServiceSelects(wrap);
    wrap.classList.toggle('is-open',opening);
    menu.hidden=!opening;
    if(opening) positionServiceSelect(wrap);
    trigger.setAttribute('aria-expanded',opening ? 'true' : 'false');
  });
  menu.addEventListener('click',event=>event.stopPropagation());

  wrap.append(trigger);
  document.body.appendChild(menu);
  syncSelectDisplay();
});
document.addEventListener('click',()=>closeServiceSelects());
document.addEventListener('keydown',event=>{
  if(event.key==='Escape') closeServiceSelects();
});
window.addEventListener('resize',()=>closeServiceSelects());
window.addEventListener('scroll',()=>closeServiceSelects(),{passive:true});

document.querySelectorAll('.fb-select').forEach(select=>{
  select.addEventListener('change',()=>select.closest('form').submit());
});

/* ── service card navigation ───────────────── */
document.querySelectorAll('.gp-card[data-url]').forEach(card=>{
  const openCard=()=>{
    const url=card.dataset.url;
    if(url) window.location.href=url;
  };
  card.addEventListener('click',event=>{
    if(event.target.closest('a,button,input,select,textarea')) return;
    openCard();
  });
  card.addEventListener('keydown',event=>{
    if(event.key==='Enter' || event.key===' '){
      event.preventDefault();
      openCard();
    }
  });
});

/* ── hero pop-out reveal ──────────────────── */
const heroOverlay=document.querySelector('.hero-overlay');
const fromFilterRequest=<?= $fromFilterRequest ? 'true' : 'false' ?>;
if(fromFilterRequest && 'scrollRestoration' in history){
  history.scrollRestoration='manual';
}
function jumpToFilteredCards(){
  const target=document.getElementById('trackWrap') || document.getElementById('filterHolder') || document.getElementById('gpTrack');
  if(!target) return;
  const header=document.querySelector('.navbar');
  const offset=(header?.getBoundingClientRect().height || 0) + 10;
  const top=Math.max(0,target.getBoundingClientRect().top + window.scrollY - offset);
  window.scrollTo({top,behavior:'auto'});
}
if(fromFilterRequest){
  jumpToFilteredCards();
  requestAnimationFrame(jumpToFilteredCards);
  window.addEventListener('load',jumpToFilteredCards,{once:true});
}
if(heroOverlay){
  if(fromFilterRequest){
    heroOverlay.classList.add('no-pop');
  }

  if('IntersectionObserver' in window){
    const heroTarget=document.querySelector('.hero-banner') || heroOverlay;
    let hasLeftHero=false;
    const heroIo=new IntersectionObserver(entries=>entries.forEach(entry=>{
      if(fromFilterRequest && !entry.isIntersecting){
        hasLeftHero=true;
        return;
      }

      if(entry.isIntersecting && (!fromFilterRequest || hasLeftHero)){
        heroOverlay.classList.remove('no-pop');
        heroOverlay.classList.add('is-in');
        heroIo.disconnect();
      }
    }),{threshold:.35});
    heroIo.observe(heroTarget);
  } else {
    heroOverlay.classList.add(fromFilterRequest ? 'no-pop' : 'is-in');
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
