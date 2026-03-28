<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Verification Failed</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #fef2f2;
      text-align: center;
      padding: 60px;
    }
    .card {
      background: white;
      padding: 30px;
      border-radius: 10px;
      display: inline-block;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .error {
      color: #dc2626;
      font-size: 22px;
      margin-bottom: 20px;
    }
    .btn {
      background: #3b82f6;
      color: white;
      padding: 10px 20px;
      border-radius: 6px;
      text-decoration: none;
    }
  </style>
</head>
<body>
  <div class="card">
    <h2 class="error">{{ $message }}</h2>
    <p>Your verification link may have expired or is invalid.</p>
    <form action="{{ config('app.url_backend') }}api/v1/admin/auth/reset-token-verify-email" method="POST">
      @csrf
      <input type="hidden" name="token" value="{{ $token }}">
      <button type="submit" class="btn">Resend Verification Email</button>
    </form>
  </div>
</body>
</html>
