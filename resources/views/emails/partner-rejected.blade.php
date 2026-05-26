<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông báo kết quả duyệt hồ sơ đối tác</title>
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
            background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);
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
        .reason-box {
            background-color: #fef2f2;
            border-left: 4px solid #ef4444;
            border-radius: 6px;
            padding: 20px;
            margin: 24px 0;
        }
        .reason-title {
            font-weight: 600;
            color: #991b1b;
            margin-bottom: 8px;
            font-size: 15px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .reason-content {
            color: #7f1d1d;
            font-size: 16px;
            margin: 0;
            font-style: italic;
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
                <p class="body-text">Cảm ơn bạn đã đăng ký tài khoản đối tác tại <strong>BKS System</strong> và nộp hồ sơ xét duyệt thông tin.</p>
                <p class="body-text">Sau khi tiến hành rà soát kỹ lưỡng hồ sơ và các giấy tờ đính kèm, chúng tôi rất tiếc phải thông báo rằng yêu cầu tham gia hệ thống của bạn chưa thể được phê duyệt vào lúc này.</p>
                
                <div class="reason-box">
                    <div class="reason-title">Lý do từ chối chi tiết:</div>
                    <p class="reason-content">{{ $rejection_reason }}</p>
                </div>
                
                <p class="body-text">Bạn hoàn toàn có thể cập nhật lại thông tin hồ sơ hoặc thay thế các giấy tờ liên quan bằng cách đăng nhập lại và thực hiện các bước chỉnh sửa trực tiếp trên giao diện của mình.</p>
                
                <div class="cta-container">
                    <a href="{{ config('app.url_frontend') }}/partner/login" class="btn">Đăng nhập để cập nhật hồ sơ</a>
                </div>
                
                <p class="body-text" style="margin-top: 24px;">Nếu cần thêm thông tin làm rõ hoặc hỗ trợ trong quá trình chuẩn bị lại hồ sơ, vui lòng liên hệ với bộ phận CSKH của BKS System.</p>
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
