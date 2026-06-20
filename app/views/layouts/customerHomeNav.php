<?php
$gpNavActive = $gpNavActive ?? '';
$gpNavOverlay = !empty($gpNavOverlay);
$isLoggedIn = $isLoggedIn ?? !empty($_SESSION['session_uid']);
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
.navbar{position:fixed;top:0;left:0;z-index:1000;display:flex;align-items:center;justify-content:space-between;gap:16px;width:100%;min-height:64px;padding:12px 20px;border-radius:0 0 6px 6px;border-bottom:0;background:transparent;box-shadow:0 4px 30px rgba(0,0,0,.20);pointer-events:auto}
.nav-left-spacer{width:92px;height:40px;flex:0 0 92px}
.nav-center-logo{position:absolute;left:24px;top:50%;z-index:2;display:grid;width:76px;height:76px;place-items:center;overflow:hidden;border-radius:50%;transform:translateY(-50%)}
.nav-center-logo img{width:100%;height:100%;object-fit:cover}
.nav-links{position:absolute;left:50%;top:50%;display:flex;align-items:center;gap:8px;padding:5px;border-radius:10px;background:rgba(0,0,0,.52);transform:translate(-50%,-50%);color:#fff4e6;font-size:14px;font-weight:700;font-family:'Playfair Display',Georgia,serif;box-shadow:inset 0 1px 0 rgba(255,255,255,.14);-webkit-backdrop-filter:blur(12px);backdrop-filter:blur(12px)}
.nav-links a{border:0;border-radius:8px;background:transparent;padding:7px 18px;color:#fff4e6;font:inherit;text-decoration:none;white-space:nowrap;cursor:pointer;transition:all .2s ease}
.nav-links a:hover,.nav-links a.active{background:rgba(255,255,255,.92);color:#3f2f24}
.nav-actions{display:flex;align-items:center;gap:12px;margin-left:auto;font-family:'Playfair Display',Georgia,serif}
.nav-partner,.nav-login{display:inline-flex;align-items:center;justify-content:center;min-height:34px;border-radius:8px;font-size:13px;font-weight:800;text-decoration:none;transition:all .2s ease}
.nav-partner{padding:6px 14px;background:#3f241a;color:#fff8ef;box-shadow:0 10px 25px rgba(63,36,26,.24)}
.nav-partner:hover{transform:translateY(-1px);background:#4a2d22;color:#fff8ef}
.nav-login{padding:6px 12px;background:#fff8ef;color:#3f2f24}
.nav-login:hover{background:#f3d9a4;color:#3f2f24}
.home-profile-dropdown{position:relative}
.home-profile-btn{display:flex;align-items:center;gap:8px;padding:4px 12px 4px 4px;border-radius:8px;border:1px solid rgba(255,255,255,.15);background:rgba(255,255,255,.08);cursor:pointer;color:#fff4e6;font-family:'Playfair Display',Georgia,serif;font-size:13px;font-weight:600;transition:all .2s}
.home-profile-btn:hover{background:rgba(255,255,255,.15)}
.home-profile-avatar{display:grid;place-items:center;width:32px;height:32px;border-radius:50%;background:#d8b46a;color:#3f2f24;font-size:12px;font-weight:800;letter-spacing:.5px}
.home-profile-name{max-width:100px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.home-profile-chevron{opacity:.7;transition:transform .2s}
.home-profile-btn[aria-expanded="true"] .home-profile-chevron{transform:rotate(180deg)}
.home-profile-menu{position:absolute;top:calc(100% + 8px);right:0;z-index:1100;min-width:180px;padding:6px;border-radius:10px;border:1px solid rgba(255,255,255,.10);background:#765a46;box-shadow:0 12px 35px rgba(92,67,48,.25);opacity:0;visibility:hidden;transform:translateY(-4px);transition:all .15s ease}
.home-profile-btn[aria-expanded="true"]+.home-profile-menu{opacity:1;visibility:visible;transform:translateY(0)}
.home-profile-menu-item{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:10px;color:#fff4e6;font-size:13px;font-weight:600;text-decoration:none;transition:all .15s}
.home-profile-menu-item:hover{background:rgba(216,180,106,.16);color:#f3d9a4}
.home-profile-menu-item--danger{color:#f5a0a0}
.home-profile-menu-item--danger:hover{background:rgba(185,75,75,.20);color:#ffcccc}
.mobile-menu-btn{display:none;align-items:center;justify-content:center;min-height:40px;padding:0 14px;border:1px solid transparent;border-radius:8px;background:rgba(255,255,255,.10);color:#fff4e6;cursor:pointer;font-family:'Playfair Display',Georgia,serif;font-size:13px;font-weight:800;box-shadow:0 6px 18px rgba(92,67,48,.14)}
.mobile-menu{position:fixed;top:74px;left:50%;z-index:999;display:none;width:min(calc(100% - 24px),1152px);padding:10px;border:1px solid transparent;border-radius:10px;background:#765a46;box-shadow:0 18px 36px rgba(92,67,48,.18);transform:translateX(-50%);pointer-events:auto}
.mobile-menu.open{display:grid}
.mobile-menu a{padding:12px 14px;border-radius:8px;color:#fff4e6;font-weight:800;text-decoration:none}
.mobile-menu a:hover{background:rgba(216,180,106,.16);color:#f3d9a4}
.mobile-menu .mobile-partner{background:#3f241a;color:#fff8ef}
.mobile-menu .mobile-partner:hover{background:#4a2d22;color:#fff8ef}
.mobile-menu .mobile-login{background:#fff8ef;color:#3f2f24}
.mobile-menu .mobile-login:hover{background:#f3d9a4;color:#3f2f24}
.gp-customer-nav-spacer{height:78px}
@media(max-width:900px){.nav-links,.nav-actions{display:none}.mobile-menu-btn{display:inline-flex}}
@media(max-width:700px){.navbar{min-height:59px;padding:10px 12px}.nav-left-spacer{width:70px;flex-basis:70px}.nav-center-logo{left:12px;width:64px;height:64px}.mobile-menu{top:68px}.gp-customer-nav-spacer{height:72px}}
</style>

<header class="site-header">
  <nav class="navbar" aria-label="Main navigation">
    <div class="nav-left-spacer" aria-hidden="true"></div>
    <a class="nav-center-logo" href="<?= URLROOT ?>/main/index#top" aria-label="Golden Promise home">
      <img src="<?= URLROOT ?>/public/images/home/gp_logo.png" alt="Golden Promise logo">
    </a>
    <div class="nav-links">
      <a class="<?= $gpNavActive === 'home' ? 'active' : '' ?>" href="<?= URLROOT ?>/main/index#top">Home</a>
      <a class="<?= $gpNavActive === 'packages' ? 'active' : '' ?>" href="<?= URLROOT ?>/customerServices/packages">Packages</a>
      <a class="<?= $gpNavActive === 'services' ? 'active' : '' ?>" href="<?= URLROOT ?>/customerServices/service">Services</a>
    </div>
    <div class="nav-actions">
      <a class="nav-partner" href="<?= URLROOT ?>/users/register?type=supplier">Be a Partner</a>
      <?php if ($isLoggedIn): ?>
        <?php if (defined('APPROOT') && file_exists(APPROOT . '/views/dashboardLayout/customerNotification.php')) require APPROOT . '/views/dashboardLayout/customerNotification.php'; ?>
        <div class="home-profile-dropdown">
          <button class="home-profile-btn" type="button" aria-expanded="false">
            <span class="home-profile-avatar"><?= strtoupper(substr($_SESSION['session_name'] ?? 'U', 0, 1)) ?></span>
            <span class="home-profile-name"><?= $gpNavEsc(explode(' ', $_SESSION['session_name'] ?? 'User')[0]) ?></span>
            <svg class="home-profile-chevron" width="10" height="6" viewBox="0 0 10 6" fill="none"><path d="M1 1l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </button>
          <div class="home-profile-menu" aria-hidden="true">
            <a class="home-profile-menu-item" href="<?= URLROOT ?>/booking/myBookings"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>My Bookings</a>
            <a class="home-profile-menu-item" href="<?= URLROOT ?>/review/my"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>My Reviews</a>
            <a class="home-profile-menu-item home-profile-menu-item--danger" href="<?= URLROOT ?>/users/logout"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>Logout</a>
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
    <a class="mobile-partner" href="<?= URLROOT ?>/users/register?type=supplier">Be a Partner</a>
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
<script>
(function(){
  const mobileBtn=document.querySelector('[data-customer-mobile-btn]');
  const mobileMenu=document.querySelector('[data-customer-mobile-menu]');
  mobileBtn?.addEventListener('click',event=>{
    event.stopPropagation();
    const isOpen=mobileMenu.classList.toggle('open');
    mobileBtn.setAttribute('aria-expanded',String(isOpen));
  });

  document.addEventListener('click',event=>{
    const profileBtn=event.target.closest('.home-profile-btn');
    if(profileBtn){
      const expanded=profileBtn.getAttribute('aria-expanded')==='true';
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
})();
</script>
