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
