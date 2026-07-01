<?php
$supplier = $supplier ?? [];
$payment = $payment ?? [];
$dashboardData = $dashboardData ?? [];
$kpi = $kpi ?? null;

$dashboardTitle = 'Supplier';
$dashboardCrumb = 'Overview';
$dashboardBreadcrumbs = [
    ['label' => 'Dashboard', 'url' => URLROOT . '/supplier/dashboard'],
    ['label' => 'Overview', 'url' => null],
];
$dashboardContentClass = 'bg-[#F4F1EE] px-0 py-0';
$dashboardContent = function () use ($supplier, $payment, $dashboardData, $kpi) {
    require APPROOT . '/views/supplier/supplierDashboard.php';
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php $pageTitle = 'Dashboard — Golden Promise'; ?>
    <?php require_once APPROOT . '/views/dashboardLayout/head.php'; ?>
</head>
<body class="grid h-screen gap-0 bg-app-page" style="grid-template-columns: 280px 1fr;">
    <?php require APPROOT . '/views/dashboardLayout/suppliersidebar.php'; ?>
</body>
</html>
