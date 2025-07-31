<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="{{ asset('css/profile-professor.css') }}">
</head>
<body>
  @include('components.navbarprof')

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
            <i class='bx bx-edit-alt edit-icon'></i>
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
      <div class="chat-input">
        <input type="text" id="userInput" placeholder="Type your message..." onkeydown="handleKey(event)">
        <button onclick="sendMessage()">Send</button>
      </div>
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
            <i class='bx bx-show eye-icon' onclick="togglePasswordVisibility('oldPassword', this)"></i>
          </div>
          <!-- New Password -->
          <label for="newPassword">New Password</label>
          <div class="password-field">
            <input type="password" id="newPassword" name="newPassword" placeholder="Enter new password" required>
            <i class='bx bx-show eye-icon' onclick="togglePasswordVisibility('newPassword', this)"></i>
          </div>
          <!-- Confirm New Password -->
          <label for="newPassword_confirmation">Confirm New Password</label>
          <div class="password-field">
            <input type="password" id="newPassword_confirmation" name="newPassword_confirmation" placeholder="Confirm new password" required>
            <i class='bx bx-show eye-icon' onclick="togglePasswordVisibility('newPassword_confirmation', this)"></i>
          </div>
          <div class="panel-footer">
            <button type="button" class="cancel-btn" onclick="closePanel('passwordPanel')">Cancel</button>
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
               src="{{ $user->profile_picture ? asset('storage/' . $user->profile_picture) : asset('images/dprof.jpg') }}"
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
    if (confirm('Are you sure you want to delete your profile picture?')) {
        fetch("{{ route('profile.deletePicture.professor') }}", {
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
        }, 300);
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
</html>