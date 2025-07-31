<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Consultation Activity</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/dashboard-professor.css') }}">
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
        </div>
      </div>
      <div class="box">
        <!-- NOTE: ADD ITEMS HERE... -->
      </div>
    </div>

{{-- 
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
    </div> --}}

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

  </div>

  {{-- <script src="{{ asset('js/dashboard.js') }}"></script> --}}
  <script src="https://cdn.jsdelivr.net/npm/pikaday/pikaday.js"></script>
  <script>
    
   const bookingMap = new Map();
  fetch('/api/consultations')
    .then(response => response.json())
    .then(data => {
      
      data.forEach(entry => {
        const date = new Date(entry.Booking_Date);
        bookingMap.set(date.toDateString(), { status: entry.Status.toLowerCase(), id: entry.Booking_ID });
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
              const booking = bookingMap.get(key);
              const status = booking.status;
              const bookingId = booking.id;
              const classMap = {
                pending: 'status-pending',
                approved: 'status-approved',
                completed: 'status-completed',
                rescheduled: 'status-rescheduled'
              };
              cell.classList.add('has-booking');
              cell.classList.add(classMap[status]);
              cell.setAttribute('data-status', status);
              cell.removeEventListener('click', cell.bookingClickHandler);

              // Only allow click for pending
               if (status === 'pending') {
                cell.style.cursor = 'pointer';

                // Define the event handler function
                cell.bookingClickHandler = function(e) {
                  console.log(283232828);
                  bookingModal(cellDate, bookingId);
                  e.preventDefault();
                };

                // Attach the event listener
                cell.addEventListener('click', cell.bookingClickHandler);
              } else {
                // Remove the event listener if it exists
                cell.style.cursor = 'not-allowed';
              }
            }
          }
        });
        }
      });
      picker.show();
      picker.draw();
    })
    .catch((err) => console.log(err));
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
  
  </script>
</body>
</html>
