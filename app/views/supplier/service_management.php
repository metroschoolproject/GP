<?php
$supplier = $supplier ?? [];
$payment = $payment ?? [];
$dashboardData = $dashboardData ?? [];
$serviceManagementData = $serviceManagementData ?? ['services' => [], 'packages' => [], 'categories' => []];

$dashboardTitle = 'Supplier';
$dashboardCrumb = 'Services';
$dashboardBreadcrumbs = [
    ['label' => 'Dashboard', 'url' => URLROOT . '/supplier/dashboard'],
    ['label' => 'Services', 'url' => null],
];
$dashboardSearchPlaceholder = 'Search services, packages...';
$dashboardContentClass = 'bg-[#f7f3ed] px-0 py-0';
$dashboardContent = function () use ($serviceManagementData) {
    $serviceManagementPath = APPROOT . '/views/supplier/service_management.html';
    $serviceManagementHtml = file_exists($serviceManagementPath) ? file_get_contents($serviceManagementPath) : '';

    $serviceManagementConfigScript = '<script>window.serviceManagementConfig = ' . json_encode([
        'urls' => [
            'data' => URLROOT . '/supplier/serviceManagementData',
            'serviceCreate' => URLROOT . '/supplier/serviceCreate',
            'serviceUpdate' => URLROOT . '/supplier/serviceUpdate/',
            'serviceDelete' => URLROOT . '/supplier/serviceDelete/',
            'serviceStatus' => URLROOT . '/supplier/serviceStatus/',
            'serviceDetail' => URLROOT . '/supplier/serviceDetail/',
            'packageCreate' => URLROOT . '/supplier/packageCreate',
            'packageUpdate' => URLROOT . '/supplier/packageUpdate/',
            'packageDelete' => URLROOT . '/supplier/packageDelete/',
            'packageStatus' => URLROOT . '/supplier/packageStatus/',
        ],
        'initialData' => $serviceManagementData ?? ['services' => [], 'packages' => [], 'categories' => []],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ';</script><base target="_top">';

    if (preg_match('/<head\b[^>]*>/i', $serviceManagementHtml)) {
        $serviceManagementHtml = preg_replace('/<head\b[^>]*>/i', '$0' . $serviceManagementConfigScript, $serviceManagementHtml, 1);
    } else {
        $serviceManagementHtml = $serviceManagementConfigScript . $serviceManagementHtml;
    }

    echo '<iframe title="Service management" srcdoc="' . htmlspecialchars($serviceManagementHtml, ENT_QUOTES, 'UTF-8') . '" class="block w-full border-0" style="height:calc(100vh - 78px);min-height:760px;background:#f7f3ed;"></iframe>';
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
