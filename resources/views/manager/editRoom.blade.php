<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Manager Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8fafc;
            height: 100vh;
            overflow: hidden;
        }

        .dashboard-container {
            display: flex;
            height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, #1e293b 0%, #334155 100%);
            color: white;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
            z-index: 1000;
            transition: transform 0.3s ease;
        }

        .sidebar.collapsed {
            transform: translateX(-280px);
        }

        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }

        .sidebar-header h2 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .sidebar-header .subtitle {
            font-size: 0.9rem;
            color: #94a3b8;
        }

        .sidebar-menu {
            flex: 1;
            padding: 20px 0;
            overflow-y: auto;
        }

        .menu-section {
            margin-bottom: 30px;
        }

        .menu-section-title {
            padding: 0 20px 10px;
            font-size: 0.8rem;
            text-transform: uppercase;
            color: #94a3b8;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .menu-item {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: #e2e8f0;
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
        }

        .menu-item:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .menu-item.active {
            background: linear-gradient(90deg, #3b82f6, #1d4ed8);
            color: white;
        }

        .menu-item.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background: #60a5fa;
        }

        .menu-item i {
            width: 20px;
            margin-right: 15px;
            font-size: 1.1rem;
        }

        .menu-item span {
            font-size: 0.95rem;
            font-weight: 500;
        }

        .menu-item .badge {
            margin-left: auto;
            background: #ef4444;
            color: white;
            font-size: 0.75rem;
            padding: 2px 8px;
            border-radius: 12px;
            min-width: 20px;
            text-align: center;
        }

        .sidebar-footer {
            padding: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(45deg, #3b82f6, #1d4ed8);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }

        .user-info h4 {
            font-size: 0.9rem;
            margin-bottom: 2px;
        }

        .user-info span {
            font-size: 0.8rem;
            color: #94a3b8;
        }

        /* Main Content Styles */
        .main-content {
            flex: 1;
            margin-left: 280px;
            display: flex;
            flex-direction: column;
            height: 100vh;
            transition: margin-left 0.3s ease;
        }

        .main-content.expanded {
            margin-left: 0;
        }

        .main-header {
            background: white;
            padding: 20px 30px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .main-header-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .toggle-sidebar {
            background: none;
            border: none;
            font-size: 1.2rem;
            color: #64748b;
            cursor: pointer;
            padding: 8px;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .toggle-sidebar:hover {
            background: #f1f5f9;
            color: #334155;
        }

        .page-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #1e293b;
        }

        .main-header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .header-actions {
            display: flex;
            gap: 15px;
        }

        .header-btn {
            background: #f1f5f9;
            border: none;
            padding: 10px 12px;
            border-radius: 8px;
            color: #64748b;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.2s ease;
            position: relative;
        }

        .header-btn:hover {
            background: #e2e8f0;
            color: #334155;
        }

        .header-btn .notification-dot {
            position: absolute;
            top: 6px;
            right: 6px;
            width: 8px;
            height: 8px;
            background: #ef4444;
            border-radius: 50%;
        }

        .current-time {
            background: linear-gradient(45deg, #3b82f6, #1d4ed8);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .main-body {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
            background: #f8fafc;
        }
        .booking-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid #007bff;
            display: flex;
            gap: 30px;
        }

        .guest-info {
            flex: 1;
            min-width: 300px;
        }

        .guest-name {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
        }

        .guest-details {
            color: #666;
            font-size: 14px;
            line-height: 1.8;
        }

        .guest-details div {
            margin-bottom: 8px;
        }

        .rooms-section {
            flex: 2;
            min-width: 400px;
        }

        .rooms-title {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }

        .room-item {
            display: flex;
            align-items: center;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 6px;
            margin-bottom: 8px;
            gap: 15px;
        }

        .room-image {
            width: 80px;
            height: 60px;
            border-radius: 4px;
            object-fit: cover;
            border: 1px solid #ddd;
        }

        .room-info {
            flex: 1;
        }

        .room-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 4px;
        }

        .room-nights {
            color: #666;
            font-size: 14px;
        }

        .room-edit-form {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #333;
            margin-bottom: 25px;
            text-align: center;
            font-size: 24px;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #555;
            font-size: 14px;
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
        }

        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }

        .form-control-file {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: white;
        }

        .room-edit-image-wrapper {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 10px;
        }

        .room-edit-image-box {
            position: relative;
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 4px;
            overflow: hidden;
        }

        .room-edit-image-box img {
            width: 100%;
            height: 120px;
            object-fit: cover;
            display: block;
        }

        .room-edit-image-box form {
            position: absolute;
            top: 5px;
            right: 5px;
            margin: 0;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-primary {
            background-color: #007bff;
            color: white;
            width: 100%;
            margin-top: 20px;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .btn-danger {
            background-color: #dc3545;
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            line-height: 1;
        }

        .btn-danger:hover {
            background-color: #c82333;
        }

        .btn-sm {
            padding: 4px 8px;
            font-size: 12px;
        }
        .room-edit-error {
            font-size: 13px;
            color: red;
            margin-top: 4px;
        }
        

        
        



        
        
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Mobile Overlay -->
        <div class="mobile-overlay" id="mobileOverlay"></div>

        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-hotel"></i> Hotel Manager</h2>
                <div class="subtitle">Quản lý khách sạn</div>
            </div>
            
            <div class="sidebar-menu">
                <!-- Dashboard Section -->
                <div class="menu-section">
                    <div class="menu-section-title">Dashboard</div>
                    <a href="{{route('manager.dashboard')}}" class="menu-item ">
                        <i class="fas fa-chart-pie"></i>
                        <span>Tổng quan</span>
                    </a>
                    <a href="{{route('manager.showChart')}}" class="menu-item">
                        <i class="fas fa-chart-line"></i>
                        <span>Thống kê</span>
                    </a>
                    <a href="{{route('manager.showbooking')}}" class="menu-item">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Đơn đặt phòng</span>
                    </a>
                    <a href="{{route('manager.showPayment')}}" class="menu-item ">
                        <i class="fas fa-credit-card"></i>
                        <span>Đơn thanh toán</span>
                    </a>
                    <a href="{{route('manager.showCancel')}}" class="menu-item ">
                        <i class="fas fa-credit-card"></i>
                        <span>Đơn chờ hủy</span>
                    </a>
                </div>

                <!-- Quản lý phòng -->
                <div class="menu-section">
                    <div class="menu-section-title">Quản lý phòng</div>
                    <a href="{{route('manager.showRoom')}}" class="menu-item">
                        <i class="fas fa-bed"></i>
                        <span>Danh sách phòng</span>
                    </a>
                    
                    <a href="{{route('manager.showMaintenance')}}" class="menu-item">
                        <i class="fas fa-tools"></i>
                        <span>Bảo trì phòng</span>
                        
                    </a>
                    
                </div>

                <!-- Khách hàng -->
                <div class="menu-section">
                    <div class="menu-section-title">Khách hàng</div>
                    <a href="{{route('manager.showGuest')}}" class="menu-item ">
                        <i class="fas fa-users"></i>
                        <span>Danh sách khách</span>
                    </a>
                    
                    <a href="{{route('manager.showFeedback')}}" class="menu-item">
                        <i class="fas fa-star"></i>
                        <span>Đánh giá</span>
                    </a>
                </div>
            </div>

            <div class="sidebar-footer">
                <div class="user-profile">
                    <div class="user-avatar">NV</div>
                    <div class="user-info">
                        <h4>{{$name}}</h4>
                        <span>Hotel Manager</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content" id="mainContent">
            <!-- Header -->
            <div class="main-header">
                <div class="main-header-left">
                    <button class="toggle-sidebar" id="toggleSidebar">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="page-title">Tổng quan tại {{$hotel_name}}</h1>
                </div>
                <div class="main-header-right">
                    <div class="header-actions">
                        <button class="header-btn">
                            <i class="fas fa-bell"></i>
                            <span class="notification-dot"></span>
                        </button>
                        <button class="header-btn">
                            <i class="fas fa-envelope"></i>
                        </button>
                        <button class="header-btn">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <div class="current-time" id="currentTime"></div>
                    <div class="list-inline-item logout">    
                        <form method="POST" id="logout-form" action="{{ route('logout') }}">
                        @csrf
                        <a href="#" class="text-red-600 hover:underline" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            Đăng xuất
                        </a>
                        </form>   
                    </div>
                </div>
            </div>

            <!-- Main Body Content -->
            <div class="main-body">
                <div class="room-edit-form">
                    <h2>Cập nhật phòng: {{ $room->room_name }}</h2>

                    {{-- Hiển thị ảnh cũ và nút xoá riêng từng ảnh --}}
                    <div class="form-group">
                        <label>Ảnh hiện tại:</label>
                        <div class="room-edit-image-wrapper">
                        @foreach ($room->images as $image)
                            <div class="room-edit-image-box">
                            <img src="{{ asset('roomImg/' . $image->image_path) }}">
                            <form action="{{ route('manager.rooms.deleteImage', $image->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn xoá ảnh này?')">
                                @csrf
                                
                                <button type="submit" class="btn btn-sm btn-danger">×</button>
                            </form>
                            </div>
                        @endforeach
                        </div>
                    </div>

                    {{-- Form cập nhật --}}
                    <form id="roomEditForm" action="{{ route('manager.rooms.update', $room->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        

                        <div class="form-group">
                        <label for="room_name">Tên phòng</label>
                        <input type="text" class="form-control" name="room_name" id="room_name" value="{{ old('room_name', $room->room_name) }}">
                        <div class="room-edit-error" id="room_name_error"></div>
                        </div>

                        <div class="form-group">
                        <label for="type">Loại phòng</label>
                        <select class="form-control" name="type" id="type">
                            <option value="standard" {{ old('type', $room->type) == 'standard' ? 'selected' : '' }}>Standard</option>
                            <option value="deluxe" {{ old('type', $room->type) == 'deluxe' ? 'selected' : '' }}>Deluxe</option>
                            <option value="family" {{ old('type', $room->type) == 'family' ? 'selected' : '' }}>Family</option>
                        </select>
                        <div class="room-edit-error" id="type_error"></div>
                        </div>

                        <div class="form-group">
                        <label for="price">Giá (VNĐ/đêm)</label>
                        <input type="number" class="form-control" name="price" id="price" value="{{ old('price', $room->price) }}">
                        <div class="room-edit-error" id="price_error"></div>
                        </div>

                        <div class="form-group">
                        <label for="capacity">Sức chứa tối đa</label>
                        <input type="number" class="form-control" name="capacity" id="capacity" value="{{ old('capacity', $room->capacity) }}">
                        <div class="room-edit-error" id="capacity_error"></div>
                        </div>

                        <div class="form-group">
                        <label for="description">Mô tả</label>
                        <textarea class="form-control" name="description" id="description">{{ old('description', $room->description) }}</textarea>
                        
                        </div>

                        <div class="form-group">
                        <label for="images">Thêm ảnh mới</label>
                        <input type="file" name="images[]" id="images" class="form-control-file" multiple>
                        </div>

                        <button type="submit" class="btn btn-primary">Cập nhật</button>
                    </form>
                    </div>
            </div>
        </div>
    </div>

    <script>
        // Update current time
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('vi-VN', {
                hour: '2-digit',
                minute: '2-digit'
            });
            const dateString = now.toLocaleDateString('vi-VN', {
                day: '2-digit',
                month: '2-digit'
            });
            document.getElementById('currentTime').textContent = `${timeString} ${dateString}`;
        }
        // Initialize
        updateTime();
        setInterval(updateTime, 1000);
        
        // Toggle sidebar button
        document.getElementById('toggleSidebar').addEventListener('click', toggleSidebar);
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            

            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
            
        }

        // Handle window resize
        window.addEventListener('resize', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const overlay = document.getElementById('mobileOverlay');
            
            if (window.innerWidth > 768) {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            }
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('roomEditForm');

            form.addEventListener('submit', function (e) {
                let hasError = false;

                // Reset lỗi cũ
                document.querySelectorAll('.room-edit-error').forEach(el => el.innerText = '');

                // Validate tên phòng
                const roomName = document.getElementById('room_name');
                const roomNameError = document.getElementById('room_name_error');
                if (!roomName.value.trim()) {
                    roomNameError.innerText = 'Tên phòng không được để trống';
                    hasError = true;
                }

                // Validate loại phòng
                const type = document.getElementById('type');
                const typeError = document.getElementById('type_error');
                if (!type.value) {
                    typeError.innerText = 'Vui lòng chọn loại phòng';
                    hasError = true;
                }

                // Validate giá
                const price = document.getElementById('price');
                const priceError = document.getElementById('price_error');
                if (!price.value.trim()) {
                    priceError.innerText = 'Giá không được để trống';
                    hasError = true;
                } else if (parseInt(price.value) <= 0) {
                    priceError.innerText = 'Giá phải lớn hơn 0';
                    hasError = true;
                }

                // Validate sức chứa
                const capacity = document.getElementById('capacity');
                const capacityError = document.getElementById('capacity_error');
                if (!capacity.value.trim()) {
                    capacityError.innerText = 'Sức chứa không được để trống';
                    hasError = true;
                } else if (parseInt(capacity.value) <= 0) {
                    capacityError.innerText = 'Sức chứa phải lớn hơn 0';
                    hasError = true;
                }

                // Validate mô tả (optional - có thể bỏ qua nếu không bắt buộc)
                const description = document.getElementById('description');
                if (description.value.trim().length > 1000) {
                    // Nếu bạn muốn giới hạn độ dài mô tả
                    // Thêm error element cho description nếu cần
                    console.log('Mô tả quá dài');
                }

                // Ngăn form submit nếu có lỗi
                if (hasError) {
                    e.preventDefault();
                    console.log("Form không được gửi do có lỗi validation.");
                    return false;
                }
            });
        });

    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
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
                timeOut: 10000,
                positionClass: 'toast-top-right'
            });
        }
    </script>
    @php Session::forget('success'); @endphp
    @endif
    @if (Session::has('error'))
    <script>
        const navType = performance.getEntriesByType("navigation")[0]?.type;
        if (navType !== "back_forward") {
            toastr.options = {
                progressBar: true,
                closeButton: true
            };
            toastr.error("{{ Session::get('error') }}", 'Error', {
                timeOut: 10000,
                positionClass: 'toast-top-right'
            });
        }
    </script>
    @php Session::forget('error'); @endphp
    @endif
</body>
</html>