<!DOCTYPE html>
<html>
<head>
    <base href="/public">
  @include('admin.css')
  <style>
    .input{
        max-width: 400px;
    }
    </style>
</head>
<body>
  @include('admin.header')
  @include('admin.sidebar')

  <div class="page-content">
    <div class="page-header">
      <div class="container-fluid">
        <div class="container mt-4">
            
            <div style="max-width: 600px;">
                <h2 style=" margin-bottom: 24px;">Tạo tài khoản quản lý khách sạn</h2>

                <form method="POST" action="{{ route('admin.manager.store') }}" id="managerForm">
                    @csrf

                    <!-- Họ tên -->
                    <div style="margin-bottom: 16px;">
                        <label for="name">Họ tên</label>
                        <input type="text" name="name" id="name" style="width: 100%; padding: 8px;" value="{{ old('name') }}">
                        @error('name')
                            <div style="color: red; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div style="margin-bottom: 16px;">
                        <label for="email">Email quản lý</label>
                        <input type="email" name="email" id="email" style="width: 100%; padding: 8px;" value="{{ old('email') }}">
                        @error('email')
                            <div style="color: red; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Số điện thoại -->
                    <div style="margin-bottom: 16px;">
                        <label for="phone">SĐT quản lý</label>
                        <input type="tel" name="phone" id="phone" style="width: 100%; padding: 8px;" value="{{ old('phone') }}">
                        @error('phone')
                            <div style="color: red; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Khách sạn -->
                    <div style="margin-bottom: 16px;">
                        <label for="hotel_id">Khách sạn quản lý</label>
                        <select name="hotel_id" id="hotel_id" style="width: 100%; padding: 8px;">
                            <option value="">-- Chọn khách sạn --</option>
                            @foreach ($hotels as $hotel)
                                <option value="{{ $hotel->id }}" {{ old('hotel_id') == $hotel->id ? 'selected' : '' }}>
                                    {{ $hotel->hotel_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('hotel_id')
                            <div style="color: red; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Mật khẩu -->
                    <div style="margin-bottom: 16px;">
                        <label for="password">Mật khẩu</label>
                        <input type="password" name="password" id="password" style="width: 100%; padding: 8px;">
                        @error('password')
                            <div style="color: red; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Xác nhận mật khẩu -->
                    <div style="margin-bottom: 24px;">
                        <label for="password_confirmation">Xác nhận mật khẩu</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" style="width: 100%; padding: 8px;">
                        @error('password_confirmation')
                            <div style="color: red; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Nút submit -->
                    <div style="text-align: center;">
                        <button type="submit" style="padding: 10px 24px; background: #007bff; color: white; border: none; cursor: pointer;">
                            Tạo tài khoản
                        </button>
                    </div>
                </form>
            </div>
                
      </div>
    </div>
  </div>
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

  {{-- Các script khác --}}
  <script src="admin/vendor/jquery/jquery.min.js"></script>
  <script src="admin/vendor/popper.js/umd/popper.min.js"></script>
  <script src="admin/vendor/bootstrap/js/bootstrap.min.js"></script>
  <script src="admin/vendor/jquery.cookie/jquery.cookie.js"></script>
  <script src="admin/vendor/chart.js/Chart.min.js"></script>
  <script src="admin/vendor/jquery-validation/jquery.validate.min.js"></script>
  <script src="admin/js/charts-home.js"></script>
  <script src="admin/js/front.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('managerForm');

        form.addEventListener('submit', function (e) {
            let isValid = true;

            // Clear lỗi cũ
            document.querySelectorAll('.error-message').forEach(el => el.remove());

            // Lấy dữ liệu input
            const name = form.name.value.trim();
            const email = form.email.value.trim();
            const phone = form.phone.value.trim();
            const hotel = form.hotel_id.value;
            const password = form.password.value;
            const confirm = form.password_confirmation.value;

            // Hàm hiển thị lỗi
            const showError = (field, message) => {
                const input = form[field];
                const errorDiv = document.createElement('div');
                errorDiv.className = 'error-message';
                errorDiv.style.color = 'red';
                errorDiv.style.marginTop = '4px';
                errorDiv.textContent = message;
                input.parentNode.appendChild(errorDiv);
                isValid = false;
            };

            // Validate từng field
            if (!name) showError('name', 'Vui lòng nhập họ tên');
            if (!email) showError('email', 'Vui lòng nhập email');
            else if (!/^\S+@\S+\.\S+$/.test(email)) showError('email', 'Email không hợp lệ');

            if (!phone) showError('phone', 'Vui lòng nhập SĐT');
            else if (!/^[0-9]{9,12}$/.test(phone)) showError('phone', 'SĐT không hợp lệ');

            if (!hotel) showError('hotel_id', 'Vui lòng chọn khách sạn');

            if (!password) showError('password', 'Vui lòng nhập mật khẩu');
            else if (password.length < 6) showError('password', 'Mật khẩu tối thiểu 6 ký tự');

            if (confirm !== password) showError('password_confirmation', 'Xác nhận mật khẩu không khớp');

            if (!isValid) e.preventDefault();
        });
    });
</script>

  
</body>
</html>




