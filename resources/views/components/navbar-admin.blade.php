<!-- filepath: resources/views/components/navbar-admin.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Sidebar</title>
    <link rel="stylesheet" href="{{ asset('css/admin-navbar.css') }}">
</head>
<body>
    <!-- Hamburger button for mobile -->
    <button class="hamburger" id="hamburger">&#9776;</button>
    <div class="sidebar" id="sidebar">
        <img src="{{ asset('images/CCIT_logo2.png') }}" alt="Logo">
        <ul>
            <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li><a href="{{ url('/admin-comsci') }}">Computer Science</a></li>
            <li><a href="{{ url('/admin-itis') }}">IT & IS</a></li>
            <li><a href="{{ url('/admin-analytics') }}">Analytics</a></li>
            <li><a href="{{ url('logout.admin') }}">Sign Out</a></li>
        </ul>
    </div>
    <script>
        // Only run if hamburger exists (mobile)
        const hamburger = document.getElementById('hamburger');
        const sidebar = document.getElementById('sidebar');
        if (hamburger) {
            hamburger.addEventListener('click', () => {
                sidebar.classList.toggle('active');
                hamburger.classList.toggle('active');
            });
        }
    </script>
</body>
</html>