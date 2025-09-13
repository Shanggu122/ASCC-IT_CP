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
      <h2 class="college-title">
        <div class="adamson-uni">Adamson University</div>
        <div class="college-bottom">College of Computing and Information Technology</div>
      </h2>
    </div>

    <!-- Right Panel -->
    <div class="right-panel">
      <div class="fp-header">
  @php $roleParam = request('role') ?? session('password_reset_role_param'); @endphp
  <a href="{{ route('otp.verify.form', ['role'=>$roleParam]) }}" class="back-btn">
          <i class='bx bx-chevron-left'></i>
        </a>
        <span class="fp-title">New Password</span>
      </div>
      <form action="{{ route('password.update') }}" method="POST">
        @csrf
        <div class="input-group">
          <input type="password" name="new_password" id="new-password" placeholder="New Password" required>
          <i class='bx bx-hide toggle-password' data-target="new-password"></i>
        </div>
        <div class="input-group">
          <input type="password" name="new_password_confirmation" id="confirm-password" placeholder="Confirm New Password" required/>
          <i class='bx bx-hide toggle-password' data-target="confirm-password"></i>
          @php
            $firstError = $errors->first('new_password') ?: $errors->first('new_password_confirmation');
          @endphp
          @if($firstError)
            <div class="field-error">{{ $firstError }}</div>
          @endif
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
<script>
// Prevent copying the new password and pasting into confirmation without altering placeholders
(function() {
  const newPwd = document.getElementById('new-password');
  const confirmPwd = document.getElementById('confirm-password');
  if(!newPwd || !confirmPwd) return;
  ['copy','cut'].forEach(evt => newPwd.addEventListener(evt, e => e.preventDefault()));
  ['paste','drop'].forEach(evt => confirmPwd.addEventListener(evt, e => { e.preventDefault(); confirmPwd.value=''; }));
  newPwd.addEventListener('dragstart', e => e.preventDefault());
  confirmPwd.addEventListener('contextmenu', e => e.preventDefault());
})();
</script>
