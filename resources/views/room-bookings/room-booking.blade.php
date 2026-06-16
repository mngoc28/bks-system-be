<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác nhận đặt phòng - BKS Stay</title>
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
        .header.paid {
            background-color: #10b981;
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
        <div class="header {{ !empty($data['is_paid']) ? 'paid' : '' }}">
            <h1>Welcome to BKS</h1>
            <p>{{ !empty($data['is_paid']) ? 'Xác nhận thanh toán & Hoàn tất đặt phòng' : 'Xác nhận đặt phòng tại hệ thống BKS Stay' }}</p>
        </div>

        <!-- Content -->
        <div class="content">
            <!-- Greeting -->
            <div class="greeting">
                <p>Kính gửi: <strong>{{ $name }}</strong>,</p>
                @if(!empty($data['is_paid']))
                <p>Hệ thống <strong>BKS Stay</strong> đã nhận được thanh toán đặt cọc của bạn. Yêu cầu đặt phòng của bạn đã được chuyển sang trạng thái <strong>Đã xác nhận (Confirmed)</strong> và phòng đã được giữ thành công cho kỳ lưu trú của bạn.</p>
                @else
                <p>Đặt phòng của bạn đã được <strong style="color: #10b981;">ghi nhận thành công</strong> trên hệ thống <strong>BKS Stay</strong>. Phòng đã được hệ thống giữ cho bạn, vui lòng hoàn tất đặt cọc trước thời hạn bên dưới để đảm bảo chỗ ở.</p>
                @endif
            </div>

            <!-- Room Info -->
            <div class="highlight-box">
                <strong>{{ $data['room_title'] }} - {{ $data['property_name'] }}</strong><br>
                Địa chỉ: {{ $data['property_address'] }}<br>
                Mã đặt phòng: <strong>{{ $data['booking_code'] }}</strong><br>
                Ngày giờ đặt phòng: <strong>{{ $data['booking_created_at'] }}</strong><br>
                Từ ngày: <strong>{{ $data['start_time'] }}</strong><br>
                Đến ngày: <strong>{{ $data['end_time'] }}</strong><br>
                Tổng số {{ ($data['price_unit'] ?? 'night') === 'month' ? 'tháng' : 'đêm' }} lưu trú: <strong>{{ (int) ($data['total_days'] ?? 0) }} {{ ($data['price_unit'] ?? 'night') === 'month' ? 'tháng' : 'đêm' }}</strong><br>
                Xem chi tiết phòng tại:
                <a href="{{ $data['room_url'] }}"
                    style="text-decoration: underline;">Xem chi tiết căn hộ
                </a>
            </div>

            {{-- Deposit Deadline / Success Notice --}}
            @if(!empty($data['is_paid']))
            <div style="background:#f0fdf4; border:2px solid #bbf7d0; padding:16px; border-radius:8px; margin:16px 0; text-align:center;">
                <p style="margin:0 0 6px 0; font-size:13px; color:#166534; font-weight:700;">✅ THANH TOÁN THÀNH CÔNG</p>
                <p style="margin:0 0 6px 0; font-size:16px; font-weight:600; color:#14532d;">Đã xác nhận số tiền cọc: {{ number_format($data['room_deposit'], 0) }} VNĐ</p>
                <p style="margin:0; font-size:12px; color:#166534;">Cảm ơn bạn đã tin dùng dịch vụ BKS Stay.</p>
            </div>
            @elseif(!empty($data['deposit_deadline']))
            <div style="background:#fef2f2; border:2px solid #fca5a5; padding:16px; border-radius:8px; margin:16px 0; text-align:center;">
                <p style="margin:0 0 6px 0; font-size:13px; color:#374151; font-weight:600;">⏰ Hạn nộp cọc giữ phòng:</p>
                <p style="margin:0 0 6px 0; font-size:22px; font-weight:700; color:#dc2626; letter-spacing:1px;">{{ $data['deposit_deadline'] }}</p>
                <p style="margin:0; font-size:12px; color:#6b7280;">Sau thời điểm này, hệ thống sẽ tự động hủy đặt phòng nếu chưa có biên lai cọc.</p>
            </div>
            @endif

            <!-- Registration Links -->
            <div class="section">
                <div class="section-title">Đăng ký thông tin</div>
                
                <div style="background-color: #f9fafb; padding: 20px; border-radius: 8px; margin: 15px 0; line-height: 1.8;">
                    @if(!empty($data['is_first_time']))
                    <p style="margin: 0 0 10px 0; color: #4b5563;">
                        <strong>Bước 1:</strong> Đây là lần đầu bạn đặt phòng tại hệ thống BKS Stay. Vui lòng nhấp vào liên kết dưới đây để thiết lập mật khẩu và kích hoạt tài khoản thành viên của bạn:
                    </p>
                    <a href="{{ config('app.url_frontend') }}/set-password/{{ $data['token'] }}" style="color: #3B82F6; font-weight: 600; text-decoration: underline; display: inline-block; margin-bottom: 15px;">
                        Kích hoạt tài khoản & Thiết lập mật khẩu
                    </a>
                    <br>
                    <p style="margin: 10px 0 10px 0; color: #4b5563;">
                        <strong>Bước 2:</strong> Sau khi kích hoạt tài khoản thành công, nhấp vào liên kết dưới đây để xem trực tiếp chi tiết đặt phòng của bạn:
                    </p>
                    <a href="{{ $data['bookings_url'] }}" style="color: #3B82F6; font-weight: 600; text-decoration: underline;">
                        Xem chi tiết đặt phòng của bạn
                    </a>
                    @else
                    <p style="margin: 0 0 15px 0; color: #4b5563;">
                        Tài khoản thành viên của bạn đã tồn tại trên hệ thống BKS Stay. Vui lòng nhấp vào liên kết dưới đây để truy cập chi tiết đặt phòng của bạn (hệ thống sẽ tự động chuyển hướng đến trang đăng nhập nếu phiên làm việc đã hết hạn):
                    </p>
                    <a href="{{ $data['bookings_url'] }}" style="color: #3B82F6; font-weight: 600; text-decoration: underline;">
                        Xem chi tiết đặt phòng của bạn
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

                <div class="info-box" style="margin-top: 20px; background-color: #eff6ff; border: 1px solid #3B82F6; line-height: 1.8;">
                    <strong>ℹ️ Hướng dẫn nhận phòng (Check-in):</strong><br>
                    @if(($data['price_unit'] ?? '') === 'month')
                        • Sau khi đăng ký và được Đối tác (Chủ phòng) xác nhận đặt phòng <strong style="color: #10b981;">thành công</strong>, hệ thống BKS Stay sẽ tự động khởi tạo <strong>Hợp đồng thuê căn hộ dịch vụ</strong> điện tử.<br>
                        • <strong>Khuyến nghị:</strong> Bạn hãy truy cập chi tiết đặt phòng, bấm <strong>"Ký hợp đồng & Xác nhận"</strong> để thực hiện ký trực tuyến và chọn <strong>"In hợp đồng / Xem trước"</strong> để lưu hoặc in bản hợp đồng nhằm xuất trình khi nhận bàn giao căn hộ.
                    @else
                        • Sau khi đăng ký và được Đối tác (Chủ phòng) xác nhận đặt phòng <strong style="color: #10b981;">thành công</strong>, hệ thống BKS Stay sẽ tự động cấp phát <strong>Phiếu xác nhận lưu trú (Stay Voucher)</strong> để bạn kiểm tra trong phần chi tiết đặt phòng.<br>
                        • <strong>Khuyến nghị:</strong> Hãy bấm nút <strong>"Tải ảnh (PNG)"</strong> để lưu Phiếu về thiết bị ngay khi có thể, giúp bạn chủ động xuất trình cho lễ tân khi check-in ngay cả khi điện thoại không có kết nối internet.
                    @endif
                </div>

            <!-- Pricing Details -->
            <div class="section">
                <div class="section-title">Tổng giá trị ước tính</div>
                
                @php
                    $totalDays = (int) ($data['total_days'] ?? 0);
                    $servicesTotal = 0;
                    if (!empty($data['services'])) {
                        foreach ($data['services'] as $item) {
                            $servicesTotal += (float) ($item['amount'] ?? 0);
                        }
                    }
                    $roomStayAmount = (float) ($data['room_stay_amount'] ?? 0);
                    if ($roomStayAmount <= 0 && !empty($data['total_amount'])) {
                        $roomStayAmount = max(0, (float) $data['total_amount'] - $servicesTotal);
                    }
                    $grandTotal = $roomStayAmount + $servicesTotal;
                    $priceUnit = strtolower((string) ($data['price_unit'] ?? 'night'));
                    $isMonthly = $priceUnit === 'month';
                    $unitText = $isMonthly ? 'tháng' : 'đêm';
                    $unitSuffix = match ($priceUnit) {
                        'month' => ' · gói tháng',
                        'week'  => ' · gói tuần',
                        'year'  => ' · gói năm',
                        default => '',
                    };
                    $roomFeeLabel = 'Phí thuê phòng (' . $totalDays . ' ' . $unitText . $unitSuffix . ')';
                @endphp
                
                <div class="list-items">
                    @if (!empty($data['unit_price']) && (float) $data['unit_price'] > 0)
                        <div class="list-item" style="justify-content: space-between;">
                            <span class="item-name">Đơn giá</span>
                            <span class="item-price" style="margin-left: auto; text-align: right; min-width: 120px; display: inline-block;">
                                {{ number_format((float) $data['unit_price'], 0) }} VNĐ / {{ $data['price_unit'] === 'month' ? 'tháng' : 'đêm' }}
                            </span>
                        </div>
                    @endif
                    <div class="list-item" style="justify-content: space-between;">
                        <span class="item-name">{{ $roomFeeLabel }}</span>
                        <span class="item-price" style="margin-left: auto; text-align: right; min-width: 120px; display: inline-block;">
                            {{ number_format($roomStayAmount, 0) }} VNĐ
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
                            {{ number_format($grandTotal, 0) }} VNĐ
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
                <strong>Lưu ý quan trọng:</strong> Phòng đã được hệ thống giữ cho bạn kể từ khi đặt phòng thành công.
                Vui lòng hoàn tất đặt cọc trước hạn để tránh bị hủy tự động.
            </div>

            <div class="section">
                <div class="section-title">Chính sách hủy phòng</div>
                <div style="background:#fff; border:1px solid #e5e7eb; padding:15px; border-radius:8px;">
                    @if(!empty($data['cancellation_policy']))
                        {!! $data['cancellation_policy'] !!}
                    @else
                        <p style="font-size:13px; color:#6b7280;">Vui lòng liên hệ hỗ trợ để biết chi tiết chính sách hủy phòng.</p>
                    @endif
                </div>
            </div>

            {{-- Support Information --}}
            <div class="section">
                <div class="section-title">Cần hỗ trợ?</div>
                <p>Nếu bạn gặp khó khăn khi đăng ký trực tuyến, vui lòng liên hệ:</p>
                <p><strong>Tổng đài hỗ trợ hàng tháng:</strong> {{ $data['company_phone'] }} (miễn phí)</p>
                <p><strong>Mọi thắg mắc xin gửi đến:</strong> <a href="mailto: support@bks.co.jp" style="color: #3B82F6;">support@bks.co.jp</a></p>
            </div>

            <!-- Contact Information -->
            <div class="section">
                <div class="section-title">Thông tin liên hệ</div>
                <p><strong>Nền tảng căn hộ / căn hộ dịch vụ lưu trú ngắn và trung hạn "BKS SYSTEM":</strong> <a href="{{ config('app.url_frontend') }}" style="color: #3B82F6;">Website BKS System</a></p>
                <p><strong>Hệ thống quản lý đặt phòng "StayConnect":</strong> <a href="{{ config('app.url_frontend') }}/bks-stay" style="color: #3B82F6;">BKS Stay Portal</a></p>
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
