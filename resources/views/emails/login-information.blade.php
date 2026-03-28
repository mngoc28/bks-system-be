<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông tin đăng nhập</title>
</head>
<body style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4; padding-top: 20px; padding-bottom: 20px;">
    <div style="max-width: 600px; margin: 20px auto; padding: 20px; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
        <div style="text-align: center; padding: 20px 0; border-bottom: 2px solid #f0f0f0;">
            <div style="font-size: 32px; font-weight: bold; color: #3498db; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 20px; text-decoration: none; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">GoEdu</div>
            <h2 style="color: #2c3e50; margin: 0; font-size: 24px;">Thông tin đăng nhập</h2>
        </div>

        <div style="padding: 30px 20px;">
            <p>Xin chào <strong>{{ $name }}</strong>,</p>

            <p>Chúng tôi đã tạo tài khoản cho bạn trong hệ thống GoEdu.</p>

            <p>Dưới đây là thông tin đăng nhập của bạn:</p>

            <div style="background-color: #f8f9fa; padding: 15px; border-radius: 4px; margin: 20px 0;">
                <p><strong>Email:</strong> {{ $email }}</p>
                <p><strong>Mật khẩu:</strong> {{ $password }}</p>
            </div>

            <p>Vui lòng nhấp vào nút bên dưới để đăng nhập vào hệ thống:</p>

            <div style="text-align: center;">
                <a href="{{ $loginUrl }}" style="display: inline-block; padding: 12px 50px; background-color: #3498db; color: #ffffff !important; text-decoration: none; border-radius: 4px; margin: 20px 0; font-weight: bold;">Đăng nhập</a>
            </div>

            <p>Nếu bạn không yêu cầu tạo tài khoản này, vui lòng bỏ qua email này.</p>
        </div>

        <div style="text-align: center; padding: 20px; color: #666; font-size: 14px; border-top: 2px solid #f0f0f0;">
            <p>Trân trọng,<br><strong>Đội ngũ GoEdu</strong></p>
            <p style="font-size: 12px; color: #999;">© {{ date('Y') }} GoEdu. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
