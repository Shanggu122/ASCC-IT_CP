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
  <title>Admin Login</title>
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/login-admin.css') }}">
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
      <form method="POST" action="{{ route('login.admin.submit') }}">
        @csrf
        <div class="input-group">
          <input type="text" name="Admin_ID" placeholder="Admin ID" required>
        </div>
        <div class="input-group password-group">
          <input type="password" name="Password" placeholder="Enter your password" required>
          <i class='bx bx-hide toggle-password' id="toggle-password"></i>
        </div>
        <div class="options-row">
          @if($errors->any())
            <div class="login-error">
              {{ $errors->first('login') }}
            </div>
          @else
            <span></span>
          @endif
        </div>
        <button type="submit" class="login-btn">Log In</button>
      </form>
    </div>
  </div>

  <script>
    // Simple password toggle
    document.getElementById("toggle-password").addEventListener("click", function () {
      const passwordInput = document.querySelector('input[name="Password"]');
      if (passwordInput.type === "password") {
        passwordInput.type = "text";
        this.classList.remove('bx-hide');
        this.classList.add('bx-show');
      } else {
        passwordInput.type = "password";
        this.classList.remove('bx-show');
        this.classList.add('bx-hide');
      }
    });
  </script>
</body>
</html>