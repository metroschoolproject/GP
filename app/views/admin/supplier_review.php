<?php
$supplierName = htmlspecialchars($supplier['shop_name'] ?? 'Supplier', ENT_QUOTES, 'UTF-8');
$status = strtolower($supplier['status'] ?? 'pending');
$dashboardTitle = 'Suppliers';
$dashboardCrumb = 'Review';
$dashboardContentClass = 'supplier-review-content';
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
            --review-bg: #FBFBF9;
            --review-surface: #ffffff;
            --review-soft: #faf5ef;
            --review-soft-hover: #eddecc;
            --review-border: #ead8c7;
            --review-border-light: #eddecc;
            --review-primary: #6d4c5b;
            --review-primary-hover: #7b5c69;
            --review-primary-soft: #eddecc;
            --review-text: #111827;
            --review-muted: #b79c8b;
            --review-body: #7b5c69;
            --review-success-bg: #d1fae5;
            --review-success-text: #065f46;
            --review-warn-bg: #fef3c7;
            --review-warn-text: #92400e;
            --review-danger-bg: #fee2e2;
            --review-danger-text: #991b1b;
            --review-neutral-bg: #f3f4f6;
            color: var(--review-text);
            font-size: 13px;
            max-width: 1600px;
            margin: 0 auto;
        }

        .supplier-review-content {
            min-height: 100%;
            background: var(--review-bg);
            padding: 28px 32px;
        }

        .review-page-header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 22px;
        }

        .review-eyebrow,
        .review-label {
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--review-muted);
        }

        .review-title {
            margin: 0;
            color: var(--review-text);
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -0.3px;
            line-height: 1.2;
        }

        .review-subtitle {
            margin-top: 4px;
            max-width: 46rem;
            color: var(--review-body);
            font-size: 13px;
            line-height: 1.5;
        }

        .review-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(300px, 380px);
            gap: 20px;
            align-items: start;
        }

        .review-panel {
            background: var(--review-surface);
            border: 1px solid var(--review-border);
            border-radius: 0.75rem;
            box-shadow: 0 1px 2px rgba(28, 25, 23, 0.04);
            overflow: hidden;
        }

        .review-primary-panel {
            min-height: 0;
        }

        .review-panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            border-bottom: 1px solid var(--review-border-light);
            padding: 14px 20px;
        }

        .review-panel-title-row {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .review-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            flex: 0 0 auto;
            border-radius: 0.75rem;
            background: var(--review-primary-soft);
            color: var(--review-primary);
        }

        .review-panel-title {
            color: var(--review-text);
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0;
        }

        .review-panel-note {
            margin-top: 2px;
            color: var(--review-muted);
            font-size: 11px;
            line-height: 1.5;
        }

        .review-section {
            padding: 20px;
        }

        .review-section + .review-section {
            border-top: 1px solid var(--review-border-light);
        }

        .review-summary-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
        }

        .review-summary-item {
            border: 1px solid var(--review-border);
            border-radius: 0.75rem;
            background: var(--review-surface);
            padding: 14px 16px;
        }

        .review-summary-value {
            margin-top: 4px;
            color: var(--review-text);
            font-size: 16px;
            font-weight: 700;
            line-height: 1.35;
        }

        .review-detail-list {
            display: grid;
            gap: 0;
        }

        .review-detail-row {
            display: grid;
            grid-template-columns: minmax(9rem, 0.34fr) minmax(0, 1fr);
            gap: 1rem;
            padding: 13px 0;
            border-bottom: 1px solid var(--review-border-light);
            align-items: start;
        }

        .review-detail-row:first-child {
            padding-top: 0;
        }

        .review-detail-row:last-child {
            border-bottom: 0;
            padding-bottom: 0;
        }

        .review-value {
            color: var(--review-text);
            font-size: 13px;
            font-weight: 600;
            line-height: 1.55;
            overflow-wrap: anywhere;
        }

        .review-description {
            border: 1px solid var(--review-border-light);
            border-radius: 0.75rem;
            background: var(--review-soft);
            padding: 14px 16px;
        }

        .review-description p {
            margin-top: 0.55rem;
            color: var(--review-body);
            line-height: 1.75;
        }

        .admin-message {
            margin-bottom: 1.25rem;
            border: 1px solid var(--review-success-bg);
            border-radius: 0.75rem;
            background: var(--review-success-bg);
            color: var(--review-success-text);
            padding: 12px 14px;
            font-size: 13px;
            font-weight: 700;
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

        .admin-badge-pending { background: var(--review-warn-bg); color: var(--review-warn-text); }
        .admin-badge-approved { background: var(--review-success-bg); color: var(--review-success-text); }
        .admin-badge-rejected { background: var(--review-danger-bg); color: var(--review-danger-text); }
        .admin-badge-muted { background: var(--review-neutral-bg); color: var(--review-body); border: 1px solid var(--review-border); }

        .review-rail {
            display: grid;
            gap: 1rem;
            position: sticky;
            top: 20px;
        }

        .review-link-list,
        .review-action-stack {
            display: grid;
            gap: 10px;
            padding: 14px;
        }

        .review-file-link {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            min-height: 42px;
            border: 1px solid var(--review-border);
            border-radius: 0.75rem;
            background: var(--review-surface);
            padding: 9px 10px;
            color: var(--review-primary);
            font-size: 13px;
            font-weight: 800;
            text-decoration: none;
            transition: background 0.12s ease, border-color 0.12s ease, transform 0.12s ease;
        }

        .review-file-link:hover {
            border-color: var(--review-border);
            background: var(--review-soft-hover);
            transform: translateY(-1px);
        }

        .review-empty {
            border: 1px dashed var(--review-border);
            border-radius: 0.75rem;
            background: var(--review-soft);
            padding: 1.2rem;
            color: var(--review-muted);
            text-align: center;
            line-height: 1.6;
        }

        .admin-action-primary,
        .admin-action-danger {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            min-height: 34px;
            border-radius: 0.75rem;
            padding: 0 12px;
            font-size: 12px;
            font-weight: 800;
            box-shadow: 0 1px 2px rgba(28, 25, 23, 0.05);
            transition: background 0.12s ease, border-color 0.12s ease, transform 0.12s ease;
        }

        .admin-action-primary {
            background: var(--review-primary);
            color: var(--review-surface);
        }

        .admin-action-primary:hover {
            background: var(--review-primary-hover);
            transform: translateY(-1px);
        }

        .admin-action-danger {
            background: var(--review-danger-text);
            color: var(--review-surface);
        }

        .admin-action-danger:hover {
            background: var(--review-danger-text);
            transform: translateY(-1px);
        }

        .review-reviewed {
            border: 1px solid var(--review-border);
            border-radius: 0.75rem;
            background: var(--review-soft);
            padding: 1rem;
            color: var(--review-body);
            line-height: 1.6;
        }

        @media (max-width: 1100px) {
            .review-layout {
                grid-template-columns: 1fr;
            }

            .review-rail {
                position: static;
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 760px) {
            .supplier-review-content {
                padding: 20px 16px;
            }

            .review-page-header,
            .review-panel-header {
                flex-direction: column;
            }

            .review-summary-grid,
            .review-rail {
                grid-template-columns: 1fr;
            }

            .review-detail-row {
                grid-template-columns: 1fr;
                gap: 0.35rem;
            }
        }
    </style>
    <div class="supplier-review-shell">
        <div class="review-page-header mb-5">
            <div>
                <p class="review-eyebrow mb-3">Supplier Application</p>
                <h1 class="review-title mt-3"><?= $supplierName ?></h1>
                <!-- <p class="review-subtitle">Review the submitted business profile, verification links, uploaded documents, and decision status in one focused workspace.</p> -->
            </div>
            <?php $statusClass = 'admin-badge-' . ($status ?: 'muted'); ?>
            <span class="admin-badge <?= htmlspecialchars($statusClass, ENT_QUOTES, 'UTF-8') ?>">
                <?= htmlspecialchars($supplier['status'] ?? 'pending', ENT_QUOTES, 'UTF-8') ?>
            </span>
        </div>

        <?php if (!empty($message)): ?>
            <div class="admin-message">
                <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <div class="review-layout">
            <section class="review-panel review-primary-panel">
                <div class="review-panel-header">
                    <div>
                        <div class="review-panel-title-row">
                            <span class="review-icon"><i data-lucide="store" class="h-4 w-4"></i></span>
                            <h2 class="review-panel-title">Business Profile</h2>
                        </div>
                    </div>
                </div>

                <div class="review-section">
                    <div class="review-summary-grid">
                        <div class="review-summary-item">
                            <p class="review-label">Owner</p>
                            <p class="review-summary-value"><?= htmlspecialchars($supplier['owner_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                        <div class="review-summary-item">
                            <p class="review-label">Categories</p>
                            <p class="review-summary-value"><?= htmlspecialchars($supplier['category_names'] ?? '-', ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                        <div class="review-summary-item">
                            <p class="review-label">Payment</p>
                            <p class="review-summary-value"><?= htmlspecialchars($supplier['payment_status'] ?? '-', ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                    </div>
                </div>

                <div class="review-section">
                    <div class="review-detail-list">
                    <?php foreach ($rows as $label => $value): ?>
                        <div class="review-detail-row">
                            <span class="review-label"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span>
                            <strong class="review-value"><?= htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8') ?></strong>
                        </div>
                    <?php endforeach; ?>
                    </div>
                </div>

                <div class="review-section">
                    <div class="review-description">
                        <p class="review-label">Business description</p>
                        <p><?= htmlspecialchars($supplier['description'] ?? '-', ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                </div>
            </section>

            <aside class="review-rail">
                <section class="review-panel">
                    <div class="review-panel-header">
                        <div>
                            <div class="review-panel-title-row">
                                <span class="review-icon"><i data-lucide="shield-check" class="h-4 w-4"></i></span>
                                <h2 class="review-panel-title">Verification</h2>
                            </div>
                            <p class="review-panel-note">Open each submitted file or link before deciding.</p>
                        </div>
                    </div>
                    <div class="review-link-list">
                        <?php if (!empty($supplier['verify_url'])): ?>
                            <a href="<?= htmlspecialchars($supplier['verify_url'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" class="review-file-link">
                                <span class="review-icon"><i data-lucide="external-link" class="h-4 w-4"></i></span>
                                <span>Open website / social link</span>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($supplier['business_license_url'])): ?>
                            <a href="<?= htmlspecialchars($supplier['business_license_url'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" class="review-file-link">
                                <span class="review-icon"><i data-lucide="file-badge" class="h-4 w-4"></i></span>
                                <span>Open business license</span>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($supplier['cover_url'])): ?>
                            <a href="<?= htmlspecialchars($supplier['cover_url'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" class="review-file-link">
                                <span class="review-icon"><i data-lucide="image" class="h-4 w-4"></i></span>
                                <span>Open cover image</span>
                            </a>
                        <?php endif; ?>
                        <?php if (empty($supplier['verify_url']) && empty($supplier['business_license_url']) && empty($supplier['cover_url'])): ?>
                            <p class="review-empty">No verification files found.</p>
                        <?php endif; ?>
                    </div>
                </section>

                <section class="review-panel">
                    <div class="review-panel-header">
                        <div>
                            <div class="review-panel-title-row">
                                <span class="review-icon"><i data-lucide="list-checks" class="h-4 w-4"></i></span>
                                <h2 class="review-panel-title">Decision</h2>
                            </div>
                            <p class="review-panel-note">Approved suppliers can enter the locked dashboard and submit payment.</p>
                        </div>
                    </div>
                    <div class="review-action-stack">
                        <?php if ($status === 'pending'): ?>
                            <form method="post" action="<?= URLROOT ?>/admin/approveSupplier/<?= (int)$supplier['supplier_id'] ?>">
                                <input type="hidden" name="suppress_method_token" value="1">
                                <button class="admin-action-primary" type="submit">
                                    <i data-lucide="check" class="h-4 w-4"></i>
                                    <span>Approve supplier</span>
                                </button>
                            </form>
                            <form method="post" action="<?= URLROOT ?>/admin/rejectSupplier/<?= (int)$supplier['supplier_id'] ?>">
                                <input type="hidden" name="suppress_method_token" value="1">
                                <button class="admin-action-danger" type="submit">
                                    <i data-lucide="x" class="h-4 w-4"></i>
                                    <span>Reject</span>
                                </button>
                            </form>
                        <?php else: ?>
                            <p class="review-reviewed">This supplier has already been reviewed.</p>
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
