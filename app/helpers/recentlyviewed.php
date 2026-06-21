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
 * @return array Service rows (service_id, name, category, cover_image, starting_price)
 */
function fetchRecentlyViewedServices(object $db): array
{
    $ids = getRecentlyViewedIds();
    if (empty($ids)) {
        return [];
    }

    $results = [];
    foreach ($ids as $id) {
        $db->dbquery(
            "SELECT s.id AS service_id,
                    s.name,
                    c.name AS category,
                    COALESCE(
                        NULLIF(s.thumbnail_url, ''),
                        (
                            SELECT sm.file_url
                            FROM service_media sm
                            WHERE sm.service_id = s.id
                              AND TRIM(COALESCE(sm.file_url, '')) <> ''
                            ORDER BY sm.id ASC
                            LIMIT 1
                        )
                    ) AS cover_image,
                    COALESCE(s.price_min, s.price) AS starting_price
             FROM services s
             INNER JOIN suppliers sup ON sup.supplier_id = s.supplier_id
             LEFT JOIN categories c ON c.id = s.category_id
             WHERE s.id = :id
               AND s.is_active = 1
               AND sup.deleted_at IS NULL
               AND sup.is_available = 1
               AND sup.status IN ('approved', 'verified')
               AND sup.payment_status = 'paid'
             LIMIT 1"
        );
        $db->dbbind(':id', $id);
        $row = $db->getsingledata();
        if ($row) {
            $results[] = $row;
        }
    }

    return $results;
}
