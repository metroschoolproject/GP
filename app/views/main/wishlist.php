<?php
$collections     = $collections     ?? [];
$items           = $items           ?? [];
$total           = $total           ?? 0;
$wishlistCount   = $wishlistCount   ?? 0;
$activeCollection = $activeCollection ?? null;

$isLoggedIn = !empty($_SESSION['session_uid']);
$cartCount  = (int)($cartCount ?? 0);

$h     = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$money = fn($v) => 'MMK ' . number_format((float)$v, 0);
$moneyRange = function ($item) use ($money) {
    $min = (float)($item['price_min'] ?? $item['price'] ?? 0);
    $max = (float)($item['price_max'] ?? $item['price'] ?? 0);
    if ($min > 0 && $max > 0 && $max > $min) {
        return $money($min) . ' – ' . $money($max);
    }
    return $money($max > 0 ? $max : $min);
};

$detailUrl = fn($id) => URLROOT . '/customerServices/detail/' . (int)$id;
$packageDetailUrl = fn($slug) => URLROOT . '/customerServices/packageDetail/' . rawurlencode((string)$slug);
$wishlistPageUrl = URLROOT . '/main/wishlist';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" type="image/png" href="<?= URLROOT ?>/public/images/home/gp_logo.png">
<title>My Wishlist — Golden Promise</title>
<?php include APPROOT . '/views/partials/ga-tracking.php'; ?>
<?php $publicCssVersion = file_exists(APPROOT . '/../public/css/app.css') ? filemtime(APPROOT . '/../public/css/app.css') : time(); ?>
<link rel="stylesheet" href="<?= URLROOT ?>/public/css/app.css?v=<?= $publicCssVersion ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400;1,600&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root{
  --c-bg:#f5e8d9;--c-white:#fcf8f5;--c-rule:#ead8c7;
  --c-strong:#765a46;--c-accent:#6f625a;--c-muted:#9b7d6b;
  --c-text:#211d1a;--c-pale:#b79c8b;
  --c-gold:#d8b46a;--c-red:#b94a48;--c-heart:#e55b5b;
  --font-display:'Playfair Display',Georgia,serif;
  --font-body:'Poppins',system-ui,-apple-system,sans-serif;
  --pad-x:clamp(20px,5vw,72px);
  --ease:cubic-bezier(.19,1,.22,1);
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{
  overflow-x:hidden;
  background:
    linear-gradient(180deg,#ead8c8 0%,#dfc9b7 48%,#d2bba8 100%);
  color:var(--c-text);
  font-family:var(--font-body);
  font-size:14px;
  line-height:1.6;
  -webkit-font-smoothing:antialiased;
}
a{color:inherit;text-decoration:none}
img{display:block;max-width:100%}
button,input,select{font-family:var(--font-body);outline:none}

/* ── HEADER ── */
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
.gp-cart-count{display:inline-flex;align-items:center;justify-content:center;min-width:20px;height:20px;padding:0 6px;border-radius:999px;background:var(--c-strong);color:#fcf8f5;font-size:10px;font-weight:700}
.gp-header-cta{display:inline-flex;align-items:center;justify-content:center;min-height:40px;padding:0 20px;border-radius:999px;border:none;background:var(--c-strong);color:#fffaf3;font-size:13px;font-weight:800;cursor:pointer;box-shadow:0 8px 24px rgba(118,90,70,.22);transition:all .2s}
.gp-header-cta:hover{background:var(--c-red);transform:translateY(-1px)}
.gp-profile-wrap{position:relative}
.gp-profile-btn{display:flex;align-items:center;gap:8px;padding:4px 12px 4px 4px;border-radius:999px;border:1px solid var(--c-rule);background:var(--c-white);cursor:pointer;color:var(--c-strong);font-size:13px;font-weight:600;transition:all .2s}
.gp-profile-btn:hover{border-color:var(--c-red);color:var(--c-red)}
.gp-profile-avatar{display:grid;place-items:center;width:32px;height:32px;border-radius:50%;background:var(--c-strong);color:#fff4e6;font-size:12px;font-weight:800}
.gp-chevron{opacity:.6;transition:transform .2s}
.gp-profile-btn[aria-expanded="true"] .gp-chevron{transform:rotate(180deg)}
.gp-profile-menu{position:absolute;top:calc(100% + 8px);right:0;min-width:200px;padding:6px;border-radius:12px;border:1px solid var(--c-rule);background:var(--c-white);box-shadow:0 12px 35px rgba(15,23,42,.10);opacity:0;visibility:hidden;transform:translateY(-4px);transition:all .15s var(--ease);z-index:200}
.gp-profile-btn[aria-expanded="true"]+.gp-profile-menu{opacity:1;visibility:visible;transform:translateY(0)}
.gp-menu-item{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:8px;font-size:13px;font-weight:600;color:var(--c-text);transition:background .15s;text-decoration:none}
.gp-menu-item:hover{background:rgba(185,74,72,.06)}
.gp-menu-item--danger{color:var(--c-red)}
.gp-menu-item--danger:hover{background:rgba(185,74,72,.08)}

/* ── PAGE LAYOUT ── */
.wl-page{padding:40px var(--pad-x) 80px;min-height:80vh;max-width:1380px;margin:0 auto}
.wl-page-head{display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:16px;flex-wrap:wrap}
.wl-page-title{font-family:var(--font-display);font-size:clamp(28px,3.5vw,42px);font-weight:600;color:var(--c-text);line-height:1}
.wl-layout{display:block}

/* ── COLLECTION TAGS ── */
.wl-sidebar{
  position:relative;
  margin:0 0 30px;
  padding:14px 0 4px;
}
.wl-col-list{display:flex;flex-direction:row;flex-wrap:wrap;gap:8px;align-items:center}
.wl-col-add-wrap{position:relative;display:inline-flex;align-items:center}
.wl-col-item{
  display:flex;align-items:center;gap:8px;
  width:auto;
  min-height:38px;
  padding:8px 14px;border-radius:999px;
  border:1px solid rgba(234,216,199,.9);
  background:rgba(255,248,239,.72);
  font-size:13px;font-weight:600;
  color:var(--c-accent);cursor:pointer;
  transition:all .15s;text-align:left;text-decoration:none;
}
.wl-col-item:hover{background:#fff8ef;color:var(--c-strong);transform:translateY(-1px)}
.wl-col-item.is-active{background:#6D4C5B;border-color:#6D4C5B;color:#fff8ef}
.wl-col-item.is-drop-target{background:rgba(216,180,106,.16);color:var(--c-strong);box-shadow:inset 0 0 0 1px rgba(216,180,106,.36)}
.wl-col-item.is-drag-over{background:rgba(109,76,91,.14);color:#6D4C5B;transform:translateX(3px)}
.wl-col-count{margin-left:2px;font-size:11px;color:var(--c-pale);font-weight:700}
.wl-col-item.is-active .wl-col-count{color:rgba(255,248,239,.72)}
.wl-col-actions{display:none;gap:4px;margin-left:4px;flex-shrink:0}
.wl-col-item:hover .wl-col-actions{display:flex}
@media (hover:none) and (pointer:coarse){
  .wl-col-actions{display:flex !important}
}
.wl-col-actions button{
  display:grid;place-items:center;width:22px;height:22px;border-radius:6px;
  border:none;background:transparent;color:var(--c-pale);cursor:pointer;font-size:11px;
  transition:all .12s;
}
.wl-col-actions button:hover{background:rgba(185,74,72,.10);color:var(--c-red)}

/* Add collection row */
.wl-col-add{
  display:inline-flex;align-items:center;gap:8px;margin-top:12px;
  width:auto;
  padding:8px 14px;border-radius:999px;border:1.5px dotted rgba(216,180,106,.95);
  color:var(--c-pale);font-size:12px;font-weight:600;cursor:pointer;
  transition:all .15s;background:transparent;
}
.wl-col-list .wl-col-add{margin-top:0;min-height:38px}
.wl-col-add:hover{border-color:var(--c-gold);background:rgba(255,248,239,.42);color:var(--c-strong)}
.wl-col-add-form{
  position:absolute;
  left:0;
  top:calc(100% + 8px);
  z-index:25;
  display:none;
  width:300px;
  padding:10px;
  border:1px solid var(--c-rule);
  border-radius:12px;
  background:#fff8ef;
  box-shadow:0 14px 34px rgba(63,36,26,.14);
}
.wl-col-add-form.is-open{display:block}
.wl-col-add-form input{
  width:100%;padding:8px 12px;border-radius:8px;border:1px solid var(--c-rule);
  font-size:13px;background:var(--c-white);margin-bottom:6px;
}
.wl-col-add-form .wl-col-add-btns{display:flex;gap:6px}
.wl-col-add-form button{
  flex:1;padding:6px 0;border-radius:6px;font-size:12px;font-weight:600;cursor:pointer;border:none;
}
.wl-col-save{background:var(--c-strong);color:#fcf8f5}
.wl-col-cancel{background:rgba(118,90,70,.08);color:var(--c-accent)}

/* Rename form */
.wl-col-rename-form{
  position:absolute;
  left:0;
  top:calc(100% + 6px);
  z-index:20;
  display:none;
  min-width:250px;
  padding:8px;
  border:1px solid var(--c-rule);
  border-radius:12px;
  background:#fff8ef;
  box-shadow:0 14px 34px rgba(63,36,26,.14);
}
.wl-col-rename-form.is-open{display:flex;gap:6px;align-items:center}
.wl-col-rename-form input{
  flex:1;padding:6px 10px;border-radius:6px;border:1px solid var(--c-rule);
  font-size:12px;background:var(--c-white);
}
.wl-col-rename-form button{
  padding:6px 10px;border-radius:6px;font-size:11px;font-weight:600;cursor:pointer;border:none;
  background:var(--c-strong);color:#fcf8f5;
}

/* ── ITEMS GRID ── */
.wl-grid{display:grid;grid-template-columns:repeat(3, minmax(0,1fr));gap:26px 24px;align-items:start}
.wl-empty{grid-column:1/-1;text-align:center;padding:80px 24px}
.wl-empty-icon{font-size:48px;margin-bottom:16px;opacity:.4}
.wl-empty h3{font-family:var(--font-display);font-size:28px;font-weight:600;margin-bottom:8px}
.wl-empty p{color:var(--c-accent);font-size:14px;max-width:400px;margin:0 auto 20px}
.wl-empty-btn{
  display:inline-flex;align-items:center;gap:8px;padding:12px 24px;border-radius:999px;
  border:none;background:var(--c-strong);color:#fcf8f5;font-size:13px;font-weight:700;
  cursor:pointer;transition:all .2s;text-decoration:none;
}
.wl-empty-btn:hover{background:var(--c-red);transform:translateY(-1px)}

/* ── CARD ── */
.wl-card{
  position:relative;
  display:flex;
  flex-direction:column;
  height:430px;
  min-height:430px;
  padding:12px;
  overflow:hidden;
  border:1px solid rgba(201,193,187,.58);
  border-radius:14px;
  background:#fff8ef;
  box-shadow:0 18px 42px rgba(63,36,26,.13);
  transition:transform .22s var(--ease),box-shadow .22s var(--ease),border-color .22s var(--ease);
}
.wl-card:hover{transform:translateY(-7px);border-color:rgba(154,104,127,.28);box-shadow:0 24px 52px rgba(63,36,26,.17)}
.wl-card.is-dragging{opacity:.62;transform:scale(.985);cursor:grabbing;box-shadow:0 30px 60px rgba(63,36,26,.22)}
.wl-card.is-filter-hidden,
.wl-card.is-page-hidden{display:none}
.wl-card-img{order:1;display:block;position:relative;height:250px;overflow:hidden;border-radius:8px;background:linear-gradient(160deg,#ede0d0,#ddcebb)}
.wl-card-img img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;border-radius:8px;transition:transform .45s var(--ease)}
.wl-card:hover .wl-card-img img{transform:scale(1.035)}
.wl-card-img-ph{position:absolute;inset:0;display:grid;place-items:center;color:var(--c-pale);font-size:34px;opacity:.35}
.wl-card-body{order:2;display:flex;flex:1;flex-direction:column;padding:14px 4px 2px}
.wl-card-sup{order:2;margin-top:2px;color:#6f625a;font-size:13px;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.wl-card-name{order:1;margin:0 0 3px;color:#211d1a;font-family:var(--font-body);font-size:16px;font-weight:700;line-height:1.25;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.wl-card-meta{order:3;display:flex;align-items:center;gap:10px;margin:7px 0 0;color:#7f6758;font-size:12px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.wl-card-meta span{display:inline-flex;align-items:center;gap:4px}
.wl-card-foot{order:4;display:flex;align-items:center;justify-content:space-between;gap:10px;margin-top:auto;padding-top:12px;border-top:0}
.wl-card-price{display:block;color:#211d1a;font-family:var(--font-body);font-size:15px;font-weight:900;line-height:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.wl-card-unit{display:block;margin-top:3px;color:#8f7666;font-size:11px;font-weight:700}
.wl-card-btn{
  display:inline-flex;align-items:center;justify-content:space-between;gap:12px;min-height:48px;
  padding:6px 8px 6px 20px;border:1px solid rgba(154,104,127,.22);border-radius:12px;
  background:#6D4C5B;color:#fff8ef;font-size:13px;font-weight:800;white-space:nowrap;
  cursor:pointer;transition:background .18s var(--ease),transform .18s var(--ease);text-decoration:none;
}
.wl-card-btn:hover{background:#7E4F65;color:#fff8ef;border-color:rgba(154,104,127,.22);transform:translateY(-1px)}
.wl-card:not(.is-package) .wl-card-btn::after{
  content:'↗';
  display:inline-grid;
  place-items:center;
  width:30px;
  height:30px;
  border-radius:50%;
  background:#fff8ef;
  color:#6D4C5B;
  font-size:14px;
}

/* Package favorites use the same visual language as package cards. */
.wl-card.is-package{
  align-items:stretch;
  height:430px;
  min-height:430px;
  padding:12px;
  border:1px solid rgba(201,193,187,.58);
  border-radius:14px;
  background:#fff8ef;
  box-shadow:0 18px 42px rgba(63,36,26,.13);
  text-align:left;
}
.wl-card.is-package:hover{transform:translateY(-7px);box-shadow:0 24px 52px rgba(63,36,26,.17)}
.wl-card.is-package .wl-card-img{
  order:1;
  width:100%;
  height:250px;
  aspect-ratio:auto;
  border-radius:8px;
  background:#f5e8d9;
}
.wl-card.is-package .wl-card-body{
  order:2;
  width:100%;
  padding:14px 4px 2px;
  align-items:stretch;
}
.wl-card.is-package .wl-card-sup{display:block}
.wl-card.is-package .wl-card-meta{display:flex}
.wl-card.is-package .wl-card-unit{display:block}
.wl-card.is-package .wl-card-name{
  order:1;
  margin:0 0 3px;
  color:#211d1a;
  font-family:var(--font-body);
  font-size:16px;
  font-weight:700;
  line-height:1.25;
  text-align:left;
}
.wl-card-desc{
  display:none;
}
.wl-card.is-package .wl-card-desc{
  display:none;
}
.wl-card.is-package .wl-card-foot{
  order:4;
  flex-direction:row;
  align-items:center;
  width:100%;
  gap:10px;
  margin-top:auto;
  padding-top:12px;
  border-top:0;
}
.wl-card.is-package .wl-card-price{
  color:#211d1a;
  font-family:var(--font-body);
  font-size:15px;
  font-weight:900;
  text-align:left;
}
.wl-card.is-package .wl-card-btn{
  justify-content:space-between;
  width:auto;
  min-height:48px;
  padding:6px 8px 6px 20px;
  border:1px solid rgba(154,104,127,.22);
  border-radius:12px;
  background:#6D4C5B;
  color:#fff8ef;
  font-size:13px;
  font-weight:800;
}
.wl-card.is-package .wl-card-btn::after{
  content:'↗';
  display:grid;
  place-items:center;
  width:30px;
  height:30px;
  border-radius:50%;
  background:#fff8ef;
  color:#6D4C5B;
  font-size:14px;
  line-height:1;
}
.wl-card.is-package .wl-card-heart{
  top:12px;
  right:12px;
  background:rgba(255,248,239,.94);
  color:#e55b5b;
  box-shadow:0 8px 18px rgba(63,36,26,.14);
}
.wl-card.is-package .wl-card-badge{
  top:224px;
  left:12px;
  padding:5px 10px;
  background:#f0dfe7;
  color:#7E4F65;
  font-weight:800;
}
.wl-card.is-package .wl-card-tools{
  width:100%;
  margin-top:12px;
  padding:10px 0 0;
}

/* ── CARD OVERLAYS ── */
.wl-card-heart{
  position:absolute;top:12px;right:12px;z-index:5;
  display:grid;place-items:center;width:34px;height:34px;
  border-radius:50%;border:none;background:rgba(255,248,239,.94);
  color:#6D4C5B;cursor:pointer;font-size:15px;
  box-shadow:0 8px 18px rgba(63,36,26,.14);
  transition:all .2s var(--ease);
}
.wl-card-heart:hover{transform:translateY(-1px);background:#fff8ef}
.wl-card-heart.is-saved{background:#fff8ef;color:#e55b5b}
.wl-card-heart.is-loading{pointer-events:none;opacity:.6}

.wl-card-badge{
  position:absolute;left:12px;top:224px;z-index:5;
  padding:5px 10px;border-radius:7px;
  border:1px solid rgba(154,104,127,.14);
  background:#f0dfe7;color:#7E4F65;
  font-size:11px;font-weight:800;letter-spacing:0;
}
.wl-card-badge--unavailable{background:rgba(185,74,72,.12);color:var(--c-red)}

/* Hidden move-to select kept only for existing move logic compatibility. */
.wl-card-tools{display:none}
.wl-card-select{
  display:none;
  flex:1;padding:6px 28px 6px 10px;border-radius:8px;border:1px solid var(--c-rule);
  font-size:11px;color:var(--c-accent);background:var(--c-white);cursor:pointer;
  appearance:none;-webkit-appearance:none;
  background-image:url("data:image/svg+xml,%3Csvg width='10' height='6' viewBox='0 0 10 6' fill='none'%3E%3Cpath d='M1 1l4 4 4-4' stroke='%239b7d6b' stroke-width='1.5' stroke-linecap='round'/%3E%3C/svg%3E");
  background-repeat:no-repeat;background-position:right 10px center;
}
/* ── TOAST ── */
.gp-toast{
  position:fixed;bottom:28px;left:50%;transform:translateX(-50%) translateY(80px);
  z-index:9999;display:flex;align-items:center;gap:10px;
  padding:12px 20px;border-radius:999px;
  background:#211d1a;color:#fcf8f5;font-size:13px;font-weight:600;
  box-shadow:0 12px 40px rgba(0,0,0,.18);
  opacity:0;transition:all .3s var(--ease);
  pointer-events:none;
}
.gp-toast.is-shown{opacity:1;transform:translateX(-50%) translateY(0)}
.gp-toast a{color:var(--c-gold);text-decoration:underline;margin-left:4px}

.gp-pagination{
  display:flex;
  justify-content:center;
  align-items:center;
  gap:10px;
  padding:28px 0 0;
  flex-wrap:wrap;
}
.gp-pagination[hidden]{display:none}
.gp-page-link{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  min-width:44px;
  height:44px;
  padding:0 14px;
  border-radius:10px;
  border:1px solid rgba(118,90,70,.16);
  background:rgba(255,248,239,.74);
  color:#6f625a;
  font-size:14px;
  font-weight:800;
  transition:transform .18s var(--ease), background .18s, color .18s, border-color .18s;
  cursor:pointer;
}
.gp-page-link svg{width:17px;height:17px;stroke:currentColor}
.gp-page-link:hover{transform:translateY(-2px);background:#fcf8f5;border-color:rgba(154,104,127,.28);color:#7E4F65}
.gp-page-link.is-active{background:#6D4C5B;border-color:#6D4C5B;color:#fcf8f5}
.gp-page-link.is-edge{padding:0;width:44px}
.gp-page-link.is-disabled{opacity:.35;pointer-events:none}

/* ── RESPONSIVE ── */
@media(max-width:900px){
  .wl-sidebar{margin-bottom:24px}
  .wl-col-list{gap:6px}
  .wl-col-item{padding:8px 12px;font-size:12px}
  .wl-col-actions{display:none!important}
  .wl-col-add{width:auto;padding:8px 12px}
  .wl-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
  .gp-header-nav{display:none}
}
@media(max-width:480px){
  :root{--pad-x:16px}
  .wl-grid{grid-template-columns:1fr}
}

/* Match the shared footer wave to this page's lower background so there is no visible color break. */
body .gp-shared-footer::before{
  background:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='260' height='42' viewBox='0 0 260 42'%3E%3Cpath fill='%23d2bba8' d='M0 0h260v18C218 42 174-2 130 18 86 38 42-2 0 18V0Z'/%3E%3C/svg%3E") repeat-x top left/260px 42px;
}
</style>
</head>
<body>

<?php $gpNavActive = 'wishlist'; require APPROOT . '/views/layouts/customerHomeNav.php'; ?>

<!-- PAGE -->
<div class="wl-page">
  <div class="wl-page-head">
    <div>
      <h1 class="wl-page-title">My Wishlist</h1>
    </div>
  </div>

  <section class="wl-sidebar" aria-label="Wishlist collections">
    <div class="wl-col-list" id="wlCollectionList">
      <?php foreach ($collections as $col):
        $colId    = $col['id'] ?? null;
        $colName  = $col['name'] ?? 'All Saved';
        $colCount = (int)($col['item_count'] ?? 0);
        $isActive = ($activeCollection === ($colId !== null ? (int)$colId : null))
                 || ($activeCollection === null && $colId === null);
        $isDefault = !empty($col['is_default']);
        $colUrl = $wishlistPageUrl . ($colId !== null ? '?collection=' . (int)$colId : '');
        ?>
        <div style="position:relative">
          <a class="wl-col-item <?= $isActive ? 'is-active' : '' ?>" href="<?= $h($colUrl) ?>" data-drop-collection="<?= $colId !== null ? (int)$colId : '' ?>">
            <span><?= $h($colName) ?></span>
            <span class="wl-col-count" data-collection-count="<?= $colId !== null ? (int)$colId : '' ?>"><?= $colCount ?></span>
            <?php if (!$isDefault): ?>
            <span class="wl-col-actions" onclick="event.preventDefault();event.stopPropagation()">
              <button title="Rename" onclick="startRename(<?= (int)$colId ?>,'<?= $h($colName) ?>')">✎</button>
              <button title="Delete" onclick="deleteCollection(<?= (int)$colId ?>, event)">×</button>
            </span>
            <?php endif; ?>
          </a>
          <?php if (!$isDefault): ?>
          <div class="wl-col-rename-form" id="renameForm-<?= (int)$colId ?>">
            <input type="text" id="renameInput-<?= (int)$colId ?>" value="<?= $h($colName) ?>" maxlength="100">
            <button onclick="renameCollection(<?= (int)$colId ?>)">Save</button>
          </div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
      <div class="wl-col-add-wrap">
        <button class="wl-col-add" id="wlAddColBtn" onclick="showAddCollection()">+ New collection</button>
        <div class="wl-col-add-form" id="wlAddColForm">
          <input type="text" id="wlNewColName" placeholder="Collection name…" maxlength="100">
          <div class="wl-col-add-btns">
            <button class="wl-col-save" onclick="createCollection()">Create</button>
            <button class="wl-col-cancel" onclick="hideAddCollection()">Cancel</button>
          </div>
        </div>
      </div>
    </div>
  </section>

  <div class="wl-layout">
    <!-- MAIN: Cards grid -->
    <main>
      <?php if (empty($items)): ?>
        <div class="wl-empty">
          <div class="wl-empty-icon">💝</div>
          <h3><?= $activeCollection !== null ? 'This collection is empty' : 'No saved items yet' ?></h3>
          <p>
            <?php if ($activeCollection !== null): ?>
              Move items here or start browsing to fill it up.
            <?php else: ?>
              Start browsing services or packages and tap the heart to save your favorites — your wedding dream team is just a few clicks away.
            <?php endif; ?>
          </p>
        </div>
      <?php else: ?>
      <div class="wl-grid">
        <?php foreach ($items as $item):
          $favId   = (int)($item['favorite_id'] ?? 0);
          $itemType = (string)($item['item_type'] ?? 'service');
          $itemId = (int)($item['item_id'] ?? $item['service_id'] ?? $item['package_id'] ?? 0);
          $svcId   = (int)($item['service_id'] ?? 0);
          $svcName = $item['service_name'] ?? 'Unknown service';
          $svcImg  = trim((string)($item['image'] ?? ''));
          $svcCat  = $item['category'] ?? 'Service';
          $svcSup  = $item['supplier_name'] ?? 'Supplier';
          $svcDesc = trim((string)($item['service_description'] ?? ''));
          $isActive = (bool)($item['is_active'] ?? true);
          $hasImage = $svcImg !== '';
          $colId = $item['collection_id'] ?? null;
          $ratingVal = (float)($item['rating'] ?? 0);
          $reviewCnt = (int)($item['review_count'] ?? 0);
          $isPackageItem = $itemType === 'package';
          $packageSlug = trim((string)($item['package_slug'] ?? ''));
          $itemHref = $isPackageItem
              ? ($packageSlug !== '' ? $packageDetailUrl($packageSlug) : URLROOT . '/customerServices/packages')
              : $detailUrl($svcId);
          $bookingLabel = $isPackageItem ? 'Package' : (($item['booking_type'] ?? 'fullday') === 'slot' ? 'Per session' : (($item['booking_type'] ?? '') === 'flexible' ? 'Flexible' : 'Full day'));
        ?>
          <article class="wl-card <?= $isPackageItem ? 'is-package' : '' ?>" id="wlCard-<?= $favId ?>" draggable="true" data-fav-id="<?= $favId ?>" data-current-collection="<?= $colId !== null ? (int)$colId : '' ?>" data-item-type="<?= $h($itemType) ?>">
            <button class="wl-card-heart is-saved" data-favorite-id="<?= $favId ?>" data-item-type="<?= $h($itemType) ?>" data-item-id="<?= $itemId ?>" aria-label="Remove from wishlist" onclick="toggleWishlist(this, <?= $favId ?>, '<?= $h($itemType) ?>', <?= $itemId ?>)">♥</button>

            <?php if (!$isActive): ?>
              <span class="wl-card-badge wl-card-badge--unavailable">Currently unavailable</span>
            <?php else: ?>
              <span class="wl-card-badge"><?= $h($svcCat) ?></span>
            <?php endif; ?>

            <a class="wl-card-img" href="<?= $h($itemHref) ?>" tabindex="-1" aria-hidden="true">
              <?php if ($hasImage): ?>
                <img src="<?= $h($svcImg) ?>" alt="<?= $h($svcName) ?>" loading="lazy">
              <?php else: ?>
                <div class="wl-card-img-ph">🖼️</div>
              <?php endif; ?>
            </a>

            <div class="wl-card-body">
              <div class="wl-card-sup"><?= $h($svcSup) ?></div>
              <h3 class="wl-card-name"><?= $h($svcName) ?></h3>
              <?php if ($svcDesc !== ''): ?>
                <p class="wl-card-desc"><?= $h($svcDesc) ?></p>
              <?php endif; ?>
              <div class="wl-card-meta">
                <span>⭐ <?= $ratingVal > 0 ? number_format($ratingVal, 1) : 'New' ?></span>
                <span>💬 <?= $reviewCnt ?></span>
                <span>·</span>
                <span><?= $h($bookingLabel) ?></span>
              </div>

              <div class="wl-card-foot">
                <div>
                  <span class="wl-card-price"><?= $moneyRange($item) ?></span>
                  <span class="wl-card-unit"><?= $h($bookingLabel) ?></span>
                </div>
                <a class="wl-card-btn" href="<?= $h($itemHref) ?>"><?= $isPackageItem ? 'View Package' : 'View' ?></a>
              </div>

              <!-- Hidden move-to select kept for compatibility with existing move logic. -->
              <div class="wl-card-tools">
                <select class="wl-card-select" onchange="moveToCollection(<?= $favId ?>, this.value)" data-fav-id="<?= $favId ?>">
                  <option value="" <?= $colId === null ? 'selected' : '' ?>>All Saved</option>
                  <?php foreach ($collections as $col):
                    if (!empty($col['is_default'])) continue;
                    $cId = $col['id'] ?? null;
                    if ($cId === null) continue;
                  ?>
                    <option value="<?= (int)$cId ?>" <?= $colId !== null && (int)$colId === (int)$cId ? 'selected' : '' ?>>
                      <?= $h($col['name'] ?? '') ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
      <nav class="gp-pagination" id="wlPagination" aria-label="Wishlist pages" hidden></nav>
      <?php endif; ?>
    </main>
  </div>
</div>

<!-- TOAST -->
<div class="gp-toast" id="gpToast"></div>

<script>
(function(){
'use strict';

/* ── profile dropdown ── */
document.querySelectorAll('.gp-profile-btn').forEach(function(btn){
  btn.addEventListener('click', function(e){
    e.stopPropagation();
    var was = btn.getAttribute('aria-expanded') === 'true';
    document.querySelectorAll('.gp-profile-btn').forEach(function(b){ b.setAttribute('aria-expanded', 'false'); });
    btn.setAttribute('aria-expanded', String(!was));
  });
});
document.addEventListener('click', function(){
  document.querySelectorAll('.gp-profile-btn').forEach(function(b){ b.setAttribute('aria-expanded', 'false'); });
});

/* ── toast ── */
var toastTimer;
function showToast(msg, link){
  var t = document.getElementById('gpToast');
  t.innerHTML = link ? msg + ' <a href="' + link + '">View →</a>' : msg;
  t.classList.add('is-shown');
  clearTimeout(toastTimer);
  toastTimer = setTimeout(function(){ t.classList.remove('is-shown'); }, 3500);
}

/* ── wishlist toggle ── */
var activeCollectionId = <?= $activeCollection !== null ? (int)$activeCollection : 'null' ?>;
var wishlistPageSize = 6;
var wishlistCurrentPage = 1;
function collectionKey(value){
  return value === null || typeof value === 'undefined' ? '' : String(value);
}
function updateCollectionCount(collectionId, delta){
  var countEl = document.querySelector('[data-collection-count="' + collectionKey(collectionId) + '"]');
  if (!countEl) return;
  var next = Math.max(0, (parseInt(countEl.textContent, 10) || 0) + delta);
  countEl.textContent = next;
}
function setActiveCollectionInSidebar(collectionId){
  var key = collectionKey(collectionId);
  document.querySelectorAll('[data-drop-collection]').forEach(function(item){
    item.classList.toggle('is-active', item.dataset.dropCollection === key);
  });
}
function visibleWishlistCards(){
  return Array.prototype.slice.call(document.querySelectorAll('.wl-card')).filter(function(card){
    return !card.classList.contains('is-filter-hidden');
  });
}
function renderWishlistPagination(){
  var pagination = document.getElementById('wlPagination');
  if (!pagination) return;
  var cards = visibleWishlistCards();
  var totalPages = Math.ceil(cards.length / wishlistPageSize);
  if (totalPages <= 1) {
    pagination.hidden = true;
    cards.forEach(function(card){ card.classList.remove('is-page-hidden'); });
    wishlistCurrentPage = 1;
    return;
  }

  wishlistCurrentPage = Math.min(Math.max(wishlistCurrentPage, 1), totalPages);
  cards.forEach(function(card, index){
    var page = Math.floor(index / wishlistPageSize) + 1;
    card.classList.toggle('is-page-hidden', page !== wishlistCurrentPage);
  });

  var prevDisabled = wishlistCurrentPage === 1 ? ' is-disabled' : '';
  var nextDisabled = wishlistCurrentPage === totalPages ? ' is-disabled' : '';
  var html = '<button class="gp-page-link is-edge' + prevDisabled + '" type="button" data-wl-page="' + (wishlistCurrentPage - 1) + '" aria-label="Previous page">' +
    '<svg viewBox="0 0 24 24" fill="none" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>' +
    '</button>';
  for (var page = 1; page <= totalPages; page++) {
    html += '<button class="gp-page-link' + (page === wishlistCurrentPage ? ' is-active' : '') + '" type="button" data-wl-page="' + page + '"' + (page === wishlistCurrentPage ? ' aria-current="page"' : '') + '>' + page + '</button>';
  }
  html += '<button class="gp-page-link is-edge' + nextDisabled + '" type="button" data-wl-page="' + (wishlistCurrentPage + 1) + '" aria-label="Next page">' +
    '<svg viewBox="0 0 24 24" fill="none" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>' +
    '</button>';
  pagination.innerHTML = html;
  pagination.hidden = false;
}
document.getElementById('wlPagination')?.addEventListener('click', function(event){
  var button = event.target.closest('[data-wl-page]');
  if (!button || button.classList.contains('is-disabled')) return;
  wishlistCurrentPage = parseInt(button.dataset.wlPage, 10) || 1;
  renderWishlistPagination();
  document.querySelector('.wl-grid')?.scrollIntoView({behavior:'smooth', block:'start'});
});
function filterCardsWithoutRefresh(collectionId){
  var key = collectionKey(collectionId);
  document.querySelectorAll('.wl-card').forEach(function(card){
    var show = key === '' || collectionKey(card.dataset.currentCollection) === key;
    card.classList.toggle('is-filter-hidden', !show);
  });
  wishlistCurrentPage = 1;
  renderWishlistPagination();
  activeCollectionId = key === '' ? null : parseInt(key, 10);
  setActiveCollectionInSidebar(activeCollectionId);
  var nextUrl = '<?= $wishlistPageUrl ?>' + (key !== '' ? '?collection=' + encodeURIComponent(key) : '');
  if (window.history && window.history.replaceState) {
    window.history.replaceState(null, '', nextUrl);
  }
}
function showCollectionWithoutRefresh(collectionId){
  filterCardsWithoutRefresh(collectionId);
}
window.toggleWishlist = function(btn, favoriteId, itemType, itemId){
  btn.classList.add('is-loading');
  fetch('<?= URLROOT ?>/main/toggleWishlist', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({item_type: itemType, item_id: itemId, collection_id: null})
  })
  .then(function(r){ return r.json(); })
  .then(function(d){
    btn.classList.remove('is-loading');
    if (d.ok && d.action === 'removed') {
      var card = document.getElementById('wlCard-' + favoriteId);
      if (card) {
        card.style.opacity = '0';
        card.style.transform = 'scale(.95)';
        card.style.transition = 'all .25s ease';
        setTimeout(function(){
          card.remove();
          renderWishlistPagination();
          if (!document.querySelector('.wl-card')) {
            // Last item removed — if inside a collection, delete the collection too
            if (activeCollectionId !== null) {
              fetch('<?= URLROOT ?>/main/collectionDelete', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({collection_id: activeCollectionId})
              })
              .then(function(){ location.href = '<?= $wishlistPageUrl ?>'; })
              .catch(function(){ location.href = '<?= $wishlistPageUrl ?>'; });
            } else {
              location.reload();
            }
          }
        }, 260);
      }
      showToast('Removed from wishlist');
    }
  })
  .catch(function(){ btn.classList.remove('is-loading'); });
};

/* ── collection CRUD ── */
window.showAddCollection = function(){
  document.getElementById('wlAddColForm').classList.add('is-open');
  document.getElementById('wlNewColName').focus();
};
window.hideAddCollection = function(){
  document.getElementById('wlAddColForm').classList.remove('is-open');
  document.getElementById('wlNewColName').value = '';
};
window.createCollection = function(){
  var name = document.getElementById('wlNewColName').value.trim();
  if (!name) return;
  fetch('<?= URLROOT ?>/main/collectionCreate', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({name: name})
  })
  .then(function(r){ return r.json(); })
  .then(function(d){
    if (d.ok) { location.reload(); }
    else { showToast(d.error || 'Failed to create collection'); }
  })
  .catch(function(){});
};

window.startRename = function(colId, currentName){
  var form = document.getElementById('renameForm-' + colId);
  var input = document.getElementById('renameInput-' + colId);
  form.classList.toggle('is-open');
  if (form.classList.contains('is-open')) { input.value = currentName; input.focus(); input.select(); }
};
window.renameCollection = function(colId){
  var name = document.getElementById('renameInput-' + colId).value.trim();
  if (!name) return;
  fetch('<?= URLROOT ?>/main/collectionRename', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({collection_id: colId, name: name})
  })
  .then(function(r){ return r.json(); })
  .then(function(d){
    if (d.ok) { location.reload(); }
    else { showToast(d.error || 'Failed to rename'); }
  })
  .catch(function(){});
};

window.deleteCollection = function(colId, e){
  if (e) { e.preventDefault(); e.stopPropagation(); }
  if (!confirm('Delete this collection? Items inside will move to "All Saved".')) return;
  fetch('<?= URLROOT ?>/main/collectionDelete', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({collection_id: colId})
  })
  .then(function(r){ return r.json(); })
  .then(function(d){
    if (d.ok) {
      showToast('Collection deleted');
      setTimeout(function(){ location.href = '<?= $wishlistPageUrl ?>'; }, 500);
    } else {
      showToast(d.error || 'Failed to delete');
      console.error('Delete collection failed:', d);
    }
  })
  .catch(function(err){
    showToast('Network error — please try again');
    console.error('Delete collection error:', err);
  });
};

/* ── move to collection ── */
window.moveToCollection = function(favId, collectionId){
  return fetch('<?= URLROOT ?>/main/moveToCollection', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({favorite_id: favId, collection_id: collectionId === '' ? null : parseInt(collectionId)})
  })
  .then(function(r){ return r.json(); })
  .then(function(d){
    if (d.ok) {
      showToast('Moved to collection');
      var card = document.getElementById('wlCard-' + favId);
      if (card) {
        var previousCollection = collectionKey(card.dataset.currentCollection);
        var nextCollection = collectionKey(collectionId);
        card.dataset.currentCollection = collectionId;
        var select = card.querySelector('.wl-card-select');
        if (select) select.value = collectionId;
        if (previousCollection !== nextCollection) {
          updateCollectionCount(previousCollection, -1);
          updateCollectionCount(nextCollection, 1);
        }
        if (activeCollectionId === null) {
          showCollectionWithoutRefresh(nextCollection);
        } else if (String(activeCollectionId) !== String(collectionId)) {
          card.style.opacity = '0';
          card.style.transform = 'scale(.96)';
          card.style.transition = 'all .22s ease';
          setTimeout(function(){
            card.remove();
            renderWishlistPagination();
          }, 230);
        }
      }
    }
    return d;
  })
  .catch(function(){ showToast('Could not move item'); });
};

/* ── drag cards into sidebar collection tags ── */
var draggedCard = null;
document.querySelectorAll('.wl-card[draggable="true"]').forEach(function(card){
  card.addEventListener('dragstart', function(event){
    if (event.target.closest('button,input,select,textarea')) {
      event.preventDefault();
      return;
    }
    draggedCard = card;
    card.classList.add('is-dragging');
    event.dataTransfer.effectAllowed = 'move';
    event.dataTransfer.setData('text/plain', card.dataset.favId || '');
    if (event.dataTransfer.setDragImage) {
      var ghost = card.cloneNode(true);
      var rect = card.getBoundingClientRect();
      ghost.style.position = 'fixed';
      ghost.style.top = '-1200px';
      ghost.style.left = '-1200px';
      ghost.style.width = rect.width + 'px';
      ghost.style.height = rect.height + 'px';
      ghost.style.pointerEvents = 'none';
      ghost.style.opacity = '.92';
      ghost.style.transform = 'none';
      ghost.style.boxShadow = '0 28px 70px rgba(63,36,26,.28)';
      document.body.appendChild(ghost);
      event.dataTransfer.setDragImage(ghost, Math.min(event.offsetX || rect.width / 2, rect.width - 10), Math.min(event.offsetY || rect.height / 2, rect.height - 10));
      setTimeout(function(){ ghost.remove(); }, 0);
    }
    document.querySelectorAll('[data-drop-collection]').forEach(function(target){
      target.classList.add('is-drop-target');
    });
  });
  card.addEventListener('dragend', function(){
    card.classList.remove('is-dragging');
    draggedCard = null;
    document.querySelectorAll('[data-drop-collection]').forEach(function(target){
      target.classList.remove('is-drop-target','is-drag-over');
    });
  });
});

document.querySelectorAll('[data-drop-collection]').forEach(function(target){
  target.addEventListener('dragover', function(event){
    if (!draggedCard) return;
    event.preventDefault();
    event.dataTransfer.dropEffect = 'move';
    target.classList.add('is-drag-over');
  });
  target.addEventListener('dragleave', function(){
    target.classList.remove('is-drag-over');
  });
  target.addEventListener('drop', function(event){
    if (!draggedCard) return;
    event.preventDefault();
    target.classList.remove('is-drag-over');
    var favId = parseInt(event.dataTransfer.getData('text/plain') || draggedCard.dataset.favId || '0', 10);
    var collectionId = target.dataset.dropCollection || '';
    if (!favId) return;
    if (String(draggedCard.dataset.currentCollection || '') === String(collectionId)) {
      showToast('Already in this collection');
      return;
    }
    window.moveToCollection(favId, collectionId);
  });
});

renderWishlistPagination();

})();
</script>
<?php include APPROOT . '/views/partials/cookie-consent.php'; ?>
</body>
</html>
