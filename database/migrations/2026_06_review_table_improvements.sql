-- Review table improvements: soft-delete, edit window, nullable columns, unique key
-- Run after 2026_04_category_specific_data.sql

ALTER TABLE reviews
    MODIFY booking_item_id BIGINT DEFAULT NULL,
    MODIFY service_id BIGINT DEFAULT NULL,
    MODIFY customer_id BIGINT NOT NULL,
    MODIFY rating TINYINT(1) NOT NULL,
    ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at,
    ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP DEFAULT NULL AFTER updated_at;

-- Drop the old index if it exists, then add the unique constraint
-- (MySQL allows NULL duplicates in unique keys, so deleted_at=NULL acts as the live-review guard)
ALTER TABLE reviews
    DROP INDEX IF EXISTS booking_item_id;

ALTER TABLE reviews
    ADD UNIQUE KEY IF NOT EXISTS unique_review (booking_id, customer_id, deleted_at);
