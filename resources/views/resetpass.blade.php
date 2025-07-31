<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Create New Password</title>
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/reset.css') }}">
  
</head>
<body>
  <div class="container">
    <!-- Left Panel -->
    <div class="left-panel">
      <!-- Update image path using asset() -->
      <img src="{{ asset('images/CCIT_logo2.png') }}" alt="Adamson Logo" class="left-logo"/>
      <h2>Adamson University College of<br><strong>Computing and Information Technology</strong></h2>
    </div>

    <!-- Right Panel -->
    <div class="right-panel">
      <div class="fp-header">
        <a href="{{ route('verify') }}" class="back-btn">
          <i class='bx bx-chevron-left'></i>
        </a>
        <span class="fp-title">New Password</span>
      </div>
      <form action="{{ route('login') }}" method="get">
        <div class="input-group">
          <input type="password" name="new_password" id="new-password" placeholder="New Password" required>
          <i class='bx bx-hide toggle-password' data-target="new-password"></i>
        </div>
        <div class="input-group">
          <input type="password" name="confirm_password" id="confirm-password" placeholder="Confirm New Password" required/>
          <i class='bx bx-hide toggle-password' data-target="confirm-password"></i>
        </div>
        <button type="submit" class="login-btn">Reset Password</button>
      </form>
    </div>
  </div>
 <script >
  document.querySelectorAll('.toggle-password').forEach(function(icon) {
    icon.addEventListener('click', function () {
        const inputId = this.getAttribute('data-target');
        const passwordInput = document.getElementById(inputId);
        if (passwordInput.type === "password") {
            passwordInput.type = "text";
            this.classList.replace("bx-hide", "bx-show");
        } else {
            passwordInput.type = "password";
            this.classList.replace("bx-show", "bx-hide");
        }
    });
});
  </script>
</body>
</html>
