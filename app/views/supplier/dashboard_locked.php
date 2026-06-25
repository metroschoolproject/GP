<?php
$supplier = $supplier ?? [];
$payment = $payment ?? [];

$supplierName = htmlspecialchars($supplier['shop_name'] ?? 'Your supplier account', ENT_QUOTES, 'UTF-8');
$serviceName = htmlspecialchars($supplier['service_name'] ?? 'Service information', ENT_QUOTES, 'UTF-8');
$paymentMethodRaw = (string)($payment['method'] ?? '');
$paymentMethod = htmlspecialchars($paymentMethodRaw !== '' ? $paymentMethodRaw : '-', ENT_QUOTES, 'UTF-8');
$paymentRefRaw = trim((string)($payment['transaction_ref'] ?? ''));
$paymentRef = htmlspecialchars($paymentRefRaw !== '' ? $paymentRefRaw : '-', ENT_QUOTES, 'UTF-8');
$hasPaymentSlip = preg_match('/\.(jpe?g|png|webp)$/i', $paymentRefRaw) === 1;
$paymentDate = !empty($payment['created_at']) ? htmlspecialchars(date('M j, Y', strtotime($payment['created_at'])), ENT_QUOTES, 'UTF-8') : '-';
$isPaymentPending = ($lockState ?? '') === 'payment_pending';
$isPaymentRequired = ($lockState ?? '') === 'payment_required';
$isProfileBlocked = ($lockState ?? '') === 'profile_not_approved';
$isKbzPending = $isPaymentPending && $paymentMethodRaw === 'KBZ Pay';

$dashboardTitle = 'Supplier';
$dashboardCrumb = 'Dashboard Locked';
$dashboardBreadcrumbs = [
    ['label' => 'Dashboard', 'url' => URLROOT . '/supplier/dashboard'],
    ['label' => 'Locked', 'url' => null],
];
$dashboardSearchPlaceholder = 'Dashboard is locked';
$dashboardContentClass = 'locked-dashboard-content bg-app-content px-6 py-6';
$dashboardContent = function () use ($supplierName, $serviceName, $paymentMethod, $paymentRef, $paymentRefRaw, $hasPaymentSlip, $paymentDate, $isPaymentPending, $isPaymentRequired, $isProfileBlocked, $isKbzPending) {
    $lockBadge = $isKbzPending ? 'KBZ Pay pending' : ($isPaymentPending ? 'Payment under review' : ($isProfileBlocked ? 'Profile review' : 'Dashboard locked'));
    $lockTitle = $isPaymentPending
        ? ($isKbzPending ? 'Complete your KBZ Pay payment' : 'Payment verification is in progress')
        : ($isProfileBlocked ? 'Your profile needs approval first' : 'Complete payment to unlock your dashboard');
    $lockCopy = $isProfileBlocked
        ? 'Your supplier dashboard will open after the admin team approves your profile and verifies your access.'
        : ($isKbzPending
            ? 'Your supplier profile has been approved. Scan the KBZ QR and enter the demo PIN to unlock the dashboard automatically.'
            : 'Your supplier profile has been approved. Full dashboard tools stay locked until the membership payment is submitted and verified by admin.');
?>
    <style>
        .locked-dashboard-content{min-height:100%;color:#6d4c5b}
        .locked-shell{max-width:1180px;margin:0 auto}
        .locked-hero{display:grid;grid-template-columns:minmax(0,1.35fr) minmax(300px,.65fr);gap:18px;align-items:start}
        .locked-panel,.locked-card,.locked-side{border:1px solid #ead8c7;border-radius:.75rem;background:#FFFFFF;box-shadow:0 1px 2px rgba(15,23,42,.05)}
        .locked-panel{overflow:hidden}
        .locked-header{padding:22px 24px;border-bottom:1px solid #eddecc;background:#FFFFFF}
        .locked-badge{display:inline-flex;align-items:center;gap:7px;border:1px solid #ead8c7;border-radius:.75rem;background:#eddecc;padding:6px 10px;color:#6d4c5b;font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase}
        .locked-title{margin:14px 0 8px;color:#6d4c5b;font-size:26px;font-weight:700;line-height:1.18;letter-spacing:-.02em}
        .locked-copy{margin:0;max-width:680px;color:#7b5c69;font-size:13px;line-height:1.7}
        .locked-body{padding:20px 24px 24px}
        .status-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
        .status-card{border:1px solid #ead8c7;border-radius:.75rem;background:#FFFFFF;padding:14px}
        .status-card span{display:block;margin-bottom:5px;color:#b79c8b;font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase}
        .status-card strong{display:block;color:#6d4c5b;font-size:13px;font-weight:700;line-height:1.45;word-break:break-word}
        .locked-actions{display:flex;flex-wrap:wrap;align-items:center;gap:10px;margin-top:18px;padding-top:18px;border-top:1px solid #eddecc}
        .primary-action,.secondary-action{display:inline-flex;align-items:center;justify-content:center;gap:8px;height:38px;border-radius:.75rem;padding:0 14px;font-size:12px;font-weight:800;text-decoration:none;transition:all .12s}
        .primary-action{border:1px solid #6d4c5b;background:#6d4c5b;color:#FFFFFF;box-shadow:0 10px 20px rgba(109,76,91,.12)}
        .primary-action:hover{background:#7b5c69;border-color:#7b5c69}
        .secondary-action{border:1px solid #ead8c7;background:#FFFFFF;color:#7b5c69}
        .secondary-action:hover{background:#FFFFFF;color:#6d4c5b}
        .locked-side{padding:16px}
        .side-title{display:flex;align-items:center;gap:8px;margin:0 0 12px;color:#6d4c5b;font-size:13px;font-weight:700}
        .side-title span{display:flex;width:28px;height:28px;align-items:center;justify-content:center;border-radius:.75rem;background:#eddecc;color:#6d4c5b}
        .step-list{display:grid;gap:10px}
        .step-item{display:flex;gap:10px;border:1px solid #eddecc;border-radius:.75rem;background:#FFFFFF;padding:12px}
        .step-dot{display:flex;width:24px;height:24px;flex:0 0 24px;align-items:center;justify-content:center;border-radius:.75rem;background:#FFFFFF;color:#6d4c5b;font-size:11px;font-weight:800}
        .step-item p{margin:0;color:#6d4c5b;font-size:12px;font-weight:700}
        .step-item small{display:block;margin-top:3px;color:#b79c8b;font-size:11px;line-height:1.45}
        .locked-preview{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin-bottom:18px}
        .preview-stat{min-height:86px;border:1px solid #ead8c7;border-radius:.75rem;background:#FFFFFF;padding:14px}
        .preview-line{height:8px;border-radius:99px;background:#eddecc}
        .preview-line.short{width:44%;margin-top:14px}
        .preview-line.long{width:72%;margin-top:10px;background:#FFFFFF}
        @media(max-width:1024px){.locked-hero{grid-template-columns:1fr}.locked-preview{grid-template-columns:repeat(2,minmax(0,1fr))}}
        @media(max-width:640px){.locked-dashboard-content{padding:18px 14px}.locked-header,.locked-body{padding:18px}.status-grid,.locked-preview{grid-template-columns:1fr}.locked-actions a{width:100%}}
    </style>

    <div class="locked-shell">
        <div class="locked-preview" aria-hidden="true">
            <div class="preview-stat">
                <div class="preview-line"></div>
                <div class="preview-line short"></div>
                <div class="preview-line long"></div>
            </div>
            <div class="preview-stat">
                <div class="preview-line"></div>
                <div class="preview-line short"></div>
                <div class="preview-line long"></div>
            </div>
            <div class="preview-stat">
                <div class="preview-line"></div>
                <div class="preview-line short"></div>
                <div class="preview-line long"></div>
            </div>
        </div>

        <div class="locked-hero">
            <section class="locked-panel">
                <div class="locked-header">
                    <span class="locked-badge">
                        <i data-lucide="lock-keyhole" class="h-3.5 w-3.5"></i>
                        <?= $lockBadge ?>
                    </span>
                    <h1 class="locked-title"><?= $lockTitle ?></h1>
                    <p class="locked-copy"><?= $lockCopy ?></p>
                </div>

                <div class="locked-body">
                    <div class="status-grid">
                        <div class="status-card">
                            <span>Business</span>
                            <strong><?= $supplierName ?></strong>
                        </div>
                        <div class="status-card">
                            <span>Service</span>
                            <strong><?= $serviceName ?></strong>
                        </div>
                        <div class="status-card">
                            <span>Profile approval</span>
                            <strong><?= $isProfileBlocked ? 'Needs approval' : 'Approved' ?></strong>
                        </div>
                        <div class="status-card">
                            <span>Payment status</span>
                            <strong><?= $isKbzPending ? 'Waiting for KBZ Pay' : ($isPaymentPending ? 'Submitted for review' : ($isPaymentRequired ? 'Required' : 'Not available')) ?></strong>
                        </div>
                        <?php if ($isPaymentPending): ?>
                            <div class="status-card">
                                <span>Method</span>
                                <strong><?= $paymentMethod ?></strong>
                            </div>
                            <div class="status-card">
                                <span><?= $hasPaymentSlip ? 'Payment slip' : 'Reference' ?></span>
                                <strong>
                                    <?php if ($hasPaymentSlip): ?>
                                        <a href="<?= htmlspecialchars($paymentRefRaw, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" style="color:#6d4c5b;text-decoration:none;">View uploaded slip</a>
                                    <?php else: ?>
                                        <?= $paymentRef ?>
                                    <?php endif; ?>
                                </strong>
                            </div>
                            <div class="status-card">
                                <span>Submitted</span>
                                <strong><?= $paymentDate ?></strong>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="locked-actions">
                        <?php if ($isPaymentRequired || $isKbzPending): ?>
                            <a class="primary-action" href="<?= URLROOT ?>/payments/supplierFee">
                                <i data-lucide="credit-card" class="h-4 w-4"></i>
                                <?= $isKbzPending ? 'Continue KBZ Pay' : 'Pay membership fee' ?>
                            </a>
                        <?php endif; ?>
                        <a class="secondary-action" href="<?= URLROOT ?>/main/home">
                            <i data-lucide="home" class="h-4 w-4"></i>
                            Back home
                        </a>
                        <a class="secondary-action" href="<?= URLROOT ?>/users/logout">
                            <i data-lucide="log-out" class="h-4 w-4"></i>
                            Sign out
                        </a>
                    </div>
                </div>
            </section>

            <aside class="locked-side">
                <h2 class="side-title">
                    <span><i data-lucide="list-checks" class="h-4 w-4"></i></span>
                    Access status
                </h2>
                <div class="step-list">
                    <div class="step-item">
                        <span class="step-dot">1</span>
                        <div>
                            <p>Supplier profile</p>
                            <small><?= $isProfileBlocked ? 'Waiting for admin approval.' : 'Approved by admin.' ?></small>
                        </div>
                    </div>
                    <div class="step-item">
                        <span class="step-dot">2</span>
                        <div>
                            <p>Membership payment</p>
                            <small><?= $isPaymentPending ? 'Submitted and waiting for verification.' : ($isPaymentRequired ? 'Payment is required before access opens.' : 'Will be checked after profile approval.') ?></small>
                        </div>
                    </div>
                    <div class="step-item">
                        <span class="step-dot">3</span>
                        <div>
                            <p>Dashboard access</p>
                            <small>Tools unlock automatically after admin verification.</small>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>
<?php
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php require_once APPROOT . '/views/dashboardLayout/head.php'; ?>
</head>
<body class="grid h-screen gap-0 bg-app-page" style="grid-template-columns: 280px 1fr;">
    <?php require APPROOT . '/views/dashboardLayout/suppliersidebar.php'; ?>
</body>
</html>
