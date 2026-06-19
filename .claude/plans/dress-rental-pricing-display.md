# Plan: Show Rental Pricing for Dress/Accessories in Admin Package Detail + Fix return_days display

## Problem
1. **Admin package detail**: The included services table shows `Hall` column only for venue services. Dress/accessories services don't show their borrow/buy pricing columns. `getPackageItems()` doesn't JOIN `service_rental_pricing`.

2. **Supplier service detail**: `return_days` is shown only inside the borrow price block (line 496-497). If a dress has only buy pricing, the return_days is hidden.

3. Customer-facing views (`package_detail.php`, `_service_detail_template.php`) already handle this correctly — only the admin side is missing.

---

## Changes

### 1. `app/models/PlatformPackage.php` — `getPackageItems()`
Add rental pricing JOIN and fields to the SELECT:
```sql
LEFT JOIN service_rental_pricing srp ON srp.service_id = svc.id
```
Add rental fields to the column list:
```
srp.borrow_package_price,
srp.borrow_customize_price,
srp.borrow_price,
srp.buy_package_price,
srp.buy_customize_price,
srp.buy_price,
srp.return_days
```
With fallback for when the table/columns don't exist (use the existing `hasServiceRentalPricingTable()` / `hasRentalPriceMatrixColumns()` helpers).

### 2. `app/views/admin/packages/detail.php` — both published and draft tables
Add conditional columns after Hall (like Hall conditional on venue):
- Check if any item has dress/accessories category: `$hasRentalItems`
- If yes, show "Borrow Price" and "Buy Price" columns
- Each cell shows Package price / Customize price in the same format as the customer package_detail.php rental pills

Display format per dress item:
```
Borrow: MMK X (Pkg) / MMK Y (Cust)
Buy:    MMK X (Pkg) / MMK Y (Cust)
Return: N days
```

### 3. `app/views/supplier/partials/service_detail_content.php` — fix return_days visibility
Move `return_days` display OUTSIDE the `if ($rentBorrowPackagePrice > 0 || ...)` block so it shows independently when either borrow OR buy pricing exists.

---

## Implementation order
1. Add rental SELECT + JOIN to `getPackageItems()` in PlatformPackage
2. Update admin package detail.php — add rental price columns to both read-only and editable tables
3. Fix return_days display in supplier detail to show even when only buy price is set
4. Test with a package containing a dress service
