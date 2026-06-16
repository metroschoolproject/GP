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
        return true;
    }, ARRAY_FILTER_USE_BOTH);

    $query = http_build_query($params);
    return URLROOT . '/customerServices/service' . ($query !== '' ? '?' . $query : '');
};

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

/* ══ SCENE — fills viewport below header ═════════════════ */
.gp-scene{
  position:relative;
  width:100%;
  height:calc(100svh - var(--header-h));
  min-height:580px;
  overflow:hidden; /* clip blurred bg only — cards allowed to overflow via JS */
  background:#1a1410;
}

/* blurred background image layer */
.gp-scene-bg{
  position:absolute;inset:-48px;z-index:0;
  background-image:var(--scene-img,none);
  background-size:cover;background-position:center;
  filter:blur(32px) saturate(.85) brightness(.55);
  transform:scale(1.12);
  transition:background-image .6s;
}
/* colour vignette / depth overlay */
.gp-scene-vignette{
  position:absolute;inset:0;z-index:1;pointer-events:none;
  background:
    radial-gradient(ellipse at 50% 105%,rgba(0,0,0,.72) 0%,transparent 58%),
    radial-gradient(ellipse at 50% -5%,rgba(0,0,0,.42) 0%,transparent 52%),
    linear-gradient(180deg,rgba(0,0,0,.12),rgba(0,0,0,.04) 32%,rgba(0,0,0,.04) 68%,rgba(0,0,0,.22));
}

/* ── FLOATING FILTER BAR ── */
.gp-float-bar{
  position:absolute;
  left:50%;
  bottom:46px;
  transform:translateX(-50%);
  z-index:35;
  display:flex;align-items:center;gap:5px;
  background:rgba(14,9,4,.68);
  border:0.5px solid rgba(255,255,255,.11);
  border-radius:999px;
  padding:5px 6px;
  width:max-content;
  backdrop-filter:blur(22px);-webkit-backdrop-filter:blur(22px);
  max-width:calc(100vw - 32px);
  white-space:nowrap;
  overflow-x:auto;
  scrollbar-width:none;
}
.gp-float-bar::-webkit-scrollbar{display:none}

.fb-search{
  display:flex;align-items:center;gap:7px;
  background:rgba(255,255,255,.08);
  border:0.5px solid rgba(255,255,255,.10);
  border-radius:999px;padding:7px 14px;min-width:0;
}
.fb-search svg{flex-shrink:0;opacity:.5}
.fb-search input{
  background:transparent;border:none;
  color:#fff;font-size:12px;font-family:var(--font-body);
  width:148px;
}
.fb-search input::placeholder{color:rgba(255,255,255,.38)}
.fb-search input:focus{outline:none}

.fb-div{width:1px;height:20px;background:rgba(255,255,255,.10);flex-shrink:0}

.fb-chip{
  flex-shrink:0;
  display:flex;align-items:center;gap:5px;
  background:rgba(255,255,255,.07);
  border:0.5px solid rgba(255,255,255,.09);
  border-radius:999px;padding:7px 13px;
  color:rgba(255,255,255,.62);font-size:11px;font-weight:600;
  cursor:pointer;transition:all .15s;
}
.fb-chip:hover{background:rgba(255,255,255,.14);color:#fff}
.fb-chip.on{background:rgba(216,180,106,.22);border-color:rgba(216,180,106,.42);color:#f3d9a4}
.fb-budget{gap:6px}
.fb-budget input{
  width:72px;
  border:none;
  background:transparent;
  color:#fff;
  font-size:11px;
  font-weight:600;
}
.fb-budget input::placeholder{color:rgba(255,255,255,.42)}
.fb-budget input:focus{outline:none}
.fb-budget-sep{color:rgba(255,255,255,.32);font-size:10px}

.fb-select{
  flex-shrink:0;
  background:rgba(255,255,255,.07);
  border:0.5px solid rgba(255,255,255,.09);
  border-radius:999px;
  padding:7px 28px 7px 14px;
  color:rgba(255,255,255,.72);
  font-size:11px;font-weight:600;
  cursor:pointer;appearance:none;
  min-width:128px;max-width:172px;
}
.fb-sort{min-width:108px;max-width:132px}
.fb-find{
  flex-shrink:0;
  display:flex;align-items:center;gap:6px;
  background:var(--c-red);border:none;border-radius:999px;
  padding:8px 18px;color:#fff;font-size:12px;font-weight:700;
  cursor:pointer;transition:background .15s;
}
.fb-find:hover{background:#8f2e2c}

/* ── TRACK + CARDS ── */
.gp-track-wrap{
  position:absolute;
  inset:0;
  z-index:10;
  display:flex;align-items:center;justify-content:center;
  /* DO NOT overflow:hidden here — cards fan outside bounds */
}
.gp-track{
  position:relative;
  width:100%;
  height:var(--card-h,540px);
  transform-style:preserve-3d;
}

.gp-card{
  position:absolute;
  /* width & height set by JS via CSS vars */
  width:var(--card-w,500px);
  height:var(--card-h,540px);
  border-radius:14px;
  overflow:hidden;
  cursor:pointer;
  transform-origin:center bottom;
  transition:transform .55s var(--ease), opacity .55s var(--ease), box-shadow .4s;
  will-change:transform,opacity;
  backface-visibility:hidden;
}
.gp-card.center{
  border-radius:16px;
  box-shadow:0 48px 96px -16px rgba(0,0,0,.55),0 0 0 1px rgba(255,255,255,.07);
  cursor:default;
}
.gp-card:not(.center){
  box-shadow:0 24px 60px -12px rgba(0,0,0,.40);
}

.gc-img{position:absolute;inset:0;background:#261e18}
.gc-img img{width:100%;height:100%;object-fit:cover;transition:transform .6s var(--ease)}
.gp-card.center:hover .gc-img img{transform:scale(1.04)}
.gc-img-ph{position:absolute;inset:0;display:grid;place-items:center;color:rgba(255,255,255,.14)}

/* gradient: stronger at bottom, fades cleanly */
.gc-grad{
  position:absolute;inset:0;
  background:linear-gradient(to top,
    rgba(0,0,0,.92) 0%,
    rgba(0,0,0,.55) 28%,
    rgba(0,0,0,.18) 52%,
    rgba(0,0,0,.04) 72%,
    transparent 100%);
}
.gc-badge{
  position:absolute;top:16px;left:16px;
  background:rgba(255,255,255,.13);
  backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);
  border:0.5px solid rgba(255,255,255,.20);
  border-radius:999px;padding:4px 11px;
  font-size:10px;font-weight:700;color:rgba(255,255,255,.90);
  letter-spacing:.07em;text-transform:uppercase;
}

.gc-body{
  position:absolute;bottom:0;left:0;right:0;
  padding:24px 22px 26px;
}
.gc-sup{font-size:10px;color:rgba(255,255,255,.44);margin-bottom:4px;letter-spacing:.02em}
.gc-name{
  font-family:var(--font-display);
  font-size:clamp(20px,2.2vw,34px);
  font-weight:600;color:#fff;
  line-height:1.08;margin-bottom:14px;
}
.gc-foot{display:flex;align-items:flex-end;justify-content:space-between;gap:8px}
.gc-price-wrap{}
.gc-price{font-family:var(--font-display);font-size:18px;font-weight:600;color:#f3d9a4;line-height:1}
.gc-unit{font-size:10px;color:rgba(255,255,255,.38);margin-top:2px;font-family:var(--font-body)}
.gc-rating{display:flex;align-items:center;gap:4px;font-size:11px;color:rgba(255,255,255,.52);margin-top:6px}

/* View button — only prominent on center card */
.gc-viewbtn{
  display:inline-flex;align-items:center;gap:6px;
  background:rgba(255,255,255,.14);
  border:0.5px solid rgba(255,255,255,.22);
  border-radius:999px;padding:8px 18px;
  font-size:12px;font-weight:700;color:#fff;
  text-decoration:none;
  transition:background .15s,transform .2s;
  white-space:nowrap;
}
.gp-card.center .gc-viewbtn:hover{background:rgba(255,255,255,.26);transform:translateX(3px)}
.gp-card:not(.center) .gc-viewbtn{font-size:11px;padding:6px 13px}

/* ── NAV ARROWS ── */
.gp-nav{
  position:absolute;top:50%;transform:translateY(-50%);z-index:30;
  width:50px;height:50px;border-radius:50%;
  background:rgba(255,255,255,.12);
  border:0.5px solid rgba(255,255,255,.18);
  display:grid;place-items:center;
  cursor:pointer;color:rgba(255,255,255,.82);
  transition:all .2s;backdrop-filter:blur(10px);
  -webkit-user-select:none;user-select:none;
  flex-shrink:0;
}
.gp-nav:hover{background:rgba(255,255,255,.22);color:#fff;border-color:rgba(255,255,255,.32)}
.gp-nav-l{left:clamp(16px,3.5vw,52px)}
.gp-nav-r{right:clamp(16px,3.5vw,52px)}

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

.gp-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,280px),1fr));gap:20px}
.gp-gc{min-width:0;background:var(--c-white);border:1px solid var(--c-rule);border-radius:20px;overflow:hidden;box-shadow:0 12px 28px -10px rgba(118,90,70,.10);transition:all .4s var(--ease);display:flex;flex-direction:column}
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
  .gp-scene{height:calc(100svh - 65px);min-height:520px}
}
@media(max-width:700px){
  .gp-header{padding:12px var(--pad-x)}
  .gp-brand-mark{width:34px;height:34px;font-size:12px}
  .gp-brand{font-size:15px}
  .gp-scene{height:calc(100svh - 59px);min-height:500px}
  .gp-float-bar{bottom:42px;border-radius:22px;max-width:calc(100vw - 24px)}
  .fb-search input{width:90px}
}
@media(max-width:480px){
  :root{--pad-x:16px}
  .gp-footer{flex-direction:column;align-items:flex-start}
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
    <?php require APPROOT . '/views/dashboardLayout/customerNotification.php'; ?>
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

<!-- ══ CAROUSEL SCENE ═══════════════════════════════════ -->
<section class="gp-scene" id="gpScene" aria-label="Service cards">

  <div class="gp-scene-bg" id="sceneBg"></div>
  <div class="gp-scene-vignette"></div>



  <!-- Nav arrows -->
  <button class="gp-nav gp-nav-l" id="navL" aria-label="Previous">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
  </button>
  <button class="gp-nav gp-nav-r" id="navR" aria-label="Next">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
  </button>

  <!-- Track -->
  <div class="gp-track-wrap" id="trackWrap">
    <div class="gp-track" id="gpTrack">
      <?php foreach (array_slice($services, 0, 10) as $ci => $svc):
        $dUrl = URLROOT . '/customerServices/detail/' . (int)$svc['id'] . $detailDateQuery;
      ?>
      <article class="gp-card" data-idx="<?= $ci ?>" data-url="<?= $h($dUrl) ?>"
               data-img="<?= $h(trim((string)($svc['image'] ?? ''))) ?>">
        <div class="gc-img">
          <?php if (trim((string)($svc['image'] ?? '')) !== ''): ?>
            <img src="<?= $h($svc['image']) ?>" alt="<?= $h($svc['name'] ?? '') ?>" loading="<?= $ci < 3 ? 'eager' : 'lazy' ?>">
          <?php else: ?>
            <div class="gc-img-ph">
              <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
            </div>
          <?php endif; ?>
        </div>
        <div class="gc-grad"></div>
        <span class="gc-badge"><?= $h($svc['category'] ?? 'Service') ?></span>
        <div class="gc-body">
          <div class="gc-sup"><?= $h($svc['supplier_name'] ?? '') ?></div>
          <div class="gc-name"><?= $h($svc['name'] ?? '') ?></div>
          <div class="gc-foot">
            <div class="gc-price-wrap">
              <div class="gc-price"><?= $moneyRange($svc) ?></div>
              <div class="gc-unit"><?= $h($durationText($svc)) . ' ' . $pricingUnit($svc) ?></div>
              <?php if ((float)($svc['rating'] ?? 0) > 0): ?>
              <div class="gc-rating">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="#f3d9a4"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                <?= number_format((float)$svc['rating'], 1) ?>
                <span style="opacity:.5;font-weight:400">(<?= (int)($svc['review_count'] ?? 0) ?>)</span>
              </div>
              <?php endif; ?>
            </div>
            <a class="gc-viewbtn" href="<?= $h($dUrl) ?>">
              View
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
            </a>
          </div>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="gp-dots" id="gpDots" aria-hidden="true"></div>

<!-- Filter bar -->
<form class="gp-float-bar" method="GET" action="<?= URLROOT ?>/customerServices/service" role="search">
  <div class="fb-search">
    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
    <input type="search" name="q" value="<?= $h($filters['search'] ?? '') ?>" placeholder="Search services…" aria-label="Search">
  </div>
  <div class="fb-div"></div>
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
  <button class="fb-find" type="submit">
    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
    Find
  </button>
</form>
</section>



<!-- Active filter chips -->
<?php if ($hasActiveFilters): ?>
<div class="gp-chips">
  <?php if (trim((string)($filters['search'] ?? '')) !== ''): ?>
  <span class="gp-chip">"<?= $h($filters['search']) ?>"
    <a class="gp-chip-x" href="<?= $h($serviceUrl(['q' => null])) ?>">✕</a>
  </span>
  <?php endif; ?>
  <?php if ($activeDate !== ''): ?>
  <span class="gp-chip"><?= $h(date('M j, Y', strtotime($activeDate))) ?>
    <a class="gp-chip-x" href="<?= $h($serviceUrl(['date' => null])) ?>">✕</a>
  </span>
  <?php endif; ?>
  <?php if ($activeCategory !== 'all'): ?>
  <span class="gp-chip"><?= $h($activeCategory) ?>
    <a class="gp-chip-x" href="<?= $h($serviceUrl(['category' => 'all'])) ?>">✕</a>
  </span>
  <?php endif; ?>
  <?php if ($activePriceMin !== '' || $activePriceMax !== ''): ?>
  <span class="gp-chip">MMK <?= $h($activePriceMin ?: '0') ?> – <?= $activePriceMax !== '' ? 'MMK ' . $h($activePriceMax) : '∞' ?>
    <a class="gp-chip-x" href="<?= $h($serviceUrl(['price_min' => null, 'price_max' => null])) ?>">✕</a>
  </span>
  <?php endif; ?>
  <?php if ($activeSort !== 'featured'): ?>
  <span class="gp-chip"><?= $h(ucwords(str_replace('_', ' ', $activeSort))) ?>
    <a class="gp-chip-x" href="<?= $h($serviceUrl(['sort' => 'featured'])) ?>">✕</a>
  </span>
  <?php endif; ?>
</div>
<?php endif; ?>

<!-- Remaining grid -->
<?php $remaining = array_slice($services, 10); ?>
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

/* ════════════════════════════════════════════
   FAN CAROUSEL
   ════════════════════════════════════════════ */
const scene  = document.getElementById('gpScene');
const bg     = document.getElementById('sceneBg');
const track  = document.getElementById('gpTrack');
const wrap   = document.getElementById('trackWrap');
const dotsEl = document.getElementById('gpDots');
const prog   = document.getElementById('gpProg');
const navL   = document.getElementById('navL');
const navR   = document.getElementById('navR');

const cards  = Array.from(track ? track.querySelectorAll('.gp-card') : []);
const N      = cards.length;
if(!N) return;

let cur=0, autoT=null;

/* ── sizing ── */
function cardSize(){
  const sh = scene.clientHeight;
  const sw = scene.clientWidth;
  /* card fills ~70% of scene height, aspect 9:16 */
  const h = Math.round(Math.min(sh * .76, sw * .55));
  const w = Math.round(h * (9/16));
  return {w,h};
}

function applySize(){
  const {w,h}=cardSize();
  track.style.cssText=`position:relative;width:100%;height:${h}px;transform-style:preserve-3d`;
  cards.forEach(c=>{ c.style.width=w+'px'; c.style.height=h+'px'; });
}

/* ── layout positions ── */
function layout(){
  const {w,h}=cardSize();
  const sw   = wrap.clientWidth;
  const cx   = sw/2;

  /* dynamic gap so side cards just peek in comfortably */
  const sideGap = Math.min(w*0.68, sw*0.22);
  const farGap  = Math.min(w*1.28, sw*0.44);

  /* update bg */
  const activeImg = cards[cur]?.dataset.img;
  if(bg && activeImg) bg.style.backgroundImage=`url("${activeImg}")`;

  cards.forEach((c,i)=>{
    const rel=((i-cur)+N)%N;
    const pos=rel<=N/2?rel:rel-N;
    let tx,tz,ry,sc,op,zi;

    if(pos===0){
      tx=cx-w/2; tz=0; ry=0; sc=1; op=1; zi=10;
      c.classList.add('center');
    } else if(Math.abs(pos)===1){
      const s=pos>0?1:-1;
      tx=cx-w/2+s*sideGap; tz=-100; ry=s*14; sc=.88; op=.82; zi=8;
      c.classList.remove('center');
    } else if(Math.abs(pos)===2){
      const s=pos>0?1:-1;
      tx=cx-w/2+s*farGap; tz=-210; ry=s*24; sc=.74; op=.52; zi=6;
      c.classList.remove('center');
    } else {
      const s=pos>0?1:-1;
      tx=cx-w/2+s*(farGap*1.6); tz=-320; ry=s*36; sc=.58; op=0; zi=1;
      c.classList.remove('center');
    }
    c.style.transform=`translateX(${tx}px) translateZ(${tz}px) rotateY(${ry}deg) scale(${sc})`;
    c.style.opacity=op;
    c.style.zIndex=zi;
  });

  dotsEl.querySelectorAll('.gp-dot').forEach((d,i)=>d.classList.toggle('on',i===cur));
}

/* ── build dots ── */
cards.forEach((_,i)=>{
  const d=document.createElement('button');
  d.className='gp-dot'+(i===0?' on':'');
  d.setAttribute('aria-label',`Card ${i+1}`);
  d.addEventListener('click',()=>goTo(i));
  dotsEl.appendChild(d);
});

function goTo(idx){
  cur=((idx%N)+N)%N;
  layout();
  resetAuto();
}

/* side-card click → navigate to it */
cards.forEach((c,i)=>{
  c.addEventListener('click',e=>{
    if(i!==cur){ e.preventDefault(); goTo(i); }
  });
});

navL.addEventListener('click',()=>goTo(cur-1));
navR.addEventListener('click',()=>goTo(cur+1));

/* keyboard */
document.addEventListener('keydown',e=>{
  if(e.key==='ArrowLeft') goTo(cur-1);
  if(e.key==='ArrowRight') goTo(cur+1);
});

/* swipe */
let tx0=0;
track.addEventListener('touchstart',e=>{tx0=e.touches[0].clientX},{passive:true});
track.addEventListener('touchend',e=>{
  const dx=e.changedTouches[0].clientX-tx0;
  if(Math.abs(dx)>40) goTo(dx<0?cur+1:cur-1);
},{passive:true});

/* autoplay + progress bar */
function startProg(){
  if(!prog) return;
  prog.style.transition='none'; prog.style.width='0%';
  requestAnimationFrame(()=>requestAnimationFrame(()=>{
    prog.style.transition='width 5s linear'; prog.style.width='100%';
  }));
}
function resetAuto(){
  clearInterval(autoT); startProg();
  autoT=setInterval(()=>goTo(cur+1),5000);
}
scene.addEventListener('mouseenter',()=>{ clearInterval(autoT); if(prog){ prog.style.transition='none'; } });
scene.addEventListener('mouseleave',()=>resetAuto());

/* init */
applySize();
layout();
resetAuto();

let rT;
window.addEventListener('resize',()=>{ clearTimeout(rT); rT=setTimeout(()=>{ applySize(); layout(); },80); });

/* ── scroll reveal ────────────────────────── */
const revEls=document.querySelectorAll('.rev');
if('IntersectionObserver' in window){
  const io=new IntersectionObserver(entries=>entries.forEach(e=>{
    if(e.isIntersecting){ e.target.classList.add('in'); io.unobserve(e.target); }
  }),{threshold:.06,rootMargin:'0px 0px -40px 0px'});
  revEls.forEach(el=>io.observe(el));
} else revEls.forEach(el=>el.classList.add('in'));

})();
</script>
</body>
</html>
