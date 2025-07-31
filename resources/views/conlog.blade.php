<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
          <div class="table-cell" data-label="Booked At">{{ \Carbon\Carbon::parse($b->Created_At)->timezone('Asia/Manila')->format('m/d/Y h:i A') }}</div>
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
</script>
</body>
</html>
