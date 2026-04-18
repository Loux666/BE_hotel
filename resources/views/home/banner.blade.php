@include('home.css')
<section class="banner_main" >
    <div id="myCarousel" class="carousel slide banner" data-ride="carousel">
       <ol class="carousel-indicators">
          <li data-target="#myCarousel" data-slide-to="0" class="active"></li>
          <li data-target="#myCarousel" data-slide-to="1"></li>
          <li data-target="#myCarousel" data-slide-to="2"></li>
       </ol>
       <div class="carousel-inner" style="height: 500px;">
          <div class="carousel-item active">
             <img class="first-slide" src="images/banner1.jpg" alt="First slide">
             <div class="container">
             </div>
          </div>
          <div class="carousel-item">
             <img class="second-slide" src="images/banner2.jpg" alt="Second slide">
          </div>
          <div class="carousel-item">
             <img class="third-slide" src="images/banner3.jpg" alt="Third slide">
          </div>
       </div>
       <a class="carousel-control-prev" href="#myCarousel" role="button" data-slide="prev">
       <span class="carousel-control-prev-icon" aria-hidden="true"></span>
       <span class="sr-only">Previous</span>
       </a>
       <a class="carousel-control-next" href="#myCarousel" role="button" data-slide="next">
       <span class="carousel-control-next-icon" aria-hidden="true"></span>
       <span class="sr-only">Next</span>
       </a>
    </div>
    <div class="booking_online py-4" style="background-color: #f8f9fa;">
      <div class="container">
         <div class="book_room p-4 shadow bg-white rounded-4">
            <h1 class="mb-4 fw-bold text-center">Bạn cần thuê phòng thế nào?</h1>
            <form action="{{ route('room.filter') }}" method="GET" class="first-form" id="first-form">
               <div class="row g-3">
                  
                  {{-- Địa điểm có autocomplete --}}
                  <div class="col-md-12 position-relative">
                     <label for="location" class="form-label">Địa điểm</label>
                     <input type="text" class="form-control rounded-pill px-3" id="location" name="location" placeholder="Nhập địa điểm (Hà Nội, Đà Nẵng...)">
                     <div id="locationSuggestions" class="position-absolute bg-white border rounded w-100 z-3" style="top: 100%; left: 0;"></div>
                  </div>

                  {{-- Loại phòng --}}
                  <div class="col-md-3 mt-3">
                     <label for="room_type" class="form-label">Loại phòng</label>
                     <select class="form-control rounded-pill px-3" id="room_type" name="room_type">
                        <option value="">Phòng bất kỳ</option>
                        <option value="standard" {{ request('room_type') == 'standard' ? 'selected' : '' }}>Phòng tiêu chuẩn</option>
                        <option value="deluxe" {{ request('room_type') == 'deluxe' ? 'selected' : '' }}>Phòng cao cấp (Deluxe)</option>
                        <option value="family" {{ request('room_type') == 'family' ? 'selected' : '' }}>Phòng gia đình</option>
                     </select>
                  </div>

                  {{-- Số người --}}
                  <div class="col-md-2 mt-3">
                     <label for="guests" class="form-label">Số người</label>
                     <input type="number" class="form-control rounded-pill px-3" id="guests" name="guests" placeholder="2 người" min="1" step="1" value="{{ request('guests') ?? 2 }}">
                  </div>

                  {{-- Ngày thuê --}}
                  <div class="col-md-3 mt-3">
                     <label for="checkin" class="form-label">Ngày thuê</label>
                     <input type="text" class="form-control rounded-pill px-3" id="checkin" name="checkin" placeholder="Chọn ngày">
                  </div>

                  {{-- Ngày trả phòng --}}
                  <div class="col-md-3 mt-3">
                     <label for="checkout" class="form-label">Ngày trả phòng</label>
                     <input type="text" class="form-control rounded-pill px-3" id="checkout" name="checkout" placeholder="Chọn ngày">
                  </div>
               </div>

               {{-- Nút tìm --}}
               <div class="text-center mt-4">
                  <button class="submit-btn px-5 py-2 rounded-pill fw-bold">Tìm</button>
               </div>
            </form>

   
            
         </div>
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





 <script>// auto set ngày
    document.addEventListener("DOMContentLoaded", function () {
        
        let checkoutPicker;

        // Khởi tạo checkin picker
        flatpickr("#checkin", {
            dateFormat: "Y-m-d",
            minDate: "today",
            defaultDate: new Date(),
            locale: "vn",
            onChange: function (selectedDates, dateStr, instance) {
                if (selectedDates.length > 0) {
                    const checkinDate = selectedDates[0];
                    const nextDay = new Date(checkinDate);
                    nextDay.setDate(checkinDate.getDate() + 1);

                    // Cập nhật  minDate 
                    if (checkoutPicker) {
                        checkoutPicker.setDate(nextDay);
                        checkoutPicker.set("minDate", nextDay);
                    }
                }
            }
        });

        
        checkoutPicker = flatpickr("#checkout", {
            dateFormat: "Y-m-d",
            minDate: new Date().fp_incr(1), // Tối thiểu là ngày mai
            defaultDate: new Date().fp_incr(1),
            locale: "vn"
        });
    });
</script>
<script> //validate
            document.addEventListener('DOMContentLoaded', function () {
                const form = document.getElementById('first-form');
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
      console.log('✅ DOM đã sẵn sàng');
      console.log('📦 #location có tồn tại?', $('#location').length);

      $('#location').on('keyup', function () {
         let query = $(this).val();
         if (query.length >= 1) {
               $.ajax({
                  url: "{{ secure_url(route('locations.search', [], false)) }}",
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
               console.log('❌ Query rỗng, xóa dropdown');
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


