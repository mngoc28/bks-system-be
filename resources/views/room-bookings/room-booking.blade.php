<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo giá và thủ tục đăng ký - BKS System</title>
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
            background-color: #3B82F6;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .header p {
            margin: 10px 0 0 0;
            font-size: 14px;
        }
        .content {
            background-color: #f9fafb;
            padding: 20px;
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
            color: #3B82F6;
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #3B82F6;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
        }
        .info-table tr {
            border-bottom: 1px solid #e5e7eb;
        }
        .info-table tr:last-child {
            border-bottom: none;
        }
        .info-table td {
            padding: 12px;
            vertical-align: top;
        }
        .info-table td:first-child {
            font-weight: 600;
            color: #4b5563;
            width: 40%;
        }
        .info-table td:last-child {
            color: #1f2937;
        }
        .highlight-box {
            background-color: white;
            border: 1px solid #3B82F6;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
        }
        .highlight-box a {
            color: #3B82F6;
            text-decoration: none;
            font-weight: 600;
        }
        .total-box {
            background-color: white;
            border: 2px solid #3B82F6;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
            margin: 20px 0;
        }
        .total-box .label {
            font-size: 14px;
            color: #4b5563;
            margin-bottom: 8px;
        }
        .total-box .amount {
            font-size: 28px;
            font-weight: 700;
            color: #3B82F6;
        }
        .list-items {
            background-color: white;
            padding: 15px;
            border-radius: 5px;
        }
        .list-item {
            padding: 10px 0;
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid #e5e7eb;
        }
        .list-item:last-child {
            border-bottom: none;
        }
        .list-item .item-name {
            color: #4b5563;
        }
        .list-item .item-price {
            font-weight: 600;
            color: #1f2937;
        }
        .info-box {
            background-color: #eff6ff;
            border: 1px solid #3B82F6;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
        }
        .button {
            display: inline-block;
            background-color: #3B82F6;
            color: white;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 5px;
            font-weight: 600;
            margin: 10px 5px;
            text-align: center;
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
            .container {
                padding: 10px !important;
            }
            .header, .content {
                padding: 15px !important;
            }
            .button {
                padding: 10px 15px !important;
            }
            .info-table td {
                display: block;
                width: 100%;
                padding: 8px 0;
            }
            .info-table td:first-child {
                width: 100%;
                padding-bottom: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>Welcome to BKS</h1>
            <p>Báo giá và thủ tục đăng ký đặt phòng</p>
        </div>

        <!-- Content -->
        <div class="content">
            <!-- Greeting -->
            <div class="greeting">
                <p>Kính gửi: <strong>{{ $name }}</strong>,</p>
                <p>Căn hộ/Phòng mà bạn yêu cầu thông tin qua
                <strong>"BKS System"</strong>
                hiện trong tình trạng
                <strong style="color: #10b981;">sẵn sàng</strong> và có thể đặt.</p>
            </div>

            <!-- Room Info -->
            <div class="highlight-box">
                <strong>{{ $data['room_title'] }} - {{ $data['building_name'] }}</strong><br>
                Địa chỉ: {{ $data['building_address'] }}<br>
                Mã đặt phòng: <strong>{{ $data['booking_code'] }}</strong><br>
                Từ ngày: <strong>{{ $data['start_time'] }}</strong><br>
                Đến ngày: <strong>{{ $data['end_time'] }}</strong><br>
                Xem chi tiết phòng tại:
                <a href="{{ $data['room_url'] }}"
                    style="text-decoration: underline;">{{ $data['room_url'] }}
                </a>
            </div>

            <!-- Registration Links -->
            <div class="section">
                <div class="section-title">Đăng ký thông tin</div>
                
                <div style="background-color: #f9fafb; padding: 20px; border-radius: 8px; margin: 15px 0;">
                    <p style="margin: 0 0 15px 0; color: #4b5563;">
                        Xem các đơn đặt phòng của bạn tại URL bên dưới.
                    </p>
                    <a href="{{ $data['bookings_url'] }}"
                        >{{ $data['bookings_url'] }}
                    </a>
                
                    @if(!empty($data['is_first_time']))
                    <br>
                    <p style="margin: 15px 0 5px 0;">
                        Nếu bạn chưa thiết lập mật khẩu, vui lòng thiết lập mật khẩu tại URL bên dưới.
                    </p>
                    <a href="{{ config('app.url_frontend') }}/set-password/{{ $data['token'] }}">
                        {{ config('app.url_frontend') }}/set-password/{{ $data['token'] }}
                    </a>
                    @else
                    <br>
                    <p style="margin: 15px 0 5px 0;">Tài khoản của bạn đã tồn tại, vui lòng đăng nhập hệ thống để quản lý lịch sử đặt phòng:</p>
                    <a href="{{ config('app.url_frontend') }}/login">
                        {{ config('app.url_frontend') }}/login
                    </a>
                    @endif
                </div>

                <div class="info-box" style="margin-top: 20px;">
                    <strong>Lưu ý:</strong>
                    Việc đặt chỗ được xem xét trên cơ sở
                    <strong>ai đăng ký trước được phục vụ trước</strong>
                    , vì vậy tùy thuộc vào thời gian đăng ký của bạn, chúng tôi có thể đã kín chỗ.
                    Ngoài ra, số tiền cuối cùng có thể thay đổi tùy theo thời gian trong năm, v.v.
                </div>

            <!-- Pricing Details -->
            <div class="section">
                <div class="section-title">Tổng giá trị ước tính</div>
                
                @php
                    $total_service = 0;
                    if (!empty($data['services'])) {
                        foreach ($data['services'] as $item) {
                            $total_service += (float) ($item['amount'] ?? 0);
                        }
                    }
                    $total = $data['total_amount'] + $total_service;
                @endphp
                
                <div class="list-items">
                    <div class="list-item" style="justify-content: space-between;">
                        <span class="item-name">Phí thuê phòng ({{ $data['total_days'] }} ngày)</span>
                        <span class="item-price" style="margin-left: auto; text-align: right; min-width: 120px; display: inline-block;">
                            {{ number_format($data['total_amount'], 0) }} VNĐ
                        </span>
                        </div>

                    <!-- additional services -->
                    @if (!empty($data['services']))
                        @foreach ($data['services'] as $item)
                            <div class="list-item">
                                <span class="item-name" style="justify-content: space-between;">{{ $item['name'] }}</span>
                                <span class="item-price" style="margin-left: auto; text-align: right; min-width: 120px; display: inline-block;">
                                    {{ number_format($item['amount'], 0) }} VNĐ</span>
                            </div>
                        @endforeach
                    @endif

                    <hr style="border: 0; border-top: 1px solid #e5e7eb; margin: 10px 0;">

                    <div class="list-item">
                        <span class="item-name"><strong>Tổng tiền</strong></span>
                        <span class="item-price" style="margin-left: auto; text-align: right; min-width: 120px; display: inline-block;">
                            {{ number_format($total, 0) }} VNĐ
                        </span>
                    </div>

                    <div class="list-item">
                        <span class="item-name">Tiền đặt cọc phòng</span>
                        <span class="item-price" style="margin-left: auto; text-align: right; min-width: 120px; display: inline-block;">
                            {{ number_format($data['room_deposit'], 0) }} VNĐ
                        </span>
                    </div>
                </hr>

                <p style="font-size: 12px; color: #6b7280; margin-top: 10px; text-align: center;">
                    *Vui lòng xác nhận lại số tiền thanh toán cuối cùng khi đăng ký với
                    <strong>{{ $data['company_name'] }}</strong>
                </p>
            </div>

            <!-- Important Reminders -->
            <div class="info-box">
                <strong>Lưu ý quan trọng:</strong> Tình trạng phòng trống và báo giá được cập nhật tại thời điểm gửi email này. 
                Việc đặt phòng được xử lý theo thứ tự ưu tiên.
                Nếu bạn quan tâm, vui lòng cân nhắc đăng ký càng sớm càng tốt.
            </div>

            <!-- Support Information -->
            <div class="section">
                <div class="section-title">Cần hỗ trợ?</div>
                <p>Nếu bạn gặp khó khăn khi đăng ký trực tuyến, vui lòng liên hệ:</p>
                <p><strong>Tổng đài hỗ trợ hàng tháng:</strong> {{ $data['company_phone'] }} (miễn phí)</p>
                <p><strong>Mọi thắg mắc xin gửi đến:</strong> <a href="mailto: support@bks.co.jp" style="color: #3B82F6;">support@bks.co.jp</a></p>
            </div>

            <!-- Contact Information -->
            <div class="section">
                <div class="section-title">Thông tin liên hệ</div>
                <p><strong>Căn hộ/Phòng cho thuê theo ngày và tháng "BKS SYSTEM":</strong> <a href="https://bks.golineglobal.vn" style="color: #3B82F6;">https://bks.golineglobal.vn</a></p>
                <p><strong>Hệ thống quản lý đặt phòng "StayConnect":</strong> <a href="https://bks.stayconnect.jp" style="color: #3B82F6;">StayConnect</a></p>
                <p><strong>Được vận hành bởi:</strong> {{ $data['company_name'] }} </p>
            </div>

            <div class="section">
                <div class="section-title">Thông báo về thông tin bảo mật</div>
                <p>Email này chỉ dành cho người nhận được chỉ định xem và sử dụng. Nếu bạn không phải là người nhận được chỉ định, vui lòng liên hệ ngay với người gửi và xóa email này.</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Địa chỉ email này chỉ được sử dụng cho mục đích phân phối.</p>
            <p>Nếu bạn có bất kỳ câu hỏi nào, vui lòng liên hệ: {{ $data['goline_phone'] }}</p>
            <p style="margin-top: 10px;">&copy; {{ date('Y') }} Goline global. All rights reserved.
        </div>
    </div>
</body>
</html>
