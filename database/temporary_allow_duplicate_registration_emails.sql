-- TEMPORARY local testing change:
-- Allows inserting multiple users with the same email during test-data entry.
--
-- Apply:
ALTER TABLE users DROP INDEX unique_email;
--
-- Roll back after duplicate test rows are removed:
-- ALTER TABLE users ADD UNIQUE KEY unique_email (email);
