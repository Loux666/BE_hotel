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
                                <a class="nav-link" style="font-size: 14px" href="{{url('coupon')}}">Ưu đãi</a>
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
                    <div id="locationSuggestions" class="position-absolute bg-white border rounded w-100 z-3" style="top: 100%; left: 0;"></div>

                </div>

                <div class="col-md-2">
                    <input type="number" class="form-control" id="guests" name="guests" placeholder="2 người" min="1"
                        value="{{ request('guests') }}">
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
                    <a href=".">
                    <button type="submit" class="btn btn-primary w-100 fw-bold">TÌM</button>
                    </a>
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
            @foreach ($bannerImages as $image)
                <div class="room-image-card">
                    <img src="{{ asset('roomImg/' . $image) }}" alt="Ảnh phòng">
                </div>
            @endforeach
            </div>
        </div>

        <div class="hotel-detail">
            <div class="hotel-information">
                <h3>{{$hotel->hotel_name}}</h3>
                <div class="hotel-detail-adress"> {{$hotel->hotel_address}}</div>
                <div class="hotel_detail-description">{{$hotel->hotel_description ?? "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum."}}</div>

            </div>
            <div class="show-hotel-location">
                <img src="{{ asset('images/ggmap.jpeg') }}"  alt="Xem bản đồ vị trí">
                        <div class="map-overlay">
                            <span>XEM VỊ TRÍ</span>
                        </div>
            </div>

</div>
<div class="roomAvailable_list" style="margin-top: 20px">
    
        <main class="room-list-content">
            <h2>Danh sách các phòng còn trống tại {{$location}}  </h2>
            <div class="available-room-grid">
                @forelse ($rooms as $room)
                @php
                    $roomTypes = [
                        'standard' => 'Tiêu chuẩn',
                        'deluxe' => 'Cao cấp',
                        'family' => 'Gia đình',
                    ];

                    $typeVi = $roomTypes[$room->room_type ?? $room->type] ?? 'Không xác định';
                @endphp
                    <div class="available-rooms-card">
                        <div class="available-room-card-left">
                            @if($room->images && $room->images->isNotEmpty())
                                <div class="available-room-images">
                                     <div class="main-image">
                                        <img src="{{ asset('roomImg/' . ($room->images[0] ?? 'default.jpg')) }}" alt="Ảnh chính">
                                    </div>

                                    {{-- 2 ảnh nhỏ --}}
                                    <div class="sub-images">
                                        <img src="{{ asset('roomImg/' . ($room->images[1] ?? 'default.jpg')) }}" alt="Ảnh phụ 1">
                                        <img src="{{ asset('roomImg/' . ($room->images[2] ?? 'default.jpg')) }}" alt="Ảnh phụ 2">
                                    </div>
                                </div>
                            @endif

                            <div class="available-room-info">
                                <h3 class="available-room-name" style="font-weight:bold">{{ $room->room_name }}</h3>
                                <p class="available-room-type">🛏 Loại phòng: {{ $typeVi }}</p>
                                <p class="available-room-capacity">👥 Sức chứa: {{ $room->max_guests }} khách</p>
                                <p class="available-room-price">💰 Giá: {{ number_format($room->price, 0, ',', '.') }} VNĐ / đêm</p>
                            </div>
                        </div>
                        
                        <div class="available-room-card-right">
                            <form action="{{ route('booking.verify') }}" method="POST" target="_blank" >
                            @csrf
                            <input type="hidden" name="room_id" value="{{ $room->room_id }}">
                            <input type="hidden" name="hotel_id" value="{{ $hotel->id }}">
                            <input type="hidden" name="checkin" value="{{ $checkin }}">
                            <input type="hidden" name="checkout" value="{{ $checkout }}">
                            <input type="hidden" name="guests" value="{{ request('guests') }}">
                            <button type="submit" class="btn-book-now">Đặt ngay</button>
                        </form>
                            @if (Route::has('login'))
                            @auth

                            <form action="{{ route('cart.add') }}" method="POST">
                                @csrf
                                <input type="hidden" name="room_id" value="{{ $room->room_id }}">
                                <input type="hidden" name="hotel_id" value="{{ $hotel->id }}">
                                <input type="hidden" name="checkin" value="{{ $checkin }}">
                                <input type="hidden" name="checkout" value="{{ $checkout }}">
                                <input type="hidden" name="guests" value="{{ request('guests') }}">
                                <button type="submit" class="btn-add-to-cart">Thêm vào giỏ hàng</button>
                            </form>
                            
                                
                            @endif
                            @endauth
                        </div>
                    </div>
                @empty
                    <p>Không tìm thấy phòng phù hợp tại {{ $location }}.</p>
                @endforelse
            </div>      
        </main>
    </div>
</div>

<div class="feedback-hotel-reviews">
    <h3>Đánh giá của khách về khách sạn "{{ $hotel->hotel_name }}"</h3>
    
    @forelse ($feedbacks as $feedback)
        <div class="feedback-review-item">
            <div class="feedback-review-user">
                <div class="feedback-user-info">
                    <strong>{{ $feedback->user->name ?? 'Ẩn danh' }}</strong>
                    <small class="feedback-review-date">{{ $feedback->created_at->format('d/m/Y H:i') }}</small>
                    @if ($feedback->bookingDetail && $feedback->bookingDetail->room)
                        <small class="feedback-room-info">Phòng: {{ $feedback->bookingDetail->room->room_name }}</small>
                    @endif
                </div>
            </div>
            
            <div class="feedback-review-content">
                <div class="feedback-rating">
                    <span class="feedback-stars">
                        @for ($i = 1; $i <= 5; $i++)
                            @if ($i <= $feedback->rating)
                                ⭐
                            @else
                                ☆
                            @endif
                        @endfor
                    </span>
                    <span class="feedback-rating-number">{{ $feedback->rating }}/5</span>
                </div>
                <p class="feedback-text">"{{ $feedback->content }}"</p>
            </div>
        </div>
    @empty
        <div class="feedback-no-reviews">
            <p>Chưa có đánh giá nào cho khách sạn này.</p>
        </div>
    @endforelse
</div>

@include('home.footer')

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

        <script> //live search
   $(document).ready(function () {
      $('#location').on('keyup', function () {
         let query = $(this).val();
         if (query.length >= 1) {
               $.ajax({
                  url: "{{ route('locations.search') }}",
                  method: 'GET',
                  data: { query: query },
                  success: function (data) {
                     let suggestions = $('#locationSuggestions');
                     suggestions.empty();

                     if (data.length > 0) {
                           data.forEach(function (item) {
                              suggestions.append(`
                                 <div class="suggest-item  items-center gap-2 p-2 cursor-pointer hover:bg-gray-100" data-full="${item.hotel_name}">
                                    <img src="/hotelImg/${item.hotel_image}" alt="${item.hotel_name}" class="w-12 h-12 object-cover rounded">
                                    <div class=hotel_info>
                                       <strong>${item.hotel_name}</strong><br>
                                       <small>${item.hotel_address},    TP: ${item.hotel_city}</small>
                                    </div>
                                 </div>
                              `);
                           });
                     } else {
                           suggestions.append(`<div class="suggest-item">Không tìm thấy</div>`);
                     }
                  }
               });
         } else {
               $('#locationSuggestions').empty();
         }
      });

      // Khi chọn gợi ý
      $(document).on('click', '.suggest-item', function () {
         $('#location').val($(this).data('full'));
         $('#locationSuggestions').empty();
      });

      // Ẩn khi click ra ngoài
      $(document).on('click', function (e) {
         if (!$(e.target).closest('#location, #locationSuggestions').length) {
               $('#locationSuggestions').empty();
         }
      });
   });
</script>
<script> //live search
   $(document).ready(function () {
      $('#location').on('keyup', function () {
         let query = $(this).val();
         if (query.length >= 1) {
               $.ajax({
                  url: "{{ route('locations.search') }}",
                  method: 'GET',
                  data: { query: query },
                  success: function (data) {
                     let suggestions = $('#locationSuggestions');
                     suggestions.empty();

                     if (data.length > 0) {
                           data.forEach(function (item) {
                              suggestions.append(`
                                 <div class="suggest-item  items-center gap-2 p-2 cursor-pointer hover:bg-gray-100" data-full="${item.hotel_name}">
                                    <img src="/hotelImg/${item.hotel_image}" alt="${item.hotel_name}" class="w-12 h-12 object-cover rounded">
                                    <div class=hotel_info>
                                       <strong>${item.hotel_name}</strong><br>
                                       <small>${item.hotel_address},    TP: ${item.hotel_city}</small>
                                    </div>
                                 </div>
                              `);
                           });
                     } else {
                           suggestions.append(`<div class="suggest-item">Không tìm thấy</div>`);
                     }
                  }
               });
         } else {
               $('#locationSuggestions').empty();
         }
      });

      // Khi chọn gợi ý
      $(document).on('click', '.suggest-item', function () {
         $('#location').val($(this).data('full'));
         $('#locationSuggestions').empty();
      });

      // Ẩn khi click ra ngoài
      $(document).on('click', function (e) {
         if (!$(e.target).closest('#location, #locationSuggestions').length) {
               $('#locationSuggestions').empty();
         }
      });
   });
</script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    @if (Session::has('success'))
    <script>
        const navType = performance.getEntriesByType("navigation")[0]?.type;
        if (navType !== "back_forward") {
            toastr.options = {
                progressBar: true,
                closeButton: true
            };
            toastr.success("{{ Session::get('success') }}", 'Thành công', {
                timeOut: 10000,
                positionClass: 'toast-top-right'
            });
        }
    </script>
    @php Session::forget('message'); @endphp
    @endif

<style>
    .feedback-hotel-reviews {
    max-width: 1150px;
    margin: 100px auto;
    padding: 20px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .feedback-hotel-reviews h3 {
        color: #2c3e50;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #3498db;
        font-size: 1.5em;
    }

    .feedback-review-item {
        display: flex;
        gap: 20px;
        margin-bottom: 20px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
        border-left: 4px solid #3498db;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        transition: box-shadow 0.3s ease;
    }

    .feedback-review-item:hover {
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }

    .feedback-review-user {
        flex: 0 0 200px;
        padding-right: 15px;
        border-right: 1px solid #dee2e6;
    }

    .feedback-user-info {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .feedback-user-info strong {
        color: #2c3e50;
        font-size: 1.1em;
        margin-bottom: 5px;
    }

    .feedback-review-date {
        color: #6c757d;
        font-size: 0.9em;
    }

    .feedback-room-info {
        color: #495057;
        font-size: 0.85em;
        background: #e9ecef;
        padding: 2px 6px;
        border-radius: 4px;
        margin-top: 5px;
        display: inline-block;
    }

    .feedback-review-content {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .feedback-rating {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 8px;
    }

    .feedback-stars {
        font-size: 1.2em;
        color: #ffc107;
    }

    .feedback-rating-number {
        background: #3498db;
        color: white;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 0.85em;
        font-weight: bold;
    }

    .feedback-text {
        color: #495057;
        line-height: 1.6;
        font-style: italic;
        margin: 0;
        font-size: 1em;
    }

    .feedback-no-reviews {
        text-align: center;
        padding: 40px;
        background: #f8f9fa;
        border-radius: 8px;
        color: #6c757d;
        font-style: italic;
    }

</style>