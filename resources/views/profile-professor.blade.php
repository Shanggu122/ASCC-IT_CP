<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Profile</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/profile-professor.css') }}">
    <link rel="stylesheet" href="{{ asset('css/profile-shared.css') }}">
  <link rel="stylesheet" href="{{ asset('css/confirm-modal.css') }}">
</head>
<body>
  @include('components.navbarprof')

  <div class="main-content">
    <!-- Header -->
    <div class="header-info">
      <div class="profile-pic-wrapper">
        @php
            use Illuminate\Support\Facades\Storage;
            $profileUrl = ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture))
                ? Storage::url($user->profile_picture)
                : asset('images/dprof.jpg');
        @endphp
        <img src="{{ $profileUrl }}" alt="Profile Picture" class="profile-picture" id="profilePicture">
        <button type="button" class="edit-profile-pic-btn" onclick="togglePanel('profilePicPanel')">
          <i class='bx bx-camera'></i>
        </button>
      </div>
      <div>
        <h2 class="user-name">{{ $user->Name }}</h2>
        <div class="prof-id">{{ $user->Prof_ID }}</div>
      </div>
    </div>

    <!-- Basic Information -->
    <div class="info-section">
      <div class="section-title">BASIC INFORMATION</div>
      <table class="info-table">
        <tr>
          <td class="info-label">Full name</td>
          <td>{{ $user->Name ?? '' }}</td>
        </tr>
        <tr>
          <td class="info-label">Email</td>
          <td>{{ $user->Email ?? '' }}</td>
        </tr>
        <tr>
          <td class="info-label">Password</td>
          <td>
            <a href="javascript:void(0)" onclick="togglePanel('passwordPanel')" class="change-link">Change Password</a>
            <i class='bx bx-edit-alt edit-icon' title="Edit Password"></i>
          </td>
        </tr>
      </table>
    </div>

    <!-- Chat Overlay Panel -->
    <div class="chat-overlay" id="chatOverlay">
      <div class="chat-header">
        <span>AI Chat Assistant</span>
        <button class="close-btn" onclick="closePanel('chatOverlay')">×</button>
      </div>
      <div class="chat-body" id="chatBody">
        <div class="message bot">Hi! How can I help you today?</div>
      </div>
      <form id="chatForm">
        <input type="text" id="userInput" placeholder="Type your message..." required>
        <button type="submit">Send</button>
      </form>
    </div>

    <!-- Password Change Panel -->
    <div class="side-panel" id="passwordPanel">
      <div class="panel-header">
        <span>Change Password</span>
      </div>
      <div class="panel-body">
        <p>
          You can change your ASCC-IT account password here.
        </p>
        <form action="{{ route('changePassword.professor') }}" method="POST">
          @csrf
          <!-- Old Password -->
          <label for="oldPassword">Old Password</label>
          <div class="password-field">
            <input type="password" id="oldPassword" name="oldPassword" placeholder="Enter current password" required>
            <i class='bx bx-hide eye-icon' onclick="togglePasswordVisibility('oldPassword', this)"></i>
          </div>
          <!-- New Password -->
          <label for="newPassword">New Password</label>
          <div class="password-field">
            <input type="password" id="newPassword" name="newPassword" placeholder="Enter new password" required>
            <i class='bx bx-hide eye-icon' onclick="togglePasswordVisibility('newPassword', this)"></i>
          </div>
          <small class="password-hint">
            Password must be at least 8 characters long and different from your current password.
          </small>
          <!-- Confirm New Password -->
          <label for="newPassword_confirmation">Confirm New Password</label>
          <div class="password-field">
            <input type="password" id="newPassword_confirmation" name="newPassword_confirmation" placeholder="Confirm new password" required>
            <i class='bx bx-hide eye-icon' onclick="togglePasswordVisibility('newPassword_confirmation', this)"></i>
          </div>
          <small class="confirm-password-hint">
            Please re-enter your new password to confirm.
          </small>
          <div class="panel-footer">
            <button type="button" class="cancel-btn" id="pw-cancel-btn-prof">Cancel</button>
            <button type="submit" class="save-btn">Save</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Profile Picture Change Panel -->
    <div class="side-panel" id="profilePicPanel">
      <div class="panel-header">
        <span>Change Profile Picture</span>
        <button class="close-btn" type="button" onclick="closePanel('profilePicPanel')">&times;</button>
      </div>
      <div class="panel-body profile-pic-panel-body">
        <div class="profile-pic-container">
    <img id="sidePanelProfilePic"
      src="{{ $profileUrl }}"
      alt="Profile Picture"
      class="side-panel-profile-pic">
          <button class="delete-pic-btn" type="button" onclick="deleteProfilePicture()" title="Delete Profile Picture">
            <i class='bx bx-trash'></i>
          </button>
        </div>
        <form id="profilePicForm" action="{{ route('profile.uploadPicture.professor') }}" method="POST" enctype="multipart/form-data" class="profile-pic-form">
          @csrf
          <label for="sidePanelInputFile" class="upload-label">
            Upload new profile picture
          </label>
          <input type="file" id="sidePanelInputFile" name="profile_picture" accept="image/jpeg, image/png, image/jpg">
          <button type="submit" class="save-btn" id="sidePanelSaveBtn">Save</button>
        </form>
      </div>
    </div>

    <button class="chat-button" onclick="togglePanel('chatOverlay')">
      <i class='bx bxs-message-rounded-dots'></i>
      Click to chat with me!
    </button>

    <!-- Notification Div -->
    <div id="notification" class="notification" style="display:none;">
      <span id="notification-message"></span>
      <button onclick="hideNotification()" class="close-btn">&times;</button>
    </div>
  </div>

  <script src="{{ asset('js/profileProf.js') }}"></script>
  <script>
function showNotification(message, isError = false) {
  let notif = document.getElementById('notification');
  notif.classList.toggle('error', isError);
  document.getElementById('notification-message').textContent = message;
  notif.style.display = 'flex';
  setTimeout(hideNotification, 4000);
}
function hideNotification() {
  document.getElementById('notification').style.display = 'none';
}

function togglePanel(panelId) {
  // Close all panels first
  let panels = ['passwordPanel', 'profilePicPanel', 'chatOverlay'];
  panels.forEach(id => {
    let el = document.getElementById(id);
    if (el) el.classList.remove('open');
  });
  // Open the requested panel
  let panel = document.getElementById(panelId);
  if (panel) panel.classList.toggle('open');
}

function closePanel(panelId) {
  let panel = document.getElementById(panelId);
  if (panel) panel.classList.remove('open');
}

// Profile picture panel file input and preview
const sidePanelInputFile = document.getElementById('sidePanelInputFile');
const sidePanelProfilePic = document.getElementById('sidePanelProfilePic');
const sidePanelSaveBtn = document.getElementById('sidePanelSaveBtn');

if (sidePanelInputFile) {
  sidePanelInputFile.onchange = function() {
    if (sidePanelInputFile.files && sidePanelInputFile.files[0]) {
      sidePanelProfilePic.src = URL.createObjectURL(sidePanelInputFile.files[0]);
      if (sidePanelSaveBtn) sidePanelSaveBtn.style.display = "inline-block";
    } else {
      if (sidePanelSaveBtn) sidePanelSaveBtn.style.display = "none";
    }
  };
}

function deleteProfilePicture() {
  // Themed confirmation overlay
  let overlay = document.getElementById('confirmOverlayDeleteProf');
  if(!overlay){
    overlay = document.createElement('div');
    overlay.id = 'confirmOverlayDeleteProf';
    overlay.className = 'confirm-overlay';
    overlay.innerHTML = `
      <div class="confirm-modal">
        <div class="confirm-header"><i class='bx bx-trash'></i> Delete profile picture?</div>
        <div class="confirm-body">This action will remove your current profile photo and revert to the default avatar.</div>
        <div class="confirm-actions">
          <button type="button" class="btn-cancel-red" id="delNoProf">Cancel</button>
          <button type="button" class="btn-confirm-green" id="delYesProf">Delete</button>
        </div>
      </div>`;
    document.body.appendChild(overlay);
  }
  const close = ()=> overlay.classList.remove('active');
  overlay.classList.add('active');
  // wire handlers
  overlay.querySelector('#delNoProf').onclick = close;
  overlay.querySelector('#delYesProf').onclick = function(){
    fetch("{{ route('profile.deletePicture.professor') }}", {
      method: "POST",
      headers: {
        "X-CSRF-TOKEN": "{{ csrf_token() }}",
        "Accept": "application/json"
      }
    })
    .then(r => r.json())
    .then(data => {
      if(data.success){
        document.getElementById('profilePicture').src = "{{ asset('images/dprof.jpg') }}";
        document.getElementById('sidePanelProfilePic').src = "{{ asset('images/dprof.jpg') }}";
        showNotification('Profile picture deleted.');
      } else {
        showNotification('Failed to delete profile picture.', true);
      }
    })
    .catch(() => showNotification('Error deleting profile picture.', true))
    .finally(close);
  };
  overlay.addEventListener('click', (e)=>{ if(e.target === overlay) close(); }, { once:true });
}

// Initialize chat functionality exactly like dashboard
document.addEventListener('DOMContentLoaded', function() {
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  const chatForm = document.getElementById('chatForm');
  const input = document.getElementById('userInput');  // Profile uses userInput instead of message
  const chatBody = document.getElementById('chatBody');

  if (chatForm && input && chatBody && csrfToken) {
    chatForm.addEventListener('submit', async function(e) {
      e.preventDefault();
      const text = input.value.trim();
      if (!text) return;

      // show user message
      const um = document.createElement('div');
      um.classList.add('message', 'user');
      um.innerText = text;
      chatBody.appendChild(um);

      chatBody.scrollTop = chatBody.scrollHeight;
      input.value = '';

      // send request to server
      const res = await fetch('/chat', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken,  
        },
        body: JSON.stringify({ message: text }),
      });

      if (!res.ok) {
        const err = await res.json();
        const bm = document.createElement('div');
        bm.classList.add('message', 'bot');
        bm.innerText = err.message || 'Server error.';
        chatBody.appendChild(bm);
        return;
      }

      // render bot reply
      const { reply } = await res.json();
      const bm = document.createElement('div');
      bm.classList.add('message', 'bot');
      bm.innerText = reply;
      chatBody.appendChild(bm);
      chatBody.scrollTop = chatBody.scrollHeight;
    });
  }
});

// OVERRIDE function for backward compatibility (if called directly)
async function sendMessage() {
    const input = document.getElementById("userInput");
    if (!input) return;
    
    const form = document.getElementById('chatForm');
    if (form) {
        // Trigger the form submit which will handle everything
        form.dispatchEvent(new Event('submit'));
    }
}

// Add Enter key functionality for forms
document.addEventListener('DOMContentLoaded', function() {
    // Password change form inputs
    const passwordInputs = ['oldPassword', 'newPassword', 'newPassword_confirmation'];
    passwordInputs.forEach(inputId => {
        const input = document.getElementById(inputId);
        if (input) {
            input.addEventListener('keydown', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    const form = input.closest('form');
                    if (form) {
                        form.requestSubmit();
                    }
                }
            });
        }
    });
    
    // Add Enter key functionality for the chat input (exactly like dashboard)
    const messageInput = document.getElementById('userInput');
    if (messageInput) {
        messageInput.addEventListener('keydown', function(event) {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                const form = document.getElementById('chatForm');
                if (form) {
                    form.dispatchEvent(new Event('submit'));
                }
            }
        });
    }
});
  </script>
  @php
    $pwHasErrorsProf = session('error') || $errors->has('oldPassword') || $errors->has('newPassword') || $errors->has('newPassword_confirmation');
  @endphp
  @if($pwHasErrorsProf)
  <script>
    document.addEventListener('DOMContentLoaded', function(){
      const p = document.getElementById('passwordPanel');
      if(p) p.classList.add('open');
    });
  </script>
  @endif

  <!-- Blade logic to trigger notification -->
  @if (session('status'))
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        showNotification(@json(session('status')), false);
      });
    </script>
  @endif

  @if (session('password_status'))
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        closePanel('passwordPanel');
        setTimeout(function() {
          showNotification(@json(session('password_status')), false);
        }, 300);
      });
    </script>
  @endif

  @if ($errors->any())
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        // Handle password-specific errors
        @if ($errors->has('oldPassword'))
          showNotification(@json($errors->first('oldPassword')), true);
        @elseif ($errors->has('newPassword'))
          showNotification(@json($errors->first('newPassword')), true);
        @elseif ($errors->has('newPassword_confirmation'))
          showNotification('Your new password and confirmation password do not match. Please re-enter them correctly.', true);
        @else
          showNotification(@json($errors->first()), true);
        @endif
      });
    </script>
  @endif

    <script>
    // Professor: confirmation modal for cancel (only if all fields filled)
    document.addEventListener('DOMContentLoaded', function(){
      const cancelBtn = document.getElementById('pw-cancel-btn-prof');
      if(!cancelBtn) return;
      let overlay = document.getElementById('confirmOverlayProf');
      if(!overlay){
        overlay = document.createElement('div');
        overlay.id = 'confirmOverlayProf';
        overlay.className = 'confirm-overlay';
        overlay.innerHTML = `
          <div class="confirm-modal">
            <div class="confirm-header"><i class='bx bx-help-circle'></i> Confirm cancel</div>
            <div class="confirm-body">Are you sure you want to cancel changing your password? Your changes will not be saved.</div>
            <div class="confirm-actions">
              <button type="button" class="btn-cancel-red" id="confirmNoProf">No, keep editing</button>
              <button type="button" class="btn-confirm-green" id="confirmYesProf">Yes, cancel</button>
            </div>
          </div>`;
        document.body.appendChild(overlay);
      }
      cancelBtn.addEventListener('click', function(){
        const oldP = document.getElementById('oldPassword');
        const newP = document.getElementById('newPassword');
        const confP = document.getElementById('newPassword_confirmation');
        const allFilled = [oldP, newP, confP].every(el => el && el.value.trim().length > 0);
        if(allFilled){
          overlay.classList.add('active');
          const onNo = ()=>{ overlay.classList.remove('active'); };
          const onYes = ()=>{ overlay.classList.remove('active'); closePanel('passwordPanel'); };
          document.getElementById('confirmNoProf').onclick = onNo;
          document.getElementById('confirmYesProf').onclick = onYes;
          overlay.addEventListener('click', (e)=>{ if(e.target === overlay) onNo(); }, { once:true });
          return;
        }
        closePanel('passwordPanel');
      });
    });
    </script>

</body>
</html>