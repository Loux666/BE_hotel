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
        $today = now();
        $coupons = Coupon::where('is_active', 1)
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->where(function($q) {
                $q->whereNull('max_uses')
                  ->orWhereColumn('used_count', '<', 'max_uses');
            })
            ->get();
            
        return $this->success($coupons, 'Lấy danh sách mã giảm giá thành công');
    }
}
