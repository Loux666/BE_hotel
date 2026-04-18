<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Beautiful Footer</title>
    <style>
        

        xbody {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #fafafa 0%, #fcfcfc 100%);
            display: flex;
            flex-direction: column;
        }

        /* Demo content */
        
        /* Footer styles */
        .footer {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: #ecf0f1;
            padding: 3rem 0 1.5rem;
            margin-top: auto;
            position: relative;
            overflow: hidden;
        }

        .footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, #3498db, transparent);
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .footer-section h3 {
            color: #3498db;
            margin-bottom: 1rem;
            font-size: 1.1rem;
            font-weight: 600;
            position: relative;
        }

        .footer-section h3::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 30px;
            height: 2px;
            background: #3498db;
            border-radius: 1px;
        }

        .footer-section p {
            line-height: 1.6;
            color: #bdc3c7;
            margin-bottom: 1rem;
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 0.5rem;
        }

        .footer-links a {
            color: #bdc3c7;
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
        }

        .footer-links a:hover {
            color: #3498db;
            padding-left: 5px;
        }

        .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .social-links a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: rgba(52, 152, 219, 0.1);
            border: 1px solid rgba(52, 152, 219, 0.3);
            border-radius: 50%;
            color: #3498db;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .social-links a:hover {
            background: #3498db;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
        }

        .footer-bottom {
            border-top: 1px solid rgba(189, 195, 199, 0.1);
            padding-top: 1.5rem;
            text-align: center;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .footer-bottom p {
            color: #95a5a6;
            font-size: 0.9rem;
        }

        .footer-nav {
            display: flex;
            justify-content: center;
            gap: 2rem;
            flex-wrap: wrap;
        }

        .footer-nav a {
            color: #bdc3c7;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }

        .footer-nav a:hover {
            color: #3498db;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .footer-content {
                grid-template-columns: 1fr;
                text-align: center;
            }
            
            .footer-nav {
                flex-direction: column;
                gap: 1rem;
            }
            
            .social-links {
                justify-content: center;
            }
            
            .content h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<xbody>
    

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Về Chúng Tôi</h3>
                    <p>Chúng tôi là công ty hàng đầu trong lĩnh vực công nghệ, mang đến những giải pháp sáng tạo và hiệu quả nhất cho khách hàng.</p>
                    <div class="social-links">
                        <a href="#" aria-label="Facebook">📘</a>
                        <a href="#" aria-label="Twitter">🐦</a>
                        <a href="#" aria-label="Instagram">📷</a>
                        <a href="#" aria-label="LinkedIn">💼</a>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h3>Dịch Vụ</h3>
                    <ul class="footer-links">
                        <li><a href="#">Thiết kế website</a></li>
                        <li><a href="#">Phát triển ứng dụng</a></li>
                        <li><a href="#">Digital Marketing</a></li>
                        <li><a href="#">SEO & SEM</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Liên Hệ</h3>
                    <p>📍 123 Đường ABC, Quận XYZ, Hà Nội</p>
                    <p>📞 0123 456 789</p>
                    <p>✉️ contact@example.com</p>
                </div>
            </div>
            
            <div class="footer-bottom">
                <nav class="footer-nav">
                    <a href="#">Chính sách bảo mật</a>
                    <a href="#">Điều khoản sử dụng</a>
                    <a href="#">Sitemap</a>
                    <a href="#">FAQ</a>
                </nav>
                <p>&copy; 2025 Your Company. All rights reserved. Made with ❤️ in Vietnam</p>
            </div>
        </div>
    </footer>
</xbody>
</html>