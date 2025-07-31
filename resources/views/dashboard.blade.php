<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Consultation Activity</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pikaday/css/pikaday.css">
  <style>
    #calendar {
      visibility: hidden;
    }
    .pika-prev, .pika-next {
      background-color: #12372a;
      border-radius: 50%;
      color: #12372a;
      border: 2px solid #12372a;
      font-size: 18px;
      padding: 10px;
      width: 35px !important;
      opacity: 100%;
    }
    .pika-table th:has([title="Saturday"]),
    .pika-table th:has([title="Tuesday"]),
    .pika-table th:has([title="Wednesday"]),
    .pika-table th:has([title="Thursday"]),
    .pika-table th:has([title="Friday"]) {
      background-color: #12372a;
      color: #fff;
      border-radius: 4px;
      padding: 5px;
      height: 60px !important;
    }
    .pika-table th:has([title="Monday"]),
    .pika-table th:has([title="Sunday"]) {
      background-color: #01703c;
      color: #fff;
      border-radius: 4px;
      padding: 10px;
    }
    .pika-single {
      display: block !important;
      border: none;
      min-height: 500px;   /* Set your desired height */
      height: 500px;
      max-height: 1000px;
      box-sizing: border-box;
      
    }
    .pika-table {
      border-radius: 3px;
      width: 100%;
      height: 100%;
      border-collapse: separate;
      border-spacing: 8px;
    }
    .pika-label {
      color: #12372a;
      font-size: 25px;
    }
    .pika-day {
      text-align: center;
    }
    .pika-lendar {
      height: 100%;
      width: 100%;
      display: flex;
      flex-direction: column;
    }
    .pika-button {
      background-color: #cac7c7;
      border-radius: 4px;
      color: #ffffff;
      padding: 10px;
      height: 50px;
      margin: 5px 0;
      pointer-events: none;
    }
    .pika-button:hover, .pika-row.pick-whole-week:hover .pika-button {
      color: #fff;
      background: #01703c;
      box-shadow: none;
      border-radius: 3px;
    }
    .is-selected .pika-button, .has-event .pika-button {
      color: #ffffff;
      background-color: #12372a !important;
      box-shadow: none;
    }
    .is-today .pika-button {
      color: #fff;
      background-color: #5fb9d4;
      font-weight: bold;
    }

    .has-booking {
      border-radius: 4px;
      position: relative;
      color:#fff;
      font-weight: bold
    }

    .calendar-box{
      height: 100%;
      width: 100%;
      max-width: 1000px;
   
    }

    /* Inbox Notification Styles */
    .inbox-notifications {
      background: #fff;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      height: 650px; /* Increased height */
      max-height: 650px; /* Increased max height */
      display: flex;
      flex-direction: column;
      overflow: hidden;
    }

    .inbox-header {
      padding: 20px;
      background: #12372a;
      color: white;
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-radius: 10px 10px 0 0;
    }

    .inbox-header h3 {
      margin: 0;
      font-size: 18px;
      font-weight: 600;
    }

    .inbox-actions {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .mark-all-btn {
      background: rgba(255, 255, 255, 0.2);
      border: none;
      color: white;
      padding: 8px;
      border-radius: 5px;
      cursor: pointer;
      transition: background 0.3s;
    }

    .mark-all-btn:hover {
      background: rgba(255, 255, 255, 0.3);
    }

    .unread-count {
      background: #ff4444;
      color: white;
      padding: 2px 8px;
      border-radius: 12px;
      font-size: 12px;
      font-weight: bold;
      min-width: 20px;
      text-align: center;
    }

    .inbox-content {
      flex: 1;
      overflow-y: auto; /* Make it scrollable */
      overflow-x: hidden; /* Hide horizontal scroll */
      padding: 0;
      max-height: calc(650px - 70px); /* Subtract header height, match new box height */
    }

    /* Custom scrollbar styling */
    .inbox-content::-webkit-scrollbar {
      width: 6px;
    }

    .inbox-content::-webkit-scrollbar-track {
      background: #f1f1f1;
      border-radius: 3px;
    }

    .inbox-content::-webkit-scrollbar-thumb {
      background: #c1c1c1;
      border-radius: 3px;
    }

    .inbox-content::-webkit-scrollbar-thumb:hover {
      background: #a8a8a8;
    }

    .loading-notifications {
      padding: 40px 20px;
      text-align: center;
      color: #666;
    }

    .loading-notifications i {
      font-size: 24px;
      margin-bottom: 10px;
    }

    .notification-item {
      padding: 15px 20px;
      border-bottom: 1px solid #eee;
      cursor: pointer;
      transition: background 0.3s;
      position: relative;
    }

    .notification-item:hover {
      background: #f8f9fa;
    }

    .notification-item.unread {
      background: #f0f8ff;
      border-left: 4px solid #12372a;
    }

    .notification-item.unread::before {
      content: '';
      position: absolute;
      left: 8px;
      top: 20px;
      width: 8px;
      height: 8px;
      background: #12372a;
      border-radius: 50%;
    }

    .notification-title {
      font-weight: 600;
      color: #333;
      margin-bottom: 5px;
      font-size: 14px;
    }

    .notification-message {
      color: #666;
      font-size: 13px;
      line-height: 1.4;
      margin-bottom: 8px;
    }

    .notification-time {
      color: #999;
      font-size: 12px;
    }

    .notification-type {
      position: absolute;
      right: 15px;
      top: 15px;
      padding: 2px 6px;
      border-radius: 10px;
      font-size: 10px;
      font-weight: bold;
      text-transform: uppercase;
    }

    .notification-type.accepted {
      background: #d4edda;
      color: #155724;
    }

    .notification-type.completed {
      background: #cce5ff;
      color: #0056b3;
    }

    .notification-type.rescheduled {
      background: #fff3cd;
      color: #856404;
    }

    .notification-type.cancelled {
      background: #f8d7da;
      color: #721c24;
    }

    .no-notifications {
      padding: 40px 20px;
      text-align: center;
      color: #666;
    }

    .no-notifications i {
      font-size: 48px;
      margin-bottom: 15px;
      color: #ddd;
    }

    /* Responsive design */
    @media (max-width: 768px) {
      .flex-layout {
        flex-direction: column;
        gap: 20px;
      }
      
      .calendar-box,
      .box {
        width: 100%;
      }
      
      .inbox-notifications {
        height: 450px; /* Slightly larger for mobile */
        max-height: 450px;
        min-height: 300px;
      }

      .inbox-content {
        max-height: calc(450px - 70px); /* Adjust for mobile */
      }
    }

  </style>
</head>
<body>
  @include('components.navbar')

  <div class="main-content">
    <div class="header">
      <h1>Consultation Activity</h1>
    </div>
    <div class="flex-layout">
      <div class="calendar-box">
        <div class="calendar-wrapper-container">
          <input id="calendar" type="text" placeholder="Select Date" name="booking_date" required>
        </div>
        <div class="legend">
          <div><span class="legend-box pending"></span> Pending</div>
          <div><span class="legend-box approved"></span> Approved</div>
          <div><span class="legend-box completed"></span> Completed</div>
          <div><span class="legend-box rescheduled"></span> Rescheduled</div>
        </div>
      </div>
      <div class="box">
        <div class="inbox-notifications">
          <div class="inbox-header">
            <h3>Notifications</h3>
            <div class="inbox-actions">
              <button id="mark-all-read" class="mark-all-btn" title="Mark all as read">
                <i class='bx bx-check-double'></i>
              </button>
              <span id="unread-count" class="unread-count">0</span>
            </div>
          </div>
          <div class="inbox-content" id="inbox-content">
            <div class="loading-notifications">
              <i class='bx bx-loader-alt bx-spin'></i>
              <span>Loading notifications...</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <button class="chat-button" onclick="toggleChat()">
      <i class='bx bxs-message-rounded-dots'></i>
      Click to chat with me!
    </button>

    <div class="chat-overlay" id="chatOverlay">
      <div class="chat-header">
        <span>AI Chat Assistant</span>
        <button class="close-btn" onclick="toggleChat()">×</button>
      </div>
      <div class="chat-body" id="chatBody">
        <div class="message bot">Hi! How can I help you today?</div>
        <div id="chatBox"></div>
      </div>

      <form id="chatForm">
        <input type="text" id="message" placeholder="Type your message" required>
        <button type="submit">Send</button>
      </form>
    </div>
  </div>

  <script src="{{ asset('js/dashboard.js') }}"></script>
  <script src="https://cdn.jsdelivr.net/npm/pikaday/pikaday.js"></script>
  <script>
    
   const bookingMap = new Map();
  fetch('/api/consul')
    .then(response => response.json())
    .then(data => {
      data.forEach(entry => {
        const date = new Date(entry.Booking_Date);
        bookingMap.set(date.toDateString(), entry.Status.toLowerCase());
      });

      // Initialize Pikaday AFTER data is loaded
      const picker = new Pikaday({
        field: document.getElementById('calendar'),
        format: 'ddd, MMM DD YYYY',
        showDaysInNextAndPreviousMonths: true,
        firstDay: 1,
        bound: false,
        onDraw: function() {
          const cells = document.querySelectorAll('.pika-button');
          cells.forEach(cell => {
            const day = cell.getAttribute('data-pika-day');
            const month = cell.getAttribute('data-pika-month');
            const year = cell.getAttribute('data-pika-year');
            if (day && month && year) {
              const cellDate = new Date(year, month, day);
              const key = cellDate.toDateString();
              if (bookingMap.has(key)) {
                const status = bookingMap.get(key);
                const classMap = {
                  pending: 'status-pending',
                  approved: 'status-approved',
                  completed: 'status-completed',
                  rescheduled: 'status-rescheduled'
                };
                cell.classList.add('has-booking');
                cell.classList.add(classMap[status]);
                cell.setAttribute('data-status', status);
              }
            }
          });
        }
      });
      picker.show();
      picker.draw();
    })
    .catch((err) => console.log(err));
        
    // Initialize inbox notifications
    loadNotifications();
    
    // Load notifications every 30 seconds
    setInterval(loadNotifications, 30000);
    
    // Mark all as read functionality
    document.getElementById('mark-all-read').addEventListener('click', function() {
      markAllNotificationsAsRead();
    });
    
    function loadNotifications() {
      fetch('/api/notifications')
        .then(response => response.json())
        .then(data => {
          displayNotifications(data.notifications);
          updateUnreadCount();
        })
        .catch(error => {
          console.error('Error loading notifications:', error);
          document.getElementById('inbox-content').innerHTML = 
            '<div class="no-notifications"><i class="bx bx-error"></i><p>Error loading notifications</p></div>';
        });
    }
    
    function displayNotifications(notifications) {
      const inboxContent = document.getElementById('inbox-content');
      
      if (notifications.length === 0) {
        inboxContent.innerHTML = `
          <div class="no-notifications">
            <i class='bx bx-bell-off'></i>
            <p>No notifications yet</p>
          </div>
        `;
        return;
      }
      
      inboxContent.innerHTML = notifications.map(notification => `
        <div class="notification-item ${notification.is_read ? '' : 'unread'}" 
             onclick="markNotificationAsRead(${notification.id})">
          <div class="notification-type ${notification.type}">${notification.type}</div>
          <div class="notification-title">${notification.title}</div>
          <div class="notification-message">${notification.message}</div>
          <div class="notification-time">${formatNotificationTime(notification.created_at)}</div>
        </div>
      `).join('');
    }
    
    function updateUnreadCount() {
      fetch('/api/notifications/unread-count')
        .then(response => response.json())
        .then(data => {
          const countElement = document.getElementById('unread-count');
          countElement.textContent = data.count;
          countElement.style.display = data.count > 0 ? 'inline-block' : 'none';
        })
        .catch(error => console.error('Error updating unread count:', error));
    }
    
    function markNotificationAsRead(notificationId) {
      fetch('/api/notifications/mark-read', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ notification_id: notificationId })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          loadNotifications();
        }
      })
      .catch(error => console.error('Error marking notification as read:', error));
    }
    
    function markAllNotificationsAsRead() {
      fetch('/api/notifications/mark-all-read', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          loadNotifications();
        }
      })
      .catch(error => console.error('Error marking all notifications as read:', error));
    }
    
    function formatNotificationTime(timestamp) {
      const date = new Date(timestamp);
      const now = new Date();
      const diffInMinutes = Math.floor((now - date) / (1000 * 60));
      
      if (diffInMinutes < 1) {
        return 'Just now';
      } else if (diffInMinutes < 60) {
        return `${diffInMinutes}m ago`;
      } else if (diffInMinutes < 1440) {
        const hours = Math.floor(diffInMinutes / 60);
        return `${hours}h ago`;
      } else {
        const days = Math.floor(diffInMinutes / 1440);
        return `${days}d ago`;
      }
    }
        
    
  </script>
</body>
</html>
