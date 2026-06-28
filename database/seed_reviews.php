<?php
/**
 * Seed realistic COMPLETED bookings and the reviews left by those customers.
 *
 * For each seeded service (supplier_id >= 23) we create 2-3 completed bookings,
 * one per reviewing customer, each with a full lifecycle row set:
 *   bookings (status=completed, paid) + event_details (past wedding date)
 *   + booking_items (completed) + booking_suppliers (completed)
 *   + one review by that booking's customer.
 *
 * So every review traces to a genuine completed booking by that customer.
 * Idempotent: a service that already has reviews is skipped.
 *
 * Run:  php database/seed_reviews.php
 */
require __DIR__ . '/../app/config/config.php';

$db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$customers = $db->query('SELECT user_id FROM user_roles WHERE role_id = 1 ORDER BY user_id')->fetchAll(PDO::FETCH_COLUMN);
if (!$customers) { exit("No customer-role users.\n"); }

// cart per customer
$cartOf = [];
foreach ($customers as $uid) {
    $cid = $db->query('SELECT id FROM carts WHERE user_id = ' . (int)$uid . ' LIMIT 1')->fetchColumn();
    if (!$cid) {
        $db->prepare('INSERT INTO carts (user_id, created_at) VALUES (:u, NOW())')->execute([':u' => $uid]);
        $cid = (int)$db->lastInsertId();
    }
    $cartOf[$uid] = (int)$cid;
}

$pastDates = ['2026-03-15', '2026-04-12', '2026-04-26', '2026-05-10', '2026-05-24', '2026-06-07'];
$comments = [
    'Beautiful work and very friendly service. Highly recommend!',
    'အရမ်းကျေနပ်ပါတယ်။ ဝန်ဆောင်မှုကောင်းပြီး ဝန်ထမ်းတွေ စိတ်ရှည်ပါတယ်။',
    'Everything was perfect on our wedding day. Thank you so much!',
    'Professional team, fair price, lovely result. ⭐⭐⭐⭐⭐',
    'ဈေးနှုန်းသင့်တင့်ပြီး အရည်အသွေးကောင်းပါတယ်။ ထပ်အားပေးဖြစ်မှာပါ။',
    'Lovely experience, would book again for sure.',
    'Good service and right on time. Recommended for weddings.',
    'စိတ်တိုင်းကျပါတယ်။ ကျေးဇူးတင်ပါတယ်ရှင့်။',
];
$ratings = [5, 5, 4, 5, 4];

$services = $db->query(
    'SELECT s.id, s.supplier_id, s.category_id, s.name, s.price, c.name AS category, sup.shop_name
       FROM services s
       JOIN suppliers sup ON sup.supplier_id = s.supplier_id
       LEFT JOIN categories c ON c.id = s.category_id
      WHERE s.supplier_id >= 23
        AND NOT EXISTS (SELECT 1 FROM reviews r WHERE r.service_id = s.id)
      ORDER BY s.id'
)->fetchAll(PDO::FETCH_ASSOC);

$insBooking = $db->prepare(
    'INSERT INTO bookings (user_id, cart_id, total_amount, paid_amount, payment_status, status, approved_by, approved_at, created_at)
     VALUES (:u, :cart, :tot, :paid, "paid", "completed", 1, NOW(), NOW())'
);
$insItem = $db->prepare(
    'INSERT INTO booking_items (booking_id, item_type, source, item_id, price, item_name, supplier_name, category_name, status, booking_type, booking_date)
     VALUES (:bid, "service", "custom", :iid, :price, :iname, :sname, :cname, "completed", "fullday", :bdate)'
);
$insEvent = $db->prepare(
    'INSERT INTO event_details (booking_id, booking_item_id, event_date, start_time, end_time, guest_count, venue_type, location, contact_name, created_at)
     VALUES (:bid, :biid, :edate, "18:00:00", "22:00:00", :guests, "both", "Yangon", :contact, NOW())'
);
$insBS = $db->prepare(
    'INSERT INTO booking_suppliers (booking_id, supplier_id, service_id, category_id, item_price, status, confirmed_at, completed_at, created_at)
     VALUES (:bid, :sup, :svc, :cat, :price, "completed", NOW(), NOW(), NOW())'
);
$insReview = $db->prepare(
    'INSERT INTO reviews (booking_id, booking_item_id, service_id, customer_id, supplier_id, rating, comment, created_at)
     VALUES (:bid, :biid, :svc, :cust, :sup, :rate, :comment, NOW())'
);

$bookingCount = 0; $reviewCount = 0; $svcCount = 0; $ci = 0;
foreach ($services as $idx => $s) {
    $price = max(10000, (float)$s['price']);
    $n = 2 + ($idx % 2); // 2-3 completed bookings (one per reviewer)

    for ($j = 0; $j < $n; $j++) {
        $owner = $customers[($idx + $j) % count($customers)];
        $date  = $pastDates[($idx + $j) % count($pastDates)];
        $db->beginTransaction();
        try {
            $insBooking->execute([':u' => $owner, ':cart' => $cartOf[$owner], ':tot' => $price, ':paid' => $price]);
            $bid = (int)$db->lastInsertId();
            $insItem->execute([
                ':bid' => $bid, ':iid' => $s['id'], ':price' => $price,
                ':iname' => $s['name'], ':sname' => $s['shop_name'], ':cname' => $s['category'], ':bdate' => $date . ' 18:00:00',
            ]);
            $biid = (int)$db->lastInsertId();
            $insEvent->execute([':bid' => $bid, ':biid' => $biid, ':edate' => $date, ':guests' => 150 + (($idx + $j) % 6) * 50, ':contact' => 'Customer ' . $owner]);
            $insBS->execute([
                ':bid' => $bid, ':sup' => $s['supplier_id'], ':svc' => $s['id'],
                ':cat' => $s['category_id'], ':price' => $price,
            ]);
            $insReview->execute([
                ':bid' => $bid, ':biid' => $biid, ':svc' => $s['id'], ':cust' => $owner, ':sup' => $s['supplier_id'],
                ':rate' => $ratings[($idx + $j) % count($ratings)], ':comment' => $comments[$ci % count($comments)],
            ]);
            $db->commit();
            $bookingCount++; $reviewCount++; $ci++;
        } catch (Exception $e) {
            $db->rollBack();
            echo "FAIL svc#{$s['id']}: " . $e->getMessage() . "\n";
        }
    }
    $svcCount++;
}

echo "completed bookings created: $bookingCount\nreviews created: $reviewCount across $svcCount services\n";
