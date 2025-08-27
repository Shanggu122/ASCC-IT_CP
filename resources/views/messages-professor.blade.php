<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Messages - Professor</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/messages-professor.css') }}">
  <style>
   .message.sent {
      text-align: right;
      background-color: #c7e5dd; /* Softer green, matches #e5f0ed theme */
      color: #12372a;   
      margin-left: auto;
      display: block;
      max-width: 70%;
    }

    .message.received {
      text-align: left;
      background-color: #f1f1f1;
      margin-right: auto;
      display: block;
      max-width: 70%;
    }
  </style>
</head>
<body>
  @include('components.navbarprof')

  <div class="main-content">
  <!-- Messaging Area -->
  <div class="messages-wrapper">
    <!-- Inbox -->
    <div class="inbox">
      <h2>Inbox</h2>
      @foreach($students as $student)
        @php
          $pic = null;
          if (is_object($student)) {
            if (property_exists($student,'profile_picture')) { $pic = $student->profile_picture; }
          }
          $picUrl = $pic ? asset('storage/'.$pic) : asset('images/dprof.jpg');
          $lastMessage = $student->last_message ?? 'No messages yet';
          $youPrefix = isset($student->last_sender) && $student->last_sender === 'professor' ? 'You: ' : '';
          $displayMessage = $youPrefix . $lastMessage;
          $relTime = $student->last_message_time ? \Carbon\Carbon::parse($student->last_message_time)->timezone('Asia/Manila')->diffForHumans(['short'=>true]) : '';
        @endphp
        <div class="inbox-item" onclick="loadChat('{{ $student->name }}', {{ $student->booking_id }})">
          <img class="inbox-avatar" src="{{ $picUrl }}" alt="{{ $student->name }}">
          <div class="inbox-meta">
            <div class="name">{{ $student->name }}</div>
            <div class="snippet-line">
              <span class="snippet" title="{{ $displayMessage }}">{!! isset($student->last_sender) && $student->last_sender==='professor' ? '<strong>You:</strong> ' : '' !!}{{ \Illuminate\Support\Str::limit($lastMessage, 36) }}</span>
              @if($relTime)<span class="rel-time">{{ $relTime }}</span>@endif
            </div>
          </div>
        </div>
      @endforeach
    </div>

    <!-- Chat Panel -->
    <div class="chat-panel" id="chat-panel">
      <div class="chat-header">
        <button class="back-btn" id="back-btn" style="display:none;"><i class='bx bx-arrow-back'></i></button>
        <span id="chat-person">Select a student</span>
        <button class="video-btn" onclick="startVideoCall()">Video Call</button>
      </div>
      <div class="chat-body" id="chat-body">
        @if(count($students) === 0)
          <div class="message">No students found.</div>
        @endif
        <!-- Messages will be dynamically loaded here -->
      </div>
      <div class="chat-input" id="chat-input">
        <div id="file-preview-container" class="file-preview-container"></div>
        <label for="file-input" class="attach-btn" title="Upload file">
            <i class='bx bx-paperclip'></i>
        </label>
        <input type="file" id="file-input" multiple style="display:none;" accept="image/*,.pdf,.doc,.docx" />
        <textarea id="message-input" placeholder="Type a message..." rows="1"></textarea>
        <button id="send-btn" onclick="sendMessage()">Send</button>
      </div>
    </div>
  </div>
</div>
  <script src="https://js.pusher.com/7.0/pusher.min.js"></script>
  <script>
    let currentChatPerson = '';
    let bookingId = null;

    // Enable pusher logging - don't include this in production
    Pusher.logToConsole = true;
    
    var pusher = new Pusher('00e7e382ce019a1fa987', {
      cluster: 'ap1'
    });

    var channel = pusher.subscribe('chat');
    channel.bind('MessageSent', function(data) {
      if (data.bookingId === bookingId) {
        const chatBody = document.getElementById('chat-body');
        const msgDiv = document.createElement('div');
        msgDiv.className = `message ${data.sender === 'professor' ? 'sent' : 'received'}`;
        msgDiv.textContent = data.message;
        chatBody.appendChild(msgDiv);
        chatBody.scrollTop = chatBody.scrollHeight;
      }
    });

    function loadChat(person, chatBookingId) {
      currentChatPerson = person;
      bookingId = chatBookingId;
      document.getElementById('chat-person').textContent = person;

      // Fetch messages for the selected chat
      fetch(`/load-messages/${bookingId}`)
        .then(response => response.json())
        .then(messages => {
          const chatBody = document.getElementById('chat-body');
          chatBody.innerHTML = ''; // Clear existing messages
          let lastMsgTime = null;
          const chatImages = [];
          messages.forEach((msg, idx) => {
            const msgDate = new Date(msg.created_at_iso || msg.Created_At);
            if (isNaN(msgDate.getTime())) return;

            let showDate = false;
            let dateLabel = '';

            // Show label if first message or 30+ min gap
            if (!lastMsgTime || (msgDate - lastMsgTime) / (1000 * 60) >= 30) {
              showDate = true;
              const today = new Date();
              const oneWeekAgo = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000);
              
              if (
                msgDate.getDate() === today.getDate() &&
                msgDate.getMonth() === today.getMonth() &&
                msgDate.getFullYear() === today.getFullYear()
              ) {
                // Today: show only time
                dateLabel = msgDate.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
              } else if (msgDate > oneWeekAgo) {
                // Within a week: show weekday and time
                dateLabel =
                  msgDate.toLocaleDateString([], { weekday: 'short' }) +
                  ' ' +
                  msgDate.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
              } else {
                // Older than a week: show full date and time
                dateLabel = msgDate.toLocaleDateString('en-US', { 
                  month: 'numeric', 
                  day: 'numeric', 
                  year: '2-digit' 
                }) + ', ' + msgDate.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
              }
            }
            lastMsgTime = msgDate;

            if (showDate) {
              const dateDiv = document.createElement('div');
              dateDiv.className = 'chat-date-label';
              dateDiv.textContent = dateLabel;
              chatBody.appendChild(dateDiv);
            }

            // Render message with hover time (only time, no date, no seconds)
            const msgDiv = document.createElement('div');
            msgDiv.className = `message ${msg.Sender === 'professor' ? 'sent' : 'received'}`;
            if (msg.file_path) {
              const fileUrl = `/storage/${msg.file_path}`;
              if (msg.file_type && msg.file_type.startsWith('image/')) {
                const imgIndex = chatImages.length;
                chatImages.push({ url: fileUrl, name: msg.original_name || 'image', createdAt: msgDate.toISOString() });
                msgDiv.innerHTML = `<div class="chat-img-wrapper" data-index="${imgIndex}"><img src="${fileUrl}" alt="${msg.original_name || 'image'}" class="chat-image"/></div>`;
              } else {
                msgDiv.innerHTML = `<a href="${fileUrl}" target="_blank">${msg.original_name || 'Download file'}</a>`;
              }
            } else {
              msgDiv.textContent = msg.Message;
            }
            msgDiv.title = msgDate.toLocaleTimeString('en-US', { 
              timeZone: 'Asia/Manila', 
              hour: '2-digit', 
              minute: '2-digit' 
            });
            chatBody.appendChild(msgDiv);
          });
          // Click handlers for inline images
          document.querySelectorAll('.chat-img-wrapper').forEach(el => {
            el.addEventListener('click', () => {
              const idx = parseInt(el.getAttribute('data-index'));
              openImageOverlayProf(idx);
            });
          });
          window.currentProfChatImages = chatImages;
          // After rendering all messages
          setTimeout(() => {
            chatBody.scrollTop = chatBody.scrollHeight;
          }, 0);
        })
        .catch(error => {
            // Error loading messages
        });
    }

    function startVideoCall() {
      if (!currentChatPerson) {
        alert('Please select a student to start a video call.');
        return;
      }
      const channel = encodeURIComponent(currentChatPerson.replace(/\s+/g, ''));
      window.location.href = `/prof-call/${channel}`;
    }

    let selectedFiles = [];

    document.getElementById("file-input").addEventListener("change", function (e) {
        const files = Array.from(e.target.files);
        selectedFiles = selectedFiles.concat(files);
        renderFilePreviews();
        e.target.value = ''; // Reset file input for next selection
    });

    function renderFilePreviews() {
        const container = document.getElementById('file-preview-container');
        container.innerHTML = '';
        selectedFiles.forEach((file, idx) => {
            const preview = document.createElement('div');
            preview.className = 'file-preview';
            if (file.type.startsWith('image/')) {
                const img = document.createElement('img');
                img.src = URL.createObjectURL(file);
                preview.appendChild(img);
                // Do NOT append file name for images
            } else {
                const icon = document.createElement('span');
                icon.innerHTML = "<i class='bx bx-file'></i>";
                preview.appendChild(icon);
                const name = document.createElement('span');
                name.textContent = file.name.length > 20 ? file.name.slice(0, 17) + '...' : file.name;
                preview.appendChild(name);
            }
            const removeBtn = document.createElement('button');
            removeBtn.className = 'remove-file';
            removeBtn.innerHTML = '&times;';
            removeBtn.onclick = () => {
                selectedFiles.splice(idx, 1);
                renderFilePreviews();
            };
            preview.appendChild(removeBtn);

            container.appendChild(preview);
        });
    }

    // Stretch textarea like Messenger
    const textarea = document.getElementById('message-input');
    textarea.addEventListener('input', function () {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });

    // Send message with files
    function sendMessage() {
        const message = textarea.value.trim();
        if (!message && selectedFiles.length === 0) return;

        const formData = new FormData();
        formData.append('message', message);
        formData.append('recipient', currentChatPerson);
        formData.append('bookingId', bookingId);
        formData.append('sender', 'professor'); // or 'student' for student side
        formData.append('_token', '{{ csrf_token() }}');
        selectedFiles.forEach((file, i) => {
            formData.append('files[]', file);
        });

        fetch('/send-message', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'Message sent!') {
                textarea.value = '';
                textarea.style.height = 'auto';
                selectedFiles = [];
                renderFilePreviews();
                loadChat(currentChatPerson, bookingId); // <--- This reloads the chat
            } else {
                alert('Error sending: ' + (data.error || data.status));
            }
        })
        .catch(error => alert('Error: ' + error));
    }

    document.getElementById("attach-btn")?.addEventListener("click", function () {
        document.getElementById("file-input").click();
    });

    // ENTER to send (Shift+Enter = newline) for professor textarea
    const profMsgInput = document.getElementById('message-input');
    if (profMsgInput) {
      profMsgInput.addEventListener('keydown', function(e){
        if(e.key === 'Enter' && !e.shiftKey){
          e.preventDefault();
          sendMessage();
        }
      });
      // Auto-resize like student side
      profMsgInput.addEventListener('input', function(){
        this.style.height='auto';
        this.style.height=this.scrollHeight+'px';
      });
    }

    // document.getElementById("file-input").addEventListener("change", function (e) {
    //     const files = Array.from(e.target.files);
    //     const filePreviewContainer = document.getElementById("file-preview-container");
    //     filePreviewContainer.innerHTML = ""; // Clear previous previews

    //     files.forEach((file) => {
    //         const fileDiv = document.createElement("div");
    //         fileDiv.className = "file-preview";

    //         const fileName = document.createElement("span");
    //         fileName.className = "file-name";
    //         fileName.textContent = file.name;

    //         const removeBtn = document.createElement("button");
    //         removeBtn.className = "remove-file";
    //         removeBtn.textContent = "Remove";
    //         removeBtn.onclick = function () {
    //             fileDiv.remove();
    //             const index = files.indexOf(file);
    //             if (index > -1) {
    //                 files.splice(index, 1);
    //             }
    //         };

    //         fileDiv.appendChild(fileName);
    //         fileDiv.appendChild(removeBtn);
    //         filePreviewContainer.appendChild(fileDiv);
    //     });

    //     e.target.value = ""; // Reset file input
    // });
  </script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Auto-select the first student in the inbox
      const firstInboxItem = document.querySelector('.inbox-item');
      if (firstInboxItem) {
        firstInboxItem.click();
      }
    });


    function isMobile() {
      return window.innerWidth <= 700;
    }

    function showChatPanel() {
      if (isMobile()) {
        document.getElementById('chat-panel').classList.add('active');
        document.getElementById('back-btn').style.display = 'block';
        document.body.style.overflow = 'hidden';
      }
    }

    function hideChatPanel() {
      if (isMobile()) {
        document.getElementById('chat-panel').classList.remove('active');
        document.getElementById('back-btn').style.display = 'none';
        document.body.style.overflow = '';
      }
    }

    // Show chat panel on inbox item click (mobile)
    document.querySelectorAll('.inbox-item').forEach(item => {
      item.addEventListener('click', showChatPanel);
    });

    // Back button to return to inbox (mobile)
    document.getElementById('back-btn').addEventListener('click', hideChatPanel);

    // On resize, hide chat panel if switching to desktop
    window.addEventListener('resize', function() {
      if (!isMobile()) {
        document.getElementById('chat-panel').classList.add('active');
        document.getElementById('back-btn').style.display = 'none';
        document.body.style.overflow = '';
      } else {
        document.getElementById('chat-panel').classList.remove('active');
        document.getElementById('back-btn').style.display = 'none';
        document.body.style.overflow = '';
      }
    });

    // On load, show chat panel on desktop, hide on mobile
    document.addEventListener('DOMContentLoaded', function() {
      if (!isMobile()) {
        document.getElementById('chat-panel').classList.add('active');
        document.getElementById('back-btn').style.display = 'none';
        const firstInboxItem = document.querySelector('.inbox-item');
        if (firstInboxItem) {
          firstInboxItem.click();
        }
      } else {
        document.getElementById('chat-panel').classList.remove('active');
        document.getElementById('back-btn').style.display = 'none';
        // Do NOT auto-load any chat on mobile
      }
    });

    // IMAGE OVERLAY (professor side)
    function openImageOverlayProf(index) {
      const images = window.currentProfChatImages || [];
      if (!images.length || index < 0 || index >= images.length) return;
      window.currentProfImageIndex = index;
      const overlay = document.getElementById('prof-image-overlay');
      const mainImg = document.getElementById('prof-overlay-main');
      const dl = document.getElementById('prof-overlay-download');
      const data = images[index];
      mainImg.src = data.url;
      mainImg.alt = data.name;
      dl.href = data.url;
      dl.setAttribute('download', data.name.replace(/[^a-zA-Z0-9._-]/g,'_'));
      buildProfThumbs();
      overlay.classList.remove('hidden');
      document.body.style.overflow = 'hidden';
    }
    function closeProfImageOverlay(){
      const overlay = document.getElementById('prof-image-overlay');
      overlay.classList.add('hidden');
      document.body.style.overflow='';
    }
    function navProfImage(delta){
      const images = window.currentProfChatImages || [];
      if(!images.length) return;
      let idx = (window.currentProfImageIndex||0)+delta;
      if(idx<0) idx = images.length-1;
      if(idx>=images.length) idx=0;
      openImageOverlayProf(idx);
    }
    function buildProfThumbs(){
      const images = window.currentProfChatImages || [];
      const thumbs = document.getElementById('prof-overlay-thumbs');
      if(!thumbs) return;
      thumbs.innerHTML='';
      images.forEach((img,i)=>{
        const t=document.createElement('img');
        t.src=img.url; t.alt=img.name; t.className='overlay-thumb'+(i===window.currentProfImageIndex?' active':'');
        t.addEventListener('click',()=>openImageOverlayProf(i));
        thumbs.appendChild(t);
      });
    }
    document.addEventListener('keydown',(e)=>{
      const overlay=document.getElementById('prof-image-overlay');
      if(!overlay || overlay.classList.contains('hidden')) return;
      if(e.key==='Escape') closeProfImageOverlay();
      else if(e.key==='ArrowRight') navProfImage(1);
      else if(e.key==='ArrowLeft') navProfImage(-1);
    });
    document.addEventListener('click',(e)=>{
      const overlay=document.getElementById('prof-image-overlay');
      if(!overlay || overlay.classList.contains('hidden')) return;
      if(e.target===overlay) closeProfImageOverlay();
    });
  </script>
  <!-- Professor Image Overlay -->
  <div id="prof-image-overlay" class="image-overlay hidden">
    <button class="overlay-btn close" onclick="closeProfImageOverlay()" aria-label="Close image">&times;</button>
    <a id="prof-overlay-download" class="overlay-btn download" aria-label="Download image"><i class='bx bx-download'></i></a>
    <button class="overlay-nav prev" onclick="navProfImage(-1)" aria-label="Previous image">&#10094;</button>
    <button class="overlay-nav next" onclick="navProfImage(1)" aria-label="Next image">&#10095;</button>
    <img id="prof-overlay-main" class="overlay-main" alt="Preview" />
    <div id="prof-overlay-thumbs" class="overlay-thumbs"></div>
  </div>
</body>
</html>