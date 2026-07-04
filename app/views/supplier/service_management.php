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
$dashboardContentClass = 'bg-[#F4F1EE] px-0 py-0';
$initialServiceTab = ($_GET['tab'] ?? '') === 'packages' ? 'packages' : 'services';
$dashboardContent = function () use ($supplier, $serviceManagementData, $initialServiceTab) {
    $serviceManagementPath = APPROOT . '/views/supplier/service_management.html';
    $serviceManagementHtml = file_exists($serviceManagementPath) ? file_get_contents($serviceManagementPath) : '';
    $serviceManagementStylesheet = URLROOT . '/public/css/supplier-service-management.css?v='
        . rawurlencode((string)@filemtime(dirname(APPROOT) . '/public/css/supplier-service-management.css'));
    $dashboardStylesheet = URLROOT . '/public/css/app.css?v='
        . rawurlencode((string)@filemtime(dirname(APPROOT) . '/public/css/app.css'));
    $serviceManagementScript = '<script src="' . URLROOT . '/public/js/supplier-service-management.js?v=' . rawurlencode((string)@filemtime(dirname(APPROOT) . '/public/js/supplier-service-management.js')) . '"></script>';

    $serviceManagementConfigScript = '<script>window.serviceManagementConfig = ' . json_encode([
        'urls' => [
            'data' => URLROOT . '/supplierServices/serviceManagementData',
            'serviceCreate' => URLROOT . '/supplierServices/serviceCreate',
            'serviceUpdate' => URLROOT . '/supplierServices/serviceUpdate/',
            'serviceDelete' => URLROOT . '/supplierServices/serviceDelete/',
            'serviceStatus' => URLROOT . '/supplierServices/serviceStatus/',
            'servicePublishRequest' => URLROOT . '/supplier/servicePublishRequest/',
            'serviceDetail' => URLROOT . '/supplier/serviceDetail/',
            'packageCreate' => URLROOT . '/supplierServices/packageCreate',
            'packageUpdate' => URLROOT . '/supplierServices/packageUpdate/',
            'packageDelete' => URLROOT . '/supplierServices/packageDelete/',
            'packageStatus' => URLROOT . '/supplierServices/packageStatus/',
        ],
        'initialData' => $serviceManagementData ?? ['services' => [], 'packages' => [], 'categories' => []],
        'supplierDefaults' => [
            'minLeadDays' => max(0, min(365, (int)($supplier['min_advance_days'] ?? 0))),
        ],
        'pageSize' => 24,
        'initialTab' => $initialServiceTab,
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ';</script><base target="_top">';

    $serviceManagementHtml = str_replace('<!-- SERVICE_MANAGEMENT_SCRIPT -->', $serviceManagementScript, $serviceManagementHtml);
    $serviceManagementHtml = str_replace(
        '/GP/public/css/supplier-service-management.css',
        $serviceManagementStylesheet,
        $serviceManagementHtml
    );
    $serviceManagementHtml = str_replace(
        '/GP/public/css/app.css',
        $dashboardStylesheet,
        $serviceManagementHtml
    );

    if (preg_match('/<head\b[^>]*>/i', $serviceManagementHtml)) {
        $serviceManagementHtml = preg_replace('/<head\b[^>]*>/i', '$0' . $serviceManagementConfigScript, $serviceManagementHtml, 1);
    } else {
        $serviceManagementHtml = $serviceManagementConfigScript . $serviceManagementHtml;
    }

    echo '<iframe title="Service management" srcdoc="' . htmlspecialchars($serviceManagementHtml, ENT_QUOTES, 'UTF-8') . '" class="block w-full border-0" style="height:calc(100vh - 78px);min-height:760px;background:#fbfbf9;"></iframe>';
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php $pageTitle = 'Services — Golden Promise'; ?>
    <?php require_once APPROOT . '/views/dashboardLayout/head.php'; ?>
</head>
<body class="grid h-screen gap-0 bg-app-page" style="grid-template-columns: 280px 1fr;">
    <?php require APPROOT . '/views/dashboardLayout/suppliersidebar.php'; ?>
</body>
</html>
