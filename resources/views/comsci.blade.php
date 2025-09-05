<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Computer Science Department</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/comsci.css') }}">
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

  /* Unified arrow styling to match IT&IS */
  .pika-prev, .pika-next {
    background-color: #0d2b20; /* darker fill */
    border-radius: 50%;
    color: #ffffff;
    border: 2px solid #071a13; /* darker edge */
    font-size: 18px;
    padding: 10px;
    width: 38px !important;
    height: 38px;
    display: flex; align-items:center; justify-content:center;
    opacity: 100%;
    text-indent: -9999px; /* hide default */
    position: relative;
    background-image:none !important;
  }
  .pika-prev:after, .pika-next:after { content:''; position:absolute; top:46%; left:50%; transform:translate(-50%,-50%); font-size:24px; font-weight:700; color:#fff; text-indent:0; }
  .pika-prev:after { content:'\2039'; }
  .pika-next:after { content:'\203A'; }

  /* Weekday header styling (dynamic classes applied via JS) */
  .pika-table th { 
    background-color:#12372a; /* default Tue-Sat */
    color:#fff;
    border-radius:4px;
    padding:5px;
    transition:background-color .25s, opacity .25s;
  }
  .pika-table th.weekday-mon, .pika-table th.weekday-sun { background-color:#01703c; padding:10px; }
  .pika-table th.allowed-day { }
  .pika-table th.disallowed-day { }
  .pika-table th.weekend-day { }

  /* Disabled (blocked) days clearer */
  .is-disabled .pika-button, .pika-button.is-disabled { background:#e5f0ed !important; color:#94a5a0 !important; border:1px solid #d0dbd8; opacity:1 !important; cursor:not-allowed; }
  .is-disabled .pika-button:hover { background:#f1f4f6 !important; color:#b3bcc3 !important; }


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

  /* Available day buttons: unified green theme */
  .pika-button{
    background-color:#01703c; /* was grey */
    border-radius:4px;
    color:#ffffff;
    padding:10px;
    height:40px;
    margin:5px 0;
    transition:background .18s, transform .18s;
  }

  .pika-button:hover,
  .pika-row.pick-whole-week:hover .pika-button {
    color:#fff;
    background:#0d2b20; /* darker hover */
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

  .calendar-wrapper-container {
    display: block !important;
    visibility: visible !important;
  }
  
  .pika-single {
    display: block !important;
    visibility: visible !important;
  }



  </style>
</head>
<body>
  @include('components.navbar')

  <div class="main-content">
    <div class="header">
      <h1>Computer Science</h1>
    </div>

    <div class="search-container">
      <input type="text" id="searchInput" placeholder="Search...">
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
        <!-- Custom dropdown (mobile only) - keeps native select for form submit -->
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
      let allowedWeekdays = new Set(); // numeric 1-5 Mon-Fri allowed for selected professor

      function disableDayFn(date){
        const day = date.getDay(); // 0 Sun..6 Sat
        if(day===0 || day===6) return true; // block weekends always
        if(allowedWeekdays.size>0 && !allowedWeekdays.has(day)) return true;
        return false;
      }

      function updateWeekdayHeaders(){
        const headers=document.querySelectorAll('.pika-table th');
        if(!headers.length) return;
        headers.forEach(th=>{
          th.classList.remove('allowed-day','disallowed-day','weekend-day','weekday-mon','weekday-sun');
          const ab=th.querySelector('abbr'); if(!ab) return;
          const title=ab.getAttribute('title');
          const map={'Sunday':0,'Monday':1,'Tuesday':2,'Wednesday':3,'Thursday':4,'Friday':5,'Saturday':6};
          const d=map[title];
          if(d===1) th.classList.add('weekday-mon');
          if(d===0) th.classList.add('weekday-sun');
          if(d===0 || d===6){ th.classList.add('weekend-day'); return; }
          if(allowedWeekdays.size===0){ th.classList.add('disallowed-day'); return; }
          if(allowedWeekdays.has(d)) th.classList.add('allowed-day'); else th.classList.add('disallowed-day');
        });
      }

      window.__updateAllowedWeekdays = function(scheduleText){
        allowedWeekdays.clear();
        if(!scheduleText){ picker.draw(); updateWeekdayHeaders(); return; }
        const lines = scheduleText.split(/\n|<br\s*\/>/i).map(l=>l.trim()).filter(Boolean);
        const nameToNum = { Monday:1, Tuesday:2, Wednesday:3, Thursday:4, Friday:5 };
        lines.forEach(line=>{
          const m=line.match(/^(Monday|Tuesday|Wednesday|Thursday|Friday)\b/i);
          if(m){
            const key=m[1].charAt(0).toUpperCase()+m[1].slice(1).toLowerCase();
            if(nameToNum[key]) allowedWeekdays.add(nameToNum[key]);
          }
        });
        picker.draw();
        updateWeekdayHeaders();
      };

      var picker = new Pikaday({
        field: document.getElementById('calendar'),
        format: 'ddd, MMM DD YYYY',
        onSelect: function(){ document.getElementById('calendar').value = this.toString('ddd, MMM DD YYYY'); },
        showDaysInNextAndPreviousMonths: true,
        firstDay: 1,
        bound: false,
        minDate: new Date(),
        disableDayFn: disableDayFn
      });
      const _origDraw = picker.draw.bind(picker);
      picker.draw = function(){ _origDraw(); updateWeekdayHeaders(); };
      picker.show();
      updateWeekdayHeaders();
      window.picker = picker;
  });

// Open modal and set professor info
function openModal(card) {
    document.getElementById("consultationModal").style.display = "flex";
    document.body.classList.add("modal-open");

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

  initCustomSubjectDropdown();

    document.getElementById("modalProfilePic").src = img;
    document.getElementById("modalProfileName").textContent = name;
    document.getElementById("modalProfId").value = profId;
    
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
}

// Custom dropdown (isolated)
function initCustomSubjectDropdown(){
  const wrap=document.getElementById('csSubjectDropdown');
  const trigger=document.getElementById('csDdTrigger');
  const list=document.getElementById('csDdList');
  const native=document.getElementById('modalSubjectSelect');
  if(!wrap||!trigger||!list||!native) return;
  list.innerHTML='';
  Array.from(native.options).forEach((o,i)=>{
    const li=document.createElement('li');
    li.textContent=o.text; if(i===native.selectedIndex) li.classList.add('active');
    li.addEventListener('click',()=>{ native.selectedIndex=i; updateCsTrigger(); wrap.classList.remove('open'); Array.from(list.children).forEach(c=>c.classList.remove('active')); li.classList.add('active'); });
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

// Enforce Others text presence before submit
const bookingForm = document.getElementById('bookingForm');
if(bookingForm){
  bookingForm.addEventListener('submit', function(e){
    const otherCb = document.getElementById('otherTypeCheckbox');
    const otherTxt = document.getElementById('otherTypeText');
    if(otherCb && otherCb.checked){
      if(!otherTxt.value.trim()){
        e.preventDefault();
        otherTxt.focus();
        showNotification('Please specify the consultation type in the Others field.', true);
      }
    }
  });
}

window.professors = @json($professors);

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
    const channel = pusher.subscribe('professors.dept.2'); // Dept_ID 2 for ComSci

    function buildCard(data){
      const grid = document.querySelector('.profile-cards-grid');
      if(!grid) return;
      // Avoid duplicates
      if(grid.querySelector('[data-prof-id="'+data.Prof_ID+'"]')) return;
      const div = document.createElement('div');
      div.className='profile-card';
      div.setAttribute('onclick','openModal(this)');
      div.dataset.name = data.Name;
      const imgPath = data.profile_picture ? ('{{ asset('storage') }}/'+data.profile_picture) : '{{ asset('images/dprof.jpg') }}';
      div.dataset.img = imgPath;
      div.dataset.profId = data.Prof_ID;
      div.dataset.profId = data.Prof_ID;
      div.setAttribute('data-prof-id', data.Prof_ID);
      div.dataset.schedule = data.Schedule || 'No schedule set';
  /* Width managed by responsive CSS grid */
      div.innerHTML = `<img src="${imgPath}" alt="Profile Picture"><div class="profile-name">${data.Name}</div>`;
      grid.prepend(div); // put newest first
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
