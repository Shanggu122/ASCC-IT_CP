<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Forgot Password</title>
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/fp.css') }}">
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
  <a href="{{ request('role') === 'professor' ? route('login.professor') : (request('role') === 'admin' ? route('login.admin') : route('login')) }}" class="back-btn">
          <i class='bx bx-chevron-left'></i>
        </a>
        <span class="fp-title">Forgot Password</span>
      </div>
      <form action="{{ route('forgotpassword.send') }}" method="POST">
        @csrf
        @if(request('role'))<input type="hidden" name="role" value="{{ request('role') }}">@endif
        <h3 class="fp-instruction">Enter your Adamson email address</h3>
        <div class="input-group">
          <input type="email" name="email" placeholder="example@adamson.edu.ph" required>
          @error('email')<div class="field-error">{{ $message }}</div>@enderror
          @if(session('status'))<div class="field-success">{{ session('status') }}</div>@endif
        </div>
        <button type="submit" class="login-btn">Send OTP</button>
      </form>
    </div>
  </div>
</body>
</html>
