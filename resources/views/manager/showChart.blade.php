<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Manager Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            padding: 30px;
            background: #f8fafc;
        }
    </style>
    <style>
    canvas {
        max-width: 400;
        height: auto;
        max-height: 300px;
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
                    <a href="{{route('manager.showChart')}}" class="menu-item active">
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
                <!-- Dashboard Content -->
                <div class="chart-container" style="padding: 20px; font-family: Arial, sans-serif;">
                    <!-- Header -->
                    

                    <!-- Biểu đồ doanh thu tháng -->
                    <div style="background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); padding: 20px; margin-bottom: 30px;">
                        <h3 style="color: #2c3e50; margin-bottom: 20px; text-align: center;">Biểu Đồ Doanh Thu Tháng</h3>
                        <canvas id="revenueChart" width="400" height="200"></canvas>
                    </div>

                    <!-- Stats Cards -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
                        <!-- Doanh thu hôm nay -->
                        <div style="background: rgb(47, 27, 43); color: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                            <div style="display: flex; align-items: center; justify-content: space-between;">
                                <div>
                                    <h4 style="font-size: 14px; opacity: 0.9; margin-bottom: 10px;">Doanh Thu Hôm Nay</h4>
                                    <p style="font-size: 24px; font-weight: bold;">{{ number_format($revenueToday) }} VNĐ</p>
                                </div>
                                <div style="font-size: 30px; opacity: 0.7;">💰</div>
                            </div>
                        </div>

                        <!-- Doanh thu tháng -->
                        <div style="background: rgb(47, 27, 43); color: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                            <div style="display: flex; align-items: center; justify-content: space-between;">
                                <div>
                                    <h4 style="font-size: 14px; opacity: 0.9; margin-bottom: 10px;">Doanh Thu Tháng Này</h4>
                                    <p style="font-size: 24px; font-weight: bold;">{{ number_format($revenueThisMonth) }} VNĐ</p>
                                </div>
                                <div style="font-size: 30px; opacity: 0.7;">📊</div>
                            </div>
                        </div>

                        <!-- Lượt đặt phòng hôm nay -->
                        <div style="background: rgb(47, 27, 43); color: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                            <div style="display: flex; align-items: center; justify-content: space-between;">
                                <div>
                                    <h4 style="font-size: 14px; opacity: 0.9; margin-bottom: 10px;">Đặt Phòng Hôm Nay</h4>
                                    <p style="font-size: 24px; font-weight: bold;">{{ $bookingCountToday }}</p>
                                </div>
                                <div style="font-size: 30px; opacity: 0.7;">🏨</div>
                            </div>
                        </div>

                        <!-- Lượt đặt phòng tháng -->
                        <div style="background: rgb(47, 27, 43); color: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                            <div style="display: flex; align-items: center; justify-content: space-between;">
                                <div>
                                    <h4 style="font-size: 14px; opacity: 0.9; margin-bottom: 10px;">Đặt Phòng Tháng Này</h4>
                                    <p style="font-size: 24px; font-weight: bold;">{{ $bookingCountThisMonth }}</p>
                                </div>
                                <div style="font-size: 30px; opacity: 0.7;">📅</div>
                            </div>
                        </div>

                        <!-- Phòng bị hủy -->
                        <div style="background: rgb(47, 27, 43); color: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                            <div style="display: flex; align-items: center; justify-content: space-between;">
                                <div>
                                    <h4 style="font-size: 14px; opacity: 0.9; margin-bottom: 10px;">Phòng Bị Hủy Tháng</h4>
                                    <p style="font-size: 24px; font-weight: bold;">{{ $cancelledCountThisMonth }}</p>
                                </div>
                                <div style="font-size: 30px; opacity: 0.7;">❌</div>
                            </div>
                        </div>

                        <!-- Khách hàng hôm nay -->
                        <div style="background: rgb(47, 27, 43); color: #e4e7e9; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                            <div style="display: flex; align-items: center; justify-content: space-between;">
                                <div>
                                    <h4 style="font-size: 14px; opacity: 0.8; margin-bottom: 10px;">Khách Hàng Hôm Nay</h4>
                                    <p style="font-size: 24px; font-weight: bold;">{{ $userCountToday }}</p>
                                </div>
                                <div style="font-size: 30px; opacity: 0.7;">👥</div>
                            </div>
                        </div>

                        <!-- Khách hàng tháng -->
                        <div style="background: rgb(47, 27, 43); color: #f2f7fb; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                            <div style="display: flex; align-items: center; justify-content: space-between;">
                                <div>
                                    <h4 style="font-size: 14px; opacity: 0.8; margin-bottom: 10px;">Khách Hàng Tháng Này</h4>
                                    <p style="font-size: 24px; font-weight: bold;">{{ $userCountThisMonth }}</p>
                                </div>
                                <div style="font-size: 30px; opacity: 0.7;">👨‍👩‍👧‍👦</div>
                            </div>
                        </div>

                        <!-- Đánh giá trung bình -->
                        <div style="background: rgb(47, 27, 43); color: #f3f7fc; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                            <div style="display: flex; align-items: center; justify-content: space-between;">
                                <div>
                                    <h4 style="font-size: 14px; opacity: 0.8; margin-bottom: 10px;">Đánh Giá Trung Bình</h4>
                                    <p style="font-size: 24px; font-weight: bold;">{{ number_format($avgRatingThisMonth, 1) }}/5 ⭐</p>
                                </div>
                                <div style="font-size: 30px; opacity: 0.7;">⭐</div>
                            </div>
                        </div>
                    </div>

                    <!-- Biểu đồ đường số phòng đặt theo ngày -->
                    <div style="background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); padding: 20px;">
                        <h3 style="color: #2c3e50; margin-bottom: 20px; text-align: center;">Biểu Đồ Lượt Đặt Phòng Theo Ngày</h3>
                        <canvas id="bookingChart" width="400" height="200"></canvas>
                    </div>
                </div>

                
            </div>
        </div>
    </div>
    <script>
                    // Dữ liệu từ PHP
                    const dailyRevenue = @json($dailyRevenue);
                    const dailyBookings = @json($dailyBookings);

                    // Tạo mảng ngày từ đầu tháng đến hiện tại
                    const currentDate = new Date();
                    const currentDay = currentDate.getDate();
                    const daysArray = Array.from({length: currentDay}, (_, i) => (i + 1).toString().padStart(2, '0'));

                    // Chuẩn bị dữ liệu doanh thu
                    const revenueData = daysArray.map(day => {
                        const found = dailyRevenue.find(item => item.day === day.replace(/^0/, ''));
                        return found ? found.total : 0;
                    });

                    // Chuẩn bị dữ liệu booking
                    const bookingData = daysArray.map(day => {
                        const found = dailyBookings.find(item => item.day === day.replace(/^0/, ''));
                        return found ? found.total : 0;
                    });

                    // Biểu đồ doanh thu (Bar Chart)
                    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
                    new Chart(revenueCtx, {
                        type: 'bar',
                        data: {
                            labels: daysArray.map(day => `${day}/${currentDate.getMonth() + 1}`),
                            datasets: [{
                                label: 'Doanh Thu (VNĐ)',
                                data: revenueData,
                                backgroundColor: 'rgba(54, 162, 235, 0.8)',
                                borderColor: 'rgba(54, 162, 235, 1)',
                                borderWidth: 1,
                                borderRadius: 4,
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'top'
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            return new Intl.NumberFormat('vi-VN').format(value) + ' VNĐ';
                                        }
                                    }
                                },
                                x: {
                                    title: {
                                        display: true,
                                        text: 'Ngày trong tháng'
                                    }
                                }
                            },
                            tooltips: {
                                callbacks: {
                                    label: function(context) {
                                        return 'Doanh thu: ' + new Intl.NumberFormat('vi-VN').format(context.parsed.y) + ' VNĐ';
                                    }
                                }
                            }
                        }
                    });

                    // Biểu đồ đường số phòng đặt (Line Chart)
                    const bookingCtx = document.getElementById('bookingChart').getContext('2d');
                    new Chart(bookingCtx, {
                        type: 'line',
                        data: {
                            labels: daysArray.map(day => `${day}/${currentDate.getMonth() + 1}`),
                            datasets: [{
                                label: 'Số Phòng Đặt',
                                data: bookingData,
                                borderColor: 'rgba(255, 99, 132, 1)',
                                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                                borderWidth: 2,
                                fill: true,
                                tension: 0.4,
                                pointBackgroundColor: 'rgba(255, 99, 132, 1)',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                pointRadius: 5,
                                pointHoverRadius: 7
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'top'
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1
                                    }
                                },
                                x: {
                                    title: {
                                        display: true,
                                        text: 'Ngày trong tháng'
                                    }
                                }
                            },
                            tooltips: {
                                callbacks: {
                                    label: function(context) {
                                        return 'Số phòng đặt: ' + context.parsed.y;
                                    }
                                }
                            }
                        }
                    });
                </script>
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

    
</body>
</html>

