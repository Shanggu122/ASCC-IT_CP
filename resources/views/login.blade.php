{{-- status moved under first input --}}

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>ASCC-IT Login</title>
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/login.css') }}">
  <link rel="stylesheet" href="{{ asset('css/toast.css') }}">
</head>
<body>
  <div class="container">
    <!-- Left Panel -->
    <div class="left-panel">
      <img src="{{ asset('images/CCIT_logo2.png') }}" alt="Adamson Logo" class="left-logo"/>
      <h2 class="college-title">
        <div class="adamson-uni">Adamson University</div>
        <div class="college-bottom">College of Computing and Information Technology</div>
      </h2>
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
  <form action="{{ route('login.submit') }}" method="post" id="student-login-form" autocomplete="on">
        @csrf
        <div class="input-group float-stack">
          <input type="text" id="Stud_ID" name="Stud_ID" placeholder=" " value="{{ old('Stud_ID') }}" required maxlength="9" pattern="\d{1,9}" inputmode="numeric" autocomplete="username" class="{{ $errors->has('Stud_ID') ? 'input-error' : '' }}" title="Enter up to 9 numeric digits">
          <label for="Stud_ID">Student ID</label>
        </div>
        <div class="input-group password-group float-stack">
          <input type="password" id="password" name="Password" placeholder=" " class="{{ $errors->has('Password') ? 'input-error' : '' }}" autocomplete="current-password" />
          <label for="password">Password</label>
          <button type="button" class="toggle-password" id="toggle-password-btn" aria-label="Show password" aria-pressed="false"><i class='bx bx-hide'></i></button>
          @if(session('status'))<div class="field-success">{{ session('status') }}</div>@endif
        </div>
        <div class="options-row">
          @php
            $messages = [];
            if($errors->has('login')) $messages[] = $errors->first('login');
            if($errors->has('Stud_ID')) $messages[] = $errors->first('Stud_ID');
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
          <label class="remember-inline"><input type="checkbox" name="remember" value="1"> <span>Remember me</span></label>
        </div>
        <button type="submit" class="login-btn">Log In</button>
        <div class="below-actions">
          <a class="forgot-bottom" href="{{ route('forgotpassword') }}">Forgot Password?</a>
        </div>
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
      const form = document.getElementById('student-login-form');
      const overlay = document.getElementById('authLoading');
      if(!form || !overlay) return;
  const MIN_LOADING_MS = 1000; // 1 second minimum display
      form.addEventListener('submit', function(e){
        if(form.dataset.submitting==='1') return; // prevent double
        // Browser native validation runs before this; if we reach here inputs are valid
        e.preventDefault();
        overlay.classList.add('active');
        form.dataset.submitting='1';
        setTimeout(()=> form.submit(), MIN_LOADING_MS);
      });
      // Optional: hide overlay if back/forward navigation caches page
      window.addEventListener('pageshow', e => { if(e.persisted) overlay.classList.remove('active'); });
    })();

    // Idle timeout warning (60s before server 5-minute timeout)
    (function(){
      const idleMinutes = {{ (int)config('auth_security.idle_timeout_minutes',5) }};
      const warnSeconds = 60; // show warning 1 minute before logout
      const warningAt = (idleMinutes*60 - warnSeconds) * 1000;
      if (warningAt <= 0) return; // too small to matter
      let warned = false;
      function showWarning(){
        if(warned) return; warned = true;
        const div = document.createElement('div');
        div.id='idle-warning';
        div.style.cssText='position:fixed;bottom:1rem;right:1rem;background:#222;color:#fff;padding:1rem 1.25rem;border-radius:8px;z-index:2000;font-size:.9rem;box-shadow:0 4px 12px rgba(0,0,0,.3);';
        div.innerHTML='You will be logged out in <span id="idle-count">'+warnSeconds+'</span>s due to inactivity.';
        document.body.appendChild(div);
        let remain = warnSeconds;
        const intv=setInterval(()=>{ remain--; const span=document.getElementById('idle-count'); if(span) span.textContent=remain; if(remain<=0){ clearInterval(intv); div.remove(); } },1000);
      }
      let timer = setTimeout(showWarning, warningAt);
      const reset = () => { if(warned) return; clearTimeout(timer); timer = setTimeout(showWarning, warningAt); };
      ['mousemove','keydown','click','scroll','touchstart'].forEach(evt=>window.addEventListener(evt, reset));
    })();

    // Remember Student ID locally (not password) independent of server remember cookie
    (function(){
      const KEY = 'login_stud_id';
      const idInput = document.getElementById('Stud_ID');
      const rememberBox = document.querySelector('input[name="remember"]');
      if(!idInput || !rememberBox) return;
      // Prefill if stored and no old value
      if(!idInput.value){
        const stored = localStorage.getItem(KEY);
        if(stored){ idInput.value = stored; rememberBox.checked = true; }
      }
      // On submit, decide to store or clear
      const form = document.getElementById('student-login-form');
      if(form){
        form.addEventListener('submit', ()=>{
          if(rememberBox.checked && idInput.value){
            localStorage.setItem(KEY, idInput.value.trim());
          } else {
            localStorage.removeItem(KEY);
          }
        });
      }
      // Provide quick clear option via context menu (optional UX)
      idInput.addEventListener('contextmenu', e=>{
        if(localStorage.getItem(KEY)){
          // hold Shift + right-click to clear
          if(e.shiftKey){ localStorage.removeItem(KEY); }
        }
      });
    })();
  </script>
  @include('partials.toast')
</body>
</html>

