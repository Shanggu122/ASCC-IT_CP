<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sidebar</title>
    <link rel="stylesheet" href="{{ asset('css/navbar.css') }}">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <!-- Mobile header with hamburger and bell icon -->
    <div class="mobile-header">
        <button class="hamburger" id="hamburger">&#9776;</button>
        <button class="mobile-notification-bell" id="mobileNotificationBell" onclick="toggleMobileNotifications()">
            <i class='bx bx-bell'></i>
            <span class="mobile-notification-badge" id="mobileNotificationBadge" style="display: none;">0</span>
        </button>
    </div>
    
    <div class="sidebar" id="sidebar">
        <img src="{{ asset('images/CCIT_logo2.png') }}" alt="Logo">
        
        <!-- Simple role indicator -->
        <div class="simple-role-indicator">
            <span class="role-line professor-line"></span>
            <span class="role-label">Professor Portal</span>
        </div>
        
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

    <!-- Mobile Notifications Dropdown -->
    <div class="mobile-notifications-dropdown" id="mobileNotificationDropdown">
        <div class="mobile-notifications-header">
            <h3>Notifications</h3>
            <button class="close-mobile-notifications" onclick="toggleMobileNotifications()">×</button>
        </div>
        <div class="mobile-notifications-content" id="mobileNotificationsContainer">
            <div class="loading-notifications">
                <i class='bx bx-loader-alt bx-spin'></i>
                <p>Loading notifications...</p>
            </div>
        </div>
        <div class="mobile-notifications-footer">
            <button class="mark-all-read-mobile" onclick="markAllNotificationsAsRead()">Mark All as Read</button>
        </div>
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

        // Mobile notifications toggle
        function toggleMobileNotifications() {
            const dropdown = document.getElementById('mobileNotificationDropdown');
            if (dropdown && dropdown.classList) {
                dropdown.classList.toggle('active');
                
                // Close sidebar if open
                if (sidebar && sidebar.classList && sidebar.classList.contains('active')) {
                    sidebar.classList.remove('active');
                    hamburger.classList.remove('active');
                }
            }
        }

        // Close mobile notifications when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('mobileNotificationDropdown');
            const bell = document.getElementById('mobileNotificationBell');
            
            if (dropdown && dropdown.classList && bell && !dropdown.contains(event.target) && !bell.contains(event.target)) {
                dropdown.classList.remove('active');
            }
        });
    </script>
</body>
</html>