<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Computer Science Department</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/itis.css') }}">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pikaday/css/pikaday.css">

  <style>

  #calendar {
    visibility: hidden;  /* Hide the input field */
   }

  .pika-prev, .pika-next {
    background-color: #01703c;
    border-radius: 50%;              /* Circular shape */
    color: #12372a;                    /* Green arrow color */
    border: 2px solid #12372a;         /* Green border around the circle */
    font-size: 18px;                 /* Adjust font size for visibility */
    padding: 10px;                   /* Padding to make the circle big enough */
    width: 35px !important;
    opacity: 100%;
  }

  .pika-table th:has( [title="Saturday"] ), 
  .pika-table th:has( [title="Tuesday"] ), 
  .pika-table th:has( [title="Wednesday"] ), 
  .pika-table th:has( [title="Thursday"] ), 
  .pika-table th:has( [title="Friday"] ) {
    background-color: #12372a;
    color: #fff;
    border-radius: 4px;
    padding: 5px;
  }

  .pika-table th:has( [title="Monday"] ), 
  .pika-table th:has( [title="Sunday"] ) {
    background-color: #01703c;
    color: #fff;
    border-radius: 4px;    
    padding: 10px;
  }


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

  .pika-button{
    background-color: #888888;
    border-radius: 4px;
    color: #ffffff;
    padding: 10px;
    height: 40px;
    margin: 5px 0;
  }

  .pika-button:hover,
  .pika-row.pick-whole-week:hover .pika-button {
    color: #fff;
    background: #01703c;
    box-shadow: none;
    border-radius: 3px;
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



  </style>
</head>
<body>
  @include('components.navbar')

  <div class="main-content">
    <div class="header">
      <h1>Information Technology and Information System</h1>
    </div>

    <div class="search-container">
      <input type="text" id="searchInput" placeholder="Search...">
    </div>

    <div class="profile-cards-grid">
      @foreach($professors as $prof)
        <div class="profile-card"
             onclick="openModal(this)"
             data-name="{{ $prof->Name }}"
             data-img="{{ $prof->profile_picture ? asset('storage/' . $prof->profile_picture) : asset('images/dprof.jpg') }}"
             data-prof-id="{{ $prof->Prof_ID }}"
             style="width: 300px;">
          <img src="{{ $prof->profile_picture ? asset('storage/' . $prof->profile_picture) : asset('images/dprof.jpg') }}" alt="Profile Picture">
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
              <h2 id="modalProfileName">{{ $professor->Name ?? 'Professor Name' }}</h2>
              <p>Tuesday: 10:00–11:00</p>
              <p>Wednesday: 17:00–18:00</p>
              <p>Thursday: 17:00–18:00</p>
          </div>
        </div>

        <select name="subject_id" id="modalSubjectSelect">
          {{-- Options will be filled by JS --}}
        </select>
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

  <script src="{{ asset('js/itis.js') }}"></script>
  <script src="https://cdn.jsdelivr.net/npm/pikaday/pikaday.js"></script>
  <script>
  document.addEventListener("DOMContentLoaded", function() {
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
      });
      picker.show();
});

// Open modal and set professor info
function openModal(card) {
    document.getElementById("consultationModal").style.display = "flex";
    document.body.classList.add("modal-open");

    const name = card.getAttribute("data-name");
    const img = card.getAttribute("data-img");
    const profId = card.getAttribute("data-prof-id");
    // Find professor in JS (pass professors data as JSON to the page)
    const prof = window.professors.find(p => p.Prof_ID == profId);
    const select = document.getElementById("modalSubjectSelect");
    select.innerHTML = "";
    if (prof && prof.subjects.length) {
        prof.subjects.forEach(subj => {
            const opt = document.createElement("option");
            opt.value = subj.Subject_ID;
            opt.textContent = subj.Subject_Name;
            select.appendChild(opt);
        });
    }

    document.getElementById("modalProfilePic").src = img;
    document.getElementById("modalProfileName").textContent = name;
    document.getElementById("modalProfId").value = profId;
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
            if (!this.checked) otherText.value = '';
        });
    }
});

window.professors = @json($professors);
  </script>
</body>
</html>
