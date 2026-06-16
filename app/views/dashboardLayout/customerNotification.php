<?php
$notificationConfig = [
    'role' => 'booking',
    'reviewUrl' => URLROOT . '/booking/myBookings',
    'defaultUrl' => URLROOT . '/booking/myBookings',
    'reviewLabel' => 'View bookings',
    'emptyText' => 'No customer notifications yet.',
    'referenceUrls' => [
        'booking' => URLROOT . '/booking/detail/',
        'payment' => URLROOT . '/booking/detail/',
    ],
];
?>
<style>
    .gp-customer-notification {
        --color-app-border: rgba(107, 68, 89, 0.16);
        --color-app-input: #fff;
        --color-app-secondary: #6b4459;
        --color-app-danger: #b42318;
        --color-app-white: #fff;
        --color-app-sidebar: #fff;
        --color-app-text: #2b1b24;
        --color-app-muted: #8a7180;
        --color-app-primary: #6b4459;
        --color-app-soft: #faf6f2;
        --color-app-focus: rgba(107, 68, 89, 0.28);
        --color-app-surface: #fff7ed;
        position: relative;
        z-index: 40;
    }

    .gp-customer-notification #dashboardNotificationBtn {
        width: 40px;
        height: 40px;
        border-radius: 999px;
    }

    .gp-customer-notification .dashboard-notification-panel {
        border-radius: 18px;
        box-shadow: 0 18px 45px rgba(43, 27, 36, 0.14);
    }
</style>
<div class="gp-customer-notification">
    <?php require APPROOT . '/views/dashboardLayout/notification.php'; ?>
</div>
