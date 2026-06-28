# Admin Category Management — Implementation Plan

## Context
Categories exist in the database (`categories` table with id, name, slug, created_at) and are used throughout the system (supplier onboarding, services, packages, booking logic, customer filtering). But there is **no admin CRUD interface** to manage them. Categories can only be created implicitly via `SupplierServiceManager::findOrCreateCategory()` when a supplier creates a service. Admin needs a proper management page.

## Approach
Follow the existing admin CRUD pattern (same as Packages module): list page + inline add/edit via modals. Keep it simple — the categories table has only `id`, `name`, `slug`, `created_at`.

## Files to Create/Modify

### 1. NEW: `app/models/Category.php`
Dedicated Category model with CRUD methods:
- `getAll()` — `SELECT * FROM categories ORDER BY name ASC`
- `getById($id)` — single category
- `create($name, $slug)` — INSERT
- `update($id, $name, $slug)` — UPDATE
- `delete($id)` — DELETE (cascades via FK to supplier_categories, services, etc.)
- `slugExists($slug, $excludeId)` — duplicate slug check
- `nameExists($name, $excludeId)` — duplicate name check
- `getSupplierCount($categoryId)` — count suppliers using this category
- `getServiceCount($categoryId)` — count services using this category

### 2. MODIFY: `app/controllers/Admin.php`
Add 4 methods (following the existing `packages()` / `packageCreate()` / `packageDelete()` pattern):

- **`categories()`** — GET: list all categories with supplier/service counts
- **`categoryCreate()`** — POST: validate + insert + redirect
- **`categoryUpdate($id)`** — POST: validate + update + redirect
- **`categoryDelete($id)`** — POST: check usage counts + delete + redirect

All methods will:
- Use `$this->requireRole('admin')`
- Use `$this->requireCsrf()` for POST
- Set `$_SESSION['admin_flash']` for success/error messages
- Follow the existing redirect-after-POST pattern

### 3. NEW: `app/views/admin/categories.php`
Single view file with the list table and modals (add/edit/delete confirmation). Following the existing admin view pattern:

**Layout:**
- `$dashboardTitle = 'Categories'`
- Breadcrumbs: Dashboard → Categories
- Add Category button (opens modal)
- Table: name, slug, suppliers count, services count, created date, actions (edit/delete)
- Add Modal: form with name input (slug auto-generated from name)
- Edit Modal: form with name input + hidden id
- Delete confirmation modal with usage warning
- Flash message display

**CSS:** Scoped with `admin-cat-` prefix, using the same color variables as other admin pages (`--primary: #6d4c5b`, `--border: #ead8c7`, etc.)

### 4. MODIFY: `app/views/dashboardLayout/adminsidebar.php`
Add "Categories" link in the Workspace section, between "Packages" and "Suppliers":
```php
<a href="<?= URLROOT ?>/admin/categories" class="<?= dashboard_admin_nav_class('admin/categories', $currentPath) ?>">
    <i data-lucide="tags" class="h-4 w-4"></i>
    <span class="flex-1">Categories</span>
</a>
```

## Implementation Order
1. Create `Category` model
2. Add controller methods to `Admin.php`
3. Create `categories.php` view
4. Add sidebar link

## Verification
1. Navigate to `/admin/categories` — should see all 11 categories in a table
2. Click "Add Category" — modal opens, enter name, submit → new category appears in table
3. Click edit on a category — modal opens with current name, change it, submit → name updates
4. Try duplicate name/slug → error message shown
5. Click delete on a category with no services → deleted successfully
6. Click delete on a category with services → warning shown with count
7. Sidebar link highlights when on categories page
8. New categories appear in supplier onboarding and service creation dropdowns
