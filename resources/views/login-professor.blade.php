<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Professor Login</title>
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
	<link rel="stylesheet" href="{{ asset('css/login.css') }}">
	<link rel="stylesheet" href="{{ asset('css/toast.css') }}">
</head>
<body class="prof-login">
	<main class="auth-wrapper">
		<section class="auth-card">
			<header class="auth-header">
				<h1>Professor Portal</h1>
				<p>Sign in with your consultation credentials.</p>
			</header>
			<form id="prof-login-form" action="{{ route('login.professor.submit') }}" method="POST" novalidate autocomplete="on">
				@csrf
				<div class="input-group float-stack">
					<input
						type="text"
						name="Prof_ID"
						id="Prof_ID"
						value="{{ old('Prof_ID') }}"
						placeholder=" "
						maxlength="9"
						required
						autocomplete="username"
						class="{{ $errors->has('Prof_ID') ? 'input-error' : '' }}"
					>
					<label for="Prof_ID">Professor ID</label>
				</div>
				<div class="input-group password-group float-stack">
					<input
						type="password"
						name="Password"
						id="prof-password"
						placeholder=" "
						required
						autocomplete="current-password"
						class="{{ $errors->has('Password') ? 'input-error' : '' }}"
					>
					<label for="prof-password">Password</label>
					<button type="button" class="toggle-password" data-target="prof-password" aria-label="Show password" aria-pressed="false"><i class='bx bx-hide'></i></button>
				</div>
				<div class="options-row" data-lock-until="{{ session('lock_until_prof') ?? '' }}">
					@php
						$messages = [];
						foreach (['login', 'Prof_ID', 'Password'] as $field) {
							if ($errors->has($field)) {
								$messages = array_merge($messages, $errors->get($field));
							}
						}
					@endphp
					@if (count($messages))
						<div class="login-error" role="alert" aria-live="assertive">
							@foreach ($messages as $idx => $msg)
								@if ($idx) <br> @endif {{ $msg }}
							@endforeach
						</div>
					@else
						<span class="login-error-placeholder"></span>
					@endif
					<label class="remember-inline">
						<input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
						<span>Remember me</span>
					</label>
				</div>
				<button type="submit" class="login-btn">Log In</button>
				<div class="below-actions">
					<a class="forgot-bottom" href="{{ route('forgotpassword') }}">Forgot Password?</a>
					<a class="back-to-main" href="{{ route('login') }}">Back to unified login</a>
				</div>
			</form>
		</section>
	</main>
	<script>
		document.addEventListener('DOMContentLoaded', () => {
			document.querySelectorAll('.toggle-password').forEach(btn => {
				btn.addEventListener('click', () => {
					const targetId = btn.getAttribute('data-target');
					const field = document.getElementById(targetId);
					if (!field) {
						return;
					}
					const showing = field.type === 'text';
					field.type = showing ? 'password' : 'text';
					btn.setAttribute('aria-pressed', showing ? 'false' : 'true');
					btn.innerHTML = showing ? "<i class='bx bx-hide'></i>" : "<i class='bx bx-show'></i>";
				});
			});
		});
	</script>
	<script src="{{ asset('js/errors-auto-dismiss.js') }}"></script>
	@include('partials.toast')
</body>
</html>