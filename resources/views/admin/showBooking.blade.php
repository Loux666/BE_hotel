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

        
        
        <h2>Danh sách đơn đặt phòng (Đã xác nhận)</h2>
        <form action="{{ route('admin.showBooking') }}" method="GET" class="mb-3">
            <div class="input-group" style="max-width: 300px;">
                <input type="text" name="phone" class="form-control" placeholder="Tìm theo số điện thoại"
                    value="{{ request('phone') }}">
                <button class="btn btn-primary" type="submit">Tìm</button>
            </div>
        </form>

   

    @if ($bookings->isEmpty())
        <div class="alert alert-info mt-4">Chưa có đơn đặt phòng nào được xác nhận.</div>
    @else
        <div class="table-responsive mt-3">
            <table class="table table-bordered text-center">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>Khách hàng</th>
                        <th>SĐT</th>
                        
                        <th>Tổng tiền</th>
                        <th>Ngày đặt</th>
                        <th>Trạng thái</th>
                        <th>Thanh toán</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($bookings as $key => $booking)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>{{ $booking->guest_name ?? 'N/A' }}</td>
                            <td>{{ $booking->guest_phone ?? 'N/A' }}</td>
                            
                            <td>{{ number_format($booking->total_price) }} VNĐ</td>
                            <td>{{ \Carbon\Carbon::parse($booking->created_at)->format('d/m/Y H:i') }}</td>
                            <td>
                                <span class="badge badge-success">Đã xác nhận</span>
                            </td>
                            <td>{{$booking->payment_status == 'paid' ? 'Đã thanh toán' : "Chưa thanh toán"}}</td>
                            <td>
                              <button class="btn btn-sm btn-danger mt-2" data-toggle="modal" data-target="#cancelModal" data-id="{{ $booking->id }}">
                                  Hủy đơn
                              </button>

                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
        
        

      </div>
    </div>
  </div>

  {{-- SCRIPT --}}
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
<div class="modal fade" id="cancelModal" tabindex="-1" role="dialog" aria-labelledby="cancelModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form method="POST" action="" id="cancelForm">
        @csrf
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Xác nhận hủy đơn</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Đóng">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                Bạn có chắc chắn muốn hủy đơn này không?
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                <button type="submit" class="btn btn-danger">Xác nhận hủy</button>
            </div>
        </div>
    </form>
  </div>
</div>
<script>
    $('#cancelModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var bookingId = button.data('id');
        var form = $('#cancelForm');

        var actionUrl = "{{ route('admin.refunds.manual.process', ['refund' => ':id']) }}";
        form.attr('action', actionUrl.replace(':id', bookingId));
    });
</script>