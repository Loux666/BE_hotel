<base href="/public">
@include('home.css')
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
            <div class="step inactive">
                    <div class="step-number">1</div>
                    <div class="step-text">Thông tin khách hàng</div>
                </div>
                <div class="step inactive">
                    <div class="step-number">2</div>
                    <div class="step-text">Chi tiết thanh toán</div>
                </div>
                <div class="step active">
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
<div class="container py-5">
    <div class="alert alert-success text-center">
        <h4 class="alert-heading">🎉 Đặt phòng thành công!</h4>
        <p>Thông tin chi tiết về phòng đã được gửi qua email của bạn.</p>
        <hr>
        <p class="mb-0">Cảm ơn bạn đã tin tưởng sử dụng dịch vụ của chúng tôi.</p>
        <p class="mb-0">Bạn có thể xem đơn hàng của mình thông qua giỏ hàng</p>
    </div>

    <div class="text-center mt-4">
        <a href="{{ route('cart.view') }}?tab=history" class="btn btn-primary">
            Xem lịch sử đặt phòng
        </a>
    </div>
</div>


