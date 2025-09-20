<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Profile</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/profile.css') }}">
  <style>
    /* Confirmation Modal Styles */
    .confirmation-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.6);
      display: none;
      justify-content: center;
      align-items: center;
      z-index: 10000;
    }

    .confirmation-overlay.show {
      display: flex;
    }

    .confirmation-modal {
      background: #194d36;
      border-radius: 32px;
      padding: 2.5rem 2.2rem 2rem 2.2rem;
      min-width: 340px;
      max-width: 500px;
      width: 100%;
      text-align: center;
      color: #fff;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.18);
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .confirmation-modal-text {
      font-size: 1.25rem;
      font-weight: 700;
      margin-bottom: 1.2rem;
      line-height: 1.3;
      font-family: "Poppins", sans-serif;
    }

    .confirmation-modal-sub {
      font-size: 1.1rem;
      font-weight: 400;
      margin-bottom: 2.2rem;
      color: #e5e5e5;
      font-family: "Poppins", sans-serif;
    }

    .confirm-red {
      color: #e74c3c;
      font-weight: 700;
    }

    .confirm-green {
      color: #27ae60;
      font-weight: 700;
    }

    .confirmation-modal-btns {
      display: flex;
      gap: 2.5rem;
      justify-content: center;
    }

    .modal-confirm,
    .modal-cancel {
      background: #fff;
      border: none;
      border-radius: 22px;
      padding: 0.7rem 2.5rem;
      font-size: 1.25rem;
      font-family: "Poppins", sans-serif;
      font-weight: 700;
      cursor: pointer;
      transition: background 0.2s, color 0.2s;
      margin-top: 0;
    }

    .modal-confirm.danger {
      color: #e74c3c;
    }

    .modal-confirm.danger:hover {
      background: #ffeaea;
    }

    .modal-confirm.success {
      color: #27ae60;
    }

    .modal-confirm.success:hover {
      background: #eafaf1;
    }

    .modal-cancel {
      color: #194d36;
      font-weight: 600;
    }

    .modal-cancel:hover {
      background: #e5f0ed;
    }

    .italic {
      font-style: italic;
    }

    /* Warning notification style */
    .notification.warning {
      background-color: #f39c12;
      border-left: 4px solid #e67e22;
    }
  </style>
</head>
<body>
  @include('components.navbar')

  <div class="main-content">
    <!-- Header -->
    <div class="header-info">
      <div class="profile-pic-wrapper">
        <img src="{{ $user->profile_picture ? asset('storage/' . $user->profile_picture) : asset('images/dprof.jpg') }}" alt="Profile Picture" class="profile-picture" id="profilePicture">
        <button type="button" class="edit-profile-pic-btn" onclick="togglePanel('profilePicPanel')">
          <i class='bx bx-camera'></i>
        </button>
      </div>
       <div>
         <h2 class="user-name">{{ $user->Name }}</h2>
         <div class="student-id">{{ $user->Stud_ID }}</div>
       </div>
    </div>

    <!-- Basic Information -->
    <div class="info-section">
      <div class="section-title">BASIC INFORMATION</div>
      <table class="info-table">
        <tr>
          <td class="info-label">Full name</td>
           <td>{{ $user->Name }}</td>
        </tr>
        <tr>
          <td class="info-label">Email</td>
           <td>{{ $user->Email }}</td>
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

    <!-- Password Change Panel -->
    <div class="side-panel" id="passwordPanel">
      <div class="panel-header">
        <span>Change Password</span>
      </div>
      <div class="panel-body">
        <p>
          You can change your ASCC-IT account password here.
        </p>
  
        <form action="{{ route('changePassword') }}" method="POST">
          @csrf <!-- CSRF token to protect from cross-site request forgery -->
          
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
        <small class="password-hint" style="color: #666; font-size: 12px; margin-top: 4px; display: block;">
          Password must be at least 8 characters long and different from your current password.
        </small>

        <!-- Confirm New Password -->
        <label for="newPassword_confirmation">Confirm New Password</label>
        <div class="password-field">
          <input type="password" id="newPassword_confirmation" name="newPassword_confirmation" placeholder="Confirm new password" required>
          <i class='bx bx-hide eye-icon' onclick="togglePasswordVisibility('newPassword_confirmation', this)"></i>
        </div>
        <small class="confirm-password-hint" style="color: #666; font-size: 12px; margin-top: 4px; display: block;">
          Please re-enter your new password to confirm.
        </small>

        <div class="panel-footer">
          <button type="button" class="cancel-btn" onclick="cancelWithConfirmation('passwordPanel')">Cancel</button>
          <button type="submit" class="save-btn" onclick="confirmPasswordSave(event)">Save</button>  
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
                 src="{{ $user->profile_picture ? asset('storage/' . $user->profile_picture) : asset('images/dprof.jpg') }}"
                 alt="Profile Picture"
                 class="side-panel-profile-pic">
            <button class="delete-pic-btn" type="button" onclick="deleteProfilePicture()" title="Delete Profile Picture">
                <i class='bx bx-trash'></i>
            </button>
        </div>
        <form id="profilePicForm" action="{{ route('profile.uploadPicture') }}" method="POST" enctype="multipart/form-data" class="profile-pic-form">
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

    <div class="chat-overlay" id="chatOverlay">
      <div class="chat-header">
        <span>AI Chat Assistant</span>
        <button class="close-btn" onclick="closePanel('chatOverlay')">×</button>
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

    <!-- Notification Div -->
    <div id="notification" class="notification">
      <span id="notification-message"></span>
      <button onclick="hideNotification()" class="close-btn">&times;</button>
    </div>

    <!-- Cancel Confirmation Modal -->
    <div id="cancelConfirmationOverlay" class="confirmation-overlay">
      <div class="confirmation-modal">
        <div class="confirmation-modal-text">
          Are you sure you want to <span class="confirm-red">cancel</span>?
        </div>
        <div class="confirmation-modal-sub">
          <span class="italic">All unsaved changes will be lost.</span>
        </div>
        <div class="confirmation-modal-btns">
          <button type="button" class="modal-confirm danger" onclick="confirmCancel()">Yes, Cancel</button>
          <button type="button" class="modal-cancel" onclick="closeCancelModal()">No, Keep Editing</button>
        </div>
      </div>
    </div>

    <!-- Save Confirmation Modal -->
    <div id="saveConfirmationOverlay" class="confirmation-overlay">
      <div class="confirmation-modal">
        <div class="confirmation-modal-text">
          Are you sure you want to <span class="confirm-green">save</span> the new password?
        </div>
        <div class="confirmation-modal-sub">
          <span class="italic">Your password will be permanently changed.</span>
        </div>
        <div class="confirmation-modal-btns">
          <button type="button" class="modal-confirm success" onclick="confirmSave()">Yes, Save</button>
          <button type="button" class="modal-cancel" onclick="closeSaveModal()">No, Keep Editing</button>
        </div>
      </div>
    </div>
</div>

<script src="{{ asset('js/profile.js') }}"></script>
<script>
function showNotification(message, isError = false, isWarning = false) {
  const notif = document.getElementById('notification');
  notif.classList.remove('error', 'warning');
  if (isError) {
    notif.classList.add('error');
  } else if (isWarning) {
    notif.classList.add('warning');
  }
  document.getElementById('notification-message').textContent = message;
  notif.style.display = 'flex';
  setTimeout(hideNotification, 4000);
}
function hideNotification() {
  document.getElementById('notification').style.display = 'none';
}

function togglePanel(panelId) {
  // Close all panels first
  document.getElementById('passwordPanel').classList.remove('open');
  document.getElementById('profilePicPanel').classList.remove('open');
  document.getElementById('chatOverlay').classList.remove('open');
  // Open the requested panel
  document.getElementById(panelId).classList.toggle('open');
}

function closePanel(panelId) {
  document.getElementById(panelId).classList.remove('open');
}

// Variables to store the current panel for modal actions
let currentPanel = null;
let currentForm = null;

function cancelWithConfirmation(panelId) {
  currentPanel = panelId;
  document.getElementById('cancelConfirmationOverlay').classList.add('show');
}

function closeCancelModal() {
  document.getElementById('cancelConfirmationOverlay').classList.remove('show');
  currentPanel = null;
}

function confirmCancel() {
  if (currentPanel) {
    // Clear all text input fields in the panel
    const panel = document.getElementById(currentPanel);
    const textInputs = panel.querySelectorAll('input[type="text"], input[type="password"]');
    textInputs.forEach(input => {
      input.value = '';
    });
    
    // Reset password visibility icons to default state (hidden)
    const eyeIcons = panel.querySelectorAll('.eye-icon');
    eyeIcons.forEach(icon => {
      icon.classList.remove('bx-show');
      icon.classList.add('bx-hide');
      const inputId = icon.parentElement.querySelector('input').id;
      document.getElementById(inputId).type = 'password';
    });
    
    // Close the panel
    closePanel(currentPanel);
    
    // Show notification after a short delay to ensure panel is closed
    setTimeout(function() {
      showNotification('No password changes made.', false, true);
    }, 300);
  }
  
  // Close the modal
  closeCancelModal();
}

function confirmPasswordSave(event) {
  event.preventDefault(); // Prevent default form submission
  currentForm = event.target.closest('form');
  document.getElementById('saveConfirmationOverlay').classList.add('show');
}

function closeSaveModal() {
  document.getElementById('saveConfirmationOverlay').classList.remove('show');
  currentForm = null;
}

function confirmSave() {
  if (currentForm) {
    currentForm.submit();
  }
  closeSaveModal();
}

// Close modals when clicking outside of them
document.addEventListener('DOMContentLoaded', function() {
  const cancelOverlay = document.getElementById('cancelConfirmationOverlay');
  const saveOverlay = document.getElementById('saveConfirmationOverlay');
  
  cancelOverlay.addEventListener('click', function(e) {
    if (e.target === cancelOverlay) {
      closeCancelModal();
    }
  });
  
  saveOverlay.addEventListener('click', function(e) {
    if (e.target === saveOverlay) {
      closeSaveModal();
    }
  });
});

// Profile picture panel file input and preview
const sidePanelInputFile = document.getElementById('sidePanelInputFile');
const sidePanelProfilePic = document.getElementById('sidePanelProfilePic');
const sidePanelSaveBtn = document.getElementById('sidePanelSaveBtn');

if (sidePanelInputFile) {
  sidePanelInputFile.onchange = function() {
    if (sidePanelInputFile.files && sidePanelInputFile.files[0]) {
      sidePanelProfilePic.src = URL.createObjectURL(sidePanelInputFile.files[0]);
      sidePanelSaveBtn.style.display = "inline-block";
    } else {
      sidePanelSaveBtn.style.display = "none";
    }
  };
}

function deleteProfilePicture() {
    if (confirm('Are you sure you want to delete your profile picture?')) {
        fetch("{{ route('profile.deletePicture') }}", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "Accept": "application/json"
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reset the profile picture to default
                document.getElementById('profilePicture').src = "{{ asset('images/profile.png') }}";
                document.getElementById('sidePanelProfilePic').src = "{{ asset('images/profile.png') }}";
                showNotification('Profile picture deleted.');
            } else {
                showNotification('Failed to delete profile picture.', true);
            }
        })
        .catch(() => showNotification('Error deleting profile picture.', true));
    }
}

// === Chatbot ===
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
      }, 300); // Wait for panel to close before showing notification
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
        showNotification('New password and confirmation password do not match.', true);
      @else
        showNotification(@json($errors->first()), true);
      @endif
    });
  </script>
@endif
</body>
</html>

