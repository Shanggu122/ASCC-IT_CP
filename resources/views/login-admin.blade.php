<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Admin Login</title>
	<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
	<link rel="stylesheet" href="{{ asset('css/login.css') }}">
	<link rel="stylesheet" href="{{ asset('css/toast.css') }}">
</head>
<body class="admin-login">
	<main class="auth-wrapper">
		<section class="auth-card">
			<header class="auth-header">
				<h1>Admin Portal</h1>
				<p>Use your administrator credentials to access analytics and management tools.</p>
			</header>
			<form id="admin-login-form" action="{{ route('login.admin.submit') }}" method="POST" novalidate autocomplete="on">
				@csrf
				<div class="input-group float-stack">
					<input
						type="text"
						name="Admin_ID"
						id="Admin_ID"
						value="{{ old('Admin_ID') }}"
						placeholder=" "
						maxlength="9"
						required
						autocomplete="username"
						class="{{ $errors->has('Admin_ID') ? 'input-error' : '' }}"
					>
					<label for="Admin_ID">Admin ID</label>
				</div>
				<div class="input-group password-group float-stack">
					<input
						type="password"
						name="Password"
						id="admin-password"
						placeholder=" "
						required
						autocomplete="current-password"
						class="{{ $errors->has('Password') ? 'input-error' : '' }}"
					>
					<label for="admin-password">Password</label>
					<button type="button" class="toggle-password" data-target="admin-password" aria-label="Show password" aria-pressed="false"><i class='bx bx-hide'></i></button>
				</div>
				<div class="options-row" data-lock-until="{{ session('lock_until_admin') ?? '' }}">
					@php
						$messages = [];
						foreach (['login', 'Admin_ID', 'Password'] as $field) {
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