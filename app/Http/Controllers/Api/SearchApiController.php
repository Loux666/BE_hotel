<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Hotel;
use App\Traits\ApiResponser;

class SearchApiController extends Controller
{
    use ApiResponser;

    /**
     * Live search for hotels and locations
     */
    public function suggestions(Request $request)
    {
        // ... (giữ nguyên hoặc xóa nếu muốn dùng hoàn toàn local)
    }

    /**
     * Get all unique cities for local filtering
     */
    public function cities()
    {
        $cities = Hotel::distinct()->pluck('hotel_city');
        return $this->success($cities, 'Lấy danh sách thành phố thành công');
    }
}
