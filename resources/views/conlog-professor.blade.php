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
    }

    .date-input:focus {
      outline: none;
      border-color: #2c5f4f;
      box-shadow: 0 0 0 3px rgba(44, 95, 79, 0.1);
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

  <div class="main-content">
    <div class="header">
      <h1>Consultation Logs</h1>
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
          <div class="table-cell" data-label="Date">{{ \Carbon\Carbon::parse($b->Booking_Date)->format('m/d/Y') }}</div>
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
                  onclick="removeThisButton(this, {{ $b->Booking_ID }}, 'Approved')" 
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
          <div class="reschedule-buttons">
            <button type="button" class="btn-cancel" onclick="closeRescheduleModal()">Cancel</button>
            <button type="button" class="btn-confirm" onclick="confirmReschedule()">Reschedule</button>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script>
    let currentBookingId = null;
    let currentRescheduleButton = null;

    function showRescheduleModal(bookingId, currentDate) {
      console.log('Debug - showRescheduleModal called:', { bookingId, currentDate });
      
      currentBookingId = bookingId;
      currentRescheduleButton = event.target.closest('button');
      
      console.log('Debug - variables set:', { 
        currentBookingId, 
        currentRescheduleButton: currentRescheduleButton ? 'found' : 'not found' 
      });
      
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
    }

    function confirmReschedule() {
      const newDate = document.getElementById('newDate').value;
      
      console.log('Debug - confirmReschedule called:', { 
        currentBookingId, 
        newDate,
        currentRescheduleButton 
      });
      
      if (!newDate) {
        alert('Please select a new date.');
        return;
      }
      
      if (!currentBookingId) {
        alert('Error: Booking ID is missing. Please try again.');
        return;
      }
      
      // Store the values before closing modal (which sets them to null)
      const bookingId = currentBookingId;
      const rescheduleButton = currentRescheduleButton;
      
      // Convert date to a more readable format
      const dateObj = new Date(newDate);
      const options = { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' };
      const formattedDate = dateObj.toLocaleDateString('en-US', options);
      
      console.log('Debug - formatted date:', formattedDate);
      
      // Close modal (this sets currentBookingId and currentRescheduleButton to null)
      closeRescheduleModal();
      
      // Remove the button immediately for better UX
      if (rescheduleButton) {
        rescheduleButton.remove();
      }
      
      // Call the update function with the stored booking ID
      updateStatusWithDate(bookingId, 'rescheduled', formattedDate);
    }

    function updateStatusWithDate(bookingId, status, newDate = null) {
      console.log('Updating status:', { bookingId, status, newDate }); // Debug log
      
      const requestBody = {
        id: bookingId,
        status: status.toLowerCase()
      };
      
      if (newDate) {
        requestBody.new_date = newDate;
      }
      
      fetch('/api/consultations/update-status', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(requestBody)
      })
      .then(response => {
        console.log('Response status:', response.status); // Debug log
        
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
      })
      .then(data => {
        console.log('Response data:', data); // Debug log
        if (data.success) {
          alert(data.message);
          location.reload(); // Reload the page to reflect changes
        } else {
          alert('Failed to update status: ' + data.message);
        }
      })
      .catch(error => {
        console.error('Fetch error:', error);
        alert('An error occurred while updating the status: ' + error.message);
      });
    }

    function updateStatus(bookingId, status) {
      updateStatusWithDate(bookingId, status);
    }

    function removeThisButton(btn, bookingId, status) {
      btn.remove(); // Only remove the clicked button
      updateStatus(bookingId, status);
    }

    // Close modal when clicking outside of it
    document.addEventListener('click', function(event) {
      const modal = document.getElementById('rescheduleOverlay');
      if (event.target === modal) {
        closeRescheduleModal();
      }
    });

    // Close modal with Escape key
    document.addEventListener('keydown', function(event) {
      if (event.key === 'Escape') {
        closeRescheduleModal();
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
  </script>
  <script src="{{ asset('js/ccit.js') }}"></script>
</body>
</html>