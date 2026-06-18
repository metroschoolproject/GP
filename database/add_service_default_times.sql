-- Add default service hours to services table.
-- Suppliers set these in the create/edit service form.
-- Used as layer-2 fallback for fullday package booking time resolution
-- (after service_schedules open/close, before category-based PHP defaults).

ALTER TABLE `services`
  ADD COLUMN `default_start_time` TIME NULL DEFAULT NULL AFTER `min_lead_days`,
  ADD COLUMN `default_end_time`   TIME NULL DEFAULT NULL AFTER `default_start_time`;
