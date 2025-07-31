<!-- filepath: resources/views/admin-itis.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Information Technology and Information System (Admin)</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/admin-itis.css') }}">
</head>
<body>
  @include('components.navbar-admin')

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
             data-name="{{ $prof->Name }}"
             data-img="{{ $prof->profile_picture ? asset('storage/' . $prof->profile_picture) : asset('images/dprof.jpg') }}"
             data-prof-id="{{ $prof->Prof_ID }}"
             data-sched="Tuesday: 10:00-11:00&#10;Wednesday: 17:00-18:00&#10;Thursday: 17:00-18:00">
          <img src="{{ $prof->profile_picture ? asset('storage/' . $prof->profile_picture) : asset('images/dprof.jpg') }}" alt="Profile Picture">
          <div class="profile-name">{{ $prof->Name }}</div>
        </div>
      @endforeach
    </div>
  </div>

  <button id="addFacultyBtn" class="add-faculty-btn">+</button>

  <!-- Right-side Edit Faculty Panel -->
  <div id="editFacultyPanel" class="edit-faculty-panel">
    <button type="button" id="closeEditFacultyPanel" class="close-edit-faculty-panel">&#10005;</button>
    <div class="edit-faculty-panel-content">
      <img id="editProfilePic" src="" alt="Profile Picture" class="edit-profile-pic">
      <div class="edit-profile-info">
        <div id="editProfileNameWrapper">
          <span id="editProfileName" class="edit-profile-name"></span>
          <input type="text" id="editProfileNameInput" class="edit-profile-input" style="display:none;" />
        </div>
        <div id="editProfileSched" class="edit-profile-sched"></div>
        <div id="editProfileActions" style="display:none; margin-top:1rem;">
          <button type="button" id="saveEditBtn" class="edit-save-btn">Save</button>
          <button type="button" id="cancelEditBtn" class="edit-cancel-btn">Cancel</button>
        </div>
        <div class="edit-delete-btns">
          <button type="button" class="edit-prof-btn-modal">Edit</button>
          <button type="button" class="delete-prof-btn-modal">Delete</button>
        </div>
        <button type="button" class="assign-subjects-btn-modal">Assign Subjects</button>
      </div>
    </div>
  </div>

  <!-- Delete Professor Overlay -->
  <div id="deleteOverlay">
    <div class="delete-modal">
      <div class="delete-modal-text">
        Are you sure you want to <span class="delete-red">delete</span> this faculty member from the department?
      </div>
      <div class="delete-modal-sub">
        <span class="italic">
          All associated records and data may be permanently removed.
        </span>
      </div>
      <form id="deleteForm" method="POST" action="">
        @csrf
        @method('DELETE')
        <div class="delete-modal-btns">
          <button type="submit" class="delete-confirm">Confirm</button>
          <button type="button" class="delete-cancel">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Assign Subjects Modal -->
  <div id="assignSubjectsModal" class="assign-subjects-modal">
    <form id="assignSubjectsForm" method="POST" action="{{ route('admin.professor.assignSubjects') }}">
      @csrf
      <input type="hidden" name="Prof_ID" id="assignProfId">
      <h2>Assign Subjects</h2>
      <div class="assign-subjects-list">
        @foreach($subjects as $subject)
          <label>
            <input type="checkbox" name="subjects[]" value="{{ $subject->Subject_ID }}">
            {{ $subject->Subject_Name }}
          </label>
        @endforeach
      </div>
      <button type="submit" class="assign-subjects-save">Save</button>
      <button type="button" id="closeAssignSubjectsModal" class="assign-subjects-cancel">Cancel</button>
    </form>
  </div>

  <!-- Add Faculty Modal -->
  <div id="addFacultyModal">
    <form id="addFacultyForm" method="POST" action="{{ route('admin.professor.add') }}">
      @csrf
      <h2>Add Faculty Member</h2>
      <input type="text" name="Prof_ID" placeholder="Faculty ID" required>
      <input type="text" name="Name" placeholder="Full Name" required>
      <input type="email" name="Email" placeholder="Email" required>
      <input type="hidden" name="Dept_ID" value="1">
      <input type="text" name="Password" placeholder="Dummy Password" value="password1" required>

      <!-- Schedule input -->
      <textarea name="Schedule" class="add-faculty-sched" placeholder="Schedule (e.g. Tuesday: 10:00-11:00&#10;Wednesday: 17:00-18:00)" rows="3"></textarea>

      <!-- Assign Subjects (checkboxes) -->
      <div class="add-faculty-subjects">
          <label class="add-faculty-label">Assign Subjects:</label>
          <div class="add-faculty-subject-list">
              @foreach($subjects as $subject)
                  <label>
                      <input type="checkbox" name="subjects[]" value="{{ $subject->Subject_ID }}">
                      {{ $subject->Subject_Name }}
                  </label>
              @endforeach
          </div>
      </div>

      <button type="submit" class="add-faculty-save">Add</button>
      <button type="button" id="closeAddFacultyModal" class="add-faculty-cancel">Cancel</button>
    </form>
  </div>

  <script>
    // Simple search filter for cards
    document.getElementById('searchInput').addEventListener('input', function() {
      const filter = this.value.toLowerCase();
      document.querySelectorAll('.profile-card').forEach(function(card) {
        const name = card.getAttribute('data-name').toLowerCase();
        card.style.display = name.includes(filter) ? '' : 'none';
      });
    });

    // Show Add Faculty Modal
    document.getElementById('addFacultyBtn').onclick = function() {
        document.getElementById('addFacultyModal').style.display = 'flex';
    };
    // Hide Add Faculty Modal
    document.getElementById('closeAddFacultyModal').onclick = function() {
        document.getElementById('addFacultyModal').style.display = 'none';
    };
    // Hide modal when clicking outside the form
    document.getElementById('addFacultyModal').onclick = function(e) {
        if (e.target === this) this.style.display = 'none';
    };

    // --- Edit Modal Logic ---
    let currentProfId = null;
    document.querySelectorAll('.profile-card').forEach(function(card) {
      card.onclick = function(e) {
        if (e.target.tagName === 'BUTTON') return;
        const name = card.getAttribute('data-name');
        const img = card.getAttribute('data-img');
        const profId = card.getAttribute('data-prof-id');
        const sched = card.getAttribute('data-sched');
        currentProfId = profId;

        document.getElementById('editProfilePic').src = img;
        document.getElementById('editProfileName').textContent = name;
        document.getElementById('editProfileSched').textContent = sched;

        document.getElementById('editFacultyPanel').classList.add('show');
      };
    });
    document.getElementById('closeEditFacultyPanel').onclick = function() {
      document.getElementById('editFacultyPanel').classList.remove('show');
    };

    // --- Assign Subjects from Edit Modal ---
    document.querySelector('.assign-subjects-btn-modal').onclick = function() {
      document.getElementById('editFacultyPanel').classList.remove('show');
      document.getElementById('assignProfId').value = currentProfId;
      document.getElementById('assignSubjectsModal').classList.add('show');
    };

    // --- Delete from Edit Modal ---
    document.querySelector('.delete-prof-btn-modal').onclick = function() {
      document.getElementById('editFacultyPanel').classList.remove('show');
      showDeleteModal(currentProfId);
    };

    // Assign Subjects Modal logic
    document.getElementById('closeAssignSubjectsModal').onclick = function() {
      document.getElementById('assignSubjectsModal').classList.remove('show');
    };
    document.getElementById('assignSubjectsModal').onclick = function(e) {
      if (e.target === this) this.classList.remove('show');
    };

    // --- Delete Modal Logic ---
    function showDeleteModal(profId) {
      var form = document.getElementById('deleteForm');
      form.action = '/admin-itis/delete-professor/' + profId;
      document.getElementById('deleteOverlay').classList.add('show');
    }
    document.querySelector('.delete-cancel').onclick = function() {
      document.getElementById('deleteOverlay').classList.remove('show');
    };
    document.getElementById('deleteOverlay').onclick = function(e) {
      if (e.target === this) this.classList.remove('show');
    };

    // --- Edit Professor Form Logic ---
    document.querySelector('.edit-prof-btn-modal').onclick = function() {
      document.getElementById('editProfileNameInput').value = document.getElementById('editProfileName').textContent;
      document.getElementById('editProfileName').style.display = 'none';
      document.getElementById('editProfileNameInput').style.display = '';
      document.getElementById('editProfileActions').style.display = '';
    };

    // Cancel inline edit
    document.getElementById('cancelEditBtn').onclick = function() {
      document.getElementById('editProfileName').style.display = '';
      document.getElementById('editProfileNameInput').style.display = 'none';
      document.getElementById('editProfileActions').style.display = 'none';
    };

    // Save inline edit
    document.getElementById('saveEditBtn').onclick = function() {
      const name = document.getElementById('editProfileNameInput').value;
      const formData = new FormData();
      formData.append('Name', name);
      formData.append('Prof_ID', currentProfId);

      fetch('/admin-itis/update-professor/' + currentProfId, {
        method: 'POST',
        body: formData,
        headers: {
          'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          document.getElementById('editProfileName').textContent = name;
          const card = Array.from(document.querySelectorAll('.profile-card')).find(c => c.getAttribute('data-prof-id') === currentProfId);
          card.setAttribute('data-name', name);
          card.querySelector('.profile-name').textContent = name;
          document.getElementById('editProfileName').style.display = '';
          document.getElementById('editProfileNameInput').style.display = 'none';
          document.getElementById('editProfileActions').style.display = 'none';
        } else {
          alert('Error updating professor data');
        }
      })
      .catch(error => console.error('Error:', error));
    };

    // Close Add Faculty Modal
    document.getElementById('closeAddFacultyModal').onclick = function() {
      document.getElementById('addFacultyModal').style.display = 'none';
    };
    document.getElementById('addFacultyModal').onclick = function(e) {
      if (e.target === this) this.classList.remove('show');
    };
  </script>
</body>
</html>