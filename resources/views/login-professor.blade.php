@if(session('status'))
  <div class="alert alert-success">
    {{ session('status') }}
  </div>
@endif

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>ASCC-IT Login</title>
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/login.css') }}">
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
        <p><em>Catalyzing Change Innovating for Tomorrow</em></p>
      </div>
      <form action="{{ url('login-professor') }}" method="post">
        @csrf
        <div class="input-group">
          <input type="text" id="Prof_ID" name="Prof_ID" placeholder="Professor ID" required>
        </div>
        <div class="input-group password-group">
          <input type="password" id="password" name="Password" placeholder="Enter your password" />
          <i class='bx bx-hide toggle-password' id="toggle-password"></i>
        </div>
        <div class="options">
          <a href="{{ route('forgotpassword') }}">Forgot Password?</a>
        </div>
        <button type="submit" class="login-btn">Log In</button>
      </form>
    </div>
  </div>

  <script src="{{ asset('js/login.js') }}"></script>
</body>
</html> 