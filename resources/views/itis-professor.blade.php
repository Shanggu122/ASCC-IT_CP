<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Information Technology and Information Systems Department</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/itis.css') }}">
</head>
<body>
  @include('components.navbarprof')

  <div class="main-content">
    <div class="header">
      <h1>Information Technology and Information Systems Department</h1>
    </div>

    <div class="search-container">
      <input type="text" id="searchInput" placeholder="Search..." onkeyup="filterColleagues()"
             autocomplete="off" spellcheck="false" maxlength="50"
             pattern="[A-Za-z0-9 .,@_-]{0,50}" aria-label="Search colleagues">
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
      <input type="text" id="message" placeholder="Type your message" required>
      <button type="submit">Send</button>
    </form>
  </div>

  <script src="{{ asset('js/itis.js') }}"></script>
  <script>
    function sanitize(raw){
      if(!raw) return '';
      return raw
        .replace(/\/*.*?\*\//g,'')
        .replace(/--+/g,' ')
        .replace(/[;`'"<>]/g,' ')
        .replace(/\s+/g,' ')
        .trim()
        .slice(0,50);
    }

    function filterColleagues() {
      const searchInput = document.getElementById('searchInput');
      const cleaned = sanitize(searchInput.value);
      if(searchInput.value !== cleaned) searchInput.value = cleaned;
      const filter = cleaned.toLowerCase();
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

    // Add Enter key functionality for chat form
    document.addEventListener('DOMContentLoaded', function() {
  const messageInput = document.getElementById('message');
      if (messageInput) {
        // Remove any existing event listeners first
        messageInput.removeEventListener('keydown', handleEnterKey);
        
        // Add our Enter key handler
        messageInput.addEventListener('keydown', handleEnterKey);
      }
      
      // Add Enter key functionality for search input as well
      const searchInput = document.getElementById('searchInput');
      if (searchInput) {
        searchInput.addEventListener('keydown', function(event) {
          if (event.key === 'Enter') {
            event.preventDefault();
            filterColleagues();
          }
        });
        searchInput.addEventListener('input', filterColleagues);
      }
    });
    
    // Define the Enter key handler function
    function handleEnterKey(event) {
      if (event.key === 'Enter') {
        event.preventDefault();
        const chatForm = document.getElementById('chatForm');
        if (chatForm) {
          const msg = document.getElementById('message');
          if(msg){
            const cleaned = sanitize(msg.value);
            if(cleaned){ msg.value = cleaned; chatForm.requestSubmit(); }
          }
        }
      }
    }

    // Chat form sanitization on submit
    document.addEventListener('DOMContentLoaded', function(){
      const form = document.getElementById('chatForm');
      const msg = document.getElementById('message');
      if(form && msg){
        msg.setAttribute('maxlength','250');
        msg.setAttribute('autocomplete','off');
        msg.setAttribute('spellcheck','false');
        form.addEventListener('submit', function(e){
          const cleaned = sanitize(msg.value);
          if(!cleaned){ e.preventDefault(); msg.value=''; return; }
          msg.value = cleaned;
        });
      }
    });
  </script>
</body>
</html> 