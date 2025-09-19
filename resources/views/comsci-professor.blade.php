<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Computer Science Department</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/comsci.css') }}">
</head>
<body>
  @include('components.navbarprof')

  <main class="main-content">
    <header class="header">
      <h1>Computer Science Department</h1>
    </header>

    <section class="search-container">
      <label for="searchInput" class="visually-hidden">Search colleagues</label>
      <input type="text" id="searchInput" placeholder="Search..." maxlength="50"
             autocomplete="off" spellcheck="false" aria-label="Search colleagues">
    </section>

    <section class="profile-cards-grid">
      @if($colleagues->count() > 0)
        @foreach($colleagues as $colleague)
          <div class="profile-card" data-name="{{ $colleague->Name }}">
            <img src="{{ $colleague->profile_picture ? asset('storage/' . $colleague->profile_picture) : asset('images/dprof.jpg') }}" alt="Profile Picture of {{ $colleague->Name }}">
            <div class="profile-name">{{ $colleague->Name }}</div>
          </div>
        @endforeach
      @else
        <div class="no-colleagues">
          <p>No other colleagues in this department.</p>
        </div>
      @endif
    </section>
  </main>

  <button class="chat-button" onclick="toggleChat()">
    <i class='bx bxs-message-rounded-dots'></i>
    Click to chat with me!
  </button>

  <aside class="chat-overlay" id="chatOverlay" aria-hidden="true">
    <div class="chat-header">
      <span>AI Chat Assistant</span>
      <button class="close-btn" onclick="toggleChat()" aria-label="Close chat">×</button>
    </div>
    <div class="chat-body" id="chatBody">
      <div class="message bot">Hi! How can I help you today?</div>
      <div id="chatBox"></div>
    </div>
    <form id="chatForm">
      <label for="message" class="visually-hidden">Type your message</label>
      <input type="text" id="message" placeholder="Type your message" required maxlength="250" autocomplete="off" spellcheck="false">
      <button type="submit">Send</button>
    </form>
  </aside>

  <script src="{{ asset('js/comsci.js') }}"></script>
  <script>
    function sanitize(input = '') {
      return input
        .replace(/\/\*.*?\*\//g, '')
        .replace(/--+/g, ' ')
        .replace(/[;`'"<>]/g, ' ')
        .replace(/\s+/g, ' ')
        .trim()
        .slice(0, 50);
    }

    function filterColleagues() {
      const input = document.getElementById('searchInput');
      const cleaned = sanitize(input.value);
      if (input.value !== cleaned) input.value = cleaned;

      const filter = cleaned.toLowerCase();
      document.querySelectorAll('.profile-card').forEach(card => {
        const name = card.dataset.name.toLowerCase();
        card.style.display = name.includes(filter) ? 'block' : 'none';
      });
    }

    function handleEnterKey(event, callback) {
      if (event.key === 'Enter') {
        event.preventDefault();
        callback();
      }
    }

    function setupListeners() {
      const searchInput = document.getElementById('searchInput');
      const messageInput = document.getElementById('message');
      const chatForm = document.getElementById('chatForm');

      if (searchInput) {
        searchInput.addEventListener('input', filterColleagues);
        searchInput.addEventListener('keydown', e => handleEnterKey(e, filterColleagues));
      }

      if (messageInput && chatForm) {
        messageInput.addEventListener('keydown', e => handleEnterKey(e, () => {
          const cleaned = sanitize(messageInput.value);
          if (cleaned) {
            messageInput.value = cleaned;
            chatForm.requestSubmit();
          }
        }));

        chatForm.addEventListener('submit', e => {
          const cleaned = sanitize(messageInput.value);
          if (!cleaned) {
            e.preventDefault();
            messageInput.value = '';
          } else {
            messageInput.value = cleaned;
          }
        });
      }
    }

    document.addEventListener('DOMContentLoaded', setupListeners);
  </script>
</body>
</html>
