{{-- status moved under first input --}}

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>ASCC-IT Login</title>
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/loginprof.css') }}">
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
  <form action="{{ url('login-professor') }}" method="post" id="prof-login-form">
        @csrf
        <div class="input-group">
          <input type="text" id="Prof_ID" name="Prof_ID" placeholder="Professor ID" value="{{ old('Prof_ID') }}" required maxlength="9" pattern=".{1,9}" class="{{ $errors->has('Prof_ID') ? 'input-error' : '' }}" title="Maximum 9 characters">
        </div>
        <div class="input-group password-group">
          <input type="password" id="password" name="Password" placeholder="Enter your password" class="{{ $errors->has('Password') ? 'input-error' : '' }}" />
          <i class='bx bx-hide toggle-password' id="toggle-password"></i>
          @if(session('status'))<div class="field-success">{{ session('status') }}</div>@endif
        </div>
        <div class="options-row">
          @php
            $messages = [];
            if($errors->has('login')) $messages[] = $errors->first('login');
            if($errors->has('Prof_ID')) $messages[] = $errors->first('Prof_ID');
            if($errors->has('Password')) $messages[] = $errors->first('Password');
            if(session('error')) $messages[] = session('error');
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
          <a href="{{ route('forgotpassword', ['role'=>'professor']) }}">Forgot Password?</a>
        </div>
        <button type="submit" class="login-btn">Log In</button>
      </form>
    </div>
  </div>

  <div class="auth-loading-overlay" id="authLoading">
    <div class="auth-loading-spinner"></div>
    <div class="auth-loading-text">Signing you in...</div>
  </div>
  <script src="{{ asset('js/login.js') }}"></script>
  <script>
    (function(){
      const form = document.getElementById('prof-login-form');
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
  @include('partials.toast')
</body>
</html> 