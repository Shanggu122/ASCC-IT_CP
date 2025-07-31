<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>IT & IS Department</title>
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
      <input type="text" placeholder="Search...">
    </div>

    <!-- Clickable Profile -->
    <div class="profile-card" onclick="openModal()">
      <img src="{{ asset('images/anette.jpg') }}" alt="Profile Picture">
      <div class="profile-name">Prof. Anette Daligcon</div>
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
</body>
</html> 