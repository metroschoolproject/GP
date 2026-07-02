<?php
$gpNavActive = $gpNavActive ?? '';
$gpNavOverlay = !empty($gpNavOverlay);
$isLoggedIn = $isLoggedIn ?? !empty($_SESSION['session_uid']);
$cartCount = (int)($cartCount ?? 0);
$gpShowFloatingCart = $isLoggedIn && ($gpShowFloatingCart ?? true);

// Fetch wishlist count for logged-in user if not already provided
if ($isLoggedIn && !isset($wishlistCount)) {
    try {
        require_once APPROOT . '/models/WishlistModel.php';
        $wlModel = new WishlistModel();
        $wishlistCount = $wlModel->getWishlistCount((int)$_SESSION['session_uid']);
    } catch (\Throwable $e) {
        $wishlistCount = 0;
    }
}
$wishlistCount = (int)($wishlistCount ?? 0);
$gpServiceCategories = $serviceCategories ?? [
    ['name' => 'Planning', 'slug' => 'planning'],
    ['name' => 'Florals', 'slug' => 'florals'],
    ['name' => 'Photography', 'slug' => 'photography'],
    ['name' => 'Catering', 'slug' => 'catering'],
];
$gpNavEsc = function ($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
};
?>
<style>
.site-header{position:fixed;inset:0 0 auto;z-index:1000;padding:0;pointer-events:none;font-family:'Playfair Display',Georgia,serif}
.navbar{position:fixed;top:0;left:0;z-index:1000;display:flex;align-items:center;justify-content:space-between;gap:12px;width:100%;min-height:58px;padding:9px 18px;border-radius:0 0 6px 6px;border-bottom:0;background:transparent;box-shadow:none;pointer-events:auto}
.nav-left-spacer{width:82px;height:36px;flex:0 0 82px}
.nav-center-logo{position:absolute;left:24px;top:50%;z-index:2;display:grid;width:68px;height:68px;place-items:center;overflow:hidden;border-radius:50%;transform:translateY(-50%)}
.nav-center-logo img{width:100%;height:100%;object-fit:cover}
.nav-links{position:absolute;left:50%;top:50%;display:flex;align-items:center;gap:7px;padding:5px;border-radius:8px;background:rgba(0,0,0,.52);transform:translate(-50%,-50%);color:#fff4e6;font-size:14px;font-weight:700;font-family:'Playfair Display',Georgia,serif;box-shadow:inset 0 1px 0 rgba(252,248,245,.14);-webkit-backdrop-filter:blur(12px);backdrop-filter:blur(12px)}
.nav-runner{position:absolute;left:0;top:4px;z-index:0;width:0;height:calc(100% - 8px);border-radius:7px;background:rgba(252,248,245,.92);opacity:0;transform:translateX(4px);transition:transform .34s cubic-bezier(.22,1,.36,1),width .34s cubic-bezier(.22,1,.36,1),opacity .18s ease;pointer-events:none}
.nav-links a{position:relative;z-index:1;border:0;border-radius:7px;background:transparent;padding:7px 18px;color:#fff4e6;font:inherit;text-decoration:none;white-space:nowrap;cursor:pointer;transition:all .2s ease}
.nav-links a:hover,.nav-links a.active{background:transparent;color:#3f2f24}
.nav-actions{display:flex;align-items:center;gap:8px;margin-left:auto;font-family:'Playfair Display',Georgia,serif}
.nav-partner,.nav-login{display:inline-flex;align-items:center;justify-content:center;min-height:31px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;transition:all .3s ease;letter-spacing:0.04em}
.nav-partner{padding:8px 16px;background:#6d4c5B;color:#fff8ef;box-shadow:0 10px 25px rgba(63,36,26,.24)}
.nav-partner:hover{transform:translateY(-2px);background:#4a2d22;color:#fff8ef}
.nav-login{padding:8px 16px;background:#fff8ef;color:#3f2f24}
.nav-login:hover{background:#f3d9a4;color:#3f2f24}
.home-profile-dropdown{position:relative}
.home-profile-btn{display:grid;place-items:center;width:44px;height:44px;padding:4px;border-radius:9px;border:0;background:transparent;cursor:pointer;color:#fff4e6;font-family:'Playfair Display',Georgia,serif;transition:all .2s}
.home-profile-btn:hover{background:rgba(252,248,245,.22)}
.home-profile-btn[aria-expanded="true"]{background:rgba(252,248,245,.16)}
.home-profile-avatar{display:grid;place-items:center;width:36px;height:36px;border-radius:50%;background:#d8b46a;color:#3f2f24;font-size:14px;font-weight:800;letter-spacing:.5px;overflow:hidden;box-shadow:0 0 0 0 rgba(216,180,106,0);transition:box-shadow .18s ease}
.home-profile-avatar img{width:100%;height:100%;object-fit:cover}
.home-profile-btn[aria-expanded="true"] .home-profile-avatar{box-shadow:0 0 0 2px #fff8ef,0 0 0 4px rgba(216,180,106,.76)}
.nav-actions .gp-customer-notification{z-index:1100}
.nav-actions .gp-customer-notification #dashboardNotificationBtn{width:40px;height:40px;border-radius:9px;border-color:rgba(255,248,239,.22);background:#fff8ef;color:#3f2f24;box-shadow:none}
.nav-actions .gp-customer-notification #dashboardNotificationBtn svg{width:18px;height:18px}
.nav-actions .gp-customer-notification .dashboard-notification-panel{right:0;top:calc(100% + 9px)}
.nav-actions .gp-customer-notification .dashboard-notification-title{color:#fcf8f5 !important}
.home-profile-menu{position:absolute;top:calc(100% + 10px);right:0;z-index:1100;width:min(292px,calc(100vw - 24px));padding:14px;border-radius:14px;border:1px solid rgba(107,68,89,.12);background:#fcf8f5;box-shadow:0 18px 48px rgba(43,27,36,.18);opacity:0;visibility:hidden;transform:translateY(-4px);transition:all .15s ease;color:#2b1b24;font-family:'Poppins',system-ui,-apple-system,sans-serif}
.home-profile-btn[aria-expanded="true"]+.home-profile-menu{opacity:1;visibility:visible;transform:translateY(0)}
.home-profile-menu-top{display:none}
.home-profile-email{max-width:100%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:13px;font-weight:700;color:#2b1b24}
.home-profile-close{position:absolute;right:0;top:0;display:grid;place-items:center;width:26px;height:26px;border:0;border-radius:6px;background:transparent;color:#4f454b;cursor:pointer;transition:background .15s ease,color .15s ease}
.home-profile-close:hover{background:rgba(43,27,36,.08);color:#2b1b24}
.home-profile-hero{display:grid;grid-template-columns:48px minmax(0,1fr);align-items:start;gap:7px 11px;padding:5px 2px 8px;text-align:left}
.home-profile-photo{display:grid;place-items:center;width:46px;height:46px;border-radius:50%;background:#d8b46a;color:#3f2f24;font-size:17px;font-weight:800;overflow:hidden}
.home-profile-photo img{width:100%;height:100%;object-fit:cover}
.home-profile-profile-copy{min-width:0}
.home-profile-greeting{display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:14px;font-weight:700;color:#2b1b24;line-height:1.2}
.home-profile-inline-email{display:block;margin-top:4px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:11.5px;font-weight:500;color:#7d6f76;line-height:1.2}
.home-profile-edit{display:inline-flex;grid-column:2;align-items:center;justify-content:flex-start;min-height:18px;margin-top:-8px;padding:0;border:0;border-radius:5px;color:#6D4C5B;background:transparent;font-size:11px;font-weight:600;text-decoration:underline;text-underline-offset:2px;transition:all .15s ease}
.home-profile-edit:hover{background:rgba(154,104,127,.09);color:#3f241a;border-color:#6D4C5B}
.home-profile-activity{margin-top:8px;padding:8px;border-radius:12px;background:#f4eee9;border:1px solid rgba(107,68,89,.08)}
.home-profile-activity-title{display:flex;align-items:center;justify-content:space-between;padding:4px 7px 7px;color:#2b1b24;font-size:12.5px;font-weight:800}
.home-profile-menu-item{display:flex;align-items:center;gap:12px;padding:9px 8px;border-radius:9px;color:#4f454b;font-size:12px;font-weight:650;text-decoration:none;transition:all .15s}
.home-profile-menu-item svg{width:17px;height:17px;color:#6D4C5B}
.home-profile-menu-item:hover{background:rgba(154,104,127,.08);color:#3f241a}
.home-profile-menu-item--danger{margin-top:8px;color:#b94a48}
.home-profile-menu-item--danger svg{color:#b94a48}
.home-profile-menu-item--danger:hover{background:rgba(185,75,75,.08);color:#8f2e2d}
.mobile-menu-btn{display:none;align-items:center;justify-content:center;min-height:40px;padding:0 14px;border:1px solid transparent;border-radius:8px;background:rgba(252,248,245,.10);color:#fff4e6;cursor:pointer;font-family:'Playfair Display',Georgia,serif;font-size:13px;font-weight:800;box-shadow:0 6px 18px rgba(92,67,48,.14)}
.mobile-menu{position:fixed;top:74px;left:50%;z-index:999;display:none;width:min(calc(100% - 24px),1152px);padding:10px;border:1px solid transparent;border-radius:10px;background:#765a46;box-shadow:0 18px 36px rgba(92,67,48,.18);transform:translateX(-50%);pointer-events:auto}
.mobile-menu.open{display:grid}
.mobile-menu a{padding:12px 14px;border-radius:8px;color:#fff4e6;font-weight:800;text-decoration:none}
.mobile-menu a:hover{background:rgba(216,180,106,.16);color:#f3d9a4}
.mobile-menu .mobile-partner{background:#3f241a;color:#fff8ef;font-weight:600;font-size:13px}
.mobile-menu .mobile-partner:hover{background:#4a2d22;color:#fff8ef}
.mobile-menu .mobile-login{background:#fff8ef;color:#3f2f24;font-weight:600;font-size:13px}
.mobile-menu .mobile-login:hover{background:#f3d9a4;color:#3f2f24}
.gp-customer-nav-spacer{height:78px}
.gp-floating-cart{position:fixed;right:clamp(20px,5vw,60px);bottom:clamp(24px,6vw,60px);z-index:900;width:54px;height:54px;display:grid;place-items:center;border:1px solid rgba(234,216,199,.86);border-radius:16px;background:#fff8ef;color:#6D4C5B;text-decoration:none;box-shadow:0 12px 36px rgba(74,52,47,.15);transition:transform .3s cubic-bezier(.34,1.56,.64,1),box-shadow .3s ease}
.gp-floating-cart:hover{transform:translateY(-3px);background:#6D4C5B;color:#fcf8f5;border-color:#6D4C5B;box-shadow:0 18px 44px rgba(74,52,47,.18)}
.gp-floating-cart-count{position:absolute;right:-6px;top:-7px;display:inline-flex;align-items:center;justify-content:center;min-width:20px;height:20px;padding:0 6px;border:2px solid #fff8ef;border-radius:999px;background:#6D4C5B;color:#fff8ef;font-family:Arial,sans-serif;font-size:10px;font-weight:800;line-height:1}
.gp-floating-cart-count:empty{display:none}
footer.gp-footer{display:none!important}
.gp-shared-footer{position:relative;z-index:1;overflow:hidden;width:100%;margin-top:0;padding:clamp(58px,5vw,74px) 24px 12px 0;background:#2A1710;color:#fcf8f5;font-family:'Playfair Display',Georgia,serif}
.gp-shared-footer::before{content:"";position:absolute;top:-1px;left:0;right:0;height:42px;background:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='260' height='42' viewBox='0 0 260 42'%3E%3Cpath fill='%23ead8c8' d='M0 0h260v18C218 42 174-2 130 18 86 38 42-2 0 18V0Z'/%3E%3C/svg%3E") repeat-x top left/260px 42px;pointer-events:none}
.gp-shared-footer-inner{width:min(100%,1180px);display:grid;grid-template-columns:minmax(260px,.82fr) minmax(460px,1fr);gap:clamp(34px,7vw,110px);align-content:center;align-items:center;justify-items:start;text-align:left;margin:0 auto}
.gp-shared-footer-left{display:grid;justify-items:start}
.gp-shared-footer-nav{display:flex;align-items:center;justify-content:flex-start;gap:clamp(28px,7vw,92px);margin-top:0;font-family:'Playfair Display',Georgia,serif}
.gp-shared-footer-nav a{color:rgba(252,248,245,.84);font-size:12px;font-weight:700;letter-spacing:.22em;text-transform:uppercase;text-decoration:none;transition:color 220ms ease,opacity 220ms ease}
.gp-shared-footer-nav a:hover{color:#fcf8f5;opacity:1}
.gp-shared-footer-contact-card{text-align:left;margin-top:clamp(10px,1.4vw,16px)}
.gp-shared-footer-contact-card h2{margin:0;color:#fcf8f5;font-size:clamp(1.7rem,4vw,3rem);font-weight:700;line-height:1.15}
.gp-shared-footer-contact-card p{margin:4px 0 0;color:#aaa;font-family:'Poppins',system-ui,sans-serif;font-size:13px}
.gp-shared-footer-contact-card h2,.gp-shared-footer-contact-card p{transition:opacity .35s ease,transform .35s ease}
.gp-shared-footer-contact-card .text-changing{opacity:0;transform:translateY(12px)}
.gp-shared-footer-icons{display:flex;align-items:center;justify-content:flex-start;gap:12px;margin-top:12px}
.gp-shared-social{display:grid;place-items:center;width:42px;height:42px;border:1px solid rgba(252,248,245,.16);border-radius:10px;background:rgba(252,248,245,.05);color:#999;font-family:'Poppins',system-ui,sans-serif;font-size:12px;font-weight:700;text-decoration:none;transition:background .3s ease,color .3s ease,border-color .3s ease}
.gp-shared-social svg{width:21px;height:21px}
.gp-shared-social:hover,.gp-shared-social.active{background:rgba(252,248,245,.16);border-color:rgba(252,248,245,.4);color:#fcf8f5}
.gp-shared-footer-right-space{justify-self:start;display:grid;grid-template-columns:minmax(130px,.72fr) minmax(220px,1fr);gap:clamp(28px,5vw,58px);padding-left:0}
.gp-shared-footer-col+.gp-shared-footer-col{padding-left:clamp(24px,4vw,46px);border-left:1px solid rgba(252,248,245,.16)}
.gp-shared-footer-title{margin:0 0 18px;color:#fcf8f5;font-size:15px;font-weight:700;letter-spacing:.06em;text-transform:uppercase}
.gp-shared-footer-links,.gp-shared-footer-contact-list{display:grid;gap:11px;margin:0;padding:0;list-style:none}
.gp-shared-footer-links a{color:rgba(252,248,245,.78);font-family:'Poppins',system-ui,sans-serif;font-size:13px;text-decoration:none;transition:color .2s ease,transform .2s ease}
.gp-shared-footer-links a:hover{color:#fcf8f5;transform:translateX(2px)}
.gp-shared-footer-contact-list li{display:grid;grid-template-columns:22px 1fr;align-items:center;gap:12px;color:rgba(252,248,245,.78);font-family:'Poppins',system-ui,sans-serif;font-size:13px;line-height:1.55}
.gp-shared-footer-contact-list svg{width:18px;height:18px;color:rgba(252,248,245,.86);stroke-width:1.7}
.gp-shared-footer-copy{grid-column:1/-1;margin:clamp(10px,1.4vw,16px) 0 0;color:rgba(252,248,245,.78);font-family:'Poppins',system-ui,sans-serif;font-size:11px;letter-spacing:.04em}
@media(max-width:900px){.gp-shared-footer{padding:56px 18px 16px}.gp-shared-footer-inner,.gp-shared-footer-right-space{grid-template-columns:1fr}.gp-shared-footer-nav{gap:18px;flex-wrap:wrap}.gp-shared-footer-col+.gp-shared-footer-col{padding-left:0;border-left:0}.gp-shared-footer-copy{grid-column:auto}}
@media(max-width:900px){.nav-links,.nav-actions{display:none}.mobile-menu-btn{display:inline-flex}}
@media(max-width:700px){.navbar{min-height:59px;padding:10px 12px}.nav-left-spacer{width:70px;flex-basis:70px}.nav-center-logo{left:12px;width:64px;height:64px}.mobile-menu{top:68px}.gp-customer-nav-spacer{height:72px}.gp-floating-cart{width:44px;height:44px;border-radius:12px;bottom:80px}.gp-floating-cart svg{width:18px;height:18px}}
</style>

<header class="site-header">
  <nav class="navbar" aria-label="Main navigation">
    <div class="nav-left-spacer" aria-hidden="true"></div>
    <a class="nav-center-logo" href="<?= URLROOT ?>/main/index#top" aria-label="Golden Promise home">
      <img src="<?= URLROOT ?>/public/images/home/gp_logo.png" alt="Golden Promise logo">
    </a>
    <div class="nav-links">
      <span class="nav-runner" aria-hidden="true"></span>
      <a class="<?= $gpNavActive === 'home' ? 'active' : '' ?>" href="<?= URLROOT ?>/main/index#top">Home</a>
      <a class="<?= $gpNavActive === 'packages' ? 'active' : '' ?>" href="<?= URLROOT ?>/customerServices/packages">Packages</a>
      <a class="<?= $gpNavActive === 'services' ? 'active' : '' ?>" href="<?= URLROOT ?>/customerServices/service">Services</a>
    </div>
    <div class="nav-actions">
      <a class="nav-partner" href="<?= URLROOT ?>/users/auth?type=supplier">Be a Partner</a>
      <?php if ($isLoggedIn): ?>
        <?php if (defined('APPROOT') && file_exists(APPROOT . '/views/dashboardLayout/customerNotification.php')) require APPROOT . '/views/dashboardLayout/customerNotification.php'; ?>
        <?php
          $gpProfileName = trim((string)($_SESSION['session_name'] ?? 'User'));
          $gpProfileEmail = trim((string)($_SESSION['session_email'] ?? ''));
          $gpProfileAvatar = trim((string)($_SESSION['session_avatar'] ?? ''));
          $gpProfileInitial = strtoupper(substr($gpProfileName ?: 'U', 0, 1));
        ?>
        <div class="home-profile-dropdown">
          <button class="home-profile-btn" type="button" aria-expanded="false">
            <span class="home-profile-avatar"><?php if ($gpProfileAvatar !== ''): ?><img src="<?= $gpNavEsc($gpProfileAvatar) ?>" alt=""><?php else: ?><?= $gpNavEsc($gpProfileInitial) ?><?php endif; ?></span>
          </button>
          <div class="home-profile-menu" aria-hidden="true">
            <div class="home-profile-menu-top">
              <span class="home-profile-email"><?= $gpNavEsc($gpProfileEmail !== '' ? $gpProfileEmail : $gpProfileName) ?></span>
              <button class="home-profile-close" type="button" aria-label="Close profile menu" data-profile-close>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
              </button>
            </div>
            <div class="home-profile-hero">
              <span class="home-profile-photo"><?php if ($gpProfileAvatar !== ''): ?><img src="<?= $gpNavEsc($gpProfileAvatar) ?>" alt=""><?php else: ?><?= $gpNavEsc($gpProfileInitial) ?><?php endif; ?></span>
              <span class="home-profile-profile-copy">
                <span class="home-profile-greeting"><?= $gpNavEsc($gpProfileName) ?></span>
                <span class="home-profile-inline-email"><?= $gpNavEsc($gpProfileEmail !== '' ? $gpProfileEmail : $gpProfileName) ?></span>
              </span>
              <a class="home-profile-edit" href="<?= URLROOT ?>/main/profile">Edit profile</a>
            </div>
            <div class="home-profile-activity">
              <div class="home-profile-activity-title">Your activity</div>
              <a class="home-profile-menu-item" href="<?= URLROOT ?>/booking/myBookings"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 6h13M8 12h13M8 18h13"/><path d="M3 6h.01M3 12h.01M3 18h.01"/></svg>Bookings</a>
              <a class="home-profile-menu-item" href="<?= URLROOT ?>/main/wishlist"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78L12 21.23l7.78-7.78a5.5 5.5 0 0 0 1.06-8.84z"/></svg>Wishlist<?php if ($wishlistCount > 0): ?> <span style="display:inline-flex;align-items:center;justify-content:center;min-width:20px;height:20px;padding:0 6px;border-radius:999px;background:#6d4c5b;color:white;font-size:11px;font-weight:700;margin-left:6px"><?= $wishlistCount ?></span><?php endif; ?></a>
              <a class="home-profile-menu-item" href="<?= URLROOT ?>/review/my"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>My reviews</a>
            </div>
            <a class="home-profile-menu-item home-profile-menu-item--danger" href="<?= URLROOT ?>/users/logout"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>Log out</a>
          </div>
        </div>
      <?php else: ?>
        <a class="nav-login" href="<?= URLROOT ?>/users/auth">Log In</a>
      <?php endif; ?>
    </div>
    <button class="mobile-menu-btn" type="button" aria-label="Open navigation" aria-expanded="false" data-customer-mobile-btn>Menu</button>
  </nav>
  <div class="mobile-menu" data-customer-mobile-menu>
    <a href="<?= URLROOT ?>/main/index#top">Home</a>
    <a href="<?= URLROOT ?>/customerServices/service">Our Service</a>
    <a href="<?= URLROOT ?>/customerServices/packages">Packages</a>
    <a class="mobile-partner" href="<?= URLROOT ?>/users/auth?type=supplier">Be a Partner</a>
    <?php if ($isLoggedIn): ?>
      <a href="<?= URLROOT ?>/booking/myBookings">My Bookings</a>
      <a href="<?= URLROOT ?>/review/my">My Reviews</a>
      <a href="<?= URLROOT ?>/users/logout">Logout</a>
    <?php else: ?>
      <a class="mobile-login" href="<?= URLROOT ?>/users/auth">Log In</a>
    <?php endif; ?>
  </div>
</header>
<?php if (!$gpNavOverlay): ?><div class="gp-customer-nav-spacer" aria-hidden="true"></div><?php endif; ?>
<?php if ($gpShowFloatingCart): ?>
<a class="gp-floating-cart" href="<?= URLROOT ?>/cart" aria-label="Open cart<?= $cartCount > 0 ? ' with ' . $cartCount . ' selected service' . ($cartCount === 1 ? '' : 's') : '' ?>">
  <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
  <span class="gp-floating-cart-count" data-cart-count-badge><?= $cartCount > 0 ? ($cartCount > 99 ? '99+' : $cartCount) : '' ?></span>
</a>
<?php endif; ?>
<script>
(function(){
  document.querySelectorAll('.nav-links').forEach(nav=>{
    const runner=nav.querySelector('.nav-runner');
    if(!runner) return;
    const links=Array.from(nav.querySelectorAll('a'));
    const active=()=>nav.querySelector('a.active');
    const moveTo=link=>{
      if(!link){
        runner.style.opacity='0';
        runner.style.width='0';
        return;
      }
      runner.style.width=link.offsetWidth+'px';
      runner.style.transform='translateX('+link.offsetLeft+'px)';
      runner.style.opacity='1';
    };
    requestAnimationFrame(()=>moveTo(active()));
    links.forEach(link=>{
      link.addEventListener('mouseenter',()=>moveTo(link));
      link.addEventListener('focus',()=>moveTo(link));
      link.addEventListener('click',()=>moveTo(link));
    });
    nav.addEventListener('mouseleave',()=>moveTo(active()));
    window.addEventListener('resize',()=>moveTo(active()));
  });

  const mobileBtn=document.querySelector('[data-customer-mobile-btn]');
  const mobileMenu=document.querySelector('[data-customer-mobile-menu]');
  function closeDashboardNotification(){
    const btn=document.getElementById('dashboardNotificationBtn');
    const panel=document.getElementById('dashboardNotificationPanel');
    btn?.setAttribute('aria-expanded','false');
    panel?.classList.add('invisible','opacity-0','scale-95');
  }
  mobileBtn?.addEventListener('click',event=>{
    event.stopPropagation();
    const isOpen=mobileMenu.classList.toggle('open');
    mobileBtn.setAttribute('aria-expanded',String(isOpen));
  });

  document.addEventListener('click',event=>{
    const profileClose=event.target.closest('[data-profile-close]');
    if(profileClose){
      event.stopPropagation();
      profileClose.closest('.home-profile-dropdown')?.querySelector('.home-profile-btn')?.setAttribute('aria-expanded','false');
      return;
    }
    const profileBtn=event.target.closest('.home-profile-btn');
    if(profileBtn){
      const expanded=profileBtn.getAttribute('aria-expanded')==='true';
      closeDashboardNotification();
      document.querySelectorAll('.home-profile-btn').forEach(btn=>btn.setAttribute('aria-expanded','false'));
      profileBtn.setAttribute('aria-expanded',String(!expanded));
      mobileMenu?.classList.remove('open');
      mobileBtn?.setAttribute('aria-expanded','false');
      return;
    }
    document.querySelectorAll('.home-profile-btn').forEach(btn=>btn.setAttribute('aria-expanded','false'));
    mobileMenu?.classList.remove('open');
    mobileBtn?.setAttribute('aria-expanded','false');
  });

  mobileMenu?.querySelectorAll('a').forEach(link=>link.addEventListener('click',()=>{
    mobileMenu.classList.remove('open');
    mobileBtn?.setAttribute('aria-expanded','false');
  }));

  const cartBadge=document.querySelector('[data-cart-count-badge]');
  if(cartBadge){
    fetch('<?= URLROOT ?>/cart/cartCount',{headers:{'Accept':'application/json'}})
      .then(response=>response.ok ? response.json() : null)
      .then(data=>{
        if(!data || typeof data.count === 'undefined') return;
        const count=parseInt(data.count,10) || 0;
        cartBadge.textContent=count > 0 ? (count > 99 ? '99+' : String(count)) : '';
        const cartLink=cartBadge.closest('.gp-floating-cart');
        if(cartLink){
          cartLink.setAttribute('aria-label', count > 0 ? 'Open cart with '+count+' selected service'+(count === 1 ? '' : 's') : 'Open cart');
        }
      })
      .catch(()=>{});
  }

  function mountGpSharedFooter(){
    if(document.querySelector('.gp-shared-footer')) return;
    const footer=document.createElement('footer');
    footer.className='gp-shared-footer';
    footer.setAttribute('aria-label','Website Footer');
    footer.innerHTML=`
      <div class="gp-shared-footer-inner">
        <div class="gp-shared-footer-left">
          <nav class="gp-shared-footer-nav" aria-label="Footer navigation">
            <a href="<?= URLROOT ?>/customerServices/service">Services</a>
            <a href="<?= URLROOT ?>/main/index#story">Our Story</a>
            <a href="<?= URLROOT ?>/main/index#reviews">Review</a>
          </nav>
          <div class="gp-shared-footer-contact-card">
            <h2 data-gp-shared-footer-text>GPromise Wedding</h2>
            <p data-gp-shared-footer-label>Facebook</p>
            <div class="gp-shared-footer-icons" aria-label="Social links">
              <a class="gp-shared-social active" href="#" aria-label="Facebook" data-text="GPromise Wedding" data-label="Facebook">
                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M14 8.5h2.2V5.2c-.4-.1-1.7-.2-3.1-.2-3.1 0-5.2 1.9-5.2 5.4v3H4.5V17h3.4v7h4.1v-7h3.3l.5-3.6H12v-2.6c0-1 .3-2.3 2-2.3Z"/></svg>
              </a>
              <a class="gp-shared-social" href="#" aria-label="Instagram" data-text="@gpromise_wedding" data-label="Instagram">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="4" y="4" width="16" height="16" rx="5"/><circle cx="12" cy="12" r="3.2"/><circle cx="16.8" cy="7.2" r=".8" fill="currentColor" stroke="none"/></svg>
              </a>
              <a class="gp-shared-social" href="<?= URLROOT ?>/main/index#top" aria-label="Website" data-text="GPromise.com" data-label="Website">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M3 12h18"/><path d="M12 3c2.3 2.5 3.5 5.5 3.5 9S14.3 18.5 12 21c-2.3-2.5-3.5-5.5-3.5-9S9.7 5.5 12 3Z"/></svg>
              </a>
            </div>
          </div>
        </div>

        <div class="gp-shared-footer-right-space" aria-label="Footer quick links and contact">
          <div class="gp-shared-footer-col">
            <h3 class="gp-shared-footer-title">Quick Links</h3>
            <ul class="gp-shared-footer-links">
              <li><a href="<?= URLROOT ?>/main/index#top">Home</a></li>
              <li><a href="<?= URLROOT ?>/customerServices/service">Services</a></li>
              <li><a href="<?= URLROOT ?>/customerServices/packages">Wedding Packages</a></li>
              <li><a href="<?= URLROOT ?>/users/auth?type=supplier">Suppliers</a></li>
              <li><a href="<?= URLROOT ?>/main/index#contact">Contact Us</a></li>
            </ul>
          </div>
          <div class="gp-shared-footer-col">
            <h3 class="gp-shared-footer-title">Contact</h3>
            <ul class="gp-shared-footer-contact-list">
              <li>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 21s7-4.9 7-11a7 7 0 1 0-14 0c0 6.1 7 11 7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg>
                <span>Yangon, Myanmar</span>
              </li>
              <li>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 6h16v12H4z"/><path d="m4 7 8 6 8-6"/></svg>
                <span>support@gpromise.com</span>
              </li>
              <li>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.9 19.9 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.9 19.9 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 2 .7 2.9a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.2-1.2a2 2 0 0 1 2.1-.5c.9.3 1.9.6 2.9.7a2 2 0 0 1 1.7 2Z"/></svg>
                <span>+95 XXX XXX XXXX</span>
              </li>
            </ul>
          </div>
        </div>
        <p class="gp-shared-footer-copy">&copy; <span data-gp-shared-footer-year></span> GPromise Wedding. All rights reserved.</p>
      </div>
    `;
    document.body.appendChild(footer);
    const year=footer.querySelector('[data-gp-shared-footer-year]');
    if(year) year.textContent=new Date().getFullYear();

    const footerButtons=footer.querySelectorAll('.gp-shared-social');
    const footerText=footer.querySelector('[data-gp-shared-footer-text]');
    const footerLabel=footer.querySelector('[data-gp-shared-footer-label]');
    footerButtons.forEach(btn=>{
      btn.addEventListener('mouseenter',()=>{
        footerButtons.forEach(item=>item.classList.remove('active'));
        btn.classList.add('active');
        if(!footerText || !footerLabel) return;
        footerText.classList.add('text-changing');
        footerLabel.classList.add('text-changing');
        setTimeout(()=>{
          footerText.textContent=btn.dataset.text || footerText.textContent;
          footerLabel.textContent=btn.dataset.label || footerLabel.textContent;
          footerText.classList.remove('text-changing');
          footerLabel.classList.remove('text-changing');
        },180);
      });
    });
  }

  if(document.readyState === 'loading'){
    document.addEventListener('DOMContentLoaded', mountGpSharedFooter);
  }else{
    mountGpSharedFooter();
  }
})();
</script>
