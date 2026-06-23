<?php
$serviceCategories = array_values(array_filter(
    $serviceCategories ?? [],
    static fn($category) => trim((string)($category['name'] ?? '')) !== ''
));
$isLoggedIn = !empty($_SESSION['session_uid']);
$authNavUrl = $isLoggedIn ? URLROOT . '/users/logout' : URLROOT . '/users/auth';
$authNavLabel = $isLoggedIn ? 'Logout' : 'Log In';
$homeImageRoot = URLROOT . '/public/images/home';
$gpLogoPath = APPROOT . '/../public/images/home/gp_logo.png';
$gpLogoUrl = $homeImageRoot . '/gp_logo.png?v=' . (is_file($gpLogoPath) ? filemtime($gpLogoPath) : time());
$h = static fn($value) => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= $h(APPNAME) ?></title>
  <?php include APPROOT . '/views/partials/ga-tracking.php'; ?>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <style>
    @import url("https://fonts.googleapis.com/css2?family=Great+Vibes&family=Playfair+Display:wght@400;500;600;700&display=swap");

    @property --mask-width {
      syntax: "<length>";
      inherits: true;
      initial-value: 0px;
    }

    @property --mask-height {
      syntax: "<length>";
      inherits: true;
      initial-value: 0px;
    }

    html,
    body {
      height: 100%;
    }

    body {
      margin: 0;
      height: 100vh;
      overflow: hidden;
      background: #f5e8d9;
      font-family: "Playfair Display", serif;
    }

    button,
    input,
    textarea,
    select {
      font: inherit;
    }

    .font-serif {
      font-family: "Playfair Display", serif;
    }

    .font-serif-elegant {
      font-family: "Playfair Display", serif;
    }

    .cursive-font {
      font-family: "Great Vibes", cursive;
    }

    .intro-scroll {
      position: relative;
      height: 100vh;
      overflow-y: auto;
      overflow-x: hidden;
      scroll-snap-type: y proximity;
      scroll-behavior: smooth;
      overscroll-behavior-y: contain;
      -webkit-overflow-scrolling: touch;
    }

    .intro-section {
      --intro-hole: 120px;
      position: absolute;
      top: 0;
      left: 0;
      z-index: 10;
      width: 100%;
      height: 200vh;
      pointer-events: none;
    }

    .scroll-spacer {
      height: 100vh;
      scroll-snap-align: start;
      scroll-snap-stop: normal;
      pointer-events: none;
    }

    .reveal-spacer {
      height: 100vh;
      scroll-snap-align: start;
      scroll-snap-stop: normal;
      pointer-events: none;
    }

    .page-sections {
      position: relative;
      z-index: 20;
      border-radius: 0;
      background: #F5E8D9;
      color: #211d1a;
    }

    .page-sections::before {
      content: "";
      position: absolute;
      inset: 0;
      z-index: -1;
      background:
        radial-gradient(ellipse at 16% 20%, rgba(252,248,245,0.72) 0 12%, transparent 27%),
        radial-gradient(ellipse at 82% 10%, rgba(233,171,145,0.12), transparent 28%),
        radial-gradient(ellipse at 52% 88%, rgba(245,232,217,0.56), transparent 44%);
      pointer-events: none;
    }

    .page-sections > section {
      scroll-snap-align: start;
      scroll-snap-stop: normal;
    }

    .hero {
      position: relative;
      min-height: 100vh;
      overflow: hidden;
      border-radius: 0;
      background:
        url("<?= $homeImageRoot ?>/hero-bg.png") center / cover no-repeat,
        radial-gradient(ellipse at 18% 18%, rgba(252,248,245,0.92), transparent 34%),
        linear-gradient(180deg, #fff6ec 0%, #f5e4d7 100%);
      isolation: isolate;
    }

    .hero-content {
      --hero-shell: #fff6ec;
      position: relative;
      z-index: 2;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 0;
      text-align: center;
    }

    .molded-hero-card {
      --dock-radius: 24px;
      position: relative;
      width: min(100%, 1680px);
      min-height: 100vh;
      overflow: hidden;
      border-radius: 0;
      background: transparent;
      box-shadow: 0 32px 80px rgba(65, 42, 32, 0.24);
      isolation: isolate;
    }

    .molded-hero-card::after {
      content: "";
      position: absolute;
      inset: 0;
      z-index: 1;
      background:
        linear-gradient(180deg, rgba(36, 25, 20, 0.08), rgba(36, 25, 20, 0.42));
      pointer-events: none;
    }

    .molded-hero-copy {
      position: absolute;
      left: 50%;
      bottom: clamp(112px, 10vw, 144px);
      z-index: 2;
      width: min(88%, 920px);
      transform: translateX(-50%);
      color: #fceade;
    }

    .molded-hero-dock {
      position: absolute;
      left: 50%;
      bottom: clamp(38px, 5vw, 62px);
      z-index: 3;
      display: flex;
      justify-content: center;
      min-width: clamp(250px, 22vw, 360px);
      padding: 0 22px;
      background: transparent;
      border-radius: 0;
      transform: translateX(-50%);
    }

    .hero-bottom-prompt {
      position: absolute;
      left: 50%;
      bottom: clamp(88px, 10vw, 132px);
      z-index: 4;
      display: grid;
      gap: 4px;
      color: rgba(255, 246, 236, 0.9);
      font-family: "Playfair Display", Georgia, serif;
      line-height: 0.95;
      text-align: center;
      text-decoration: none;
      text-shadow: 0 8px 24px rgba(0, 0, 0, 0.44);
      transform: translateX(-50%);
    }

    .hero-bottom-prompt span {
      font-family: Arial, sans-serif;
      font-size: 11px;
      font-weight: 800;
      letter-spacing: 0.26em;
      text-transform: uppercase;
    }

    .hero-bottom-prompt strong {
      font-size: clamp(34px, 5vw, 62px);
      font-weight: 400;
      letter-spacing: 0.02em;
      text-transform: uppercase;
    }

    @media (max-width: 760px) {
      .hero-content {
        padding: 0;
      }

      .molded-hero-card {
        min-height: 100vh;
        border-radius: 0;
      }

      .molded-hero-copy {
        bottom: 118px;
      }

      .molded-hero-dock {
        width: min(86%, 320px);
        min-width: 0;
      }

      .hero-bottom-prompt {
        bottom: 84px;
      }
    }

    .site-header {
  position: fixed;
  inset: 0 0 auto;
  z-index: 1000;

  opacity: 0;
  transform: translateY(-100%);
  transition:
    transform 0.8s cubic-bezier(0.16, 1, 0.3, 1),
    opacity 0.6s ease;
}

.site-header.visible {
  opacity: 1;
  transform: translateY(0);
}

.site-header,
.site-header a,
.site-header button,
.mobile-menu {
  font-family: "Playfair Display", Georgia, serif;
}

.navbar {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;

  background: transparent;

  border-radius: 0 0 6px 6px;

  border-bottom: 0;

  box-shadow:
    0 10px 30px rgba(74, 52, 47, 0.08),
    inset 0 1px 0 rgba(252,248,245,0.4);
}
.navbar::before { display: none; }
    .nav-left-spacer {
      width: 92px;
      height: 40px;
      flex: 0 0 92px;
    }

    .nav-center-logo {
      position: absolute;
      left: 24px;
      top: 50%;
      z-index: 2;
      display: grid;
      width: 76px;
      height: 76px;
      place-items: center;
      transform: translateY(-50%);
    }

    .nav-center-logo img {
      width: 100%;
      height: 100%;
      object-fit: contain;
      filter: drop-shadow(0 4px 8px rgba(74, 52, 47, 0.22));
    }

    .home-nav-pill {
      gap: 8px;
      padding: 5px;
      border-radius: 10px;
      background: rgba(0, 0, 0, 0.52);
      box-shadow: inset 0 1px 0 rgba(252,248,245, 0.14);
      -webkit-backdrop-filter: blur(12px);
      backdrop-filter: blur(12px);
    }

    .home-nav-pill a {
      border-radius: 8px;
      padding: 7px 18px;
      color: #FFF4E6;
      white-space: nowrap;
    }

    .home-nav-pill a:first-child {
      background: rgba(252,248,245, 0.92);
      color: #3F2F24;
    }
    /* dropdown */
    .service-item {
      display: block;
      padding: 14px 18px;
      border-radius: 12px;

      color: #4A342F;
      text-decoration: none;
      font-weight: 500;

      transition: all 0.3s ease;
    }

    .service-item:hover {
      background: rgba(216, 180, 106, 0.12);
      color: #8A6A58;
      transform: translateX(4px);
    }

    .hero-brand-title {
      margin: 0 0 18px;
      color: #F3D9A4;
      font-family: "Playfair Display", serif;
      font-size: clamp(16px, 1.4vw, 24px);
      font-weight: 700;
      letter-spacing: 0.45em;
      text-transform: uppercase;

      position: relative;
      transform-origin: center;
      will-change: transform, opacity;
    }
    .mobile-menu.open {
      display: grid;
    }

    .floating-services {
      position: fixed;
      inset: 0;
      z-index: 55;
      opacity: 0;
      pointer-events: none;
      transition: opacity 220ms ease;
    }

    .floating-services.visible {
      opacity: 1;
    }

    .float-button {
      position: absolute;
      left: 0;
      top: 0;
      opacity: 1;
      transform: translate(-50%, -50%);
      will-change: transform, opacity;
    }

    .float-button:hover {
      cursor: none;
    }

    .float-button-shell {
      --float-duration: 3.5s;
      --icon-color: #7a4f54;
      --icon-soft: rgba(122, 79, 84, 0.13);
      --icon-second: #d8b46a;
      display: inline-flex;
      min-width: clamp(96px, 8vw, 132px);
      min-height: clamp(42px, 4vw, 54px);
      align-items: center;
      justify-content: center;
      padding: 0 18px;
      place-items: center;
      border: 1px solid rgba(252,248,245, 0.92);
      border-radius: 10px;
      background:
        linear-gradient(145deg, rgba(252,248,245, 0.98), rgba(255, 241, 236, 0.9));
      color: var(--icon-color);
      box-shadow:
        inset 0 1px 0 rgba(252,248,245, 0.86),
        0 18px 38px rgba(92, 55, 72, 0.18);
      -webkit-backdrop-filter: blur(18px);
      backdrop-filter: blur(18px);
      animation: appIconFloat var(--float-duration) ease-in-out infinite;
    }

    .float-button-label {
      position: static;
      width: auto;
      height: auto;
      overflow: visible;
      clip: auto;
      white-space: nowrap;
      font-family: Arial, sans-serif;
      font-size: clamp(10px, 0.78vw, 12px);
      font-weight: 800;
      letter-spacing: 0.16em;
      line-height: 1;
      text-transform: uppercase;
    }

    .service-slide-left,
    .service-slide-right,
    .service-slide-top {
      opacity: 0;
      transform: translate3d(0, 0, 0);
      transition: opacity 760ms ease;
      will-change: transform, opacity;
    }

    #our-services.images-visible .service-slide-left,
    #our-services.images-visible .service-slide-right,
    #our-services.images-visible .service-slide-top {
      opacity: 1;
    }

    #our-services.images-visible .service-slide-left {
      animation: serviceSlideFromLeft 1250ms cubic-bezier(0.2, 0.8, 0.2, 1) both;
    }

    #our-services.images-visible .service-slide-right {
      animation: serviceSlideFromRight 1250ms cubic-bezier(0.2, 0.8, 0.2, 1) both;
    }

    #our-services.images-visible .service-slide-top {
      animation: serviceSlideFromTop 1250ms cubic-bezier(0.2, 0.8, 0.2, 1) both;
    }

    #our-services {
      scroll-snap-align: start;
      scroll-snap-stop: always;
      scroll-margin-top: 0;
      padding-left: clamp(32px, 6vw, 96px);
      padding-right: clamp(32px, 6vw, 96px);
      padding-top: clamp(58px, 7vh, 76px);
      padding-bottom: clamp(58px, 7vh, 76px);
    }

    #our-services > .mx-auto {
      min-height: calc(100vh - 152px);
      gap: clamp(28px, 4vw, 56px);
    }

    #our-services > .mx-auto > div:first-child {
      margin-left: 0;
    }

    #our-services .service-slide-top {
      width: 100%;
      max-width: 720px;
    }

    #our-services .service-slide-left {
      min-height: clamp(360px, 46vh, 460px);
      width: 100%;
    }

    #our-services > .mx-auto > div:last-child {
      min-height: clamp(500px, 62vh, 580px);
    }

    #our-services .service-slide-right:first-child {
      min-height: clamp(140px, 18vh, 170px);
      width: min(56%, 340px);
    }

    #our-services .service-slide-right:first-child img {
      min-height: clamp(140px, 18vh, 170px);
    }

    #our-services .service-slide-right:last-child {
      height: clamp(220px, 30vh, 270px);
      width: min(92%, 520px);
    }

    @media (max-width: 767px) {
      #our-services {
        padding-left: 20px;
        padding-right: 20px;
        padding-top: 56px;
        padding-bottom: 56px;
      }

      #our-services > .mx-auto {
        min-height: 0;
      }

      #our-services > .mx-auto > div:last-child {
        min-height: 0;
      }
    }

    @keyframes serviceSlideFromLeft {
      from { transform: translate3d(-90px, 0, 0); }
      to { transform: translate3d(0, 0, 0); }
    }

    @keyframes serviceSlideFromRight {
      from { transform: translate3d(90px, 0, 0); }
      to { transform: translate3d(0, 0, 0); }
    }

    @keyframes serviceSlideFromTop {
      from { transform: translate3d(0, -70px, 0); }
      to { transform: translate3d(0, 0, 0); }
    }

    @keyframes liftIn {
      from { opacity: 0; transform: translateY(30px); }
      to { opacity: 1; transform: translateY(0); }
    }

    @keyframes smallFloat {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-9px); }
    }

    @keyframes appIconFloat {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-8px); }
    }

    .explore-badge {
      width: 3.25rem;
      min-width: 3.25rem;
      height: 3.25rem;
      overflow: hidden;
      gap: 0.4rem;
      padding: 0.25rem;
      transition:
        width 1450ms cubic-bezier(0.16, 1, 0.3, 1),
        min-width 1450ms cubic-bezier(0.16, 1, 0.3, 1),
        box-shadow 300ms ease;
      will-change: width, min-width;
    }

    .group:hover .explore-badge,
    .group:focus-within .explore-badge {
      width: 12.75rem;
      min-width: 12.75rem;
    }

    .explore-badge-icon {
      display: grid;
      place-items: center;
      flex: 0 0 2.5rem;
      width: 2.5rem;
      height: 2.5rem;
      position: relative;
      z-index: 2;
    }

    .explore-badge-label {
      display: block;
      line-height: 1;
      position: relative;
      z-index: 1;
      max-width: 0;
      opacity: 0;
      transform: translateX(-28px);
      background: rgba(255, 248, 239, 0.98);
      border-radius: 999px;
      box-shadow: 0 10px 22px rgba(74, 52, 47, 0.08);
      padding: 0.7rem 1rem 0.68rem 1rem;
      transition:
        max-width 1600ms cubic-bezier(0.16, 1, 0.3, 1),
        opacity 1200ms ease 350ms,
        transform 1600ms cubic-bezier(0.16, 1, 0.3, 1);
      will-change: max-width, opacity, transform;
    }

    .group:hover .explore-badge-label,
    .group:focus-within .explore-badge-label {
      max-width: 7.25rem;
      opacity: 1;
      transform: translateX(0);
    }

    #gp-traveler{
      position:fixed;
      left:0;
      top:0;
      z-index:9999;

      color:#F3D9A4;
      font-family:"Playfair Display",serif;
      font-weight:700;
      letter-spacing:.45em;
      text-transform:uppercase;

      pointer-events:none;

      opacity:0;
      transform-origin:center center;
    }
    #how-it-works {
      min-height: 260vh;
      overflow: visible;
      padding: 0 16px;
      background:
        radial-gradient(ellipse at 15% 18%, rgba(252,248,245,0.78), transparent 32%),
        radial-gradient(ellipse at 82% 12%, rgba(185,74,72,0.12), transparent 28%),
        linear-gradient(135deg, #fff8ef 0%, #f8f2ec 48%, #f5e8d9 100%);
      color: #4a342f;
      scroll-snap-align: start;
      scroll-snap-stop: normal;
      contain: paint;
    }

    .hiw-sticky {
      position: sticky;
      top: 0;
      height: 100vh;
      overflow: hidden;
      transform: translateZ(0);
    }

    .hiw-section-title {
      position: absolute;
      left: clamp(24px, 5vw, 76px);
      top: clamp(84px, 10vh, 96px);
      z-index: 5;
      width: min(620px, 90vw);
      pointer-events: none;
    }

    .hiw-section-title p {
      margin: 0 0 12px;
      color: #B94A48;
      font-size: 12px;
      font-weight: 800;
      letter-spacing: 0.38em;
      text-transform: uppercase;
    }

    .hiw-section-title h2 {
      margin: 0;
      color: #211d1a;
      font-size: clamp(42px, 5.4vw, 76px);
      font-weight: 600;
      line-height: 0.96;
      white-space: nowrap;
    }

    .hiw-canvas {
      position: absolute;
      left: 0;
      top: 58%;
      width: 3560px;
      height: 1000px;
      transform: translate3d(0, -50%, 0);
      backface-visibility: hidden;
      will-change: transform;
    }

    .hiw-lines-layer {
      position: absolute;
      inset: 0;
      z-index: 1;
      width: 100%;
      height: 100%;
      pointer-events: none;
    }

    .hiw-line-path {
      fill: none;
      stroke: #B94A48;
      stroke-width: 3;
      stroke-linecap: round;
      stroke-linejoin: round;
      stroke-dasharray: 10 12;
      filter: drop-shadow(0 10px 16px rgba(185, 74, 72, 0.14));
    }

    .hiw-card {
      position: absolute;
      z-index: 2;
      width: 320px;
      height: 260px;
      overflow: hidden;
      border: 0;
      border-radius: 28px;
      background: #211d1a;
      box-shadow: 0 34px 84px rgba(20, 12, 8, 0.28);
      transform: translate(-50%, -50%) scale(1);
      opacity: 1;
      transition: transform 620ms cubic-bezier(0.16, 1, 0.3, 1);
    }

    .hiw-card.visible {
      opacity: 1;
      transform: translate(-50%, -50%) scale(1);
    }

    .hiw-card:hover,
    .hiw-card:focus {
      transform: translate(-50%, -55%) scale(1);
      box-shadow: 0 42px 92px rgba(20, 12, 8, 0.36);
      z-index: 10;
    }

    .hiw-card-media {
      position: relative;
      height: 100%;
      width: 100%;
      overflow: hidden;
      border-radius: inherit;
      background: #2A1710;
    }

    .hiw-card-media img {
      position: absolute;
      inset: 0;
      z-index: 1;
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .hiw-card-media::after {
      content: "";
      position: absolute;
      inset: 0;
      z-index: 2;
      background:
        linear-gradient(180deg, rgba(0,0,0,0) 0%, rgba(0,0,0,0.04) 42%, rgba(42,32,30,0.56) 100%),
        linear-gradient(90deg, rgba(185,74,72,0.18), transparent 56%);
    }

    .hiw-card-media::before {
      content: "";
      position: absolute;
      left: 0;
      right: 0;
      bottom: 0;
      z-index: 3;
      height: 50%;
      background:
        linear-gradient(180deg, rgba(252,248,245,0) 0%, rgba(154,126,112,0.28) 34%, rgba(74,62,59,0.48) 100%),
        radial-gradient(ellipse at 18% 72%, rgba(210,132,96,0.18), transparent 56%);
      -webkit-backdrop-filter: blur(12px) saturate(1.05);
      backdrop-filter: blur(12px) saturate(1.05);
      mask-image: linear-gradient(180deg, transparent 0%, #000 34%, #000 100%);
      -webkit-mask-image: linear-gradient(180deg, transparent 0%, #000 34%, #000 100%);
    }

    .hiw-card-title {
      position: absolute;
      left: 22px;
      top: 80%;
      z-index: 4;
      width: calc(100% - 44px);
      margin: 0;
      padding: 0;
      color: #fcf8f5;
      font-family: Arial, sans-serif;
      font-size: 21px;
      font-weight: 800;
      line-height: 1.05;
      text-align: left;
      text-shadow: 0 3px 12px rgba(0, 0, 0, 0.44);
      transform: translateY(-50%);
      transition: top 220ms ease, transform 220ms ease;
    }

    .hiw-card:hover .hiw-card-title,
    .hiw-card:focus .hiw-card-title {
      top: 34px;
      transform: translateY(0);
    }

    .hiw-card-copy {
      position: absolute;
      left: 22px;
      right: 48px;
      bottom: 26px;
      z-index: 4;
      height: auto;
      margin: 0;
      padding: 0;
      overflow: hidden;
      color: rgba(252,248,245,0.92);
      font-family: Arial, sans-serif;
      font-size: 15px;
      font-weight: 700;
      line-height: 1.25;
      text-align: left;
      display: -webkit-box;
      max-height: 38px;
      -webkit-box-orient: vertical;
      line-clamp: 2;
      -webkit-line-clamp: 2;
      white-space: normal;
      opacity: 0;
      transform: translateY(10px);
      transition: opacity 220ms ease, transform 220ms ease;
      pointer-events: none;
    }

    .hiw-card:hover .hiw-card-copy,
    .hiw-card:focus .hiw-card-copy {
      opacity: 1;
      transform: translateY(-4px);
    }

    .hiw-card-copy::after {
      content: "›";
      position: absolute;
      right: -26px;
      top: 50%;
      color: rgba(252,248,245,0.82);
      font-size: 34px;
      font-weight: 400;
      line-height: 1;
      transform: translateY(-52%);
    }

    @media (max-width: 767px) {
      #how-it-works {
        min-height: auto;
        padding: 64px 16px;
      }

      .hiw-sticky {
        position: relative;
        height: auto;
        overflow: visible;
      }

      .hiw-section-title {
        position: relative;
        left: auto;
        top: auto;
        margin: 0 auto 34px;
        text-align: center;
      }

      .hiw-canvas {
        position: relative;
        left: auto;
        top: auto;
        display: grid;
        width: min(100%, 360px);
        height: auto;
        margin: 0 auto;
        gap: 24px;
        transform: none !important;
      }

      .hiw-lines-layer {
        display: none;
      }

      .hiw-card {
        position: relative;
        left: auto !important;
        top: auto !important;
        width: 100%;
        max-width: 320px;
        height: min(72vw, 260px);
        margin: 0 auto;
        opacity: 1;
        transform: none;
      }

      .hiw-card.visible,
      .hiw-card:hover,
      .hiw-card:focus {
        transform: none;
      }
    }

    #gallery {
      min-height: 240vh;
      background: #211d1a;
      color: #fff4e6;
      scroll-snap-align: start;
      scroll-snap-stop: normal;
    }

    .gp-gallery-sticky {
      position: sticky;
      top: 0;
      display: flex;
      min-height: 100vh;
      align-items: center;
      justify-content: center;
      overflow: hidden;
      padding: clamp(72px, 8vw, 112px) 24px;
    }

    .gp-gallery-heading {
      position: absolute;
      left: clamp(20px, 5vw, 72px);
      top: clamp(84px, 12vh, 132px);
      z-index: 4;
      max-width: 520px;
      pointer-events: none;
    }

    .gp-gallery-heading p {
      margin: 0 0 10px;
      color: rgba(243, 217, 164, 0.78);
      font-size: 11px;
      font-weight: 800;
      letter-spacing: 0.42em;
      text-transform: uppercase;
    }

    .gp-gallery-heading h2 {
      margin: 0;
      font-family: "Playfair Display", serif;
      font-size: clamp(44px, 8vw, 118px);
      font-weight: 600;
      line-height: 0.9;
      color: #fff4e6;
    }

    .gp-gallery-grid {
      width: min(100% - 32px, 1520px);
      display: grid;
      grid-template-columns: repeat(5, 1fr);
      grid-template-rows: repeat(3, auto);
      gap: clamp(10px, 7.35vw, 80px);
      align-content: center;
      position: absolute;
      left: 50%;
      top: 54%;
      transform: translate(-50%, -50%);
    }

    .gp-gallery-layer {
      display: grid;
      grid-column: 1 / -1;
      grid-row: 1 / -1;
      z-index: 4;
      grid-template-columns: subgrid;
      grid-template-rows: subgrid;
      opacity: 0;
      transform: scale(0.82);
      transform-origin: center;
      pointer-events: none;
      will-change: opacity, transform;
    }

    .gp-gallery-photo {
      position: relative;
      pointer-events: auto;
    }

    .gp-gallery-layer > .gp-gallery-photo {
      --gallery-float-distance: 18px;
      animation: galleryVerticalFloat 6.8s ease-in-out infinite;
      will-change: transform;
    }

    .gp-gallery-layer > .gp-gallery-photo:nth-child(2n) {
      --gallery-float-distance: -22px;
      animation-duration: 7.6s;
      animation-delay: -1.8s;
    }

    .gp-gallery-layer > .gp-gallery-photo:nth-child(3n) {
      --gallery-float-distance: 26px;
      animation-duration: 8.4s;
      animation-delay: -3.2s;
    }

    .gp-gallery-layer > .gp-gallery-photo:nth-child(4n) {
      --gallery-float-distance: -16px;
      animation-duration: 6.2s;
      animation-delay: -4.4s;
    }

    .gp-gallery-layer:nth-of-type(1) div:nth-of-type(odd) {
      grid-column: 1;
    }

    .gp-gallery-layer:nth-of-type(1) div:nth-of-type(even) {
      grid-column: -2;
    }

    .gp-gallery-layer:nth-of-type(2) div:nth-of-type(odd) {
      grid-column: 2;
    }

    .gp-gallery-layer:nth-of-type(2) div:nth-of-type(even) {
      grid-column: -3;
    }

    .gp-gallery-layer:nth-of-type(3) div:first-of-type {
      grid-column: 3;
      grid-row: 1;
    }

    .gp-gallery-layer:nth-of-type(3) div:last-of-type {
      grid-column: 3;
      grid-row: -1;
    }

    .gp-gallery-grid img {
      position: relative;
      width: 100%;
      aspect-ratio: 4 / 5;
      object-fit: cover;
      border-radius: 1rem;
      box-shadow: 0 24px 64px rgba(0, 0, 0, 0.28);
      filter: blur(0);
      transform: translateZ(0) scale(1);
      transition:
        filter 420ms ease,
        opacity 420ms ease,
        transform 420ms cubic-bezier(0.16, 1, 0.3, 1);
      will-change: filter, transform;
    }

    .gp-gallery-grid.gallery-photo-hovering .gp-gallery-photo img,
    .gp-gallery-grid:has(img:hover) img {
      filter: blur(7px) saturate(0.78);
      opacity: 0.56;
    }

    .gp-gallery-grid.gallery-photo-hovering .gp-gallery-photo.active img,
    .gp-gallery-grid:has(img:hover) img:hover {
      filter: blur(0) saturate(1.08);
      opacity: 1;
      z-index: 6;
    }

    .gp-gallery-layer .gp-gallery-photo.active,
    .gp-gallery-layer .gp-gallery-photo:has(img:hover) {
      z-index: 7;
    }

    .gp-gallery-layer .gp-gallery-photo.active img,
    .gp-gallery-layer .gp-gallery-photo:has(img:hover) img {
      transform: translateZ(0) scale(1.045);
    }

    .gp-gallery-scaler {
      position: relative;
      z-index: 3;
      grid-area: 2 / 3;
      width: 100%;
      height: 100%;
      min-height: 220px;
    }

    .gp-gallery-scaler img {
      position: absolute;
      left: 50%;
      top: 50%;
      width: 100vw;
      height: 100vh;
      max-width: none;
      object-fit: cover;
      border-radius: 1rem;
      transform: translate(-50%, -50%);
      animation: galleryScalerFloat 7.2s ease-in-out infinite;
      will-change: width, height, filter, transform;
    }

    @keyframes galleryVerticalFloat {
      0%, 100% {
        transform: translate3d(0, calc(var(--gallery-float-distance) * -0.5), 0);
      }

      50% {
        transform: translate3d(0, calc(var(--gallery-float-distance) * 0.5), 0);
      }
    }

    @keyframes galleryScalerFloat {
      0%, 100% {
        transform: translate(-50%, calc(-50% - 10px));
      }

      50% {
        transform: translate(-50%, calc(-50% + 10px));
      }
    }

    @media (max-width: 760px) {
      #gallery {
        min-height: 170vh;
      }

      .gp-gallery-heading {
        top: 72px;
      }

      .gp-gallery-grid {
        width: min(100% - 24px, 680px);
        grid-template-columns: repeat(3, 1fr);
        gap: 14px;
      }

      .gp-gallery-layer:nth-of-type(1) {
        display: none;
      }

      .gp-gallery-layer:nth-of-type(2) div:nth-of-type(odd) {
        grid-column: 1;
        transform: translateY(0);
      }

      .gp-gallery-layer:nth-of-type(2) div:nth-of-type(even) {
        grid-column: -2;
        transform: translateY(0);
      }

      .gp-gallery-layer:nth-of-type(3) div:first-of-type,
      .gp-gallery-layer:nth-of-type(3) div:last-of-type {
        grid-column: 2;
        transform: translateY(0);
      }

      .gp-gallery-scaler {
        grid-area: 2 / 2;
      }

      .gp-gallery-grid img {
        border-radius: 14px;
      }
    }

    @media (prefers-reduced-motion: reduce) {
      .gp-gallery-layer > div,
      .gp-gallery-scaler img {
        animation: none;
      }
    }

    #reviews {
      position: relative;
      min-height: 78vh;
      overflow: hidden;
      background: #C8B19F;
      color: #4A342F;
      padding: clamp(44px, 5vw, 72px) 24px;
      scroll-snap-align: start;
      scroll-snap-stop: always;
    }

    .review-cloud {
      position: relative;
      width: min(100%, 1040px);
      min-height: 500px;
      margin: 0 auto;
    }

    .review-card {
      position: absolute;
      border: 1px solid rgba(118, 90, 70, 0.12);
      background: rgba(255, 248, 239, 0.95);
      box-shadow: 0 24px 58px rgba(74, 52, 47, 0.16);
      color: #5f514a;
      backdrop-filter: blur(14px);
      opacity: 0;
      transform:
        translate3d(var(--review-enter-x, 0), var(--review-enter-y, 40px), 0)
        rotate(var(--review-enter-rotate, 0deg))
        scale(0.94);
      transition:
        opacity 720ms ease,
        transform 920ms cubic-bezier(0.16, 1, 0.3, 1);
      transition-delay: var(--review-enter-delay, 0ms);
      will-change: opacity, transform;
    }

    #reviews.reviews-visible .review-card {
      opacity: 1;
      transform: translate3d(0, 0, 0) rotate(0deg) scale(1);
    }

    .review-card h3 {
      margin: 0 0 10px;
      color: #4A342F;
      font-family: "Playfair Display", serif;
      font-size: clamp(18px, 1.8vw, 26px);
      line-height: 1.05;
    }

    .review-card p,
    .review-card blockquote {
      margin: 0;
      font-size: 13px;
      font-weight: 600;
      line-height: 1.45;
    }

    .review-stars {
      color: #D8B46A;
      font-size: 15px;
      letter-spacing: 0.08em;
    }

    .review-avatar {
      display: grid;
      place-items: center;
      width: 58px;
      height: 58px;
      border: 5px solid rgba(255, 248, 239, 0.92);
      border-radius: 999px;
      background: #8A6A58;
      color: #FFF8EF;
      font-family: "Playfair Display", serif;
      font-size: 19px;
      font-weight: 700;
      box-shadow: 0 14px 34px rgba(74, 52, 47, 0.16);
    }

    .review-card-feature {
      left: 38%;
      top: 54px;
      display: grid;
      justify-items: center;
      width: min(260px, 82vw);
      padding: 56px 26px 26px;
      border-radius: 28px;
      text-align: center;
    }

    .review-card-feature .review-avatar {
      position: absolute;
      top: -38px;
      width: 82px;
      height: 82px;
      font-size: 25px;
    }

    .review-card-feature .signature {
      margin-top: 12px;
      color: rgba(74, 52, 47, 0.55);
      font-family: "Great Vibes", cursive;
      font-size: 30px;
      font-weight: 400;
    }

    .review-card-wide {
      width: min(290px, 78vw);
      padding: 16px 20px;
      border-radius: 10px;
    }

    .review-card-pill {
      display: grid;
      grid-template-columns: auto 1fr;
      gap: 12px;
      align-items: center;
      width: min(310px, 82vw);
      padding: 14px 18px;
      border-radius: 999px;
    }

    .review-card-note {
      width: min(240px, 78vw);
      padding: 18px 20px;
      border-radius: 22px;
      text-align: center;
    }

    .review-card-speech {
      width: min(260px, 80vw);
      padding: 18px 20px;
      border-radius: 14px;
    }

    .review-card-speech::after {
      content: "";
      position: absolute;
      right: 36px;
      bottom: -14px;
      width: 28px;
      height: 28px;
      background: inherit;
      clip-path: polygon(0 0, 100% 0, 100% 100%);
    }

    .review-pos-1 { left: 5%; top: 24px; }
    .review-pos-2 { left: 0; top: 152px; }
    .review-pos-3 { left: 2%; top: 318px; }
    .review-pos-4 { left: 26%; top: 346px; }
    .review-pos-5 { right: 3%; top: 22px; }
    .review-pos-6 { right: 0; top: 160px; }
    .review-pos-7 { right: 5%; top: 300px; }
    .review-pos-8 { left: 58%; top: 338px; }

    @media (max-width: 980px) {
      #reviews {
        min-height: auto;
      }

      .review-cloud {
        display: grid;
        min-height: 0;
        gap: 22px;
      }

      .review-card,
      .review-card-feature,
      .review-card-wide,
      .review-card-pill,
      .review-card-note,
      .review-card-speech {
        position: relative;
        inset: auto;
        width: min(100%, 620px);
        margin: 0 auto;
      }

      .review-card-feature {
        margin-top: 48px;
      }
    }

    @media (prefers-reduced-motion: reduce) {
      .review-card {
        opacity: 1;
        transform: none;
        transition: none;
      }
    }

    #contact {
      min-height: 72vh;
      display: grid;
      place-items: center;
      padding: clamp(42px, 5vw, 72px) 24px;
      background: #2A1710;
      color: #FFF8EF;
      scroll-snap-align: start;
      scroll-snap-stop: always;
    }

    .contact-card {
      position: relative;
      width: min(100%, 1120px);
      min-height: 420px;
      overflow: hidden;
      border: 1px solid rgba(216, 180, 106, 0.22);
      border-radius: 34px;
      background: #2A1710;
      box-shadow: 0 36px 90px rgba(0, 0, 0, 0.32);
      backdrop-filter: blur(18px);
    }

    .contact-card::before {
      content: "";
      position: absolute;
      inset: 20px;
      border: 1px solid rgba(216, 180, 106, 0.18);
      border-radius: 24px;
      pointer-events: none;
    }

    .contact-card::after {
      content: "GP";
      position: absolute;
      right: clamp(28px, 6vw, 76px);
      bottom: clamp(16px, 4vw, 44px);
      color: rgba(216, 180, 106, 0.08);
      background: transparent;
      font-size: clamp(110px, 19vw, 240px);
      font-weight: 700;
      line-height: 0.8;
      pointer-events: none;
    }

    .contact-card-inner {
      position: relative;
      z-index: 1;
      display: grid;
      grid-template-columns: 0.92fr 1.08fr;
      gap: clamp(36px, 6vw, 86px);
      min-height: inherit;
      padding: clamp(28px, 4vw, 52px);
    }

    .contact-kicker {
      margin: 0 0 18px;
      color: #B94A48;
      font-size: 12px;
      font-weight: 800;
      letter-spacing: 0.34em;
      text-transform: uppercase;
    }

    .contact-title {
      margin: 0;
      font-family: "Playfair Display", serif;
      font-size: clamp(42px, 6vw, 84px);
      font-weight: 600;
      line-height: 0.94;
      color: #FFF8EF;
    }

    .contact-script {
      display: block;
      margin-top: 14px;
      color: #B94A48;
      font-family: "Great Vibes", cursive;
      font-size: clamp(42px, 6vw, 78px);
      font-weight: 400;
      line-height: 0.9;
    }

    .contact-copy {
      max-width: 420px;
      margin: 30px 0 0;
      color: #e2cdb9;
      font-size: 16px;
      font-weight: 600;
      line-height: 1.75;
    }

    .contact-details {
      display: grid;
      gap: 18px;
      margin-top: 46px;
    }

    .contact-detail {
      display: grid;
      gap: 4px;
    }

    .contact-detail span {
      color: rgba(255, 248, 239, 0.55);
      font-size: 11px;
      font-weight: 900;
      letter-spacing: 0.22em;
      text-transform: uppercase;
    }

    .contact-detail a,
    .contact-detail p {
      margin: 0;
      color: #FFF8EF;
      font-size: clamp(18px, 2vw, 24px);
      font-weight: 700;
      text-decoration: none;
    }

    .contact-form {
      align-self: center;
      display: grid;
      gap: 16px;
      padding: clamp(22px, 3vw, 34px);
      border: 1px solid rgba(216, 180, 106, 0.20);
      border-radius: 26px;
      background: rgba(252,248,245, 0.08);
      box-shadow: inset 0 1px 0 rgba(252,248,245, 0.12);
    }

    .contact-form label {
      display: grid;
      gap: 8px;
      color: #d8b46a;
      font-size: 11px;
      font-weight: 900;
      letter-spacing: 0.18em;
      text-transform: uppercase;
    }

    .contact-form input,
    .contact-form textarea {
      width: 100%;
      border: 0;
      border-bottom: 1px solid rgba(255, 248, 239, 0.28);
      border-radius: 0;
      background: transparent;
      padding: 10px 0 12px;
      color: #FFF8EF;
      font-size: 16px;
      outline: none;
    }

    .contact-form textarea {
      min-height: 112px;
      resize: vertical;
    }

    .contact-form input:focus,
    .contact-form textarea:focus {
      border-bottom-color: #B94A48;
    }

    .contact-submit {
      justify-self: start;
      margin-top: 12px;
      border: 0;
      border-radius: 999px;
      background: #B94A48;
      padding: 14px 26px;
      color: #FFF8EF;
      font-size: 12px;
      font-weight: 900;
      letter-spacing: 0.18em;
      text-transform: uppercase;
      box-shadow: 0 18px 38px rgba(185, 74, 72, 0.24);
      cursor: pointer;
      transition: transform 240ms ease, background-color 240ms ease;
    }

    .contact-submit:hover {
      transform: translateY(-2px);
      background: #7F2F2D;
    }

    @media (max-width: 860px) {
      .contact-card-inner {
        grid-template-columns: 1fr;
      }

      .contact-card {
        border-radius: 26px;
      }
    }

    @media (prefers-reduced-motion: reduce) {
      .hiw-card,
      .hiw-line-path {
        opacity: 1;
        transform: none;
        animation: none;
        transition: none;
      }
    }

    .site-footer {
      min-height: 22vh;
      display: grid;
      place-items: center;
      padding: clamp(18px, 2.2vw, 28px) 24px 18px;
      background: #2A1710;
      color: #fcf8f5;
      scroll-snap-align: start;
      scroll-snap-stop: always;
    }
.footer-contact-card{
    text-align:center;
    margin-top:clamp(16px,2.2vw,24px);
}

#footerText{
    font-size:clamp(1.7rem,4vw,3rem);
    font-weight:700;
    transition:.35s ease;
}

#footerLabel{
    color:#aaa;
    margin-top:4px;
    transition:.35s ease;
}

.footer-icons{
    display:flex;
    justify-content:center;
    gap:14px;
    margin-top:18px;
}

.contact-btn{
    background: rgba(252,248,245,0.05);
    border: 1px solid rgba(252,248,245,0.16);
    border-radius: 10px;
    cursor: pointer;
    color: #999;
    width: 42px;
    height: 42px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: background .3s ease, color .3s ease, border-color .3s ease;
}

.contact-btn svg{
    width:22px;
    height:22px;
}

.contact-btn:hover,
.contact-btn.active{
    background: rgba(252,248,245,0.16);
    border-color: rgba(252,248,245,0.4);
    color: #fcf8f5;
}

.text-changing{
    opacity:0;
    transform:translateY(12px);
}

    .footer-inner {
      width: min(100%, 980px);
      display: grid;
      justify-items: center;
      text-align: center;
    }

    .footer-brand {
      margin: 0;
      color: #fcf8f5;
      font-family: Arial, Helvetica, sans-serif;
      font-size: clamp(30px, 5.4vw, 76px);
      font-weight: 800;
      letter-spacing: 0;
      line-height: 1;
    }

    .footer-nav {
      display: flex;
      justify-content: center;
      gap: clamp(28px, 7vw, 92px);
      margin-top: clamp(14px, 2vw, 22px);
      font-family: "Playfair Display", Georgia, serif;
    }

    .footer-nav a {
      color: rgba(252,248,245, 0.84);
      font-size: 12px;
      font-weight: 700;
      letter-spacing: 0.22em;
      text-decoration: none;
      text-transform: uppercase;
      transition: color 220ms ease, opacity 220ms ease;
    }

    .footer-nav a:hover,
    .footer-nav a:focus-visible {
      color: #fcf8f5;
      opacity: 1;
      outline: none;
    }

    .footer-socials {
      display: flex;
      justify-content: center;
      gap: 18px;
      margin-top: clamp(24px, 3vw, 36px);
    }

    .footer-social {
      display: grid;
      width: 46px;
      height: 46px;
      place-items: center;
      border: 1px solid #fcf8f5;
      border-radius: 50%;
      background: #fcf8f5;
      color: #2A1710;
      text-decoration: none;
      transition:
        background-color 220ms ease,
        color 220ms ease,
        transform 220ms ease;
    }

    .footer-social:hover,
    .footer-social:focus-visible {
      background: #2A1710;
      color: #fcf8f5;
      transform: translateY(-3px);
      outline: none;
    }

    .footer-social-mark {
      font-family: Arial, Helvetica, sans-serif;
      font-size: 22px;
      font-weight: 900;
      line-height: 1;
    }

    .footer-copy {
      margin: clamp(18px, 2.4vw, 28px) 0 0;
      color: rgba(252,248,245, 0.78);
      font-family: Arial, Helvetica, sans-serif;
      font-size: 12px;
      font-weight: 500;
      letter-spacing: 0.04em;
    }

    @media (max-width: 640px) {
      .site-footer {
        min-height: 30vh;
      }

      .footer-nav {
        gap: 18px;
        flex-wrap: wrap;
      }

      .footer-nav a {
        font-size: 11px;
      }
    }

    .intro-sticky {
      position: sticky;
      top: 0;
      height: 100vh;
      overflow: hidden;
      display: flex;
      align-items: center;
      justify-content: center;
      background: transparent;
    }

    .intro-image {
      position: absolute;
      inset: 0;
      width: 100%;
      height: 100%;
      object-fit: cover;
      object-position: center;
      transform: scale(1);
      opacity: 1;
      transform-origin: center center;
      will-change: transform, opacity;
      -webkit-mask-image: radial-gradient(
        circle at center,
        transparent 0 var(--intro-hole),
        rgba(0, 0, 0, 1) calc(var(--intro-hole) + 3px) 100%
      );
      mask-image: radial-gradient(
        circle at center,
        transparent 0 var(--intro-hole),
        rgba(0, 0, 0, 1) calc(var(--intro-hole) + 3px) 100%
      );
      pointer-events: none;
    }

    .magic-reveal {
      --mask-x: 50%;
      --mask-y: 50%;
      --mask-width: 0px;
      --mask-height: 0px;
      position: fixed;
      top: 0;
      left: 0;
      z-index: 1;
      width: 100vw;
      height: 100vh;
      overflow: hidden;
      display: grid;
      place-items: center;
      isolation: isolate;
      background: #f9f6f0;
      pointer-events: none;
    }

    .magic-reveal::before {
      content: "";
      position: absolute;
      inset: -28%;
      z-index: 0;
      background:
        radial-gradient(circle at 50% 50%, rgba(252,248,245, 0.9), transparent 28%),
        radial-gradient(circle at 24% 24%, rgba(252,248,245, 0.72), transparent 44%),
        radial-gradient(circle at 74% 34%, rgba(255, 238, 242, 0.18), transparent 56%),
        radial-gradient(circle at 50% 72%, rgba(255, 250, 240, 0.26), transparent 62%),
        linear-gradient(135deg, rgba(255, 252, 247, 0.34), rgba(255, 249, 244, 0.3)),
        url("<?= $homeImageRoot ?>/hiddenIntro.png");
      background-position: center;
      background-size: cover;
      transform: scale(1.12);
    }

    .magic-reveal::after {
      content: "";
      position: absolute;
      inset: 0;
      z-index: 2;
      background:
        linear-gradient(90deg, rgba(248, 202, 213, 0.34), transparent 24%, transparent 76%, rgba(132, 88, 65, 0.2)),
        linear-gradient(180deg, rgba(132, 88, 65, 0.16), transparent 24%, transparent 76%, rgba(248, 202, 213, 0.3));
      -webkit-backdrop-filter: blur(8px);
      backdrop-filter: blur(8px);
      -webkit-mask-image: radial-gradient(circle at center, transparent 0 32%, rgba(0, 0, 0, 1) 58%);
      mask-image: radial-gradient(circle at center, transparent 0 32%, rgba(0, 0, 0, 1) 58%);
      pointer-events: none;
    }

    .reveal-photo {
      position: absolute;
      inset: 0;
      z-index: 1;
      background-image: url("<?= $homeImageRoot ?>/hiddenIntro.png");
      background-size: cover;
      background-position: center;
      opacity: 1;
      -webkit-mask-image: radial-gradient(
        ellipse var(--mask-width) var(--mask-height) at var(--mask-x) var(--mask-y),
        rgba(0, 0, 0, 1) 0,
        rgba(0, 0, 0, 0.92) 34%,
        rgba(0, 0, 0, 0.42) 66%,
        rgba(0, 0, 0, 0) 100%
      );
      mask-image: radial-gradient(
        ellipse var(--mask-width) var(--mask-height) at var(--mask-x) var(--mask-y),
        rgba(0, 0, 0, 1) 0,
        rgba(0, 0, 0, 0.92) 34%,
        rgba(0, 0, 0, 0.42) 66%,
        rgba(0, 0, 0, 0) 100%
      );
      pointer-events: none;
    }

    .brand-banner {
      position: absolute;
      inset: 0;
      z-index: 5;
      display: grid;
      place-items: center;
      width: 100%;
      height: 100%;
      padding: 0 1rem;
      text-align: center;
      color: #6f4b3e;
      text-shadow: 0 18px 50px rgba(111, 75, 62, 0.16);
      transform: translateY(-24px);
    }

    .brand-name {
      display: block;
      width: min(46vw, 380px);
      height: min(46vw, 380px);
      margin: 0 auto;
      border-radius: 50%;
      object-fit: contain;
    }

    .brand-tagline {
      position: absolute;
      top: calc(55% + min(24vw, 120px));
      left: 50%;
      width: min(92vw, 760px);
      margin: 0;
      transform: translateX(-50%);
      font-family: "Great Vibes", cursive;
      font-size: clamp(2.6rem, 6vw, 4.6rem);
      font-weight: 400;
      letter-spacing: 0;
      text-indent: 0;
    }

    .brand-subtitle {
      position: absolute;
      top: calc(60% + min(24vw, 192px));
      left: 50%;
      width: min(92vw, 760px);
      margin: 0;
      transform: translateX(-50%);
      font-family: "Playfair Display", serif;
      font-size: clamp(0.85rem, 1.8vw, 1.08rem);
      letter-spacing: 0.08em;
    }

    @media (hover: none), (pointer: coarse) {
      .reveal-photo {
        opacity: 0.32;
        -webkit-mask-image: none;
        mask-image: none;
      }
    }
  </style>
</head>

<body>
<header class="site-header">
  <nav class="navbar flex items-center justify-between gap-4 px-5 py-3 max-[640px]:px-3 max-[640px]:py-2.5" aria-label="Main navigation">

    <!-- LEFT SPACER -->
    <div class="nav-left-spacer" aria-hidden="true"></div>

    <!-- CENTER LOGO -->
    <a class="nav-center-logo" href="#top" aria-label="Golden Promise home">
      <img src="<?= $h($gpLogoUrl) ?>" alt="Golden Promise logo">
    </a>

    <!-- NAV LINKS -->
    <div class="absolute left-1/2 top-1/2 flex -translate-x-1/2 -translate-y-1/2 items-center gap-[18px]">
      <div class="home-nav-pill flex items-center text-sm font-semibold text-[#4A342F] max-[980px]:hidden">

        <a class="transition duration-300 hover:text-[#5a4038]" href="#top">
          Home
        </a>

        <a class="transition duration-300 hover:text-[#5a4038]" href="<?= URLROOT ?>/customerServices/packages">
          Packages
        </a>

        <a class="transition duration-300 hover:text-[#5a4038]" id="servicesNavLink" href="<?= URLROOT ?>/customerServices/service">
          Services
        </a>

      </div>
    </div>

    <!-- RIGHT BUTTONS -->
    <div class="ml-auto flex items-center gap-3 max-[980px]:hidden">
      <a
  class="rounded-[8px] bg-[#3F241A] px-4 py-2 text-[13px] font-semibold text-[#FFF8EF] shadow-[0_10px_25px_rgba(63,36,26,0.24)] transition duration-300 hover:-translate-y-0.5 hover:bg-[#4A2D22]"
  href="<?= URLROOT ?>/users/register?type=supplier">
  Be a Partner
</a>

      <a
  class="rounded-[8px] border border-transparent bg-[#FFF8EF] px-4 py-2 text-[13px] font-semibold tracking-[0.04em] text-[#3F2F24] transition duration-300 hover:bg-[#F3D9A4] hover:text-[#3F2F24]"
  href="<?= $h($authNavUrl) ?>">
  <?= $h($authNavLabel) ?>
</a>
    </div>

    <!-- MOBILE BUTTON -->
    <button
      class="hidden min-h-10 cursor-pointer items-center justify-center rounded-[8px] border border-transparent bg-white/10 px-3.5 text-[13px] font-bold text-[#FFF4E6] shadow-[0_6px_18px_rgba(92,67,48,0.14)] max-[980px]:inline-flex"
      id="menuButton"
      type="button"
      aria-label="Open navigation"
      aria-expanded="false">

      Menu
    </button>

  </nav>

  <!-- MOBILE MENU -->
  <div
    class="mobile-menu mx-auto mt-2.5 hidden w-[min(100%,1152px)] rounded-[10px] border border-transparent bg-[#765A46] p-2.5 shadow-[0_18px_36px_rgba(92,67,48,0.18)]"
    id="mobileMenu">

    <a class="rounded-[8px] px-3.5 py-3 font-bold text-[#FFF4E6] hover:bg-[#D8B46A]/16 hover:text-[#F3D9A4]" href="#top">
      Home
    </a>

    <a class="rounded-[8px] px-3.5 py-3 font-bold text-[#FFF4E6] hover:bg-[#D8B46A]/16 hover:text-[#F3D9A4]" href="<?= URLROOT ?>/customerServices/packages">
      Packages
    </a>

    <a class="rounded-[8px] px-3.5 py-3 font-bold text-[#FFF4E6] hover:bg-[#D8B46A]/16 hover:text-[#F3D9A4]" href="<?= URLROOT ?>/customerServices/service">
      Services
    </a>

    <a class="rounded-[8px] bg-[#3F241A] px-3.5 py-3 text-[13px] font-semibold text-[#FFF8EF] transition duration-300 hover:-translate-y-0.5 hover:bg-[#4A2D22]" href="<?= URLROOT ?>/users/register?type=supplier">
      Be a Partner
    </a>

    <a class="rounded-[8px] bg-[#FFF8EF] px-3.5 py-3 text-[13px] font-semibold text-[#3F2F24] transition duration-300 hover:bg-[#F3D9A4]" href="<?= $h($authNavUrl) ?>">
      <?= $h($authNavLabel) ?>
    </a>

  </div>
</header> 

  <!-- travelling text -->
  <div id="gp-traveler">Golden Promise</div>

  <div class="floating-services" id="floatingServices" aria-hidden="true"></div>

  <main id="introScroll" class="intro-scroll">
    <section id="introSection" class="intro-section" aria-label="Intro Image Section">
      <div class="intro-sticky">
        <img
          id="introImage"
          class="intro-image"
          src="<?= $homeImageRoot ?>/introImage.png"
          alt="Golden Promise intro"
        />
      </div>
    </section>

    <section id="magicReveal" class="magic-reveal" aria-label="Golden Promise magic reveal">
      <div class="reveal-photo" aria-hidden="true"></div>

      <div class="brand-banner">
        <img class="brand-name" src="<?= $h($gpLogoUrl) ?>" alt="Golden Promise">
        <p class="brand-tagline">Golden Promise</p>
        <p class="brand-subtitle">Designing Weddings as Unique as Your Love.</p>
      </div>
    </section>

    <div class="scroll-spacer" aria-hidden="true"></div>
    <div class="reveal-spacer" aria-hidden="true"></div>
    <div class="page-sections" id="top">
      <section class="hero" aria-label="Golden Promise wedding hero">
        <div class="hero-content">
          <div class="molded-hero-card animate-[liftIn_850ms_ease_both]">
            <div class="molded-hero-copy">
              <p id="hero-gp" class="hero-brand-title">
                Golden Promise
              </p>

               <h1 class="cursive-font mx-auto max-w-[1050px] text-[clamp(72px,9vw,140px)] font-normal leading-[1.12] max-[640px]:max-w-full max-[640px]:text-[clamp(52px,14vw,90px)]">Hand in Hand, Promised</h1>
              <p class="mx-auto mt-7 max-w-[760px] text-[clamp(17px,2vw,20px)] leading-[1.7]">We design graceful wedding experiences filled with soft florals, warm candlelight, timeless details, and unforgettable emotion.</p>
            </div>

            <div class="molded-hero-dock">
              <a href="#our-services" class="relative z-10 inline-flex min-h-[54px] items-center justify-center rounded-full border border-white/70 bg-[#fceade]/90 px-[30px] text-[13px] font-extrabold uppercase tracking-[0.18em] text-[#530B0A] shadow-[0_20px_42px_rgba(120,95,85,0.18)] transition hover:-translate-y-0.5 hover:bg-white hover:shadow-[0_16px_30px_rgba(117,91,80,0.2)] max-[640px]:w-full max-[640px]:tracking-[0.14em]">Start your journey</a>
            </div>

          </div>
        </div>
      </section>

      <section id="our-services" class="min-h-screen bg-[#F5E8D9] px-4 pb-24 pt-24 max-[767px]:py-16" aria-label="Our Services">
        <div class="mx-auto grid min-h-[calc(100vh-12rem)] w-[min(100%,1240px)] grid-cols-[1fr_1fr] items-center gap-6 max-[767px]:min-h-0 max-[767px]:grid-cols-1">
          <div class="-ml-6 max-[767px]:ml-0">
            <div class="service-slide-top relative z-10 mb-5 w-[138%] max-w-none max-[767px]:mb-5 max-[767px]:w-full">
             <h2 id="serviceTitle" class="font-serif mb-3 text-[clamp(34px,4.1vw,62px)] font-semibold leading-[1] text-[#211d1a]">Our Service</h2>
              <p class="max-w-[820px] text-base leading-[1.75] text-[#6f625a]">
                At <span id="service-gp" class="font-bold text-[#530B0A]">Golden Promise</span> we operates as a centralized hub designed to streamline the connection between customers and qualified wedding professionals, helping couples discover trusted planners, florists, photographers, caterers, stylists, entertainers, and detail-focused creative teams through one refined experience.
              </p>
            </div>

            <figure class="service-slide-left relative m-0 min-h-[520px] w-[98%] overflow-hidden rounded-[18px] bg-[#211d1a] shadow-[0_34px_82px_rgba(54,35,28,0.22)] max-[767px]:min-h-[430px] max-[767px]:w-full max-[767px]:rounded-[16px]">
              <img
                class="absolute inset-0 h-full w-full object-cover"
                src="<?= $homeImageRoot ?>/serviceImg1.png"
                alt="Cinematic wedding reception with dramatic light"
              >
            </figure>
          </div>

          <div class="flex min-h-[650px] flex-col justify-between pt-0 max-[767px]:min-h-0 max-[767px]:gap-6">
            <figure class="service-slide-right relative ml-auto min-h-[165px] w-[56%] overflow-hidden rounded-[16px] bg-[#211d1a] shadow-[0_24px_54px_rgba(54,35,28,0.16)] max-[767px]:min-h-[260px] max-[767px]:w-full max-[767px]:rounded-[14px]">
              <img
                class="h-full min-h-[165px] w-full object-cover max-[767px]:min-h-[260px]"
                src="<?= $homeImageRoot ?>/serviceImg2.png"
                alt="Dark romantic wedding floral detail"
              >
            </figure>

            <div class="mx-auto max-w-[520px] py-8 text-center max-[767px]:py-0">
              <p class="text-[12px] font-extrabold uppercase tracking-[0.28em] text-[#6D4C5B]">Curated Vendor Network</p>
              <p class="mt-3 text-sm leading-[1.7] text-[#6f625a]">
                Planning, florals, photography, catering, music, beauty, attire, decor, venue styling, and day-of coordination partners gathered in one graceful place for an easier wedding journey.
              </p>
            </div>

            <figure class="service-slide-right relative ml-auto h-[285px] w-[92%] overflow-hidden rounded-[16px] bg-[#211d1a] shadow-[0_28px_64px_rgba(54,35,28,0.18)] max-[767px]:h-[280px] max-[767px]:w-full max-[767px]:rounded-[14px]">
              <img
                class="h-full w-full object-cover"
                src="<?= $homeImageRoot ?>/serviceImg3.png"
                alt="Elegant wedding service detail"


                >
            </figure>
          </div>
        </div>
      </section>

      <section id="services" class="relative z-10 min-h-[620px] w-full bg-[#F5E8D9] px-4 py-24" aria-label="Most Popular Packages">
        <div class="mx-auto mb-5 max-w-[1240px]">
          <h2 class="font-serif text-center text-[clamp(34px,4.1vw,62px)] font-semibold leading-[1] text-[#211d1a]">Most Popular Packages</h2>
        </div>
        <div class="mx-auto mb-8 flex max-w-[1400px] justify-end">

        </div>
        <div class="mx-auto flex h-[72vh] min-h-[520px] w-full max-w-[1400px] flex-col overflow-hidden md:flex-row">
          <a href="<?= URLROOT ?>/customerServices/packages" class="group relative min-h-[170px] flex-1 overflow-hidden rounded-[18px] border-white/15 transition-[flex] duration-700 ease-[cubic-bezier(0.25,1,0.3,1)] md:h-full md:rounded-[24px] md:hover:flex-[4]">
            <div class="absolute inset-0 bg-cover bg-center opacity-60 transition duration-700 group-hover:scale-105 group-hover:opacity-100" style="background-image: url('<?= $homeImageRoot ?>/serviceImg1.png');"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent"></div>
            <div class="absolute left-6 top-1/4 z-10 max-w-sm translate-y-0 text-white opacity-100 transition duration-500 md:left-10 md:translate-y-6 md:opacity-0 md:group-hover:translate-y-0 md:group-hover:opacity-100">
              <span class="text-xs font-semibold uppercase tracking-[0.35em] text-white/75">Most Popular Package</span>
              <h2 class="font-serif-elegant mt-2 text-5xl uppercase leading-none md:text-6xl">Golden Vow</h2>
              <p class="mt-3 text-xs font-semibold uppercase tracking-wider text-white/80">A refined ceremony package with venue styling, florals, and graceful coordination.</p>
              <span data-explore-button class="explore-badge mt-5 inline-flex items-center rounded-full bg-transparent px-0.5 py-0.5 text-[#4A342F] shadow-none">
                <span class="explore-badge-icon grid rounded-full bg-[#8A6A58] text-white shadow-[0_12px_24px_rgba(74,52,47,0.18)]">
                  <i data-lucide="arrow-right" class="h-4 w-4"></i>
                </span>
                <span class="explore-badge-label ml-0 overflow-hidden whitespace-nowrap text-[11px] font-extrabold uppercase tracking-[0.24em]">Explore</span>
              </span>
            </div>
            <h3 class="font-serif-elegant absolute bottom-6 left-6 z-10 text-3xl uppercase text-white md:left-8">Golden Vow</h3>
          </a>

          <a href="<?= URLROOT ?>/customerServices/packages" class="group relative min-h-[170px] flex-1 overflow-hidden rounded-[18px] border-white/15 transition-[flex] duration-700 ease-[cubic-bezier(0.25,1,0.3,1)] md:h-full md:rounded-[24px] md:border-l md:hover:flex-[4]">
            <div class="absolute inset-0 bg-cover bg-center opacity-60 transition duration-700 group-hover:scale-105 group-hover:opacity-100" style="background-image: url('<?= $homeImageRoot ?>/serviceImg2.png');"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent"></div>
            <div class="absolute left-6 top-1/4 z-10 max-w-sm translate-y-0 text-white opacity-100 transition duration-500 md:left-10 md:translate-y-6 md:opacity-0 md:group-hover:translate-y-0 md:group-hover:opacity-100">
              <span class="text-xs font-semibold uppercase tracking-[0.35em] text-white/75">Most Popular Package</span>
              <h2 class="font-serif-elegant mt-2 text-5xl uppercase leading-none md:text-6xl">Classic Bloom</h2>
              <p class="mt-3 text-xs font-semibold uppercase tracking-wider text-white/80">A romantic floral-focused package for couples who want soft, timeless detail.</p>
              <span data-explore-button class="explore-badge mt-5 inline-flex items-center rounded-full bg-transparent px-0.5 py-0.5 text-[#4A342F] shadow-none">
                <span class="explore-badge-icon grid rounded-full bg-[#8A6A58] text-white shadow-[0_12px_24px_rgba(74,52,47,0.18)]">
                  <i data-lucide="arrow-right" class="h-4 w-4"></i>
                </span>
                <span class="explore-badge-label ml-0 overflow-hidden whitespace-nowrap text-[11px] font-extrabold uppercase tracking-[0.24em]">Explore</span>
              </span>
            </div>
            <h3 class="font-serif-elegant absolute bottom-6 left-6 z-10 text-3xl uppercase text-white md:left-8">Classic Bloom</h3>
          </a>

          <a href="<?= URLROOT ?>/customerServices/packages" class="group relative min-h-[170px] flex-1 overflow-hidden rounded-[18px] border-white/15 transition-[flex] duration-700 ease-[cubic-bezier(0.25,1,0.3,1)] md:h-full md:rounded-[24px] md:border-l md:hover:flex-[4]">
            <div class="absolute inset-0 bg-cover bg-center opacity-60 transition duration-700 group-hover:scale-105 group-hover:opacity-100" style="background-image: url('<?= $homeImageRoot ?>/serviceImg3.png');"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent"></div>
            <div class="absolute left-6 top-1/4 z-10 max-w-sm translate-y-0 text-white opacity-100 transition duration-500 md:left-10 md:translate-y-6 md:opacity-0 md:group-hover:translate-y-0 md:group-hover:opacity-100">
              <span class="text-xs font-semibold uppercase tracking-[0.35em] text-white/75">Most Popular Package</span>
              <h2 class="font-serif-elegant mt-2 text-5xl uppercase leading-none md:text-6xl">Forever Suite</h2>
              <p class="mt-3 text-xs font-semibold uppercase tracking-wider text-white/80">A complete planning package covering styling, vendors, timeline, and final details.</p>
              <span data-explore-button class="explore-badge mt-5 inline-flex items-center rounded-full bg-transparent px-0.5 py-0.5 text-[#4A342F] shadow-none">
                <span class="explore-badge-icon grid rounded-full bg-[#8A6A58] text-white shadow-[0_12px_24px_rgba(74,52,47,0.18)]">
                  <i data-lucide="arrow-right" class="h-4 w-4"></i>
                </span>
                <span class="explore-badge-label ml-0 overflow-hidden whitespace-nowrap text-[11px] font-extrabold uppercase tracking-[0.24em]">Explore</span>
              </span>
            </div>
            <h3 class="font-serif-elegant absolute bottom-6 left-6 z-10 text-3xl uppercase text-white md:left-8">Forever Suite</h3>
          </a>
        </div>
      </section>

      <section id="how-it-works" aria-label="How It Works">
        <div class="hiw-sticky">
          <div class="hiw-section-title">
            <p>Golden Promise Journey</p>
            <h2>How It Works</h2>
          </div>

          <div class="hiw-canvas" id="howItWorksCanvas">
            <svg class="hiw-lines-layer" viewBox="0 0 3560 1000" aria-hidden="true">
              <path class="hiw-line-path" data-start="0.04" data-end="0.32" d="M 376 500 C 526 500, 586 360, 706 360" />
              <path class="hiw-line-path" data-start="0.04" data-end="0.4" d="M 376 500 C 526 500, 586 640, 706 640" />
              <path class="hiw-line-path" data-start="0.36" data-end="0.64" d="M 1026 360 C 1176 360, 1246 500, 1376 500" />
              <path class="hiw-line-path" data-start="0.36" data-end="0.64" d="M 1026 640 C 1176 640, 1246 500, 1376 500" />
              <path class="hiw-line-path" data-start="0.6" data-end="0.78" d="M 1696 500 C 1816 500, 1886 500, 2016 500" />
              <path class="hiw-line-path" data-start="0.74" data-end="0.9" d="M 2336 500 C 2456 500, 2526 500, 2656 500" />
            </svg>

            <article class="hiw-card" style="left: 216px; top: 500px;" data-reveal-at="0" tabindex="0">
              <div class="hiw-card-media">
                <img src="<?= $homeImageRoot ?>/browse.png" alt="Browsing wedding packages">
                <h3 class="hiw-card-title">Browse Packages</h3>
              </div>
              <p class="hiw-card-copy">Choose the ceremony, styling, and celebration experience that feels like you.</p>
            </article>

            <article class="hiw-card" style="left: 866px; top: 360px;" data-reveal-at="0.24" tabindex="0">
              <div class="hiw-card-media">
                <img src="<?= $homeImageRoot ?>/service2.png" alt="Couple details and bouquet">
                <h3 class="hiw-card-title">Choosing Packages</h3>
              </div>
              <p class="hiw-card-copy">Select the package that fits your celebration style.</p>
            </article>

            <article class="hiw-card" style="left: 866px; top: 640px;" data-reveal-at="0.34" tabindex="0">
              <div class="hiw-card-media">
                <img src="<?= $homeImageRoot ?>/service1.jpg" alt="Wedding professionals at work">
                <h3 class="hiw-card-title">Choosing Services</h3>
              </div>
              <p class="hiw-card-copy">Add the services and details your wedding needs.</p>
            </article>

            <article class="hiw-card" style="left: 1536px; top: 500px;" data-reveal-at="0.58" tabindex="0">
              <div class="hiw-card-media">
                <img src="<?= $homeImageRoot ?>/whyChooseUs2.png" alt="Hands joined with rings">
                <h3 class="hiw-card-title">Deposit</h3>
              </div>
              <p class="hiw-card-copy">Secure your booking with the required deposit.</p>
            </article>

            <article class="hiw-card" style="left: 2176px; top: 500px;" data-reveal-at="0.72" tabindex="0">
              <div class="hiw-card-media">
                <img src="<?= $homeImageRoot ?>/deposit.jpg" alt="Payment cards being exchanged">
                <h3 class="hiw-card-title">Final Payment</h3>
              </div>
              <p class="hiw-card-copy">Complete the remaining balance before the event.</p>
            </article>

            <article class="hiw-card" style="left: 2816px; top: 500px;" data-reveal-at="0.84" tabindex="0">
              <div class="hiw-card-media">
                <img src="<?= $homeImageRoot ?>/hiddenIntro.png" alt="Wedding day celebration collage">
                <h3 class="hiw-card-title">Wedding Day</h3>
              </div>
              <p class="hiw-card-copy">Enjoy your beautifully prepared wedding day.</p>
            </article>
          </div>
        </div>
      </section>

      <section id="gallery" aria-label="Wedding Gallery">
        <div class="gp-gallery-sticky">
          <div class="gp-gallery-heading">
            <p>Golden Promise Gallery</p>
            <h2>Moments<br>in Bloom.</h2>
          </div>

          <div class="gp-gallery-grid" aria-hidden="true">
            <div class="gp-gallery-layer">
              <div class="gp-gallery-photo"><img src="<?= $homeImageRoot ?>/gallery1.png" alt=""></div>
              <div class="gp-gallery-photo"><img src="<?= $homeImageRoot ?>/gallery2.png" alt=""></div>
              <div class="gp-gallery-photo"><img src="<?= $homeImageRoot ?>/gallery3.png" alt=""></div>
              <div class="gp-gallery-photo"><img src="<?= $homeImageRoot ?>/gallery4.png" alt=""></div>
              <div class="gp-gallery-photo"><img src="<?= $homeImageRoot ?>/gallery5.png" alt=""></div>
              <div class="gp-gallery-photo"><img src="<?= $homeImageRoot ?>/galleryImg.png" alt=""></div>
            </div>

            <div class="gp-gallery-layer">
              <div class="gp-gallery-photo"><img src="<?= $homeImageRoot ?>/gallery2.png" alt=""></div>
              <div class="gp-gallery-photo"><img src="<?= $homeImageRoot ?>/gallery3.png" alt=""></div>
              <div class="gp-gallery-photo"><img src="<?= $homeImageRoot ?>/galleryImg.png" alt=""></div>
              <div class="gp-gallery-photo"><img src="<?= $homeImageRoot ?>/gallery1.png" alt=""></div>
              <div class="gp-gallery-photo"><img src="<?= $homeImageRoot ?>/gallery4.png" alt=""></div>
              <div class="gp-gallery-photo"><img src="<?= $homeImageRoot ?>/gallery5.png" alt=""></div>
            </div>

            <div class="gp-gallery-layer">
              <div class="gp-gallery-photo"><img src="<?= $homeImageRoot ?>/garlleryMain.png" alt=""></div>
              <div class="gp-gallery-photo"><img src="<?= $homeImageRoot ?>/gallery5.png" alt=""></div>
            </div>

            <div class="gp-gallery-scaler gp-gallery-photo">
              <img src="<?= $homeImageRoot ?>/garlleryMain.png" alt="Golden Promise wedding gallery highlight">
            </div>
          </div>
        </div>

      </section>

      <section id="reviews" aria-label="Client Reviews">
        <div class="review-cloud">
          <article class="review-card review-card-wide review-pos-1">
            <h3>Victoria Linton</h3>
            <div class="review-stars" aria-label="5 out of 5 stars">★★★★★</div>
            <p>From our first booking to the final timeline, everything felt calm and beautifully handled.</p>
          </article>

          <article class="review-card review-card-pill review-pos-2">
            <div class="review-avatar" aria-hidden="true">DW</div>
            <div>
              <blockquote>“Every detail was thoughtful, graceful, and exactly what we imagined.”</blockquote>
              <p class="mt-3 text-xs font-extrabold uppercase tracking-[0.18em] text-[#765A46]">Dmitri Woodhouse</p>
              <div class="review-stars mt-2" aria-label="5 out of 5 stars">★★★★★</div>
            </div>
          </article>

          <article class="review-card review-card-note review-pos-3">
            <h3>Top-notch!</h3>
            <p>Elegant planning, warm communication, and a wedding day that felt effortless.</p>
            <div class="review-stars mt-5" aria-label="5 out of 5 stars">★★★★★</div>
            <p class="mt-5 font-bold text-[#4A342F]">Hindley M.</p>
          </article>

          <article class="review-card review-card-speech review-pos-4">
            <h3>Testimonial</h3>
            <blockquote>“The package, florals, and schedule came together with such quiet luxury.”</blockquote>
            <p class="mt-4 font-bold text-[#4A342F]">@CatherineDoe</p>
          </article>

          <article class="review-card review-card-feature">
            <div class="review-avatar" aria-hidden="true">FB</div>
            <h3>Excellent Job!</h3>
            <div class="review-stars mb-4" aria-label="5 out of 5 stars">★★★★★</div>
            <blockquote>“A seamless experience from deposit to wedding day. Our celebration felt personal, polished, and full of heart.”</blockquote>
            <p class="signature">Faye Bina</p>
          </article>

          <article class="review-card review-card-wide review-pos-5">
            <p class="mb-3 text-lg font-bold text-[#4A342F]">Client Review</p>
            <blockquote>“Golden Promise made our ceremony feel refined without ever feeling stressful.”</blockquote>
            <p class="mt-5 text-xs font-extrabold uppercase tracking-[0.18em] text-[#765A46]">Read more →</p>
          </article>

          <article class="review-card review-card-pill review-pos-6">
            <div class="review-avatar" aria-hidden="true">NV</div>
            <div>
              <h3>Nelly Vane</h3>
              <p>Venue styling, timing, and communication were all handled with care.</p>
              <div class="review-stars mt-2" aria-label="5 out of 5 stars">★★★★★</div>
            </div>
          </article>

          <article class="review-card review-card-speech review-pos-7">
            <blockquote>“The team gave us room to enjoy the day while every detail stayed on track.”</blockquote>
            <p class="mt-4 text-right font-bold text-[#4A342F]">Jane M.</p>
          </article>

          <article class="review-card review-card-note review-pos-8">
            <h3>Recommended</h3>
            <div class="review-stars" aria-label="5 out of 5 stars">★★★★★</div>
            <p class="mt-4">The perfect balance of luxury, clarity, and kindness.</p>
          </article>
        </div>
      </section>

      <footer class="site-footer" id="contact" aria-label="Website Footer">
        <div class="footer-inner">

          <!-- Footer nav -->
          <nav class="footer-nav" aria-label="Footer navigation">
            <a href="#our-services">Services</a>
            <a href="#top">Our Story</a>
            <a href="#reviews">Review</a>
          </nav>

          <!-- Social icons with hover animation -->
          <div class="footer-contact-card">
            <h2 id="footerText">GPromise Wedding</h2>
            <p id="footerLabel">Facebook</p>
            <div class="footer-icons">
              <button class="contact-btn active"
                      data-text="GPromise Wedding"
                      data-label="Facebook">
                <i class="fa-brands fa-facebook-f"></i>
              </button>
              <button class="contact-btn"
                      data-text="@gpromise_wedding"
                      data-label="Instagram">
                <i class="fa-brands fa-instagram" style="font-size:18px;"></i>
              </button>
              <button class="contact-btn"
                      data-text="GPromise.com"
                      data-label="Website">
                <i class="fa-solid fa-globe"></i>
              </button>
            </div>
          </div>

          <!-- Copyright -->
          <p class="footer-copy">
            &copy; <span id="footer-year"></span> GPromise Wedding. All rights reserved.
          </p>

        </div>
      </footer>

    </div>
  </main>

  <script>
    const header = document.querySelector(".site-header");

      window.addEventListener("scroll", () => {
          if (window.scrollY > 100) {
              header.classList.add("visible");
          } else {
              header.classList.remove("visible");
          }
      });
    const introScroll = document.getElementById("introScroll");
    const introSection = document.getElementById("introSection");
    const introImage = document.getElementById("introImage");
    const magicReveal = document.getElementById("magicReveal");
    const siteHeader = document.querySelector(".site-header");
    const heroSection = document.querySelector(".hero");
    const ourServicesSection = document.getElementById("our-services");
    const floatingServices = document.getElementById("floatingServices");
    const howItWorksSection = document.getElementById("how-it-works");
    const howItWorksCanvas = document.getElementById("howItWorksCanvas");
    const howItWorksLines = document.querySelectorAll(".hiw-line-path");
    const howItWorksCards = document.querySelectorAll(".hiw-card");
    const gallerySection = document.getElementById("gallery");
    const reviewsSection = document.getElementById("reviews");
    const navbar = document.querySelector(".navbar");
    const howItWorksMotion = {
      currentX: 0,
      targetX: 0,
      frame: null,
      updateFrame: null,
      layoutFrame: null,
      sectionTop: 0,
      scrollableDistance: 1,
      canvasTravel: 0
    };

    function clamp(value, min, max) {
      return Math.max(min, Math.min(max, value));
    }

    function lerp(start, end, amount) {
      return start + (end - start) * amount;
    }

    function getScrollPositionInIntro(element) {
      const scrollRect = introScroll.getBoundingClientRect();
      const elementRect = element.getBoundingClientRect();
      return elementRect.top - scrollRect.top + introScroll.scrollTop;
    }

    function updateIntroImage() {
      const totalScroll = window.innerHeight;
      const progress = totalScroll > 0
        ? clamp(introScroll.scrollTop / totalScroll, 0, 1)
        : 0;

      const scale = 1 + progress * 3.5;
      const fadeProgress = clamp((progress - 0.45) / 0.55, 0, 1);
      const opacity = 1 - fadeProgress;
      if (progress < 1) {
        introSection.style.display = "";
      }

      introImage.style.transform = `scale(${scale})`;
      introImage.style.opacity = opacity.toFixed(3);
      introSection.style.display = progress >= 1 ? "none" : "";
      updateHeaderVisibility();
    }


    function updateHeaderVisibility() {
      const heroTop = getScrollPositionInIntro(heroSection);
      siteHeader.classList.toggle("visible", introScroll.scrollTop >= heroTop);
    }

    introScroll.addEventListener("scroll", () => {
      requestAnimationFrame(updateIntroImage);
      requestAnimationFrame(updateHeaderVisibility);
    }, { passive: true });

    window.addEventListener("resize", () => {
      updateIntroImage();
      updateHeaderVisibility();
    });
    updateIntroImage();

    function updateOurServicesImages() {
      const rect = ourServicesSection.getBoundingClientRect();
      const triggerPoint = window.innerHeight * 0.72;
      const isVisible = rect.top < triggerPoint && rect.bottom > window.innerHeight * 0.18;

      if (isVisible) {
        ourServicesSection.classList.add("images-visible");
      } else {
        ourServicesSection.classList.remove("images-visible");
      }
    }

    introScroll.addEventListener("scroll", () => {
      requestAnimationFrame(updateOurServicesImages);
    }, { passive: true });

    window.addEventListener("resize", updateOurServicesImages);
    updateOurServicesImages();

    function measureHowItWorksFlow() {
      if (!howItWorksCanvas || !howItWorksSection) return;

      if (window.matchMedia("(max-width: 767px)").matches) {
        howItWorksSection.style.minHeight = "";
        howItWorksCanvas.style.transform = "none";
        howItWorksMotion.currentX = 0;
        howItWorksMotion.targetX = 0;
        howItWorksLines.forEach((path) => {
          path.style.strokeDashoffset = "0";
        });
        howItWorksCards.forEach((card) => card.classList.add("visible"));
        return;
      }

      const sectionTop = getScrollPositionInIntro(howItWorksSection);
      const contentRight = Array.from(howItWorksCards).reduce((right, card) => {
        return Math.max(right, card.offsetLeft + card.offsetWidth / 2);
      }, 0);
      const canvasLeft = howItWorksCanvas.offsetLeft;
      const canvasTravel = Math.max(canvasLeft + contentRight - window.innerWidth + 24, 0);
      howItWorksSection.style.minHeight = `${window.innerHeight + canvasTravel}px`;
      const sectionHeight = howItWorksSection.offsetHeight;
      const scrollableDistance = Math.max(sectionHeight - window.innerHeight, 1);

      howItWorksMotion.sectionTop = sectionTop;
      howItWorksMotion.scrollableDistance = scrollableDistance;
      howItWorksMotion.canvasTravel = canvasTravel;
      updateHowItWorksFlow();
    }

    function scheduleHowItWorksMeasure() {
      if (howItWorksMotion.layoutFrame) return;
      howItWorksMotion.layoutFrame = requestAnimationFrame(() => {
        howItWorksMotion.layoutFrame = null;
        measureHowItWorksFlow();
      });
    }

    function updateHowItWorksFlow() {
      if (!howItWorksCanvas || !howItWorksSection) return;

      if (window.matchMedia("(max-width: 767px)").matches) {
        return;
      }

      const progress = clamp(
        (introScroll.scrollTop - howItWorksMotion.sectionTop) / howItWorksMotion.scrollableDistance,
        0,
        1
      );

      howItWorksMotion.targetX = -howItWorksMotion.canvasTravel * progress;
      startHowItWorksMotion();

      howItWorksLines.forEach((path) => {
        const length = Number(path.dataset.length || 0);
        const lineStart = Number(path.dataset.start || 0);
        const lineEnd = Number(path.dataset.end || 1);
        const rawLineProgress = clamp((progress - lineStart) / (lineEnd - lineStart), 0, 1);
        const lineProgress = rawLineProgress * rawLineProgress * (3 - 2 * rawLineProgress);
        path.style.strokeDashoffset = String(length * (1 - lineProgress));
      });

    }

    function renderHowItWorksMotion() {
      howItWorksMotion.currentX = lerp(howItWorksMotion.currentX, howItWorksMotion.targetX, 0.18);

      if (Math.abs(howItWorksMotion.targetX - howItWorksMotion.currentX) < 0.35) {
        howItWorksMotion.currentX = howItWorksMotion.targetX;
      }

      howItWorksCanvas.style.transform = `translate3d(${howItWorksMotion.currentX}px, -50%, 0)`;

      if (howItWorksMotion.currentX !== howItWorksMotion.targetX) {
        howItWorksMotion.frame = requestAnimationFrame(renderHowItWorksMotion);
        return;
      }

      howItWorksMotion.frame = null;
    }

    function startHowItWorksMotion() {
      if (!howItWorksMotion.frame) {
        howItWorksMotion.frame = requestAnimationFrame(renderHowItWorksMotion);
      }
    }

    function scheduleHowItWorksFlow() {
      if (howItWorksMotion.updateFrame) return;
      howItWorksMotion.updateFrame = requestAnimationFrame(() => {
        howItWorksMotion.updateFrame = null;
        updateHowItWorksFlow();
      });
    }

    introScroll.addEventListener("scroll", () => {
      scheduleHowItWorksFlow();
    }, { passive: true });

    window.addEventListener("resize", scheduleHowItWorksMeasure);
    howItWorksLines.forEach((path) => {
      const pathLength = path.getTotalLength();
      path.dataset.length = String(pathLength);
      path.style.strokeDasharray = String(pathLength);
      path.style.strokeDashoffset = String(pathLength);
    });
    measureHowItWorksFlow();

    function updateGalleryScroll() {
      if (!gallerySection) return;

      const galleryGrid = gallerySection.querySelector(".gp-gallery-grid");
      const scaler = gallerySection.querySelector(".gp-gallery-scaler");
      const scalerImage = gallerySection.querySelector(".gp-gallery-scaler img");
      const layers = gallerySection.querySelectorAll(".gp-gallery-layer");
      if (!galleryGrid || !scaler || !scalerImage || !layers.length) return;

      const start = getScrollPositionInIntro(gallerySection);
      const travel = Math.max(gallerySection.offsetHeight - window.innerHeight, 1);
      const progress = clamp((introScroll.scrollTop - start) / travel, 0, 1);
      const easeProgress = progress * progress * (3 - 2 * progress);
      const scalerRect = scaler.getBoundingClientRect();
      const targetWidth = Math.max(scalerRect.width, 120);
      const targetHeight = Math.max(scalerRect.height, 160);

      scalerImage.style.width = `${lerp(window.innerWidth, targetWidth, easeProgress)}px`;
      scalerImage.style.height = `${lerp(window.innerHeight, targetHeight, easeProgress)}px`;

      layers.forEach((layer, index) => {
        const layerProgress = clamp((progress - 0.18 - index * 0.08) / 0.58, 0, 1);
        const easedLayer = layerProgress * layerProgress * (3 - 2 * layerProgress);
        layer.style.opacity = easedLayer.toFixed(3);
        layer.style.transform = `scale(${lerp(0.72, 1, easedLayer).toFixed(3)})`;
      });
    }

    introScroll.addEventListener("scroll", () => {
      requestAnimationFrame(updateGalleryScroll);
    }, { passive: true });

    window.addEventListener("resize", updateGalleryScroll);
    updateGalleryScroll();

    const galleryGrid = gallerySection?.querySelector(".gp-gallery-grid");
    const galleryPhotos = galleryGrid?.querySelectorAll(".gp-gallery-photo");

    galleryPhotos?.forEach((photo) => {
      photo.addEventListener("mouseenter", () => {
        galleryGrid.classList.add("gallery-photo-hovering");
        galleryPhotos.forEach((item) => item.classList.toggle("active", item === photo));
      });

      photo.addEventListener("mouseleave", () => {
        galleryGrid.classList.remove("gallery-photo-hovering");
        photo.classList.remove("active");
      });
    });

    const reviewCards = reviewsSection?.querySelectorAll(".review-card");

    function setupReviewReveal() {
      if (!reviewsSection || !reviewCards?.length) return;

      const revealOrder = Array.from(reviewCards)
        .map((card) => ({ card, sort: Math.random() }))
        .sort((a, b) => a.sort - b.sort);

      revealOrder.forEach(({ card }, index) => {
        const enterX = `${Math.round((Math.random() - 0.5) * 150)}px`;
        const enterY = `${Math.round(36 + Math.random() * 84)}px`;
        const rotate = `${((Math.random() - 0.5) * 10).toFixed(2)}deg`;

        card.style.setProperty("--review-enter-delay", `${index * 95}ms`);
        card.style.setProperty("--review-enter-x", enterX);
        card.style.setProperty("--review-enter-y", enterY);
        card.style.setProperty("--review-enter-rotate", rotate);
      });
    }

    function revealReviewsWhenReached() {
      if (!reviewsSection || reviewsSection.classList.contains("reviews-visible")) return;

      const rect = reviewsSection.getBoundingClientRect();
      const triggerPoint = window.innerHeight * 0.72;

      if (rect.top < triggerPoint && rect.bottom > window.innerHeight * 0.18) {
        reviewsSection.classList.add("reviews-visible");
      }
    }

    setupReviewReveal();
    introScroll.addEventListener("scroll", revealReviewsWhenReached, { passive: true });
    window.addEventListener("resize", revealReviewsWhenReached);
    revealReviewsWhenReached();

    const revealMask = {
      x: window.innerWidth / 2,
      y: window.innerHeight / 2,
      targetX: window.innerWidth / 2,
      targetY: window.innerHeight / 2,
      width: 0,
      height: 0,
      targetWidth: 0,
      targetHeight: 0,
      frame: null
    };

    function updateRevealMask() {
      revealMask.x = lerp(revealMask.x, revealMask.targetX, 0.07);
      revealMask.y = lerp(revealMask.y, revealMask.targetY, 0.07);
      revealMask.width = lerp(revealMask.width, revealMask.targetWidth, 0.07);
      revealMask.height = lerp(revealMask.height, revealMask.targetHeight, 0.07);

      if (Math.abs(revealMask.targetX - revealMask.x) < 0.4) {
        revealMask.x = revealMask.targetX;
      }

      if (Math.abs(revealMask.targetY - revealMask.y) < 0.4) {
        revealMask.y = revealMask.targetY;
      }

      if (Math.abs(revealMask.targetWidth - revealMask.width) < 0.4) {
        revealMask.width = revealMask.targetWidth;
      }

      if (Math.abs(revealMask.targetHeight - revealMask.height) < 0.4) {
        revealMask.height = revealMask.targetHeight;
      }

      magicReveal.style.setProperty("--mask-x", `${revealMask.x}px`);
      magicReveal.style.setProperty("--mask-y", `${revealMask.y}px`);
      magicReveal.style.setProperty("--mask-width", `${revealMask.width}px`);
      magicReveal.style.setProperty("--mask-height", `${revealMask.height}px`);

      if (
        revealMask.x !== revealMask.targetX ||
        revealMask.y !== revealMask.targetY ||
        revealMask.width !== revealMask.targetWidth ||
        revealMask.height !== revealMask.targetHeight
      ) {
        revealMask.frame = requestAnimationFrame(updateRevealMask);
        return;
      }

      revealMask.frame = null;
    }

    function setRevealMaskSize(width, height) {
      revealMask.targetWidth = width;
      revealMask.targetHeight = height;

      if (!revealMask.frame) {
        revealMask.frame = requestAnimationFrame(updateRevealMask);
      }
    }

    function setRevealMaskPosition(x, y) {
      revealMask.targetX = x;
      revealMask.targetY = y;

      if (!revealMask.frame) {
        revealMask.frame = requestAnimationFrame(updateRevealMask);
      }
    }

    // Lerp the feathered photo mask toward the cursor for a soft lagging effect.
    window.addEventListener("mousemove", (event) => {
      setRevealMaskPosition(event.clientX, event.clientY);
      setRevealMaskSize(205, 176);
    });

    // Ease the reveal closed when the cursor leaves the section.
    document.addEventListener("mouseleave", () => {
      setRevealMaskSize(0, 0);
    });

    const floatingServiceItems = [
      {
        label: "Venue",
        color: "#7a4f3a",
        soft: "rgba(122, 79, 58, 0.14)",
        second: "#b58a62"
      },
      {
        label: "Bridal",
        color: "#8a5b42",
        soft: "rgba(138, 91, 66, 0.14)",
        second: "#c09a6b"
      },
      {
        label: "Media",
        color: "#6f4a36",
        soft: "rgba(111, 74, 54, 0.14)",
        second: "#ad8057"
      },
      {
        label: "Catering",
        color: "#9a6441",
        soft: "rgba(154, 100, 65, 0.15)",
        second: "#c89a62"
      },
      {
        label: "Invites",
        color: "#76513d",
        soft: "rgba(118, 81, 61, 0.14)",
        second: "#b98c64"
      },

    ];

    floatingServiceItems.forEach((service, index) => {
      const floatButton = document.createElement("div");
      floatButton.className = "float-button";
      floatButton.dataset.index = index;
      floatButton.innerHTML =
        `<div class="float-button-shell" style="--float-duration:${3.5 + index * 0.25}s; --icon-color:${service.color}; --icon-soft:${service.soft}; --icon-second:${service.second};" aria-label="${service.label}">` +
        `<span class="float-button-label">${service.label}</span></div>`;
      floatingServices.appendChild(floatButton);
    });

    if (window.lucide) {
      lucide.createIcons();
    }

    const footerYear = document.getElementById("footer-year");
    if (footerYear) footerYear.textContent = new Date().getFullYear();

    const floatingButtons = Array.from(document.querySelectorAll(".float-button"));
    const floatingButtonStates = {};
    floatingButtons.forEach((button) => {
      floatingButtonStates[button.dataset.index] = { smoothX: 0, smoothY: 0 };
    });

    let floatingMouse = { x: null, y: null };
    let floatingProgress = 0;
    let floatingLastScrollTop = introScroll.scrollTop;
    let floatingLastTime = Date.now();

    window.addEventListener("mousemove", (event) => {
      floatingMouse.x = event.clientX;
      floatingMouse.y = event.clientY;
    });

    document.addEventListener("mouseleave", () => {
      floatingMouse.x = null;
      floatingMouse.y = null;
    });

    function updateFloatingButtons() {
      const now = Date.now();
      const scrollTop = introScroll.scrollTop;
      const dy = Math.abs(scrollTop - floatingLastScrollTop);
      const dt = Math.max(now - floatingLastTime, 16);
      const speed = clamp(dy / dt, 0, 3);
      floatingLastScrollTop = scrollTop;
      floatingLastTime = now;

      const heroTop = getScrollPositionInIntro(heroSection);
      const heroBottom = heroTop + heroSection.offsetHeight;
      const floatTravel = heroSection.offsetHeight;
      const targetProgress = clamp((scrollTop - heroTop) / floatTravel, 0, 1);
      const isHeroActive = scrollTop >= heroTop && scrollTop < heroBottom;
      const spring = clamp(0.08 + speed * 0.04, 0.08, 0.22);

      floatingProgress += (targetProgress - floatingProgress) * spring;
      if (targetProgress === 1 && Math.abs(1 - floatingProgress) < 0.015) {
        floatingProgress = 1;
      }

      floatingServices.classList.toggle("visible", isHeroActive && floatingProgress < 0.94);

      const arcSideInset = clamp(window.innerWidth * 0.11, 42, 165);
      const arcBaseY = clamp(window.innerHeight * 0.45, 295, 430);
      const arcLift = clamp(window.innerHeight * 0.26, 150, 245);
      const servicesButton = document.getElementById("servicesNavLink");
      const navRect = navbar.getBoundingClientRect();
      const dockRect = servicesButton.getBoundingClientRect();
      const canDock = dockRect.width > 4 && dockRect.height > 4;

      floatingButtons.forEach((button) => {
        const index = Number(button.dataset.index);
        const arcProgress = floatingButtons.length > 1
          ? index / (floatingButtons.length - 1)
          : 0.5;
        const startX = arcSideInset + (window.innerWidth - arcSideInset * 2) * arcProgress;
        const startY = arcBaseY - Math.sin(arcProgress * Math.PI) * arcLift;
        const fallbackStart = navRect.left + navRect.width * 0.22;
        const fallbackEnd = navRect.left + navRect.width * 0.46;
        const fallbackStep = (fallbackEnd - fallbackStart) / Math.max(floatingServiceItems.length - 1, 1);
        const dockX = canDock ? dockRect.left + dockRect.width / 2 : fallbackStart + fallbackStep * index;
        const dockY = canDock ? dockRect.top + dockRect.height / 2 : navRect.top + navRect.height / 2;

        let x = startX + (dockX - startX) * floatingProgress;
        let y = startY + (dockY - startY) * floatingProgress;
        const state = floatingButtonStates[index];

        if (floatingMouse.x != null) {
          const dist = Math.hypot(floatingMouse.x - x, floatingMouse.y - y);
          const rawInfluence = Math.max(0, (320 - dist) / 320);
          const influence = rawInfluence * rawInfluence * (3 - 2 * rawInfluence);

          if (influence > 0.001) {
            const targetOffsetX = ((floatingMouse.x - x) / (dist || 1)) * influence * 22;
            const targetOffsetY = ((floatingMouse.y - y) / (dist || 1)) * influence * 16;
            state.smoothX += (targetOffsetX - state.smoothX) * 0.12;
            state.smoothY += (targetOffsetY - state.smoothY) * 0.12;
          }
        }

        x += state.smoothX;
        y += state.smoothY;

        const scale = clamp(1 - floatingProgress * 0.32, 0.18, 1);
        const opacity = floatingProgress > 0.9 ? 1 - (floatingProgress - 0.9) / 0.1 : 1;

        button.style.transform = `translate(${x.toFixed(1)}px, ${y.toFixed(1)}px) translate(-50%, -50%) scale(${scale.toFixed(3)})`;
        button.style.opacity = clamp(opacity, 0, 1).toFixed(3);
      });

      requestAnimationFrame(updateFloatingButtons);
    }

    requestAnimationFrame(updateFloatingButtons);

    const menuButton = document.getElementById("menuButton");
    const mobileMenu = document.getElementById("mobileMenu");

    function scrollToHash(hash, behavior = "smooth") {
      const target = document.querySelector(hash);
      if (!target) return;
      const navOffset = hash === "#contact" ? (navbar?.getBoundingClientRect().height || 0) + 12 : 0;

      introScroll.scrollTo({
        top: Math.max(0, getScrollPositionInIntro(target) - navOffset),
        behavior
      });
    }

    menuButton.addEventListener("click", () => {
      const isOpen = mobileMenu.classList.toggle("open");
      menuButton.setAttribute("aria-expanded", String(isOpen));
    });

    document.querySelectorAll('a[href^="#"]').forEach((link) => {
      link.addEventListener("click", (event) => {
        const hash = link.getAttribute("href");
        if (!hash || hash === "#") return;

        const target = document.querySelector(hash);
        if (!target) return;

        event.preventDefault();
        scrollToHash(hash);
        mobileMenu.classList.remove("open");
        menuButton.setAttribute("aria-expanded", "false");
      });
    });

    window.addEventListener("load", () => {
      if (!window.location.hash) return;

      requestAnimationFrame(() => {
        scrollToHash(window.location.hash, "auto");
      });
    });
    const heroBrandTitle = document.getElementById("hero-gp");
const serviceGP = document.getElementById("service-gp");

function animateBrandTransition() {
  if (!heroBrandTitle || !serviceGP) return;

  const heroRect = heroBrandTitle.getBoundingClientRect();
  const targetRect = serviceGP.getBoundingClientRect();

  const triggerStart = window.innerHeight * 0.2;
  const triggerEnd = window.innerHeight * 0.8;

  const progress = Math.max(
    0,
    Math.min(
      1,
      (triggerEnd - targetRect.top) /
      (triggerEnd - triggerStart)
    )
  );

  const dx =
    targetRect.left +
    targetRect.width / 2 -
    (heroRect.left + heroRect.width / 2);

  const dy =
    targetRect.top +
    targetRect.height / 2 -
    (heroRect.top + heroRect.height / 2);

  heroBrandTitle.style.transform =
    `translate(${dx * progress}px, ${dy * progress}px)
     scale(${1 - progress * 0.4})`;

  heroBrandTitle.style.opacity = 1 - progress;
}

introScroll.addEventListener(
  "scroll",
  () => requestAnimationFrame(animateBrandTransition),
  { passive: true }
);

animateBrandTransition();

const buttons = document.querySelectorAll(".contact-btn");
const text = document.getElementById("footerText");
const label = document.getElementById("footerLabel");

buttons.forEach(btn => {
    btn.addEventListener("mouseenter", () => {

        buttons.forEach(b => b.classList.remove("active"));
        btn.classList.add("active");

        text.classList.add("text-changing");
        label.classList.add("text-changing");

        setTimeout(() => {
            text.textContent = btn.dataset.text;
            label.textContent = btn.dataset.label;

            text.classList.remove("text-changing");
            label.classList.remove("text-changing");
        }, 180);
    });
});
  </script>
  <?php include APPROOT . '/views/partials/cookie-consent.php'; ?>
</body>
</html>
