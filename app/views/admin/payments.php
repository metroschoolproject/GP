<?php
$dashboardTitle = 'Payments';
$dashboardCrumb = 'Queue';
$dashboardContentClass = 'admin-payment-content px-6 py-6';
$dashboardContent = function () use ($payments, $status, $selectedPaymentId, $message) {
?>
    <style>
        .admin-payment-content {
            min-height: 100%;
            background: #FBFBF9;
        }

        .admin-payment-shell {
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
            overflow: hidden;
            border: 1px solid #e7e5e4;
            border-radius: 1.2rem;
            background: #fff;
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
            color: #a8a29e;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }

        .admin-message {
            border: 1px solid #a7f3d0;
            border-radius: 1.2rem;
            background: #ecfdf5;
            color: #047857;
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
        .admin-badge-success { background: #d1fae5; color: #065f46; }
        .admin-badge-failed { background: #fee2e2; color: #991b1b; }
        .admin-badge-muted { background: #f3f4f6; color: #57534e; border: 1px solid #d1d5db; }

        .admin-action-primary,
        .admin-action-danger,
        .admin-action-ghost {
            display: inline-flex;
            min-height: 2.25rem;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
            border-radius: 0.75rem;
            padding: 0.55rem 1rem;
            font-size: 12px;
            font-weight: 800;
            box-shadow: 0 1px 2px rgba(28, 25, 23, 0.05);
            transition: background 0.12s ease, box-shadow 0.12s ease;
        }

        .admin-action-primary {
            background: #673049;
            color: #fff;
        }

        .admin-action-primary:hover {
            background: #9b1c4a;
            box-shadow: 0 4px 12px rgba(28, 25, 23, 0.08);
        }

        .admin-action-danger {
            background: #991b1b;
            color: #fff;
        }

        .admin-action-danger:hover {
            background: #7f1d1d;
            box-shadow: 0 4px 12px rgba(28, 25, 23, 0.08);
        }

        .admin-action-ghost {
            border: 1px solid #e7e5e4;
            background: #fff;
            color: #673049;
        }

        .admin-action-ghost:hover {
            background: #fde8ef;
        }

        .payment-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 13px;
        }

        .payment-table thead {
            background: #f9f8f6;
        }

        .payment-table th {
            padding: 0.75rem 1.25rem;
            color: #a8a29e;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }

        .payment-table th:last-child,
        .payment-table td:last-child {
            text-align: right;
        }

        .payment-table tbody tr {
            border-top: 1px solid #e7e5e4;
            transition: background 0.12s ease;
        }

        .payment-table tbody tr:hover,
        .payment-table tbody tr.is-selected {
            background: #f5f5f3;
        }

        .payment-table td {
            padding: 1rem 1.25rem;
            vertical-align: middle;
        }

        .payment-actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 0.5rem;
        }

        .payment-empty {
            padding: 3rem 1.25rem;
            text-align: center;
            color: #94a3b8;
            font-size: 13px;
        }
    </style>
    <div class="admin-payment-shell mx-auto">
        <div class="mb-5 flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="admin-stat-label">Payment Review</p>
                <h1 class="admin-section-heading mt-1 text-2xl font-bold tracking-tight">Payment Queue</h1>
                <p class="admin-muted mt-1 text-sm">Verify supplier membership payments and unlock approved supplier dashboards.</p>
            </div>
            <a href="<?= URLROOT ?>/admin/suppliers" class="admin-action-ghost">
                <i data-lucide="store" class="h-4 w-4"></i>
                <span>Suppliers</span>
            </a>
        </div>

        <?php if (!empty($message)): ?>
            <div class="admin-message mb-5 px-4 py-3 text-sm font-semibold">
                <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <div class="admin-filter-row">
            <?php foreach (['pending' => 'Pending', 'success' => 'Approved', 'failed' => 'Rejected', 'all' => 'All'] as $filter => $label): ?>
                <a href="<?= URLROOT ?>/admin/payments?status=<?= $filter ?>"
                   class="admin-filter <?= ($status ?? '') === $filter ? 'active' : '' ?>">
                    <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                </a>
            <?php endforeach; ?>
        </div>

        <section class="admin-card">
            <div class="admin-card-header">
                <div class="flex items-center gap-2">
                    <i data-lucide="credit-card" class="admin-section-icon h-4 w-4"></i>
                    <h2 class="admin-section-heading text-sm font-bold">Supplier Fee Payments</h2>
                </div>
                <p class="admin-muted mt-1 text-xs">Approve a payment only after matching the transaction reference with your payment account.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="payment-table">
                    <thead>
                        <tr>
                            <th>Business</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Reference</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($payments)): ?>
                            <tr>
                                <td colspan="7" class="payment-empty">No supplier payment submissions found.</td>
                            </tr>
                        <?php endif; ?>
                        <?php foreach (($payments ?? []) as $payment): ?>
                            <?php
                                $paymentId = (int)($payment['id'] ?? 0);
                                $paymentStatus = strtolower($payment['status'] ?? 'pending');
                                $badgeClass = 'admin-badge-' . ($paymentStatus ?: 'muted');
                                $submittedAt = !empty($payment['created_at']) ? date('M j, Y g:i A', strtotime($payment['created_at'])) : '-';
                            ?>
                            <tr class="<?= $selectedPaymentId === $paymentId ? 'is-selected' : '' ?>">
                                <td>
                                    <p class="admin-value font-semibold"><?= htmlspecialchars($payment['shop_name'] ?? 'Supplier', ENT_QUOTES, 'UTF-8') ?></p>
                                    <p class="admin-muted mt-1 text-xs"><?= htmlspecialchars($payment['owner_email'] ?? '-', ENT_QUOTES, 'UTF-8') ?></p>
                                </td>
                                <td class="admin-value font-semibold"><?= number_format((float)($payment['amount'] ?? 0)) ?> MMK</td>
                                <td class="admin-body-copy"><?= htmlspecialchars($payment['method'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="admin-body-copy"><?= htmlspecialchars($payment['transaction_ref'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <span class="admin-badge <?= htmlspecialchars($badgeClass, ENT_QUOTES, 'UTF-8') ?>">
                                        <?= htmlspecialchars($paymentStatus, ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td class="admin-muted"><?= htmlspecialchars($submittedAt, ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <?php if ($paymentStatus === 'pending'): ?>
                                        <div class="payment-actions">
                                            <form method="POST" action="<?= URLROOT ?>/admin/approvePayment/<?= $paymentId ?>">
                                                <button class="admin-action-primary" type="submit">
                                                    <i data-lucide="check" class="h-4 w-4"></i>
                                                    <span>Approve</span>
                                                </button>
                                            </form>
                                            <form method="POST" action="<?= URLROOT ?>/admin/rejectPayment/<?= $paymentId ?>">
                                                <button class="admin-action-danger" type="submit">
                                                    <i data-lucide="x" class="h-4 w-4"></i>
                                                    <span>Reject</span>
                                                </button>
                                            </form>
                                        </div>
                                    <?php else: ?>
                                        <span class="admin-muted text-xs">Reviewed</span>
                                    <?php endif; ?>
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
