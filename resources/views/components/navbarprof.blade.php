<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sidebar</title>
    <link rel="stylesheet" href="{{ asset('css/navbar.css') }}">
</head>
<body>
    <!-- Hamburger button for mobile -->
    <button class="hamburger" id="hamburger">&#9776;</button>
    <div class="sidebar">
        <img src="{{ asset('images/CCIT_logo2.png') }}" alt="Logo">
         <ul>
          <li><a href="{{ url('/dashboard-professor') }}">Dashboard</a></li>
          <li><a href="{{ url('/comsci-professor') }}">Computer Science</a></li>
          <li><a href="{{ url('/itis-professor') }}">IT & IS</a></li>
          <li><a href="{{ url('/profile-professor') }}">Profile</a></li>
          <li><a href="{{ url('/conlog-professor') }}">Consultation Log</a></li>
          <li><a href="{{ url('/messages-professor') }}">Messages</a></li>
          <li><a href="{{ url('/logout-professor') }}">Sign Out</a></li>
        </ul>
      </div>
    <script>
        // Only run if hamburger exists (mobile)
        const hamburger = document.getElementById('hamburger');
        const sidebar = document.getElementById('sidebar');
        if (hamburger) {
            hamburger.addEventListener('click', () => {
                sidebar.classList.toggle('active');
            });
        }
    </script>
</body>
</html>