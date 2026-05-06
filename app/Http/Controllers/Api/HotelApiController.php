<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Hotel;
use App\Models\Room;
use App\Http\Resources\HotelResource;
use App\Traits\ApiResponser;
use App\Services\HotelService;

class HotelApiController extends Controller
{
    use ApiResponser;

    protected $hotelService;

    public function __construct(HotelService $hotelService)
    {
        $this->hotelService = $hotelService;
    }

    public function index(Request $request)
    {
        $hotels = $this->hotelService->getFilteredHotels($request->all());
        return $this->success(HotelResource::collection($hotels), 'Lấy danh sách khách sạn thành công');
    }

    public function show(Request $request, $id)
    {
        try {
            $hotel = $this->hotelService->getHotelDetails($id, $request->all());
            return $this->success(new HotelResource($hotel), 'Lấy chi tiết khách sạn thành công');
        } catch (\Exception $e) {
            return $this->error('Không tìm thấy khách sạn', 404);
        }
    }
}
