# Password Section Redesign + Customer Wiring + Email Alert

## Problem
- Password section design (floating labels) is not liked — needs clear, always-visible labels
- Customer profile forms are NOT wired (photo, personal info, password)
- No email notification when password is changed
- Need consistent behavior across all 3 roles

## Changes

### 1. EmailService — add password change notification
**File:** `app/services/EmailService.php`
- Add `sendPasswordChangedEmail(array $user, string $deviceInfo): bool`
- HTML email: "Your Golden Promise password was changed", includes time, device/browser info, IP
- Uses same `createMailer()` pattern as all other emails

### 2. Customer controller — add missing endpoints
**File:** `app/controllers/Main.php`
- Add `updateProfile()` — JSON POST, validates → `User::updateProfile()`, updates session
- Add `updatePassword()` — JSON POST, verifies current → `User::updatePassword()`, sends email notification

### 3. Admin controller — add email on password change
**File:** `app/controllers/Admin.php`
- Update `updatePassword()` to call `EmailService::sendPasswordChangedEmail()`

### 4. Supplier controller — add email on password change
**File:** `app/controllers/Supplier.php`
- Update `updatePassword()` to call `EmailService::sendPasswordChangedEmail()`

### 5. Redesign password section — admin profile
**File:** `app/views/admin/profile/profile.php`
- Replace floating-label decorated inputs with clean always-visible labels
- Keep: eye toggles, strength meter, match check
- Add: device info capture (send userAgent via JS)
- Add: success/error inline messages (not alerts)

### 6. Redesign password section — supplier profile
**File:** `app/views/supplier/profile/profile.php`
- Same clean redesign as admin
- Add: device info capture, inline messages

### 7. Redesign password section + wire forms — customer profile
**File:** `app/views/main/profile.php`
- Redesign password section with always-visible labels
- Wire photo upload to `/main/uploadProfilePhoto` (endpoint exists, JS was placeholder)
- Wire profile Save Changes to `/main/updateProfile`
- Wire password Update to `/main/updatePassword`
- Add device info capture, inline messages

### 8. Rebuild CSS
`npm run build:css`

## Password Section Design (unified across roles)
```
┌─────────────────────────────────────────┐
│ 🔒 Change Password                      │
│                                         │
│  Current Password                       │
│  ┌─────────────────────────────┐ 👁    │
│  │                             │       │
│  └─────────────────────────────┘       │
│                                         │
│  New Password                           │
│  ┌─────────────────────────────┐ 👁    │
│  │                             │       │
│  └─────────────────────────────┘       │
│  ████░░░░  Weak                        │
│                                         │
│  Confirm New Password                   │
│  ┌─────────────────────────────┐ 👁    │
│  │                             │       │
│  └─────────────────────────────┘       │
│  Passwords do not match.               │
│                                         │
│  [✓ Password updated successfully]      │
│                                         │
│              [Update Password]          │
└─────────────────────────────────────────┘
```
- Labels always visible above inputs (not floating)
- Eye toggle on the right
- Strength meter below new password
- Match hint below confirm
- Inline success/error message
- Device info silently collected and sent with request
