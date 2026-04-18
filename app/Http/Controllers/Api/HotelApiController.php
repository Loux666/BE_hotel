<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Hotel;
use App\Models\Room;
use App\Http\Resources\HotelResource;
use App\Traits\ApiResponser;

class HotelApiController extends Controller
{
    use ApiResponser;

    public function index(Request $request)
    {
        $query = Hotel::query()->select('hotels.*');

        // Thêm min_price từ aggregate subquery, đồng nhất bộ lọc với câu query chính
        $query->withMin(['rooms as rooms_min_price' => function($q) use ($request) {
            if ($request->filled('min_price')) {
                $q->where('price', '>=', $request->min_price);
            }
            if ($request->filled('max_price')) {
                $q->where('price', '<=', $request->max_price);
            }
        }], 'price');

        if ($request->filled('city')) {
            $query->where('hotel_city', 'like', '%' . $request->city . '%');
        }

        if ($request->filled('stars')) {
            $stars = explode(',', $request->stars);
            $query->whereIn('hotel_rating', $stars);
        }

        if ($request->filled('min_price')) {
            $query->whereHas('rooms', function($q) use ($request) {
                $q->where('price', '>=', $request->min_price);
            });
        }

        if ($request->filled('max_price')) {
            $query->whereHas('rooms', function($q) use ($request) {
                $q->where('price', '<=', $request->max_price);
            });
        }

        // Eager load rooms cũng phải lọc theo price range để FE hiển thị đúng
        $query->with(['rooms' => function($q) use ($request) {
            if ($request->filled('min_price')) {
                $q->where('price', '>=', $request->min_price);
            }
            if ($request->filled('max_price')) {
                $q->where('price', '<=', $request->max_price);
            }
        }]);

        // Sorting - Sử dụng explicitly column alias từ withMin
        if ($request->filled('sort')) {
            switch ($request->sort) {
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
        } else {
            $query->orderBy('id', 'desc');
        }

        $hotels = $query->get();
        return $this->success(HotelResource::collection($hotels), 'Lấy danh sách khách sạn thành công');
    }

    public function show(Request $request, $id)
    {
        $hotel = Hotel::find($id);
        if (!$hotel) {
            return $this->error('Không tìm thấy khách sạn', 404);
        }

        $checkin = $request->query('checkin');
        $checkout = $request->query('checkout');
        $type = $request->query('type');
        $guests = $request->query('guests');

        $roomsQuery = $hotel->rooms()->with('images');

        if ($checkin && $checkout) {
            // Logic lọc phòng trống theo ngày (kiểm tra bảng booking_details)
            $roomsQuery->whereDoesntHave('booking_details', function($query) use ($checkin, $checkout) {
                $query->where(function($q) use ($checkin, $checkout) {
                    $q->where('checkin', '<', $checkout)
                      ->where('checkout', '>', $checkin);
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

        return $this->success(new HotelResource($hotel), 'Lấy chi tiết khách sạn thành công');
    }
}
