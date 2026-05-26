<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông báo hủy đặt phòng - BKS System</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #ef4444;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
            font-weight: 600;
        }
        .header p {
            margin: 8px 0 0 0;
            font-size: 14px;
            opacity: 0.9;
        }
        .content {
            background-color: #f9fafb;
            padding: 24px;
            border: 1px solid #e5e7eb;
            border-top: none;
        }
        .greeting {
            margin-bottom: 20px;
        }
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            color: #374151;
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e5e7eb;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            border-radius: 6px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
        }
        .info-table tr {
            border-bottom: 1px solid #f3f4f6;
        }
        .info-table tr:last-child {
            border-bottom: none;
        }
        .info-table td {
            padding: 11px 14px;
            vertical-align: top;
        }
        .info-table td:first-child {
            font-weight: 600;
            color: #6b7280;
            width: 38%;
            font-size: 13px;
        }
        .info-table td:last-child {
            color: #1f2937;
            font-size: 14px;
        }
        .reason-box {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            border-left: 4px solid #ef4444;
            padding: 16px;
            margin: 20px 0;
            border-radius: 0 6px 6px 0;
        }
        .reason-box .reason-label {
            font-size: 13px;
            font-weight: 700;
            color: #991b1b;
            margin-bottom: 6px;
        }
        .reason-box .reason-text {
            font-size: 14px;
            color: #7f1d1d;
            line-height: 1.7;
            font-style: italic;
        }
        .info-box {
            background-color: #eff6ff;
            border: 1px solid #bfdbfe;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
        }
        .info-box a {
            color: #2563eb;
            text-decoration: underline;
        }
        .sorry-box {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            padding: 15px 18px;
            border-radius: 6px;
            margin: 18px 0;
        }
        .sorry-box p {
            margin: 0;
            color: #166534;
            font-size: 14px;
            line-height: 1.7;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            padding: 20px;
            font-size: 12px;
            color: #6b7280;
            border-radius: 0 0 5px 5px;
        }
        @media screen and (max-width: 600px) {
            .container { padding: 10px !important; }
            .header, .content { padding: 15px !important; }
            .info-table td { display: block; width: 100%; padding: 8px 0; }
            .info-table td:first-child { width: 100%; padding-bottom: 2px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>⚠ Thông báo hủy đặt phòng</h1>
            <p>BKS System – Hệ thống đặt phòng trực tuyến</p>
        </div>

        <!-- Content -->
        <div class="content">
            <!-- Greeting -->
            <div class="greeting">
                <p>Kính gửi: <strong>{{ $name }}</strong>,</p>
                <p>
                    Chúng tôi xin gửi thông báo rằng đơn đặt phòng
                    <strong>#{{ $data['booking_code'] }}</strong> của quý khách
                    <strong style="color: #ef4444;">đã bị hủy</strong>.
                </p>
            </div>

            <!-- Booking Info -->
            <div class="section">
                <div class="section-title">Thông tin đặt phòng</div>
                <table class="info-table">
                    <tr>
                        <td>Mã đặt phòng</td>
                        <td><strong>{{ $data['booking_code'] }}</strong></td>
                    </tr>
                    <tr>
                        <td>Thời gian đặt phòng</td>
                        <td style="color: #1d4ed8; font-weight: 600;">{{ $data['booking_created_at'] }}</td>
                    </tr>
                    <tr>
                        <td>Tên cơ sở</td>
                        <td><strong>{{ $data['property_name'] }}</strong></td>
                    </tr>
                    <tr>
                        <td>Phòng</td>
                        <td><strong>{{ $data['room_title'] }}</strong></td>
                    </tr>
                    @if(!empty($data['property_address']))
                    <tr>
                        <td>Địa chỉ</td>
                        <td>{{ $data['property_address'] }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td>Ngày nhận phòng</td>
                        <td>{{ $data['start_date'] }}</td>
                    </tr>
                    <tr>
                        <td>Ngày trả phòng</td>
                        <td>{{ $data['end_date'] }}</td>
                    </tr>
                    <tr>
                        <td>Thời điểm hủy</td>
                        <td>{{ $data['cancelled_at'] }}</td>
                    </tr>
                </table>
            </div>

            <!-- Cancellation Reason -->
            <div class="reason-box">
                <div class="reason-label">📋 Lý do hủy đặt phòng</div>
                <div class="reason-text">{{ $data['cancellation_reason'] }}</div>
            </div>

            <!-- Support / Next Steps -->
            <div class="section">
                <div class="section-title">Hỗ trợ & Bước tiếp theo</div>
                <div class="info-box">
                    <p style="margin: 0 0 10px 0;">Nếu bạn muốn tìm phòng thay thế hoặc cần thêm thông tin, vui lòng:</p>
                    <ul style="margin: 0; padding-left: 20px; color: #1e40af;">
                        <li style="margin-bottom: 6px;">
                            Xem lại lịch sử đặt phòng tại:
                            <a href="{{ $data['bookings_url'] }}">{{ $data['bookings_url'] }}</a>
                        </li>
                        <li>
                            Xem chi tiết phòng tại:
                            <a href="{{ $data['room_url'] }}">{{ $data['room_url'] }}</a>
                        </li>
                    </ul>
                </div>

                <p style="color: #374151; font-size: 14px; margin-top: 14px;">
                    <strong>Tổng đài hỗ trợ:</strong> {{ $data['goline_phone'] }}<br>
                    <strong>Email:</strong> <a href="mailto:support@bks.co.jp" style="color: #2563eb;">support@bks.co.jp</a>
                </p>
            </div>

            <!-- Apology note -->
            <div class="sorry-box">
                <p>
                    Chúng tôi thành thật xin lỗi quý khách vì sự bất tiện này.
                    BKS System luôn nỗ lực mang đến trải nghiệm đặt phòng tốt nhất
                    và rất mong được phục vụ quý khách trong những lần tới.
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Email này được gửi tự động từ hệ thống BKS System.</p>
            <p>Nếu bạn có bất kỳ câu hỏi nào, vui lòng liên hệ: {{ $data['goline_phone'] }}</p>
            <p style="margin-top: 10px;">&copy; {{ date('Y') }} BKS System. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
