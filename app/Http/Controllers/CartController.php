<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Room;
use App\Models\Booking;
use App\Models\Coupon;
use App\Models\BookingDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    public function addtoCart(Request $request)
    {
        $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'checkin' => 'required|date',
            'checkout' => 'required|date|after:checkin',
        ]);

        $room = Room::findOrFail($request->room_id);

        // Check nếu phòng đó + ngày đó đã tồn tại trong cart (tránh thêm trùng)
        $exists = Cart::where('user_id', Auth::id())
            ->where('room_id', $room->id)
            ->whereDate('checkin', $request->checkin)
            ->whereDate('checkout', $request->checkout)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'Phòng này đã có trong giỏ hàng.');
        }

        // Thêm mới vào cart
        Cart::create([
            'user_id' => Auth::id(),
            'room_id' => $room->id,
            'price_at_time' => $room->price,
            'checkin' => $request->checkin,
            'checkout' => $request->checkout,
            'is_selected' => false, // mặc định chưa chọn để thanh toán
        ]);

        return redirect()->back()->with('success', 'Đã thêm phòng vào giỏ hàng.');
    }


    public function remove($id)
    {
        $item = Cart::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $item->delete();

        return redirect()->back()->with('success', 'Đã xoá khỏi giỏ hàng.');
    }


    public function viewCart()
    {
        $userId = Auth::id();
        $today = now()->startOfDay();

        // ===== Xử lý giỏ hàng =====
        $cartItems = Cart::with(['room.hotel'])
            ->where('user_id', $userId)
            ->get();

        foreach ($cartItems as $item) {
            $checkin = Carbon::parse($item->checkin)->startOfDay();
            $checkout = Carbon::parse($item->checkout)->startOfDay();

            $item->is_expired = $today->gte($checkout);

            if ($item->is_expired) {
                $item->is_available_now = false;
            } else {
                $unavailable = DB::table('room_availabilities')
                    ->where('room_id', $item->room_id)
                    ->whereBetween('date', [$checkin, $checkout->copy()->subDay()])
                    ->where(function ($q) {
                        $q->where('is_available', 0)
                            ->orWhere('available_rooms', '<', 1);
                    })
                    ->exists();

                $item->is_available_now = !$unavailable;
            }

            $item->price = $item->room->price;
        }

        // ===== Xử lý đơn đã đặt =====
        $bookings = Booking::with(['booking_details.room.hotel'])
            ->where('user_id', $userId)
            ->whereIn('status', ['confirmed', 'cancelled'])
            ->latest()
            ->get();

        // Tính xem có thể huỷ được hay không
        foreach ($bookings as $booking) {
            $booking->can_cancel = $booking->booking_details->every(function ($detail) {
                return now()->lt(Carbon::parse($detail->checkin));
            }) && $booking->status !== 'cancelled';
        }

        return view('home.cart', compact('cartItems', 'bookings'));
    }


    public function verifyCart(Request $request)
    {
        $userId = Auth::id();
        $today = now()->startOfDay();
        $selectedIds = json_decode($request->input('selected_ids'), true);

        Log::info('User ID:', ['user_id' => $userId]);
        Log::info('Selected cart item IDs:', ['selected_ids' => $selectedIds]);

        if (empty($selectedIds)) {
            Log::warning('No cart items selected.');
            return back()->with('error', 'Bạn chưa chọn phòng nào.');
        }

        $cartItems = Cart::with(['room.hotel'])
            ->where('user_id', $userId)
            ->whereIn('id', $selectedIds)
            ->get();

        Log::info('Fetched cart items:', ['count' => $cartItems->count()]);

        $invalidItems = [];

        foreach ($cartItems as $item) {
            $checkin = Carbon::parse($item->checkin)->startOfDay();
            $checkout = Carbon::parse($item->checkout)->startOfDay();

            Log::info('Checking item:', [
                'cart_id' => $item->id,
                'room_id' => $item->room_id,
                'checkin' => $checkin,
                'checkout' => $checkout
            ]);

            if ($checkout->lte($today)) {
                Log::warning('Invalid checkout date.', ['cart_id' => $item->id]);
                $invalidItems[] = $item;
                continue;
            }

            $unavailable = DB::table('room_availabilities')
                ->where('room_id', $item->room_id)
                ->whereBetween('date', [$checkin, $checkout->copy()->subDay()])
                ->where(function ($q) {
                    $q->where('is_available', 0)
                        ->orWhere('available_rooms', '<', 1);
                })
                ->exists();

            if ($unavailable) {
                Log::warning('Room unavailable.', ['cart_id' => $item->id]);
                $invalidItems[] = $item;
            }
        }

        if (count($invalidItems) > 0) {
            Log::info('Some cart items are invalid.', ['invalid_count' => count($invalidItems)]);
            return back()->with('modal_error', 'Một số phòng không còn khả dụng.');
        }

        Log::info('All selected items are valid. Saving to session.');
        session()->put('selected_cart_ids', $selectedIds);

        return redirect()->route('cart_booking.form');
    }
}
