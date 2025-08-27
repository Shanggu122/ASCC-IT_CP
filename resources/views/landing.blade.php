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
        <!-- Logo with stars decoration -->
        <div class="logo-section">
            <div class="star star-1">✦</div>
            <div class="star star-2">✦</div>
            <img src="{{ asset('images/CCIT_logo2.png') }}" alt="ASCC-IT Logo" class="logo">
            <div class="star star-3">✦</div>
        </div>
        
        <h1>Welcome to the ASCC-IT Portal</h1>
        <p class="subtitle">Select your role to log in</p>
        
        <div class="login-cards">
            <div class="login-card">
                <div class="card-icon">
                    <i class='bx bxs-graduation'></i>
                </div>
                <h3>Student</h3>
                <p>Login as student</p>
                <a href="{{ route('login') }}" class="login-btn">
                    Login <i class='bx bx-right-arrow-alt'></i>
                </a>
            </div>
            
            <div class="login-card">
                <div class="card-icon">
                    <i class='bx bxs-user-voice'></i>
                </div>
                <h3>Professor</h3>
                <p>Login as professor</p>
                <a href="{{ route('login.professor') }}" class="login-btn">
                    Login <i class='bx bx-right-arrow-alt'></i>
                </a>
            </div>
            
            <div class="login-card">
                <div class="card-icon">
                    <i class='bx bxs-user-account'></i>
                </div>
                <h3>Admin</h3>
                <p>Login as admin</p>
                <a href="{{ route('login.admin') }}" class="login-btn">
                    Login <i class='bx bx-right-arrow-alt'></i>
                </a>
            </div>
        </div>
    </div>
</body>
</html>