<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\Room;
use Illuminate\Support\Facades\Mail;
use App\Models\Hotel;
use App\Models\Coupon;
use App\Models\RoomImage;
use App\Models\RefundRequest;
use App\Models\RoomAvailability;
use App\Models\Booking;
use App\Models\BookingDetail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Mail\RefundSuccessMail;
use Carbon\Carbon;
use PhpParser\Node\Stmt\Catch_;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function index()
    {
        if (Auth::id()) {
            $usertype = Auth::user()->usertype;
            if ($usertype == 'user') {
                return $this->home();
            } else if ($usertype == 'admin') {
                return $this->adminChart();
            } else if ($usertype == 'manager') {
                return app(ManagerController::class)->dashboard();
            } else {
                return redirect()->back();
            }
        }
    }
    public function adminChart()
    {

        $totalUsers = User::where('usertype', 'user')->count();

        // Số booking có status == 'confirmed'
        $confirmedBookings = Booking::where('status', 'confirmed')->count();

        // Tổng số phòng
        $totalRooms = Room::count();

        // Tổng số khách sạn
        $totalHotels = Hotel::count();
        $monthlyRevenue = Booking::where('status', 'confirmed')
            ->selectRaw('MONTH(created_at) as month, SUM(total_price) as total')
            ->groupBy('month')
            ->pluck('total', 'month');

        $monthlyBookings = Booking::where('status', 'confirmed')
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->groupBy('month')
            ->pluck('count', 'month');

        // 3: Booking theo khách sạn
        $bookingsByHotel = DB::table('booking_details')
            ->select('hotels.hotel_name', DB::raw('COUNT(DISTINCT booking_details.booking_id) as total'))
            ->join('hotels', 'booking_details.hotel_id', '=', 'hotels.id')
            ->join('bookings', 'booking_details.booking_id', '=', 'bookings.id')
            ->where('bookings.status', 'confirmed')
            ->groupBy('hotels.id', 'hotels.hotel_name')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        // 4: Thành công vs huỷ
        $confirmed = Booking::where('status', 'confirmed')->count();
        $cancelled = Booking::where('status', 'cancelled')->count();

        // 5: Tỉ lệ phòng đã đặt
        $totalRooms = Room::count();
        $bookedRooms = BookingDetail::whereHas('booking', fn($q) =>
        $q->where('status', 'confirmed'))->count();
        return view('admin.index', compact(
            'totalUsers',
            'confirmedBookings',
            'totalRooms',
            'totalHotels',
            'monthlyRevenue',
            'monthlyBookings',
            'bookingsByHotel',
            'confirmed',
            'cancelled',
            'totalRooms',
            'bookedRooms'
        ));
    }

    public function home()
    {
        $cities = ['Hà Nội', 'Vũng Tàu', 'Đà Lạt', 'Hạ Long', 'Hồ Chí Minh', 'Đà Năng'];
        $rooms = collect();

        foreach ($cities as $city) {
            $room = Room::whereHas('hotel', function ($q) use ($city) {
                $q->where('hotel_city', $city);
            })
                ->with(['hotel', 'firstImage']) // Load cả hotel và ảnh đầu tiên
                ->inRandomOrder()
                ->first();

            if ($room) {
                $rooms->push($room);
            }
        }
        return view('home.index', compact('rooms'));
    }

    public function about()
    {
        return view('home.about');
    }

    public function room()
    {
        return view('home.room_list');
    }
    public function cart()
    {
        return view('home.cart');
    }
    public function coupon()
    {
        $today = Carbon::now();

        $coupons = Coupon::where('is_active', 1)
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->whereColumn('used_count', '<', 'max_uses')
            ->get();

        return view('home.coupon', compact('coupons'));
    }
    public function liveSearch(Request $request)
    {
        $query = $request->get('query');

        $results = Hotel::where('hotel_name', 'like', '%' . $query . '%')
            ->orWhere('hotel_address', 'like', '%' . $query . '%')
            ->orWhere('hotel_city', 'like', '%' . $query . '%')
            ->limit(5)
            ->get(['hotel_name', 'hotel_address', 'hotel_city', 'hotel_image']);

        return response()->json($results);
    }

    public function createManager()
    {
        $hotels = Hotel::all(); // Để admin chọn khách sạn
        return view('admin.createManager', compact('hotels'));
    }
    public function showManager(Request $request)
    {
        $query = User::with('hotel')
            ->where('usertype', 'manager');

        // Nếu có hotel_id từ form search
        if ($request->filled('hotel_id')) {
            $query->where('hotel_id', $request->hotel_id);
        }

        $managers = $query->latest()->get();

        // Gửi thêm danh sách khách sạn để render dropdown lọc
        $hotels = Hotel::all();

        return view('admin.showManager', compact('managers', 'hotels'));
    }
    public function storeManager(Request $request)
    {
        Log::info('[CREATE MANAGER] hotel_id:', ['hotel_id' => $request->hotel_id]);
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
            'hotel_id' => 'required|exists:hotels,id',
            'phone' => 'required|string|max:15',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'usertype' => 'manager',
            'phone' => $request->phone,
            'hotel_id' => $request->hotel_id,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('admin.manager.create')->with('success', 'Tạo tài khoản quản lý khách sạn thành công!');
    }

    public function editManager($id)
    {
        $managers = User::where('usertype', 'manager')->findorFail($id);
        $hotels = Hotel::all();
        return view('admin.editManager', compact('managers', 'hotels'));
    }
    public function updateManager(Request $request, $id)
    {
        $managers = User::findorFail($id);
        $request->validate([
            'email' => 'required|email|unique:users,email,' . $id,
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'hotel_id' => 'required|exists:hotels,id',
        ]);
        $managers->update([
            'email' => $request->email,
            'name' => $request->name,
            'phone' => $request->phone,
            'hotel_id' => $request->hotel_id,

        ]);
        return redirect()->back()->with('success', 'Cập nhật thông tin manager thành công');
    }
    public function deleteManager($id)
    {
        $manager = User::where('usertype', 'manager')->findOrFail($id);

        try {
            $manager->delete();
            return redirect()->back()->with('success', 'Xoá manager thành công!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Xoá thất bại!');
        }
    }
    public function showBooking(Request $request)
    {
        $query = Booking::query()->latest();

        if ($request->has('phone') && $request->phone != '') {
            $query->where('guest_phone', 'like', '%' . $request->phone . '%');
        }

        $bookings = $query->where('status', 'confirmed')->get();

        return view('admin.showBooking', compact('bookings'));
    }



    public function showRefund(Request $request)
    {
        $query = RefundRequest::with('booking.user')->where('status', 'pending')->latest();

        if ($request->has('phone') && $request->phone != '') {
            $query->whereHas('booking', function ($q) use ($request) {
                $q->where('guest_phone', 'like', '%' . $request->phone . '%');
            });
        }

        $refunds = $query->paginate(10);

        return view('admin.showRefund', compact('refunds'));
    }
    public function processManualRefund(RefundRequest $refund)
    {
        if ($refund->type !== 'offline' || $refund->status !== 'pending') {
            return back()->with('error', 'Yêu cầu không hợp lệ hoặc đã xử lý.');
        }

        // Cập nhật trạng thái
        $refund->update([
            'status' => 'done',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);
        $refund->booking->update([
            'status' => 'cancelled',
        ]);
        $refund->booking->payments->update([
            'status' => 'cancelled',
        ]);
        foreach ($refund->booking->booking_details as $detail) {
            $start = Carbon::parse($detail->checkin);
            $end = Carbon::parse($detail->checkout);

            for ($date = $start->copy(); $date->lt($end); $date->addDay()) {
                RoomAvailability::where('room_id', $detail->room_id)
                    ->whereDate('date', $date)
                    ->increment('available_rooms', 1); // ✅ Hoàn lại phòng
            }
        }

        // Gửi mail cho người dùng
        $user = $refund->booking->user;
        if ($user && $user->email) {
            Mail::to($user->email)->send(new RefundSuccessMail($refund));
        }

        return back()->with('success', 'Đã hoàn tất hoàn tiền thủ công và gửi thông báo cho người dùng.');
    }

    public function couponManage()
    {
        $coupons = Coupon::latest()->get();
        return view('admin.couponManage', compact('coupons'));
    }
    public function addCoupon()
    {

        return view('admin.addCoupon');
    }
    public function storeCoupon(Request $request)
    {
        try {
            Coupon::create([
                'code' => strtoupper($request->code),
                'type' => $request->type,
                'value' => floatval($request->value),
                'min_order_price' => floatval($request->min_order_price),
                'max_uses' => $request->max_uses,
                'user_limit' => $request->user_limit,
                'start_date' => $request->start_date . ' 00:00:00',
                'end_date' => $request->end_date . ' 23:59:59',
                'is_active' => $request->has('is_active'),
                'used_count' => 0,
            ]);
        } catch (\Exception $e) {
            dd($e->getMessage()); // in lỗi cụ thể
            return back()->with('erorr', 'Lỗi:', $e);
        }

        return back()->with('success', 'Tạo mã thành công!');
    }

    public function editCoupon($id)
    {
        $coupon = Coupon::findOrFail($id);
        return view('admin.editCoupon', compact('coupon'));
    }
    public function updateCoupon(Request $request, $id)
    {
        $coupon = Coupon::findOrFail($id);

        $request->validate([
            'code' => 'required|string|unique:coupons,code,' . $coupon->id,
            'type' => 'required|in:percent,fixed',
            'value' => 'required|numeric|min:1',
            'min_order_price' => 'required|numeric|min:0',
            'max_uses' => 'required|integer|min:1',
            'user_limit' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_active' => 'nullable|boolean',
        ]);

        $coupon->update([
            'code' => strtoupper($request->code),
            'type' => $request->type,
            'value' => $request->value,
            'min_order_price' => $request->min_order_price,
            'max_uses' => $request->max_uses,
            'user_limit' => $request->user_limit,
            'start_date' => $request->start_date . ' 00:00:00',
            'end_date' => $request->end_date . ' 23:59:59',
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->back()->with('success', 'Cập nhật mã giảm giá thành công!');
    }
    public function deleteCoupon($id)
    {
        $coupon = Coupon::findOrFail($id);

        try {
            $coupon->delete();
            return redirect()->back()->with('success', 'Xóa mã giảm giá thành công!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Xóa không thành công!');
        }
    }
}
