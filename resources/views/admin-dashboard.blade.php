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
      color: #ffffff;
      padding: 10px;
      height: 50px;
      margin: 5px 0;
      /* pointer-events: none; */
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
        </div>
      </div>
      <div class="box">
        <!-- NOTE: ADD ADMIN ITEMS HERE... -->
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

  <!-- Consultation Popup -->
<div id="consultationPopup" style="display:none; position:absolute; z-index:9999;"></div>

  <script src="{{ asset('js/dashboard.js') }}"></script>
  <script src="https://cdn.jsdelivr.net/npm/pikaday/pikaday.js"></script>
  <script>
const bookingMap = new Map();
const detailsMap = new Map();

fetch('/api/consul')
  .then(response => response.json())
  .then(data => {
    data.forEach(entry => {
      const date = new Date(entry.Booking_Date);
      const key = date.toDateString();
      // For status coloring
      bookingMap.set(key, entry.Status.toLowerCase());
      // For popup details
      if (!detailsMap.has(key)) detailsMap.set(key, []);
      detailsMap.get(key).push(entry);
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

              // --- HOVER FUNCTIONALITY ---
              cell.onmouseenter = function(e) {
                const consultations = detailsMap.get(key) || [];
                if (consultations.length === 0) return;
                let html = '';
                consultations.forEach(entry => {
                  html += `
                    <div class="consultation-card">
                      <div class="consult-date"><b>${formatDate(entry.Booking_Date)}</b></div>
                      <div class="consult-time">${formatDay(entry.Booking_Date)} ${entry.Time_Start}–${entry.Time_End}</div>
                      <div class="consult-prof">${entry.Professor} - <span class="consult-type">${entry.Consultation_Type ? `<i>${entry.Consultation_Type}</i>` : ''}</span></div>
                      <div class="consult-mode">via ${entry.Mode} | ${entry.Room}</div>
                      <div class="consult-status ${entry.Status.toLowerCase()}">${entry.Status}</div>
                    </div>
                  `;
                });
                const popup = document.getElementById('consultationPopup');
                popup.innerHTML = html;
                popup.style.display = 'block';
                // Position popup near mouse
                popup.style.left = (e.clientX + 20) + 'px';
                popup.style.top = (e.clientY - 20) + 'px';
              };
              cell.onmousemove = function(e) {
                const popup = document.getElementById('consultationPopup');
                popup.style.left = (e.clientX + 20) + 'px';
                popup.style.top = (e.clientY - 20) + 'px';
              };
              cell.onmouseleave = function() {
                document.getElementById('consultationPopup').style.display = 'none';
              };
            }
          }
        });
      }
    });
    picker.show();
    picker.draw();
  })
  .catch((err) => console.log(err));

// Helper functions
function formatDate(dateStr) {
  const d = new Date(dateStr);
  return d.toLocaleString('en-US', { month: 'long', day: 'numeric' });
}
function formatDay(dateStr) {
  const d = new Date(dateStr);
  return d.toLocaleString('en-US', { weekday: 'long' });
}
  </script>
</body>
</html>