-- Add minimum lead days configuration to services
-- Allows suppliers to require advance notice for bookings

ALTER TABLE `services`
ADD COLUMN IF NOT EXISTS `min_lead_days` INT DEFAULT 0 COMMENT 'Minimum days in advance customer must book (0 = same day allowed)';

-- Add per-room override for venue services
ALTER TABLE `venue_rooms`
ADD COLUMN IF NOT EXISTS `min_lead_days` INT DEFAULT NULL COMMENT 'Room-specific override. NULL = inherit from parent service.';

-- Add indexes for potential filtering
ALTER TABLE `services` ADD INDEX IF NOT EXISTS `idx_min_lead_days` (`min_lead_days`);
ALTER TABLE `venue_rooms` ADD INDEX IF NOT EXISTS `idx_room_min_lead_days` (`min_lead_days`);
