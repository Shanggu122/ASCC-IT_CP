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
  <link rel="stylesheet" href="{{ asset('css/legend.css') }}">
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
    /* Whole-cell background for overrides */
  .day-holiday { background: rgba(155, 89, 182, 0.55) !important; } /* Violet */
    .day-blocked { background: rgba(55, 65, 81, 0.75) !important; }   /* Suspended */
    .day-force   { background: rgba(37, 99, 235, 0.6) !important; }   /* Forced Online */
    .day-online  { background: rgba(255, 105, 180, 0.45) !important; }/* Online Day */
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

    /* Legend styles are centralized in public/css/legend.css */

    /* Position the legend button just to the right of the left sidebar (desktop only) */
    @media (min-width: 951px) {
      .legend-toggle { left: calc(220px + 20px) !important; }
      .legend-panel { left: calc(220px + 24px) !important; }
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
        <!-- Collapsible legend (bottom-left FAB to avoid chatbot at bottom-right) -->
        <button id="legendToggle" class="legend-toggle" aria-haspopup="dialog" aria-controls="legendBackdrop" aria-label="View Legend" title="View Legend">
          <svg width="22" height="22" viewBox="0 0 24 24" aria-hidden="true" focusable="false" style="color:#fff">
            <path fill="currentColor" d="M12 2a10 10 0 1 0 0 20a10 10 0 0 0 0-20zm0 7a1.25 1.25 0 1 1 0-2.5a1.25 1.25 0 0 1 0 2.5zM11 11h2v6h-2z"/>
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

  // Legend panel interactions
  (function legendPanelInit(){
    const btn = document.getElementById('legendToggle');
    const backdrop = document.getElementById('legendBackdrop');
    const closeBtn = document.getElementById('legendClose');
    if(!btn || !backdrop) return;
    const open = () => {
      backdrop.classList.add('open');
      backdrop.setAttribute('aria-hidden','false');
      document.body.classList.add('legend-open');
    };
    const close = () => {
      backdrop.classList.remove('open');
      backdrop.setAttribute('aria-hidden','true');
      document.body.classList.remove('legend-open');
    };
    btn.addEventListener('click', open);
    closeBtn && closeBtn.addEventListener('click', close);
    backdrop.addEventListener('click', (e)=>{ if(e.target === backdrop) close(); });
    document.addEventListener('keydown', (e)=>{ if(e.key === 'Escape') close(); });
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
        // Remove existing status classes and override visuals
        cell.classList.remove('has-booking', 'status-pending', 'status-approved', 'status-completed', 'status-rescheduled');
        cell.classList.remove('day-holiday','day-blocked','day-force','day-online');
        const oldBadge = cell.querySelector('.ov-badge');
        if (oldBadge) oldBadge.remove();
        cell.removeAttribute('data-status');
        
        const day = cell.getAttribute('data-pika-day');
        const month = cell.getAttribute('data-pika-month');
        const year = cell.getAttribute('data-pika-year');
        if (day && month && year) {
          const cellDate = new Date(year, month, day);
          const key = cellDate.toDateString();
          const isoKey = `${cellDate.getFullYear()}-${String(cellDate.getMonth()+1).padStart(2,'0')}-${String(cellDate.getDate()).padStart(2,'0')}`;
          // Render overrides (if any)
          if (window.studentOverrides && window.studentOverrides[isoKey] && window.studentOverrides[isoKey].length > 0) {
            const items = window.studentOverrides[isoKey];
            // Priority: holiday > block_all > force_mode
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
              chosenCls = 'ov-blocked';
            } else if (chosen.effect === 'force_mode') {
              chosenCls = (chosen.reason_key === 'online_day') ? 'ov-online' : 'ov-force';
            } else {
              chosenCls = 'ov-force';
            }
            badge.className = 'ov-badge ' + chosenCls;
            const forceLabel = (chosen.effect === 'force_mode' && (chosen.reason_key === 'online_day')) ? 'Online Day' : 'Forced Online';
            badge.title = chosen.label || chosen.reason_text || (chosen.effect === 'force_mode' ? forceLabel : chosen.effect);
            badge.textContent = chosen.effect === 'holiday' ? (chosen.reason_text || 'Holiday') : (chosen.effect === 'block_all' ? 'Suspended' : forceLabel);
            cell.style.position = 'relative';
            cell.appendChild(badge);
            // Cell background class, with Online Day distinct from Forced Online
            let dayCls;
            if (chosen.effect === 'holiday') {
              dayCls = 'day-holiday';
            } else if (chosen.effect === 'block_all') {
              dayCls = 'day-blocked';
            } else if (chosen.effect === 'force_mode') {
              dayCls = (chosen.reason_key === 'online_day') ? 'day-online' : 'day-force';
            } else {
              dayCls = 'day-force';
            }
            cell.classList.add(dayCls);
          }
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
  // Immediately load overrides for the visible month after initial draw
  try { if (typeof fetchStudentOverridesForMonth === 'function' && typeof getVisibleMonthBaseDate === 'function') { fetchStudentOverridesForMonth(getVisibleMonthBaseDate()); } } catch (_) {}
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

  function fetchStudentOverridesForMonth(dateObj) {
    try {
      if (!dateObj || !(dateObj instanceof Date) || isNaN(dateObj.getTime())) return;
      if (window.__studentOvLoading) return; // prevent overlapping requests
      window.__studentOvLoading = true;
      const start = new Date(dateObj.getFullYear(), dateObj.getMonth(), 1);
      const end = new Date(dateObj.getFullYear(), dateObj.getMonth() + 1, 0);
      const toIso = (d) => `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
      const startStr = toIso(start);
      const endStr = toIso(end);
      const bust = Date.now();
      fetch(`/api/calendar/overrides?start_date=${startStr}&end_date=${endStr}&_=${bust}`, { headers: { 'Accept':'application/json' } })
        .then(r=>r.json())
        .then(data => {
          if (data && data.success) {
            const incoming = data.overrides || {};
            const prev = window.studentOverrides || {};
            const changed = JSON.stringify(incoming) !== JSON.stringify(prev);
            if (changed) {
              window.studentOverrides = incoming;
              if (window.picker) window.picker.draw();
            }
          }
        })
        .catch(()=>{})
        .finally(()=>{ window.__studentOvLoading = false; });
    } catch(_){}
  }

  // Initial overrides load and month navigation observation
  (function observeMonthNavigation(){
    const run = () => fetchStudentOverridesForMonth(getVisibleMonthBaseDate());
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
            <div class="notification-time" data-timeago data-ts="${notification.created_at}"></div>
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
    
    // Live timeago handled by public/js/timeago.js
        
    
  </script>
  <script src="{{ asset('js/timeago.js') }}"></script>
</body>
</html>
