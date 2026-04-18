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
        <h2 class="mb-4">Tạo mã giảm giá mới</h2>

        <form action="{{ route('coupon.store') }}" method="POST" id="couponForm">
            @csrf

            <div class="row">
                <div class="col-md-6">
                    <label>Mã giảm giá</label>
                    <input type="text" name="code" class="form-control" required>
                    @error('code')
                        <div style="color:red; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label>Loại giảm giá</label>
                    <select name="type" class="form-control" required>
                        <option value="percent">Phần trăm (%)</option>
                        <option value="fixed">Số tiền (VNĐ)</option>
                    </select>
                    @error('type')
                        <div style="color:red; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mt-3">
                    <label>Giá trị giảm</label>
                    <input type="number" name="value" class="form-control" required>
                    @error('value')
                        <div style="color:red; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mt-3">
                    <label>Đơn tối thiểu</label>
                    <input type="number" name="min_order_price" class="form-control" required>
                    @error('min_order_price')
                        <div style="color:red; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mt-3">
                    <label>Tổng lượt dùng</label>
                    <input type="number" name="max_uses" class="form-control" required>
                    @error('max_uses')
                        <div style="color:red; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mt-3">
                    <label>Mỗi user dùng tối đa</label>
                    <input type="number" name="user_limit" class="form-control" required>
                    @error('user_limit')
                        <div style="color:red; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mt-3">
                    <label>Ngày bắt đầu</label>
                    <input type="date" name="start_date" class="form-control" required>
                    @error('start_date')
                        <div style="color:red; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mt-3">
                    <label>Ngày kết thúc</label>
                    <input type="date" name="end_date" class="form-control" required>
                    @error('end_date')
                        <div style="color:red; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mt-3">
                    <input type="hidden" name="is_active" value="0">
                    <label>
                        <input type="checkbox" name="is_active" value="1" checked>
                        Kích hoạt ngay
                    </label>
                    @error('is_active')
                        <div style="color:red; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-success">Tạo mã</button>
                <a href="{{ route('coupon.manage') }}" class="btn btn-secondary">Quay lại</a>
            </div>
        </form>

</div>




        
      </div>
    </div>
  </div>



  {{-- Các script khác --}}
  <script src="admin/vendor/jquery/jquery.min.js"></script>
  <script src="admin/vendor/popper.js/umd/popper.min.js"></script>
  <script src="admin/vendor/bootstrap/js/bootstrap.min.js"></script>
  <script src="admin/vendor/jquery.cookie/jquery.cookie.js"></script>
  <script src="admin/vendor/chart.js/Chart.min.js"></script>
  <script src="admin/vendor/jquery-validation/jquery.validate.min.js"></script>
  <script src="admin/js/charts-home.js"></script>
  <script src="admin/js/front.js"></script>
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
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('couponForm');

        form.addEventListener('submit', function (e) {
            let isValid = true;
            document.querySelectorAll('.js-error').forEach(el => el.remove());

            const showError = (name, msg) => {
                const input = form[name];
                const err = document.createElement('div');
                err.className = 'js-error';
                err.style.color = 'red';
                err.style.marginTop = '4px';
                err.innerText = msg;
                input.parentNode.appendChild(err);
                isValid = false;
            };

            const getVal = (name) => form[name].value.trim();

            const code = getVal('code');
            const value = getVal('value');
            const min_order = getVal('min_order_price');
            const max_uses = getVal('max_uses');
            const user_limit = getVal('user_limit');
            const start = getVal('start_date');
            const end = getVal('end_date');

            if (!code) showError('code', 'Vui lòng nhập mã');
            if (!value || value <= 0) showError('value', 'Giá trị giảm không hợp lệ');
            if (!min_order || min_order < 0) showError('min_order_price', 'Đơn tối thiểu không hợp lệ');
            if (!max_uses || max_uses <= 0) showError('max_uses', 'Tổng lượt dùng phải > 0');
            if (!user_limit || user_limit <= 0) showError('user_limit', 'Giới hạn user phải > 0');
            if (!start) showError('start_date', 'Chọn ngày bắt đầu');
            if (!end) showError('end_date', 'Chọn ngày kết thúc');
            else if (start > end) showError('end_date', 'Ngày kết thúc phải sau ngày bắt đầu');

            if (!isValid) e.preventDefault();
        });
    });
</script>

  
</body>
</html>




    