<!DOCTYPE html>
<html>
<head>
    <base href="/public">
  @include('admin.css')
  <style type="text/css">
    label {
      display: inline-block;
      width: 200px;
    }
    .div_des {
      padding-top: 10px;
      margin: 5px;
    }
    .div_center {
      padding-top: 40px;
    }

    #hotel-suggestions {
      position: absolute;
      background: white;
      width: 620px;
      border: 1px solid #ccc;
      z-index: 10;
      max-height: 300px;
      overflow-y: auto;
      box-shadow: 0 2px 6px rgba(0,0,0,0.2);
    }

    .suggestion-item {
      display: flex;
      gap: 10px;
      padding: 10px;
      cursor: pointer;
      border-bottom: 1px solid #eee;
    }

    .suggestion-item:hover {
      background-color: #f0f0f0;
    }

    .hotel-info {
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .suggestion-item img {
      border-radius: 6px;
      object-fit: cover;
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
            <div class="container mt-4">
                <h2 class="mb-4">Cập nhật mã giảm giá</h2>
                <form action="{{ route('coupon.update', $coupon->id) }}" method="POST" id="couponForm">
                @csrf

                <div class="row">
                    <div class="col-md-6">
                        <label>Mã giảm giá</label>
                        <input type="text" name="code" value="{{ old('code', $coupon->code) }}" class="form-control" required>
                        @error('code')
                            <div style="color:red; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label>Loại giảm giá</label>
                        <select name="type" class="form-control" required>
                            <option value="percent" {{ old('type', $coupon->type) == 'percent' ? 'selected' : '' }}>Phần trăm</option>
                            <option value="fixed" {{ old('type', $coupon->type) == 'fixed' ? 'selected' : '' }}>Số tiền</option>
                        </select>
                        @error('type')
                            <div style="color:red; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mt-3">
                        <label>Giá trị giảm</label>
                        <input type="number" name="value" value="{{ old('value', $coupon->value) }}" class="form-control" required>
                        @error('value')
                            <div style="color:red; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mt-3">
                        <label>Đơn tối thiểu</label>
                        <input type="number" name="min_order_price" value="{{ old('min_order_price', $coupon->min_order_price) }}" class="form-control" required>
                        @error('min_order_price')
                            <div style="color:red; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mt-3">
                        <label>Tổng lượt dùng</label>
                        <input type="number" name="max_uses" value="{{ old('max_uses', $coupon->max_uses) }}" class="form-control" required>
                        @error('max_uses')
                            <div style="color:red; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mt-3">
                        <label>Mỗi user dùng tối đa</label>
                        <input type="number" name="user_limit" value="{{ old('user_limit', $coupon->user_limit) }}" class="form-control" required>
                        @error('user_limit')
                            <div style="color:red; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mt-3">
                        <label>Ngày bắt đầu</label>
                        <input type="date" name="start_date" value="{{ old('start_date', \Carbon\Carbon::parse($coupon->start_date)->format('Y-m-d')) }}" class="form-control" required>
                        @error('start_date')
                            <div style="color:red; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mt-3">
                        <label>Ngày kết thúc</label>
                        <input type="date" name="end_date" value="{{ old('end_date', \Carbon\Carbon::parse($coupon->end_date)->format('Y-m-d')) }}" class="form-control" required>
                        @error('end_date')
                            <div style="color:red; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mt-3">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" class="form-check-input" name="is_active" value="1"
                            {{ old('is_active', $coupon->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label">Kích hoạt ngay</label>
                        @error('is_active')
                            <div style="color:red; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Cập nhật</button>
                    <a href="{{ route('coupon.manage') }}" class="btn btn-secondary">Quay lại</a>
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
        const form = document.getElementById('couponForm');

        form.addEventListener('submit', function (e) {
            let isValid = true;

            // Xóa thông báo lỗi cũ
            document.querySelectorAll('.js-error').forEach(el => el.remove());

            const showError = (inputName, message) => {
                const input = form[inputName];
                const errorDiv = document.createElement('div');
                errorDiv.className = 'js-error';
                errorDiv.style.color = 'red';
                errorDiv.style.marginTop = '4px';
                errorDiv.innerText = message;
                input.parentNode.appendChild(errorDiv);
                isValid = false;
            };

            // Lấy giá trị
            const code = form.code.value.trim();
            const value = form.value.value;
            const minOrder = form.min_order_price.value;
            const maxUses = form.max_uses.value;
            const userLimit = form.user_limit.value;
            const startDate = form.start_date.value;
            const endDate = form.end_date.value;

            // Validate
            if (!code) showError('code', 'Vui lòng nhập mã');
            if (!value || value <= 0) showError('value', 'Giá trị giảm không hợp lệ');
            if (!minOrder || minOrder < 0) showError('min_order_price', 'Đơn tối thiểu không hợp lệ');
            if (!maxUses || maxUses <= 0) showError('max_uses', 'Tổng lượt dùng phải > 0');
            if (!userLimit || userLimit <= 0) showError('user_limit', 'Giới hạn user phải > 0');
            if (!startDate) showError('start_date', 'Chọn ngày bắt đầu');
            if (!endDate) showError('end_date', 'Chọn ngày kết thúc');
            else if (startDate > endDate) showError('end_date', 'Ngày kết thúc phải >= ngày bắt đầu');

            if (!isValid) e.preventDefault();
        });
    });
</script>


  
</body>
</html>



