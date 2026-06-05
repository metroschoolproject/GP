<!-- <!DOCTYPE html>
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
                                        
                        1. Membership Fees
                        Supplier Member အဖြစ် စတင်လက်တွဲရန်အတွက် သတ်မှတ်ထားသော Members Fees ကို ကြိုတင်ပေးသွင်းရမည်။
                        2. Service Fees
                        Supplier မှ Admin ဘက်သို့ Service Fees ပေးချေသည့်အခါ ပေးချေငွေ၏ 10% ကို Admin Service Charge အဖြစ် ကောက်ခံမည်။
                        3. Booking Cancelation Policy
                        Supplier ဘက်မှ Booking Cancel ပြုလုပ်ပါက အောက်ပါစည်းကမ်းများကို လိုက်နာရမည်။
                        •	သတ်မှတ်ထားသော Service Date/Time မတိုင်မီ အချိန်တစ်ဝက်အလိုတွင် Cancel ပြုလုပ်ပါက
                        Customer အား စရံငွေပြန်လည်ပေးအပ်ရမည်ဖြစ်ပြီး၊ Customer ရွေးချယ်ထားသော Package တန်ဖိုး၏ 50% ကိုလည်း လျော်ကြေးအဖြစ် ပေးဆောင်ရမည်။ 
                        •	သတ်မှတ်ချိန်၏ တစ်ဝက်ကျော်သွားပြီးမှ Cancel ပြုလုပ်ပါက
                        Customer ရွေးချယ်ထားသော Package တန်ဖိုး၏ 100% ကို လျော်ကြေးအဖြစ် ပေးဆောင်ရမည်။ 
                        •	ထို့အပြင် Supplier သည် Admin ဘက်မှ သတ်မှတ်သော ကာလအပိုင်းအခြားတစ်ခုအတွင်း Temporary Ban ခံရနိုင်သည်။ 
                        4. Excessive Cancelation
                        Supplier Member သည် Booking Cancelation ကို 3 ကြိမ်ထက်ကျော်လွန်ပြုလုပ်ပါက Member အဖြစ်မှ အပြီးတိုင် ဖယ်ရှားမည်။
                        5. Customer Reviews
                        Customer များထံမှ Bad Review 5 ကြိမ်ထက်ကျော်လွန်လက်ခံရရှိပါက Supplier Member အဖြစ်မှ ဖယ်ရှားမည်။
                        6. Package Participation Requirement
                        Supplier သည် Package List တွင် စတင်ပါဝင်နိုင်ရန် အနည်းဆုံး Member ဝင်ပြီး 3 လအတွင်း Booking 5 ကြိမ် ရရှိထားရမည်။
                        7. Bonus Program
                        နှစ်စဉ် 3 လအတွင်း သတ်မှတ်ထားသော ရောင်းအား Target ပြည့်မီပြီး၊ အရောင်းရဆုံး နံပါတ် (1) Supplier ဖြစ်ပါက Admin ဘက်မှ Bonus ချီးမြှင့်ပေးမည်။
                        8. Agreement Acceptance
                        Supplier Member သည် ဤစာချုပ်ပါ စည်းကမ်းချက်များအား ဖတ်ရှုနားလည်ပြီး သဘောတူညီပါကြောင်း လက်မှတ်ရေးထိုးအတည်ပြုရမည်။
                        9. Payment Terms
                        Service ပြီးဆုံးပြီးနောက် သတ်မှတ်ထားသော အချိန်အတွင်း Supplier ထံသို့ ငွေပေးချေမှု ပြုလုပ်မည်။
                        Admin Service Fees နှင့် အခြားသတ်မှတ်ထားသော Charges များကို နုတ်ယူပြီးမှ ကျန်ရှိသောငွေကို Supplier ထံ လွှဲပြောင်းပေးမည်။
                        
                        10. Supplier Responsibilities
                        Supplier သည် Package တွင် ဖော်ပြထားသော Service Quality နှင့် အချိန်တိကျမှုကို တာဝန်ယူရမည်။
                        Late ဖြစ်ခြင်း၊ Service Quality မမှီခြင်း၊ သို့မဟုတ် Booking အတိုင်း ဝန်ဆောင်မှုမပေးနိုင်ခြင်းများ ဖြစ်ပေါ်ပါက Admin Team မှ Warning, Temporary Ban သို့မဟုတ် Member Removal အထိ အရေးယူနိုင်သည်။
                        
                        11. Fraud and Policy Violations
                        Supplier သည် Platform ပြင်ပ Customer များနှင့် တိုက်ရိုက်ဆက်သွယ်ပြီး ငွေလက်ခံခြင်း၊ Fake Booking ပြုလုပ်ခြင်း၊ Fake Review တင်ခြင်း သို့မဟုတ် လိမ်လည်မှုတစ်စုံတစ်ရာ ပြုလုပ်ခြင်း မပြုရ။
                        စည်းကမ်းဖောက်ဖျက်မှု တွေ့ရှိပါက Admin Team မှ Member အဖြစ်မှ ချက်ချင်းဖယ်ရှားနိုင်သည်။
                        
                        12. Customer Cancelation and No-Show Policy
                        Customer ဘက်မှ သတ်မှတ်ချိန်နီးကပ်မှ Booking Cancel ပြုလုပ်ပါက စရံငွေကို ပြန်လည်မပေးနိုင်ပါ။
                        Customer သည် သတ်မှတ်ထားသောအချိန်တွင် မပေါ်လာပါက Booking ကို Completed အဖြစ် သတ်မှတ်နိုင်သည်။
                        
                        13. Marketing and Content Usage
                        Supplier တင်ထားသော Photo, Video, Logo, Description နှင့် အခြား Content များကို Admin Team မှ Marketing, Advertising နှင့် Promotion ရည်ရွယ်ချက်များအတွက် အသုံးပြုခွင့်ရှိသည်။
                        
                        14. Price Control Policy
                        Booking Confirmed ဖြစ်ပြီးနောက် Supplier သည် သတ်မှတ်ထားသော Package Price ကို တိုးမြှင့်ခြင်း သို့မဟုတ် ပြောင်းလဲခြင်း မပြုလုပ်ရ။
                        
                        15. Confidentiality
                        Supplier သည် Customer ၏ ကိုယ်ရေးအချက်အလက်များ၊ ဖုန်းနံပါတ်များ၊ လိပ်စာများနှင့် အခြား Personal Information များကို ခွင့်ပြုချက်မရှိဘဲ မျှဝေခြင်း၊ ဖြန့်ဝေခြင်း မပြုရ။
                        
                        16. Agreement Updates
                        Admin Team သည် လိုအပ်ပါက ဤ Agreement ပါ စည်းကမ်းချက်များကို ပြင်ဆင်၊ ထပ်မံဖြည့်စွက်နိုင်ပြီး Supplier များအား ကြိုတင်အသိပေးမည်။
                        
                        17. Force Majeure
                        သဘာဝဘေးအန္တရာယ်၊ မီးလောင်မှု၊ စစ်ရေးအခြေအနေ၊ Internet/Network ပြတ်တောက်မှု သို့မဟုတ် ထိန်းချုပ်မရသော အခြေအနေများကြောင့် Service မပေးနိုင်ပါက နှစ်ဖက်စလုံးအား တာဝန်ယူမှုကင်းလွတ်ခွင့် ရှိသည်။

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
</html> -->


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Onboarding - <?= APPNAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Pinyon+Script&family=Cormorant+Garamond:ital,wght@0,400;0,500;1,400&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --env-bg: #d99aa3;
            --env-dark: #8a4e5a;
            --env-border: rgba(198, 154, 112, 0.48);
            --paper: #fff7ed;
            --accent: #5d3043;
            --accent-hover: #442231;
            --gold: #b38a52;
            --ink-soft: rgba(38, 20, 28, 0.72);
            --focus-color: rgb(247, 236, 236);
            --input-field-color: rgba(255, 250, 245, 0.84);
            --header-font: "Pinyon Script", cursive;
            --body-font: "Cormorant Garamond", Georgia, serif;
            --ui-font: system-ui, -apple-system, sans-serif;
        }

        * { box-sizing: border-box; }

        body {
            min-height: 100vh;
            margin: 0;
            font-family: var(--body-font);
            color: rgba(38, 20, 28, 0.92);
            /* background:
                linear-gradient(125deg, rgba(255, 248, 240, 0.28), transparent 32%),
                linear-gradient(145deg, #e8b4b8 0%, #ca858f 45%, #965a68 100%); */

                            background:
                linear-gradient(90deg, rgba(179, 138, 82, 0.14), transparent 120px),
                rgba(255, 247, 237, 0.94);
        }

        /* ── Progress bar ── */
        .progress-track {
            position: fixed;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: rgba(255, 250, 245, 0.22);
            z-index: 100;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #fff7ed, #d7b06d);
            transition: width 0.6s cubic-bezier(0.4,0,0.2,1);
            border-radius: 0 2px 2px 0;
        }
        .step-counter {
            position: fixed;
            top: 12px; right: 18px;
            font-family: var(--ui-font);
            font-size: 11px; font-weight: 500;
            letter-spacing: 0.08em;
            color: rgba(255,255,255,0.72);
            z-index: 100;
        }

        /* ── Luxury application surface ── */
        .supplier-card {
            position: relative;
            isolation: isolate;
            background:
                linear-gradient(90deg, rgba(179, 138, 82, 0.14), transparent 120px),
                rgba(255, 247, 237, 0.94);
            border: 1px solid rgba(255, 247, 237, 0.52);
            border-top-color: rgba(255, 247, 237, 0.82);
            border-radius: 4px;
            box-shadow:
                0 34px 90px rgba(57, 19, 35, 0.28),
                0 1px 0 rgba(255, 255, 255, 0.52) inset;
            overflow: hidden;
            width: 100%;
            /* max-width: 720px; */
        }
        .supplier-card::before,
        .supplier-card::after {
            content: "";
            position: absolute;
            pointer-events: none;
            z-index: 1;
        }
        .supplier-card::before {
            inset: 18px;
            border: 1px solid rgba(179, 138, 82, 0.42);
        }
        .supplier-card::after {
            top: 0;
            bottom: 0;
            left: 72px;
            width: 1px;
            background: linear-gradient(
                to bottom,
                transparent,
                rgba(179, 138, 82, 0.64) 16%,
                rgba(179, 138, 82, 0.64) 84%,
                transparent
            );
        }
        .supplier-card > *:not(.sparkle-canvas) {
            position: relative;
            z-index: 2;
        }

        /* ── Sparkle canvas ── */
        .sparkle-canvas {
            position: absolute;
            inset: 0;
            width: 100%; height: 100%;
            pointer-events: none;
            z-index: 3;
        }

        /* ── Typography ── */
        .script-heading {
            font-family: var(--header-font);
            color: var(--accent);
            text-shadow: 0 1px 0 rgba(255, 255, 255, 0.5);
        }
        .eyebrow {
            font-family: var(--ui-font);
            font-size: 10px; font-weight: 600;
            letter-spacing: 0.12em; text-transform: uppercase;
            color: rgba(93, 48, 67, 0.62);
            margin-bottom: 6px;
        }
        .panel-question {
            font-family: var(--body-font);
            font-size: 32px; font-weight: 400;
            line-height: 1.28;
            color: rgba(38,20,28,0.92);
            margin-bottom: 6px;
        }
        .panel-hint {
            font-family: var(--ui-font);
            font-size: 13px;
            color: var(--ink-soft);
            line-height: 1.55;
            margin-bottom: 30px;
        }

        /* ── Inputs ── */
        #supplierOnboardingForm input:not([type="file"]):not([type="checkbox"]),
        #supplierOnboardingForm select,
        #supplierOnboardingForm textarea {
            width: 100%;
            background: var(--input-field-color);
            border: 1px solid rgba(179, 138, 82, 0.36);
            border-radius: 3px;
            padding: 15px 16px;
            font-size: 17px;
            font-family: var(--body-font);
            color: rgba(15,1,1,0.9);
            box-shadow: 0 1px 0 rgba(255, 255, 255, 0.62) inset;
            transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;
            outline: none;
        }
        #supplierOnboardingForm input:not([type="file"]):not([type="checkbox"]):focus,
        #supplierOnboardingForm select:focus,
        #supplierOnboardingForm textarea:focus {
            border-color: var(--accent);
            background: var(--focus-color);
            box-shadow: 0 0 0 3px rgba(179, 138, 82, 0.18);
        }
        #supplierOnboardingForm input:not([type="file"]):not([type="checkbox"]) {
            min-height: 52px;
        }
        #supplierOnboardingForm select {
            min-height: 52px;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%236d4c5b' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
            padding-right: 40px;
        }
        #supplierOnboardingForm textarea {
            min-height: 120px;
            resize: none;
        }
        .field-label {
            font-family: var(--ui-font);
            font-size: 11px; font-weight: 600;
            letter-spacing: 0.07em; text-transform: uppercase;
            color: rgba(93, 48, 67, 0.72);
            display: block; margin-bottom: 6px;
        }

        /* ── Choice tiles ── */
        .choice-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 28px;
        }
        .choice-tile {
            background: var(--input-field-color);
            border: 1px solid rgba(179, 138, 82, 0.34);
            border-radius: 3px;
            padding: 16px 14px;
            cursor: pointer;
            text-align: left;
            transition: border-color 0.18s, background 0.18s, transform 0.12s, box-shadow 0.18s;
            font-family: inherit;
            display: flex; flex-direction: column; gap: 3px;
        }
        .choice-tile:hover {
            border-color: var(--accent);
            background: rgba(247,236,236,0.9);
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(64,20,35,0.13);
        }
        .choice-tile.selected {
            border-color: var(--accent);
            background: rgba(255, 247, 237, 0.95);
            box-shadow: 0 0 0 3px rgba(179, 138, 82, 0.16);
        }
        .choice-tile .tile-icon { font-size: 22px; line-height: 1; margin-bottom: 4px; }
        .choice-tile .tile-label {
            font-size: 14px; font-weight: 500;
            color: var(--accent); font-family: var(--ui-font);
        }
        .choice-tile .tile-sub {
            font-size: 11px;
            color: rgba(109,76,91,0.6);
            font-family: var(--ui-font);
        }

        /* ── Upload zone ── */
        .upload-zone {
            border: 1px dashed rgba(179, 138, 82, 0.54);
            border-radius: 3px;
            background: rgba(255,250,246,0.62);
            padding: 32px 20px;
            text-align: center;
            cursor: pointer;
            transition: border-color 0.18s, background 0.18s;
            display: block;
            margin-bottom: 20px;
        }
        .upload-zone:hover,
        .upload-zone.drag-over {
            border-color: var(--accent);
            background: rgba(247,236,236,0.68);
        }

        /* ── Agreement box ── */
        .agreement-scroll {
            background: rgba(255,250,246,0.72);
            border: 1px solid rgba(179, 138, 82, 0.36);
            border-radius: 3px;
            padding: 16px 18px;
            max-height: 200px;
            overflow-y: auto;
            font-size: 13px;
            line-height: 1.75;
            color: rgba(38,20,28,0.82);
            font-family: var(--ui-font);
            margin-bottom: 16px;
            scroll-behavior: smooth;
        }
        .agreement-scroll::-webkit-scrollbar { width: 4px; }
        .agreement-scroll::-webkit-scrollbar-track { background: transparent; }
        .agreement-scroll::-webkit-scrollbar-thumb { background: rgba(109,76,91,0.3); border-radius: 4px; }

        /* ── Buttons ── */
        .btn-primary {
            background: var(--accent);
            color: white;
            border: none;
            border-radius: 3px;
            padding: 13px 28px;
            font-family: var(--ui-font);
            font-size: 14px; font-weight: 600;
            letter-spacing: 0.04em;
            cursor: pointer;
            box-shadow: 0 12px 26px rgba(64,20,35,0.22);
            transition: background 0.15s, transform 0.1s, box-shadow 0.15s;
        }
        .btn-primary:hover { background: var(--accent-hover); transform: translateY(-1px); box-shadow: 0 16px 30px rgba(64,20,35,0.28); }
        .btn-primary:active { transform: translateY(0); }
        .btn-primary:disabled { opacity: 0.45; cursor: not-allowed; transform: none; }
        .btn-primary.success { background: #4a7c59; }
        .btn-primary.success:hover { background: #3d6849; }

        .btn-back {
            background: none; border: none;
            font-family: var(--ui-font);
            font-size: 13px; font-weight: 500;
            color: rgba(109,76,91,0.65);
            cursor: pointer; padding: 8px 0;
            display: flex; align-items: center; gap: 5px;
            transition: color 0.15s;
        }
        .btn-back:hover { color: var(--accent); }

        .enter-hint {
            font-family: var(--ui-font);
            font-size: 11px;
            color: rgba(109,76,91,0.5);
            margin-right: 10px;
        }
        kbd {
            background: rgba(109,76,91,0.1);
            border: 1px solid rgba(179, 138, 82, 0.34);
            border-radius: 3px;
            padding: 1px 5px;
            font-size: 10px;
            font-family: var(--ui-font);
            color: var(--accent);
        }

        /* ── Step panels ── */
        .step-panel {
            transition: opacity 0.4s cubic-bezier(0.4,0,0.2,1),
                        transform 0.4s cubic-bezier(0.4,0,0.2,1),
                        filter 0.4s cubic-bezier(0.4,0,0.2,1);
            padding: 2rem 3.25rem 2.25rem 7rem !important;
        }
        .step-panel.is-dissolving {
            opacity: 0;
            filter: blur(5px);
            transform: translateY(14px) scale(0.984);
        }
        .step-panel.is-assembling {
            opacity: 0;
            filter: blur(5px);
            transform: translateY(-10px) scale(0.984);
        }

        /* ── Review panel ── */
        .review-row {
            display: flex; justify-content: space-between; align-items: baseline;
            padding: 9px 0;
            border-bottom: 1px solid rgba(109,76,91,0.1);
            font-size: 14px;
        }
        .review-row:last-child { border-bottom: none; }
        .review-key {
            font-family: var(--ui-font); font-size: 11px; font-weight: 600;
            letter-spacing: 0.06em; text-transform: uppercase;
            color: rgba(109,76,91,0.55); flex-shrink: 0; margin-right: 12px;
        }
        .review-val {
            color: rgba(38,20,28,0.88); text-align: right;
            font-family: var(--body-font); font-size: 15px;
            word-break: break-word;
        }

        /* ── Divider ── */
        .panel-divider {
            height: 1px;
            background: linear-gradient(90deg, rgba(179, 138, 82, 0.72), rgba(179, 138, 82, 0.08));
            margin: 0 0 32px;
        }

        .supplier-card > .px-8,
        .supplier-card form > .px-8.pb-6 {
            padding-left: 7rem !important;
            padding-right: 3.25rem !important;
        }

        .supplier-card form > #stepErrorGlobal {
            margin-left: 7rem !important;
            margin-right: 3.25rem !important;
        }

        /* ── Error message ── */
        #stepError {
            font-family: var(--ui-font);
            font-size: 13px;
        }

        /* ── Email field (readonly) ── */
        .email-readonly {
            width: 100%;
            background: rgba(255,250,246,0.62);
            border: 1px solid rgba(179, 138, 82, 0.34);
            border-radius: 3px;
            padding: 12px 16px;
            font-size: 15px;
            font-family: var(--body-font);
            color: rgba(109,76,91,0.7);
            outline: none;
        }

        /* ── Step char animation ── */
        .step-char {
            display: inline-block;
            white-space: pre;
            opacity: 0;
            filter: blur(8px);
            transform: scale(0.86);
        }

        /* ── Checkbox ── */
        .agree-check-row {
            display: flex; align-items: flex-start; gap: 10px;
            font-family: var(--ui-font); font-size: 13px;
            line-height: 1.6; color: rgba(38,20,28,0.82);
            cursor: pointer;
        }
        .agree-check-row input[type="checkbox"] {
            width: 16px; height: 16px; margin-top: 2px;
            accent-color: var(--accent);
            flex-shrink: 0;
        }

        @media (prefers-reduced-motion: reduce) {
            .step-panel, .step-char {
                transition: none !important;
                animation: none !important;
            }
        }
        @media (max-width: 560px) {
            .supplier-card {
                border-radius: 3px;
            }
            .supplier-card::before {
                inset: 12px;
            }
            .supplier-card::after {
                display: none;
            }
            .step-panel,
            .supplier-card > .px-8,
            .supplier-card form > .px-8.pb-6 {
                padding-left: 1.5rem !important;
                padding-right: 1.5rem !important;
            }
            .supplier-card form > #stepErrorGlobal {
                margin-left: 1.5rem !important;
                margin-right: 1.5rem !important;
            }
            .panel-question { font-size: 25px; }
            .choice-grid { grid-template-columns: 1fr 1fr; }
        }
    </style>
</head>
<body>
    <!-- Progress bar -->
    <div class="progress-track">
        <div class="progress-fill" id="progressFill" style="width: 14.28%"></div>
    </div>
    <div class="step-counter" id="stepCounter">1 of 7</div>

    <main class="mx-auto flex min-h-screen w-full max-w-4xl items-center justify-center px-4 py-14">
        <div class="supplier-card w-full">
            <canvas class="sparkle-canvas" id="supplierSparkleCanvas"></canvas>

            <!-- Card header -->
            <div class="px-8 pt-8 pb-2 text-center">
                <p class="eyebrow">Partner application</p>
                <h1 class="script-heading text-5xl mt-1">Golden Promise</h1>
            </div>

            <?php if (!empty($message)): ?>
                <div class="mx-8 mb-2 mt-4 rounded-xl border <?= !empty($submitted) ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-rose-200 bg-rose-50 text-rose-700' ?> px-4 py-3 text-sm font-sans">
                    <?= $message ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= URLROOT ?>/supplier/onboarding" enctype="multipart/form-data" id="supplierOnboardingForm" novalidate>

                <!-- Hidden email field (always submitted) -->
                <input type="hidden" name="email" value="<?= htmlspecialchars($email ?? '', ENT_QUOTES, 'UTF-8') ?>">

                <!-- ══════════════════════════════════════════
                     PANEL 0 — Welcome / email confirm
                ══════════════════════════════════════════ -->
                <div class="step-panel px-8 pt-6 pb-8" data-step-panel="0">
                    <div class="panel-divider"></div>
                    <div class="eyebrow">Account</div>
                    <h2 class="panel-question">Let's confirm who you are</h2>
                    <p class="panel-hint">You're registering as a supplier under this account.</p>

                    <div class="mb-6">
                        <label class="field-label">Account email</label>
                        <input class="email-readonly" type="email" value="<?= htmlspecialchars($email ?? '', ENT_QUOTES, 'UTF-8') ?>" readonly tabindex="-1">
                    </div>

                    <div class="flex items-center justify-between mt-2">
                        <span></span>
                        <div class="flex items-center">
                            <span class="enter-hint">Press <kbd>Enter</kbd></span>
                            <button type="button" id="nextStepBtn" class="btn-primary">Let's begin →</button>
                        </div>
                    </div>
                </div>

                <!-- ══════════════════════════════════════════
                     PANEL 1 — Business name
                ══════════════════════════════════════════ -->
                <div class="step-panel hidden px-8 pt-6 pb-8" data-step-panel="1">
                    <div class="panel-divider"></div>
                    <div class="eyebrow">Business identity</div>
                    <h2 class="panel-question supplier-panel-title">What's your business name?</h2>
                    <p class="panel-hint">This is how couples will find and recognise you on the platform.</p>

                    <div class="mb-6">
                        <input required name="business_name" type="text"
                               placeholder="e.g. Blossom & Co."
                               value="<?= htmlspecialchars($business_name ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <div class="flex items-center justify-between mt-2">
                        <button type="button" id="backStepBtn" class="btn-back">← Back</button>
                        <div class="flex items-center">
                            <span class="enter-hint">Press <kbd>Enter</kbd></span>
                            <button type="button" id="nextStepBtn" class="btn-primary">Continue →</button>
                        </div>
                    </div>
                </div>

                <!-- ══════════════════════════════════════════
                     PANEL 2 — Service category (choice tiles)
                ══════════════════════════════════════════ -->
                <div class="step-panel hidden px-8 pt-6 pb-8" data-step-panel="2">
                    <div class="panel-divider"></div>
                    <div class="eyebrow">Service category</div>
                    <h2 class="panel-question supplier-panel-title">What kind of service do you offer?</h2>
                    <p class="panel-hint">Choose the category that best describes your work.</p>

                    <!-- Hidden real select (submitted with form) -->
                    <select required name="category_id" id="categorySelect" class="sr-only" aria-hidden="true" tabindex="-1">
                        <?php $selectedCategoryId = (int)($category_id ?? 0); ?>
                        <option value="">Choose category</option>
                        <?php foreach (($categories ?? []) as $category): ?>
                            <?php $categoryId = (int)$category['id']; ?>
                            <option value="<?= $categoryId ?>" <?= $selectedCategoryId === $categoryId ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <!-- Visual tiles (JS-driven, syncs to select above) -->
                    <div class="choice-grid" id="categoryTiles">
                        <?php
                        $categoryIcons = [
                            'Photography' => ['📸', 'Photo & video'],
                            'Floral'      => ['🌸', 'Flowers & décor'],
                            'Catering'    => ['🎂', 'Food & cake'],
                            'Music'       => ['🎵', 'Music & events'],
                            'Beauty'      => ['💄', 'Hair & makeup'],
                            'Transport'   => ['🚗', 'Cars & logistics'],
                        ];
                        foreach (($categories ?? []) as $category):
                            $catId   = (int)$category['id'];
                            $catName = htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8');
                            $meta    = $categoryIcons[$category['name']] ?? ['🎀', 'Wedding service'];
                            $isSelected = $selectedCategoryId === $catId ? 'selected' : '';
                        ?>
                        <button type="button"
                                class="choice-tile <?= $isSelected ?>"
                                data-category-id="<?= $catId ?>"
                                data-category-name="<?= $catName ?>">
                            <span class="tile-icon"><?= $meta[0] ?></span>
                            <span class="tile-label"><?= $catName ?></span>
                            <span class="tile-sub"><?= htmlspecialchars($meta[1], ENT_QUOTES, 'UTF-8') ?></span>
                        </button>
                        <?php endforeach; ?>
                    </div>

                    <div class="flex items-center justify-between mt-2">
                        <button type="button" id="backStepBtn" class="btn-back">← Back</button>
                        <button type="button" id="nextStepBtn" class="btn-primary" disabled>Continue →</button>
                    </div>
                </div>

                <!-- ══════════════════════════════════════════
                     PANEL 3 — Service details (name + price)
                ══════════════════════════════════════════ -->
                <div class="step-panel hidden px-8 pt-6 pb-8" data-step-panel="3">
                    <div class="panel-divider"></div>
                    <div class="eyebrow">Your service</div>
                    <h2 class="panel-question supplier-panel-title">Tell us about your service</h2>
                    <p class="panel-hint">A clear name and starting price helps couples find you quickly.</p>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="field-label">Service name</label>
                            <input required name="service_name" type="text"
                                   placeholder="e.g. Full-day coverage"
                                   value="<?= htmlspecialchars($service_name ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div>
                            <label class="field-label">Starting price (MMK)</label>
                            <input required name="service_price" type="number" min="0" step="0.01"
                                   placeholder="0"
                                   value="<?= htmlspecialchars($service_price ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="field-label">Website or social link</label>
                        <input required name="business_url" type="url"
                               placeholder="https://example.com"
                               value="<?= htmlspecialchars($business_url ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <div class="flex items-center justify-between mt-6">
                        <button type="button" id="backStepBtn" class="btn-back">← Back</button>
                        <div class="flex items-center">
                            <span class="enter-hint">Press <kbd>Enter</kbd></span>
                            <button type="button" id="nextStepBtn" class="btn-primary">Continue →</button>
                        </div>
                    </div>
                </div>

                <!-- ══════════════════════════════════════════
                     PANEL 4 — Contact & location
                ══════════════════════════════════════════ -->
                <div class="step-panel hidden px-8 pt-6 pb-8" data-step-panel="4">
                    <div class="panel-divider"></div>
                    <div class="eyebrow">Contact details</div>
                    <h2 class="panel-question supplier-panel-title">How can clients reach you?</h2>
                    <p class="panel-hint">Your phone and location help us match you with nearby couples.</p>

                    <div class="grid grid-cols-2 gap-4 mb-2">
                        <div>
                            <label class="field-label">Phone number</label>
                            <input required name="phone" type="tel" inputmode="numeric"
                                   pattern="[0-9]{11}" minlength="11" maxlength="11"
                                   title="Phone number must be exactly 11 digits."
                                   placeholder="09xxxxxxxxx"
                                   value="<?= htmlspecialchars($phone ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div>
                            <label class="field-label">Business address</label>
                            <input required name="business_address" type="text"
                                   placeholder="City or full address"
                                   value="<?= htmlspecialchars($business_address ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>

                    <div class="flex items-center justify-between mt-6">
                        <button type="button" id="backStepBtn" class="btn-back">← Back</button>
                        <div class="flex items-center">
                            <span class="enter-hint">Press <kbd>Enter</kbd></span>
                            <button type="button" id="nextStepBtn" class="btn-primary">Continue →</button>
                        </div>
                    </div>
                </div>

                <!-- ══════════════════════════════════════════
                     PANEL 5 — Descriptions + cover photo
                ══════════════════════════════════════════ -->
                <div class="step-panel hidden px-8 pt-6 pb-8" data-step-panel="5">
                    <div class="panel-divider"></div>
                    <div class="eyebrow">Your story</div>
                    <h2 class="panel-question supplier-panel-title">Describe your business & service</h2>
                    <p class="panel-hint">Couples read this first. Keep it warm and genuine.</p>

                    <div class="mb-4">
                        <label class="field-label">Business description</label>
                        <textarea required name="business_description"
                                  placeholder="We specialise in capturing..."><?= htmlspecialchars($business_description ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="field-label">Service description</label>
                        <textarea required name="service_description"
                                  placeholder="Our signature package includes..."><?= htmlspecialchars($service_description ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>

                    <!-- Cover photo upload -->
                    <label class="field-label mt-2">Cover photo</label>
                    <label id="coverDropZone" for="coverPhotoInput" class="upload-zone">
                        <input required id="coverPhotoInput" name="cover_photo" type="file"
                               accept="image/jpeg,image/png,image/webp" class="sr-only">
                        <div class="text-4xl mb-3">🖼️</div>
                        <p class="text-sm font-semibold text-stone-700 mb-1">Click or drag & drop your best photo</p>
                        <p class="text-xs text-stone-500">JPG, PNG, WEBP · max 5 MB</p>
                        <span id="coverPhotoName" class="hidden mt-3 inline-block rounded-full bg-white px-3 py-1 text-xs font-semibold text-stone-700 shadow-sm"></span>
                    </label>

                    <label class="field-label mt-2">Business license</label>
                    <label for="businessLicenseInput" class="upload-zone">
                        <input required id="businessLicenseInput" name="business_license" type="file"
                               accept="image/jpeg,image/png,image/webp,application/pdf" class="sr-only">
                        <div class="text-4xl mb-3">▣</div>
                        <p class="text-sm font-semibold text-stone-700 mb-1">Upload your license or registration document</p>
                        <p class="text-xs text-stone-500">PDF, JPG, PNG, WEBP · max 5 MB</p>
                        <span id="businessLicenseName" class="hidden mt-3 inline-block rounded-full bg-white px-3 py-1 text-xs font-semibold text-stone-700 shadow-sm"></span>
                    </label>

                    <div class="flex items-center justify-between mt-4">
                        <button type="button" id="backStepBtn" class="btn-back">← Back</button>
                        <button type="button" id="nextStepBtn" class="btn-primary">Continue →</button>
                    </div>
                </div>

                <!-- ══════════════════════════════════════════
                     PANEL 6 — Agreement + submit
                ══════════════════════════════════════════ -->
                <div class="step-panel hidden px-8 pt-6 pb-8" data-step-panel="6">
                    <div class="panel-divider"></div>
                    <div class="eyebrow">Almost done</div>
                    <h2 class="panel-question supplier-panel-title">Review the supplier agreement</h2>
                    <p class="panel-hint">Please read through the terms before submitting your application.</p>

                    <div class="agreement-scroll mb-4">
                        <strong>1. Membership Fees</strong><br>
                        Supplier Member အဖြစ် စတင်လက်တွဲရန်အတွက် သတ်မှတ်ထားသော Members Fees ကို ကြိုတင်ပေးသွင်းရမည်။<br><br>
                        <strong>2. Service Fees</strong><br>
                        Supplier မှ Admin ဘက်သို့ Service Fees ပေးချေသည့်အခါ ပေးချေငွေ၏ 10% ကို Admin Service Charge အဖြစ် ကောက်ခံမည်။<br><br>
                        <strong>3. Booking Cancelation Policy</strong><br>
                        Supplier ဘက်မှ Booking Cancel ပြုလုပ်ပါက အောက်ပါစည်းကမ်းများကို လိုက်နာရမည်။<br>
                        • သတ်မှတ်ထားသော Service Date/Time မတိုင်မီ အချိန်တစ်ဝက်အလိုတွင် Cancel ပြုလုပ်ပါက Customer အား စရံငွေပြန်လည်ပေးအပ်ရမည်ဖြစ်ပြီး Package တန်ဖိုး၏ 50% ကိုလည်း လျော်ကြေးအဖြစ် ပေးဆောင်ရမည်။<br>
                        • သတ်မှတ်ချိန်၏ တစ်ဝက်ကျော်သွားပြီးမှ Cancel ပြုလုပ်ပါက Package တန်ဖိုး၏ 100% ကို လျော်ကြေးအဖြစ် ပေးဆောင်ရမည်။<br><br>
                        <strong>4. Excessive Cancelation</strong><br>
                        Booking Cancelation ကို 3 ကြိမ်ထက်ကျော်လွန်ပြုလုပ်ပါက Member အဖြစ်မှ အပြီးတိုင် ဖယ်ရှားမည်။<br><br>
                        <strong>5. Customer Reviews</strong><br>
                        Bad Review 5 ကြိမ်ထက်ကျော်လွန်လက်ခံရရှိပါက Supplier Member အဖြစ်မှ ဖယ်ရှားမည်။<br><br>
                        <strong>6. Package Participation Requirement</strong><br>
                        Package List တွင် စတင်ပါဝင်နိုင်ရန် Member ဝင်ပြီး 3 လအတွင်း Booking 5 ကြိမ် ရရှိထားရမည်။<br><br>
                        <strong>7. Bonus Program</strong><br>
                        နှစ်စဉ် 3 လအတွင်း သတ်မှတ်ထားသော ရောင်းအား Target ပြည့်မီပြီး အရောင်းရဆုံး နံပါတ် (1) Supplier ဖြစ်ပါက Admin ဘက်မှ Bonus ချီးမြှင့်ပေးမည်။<br><br>
                        <strong>8. Agreement Acceptance</strong><br>
                        Supplier Member သည် ဤစာချုပ်ပါ စည်းကမ်းချက်များအား ဖတ်ရှုနားလည်ပြီး သဘောတူညီပါကြောင်း လက်မှတ်ရေးထိုးအတည်ပြုရမည်။<br><br>
                        <strong>9. Payment Terms</strong><br>
                        Service ပြီးဆုံးပြီးနောက် သတ်မှတ်ထားသော အချိန်အတွင်း Supplier ထံသို့ ငွေပေးချေမှု ပြုလုပ်မည်။ Admin Service Fees နှင့် အခြားသတ်မှတ်ထားသော Charges များကို နုတ်ယူပြီးမှ ကျန်ရှိသောငွေကို Supplier ထံ လွှဲပြောင်းပေးမည်။<br><br>
                        <strong>10. Supplier Responsibilities</strong><br>
                        Supplier သည် Package တွင် ဖော်ပြထားသော Service Quality နှင့် အချိန်တိကျမှုကို တာဝန်ယူရမည်။<br><br>
                        <strong>11. Fraud and Policy Violations</strong><br>
                        Supplier သည် Platform ပြင်ပ Customer များနှင့် တိုက်ရိုက်ဆက်သွယ်ပြီး ငွေလက်ခံခြင်း၊ Fake Booking/Review ပြုလုပ်ခြင်း မပြုရ။<br><br>
                        <strong>12. Customer Cancelation and No-Show Policy</strong><br>
                        Customer ဘက်မှ သတ်မှတ်ချိန်နီးကပ်မှ Cancel ပြုလုပ်ပါက စရံငွေကို ပြန်လည်မပေးနိုင်ပါ။<br><br>
                        <strong>13. Marketing and Content Usage</strong><br>
                        Supplier တင်ထားသော Photo, Video, Logo, Description များကို Admin Team မှ Marketing ရည်ရွယ်ချက်များအတွက် အသုံးပြုခွင့်ရှိသည်။<br><br>
                        <strong>14. Price Control Policy</strong><br>
                        Booking Confirmed ဖြစ်ပြီးနောက် Package Price ကို တိုးမြှင့်ခြင်း သို့မဟုတ် ပြောင်းလဲခြင်း မပြုလုပ်ရ။<br><br>
                        <strong>15. Confidentiality</strong><br>
                        Customer ၏ Personal Information များကို ခွင့်ပြုချက်မရှိဘဲ မျှဝေခြင်း မပြုရ။<br><br>
                        <strong>16. Agreement Updates</strong><br>
                        Admin Team သည် လိုအပ်ပါက Agreement ပါ စည်းကမ်းချက်များကို ပြင်ဆင်နိုင်ပြီး Supplier များအား ကြိုတင်အသိပေးမည်။<br><br>
                        <strong>17. Force Majeure</strong><br>
                        သဘာဝဘေးအန္တရာယ်၊ စစ်ရေးအခြေအနေ၊ Network ပြတ်တောက်မှု သို့မဟုတ် ထိန်းချုပ်မရသော အခြေအနေများကြောင့် Service မပေးနိုင်ပါက နှစ်ဖက်စလုံးအား တာဝန်ယူမှုကင်းလွတ်ခွင့် ရှိသည်။
                    </div>

                    <label class="agree-check-row mb-6">
                        <input required name="agreement_accepted" type="checkbox" value="1" <?= !empty($agreement_accepted) ? 'checked' : '' ?>>
                        <span>I have read and agree to the Golden Promise supplier business agreement.</span>
                    </label>

                    <p id="stepError" class="hidden rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 mb-4"></p>

                    <div class="flex items-center justify-between">
                        <button type="button" id="backStepBtn" class="btn-back">← Back</button>
                        <button type="submit" id="submitStepBtn" class="btn-primary success">Submit application ✦</button>
                    </div>
                </div>

                <!-- Global error (non-last panels) -->
                <p id="stepErrorGlobal" class="hidden mx-8 mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700"></p>

                <!-- Back home link -->
                <div class="px-8 pb-6 text-center">
                    <a href="<?= URLROOT ?>/main/home" class="text-xs font-sans text-stone-500 hover:text-stone-700 transition-colors">← Back to home</a>
                </div>

            </form>
        </div>
    </main>

    <script>
    (() => {
        // ── Element refs ──
        const form        = document.getElementById('supplierOnboardingForm');
        const panels      = Array.from(document.querySelectorAll('[data-step-panel]'));
        const progressFill = document.getElementById('progressFill');
        const stepCounter  = document.getElementById('stepCounter');
        const coverInput   = document.getElementById('coverPhotoInput');
        const coverDropZone = document.getElementById('coverDropZone');
        const coverPhotoName = document.getElementById('coverPhotoName');
        const businessLicenseInput = document.getElementById('businessLicenseInput');
        const businessLicenseName = document.getElementById('businessLicenseName');
        const phoneInput   = form.querySelector('[name="phone"]');
        const sparkleCanvas = document.getElementById('supplierSparkleCanvas');
        const sparkleCtx   = sparkleCanvas.getContext('2d');
        const categorySelect = document.getElementById('categorySelect');
        const categoryTiles  = document.getElementById('categoryTiles');

        const TOTAL_STEPS = panels.length; // 7

        const draftKey = 'gp_supplier_onboarding_' + encodeURIComponent(
            (form.querySelector('[name="email"]') || {}).value || 'guest'
        );
        const draftFields = [
            'business_name','business_description','phone','business_address',
            'category_id','service_name','service_description',
            'service_price','business_url','agreement_accepted'
        ];

        let currentStep = 0;
        let isStepAnimating = false;
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        // ── Sparkle engine ──
        const sparkleParticles = [];

        function resizeSparkleCanvas() {
            sparkleCanvas.width  = sparkleCanvas.parentElement.offsetWidth;
            sparkleCanvas.height = sparkleCanvas.parentElement.offsetHeight;
        }

        class SparkleParticle {
            constructor(x, y) {
                this.x = x; this.y = y;
                const angle = Math.random() * Math.PI * 2;
                const speed = 0.35 + Math.random() * 1.4;
                this.vx = Math.cos(angle) * speed;
                this.vy = Math.sin(angle) * speed - 0.35;
                this.life  = 1;
                this.decay = 0.02 + Math.random() * 0.025;
                this.size  = 0.4 + Math.random() * 1;
                this.phase = Math.random() * Math.PI * 2;
                this.color = ['rgba(255,182,193,0.95)','rgba(255,105,180,0.82)','rgba(219,112,147,0.72)','rgba(255,240,245,0.88)'][Math.floor(Math.random() * 4)];
            }
            update() {
                this.x += this.vx; this.y += this.vy;
                this.vy += 0.025; this.vx *= 0.97;
                this.life -= this.decay; this.phase += 0.2;
            }
            draw(ctx) {
                const alpha = Math.max(0, this.life) * (0.6 + 0.4 * Math.sin(this.phase));
                ctx.globalAlpha = alpha;
                ctx.fillStyle = this.color;
                ctx.beginPath(); ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2); ctx.fill();
                ctx.globalAlpha = alpha * 0.22;
                ctx.beginPath(); ctx.arc(this.x, this.y, this.size * 2.4, 0, Math.PI * 2); ctx.fill();
            }
        }

        function emitSparklesAtElement(element, count = 30) {
            if (!element) return;
            const canvasRect = sparkleCanvas.getBoundingClientRect();
            const rect = element.getBoundingClientRect();
            const cx = rect.left - canvasRect.left + rect.width  / 2;
            const cy = rect.top  - canvasRect.top  + rect.height / 2;
            for (let i = 0; i < count; i++) sparkleParticles.push(new SparkleParticle(cx, cy));
        }

        function emitSparklesAtPoint(x, y, count = 8) {
            for (let i = 0; i < count; i++) sparkleParticles.push(new SparkleParticle(x, y));
        }

        function sparkleLoop() {
            sparkleCtx.clearRect(0, 0, sparkleCanvas.width, sparkleCanvas.height);
            for (let i = sparkleParticles.length - 1; i >= 0; i--) {
                sparkleParticles[i].update();
                sparkleParticles[i].draw(sparkleCtx);
                if (sparkleParticles[i].life <= 0) sparkleParticles.splice(i, 1);
            }
            requestAnimationFrame(sparkleLoop);
        }

        resizeSparkleCanvas();
        window.addEventListener('resize', resizeSparkleCanvas);
        sparkleLoop();

        // ── Title char animation ──
        function buildTitleSpans(title, hidden = false) {
            if (!title) return;
            const text = title.dataset.titleText || title.textContent;
            title.dataset.titleText = text;
            title.innerHTML = '';
            for (let i = 0; i < text.length; i++) {
                const span = document.createElement('span');
                span.className = 'step-char';
                span.textContent = text[i];
                if (!hidden) {
                    span.style.opacity   = '1';
                    span.style.filter    = 'blur(0)';
                    span.style.transform = 'scale(1)';
                }
                title.appendChild(span);
            }
        }

        function getCharRects(title) {
            if (!title) return [];
            const canvasRect = sparkleCanvas.getBoundingClientRect();
            return Array.from(title.querySelectorAll('.step-char')).map(char => {
                const r = char.getBoundingClientRect();
                return { char, cx: r.left - canvasRect.left + r.width / 2, cy: r.top - canvasRect.top + r.height / 2 };
            });
        }

        function dissolveTitle(panel) {
            return new Promise(resolve => {
                const title = panel.querySelector('.supplier-panel-title');
                if (!title || prefersReducedMotion) { resolve(); return; }
                buildTitleSpans(title, false);
                const chars = getCharRects(title);
                chars.forEach(({ char, cx, cy }, i) => {
                    setTimeout(() => {
                        if (char.textContent.trim()) emitSparklesAtPoint(cx, cy, 7);
                        char.style.transition = 'opacity 0.36s ease, filter 0.36s ease, transform 0.36s ease';
                        char.style.opacity = '0'; char.style.filter = 'blur(6px)'; char.style.transform = 'scale(0.87)';
                    }, i * 16);
                });
                setTimeout(resolve, chars.length * 16 + 200);
            });
        }

        function assembleTitle(panel) {
            return new Promise(resolve => {
                const title = panel.querySelector('.supplier-panel-title');
                if (!title || prefersReducedMotion) { resolve(); return; }
                buildTitleSpans(title, true);
                const chars = getCharRects(title);
                chars.forEach(({ char, cx, cy }, i) => {
                    setTimeout(() => {
                        if (char.textContent.trim()) emitSparklesAtPoint(cx, cy, 5);
                        char.style.transition = 'opacity 0.44s cubic-bezier(0,0,0.2,1), filter 0.44s cubic-bezier(0,0,0.2,1), transform 0.44s cubic-bezier(0,0,0.2,1)';
                        char.style.opacity = '1'; char.style.filter = 'blur(0)'; char.style.transform = 'scale(1)';
                    }, i * 18);
                });
                setTimeout(resolve, chars.length * 18 + 480);
            });
        }

        // ── Content stagger ──
        function contentElements(panel) {
            return Array.from(panel.children).filter(c => !c.classList.contains('supplier-panel-title') && !c.classList.contains('panel-divider'));
        }

        function hideContent(panel) {
            contentElements(panel).forEach((child, i) => {
                setTimeout(() => {
                    child.style.transition = 'opacity 0.3s ease, transform 0.3s ease, filter 0.3s ease';
                    child.style.opacity = '0'; child.style.transform = 'translateY(10px)'; child.style.filter = 'blur(3px)';
                }, i * 40);
            });
        }

        function showContent(panel) {
            const els = contentElements(panel);
            els.forEach(child => { child.style.opacity = '0'; child.style.transform = 'translateY(10px)'; child.style.filter = 'blur(3px)'; });
            requestAnimationFrame(() => {
                els.forEach((child, i) => {
                    setTimeout(() => {
                        child.style.transition = 'opacity 0.4s cubic-bezier(0,0,0.2,1), transform 0.4s cubic-bezier(0,0,0.2,1), filter 0.4s cubic-bezier(0,0,0.2,1)';
                        child.style.opacity = '1'; child.style.transform = 'translateY(0)'; child.style.filter = 'blur(0)';
                    }, i * 65);
                });
            });
        }

        function resetContent(panel) {
            contentElements(panel).forEach(child => {
                child.style.transition = ''; child.style.opacity = ''; child.style.transform = ''; child.style.filter = '';
            });
        }

        // ── Progress & step shell ──
        function updateProgress() {
            const pct = ((currentStep + 1) / TOTAL_STEPS) * 100;
            progressFill.style.width = pct + '%';
            stepCounter.textContent  = (currentStep + 1) + ' of ' + TOTAL_STEPS;
        }

        function setVisibility() {
            panels.forEach((panel, i) => {
                panel.classList.remove('is-dissolving', 'is-assembling');
                const active = i === currentStep;
                panel.classList.toggle('hidden', !active);
                panel.classList.toggle('grid',   active);
                resetContent(panel);
                buildTitleSpans(panel.querySelector('.supplier-panel-title'), false);
            });
        }

        // ── Navigation ──
        async function showStep(index, animate = true) {
            if (isStepAnimating || index === currentStep) return;
            if (index < 0 || index >= TOTAL_STEPS) return;

            const prevPanel = panels[currentStep];
            const nextPanel = panels[index];
            isStepAnimating = animate && !prefersReducedMotion;

            setNavDisabled(isStepAnimating);

            if (isStepAnimating) {
                hideContent(prevPanel);
                prevPanel.classList.add('is-dissolving');
                await dissolveTitle(prevPanel);
            }

            currentStep = index;
            saveDraft();
            setVisibility();
            updateProgress();
            updateCategoryNextBtn();

            if (isStepAnimating) {
                nextPanel.classList.add('is-assembling');
                requestAnimationFrame(() => {
                    nextPanel.classList.remove('is-assembling');
                    showContent(nextPanel);
                });
                emitSparklesAtElement(progressFill, 22);
                await assembleTitle(nextPanel);
            } else {
                emitSparklesAtElement(progressFill, 14);
            }

            setNavDisabled(false);
            isStepAnimating = false;

            // Focus first input in new panel
            const firstInput = nextPanel.querySelector('input:not([type="hidden"]):not([type="checkbox"]):not(.email-readonly), select, textarea');
            if (firstInput && firstInput.type !== 'file') setTimeout(() => firstInput.focus(), 80);
        }

        function setNavDisabled(disabled) {
            panels.forEach(panel => {
                panel.querySelectorAll('#nextStepBtn, #backStepBtn, #submitStepBtn').forEach(btn => {
                    btn.disabled = disabled;
                });
            });
        }

        // ── Validation ──
        function clearError() {
            document.querySelectorAll('#stepError, #stepErrorGlobal').forEach(el => {
                el.classList.add('hidden'); el.textContent = '';
            });
        }

        function showError(msg, panelIndex) {
            const errorEl = (panelIndex === TOTAL_STEPS - 1)
                ? document.getElementById('stepError')
                : document.getElementById('stepErrorGlobal');
            if (!errorEl) return;
            errorEl.textContent = msg;
            errorEl.classList.remove('hidden');
        }

        function validateStep(stepIndex) {
            const fields = Array.from(panels[stepIndex].querySelectorAll('input:not([aria-hidden]), select:not([aria-hidden]), textarea'));
            const invalidField = fields.find(f => !f.checkValidity());
            if (!invalidField) { clearError(); return true; }
            if (stepIndex !== currentStep) showStep(stepIndex, false);
            invalidField.reportValidity();
            invalidField.focus();
            showError(invalidField.validationMessage || 'Please complete this field before continuing.', stepIndex);
            return false;
        }

        function validateCurrentStep() { return validateStep(currentStep); }
        function validateAllSteps()    { return panels.every((_, i) => validateStep(i)); }

        // ── Category tiles ──
        function updateCategoryNextBtn() {
            if (currentStep !== 2) return;
            const nextBtn = panels[2].querySelector('#nextStepBtn');
            if (nextBtn) nextBtn.disabled = !categorySelect?.value;
        }

        if (categoryTiles) {
            categoryTiles.addEventListener('click', e => {
                const tile = e.target.closest('.choice-tile');
                if (!tile) return;
                categoryTiles.querySelectorAll('.choice-tile').forEach(t => t.classList.remove('selected'));
                tile.classList.add('selected');
                if (categorySelect) categorySelect.value = tile.dataset.categoryId;
                updateCategoryNextBtn();
                // Auto-advance after brief delay
                setTimeout(() => {
                    if (validateCurrentStep()) showStep(currentStep + 1);
                }, 320);
            });
        }

        // ── Enter key to advance ──
        form.addEventListener('keydown', e => {
            if (e.key !== 'Enter') return;
            const tag = e.target.tagName;
            if (tag === 'TEXTAREA') return; // allow newlines
            if (e.target.type === 'checkbox') return;
            e.preventDefault();
            const panel = panels[currentStep];
            const nextBtn = panel.querySelector('#nextStepBtn');
            if (nextBtn && !nextBtn.disabled) nextBtn.click();
        });

        // ── Next / Back button delegation ──
        panels.forEach((panel, panelIndex) => {
            panel.addEventListener('click', e => {
                if (isStepAnimating) return;
                const btn = e.target.closest('button');
                if (!btn) return;
                if (btn.id === 'nextStepBtn') {
                    if (validateCurrentStep()) showStep(currentStep + 1);
                }
                if (btn.id === 'backStepBtn') {
                    showStep(currentStep - 1);
                }
            });
        });

        // ── File upload ──
        coverInput?.addEventListener('change', () => {
            const file = coverInput.files[0];
            if (!file) { coverPhotoName.classList.add('hidden'); coverPhotoName.textContent = ''; return; }
            coverPhotoName.textContent = '✓ ' + file.name;
            coverPhotoName.classList.remove('hidden');
        });

        businessLicenseInput?.addEventListener('change', () => {
            const file = businessLicenseInput.files[0];
            if (!file) { businessLicenseName.classList.add('hidden'); businessLicenseName.textContent = ''; return; }
            businessLicenseName.textContent = '✓ ' + file.name;
            businessLicenseName.classList.remove('hidden');
        });

        ['dragenter', 'dragover'].forEach(ev => {
            coverDropZone?.addEventListener(ev, e => { e.preventDefault(); coverDropZone.classList.add('drag-over'); });
        });
        ['dragleave', 'drop'].forEach(ev => {
            coverDropZone?.addEventListener(ev, e => { e.preventDefault(); coverDropZone.classList.remove('drag-over'); });
        });
        coverDropZone?.addEventListener('drop', e => {
            const file = e.dataTransfer.files[0];
            if (!file) return;
            const dt = new DataTransfer(); dt.items.add(file);
            coverInput.files = dt.files;
            coverInput.dispatchEvent(new Event('change', { bubbles: true }));
        });

        // ── Phone — digits only ──
        phoneInput?.addEventListener('input', () => {
            phoneInput.value = phoneInput.value.replace(/\D/g, '').slice(0, 11);
            phoneInput.setCustomValidity(phoneInput.value.length === 11 ? '' : 'Phone number must be exactly 11 digits.');
        });

        // ── Draft save / restore ──
        function getFormField(name) {
            const field = form.elements[name];
            if (!field) return null;
            return typeof field.addEventListener === 'function' ? field : (field[0] || null);
        }

        function saveDraft() {
            const draft = { currentStep, updatedAt: new Date().toISOString(), fields: {} };
            draftFields.forEach(name => {
                const field = getFormField(name);
                if (!field) return;
                draft.fields[name] = field.type === 'checkbox' ? field.checked : field.value;
            });
            try { localStorage.setItem(draftKey, JSON.stringify(draft)); } catch (_) {}
        }

        function restoreDraft() {
            let draft = {};
            try { draft = JSON.parse(localStorage.getItem(draftKey)) || {}; } catch (_) {}
            if (!draft.fields) return 0;
            draftFields.forEach(name => {
                const field = getFormField(name);
                if (!field || typeof draft.fields[name] === 'undefined') return;
                if (field.type === 'checkbox') { field.checked = draft.fields[name] === true; return; }
                field.value = draft.fields[name];
            });
            // Sync category tiles after restore
            if (categorySelect?.value) {
                categoryTiles?.querySelectorAll('.choice-tile').forEach(t => {
                    t.classList.toggle('selected', t.dataset.categoryId === categorySelect.value);
                });
            }
            const step = parseInt(draft.currentStep, 10);
            return (Number.isInteger(step)) ? Math.min(Math.max(step, 0), TOTAL_STEPS - 1) : 0;
        }

        draftFields.forEach(name => {
            const field = getFormField(name);
            if (!field) return;
            field.addEventListener('input',  saveDraft);
            field.addEventListener('change', saveDraft);
        });

        // ── Form submit (AJAX) ──
        form.addEventListener('submit', async e => {
            e.preventDefault();
            if (!validateCurrentStep()) return;
            if (currentStep < TOTAL_STEPS - 1) { showStep(currentStep + 1); return; }
            if (!validateAllSteps()) return;

            const submitBtn = document.getElementById('submitStepBtn');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Submitting…';

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    body: new FormData(form)
                });
                const result = await response.json();

                if (result.status === 'success') {
                    clearError();
                    try { localStorage.removeItem(draftKey); } catch (_) {}
                    window.location.href = result.redirect || '<?= URLROOT ?>/supplier/pending';
                    return;
                }
                showError(result.message || 'Please check your information and try again.', currentStep);
            } catch (_) {
                showError('Something went wrong. Please try again.', currentStep);
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Submit application ✦';
            }
        });

        // ── Init ──
        const restoredStep = restoreDraft();
        if (restoredStep === 0) {
            setVisibility();
            updateProgress();
            updateCategoryNextBtn();
        } else {
            currentStep = restoredStep - 1; // showStep will increment
            showStep(restoredStep, false);
        }
    })();
    </script>
</body>
</html>
