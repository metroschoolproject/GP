# Plan: Supplier Notification Page + Redesign Both Notification Pages

## Overview

1. Add a dedicated notification list page for suppliers (like admin's `/admin/notifications`)
2. Redesign both the admin and supplier notification list pages with a modern Tailwind UI using the project's `app-*` design tokens

---

## Step 1: Add backend methods — `SupplierNotifications.php`

Add two new methods to `app/controllers/SupplierNotifications.php`:

### `notifications()`
```php
public function notifications()
{
    $this->view('supplier/notifications', [
        'notifications' => $this->notificationModel->getAll($this->currentUserId(), 80),
        'unreadCount' => $this->notificationModel->getUnreadCount($this->currentUserId()),
        'message' => $_SESSION['supplier_flash'] ?? '',
    ]);
    unset($_SESSION['supplier_flash']);
}
```

### `notification($notificationId = null)`
```php
public function notification($notificationId = null)
{
    if (!$notificationId) {
        redirect('supplier/notifications');
    }

    $notification = $this->notificationModel->getById((int)$notificationId, $this->currentUserId());

    if (!$notification) {
        redirect('supplier/notifications');
    }

    $this->notificationModel->markRead((int)$notificationId, $this->currentUserId());
    $referenceType = (string)($notification['reference_type'] ?? '');
    $referenceId = (int)($notification['reference_id'] ?? 0);

    if ($referenceType === 'booking' && $referenceId > 0) {
        redirect('supplier/bookingDetail/' . $referenceId);
    }

    redirect('supplier/dashboard');
}
```

---

## Step 2: Forward from `Supplier.php`

Add forwarding methods in `app/controllers/Supplier.php` (after `markNotificationRead`):

```php
public function notifications()
{
    return $this->forwardTo(SupplierNotifications::class, __FUNCTION__, func_get_args());
}

public function notification($notificationId = null)
{
    return $this->forwardTo(SupplierNotifications::class, __FUNCTION__, func_get_args());
}
```

---

## Step 3: Create supplier notification view

New file: `app/views/supplier/notifications.php`

Pattern: Follows the same layout convention as `supplier/bookings.php` and `supplier/dashboard.php` (sets `$dashboardTitle`, `$dashboardCrumb`, `$dashboardContentClass`, `$dashboardContent` closure, then renders the sidebar layout).

Design:
- Clean Tailwind card-based layout using `app-*` tokens
- Header: page title + unread count pill
- Optional flash message
- Each notification = a linked card with:
  - Unread dot indicator (left column)
  - Type badge (small uppercase label)
  - Title (bold)
  - Message body (truncated)
  - Timestamp (right column)
- Empty state: dashed border card with centered text
- Each card links to `/supplier/notification/{id}` (which marks read & redirects)

---

## Step 4: Redesign admin notification view

Rewrite `app/views/admin/notifications.php`:

- Replace the inline hardcoded CSS with Tailwind utility classes using `app-*` tokens
- Same visual structure as the new supplier page (for consistency)
- Keep the same data flow and PHP variables
- Update `$notificationHref` to continue using `/admin/notification/{id}`
- Flash message, empty state, unread dot — all Tailwind-ified

---

## Step 5: Update sidebar link and config

In `app/views/dashboardLayout/suppliersidebar.php`:

1. Update `$notificationConfig['reviewUrl']` from `/supplier/dashboard` → `/supplier/notifications`
2. Change the sidebar notification link `href` from `/supplier/dashboard` to `/supplier/notifications`

---

## Design Tokens (Tailwind `app-*` classes)

The project uses these Tailwind theme tokens (from `resources/css/app.css`):
- `bg-app-page` / `bg-app-card` / `bg-app-sidebar` / `bg-app-soft` / `bg-app-input`
- `text-app-text` / `text-app-muted` / `text-app-primary` / `text-app-secondary` / `text-app-strong`
- `border-app-border` / `border-app-focus` / `border-app-ring`
- `bg-app-primary` / `bg-app-danger` / `bg-app-success` / `bg-app-warning`
- `shadow-card` / `shadow-panel`
- `rounded-card` / `rounded-field`

---

## Files touched

| File | Action |
|---|---|
| `app/controllers/SupplierNotifications.php` | Add `notifications()` and `notification($id)` |
| `app/controllers/Supplier.php` | Add forwarding stubs |
| `app/views/supplier/notifications.php` | **Create** — new view |
| `app/views/admin/notifications.php` | **Rewrite** — Tailwind redesign |
| `app/views/dashboardLayout/suppliersidebar.php` | Update link URL + config |
