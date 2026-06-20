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
