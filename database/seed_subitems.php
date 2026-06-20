<?php
/**
 * Seed per-service sub-items so detail pages have real structure:
 *   - Attire (cat 2)      -> attire_items   (per-dress borrow/buy pricing)
 *   - Venue (cat 6)       -> venue_rooms    (named halls w/ capacity + price)
 *   - Decoration (cat 12) -> decoration_styles
 *
 * requireData has no structured per-item data, so items are representative,
 * scaled from each service's price. Touches seeded services only (supplier_id >= 23).
 * Idempotent: skips a service that already has its sub-items.
 *
 * Run:  php database/seed_subitems.php
 */
require __DIR__ . '/../app/config/config.php';

$db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$HERO = URLROOT . '/public/uploads/serviceHero';
$hero = fn(int $n) => $HERO . (($n % 3) + 1) . '.png';
$m = fn($v) => (int)round($v);

/* ───────────── Attire -> attire_items ───────────── */
$attireSvcs = $db->query(
    'SELECT s.id, s.price, s.name FROM services s
       WHERE s.category_id = 2 AND s.supplier_id >= 23
         AND NOT EXISTS (SELECT 1 FROM attire_items ai WHERE ai.service_id = s.id)'
)->fetchAll(PDO::FETCH_ASSOC);

$insAttire = $db->prepare(
    'INSERT INTO attire_items (service_id, name, description, photo_url, borrow_package_price, borrow_customize_price, buy_package_price, buy_customize_price, return_days, sort_order, created_at)
     VALUES (:sid, :name, :desc, :photo, :bp, :bc, :yp, :yc, :ret, :sort, NOW())'
);
$attireCreated = 0;
foreach ($attireSvcs as $i => $s) {
    $p = max(150000, (float)$s['price']);
    $items = [
        ['Bridal Gown',                 $p,        $p * 1.3, $p * 3,   $p * 3.6, 3],
        ['Groom\'s Suit / Taik-pon',    $p * 0.55, $p * 0.8, $p * 2,   $p * 2.4, 3],
        ['Traditional Htaing-ma-theim Set', $p * 1.2, $p * 1.6, $p * 3.4, $p * 4, 5],
    ];
    foreach ($items as $k => $it) {
        $insAttire->execute([
            ':sid' => $s['id'], ':name' => $it[0],
            ':desc' => $it[0] . ' — rental and purchase available.',
            ':photo' => $hero($i + $k),
            ':bp' => $m($it[1]), ':bc' => $m($it[2]), ':yp' => $m($it[3]), ':yc' => $m($it[4]),
            ':ret' => $it[5], ':sort' => $k,
        ]);
    }
    $attireCreated += count($items);
}

/* ───────────── Venue -> venue_rooms (named halls) ───────────── */
// Replace the generic single "Main Hall" placeholder with real named halls.
$db->exec(
    'DELETE vr FROM venue_rooms vr
       JOIN venues v ON v.id = vr.venue_id
       JOIN services s ON s.id = v.service_id
      WHERE s.supplier_id >= 23 AND vr.name = "Main Hall"'
);
$venues = $db->query(
    'SELECT v.id AS venue_id, s.price, s.id AS service_id FROM venues v
       JOIN services s ON s.id = v.service_id
      WHERE s.supplier_id >= 23
        AND NOT EXISTS (SELECT 1 FROM venue_rooms vr WHERE vr.venue_id = v.id)'
)->fetchAll(PDO::FETCH_ASSOC);

$insRoom = $db->prepare(
    'INSERT INTO venue_rooms (venue_id, name, capacity, price, min_lead_days, photo_url, created_at)
     VALUES (:vid, :name, :cap, :price, 0, :photo, NOW())'
);
$roomCreated = 0;
foreach ($venues as $i => $v) {
    $p = max(300000, (float)$v['price']);
    $halls = [
        ['Grand Ballroom (Indoor)', 400, $p],
        ['Garden Lawn (Outdoor)',   250, $m($p * 0.8)],
    ];
    foreach ($halls as $k => $hh) {
        $insRoom->execute([
            ':vid' => $v['venue_id'], ':name' => $hh[0], ':cap' => $hh[1],
            ':price' => $m($hh[2]), ':photo' => $hero($i + $k),
        ]);
    }
    $roomCreated += count($halls);
}

/* ───────────── Decoration -> decoration_styles ───────────── */
$decoSvcs = $db->query(
    'SELECT s.id, s.price FROM services s
       WHERE s.category_id = 12 AND s.supplier_id >= 23
         AND NOT EXISTS (SELECT 1 FROM decoration_styles d WHERE d.service_id = s.id)'
)->fetchAll(PDO::FETCH_ASSOC);

$insStyle = $db->prepare(
    'INSERT INTO decoration_styles (service_id, name, price, package_price, customize_price, photo_url, sort_order, created_at)
     VALUES (:sid, :name, :price, :pkg, :cust, :photo, :sort, NOW())'
);
$styleCreated = 0;
foreach ($decoSvcs as $i => $s) {
    $p = max(200000, (float)$s['price']);
    $styles = [
        ['Classic Elegance',  $p],
        ['Floral Romance',    $m($p * 1.3)],
        ['Theme-based Custom', $m($p * 1.6)],
    ];
    foreach ($styles as $k => $st) {
        $insStyle->execute([
            ':sid' => $s['id'], ':name' => $st[0],
            ':price' => $m($st[1]), ':pkg' => $m($st[1]), ':cust' => $m($st[1] * 1.2),
            ':photo' => $hero($i + $k), ':sort' => $k,
        ]);
    }
    $styleCreated += count($styles);
}

echo "attire_items created: $attireCreated (for " . count($attireSvcs) . " dress services)\n";
echo "venue_rooms created:  $roomCreated (for " . count($venues) . " venues)\n";
echo "decoration_styles created: $styleCreated (for " . count($decoSvcs) . " services)\n";
