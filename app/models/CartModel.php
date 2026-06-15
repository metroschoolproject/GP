<?php

class CartModel
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * Get the user's active cart, or create one if none exists.
     */
    public function getOrCreateCart(int $userId): int
    {
        $this->db->dbquery("SELECT id FROM carts WHERE user_id = :uid LIMIT 1");
        $this->db->dbbind(':uid', $userId);
        $row = $this->db->getsingledata();

        if ($row && !empty($row['id'])) {
            return (int)$row['id'];
        }

        $this->db->dbquery("INSERT INTO carts (user_id) VALUES (:uid)");
        $this->db->dbbind(':uid', $userId);
        $this->db->dbexecute();
        return (int)$this->db->lastinsertid();
    }

    /**
     * Add an item to the user's cart.
     * Returns the cart_item_id on success, or false if duplicate/error.
     */
    public function addItem(int $userId, array $data)
    {
        $itemType  = $data['item_type'] ?? 'service';
        $itemId    = (int)($data['item_id'] ?? 0);
        $date      = $data['selected_date'] ?? null;
        $price     = $data['price'] ?? null;
        $source    = $data['source'] ?? null;
        $slotId    = !empty($data['slot_id']) ? (int)$data['slot_id'] : null;
        $startTime = $data['start_time'] ?? null;
        $endTime   = $data['end_time'] ?? null;

        if ($itemId <= 0) {
            return false;
        }

        // Check for duplicate: same user, item_type, item_id, date, slot
        $this->db->dbquery(
            "SELECT id FROM cart_items
             WHERE user_id = :uid AND item_type = :itype AND item_id = :iid
               AND (selected_date = :sdate OR (selected_date IS NULL AND :sdate IS NULL))
               AND (slot_id = :sid OR (slot_id IS NULL AND :sid IS NULL))
             LIMIT 1"
        );
        $this->db->dbbind(':uid', $userId);
        $this->db->dbbind(':itype', $itemType);
        $this->db->dbbind(':iid', $itemId, PDO::PARAM_INT);
        $this->db->dbbind(':sdate', $date);
        $this->db->dbbind(':sid', $slotId, PDO::PARAM_INT);
        $existing = $this->db->getsingledata();

        if ($existing && !empty($existing['id'])) {
            return false; // Already in cart
        }

        $cartId = $this->getOrCreateCart($userId);

        $this->db->dbquery(
            "INSERT INTO cart_items (cart_id, user_id, item_type, item_id, selected_date, price, source, slot_id, start_time, end_time)
             VALUES (:cid, :uid, :itype, :iid, :sdate, :price, :src, :sid, :stime, :etime)"
        );
        $this->db->dbbind(':cid', $cartId, PDO::PARAM_INT);
        $this->db->dbbind(':uid', $userId, PDO::PARAM_INT);
        $this->db->dbbind(':itype', $itemType);
        $this->db->dbbind(':iid', $itemId, PDO::PARAM_INT);
        $this->db->dbbind(':sdate', $date);
        $this->db->dbbind(':price', $price);
        $this->db->dbbind(':src', $source);
        $this->db->dbbind(':sid', $slotId, PDO::PARAM_INT);
        $this->db->dbbind(':stime', $startTime);
        $this->db->dbbind(':etime', $endTime);

        if ($this->db->dbexecute()) {
            return (int)$this->db->lastinsertid();
        }
        return false;
    }

    /**
     * Find a package already in the user's cart that includes this service.
     */
    public function findCartPackageIncludingService(int $userId, int $serviceId): array|false
    {
        $this->db->dbquery(
            "SELECT ci.id AS cart_item_id,
                    p.package_id,
                    p.name AS package_name,
                    s.name AS service_name
             FROM cart_items ci
             INNER JOIN packages p ON ci.item_id = p.package_id AND ci.item_type = 'package'
             INNER JOIN package_items pi ON pi.package_id = p.package_id
             INNER JOIN services s ON pi.service_id = s.id
             WHERE ci.user_id = :uid
               AND pi.service_id = :sid
             ORDER BY ci.id DESC
             LIMIT 1"
        );
        $this->db->dbbind(':uid', $userId, PDO::PARAM_INT);
        $this->db->dbbind(':sid', $serviceId, PDO::PARAM_INT);

        return $this->db->getsingledata();
    }

    /**
     * Remove a single item from the cart.
     */
    public function removeItem(int $userId, int $cartItemId): bool
    {
        $this->db->dbquery(
            "DELETE FROM cart_items WHERE id = :ciid AND user_id = :uid LIMIT 1"
        );
        $this->db->dbbind(':ciid', $cartItemId, PDO::PARAM_INT);
        $this->db->dbbind(':uid', $userId, PDO::PARAM_INT);
        return $this->db->dbexecute();
    }

    /**
     * Get all cart items for a user, joined with service details.
     */
    public function getCartItems(int $userId): array
    {
        $this->db->dbquery(
            "SELECT ci.id AS cart_item_id, ci.item_type, ci.item_id, ci.selected_date,
                    ci.price AS cart_price, ci.slot_id, ci.start_time, ci.end_time,
                    COALESCE(s.name, p.name, sp.name) AS service_name,
                    COALESCE(s.thumbnail_url, p.image_url, sp.thumbnail_url) AS thumbnail_url,
                    COALESCE(s.price_min, p.base_price, sp.total_price) AS price_min,
                    COALESCE(s.price_max, p.base_price, sp.total_price) AS price_max,
                    COALESCE(s.booking_type, 'fullday') AS booking_type,
                    COALESCE(sup.shop_name, sp_sup.shop_name, 'Golden Promise') AS supplier_name,
                    COALESCE(cat.name, package_cat.name) AS category_name,
                    p.slug AS package_slug
             FROM cart_items ci
             LEFT JOIN services s ON ci.item_id = s.id AND ci.item_type = 'service'
             LEFT JOIN packages p ON ci.item_id = p.package_id AND ci.item_type = 'package'
             LEFT JOIN supplier_packages sp ON ci.item_id = sp.id AND ci.item_type = 'supplier_package'
             LEFT JOIN suppliers sup ON s.supplier_id = sup.supplier_id
             LEFT JOIN suppliers sp_sup ON sp.supplier_id = sp_sup.supplier_id
             LEFT JOIN categories cat ON s.category_id = cat.id
             LEFT JOIN categories package_cat ON package_cat.slug = 'package'
             WHERE ci.user_id = :uid
             ORDER BY ci.id DESC"
        );
        $this->db->dbbind(':uid', $userId, PDO::PARAM_INT);
        return $this->db->getmultidata();
    }

    /**
     * Get the total number of items in the user's cart.
     */
    public function getCartCount(int $userId): int
    {
        $this->db->dbquery(
            "SELECT COUNT(*) AS cnt FROM cart_items WHERE user_id = :uid"
        );
        $this->db->dbbind(':uid', $userId, PDO::PARAM_INT);
        $row = $this->db->getsingledata();
        return $row ? (int)$row['cnt'] : 0;
    }

    /**
     * Calculate the total price of all items in the cart.
     */
    public function getCartTotal(int $userId): float
    {
        $this->db->dbquery(
            "SELECT COALESCE(SUM(COALESCE(ci.price, s.price_min, s.price, p.base_price, sp.total_price, 0)), 0) AS total
             FROM cart_items ci
             LEFT JOIN services s ON ci.item_id = s.id AND ci.item_type = 'service'
             LEFT JOIN packages p ON ci.item_id = p.package_id AND ci.item_type = 'package'
             LEFT JOIN supplier_packages sp ON ci.item_id = sp.id AND ci.item_type = 'supplier_package'
             WHERE ci.user_id = :uid"
        );
        $this->db->dbbind(':uid', $userId, PDO::PARAM_INT);
        $row = $this->db->getsingledata();
        return $row ? (float)$row['total'] : 0;
    }
}
