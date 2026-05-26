<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Hợp đồng nguyên tắc</title>
    <link rel="stylesheet" href="{{ public_path('css/contract.css') }}">
</head>
<body>
    <div class="contract-container">
        <div class="national-motto">
            <strong>CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM</strong><br>
            <span class="motto-sub">Độc lập - Tự do - Hạnh phúc</span>
            <div class="motto-line"></div>
        </div>

        <div class="title">HỢP ĐỒNG NGUYÊN TẮC</div>
        <div class="subtitle">Dịch vụ Stay và Hợp tác đại lý BKS</div>
        
        <p>Hợp đồng này được ký kết vào ngày <strong>{{ $date }}</strong> giữa các bên dưới đây:</p>
        
        <table class="party-table">
            <tr>
                <td class="party-card">
                    <div class="party-title">BÊN A (Đại diện Nền tảng)</div>
                    <strong>Hệ thống BKS (BKS System)</strong><br>
                    Điện thoại: 0999999999<br>
                    Email: support@bks.co.jp<br>
                    Website: bks-stay.com
                </td>
                <td style="width: 4%;"></td>
                <td class="party-card">
                    <div class="party-title">BÊN B (Đối tác liên kết)</div>
                    <strong>{{ $company_name ?? 'N/A' }}</strong><br>
                    Đại diện: {{ $representative_name ?? 'N/A' }}<br>
                    Mã số thuế: {{ $tax_code ?? 'N/A' }}<br>
                    Địa chỉ: {{ $address ?? 'N/A' }}
                </td>
            </tr>
        </table>
        
        <div class="section-title">ĐIỀU KHOẢN CHUNG</div>
        <p>1. Bên B đồng ý đăng ký tài khoản đại lý trên nền tảng BKS để khai thác, quản lý phòng và dịch vụ lưu trú.</p>
        <p>2. Bên A cung cấp giải pháp kỹ thuật, hệ thống quản lý BKS và hỗ trợ quảng bá, phân phối phòng cho Bên B.</p>
        <p>3. Doanh thu và tỷ lệ đối soát tài chính sẽ được thực hiện tự động qua tài khoản đối soát đã đăng ký của Bên B.</p>
        <p>4. Hai bên cam kết thực hiện đúng trách nhiệm, bảo mật thông tin khách hàng và tuân thủ pháp luật hiện hành.</p>
        
        <table class="signature-table">
            <tr>
                <td class="signature-cell">
                    <p><strong>Đại diện Bên A</strong></p>
                    <p style="margin-top: 60px; color: #9ca3af; font-style: italic;">(Đã ký điện tử)</p>
                </td>
                <td class="signature-cell">
                    <p><strong>Đại diện Bên B</strong></p>
                    <div style="margin-top: 10px; min-height: 80px; display: inline-block;">
                        @if(!empty($signature_base64))
                            <img src="data:image/png;base64,{{ $signature_base64 }}" style="width: 150px; max-height: 80px; object-fit: contain;" />
                        @else
                            <p style="color: #ef4444; font-style: italic;">(Lỗi tải chữ ký)</p>
                        @endif
                    </div>
                    <p style="margin-top: 5px; font-size: 12px; color: #4b5563;">{{ $representative_name ?? '' }}</p>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
