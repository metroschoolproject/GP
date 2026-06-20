<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- don't forget to change this after sign in to sign up -->
  <title>Sign In</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Pinyon+Script&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<style>

  :root {
    --env-bg: #e8b4b8;
    --env-dark: #d89aa0;
    --env-border:#f4c7c4e5;
    --paper: #f5e8d9;
    --accent: #6d4c5b;
    --header-font : "Pinyon Script", cursive;
    --body-font: serif;
    --body-font-color: rgba(250, 242, 238, 0.9);
    --focus-color: rgb(247, 236, 236);
    --input-field-color: rgba(249, 237, 228, 0.9);
    --social-btn-hover: rgba(38, 2, 2, 0.471);
    --placeholder: rgba(141, 140, 140, 0.743);

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
    /* Link to your uploaded image here */
    --paper-img: url('noti-bg.jpg'); 
  }

  body{
    font-family: var(--body-font);
    min-height: 100vh;
    margin: 0;
    align-items: center;
  }

  /* text animation */
  .char { 
    display: inline-block; 
    white-space: pre; 
    opacity: 0;
    filter: blur(8px); 
    transform: scale(0.86); 
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

  /* screen */
  .screen {
    position: absolute;
    inset: 0;
    padding: 38px 40px 40px;
    display: flex;
    flex-direction: column;
    opacity: 0;
    pointer-events: none;
    transform: translateX(40px);
    transition: opacity 0.45s cubic-bezier(0.4,0,0.2,1),
                transform 0.45s cubic-bezier(0.4,0,0.2,1);
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
    min-height: 90px;
    justify-content: center;
  }

  .main-heading {
    font-family: var(--header-font);
    font-size: 44px;
    font-weight: 600;
    color: var(--accent);
    line-height: 1.2;
    letter-spacing: -0.3px;
    margin-bottom: 2px;
  }

  .sub-heading {
    font-family: var(--body-font);
    font-size: 15px;
    color: var(--accent);
    line-height: 1.4;
  }

  /* text fields */
  .field-wrap {
    margin-bottom: 0;
    transition:
      max-height 0.55s cubic-bezier(0.4,0,0.2,1),
      opacity 0.48s cubic-bezier(0.4,0,0.2,1),
      transform 0.48s cubic-bezier(0.4,0,0.2,1);
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
    font-size: 18px;
    color: rgba(15, 1, 1, 0.9);
    outline: none;
    font-family: inherit;
    box-shadow: 5px 5px 10px rgba(2, 2, 2, 0.332);
    transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;
  }

  .decorated-input input::placeholder {
    color: transparent;
  }

  .decorated-input label {
    position: absolute;
    left: 18px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 14px;
    color: #9aa4b2;
    pointer-events: none;
    transition: all 0.25s ease;
  }

  .decorated-input:hover label,
  .decorated-input input:focus + label,
  .decorated-input input:not(:placeholder-shown) + label {
    top: 4px;
    transform: translateY(0);
    font-size: 14px;
    color: var(--accent);
    letter-spacing: 0.3px;
  }

  .decorated-input input:focus {
    border: 1px solid var(--accent);
    box-shadow: 0 0 10px rgba(109, 76, 91, 0.45);
    background-color: var(--focus-color);
  }

  .decorated-input:hover input {
    border-color: rgba(109, 76, 91, 0.6);
  }

  .decorated-input input:-webkit-autofill,
  .decorated-input input:-webkit-autofill:hover,
  .decorated-input input:-webkit-autofill:focus {
    -webkit-box-shadow: 0 0 0px 1000px var(--input-field-color) inset;
    box-shadow: 0 0 0px 1000px var(--input-field-color) inset;
    -webkit-text-fill-color: rgba(15, 1, 1, 0.9);
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
    transition: opacity 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    line-height: 0;
  }

  .eye-btn:hover { opacity: 1; }
  .eye-btn svg { display: block; }





  /* button */
  .btn {
    width: 100%; 
    padding: 14px; 
    border-radius: 12px; 
    border: none;
    font-size: 15px; 
    font-weight: 600; 
    cursor: pointer;
    background-color: var(--accent);
    color: white;
    letter-spacing: 0.2px;
    transition: transform 0.15s, box-shadow 0.15s, opacity 0.4s, filter 0.4s;
    box-shadow:5px 5px 8px rgba(0, 0, 0, 0.628);
    position: relative;
    overflow: hidden;
  }

  .btn:hover { 
    transform: translateY(-1px); 
    box-shadow:5px 5px 8px rgba(0, 0, 0, 0.628);
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
    background:linear-gradient(90deg,transparent 0%,rgba(255,255,255,0.18) 50%,transparent 100%);
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
    background: rgba(250, 242, 237, 0.72);
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
    border: 1px solid rgba(109, 76, 91, 0.18);
    border-radius: 14px;
    background: rgba(255, 252, 249, 0.9);
    padding: 18px;
    box-shadow: 0 18px 46px rgba(80, 40, 80, 0.16);
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
    height:1px;
    background:rgba(255,255,255,0.08);
    margin:20px 0;
    position:relative;
    opacity: 1; 
    transition: opacity 0.4s;
    display: flex;
    align-items: center;
    text-align: center;
  }

  .divider span {
    padding: 0 10px;
    font-size: 12px;
    color: var(--accent);
    white-space: nowrap;
  }

  .divider::before,
  .divider::after {
    content: "";
    flex: 1;
    border-bottom: 0.1px solid var(--accent);
  }

  .divider::before { margin-right: 10px; }
  .divider::after { margin-left: 10px; }

  /* social buttons */
  .social-btn {
    display:flex;
    align-items:center;
    justify-content:center;
    gap:8px;
    padding:10px;
    border-radius:10px;
    border:1px solid var(--accent);
    background-color:var(--input-field-color);
    cursor:pointer;
    font-size:13px;
    color: var(--accent);
    transition:background 0.2s,border-color 0.2s;
    font-family:inherit;
  }

  .social-btn:hover { 
    background-color:var(--social-btn-hover);
    border-color:var(--accent);
    color: var(--paper);
  }

  .toggle-row { 
    text-align:center;
    margin-top:20px;
    font-size:13.5px;
    color:rgba(200,180,255,0.6); 
  }

/* PASSWORD STRENGTH */

.strength-seg{
  flex:1;
  height:3px;
  border-radius:999px;
  background:rgba(109,76,91,0.15);
  transition:background 0.3s;
}

.strength-seg.active{
  background:var(--accent);
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
  <div class="relative w-full max-w-[420px] rounded-[24px] border border-white/14 bg-[var(--paper)] backdrop-blur-[24px] shadow-[0_8px_48px_rgba(80,40,180,0.18),inset_0_1px_0_rgba(255,255,255,0.12)]" style="min-height: 580px; height: auto;">
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
    <div class="screen active" id="screenAuth">
 
      <div class="heading-area mb-2">
        <div class="main-heading" id="mainHeading" data-signin="Welcome Back" data-signup="Create account">Welcome Back</div>
        <div class="sub-heading" id="subHeading" data-signin="Sign in to your account" data-signup="Join us and start your journey">Sign in to your account</div>
        <div style="width:192px; margin-top:2px">
          <img id="decorLine" src="signInDecorLine.png" style="transition: opacity 1s, transform 1s; display:block;">
        </div>
      </div>
 
      <div class="flex flex-col mb-2" id="fieldGroup">
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
        </div>
      </div>
 
      <!-- <button type="button" class="btn mb-5 loginregister_btn" id="mainBtn"><span class="btn-shimmer"></span><span id="btnText" data-signin="Sign In" data-signup="Create Account">Sign In</span></button> -->
        <button type="button" class="btn mb-5 loginregister_btn" id="mainBtn">
        <span class="btn-shimmer"></span>
        <span id="btnText" data-signin="Sign In" data-signup="Create Account">Sign In</span>
        </button>

      <!-- Backend validation hooks: hidden by default, used by the inline login script -->
      <p class="emailvalid hidden text-[12px] text-red-500 mt-[-10px] mb-2">Please check your email.</p>
      <p id="pwvalid" class="hidden text-[12px] text-red-500 mt-[-10px] mb-2">Invalid password.</p>
      <div class="warning-bar hidden text-[12px] text-red-500 mb-2"></div>
      <div class="accountnotfound-warning-bar hidden text-[12px] text-red-500 mb-2">Account not found.</div>
      <span class="lock-until-time hidden"></span>
 
      <?php
        $authType = $_GET['type'] ?? 'customer';
        $isInternalLogin = $authType === 'internal';
        $hideSocialLogin = $isInternalLogin;
        $socialType = $authType === 'supplier' ? 'supplier' : 'customer';
      ?>
      <div class="divider" id="divider" style="<?= $hideSocialLogin ? 'display:none' : '' ?>"><span>or continue with</span></div>
      <div id="socialAuth" class="grid grid-cols-2 gap-[10px]" style="<?= $hideSocialLogin ? 'display:none' : '' ?>">
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
                            inputs[i].style.border = "1px solid rgb(120, 120, 196)";
                        } else {
                            inputs[i].style.border = "1px solid red";
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
                        passwordInput.style.border = '1px solid red';
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
                        passwordInput.style.border = '1px solid red';
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
                        passwordInput.style.border = '1px solid red';
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
                        input.style.border = "1px solid red";
                        valid = false;
                    } else {
                        input.style.border = "1px solid rgb(120, 120, 196)";
                    }
                });

                if (!valid) {
                    return;
                }

                if (!validEmail(email)) {
                    emailInput.style.border = "1px solid red";
                    emailInput.setCustomValidity("Enter a valid email address");
                    emailInput.reportValidity();
                    return;
                }

                if (password.length < 8) {
                    passwordInput.style.border = "1px solid red";
                    return;
                }

                if (password !== confirmPassword) {
                    confirmInput.style.border = "1px solid red";
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
                        emailvalid.style.display = "block";
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
            } else {
                emailInput.setCustomValidity('');
            }
            });

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




</body>
</html>
