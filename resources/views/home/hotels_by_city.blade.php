<base href="/public">
@include('home.css ')
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
          <div class="col-xl-9 col-lg-9 col-md-9 col-sm-9" >
             <nav class="navigation navbar navbar-expand-md navbar-dark  " style="display: flex; justify-content: center;">
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExample04" aria-controls="navbarsExample04" aria-expanded="false" aria-label="Toggle navigation">
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


<section class="hero-banner position-relative d-flex align-items-center" style="height: 500px;">
    <div class="bg-image"></div>

    <div class="container position-relative z-2">
        <div class="text-center mb-4">
            <h1 style="color: aliceblue" class="fw-bold">Khách sạn và nơi để ở tại Hà Nội</h1>
            <p  style="color: aliceblue" class="lead">Tìm kiếm để so sánh giá cả và khám phá ưu đãi tuyệt vời </p>
        </div>

        <form action="{{ route('room.filter')}}" method="GET" id="searchForm" class="search-form">
            <div class="row justify-content-center g-2 bg-white bg-opacity-75 rounded-3 p-3 shadow-lg">
                
                <div class="col-md-3">
                    <input type="text" class="form-control" id="location" name="location" placeholder="🔍"
                        value="{{ request('location') }}">
                    <div id="locationSuggestions" class="position-absolute bg-white border rounded w-100 z-3" style="top: 100%; left: 0; text-color:black; position:absolute"></div>
                </div>

                <div class="col-md-2">
                    <input type="number" class="form-control" id="guests" name="guests" placeholder="Nhập số người" min="1"
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
                    <input type="text" class="form-control rounded-pill px-3" id="checkin" name="checkin" placeholder="Chọn ngày">
                </div>

                <div class="col-md-2">
                    <input type="text" class="form-control rounded-pill px-3" id="checkout" name="checkout" placeholder="Chọn ngày">
                </div>

                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100 fw-bold">TÌM</button>
                </div>

            </div>
        </form>
    </div>
</section>

 


<div class="hotel_list-body" style="margin-top: 20px">
    <div class="hotel-list-container">
        
        <aside class="hotel-list-sidebar">
            <!-- Preview bản đồ -->
            <div class="sidebar-map-preview" onclick="openMapModal()">
                <img src="{{ asset('images/ggmap.jpeg') }}" style="width:240px; height:150px" alt="Xem bản đồ vị trí">
                <div class="map-overlay">
                    <span>XEM VỊ TRÍ</span>
                </div>
            </div>
          <!-- Tìm kiếm và bộ lọc -->
            <div class="sidebar-search" style="position: relative;">
                <input type="text" id="hotel-search-input" placeholder="{{$city}}..." class="sidebar-search-input">
                <span class="sidebar-search-icon">🔍</span>
                <ul id="search-suggestions" class="suggestions-dropdown"></ul>
            </div>
            <ul>
                <h5><strong>Giá mỗi đêm</strong></h5>
                <div id="price-slider"></div>

                <div style="display: flex; justify-content: space-between; margin-top: 10px;">
                    <div>
                        <label for="min_price">TỐI THIỂU</label>
                        <input type="number" id="min_price" name="min_price" class="form-control" style="width: 100px;font-size:12px">
                    </div>
                    <div>
                        <label for="max_price">TỐI ĐA</label>
                        <input type="number" id="max_price" name="max_price" class="form-control" style="width: 100px;font-size:12px" min="0" max="2000000" value="{{ request('max_price', 2000000) }}"> 
                    </div>
                </div>

                <h5><strong>Theo số sao</strong></h5>
                <div class="rating-filter">
                    @for ($i = 4; $i >= 1; $i--)
                        <div>
                            <input type="checkbox" class="rating-checkbox" value="{{ $i }}" id="rating-{{ $i }}">
                            <label for="rating-{{ $i }}">
                                {{ $i }} ⭐ trở lên
                            </label>
                        </div>
                    @endfor
                </div>
            </ul>
        </aside>

        <!-- Nội dung chính -->
        <main class="hotel-list-content">
            <h1>Danh sách khách sạn tại {{ $city }}</h1>

            <div class="hotel-list-grid">
                @forelse ($hotels as $hotel)
                    <div class="hotel-list-card" data-average-price="{{ $hotel->average_price }}" data-rating="{{ $hotel->average_rating ?? 0 }}" data-id="{{ $hotel->id }}">
                        <div class="hotel-info-left">
                            <img src="{{ asset('hotelImg/' . $hotel->hotel_image) }}"
                                 alt="{{ $hotel->hotel_name }}"
                                 class="hotel-list-image">
                            <div class="hotel-list-info">
                                <h3 class="hotel-list-name">
                                    <a href="{{ route('rooms.by.hotel', ['id' => $hotel->id]) }}">
                                        {{ $hotel->hotel_name }}
                                    </a>
                                </h3>

                                <p class="hotel-list-rating">
                                    ⭐ Đánh giá trung bình: {{ $hotel->average_rating }} / 5
                                </p>
                                <p class="hotel-list-address">📍 Địa chỉ: {{ $hotel->hotel_address }}</p>
                                <p class="hotel-list-price">
                                    💰 Giá trung bình 1 đêm:
                                    <span>{{ number_format($hotel->average_price, 0, ',', '.') }} VNĐ</span>
                                </p>
                            </div>
                        </div>
                        <div class="hotel-action-right">
                            <button class="check-room-btn" data-hotel="{{ $hotel->hotel_name }}">Kiểm tra phòng còn trống</button>
                        </div>
                    </div>
                @empty
                    <p>Không có khách sạn nào tại {{ $city }}.</p>
                @endforelse
            </div>
            <p id="no-result-msg" style="display:none; margin: 20px; color: red;">Không có khách sạn nào phù hợp với bộ lọc.</p>
        </main>
        

    </div>
</div>




<!-- Modal Cảnh Báo -->
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

<!-- Modal gg map-->
<div id="mapModal" class="modal-map" style="display: none;">
    <div class="modal-map-content">
        <span class="close-map" onclick="closeMapModal()">&times;</span>
        <div id="hotel-map-modal" style="width: 100%; height: 600px;"></div>
    </div>
</div>



 @include('home.footer')
 


<script> // sticky form roll
         window.addEventListener('scroll', function() {
            const form = document.getElementById('searchForm');
            const triggerPoint = 400; // khi scroll xuống 400px

            if (window.scrollY >= triggerPoint) {
                  form.classList.add('sticky');
            } else {
                  form.classList.remove('sticky');
            }
         });
</script>
<script>//date pick
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
                
    }); // Auto fill khi ấn "Xem tất cả các phòng"
            
</script>
         
         
 <script>//validate
    
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

<script>//loc theo slide gia tien va rating
    document.addEventListener('DOMContentLoaded', function () {
        const slider = document.getElementById('price-slider');
        const minInput = document.getElementById('min_price');
        const maxInput = document.getElementById('max_price');

        const MAX_VALUE = 1000000;

        noUiSlider.create(slider, {
            start: [0, MAX_VALUE],
            connect: true,
            step: 50000,
            range: {
                'min': 0,
                'max': MAX_VALUE
            },
            format: {
                to: value => Math.round(value),
                from: value => Number(value)
            }
        });
        document.querySelectorAll('.rating-checkbox').forEach(cb => {
            cb.addEventListener('change', () => {
                const min = parseInt(minInput.value);
                const max = parseInt(maxInput.value);
                filterHotels(min, max);
            });
        });

        // Update input fields
        slider.noUiSlider.on('update', function (values) {
            minInput.value = values[0];
            maxInput.value = values[1];
        });

        // Main filter logic: no reload
        function filterHotels(minPrice, maxPrice) {
            const checkedRatings = Array.from(document.querySelectorAll('.rating-checkbox:checked'))
                .map(cb => parseInt(cb.value));

            let visibleCount = 0;

            document.querySelectorAll('.hotel-list-card').forEach(card => {
                const avg = parseInt(card.getAttribute('data-average-price'));
                const rating = parseFloat(card.getAttribute('data-rating')) || 0;

                const inPriceRange = avg >= minPrice && avg <= maxPrice;
                const inRating = checkedRatings.length === 0 || checkedRatings.some(r => rating >= r);

                const shouldShow = inPriceRange && inRating;
                card.style.display = shouldShow ? '' : 'none';

                if (shouldShow) visibleCount++;
            });

            // Ẩn/hiện dòng "Không có kết quả"
            const noResultMsg = document.getElementById('no-result-msg');
            if (visibleCount === 0) {
                noResultMsg.style.display = 'block';
            } else {
                noResultMsg.style.display = 'none';
            }
        }

        slider.noUiSlider.on('change', function (values) {
            const min = parseInt(values[0]);
            const max = parseInt(values[1]);
            filterHotels(min, max);
        });

        // Update slider when blur input
        minInput.addEventListener('blur', function () {
            slider.noUiSlider.set([this.value, maxInput.value]);
            filterHotels(parseInt(this.value), parseInt(maxInput.value));
        });

        maxInput.addEventListener('blur', function () {
            slider.noUiSlider.set([minInput.value, this.value]);
            filterHotels(parseInt(minInput.value), parseInt(this.value));
        });
    });
</script>
<script>//suggest
    document.addEventListener('DOMContentLoaded', function () {
        const input = document.getElementById('hotel-search-input');
        const suggestionBox = document.getElementById('search-suggestions');
        const city = "{{ urlencode($city) }}";

        input.addEventListener('input', function () {
            const query = this.value.trim();
            if (query.length < 1) {
                suggestionBox.innerHTML = '';
                suggestionBox.style.display = 'none';
                return;
            }

            fetch(`/hotels/search/${city}?query=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    suggestionBox.innerHTML = '';
                    if (data.length > 0) {
                        data.forEach(hotel => {
                            const li = document.createElement('li');
                            li.textContent = `${hotel.hotel_name} - ${hotel.hotel_address}`;
                            li.addEventListener('click', () => {
                                input.value = hotel.hotel_name;
                                suggestionBox.innerHTML = '';
                                suggestionBox.style.display = 'none';
                                scrollToHotelCard(hotel.id); // Tự scroll đến khách sạn nếu muốn
                            });
                            suggestionBox.appendChild(li);
                        });
                        suggestionBox.style.display = 'block';
                    } else {
                        suggestionBox.style.display = 'none';
                    }
                });
        });

        // Ẩn dropdown khi click ra ngoài
        document.addEventListener('click', function (e) {
            if (!input.contains(e.target) && !suggestionBox.contains(e.target)) {
                suggestionBox.style.display = 'none';
            }
        });

        function scrollToHotelCard(id) {
            const target = document.querySelector(`.hotel-list-card[data-id="${id}"]`);
            if (target) {
                const offset = 100;
                const y = target.getBoundingClientRect().top + window.scrollY - offset;
                window.scrollTo({ top: y, behavior: 'smooth',  });

                // Thêm hiệu ứng highlight
                target.classList.add('highlight-flash');

                // Gỡ bỏ sau 2s
                setTimeout(() => {
                    target.classList.remove('highlight-flash');
                }, 2000);
            }
        }
    });
</script>

<script> //gg map overlay
            let map;
            let markers = [];

            const hotels = @json($hotels);

            function openMapModal() {
                document.getElementById("mapModal").style.display = "block";
                setTimeout(() => initMap(), 200); // Cho div map kịp hiển thị
            }

            function closeMapModal() {
                document.getElementById("mapModal").style.display = "none";
            }

            function initMap() {
                if (!map) {
                    map = new google.maps.Map(document.getElementById("hotel-map-modal"), {
                        zoom: 12,
                        center: { lat: 16.047079, lng: 108.20623 } // ví dụ: Đà Nẵng
                    });
                }

                // Xóa marker cũ
                markers.forEach(marker => marker.setMap(null));
                markers = [];

                const geocoder = new google.maps.Geocoder();

                hotels.forEach(hotel => {
                    geocoder.geocode({ address: hotel.hotel_address }, (results, status) => {
                        if (status === 'OK') {
                            const position = results[0].geometry.location;
                            const marker = new google.maps.Marker({
                                map: map,
                                position: position,
                                title: hotel.hotel_name
                            });

                            const infoWindow = new google.maps.InfoWindow({
                                content: `<strong>${hotel.hotel_name}</strong><br>${hotel.hotel_address}`
                            });

                            marker.addListener('click', () => {
                                infoWindow.open(map, marker);
                            });

                            markers.push(marker);
                        } else {
                            console.warn("Không tìm được vị trí:", hotel.hotel_address);
                        }
                    });
                });
            }
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
<script>// focus
    document.addEventListener("DOMContentLoaded", function () {
        const form = document.querySelector(".search-form");
        const inputs = form.querySelectorAll("input, select");
        let overlay;

        // Khi một phần tử input/select được focus
        inputs.forEach(input => {
            input.addEventListener("focus", () => {
                // Nếu lớp tối chưa tồn tại, tạo nó
                if (!overlay) {
                    overlay = document.createElement("div");
                    overlay.style.position = "fixed";
                    overlay.style.top = "0";
                    overlay.style.left = "0";
                    overlay.style.width = "100%";
                    overlay.style.height = "100%";
                    overlay.style.background = "rgba(0, 0, 0, 0.5)";
                    overlay.style.zIndex = "1";
                    document.body.appendChild(overlay);
                }
            });
        });

        // Khi click ra ngoài form, ẩn lớp tối
        document.addEventListener("click", (event) => {
            if (overlay && !form.contains(event.target)) {
                overlay.remove();
                overlay = null;
            }
        });
    });
</script>

<style>
    .highlight-flash {
    animation: flashBorder 1s ease-in-out 2;
    }

    @keyframes flashBorder {
        0%   { box-shadow: 0 0 0px rgba(255, 193, 7, 0); }
        50%  { box-shadow: 0 0 10px 5px rgba(255, 193, 7, 0.8); }
        100% { box-shadow: 0 0 0px rgba(255, 193, 7, 0); }
    }
</style>



     
      


        
     

        

         