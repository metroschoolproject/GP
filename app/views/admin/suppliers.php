<?php
$dashboardTitle = 'Suppliers';
$dashboardCrumb = 'Applications';
$dashboardContentClass = 'supplier-admin-content px-6 py-6';
$dashboardContent = function () use ($suppliers, $status) {
    $supplierTotal = count($suppliers ?? []);
    $supplierPending = 0;
    $supplierApproved = 0;
    $supplierRejected = 0;

    foreach (($suppliers ?? []) as $supplier) {
        $supplierStatus = strtolower($supplier['status'] ?? 'pending');
        if ($supplierStatus === 'approved') {
            $supplierApproved++;
        } elseif ($supplierStatus === 'rejected') {
            $supplierRejected++;
        } else {
            $supplierPending++;
        }
    }
?>
    <style>
        .supplier-admin-content{min-height:100%;background:#FBFBF9;padding:28px 32px;color:#111827;font-size:13px}
        .supplier-admin-shell{--surface:#ffffff;--soft:#faf5ef;--hover:#eddecc;--border:#ead8c7;--border-light:#eddecc;--primary:#6d4c5b;--primary-hover:#7b5c69;--text:#111827;--muted:#b79c8b;--body:#7b5c69;--success-bg:#d1fae5;--success-text:#065f46;--warn-bg:#fef3c7;--warn-text:#92400e;--danger-bg:#fee2e2;--danger-text:#991b1b;--neutral-bg:#f3f4f6;--neutral-text:#57534e;max-width:1600px;margin:0 auto}
        .supplier-admin-shell *{box-sizing:border-box}
        .page-header{display:flex;align-items:flex-end;justify-content:space-between;margin-bottom:22px}
        .eyebrow,.stat-label{font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--muted);margin-bottom:6px}
        .page-header h1{font-size:22px;font-weight:700;color:var(--text);letter-spacing:-.3px;margin:0}
        .page-sub{margin-top:4px;color:var(--body);font-size:13px}
        .toolbar{display:flex;align-items:center;gap:8px;margin-bottom:20px;flex-wrap:wrap}
        .filters{display:flex;gap:6px;flex-wrap:wrap}
        .filter{display:inline-flex;align-items:center;height:34px;padding:0 14px;border:1px solid var(--border);border-radius:.75rem;background:var(--soft);color:var(--body);font-size:12px;font-weight:700;transition:all .12s;white-space:nowrap;text-decoration:none}
        .filter:hover{border-color:var(--border);background:var(--hover);color:var(--primary)}
        .filter.active{border-color:var(--primary);background:var(--primary);color:#fff}
        .summary-row{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:20px}
        .stat{background:var(--surface);border:1px solid var(--border);border-radius:.75rem;padding:14px 16px}
        .stat-value{font-size:20px;font-weight:700;color:var(--text);letter-spacing:-.3px}
        .stat-sub{font-size:11px;color:var(--muted);margin-top:3px}
        .stat-value.success{color:var(--success-text)}
        .stat-value.warn{color:var(--warn-text)}
        .stat-value.danger{color:var(--danger-text)}
        .card{background:var(--surface);border:1px solid var(--border);border-radius:.75rem;overflow:hidden;box-shadow:0 1px 2px rgba(28,25,23,.04)}
        .card-head{padding:14px 20px;border-bottom:1px solid var(--border-light);display:flex;align-items:center;justify-content:space-between}
        .card-head-left{display:flex;align-items:center;gap:8px}
        .card-head-icon{width:28px;height:28px;border-radius:.75rem;background:var(--hover);display:flex;align-items:center;justify-content:center;color:var(--primary)}
        .card-head-title{font-size:13px;font-weight:700;color:var(--text)}
        .card-count{font-size:11px;color:var(--muted);font-weight:600}
        .supplier-table-wrap{overflow-x:auto}
        .pagination{display:flex;align-items:center;justify-content:space-between;padding:12px 20px;border-top:1px solid var(--border-light)}
        .page-info{font-size:12px;color:var(--muted)}
        .page-btns{display:flex;gap:4px}
        .page-btn{height:28px;min-width:28px;padding:0 8px;border:1px solid var(--border);border-radius:.75rem;background:var(--surface);color:var(--body);font-size:12px;font-family:inherit;font-weight:600;cursor:pointer;transition:all .12s}
        .page-btn:hover{background:var(--soft)}
        .page-btn.active{background:var(--primary);color:#fff;border-color:var(--primary)}
        .page-btn:disabled{opacity:.4;cursor:default}
        .supplier-table{width:100%;border-collapse:collapse;text-align:left;font-size:13px}
        .supplier-table thead tr{background:var(--soft)}
        .supplier-table th{padding:9px 20px;color:var(--muted);font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;white-space:nowrap}
        .supplier-table th:last-child,.supplier-table td:last-child{text-align:right}
        .supplier-table tbody tr{border-top:1px solid var(--border-light);transition:background .1s}
        .supplier-table tbody tr:hover{background:var(--soft)}
        .supplier-table td{padding:13px 20px;vertical-align:middle}
        .supplier-empty{padding:34px 20px;text-align:center;color:var(--muted)}
        .biz-name{font-weight:600;color:var(--text);font-size:13px}
        .biz-email,.muted-text{font-size:11px;color:var(--muted);margin-top:2px}
        .body-text{font-size:12px;color:var(--body)}
        .badge{display:inline-flex;align-items:center;border-radius:20px;padding:3px 9px;font-size:10px;font-weight:800;letter-spacing:.05em;text-transform:uppercase}
        .admin-badge-pending{background:var(--warn-bg);color:var(--warn-text)}
        .admin-badge-approved{background:var(--success-bg);color:var(--success-text)}
        .admin-badge-rejected{background:var(--danger-bg);color:var(--danger-text)}
        .admin-badge-muted{background:var(--neutral-bg);color:var(--neutral-text)}
        .review-btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;height:30px;border:0;border-radius:.75rem;background:var(--primary);padding:0 10px;color:#fff;font-size:11px;font-weight:800;text-decoration:none;transition:background .12s}
        .review-btn:hover{background:var(--primary-hover)}
        @media(max-width:1100px){.summary-row{grid-template-columns:repeat(2,1fr)}}
        @media(max-width:760px){.supplier-admin-content{padding:20px 16px}.summary-row{grid-template-columns:1fr}}
    </style>
    <div class="supplier-admin-shell">
        <div class="page-header flex justify-between">
            <div>
                <p class="eyebrow">Suppliers</p>
                <h1>Supplier Applications</h1>
                <p class="page-sub">Review supplier profiles, license documents, and social verification links.</p>
            </div>

            <div class="toolbar">
                <div class="filters ">
                    <?php foreach ( ['all', 'rejected', 'approved', 'pending'] as $filter): ?>
                        <a href="<?= URLROOT ?>/admin/suppliers?status=<?= $filter ?>" class="filter <?= ($status ?? '') === $filter ? 'active' : '' ?>">
                            <?= ucfirst($filter) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>



        <div class="summary-row">
            <div class="stat">
                <div class="stat-label">Visible</div>
                <div class="stat-value"><?= $supplierTotal ?></div>
                <div class="stat-sub">Applications in this view</div>
            </div>
            <div class="stat">
                <div class="stat-label">Pending</div>
                <div class="stat-value warn"><?= $supplierPending ?></div>
                <div class="stat-sub">Awaiting review</div>
            </div>
            <div class="stat">
                <div class="stat-label">Approved</div>
                <div class="stat-value success"><?= $supplierApproved ?></div>
                <div class="stat-sub">Accepted suppliers</div>
            </div>
            <div class="stat">
                <div class="stat-label">Rejected</div>
                <div class="stat-value danger"><?= $supplierRejected ?></div>
                <div class="stat-sub">Declined applications</div>
            </div>
        </div>

        <section class="card">
            <div class="card-head">
                <div class="card-head-left">
                    <span class="card-head-icon"><i data-lucide="store" class="h-4 w-4"></i></span>
                    <span class="card-head-title">Applications Queue</span>
                </div>
                <span class="card-count"><?= $supplierTotal ?> records</span>
            </div>
            <div class="supplier-table-wrap">
                <table class="supplier-table">
                    <thead>
                        <tr>
                            <th>Business</th>
                            <th>Owner</th>
                            <th>Categories</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($suppliers)): ?>
                            <tr>
                                <td colspan="6" class="supplier-empty">No supplier applications found.</td>
                            </tr>
                        <?php endif; ?>
                        <?php foreach (($suppliers ?? []) as $supplier): ?>
                            <tr>
                                <td>
                                    <p class="biz-name"><?= htmlspecialchars($supplier['shop_name'] ?? 'Supplier', ENT_QUOTES, 'UTF-8') ?></p>
                                    <p class="biz-email max-w-xs truncate"><?= htmlspecialchars($supplier['verify_url'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                                </td>
                                <td>
                                    <p class="biz-name"><?= htmlspecialchars($supplier['owner_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?></p>
                                    <p class="muted-text"><?= htmlspecialchars($supplier['owner_email'] ?? '-', ENT_QUOTES, 'UTF-8') ?></p>
                                </td>
                                <td class="body-text"><?= htmlspecialchars($supplier['category_names'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <?php $statusClass = 'admin-badge-' . strtolower($supplier['status'] ?? 'muted'); ?>
                                    <span class="badge <?= htmlspecialchars($statusClass, ENT_QUOTES, 'UTF-8') ?>">
                                        <?= htmlspecialchars($supplier['status'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td class="body-text"><?= htmlspecialchars($supplier['payment_status'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <a href="<?= URLROOT ?>/admin/supplier/<?= (int)$supplier['supplier_id'] ?>" class="review-btn">
                                        <i data-lucide="clipboard-check" class="h-4 w-4"></i>
                                        <span>Review</span>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="pagination">
                <span class="page-info">Showing <?= empty($suppliers) ? '0' : '1' ?>-<?= $supplierTotal ?> of <?= $supplierTotal ?> results</span>
                <div class="page-btns">
                    <button class="page-btn" disabled><i data-lucide="chevron-left" class="h-3 w-3"></i></button>
                    <button class="page-btn active">1</button>
                    <button class="page-btn" disabled><i data-lucide="chevron-right" class="h-3 w-3"></i></button>
                </div>
            </div>
        </section>
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
