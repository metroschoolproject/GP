<?php
$items = $items ?? [];
$total = (float)($total ?? 0);
$cartCount = (int)($cartCount ?? 0);

$isLoggedIn = !empty($_SESSION['session_uid']);
$authNavUrl = $isLoggedIn ? URLROOT . '/users/logout' : URLROOT . '/users/auth';
$authNavLabel = $isLoggedIn ? 'Logout' : 'Sign in';

$money = fn($v) => 'RM ' . number_format((float)$v, 0);
$plain = function ($v) {
    $text = (string)$v;
    for ($i = 0; $i < 10; $i++) {
        $decoded = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if ($decoded === $text) {
            break;
        }
        $text = $decoded;
    }

    return $text;
};
$h = fn($v) => htmlspecialchars($plain($v), ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Cart — Golden Promise</title>
<?php
$publicCssVersion = file_exists(APPROOT . '/../public/css/app.css') ? filemtime(APPROOT . '/../public/css/app.css') : time();
?>
<link rel="stylesheet" href="<?= URLROOT ?>/public/css/app.css?v=<?= $publicCssVersion ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
<style>
:root {
  --c-bg:        #f5e8d9;
  --c-surface:   #faf5ef;
  --c-white:     #ffffff;
  --c-card:      #faf5ef;
  --c-rule:      #ead8c7;
  --c-strong:    #6d4c5b;
  --c-accent:    #7b5c69;
  --c-muted:     #b79c8b;
  --c-text:      #111827;
  --c-danger:    #b94b4b;

  --r-card:  0.75rem;
  --sh-card:   0 20px 40px rgba(15, 23, 42, 0.08);
  --sh-panel:  0 18px 45px rgba(15, 23, 42, 0.06);

  --font-display: 'Playfair Display', Georgia, serif;
  --font-body:    'Poppins', system-ui, -apple-system, sans-serif;

  --pad-x: clamp(20px, 5vw, 72px);
  --ease-out-expo: cubic-bezier(0.19, 1, 0.22, 1);
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

body {
  background: var(--c-bg);
  color: var(--c-text);
  font-family: var(--font-body);
  font-size: 14px;
  line-height: 1.6;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  min-height: 100vh;
  display: flex; flex-direction: column;
}

a { color: inherit; text-decoration: none; }
img { display: block; max-width: 100%; }
button { font-family: var(--font-body); outline: none; }

/* Texture */
.gp-texture {
  position: fixed; inset: 0; z-index: -1; pointer-events: none;
  background-image:
    radial-gradient(ellipse at 20% 8%, rgba(109,76,91,0.04) 0%, transparent 60%),
    radial-gradient(ellipse at 80% 92%, rgba(183,156,139,0.07) 0%, transparent 55%);
}

/* Header */
.gp-header {
  position: sticky; top: 0; z-index: 50;
  display: grid;
  grid-template-columns: auto 1fr auto;
  align-items: center;
  gap: 24px;
  padding: 16px var(--pad-x);
  border-bottom: 1px solid rgba(184, 154, 109, 0.25);
  background: rgba(248, 245, 239, 0.90);
  backdrop-filter: blur(18px);
  -webkit-backdrop-filter: blur(18px);
}

.gp-brand {
  display: flex; align-items: center; gap: 12px;
  color: #211b17;
  font-size: 18px;
  font-weight: 800;
  white-space: nowrap;
}

.gp-brand-mark {
  display: grid; place-items: center;
  width: 40px; height: 40px;
  border-radius: 50%;
  background: var(--c-strong);
  color: #fffaf3;
  font-size: 14px;
  letter-spacing: 1px;
}

.gp-header-nav {
  display: flex; align-items: center; justify-content: center; gap: 4px;
}

.gp-header-nav a {
  padding: 8px 18px; border-radius: 999px;
  font-size: 13px; font-weight: 700; color: #51483f;
  transition: all 0.2s;
}
.gp-header-nav a:hover { color: var(--c-strong); background: rgba(109,76,91,0.08); }
.gp-header-nav a.active { color: var(--c-strong); background: rgba(109,76,91,0.08); }

.gp-header-actions {
  display: flex; align-items: center; gap: 12px; justify-content: flex-end;
}

.gp-header-cta {
  display: inline-flex; align-items: center; justify-content: center;
  min-height: 40px; padding: 0 20px; border-radius: 999px; border: none;
  background: var(--c-strong); color: #fffaf3;
  font-size: 13px; font-weight: 800;
  cursor: pointer;
  box-shadow: 0 14px 30px rgba(109,76,91,0.18);
  transition: all 0.2s;
}
.gp-header-cta:hover { background: #5a3d4a; transform: translateY(-1px); }

/* Cart header badge */
.gp-cart-badge {
  display: inline-flex; align-items: center; gap: 6px;
  position: relative;
  padding: 8px 14px 8px 10px;
  border-radius: 999px;
  border: 1px solid var(--c-rule);
  background: var(--c-white);
  color: var(--c-strong);
  font-size: 13px; font-weight: 700;
  transition: all 0.2s;
}
.gp-cart-badge:hover { border-color: var(--c-strong); background: rgba(109,76,91,0.06); }
.gp-cart-badge-count {
  display: inline-flex; align-items: center; justify-content: center;
  min-width: 20px; height: 20px; padding: 0 6px;
  border-radius: 999px;
  background: var(--c-strong);
  color: #fff;
  font-size: 10px; font-weight: 700;
  line-height: 1;
}

/* Page layout */
.gp-page {
  flex: 1;
  padding: 48px var(--pad-x) 64px;
  max-width: 1120px;
  margin: 0 auto;
  width: 100%;
}

.gp-page-head {
  display: flex; align-items: flex-end; justify-content: space-between; gap: 16px;
  margin-bottom: 36px;
}

.gp-page-title {
  font-family: var(--font-display);
  font-size: clamp(32px, 4vw, 48px); font-weight: 600;
  color: var(--c-text); line-height: 0.95;
}
.gp-page-count {
  font-size: 14px; font-weight: 500; color: var(--c-muted);
  padding-bottom: 4px;
}

/* Empty state */
.gp-empty {
  text-align: center;
  padding: 80px 24px;
  border: 1px dashed rgba(109,76,91,0.18);
  border-radius: var(--r-card);
  background: var(--c-card);
}

.gp-empty-icon {
  display: inline-flex; align-items: center; justify-content: center;
  width: 80px; height: 80px; border-radius: 50%;
  background: rgba(109,76,91,0.08);
  color: var(--c-strong);
  margin-bottom: 24px;
}

.gp-empty h2 {
  font-family: var(--font-display); font-size: 32px; font-weight: 600;
  color: var(--c-text); margin-bottom: 10px;
}

.gp-empty p {
  color: var(--c-accent); font-size: 14px; line-height: 1.7;
  max-width: 420px; margin: 0 auto 28px;
}

.gp-empty-btn {
  display: inline-flex; align-items: center; gap: 8px;
  height: 46px; padding: 0 28px; border-radius: 999px; border: none;
  background: var(--c-strong); color: #fff;
  font-size: 14px; font-weight: 700;
  cursor: pointer;
  box-shadow: 0 2px 8px rgba(109,76,91,0.18);
  transition: all 0.2s var(--ease-out-expo);
}
.gp-empty-btn:hover { background: #5a3d4a; transform: translateY(-2px); box-shadow: 0 6px 20px rgba(109,76,91,0.20); }

/* Cart items */
.gp-cart-items {
  display: grid; gap: 16px;
}

.gp-cart-item {
  display: grid;
  grid-template-columns: 140px 1fr auto;
  gap: 20px;
  align-items: start;
  background: var(--c-card);
  border: 1px solid var(--c-rule);
  border-radius: var(--r-card);
  padding: 16px;
  box-shadow: var(--sh-card);
  transition: all 0.3s var(--ease-out-expo);
}
.gp-cart-item:hover { box-shadow: var(--sh-panel); }

.gp-cart-item-img {
  width: 140px; height: 100px;
  border-radius: calc(var(--r-card) - 2px);
  overflow: hidden;
  background: linear-gradient(160deg, #ede0d0, #ddcebb);
  flex-shrink: 0;
}
.gp-cart-item-img img {
  width: 100%; height: 100%; object-fit: cover;
}

.gp-cart-item-body {
  display: flex; flex-direction: column; gap: 4px;
  min-width: 0;
}

.gp-cart-item-cat {
  font-size: 10px; font-weight: 700; letter-spacing: 0.10em; text-transform: uppercase;
  color: var(--c-muted);
}

.gp-cart-item-name {
  font-family: var(--font-display);
  font-size: 20px; font-weight: 600;
  color: var(--c-text);
  line-height: 1.1;
}

.gp-cart-item-supplier {
  font-size: 12px; font-weight: 500; color: var(--c-accent);
  display: flex; align-items: center; gap: 4px;
}

.gp-cart-item-details {
  display: flex; flex-wrap: wrap; gap: 12px;
  margin-top: 6px;
  font-size: 12px; color: var(--c-muted);
}
.gp-cart-item-details span {
  display: inline-flex; align-items: center; gap: 4px;
}

.gp-cart-item-right {
  display: flex; flex-direction: column; align-items: flex-end;
  justify-content: space-between; gap: 12px;
  flex-shrink: 0;
}

.gp-cart-item-price {
  font-family: var(--font-display);
  font-size: 24px; font-weight: 600;
  color: var(--c-strong);
  line-height: 1;
  white-space: nowrap;
}

.gp-cart-remove {
  display: inline-flex; align-items: center; gap: 4px;
  padding: 6px 12px; border-radius: 999px; border: 1px solid var(--c-rule);
  background: var(--c-white);
  color: var(--c-danger);
  font-size: 11px; font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}
.gp-cart-remove:hover { background: var(--c-danger); color: #fff; border-color: var(--c-danger); }

/* Cart footer / total */
.gp-cart-footer {
  display: flex; align-items: center; justify-content: space-between; gap: 20px;
  margin-top: 28px;
  padding: 20px 24px;
  background: var(--c-card);
  border: 1px solid var(--c-rule);
  border-radius: var(--r-card);
  box-shadow: var(--sh-card);
}

.gp-cart-total-label {
  font-size: 14px; color: var(--c-accent); font-weight: 500;
}

.gp-cart-total-amount {
  font-family: var(--font-display);
  font-size: 32px; font-weight: 600;
  color: var(--c-strong);
  line-height: 1;
}

.gp-cart-actions {
  display: flex; gap: 10px;
}

.gp-btn-outline {
  display: inline-flex; align-items: center; gap: 6px;
  height: 44px; padding: 0 22px; border-radius: 999px;
  border: 1px solid var(--c-rule);
  background: var(--c-white);
  color: var(--c-strong);
  font-size: 13px; font-weight: 700;
  cursor: pointer; text-decoration: none;
  transition: all 0.2s var(--ease-out-expo);
}
.gp-btn-outline:hover { border-color: var(--c-strong); background: rgba(109,76,91,0.06); }

.gp-btn-primary {
  display: inline-flex; align-items: center; gap: 6px;
  height: 44px; padding: 0 24px; border-radius: 999px; border: none;
  background: var(--c-strong); color: #fff;
  font-size: 13px; font-weight: 700;
  cursor: pointer; text-decoration: none;
  box-shadow: 0 2px 8px rgba(109,76,91,0.18);
  transition: all 0.2s var(--ease-out-expo);
}
.gp-btn-primary:hover { background: #5a3d4a; transform: translateY(-1px); box-shadow: 0 6px 20px rgba(109,76,91,0.20); }

/* Footer */
.gp-footer {
  padding: 24px var(--pad-x);
  border-top: 1px solid var(--c-rule);
  display: flex; align-items: center; justify-content: space-between; gap: 16px;
  font-size: 12px; color: var(--c-muted);
}

/* Responsive */
@media (max-width: 768px) {
  .gp-cart-item {
    grid-template-columns: 1fr;
    gap: 14px;
  }
  .gp-cart-item-img {
    width: 100%; height: 180px;
  }
  .gp-cart-item-right {
    flex-direction: row;
    align-items: center;
    justify-content: space-between;
    width: 100%;
  }
  .gp-cart-footer {
    flex-direction: column;
    text-align: center;
  }
  .gp-header-nav a { display: none; }
}

@media (max-width: 480px) {
  :root { --pad-x: 16px; }
  .gp-page { padding: 32px 16px 48px; }
  .gp-brand { font-size: 15px; }
  .gp-brand-mark { width: 34px; height: 34px; font-size: 12px; }
}
</style>
</head>
<body>

<div class="gp-texture" aria-hidden="true"></div>

<!-- Header -->
<header class="gp-header">
  <a class="gp-brand" href="<?= URLROOT ?>/main/index">
    <span class="gp-brand-mark">G</span>
    <span>Golden Promise</span>
  </a>
  <nav class="gp-header-nav" aria-label="Main navigation">
    <a href="<?= URLROOT ?>/main/index">Home</a>
    <a href="<?= URLROOT ?>/customerServices/service">Services</a>
    <a href="<?= URLROOT ?>/main/package">Packages</a>
  </nav>
  <div class="gp-header-actions">
    <a class="gp-cart-badge" href="<?= URLROOT ?>/cart" aria-label="Cart">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
      <?php if ($cartCount > 0): ?>
      <span class="gp-cart-badge-count"><?= $cartCount ?></span>
      <?php endif; ?>
    </a>
    <a class="gp-header-cta" href="<?= $authNavUrl ?>"><?= $authNavLabel ?></a>
  </div>
</header>

<!-- Main -->
<main class="gp-page">
  <div class="gp-page-head">
    <h1 class="gp-page-title">My Cart</h1>
    <span class="gp-page-count"><?= $cartCount ?> item<?= $cartCount === 1 ? '' : 's' ?></span>
  </div>

  <?php if (empty($items)): ?>
    <!-- Empty state -->
    <div class="gp-empty">
      <div class="gp-empty-icon">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
      </div>
      <h2>Your cart is empty</h2>
      <p>Browse our curated collection of wedding services and add the ones you love.</p>
      <a class="gp-empty-btn" href="<?= URLROOT ?>/customerServices/service">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
        Browse services
      </a>
    </div>
  <?php else: ?>
    <!-- Cart items -->
    <div class="gp-cart-items">
      <?php foreach ($items as $item):
        $itemId = (int)($item['cart_item_id'] ?? 0);
        $name = $item['service_name'] ?? 'Service';
        $supplier = $item['supplier_name'] ?? 'Supplier';
        $category = $item['category_name'] ?? 'Service';
        $img = trim($item['thumbnail_url'] ?? '');
        $displayPrice = (float)($item['cart_price'] ?? $item['price_min'] ?? $item['price_max'] ?? 0);
        $selectedDate = $item['selected_date'] ?? '';
        $startTime = $item['start_time'] ?? '';
        $itemType = $item['item_type'] ?? 'service';
        if ($itemType === 'package' && !empty($item['package_slug'])) {
          $detailUrl = URLROOT . '/customerServices/packageDetail/' . $h($item['package_slug']);
        } else {
          $detailUrl = URLROOT . '/customerServices/detail/' . (int)($item['item_id'] ?? 0) . ($selectedDate ? '?date=' . $h($selectedDate) : '');
        }
      ?>
      <div class="gp-cart-item">
        <a class="gp-cart-item-img" href="<?= $h($detailUrl) ?>">
          <?php if ($img): ?>
            <img src="<?= $h($img) ?>" alt="<?= $h($name) ?>" loading="lazy">
          <?php else: ?>
            <div style="width:100%;height:100%;display:grid;place-items:center;color:var(--c-muted);">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
            </div>
          <?php endif; ?>
        </a>
        <div class="gp-cart-item-body">
          <span class="gp-cart-item-cat"><?= $h($category) ?></span>
          <h3 class="gp-cart-item-name">
            <a href="<?= $h($detailUrl) ?>"><?= $h($name) ?></a>
          </h3>
          <span class="gp-cart-item-supplier">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="opacity:0.5;"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            <?= $h($supplier) ?>
          </span>
          <?php if ($selectedDate || $startTime): ?>
          <div class="gp-cart-item-details">
            <?php if ($selectedDate): ?>
            <span>
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
              <?= $h(date('M j, Y', strtotime($selectedDate))) ?>
            </span>
            <?php endif; ?>
            <?php if ($startTime): ?>
            <span>
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
              <?= $h(date('g:i A', strtotime($startTime))) ?>
            </span>
            <?php endif; ?>
          </div>
          <?php endif; ?>
        </div>
        <div class="gp-cart-item-right">
          <span class="gp-cart-item-price"><?= $money($displayPrice) ?></span>
          <form method="POST" action="<?= URLROOT ?>/cart/remove" onsubmit="return confirm('Remove this item from your cart?');">
            <input type="hidden" name="cart_item_id" value="<?= $itemId ?>">
            <button class="gp-cart-remove" type="submit">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
              Remove
            </button>
          </form>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Cart footer -->
    <div class="gp-cart-footer">
      <div>
        <div class="gp-cart-total-label">Estimated total</div>
        <div class="gp-cart-total-amount"><?= $money($total) ?></div>
      </div>
      <div class="gp-cart-actions">
        <a class="gp-btn-outline" href="<?= URLROOT ?>/customerServices/service">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
          Add more
        </a>
      </div>
    </div>
  <?php endif; ?>
</main>

<footer class="gp-footer">
  <span>&copy; <?= date('Y') ?> Golden Promise</span>
  <span>Your curated wedding service collection</span>
</footer>

<script>
document.addEventListener('DOMContentLoaded', () => {
  if (typeof lucide !== 'undefined') lucide.createIcons();
});
</script>
</body>
</html>
