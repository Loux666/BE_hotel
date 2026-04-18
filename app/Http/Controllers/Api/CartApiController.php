<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Traits\ApiResponser;
use App\Http\Resources\CartResource;
use App\Http\Requests\StoreCartRequest;
use App\Services\CartService;
use Exception;

class CartApiController extends Controller
{
    use ApiResponser;

    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $cartItems = Cart::with(['room.hotel', 'room.images'])->where('user_id', $userId)->get();

        return $this->success(CartResource::collection($cartItems), 'Lấy danh sách giỏ hàng thành công');
    }

    public function store(StoreCartRequest $request)
    {
        try {
            $cart = $this->cartService->addToCart(
                $request->user()->id, 
                $request->validated()
            );

            return $this->success($cart, 'Đã thêm vào giỏ hàng', 201);
        } catch (Exception $e) {
            // Dùng code 409 nếu lỗi do trùng phòng
            $code = $e->getCode() == 409 ? 409 : 400;
            return $this->error($e->getMessage(), $code);
        }
    }

    public function destroy(Request $request, $id)
    {
        $item = Cart::where('id', $id)->where('user_id', $request->user()->id)->first();
        if (!$item) {
            return $this->error('Không tìm thấy mục trong giỏ hàng', 404);
        }
        $item->delete();
        return $this->success(null, 'Đã xoá mục khỏi giỏ hàng');
    }
}
