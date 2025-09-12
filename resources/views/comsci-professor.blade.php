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
</head>
<body>
  @include('components.navbarprof')
  
  <div class="main-content">
    <div class="header">
      <h1>Computer Science Department</h1>
    </div>
    
    <div class="search-container">
      <input type="text" id="searchInput" placeholder="Search..." onkeyup="filterColleagues()">
    </div>
    
    <div class="profile-cards-grid">
      @if($colleagues->count() > 0)
        @foreach($colleagues as $colleague)
          <div class="profile-card" data-name="{{ $colleague->Name }}">
            <img src="{{ $colleague->profile_picture ? asset('storage/' . $colleague->profile_picture) : asset('images/dprof.jpg') }}" alt="Profile Picture">
            <div class="profile-name">{{ $colleague->Name }}</div>
          </div>
        @endforeach
      @else
        <div class="no-colleagues">
          <p>No other colleagues in this department.</p>
        </div>
      @endif
    </div>
  </div>
  
  <!-- Chat button now focuses the search bar instead -->
  <button class="chat-button" onclick="toggleChat()">
    <i class='bx bxs-message-rounded-dots'></i>
    Click to chat with me!
  </button>
  
  <!-- Chat overlay kept here but unused -->
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
  
  <script src="{{ asset('js/comsci.js') }}"></script>
  <script>
    // --- Helper: simple unit test logger ---
    function assert(desc, condition) {
      if (condition) {
        console.log("✅ PASS:", desc);
      } else {
        console.error("❌ FAIL:", desc);
      }
    }

    // Replaced toggleChat: now focuses search bar
    function toggleChat() {
      const searchInput = document.getElementById('searchInput');
      if (searchInput) {
        searchInput.scrollIntoView({ behavior: "smooth", block: "center" });
        searchInput.focus();
        console.log("Chat button now redirects to search bar!");

        // --- UNIT TESTING: Check if search bar is focused ---
        setTimeout(() => {
          assert("Search bar should be focused after clicking chat button", document.activeElement === searchInput);
        }, 200);
      } else {
        console.error("Search bar not found!");
      }
    }

    function filterColleagues() {
      const searchInput = document.getElementById('searchInput');
      const filter = searchInput.value.toLowerCase();
      const cards = document.querySelectorAll('.profile-card');
      
      cards.forEach(card => {
        const name = card.getAttribute('data-name').toLowerCase();
        if (name.includes(filter)) {
          card.style.display = 'block';
        } else {
          card.style.display = 'none';
        }
      });
    }

    // Keep enter key functionality for search input
    document.addEventListener('DOMContentLoaded', function() {
      const searchInput = document.getElementById('searchInput');
      if (searchInput) {
        searchInput.addEventListener('keydown', function(event) {
          if (event.key === 'Enter') {
            event.preventDefault();
            filterColleagues();
          }
        });
      }
    });
  </script>
</body>
</html>
