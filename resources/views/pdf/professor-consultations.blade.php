<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Consultation Logs</title>
    <style>
        @page { margin: 40px 35px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color:#122826; }
    h1 { text-align:center; margin:0 0 6px; font-size:28px; font-weight:700; }
        .prof-block { text-align:left; font-size:13px; line-height:1.45; margin-bottom:14px; }
        .prof-name { font-size:15px; font-weight:700; }
        .meta { text-align:center; font-size:12px; margin-bottom:12px; }
        table { width:100%; border-collapse:collapse; font-size:11px; }
        th, td { border:1px solid #122826; padding:4px 6px; }
        th { background:#f0f5f4; font-weight:600; }
        .footer { position:fixed; bottom:0; left:0; right:0; text-align:right; font-size:10px; color:#666; }
    </style>
</head>
<body>
    <h1>Professor Consultation Log</h1>
    <div class="prof-block">
        <div class="prof-name">Professor: {{ $professor->Name ?? 'Professor' }}</div>
        <div>ID: {{ $professor->Prof_ID ?? '—' }}</div>
        <div>Department: {{ $department }}</div>
        <div>Total: {{ $total }}</div>
    </div>
    <table>
        <thead>
            <tr>
                <th style="width:30px">No.</th>
                <th>Student</th>
                <th>Subject</th>
                <th style="width:95px">Date</th>
                <th>Type</th>
                <th style="width:55px">Mode</th>
                <th style="width:70px">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $i => $log)
                <tr>
                    <td>{{ $i+1 }}</td>
                    <td>{{ $log['student'] ?? '' }}</td>
                    <td>{{ $log['subject'] ?? '' }}</td>
                    <td>{{ $log['date'] ?? '' }}</td>
                    <td>{{ $log['type'] ?? '' }}</td>
                    <td>{{ $log['mode'] ?? '' }}</td>
                    <td>{{ $log['status'] ?? '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="footer">Generated {{ $generated }}</div>
</body>
</html>
