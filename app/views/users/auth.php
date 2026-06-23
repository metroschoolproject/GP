<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- don't forget to change this after sign in to sign up -->
  <title>Sign In</title>
  <?php include APPROOT . '/views/partials/ga-tracking.php'; ?>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<style>

  :root {
    --env-bg: #e8b4b8;
    --env-dark: #d89aa0;
    --env-border: #ead8c7;
    --paper: #faf5ef;
    --accent: #6d4c5b;
    --header-font: "Playfair Display", Georgia, serif;
    --body-font: "Poppins", system-ui, -apple-system, sans-serif;
    --body-font-color: #111827;
    --focus-color: #faf5ef;
    --input-field-color: #fcf8f5;
    --social-btn-hover: #f5e8d9;
    --placeholder: #b79c8b;
    --gold: #d4a047;
    --gold-light: rgba(212, 160, 71, 0.12);
    --muted: #b79c8b;
    --c-danger: #b94b4b;
    --bg: #f5e8d9;
    --white: #fcf8f5;

    --scroll-height: 72px;
    --scroll-width: clamp(240px, 38vw, 360px);
    --intro-speed: 0.45s;
    --open-speed: 1s;
    --hold-speed: 4.2s;
    --close-speed: 0.9s;
    --outro-speed: 0.45s;
    --open-delay: var(--intro-speed);
    --close-delay: calc(var(--intro-speed) + var(--open-speed) + var(--hold-speed));
    --outro-delay: calc(var(--close-delay) + var(--close-speed));
    --paper-img: url('noti-bg.jpg');
  }

  body{
    font-family: var(--body-font);
    min-height: 100vh;
    margin: 0;
    align-items: center;
    background:
      radial-gradient(ellipse at 20% 8%, rgba(109,76,91,0.04) 0%, transparent 60%),
      radial-gradient(ellipse at 80% 92%, rgba(183,156,139,0.07) 0%, transparent 55%),
      var(--bg);
    color: var(--body-font-color);
  }

  /* text animation — soft luxury dissolve */
  .char {
    display: inline-block;
    white-space: pre;
    opacity: 0;
    filter: blur(4px);
    transform: scale(0.94);
  }

  #decorLine {
    transition: all 3.5s cubic-bezier(0.22, 1, 0.36, 1);
  }

  #decorLine.signup-mode {
    transform: scale(1.05);
    filter: hue-rotate(25deg) saturate(1.2);
    opacity: 1;
  }

  .sparkle-canvas {
    position:absolute;
    top:0;
    left:0;
    width:100%;
    height:100%;
    pointer-events:none;
    border-radius:24px;
    overflow:visible;
  }

  /* screen — luxury easing */
  .screen {
    position: absolute;
    inset: 0;
    padding: 32px 40px 38px;
    display: flex;
    flex-direction: column;
    opacity: 0;
    pointer-events: none;
    transform: translateX(40px);
    transition: opacity 0.65s cubic-bezier(0.4, 0, 0.2, 1),
                transform 0.65s cubic-bezier(0.4, 0, 0.2, 1);
  }

  .screen.active {
    position: relative;
    opacity: 1;
    pointer-events: all;
    transform: translateX(0);
    inset: unset;
  }

  /* heading */
  .heading-area {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    min-height: 84px;
    justify-content: center;
  }

  .main-heading {
    font-family: var(--header-font);
    font-size: 36px;
    font-weight: 600;
    color: var(--accent);
    line-height: 1.2;
    letter-spacing: -0.3px;
    margin-bottom: 0;
  }

  .sub-heading {
    font-family: var(--body-font);
    font-size: 14px;
    font-weight: 400;
    color: var(--muted);
    line-height: 1.4;
  }

  /* text fields — luxury easing */
  .field-wrap {
    margin-bottom: 0;
    transition:
      max-height 0.7s cubic-bezier(0.4, 0, 0.2, 1),
      opacity 0.6s cubic-bezier(0.4, 0, 0.2, 1),
      transform 0.6s cubic-bezier(0.4, 0, 0.2, 1),
      margin-bottom 0.6s cubic-bezier(0.4, 0, 0.2, 1);
  }

  .field-wrap.visible {
    max-height: 70px;
    opacity: 1;
    transform: translateY(0);
  }

  .field-wrap.name-wrap.visible {
    max-height: 56px;
  }

  .decorated-input {
    position: relative;
    width: 100%;
  }

  .decorated-input input {
    width: 100%;
    background: var(--input-field-color);
    border: 1px solid var(--env-border);
    border-radius: 12px;
    padding: 20px 16px 12px;
    font-size: 16px;
    font-weight: 500;
    color: #111827;
    outline: none;
    font-family: var(--body-font);
    box-shadow: 0 1px 3px rgba(44, 36, 32, 0.04);
    transition: border-color 0.35s cubic-bezier(0.4, 0, 0.2, 1),
                background 0.35s cubic-bezier(0.4, 0, 0.2, 1),
                box-shadow 0.4s cubic-bezier(0.4, 0, 0.2, 1),
                transform 0.35s cubic-bezier(0.4, 0, 0.2, 1);
  }

  /* Hide browser's native password reveal eye (Chrome, Edge, Safari) */
  .decorated-input input::-webkit-credentials-auto-fill-button,
  .decorated-input input::-ms-reveal { display: none !important; }

  .decorated-input input::placeholder {
    color: transparent;
  }

  .decorated-input label {
    position: absolute;
    left: 18px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 13px;
    font-weight: 500;
    color: var(--placeholder);
    pointer-events: none;
    transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
  }

  .decorated-input:hover label,
  .decorated-input input:focus + label,
  .decorated-input input:not(:placeholder-shown) + label {
    top: 4px;
    transform: translateY(0);
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--accent);
  }

  .decorated-input input:focus {
    border-color: var(--gold);
    box-shadow: 0 0 0 3px var(--gold-light), 0 8px 20px rgba(212, 160, 71, 0.08);
    background-color: var(--focus-color);
    transform: translateY(-1px);
  }

  .decorated-input:hover input {
    border-color: rgba(212, 160, 71, 0.35);
    box-shadow: 0 2px 8px rgba(44, 36, 32, 0.06);
  }

  .decorated-input input:-webkit-autofill,
  .decorated-input input:-webkit-autofill:hover,
  .decorated-input input:-webkit-autofill:focus {
    -webkit-box-shadow: 0 0 0px 1000px var(--input-field-color) inset;
    box-shadow: 0 0 0px 1000px var(--input-field-color) inset;
    -webkit-text-fill-color: #111827;
    transition: background-color 5000s ease-in-out 0s;
  }

  /* eye toggle button */
  .eye-btn {
    position: absolute;
    right: 13px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    padding: 4px;
    color: var(--accent);
    opacity: 0.55;
    transition: opacity 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    align-items: center;
    justify-content: center;
    line-height: 0;
  }

  .eye-btn:hover { opacity: 1; }
  .eye-btn svg { display: block; }





  /* button — luxury */
  .btn {
    width: 100%;
    padding: 14px;
    border-radius: 12px;
    border: none;
    font-size: 15px;
    font-weight: 600;
    font-family: var(--body-font);
    letter-spacing: 0.3px;
    cursor: pointer;
    background: linear-gradient(135deg, var(--accent) 0%, #8b5e6f 100%);
    color: var(--white);
    box-shadow: 0 4px 14px rgba(109, 76, 91, 0.22);
    position: relative;
    opacity: 1;
    transform: translateY(0);
    transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1),
                box-shadow 0.4s cubic-bezier(0.4, 0, 0.2, 1),
                opacity 0.5s cubic-bezier(0.4, 0, 0.2, 1),
                filter 0.5s cubic-bezier(0.4, 0, 0.2, 1);
  }
  .btn.anim-hide {
    opacity: 0;
    transform: translateY(6px);
    pointer-events: none;
  }
  .btn.anim-show {
    opacity: 1;
    transform: translateY(0);
  }

  .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 28px rgba(109, 76, 91, 0.28);
  }

  .btn:active { transform: translateY(0); }

  .btn.hidden { 
    opacity: 0; 
    filter: blur(4px); 
    pointer-events: none; 
  }

  .btn.is-loading,
  .btn:disabled {
    cursor: wait;
    opacity: 0.82;
    transform: none;
  }

  .btn.is-loading .btn-shimmer {
    animation: authButtonShimmer 1.1s linear infinite;
  }

  .btn-shimmer {
    position:absolute;
    inset:0;
    background:linear-gradient(90deg,transparent 0%,rgba(252,248,245,0.18) 50%,transparent 100%);
    transform:translateX(-100%);
    transition:transform 0.5s;
  }

  .btn:hover .btn-shimmer { transform:translateX(100%); }

  .auth-loading-overlay {
    position: absolute;
    inset: 0;
    z-index: 30;
    display: none;
    align-items: center;
    justify-content: center;
    border-radius: 24px;
    background: rgba(250, 245, 239, 0.80);
    backdrop-filter: blur(10px);
  }

  .auth-loading-overlay.show {
    display: flex;
  }

  .auth-loading-panel {
    display: flex;
    width: min(78%, 280px);
    flex-direction: column;
    gap: 12px;
    border: 1px solid #ead8c7;
    border-radius: 14px;
    background: rgba(250, 245, 239, 0.95);
    padding: 18px;
    box-shadow: 0 18px 46px rgba(15, 23, 42, 0.08);
  }

  .auth-loading-line {
    height: 10px;
    border-radius: 999px;
    background: linear-gradient(90deg, rgba(109, 76, 91, 0.12), rgba(109, 76, 91, 0.32), rgba(109, 76, 91, 0.12));
    background-size: 220% 100%;
    animation: authSkeleton 1.05s ease-in-out infinite;
  }

  .auth-loading-line.short {
    width: 64%;
  }

  .auth-loading-line.medium {
    width: 82%;
  }

  @keyframes authSkeleton {
    0% { background-position: 120% 0; }
    100% { background-position: -120% 0; }
  }

  @keyframes authButtonShimmer {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
  }

  /* divider */
  .divider {
    height: 1px;
    margin: 22px 0;
    position: relative;
    opacity: 1;
    transform: translateY(0);
    transition: opacity 0.5s cubic-bezier(0.4, 0, 0.2, 1),
                transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    align-items: center;
    text-align: center;
  }
  .divider.anim-hide {
    opacity: 0;
    transform: translateY(6px);
    pointer-events: none;
  }

  .divider span {
    padding: 0 12px;
    font-size: 11px;
    font-weight: 500;
    color: var(--muted);
    white-space: nowrap;
    font-family: var(--body-font);
  }

  .divider::before,
  .divider::after {
    content: "";
    flex: 1;
    border-bottom: 1px solid #ead8c7;
  }

  .divider::before { margin-right: 10px; }
  .divider::after { margin-left: 10px; }

  /* social buttons — luxury */
  .social-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 10px;
    border-radius: 10px;
    border: 1px solid #ead8c7;
    background-color: var(--input-field-color);
    cursor: pointer;
    font-size: 13px;
    font-weight: 500;
    font-family: var(--body-font);
    color: #3a2030;
    transition: background 0.35s cubic-bezier(0.4, 0, 0.2, 1),
                border-color 0.35s cubic-bezier(0.4, 0, 0.2, 1),
                box-shadow 0.4s cubic-bezier(0.4, 0, 0.2, 1),
                transform 0.35s cubic-bezier(0.4, 0, 0.2, 1);
  }

  .social-btn:hover {
    background-color: var(--social-btn-hover);
    border-color: var(--gold);
    box-shadow: 0 0 0 3px var(--gold-light), 0 4px 12px rgba(212, 160, 71, 0.10);
    color: #3a2030;
    transform: translateY(-1px);
  }

  .toggle-row {
    text-align: center;
    margin-top: 20px;
    font-size: 13px;
    font-weight: 400;
    color: var(--muted);
    font-family: var(--body-font);
  }

/* PASSWORD STRENGTH — luxury redesign */

.strength-seg {
  flex: 1;
  height: 5px;
  border-radius: 999px;
  background: rgba(109, 76, 91, 0.10);
  transition: background 0.5s cubic-bezier(0.4, 0, 0.2, 1),
              box-shadow 0.5s cubic-bezier(0.4, 0, 0.2, 1),
              transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.strength-seg.active {
  transform: scaleY(1.15);
}

/* Strength colors by level — red → amber → green → gold */
.strength-seg.active[data-level="1"] {
  background: #c97070;
  box-shadow: 0 0 6px rgba(201, 112, 112, 0.3);
}
.strength-seg.active[data-level="2"] {
  background: #d4a047;
  box-shadow: 0 0 6px rgba(212, 160, 71, 0.3);
}
.strength-seg.active[data-level="3"] {
  background: #7db87d;
  box-shadow: 0 0 6px rgba(125, 184, 125, 0.3);
}
.strength-seg.active[data-level="4"] {
  background: #d4a047;
  box-shadow: 0 0 8px rgba(212, 160, 71, 0.35);
}

/* Password requirements hint — luxury redesign */
.pw-requirements {
  font-size: 11px;
  font-weight: 500;
  color: var(--muted);
  margin-top: 4px;
  padding: 8px 10px;
  line-height: 1.55;
  font-family: var(--body-font);
  background: rgba(212, 160, 71, 0.04);
  border: 1px solid rgba(212, 160, 71, 0.08);
  border-radius: 10px;
}
.pw-req {
  display: flex;
  align-items: center;
  gap: 6px;
  transition: color 0.45s cubic-bezier(0.4, 0, 0.2, 1),
              opacity 0.45s cubic-bezier(0.4, 0, 0.2, 1),
              transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  transform: translateX(0);
}
.pw-req::before {
  content: '';
  display: inline-block;
  width: 14px;
  height: 14px;
  border-radius: 50%;
  border: 1.5px solid rgba(109, 76, 91, 0.20);
  flex-shrink: 0;
  transition: background 0.45s cubic-bezier(0.4, 0, 0.2, 1),
              border-color 0.45s cubic-bezier(0.4, 0, 0.2, 1);
}
.pw-req.met {
  color: #5b8c5a;
  opacity: 1;
  transform: translateX(2px);
}
.pw-req.met::before {
  background: #5b8c5a;
  border-color: #5b8c5a;
  box-shadow: 0 0 6px rgba(91, 140, 90, 0.25);
}

/* Email validation indicator */
.email-status {
  position: absolute;
  right: 14px;
  top: 50%;
  transform: translateY(-50%);
  font-size: 14px;
  opacity: 0;
  transition: opacity 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}
.email-status.show { opacity: 1; }

/* Name character counter */
.char-counter {
  font-size: 10px;
  color: var(--accent);
  opacity: 0.5;
  text-align: right;
  margin-top: 2px;
  padding-right: 4px;
}

</style>


<body class="flex  justify-center">
  <style>

    .scroll-wrapper {
      display: none;
      justify-content: flex-start;
      align-items: flex-start;
      position: fixed;
      top: 18px;
      left: 18px;
      z-index: 50;
      pointer-events: none;
      /* Darker background makes the gold pop */
    }

    .scroll-wrapper.show {
      display: flex;
    }

    .scroll-container {
      display: flex;
      align-items: stretch;
      min-height: var(--scroll-height);
      margin: 12px;
      filter: drop-shadow(0 10px 20px rgba(0,0,0,0.5));
      opacity: 0;
      transform-origin: top left;
      animation:
        popDown var(--intro-speed) forwards cubic-bezier(0.2, 1.35, 0.35, 1),
        popUp var(--outro-speed) forwards ease-in var(--outro-delay);
    }

    /* The vertical wooden rollers */
    .roller-vertical {
      width: 8px;
      background: linear-gradient(to right, #3d2b1f, #e0d5c1 50%, #3d2b1f);
      border-radius: 10px;
      position: relative;
      z-index: 5;
      align-self: stretch;
    }

    .roller-vertical::before, .roller-vertical::after {
      content: '';
      position: absolute;
      width: 15px;
      height: 7px;
      background: #2a1b0a;
      left: -3px;
      border-radius: 3px;
    }
    .roller-vertical::before { top: -6px; }
    .roller-vertical::after { bottom: -6px; }

    /* The paper using your specific image */
    .scroll-paper {
      background-image: var(--paper-img);
      background-size: 100% 100%; /* Forces the image to stretch/shrink with the animation */
      background-position: center;
      background-repeat: no-repeat;
      min-height: var(--scroll-height);
      width: 0;
      overflow: hidden;
      position: relative;
      
      animation:
        openScroll var(--open-speed) forwards ease-in-out var(--open-delay),
        closeScroll var(--close-speed) forwards ease-in-out var(--close-delay);

      /* Subtle shadow to make it look like it's tucked under the rollers */
      box-shadow: inset 20px 0 20px -10px rgba(0,0,0,0.3), 
                  inset -20px 0 20px -10px rgba(0,0,0,0.3);
    }

    .content-box {
      width: var(--scroll-width);
      min-height: var(--scroll-height);
      padding: 18px 32px;
      display: flex;
      justify-content: center;
      align-items: center;
      font-family: 'Garamond', serif;
      font-size: 14px;
      color: #4a3721;
      opacity: 0;
      text-align: center;
      animation:
        fadeIn 0.35s forwards ease-out calc(var(--intro-speed) + var(--open-speed) - 0.15s),
        fadeOut 0.25s forwards ease-in calc(var(--close-delay) - 0.2s);
    }

    .content-box p {
      margin: 0;
      line-height: 1.45;
      overflow-wrap: break-word;
    }

    @keyframes popDown {
      0% {
        opacity: 0;
        transform: translateY(-22px) scale(0.92);
      }
      70% {
        opacity: 1;
        transform: translateY(3px) scale(1.03);
      }
      100% {
        opacity: 1;
        transform: translateY(0) scale(1);
      }
    }

    @keyframes popUp {
      from {
        opacity: 1;
        transform: translateY(0) scale(1);
      }
      to {
        opacity: 0;
        transform: translateY(-22px) scale(0.92);
      }
    }

    @keyframes openScroll {
      from { width: 0; }
      to { width: var(--scroll-width); } 
    }

    @keyframes closeScroll {
      from { width: var(--scroll-width); }
      to { width: 0; }
    }

    @keyframes fadeIn {
      to { opacity: 1; }
    }

    @keyframes fadeOut {
      to { opacity: 0; }
    }
  </style>
  <!-- Show The Information that User should Khow  -->
  <div class="scroll-wrapper">
    <div class="scroll-container">
      <div class="roller-vertical"></div>
      
      <div class="scroll-paper">
        <div class="content-box">
          <p class="login_warning_bar"></p>
        </div>
      </div>
      
      <div class="roller-vertical"></div>
    </div>
  </div>

  <!-- card -->
  <div class="relative w-full max-w-[480px] rounded-[24px] border border-[#ead8c7] bg-[#faf5ef] shadow-[0_20px_40px_rgba(15,23,42,0.08)]" style="height: auto;">
    <canvas class="sparkle-canvas" id="sparkleCanvas"></canvas>
    <div class="auth-loading-overlay" id="authLoadingOverlay" aria-hidden="true">
      <div class="auth-loading-panel" role="status" aria-live="polite">
        <div class="auth-loading-line medium"></div>
        <div class="auth-loading-line"></div>
        <div class="auth-loading-line short"></div>
      </div>
    </div>

    <!-- Screen 1 (Sign up/in) -->
     
      <!-- ═══════════════════════════════════════════
         SCREEN 1 — SIGN IN / SIGN UP
    ═══════════════════════════════════════════ -->
    <?php
      $authType = $_GET['type'] ?? 'customer';
      $isInternalLogin = $authType === 'internal';
      $hideSocialLogin = $isInternalLogin;
      $socialType = $authType === 'supplier' ? 'supplier' : 'customer';
    ?>
    <div class="screen active" id="screenAuth">

      <div class="heading-area mb-1">
        <div class="main-heading" id="mainHeading" data-signin="<?= $isInternalLogin ? 'Staff Portal' : 'Welcome Back' ?>" data-signup="Create account"><?= $isInternalLogin ? 'Staff Portal' : 'Welcome Back' ?></div>
        <div class="sub-heading" id="subHeading" data-signin="<?= $isInternalLogin ? 'Sign in with your staff credentials' : 'Sign in to your account' ?>" data-signup="Join us and start your journey"><?= $isInternalLogin ? 'Sign in with your staff credentials' : 'Sign in to your account' ?></div>
        <div style="width:192px; margin-top:2px">
          <img id="decorLine" src="signInDecorLine.png" style="transition: opacity 1s, transform 1s; display:block;">
        </div>
      </div>
 
      <div class="flex flex-col mb-4" id="fieldGroup">
        <!-- Name -->
        <div class="field-wrap" id="fwName" data-modes="signup" data-height="56px" data-margin="14px" style="max-height:0;opacity:0;margin-bottom:0">
          <div class="decorated-input">
            <input id="name" name="name" type="text" placeholder=" " onmousedown="event.stopPropagation()">
            <label for="name">Name</label>
          </div>
        </div>
        <!-- Email -->
        <div class="field-wrap" id="fwEmail" data-modes="signin signup" data-height="70px" data-margin="14px" style="max-height:70px;opacity:1;margin-bottom:14px">
          <div class="decorated-input">
            <input
            id="email"
            name="email"
            type="email"
            placeholder=" "
            autocomplete="username"
            pattern="^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$"
            required
            onmousedown="event.stopPropagation()"
            >            <label for="email">Email</label>
          </div>
        </div>
        <!-- PASSWORD -->
        <div class="field-wrap"
            id="fwPassword"
            data-modes="signin signup"
            data-height="70px"
            data-margin="14px"
            style="max-height:70px;opacity:1;margin-bottom:14px">

            <div class="decorated-input">

                <input
                id="passwordInput"
                name="password"
                type="password"
                placeholder=" "
                autocomplete="current-password"
                minlength="8"
                required
                onmousedown="event.stopPropagation()"
                >

                <label for="passwordInput">Password</label>

                <button
                type="button"
                class="eye-btn"
                id="eyePassword"
                onclick="toggleVisibility('passwordInput','eyePassword')"
                aria-label="Toggle password">

                <svg id="eyePassword-icon"
                    width="18"
                    height="18"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round">

                    <rect x="3" y="11" width="18" height="11" rx="2"/>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>

                </svg>
                </button>

            </div>
        </div>

        <!-- PASSWORD STRENGTH -->
        <div class="field-wrap"
            id="fwStrength"
            data-modes="signup"
            data-height="20px"
            data-margin="6px"
            style="max-height:0;opacity:0;margin-bottom:0">

            <div class="flex items-center justify-between gap-3">

                <div class="flex-1 flex gap-[4px]">

                <span class="strength-seg" id="seg1"></span>
                <span class="strength-seg" id="seg2"></span>
                <span class="strength-seg" id="seg3"></span>
                <span class="strength-seg" id="seg4"></span>

                </div>

                <span id="strengthText"
                class="text-[11px] text-[var(--accent)] opacity-70 whitespace-nowrap">

                Weak

                </span>

            </div>
        </div>

        <!-- PASSWORD REQUIREMENTS HINT -->
        <div class="field-wrap"
            id="fwPwHint"
            data-modes="signup"
            data-height="80px"
            data-margin="2px"
            style="max-height:0;opacity:0;margin-bottom:0">
            <div class="pw-requirements" id="pwRequirements">
              <div class="pw-req" id="reqLength" data-label="At least 8 characters">At least 8 characters</div>
              <div class="pw-req" id="reqUpper" data-label="Uppercase letter (A-Z)">Uppercase letter (A-Z)</div>
              <div class="pw-req" id="reqLower" data-label="Lowercase letter (a-z)">Lowercase letter (a-z)</div>
              <div class="pw-req" id="reqNumber" data-label="Number (0-9)">Number (0-9)</div>
              <div class="pw-req" id="reqSymbol" data-label="Symbol (!@#$...)">Symbol (!@#$...)</div>
            </div>
        </div>



        <!-- Remember & Forgot -->
        <div class="field-wrap flex items-center justify-between" id="fwForgot" data-modes="signin" data-height="28px" data-margin="4px" style="max-height:28px;opacity:1;margin-bottom:4px;overflow:visible">
          <div class="text-left">
            <label class="flex items-center gap-2 text-[13px] text-[var(--accent)] cursor-pointer">
              <input type="checkbox" id="rememberMe" name="remember_me" class="accent-[var(--accent)]">
              <span>Remember me</span>
            </label>
          </div>
          <div class="text-right">
            <button type="button" id="forgetpwbtn"
              class="text-[13px] text-[var(--accent)] hover:underline transition duration-200 bg-transparent border-none cursor-pointer font-[family-name:var(--body-font)]">
              Forgot Password?
            </button>
          </div>
        </div>


        <!-- CONFIRM PASSWORD -->

        <!-- Confirm -->
        <div class="field-wrap" id="fwConfirm" data-modes="signup" data-height="70px" data-margin="14px" style="max-height:0;opacity:0;margin-bottom:0">
          <div class="decorated-input">
            <input id="confirmInput" name="confirm_password" type="password" placeholder=" " onmousedown="event.stopPropagation()">
            <label for="confirmInput">Confirm Password</label>
            <button type="button" class="eye-btn" id="eyeConfirm"
              onclick="toggleVisibility('confirmInput','eyeConfirm')" aria-label="Toggle confirm">
              <svg id="eyeConfirm-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            </button>
          </div>
          <!-- Password match indicator -->
          <div id="matchIndicator" class="flex items-center gap-1.5 mt-1 px-1 text-[11px]" style="opacity:0; transition: opacity 0.4s cubic-bezier(0.4, 0, 0.2, 1);">
            <span id="matchIcon"></span>
            <span id="matchText"></span>
          </div>
        </div>
      </div>
 
      <!-- <button type="button" class="btn mb-5 loginregister_btn" id="mainBtn"><span class="btn-shimmer"></span><span id="btnText" data-signin="Sign In" data-signup="Create Account">Sign In</span></button> -->
        <button type="button" class="btn mb-5 loginregister_btn" id="mainBtn">
        <span class="btn-shimmer"></span>
        <span id="btnText" data-signin="Sign In" data-signup="Create Account">Sign In</span>
        </button>

      <!-- Backend validation hooks: hidden by default, used by the inline login script -->
      <!-- Duplicate email error -->
      <div class="emailvalid hidden mt-[-6px] mb-2">
        <div class="flex items-center gap-2 px-3 py-2.5 rounded-lg" style="background:rgba(185,75,75,0.06);border:1px solid rgba(185,75,75,0.15)">
          <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="#b94b4b" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <div class="flex-1">
            <span class="text-[12px] font-medium" style="color:#b94b4b">This email is already registered.</span>
            <a href="<?= URLROOT ?>/users/auth" class="text-[12px] text-[var(--accent)] font-semibold ml-1 hover:underline">Login instead?</a>
          </div>
        </div>
      </div>
      <p id="pwvalid" class="hidden text-[12px] mt-[-10px] mb-2" style="color:#b94b4b">Invalid password.</p>
      <div class="warning-bar hidden text-[12px] mb-2" style="color:#b94b4b"></div>
      <div class="accountnotfound-warning-bar hidden text-[12px] mb-2" style="color:#b94b4b">Account not found.</div>
      <span class="lock-until-time hidden"></span>

      <div class="divider" id="divider" style="<?= $hideSocialLogin ? 'display:none' : '' ?>"><span>or continue with</span></div>
      <div id="socialAuth" class="grid grid-cols-2 gap-[12px]" style="<?= $hideSocialLogin ? 'display:none' : '' ?>">
        <a class="social-btn" href="<?= URLROOT ?>/users/google?type=<?= $socialType ?>">          <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
          Google
        </a>
        <a class="social-btn" href="<?= URLROOT ?>/users/facebook?type=<?= $socialType ?>">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073C24 5.405 18.627 0 12 0S0 5.405 0 12.073c0 6.019 4.388 11.008 10.125 11.927v-8.437H7.078v-3.49h3.047V9.413c0-3.017 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.963h-1.514c-1.491 0-1.956.931-1.956 1.887v2.264h3.328l-.532 3.49h-2.796V24C19.612 23.081 24 18.092 24 12.073z"/></svg>
          Facebook
        </a>
      </div>
 
      <div class="toggle-row">
        <span class="font-[family-name:var(--header-font)] text-[var(--placeholder)]" id="togglePrompt" data-signin="Don't have an account? " data-signup="Already have an account? ">Don't have an account? </span>
        <button class="text-[var(--accent)] underline bg-transparent border-none cursor-pointer font-[family-name:var(--body-font)] text-[13.5px]" id="toggleBtn" data-signin="Create one" data-signup="Sign in" style="<?= $isInternalLogin ? 'display:none' : '' ?>">Create one</button>
      </div>
    </div>
    

 

  </div>
  <!-- /card -->

  <template id="eyeOpenTemplate">
    <svg id="{id}-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
  </template>
  <template id="eyeClosedTemplate">
    <svg id="{id}-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 9.9-1"/></svg>
  </template>

  <script src="<?= URLROOT; ?>/public/js/login.js"></script>
    <script>
            const rawAccountIntent = new URLSearchParams(window.location.search).get('type') || 'customer';
            const accountIntent = rawAccountIntent === 'supplier' ? 'supplier' : 'customer';
            const hideSocialLogin = rawAccountIntent === 'internal';

            window.addEventListener('load', () => {
                if (hideSocialLogin) {
                    document.getElementById('divider').style.display = 'none';
                    document.getElementById('socialAuth').style.display = 'none';
                    document.getElementById('toggleBtn').style.display = 'none';
                }

                if (accountIntent === 'supplier') {
                    document.getElementById('toggleBtn')?.click();
                }
            });
    
        //   Login 
            // all rules are completed ?
            document.addEventListener("DOMContentLoaded", () => {
                var inputs = document.getElementsByTagName('input');
                let loginregister_btn = document.querySelector(".loginregister_btn");
                let emailvalid = document.querySelector('.emailvalid');
                const lock_warning_bar = document.querySelector('.warning-bar');
                const lock_until_time = document.querySelector('.lock-until-time');

                const accountnotfound_warning_bar  = document.querySelector('.accountnotfound-warning-bar');
                loginregister_btn.addEventListener("click", () => {
                    if (authLoading) {
                        return;
                    }

                    clearAuthErrors();
                    const btnText = document.getElementById("btnText").textContent.trim();

                    if (btnText === "Create Account") {
                        handleRegister();
                        return;
                    }                    for (var i = 0; i < inputs.length; i++) {
                        if (!inputs[i].value.trim() == "") {
                            inputs[i].style.border = "1px solid #c8b1a1";
                        } else {
                            inputs[i].style.border = "1px solid #b94b4b";
                        }

                    }

                    // fetch api
                    const data = {
                        email: safeInput("email"),
                    };

                    setAuthLoading(true, 'Checking account...');

                    fetch("<?= URLROOT ?>/users/login", {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify(data),
                    })
                        .then(res => res.json())
                        .then(res => {
                            // If no lock 
                            if (res.challenge == false) {
                                setAuthLoading(false);
                                emailvalid.style.display = 'block';
                            } 
                            else if (res.status == 'success') {
                                console.log(res);
                                const ccode = res.challenge.challenge;
                                handleLogin(ccode);
                            }

                            // If account locked 
                            if(res.status == 'lock'){
                                setAuthLoading(false);
                                console.log(res);
                                const lockedUntil = res.lockedUntil && res.lockedUntil.date ? res.lockedUntil.date : '';
                                showScrollMessage(accountLockMessage(lockedUntil));
                                lock_warning_bar.classList.replace('hidden','show')
                                lock_warning_bar.style.display = 'block';
                                lock_warning_bar.innerHTML = accountLockMessage(lockedUntil);
                                lock_until_time.innerHTML = lockedUntil
                            }

                            // account not found 
                            if(res.status == 'accountnotfound'){
                                setAuthLoading(false);
                                console.log(res);
                                accountnotfound_warning_bar.classList.replace('hidden','show'); 
                                accountnotfound_warning_bar.style.display = 'block';
                                pwvalid.style.display = 'block';
                            }

                            if(res.status == 'email_unverified'){
                                window.location.href = "<?= URLROOT ?>/users/verificationSent?e=" + encodeURIComponent(res.email || data.email);
                                return;
                            }

                            // Account suspended / banned / deleted by an admin
                            if(res.status == 'account_blocked'){
                                setAuthLoading(false);
                                accountnotfound_warning_bar.textContent = res.message || 'Your account is not active. Please contact support.';
                                accountnotfound_warning_bar.classList.replace('hidden','show');
                                accountnotfound_warning_bar.style.display = 'block';
                                return;
                            }

                            if (res.status !== 'success') {
                                setAuthLoading(false);
                            }
                        }

                        )
                        .catch(err => {
                            setAuthLoading(false);
                            console.error("Fetch error:", err);
                        });

                });

            })

            // Handle Login 
            async function handleLogin(ccode){
                clearAuthErrors();
                const password = safeInput("password");
                const pw_sha = await sha256(password);
                const challenge = pw_sha + ccode;
                const response = await sha256(challenge);
                const pwvalid = document.querySelector('#pwvalid');
                const passwordInput = document.getElementById('passwordInput');
                const login_warning_bar = document.querySelector('.warning-bar');


                const data = {
                    email: safeInput("email"),
                    pw_sha: pw_sha,        
                    res_code: response,
                    remember_me: document.getElementById("rememberMe")?.checked === true
                };

                setAuthLoading(true, 'Verifying password...');

                fetch("<?= URLROOT ?>/users/verifyChallenge", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(data),
                })
                .then(res => res.json())
                .then(res => {
                    if(res.loginfailnotyet == true || res.pwd === false || res.status === false){
                        setAuthLoading(false);
                        const attemptText = res.attempt_count && res.max_attempts
                            ? ` Attempt ${res.attempt_count} of ${res.max_attempts}.`
                            : '';
                        const wrongPasswordMessage = `Wrong password. Please try again.${attemptText}`;
                        showScrollMessage(wrongPasswordMessage);
                        pwvalid.classList.remove('hidden');
                        pwvalid.style.display = 'block';
                        pwvalid.textContent = wrongPasswordMessage;
                        passwordInput.style.border = '1px solid #b94b4b';
                        return;
                    }
                    if(res.loginfailover == true){
                        setAuthLoading(false);
                        const lockedUntil = res.lockedUntil && res.lockedUntil.date ? res.lockedUntil.date : '';
                        showScrollMessage(accountLockMessage(lockedUntil));
                        login_warning_bar.classList.replace('hidden','show');
                        login_warning_bar.style.display = 'block';
                        login_warning_bar.innerHTML = accountLockMessage(lockedUntil) + " We sent a login fail alert to your email.";
                        console.log('hh')
                        passwordInput.style.border = '1px solid #b94b4b';
                        pwvalid.style.display = 'block';
                        return;
                    }
                    if(res.status == 'lock'){
                        setAuthLoading(false);
                        const lockedUntil = res.lockedUntil && res.lockedUntil.date ? res.lockedUntil.date : '';
                        showScrollMessage(accountLockMessage(lockedUntil));
                        login_warning_bar.classList.replace('hidden','show');
                        login_warning_bar.style.display = 'block';
                        login_warning_bar.innerHTML = accountLockMessage(lockedUntil);
                        passwordInput.style.border = '1px solid #b94b4b';
                        pwvalid.style.display = 'block';
                        return;
                    }
                    if(res.status == true){
                        window.location.href = "<?= URLROOT ?>/otps/otp";
                        console.log(window.location.href)
                        return;

                    }
                
                    setAuthLoading(false);
                    console.log(res);
                })
                .catch(err => {
                    setAuthLoading(false);
                    console.error("Fetch error:", err);
                });

            }
            // Handle Register 
            async function handleRegister() {
                if (authLoading) {
                    return;
                }

                const name = safeInput("name");
                const email = safeInput("email");
                const password = safeInput("password");
                const confirmPassword = safeInput("confirm_password");

                const emailvalid = document.querySelector('.emailvalid');

                const nameInput = document.getElementById("name");
                const emailInput = document.getElementById("email");
                const passwordInput = document.getElementById("passwordInput");
                const confirmInput = document.getElementById("confirmInput");

                let valid = true;

                [nameInput, emailInput, passwordInput, confirmInput].forEach(input => {
                    if (!input.value.trim()) {
                        input.style.border = "1px solid #b94b4b";
                        valid = false;
                    } else {
                        input.style.border = "1px solid #c8b1a1";
                    }
                });

                if (!valid) {
                    return;
                }

                if (!validEmail(email)) {
                    emailInput.style.border = "1px solid #b94b4b";
                    emailInput.setCustomValidity("Enter a valid email address");
                    emailInput.reportValidity();
                    return;
                }

                if (password.length < 8) {
                    passwordInput.style.border = "1px solid #b94b4b";
                    return;
                }

                // Require at least "Fair" strength (score >= 2)
                let pwScore = 0;
                if (password.length >= 8) pwScore++;
                if (/[A-Z]/.test(password)) pwScore++;
                if (/[0-9]/.test(password)) pwScore++;
                if (/[^A-Za-z0-9]/.test(password)) pwScore++;
                if (pwScore < 2) {
                    passwordInput.style.border = "1px solid #b94b4b";
                    const warningBar = document.querySelector('.warning-bar');
                    warningBar.classList.remove('hidden');
                    warningBar.style.display = 'block';
                    warningBar.textContent = 'Password is too weak. Include uppercase letters, numbers, or symbols.';
                    passwordInput.focus();
                    return;
                }

                if (password !== confirmPassword) {
                    confirmInput.style.border = "1px solid #b94b4b";
                    return;
                }

                const data = {
                    username: name,
                    email: email,
                    password: password,
                    compassword: confirmPassword,
                    role: accountIntent
                };

                setAuthLoading(true, 'Creating account...');

                fetch("<?= URLROOT ?>/users/register", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(data),
                })
                .then(async res => {
                    const text = await res.text();
                    const payload = text ? JSON.parse(text) : {};

                    if (!res.ok) {
                        throw new Error(payload.message || text || "Register request failed");
                    }

                    return payload;
                })
                .then(res => {
                    if (res.email == true) {
                        setAuthLoading(false);
                        emailvalid.classList.remove('hidden');
                        emailvalid.style.display = "block";
                        emailInput.style.borderColor = '#b94b4b';
                        emailInput.style.boxShadow = '0 0 0 3px rgba(185, 75, 75, 0.12)';
                        emailInput.focus();
                    } else if (res.status == "success") {
                        window.location.href = "<?= URLROOT ?>/" + res.redirect;
                    } else if (res.message) {
                        setAuthLoading(false);
                        const warningBar = document.querySelector('.warning-bar');
                        warningBar.classList.remove('hidden');
                        warningBar.style.display = 'block';
                        warningBar.textContent = res.message;
                    } else {
                        setAuthLoading(false);
                    }
                })
                .catch(err => {
                    setAuthLoading(false);
                    console.error("Register Fetch error:", err);
                });
            }


            // input sanitization
            function encodeHTML(str) {
                return str.replace(/[&<>"']/g, function (char) {
                    const entities = {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;'
                    };
                    return entities[char];
                });
            }

            /* INPUT SANITIZATION + EMAIL PATTERN */

            const nameInput = document.getElementById('name');
            const emailInput = document.getElementById('email');
            const passwordField = document.getElementById('passwordInput');
            const confirmField = document.getElementById('confirmInput');

            function allowEnglishOnly(input, regex) {
            if (!input) return;

            input.addEventListener('input', () => {
                input.value = input.value.replace(regex, '');
            });

            input.addEventListener('paste', e => {
                e.preventDefault();

                const pasted = (e.clipboardData || window.clipboardData)
                .getData('text')
                .replace(regex, '');

                input.value += pasted;
                input.dispatchEvent(new Event('input', { bubbles: true }));
            });
            }

            allowEnglishOnly(nameInput, /[^A-Za-z\s]/g);
            allowEnglishOnly(emailInput, /[^A-Za-z0-9@._%+-]/g);
            allowEnglishOnly(passwordField, /[^\x20-\x7E]/g);
            allowEnglishOnly(confirmField, /[^\x20-\x7E]/g);

            /* EMAIL PATTERN */

            function validEmail(email) {
            return /^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/.test(email);
            }

            emailInput.addEventListener('input', () => {
            const value = emailInput.value.trim();

            if (value && !validEmail(value)) {
                emailInput.setCustomValidity('Enter a valid email address');
                emailInput.style.borderColor = '#d4a047';
            } else if (value && validEmail(value)) {
                emailInput.setCustomValidity('');
                emailInput.style.borderColor = '#16a34a';
            } else {
                emailInput.setCustomValidity('');
                emailInput.style.borderColor = '';
            }
            });

            /* PASSWORD MATCH INDICATOR */
            if (confirmField && passwordField) {
                const matchIndicator = document.getElementById('matchIndicator');
                const matchIcon = document.getElementById('matchIcon');
                const matchText = document.getElementById('matchText');

                function checkMatch() {
                    const pw = passwordField.value;
                    const cp = confirmField.value;

                    if (!cp) {
                        matchIndicator.style.opacity = '0';
                        confirmField.style.borderColor = '';
                        return;
                    }

                    matchIndicator.style.opacity = '1';

                    if (pw === cp) {
                        matchIcon.textContent = '✓';
                        matchIcon.style.color = '#4ade80';
                        matchText.textContent = 'Passwords match';
                        matchText.style.color = '#4ade80';
                        confirmField.style.borderColor = '#16a34a';
                    } else {
                        matchIcon.textContent = '✗';
                        matchIcon.style.color = '#f87171';
                        matchText.textContent = 'Passwords don\'t match';
                        matchText.style.color = '#f87171';
                        confirmField.style.borderColor = '#b94b4b';
                    }
                }

                passwordField.addEventListener('input', checkMatch);
                confirmField.addEventListener('input', checkMatch);
            }

            /* NAME FIELD VALIDATION */
            const nameField = document.getElementById('name');
            if (nameField) {
                nameField.addEventListener('input', () => {
                    const val = nameField.value.trim();
                    if (val.length > 0 && val.length < 2) {
                        nameField.style.borderColor = '#d4a047';
                    } else if (val.length >= 2) {
                        nameField.style.borderColor = '#16a34a';
                    } else {
                        nameField.style.borderColor = '';
                    }
                });
            }

            function safeInput(name) {
                const el = document.querySelector(`input[name='${name}']`);
                return encodeHTML(el.value.trim());
            }

            // Web Crypto 
            async function sha256(message) {
                const msgBuffer = new TextEncoder().encode(message);
                const hashBuffer = await crypto.subtle.digest("SHA-256", msgBuffer);
                const hashArray = Array.from(new Uint8Array(hashBuffer));
                return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
            }



            // clear login/register error messages when switching mode
            function hideScrollMessage() {
                const wrapper = document.querySelector('.scroll-wrapper');
                if (wrapper) {
                    wrapper.classList.remove('show');
                }
            }

            function showScrollMessage(message) {
                const wrapper = document.querySelector('.scroll-wrapper');
                const text = document.querySelector('.login_warning_bar');

                if (!wrapper || !text) return;

                text.textContent = message;
                wrapper.classList.add('show');

                const animatedParts = wrapper.querySelectorAll('.scroll-container, .scroll-paper, .content-box');
                animatedParts.forEach(el => {
                    el.style.animation = 'none';
                });

                void wrapper.offsetWidth;

                animatedParts.forEach(el => {
                    el.style.animation = '';
                });

                clearTimeout(showScrollMessage.hideTimer);
                showScrollMessage.hideTimer = setTimeout(hideScrollMessage, 7200);
            }

            function accountLockMessage(lockedUntil) {
                const untilText = lockedUntil
                    ? ` You can try again after ${lockedUntil}.`
                    : ' Please wait before trying again.';

                return `Your account is locked because there were too many wrong password attempts.${untilText} You can also use Forgot Password if this was not you.`;
            }

            let authLoading = false;

            function setAuthLoading(isLoading, message = 'Please wait...') {
                authLoading = isLoading;

                const overlay = document.getElementById('authLoadingOverlay');
                const button = document.getElementById('mainBtn');
                const buttonText = document.getElementById('btnText');

                if (overlay) {
                    overlay.classList.toggle('show', isLoading);
                    overlay.setAttribute('aria-hidden', isLoading ? 'false' : 'true');
                }

                if (button) {
                    button.disabled = isLoading;
                    button.classList.toggle('is-loading', isLoading);
                }

                if (buttonText) {
                    if (!buttonText.dataset.idleText) {
                        buttonText.dataset.idleText = buttonText.textContent;
                    }

                    buttonText.textContent = isLoading ? message : buttonText.dataset.idleText;

                    if (!isLoading) {
                        delete buttonText.dataset.idleText;
                    }
                }
            }

            function clearAuthErrors() {
                hideScrollMessage();
                document.querySelectorAll('.emailvalid, #pwvalid, .warning-bar, .accountnotfound-warning-bar, .lock-until-time')
                    .forEach(el => {
                        el.classList.add('hidden');
                        el.classList.remove('show');
                        el.style.display = 'none';
                    });

                const warningBar = document.querySelector('.warning-bar');
                const accountNotFound = document.querySelector('.accountnotfound-warning-bar');
                const lockUntil = document.querySelector('.lock-until-time');

                if (warningBar) warningBar.innerHTML = '';
                if (accountNotFound) accountNotFound.innerHTML = 'Account not found.';
                if (lockUntil) lockUntil.innerHTML = '';

                document.querySelectorAll('#fieldGroup input')
                    .forEach(input => {
                        input.style.border = '';
                        input.style.boxShadow = '';
                        input.setCustomValidity('');
                    });
            }

            const toggleModeBtn = document.querySelector('#toggleBtn');
            if (toggleModeBtn) {
                toggleModeBtn.addEventListener('click', clearAuthErrors, true);
            }


            // forget password 
            const forgetpwbtn = document.querySelector('#forgetpwbtn');
            forgetpwbtn.addEventListener('click',()=>{
            window.location.href = '<?= URLROOT ?>/resetpassword/singleresettoken';  
            })

    
    </script>




<?php include APPROOT . '/views/partials/cookie-consent.php'; ?>
</body>
</html>
