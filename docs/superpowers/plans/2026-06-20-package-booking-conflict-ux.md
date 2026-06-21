# Package Booking Conflict UX Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** When a customer books a package and one of its slot-type services is full on the chosen date, tell them *which* service, report *all* conflicts at once, and (Phase 2) suggest alternative dates — instead of failing with a single generic error.

**Architecture:** Today `Booking::createPost()` (`app/controllers/Booking.php`) opens a DB transaction, calls `BookingModel::reservePackageServiceSlots()`, and on the first slot that can't be reserved throws a generic `RuntimeException` that the catch block turns into HTTP 500 `"Booking could not be created. Please review availability and try again."`. The client submit handler (`app/views/booking/create.php`) only reads `data.error`. This plan adds (1) a **pre-flight check** before the transaction that reuses the *already-built* availability data from `CartModel::getPackageEventSchedule()` to collect every unavailable service, (2) a **race backstop** so the in-transaction failure also names the failed service, returned as structured `422 {error, unavailable:[…]}`, and (4) a client renderer for that list. Phase 2 adds alternative-date suggestions, which the same client renderer already displays.

**Tech Stack:** PHP 8 custom MVC, PDO via `app/libraries/Database.php`, MySQL, vanilla JS (`fetch`) in `app/views/booking/create.php`. No test framework — verification uses CLI sanity scripts under `database/` (the established pattern, same as `seed_*.php`) plus manual browser checks.

**Implementation status (2026-06-21):** All six implementation tasks are present in the codebase and were committed in `fa1ff7c2` through `c18a0692`. The unchecked task-level boxes below are retained as the original execution record; remaining work is environment-backed CLI/browser verification.

## Global Constraints

- No new Composer/npm dependencies. No test framework — verify with CLI scripts (`php database/verify_*.php`) and manual browser checks.
- Run all CLI scripts **from the project root**: `php database/<script>.php`.
- Models construct their own `Database` (`new Database()` reads `DB_*` constants from `app/config/config.php`); do not add constructor params.
- Follow existing model style: per-call failure state stored on a private property with a getter (mirrors the existing `$replacementSwapError` pattern in `BookingModel.php:17`).
- Pool concurrency columns (`confirmed_package_count`, `max_concurrent_package`, etc.) already exist in `service_time_slots` (applied migration `database/migration_add_concurrency_pools.sql`). Do not re-add them.
- The slot UPDATE in `BookingModel::reserveServiceSlot()` (`BookingModel.php:651-680`) is the authoritative race guard. The pre-flight check is advisory only; never remove the reserve-time check.
- JSON contract for an availability conflict (used by every task):
  ```json
  HTTP 422
  {
    "error": "Some package services aren't available on your selected date.",
    "unavailable": [
      {
        "service_id": 12,
        "service_name": "Wedding Photographer",
        "date": "2026-06-20",
        "message": "No package slots available for this time",
        "alternatives": [ { "date": "2026-06-27", "label": "Sat, Jun 27" } ]
      }
    ]
  }
  ```
  Phase 1 omits `alternatives` (or leaves it `[]`). Phase 2 populates it. The client renderer (Task 4) handles both.

---

## File Structure

| File | Responsibility | Phase |
|---|---|---|
| `app/models/CartModel.php` | Add `getUnavailablePackageServices()`; Phase 2 add `findAlternativePackageDates()` | 1, 2 |
| `app/libraries/SlotUnavailableException.php` | New typed exception carrying the unavailable-service list (autoloaded from `libraries/`) | 1 |
| `app/models/BookingModel.php` | Record which service failed at reserve time; expose via getter | 1 |
| `app/controllers/Booking.php` | Pre-flight collection before the transaction; catch `SlotUnavailableException` → 422; Phase 2 attach alternatives | 1, 2 |
| `app/views/booking/create.php` | Render `data.unavailable` (incl. alternatives) in the submit handler | 1 |
| `database/_verify_bootstrap.php` | Shared CLI harness: framework autoload, assert helpers, fixture setup/teardown | 1 |
| `database/verify_unavailable_package_services.php` | Verify Task 1 | 1 |
| `database/verify_reserve_failure_detail.php` | Verify Task 2 | 1 |
| `database/verify_alternative_dates.php` | Verify Task 5 | 2 |

---

# PHASE 1 — Name the failing service (Option 1) + Pre-flight all conflicts (Option 2)

## Task 1: `CartModel::getUnavailablePackageServices()` + CLI harness

Reuse the availability data `getPackageEventSchedule()` already computes (`is_available`, `service_name`, `availability_message` per row — see `CartModel.php:602-619, 681-691`) and return only the unavailable slot-type services.

**Files:**
- Create: `database/_verify_bootstrap.php`
- Create: `database/verify_unavailable_package_services.php`
- Modify: `app/models/CartModel.php` (add method after `getPackageEventSchedule()`, which ends at `CartModel.php:627`)

**Interfaces:**
- Consumes: `CartModel::getPackageEventSchedule(int $packageId, string $eventDate): array` (existing) — each row has keys `service_id`, `service_name`, `booking_type`, `is_available`, `availability_message`.
- Produces:
  - `CartModel::getUnavailablePackageServices(int $packageId, string $eventDate): array` — returns `array<int,array{service_id:int,service_name:string,date:string,message:string}>`, one entry per slot-type service that is **not** available on `$eventDate`. Empty array means all available.
  - Shared CLI helpers (in `_verify_bootstrap.php`): `check(bool $cond, string $msg): void`, `verifyDone(): void`, `verifyPdo(): PDO`, `findSlotPackageService(): ?array` returning `[int $packageId, int $serviceId, string $serviceName]` or `null`, `forceSlotFull(CartModel $cart, int $packageId, int $serviceId, string $date): void`, `clearSlot(int $serviceId, string $date): void`.

- [ ] **Step 1: Create the shared CLI harness**

Create `database/_verify_bootstrap.php`:

```php
<?php
/**
 * Shared bootstrap for verify_*.php sanity scripts.
 * Loads the framework autoload + models so model methods can be driven from CLI.
 * Run from the project root:  php database/verify_xxx.php
 */
require __DIR__ . '/../app/config/config.php';            // defines APPROOT, DB_*
require_once APPROOT . '/../vendor/autoload.php';
spl_autoload_register(function ($class) {
    $f = APPROOT . '/libraries/' . $class . '.php';
    if (is_file($f)) {
        require_once $f;
    }
});
require_once APPROOT . '/models/CartModel.php';
require_once APPROOT . '/models/BookingModel.php';

$GLOBALS['__verify_failed'] = false;

function check(bool $cond, string $msg): void {
    echo ($cond ? "PASS" : "FAIL") . ": {$msg}\n";
    if (!$cond) { $GLOBALS['__verify_failed'] = true; }
}

function verifyDone(): void {
    echo $GLOBALS['__verify_failed'] ? "\n=== FAILED ===\n" : "\n=== ALL PASS ===\n";
    exit($GLOBALS['__verify_failed'] ? 1 : 0);
}

/** Raw PDO for fixture setup/teardown, separate from the models' own connections. */
function verifyPdo(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
    }
    return $pdo;
}

/** Find any package containing a slot-type service. Returns [packageId, serviceId, name] or null. */
function findSlotPackageService(): ?array {
    $row = verifyPdo()->query(
        "SELECT pi.package_id, pi.service_id, s.name
           FROM package_items pi
           JOIN services s ON s.id = pi.service_id
          WHERE s.booking_type = 'slot' AND pi.deleted_at IS NULL
          LIMIT 1"
    )->fetch(PDO::FETCH_ASSOC);
    return $row ? [(int)$row['package_id'], (int)$row['service_id'], (string)$row['name']] : null;
}

/**
 * Force the service's package-resolved slot on $date to FULL.
 * Creates the slot row with capacity 1 already consumed (total + package pool),
 * so availability is exhausted regardless of the service's real max_concurrent
 * — which can exceed the slot counter column's UNSIGNED range for high-capacity
 * venues. The slot row's own caps govern getPackageServiceSlotAvailability().
 */
function forceSlotFull(CartModel $cart, int $packageId, int $serviceId, string $date): void {
    $start = '09:00:00'; $end = '17:00:00';
    foreach ($cart->getPackageEventSchedule($packageId, $date) as $r) {
        if ((int)($r['service_id'] ?? 0) === $serviceId) {
            $start = (string)$r['start_time'];
            $end   = (string)$r['end_time'];
            break;
        }
    }
    $pdo = verifyPdo();
    $pdo->prepare("DELETE FROM service_time_slots WHERE service_id=:s AND date=:d AND start_time=:st AND end_time=:et")
        ->execute([':s' => $serviceId, ':d' => $date, ':st' => $start, ':et' => $end]);
    $pdo->prepare(
        "INSERT INTO service_time_slots
            (service_id, date, start_time, end_time, confirmed_count, max_concurrent,
             confirmed_package_count, confirmed_customize_count,
             max_concurrent_package, max_concurrent_customize, status)
         VALUES (:s,:d,:st,:et, 1,1, 1,0, 1,0, 'full')"
    )->execute([':s' => $serviceId, ':d' => $date, ':st' => $start, ':et' => $end]);
}

/** Remove every slot row for a service on a date (teardown). */
function clearSlot(int $serviceId, string $date): void {
    verifyPdo()->prepare("DELETE FROM service_time_slots WHERE service_id=:s AND date=:d")
               ->execute([':s' => $serviceId, ':d' => $date]);
}
```

- [ ] **Step 2: Write the failing verify script**

Create `database/verify_unavailable_package_services.php`:

```php
<?php
require __DIR__ . '/_verify_bootstrap.php';

$f = findSlotPackageService();
if (!$f) { echo "SKIP: no slot-type package service in DB\n"; exit(0); }
[$packageId, $serviceId, $name] = $f;
$date = '2027-12-31';                 // far future → safe to wipe/teardown
$cart = new CartModel();

// Baseline: no slot row → service is available → not in the unavailable list.
clearSlot($serviceId, $date);
$before = $cart->getUnavailablePackageServices($packageId, $date);
check(!in_array($serviceId, array_column($before, 'service_id'), true),
      "service {$serviceId} ({$name}) is available when no slot row exists");

// Force full → must appear as unavailable, carrying name + date + message.
forceSlotFull($cart, $packageId, $serviceId, $date);
$after = $cart->getUnavailablePackageServices($packageId, $date);
$hit = null;
foreach ($after as $u) { if ((int)$u['service_id'] === $serviceId) { $hit = $u; break; } }
check($hit !== null,                          "service reported unavailable when slot is full");
check($hit && $hit['service_name'] !== '',    "unavailable entry carries a service_name");
check($hit && $hit['date'] === $date,         "unavailable entry carries the queried date");
check($hit && !empty($hit['message']),        "unavailable entry carries a message");

clearSlot($serviceId, $date);                 // teardown
verifyDone();
```

- [ ] **Step 3: Run it to verify it fails**

Run: `php database/verify_unavailable_package_services.php`
Expected: PHP fatal error `Call to undefined method CartModel::getUnavailablePackageServices()` (method not written yet). If it prints `SKIP`, seed at least one published package with a slot-type service first, then re-run.

- [ ] **Step 4: Implement the method**

In `app/models/CartModel.php`, add immediately after `getPackageEventSchedule()` (after the closing brace at `CartModel.php:627`):

```php
    /**
     * Return the slot-type services in a package that are NOT available on a
     * given date. Reuses getPackageEventSchedule()'s computed availability so
     * the logic stays in one place. Empty array = every service is bookable.
     *
     * @return array<int,array{service_id:int,service_name:string,date:string,message:string}>
     */
    public function getUnavailablePackageServices(int $packageId, string $eventDate): array
    {
        $unavailable = [];
        foreach ($this->getPackageEventSchedule($packageId, $eventDate) as $row) {
            if (($row['booking_type'] ?? '') !== 'slot') {
                continue; // 'managed' services are always available
            }
            if (empty($row['is_available'])) {
                $unavailable[] = [
                    'service_id'   => (int)($row['service_id'] ?? 0),
                    'service_name' => (string)($row['service_name'] ?? 'Package service'),
                    'date'         => $eventDate,
                    'message'      => (string)($row['availability_message']
                                        ?? 'No package slots available for this time'),
                ];
            }
        }
        return $unavailable;
    }
```

- [ ] **Step 5: Run it to verify it passes**

Run: `php database/verify_unavailable_package_services.php`
Expected: four `PASS:` lines then `=== ALL PASS ===` (exit 0).

- [ ] **Step 6: Commit**

```bash
git add database/_verify_bootstrap.php database/verify_unavailable_package_services.php app/models/CartModel.php
git commit -m "feat(booking): collect unavailable package services for a date"
```

---

## Task 2: Name the failed service at reserve time (race backstop)

Even after pre-flight passes, the atomic reserve at commit can still fail if someone books in the gap. Make that failure carry the service identity instead of a bare `false`, and add a typed exception the controller can catch.

**Files:**
- Create: `app/libraries/SlotUnavailableException.php`
- Modify: `app/models/BookingModel.php` (property near `BookingModel.php:17`; method body `reservePackageServiceSlots` at `BookingModel.php:468-508`; add getter)
- Create: `database/verify_reserve_failure_detail.php`

**Interfaces:**
- Consumes: `BookingModel::reservePackageServiceSlots(int $bookingId, string $eventDate, array $packageSchedule): array|false` (existing) — each schedule event has keys `booking_type`, `service_id`, `service_name`, `start_time`, `end_time`, `package_item_id`, `item_max_concurrent`.
- Produces:
  - `SlotUnavailableException` (extends `RuntimeException`) with public `array $services` (list of `{service_id,service_name,date,message,alternatives?}`).
  - `BookingModel::getLastUnavailableService(): ?array` returning `{service_id:int,service_name:string,date:string,message:string}` for the most recent `reservePackageServiceSlots` failure, or `null`.

- [ ] **Step 1: Write the failing verify script**

Create `database/verify_reserve_failure_detail.php`:

```php
<?php
require __DIR__ . '/_verify_bootstrap.php';

$f = findSlotPackageService();
if (!$f) { echo "SKIP: no slot-type package service in DB\n"; exit(0); }
[$packageId, $serviceId, $name] = $f;
$date = '2027-12-31';
$cart = new CartModel();
$booking = new BookingModel();

// Resolve the package's auto start/end for this service, then force that slot full.
$start = '09:00:00'; $end = '17:00:00';
foreach ($cart->getPackageEventSchedule($packageId, $date) as $r) {
    if ((int)($r['service_id'] ?? 0) === $serviceId) { $start=(string)$r['start_time']; $end=(string)$r['end_time']; break; }
}
forceSlotFull($cart, $packageId, $serviceId, $date);

// reserveServiceSlot fails BEFORE recordSlotReservation, so bookingId 0 is fine.
$event = [
    'booking_type'        => 'slot',
    'service_id'          => $serviceId,
    'service_name'        => $name,
    'start_time'          => $start,
    'end_time'            => $end,
    'package_item_id'     => 0,
    'item_max_concurrent' => 0,
];
$result = $booking->reservePackageServiceSlots(0, $date, [$event]);
check($result === false, "reservePackageServiceSlots returns false when the slot is full");

$fail = $booking->getLastUnavailableService();
check(is_array($fail) && (int)$fail['service_id'] === $serviceId, "getLastUnavailableService names the failed service");
check(is_array($fail) && $fail['service_name'] === $name,         "failure detail carries service_name");
check(is_array($fail) && $fail['date'] === $date,                 "failure detail carries the date");

clearSlot($serviceId, $date);  // teardown
verifyDone();
```

- [ ] **Step 2: Run it to verify it fails**

Run: `php database/verify_reserve_failure_detail.php`
Expected: PHP fatal error `Call to undefined method BookingModel::getLastUnavailableService()`.

- [ ] **Step 3: Create the exception class**

Create `app/libraries/SlotUnavailableException.php` (the `libraries/` autoloader at `app/boostrap.php:12-14` resolves the class by filename):

```php
<?php

class SlotUnavailableException extends RuntimeException
{
    /** @var array<int,array> list of unavailable-service entries for the JSON response */
    public array $services;

    public function __construct(array $services, string $message = 'Package services unavailable')
    {
        parent::__construct($message);
        $this->services = $services;
    }
}
```

- [ ] **Step 4: Add the failure property + getter to BookingModel**

In `app/models/BookingModel.php`, add the property alongside the existing private state (immediately after `private ?string $replacementSwapError = null;` at `BookingModel.php:17`):

```php
    private ?array $lastUnavailableService = null;
```

Add the getter (place it right before `reservePackageServiceSlots`, i.e. just above `BookingModel.php:468`):

```php
    /**
     * Details of the service whose slot could not be reserved in the most
     * recent reservePackageServiceSlots() call, or null if the last call
     * succeeded. Mirrors the $replacementSwapError accessor pattern.
     *
     * @return array{service_id:int,service_name:string,date:string,message:string}|null
     */
    public function getLastUnavailableService(): ?array
    {
        return $this->lastUnavailableService;
    }
```

- [ ] **Step 5: Record the failed service in reservePackageServiceSlots**

In `app/models/BookingModel.php`, modify `reservePackageServiceSlots` (`BookingModel.php:468-508`). Reset state at the top of the method (insert as the first line inside the method, before the `foreach` at `BookingModel.php:470`):

```php
        $this->lastUnavailableService = null;
```

Then replace the slot-reserve failure block (currently `BookingModel.php:492-494`):

```php
                if (!$slotId || !$this->reserveServiceSlot($slotId, 'package')) {
                    return false;
                }
```

with:

```php
                if (!$slotId || !$this->reserveServiceSlot($slotId, 'package')) {
                    $this->lastUnavailableService = [
                        'service_id'   => $svcId,
                        'service_name' => (string)($event['service_name'] ?? 'Package service'),
                        'date'         => $eventDate,
                        'message'      => 'No package slots available for this time',
                    ];
                    return false;
                }
```

Leave the `recordSlotReservation` failure `return false;` (`BookingModel.php:502-504`) unchanged — it is a DB write failure, not an availability conflict.

- [ ] **Step 6: Run it to verify it passes**

Run: `php database/verify_reserve_failure_detail.php`
Expected: four `PASS:` lines then `=== ALL PASS ===` (exit 0).

- [ ] **Step 7: Commit**

```bash
git add app/libraries/SlotUnavailableException.php app/models/BookingModel.php database/verify_reserve_failure_detail.php
git commit -m "feat(booking): record which package service failed slot reservation"
```

---

## Task 3: Pre-flight check + structured 422 in the controller

Collect every conflict before opening the transaction, and surface the in-transaction backstop failure as the same structured 422.

**Files:**
- Modify: `app/controllers/Booking.php` (insert pre-flight before `beginTransaction()` at `Booking.php:334`; change the reserve-failure throw at `Booking.php:387-389`; add a catch before the generic one at `Booking.php:466`)

**Interfaces:**
- Consumes: `CartModel::getUnavailablePackageServices()` (Task 1), `BookingModel::getLastUnavailableService()` and `SlotUnavailableException` (Task 2), existing `jsonResponse($payload, $statusCode)` (`app/traits/JsonResponseTrait.php`).
- Produces: HTTP `422` JSON matching the Global Constraints contract whenever a package service is unavailable; the existing `200`/`500` paths are unchanged.

- [ ] **Step 1: Add the pre-flight collection before the transaction**

In `app/controllers/Booking.php`, insert this block immediately **before** `$this->bookingModel->beginTransaction();` (`Booking.php:334`):

```php
        // PRE-FLIGHT: gather every package service with no slot left on its
        // chosen date so the customer sees all conflicts at once, before we
        // open a transaction. Advisory only — reserveServiceSlot() is still
        // the authoritative race guard inside the transaction below.
        $unavailable = [];
        foreach ($items as $i => $item) {
            if (($item['item_type'] ?? '') !== 'package') {
                continue;
            }
            if (!empty($item['package_cart_item_id'])) {
                continue; // add-ons inherit the parent package's schedule
            }
            $pkgDate = trim($_POST['item_date'][$i] ?? '') ?: trim((string)($item['selected_date'] ?? ''));
            if ($pkgDate === '') {
                continue;
            }
            foreach ($this->cartModel->getUnavailablePackageServices((int)($item['item_id'] ?? 0), $pkgDate) as $u) {
                $unavailable[] = $u;
            }
        }
        if (!empty($unavailable)) {
            $this->jsonResponse([
                'error'       => "Some package services aren't available on your selected date.",
                'unavailable' => $unavailable,
            ], 422);
        }
```

(`jsonResponse` calls `exit`, so this returns before any transaction is opened — no rollback needed.)

- [ ] **Step 2: Make the in-transaction failure carry the service**

In `app/controllers/Booking.php`, replace the reserve-failure throw (`Booking.php:387-389`):

```php
                    if ($this->bookingModel->reservePackageServiceSlots($bookingId, $pkgDate, $packageSchedule) === false) {
                        throw new RuntimeException('One of the selected package services is no longer available.');
                    }
```

with:

```php
                    if ($this->bookingModel->reservePackageServiceSlots($bookingId, $pkgDate, $packageSchedule) === false) {
                        $fail = $this->bookingModel->getLastUnavailableService();
                        throw new SlotUnavailableException($fail ? [$fail] : []);
                    }
```

- [ ] **Step 3: Catch the typed exception → 422**

In `app/controllers/Booking.php`, add a catch clause **before** the existing `} catch (Throwable $e) {` (`Booking.php:466`):

```php
        } catch (SlotUnavailableException $e) {
            if ($transactionStarted) {
                $this->bookingModel->rollBack();
            }
            $this->jsonResponse([
                'error'       => "Some package services aren't available on your selected date.",
                'unavailable' => $e->services,
            ], 422);
        } catch (Throwable $e) {
```

- [ ] **Step 4: Syntax-check the controller**

Run: `php -l app/controllers/Booking.php`
Expected: `No syntax errors detected in app/controllers/Booking.php`

- [ ] **Step 5: Manual pre-flight verification in the browser**

1. Pick a real slot-type package service and a date you will book. Force its slot full for that date (reuse the harness):
   ```bash
   php -r 'require "database/_verify_bootstrap.php"; $f=findSlotPackageService(); [$p,$s,$n]=$f; $d="2027-12-31"; forceSlotFull(new CartModel(),$p,$s,$d); echo "Forced full: package $p service $s ($n) on $d\n";'
   ```
2. Log in as a customer, add that package (id `$p`) to the cart, go to `booking/create`, and select date `2027-12-31`.
3. Open DevTools → Network, submit the booking.
4. Expected: the `createPost` request returns **HTTP 422** with a JSON body containing `unavailable: [{ service_name: "<$n>", date: "2027-12-31", message: "No package slots available for this time" }]`. No booking row is created.
5. Teardown:
   ```bash
   php -r 'require "database/_verify_bootstrap.php"; $f=findSlotPackageService(); [$p,$s,$n]=$f; clearSlot($s,"2027-12-31"); echo "cleared\n";'
   ```

- [ ] **Step 6: Commit**

```bash
git add app/controllers/Booking.php
git commit -m "feat(booking): pre-flight package availability and return structured 422"
```

---

## Task 4: Render the unavailable list in the booking form

Make the submit handler show *which* services failed (and, in Phase 2, their alternative dates) instead of one generic toast.

**Files:**
- Modify: `app/views/booking/create.php` (submit handler `.then(data => …)` block at `create.php:2159-2168`)

**Interfaces:**
- Consumes: the 422 JSON contract (`data.unavailable` array). Existing JS helpers `showBookingReminder(lines, heading)` and `showToast(message, 'error')` (already used at `create.php:2164-2165`), and the `submitBtn` / `originalSubmitHtml` locals in scope.
- Produces: no new exports; renders `data.unavailable` to the user. Handles `alternatives` if present (forward-compatible with Phase 2 — no further frontend change needed for Phase 2).

- [ ] **Step 1: Replace the error branch of the submit handler**

In `app/views/booking/create.php`, replace the `else` branch (`create.php:2162-2168`):

```js
        } else {
          const error = data.error || 'Something went wrong. Please try again.';
          showBookingReminder([error], 'Please fix this before proceeding.');
          showToast(error, 'error');
          submitBtn.disabled = false;
          submitBtn.innerHTML = originalSubmitHtml;
        }
```

with:

```js
        } else if (Array.isArray(data.unavailable) && data.unavailable.length) {
          const lines = data.unavailable.map(u => {
            let line = (u.service_name || 'A package service') + ': ' + (u.message || 'not available');
            if (Array.isArray(u.alternatives) && u.alternatives.length) {
              const dates = u.alternatives.map(a => a.label || a.date).join(', ');
              line += ' — try ' + dates;
            }
            return line;
          });
          showBookingReminder(lines, "These package services aren't available on your date:");
          showToast(lines[0], 'error');
          submitBtn.disabled = false;
          submitBtn.innerHTML = originalSubmitHtml;
        } else {
          const error = data.error || 'Something went wrong. Please try again.';
          showBookingReminder([error], 'Please fix this before proceeding.');
          showToast(error, 'error');
          submitBtn.disabled = false;
          submitBtn.innerHTML = originalSubmitHtml;
        }
```

- [ ] **Step 2: Manual verification in the browser**

1. Force a slot full and book it (same steps as Task 3 Step 5, steps 1–3).
2. Expected: the booking reminder banner lists the service by name with its message (e.g. *"Wedding Photographer: No package slots available for this time"*), and the submit button re-enables. No redirect, no generic "Something went wrong".
3. Teardown the forced slot (Task 3 Step 5, step 5).

- [ ] **Step 3: Commit**

```bash
git add app/views/booking/create.php
git commit -m "feat(booking): show which package services are unavailable on submit"
```

---

**Phase 1 complete.** Booking-time conflicts now report *which* services failed and *all* of them at once, as a structured 422 rendered in the form.

---

# PHASE 2 — Suggest alternative dates (date-based Option 3)

Package service times are **auto-arranged** from `service_schedules` / defaults per the chosen date (`CartModel.php:596-599`), and the day-of-week changes those times. So the only customer-controllable lever is the **date**, and alternatives must be *other dates* — recomputing availability per candidate date. The cheapest correct way is to re-run `getPackageEventSchedule()` for each candidate date rather than hand-roll a slot query.

## Task 5: `CartModel::findAlternativePackageDates()`

**Files:**
- Modify: `app/models/CartModel.php` (add after `getUnavailablePackageServices()` from Task 1)
- Create: `database/verify_alternative_dates.php`

**Interfaces:**
- Consumes: `CartModel::getPackageEventSchedule(int $packageId, string $eventDate): array` (existing); `DateTimeImmutable` (already used in `CartModel.php`, e.g. `getPackageEventSchedule` at `CartModel.php:535`).
- Produces: `CartModel::findAlternativePackageDates(int $packageId, int $serviceId, string $fromDate, int $maxResults = 3, int $horizonDays = 60): array` — returns `array<int,array{date:string,label:string}>` of upcoming dates (after `$fromDate`, within `$horizonDays`) where that service is available, newest-first up to `$maxResults`. Empty array if none.

- [ ] **Step 1: Write the failing verify script**

Create `database/verify_alternative_dates.php`:

```php
<?php
require __DIR__ . '/_verify_bootstrap.php';

$f = findSlotPackageService();
if (!$f) { echo "SKIP: no slot-type package service in DB\n"; exit(0); }
[$packageId, $serviceId, $name] = $f;
$cart = new CartModel();
$from = '2027-12-31';

// Make 'from' full but leave the next few days open (no slot rows = available).
forceSlotFull($cart, $packageId, $serviceId, $from);
foreach (['2028-01-01','2028-01-02','2028-01-03','2028-01-04'] as $d) { clearSlot($serviceId, $d); }

$alts = $cart->findAlternativePackageDates($packageId, $serviceId, $from, 3, 30);
check(count($alts) >= 1,                                            "found at least one alternative date");
check($alts && preg_match('/^\d{4}-\d{2}-\d{2}$/', $alts[0]['date'] ?? ''), "alternative carries an ISO date");
check($alts && !empty($alts[0]['label']),                          "alternative carries a display label");
check($alts && $alts[0]['date'] !== $from,                         "alternative is not the full 'from' date");

clearSlot($serviceId, $from);   // teardown
verifyDone();
```

- [ ] **Step 2: Run it to verify it fails**

Run: `php database/verify_alternative_dates.php`
Expected: PHP fatal error `Call to undefined method CartModel::findAlternativePackageDates()`.

- [ ] **Step 3: Implement the method**

In `app/models/CartModel.php`, add after `getUnavailablePackageServices()`:

```php
    /**
     * Suggest upcoming dates on which a specific package service is available,
     * for when the customer's chosen date is full. Re-runs the package schedule
     * per candidate date because auto-resolved times shift with day-of-week.
     *
     * @return array<int,array{date:string,label:string}>
     */
    public function findAlternativePackageDates(
        int $packageId,
        int $serviceId,
        string $fromDate,
        int $maxResults = 3,
        int $horizonDays = 60
    ): array {
        $alternatives = [];
        $start = DateTimeImmutable::createFromFormat('!Y-m-d', $fromDate);
        if (!$start || $packageId <= 0 || $serviceId <= 0) {
            return $alternatives;
        }
        for ($offset = 1; $offset <= $horizonDays && count($alternatives) < $maxResults; $offset++) {
            $candidate = $start->modify('+' . $offset . ' days');
            $candidateStr = $candidate->format('Y-m-d');
            foreach ($this->getPackageEventSchedule($packageId, $candidateStr) as $row) {
                if ((int)($row['service_id'] ?? 0) !== $serviceId) {
                    continue;
                }
                if (($row['booking_type'] ?? '') === 'slot' && !empty($row['is_available'])) {
                    $alternatives[] = [
                        'date'  => $candidateStr,
                        'label' => $candidate->format('D, M j'),
                    ];
                }
                break; // this service appears once per schedule
            }
        }
        return $alternatives;
    }
```

- [ ] **Step 4: Run it to verify it passes**

Run: `php database/verify_alternative_dates.php`
Expected: four `PASS:` lines then `=== ALL PASS ===` (exit 0).

- [ ] **Step 5: Commit**

```bash
git add app/models/CartModel.php database/verify_alternative_dates.php
git commit -m "feat(booking): suggest alternative dates for unavailable package services"
```

---

## Task 6: Attach alternatives to the 422 response

Populate the `alternatives` field on each unavailable entry, in both the pre-flight and the race-backstop paths. The client renderer (Task 4) already displays them, so there is **no frontend change** in this task.

**Files:**
- Modify: `app/controllers/Booking.php` (pre-flight loop and backstop throw added in Task 3)

**Interfaces:**
- Consumes: `CartModel::findAlternativePackageDates()` (Task 5); the pre-flight loop and `SlotUnavailableException` throw from Task 3.
- Produces: each `unavailable[]` entry now includes `alternatives: array<{date,label}>` (possibly empty).

- [ ] **Step 1: Attach alternatives in the pre-flight loop**

In `app/controllers/Booking.php`, in the pre-flight block added in Task 3 Step 1, replace the inner collection loop:

```php
            foreach ($this->cartModel->getUnavailablePackageServices((int)($item['item_id'] ?? 0), $pkgDate) as $u) {
                $unavailable[] = $u;
            }
```

with:

```php
            foreach ($this->cartModel->getUnavailablePackageServices((int)($item['item_id'] ?? 0), $pkgDate) as $u) {
                $u['alternatives'] = $this->cartModel->findAlternativePackageDates(
                    (int)($item['item_id'] ?? 0),
                    (int)$u['service_id'],
                    $pkgDate
                );
                $unavailable[] = $u;
            }
```

- [ ] **Step 2: Attach alternatives in the backstop throw**

In `app/controllers/Booking.php`, replace the reserve-failure block from Task 3 Step 2:

```php
                    if ($this->bookingModel->reservePackageServiceSlots($bookingId, $pkgDate, $packageSchedule) === false) {
                        $fail = $this->bookingModel->getLastUnavailableService();
                        throw new SlotUnavailableException($fail ? [$fail] : []);
                    }
```

with:

```php
                    if ($this->bookingModel->reservePackageServiceSlots($bookingId, $pkgDate, $packageSchedule) === false) {
                        $fail = $this->bookingModel->getLastUnavailableService();
                        if ($fail) {
                            $fail['alternatives'] = $this->cartModel->findAlternativePackageDates(
                                (int)($item['item_id'] ?? 0),
                                (int)$fail['service_id'],
                                $pkgDate
                            );
                        }
                        throw new SlotUnavailableException($fail ? [$fail] : []);
                    }
```

- [ ] **Step 3: Syntax-check the controller**

Run: `php -l app/controllers/Booking.php`
Expected: `No syntax errors detected in app/controllers/Booking.php`

- [ ] **Step 4: Manual end-to-end verification**

1. Force a slot full on `2027-12-31` (Task 3 Step 5, step 1) and ensure the days after it have no slot rows (they are available by default).
2. As a customer, book that package for `2027-12-31`.
3. Expected: the reminder banner shows the service name, its message, **and** "try Sat, Jan 1, …" with up to three upcoming dates. Network tab shows `unavailable[0].alternatives` populated.
4. Teardown the forced slot (Task 3 Step 5, step 5).

- [ ] **Step 5: Commit**

```bash
git add app/controllers/Booking.php
git commit -m "feat(booking): include alternative dates in unavailable-service response"
```

---

**Phase 2 complete.** Conflicts now suggest the next available dates for each blocked service.

---

## Notes / Out of Scope

- **Custom (non-package) service path:** `reserveBookingItemSlots()` (`BookingModel.php:510-536`) used by single-service bookings (`Booking.php:350`) still throws a generic message. Naming the service there is a small follow-on using the same `getLastUnavailableService` pattern — not included here to keep the change package-focused.
- **Soft holds / reservation locks** (the earlier "Option 4") and **partial booking / replacement-at-booking** ("Option 5") are deliberately excluded; they are larger product decisions.
- The pre-flight check narrows the race window but does not close it — the reserve-time UPDATE (`BookingModel.php:651-680`) remains the source of truth, and Task 2's backstop handles the residual race.
- The `verify_*.php` scripts are dev sanity tools (same spirit as `database/seed_*.php`); they self-clean their fixtures and `SKIP` when no slot-type package service exists.
