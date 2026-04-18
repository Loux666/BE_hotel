<div class="room_main">
   <div class="room_recommend">
      <div class="top-destinations">
         <div class="row">
            <div class="col-md-12">
               <div class="titlepage">
                  <h2>Các điểm đến nổi tiếng</h2>
                  <p>Một số điểm đến mà có thể bạn sẽ ghé qua</p>
               </div>
            </div>
         </div>
         <div class="body-carousel-container">
            <button class="body-nav left" onclick="scrollCarousel(-1)">&#10094;</button>

            <div class="body-carousel-wrapper">
               <div class="body-carousel">
                  
                  <a href="{{ route('hotels.by_city', ['city' => 'Đà Lạt']) }}" class="body-carousel-item">
                     <img src="images/dalat.jpg" alt="Đà Lạt">
                        <h4>Đà Lạt</h4>
                     
                  </a>
                  <a href="{{ route('hotels.by_city', ['city' => 'Đà Nẵng']) }}" class="body-carousel-item">
                     <img src="images/danang.webp" alt="Đà Nẵng">
                     <h4>Đà Nẵng</h4>
                     
                  </a>
                  <a href="{{ route('hotels.by_city', ['city' => 'Hà Nội']) }}" class="body-carousel-item">
                     <img src="images/hanoi.jpg" alt="Hà Nội">
                     <h4>Hà Nội</h4>
                     
                  </a>
                  <a href="{{ route('hotels.by_city', ['city' => 'Hồ Chí Minh']) }}" class="body-carousel-item">
                     <img src="images/tphcm.webp" alt="TP.HCM">
                     <h4>TP.HCM</h4>

                     
                  </a>
                  
                  <a href="{{ route('hotels.by_city', ['city' => 'Vũng Tàu']) }}" class="body-carousel-item">
                     <img src="images/vungtau.jpg" alt="Vũng Tàu">
                     <h4>Vũng Tàu</h4>                   
                  </a>

                  <a href="{{ route('hotels.by_city', ['city' => 'Nha Trang']) }}" class="body-carousel-item">
                     <img src="images/nhatrang.jpg" alt="Nha Trang">
                     <h4>Nha Trang</h4>
                  </a>

                  <a href="{{ route('hotels.by_city', ['city' => 'Hội An']) }}" class="body-carousel-item">
                     <img src="images/hoian.jpg" alt="Hội An">
                     <h4>Hội An</h4>
                  </a>

                  <a href="{{ route('hotels.by_city', ['city' => 'Phú Quốc']) }}" class="body-carousel-item">
                     <img src="images/phuquoc.webp" alt="Phú Quốc">
                     <h4>Phú Quốc</h4>
                  </a>
                  
                  <a href="{{ route('hotels.by_city', ['city' => 'Hạ Long']) }}" class="body-carousel-item">
                     <img src="images/halong.webp" alt="Hạ Long">
                     <h4>Hạ Long</h4>
                  </a>

                  <a href="{{ route('hotels.by_city', ['city' => 'Huế']) }}" class="body-carousel-item">
                     <img src="images/hue.jpg" alt="Huế">
                     <h4>Huế</h4>
                  </a>

                  <a href="{{ route('hotels.by_city', ['city' => 'Cô Tô']) }}" class="body-carousel-item">
                     <img src="images/coto.jpg" alt="Cô Tô">
                     <h4>Cô Tô</h4>
                  </a>
                  
               </div>
            </div>

            <button class="body-nav right" onclick="scrollCarousel(1)">&#10095;</button>
         </div>
      <div class="our_room">
         <div class="container "style="margin-top: 100px;">
            <div class="row">
               <div class="col-md-12">
                  <div class="titlepage">
                     <h2>Gợi ý cho bạn một số phòng tốt </h2>
                     
                  </div>
               </div>
            </div>
            <div class="row" style="margin-top: 50px;">
               @foreach ($rooms as $room)
                  <div class="col-md-4 col-sm-6 mb-4">
                     <a href="{{ route('hotel.show', ['id' => $room->hotel_id]) }}#room-{{ $room->room_id }}" style="text-decoration: none; color: inherit;">
                           <div class="room_card shadow-sm" style="border-radius: 12px; overflow: hidden; cursor: pointer;">
                              <div class="position-relative">
                                 <img src="{{ asset('roomImg/' . ($room->firstImage->image_path ?? 'default.jpg')) }}" alt="{{ $room->room_name }}" style="object-fit: cover; border-radius:9px">
                              </div>

                              <div class="p-3">
                                 <h5 class="mb-1" style="font-weight: bold;">{{ $room->room_name }}</h5>

                                 <p class="mb-1 text-muted">
                                       <i class="fa fa-map-marker-alt"></i>
                                       <span style="color: blue;">
                                          {{ $room->hotel->hotel_name ?? 'Tên KS' }} - {{ $room->hotel->hotel_city ?? 'Thành phố' }}
                                       </span>
                                 </p>
                                 <p class="mb-1" style="font-size: 14px; color: #666; font-style:italic">Giá mỗi đêm chưa gồm thuế và phí</p>
                                 <p class="mb-0" style="color: red; font-weight: bold;">
                                       VND {{ number_format($room->price, 0, ',', '.') }}
                                 </p>
                              </div>
                           </div>
                     </a>
                  </div>
               @endforeach
            </div>
         </div>
      </div>
</div>

   

<script>
   function scrollCarousel(direction) {
      const carousel = document.querySelector('.body-carousel');
      const item = document.querySelector('.body-carousel-item');
      if (!carousel || !item) return;

      const itemWidth = item.offsetWidth + 20; // item width + margin
      carousel.scrollBy({
         left: direction * itemWidth * 5,
         behavior: 'smooth'
      });
   }
</script>

