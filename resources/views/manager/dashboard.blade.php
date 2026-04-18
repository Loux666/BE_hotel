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

        .content-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .welcome-section {
            text-align: center;
            padding: 60px 20px;
        }

        .welcome-section h1 {
            font-size: 2.5rem;
            color: #1e293b;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .welcome-section p {
            font-size: 1.1rem;
            color: #64748b;
            max-width: 600px;
            margin: 0 auto 40px;
            line-height: 1.6;
        }

        .quick-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 40px;
        }

        .stat-box {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            padding: 25px;
            border-radius: 12px;
            text-align: center;
        }

        .stat-box i {
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .stat-box .number {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-box .label {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-280px);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .main-header {
                padding: 15px 20px;
            }

            .page-title {
                font-size: 1.4rem;
            }

            .main-body {
                padding: 20px;
            }

            .welcome-section h1 {
                font-size: 2rem;
            }

            .quick-stats {
                grid-template-columns: 1fr;
            }
        }

        /* Mobile overlay */
        .mobile-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        .notification-dropdown {
            position: absolute;
            right: 0;
            top: 45px;
            background: #fff;
            border: 1px solid #ddd;
            width: 300px;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            z-index: 999;
        }

        .hidden {
            display: none;
        }

        .notification-item {
            display: block;
            padding: 10px 15px;
            text-decoration: none;
            color: #333;
            border-bottom: 1px solid #eee;
        }

        .notification-item:hover {
            background-color: #f0f0f0;
        }

        .notification-empty {
            padding: 10px 15px;
            color: #666;
        }
        .notification-dot {
            position: absolute;
            top: 6px;
            right: 6px;
            height: 10px;
            width: 10px;
            background-color: red;
            border-radius: 50%;
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
                    <a href="{{route('manager.dashboard')}}" class="menu-item active">
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
                        <button class="header-btn" id="notificationToggle">
                            <i class="fas fa-bell"></i>
                            {{-- Chấm đỏ --}}
                            @if(session('new_booking'))
                                <span class="notification-dot"></span>
                            @endif
                        </button>
                        <div id="notificationDropdown" class="hidden" style="
                            position: absolute;
                            right: 0;
                            top: 40px;
                            background: white;
                            border: 1px solid #ddd;
                            width: 300px;
                            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
                            border-radius: 6px;
                            z-index: 999;
                            padding: 10px;
                        ">
                            <a href="/your-booking-link" class="notification-item">🛎️ Bạn có đơn đặt phòng mới!</a>
                        </div>
                        
                            <button class="header-btn">
                            <i class="fas fa-envelope"></i>
                        </button>
                        {{-- <button class="header-btn">
                            <i class="fas fa-search"></i>
                        </button> --}}
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
                <div class="content-card">
                    <div class="welcome-section">
                        <h1>Chào mừng quay trở lại với trang quản lí tại {{$hotel_name}}</h1>
                        <p>
                            Quản lý khách sạn của bạn một cách hiệu quả với các công cụ và thống kê chi tiết. 
                            Theo dõi tình trạng phòng, doanh thu, và trải nghiệm khách hàng tại một nơi.
                        </p>

                        <div class="quick-stats">
                            <div class="stat-box">
                            <i class="fas fa-bed"></i>
                            <div class="number">{{ $roomsInUse }}/{{ $totalRooms }}</div>
                            <div class="label">Phòng đang sử dụng</div>
                        </div>

                        <div class="stat-box">
                            <i class="fas fa-dollar-sign"></i>
                            <div class="number">{{ number_format($todayRevenue) }}₫</div>
                            <div class="label">Doanh thu hôm nay</div>
                        </div>

                        <div class="stat-box">
                            <i class="fas fa-users"></i>
                            <div class="number">{{ $checkinsToday }}</div>
                            <div class="label">Check-in hôm nay</div>
                        </div>

                        <div class="stat-box">
                            <i class="fas fa-star"></i>
                            <div class="number">{{ $averageRating }}</div>
                            <div class="label">Đánh giá trung bình</div>
                        </div>

                        </div>
                    </div>
                </div>

                <div class="content-card">
                    <h3 style="margin-bottom: 20px; color: #1e293b;">
                        <i class="fas fa-info-circle" style="margin-right: 10px; color: #3b82f6;"></i>
                        Nội dung chính sẽ được hiển thị ở đây
                    </h3>
                    <p style="color: #64748b; line-height: 1.6;">
                        Đây là khu vực main content. Bạn có thể thay đổi nội dung này tùy theo menu được chọn ở sidebar. 
                        Layout này responsive và tương thích với cả desktop và mobile.
                    </p>
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
    const toggle = document.getElementById('notificationToggle');
    const dropdown = document.getElementById('notificationDropdown');

    if (!toggle || !dropdown) {
        console.error("Không tìm thấy phần tử toggle hoặc dropdown!");
        return;
    }

    toggle.addEventListener('click', function (e) {
        e.stopPropagation();
        dropdown.classList.toggle('hidden');
    });

    document.addEventListener('click', function () {
        dropdown.classList.add('hidden');
    });

    dropdown.addEventListener('click', function (e) {
        e.stopPropagation();
    });
});
</script>
</body>
</html>

