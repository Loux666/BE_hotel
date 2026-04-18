<base href="/public">
@include('home.css')

<div class="header">
    <div class="container">
        <div class="row">
            <div class="col-xl-3 col-lg-3 col-md-3 col-sm-3 col logo_section">
                <div class="full">
                    <div class="center-desk">
                        <div class="logo">
                            <a href="."><img src="images/logo.png" alt="#" /></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-9 col-lg-9 col-md-9 col-sm-9">
                <nav class="navigation navbar navbar-expand-md navbar-dark  " style="display: flex; justify-content: center;">
                    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExample04" aria-controls="navbarsExample04" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarsExample04">
                        <ul class="navbar-nav mr-auto">
                            <li class="nav-item ">
                                <a class="nav-link" style="font-size: 14px " href="/">Trang chủ</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" style="font-size: 14px" href="{{url('about')}}">Mô tả</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" style="font-size: 14px" href="{{url('room')}}">Tìm phòng</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" style="font-size: 14px" href="contact.html">Liên hệ ngay</a>
                            </li>



                            @if (Route::has('login'))
                            @auth
                            <li class="nav-item dropdown" style="padding-right: 10px;">
                                <div class="dropdown">
                                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        {{ Auth::user()->name }}
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                        <li><a class="dropdown-item" href="{{ route('profile.show') }}">Thông tin tài khoản</a></li>
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li>
                                            <form method="POST" action="{{ route('logout') }}">
                                                @csrf
                                                <button type="submit" class="dropdown-item">Đăng xuất</button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            @else
                            <li class="nav-item" style="padding-right: 10px;">
                                <a class="btn btn-primary" style="font-size: 14px;" href="{{ url('login') }}">Đăng nhập</a>
                            </li>
                            @if (Route::has('register'))
                            <li class="nav-item">
                                <a class="btn btn-success" style="font-size: 14px;" href="{{ url('register') }}">Đăng kí</a>
                            </li>
                            @endif
                            @endauth
                            @endif



                            <li class="nav-item" style="margin-top:5px; padding-left:20px">
                                <a href="{{url('cart')}}">
                                    <img src="images/cart_logo.png" width="20px" height="20px">
                                </a>
                            </li>
                        </ul>
                    </div>
                </nav>
            </div>
        </div>
    </div>
</div>
<!-- Thông báo hết phòng -->
<div class="room-sold-out">
    <div class="sold-out-content">
        <div class="sold-out-icon">😔</div>
        <h2 class="sold-out-title">Phòng này đã hết</h2>
        <p class="sold-out-text">
            Rất tiếc, loại phòng này hiện tại đã được đặt hết.<br>
            Vui lòng chọn loại phòng khác hoặc thay đổi ngày đặt.
        </p>
        
        <div class="sold-out-actions">
            
            <button class="btn-secondary" onclick="window.close()">Quay lại</button>
        </div>
    </div>
</div>

<style>
    .room-sold-out {
        background: #f8f9fa;
        border: 2px solid #e74c3c;
        border-radius: 12px;
        padding: 30px;
        text-align: center;
        margin: 20px 0;
    }

    .sold-out-content {
        max-width: 400px;
        margin: 0 auto;
    }

    .sold-out-icon {
        font-size: 48px;
        margin-bottom: 15px;
    }

    .sold-out-title {
        color: #e74c3c;
        font-size: 24px;
        margin-bottom: 15px;
        font-weight: 600;
    }

    .sold-out-text {
        color: #6c757d;
        font-size: 16px;
        line-height: 1.5;
        margin-bottom: 25px;
    }

    .sold-out-actions {
        display: flex;
        gap: 15px;
        justify-content: center;
        flex-wrap: wrap;
    }

    .btn-primary {
        background: #007bff;
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 6px;
        font-size: 14px;
        cursor: pointer;
        transition: background 0.3s;
    }

    .btn-primary:hover {
        background: #0056b3;
    }

    .btn-secondary {
        background: #6c757d;
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 6px;
        font-size: 14px;
        cursor: pointer;
        transition: background 0.3s;
    }

    .btn-secondary:hover {
        background: #545b62;
    }

    @media (max-width: 480px) {
        .sold-out-actions {
            flex-direction: column;
        }
        
        .btn-primary, .btn-secondary {
            width: 100%;
        }
}