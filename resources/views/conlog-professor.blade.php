<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Consultation Log</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/conlog-professor.css') }}">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pikaday/css/pikaday.css">
  
  
  <style>
    /* Reschedule Modal Styles */
    .reschedule-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.7);
      display: none;
      justify-content: center;
      align-items: center;
      z-index: 1000;
    }

    .reschedule-modal {
      background: white;
      border-radius: 12px;
      padding: 0;
      width: 90%;
      max-width: 450px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
      animation: modalSlideIn 0.3s ease-out;
    }

    @keyframes modalSlideIn {
      from {
        opacity: 0;
        transform: translateY(-50px) scale(0.9);
      }
      to {
        opacity: 1;
        transform: translateY(0) scale(1);
      }
    }

    .reschedule-header {
      background: #2c5f4f;
      color: white;
      padding: 20px;
      border-radius: 12px 12px 0 0;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .reschedule-header h3 {
      margin: 0;
      font-size: 18px;
      font-weight: 600;
    }

    .reschedule-header .close-btn {
      background: none;
      border: none;
      color: white;
      font-size: 24px;
      cursor: pointer;
      padding: 0;
      width: 30px;
      height: 30px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 50%;
      transition: background-color 0.3s;
    }

    .reschedule-header .close-btn:hover {
      background: rgba(255, 255, 255, 0.2);
    }

    .reschedule-body {
      padding: 25px;
    }

    .reschedule-body p {
      margin: 0 0 20px 0;
      color: #555;
      font-size: 14px;
    }

    .date-input-group {
      margin-bottom: 25px;
    }

    .date-input-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: 600;
      color: #333;
      font-size: 14px;
    }

    .date-input {
      width: 100%;
      padding: 12px;
      border: 2px solid #e1e5e9;
      border-radius: 8px;
      font-size: 14px;
      transition: border-color 0.3s;
      font-family: 'Poppins', sans-serif;
      resize: vertical;
    }

    .date-input:focus {
      outline: none;
      border-color: #2c5f4f;
      box-shadow: 0 0 0 3px rgba(44, 95, 79, 0.1);
    }

    .date-input[type="date"] {
      resize: none;
    }

    .reschedule-buttons {
      display: flex;
      gap: 12px;
      justify-content: flex-end;
    }

    .btn-cancel,
    .btn-confirm {
      padding: 10px 20px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 14px;
      font-weight: 600;
      transition: all 0.3s;
      font-family: 'Poppins', sans-serif;
    }

    .btn-cancel {
      background: #f8f9fa;
      color: #6c757d;
      border: 1px solid #dee2e6;
    }

    .btn-cancel:hover {
      background: #e9ecef;
      color: #495057;
    }

    .btn-confirm {
      background: #2c5f4f;
      color: white;
    }

    .btn-confirm:hover {
      background: #1e4235;
      transform: translateY(-1px);
    }

    .btn-confirm:disabled {
      background: #ccc;
      cursor: not-allowed;
      transform: none;
    }
  </style>
</head>
<body>
  @include('components.navbarprof')
  <!-- Custom Modal HTML for Professor Message Handling -->
  <div class="custom-modal" id="professorModal">
    <div class="custom-modal-content">
      <span id="professorModalMessage"></span>
      <button class="custom-modal-btn" onclick="closeProfessorModal()">OK</button>
    </div>
  </div>

  <div class="main-content">
    <div class="header">
      <h1 style="display:flex;align-items:center;gap:14px;">Consultation Log
        <button id="print-logs-btn" type="button" class="print-logs-btn" title="Print Consultation Log">
          <i class='bx bx-printer'></i><span class="print-label">Print</span>
        </button>
      </h1>
    </div>

    <div class="search-container">
      <input type="text" id="searchInput" placeholder="Search..." style="flex:1;"
        autocomplete="off" spellcheck="false" maxlength="100"
        pattern="[A-Za-z0-9 .,@_-]{0,100}" aria-label="Search consultation">
      <button type="button" class="filters-btn" id="openFiltersBtn" aria-label="Open filters" title="Filters">
        <i class='bx bx-slider-alt'></i>
      </button>
      <div class="filter-group-horizontal">
        <select id="typeFilter" class="filter-select" aria-label="Type filter">
          <option value="">All Types</option>
          @php
            $fixedTypes = [
              'Tutoring',
              'Grade Consultation',
              'Missed Activities',
              'Special Quiz or Exam',
              'Capstone Consultation'
            ];
          @endphp
          @foreach($fixedTypes as $type)
            <option value="{{ $type }}">{{ $type }}</option>
          @endforeach
          <option value="Others">Others</option>
        </select>
      </div>
      <div class="filter-group-horizontal">
        @php
          $bookingsFiltered = collect($bookings ?? [])->filter(function($b){
            return strtolower($b->Status ?? '') !== 'cancelled';
          })->values();
          $subjects = collect($bookingsFiltered ?? [])->pluck('subject')->filter(fn($s)=>filled($s))
                       ->map(fn($s)=>trim($s))->unique()->sort()->values();
        @endphp
        <select id="subjectFilter" class="filter-select" aria-label="Subject filter">
          <option value="">All Subjects</option>
          @foreach($subjects as $s)
            <option value="{{ $s }}">{{ $s }}</option>
          @endforeach
        </select>
      </div>
      <div class="filter-group-horizontal page-size-group" style="margin-left:auto">
        <select id="pageSize" class="filter-select" aria-label="Items per page" style="width:92px">
          <option value="5">5</option>
          <option value="10" selected>10</option>
          <option value="25">25</option>
          <option value="50">50</option>
          <option value="100">100</option>
        </select>
        <span class="filter-label-inline items-per-page-label">items per page</span>
      </div>
    </div>

    <div class="table-container">
      <div class="table">
        <!-- Header Row -->
        <div class="table-row table-header" id="profConlogHeader">
          <div class="table-cell">No.</div>
          <div class="table-cell sort-header" data-sort="student" role="button" tabindex="0">Student <span class="sort-icon"></span></div>
          <div class="table-cell sort-header" data-sort="subject" role="button" tabindex="0">Subject <span class="sort-icon"></span></div>
          <div class="table-cell sort-header" data-sort="date" role="button" tabindex="0">Date <span class="sort-icon"></span></div>
          <div class="table-cell sort-header" data-sort="type" role="button" tabindex="0">Type <span class="sort-icon"></span></div>
          <div class="table-cell sort-header" data-sort="mode" role="button" tabindex="0">Mode <span class="sort-icon"></span></div>
          <div class="table-cell sort-header" data-sort="status" role="button" tabindex="0">Status <span class="sort-icon"></span></div>
          <div class="table-cell" style="width: 180px">Action</div>
        </div>
    
        <!-- Dynamic Data Rows -->
  @forelse($bookingsFiltered as $b)
  <div class="table-row"
    data-student="{{ strtolower($b->student) }}"
    data-subject="{{ strtolower($b->subject) }}"
    data-date="{{ \Carbon\Carbon::parse($b->Booking_Date)->format('Y-m-d') }}"
    data-date-ts="{{ \Carbon\Carbon::parse($b->Booking_Date)->timestamp }}"
    data-type="{{ strtolower($b->type) }}"
    data-mode="{{ strtolower($b->Mode) }}"
    data-status="{{ strtolower($b->Status) }}"
    data-matched="1"
  >
    <div class="table-cell" data-label="No." data-booking-id="{{ $b->Booking_ID }}">{{ $loop->iteration }}</div>
          <div class="table-cell" data-label="Student">{{ $b->student }}</div> <!-- Student name -->
          <div class="table-cell" data-label="Subject">{{ $b->subject }}</div>
          <div class="table-cell" data-label="Date">{{ \Carbon\Carbon::parse($b->Booking_Date)->format('D, M d Y') }}</div>
          <div class="table-cell" data-label="Type">{{ $b->type }}</div>
          <div class="table-cell" data-label="Mode">{{ ucfirst($b->Mode) }}</div>
          <div class="table-cell" data-label="Status">{{ ucfirst($b->Status) }}</div>
          <div class="table-cell" data-label="Action" style="width: 180px;">
            <div class="action-btn-group" style="display: flex; gap: 8px;">
              @if(strtolower($b->Status) !== 'completed')
                @if($b->Status !== 'rescheduled')
                <button 
                  onclick="showRescheduleModal({{ $b->Booking_ID }}, '{{ $b->Booking_Date }}', '{{ $b->Mode }}')" 
                  class="action-btn btn-reschedule"
                  title="Reschedule"
                >
                  <i class='bx bx-calendar-x'></i>
                </button>
                @endif

                @if($b->Status !== 'approved')
                <button 
                  onclick="approveWithWarning(this, {{ $b->Booking_ID }}, '{{ $b->Booking_Date }}')" 
                  class="action-btn btn-approve"
                  title="Approve"
                >
                  <i class='bx bx-check-circle'></i>
                </button>
                @endif

                @if($b->Status !== 'completed')
                <button 
                  onclick="confirmComplete(this, {{ $b->Booking_ID }})" 
                  class="action-btn btn-completed"
                  title="Completed"
                >
                  <i class='bx bx-task'></i>
                </button>
                @endif
              @endif
            </div>
          </div>
        </div>
        @empty
          <div class="table-row no-results-row">
            <div class="table-cell" style="text-align:center;color:#666;font-style:italic;">No Consultations Found.</div>
          </div>
        @endforelse
      <!-- Spacer removed: layout handled by CSS margins -->
      </div>
    </div>

    <!-- Pagination controls (no left info) -->
    <div class="pagination-bar">
      <div class="pagination-right">
        <div id="paginationControls" class="pagination"></div>
      </div>
    </div>

    <!-- Bottom scroll spacer for mobile chat overlay clearance -->
    <div class="bottom-safe-space" aria-hidden="true"></div>

    <!-- Mobile Filters Overlay -->
    <div class="filters-overlay" id="filtersOverlay" aria-hidden="true">
      <div class="filters-drawer" role="dialog" aria-modal="true" aria-labelledby="filtersTitle">
        <div class="filters-drawer-header">
          <h2 id="filtersTitle">Filters</h2>
          <button type="button" class="filters-close" id="closeFiltersBtn" aria-label="Close">×</button>
        </div>
        <div class="filters-drawer-body">
          <div class="filter-group">
            <label class="filter-label" for="typeFilterMobile">Type</label>
            <select id="typeFilterMobile" class="filter-select" aria-label="Type (mobile)">
              <option value="">All Types</option>
              @foreach($fixedTypes as $type)
                <option value="{{ $type }}">{{ $type }}</option>
              @endforeach
              <option value="Others">Others</option>
            </select>
          </div>
          <div class="filter-group">
            <label class="filter-label" for="subjectFilterMobile">Subject</label>
            <select id="subjectFilterMobile" class="filter-select" aria-label="Subject (mobile)">
              <option value="">All Subjects</option>
              @foreach($subjects as $s)
                <option value="{{ $s }}">{{ $s }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="filters-drawer-footer">
          <button type="button" class="btn-reset" id="resetFiltersBtn">Reset</button>
          <button type="button" class="btn-apply" id="applyFiltersBtn">Apply</button>
        </div>
      </div>
    </div>

    <button class="chat-button" onclick="toggleChat()">
      <i class='bx bxs-message-rounded-dots'></i>
      Click to chat with me!
    </button>

    <!-- Chat Overlay Panel -->
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
        <input type="text" id="message" placeholder="Type your message" required
               autocomplete="off" spellcheck="false" maxlength="250"
               pattern="[A-Za-z0-9 .,@_!?-]{1,250}" aria-label="Chat message">
        <button type="submit">Send</button>
      </form>
    </div>

    <!-- Reschedule Modal -->
    <div class="reschedule-overlay" id="rescheduleOverlay">
      <div class="reschedule-modal">
        <div class="reschedule-header">
          <h3>Reschedule Consultation</h3>
          <button class="close-btn" onclick="closeRescheduleModal()">×</button>
        </div>
        <div class="reschedule-body">
          <p><strong>Current Date:</strong> <span id="currentDate"></span></p>
          <div class="date-input-group">
            <label for="newDate">Select New Date:</label>
            <input type="text" id="newDate" class="date-input" placeholder="YYYY-MM-DD" required>
          </div>
          <div class="date-input-group">
            <label for="rescheduleReason">Reason for Rescheduling:</label>
            <textarea id="rescheduleReason" class="date-input" rows="3" placeholder="Please provide a reason for rescheduling this consultation..." required></textarea>
          </div>
          <div class="reschedule-buttons">
            <button type="button" class="btn-cancel" onclick="closeRescheduleModal()">Cancel</button>
            <button type="button" class="btn-confirm" onclick="confirmReschedule()">Reschedule</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Generic Confirmation Modal -->
    <div class="confirm-overlay" id="confirmOverlay" aria-hidden="true">
      <div class="confirm-modal" role="dialog" aria-modal="true" aria-labelledby="confirmTitle">
        <div class="confirm-header">
          <i class='bx bx-help-circle'></i>
          <div id="confirmTitle">Please confirm your action</div>
        </div>
        <div class="confirm-body">
          <div id="confirmMessage">Are you sure you want to continue?</div>
        </div>
        <div class="confirm-actions">
          <button type="button" class="btn-cancel-red" id="confirmCancelBtn">Cancel</button>
          <button type="button" class="btn-confirm-green" id="confirmOkBtn">Yes, proceed</button>
        </div>
      </div>
    </div>

    <!-- Approval Warning Modal -->
    <div class="reschedule-overlay approval-warning-modal" id="approvalWarningOverlay">
      <div class="reschedule-modal approval-warning-content">
        <div class="reschedule-header">
          <h3>⚠️ High Volume Warning</h3>
          <button class="close-btn" onclick="closeApprovalWarningModal()">×</button>
        </div>
        <div class="reschedule-body approval-warning-body">
          <div class="warning-info">
            <p>
              <i class='bx bx-info-circle'></i>
              This date already has <span id="existingConsultationsCount">5</span> approved consultations
            </p>
            <p>
              <strong>Date:</strong> <span id="warningDate"></span>
            </p>
          </div>
          <p class="warning-text">
            Are you sure you want to approve another consultation for this date? This will bring your total to <span id="totalAfterApproval">6</span> consultations.
          </p>
          <div class="reschedule-buttons">
            <button type="button" class="btn-cancel" onclick="showRescheduleFromWarning()">Reschedule Instead</button>
            <button type="button" class="btn-confirm" onclick="confirmApproval()">
              Yes, Approve Anyway
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Hidden printable container -->
    <div id="printLogsContainer" style="display:none;">
      <div class="print-header">
        <h2>Professor Consultation Log</h2>
  <div id="printProfessor" class="print-professor" 
      data-prof-name="{{ optional(auth()->guard('professor')->user())->Name ?? (auth()->user()->Name ?? auth()->user()->name ?? '') }}"
      data-prof-id="{{ optional(auth()->guard('professor')->user())->Prof_ID ?? (auth()->user()->Prof_ID ?? auth()->user()->id ?? '') }}"
      data-prof-schedule="{{ optional(auth()->guard('professor')->user())->Schedule ?? '' }}">
  </div>
  <div id="printMeta" class="print-meta"></div>
      </div>
      <table class="print-table" id="printLogsTable">
        <thead>
          <tr>
            <th>No.</th>
            <th>Student</th>
            <th>Subject</th>
            <th>Date</th>
            <th>Type</th>
            <th>Mode</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
      <div id="printFooter" class="print-footer-note"></div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/pikaday/pikaday.js"></script>
  <script>
    let currentBookingId = null;
    let currentRescheduleButton = null;
    let reschedulePicker = null;
    // Sets/maps used by disableDayFn (populated on modal open)
    let __resAllowedWeekdays = new Set(); // 1-5 = Mon-Fri
    let __resBlockedIso = new Set(); // 'YYYY-MM-DD' dates blocked by overrides
    let __resForcedByIso = new Map(); // iso -> forced_mode string ('online'|'onsite')

    function parseAllowedWeekdaysFromSchedule(scheduleText){
      const set = new Set();
      if(!scheduleText) return set;
      try{
        const lines = String(scheduleText).split(/\n|<br\s*\/>/i).map(s=>s.trim()).filter(Boolean);
        const nameToNum = { Monday:1, Tuesday:2, Wednesday:3, Thursday:4, Friday:5 };
        lines.forEach(line=>{
          const m = line.match(/^(Monday|Tuesday|Wednesday|Thursday|Friday)\b/i);
          if(m){
            const key = m[1].charAt(0).toUpperCase()+m[1].slice(1).toLowerCase();
            const n = nameToNum[key]; if(n) set.add(n);
          }
        });
      }catch(_){ }
      return set;
    }

    function isoFromDateObj(d){
      try { return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`; } catch(_){ return ''; }
    }

    function makeRescheduleDisableDayFn(){
      return function(date){
        const day = date.getDay(); // 0 Sun..6 Sat
        // Always block weekends
        if(day===0 || day===6) return true;
        // Overrides: if blocked and not specifically force-open by mode, disable
        const iso = isoFromDateObj(date);
        if(__resBlockedIso.has(iso)) return true;
        // If no schedule at all, block weekdays unless there's a forced mode override
        if(__resAllowedWeekdays.size===0){
          return __resForcedByIso.has(iso) ? false : true;
        }
        // If weekday not in allowed schedule, allow only if forced mode override exists
        if(!__resAllowedWeekdays.has(day)){
          return __resForcedByIso.has(iso) ? false : true;
        }
        return false; // allowed
      };
    }

    function showRescheduleModal(bookingId, currentDate, bookingMode) {
      currentBookingId = bookingId;
      // Resolve button context only if invoked via a click event
      try{
        const ev = (typeof event !== 'undefined') ? event : null;
        currentRescheduleButton = ev && ev.target && ev.target.closest ? ev.target.closest('button') : currentRescheduleButton;
      }catch(_){ /* ignore */ }
      
      // Set current date in the modal
      document.getElementById('currentDate').textContent = currentDate;
      
      // Prepare schedule context for disableDayFn
      const profIdEl = document.getElementById('printProfessor');
      const profSchedule = profIdEl ? (profIdEl.getAttribute('data-prof-schedule')||'') : '';
      __resAllowedWeekdays = parseAllowedWeekdaysFromSchedule(profSchedule);
      __resBlockedIso = new Set();
      __resForcedByIso = new Map();

      // Initialize/Reset Pikaday on the input
      const dateInput = document.getElementById('newDate');
      if(reschedulePicker && typeof reschedulePicker.destroy==='function'){
        reschedulePicker.destroy(); reschedulePicker = null;
      }
      dateInput.value = '';
      reschedulePicker = new Pikaday({
        field: dateInput,
        format: 'YYYY-MM-DD',
        firstDay: 1,
        minDate: new Date(),
        disableDayFn: makeRescheduleDisableDayFn(),
        onSelect: function(){
          // Keep ISO format for downstream validators
          dateInput.value = this.getMoment ? this.getMoment().format('YYYY-MM-DD') : this.toString();
          dateInput.dispatchEvent(new Event('input', { bubbles: true }));
        }
      });

      // Detect original mode from parameter or table cell fallback
      let originalMode = (bookingMode||'').toLowerCase();
      if(!originalMode){
        // Prefer resolving via the row with matching booking id
        let row = null;
        try{
          const cell = document.querySelector(`.table .table-cell[data-booking-id="${bookingId}"]`);
          row = cell ? cell.closest('.table-row') : null;
        }catch(_){ }
        if(!row && currentRescheduleButton) row = currentRescheduleButton.closest('.table-row');
        const modeCell = row ? row.querySelector('.table-cell[data-label="Mode"]') : null;
        originalMode = (modeCell ? (modeCell.textContent||'').trim().toLowerCase() : '').replace(/[^a-z]/g,'');
      }

      // Fetch fully booked dates and availability (with per-day mode) to enforce client-side rule
      Promise.all([
        fetch('/api/professor/fully-booked-dates').then(r=>r.json()).catch(()=>null),
        (function(){
          // Build a short range availability request for next 60 days
          try{
            const profId = profIdEl ? profIdEl.getAttribute('data-prof-id') : null;
            if(!profId) return Promise.resolve(null);
            const now = new Date();
            const start = now.toISOString().slice(0,10);
            const endDate = new Date(now.getFullYear(), now.getMonth()+2, 0);
            const end = endDate.toISOString().slice(0,10);
            return fetch(`/api/professor/availability?prof_id=${profId}&start=${start}&end=${end}`).then(r=>r.json()).catch(()=>null);
          }catch(e){ return Promise.resolve(null); }
        })()
      ]).then(([fullResp, availResp])=>{
        // Store fully-booked list for capacity validation
        dateInput.removeAttribute('data-full');
        if(fullResp && fullResp.success){ dateInput.setAttribute('data-full', JSON.stringify(fullResp.dates)); }

        // Store availability map to check per-day mode lock
        if(availResp && availResp.success){
          const map = {};
          (availResp.dates||[]).forEach(rec=>{ map[rec.date]=rec; });
          dateInput.setAttribute('data-avail', JSON.stringify(map));
          // Build overrides sets for disableDayFn (blocked/forced_mode)
          __resBlockedIso.clear(); __resForcedByIso.clear();
          (availResp.dates||[]).forEach(rec=>{
            // rec.date is like 'Mon Jan 01 2025' — convert to ISO
            try{
              const d = new Date(rec.date);
              const iso = `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
              if(rec.blocked) __resBlockedIso.add(iso);
              if(rec.forced_mode) __resForcedByIso.set(iso, rec.forced_mode);
            }catch(_){ }
          });
          // Redraw picker to apply new disable rules
          try{ reschedulePicker && reschedulePicker.draw && reschedulePicker.draw(); }catch(_){ }
        } else {
          dateInput.removeAttribute('data-avail');
        }

        // Attach one-time input validator combining capacity + mode rule
        dateInput.addEventListener('input', function(){
          try{
            if(!this.value){ this.setCustomValidity(''); return; }
            const d = new Date(this.value);
            const mapDays = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
            const mons = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
            const fmt = `${mapDays[d.getUTCDay()]} ${mons[d.getUTCMonth()]} ${('0'+d.getUTCDate()).slice(-2)} ${d.getUTCFullYear()}`;

            // Capacity check
            const full = JSON.parse(this.getAttribute('data-full')||'[]');
            if(full.includes(fmt)){
              this.setCustomValidity('This date is already fully booked (5 consultations). Choose another.');
              return;
            }

            // Mode rule: if the target date already has a lock, it must match original booking mode
            const avail = JSON.parse(this.getAttribute('data-avail')||'{}');
            const rec = avail[fmt];
            if(rec && rec.mode){
              if(originalMode && rec.mode !== originalMode){
                this.setCustomValidity(`This date is locked to ${rec.mode}. You can only reschedule this ${originalMode} booking to a ${originalMode} date.`);
                return;
              }
            }
            // If no lock on that day: allowed client-side; backend will enforce final rule
            this.setCustomValidity('');
          }catch(e){ this.setCustomValidity(''); }
        }, { once: true });

        document.getElementById('rescheduleOverlay').style.display = 'flex';
      }).catch(()=>{
        document.getElementById('rescheduleOverlay').style.display = 'flex';
      });
    }

    function closeRescheduleModal() {
      document.getElementById('rescheduleOverlay').style.display = 'none';
      currentBookingId = null;
      currentRescheduleButton = null;
      if(reschedulePicker && typeof reschedulePicker.destroy==='function'){
        try{ reschedulePicker.destroy(); }catch(_){ }
        reschedulePicker = null;
      }
      
      // Clear form fields
      document.getElementById('newDate').value = '';
      document.getElementById('rescheduleReason').value = '';
    }

    // Function to show reschedule modal from the approval warning
    function showRescheduleFromWarning() {
      if (!pendingApprovalBookingId) {
        showProfessorModal('Error: No booking selected for rescheduling.');
        return;
      }

      // Get the booking date from the warning modal
      const warningDateElement = document.getElementById('warningDate');
      const currentDate = warningDateElement ? warningDateElement.textContent : '';

      // Close the approval warning modal
      closeApprovalWarningModal();

      // Set up the reschedule modal with the pending booking data
      currentBookingId = pendingApprovalBookingId;
      currentRescheduleButton = pendingApprovalButton;

      // Set current date in the modal
      document.getElementById('currentDate').textContent = currentDate;
      // Defer to main initializer to setup picker, availability, and open modal
      showRescheduleModal(currentBookingId, currentDate, '');

      // Clear the pending approval variables since we're now rescheduling
      pendingApprovalButton = null;
      pendingApprovalBookingId = null;
      return;
    }

    function confirmReschedule() {
      const newDate = document.getElementById('newDate').value;
      const reason = document.getElementById('rescheduleReason').value.trim();

          // Client-side capacity check using cached fully booked list
          const input = document.getElementById('newDate');
          try {
            if (newDate && input.getAttribute('data-full')) {
              const full = JSON.parse(input.getAttribute('data-full'));
              const d = new Date(newDate);
              const map = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
              const mons = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
              const fmt = `${map[d.getUTCDay()]} ${mons[d.getUTCMonth()]} ${('0'+d.getUTCDate()).slice(-2)} ${d.getUTCFullYear()}`;
              if (full.includes(fmt)) {
                showProfessorModal('That date is already fully booked (5 consultations). Please pick another date.');
                return;
              }
            }
          } catch(e) {}
      
      if (!newDate) {
        showProfessorModal('Please select a new date.');
        return;
      }
      
      if (!reason) {
        showProfessorModal('Please provide a reason for rescheduling.');
        return;
      }
      
      if (!currentBookingId) {
        showProfessorModal('Error: Booking ID is missing. Please try again.');
        return;
      }
      
      // Store the values before closing modal (which sets them to null)
      const bookingId = currentBookingId;
      const rescheduleButton = currentRescheduleButton;
      
      // Convert date to a more readable format
      const dateObj = new Date(newDate);
      const options = { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' };
      const formattedDate = dateObj.toLocaleDateString('en-US', options);
      
      // Close modal (this sets currentBookingId and currentRescheduleButton to null)
      closeRescheduleModal();
      
      // Remove the button immediately for better UX
      if (rescheduleButton) {
        rescheduleButton.remove();
      }
      
      // Call the update function with the stored booking ID, date, and reason
      updateStatusWithDate(bookingId, 'rescheduled', formattedDate, reason);
    }

    function updateStatusWithDate(bookingId, status, newDate = null, reason = null) {
      
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
      
      if (!csrfToken) {
        showProfessorModal('Error: CSRF token not found. Please refresh the page and try again.');
        location.reload();
        return;
      }
      
      const requestBody = {
        id: bookingId,
        status: status.toLowerCase()
      };
      
      if (newDate) {
        requestBody.new_date = newDate;
      }
      
      if (reason) {
        requestBody.reschedule_reason = reason;
      }
      
      fetch('/api/consultations/update-status', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify(requestBody)
      })
      .then(response => {
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
      })
      .then(data => {
        if (data.success) {
          showProfessorModal('Success: ' + data.message);
          setTimeout(() => location.reload(), 3500); // Reload the page to reflect changes
        } else {
          showProfessorModal('Failed to update status: ' + data.message);
          setTimeout(() => location.reload(), 3500); // Reload to restore the original state
        }
      })
      .catch(error => {
        console.error('Fetch error:', error);
        showProfessorModal('Network error occurred while updating status. Please check your connection and try again.\n\nError: ' + error.message);
  setTimeout(() => location.reload(), 3500);
      });
    }

    function updateStatus(bookingId, status) {
      updateStatusWithDate(bookingId, status);
    }

    function removeThisButton(btn, bookingId, status) {
      const isCompleted = String(status||'').toLowerCase() === 'completed';
      const row = btn && btn.closest ? btn.closest('.table-row') : null;
      if (isCompleted && row) {
        // Clear all action buttons in this row
        const actionGroup = row.querySelector('.action-btn-group');
        if (actionGroup) actionGroup.innerHTML = '';
        else {
          const actionCell = row.querySelector('.table-cell[data-label="Action"]');
          if (actionCell) actionCell.innerHTML = '';
        }
        // Update the status cell immediately for better UX
        const statusCell = row.querySelector('.table-cell[data-label="Status"]');
        if (statusCell) statusCell.textContent = 'Completed';
        // Lock this row to prevent realtime re-render from re-adding buttons
        row.setAttribute('data-completed-lock', '1');
      } else {
        // Default: only remove the clicked button
        if (btn && btn.remove) btn.remove();
      }
      updateStatus(bookingId, status);
    }

    // Confirmation modal logic
    let __confirmPending = null;
    function showConfirm(message, onConfirm){
      const overlay = document.getElementById('confirmOverlay');
      const msg = document.getElementById('confirmMessage');
      const ok = document.getElementById('confirmOkBtn');
      const cancel = document.getElementById('confirmCancelBtn');
      if(!overlay||!msg||!ok||!cancel){ if(typeof onConfirm==='function') onConfirm(false); return; }
      msg.textContent = message || 'Are you sure you want to continue?';
      // clear prior
      if(__confirmPending){ ok.removeEventListener('click', __confirmPending); }
      const handler = ()=>{ overlay.style.display='none'; document.removeEventListener('keydown', escHandler); onConfirm && onConfirm(true); };
      const cancelHandler = ()=>{ overlay.style.display='none'; document.removeEventListener('keydown', escHandler); onConfirm && onConfirm(false); };
      function escHandler(e){ if(e.key==='Escape'){ cancelHandler(); } }
      __confirmPending = handler;
      ok.addEventListener('click', handler, { once: true });
      cancel.onclick = cancelHandler;
      overlay.style.display='flex';
      setTimeout(()=>document.addEventListener('keydown', escHandler), 0);
    }

    // Completed flow: ask confirmation first
    function confirmComplete(btn, bookingId){
      showConfirm('Mark this consultation as Completed? This will finalize the record.', (ok)=>{
        if(!ok) return; removeThisButton(btn, bookingId, 'Completed');
      });
    }

    // Variables to store approval context
    let pendingApprovalButton = null;
    let pendingApprovalBookingId = null;

    // New function to handle approval with warning
  function approveWithWarning(btn, bookingId, bookingDate) {
      console.log('approveWithWarning called:', { bookingId, bookingDate });
      
      // Store the context for later use
      pendingApprovalButton = btn;
      pendingApprovalBookingId = bookingId;

      // Count existing approved consultations for this date
      fetch('/api/consultations')
        .then(response => response.json())
        .then(data => {
          // Filter consultations for the specific date with approved status
          const consultationsOnDate = data.filter(consultation => {
            const consultationDate = new Date(consultation.Booking_Date).toDateString();
            const targetDate = new Date(bookingDate).toDateString();
            return consultationDate === targetDate && consultation.Status.toLowerCase() === 'approved';
          });

          const approvedCount = consultationsOnDate.length;
          console.log('Approved consultations on', bookingDate, ':', approvedCount);

          // Show warning if already 5 or more approved consultations
          if (approvedCount >= 5) {
            showApprovalWarningModal(bookingDate, approvedCount);
          } else {
            // Ask confirmation then approve if less than 5
            showConfirm('Approve this consultation? A notification will be sent to the student.', (ok)=>{
              if(!ok) return; removeThisButton(btn, bookingId, 'Approved');
            });
          }
        })
        .catch(error => {
          console.error('Error fetching consultation data:', error);
          // If we can't fetch data, show a generic warning
          showProfessorModal('Unable to verify consultation count. Please try again.');
        });
// Custom Modal JS (moved to global scope)
function showProfessorModal(message) {
  document.getElementById('professorModalMessage').textContent = message;
  document.getElementById('professorModal').style.display = 'flex';
}
function closeProfessorModal() {
  document.getElementById('professorModal').style.display = 'none';
}
    }

    function showApprovalWarningModal(bookingDate, currentCount) {
      const modal = document.getElementById('approvalWarningOverlay');
      const dateElement = document.getElementById('warningDate');
      const countElement = document.getElementById('existingConsultationsCount');
      const totalElement = document.getElementById('totalAfterApproval');

      // Format the date nicely
      const formattedDate = new Date(bookingDate).toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });

      dateElement.textContent = formattedDate;
      countElement.textContent = currentCount;
      totalElement.textContent = currentCount + 1;

      modal.style.display = 'flex';
    }

    function closeApprovalWarningModal() {
      const modal = document.getElementById('approvalWarningOverlay');
      modal.style.display = 'none';
      
      // Clear the context
      pendingApprovalButton = null;
      pendingApprovalBookingId = null;
    }

    function confirmApproval() {
      if (pendingApprovalButton && pendingApprovalBookingId) {
        // Proceed with the approval after second confirmation
        closeApprovalWarningModal();
        showConfirm('You are approving despite high volume on this date. Continue?', (ok)=>{
          if(!ok) return; removeThisButton(pendingApprovalButton, pendingApprovalBookingId, 'Approved');
        });
      }
    }

    // Close modal when clicking outside of it
    document.addEventListener('click', function(event) {
      const rescheduleModal = document.getElementById('rescheduleOverlay');
      const approvalWarningModal = document.getElementById('approvalWarningOverlay');
      
      if (event.target === rescheduleModal) {
        closeRescheduleModal();
      }
      
      if (event.target === approvalWarningModal) {
        closeApprovalWarningModal();
      }
    });

    // Close modal with Escape key
    document.addEventListener('keydown', function(event) {
      if (event.key === 'Escape') {
        closeRescheduleModal();
        closeApprovalWarningModal();
      }
    });

    const fixedTypes = [
      'tutoring',
      'grade consultation',
      'missed activities',
      'special quiz or exam',
      'capstone consultation'
    ];

    // Basic front-end sanitizer to reduce junk / obvious attempt strings
    function sanitize(input){
      if(!input) return '';
      let cleaned = input.replace(/\/\*.*?\*\//g,''); // remove /* */ comments
      cleaned = cleaned.replace(/--/g,' '); // collapse double dashes
      cleaned = cleaned.replace(/[;`'"<>]/g,' '); // strip risky punctuation
      cleaned = cleaned.replace(/\s+/g,' ').trim(); // normalize whitespace
      return cleaned.slice(0,250); // enforce hard limit
    }

  function filterRows() {
    const si = document.getElementById('searchInput');
    const raw = si.value;
    const cleaned = sanitize(raw).slice(0,50); // search shorter cap
    if(cleaned !== raw) si.value = cleaned; // reflect cleaned input
    let search = cleaned.toLowerCase();
        let type = document.getElementById('typeFilter').value.toLowerCase();
        let rows = document.querySelectorAll('.table-row:not(.table-header)');
        rows.forEach(row => {
            let rowType = row.querySelector('[data-label="Type"]')?.textContent.toLowerCase() || '';
            let student = row.querySelector('[data-label="Student"]')?.textContent.toLowerCase() || '';
            let rowSubject = row.querySelector('[data-label="Subject"]')?.textContent.toLowerCase() || '';

            // Is this row a custom type (not in fixedTypes)?
            let isOthers = fixedTypes.indexOf(rowType) === -1 && rowType !== '';

            let matchesType =
                !type ||
                (type !== "others" && rowType === type) ||
                (type === "others" && isOthers);

            let matchesSearch = student.includes(search) || rowSubject.includes(search) || rowType.includes(search);

            if (matchesSearch && matchesType) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

  document.getElementById('searchInput').addEventListener('input', filterRows);
  document.getElementById('typeFilter').addEventListener('change', filterRows);
  document.getElementById('subjectFilter')?.addEventListener('change', filterRows);

    // Chat form hardening (local only – actual server still validates)
    (function(){
      const chatForm = document.getElementById('chatForm');
      const chatInput = document.getElementById('message');
      if(!chatForm || !chatInput) return;
      chatInput.addEventListener('input', () => {
        const raw = chatInput.value;
        const cleaned = sanitize(raw);
        if(cleaned !== raw) chatInput.value = cleaned;
      });
      chatForm.addEventListener('submit', (e) => {
        const raw = chatInput.value;
        const cleaned = sanitize(raw);
        if(!cleaned){
          e.preventDefault();
          chatInput.value='';
          chatInput.focus();
          return;
        }
        if(cleaned !== raw) chatInput.value = cleaned;
      });
      chatInput.addEventListener('keydown', (e)=>{
        if(e.key==='Enter' && !e.shiftKey){
          e.preventDefault();
          chatForm.requestSubmit();
        }
      });
    })();

// ===== Sorting + Pagination (mirrors student log) =====
let sortKey = 'date';
let sortDir = 'desc';
let currentPage = 1;
let pageSize = parseInt(localStorage.getItem('proflog.pageSize')||'10',10);
if(![5,10,25,50,100].includes(pageSize)) pageSize = 10;
document.addEventListener('DOMContentLoaded',()=>{ const ps=document.getElementById('pageSize'); if(ps) ps.value=String(pageSize); });

function profGetRows(){
  return Array.from(document.querySelectorAll('.table .table-row'))
    .filter(r=>!r.classList.contains('table-header') && !r.classList.contains('no-results-row'));
}

function profSetSortIndicators(){
  const headers = document.querySelectorAll('#profConlogHeader .sort-header');
  headers.forEach(h=>{
    const icon = h.querySelector('.sort-icon');
    const key = h.getAttribute('data-sort');
    if(key===sortKey){ icon.textContent = sortDir==='asc' ? ' ▲' : ' ▼'; h.classList.add('active-sort'); }
    else { icon.textContent=''; h.classList.remove('active-sort'); }
  });
}

function profCompare(a,b){
  const get=(row,key)=>{
    if(key==='date') return Number(row.dataset.dateTs||0);
    return (row.dataset[key]||'')+'';
  };
  const va=get(a,sortKey), vb=get(b,sortKey);
  let cmp = (typeof va==='number' && typeof vb==='number')? (va-vb) : (va.localeCompare(vb));
  return sortDir==='asc'? cmp : -cmp;
}

function profApply(){
  const table=document.querySelector('.table'); if(!table) return;
  const header=document.getElementById('profConlogHeader');
  const rows=profGetRows();
  const matched=rows.filter(r=>r.dataset.matched==='1');
  const existingNo = document.querySelector('.no-results-row'); if(existingNo) existingNo.remove();
  if(matched.length===0){
    rows.forEach(r=>r.style.display='none');
    const noRow=document.createElement('div'); noRow.className='table-row no-results-row';
    noRow.innerHTML = `<div class="table-cell" style="text-align:center;padding:20px;color:#666;font-style:italic;grid-column:1 / -1;">No Consultations Found.</div>`;
    header.insertAdjacentElement('afterend', noRow);
    const pag=document.getElementById('paginationControls'); if(pag) pag.innerHTML='';
    profSetSortIndicators(); return;
  }
  matched.sort(profCompare);
  const frag=document.createDocumentFragment(); matched.forEach(r=>frag.appendChild(r)); table.appendChild(frag);
  const total=matched.length; const totalPages=Math.max(1, Math.ceil(total/pageSize));
  if(currentPage>totalPages) currentPage=totalPages;
  const start=(currentPage-1)*pageSize; const end=Math.min(total, start+pageSize)-1;
  const set=new Set(matched);
  rows.forEach(r=>{
    if(!set.has(r)) { r.style.display='none'; return; }
    const idx=matched.indexOf(r);
    r.style.display = (idx>=start && idx<=end) ? '' : 'none';
  });
  const pag=document.getElementById('paginationControls');
  if(pag){
    const makeBtn=(label,target,disabled=false)=>{ const b=document.createElement('button'); b.className='page-btn'; b.textContent=label; b.disabled=disabled; b.addEventListener('click',()=>{ currentPage=target; profApply();}); return b; };
    pag.innerHTML='';
    const totalPagesCalc = Math.max(1, Math.ceil(total/pageSize));
    const prev = makeBtn('‹', Math.max(1,currentPage-1), currentPage===1); prev.classList.add('chev','prev'); pag.appendChild(prev);
    const lbl=document.createElement('span'); lbl.className='page-label'; lbl.textContent='Page'; pag.appendChild(lbl);
    const sel=document.createElement('select'); sel.className='page-select'; for(let p=1;p<=totalPagesCalc;p++){ const o=document.createElement('option'); o.value=String(p); o.textContent=String(p); if(p===currentPage) o.selected=true; sel.appendChild(o);} sel.addEventListener('change',(e)=>{ const v=parseInt(e.target.value,10)||1; currentPage=Math.min(Math.max(1,v), totalPagesCalc); profApply();}); pag.appendChild(sel);
    const of=document.createElement('span'); of.className='page-of'; of.textContent=`of ${totalPagesCalc}`; pag.appendChild(of);
    const next = makeBtn('›', Math.min(totalPagesCalc,currentPage+1), currentPage===totalPagesCalc); next.classList.add('chev','next'); pag.appendChild(next);
  }
  profSetSortIndicators();
}

// Override filterRows to cooperate with pagination
function filterRows(){
  const si=document.getElementById('searchInput');
  const search=(si.value||'').toLowerCase();
  const type=(document.getElementById('typeFilter')?.value||'').toLowerCase();
  const subject=(document.getElementById('subjectFilter')?.value||'').toLowerCase();
  document.querySelectorAll('.table-row:not(.table-header)').forEach(row=>{
    if(row.classList.contains('no-results-row')) return;
    const rowType=(row.dataset.type||'').toLowerCase();
    const student=(row.dataset.student||'').toLowerCase();
    const rowSubject=(row.dataset.subject||'').toLowerCase();
    const isOthers = !['tutoring','grade consultation','missed activities','special quiz or exam','capstone consultation'].includes(rowType) && rowType!=='';
    const matchesType = !type || (type!=='others' && rowType===type) || (type==='others' && isOthers);
    const matchesSubject = !subject || rowSubject===subject;
    const matchesSearch = student.includes(search) || rowSubject.includes(search) || rowType.includes(search);
    row.dataset.matched = (matchesType && matchesSubject && matchesSearch) ? '1' : '0';
  });
  currentPage = 1;
  profApply();
}

// listeners
document.getElementById('pageSize')?.addEventListener('change',(e)=>{
  pageSize = parseInt(e.target.value,10) || 10;
  localStorage.setItem('proflog.pageSize', String(pageSize));
  currentPage = 1;
  profApply();
});
document.querySelectorAll('#profConlogHeader .sort-header').forEach(h=>{
  const set=()=>{ const key=h.getAttribute('data-sort'); if(sortKey===key){ sortDir=(sortDir==='asc'?'desc':'asc'); } else { sortKey=key; sortDir=(key==='date'?'desc':'asc'); } profApply(); };
  h.addEventListener('click', set);
  h.addEventListener('keypress', (e)=>{ if(e.key==='Enter'||e.key===' '){ e.preventDefault(); set(); }});
});
document.addEventListener('DOMContentLoaded', ()=>{ filterRows(); });

// Mobile filters overlay
function profSyncOverlay(){
  const tMain=document.getElementById('typeFilter');
  const sMain=document.getElementById('subjectFilter');
  const tMob=document.getElementById('typeFilterMobile');
  const sMob=document.getElementById('subjectFilterMobile');
  if(tMain && tMob) tMob.value=tMain.value;
  if(sMain && sMob) {
    const seen=new Set();
    profGetRows().forEach(r=>{ const v=(r.dataset.subject||'').trim(); if(v) seen.add(v); });
    const arr=Array.from(seen).sort((a,b)=>a.localeCompare(b));
    sMob.innerHTML = '<option value="">All Subjects</option>' + arr.map(v=>`<option value="${v}">${v}</option>`).join('');
    sMob.value = sMain.value;
  }
}
function openFilters(){ const ov=document.getElementById('filtersOverlay'); if(!ov) return; profSyncOverlay(); ov.classList.add('open'); ov.setAttribute('aria-hidden','false'); document.body.style.overflow='hidden'; }
function closeFilters(){ const ov=document.getElementById('filtersOverlay'); if(!ov) return; ov.classList.remove('open'); ov.setAttribute('aria-hidden','true'); document.body.style.overflow=''; }
function applyFiltersFromOverlay(){ const tMain=document.getElementById('typeFilter'); const sMain=document.getElementById('subjectFilter'); const tMob=document.getElementById('typeFilterMobile'); const sMob=document.getElementById('subjectFilterMobile'); if(tMain&&tMob){ tMain.value=tMob.value; tMain.dispatchEvent(new Event('change')); } if(sMain&&sMob){ sMain.value=sMob.value; sMain.dispatchEvent(new Event('change')); } closeFilters(); }
function resetFiltersOverlay(){ const tMob=document.getElementById('typeFilterMobile'); const sMob=document.getElementById('subjectFilterMobile'); if(tMob) tMob.value=''; if(sMob) sMob.value=''; }
document.getElementById('openFiltersBtn')?.addEventListener('click', openFilters);
document.getElementById('closeFiltersBtn')?.addEventListener('click', closeFilters);
document.getElementById('applyFiltersBtn')?.addEventListener('click', applyFiltersFromOverlay);
document.getElementById('resetFiltersBtn')?.addEventListener('click', resetFiltersOverlay);

    // Real-time updates for professor consultation log - DISABLED TO PREVENT DUPLICATE ROWS
    /*
    function loadProfessorConsultationLogs() {
      fetch('/api/professor/consultation-logs')
        .then(response => response.json())
        .then(data => {
          updateProfessorConsultationTable(data);
        })
        .catch(error => {
          console.error('Error loading professor consultation logs:', error);
        });
    }

    function updateProfessorConsultationTable(bookings) {
      const table = document.querySelector('.table');
      const header = document.querySelector('.table-header');
      
      // Clear existing rows except header
      const existingRows = table.querySelectorAll('.table-row:not(.table-header)');
      existingRows.forEach(row => row.remove());
      
      if (bookings.length === 0) {
        const emptyRow = document.createElement('div');
        emptyRow.className = 'table-row';
        emptyRow.innerHTML = `
          <div class="table-cell" colspan="9">No consultations found.</div>
        `;
        table.appendChild(emptyRow);
      } else {
        bookings.forEach((booking, index) => {
          const row = document.createElement('div');
          row.className = 'table-row';
          
          const bookingDate = new Date(booking.Booking_Date);
          const createdAt = new Date(booking.Created_At);
          
          let statusActions = '';
          if (booking.Status.toLowerCase() === 'pending') {
            statusActions = `
              <button class="action-btn approve-btn" onclick="updateStatus(${booking.Booking_ID}, 'approved')">Approve</button>
              <button class="action-btn reschedule-btn" onclick="showRescheduleModal(${booking.Booking_ID})">Reschedule</button>
            `;
          } else if (booking.Status.toLowerCase() === 'approved') {
            statusActions = `
              <button class="action-btn complete-btn" onclick="updateStatus(${booking.Booking_ID}, 'completed')">Complete</button>
              <button class="action-btn reschedule-btn" onclick="showRescheduleModal(${booking.Booking_ID})">Reschedule</button>
            `;
          } else {
            statusActions = `<span class="status-final">${booking.Status.charAt(0).toUpperCase() + booking.Status.slice(1)}</span>`;
          }
          
          row.innerHTML = `
            <div class="table-cell" data-label="No.">${index + 1}</div>
            <div class="table-cell" data-label="Student">${booking.student || 'N/A'}</div>
            <div class="table-cell" data-label="Subject">${booking.subject}</div>
            <div class="table-cell" data-label="Date">${bookingDate.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' })}</div>
            <div class="table-cell" data-label="Type">${booking.type}</div>
            <div class="table-cell" data-label="Mode">${booking.Mode.charAt(0).toUpperCase() + booking.Mode.slice(1)}</div>
            <div class="table-cell" data-label="Status">${booking.Status.charAt(0).toUpperCase() + booking.Status.slice(1)}</div>
            <div class="table-cell action-cell" data-label="Action">${statusActions}</div>
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
    loadProfessorConsultationLogs();
    setInterval(loadProfessorConsultationLogs, 5000);
    */

    // Mobile notification functions for navbar
    function toggleMobileNotifications() {
      const dropdown = document.getElementById('mobileNotificationsDropdown');
      if (dropdown) {
        dropdown.classList.toggle('active');
        
        // Close sidebar if open
        const sidebar = document.getElementById('sidebar');
        if (sidebar && sidebar.classList.contains('active')) {
          sidebar.classList.remove('active');
        }
        
        // Load notifications if opening dropdown
        if (dropdown.classList.contains('active')) {
          loadMobileNotifications();
        }
      }
    }

    function loadMobileNotifications() {
      fetch('/api/professor/notifications')
        .then(response => response.json())
        .then(data => {
          displayMobileNotifications(data.notifications);
          updateMobileNotificationBadge();
        })
        .catch(error => {
          console.error('Error loading mobile notifications:', error);
        });
    }

    function displayMobileNotifications(notifications) {
      const mobileContainer = document.getElementById('mobileNotificationsContainer');
      if (!mobileContainer) return;
      
      if (notifications.length === 0) {
        mobileContainer.innerHTML = `
          <div class="no-notifications">
            <i class='bx bx-bell-off'></i>
            <p>No notifications yet</p>
          </div>
        `;
        return;
      }
      
      const notificationsHtml = notifications.map(notification => {
        const unreadClass = notification.is_read ? '' : 'unread';
        
        return `
          <div class="notification-item ${unreadClass}" onclick="markMobileNotificationAsRead(${notification.id})">
            <div class="notification-type ${notification.type}">${notification.type.replace('_', ' ')}</div>
            <div class="notification-title">${notification.title}</div>
            <div class="notification-message">${notification.message}</div>
            <div class="notification-time" data-timeago data-ts="${notification.created_at}"></div>
          </div>
        `;
      }).join('');
      
      mobileContainer.innerHTML = notificationsHtml;
    }

    function updateMobileNotificationBadge() {
      fetch('/api/professor/notifications/unread-count')
        .then(response => response.json())
        .then(data => {
          const mobileCountElement = document.getElementById('mobileNotificationBadge');
          if (mobileCountElement) {
            if (data.unread_count > 0) {
              mobileCountElement.textContent = data.unread_count;
              mobileCountElement.style.display = 'flex';
            } else {
              mobileCountElement.style.display = 'none';
            }
          }
        })
        .catch(error => {
          console.error('Error updating mobile notification badge:', error);
        });
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
          loadMobileNotifications(); // Reload to update read status
        }
      })
      .catch(error => {
        console.error('Error marking mobile notification as read:', error);
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
          loadMobileNotifications(); // Reload to update read status
        }
      })
      .catch(error => {
        console.error('Error marking all professor notifications as read:', error);
      });
    }

    // Live timeago handled by public/js/timeago.js

    // Initialize mobile notifications on page load
    document.addEventListener('DOMContentLoaded', function() {
      updateMobileNotificationBadge();
      // Update badge every 30 seconds
      setInterval(updateMobileNotificationBadge, 30000);
  const printBtn = document.getElementById('print-logs-btn');
   if (printBtn) printBtn.addEventListener('click', generateAndDownloadPdf);
    });
  </script>
  <script src="https://js.pusher.com/7.0/pusher.min.js"></script>
  <script>
    // Live updates: subscribe to the professor's booking channel and patch rows in-place
    (function(){
      try {
        const profEl = document.getElementById('printProfessor');
        const profId = profEl ? profEl.getAttribute('data-prof-id') : null;
        if(!profId) return;
        const pusher = new Pusher('{{ config('broadcasting.connections.pusher.key') }}', {cluster: '{{ config('broadcasting.connections.pusher.options.cluster') }}'});
  const channel = pusher.subscribe('bookings.prof.'+profId);

        function normalizeDate(str){ try{ return new Date(str).toLocaleDateString('en-US',{weekday:'short', month:'short', day:'numeric', year:'numeric'}); }catch(e){ return str; } }
        function titleCase(s){ return (s||'').toLowerCase().replace(/^.|\s. /g, c => c.toUpperCase()); }
        function renderRow(data){
          const table = document.querySelector('.table');
          if(!table) return;
          // find existing row by Booking_ID
          const rows = Array.from(table.querySelectorAll('.table-row')).filter(r=>!r.classList.contains('table-header'));
          let existing = null; let index = 0;
          rows.forEach((r,i)=>{ const idCell = r.querySelector('[data-booking-id]'); if(idCell && parseInt(idCell.getAttribute('data-booking-id'))===parseInt(data.Booking_ID)){ existing = r; index=i; } });

          // If cancelled, remove the row and refresh UI
          if(String((data.Status||'')).toLowerCase()==='cancelled'){
            if(existing){ try{ existing.remove(); }catch(e){} }
            if(typeof filterRows==='function') filterRows();
            return;
          }

          // If updating and some fields are missing, read them from the existing row
          if(existing){
            const cells = existing.querySelectorAll('.table-cell');
            data.student = data.student ?? (cells[1]?.textContent.trim()||'');
            data.subject = data.subject ?? (cells[2]?.textContent.trim()||'');
            data.Booking_Date = data.Booking_Date ?? (cells[3]?.textContent.trim()||'');
            data.type = data.type ?? (cells[4]?.textContent.trim()||'');
            data.Mode = data.Mode ?? (cells[5]?.textContent.trim().toLowerCase()||'');
            data.Status = data.Status ?? (cells[6]?.textContent.trim().toLowerCase()||'');
          }

          const mode = (data.Mode||'').charAt(0).toUpperCase() + (data.Mode||'').slice(1);
          // If the row was locally locked as completed, force completed state to avoid re-adding buttons
          const lockedCompleted = existing && existing.getAttribute('data-completed-lock') === '1';
          if (lockedCompleted) { data.Status = 'completed'; }
          const status = (data.Status||'').charAt(0).toUpperCase() + (data.Status||'').slice(1);
          const date = normalizeDate(data.Booking_Date||'');
          const iter = existing ? (existing.querySelector('.table-cell')?.textContent||'') : (rows.length+1);

          const isCompletedStatus = (data.Status||'').toLowerCase() === 'completed';
          const actionsHtml = isCompletedStatus
            ? `<div class="action-btn-group" style="display:flex;gap:8px;"></div>`
            : `
            <div class="action-btn-group" style="display:flex;gap:8px;">
              ${data.Status?.toLowerCase()!=='rescheduled' ? `<button onclick="showRescheduleModal(${data.Booking_ID}, '${data.Booking_Date}', '${data.Mode||''}')" class="action-btn btn-reschedule" title="Reschedule"><i class='bx bx-calendar-x'></i></button>`:''}
              ${data.Status?.toLowerCase()!=='approved' ? `<button onclick="approveWithWarning(this, ${data.Booking_ID}, '${data.Booking_Date}')" class="action-btn btn-approve" title="Approve"><i class='bx bx-check-circle'></i></button>`:''}
              ${data.Status?.toLowerCase()!=='completed' ? `<button onclick="confirmComplete(this, ${data.Booking_ID})" class="action-btn btn-completed" title="Completed"><i class='bx bx-task'></i></button>`:''}
            </div>`;

          const html = `
            <div class="table-cell" data-label="No.">${iter}</div>
            <div class="table-cell" data-label="Student">${data.student||'N/A'}</div>
            <div class="table-cell" data-label="Subject">${data.subject||''}</div>
            <div class="table-cell" data-label="Date">${date}</div>
            <div class="table-cell" data-label="Type">${data.type||''}</div>
            <div class="table-cell" data-label="Mode">${mode}</div>
            <div class="table-cell" data-label="Status">${status}</div>
            <div class="table-cell" data-label="Action" style="width:180px;">${actionsHtml}</div>`;

          if(existing){ existing.innerHTML = html; existing.querySelector('.table-cell').setAttribute('data-booking-id', data.Booking_ID); }
          else {
            const row = document.createElement('div');
            row.className = 'table-row';
            row.innerHTML = html;
            // attach booking id to first cell for lookup next time
            const first = row.querySelector('.table-cell'); if(first){ first.setAttribute('data-booking-id', data.Booking_ID); }
            table.appendChild(row);
          }

          // Re-apply filters to respect current UI state
          if(typeof filterRows==='function') filterRows();
        }

        channel.bind('BookingUpdated', function(data){
          // data.event may be 'BookingCreated' or 'BookingUpdated'
          renderRow(data);
        });
        // Fallback for environments where event name is the FQCN
        channel.bind('App\\Events\\BookingUpdated', function(data){
          renderRow(data);
        });
      } catch(e) { console.warn('Realtime init failed', e); }
    })();
  </script>
  <script src="{{ asset('js/ccit.js') }}"></script>
  <script>
    // Custom Modal JS (guaranteed global)
    function showProfessorModal(message) {
      document.getElementById('professorModalMessage').textContent = message;
      document.getElementById('professorModal').style.display = 'flex';
    }
    function closeProfessorModal() {
      document.getElementById('professorModal').style.display = 'none';
    }

    // PDF DOWNLOAD FEATURE
    function generateAndDownloadPdf(){
      try {
        const rows = Array.from(document.querySelectorAll('.table-row')).filter(r => !r.classList.contains('table-header'));
        const data = [];
        rows.forEach(r => {
          if (r.style.display === 'none') return; // respect active filters
          const cells = r.querySelectorAll('.table-cell');
          if(cells.length < 7) return;
          data.push({
            no: cells[0]?.innerText.trim() || '',
            student: cells[1]?.innerText.trim() || '',
            subject: cells[2]?.innerText.trim() || '',
            date: cells[3]?.innerText.trim() || '',
            type: cells[4]?.innerText.trim() || '',
            mode: cells[5]?.innerText.trim() || '',
            status: cells[6]?.innerText.trim() || ''
          });
        });
        if (data.length === 0){ alert('No consultation to print.'); return; }
        // sort by date then student
        data.sort((a,b)=> parseDate(a.date) - parseDate(b.date) || a.student.localeCompare(b.student));
        // Prepare payload for server
        const payload = data.map(d => ({
          student: d.student,
          subject: d.subject,
          date: d.date,
          type: d.type,
          mode: d.mode,
          status: d.status
        }));
        fetch("{{ route('conlog-professor.pdf') }}", {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: JSON.stringify({ logs: payload })
        }).then(res => {
          if(!res.ok) throw new Error('Failed to generate PDF');
          return res.blob();
        }).then(blob => {
          const url = window.URL.createObjectURL(blob);
          const a = document.createElement('a');
          a.href = url;
          a.download = 'consultation_logs.pdf';
          document.body.appendChild(a);
          a.click();
          a.remove();
          setTimeout(()=>window.URL.revokeObjectURL(url), 1500);
        }).catch(e => {
          console.error(e); alert('PDF generation failed.');
        });
      } catch(err){
        console.error('Export error', err); alert('Failed to prepare data.');
      }
    }
    function parseDate(str){ const d = new Date(str); return isNaN(d)? Infinity : d; }
    function extractCreatedAt(){ return ''; }
    function escapeHtml(s){ return String(s).replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[c])); }
  function getPrintStyles(){ return `body{font-family:Poppins,Arial,sans-serif;margin:24px;}h2{margin:0 0 4px;color:#12372a;font-size:26px;} .print-professor{font-size:12px;color:#234b3b;margin-bottom:2px;font-weight:500;} .print-meta{font-size:12px;color:#555;margin-bottom:12px;}table{width:100%;border-collapse:collapse;font-size:12px;}th,td{border:1px solid #222;padding:6px 8px;text-align:left;}th{background:#12372a;color:#fff;font-weight:600;} .status-badge{padding:2px 6px;border-radius:4px;font-weight:600;font-size:11px;color:#fff;display:inline-block;} .status-badge.status-pending{background:#ffa600;} .status-badge.status-approved{background:#27ae60;} .status-badge.status-completed{background:#093b2f;} .status-badge.status-rescheduled{background:#c50000;} .print-footer-note{margin-top:22px;font-size:11px;color:#444;text-align:right;}@media print{body{margin:0;padding:0;} }`; }
  </script>
  <script src="{{ asset('js/timeago.js') }}"></script>
</body>
</html>