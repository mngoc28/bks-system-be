<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chào mừng bạn đến với BKS Stay!</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #1e293b;
            margin: 0;
            padding: 0;
            background-color: #f8fafc;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            border: 1px solid #e2e8f0;
        }
        .header {
            background-color: #0f172a;
            color: #ffffff;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 800;
            letter-spacing: 1px;
        }
        .header span {
            color: #fbbf24;
        }
        .content {
            padding: 30px;
        }
        .welcome-title {
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
            margin-top: 0;
            margin-bottom: 15px;
        }
        .coupon-container {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            border: 1px dashed #22c55e;
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
            text-align: center;
        }
        .coupon-label {
            font-size: 13px;
            color: #166534;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }
        .coupon-code {
            display: inline-block;
            font-family: 'Courier New', Courier, monospace;
            font-size: 28px;
            font-weight: 800;
            color: #b45309;
            background-color: #fef3c7;
            border: 1px solid #fde047;
            padding: 8px 24px;
            border-radius: 6px;
            margin: 10px 0;
            letter-spacing: 2px;
        }
        .coupon-value {
            font-size: 14px;
            color: #14532d;
            font-weight: 500;
            margin-top: 5px;
        }
        .button {
            display: inline-block;
            background-color: #0284c7;
            color: #ffffff !important;
            text-decoration: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 15px;
            margin: 15px 0;
            box-shadow: 0 4px 6px -1px rgba(2, 132, 199, 0.2);
            transition: background-color 0.2s;
        }
        .footer {
            background-color: #f1f5f9;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
        }
        .footer p {
            margin: 5px 0;
        }
        .footer a {
            color: #0284c7;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>BKS <span>Stay</span></h1>
        </div>

        <div class="content">
            <h2 class="welcome-title">Chào mừng bạn đến với Bản tin BKS Stay!</h2>
            <p>Xin chào,</p>
            <p>Cảm ơn bạn đã đăng ký nhận bản tin của chúng tôi. Kể từ hôm nay, bạn sẽ là một trong những thành viên đầu tiên nhận được thông tin về các chương trình ưu đãi đặt phòng tốt nhất, các căn homestay cực đẹp và mã giảm giá độc quyền từ BKS Stay.</p>
            
            <p>Để chào mừng bạn, chúng tôi gửi tặng bạn mã giảm giá đặc quyền dành riêng cho thành viên mới:</p>

            <div class="coupon-container">
                <div class="coupon-label">Mã Giảm Giá Của Bạn</div>
                <div class="coupon-code">{{ $code }}</div>
                <div class="coupon-value">
                    Ưu đãi giảm {{ $value }}{{ $type === 'percent' ? '%' : 'đ' }} cho đơn đặt phòng đầu tiên của bạn.
                </div>
            </div>

            <p>Hãy nhanh tay sử dụng mã giảm giá này khi tiến hành đặt phòng trên website của chúng tôi nhé!</p>

            <div style="text-align: center; margin: 30px 0 20px 0;">
                <a href="{{ config('app.url_frontend') }}/search/rooms" class="button">Khám Phá Phòng Ngay</a>
            </div>

            <p>Chúc bạn có những chuyến đi thật ý nghĩa và những kỳ nghỉ tuyệt vời cùng BKS Stay!</p>
            <p>Trân trọng,<br>Đội ngũ BKS Stay</p>
        </div>

        <div class="footer">
            <p>Email này được gửi tự động từ hệ thống BKS Stay. Vui lòng không trả lời trực tiếp email này.</p>
            <p>&copy; {{ date('Y') }} BKS Stay. Bảo lưu mọi quyền.</p>
        </div>
    </div>
</body>
</html>
