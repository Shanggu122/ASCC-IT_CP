<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


class ConsultationBookingController extends Controller
{
    public function store(Request $request)
{
    // 1) Validate incoming data
    $data = $request->validate([
        'subject_id'    => 'required|integer|exists:t_subject,Subject_ID',
        'types'         => 'array',
        'types.*'       => 'integer|exists:t_consultation_types,Consult_type_ID|string|max:255',
        'other_type_text' => 'nullable|string|max:255',
        'booking_date'  => 'nullable|string|max:50',
        'mode'          => 'required|in:online,onsite',
        'prof_id'       => 'required|integer|exists:professors,Prof_ID',
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


    // 3) If you need to attach the checkbox “types” somewhere, you can handle that here…

    // 4) Redirect back with success
    return redirect()->back()->with('success','Consultation booked!');
    
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
    return view('admin-comsi', [
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
public function addProfessor(Request $request)
{
    $request->validate([
        'Prof_ID' => 'required|string|unique:professors,Prof_ID',
        'Name' => 'required|string|max:255',
        'Email' => 'required|email|unique:professors,Email',
        'Password' => 'required|string|min:3',
        'Dept_ID' => 'required|integer'
    ]);

    DB::table('professors')->insert([
        'Prof_ID' => $request->Prof_ID,
        'Name' => $request->Name,
        'Email' => $request->Email,
        'Password' => $request->Password, // For demo only! Hash in real apps!
        'Dept_ID' => $request->Dept_ID,
    ]);

    return redirect()->back()->with('success', 'Professor added!');
}
public function deleteProfessor($profId)
{
    DB::table('professors')->where('Prof_ID', $profId)->delete();
    return redirect()->back()->with('success', 'Professor deleted!');
}
public function assignSubjects(Request $request)
{
    $profId = $request->input('Prof_ID');
    $subjectIds = $request->input('subjects', []);
    $prof = \App\Models\Professor::findOrFail($profId);
    $prof->subjects()->sync($subjectIds); // sync replaces old assignments
    return redirect()->back()->with('success', 'Subjects assigned!');
}
public function editProfessor(Request $request, $profId)
{
    $request->validate([
        'Name' => 'required|string|max:255',
        'Email' => 'required|email|max:255',
    ]);
    \App\Models\Professor::where('Prof_ID', $profId)->update([
        'Name' => $request->Name,
        'Email' => $request->Email,
    ]);
    return redirect()->back()->with('success', 'Professor updated!');
}
public function updateProfessor(Request $request, $profId)
{
    $request->validate([
        'Name' => 'required|string|max:255',
        'Email' => 'required|email|max:255',
    ]);
    \App\Models\Professor::where('Prof_ID', $profId)->update([
        'Name' => $request->Name,
        'Email' => $request->Email,
    ]);
    return response()->json(['success' => true]);
}
}