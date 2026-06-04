<?php
$dashboardTitle = 'Suppliers';
$dashboardCrumb = 'Applications';
$dashboardContentClass = 'supplier-admin-content px-6 py-6';
$dashboardContent = function () use ($suppliers, $status) {
?>
    <style>
        .supplier-admin-content {
            min-height: 100%;
            background: #FBFBF9;
        }

        .supplier-admin-shell {
            max-width: 1600px;
            color: #1c1917;
            font-size: 13px;
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
            transition: box-shadow 0.18s ease;
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

        .admin-filter-row {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 1.25rem;
        }

        .admin-filter {
            display: inline-flex;
            min-height: 2.25rem;
            align-items: center;
            justify-content: center;
            border: 1px solid #e7e5e4;
            border-radius: 0.75rem;
            background: #f5f5f3;
            color: #57534e;
            padding: 0.55rem 1rem;
            font-size: 13px;
            font-weight: 700;
            transition: all 0.12s ease;
        }

        .admin-filter:hover {
            border-color: #f9c0d2;
            background: #fde8ef;
            color: #673049;
        }

        .admin-filter.active {
            border-color: #673049;
            background: #673049;
            color: #fff;
            box-shadow: 0 1px 2px rgba(28, 25, 23, 0.05);
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

        .admin-action-primary {
            display: inline-flex;
            min-height: 2.25rem;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
            border-radius: 0.75rem;
            background: #673049;
            padding: 0.55rem 1rem;
            color: #fff;
            font-size: 12px;
            font-weight: 800;
            box-shadow: 0 1px 2px rgba(28, 25, 23, 0.05);
            transition: background 0.12s ease, box-shadow 0.12s ease;
        }

        .admin-action-primary:hover {
            background: #9b1c4a;
            box-shadow: 0 4px 12px rgba(28, 25, 23, 0.08);
        }

        .supplier-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 13px;
        }

        .supplier-table thead {
            background: #f9f8f6;
        }

        .supplier-table th {
            padding: 0.75rem 1.25rem;
            color: #a8a29e;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }

        .supplier-table th:last-child,
        .supplier-table td:last-child {
            text-align: right;
        }

        .supplier-table tbody tr {
            border-top: 1px solid #e7e5e4;
            transition: background 0.12s ease;
        }

        .supplier-table tbody tr:hover {
            background: #f5f5f3;
        }

        .supplier-table td {
            padding: 1rem 1.25rem;
            vertical-align: middle;
        }

        .supplier-table td.supplier-empty {
            padding: 3rem 1.25rem;
            text-align: center;
            color: #94a3b8;
            font-size: 13px;
        }
    </style>
    <div class="supplier-admin-shell mx-auto">
        <div class="mb-5 flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="admin-stat-label">Supplier Review</p>
                <h1 class="admin-section-heading mt-1 text-2xl font-bold tracking-tight">Supplier Applications</h1>
                <p class="admin-muted mt-1 text-sm">Review supplier profiles, license documents, and social verification links.</p>
            </div>
        </div>

        <div class="admin-filter-row">
            <?php foreach (['pending', 'approved', 'rejected', 'all'] as $filter): ?>
                <a href="<?= URLROOT ?>/admin/suppliers?status=<?= $filter ?>"
                   class="admin-filter <?= ($status ?? '') === $filter ? 'active' : '' ?>">
                    <?= ucfirst($filter) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <section class="admin-card overflow-hidden">
            <div class="admin-card-header">
                <div class="flex items-center gap-2">
                    <i data-lucide="store" class="admin-section-icon h-4 w-4"></i>
                    <h2 class="admin-section-heading text-sm font-bold">Applications Queue</h2>
                </div>
                <p class="admin-muted mt-1 text-xs">Filter applications by approval state and open each profile to review.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="supplier-table">
                    <thead>
                        <tr>
                            <th>Business</th>
                            <th>Owner</th>
                            <th>Service</th>
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
                                    <p class="admin-value font-semibold"><?= htmlspecialchars($supplier['shop_name'] ?? 'Supplier', ENT_QUOTES, 'UTF-8') ?></p>
                                    <p class="admin-muted mt-1 max-w-xs truncate text-xs"><?= htmlspecialchars($supplier['verify_url'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                                </td>
                                <td>
                                    <p class="admin-value font-medium"><?= htmlspecialchars($supplier['owner_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?></p>
                                    <p class="admin-muted text-xs"><?= htmlspecialchars($supplier['owner_email'] ?? '-', ENT_QUOTES, 'UTF-8') ?></p>
                                </td>
                                <td class="admin-body-copy"><?= htmlspecialchars($supplier['service_name'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <?php $statusClass = 'admin-badge-' . strtolower($supplier['status'] ?? 'muted'); ?>
                                    <span class="admin-badge <?= htmlspecialchars($statusClass, ENT_QUOTES, 'UTF-8') ?>">
                                        <?= htmlspecialchars($supplier['status'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td class="admin-body-copy"><?= htmlspecialchars($supplier['payment_status'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <a href="<?= URLROOT ?>/admin/supplier/<?= (int)$supplier['supplier_id'] ?>" class="admin-action-primary">
                                        <i data-lucide="clipboard-check" class="h-4 w-4"></i>
                                        <span>Review</span>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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
