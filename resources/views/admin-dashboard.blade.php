<!-- filepath: c:\Users\Admin\ASCC-ITv1-studentV1\ASCC-ITv1-student\resources\views\dashboard-admin.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Admin Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/admin-navbar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard-admin.css') }}">
  <link rel="stylesheet" href="{{ asset('css/notifications.css') }}">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pikaday/css/pikaday.css">
  <style>
    #calendar {
      visibility: visible;
      display: none; /* Hide the input field completely */
    }
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
      min-height: 500px;
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
      pointer-events: auto; /* Keep auto for hover tooltips to work */
      cursor: default; /* Change cursor to default - no pointer cursor */
      user-select: none; /* Prevent text selection */
    }
    .pika-button:hover, .pika-row.pick-whole-week:hover .pika-button {
      color: #fff !important;
      background: #cac7c7;
      box-shadow: none;
      border-radius: 3px;
    }
    
    /* Force white text on ALL calendar buttons regardless of status */
    .pika-button.status-pending,
    .pika-button.status-approved, 
    .pika-button.status-completed,
    .pika-button.status-rescheduled,
    .pika-button.has-booking {
      color: #ffffff !important;
    }
    
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
      color: #ffffff !important;
      background-color: #12372a !important;
      box-shadow: none;
    }
    .is-today .pika-button {
      color: #fff !important;
      background-color: #5fb9d4;
      font-weight: bold;
    }

    .has-booking {
      border-radius: 4px;
      position: relative;
      color: #fff !important;
      font-weight: bold
    }

    .calendar-box{
      height: 100%;
      width: 100%;
      max-width: 1000px;
    }

    /* Mobile responsive styles */
    @media (max-width: 768px) {
      .pika-button {
        pointer-events: auto !important; /* Keep for hover tooltips */
        cursor: default;
        user-select: none;
      }
      
      /* Disable hover effects on mobile to prevent sticky hover states */
      .pika-button:hover {
        background: #cac7c7 !important;
        transform: none !important;
      }
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

  /* Override badges (holiday/suspended/force) */
    .pika-button .ov-badge {
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
    }
  /* Override badge colors (admin) */
  .ov-holiday { background: #9B59B6; } /* Holiday → Violet */
  .ov-blocked { background: #374151; } /* Suspended → Dark Gray (unchanged) */
  .ov-force   { background: #2563eb; } /* Forced Online → Blue (reverted) */
  .ov-online  { background: #FF69B4; } /* Online Day → Pink */
  /* Whole-cell background for overrides */
  .day-holiday  { background-color: rgba(155,89,182,0.55) !important; } /* Violet */
  .day-blocked  { background-color: rgba(55,65,81,0.75) !important; }  /* Suspended */
  .day-force    { background-color: rgba(37,99,235,0.6) !important; }   /* Blue (reverted) */
  .day-online   { background-color: rgba(255,105,180,0.45) !important; }/* Pink */

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

    #consultationTooltip .professor-info {
      font-size: 11px;
      color: #2c5f4f;
      font-weight: 500;
    }

    /* Modal Styles */
    .modal {
      position: fixed;
      z-index: 10000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .modal-content {
      background-color: white;
      border-radius: 12px;
      padding: 0;
      max-width: 600px;
      width: 90%;
      max-height: 80vh;
      overflow-y: auto;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
      font-family: 'Poppins', sans-serif;
    }

    .modal-header {
      background: #12372a;
      color: white;
      padding: 20px 25px;
      border-radius: 12px 12px 0 0;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .modal-header h3 {
      margin: 0;
      font-size: 20px;
      font-weight: 600;
    }

    .modal-close {
      font-size: 28px;
      font-weight: bold;
      cursor: pointer;
      line-height: 1;
      opacity: 0.8;
      transition: opacity 0.2s;
    }

    .modal-close:hover {
      opacity: 1;
    }

    .modal-body {
      padding: 25px;
    }

    .consultation-detail-card {
      background: #f8f9fa;
      border-radius: 8px;
      padding: 20px;
      margin-bottom: 15px;
      border-left: 4px solid #12372a;
    }

    .consultation-detail-card h4 {
      color: #12372a;
      margin: 0 0 15px 0;
      font-size: 18px;
      font-weight: 600;
    }

    .detail-row {
      display: flex;
      margin-bottom: 10px;
      padding: 5px 0;
      border-bottom: 1px solid #e9ecef;
    }

    .detail-row:last-child {
      border-bottom: none;
      margin-bottom: 0;
    }

    .detail-label {
      font-weight: 600;
      color: #495057;
      width: 150px;
      flex-shrink: 0;
    }

    .detail-value {
      color: #343a40;
      flex: 1;
    }

    .status-badge {
      display: inline-block;
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
      text-transform: uppercase;
    }

    /* Tooltip overrides: remove borders & extra spacing for Subject/Type/Mode/Status inside admin tooltip */
    #consultationTooltip .detail-row,
    #consultationTooltip .status-row {
      display: block; /* stack nicely */
      border: none !important;
      padding: 0 !important;
      margin: 0 0 4px 0 !important;
    }
    #consultationTooltip .detail-row:last-child { margin-bottom: 4px !important; }

  /* Updated: make pending badge text white for consistency */
  /* Match legend colors exactly for status badges */
  .status-pending { background: #ffa600; color: #ffffff !important; }
    .status-approved { background: #0f9657; color: #ffffff !important; }
    .status-completed { background: #093b2f; color: #ffffff !important; }
    .status-rescheduled { background: #c50000; color: #ffffff !important; }

    /* Legend toggle + panel (collapsible) */
    .calendar-box { position: relative; }
    .legend-toggle {
      position: fixed; /* stay visible while scrolling */
      left: 20px;
      bottom: 20px; /* docked in the bottom-left corner */
      z-index: 12000;
      background: #14b8a6; /* teal */
      color: #fff;
      border: none;
      width: 48px; height: 48px; /* consistent FAB size */
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      cursor: pointer;
      box-shadow: 0 10px 20px rgba(0,0,0,0.25);
      transition: transform .15s ease, background-color .15s ease, box-shadow .2s ease;
    }
    .legend-toggle:hover { background:#0d9488; transform: scale(1.05); }
    .legend-toggle:focus-visible { outline: none; box-shadow: 0 0 0 3px rgba(20,184,166,.35), 0 10px 20px rgba(0,0,0,0.25); }
    /* Chatbot FAB: bottom-right corner, same look as legend */
    .chat-button {
      position: fixed;
      right: 20px;
      bottom: 20px;
      z-index: 12000;
      background: #14b8a6; /* teal */
      color: #fff;
      border: none;
      width: 48px; height: 48px;
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      cursor: pointer;
      box-shadow: 0 10px 20px rgba(0,0,0,0.25);
      transition: transform .15s ease, background-color .15s ease, box-shadow .2s ease;
    }
    .chat-button:hover { background:#0d9488; transform: scale(1.05); }
    .chat-button:focus-visible { outline: none; box-shadow: 0 0 0 3px rgba(20,184,166,.35), 0 10px 20px rgba(0,0,0,0.25); }

    .legend-backdrop {
      position: fixed; inset: 0;
      background: rgba(0,0,0,0.2);
      z-index: 11000;
      opacity: 0; visibility: hidden;
      transition: opacity .2s ease, visibility .2s ease;
    }
    .legend-backdrop.open { opacity: 1; visibility: visible; }

    .legend-panel {
      position: fixed;
      left: 24px; bottom: 80px; /* float above the bottom-left FAB on desktop */
      width: 420px; max-width: calc(100vw - 32px);
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.25);
      transform: translateY(10px);
      transition: transform .25s ease, opacity .25s ease;
      opacity: 0;
    }
    .legend-backdrop.open .legend-panel { transform: translateY(0); opacity: 1; }
    .legend-header { display:flex; align-items:center; justify-content:space-between; padding:12px 14px; border-bottom:1px solid #e5e7eb; }
    .legend-header h3 { margin:0; font-size:16px; color:#12372a; }
    .legend-close { background:none; border:none; font-size:22px; line-height:1; cursor:pointer; color:#334155; }
    .legend-content { padding:12px 14px 14px; }
    .legend-section { margin-bottom: 12px; }
    .legend-section-title { font-weight:600; color:#0f172a; margin: 0 0 8px 0; font-size:14px; }
  .legend-grid { display:grid; grid-template-columns: 1fr 1fr; gap: 8px 16px; }
  .legend-item { display:flex; align-items:center; font-size: 13px; color:#111827; }
    .legend-swatch { width:16px; height:16px; border-radius:3px; margin-right:8px; box-shadow: inset 0 0 0 1px rgba(0,0,0,0.06); }
  .legend-icon { font-size:16px; color:#6b7280; margin-left:6px; }
    /* Swatch colors (match calendar colors) */
    .swatch-pending { background:#ffa600; }
    .swatch-approved { background:#0f9657; }
    .swatch-completed { background:#093b2f; }
    .swatch-rescheduled { background:#c50000; }
  .swatch-suspended { background:#374151; }
  .swatch-online { background:#FF69B4; }   /* Online Day → Pink */
  .swatch-forced { background:#2563eb; }   /* Forced Online → Blue (reverted) */
  .swatch-holiday { background:#9B59B6; }  /* Holiday → Violet */
  .swatch-multiple { background:#FF4500; } /* Multiple Bookings → Orangey-Red */

    /* Drawer behavior on small screens */
    @media (max-width: 768px) {
      .legend-panel {
        left: 0; right: 0; bottom: 0; width: auto; max-width: none;
        border-radius: 12px 12px 0 0;
        transform: translateY(100%);
        padding-bottom: 84px; /* avoid overlap with corner FABs on mobile */
      }
      .legend-backdrop.open .legend-panel { transform: translateY(0); }
      .legend-grid { grid-template-columns: 1fr; }
      .legend-toggle { left: 12px; bottom: 12px; }
      .chat-button { right: 12px; bottom: 12px; }
    }

    /* Position the legend button just to the right of the left sidebar (desktop only) */
    @media (min-width: 951px) {
      .legend-toggle { left: calc(220px + 20px) !important; }
      .legend-panel { left: calc(220px + 24px) !important; }
    }
  </style>
</head>
<body>
  @include('components.navbar-admin')

  <div class="main-content">
    <div class="header">
      <h1>Consultation Activity</h1>
    </div>
    <div class="flex-layout">
      <div class="calendar-box">
        <div class="calendar-wrapper-container">
          <input id="calendar" type="text" placeholder="Select Date" name="booking_date" required>
        </div>
        <!-- Collapsible legend: toggle button + panel -->
        <button id="legendToggle" class="legend-toggle" aria-haspopup="dialog" aria-controls="legendBackdrop" aria-label="View Legend" title="View Legend">
          <svg width="22" height="22" viewBox="0 0 24 24" aria-hidden="true" focusable="false" style="color:#fff">
            <path fill="currentColor" d="M12 2a10 10 0 1 0 0 20a10 10 0 0 0 0-20zm0 7a1.25 1.25 0 1 1 0-2.5a1.25 1.25 0 0 1 0 2.5zM11 11h2v6h-2z"/>
          </svg>
        </button>
        <div id="legendBackdrop" class="legend-backdrop" aria-hidden="true">
          <div class="legend-panel" role="dialog" aria-modal="true" aria-labelledby="legendTitle">
            <div class="legend-header">
              <h3 id="legendTitle">Legend</h3>
              <button id="legendClose" class="legend-close" aria-label="Close">×</button>
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
                ✓
              </button>
              <span id="unread-count" class="unread-count">0</span>
            </div>
          </div>
          <div class="inbox-content" id="notifications-container">
            <div class="loading-notifications">
              <i class='bx bx-loader-alt bx-spin'></i>
              <p>Loading notifications...</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- <button class="chat-button" onclick="toggleChat()">
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
  </div> --}}

  <!-- Consultation Tooltip (Admin version shows ALL consultations) -->
  <div id="consultationTooltip" style="display:none; position:absolute; z-index:9999; background:#fff; border:1px solid #ccc; border-radius:8px; padding:12px; max-width:320px; box-shadow:0 4px 12px rgba(0,0,0,0.15); font-family:'Poppins',sans-serif; font-size:13px;"></div>

  <!-- Consultation Details Modal -->
  <div id="consultationModal" class="modal" style="display:none;">
    <div class="modal-content">
      <div class="modal-header">
        <h3>Consultation Details</h3>
        <span class="modal-close" onclick="closeConsultationModal()">&times;</span>
      </div>
      <div class="modal-body" id="modalConsultationDetails">
        <div class="loading">Loading consultation details...</div>
      </div>
    </div>
  </div>

  <script src="{{ asset('js/dashboard.js') }}"></script>
  <script src="https://cdn.jsdelivr.net/npm/pikaday/pikaday.js"></script>
  <link rel="stylesheet" href="{{ asset('css/toast.css') }}">
  <link rel="stylesheet" href="{{ asset('css/confirm.css') }}">
  <script>
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
        container.innerHTML = '<div class="mobile-notification-item">No notifications</div>';
        return;
      }

      container.innerHTML = notifications.map(notification => `
        <div class="mobile-notification-item ${notification.is_read ? 'read' : 'unread'}" 
             onclick="markMobileNotificationAsRead(${notification.id})">
          <div class="mobile-notification-content">
            <div class="mobile-notification-title">${notification.title}</div>
            <div class="mobile-notification-message">${notification.message}</div>
            <div class="mobile-notification-time">${formatMobileTime(notification.created_at)}</div>
          </div>
          ${!notification.is_read ? '<div class="mobile-notification-dot"></div>' : ''}
        </div>
      `).join('');
    }

    function updateMobileNotificationBadge(count) {
      const badge = document.getElementById('mobileNotificationBadge');
      if (badge) {
        badge.textContent = count;
        badge.style.display = count > 0 ? 'flex' : 'none';
      }
    }

    function markMobileNotificationAsRead(notificationId) {
      fetch('/api/admin/notifications/mark-read', {
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
      fetch('/api/admin/notifications/mark-all-read', {
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

    function formatMobileTime(dateString) {
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
   
    // Initialize Pikaday immediately so the calendar renders without waiting for network calls
    (function initAdminCalendar() {
      const picker = new Pikaday({
        field: document.getElementById('calendar'),
        format: 'ddd, MMM DD YYYY',
        showDaysInNextAndPreviousMonths: true,
        firstDay: 1,
        bound: false,
        onDraw: function() {
          // Determine visible month key once per draw
          const baseForDraw = (function(){
            try { return getVisibleMonthBaseDate(); } catch(_) { const t=new Date(); return new Date(t.getFullYear(), t.getMonth(), 1); }
          })();
          const monthKeyForDraw = `${baseForDraw.getFullYear()}-${String(baseForDraw.getMonth()+1).padStart(2,'0')}`;
          const cells = document.querySelectorAll('.pika-button');
          cells.forEach(cell => {
            const day = cell.getAttribute('data-pika-day');
            const month = cell.getAttribute('data-pika-month');
            const year = cell.getAttribute('data-pika-year');
            if (day && month && year) {
              const cellDate = new Date(year, month, day);
              const key = cellDate.toDateString();
              const isoKey = `${cellDate.getFullYear()}-${String(cellDate.getMonth()+1).padStart(2,'0')}-${String(cellDate.getDate()).padStart(2,'0')}`;

              // Use overrides source with per-month sticky fallback so badges don't flicker
              const ovSource = (function(){
                if (window.adminOverrides && typeof window.adminOverrides === 'object') return window.adminOverrides;
                if (window.__adminOvCacheByMonth && window.__adminOvCacheByMonth[monthKeyForDraw]) return window.__adminOvCacheByMonth[monthKeyForDraw];
                // legacy single cache fallback
                if (window.__adminOvCache && typeof window.__adminOvCache === 'object') return window.__adminOvCache;
                return null;
              })();
              const haveOv = !!ovSource;

              // Only clear previous override visuals if we have some source to repaint from
              if (haveOv) {
                const oldBadge = cell.querySelector('.ov-badge');
                if (oldBadge) oldBadge.remove();
                cell.classList.remove('day-holiday','day-blocked','day-force','day-online');
              }

              // Render overrides badge if present (pulling from chosen source)
              if (haveOv && ovSource[isoKey] && ovSource[isoKey].length > 0) {
                const items = ovSource[isoKey];
                let chosen = null;
                for (const ov of items) { if (ov.effect === 'holiday') { chosen = ov; break; } }
                if (!chosen) { for (const ov of items) { if (ov.effect === 'block_all') { chosen = ov; break; } } }
                if (!chosen) { chosen = items[0]; }
                const badge = document.createElement('span');
                const isOnlineDay = (chosen.effect === 'force_mode' && (chosen.reason_key === 'online_day'));
                const chosenCls = (chosen.effect === 'holiday' ? 'ov-holiday' : (chosen.effect === 'block_all' ? 'ov-blocked' : (isOnlineDay ? 'ov-online' : 'ov-force')));
                badge.className = 'ov-badge ' + chosenCls;
                const forceLabel = isOnlineDay ? 'Online Day' : 'Forced Online';
                badge.title = chosen.label || chosen.reason_text || (chosen.effect === 'force_mode' ? forceLabel : chosen.effect);
                badge.textContent = chosen.effect === 'holiday' ? (chosen.reason_text || 'Holiday') : (chosen.effect === 'block_all' ? 'Suspended' : forceLabel);
                cell.style.position = 'relative';
                cell.appendChild(badge);
                const dayCls = (chosen.effect === 'holiday' ? 'day-holiday' : (chosen.effect === 'block_all' ? 'day-blocked' : (isOnlineDay ? 'day-online' : 'day-force')));
                cell.classList.add(dayCls);
              }

              // Render booking status when data is available (bookingMap may initially be empty)
              if (bookingMap.has(key)) {
                const booking = bookingMap.get(key);
                const status = booking.status;
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
                if (consultationCount >= 2) cell.classList.add('has-multiple-bookings');
                cell.setAttribute('data-consultation-count', consultationCount);
                // Prepare hover data
                cell.setAttribute('data-consultation-key', key);
                cell.setAttribute('data-has-consultations', 'true');
                cell.style.cursor = 'default';
              }
            }
          });
          // Debug: show how many override days are available for the visible month
          try {
            const monthKey = `${baseForDraw.getFullYear()}-${String(baseForDraw.getMonth()+1).padStart(2,'0')}`;
            const src = (function(){
              if (window.adminOverrides && typeof window.adminOverrides === 'object') return window.adminOverrides;
              if (window.__adminOvCacheByMonth && window.__adminOvCacheByMonth[monthKey]) return window.__adminOvCacheByMonth[monthKey];
              if (window.__adminOvCache && typeof window.__adminOvCache === 'object') return window.__adminOvCache;
              return null;
            })();
            if (src) {
              const cnt = Object.keys(src).filter(k=>Array.isArray(src[k]) && src[k].length>0).length;
              console.debug('[OV] Draw visible month', monthKey, 'days:', cnt);
            } else {
              console.debug('[OV] Draw visible month', monthKey, 'no source yet');
            }
          } catch(_) {}
        }
      });
      picker.show();
      picker.draw();
      window.adminPicker = picker;
    })();

    // Kick off initial background fetches without blocking initial render
    (function initialFetches() {
      // Fetch overrides for visible month right away; fetchAdminOverridesForMonth will redraw on success
      try {
        const base = (function(){
          const t = new Date();
          return new Date(t.getFullYear(), t.getMonth(), 1);
        })();
        fetchAdminOverridesForMonth(base);
      } catch (e) { console.warn('Initial overrides fetch error:', e); }

      // Load consultations data; when it completes, loadAdminCalendarData will also refresh overrides as needed
      if (typeof loadAdminCalendarData === 'function') {
        loadAdminCalendarData();
      } else {
        // Fallback simple populate if function not yet defined (should be defined later)
        fetch('/api/admin/all-consultations')
          .then(r => r.json())
          .then(data => {
            bookingMap.clear();
            detailsMap.clear();
            data.forEach(entry => {
              const date = new Date(entry.Booking_Date);
              const key = date.toDateString();
              bookingMap.set(key, { status: entry.Status.toLowerCase(), id: entry.Booking_ID });
              if (!detailsMap.has(key)) detailsMap.set(key, []);
              detailsMap.get(key).push(entry);
            });
            if (window.adminPicker) window.adminPicker.draw();
          })
          .catch(err => console.warn('Initial consultations fetch failed:', err));
      }
    })();

    // Fetch overrides for current visible month and cache on window
    function fetchAdminOverridesForMonth(dateObj) {
      try {
        if (!dateObj || !(dateObj instanceof Date) || isNaN(dateObj.getTime())) {
          console.warn('Overrides fetch skipped: invalid base date', dateObj);
          return;
        }
        const start = new Date(dateObj.getFullYear(), dateObj.getMonth(), 1);
        const end = new Date(dateObj.getFullYear(), dateObj.getMonth() + 1, 0);
        const monthKey = `${start.getFullYear()}-${String(start.getMonth()+1).padStart(2,'0')}`;
        const toIso = (d) => {
          if (!d || !(d instanceof Date) || isNaN(d.getTime())) return null;
          const y = d.getFullYear();
          const m = String(d.getMonth() + 1).padStart(2, '0');
          const day = String(d.getDate()).padStart(2, '0');
          return `${y}-${m}-${day}`;
        };
        const startStr = toIso(start);
        const endStr = toIso(end);
        if (!startStr || !endStr) {
          console.warn('Overrides fetch skipped: start/end invalid', { start, end });
          return;
        }
        const adminUrl = `/api/admin/calendar/overrides?start_date=${startStr}&end_date=${endStr}`;
        const publicUrl = `/api/calendar/overrides?start_date=${startStr}&end_date=${endStr}`;
        console.debug('[OV] Fetching admin overrides for', monthKey, adminUrl);
        fetch(adminUrl, {
        method: 'GET',
        headers: { 'Accept':'application/json' },
        credentials: 'same-origin'
      }).then(async r=>{
        if (!r.ok) {
          console.warn('[OV] Admin overrides HTTP status', r.status);
          throw new Error('http_' + r.status);
        }
        let data;
        try {
          data = await r.json();
        } catch(jsonErr) {
          console.warn('[OV] Admin overrides non-JSON response, keeping cache', jsonErr);
          throw new Error('non_json');
        }
        if (data && data.success) {
          const incoming = data.overrides || {};
          const keys = Object.keys(incoming).filter(k=>Array.isArray(incoming[k]) && incoming[k].length>0);
          console.debug('[OV] Admin overrides loaded', { monthKey, days: keys.length, sample: keys.slice(0,5) });
          // Init per-month cache
          if (!window.__adminOvCacheByMonth) window.__adminOvCacheByMonth = {};
          window.__adminOvCacheByMonth[monthKey] = incoming;
          // Only update live overrides if the fetched month matches the currently visible month
          const visibleBase = (function(){
            try { return getVisibleMonthBaseDate(); } catch(_) { const t=new Date(); return new Date(t.getFullYear(), t.getMonth(), 1); }
          })();
          const visibleKey = `${visibleBase.getFullYear()}-${String(visibleBase.getMonth()+1).padStart(2,'0')}`;
          if (visibleKey === monthKey) {
            window.adminOverrides = incoming;
            // Legacy single cache for older paths
            window.__adminOvCache = incoming;
            // Re-draw if picker exists to paint badges
            if (window.adminPicker) window.adminPicker.draw();
          }
        } else {
          console.warn('[OV] Admin overrides payload not successful', data);
          throw new Error('payload_unsuccessful');
        }
      }).catch((e) => {
        console.warn('[OV] Admin overrides fetch failed, will try public fallback', e && e.message);
        // Public fallback: global overrides only (fine for Suspended/Holiday/Online Day global cases)
        fetch(publicUrl, { method:'GET', headers:{'Accept':'application/json'} })
          .then(r=>r.ok ? r.json() : Promise.reject(new Error('public_http_'+r.status)))
          .then(data=>{
            if (data && data.success) {
              const incoming = data.overrides || {};
              const keys = Object.keys(incoming).filter(k=>Array.isArray(incoming[k]) && incoming[k].length>0);
              console.debug('[OV/FALLBACK] Public overrides loaded', { monthKey, days: keys.length, sample: keys.slice(0,5) });
              if (!window.__adminOvCacheByMonth) window.__adminOvCacheByMonth = {};
              window.__adminOvCacheByMonth[monthKey] = incoming;
              const visibleBase = (function(){
                try { return getVisibleMonthBaseDate(); } catch(_) { const t=new Date(); return new Date(t.getFullYear(), t.getMonth(), 1); }
              })();
              const visibleKey = `${visibleBase.getFullYear()}-${String(visibleBase.getMonth()+1).padStart(2,'0')}`;
              if (visibleKey === monthKey) {
                // Note: mark as fallback source
                window.adminOverrides = incoming;
                window.__adminOvCache = incoming;
                if (window.adminPicker) window.adminPicker.draw();
              }
            } else {
              console.warn('[OV/FALLBACK] Public overrides payload not successful', data);
            }
          })
          .catch(err=>{
            console.warn('[OV/FALLBACK] Public overrides failed', err && err.message);
          });
      });
      } catch (err) {
        console.error('Admin Error loading calendar data:', err);
      }
    }

    // Helper: find the currently visible calendar month as a safe Date (YYYY,MM,1)
    function getVisibleMonthBaseDate() {
      try {
        // 1) Prefer Pikaday select elements (most reliable)
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
        // 2) Parse label; support full and short month names
        const labelEl = document.querySelector('.pika-label');
        if (labelEl) {
          const text = (labelEl.textContent || '').trim();
          const parts = text.split(/\s+/);
          if (parts.length === 2) {
            const monthMap = {
              January:0, February:1, March:2, April:3, May:4, June:5, July:6, August:7, September:8, October:9, November:10, December:11,
              Jan:0, Feb:1, Mar:2, Apr:3, Jun:5, Jul:6, Aug:7, Sep:8, Oct:9, Nov:10, Dec:11
            };
            const m = monthMap[parts[0]];
            const y = parseInt(parts[1], 10);
            if (!isNaN(m) && !isNaN(y)) {
              const d = new Date(y, m, 1);
              if (!isNaN(d.getTime())) return d;
            }
          }
        }
        // 3) Fallback: use any current-month day cell if available
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

    // Hook into month navigation to refresh overrides
    (function observeMonthNavigation(){
      const calendarEl = document.getElementById('calendar');
      if (!calendarEl) return;
      const run = () => {
        const base = getVisibleMonthBaseDate();
        fetchAdminOverridesForMonth(base);
      };
      // Initial load
      setTimeout(run, 100);
      // Re-run on next/prev clicks
      document.addEventListener('click', (e)=>{
        const t = e.target;
        if (t.closest && (t.closest('.pika-prev') || t.closest('.pika-next'))) {
          setTimeout(run, 150);
        }
      });
    })();

    // ADMIN TOOLTIP HOVER FUNCTIONALITY
    console.log('Setting up ADMIN global hover event delegation...');

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
          
          console.log('Admin Hovering over cell with key:', key);
          const consultations = detailsMap.get(key) || [];
          console.log('Admin Consultations found:', consultations);
          
          if (consultations.length === 0) {
            return;
          }
          
          let html = '';
          
          // Add header with consultation count
          const countText = consultations.length === 1 ? '1 Consultation' : `${consultations.length} Consultations`;
          html += `<div style="font-weight: bold; margin-bottom: 8px; color: #12372a; border-bottom: 1px solid #ddd; padding-bottom: 4px;">${countText}</div>`;
          
          // Helper to convert 'YYYY-MM-DD HH:MM:SS' to 'YYYY-MM-DD hh:MM:SS AM/PM'
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

          // Build each consultation entry with ONLY a bottom divider like professor dashboard (no additional top borders)
          consultations.forEach((entry, index) => {
            html += `
              <div class="consultation-entry">
                <div class="student-name">${entry.student} have consultation with ${entry.professor}</div>
                <div class="detail-row">Subject: ${entry.subject}</div>
                <div class="detail-row">Type: ${entry.type}</div>
                <div class="detail-row">Mode: ${entry.Mode}</div>
                <div class="status-row" style="color:${getStatusColor(entry.Status)};">Status: ${entry.Status}</div>
                <div class="booking-time">Booked: ${formatTo12Hour(entry.Created_At)}</div>
              </div>
            `;
          });
          
          if (!tooltip) {
            console.error('Admin Tooltip element not found!');
            return;
          }
          
          tooltip.innerHTML = html;
          tooltip.style.display = 'block';

          // Anchor tooltip to the RIGHT of the hovered cell (consistent placement)
          const cellRect = target.getBoundingClientRect();
          const tooltipRect = tooltip.getBoundingClientRect();
          const viewportHeight = window.innerHeight;
          const scrollY = window.scrollY || document.documentElement.scrollTop;
          const scrollX = window.scrollX || document.documentElement.scrollLeft;
          const GAP = 12; // space between day cell and tooltip

          // Base positions
          let left = cellRect.right + GAP + scrollX;
          let top = cellRect.top + scrollY; // align tops by default

          // Vertical adjustments to keep fully in view
          if (top + tooltipRect.height > scrollY + viewportHeight - 10) {
            top = scrollY + viewportHeight - tooltipRect.height - 10;
          }
          if (top < scrollY + 10) {
            top = scrollY + 10;
          }

          // Optional: if extremely close to right edge and would overflow, gently shift left but keep to the right side
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

    console.log('Admin Global hover delegation system initialized');

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

    // PREVENT ONLY CLICK AND TOUCH EVENTS ON CALENDAR DATE CELLS, ALLOW HOVER
    // Allow clicks when admin date edit modal is enabled
    window.ADMIN_DATE_EDIT_ENABLED = true;
    function preventCalendarClicks(e) {
      const target = e.target;
      // Only prevent clicks/touches on date buttons inside the table, not navigation buttons
      // Allow mouseover/mouseout for tooltips
      if (window.ADMIN_DATE_EDIT_ENABLED) {
        return; // allow click through for admin edit
      }
      if (target && target.classList && target.classList.contains('pika-button') && target.closest('.pika-table')) {
        if (e.type === 'click' || e.type === 'mousedown' || e.type === 'touchstart' || e.type === 'touchend') {
          e.preventDefault();
          e.stopPropagation();
          e.stopImmediatePropagation();
          console.log('Admin Calendar date interaction prevented:', e.type);
          return false;
        }
      }
    }

    // Prevent only specific events that cause date selection
    ['click', 'mousedown', 'touchstart', 'touchend'].forEach(eventType => {
      document.addEventListener(eventType, preventCalendarClicks, true); // Capture phase
      document.addEventListener(eventType, preventCalendarClicks, false); // Bubble phase
    });

    // --- Admin Date Edit Modal ---
    // Modal template
    const modalHtml = `
      <div id="adminOverrideBackdrop" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.35);z-index:9998;"></div>
      <div id="adminOverrideModal" style="display:none;position:fixed;left:50%;top:50%;transform:translate(-50%,-50%);z-index:9999;background:#fff;border-radius:10px;box-shadow:0 10px 30px rgba(0,0,0,0.2);width:560px;max-width:92vw;">
        <div style="padding:14px 18px;border-bottom:1px solid #e5e7eb;display:flex;align-items:center;justify-content:space-between;background:#12372a;color:#fff;border-top-left-radius:10px;border-top-right-radius:10px;">
          <div style="font-weight:600">Edit Day</div>
          <button id="adminOverrideClose" style="background:transparent;border:none;color:#fff;font-size:18px;cursor:pointer">×</button>
        </div>
        <div style="padding:16px 18px;color:#12372a;">
          <div style="margin-bottom:10px;font-size:14px">Date: <span id="adminOverrideDate" style="font-weight:600"></span></div>
          <div style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:10px">
            <label style="display:flex;gap:8px;align-items:center"><input type="radio" name="ov_effect" value="online_day" checked> Online Day</label>
            <label style="display:flex;gap:8px;align-items:center"><input type="radio" name="ov_effect" value="force_online"> Forced Online</label>
            <label style="display:flex;gap:8px;align-items:center"><input type="radio" name="ov_effect" value="block_all"> Suspended</label>
            <label style="display:flex;gap:8px;align-items:center"><input type="radio" name="ov_effect" value="holiday"> Holiday</label>
          </div>
          <div id="forceModeRow" style="margin-bottom:10px; display:none">
            <label>Allowed Mode:
              <select id="ov_allowed_mode" style="margin-left:8px;padding:6px 8px;border:1px solid #cbd5e1;border-radius:6px">
                <option value="online">Online</option>
                <option value="onsite">Onsite</option>
              </select>
            </label>
          </div>
          <div id="reasonRow" style="display:flex;gap:12px;flex-wrap:wrap;margin:10px 0">
            <label>Reason
              <select id="ov_reason_key" style="margin-left:8px;padding:6px 8px;border:1px solid #cbd5e1;border-radius:6px">
                <option value="">—</option>
                <option value="weather">Weather</option>
                <option value="power_outage">Power outage</option>
                <option value="health_advisory">Health advisory</option>
                <option value="holiday_shift">Holiday shift</option>
                <option value="facility">Facility issue</option>
                <option value="others">Others</option>
              </select>
            </label>
            <input id="ov_reason_text" placeholder="Notes (optional)" style="flex:1;min-width:200px;padding:6px 8px;border:1px solid #cbd5e1;border-radius:6px"></input>
          </div>
          <div id="holidayRow" style="display:none;margin:10px 0">
            <label>Holiday Name
              <input id="ov_holiday_name" placeholder="e.g., Christmas Day" style="margin-left:8px;padding:6px 8px;border:1px solid #cbd5e1;border-radius:6px;width:calc(100% - 8px)">
              </input>
            </label>
          </div>
          <div id="autoReschedRow" style="display:none;margin:4px 0 12px 0">
            <label style="display:flex;gap:8px;align-items:center"><input type="checkbox" id="ov_auto_reschedule"> Auto‑reschedule affected bookings</label>
            <div style="font-size:12px;color:#64748b;margin-top:6px">Exam/Quiz bookings will be placed first into onsite slots. Others will follow mode rules.</div>
          </div>
          <div id="ov_preview" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:10px;margin-top:6px;display:none"></div>
        </div>
        <div style="padding:12px 18px;border-top:1px solid #e5e7eb;display:flex;gap:10px;justify-content:space-between;align-items:center">
          <button id="ovRemoveBtn" style="padding:8px 12px;border:1px solid #ef4444;border-radius:8px;background:#fff;color:#b91c1c;cursor:pointer">Remove</button>
          <div style="display:flex;gap:10px">
            <button id="ovPreviewBtn" style="padding:8px 12px;border:1px solid #cbd5e1;border-radius:8px;background:#fff;color:#12372a;cursor:pointer">Preview</button>
            <button id="ovApplyBtn" style="padding:8px 12px;border:none;border-radius:8px;background:#12372a;color:#fff;cursor:pointer">Apply</button>
          </div>
        </div>
      </div>`;

    // Inject modal once
    if (!document.getElementById('adminOverrideModal')) {
      const wrap = document.createElement('div');
      wrap.innerHTML = modalHtml;
      document.body.appendChild(wrap);
    }

    // Helper: determine if a given date has any override applied (by data map or DOM fallback)
    function hasOverrideForDate(dateStr) {
      try {
        const dt = new Date(dateStr);
        const iso = isNaN(dt.getTime()) ? null : `${dt.getFullYear()}-${String(dt.getMonth()+1).padStart(2,'0')}-${String(dt.getDate()).padStart(2,'0')}`;
        if (iso && window.adminOverrides && window.adminOverrides[iso] && window.adminOverrides[iso].length > 0) {
          return true;
        }
        // Fallback: scan DOM for a badge or override day class on the specific cell
        const cells = document.querySelectorAll('.pika-button');
        for (const cell of cells) {
          const d = new Date(
            cell.getAttribute('data-pika-year'),
            cell.getAttribute('data-pika-month'),
            cell.getAttribute('data-pika-day')
          );
          if (d.toDateString() === dateStr) {
            if (cell.querySelector('.ov-badge')) return true;
            if (cell.classList.contains('day-holiday') || cell.classList.contains('day-blocked') || cell.classList.contains('day-force')) return true;
            break;
          }
        }
      } catch (e) { /* noop */ }
      return false;
    }

    function openOverrideModal(dateStr) {
      const modal = document.getElementById('adminOverrideModal');
      const backdrop = document.getElementById('adminOverrideBackdrop');
      document.getElementById('adminOverrideDate').textContent = dateStr;
      modal.style.display = 'block';
      backdrop.style.display = 'block';
      // Toggle Remove button availability based on whether an override exists on that date
      const removeBtn = document.getElementById('ovRemoveBtn');
      if (removeBtn) {
        const exists = hasOverrideForDate(dateStr);
        removeBtn.disabled = !exists;
        removeBtn.setAttribute('aria-disabled', String(!exists));
        removeBtn.title = exists ? 'Remove existing override' : 'No override on this date';
        // visual state
        removeBtn.style.opacity = exists ? '1' : '0.5';
        removeBtn.style.cursor = exists ? 'pointer' : 'not-allowed';
      }
      // Ensure rows reflect the currently selected option (default Online Day hides reason/notes)
      if (typeof updateOverrideRows === 'function') updateOverrideRows();
    }
    function closeOverrideModal() {
      const modal = document.getElementById('adminOverrideModal');
      const backdrop = document.getElementById('adminOverrideBackdrop');
      modal.style.display = 'none';
      backdrop.style.display = 'none';
      const preview = document.getElementById('ov_preview');
      if (preview) { preview.style.display = 'none'; preview.innerHTML=''; }
    }
    (function(){
      const closeBtn = document.getElementById('adminOverrideClose');
      if (closeBtn) closeBtn.addEventListener('click', closeOverrideModal);
      const backdrop = document.getElementById('adminOverrideBackdrop');
      if (backdrop) backdrop.addEventListener('click', closeOverrideModal);
    })();

    // Centralized UI toggle for modal rows
    function updateOverrideRows() {
      const effect = document.querySelector('input[name="ov_effect"]:checked')?.value;
      const forceRow = document.getElementById('forceModeRow');
      const autoRow = document.getElementById('autoReschedRow');
      const reasonRow = document.getElementById('reasonRow');
      const holidayRow = document.getElementById('holidayRow');
      const reasonKeyEl = document.getElementById('ov_reason_key');
      const reasonTextEl = document.getElementById('ov_reason_text');

  if (forceRow) forceRow.style.display = 'none';
  // Show auto-reschedule for Suspended and Forced Online
  if (autoRow) autoRow.style.display = (effect === 'block_all' || effect === 'force_online') ? 'block' : 'none';

      const hideReasons = (effect === 'online_day' || effect === 'holiday');
      if (reasonRow) reasonRow.style.display = hideReasons ? 'none' : 'flex';
      if (holidayRow) holidayRow.style.display = effect === 'holiday' ? 'block' : 'none';

      // Disable and clear reason inputs when hidden
      if (reasonKeyEl) {
        reasonKeyEl.disabled = hideReasons;
        if (hideReasons) reasonKeyEl.value = '';
      }
      if (reasonTextEl) {
        reasonTextEl.disabled = hideReasons;
        if (hideReasons) {
          reasonTextEl.value = '';
        } else {
          // Dynamic placeholder: if Others selected, prompt to enter the reason
          const rk = reasonKeyEl ? reasonKeyEl.value : '';
          if (rk === 'others') {
            reasonTextEl.placeholder = 'Enter reason';
          } else {
            reasonTextEl.placeholder = 'Notes (optional)';
          }
        }
      }
    }
    // Change handler for reason key to toggle placeholder and ensure input is enabled when Others
    document.addEventListener('change', function(e){
      if (e.target && e.target.id === 'ov_reason_key') {
        const reasonTextEl = document.getElementById('ov_reason_text');
        const val = e.target.value;
        if (reasonTextEl) {
          reasonTextEl.placeholder = (val === 'others') ? 'Enter reason' : 'Notes (optional)';
          // Ensure enabled for Others
          if (val === 'others') reasonTextEl.disabled = false;
        }
      }
    });

    // Wire change handler for radios
    document.addEventListener('change', function(e){
      if (e.target && e.target.name === 'ov_effect') {
        updateOverrideRows();
      }
    });

    // Click handler on date cells to open modal (robust delegation)
    document.addEventListener('click', function(e){
      if (!window.ADMIN_DATE_EDIT_ENABLED) return;
      const btn = e.target && e.target.closest ? e.target.closest('.pika-button') : null;
      if (!btn) return;
      // Ensure it's inside the calendar table, not prev/next buttons
      if (!btn.closest('.pika-table')) return;
      const year = btn.getAttribute('data-pika-year');
      const month = btn.getAttribute('data-pika-month');
      const day = btn.getAttribute('data-pika-day');
      if (year && month && day) {
        const d = new Date(year, month, day);
        const dateStr = d.toDateString();
        openOverrideModal(dateStr);
      }
    });

    // Also handle mousedown early in capture phase to beat any other handlers
    document.addEventListener('mousedown', function(e){
      if (!window.ADMIN_DATE_EDIT_ENABLED) return;
      const btn = e.target && e.target.closest ? e.target.closest('.pika-button') : null;
      if (!btn || !btn.closest('.pika-table')) return;
      const year = btn.getAttribute('data-pika-year');
      const month = btn.getAttribute('data-pika-month');
      const day = btn.getAttribute('data-pika-day');
      if (year && month && day) {
        // Prevent Pikaday from consuming the click if we're editing
        e.preventDefault();
        e.stopPropagation();
        const d = new Date(year, month, day);
        const dateStr = d.toDateString();
        openOverrideModal(dateStr);
      }
    }, true);

    // Preview and Apply actions
    function postJson(url, payload){
      return fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(payload)
      }).then(r => r.json());
    }

  const ovPreviewBtn = document.getElementById('ovPreviewBtn');
  if (ovPreviewBtn) ovPreviewBtn.addEventListener('click', function(){
      const sel = document.querySelector('input[name="ov_effect"]:checked').value;
      // Map UI selection into API effect/allowed_mode
      let effect = sel;
      let allowed = null;
      if (sel === 'force_online' || sel === 'online_day') { effect = 'force_mode'; allowed = 'online'; }
      let reason_key, reason_text;
      if (effect === 'holiday') {
        reason_key = 'holiday';
        reason_text = (document.getElementById('ov_holiday_name').value || '').trim();
      } else if (sel === 'online_day') {
        // Online Day: no reason/notes
        reason_key = 'online_day';
        reason_text = '';
      } else {
  // Forced Online / Suspended: allow reasons
        reason_key = document.getElementById('ov_reason_key').value;
        reason_text = document.getElementById('ov_reason_text').value;
        // If Others is selected, require a typed reason
        if ((sel === 'block_all' || sel === 'force_online') && reason_key === 'others') {
          if (!reason_text || !reason_text.trim()) {
            showToast('Please enter a reason for Others', 'error');
            return;
          }
        }
      }
      const dateLabel = document.getElementById('adminOverrideDate').textContent;
      const start = new Date(dateLabel);
      if (!(start instanceof Date) || isNaN(start.getTime())) {
        alert('Selected date is invalid. Please pick another day.');
        return;
      }
      const startIso = `${start.getFullYear()}-${String(start.getMonth()+1).padStart(2,'0')}-${String(start.getDate()).padStart(2,'0')}`;
      const payload = {
        start_date: startIso,
        effect: effect,
        allowed_mode: effect === 'force_mode' ? allowed : null,
        reason_key, reason_text,
        auto_reschedule: document.getElementById('ov_auto_reschedule').checked
      };
      postJson('/api/admin/calendar/overrides/preview', payload).then(data => {
        const box = document.getElementById('ov_preview');
        box.style.display = 'block';
        if (data && data.success) {
          let html = `<div><strong>Preview</strong></div><div>Affected bookings: ${data.affected_count}</div>`;
          if (typeof data.reschedule_candidate_count !== 'undefined') {
            html += `<div>Rescheduling candidates (exam/quiz): ${data.reschedule_candidate_count}</div>`;
          }
          box.innerHTML = html;
        } else {
          box.innerHTML = `<div style="color:#b91c1c">Failed to preview.</div>`;
        }
      }).catch(()=>{
        const box = document.getElementById('ov_preview');
        box.style.display = 'block';
        box.innerHTML = `<div style="color:#b91c1c">Failed to preview.</div>`;
      });
    });

  const ovApplyBtn = document.getElementById('ovApplyBtn');
  if (ovApplyBtn) ovApplyBtn.addEventListener('click', async function(){
      const sel = document.querySelector('input[name="ov_effect"]:checked').value;
      let effect = sel;
      let allowed = null;
      if (sel === 'force_online' || sel === 'online_day') { effect = 'force_mode'; allowed = 'online'; }
      let reason_key, reason_text;
      if (effect === 'holiday') {
        reason_key = 'holiday';
        reason_text = (document.getElementById('ov_holiday_name').value || '').trim();
      } else if (sel === 'online_day') {
        reason_key = 'online_day';
        reason_text = '';
      } else {
        reason_key = document.getElementById('ov_reason_key').value;
        reason_text = document.getElementById('ov_reason_text').value;
        // If Others is selected, require a typed reason
        if ((sel === 'block_all' || sel === 'force_online') && reason_key === 'others') {
          if (!reason_text || !reason_text.trim()) {
            showToast('Please enter a reason for Others', 'error');
            return;
          }
        }
      }
      const auto_reschedule = document.getElementById('ov_auto_reschedule').checked;
      const dateLabel = document.getElementById('adminOverrideDate').textContent;
      const start = new Date(dateLabel);
      if (!(start instanceof Date) || isNaN(start.getTime())) {
        alert('Selected date is invalid. Please pick another day.');
        return;
      }

      // Themed confirmation before applying
  const labelMap = { online_day: 'Online Day', force_online: 'Forced Online', block_all: 'Suspended', holiday: 'Holiday' };
      const humanLabel = labelMap[sel] || 'Change';
      const proceed = await themedConfirm(`Apply ${humanLabel}`, `Are you sure you want to apply "${humanLabel}" for <strong>${dateLabel}</strong>?`);
      if (!proceed) return;

      const startIso = `${start.getFullYear()}-${String(start.getMonth()+1).padStart(2,'0')}-${String(start.getDate()).padStart(2,'0')}`;
      const payload = {
        start_date: startIso,
        effect: effect,
        allowed_mode: effect === 'force_mode' ? allowed : null,
        reason_key, reason_text,
        auto_reschedule
      };
      postJson('/api/admin/calendar/overrides/apply', payload).then(data => {
        if (data && data.success) {
          closeOverrideModal();
          // refresh admin calendar data
          if (typeof loadAdminCalendarData === 'function') {
            loadAdminCalendarData();
          }
          // refresh overrides for current month to paint badges immediately
          try {
            const base = getVisibleMonthBaseDate();
            fetchAdminOverridesForMonth(base);
            // Also stamp the just-applied date immediately as a badge (fallback)
            const immediateDateStr = dateLabel; // e.g., Sun Dec 25 2025
            const ovItem = { effect, reason_text: reason_text, reason_key, allowed_mode: allowed };
            addBadgeForDate(immediateDateStr, ovItem);
          } catch(e) {}
          showToast('Changes applied', 'success');
        } else {
          alert('Failed to apply changes');
        }
      }).catch(()=> alert('Failed to apply changes'));

    // Helper: add badge and day-level background directly to a specific date cell (immediate feedback)
    function addBadgeForDate(dateStr, item) {
      const cells = document.querySelectorAll('.pika-button');
      for (const cell of cells) {
        const d = new Date(
          cell.getAttribute('data-pika-year'),
          cell.getAttribute('data-pika-month'),
          cell.getAttribute('data-pika-day')
        );
        if (d.toDateString() === dateStr) {
          // Remove any existing badge and override day classes
          const old = cell.querySelector('.ov-badge');
          if (old) old.remove();
          cell.classList.remove('day-holiday','day-blocked','day-force','day-online');

          // Create new badge
          const badge = document.createElement('span');
          const isOnline = (item.effect !== 'holiday' && item.effect !== 'block_all' && item.reason_key === 'online_day');
          const cls = item.effect === 'holiday' ? 'ov-holiday' : (item.effect === 'block_all' ? 'ov-blocked' : (isOnline ? 'ov-online' : 'ov-force'));
          badge.className = 'ov-badge ' + cls;
            const text = item.effect === 'holiday'
            ? (item.reason_text || 'Holiday')
            : (item.effect === 'block_all'
              ? 'Suspended'
              : (item.reason_key === 'online_day' ? 'Online Day' : 'Forced Online'));
          badge.textContent = text;
          badge.title = text;

          // Apply cell-level background to make it visually obvious immediately
          const dayCls = item.effect === 'holiday' ? 'day-holiday' : (item.effect === 'block_all' ? 'day-blocked' : (isOnline ? 'day-online' : 'day-force'));
          cell.classList.add(dayCls);

          cell.style.position = 'relative';
          cell.appendChild(badge);
          break;
        }
      }
    }

    // Helper: clear all temporary override badges and background classes on the visible calendar
    function resetCalendarHighlights() {
      try {
        const cells = document.querySelectorAll('.pika-table .pika-button');
        cells.forEach(cell => {
          // Remove any override badge
          const b = cell.querySelector('.ov-badge');
          if (b) b.remove();
          // Remove background classes
          cell.classList.remove('day-holiday','day-blocked','day-force','day-online');
        });
        // Clear any selected cells
        document.querySelectorAll('.pika-table td.is-selected').forEach(td => td.classList.remove('is-selected'));
        // Hide tooltip if visible
        const tooltip = document.getElementById('consultationTooltip');
        if (tooltip) tooltip.style.display = 'none';
        // Soft reset flag: do not change window.adminOverrides so persisted server overrides will return on next redraw
        console.log('Admin calendar highlights reset');
      } catch (e) {
        console.warn('Reset calendar highlights encountered an issue:', e);
      }
    }
    });

    // ADMIN NOTIFICATION FUNCTIONS
    // Mark all as read functionality
    (function(){
      const markAllBtn = document.getElementById('mark-all-read');
      if (markAllBtn) {
        markAllBtn.addEventListener('click', function() {
          markAllAdminNotificationsAsRead();
        });
      }
    })();

    // Remove overrides for selected date
    (function(){
      const btn = document.getElementById('ovRemoveBtn');
      if (!btn) return;
      btn.addEventListener('click', function(){
        const dateLabel = document.getElementById('adminOverrideDate').textContent;
        const start = new Date(dateLabel);
        if (!(start instanceof Date) || isNaN(start.getTime())) {
          alert('Selected date is invalid.');
          return;
        }

        // If there is no override for this date, do not proceed
        try {
          const exists = (function(){
            const iso = `${start.getFullYear()}-${String(start.getMonth()+1).padStart(2,'0')}-${String(start.getDate()).padStart(2,'0')}`;
            if (window.adminOverrides && window.adminOverrides[iso] && window.adminOverrides[iso].length > 0) return true;
            const cells = document.querySelectorAll('.pika-button');
            for (const cell of cells) {
              const d = new Date(
                cell.getAttribute('data-pika-year'),
                cell.getAttribute('data-pika-month'),
                cell.getAttribute('data-pika-day')
              );
              if (d.toDateString() === dateLabel) {
                if (cell.querySelector('.ov-badge')) return true;
                if (cell.classList.contains('day-holiday') || cell.classList.contains('day-blocked') || cell.classList.contains('day-force')) return true;
                break;
              }
            }
            return false;
          })();
          if (!exists) {
            showToast('No override on this date to remove', 'info');
            // keep button disabled to reflect state
            btn.disabled = true; btn.setAttribute('aria-disabled','true'); btn.style.opacity = '0.5'; btn.style.cursor = 'not-allowed';
            return;
          }
        } catch (e) { /* ignore and continue */ }

        // Themed confirmation before removing
        themedConfirm('Remove Override', `Are you sure you want to remove overrides for <strong>${dateLabel}</strong>?`).then(ok => {
          if (!ok) return;

        const startIso = `${start.getFullYear()}-${String(start.getMonth()+1).padStart(2,'0')}-${String(start.getDate()).padStart(2,'0')}`;
        postJson('/api/admin/calendar/overrides/remove', { start_date: startIso })
          .then(data => {
            if (data && data.success) {
              // Clear badge/background for that date immediately
              const cells = document.querySelectorAll('.pika-button');
              for (const cell of cells) {
                const d = new Date(
                  cell.getAttribute('data-pika-year'),
                  cell.getAttribute('data-pika-month'),
                  cell.getAttribute('data-pika-day')
                );
                if (d.toDateString() === dateLabel) {
                  const old = cell.querySelector('.ov-badge');
                  if (old) old.remove();
                  cell.classList.remove('day-holiday','day-blocked','day-force','day-online');
                  break;
                }
              }
              // Refresh month overrides
              const base = getVisibleMonthBaseDate();
              fetchAdminOverridesForMonth(base);
              // Close modal
              closeOverrideModal();
              showToast(data.deleted > 0 ? 'Override removed' : 'No override found', data.deleted > 0 ? 'success' : 'info');
              // After removal, ensure Remove stays disabled for this date
              try {
                const removeBtn = document.getElementById('ovRemoveBtn');
                if (removeBtn) { removeBtn.disabled = true; removeBtn.setAttribute('aria-disabled','true'); removeBtn.style.opacity = '0.5'; removeBtn.style.cursor = 'not-allowed'; }
              } catch(e) {}
            } else {
              alert('Failed to remove override');
            }
          })
          .catch(()=> alert('Failed to remove override'));
        });
      });
    })();
    // Themed toast + confirm helpers (aligned with site theme)
    function ensureToastWrapper() {
      let wrap = document.querySelector('.toast-wrapper');
      if (!wrap) {
        wrap = document.createElement('div');
        wrap.className = 'toast-wrapper';
        document.body.appendChild(wrap);
      }
      return wrap;
    }

    function showToast(message, type='info', timeout=2200) {
      const wrap = ensureToastWrapper();
      const toast = document.createElement('div');
      toast.className = `ascc-toast ${type==='success'?'ascc-toast-success': type==='error'?'ascc-toast-error':'ascc-toast-info'}`;
      toast.innerHTML = `<div>${message}</div><button class="ascc-toast-close" aria-label="Close">×</button>`;
      wrap.appendChild(toast);
      const closer = toast.querySelector('.ascc-toast-close');
      let hid = false;
      const hide = () => { if (hid) return; hid = true; toast.classList.add('hide'); setTimeout(()=> toast.remove(), 250); };
      closer.addEventListener('click', hide);
      setTimeout(hide, timeout);
    }

    function themedConfirm(title, htmlMessage) {
      return new Promise(resolve => {
        const overlay = document.createElement('div');
        overlay.className = 'ascc-confirm-overlay';
        const dlg = document.createElement('div');
        dlg.className = 'ascc-confirm';
        dlg.setAttribute('role', 'dialog');
        dlg.setAttribute('aria-modal', 'true');
        dlg.innerHTML = `
          <div class="ascc-confirm-header">
            <div class="ascc-confirm-title">${title}</div>
            <button class="ascc-confirm-close" aria-label="Close">×</button>
          </div>
          <div class="ascc-confirm-body">${htmlMessage}</div>
          <div class="ascc-confirm-actions">
            <button id="dlgCancel" class="ascc-btn ascc-btn-secondary">Cancel</button>
            <button id="dlgOk" class="ascc-btn ascc-btn-primary">Confirm</button>
          </div>
        `;
        overlay.appendChild(dlg);
        document.body.appendChild(overlay);

        const okBtn = dlg.querySelector('#dlgOk');
        const cancelBtn = dlg.querySelector('#dlgCancel');
        const closeBtn = dlg.querySelector('.ascc-confirm-close');

        const cleanup = () => {
          document.removeEventListener('keydown', onKey);
          overlay.remove();
        };
        const close = (val) => { cleanup(); resolve(val); };

        const onKey = (e) => {
          if (e.key === 'Escape') { e.preventDefault(); close(false); }
          if (e.key === 'Tab') {
            // basic focus trap
            const focusables = dlg.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
            if (focusables.length) {
              const first = focusables[0];
              const last = focusables[focusables.length - 1];
              if (e.shiftKey && document.activeElement === first) { last.focus(); e.preventDefault(); }
              else if (!e.shiftKey && document.activeElement === last) { first.focus(); e.preventDefault(); }
            }
          }
        };

        closeBtn.addEventListener('click', () => close(false));
        cancelBtn.addEventListener('click', () => close(false));
        okBtn.addEventListener('click', () => close(true));
        document.addEventListener('keydown', onKey);
        // Initial focus
        okBtn.focus();
      });
    }

    // (Reset button removed as requested)

  function loadAdminCalendarData() {
      fetch('/api/admin/all-consultations', {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          'Accept': 'application/json'
        }
      })
        .then(response => {
          console.log('Admin Real-time API Response status:', response.status);
          if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
          }
          return response.json();
        })
        .then(data => {
          console.log('Admin Real-time update - fetched data:', data.length, 'entries');
          // Ensure overrides for this month are loaded too
          const base = getVisibleMonthBaseDate();
          fetchAdminOverridesForMonth(base);
          
          // Store previous booking map for comparison
          const previousBookings = new Map();
          bookingMap.forEach((value, key) => {
            previousBookings.set(key, value);
          });
          
          bookingMap.clear(); // Clear existing data
          detailsMap.clear(); // Clear details data
          
          data.forEach(entry => {
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

          // Only update calendar cells if there are changes
          if (hasChanges && window.adminPicker) {
            const cells = document.querySelectorAll('.pika-button');
            cells.forEach(cell => {
              const cellDate = new Date(cell.getAttribute('data-pika-year'), cell.getAttribute('data-pika-month'), cell.getAttribute('data-pika-day'));
              const dateStr = cellDate.toDateString();
              const isoKey = `${cellDate.getFullYear()}-${String(cellDate.getMonth()+1).padStart(2,'0')}-${String(cellDate.getDate()).padStart(2,'0')}`;
              // Refresh override badges/classes on update, using per-month sticky cache to avoid flicker
              const visibleBaseNow = (function(){
                try { return getVisibleMonthBaseDate(); } catch(_) { const t=new Date(); return new Date(t.getFullYear(), t.getMonth(), 1); }
              })();
              const visKeyNow = `${visibleBaseNow.getFullYear()}-${String(visibleBaseNow.getMonth()+1).padStart(2,'0')}`;
              const ovSource = (function(){
                if (window.adminOverrides && typeof window.adminOverrides === 'object') return window.adminOverrides;
                if (window.__adminOvCacheByMonth && window.__adminOvCacheByMonth[visKeyNow]) return window.__adminOvCacheByMonth[visKeyNow];
                if (window.__adminOvCache && typeof window.__adminOvCache === 'object') return window.__adminOvCache;
                return null;
              })();
              if (ovSource) {
                const oldBadge = cell.querySelector('.ov-badge');
                if (oldBadge) oldBadge.remove();
                cell.classList.remove('day-holiday','day-blocked','day-force','day-online');
              }
              if (ovSource && ovSource[isoKey] && ovSource[isoKey].length > 0) {
                const items = ovSource[isoKey];
                let chosen = null;
                for (const ov of items) { if (ov.effect === 'holiday') { chosen = ov; break; } }
                if (!chosen) { for (const ov of items) { if (ov.effect === 'block_all') { chosen = ov; break; } } }
                if (!chosen) { chosen = items[0]; }
                const badge = document.createElement('span');
                // Distinguish Online Day vs Forced Online for clarity
                const isOnlineDay = (chosen.effect === 'force_mode' && (chosen.reason_key === 'online_day'));
                const chosenCls = (chosen.effect === 'holiday'
                  ? 'ov-holiday'
                  : (chosen.effect === 'block_all'
                    ? 'ov-blocked'
                    : (isOnlineDay ? 'ov-online' : 'ov-force')));
                badge.className = 'ov-badge ' + chosenCls;
                const forceLabel2 = isOnlineDay ? 'Online Day' : 'Forced Online';
                badge.title = chosen.label || chosen.reason_text || (chosen.effect === 'force_mode' ? forceLabel2 : chosen.effect);
                badge.textContent = chosen.effect === 'holiday' ? (chosen.reason_text || 'Holiday') : (chosen.effect === 'block_all' ? 'Suspended' : forceLabel2);
                cell.style.position = 'relative';
                cell.appendChild(badge);
                const dayCls = (chosen.effect === 'holiday'
                  ? 'day-holiday'
                  : (chosen.effect === 'block_all'
                    ? 'day-blocked'
                    : (isOnlineDay ? 'day-online' : 'day-force')));
                cell.classList.add(dayCls);
              }
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
                  
                  console.log('Admin Updated cell with global hover data:', key, 'Consultations:', consultationCount);
                }
              }
            });
          }
        })
        .catch(error => {
          console.error('Admin Error loading calendar data:', error);
        });
    }

    let adminNotificationsHash = '';

    function loadAdminNotifications() {
      fetch('/api/admin/notifications')
        .then(response => response.json())
        .then(data => {
          // Create a hash of the notifications to detect changes
          const notificationsString = JSON.stringify(data.notifications);
          const currentHash = btoa(notificationsString);
          
          // Only update if notifications have changed
          if (currentHash !== adminNotificationsHash) {
            adminNotificationsHash = currentHash;
            displayAdminNotifications(data.notifications);
            updateAdminUnreadCount();
          }
        })
        .catch(error => {
          console.error('Error loading admin notifications:', error);
        });
    }

    function displayAdminNotifications(notifications) {
      const container = document.getElementById('notifications-container');
      const mobileContainer = document.getElementById('mobileNotificationsContainer');
      
      if (notifications.length === 0) {
        const noNotificationsHtml = `
          <div class="no-notifications">
            🔔
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
  const timeTs = notification.created_at;
        const unreadClass = notification.is_read ? '' : 'unread';
        
        return `
          <div class="notification-item ${unreadClass}" onclick="showConsultationDetails(${notification.id}, ${notification.booking_id})">
            <div class="notification-type ${notification.type}">${notification.type.replace('_', ' ')}</div>
            <div class="notification-title">${notification.title}</div>
            <div class="notification-message">${notification.message}</div>
            <div class="notification-time" data-timeago data-ts="${timeTs}"></div>
          </div>
        `;
      }).join('');
      
      container.innerHTML = notificationsHtml;
      if (mobileContainer) {
        mobileContainer.innerHTML = notificationsHtml;
      }
    }

    function markAdminNotificationAsRead(notificationId) {
      fetch('/api/admin/notifications/mark-read', {
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
          adminNotificationsHash = '';
          loadAdminNotifications(); // Reload to update read status
        }
      })
      .catch(error => {
        console.error('Error marking admin notification as read:', error);
      });
    }

    function markAllAdminNotificationsAsRead() {
      fetch('/api/admin/notifications/mark-all-read', {
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
          adminNotificationsHash = '';
          loadAdminNotifications(); // Reload to update read status
          updateAdminUnreadCount();
        }
      })
      .catch(error => {
        console.error('Error marking all admin notifications as read:', error);
      });
    }

    function updateAdminUnreadCount() {
      const unreadCount = document.querySelectorAll('#notifications-container .notification-item.unread').length;
      const badge = document.getElementById('unread-count');
      if (badge) {
        badge.textContent = unreadCount;
        badge.style.display = unreadCount > 0 ? 'inline' : 'none';
      }
    }

    function getStatusColor(status) {
      const colors = {
        'Pending': '#f39c12',
        'Approved': '#27ae60',
        'Completed': '#2c5f4f',
        'Rescheduled': '#e74c3c'
      };
      return colors[status] || '#666';
    }

    // Live timeago handled by public/js/timeago.js

    // Modal functions for consultation details
    function showConsultationDetails(notificationId, bookingId) {
      // Mark notification as read
      markAdminNotificationAsRead(notificationId);
      
      // Show modal
      const modal = document.getElementById('consultationModal');
      const modalBody = document.getElementById('modalConsultationDetails');
      
      modal.style.display = 'flex';
      modalBody.innerHTML = '<div class="loading">Loading consultation details...</div>';
      
      // Fetch consultation details
      if (bookingId) {
        fetchConsultationDetails(bookingId);
      } else {
        modalBody.innerHTML = '<div class="error">No booking information available for this notification.</div>';
      }
    }

    function closeConsultationModal() {
      document.getElementById('consultationModal').style.display = 'none';
    }

    function fetchConsultationDetails(bookingId) {
      fetch(`/api/admin/consultation-details/${bookingId}`, {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          'Accept': 'application/json'
        }
      })
      .then(response => {
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
      })
      .then(consultation => {
        displayConsultationDetails(consultation);
      })
      .catch(error => {
        console.error('Error fetching consultation details:', error);
        document.getElementById('modalConsultationDetails').innerHTML = 
          '<div class="error">Failed to load consultation details. Please try again.</div>';
      });
    }

    function displayConsultationDetails(consultation) {
      const modalBody = document.getElementById('modalConsultationDetails');
      
      const html = `
        <div class="consultation-detail-card">
          <h4>Consultation Information</h4>
          
          <div class="detail-row">
            <span class="detail-label">Student:</span>
            <span class="detail-value"><strong>${consultation.student_name || 'N/A'}</strong></span>
          </div>
          
          <div class="detail-row">
            <span class="detail-label">Professor:</span>
            <span class="detail-value"><strong>${consultation.professor_name || 'N/A'}</strong></span>
          </div>
          
          <div class="detail-row">
            <span class="detail-label">Subject:</span>
            <span class="detail-value">${consultation.subject || 'N/A'}</span>
          </div>
          
          <div class="detail-row">
            <span class="detail-label">Consultation Type:</span>
            <span class="detail-value">${consultation.type || 'N/A'}</span>
          </div>
          
          <div class="detail-row">
            <span class="detail-label">Date:</span>
            <span class="detail-value">${consultation.booking_date || 'N/A'}</span>
          </div>
          
          <div class="detail-row">
            <span class="detail-label">Mode:</span>
            <span class="detail-value">${consultation.mode || 'N/A'}</span>
          </div>
          
          <div class="detail-row">
            <span class="detail-label">Status:</span>
            <span class="detail-value">
              <span class="status-badge status-${consultation.status ? consultation.status.toLowerCase() : 'unknown'}">
                ${consultation.status || 'Unknown'}
              </span>
            </span>
          </div>
          
          <div class="detail-row">
            <span class="detail-label">Booking ID:</span>
            <span class="detail-value">#${consultation.booking_id || 'N/A'}</span>
          </div>
          
          <div class="detail-row">
            <span class="detail-label">Created:</span>
            <span class="detail-value">${consultation.created_at ? new Date(consultation.created_at).toLocaleString() : 'N/A'}</span>
          </div>
        </div>
      `;
      
      modalBody.innerHTML = html;
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
      const modal = document.getElementById('consultationModal');
      if (event.target === modal) {
        closeConsultationModal();
      }
    }

    // Initialize admin notifications
    loadAdminNotifications();

    // Initialize calendar data refresh
    loadAdminCalendarData();

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

    // Real-time load notifications every 3 seconds (reduced for smoother updates)
    setInterval(loadAdminNotifications, 3000);

    // Real-time refresh calendar data every 3 seconds (reduced for smoother updates)
    setInterval(loadAdminCalendarData, 3000);
  </script>
  <script src="{{ asset('js/timeago.js') }}"></script>
</body>
</html>