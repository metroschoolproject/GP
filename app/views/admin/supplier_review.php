<?php
$supplierName = htmlspecialchars($supplier['shop_name'] ?? 'Supplier', ENT_QUOTES, 'UTF-8');
$status = strtolower($supplier['status'] ?? 'pending');
$dashboardTitle = 'Suppliers';
$dashboardCrumb = 'Review';
$dashboardContentClass = 'supplier-review-content px-6 py-6';
$dashboardContent = function () use ($supplier, $supplierName, $status, $message) {
    $rows = [
        'Owner' => $supplier['owner_name'] ?? '-',
        'Email' => $supplier['owner_email'] ?? '-',
        'Phone' => $supplier['phone'] ?? '-',
        'Address' => $supplier['address'] ?? '-',
        'Categories' => $supplier['category_names'] ?? '-',
        'Agreement accepted' => !empty($supplier['agreement_accepted']) ? 'Yes' : 'No',
        'Payment status' => $supplier['payment_status'] ?? '-',
    ];
?>
    <style>
        .supplier-review-shell {
            color: #1c1917;
            font-size: 13px;
            max-width: 1600px;
        }

        .supplier-review-content {
            min-height: 100%;
            background: #FBFBF9;
        }

        .admin-section-heading,
        .admin-value {
            color: #1c1917;
        }

        .admin-muted {
            color: #a8a29e;
        }

        .admin-body-copy {
            color: #57534e;
        }

        .admin-section-icon {
            color: #673049;
        }

        .admin-card {
            background: #fff;
            border: 1px solid #e7e5e4;
            border-radius: 1.2rem;
            box-shadow: 0 1px 2px rgba(28, 25, 23, 0.05);
            transition: box-shadow 0.18s ease, transform 0.18s ease;
        }

        .admin-card:hover {
            box-shadow: 0 4px 12px rgba(28, 25, 23, 0.08);
        }

        .admin-card-header {
            border-bottom: 1px solid #e7e5e4;
            padding: 1rem 1.25rem;
        }

        .admin-stat-label {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #a8a29e;
        }

        .admin-queue-item {
            background: #f5f5f3;
            border: 1px solid transparent;
            border-radius: 0.75rem;
            padding: 0.75rem 0.9rem;
            transition: all 0.12s ease;
        }

        .admin-queue-item:hover {
            background: #eeece9;
            border-color: #e7e5e4;
        }

        .admin-icon-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: #673049;
            font-size: 13px;
            font-weight: 700;
        }

        .admin-icon-link .icon-box,
        .supplier-review-meta .icon-box {
            display: inline-flex;
            height: 2rem;
            width: 2rem;
            flex-shrink: 0;
            align-items: center;
            justify-content: center;
            border-radius: 0.5rem;
            background: #fde8ef;
            color: #673049;
        }

        .supplier-review-meta {
            display: grid;
            gap: 0.75rem;
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .supplier-review-grid {
            display: grid;
            gap: 1.25rem;
            grid-template-columns: 1.15fr 0.85fr;
        }

        .supplier-review-meta-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border: 1px solid #e7e5e4;
            border-radius: 1rem;
            background: #fff;
            padding: 0.85rem;
            box-shadow: 0 1px 2px rgba(28, 25, 23, 0.05);
        }

        .admin-divider-row:last-child {
            border-bottom: 0;
            padding-bottom: 0;
        }

        .admin-detail-row {
            border-bottom: 1px solid #e7e5e4;
            padding-bottom: 0.75rem;
        }

        .admin-detail-value {
            max-width: 60%;
            text-align: right;
        }

        .admin-message {
            border: 1px solid #a7f3d0;
            border-radius: 1.2rem;
            background: #ecfdf5;
            color: #047857;
        }

        .admin-badge {
            display: inline-flex;
            align-items: center;
            border-radius: 9999px;
            padding: 0.15rem 0.55rem;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .admin-badge-pending { background: #fef3c7; color: #92400e; }
        .admin-badge-approved { background: #d1fae5; color: #065f46; }
        .admin-badge-rejected { background: #fee2e2; color: #991b1b; }
        .admin-badge-muted { background: #f3f4f6; color: #57534e; border: 1px solid #d1d5db; }

        .admin-action-primary,
        .admin-action-danger,
        .admin-action-ghost {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border-radius: 0.75rem;
            padding: 0.65rem 1rem;
            font-size: 13px;
            font-weight: 700;
            box-shadow: 0 1px 2px rgba(28, 25, 23, 0.05);
            transition: background 0.12s ease, border-color 0.12s ease, transform 0.12s ease;
        }

        .admin-action-primary {
            background: #673049;
            color: #fff;
        }

        .admin-action-primary:hover {
            background: #9b1c4a;
        }

        .admin-action-danger {
            background: #991b1b;
            color: #fff;
        }

        .admin-action-danger:hover {
            background: #7f1d1d;
        }

        .admin-action-ghost {
            border: 1px solid #e7e5e4;
            background: #fff;
            color: #673049;
        }

        .admin-action-ghost:hover {
            background: #fde8ef;
            border-color: #f9c0d2;
        }

        @media (max-width: 900px) {
            .supplier-review-meta,
            .supplier-review-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
    <div class="supplier-review-shell mx-auto">
        <div class="mb-5 flex flex-wrap items-center justify-between gap-4">
            <div>
                <a href="<?= URLROOT ?>/admin/suppliers" class="admin-action-ghost">
                    <i data-lucide="arrow-left" class="h-4 w-4"></i>
                    <span>Suppliers</span>
                </a>
                <p class="admin-stat-label mt-5">Supplier Application</p>
                <h1 class="admin-section-heading mt-1 text-2xl font-bold tracking-tight"><?= $supplierName ?></h1>
                <p class="admin-muted mt-1 text-sm">Review business profile, categories, social verification, and license document.</p>
            </div>
            <?php $statusClass = 'admin-badge-' . ($status ?: 'muted'); ?>
            <span class="admin-badge <?= htmlspecialchars($statusClass, ENT_QUOTES, 'UTF-8') ?>">
                <?= htmlspecialchars($supplier['status'] ?? 'pending', ENT_QUOTES, 'UTF-8') ?>
            </span>
        </div>

        <?php if (!empty($message)): ?>
            <div class="admin-message mb-5 px-4 py-3 text-sm font-semibold">
                <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <div class="supplier-review-meta mb-5">
            <div class="supplier-review-meta-item">
                <span class="icon-box"><i data-lucide="user-round" class="h-4 w-4"></i></span>
                <div class="min-w-0">
                    <p class="admin-stat-label">Owner</p>
                    <p class="admin-value truncate font-bold"><?= htmlspecialchars($supplier['owner_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            </div>
            <div class="supplier-review-meta-item">
                <span class="icon-box"><i data-lucide="badge-dollar-sign" class="h-4 w-4"></i></span>
                <div class="min-w-0">
                    <p class="admin-stat-label">Categories</p>
                    <p class="admin-value truncate font-bold"><?= htmlspecialchars($supplier['category_names'] ?? '-', ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            </div>
            <div class="supplier-review-meta-item">
                <span class="icon-box"><i data-lucide="credit-card" class="h-4 w-4"></i></span>
                <div class="min-w-0">
                    <p class="admin-stat-label">Payment</p>
                    <p class="admin-value truncate font-bold"><?= htmlspecialchars($supplier['payment_status'] ?? '-', ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            </div>
        </div>

        <div class="supplier-review-grid">
            <section class="admin-card">
                <div class="admin-card-header">
                    <div class="flex items-center gap-2">
                        <i data-lucide="store" class="admin-section-icon h-4 w-4"></i>
                        <h2 class="admin-section-heading text-sm font-bold">Business Details</h2>
                    </div>
                    <p class="admin-muted mt-1 text-xs">Core business information submitted by the supplier.</p>
                </div>
                <div class="grid gap-4 p-5 text-sm">
                    <?php foreach ($rows as $label => $value): ?>
                        <div class="admin-divider-row admin-detail-row flex justify-between gap-4">
                            <span class="admin-stat-label"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span>
                            <strong class="admin-value admin-detail-value"><?= htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8') ?></strong>
                        </div>
                    <?php endforeach; ?>
                    <div class="admin-queue-item">
                        <p class="admin-stat-label">Business description</p>
                        <p class="admin-body-copy mt-2 leading-6"><?= htmlspecialchars($supplier['description'] ?? '-', ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                </div>
            </section>

            <aside class="grid content-start gap-5">
                <section class="admin-card">
                    <div class="admin-card-header">
                        <div class="flex items-center gap-2">
                            <i data-lucide="shield-check" class="admin-section-icon h-4 w-4"></i>
                            <h2 class="admin-section-heading text-sm font-bold">Verification</h2>
                        </div>
                        <p class="admin-muted mt-1 text-xs">Open each item before deciding.</p>
                    </div>
                    <div class="grid gap-3 p-5">
                        <?php if (!empty($supplier['verify_url'])): ?>
                            <a href="<?= htmlspecialchars($supplier['verify_url'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" class="admin-queue-item admin-icon-link">
                                <span class="icon-box"><i data-lucide="external-link" class="h-4 w-4"></i></span>
                                <span>Open website / social link</span>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($supplier['business_license_url'])): ?>
                            <a href="<?= htmlspecialchars($supplier['business_license_url'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" class="admin-queue-item admin-icon-link">
                                <span class="icon-box"><i data-lucide="file-badge" class="h-4 w-4"></i></span>
                                <span>Open business license</span>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($supplier['cover_url'])): ?>
                            <a href="<?= htmlspecialchars($supplier['cover_url'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" class="admin-queue-item admin-icon-link">
                                <span class="icon-box"><i data-lucide="image" class="h-4 w-4"></i></span>
                                <span>Open cover image</span>
                            </a>
                        <?php endif; ?>
                        <?php if (empty($supplier['verify_url']) && empty($supplier['business_license_url']) && empty($supplier['cover_url'])): ?>
                            <p class="rounded-xl bg-slate-50 px-4 py-6 text-center text-sm text-slate-400">No verification files found.</p>
                        <?php endif; ?>
                    </div>
                </section>

                <section class="admin-card">
                    <div class="admin-card-header">
                        <div class="flex items-center gap-2">
                            <i data-lucide="list-checks" class="admin-section-icon h-4 w-4"></i>
                            <h2 class="admin-section-heading text-sm font-bold">Decision</h2>
                        </div>
                        <p class="admin-muted mt-1 text-xs">Approved suppliers can enter the locked dashboard and submit payment.</p>
                    </div>
                    <div class="flex flex-wrap gap-3 p-5">
                        <?php if ($status === 'pending'): ?>
                            <form method="POST" action="<?= URLROOT ?>/admin/approveSupplier/<?= (int)$supplier['supplier_id'] ?>">
                                <button class="admin-action-primary" type="submit">
                                    <i data-lucide="check" class="h-4 w-4"></i>
                                    <span>Approve supplier</span>
                                </button>
                            </form>
                            <form method="POST" action="<?= URLROOT ?>/admin/rejectSupplier/<?= (int)$supplier['supplier_id'] ?>">
                                <button class="admin-action-danger" type="submit">
                                    <i data-lucide="x" class="h-4 w-4"></i>
                                    <span>Reject</span>
                                </button>
                            </form>
                        <?php else: ?>
                            <p class="rounded-xl bg-slate-50 px-4 py-4 text-sm text-slate-500">This supplier has already been reviewed.</p>
                        <?php endif; ?>
                    </div>
                </section>
            </aside>
        </div>
    </div>
<?php
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php require_once APPROOT . '/views/dashboardLayout/head.php' ?>
</head>
<body class="grid h-screen gap-0 bg-app-page" style="grid-template-columns: 280px 1fr;">
    <?php require_once APPROOT . '/views/dashboardLayout/sidebar.php' ?>
</body>
</html>
