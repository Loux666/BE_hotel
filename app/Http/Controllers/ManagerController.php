<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Carbon\CarbonPeriod;
use App\Models\Room;
use App\Models\Booking;
use App\Models\BookingDetail;
use App\Models\Payment;
use App\Models\Feedback;
use App\Models\RoomAvailability;
use App\Models\RoomImage;
use App\Models\RefundRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\RefundSuccessMail;

class ManagerController extends Controller
{
    public function dashboard()
    {
        $manager = Auth::user();
        $hotelId = $manager->hotel_id; // Lấy hotel_id mà manager quản lý
        $name = $manager->name;
        $hotel_name = $manager->hotel->hotel_name ?? 'Khách sạn không xác định';


        $today = Carbon::today();

        // Tổng số phòng của khách sạn
        $totalRooms = Room::where('hotel_id', $hotelId)->count();

        // Số phòng đang được sử dụng hôm nay (dựa trên BookingDetail liên kết phòng)
        $roomsInUse = BookingDetail::whereDate('checkin', '<=', $today)
            ->whereDate('checkout', '>=', $today)
            ->whereHas('room', function ($query) use ($hotelId) {
                $query->where('hotel_id', $hotelId);
            })
            ->count();

        // Doanh thu hôm nay của khách sạn
        $todayRevenue = Payment::whereDate('created_at', $today)
            ->where('status', 'success')
            ->whereHas('booking.booking_details.room', function ($query) use ($hotelId) {
                $query->where('hotel_id', $hotelId);
            })
            ->sum('amount');

        // Số lượt check-in hôm nay tại khách sạn
        $checkinsToday = BookingDetail::whereDate('checkin', $today)
            ->whereHas('room', function ($query) use ($hotelId) {
                $query->where('hotel_id', $hotelId);
            })
            ->count();

        // Đánh giá trung bình của khách sạn
        $averageRating = Feedback::where('hotel_id', $hotelId)->avg('rating') ?? 0;

        return view('manager.dashboard', [
            'totalRooms'     => $totalRooms,
            'roomsInUse'     => $roomsInUse,
            'todayRevenue'   => $todayRevenue,
            'checkinsToday'  => $checkinsToday,
            'averageRating'  => number_format($averageRating, 1),
            'name'           => $name,
            'hotel_name'     => $hotel_name,
        ]);
    }
    public function showBooking()
    {
        $manager = Auth::user();

        // Kiểm tra quyền và hotel_id
        if (!$manager || !$manager->hotel_id) {
            abort(403, 'Bạn không có quyền truy cập.');
        }

        $hotelId = $manager->hotel_id;
        $name = $manager->name;
        $hotelName = $manager->hotel->hotel_name ?? 'Khách sạn không xác định';

        // Lấy tất cả bookings có booking_details thuộc khách sạn này
        $bookings = Booking::whereHas('booking_details', function ($query) use ($hotelId) {
            $query->where('hotel_id', $hotelId);
        })
            ->with(['booking_details' => function ($query) use ($hotelId) {
                $query->where('hotel_id', $hotelId)->with('room');
            }, 'user'])
            ->orderByDesc('created_at')
            ->get();

        return view('manager.showbooking', compact('bookings', 'name', 'hotelName'));
    }
    public function showCancel()
    {
        $manager = Auth::user(); // đang đăng nhập
        // Kiểm tra quyền và hotel_id
        if (!$manager || !$manager->hotel_id) {
            abort(403, 'Bạn không có quyền truy cập.');
        }

        $hotelId = $manager->hotel_id;
        $name = $manager->name;
        $hotelName = $manager->hotel->hotel_name ?? 'Khách sạn không xác định';


        // Lấy refund offline đang chờ, của hotel manager quản lý
        $refunds = RefundRequest::where('type', 'offline')
            ->where('status', 'pending')
            ->whereHas('booking.booking_details.room', function ($query) use ($manager) {
                $query->where('hotel_id', $manager->hotel_id);
            })
            ->with(['booking.user', 'booking.booking_details.room'])
            ->latest()
            ->get();

        return view('manager.showCancel', compact('refunds', 'name', 'hotelName'));
    }

    public function approveCancel(RefundRequest $refund)
    {
        $manager = Auth::user();
        $hotelId = $manager->hotel_id;

        // Kiểm tra xem có quyền xử lý đơn này không
        if (!$refund->booking->booking_details->first()?->room->hotel_id === $hotelId) {
            abort(403, 'Bạn không có quyền xử lý đơn này.');
        }
        if ($refund->status !== 'pending') {
            return back()->with('error', 'Yêu cầu đã được xử lý.');
        }

        // ✅ Cập nhật trạng thái refund
        $refund->update([
            'status' => 'done',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        // ✅ Cập nhật trạng thái đơn đặt phòng
        $booking = $refund->booking;
        $booking->update([
            'status' => 'cancelled',
        ]);
        $booking->payments->update([
            'status' => 'cancelled',
        ]);



        // ✅ Trả lại phòng: +1 available_rooms cho mỗi ngày
        foreach ($booking->booking_details as $detail) {
            $start = Carbon::parse($detail->checkin);
            $end = Carbon::parse($detail->checkout);

            for ($date = $start->copy(); $date->lt($end); $date->addDay()) {
                RoomAvailability::where('room_id', $detail->room_id)
                    ->whereDate('date', $date)
                    ->increment('available_rooms', 1);
            }
        }

        $user = $refund->booking->user;
        if ($user && $user->email) {
            Mail::to($user->email)->send(new RefundSuccessMail($refund));
        }

        return back()->with('success', 'Đã duyệt yêu cầu hủy và hoàn tất cập nhật.');
    }
    public function showPayment()
    {
        $manager = Auth::user();
        $hotelId = $manager->hotel_id;
        $name = $manager->name;
        $hotel_name = $manager->hotel->hotel_name ?? 'Khách sạn không xác định';

        if (!$manager || !$manager->hotel_id) {
            abort(403, 'Bạn không có quyền truy cập.');
        }

        // Lấy tất cả các payment có liên kết với booking thuộc về hotel của manager
        $payments = Payment::whereHas('booking.booking_details.room', function ($query) use ($hotelId) {
            $query->where('hotel_id', $hotelId);
        })
            ->with([
                'booking' => function ($query) {
                    $query->select('id', 'guest_name', 'guest_phone', 'guest_email');
                },
                'booking.booking_details.room.images' => function ($query) {
                    $query->limit(1); // lấy 1 ảnh đầu tiên
                }
            ])
            ->latest()
            ->get();


        return view('manager.showPayment', compact('payments', 'name', 'hotel_name'));
    }

    public function showRoom()
    {
        $manager = Auth::user();
        $hotelId = $manager->hotel_id;
        $name = $manager->name;
        $hotel_name = $manager->hotel->hotel_name ?? 'Khách sạn không xác định';

        if (!$manager || !$manager->hotel_id) {
            abort(403, 'Bạn không có quyền truy cập.');
        }

        $hotelId = $manager->hotel_id;

        $rooms = Room::where('hotel_id', $hotelId)
            ->with([
                'images' => function ($query) {
                    $query->limit(1); // chỉ lấy 1 ảnh đầu tiên
                },

            ])
            ->orderBy('id', 'desc') // hoặc bỏ nếu không cần sắp xếp
            ->get();

        return view('manager.roomList', compact('rooms', 'name', 'hotel_name'));
    }
    public function showGuest()
    {
        $manager = Auth::user();
        $hotelId = $manager->hotel_id;
        $name = $manager->name;
        $hotel_name = $manager->hotel->hotel_name ?? 'Khách sạn không xác định';

        if (!$manager || !$manager->hotel_id) {
            abort(403, 'Bạn không có quyền truy cập.');
        }

        $today = Carbon::today();

        $bookings = Booking::where('status', 'confirmed') // ✅ THÊM điều kiện status
            ->whereHas('booking_details', function ($q) use ($hotelId, $today) {
                $q->whereHas('room', function ($q2) use ($hotelId) {
                    $q2->where('hotel_id', $hotelId);
                })
                    ->whereDate('checkin', '<=', $today)
                    ->whereDate('checkout', '>', $today);
            })
            ->with([
                'booking_details' => function ($q) use ($hotelId, $today) {
                    $q->whereHas('room', function ($q2) use ($hotelId) {
                        $q2->where('hotel_id', $hotelId);
                    })
                        ->whereDate('checkin', '<=', $today)
                        ->whereDate('checkout', '>', $today)
                        ->with(['room.images' => function ($q) {
                            $q->limit(1);
                        }]);
                }
            ])
            ->latest()
            ->get();

        return view('manager.guestList', compact('bookings', 'name', 'hotel_name'));
    }
    public function showFeedback()
    {
        $manager = Auth::user();

        if (!$manager || !$manager->hotel_id) {
            abort(403, 'Không có quyền truy cập');
        }

        $hotelId = $manager->hotel_id;
        $name = $manager->name;
        $hotel_name = $manager->hotel->hotel_name ?? 'Khách sạn không xác định';

        // Lấy các booking có phòng thuộc khách sạn của manager
        $bookings = Booking::whereHas('booking_details.room', function ($query) use ($hotelId) {
            $query->where('hotel_id', $hotelId);
        })
            ->with(['booking_details.room.images', 'user']) // load thêm room.images nếu cần hiển thị ảnh
            ->get();

        // Lấy feedback gắn với từng phòng cụ thể (booking_detail_id)
        $feedbacksByDetail = Feedback::where('hotel_id', $hotelId)
            ->get()
            ->groupBy('booking_detail_id');

        return view('manager.showFeedback', compact('bookings', 'feedbacksByDetail', 'name', 'hotel_name'));
    }


    public function showMaintenanceForm()
    {
        $manager = Auth::user();
        $hotelId = $manager->hotel_id;
        $name = $manager->name;
        $hotel_name = $manager->hotel->hotel_name ?? 'Khách sạn không xác định';

        if (!$manager || !$manager->hotel_id) {
            abort(403, 'Không có quyền truy cập');
        }

        $rooms = Room::where('hotel_id', $manager->hotel_id)->get();

        return view('manager.showMaintenance', compact('rooms', 'name', 'hotel_name'));
    }
    public function setMaintenance(Request $request)
    {
        $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $roomId = $request->room_id;
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        // Tạo khoảng ngày
        $dates = CarbonPeriod::create($startDate, $endDate);

        foreach ($dates as $date) {
            RoomAvailability::where('room_id', $roomId)
                ->whereDate('date', $date->format('Y-m-d'))
                ->update(['is_available' => false]);
        }

        return back()->with('success', 'Đã cập nhật bảo trì phòng từ ngày ' . $startDate->format('d/m') . ' đến ' . $endDate->format('d/m'));
    }
    public function showChart()
    {
        $manager = Auth::user();
        $hotelId = $manager->hotel_id;
        $name = $manager->name;
        $hotel_name = $manager->hotel->hotel_name ?? 'Khách sạn không xác định';

        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfToday = Carbon::now();

        // Doanh thu theo ngày trong tháng
        $dailyRevenue = Payment::selectRaw('DATE(created_at) as day, SUM(amount) as total')
            ->whereBetween('created_at', [$startOfMonth, $endOfToday])
            ->where('status', 'success')
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->map(function ($item) {
                return [
                    'day' => Carbon::parse($item->day)->format('d'),
                    'total' => $item->total,
                ];
            });

        // Lượt đặt phòng theo ngày trong tháng
        $dailyBookings = BookingDetail::selectRaw('DATE(checkin) as day, COUNT(*) as total')
            ->whereHas('booking', function ($q) use ($hotelId) {
                $q->where('hotel_id', $hotelId);
            })
            ->whereBetween('checkin', [$startOfMonth, $endOfToday])
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->map(function ($item) {
                return [
                    'day' => Carbon::parse($item->day)->format('d'),
                    'total' => $item->total,
                ];
            });

        // Thống kê nhanh
        $revenueToday = Payment::whereDate('created_at', $today)
            ->where('status', 'success')
            ->sum('amount');

        $revenueThisMonth = Payment::whereBetween('created_at', [$startOfMonth, $endOfToday])
            ->where('status', 'success')
            ->sum('amount');

        $bookingCountToday = Booking::whereHas('booking_details.room', function ($query) use ($hotelId) {
            $query->where('hotel_id', $hotelId);
        })
            ->whereDate('created_at', $today)
            ->count();

        $bookingCountThisMonth = Booking::whereHas('booking_details.room', function ($query) use ($hotelId) {
            $query->where('hotel_id', $hotelId);
        })
            ->whereBetween('created_at', [$startOfMonth, $endOfToday])
            ->count();

        $cancelledCountThisMonth = Booking::whereHas('booking_details.room', function ($query) use ($hotelId) {
            $query->where('hotel_id', $hotelId);
        })
            ->where('status', 'cancelled')
            ->whereBetween('created_at', [$startOfMonth, $endOfToday])
            ->count();

        $userCountToday = Booking::whereHas('booking_details.room', function ($query) use ($hotelId) {
            $query->where('hotel_id', $hotelId);
        })
            ->whereDate('created_at', $today)
            ->distinct('user_id')
            ->count('user_id');

        $userCountThisMonth = Booking::whereHas('booking_details.room', function ($query) use ($hotelId) {
            $query->where('hotel_id', $hotelId);
        })
            ->whereBetween('created_at', [$startOfMonth, $endOfToday])
            ->distinct('user_id')
            ->count('user_id');

        $avgRatingThisMonth = Feedback::where('hotel_id', $hotelId)
            ->whereBetween('created_at', [$startOfMonth, $endOfToday])
            ->avg('rating') ?? 0;

        return view('manager.showChart', compact(
            'dailyRevenue',
            'dailyBookings',
            'revenueToday',
            'revenueThisMonth',
            'bookingCountToday',
            'bookingCountThisMonth',
            'cancelledCountThisMonth',
            'userCountToday',
            'userCountThisMonth',
            'avgRatingThisMonth',
            'name',
            'hotel_name'
        ));
    }
    public function updateBookingDetail(Request $request, $id)
    {
        $request->validate([
            'payment_status' => 'required|in:unpaid,paid',
        ]);

        DB::beginTransaction();

        try {
            $detail = BookingDetail::findOrFail($id);
            $detail->payment_status = $request->payment_status;
            $detail->save();

            // Kiểm tra tất cả các booking_detail của đơn này
            $allPaid = BookingDetail::where('booking_id', $detail->booking_id)
                ->where('payment_status', '!=', 'paid')
                ->doesntExist();

            if ($allPaid) {
                Booking::where('id', $detail->booking_id)->update([
                    'payment_status' => 'paid',
                ]);
                Payment::where('booking_id', $detail->booking_id)->update([
                    'status' => 'success',
                ]);
            }

            DB::commit();

            return back()->with('success', 'Cập nhật trạng thái thanh toán thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi cập nhật thanh toán:', ['error' => $e->getMessage()]);
            return back()->with('error', 'Đã xảy ra lỗi khi cập nhật.');
        }
    }
    public function editRoom($id)
    {
        $manager = Auth::user();
        $hotelId = $manager->hotel_id;
        $name = $manager->name;
        $hotel_name = $manager->hotel->hotel_name ?? 'Khách sạn không xác định';
        $room = Room::findOrFail($id);
        return view('manager.editRoom', compact('room', 'name', 'hotel_name'));
    }
    public function deleteImage($id)
    {
        $image = RoomImage::findOrFail($id);

        // Xoá file vật lý
        $path = public_path('roomImg/' . $image->image_path);
        if (file_exists($path)) {
            unlink($path);
        }

        // Xoá record trong DB
        $image->delete();

        return back()->with('success', 'Đã xoá ảnh thành công!');
    }
    public function updateRoom(Request $request, $id)
    {
        $room = Room::findOrFail($id);

        // Validate dữ liệu
        $request->validate([
            'room_name' => 'required|string|max:255',
            'type' => 'required|string|max:100',
            'price' => 'required|numeric|min:1',
            'capacity' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        // Cập nhật thông tin phòng
        $room->update([
            'room_name' => $request->room_name,
            'type' => $request->type,
            'price' => $request->price,
            'capacity' => $request->capacity,
            'description' => $request->description,
        ]);

        // Nếu có ảnh mới được upload
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $imageFile) {
                $fileName = time() . '_' . $imageFile->getClientOriginalName();
                $imageFile->move(public_path('roomImg'), $fileName);

                RoomImage::create([
                    'room_id' => $room->id,
                    'image_path' => $fileName,
                ]);
            }
        }

        return redirect()->route('manager.rooms.edit', $room->id)->with('success', 'Cập nhật phòng thành công!');
    }
    public function deleteRoom($id)
    {
        try {
            $room = Room::findOrFail($id);
            $room->delete();
        } catch (\Exception $e) {
            Log::error('Error deleting room: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Xóa phòng thất bại. Vui lòng thử lại sau.']);
        }

        return redirect()->back()->with('success', 'Xóa phòng thành công.');
    }
    public function createRoom()
    {
        $manager = Auth::user();
        $hotelId = $manager->hotel_id;
        $name = $manager->name;
        $hotel_name = $manager->hotel->hotel_name ?? 'Khách sạn không xác định';

        return view('manager.createRoom', compact('name', 'hotel_name'));
    }
    public function addRoom(Request $request)
    {
        try {
            $manager = Auth::user(); // Lấy user hiện tại
            $hotelId = $manager->hotel_id;

            $request->validate([

                'room_name' => 'required|string|max:255',
                'room_image.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
                'price' => 'required|numeric|min:0',
                'capacity' => 'required|integer|min:1',
            ]);

            $room = Room::create([
                'hotel_id'   => $hotelId,
                'room_name'  => $request->room_name,
                'price'      => $request->price,
                'description' => $request->description ?? null,
                'capacity'   => $request->capacity,
                'type'       => $request->room_type,
                'total_rooms' => '1',
                'wifi'       => 'yes',
            ]);

            // ✅ Tự tạo dữ liệu availability cho 6 tháng
            $start = Carbon::today();
            $end = Carbon::today()->addMonths(6);
            $dates = CarbonPeriod::create($start, $end);

            foreach ($dates as $date) {
                RoomAvailability::updateOrCreate(
                    [
                        'room_id' => $room->id,
                        'date' => $date->toDateString(),
                    ],
                    [
                        'available_rooms' => 1,
                        'is_available' => 1,
                        'price_override' => null,
                    ]
                );
            }

            // Xử lý upload ảnh nếu có
            if ($request->hasFile('room_image')) {
                foreach ($request->file('room_image') as $image) {
                    $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                    $image->move(public_path('roomImg'), $filename);

                    RoomImage::create([
                        'room_id' => $room->id,
                        'image_path' => $filename,
                    ]);
                }
            }

            return redirect()->route('manager.rooms.create')->with('success', 'Thêm phòng thành công!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Đã có lỗi xảy ra!');
        }
    }
}
