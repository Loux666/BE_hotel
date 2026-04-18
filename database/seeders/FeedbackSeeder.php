<?php

namespace Database\Seeders;

use App\Models\Feedback;
use App\Models\Hotel;
use App\Models\User;
use Illuminate\Database\Seeder;

class FeedbackSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hotels = Hotel::all();
        $users = User::all();

        if ($users->isEmpty()) {
            User::create([
                'name' => 'Quang Đặng',
                'email' => 'quang@gmail.com',
                'password' => bcrypt('12345678'),
                'phone' => '0987654321',
                'usertype' => 'user'
            ]);
            User::create([
                'name' => 'Minh Anh',
                'email' => 'minhanh@gmail.com',
                'password' => bcrypt('12345678'),
                'phone' => '0123456789',
                'usertype' => 'user'
            ]);
            $users = User::all();
        }

        $comments = [
            "Khách sạn rất tuyệt vời, nhân viên phục vụ rất tận tình và chu đáo.",
            "Phòng ốc sạch sẽ, không gian thoáng mát, view nhìn ra thành phố rất đẹp.",
            "Mức giá cực kỳ hợp lý so với chất lượng dịch vụ. Tôi sẽ còn quay lại.",
            "Vị trí rất thuận tiện cho việc đi lại và tham quan các địa điểm nổi tiếng.",
            "Bữa sáng tại khách sạn rất ngon và đa dạng món ăn. Không gian rất yên tĩnh.",
            "Thủ tục nhận phòng và trả phòng rất nhanh gọn. Nhân viên thân thiện.",
            "Trải nghiệm tuyệt vời! Giường ngủ êm ái, wifi mạnh, mọi thứ đều tốt.",
            "Hồ bơi sạch và đẹp. Dịch vụ spa cũng rất chuyên nghiệp.",
            "Tôi rất thích phong cách thiết kế của khách sạn này, rất sang trọng.",
            "Gần trung tâm nhưng không hề bị ồn ào, rất hợp để nghỉ dưỡng."
        ];

        foreach ($hotels as $hotel) {
            // Tạo ngẫu nhiên 3-6 feedback cho mỗi khách sạn
            $numFeedbacks = rand(3, 6);
            
            for ($i = 0; $i < $numFeedbacks; $i++) {
                // Thử tìm một booking detail của khách sạn này để gán (nếu có)
                $bookingDetail = \App\Models\BookingDetail::where('hotel_id', $hotel->id)->first();
                
                if ($bookingDetail) {
                    Feedback::create([
                        'user_id' => $users->random()->id,
                        'hotel_id' => $hotel->id,
                        'content' => $comments[array_rand($comments)],
                        'rating' => rand(4, 5),
                        'booking_id' => $bookingDetail->booking_id,
                        'booking_detail_id' => $bookingDetail->id,
                    ]);
                } else {
                    // Nếu không có booking, có thể tạo dummy hoặc bỏ qua tùy logic
                    // Ở đây tôi sẽ bỏ qua nếu không có booking để tránh lỗi Foreign Key
                    continue;
                }
            }
            
            $this->command->info("Đã tạo feedback cho khách sạn: {$hotel->hotel_name}");
        }

        $this->command->info('Đã hoàn thành seeding feedback.');
    }
}
