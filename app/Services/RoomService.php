<?php

namespace App\Services;

use App\Models\Room;
use App\Models\RoomAvailability;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class RoomService
{
    /**
     * Get room details with images
     */
    public function getRoomDetails($id)
    {
        return Room::with(['images', 'hotel'])->findOrFail($id);
    }

    public function checkAvailability($roomId, $checkin, $checkout)
    {
        $start = Carbon::parse($checkin);
        $end = Carbon::parse($checkout);
        $days = $start->diffInDays($end);

        if ($days <= 0) {
            return false;
        }

        $room = Room::findOrFail($roomId);
        $totalUnits = $room->total_rooms ?? 1;

        for ($date = $start->copy(); $date->lt($end); $date->addDay()) {
            $bookedCount = \App\Models\BookingDetail::where('room_id', $roomId)
                ->where('checkin', '<=', $date->toDateString())
                ->where('checkout', '>', $date->toDateString())
                ->whereHas('booking', function ($query) {
                    $query->where('status', '!=', 'cancelled');
                })
                ->count();

            if ($bookedCount >= $totalUnits) {
                return false;
            }
        }

        return true;
    }
}
