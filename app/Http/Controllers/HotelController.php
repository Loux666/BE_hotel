<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\Hotel;
use App\Models\RoomImage;
use App\Models\Feedback;
use Illuminate\Support\Facades\DB;

class HotelController extends Controller
{


    public function create_hotel()
    {
        return view('admin.create_hotel');
    }

    public function view_hotel()
    {
        $data = Hotel::all();
        return view('admin.view_hotel', compact('data'));
    }
    public function viewRooms($id)
    {
        $hotel = Hotel::findOrFail($id);

        // Lấy tất cả room của hotel có id là $id
        $rooms = Room::where('hotel_id', $id)->get();

        return view('admin.all_room', compact('hotel', 'rooms'));
    }


    public function delete_hotel($id)
    {
        $hotel = Hotel::find($id);
        if ($hotel) {
            $hotel->delete();
            return redirect()->back()->with('message', 'Xóa khách sạn thành công!');
        } else {
            return redirect()->back()->with('error', 'Khách sạn không tồn tại!');
        }
    }



    public function update_hotel($id)
    {
        $hotel = Hotel::find($id);
        if ($hotel) {
            return view('admin.update_hotel', compact('hotel'));
        } else {
            return redirect()->back()->with('error', 'Hotel not found!');
        }
    }
    public function edit_hotel(Request $request, $id)
    {
        $request->validate([
            'hotel_name' => 'required|string|max:255',
            'image' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
            'address' => 'required|string|max:255',
            'phone' => 'required|string|max:15',
            'email' => 'required|email|max:255',

        ]);
        $hotel = Hotel::find($id);
        if ($hotel) {
            $hotel->hotel_name = $request->input('hotel_name');

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imagename = time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('hotelImg'), $imagename);

                $hotel->hotel_image = $imagename;
            }
            $hotel->hotel_city = $request->input('city');
            $hotel->hotel_address = $request->input('address');
            $hotel->hotel_description = $request->input('description');
            $hotel->hotel_phone = $request->input('phone');
            $hotel->hotel_email = $request->input('email');

            $hotel->save();

            return redirect()->back()->with('message', 'Cập nhật khách sạn thành công!');
        } else {
            return redirect()->back()->with('error', 'Khách sạn không tồn tại!');
        }
    }






    public function add_hotel(Request $request)
    {
        $request->validate([
            'hotel_name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048', // Cho phép ảnh có thể null
            'address' => 'required|string|max:255',
            'phone' => 'required|string|max:15',
            'email' => 'required|email|max:255',
        ]);

        $hotel = new Hotel();
        $hotel->hotel_name = $request->input('hotel_name');
        $hotel->hotel_city = $request->input('city');
        $hotel->hotel_address = $request->input('address');
        $hotel->hotel_description = $request->input('description');
        $hotel->hotel_phone = $request->input('phone');
        $hotel->hotel_email = $request->input('email');

        // Kiểm tra nếu có ảnh thì lưu ảnh, nếu không thì để trống
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imagename = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('hotelImg'), $imagename);
            $hotel->hotel_image = $imagename;
        } else {
            $hotel->hotel_image = null; // Nếu không có ảnh, để trống
        }

        $hotel->save();

        return redirect()->back()->with('message', 'Thêm khách sạn thành công!');
    }

    public function searchHotels(Request $request)
    {
        $query = $request->query('query');

        $hotels = Hotel::where('hotel_name', 'like', "%{$query}%")
            ->limit(5)
            ->get(['id', 'hotel_name', 'hotel_image', 'hotel_city']);

        return response()->json($hotels);
    }



    public function searchForm(Request $request)
    {
        $hotelId = $request->query('hotel_id');

        $data = Hotel::when($hotelId, function ($query) use ($hotelId) {
            $query->where('id', $hotelId);
        })->get();

        return view('admin.view_hotel', compact('data'));
    }
    public function averagePrice()
    {
        $hotels = Hotel::withAvg('rooms', 'price')->get(['id', 'hotel_name']);

        return response()->json($hotels);
    }

    public function show($id)
    {
        $hotel = Hotel::findOrFail($id);


        // Lấy danh sách các phòng của khách sạn, mỗi phòng lấy 1 ảnh
        $rooms = Room::where('hotel_id', $id)
            ->with(['images' => function ($query) {
                $query->orderBy('id')->limit(1);
            }])
            ->get();

        // Lấy 6 ảnh phòng bất kỳ từ các phòng thuộc khách sạn này
        $roomIds = $rooms->pluck('id'); // tận dụng luôn danh sách đã lấy ở trên
        $roomImages = RoomImage::whereIn('room_id', $roomIds)
            ->inRandomOrder()
            ->take(6)
            ->get();

        return view('home.show', compact('hotel', 'rooms', 'roomImages'));
    }


    public function listByCity(Request $request, $city)
    {
        $city = urldecode($city);

        $minPrice = $request->input('min_price');
        $maxPrice = $request->input('max_price');

        $hotels = Hotel::where('hotel_city', $city)
            ->with('rooms') // lấy phòng để tính trung bình giá
            ->get()
            ->map(function ($hotel) {
                // Tính trung bình giá phòng
                $avgPrice = $hotel->rooms->avg('price') ?? 0;
                $hotel->average_price = round($avgPrice);

                // Tính trung bình rating từ bảng feedbacks
                $avgRating = Feedback::where('hotel_id', $hotel->id)->avg('rating') ?? 0;
                $hotel->average_rating = round($avgRating, 1);

                return $hotel;
            })
            ->filter(function ($hotel) use ($minPrice, $maxPrice) {
                if (!is_null($minPrice) && $hotel->average_price < $minPrice) {
                    return false;
                }
                if (!is_null($maxPrice) && $hotel->average_price > $maxPrice) {
                    return false;
                }
                return true;
            })
            ->values(); // reset lại index sau filter

        return view('home.hotels_by_city', compact('hotels', 'city'));
    }

    public function searchHotelsByCity(Request $request, $city)
    {
        $query = $request->input('query');

        $results = Hotel::where('hotel_city', urldecode($city))
            ->where(function ($q) use ($query) {
                $q->where('hotel_name', 'LIKE', '%' . $query . '%')
                    ->orWhere('hotel_address', 'LIKE', '%' . $query . '%');
            })
            ->limit(10)
            ->get(['id', 'hotel_name', 'hotel_address']);

        return response()->json($results);
    }
}
