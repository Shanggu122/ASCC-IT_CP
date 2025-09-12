<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="{{ asset('css/messages.css') }}">
    <style>
        .message.sent {
            text-align: right;
            background-color: #c7e5dd;
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
<body class="messages-page">
    @include('components.navbar')
    <div class="main-content">
        <div class="messages-wrapper">
            <div class="inbox">
                <div class="inbox-header-line">
                  <h2>Professors</h2>
                </div>
                <div class="search-wrapper">
                  <input type="text" id="prof-search" placeholder="Search professor..." oninput="filterProfessors()" />
                </div>
        @php
          $currentDept = null;
          $deptLabels = [1=>'IT & IS','2'=>'Computer Science'];
        @endphp
        @foreach($professors as $professor)
          @php
            $dept = $professor->dept_id ?? null;
            if($dept !== $currentDept){
              $currentDept = $dept;
              $label = $deptLabels[$dept] ?? 'Other Department';
              echo '<div class="dept-separator" data-dept="'.e($label).'">'.e($label).'</div>';
            }
          @endphp
          @php
            // Accept possible field name variations
            $pic = null;
            if (is_object($professor)) {
                if (property_exists($professor,'profile_picture')) { $pic = $professor->profile_picture; }
                elseif (property_exists($professor,'Profile_Picture')) { $pic = $professor->Profile_Picture; }
                elseif (property_exists($professor,'photo')) { $pic = $professor->photo; }
            }
            $picUrl = $pic ? asset('storage/'.$pic) : asset('images/dprof.jpg');
            $lastMessage = $professor->last_message ?? 'No messages yet';
            $youPrefix = isset($professor->last_sender) && $professor->last_sender === 'student' ? 'You: ' : '';
            $displayMessage = $youPrefix . $lastMessage;
            $shortMsg = \Illuminate\Support\Str::limit($displayMessage, 40); // truncate with ellipsis
            $relTime = $professor->last_message_time
              ? \Carbon\Carbon::parse($professor->last_message_time)->timezone('Asia/Manila')->diffForHumans(['short'=>true])
              : '';
          @endphp
          <div class="inbox-item" data-name="{{ strtolower($professor->name) }}" data-dept="{{ strtolower($deptLabels[$professor->dept_id] ?? 'other') }}" data-can-video="{{ isset($professor->can_video_call) && $professor->can_video_call ? '1':'0' }}" onclick="loadChat('{{ $professor->name }}', {{ $professor->booking_id ? $professor->booking_id : 'null' }})">
              <img class="inbox-avatar" src="{{ $picUrl }}" alt="{{ $professor->name }}">
              <div class="inbox-meta">
                  <div class="name">{{ $professor->name }}</div>
                  <div class="snippet-line">
                      @if($professor->booking_id)
                        <span class="snippet" title="{{ $displayMessage }}">{!! isset($professor->last_sender) && $professor->last_sender==='student' ? '<strong>You:</strong> ' : '' !!}{{ \Illuminate\Support\Str::limit($lastMessage, 36) }}</span>
                      @else
                        <span class="snippet" title="No conversation yet">No conversation yet</span>
                      @endif
                      @if($relTime)
                        <span class="rel-time">{{ $relTime }}</span>
                      @endif
                  </div>
              </div>
          </div>
        @endforeach
            </div>
            <div class="chat-panel" id="chat-panel">
                <div class="chat-header">
                    <button class="back-btn" id="back-btn" style="display:none;"><i class='bx bx-arrow-back'></i></button>
                    <span id="chat-person">Select a Professor</span>
                    <button class="video-btn" id="launch-call" onclick="startVideoCall()" disabled title="Video call available only on an approved or rescheduled consultation today">Video Call</button>
                </div>
                <div class="chat-body" id="chat-body">
                    @if(count($professors) === 0)
                        <div class="message">No professors found.</div>
                    @endif
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
    <script src="{{ asset('js/messages.js') }}"></script>
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
            msgDiv.className = `message ${data.sender === 'student' ? 'sent' : 'received'}`;
            msgDiv.textContent = data.message;
            chatBody.appendChild(msgDiv);
            chatBody.scrollTop = chatBody.scrollHeight;
          }
        });

  function loadChat(person, chatBookingId) {
          currentChatPerson = person;
          bookingId = chatBookingId;
          document.getElementById('chat-person').textContent = person;

          // Highlight the selected inbox item
          document.querySelectorAll('.inbox-item').forEach(item => item.classList.remove('active'));
          // Find the clicked inbox item and add 'active'
          const inboxItems = document.querySelectorAll('.inbox-item');
          inboxItems.forEach(item => {
            if (item.textContent.includes(person)) {
              item.classList.add('active');
            }
          });

          // Set video call button state based on inbox item attribute
          const selected = Array.from(document.querySelectorAll('.inbox-item')).find(i=>i.classList.contains('active'));
          const canVideo = selected && selected.getAttribute('data-can-video') === '1';
          const btn = document.getElementById('launch-call');
          if(canVideo){
            btn.disabled = false;
            btn.title = 'Start video call';
          } else {
            btn.disabled = true;
            btn.title = 'Video call available only on an approved or rescheduled consultation today';
          }

          // Fetch messages for the selected chat
          if(!bookingId){
            const chatBody = document.getElementById('chat-body');
            chatBody.innerHTML = '<div class="message">No conversation yet. You can start the conversation anytime.</div>';
            return;
          }

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
                msgDiv.className = `message ${msg.Sender === 'student' ? 'sent' : 'received'}`;
                if (msg.file_path) {
                  const fileUrl = `/storage/${msg.file_path}`;
                  if (msg.file_type && msg.file_type.startsWith('image/')) {
                    const imgIndex = chatImages.length; // index before push
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
              // After loop attach click handlers for images
              document.querySelectorAll('.chat-img-wrapper').forEach(el => {
                el.addEventListener('click', () => {
                  const idx = parseInt(el.getAttribute('data-index'));
                  openImageOverlay(idx);
                });
              });
              // Store images on window for navigation
              window.currentChatImages = chatImages;
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
            alert('Please select a professor to start a video call.');
            return;
          }
          const active = document.querySelector('.inbox-item.active');
          if(!active || active.getAttribute('data-can-video') !== '1'){
            alert('Video call only works if you have an approved or rescheduled consultation today.');
            return;
          }
          const channel = encodeURIComponent(currentChatPerson.replace(/\s+/g, ''));
          window.location.href = `/video-call/${channel}`;
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
    // Enter to send (Shift+Enter = newline)
    textarea.addEventListener('keydown', function(e){
      if(e.key === 'Enter' && !e.shiftKey){
        e.preventDefault();
        sendMessage();
      }
    });

        // Send message with files
        function sendMessage() {
            const message = textarea.value.trim();
            if (!message && selectedFiles.length === 0) return;

            const formData = new FormData();
            formData.append('message', message);
            formData.append('recipient', currentChatPerson);
            formData.append('bookingId', bookingId);
            formData.append('sender', 'student');
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

        // Responsive logic (mobile layout under or equal 700px)
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
          // (removed sticky & dynamic scrollbar class logic)
        });

        // IMAGE OVERLAY LIGHTBOX (student side only)
        function openImageOverlay(index) {
          const images = window.currentChatImages || [];
          if (!images.length || index < 0 || index >= images.length) return;
          window.currentImageIndex = index;
          const overlay = document.getElementById('image-overlay');
          const mainImg = document.getElementById('overlay-main');
          const dl = document.getElementById('overlay-download');
          const data = images[index];
          mainImg.src = data.url;
          mainImg.alt = data.name;
            // Force download filename
          dl.href = data.url;
          dl.setAttribute('download', data.name.replace(/[^a-zA-Z0-9._-]/g,'_'));
          buildThumbs();
          overlay.classList.remove('hidden');
          document.body.style.overflow = 'hidden';
        }

        function closeImageOverlay() {
          const overlay = document.getElementById('image-overlay');
          overlay.classList.add('hidden');
          document.body.style.overflow = '';
        }

        function navImage(delta) {
          const images = window.currentChatImages || [];
          if (!images.length) return;
          let idx = (window.currentImageIndex || 0) + delta;
          if (idx < 0) idx = images.length - 1;
          if (idx >= images.length) idx = 0;
          openImageOverlay(idx);
        }

        function buildThumbs() {
          const images = window.currentChatImages || [];
          const thumbs = document.getElementById('overlay-thumbs');
          if (!thumbs) return;
          thumbs.innerHTML = '';
          images.forEach((img, i) => {
            const t = document.createElement('img');
            t.src = img.url;
            t.alt = img.name;
            t.className = 'overlay-thumb' + (i === window.currentImageIndex ? ' active' : '');
            t.addEventListener('click', () => openImageOverlay(i));
            thumbs.appendChild(t);
          });
        }

        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
          const overlay = document.getElementById('image-overlay');
          if (overlay.classList.contains('hidden')) return;
          if (e.key === 'Escape') closeImageOverlay();
          else if (e.key === 'ArrowRight') navImage(1);
          else if (e.key === 'ArrowLeft') navImage(-1);
        });

        // Click outside main image closes
        document.addEventListener('click', (e) => {
          const overlay = document.getElementById('image-overlay');
          if (!overlay || overlay.classList.contains('hidden')) return;
          if (e.target === overlay) closeImageOverlay();
        });

        // Search filtering
        function filterProfessors(){
          const term = document.getElementById('prof-search').value.trim().toLowerCase();
          const items = document.querySelectorAll('.inbox-item');
          items.forEach(it=>{
            const name = it.getAttribute('data-name');
            if(!term || name.includes(term)){
              it.style.display='flex';
            } else {
              it.style.display='none';
            }
          });
          // Hide dept headers if no visible items below them
          document.querySelectorAll('.dept-separator').forEach(header=>{
            let next = header.nextElementSibling;
            let anyVisible = false;
            while(next && !next.classList.contains('dept-separator')){
              if(next.classList.contains('inbox-item') && next.style.display!=='none'){ anyVisible=true; break; }
              next = next.nextElementSibling;
            }
            header.style.display = anyVisible ? 'block' : 'none';
          });
        }
    </script>
    <!-- Image Overlay (Student only) -->
    <div id="image-overlay" class="image-overlay hidden">
      <button class="overlay-btn close" onclick="closeImageOverlay()" aria-label="Close image">&times;</button>
      <a id="overlay-download" class="overlay-btn download" aria-label="Download image"><i class='bx bx-download'></i></a>
      <button class="overlay-nav prev" onclick="navImage(-1)" aria-label="Previous image">&#10094;</button>
      <button class="overlay-nav next" onclick="navImage(1)" aria-label="Next image">&#10095;</button>
      <img id="overlay-main" class="overlay-main" alt="Preview" />
      <div id="overlay-thumbs" class="overlay-thumbs"></div>
    </div>
</body>
</html>
