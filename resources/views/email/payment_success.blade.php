<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác Nhận Đặt Phòng</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
            line-height: 1.6;
        }
        
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .header {
            background-color: #2c3e50;
            padding: 30px;
            text-align: center;
            color: white;
        }
        
        .header h1 {
            font-size: 24px;
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        .content {
            padding: 30px;
        }
        
        h2 {
            color: #2c3e50;
            font-size: 20px;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        p {
            color: #444;
            margin-bottom: 15px;
            font-size: 16px;
        }
        
        strong {
            color: #2c3e50;
            font-weight: 600;
        }
        
        .booking-info {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .total-amount {
            background-color: #e8f4fd;
            border: 1px solid #bee5eb;
            border-radius: 6px;
            padding: 15px;
            text-align: center;
            margin: 20px 0;
        }
        
        .total-amount p {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .footer {
            background-color: #f8f9fa;
            padding: 20px 30px;
            text-align: center;
            border-top: 1px solid #e9ecef;
        }
        
        .footer p {
            color: #666;
            margin: 0;
            font-size: 14px;
        }
        
        @media (max-width: 600px) {
            .email-container {
                margin: 10px;
            }
            
            .header, .content, .footer {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>Xác Nhận Đặt Phòng</h1>
        </div>
        
        <div class="content">
            <h2>Chào {{ $booking->guest_name }},</h2>

            @php
                $detail = $booking->booking_details->first(); // Lấy booking detail đầu tiên
            @endphp

            @if ($detail && $detail->hotel)
                <p>Bạn đã đặt phòng thành công tại khách sạn <strong>{{ $detail->hotel->hotel_name }}</strong>.</p>
            @else
                <p>Bạn đã đặt phòng thành công.</p>
            @endif
            
            @if ($detail)
                <div class="booking-info">
                    <p><strong>Thông tin đặt phòng:</strong></p>
                    <p>Tên phòng: {{ $detail->room_name }}</p>
                    <p>Check-in: {{ $detail->checkin }}</p>
                    <p>Check-out: {{ $detail->checkout }}</p>
                    <p>Giá 1 đêm: {{ number_format($detail->price_per_night) }} VND</p>
                    <p style="margin-bottom: 0;">Số lượng: {{ $detail->quantity }}</p>
                </div>
            @endif
            
            <div class="total-amount">
                <p><strong>Tổng tiền:</strong> {{ number_format($booking->total_price) }} VND</p>
            </div>
            
            <p>Cảm ơn bạn đã sử dụng dịch vụ của chúng tôi!</p>
        </div>
        
        <div class="footer">
            <p>© 2025 - Hệ thống đặt phòng khách sạn StayGo</p>
        </div>
    </div>
</body>

</html>