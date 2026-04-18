@include('home.css')
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
                      <li class="nav-item active" >
                         <a class="nav-link" style="font-size: 14px "href="/">Trang chủ</a>
                      </li>
                      <li class="nav-item">
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
 <!-- Cuối file header, trước </div> cuối -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>