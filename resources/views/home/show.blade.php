<base href="/public">
@include('home.css')
<div class="header">
    <div class="container">
        <div class="row">
            <div class="col-xl-3 col-lg-3 col-md-3 col-sm-3 col logo_section">
                <div class="full">
                    <div class="center-desk">
                        <div class="logo">
                            <a href="."><img src="images/logo.png" alt="#" /></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-9 col-lg-9 col-md-9 col-sm-9">
                <nav class="navigation navbar navbar-expand-md navbar-dark  " style="display: flex; justify-content: center;">
                    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExample04" aria-controls="navbarsExample04" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarsExample04">
                        <ul class="navbar-nav mr-auto">
                            <li class="nav-item ">
                                <a class="nav-link" style="font-size: 14px " href="/">Trang chủ</a>
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
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
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



                            <li class="nav-item" style="margin-top:5px; padding-left:20px">
                                <a href="{{url('cart')}}">
                                    <img src="images/cart_logo.png" width="20px" height="20px">
                                </a>
                            </li>
                        </ul>
                    </div>
                </nav>
            </div>
        </div>
    </div>
</div>


<!-- Form Tìm kiếm Sticky -->
<div id="hotelSearchSticky" class="hotel-search-bar">
    <form action="{{ route('room.filter')}}" method="GET" id="searchForm" class="search-form2">
            <div class="row justify-content-center g-2 bg-white bg-opacity-75 rounded-3 p-3 shadow-lg">
                
                <div class="col-md-3">
                    <input type="text" class="form-control" id="location" name="location" placeholder="🔍"
                        value="{{ request('location') }}">
                </div>

                <div class="col-md-2">
                    <input type="number" class="form-control" id="guests" name="guests" placeholder="2 người" min="1"
                        value="{{ request('guests') ?? 2}}">
                </div>

                <div class="col-md-2">
                    <select class="form-control" id="room_type" name="room_type">
                        <option value="">Phòng bất kỳ</option>
                        <option value="standard" {{ request('room_type') == 'standard' ? 'selected' : '' }}>Phòng tiêu chuẩn</option>
                        <option value="deluxe" {{ request('room_type') == 'deluxe' ? 'selected' : '' }}>Phòng cao cấp (Deluxe)</option>
                        <option value="family" {{ request('room_type') == 'family' ? 'selected' : '' }}>Phòng gia đình</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <input type="date" class="form-control" id="checkin" name="checkin" value="{{ request('checkin') }}">
                </div>

                <div class="col-md-2">
                    <input type="date" class="form-control" id="checkout" name="checkout" value="{{ request('checkout') }}">
                </div>

                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100 fw-bold">TÌM</button>
                </div>

            </div>
        </form>
</div>

<div class="hotel-image-container">
  <div class="hotel-main-image">
    <!-- Ảnh lớn của khách sạn -->
    <div class="show-main-image">
      <img src="{{ asset('hotelImg/' . $hotel->hotel_image) }}" alt="Ảnh khách sạn">
    </div>

    <!-- 6 ảnh phòng -->
    <div class="room-image-banner">
      @foreach ($roomImages as $image)
        <div class="room-image-card">
          <img src="{{ asset('roomImg/' . $image->image_path) }}" alt="Ảnh phòng">
        </div>
      @endforeach
    </div>
  </div>
</div>

<div class="hotel-detail">
    <div class="hotel-information">
        <h3>{{$hotel->hotel_name}}</h3>
        <div class="hotel-detail-adress"> {{$hotel->hotel_address}}</div>
        <div class="hotel_detail-description">{{$hotel->hotel_description ?? 'No description'}}</div>

    </div>
    <div class="show-hotel-location">
        <img src="{{ asset('images/ggmap.jpeg') }}"  alt="Xem bản đồ vị trí">
                <div class="map-overlay">
                    <span>XEM VỊ TRÍ</span>
                </div>
    </div>

</div>


<!-- Hien thi cac phong -->
<section class="available-rooms my-4">
    <div class="container">
        <h2 style="font-weight: bold" class="mb-4 fw-semibold">Danh sách các phòng tại: {{ $hotel->hotel_name }}</h2>
        <div class="room-grid">
            @foreach($rooms as $room)
                <div class="room-card">
                <div class="room-image">
                    <img src="{{ asset('roomImg/' . $room->images[0]->image_path ?? 'default.jpg') }}" alt="Room Image">
                    
                </div>
                <div class="room-info">
                    <div>
                    <h3>{{ $room->room_name }}</h3>
                    <div class="type">Loại phòng: {{ $room->type }}</div>
                    <div class="price">Giá: {{ number_format($room->price, 0, ',', '.') }} VND/đêm</div>
                    <div class="capacity">Chỗ nghỉ dành cho: {{$room->capacity}} người</div>
                    </div>
                    <button class="check-room-btn" data-hotel="{{ $hotel->hotel_name }}">Nhập ngày để kiểm tra phòng</button>
                </div>
                </div>
            @endforeach
            </div>

       
    </div>
</section>



<!-- Modal cảnh báo -->
<div class="modal fade" id="warningModal" tabindex="-1" aria-labelledby="warningModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title" id="warningModalLabel">⚠️ Cảnh báo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
      </div>
      <div class="modal-body" id="warningModalMessage">
        <!-- Nội dung cảnh báo  -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đã hiểu</button>
      </div>
    </div>
  </div>
</div>




<script>
    window.addEventListener('scroll', function () {
        const formBar = document.getElementById('hotelSearchSticky');
        if (window.scrollY > 100) {
            formBar.classList.add('sticky');
        } else {
            formBar.classList.remove('sticky');
        }
    });
</script>

<script> // Auto fill khi ấn "Xem tất cả các phòng"
            document.addEventListener('DOMContentLoaded', function () {
                const buttons = document.querySelectorAll('.check-room-btn');

                let checkinPicker, checkoutPicker;

                checkinPicker = flatpickr("#checkin", {
                    dateFormat: "Y-m-d",
                    minDate: "today",
                    locale: "vn",
                    onClose: function (selectedDates) {
                        if (selectedDates.length > 0) {
                            const nextDay = new Date(selectedDates[0]);
                            nextDay.setDate(nextDay.getDate() + 1);

                            checkoutPicker.setDate(nextDay);
                            checkoutPicker.set("minDate", nextDay);

                            checkoutPicker.open();
                        }
                    }
                });

                checkoutPicker = flatpickr("#checkout", {
                    dateFormat: "Y-m-d",
                    minDate: new Date().fp_incr(1),
                    locale: "vn"
                });

                document.getElementById('checkin').placeholder = "Nhận phòng";
                document.getElementById('checkout').placeholder = "Trả phòng";

                buttons.forEach(button => {
                    button.addEventListener('click', function () {
                        const hotelName = this.dataset.hotel;
                        const locationInput = document.getElementById('location');

                        if (hotelName) {
                            locationInput.value = hotelName;
                        } else {
                            console.error("data-hotel không tồn tại trên nút!");
                            return;
                        }

                        checkinPicker.open();
                    });
                });
            });
        </script>
         
         <!-- Validate Client = Modal popup-->
         <script>
            document.addEventListener('DOMContentLoaded', function () {
                const form = document.getElementById('searchForm');
                const modal = new bootstrap.Modal(document.getElementById('warningModal'));
                const modalMessage = document.getElementById('warningModalMessage');

                form.addEventListener('submit', function (e) {
                    const location = document.getElementById('location').value.trim();
                    const guests = parseInt(document.getElementById('guests').value);
                    const checkin = document.getElementById('checkin').value;
                    const checkout = document.getElementById('checkout').value;

                    let errorMessage = '';

                    if (!location) {
                        errorMessage = 'Vui lòng nhập địa điểm bạn muốn tìm.';
                    } else if (!guests || guests < 1) {
                        errorMessage = 'Số người phải lớn hơn 0.';
                    } else if (!checkin || !checkout) {
                        errorMessage = 'Vui lòng chọn cả ngày nhận phòng và trả phòng.';
                    } else if (new Date(checkout) <= new Date(checkin)) {
                        errorMessage = 'Ngày trả phòng phải sau ngày nhận phòng.';
                    }

                    if (errorMessage) {
                        e.preventDefault(); // Ngăn form submit
                        modalMessage.innerText = errorMessage;
                        modal.show();
                    }
                });
            });
        </script>