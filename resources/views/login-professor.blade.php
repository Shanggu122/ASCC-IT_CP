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
      <a href="{{ route('landing') }}" class="back-to-landing" aria-label="Back to landing page" title="Back">
        <i class='bx bx-left-arrow-alt'></i>
      </a>
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
  <form action="{{ url('login-professor') }}" method="post" id="prof-login-form" autocomplete="on">
        @csrf
        <div class="input-group float-stack">
          <input type="text" id="Prof_ID" name="Prof_ID" placeholder=" " value="{{ old('Prof_ID') }}" required maxlength="9" inputmode="numeric" autocomplete="username" class="numeric-only {{ $errors->has('Prof_ID') ? 'input-error' : '' }}" />
          <label for="Prof_ID">Professor ID</label>
        </div>
        <div class="input-group password-group float-stack">
          <input type="password" id="prof-password" name="Password" placeholder=" " required class="{{ $errors->has('Password') ? 'input-error' : '' }}" autocomplete="current-password" />
          <label for="prof-password">Password</label>
          <button type="button" class="toggle-password" id="toggle-password-btn" aria-label="Show password" aria-pressed="false"><i class='bx bx-hide'></i></button>
        </div>
  <div class="options-row" data-lock-prof="{{ session('lock_until_prof') ?? '' }}">
          @php
            $messages = [];
            if($errors->has('login')) $messages[] = $errors->first('login');
            if($errors->has('Prof_ID')) $messages[] = $errors->first('Prof_ID');
            if($errors->has('Password')) $messages[] = $errors->first('Password');
            if(session('error')) $messages[] = session('error');
          @endphp
          @if(session('status'))
            <div class="field-success" role="status" aria-live="polite">{{ session('status') }}</div>
          @elseif(count($messages))
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
  <button type="submit" class="login-btn" id="prof-login-btn">Log In</button>
        <div class="below-actions">
          <a class="forgot-bottom" href="{{ route('forgotpassword', ['role'=>'professor']) }}">Forgot Password?</a>
        </div>
      </form>
    </div>
  </div>

  <div class="auth-loading-overlay" id="authLoading">
    <div class="auth-loading-spinner"></div>
    <div class="auth-loading-text">Signing you in...</div>
  </div>
  <script src="{{ asset('js/login.js') }}"></script>
  <script src="{{ asset('js/errors-auto-dismiss.js') }}"></script>
  <script>
    // Mark prefilled/autofilled inputs so floating labels lift
    (function(){
      const form = document.getElementById('prof-login-form');
      if(!form) return;
      const inputs = form.querySelectorAll('.float-stack input');
      const apply = (el)=>{ if(el.value && !el.classList.contains('filled')) el.classList.add('filled'); else if(!el.value) el.classList.remove('filled'); };
      inputs.forEach(i=>{ apply(i); ['input','change'].forEach(ev=>i.addEventListener(ev,()=>apply(i))); });
      // Some browsers fill after DOMContentLoaded; re-check shortly
      setTimeout(()=>inputs.forEach(apply), 120);
      // Visibility change (when returning to tab) might trigger late autofill
      document.addEventListener('visibilitychange', ()=>{ if(!document.hidden) inputs.forEach(apply); });
    })();
    // Loading overlay & submit throttle
    (function(){
      const form = document.getElementById('prof-login-form');
      const overlay = document.getElementById('authLoading');
      if(!form || !overlay) return;
  const MIN_LOADING_MS = 2000; // standardized 2s delay
      form.addEventListener('submit', function(e){
        if(form.dataset.submitting==='1') return;
        e.preventDefault();
        overlay.classList.add('active');
        form.dataset.submitting='1';
        setTimeout(()=>form.submit(), MIN_LOADING_MS);
      });
      window.addEventListener('pageshow', e=>{ if(e.persisted) overlay.classList.remove('active'); });
    })();

    // Remember Professor ID locally (independent of server remember cookie)
    (function(){
      const KEY = 'login_prof_id';
      const idInput = document.getElementById('Prof_ID');
      const rememberBox = document.querySelector('#prof-login-form input[name="remember"]');
      if(!idInput || !rememberBox) return;
      if(!idInput.value){
        const stored = localStorage.getItem(KEY);
        if(stored){ idInput.value = stored; rememberBox.checked = true; }
      }
      const form = document.getElementById('prof-login-form');
      if(form){
        form.addEventListener('submit', ()=>{
          if(rememberBox.checked && idInput.value){
            localStorage.setItem(KEY, idInput.value.trim());
          } else {
            localStorage.removeItem(KEY);
          }
        });
      }
    })();
    // Live lock countdown (professor)
    (function(){
      const wrap = document.querySelector('.options-row[data-lock-prof]');
      if(!wrap) return;
      const lockUntil = parseInt(wrap.getAttribute('data-lock-prof'),10);
      if(!lockUntil) return;
      const btn = document.getElementById('prof-login-btn');
      const errBox = wrap.querySelector('.login-error');
      const placeholder = wrap.querySelector('.login-error-placeholder');
      function ensureBox(){
        if(errBox) return errBox;
        if(placeholder){ const div=document.createElement('div'); div.className='login-error'; placeholder.replaceWith(div); return div; }
        return null;
      }
      const box = ensureBox();
      if(btn) btn.disabled = true;
      function tick(){
        const remain = lockUntil - Math.floor(Date.now()/1000);
        if(remain>0){ if(box) box.textContent='Too many attempts. Try again in '+remain+'s.'; }
        else { if(box) box.textContent=''; if(btn) btn.disabled=false; clearInterval(intv); }
      }
      tick();
      const intv = setInterval(tick,1000);
    })();
  </script>
  @include('partials.toast')
</body>
</html> 