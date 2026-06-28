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
<title>My Wishlist — Golden Promise</title>
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
body{overflow-x:hidden;background:#faf5ef;color:var(--c-text);font-family:var(--font-body);font-size:14px;line-height:1.6;-webkit-font-smoothing:antialiased}
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
.wl-page{padding:40px var(--pad-x) 80px;min-height:80vh;max-width:1200px;margin:0 auto}
.wl-page-head{display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:32px;flex-wrap:wrap}
.wl-page-title{font-family:var(--font-display);font-size:clamp(28px,3.5vw,42px);font-weight:600;color:var(--c-text);line-height:1}
.wl-page-count{font-size:13px;color:var(--c-pale);font-weight:500}
.wl-layout{display:grid;grid-template-columns:260px 1fr;gap:32px;align-items:start}

/* ── COLLECTIONS SIDEBAR ── */
.wl-sidebar{position:sticky;top:100px}
.wl-col-list{display:flex;flex-direction:column;gap:2px}
.wl-col-item{
  display:flex;align-items:center;gap:10px;
  padding:10px 14px;border-radius:10px;
  font-size:13px;font-weight:600;
  color:var(--c-accent);cursor:pointer;
  transition:all .15s;border:none;background:transparent;width:100%;text-align:left;text-decoration:none;
}
.wl-col-item:hover{background:rgba(118,90,70,.06);color:var(--c-strong)}
.wl-col-item.is-active{background:rgba(185,74,72,.08);color:var(--c-red)}
.wl-col-icon{flex-shrink:0;width:16px;height:16px;display:grid;place-items:center;opacity:.6}
.wl-col-count{margin-left:auto;font-size:11px;color:var(--c-pale);font-weight:500}
.wl-col-actions{display:none;gap:4px;margin-left:4px}
.wl-col-item:hover .wl-col-actions{display:flex}
.wl-col-actions button{
  display:grid;place-items:center;width:22px;height:22px;border-radius:6px;
  border:none;background:transparent;color:var(--c-pale);cursor:pointer;font-size:11px;
  transition:all .12s;
}
.wl-col-actions button:hover{background:rgba(185,74,72,.10);color:var(--c-red)}

/* Add collection row */
.wl-col-add{
  display:flex;align-items:center;gap:8px;margin-top:10px;
  padding:8px 14px;border-radius:10px;border:1.5px dashed var(--c-rule);
  color:var(--c-pale);font-size:12px;font-weight:600;cursor:pointer;
  transition:all .15s;background:transparent;width:100%;
}
.wl-col-add:hover{border-color:var(--c-gold);color:var(--c-strong)}
.wl-col-add-form{display:none;margin-top:8px}
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
.wl-col-rename-form{display:none;margin-top:6px}
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
.wl-grid{display:grid;grid-template-columns:repeat(2, 1fr);gap:20px;align-items:start}
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
  background:var(--c-white);border-radius:16px;overflow:hidden;
  border:1px solid var(--c-rule);position:relative;
  display:flex;flex-direction:column;
  transition:transform .25s var(--ease),box-shadow .25s var(--ease);
}
.wl-card:hover{transform:translateY(-3px);box-shadow:0 16px 40px -12px rgba(118,90,70,.14)}
.wl-card-img{display:block;aspect-ratio:16/10;overflow:hidden;background:linear-gradient(160deg,#ede0d0,#ddcebb);position:relative}
.wl-card-img img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;transition:transform .5s var(--ease)}
.wl-card:hover .wl-card-img img{transform:scale(1.05)}
.wl-card-img-ph{position:absolute;inset:0;display:grid;place-items:center;font-size:36px;opacity:.2}
.wl-card-body{padding:16px 18px;flex:1;display:flex;flex-direction:column}
.wl-card-sup{font-size:11px;font-weight:600;color:var(--c-muted);display:flex;align-items:center;gap:4px;margin-bottom:4px}
.wl-card-name{font-family:var(--font-display);font-size:19px;font-weight:600;line-height:1.15;color:var(--c-text);margin-bottom:4px}
.wl-card-meta{display:flex;align-items:center;gap:10px;font-size:11px;color:var(--c-accent);margin-bottom:8px}
.wl-card-meta span{display:inline-flex;align-items:center;gap:4px}
.wl-card-foot{display:flex;align-items:center;justify-content:space-between;margin-top:auto;padding-top:12px;border-top:1px solid var(--c-rule)}
.wl-card-price{font-family:var(--font-display);font-size:18px;font-weight:600;color:var(--c-red);line-height:1}
.wl-card-unit{font-size:10px;color:var(--c-pale);display:block;margin-top:1px}
.wl-card-btn{
  display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:999px;
  border:1px solid var(--c-rule);background:var(--c-white);color:var(--c-strong);
  font-size:12px;font-weight:700;cursor:pointer;transition:all .2s;text-decoration:none;
}
.wl-card-btn:hover{background:var(--c-red);color:#fcf8f5;border-color:var(--c-red)}

/* Package favorites use the same visual language as package cards. */
.wl-card.is-package{
  align-items:center;
  min-height:470px;
  padding:12px;
  border:1px solid rgba(201,193,187,.58);
  border-radius:24px;
  background:#fff8ef;
  box-shadow:0 18px 42px rgba(63,36,26,.13);
  text-align:center;
}
.wl-card.is-package:hover{transform:translateY(-7px);box-shadow:0 24px 52px rgba(63,36,26,.17)}
.wl-card.is-package .wl-card-img{
  width:100%;
  height:250px;
  aspect-ratio:auto;
  border-radius:18px;
  background:#f5e8d9;
}
.wl-card.is-package .wl-card-body{
  width:100%;
  padding:18px 4px 4px;
  align-items:center;
}
.wl-card.is-package .wl-card-sup,
.wl-card.is-package .wl-card-meta,
.wl-card.is-package .wl-card-unit{display:none}
.wl-card.is-package .wl-card-name{
  margin:0 0 8px;
  color:#211d1a;
  font-family:var(--font-body);
  font-size:18px;
  font-weight:800;
  line-height:1.25;
  text-align:center;
}
.wl-card-desc{
  display:none;
}
.wl-card.is-package .wl-card-desc{
  display:-webkit-box;
  max-width:300px;
  margin:0 auto 12px;
  overflow:hidden;
  color:#6f625a;
  font-size:13px;
  font-weight:500;
  line-height:1.6;
  -webkit-line-clamp:2;
  -webkit-box-orient:vertical;
}
.wl-card.is-package .wl-card-foot{
  flex-direction:column;
  align-items:center;
  width:100%;
  gap:14px;
  margin-top:auto;
  padding-top:0;
  border-top:0;
}
.wl-card.is-package .wl-card-price{
  color:#7E4F65;
  font-family:var(--font-body);
  font-size:18px;
  font-weight:800;
  text-align:center;
}
.wl-card.is-package .wl-card-btn{
  justify-content:space-between;
  width:100%;
  min-height:54px;
  padding:7px 8px 7px 24px;
  border:0;
  border-radius:18px;
  background:#6D4C5B;
  color:#fff8ef;
  font-size:16px;
  font-weight:800;
}
.wl-card.is-package .wl-card-btn::after{
  content:'↗';
  display:grid;
  place-items:center;
  width:40px;
  height:40px;
  border-radius:50%;
  background:#fff8ef;
  color:#6D4C5B;
  font-size:18px;
  line-height:1;
}
.wl-card.is-package .wl-card-heart{
  top:26px;
  right:26px;
  background:rgba(255,248,239,.94);
  color:#e55b5b;
  box-shadow:0 8px 18px rgba(63,36,26,.14);
}
.wl-card.is-package .wl-card-badge{
  top:26px;
  left:26px;
  padding:6px 12px;
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
  display:grid;place-items:center;width:36px;height:36px;
  border-radius:50%;border:none;background:rgba(0,0,0,.35);
  backdrop-filter:blur(6px);-webkit-backdrop-filter:blur(6px);
  color:#fcf8f5;cursor:pointer;font-size:16px;
  transition:all .2s var(--ease);
}
.wl-card-heart:hover{transform:scale(1.12)}
.wl-card-heart.is-saved{background:var(--c-heart);color:#fcf8f5}
.wl-card-heart.is-loading{pointer-events:none;opacity:.6}

.wl-card-badge{
  position:absolute;top:12px;left:12px;z-index:5;
  padding:4px 10px;border-radius:999px;
  font-size:10px;font-weight:700;letter-spacing:.04em;
  background:rgba(255,250,246,.9);color:var(--c-strong);
}
.wl-card-badge--unavailable{background:rgba(185,74,72,.12);color:var(--c-red)}

/* Move-to & notes bar */
.wl-card-tools{display:flex;align-items:center;gap:8px;margin-top:10px;padding-top:10px;border-top:1px solid var(--c-rule)}
.wl-card-select{
  flex:1;padding:6px 28px 6px 10px;border-radius:8px;border:1px solid var(--c-rule);
  font-size:11px;color:var(--c-accent);background:var(--c-white);cursor:pointer;
  appearance:none;-webkit-appearance:none;
  background-image:url("data:image/svg+xml,%3Csvg width='10' height='6' viewBox='0 0 10 6' fill='none'%3E%3Cpath d='M1 1l4 4 4-4' stroke='%239b7d6b' stroke-width='1.5' stroke-linecap='round'/%3E%3C/svg%3E");
  background-repeat:no-repeat;background-position:right 10px center;
}
.wl-card-note-btn{
  display:grid;place-items:center;width:30px;height:30px;border-radius:8px;
  border:1px solid var(--c-rule);background:var(--c-white);color:var(--c-pale);
  cursor:pointer;font-size:12px;transition:all .12s;
}
.wl-card-note-btn:hover,.wl-card-note-btn.has-note{border-color:var(--c-gold);color:var(--c-gold)}
.wl-card-note-inline{display:none;margin-top:8px;gap:6px}
.wl-card-note-inline.is-open{display:flex}
.wl-card-note-inline input{
  flex:1;padding:6px 10px;border-radius:8px;border:1px solid var(--c-rule);
  font-size:12px;background:var(--c-white);
}
.wl-card-note-inline button{
  padding:6px 12px;border-radius:8px;border:none;font-size:11px;font-weight:600;
  background:var(--c-strong);color:#fcf8f5;cursor:pointer;
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

/* ── RESPONSIVE ── */
@media(max-width:900px){
  .wl-layout{grid-template-columns:1fr}
  .wl-sidebar{position:static;margin-bottom:24px}
  .wl-col-list{flex-direction:row;flex-wrap:wrap;gap:4px}
  .wl-col-item{padding:8px 12px;font-size:12px;border-radius:8px}
  .wl-col-count{display:none}
  .wl-col-actions{display:none!important}
  .wl-col-add{width:auto;padding:8px 12px}
  .wl-grid{grid-template-columns:1fr}
  .gp-header-nav{display:none}
}
@media(max-width:480px){
  :root{--pad-x:16px}
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
      <span class="wl-page-count"><?= $total ?> saved item<?= $total !== 1 ? 's' : '' ?></span>
    </div>
    <?php if ($total > 0): ?>
    <a class="wl-card-btn" href="<?= URLROOT ?>/customerServices/packages">Browse packages</a>
    <?php endif; ?>
  </div>

  <div class="wl-layout">
    <!-- SIDEBAR: Collections -->
    <aside class="wl-sidebar">
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
            <a class="wl-col-item <?= $isActive ? 'is-active' : '' ?>" href="<?= $h($colUrl) ?>">
              <span class="wl-col-icon"><?= $isDefault ? '📋' : '📁' ?></span>
              <span><?= $h($colName) ?></span>
              <span class="wl-col-count"><?= $colCount ?></span>
              <?php if (!$isDefault): ?>
              <span class="wl-col-actions" onclick="event.preventDefault();event.stopPropagation()">
                <button title="Rename" onclick="startRename(<?= (int)$colId ?>,'<?= $h($colName) ?>')">✎</button>
                <button title="Delete" onclick="deleteCollection(<?= (int)$colId ?>)">×</button>
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
      </div>

      <button class="wl-col-add" id="wlAddColBtn" onclick="showAddCollection()">+ New collection</button>
      <div class="wl-col-add-form" id="wlAddColForm">
        <input type="text" id="wlNewColName" placeholder="Collection name…" maxlength="100">
        <div class="wl-col-add-btns">
          <button class="wl-col-save" onclick="createCollection()">Create</button>
          <button class="wl-col-cancel" onclick="hideAddCollection()">Cancel</button>
        </div>
      </div>
    </aside>

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
          <a class="wl-empty-btn" href="<?= URLROOT ?>/customerServices/packages">
            Browse packages <span style="font-size:16px">→</span>
          </a>
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
          $svcNote = $item['notes'] ?? '';
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
          <article class="wl-card <?= $isPackageItem ? 'is-package' : '' ?>" id="wlCard-<?= $favId ?>">
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

              <!-- Move-to & notes -->
              <div class="wl-card-tools">
                <select class="wl-card-select" onchange="moveToCollection(<?= $favId ?>, this.value)" data-fav-id="<?= $favId ?>">
                  <option value="" <?= $colId === null ? 'selected' : '' ?>>📋 All Saved</option>
                  <?php foreach ($collections as $col):
                    if (!empty($col['is_default'])) continue;
                    $cId = $col['id'] ?? null;
                    if ($cId === null) continue;
                  ?>
                    <option value="<?= (int)$cId ?>" <?= $colId !== null && (int)$colId === (int)$cId ? 'selected' : '' ?>>
                      📁 <?= $h($col['name'] ?? '') ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <button class="wl-card-note-btn <?= $svcNote !== '' ? 'has-note' : '' ?>" title="<?= $svcNote !== '' ? $h($svcNote) : 'Add note' ?>" onclick="toggleNote(<?= $favId ?>)" data-fav-id="<?= $favId ?>">💬</button>
              </div>
              <div class="wl-card-note-inline" id="noteInline-<?= $favId ?>">
                <input type="text" id="noteInput-<?= $favId ?>" value="<?= $h($svcNote) ?>" placeholder="Add a note…" maxlength="500">
                <button onclick="saveNote(<?= $favId ?>)">Save</button>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
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
          if (!document.querySelector('.wl-card')) { location.reload(); }
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

window.deleteCollection = function(colId){
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
    } else { showToast(d.error || 'Failed to delete'); }
  })
  .catch(function(){});
};

/* ── move to collection ── */
window.moveToCollection = function(favId, collectionId){
  fetch('<?= URLROOT ?>/main/moveToCollection', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({favorite_id: favId, collection_id: collectionId === '' ? null : parseInt(collectionId)})
  })
  .then(function(r){ return r.json(); })
  .then(function(d){ if (d.ok) { showToast('Moved to collection'); } })
  .catch(function(){});
};

/* ── notes ── */
window.toggleNote = function(favId){
  var inline = document.getElementById('noteInline-' + favId);
  inline.classList.toggle('is-open');
  if (inline.classList.contains('is-open')) { document.getElementById('noteInput-' + favId).focus(); }
};
window.saveNote = function(favId){
  var note = document.getElementById('noteInput-' + favId).value;
  fetch('<?= URLROOT ?>/main/addNote', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({favorite_id: favId, note: note})
  })
  .then(function(r){ return r.json(); })
  .then(function(d){
    if (d.ok) {
      document.getElementById('noteInline-' + favId).classList.remove('is-open');
      var btn = document.querySelector('.wl-card-note-btn[data-fav-id="' + favId + '"]');
      if (note.trim() !== '') { btn.classList.add('has-note'); btn.title = note; }
      else { btn.classList.remove('has-note'); btn.title = 'Add note'; }
      showToast('Note saved');
    }
  })
  .catch(function(){});
};

})();
</script>
</body>
</html>
