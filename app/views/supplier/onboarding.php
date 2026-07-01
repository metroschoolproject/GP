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
            grid-template-columns: 48fr 50fr;
            height: 100vh;
            position: relative;
            transition: grid-template-columns 0.35s ease;
        }
        .split.agreement-mode {
            grid-template-columns: 1fr;
            overflow-y: auto;
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
        }
        .left-panel-bg {
            position: absolute;
            inset: -14px;
            z-index: 0;
            background:
                linear-gradient(rgb(245 232 217 / 0.16), rgb(250 245 239 / 0.28)),
                url("<?= URLROOT ?>/public/images/onboarding/supplier-left-bg.jpg?v=<?= $leftBgVersion ?>") center / cover no-repeat;
            filter: blur(3.5px) saturate(1.02);
            transform: scale(1.02);
            pointer-events: none;
        }
        .left-panel-bg::after {
            content: "";
            position: absolute;
            inset: 14px;
            background:
                radial-gradient(circle at 20% 26%, rgb(255 255 255 / 0.18), transparent 28%),
                linear-gradient(90deg, rgb(250 245 239 / 0.34), rgb(245 232 217 / 0.18));
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
            padding: 42px 60px 82px;
        }
        .agreement-mode .right-panel {
            display: none;
        }

        .brand {
            position: absolute;
            top: 32px; left: 48px;
            display: flex; align-items: center; gap: 10px;
        }
        .brand-name {
            font-family: var(--header-font);
            font-size: 34px;
            color: var(--accent);
        }

        .step-counter-left {
            position: absolute;
            bottom: 32px; left: 48px;
            font-family: var(--ui-font);
            font-size: 11px; font-weight: 500;
            letter-spacing: 0.1em;
            color: var(--ink-muted);
            display: flex; align-items: center; gap: 12px;
        }
        .step-dots {
            display: flex; gap: 6px;
        }
        .step-dot {
            width: 5px; height: 5px;
            border-radius: 50%;
            background: var(--gold-soft);
            transition: background 0.35s, transform 0.35s;
        }
        .step-dot.active {
            background: var(--accent);
            transform: scale(1.3);
        }
        .step-dot.done {
            background: var(--gold);
        }

        /* ── Step panel (question area) ── */
        .step-panel {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 80px 80px 80px;
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
            font-size: 11px; font-weight: 700;
            letter-spacing: 0.12em; text-transform: uppercase;
            color: var(--accent-hover);
            margin-bottom: 16px;
        }
        .question {
            max-width: 540px;
            font-size: 32px; font-weight: 800;
            line-height: 1.16;
            color: var(--accent);
            margin-bottom: 10px;
            font-style: normal;
            letter-spacing: 0;
        }
        .hint {
            font-family: var(--ui-font);
            max-width: 500px;
            font-size: 14px; line-height: 1.5;
            color: var(--ink-muted);
            margin-bottom: 28px;
        }

        /* ── Inputs ── */
        .q-input {
            width: 100%;
            max-width: 560px;
            background: var(--input-bg);
            border: 1px solid var(--env-border);
            border-radius: 14px;
            padding: 16px 18px;
            font-size: 16px;
            font-family: var(--ui-font);
            color: var(--ink);
            outline: none;
            box-shadow: none;
            transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;
            margin-bottom: 8px;
        }
        .q-input:focus {
            border-color: var(--accent);
            background: var(--focus-color);
            box-shadow: 0 0 0 3px var(--gold-soft);
        }
        .q-input::placeholder {
            color: var(--ink-muted);
            font-style: normal;
        }
        .q-input.textarea-input {
            resize: none;
            min-height: 96px;
            font-size: 16px;
            line-height: 1.55;
        }
        .q-input.small-input {
            font-size: 17px;
        }

        .field-group { margin-bottom: 20px; }
        .field-label {
            font-family: var(--ui-font);
            font-size: 10px; font-weight: 600;
            letter-spacing: 0.1em; text-transform: uppercase;
            color: var(--ink-muted);
            display: block; margin-bottom: 4px;
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
            display: flex; align-items: center; gap: 16px;
            margin-top: 4px;
        }
        .btn-next {
            background: var(--accent);
            color: var(--paper-light); border: none;
            border-radius: 999px;
            padding: 13px 28px;
            font-family: var(--ui-font);
            font-size: 13px; font-weight: 600;
            letter-spacing: 0.03em;
            cursor: pointer;
            box-shadow: 0 1px 3px rgb(0 0 0 / 0.1);
            transition: background 0.15s, transform 0.1s, box-shadow 0.15s;
        }
        .btn-next:hover { background: var(--accent-hover); transform: translateY(-1px); }
        .btn-next:active { transform: translateY(0); }
        .btn-next:disabled { opacity: 0.38; cursor: not-allowed; transform: none; }
        .btn-next.submit-btn { background: var(--accent); }
        .btn-next.submit-btn:hover { background: var(--accent-hover); }

        .btn-back {
            background: none; border: none;
            font-family: var(--ui-font);
            font-size: 13px; color: var(--ink-muted);
            cursor: pointer; padding: 0;
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

        /* ── Upload zone ── */
        .upload-zone {
            border: 1px dashed var(--gold);
            border-radius: 14px;
            padding: 24px 16px;
            max-width: 560px;
            text-align: center;
            cursor: pointer;
            transition: border-color 0.18s, background 0.18s;
            display: block;
            margin-bottom: 28px;
            background: var(--paper-light);
        }
        .upload-zone:hover, .upload-zone.drag-over {
            border-color: var(--accent);
            background: var(--gold-soft);
        }
        .upload-zone-icon { font-size: 26px; margin-bottom: 6px; }
        .upload-zone p { font-family: var(--ui-font); font-size: 12px; color: var(--ink-muted); line-height: 1.5; }

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
            justify-content: flex-start;
            padding: 112px clamp(32px, 7vw, 96px) 96px;
            overflow-y: auto;
        }
        .agreement-shell {
            width: min(100%, 1120px);
            margin: 0 auto;
            display: grid;
            grid-template-columns: minmax(0, 1fr) 320px;
            gap: 20px;
            align-items: start;
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
        }
        .agreement-document-head {
            padding: 18px 24px;
            border-bottom: 1px solid var(--env-border);
            background: var(--paper-panel);
        }
        .agreement-panel .question {
            max-width: 760px;
            margin-bottom: 8px;
            font-size: clamp(24px, 3vw, 34px);
        }
        .agreement-panel .hint {
            max-width: 720px;
            margin-bottom: 0;
            font-size: 12px;
        }
        .agreement-panel .agreement-scroll {
            max-width: none;
            max-height: min(56vh, 560px);
            margin: 0;
            border: 0;
            border-radius: 0;
            background: #FFFFFF;
            padding: 28px 32px;
            font-size: 15px;
            line-height: 1.95;
            color: var(--ink);
        }
        .agreement-panel .agreement-scroll strong {
            display: inline-block;
            margin-bottom: 3px;
            color: var(--accent);
            font-size: 15px;
        }
        .agreement-footer {
            padding: 18px 24px 22px;
            border-top: 1px solid var(--env-border);
            background: var(--paper-light);
        }
        .agreement-panel .agree-check-row {
            margin-bottom: 16px;
        }
        .agreement-panel .btn-row {
            justify-content: space-between;
        }
        .agreement-aside {
            padding: 14px;
            opacity: 0.82;
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
            position: absolute;
            top: 0;
            bottom: 0;
            left: 0;
            width: min(16vw, 150px);
            z-index: 7;
            pointer-events: none;
        }
        .agreement-mode .fate-string-divider {
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
            background: var(--gold);
        }

        .right-panel::before {
            display: none;
        }

        .right-panel::after {
            display: none;
        }

        .image-slide {
            position: absolute;
            inset: 0;
            opacity: 0;
            transform: translateY(10px) scale(0.98);
            z-index: 1;
            pointer-events: none;
            transition: opacity 0.55s cubic-bezier(0.4,0,0.2,1),
                        transform 0.55s cubic-bezier(0.4,0,0.2,1);
        }
        .image-slide::before {
            display: none;
        }
        .image-slide.active {
            opacity: 1;
            transform: translateY(0) scale(1);
            z-index: 3;
        }
        .image-slide.leaving {
            opacity: 1;
            transform: translateY(-12px) scale(1.02);
            z-index: 2;
        }

        .img-cell {
            position: absolute;
            overflow: hidden;
            background: var(--ink);
            border: 1px solid var(--paper-panel);
            border-radius: 10px;
            box-shadow: 0 18px 45px rgb(0 0 0 / 0.06);
            opacity: 0;
            transform: translateY(14px) scale(0.97);
        }
        .image-slide.active .img-cell {
            animation: photoSettle 0.58s cubic-bezier(0.22, 1, 0.36, 1) both;
        }
        .image-slide.leaving .img-cell {
            animation: photoOverlapOut 0.58s cubic-bezier(0.4,0,0.2,1) both;
        }
        .image-slide.active .img-cell:nth-child(2) { animation-delay: 0.06s; }
        .image-slide.active .img-cell:nth-child(3) { animation-delay: 0.12s; }
        .image-slide.active .img-cell:nth-child(4) { animation-delay: 0.18s; }
        .image-slide.leaving .img-cell:nth-child(2) { animation-delay: 0.04s; }
        .image-slide.leaving .img-cell:nth-child(3) { animation-delay: 0.08s; }
        .image-slide.leaving .img-cell:nth-child(4) { animation-delay: 0.12s; }
        .img-cell.span-col,
        .img-cell.span-row { grid-column: auto; grid-row: auto; }

        .img-cell:nth-child(1) {
            left: 22%;
            top: -6%;
            width: 46%;
            height: 28%;
            z-index: 2;
        }
        .img-cell:nth-child(2) {
            right: 16%;
            top: 22%;
            width: 34%;
            height: 28%;
            z-index: 4;
        }
        .img-cell:nth-child(3) {
            left: 17%;
            top: 56%;
            width: 52%;
            height: 28%;
            z-index: 3;
        }
        .img-cell:nth-child(4) {
            right: 20%;
            top: 86%;
            width: 40%;
            height: 25%;
            z-index: 5;
        }

        .img-cell img {
            width: 100%; height: 100%;
            object-fit: cover;
            display: block;
            transform: scale(1.04);
            transition: transform 8s cubic-bezier(0.25,0,0,1);
            filter: saturate(0.94) contrast(0.98) sepia(0.04);
        }
        .image-slide.active .img-cell img {
            transform: scale(1);
        }

        @keyframes photoSettle {
            from {
                opacity: 0;
                transform: translateY(18px) scale(0.96);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        @keyframes photoOverlapOut {
            from {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
            to {
                opacity: 0;
                transform: translateY(-18px) scale(1.04);
            }
        }

        /* Overlay text on right panel */
        .right-overlay {
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
            .split { grid-template-columns: 1fr; height: auto; }
            .right-panel { height: 44vh; order: -1; }
            .fate-string-divider { display: none; }
            .left-panel { padding: 48px 28px 80px; }
            .step-panel { padding: 64px 28px 80px; }
            .agreement-mode .left-panel { padding: 28px 18px 82px; }
            .agreement-panel { position: relative; padding: 92px 0 40px; }
            .agreement-shell { grid-template-columns: 1fr; gap: 14px; }
            .agreement-panel .agreement-scroll { max-height: none; }
            .brand { left: 28px; }
            .step-counter-left { left: 28px; }
            .question { font-size: 34px; }
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
                <div class="eyebrow">Partner application</div>
                <h2 class="question">Welcome,<br>let's get you started.</h2>
                <p class="hint">Supplier account.</p>
                <div class="email-chip">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    <?= htmlspecialchars($email ?? '', ENT_QUOTES, 'UTF-8') ?>
                </div>
                <div class="btn-row">
                    <button type="button" class="btn-next js-next">Begin →</button>
                </div>
            </div>

            <!-- ── PANEL 1: Business name ── -->
            <div class="step-panel hidden-panel" data-panel="1">
                <div class="eyebrow">Step 1 of 5 — Business identity</div>
                <h2 class="question">What's your<br>business name?</h2>
                <p class="hint">Public business name.</p>
                <input class="q-input" name="business_name" type="text" placeholder="e.g. Blossom & Co."
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
                <h2 class="question">What can your<br>business provide?</h2>
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
                <h2 class="question">Describe your<br>business</h2>
                <p class="hint">Profile and documents.</p>
                <div class="field-group">
                    <label class="field-label">Business description</label>
                    <textarea class="q-input textarea-input" name="business_description"
                              placeholder="Tell us what your business provides..." required><?= htmlspecialchars($business_description ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>
                <label class="upload-zone" id="coverDropZone" for="coverPhotoInput">
                    <input id="coverPhotoInput" name="cover_photo" type="file"
                           accept="image/jpeg,image/png,image/webp" class="sr-only" required>
                    <div class="upload-zone-icon">🖼️</div>
                    <p id="uploadLabel">Click or drag & drop your best photo<br><span style="font-size:11px;opacity:0.7">JPG, PNG, WEBP · max 5 MB</span></p>
                </label>
                <label class="upload-zone" id="licenseDropZone" for="businessLicenseInput">
                    <input id="businessLicenseInput" name="business_license" type="file"
                           accept="image/jpeg,image/png,image/webp,application/pdf" class="sr-only" required>
                    <div class="upload-zone-icon">📄</div>
                    <p id="licenseLabel">Click or drag & drop your business license<br><span style="font-size:11px;opacity:0.7">PDF, JPG, PNG, WEBP · max 5 MB</span></p>
                </label>
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

        <!-- Each slide corresponds to a step. Uses free Unsplash wedding images. -->
        <!-- Slide 0: Welcome -->
        <div class="image-slide active" data-slide="0">
            <div class="img-cell span-col">
                <img src="https://images.unsplash.com/photo-1519741497674-611481863552?w=900&q=80&auto=format&fit=crop" alt="">
            </div>
            <div class="img-cell">
                <img src="https://images.unsplash.com/photo-1537633552985-df8429e8048b?w=500&q=80&auto=format&fit=crop" alt="">
            </div>
            <div class="img-cell">
                <img src="https://images.unsplash.com/photo-1522413452208-996ff3f3e740?w=500&q=80&auto=format&fit=crop" alt="">
            </div>
        </div>

        <!-- Slide 1: Business name -->
        <div class="image-slide" data-slide="1">
            <div class="img-cell span-row">
                <img src="https://images.unsplash.com/photo-1604017011826-d3b4c23f8914?w=600&q=80&auto=format&fit=crop" alt="">
            </div>
            <div class="img-cell">
                <img src="https://images.unsplash.com/photo-1519225421980-715cb0215aed?w=500&q=80&auto=format&fit=crop" alt="">
            </div>
            <div class="img-cell">
                <img src="https://images.unsplash.com/photo-1511285560929-80b456fea0bc?w=500&q=80&auto=format&fit=crop" alt="">
            </div>
        </div>

        <!-- Slide 2: Category -->
        <div class="image-slide" data-slide="2">
            <div class="img-cell">
                <img src="https://images.unsplash.com/photo-1465495976277-4387d4b0b4c6?w=500&q=80&auto=format&fit=crop" alt="">
            </div>
            <div class="img-cell">
                <img src="https://images.unsplash.com/photo-1563827576-217f33e5b28a?w=500&q=80&auto=format&fit=crop" alt="">
            </div>
            <div class="img-cell">
                <img src="https://images.unsplash.com/photo-1520854221256-17451cc331bf?w=500&q=80&auto=format&fit=crop" alt="">
            </div>
            <div class="img-cell">
                <img src="https://images.unsplash.com/photo-1531956656798-56686eeef3d4?w=500&q=80&auto=format&fit=crop" alt="">
            </div>
        </div>

        <!-- Slide 3: Service -->
        <div class="image-slide" data-slide="3">
            <div class="img-cell span-col">
                <img src="https://images.unsplash.com/photo-1606800052052-a08af7148866?w=900&q=80&auto=format&fit=crop" alt="">
            </div>
            <div class="img-cell">
                <img src="https://images.unsplash.com/photo-1593030761757-71fae45fa0e7?w=500&q=80&auto=format&fit=crop" alt="">
            </div>
            <div class="img-cell">
                <img src="https://images.unsplash.com/photo-1583939003579-730e3918a45a?w=500&q=80&auto=format&fit=crop" alt="">
            </div>
        </div>

        <!-- Slide 4: Contact -->
        <div class="image-slide" data-slide="4">
            <div class="img-cell">
                <img src="https://images.unsplash.com/photo-1529636798458-92182e662485?w=500&q=80&auto=format&fit=crop" alt="">
            </div>
            <div class="img-cell">
                <img src="https://images.unsplash.com/photo-1548199973-03cce0bbc87b?w=500&q=80&auto=format&fit=crop" alt="">
            </div>
            <div class="img-cell span-col">
                <img src="https://images.unsplash.com/photo-1543157145-ea5d00ceeede?w=900&q=80&auto=format&fit=crop" alt="">
            </div>
        </div>

        <!-- Slide 5: Story -->
        <div class="image-slide" data-slide="5">
            <div class="img-cell span-row">
                <img src="https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?w=600&q=80&auto=format&fit=crop" alt="">
            </div>
            <div class="img-cell">
                <img src="https://images.unsplash.com/photo-1530103862676-de8c9debad1d?w=500&q=80&auto=format&fit=crop" alt="">
            </div>
            <div class="img-cell">
                <img src="https://images.unsplash.com/photo-1470116945706-e6bf5d5a53ca?w=500&q=80&auto=format&fit=crop" alt="">
            </div>
        </div>

        <!-- Slide 6: Agreement -->
        <div class="image-slide" data-slide="6">
            <div class="img-cell span-col">
                <img src="https://images.unsplash.com/photo-1515934751635-c81c6bc9a2d8?w=900&q=80&auto=format&fit=crop" alt="">
            </div>
            <div class="img-cell">
                <img src="https://images.unsplash.com/photo-1522673607200-164d1b6ce486?w=500&q=80&auto=format&fit=crop" alt="">
            </div>
            <div class="img-cell">
                <img src="https://images.unsplash.com/photo-1507504031003-b417219a0fde?w=500&q=80&auto=format&fit=crop" alt="">
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
    const licenseInput = document.getElementById('businessLicenseInput');
    const licenseDrop = document.getElementById('licenseDropZone');
    const licenseLabel = document.getElementById('licenseLabel');
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
        document.body.style.overflow = idx === TOTAL - 1 ? 'auto' : '';
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
            window.setTimeout(() => previous.classList.remove('leaving'), 620);
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
    coverInput?.addEventListener('change', () => {
        const f = coverInput.files[0];
        if (f && uploadLabel) uploadLabel.innerHTML = '✓ ' + f.name;
    });
    licenseInput?.addEventListener('change', () => {
        const f = licenseInput.files[0];
        if (f && licenseLabel) licenseLabel.innerHTML = '✓ ' + f.name;
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
    window.addEventListener('beforeunload', e => {
        if (submitBtn?.disabled) return;
        const hasUnsavedFiles = (coverInput?.files?.length || 0) > 0 || (licenseInput?.files?.length || 0) > 0;
        if (!hasUnsavedFiles) return;
        e.preventDefault();
        e.returnValue = '';
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
    showCategoryTiles(getSelectedCategoryChecks().length > 0);
    buildDots();
    syncAgreementMode(start);
    updateSlide(start);
    panels.forEach((p, i) => p.classList.toggle('hidden-panel', i !== start));
})();
</script>
</body>
</html>
