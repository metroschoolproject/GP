<?php
/**
 * Make seeded services pass the customer catalog visibility gate
 * (publishedServiceReadyCondition): non-empty name+description, price > 0,
 * a thumbnail/media image, an available schedule, and — for venues — a
 * venue_rooms row.
 *
 * Touches only seeded services (supplier_id >= 23). Idempotent.
 * Run:  php database/seed_make_visible.php
 */
require __DIR__ . '/../app/config/config.php';

$db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$HERO = [
    URLROOT . '/public/uploads/serviceHero1.png',
    URLROOT . '/public/uploads/serviceHero2.png',
    URLROOT . '/public/uploads/serviceHero3.png',
];

// default price per category when missing/zero
$DEFAULT_PRICE = [
    2 => 500000,   // Attire
    3 => 20000,    // Food
    5 => 400000,   // Studio
    6 => 800000,   // Venue
    8 => 50000,    // Invitation & Gifts
    9 => 1000000,  // Jewelry
    10 => 150000,  // Make Up & Hair
    11 => 100000,  // Car
    12 => 500000,  // Decoration
];

$rows = $db->query(
    'SELECT id, supplier_id, category_id, name, price, thumbnail_url
       FROM services WHERE supplier_id >= 23 ORDER BY id'
)->fetchAll(PDO::FETCH_ASSOC);

$setThumb = $db->prepare('UPDATE services SET thumbnail_url = :t WHERE id = :id');
$setPrice = $db->prepare('UPDATE services SET price = :p WHERE id = :id');
$venueExists = $db->prepare('SELECT 1 FROM venues WHERE service_id = :sid LIMIT 1');
$insVenue = $db->prepare(
    'INSERT INTO venues (supplier_id, service_id, name, location, description, created_at)
     VALUES (:sup, :svc, :name, :loc, :desc, NOW())'
);
$insRoom = $db->prepare(
    'INSERT INTO venue_rooms (venue_id, name, capacity, price, min_lead_days, created_at)
     VALUES (:vid, :name, :cap, :price, 0, NOW())'
);

$thumbed = 0; $priced = 0; $venued = 0; $i = 0;
foreach ($rows as $r) {
    $id  = (int)$r['id'];
    $cat = (int)$r['category_id'];

    if (trim((string)$r['thumbnail_url']) === '') {
        $setThumb->execute([':t' => $HERO[$i % 3], ':id' => $id]);
        $thumbed++;
    }
    $i++;

    $price = (float)$r['price'];
    if ($price <= 0) {
        $price = $DEFAULT_PRICE[$cat] ?? 100000;
        $setPrice->execute([':p' => $price, ':id' => $id]);
        $priced++;
    }

    if ($cat === 6) { // Venue needs venues + venue_rooms
        $venueExists->execute([':sid' => $id]);
        if (!$venueExists->fetchColumn()) {
            $insVenue->execute([
                ':sup'  => (int)$r['supplier_id'],
                ':svc'  => $id,
                ':name' => $r['name'],
                ':loc'  => 'Yangon',
                ':desc' => 'Wedding venue',
            ]);
            $vid = (int)$db->lastInsertId();
            $insRoom->execute([
                ':vid'   => $vid,
                ':name'  => 'Main Hall',
                ':cap'   => 300,
                ':price' => $price > 0 ? $price : 800000,
            ]);
            $venued++;
        }
    }
}

echo "thumbnails set: $thumbed\nprices defaulted: $priced\nvenue rooms created: $venued\n";
