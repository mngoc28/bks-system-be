<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Bảng Kê Đối Soát Tài Chính</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #333333;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        .header-container {
            width: 100%;
            margin-bottom: 30px;
            border-bottom: 2px solid #10b981;
            padding-bottom: 15px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #10b981;
            float: left;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            text-align: right;
            color: #1f2937;
            margin-top: 5px;
        }
        .clear {
            clear: both;
        }
        .info-table {
            width: 100%;
            margin-bottom: 25px;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 4px 0;
            vertical-align: top;
        }
        .info-label {
            font-weight: bold;
            width: 150px;
            color: #4b5563;
        }
        .info-value {
            color: #1f2937;
        }
        .summary-box {
            background-color: #f3f4f6;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 30px;
        }
        .summary-title {
            font-size: 14px;
            font-weight: bold;
            margin-top: 0;
            margin-bottom: 10px;
            color: #065f46;
        }
        .summary-row {
            margin: 5px 0;
        }
        .summary-label {
            display: inline-block;
            width: 250px;
            font-weight: bold;
        }
        .summary-value {
            display: inline-block;
            font-weight: bold;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .data-table th {
            background-color: #10b981;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            padding: 8px;
            border: 1px solid #e5e7eb;
        }
        .data-table td {
            padding: 8px;
            border: 1px solid #e5e7eb;
        }
        .data-table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .bank-box {
            border: 1px dashed #10b981;
            background-color: #f0fdf4;
            padding: 15px;
            border-radius: 6px;
            margin-top: 30px;
        }
        .bank-title {
            font-weight: bold;
            color: #047857;
            font-size: 13px;
            margin-top: 0;
            margin-bottom: 8px;
        }
        .bank-row {
            margin: 4px 0;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="header-container">
        <div class="logo">BKS STAY</div>
        <div class="title">BẢNG KÊ ĐỐI SOÁT TÀI CHÍNH</div>
        <div class="clear"></div>
    </div>

    <table class="info-table">
        <tr>
            <td class="info-label">Đối tác:</td>
            <td class="info-value">{{ $period->partner->name }}</td>
            <td class="info-label" style="text-align: right;">Mã quyết toán:</td>
            <td class="info-value" style="text-align: right;">BKS-SETTLE-{{ $period->id }}</td>
        </tr>
        <tr>
            <td class="info-label">Email đối tác:</td>
            <td class="info-value">{{ $period->partner->email }}</td>
            <td class="info-label" style="text-align: right;">Ngày phát hành:</td>
            <td class="info-value" style="text-align: right;">{{ $period->issue_date->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td class="info-label">Kỳ đối soát:</td>
            <td class="info-value">{{ $period->period_start->format('d/m/Y') }} - {{ $period->period_end->format('d/m/Y') }}</td>
            <td class="info-label" style="text-align: right;">Trạng thái:</td>
            <td class="info-value" style="text-align: right; font-weight: bold; text-transform: uppercase;">{{ $period->status }}</td>
        </tr>
    </table>

    <div class="summary-box">
        <div class="summary-title">TÓM TẮT QUYẾT TOÁN CÔNG NỢ</div>
        <div class="summary-row">
            <span class="summary-label">1. Tổng doanh thu (GMV Phòng + Dịch vụ):</span>
            <span class="summary-value">{{ number_format($period->total_gmv, 0, ',', '.') }} VND</span>
        </div>
        <div class="summary-row">
            <span class="summary-label">2. Phí dịch vụ nền tảng ({{ $period->commission_rate * 100 }}%):</span>
            <span class="summary-value">{{ number_format($period->total_commission, 0, ',', '.') }} VND</span>
        </div>
        @if($period->total_adjustments != 0)
        <div class="summary-row">
            <span class="summary-label">3. Tổng điều chỉnh công nợ:</span>
            <span class="summary-value" style="color: {{ $period->total_adjustments < 0 ? '#b91c1c' : '#047857' }}">
                {{ number_format($period->total_adjustments, 0, ',', '.') }} VND
            </span>
        </div>
        @endif
        <div class="summary-row" style="margin-top: 10px; border-top: 1px solid #d1d5db; padding-top: 8px; font-size: 14px;">
            <span class="summary-label" style="color: #b91c1c;">THỰC THU HOA HỒNG (Net Commission):</span>
            <span class="summary-value" style="color: #b91c1c;">{{ number_format($period->net_commission_to_pay, 0, ',', '.') }} VND</span>
        </div>
    </div>

    <h3>CHI TIẾT ĐƠN ĐẶT PHÒNG TRONG KỲ</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">STT</th>
                <th style="width: 20%;">Mã Đơn</th>
                <th style="width: 25%;">Tên Căn Hộ / Phòng</th>
                <th style="width: 15%;">Ngày Checkout</th>
                <th style="width: 15%; text-align: right;">Doanh Thu (VND)</th>
                <th style="width: 20%; text-align: right;">Hoa Hồng (VND)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($lineItems as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $item->booking_code }}</td>
                <td>{{ $item->booking?->room?->title ?? 'N/A' }}</td>
                <td>{{ $item->checkout_date ? $item->checkout_date->format('d/m/Y') : 'N/A' }}</td>
                <td class="text-right">{{ number_format($item->total_gmv, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($item->commission_amount, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($period->adjustments->isNotEmpty())
    <h3>DÒNG ĐIỀU CHỈNH CÔNG NỢ</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">STT</th>
                <th style="width: 20%; text-align: right;">Số Tiền (VND)</th>
                <th style="width: 50%;">Lý Do Điều Chỉnh</th>
                <th style="width: 25%;">Người Tạo</th>
            </tr>
        </thead>
        <tbody>
            @foreach($period->adjustments as $idx => $adj)
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td class="text-right" style="color: {{ $adj->amount < 0 ? '#b91c1c' : '#047857' }}">
                    {{ number_format($adj->amount, 0, ',', '.') }}
                </td>
                <td>{{ $adj->reason }}</td>
                <td>{{ $adj->creator?->name ?? 'Admin' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div class="bank-box">
        <h4 class="bank-title">HƯỚNG DẪN CHUYỂN KHOẢN THANH TOÁN PHÍ:</h4>
        <div class="bank-row"><strong>Ngân hàng thụ hưởng:</strong> {{ $bankInfo['bank_name'] }}</div>
        <div class="bank-row"><strong>Số tài khoản:</strong> {{ $bankInfo['account_number'] }}</div>
        <div class="bank-row"><strong>Chủ tài khoản:</strong> {{ $bankInfo['account_holder'] }}</div>
        <div class="bank-row" style="color: #2563eb;">
            <strong>Nội dung chuyển khoản (bắt buộc):</strong> <strong>{{ $transferSyntax }}</strong>
        </div>
        <div class="bank-row" style="color: #b91c1c; font-weight: bold; margin-top: 8px;">
            Hạn cuối nhận thanh toán phí dịch vụ: {{ $dueDate }}
        </div>
    </div>

</body>
</html>
