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
        $cartItems = $this->cartService->getCart($request->user()->id);
        return $this->success(CartResource::collection($cartItems), 'Lấy danh sách giỏ hàng thành công');
    }

    public function store(StoreCartRequest $request)
    {
        try {
            $cart = $this->cartService->addToCart(
                $request->user()->id, 
                $request->validated()
            );

            return $this->success(new CartResource($cart), 'Đã thêm vào giỏ hàng', 201);
        } catch (Exception $e) {
            $code = $e->getCode() == 409 ? 409 : 400;
            return $this->error($e->getMessage(), $code);
        }
    }

    public function destroy(Request $request, $id)
    {
        $deleted = $this->cartService->removeFromCart($request->user()->id, $id);
        if (!$deleted) {
            return $this->error('Không tìm thấy mục trong giỏ hàng', 404);
        }
        return $this->success(null, 'Đã xoá mục khỏi giỏ hàng');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer'
        ]);

        $invalidIds = $this->cartService->verifyAvailability($request->user()->id, $request->ids);

        if (count($invalidIds) > 0) {
            return $this->error('Một số phòng không còn khả dụng', 409, [
                'invalid_ids' => $invalidIds
            ]);
        }

        return $this->success(null, 'Tất cả các phòng đã chọn đều khả dụng');
    }
}
