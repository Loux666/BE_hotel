
<!-- Payment Failed Notification Component -->
<div class="pf-overlay">
    <div class="pf-container">
        <div class="pf-error-icon">
            <svg class="pf-icon" viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
            </svg>
        </div>
        
        <h1 class="pf-title">Thanh toán thất bại</h1>
        
        <p class="pf-message">
            Rất tiếc, giao dịch của bạn không thể hoàn thành. Vui lòng kiểm tra lại thông tin thanh toán hoặc thử lại sau.
        </p>
        
        
        
        <button class="pf-close-button" onclick="closePaymentWindow()">
            Đóng cửa sổ
        </button>
    </div>
</div>

<style>
    .pf-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, #f6f8ff 0%, #fdfbff 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        z-index: 9999;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .pf-container {
        background: white;
        padding: 40px;
        border-radius: 20px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        text-align: center;
        max-width: 450px;
        width: 100%;
        position: relative;
        overflow: hidden;
        animation: pf-fadeIn 0.5s ease;
    }

    .pf-container::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: linear-gradient(90deg, #ff6b6b, #ee5a24);
    }

    .pf-error-icon {
        width: 80px;
        height: 80px;
        margin: 0 auto 20px;
        background: linear-gradient(135deg, #ff6b6b, #ee5a24);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        animation: pf-pulse 2s infinite;
    }

    .pf-icon {
        width: 40px;
        height: 40px;
        fill: white;
    }

    @keyframes pf-pulse {
        0% {
            transform: scale(1);
            box-shadow: 0 0 0 0 rgba(255, 107, 107, 0.7);
        }
        70% {
            transform: scale(1.05);
            box-shadow: 0 0 0 10px rgba(255, 107, 107, 0);
        }
        100% {
            transform: scale(1);
            box-shadow: 0 0 0 0 rgba(255, 107, 107, 0);
        }
    }

    @keyframes pf-fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .pf-title {
        font-size: 28px;
        color: #2c3e50;
        margin-bottom: 15px;
        font-weight: 700;
    }

    .pf-message {
        font-size: 16px;
        color: #7f8c8d;
        line-height: 1.6;
        margin-bottom: 30px;
    }

    .pf-details {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 30px;
        border-left: 4px solid #ff6b6b;
    }

    .pf-details-title {
        color: #2c3e50;
        margin-bottom: 10px;
        font-size: 18px;
    }

    .pf-details-content {
        color: #7f8c8d;
        font-size: 14px;
        line-height: 1.5;
        margin: 0;
    }

    .pf-close-button {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        border: none;
        padding: 15px 40px;
        font-size: 16px;
        font-weight: 600;
        border-radius: 50px;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    }

    .pf-close-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
    }

    .pf-close-button:active {
        transform: translateY(0);
    }

    @media (max-width: 480px) {
        .pf-container {
            padding: 30px 20px;
        }
        
        .pf-title {
            font-size: 24px;
        }
        
        .pf-message {
            font-size: 14px;
        }
    }
</style>

<script>
    function closePaymentWindow() {
        // Thử đóng cửa sổ hiện tại
        window.close();
        
        // Nếu không thể đóng (do một số trình duyệt chặn), 
        // thì có thể redirect về trang trước đó
        setTimeout(() => {
            if (!window.closed) {
                if (window.history.length > 1) {
                    window.history.back();
                } else {
                    // Nếu không có history, có thể redirect về trang chủ
                    window.location.href = '/';
                }
            }
        }, 100);
    }
</script>