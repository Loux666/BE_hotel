<?php

namespace Database\Seeders;

use App\Models\Hotel;
use Illuminate\Database\Seeder;

class UpdateHotelRatingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hotels = Hotel::all();

        foreach ($hotels as $hotel) {
            // Gán rating ngẫu nhiên từ 3.5 đến 5.0
            $rating = rand(35, 50) / 10;
            $hotel->update(['hotel_rating' => $rating]);
            $this->command->info("Đã cập nhật rating cho {$hotel->hotel_name}: {$rating}");
        }

        $this->command->info('Đã cập nhật rating cho tất cả khách sạn thành công.');
    }
}
