@include('home.css')


@php
    use Carbon\Carbon;

    $nights = Carbon::parse($checkin)->diffInDays(Carbon::parse($checkout));
    $pricePerNight = $room->price;
    $totalPrice = $nights * $pricePerNight;
    $vat = $totalPrice * 0.10;
    $service = 100000;

    $appliedCoupon = session('applied_coupon');
    $discount = (is_array($appliedCoupon) && isset($appliedCoupon['discount'])) ? $appliedCoupon['discount'] : 0;

    $finalTotal = $totalPrice + $vat + $service - $discount;
@endphp

<div class="booking-page">
    <div class="booking-header">
        <div class="booking-logo">
            <div class="col-xl-3 col-lg-3 col-md-3 col-sm-3 col logo_section">
             <div class="full">
                <div class="center-desk">
                   <div class="logo">
                      <a href="/"><img src="images/logo.png" alt="#"  /></a>
                   </div>
                </div>
             </div>
          </div>
        </div>
        <div class="booking-progress">
            <div class="step active">
                    <div class="step-number">1</div>
                    <div class="step-text">Thông tin khách hàng</div>
                </div>
                <div class="step inactive">
                    <div class="step-number">2</div>
                    <div class="step-text">Chi tiết thanh toán</div>
                </div>
                <div class="step inactive">
                    <div class="step-number">3</div>
                    <div class="step-text">Đã xác nhận đặt phòng!</div>
                </div>
        </div>
        <div class="user-info-button">
            @if (Route::has('login'))
                      @auth
                          <li class="nav-item dropdown" style="padding-right: 10px;">
                              <div class="dropdown">
                                  <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                      {{ Auth::user()->name }}
                                  </button>
                                  <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                      <li><a class="dropdown-item" href="{{ route('profile.show') }}">Thông tin tài khoản</a></li>
                                      <li><hr class="dropdown-divider"></li>
                                      <li>
                                          <form method="POST" action="{{ route('logout') }}">
                                              @csrf
                                              <button type="submit" class="dropdown-item">Đăng xuất</button>
                                          </form>
                                      </li>
                                  </ul>
                              </div>
                          </li>
                      @else
                          <li class="nav-item" style="padding-right: 10px;">
                              <a class="btn btn-primary" style="font-size: 14px;" href="{{ url('login') }}">Đăng nhập</a>
                          </li>
                          @if (Route::has('register'))
                              <li class="nav-item">
                                  <a class="btn btn-success" style="font-size: 14px;" href="{{ url('register') }}">Đăng kí</a>
                              </li>
                          @endif
                      @endauth
                    @endif

        </div>

    </div>

    <div class="booking-holding" style="text-align: center; margin-bottom: 20px;background-color: #bec19f; padding: 10px; border-radius: 5px;">
        <span id="hold-timer" >Chúng tôi đang giữ phòng cho bạn: <strong><span id="countdown">15:00</span></strong></span>
    </div>
    <div class="booking-content">
        <div class="booking-form">
            <h2 class="form-title">Thông tin khách hàng</h2>
            <form id="room-booking-form" class="room-booking-form" method="POST" action="{{ route('booking.storeTmp') }}">
            @csrf

            <div class="form-group">
                <label class="form-label" for="fullname">Họ và tên *</label>
                <input 
                    type="text" 
                    id="guest_name" 
                    name="guest_name" 
                    class="form-input" 
                    placeholder="Nhập họ và tên đầy đủ"
                    value="{{ old('fullname', Auth::user()->name ?? '') }}"
                    required
                >
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="email">Email *</label>
                    <input 
                        type="email" 
                        id="guest_email" 
                        name="guest_email" 
                        class="form-input" 
                        placeholder="example@email.com"
                        value="{{ old('email', Auth::user()->email ?? '') }}"
                        required
                    >
                </div>
                <div class="form-group">
                    <label class="form-label" for="phone">Số điện thoại *</label>
                    <input type="tel" id="guest_phone" name="guest_phone" class="form-input" placeholder="0123456789" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="requests">Yêu cầu đặc biệt</label>
                <textarea id="requests" name="requests" class="form-input" rows="4" placeholder="Ghi chú thêm (tùy chọn)"></textarea>
            </div>

            <!-- Hidden input cho dữ liệu booking -->
            <input type="hidden" name="checkin_date" value="{{ $checkin }}">
            <input type="hidden" name="checkout_date" value="{{ $checkout }}">
            <input type="hidden" name="room_id" value="{{ $room->id }}">
            <input type="hidden" name="room_name" value="{{ $room->room_name }}">
            <input type="hidden" name="room_price" value="{{ $room->price }}">
            <input type="hidden" name="guest_number" value="{{ $guests }}">
            <input type="hidden" name="nights" value="{{ $nights }}">
            <input type="hidden" name="hotel_id" value="{{ $hotel->id }}">
            <input type="hidden" name="total_price" value="{{ $totalPrice + $vat + $service }}">
            

            <!-- Nút xác nhận -->
            <div class="submit-form-btn">
                <button type="submit" id="confirm-btn" class="confirm-btn">Xác nhận thông tin</button>
            </div>
        </form>

            
            
            
        </div>
         <div class="booking-summary">
            <div class="summary-header">
                <h3 class="summary-title">Chi tiết đặt phòng</h3>
                <div class="date-picker-container">
                    <div class="date-picker-wrapper">
                        <div class="date-section">
                            <div class="date-label">Nhận phòng</div>
                            <div class="date-value">{{ \Carbon\Carbon::parse($checkin)->isoFormat('dd, D [tháng] M ') }}</div>
                        </div>

                        <div class="arrow-section">
                            <div class="arrow">→</div>
                        </div>

                        <div class="date-section">
                            <div class="date-label">Trả phòng</div>
                            <div class="date-value">{{ \Carbon\Carbon::parse($checkout)->isoFormat('dd, D [tháng] M ') }}</div>
                        </div>

                        <div class="nights-section">
                            <div class="nights-number">
                                {{ \Carbon\Carbon::parse($checkin)->diffInDays(\Carbon\Carbon::parse($checkout)) }}
                            </div>
                            <div class="nights-label">đêm</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="summary-content">
                <!-- Hotel Info -->
                 <div class="hotel-booking-info">
                    <img src="{{ asset('hotelImg/' . $hotel->hotel_image) }}" alt="Hotel Image" class="hotel-booking-image">
                    <div class="hotel-booking-details">
                        <h3>{{ $hotel->hotel_name }}</h3>
                        {{-- Bạn có thể thêm đánh giá nếu có --}}
                        <div class="hotel-rating">
                            @if($hotel->hotel_rating ==5 || $hotel->hotel_rating==null)
                            ⭐⭐⭐⭐⭐
                            @elseif($hotel->hotel_rating <5 && $hotel->hotel_rating >=4 )
                                ⭐⭐⭐⭐
                            @elseif($hotel->hotel_rating <4 && $hotel->hotel_rating >=3 )  
                                 ⭐⭐⭐
                            @elseif($hotel->hotel_rating <3 && $hotel->hotel_rating >=2 )
                                ⭐⭐
                            @else 
                                ⭐⭐
                            @endif
                        </div>
                        <div class="hotel-location">{{ $hotel->hotel_address }}</div>
                    </div>
                </div>
                <!-- Room Info -->
                <div class="room-booking-detail">
                    <div class="room-booking-header">
                        <img src="{{ asset('roomImg/' . ($room->images->first()->image_path ?? 'default.jpg')) }}" alt="Room Image" class="room-booking-image">
                        <div class="room-booking-info">
                            <h4>{{ $room->room_name }}</h4>
                            <div class="room-booking-capacity">
                                👤 {{ $room->capacity }} người 
                            </div>  
                            <div class="room-booking-type" >
                                Loại phòng: 
                                @if($room->type === 'family')
                                    Gia đình
                                @elseif($room->type === 'deluxe')
                                    Cao cấp
                                @elseif($room->type === 'standard')
                                    Tiêu chuẩn
                                @else
                                    Không xác định
                                @endif
                            </div>
                            <div class="room-booking-price">
                                Giá 1 đêm (VNĐ): 
                                {{ number_format($room->price, 0, ',', '.') }}

                            </div>
                        
                        </div>
                        
                    </div>
                </div>

                <!-- Price Breakdown -->
                <div class="price-breakdown">
                    <div class="price-item">
                        <span>Giá phòng ({{ $nights }} đêm)</span>
                        <span>{{ number_format($totalPrice, 0, ',', '.') }} VNĐ</span>
                    </div>
                    <div class="price-item">
                        <span>Phí dịch vụ</span>
                        <span>{{ number_format($service, 0, ',', '.') }} VNĐ</span>
                    </div>
                    <div class="price-item">
                        <span>Thuế VAT</span>
                        <span>{{ number_format($vat, 0, ',', '.') }} VNĐ</span>
                    </div>

                    @if(isset($appliedCoupon['code']) && $discount > 0)
                        <div class="price-item discount">
                            <span>Ưu đãi ({{ $appliedCoupon['code'] }})</span>
                            <span class="text-success">-{{ number_format($discount, 0, ',', '.') }} VNĐ</span>
                        </div>
                    @endif

                    <div class="price-item total" style="font-weight: bold; font-size: 18px;">
                        <span>Tổng cộng</span>
                        <span>{{ number_format($finalTotal, 0, ',', '.') }} VNĐ</span>
                    </div>
                </div>


                <!-- Coupon Form -->
                <div class="coupon-section mt-3">
                    <form method="POST" action="{{ route('booking.applyCoupon') }}" class="coupon-input" id="apply-coupon-form">
                        @csrf
                        <input type="hidden" name="total_price" value="{{ $totalPrice + $vat + $service }}">
                        <input type="text" name="coupon_code" placeholder="Nhập mã giảm giá" class="form-control @error('coupon_code') is-invalid @enderror">
                        <button type="submit" class="coupon-btn mt-2">Áp dụng</button>
                    </form>

                    @if(isset($appliedCoupon['code']) && $discount > 0)
                        <div class="mt-2 text-success">
                            Mã "{{ $appliedCoupon['code'] }}" đã được áp dụng:
                            <strong>{{ number_format($discount, 0, ',', '.') }}đ</strong>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mt-2 text-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    @error('coupon_code')
                        <div class="mt-2 text-danger">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
                            </div>
                    </div>
                </div>

                <div class="modal fade" id="expiredModal"
                tabindex="-1"
                aria-labelledby="expiredModalLabel"
                aria-hidden="true"
                data-bs-backdrop="static"
                data-bs-keyboard="false">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="expiredModalLabel">⏰ Hết thời gian giữ phòng</h5>
                    </div>
                    <div class="modal-body">
                        Rất tiếc, bạn đã vượt quá thời gian giữ phòng tạm. Vui lòng quay lại và chọn phòng khác.
                    </div>
                    <div class="modal-footer">
                        <button onclick="window.close()" class="btn btn-primary">Đóng tab & quay lại chọn phòng</button>
                    </div>
                    </div>
                </div>
                </div>

<script> //Validate
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('room-booking-form');
        const bookingBtn = document.getElementById('confirm-btn');

        bookingBtn.addEventListener('click', function () {
            // Xóa lỗi cũ
            document.querySelectorAll('.error-message').forEach(el => el.textContent = '');

            let isValid = true;

            const fullName = document.getElementById('fullname');
            const email = document.getElementById('email');
            const phone = document.getElementById('phone');

            // Validate họ tên
            if (!fullName.value.trim()) {
                document.getElementById('fullname-error').textContent = 'Vui lòng nhập họ và tên.';
                isValid = false;
            } else {
                document.getElementById('fullname-error').textContent = ''; // Xóa lỗi nếu hợp lệ
            }

            // Validate email
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;v
            if (!email.value.trim()) {
                document.getElementById('email-error').textContent = 'Vui lòng nhập email.';
                isValid = false;
            } else if (!emailRegex.test(email.value.trim())) {
                document.getElementById('email-error').textContent = 'Email không hợp lệ.';
                isValid = false;
            } else {
                document.getElementById('email-error').textContent = '';
            }

            // Validate số điện thoại
            const phoneRegex = /^[0-9]{9,11}$/;
            if (!phone.value.trim()) {
                document.getElementById('phone-error').textContent = 'Vui lòng nhập số điện thoại.';
                isValid = false;
            } else if (!phoneRegex.test(phone.value.trim())) {
                document.getElementById('phone-error').textContent = 'Số điện thoại phải gồm 9-11 chữ số.';
                isValid = false;
            } else {
                document.getElementById('phone-error').textContent = '';
            }


            if (isValid) {
                form.submit(); // Submit nếu ok
            }
        });
    });
</script>

<script>
    let countdownTime = 15 * 60; // 30 phút = 1800 giây
    const countdownEl = document.getElementById('countdown');
    const submitBtn = document.getElementById('submitBookingBtn');

    const timer = setInterval(() => {
        const minutes = Math.floor(countdownTime / 60);
        const seconds = countdownTime % 60;

        countdownEl.textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;

        if (countdownTime <= 0) {
            clearInterval(timer);

            // Disable nút submit
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.classList.add('btn-secondary');
                submitBtn.classList.remove('btn-primary'); // hoặc class cũ
                submitBtn.innerText = 'Hết thời gian giữ phòng';
            }

            // Hiện modal hết hạn
            const modal = new bootstrap.Modal(document.getElementById('expiredModal'));
            modal.show();
        }

        countdownTime--;
    }, 1000);
</script>


