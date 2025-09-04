<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Consultation Activity</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/dashboard-professor.css') }}">
  <link rel="stylesheet" href="{{ asset('css/notifications.css') }}">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pikaday/css/pikaday.css">
  <style>
     #calendar {
      visibility: hidden;
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
      pointer-events: auto; /* Keep auto for hover tooltips to work */
      cursor: default; /* Change cursor to default - no pointer cursor */
      user-select: none; /* Prevent text selection */
    }
    .pika-button:hover, .pika-row.pick-whole-week:hover .pika-button {
      color: #fff;
      background: #cac7c7;
      box-shadow: none;
      border-radius: 3px;
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
      
      /* Mobile-specific calendar button styling - CLICK DISABLED */
      .pika-button {
        pointer-events: auto !important; /* Keep for hover tooltips */
        cursor: default !important; /* No pointer cursor on mobile */
        user-select: none !important; /* Prevent text selection */
        -webkit-touch-callout: none !important; /* Disable iOS callout */
        -webkit-user-select: none !important; /* Disable text selection on Safari */
        -moz-user-select: none !important; /* Disable text selection on Firefox */
        -ms-user-select: none !important; /* Disable text selection on IE/Edge */
        touch-action: manipulation !important; /* Disable double-tap zoom */
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
  </style>
</head>
<body>
  @include('components.navbarprof')


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
        const timeAgo = formatMobileTime(n.created_at);
        const unreadClass = n.is_read ? '' : 'unread';
        const typeLabel = (n.type || '').replace('_',' ');
        const cleanTitle = (n.title || '').includes('Consultation') ? 'Consultation' : (n.title || '');
        return `
          <div class="notification-item ${unreadClass}" onclick="markMobileNotificationAsRead(${n.id})">
            <div class="notification-type ${n.type}">${typeLabel}</div>
            <div class="notification-title">${cleanTitle}</div>
            <div class="notification-message">${n.message}</div>
            <div class="notification-time">${timeAgo}</div>
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
   
  fetch('/api/consultations')
    .then(response => response.json())
    .then(data => {
      console.log('� Initial load - fetched consultation data:', data.length, 'entries');
      
      data.forEach(entry => {
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
          const day = cell.getAttribute('data-pika-day');
          const month = cell.getAttribute('data-pika-month');
          const year = cell.getAttribute('data-pika-year');
          
          if (day && month && year) {
            const cellDate = new Date(year, month, day);
            const key = cellDate.toDateString();
            
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
    })
    .catch(error => {
      console.error('Error fetching consultation data:', error);
      console.log('Calendar will still load without consultation data');
    });
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
      
      consultations.forEach((entry, index) => {
        html += `
          <div class="consultation-entry" style="${index > 0 ? 'border-top: 1px solid #eee; padding-top: 6px; margin-top: 6px;' : ''}">
            <div class="student-name">${entry.student}</div>
            <div class="detail-row">Subject: ${entry.subject}</div>
            <div class="detail-row">Type: ${entry.type}</div>
            <div class="detail-row">Mode: ${entry.Mode}</div>
            <div class="status-row" style="color:${getStatusColor(entry.Status)};">Status: ${entry.Status}</div>
            <div class="booking-time">Booked: ${entry.Created_At}</div>
          </div>
        `;
      });
      
      if (!tooltip) {
        console.error('Tooltip element not found!');
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

console.log('Global hover delegation system initialized');

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
  document.addEventListener(eventType, preventCalendarClicks, true); // Capture phase
  document.addEventListener(eventType, preventCalendarClicks, false); // Bubble phase
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
      }
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
  if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)} min ago`;
  if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)} hrs ago`;
  return `${Math.floor(diffInSeconds / 86400)} days ago`;
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
</body>
</html>
