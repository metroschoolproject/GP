<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Onboarding - <?= APPNAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --env-border: rgba(118,90,70,0.22);
            --paper: #f5e8d9;
            --paper-light: #fff4e6;
            --accent: #530b0a;
            --accent-hover: #3f0908;
            --gold: #d8b46a;
            --gold-soft: #f3d9a4;
            --focus-color: #fff8ef;
            --input-bg: rgba(255,244,230,0.74);
            --header-font: "Great Vibes", cursive;
            --body-font: "Playfair Display", Georgia, serif;
            --ui-font: system-ui, -apple-system, sans-serif;
            --ink: #211d1a;
            --ink-muted: rgba(111,98,90,0.82);
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html, body { height: 100%; }

        body {
            font-family: var(--body-font);
            color: var(--ink);
            background: var(--paper);
            overflow: hidden;
        }

        /* ── Split layout ── */
        .split {
            display: grid;
            grid-template-columns: 1fr 1fr;
            height: 100vh;
        }

        /* ── LEFT PANEL ── */
        .left-panel {
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 64px 60px;
            position: relative;
            background:
                radial-gradient(ellipse at 18% 18%, rgba(255,255,255,0.68) 0 10%, transparent 28%),
                radial-gradient(ellipse at 86% 74%, rgba(216,180,106,0.14), transparent 34%),
                linear-gradient(180deg, #fff4e6 0%, #f5e8d9 100%);
            z-index: 2;
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
            background: rgba(118,90,70,0.24);
            transition: background 0.35s, transform 0.35s;
        }
        .step-dot.active {
            background: var(--accent);
            transform: scale(1.3);
        }
        .step-dot.done {
            background: rgba(216,180,106,0.78);
        }

        /* ── Step panel (question area) ── */
        .step-panel {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 80px 60px 80px;
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
            font-size: 10px; font-weight: 600;
            letter-spacing: 0.14em; text-transform: uppercase;
            color: #9a687f;
            margin-bottom: 10px;
        }
        .question {
            font-size: 42px; font-weight: 600;
            line-height: 1.05;
            color: var(--ink);
            margin-bottom: 8px;
            font-style: normal;
            letter-spacing: 0;
        }
        .hint {
            font-family: var(--ui-font);
            font-size: 13px; line-height: 1.6;
            color: var(--ink-muted);
            margin-bottom: 36px;
        }

        /* ── Inputs ── */
        .q-input {
            width: 100%;
            background: var(--input-bg);
            border: 1px solid var(--env-border);
            border-radius: 8px;
            padding: 13px 14px;
            font-size: 18px;
            font-family: var(--body-font);
            color: var(--ink);
            outline: none;
            box-shadow: 0 18px 38px rgba(92,67,48,0.07);
            transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;
            margin-bottom: 8px;
        }
        .q-input:focus {
            border-color: var(--accent);
            background: var(--focus-color);
            box-shadow: 0 18px 38px rgba(92,67,48,0.1);
        }
        .q-input::placeholder {
            color: rgba(111,98,90,0.5);
            font-style: normal;
        }
        .q-input.textarea-input {
            resize: none;
            min-height: 90px;
            font-size: 17px;
            line-height: 1.6;
        }
        .q-input.small-input {
            font-size: 17px;
        }

        .field-group { margin-bottom: 20px; }
        .field-label {
            font-family: var(--ui-font);
            font-size: 10px; font-weight: 600;
            letter-spacing: 0.1em; text-transform: uppercase;
            color: #765a46;
            display: block; margin-bottom: 4px;
        }

        /* ── Category tiles ── */
        .choice-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 9px;
            margin-bottom: 32px;
        }
        .choice-grid.is-hidden {
            display: none;
        }
        .choice-tile {
            background: transparent;
            border: 1px solid var(--env-border);
            border-radius: 8px;
            padding: 13px 14px;
            cursor: pointer;
            text-align: left;
            font-family: inherit;
            transition: border-color 0.18s, background 0.18s, transform 0.12s;
            display: flex; align-items: center; gap: 10px;
        }
        .choice-tile:hover {
            border-color: var(--accent);
            background: rgba(216,180,106,0.12);
            transform: translateY(-1px);
        }
        .choice-tile.selected {
            border-color: var(--accent);
            background: rgba(216,180,106,0.2);
        }
        .choice-tile.suggested {
            border-color: rgba(216,180,106,0.9);
            box-shadow: 0 12px 28px rgba(216,180,106,0.14);
        }
        .choice-tile.is-filtered-out {
            display: none;
        }
        .tile-icon { font-size: 18px; flex-shrink: 0; }
        .tile-label {
            font-size: 13px; font-weight: 500;
            color: var(--ink); font-family: var(--ui-font);
        }
        .ai-category-box {
            display: grid;
            gap: 12px;
            margin-bottom: 18px;
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
            background: rgba(255,244,230,0.78);
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
            background: var(--focus-color);
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
            padding: 12px 26px;
            font-family: var(--ui-font);
            font-size: 13px; font-weight: 600;
            letter-spacing: 0.03em;
            cursor: pointer;
            box-shadow: 0 16px 34px rgba(83,11,10,0.16);
            transition: background 0.15s, transform 0.1s, box-shadow 0.15s;
        }
        .btn-next:hover { background: var(--accent-hover); transform: translateY(-1px); }
        .btn-next:active { transform: translateY(0); }
        .btn-next:disabled { opacity: 0.38; cursor: not-allowed; transform: none; }
        .btn-next.submit-btn { background: #765a46; }
        .btn-next.submit-btn:hover { background: #5f4636; }

        .btn-back {
            background: none; border: none;
            font-family: var(--ui-font);
            font-size: 13px; color: var(--ink-muted);
            cursor: pointer; padding: 0;
            transition: color 0.15s;
        }
        .btn-back:hover { color: var(--accent); }

        .enter-hint {
            font-family: var(--ui-font);
            font-size: 11px; color: rgba(111,98,90,0.62);
        }
        kbd {
            background: rgba(216,180,106,0.16);
            border: 1px solid var(--env-border);
            border-radius: 3px; padding: 1px 5px;
            font-size: 10px; font-family: var(--ui-font);
            color: var(--accent);
        }

        /* ── Upload zone ── */
        .upload-zone {
            border: 1.5px dashed rgba(118,90,70,0.34);
            border-radius: 8px;
            padding: 24px 16px;
            text-align: center;
            cursor: pointer;
            transition: border-color 0.18s, background 0.18s;
            display: block;
            margin-bottom: 28px;
        }
        .upload-zone:hover, .upload-zone.drag-over {
            border-color: var(--accent);
            background: rgba(216,180,106,0.12);
        }
        .upload-zone-icon { font-size: 26px; margin-bottom: 6px; }
        .upload-zone p { font-family: var(--ui-font); font-size: 12px; color: var(--ink-muted); line-height: 1.5; }

        /* ── Agreement ── */
        .agreement-scroll {
            border: 1px solid var(--env-border);
            border-radius: 8px;
            padding: 14px 16px;
            max-height: 180px;
            overflow-y: auto;
            font-size: 12px;
            line-height: 1.75;
            color: rgba(33,29,26,0.72);
            font-family: var(--ui-font);
            margin-bottom: 16px;
            background: rgba(255,244,230,0.58);
        }
        .agreement-scroll::-webkit-scrollbar { width: 3px; }
        .agreement-scroll::-webkit-scrollbar-thumb { background: rgba(118,90,70,0.32); border-radius: 4px; }

        .agree-check-row {
            display: flex; align-items: flex-start; gap: 10px;
            font-family: var(--ui-font); font-size: 13px;
            line-height: 1.6; color: rgba(33,29,26,0.8);
            cursor: pointer; margin-bottom: 28px;
        }
        .agree-check-row input[type="checkbox"] {
            width: 15px; height: 15px; margin-top: 2px;
            accent-color: var(--accent); flex-shrink: 0;
        }

        /* ── Error ── */
        .step-error {
            font-family: var(--ui-font);
            font-size: 12px;
            color: #b91c1c;
            margin-bottom: 12px;
            display: none;
        }
        .step-error.visible { display: block; }

        /* ── Email readonly ── */
        .email-chip {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(216,180,106,0.14);
            border: 1px solid var(--env-border);
            border-radius: 100px;
            padding: 8px 16px;
            font-family: var(--ui-font);
            font-size: 13px; color: var(--accent);
            margin-bottom: 36px;
        }
        .email-chip svg { width: 13px; height: 13px; flex-shrink: 0; }

        /* ── RIGHT PANEL (loose home-style photo collage) ── */
        .right-panel {
            position: relative;
            overflow: hidden;
            background:
                radial-gradient(ellipse at 78% 20%, rgba(216,180,106,0.2), transparent 34%),
                linear-gradient(160deg, #211d1a 0%, #4a342f 46%, #765a46 100%);
        }

        .right-panel::before {
            content: "";
            position: absolute;
            inset: 0;
            z-index: 2;
            background:
                linear-gradient(180deg, rgba(33,29,26,0.08), rgba(33,29,26,0.72)),
                radial-gradient(ellipse at 42% 50%, transparent 0 36%, rgba(33,29,26,0.34) 70%);
            pointer-events: none;
        }

        .image-slide {
            position: absolute;
            inset: 0;
            transition: opacity 0.7s cubic-bezier(0.4,0,0.2,1);
            opacity: 0;
        }
        .image-slide.active { opacity: 1; }

        .img-cell {
            position: absolute;
            overflow: hidden;
            background: #211d1a;
            border: 1px solid rgba(255,244,230,0.16);
            border-radius: 16px;
            box-shadow: 0 28px 70px rgba(33,29,26,0.28);
        }
        .img-cell.span-col,
        .img-cell.span-row { grid-column: auto; grid-row: auto; }

        .img-cell:nth-child(1) {
            left: 9%;
            top: 10%;
            width: 62%;
            height: 42%;
            transform: rotate(-2.6deg);
        }
        .img-cell:nth-child(2) {
            right: 8%;
            top: 38%;
            width: 48%;
            height: 32%;
            z-index: 1;
            transform: rotate(3.2deg);
        }
        .img-cell:nth-child(3) {
            left: 14%;
            bottom: 9%;
            width: 44%;
            height: 30%;
            transform: rotate(-1.4deg);
        }
        .img-cell:nth-child(4) {
            right: 15%;
            top: 9%;
            width: 30%;
            height: 24%;
            transform: rotate(4deg);
        }

        .img-cell img {
            width: 100%; height: 100%;
            object-fit: cover;
            display: block;
            transform: scale(1.04);
            transition: transform 8s cubic-bezier(0.25,0,0,1);
            filter: saturate(0.92) brightness(0.94);
        }
        .image-slide.active .img-cell img {
            transform: scale(1);
        }

        /* Overlay text on right panel */
        .right-overlay {
            position: absolute;
            bottom: 36px; left: 36px; right: 36px;
            z-index: 10;
        }
        .right-caption {
            font-family: var(--header-font);
            font-size: 56px;
            color: #fff4e6;
            line-height: 1.1;
            text-shadow: 0 2px 20px rgba(0,0,0,0.4);
            transition: opacity 0.5s, transform 0.5s;
        }
        .right-sub {
            font-family: var(--ui-font);
            font-size: 11px; font-weight: 500;
            letter-spacing: 0.1em; text-transform: uppercase;
            color: rgba(243,217,164,0.78);
            margin-top: 6px;
            transition: opacity 0.5s, transform 0.5s;
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
            .left-panel { padding: 48px 28px 80px; }
            .step-panel { padding: 64px 28px 80px; }
            .brand { left: 28px; }
            .step-counter-left { left: 28px; }
            .question { font-size: 34px; }
            .right-caption { font-size: 42px; }
            .img-cell { border-radius: 12px; }
        }
    </style>
</head>
<body>
<div class="split">

    <!-- ════════════════════════════════
         LEFT PANEL
    ════════════════════════════════ -->
    <div class="left-panel" id="leftPanel">
        <canvas class="sparkle-canvas" id="sparkleCanvas"></canvas>

        <div class="brand">
            <span class="brand-name">Golden Promise</span>
        </div>

        <?php if (!empty($message)): ?>
        <div style="position:absolute;top:80px;left:48px;right:48px;padding:12px 16px;border-radius:10px;font-family:system-ui;font-size:13px;border:1px solid <?= !empty($submitted) ? '#a7f3d0' : '#fecaca' ?>;background:<?= !empty($submitted) ? '#f0fdf4' : '#fef2f2' ?>;color:<?= !empty($submitted) ? '#065f46' : '#b91c1c' ?>">
            <?= $message ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="<?= URLROOT ?>/supplier/onboarding" enctype="multipart/form-data" id="supplierOnboardingForm" novalidate>

            <!-- hidden email -->
            <input type="hidden" name="email" value="<?= htmlspecialchars($email ?? '', ENT_QUOTES, 'UTF-8') ?>">

            <?php $selectedCategoryIds = array_map('intval', $category_ids ?? []); ?>

            <!-- ── PANEL 0: Welcome ── -->
            <div class="step-panel" data-panel="0">
                <div class="eyebrow">Partner application</div>
                <h2 class="question">Welcome,<br>let's get you started.</h2>
                <p class="hint">You're applying as a supplier under this account. This takes about 3 minutes.</p>
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
                <p class="hint">This is how couples will find you on the platform.</p>
                <input class="q-input" name="business_name" type="text" placeholder="e.g. Blossom & Co."
                       value="<?= htmlspecialchars($business_name ?? '', ENT_QUOTES, 'UTF-8') ?>" required autocomplete="organization">
                <div class="step-error" id="err1"></div>
                <div class="btn-row">
                    <button type="button" class="btn-next js-next">Continue</button>
                    <button type="button" class="btn-back js-back">← Back</button>
                    <span class="enter-hint">or press <kbd>Enter</kbd></span>
                </div>
            </div>

            <!-- ── PANEL 2: Category ── -->
            <div class="step-panel hidden-panel" data-panel="2">
                <div class="eyebrow">Step 2 of 5 — Business categories</div>
                <h2 class="question">What can your<br>business provide?</h2>
                <p class="hint">Describe the business. We'll suggest categories, then you can confirm or edit them.</p>
                <div class="ai-category-box">
                    <textarea class="q-input textarea-input" id="categoryPrompt" name="category_prompt"
                              placeholder="e.g. We rent bridal dresses, accessories, and provide pre-wedding studio photos."><?= htmlspecialchars($category_prompt ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                    <div class="category-suggest-row">
                        <button type="button" class="btn-suggest" id="suggestCategoryBtn">Suggest categories</button>
                        <span class="category-suggestion-note" id="categorySuggestionNote">Use suggestions as a starting point. You can still choose manually.</span>
                    </div>
                </div>
                <div class="choice-grid <?= empty($selectedCategoryIds) ? 'is-hidden' : '' ?>" id="categoryTiles">
                    <?php
                    $icons = ['Accessories'=>'💍','Dress'=>'👗','Food'=>'🍽️','Package'=>'🎁','Studio'=>'📸','Venue'=>'🏛️','Photography'=>'📸','Floral'=>'🌸','Catering'=>'🎂','Music'=>'🎵','Beauty'=>'💄','Transport'=>'🚗','Decoration'=>'🎀'];
                    foreach (($categories ?? []) as $cat):
                        $cid  = (int)$cat['id'];
                        $cname = htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8');
                        $icon  = $icons[$cat['name']] ?? '✨';
                        $isSelected = in_array($cid, $selectedCategoryIds, true);
                    ?>
                    <button type="button" class="choice-tile <?= $isSelected ? 'selected' : '' ?>"
                            data-cid="<?= $cid ?>"
                            data-name="<?= $cname ?>"
                            aria-pressed="<?= $isSelected ? 'true' : 'false' ?>">
                        <input type="checkbox" name="category_ids[]" value="<?= $cid ?>" class="sr-only category-check" <?= $isSelected ? 'checked' : '' ?>>
                        <span class="tile-icon"><?= $icon ?></span>
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
                <p class="hint">Your phone, location, and public link help admin verify your business.</p>
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
                    <span class="enter-hint">or press <kbd>Enter</kbd></span>
                </div>
            </div>

            <!-- ── PANEL 4: Description + uploads ── -->
            <div class="step-panel hidden-panel" data-panel="4">
                <div class="eyebrow">Step 4 of 5 — Your story</div>
                <h2 class="question">Describe your<br>business</h2>
                <p class="hint">Admin reads this first. Keep it clear, warm, and genuine.</p>
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
            <div class="step-panel hidden-panel" data-panel="5">
                <div class="eyebrow">Step 5 of 5 — Agreement</div>
                <h2 class="question">One last step —<br>review &amp; agree</h2>
                <p class="hint">Please read through the supplier terms before submitting.</p>
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
                <label class="agree-check-row">
                    <input name="agreement_accepted" type="checkbox" value="1" required <?= !empty($agreement_accepted) ? 'checked' : '' ?>>
                    <span>I have read and agree to the Golden Promise supplier business agreement.</span>
                </label>
                <div class="step-error" id="err5"></div>
                <div class="btn-row">
                    <button type="submit" class="btn-next submit-btn" id="submitBtn">Submit application ✦</button>
                    <button type="button" class="btn-back js-back">← Back</button>
                </div>
            </div>

        </form><!-- /form -->

        <!-- Dot nav -->
        <div class="step-counter-left">
            <div class="step-dots" id="stepDots"></div>
            <a href="<?= URLROOT ?>/main/home" style="font-family:system-ui;font-size:11px;color:rgba(111,98,90,0.72);text-decoration:none;margin-left:8px;">Back home</a>
        </div>
    </div><!-- /left-panel -->

    <!-- ════════════════════════════════
         RIGHT PANEL (imagery)
    ════════════════════════════════ -->
    <div class="right-panel" id="rightPanel">

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

    // ── Slides ──
    function updateSlide(idx) {
        slides.forEach(s => s.classList.remove('active'));
        const s = slides[idx];
        if (s) s.classList.add('active');
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
        bad.reportValidity(); bad.focus();
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
        categorySuggestionNote.textContent = matches
            ? `${matches} AI suggested. ${reason || 'Review the highlighted categories before continuing.'}`
            : 'AI could not match a valid category. Try adding more detail.';
    }

    function suggestCategoriesLocal() {
        const text = (categoryPrompt?.value || '').toLowerCase();
        const keywordMap = {
            accessories: ['accessory','accessories','jewelry','jewellery','ring','rings','earring','necklace','tiara','veil','bouquet','shoe','shoes','လက်ဝတ်','လက်ဝတ်ရတနာ','ရတနာ','လက်စွပ်','နားကပ်','လည်ဆွဲ','သရဖူ','ပန်းစည်း','ဖိနပ်','ဆက်စပ်ပစ္စည်း'],
            dress: ['dress','dresses','gown','bridal','bride','suit','tuxedo','outfit','attire','rental','rent','ဝတ်စုံ','မင်္ဂလာဝတ်စုံ','သတို့သမီးဝတ်စုံ','ဂါဝန်','အငှား','ငှား','ဝတ်စုံအငှား','သတို့သားဝတ်စုံ'],
            food: ['food','catering','cater','buffet','meal','menu','cake','dessert','snack','drink','beverage','အစားအစာ','အစားအသောက်','ကိတ်','မုန့်','ဘူဖေး','ကျွေးမွေး','အချိုပွဲ','သောက်စရာ','အဖျော်ယမကာ'],
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
            categorySuggestionNote.textContent = 'Type a business description or one category word to get suggestions.';
            return;
        }
        categorySuggestionNote.textContent = matches
            ? `${matches} suggested. Review the highlighted categories before continuing.`
            : 'No confident match yet. Try words like dress, studio, venue, ဝတ်စုံ, ဓာတ်ပုံ, or ခန်းမ.';
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
            categorySuggestionNote.textContent = 'AI is reading your business description...';
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
                throw new Error(result.message || 'AI suggestion failed.');
            }

            applySuggestedCategoryIds(result.category_ids || [], result.reason || '');
        } catch (error) {
            if (error.name === 'AbortError') return;
            suggestCategoriesLocal();
            if (!silent && categorySuggestionNote) {
                categorySuggestionNote.textContent += ' Local suggestions were used because AI was unavailable.';
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
        return Number.isInteger(s) ? Math.min(Math.max(s,0), TOTAL-1) : 0;
    }
    draftFields.forEach(n => {
        if (n === 'category_ids[]') return;
        const f = getField(n); if (f) { f.addEventListener('input', saveDraft); f.addEventListener('change', saveDraft); }
    });

    // ── Submit ──
    form.addEventListener('submit', async e => {
        e.preventDefault();
        if (current < TOTAL - 1) { if (validatePanel(current)) goTo(current+1); return; }
        if (!validateAll()) return;
        submitBtn.disabled = true; submitBtn.textContent = 'Submitting…';
        try {
            const r = await fetch(form.action, { method:'POST', headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}, body: new FormData(form) });
            const res = await r.json();
            if (res.status === 'success') {
                try { localStorage.removeItem(draftKey); } catch(_) {}
                window.location.href = res.redirect || '<?= URLROOT ?>/supplier/pending';
                return;
            }
            showError(current, res.message || 'Please check your information and try again.');
        } catch(_) {
            showError(current, 'Something went wrong. Please try again.');
        } finally {
            submitBtn.disabled = false; submitBtn.textContent = 'Submit application ✦';
        }
    });

    // ── Sparkles ──
    const particles = [];
    function resizeCanvas() { sparkleCanvas.width = sparkleCanvas.parentElement.offsetWidth; sparkleCanvas.height = sparkleCanvas.parentElement.offsetHeight; }
    class Particle {
        constructor() {
            this.x = Math.random() * sparkleCanvas.width;
            this.y = Math.random() * sparkleCanvas.height;
            const a = Math.random()*Math.PI*2, sp = 0.5+Math.random()*1.2;
            this.vx = Math.cos(a)*sp; this.vy = Math.sin(a)*sp - 0.3;
            this.life = 1; this.decay = 0.018+Math.random()*0.022;
            this.size = 0.5+Math.random()*1.2;
            this.color = ['rgba(216,180,106,0.88)','rgba(243,217,164,0.78)','rgba(83,11,10,0.42)'][Math.floor(Math.random()*3)];
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
    updateSlide(start);
    panels.forEach((p, i) => p.classList.toggle('hidden-panel', i !== start));
})();
</script>
</body>
</html>
