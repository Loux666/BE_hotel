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
        $query = $request->get('query');

        if (empty($query)) {
            return $this->success([], 'Empty query');
        }

        $results = Hotel::where('hotel_name', 'like', '%' . $query . '%')
            ->orWhere('hotel_address', 'like', '%' . $query . '%')
            ->orWhere('hotel_city', 'like', '%' . $query . '%')
            ->limit(5)
            ->get(['id', 'hotel_name', 'hotel_address', 'hotel_city', 'hotel_image']);

        return $this->success($results, 'Lấy gợi ý tìm kiếm thành công');
    }
}
