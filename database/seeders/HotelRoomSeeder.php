<?php

namespace Database\Seeders;

use App\Models\Hotel;
use App\Models\Room;
use Illuminate\Database\Seeder;

class HotelRoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lấy những khách sạn chưa có phòng nào
        $hotels = Hotel::doesntHave('rooms')->get();

        if ($hotels->isEmpty()) {
            $this->command->info('Tất cả khách sạn đã có phòng.');
            return;
        }

        foreach ($hotels as $hotel) {
            $this->command->info("Đang tạo phòng cho khách sạn: {$hotel->hotel_name}");
            
            for ($i = 1; $i <= 5; $i++) {
                Room::create([
                    'room_name' => "Phòng " . $i . " - " . $hotel->hotel_name,
                    'price' => rand(200, 1500) * 1000, // Giá từ 200k đến 1.5tr
                    'description' => "Mô tả chi tiết cho phòng số " . $i . " tại " . $hotel->hotel_name . ". Phòng được trang bị đầy đủ tiện nghi, không gian thoáng mát.",
                    'wifi' => 'Yes',
                    'capacity' => rand(1, 4), // 1 đến 4 người
                    'type' => ['Standard', 'Deluxe', 'Suite', 'Family Room'][rand(0, 3)],
                    'total_rooms' => rand(1, 10), // Số lượng phòng cùng loại
                    'status' => 'Available',
                    'hotel_id' => $hotel->id,
                    'room_image' => null, // Sẽ cập nhật sau hoặc dùng ảnh mặc định
                ]);
            }
        }

        $this->command->info('Đã tạo thêm phòng thành công cho các khách sạn còn thiếu.');
    }
}
