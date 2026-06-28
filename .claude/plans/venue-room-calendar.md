# Plan: Supplier Calendar Per-Room Availability View

## Context

The supplier service calendar currently shows a single venue-level status per day (open/closed/booked/unavailable). For venue services with multiple rooms that each have different `min_lead_days`, pricing, and booking counts, suppliers need to see which specific rooms are available/booked on each date. The data infrastructure already exists (`venue_room_availability` table with per-room overrides, per-room `min_lead_days`, per-room booking tracking via `venue_room_id` in `booking_items`) — it's just not surfaced in the calendar UI.

## Files to Modify

| File | Change |
|---|---|
| `app/models/SupplierServiceManager.php` | Extend `getServiceCalendarMonth` to include per-room day data for venue services |
| `app/views/supplier/service_calendar.php` | Add room filter/selector UI and per-room display sections |
| `public/js/supplier-service-calendar.js` | Handle room filtering, per-room rendering, room-scoped override modal |
| `public/css/supplier-service-calendar.css` | Style room selector, per-room status pills, room detail cards |

## Step 1: Model — Extend Calendar Data for Venues

In `SupplierServiceManager::getServiceCalendarMonth`:

1. Detect if the service is a venue category
2. If venue, load rooms via `getVenueRooms($venueId)` (already exists)
3. For each room, compute per-day status across the calendar grid:
   - Check if service-level override makes the day `unavailable` → all rooms closed
   - Check room-specific override from `venue_room_availability` for that date
   - If no room-specific override, fall back to room's template record (default hours)
   - Check booking count for that room on that date
   - Check `min_lead_days` per room to flag dates too soon to book
4. Return `venue_rooms` array in the calendar response with per-day status for each room

Add a helper method `calendarRoomDayStatus($roomId, $date)` that returns:
```php
[
    'room_id' => int,
    'date' => string,
    'status' => 'open'|'booked'|'unavailable'|'closed',
    'start_time' => string|null,
    'end_time' => string|null,
    'booking_count' => int,
    'source' => 'override'|'default',
    'min_lead_days' => int,
]
```

### Data structure in calendar response

```json
{
  "venue_rooms": [
    {"id": 1, "name": "Grand Hall", "capacity": 200, "min_lead_days": 7},
    {"id": 2, "name": "Garden Room", "capacity": 50, "min_lead_days": 3}
  ],
  "days": [
    {
      "date": "2026-07-01",
      "status": "open",
      "rooms": [
        {"room_id": 1, "status": "open", "booking_count": 0, "start_time": "09:00", "end_time": "17:00", "source": "default"},
        {"room_id": 2, "status": "booked", "booking_count": 1, "start_time": "09:00", "end_time": "17:00", "source": "default"}
      ]
    }
  ]
}
```

For non-venue services, `venue_rooms` is empty/null and `rooms` is omitted from days.

## Step 2: View — Add Room Selector & Per-Room Display

In `service_calendar.php`:

1. Add a room filter bar above the calendar grid (between toolbar and grid):
   - "All rooms" button (default, shows current venue-level view)
   - One button per room (shows that room's availability on the grid)
   - Only rendered when `venue_rooms` data is present

2. Update the day detail sidebar to show per-room breakdown when "All rooms" is selected:
   - List each room with its status pill, hours, and booking count
   - Show room name, capacity, and min lead days

3. Update the override modal to support room-scoped overrides:
   - Add "Apply to" dropdown: "Entire venue" / "Specific hall"
   - When "Specific hall" is selected, show room selector
   - Use `venueRoomAvailabilityOverrideSave` URL for room overrides (already exists in `Supplier.php` facade at `/supplier/venueRoomAvailabilityOverrideSave/{id}`)

4. Add the room override URLs to `calendarConfig`:
   ```php
   'roomOverrideSave' => URLROOT . '/supplier/venueRoomAvailabilityOverrideSave/' . $serviceId,
   'roomOverrideDelete' => URLROOT . '/supplier/venueRoomAvailabilityOverrideDelete/' . $serviceId . '/',
   ```

## Step 3: JS — Room Filtering & Per-Room Rendering

In `supplier-service-calendar.js`:

1. Add `state.selectedRoomId = null` (null = "All rooms")
2. Render room selector buttons from `calendar.venue_rooms`
3. When a room is selected:
   - Update grid to show that room's status per day (from `day.rooms[roomId]`)
   - Update status pills, colors, and time text to reflect room-specific data
   - Update sidebar to show room-specific info for the selected day
4. When "All rooms" is selected:
   - Show venue-level status (current behavior)
   - Sidebar shows per-room breakdown for the selected day
5. Update `openDayModal()`:
   - Add room selector in the modal (scope dropdown)
   - When scope is "Specific hall", show room dropdown
   - Save override via room-specific URL
   - Clear override works for both service-level and room-level overrides

## Step 4: CSS — Room Selector & Per-Room Styling

In `supplier-service-calendar.css`:

1. Style the room filter bar (horizontal button group, scrollable)
2. Style room status pills in the sidebar (smaller, room-colored)
3. Style the "Apply to" scope selector in the modal
4. Add room-specific grid cell styles (e.g., partially-booked rooms show a different indicator)

## Verification

1. Open supplier calendar for a venue service with multiple rooms
2. Verify "All rooms" shows the venue-level view (unchanged behavior)
3. Click a specific room → grid updates to show that room's availability
4. Click a date → sidebar shows room-specific details (bookings, hours, min lead days)
5. Open override modal → select "Specific hall" → save a room override
6. Verify the override appears on the calendar for that room
7. Verify non-venue services still work with no room selector shown
