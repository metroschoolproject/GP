<?php

class ReviewModel
{
    private Database $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /* ─── Eligibility ─────────────────────────────────────────────── */

    public function canReview(int $customerId, int $bookingId): bool
    {
        $this->db->dbquery(
            'SELECT id FROM bookings
             WHERE id = :booking_id
               AND user_id = :customer_id
               AND status = \'completed\'
             LIMIT 1'
        );
        $this->db->dbbind(':booking_id', $bookingId);
        $this->db->dbbind(':customer_id', $customerId);
        if (!$this->db->getsingledata()) {
            return false;
        }

        return !$this->exists($bookingId, $customerId);
    }

    public function exists(int $bookingId, int $customerId): bool
    {
        $this->db->dbquery(
            'SELECT id FROM reviews
             WHERE booking_id = :booking_id
               AND customer_id = :customer_id
               AND deleted_at IS NULL
             LIMIT 1'
        );
        $this->db->dbbind(':booking_id', $bookingId);
        $this->db->dbbind(':customer_id', $customerId);

        return (bool)$this->db->getsingledata();
    }

    public function isWithinEditWindow(int $reviewId): bool
    {
        $this->db->dbquery(
            'SELECT id FROM reviews
             WHERE id = :id
               AND deleted_at IS NULL
               AND created_at > NOW() - INTERVAL 7 DAY
             LIMIT 1'
        );
        $this->db->dbbind(':id', $reviewId);

        return (bool)$this->db->getsingledata();
    }

    /* ─── Write ───────────────────────────────────────────────────── */

    public function create(int $bookingId, int $customerId, int $rating, string $comment): int
    {
        $this->db->dbquery(
            'SELECT s.supplier_id
              FROM booking_items bi
              JOIN services s ON s.id = bi.item_id AND bi.item_type = \'service\'
             WHERE bi.booking_id = :booking_id
             LIMIT 1'
        );
        $this->db->dbbind(':booking_id', $bookingId);
        $row = $this->db->getsingledata();
        $supplierId = $row ? (int)$row['supplier_id'] : null;

        $this->db->dbquery(
            'INSERT INTO reviews (booking_id, customer_id, supplier_id, rating, comment, created_at)
             VALUES (:booking_id, :customer_id, :supplier_id, :rating, :comment, NOW())'
        );
        $this->db->dbbind(':booking_id', $bookingId);
        $this->db->dbbind(':customer_id', $customerId);
        $this->db->dbbind(':supplier_id', $supplierId);
        $this->db->dbbind(':rating', $rating);
        $this->db->dbbind(':comment', $comment);
        $this->db->dbexecute();

        return (int)$this->db->lastinsertid();
    }

    public function update(int $reviewId, int $customerId, int $rating, string $comment): bool
    {
        if (!$this->isWithinEditWindow($reviewId)) {
            return false;
        }

        $this->db->dbquery(
            'UPDATE reviews
             SET rating = :rating, comment = :comment, updated_at = NOW()
             WHERE id = :id
               AND customer_id = :customer_id
               AND deleted_at IS NULL'
        );
        $this->db->dbbind(':rating', $rating);
        $this->db->dbbind(':comment', $comment);
        $this->db->dbbind(':id', $reviewId);
        $this->db->dbbind(':customer_id', $customerId);
        $this->db->dbexecute();

        return true;
    }

    public function delete(int $reviewId, int $customerId): bool
    {
        $this->db->dbquery(
            'UPDATE reviews
             SET deleted_at = NOW()
             WHERE id = :id
               AND customer_id = :customer_id
               AND deleted_at IS NULL'
        );
        $this->db->dbbind(':id', $reviewId);
        $this->db->dbbind(':customer_id', $customerId);
        $this->db->dbexecute();

        return true;
    }

    /* ─── Read ────────────────────────────────────────────────────── */

    public function getByBooking(int $bookingId): ?array
    {
        $this->db->dbquery(
            'SELECT r.id, r.rating, r.comment, r.created_at, r.updated_at,
                    u.name AS customer_name
             FROM reviews r
             JOIN users u ON u.user_id = r.customer_id
             WHERE r.booking_id = :booking_id
               AND r.deleted_at IS NULL
             LIMIT 1'
        );
        $this->db->dbbind(':booking_id', $bookingId);

        return $this->db->getsingledata() ?: null;
    }

    public function getByCustomer(int $customerId, int $limit = 20, int $offset = 0): array
    {
        $this->db->dbquery(
            'SELECT r.id, r.booking_id, r.rating, r.comment, r.created_at, r.updated_at,
                    MIN(bi.booking_date) AS event_date
             FROM reviews r
             JOIN bookings b ON b.id = r.booking_id
             LEFT JOIN booking_items bi ON bi.booking_id = b.id
             WHERE r.customer_id = :customer_id
               AND r.deleted_at IS NULL
             GROUP BY r.id
             ORDER BY r.created_at DESC
             LIMIT :lim OFFSET :off'
        );
        $this->db->dbbind(':customer_id', $customerId);
        $this->db->dbbind(':lim', $limit);
        $this->db->dbbind(':off', $offset);

        return $this->db->getmultidata() ?: [];
    }

    public function getByService(int $serviceId, string $sort = 'recent', int $limit = 4, int $offset = 0): array
    {
        $orderBy = match ($sort) {
            'highest' => 'r.rating DESC, r.created_at DESC',
            'lowest'  => 'r.rating ASC, r.created_at DESC',
            default   => 'r.created_at DESC',
        };

        $this->db->dbquery(
            'SELECT r.id, r.booking_id, r.rating, r.comment, r.created_at, r.updated_at,
                    u.name AS customer_name, u.avatar AS customer_avatar
             FROM reviews r
             JOIN booking_items bi ON bi.booking_id = r.booking_id
             JOIN users u ON u.user_id = r.customer_id
             WHERE bi.item_id = :service_id
               AND bi.item_type = \'service\'
               AND r.deleted_at IS NULL
             GROUP BY r.id
             ORDER BY ' . $orderBy . '
             LIMIT :lim OFFSET :off'
        );
        $this->db->dbbind(':service_id', $serviceId);
        $this->db->dbbind(':lim', $limit);
        $this->db->dbbind(':off', $offset);

        return $this->db->getmultidata() ?: [];
    }

    public function getAverageRating(int $serviceId): array
    {
        $this->db->dbquery(
            'SELECT
                COUNT(*) AS review_count,
                COALESCE(AVG(r.rating), 0) AS avg_rating,
                SUM(r.rating = 5) AS five,
                SUM(r.rating = 4) AS four,
                SUM(r.rating = 3) AS three,
                SUM(r.rating = 2) AS two,
                SUM(r.rating = 1) AS one
             FROM reviews r
             JOIN booking_items bi ON bi.booking_id = r.booking_id
             WHERE bi.item_id = :service_id
               AND bi.item_type = \'service\'
               AND r.deleted_at IS NULL'
        );
        $this->db->dbbind(':service_id', $serviceId);
        $row = $this->db->getsingledata() ?: [];

        return [
            'avg_rating'   => round((float)($row['avg_rating'] ?? 0), 1),
            'review_count' => (int)($row['review_count'] ?? 0),
            'distribution' => [
                5 => (int)($row['five']  ?? 0),
                4 => (int)($row['four']  ?? 0),
                3 => (int)($row['three'] ?? 0),
                2 => (int)($row['two']   ?? 0),
                1 => (int)($row['one']   ?? 0),
            ],
        ];
    }

    public function getPendingBookings(int $customerId): array
    {
        $this->db->dbquery(
            'SELECT b.id AS booking_id, b.total_amount,
                    MIN(ed.event_date) AS event_date
             FROM bookings b
             LEFT JOIN event_details ed ON ed.booking_id = b.id
             WHERE b.user_id = :customer_id
               AND b.status = \'completed\'
               AND b.created_at >= NOW() - INTERVAL 12 MONTH
               AND NOT EXISTS (
                   SELECT 1 FROM reviews r
                   WHERE r.booking_id = b.id
                     AND r.customer_id = :customer_id2
                     AND r.deleted_at IS NULL
               )
             GROUP BY b.id, b.total_amount
             ORDER BY event_date DESC'
        );
        $this->db->dbbind(':customer_id', $customerId);
        $this->db->dbbind(':customer_id2', $customerId);

        return $this->db->getmultidata() ?: [];
    }

    public function getBySupplier(int $supplierId, int $limit = 20, int $offset = 0): array
    {
        $this->db->dbquery(
            'SELECT r.id, r.booking_id, r.rating, r.comment, r.created_at, r.updated_at,
                    u.name AS customer_name,
                    s.name AS service_name
             FROM reviews r
             JOIN bookings b ON b.id = r.booking_id
             JOIN users u ON u.user_id = r.customer_id
             JOIN booking_items bi ON bi.booking_id = r.booking_id AND bi.item_type = \'service\'
             JOIN services s ON s.id = bi.item_id AND s.supplier_id = :supplier_id
             WHERE r.supplier_id = :supplier_id2
               AND r.deleted_at IS NULL
             GROUP BY r.id
             ORDER BY r.created_at DESC
             LIMIT :lim OFFSET :off'
        );
        $this->db->dbbind(':supplier_id', $supplierId);
        $this->db->dbbind(':supplier_id2', $supplierId);
        $this->db->dbbind(':lim', $limit);
        $this->db->dbbind(':off', $offset);

        return $this->db->getmultidata() ?: [];
    }

    public function getSupplierStats(int $supplierId): array
    {
        $this->db->dbquery(
            'SELECT
                COUNT(*) AS total,
                COALESCE(AVG(r.rating), 0) AS avg_rating,
                SUM(r.rating = 5) AS five,
                SUM(r.rating = 4) AS four,
                SUM(r.rating = 3) AS three,
                SUM(r.rating = 2) AS two,
                SUM(r.rating = 1) AS one
             FROM reviews r
             JOIN bookings b ON b.id = r.booking_id
             WHERE r.supplier_id = :supplier_id
               AND r.deleted_at IS NULL'
        );
        $this->db->dbbind(':supplier_id', $supplierId);
        $row = $this->db->getsingledata() ?: [];

        return [
            'total'        => (int)($row['total'] ?? 0),
            'avg_rating'   => round((float)($row['avg_rating'] ?? 0), 1),
            'distribution' => [
                5 => (int)($row['five']  ?? 0),
                4 => (int)($row['four']  ?? 0),
                3 => (int)($row['three'] ?? 0),
                2 => (int)($row['two']   ?? 0),
                1 => (int)($row['one']   ?? 0),
            ],
        ];
    }

}
