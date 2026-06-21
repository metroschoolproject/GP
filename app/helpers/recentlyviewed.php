<?php
/**
 * Recently Viewed Services — Cookie-based helper
 *
 * Stores up to 10 service IDs in a cookie (JSON array).
 * Cookie name: gp_recently_viewed
 * Lifetime: 30 days
 */

define('RECENTLY_VIEWED_COOKIE', 'gp_recently_viewed');
define('RECENTLY_VIEWED_MAX', 10);
define('RECENTLY_VIEWED_DAYS', 30);

/**
 * Get recently viewed service IDs from cookie.
 * @return int[] Array of service IDs
 */
function getRecentlyViewedIds(): array
{
    if (empty($_COOKIE[RECENTLY_VIEWED_COOKIE])) {
        return [];
    }

    $decoded = json_decode($_COOKIE[RECENTLY_VIEWED_COOKIE], true);
    if (!is_array($decoded)) {
        return [];
    }

    // Filter to integers only
    return array_values(array_filter(array_map('intval', $decoded), fn($id) => $id > 0));
}

/**
 * Add a service ID to the recently viewed list.
 * Prepends the ID, deduplicates, caps at RECENTLY_VIEWED_MAX.
 * @param int $serviceId
 */
function addRecentlyViewed(int $serviceId): void
{
    if ($serviceId <= 0) return;

    $ids = getRecentlyViewedIds();

    // Remove if already exists (will re-add at front)
    $ids = array_filter($ids, fn($id) => $id !== $serviceId);

    // Prepend new ID
    array_unshift($ids, $serviceId);

    // Cap at max
    $ids = array_slice($ids, 0, RECENTLY_VIEWED_MAX);

    // Save to cookie
    $expires = time() + (RECENTLY_VIEWED_DAYS * 86400);
    $path = parse_url(URLROOT, PHP_URL_PATH) ?: '/';

    setcookie(
        RECENTLY_VIEWED_COOKIE,
        json_encode($ids),
        [
            'expires'  => $expires,
            'path'     => $path,
            'httponly'  => false,
            'samesite' => 'Lax',
        ]
    );

    // Also update $_COOKIE so current request sees it
    $_COOKIE[RECENTLY_VIEWED_COOKIE] = json_encode($ids);
}

/**
 * Fetch recently viewed services from DB.
 * @param object $db Database instance
 * @return array Service rows (id, name, category, cover_image, starting_price)
 */
function fetchRecentlyViewedServices(object $db): array
{
    $ids = getRecentlyViewedIds();
    if (empty($ids)) {
        return [];
    }

    // Build placeholders
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    // Fetch services, preserving cookie order
    $sql = "SELECT s.service_id, s.name, s.category, s.cover_image, s.starting_price,
                   s.supplier_id, u.name AS supplier_name
            FROM services s
            LEFT JOIN users u ON s.supplier_id = u.user_id
            WHERE s.service_id IN ($placeholders)
              AND s.status = 'published'";

    $db->dbquery($sql);
    foreach ($ids as $id) {
        $db->dbbind(':id' . $id, $id);
    }

    // Note: dbbind uses named params, but we need positional for IN clause
    // Let's use a simpler approach with the DB wrapper
    $results = [];
    foreach ($ids as $id) {
        $db->dbquery("SELECT service_id, name, category, cover_image, starting_price
                       FROM services WHERE service_id = :id AND status = 'published'");
        $db->dbbind(':id', $id);
        $row = $db->getsingledata();
        if ($row) {
            $results[] = $row;
        }
    }

    return $results;
}
