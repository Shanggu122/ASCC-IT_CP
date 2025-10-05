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
      <h1>Consultation Log</h1>
    </div>
    @php
      // Build unique subjects list for subject filter
      $subjects = collect($bookings ?? [])->pluck('subject')->filter(fn($s)=>filled($s))
                   ->map(fn($s)=>trim($s))->unique()->sort()->values();
    @endphp

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
      <div class="filter-group-horizontal">
        <select id="subjectFilter" class="filter-select" aria-label="Subject filter">
          <option value="">All Subjects</option>
          @foreach($subjects as $s)
            <option value="{{ $s }}">{{ $s }}</option>
          @endforeach
        </select>
      </div>
      <div class="filter-group-horizontal" style="margin-left:auto">
        <label for="pageSize" class="filter-label-inline">Show</label>
        <select id="pageSize" class="filter-select" style="width:92px">
          <option value="5">5</option>
          <option value="10" selected>10</option>
          <option value="25">25</option>
          <option value="50">50</option>
          <option value="100">100</option>
        </select>
        <span class="filter-label-inline">entries</span>
      </div>
    </div>

    <div class="table-container">
      <div class="table">
        <!-- Header Row -->
        <div class="table-row table-header" id="conlogHeader">
          <div class="table-cell">No.</div>
          <div class="table-cell sort-header" data-sort="instructor" role="button" tabindex="0" aria-label="Sort by instructor">Instructor <span class="sort-icon"></span></div>
          <div class="table-cell sort-header" data-sort="subject" role="button" tabindex="0" aria-label="Sort by subject">Subject <span class="sort-icon"></span></div>
          <div class="table-cell sort-header" data-sort="date" role="button" tabindex="0" aria-label="Sort by date">Date <span class="sort-icon"></span></div>
          <div class="table-cell sort-header" data-sort="type" role="button" tabindex="0" aria-label="Sort by type">Type <span class="sort-icon"></span></div>
          <div class="table-cell sort-header" data-sort="mode" role="button" tabindex="0" aria-label="Sort by mode">Mode <span class="sort-icon"></span></div>
          <div class="table-cell sort-header" data-sort="booked" role="button" tabindex="0" aria-label="Sort by booked at">Booked At <span class="sort-icon"></span></div>
          <div class="table-cell sort-header" data-sort="status" role="button" tabindex="0" aria-label="Sort by status">Status <span class="sort-icon"></span></div>
        </div>
    
        <!-- Dynamic Data Rows -->
        @forelse($bookings as $b)
        <div class="table-row"
             data-instructor="{{ strtolower($b->Professor) }}"
             data-subject="{{ strtolower($b->subject) }}"
             data-date="{{ \Carbon\Carbon::parse($b->Booking_Date)->format('Y-m-d') }}"
             data-date-ts="{{ \Carbon\Carbon::parse($b->Booking_Date)->timestamp }}"
             data-type="{{ strtolower($b->type) }}"
             data-mode="{{ strtolower($b->Mode) }}"
             data-booked="{{ \Carbon\Carbon::parse($b->Created_At)->timezone('Asia/Manila')->format('Y-m-d H:i:s') }}"
             data-booked-ts="{{ \Carbon\Carbon::parse($b->Created_At)->timezone('Asia/Manila')->timestamp }}"
             data-status="{{ strtolower($b->Status) }}"
             data-matched="1"
        >
          <div class="table-cell" data-label="No." data-booking-id="{{ $b->Booking_ID ?? '' }}">{{ $loop->iteration }}</div>
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
          <div class="table-cell" colspan="8">No consultation found.</div>
        </div>
      @endforelse
      <div style="height: 80px;"></div> <!-- Spacer under the last table row -->
    
      </div>
    </div>

    <!-- Pagination controls -->
    <div class="pagination-bar">
      <div id="pageInfo" class="page-info"></div>
      <div id="paginationControls" class="pagination"></div>
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
  <script src="https://js.pusher.com/7.0/pusher.min.js"></script>
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

  // ===== Sorting + Pagination State =====
  let sortKey = 'date'; // default sort
  let sortDir = 'desc';
  let currentPage = 1;
  let pageSize = parseInt(localStorage.getItem('conlog.pageSize')||'10',10);
  if(![5,10,25,50,100].includes(pageSize)) pageSize = 10;
  document.addEventListener('DOMContentLoaded',()=>{
    const ps = document.getElementById('pageSize'); if(ps) ps.value = String(pageSize);
  });

  function getDataRows(){
    return Array.from(document.querySelectorAll('.table .table-row'))
      .filter(r=>!r.classList.contains('table-header') && !r.classList.contains('no-results-row'));
  }

  function setSortIndicators(){
    const headers = document.querySelectorAll('#conlogHeader .sort-header');
    headers.forEach(h=>{
      const icon = h.querySelector('.sort-icon');
      if(!icon) return;
      const key = h.getAttribute('data-sort');
      if(key===sortKey){ icon.textContent = sortDir==='asc' ? ' ▲' : ' ▼'; h.classList.add('active-sort'); }
      else { icon.textContent=''; h.classList.remove('active-sort'); }
    });
  }

  function compareRows(a,b){
    const get = (row,key)=>{
      if(key==='date') return Number(row.dataset.dateTs||0);
      if(key==='booked') return Number(row.dataset.bookedTs||0);
      return (row.dataset[key]||'').toString();
    };
    const va = get(a,sortKey); const vb = get(b,sortKey);
    let cmp = 0;
    if(typeof va==='number' && typeof vb==='number') cmp = va - vb;
    else cmp = va.localeCompare(vb);
    return sortDir==='asc' ? cmp : -cmp;
  }

  function rebuildSubjectOptions(){
    const sel = document.getElementById('subjectFilter'); if(!sel) return;
    const seen = new Set();
    getDataRows().forEach(r=>{ const s=(r.dataset.subject||'').trim(); if(s) seen.add(s); });
    const cur = sel.value; const arr = Array.from(seen).sort((a,b)=>a.localeCompare(b));
    sel.innerHTML = '<option value="">All Subjects</option>' + arr.map(v=>`<option value="${v}">${v}</option>`).join('');
    if(arr.includes(cur)) sel.value = cur;
  }

  function applySortAndPaginate(){
    const table = document.querySelector('.table'); if(!table) return;
    const header = document.querySelector('.table-header');
    const rows = getDataRows();
    const matched = rows.filter(r=>r.dataset.matched==='1');

    const existingNo = document.querySelector('.no-results-row'); if(existingNo) existingNo.remove();
    if(matched.length===0){
      rows.forEach(r=>r.style.display='none');
      const noRow = document.createElement('div');
      noRow.className='table-row no-results-row';
      noRow.innerHTML = `<div class="table-cell" style="text-align:center;padding:20px;color:#666;font-style:italic;grid-column:1 / -1;">No Consultations Found.</div>`;
      header.insertAdjacentElement('afterend', noRow);
      const info = document.getElementById('pageInfo'); if(info) info.textContent='';
      const pag = document.getElementById('paginationControls'); if(pag) pag.innerHTML='';
      setSortIndicators();
      return;
    }

    matched.sort(compareRows);
    const frag = document.createDocumentFragment(); matched.forEach(r=>frag.appendChild(r)); table.appendChild(frag);

    const total = matched.length; const totalPages = Math.max(1, Math.ceil(total/pageSize));
    if(currentPage>totalPages) currentPage = totalPages;
    const start = (currentPage-1)*pageSize; const end = Math.min(total, start+pageSize)-1;

    const matchedSet = new Set(matched);
    rows.forEach(r=>{
      if(!matchedSet.has(r)) { r.style.display='none'; return; }
      const idx = matched.indexOf(r);
      r.style.display = (idx>=start && idx<=end) ? '' : 'none';
    });

    const pageInfo = document.getElementById('pageInfo'); if(pageInfo) pageInfo.textContent = `Showing ${start+1} to ${end+1} of ${total} entries`;
    const pag = document.getElementById('paginationControls');
    if(pag){
      const mk = (label, page, disabled=false, active=false)=>{ const b=document.createElement('button'); b.className='page-btn'+(active?' active':''); b.textContent=label; b.disabled=disabled; b.addEventListener('click',()=>{ currentPage=page; applySortAndPaginate();}); return b; };
      pag.innerHTML='';
      const totalPagesCalc = Math.max(1, Math.ceil(total/pageSize));
      pag.appendChild(mk('Prev', Math.max(1,currentPage-1), currentPage===1));
      const span=2; let s=Math.max(1,currentPage-span); let e=Math.min(totalPagesCalc,currentPage+span);
      if(e-s<span*2){ if(s===1) e=Math.min(totalPagesCalc, s+span*2); else if(e===totalPagesCalc) s=Math.max(1, e-span*2); }
      for(let p=s;p<=e;p++) pag.appendChild(mk(String(p), p, false, p===currentPage));
      pag.appendChild(mk('Next', Math.min(totalPagesCalc,currentPage+1), currentPage===totalPagesCalc));
    }
    setSortIndicators();
  }

function filterRows() {
  const inputEl = document.getElementById('searchInput');
  let search = sanitize(inputEl.value).toLowerCase();
  if(inputEl.value !== search) inputEl.value = search; // reflect sanitized
  let type = document.getElementById('typeFilter').value.toLowerCase();
  let subject = (document.getElementById('subjectFilter')?.value||'').toLowerCase();
  let rows = document.querySelectorAll('.table-row:not(.table-header)');

  rows.forEach(row => {
    if (row.classList.contains('no-results-row')) return;
    let rowType = (row.dataset.type||'').toLowerCase();
    let instructor = (row.dataset.instructor||'').toLowerCase();
    let rowSubject = (row.dataset.subject||'').toLowerCase();

    let isOthers = fixedTypes.indexOf(rowType) === -1 && rowType !== '';

    let matchesType = !type || (type !== 'others' && rowType === type) || (type === 'others' && isOthers);
    let matchesSubject = !subject || rowSubject === subject;
    let matchesSearch = instructor.includes(search) || rowSubject.includes(search) || rowType.includes(search);

    row.dataset.matched = (matchesSearch && matchesType && matchesSubject) ? '1' : '0';
  });
  currentPage = 1;
  applySortAndPaginate();
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
document.getElementById('subjectFilter').addEventListener('change', filterRows);
document.getElementById('pageSize').addEventListener('change', (e)=>{
  pageSize = parseInt(e.target.value,10) || 10;
  localStorage.setItem('conlog.pageSize', String(pageSize));
  currentPage = 1;
  applySortAndPaginate();
});

// Header sorting handlers
document.querySelectorAll('#conlogHeader .sort-header').forEach(h=>{
  const set = ()=>{
    const key = h.getAttribute('data-sort');
    if(sortKey===key){ sortDir = (sortDir==='asc' ? 'desc' : 'asc'); }
    else { sortKey = key; sortDir = (key==='date' || key==='booked') ? 'desc' : 'asc'; }
    applySortAndPaginate();
  };
  h.addEventListener('click', set);
  h.addEventListener('keypress', (e)=>{ if(e.key==='Enter' || e.key===' ') { e.preventDefault(); set(); } });
});

document.addEventListener('DOMContentLoaded',()=>{ filterRows(); });

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

// Live updates via Pusher for the current student
(function(){
  try {
    // Try to determine current student ID from navbar or hidden context
    const metaUser = document.querySelector('meta[name="csrf-token"]'); // placeholder anchor; no student id here
    // Prefer server-side injection through navbar; fallback to fetch on first update if needed
    const studIdEl = document.getElementById('navbar') || document.body; // just to keep DOM lookup cheap
  // Prefer student guard if available; fall back to web user with Stud_ID
  const studId = {{ optional(auth()->user())->Stud_ID ?? optional(auth()->guard('web')->user())->Stud_ID ?? 'null' }};
    if(!studId) return;

  const pusher = new Pusher('{{ config('broadcasting.connections.pusher.key') }}', {cluster: '{{ config('broadcasting.connections.pusher.options.cluster') }}'});
  Pusher.logToConsole = false;
    const channel = pusher.subscribe('bookings.stud.'+studId);

    function normalizeDate(str){ try{ return new Date(str).toLocaleDateString('en-US',{weekday:'short', month:'short', day:'numeric', year:'numeric'}); }catch(e){ return str; } }

    function renderRow(data){
      const table = document.querySelector('.table'); if(!table) return;
      const rows = Array.from(table.querySelectorAll('.table-row'))
        .filter(r=>!r.classList.contains('table-header') && !r.classList.contains('no-results-row'));
      let existing = null;
      const targetId = (data.Booking_ID!==undefined && data.Booking_ID!==null) ? String(data.Booking_ID).trim() : '';
      rows.forEach(r=>{ const idCell=r.querySelector('[data-booking-id]'); const rowId = idCell? String(idCell.getAttribute('data-booking-id')||'').trim():''; if(rowId && targetId && rowId===targetId){ existing=r; } });

      // Helper to merge missing fields from a given row's current cells
      function mergeFromRow(row) {
        if(!row) return;
        const cells = row.querySelectorAll('.table-cell');
        data.Professor = data.Professor ?? (cells[1]?.textContent.trim()||'');
        data.subject = data.subject ?? (cells[2]?.textContent.trim()||'');
        data.Booking_Date = data.Booking_Date ?? (cells[3]?.textContent.trim()||'');
        data.type = data.type ?? (cells[4]?.textContent.trim()||'');
        data.Mode = data.Mode ?? (cells[5]?.textContent.trim().toLowerCase()||'');
        data.Created_At = data.Created_At ?? (cells[6]?.textContent.trim()||'');
        data.Status = data.Status ?? (cells[7]?.textContent.trim().toLowerCase()||'');
      }
      if(existing){ mergeFromRow(existing); }

      const date = normalizeDate(data.Booking_Date||'');
      const mode = (data.Mode||'').charAt(0).toUpperCase() + (data.Mode||'').slice(1);
      const bookedAt = data.Created_At ? new Date(data.Created_At).toLocaleString('en-US', { month:'short', day:'2-digit', year:'numeric', hour:'numeric', minute:'2-digit'}) : (existing? (existing.querySelectorAll('.table-cell')[6]?.textContent||'') : '');
      const status = (data.Status||'').charAt(0).toUpperCase() + (data.Status||'').slice(1);
      const iter = existing ? (existing.querySelector('.table-cell')?.textContent||'') : (rows.length+1);

      const html = `
        <div class="table-cell" data-label="No." data-booking-id="${data.Booking_ID}">${iter}</div>
        <div class="table-cell instructor-cell" data-label="Instructor">${data.Professor||''}</div>
        <div class="table-cell" data-label="Subject">${data.subject||''}</div>
        <div class="table-cell" data-label="Date">${date}</div>
        <div class="table-cell" data-label="Type">${data.type||''}</div>
        <div class="table-cell" data-label="Mode">${mode}</div>
        <div class="table-cell" data-label="Booked At">${bookedAt}</div>
        <div class="table-cell" data-label="Status">${status}</div>`;

      function setDataAttrs(row){
        const d = new Date(data.Booking_Date||'');
        const created = data.Created_At ? new Date(data.Created_At) : null;
        row.dataset.instructor = (data.Professor||'').toString().toLowerCase();
        row.dataset.subject = (data.subject||'').toString().toLowerCase();
        if(!isNaN(d.getTime())){
          row.dataset.date = `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
          row.dataset.dateTs = String(Math.floor(d.getTime()/1000));
        }
        row.dataset.type = (data.type||'').toString().toLowerCase();
        row.dataset.mode = (data.Mode||'').toString().toLowerCase();
        if(created && !isNaN(created.getTime())){
          row.dataset.booked = `${created.getFullYear()}-${String(created.getMonth()+1).padStart(2,'0')}-${String(created.getDate()).padStart(2,'0')} ${String(created.getHours()).padStart(2,'0')}:${String(created.getMinutes()).padStart(2,'0')}:00`;
          row.dataset.bookedTs = String(Math.floor(created.getTime()/1000));
        }
        row.dataset.status = (data.Status||'').toString().toLowerCase();
        row.dataset.matched = '1';
      }

      if(existing){
        existing.innerHTML = html;
        // guarantee the data-booking-id attribute remains for subsequent updates
        const first = existing.querySelector('.table-cell'); if(first){ first.setAttribute('data-booking-id', String(data.Booking_ID)); }
        setDataAttrs(existing);
      }
      else {
        // Try to reuse any orphan row (missing data-booking-id) to avoid duplicates from earlier sessions
        const orphan = rows.find(r => !r.querySelector('[data-booking-id]'));
        if(orphan){
          // Merge current orphan cell values for fields not present in payload
          mergeFromRow(orphan);
          orphan.innerHTML = html;
          const first = orphan.querySelector('.table-cell'); if(first){ first.setAttribute('data-booking-id', String(data.Booking_ID)); }
          setDataAttrs(orphan);
        } else {
        const row = document.createElement('div');
        row.className = 'table-row';
        row.innerHTML = html;
        const first = row.querySelector('.table-cell'); if(first){ first.setAttribute('data-booking-id', String(data.Booking_ID)); }
        setDataAttrs(row);
        table.appendChild(row);
        }
      }

      if(typeof filterRows==='function') filterRows();
      rebuildSubjectOptions();
    }

    // Bind to the explicit alias and FQCN fallback to be safe across drivers
    channel.bind('BookingUpdated', renderRow);
    channel.bind('App\\Events\\BookingUpdatedStudent', renderRow);
  } catch(e){ console.warn('Realtime (student) init failed', e); }
})();

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
