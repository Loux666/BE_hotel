<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\Hotel;
use App\Models\RoomImage;
use Illuminate\Support\Facades\DB;
use App\Models\Feedback;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\RoomAvailability;

class RoomController extends Controller
{

    public function create_room()
    {
        return view('admin.create_room');
    }


    public function add_room(Request $request)
    {
        try {
            $request->validate([
                'hotel_id' => 'required|exists:hotels,id',
                'room_name' => 'required|string|max:255',
                'room_image.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
                'price' => 'required|numeric|min:0',
                'capacity' => 'required|integer|min:1',
            ]);

            $room = Room::create([
                'hotel_id'   => $request->hotel_id,
                'room_name'  => $request->room_name,
                'price'      => $request->price,
                'description' => $request->description,
                'capacity'   => $request->capacity,
                'type'       => $request->room_type,
                'total_rooms' => $request->total_rooms ?? 1,
                'wifi'       => $request->wifi,
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
                        'available_rooms' => $room->total_rooms ?? 1,
                        'is_available' => true,
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

            return redirect()->back()->with('message', 'Thêm phòng thành công!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Đã có lỗi xảy ra!');
        }
    }


    public function view_room()
    {
        $data = Room::with('images')->get();
        $hotel = Room::with('hotel')->get();

        return view('admin.view_room', compact('data'));
    }
    public function delete_room($id)
    {
        $room = Room::find($id);
        if ($room) {
            $room->delete();
            return redirect()->back()->with('message', 'Xóa phòng thành công!');
        } else {
            return redirect()->back()->with('error', 'Đã có lỗi xảy ra!');
        }
    }
    public function update_room($id)
    {
        $room = Room::with(['hotel', 'images'])->find($id);
        if ($room) {
            return view('admin.update_room', compact('room'));
        }
    }
    public function edit_room(Request $request, $id)
    {
        try {
            $request->validate([
                'room_name' => 'required|string|max:255',
                'room_image.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
                'price' => 'required|numeric|min:0',
                'capacity' => 'required|integer|min:1',
                'total_rooms' => 'nullable|integer|min:1', // Thêm dòng này nếu muốn sửa số lượng
            ]);

            $room = Room::find($id);
            if ($room) {
                $room->room_name = $request->input('room_name');
                $room->price = $request->input('price');
                $room->description = $request->input('description');
                $room->capacity = $request->input('capacity');
                $room->type = $request->input('room_type');
                $room->wifi = $request->input('wifi');
                $room->status = $request->input('status');

                if ($request->has('total_rooms')) {
                    $room->total_rooms = $request->input('total_rooms');
                }

                $room->save();

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

                // Cập nhật lại available_rooms nếu total_rooms được thay đổi
                if ($request->has('total_rooms')) {
                    $start = \Carbon\Carbon::today();
                    $end = \Carbon\Carbon::today()->addMonths(6);
                    $dates = \Carbon\CarbonPeriod::create($start, $end);

                    foreach ($dates as $date) {
                        \App\Models\RoomAvailability::updateOrCreate(
                            [
                                'room_id' => $room->id,
                                'date' => $date->toDateString(),
                            ],
                            [
                                'available_rooms' => $room->total_rooms ?? 1,
                                'is_available' => true,
                            ]
                        );
                    }
                }

                return redirect()->back()->with('message', 'Cập nhật phòng thành công!');
            }

            return redirect()->back()->with('error', 'Không tìm thấy phòng!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Đã có lỗi xảy ra!');
        }
    }


    public function showAvailableRoomByCity(Request $request)
    {
        $location = $request->query('location');
        $guests = $request->query('guests');
        $roomType = $request->query('room_type');
        $checkin = $request->query('checkin');
        $checkout = $request->query('checkout');

        // Kiểm tra đầu vào
        if (!$location || !$guests || !$checkin || !$checkout) {
            return redirect()->back()->with('error', 'Vui lòng điền đầy đủ thông tin!');
        }
        if (Carbon::parse($checkin)->gte(Carbon::parse($checkout))) {
            return redirect()->back()->with('error', 'Ngày checkout phải sau ngày checkin!');
        }

        // Lấy hotel_id từ hotel_name
        $hotel = DB::table('hotels')->where('hotel_name', $location)->first();
        if (!$hotel) {
            return redirect()->back()->with('error', 'Không tìm thấy khách sạn!');
        }
        $hotelId = $hotel->id;

        //  Lấy 6 ảnh banner ngẫu nhiên từ tất cả các phòng của khách sạn
        $bannerImages = DB::table('room_images')
            ->join('rooms', 'room_images.room_id', '=', 'rooms.id')
            ->where('rooms.hotel_id', $hotelId)
            ->inRandomOrder()
            ->limit(6)
            ->pluck('room_images.image_path');

        //  Lọc danh sách phòng khả dụng theo yêu cầu


        $rooms = DB::table('rooms')
            ->join('room_availabilities', 'rooms.id', '=', 'room_availabilities.room_id')
            ->where('rooms.hotel_id', $hotelId)
            ->where('rooms.capacity', '>=', $guests)
            ->where('rooms.status', 'active')
            ->when($roomType, function ($query) use ($roomType) {
                $query->where('rooms.type', $roomType);
            })
            ->where('room_availabilities.date', '>=', $checkin)
            ->where('room_availabilities.date', '<', $checkout) // exclude checkout
            ->where('room_availabilities.is_available', 1)
            ->where('room_availabilities.available_rooms', '>=', 1)
            ->groupBy('rooms.id', 'rooms.room_name', 'rooms.type', 'rooms.capacity', 'rooms.price')
            ->havingRaw('COUNT(DISTINCT room_availabilities.date) = ?', [
                Carbon::parse($checkin)->diffInDays(Carbon::parse($checkout))
            ])
            ->select(
                'rooms.id as room_id',
                'rooms.room_name',
                'rooms.type as room_type',
                'rooms.capacity as max_guests',
                'rooms.price'
            )
            ->get();
        $feedbacks = Feedback::where('hotel_id', $hotelId)
            ->with(['user', 'bookingDetail.room']) // nếu cần load thêm thông tin
            ->latest()
            ->get();


        //  Gắn ảnh cho từng phòng
        foreach ($rooms as $room) {
            $images = DB::table('room_images')
                ->where('room_id', $room->room_id)
                ->inRandomOrder()
                ->limit(3)
                ->pluck('image_path');

            $room->images = $images;
        }

        return view('home.room_available_by_hotel', [
            'rooms' => $rooms,
            'location' => $location,
            'guests' => $guests,
            'checkin' => $checkin,
            'checkout' => $checkout,
            'room_type' => $roomType,
            'bannerImages' => $bannerImages,
            'hotel' => $hotel,
            'feedbacks' => $feedbacks,
        ])->with('message', 'Danh sách phòng đã được tải!');
    }






    public function roomsByHotel($id)
    {

        $hotel = Hotel::findOrFail($id);


        $rooms = Room::where('hotel_id', $id)
            ->with(['images' => function ($query) {
                $query->orderBy('id')->limit(1);
            }])
            ->get();


        $roomIds = $rooms->pluck('id');
        $roomImages = RoomImage::whereIn('room_id', $roomIds)
            ->inRandomOrder()
            ->take(6)
            ->get();
        $feedbacks = Feedback::where('hotel_id', $id)
            ->with(['user', 'bookingDetail.room']) // nếu cần load thêm thông tin
            ->latest()
            ->get();

        return view('home.rooms_by_hotel', compact('hotel', 'rooms', 'roomImages', 'feedbacks'));
    }
    public function unavailableToBook()
    {
        return view('home.no_room');
    }
}
