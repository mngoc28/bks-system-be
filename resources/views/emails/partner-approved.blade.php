<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ sơ đối tác đã được phê duyệt</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #1f2937;
            margin: 0;
            padding: 0;
            background-color: #f3f4f6;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            padding: 20px;
        }
        .card {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.02);
            overflow: hidden;
            border: 1px solid #e5e7eb;
        }
        .header {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: #ffffff;
            padding: 32px 24px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: -0.025em;
        }
        .content {
            padding: 32px 24px;
        }
        .greeting {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 16px;
            color: #111827;
        }
        .body-text {
            color: #4b5563;
            margin-bottom: 24px;
            font-size: 16px;
        }
        .cta-container {
            text-align: center;
            margin: 32px 0;
        }
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.3);
            transition: all 0.2s ease;
        }
        .footer {
            text-align: center;
            padding: 24px;
            background-color: #f9fafb;
            border-top: 1px solid #f3f4f6;
            color: #9ca3af;
            font-size: 14px;
        }
        .footer p {
            margin: 4px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="header">
                <h1>BKS SYSTEM</h1>
            </div>
            <div class="content">
                <p class="greeting">Xin chào {{ $name }},</p>
                <p class="body-text">Chúng tôi rất vui mừng thông báo rằng hồ sơ đăng ký đối tác của bạn tại <strong>BKS System</strong> đã được phê duyệt thành công!</p>
                <p class="body-text">Tài khoản của bạn hiện đã được kích hoạt. Bây giờ bạn có thể đăng nhập vào hệ thống dành cho đối tác (Partner Portal) để bắt đầu đăng tải các căn hộ/phòng nghỉ của mình và tiếp cận hàng ngàn khách hàng tiềm năng.</p>
                
                <div class="cta-container">
                    <a href="{{ config('app.url_frontend') }}/partner/login" class="btn">Đăng nhập Partner Portal</a>
                </div>
                
                <p class="body-text" style="margin-top: 24px;">Nếu có bất kỳ thắc mắc nào trong quá trình vận hành, vui lòng liên hệ với đội ngũ hỗ trợ của chúng tôi.</p>
                <p class="body-text">Trân trọng,<br><strong>Đội ngũ BKS System</strong></p>
            </div>
            <div class="footer">
                <p>Email này được gửi tự động từ hệ thống BKS System, vui lòng không trả lời.</p>
                <p>&copy; {{ date('Y') }} BKS System. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
