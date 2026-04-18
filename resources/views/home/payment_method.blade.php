
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
            <div class="step inactive">
                <div class="step-number">1</div>
                <div class="step-text">Thông tin khách hàng</div>
            </div>
            <div class="step active">
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
</div>
<div class="payment-methods-container" style="max-width:600px; margin: 40px auto; padding:20px; border:1px solid #ddd; border-radius:8px;">

    <h2>Chọn phương thức thanh toán</h2>
    <p>Đơn của bạn: <strong>#{{ $booking->id }}</strong> - Tổng tiền: <strong>{{ number_format($booking->total_price, 0, ',', '.') }}đ</strong></p>

    <form action="{{ route('payment.init') }}" method="POST">
        @csrf
        <input type="hidden" name="booking_id" value="{{ $booking->id }}">

        <div class="mb-3">
            <label><input type="radio" name="method" value="vnpay" checked> Thanh toán qua VNPAY</label><br>
            
            <label><input type="radio" name="method" value="offline"> Thanh toán khi đến nhận phòng</label>
        </div>

        <button type="submit" class="btn btn-primary">Tiếp tục thanh toán</button>
    </form>
</div>
