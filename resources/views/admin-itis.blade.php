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
  <link rel="stylesheet" href="{{ asset('css/admin-navbar.css') }}">
  <link rel="stylesheet" href="{{ asset('css/admin-itis.css') }}">
</head>
<body>
  @include('components.navbar-admin')

  <!-- Global full-screen loading overlay (reuses login styles) -->
  <div id="globalLoading" class="auth-loading-overlay" aria-hidden="true">
    <div class="auth-loading-spinner" role="status" aria-live="polite"></div>
    <div class="auth-loading-text">Please wait…</div>
  </div>

  <div class="main-content">
    <div class="header">
      <h1>Information Technology and Information System Faculty</h1>
    </div>

    <div class="search-container">
  <input type="text" id="searchInput" placeholder="Search..." autocomplete="off" spellcheck="false" maxlength="50" pattern="[A-Za-z0-9 ]{0,50}" aria-label="Search professors" oninput="this.value=this.value.replace(/[^A-Za-z0-9 ]/g,'')">
    </div>

    <div class="profile-cards-grid">
      @foreach($professors as $prof)
        @php
          $photoUrl = isset($prof->profile_photo_url)
              ? $prof->profile_photo_url
              : ($prof->profile_picture ? asset('storage/' . $prof->profile_picture) : asset('images/dprof.jpg'));
        @endphp
        <div class="profile-card"
             data-name="{{ $prof->Name }}"
             data-img="{{ $photoUrl }}"
             data-prof-id="{{ $prof->Prof_ID }}"
             data-sched="{{ $prof->Schedule ? str_replace('\n', '&#10;', $prof->Schedule) : 'No schedule set' }}">
          <img src="{{ $photoUrl }}" alt="Profile Picture">
          <div class="profile-name">{{ $prof->Name }}</div>
        </div>
      @endforeach
    </div>
  </div>

  <button id="addChooserBtn" class="add-fab">+</button>
  <!-- Centered chooser modal for Add options -->
  <div id="addChooserModal" class="mini-modal" aria-hidden="true">
    <div class="mini-modal-card" role="dialog" aria-modal="true" aria-labelledby="chooserTitle">
      <div class="mini-modal-header">
  <div class="mini-modal-title" id="chooserTitle">Add</div>
        <button type="button" class="mini-modal-close" data-close-chooser>&times;</button>
      </div>
      <div class="mini-modal-body">
        <div class="chooser-options">
          <button type="button" class="chooser-option" data-open-add="faculty">Add Faculty</button>
          <button type="button" class="chooser-option" data-open-add="student">Add Student</button>
        </div>
      </div>
      <div class="mini-modal-actions">
        <button type="button" class="btn-secondary" data-close-chooser>Close</button>
      </div>
    </div>
  </div>

  <!-- Panel Overlay -->
  <div class="panel-overlay"></div>

  <!-- Add Faculty Side Panel -->
  <form id="addFacultyPanel" class="add-faculty-panel" method="POST" action="{{ route('admin.itis.professor.add') }}">
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
          <input type="text" name="Prof_ID" placeholder="Enter faculty ID" required inputmode="numeric" maxlength="9" pattern="\d{1,9}" oninput="this.value=this.value.replace(/\D/g,'').slice(0,9)">
        </div>
        <div class="input-group">
          <label class="input-label">Full Name</label>
          <input type="text" name="Name" placeholder="Enter full name" required maxlength="50">
        </div>
        <div class="input-group">
          <label class="input-label">Email Address</label>
          <input type="email" name="Email" placeholder="Enter email address" required maxlength="100">
        </div>
        <input type="hidden" name="Dept_ID" value="1">
        <div class="input-group">
          <label class="input-label">Temporary Password</label>
          <div class="password-row" style="display:flex; gap:8px; align-items:center;">
            <input type="text" id="addTempPassword" name="Password" value="password1" required style="flex:1;">
            <button type="button" id="btnGenTempPw" class="btn-secondary" style="white-space:nowrap; padding:4px 8px; font-size:12px; line-height:1;">Generate</button>
          </div>
        </div>

        <!-- Subject Assignment -->
        <div class="section-title" style="margin-top: 1rem;">Subject Assignment</div>
        <div class="subject-list">
          @foreach($subjects as $subj)
          <div class="subject-item">
            @php
              $sid = is_object($subj) ? ($subj->Subject_ID ?? '') : (is_array($subj) ? ($subj['Subject_ID'] ?? '') : '');
              $sname = is_object($subj) ? ($subj->Subject_Name ?? '') : (is_array($subj) ? ($subj['Subject_Name'] ?? '') : '');
            @endphp
            <input type="checkbox" name="subjects[]" value="{{ $sid }}" id="subject_{{ $sid }}">
            <label for="subject_{{ $sid }}" class="subject-name">{{ $sname }}</label>
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
          <div class="schedule-label">Schedule 1 <button type="button" class="schedule-clear-btn" data-scope="add" data-index="1">Remove</button></div>
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
          
          <div class="schedule-label">Schedule 2 <button type="button" class="schedule-clear-btn" data-scope="add" data-index="2">Remove</button></div>
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
          
          <div class="schedule-label">Schedule 3 <button type="button" class="schedule-clear-btn" data-scope="add" data-index="3">Remove</button></div>
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

  <!-- Add Student Side Panel (compact) -->
  <form id="addStudentPanel" class="add-student-panel" method="POST" action="{{ route('admin.itis.student.add') }}">
    @csrf
    <div class="panel-header">
      <h2>Add Student</h2>
      <button type="button" id="closeAddStudentPanel" class="close-panel-btn">&times;</button>
    </div>
    <div class="form-content">
      <div class="left-column">
        <div class="input-group">
          <label class="input-label">Student ID</label>
          <input type="text" name="Stud_ID" placeholder="Enter student ID" required inputmode="numeric" maxlength="9" pattern="\d{1,9}" oninput="this.value=this.value.replace(/\D/g,'').slice(0,9)">
        </div>
        <div class="input-group">
          <label class="input-label">Full Name</label>
          <input type="text" name="Name" placeholder="Enter full name" required maxlength="50">
        </div>
        <div class="input-group">
          <label class="input-label">Email Address</label>
          <input type="email" name="Email" placeholder="Enter email address" required maxlength="100">
        </div>
        <input type="hidden" name="Dept_ID" value="1">
        <div class="input-group">
          <label class="input-label">Temporary Password</label>
          <div class="password-row" style="display:flex; gap:8px; align-items:center;">
            <input type="text" id="addStudentTempPasswordItis" name="Password" value="password1" required style="flex:1;">
            <button type="button" id="btnGenTempPwStudentItis" class="btn-secondary" style="white-space:nowrap; padding:4px 8px; font-size:12px; line-height:1;">Generate</button>
          </div>
        </div>
      </div>
      <div class="right-column"></div>
    </div>
    <div class="panel-actions">
      <button type="button" class="btn-secondary" onclick="ModalManager.close('addStudent')">Cancel</button>
      <button type="submit" class="btn-primary">Add Student</button>
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
            <input type="text" name="Prof_ID" id="editProfId" placeholder="Enter faculty ID" required inputmode="numeric" maxlength="9" pattern="\d{1,9}" oninput="this.value=this.value.replace(/\D/g,'').slice(0,9)">
          </div>
          <div class="input-group">
            <label class="input-label">Full Name</label>
            <input type="text" name="Name" id="editName" placeholder="Enter full name" required maxlength="50">
          </div>

          <!-- Subject Assignment -->
          <div class="section-title" style="margin-top: 1rem;">Subject Assignment</div>
          <div class="subject-list" id="editSubjectList">
            @foreach($subjects as $subj)
            @php
              $sid = is_object($subj) ? ($subj->Subject_ID ?? '') : (is_array($subj) ? ($subj['Subject_ID'] ?? '') : '');
              $sname = is_object($subj) ? ($subj->Subject_Name ?? '') : (is_array($subj) ? ($subj['Subject_Name'] ?? '') : '');
            @endphp
            <div class="subject-item">
              <input type="checkbox" name="subjects[]" value="{{ $sid }}" id="edit_subject_{{ $sid }}">
              <label for="edit_subject_{{ $sid }}" class="subject-name">{{ $sname }}</label>
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
            <div class="schedule-label">Schedule 1 <button type="button" class="schedule-clear-btn" data-scope="edit" data-index="1">Remove</button></div>
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
            
            <div class="schedule-label">Schedule 2 <button type="button" class="schedule-clear-btn" data-scope="edit" data-index="2">Remove</button></div>
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
            
            <div class="schedule-label">Schedule 3 <button type="button" class="schedule-clear-btn" data-scope="edit" data-index="3">Remove</button></div>
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
    // Generate a random, readable password (12 chars: A-Z a-z 0-9 + symbols)
    function generatePassword(len=12){
      const upper = 'ABCDEFGHJKLMNPQRSTUVWXYZ'; // exclude I/O
      const lower = 'abcdefghijkmnopqrstuvwxyz'; // exclude l
      const digits = '23456789'; // exclude 0/1
      const all = upper + lower + digits;
      // Ensure at least one of each class (no symbols)
      let out = [
        upper[Math.floor(Math.random()*upper.length)],
        lower[Math.floor(Math.random()*lower.length)],
        digits[Math.floor(Math.random()*digits.length)]
      ];
      while(out.length < len){ out.push(all[Math.floor(Math.random()*all.length)]); }
      // Shuffle
      for(let i=out.length-1;i>0;i--){ const j=Math.floor(Math.random()*(i+1)); [out[i],out[j]]=[out[j],out[i]]; }
      return out.join('');
    }
    (function bindGenPw(){
      const btn = document.getElementById('btnGenTempPw');
      const input = document.getElementById('addTempPassword');
      if(!btn||!input) return;
      btn.addEventListener('click', ()=>{ input.value = generatePassword(12); input.dispatchEvent(new Event('input',{bubbles:true})); });
    })();
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
    // --- Anti-spam helpers (prevent rapid double clicks/submits) ---
    function guardRapidClicks(selector, holdMs = 1000){
      document.addEventListener('click', function(e){
        const btn = e.target && e.target.closest ? e.target.closest(selector) : null;
        if(!btn) return;
        if(btn.dataset.clickLocked === '1'){
          e.preventDefault();
          e.stopPropagation();
          return;
        }
        btn.dataset.clickLocked = '1';
        if(typeof btn.disabled !== 'undefined') btn.disabled = true;
        setTimeout(()=>{ if(btn){ btn.dataset.clickLocked='0'; if(typeof btn.disabled !== 'undefined') btn.disabled = false; } }, holdMs);
      }, true);
    }
    function lockSubmitButton(formEl){
      const submitBtn = formEl.querySelector('.panel-actions .btn-primary[type="submit"], .panel-actions .btn-primary');
      if(submitBtn){
        submitBtn.disabled = true;
        submitBtn.setAttribute('aria-busy','true');
      }
      return submitBtn;
    }
    function unlockSubmitButton(btn){
      if(!btn) return;
      btn.disabled = false;
      btn.removeAttribute('aria-busy');
    }
    // Modal/Panel Management System
    const ModalManager = {
      activeModal: null,
      
      // Register all modals/panels
      modals: {
        addFaculty: {
          element: null,
          overlay: null,
          triggers: [],
          closers: ['closeAddFacultyPanel']
        },
        addStudent: {
          element: null,
          overlay: null,
          triggers: [],
          closers: []
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
    // New: Add Student is a side panel reusing the main overlay
    this.modals.addStudent.element = document.getElementById('addStudentPanel');
    this.modals.addStudent.overlay = document.querySelector('.panel-overlay');
    this.modals.addStudent.closers = ['closeAddStudentPanel'];
        
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
          this.modals.addFaculty.overlay.addEventListener('click', () => {
            this.close('addFaculty');
            this.close('addStudent');
          });
        }
        
        if (this.modals.editFaculty.overlay) {
          this.modals.editFaculty.overlay.addEventListener('click', () => this.close('editFaculty'));
        }

        // Add student panel close button already bound via closers list
        
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
        } else if (modalKey === 'addStudent') {
          // Side panel behavior like addFaculty
          if(modal.overlay) modal.overlay.classList.add('show');
          modal.element.classList.add('show');
          document.body.style.overflow = 'hidden';
          setTimeout(()=>{ const first = modal.element.querySelector('input'); if(first) first.focus(); }, 100);
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
        } else if (modalKey === 'addStudent') {
          // Side panel behavior
          if(modal.overlay) modal.overlay.classList.remove('show');
          modal.element.classList.remove('show');
          document.body.style.overflow = 'auto';
          const form = document.getElementById('addStudentPanel');
          if(form) form.reset();
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

    // Simple search filter for cards with basic sanitization
    function sanitize(input){
      if(!input) return '';
      // Keep letters, numbers, and spaces only (preserve spaces, just collapse runs)
      let cleaned = input.replace(/[^A-Za-z0-9 ]/g, '');
      cleaned = cleaned.replace(/\s{2,}/g,' ');
      return cleaned.slice(0,50);
    }
    document.getElementById('searchInput').addEventListener('input', function() {
      const raw = this.value;
      const cleaned = sanitize(raw);
      if(cleaned !== raw) this.value = cleaned; // keep spaces visible while typing
      const filter = cleaned.toLowerCase().trim(); // trim only for matching logic
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

    // Clear a specific schedule slot (add/edit scope)
    function clearScheduleSlot(scope, idx){
      try{
        const prefix = scope === 'edit' ? 'edit_' : '';
        const daySel = document.querySelector(`select[name="${prefix}day_${idx}"]`);
        const startInp = document.querySelector(`input[name="${prefix}start_time_${idx}"]`);
        const endInp = document.querySelector(`input[name="${prefix}end_time_${idx}"]`);
        if(daySel) daySel.value = '';
        if(startInp) startInp.value = '';
        if(endInp) endInp.value = '';
        // Visually soften the row
        const label = document.querySelector(`.schedule-label button.schedule-clear-btn[data-scope="${scope}"][data-index="${idx}"]`);
        const row = label ? label.closest('.schedule-label')?.nextElementSibling : null;
        if(row && row.classList){ row.classList.add('cleared'); setTimeout(()=>row.classList.remove('cleared'), 800); }
        try{ showNotification(`Schedule ${idx} cleared${scope==='edit'?' (Edit)':''}`); }catch(_){ }
      }catch(_){ }
    }

    // Attach handlers to Remove buttons
    document.addEventListener('click', function(e){
      const btn = e.target.closest && e.target.closest('.schedule-clear-btn');
      if(!btn) return;
      const scope = btn.getAttribute('data-scope');
      const idx = btn.getAttribute('data-index');
      clearScheduleSlot(scope, idx);
    });

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
      document.getElementById('editFacultyForm').action = `/admin-itis/update-professor/${profId}`;
      
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
      
      // Load and populate subject assignments, then snapshot initial state
      loadProfessorSubjects(profId).then(()=>{
        setEditInitialSnapshot();
      });
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
      return fetch(`/admin-itis/professor-subjects/${profId}`, {
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

    // ---- Dirty tracking for Edit form ----
    function getEditFormSnapshot(){
      const form = document.getElementById('editFacultyForm');
      if(!form) return '';
      const snap = {
        Name: form.querySelector('#editName')?.value || '',
        sched: {
          d1: form.querySelector('select[name="edit_day_1"]')?.value || '',
          s1: form.querySelector('input[name="edit_start_time_1"]')?.value || '',
          e1: form.querySelector('input[name="edit_end_time_1"]')?.value || '',
          d2: form.querySelector('select[name="edit_day_2"]')?.value || '',
          s2: form.querySelector('input[name="edit_start_time_2"]')?.value || '',
          e2: form.querySelector('input[name="edit_end_time_2"]')?.value || '',
          d3: form.querySelector('select[name="edit_day_3"]')?.value || '',
          s3: form.querySelector('input[name="edit_start_time_3"]')?.value || '',
          e3: form.querySelector('input[name="edit_end_time_3"]')?.value || '',
        },
        subjects: Array.from(document.querySelectorAll('#editSubjectList input[type="checkbox"]:checked')).map(cb=>cb.value).sort()
      };
      try{ return JSON.stringify(snap); }catch(_){ return '' }
    }
    function toggleEditSubmitEnabled(enabled){
      const form = document.getElementById('editFacultyForm');
      const btn = form?.querySelector('.panel-actions .btn-primary');
      if(btn){ btn.disabled = !enabled; }
    }
    function setEditInitialSnapshot(){
      const form = document.getElementById('editFacultyForm');
      if(!form) return;
      form.dataset.initialSnapshot = getEditFormSnapshot();
      form.dataset.dirty = '0';
      toggleEditSubmitEnabled(false);
    }
    function refreshEditDirty(){
      const form = document.getElementById('editFacultyForm');
      if(!form) return;
      const cur = getEditFormSnapshot();
      const dirty = (cur !== (form.dataset.initialSnapshot||''));
      form.dataset.dirty = dirty ? '1' : '0';
      toggleEditSubmitEnabled(dirty);
    }
    // Bind change listeners once
    (function bindEditDirtyListeners(){
      const form = document.getElementById('editFacultyForm');
      if(!form) return;
      form.addEventListener('input', refreshEditDirty, true);
      form.addEventListener('change', refreshEditDirty, true);
    })();

    // --- Delete from Edit Modal ---
    document.querySelector('.delete-prof-btn-modal').onclick = function() {
      ModalManager.close('editFaculty');
      showDeleteModal(currentProfId);
    };

    // --- Delete Modal Logic ---
    function showDeleteModal(profId) {
      var form = document.getElementById('deleteForm');
      form.action = '/admin-itis/delete-professor/' + profId;
      ModalManager.open('deleteConfirm');
    }

  // Removed legacy addFacultyPanel submit handler (handled by enhanceAddProfessorFormItis)

    // Handle edit form submission
    document.getElementById('editFacultyForm').addEventListener('submit', function(e) {
      e.preventDefault();
      if(this.dataset.dirty !== '1'){
        showNotification('No changes to update', true);
        return;
      }
      
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
        // Use themed notification instead of default alert
        showNotification('Please fill in all required fields', true);
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
            // Themed error notification
            showNotification(`End time must be after start time for ${schedule.day}`, true);
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
      
      // Always include Schedule hidden input so clearing all rows will wipe schedule
      const scheduleInput = document.createElement('input');
      scheduleInput.type = 'hidden';
      scheduleInput.name = 'Schedule';
      scheduleInput.value = scheduleData.length > 0 ? scheduleData.join('\n') : '';
      this.appendChild(scheduleInput);
      // Debug log
      console.log('Schedule being sent:', scheduleInput.value);
      
  // Anti-spam: prevent duplicate submissions while request is in-flight
  if(this.dataset.submitting === '1') return;
  // 5-second cooldown per click
  if(this.dataset.cooldown === '1') return;
  this.dataset.cooldown = '1';
  setTimeout(()=>{ this.dataset.cooldown = '0'; }, 5000);
  this.dataset.submitting = '1';
  const submitBtnRef = lockSubmitButton(this);

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
          // Themed success notification
          showNotification('Professor updated successfully');
          const profId = document.getElementById('editProfId').value;
          const card = document.querySelector(`[data-prof-id="${profId}"]`);
            if(card){
              const newName = document.getElementById('editName').value;
              card.dataset.name = newName;
              if(card.querySelector('.profile-name')) card.querySelector('.profile-name').textContent = newName;
            }
          ModalManager.close('editFaculty');
        } else {
          showNotification('Error updating professor: ' + (data.message || 'Unknown error'), true);
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showNotification('Error updating professor', true);
      })
      .finally(() => {
        this.dataset.submitting = '0';
        unlockSubmitButton(submitBtnRef);
      });
    });

    // Initialize Modal Manager when page loads
    document.addEventListener('DOMContentLoaded', function() {
      ModalManager.init();
      initRealtimeAdminItis();
      enhanceAddProfessorFormItis();
      setupAddChooserModal();
      enhanceAddStudentPanelItis();
    });

    function enhanceAddProfessorFormItis(){
      const form = document.getElementById('addFacultyPanel');
      if(!form) return;
      form.addEventListener('submit', function(e){
        if(e.defaultPrevented) return;
      });
      form.addEventListener('submit', function(e){
        if(form.dataset.ajaxDone) return;
        e.preventDefault();
        const fd = new FormData(form);
        const overlay = document.getElementById('globalLoading');
        if(overlay){ overlay.classList.add('active'); overlay.setAttribute('aria-hidden','false'); }
        // Allow the overlay to paint before starting the request so the spinner animates
        requestAnimationFrame(() => {
        fetch(form.action,{method:'POST', body: fd, headers:{
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
              if(data.professor){ addOrUpdateCardItis(data.professor); }
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
          .catch(err=>{ console.error(err); showNotification('Request failed: '+(err&&err.message?err.message:'Unexpected error'), true); })
          .finally(()=>{ if(overlay){ overlay.classList.remove('active'); overlay.setAttribute('aria-hidden','true'); } });
        });
      }, true);
    }

    function enhanceAddStudentPanelItis(){
      const form = document.getElementById('addStudentPanel');
      if(!form) return;
      const genBtn = document.getElementById('btnGenTempPwStudentItis');
      const pwInput = document.getElementById('addStudentTempPasswordItis');
      if(genBtn && pwInput){ genBtn.addEventListener('click', ()=>{ pwInput.value = generatePassword(12); pwInput.dispatchEvent(new Event('input',{bubbles:true})); }); }
      form.addEventListener('submit', function(e){
        if(form.dataset.ajaxDone) return;
        e.preventDefault();
        const overlay = document.getElementById('globalLoading');
        if(overlay){ overlay.classList.add('active'); overlay.setAttribute('aria-hidden','false'); }
        requestAnimationFrame(()=>{
          fetch(form.action,{method:'POST', body: new FormData(form), headers:{
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content,
            'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest'
          }})
          .then(async r=>{ try{ return await r.json(); } catch(e){ showNotification('Unexpected server response', true); throw e; } })
          .then(data=>{
            if(data.success){ showNotification('Student added successfully'); ModalManager.close('addStudent'); }
            else { let msg = data.message || 'Failed to add student'; if(data.errors){ try{ const all = Object.values(data.errors).flat(); if(all.length) msg = all.join(' • ');}catch(_){ }} showNotification(msg, true); }
          })
          .catch(err=>{ console.error(err); showNotification('Request failed: '+(err&&err.message?err.message:'Unexpected error'), true); })
          .finally(()=>{ if(overlay){ overlay.classList.remove('active'); overlay.setAttribute('aria-hidden','true'); } });
        });
      }, true);
    }

    function setupAddChooserModal(){
      const fab = document.getElementById('addChooserBtn');
      const modal = document.getElementById('addChooserModal');
      if(!fab || !modal) return;
      function toggle(force){ const open = force!==undefined?force:!modal.classList.contains('show'); modal.classList.toggle('show', open); modal.setAttribute('aria-hidden', open? 'false':'true'); }
      fab.addEventListener('click', ()=> toggle(true));
      // Close actions
      modal.querySelectorAll('[data-close-chooser]').forEach(btn=> btn.addEventListener('click', ()=> toggle(false)));
      modal.addEventListener('click', (e)=>{ if(e.target === modal) toggle(false); });
      // Option buttons
      modal.querySelectorAll('[data-open-add]').forEach(btn=>{
        btn.addEventListener('click', ()=>{
          const type = btn.getAttribute('data-open-add');
          toggle(false);
          if(type==='faculty') ModalManager.open('addFaculty');
          if(type==='student') ModalManager.open('addStudent');
        });
      });
    }

    function addOrUpdateCardItis(p){
      const grid = document.querySelector('.profile-cards-grid'); if(!grid) return;
      const existing = grid.querySelector(`[data-prof-id="${p.Prof_ID}"]`);
  const imgPath = p.profile_photo_url || (p.profile_picture ? ('{{ url('/storage') }}/'+p.profile_picture) : '{{ asset('images/dprof.jpg') }}');
      if(existing){
        existing.dataset.name = p.Name;
        existing.dataset.sched = p.Schedule || 'No schedule set';
        existing.dataset.img = imgPath;
        existing.querySelector('.profile-name').textContent = p.Name;
        const imgEl= existing.querySelector('img'); if(imgEl) imgEl.src=imgPath; return;
      }
      const div = document.createElement('div');
      div.className='profile-card';
      div.dataset.name=p.Name; div.dataset.img=imgPath; div.dataset.profId=p.Prof_ID; div.setAttribute('data-prof-id',p.Prof_ID); div.dataset.sched=p.Schedule||'No schedule set';
      bindCardEventsItis(div);
      div.innerHTML=`<img src="${imgPath}" alt="Profile Picture"><div class='profile-name'>${p.Name}</div>`;
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

    function bindCardEventsItis(card){
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

    function initRealtimeAdminItis(){
      const script = document.createElement('script'); script.src='https://js.pusher.com/7.0/pusher.min.js'; script.onload=subscribe; document.body.appendChild(script);
      function subscribe(){
        const pusher = new Pusher('{{ config('broadcasting.connections.pusher.key') }}',{cluster:'{{ config('broadcasting.connections.pusher.options.cluster') }}'});
        const channel = pusher.subscribe('professors.dept.1');
        channel.bind('ProfessorAdded', data=> addOrUpdateCardItis(data));
        channel.bind('ProfessorUpdated', data=> addOrUpdateCardItis(data));
  channel.bind('ProfessorDeleted', data=> { const card=document.querySelector(`[data-prof-id="${data.Prof_ID}"]`); if(card) card.remove(); showNotification('Professor deleted successfully'); });
      }
    }

    // Intercept delete form submit to stay on page & show notice immediately (with anti-spam)
    document.addEventListener('submit', function(e){
      const form = e.target;
      if(form && form.id === 'deleteForm'){
  if(form.dataset.submitting === '1'){ e.preventDefault(); return; }
  // 5-second cooldown per click
  if(form.dataset.cooldown === '1'){ e.preventDefault(); return; }
  e.preventDefault();
  form.dataset.cooldown = '1';
  setTimeout(()=>{ form.dataset.cooldown = '0'; }, 5000);
  form.dataset.submitting = '1';
  const confirmBtn = form.querySelector('.delete-confirm');
  const cancelBtn = form.querySelector('.delete-cancel');
  if(confirmBtn){ confirmBtn.disabled = true; confirmBtn.setAttribute('aria-busy','true'); }
        if(cancelBtn){ cancelBtn.disabled = true; }
        fetch(form.action, {method:'POST', headers:{'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content,'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}, body: new FormData(form)})
          .then(r=> r.ok ? r.json().catch(()=>({success:true})) : Promise.reject(r))
          .then(()=>{ 
            const id = form.action.split('/').pop();
            const card = document.querySelector(`[data-prof-id="${id}"]`);
            if(card) card.remove();
            ModalManager.close('deleteConfirm'); showNotification('Professor deleted successfully');
          })
          .catch(()=>{ ModalManager.close('deleteConfirm'); showNotification('Deletion failed', true); })
          .finally(()=>{
            form.dataset.submitting = '0';
            if(confirmBtn){ confirmBtn.disabled = false; confirmBtn.removeAttribute('aria-busy'); }
            if(cancelBtn){ cancelBtn.disabled = false; }
          });
      }
    }, true);

    // Apply lightweight click guards for Cancel/Open Delete in edit panel and delete cancel
    guardRapidClicks('.panel-actions .btn-secondary', 5000);
    guardRapidClicks('.delete-prof-btn-modal', 5000);
    guardRapidClicks('#deleteForm .delete-cancel', 5000);
  </script>
</body>
</html>