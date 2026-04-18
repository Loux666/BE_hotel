<base href="/public">
@include('home.css')

<div class="booking-page">
    <div class="booking-header">
        <div class="booking-logo">
            <div class="col-xl-3 col-lg-3 col-md-3 col-sm-3 col logo_section">
                <div class="full">
                    <div class="center-desk">
                        <div class="logo">
                            <a href="/"><img src="images/logo.png" alt="#" /></a>
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
        <span id="hold-timer" >Chúng tôi đang giữ phòng cho bạn: <strong><span id="countdown">20:00</span></strong></span>
    </div>

    <div class="booking-content">
        <div class="booking-form">
            <h2 class="form-title">Thông tin khách hàng</h2>
            <form id="room-booking-form" method="POST" action="{{ route('cart.store.temp') }}">
                @csrf
                <div class="form-group">
                    <label for="fullname" class="form-label">Họ và tên *</label>
                    <input type="text" id="fullname" name="fullname" class="form-input"
                        value="{{ old('fullname', Auth::user()->name ?? '') }}"
                        placeholder="Nhập họ và tên đầy đủ" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="email" class="form-label">Email *</label>
                        <input type="email" id="email" name="email" class="form-input"
                            value="{{ old('email', Auth::user()->email ?? '') }}"
                            placeholder="example@email.com" required>
                    </div>
                    <div class="form-group">
                        <label for="phone" class="form-label">Số điện thoại *</label>
                        <input type="tel" id="phone" name="phone" class="form-input"
                            placeholder="0123456789" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="requests" class="form-label">Yêu cầu đặc biệt</label>
                    <textarea id="requests" name="requests" class="form-input" rows="4" placeholder="Ghi chú thêm (tùy chọn)"></textarea>
                </div>


                @if (session('cart_coupon'))
                    <input type="hidden" name="coupon_id" value="{{ session('cart_coupon.coupon_id') }}">
                    <input type="hidden" name="discount" value="{{ session('cart_coupon.discount') }}">
                @endif

                <div class="submit-form-btn">
                    <button type="submit" class="confirm-btn">Xác nhận thông tin</button>
                </div>
            </form>
        </div>
        

        <div class="booking-summary">
            @if (count($detailedRooms) === 1)
                @php $item = $detailedRooms[0]; @endphp
                <div class="single-room-detail">
                    <div class="summary-header">
                        <h3 class="summary-title">Chi tiết đặt phòng</h3>
                        <div class="date-picker-container">
                            <div class="date-picker-wrapper">
                                <div class="date-section">
                                    <div class="date-label">Nhận phòng</div>
                                    <div class="date-value">{{ \Carbon\Carbon::parse($item['checkin'])->isoFormat('dd,D [tháng] M') }}</div>
                                </div>
                                <div class="arrow-section"><div class="arrow">→</div></div>
                                <div class="date-section">
                                    <div class="date-label">Trả phòng</div>
                                    <div class="date-value">{{ \Carbon\Carbon::parse($item['checkout'])->isoFormat('dd,D [tháng] M') }}</div>
                                </div>
                                <div class="nights-section">
                                    <div class="nights-number">{{ $item['nights'] }}</div>
                                    <div class="nights-label">đêm</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="summary-content">
                        <div class="hotel-booking-info">
                            <img src="{{ asset('hotelImg/' . $item['hotel']->hotel_image) }}" alt="Hotel Image" class="hotel-booking-image">
                            <div class="hotel-booking-details">
                                <h3>{{ $item['hotel']->hotel_name }}</h3>
                                <div class="hotel-location">{{ $item['hotel']->hotel_address }}</div>
                            </div>
                        </div>

                        <div class="room-booking-detail">
                            <div class="room-booking-header">
                                <img src="{{ asset('roomImg/' . ($item['room']->images->first()->image_path ?? 'default.jpg')) }}" alt="Room Image" class="room-booking-image">
                                <div class="room-booking-info">
                                    <h4>{{ $item['room']->room_name }}</h4>
                                    <div class="room-booking-capacity">
                                        👤 {{ $item['room']->capacity }} người 
                                    </div>  
                                    <div class="room-booking-type">
                                        Loại phòng: 
                                        @if($item['room']->room_type === 'family')
                                            Gia đình
                                        @elseif($item['room']->room_type === 'deluxe')
                                            Cao cấp
                                        @elseif($item['room']->room_type === 'standard')
                                            Tiêu chuẩn
                                        @else
                                            Không xác định
                                        @endif
                                    </div>
                                    <div class="room-booking-price">
                                        Giá 1 đêm: {{ number_format($item['pricePerNight'], 0, ',', '.') }} VNĐ
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="price-breakdown">
                            <div class="price-item">
                                <span>Giá phòng ({{ $item['nights'] }} đêm)</span>
                                <span>{{ number_format($item['totalPrice'], 0, ',', '.') }} VNĐ</span>
                            </div>
                            <div class="price-item">
                                <span>Phí dịch vụ</span>
                                <span>{{ number_format($item['service'], 0, ',', '.') }} VNĐ</span>
                            </div>
                            <div class="price-item">
                                <span>Thuế VAT</span>
                                <span>{{ number_format($item['vat'], 0, ',', '.') }} VNĐ</span>
                            </div>
                            <div class="price-item total">
                                <strong>Tạm tính</strong>
                                <span><strong>{{ number_format($item['roomTotal'], 0, ',', '.') }} VNĐ</strong></span>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                {{-- Trường hợp có nhiều phòng --}}
                <div class="summary-header">
                    <h3 class="summary-title">Chi tiết đặt phòng</h3>
                    @foreach($detailedRooms as $item)
                        <div class="room-booking-detail" style="border: 1px solid #ccc; border-radius: 10px; padding: 10px; margin-bottom: 15px;">
                            <div class="room-booking-header">
                                <img src="{{ asset('roomImg/' . ($item['room']->images->first()->image_path ?? 'default.jpg')) }}" alt="Room Image" class="room-booking-image">
                                <div class="room-booking-info">
                                    <h4>{{ $item['room']->room_name }}</h4>
                                    <div>Khách sạn: {{ $item['hotel']->hotel_name }} - {{ $item['hotel']->hotel_address }}</div>
                                    <div>Giá 1 đêm: {{ number_format($item['pricePerNight'], 0, ',', '.') }} VNĐ</div>
                                    <div>Checkin: {{ \Carbon\Carbon::parse($item['checkin'])->format('d/m/Y') }}</div>
                                    <div>Checkout: {{ \Carbon\Carbon::parse($item['checkout'])->format('d/m/Y') }}</div>
                                </div>
                            </div>
                            <div class="price-breakdown">
                                <div class="price-item"><span>Giá phòng</span><span>{{ number_format($item['totalPrice'], 0, ',', '.') }} VNĐ</span></div>
                                <div class="price-item"><span>Phí dịch vụ</span><span>{{ number_format($item['service'], 0, ',', '.') }} VNĐ</span></div>
                                <div class="price-item"><span>VAT</span><span>{{ number_format($item['vat'], 0, ',', '.') }} VNĐ</span></div>
                                <div class="price-item total"><strong>Tạm tính</strong><span><strong>{{ number_format($item['roomTotal'], 0, ',', '.') }} VNĐ</strong></span></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Tổng cộng và coupon --}}
            <div class="final-price-summary" style="padding: 0px 20px 20px 20px">
                @if(isset($couponDiscount) && $couponDiscount > 0)
                    <div class="price-item discount">
                        <span>Ưu đãi (Coupon)</span>
                        <span class="text-success">-{{ number_format($couponDiscount, 0, ',', '.') }} VNĐ</span>
                    </div>
                @endif
                <div class="price-item total">
                    <span>Tổng cộng tất cả:</span>
                    <span>{{ number_format($finalTotal, 0, ',', '.') }} VNĐ</span>
                </div>
            </div>
            <div class="coupon-section" style="padding: 10px 10px 10px 10px">
            <form method="POST" action="{{ route('cart.applyCoupon') }}" class="coupon-input">
                @csrf
                <input type="hidden" name="total_price" value="{{ $finalTotal }}">
                <input 
                    type="text" 
                    name="coupon_code" 
                    placeholder="Nhập mã giảm giá"
                    class="form-control @error('coupon_code') is-invalid @enderror"
                    value="{{ old('coupon_code', session('cart_coupon.code') ?? '') }}"
                >
                <button type="submit" class="coupon-btn">Áp dụng</button>
            </form>

            @if(session('cart_coupon'))
                <div class="mt-2 text-success">
                    Mã "{{ session('cart_coupon.code') }}" đã được áp dụng:
                    <strong>{{ number_format(session('cart_coupon.discount')) }} VNĐ</strong>
                </div>
            @endif

            @if(session('error'))
                <div class="mt-2 text-danger">{{ session('error') }}</div>
            @endif

            @error('coupon_code')
                <div class="mt-2 text-danger">{{ $message }}</div>
            @enderror
        </div>
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

<script>
    let countdownTime = 20 * 60; // 30 phút = 1800 giây
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