<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Verify OTP</title>
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/verify.css') }}">
</head>
<body>
  <div class="container">
    <!-- Left Panel -->
    <div class="left-panel">
      <img src="{{ asset('images/CCIT_logo2.png') }}" alt="Adamson Logo" class="left-logo"/>
      <h2>Adamson University College of<br><strong>Computing and Information Technology</strong></h2>
    </div>

    <!-- Right Panel -->
    <div class="right-panel">
      <div class="fp-header">
        <a href="{{ route('forgotpassword') }}" class="back-btn">
          <i class='bx bx-chevron-left'></i>
        </a>
        <span class="fp-title">Email Verification</span>
      </div>
      <form action="{{ route('otp.verify') }}" method="POST" style="margin-top:0;">
        @csrf
        <div class="input-group">
          <h3 class="fp-instruction">Enter Verification Code</h3>
          <input type="text" name="otp" placeholder="Enter 4-digit OTP" maxlength="4" required>
          @error('otp')<div class="field-error">{{ $message }}</div>@enderror
          @if(session('status'))<div class="field-success">{{ session('status') }}</div>@endif
        </div>
        <button type="submit" class="login-btn" style="margin-top:10px;">Verify OTP</button>
        <div style="margin-top:14px;text-align:center;font-size:13px;">
            Didn't get the code? <a href="{{ route('otp.resend') }}" style="color:#0d5c46;font-weight:600;">Resend OTP</a>
        </div>
      </form>
    </div>
  </div>
</body>
</html>
