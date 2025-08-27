{{-- status moved under first input --}}

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>ASCC-IT Login</title>
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/loginprof.css') }}">
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
      <div class="brand">
        <img src="{{ asset('images/ASCCITlogo.png') }}" alt="ASCC-IT Logo" class="small-logo">
        <h1>ASCC-IT</h1>
      </div>
      <p class="brand-slogan">
        <em><b>C</b>atalyzing <b>C</b>hange <b>I</b>nnovating for <b>T</b>omorrow</em>
      </p>
      <form action="{{ url('login-professor') }}" method="post">
        @csrf
        <div class="input-group">
          <input type="text" id="Prof_ID" name="Prof_ID" placeholder="Professor ID" required>
          @error('Prof_ID')<div class="field-error">{{ $message }}</div>@enderror
        </div>
        <div class="input-group password-group">
          <input type="password" id="password" name="Password" placeholder="Enter your password" />
          <i class='bx bx-hide toggle-password' id="toggle-password"></i>
          @error('Password')<div class="field-error">{{ $message }}</div>@enderror
          @if(session('status'))<div class="field-success">{{ session('status') }}</div>@endif
        </div>
        <div class="options-row">
          @if($errors->has('login'))
            <div class="login-error">{{ $errors->first('login') }}</div>
          @elseif(session('error'))
            <div class="login-error">{{ session('error') }}</div>
          @else
            <span></span>
          @endif
          <a href="{{ route('forgotpassword') }}">Forgot Password?</a>
        </div>
        <button type="submit" class="login-btn">Log In</button>
      </form>
    </div>
  </div>

  <script src="{{ asset('js/login.js') }}"></script>
</body>
</html> 