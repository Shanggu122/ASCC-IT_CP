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
  <form method="POST" action="{{ route('login.admin.submit') }}" id="admin-login-form" autocomplete="on">
        @csrf
        <div class="input-group float-stack">
          <input type="text" id="Admin_ID" name="Admin_ID" placeholder=" " value="{{ old('Admin_ID') }}" required maxlength="9" inputmode="numeric" class="numeric-only {{ $errors->has('Admin_ID') ? 'input-error' : '' }}" autocomplete="username" />
          <label for="Admin_ID">Admin ID</label>
        </div>
        <div class="input-group password-group float-stack">
          <input type="password" id="admin-password" name="Password" placeholder=" " required class="{{ $errors->has('Password') ? 'input-error' : '' }}" autocomplete="current-password" />
          <label for="admin-password">Password</label>
          <button type="button" class="toggle-password" id="toggle-password-btn" aria-label="Show password" aria-pressed="false"><i class='bx bx-hide'></i></button>
        </div>
  <div class="options-row" data-lock-admin="{{ session('lock_until_admin') ?? '' }}">
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
          <label class="remember-inline"><input type="checkbox" name="remember" value="1"> <span>Remember me</span></label>
        </div>
  <button type="submit" class="login-btn" id="admin-login-btn">Log In</button>
      </form>
    </div>
  </div>

  <div class="auth-loading-overlay" id="authLoading">
    <div class="auth-loading-spinner"></div>
    <div class="auth-loading-text">Signing you in...</div>
  </div>
  <script src="{{ asset('js/login.js') }}"></script>
  <script>
    // Autofill / prefilled support to float labels (admin)
    (function(){
      const form = document.getElementById('admin-login-form');
      if(!form) return;
      const inputs = form.querySelectorAll('.float-stack input');
      const apply = el=>{ if(el.value) el.classList.add('filled'); else el.classList.remove('filled'); };
      inputs.forEach(i=>{ apply(i); ['input','change'].forEach(ev=>i.addEventListener(ev,()=>apply(i))); });
      setTimeout(()=>inputs.forEach(apply), 120);
      document.addEventListener('visibilitychange', ()=>{ if(!document.hidden) inputs.forEach(apply); });
    })();
    // Password toggle handled globally in login.js
    // Loading overlay
    (function(){
      const form = document.getElementById('admin-login-form');
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
    // Remember Admin ID (local only)
    (function(){
      const KEY = 'login_admin_id';
      const idInput = document.getElementById('Admin_ID');
      const rememberBox = document.querySelector('#admin-login-form input[name="remember"]');
      if(!idInput || !rememberBox) return;
      if(!idInput.value){
        const stored = localStorage.getItem(KEY);
        if(stored){ idInput.value = stored; rememberBox.checked = true; }
      }
      const form = document.getElementById('admin-login-form');
      if(form){
        form.addEventListener('submit', ()=>{
          if(rememberBox.checked && idInput.value){ localStorage.setItem(KEY, idInput.value.trim()); }
          else { localStorage.removeItem(KEY); }
        });
      }
    })();
    // Live lock countdown (admin)
    (function(){
      const wrap = document.querySelector('.options-row[data-lock-admin]');
      if(!wrap) return;
      const lockUntil = parseInt(wrap.getAttribute('data-lock-admin'),10);
      if(!lockUntil) return;
      const btn = document.getElementById('admin-login-btn');
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
</body>
@include('partials.toast')
</html>