<!DOCTYPE html>
<html>
  <head> 
    <base href="/public">
    @include('admin.css')

    <style type="text/css">
    label{
        display: inline-block;
        width: 200px;
    }
    .div_des{
        padding-top: 10px;
        margin: 5px;
    }
    .div_center{
        
        padding-top: 40px;
    }
    </style>
  </head>
  <body>
    @include('admin.header')   
    
    @include('admin.sidebar')
    <div class="page-content">
        <div class="page-header">
          <div class="container-fluid">

            <div class="div_center">
                <h1 style="font-size:30px; font-weight:bold; ">THÊM KHÁCH SẠN MỚI</h1>
                <form action="{{url('add_hotel')}}" method="Post" enctype="multipart/form-data" id="hotel-form">

                    @csrf
                    
                    <div class="div_des">
                        <label>Hotel Name</label>
                        <input type="text" name="hotel_name" id="hotel_name" >
                        <div id="error-hotel_name" style="color: red; margin-top: 5px;"></div>
                    </div>
                    <div class="div_des">
                        <label>Upload Image</label>
                        <input type="file" name="image">
                    </div>
                    <div class="div_des">
                        <label>City</label>
                        <select name="city" id="">
                            <option value="Hà Nội">Hà Nội</option>
                            <option value="Hồ Chí Minh">Hồ Chí Minh</option>
                            <option value="Vũng Tàu">Vũng Tàu</option>
                            <option value="Đà Nẵng">Đà Nẵng</option>
                            <option value="Nha Trang">Nha Trang</option>
                            <option value="Phú Quốc">Phú Quốc</option>
                            <option value="Đà Lạt">Đà Lạt</option>
                            <option value="Hạ Long">Hạ Long</option>
                            
                        </select>
                    </div>
                    <div class="div_des">
                        <label>Address</label>
                        <input type="text" name="address" id="address" placeholder="Nhập địa chỉ" >
                        <div id="error-hotel_address" style="color: red; margin-top: 5px;"></div>
                    </div>
                    <div class="div_des">
                        <label>Description</label>
                        <textarea name="description" id="description"></textarea>
                    </div>
                    <div class="div_des">
                        <label>Phone</label>
                        <input type="phone" name="phone" id="phone" placeholder="Nhập số điện thoại" >
                        <div id="error-hotel_phone" style="color: red; margin-top: 5px;"></div>
                    </div>
                    <div class="div_des">
                        <label>Email</label>
                        <input type="email" name="email" id="email" placeholder="Nhập email" >
                        <div id="error-hotel_email" style="color: red; margin-top: 5px;"></div>
                    </div>
                    
                    
                    </div>
                    <div class="div_des">
                        <input class="btn btn-primary" type="submit" value="Add Hotel">
                    </div>

                    
                </form>
            </div>

          </div>
        </div>
    </div>
      
        @include('admin.footer')
      </div>
    </div>
    <!-- JavaScript files-->
    <script src="admin/vendor/jquery/jquery.min.js"></script>
    <script src="admin/vendor/popper.js/umd/popper.min.js"> </script>
    <script src="admin/vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="admin/vendor/jquery.cookie/jquery.cookie.js"> </script>
    <script src="admin/vendor/chart.js/Chart.min.js"></script>
    <script src="admin/vendor/jquery-validation/jquery.validate.min.js"></script>
    <script src="admin/js/charts-home.js"></script>
    <script src="admin/js/front.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Toastr CSS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

<!-- Toastr JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
        
        
    @if (Session::has('success'))
    <script>
        const navType = performance.getEntriesByType("navigation")[0]?.type;
        if (navType !== "back_forward") {
            toastr.options = {
                progressBar: true,
                closeButton: true
            };
            toastr.success("{{ Session::get('success') }}", 'Success', {
                timeOut: 12000,
                positionClass: 'toast-top-right'
            });
        }
    </script>
    @php Session::forget('success'); @endphp
    @elseif (Session::has('error'))
        <script>
            const navType = performance.getEntriesByType("navigation")[0]?.type;
            if (navType !== "back_forward") {
                toastr.options = {
                    progressBar: true,
                    closeButton: true
                };
                toastr.error("{{ Session::get('error') }}", 'Error', {
                    timeOut: 12000,
                    positionClass: 'toast-top-right'
                });
            }
        </script>
        @php Session::forget('error'); @endphp
    @endif

    
    <script> // Validate
        const form = document.getElementById('hotel-form'); // ✅ Khai báo biến form đúng
    
        form.addEventListener('submit', function(event) {
            event.preventDefault(); // Ngăn gửi form mặc định lúc đầu
    
            let hasError = false;
    
            
            document.getElementById('error-hotel_name').innerText = '';
            document.getElementById('error-hotel_address').innerText = '';
            document.getElementById('error-hotel_phone').innerText = '';
            document.getElementById('error-hotel_email').innerText = '';
    
            
            const hotelName = document.getElementById('hotel_name').value.trim();
            if (hotelName === '') {
                document.getElementById('error-hotel_name').innerText = 'Vui lòng nhập tên khách sạn';
                hasError = true;
            } else if (hotelName.length > 255) {
                document.getElementById('error-hotel_name').innerText = 'Tên khách sạn không quá 255 kí tự';
                hasError = true;
            }
    
            
            const address = document.getElementById('address').value.trim();
            if (address === '') {
                document.getElementById('error-hotel_address').innerText = 'Vui lòng nhập địa chỉ';
                hasError = true;
            } else if (address.length > 255) {
                document.getElementById('error-hotel_address').innerText = 'Địa chỉ không quá 255 kí tự';
                hasError = true;
            }
    
            
            const phone = document.getElementById('phone').value.trim();
            if (phone === '') {
                document.getElementById('error-hotel_phone').innerText = 'Vui lòng nhập số điện thoại';
                hasError = true;
            } else if (!/^\d{10,11}$/.test(phone)) {
                document.getElementById('error-hotel_phone').innerText = 'Số điện thoại không hợp lệ';
                hasError = true;
            }
    
            
            const email = document.getElementById('email').value.trim();
            if (email === '') {
                document.getElementById('error-hotel_email').innerText = 'Vui lòng nhập email';
                hasError = true;
            } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                document.getElementById('error-hotel_email').innerText = 'Email không hợp lệ';
                hasError = true;
            }
    
            
            if (!hasError) {
                form.submit();
            }
        });
    </script>
    
  </body>
</html>