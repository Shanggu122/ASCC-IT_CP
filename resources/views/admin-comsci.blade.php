<!-- filepath: resources/views/admin-comsci.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Computer Science (Admin)</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/admin-navbar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/admin-comsci.css') }}">
</head>
<body>
  @include('components.navbar-admin')

  <div class="main-content">
    <div class="header">
      <h1>Computer Science DADADAS</h1>
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
             data-sched="{{ $prof->Schedule ? str_replace('\n', '&#10;', $prof->Schedule) : 'No schedule set' }}">
          <img src="{{ $prof->profile_picture ? asset('storage/' . $prof->profile_picture) : asset('images/dprof.jpg') }}" alt="Profile Picture">
          <div class="profile-name">{{ $prof->Name }}</div>
        </div>
      @endforeach
    </div>
  </div>

  <button id="addFacultyBtn" class="add-faculty-btn">+</button>

  <!-- Panel Overlay -->
  <div class="panel-overlay"></div>

  <!-- Add Faculty Side Panel -->
  <form id="addFacultyPanel" class="add-faculty-panel" method="POST" action="{{ route('admin.comsci.professor.add') }}">
    @csrf
    <div class="panel-header">
      <h2>Add Faculty Member</h2>
      <button type="button" id="closeAddFacultyPanel" class="close-panel-btn">&times;</button>
    </div>
    
    <div class="form-content">
      <!-- Left Column -->
      <div class="left-column">
        <div class="input-group">
          <label class="input-label">Faculty ID</label>
          <input type="text" name="Prof_ID" placeholder="Enter faculty ID" required>
        </div>
        <div class="input-group">
          <label class="input-label">Full Name</label>
          <input type="text" name="Name" placeholder="Enter full name" required>
        </div>
        <div class="input-group">
          <label class="input-label">Email Address</label>
          <input type="email" name="Email" placeholder="Enter email address" required>
        </div>
  <input type="hidden" name="Dept_ID" value="2">
        <div class="input-group">
          <label class="input-label">Temporary Password</label>
          <input type="text" name="Password" value="password1" required>
        </div>

        <!-- Subject Assignment -->
        <div class="section-title" style="margin-top: 1rem;">Subject Assignment</div>
        <div class="subject-list">
          @foreach($subjects as $subject)
          <div class="subject-item">
            <input type="checkbox" name="subjects[]" value="{{ $subject->Subject_ID }}" id="subject_{{ $subject->Subject_ID }}">
            <label for="subject_{{ $subject->Subject_ID }}" class="subject-name">{{ $subject->Subject_Name }}</label>
          </div>
          @endforeach
        </div>
      </div>

      <!-- Right Column -->
      <div class="right-column">
        <div class="section-title">Schedule Configuration</div>
        <div class="schedule-info">Set up to 3 different time slots for faculty availability</div>
        <div class="schedule-guide">
          <div class="guide-item">• Select day of the week (Monday-Friday)</div>
          <div class="guide-item">• Choose start time and end time for each slot</div>
          <div class="guide-item">• Example: Monday 9:00 AM to 11:00 AM</div>
        </div>
        
        <div class="schedule-rows">
          <div class="schedule-label">Schedule 1</div>
          <div class="schedule-row">
            <div class="day-selector">
              <label class="field-label">Day</label>
              <select name="day_1" class="schedule-day">
                <option value="">Select day</option>
                <option value="Monday">Monday</option>
                <option value="Tuesday">Tuesday</option>
                <option value="Wednesday">Wednesday</option>
                <option value="Thursday">Thursday</option>
                <option value="Friday">Friday</option>
              </select>
            </div>
            <div class="time-section">
              <div class="time-labels">
                <label class="field-label">Start Time</label>
                <span class="time-label-separator"></span>
                <label class="field-label">End Time</label>
              </div>
              <div class="time-inputs">
                <div class="time-field">
                  <input type="time" name="start_time_1" class="schedule-time">
                </div>
                <span class="time-separator">to</span>
                <div class="time-field">
                  <input type="time" name="end_time_1" class="schedule-time">
                </div>
              </div>
            </div>
          </div>
          
          <div class="schedule-label">Schedule 2</div>
          <div class="schedule-row">
            <div class="day-selector">
              <label class="field-label">Day</label>
              <select name="day_2" class="schedule-day">
                <option value="">Select day</option>
                <option value="Monday">Monday</option>
                <option value="Tuesday">Tuesday</option>
                <option value="Wednesday">Wednesday</option>
                <option value="Thursday">Thursday</option>
                <option value="Friday">Friday</option>
              </select>
            </div>
            <div class="time-section">
              <div class="time-labels">
                <label class="field-label">Start Time</label>
                <span class="time-label-separator"></span>
                <label class="field-label">End Time</label>
              </div>
              <div class="time-inputs">
                <div class="time-field">
                  <input type="time" name="start_time_2" class="schedule-time">
                </div>
                <span class="time-separator">to</span>
                <div class="time-field">
                  <input type="time" name="end_time_2" class="schedule-time">
                </div>
              </div>
            </div>
          </div>
          
          <div class="schedule-label">Schedule 3</div>
          <div class="schedule-row">
            <div class="day-selector">
              <label class="field-label">Day</label>
              <select name="day_3" class="schedule-day">
                <option value="">Select day</option>
                <option value="Monday">Monday</option>
                <option value="Tuesday">Tuesday</option>
                <option value="Wednesday">Wednesday</option>
                <option value="Thursday">Thursday</option>
                <option value="Friday">Friday</option>
              </select>
            </div>
            <div class="time-section">
              <div class="time-labels">
                <label class="field-label">Start Time</label>
                <span class="time-label-separator"></span>
                <label class="field-label">End Time</label>
              </div>
              <div class="time-inputs">
                <div class="time-field">
                  <input type="time" name="start_time_3" class="schedule-time">
                </div>
                <span class="time-separator">to</span>
                <div class="time-field">
                  <input type="time" name="end_time_3" class="schedule-time">
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Panel Actions -->
        <div class="panel-actions">
          <button type="button" class="btn-secondary" onclick="ModalManager.close('addFaculty')">Cancel</button>
          <button type="submit" class="btn-primary">Add Faculty</button>
        </div>
      </div>
    </div>
  </form>

  <!-- Edit Faculty Panel Overlay -->
  <div class="edit-panel-overlay"></div>

  <!-- Edit Faculty Panel -->
  <div id="editFacultyPanel" class="edit-faculty-panel">
    <form id="editFacultyForm" method="POST" action="">
      @csrf
      <div class="panel-header">
        <h2>Edit Faculty Member</h2>
        <button type="button" id="closeEditFacultyPanel" class="close-panel-btn">&times;</button>
      </div>
      
      <div class="form-content">
        <!-- Left Column -->
        <div class="left-column">
          <div class="input-group">
            <label class="input-label">Faculty ID</label>
            <input type="text" name="Prof_ID" id="editProfId" placeholder="Enter faculty ID" required>
          </div>
          <div class="input-group">
            <label class="input-label">Full Name</label>
            <input type="text" name="Name" id="editName" placeholder="Enter full name" required>
          </div>

          <!-- Subject Assignment -->
          <div class="section-title" style="margin-top: 1rem;">Subject Assignment</div>
          <div class="subject-list" id="editSubjectList">
            @foreach($subjects as $subject)
            <div class="subject-item">
              <input type="checkbox" name="subjects[]" value="{{ $subject->Subject_ID }}" id="edit_subject_{{ $subject->Subject_ID }}">
              <label for="edit_subject_{{ $subject->Subject_ID }}" class="subject-name">{{ $subject->Subject_Name }}</label>
            </div>
            @endforeach
          </div>
        </div>

        <!-- Right Column -->
        <div class="right-column">
          <div class="section-title">Schedule Configuration</div>
          <div class="schedule-info">Set up to 3 different time slots for faculty availability</div>
          <div class="schedule-guide">
            <div class="guide-item">• Select day of the week (Monday-Friday)</div>
            <div class="guide-item">• Choose start time and end time for each slot</div>
            <div class="guide-item">• Example: Monday 9:00 AM to 11:00 AM</div>
          </div>
          
          <div class="schedule-rows">
            <div class="schedule-label">Schedule 1</div>
            <div class="schedule-row">
              <div class="day-selector">
                <label class="field-label">Day</label>
                <select name="edit_day_1" class="schedule-day">
                  <option value="">Select day</option>
                  <option value="Monday">Monday</option>
                  <option value="Tuesday">Tuesday</option>
                  <option value="Wednesday">Wednesday</option>
                  <option value="Thursday">Thursday</option>
                  <option value="Friday">Friday</option>
                </select>
              </div>
              <div class="time-section">
                <div class="time-labels">
                  <label class="field-label">Start Time</label>
                  <span class="time-label-separator"></span>
                  <label class="field-label">End Time</label>
                </div>
                <div class="time-inputs">
                  <div class="time-field">
                    <input type="time" name="edit_start_time_1" class="schedule-time">
                  </div>
                  <span class="time-separator">to</span>
                  <div class="time-field">
                    <input type="time" name="edit_end_time_1" class="schedule-time">
                  </div>
                </div>
              </div>
            </div>
            
            <div class="schedule-label">Schedule 2</div>
            <div class="schedule-row">
              <div class="day-selector">
                <label class="field-label">Day</label>
                <select name="edit_day_2" class="schedule-day">
                  <option value="">Select day</option>
                  <option value="Monday">Monday</option>
                  <option value="Tuesday">Tuesday</option>
                  <option value="Wednesday">Wednesday</option>
                  <option value="Thursday">Thursday</option>
                  <option value="Friday">Friday</option>
                </select>
              </div>
              <div class="time-section">
                <div class="time-labels">
                  <label class="field-label">Start Time</label>
                  <span class="time-label-separator"></span>
                  <label class="field-label">End Time</label>
                </div>
                <div class="time-inputs">
                  <div class="time-field">
                    <input type="time" name="edit_start_time_2" class="schedule-time">
                  </div>
                  <span class="time-separator">to</span>
                  <div class="time-field">
                    <input type="time" name="edit_end_time_2" class="schedule-time">
                  </div>
                </div>
              </div>
            </div>
            
            <div class="schedule-label">Schedule 3</div>
            <div class="schedule-row">
              <div class="day-selector">
                <label class="field-label">Day</label>
                <select name="edit_day_3" class="schedule-day">
                  <option value="">Select day</option>
                  <option value="Monday">Monday</option>
                  <option value="Tuesday">Tuesday</option>
                  <option value="Wednesday">Wednesday</option>
                  <option value="Thursday">Thursday</option>
                  <option value="Friday">Friday</option>
                </select>
              </div>
              <div class="time-section">
                <div class="time-labels">
                  <label class="field-label">Start Time</label>
                  <span class="time-label-separator"></span>
                  <label class="field-label">End Time</label>
                </div>
                <div class="time-inputs">
                  <div class="time-field">
                    <input type="time" name="edit_start_time_3" class="schedule-time">
                  </div>
                  <span class="time-separator">to</span>
                  <div class="time-field">
                    <input type="time" name="edit_end_time_3" class="schedule-time">
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Panel Actions -->
          <div class="panel-actions">
            <button type="button" class="btn-secondary" onclick="ModalManager.close('editFaculty')">Cancel</button>
            <button type="button" class="delete-prof-btn-modal btn-danger" style="margin-right: auto;">Delete</button>
            <button type="submit" class="btn-primary">Update Faculty</button>
          </div>
        </div>
      </div>
    </form>
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

  </div>

  <div id="notification" class="notification" style="display:none;">
    <span id="notification-message"></span>
    <button onclick="hideNotification()" class="close-btn" type="button">&times;</button>
  </div>

  <script>
    function showNotification(message, isError = false) {
      const notif = document.getElementById('notification');
      if(!notif) return;
      notif.classList.toggle('error', !!isError);
      document.getElementById('notification-message').textContent = message;
      notif.style.display = 'flex';
      clearTimeout(window.__notifTimer);
      window.__notifTimer = setTimeout(hideNotification, 4000);
    }
    function hideNotification(){
      const notif = document.getElementById('notification');
      if(notif) notif.style.display='none';
    }
    // Modal/Panel Management System
    const ModalManager = {
      activeModal: null,
      
      // Register all modals/panels
      modals: {
        addFaculty: {
          element: null,
          overlay: null,
          triggers: ['addFacultyBtn'],
          closers: ['closeAddFacultyPanel']
        },
        editFaculty: {
          element: null,
          overlay: null,
          triggers: [],
          closers: ['closeEditFacultyPanel']
        },
        deleteConfirm: {
          element: null,
          overlay: null,
          triggers: [],
          closers: ['delete-cancel']
        }
      },
      
      init() {
        // Initialize modal elements
        this.modals.addFaculty.element = document.getElementById('addFacultyPanel');
        this.modals.addFaculty.overlay = document.querySelector('.panel-overlay');
        this.modals.editFaculty.element = document.getElementById('editFacultyPanel');
        this.modals.editFaculty.overlay = document.querySelector('.edit-panel-overlay');
        this.modals.deleteConfirm.element = document.getElementById('deleteOverlay');
        
        // Bind events
        this.bindEvents();
        
        // Handle escape key
        document.addEventListener('keydown', (e) => {
          if (e.key === 'Escape') {
            this.closeAll();
          }
        });
      },
      
      bindEvents() {
        // Bind trigger buttons
        Object.keys(this.modals).forEach(modalKey => {
          const modal = this.modals[modalKey];
          
          // Bind trigger buttons
          modal.triggers.forEach(triggerId => {
            const trigger = document.getElementById(triggerId);
            if (trigger) {
              trigger.addEventListener('click', () => this.open(modalKey));
            }
          });
          
          // Bind close buttons
          modal.closers.forEach(closerId => {
            const closer = document.getElementById(closerId) || document.querySelector(`.${closerId}`);
            if (closer) {
              closer.addEventListener('click', () => this.close(modalKey));
            }
          });
        });
        
        // Bind overlay clicks
        if (this.modals.addFaculty.overlay) {
          this.modals.addFaculty.overlay.addEventListener('click', () => this.close('addFaculty'));
        }
        
        if (this.modals.editFaculty.overlay) {
          this.modals.editFaculty.overlay.addEventListener('click', () => this.close('editFaculty'));
        }
        
        // Bind other modal overlays
        const deleteModal = this.modals.deleteConfirm.element;
        if (deleteModal) {
          deleteModal.addEventListener('click', (e) => {
            if (e.target === deleteModal) this.close('deleteConfirm');
          });
        }
      },
      
      open(modalKey) {
        // Close any currently open modal first (except the one we're opening)
        Object.keys(this.modals).forEach(key => {
          if (key !== modalKey) {
            this.close(key);
          }
        });
        
        const modal = this.modals[modalKey];
        if (!modal || !modal.element) return;
        
        this.activeModal = modalKey;
        
        // Show the modal
        if (modalKey === 'addFaculty') {
          modal.overlay.classList.add('show');
          modal.element.classList.add('show');
          document.body.style.overflow = 'hidden';
          
          // Auto-focus first input
          setTimeout(() => {
            const firstInput = modal.element.querySelector('input');
            if (firstInput) firstInput.focus();
          }, 300);
        } else if (modalKey === 'editFaculty') {
          modal.overlay.classList.add('show');
          modal.element.classList.add('show');
          document.body.style.overflow = 'hidden';
          
          // Auto-focus first input
          setTimeout(() => {
            const firstInput = modal.element.querySelector('input');
            if (firstInput) firstInput.focus();
          }, 300);
        } else {
          modal.element.classList.add('show');
        }
      },
      
      close(modalKey) {
        const modal = this.modals[modalKey];
        if (!modal || !modal.element) return;
        
        if (modalKey === 'addFaculty') {
          modal.overlay.classList.remove('show');
          modal.element.classList.remove('show');
          document.body.style.overflow = 'auto';
          
          // Reset form
          const form = document.getElementById('addFacultyPanel');
          if (form) form.reset();
        } else if (modalKey === 'editFaculty') {
          modal.overlay.classList.remove('show');
          modal.element.classList.remove('show');
          document.body.style.overflow = 'auto';
          
          // Reset form
          const form = document.getElementById('editFacultyForm');
          if (form) form.reset();
        } else {
          modal.element.classList.remove('show');
        }
        
        if (this.activeModal === modalKey) {
          this.activeModal = null;
        }
      },
      
      closeAll() {
        Object.keys(this.modals).forEach(modalKey => {
          this.close(modalKey);
        });
      }
    };

    // Simple search filter for cards
    document.getElementById('searchInput').addEventListener('input', function() {
      const filter = this.value.toLowerCase();
      document.querySelectorAll('.profile-card').forEach(function(card) {
        const name = card.getAttribute('data-name').toLowerCase();
        card.style.display = name.includes(filter) ? '' : 'none';
      });
    });

    // Schedule Management
    let scheduleCount = 1;

    function addScheduleRow() {
        scheduleCount++;
        const scheduleRows = document.querySelector('.schedule-rows');
        const newRow = document.createElement('div');
        newRow.className = 'schedule-row';
        newRow.innerHTML = `
            <div class="day-selector">
                <label class="field-label">Day</label>
                <select class="schedule-day" name="schedule_day[]" required>
                    <option value="">Select Day</option>
                    <option value="Monday">Monday</option>
                    <option value="Tuesday">Tuesday</option>
                    <option value="Wednesday">Wednesday</option>
                    <option value="Thursday">Thursday</option>
                    <option value="Friday">Friday</option>
                    <option value="Saturday">Saturday</option>
                    <option value="Sunday">Sunday</option>
                </select>
            </div>
            <div class="time-inputs">
                <div class="time-field">
                    <label class="field-label">Start Time</label>
                    <input type="time" class="schedule-time" name="schedule_start[]" required>
                </div>
                <span class="time-separator">to</span>
                <div class="time-field">
                    <label class="field-label">End Time</label>
                    <input type="time" class="schedule-time" name="schedule_end[]" required>
                </div>
            </div>
        `;
        scheduleRows.appendChild(newRow);
    }

    // --- Edit Modal Logic ---
    let currentProfId = null;
    let currentProfData = null;
    
    document.querySelectorAll('.profile-card').forEach(function(card) {
      card.onclick = function(e) {
        if (e.target.tagName === 'BUTTON') return;
        const name = card.getAttribute('data-name');
        const profId = card.getAttribute('data-prof-id');
        const sched = card.getAttribute('data-sched');
        currentProfId = profId;

        // Populate the edit form first
        populateEditForm(profId, name, sched);
        
        // Then open modal
        ModalManager.open('editFaculty');
      };
    });

    function populateEditForm(profId, name, schedule) {
      // Set the form action URL
      document.getElementById('editFacultyForm').action = `/admin-comsci/update-professor/${profId}`;
      
      // Populate Faculty ID field
      document.getElementById('editProfId').value = profId;
      
      // Populate name field
      console.log('Populating name field with:', name); // Debug log
      document.getElementById('editName').value = name;
      
      // Clear all schedule fields first
      clearScheduleFields();
      
      // Parse and populate schedule if available
      console.log('Raw schedule data received:', schedule); // Debug log
      if (schedule && schedule !== 'No schedule set' && schedule.trim() !== '') {
        // Handle both encoded newlines and actual newlines
        const formattedSchedule = schedule.replace(/&#10;/g, '\n').replace(/<br>/g, '\n');
        const scheduleLines = formattedSchedule.split('\n').filter(line => line.trim() !== '');
        console.log('Processed schedule lines:', scheduleLines); // Debug log
        
        scheduleLines.forEach((line, index) => {
          if (line.trim() && index < 3) {
            console.log(`Processing schedule line ${index + 1}:`, line); // Debug log
            
            // Parse format: "Day: StartTime-EndTime"
            const colonIndex = line.indexOf(':');
            if (colonIndex !== -1) {
              const day = line.substring(0, colonIndex).trim();
              const timeRange = line.substring(colonIndex + 1).trim();
              const times = timeRange.split('-');
              
              console.log(`Parsed - Day: "${day}", Time range: "${timeRange}", Times:`, times); // Debug log
              
              if (times.length === 2) {
                const startTime = convertTo24Hour(times[0].trim());
                const endTime = convertTo24Hour(times[1].trim());
                
                console.log(`Converted times - Start: ${startTime}, End: ${endTime}`); // Debug log
                
                // Populate the schedule fields
                const scheduleNum = index + 1;
                const daySelect = document.querySelector(`select[name="edit_day_${scheduleNum}"]`);
                const startInput = document.querySelector(`input[name="edit_start_time_${scheduleNum}"]`);
                const endInput = document.querySelector(`input[name="edit_end_time_${scheduleNum}"]`);
                
                if (daySelect && startInput && endInput) {
                  daySelect.value = day;
                  startInput.value = startTime;
                  endInput.value = endTime;
                  console.log(`✓ Set schedule ${scheduleNum}: ${day} ${startTime}-${endTime}`); // Debug log
                } else {
                  console.error(`✗ Could not find schedule fields for schedule ${scheduleNum}`); // Debug log
                }
              } else {
                console.error('Invalid time format:', timeRange);
              }
            } else {
              console.error('Invalid schedule line format:', line);
            }
          }
        });
      } else {
        console.log('No schedule data to populate - schedule value:', schedule); // Debug log
      }
      
      // Load and populate subject assignments
      loadProfessorSubjects(profId);
    }

    function clearScheduleFields() {
      for (let i = 1; i <= 3; i++) {
        document.querySelector(`select[name="edit_day_${i}"]`).value = '';
        document.querySelector(`input[name="edit_start_time_${i}"]`).value = '';
        document.querySelector(`input[name="edit_end_time_${i}"]`).value = '';
      }
      
      // Clear all subject checkboxes
      document.querySelectorAll('#editSubjectList input[type="checkbox"]').forEach(checkbox => {
        checkbox.checked = false;
      });
    }

    function convertTo24Hour(time12h) {
      try {
        const [time, modifier] = time12h.split(' ');
        let [hours, minutes] = time.split(':');
        
        hours = parseInt(hours, 10);
        minutes = minutes || '00';
        
        if (modifier === 'AM') {
          if (hours === 12) {
            hours = 0;
          }
        } else if (modifier === 'PM') {
          if (hours !== 12) {
            hours = hours + 12;
          }
        }
        
        return `${hours.toString().padStart(2, '0')}:${minutes}`;
      } catch (error) {
        console.error('Error converting time:', time12h, error);
        return '00:00';
      }
    }

    function loadProfessorSubjects(profId) {
      // Fetch professor's current subject assignments
      fetch(`/admin-comsci/professor-subjects/${profId}`, {
        method: 'GET',
        headers: {
          'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Check the appropriate subject checkboxes
          data.subjects.forEach(subjectId => {
            const checkbox = document.querySelector(`#edit_subject_${subjectId}`);
            if (checkbox) {
              checkbox.checked = true;
            }
          });
        }
      })
      .catch(error => {
        console.log('Could not load professor subjects:', error);
      });
    }

    // --- Delete from Edit Modal ---
    document.querySelector('.delete-prof-btn-modal').onclick = function() {
      ModalManager.close('editFaculty');
      showDeleteModal(currentProfId);
    };

    // --- Delete Modal Logic ---
    function showDeleteModal(profId) {
      var form = document.getElementById('deleteForm');
      form.action = '/admin-comsci/delete-professor/' + profId;
      ModalManager.open('deleteConfirm');
    }

  // Removed legacy addFacultyPanel submit listener (handled by enhanceAddProfessorForm)

    // Handle edit form submission
    document.getElementById('editFacultyForm').addEventListener('submit', function(e) {
      e.preventDefault();
      
      const formData = new FormData(this);
      
      // Validate required fields
      const requiredFields = ['Prof_ID', 'Name'];
      let isValid = true;
      
      requiredFields.forEach(field => {
        const input = this.querySelector(`[name="${field}"]`);
        if (!input || !input.value.trim()) {
          isValid = false;
          input?.focus();
          return;
        }
      });
      
      if (!isValid) {
        alert('Please fill in all required fields');
        return;
      }
      
      // Process schedule data
      const day1 = formData.get('edit_day_1');
      const startTime1 = formData.get('edit_start_time_1');
      const endTime1 = formData.get('edit_end_time_1');
      
      const day2 = formData.get('edit_day_2');
      const startTime2 = formData.get('edit_start_time_2');
      const endTime2 = formData.get('edit_end_time_2');
      
      const day3 = formData.get('edit_day_3');
      const startTime3 = formData.get('edit_start_time_3');
      const endTime3 = formData.get('edit_end_time_3');
      
      let scheduleData = [];
      const schedules = [
        {day: day1, start: startTime1, end: endTime1},
        {day: day2, start: startTime2, end: endTime2},
        {day: day3, start: startTime3, end: endTime3}
      ];
      
      for (let schedule of schedules) {
        if (schedule.day && schedule.start && schedule.end) {
          // Check if end time is after start time
          const startMinutes = parseInt(schedule.start.split(':')[0]) * 60 + parseInt(schedule.start.split(':')[1]);
          const endMinutes = parseInt(schedule.end.split(':')[0]) * 60 + parseInt(schedule.end.split(':')[1]);
          
          if (endMinutes <= startMinutes) {
            alert(`End time must be after start time for ${schedule.day}`);
            return;
          }
          
          // Convert to 12-hour format for display
          const formatTime = (time) => {
            const [hour, minute] = time.split(':');
            const hourNum = parseInt(hour);
            const ampm = hourNum >= 12 ? 'PM' : 'AM';
            const displayHour = hourNum > 12 ? hourNum - 12 : (hourNum === 0 ? 12 : hourNum);
            return `${displayHour}:${minute} ${ampm}`;
          };
          
          scheduleData.push(`${schedule.day}: ${formatTime(schedule.start)}-${formatTime(schedule.end)}`);
        }
      }
      
      // Add formatted schedule to form if any schedule data exists
      if (scheduleData.length > 0) {
        const scheduleInput = document.createElement('input');
        scheduleInput.type = 'hidden';
        scheduleInput.name = 'Schedule';
        scheduleInput.value = scheduleData.join('\n');
        this.appendChild(scheduleInput);
        
        // Debug log
        console.log('Schedule being sent:', scheduleData.join('\n'));
      }
      
      // Submit the form via fetch to handle the response
      fetch(this.action, {
        method: 'POST',
        body: new FormData(this),
        headers: {
          'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
      })
      .then(response => {
        console.log('Response status:', response.status); // Debug log
        return response.json();
      })
      .then(data => {
        console.log('Response data:', data); // Debug log
        if (data.success) {
          alert('Professor updated successfully!');
          // Update card locally (real-time event also handles others)
          const profId = document.getElementById('editProfId').value;
          const card = document.querySelector(`[data-prof-id="${profId}"]`);
          if(card){
            const newName = document.getElementById('editName').value;
            card.dataset.name = newName;
            if(card.querySelector('.profile-name')) card.querySelector('.profile-name').textContent = newName;
            // Schedule updated via event; keep local dataset for instant UX
          }
          ModalManager.close('editFaculty');
        } else {
          alert('Error updating professor: ' + (data.message || 'Unknown error'));
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Error updating professor');
      });
    });

    // Initialize Modal Manager when page loads
    document.addEventListener('DOMContentLoaded', function() {
      ModalManager.init();
      initRealtimeAdminComsci();
      enhanceAddProfessorForm();
    });

    // Use fetch for add professor to prevent full reload (server event will update others)
    function enhanceAddProfessorForm(){
      const form = document.getElementById('addFacultyPanel');
      if(!form) return;
      form.addEventListener('submit', function(ev){
        // If schedule hidden input already appended by earlier handler, allow fetch else will be appended there.
        if(ev.defaultPrevented) return; // already custom-handled
      });
      // Intercept native submit (after schedule injection) using capturing
      form.addEventListener('submit', function(e){
        if(form.dataset.ajaxDone) return; // avoid double
        e.preventDefault();
        const fd = new FormData(form);
        fetch(form.action, {method:'POST', body: fd, headers:{
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content,
            'Accept':'application/json',
            'X-Requested-With':'XMLHttpRequest'
        }})
          .then(async r=>{ try{ return await r.json(); } catch(e){ showNotification('Unexpected server response', true); throw e; } })
          .then(data=>{
            if(data.success){
              showNotification('Professor added successfully');
              form.reset();
              ModalManager.close('addFaculty');
              if(data.professor){ addOrUpdateCard(data.professor); }
            } else {
              let msg = data.message || 'Failed to add professor';
              if(data.errors){
                try {
                  const all = Object.values(data.errors).flat();
                  if(all.length) msg = all.join(' • ');
                } catch(_) { /* ignore */ }
              }
              showNotification(msg, true);
            }
          })
          .catch(err=>{ console.error(err); showNotification('Request failed: '+(err&&err.message?err.message:'Unexpected error'), true); });
      }, true);
    }

    function addOrUpdateCard(p){
      const grid = document.querySelector('.profile-cards-grid');
      if(!grid) return; const existing = grid.querySelector(`[data-prof-id="${p.Prof_ID}"]`);
  const imgPath = p.profile_picture ? ('{{ url('/storage') }}/'+p.profile_picture) : '{{ asset("images/dprof.jpg") }}';
      if(existing){
        existing.dataset.name = p.Name;
        existing.dataset.sched = p.Schedule || 'No schedule set';
        existing.dataset.img = imgPath;
        existing.querySelector('.profile-name').textContent = p.Name;
        const imgEl= existing.querySelector('img'); if(imgEl) imgEl.src=imgPath;
        bindCardEvents(existing);
        return;
      }
      const div = document.createElement('div');
      div.className='profile-card';
      div.dataset.name=p.Name; div.dataset.img=imgPath; div.dataset.profId=p.Prof_ID; div.setAttribute('data-prof-id',p.Prof_ID); div.dataset.sched=p.Schedule||'No schedule set';
      bindCardEvents(div);
      div.innerHTML=`<img src="${imgPath}" alt="Profile Picture"><div class='profile-name'>${p.Name}</div>`;
      // Insert alphabetically by name for consistency with server ordering
      const cards = Array.from(grid.querySelectorAll('.profile-card'));
      const newName = p.Name.toLowerCase();
      let inserted = false;
      for(const c of cards){
        const cname = (c.getAttribute('data-name')||'').toLowerCase();
        if(newName < cname){
          grid.insertBefore(div, c);
          inserted = true;
          break;
        }
      }
      if(!inserted) grid.appendChild(div);
    }

    function bindCardEvents(card){
      card.onclick = function(e){
        if (e.target.tagName === 'BUTTON') return;
        const profId = card.getAttribute('data-prof-id');
        const name = card.getAttribute('data-name');
        const sched = card.getAttribute('data-sched');
        currentProfId = profId;
        populateEditForm(profId, name, sched);
        ModalManager.open('editFaculty');
      };
    }
    function openEditPanelFromCard(id){ /* kept for compatibility */ }

    function initRealtimeAdminComsci(){
      const script = document.createElement('script'); script.src='https://js.pusher.com/7.0/pusher.min.js'; script.onload=subscribe; document.body.appendChild(script);
      function subscribe(){
        const pusher = new Pusher('{{ config('broadcasting.connections.pusher.key') }}',{cluster:'{{ config('broadcasting.connections.pusher.options.cluster') }}'});
        const channel = pusher.subscribe('professors.dept.2');
        channel.bind('ProfessorAdded', data=> addOrUpdateCard(data));
        channel.bind('ProfessorUpdated', data=> addOrUpdateCard(data));
        channel.bind('ProfessorDeleted', data=> {
          const card = document.querySelector(`[data-prof-id="${data.Prof_ID}"]`); if(card) card.remove();
          showNotification('Professor deleted successfully');
        });
      }
    }

    // Intercept delete form submit to show notice immediately without full reload
    document.addEventListener('submit', function(e){
      const form = e.target;
      if(form && form.id === 'deleteForm'){
        e.preventDefault();
        fetch(form.action, {method:'POST', headers:{'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content,'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}, body: new FormData(form)})
          .then(r=> r.ok ? r.json().catch(()=>({success:true})) : Promise.reject(r))
          .then(()=>{ 
            // Optimistic local removal
            const id = form.action.split('/').pop();
            const card = document.querySelector(`[data-prof-id="${id}"]`);
            if(card) card.remove();
            ModalManager.close('deleteConfirm'); showNotification('Professor deleted successfully');
          })
          .catch(()=>{ ModalManager.close('deleteConfirm'); showNotification('Deletion failed', true); });
      }
    }, true);
  </script>
</body>
</html>