<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>IT&IS Department</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/itis.css') }}">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pikaday/css/pikaday.css">

  <style>

  #calendar {
    /* Calendar input field styling */
    border: 1px solid #ccc;
    padding: 8px;
    border-radius: 4px;
    width: 100%;
    display: none !important; /* Hide the calendar input field */
   }

  /* Reverted original nav button styling */
  .pika-prev, .pika-next {
    background-color: #0d2b20; /* darker fill */
    border-radius: 50%;
    color: #ffffff;
    border: 2px solid #071a13; /* even darker edge */
    font-size: 18px;
    padding: 10px;
    width: 38px !important;
    height: 38px;
    display: flex; align-items: center; justify-content: center;
    opacity: 100%;
    text-indent: -9999px; /* hide default text */
    position: relative;
    overflow: hidden;
    background-image:none !important;
  }
  .pika-prev:after, .pika-next:after {
    content: '';
    position: absolute;
    top: 46%; /* slightly upward */
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 24px; /* bigger arrow */
    line-height: 1;
    font-weight: 700;
    color: #ffffff; /* white arrow */
    text-indent: 0;
    z-index: 2;
  }
  .pika-prev:after { content: '\2039'; }
  .pika-next:after { content: '\203A'; }


  .pika-single {
    display: block !important;  /* Make sure the calendar is always visible */
    /* height: 300px; */
    border: none;
  }

  .pika-table {
    border-radius: 3px;
    width: 100%;
    /* height: 264px; */
    border-collapse: separate;
    border-spacing: 3px;
  }

  .pika-label {
    color: #12372a;
    font-size: 25px
  }

  .pika-day {
    text-align: center;
  }

  .pika-lendar{
    width: 100%;
    display: flex;
    flex-direction: column;
  }

  /* Available day buttons: use green instead of grey */
  .pika-button{
    background-color:#01703c; /* previously #888 */
    border-radius:4px;
    color:#ffffff;
    padding:10px;
    height:40px;
    margin:5px 0;
    transition:background .18s, transform .18s;
  }

  /* Availability color states */
  .slot-free .pika-button { background:#01703c !important; }
  .slot-low .pika-button { background:#e6a100 !important; }
  .slot-full .pika-button { background:#b30000 !important; }
  .slot-free .pika-button:hover { background:#0d2b20 !important; }
  .slot-low .pika-button:hover { background:#cc8f00 !important; }
  .slot-full .pika-button:hover { background:#990000 !important; }
  .slot-full .pika-button[disabled] { pointer-events:none; opacity:0.95; }
  .slot-full .pika-button { cursor: not-allowed; }

  .availability-legend { display:flex; gap:14px; font-size:12px; margin:6px 0 4px; flex-wrap:wrap; }
  .availability-legend span { display:flex; align-items:center; gap:6px; }
  .availability-legend i { width:14px; height:14px; border-radius:3px; display:inline-block; }
  .legend-free { background:#01703c; }
  .legend-low { background:#e6a100; }
  .legend-full { background:#b30000; }

  .pika-button:hover,
  .pika-row.pick-whole-week:hover .pika-button {
    color:#fff;
    background:#0d2b20; /* darker on hover */
    box-shadow:none;
    border-radius:4px;
  }

  .is-selected .pika-button, .has-event .pika-button{
    color: #ffffff;
    background-color: #12372a !important;
    box-shadow: none;
  }

  .is-today .pika-button {
    color: #fff;
    background-color:#5fb9d4;
    font-weight: bold;
  }

  .is-today .pika-button  { 
    color: #ffffff
  }

  /* Better contrast for disabled (blocked) days so they don't blend with page background */
  .is-disabled .pika-button,
  .pika-button.is-disabled {
    background: #e5f0ed !important; /* match page background */
    color: #94a5a0 !important;      /* softened text */
    border: 1px solid #d0dbd8;      /* subtle outline */
    opacity: 1 !important;
    cursor: not-allowed;
  }
  .is-disabled .pika-button { background-image: none; }

  /* Larger label for Select Date */
  .calendar-wrapper-container label[for="calendar"] {
    font-size: 1.15rem;
    font-weight: 600;
    color: #12372a;
    display: inline-block;
    margin-bottom: 6px;
  }
  /* Hover should not change disabled look */
  .is-disabled .pika-button:hover { background: #f1f4f6 !important; color:#b3bcc3 !important; }

  /* === Restored weekday header styling (green variants) === */
  .pika-table th { 
    background-color: #12372a; /* default for Tue-Sat (except Sunday & Monday variant) */
    color: #fff; 
    border-radius: 4px; 
    padding: 5px; 
    transition: background-color .25s, opacity .25s; 
  }
  /* Monday & Sunday variant (lighter green, larger padding like original) */
  .pika-table th.weekday-mon,
  .pika-table th.weekday-sun { 
    background-color: #01703c; 
    padding: 10px; 
  }
  /* Keep semantic state classes (opacity changes only if you later want) */
  .pika-table th.allowed-day { /* intentionally same bg, full opacity */ }
  .pika-table th.disallowed-day { /* could dim if desired */ }
  .pika-table th.weekend-day { /* Sunday already styled via weekday-sun; Saturday keeps default */ }

  /* Notification: top-right corner above modal */
  .notification {
    position: fixed;
    top: 18px;
    right: 22px;
    left: auto;
    transform: none;
    z-index: 12000;
    max-width: 420px;
    width: auto;
  }

  /* Minimal helper: dim label when disabled (class applied via JS); keep main CSS in public css */
  .mode-selection label.disabled { opacity:.6; cursor:not-allowed; pointer-events:none; }
  /* Calendar error highlight */
  .calendar-wrapper-container.has-error { outline:2px solid #d93025; border-radius:8px; padding:4px 6px 10px; }
  .calendar-wrapper-container.has-error label[for="calendar"] { color:#d93025; }




  </style>
</head>
<body>
  @include('components.navbar')

  <div class="main-content">
    <div class="header">
      <h1>Information Technology and Information System Professors</h1>
    </div>

    <div class="search-container">
  <input type="text" id="searchInput" placeholder="Search..."
     autocomplete="off" spellcheck="false" inputmode="text"
     maxlength="50" aria-label="Search professors by name"
     pattern="[A-Za-z0-9 .,@_-]{0,50}">
    </div>

    <div class="profile-cards-grid">
      @foreach($professors as $prof)
  <div class="profile-card"
       onclick="openModal(this)"
       data-name="{{ $prof->Name }}"
       data-img="{{ $prof->profile_photo_url }}"
       data-prof-id="{{ $prof->Prof_ID }}"
       data-schedule="{{ $prof->Schedule ?: 'No schedule set' }}">
    <img src="{{ $prof->profile_photo_url }}" alt="Profile Picture">
          <div class="profile-name">{{ $prof->Name }}</div>
        </div>
      @endforeach
    </div>
    <div id="noResults" style="display:none; margin-top:12px; color:#b00020; font-weight:600; font-style: italic;">
      No PROFESSOR FOUND
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


  <div id="consultationModal" class="modal-overlay" style="display:none;">
    <form id="bookingForm" action="{{ route('consultation-book') }}" method="POST" class="modal-content">
      @csrf

      {{-- <input type="hidden" name="prof_id" value="{{ $professor->Prof_ID }}"> --}}
      <input type="hidden" name="prof_id" id="modalProfId" value="">


      <div class="modal-header">
        <div class="profile-section">
          <img id="modalProfilePic" class="profile-pic" src="" alt="Profile Picture">
          <div class="profile-info">
              <h2 id="modalProfileName">Professor Name</h2>
              <div id="modalSchedule" class="schedule-display">
                <!-- Schedule will be populated by JavaScript -->
              </div>
          </div>
        </div>

        <select name="subject_id" id="modalSubjectSelect">
          {{-- Options will be filled by JS --}}
        </select>
        <div id="csSubjectDropdown" class="cs-dd" style="display:none;">
          <button type="button" class="cs-dd-trigger" id="csDdTrigger">Select Subject</button>
          <ul class="cs-dd-list" id="csDdList"></ul>
        </div>
      </div>

      <div class="checkbox-section">
        @foreach($consultationTypes as $type)
          @if($type->Consult_Type === 'Others')
            <div class="others-checkbox-container">
              <label id="othersLabel">
                <input type="checkbox" name="types[]" value="{{ $type->Consult_type_ID }}" id="otherTypeCheckbox">
                {{ $type->Consult_Type }}
              </label>
              <input type="text" name="other_type_text" id="otherTypeText"
                placeholder="Please specify...">
            </div>
          @else
            <label>
              <input type="checkbox" name="types[]" value="{{ $type->Consult_type_ID }}">
              {{ $type->Consult_Type }}
            </label>
          @endif
        @endforeach
      </div>

      <div class="flex-layout">
        <div class="calendar-wrapper-container">
          <label for="calendar">Select Date:</label>
          <div class="availability-legend">
            <span><i class="legend-free"></i> Available</span>
            <span><i class="legend-low"></i> Almost Full</span>
            <span><i class="legend-full"></i> Full</span>
          </div>
          <input id="calendar" type="text" placeholder="Select Date" name="booking_date" required>
        </div>

        <div class="message-mode-container">
          <div class="mode-selection">
            <label><input type="radio" name="mode" value="online"> Online</label>
            <label><input type="radio" name="mode" value="onsite"> Onsite</label>
          </div>
          <div class="button-group">
        <button type="submit" class="submit-btn">Submit</button>
        <button type="button" class="cancel-btn" onclick="closeModal()">Cancel</button>
      </div>
        </div>
        
      </div>

    </form>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/pikaday/pikaday.js"></script>
  <script>
  document.addEventListener("DOMContentLoaded", function() {
      // Will be updated whenever modal opened
      let allowedWeekdays = new Set(); // numeric 1-5 (Mon-Fri) allowed for selected professor

      function disableDayFn(date){
        const day = date.getDay(); // 0 Sun .. 6 Sat
        // Always block weekends
  if(day === 0 || day === 6) return true;
  // If no schedule (allowedWeekdays empty) block ALL weekdays
  if(allowedWeekdays.size === 0) return true;
  // Otherwise block days not in allowed set
  if(!allowedWeekdays.has(day)) return true;
        return false;
      }

      function updateWeekdayHeaders(){
        const headers = document.querySelectorAll('.pika-table th');
        if(!headers.length) return;
        headers.forEach(th=>{
          th.classList.remove('allowed-day','disallowed-day','weekend-day','weekday-mon','weekday-sun');
          const titleEl = th.querySelector('abbr');
          if(!titleEl) return;
          const title = titleEl.getAttribute('title');
          const map = { 'Sunday':0,'Monday':1,'Tuesday':2,'Wednesday':3,'Thursday':4,'Friday':5,'Saturday':6 };
            const d = map[title];
            if(d===1) th.classList.add('weekday-mon');
            if(d===0) th.classList.add('weekday-sun');
            if(d===0 || d===6){ th.classList.add('weekend-day'); return; }
            if(allowedWeekdays.size===0){ th.classList.add('disallowed-day'); return; }
            if(allowedWeekdays.has(d)) th.classList.add('allowed-day'); else th.classList.add('disallowed-day');
        });
      }

      window.__updateAllowedWeekdays = function(scheduleText){
        allowedWeekdays.clear();
        if(!scheduleText) { picker.draw(); updateWeekdayHeaders(); return; }
        // Extract weekday names at line starts before colon
        const lines = scheduleText.split(/\n|<br\s*\/>/i).map(l=>l.trim()).filter(Boolean);
        const nameToNum = { Monday:1, Tuesday:2, Wednesday:3, Thursday:4, Friday:5 };
        lines.forEach(line=>{
          const m = line.match(/^(Monday|Tuesday|Wednesday|Thursday|Friday)\b/i);
          if(m){
            const key = m[1].charAt(0).toUpperCase()+m[1].slice(1).toLowerCase();
            if(nameToNum[key]) allowedWeekdays.add(nameToNum[key]);
          }
        });
        picker.draw();
        updateWeekdayHeaders();
      };

      var picker = new Pikaday({
        field: document.getElementById('calendar'),
        format: 'ddd, MMM DD YYYY',
        onSelect: function() {
          document.getElementById('calendar').value = this.toString('ddd, MMM DD YYYY');
        },
        showDaysInNextAndPreviousMonths: true,
        firstDay: 1,
        bound: false,
        minDate: new Date(),
        disableDayFn: disableDayFn
      });
      // Ensure header styling persists after navigating months (Pikaday redraws table)
      const _origDraw = picker.draw.bind(picker);
      picker.draw = function(){
        _origDraw();
        updateWeekdayHeaders();
      };
      picker.show();
      updateWeekdayHeaders();
      window.picker = picker;
  let __availabilityCache = {}; // { dateKey => {remaining, booked, mode} }
  window.__availabilityCache = __availabilityCache;
  window.__DEBUG_MODE_LOCK = window.__DEBUG_MODE_LOCK || false;
      let __dailyCapacity = 5; // default; overridden by API response
      function setLabelDisabled(input, disabled){ if(!input) return; const label = input.closest('label'); if(label) label.classList.toggle('disabled', !!disabled); }
      function setModeLockUI(mode){
        const online = document.querySelector('input[name="mode"][value="online"]');
        const onsite = document.querySelector('input[name="mode"][value="onsite"]');
        if(!online || !onsite) return;
        online.disabled = false; onsite.disabled = false; setLabelDisabled(online,false); setLabelDisabled(onsite,false);
        if(!mode){
          // No lock on this date: clear any previous selection
          online.checked = false; onsite.checked = false;
          return;
        }
        if(mode === 'online'){
          online.checked = true; onsite.checked = false; onsite.disabled = true; setLabelDisabled(onsite,true);
          online.dispatchEvent(new Event('change', { bubbles: true }));
          online.focus({ preventScroll: true });
        }
        if(mode === 'onsite'){
          onsite.checked = true; online.checked = false; online.disabled = true; setLabelDisabled(online,true);
          onsite.dispatchEvent(new Event('change', { bubbles: true }));
          onsite.focus({ preventScroll: true });
        }
      }

      function applyLockForSelectedDate(){
        try{
          if(!window.picker) return;
          const d = window.picker.getDate(); if(!d){ setModeLockUI(null); return; }
          const key = d.toLocaleDateString('en-US', { weekday:'short', month:'short', day:'2-digit', year:'numeric'}).replace(/,/g,'');
          const rec = (window.__availabilityCache||{})[key];
          const mode = rec && rec.mode ? rec.mode : null;
          if(window.__DEBUG_MODE_LOCK) console.log('[mode-lock][itis] applyLockForSelectedDate', { key, mode, rec });
          setModeLockUI(mode);
          if(!mode){ setTimeout(()=>{
            const r2 = (window.__availabilityCache||{})[key];
            const m2 = r2 && r2.mode ? r2.mode : null;
            if(window.__DEBUG_MODE_LOCK) console.log('[mode-lock][itis] retry applyLockForSelectedDate', { key, m2 });
            setModeLockUI(m2);
          }, 60); }
        }catch(_){ }
      }

  window.__applyAvailability = function(map){
        const cells = document.querySelectorAll('.pika-table td');
        cells.forEach(td=> td.classList.remove('slot-free','slot-low','slot-full'));
        cells.forEach(td=>{
          const btn = td.querySelector('.pika-button');
          if(!btn || td.classList.contains('is-disabled')) return;
          const year = btn.getAttribute('data-pika-year');
          if(!year) return;
          const month = parseInt(btn.getAttribute('data-pika-month'),10);
          const day = parseInt(btn.getAttribute('data-pika-day'),10);
          const d = new Date(year, month, day);
          const key = d.toLocaleDateString('en-US', { weekday:'short', month:'short', day:'2-digit', year:'numeric'}).replace(/,/g,'');
          const info = map[key];
          let remaining, booked;
          if(info){ remaining = info.remaining; booked = info.booked; }
          else { remaining = __dailyCapacity; booked = 0; }
          btn.dataset.remaining = remaining;
          btn.dataset.booked = booked;
          btn.dataset.capacity = __dailyCapacity;
          btn.dataset.mode = (info && info.mode) ? info.mode : '';
          const modeTxt = info && info.mode ? ` • Mode: ${info.mode}` : '';
          btn.setAttribute('title', (remaining <= 0 ? `Fully booked (0/${__dailyCapacity})` : `${remaining} slot${remaining===1?'':'s'} left (${booked}/${__dailyCapacity} booked)`) + modeTxt);
          if(remaining <= 0){
            td.classList.add('slot-full');
            btn.setAttribute('disabled','disabled');
            btn.setAttribute('aria-disabled','true');
            btn.style.pointerEvents='none';
          }
          else if(remaining <= 2) td.classList.add('slot-low');
          else td.classList.add('slot-free');
        });
      };
      function refreshAvailabilityColors(){ window.__applyAvailability(__availabilityCache); }
      const __origDraw2 = picker.draw.bind(picker);
      picker.draw = function(){
        __origDraw2();
        updateWeekdayHeaders();
        refreshAvailabilityColors();
        try{ applyLockForSelectedDate(); }catch(_){ }
        try{ attachSelectionObserver(); }catch(_){ }
      };
      function fetchAvailability(profId){
        if(!profId) return;
        const now = new Date();
        const start = now.toISOString().slice(0,10);
        const endDate = new Date(now.getFullYear(), now.getMonth()+2, 0); // cover two months
        const end = endDate.toISOString().slice(0,10);
        fetch(`/api/professor/availability?prof_id=${profId}&start=${start}&end=${end}`)
          .then(r=>r.json())
          .then(data=>{
            if(!data.success) return;
            if(typeof data.capacity === 'number') __dailyCapacity = data.capacity;
            __availabilityCache = {};
            data.dates.forEach(rec=>{ __availabilityCache[rec.date]=rec; });
            window.__availabilityCache = __availabilityCache;
            refreshAvailabilityColors();
            applyLockForSelectedDate();
          })
          .catch(()=>{});
      }
      window.__fetchAvailability = fetchAvailability;
      
      // Observe when a calendar cell becomes selected and apply the mode lock
      function attachSelectionObserver(){
        const table = document.querySelector('.pika-table');
        if(!table) return;
        if(table.__modeSelObserver){ return; }
        const obs = new MutationObserver(()=>{
          const td = table.querySelector('td.is-selected .pika-button');
          if(!td) return;
          let mode = td.dataset.mode || null;
          if(!mode){
            try{
              const key = new Date(td.getAttribute('data-pika-year'), parseInt(td.getAttribute('data-pika-month'),10), parseInt(td.getAttribute('data-pika-day'),10))
                .toLocaleDateString('en-US', { weekday:'short', month:'short', day:'2-digit', year:'numeric'}).replace(/,/g,'');
              const rec = (window.__availabilityCache||{})[key];
              if(rec && rec.mode) mode = rec.mode;
            }catch(_){ }
          }
          if(window.__DEBUG_MODE_LOCK) console.log('[mode-lock][itis][observer] apply', { mode });
          setModeLockUI(mode);
        });
        obs.observe(table, { attributes:true, subtree:true, attributeFilter:['class'] });
        table.__modeSelObserver = obs;
      }
      attachSelectionObserver();

  // Prevent selecting fully-booked cells and apply mode lock to radios on selection
      document.addEventListener('click', function(e){
        const btn = e.target.closest('.pika-button');
        if(!btn) return;
        let mode = btn.dataset.mode || null;
        if(!mode){
          // Fallback to cache in case the dataset isn't attached on this draw
          try {
            const key = new Date(btn.getAttribute('data-pika-year'), parseInt(btn.getAttribute('data-pika-month'),10), parseInt(btn.getAttribute('data-pika-day'),10))
              .toLocaleDateString('en-US', { weekday:'short', month:'short', day:'2-digit', year:'numeric'}).replace(/,/g,'');
            const rec = (window.__availabilityCache||{})[key];
            if(rec && rec.mode) mode = rec.mode;
          } catch(_) {}
        }
        if(window.__DEBUG_MODE_LOCK) console.log('[mode-lock][itis] click day', { mode, btn });
        setModeLockUI(mode);
        setTimeout(()=>{ setModeLockUI(mode); }, 0);
        setTimeout(()=>{ try{ applyLockForSelectedDate(); }catch(_){ } }, 0);
        if(btn.dataset && btn.dataset.remaining === '0'){
          e.preventDefault();
          e.stopPropagation();
          if(e.stopImmediatePropagation) e.stopImmediatePropagation();
          // Remove accidental selection highlight
          const sel = document.querySelector('.pika-table td.is-selected');
          if(sel && sel.classList.contains('slot-full')) sel.classList.remove('is-selected');
          return false;
        }
      }, true);
});

// Open modal and set professor info
function openModal(card) {
    document.getElementById("consultationModal").style.display = "flex";
    document.body.classList.add("modal-open");

    // Reset any previous calendar selection so user must pick a date each time
    (function resetCalendar(){
      const input = document.getElementById('calendar');
      if(input) { input.value=''; }
      document.querySelector('.calendar-wrapper-container')?.classList.remove('has-error');
      try { if(window.picker){ window.picker.setDate(null); } } catch(e) {}
      document.querySelectorAll('.pika-table td.is-selected').forEach(td=>td.classList.remove('is-selected'));
    })();

    const name = card.getAttribute("data-name");
    const img = card.getAttribute("data-img");
    const profId = card.getAttribute("data-prof-id");
    const schedule = card.getAttribute("data-schedule");
    
    // Find professor in JS (pass professors data as JSON to the page)
    const prof = window.professors.find(p => p.Prof_ID == profId);
    const select = document.getElementById("modalSubjectSelect");
    select.innerHTML = "";
    
    if (prof && prof.subjects && prof.subjects.length > 0) {
        prof.subjects.forEach(subj => {
            const opt = document.createElement("option");
            opt.value = subj.Subject_ID;
            opt.textContent = subj.Subject_Name;
            select.appendChild(opt);
        });
    } else {
        // If professor has no subjects assigned, show a default message
        const opt = document.createElement("option");
        opt.value = "";
        opt.textContent = "No subjects assigned to this professor";
        opt.disabled = true;
        select.appendChild(opt);
    }

  // Initialize / rebuild custom subject dropdown (mobile)
  initCustomSubjectDropdown();

    document.getElementById("modalProfilePic").src = img;
    document.getElementById("modalProfileName").textContent = name;
    document.getElementById("modalProfId").value = profId;
  if(window.__fetchAvailability){ setTimeout(()=>window.__fetchAvailability(profId),150); }
    
    // Populate schedule
    const scheduleDiv = document.getElementById("modalSchedule");
  if (schedule && schedule !== 'No schedule set') {
    const scheduleLines = schedule.split('\n');
    scheduleDiv.innerHTML = scheduleLines.map(line => `<p>${line}</p>`).join('');
    if(window.__updateAllowedWeekdays){ window.__updateAllowedWeekdays(schedule); }
  } else {
    scheduleDiv.innerHTML = '<p style="color: #888;">No schedule available</p>';
    if(window.__updateAllowedWeekdays){ window.__updateAllowedWeekdays(''); }
  }

  // Disable submit if no schedule
  const submitBtn = document.querySelector('#bookingForm .submit-btn');
  if(submitBtn){
    const hasSchedule = schedule && schedule !== 'No schedule set';
    submitBtn.disabled = !hasSchedule;
    submitBtn.classList.toggle('no-schedule', !hasSchedule);
    submitBtn.title = !hasSchedule ? 'Cannot book: professor has no schedule set.' : '';
  }
  // Reset mode radios on open to avoid stale locks
  const online = document.querySelector('input[name="mode"][value="online"]');
  const onsite = document.querySelector('input[name="mode"][value="onsite"]');
  if(online && onsite){
    online.checked=false; onsite.checked=false; online.disabled=false; onsite.disabled=false;
    const cont = document.querySelector('.mode-selection');
    cont && cont.querySelectorAll('label').forEach(l=>l.classList.remove('disabled'));
  }
}

// Custom dropdown (isolated; mirrors comsci implementation)
function initCustomSubjectDropdown(){
  const wrap=document.getElementById('csSubjectDropdown');
  const trigger=document.getElementById('csDdTrigger');
  const list=document.getElementById('csDdList');
  const native=document.getElementById('modalSubjectSelect');
  if(!wrap||!trigger||!list||!native) return; // safety
  list.innerHTML='';
  Array.from(native.options).forEach((o,i)=>{
    const li=document.createElement('li');
    li.textContent=o.text; if(i===native.selectedIndex) li.classList.add('active');
    li.addEventListener('click',()=>{ 
      native.selectedIndex=i; 
      updateCsTrigger(); 
      wrap.classList.remove('open'); 
      Array.from(list.children).forEach(c=>c.classList.remove('active')); 
      li.classList.add('active');
    });
    list.appendChild(li);
  });
  updateCsTrigger();
  trigger.onclick=()=>{ wrap.classList.toggle('open'); };
  document.addEventListener('click',e=>{ if(!wrap.contains(e.target)) wrap.classList.remove('open'); });
  function updateCsTrigger(){ const sel=native.options[native.selectedIndex]; trigger.textContent=(sel?sel.text:'Select Subject'); }
}

// Close modal function
function closeModal() {
    document.getElementById("consultationModal").style.display = "none";
    document.body.classList.remove("modal-open");
}

// Optional: Close modal when clicking outside modal-content
window.onclick = function(event) {
    const modal = document.getElementById("consultationModal");
    if (event.target === modal) {
        closeModal();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const otherCheckbox = document.getElementById('otherTypeCheckbox');
    const otherText = document.getElementById('otherTypeText');
    if (otherCheckbox && otherText) {
        otherCheckbox.addEventListener('change', function() {
            otherText.style.display = this.checked ? 'inline-block' : 'none';
            if (this.checked) {
                otherText.setAttribute('required','required');
            } else {
                otherText.removeAttribute('required');
                otherText.value = '';
            }
        });
    }
});

// Client-side validation to keep modal open (prevent submit if invalid)
const bookingForm = document.getElementById('bookingForm');
if(bookingForm){
  function validateBooking(){
    const profId = document.getElementById('modalProfId').value.trim();
    if(!profId) return 'Professor not selected.';
    const subjectSel = document.getElementById('modalSubjectSelect');
    if(!subjectSel || !subjectSel.value){ return 'Please select a subject.'; }
    const typesChecked = bookingForm.querySelectorAll('input[name="types[]"]:checked').length;
    if(typesChecked === 0) return 'Please select at least one consultation type.';
  const modeInputs = bookingForm.querySelectorAll('input[name="mode"]');
  const selected = Array.from(modeInputs).find(i=>i.checked);
  if(!selected) return 'Please select consultation mode (Online or Onsite).';
    const dateInput = document.getElementById('calendar');
  const hasSelectedCell = document.querySelector('.pika-table td.is-selected');
  if(!dateInput.value.trim() || !hasSelectedCell){
      document.querySelector('.calendar-wrapper-container')?.classList.add('has-error');
      return 'Please select a booking date.';
    } else {
      document.querySelector('.calendar-wrapper-container')?.classList.remove('has-error');
    }
    // Check availability cache for fully booked (defensive race)
    if(window.__availabilityCache){
      const key = dateInput.value.replace(/,/g,'');
      const rec = window.__availabilityCache[key];
      if(rec && rec.remaining <= 0) return 'Selected date is already fully booked.';
    }
    const otherCb = document.getElementById('otherTypeCheckbox');
    const otherTxt = document.getElementById('otherTypeText');
    if(otherCb && otherCb.checked && !otherTxt.value.trim()) return 'Please specify the consultation type in the Others field.';
    if(window.__availabilityCache){
      const key = dateInput.value.replace(/,/g,'');
      const rec = window.__availabilityCache[key];
      if(rec && rec.mode && selected && selected.value !== rec.mode){
        return `This date is locked to ${rec.mode}.`;
      }
    }
    return null;
  }

  bookingForm.addEventListener('submit', async function(e){
    e.preventDefault();
    const err = validateBooking();
    if(err){ showNotification(err, true); return; }
    const submitBtn = bookingForm.querySelector('.submit-btn');
    if(submitBtn){ submitBtn.disabled = true; submitBtn.dataset.originalText = submitBtn.textContent; submitBtn.textContent = 'Submitting...'; }
    try {
      const fd = new FormData(bookingForm);
      const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
      const res = await fetch(bookingForm.action, { method:'POST', headers:{ 'X-CSRF-TOKEN': token, 'Accept':'application/json' }, body: fd });
      const contentType = res.headers.get('content-type')||'';
      if(res.status === 422){
        let msg = 'Validation error.';
        if(contentType.includes('application/json')){
          const data = await res.json();
            if(data.errors){
              const first = Object.values(data.errors)[0];
              if(first && first[0]) msg = first[0];
            } else if(data.message){ msg = data.message; }
        }
        showNotification(msg, true);
        return;
  }
      if(!res.ok){ showNotification('Server error. Please try again.', true); return; }
      if(contentType.includes('application/json')){
        const data = await res.json();
        if(data.success){
          showNotification(data.message || 'Consultation booked successfully.', false);
          closeModal();
          bookingForm.reset();
        } else {
          showNotification(data.message || 'Unexpected response.', true);
        }
      } else {
        // Fallback: treat non-JSON (redirect HTML) as success
        showNotification('Consultation booked successfully.', false);
        closeModal();
        bookingForm.reset();
      }
    } catch(ex){
      showNotification('Network error. Please try again.', true);
    } finally {
      if(submitBtn){ submitBtn.disabled = false; submitBtn.textContent = submitBtn.dataset.originalText || 'Submit'; }
    }
  });
}

window.professors = @json($professors);

// === Secure client-side search (defensive) ===
// DOM-only filtering; sanitize to mitigate injection-style payload attempts.
(function secureSearch(){
  const input = document.getElementById('searchInput');
  if(!input) return;
  const MAX_LEN = 50;
  function sanitize(raw){
    if(!raw) return '';
    return raw
      .replace(/\/*.*?\*\//g,'')   // strip block comments
      .replace(/--+/g,' ')            // SQL line comment openers
      .replace(/[;`'"<>]/g,' ')      // dangerous punctuation
      .slice(0,MAX_LEN);
  }
  function filter(){
    const safe = sanitize(input.value);
    const term = safe.toLowerCase();
    const norm = term.replace(/\s+/g,' ').trim();
    const cards = document.querySelectorAll('.profile-card');
    let visible = 0;
    cards.forEach(c=>{
      const name = (c.dataset.name||c.textContent||'').toLowerCase();
      const nameNorm = name.replace(/\s+/g,' ').trim();
      const show = norm === '' || nameNorm.includes(norm);
      c.style.display = show ? '' : 'none';
      if(show) visible++;
    });
    const msg = document.getElementById('noResults');
    if(msg){ msg.style.display = (norm !== '' && visible === 0) ? 'block' : 'none'; }
  }
  input.addEventListener('input', filter);
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

function sanitize(raw){
  if(!raw) return '';
  return raw
    .replace(/\/*.*?\*\//g,'')
    .replace(/--+/g,' ')
    .replace(/[;`'"<>]/g,' ')
    .replace(/\s+/g,' ')
    .trim()
    .slice(0,250);
}

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

  <!-- Notification Div -->
  <div id="notification" class="notification">
    <span id="notification-message"></span>
    <button onclick="hideNotification()" class="close-btn">&times;</button>
  </div>

  <script>
    function showNotification(message, isError = false) {
      const notif = document.getElementById('notification');
      notif.classList.toggle('error', isError);
      document.getElementById('notification-message').textContent = message;
      notif.style.display = 'flex';
      setTimeout(hideNotification, 4000);
    }
    
    function hideNotification() {
      document.getElementById('notification').style.display = 'none';
    }
  </script>

  <!-- Handle Laravel session messages -->
  @if (session('success'))
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        showNotification(@json(session('success')), false);
      });
    </script>
  @endif

  @if (session('error'))
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        showNotification(@json(session('error')), true);
      });
    </script>
  @endif

  @if ($errors->any())
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        showNotification(@json($errors->first()), true);
      });
    </script>
  @endif
</body>
<script src="https://js.pusher.com/7.0/pusher.min.js"></script>
<script>
  (function(){
    const pusher = new Pusher('{{ config('broadcasting.connections.pusher.key') }}', {cluster: '{{ config('broadcasting.connections.pusher.options.cluster') }}'});
    const channel = pusher.subscribe('professors.dept.1'); // Dept_ID 1 for IT/IS

    function buildCard(data){
      const grid = document.querySelector('.profile-cards-grid');
      if(!grid) return;
      if(grid.querySelector('[data-prof-id="'+data.Prof_ID+'"]')) return;
      const div = document.createElement('div');
      div.className='profile-card';
      div.setAttribute('onclick','openModal(this)');
      div.dataset.name = data.Name;
      const imgPath = data.profile_picture ? ('{{ asset('storage') }}/'+data.profile_picture) : '{{ asset('images/dprof.jpg') }}';
      div.dataset.img = imgPath;
      div.setAttribute('data-prof-id', data.Prof_ID);
      div.dataset.schedule = data.Schedule || 'No schedule set';
  /* Width now controlled by responsive CSS grid */
      div.innerHTML = `<img src="${imgPath}" alt="Profile Picture"><div class="profile-name">${data.Name}</div>`;
      grid.prepend(div);
    }

    channel.bind('ProfessorAdded', function(data){ buildCard(data); });
    channel.bind('ProfessorUpdated', function(data){
      const card = document.querySelector('[data-prof-id="'+data.Prof_ID+'"]');
      if(card){
        card.dataset.name = data.Name;
        card.dataset.schedule = data.Schedule || 'No schedule set';
        const imgPath = data.profile_picture ? ('{{ asset('storage') }}/'+data.profile_picture) : '{{ asset('images/dprof.jpg') }}';
        card.dataset.img = imgPath;
        card.querySelector('.profile-name').textContent = data.Name;
        const imgEl = card.querySelector('img'); if(imgEl) imgEl.src = imgPath;
      } else { buildCard(data); }
    });
    channel.bind('ProfessorDeleted', function(data){
      const card = document.querySelector('[data-prof-id="'+data.Prof_ID+'"]');
      if(card) card.remove();
    });
  })();
</script>
</html>
