<?php
/**
 * Give every supplier row that lacks a login a real user account.
 * - Creates a users row (supplier role) and links suppliers.user_id.
 * - Password is the same test password for all seeded suppliers.
 * - Email: supplier<ID>@goldenpromise.test (unique), or the supplier's own
 *   email if one is embedded in its description.
 * - Idempotent: only touches suppliers whose user_id IS NULL.
 *
 * Login format matches User model: stored = password_hash(sha256(plain)).
 *
 * Run:  php database/seed_supplier_users.php
 */
require __DIR__ . '/../app/config/config.php';

const TEST_PASSWORD   = 'Aa12@3456';
const SUPPLIER_ROLE_ID = 2;

$db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$hash = password_hash(hash('sha256', TEST_PASSWORD), PASSWORD_DEFAULT);

$suppliers = $db->query(
    'SELECT supplier_id, shop_name, description FROM suppliers WHERE user_id IS NULL ORDER BY supplier_id'
)->fetchAll(PDO::FETCH_ASSOC);

$insUser  = $db->prepare(
    'INSERT INTO users (name, email, password, status, email_verified_at, created_at)
     VALUES (:name, :email, :pw, "active", NOW(), NOW())'
);
$insRole  = $db->prepare('INSERT INTO user_roles (user_id, role_id, created_at) VALUES (:uid, :rid, NOW())');
$linkSup  = $db->prepare('UPDATE suppliers SET user_id = :uid WHERE supplier_id = :sid');
$emailTaken = $db->prepare('SELECT 1 FROM users WHERE email = :e LIMIT 1');

$created = 0;
foreach ($suppliers as $s) {
    $sid  = (int)$s['supplier_id'];
    $name = $s['shop_name'] ?: ('Supplier ' . $sid);

    // Prefer a real email found in the description, else a deterministic one.
    $email = 'supplier' . $sid . '@goldenpromise.test';
    if (preg_match('/[A-Za-z0-9._%+\-]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,}/', (string)$s['description'], $m)) {
        $emailTaken->execute([':e' => $m[0]]);
        if (!$emailTaken->fetchColumn()) {
            $email = $m[0];
        }
    }

    $db->beginTransaction();
    try {
        $insUser->execute([':name' => $name, ':email' => $email, ':pw' => $hash]);
        $uid = (int)$db->lastInsertId();
        $insRole->execute([':uid' => $uid, ':rid' => SUPPLIER_ROLE_ID]);
        $linkSup->execute([':uid' => $uid, ':sid' => $sid]);
        $db->commit();
        echo "user #$uid  <$email>  -> supplier #$sid  ($name)\n";
        $created++;
    } catch (Exception $e) {
        $db->rollBack();
        echo "FAIL supplier #$sid: " . $e->getMessage() . "\n";
    }
}

echo "\nDone. created=$created users. Password for all: " . TEST_PASSWORD . "\n";
