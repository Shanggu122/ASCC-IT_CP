<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ConsultationBookingControllerProfessor extends Controller
{
    public function store(Request $request)
    {
        // 1) Validate incoming data
    $data = $request->validate([
        'subject_id'    => 'required|integer|exists:t_subject,Subject_ID',
        'types'         => 'array',                // optional multiple checkboxes
        'types.*'       => 'integer|exists:t_consultation_types,Consult_type_ID|string|max:255',
        'booking_date' => 'nullable|string|max:50',  // Ensure it's treated as a string
        'mode'          => 'required|in:online,onsite',
    ]);

    $date = $data['booking_date'] ?? now()->format('D M d Y'); // "Fri May 23 2025"
        
    // 2) Insert into t_consultation_bookings
    $bookingId = DB::table('t_consultation_bookings')->insertGetId([
        'Stud_ID' => Auth::user()->Stud_ID ?? null, // Assuming you have a logged-in student
        'Dept_ID' => 2,                                     // assuming Anette is Prof_ID=1
        'Consult_type_ID' => $data['subject_id'],
        'Booking_Date' => $date,                            // ← use the computed default
        'Mode' => $data['mode'],
        'Status' => 'pending',                              // Default status is pending
        'Created_At' => now(),
    ]);

    // 3) If you need to attach the checkbox “types” somewhere, you can handle that here…

    // 4) Redirect back with success
    return redirect()->back()->with('success','Consultation booked!');
}
}
