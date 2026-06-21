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
 * Creates the slot row with capacity 1 already consumed (both the total pool
 * and the package pool), so availability is exhausted regardless of the
 * service's real max_concurrent — which can exceed the slot counter column's
 * UNSIGNED range for high-capacity venues. The slot row's own caps govern
 * getPackageServiceSlotAvailability(), so capacity 1 reliably reads as full.
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
