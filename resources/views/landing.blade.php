<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome | ASCC-IT Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}">
</head>
<body>
    <div class="landing-container">
        <img src="{{ asset('images/CCIT_logo2.png') }}" alt="ASCC-IT Logo" class="logo">
        <h1>Welcome to ASCC-IT Portal</h1>
        <div class="choose-text">Please select your login type:</div>
        <div class="login-options">
            <a href="{{ route('login') }}" class="login-card">
                <i class='bx bxs-user'></i>
                <span>Student Login</span>
            </a>
            <a href="{{ route('login.professor') }}" class="login-card">
                <i class='bx bxs-user-voice'></i>
                <span>Professor Login</span>
            </a>
            <a href="{{ route('login.admin') }}" class="login-card">
                <i class="bx bxs-user-circle"></i>
                <span>Admin Login</span>
            </a>
        </div>
    </div>
</body>
</html>