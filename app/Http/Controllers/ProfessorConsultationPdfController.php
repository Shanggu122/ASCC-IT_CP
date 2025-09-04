<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class ProfessorConsultationPdfController extends Controller
{
    public function download(Request $request)
    {
        $professor = Auth::guard('professor')->user();
        // Expect the frontend to send an array of logs; if not present, try to build from session or abort.
        $logs = $request->input('logs', []);
        if (!is_array($logs) || empty($logs)) {
            return response()->json(['error' => 'No consultation logs supplied'], 422);
        }
        // Sort by date then student (assuming date is a printable string)
        usort($logs, function($a, $b){
            $da = strtotime($a['date'] ?? '') ?: 0;
            $db = strtotime($b['date'] ?? '') ?: 0;
            if ($da === $db) return strcmp($a['student'] ?? '', $b['student'] ?? '');
            return $da <=> $db;
        });

        $deptMap = [
            1 => 'IT&IS',
            2 => 'COMSCI'
        ];
        $departmentLabel = isset($professor->Dept_ID) ? ($deptMap[$professor->Dept_ID] ?? 'N/A') : 'N/A';

        $data = [
            'professor' => $professor,
            'department' => $departmentLabel,
            'logs' => $logs,
            'total' => count($logs),
            'generated' => now()->format('M d, Y h:i A')
        ];

        $pdf = Pdf::loadView('pdf.professor-consultations', $data)->setPaper('A4', 'portrait');
        $filename = 'consultation_logs_'.($professor->Prof_ID ?? 'professor').'_'.now()->format('Ymd_His').'.pdf';
        return $pdf->download($filename);
    }
}
