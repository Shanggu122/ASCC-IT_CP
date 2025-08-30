<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Notification;
use App\Models\Professor;
use App\Models\Subject;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Events\ProfessorAdded;
use App\Events\ProfessorDeleted;
use App\Events\ProfessorUpdated;


class ConsultationBookingController extends Controller
{
    /**
     * Store a new student consultation booking.
     */
    public function store(Request $request)
    {
        // 1) Validate incoming data
        $data = $request->validate([
            'subject_id'    => 'required|integer|exists:t_subject,Subject_ID',
            'types'         => 'required|array|min:1',
            'types.*'       => 'integer|exists:t_consultation_types,Consult_type_ID|string|max:255',
            'other_type_text' => 'nullable|string|max:255',
            'booking_date'  => 'required|string|max:50',
            'mode'          => 'required|in:online,onsite',
            'prof_id'       => 'required|integer|exists:professors,Prof_ID',
        ], [
            'subject_id.required' => 'Please select a subject.',
            'types.required' => 'Please select at least one consultation type.',
            'types.min' => 'Please select at least one consultation type.',
            'booking_date.required' => 'Please select a booking date.',
            'mode.required' => 'Please select consultation mode (Online or Onsite).',
            'prof_id.required' => 'Professor information is missing. Please try again.',
        ]);

        // Normalize to Asia/Manila and enforce weekday (Mon-Fri) only
        $rawInputDate = $data['booking_date'] ?? null;
        $carbonDate = null;
        if ($rawInputDate) {
            // Remove commas; try specific formats then fallback
            $clean = str_replace(',', '', trim($rawInputDate)); // e.g. "Fri Aug 29 2025"
            $tryFormats = ['D M d Y','D M d Y H:i','Y-m-d'];
            foreach ($tryFormats as $fmt) {
                try { $carbonDate = Carbon::createFromFormat($fmt, $clean, 'Asia/Manila'); break; } catch (\Exception $e) {}
            }
            if (!$carbonDate) { // fallback generic
                try { $carbonDate = Carbon::parse($clean, 'Asia/Manila'); } catch (\Exception $e) {}
            }
        }
        if (!$carbonDate) { $carbonDate = Carbon::now('Asia/Manila'); }
        $carbonDate = $carbonDate->setTimezone('Asia/Manila')->startOfDay();
        if ($carbonDate->isWeekend()) {
            return redirect()->back()->withErrors(['booking_date' => 'Weekend dates (Sat/Sun) are not allowed. Please pick a weekday (Mon–Fri).'])->withInput();
        }
        $date = $carbonDate->format('D M d Y');

        // Check if "Others" is selected (Consult_type_ID = 6 in your DB)
        $customType = null;
        if (in_array(6, $data['types']) && !empty($data['other_type_text'])) {
            $customType = $data['other_type_text'];
        }

        // Insert into t_consultation_bookings
        $bookingId = DB::table('t_consultation_bookings')->insertGetId([
            'Stud_ID' => Auth::user()->Stud_ID ?? null,
            'Prof_ID' => $data['prof_id'],
            'Consult_type_ID' => $data['types'][0] ?? null,
            'Custom_Type' => $customType, // <-- Save custom type if present
            'Subject_ID' => $data['subject_id'],
            'Booking_Date' => $date, // normalized Manila weekday date
            'Mode' => $data['mode'],
            'Status' => 'pending',
            'Created_At' => now(),
        ]);

        // Create notification for the professor
        if ($bookingId) {
            try {
                $student = Auth::user();
                $professor = DB::table('professors')->where('Prof_ID', $data['prof_id'])->first();
                $subject = DB::table('t_subject')->where('Subject_ID', $data['subject_id'])->first();
                $consultationType = DB::table('t_consultation_types')->where('Consult_type_ID', $data['types'][0])->first();
                
                $studentName = $student->Name ?? 'A student';
                $subjectName = $subject->Subject_Name ?? 'Unknown subject';
                $typeName = $consultationType->Consult_Type ?? 'consultation';
                
                // Use custom type if "Others" was selected
                if ($customType) {
                    $typeName = $customType;
                }
                
                Notification::createProfessorNotification(
                    $data['prof_id'], 
                    $bookingId, 
                    $studentName, 
                    $subjectName, 
                    $date, 
                    $typeName
                );
            } catch (\Exception $e) {
                Log::error('Failed to create notification: ' . $e->getMessage());
                // Continue with the booking even if notification fails
            }
        }

        // 4) Redirect back with success
        return redirect()->back()->with('success','Consultation booking submitted successfully! You will be notified once the professor responds.');
    }

    public function showBookingForm()
    {
        $professors = \App\Models\Professor::with('subjects')->where('Dept_ID', 1)->get();
        $consultationTypes = DB::table('t_consultation_types')->get();

        return view('itis', [
            'professors' => $professors,
            'consultationTypes' => $consultationTypes
        ]);
    }


    public function showForm()
    {
        $professors = \App\Models\Professor::with('subjects')->where('Dept_ID', 2)->get();
        $consultationTypes = DB::table('t_consultation_types')->get();

        return view('comsci', [
            'professors' => $professors,
            'consultationTypes' => $consultationTypes
        ]);
    }

    public function showFormAdmin()
    {
        $professors = \App\Models\Professor::where('Dept_ID', 2)->get();
        $subjects = \App\Models\Subject::all();
        return view('admin-comsci', [
            'professors' => $professors,
            'subjects' => $subjects,
        ]);
    }

    public function showItisAdmin()
    {
        $professors = DB::table('professors')->where('Dept_ID', 1)->get();
        $subjects = DB::table('t_subject')->get();
        return view('admin-itis', [
            'professors' => $professors,
            'subjects' => $subjects,
        ]);
    }

    /**
     * Return subject ids currently assigned to a professor (AJAX JSON).
     */
    public function getProfessorSubjects($profId)
    {
        try {
            $professor = Professor::with('subjects')->find($profId);
            if (!$professor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Professor not found',
                ], 404);
            }
            return response()->json([
                'success' => true,
                'subjects' => $professor->subjects->pluck('Subject_ID'),
            ]);
        } catch (\Exception $e) {
            Log::error('getProfessorSubjects error: '.$e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server error retrieving subjects',
            ], 500);
        }
    }

    /**
     * Assign subjects to a professor (used by legacy route). Accepts prof_id & subjects[]
     */
    public function assignSubjects(Request $request)
    {
        $data = $request->validate([
            'prof_id' => 'required|integer|exists:professors,Prof_ID',
            'subjects' => 'array',
            'subjects.*' => 'integer|exists:t_subject,Subject_ID',
        ]);
        try {
            $professor = Professor::find($data['prof_id']);
            $professor->subjects()->sync($data['subjects'] ?? []);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('assignSubjects error: '.$e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign subjects',
            ], 500);
        }
    }

    /**
     * Update professor (name, schedule, subject assignments) from admin panel.
     */
    public function updateProfessor(Request $request, $profId)
    {
        try {
            $professor = Professor::find($profId);
            if (!$professor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Professor not found',
                ], 404);
            }

            // Validate basic fields. Prof_ID field in form is ignored for update of PK.
            $validated = $request->validate([
                'Name' => 'required|string|max:255',
                'Schedule' => 'nullable|string',
                'subjects' => 'array',
                'subjects.*' => 'integer|exists:t_subject,Subject_ID',
            ]);

            $professor->Name = $validated['Name'];
            if (array_key_exists('Schedule', $validated)) {
                $professor->Schedule = $validated['Schedule'];
            }
            $professor->save();

            // Broadcast update
            event(new ProfessorUpdated($professor));

            // Sync subject assignments
            if ($request->has('subjects')) {
                $professor->subjects()->sync($validated['subjects'] ?? []);
            }

            return response()->json([
                'success' => true,
                'message' => 'Professor updated',
            ]);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $ve->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('updateProfessor error: '.$e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server error updating professor',
            ], 500);
        }
    }

    /**
     * Create a new professor (admin panel form submit).
     */
    public function addProfessor(Request $request)
    {
        try {
            // Only Faculty ID must be unique. Allow duplicate Names (and Emails if business rules permit duplicates).
            // If you still want Email unique, add |unique:professors,Email back to the rule below.
            $validated = $request->validate([
                'Prof_ID'   => 'required|integer|unique:professors,Prof_ID',
                'Name'      => 'required|string|max:255',
                'Email'     => 'required|email',
                'Dept_ID'   => 'required|integer',
                'Password'  => 'required|string|min:6',
                'Schedule'  => 'nullable|string',
                'subjects'  => 'array',
                'subjects.*'=> 'integer|exists:t_subject,Subject_ID',
            ], [
                'Prof_ID.unique' => 'Faculty ID already exists.',
            ]);

            $professor = Professor::create([
                'Prof_ID'  => $validated['Prof_ID'],
                'Name'     => $validated['Name'],
                'Email'    => $validated['Email'],
                'Dept_ID'  => $validated['Dept_ID'],
                'Password' => Hash::make($validated['Password']),
                'Schedule' => $validated['Schedule'] ?? null,
            ]);

            if ($request->has('subjects')) {
                $professor->subjects()->sync($validated['subjects'] ?? []);
            }

            // Broadcast new professor
            event(new ProfessorAdded($professor));

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Professor added', 'professor' => $professor]);
            }
            return redirect()->back()->with('success', 'Professor added successfully');
        } catch (\Illuminate\Validation\ValidationException $ve) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $ve->errors(),
                ], 422);
            }
            return redirect()->back()->withErrors($ve->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('addProfessor error: '.$e->getMessage());
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Server error adding professor',
                ], 500);
            }
            return redirect()->back()->withErrors(['general' => 'Server error adding professor'])->withInput();
        }
    }

    /**
     * Return professor details (optional route usage) as JSON for editing.
     */
    public function editProfessor($profId)
    {
        try {
            $professor = Professor::with('subjects')->find($profId);
            if (!$professor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Professor not found',
                ], 404);
            }
            return response()->json([
                'success' => true,
                'professor' => $professor,
                'subjects' => $professor->subjects->pluck('Subject_ID'),
            ]);
        } catch (\Exception $e) {
            Log::error('editProfessor fetch error: '.$e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server error retrieving professor',
            ], 500);
        }
    }

    /**
     * Delete professor (and cascades remove pivot records).
     */
    public function deleteProfessor($profId)
    {
        try {
            $professor = Professor::find($profId);
            if (!$professor) {
                return request()->wantsJson()
                    ? response()->json(['success' => false, 'message' => 'Professor not found'], 404)
                    : redirect()->back()->withErrors(['general' => 'Professor not found']);
            }
            $deptId = $professor->Dept_ID;
            $profId = $professor->Prof_ID;
            $professor->delete();
            // Broadcast deletion
            event(new ProfessorDeleted($profId, $deptId));
            if (request()->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Professor deleted']);
            }
            return redirect()->back()->with('success', 'Professor deleted successfully');
        } catch (\Exception $e) {
            Log::error('deleteProfessor error: '.$e->getMessage());
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Server error deleting professor'], 500);
            }
            return redirect()->back()->withErrors(['general' => 'Server error deleting professor']);
        }
    }
}
