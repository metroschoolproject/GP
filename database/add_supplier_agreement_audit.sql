ALTER TABLE suppliers
  ADD COLUMN agreement_accepted_at TIMESTAMP NULL DEFAULT NULL AFTER agreement_accepted,
  ADD COLUMN agreement_version VARCHAR(50) DEFAULT NULL AFTER agreement_accepted_at;
