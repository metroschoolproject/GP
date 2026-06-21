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
