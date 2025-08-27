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
  <link rel="stylesheet" href="{{ asset('css/dashboard-admin.css') }}">
  <link rel="stylesheet" href="{{ asset('css/notifications.css') }}">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pikaday/css/pikaday.css">
  <style>
    #calendar {
      visibility: visible;
      display: none; /* Hide the input field completely */
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

    .status-pending { background: #fff3cd; color: #856404 !important; }
    .status-approved { background: #28a745; color: #ffffff !important; }
    .status-completed { background: #17a2b8; color: #ffffff !important; }
    .status-rescheduled { background: #dc3545; color: #ffffff !important; }
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
        <div class="legend">
          <div><span class="legend-box pending"></span> Pending</div>
          <div><span class="legend-box approved"></span> Approved</div>
          <div><span class="legend-box completed"></span> Completed</div>
          <div><span class="legend-box rescheduled"></span> Rescheduled</div>
          <div><span class="legend-box multiple-bookings"></span> Multiple Bookings</div>
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
      if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)} min ago`;
      if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)} hrs ago`;
      return `${Math.floor(diffInSeconds / 86400)} days ago`;
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
   
    // ADMIN VERSION: Fetch ALL consultations from all professors and students
    fetch('/api/admin/all-consultations')
      .then(response => response.json())
      .then(data => {
        console.log('🔧 ADMIN - Initial load - fetched ALL consultation data:', data.length, 'entries');
        
        data.forEach(entry => {
          const date = new Date(entry.Booking_Date);
          const key = date.toDateString();
          
          // For status coloring and modal
          bookingMap.set(key, { status: entry.Status.toLowerCase(), id: entry.Booking_ID });
          // For hover tooltip details
          if (!detailsMap.has(key)) detailsMap.set(key, []);
          detailsMap.get(key).push(entry);
        });

        console.log('Admin Booking Map size:', bookingMap.size);
        console.log('Admin Details Map size:', detailsMap.size);

        // Initialize Pikaday AFTER data is loaded
        const picker = new Pikaday({
          field: document.getElementById('calendar'),
          format: 'ddd, MMM DD YYYY',
          showDaysInNextAndPreviousMonths: true,
          firstDay: 1,
          bound: false,
          onDraw: function() {
            console.log('Admin Calendar onDraw called');
            const cells = document.querySelectorAll('.pika-button');
            console.log('Found calendar cells:', cells.length);
            
            cells.forEach(cell => {
            const day = cell.getAttribute('data-pika-day');
            const month = cell.getAttribute('data-pika-month');
            const year = cell.getAttribute('data-pika-year');
            
            if (day && month && year) {
              const cellDate = new Date(year, month, day);
              const key = cellDate.toDateString();
              
              if (bookingMap.has(key)) {
                console.log('Admin found booking for date:', key, bookingMap.get(key));
                
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
                
                console.log('Admin Added classes to cell:', cell.className, 'Consultations:', consultationCount);
                
                // CLICK FUNCTIONALITY DISABLED - Only hover tooltips enabled
                // cell.removeEventListener('click', cell.bookingClickHandler);

                // Disabled click functionality - only hover enabled
                cell.style.cursor = 'default'; // Changed from 'pointer' to 'default'
                console.log('Admin Click disabled - only hover tooltip enabled');

                // --- IMPROVED HOVER FUNCTIONALITY FOR ADMIN ---
                console.log('Admin Adding hover events to cell:', key);
                
                // Store data directly on the DOM element for reliable access
                cell.setAttribute('data-consultation-key', key);
                cell.setAttribute('data-has-consultations', 'true');
                
                console.log('Admin Cell prepared for hover with key:', key);
              } else {
                // console.log('⚪ Admin No booking for date:', key);
              }
            }
          });
          }
        });
        picker.show();
        picker.draw();
        
        // Store picker globally for real-time updates
        window.adminPicker = picker;
      })
      .catch(error => {
        console.error('Admin Error fetching consultation data:', error);
        console.log('Admin Calendar will still load without consultation data');
      });

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
          
          consultations.forEach((entry, index) => {
            html += `
              <div class="consultation-entry" style="${index > 0 ? 'border-top: 1px solid #eee; padding-top: 6px; margin-top: 6px;' : ''}">
                <div class="student-name">${entry.student} have consultation with ${entry.professor}</div>
                <div class="detail-row">Subject: ${entry.subject}</div>
                <div class="detail-row">Type: ${entry.type}</div>
                <div class="detail-row">Mode: ${entry.Mode}</div>
                <div class="status-row" style="color:${getStatusColor(entry.Status)};">Status: ${entry.Status}</div>
                <div class="booking-time">Booked: ${entry.Created_At}</div>
              </div>
            `;
          });
          
          if (!tooltip) {
            console.error('Admin Tooltip element not found!');
            return;
          }
          
          tooltip.innerHTML = html;
          tooltip.style.display = 'block';
          
          // Smart positioning to keep tooltip within viewport (only on new cell hover)
          let left = e.clientX + 20;
          let top = e.clientY - 20;
          
          // Get tooltip dimensions after it's displayed
          const tooltipRect = tooltip.getBoundingClientRect();
          const viewportWidth = window.innerWidth;
          const viewportHeight = window.innerHeight;
          
          // Adjust horizontal position if tooltip would go off-screen
          if (left + tooltipRect.width > viewportWidth - 10) {
            left = e.clientX - tooltipRect.width - 20;
          }
          
          // Adjust vertical position if tooltip would go off-screen
          if (top + tooltipRect.height > viewportHeight - 10) {
            top = Math.max(10, viewportHeight - tooltipRect.height - 10);
          }
          
          // Ensure tooltip doesn't go above viewport
          if (top < 10) {
            top = 10;
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
    function preventCalendarClicks(e) {
      const target = e.target;
      // Only prevent clicks/touches on date buttons inside the table, not navigation buttons
      // Allow mouseover/mouseout for tooltips
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

    // ADMIN NOTIFICATION FUNCTIONS
    // Mark all as read functionality
    document.getElementById('mark-all-read').addEventListener('click', function() {
      markAllAdminNotificationsAsRead();
    });

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
        const timeAgo = getTimeAgo(notification.created_at);
        const unreadClass = notification.is_read ? '' : 'unread';
        
        return `
          <div class="notification-item ${unreadClass}" onclick="showConsultationDetails(${notification.id}, ${notification.booking_id})">
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

    function getTimeAgo(dateString) {
      const date = new Date(dateString);
      const now = new Date();
      const diffInSeconds = Math.floor((now - date) / 1000);
      
      if (diffInSeconds < 60) return 'Just now';
      if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)} min ago`;
      if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)} hrs ago`;
      return `${Math.floor(diffInSeconds / 86400)} days ago`;
    }

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

    // Real-time load notifications every 3 seconds (reduced for smoother updates)
    setInterval(loadAdminNotifications, 3000);

    // Real-time refresh calendar data every 3 seconds (reduced for smoother updates)
    setInterval(loadAdminCalendarData, 3000);
  </script>
</body>
</html>