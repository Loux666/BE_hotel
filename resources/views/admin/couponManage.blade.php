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
            <h2 class="mb-4">Quản lý mã giảm giá</h2>

            <a href="{{route('coupon.add')}}" class="btn btn-primary mb-3">+ Tạo mã mới</a>

            <div class="table-responsive">
                <table class="table  table-hover text-center">
                    <thead  style="background: white">
                        <tr >
                            <th>#</th>
                            <th>Mã</th>
                            <th>Loại</th>
                            <th>Giá trị</th>
                            <th>Đơn tối thiểu</th>
                            <th>Lượt dùng</th>
                            <th>HSD</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody style="background: white">
                        @forelse ($coupons as $key => $coupon)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td><strong>{{ $coupon->code }}</strong></td>
                                <td>{{ ucfirst($coupon->type) }}</td>
                                <td>
                                    @if ($coupon->type === 'percent')
                                        {{ $coupon->value }}%
                                    @else
                                        {{ number_format($coupon->value) }}đ
                                    @endif
                                </td>
                                <td>{{ number_format($coupon->min_order_price) }}đ</td>
                                <td>{{ $coupon->used_count }} / {{ $coupon->max_uses }}</td>
                                <td>{{ \Carbon\Carbon::parse($coupon->end_date)->format('d/m/Y') }}</td>
                                <td>
                                    @if ($coupon->is_active)
                                        <span class="badge badge-success">Đang hoạt động</span>
                                    @else
                                        <span class="badge badge-secondary">Đã tắt</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('coupon.edit', $coupon->id) }}" class="btn btn-sm btn-warning">Sửa</a>
                                    <form action="{{ route('coupon.delete', $coupon->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Xác nhận xoá mã này?')">
                                        @csrf
                                        @method('POST')
                                        <button type="submit" class="btn btn-sm btn-danger">Xoá</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9">Chưa có mã nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
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

  
</body>
</html>


