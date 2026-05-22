# Golden Promise Auth Flow Handoff

## Project Context

- PHP MVC project in `/Applications/XAMPP/xamppfiles/htdocs/GP`.
- Main auth controller: `app/controllers/Users.php`.
- Main auth UI: `app/views/users/auth.php`.
- Registration and login share one auth form.
- Database dump checked from `/Users/hsumyatmoe/Downloads/goldenpromise (3).sql`.

## Database Facts

Do not add a `role` column to `users`. Roles already use separate tables.

Existing relevant tables:

- `users`
  - primary key: `user_id`
  - has `email_verified_at`
  - has `phone`, `address`, `status`
- `roles`
  - existing role names: `customer`, `supplier`, `staff`, `admin`
- `user_roles`
  - links `user_id` and `role_id`
- `suppliers`
  - primary key: `supplier_id`
  - fields include `user_id`, `shop_name`, `description`, `status`
  - `status` supports `pending`, `verified`, `approved`, `rejected`, `banned`
- `services`
  - stores supplier service records
- `password_resets`
  - keep this for password reset only

Recommended token table for email verification is separate:

- `email_verifications`
  - SQL added in `database/add_email_verification.sql`

## Product Decisions

### Shared Auth Form

There is one public auth page:

- `/users/auth` for normal public login/register
- `/users/auth?type=supplier` for supplier register intent
- `/users/auth?type=internal` for internal admin/staff login mode

The form should not decide admin/staff role before login.

After successful password login and OTP, redirect by roles from `user_roles`:

- admin -> `admin/dashboard`
- staff -> `admin/dashboard` for now because there is no `Staff` controller/dashboard yet
- supplier -> `supplier/onboarding`
- customer/default -> `main/home`

### Public Registration

Public register can only assign:

- `customer`
- `supplier`

Never allow public register or OAuth intent to assign:

- `admin`
- `staff`

Admin/staff accounts should be created/managed internally later.

### Supplier Flow

Supplier flow:

1. User clicks `Be our partner`.
2. Auth page opens with `?type=supplier`.
3. Same register form creates `users` row.
4. Assign supplier role through `user_roles`.
5. Verify email first.
6. Verified supplier continues to `supplier/onboarding`.
7. Onboarding saves supplier profile to existing `suppliers` table.

Do not use a `supplier_applications` table. The project should use existing:

- `suppliers`
- `services`

Current supplier onboarding mapping:

- onboarding business name -> `suppliers.shop_name`
- onboarding description -> `suppliers.description`
- new supplier status -> `suppliers.status = pending`
- onboarding phone -> `users.phone`
- onboarding location -> `users.address`
- onboarding service category -> initial inactive `services` row

## Email Verification Decision

For customer/supplier email/password registration:

1. Create account and role.
2. Send verification email link.
3. Show a check-email page.
4. Verification link updates `users.email_verified_at`.
5. Verified customer goes home.
6. Verified supplier goes to onboarding.

Reason: forcing `register -> login again -> OTP` was considered annoying.

Keep `password_resets` separate from email verification even though token logic is similar.

## Current Code Changes Already Made

### Register and Login

- `app/controllers/Users.php`
  - register creates user and role intent
  - register success logs with the new registered user id
  - has email verification routes:
    - `verificationSent()`
    - `verifyEmail()`
  - password login blocks unverified customer/supplier email accounts before challenge/OTP
  - login destination is stored for OTP role-based redirect
  - Google/Facebook OAuth intent only allows customer/supplier

- `app/models/User.php`
  - `register()` returns new inserted user id
  - `assignRole()` inserts into `user_roles` without duplicates
  - `getUserRoles()` reads roles
  - includes email verification helpers

### Email Verification

- `app/models/EmailVerification.php`
- `app/libraries/EmailVerificationMailServer.php`
- `app/views/users/verification_sent.php`
- `app/views/users/email_verified.php`
- `database/add_email_verification.sql`
- stale unverified public customer/supplier accounts are cleaned after 7 days when a new registration request starts
  - cleanup skips verified, OAuth, admin, and staff accounts
  - cleanup removes non-cascading auth records before deleting the abandoned user

Run the SQL file before testing register email verification.

### Supplier

- `app/controllers/Supplier.php`
- `app/models/SupplierProfile.php`
- `app/views/supplier/onboarding.php`

### OAuth UI

- `app/views/users/auth.php`
  - normal auth shows Google/Facebook
  - supplier auth passes `type=supplier` to Google/Facebook
  - internal auth hides Google/Facebook and register toggle

## Important Verification Notes

- PHP syntax checks were run on changed PHP files.
- Real database insert tests were not completed because MySQL was not reachable from terminal during work.
- Real SMTP email verification was not tested end to end.
- Google/Facebook OAuth callback was not tested end to end.

## Suggested Next Tasks

1. Run `database/add_email_verification.sql` in phpMyAdmin.
2. Start XAMPP MySQL/Apache and test customer register email verification.
3. Test supplier register email verification and onboarding insert.
4. Decide whether login OTP should remain for every verified role.
5. Add a dedicated staff dashboard/controller if staff should not share admin dashboard.
