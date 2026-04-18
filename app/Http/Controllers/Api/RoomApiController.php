<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\RoomAvailability;
use Illuminate\Support\Facades\DB;
use App\Traits\ApiResponser;
use App\Http\Resources\RoomResource;

class RoomApiController extends Controller
{
    use ApiResponser;

    /**
     * Get recommended available rooms
     */
    public function recommended(Request $request)
    {
        $today = now()->toDateString();
        
        // Find rooms that have availability entries >= today
        $rooms = Room::with(['hotel', 'images'])
            ->where('status', 'active')
            ->leftJoin('room_availabilities', function($join) use ($today) {
                $join->on('rooms.id', '=', 'room_availabilities.room_id')
                     ->where('room_availabilities.date', '=', $today);
            })
            ->where(function($query) {
                $query->whereNull('room_availabilities.id')
                      ->orWhere(function($subq) {
                          $subq->where('room_availabilities.is_available', 1)
                               ->where('room_availabilities.available_rooms', '>', 0);
                      });
            })
            ->select('rooms.*')
            ->inRandomOrder()
            ->limit(4)
            ->get();
            
        return $this->success(RoomResource::collection($rooms), 'Lấy phòng gợi ý thành công');
    }
}
