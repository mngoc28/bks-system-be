<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Verification Result</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #ecfdf5;
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

    /* Colors according to status */
    .success {
      color: #16a34a;
    }
    .error {
      color: #dc2626;
    }
    .warning {
      color: #d97706;
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

    <!-- Title according to status -->
    <h2 class="{{ $status }}">{{ $message }}</h2>

    <p>{{ $content }}</p>

    <a href="{{ config('app.url_frontend') }}login" class="btn">Login Now</a>

  </div>
</body>
</html>
