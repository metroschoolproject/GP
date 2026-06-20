<?php

class WishlistModel
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    // ═══════════════════════════════════════════════════════════
    //  TOGGLE
    // ═══════════════════════════════════════════════════════════

    /**
     * Toggle a favorite item — add if not present, remove if already saved.
     * Returns ['action' => 'added'|'removed', 'id' => ?int]
     */
    public function toggle(int $userId, string $itemType, int $itemId, ?int $collectionId = null): array
    {
        $existing = $this->findExisting($userId, $itemType, $itemId);

        if ($existing) {
            $this->db->dbquery("DELETE FROM favorites WHERE id = :id");
            $this->db->dbbind(':id', (int)$existing['id']);
            $this->db->dbexecute();
            return ['action' => 'removed', 'id' => null];
        }

        $this->db->dbquery(
            "INSERT INTO favorites (user_id, item_type, item_id, collection_id)
             VALUES (:uid, :item_type, :item_id, :collection_id)"
        );
        $this->db->dbbind(':uid', $userId);
        $this->db->dbbind(':item_type', $itemType);
        $this->db->dbbind(':item_id', $itemId);
        $this->db->dbbind(':collection_id', $collectionId);
        $this->db->dbexecute();

        return ['action' => 'added', 'id' => (int)$this->db->lastinsertid()];
    }

    // ═══════════════════════════════════════════════════════════
    //  READ
    // ═══════════════════════════════════════════════════════════

    /**
     * Check if an item is already favorited by a user.
     */
    public function isFavorited(int $userId, string $itemType, int $itemId): bool
    {
        return $this->findExisting($userId, $itemType, $itemId) !== null;
    }

    /**
     * Get all favorited service IDs for a user (for pre-filling heart states on catalog).
     * Returns array of service IDs.
     */
    public function getFavoritedServiceIds(int $userId): array
    {
        $this->db->dbquery(
            "SELECT item_id FROM favorites
             WHERE user_id = :uid AND item_type = 'service'"
        );
        $this->db->dbbind(':uid', $userId);
        $rows = $this->db->getmultidata();
        return array_map(fn($row) => (int)$row['item_id'], $rows ?: []);
    }

    /**
     * Get the wishlist count for a user (for nav badge).
     */
    public function getWishlistCount(int $userId): int
    {
        $this->db->dbquery(
            "SELECT COUNT(*) AS cnt FROM favorites WHERE user_id = :uid AND item_type = 'service'"
        );
        $this->db->dbbind(':uid', $userId);
        $row = $this->db->getsingledata();
        return (int)($row['cnt'] ?? 0);
    }

    /**
     * Get all collections for a user.
     */
    public function getCollections(int $userId): array
    {
        $this->db->dbquery(
            "SELECT c.*, COUNT(f.id) AS item_count
             FROM wishlist_collections c
             LEFT JOIN favorites f ON f.collection_id = c.id
             GROUP BY c.id
             ORDER BY c.sort_order ASC, c.created_at ASC"
        );
        $collections = $this->db->getmultidata() ?: [];

        // "All Saved" count (items with no collection)
        $this->db->dbquery(
            "SELECT COUNT(*) AS cnt FROM favorites
             WHERE user_id = :uid AND item_type = 'service' AND collection_id IS NULL"
        );
        $this->db->dbbind(':uid', $userId);
        $allCount = (int)(($this->db->getsingledata())['cnt'] ?? 0);

        array_unshift($collections, [
            'id'         => null,
            'name'       => 'All Saved',
            'item_count' => $allCount,
            'is_default' => true,
        ]);

        return $collections;
    }

    /**
     * Get the full wishlist — items with service details, grouped/ filterable by collection.
     * Returns ['collections' => [...], 'items' => [...], 'total' => int]
     */
    public function getUserWishlist(int $userId, ?int $collectionId = null): array
    {
        $collections = $this->getCollections($userId);

        $where  = "f.user_id = :uid AND f.item_type = 'service'";
        $params = [':uid' => $userId];

        if ($collectionId !== null) {
            $where .= " AND f.collection_id = :cid";
            $params[':cid'] = $collectionId;
        }

        $sql = "
            SELECT
                f.id                 AS favorite_id,
                f.item_type,
                f.item_id            AS service_id,
                f.collection_id,
                f.notes,
                f.created_at         AS saved_at,
                s.name               AS service_name,
                s.description        AS service_description,
                s.price,
                s.price_min,
                s.price_max,
                s.thumbnail_url      AS image,
                s.booking_type,
                s.duration_minutes,
                s.pricing_unit,
                s.is_active,
                s.supplier_id,
                COALESCE(sup.shop_name, u.name) AS supplier_name,
                COALESCE(cat.name, 'Service') AS category,
                COALESCE(cat.slug, '') AS category_slug,
                COALESCE(avg_reviews.rating, 0) AS rating,
                COALESCE(avg_reviews.review_count, 0) AS review_count
            FROM favorites f
            LEFT JOIN services s        ON s.id = f.item_id
            LEFT JOIN categories cat    ON cat.id = s.category_id
            LEFT JOIN suppliers sup     ON sup.user_id = s.supplier_id
            LEFT JOIN users u          ON u.user_id = s.supplier_id
            LEFT JOIN (
                SELECT service_id,
                       AVG(rating) AS rating,
                       COUNT(*)    AS review_count
                FROM reviews
                GROUP BY service_id
            ) avg_reviews ON avg_reviews.service_id = s.id
            WHERE {$where}
            ORDER BY f.created_at DESC
        ";

        $this->db->dbquery($sql);
        foreach ($params as $key => $val) {
            $this->db->dbbind($key, $val);
        }
        $items = $this->db->getmultidata() ?: [];

        // Attach collection name to each item
        $collectionMap = [];
        foreach ($collections as $col) {
            $cid = $col['id'];
            $collectionMap[(string)($cid ?? '')] = $col['name'] ?? 'All Saved';
        }
        foreach ($items as &$item) {
            $cidKey = (string)($item['collection_id'] ?? '');
            $item['collection_name'] = $collectionMap[$cidKey] ?? 'All Saved';
        }
        unset($item);

        $total = count($items);

        return [
            'collections' => $collections,
            'items'       => $items,
            'total'       => $total,
        ];
    }

    // ═══════════════════════════════════════════════════════════
    //  COLLECTION CRUD
    // ═══════════════════════════════════════════════════════════

    /**
     * Create a new collection. Max 20 collections per user.
     * Returns collection ID or -1 if limit reached.
     */
    public function createCollection(int $userId, string $name): int
    {
        $name = trim($name);
        if ($name === '') {
            return 0;
        }

        // Check count
        $this->db->dbquery("SELECT COUNT(*) AS cnt FROM wishlist_collections WHERE user_id = :uid");
        $this->db->dbbind(':uid', $userId);
        $count = (int)(($this->db->getsingledata())['cnt'] ?? 0);
        if ($count >= 20) {
            return -1; // limit reached
        }

        // Duplicate name check
        $this->db->dbquery(
            "SELECT id FROM wishlist_collections WHERE user_id = :uid AND name = :name LIMIT 1"
        );
        $this->db->dbbind(':uid', $userId);
        $this->db->dbbind(':name', $name);
        if ($this->db->getsingledata()) {
            return -2; // duplicate name
        }

        // Determine next sort_order
        $this->db->dbquery(
            "SELECT COALESCE(MAX(sort_order), 0) + 1 AS nxt FROM wishlist_collections WHERE user_id = :uid"
        );
        $this->db->dbbind(':uid', $userId);
        $nextOrder = (int)(($this->db->getsingledata())['nxt'] ?? 1);

        $this->db->dbquery(
            "INSERT INTO wishlist_collections (user_id, name, sort_order) VALUES (:uid, :name, :so)"
        );
        $this->db->dbbind(':uid', $userId);
        $this->db->dbbind(':name', $name);
        $this->db->dbbind(':so', $nextOrder);
        $this->db->dbexecute();

        return (int)$this->db->lastinsertid();
    }

    /**
     * Rename a collection. Returns true on success.
     */
    public function renameCollection(int $collectionId, int $userId, string $name): bool
    {
        $name = trim($name);
        if ($name === '') {
            return false;
        }

        $this->db->dbquery(
            "UPDATE wishlist_collections SET name = :name WHERE id = :id AND user_id = :uid"
        );
        $this->db->dbbind(':name', $name);
        $this->db->dbbind(':id', $collectionId);
        $this->db->dbbind(':uid', $userId);
        return $this->db->dbexecute();
    }

    /**
     * Delete a collection. Items inside go back to "All Saved" (collection_id = NULL).
     */
    public function deleteCollection(int $collectionId, int $userId): bool
    {
        $this->db->dbquery(
            "DELETE FROM wishlist_collections WHERE id = :id AND user_id = :uid"
        );
        $this->db->dbbind(':id', $collectionId);
        $this->db->dbbind(':uid', $userId);
        return $this->db->dbexecute();
    }

    // ═══════════════════════════════════════════════════════════
    //  ITEM ACTIONS
    // ═══════════════════════════════════════════════════════════

    /**
     * Move a favorite item to a different collection (or NULL for "All Saved").
     */
    public function moveToCollection(int $favoriteId, ?int $collectionId, int $userId): bool
    {
        // Verify ownership
        if ($collectionId !== null) {
            $this->db->dbquery(
                "SELECT id FROM wishlist_collections WHERE id = :id AND user_id = :uid"
            );
            $this->db->dbbind(':id', $collectionId);
            $this->db->dbbind(':uid', $userId);
            if (!$this->db->getsingledata()) {
                return false; // collection doesn't belong to this user
            }
        }

        $this->db->dbquery(
            "UPDATE favorites SET collection_id = :cid WHERE id = :id AND user_id = :uid"
        );
        $this->db->dbbind(':cid', $collectionId);
        $this->db->dbbind(':id', $favoriteId);
        $this->db->dbbind(':uid', $userId);
        return $this->db->dbexecute();
    }

    /**
     * Add or update a note on a favorite item.
     */
    public function addNote(int $favoriteId, int $userId, string $note): bool
    {
        $note = trim($note);
        $this->db->dbquery(
            "UPDATE favorites SET notes = :notes WHERE id = :id AND user_id = :uid"
        );
        $this->db->dbbind(':notes', $note === '' ? null : $note);
        $this->db->dbbind(':id', $favoriteId);
        $this->db->dbbind(':uid', $userId);
        return $this->db->dbexecute();
    }

    // ═══════════════════════════════════════════════════════════
    //  PRIVATE HELPERS
    // ═══════════════════════════════════════════════════════════

    private function findExisting(int $userId, string $itemType, int $itemId): ?array
    {
        $this->db->dbquery(
            "SELECT id, collection_id FROM favorites
             WHERE user_id = :uid AND item_type = :item_type AND item_id = :item_id
             LIMIT 1"
        );
        $this->db->dbbind(':uid', $userId);
        $this->db->dbbind(':item_type', $itemType);
        $this->db->dbbind(':item_id', $itemId);
        $row = $this->db->getsingledata();
        return $row ?: null;
    }
}
