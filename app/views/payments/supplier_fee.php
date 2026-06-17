<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Payment - <?= APPNAME ?></title>
    <?php $paymentCssVersion = file_exists(APPROOT . '/../public/css/app.css') ? filemtime(APPROOT . '/../public/css/app.css') : time(); ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= URLROOT ?>/public/css/app.css?v=<?= $paymentCssVersion ?>">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root{--surface:#fff;--content:#FBFBF9;--soft:#faf5ef;--hover:#eddecc;--border:#ead8c7;--border-light:#eddecc;--primary:#6d4c5b;--primary-hover:#7b5c69;--text:#111827;--muted:#b79c8b;--body:#7b5c69;--danger:#b94b4b;--danger-soft:#f9dede}
        *{box-sizing:border-box}
        body{min-height:100vh;margin:0;background:#f3f6fb;color:var(--text);font-family:Poppins,system-ui,-apple-system,sans-serif}
        .payment-shell{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:32px 18px;background:#f3f6fb}
        .payment-layout{width:min(100%,1040px);display:grid;grid-template-columns:minmax(0,1fr) 320px;gap:18px;align-items:start}
        .payment-surface,.payment-side{border:1px solid var(--border);border-radius:.75rem;background:var(--surface);box-shadow:0 1px 2px rgba(15,23,42,.05);overflow:hidden}
        .payment-header{border-bottom:1px solid var(--border-light);background:var(--soft);padding:22px 24px}
        .payment-eyebrow,.payment-kicker,.payment-label,.summary-row span{font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--muted)}
        .payment-eyebrow{display:inline-flex;align-items:center;gap:7px;border:1px solid var(--border);border-radius:.75rem;background:var(--hover);padding:6px 10px;color:var(--primary)}
        .payment-title{margin:14px 0 8px;color:var(--text);font-size:26px;font-weight:700;line-height:1.18;letter-spacing:-.02em}
        .payment-intro{max-width:700px;margin:0;color:var(--body);font-size:13px;line-height:1.7}
        .payment-content{padding:20px 24px 24px}
        .payment-alert{margin-bottom:16px;border:1px solid rgba(185,75,75,.22);border-radius:.75rem;background:var(--danger-soft);padding:12px 14px;color:var(--danger);font-size:13px;font-weight:600}
        .payment-form{display:grid;gap:14px}
        .payment-panel{border:1px solid var(--border);border-radius:.75rem;background:var(--surface);padding:16px}
        .payment-summary{display:grid;grid-template-columns:minmax(180px,.75fr) minmax(0,1.25fr);gap:18px;align-items:start;background:var(--soft)}
        .payment-amount{margin:5px 0 0;color:var(--text);font-size:30px;font-weight:700;line-height:1;letter-spacing:-.02em}
        .payment-amount span{color:var(--muted);font-size:11px;font-weight:800;letter-spacing:.12em}
        .summary-list{display:grid;gap:10px}
        .summary-row{display:flex;justify-content:space-between;gap:18px;border-bottom:1px solid var(--border-light);padding-bottom:10px}
        .summary-row:last-child{border-bottom:0;padding-bottom:0}
        .summary-row strong{max-width:58%;text-align:right;color:var(--text);font-size:13px;font-weight:700;line-height:1.45}
        .payment-label{display:block;margin:0 0 8px}
        .method-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;margin-bottom:16px}
        .method-option{position:relative;cursor:pointer}
        .method-option input{position:absolute;opacity:0;pointer-events:none}
        .method-option span{display:flex;min-height:42px;align-items:center;justify-content:center;border:1px solid var(--border);border-radius:.75rem;background:var(--surface);padding:9px 12px;color:var(--body);font-size:12px;font-weight:800;transition:border-color .12s,box-shadow .12s,background .12s,color .12s}
        .method-option span:hover{background:var(--soft);color:var(--primary)}
        .method-option input:checked + span{border-color:var(--primary);background:var(--primary);color:#fff;box-shadow:0 0 0 3px rgba(232,215,202,.7)}
        .payment-form input[type="text"],.payment-form textarea{width:100%;border:1px solid var(--border);border-radius:.75rem;background:var(--soft);padding:12px 13px;color:var(--text);font-family:inherit;font-size:13px;outline:none;transition:border-color .12s,box-shadow .12s,background .12s}
        .payment-form input[type="text"]::placeholder,.payment-form textarea::placeholder{color:var(--muted)}
        .payment-form input[type="text"]:focus,.payment-form textarea:focus{border-color:#c8b1a1;background:#fff;box-shadow:0 0 0 3px #e8d7ca}
        .payment-form textarea{margin-bottom:12px;resize:vertical}
        .payment-slip-upload{display:flex;min-height:76px;align-items:center;gap:12px;border:1px dashed var(--border);border-radius:.75rem;background:var(--soft);padding:14px;cursor:pointer;transition:all .12s}
        .payment-slip-upload:hover{border-color:var(--primary);background:#fff}
        .payment-slip-upload strong{display:block;color:var(--text);font-size:13px;font-weight:800;line-height:1.35}
        .payment-slip-upload small{display:block;margin-top:3px;color:var(--muted);font-size:11px;line-height:1.35}
        .payment-slip-icon{display:flex;width:44px;height:44px;flex:0 0 44px;align-items:center;justify-content:center;border-radius:.75rem;background:var(--hover);color:var(--primary);font-size:10px;font-weight:900;letter-spacing:.08em;text-transform:uppercase}
        .payment-form input[type="file"]{position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;clip-path:inset(50%)}
        .method-info,.gateway-panel{display:grid;grid-template-columns:auto minmax(0,1fr);gap:14px;align-items:start;margin-bottom:14px;background:var(--soft)}
        .method-info[hidden]{display:none}
        .method-info-icon{display:flex;width:44px;height:44px;align-items:center;justify-content:center;border-radius:.75rem;background:var(--hover);color:var(--primary);font-size:11px;font-weight:900;letter-spacing:.04em}
        .method-info strong,.gateway-copy h2{display:block;margin:0;color:var(--text);font-size:14px;font-weight:800;line-height:1.35}
        .method-info p,.gateway-copy p{margin:4px 0 0;color:var(--body);font-size:12px;line-height:1.55}
        .bank-detail-list{display:grid;gap:8px;margin:10px 0 0}
        .bank-detail-list div{display:flex;justify-content:space-between;gap:14px;border-top:1px solid var(--border-light);padding-top:8px}
        .bank-detail-list dt{color:var(--muted);font-size:10px;font-weight:800;letter-spacing:.08em;text-transform:uppercase}
        .bank-detail-list dd{margin:0;max-width:58%;text-align:right;color:var(--text);font-size:12px;font-weight:800;word-break:break-word}
        .gateway-panel{grid-template-columns:minmax(0,1fr) 220px;align-items:center}
        .gateway-link{display:block;margin-top:10px;color:var(--primary);font-size:12px;font-weight:700;line-height:1.45;overflow-wrap:anywhere;text-decoration:none}
        .qr-card{display:grid;justify-items:center;gap:8px;border:1px solid var(--border);border-radius:.75rem;background:#fff;padding:12px}
        .qr-card img{display:block;width:100%;max-width:196px;height:auto}
        .qr-card span{color:var(--muted);font-size:10px;font-weight:800;letter-spacing:.08em;text-transform:uppercase}
        .payment-note{margin:0;color:var(--body);font-size:12px;line-height:1.55}
        .payment-actions{display:flex;align-items:center;justify-content:space-between;gap:12px;padding-top:2px}
        .payment-back,.payment-submit{display:inline-flex;height:38px;align-items:center;justify-content:center;border-radius:.75rem;padding:0 14px;font-family:inherit;font-size:12px;font-weight:800;text-decoration:none;transition:all .12s}
        .payment-back{border:1px solid var(--border);background:#fff;color:var(--body)}
        .payment-back:hover{background:var(--soft);color:var(--primary)}
        .payment-submit{border:1px solid var(--primary);background:var(--primary);color:#fff;cursor:pointer;box-shadow:0 10px 20px rgba(109,76,91,.12)}
        .payment-submit:hover{border-color:var(--primary-hover);background:var(--primary-hover)}
        .payment-side{padding:16px}
        .side-heading{display:flex;align-items:center;gap:8px;margin:0 0 12px;color:var(--text);font-size:13px;font-weight:700}
        .side-icon{display:flex;width:28px;height:28px;align-items:center;justify-content:center;border-radius:.75rem;background:var(--hover);color:var(--primary)}
        .side-list{display:grid;gap:10px}
        .side-item{border:1px solid var(--border-light);border-radius:.75rem;background:var(--soft);padding:12px}
        .side-item span{display:block;margin-bottom:4px;color:var(--muted);font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase}
        .side-item p{margin:0;color:var(--text);font-size:12px;font-weight:700;line-height:1.45}
        @media(max-width:920px){.payment-layout{grid-template-columns:1fr}.payment-side{order:-1}}
        @media(max-width:680px){.payment-shell{padding:18px 14px}.payment-header,.payment-content{padding:18px}.payment-summary,.method-grid,.gateway-panel{grid-template-columns:1fr}.summary-row strong{max-width:62%}.payment-actions{align-items:stretch;flex-direction:column-reverse}.payment-actions a,.payment-actions button{width:100%}}
    </style>
</head>
<body>
    <main class="payment-shell">
        <div class="payment-layout">
            <section class="payment-surface">
                <div class="payment-header">
                    <p class="payment-eyebrow">
                        <i data-lucide="credit-card" class="h-3.5 w-3.5"></i>
                        <?= htmlspecialchars($paymentContext['eyebrow'] ?? 'Payment', ENT_QUOTES, 'UTF-8') ?>
                    </p>
                    <h1 class="payment-title"><?= htmlspecialchars($paymentContext['title'] ?? 'Payment', ENT_QUOTES, 'UTF-8') ?></h1>
                    <p class="payment-intro"><?= htmlspecialchars($paymentContext['intro'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                </div>

                <div class="payment-content">
                    <?php if (!empty($message)): ?>
                        <div class="payment-alert"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>

                    <?php require APPROOT . '/views/payments/_form.php'; ?>
                </div>
            </section>

            <aside class="payment-side">
                <h2 class="side-heading">
                    <span class="side-icon"><i data-lucide="list-checks" class="h-4 w-4"></i></span>
                    How it works
                </h2>
                <div class="side-list">
                    <div class="side-item">
                        <span>Step 1</span>
                        <p>Choose your bank or payment app (KBZ Pay, Wave Money, AYA Pay, etc.).</p>
                    </div>
                    <div class="side-item">
                        <span>Step 2</span>
                        <p>Transfer 50,000 MMK to the account shown, then fill in your transfer details.</p>
                    </div>
                    <div class="side-item">
                        <span>Step 3</span>
                        <p>Our admin will verify your payment and unlock your supplier dashboard.</p>
                    </div>
                </div>
            </aside>
        </div>
    </main>
    <script>lucide.createIcons();</script>
</body>
</html>
