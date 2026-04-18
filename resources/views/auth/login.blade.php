<x-guest-layout>
    <div class="login-container">
        <div class="login-card">
            <!-- Logo và Title -->
            <div class="login-header">
                <div class="logo-container">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo" class="logo-img">
                </div>
                <h2 class="login-title">Chào mừng trở lại</h2>
                <p class="login-subtitle">Đăng nhập để tiếp tục trải nghiệm</p>
            </div>

            <!-- Validation Errors -->
            <x-validation-errors class="mb-4" />

            <!-- Success Message -->
            @session('status')
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    {{ $value }}
                </div>
            @endsession

            <form method="POST" action="{{ route('login') }}" class="login-form">
                @csrf

                <!-- Email -->
                <div class="form-group">
                    <label for="email" class="form-label">
                        <i class="fas fa-envelope"></i>
                        Email
                    </label>
                    <input id="email" 
                           class="form-input" 
                           type="email" 
                           name="email" 
                           value="{{ old('email') }}" 
                           required 
                           autofocus 
                           autocomplete="username"
                           placeholder="Nhập email của bạn">
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock"></i>
                        Mật khẩu
                    </label>
                    <div class="password-container">
                        <input id="password" 
                               class="form-input password-input" 
                               type="password" 
                               name="password" 
                               required 
                               autocomplete="current-password"
                               placeholder="Nhập mật khẩu">
                        <button type="button" class="password-toggle" onclick="togglePassword()">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="form-group">
                    <div class="checkbox-container">
                        <input type="checkbox" id="remember_me" name="remember" class="custom-checkbox">
                        <label for="remember_me" class="checkbox-label">
                            <i class="fas fa-heart"></i>
                            Ghi nhớ đăng nhập
                        </label>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="form-group">
                    <button type="submit" class="login-btn">
                        <i class="fas fa-sign-in-alt"></i>
                        Đăng nhập
                    </button>
                </div>

                <!-- Links -->
                <div class="form-links">
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="forgot-link">
                            <i class="fas fa-key"></i>
                            Quên mật khẩu?
                        </a>
                    @endif
                </div>

                <!-- Register Link -->
                <div class="register-link">
                    <span>Chưa có tài khoản? </span>
                    <a href="{{ route('register') }}" class="link-primary">Đăng ký ngay</a>
                </div>
            </form>

            
            
        </div>
    </div>

    <style>
        .login-container {
            min-height: 100vh;
            
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
        }

        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><radialGradient id="a"><stop offset="0" stop-color="%23ffffff" stop-opacity="0.1"/><stop offset="1" stop-color="%23ffffff" stop-opacity="0"/></radialGradient></defs><circle cx="50" cy="50" r="50" fill="url(%23a)"/></svg>') center/cover;
            opacity: 0.1;
        }

        .login-card {
            background: white;
            border-radius: 24px;
            box-shadow: 0 30px 60px rgba(0,0,0,0.12);
            padding: 40px;
            width: 100%;
            max-width: 420px;
            position: relative;
            z-index: 1;
            backdrop-filter: blur(10px);
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #667eea, #764ba2);
            border-radius: 24px 24px 0 0;
        }

        .login-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .logo-container {
            margin-bottom: 20px;
        }

        .logo-img {
            width: 100px;
            height: auto;
        }

        .login-title {
            font-size: 32px;
            font-weight: 700;
            color: #2d3748;
            margin: 0 0 8px 0;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .login-subtitle {
            color: #718096;
            font-size: 16px;
            margin: 0;
        }

        .success-message {
            background: linear-gradient(135deg, #48bb78, #38a169);
            color: white;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 500;
        }

        .login-form {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-label {
            font-weight: 600;
            color: #4a5568;
            margin-bottom: 8px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-label i {
            color: #667eea;
            width: 16px;
        }

        .form-input {
            padding: 16px 20px;
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #f8fafc;
            width: 100%;
        }

        .form-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
            background: white;
            transform: translateY(-1px);
        }

        .form-input::placeholder {
            color: #a0aec0;
        }

        .password-container {
            position: relative;
        }

        .password-input {
            padding-right: 50px;
        }

        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #718096;
            cursor: pointer;
            font-size: 16px;
            transition: color 0.3s ease;
        }

        .password-toggle:hover {
            color: #667eea;
        }

        .checkbox-container {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .custom-checkbox {
            width: 18px;
            height: 18px;
            accent-color: #667eea;
        }

        .checkbox-label {
            font-size: 14px;
            color: #4a5568;
            margin-bottom: 0;
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
        }

        .checkbox-label i {
            color: #e53e3e;
            font-size: 12px;
        }

        .login-btn {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 16px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            position: relative;
            overflow: hidden;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px rgba(102, 126, 234, 0.4);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        .login-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .login-btn:hover::before {
            left: 100%;
        }

        .form-links {
            text-align: center;
            margin-top: 16px;
        }

        .forgot-link {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.3s ease;
        }

        .forgot-link:hover {
            color: #5a67d8;
            transform: translateY(-1px);
            text-decoration: underline;
        }

        .register-link {
            text-align: center;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid #e2e8f0;
            color: #718096;
        }

        .link-primary {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .link-primary:hover {
            color: #5a67d8;
            text-decoration: underline;
        }

        .social-login {
            margin-top: 32px;
        }

        .divider {
            text-align: center;
            margin: 24px 0;
            position: relative;
            color: #718096;
            font-size: 14px;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e2e8f0;
            z-index: 1;
        }

        .divider span {
            background: white;
            padding: 0 16px;
            position: relative;
            z-index: 2;
        }

        .social-buttons {
            display: flex;
            gap: 12px;
        }

        .social-btn {
            flex: 1;
            padding: 12px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-weight: 500;
            font-size: 14px;
        }

        .google-btn {
            color: #ea4335;
        }

        .google-btn:hover {
            border-color: #ea4335;
            background: #fef2f2;
        }

        .facebook-btn {
            color: #1877f2;
        }

        .facebook-btn:hover {
            border-color: #1877f2;
            background: #eff6ff;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .login-card {
                padding: 30px 24px;
                margin: 10px;
            }

            .login-title {
                font-size: 28px;
            }

            .social-buttons {
                flex-direction: column;
            }
        }

        /* Animation */
        .login-card {
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Auto-focus animation
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('.form-input');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.classList.add('focused');
                });
                input.addEventListener('blur', function() {
                    this.parentElement.classList.remove('focused');
                });
            });
        });
    </script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</x-guest-layout>