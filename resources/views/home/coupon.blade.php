@include('home.css')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<div class="header">
    <div class="container">
       <div class="row">
          <div class="col-xl-3 col-lg-3 col-md-3 col-sm-3 col logo_section">
             <div class="full">
                <div class="center-desk">
                   <div class="logo">
                      <a href="."><img src="images/logo.png" alt="#"  width="135px" height="30px"/></a>
                   </div>
                </div>
             </div>
          </div>
          <div class="col-xl-9 col-lg-9 col-md-9 col-sm-9" >
             <nav class="navigation navbar navbar-expand-md navbar-dark  " style="display: flex; justify-content: center;">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarsExample04" aria-controls="navbarsExample04" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarsExample04" >
                   <ul class="navbar-nav mr-auto"  >
                      <li class="nav-item" >
                         <a class="nav-link" style="font-size: 14px "href="/">Trang chủ</a>
                      </li>
                      <li class="nav-item active">
                         <a class="nav-link"style="font-size: 14px" href="{{url('coupon')}}">ưu đãi</a>
                      </li>
                     
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



                     
                  
                      

                     <li class="nav-item" style="margin-top:5px; padding-left:20px" >
                        <a href="{{url('cart')}}" >
                           <img src="images/cart_logo.png" width="20px" height="20px" >
                        </a>
                     </li>
                   </ul>
                </div>
             </nav>
          </div>
       </div>
    </div>
</div>


<div class="coupon-container">
    <h2 class="coupon-title text-center mb-4">🎁 Mã ưu đãi hiện có</h2>
    
    <div class="coupon-grid">
        @forelse ($coupons as $coupon)
            <div class="coupon-card">
                <div class="coupon-card-inner">
                    <div class="coupon-badge">HOT</div>
                    
                    <div class="coupon-discount">
                        @if ($coupon->type === 'percent')
                            {{ $coupon->value }}%
                        @else
                            {{ number_format($coupon->value) }}đ
                        @endif
                    </div>
                    <div class="coupon-details">
                        <div class="coupon-details-item">
                            <span class="coupon-details-label">Giảm:</span>
                            <span class="coupon-details-value">
                                @if ($coupon->type === 'percent')
                                    {{ $coupon->value }}%
                                @else
                                    {{ number_format($coupon->value) }}đ
                                @endif
                            </span>
                        </div>
                        <div class="coupon-details-item">
                            <span class="coupon-details-label">Đơn tối thiểu:</span>
                            <span class="coupon-details-value">{{ number_format($coupon->min_order_price) }}đ</span>
                        </div>
                        <div class="coupon-details-item">
                            <span class="coupon-details-label">HSD:</span>
                            <span class="coupon-details-value">{{ \Carbon\Carbon::parse($coupon->end_date)->format('d/m/Y') }}</span>
                        </div>
                    </div>
                    <button class="coupon-get-code-btn" data-code="{{ $coupon->code }}">Lấy mã</button>
                    <div class="coupon-code-display coupon-hidden">
                        <div class="coupon-code-text">{{ $coupon->code }}</div>
                        <button class="coupon-copy-btn">Copy</button>
                    </div>
                </div>
            </div>
        @empty
            <div class="coupon-empty-state">
                <div class="coupon-empty-icon">🎫</div>
                <p class="coupon-empty-text">Hiện tại không có mã giảm giá khả dụng.</p>
            </div>
        @endforelse
    </div>
</div>

<script>
        document.addEventListener('DOMContentLoaded', function () {
            // Xử lý nút "Lấy mã"
            const buttons = document.querySelectorAll('.coupon-get-code-btn');
            buttons.forEach(btn => {
                btn.addEventListener('click', function () {
                    const codeDisplay = this.nextElementSibling;
                    codeDisplay.classList.remove('coupon-hidden');
                    this.classList.add('coupon-hidden');
                });
            });

            // Xử lý nút copy
            const copyButtons = document.querySelectorAll('.coupon-copy-btn');
            copyButtons.forEach(btn => {
                btn.addEventListener('click', function () {
                    const codeElement = this.previousElementSibling;
                    const code = codeElement.innerText;
                    
                    // Copy to clipboard
                    navigator.clipboard.writeText(code).then(function() {
                        const originalText = btn.innerText;
                        btn.innerText = 'Copied!';
                        btn.style.background = '#17a2b8';
                        
                        setTimeout(function() {
                            btn.innerText = originalText;
                            btn.style.background = '#28a745';
                        }, 1500);
                    }).catch(function() {
                        alert('Đã copy mã: ' + code);
                    });
                });
            });
        });
</script>

<style>
.coupon-container { padding: 30px; justify-content: center;align-items: center;padding: auto;
.coupon-grid { display: flex; flex-wrap: wrap; gap: 20px; }
.coupon-card { width: 100%; max-width: 300px; border: 1px solid #ddd; border-radius: 10px; overflow: hidden; background: #fff; box-shadow: 0 0 10px rgba(0,0,0,0.05); }
.coupon-card-inner { padding: 20px; text-align: center; position: relative; }
.coupon-badge { position: absolute; top: 10px; left: 10px; background: #ff4d4f; color: white; padding: 3px 8px; border-radius: 5px; font-size: 12px; }
.coupon-code-title { font-size: 20px; font-weight: bold; }
.coupon-discount { font-size: 28px; color: #28a745; margin-bottom: 10px; }
.coupon-details { margin-bottom: 10px; }
.coupon-details-item { display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 14px; }
.coupon-get-code-btn { background: #007bff; color: white; padding: 8px 12px; border: none; border-radius: 5px; cursor: pointer; }
.coupon-code-display { margin-top: 10px; }
.coupon-code-text { font-weight: bold; color: #28a745; }
.coupon-copy-btn { margin-left: 10px; padding: 5px 10px; }
.coupon-hidden { display: none; }
.coupon-empty-state { text-align: center; margin-top: 50px; }
.coupon-empty-icon { font-size: 50px; }
.coupon-empty-text { font-size: 18px; }
</style>
       