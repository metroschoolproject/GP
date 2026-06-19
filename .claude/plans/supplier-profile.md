# Supplier + Admin Profile — Complete ✅

## Summary
- ✅ Wired admin profile forms (Personal Information + Change Password)
- ✅ Built supplier profile page — same "Elegant Rose" design
- ✅ Refactored UploadService for dual avatar directories

## Files Modified/Created

| File | Action | Details |
|---|---|---|
| `app/services/UploadService.php` | Modified | `storeProfilePhoto()` and `removeOldProfilePhotos()` now accept `$subDirectory` param |
| `app/models/User.php` | Modified | Added `verifyPassword()`, `updatePassword()`, `updateProfile()` |
| `app/models/SupplierProfile.php` | Modified | Added `updateProfile()`, added `created_at` to `getByUserId()` |
| `app/controllers/Admin.php` | Modified | Added `updateProfile()`, `updatePassword()` JSON endpoints |
| `app/views/admin/profile/profile.php` | Modified | Wired Save Changes + Update Password to backend |
| `app/controllers/Supplier.php` | Modified | Added `profile()`, `uploadProfilePhoto()`, `removeProfilePhoto()`, `updateProfile()`, `updatePassword()` |
| `app/views/supplier/profile/profile.php` | **NEW** | Full supplier profile view with Elegant Rose design |
| `app/views/dashboardLayout/suppliersidebar.php` | Modified | Wired profile link + active state + avatar display |
| `public/css/app.css` | Rebuilt | Tailwind CSS recompiled |

## Routes (auto-resolved)
- `GET /supplier/profile` → `Supplier::profile()`
- `POST /supplier/uploadProfilePhoto` → `Supplier::uploadProfilePhoto()`
- `POST /supplier/removeProfilePhoto` → `Supplier::removeProfilePhoto()`
- `POST /supplier/updateProfile` → `Supplier::updateProfile()`
- `POST /supplier/updatePassword` → `Supplier::updatePassword()`
- `POST /admin/updateProfile` → `Admin::updateProfile()`
- `POST /admin/updatePassword` → `Admin::updatePassword()`
