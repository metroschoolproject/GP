<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="<?= URLROOT ?>/public/images/home/gp_logo.png">
    <title>Supplier Onboarding - <?= APPNAME ?></title>
    <?php $dashboardCssVersion = file_exists(APPROOT . '/../public/css/app.css') ? filemtime(APPROOT . '/../public/css/app.css') : time(); ?>
    <?php $leftBgVersion = file_exists(APPROOT . '/../public/images/onboarding/supplier-left-bg.jpg') ? filemtime(APPROOT . '/../public/images/onboarding/supplier-left-bg.jpg') : time(); ?>
    <link rel="stylesheet" href="<?= URLROOT ?>/public/css/app.css?v=<?= $dashboardCssVersion ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --env-border: #ead8c7;
            --paper: #f5e8d9;
            --paper-light: #FFFFFF;
            --paper-panel: #FFFFFF;
            --accent: #6d4c5b;
            --accent-hover: #7b5c69;
            --gold: #c8b1a1;
            --gold-soft: #e8d7ca;
            --danger: #b94b4b;
            --focus-color: #FFFFFF;
            --input-bg: #FFFFFF;
            --header-font: "Great Vibes", cursive;
            --body-font: system-ui, -apple-system, sans-serif;
            --ui-font: system-ui, -apple-system, sans-serif;
            --ink: #111827;
            --ink-muted: #7b5c69;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html, body { height: 100%; }

        body {
            font-family: var(--body-font);
            color: var(--ink);
            overflow: hidden;
        }

        /* ── Split layout ── */
        .split {
            display: grid;
            grid-template-columns: 1fr;
            height: 100vh;
            position: relative;
            transition: grid-template-columns 0.35s ease;
            background:
                radial-gradient(circle at 18% 18%, rgba(255,255,255,0.92), transparent 30%),
                radial-gradient(circle at 88% 18%, rgba(200,177,161,0.14), transparent 26%),
                linear-gradient(135deg, #fff9f5 0%, #f8ece3 48%, #fffdfb 100%);
        }
        .split::before {
            content: "Tell us about\A your business";
            position: absolute;
            left: clamp(36px, 6vw, 78px);
            top: 48%;
            z-index: 4;
            width: min(30vw, 330px);
            transform: translateY(-50%);
            white-space: pre-line;
            color: #7a3f56;
            font-family: "Playfair Display", Georgia, serif;
            font-size: clamp(28px, 3.5vw, 44px);
            font-weight: 600;
            line-height: 1.08;
            pointer-events: none;
        }
        .split[data-step="2"]::before {
            content: "Tell us more about\A your business";
        }
        .split[data-step="3"]::before {
            content: "Business contact\A information";
        }
        .split[data-step="4"]::before {
            content: "Verify your business";
        }
        .split::after {
            content: "";
            position: absolute;
            top: 0;
            bottom: 0;
            left: calc(44% - 70px);
            width: 190px;
            z-index: 3;
            pointer-events: none;
            background:
                url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 190 900' preserveAspectRatio='none'%3E%3Cpath fill='%23fff9f5' d='M0 0H78C146 92 24 174 84 292C137 397 38 472 94 585C154 706 64 802 112 900H0Z'/%3E%3C/svg%3E") left top / 100% 100% no-repeat;
            opacity: 0.82;
            filter: drop-shadow(20px 0 28px rgba(116,73,93,0.1));
            animation: waveBreath 9s ease-in-out infinite;
        }
        .split.agreement-mode {
            grid-template-columns: 1fr;
            height: 100vh;
            overflow: hidden;
        }
        .split.agreement-mode::before,
        .split.agreement-mode::after {
            display: none;
        }

        /* ── LEFT PANEL ── */
        .left-panel {
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 64px 60px;
            position: relative;
            z-index: 2;
            border-right: 0 !important;
            overflow: hidden;
            background: transparent !important;
        }
        .left-panel::before {
            
            position: absolute;
            top: 42px;
            right: clamp(52px, 6vw, 94px);
            z-index: 6;
            display: block;
            color: #9b5b72;
            font-family: var(--ui-font);
            font-size: 14px;
            font-weight: 800;
            letter-spacing: 0.01em;
        }
        .left-panel-bg {
            position: absolute;
            inset: -14px;
            z-index: 0;
            background:
                radial-gradient(circle at 12% 88%, rgba(200,177,161,0.22), transparent 24%),
                radial-gradient(circle at 24% 18%, rgba(255,255,255,0.8), transparent 28%),
                linear-gradient(145deg, rgba(255,249,245,0.94), rgba(248,236,227,0.84));
            filter: none;
            transform: none;
            pointer-events: none;
        }
        .left-panel-bg::after {
            content: "SUPPLIER ONBOARDING\A-----\A\AThis information helps us personalize your experience on Golden Promise.";
            position: absolute;
            left: clamp(36px, 6vw, 78px);
            top: calc(48% - 172px);
            width: min(30vw, 320px);
            display: block;
            white-space: pre-line;
            color: #9b7289;
            font-family: var(--ui-font);
            font-size: 12px;
            font-weight: 500;
            line-height: 1.7;
            letter-spacing: 0.02em;
        }
        .split[data-step="2"] .left-panel-bg::after {
            content: "SUPPLIER ONBOARDING\A-----\A\AHelp couples understand your services better.";
        }
        .split[data-step="3"] .left-panel-bg::after {
            content: "SUPPLIER ONBOARDING\A-----\A\AWe'll use this to reach out to you when needed.";
        }
        .split[data-step="4"] .left-panel-bg::after {
            content: "SUPPLIER ONBOARDING\A-----\A\AUpload your business materials to get verified on Golden Promise.";
        }
        
        
            
        
        
        .left-panel::after {
            display: none;
        }
        .brand,
        .supplier-flash,
        .step-counter-left,
        .step-panel {
            z-index: 2;
        }
        .agreement-mode .left-panel {
            min-height: 100vh;
            border-right: 0;
            height: 100vh;
            padding: 0;
        }
        .agreement-mode .left-panel::before,
        .agreement-mode .left-panel-bg::after,
        .agreement-mode .right-panel {
            display: none;
        }
        .split:not(.agreement-mode) .right-panel {
            display: none;
        }

        .brand {
            position: absolute;
            top: 34px; left: clamp(34px, 4vw, 64px);
            display: flex; align-items: center; gap: 14px;
        }
        .brand::before {
            content: "";
            width: 72px;
            height: 72px;
            background: url("<?= URLROOT ?>/public/images/home/gp_logo.png") center/contain no-repeat;
            box-shadow: none;
        }
        .brand-name {
            font-family: "Playfair Display", Georgia, serif;
            width: 118px;
            font-size: 18px;
            font-weight: 600;
            line-height: 0.9;
            color: #9b7289;
            transform: translateX(-16px);
        }

        .step-counter-left {
            position: absolute;
            bottom: 48px; left: clamp(92px, 10vw, 150px);
            font-family: var(--ui-font);
            font-size: 12px; font-weight: 800;
            letter-spacing: 0.1em;
            color: #7a3f56;
            display: flex; align-items: center; gap: 8px;
        }
        .step-counter-left::before {
            display: none;
        }
        .split[data-step="1"] .step-counter-left::before { content: "STEP 2 OF 5"; }
        .split[data-step="2"] .step-counter-left::before { content: "STEP 3 OF 5"; }
        .split[data-step="3"] .step-counter-left::before { content: "STEP 4 OF 5"; }
        .split[data-step="4"] .step-counter-left::before { content: "STEP 5 OF 5"; }
        .step-dots {
            display: flex; gap: 10px;
        }
        .step-dot {
            width: 8px; height: 8px;
            border-radius: 50%;
            border: 1px solid #d8beb0;
            background: transparent;
            transition: background 0.35s, transform 0.35s;
        }
        .step-dot.active {
            border-color: #9b4f68;
            background: #9b4f68;
            transform: scale(1.05);
        }
        .step-dot.done {
            border-color: #c8b1a1;
            background: #c8b1a1;
        }
        .home-link { display: none; }
        .agreement-mode .home-link { display: inline; }
        .agreement-mode .step-counter-left::before { display: none; }

        /* ── Step panel (question area) ── */
        .step-panel {
            position: absolute;
            top: 0;
            right: clamp(70px, 8vw, 128px);
            bottom: 0;
            left: 56.5%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            max-width: 590px;
            padding: 112px 0 86px;
            transition: opacity 0.45s cubic-bezier(0.4,0,0.2,1),
                        transform 0.45s cubic-bezier(0.4,0,0.2,1),
                        filter 0.45s cubic-bezier(0.4,0,0.2,1);
        }
        .step-panel.hidden-panel {
            opacity: 0;
            pointer-events: none;
            transform: translateY(20px);
            filter: blur(4px);
        }
        .step-panel.enter-panel {
            opacity: 0;
            transform: translateY(-16px);
            filter: blur(4px);
        }

        .eyebrow {
            font-family: var(--ui-font);
            font-size: 13px; font-weight: 800;
            letter-spacing: 0.18em; text-transform: uppercase;
            color: #9a586b;
            margin-bottom: 22px;
            display: block;
        }
        .question {
            max-width: 540px;
            font-family: Georgia, "Times New Roman", serif;
            font-size: clamp(30px, 2.6vw, 42px);
            font-weight: 700;
            line-height: 1.16;
            color: #7a3f56;
            margin-bottom: 16px;
            font-style: normal;
            letter-spacing: 0;
        }
        .hint {
            font-family: var(--ui-font);
            max-width: 500px;
            font-size: 16px; line-height: 1.55;
            color: #8c8582;
            margin-bottom: 32px;
        }

        .split[data-step="0"] {
            background: #fff9f5;
        }
        .split[data-step="0"]::before,
        .split[data-step="0"]::after {
            display: none;
        }
        .split[data-step="0"] .left-panel {
            min-height: 100vh;
            background:
                linear-gradient(180deg, rgba(255,249,245,0.96), rgba(250,235,225,0.84));
        }
        .split[data-step="0"] .left-panel-bg,
        .split[data-step="0"] .left-panel-bg::before,
        .split[data-step="0"] .left-panel-bg::after {
            display: none;
        }
        .step-panel[data-panel="0"] {
            inset: 0;
            left: 0;
            right: 0;
            max-width: none;
            align-items: center;
            justify-content: center;
            padding: 110px 40px 90px;
            text-align: center;
            overflow: hidden;
        }
        .welcome-orbit {
            position: absolute;
            inset: 0;
            z-index: 0;
            pointer-events: none;
        }
        .orbit-ring {
            position: absolute;
            inset: 0;
        }
        .orbit-ring.inner {
            inset: 0;
        }
        .orbit-photo {
            --size: 112px;
            --tilt: 0deg;
            --float-x: 10px;
            --float-y: -12px;
            --photo-opacity: 0.72;
            --photo-blur: 0px;
            position: absolute;
            width: var(--size);
            height: var(--size);
            border-radius: 18px;
            overflow: hidden;
            background: #fffaf8;
            box-shadow: 0 20px 48px rgba(122,63,86,0.14);
            transform: translate(0, 0) rotate(var(--tilt));
            animation: scatterFloat 8s ease-in-out infinite;
            opacity: var(--photo-opacity);
        }
        .orbit-ring.inner .orbit-photo {
            --size: 96px;
            --photo-opacity: 0.48;
            --photo-blur: 1.6px;
            box-shadow: 0 16px 34px rgba(122,63,86,0.1);
        }
        .orbit-ring.outer .orbit-photo:nth-child(1) { left: 8%; top: 18%; --tilt: -12deg; animation-delay: -0.4s; }
        .orbit-ring.outer .orbit-photo:nth-child(2) { left: 22%; top: 10%; --tilt: 9deg; --float-x: -8px; animation-delay: -2.1s; }
        .orbit-ring.outer .orbit-photo:nth-child(3) { left: 43%; top: 5%; --tilt: 7deg; --float-y: 10px; animation-delay: -3.3s; --photo-opacity: 0.42; --photo-blur: 1.8px; }
        .orbit-ring.outer .orbit-photo:nth-child(4) { right: 22%; top: 10%; --tilt: -8deg; animation-delay: -1.2s; }
        .orbit-ring.outer .orbit-photo:nth-child(5) { right: 8%; top: 18%; --tilt: 12deg; --float-x: 12px; animation-delay: -4.4s; }
        .orbit-ring.outer .orbit-photo:nth-child(6) { right: 4%; top: 48%; --tilt: -10deg; --float-y: 11px; animation-delay: -2.8s; }
        .orbit-ring.outer .orbit-photo:nth-child(7) { right: 12%; bottom: 13%; --tilt: 8deg; animation-delay: -5.6s; }
        .orbit-ring.outer .orbit-photo:nth-child(8) { right: 34%; bottom: 6%; --tilt: -10deg; --float-x: -12px; animation-delay: -3.9s; --photo-opacity: 0.44; --photo-blur: 1.4px; }
        .orbit-ring.outer .orbit-photo:nth-child(9) { left: 34%; bottom: 6%; --size: 102px; --tilt: 10deg; --float-x: 14px; animation-delay: -6.8s; --photo-opacity: 0.44; --photo-blur: 1.4px; }
        .orbit-ring.outer .orbit-photo:nth-child(10) { left: 12%; bottom: 13%; --size: 108px; --tilt: -9deg; --float-y: 14px; animation-delay: -1.9s; }
        .orbit-ring.outer .orbit-photo:nth-child(11) { left: 4%; top: 48%; --size: 96px; --tilt: 9deg; --float-x: -13px; animation-delay: -5.1s; }
        .orbit-ring.outer .orbit-photo:nth-child(12) { left: 49%; bottom: 4%; --size: 94px; --tilt: -6deg; --float-y: 13px; animation-delay: -7.2s; --photo-opacity: 0.32; --photo-blur: 2.4px; }
        .orbit-ring.inner .orbit-photo:nth-child(1) { left: 28%; top: 27%; --tilt: 8deg; animation-delay: -1.5s; }
        .orbit-ring.inner .orbit-photo:nth-child(2) { right: 28%; top: 27%; --tilt: -10deg; --float-y: 9px; animation-delay: -4.7s; }
        .orbit-ring.inner .orbit-photo:nth-child(3) { left: 24%; top: 51%; --tilt: -12deg; animation-delay: -2.6s; }
        .orbit-ring.inner .orbit-photo:nth-child(4) { right: 24%; top: 51%; --tilt: 10deg; --float-x: -9px; animation-delay: -0.9s; }
        .orbit-ring.inner .orbit-photo:nth-child(5) { left: 47%; top: 22%; --tilt: 5deg; --float-y: 10px; animation-delay: -6.1s; --photo-opacity: 0.3; --photo-blur: 2.8px; }
        .orbit-ring.inner .orbit-photo:nth-child(6) { left: 18%; top: 38%; --size: 84px; --tilt: -6deg; --float-x: 10px; animation-delay: -3.4s; }
        .orbit-ring.inner .orbit-photo:nth-child(7) { right: 18%; top: 38%; --size: 88px; --tilt: 12deg; --float-y: -10px; animation-delay: -5.8s; }
        .orbit-ring.inner .orbit-photo:nth-child(8) { left: 47%; top: 64%; --size: 80px; --tilt: -8deg; --float-x: -11px; animation-delay: -2.2s; --photo-opacity: 0.32; --photo-blur: 2px; }
        .orbit-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            filter: blur(var(--photo-blur)) saturate(0.92) sepia(0.06);
        }
        .welcome-copy {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            align-items: center;
            width: min(100%, 720px);
        }
        .step-panel[data-panel="0"] .eyebrow {
            margin-bottom: 26px;
            color: #8f5b69;
            letter-spacing: 0.16em;
        }
        .step-panel[data-panel="0"] .question {
            max-width: none;
            margin-bottom: 28px;
            color: #5f3b47;
            font-family: var(--ui-font);
            font-size: clamp(24px, 2.35vw, 14px);
            font-weight: 600;
            line-height: 1.1;
        }
        .step-panel[data-panel="0"] .btn-row {
            justify-content: center;
            max-width: none;
            margin-top: 0;
            margin-bottom: 24px;
        }
        .step-panel[data-panel="0"] .btn-next {
            width: auto;
            min-width: min(72vw, 560px);
            min-height: 96px;
            border-radius: 999px;
            padding: 0 clamp(38px, 6vw, 84px);
            background: #7a3f56;
            color: #fffaf8;
            font-size: clamp(22px, 3vw, 40px);
            font-weight: 400;
            letter-spacing: 0;
            box-shadow: 0 24px 64px rgba(122,63,86,0.2);
        }
        .step-panel[data-panel="0"] .btn-next:hover {
            background: #8f5267;
        }
        .step-panel[data-panel="0"] .email-chip {
            margin: 0;
            min-width: min(78vw, 420px);
            justify-content: center;
            border-radius: 999px;
            padding: 16px 24px;
            background: rgba(255,255,255,0.72);
            border-color: rgba(200,177,161,0.48);
            color: #6d4c5b;
            font-size: 14px;
            box-shadow: 0 18px 44px rgba(122,63,86,0.08);
            backdrop-filter: blur(8px);
        }

        /* ── Inputs ── */
        .q-input {
            width: 100%;
            max-width: 590px;
            background: var(--input-bg);
            border: 1px solid #eadbd6;
            border-radius: 8px;
            padding: 0 26px;
            min-height: 64px;
            font-size: 17px;
            font-family: var(--ui-font);
            color: var(--ink);
            outline: none;
            box-shadow: none;
            transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;
            margin-bottom: 8px;
        }
        .q-input:focus {
            border-color: #9b4f68;
            background: var(--focus-color);
            box-shadow: 0 0 0 4px rgba(155,79,104,0.1);
        }
        .q-input::placeholder {
            color: #9b9491;
            font-style: normal;
        }
        .q-input.textarea-input {
            resize: none;
            min-height: 154px;
            font-size: 16px;
            line-height: 1.55;
            padding-top: 18px;
            padding-bottom: 18px;
        }
        .q-input.story-input {
            min-height: 112px;
            background: #fffaf7;
            border-color: rgb(200 177 161 / 0.72);
            box-shadow: 0 16px 34px rgb(109 76 91 / 0.08);
        }
        .q-input.small-input {
            font-size: 17px;
        }

        .field-group { margin-bottom: 20px; }
        .field-label {
            font-family: var(--ui-font);
            font-size: 14px; font-weight: 800;
            letter-spacing: 0; text-transform: none;
            color: #7a3f56;
            display: block; margin-bottom: 10px;
        }

        /* ── Category tiles ── */
        .choice-grid {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 24px;
            max-width: 560px;
        }
        .choice-grid::before {
            content: "Suggestions:";
            flex: 0 0 100%;
            font-family: var(--ui-font);
            font-size: 13px;
            font-weight: 700;
            line-height: 1.1;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--ink-muted);
            text-align: left;
        }
        .choice-grid.is-hidden {
            display: none;
        }
        .choice-tile {
            min-height: 38px;
            background: var(--input-bg);
            border: 1px solid var(--env-border);
            border-radius: 999px;
            padding: 0 16px;
            cursor: pointer;
            text-align: center;
            font-family: inherit;
            transition: border-color 0.18s, background 0.18s, transform 0.12s;
            display: inline-flex; align-items: center; justify-content: center;
            box-shadow: none;
        }
        .choice-tile:hover {
            border-color: var(--accent);
            background: var(--paper-panel);
            transform: translateY(-1px);
        }
        .choice-tile.selected {
            border-color: var(--accent);
            background: var(--gold-soft);
            box-shadow: inset 0 0 0 1px var(--gold);
        }
        .choice-tile.suggested {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px var(--gold-soft);
        }
        .choice-tile.is-filtered-out {
            display: none;
        }
        .tile-label {
            font-size: 13px; font-weight: 700;
            color: var(--ink); font-family: var(--ui-font);
        }
        .suggestion-category-box {
            display: grid;
            gap: 12px;
            margin-bottom: 18px;
            max-width: 560px;
        }
        .category-suggest-row {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        .category-suggestion-note {
            font-family: var(--ui-font);
            font-size: 12px;
            line-height: 1.5;
            color: var(--ink-muted);
        }
        .btn-suggest {
            align-self: start;
            border: 1px solid var(--env-border);
            border-radius: 999px;
            background: var(--paper-light);
            color: var(--accent);
            cursor: pointer;
            font-family: var(--ui-font);
            font-size: 12px;
            font-weight: 700;
            padding: 10px 16px;
            transition: border-color 0.15s, background 0.15s, transform 0.1s;
        }
        .btn-suggest:hover {
            border-color: var(--accent);
            background: var(--gold-soft);
            transform: translateY(-1px);
        }

        /* ── Buttons ── */
        .btn-row {
            display: flex; align-items: center; justify-content: flex-end; gap: 16px;
            width: 100%;
            max-width: 590px;
            margin-top: 52px;
        }
        .btn-next {
            width: 100%;
            min-width: 0;
            min-height: 70px;
            background: #8f4f65;
            color: var(--paper-light); border: none;
            border-radius: 8px;
            padding: 0 34px;
            font-family: var(--ui-font);
            font-size: 22px; font-weight: 700;
            letter-spacing: 0.03em;
            cursor: pointer;
            box-shadow: 0 14px 28px rgba(109,76,91,0.24);
            transition: background 0.15s, transform 0.1s, box-shadow 0.15s;
        }
        .btn-next:hover { background: #7a3f56; transform: translateY(-1px); }
        .btn-next:active { transform: translateY(0); }
        .btn-next:disabled { opacity: 0.38; cursor: not-allowed; transform: none; }
        .btn-next.submit-btn { background: var(--accent); }
        .btn-next.submit-btn:hover { background: var(--accent-hover); }

        .btn-back {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 108px;
            min-height: 46px;
            background: #fffaf8;
            border: 1px solid #eadbd6;
            border-radius: 8px;
            font-family: var(--ui-font);
            font-size: 13px; color: var(--ink-muted);
            cursor: pointer; padding: 0 20px;
            transition: color 0.15s;
        }
        .btn-back:hover { color: var(--accent); }

        .enter-hint {
            display: none;
        }
        kbd {
            background: var(--gold-soft);
            border: 1px solid var(--env-border);
            border-radius: 3px; padding: 1px 5px;
            font-size: 10px; font-family: var(--ui-font);
            color: var(--accent);
        }

        /* ── Step 4 profile material inputs ── */
        .profile-materials {
            display: grid;
            grid-template-columns: minmax(0, 1.08fr) minmax(0, 0.92fr);
            gap: 14px;
            max-width: 560px;
            margin: 2px 0 22px;
        }
        .upload-zone {
            height: 176px;
            min-height: 176px;
            max-height: 176px;
            border: 1px solid rgb(200 177 161 / 0.82);
            border-radius: 16px;
            padding: 14px;
            cursor: pointer;
            transition: border-color 0.18s, background 0.18s, transform 0.18s, box-shadow 0.18s;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
            background: #fffaf7;
            box-shadow: 0 18px 36px rgb(109 76 91 / 0.1);
        }
        .upload-zone:hover,
        .upload-zone.drag-over {
            border-color: var(--accent);
            background: #fff6f0;
            box-shadow: 0 20px 42px rgb(109 76 91 / 0.15);
            transform: translateY(-1px);
        }
        .upload-zone:focus-within {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px var(--gold-soft), 0 18px 36px rgb(109 76 91 / 0.1);
        }
        .upload-zone.photo-zone {
            height: 210px;
            min-height: 210px;
            max-height: 210px;
            padding: 0;
        }
        .photo-preview {
            position: relative;
            flex: 0 0 128px;
            height: 128px;
            min-height: 128px;
            max-height: 128px;
            background: #f3e6dd;
            overflow: hidden;
        }
        .photo-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: none;
        }
        .upload-zone.has-file .photo-preview img {
            display: block;
        }
        .photo-preview-mark {
            position: absolute;
            inset: 16px;
            border: 1px dashed rgb(109 76 91 / 0.42);
            border-radius: 12px;
            display: grid;
            place-items: center;
            color: var(--accent);
        }
        .upload-zone.has-file .photo-preview-mark {
            display: none;
        }
        .upload-zone-icon {
            width: 38px;
            height: 38px;
            border-radius: 999px;
            display: inline-grid;
            place-items: center;
            background: rgb(109 76 91 / 0.1);
            color: var(--accent);
        }
        .upload-zone-icon svg {
            width: 19px;
            height: 19px;
            stroke-width: 1.8;
        }
        .upload-copy {
            flex: 0 0 auto;
            padding: 12px 14px 14px;
        }
        .photo-zone .upload-copy {
            height: 82px;
        }
        .license-zone {
            height: 210px;
            min-height: 210px;
            max-height: 210px;
            padding: 0;
        }
        .license-zone .upload-copy {
            padding-left: 14px;
            padding-right: 14px;
            padding-top: 1px;
            padding-bottom: 8px;
        }
        .license-preview {
            flex: 0 0 112px;
            height: 112px;
            min-height: 128px;
            max-height: 128px;
            margin: 0;
            width: 100%;
            border-radius: 16px 16px 0 0;
        }
        .license-preview .photo-preview-mark {
            inset: 10px;
            border-radius: 10px;
        }
        .upload-kicker {
            display: block;
            margin-bottom: 6px;
            font-family: var(--ui-font);
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--accent-hover);
        }
        .upload-title {
            display: block;
            font-family: var(--ui-font);
            font-size: 14px;
            font-weight: 800;
            line-height: 1.25;
            color: var(--accent);
        }
        .upload-meta,
        .file-name {
            display: block;
            margin-top: 5px;
            font-family: var(--ui-font);
            font-size: 11px;
            line-height: 1.45;
            color: var(--ink-muted);
        }
        .file-name {
            height: 16px;
            font-weight: 500;
            color: var(--accent);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .license-zone .file-name {
            margin-top: 0;
            margin-bottom: 4px;
            font-size: 12px;
        }
        .license-zone {
            gap: 16px;
        }
        .license-card-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        .license-lines {
            display: grid;
            gap: 7px;
            margin-top: 14px;
        }
        .license-lines span {
            height: 7px;
            border-radius: 999px;
            background: rgb(200 177 161 / 0.34);
        }
        .license-lines span:nth-child(2) {
            width: 78%;
        }
        .license-lines span:nth-child(3) {
            width: 52%;
        }

        /* Clean onboarding steps: branding + wave + form */
.split[data-step="1"],
.split[data-step="2"],
.split[data-step="3"],
.split[data-step="4"] {
    display: grid;
    grid-template-columns: 44% 56%;
    background: #fff9f5;
}

.split[data-step="1"]::before,
.split[data-step="2"]::before,
.split[data-step="3"]::before,
.split[data-step="4"]::before {
    display: block;
    position: absolute;
    left: clamp(52px, 6vw, 90px);
    top: 38%;
    transform: translateY(-50%);
    width: min(34vw, 420px);

    font-family: "Playfair Display", serif;
    font-size: clamp(32px, 3vw, 44px);
    font-weight: 600;
    line-height: 1.18;
    letter-spacing: 0;
    text-align: center;

    color: #7a3f56;
    z-index: 8;
}

.split[data-step="1"]::before {
    content: "Become a Verified\AWedding Supplier";
}

.split[data-step="4"]::before {
    white-space: nowrap;
}

.split[data-step="1"]::after,
.split[data-step="2"]::after,
.split[data-step="3"]::after,
.split[data-step="4"]::after {
    display: block;
    left: calc(44% - 128px);
    width: 190px;
    z-index: 6;
    opacity: 1;
    filter: none;
    animation: none;
    transform: scaleX(-1);
    transform-origin: center;
    background:
        url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 220 1000' preserveAspectRatio='none'%3E%3Cpath fill='%23fff9f5' d='M0 0H90C145 70 150 170 105 255C65 340 70 435 118 520C160 610 155 710 110 800C70 885 78 955 120 1000H0Z'/%3E%3C/svg%3E") center/100% 100% no-repeat,
        url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 220 1000' preserveAspectRatio='none'%3E%3Cpath fill='%23ead8cf' d='M0 0H90C145 70 150 170 105 255C65 340 70 435 118 520C160 610 155 710 110 800C70 885 78 955 120 1000H0Z'/%3E%3C/svg%3E") calc(50% + 10px) 0/100% 100% no-repeat;
}

.split[data-step="1"] .right-panel,
.split[data-step="2"] .right-panel,
.split[data-step="3"] .right-panel,
.split[data-step="4"] .right-panel {
    display: none !important;
}

.split[data-step="1"] .left-panel,
.split[data-step="2"] .left-panel,
.split[data-step="3"] .left-panel,
.split[data-step="4"] .left-panel {
    grid-column: 1 / -1;
    position: relative;
    min-height: 100vh;
    
}

.split[data-step="1"] .left-panel-bg,
.split[data-step="2"] .left-panel-bg,
.split[data-step="3"] .left-panel-bg,
.split[data-step="4"] .left-panel-bg {
    inset: 0;
    background:
        linear-gradient(90deg, #fff5ef 0%, #f7e1d7 44%, #fff9f5 44%, #fff9f5 100%);
}

.split[data-step="1"] .left-panel-bg::before,
.split[data-step="2"] .left-panel-bg::before,
.split[data-step="3"] .left-panel-bg::before,
.split[data-step="4"] .left-panel-bg::before {
    content: "";
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 44%;
    background: url("<?= URLROOT ?>/public/images/onboarding/supplier-left-bg.jpg?v=<?= $leftBgVersion ?>") center / cover no-repeat;
    opacity: 0.12;
    pointer-events: none;
}

.split[data-step="1"] .left-panel::before,
.split[data-step="2"] .left-panel::before,
.split[data-step="3"] .left-panel::before,
.split[data-step="4"] .left-panel::before {
    display: block;
    position: absolute;
    left: clamp(52px, 6vw, 90px);
    right: auto;
    top: calc(38% - 116px);
    width: min(34vw, 420px);
    color: #9a586b;
    font-family: var(--ui-font);
    font-size: 13px;
    font-weight: 800;
    line-height: 1.4;
    letter-spacing: 0.18em;
    text-align: center;
    text-transform: uppercase;
    z-index: 9;
    pointer-events: none;
}

.split[data-step="1"] .left-panel::before {
    content: "STEP 1 OF 5 - BUSINESS IDENTITY";
}

.split[data-step="2"] .left-panel::before {
    content: "STEP 2 OF 5 - BUSINESS CATEGORIES";
}

.split[data-step="3"] .left-panel::before {
    content: "STEP 3 OF 5 - CONTACT";
}

.split[data-step="4"] .left-panel::before {
    content: "STEP 4 OF 5 - YOUR STORY";
}

.split[data-step="1"] .step-panel[data-panel="1"] .eyebrow,
.split[data-step="2"] .step-panel[data-panel="2"] .eyebrow,
.split[data-step="3"] .step-panel[data-panel="3"] .eyebrow,
.split[data-step="4"] .step-panel[data-panel="4"] .eyebrow {
    display: none;
}

.split[data-step="1"] .left-panel-bg::after,
.split[data-step="2"] .left-panel-bg::after,
.split[data-step="3"] .left-panel-bg::after,
.split[data-step="4"] .left-panel-bg::after {
    content: "This information helps us personalize\A your experience on Golden Promise.";
    display: block;
    left: clamp(52px, 6vw, 90px);
    top: calc(38% + 96px);
    width: min(34vw, 420px);
    color: #74656b;
    font-family: var(--ui-font);
    font-size: clamp(15px, 1.25vw, 19px);
    font-weight: 500;
    line-height: 1.65;
    letter-spacing: 0;
    white-space: pre-line;
    text-align: center;
}

.split[data-step="2"] .left-panel-bg::after {
    content: "Help couples understand what your business\A can provide for their day.";
}

.split[data-step="3"] .left-panel-bg::after {
    content: "Share the details clients need\A to reach and trust your business.";
}

.split[data-step="4"] .left-panel-bg::after {
    content: "Tell your story and upload documents\A so we can verify your profile.";
}

.split[data-step="1"] .left-panel::before,
.split[data-step="1"] .left-panel-bg::after {
    animation: leftTextEnter1 0.5s ease both;
}

.split[data-step="2"] .left-panel::before,
.split[data-step="2"] .left-panel-bg::after {
    animation: leftTextEnter2 0.5s ease both;
}

.split[data-step="3"] .left-panel::before,
.split[data-step="3"] .left-panel-bg::after {
    animation: leftTextEnter3 0.5s ease both;
}

.split[data-step="4"] .left-panel::before,
.split[data-step="4"] .left-panel-bg::after {
    animation: leftTextEnter4 0.5s ease both;
}

.split[data-step="1"]::before {
    animation: leftTitleEnter1 0.5s ease both;
}

.split[data-step="2"]::before {
    animation: leftTitleEnter2 0.5s ease both;
}

.split[data-step="3"]::before {
    animation: leftTitleEnter3 0.5s ease both;
}

.split[data-step="4"]::before {
    animation: leftTitleEnter4 0.5s ease both;
}

.split[data-step="1"] .step-panel[data-panel="1"],
.split[data-step="2"] .step-panel[data-panel="2"],
.split[data-step="3"] .step-panel[data-panel="3"],
.split[data-step="4"] .step-panel[data-panel="4"] {
    position: absolute;
    left: 54%;
    right: clamp(56px, 7vw, 110px);
    top: 0;
    bottom: 0;
    max-width: 560px;
    z-index: 10;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.split[data-step="2"] .step-panel[data-panel="2"] .question {
    max-width: none;
    white-space: nowrap;
    font-size: clamp(24px, 2.15vw, 34px);
}

.split[data-step="4"] .step-panel[data-panel="4"] .question {
    white-space: nowrap;
}

.split[data-step="1"] .step-panel[data-panel="1"] .btn-next,
.split[data-step="2"] .step-panel[data-panel="2"] .btn-next,
.split[data-step="3"] .step-panel[data-panel="3"] .btn-next,
.split[data-step="4"] .step-panel[data-panel="4"] .btn-next {
    min-height: 52px;
    border-radius: 10px;
    font-size: 16px;
}
        @media (max-width: 980px) {
            .profile-materials {
                grid-template-columns: 1fr;
            }
        }

        /* ── Agreement ── */
        .agreement-scroll {
            border: 1px solid var(--env-border);
            border-radius: 8px;
            padding: 14px 16px;
            max-width: 560px;
            max-height: 180px;
            overflow-y: auto;
            font-size: 12px;
            line-height: 1.75;
            color: var(--ink-muted);
            font-family: var(--ui-font);
            margin-bottom: 16px;
            background: var(--paper-light);
        }
        .agreement-scroll::-webkit-scrollbar { width: 3px; }
        .agreement-scroll::-webkit-scrollbar-thumb { background: var(--gold); border-radius: 4px; }

        .agree-check-row {
            display: flex; align-items: flex-start; gap: 10px;
            font-family: var(--ui-font); font-size: 13px;
            line-height: 1.6; color: var(--ink);
            cursor: pointer; margin-bottom: 28px;
        }
        .agree-check-row input[type="checkbox"] {
            width: 15px; height: 15px; margin-top: 2px;
            accent-color: var(--accent); flex-shrink: 0;
        }
        .agreement-panel {
            position: fixed;
            inset: 0;
            left: 0;
            right: 0;
            max-width: none;
            justify-content: flex-start;
            padding: 54px clamp(28px, 5vw, 72px);
            overflow: hidden;
        }
        .agreement-shell {
            width: min(100%, 760px);
            margin: 0 auto;
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 0;
            align-items: start;
            justify-content: center;
            max-height: calc(100vh - 108px);
        }
        .agreement-document,
        .agreement-aside {
            border: 1px solid var(--env-border);
            border-radius: 14px;
            background: var(--paper-light);
            box-shadow: 0 1px 3px rgb(0 0 0 / 0.06);
        }
        .agreement-document {
            overflow: hidden;
            border-color: var(--accent);
            box-shadow: 0 18px 45px rgb(109 76 91 / 0.12);
            display: flex;
            flex-direction: column;
            max-height: calc(100vh - 108px);
        }
        .agreement-document-head {
            flex: 0 0 auto;
            padding: 14px 22px;
            border-bottom: 1px solid var(--env-border);
            background: var(--paper-panel);
        }
        .agreement-panel .eyebrow {
            display: block;
        }
        .agreement-panel .question {
            max-width: 760px;
            margin-bottom: 5px;
            font-family: var(--ui-font);
            font-size: clamp(22px, 2.4vw, 28px);
            font-weight: 800;
            color: var(--accent);
        }
        .agreement-panel .hint {
            max-width: 720px;
            margin-bottom: 0;
            font-size: 12px;
            color: var(--ink-muted);
        }
        .agreement-panel .agreement-scroll {
            max-width: none;
            flex: 1 1 auto;
            max-height: none;
            min-height: 0;
            margin: 0;
            border: 0;
            border-radius: 0;
            background: #FFFFFF;
            padding: 18px 24px;
            font-size: 13px;
            line-height: 1.72;
            color: var(--ink);
        }
        .agreement-panel .agreement-scroll strong {
            display: inline-block;
            margin-bottom: 3px;
            color: var(--accent);
            font-size: 15px;
        }
        .agreement-footer {
            flex: 0 0 auto;
            padding: 12px 22px 14px;
            border-top: 1px solid var(--env-border);
            background: var(--paper-light);
        }
        .agreement-panel .agree-check-row {
            margin-bottom: 10px;
        }
        .agreement-panel .btn-row {
            justify-content: space-between;
            width: 100%;
            max-width: none;
            margin-top: 4px;
        }
        .agreement-panel .btn-next {
            width: 242px;
            min-width: 0;
            min-height: 0;
            border-radius: 5px;
            padding: 11px 14px;
            background: var(--accent);
            font-size: 13px;
            box-shadow: 0 1px 3px rgb(0 0 0 / 0.1);
        }
        .agreement-panel .btn-next:hover {
            background: var(--accent-hover);
        }
        .agreement-panel .btn-back {
            min-width: 0;
            min-height: 0;
            padding: 0;
            border: 0;
            background: none;
            border-radius: 0;
        }
        .agreement-aside {
            display: none;
        }
        .agreement-aside-title {
            font-family: var(--ui-font);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--ink);
            margin-bottom: 10px;
        }
        .agreement-summary {
            display: grid;
            gap: 10px;
        }
        .agreement-summary-item {
            border: 1px solid var(--env-border);
            border-radius: 12px;
            background: #FFFFFF;
            padding: 10px;
        }
        .agreement-summary-item span {
            display: block;
            margin-bottom: 4px;
            font-family: var(--ui-font);
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--ink-muted);
        }
        .agreement-summary-item strong {
            display: block;
            font-family: var(--ui-font);
            font-size: 12px;
            line-height: 1.45;
            color: var(--ink);
            word-break: break-word;
        }

        /* ── Error ── */
        .step-error {
            font-family: var(--ui-font);
            font-size: 12px;
            color: var(--danger);
            margin-bottom: 12px;
            display: none;
        }
        .step-error.visible { display: block; }

        /* ── Email readonly ── */
        .email-chip {
            display: inline-flex; align-items: center; gap: 8px;
            background: var(--paper-light);
            border: 1px solid var(--env-border);
            border-radius: 100px;
            padding: 8px 16px;
            font-family: var(--ui-font);
            font-size: 13px; color: var(--accent);
            margin-bottom: 36px;
            box-shadow: 0 1px 3px rgb(0 0 0 / 0.1);
        }
        .email-chip svg { width: 13px; height: 13px; flex-shrink: 0; }

        /* ── Red string divider ── */
        .fate-string-divider {
            display: none;
        }
        .fate-string-divider svg {
            width: 100%;
            height: 100%;
            display: block;
            overflow: visible;
        }
        .fate-string-shadow,
        .fate-string-main,
        .fate-string-highlight,
        .fate-string-fiber,
        .fate-heart-thread {
            fill: none;
            stroke-linecap: round;
            stroke-linejoin: round;
        }
        .fate-string-shadow {
            stroke: rgb(62 12 18 / 0.14);
            stroke-width: 4.6;
            filter: blur(1.4px);
        }
        .fate-string-main {
            stroke: #9f1020;
            stroke-width: 1.9;
            filter: drop-shadow(1px 3px 4px rgb(64 14 20 / 0.26));
        }
        .fate-string-highlight {
            stroke: #f0a4a0;
            stroke-width: 0.55;
            opacity: 0.52;
        }
        .fate-string-fiber {
            stroke: rgb(92 8 17 / 0.34);
            stroke-width: 0.55;
            stroke-dasharray: 1 8;
            opacity: 0.7;
        }
        .fate-heart-thread {
            stroke: #9f1020;
            stroke-width: 1.85;
            filter: drop-shadow(2px 4px 6px rgb(70 18 24 / 0.2));
        }

        /* ── RIGHT PANEL (loose home-style photo collage) ── */
        .right-panel {
            position: relative;
            overflow: hidden;
            background: #ead8c9;
        }

        .right-panel::before,
        .right-panel::after {
            display: none;
        }

        .image-slide {
            position: absolute;
            inset: 0;
            opacity: 0;
            transform: translateY(105%);
            z-index: 1;
            pointer-events: none;
            visibility: hidden;
            transition: none;
        }
        .image-slide.active {
            opacity: 1;
            transform: translateY(0);
            z-index: 3;
            visibility: visible;
            transition: transform 1.15s cubic-bezier(0.22,1,0.36,1);
        }
        .image-slide.leaving {
            opacity: 1;
            transform: translateY(-105%);
            z-index: 2;
            visibility: visible;
            transition: transform 1.15s cubic-bezier(0.22,1,0.36,1);
        }

        .img-cell {
            position: absolute;
            overflow: hidden;
            border: 0.5px solid rgba(252,248,245,0.72);
            border-radius: 10px;
            box-shadow: 0 18px 45px rgb(0 0 0 / 0.06);
            opacity: 1;
            padding: 4px;
            transform: translateY(0) scale(1);
            background: rgba(252,248,245,0.52);
        }
        .img-cell.span-col,
        .img-cell.span-row { grid-column: auto; grid-row: auto; }

        .img-cell:nth-child(1) {
            left: 20%;
            top: 8%;
            width: 48%;
            height: 26%;
            z-index: 2;
        }
        .img-cell:nth-child(2) {
            right: 14%;
            top: 39%;
            width: 35%;
            height: 24%;
            z-index: 4;
        }
        .img-cell:nth-child(3) {
            left: 15%;
            top: 68%;
            width: 52%;
            height: 24%;
            z-index: 3;
        }
        .img-cell:nth-child(4) {
            right: 18%;
            top: 98%;
            width: 40%;
            height: 22%;
            z-index: 5;
        }
        .image-slide:nth-of-type(2) .img-cell:nth-child(1),
        .image-slide:nth-of-type(5) .img-cell:nth-child(1) {
            left: 24%;
            top: 12%;
        }
        .image-slide:nth-of-type(2) .img-cell:nth-child(2),
        .image-slide:nth-of-type(5) .img-cell:nth-child(2) {
            right: 20%;
            top: 44%;
        }
        .image-slide:nth-of-type(2) .img-cell:nth-child(3),
        .image-slide:nth-of-type(5) .img-cell:nth-child(3) {
            left: 20%;
            top: 74%;
        }
        .image-slide:nth-of-type(3) .img-cell:nth-child(1),
        .image-slide:nth-of-type(6) .img-cell:nth-child(1) {
            left: 16%;
            top: 10%;
            width: 42%;
        }
        .image-slide:nth-of-type(3) .img-cell:nth-child(2),
        .image-slide:nth-of-type(6) .img-cell:nth-child(2) {
            right: 12%;
            top: 36%;
            width: 39%;
        }
        .image-slide:nth-of-type(3) .img-cell:nth-child(3),
        .image-slide:nth-of-type(6) .img-cell:nth-child(3) {
            left: 18%;
            top: 69%;
            width: 47%;
        }
        .image-slide:nth-of-type(4) .img-cell:nth-child(1),
        .image-slide:nth-of-type(7) .img-cell:nth-child(1) {
            left: 28%;
            top: 7%;
            width: 40%;
        }
        .image-slide:nth-of-type(4) .img-cell:nth-child(2),
        .image-slide:nth-of-type(7) .img-cell:nth-child(2) {
            right: 16%;
            top: 41%;
        }
        .image-slide:nth-of-type(4) .img-cell:nth-child(3),
        .image-slide:nth-of-type(7) .img-cell:nth-child(3) {
            left: 13%;
            top: 71%;
            width: 54%;
        }

        .img-cell img {
            width: 100%; height: 100%;
            object-fit: cover;
            display: block;
            border-radius: 7px;
            transform: scale(1);
            transition: none;
            filter: saturate(0.94) contrast(0.98) sepia(0.04);
        }
        
        
        @keyframes waveBreath {
            0%, 100% {
                transform: translateX(0) scaleX(1);
            }
            50% {
                transform: translateX(3px) scaleX(1.015);
            }
        }
        @keyframes scatterFloat {
            0%, 100% {
                transform: translate(0, 0) rotate(var(--tilt));
            }
            50% {
                transform: translate(var(--float-x), var(--float-y)) rotate(calc(var(--tilt) + 4deg));
            }
        }
        @keyframes leftTextEnter1 {
            from {
                opacity: 0;
                transform: translateY(8px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        @keyframes leftTextEnter2 {
            from {
                opacity: 0;
                transform: translateY(8px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        @keyframes leftTextEnter3 {
            from {
                opacity: 0;
                transform: translateY(8px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        @keyframes leftTextEnter4 {
            from {
                opacity: 0;
                transform: translateY(8px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        @keyframes leftTitleEnter1 {
            from {
                opacity: 0;
                transform: translateY(calc(-50% + 8px));
            }
            to {
                opacity: 1;
                transform: translateY(-50%);
            }
        }
        @keyframes leftTitleEnter2 {
            from {
                opacity: 0;
                transform: translateY(calc(-50% + 8px));
            }
            to {
                opacity: 1;
                transform: translateY(-50%);
            }
        }
        @keyframes leftTitleEnter3 {
            from {
                opacity: 0;
                transform: translateY(calc(-50% + 8px));
            }
            to {
                opacity: 1;
                transform: translateY(-50%);
            }
        }
        @keyframes leftTitleEnter4 {
            from {
                opacity: 0;
                transform: translateY(calc(-50% + 8px));
            }
            to {
                opacity: 1;
                transform: translateY(-50%);
            }
        }

        /* Overlay text on right panel */
        .right-overlay {
            display: none;
            position: absolute;
            left: clamp(28px, 6vw, 76px);
            bottom: clamp(36px, 7vw, 82px);
            z-index: 4;
            max-width: 440px;
        }
        .right-caption {
            font-family: var(--header-font);
            font-size: 56px;
            color: var(--paper-light);
            line-height: 1.1;
            transition: opacity 0.5s, transform 0.5s;
        }
        .right-sub {
            font-family: var(--ui-font);
            font-size: 11px; font-weight: 500;
            letter-spacing: 0.1em; text-transform: uppercase;
            color: var(--paper-light);
            margin-top: 6px;
            transition: opacity 0.5s, transform 0.5s;
        }

        .home-link {
            font-family: var(--ui-font);
            font-size: 11px;
            color: var(--accent);
            text-decoration: none;
            margin-left: 8px;
            transition: color 0.15s;
        }
        .home-link:hover {
            color: var(--accent);
        }

        /* ── Sparkle canvas (left panel only) ── */
        .sparkle-canvas {
            position: absolute;
            inset: 0; width: 100%; height: 100%;
            pointer-events: none; z-index: 5;
        }

        /* ── sr-only ── */
        .sr-only {
            position: absolute; width: 1px; height: 1px;
            padding: 0; margin: -1px; overflow: hidden;
            clip: rect(0,0,0,0); white-space: nowrap; border-width: 0;
        }

        /* ── Mobile ── */
        @media (max-width: 768px) {
            body { overflow: auto; }
            .split {
                grid-template-columns: 1fr;
                height: auto;
                min-height: 100vh;
                background: #fffdfb;
            }
            .split::before,
            .split::after,
            .left-panel::before,
            .left-panel-bg::after {
                display: none;
            }
            .right-panel { height: 44vh; order: -1; }
            .left-panel {
                min-height: 100vh;
                padding: 48px 28px 96px;
                background: #fffdfb !important;
            }
            .left-panel-bg {
                background: #fffdfb;
            }
            .step-panel {
                left: 0;
                right: 0;
                max-width: none;
                padding: 112px 28px 110px;
            }
            .agreement-mode .left-panel { height: 100vh; padding: 0; }
            .agreement-panel { position: fixed; padding: 74px 16px 24px; }
            .agreement-shell {
                grid-template-columns: 1fr;
                gap: 12px;
                max-height: calc(100vh - 98px);
                overflow: hidden;
            }
            .agreement-document { max-height: calc(100vh - 98px); }
            .agreement-aside { display: none; }
            .agreement-panel .agreement-scroll { max-height: none; }
            .brand { left: 28px; top: 28px; }
            .brand::before { width: 44px; height: 44px; }
            .brand-name { font-size: 32px; }
            .split[data-step="0"] .brand-name { font-size: 18px; }
            .step-panel[data-panel="0"] {
                min-height: 100vh;
                padding: 118px 20px 92px;
            }
            .orbit-photo {
                --size: 72px;
                border-radius: 14px;
            }
            .orbit-ring.inner .orbit-photo {
                --size: 62px;
            }
            .orbit-ring.outer .orbit-photo:nth-child(2),
            .orbit-ring.outer .orbit-photo:nth-child(4),
            .orbit-ring.outer .orbit-photo:nth-child(11),
            .orbit-ring.inner .orbit-photo:nth-child(3),
            .orbit-ring.inner .orbit-photo:nth-child(8) {
                display: none;
            }
            .step-panel[data-panel="0"] .question {
                font-size: 24px;
            }
            .step-panel[data-panel="0"] .btn-next {
                min-width: min(88vw, 360px);
                min-height: 68px;
                font-size: clamp(22px, 6vw, 30px);
            }
            .step-counter-left { left: 28px; }
            .question { font-size: 34px; }
            .btn-row { justify-content: stretch; }
            .btn-next { width: 100%; }
            .step-panel[data-panel="0"] .btn-next { width: auto; }
            .right-caption { font-size: 42px; }
            .right-overlay { display: none; }
            .img-cell { border-radius: 12px; }
        }
    </style>
</head>
<body class="bg-app-bg font-ui text-app-text">
<div class="split bg-app-bg">

    <!-- ════════════════════════════════
         LEFT PANEL
    ════════════════════════════════ -->
    <div class="left-panel border-r border-app-border bg-app-sidebar shadow-panel" id="leftPanel">
        <div class="left-panel-bg" aria-hidden="true"></div>
        <canvas class="sparkle-canvas" id="sparkleCanvas"></canvas>

        <div class="brand">
            <span class="brand-name">Golden Promise</span>
        </div>

        <?php if (!empty($message)): ?>
        <div class="supplier-flash <?= !empty($submitted) ? 'border-app-border bg-app-soft text-app-success' : 'border-app-border bg-app-danger-soft text-app-danger' ?>" style="position:absolute;top:80px;left:48px;right:48px;padding:12px 16px;border-radius:10px;font-family:system-ui;font-size:13px;border-width:1px;border-style:solid;">
            <?= $message ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="<?= URLROOT ?>/supplier/onboarding" enctype="multipart/form-data" id="supplierOnboardingForm" novalidate>
            <?= csrf_field() ?>
            <!-- hidden email -->
            <input type="hidden" name="email" value="<?= htmlspecialchars($email ?? '', ENT_QUOTES, 'UTF-8') ?>">

            <?php $selectedCategoryIds = array_map('intval', $category_ids ?? []); ?>

            <!-- ── PANEL 0: Welcome ── -->
            <div class="step-panel" data-panel="0">
                <div class="welcome-orbit" aria-hidden="true">
                    <div class="orbit-ring outer">
                        <span class="orbit-photo" style="--angle:0deg;--size:126px"><img src="<?= URLROOT ?>/public/images/onboarding/supplier1.png" alt=""></span>
                        <span class="orbit-photo" style="--angle:45deg;--size:102px"><img src="<?= URLROOT ?>/public/images/onboarding/supplier2.png" alt=""></span>
                        <span class="orbit-photo" style="--angle:90deg;--size:138px"><img src="<?= URLROOT ?>/public/images/onboarding/supplier3.png" alt=""></span>
                        <span class="orbit-photo" style="--angle:135deg;--size:112px"><img src="<?= URLROOT ?>/public/images/onboarding/supplier4.png" alt=""></span>
                        <span class="orbit-photo" style="--angle:180deg;--size:132px"><img src="<?= URLROOT ?>/public/images/onboarding/supplier5.png" alt=""></span>
                        <span class="orbit-photo" style="--angle:225deg;--size:104px"><img src="<?= URLROOT ?>/public/images/onboarding/supplier6.jpg" alt=""></span>
                        <span class="orbit-photo" style="--angle:270deg;--size:122px"><img src="<?= URLROOT ?>/public/images/onboarding/supplier7.jpg" alt=""></span>
                        <span class="orbit-photo" style="--angle:315deg;--size:114px"><img src="<?= URLROOT ?>/public/images/onboarding/supplier8.jpg" alt=""></span>
                        <span class="orbit-photo"><img src="<?= URLROOT ?>/public/images/onboarding/supplier9.jpg" alt=""></span>
                        <span class="orbit-photo"><img src="<?= URLROOT ?>/public/images/onboarding/supplier10.jpg" alt=""></span>
                        <span class="orbit-photo"><img src="<?= URLROOT ?>/public/images/onboarding/supplier11.jpg" alt=""></span>
                        <span class="orbit-photo"><img src="<?= URLROOT ?>/public/images/onboarding/supplier12.jpg" alt=""></span>
                    </div>
                    <div class="orbit-ring inner">
                        <span class="orbit-photo" style="--angle:25deg"><img src="<?= URLROOT ?>/public/images/onboarding/supplier9.jpg" alt=""></span>
                        <span class="orbit-photo" style="--angle:97deg"><img src="<?= URLROOT ?>/public/images/onboarding/supplier10.jpg" alt=""></span>
                        <span class="orbit-photo" style="--angle:169deg"><img src="<?= URLROOT ?>/public/images/onboarding/supplier11.jpg" alt=""></span>
                        <span class="orbit-photo" style="--angle:241deg"><img src="<?= URLROOT ?>/public/images/onboarding/supplier12.jpg" alt=""></span>
                        <span class="orbit-photo" style="--angle:313deg"><img src="<?= URLROOT ?>/public/images/onboarding/supplier13.jpg" alt=""></span>
                        <span class="orbit-photo"><img src="<?= URLROOT ?>/public/images/onboarding/supplier1.png" alt=""></span>
                        <span class="orbit-photo"><img src="<?= URLROOT ?>/public/images/onboarding/supplier4.png" alt=""></span>
                        <span class="orbit-photo"><img src="<?= URLROOT ?>/public/images/onboarding/supplier8.jpg" alt=""></span>
                    </div>
                </div>
                <div class="welcome-copy">
                    <div class="eyebrow">Partner application</div>
                    <h2 class="question">Elevate Your Business.</h2>
                    <div class="btn-row">
                        <button type="button" class="btn-next js-next">Become Our Partner</button>
                    </div>
                    <div class="email-chip">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        <?= htmlspecialchars($email ?? '', ENT_QUOTES, 'UTF-8') ?>
                    </div>
                </div>
            </div>

            <!-- ── PANEL 1: Business name ── -->
            <div class="step-panel hidden-panel" data-panel="1">
                <div class="eyebrow">Step 1 of 5 — Business identity</div>
                <h2 class="question">What is your<br>business name?</h2>
                <p class="hint">This will be displayed on your profile.</p>
                <input class="q-input" name="business_name" type="text" placeholder="Enter your business name"
                       value="<?= htmlspecialchars($business_name ?? '', ENT_QUOTES, 'UTF-8') ?>" required autocomplete="organization">
                <div class="step-error" id="err1"></div>
                <div class="btn-row">
                    <button type="button" class="btn-next js-next">Continue</button>
                    <button type="button" class="btn-back js-back">← Back</button>
                </div>
            </div>

            <!-- ── PANEL 2: Category ── -->
            <div class="step-panel hidden-panel" data-panel="2">
                <div class="eyebrow">Step 2 of 5 — Business categories</div>
                <h2 class="question">What can your business provide?</h2>
                <p class="hint">Type a service, description, or Myanmar keyword.</p>
                <div class="suggestion-category-box">
                    <textarea class="q-input textarea-input" id="categoryPrompt" name="category_prompt"
                              placeholder="e.g. We rent bridal dresses, accessories, and provide pre-wedding studio photos."><?= htmlspecialchars($category_prompt ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                    <div class="category-suggest-row">
                        <button type="button" class="btn-suggest" id="suggestCategoryBtn">Show suggestions</button>
                        <span class="category-suggestion-note" id="categorySuggestionNote"></span>
                    </div>
                </div>
                <div class="choice-grid <?= empty($selectedCategoryIds) ? 'is-hidden' : '' ?>" id="categoryTiles">
                    <?php
                    foreach (($categories ?? []) as $cat):
                        $cid  = (int)$cat['id'];
                        $cname = htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8');
                        $isSelected = in_array($cid, $selectedCategoryIds, true);
                    ?>
                    <button type="button" class="choice-tile <?= $isSelected ? 'selected' : '' ?>"
                            data-cid="<?= $cid ?>"
                            data-name="<?= $cname ?>"
                            aria-pressed="<?= $isSelected ? 'true' : 'false' ?>">
                        <input type="checkbox" name="category_ids[]" value="<?= $cid ?>" class="sr-only category-check" <?= $isSelected ? 'checked' : '' ?>>
                        <span class="tile-label"><?= $cname ?></span>
                    </button>
                    <?php endforeach; ?>
                </div>
                <div class="step-error" id="err2"></div>
                <div class="btn-row">
                    <button type="button" class="btn-next js-next" id="catNextBtn" <?= !empty($selectedCategoryIds) ? '' : 'disabled' ?>>Continue</button>
                    <button type="button" class="btn-back js-back">← Back</button>
                </div>
            </div>

            <!-- ── PANEL 3: Contact ── -->
            <div class="step-panel hidden-panel" data-panel="3">
                <div class="eyebrow">Step 3 of 5 — Contact</div>
                <h2 class="question">How can clients<br>reach you?</h2>
                <p class="hint">Verification details.</p>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:8px">
                    <div class="field-group">
                        <label class="field-label">Phone number</label>
                        <input class="q-input small-input" name="phone" type="tel" inputmode="numeric"
                               pattern="[0-9]{11}" minlength="11" maxlength="11"
                               title="Phone number must be exactly 11 digits."
                               placeholder="09xxxxxxxxx"
                               value="<?= htmlspecialchars($phone ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                    <div class="field-group">
                        <label class="field-label">Business address</label>
                        <input class="q-input small-input" name="business_address" type="text"
                               placeholder="City or area"
                               value="<?= htmlspecialchars($business_address ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                </div>
                <div class="field-group">
                    <label class="field-label">Website or social link</label>
                    <input class="q-input small-input" name="business_url" type="url"
                           placeholder="https://example.com"
                           value="<?= htmlspecialchars($business_url ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                </div>
                <div class="step-error" id="err3"></div>
                <div class="btn-row">
                    <button type="button" class="btn-next js-next">Continue</button>
                    <button type="button" class="btn-back js-back">← Back</button>
                </div>
            </div>

            <!-- ── PANEL 4: Description + uploads ── -->
            <div class="step-panel hidden-panel" data-panel="4">
                <div class="eyebrow">Step 4 of 5 — Your story</div>
                <h2 class="question">Describe your business</h2>
                <p class="hint">Profile and documents.</p>
                <div class="field-group">
                    <label class="field-label">Business description</label>
                    <textarea class="q-input textarea-input story-input" name="business_description"
                              placeholder="Tell us what your business provides..." required><?= htmlspecialchars($business_description ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>
                <div class="profile-materials">
                    <label class="upload-zone photo-zone" id="coverDropZone" for="coverPhotoInput">
                        <input id="coverPhotoInput" name="cover_photo" type="file"
                               accept="image/jpeg,image/png,image/webp" class="sr-only" required>
                        <div class="photo-preview" aria-hidden="true">
                            <img id="coverPreviewImg" alt="">
                            <div class="photo-preview-mark">
                                <span class="upload-zone-icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path d="M12 16V4"></path>
                                        <path d="M7 9l5-5 5 5"></path>
                                        <path d="M4 17v2a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-2"></path>
                                    </svg>
                                </span>
                            </div>
                        </div>
                        <span class="upload-copy">
                            <span class="upload-kicker">Cover photo</span>
                            <span class="upload-title">Choose your best business photo</span>
                            <span class="upload-meta">JPG, PNG, WEBP · max 5 MB</span>
                            <span class="file-name" id="uploadLabel"></span>
                        </span>
                    </label>
                    <label class="upload-zone license-zone" id="licenseDropZone" for="businessLicenseInput">
                        <input id="businessLicenseInput" name="business_license" type="file"
                               accept="image/jpeg,image/png,image/webp,application/pdf" class="sr-only" required>
                        <span class="photo-preview license-preview" aria-hidden="true">
                            <img id="licensePreviewImg" alt="">
                            <span class="photo-preview-mark">
                                <span class="upload-zone-icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path d="M14 2H7a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7z"></path>
                                        <path d="M14 2v5h5"></path>
                                        <path d="M9 14h6"></path>
                                        <path d="M9 18h4"></path>
                                    </svg>
                                </span>
                            </span>
                        </span>
                        <span class="upload-copy">
                            <span class="file-name" id="licenseLabel"></span>
                            <span class="upload-title">Upload business license</span>
                            <span class="upload-meta">PDF, JPG, PNG, WEBP · max 5 MB</span>
                        </span>
                        <span class="license-lines" aria-hidden="true">
                            <span></span>
                            <span></span>
                            <span></span>
                        </span>
                    </label>
                </div>
                <div class="step-error" id="err4"></div>
                <div class="btn-row">
                    <button type="button" class="btn-next js-next">Continue</button>
                    <button type="button" class="btn-back js-back">← Back</button>
                </div>
            </div>

            <!-- ── PANEL 5: Agreement + submit ── -->
            <div class="step-panel agreement-panel hidden-panel" data-panel="5">
                <div class="agreement-shell">
                    <section class="agreement-document">
                        <div class="agreement-document-head">
                            <div class="eyebrow">Step 5 of 5 — Supplier agreement</div>
                            <h2 class="question">Review the agreement before submitting</h2>
                            <p class="hint">This agreement controls membership fees, service quality, cancellations, payment handling, and platform rules.</p>
                        </div>
                        <div class="agreement-scroll">
                            <strong>1. Membership Fees</strong><br>Supplier Member အဖြစ် စတင်လက်တွဲရန် သတ်မှတ်ထားသော Members Fees ကို ကြိုတင်ပေးသွင်းရမည်။<br><br>
                            <strong>2. Service Fees</strong><br>ပေးချေငွေ၏ 10% ကို Admin Service Charge အဖြစ် ကောက်ခံမည်။<br><br>
                            <strong>3. Booking Cancelation Policy</strong><br>အချိန်တစ်ဝက်အလိုတွင် Cancel ပြုလုပ်ပါက Package တန်ဖိုး၏ 50%, တစ်ဝက်ကျော်ပါက 100% လျော်ကြေးပေးဆောင်ရမည်။<br><br>
                            <strong>4. Excessive Cancelation</strong><br>Booking Cancelation 3 ကြိမ်ထက်ကျော်ပါက Member အဖြစ်မှ ဖယ်ရှားမည်။<br><br>
                            <strong>5. Customer Reviews</strong><br>Bad Review 5 ကြိမ်ထက်ကျော်ပါက Member အဖြစ်မှ ဖယ်ရှားမည်။<br><br>
                            <strong>6. Package Participation</strong><br>Member ဝင်ပြီး 3 လအတွင်း Booking 5 ကြိမ် ရရှိထားရမည်။<br><br>
                            <strong>7. Bonus Program</strong><br>အရောင်းရဆုံး နံပါတ် (1) Supplier ဖြစ်ပါက Bonus ချီးမြှင့်ပေးမည်။<br><br>
                            <strong>8. Payment Terms</strong><br>Admin Service Fees နှင့် Charges များ နုတ်ယူပြီးမှ ကျန်ရှိသောငွေကို Supplier ထံ လွှဲပြောင်းပေးမည်။<br><br>
                            <strong>9. Supplier Responsibilities</strong><br>Service Quality နှင့် အချိန်တိကျမှုကို တာဝန်ယူရမည်။<br><br>
                            <strong>10. Fraud & Policy Violations</strong><br>Platform ပြင်ပ Customer များနှင့် တိုက်ရိုက်ဆက်သွယ်ပြီး ငွေလက်ခံခြင်း မပြုရ။<br><br>
                            <strong>11. Marketing & Content Usage</strong><br>Admin Team မှ Photo, Video, Content များကို Marketing ရည်ရွယ်ချက်တွက် အသုံးပြုခွင့်ရှိသည်။<br><br>
                            <strong>12. Price Control</strong><br>Booking Confirmed ပြီးနောက် Package Price ပြောင်းလဲခြင်း မပြုလုပ်ရ။<br><br>
                            <strong>13. Confidentiality</strong><br>Customer ၏ Personal Information များကို ခွင့်ပြုချက်မရှိဘဲ မျှဝေခြင်း မပြုရ။<br><br>
                            <strong>14. Force Majeure</strong><br>ထိန်းချုပ်မရသော အခြေအနေများကြောင့် Service မပေးနိုင်ပါက နှစ်ဖက်စလုံးအား တာဝန်ကင်းလွတ်ခွင့် ရှိသည်။
                        </div>
                        <div class="agreement-footer">
                            <label class="agree-check-row">
                                <input name="agreement_accepted" type="checkbox" value="1" required <?= !empty($agreement_accepted) ? 'checked' : '' ?>>
                                <span>I have read and agree to the Golden Promise supplier business agreement.</span>
                            </label>
                            <div class="step-error" id="err5"></div>
                            <div class="btn-row">
                                <button type="button" class="btn-back js-back">← Back to application</button>
                                <button type="submit" class="btn-next submit-btn" id="submitBtn">Submit application</button>
                            </div>
                        </div>
                    </section>

                    <aside class="agreement-aside">
                        <h3 class="agreement-aside-title">Application summary</h3>
                        <div class="agreement-summary">
                            <div class="agreement-summary-item">
                                <span>Business</span>
                                <strong id="agreementBusinessName">-</strong>
                            </div>
                            <div class="agreement-summary-item">
                                <span>Contact</span>
                                <strong id="agreementPhone">-</strong>
                            </div>
                            <div class="agreement-summary-item">
                                <span>Business link</span>
                                <strong id="agreementBusinessUrl">-</strong>
                            </div>
                            <div class="agreement-summary-item">
                                <span>Agreement version</span>
                                <strong>supplier-v1</strong>
                            </div>
                        </div>
                    </aside>
                </div>
            </div>

        </form><!-- /form -->

        <!-- Dot nav -->
        <div class="step-counter-left">
            <div class="step-dots" id="stepDots"></div>
            <a href="<?= URLROOT ?>/main/home" class="home-link">Back home</a>
        </div>
    </div><!-- /left-panel -->

    <!-- ════════════════════════════════
         RIGHT PANEL (imagery)
    ════════════════════════════════ -->
    <div class="right-panel" id="rightPanel">

        <div class="fate-string-divider" aria-hidden="true">
            <svg viewBox="0 0 150 900" preserveAspectRatio="none" focusable="false">
                <path class="fate-string-shadow" d="M0 0 C6 126 38 202 78 272 C122 350 122 396 70 452 C24 502 40 562 88 634 C126 690 112 768 68 822 C30 864 8 888 0 900" />
                <path class="fate-string-main" d="M0 0 C6 126 38 202 78 272 C122 350 122 396 70 452 C24 502 40 562 88 634 C126 690 112 768 68 822 C30 864 8 888 0 900" />
                <path class="fate-string-highlight" d="M1.5 0 C8 124 40 200 80 270 C124 348 124 396 72 452 C26 502 42 560 90 632 C128 690 114 770 70 824 C32 866 10 890 1.5 900" />
                <path class="fate-string-fiber" d="M-1.5 0 C4 128 36 204 76 274 C120 352 120 396 68 452 C22 504 38 564 86 636 C124 688 110 766 66 820 C28 862 6 886 -1.5 900" />
                <path class="fate-heart-thread" d="M60 454 C39 454 32 433 49 425 C69 416 82 436 77 464 C73 435 87 402 110 411 C136 421 123 462 78 490 C61 501 55 482 76 467 C93 455 112 458 139 468" />
            </svg>
        </div>

        <!-- Each slide corresponds to a step. Uses local supplier onboarding images. -->
        <!-- Slide 0: Welcome -->
        <div class="image-slide active" data-slide="0">
            <div class="img-cell span-col">
                <img src="<?= URLROOT ?>/public/images/onboarding/supplier1.png" alt="">
            </div>
            <div class="img-cell">
                <img src="<?= URLROOT ?>/public/images/onboarding/supplier2.png" alt="">
            </div>
            <div class="img-cell">
                <img src="<?= URLROOT ?>/public/images/onboarding/supplier3.png" alt="">
            </div>
        </div>

        <!-- Slide 1: Business name -->
        <div class="image-slide" data-slide="1">
            <div class="img-cell span-row">
                <img src="<?= URLROOT ?>/public/images/onboarding/supplier4.png" alt="">
            </div>
            <div class="img-cell">
                <img src="<?= URLROOT ?>/public/images/onboarding/supplier5.png" alt="">
            </div>
            <div class="img-cell">
                <img src="<?= URLROOT ?>/public/images/onboarding/supplier6.jpg" alt="">
            </div>
        </div>

        <!-- Slide 2: Category -->
        <div class="image-slide" data-slide="2">
            <div class="img-cell">
                <img src="<?= URLROOT ?>/public/images/onboarding/supplier7.jpg" alt="">
            </div>
            <div class="img-cell">
                <img src="<?= URLROOT ?>/public/images/onboarding/supplier8.jpg" alt="">
            </div>
            <div class="img-cell">
                <img src="<?= URLROOT ?>/public/images/onboarding/supplier9.jpg" alt="">
            </div>
            <div class="img-cell">
                <img src="<?= URLROOT ?>/public/images/onboarding/supplier10.jpg" alt="">
            </div>
        </div>

        <!-- Slide 3: Service -->
        <div class="image-slide" data-slide="3">
            <div class="img-cell span-col">
                <img src="<?= URLROOT ?>/public/images/onboarding/supplier11.jpg" alt="">
            </div>
            <div class="img-cell">
                <img src="<?= URLROOT ?>/public/images/onboarding/supplier12.jpg" alt="">
            </div>
            <div class="img-cell">
                <img src="<?= URLROOT ?>/public/images/onboarding/supplier13.jpg" alt="">
            </div>
        </div>

        <!-- Slide 4: Contact -->
        <div class="image-slide" data-slide="4">
            <div class="img-cell">
                <img src="<?= URLROOT ?>/public/images/onboarding/supplier1.png" alt="">
            </div>
            <div class="img-cell">
                <img src="<?= URLROOT ?>/public/images/onboarding/supplier5.png" alt="">
            </div>
            <div class="img-cell span-col">
                <img src="<?= URLROOT ?>/public/images/onboarding/supplier9.jpg" alt="">
            </div>
        </div>

        <!-- Slide 5: Story -->
        <div class="image-slide" data-slide="5">
            <div class="img-cell span-row">
                <img src="<?= URLROOT ?>/public/images/onboarding/supplier2.png" alt="">
            </div>
            <div class="img-cell">
                <img src="<?= URLROOT ?>/public/images/onboarding/supplier6.jpg" alt="">
            </div>
            <div class="img-cell">
                <img src="<?= URLROOT ?>/public/images/onboarding/supplier10.jpg" alt="">
            </div>
        </div>

        <!-- Slide 6: Agreement -->
        <div class="image-slide" data-slide="6">
            <div class="img-cell span-col">
                <img src="<?= URLROOT ?>/public/images/onboarding/supplier3.png" alt="">
            </div>
            <div class="img-cell">
                <img src="<?= URLROOT ?>/public/images/onboarding/supplier7.jpg" alt="">
            </div>
            <div class="img-cell">
                <img src="<?= URLROOT ?>/public/images/onboarding/supplier11.jpg" alt="">
            </div>
        </div>

        <!-- Captions -->
        <div class="right-overlay">
            <div class="right-caption" id="rightCaption">Begin your journey</div>
            <div class="right-sub" id="rightSub">Golden Promise · Partner Program</div>
        </div>
    </div><!-- /right-panel -->

</div><!-- /split -->

<script>
(() => {
    const TOTAL = 6;
    const split    = document.querySelector('.split');
    const panels   = Array.from(document.querySelectorAll('[data-panel]'));
    const slides   = Array.from(document.querySelectorAll('[data-slide]'));
    const dotsWrap = document.getElementById('stepDots');
    const caption  = document.getElementById('rightCaption');
    const sub      = document.getElementById('rightSub');
    const form     = document.getElementById('supplierOnboardingForm');
    const catTiles  = document.getElementById('categoryTiles');
    const catNextBtn = document.getElementById('catNextBtn');
    const categoryPrompt = document.getElementById('categoryPrompt');
    const suggestCategoryBtn = document.getElementById('suggestCategoryBtn');
    const categorySuggestionNote = document.getElementById('categorySuggestionNote');
    const coverInput = document.getElementById('coverPhotoInput');
    const coverDrop  = document.getElementById('coverDropZone');
    const uploadLabel = document.getElementById('uploadLabel');
    const coverPreviewImg = document.getElementById('coverPreviewImg');
    const licenseInput = document.getElementById('businessLicenseInput');
    const licenseDrop = document.getElementById('licenseDropZone');
    const licenseLabel = document.getElementById('licenseLabel');
    const licensePreviewImg = document.getElementById('licensePreviewImg');
    const phoneInput = form.querySelector('[name="phone"]');
    const submitBtn  = document.getElementById('submitBtn');
    const sparkleCanvas = document.getElementById('sparkleCanvas');
    const sparkleCtx = sparkleCanvas.getContext('2d');
    const agreementBusinessName = document.getElementById('agreementBusinessName');
    const agreementPhone = document.getElementById('agreementPhone');
    const agreementBusinessUrl = document.getElementById('agreementBusinessUrl');

    const CAPTIONS = [
        ['Begin your journey',           'Golden Promise · Partner Program'],
        ['Your name, your brand',         'Business identity'],
        ['Choose your specialties',       'Business categories'],
        ['Be found by the right couples', 'Contact details'],
        ['Your story matters',           'Profile & documents'],
        ['Almost there',                 'Review & agree'],
    ];

    const draftKey = 'gp_sup_v2_' + encodeURIComponent((form.querySelector('[name="email"]') || {}).value || 'g');
    const draftFields = ['business_name','business_description','phone','business_address','category_prompt','category_ids[]','business_url','agreement_accepted'];

    let current = 0;
    let busy = false;
    const pm = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // ── Dots ──
    function buildDots() {
        dotsWrap.innerHTML = '';
        for (let i = 0; i < TOTAL; i++) {
            const d = document.createElement('div');
            d.className = 'step-dot' + (i === current ? ' active' : i < current ? ' done' : '');
            dotsWrap.appendChild(d);
        }
    }

    function syncAgreementSummary() {
        const businessName = form.elements.business_name?.value?.trim() || '-';
        const phone = form.elements.phone?.value?.trim() || '-';
        const businessUrl = form.elements.business_url?.value?.trim() || '-';

        if (agreementBusinessName) agreementBusinessName.textContent = businessName;
        if (agreementPhone) agreementPhone.textContent = phone;
        if (agreementBusinessUrl) agreementBusinessUrl.textContent = businessUrl;
    }

    function syncAgreementMode(idx) {
        split?.classList.toggle('agreement-mode', idx === TOTAL - 1);
        if (split) split.dataset.step = String(idx);
        document.body.style.overflow = idx === TOTAL - 1 ? 'hidden' : '';
        syncAgreementSummary();
        window.setTimeout(resizeCanvas, 60);
    }

    // ── Slides ──
    function updateSlide(idx) {
        const previous = slides.find(s => s.classList.contains('active'));
        const s = slides[idx];
        slides.forEach(slide => {
            if (slide !== previous && slide !== s) {
                slide.classList.remove('active', 'leaving');
            }
        });
        if (previous && previous !== s) {
            previous.classList.remove('active');
            previous.classList.add('leaving');
            window.setTimeout(() => previous.classList.remove('leaving'), 1250);
        }
        if (s) {
            s.classList.remove('leaving', 'active');
            void s.offsetWidth;
            s.classList.add('active');
        }
        if (caption && CAPTIONS[idx]) {
            caption.style.opacity = '0'; caption.style.transform = 'translateY(8px)';
            sub.style.opacity = '0';
            setTimeout(() => {
                caption.textContent = CAPTIONS[idx][0];
                sub.textContent     = CAPTIONS[idx][1];
                caption.style.transition = 'opacity 0.5s, transform 0.5s';
                sub.style.transition     = 'opacity 0.5s 0.1s';
                caption.style.opacity = '1'; caption.style.transform = 'translateY(0)';
                sub.style.opacity     = '1';
            }, 200);
        }
    }

    // ── Panel transition ──
    async function goTo(idx, animate = true) {
        if (busy || idx === current || idx < 0 || idx >= TOTAL) return;
        busy = true;

        const from = panels[current];
        const to   = panels[idx];

        if (animate && !pm) {
            from.style.transition = 'opacity 0.35s ease, transform 0.35s ease, filter 0.35s ease';
            from.style.opacity = '0'; from.style.transform = 'translateY(16px)'; from.style.filter = 'blur(4px)';
            await wait(320);
        }

        from.classList.add('hidden-panel');
        from.style.cssText = '';

        current = idx;
        saveDraft();
        buildDots();
        syncAgreementMode(idx);
        updateSlide(idx);
        clearErrors();

        to.classList.remove('hidden-panel');
        if (animate && !pm) {
            to.style.opacity = '0'; to.style.transform = 'translateY(-14px)'; to.style.filter = 'blur(4px)';
            await wait(20);
            to.style.transition = 'opacity 0.4s cubic-bezier(0,0,0.2,1), transform 0.4s cubic-bezier(0,0,0.2,1), filter 0.4s cubic-bezier(0,0,0.2,1)';
            to.style.opacity = '1'; to.style.transform = 'translateY(0)'; to.style.filter = 'blur(0)';
            await wait(400);
            to.style.cssText = '';
        }

        emitSparkles(12);

        // Focus first input
        const inp = to.querySelector('input:not([type=hidden]):not([type=checkbox]):not(.sr-only), textarea');
        if (inp && inp.type !== 'file') setTimeout(() => inp.focus(), 60);

        busy = false;
    }

    function wait(ms) { return new Promise(r => setTimeout(r, ms)); }

    // ── Validation ──
    function clearErrors() {
        document.querySelectorAll('.step-error').forEach(e => { e.textContent = ''; e.classList.remove('visible'); });
    }
    function showError(panelIdx, msg) {
        const el = document.getElementById('err' + panelIdx);
        if (el) { el.textContent = msg; el.classList.add('visible'); }
    }
    function validatePanel(idx) {
        const panel = panels[idx];
        const fields = Array.from(panel.querySelectorAll('input:not([type=hidden]):not(.category-check):not([aria-hidden]), select:not([aria-hidden]), textarea'));
        if (idx === 2 && !getSelectedCategoryChecks().length) {
            showError(2, 'Please select at least one business category.');
            return false;
        }
        const bad = fields.find(f => !f.checkValidity());
        if (!bad) return true;
        if (bad.type === 'file') {
            goTo(idx);
        } else if (bad.type === 'checkbox') {
            bad.focus();
        } else {
            bad.reportValidity();
            bad.focus();
        }
        showError(idx, bad.validationMessage || 'Please fill in this field.');
        return false;
    }
    function validateAll() { return panels.every((_, i) => validatePanel(i)); }

    // ── Nav delegation ──
    document.addEventListener('click', e => {
        if (busy) return;
        if (e.target.closest('.js-next')) {
            if (validatePanel(current)) goTo(current + 1);
        }
        if (e.target.closest('.js-back')) {
            goTo(current - 1);
        }
    });

    // ── Enter key ──
    form.addEventListener('keydown', e => {
        if (e.key !== 'Enter' || e.target.tagName === 'TEXTAREA' || e.target.type === 'checkbox') return;
        e.preventDefault();
        if (validatePanel(current)) goTo(current + 1);
    });

    // ── Category tiles ──
    function getSelectedCategoryChecks() {
        return Array.from(form.querySelectorAll('input[name="category_ids[]"]:checked'));
    }

    function syncCategoryNextBtn() {
        if (catNextBtn) catNextBtn.disabled = getSelectedCategoryChecks().length === 0;
    }

    function showCategoryTiles(show = true) {
        catTiles?.classList.toggle('is-hidden', !show);
    }

    function setCategoryTile(tile, checked, suggested = false) {
        const checkbox = tile.querySelector('input[type="checkbox"]');
        if (checkbox) checkbox.checked = checked;
        tile.classList.toggle('selected', checked);
        tile.classList.toggle('suggested', suggested);
        tile.setAttribute('aria-pressed', checked ? 'true' : 'false');
    }

    function applySuggestedCategoryIds(categoryIds, reason = '') {
        const ids = (categoryIds || []).map(String);
        let matches = 0;

        catTiles?.querySelectorAll('.choice-tile').forEach(tile => {
            const suggested = ids.includes(String(tile.dataset.cid));
            if (suggested) matches++;
            tile.classList.toggle('is-filtered-out', !suggested);
            setCategoryTile(tile, suggested, suggested);
        });

        showCategoryTiles(matches > 0);
        syncCategoryNextBtn();
        saveDraft();

        if (!categorySuggestionNote) return;
        categorySuggestionNote.textContent = matches ? '' : 'No matching suggestions.';
    }

    function suggestCategoriesLocal() {
        const text = (categoryPrompt?.value || '').toLowerCase();
        const keywordMap = {
            attire: ['dress','dresses','gown','bridal','bride','suit','tuxedo','outfit','attire','rental','rent','accessory','accessories','jewelry','jewellery','ring','rings','earring','necklace','tiara','veil','bouquet','shoe','shoes','ဝတ်စုံ','မင်္ဂလာဝတ်စုံ','သတို့သမီးဝတ်စုံ','ဂါဝန်','အငှား','ငှား','ဝတ်စုံအငှား','သတို့သားဝတ်စုံ','လက်ဝတ်','လက်ဝတ်ရတနာ','ရတနာ','လက်စွပ်','နားကပ်','လည်ဆွဲ','သရဖူ','ပန်းစည်း','ဖိနပ်','ဆက်စပ်ပစ္စည်း'],
            cake: ['cake','dessert','bakery','baker','tier','wedding cake','cupcake','pastry','ကိတ်','မုန့်','အချိုပွဲ','မင်္ဂလာကိတ်'],
            'food & drinks': ['food','catering','cater','buffet','meal','menu','snack','drink','beverage','bartender','bar','အစားအစာ','အစားအသောက်','ဘူဖေး','ကျွေးမွေး','သောက်စရာ','အဖျော်ယမကာ'],
            package: ['package','bundle','full service','all in one','complete','combo','planning','coordination','ပက်ကေ့ချ်','package','အစုံလိုက်','အပြီးအစီး','စီစဉ်','စီစဉ်ပေး','မင်္ဂလာအစီအစဉ်'],
            studio: ['studio','photo','photography','photographer','video','portrait','pre-wedding','pre wedding','shoot','camera','album','စတူဒီယို','ဓာတ်ပုံ','ဓါတ်ပုံ','ဗီဒီယို','ရိုက်ကူး','ပုံရိုက်','မင်္ဂလာဓာတ်ပုံ','prewedding','အယ်လ်ဘမ်'],
            venue: ['venue','hall','hotel','garden','ballroom','room','space','reception','ceremony location','နေရာ','ခန်းမ','ဟိုတယ်','ဥယျာဉ်','မင်္ဂလာခန်းမ','ဧည့်ခံပွဲ','နေရာငှား','အခန်း']
        };
        let matches = 0;
        const hasText = text.trim().length >= 2;

        catTiles?.querySelectorAll('.choice-tile').forEach(tile => {
            const name = (tile.dataset.name || '').toLowerCase();
            const words = keywordMap[name] || [name];
            const suggested = hasText && words.some(word => text.includes(word));
            if (suggested) matches++;
            tile.classList.toggle('is-filtered-out', hasText && !suggested);
            setCategoryTile(tile, suggested, suggested);
        });

        showCategoryTiles(hasText && matches > 0);
        syncCategoryNextBtn();
        saveDraft();

        if (!categorySuggestionNote) return;
        if (!hasText) {
            categorySuggestionNote.textContent = '';
            return;
        }
        categorySuggestionNote.textContent = matches ? '' : 'No matching suggestions.';
    }

    let activeSuggestRequest = null;
    let lastSuggestedPrompt = '';

    async function suggestCategories({silent = false} = {}) {
        const prompt = (categoryPrompt?.value || '').trim();

        if (prompt.length < 2) {
            suggestCategoriesLocal();
            return;
        }

        if (prompt === lastSuggestedPrompt && getSelectedCategoryChecks().length > 0) {
            return;
        }

        lastSuggestedPrompt = prompt;

        if (activeSuggestRequest) {
            activeSuggestRequest.abort();
        }

        activeSuggestRequest = new AbortController();

        if (!silent && categorySuggestionNote) {
            categorySuggestionNote.textContent = 'Suggesting...';
        }

        try {
            const response = await fetch('<?= URLROOT ?>/supplier/suggestCategories', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ prompt }),
                signal: activeSuggestRequest.signal,
            });
            const result = await response.json();

            if (result.status !== 'success') {
                throw new Error(result.message || 'Suggestion failed.');
            }

            applySuggestedCategoryIds(result.category_ids || [], result.reason || '');
        } catch (error) {
            if (error.name === 'AbortError') return;
            suggestCategoriesLocal();
            if (!silent && categorySuggestionNote) {
                categorySuggestionNote.textContent += categorySuggestionNote.textContent ? ' Try a different word.' : '';
            }
        } finally {
            activeSuggestRequest = null;
        }
    }

    catTiles?.addEventListener('click', e => {
        const tile = e.target.closest('.choice-tile');
        if (!tile) return;
        const checkbox = tile.querySelector('input[type="checkbox"]');
        if (!checkbox) return;
        setCategoryTile(tile, !checkbox.checked, false);
        showCategoryTiles(true);
        syncCategoryNextBtn();
        saveDraft();
    });

    suggestCategoryBtn?.addEventListener('click', suggestCategories);
    let suggestTimer = null;
    categoryPrompt?.addEventListener('input', () => {
        window.clearTimeout(suggestTimer);
        suggestTimer = window.setTimeout(() => suggestCategories({silent: true}), 900);
    });

    // ── Phone ──
    phoneInput?.addEventListener('input', () => {
        phoneInput.value = phoneInput.value.replace(/\D/g,'').slice(0,11);
        phoneInput.setCustomValidity(phoneInput.value.length === 11 ? '' : 'Phone number must be exactly 11 digits.');
    });

    // ── File upload ──
    function updateFileCard(input, dropZone, label, emptyText, previewImg = null) {
        const file = input?.files?.[0];
        dropZone?.classList.toggle('has-file', Boolean(file));
        if (label) label.textContent = file ? file.name : emptyText;

        if (!previewImg) return;
        if (!file || !file.type.startsWith('image/')) {
            previewImg.removeAttribute('src');
            return;
        }

        const reader = new FileReader();
        reader.onload = event => {
            previewImg.src = event.target.result;
        };
        reader.readAsDataURL(file);
    }

    coverInput?.addEventListener('change', () => {
        updateFileCard(coverInput, coverDrop, uploadLabel, 'No photo selected', coverPreviewImg);
    });
    licenseInput?.addEventListener('change', () => {
        updateFileCard(licenseInput, licenseDrop, licenseLabel, 'No license selected', licensePreviewImg);
    });
    ['dragenter','dragover'].forEach(ev => coverDrop?.addEventListener(ev, e => { e.preventDefault(); coverDrop.classList.add('drag-over'); }));
    ['dragleave','drop'].forEach(ev => coverDrop?.addEventListener(ev, e => { e.preventDefault(); coverDrop.classList.remove('drag-over'); }));
    coverDrop?.addEventListener('drop', e => {
        const f = e.dataTransfer.files[0]; if (!f) return;
        const dt = new DataTransfer(); dt.items.add(f); coverInput.files = dt.files;
        coverInput.dispatchEvent(new Event('change', {bubbles:true}));
    });
    ['dragenter','dragover'].forEach(ev => licenseDrop?.addEventListener(ev, e => { e.preventDefault(); licenseDrop.classList.add('drag-over'); }));
    ['dragleave','drop'].forEach(ev => licenseDrop?.addEventListener(ev, e => { e.preventDefault(); licenseDrop.classList.remove('drag-over'); }));
    licenseDrop?.addEventListener('drop', e => {
        const f = e.dataTransfer.files[0]; if (!f) return;
        const dt = new DataTransfer(); dt.items.add(f); licenseInput.files = dt.files;
        licenseInput.dispatchEvent(new Event('change', {bubbles:true}));
    });
    // ── Draft ──
    function getField(name) { const f = form.elements[name]; if (!f) return null; return typeof f.addEventListener==='function' ? f : (f[0]||null); }
    function saveDraft() {
        const d = { current, fields: {} };
        draftFields.forEach(n => {
            if (n === 'category_ids[]') {
                d.fields[n] = getSelectedCategoryChecks().map(f => f.value);
                return;
            }
            const f = getField(n); if (!f) return; d.fields[n] = f.type==='checkbox' ? f.checked : f.value;
        });
        try { localStorage.setItem(draftKey, JSON.stringify(d)); } catch(_) {}
    }
    function restoreDraft() {
        let d = {}; try { d = JSON.parse(localStorage.getItem(draftKey))||{}; } catch(_) {}
        if (!d.fields) return 0;
        draftFields.forEach(n => {
            if (n === 'category_ids[]') {
                const selected = Array.isArray(d.fields[n]) ? d.fields[n].map(String) : [];
                catTiles?.querySelectorAll('.choice-tile').forEach(t => {
                    const checked = selected.includes(String(t.dataset.cid));
                    setCategoryTile(t, checked, false);
                });
                return;
            }
            const f = getField(n); if (!f || d.fields[n]==null) return; if (f.type==='checkbox') { f.checked = d.fields[n]===true; return; } f.value = d.fields[n];
        });
        syncCategoryNextBtn();
        const s = parseInt(d.current, 10);
        if (!Number.isInteger(s)) return 0;

        // Browsers clear file inputs after refresh, so never restore past the upload step.
        return Math.min(Math.max(s, 0), 4);
    }
    draftFields.forEach(n => {
        if (n === 'category_ids[]') return;
        const f = getField(n); if (f) { f.addEventListener('input', saveDraft); f.addEventListener('change', saveDraft); }
    });
    ['business_name','phone','business_url'].forEach(name => {
        const f = getField(name);
        if (f) {
            f.addEventListener('input', syncAgreementSummary);
            f.addEventListener('change', syncAgreementSummary);
        }
    });

    // ── Submit ──
    form.addEventListener('submit', async e => {
        e.preventDefault();
        if (current < TOTAL - 1) { if (validatePanel(current)) goTo(current+1); return; }
        if (!validateAll()) return;
        submitBtn.disabled = true; submitBtn.textContent = 'Submitting…';
        try {
            const r = await fetch(form.action, { method:'POST', headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}, body: new FormData(form) });
            const responseText = await r.text();
            let res = null;

            try {
                res = JSON.parse(responseText);
            } catch (_) {
                if (r.redirected || responseText.toLowerCase().includes('<!doctype') || responseText.toLowerCase().includes('<html')) {
                    throw new Error('The server returned a page instead of JSON. Please make sure you are still signed in and the uploaded files are under 5MB.');
                }

                throw new Error(responseText.trim() || 'The server response could not be read.');
            }

            if (res.status === 'success') {
                try { localStorage.removeItem(draftKey); } catch(_) {}
                window.location.href = res.redirect || '<?= URLROOT ?>/supplier/pending';
                return;
            }
            showError(current, res.message || 'Please check your information and try again.');
        } catch(error) {
            showError(current, error.message || 'Something went wrong. Please try again.');
        } finally {
            submitBtn.disabled = false; submitBtn.textContent = 'Submit application ✦';
        }
    });

    // ── Sparkles ──
    const particles = [];
    const themeStyles = getComputedStyle(document.documentElement);
    const sparkleColors = ['--gold', '--gold-soft', '--accent']
        .map(name => themeStyles.getPropertyValue(name).trim())
        .filter(Boolean);
    function resizeCanvas() { sparkleCanvas.width = sparkleCanvas.parentElement.offsetWidth; sparkleCanvas.height = sparkleCanvas.parentElement.offsetHeight; }
    class Particle {
        constructor() {
            this.x = Math.random() * sparkleCanvas.width;
            this.y = Math.random() * sparkleCanvas.height;
            const a = Math.random()*Math.PI*2, sp = 0.5+Math.random()*1.2;
            this.vx = Math.cos(a)*sp; this.vy = Math.sin(a)*sp - 0.3;
            this.life = 1; this.decay = 0.018+Math.random()*0.022;
            this.size = 0.5+Math.random()*1.2;
            this.color = sparkleColors[Math.floor(Math.random()*sparkleColors.length)] || '#6d4c5b';
        }
        update() { this.x+=this.vx; this.y+=this.vy; this.vy+=0.02; this.life-=this.decay; }
        draw(ctx) { ctx.globalAlpha=Math.max(0,this.life); ctx.fillStyle=this.color; ctx.beginPath(); ctx.arc(this.x,this.y,this.size,0,Math.PI*2); ctx.fill(); }
    }
    function emitSparkles(n) { for (let i=0;i<n;i++) particles.push(new Particle()); }
    function loop() {
        sparkleCtx.clearRect(0,0,sparkleCanvas.width,sparkleCanvas.height);
        for (let i=particles.length-1;i>=0;i--) { particles[i].update(); particles[i].draw(sparkleCtx); if(particles[i].life<=0) particles.splice(i,1); }
        requestAnimationFrame(loop);
    }
    resizeCanvas(); window.addEventListener('resize', resizeCanvas); loop();

    // ── Init ──
    const start = restoreDraft();
    current = start;
    syncCategoryNextBtn();
    updateFileCard(coverInput, coverDrop, uploadLabel, 'No photo selected', coverPreviewImg);
    updateFileCard(licenseInput, licenseDrop, licenseLabel, 'No license selected', licensePreviewImg);
    showCategoryTiles(getSelectedCategoryChecks().length > 0);
    buildDots();
    syncAgreementMode(start);
    updateSlide(start);
    panels.forEach((p, i) => p.classList.toggle('hidden-panel', i !== start));
})();
</script>
</body>
</html>
