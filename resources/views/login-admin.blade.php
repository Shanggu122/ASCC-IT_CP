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
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Admin Login</title>
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/login-admin.css') }}">
  <link rel="stylesheet" href="{{ asset('css/toast.css') }}">
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
  <form method="POST" action="{{ route('login.admin.submit') }}" id="admin-login-form">
        @csrf
        <div class="input-group">
          <input type="text" name="Admin_ID" placeholder="Admin ID" value="{{ old('Admin_ID') }}" required class="{{ $errors->has('Admin_ID') ? 'input-error' : '' }}">
        </div>
        <div class="input-group password-group">
          <input type="password" name="Password" placeholder="Enter your password" required class="{{ $errors->has('Password') ? 'input-error' : '' }}">
          <i class='bx bx-hide toggle-password' id="toggle-password"></i>
        </div>
        <div class="options-row" style="justify-content: space-between;">
          @php
            $messages = [];
            if($errors->has('login')) $messages[] = $errors->first('login');
            if($errors->has('Admin_ID')) $messages[] = $errors->first('Admin_ID');
            if($errors->has('Password')) $messages[] = $errors->first('Password');
          @endphp
          @if(count($messages))
            <div class="login-error" role="alert" aria-live="assertive">
              @foreach($messages as $i => $msg)
                @if($i) <br> @endif {{ $msg }}
              @endforeach
            </div>
          @else
            <span class="login-error-placeholder"></span>
          @endif
          <a href="{{ route('forgotpassword', ['role'=>'admin']) }}" class="forgot-link" style="white-space:nowrap;">Forgot Password?</a>
        </div>
        <button type="submit" class="login-btn">Log In</button>
      </form>
    </div>
  </div>

  <div class="auth-loading-overlay" id="authLoading">
    <div class="auth-loading-spinner"></div>
    <div class="auth-loading-text">Signing you in...</div>
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
    // Loading overlay
    (function(){
      const form = document.getElementById('admin-login-form');
      const overlay = document.getElementById('authLoading');
      if(!form || !overlay) return;
      const MIN_LOADING_MS = 1000;
      form.addEventListener('submit', function(e){
        if(form.dataset.submitting==='1') return;
        e.preventDefault();
        overlay.classList.add('active');
        form.dataset.submitting='1';
        setTimeout(()=>form.submit(), MIN_LOADING_MS);
      });
      window.addEventListener('pageshow', e=>{ if(e.persisted) overlay.classList.remove('active'); });
    })();
  </script>
</body>
@include('partials.toast')
</html>