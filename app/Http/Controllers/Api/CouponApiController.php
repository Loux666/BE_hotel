<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Coupon;
use App\Traits\ApiResponser;

class CouponApiController extends Controller
{
    use ApiResponser;

    public function index()
    {
        $coupons = Coupon::where('status', 'active')
            ->where('expiry_date', '>=', now())
            ->get();
            
        return $this->success($coupons, 'Lấy danh sách mã giảm giá thành công');
    }
}
