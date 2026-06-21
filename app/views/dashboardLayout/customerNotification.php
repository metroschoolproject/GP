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
        --color-app-input: #fcf8f5;
        --color-app-secondary: #6b4459;
        --color-app-danger: #b42318;
        --color-app-white: #fcf8f5;
        --color-app-sidebar: #fcf8f5;
        --color-app-text: #2b1b24;
        --color-app-muted: #8a7180;
        --color-app-primary: #6b4459;
        --color-app-soft: #faf6f2;
        --color-app-focus: rgba(107, 68, 89, 0.28);
        --color-app-surface: #fff7ed;
        position: relative;
        z-index: 40;
        font-family: "Poppins", system-ui, -apple-system, sans-serif;
    }

    .gp-customer-notification #dashboardNotificationBtn {
        width: 40px;
        height: 40px;
        border-radius: 999px;
    }

    .gp-customer-notification .dashboard-notification-panel {
        width: min(282px, calc(100vw - 24px)) !important;
        max-width: 282px !important;
        padding: 8px 10px !important;
        border: 1px solid rgba(107, 68, 89, 0.12) !important;
        border-radius: 8px !important;
        background: #fffdf9 !important;
        box-shadow: 0 18px 45px rgba(43, 27, 36, 0.14);
    }

    .gp-customer-notification .dashboard-notification-header {
        margin: -2px -4px 8px !important;
        padding: 8px 10px !important;
        border: 0 !important;
        border-radius: 7px !important;
        background: #6D4C5B !important;
        color: #fcf8f5 !important;
    }

    .gp-customer-notification .dashboard-notification-title {
        color: #fcf8f5 !important;
        font-size: 12px !important;
        font-weight: 800 !important;
    }

    .gp-customer-notification .dashboard-notification-link {
        display: inline !important;
        font-size: 10px !important;
        font-weight: 400 !important;
        line-height: 1.1 !important;
        color: rgba(252, 248, 245, 0.72) !important;
        text-decoration: underline !important;
        text-underline-offset: 3px !important;
        background: transparent !important;
        background-color: transparent !important;
        border: 0 !important;
        border-radius: 0 !important;
        box-shadow: none !important;
        outline: 0 !important;
        padding: 0 !important;
        min-width: 0 !important;
        height: auto !important;
    }

    .gp-customer-notification .dashboard-notification-link:hover,
    .gp-customer-notification .dashboard-notification-link:focus,
    .gp-customer-notification .dashboard-notification-link:active {
        color: #fcf8f5 !important;
        background: transparent !important;
        background-color: transparent !important;
        border: 0 !important;
        box-shadow: none !important;
    }

    .gp-customer-notification .dashboard-notification-list {
        gap: 0 !important;
        max-height: 290px !important;
        overflow-y: auto !important;
        overflow-x: hidden !important;
        padding-top: 0 !important;
        scrollbar-width: none !important;
        -ms-overflow-style: none !important;
    }

    .gp-customer-notification .dashboard-notification-list::-webkit-scrollbar {
        display: none !important;
        width: 0 !important;
        height: 0 !important;
    }

    .gp-customer-notification .dashboard-notification-item {
        position: relative;
        display: block !important;
        display: grid !important;
        grid-template-columns: 34px minmax(0, 1fr) !important;
        align-items: center !important;
        gap: 10px !important;
        padding: 12px 4px !important;
        border: 0 !important;
        border-bottom: 1px solid rgba(107, 68, 89, 0.18) !important;
        border-radius: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
        text-decoration: none !important;
        transform: none !important;
    }

    .gp-customer-notification .dashboard-notification-item:last-child {
        border-bottom: 0 !important;
    }

    .gp-customer-notification .dashboard-notification-item::before {
        display: none !important;
    }

    .gp-customer-notification .dashboard-notification-icon {
        width: 34px !important;
        height: 34px !important;
        display: inline-grid !important;
        place-items: center !important;
        align-self: center !important;
        border-radius: 7px !important;
        background: rgba(154, 104, 127, 0.10) !important;
        color: #6D4C5B !important;
    }

    .gp-customer-notification .dashboard-notification-icon svg {
        width: 16px !important;
        height: 16px !important;
        fill: none !important;
        stroke: currentColor !important;
        stroke-width: 2 !important;
        stroke-linecap: round !important;
        stroke-linejoin: round !important;
    }

    .gp-customer-notification .dashboard-notification-content {
        min-width: 0 !important;
        display: block !important;
    }

    .gp-customer-notification .dashboard-notification-item:hover {
        background: rgba(154, 104, 127, 0.06) !important;
        box-shadow: none !important;
    }

    .gp-customer-notification .dashboard-notification-meta {
        align-items: flex-start !important;
        gap: 10px !important;
    }

    .gp-customer-notification .dashboard-notification-type {
        font-size: 9px !important;
        line-height: 1.1 !important;
        letter-spacing: 0.05em !important;
        text-transform: uppercase !important;
        font-weight: 700 !important;
        color: #c4aeb8 !important;
        min-width: 0 !important;
        overflow-wrap: anywhere !important;
    }

    .gp-customer-notification .dashboard-notification-time {
        margin-left: auto !important;
        font-size: 9px !important;
        line-height: 1.1 !important;
        font-weight: 400 !important;
        color: #9b8b94 !important;
    }

    .gp-customer-notification .dashboard-notification-icon.is-positive {
        background: rgba(45, 190, 114, 0.13) !important;
        color: #2DBE72 !important;
    }

    .gp-customer-notification .dashboard-notification-icon.is-negative {
        background: rgba(185, 74, 72, 0.12) !important;
        color: #B94A48 !important;
    }

    .gp-customer-notification .dashboard-notification-icon.is-pending {
        background: rgba(216, 180, 106, 0.18) !important;
        color: #C69A35 !important;
    }

    .gp-customer-notification .dashboard-notification-item-title {
        margin: 4px 0 0 !important;
        font-size: 12px !important;
        line-height: 1.28 !important;
        font-weight: 800 !important;
        color: #2b1b24 !important;
    }

    .gp-customer-notification .dashboard-notification-message {
        display: -webkit-box !important;
        margin-top: 3px !important;
        color: #9b8b94 !important;
        font-size: 9.5px !important;
        line-height: 1.35 !important;
        white-space: normal !important;
        overflow: hidden !important;
        text-overflow: clip !important;
        overflow-wrap: anywhere !important;
        -webkit-box-orient: vertical !important;
        -webkit-line-clamp: 2 !important;
        line-clamp: 2 !important;
    }

    .gp-customer-notification .dashboard-notification-more {
        display: inline-block !important;
        margin-top: 2px !important;
        color: #b7adb2 !important;
        font-size: 10px !important;
        font-weight: 400 !important;
        line-height: 1.2 !important;
        text-decoration: underline !important;
        text-underline-offset: 2px !important;
    }

    .gp-customer-notification .dashboard-notification-empty {
        margin: 8px 0 0 !important;
        padding: 14px 10px !important;
        border-radius: 8px !important;
        font-size: 11px !important;
    }
</style>
<div class="gp-customer-notification">
    <?php require APPROOT . '/views/dashboardLayout/notification.php'; ?>
</div>
