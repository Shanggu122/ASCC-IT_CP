<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Dev: Consultation Reminder Test</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <style>
    body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; background:#f3f6f9; color:#12372a; padding:24px; }
    .card { background:#fff; border:1px solid #e2e8f0; border-radius:10px; max-width:1100px; margin:0 auto; overflow:hidden; }
    .card h1 { margin:0; font-size:18px; padding:14px 18px; background:#12372a; color:#fff; }
    .body { display:flex; gap:24px; padding:18px; }
    form { flex: 0 0 380px; }
    label { display:block; font-weight:600; margin:10px 0 6px; }
    input, select { width:100%; padding:10px 12px; border:1px solid #cbd5e1; border-radius:8px; }
    button { background:#0f9657; color:#fff; padding:10px 14px; border:none; border-radius:8px; font-weight:700; cursor:pointer; }
    .hint { font-size:12px; color:#64748b; }
    .status { margin-top:12px; font-size:14px; }
    .status.ok { color:#0f9657; }
    .status.err { color:#b91c1c; }
    .preview { flex: 1 1 auto; }
    iframe { width:100%; height:600px; border:1px solid #e2e8f0; border-radius:10px; background:#fff; }
    .links a { display:inline-block; margin-right:10px; margin-top:10px; }
    .meta { font-size:12px; color:#475569; margin-top:8px; }
  </style>
</head>
<body>
  <div class="card">
    <h1>Dev • Consultation Reminder (1-hour) — Test Sender</h1>
    <div class="body">
      <form method="post" action="{{ route('dev.test.reminder') }}">
        @csrf
        <label>Send to (professor email)</label>
        <input type="email" name="to" value="{{ old('to', $data['to']) }}" required />
        <div class="hint">Use a real @gmail.com address for actual delivery (e.g., johnpaullariba22@gmail.com).</div>

        <label style="margin-top:14px;">Professor Name</label>
        <input type="text" name="professor_name" value="{{ old('professor_name', $data['professor_name']) }}" />

        <label>Professor Email (for booking record)</label>
        <input type="email" name="professor_email" value="{{ old('professor_email', $data['professor_email']) }}" />

        <label>Student Name</label>
        <input type="text" name="student_name" value="{{ old('student_name', $data['student_name']) }}" />

        <label>Subject</label>
        <input type="text" name="subject_name" value="{{ old('subject_name', $data['subject_name']) }}" />

        <label>Type</label>
        <input type="text" name="type_name" value="{{ old('type_name', $data['type_name']) }}" />

        <label>Date (format: D M d Y)</label>
        <input type="text" name="date" value="{{ old('date', $data['date']) }}" />

        <label>Mode</label>
        <select name="mode">
          <option value="online" {{ old('mode', $data['mode'])==='online'?'selected':'' }}>Online</option>
          <option value="onsite" {{ old('mode', $data['mode'])==='onsite'?'selected':'' }}>Onsite</option>
        </select>

        <label style="margin-top:14px;">Use existing booking ID (optional)</label>
        <input type="number" name="use_booking_id" value="{{ old('use_booking_id', $data['use_booking_id']) }}" />
        <div class="hint">If provided, we won’t create a new booking. Links will target this existing booking.</div>

        <div style="margin-top:16px;display:flex;gap:10px;align-items:center;">
          <button type="submit">Send Test Email</button>
          @if($sent)
            <span class="status ok">Email sent to {{ $data['to'] }}.</span>
          @elseif($error)
            <span class="status err">Error: {{ $error }}</span>
          @endif
        </div>
        @if($bookingId)
          <div class="meta">Booking ID: <strong>{{ $bookingId }}</strong> | Prof ID: <strong>{{ $profId }}</strong></div>
        @endif
        @if($acceptUrl || $reschedUrl)
          <div class="links">
            @if($acceptUrl)<a href="{{ $acceptUrl }}" target="_blank">Open Accept Link</a>@endif
            @if($reschedUrl)<a href="{{ $reschedUrl }}" target="_blank">Open Reschedule Link</a>@endif
          </div>
        @endif
      </form>

      <div class="preview">
        <label style="display:block;font-weight:700;margin-bottom:8px;">Email HTML Preview</label>
        @if($previewHtml)
          <iframe srcdoc="{{ htmlspecialchars($previewHtml) }}"></iframe>
        @else
          <div style="padding:14px;border:1px dashed #cbd5e1;border-radius:8px;background:#fff;color:#64748b;">Send once to see the rendered email preview here.</div>
        @endif
      </div>
    </div>
  </div>
</body>
</html>
