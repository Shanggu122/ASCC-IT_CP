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
      <input type="text" id="searchInput" placeholder="Search..." style="flex:1;"
             autocomplete="off" spellcheck="false" maxlength="50"
             pattern="[A-Za-z0-9 .,@_-]{0,50}" aria-label="Search consultations">
      <div class="filter-group-horizontal">
    <select id="typeFilter" class="filter-select">
            <option value="">All Types</option>
            @foreach($fixedTypes as $type)
              <option value="{{ $type }}">{{ $type }}</option>
            @endforeach
            <option value="Others">Others</option>
          </select>
          <!-- Custom filter dropdown (mobile) -->
    <div id="typeFilterDropdown" class="cs-dd" style="display:none;">
            <button type="button" class="cs-dd-trigger" id="typeFilterTrigger">All Types</button>
            <ul class="cs-dd-list" id="typeFilterList"></ul>
          </div>
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

// Basic sanitizer shared by search & chat below
function sanitize(raw){
  if(!raw) return '';
  return raw
    .replace(/\/*.*?\*\//g,'') // remove block comments
    .replace(/--+/g,' ')          // remove repeated dashes (SQL comment openers)
    .replace(/[;`'"<>]/g,' ')    // strip risky punctuation
    .replace(/\s+/g,' ')         // collapse whitespace
    .trim()
    .slice(0,50);
}

function filterRows() {
  const inputEl = document.getElementById('searchInput');
  let search = sanitize(inputEl.value).toLowerCase();
  if(inputEl.value !== search) inputEl.value = search; // reflect sanitized
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
      (type !== 'others' && rowType === type) ||
      (type === 'others' && isOthers);

    let matchesSearch = instructor.includes(search) || rowSubject.includes(search) || rowType.includes(search);

    row.style.display = (matchesSearch && matchesType) ? '' : 'none';
  });
}

  // Custom dropdown for type filter (mobile)
  function buildTypeFilterDropdown(){
    const wrap=document.getElementById('typeFilterDropdown');
    const trigger=document.getElementById('typeFilterTrigger');
    const list=document.getElementById('typeFilterList');
    const native=document.getElementById('typeFilter');
    if(!wrap||!trigger||!list||!native) return;
    list.innerHTML='';
    Array.from(native.options).forEach((o,i)=>{
      const li=document.createElement('li');
      li.textContent=o.textContent; if(i===native.selectedIndex) li.classList.add('active');
      li.addEventListener('click',()=>{ native.selectedIndex=i; updateTrigger(); wrap.classList.remove('open'); Array.from(list.children).forEach(c=>c.classList.remove('active')); li.classList.add('active'); native.dispatchEvent(new Event('change')); });
      list.appendChild(li);
    });
    updateTrigger();
    trigger.onclick=()=>{ wrap.classList.toggle('open'); };
    document.addEventListener('click',e=>{ if(!wrap.contains(e.target)) wrap.classList.remove('open'); });
    function updateTrigger(){ const sel=native.options[native.selectedIndex]; trigger.textContent= sel? sel.textContent : 'All Types'; }
  }

  document.addEventListener('DOMContentLoaded',function(){ buildTypeFilterDropdown(); });

document.getElementById('searchInput').addEventListener('input', filterRows);
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
if(input){
  input.setAttribute('maxlength','250');
  input.setAttribute('autocomplete','off');
  input.setAttribute('spellcheck','false');
}
const chatBody = document.getElementById("chatBody");

// Send on Enter (like ITIS/COMSCI). Prevent accidental double submits.
input.addEventListener('keydown', function(e){
  if(e.key === 'Enter'){ 
    e.preventDefault();
  if(!sanitize(input.value)) return;
    // Use requestSubmit if supported
    if(typeof chatForm.requestSubmit === 'function') chatForm.requestSubmit();
    else chatForm.dispatchEvent(new Event('submit', {cancelable:true}));
  }
});

chatForm.addEventListener("submit", async function (e) {
    e.preventDefault();
  const text = sanitize(input.value);
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
