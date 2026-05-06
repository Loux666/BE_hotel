<?php

namespace App\Services;

use App\Models\Hotel;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HotelService
{
    /**
     * Get filtered list of hotels
     */
    public function getFilteredHotels(array $filters)
    {
        $query = Hotel::query()->select('hotels.*');

        // Add min_price from aggregate subquery for sorting and filtering
        $query->withMin(['rooms as rooms_min_price' => function($q) use ($filters) {
            if (isset($filters['min_price'])) {
                $q->where('price', '>=', $filters['min_price']);
            }
            if (isset($filters['max_price'])) {
                $q->where('price', '<=', $filters['max_price']);
            }
        }], 'price');

        if (!empty($filters['city'])) {
            $query->where('hotel_city', 'like', '%' . $filters['city'] . '%');
        }

        if (!empty($filters['stars'])) {
            $stars = is_array($filters['stars']) ? $filters['stars'] : explode(',', $filters['stars']);
            $query->whereIn('hotel_rating', $stars);
        }

        if (isset($filters['min_price'])) {
            $query->whereHas('rooms', function($q) use ($filters) {
                $q->where('price', '>=', $filters['min_price']);
            });
        }

        if (isset($filters['max_price'])) {
            $query->whereHas('rooms', function($q) use ($filters) {
                $q->where('price', '<=', $filters['max_price']);
            });
        }

        // Eager load rooms with price filters
        $query->with(['rooms' => function($q) use ($filters) {
            if (isset($filters['min_price'])) {
                $q->where('price', '>=', $filters['min_price']);
            }
            if (isset($filters['max_price'])) {
                $q->where('price', '<=', $filters['max_price']);
            }
        }]);

        // Sorting
        $sort = $filters['sort'] ?? 'newest';
        switch ($sort) {
            case 'price_asc':
                $query->orderBy('rooms_min_price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('rooms_min_price', 'desc');
                break;
            case 'stars_desc':
                $query->orderBy('hotel_rating', 'desc');
                break;
            default:
                $query->orderBy('id', 'desc');
                break;
        }

        return $query->get();
    }

    /**
     * Get hotel details with available rooms
     */
    public function getHotelDetails($id, array $options = [])
    {
        $hotel = Hotel::findOrFail($id);
        
        $checkin = $options['checkin'] ?? null;
        $checkout = $options['checkout'] ?? null;
        $type = $options['type'] ?? null;
        $guests = $options['guests'] ?? null;

        $roomsQuery = $hotel->rooms()->with('images');

        if ($checkin && $checkout) {
            // Logic lọc phòng trống theo ngày
            $roomsQuery->whereDoesntHave('booking_details', function($query) use ($checkin, $checkout) {
                $query->where(function($q) use ($checkin, $checkout) {
                    $q->where('checkin', '<', $checkout)
                      ->where('checkout', '>', $checkin);
                })->whereHas('booking', function ($q) {
                    $q->where('status', '!=', 'cancelled');
                });
            });
        }

        if ($type) {
            $roomsQuery->where('type', $type);
        }

        if ($guests) {
            $roomsQuery->where('capacity', '>=', $guests);
        }

        $rooms = $roomsQuery->get();
        $hotel->setRelation('rooms', $rooms);
        $hotel->load(['feedbacks.user']);

        return $hotel;
    }
}
