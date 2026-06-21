<?php
/**
 * Seed: Dress (Attire) shops from requireData -> suppliers + Attire services + schedules.
 * Source: requireData Dress table (collected by Aye Nanda). Category 2 = Attire.
 * Idempotent: skips any shop whose supplier shop_name already exists.
 * Run:  php database/seed_dress_shops.php
 */
require __DIR__ . '/../app/config/config.php';

$db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

const ATTIRE_CATEGORY_ID = 2;

// price = representative service price (low end of the rental range); full range kept in description.
// closed = weekday to skip in the schedule (1=Mon .. 7=Sun), or null for open all week.
$shops = [
    [
        'shop'    => 'မင်္ဂလာဦး သတို့သား၊သတို့သမီး ဝတ်စုံနှင့်လက်ဝတ်ရတနာ',
        'contact' => 'No.991, Thu Mingalar Road, Thingangyun Township, Yangon. Tel 09 250 500 809',
        'price'   => 750000,
        'desc'    => 'Traditional Myanmar bridal wear — htaing-ma-theim, offering/registration outfits, taik-pon and taung-shay for the couple and parents, in various silk weaves. Rental and sale, plus custom-made rental (book 3-6 months ahead). Htaing-ma-theim rental approx 350,000 to 2,000,000, offering outfits from approx 200,000. Add-ons: floral decoration, hand bouquets, hotel/makeup booking and wedding car decoration.',
        'verify'  => null,
        'closed'  => null,
    ],
    [
        'shop'    => 'Dear Brides Wedding Dress Studio',
        'contact' => 'Karaweik Garden, near Myaw Sin Kyun entry, Mingalar Taung Nyunt, Yangon. Tel 09 771471462. Open 10:00-18:00 daily',
        'price'   => 800000,
        'desc'    => 'Western and traditional bridal wear — wedding gowns, mermaid dresses, evening dresses and pre-wedding outfits. Latest imported designs for rent or sale, customised bridal veils, and custom-made rental. Spacious studio with parking, in-house photo studio and experienced stylists. Range approx 800,000 to 3,000,000 depending on dress.',
        'verify'  => null,
        'closed'  => null,
    ],
    [
        'shop'    => 'The Vow Wedding Studio Myanmar',
        'contact' => 'No.789, 47 ward, Bohmu Ba Htoo Road, North Dagon, Yangon. Tel 09 451355553, 09 791580503. Open 09:00-17:00',
        'price'   => 1500000,
        'desc'    => 'Women\'s bridal studio with finely tailored gowns, quality fabrics and detailed finishing for each bride. Rental and sale; crowns and bridal shoes also available. Event-day rental approx 1,500,000 to 6,000,000+.',
        'verify'  => null,
        'closed'  => null,
    ],
    [
        'shop'    => 'ရွှေဖူးစာ မင်္ဂလာဝတ်စုံ YGN',
        'contact' => 'Thu Mingalar main road (between Sa Taik and Inn Wa bus stops), South Okkalapa, Yangon — above Khit Pyaing toy shop, next to CB Bank. Tel 09 777775512',
        'price'   => 200000,
        'desc'    => 'Wedding suits and dresses for men and women. Reliable remote/line ordering with good fit. Price approx 200,000 to 500,000+. Booking required.',
        'verify'  => null,
        'closed'  => null,
    ],
    [
        'shop'    => 'T&T Bridal Collection',
        'contact' => 'No.666, Thudamar Road (near Eaindra bus stop), North Okkalapa, Yangon. Tel 09 799515633, 09 799515622. Open 10:00-17:30, closed Wednesdays',
        'price'   => 400000,
        'desc'    => 'Western wedding dresses with hundreds of new pieces. Rental approx 400,000 to 1,500,000; wholesale purchase from 230,000. 10+ years wedding-industry founder advises on current trends, body-fit styling, matching makeup look and accessories. New stock monthly plus resale of older pieces.',
        'verify'  => null,
        'closed'  => 3,
    ],
    [
        'shop'    => 'ဂုဏ် တိုက်ပုံ နှင့် ပုဆိုး',
        'contact' => 'No.293, Brahmaso 4/6 Street, South Okkalapa Township, Yangon. Tel 09 422999929, 09 985808800',
        'price'   => 300000,
        'desc'    => 'Men\'s ceremony wear — "Gon" taik-pon (M/L/XL/XXL) at 300,000 and pasoe (longyi) from 43,000 to 420,000. Detailed sizing help and sharp cutting for a smart, dignified look.',
        'verify'  => null,
        'closed'  => null,
    ],
    [
        'shop'    => 'Peter\'s Bridal Garden - Studio',
        'contact' => 'No.542, Ou Zanar Street, ward 11, Mya Thidar Housing, South Okkalapa, Yangon. Tel 09 777 595010',
        'price'   => null,
        'desc'    => 'Pre-wedding outfit and photography studio. Indoor and outdoor pre-wedding packages (e.g. 3-outfit indoor package), traditional looks, makeup and full-team support with raw photos provided. Highly recommended for pre-wedding shoots.',
        'verify'  => 'peterbridalgarden@gmail.com',
        'closed'  => null,
    ],
    [
        'shop'    => 'My Everything Wedding Dresses',
        'contact' => 'No.1253, 13 ward, Ratana main road, South Okkalapa Township, Yangon. Tel 09 776040862, 09 760396053. Open 09:00-17:00',
        'price'   => 480000,
        'desc'    => 'Bridal dress rental for brides. Rental price range approx 480,000 to 1,860,000. Rental only.',
        'verify'  => null,
        'closed'  => null,
    ],
];

$findSupplier = $db->prepare('SELECT supplier_id FROM suppliers WHERE shop_name = :n LIMIT 1');
$insSupplier  = $db->prepare(
    'INSERT INTO suppliers (shop_name, description, status, payment_status, is_available, agreement_accepted, agreement_version, verify_url, created_at)
     VALUES (:shop, :desc, "approved", "paid", 1, 1, "supplier-v1", :verify, NOW())'
);
$insService = $db->prepare(
    'INSERT INTO services (supplier_id, category_id, name, description, price, is_active, booking_type, max_concurrent, max_concurrent_package, created_at)
     VALUES (:sid, :cat, :name, :desc, :price, 1, "fullday", 2, 1, NOW())'
);
$insSchedule = $db->prepare(
    'INSERT INTO service_schedules (service_id, day_of_week, open_time, close_time, is_available)
     VALUES (:svc, :dow, "10:00:00", "18:00:00", 1)'
);

$created = 0; $skipped = 0;
foreach ($shops as $s) {
    $findSupplier->execute([':n' => $s['shop']]);
    if ($findSupplier->fetchColumn()) {
        echo "skip (exists): {$s['shop']}\n";
        $skipped++;
        continue;
    }
    $db->beginTransaction();
    try {
        $insSupplier->execute([
            ':shop'   => $s['shop'],
            ':desc'   => $s['contact'] . "\n\n" . $s['desc'],
            ':verify' => $s['verify'],
        ]);
        $sid = (int)$db->lastInsertId();

        $insService->execute([
            ':sid'   => $sid,
            ':cat'   => ATTIRE_CATEGORY_ID,
            ':name'  => $s['shop'] . ' - Wedding Attire',
            ':desc'  => $s['desc'],
            ':price' => $s['price'],
        ]);
        $svc = (int)$db->lastInsertId();

        for ($d = 1; $d <= 7; $d++) {
            if ($s['closed'] !== null && $d === $s['closed']) {
                continue;
            }
            $insSchedule->execute([':svc' => $svc, ':dow' => $d]);
        }
        $db->commit();
        echo "created: supplier #{$sid} / service #{$svc}  {$s['shop']}  price=" . ($s['price'] ?? 'NULL') . "\n";
        $created++;
    } catch (Exception $e) {
        $db->rollBack();
        echo "FAIL {$s['shop']}: " . $e->getMessage() . "\n";
    }
}
echo "\nDone. created={$created} skipped={$skipped}\n";
