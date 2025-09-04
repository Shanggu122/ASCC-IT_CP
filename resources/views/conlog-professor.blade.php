<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Consultation Log</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link rel="stylesheet" href="{{ asset('css/conlog-professor.css') }}">
  
  <style>
    /* Reschedule Modal Styles */
    .reschedule-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.7);
      display: none;
      justify-content: center;
      align-items: center;
      z-index: 1000;
    }

    .reschedule-modal {
      background: white;
      border-radius: 12px;
      padding: 0;
      width: 90%;
      max-width: 450px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
      animation: modalSlideIn 0.3s ease-out;
    }

    @keyframes modalSlideIn {
      from {
        opacity: 0;
        transform: translateY(-50px) scale(0.9);
      }
      to {
        opacity: 1;
        transform: translateY(0) scale(1);
      }
    }

    .reschedule-header {
      background: #2c5f4f;
      color: white;
      padding: 20px;
      border-radius: 12px 12px 0 0;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .reschedule-header h3 {
      margin: 0;
      font-size: 18px;
      font-weight: 600;
    }

    .reschedule-header .close-btn {
      background: none;
      border: none;
      color: white;
      font-size: 24px;
      cursor: pointer;
      padding: 0;
      width: 30px;
      height: 30px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 50%;
      transition: background-color 0.3s;
    }

    .reschedule-header .close-btn:hover {
      background: rgba(255, 255, 255, 0.2);
    }

    .reschedule-body {
      padding: 25px;
    }

    .reschedule-body p {
      margin: 0 0 20px 0;
      color: #555;
      font-size: 14px;
    }

    .date-input-group {
      margin-bottom: 25px;
    }

    .date-input-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: 600;
      color: #333;
      font-size: 14px;
    }

    .date-input {
      width: 100%;
      padding: 12px;
      border: 2px solid #e1e5e9;
      border-radius: 8px;
      font-size: 14px;
      transition: border-color 0.3s;
      font-family: 'Poppins', sans-serif;
      resize: vertical;
    }

    .date-input:focus {
      outline: none;
      border-color: #2c5f4f;
      box-shadow: 0 0 0 3px rgba(44, 95, 79, 0.1);
    }

    .date-input[type="date"] {
      resize: none;
    }

    .reschedule-buttons {
      display: flex;
      gap: 12px;
      justify-content: flex-end;
    }

    .btn-cancel,
    .btn-confirm {
      padding: 10px 20px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 14px;
      font-weight: 600;
      transition: all 0.3s;
      font-family: 'Poppins', sans-serif;
    }

    .btn-cancel {
      background: #f8f9fa;
      color: #6c757d;
      border: 1px solid #dee2e6;
    }

    .btn-cancel:hover {
      background: #e9ecef;
      color: #495057;
    }

    .btn-confirm {
      background: #2c5f4f;
      color: white;
    }

    .btn-confirm:hover {
      background: #1e4235;
      transform: translateY(-1px);
    }

    .btn-confirm:disabled {
      background: #ccc;
      cursor: not-allowed;
      transform: none;
    }
  </style>
</head>
<body>
  @include('components.navbarprof')
  <!-- Custom Modal HTML for Professor Message Handling -->
  <div class="custom-modal" id="professorModal">
    <div class="custom-modal-content">
      <span id="professorModalMessage"></span>
      <button class="custom-modal-btn" onclick="closeProfessorModal()">OK</button>
    </div>
  </div>

  <div class="main-content">
    <div class="header">
      <h1 style="display:flex;align-items:center;gap:14px;">Consultation Logs
        <button id="print-logs-btn" type="button" class="print-logs-btn" title="Print Consultation Log">
          <i class='bx bx-printer'></i><span class="print-label">Print</span>
        </button>
      </h1>
    </div>

    <div class="search-container">
      <input type="text" id="searchInput" placeholder="Search..." style="flex:1;">
      <div class="filter-group-horizontal">
        <select id="typeFilter" class="filter-select">
          <option value="">All Types</option>
          @php
            $fixedTypes = [
              'Tutoring',
              'Grade Consultation',
              'Missed Activities',
              'Special Quiz or Exam',
              'Capstone Consultation'
            ];
          @endphp
          @foreach($fixedTypes as $type)
            <option value="{{ $type }}">{{ $type }}</option>
          @endforeach
          <option value="Others">Others</option>
        </select>
      </div>
    </div>

    <div class="table-container">
      <div class="table">
        <!-- Header Row -->
        <div class="table-row table-header">
          <div class="table-cell">No.</div> <!-- Add this line -->
          <div class="table-cell">Student</div>
          <div class="table-cell">Subject</div>
          <div class="table-cell">Date</div>
          <div class="table-cell">Type</div>
          <div class="table-cell">Mode</div>
          <div class="table-cell">Status</div>
           <div class="table-cell " style="width: 180px">Action</div>
        </div>
    
        <!-- Dynamic Data Rows -->
  @forelse($bookings as $b)
  <div class="table-row">
          <div class="table-cell" data-label="No.">{{ $loop->iteration }}</div>
          <div class="table-cell" data-label="Student">{{ $b->student }}</div> <!-- Student name -->
          <div class="table-cell" data-label="Subject">{{ $b->subject }}</div>
          <div class="table-cell" data-label="Date">{{ \Carbon\Carbon::parse($b->Booking_Date)->format('D, M d Y') }}</div>
          <div class="table-cell" data-label="Type">{{ $b->type }}</div>
          <div class="table-cell" data-label="Mode">{{ ucfirst($b->Mode) }}</div>
          <div class="table-cell" data-label="Status">{{ ucfirst($b->Status) }}</div>
          <div class="table-cell" data-label="Action" style="width: 180px;">
            <div class="action-btn-group" style="display: flex; gap: 8px;">
              @if($b->Status !== 'rescheduled')
              <button 
                  onclick="showRescheduleModal({{ $b->Booking_ID }}, '{{ $b->Booking_Date }}')" 
                  class="action-btn btn-reschedule"
                  title="Reschedule"
              >
                  <i class='bx bx-calendar-x'></i> 
              </button>
              @endif

              @if($b->Status !== 'approved')
              <button 
                  onclick="approveWithWarning(this, {{ $b->Booking_ID }}, '{{ $b->Booking_Date }}')" 
                  class="action-btn btn-approve"
                  title="Approve"
              >
                  <i class='bx bx-check-circle'></i> 
              </button>
              @endif

              @if($b->Status !== 'completed')
              <button 
                  onclick="removeThisButton(this, {{ $b->Booking_ID }}, 'Completed')" 
                  class="action-btn btn-completed"
                  title="Completed"
              >
                  <i class='bx bx-task'></i> 
              </button>
              @endif
            </div>
          </div>
        </div>
        @empty
          <div class="table-row">
            <div class="table-cell" colspan="8">No consultations found.</div>
          </div>
        @endforelse
      <div style="height: 80px;"></div> <!-- Spacer under the last table row -->
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

    <!-- Reschedule Modal -->
    <div class="reschedule-overlay" id="rescheduleOverlay">
      <div class="reschedule-modal">
        <div class="reschedule-header">
          <h3>Reschedule Consultation</h3>
          <button class="close-btn" onclick="closeRescheduleModal()">×</button>
        </div>
        <div class="reschedule-body">
          <p><strong>Current Date:</strong> <span id="currentDate"></span></p>
          <div class="date-input-group">
            <label for="newDate">Select New Date:</label>
            <input type="date" id="newDate" class="date-input" required>
          </div>
          <div class="date-input-group">
            <label for="rescheduleReason">Reason for Rescheduling:</label>
            <textarea id="rescheduleReason" class="date-input" rows="3" placeholder="Please provide a reason for rescheduling this consultation..." required></textarea>
          </div>
          <div class="reschedule-buttons">
            <button type="button" class="btn-cancel" onclick="closeRescheduleModal()">Cancel</button>
            <button type="button" class="btn-confirm" onclick="confirmReschedule()">Reschedule</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Approval Warning Modal -->
    <div class="reschedule-overlay approval-warning-modal" id="approvalWarningOverlay">
      <div class="reschedule-modal approval-warning-content">
        <div class="reschedule-header">
          <h3>⚠️ High Volume Warning</h3>
          <button class="close-btn" onclick="closeApprovalWarningModal()">×</button>
        </div>
        <div class="reschedule-body approval-warning-body">
          <div class="warning-info">
            <p>
              <i class='bx bx-info-circle'></i>
              This date already has <span id="existingConsultationsCount">5</span> approved consultations
            </p>
            <p>
              <strong>Date:</strong> <span id="warningDate"></span>
            </p>
          </div>
          <p class="warning-text">
            Are you sure you want to approve another consultation for this date? This will bring your total to <span id="totalAfterApproval">6</span> consultations.
          </p>
          <div class="reschedule-buttons">
            <button type="button" class="btn-cancel" onclick="showRescheduleFromWarning()">Reschedule Instead</button>
            <button type="button" class="btn-confirm" onclick="confirmApproval()">
              Yes, Approve Anyway
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Hidden printable container -->
    <div id="printLogsContainer" style="display:none;">
      <div class="print-header">
        <h2>Professor Consultation Log</h2>
  <div id="printProfessor" class="print-professor" 
    data-prof-name="{{ optional(auth()->guard('professor')->user())->Name ?? (auth()->user()->Name ?? auth()->user()->name ?? '') }}"
    data-prof-id="{{ optional(auth()->guard('professor')->user())->Prof_ID ?? (auth()->user()->Prof_ID ?? auth()->user()->id ?? '') }}">
  </div>
  <div id="printMeta" class="print-meta"></div>
      </div>
      <table class="print-table" id="printLogsTable">
        <thead>
          <tr>
            <th>No.</th>
            <th>Student</th>
            <th>Subject</th>
            <th>Date</th>
            <th>Type</th>
            <th>Mode</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
      <div id="printFooter" class="print-footer-note"></div>
    </div>
  </div>
  <script>
    let currentBookingId = null;
    let currentRescheduleButton = null;

    function showRescheduleModal(bookingId, currentDate) {
      currentBookingId = bookingId;
      currentRescheduleButton = event.target.closest('button');
      
      // Set current date in the modal
      document.getElementById('currentDate').textContent = currentDate;
      
      // Set minimum date to today
      const today = new Date().toISOString().split('T')[0];
      document.getElementById('newDate').setAttribute('min', today);
      document.getElementById('newDate').value = '';
      
      // Show modal
      document.getElementById('rescheduleOverlay').style.display = 'flex';
    }

    function closeRescheduleModal() {
      document.getElementById('rescheduleOverlay').style.display = 'none';
      currentBookingId = null;
      currentRescheduleButton = null;
      
      // Clear form fields
      document.getElementById('newDate').value = '';
      document.getElementById('rescheduleReason').value = '';
    }

    // Function to show reschedule modal from the approval warning
    function showRescheduleFromWarning() {
      if (!pendingApprovalBookingId) {
        showProfessorModal('Error: No booking selected for rescheduling.');
        return;
      }

      // Get the booking date from the warning modal
      const warningDateElement = document.getElementById('warningDate');
      const currentDate = warningDateElement ? warningDateElement.textContent : '';

      // Close the approval warning modal
      closeApprovalWarningModal();

      // Set up the reschedule modal with the pending booking data
      currentBookingId = pendingApprovalBookingId;
      currentRescheduleButton = pendingApprovalButton;

      // Set current date in the modal
      document.getElementById('currentDate').textContent = currentDate;
      
      // Set minimum date to today
      const today = new Date().toISOString().split('T')[0];
      document.getElementById('newDate').setAttribute('min', today);
      document.getElementById('newDate').value = '';
      
      // Show reschedule modal
      document.getElementById('rescheduleOverlay').style.display = 'flex';

      // Clear the pending approval variables since we're now rescheduling
      pendingApprovalButton = null;
      pendingApprovalBookingId = null;
    }

    function confirmReschedule() {
      const newDate = document.getElementById('newDate').value;
      const reason = document.getElementById('rescheduleReason').value.trim();
      
      if (!newDate) {
        showProfessorModal('Please select a new date.');
        return;
      }
      
      if (!reason) {
        showProfessorModal('Please provide a reason for rescheduling.');
        return;
      }
      
      if (!currentBookingId) {
        showProfessorModal('Error: Booking ID is missing. Please try again.');
        return;
      }
      
      // Store the values before closing modal (which sets them to null)
      const bookingId = currentBookingId;
      const rescheduleButton = currentRescheduleButton;
      
      // Convert date to a more readable format
      const dateObj = new Date(newDate);
      const options = { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' };
      const formattedDate = dateObj.toLocaleDateString('en-US', options);
      
      // Close modal (this sets currentBookingId and currentRescheduleButton to null)
      closeRescheduleModal();
      
      // Remove the button immediately for better UX
      if (rescheduleButton) {
        rescheduleButton.remove();
      }
      
      // Call the update function with the stored booking ID, date, and reason
      updateStatusWithDate(bookingId, 'rescheduled', formattedDate, reason);
    }

    function updateStatusWithDate(bookingId, status, newDate = null, reason = null) {
      
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
      
      if (!csrfToken) {
        showProfessorModal('Error: CSRF token not found. Please refresh the page and try again.');
        location.reload();
        return;
      }
      
      const requestBody = {
        id: bookingId,
        status: status.toLowerCase()
      };
      
      if (newDate) {
        requestBody.new_date = newDate;
      }
      
      if (reason) {
        requestBody.reschedule_reason = reason;
      }
      
      fetch('/api/consultations/update-status', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify(requestBody)
      })
      .then(response => {
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
      })
      .then(data => {
        if (data.success) {
          showProfessorModal('Success: ' + data.message);
          setTimeout(() => location.reload(), 3500); // Reload the page to reflect changes
        } else {
          showProfessorModal('Failed to update status: ' + data.message);
          setTimeout(() => location.reload(), 3500); // Reload to restore the original state
        }
      })
      .catch(error => {
        console.error('Fetch error:', error);
        showProfessorModal('Network error occurred while updating status. Please check your connection and try again.\n\nError: ' + error.message);
  setTimeout(() => location.reload(), 3500);
      });
    }

    function updateStatus(bookingId, status) {
      updateStatusWithDate(bookingId, status);
    }

    function removeThisButton(btn, bookingId, status) {
      btn.remove(); // Only remove the clicked button
      updateStatus(bookingId, status);
    }

    // Variables to store approval context
    let pendingApprovalButton = null;
    let pendingApprovalBookingId = null;

    // New function to handle approval with warning
    function approveWithWarning(btn, bookingId, bookingDate) {
      console.log('approveWithWarning called:', { bookingId, bookingDate });
      
      // Store the context for later use
      pendingApprovalButton = btn;
      pendingApprovalBookingId = bookingId;

      // Count existing approved consultations for this date
      fetch('/api/consultations')
        .then(response => response.json())
        .then(data => {
          // Filter consultations for the specific date with approved status
          const consultationsOnDate = data.filter(consultation => {
            const consultationDate = new Date(consultation.Booking_Date).toDateString();
            const targetDate = new Date(bookingDate).toDateString();
            return consultationDate === targetDate && consultation.Status.toLowerCase() === 'approved';
          });

          const approvedCount = consultationsOnDate.length;
          console.log('Approved consultations on', bookingDate, ':', approvedCount);

          // Show warning if already 5 or more approved consultations
          if (approvedCount >= 5) {
            showApprovalWarningModal(bookingDate, approvedCount);
          } else {
            // Directly approve if less than 5
            removeThisButton(btn, bookingId, 'Approved');
          }
        })
        .catch(error => {
          console.error('Error fetching consultation data:', error);
          // If we can't fetch data, show a generic warning
          showProfessorModal('Unable to verify consultation count. Please try again.');
        });
// Custom Modal JS (moved to global scope)
function showProfessorModal(message) {
  document.getElementById('professorModalMessage').textContent = message;
  document.getElementById('professorModal').style.display = 'flex';
}
function closeProfessorModal() {
  document.getElementById('professorModal').style.display = 'none';
}
    }

    function showApprovalWarningModal(bookingDate, currentCount) {
      const modal = document.getElementById('approvalWarningOverlay');
      const dateElement = document.getElementById('warningDate');
      const countElement = document.getElementById('existingConsultationsCount');
      const totalElement = document.getElementById('totalAfterApproval');

      // Format the date nicely
      const formattedDate = new Date(bookingDate).toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });

      dateElement.textContent = formattedDate;
      countElement.textContent = currentCount;
      totalElement.textContent = currentCount + 1;

      modal.style.display = 'flex';
    }

    function closeApprovalWarningModal() {
      const modal = document.getElementById('approvalWarningOverlay');
      modal.style.display = 'none';
      
      // Clear the context
      pendingApprovalButton = null;
      pendingApprovalBookingId = null;
    }

    function confirmApproval() {
      if (pendingApprovalButton && pendingApprovalBookingId) {
        // Proceed with the approval
        removeThisButton(pendingApprovalButton, pendingApprovalBookingId, 'Approved');
        closeApprovalWarningModal();
      }
    }

    // Close modal when clicking outside of it
    document.addEventListener('click', function(event) {
      const rescheduleModal = document.getElementById('rescheduleOverlay');
      const approvalWarningModal = document.getElementById('approvalWarningOverlay');
      
      if (event.target === rescheduleModal) {
        closeRescheduleModal();
      }
      
      if (event.target === approvalWarningModal) {
        closeApprovalWarningModal();
      }
    });

    // Close modal with Escape key
    document.addEventListener('keydown', function(event) {
      if (event.key === 'Escape') {
        closeRescheduleModal();
        closeApprovalWarningModal();
      }
    });

    const fixedTypes = [
      'tutoring',
      'grade consultation',
      'missed activities',
      'special quiz or exam',
      'capstone consultation'
    ];

    function filterRows() {
        let search = document.getElementById('searchInput').value.toLowerCase();
        let type = document.getElementById('typeFilter').value.toLowerCase();
        let rows = document.querySelectorAll('.table-row:not(.table-header)');
        rows.forEach(row => {
            let rowType = row.querySelector('[data-label="Type"]')?.textContent.toLowerCase() || '';
            let student = row.querySelector('[data-label="Student"]')?.textContent.toLowerCase() || '';
            let rowSubject = row.querySelector('[data-label="Subject"]')?.textContent.toLowerCase() || '';

            // Is this row a custom type (not in fixedTypes)?
            let isOthers = fixedTypes.indexOf(rowType) === -1 && rowType !== '';

            let matchesType =
                !type ||
                (type !== "others" && rowType === type) ||
                (type === "others" && isOthers);

            let matchesSearch = student.includes(search) || rowSubject.includes(search) || rowType.includes(search);

            if (matchesSearch && matchesType) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    document.getElementById('searchInput').addEventListener('keyup', filterRows);
    document.getElementById('typeFilter').addEventListener('change', filterRows);

    // Real-time updates for professor consultation log - DISABLED TO PREVENT DUPLICATE ROWS
    /*
    function loadProfessorConsultationLogs() {
      fetch('/api/professor/consultation-logs')
        .then(response => response.json())
        .then(data => {
          updateProfessorConsultationTable(data);
        })
        .catch(error => {
          console.error('Error loading professor consultation logs:', error);
        });
    }

    function updateProfessorConsultationTable(bookings) {
      const table = document.querySelector('.table');
      const header = document.querySelector('.table-header');
      
      // Clear existing rows except header
      const existingRows = table.querySelectorAll('.table-row:not(.table-header)');
      existingRows.forEach(row => row.remove());
      
      if (bookings.length === 0) {
        const emptyRow = document.createElement('div');
        emptyRow.className = 'table-row';
        emptyRow.innerHTML = `
          <div class="table-cell" colspan="9">No consultations found.</div>
        `;
        table.appendChild(emptyRow);
      } else {
        bookings.forEach((booking, index) => {
          const row = document.createElement('div');
          row.className = 'table-row';
          
          const bookingDate = new Date(booking.Booking_Date);
          const createdAt = new Date(booking.Created_At);
          
          let statusActions = '';
          if (booking.Status.toLowerCase() === 'pending') {
            statusActions = `
              <button class="action-btn approve-btn" onclick="updateStatus(${booking.Booking_ID}, 'approved')">Approve</button>
              <button class="action-btn reschedule-btn" onclick="showRescheduleModal(${booking.Booking_ID})">Reschedule</button>
            `;
          } else if (booking.Status.toLowerCase() === 'approved') {
            statusActions = `
              <button class="action-btn complete-btn" onclick="updateStatus(${booking.Booking_ID}, 'completed')">Complete</button>
              <button class="action-btn reschedule-btn" onclick="showRescheduleModal(${booking.Booking_ID})">Reschedule</button>
            `;
          } else {
            statusActions = `<span class="status-final">${booking.Status.charAt(0).toUpperCase() + booking.Status.slice(1)}</span>`;
          }
          
          row.innerHTML = `
            <div class="table-cell" data-label="No.">${index + 1}</div>
            <div class="table-cell" data-label="Student">${booking.student || 'N/A'}</div>
            <div class="table-cell" data-label="Subject">${booking.subject}</div>
            <div class="table-cell" data-label="Date">${bookingDate.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' })}</div>
            <div class="table-cell" data-label="Type">${booking.type}</div>
            <div class="table-cell" data-label="Mode">${booking.Mode.charAt(0).toUpperCase() + booking.Mode.slice(1)}</div>
            <div class="table-cell" data-label="Status">${booking.Status.charAt(0).toUpperCase() + booking.Status.slice(1)}</div>
            <div class="table-cell action-cell" data-label="Action">${statusActions}</div>
          `;
          
          table.appendChild(row);
        });
      }
      
      // Add spacer
      const spacer = document.createElement('div');
      spacer.style.height = '80px';
      table.appendChild(spacer);
      
      // Re-apply filters after updating
      filterRows();
    }

    // Initial load and real-time updates every 5 seconds - DISABLED
    loadProfessorConsultationLogs();
    setInterval(loadProfessorConsultationLogs, 5000);
    */

    // Mobile notification functions for navbar
    function toggleMobileNotifications() {
      const dropdown = document.getElementById('mobileNotificationsDropdown');
      if (dropdown) {
        dropdown.classList.toggle('active');
        
        // Close sidebar if open
        const sidebar = document.getElementById('sidebar');
        if (sidebar && sidebar.classList.contains('active')) {
          sidebar.classList.remove('active');
        }
        
        // Load notifications if opening dropdown
        if (dropdown.classList.contains('active')) {
          loadMobileNotifications();
        }
      }
    }

    function loadMobileNotifications() {
      fetch('/api/professor/notifications')
        .then(response => response.json())
        .then(data => {
          displayMobileNotifications(data.notifications);
          updateMobileNotificationBadge();
        })
        .catch(error => {
          console.error('Error loading mobile notifications:', error);
        });
    }

    function displayMobileNotifications(notifications) {
      const mobileContainer = document.getElementById('mobileNotificationsContainer');
      if (!mobileContainer) return;
      
      if (notifications.length === 0) {
        mobileContainer.innerHTML = `
          <div class="no-notifications">
            <i class='bx bx-bell-off'></i>
            <p>No notifications yet</p>
          </div>
        `;
        return;
      }
      
      const notificationsHtml = notifications.map(notification => {
        const timeAgo = getTimeAgo(notification.created_at);
        const unreadClass = notification.is_read ? '' : 'unread';
        
        return `
          <div class="notification-item ${unreadClass}" onclick="markMobileNotificationAsRead(${notification.id})">
            <div class="notification-type ${notification.type}">${notification.type.replace('_', ' ')}</div>
            <div class="notification-title">${notification.title}</div>
            <div class="notification-message">${notification.message}</div>
            <div class="notification-time">${timeAgo}</div>
          </div>
        `;
      }).join('');
      
      mobileContainer.innerHTML = notificationsHtml;
    }

    function updateMobileNotificationBadge() {
      fetch('/api/professor/notifications/unread-count')
        .then(response => response.json())
        .then(data => {
          const mobileCountElement = document.getElementById('mobileNotificationBadge');
          if (mobileCountElement) {
            if (data.unread_count > 0) {
              mobileCountElement.textContent = data.unread_count;
              mobileCountElement.style.display = 'flex';
            } else {
              mobileCountElement.style.display = 'none';
            }
          }
        })
        .catch(error => {
          console.error('Error updating mobile notification badge:', error);
        });
    }

    function markMobileNotificationAsRead(notificationId) {
      fetch('/api/professor/notifications/mark-read', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ notification_id: notificationId })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          loadMobileNotifications(); // Reload to update read status
        }
      })
      .catch(error => {
        console.error('Error marking mobile notification as read:', error);
      });
    }

    function markAllProfessorNotificationsAsRead() {
      fetch('/api/professor/notifications/mark-all-read', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          loadMobileNotifications(); // Reload to update read status
        }
      })
      .catch(error => {
        console.error('Error marking all professor notifications as read:', error);
      });
    }

    function getTimeAgo(dateString) {
      const date = new Date(dateString);
      const now = new Date();
      const diffInSeconds = Math.floor((now - date) / 1000);
      if (diffInSeconds < 60) return 'Just now';
      if (diffInSeconds < 3600) {
        const m = Math.floor(diffInSeconds / 60);
        return `${m} ${m === 1 ? 'min' : 'mins'} ago`;
      }
      if (diffInSeconds < 86400) {
        const h = Math.floor(diffInSeconds / 3600);
        return `${h === 1 ? '1 hr' : h + ' hrs'} ago`;
      }
      const d = Math.floor(diffInSeconds / 86400);
      return `${d} ${d === 1 ? 'day' : 'days'} ago`;
    }

    // Initialize mobile notifications on page load
    document.addEventListener('DOMContentLoaded', function() {
      updateMobileNotificationBadge();
      // Update badge every 30 seconds
      setInterval(updateMobileNotificationBadge, 30000);
  const printBtn = document.getElementById('print-logs-btn');
   if (printBtn) printBtn.addEventListener('click', generateAndDownloadPdf);
    });
  </script>
  <script src="{{ asset('js/ccit.js') }}"></script>
  <script>
    // Custom Modal JS (guaranteed global)
    function showProfessorModal(message) {
      document.getElementById('professorModalMessage').textContent = message;
      document.getElementById('professorModal').style.display = 'flex';
    }
    function closeProfessorModal() {
      document.getElementById('professorModal').style.display = 'none';
    }

    // PDF DOWNLOAD FEATURE
    function generateAndDownloadPdf(){
      try {
        const rows = Array.from(document.querySelectorAll('.table-row')).filter(r => !r.classList.contains('table-header'));
        const data = [];
        rows.forEach(r => {
          if (r.style.display === 'none') return; // respect active filters
          const cells = r.querySelectorAll('.table-cell');
          if(cells.length < 7) return;
          data.push({
            no: cells[0]?.innerText.trim() || '',
            student: cells[1]?.innerText.trim() || '',
            subject: cells[2]?.innerText.trim() || '',
            date: cells[3]?.innerText.trim() || '',
            type: cells[4]?.innerText.trim() || '',
            mode: cells[5]?.innerText.trim() || '',
            status: cells[6]?.innerText.trim() || ''
          });
        });
        if (data.length === 0){ alert('No consultations to print.'); return; }
        // sort by date then student
        data.sort((a,b)=> parseDate(a.date) - parseDate(b.date) || a.student.localeCompare(b.student));
        // Prepare payload for server
        const payload = data.map(d => ({
          student: d.student,
          subject: d.subject,
          date: d.date,
          type: d.type,
          mode: d.mode,
          status: d.status
        }));
        fetch("{{ route('conlog-professor.pdf') }}", {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          },
          body: JSON.stringify({ logs: payload })
        }).then(res => {
          if(!res.ok) throw new Error('Failed to generate PDF');
          return res.blob();
        }).then(blob => {
          const url = window.URL.createObjectURL(blob);
          const a = document.createElement('a');
          a.href = url;
          a.download = 'consultation_logs.pdf';
          document.body.appendChild(a);
          a.click();
          a.remove();
          setTimeout(()=>window.URL.revokeObjectURL(url), 1500);
        }).catch(e => {
          console.error(e); alert('PDF generation failed.');
        });
      } catch(err){
        console.error('Export error', err); alert('Failed to prepare data.');
      }
    }
    function parseDate(str){ const d = new Date(str); return isNaN(d)? Infinity : d; }
    function extractCreatedAt(){ return ''; }
    function escapeHtml(s){ return String(s).replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[c])); }
  function getPrintStyles(){ return `body{font-family:Poppins,Arial,sans-serif;margin:24px;}h2{margin:0 0 4px;color:#12372a;font-size:26px;} .print-professor{font-size:12px;color:#234b3b;margin-bottom:2px;font-weight:500;} .print-meta{font-size:12px;color:#555;margin-bottom:12px;}table{width:100%;border-collapse:collapse;font-size:12px;}th,td{border:1px solid #222;padding:6px 8px;text-align:left;}th{background:#12372a;color:#fff;font-weight:600;} .status-badge{padding:2px 6px;border-radius:4px;font-weight:600;font-size:11px;color:#fff;display:inline-block;} .status-badge.status-pending{background:#ffa600;} .status-badge.status-approved{background:#27ae60;} .status-badge.status-completed{background:#093b2f;} .status-badge.status-rescheduled{background:#c50000;} .print-footer-note{margin-top:22px;font-size:11px;color:#444;text-align:right;}@media print{body{margin:0;padding:0;} }`; }
  </script>
</body>
</html>