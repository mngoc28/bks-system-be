<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông báo phát hành kỳ đối soát hoa hồng</title>
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
            background: linear-gradient(135deg, #10b981 0%, #047857 100%);
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
            margin-bottom: 20px;
            font-size: 16px;
        }
        .highlight-box {
            background-color: #f0fdf4;
            border-left: 4px solid #10b981;
            padding: 16px;
            border-radius: 4px;
            margin: 24px 0;
        }
        .highlight-title {
            font-weight: 700;
            color: #065f46;
            margin-bottom: 8px;
        }
        .highlight-item {
            margin: 4px 0;
            font-size: 15px;
        }
        .bank-box {
            background-color: #f9fafb;
            border: 1px dashed #d1d5db;
            padding: 16px;
            border-radius: 8px;
            margin: 24px 0;
        }
        .bank-title {
            font-weight: 700;
            color: #374151;
            margin-bottom: 8px;
        }
        .bank-item {
            margin: 4px 0;
            font-size: 15px;
        }
        .cta-container {
            text-align: center;
            margin: 32px 0;
        }
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.3);
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
                <h1>BKS STAY - ĐỐI SOÁT TÀI CHÍNH</h1>
            </div>
            <div class="content">
                <p class="greeting">Xin chào {{ $partnerName }},</p>
                <p class="body-text">BKS Stay xin thông báo bảng kê đối soát công nợ hoa hồng cho kỳ lưu trú của bạn đã được phát hành chính thức.</p>
                <p class="body-text">Dưới đây là tóm tắt thông tin quyết toán phí dịch vụ nền tảng:</p>
                
                <div class="highlight-box">
                    <div class="highlight-title">Tóm tắt đối soát:</div>
                    <div class="highlight-item"><strong>Kỳ đối soát:</strong> {{ $period->period_start->format('d/m/Y') }} đến {{ $period->period_end->format('d/m/Y') }}</div>
                    <div class="highlight-item"><strong>Tổng GMV (Phòng + Dịch vụ):</strong> {{ number_format($period->total_gmv, 0, ',', '.') }} VND</div>
                    <div class="highlight-item"><strong>Phí hoa hồng gốc ({{ $period->commission_rate * 100 }}%):</strong> {{ number_format($period->total_commission, 0, ',', '.') }} VND</div>
                    @if($period->total_adjustments != 0)
                    <div class="highlight-item"><strong>Điều chỉnh công nợ:</strong> {{ number_format($period->total_adjustments, 0, ',', '.') }} VND</div>
                    @endif
                    <div class="highlight-item" style="font-size: 17px; margin-top: 8px; color: #b91c1c;">
                        <strong>Thực thu phí hoa hồng (Net Commission):</strong> <strong>{{ number_format($period->net_commission_to_pay, 0, ',', '.') }} VND</strong>
                    </div>
                    <div class="highlight-item" style="color: #b91c1c;"><strong>Hạn thanh toán:</strong> {{ $dueDate }}</div>
                </div>

                <p class="body-text">Vui lòng thực hiện chuyển khoản thanh toán phí dịch vụ nền tảng trước ngày hạn nêu trên vào tài khoản ngân hàng của BKS Stay dưới đây:</p>

                <div class="bank-box">
                    <div class="bank-title">Thông tin tài khoản nhận phí:</div>
                    <div class="bank-item"><strong>Ngân hàng thụ hưởng:</strong> {{ $bankInfo['bank_name'] }}</div>
                    <div class="bank-item"><strong>Số tài khoản:</strong> {{ $bankInfo['account_number'] }}</div>
                    <div class="bank-item"><strong>Chủ tài khoản:</strong> {{ $bankInfo['account_holder'] }}</div>
                    <div class="bank-item" style="color: #2563eb; font-size: 16px; margin-top: 6px;">
                        <strong>Nội dung chuyển khoản (bắt buộc):</strong> <strong>{{ $transferSyntax }}</strong>
                    </div>
                </div>
                
                <div class="cta-container">
                    <a href="{{ config('app.url_frontend') }}/partner/finance" class="btn">Xem chi tiết trên Partner Portal</a>
                </div>
                
                <p class="body-text">Nếu bạn phát hiện sai sót hoặc có khiếu nại về bảng kê, vui lòng nhấn nút <strong>Khiếu nại</strong> trên Partner Portal hoặc liên hệ hotline hỗ trợ của BKS Stay.</p>
                <p class="body-text">Trân trọng,<br><strong>Đội ngũ BKS Stay</strong></p>
            </div>
            <div class="footer">
                <p>Email này được gửi tự động từ hệ thống BKS Stay, vui lòng không trả lời trực tiếp.</p>
                <p>&copy; {{ date('Y') }} BKS Stay. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
