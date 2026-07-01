<?php
$dashboardTitle = 'Dashboard';
$dashboardCrumb = 'Overview';
$dashboardContentClass = 'h-[100vh] overflow-hidden';
$dashboardContent = function () {
?>
    <section class="h-full w-full overflow-hidden bg-app-content">
        <iframe
            src="<?= URLROOT ?>/admin/overview"
            title="Admin overview"
            class="h-full w-full border-0"
            loading="eager"
        ></iframe>
    </section>
<?php
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php $pageTitle = 'Admin Dashboard — Golden Promise'; ?>
    <?php require_once APPROOT . '/views/dashboardLayout/head.php' ?>
</head>
<body class="grid h-screen grid-cols-[280px_1fr] gap-0 bg-app-page">
    <?php require_once APPROOT . '/views/dashboardLayout/sidebar.php' ?>
</body>
</html>
