<!DOCTYPE html>
<html>
<head>
    <base href="/public">
  @include('admin.css')
</head>
<body>
  @include('admin.header')
  @include('admin.sidebar')

  <div class="page-content">
    <div class="page-header">
      <div class="container-fluid">
        <div class="container mt-4">
                
            <h2>Sửa thông tin Manager</h2>

            <form method="POST" action="{{ route('admin.manager.update', $managers->id) }}">
                @csrf

                <div class="form-group mt-2">
                    <label>Tên</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $managers->name) }}" required>
                    @error('name')
                        <div style="color:red; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group mt-2">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $managers->email) }}" required>
                    @error('email')
                        <div style="color:red; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group mt-2">
                    <label>SĐT</label>
                    <input type="tel" name="phone" class="form-control" value="{{ old('phone', $managers->phone) }}" required>
                    @error('phone')
                        <div style="color:red; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group mt-2">
                    <label>Khách sạn quản lý</label>
                    <select name="hotel_id" class="form-control">
                        <option value="">-- Không chọn --</option>
                        @foreach ($hotels as $hotel)
                            <option value="{{ $hotel->id }}" {{ old('hotel_id', $managers->hotel_id) == $hotel->id ? 'selected' : '' }}>
                                {{ $hotel->hotel_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('hotel_id')
                        <div style="color:red; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-success mt-3">Cập nhật</button>
</form>

                
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
  
  <script src="admin/vendor/jquery-validation/jquery.validate.min.js"></script>
  <script src="admin/js/charts-home.js"></script>
  <script src="admin/js/front.js"></script>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('form');
        form.addEventListener('submit', function (e) {
            let isValid = true;

            // Xoá lỗi cũ
            document.querySelectorAll('.js-error').forEach(el => el.remove());

            const showError = (input, message) => {
                const err = document.createElement('div');
                err.className = 'js-error';
                err.style.color = 'red';
                err.style.marginTop = '4px';
                err.textContent = message;
                input.parentNode.appendChild(err);
                isValid = false;
            };

            const name = form.querySelector('[name="name"]');
            const email = form.querySelector('[name="email"]');
            const phone = form.querySelector('[name="phone"]');
            const hotel = form.querySelector('[name="hotel_id"]');
            const password = form.querySelector('[name="password"]');
            const confirm = form.querySelector('[name="password_confirmation"]');

            if (!name.value.trim()) showError(name, 'Tên không được để trống');
            if (!email.value.trim() || !email.value.includes('@')) showError(email, 'Email không hợp lệ');
            if (!phone.value.trim()) showError(phone, 'SĐT không được để trống');

            // Nếu có nhập mật khẩu thì check confirm
            if (password && password.value.trim()) {
                if (password.value.length < 6) showError(password, 'Mật khẩu phải ít nhất 6 ký tự');
                if (password.value !== confirm.value) showError(confirm, 'Xác nhận mật khẩu không khớp');
            }

            if (!isValid) e.preventDefault();
        });
    });
</script>

</body>
</html>
