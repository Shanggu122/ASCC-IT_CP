<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Consultation Activity</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/legend.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard-professor.css') }}">
  <link rel="stylesheet" href="{{ asset('css/notifications.css') }}">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pikaday/css/pikaday.css">
  <style>
     #calendar {
      visibility: hidden;
    }
    /* Override badges (holiday/suspended/force) */
    .ov-badge {
      /* match admin placement: bottom-left inside cell */
      position: absolute;
      left: 6px;
      bottom: 6px;
      font-size: 10px;
      line-height: 1;
      padding: 3px 6px;
      border-radius: 8px;
      color: #ffffff;
      pointer-events: none;
      white-space: nowrap;
      max-width: calc(100% - 12px);
      overflow: hidden;
      text-overflow: ellipsis;
      z-index: 2;
    }
  /* Override badges (palette aligned with admin) */
  .ov-holiday { background-color: #9B59B6; }   /* Holiday → Violet */
  .ov-blocked { background-color: #374151; }   /* Suspended → Dark Gray */
  .ov-force { background-color: #2563eb; }     /* Forced Online → Blue */
  .ov-online { background-color: #FF69B4; }    /* Online Day → Pink */
  .ov-leave  { background-color: #0ea5a4; }    /* Leave Day → Teal/Cyan */
  .ov-endyear{ background-color: #6366f1; }    /* End of School Year → Indigo */
    /* Whole-cell background for overrides */
  .day-holiday { background: rgba(155, 89, 182, 0.55) !important; } /* Violet */
    .day-blocked { background: rgba(55, 65, 81, 0.75) !important; }   /* Suspended */
  .day-force   { background: rgba(37, 99, 235, 0.6) !important; }   /* Forced Online */
  .day-online  { background: rgba(255, 105, 180, 0.45) !important; }/* Online Day */
  .day-leave   { background: rgba(14, 165, 164, 0.55) !important; } /* Leave Day */
  .day-endyear { background: rgba(99, 102, 241, 0.6) !important; }   /* End of School Year → Indigo */
    /* Unified arrow styling */
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
      text-indent:-9999px;
      position:relative;
      overflow:hidden;
      background-image:none !important;
      box-shadow:none;
    }
    .pika-prev:after, .pika-next:after {
      content:'';
      position:absolute;
      top:46%; left:50%;
      transform:translate(-50%, -50%);
      font-size:24px; line-height:1; font-weight:700; color:#ffffff; text-indent:0; z-index:2;
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
      color: #ffffff !important;
      padding: 10px;
      height: 50px;
      margin: 5px 0;
      pointer-events: auto; /* allow hover/tooltip */
      cursor: default; /* match admin: not a hand cursor by default */
      user-select: none; /* Prevent text selection */
    }
    .pika-button:hover,
    .pika-row.pick-whole-week:hover .pika-button,
    .pika-button:focus,
    .pika-button:active {
      /* Keep neutral look like admin; tinted states win due to !important */
      color: #fff !important;
      background: #cac7c7;
      box-shadow: none !important;
      outline: none !important;
      border-radius: 3px;
    }

  /* Tinted states keep their colors on hover due to !important */
  .pika-button.day-online:hover,
  .pika-button.day-online:focus,
  .pika-button.day-online:active { background: rgba(255, 105, 180, 0.45) !important; color:#fff !important; }
  .pika-button.day-force:hover,
  .pika-button.day-force:focus,
  .pika-button.day-force:active { background: rgba(37, 99, 235, 0.6) !important; color:#fff !important; }
  .pika-button.day-holiday:hover,
  .pika-button.day-holiday:focus,
  .pika-button.day-holiday:active { background: rgba(155, 89, 182, 0.55) !important; color:#fff !important; }
  .pika-button.day-blocked:hover,
  .pika-button.day-blocked:focus,
  .pika-button.day-blocked:active { background: rgba(55, 65, 81, 0.75) !important; color:#fff !important; }
  .pika-button.day-leave:hover,
  .pika-button.day-leave:focus,
  .pika-button.day-leave:active { background: rgba(14, 165, 164, 0.55) !important; color:#fff !important; }
  .pika-button.day-endyear:hover,
  .pika-button.day-endyear:focus,
  .pika-button.day-endyear:active { background: rgba(99, 102, 241, 0.6) !important; color:#fff !important; }
  .is-today .pika-button:hover,
  .is-today .pika-button:focus,
  .is-today .pika-button:active { background:#5fb9d4 !important; color:#fff !important; }
    
    /* Enhanced hover states for consultation cells */
    .pika-button.has-booking {
      cursor: pointer !important;
    }
    
    .pika-button.has-booking:hover {
      transform: scale(1.05);
      transition: transform 0.2s ease;
      box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    }
    .is-selected .pika-button, .has-event .pika-button {
      color: #ffffff;
      background-color: #cac7c7 !important;
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
      
      /* Mobile-specific calendar button styling */
      .pika-button {
        pointer-events: auto !important; /* allow tapping for leave toggle */
        cursor: pointer !important;
        user-select: none !important; /* Prevent text selection */
        -webkit-touch-callout: none !important; /* Disable iOS callout */
        -webkit-user-select: none !important; /* Disable text selection on Safari */
        -moz-user-select: none !important; /* Disable text selection on Firefox */
        -ms-user-select: none !important; /* Disable text selection on IE/Edge */
        touch-action: manipulation !important; /* Disable double-tap zoom */
      }
      
  /* Match admin: no special pointer and neutral hover on mobile */
  .pika-button { cursor: default; }
  .pika-button:hover { background:#cac7c7 !important; transform: none !important; }
  .pika-button:active,
  .pika-button:focus { background:#cac7c7 !important; outline: none !important; }
    }

    /* Consultation Tooltip Styles */
    #consultationTooltip {
      font-family: 'Poppins', sans-serif;
      background: #fff;
      border: 1px solid #e1e5e9;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
      z-index: 10000;
      max-width: 320px;
      max-height: 400px;
      overflow-y: auto;
      line-height: 1.4;
      scrollbar-width: thin;
      scrollbar-color: #ccc #f9f9f9;
    }

    /* Custom scrollbar for webkit browsers */
    #consultationTooltip::-webkit-scrollbar {
      width: 6px;
    }

    #consultationTooltip::-webkit-scrollbar-track {
      background: #f9f9f9;
      border-radius: 3px;
    }

    #consultationTooltip::-webkit-scrollbar-thumb {
      background: #ccc;
      border-radius: 3px;
    }

    #consultationTooltip::-webkit-scrollbar-thumb:hover {
      background: #999;
    }

    #consultationTooltip .consultation-entry {
      margin-bottom: 8px;
      padding-bottom: 8px;
      border-bottom: 1px solid #eee;
    }

    #consultationTooltip .consultation-entry:last-child {
      margin-bottom: 0;
      padding-bottom: 0;
      border-bottom: none;
    }

    #consultationTooltip .student-name {
      font-weight: 600;
      color: #2c5f4f;
      margin-bottom: 4px;
      font-size: 14px;
    }

    #consultationTooltip .detail-row {
      font-size: 12px;
      color: #666;
      margin-bottom: 2px;
    }

    #consultationTooltip .status-row {
      font-size: 12px;
      font-weight: 600;
      margin-bottom: 2px;
    }

    #consultationTooltip .booking-time {
      font-size: 11px;
      color: #999;
      font-style: italic;
    }

    /* Legend styles are centralized in public/css/legend.css */
    /* Position the legend button just to the right of the left sidebar (desktop only) */
    @media (min-width: 951px) {
      .legend-toggle { left: calc(220px + 20px) !important; }
      .legend-panel { left: calc(220px + 24px) !important; }
    }

    /* Themed confirm + toast (minimal styles to ensure visibility) */
    .ascc-confirm-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.45); z-index: 10000; display:flex; align-items:center; justify-content:center; padding:16px; }
    .ascc-confirm { background:#ffffff; border-radius:10px; width: min(92vw, 420px); box-shadow: 0 12px 40px rgba(0,0,0,0.25); overflow:hidden; font-family:'Poppins', sans-serif; }
    .ascc-confirm-header { display:flex; align-items:center; justify-content:space-between; padding:14px 16px; background:#0d2b20; color:#fff; }
    .ascc-confirm-title { font-weight:600; font-size:16px; }
    .ascc-confirm-close { background:transparent; border:none; color:#fff; font-size:20px; line-height:1; cursor:pointer; }
    .ascc-confirm-body { padding:16px; color:#12372a; }
    .ascc-confirm-actions { display:flex; gap:10px; justify-content:flex-end; padding:12px 16px 16px; }
    .ascc-btn { border:none; border-radius:8px; padding:10px 14px; font-weight:600; cursor:pointer; }
    .ascc-btn-primary { background:#0d2b20; color:#fff; }
    .ascc-btn-secondary { background:#e9ecef; color:#0d2b20; }
    .toast-wrapper { position: fixed; top: 16px; right: 16px; display:flex; flex-direction:column; gap:10px; z-index:10001; }
    .ascc-toast { background:#fff; color:#12372a; border-left:4px solid #0d2b20; border-radius:8px; padding:10px 12px; box-shadow:0 6px 20px rgba(0,0,0,0.15); display:flex; align-items:center; gap:10px; }
    .ascc-toast-success { border-left-color:#28a745; }
    .ascc-toast-error { border-left-color:#dc3545; }
    .ascc-toast-info { border-left-color:#0d2b20; }
    .ascc-toast-close { background:transparent; border:none; font-size:16px; margin-left:6px; cursor:pointer; color:#666; }
    .ascc-toast.hide { opacity:0; transform: translateY(-6px); transition: opacity .2s ease, transform .2s ease; }
    /* Local swatch color for Leave Day if not present in shared legend.css */
    .legend-swatch.swatch-leave { background-color: #0ea5a4; }
  </style>
</head>
<body>
  @include('components.navbarprof')
  <div class="main-content">
    <div class="header">
      <h1>Consultation Activity</h1>
    </div>
      <div class="calendar-box">
        <div class="calendar-wrapper-container">
          <input id="calendar" type="text" placeholder="Select Date" name="booking_date" required>
        </div>
        <!-- Collapsible legend (bottom-left FAB to avoid chatbot at bottom-right) -->
        <button id="legendToggle" class="legend-toggle" aria-haspopup="dialog" aria-controls="legendBackdrop" aria-label="View Legend" title="View Legend">
          <!-- white info icon (SVG) for consistent color control -->
          <svg viewBox="0 0 24 24" aria-hidden="true">
              try{ lsSetOv(lsKey, incoming, 6*3600*1000); }catch(_){ }
            <path d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2zm0 4.75a1.25 1.25 0 1 1 0 2.5 1.25 1.25 0 0 1 0-2.5zM13 18h-2v-7h2v7z"/>
          </svg>
        </button>
        <div id="legendBackdrop" class="legend-backdrop" aria-hidden="true">
          <div class="legend-panel" role="dialog" aria-modal="true" aria-labelledby="legendTitle">
            <div class="legend-header">
              <h3 id="legendTitle">Legend</h3>
              <button id="legendClose" class="legend-close" aria-label="Close">✖</button>
            </div>
            <div class="legend-content">
              <div class="legend-section">
                <div class="legend-section-title">Consultation Status</div>
                <div class="legend-grid">
                  <div class="legend-item"><span class="legend-swatch swatch-pending"></span>Pending <i class='bx bx-time legend-icon' aria-hidden="true"></i></div>
                  <div class="legend-item"><span class="legend-swatch swatch-approved"></span>Approved <i class='bx bx-check-circle legend-icon' aria-hidden="true"></i></div>
                  <div class="legend-item"><span class="legend-swatch swatch-completed"></span>Completed <i class='bx bx-badge-check legend-icon' aria-hidden="true"></i></div>
                  <div class="legend-item"><span class="legend-swatch swatch-rescheduled"></span>Rescheduled <i class='bx bx-calendar-edit legend-icon' aria-hidden="true"></i></div>
                  <div class="legend-item"><span class="legend-swatch swatch-suspended"></span>Suspended <i class='bx bx-block legend-icon' aria-hidden="true"></i></div>
                </div>
              </div>
              <div class="legend-section">
                <div class="legend-section-title">Day Types</div>
                <div class="legend-grid">
                  <div class="legend-item"><span class="legend-swatch swatch-online"></span>Online Day <i class='bx bx-video legend-icon' aria-hidden="true"></i></div>
                  <div class="legend-item"><span class="legend-swatch swatch-forced"></span>Forced Online <i class='bx bx-switch legend-icon' aria-hidden="true"></i></div>
                  <div class="legend-item"><span class="legend-swatch swatch-holiday"></span>Holiday <i class='bx bx-party legend-icon' aria-hidden="true"></i></div>
                  <div class="legend-item"><span class="legend-swatch swatch-multiple"></span>Multiple Bookings <i class='bx bx-group legend-icon' aria-hidden="true"></i></div>
                  <div class="legend-item"><span class="legend-swatch swatch-leave"></span>Leave Day <i class='bx bx-coffee legend-icon' aria-hidden="true"></i></div>
                  <div class="legend-item"><span class="legend-swatch swatch-endyear"></span>End of School Year <i class='bx bx-calendar-x legend-icon' aria-hidden="true"></i></div>
                </div>
              </div>
            </div>
          </div>
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
          <div class="inbox-content" id="notifications-container">
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
      <div class="chat-header">Hey, Cortana. Goodbye. 
        <span>AI Chat Assistant</span>Hey, Cortana. 
        <button class="close-btn" onclick="toggleChat()">×</button>Google. 
      </div>
      <div class="chat-body" id="chatBody">
        <div class="message bot">Hi! How can I help you today?</div>
        <div id="chatBox"></div>
      </div>Hey, Cortana.

    
      <form id="chatForm">
        <input type="text" id="message" placeholder="Type your message" required>
        <button type="submit">Send</button>
      </form>
    </div> 

  <!-- Booking Action Modal -->
  <div id="bookingModal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.4); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:#fff; padding:2rem; border-radius:8px; max-width:350px; margin:auto; text-align:center;">
      <h3 id="bookingModalDate"></h3>
      <p>Status: <span id="bookingModalStatus"></span></p>
      <div style="margin-top:1.5rem;">
        {{-- <button onclick="acceptBooking()" style="margin-right:1rem;">Accept</button> --}}
        {{-- <button onclick="rescheduleBooking()">Reschedule</button> --}}
      </div>
      <button onclick="closeBookingModal()" style="margin-top:2rem;">Close</button>
    </div>
  </div>

  <!-- Consultation Tooltip -->
  <div id="consultationTooltip" style="display:none; position:absolute; z-index:9999; background:#fff; border:1px solid #ccc; border-radius:8px; padding:12px; max-width:320px; box-shadow:0 4px 12px rgba(0,0,0,0.15); font-family:'Poppins',sans-serif; font-size:13px;"></div>

  </div>

  {{-- <script src="{{ asset('js/dashboard.js') }}"></script> --}}
  <script src="https://cdn.jsdelivr.net/npm/pikaday/pikaday.js"></script>
  <script>
    // Responsive notification visibility: hide panel between 769-1450px (bell only)
    (function(){
      function applyProfessorNotifMode(){
        const w = window.innerWidth;
        const panel = document.querySelector('.inbox-notifications');
        const bell = document.getElementById('mobileNotificationBell');
        if(!panel) return; // bail if markup missing
        if(w <= 1450 && w >= 769){
          panel.style.display = 'none';
          if(bell){ bell.style.display = 'block'; bell.style.opacity = '1'; }
        } else if (w >= 1451){
          panel.style.display = '';
          if(bell){ bell.style.display = 'none'; }
        } else { // mobile (<769)
          panel.style.display = 'none';
          if(bell){ bell.style.display = 'block'; }
        }
      }
      window.addEventListener('resize', applyProfessorNotifMode);
      document.addEventListener('DOMContentLoaded', applyProfessorNotifMode);
    })();
    // Wait for DOM to be fully loaded
    document.addEventListener('DOMContentLoaded', function() {
      // Load initial mobile notifications
      loadMobileNotifications();
    });

    // Mobile Notification Functions
    function toggleMobileNotifications() {
      const dropdown = document.getElementById('mobileNotificationDropdown');
      if (dropdown && dropdown.classList) {
        dropdown.classList.toggle('active');
        
        if (dropdown.classList.contains('active')) {
          loadMobileNotifications();
        }
      } else {
        console.log('Mobile notification dropdown not found or classList not available');
      }
    }

    function loadMobileNotifications() {
      fetch('/api/professor/notifications')
        .then(response => response.json())
        .then(data => {
          displayMobileNotifications(data.notifications);
          updateMobileNotificationBadge(data.unread_count);
        })
        .catch(error => {
          console.error('Error loading mobile notifications:', error);
        });
    }

    function displayMobileNotifications(notifications) {
      const container = document.getElementById('mobileNotificationsContainer');
      if (!container) return;

      if (notifications.length === 0) {
        container.innerHTML = `
          <div class="no-notifications">
            <i class='bx bx-bell-off'></i>
            <p>No notifications yet</p>
          </div>`;
        return;
      }

      const html = notifications.map(n => {
        const unreadClass = n.is_read ? '' : 'unread';
        const typeLabel = (n.type || '').replace('_',' ');
        const cleanTitle = (n.title || '').includes('Consultation') ? 'Consultation' : (n.title || '');
        return `
          <div class="notification-item ${unreadClass}" onclick="markMobileNotificationAsRead(${n.id})">
            <div class="notification-type ${n.type}">${typeLabel}</div>
            <div class="notification-title">${cleanTitle}</div>
            <div class="notification-message">${n.message}</div>
            <div class="notification-time" data-timeago data-ts="${n.created_at}"></div>
          </div>`; 
      }).join('');
      container.innerHTML = html;
    }

    function updateMobileNotificationBadge(count) {
      const badge = document.getElementById('mobileNotificationBadge');
      if (badge) {
        badge.textContent = count;
        badge.style.display = count > 0 ? 'flex' : 'none';
      }
    }

    function markMobileNotificationAsRead(notificationId) {
      fetch('/api/professor/notifications/mark-read', {
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
          loadMobileNotifications();
        }
      })
      .catch(error => {
        console.error('Error marking mobile notification as read:', error);
      });
    }

    function markAllNotificationsAsRead() {
      fetch('/api/professor/notifications/mark-all-read', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          loadMobileNotifications();
          updateMobileNotificationBadge(0);
        }
      })
      .catch(error => {
        console.error('Error marking all notifications as read:', error);
      });
    }

    // Live timeago handled by public/js/timeago.js

    // Close mobile notifications when clicking outside
    document.addEventListener('click', function(event) {
      const dropdown = document.getElementById('mobileNotificationDropdown');
      const bell = document.querySelector('.mobile-notification-bell');
      
      if (dropdown && dropdown.classList && bell && !dropdown.contains(event.target) && !bell.contains(event.target)) {
        dropdown.classList.remove('active');
      }
    });
    
   const bookingMap = new Map();
   const detailsMap = new Map();
   
  fetch('/api/consultations')
    .then(response => response.json())
    .then(data => {
      console.log('� Initial load - fetched consultation data:', data.length, 'entries');
      
      data.forEach(entry => {
        // Skip cancelled so they don't pollute counts/colors at first paint
        if ((entry.Status || '').toLowerCase() === 'cancelled') return;
        const date = new Date(entry.Booking_Date);
        const key = date.toDateString();
        
        // For status coloring and modal
        bookingMap.set(key, { status: entry.Status.toLowerCase(), id: entry.Booking_ID });
        // For hover tooltip details
        if (!detailsMap.has(key)) detailsMap.set(key, []);
        detailsMap.get(key).push(entry);
      });

      console.log('Booking Map size:', bookingMap.size);
      console.log('Details Map size:', detailsMap.size);

      // Initialize Pikaday AFTER data is loaded
      const picker = new Pikaday({
        field: document.getElementById('calendar'),
        format: 'ddd, MMM DD YYYY',
        showDaysInNextAndPreviousMonths: true,
        firstDay: 1,
        bound: false,
        onDraw: function() {
          console.log('Calendar onDraw called');
          const cells = document.querySelectorAll('.pika-button');
          console.log('Found calendar cells:', cells.length);
          // Dynamic height adjustment on mobile to prevent legend overlap
          try {
            if (window.innerWidth <= 768) {
              const wrapper = document.querySelector('.calendar-wrapper-container');
              const rowCount = document.querySelectorAll('.pika-table tbody tr').length;
              if (wrapper) {
                // Base heights tuned to match student dashboard spacing
                const heightFor5 = 560; // px
                const heightFor6 = 640; // px (extra space for 6th row)
                wrapper.style.minHeight = (rowCount >= 6 ? heightFor6 : heightFor5) + 'px';
              }
            }
          } catch(e) { /* silent */ }
          
          cells.forEach(cell => {
          // clear any previous override visuals
          const oldBadge = cell.querySelector('.ov-badge'); if (oldBadge) oldBadge.remove();
          cell.classList.remove('day-holiday','day-blocked','day-force','day-online','day-leave','day-endyear');
          const day = cell.getAttribute('data-pika-day');
          const month = cell.getAttribute('data-pika-month');
          const year = cell.getAttribute('data-pika-year');
          
          if (day && month && year) {
            const cellDate = new Date(year, month, day);
            const key = cellDate.toDateString();
            const isoKey = `${cellDate.getFullYear()}-${String(cellDate.getMonth()+1).padStart(2,'0')}-${String(cellDate.getDate()).padStart(2,'0')}`;
            // Render overrides (if any)
            if (window.profOverrides && window.profOverrides[isoKey] && window.profOverrides[isoKey].length > 0) {
              const items = window.profOverrides[isoKey];
              let chosen = null;
              for (const ov of items) { if (ov.effect === 'holiday') { chosen = ov; break; } }
              if (!chosen) { for (const ov of items) { if (ov.effect === 'block_all') { chosen = ov; break; } } }
              if (!chosen) { chosen = items[0]; }
              const badge = document.createElement('span');
              // Badge class: distinguish Online Day vs Forced Online
              let chosenCls;
              if (chosen.effect === 'holiday') {
                chosenCls = 'ov-holiday';
              } else if (chosen.effect === 'block_all') {
                const isLeave = (chosen.reason_key === 'prof_leave' || chosen.label === 'Leave');
                const isEndYear = (!isLeave) && ((chosen.reason_key === 'end_year') || /end\s*year/i.test(chosen.label || '') || /end\s*year/i.test(chosen.reason_text || ''));
                chosenCls = isLeave ? 'ov-leave' : (isEndYear ? 'ov-endyear' : 'ov-blocked');
              } else if (chosen.effect === 'force_mode') {
                chosenCls = (chosen.reason_key === 'online_day') ? 'ov-online' : 'ov-force';
              } else {
                chosenCls = 'ov-force';
              }
              badge.className = 'ov-badge ' + chosenCls;
              const forceLabel = (chosen.effect === 'force_mode' && (chosen.reason_key === 'online_day')) ? 'Online Day' : 'Forced Online';
              badge.title = chosen.label || chosen.reason_text || (chosen.effect === 'force_mode' ? forceLabel : chosen.effect);
              const isProfLeave = (chosen.effect === 'block_all' && (chosen.reason_key === 'prof_leave' || chosen.label === 'Leave'));
              const isEndYearLbl = (chosen.effect === 'block_all') && (!isProfLeave) && ((chosen.reason_key === 'end_year') || /end\s*year/i.test(chosen.label || '') || /end\s*year/i.test(chosen.reason_text || ''));
              badge.textContent = chosen.effect === 'holiday' ? (chosen.reason_text || 'Holiday') : (chosen.effect === 'block_all' ? (isProfLeave ? 'Leave' : (isEndYearLbl ? 'End Year' : 'Suspended')) : forceLabel);
              cell.style.position = 'relative';
              cell.appendChild(badge);
              // Cell background class, with Online Day distinct from Forced Online
              let dayCls;
              if (chosen.effect === 'holiday') {
                dayCls = 'day-holiday';
              } else if (chosen.effect === 'block_all') {
                const isLeave = (chosen.reason_key === 'prof_leave' || chosen.label === 'Leave');
                const isEndYear = (!isLeave) && ((chosen.reason_key === 'end_year') || /end\s*year/i.test(chosen.label || '') || /end\s*year/i.test(chosen.reason_text || ''));
                dayCls = isLeave ? 'day-leave' : (isEndYear ? 'day-endyear' : 'day-blocked');
              } else if (chosen.effect === 'force_mode') {
                dayCls = (chosen.reason_key === 'online_day') ? 'day-online' : 'day-force';
              } else {
                dayCls = 'day-force';
              }
              cell.classList.add(dayCls);
            }
            
            if (bookingMap.has(key)) {
              console.log('Found booking for date:', key, bookingMap.get(key));
              
              const booking = bookingMap.get(key);
              const status = booking.status;
              const bookingId = booking.id;
              
              // Get the number of consultations for this date
              const consultationsForDay = detailsMap.get(key) || [];
              const consultationCount = consultationsForDay.length;
              
              const classMap = {
                pending: 'status-pending',
                approved: 'status-approved',
                completed: 'status-completed',
                rescheduled: 'status-rescheduled'
              };
              
              cell.classList.add('has-booking');
              cell.classList.add(classMap[status]);
              cell.setAttribute('data-status', status);
              
              // Add multiple booking indicators
              if (consultationCount >= 2) {
                cell.classList.add('has-multiple-bookings');
              }
              
              // Store consultation count for tooltip or other uses
              cell.setAttribute('data-consultation-count', consultationCount);
              
              console.log('Added classes to cell:', cell.className, 'Consultations:', consultationCount);
              
              // CLICK FUNCTIONALITY DISABLED - Only hover tooltips enabled
              // cell.removeEventListener('click', cell.bookingClickHandler);

              // Disabled click functionality - only hover enabled
              cell.style.cursor = 'default'; // Changed from 'pointer' to 'default'
              console.log('Click disabled - only hover tooltip enabled');

              // --- IMPROVED HOVER FUNCTIONALITY ---
              console.log('Adding hover events to cell:', key);
              
              // Store data directly on the DOM element for reliable access
              cell.setAttribute('data-consultation-key', key);
              cell.setAttribute('data-has-consultations', 'true');
              
              console.log('Cell prepared for hover with key:', key);
            } else {
              // console.log('⚪ No booking for date:', key);
            }
          }
        });
        // After painting cells, bind leave-day click handlers to each date button
        try { if (window.__bindLeaveHandlers) { window.__bindLeaveHandlers(); } } catch (_) {}
        }
      });
      picker.show();
      picker.draw();
      // Run once after initial draw in case first month has 6 rows
      (function adjustInitialMobileHeight(){
        if (window.innerWidth <= 768) {
          const wrapper = document.querySelector('.calendar-wrapper-container');
          const rowCount = document.querySelectorAll('.pika-table tbody tr').length;
          if (wrapper) {
            wrapper.style.minHeight = (rowCount >= 6 ? 640 : 560) + 'px';
          }
        }
      })();
      
      // Store picker globally for real-time updates
      window.professorPicker = picker;
      // Immediately load overrides for the visible month after initial draw
      try { if (typeof fetchProfessorOverridesForMonth === 'function' && typeof getVisibleMonthBaseDate === 'function') { fetchProfessorOverridesForMonth(getVisibleMonthBaseDate()); } } catch (_) {}
    })
    .catch(error => {
      console.error('Error fetching consultation data:', error);
      console.log('Calendar will still load without consultation data');
    });

// ---- Overrides: fetch month data and react to month navigation ----
function getVisibleMonthBaseDate() {
  try {
    const selMonth = document.querySelector('.pika-select-month');
    const selYear = document.querySelector('.pika-select-year');
    if (selMonth && selYear) {
      const m = parseInt(selMonth.value, 10);
      const y = parseInt(selYear.value, 10);
      if (!isNaN(m) && !isNaN(y)) {
        const d = new Date(y, m, 1);
        if (!isNaN(d.getTime())) return d;
      }
    }
    const labelEl = document.querySelector('.pika-label');
    if (labelEl) {
      const text = (labelEl.textContent || '').trim();
      const parts = text.split(/\s+/);
      if (parts.length === 2) {
        const monthMap = { January:0, February:1, March:2, April:3, May:4, June:5, July:6, August:7, September:8, October:9, November:10, December:11, Jan:0, Feb:1, Mar:2, Apr:3, Jun:5, Jul:6, Aug:7, Sep:8, Oct:9, Nov:10, Dec:11 };
        const m = monthMap[parts[0]];
        const y = parseInt(parts[1], 10);
        if (!isNaN(m) && !isNaN(y)) {
          const d = new Date(y, m, 1);
          if (!isNaN(d.getTime())) return d;
        }
      }
    }
    const cur = document.querySelector('.pika-table .pika-button:not(.is-outside-current-month)');
    if (cur) {
      const y = parseInt(cur.getAttribute('data-pika-year'), 10);
      const m = parseInt(cur.getAttribute('data-pika-month'), 10);
      if (!isNaN(y) && !isNaN(m)) {
        const d = new Date(y, m, 1);
        if (!isNaN(d.getTime())) return d;
      }
    }
  } catch (_) {}
  const today = new Date();
  return new Date(today.getFullYear(), today.getMonth(), 1);
}

function fetchProfessorOverridesForMonth(dateObj) {
  try {
    if (!dateObj || !(dateObj instanceof Date) || isNaN(dateObj.getTime())) return;
    if (window.__profOvLoading) return; // prevent overlapping requests
    window.__profOvLoading = true;
    // widen range to include adjacent-month cells visible in grid
    const start = new Date(dateObj.getFullYear(), dateObj.getMonth() - 1, 1);
    const end = new Date(dateObj.getFullYear(), dateObj.getMonth() + 2, 0);
    const toIso = (d) => `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
    const startStr = toIso(start);
    const endStr = toIso(end);
    const bust = Date.now();
    // LocalStorage helpers
    function lsGetOv(key){ try{ const raw=localStorage.getItem(key); if(!raw) return null; const obj=JSON.parse(raw); if(!obj||!obj.exp||Date.now()>obj.exp){ localStorage.removeItem(key); return null; } return obj.data; }catch(_){ return null; } }
    function lsSetOv(key, data, ttlMs){ try{ localStorage.setItem(key, JSON.stringify({ exp: Date.now()+(ttlMs||6*3600*1000), data })); }catch(_){ }
    }
    const lsKey = `ov:prof:${startStr}-${endStr}`;
    // Instant paint from cached snapshot if available
    try { const ls = lsGetOv(lsKey); if (ls) { window.profOverrides = ls; if (window.professorPicker) window.professorPicker.draw(); } } catch(_) {}
    // Network refresh
    fetch(`/api/professor/calendar/overrides?start_date=${startStr}&end_date=${endStr}&_=${bust}`, { headers: { 'Accept':'application/json' }, credentials:'same-origin' })
      .then(r=>r.json())
      .then(data => {
        if (data && data.success) {
          const incoming = data.overrides || {};
          const prev = window.profOverrides || {};
          const changed = JSON.stringify(incoming) !== JSON.stringify(prev);
          if (changed) {
            window.profOverrides = incoming;
            try { lsSetOv(lsKey, incoming, 6*3600*1000); } catch(_) {}
            if (window.professorPicker) window.professorPicker.draw();
          }
        }
      })
      .catch(()=>{})
      .finally(()=>{ window.__profOvLoading = false; });
  } catch(_){}
}

(function observeMonthNavigation(){
  const run = () => fetchProfessorOverridesForMonth(getVisibleMonthBaseDate());
  setTimeout(run, 100);
  document.addEventListener('click', (e)=>{
    const t = e.target;
    if (t.closest && (t.closest('.pika-prev') || t.closest('.pika-next'))) {
      setTimeout(run, 150);
    }
  });
  // Lightweight real-time: refresh overrides periodically and on focus
  setInterval(run, 5000); // every 5s
  window.addEventListener('focus', () => setTimeout(run, 200));
  document.addEventListener('visibilitychange', () => { if (!document.hidden) setTimeout(run, 200); });
})();

// ---- Professor Leave Day: bind click per-cell on each draw ----
(function enableProfessorLeaveToggle(){
  window.__enableLeaveToggle = true;
  function toIsoFromCell(btn){
    const y = parseInt(btn.getAttribute('data-pika-year'),10);
    const m = parseInt(btn.getAttribute('data-pika-month'),10);
    const d = parseInt(btn.getAttribute('data-pika-day'),10);
    if (Number.isNaN(y)||Number.isNaN(m)||Number.isNaN(d)) return null;
    return `${y}-${String(m+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
  }
  function isLeaveOnIso(iso){
    try{
      const list = (window.profOverrides||{})[iso] || [];
      return list.some(x => x.effect==='block_all' && (x.reason_key==='prof_leave' || x.label==='Leave'));
    }catch(_){return false;}
  }
  async function postJson(url, body){
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const res = await fetch(url, { method:'POST', headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN': token}, body: JSON.stringify(body), credentials:'same-origin' });
    return res.json();
  }
  function ensureToastWrapper(){ let wrap=document.querySelector('.toast-wrapper'); if(!wrap){ wrap=document.createElement('div'); wrap.className='toast-wrapper'; document.body.appendChild(wrap);} return wrap; }
  function showToast(message, type='info', timeout=2200){ const wrap=ensureToastWrapper(); const toast=document.createElement('div'); toast.className=`ascc-toast ${type==='success'?'ascc-toast-success': type==='error'?'ascc-toast-error':'ascc-toast-info'}`; toast.innerHTML=`<div>${message}</div><button class="ascc-toast-close" aria-label="Close">×</button>`; wrap.appendChild(toast); const closer=toast.querySelector('.ascc-toast-close'); let hid=false; const hide=()=>{ if(hid) return; hid=true; toast.classList.add('hide'); setTimeout(()=> toast.remove(), 250); }; closer.addEventListener('click', hide); setTimeout(hide, timeout); }
  function themedConfirm(title, html){ return new Promise(resolve=>{ const overlay=document.createElement('div'); overlay.className='ascc-confirm-overlay'; const dlg=document.createElement('div'); dlg.className='ascc-confirm'; dlg.setAttribute('role','dialog'); dlg.setAttribute('aria-modal','true'); dlg.innerHTML=`<div class=\"ascc-confirm-header\"><div class=\"ascc-confirm-title\">${title}</div><button class=\"ascc-confirm-close\" aria-label=\"Close\">×</button></div><div class=\"ascc-confirm-body\">${html}</div><div class=\"ascc-confirm-actions\"><button id=\"dlgCancel\" class=\"ascc-btn ascc-btn-secondary\">Cancel</button><button id=\"dlgOk\" class=\"ascc-btn ascc-btn-primary\">Confirm</button></div>`; overlay.appendChild(dlg); document.body.appendChild(overlay); const okBtn=dlg.querySelector('#dlgOk'); const cancelBtn=dlg.querySelector('#dlgCancel'); const closeBtn=dlg.querySelector('.ascc-confirm-close'); const cleanup=()=>{ document.removeEventListener('keydown', onKey); overlay.remove(); }; const close=(v)=>{ cleanup(); resolve(v); }; const onKey=(e)=>{ if(e.key==='Escape'){ e.preventDefault(); close(false);} if(e.key==='Tab'){ const focusables=dlg.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex=\"-1\"])'); if(focusables.length){ const first=focusables[0]; const last=focusables[focusables.length-1]; if(e.shiftKey && document.activeElement===first){ last.focus(); e.preventDefault(); } else if(!e.shiftKey && document.activeElement===last){ first.focus(); e.preventDefault(); } } } }; closeBtn.addEventListener('click', ()=>close(false)); cancelBtn.addEventListener('click', ()=>close(false)); okBtn.addEventListener('click', ()=>close(true)); document.addEventListener('keydown', onKey); okBtn.focus(); }); }

  async function handleLeaveToggle(btn){
    try { console.log('[LeaveToggle] date cell clicked'); } catch(_) {}
    const iso = toIsoFromCell(btn);
    if (!iso) return;
    const currentlyLeave = isLeaveOnIso(iso);
    const title = currentlyLeave ? 'Remove Leave Day' : 'Set Leave Day';
    const msg = currentlyLeave ? `Remove your leave on <strong>${btn.getAttribute('aria-label')||iso}</strong>?` : `Mark <strong>${btn.getAttribute('aria-label')||iso}</strong> as a Leave day? This will block bookings.`;
    const ok = await themedConfirm(title, msg);
    if (!ok) return;
    try {
      const url = currentlyLeave ? '/api/professor/calendar/leave/remove' : '/api/professor/calendar/leave/apply';
      const data = await postJson(url, { start_date: iso });
      if (data && data.success) {
        window.profOverrides = window.profOverrides || {};
        const arr = window.profOverrides[iso] || [];
        if (currentlyLeave) {
          window.profOverrides[iso] = arr.filter(x => !(x.effect==='block_all' && (x.reason_key==='prof_leave' || x.label==='Leave')));
          showToast('Leave removed', 'success');
        } else {
          arr.push({ effect:'block_all', reason_key:'prof_leave', reason_text:'Leave', label:'Leave' });
          window.profOverrides[iso] = arr;
          showToast('Leave set', 'success');
        }
        if (window.professorPicker) window.professorPicker.draw();
      } else {
        showToast('Failed to update leave', 'error');
      }
    } catch(_){ showToast('Failed to update leave', 'error'); }
  }

  function bindLeaveHandlers(){
    const buttons = document.querySelectorAll('.pika-button');
    let boundCount = 0;
    buttons.forEach(btn => {
      if (btn.dataset.leaveBound === '1') return;
      btn.dataset.leaveBound = '1';
      // Guard to prevent double-fire when multiple events occur
      const guard = () => {
        if (btn.dataset.leaveInFlight === '1') return true;
        btn.dataset.leaveInFlight = '1';
        setTimeout(()=>{ delete btn.dataset.leaveInFlight; }, 600);
        return false;
      };
      btn.addEventListener('click', (e)=>{
        // prevent Pikaday selection but allow our own flow
        try { e.preventDefault(); e.stopPropagation(); } catch(_) {}
        if (!guard()) handleLeaveToggle(btn);
      }, { passive:false });
      // Also bind early events in case some code prevents .click later
      ['pointerdown','mousedown','touchstart'].forEach(type => {
        btn.addEventListener(type, (e)=>{
          try { e.preventDefault(); e.stopPropagation(); } catch(_) {}
          if (!guard()) handleLeaveToggle(btn);
        }, { passive:false });
      });
      boundCount++;
    });
    try { if (boundCount) console.log(`[LeaveToggle] bound per-cell handlers: ${boundCount}`); } catch(_) {}
  }
  // expose binder so onDraw can call
  window.__bindLeaveHandlers = bindLeaveHandlers;
  // In case the first onDraw fired before this script, bind once now
  setTimeout(bindLeaveHandlers, 0);

  // Delegated fallback: if a button wasn't bound yet, catch the click here
  document.addEventListener('click', function(e){
    const btn = e.target && e.target.closest ? e.target.closest('.pika-button') : null;
    if (!btn) return;
    if (btn.dataset.leaveBound === '1') return; // per-cell handler will take over
    // Avoid interfering with other UI parts
    try { console.log('[LeaveToggle] delegated bubble click caught on', btn.getAttribute('aria-label')||btn.textContent); e.preventDefault(); e.stopPropagation(); } catch(_) {}
    handleLeaveToggle(btn);
  }, { passive:false, capture:false });

  // Extra visibility: capture-phase logger to ensure clicks reach the page
  document.addEventListener('click', function(e){
    const btn = e.target && e.target.closest ? e.target.closest('.pika-button') : null;
    if (btn) {
      try { console.log('[LeaveToggle] capture click on', btn.getAttribute('aria-label')||btn.textContent); } catch(_) {}
    }
  }, { passive:false, capture:true });

  // Delegated early-phase for pointerdown/mousedown/touchstart as ultimate fallback
  ['pointerdown','mousedown','touchstart'].forEach(type => {
    document.addEventListener(type, function(e){
      const btn = e.target && e.target.closest ? e.target.closest('.pika-button') : null;
      if (!btn) return;
      if (btn.dataset.leaveBound === '1') return; // per-cell will handle
      try { console.log(`[LeaveToggle] delegated ${type} caught on`, btn.getAttribute('aria-label')||btn.textContent); e.preventDefault(); e.stopPropagation(); } catch(_) {}
      handleLeaveToggle(btn);
    }, { passive:false, capture:true });
  });
})();
function bookingModal(date, bookingId) {
  // Set the modal date
  document.getElementById('bookingModalDate').textContent = date.toDateString();

  // Get the status from bookingMap if available
  const booking = bookingMap.get(date.toDateString());
  document.getElementById('bookingModalStatus').textContent = booking ? booking.status.charAt(0).toUpperCase() + booking.status.slice(1) : '';

  // Optionally store bookingId for later use (e.g., accept/reschedule)
  document.getElementById('bookingModal').setAttribute('data-booking-id', bookingId);

  // Show the modal
  document.getElementById('bookingModal').style.display = 'flex';
}

function closeBookingModal() {
  document.getElementById('bookingModal').style.display = 'none';
}

// Initialize professor notifications
loadProfessorNotifications();

// Initialize calendar data refresh
loadProfessorCalendarData();

// Real-time load notifications every 3 seconds (reduced for smoother updates)
setInterval(loadProfessorNotifications, 3000);

// Real-time refresh calendar data every 3 seconds (reduced for smoother updates)
setInterval(loadProfessorCalendarData, 3000);

// Legend panel interactions
(function legendPanelInit(){
  const btn = document.getElementById('legendToggle');
  const backdrop = document.getElementById('legendBackdrop');
  const closeBtn = document.getElementById('legendClose');
  if(!btn || !backdrop) return;
  const open = () => { backdrop.classList.add('open'); backdrop.setAttribute('aria-hidden','false'); };
  const close = () => { backdrop.classList.remove('open'); backdrop.setAttribute('aria-hidden','true'); };
  btn.addEventListener('click', open);
  closeBtn && closeBtn.addEventListener('click', close);
  backdrop.addEventListener('click', (e)=>{ if(e.target === backdrop) close(); });
  document.addEventListener('keydown', (e)=>{ if(e.key === 'Escape') close(); });
})();

// GLOBAL EVENT DELEGATION FOR TOOLTIP HOVER (Pikaday-compatible)
console.log('Setting up global hover event delegation...');

let tooltipTimeout;
let currentHoveredCell = null;

document.addEventListener('mouseover', function(e) {
  const target = e.target;
  
  // Clear any pending hide timeout
  if (tooltipTimeout) {
    clearTimeout(tooltipTimeout);
    tooltipTimeout = null;
  }
  
  // Check if the target is a Pikaday button with consultation data
  if (target && target.classList && target.classList.contains('pika-button') && target.hasAttribute('data-consultation-key')) {
    const key = target.getAttribute('data-consultation-key');
    
    // Only update tooltip if it's a different cell or tooltip is not visible
    const tooltip = document.getElementById('consultationTooltip');
    const isTooltipVisible = tooltip && tooltip.style.display === 'block';
    const isDifferentCell = currentHoveredCell !== target;
    
    if (!isTooltipVisible || isDifferentCell) {
      currentHoveredCell = target;
      
      console.log('Hovering over cell with key:', key);
      const consultations = detailsMap.get(key) || [];
      console.log('Consultations found:', consultations);
      
      if (consultations.length === 0) {
        return;
      }
      
      let html = '';
      
      // Add header with consultation count
      const countText = consultations.length === 1 ? '1 Consultation' : `${consultations.length} Consultations`;
      html += `<div style="font-weight: bold; margin-bottom: 8px; color: #12372a; border-bottom: 1px solid #ddd; padding-bottom: 4px;">${countText}</div>`;
      
      // Helper to convert 'YYYY-MM-DD HH:MM:SS' to 12-hour format with AM/PM
      function formatTo12Hour(ts) {
        if (!ts) return '';
        const parts = ts.split(' ');
        if (parts.length < 2) return ts;
        const datePart = parts[0];
        const timePart = parts[1];
        const tPieces = timePart.split(':');
        if (tPieces.length < 2) return ts;
        let hour = parseInt(tPieces[0], 10);
        const minute = tPieces[1];
        const second = tPieces[2] || '00';
        if (isNaN(hour)) return ts;
        const suffix = hour >= 12 ? 'PM' : 'AM';
        const hour12 = ((hour + 11) % 12) + 1; // 0 -> 12
        const hourStr = hour12.toString().padStart(2, '0');
        return `${datePart} ${hourStr}:${minute}:${second} ${suffix}`;
      }

      consultations.forEach((entry, index) => {
        html += `
          <div class="consultation-entry" style="${index > 0 ? 'border-top: 1px solid #eee; padding-top: 6px; margin-top: 6px;' : ''}">
            <div class="student-name">${entry.student}</div>
            <div class="detail-row">Subject: ${entry.subject}</div>
            <div class="detail-row">Type: ${entry.type}</div>
            <div class="detail-row">Mode: ${entry.Mode}</div>
            <div class="status-row" style="color:${getStatusColor(entry.Status)};">Status: ${entry.Status}</div>
            <div class="booking-time">Booked: ${formatTo12Hour(entry.Created_At)}</div>
          </div>
        `;
      });
      
      if (!tooltip) {
        console.error('Tooltip element not found!');
        return;
      }
      
  tooltip.innerHTML = html;
      tooltip.style.display = 'block';

      // Anchor tooltip to the right of the hovered cell (consistent UI)
      const cellRect = target.getBoundingClientRect();
      const tooltipRect = tooltip.getBoundingClientRect();
      const viewportHeight = window.innerHeight;
      const scrollY = window.scrollY || document.documentElement.scrollTop;
      const scrollX = window.scrollX || document.documentElement.scrollLeft;
      const GAP = 12;
      let left = cellRect.right + GAP + scrollX;
      let top = cellRect.top + scrollY;
      if (top + tooltipRect.height > scrollY + viewportHeight - 10) {
        top = scrollY + viewportHeight - tooltipRect.height - 10;
      }
      if (top < scrollY + 10) {
        top = scrollY + 10;
      }
      const maxRight = scrollX + window.innerWidth - 10;
      if (left + tooltipRect.width > maxRight) {
        left = Math.min(left, maxRight - tooltipRect.width);
      }
      tooltip.style.left = left + 'px';
      tooltip.style.top = top + 'px';
    }
  } else {
    // Mouse is not over a consultation cell, check if it's over the tooltip
    if (currentHoveredCell && !target.closest('#consultationTooltip')) {
      tooltipTimeout = setTimeout(function() {
        const tooltip = document.getElementById('consultationTooltip');
        if (tooltip) {
          tooltip.style.display = 'none';
        }
        currentHoveredCell = null;
      }, 300); // Increased delay to allow moving to tooltip
    }
  }
});

document.addEventListener('mouseout', function(e) {
  const target = e.target;
  const relatedTarget = e.relatedTarget;
  
  // Check if we're leaving a consultation cell
  if (target && target.classList && target.classList.contains('pika-button') && target.hasAttribute('data-consultation-key')) {
    // Make sure we're not moving to the tooltip itself
    if (!relatedTarget || !relatedTarget.closest('#consultationTooltip')) {
      const tooltip = document.getElementById('consultationTooltip');
      if (tooltip) {
        tooltip.style.display = 'none';
      }
    }
  }
});

// Additional safety: Hide tooltip when mouse leaves the calendar area entirely
document.addEventListener('mouseleave', function(e) {
  const target = e.target;
  if (target && target.classList && (target.classList.contains('pika-table') || target.closest('.pika-single'))) {
    const tooltip = document.getElementById('consultationTooltip');
    if (tooltip) {
      tooltip.style.display = 'none';
    }
  }
});

// Hide tooltip when clicking anywhere outside calendar cells
document.addEventListener('click', function(e) {
  const target = e.target;
  if (!target || !target.classList || !target.classList.contains('pika-button')) {
    const tooltip = document.getElementById('consultationTooltip');
    if (tooltip) {
      tooltip.style.display = 'none';
    }
  }
});

console.log('Global hover delegation system initialized');

// PREVENT ONLY CLICK AND TOUCH EVENTS ON CALENDAR DATE CELLS, ALLOW HOVER
function preventCalendarClicks(e) {
  const target = e.target;
  // Only prevent clicks/touches on date buttons inside the table, not navigation buttons
  // Allow mouseover/mouseout for tooltips
  if (target && target.classList && target.classList.contains('pika-button') && target.closest('.pika-table')) {
    if (e.type === 'click' || e.type === 'mousedown' || e.type === 'touchstart' || e.type === 'touchend') {
      // If leave toggle is enabled, let the event flow (no preventDefault, no stopPropagation)
      if (window.__enableLeaveToggle === true) {
        return; 
      }
      // Otherwise, block default selection and propagation
      e.preventDefault();
      e.stopPropagation();
      e.stopImmediatePropagation();
      console.log('Calendar date interaction prevented:', e.type);
      return false;
    }
  }
}

// Add hover events to tooltip to keep it stable for scrolling
document.addEventListener('DOMContentLoaded', function() {
  const tooltip = document.getElementById('consultationTooltip');
  if (tooltip) {
    // Keep tooltip visible when hovering over it
    tooltip.addEventListener('mouseenter', function() {
      if (tooltipTimeout) {
        clearTimeout(tooltipTimeout);
        tooltipTimeout = null;
      }
    });
    
    // Hide tooltip when leaving it (with delay)
    tooltip.addEventListener('mouseleave', function() {
      tooltipTimeout = setTimeout(function() {
        tooltip.style.display = 'none';
        currentHoveredCell = null;
      }, 200);
    });
  }
});

// Prevent only specific events that cause date selection
['click', 'mousedown', 'touchstart', 'touchend'].forEach(eventType => {
  // Use passive:false so we may call preventDefault when we intend to block interactions
  document.addEventListener(eventType, preventCalendarClicks, { capture: true, passive: false }); // Capture phase
  document.addEventListener(eventType, preventCalendarClicks, { capture: false, passive: false }); // Bubble phase
});



// Mark all as read functionality
document.getElementById('mark-all-read').addEventListener('click', function() {
  markAllProfessorNotificationsAsRead();
});

function loadProfessorCalendarData() {
  fetch('/api/consultations', {
    method: 'GET',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      'Accept': 'application/json'
    }
  })
    .then(response => {
      console.log('Real-time API Response status:', response.status);
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      return response.json();
    })
    .then(data => {
      console.log('Real-time update - fetched data:', data.length, 'entries');
      
      // Store previous booking map and counts for comparison
      const previousBookings = new Map();
      const previousCounts = new Map();
      bookingMap.forEach((value, key) => {
        previousBookings.set(key, value);
      });
      detailsMap.forEach((arr, key) => { previousCounts.set(key, (arr||[]).length); });
      
      bookingMap.clear(); // Clear existing data
      detailsMap.clear(); // Clear details data
      
      data.forEach(entry => {
        // Skip cancelled so they don't show in tooltip
        if ((entry.Status || '').toLowerCase() === 'cancelled') return;
        const date = new Date(entry.Booking_Date);
        const key = date.toDateString();
        // For status coloring and modal
        bookingMap.set(key, { status: entry.Status.toLowerCase(), id: entry.Booking_ID });
        // For hover tooltip details
        if (!detailsMap.has(key)) detailsMap.set(key, []);
        detailsMap.get(key).push(entry);
      });

      // Only update calendar if there are actual changes
      let hasChanges = false;
      
      // Check for new or changed bookings
      for (const [dateStr, booking] of bookingMap) {
        const previousBooking = previousBookings.get(dateStr);
        if (!previousBooking || previousBooking.status !== booking.status || previousBooking.id !== booking.id) {
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

      // Check for consultation count changes (affects multi-booking color)
      if (!hasChanges) {
        // Build current counts
        const currentCounts = new Map();
        detailsMap.forEach((arr, key) => { currentCounts.set(key, (arr||[]).length); });
        // Compare previous vs. current counts
        const allKeys = new Set([...previousCounts.keys(), ...currentCounts.keys()]);
        for (const k of allKeys) {
          if ((previousCounts.get(k) || 0) !== (currentCounts.get(k) || 0)) { hasChanges = true; break; }
        }
      }

      // Only update calendar cells if there are changes
          if (hasChanges && window.professorPicker) {
        const cells = document.querySelectorAll('.pika-button');
        cells.forEach(cell => {
          const cellDate = new Date(cell.getAttribute('data-pika-year'), cell.getAttribute('data-pika-month'), cell.getAttribute('data-pika-day'));
          const dateStr = cellDate.toDateString();
          const booking = bookingMap.get(dateStr);
          const previousBooking = previousBookings.get(dateStr);
          
          // Only update if status changed for this specific date
          if (!previousBooking && booking || 
              previousBooking && !booking ||
              (previousBooking && booking && previousBooking.status !== booking.status)) {
            
            // Remove existing status classes and multiple booking classes
            cell.classList.remove('status-pending', 'status-approved', 'status-completed', 'status-rescheduled');
            cell.classList.remove('has-multiple-bookings');
            
            // Clear any existing event listeners by cloning the element
            const newCell = cell.cloneNode(true);
            cell.parentNode.replaceChild(newCell, cell);
            
              if (booking) {
              newCell.classList.add(`status-${booking.status}`);
              
              // Get the number of consultations for this date and add appropriate classes
              const consultationsForDay = detailsMap.get(dateStr) || [];
              const consultationCount = consultationsForDay.length;
              
                if (consultationCount >= 2) {
                newCell.classList.add('has-multiple-bookings');
              }
              
              // Store consultation count for tooltip or other uses
                  newCell.setAttribute('data-consultation-count', consultationCount);
              
              // Use data attributes for global event delegation (Pikaday-compatible)
              const key = dateStr;
              newCell.setAttribute('data-consultation-key', key);
              newCell.setAttribute('data-has-consultations', 'true');
              
                  console.log('Updated cell with global hover data:', key, 'Consultations:', consultationCount);
            }
          }
        });

        // If current hovered cell now has no consultations, hide tooltip
        try {
          const tooltip = document.getElementById('consultationTooltip');
          if (tooltip && currentHoveredCell) {
            const c = currentHoveredCell;
            const d = new Date(c.getAttribute('data-pika-year'), c.getAttribute('data-pika-month'), c.getAttribute('data-pika-day'));
            const key = d.toDateString();
            const list = detailsMap.get(key) || [];
            if (list.length === 0) {
              tooltip.style.display = 'none';
              currentHoveredCell = null;
            }
          }
        } catch(_) {}
      }

        // Always correct the multi-booking indicator and counts based on current data,
        // even if status/id didn't change (e.g., cancellations reduced the count).
        try {
          const cells = document.querySelectorAll('.pika-button');
          cells.forEach(cell => {
            const y = parseInt(cell.getAttribute('data-pika-year'), 10);
            const m = parseInt(cell.getAttribute('data-pika-month'), 10);
            const d = parseInt(cell.getAttribute('data-pika-day'), 10);
            if (Number.isNaN(y) || Number.isNaN(m) || Number.isNaN(d)) return;
            const dateStr = new Date(y, m, d).toDateString();
            const list = detailsMap.get(dateStr) || [];
            const cnt = list.length;
            if (cnt >= 2) cell.classList.add('has-multiple-bookings');
            else cell.classList.remove('has-multiple-bookings');
            cell.setAttribute('data-consultation-count', cnt);
            // If zero consultations for the day, ensure tooltip flags are cleared
            if (cnt === 0) {
              cell.removeAttribute('data-consultation-key');
              cell.removeAttribute('data-has-consultations');
            } else {
              cell.setAttribute('data-consultation-key', dateStr);
              cell.setAttribute('data-has-consultations', 'true');
            }
          });
        } catch (_) {}
    })
    .catch(error => {
      console.error('Error loading professor calendar data:', error);
    });
}

let professorNotificationsHash = '';

function loadProfessorNotifications() {
  fetch('/api/professor/notifications')
    .then(response => response.json())
    .then(data => {
      // Create a hash of the notifications to detect changes
      const notificationsString = JSON.stringify(data.notifications);
      const currentHash = btoa(notificationsString);
      
      // Only update if notifications have changed
      if (currentHash !== professorNotificationsHash) {
        professorNotificationsHash = currentHash;
        displayProfessorNotifications(data.notifications);
        updateProfessorUnreadCount();
      }
    })
    .catch(error => {
      console.error('Error loading professor notifications:', error);
    });
}

function displayProfessorNotifications(notifications) {
  const container = document.getElementById('notifications-container');
  const mobileContainer = document.getElementById('mobileNotificationsContainer');
  
  if (notifications.length === 0) {
    const noNotificationsHtml = `
      <div class="no-notifications">
        <i class='bx bx-bell-off'></i>
        <p>No notifications yet</p>
      </div>
    `;
    container.innerHTML = noNotificationsHtml;
    if (mobileContainer) {
      mobileContainer.innerHTML = noNotificationsHtml;
    }
    return;
  }
  
  const notificationsHtml = notifications.map(notification => {
    const timeAgo = getTimeAgo(notification.created_at);
    const unreadClass = notification.is_read ? '' : 'unread';
    return `
      <div class="notification-item ${unreadClass}" onclick="markProfessorNotificationAsRead(${notification.id})">
        <div class="notification-type ${notification.type}">${notification.type.replace('_', ' ')}</div>
        <div class="notification-title">${notification.title}</div>
        <div class="notification-message">${notification.message}</div>
        <div class="notification-time">${timeAgo}</div>
      </div>
    `;
  }).join('');
  
  container.innerHTML = notificationsHtml;
  if (mobileContainer) {
    mobileContainer.innerHTML = notificationsHtml;
  }
}

function markProfessorNotificationAsRead(notificationId) {
  fetch('/api/professor/notifications/mark-read', {
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
      professorNotificationsHash = '';
      loadProfessorNotifications(); // Reload to update read status
    }
  })
  .catch(error => {
    console.error('Error marking professor notification as read:', error);
  });
}

function markAllProfessorNotificationsAsRead() {
  fetch('/api/professor/notifications/mark-all-read', {
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
      professorNotificationsHash = '';
      loadProfessorNotifications(); // Reload to update read status
      updateProfessorUnreadCount();
    }
  })
  .catch(error => {
    console.error('Error marking all professor notifications as read:', error);
  });
}


function updateProfessorUnreadCount() {
  fetch('/api/professor/notifications/unread-count')
    .then(response => response.json())
    .then(data => {
      const countElement = document.getElementById('unread-count');
      const mobileCountElement = document.getElementById('mobileNotificationBadge');
      
      if (data.unread_count > 0) {
        // Update desktop notification count
        countElement.textContent = data.unread_count;
        countElement.style.display = 'inline-block';
        
        // Update mobile notification badge
        if (mobileCountElement) {
          mobileCountElement.textContent = data.unread_count;
          mobileCountElement.style.display = 'flex';
        }
      } else {
        // Hide desktop notification count
        countElement.style.display = 'none';
        
        // Hide mobile notification badge
        if (mobileCountElement) {
          mobileCountElement.style.display = 'none';
        }
      }
    })
    .catch(error => {
      console.error('Error getting professor unread count:', error);
    });
}

function getTimeAgo(dateString) {
  const date = new Date(dateString);
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

// Helper function for status colors
function getStatusColor(status) {
  const colors = {
    'pending': '#ffa600',
    'approved': '#0f9657', 
    'completed': '#093b2f',
    'rescheduled': '#c50000'
  };
  return colors[status.toLowerCase()] || '#666';
}

// Helper functions for formatting dates
function formatDate(dateStr) {
  const d = new Date(dateStr);
  return d.toLocaleString('en-US', { month: 'long', day: 'numeric' });
}

function formatDay(dateStr) {
  const d = new Date(dateStr);
  return d.toLocaleString('en-US', { weekday: 'short' });
}

// Chat functionality
function toggleChat() {
  const chatOverlay = document.getElementById('chatOverlay');
  if (chatOverlay) {
    chatOverlay.classList.toggle('open');
    
    // Initialize with welcome message if first time opening
    const chatBody = document.getElementById('chatBody');
    if (chatBody && chatBody.children.length <= 1) {
      // Only has the default welcome message
      setTimeout(() => {
        chatBody.scrollTop = chatBody.scrollHeight;
      }, 100);
    }
  }
}

function addMessage(message, sender) {
  const chatBody = document.getElementById('chatBody');
  if (chatBody) {
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${sender}`;
    messageDiv.textContent = message;
    chatBody.appendChild(messageDiv);
    chatBody.scrollTop = chatBody.scrollHeight;
  }
}

// Initialize chat functionality when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  const chatForm = document.getElementById('chatForm');
  const input = document.getElementById('message');
  const chatBody = document.getElementById('chatBody');

  if (chatForm && input && chatBody && csrfToken) {
    chatForm.addEventListener('submit', async function(e) {
      e.preventDefault();
      const text = input.value.trim();
      if (!text) return;

      // show user message
      const um = document.createElement('div');
      um.classList.add('message', 'user');
      um.innerText = text;
      chatBody.appendChild(um);

      chatBody.scrollTop = chatBody.scrollHeight;
      input.value = '';

      // send request to server
      const res = await fetch('/chat', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({ message: text }),
      });

      if (!res.ok) {
        const err = await res.json();
        const bm = document.createElement('div');
        bm.classList.add('message', 'bot');
        bm.innerText = err.message || 'Server error.';
        chatBody.appendChild(bm);
        return;
      }

      // render bot reply
      const { reply } = await res.json();
      const bm = document.createElement('div');
      bm.classList.add('message', 'bot');
      bm.innerText = reply;
      chatBody.appendChild(bm);
      chatBody.scrollTop = chatBody.scrollHeight;
    });

    // Add Enter key functionality for the chat input
    const messageInput = document.getElementById('message');
    if (messageInput) {
      messageInput.addEventListener('keydown', function(event) {
        if (event.key === 'Enter' && !event.shiftKey) {
          event.preventDefault();
          const chatForm = document.getElementById('chatForm');
          if (chatForm) {
            chatForm.requestSubmit();
          }
        }
      });
    }
  }
});
  
  </script>
  <script src="{{ asset('js/timeago.js') }}"></script>
</body>
</html>
