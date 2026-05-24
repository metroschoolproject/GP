<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Onboarding - <?= APPNAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Pinyon+Script&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --env-bg: #e8b4b8;
            --env-dark: #d89aa0;
            --env-border: #f4c7c4e5;
            --paper: #f5e8d9;
            --accent: #6d4c5b;
            --focus-color: rgb(247, 236, 236);
            --input-field-color: rgba(249, 237, 228, 0.9);
            --header-font: "Pinyon Script", cursive;
            --body-font: serif;
        }

        body {
            min-height: 100vh;
            margin: 0;
            font-family: var(--body-font);
            color: rgba(38, 20, 28, 0.92);

        }

        .supplier-card {
            position: relative;
            overflow: hidden;
            background: rgba(245, 232, 217, 0.94);
            border: 1px solid var(--env-border);
            box-shadow: 12px 14px 30px rgba(64, 20, 35, 0.22);
        }

        .sparkle-canvas {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            border-radius: 24px;
            z-index: 3;
        }

        .script-heading {
            font-family: var(--header-font);
            color: var(--accent);
            font-weight: 600;
            letter-spacing: 0;
        }

        .supplier-eyebrow,
        .supplier-section-title,
        .supplier-label {
            color: var(--accent);
        }

        .diamond-stepper {
            position: relative;
        }

        .diamond-stepper::before {
            content: "";
            position: absolute;
            left: 17%;
            right: 17%;
            top: 11px;
            height: 1px;
            background: rgba(109, 76, 91, 0.45);
            z-index: 0;
        }

        .step-indicator {
            position: relative;
            z-index: 1;
            color: rgba(109, 76, 91, 0.7);
            text-align: center;
        }

        .step-diamond {
            width: 22px;
            height: 22px;
            margin: 0 auto 8px;
            transform: rotate(45deg);
            border: 1px solid rgba(109, 76, 91, 0.62);
            background: var(--paper);
            box-shadow: 2px 2px 5px rgba(64, 20, 35, 0.12);
            transition: background 0.2s, border-color 0.2s, box-shadow 0.2s;
        }

        .step-diamond span {
            display: grid;
            width: 100%;
            height: 100%;
            place-items: center;
            transform: rotate(-45deg);
            font-size: 10px;
            font-weight: 700;
        }

        .step-indicator.is-active,
        .step-indicator.is-done {
            color: var(--accent);
        }

        .step-indicator.is-active .step-diamond {
            border-color: var(--accent);
            background: var(--focus-color);
            color: var(--accent);
            box-shadow: 0 0 8px rgba(109, 76, 91, 0.28);
        }

        .step-indicator.is-done .step-diamond {
            border-color: var(--accent);
            background: rgba(232, 180, 184, 0.62);
            color: var(--accent);
        }

        .supplier-input,
        #supplierOnboardingForm input:not([type="file"]):not([type="checkbox"]),
        #supplierOnboardingForm select,
        #supplierOnboardingForm textarea {
            width: 100%;
            background: var(--input-field-color);
            border: 1px solid var(--env-border);
            border-radius: 12px;
            padding: 16px 16px 12px;
            font-size: 18px;
            font-family: inherit;
            color: rgba(15, 1, 1, 0.9);
            box-shadow: 5px 5px 10px rgba(2, 2, 2, 0.24);
            transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;
        }

        #supplierOnboardingForm input:not([type="file"]):not([type="checkbox"]),
        #supplierOnboardingForm select {
            min-height: 56px;
        }

        #supplierOnboardingForm textarea {
            min-height: 118px;
            resize: vertical;
        }

        #supplierOnboardingForm input:not([type="file"]):not([type="checkbox"]):focus,
        #supplierOnboardingForm select:focus,
        #supplierOnboardingForm textarea:focus {
            border-color: var(--accent);
            background: var(--focus-color);
            box-shadow: 0 0 10px rgba(109, 76, 91, 0.36);
        }

        .supplier-primary-btn {
            background: var(--accent);
            color: white;
            box-shadow: 5px 5px 8px rgba(0, 0, 0, 0.28);
        }

        .supplier-primary-btn:hover {
            background: #5b3f4c;
        }

        .supplier-secondary-btn {
            border-color: var(--env-border);
            color: var(--accent);
            background: rgba(249, 237, 228, 0.72);
        }

        .supplier-upload-card {
            border-color: var(--env-border);
            background: rgba(255, 250, 246, 0.46);
        }

        .supplier-upload-zone {
            border-color: rgba(109, 76, 91, 0.24);
            background: rgba(249, 237, 228, 0.55);
        }

        .supplier-upload-zone:hover {
            border-color: rgba(109, 76, 91, 0.52);
            background: rgba(247, 236, 236, 0.76);
        }

        .supplier-upload-zone.bg-rose-50 {
            background: rgba(247, 236, 236, 0.86) !important;
        }

        .step-panel {
            transition: opacity 0.45s cubic-bezier(0.4, 0, 0.2, 1),
                        filter 0.45s cubic-bezier(0.4, 0, 0.2, 1),
                        transform 0.45s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .step-panel.is-dissolving {
            opacity: 0;
            filter: blur(6px);
            transform: translateY(10px) scale(0.985);
        }

        .step-panel.is-assembling {
            opacity: 0;
            filter: blur(6px);
            transform: translateY(-8px) scale(0.985);
        }

        .step-char {
            display: inline-block;
            white-space: pre;
            opacity: 0;
            filter: blur(8px);
            transform: scale(0.86);
        }

        @media (prefers-reduced-motion: reduce) {
            .step-panel,
            .step-char {
                transition: none !important;
                animation: none !important;
            }
        }
    </style>
</head>
<body>
    <main class="mx-auto flex min-h-screen w-full max-w-3xl items-center px-4 py-10">
        <section class="supplier-card w-full rounded-2xl p-6">
            <canvas class="sparkle-canvas" id="supplierSparkleCanvas"></canvas>
            <div class="mb-6 text-center">
                <p class="supplier-eyebrow text-sm font-semibold uppercase tracking-wide">Partner application</p>
                <h1 class="script-heading mt-1 text-5xl">Tell us about your service</h1>
                <p class="mt-2 text-sm text-stone-700">This information helps admin review and approve supplier accounts.</p>
            </div>

            <ol class="diamond-stepper mb-6 grid grid-cols-3 gap-2 text-[11px] font-semibold" aria-label="Supplier application steps">
                <li class="step-indicator is-active" data-step-indicator="0">
                    <div class="step-diamond"><span>1</span></div>
                    <span class="block text-[10px] uppercase tracking-wide">Business</span>
                </li>
                <li class="step-indicator" data-step-indicator="1">
                    <div class="step-diamond"><span>2</span></div>
                    <span class="block text-[10px] uppercase tracking-wide">Service</span>
                </li>
                <li class="step-indicator" data-step-indicator="2">
                    <div class="step-diamond"><span>3</span></div>
                    <span class="block text-[10px] uppercase tracking-wide">Agreement</span>
                </li>
            </ol>

            <?php if (!empty($message)): ?>
                <div class="mb-5 rounded-md border <?= !empty($submitted) ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-rose-200 bg-rose-50 text-rose-700' ?> px-4 py-3 text-sm">
                    <?= $message ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= URLROOT ?>/supplier/onboarding" enctype="multipart/form-data" class="grid gap-5" id="supplierOnboardingForm" novalidate>
                <label class="grid gap-1 text-sm font-medium">
                    Account email
                    <input required readonly name="email" type="email" class="rounded-md border border-stone-300 bg-stone-50 px-3 py-2 font-normal text-stone-600 outline-none focus:border-rose-600" value="<?= htmlspecialchars($email ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </label>

                <div class="step-panel grid gap-4 border-t border-[#f4c7c4e5] pt-5" data-step-panel="0">
                    <h2 class="supplier-section-title text-base font-bold">Business information</h2>

                    <label class="grid gap-1 text-sm font-medium">
                        Business name
                        <input required name="business_name" type="text" class="rounded-md border border-stone-300 px-3 py-2 font-normal outline-none focus:border-rose-600" value="<?= htmlspecialchars($business_name ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </label>

                    <label class="grid gap-1 text-sm font-medium">
                        Business description
                        <textarea required name="business_description" rows="3" class="rounded-md border border-stone-300 px-3 py-2 font-normal outline-none focus:border-rose-600"><?= htmlspecialchars($business_description ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                    </label>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="grid gap-1 text-sm font-medium">
                            Phone
                            <input required name="phone" type="tel" inputmode="numeric" pattern="[0-9]{11}" minlength="11" maxlength="11" title="Phone number must be exactly 11 digits." class="rounded-md border border-stone-300 px-3 py-2 font-normal outline-none focus:border-rose-600" value="<?= htmlspecialchars($phone ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </label>

                        <label class="grid gap-1 text-sm font-medium">
                            Business address
                            <input required name="business_address" type="text" class="rounded-md border border-stone-300 px-3 py-2 font-normal outline-none focus:border-rose-600" value="<?= htmlspecialchars($business_address ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </label>
                    </div>
                </div>

                <div class="step-panel hidden gap-4 border-t border-[#f4c7c4e5] pt-5" data-step-panel="1">
                    <h2 class="supplier-section-title text-base font-bold">Service information</h2>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="grid gap-1 text-sm font-medium">
                            Service category
                            <select required name="category_id" class="rounded-md border border-stone-300 px-3 py-2 font-normal outline-none focus:border-rose-600">
                                <?php $selectedCategoryId = (int)($category_id ?? 0); ?>
                                <option value="">Choose category</option>
                                <?php foreach (($categories ?? []) as $category): ?>
                                    <?php $categoryId = (int)$category['id']; ?>
                                    <option value="<?= $categoryId ?>" <?= $selectedCategoryId === $categoryId ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </label>

                        <label class="grid gap-1 text-sm font-medium">
                            Service name
                            <input required name="service_name" type="text" class="rounded-md border border-stone-300 px-3 py-2 font-normal outline-none focus:border-rose-600" value="<?= htmlspecialchars($service_name ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </label>
                    </div>

                    <label class="grid gap-1 text-sm font-medium">
                        Service description
                        <textarea required name="service_description" rows="4" class="rounded-md border border-stone-300 px-3 py-2 font-normal outline-none focus:border-rose-600"><?= htmlspecialchars($service_description ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                    </label>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="grid gap-1 text-sm font-medium">
                            Starting price
                            <input required name="service_price" type="number" min="0" step="0.01" class="rounded-md border border-stone-300 px-3 py-2 font-normal outline-none focus:border-rose-600" value="<?= htmlspecialchars($service_price ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </label>
                        <label class="grid gap-1 text-sm font-medium">
                           Business URL
                            <input required name="business_url" type="url" placeholder="https://example.com" class="rounded-md border border-stone-300 px-3 py-2 font-normal outline-none focus:border-rose-600" value="<?= htmlspecialchars($business_url ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </label>
                    </div>
               

                    <div class="supplier-upload-card rounded-lg border">
                        <div class="border-b border-[#f4c7c4e5] px-4 py-3">
                            <h3 class="supplier-section-title text-base font-bold">Images</h3>
                        </div>

                        <div class="p-4">
                            <label id="coverDropZone" for="coverPhotoInput" class="supplier-upload-zone flex min-h-48 cursor-pointer flex-col items-center justify-center rounded-lg border border-dashed px-4 py-8 text-center transition">
                                <input required id="coverPhotoInput" name="cover_photo" type="file" accept="image/jpeg,image/png,image/webp" class="sr-only">
                                <span class="inline-flex items-center gap-2 rounded-md border border-[#f4c7c4e5] bg-white/80 px-5 py-2.5 text-sm font-semibold text-stone-900 shadow-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        <polyline points="17 8 12 3 7 8"></polyline>
                                        <line x1="12" x2="12" y1="3" y2="15"></line>
                                    </svg>
                                    Upload
                                </span>
                                <span class="mt-5 text-sm text-stone-700">Choose image or drag & drop it here.</span>
                                <span class="mt-1 text-sm text-stone-500">JPG, JPEG, PNG and WEBP. Max 5 MB.</span>
                                <span id="coverPhotoName" class="mt-4 hidden rounded-full bg-white px-3 py-1 text-xs font-semibold text-stone-700 shadow-sm"></span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="step-panel hidden gap-4 border-t border-[#f4c7c4e5] pt-5" data-step-panel="2">
                    <h2 class="supplier-section-title text-base font-bold">Business agreement</h2>
                    <div class="rounded-md border border-[#f4c7c4e5] bg-white/40 px-4 py-4 text-sm leading-6 text-stone-700">
                        By submitting this application, you confirm that your business information is accurate and that Golden Promise may review your supplier account before making services available.
                    </div>

                    <label class="flex gap-3 rounded-md border border-[#f4c7c4e5] bg-white/50 px-4 py-3 text-sm">
                        <input required name="agreement_accepted" type="checkbox" value="1" class="mt-1 h-4 w-4 rounded border-stone-300 text-rose-700 focus:ring-rose-600" <?= !empty($agreement_accepted) ? 'checked' : '' ?>>
                        <span class="leading-6 text-stone-700">
                            I have read and agree to the Golden Promise supplier business agreement.
                        </span>
                    </label>
                </div>

                <p id="stepError" class="hidden rounded-md border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"></p>

                <div class="mt-2 flex flex-wrap items-center gap-3">
                    <button type="button" id="backStepBtn" class="supplier-secondary-btn hidden rounded-md border px-5 py-2.5 text-sm font-semibold transition">Back</button>
                    <button type="button" id="nextStepBtn" class="supplier-primary-btn rounded-md px-5 py-2.5 text-sm font-semibold transition">Continue</button>
                    <button type="submit" id="submitStepBtn" class="supplier-primary-btn hidden rounded-md px-5 py-2.5 text-sm font-semibold transition">Submit application</button>
                    <a href="<?= URLROOT ?>/main/home" class="text-sm font-medium text-stone-700 hover:text-stone-950">Back home</a>
                </div>
            </form>
        </section>
    </main>
    <script>
        const form = document.getElementById('supplierOnboardingForm');
        const panels = Array.from(document.querySelectorAll('[data-step-panel]'));
        const indicators = Array.from(document.querySelectorAll('[data-step-indicator]'));
        const backBtn = document.getElementById('backStepBtn');
        const nextBtn = document.getElementById('nextStepBtn');
        const submitBtn = document.getElementById('submitStepBtn');
        const stepError = document.getElementById('stepError');
        const coverInput = document.getElementById('coverPhotoInput');
        const coverDropZone = document.getElementById('coverDropZone');
        const coverPhotoName = document.getElementById('coverPhotoName');
        const phoneInput = form.querySelector('[name="phone"]');
        const sparkleCanvas = document.getElementById('supplierSparkleCanvas');
        const sparkleCtx = sparkleCanvas.getContext('2d');
        const sparkleParticles = [];
        const draftKey = 'gp_supplier_onboarding_' + encodeURIComponent(form.elements.email.value || 'guest');
        const draftFields = [
            'business_name',
            'business_description',
            'phone',
            'business_address',
            'category_id',
            'service_name',
            'service_description',
            'service_price',
            'business_url',
            'agreement_accepted'
        ];
        let currentStep = 0;
        let previousStep = 0;
        let isStepAnimating = false;
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        function getFormField(fieldName) {
            const field = form.elements[fieldName];

            if (!field) {
                return null;
            }

            return typeof field.addEventListener === 'function'
                ? field
                : field[0] || null;
        }

        function resizeSparkleCanvas() {
            sparkleCanvas.width = sparkleCanvas.parentElement.offsetWidth;
            sparkleCanvas.height = sparkleCanvas.parentElement.offsetHeight;
        }

        class SparkleParticle {
            constructor(x, y) {
                this.x = x;
                this.y = y;
                const angle = Math.random() * Math.PI * 2;
                const speed = 0.35 + Math.random() * 1.4;
                this.vx = Math.cos(angle) * speed;
                this.vy = Math.sin(angle) * speed - 0.35;
                this.life = 1;
                this.decay = 0.02 + Math.random() * 0.025;
                this.size = 0.4 + Math.random() * 1;
                this.phase = Math.random() * Math.PI * 2;
                this.color = [
                    'rgba(255,182,193,0.95)',
                    'rgba(255,105,180,0.82)',
                    'rgba(219,112,147,0.72)',
                    'rgba(255,240,245,0.88)'
                ][Math.floor(Math.random() * 4)];
            }

            update() {
                this.x += this.vx;
                this.y += this.vy;
                this.vy += 0.025;
                this.vx *= 0.97;
                this.life -= this.decay;
                this.phase += 0.2;
            }

            draw(ctx) {
                const alpha = Math.max(0, this.life) * (0.6 + 0.4 * Math.sin(this.phase));
                ctx.globalAlpha = alpha;
                ctx.fillStyle = this.color;
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
                ctx.fill();
                ctx.globalAlpha = alpha * 0.22;
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.size * 2.4, 0, Math.PI * 2);
                ctx.fill();
            }
        }

        function emitSparklesAtElement(element, count = 34) {
            if (!element) {
                return;
            }

            const canvasRect = sparkleCanvas.getBoundingClientRect();
            const rect = element.getBoundingClientRect();
            const centerX = rect.left - canvasRect.left + rect.width / 2;
            const centerY = rect.top - canvasRect.top + rect.height / 2;

            for (let i = 0; i < count; i++) {
                sparkleParticles.push(new SparkleParticle(centerX, centerY));
            }
        }

        function emitSparklesAtPoint(x, y, count = 8) {
            for (let i = 0; i < count; i++) {
                sparkleParticles.push(new SparkleParticle(x, y));
            }
        }

        function buildStepTitleSpans(title, hidden = false) {
            if (!title) {
                return;
            }

            const text = title.dataset.stepTitleText || title.textContent;
            title.dataset.stepTitleText = text;
            title.innerHTML = '';

            for (let i = 0; i < text.length; i++) {
                const span = document.createElement('span');
                span.className = 'step-char';
                span.textContent = text[i];

                if (!hidden) {
                    span.style.opacity = '1';
                    span.style.filter = 'blur(0)';
                    span.style.transform = 'scale(1)';
                }

                title.appendChild(span);
            }
        }

        function getStepCharRects(title) {
            if (!title) {
                return [];
            }

            const canvasRect = sparkleCanvas.getBoundingClientRect();

            return Array.from(title.querySelectorAll('.step-char')).map((char) => {
                const rect = char.getBoundingClientRect();

                return {
                    char,
                    cx: rect.left - canvasRect.left + rect.width / 2,
                    cy: rect.top - canvasRect.top + rect.height / 2
                };
            });
        }

        function dissolveStepTitle(panel) {
            return new Promise((resolve) => {
                const title = panel.querySelector('.supplier-section-title');

                if (!title || prefersReducedMotion) {
                    resolve();
                    return;
                }

                buildStepTitleSpans(title, false);
                const chars = getStepCharRects(title);

                chars.forEach(({ char, cx, cy }, index) => {
                    setTimeout(() => {
                        if (char.textContent.trim()) {
                            emitSparklesAtPoint(cx, cy, 9);
                        }

                        char.style.transition = 'opacity 0.4s ease, filter 0.4s ease, transform 0.4s ease';
                        char.style.opacity = '0';
                        char.style.filter = 'blur(6px)';
                        char.style.transform = 'scale(0.87)';
                    }, index * 18);
                });

                setTimeout(resolve, chars.length * 18 + 220);
            });
        }

        function assembleStepTitle(panel) {
            return new Promise((resolve) => {
                const title = panel.querySelector('.supplier-section-title');

                if (!title || prefersReducedMotion) {
                    resolve();
                    return;
                }

                buildStepTitleSpans(title, true);
                const chars = getStepCharRects(title);

                chars.forEach(({ char, cx, cy }, index) => {
                    setTimeout(() => {
                        if (char.textContent.trim()) {
                            emitSparklesAtPoint(cx, cy, 6);
                        }

                        char.style.transition = 'opacity 0.48s cubic-bezier(0,0,0.2,1), filter 0.48s cubic-bezier(0,0,0.2,1), transform 0.48s cubic-bezier(0,0,0.2,1)';
                        char.style.opacity = '1';
                        char.style.filter = 'blur(0)';
                        char.style.transform = 'scale(1)';
                    }, index * 20);
                });

                setTimeout(resolve, chars.length * 20 + 520);
            });
        }

        function stepContentElements(panel) {
            return Array.from(panel.children).filter((child) => !child.classList.contains('supplier-section-title'));
        }

        function hideStepContent(panel) {
            stepContentElements(panel).forEach((child, index) => {
                setTimeout(() => {
                    child.style.transition = 'opacity 0.34s cubic-bezier(0.4,0,0.2,1), transform 0.34s cubic-bezier(0.4,0,0.2,1), filter 0.34s cubic-bezier(0.4,0,0.2,1)';
                    child.style.opacity = '0';
                    child.style.transform = 'translateY(12px)';
                    child.style.filter = 'blur(4px)';
                }, index * 45);
            });
        }

        function showStepContent(panel) {
            const elements = stepContentElements(panel);

            elements.forEach((child) => {
                child.style.opacity = '0';
                child.style.transform = 'translateY(12px)';
                child.style.filter = 'blur(4px)';
            });

            requestAnimationFrame(() => {
                elements.forEach((child, index) => {
                    setTimeout(() => {
                        child.style.transition = 'opacity 0.42s cubic-bezier(0,0,0.2,1), transform 0.42s cubic-bezier(0,0,0.2,1), filter 0.42s cubic-bezier(0,0,0.2,1)';
                        child.style.opacity = '1';
                        child.style.transform = 'translateY(0)';
                        child.style.filter = 'blur(0)';
                    }, index * 70);
                });
            });
        }

        function resetStepContent(panel) {
            stepContentElements(panel).forEach((child) => {
                child.style.transition = '';
                child.style.opacity = '';
                child.style.transform = '';
                child.style.filter = '';
            });
        }

        function sparkleLoop() {
            sparkleCtx.clearRect(0, 0, sparkleCanvas.width, sparkleCanvas.height);

            for (let i = sparkleParticles.length - 1; i >= 0; i--) {
                sparkleParticles[i].update();
                sparkleParticles[i].draw(sparkleCtx);

                if (sparkleParticles[i].life <= 0) {
                    sparkleParticles.splice(i, 1);
                }
            }

            requestAnimationFrame(sparkleLoop);
        }

        resizeSparkleCanvas();
        window.addEventListener('resize', resizeSparkleCanvas);
        sparkleLoop();

        function updateStepShell() {
            indicators.forEach((indicator, indicatorIndex) => {
                const isActive = indicatorIndex === currentStep;
                const isDone = indicatorIndex < currentStep;
                const diamondText = indicator.querySelector('.step-diamond span');

                indicator.classList.toggle('is-active', isActive);
                indicator.classList.toggle('is-done', isDone);

                if (diamondText) {
                    diamondText.textContent = isDone ? '✓' : String(indicatorIndex + 1);
                }
            });

            backBtn.classList.toggle('hidden', currentStep === 0);
            nextBtn.classList.toggle('hidden', currentStep === panels.length - 1);
            submitBtn.classList.toggle('hidden', currentStep !== panels.length - 1);
            stepError.classList.add('hidden');
            stepError.textContent = '';
        }

        function setStepPanelVisibility() {
            panels.forEach((panel, panelIndex) => {
                panel.classList.remove('is-dissolving', 'is-assembling');
                panel.classList.toggle('hidden', panelIndex !== currentStep);
                panel.classList.toggle('grid', panelIndex === currentStep);
                resetStepContent(panel);
                buildStepTitleSpans(panel.querySelector('.supplier-section-title'), false);
            });
        }

        async function showStep(index, animate = true) {
            if (isStepAnimating || index === currentStep) {
                return;
            }

            if (index < 0 || index >= panels.length) {
                return;
            }

            previousStep = currentStep;
            const previousPanel = panels[previousStep];
            const nextPanel = panels[index];
            isStepAnimating = animate && !prefersReducedMotion;
            nextBtn.disabled = isStepAnimating;
            backBtn.disabled = isStepAnimating;

            if (isStepAnimating) {
                hideStepContent(previousPanel);
                previousPanel.classList.add('is-dissolving');
                await dissolveStepTitle(previousPanel);
            }

            currentStep = index;
            saveDraft();
            setStepPanelVisibility();
            updateStepShell();

            if (isStepAnimating) {
                nextPanel.classList.add('is-assembling');

                requestAnimationFrame(() => {
                    nextPanel.classList.remove('is-assembling');
                    showStepContent(nextPanel);
                });

                emitSparklesAtElement(indicators[currentStep]?.querySelector('.step-diamond'), 28);
                await assembleStepTitle(nextPanel);
            } else {
                emitSparklesAtElement(indicators[currentStep]?.querySelector('.step-diamond'), 20);
            }

            nextBtn.disabled = false;
            backBtn.disabled = false;
            isStepAnimating = false;
        }

        function getDraft() {
            try {
                return JSON.parse(localStorage.getItem(draftKey)) || {};
            } catch (error) {
                return {};
            }
        }

        function saveDraft() {
            const draft = {
                currentStep,
                updatedAt: new Date().toISOString(),
                fields: {}
            };

            draftFields.forEach((fieldName) => {
                const field = getFormField(fieldName);

                if (!field) {
                    return;
                }

                draft.fields[fieldName] = field.type === 'checkbox' ? field.checked : field.value;
            });

            localStorage.setItem(draftKey, JSON.stringify(draft));
        }

        function restoreDraft() {
            const draft = getDraft();

            if (!draft.fields) {
                return 0;
            }

            draftFields.forEach((fieldName) => {
                const field = getFormField(fieldName);

                if (!field || typeof draft.fields[fieldName] === 'undefined') {
                    return;
                }

                if (field.type === 'checkbox') {
                    field.checked = draft.fields[fieldName] === true;
                    return;
                }

                field.value = draft.fields[fieldName];
            });

            const restoredStep = Number.parseInt(draft.currentStep, 10);

            return Number.isInteger(restoredStep)
                ? Math.min(Math.max(restoredStep, 0), panels.length - 1)
                : 0;
        }

        function validateStep(stepIndex) {
            const fields = Array.from(panels[stepIndex].querySelectorAll('input, select, textarea'));
            const invalidField = fields.find((field) => !field.checkValidity());

            if (!invalidField) {
                stepError.classList.add('hidden');
                stepError.textContent = '';
                return true;
            }

            if (stepIndex !== currentStep) {
                showStep(stepIndex, false);
            }

            invalidField.reportValidity();
            invalidField.focus();
            stepError.textContent = invalidField.validationMessage || 'Please complete this step before continuing.';
            stepError.classList.remove('hidden');
            return false;
        }

        function validateCurrentStep() {
            return validateStep(currentStep);
        }

        function validateAllSteps() {
            return panels.every((panel, panelIndex) => validateStep(panelIndex));
        }

        function showFormMessage(message, type = 'error') {
            stepError.textContent = message;
            stepError.classList.remove('hidden', 'border-rose-200', 'bg-rose-50', 'text-rose-700', 'border-emerald-200', 'bg-emerald-50', 'text-emerald-700');

            if (type === 'success') {
                stepError.classList.add('border-emerald-200', 'bg-emerald-50', 'text-emerald-700');
                return;
            }

            stepError.classList.add('border-rose-200', 'bg-rose-50', 'text-rose-700');
        }

        function showSelectedCover(file) {
            if (!file) {
                coverPhotoName.classList.add('hidden');
                coverPhotoName.textContent = '';
                return;
            }

            coverPhotoName.textContent = file.name;
            coverPhotoName.classList.remove('hidden');
        }

        coverInput.addEventListener('change', () => {
            showSelectedCover(coverInput.files[0]);
        });

        ['dragenter', 'dragover'].forEach((eventName) => {
            coverDropZone.addEventListener(eventName, (event) => {
                event.preventDefault();
                coverDropZone.classList.add('border-rose-400', 'bg-rose-50');
            });
        });

        ['dragleave', 'drop'].forEach((eventName) => {
            coverDropZone.addEventListener(eventName, (event) => {
                event.preventDefault();
                coverDropZone.classList.remove('border-rose-400', 'bg-rose-50');
            });
        });

        coverDropZone.addEventListener('drop', (event) => {
            const file = event.dataTransfer.files[0];

            if (!file) {
                return;
            }

            const transfer = new DataTransfer();
            transfer.items.add(file);
            coverInput.files = transfer.files;
            coverInput.dispatchEvent(new Event('change', { bubbles: true }));
        });

        draftFields.forEach((fieldName) => {
            const field = getFormField(fieldName);

            if (!field) {
                return;
            }

            field.addEventListener('input', saveDraft);
            field.addEventListener('change', saveDraft);
        });

        phoneInput?.addEventListener('input', () => {
            phoneInput.value = phoneInput.value.replace(/\D/g, '').slice(0, 11);
            phoneInput.setCustomValidity(
                phoneInput.value.length === 11 ? '' : 'Phone number must be exactly 11 digits.'
            );
        });

        nextBtn.addEventListener('click', () => {
            if (isStepAnimating) {
                return;
            }

            if (validateCurrentStep()) {
                showStep(Math.min(currentStep + 1, panels.length - 1));
            }
        });

        backBtn.addEventListener('click', () => {
            if (isStepAnimating) {
                return;
            }

            showStep(Math.max(currentStep - 1, 0));
        });

        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            if (!validateCurrentStep()) {
                return;
            }

            if (currentStep < panels.length - 1) {
                showStep(currentStep + 1);
                return;
            }

            if (!validateAllSteps()) {
                return;
            }

            submitBtn.disabled = true;
            submitBtn.textContent = 'Submitting...';

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: new FormData(form)
                });
                const result = await response.json();

                if (result.status === 'success') {
                    showFormMessage(result.message || 'Application submitted successfully.', 'success');
                    localStorage.removeItem(draftKey);
                    window.location.href = result.redirect || "<?= URLROOT ?>/supplier/pending";
                    return;
                }

                showFormMessage(result.message || 'Please check your supplier information and try again.');
            } catch (error) {
                showFormMessage('Something went wrong while submitting. Please try again.');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Submit application';
            }
        });

        const restoredStep = restoreDraft();

        if (restoredStep === currentStep) {
            setStepPanelVisibility();
            updateStepShell();
        } else {
            showStep(restoredStep, false);
        }
    </script>
</body>
</html>
