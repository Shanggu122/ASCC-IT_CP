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
  <link rel="stylesheet" href="{{ asset('css/notifications.css') }}">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pikaday/css/pikaday.css">
  <style>
    #calendar {
      visibility: hidden;
    }
    /* Unified arrow styling (matches consultation form) */
    .pika-prev, .pika-next {
      background-color: #0d2b20; /* dark fill */
      border-radius: 50%;
      color: #ffffff;
      border: 2px solid #071a13; /* darker rim */
      font-size: 18px;
      padding: 10px;
      width: 38px !important;
      height: 38px;
      display:flex; align-items:center; justify-content:center;
      opacity:1;
      text-indent:-9999px; /* hide default */
      position:relative;
      overflow:hidden;
      background-image:none !important;
      box-shadow:none;
    }
    .pika-prev:after, .pika-next:after {
      content:'';
      position:absolute;
      top:46%;
      left:50%;
      transform:translate(-50%, -50%);
      font-size:24px;
      line-height:1;
      font-weight:700;
      color:#ffffff;
      text-indent:0;
      z-index:2;
    }
    .pika-prev:after { content:'\2039'; }
    .pika-next:after { content:'\203A'; }
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
      /* Make height adaptive instead of fixed 500px */
      min-height: 420px;
      height: auto;
      max-height: 70vh;
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
      padding: 6px;
      height: 48px;
      margin: 4px 0;
      pointer-events: none;
      font-size: clamp(0.7rem, 2vw, 1rem);
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
    }
      }
    }

  </style>
</head>
<body>
  @include('components.navbar')

  <div class="main-content">
    <div class="header">
      <h1>Consultation Activity</h1> <!-- Changed to a more descriptive title -->
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
      <div id="quickReplies" class="quick-replies">
        <button type="button" class="quick-reply" data-message="How do I book a consultation?">How do I book?</button>
        <button type="button" class="quick-reply" data-message="What are the consultation statuses?">Statuses?</button>
        <button type="button" class="quick-reply" data-message="How can I reschedule my consultation?">Reschedule</button>
        <button type="button" class="quick-reply" data-message="Can I cancel my booking?">Cancel booking</button>
        <button type="button" class="quick-reply" data-message="When is my next consultation?">Next consultation</button>
        <button type="button" class="quick-reply" data-message="How do I contact my professor after booking?">Contact professor</button>
      </div>
      <button type="button" id="quickRepliesToggle" class="quick-replies-toggle" style="display:none" title="Show FAQs">
        <i class='bx bx-help-circle'></i>
      </button>

      <form id="chatForm">
        <input type="text" id="message" placeholder="Type your message" required>
        <button type="submit">Send</button>
      </form>
    </div>
  </div>

  <script src="{{ asset('js/dashboard.js') }}"></script>
  <script src="https://cdn.jsdelivr.net/npm/pikaday/pikaday.js"></script>
  <script>
  // Mid-width (tablet/small desktop) notification panel toggle
  (function(){
    const notifPanelSelector = '.inbox-notifications';
    function applyNotifMode(){
      const w = window.innerWidth;
      const panel = document.querySelector(notifPanelSelector);
      const bell = document.getElementById('mobileNotificationBell');
      if(!panel) return;
    if(w <= 1450 && w >= 769){
        panel.style.display = 'none';
        if(bell){ bell.style.display = 'block'; bell.style.opacity = '1'; }
    } else if (w >= 1451) {
        panel.style.display = '';
        if(bell){ bell.style.display = 'none'; }
      } else { // real mobile keeps existing mobile styles
        if(bell){ bell.style.display = 'block'; }
      }
    }
    window.addEventListener('resize', applyNotifMode);
    document.addEventListener('DOMContentLoaded', applyNotifMode);
  })();
    
   const bookingMap = new Map();
  
  function loadBookingData() {
    fetch('/api/consul')
      .then(response => response.json())
      .then(data => {
        // Store previous booking map for comparison
        const previousBookings = new Map(bookingMap);
        bookingMap.clear(); // Clear existing data
        
        data.forEach(entry => {
          const date = new Date(entry.Booking_Date);
          bookingMap.set(date.toDateString(), entry.Status.toLowerCase());
        });
        
        // Only update calendar if there are actual changes
        let hasChanges = false;
        
        // Check for new or changed bookings
        for (const [dateStr, status] of bookingMap) {
          if (!previousBookings.has(dateStr) || previousBookings.get(dateStr) !== status) {
            hasChanges = true;
            break;
          }
        }
        
        // Check for removed bookings
        if (!hasChanges) {
          for (const [dateStr] of previousBookings) {
            if (!bookingMap.has(dateStr)) {
              hasChanges = true;
              break;
            }
          }
        }
        
        // Only update calendar cells if there are changes
        if (hasChanges && window.picker) {
          const cells = document.querySelectorAll('.pika-button');
          cells.forEach(cell => {
            const cellDate = new Date(cell.getAttribute('data-pika-year'), cell.getAttribute('data-pika-month'), cell.getAttribute('data-pika-day'));
            const dateStr = cellDate.toDateString();
            const status = bookingMap.get(dateStr);
            const previousStatus = previousBookings.get(dateStr);
            
            // Only update if status changed for this specific date
            if (status !== previousStatus) {
              // Remove existing status classes
              cell.classList.remove('status-pending', 'status-approved', 'status-completed', 'status-rescheduled');
              
              if (status) {
                cell.classList.add(`status-${status}`);
              }
            }
          });
        }
      })
      .catch((err) => {
        // Error loading booking data
      });
  }
  
  // Initial load
  loadBookingData();

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
        // Remove existing status classes
        cell.classList.remove('has-booking', 'status-pending', 'status-approved', 'status-completed', 'status-rescheduled');
        cell.removeAttribute('data-status');
        
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
  
  // Store picker globally for refresh
  window.picker = picker;
  picker.show();
  picker.draw();
  
  // Real-time refresh booking data every 3 seconds (reduced for smoother updates)
  setInterval(loadBookingData, 3000);
        
    // Initialize inbox notifications
    loadNotifications();
    
    // Real-time load notifications every 3 seconds (reduced for smoother updates)
    setInterval(loadNotifications, 3000);
    
    // Mark all as read functionality
    document.getElementById('mark-all-read').addEventListener('click', function() {
      markAllNotificationsAsRead();
    });
    
    let lastNotificationHash = '';
    
    function loadNotifications() {
      fetch('/api/notifications')
        .then(response => response.json())
        .then(data => {
          // Create a simple hash of the notifications to detect changes
          const notificationHash = JSON.stringify(data.notifications.map(n => ({id: n.id, is_read: n.is_read, message: n.message})));
          
          // Only update if notifications actually changed
          if (notificationHash !== lastNotificationHash) {
            displayNotifications(data.notifications);
            updateUnreadCount();
            lastNotificationHash = notificationHash;
          }
        })
        .catch(error => {
          document.getElementById('inbox-content').innerHTML = 
            '<div class="no-notifications"><i class="bx bx-error"></i><p>Error loading notifications</p></div>';
        });
    }
    
    function displayNotifications(notifications) {
      const inboxContent = document.getElementById('inbox-content');
      const mobileContainer = document.getElementById('mobileNotificationsContainer');
      
      if (notifications.length === 0) {
        const noNotificationsHtml = `
          <div class="no-notifications">
            <i class='bx bx-bell-off'></i>
            <p>No notifications yet</p>
          </div>
        `;
        inboxContent.innerHTML = noNotificationsHtml;
        if (mobileContainer) {
          mobileContainer.innerHTML = noNotificationsHtml;
        }
        return;
      }
      
      const notificationsHtml = notifications.map(notification => {
        // Use generic "Consultation" title to avoid redundancy with the status badge
        const cleanTitle = notification.title.includes('Consultation') ? 'Consultation' : notification.title;
        
        return `
          <div class="notification-item ${notification.is_read ? '' : 'unread'}" 
               onclick="markNotificationAsRead(${notification.id})">
            <div class="notification-type ${notification.type}">${notification.type}</div>
            <div class="notification-title">${cleanTitle}</div>
            <div class="notification-message">${notification.message}</div>
            <div class="notification-time">${formatNotificationTime(notification.created_at)}</div>
          </div>
        `;
      }).join('');
      
      inboxContent.innerHTML = notificationsHtml;
      if (mobileContainer) {
        mobileContainer.innerHTML = notificationsHtml;
      }
    }
    
    function updateUnreadCount() {
      fetch('/api/notifications/unread-count')
        .then(response => response.json())
        .then(data => {
          const countElement = document.getElementById('unread-count');
          const mobileCountElement = document.getElementById('mobileNotificationBadge');
          
          // Update desktop notification count
          countElement.textContent = data.count;
          countElement.style.display = data.count > 0 ? 'inline-block' : 'none';
          
          // Update mobile notification badge
          if (mobileCountElement) {
            mobileCountElement.textContent = data.count;
            mobileCountElement.style.display = data.count > 0 ? 'flex' : 'none';
          }
        })
        .catch(error => {
          // Error updating unread count
        });
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
          // Reset hash to force notification update
          notificationsHash = '';
          loadNotifications();
        }
      })
      .catch(error => {
        // Error marking notification as read
      });
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
          // Reset hash to force notification update
          notificationsHash = '';
          loadNotifications();
        }
      })
      .catch(error => {
        // Error marking all notifications as read
      });
    }
    
    function formatNotificationTime(timestamp) {
      const date = new Date(timestamp);
      const now = new Date();
      const diffInSeconds = Math.floor((now - date) / 1000);
      if (diffInSeconds < 60) return 'Just now';
      if (diffInSeconds < 3600) {
        const m = Math.floor(diffInSeconds / 60);
        return `${m} ${m === 1 ? 'min' : 'mins'} ago`;
      }
      if (diffInSeconds < 86400) {
        const h = Math.floor(diffInSeconds / 3600);
        return `${h === 1 ? '1 hr' : h + ' hrs'} ago`;
      }
      const d = Math.floor(diffInSeconds / 86400);
      return `${d} ${d === 1 ? 'day' : 'days'} ago`;
    }
        
    
  </script>
</body>
</html>
