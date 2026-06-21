<?php
/**
 * Seed every populated requireData "Categories Breakdown" table into
 * suppliers + one category service + a 7-day schedule.
 *
 * - Category is read from each file's "Datas:" label and mapped to category_id.
 * - Markdown rows may span multiple physical lines (cells contain newlines);
 *   a row is assembled by accumulating lines until the pipe count completes.
 * - Idempotent: a shop whose supplier shop_name already exists is skipped.
 * - Best-effort price parsing (lakh / k / USD / plain / Burmese numerals);
 *   the full price text and description are always kept on the service.
 *
 * Run:  php database/seed_all_categories.php
 */
require __DIR__ . '/../app/config/config.php';

$db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$DIR = __DIR__ . '/../requireData/Categories Breakdown';

// Datas: label  ->  [category_id, service type word]
$CAT = [
    'Studio'              => [5,  'Studio'],
    'Place'               => [6,  'Venue'],
    'Jewelry'             => [9,  'Jewelry'],
    'Dress'               => [2,  'Attire'],
    'Cake'                => [3,  'Cake'],
    'Food & Drinks'       => [3,  'Catering'],
    'Decoration'          => [12, 'Decoration'],
    'Car Rental'          => [11, 'Car Rental'],
    'Invitation and Gifts'=> [8,  'Invitation & Gifts'],
    'Makeup & Hair'       => [10, 'Makeup & Hair'],
];

function burmeseToAscii(string $s): string {
    return strtr($s, ['၀'=>'0','၁'=>'1','၂'=>'2','၃'=>'3','၄'=>'4','၅'=>'5','၆'=>'6','၇'=>'7','၈'=>'8','၉'=>'9']);
}

function parsePrice(?string $raw): ?int {
    if ($raw === null) return null;
    $s = burmeseToAscii($raw);
    $s = preg_replace('/\s+/u', ' ', $s);
    if (preg_match('/([\d.,]+)\s*သိန်း/u', $s, $m)) return (int)round((float)str_replace(',', '', $m[1]) * 100000);
    if (preg_match('/([\d.,]+)\s*[kK]\b/u', $s, $m)) return (int)round((float)str_replace(',', '', $m[1]) * 1000);
    if (preg_match('/\$\s*([\d.,]+)/', $s, $m))        return (int)round((float)str_replace(',', '', $m[1]) * 4500);
    if (preg_match('/(\d{1,3}(?:,\d{3})+|\d{5,})/', $s, $m)) return (int)str_replace(',', '', $m[1]);
    return null;
}

/** Assemble multi-line markdown rows into arrays of trimmed cells. */
function parseTable(string $content): array {
    $lines = explode("\n", $content);
    $headerIdx = -1;
    for ($i = 0; $i < count($lines) - 1; $i++) {
        if (str_contains($lines[$i], '|') && preg_match('/^\s*\|?\s*-{2,}/', $lines[$i + 1])) {
            $headerIdx = $i; break;
        }
    }
    if ($headerIdx < 0) return [[], []];

    $header = array_values(array_map('trim', array_filter(explode('|', $lines[$headerIdx]), fn($c) => trim($c) !== '')));
    $cols   = count($header);

    $rows = [];
    $buf = '';
    for ($i = $headerIdx + 2; $i < count($lines); $i++) {
        $line = $lines[$i];
        if ($buf === '' && trim($line) === '') continue;
        $buf .= ($buf === '' ? '' : "\n") . $line;
        if (rtrim($buf) !== '' && str_ends_with(rtrim($buf), '|') && substr_count($buf, '|') >= $cols + 1) {
            $cells = explode('|', rtrim($buf));
            array_shift($cells);            // drop leading empty (before first |)
            array_pop($cells);              // drop trailing empty (after last |)
            $rows[] = array_map('trim', $cells);
            $buf = '';
        }
    }
    return [$header, $rows];
}

function colIndex(array $header, array $needles): ?int {
    foreach ($header as $i => $h) {
        $hl = strtolower($h);
        foreach ($needles as $n) if (str_contains($hl, $n)) return $i;
    }
    return null;
}

$findSupplier = $db->prepare('SELECT supplier_id FROM suppliers WHERE shop_name = :n LIMIT 1');
$insSupplier  = $db->prepare(
    'INSERT INTO suppliers (shop_name, description, status, payment_status, is_available, agreement_accepted, agreement_version, created_at)
     VALUES (:shop, :desc, "approved", "paid", 1, 1, "supplier-v1", NOW())'
);
$insService = $db->prepare(
    'INSERT INTO services (supplier_id, category_id, name, description, price, is_active, booking_type, max_concurrent, max_concurrent_package, created_at)
     VALUES (:sid, :cat, :name, :desc, :price, 1, "fullday", 2, 1, NOW())'
);
$insSchedule = $db->prepare(
    'INSERT INTO service_schedules (service_id, day_of_week, open_time, close_time, is_available)
     VALUES (:svc, :dow, "09:00:00", "18:00:00", 1)'
);

$totalCreated = 0; $totalSkipped = 0;
foreach (glob($DIR . '/*.md') as $file) {
    $content = file_get_contents($file);
    if (!preg_match('/Datas:\s*([^(]+?)\s*\(/', $content, $m)) continue;
    $label = trim($m[1]);
    // Dress is seeded by the curated seed_dress_shops.php (cleaner names);
    // skip here so re-runs don't create "(recommend)"-suffixed duplicates.
    if ($label === 'Dress') { echo "-- skip Dress (handled by seed_dress_shops.php): " . basename($file) . "\n"; continue; }
    if (!isset($CAT[$label])) { echo "-- skip file (unmapped category '$label'): " . basename($file) . "\n"; continue; }
    [$catId, $typeWord] = $CAT[$label];

    [$header, $rows] = parseTable($content);
    if (!$rows) { echo "-- no rows: " . basename($file) . "\n"; continue; }

    $nameI    = colIndex($header, ['shop name', 'name']) ?? 0;
    $contactI = colIndex($header, ['place', 'contact']);
    $priceI   = colIndex($header, ['price']);
    $descI    = colIndex($header, ['description', 'describ']);
    $svcI     = colIndex($header, ['other service', 'service']);

    echo "\n=== $label (cat $catId) — " . basename($file) . " — " . count($rows) . " rows ===\n";
    $created = 0; $skipped = 0;
    foreach ($rows as $r) {
        $name = trim(preg_replace('/\*\*/', '', $r[$nameI] ?? ''));
        $name = trim(preg_replace('/\s+/u', ' ', $name));
        if ($name === '') { continue; }

        $findSupplier->execute([':n' => $name]);
        if ($findSupplier->fetchColumn()) { echo "  skip (exists): $name\n"; $skipped++; continue; }

        $contact = $contactI !== null ? ($r[$contactI] ?? '') : '';
        $priceTx = $priceI   !== null ? ($r[$priceI]   ?? '') : '';
        $descTx  = $descI    !== null ? ($r[$descI]    ?? '') : '';
        $svcTx   = $svcI !== null && $svcI !== $descI ? ($r[$svcI] ?? '') : '';
        $price   = parsePrice($priceTx);

        $supDesc = trim($contact);
        $svcDesc = trim($descTx . ($svcTx ? "\n\nOther services: " . $svcTx : '') . ($priceTx ? "\n\nPricing: " . $priceTx : ''));
        if ($svcDesc === '') $svcDesc = $name;

        $db->beginTransaction();
        try {
            $insSupplier->execute([':shop' => $name, ':desc' => $supDesc !== '' ? $supDesc : $name]);
            $sid = (int)$db->lastInsertId();
            $insService->execute([
                ':sid' => $sid, ':cat' => $catId,
                ':name' => $name . ' - ' . $typeWord,
                ':desc' => $svcDesc, ':price' => $price,
            ]);
            $svc = (int)$db->lastInsertId();
            for ($d = 1; $d <= 7; $d++) $insSchedule->execute([':svc' => $svc, ':dow' => $d]);
            $db->commit();
            echo "  created sup#$sid svc#$svc  price=" . ($price ?? 'NULL') . "  $name\n";
            $created++;
        } catch (Exception $e) {
            $db->rollBack();
            echo "  FAIL $name: " . $e->getMessage() . "\n";
        }
    }
    echo "  -> created=$created skipped=$skipped\n";
    $totalCreated += $created; $totalSkipped += $skipped;
}
echo "\n==== TOTAL created=$totalCreated skipped=$totalSkipped ====\n";
