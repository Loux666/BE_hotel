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
                <h2>Danh sách Manager</h2>
                <div >
                    <form method="GET" action="{{ route('admin.showManager') }}">
                        <label for="hotel_id">Tìm theo khách sạn:</label>
                        <select name="hotel_id" id="hotel_id">
                            <option value="">-- Tất cả --</option>
                            @foreach ($hotels as $hotel)
                                <option value="{{ $hotel->id }}" {{ request('hotel_id') == $hotel->id ? 'selected' : '' }}>
                                    {{ $hotel->hotel_name }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit">Tìm kiếm</button>
                    </form>
                </div>

                <table class="table ">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Email</th>
                            <th>Tên</th>
                            <th>SĐT</th>
                            <th>Khách sạn quản lí</th>
                            <th>Ngày tạo</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody style="background: white">
                        @forelse ($managers as $key => $manager)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $manager->email }}</td>
                                <td>{{ $manager->name }}</td>
                                <td>{{ $manager->phone }}</td>
                                <td>{{ $manager->hotel->hotel_name }} </td>
                                <td>{{ $manager->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <!-- Nút sửa -->
                                    <a href="{{ route('admin.manager.edit', $manager->id) }}" class="btn btn-sm btn-primary">
                                        Sửa
                                    </a>

                                    <!-- Nút xóa -->
                                    <form action="{{ route('admin.manager.delete', $manager->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa?')">
                                        @csrf
                                        
                                        <button type="submit" class="btn btn-sm btn-danger">Xóa</button>
                                    </form>
                                </td>
                                                
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">Không có manager nào.</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
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
  
  <script src="admin/vendor/jquery-validation/jquery.validate.min.js"></script>
  <script src="admin/js/charts-home.js"></script>
  <script src="admin/js/front.js"></script>

  
</body>
</html>
