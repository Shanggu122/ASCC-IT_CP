<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Notification;


class ConsultationBookingController extends Controller
{
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

        $date = $data['booking_date'] ?? now()->format('D M d Y');

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
            'Booking_Date' => $date,
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
}
