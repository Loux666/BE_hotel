<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Manager Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
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
        /* CSS cho trang quản lý phòng khách sạn */
        /* Header */
        .main-body h2 {
            color: #333;
            font-size: 22px;
            margin-bottom: 20px;
        }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border: 1px solid #ddd;
        }

        table th {
            background: #f5f5f5;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 1px solid #ddd;
        }

        table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            color: #555;
            text-align: center;
        }

        table tr:hover {
            background: #f9f9f9;
        }

        /* Image */
        table img {
            border-radius: 4px;
        }

        /* Buttons */
        .btn {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
            margin: 2px;
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-primary {
            background: #007bff;
            color: white;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn:hover {
            opacity: 0.8;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
        }

        .modal-dialog {
            max-width: 500px;
            margin: 50px auto;
        }

        .modal-content {
            background: white;
            border-radius: 6px;
            border: 1px solid #ddd;
        }

        .modal-header {
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            background: #f8f9fa;
        }

        .modal-title {
            font-size: 18px;
            color: #333;
            margin: 0;
        }

        .btn-close {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: #999;
        }

        .modal-body {
            padding: 20px;
        }

        .modal-footer {
            padding: 15px 20px;
            border-top: 1px solid #eee;
            background: #f8f9fa;
            text-align: right;
        }

        .btn-sm {
            padding: 0.6rem 1.2rem !important;
            min-width: 100px !important;
            font-size: 0.85rem !important;
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
                    <a href="{{route('manager.showRoom')}}" class="menu-item active">
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
                    <a href="{{route('manager.showGuest')}}" class="menu-item">
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
                <h2>Danh sách phòng trong khách sạn</h2>

                <div class="addbutton">
                    <form action="{{ route('manager.rooms.create') }}" method="GET" style="display: inline-block;">
                        <button type="submit" class="btn btn-sm btn-warning">Thêm phòng mới</button>
                    </form>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Tên phòng</th>
                            <th>Ảnh</th>
                            <th>Loại</th>
                            <th>Giá (VNĐ/đêm)</th>
                            <th>Mô tả</th>
                            <th>Dành cho tối đa (người)</th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rooms as $room)
                        <tr>
                            <td>{{ $room->room_name }}</td>
                            <td>
                            <img src="{{ asset('roomImg/' . $room->images->first()->image_path) }}"
                                width="100" height="70" style="object-fit: cover;">
                            </td>
                            <td>{{ $room->type }}</td>
                            <td>{{ number_format($room->price) }}</td>
                            <td>{{ $room->description }}</td>
                            <td>{{ $room->capacity }}</td>
                            <td>
                            <td>
                                
                                <form action="{{ route('manager.rooms.edit', $room->id) }}" method="GET" style="display: inline-block;">
                                    <button type="submit" class="btn btn-sm btn-warning">Sửa</button>
                                </form>
                            </td>
                            <td>
                                <form action="{{ route('manager.rooms.delete', $room->id) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Bạn có chắc muốn xóa phòng này không?')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-danger">Xóa</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    </table>
                
                
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

