<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Information</title>
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
        .content {
            background-color: #f9fafb;
            padding: 20px;
            border: 1px solid #e5e7eb;
            border-radius: 0 0 5px 5px;
        }
        .button {
            display: inline-block;
            background-color: #3B82F6;
            color: #f3f4f6;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #6b7280;
        }
        .password-box {
            background-color: #f3f4f6;
            border: 1px solid #d1d5db;
            border-radius: 5px;
            padding: 15px;
            margin: 15px 0;
            text-align: center;
            font-family: monospace;
            font-size: 18px;
            letter-spacing: 2px;
        }
        @media screen and (max-width: 600px){
            .container{
                padding: 10px !important;
            }
            .header, .content{
                padding: 15px !important;
            }
            .button{
                padding: 10px 15px !important;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome to BKS</h1>
        </div>

        <div class="content">
            <p>Hello <strong>{{ $name }}</strong>,</p>

            <p>Thank you for registering an account with BKS. Below are your login details:</p>

            <p><strong>Email:</strong> {{ $email }}</p>

            <p>Please verify your email by clicking the button below:</p>

            <div style="text-align: center;">
                <a href="{{ config('app.url_frontend') }}/verify-email/{{ $token }}"
                style="display:inline-block;
                       background-color:#3B82F6;
                       color:#f3f4f6;
                       text-decoration:none;
                       padding:10px 20px;
                       border-radius:5px;
                       margin:20px 0;"
             >Verify Email</a>

            </div>

            <p>If you did not request this account, please ignore this email or contact us immediately.</p>

            <p>Best regards,</p>
        </div>

        <div class="footer">
            <p>This email was sent automatically, please do not reply.</p>
            <p>&copy; {{ date('Y') }} BKS. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
