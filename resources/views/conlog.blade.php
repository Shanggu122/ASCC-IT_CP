<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Consultation Log</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/conlog.css') }}">
</head>
<body>
  @include('components.navbar')

  @php
    $fixedTypes = [
      'Tutoring',
      'Grade Consultation',
      'Missed Activities',
      'Special Quiz or Exam',
      'Capstone Consultation'
    ];
  @endphp

  <div class="main-content">
    <div class="header">
      <h1>Consultation Logs</h1>
    </div>

    <div class="search-container">
      <input type="text" id="searchInput" placeholder="Search..." style="flex:1;">
      <div class="filter-group-horizontal">
        <select id="typeFilter" class="filter-select">
          <option value="">All Types</option>
          @foreach($fixedTypes as $type)
            <option value="{{ $type }}">{{ $type }}</option>
          @endforeach
          <option value="Others">Others</option>
        </select>
      </div>
    </div>

    <div class="table-container">
      <div class="table">
        <!-- Header Row -->
        <div class="table-row table-header">
          <div class="table-cell">No.</div> <!-- Add this line -->
          <div class="table-cell">Instructor</div>
          <div class="table-cell">Subject</div>
          <div class="table-cell">Date</div>
          <div class="table-cell">Type</div>
          <div class="table-cell">Mode</div>
          <div class="table-cell">Booked At</div>
          <div class="table-cell">Status</div>
        </div>
    
        <!-- Dynamic Data Rows -->
        @forelse($bookings as $b)
        <div class="table-row">
          <div class="table-cell" data-label="No.">{{ $loop->iteration }}</div>
          <div class="table-cell instructor-cell" data-label="Instructor">{{ $b->Professor }}</div>
          <div class="table-cell" data-label="Subject">{{ $b->subject }}</div>
          <div class="table-cell" data-label="Date">{{ \Carbon\Carbon::parse($b->Booking_Date)->format('D, M d Y') }}</div>
          <div class="table-cell" data-label="Type">{{ $b->type }}</div>
          <div class="table-cell" data-label="Mode">{{ ucfirst($b->Mode) }}</div>
          <div class="table-cell" data-label="Booked At">{{ \Carbon\Carbon::parse($b->Created_At)->timezone('Asia/Manila')->format('M d Y h:i A') }}</div>
          <div class="table-cell" data-label="Status">{{ ucfirst($b->Status) }}</div>
        </div>

      @empty
        <div class="table-row">
          <div class="table-cell" colspan="8">No consultations found.</div>
        </div>
      @endforelse
      <div style="height: 80px;"></div> <!-- Spacer under the last table row -->
    
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

  <script src="{{ asset('js/ccit.js') }}"></script>
  <script>
const fixedTypes = [
  'tutoring',
  'grade consultation',
  'missed activities',
  'special quiz or exam',
  'capstone consultation'
];

function filterRows() {
    let search = document.getElementById('searchInput').value.toLowerCase();
    let type = document.getElementById('typeFilter').value.toLowerCase();
    let rows = document.querySelectorAll('.table-row:not(.table-header)');
    rows.forEach(row => {
        let rowType = row.querySelector('[data-label="Type"]')?.textContent.toLowerCase() || '';
        let instructor = row.querySelector('.instructor-cell')?.textContent.toLowerCase() || '';
        let rowSubject = row.querySelector('[data-label="Subject"]')?.textContent.toLowerCase() || '';

        // Is this row a custom type (not in fixedTypes)?
        let isOthers = fixedTypes.indexOf(rowType) === -1 && rowType !== '';

        let matchesType =
            !type ||
            (type !== "others" && rowType === type) ||
            (type === "others" && isOthers);

        let matchesSearch = instructor.includes(search) || rowSubject.includes(search) || rowType.includes(search);

        if (matchesSearch && matchesType) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

document.getElementById('searchInput').addEventListener('keyup', filterRows);
document.getElementById('typeFilter').addEventListener('change', filterRows);

// Real-time updates for consultation log - DISABLED TO PREVENT DUPLICATE ROWS
/*
function loadConsultationLogs() {
  fetch('/api/student/consultation-logs')
    .then(response => response.json())
    .then(data => {
      updateConsultationTable(data);
    })
    .catch(error => {
      console.error('Error loading consultation logs:', error);
    });
}

function updateConsultationTable(bookings) {
  const table = document.querySelector('.table');
  const header = document.querySelector('.table-header');
  
  // Clear existing rows except header
  const existingRows = table.querySelectorAll('.table-row:not(.table-header)');
  existingRows.forEach(row => row.remove());
  
  if (bookings.length === 0) {
    const emptyRow = document.createElement('div');
    emptyRow.className = 'table-row';
    emptyRow.innerHTML = `
      <div class="table-cell" colspan="8">No consultations found.</div>
    `;
    table.appendChild(emptyRow);
  } else {
    bookings.forEach((booking, index) => {
      const row = document.createElement('div');
      row.className = 'table-row';
      
      const bookingDate = new Date(booking.Booking_Date);
      const createdAt = new Date(booking.Created_At);
      
      row.innerHTML = `
        <div class="table-cell" data-label="No.">${index + 1}</div>
        <div class="table-cell instructor-cell" data-label="Instructor">${booking.Professor}</div>
        <div class="table-cell" data-label="Subject">${booking.subject}</div>
        <div class="table-cell" data-label="Date">${bookingDate.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' })}</div>
        <div class="table-cell" data-label="Type">${booking.type}</div>
        <div class="table-cell" data-label="Mode">${booking.Mode.charAt(0).toUpperCase() + booking.Mode.slice(1)}</div>
        <div class="table-cell" data-label="Booked At">${createdAt.toLocaleDateString('en-US', { month: 'numeric', day: 'numeric', year: 'numeric' })} ${createdAt.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true })}</div>
        <div class="table-cell" data-label="Status">${booking.Status.charAt(0).toUpperCase() + booking.Status.slice(1)}</div>
      `;
      
      table.appendChild(row);
    });
  }
  
  // Add spacer
  const spacer = document.createElement('div');
  spacer.style.height = '80px';
  table.appendChild(spacer);
  
  // Re-apply filters after updating
  filterRows();
}

// Initial load and real-time updates every 5 seconds - DISABLED
loadConsultationLogs();
setInterval(loadConsultationLogs, 5000);
*/

// === Chatbot ===
function toggleChat() {
    document.getElementById("chatOverlay").classList.toggle("open");
}

const csrfToken = document
    .querySelector('meta[name="csrf-token"]')
    .getAttribute("content");
const chatForm = document.getElementById("chatForm");
const input = document.getElementById("message");
const chatBody = document.getElementById("chatBody");

chatForm.addEventListener("submit", async function (e) {
    e.preventDefault();
    const text = input.value.trim();
    if (!text) return;

    const um = document.createElement("div");
    um.classList.add("message", "user");
    um.innerText = text;
    chatBody.appendChild(um);
    chatBody.scrollTop = chatBody.scrollHeight;
    input.value = "";

    const res = await fetch("/chat", {
        method: "POST",
        credentials: "same-origin",
        headers: {
            Accept: "application/json",
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": csrfToken,
        },
        body: JSON.stringify({ message: text }),
    });

    if (!res.ok) {
        const err = await res.json();
        const bm = document.createElement("div");
        bm.classList.add("message", "bot");
        bm.innerText = err.message || "Server error.";
        chatBody.appendChild(bm);
        return;
    }

    const { reply } = await res.json();
    const bm = document.createElement("div");
    bm.classList.add("message", "bot");
    bm.innerText = reply;
    chatBody.appendChild(bm);
    chatBody.scrollTop = chatBody.scrollHeight;
});
</script>
</body>
</html>
